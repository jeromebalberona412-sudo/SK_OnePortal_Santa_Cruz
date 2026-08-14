<?php

namespace App\Modules\Profile\Services;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Profile\Notifications\EmailChangeVerificationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailChangeService
{
    private const TOKEN_TTL_MINUTES = 60;

    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {
    }

    public function hasPendingChange(User $user): bool
    {
        return filled($user->pending_email)
            && filled($user->email_change_token)
            && blank($user->email_change_verified_at);
    }

    /**
     * @return array{plain_token: string}
     */
    public function requestChange(User $user, string $currentEmail, string $newEmail, string $password): array
    {
        $currentEmail = strtolower(trim($currentEmail));
        $newEmail = strtolower(trim($newEmail));

        if (strtolower((string) $user->email) !== $currentEmail) {
            throw ValidationException::withMessages([
                'current_email' => ['Current email does not match your account.'],
            ]);
        }

        if (! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        if ($newEmail === $currentEmail) {
            throw ValidationException::withMessages([
                'new_email' => ['New email must be different from your current email.'],
            ]);
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [$newEmail])->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'new_email' => ['This email address is already in use.'],
            ]);
        }

        if (User::query()->whereRaw('LOWER(pending_email) = ?', [$newEmail])->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'new_email' => ['This email address is already pending verification on another account.'],
            ]);
        }

        $plainToken = Str::random(64);

        $user->forceFill([
            'pending_email' => $newEmail,
            'email_change_token' => hash('sha256', $plainToken),
            'email_change_token_expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
            'email_change_verified_at' => null,
            'email_change_last_sent_at' => now(),
        ])->save();

        $this->sendVerificationMail($user, $plainToken);

        return ['plain_token' => $plainToken];
    }

    public function resend(User $user): void
    {
        if (! $this->hasPendingChange($user)) {
            throw ValidationException::withMessages([
                'email' => ['No pending email change request found.'],
            ]);
        }

        if ($user->email_change_last_sent_at?->isAfter(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            $seconds = max(1, self::RESEND_COOLDOWN_SECONDS - (int) $user->email_change_last_sent_at->diffInSeconds(now()));
            throw ValidationException::withMessages([
                'email' => ["Please wait {$seconds} seconds before resending."],
            ]);
        }

        $plainToken = Str::random(64);

        $user->forceFill([
            'email_change_token' => hash('sha256', $plainToken),
            'email_change_token_expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
            'email_change_last_sent_at' => now(),
        ])->save();

        $this->sendVerificationMail($user, $plainToken);
    }

    public function cancel(User $user): void
    {
        $user->forceFill([
            'pending_email' => null,
            'email_change_token' => null,
            'email_change_token_expires_at' => null,
            'email_change_verified_at' => null,
            'email_change_last_sent_at' => null,
        ])->save();
    }

    public function confirm(int $userId, string $plainToken): User
    {
        $user = User::query()->find($userId);

        if ($user === null || ! $this->hasPendingChange($user)) {
            throw ValidationException::withMessages([
                'token' => ['This email change link is invalid or has already been used.'],
            ]);
        }

        if (! hash_equals((string) $user->email_change_token, hash('sha256', $plainToken))) {
            throw ValidationException::withMessages([
                'token' => ['This email change link is invalid.'],
            ]);
        }

        if ($user->email_change_token_expires_at === null || $user->email_change_token_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'token' => ['This email change link has expired. Please request a new one.'],
            ]);
        }

        $this->applyConfirmedEmail($user);

        return $user->fresh();
    }

    public function applyConfirmedEmail(User $user): void
    {
        $newEmail = strtolower((string) $user->pending_email);

        if ($newEmail === '' || User::query()->whereRaw('LOWER(email) = ?', [$newEmail])->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email address is no longer available. Please start a new request.'],
            ]);
        }

        $user->forceFill([
            'email' => $newEmail,
            'pending_email' => null,
            'remember_token' => null,
            'email_verified_at' => now(),
            'email_change_token' => null,
            'email_change_token_expires_at' => null,
            'email_change_verified_at' => now(),
            'email_change_last_sent_at' => null,
        ])->save();

        if (Schema::hasColumn('users', 'must_change_password')) {
            User::query()
                ->whereKey($user->id)
                ->update(['must_change_password' => DB::raw("'false'::boolean")]);
        }

        $this->authenticationService->invalidateAllSessionsForUser($user->fresh() ?? $user);
    }

    public function hasPendingPasswordSet(User $user): bool
    {
        return filled($user->pending_email)
            && filled($user->email_change_token)
            && filled($user->email_change_verified_at);
    }

    public function validateSetPasswordToken(int $userId, string $plainToken): User
    {
        $user = User::query()->find($userId);

        if ($user === null || ! $this->hasPendingPasswordSet($user)) {
            throw ValidationException::withMessages([
                'token' => ['This link is invalid or has already been used.'],
            ]);
        }

        if (! hash_equals((string) $user->email_change_token, hash('sha256', $plainToken))) {
            throw ValidationException::withMessages([
                'token' => ['This link is invalid.'],
            ]);
        }

        return $user;
    }

    public function resendCooldownRemaining(User $user): int
    {
        if ($user->email_change_last_sent_at === null) {
            return 0;
        }

        return max(0, self::RESEND_COOLDOWN_SECONDS - (int) $user->email_change_last_sent_at->diffInSeconds(now()));
    }

    protected function sendVerificationMail(User $user, string $plainToken): void
    {
        Notification::route('mail', $user->pending_email)
            ->notify(new EmailChangeVerificationNotification($user, $plainToken));
    }
}
