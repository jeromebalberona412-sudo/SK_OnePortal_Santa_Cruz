<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Notifications\NewLocationLoginNotification;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    public function __construct(
        protected TenantContextService $tenantContextService,
        protected FeatureFlagService $featureFlagService,
        protected LoginSecurityService $loginSecurityService,
        protected EmailVerificationDeviceService $emailVerificationDeviceService,
        protected TrustedDeviceService $trustedDeviceService,
        protected DeviceVerificationService $deviceVerificationService,
        protected SuspiciousLoginService $suspiciousLoginService,
        protected AuthAuditLogService $auditLogService,
    ) {}

    public function authenticate(Request $request): ?User
    {
        $email = (string) $request->input('email');
        $password = (string) $request->input('password');

        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'invalid_credentials']);
            $this->loginSecurityService->applyFailureLockout($user, $email, $request);
            $this->auditLogService->log('login_failed', $user, $request, ['reason' => 'invalid_credentials']);

            return null;
        }

        if ($this->loginSecurityService->isLocked($user)) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'lockout_active']);
            $this->auditLogService->log('login_blocked_lockout', $user, $request);

            return null;
        }

        $tenantId = $this->tenantContextService->tenantId();
        $deviceFingerprint = $this->emailVerificationDeviceService->fingerprintFromRequest($request);

        if (
            ! $user->hasRole((string) config('sk_fed_auth.required_role', User::ROLE_SK_FED))
            || $tenantId === null
            || (int) ($user->tenant_id ?? 0) !== $tenantId
        ) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'role_or_tenant_mismatch']);
            $this->auditLogService->log('login_blocked_scope', $user, $request);

            return null;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->startEmailVerificationWait(
                user: $user,
                email: $email,
                request: $request,
                reason: 'email_unverified',
                message: 'A verification email has been sent. Complete verification to continue.'
            );

            return null;
        }

        if ($this->emailVerificationDeviceService->requiresReverification($user, $deviceFingerprint)) {
            $this->startEmailVerificationWait(
                user: $user,
                email: $email,
                request: $request,
                reason: 'email_device_changed',
                message: 'New device detected. Email verification is required again for security.'
            );

            return null;
        }

        if (
            $this->featureFlagService->enabled('features.device_verification')
            && ! $this->trustedDeviceService->isTrusted($user, $request)
        ) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->deviceVerificationService->issueToken($user, $request);
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'untrusted_device']);
            $this->auditLogService->log('login_challenged_device', $user, $request);
            $request->session()->flash('status', 'Device verification is required before access is granted.');
            $request->session()->flash('device_email', $user->email);

            return null;
        }

        $suspicious = ['is_suspicious' => false, 'signals' => []];

        if ($this->featureFlagService->enabled('features.suspicious_login_detection')) {
            $suspicious = $this->suspiciousLoginService->detect($user, (string) $request->ip());
        }

        if (($suspicious['is_suspicious'] ?? false) && $this->featureFlagService->enabled('features.login_alert_notifications')) {
            $user->notify(new NewLocationLoginNotification(
                ipAddress: (string) $request->ip(),
                userAgent: $request->userAgent(),
                signals: $suspicious['signals'] ?? [],
            ));
        }

        $this->trustedDeviceService->touch($user, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $this->loginSecurityService->recordAttempt($user, $email, true, $request, ['reason' => 'success']);
        $this->auditLogService->log('login_success', $user, $request, ['suspicious' => $suspicious]);
        $user->recordLogin((string) $request->ip());
        $this->emailVerificationDeviceService->upsertCurrentDevice($user, $request);

        return $user;
    }

    protected function startEmailVerificationWait(
        User $user,
        string $email,
        Request $request,
        string $reason,
        string $message,
    ): void {
        $waitMinutes = (int) config('sk_fed_auth.verification.wait_minutes', 15);
        $requiresFreshVerification = $reason === 'email_device_changed';

        $request->session()->put('sk_fed_email_verification_pending', [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'started_at' => Carbon::now()->toIso8601String(),
            'expires_at' => now()->addMinutes($waitMinutes)->toIso8601String(),
            'fingerprint' => $this->emailVerificationDeviceService->fingerprintFromRequest($request),
            'ip' => (string) $request->ip(),
            'user_agent' => (string) ($request->userAgent() ?? ''),
            'requires_fresh_verification' => $requiresFreshVerification,
            'verified_at_snapshot' => $user->email_verified_at?->toIso8601String(),
        ]);

        $user->sendEmailVerificationNotification();
        $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => $reason]);
        $this->auditLogService->log('login_blocked_'.$reason, $user, $request);
        $request->session()->flash('status', $message);
        $request->session()->flash('verification_wait', true);
    }
}
