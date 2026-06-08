<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SkOfficialPresenceService
{
    public function markOnline(User $user): void
    {
        $updates = [];

        if (Schema::hasColumn('users', 'last_seen')) {
            $updates['last_seen'] = now();
        }

        if (Schema::hasColumn('users', 'online_status')) {
            $updates['online_status'] = 'online';
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }
    }

    public function markOffline(User $user): void
    {
        $updates = [];

        if (Schema::hasColumn('users', 'online_status')) {
            $updates['online_status'] = 'offline';
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }
    }

    public function syncStaleOfflineStatuses(): void
    {
        if (! Schema::hasColumn('users', 'online_status') || ! Schema::hasColumn('users', 'last_seen')) {
            return;
        }

        $timeout = (int) config('sk_official_auth.single_session.heartbeat_timeout_seconds', 120);

        User::query()
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('online_status', 'online')
            ->where(function ($query) use ($timeout) {
                $query->whereNull('last_seen')
                    ->orWhere('last_seen', '<', now()->subSeconds($timeout));
            })
            ->update(['online_status' => 'offline']);
    }

    public function isOnline(User $user): bool
    {
        if (Schema::hasColumn('users', 'online_status')) {
            return strtolower((string) $user->online_status) === 'online';
        }

        $timeout = (int) config('sk_official_auth.single_session.heartbeat_timeout_seconds', 120);

        return $user->last_seen !== null
            && $user->last_seen->diffInSeconds(now()) <= $timeout;
    }
}
