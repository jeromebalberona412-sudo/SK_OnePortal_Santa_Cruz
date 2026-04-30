<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\Models\LoginAttempt;

class SuspiciousLoginService
{
    /**
     * @return array{is_suspicious: bool, signals: array<int, string>}
     */
    public function detect(User $user, string $ipAddress): array
    {
        $signals = [];
        $hour = (int) now()->format('G');
        $nightStart = (int) config('sk_official_auth.suspicious.night_start_hour', 0);
        $nightEnd = (int) config('sk_official_auth.suspicious.night_end_hour', 4);

        if ($hour >= $nightStart && $hour <= $nightEnd) {
            $signals[] = 'night_login';
        }

        $failureThreshold = (int) config('sk_official_auth.suspicious.failure_threshold', 3);
        $recentFailuresQuery = LoginAttempt::query()
            ->where('user_id', $user->getKey())
            ->where('attempted_at', '>=', now()->subHour());

        if ($recentFailuresQuery->getConnection()->getDriverName() === 'pgsql') {
            $recentFailuresQuery->whereRaw('"successful" = false');
        } else {
            $recentFailuresQuery->where('successful', false);
        }

        $recentFailures = $recentFailuresQuery->count();

        if ($recentFailures >= $failureThreshold) {
            $signals[] = 'recent_failed_attempts';
        }

        if (! empty($user->last_login_ip) && $user->last_login_ip !== $ipAddress) {
            $signals[] = 'new_ip_address';
        }

        return [
            'is_suspicious' => $signals !== [],
            'signals' => $signals,
        ];
    }
}
