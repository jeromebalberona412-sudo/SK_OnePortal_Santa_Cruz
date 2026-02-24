<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Shared\Models\User;
use App\Modules\Authentication\Models\LoginAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginSecurityService
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 15;
    const LOCKOUT_RESET_THRESHOLD = 3;

    public function checkAccountLockout(string $email, string $ip): ?Carbon
    {
        $user = User::where('email', $email)->first();
        if ($user && $user->lockout_until && $user->lockout_until->isFuture()) {
            return $user->lockout_until;
        }
        return null;
    }

    public function recordFailedAttempt(string $email, string $ip, string $userAgent): void
    {
        try {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'successful' => false,
                'attempted_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::channel('security')->warning('Failed to persist login attempt', [
                'email' => $email, 'ip_address' => $ip, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function evaluateLockout(?User $user, string $email, string $ip): bool
    {
        try {
            $emailAttempts = LoginAttempt::recentByEmail($email, self::LOCKOUT_DURATION)->count();
            $ipAttempts = LoginAttempt::recentByIp($ip, self::LOCKOUT_DURATION)->count();
        } catch (Throwable $e) {
            Log::channel('security')->warning('Failed to evaluate lockout state', [
                'email' => $email, 'ip_address' => $ip, 'error' => $e->getMessage(),
            ]);
            return false;
        }

        if ($emailAttempts >= self::MAX_ATTEMPTS || $ipAttempts >= self::MAX_ATTEMPTS) {
            if ($user) {
                $user->incrementLockoutCount(self::LOCKOUT_DURATION);
                Log::channel('security')->warning('Account locked due to failed login attempts', [
                    'user_id' => $user->id, 'email' => $email, 'ip_address' => $ip,
                    'lockout_count' => $user->lockout_count, 'lockout_until' => $user->lockout_until,
                ]);
                $this->logEmailEvent('account_locked', [
                    'to' => $email, 'subject' => 'Account Locked - Security Alert',
                    'lockout_duration' => self::LOCKOUT_DURATION . ' minutes',
                    'lockout_until' => $user->lockout_until,
                ]);
                return true;
            }
        }
        return false;
    }

    public function clearLoginAttempts(User $user): void
    {
        try {
            LoginAttempt::where('email', $user->email)->delete();
        } catch (Throwable $e) {
            Log::channel('security')->warning('Failed to clear login attempts', [
                'user_id' => $user->id, 'email' => $user->email, 'error' => $e->getMessage(),
            ]);
        }
        $user->resetLockout();
    }

    public function shouldRequirePasswordReset(User $user): bool
    {
        return $user->requiresPasswordReset();
    }

    public function logEmailEvent(string $event, array $data): void
    {
        Log::channel('email-events')->info("Email Event: {$event}", $data);
    }
}
