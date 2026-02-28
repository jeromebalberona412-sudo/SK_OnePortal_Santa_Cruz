<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\LoginAttempt;
use App\Modules\Shared\Models\User;

class SuspiciousLoginService
{
    public function detect(User $user, string $ipAddress): array
    {
        $signals = [];

        $lastSuccess = LoginAttempt::query()
            ->where('user_id', $user->getKey())
            ->where('successful', true)
            ->latest('attempted_at')
            ->first();

        if ($lastSuccess !== null && $lastSuccess->ip_address !== $ipAddress) {
            $signals[] = 'ip_change';
        }

        $hour = now()->hour;
        $nightStart = (int) config('sk_fed_auth.suspicious.night_start_hour', 0);
        $nightEnd = (int) config('sk_fed_auth.suspicious.night_end_hour', 4);

        if ($hour >= $nightStart && $hour <= $nightEnd) {
            $signals[] = 'abnormal_login_time';
        }

        $failureThreshold = (int) config('sk_fed_auth.suspicious.failure_threshold', 3);

        $recentFailures = LoginAttempt::query()
            ->where('email', $user->email)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes(30))
            ->count();

        if ($recentFailures >= $failureThreshold) {
            $signals[] = 'repeated_failures';
        }

        return [
            'is_suspicious' => $signals !== [],
            'signals' => $signals,
        ];
    }
}
