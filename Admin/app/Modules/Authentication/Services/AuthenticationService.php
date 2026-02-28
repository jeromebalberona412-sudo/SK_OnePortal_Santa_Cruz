<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Shared\Models\User;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    protected $loginSecurityService;
    protected $auditService;

    public function __construct(
        LoginSecurityService $loginSecurityService,
        AuditLogInterface $auditService
    ) {
        $this->loginSecurityService = $loginSecurityService;
        $this->auditService = $auditService;
    }

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

        if (!$user || !Hash::check($password, $user->password)) {
            $this->loginSecurityService->recordFailedAttempt($email, $ip, $userAgent);
            $this->auditService->logLoginFailed($email);
            $this->loginSecurityService->evaluateLockout($user, $email, $ip);
            return null;
        }

        if (! $user->isAdmin() || $user->status !== User::STATUS_ACTIVE || ! $user->tenant_id) {
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
        $this->auditService->logLoginSuccess($user);
        $request->session()->regenerate();
        return $user;
    }
}
