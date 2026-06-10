<?php

namespace App\Modules\Authentication\Services;

use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\Authentication\Support\PasswordHelper;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;

class AuthenticationService
{
    public function __construct(
        protected LoginSecurityService $loginSecurityService,
        protected AuditLogInterface $auditService,
    ) {}

    public function authenticate(Request $request): ?User
    {
        $email = (string) $request->input('email');
        $password = (string) $request->input('password');
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'unknown';

        $lockoutUntil = $this->loginSecurityService->checkAccountLockout($email, $ip);
        if ($lockoutUntil) {
            $this->loginSecurityService->recordFailedAttempt($email, $ip, $userAgent);
            $this->auditService->logLoginFailed($email);

            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! PasswordHelper::matches($password, (string) $user->password)) {
            $this->loginSecurityService->recordFailedAttempt($email, $ip, $userAgent);
            $this->auditService->logLoginFailed($email);
            $this->loginSecurityService->evaluateLockout($user, $email, $ip, $this->auditService);

            return null;
        }

        if (! $user->isAdmin() || $user->status !== User::STATUS_ACTIVE) {
            $this->loginSecurityService->recordFailedAttempt($email, $ip, $userAgent);
            $this->auditService->logLoginFailed($email);

            return null;
        }

        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            $this->loginSecurityService->recordFailedAttempt($email, $ip, $userAgent);
            $this->auditService->logLoginFailed($email);

            return null;
        }

        if ($this->loginSecurityService->shouldRequirePasswordReset($user)) {
            $this->auditService->logLoginFailed($email);

            return null;
        }

        $this->loginSecurityService->clearLoginAttempts($user);
        $user->recordLogin($ip);

        if (! $user->must_change_password) {
            $this->auditService->logLoginSuccess($user);
        }

        return $user;
    }
}
