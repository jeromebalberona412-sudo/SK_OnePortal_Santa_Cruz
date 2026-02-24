<?php

namespace App\Modules\AuditLog\Services;

use App\Modules\Shared\Models\User;
use App\Modules\AuditLog\Models\AdminActivityLog;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogService implements AuditLogInterface
{
    /**
     * Log an audit event.
     *
     * @param string $eventType
     * @param User|null $user
     * @param array $metadata
     * @return void
     */
    public function log(string $eventType, ?User $user = null, array $metadata = []): void
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        try {
            AdminActivityLog::create([
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'metadata' => $metadata,
            ]);
        } catch (Throwable $e) {
            Log::channel('audit')->warning('Failed to persist audit event', [
                'event_type' => $eventType,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Also log to audit log file for redundancy
        Log::channel('audit')->info("Audit: {$eventType}", [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip_address' => $ipAddress,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log successful login.
     *
     * @param User $user
     * @return void
     */
    public function logLoginSuccess(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_LOGIN_SUCCESS, $user, [
            'email' => $user->email,
        ]);
    }

    /**
     * Log failed login attempt.
     *
     * @param string $email
     * @return void
     */
    public function logLoginFailed(string $email): void
    {
        $this->log(AdminActivityLog::EVENT_LOGIN_FAILED, null, [
            'email' => $email,
        ]);
    }

    /**
     * Log account lockout.
     *
     * @param User $user
     * @return void
     */
    public function logAccountLocked(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_ACCOUNT_LOCKED, $user, [
            'email' => $user->email,
            'lockout_count' => $user->lockout_count,
            'lockout_until' => $user->lockout_until?->toDateTimeString(),
        ]);
    }

    /**
     * Log successful 2FA challenge.
     *
     * @param User $user
     * @return void
     */
    public function log2FAChallengePassed(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_2FA_CHALLENGE_PASSED, $user, [
            'email' => $user->email,
        ]);
    }

    /**
     * Log failed 2FA challenge.
     *
     * @param User $user
     * @return void
     */
    public function log2FAChallengeFailed(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_2FA_CHALLENGE_FAILED, $user, [
            'email' => $user->email,
        ]);
    }

    /**
     * Log 2FA enabled.
     *
     * @param User $user
     * @return void
     */
    public function log2FAEnabled(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_2FA_ENABLED, $user, [
            'email' => $user->email,
            'confirmed_at' => $user->two_factor_confirmed_at?->toDateTimeString(),
        ]);
    }

    /**
     * Log 2FA disabled.
     *
     * @param User $user
     * @return void
     */
    public function log2FADisabled(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_2FA_DISABLED, $user, [
            'email' => $user->email,
        ]);
    }

    /**
     * Log password change.
     *
     * @param User $user
     * @return void
     */
    public function logPasswordChanged(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_PASSWORD_CHANGED, $user, [
            'email' => $user->email,
        ]);
    }

    /**
     * Log password reset request.
     *
     * @param string $email
     * @return void
     */
    public function logPasswordResetRequested(string $email): void
    {
        $this->log(AdminActivityLog::EVENT_PASSWORD_RESET_REQUESTED, null, [
            'email' => $email,
        ]);
    }

    /**
     * Log user logout.
     *
     * @param User $user
     * @return void
     */
    public function logLogout(User $user): void
    {
        $this->log(AdminActivityLog::EVENT_LOGOUT, $user, [
            'email' => $user->email,
        ]);
    }
}
