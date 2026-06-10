<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Notifications\AdminLoginVerificationNotification;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginEmailVerificationService
{
    private const TOKEN_TTL_MINUTES = 60;

    private const RESEND_COOLDOWN_SECONDS = 60;

    private const TOKEN_CACHE_PREFIX = 'admin_login_verify_token:';

    private const SENT_CACHE_PREFIX = 'admin_login_verify_sent:';

    public function initiate(User $user): void
    {
        User::query()
            ->whereKey($user->id)
            ->update(['email_verified_at' => null]);

        $user->setAttribute('email_verified_at', null);
        $user->syncOriginalAttribute('email_verified_at');

        $this->send($user);
    }

    public function send(User $user): void
    {
        $plainToken = Str::random(64);

        Cache::put(
            self::TOKEN_CACHE_PREFIX.$user->id,
            hash('sha256', $plainToken),
            now()->addMinutes(self::TOKEN_TTL_MINUTES),
        );

        Cache::put(
            self::SENT_CACHE_PREFIX.$user->id,
            now()->timestamp,
            now()->addHours(2),
        );

        $user->notify(new AdminLoginVerificationNotification($plainToken));
    }

    public function resend(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Your email is already verified for this login.'],
            ]);
        }

        $remaining = $this->resendCooldownRemaining($user);
        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'email' => ["Please wait {$remaining} seconds before resending."],
            ]);
        }

        $this->send($user);
    }

    public function confirm(int $userId, string $plainToken): User
    {
        $user = User::query()->find($userId);

        if ($user === null || ! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'token' => ['This verification link is invalid or has expired.'],
            ]);
        }

        $storedHash = Cache::get(self::TOKEN_CACHE_PREFIX.$userId);

        if ($storedHash === null || ! hash_equals((string) $storedHash, hash('sha256', $plainToken))) {
            throw ValidationException::withMessages([
                'token' => ['This verification link is invalid or has expired.'],
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        Cache::forget(self::TOKEN_CACHE_PREFIX.$userId);

        return $user->fresh();
    }

    public function resendCooldownRemaining(User $user): int
    {
        $sentAt = Cache::get(self::SENT_CACHE_PREFIX.$user->id);

        if ($sentAt === null) {
            return 0;
        }

        $elapsed = now()->timestamp - (int) $sentAt;

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }
}
