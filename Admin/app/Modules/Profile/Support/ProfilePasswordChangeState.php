<?php

namespace App\Modules\Profile\Support;

use Illuminate\Support\Facades\Cache;

class ProfilePasswordChangeState
{
    private const PENDING_PREFIX = 'admin_profile_password_change_pending:';

    private const CONFIRMED_PREFIX = 'admin_profile_password_change_confirmed:';

    private const PENDING_TTL_MINUTES = 120;

    private const CONFIRMED_TTL_MINUTES = 30;

    public static function markPending(int $userId): void
    {
        Cache::put(
            self::PENDING_PREFIX.$userId,
            true,
            now()->addMinutes(self::PENDING_TTL_MINUTES),
        );
    }

    public static function isPending(int $userId): bool
    {
        return (bool) Cache::get(self::PENDING_PREFIX.$userId, false);
    }

    public static function clearPending(int $userId): void
    {
        Cache::forget(self::PENDING_PREFIX.$userId);
    }

    public static function markConfirmed(int $userId): void
    {
        Cache::put(
            self::CONFIRMED_PREFIX.$userId,
            true,
            now()->addMinutes(self::CONFIRMED_TTL_MINUTES),
        );
    }

    public static function wasConfirmed(int $userId): bool
    {
        return (bool) Cache::get(self::CONFIRMED_PREFIX.$userId, false);
    }

    public static function forgetConfirmed(int $userId): void
    {
        Cache::forget(self::CONFIRMED_PREFIX.$userId);
    }
}
