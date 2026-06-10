<?php

namespace App\Modules\Authentication\Services;

use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\Authentication\Notifications\AdminPasswordResetNotification;
use App\Modules\Authentication\Support\PasswordHelper;
use App\Modules\Profile\Support\ProfilePasswordChangeState;
use App\Modules\Shared\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function __construct(
        protected AuditLogInterface $auditService,
    ) {}

    public function sendResetLink(Request $request, string $email): void
    {
        $normalizedEmail = strtolower(trim($email));
        $user = $this->findUserByEmail($normalizedEmail);
        $isResettable = $user !== null && $user->isAdmin() && $user->status === User::STATUS_ACTIVE;

        $this->auditService->logPasswordResetRequested($normalizedEmail);

        if (! $isResettable) {
            return;
        }

        $this->broker()->sendResetLink(
            ['email' => $user->email],
            function (CanResetPassword $notifiable, #[\SensitiveParameter] string $token): void {
                if ($notifiable instanceof User) {
                    $notifiable->notify(new AdminPasswordResetNotification($token));
                }
            },
        );
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function resetPassword(Request $request, array $credentials): User
    {
        $email = strtolower(trim((string) ($credentials['email'] ?? '')));
        $token = (string) ($credentials['token'] ?? '');
        $password = (string) ($credentials['password'] ?? '');

        $user = $this->findUserByEmail($email);

        if ($user === null || ! $user->isAdmin()) {
            $this->throwInvalidResetLink();
        }

        if (! $this->broker()->tokenExists($user, $token)) {
            $this->throwInvalidResetLink();
        }

        if (PasswordHelper::matches($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $user->forceFill([
            'password' => $password,
            'email_verified_at' => now(),
            'remember_token' => null,
        ])->save();

        $user->clearMustChangePassword();

        $this->broker()->deleteToken($user);
        $this->invalidateUserSessions($user);

        event(new PasswordReset($user));

        $this->auditService->logPasswordChanged($user);

        $isProfilePasswordChange = ProfilePasswordChangeState::isPending($user->id)
            || $user->password_change_last_sent_at !== null;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isProfilePasswordChange) {
            ProfilePasswordChangeState::clearPending($user->id);
            ProfilePasswordChangeState::markConfirmed($user->id);

            $user->forceFill([
                'password_change_last_sent_at' => null,
            ])->save();
        }

        return $user;
    }

    protected function findUserByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    protected function broker(): PasswordBroker
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker(config('auth.defaults.passwords', 'users'));

        return $broker;
    }

    protected function invalidateUserSessions(User $user): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable) {
            // Best effort.
        }
    }

    protected function throwInvalidResetLink(): never
    {
        throw ValidationException::withMessages([
            'email' => 'This password reset link is invalid or has expired.',
        ]);
    }
}
