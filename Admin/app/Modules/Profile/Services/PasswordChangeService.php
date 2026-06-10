<?php

namespace App\Modules\Profile\Services;

use App\Modules\Profile\Notifications\ProfilePasswordChangeNotification;
use App\Modules\Profile\Support\ProfilePasswordChangeState;
use App\Modules\Shared\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordChangeService
{
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function hasPendingChange(User $user): bool
    {
        return $user->password_change_last_sent_at !== null
            && $this->resetTokenExists($user);
    }

    public function requestChange(User $user, string $email): void
    {
        $normalizedEmail = strtolower(trim($email));

        if (strtolower((string) $user->email) !== $normalizedEmail) {
            throw ValidationException::withMessages([
                'email' => ['This email does not match your account.'],
            ]);
        }

        ProfilePasswordChangeState::forgetConfirmed($user->id);
        $this->sendResetLink($user);
    }

    public function resend(User $user): void
    {
        if ($user->password_change_last_sent_at === null) {
            throw ValidationException::withMessages([
                'email' => ['No pending password change request found.'],
            ]);
        }

        if ($user->password_change_last_sent_at?->isAfter(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            $seconds = max(1, self::RESEND_COOLDOWN_SECONDS - (int) $user->password_change_last_sent_at->diffInSeconds(now()));
            throw ValidationException::withMessages([
                'email' => ["Please wait {$seconds} seconds before resending."],
            ]);
        }

        $this->sendResetLink($user);
    }

    public function cancel(User $user): void
    {
        $this->deleteResetToken($user);

        ProfilePasswordChangeState::clearPending($user->id);
        ProfilePasswordChangeState::forgetConfirmed($user->id);

        $user->forceFill([
            'password_change_last_sent_at' => null,
        ])->save();
    }

    public function resendCooldownRemaining(User $user): int
    {
        if ($user->password_change_last_sent_at === null) {
            return 0;
        }

        return max(0, self::RESEND_COOLDOWN_SECONDS - (int) $user->password_change_last_sent_at->diffInSeconds(now()));
    }

    protected function sendResetLink(User $user): void
    {
        $status = $this->broker()->sendResetLink(
            ['email' => $user->email],
            function (CanResetPassword $notifiable, #[\SensitiveParameter] string $token): void {
                if ($notifiable instanceof User) {
                    $notifiable->notify(new ProfilePasswordChangeNotification($token));
                }
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        $user->forceFill([
            'password_change_last_sent_at' => now(),
        ])->save();

        ProfilePasswordChangeState::markPending($user->id);
    }

    protected function resetTokenExists(User $user): bool
    {
        $table = (string) config(
            'auth.passwords.'.config('auth.defaults.passwords', 'users').'.table',
            'password_reset_tokens',
        );

        return DB::table($table)
            ->where('email', $user->email)
            ->exists();
    }

    protected function deleteResetToken(User $user): void
    {
        $table = (string) config(
            'auth.passwords.'.config('auth.defaults.passwords', 'users').'.table',
            'password_reset_tokens',
        );

        DB::table($table)
            ->where('email', $user->email)
            ->delete();
    }

    protected function broker(): PasswordBroker
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker(config('auth.defaults.passwords', 'users'));

        return $broker;
    }
}
