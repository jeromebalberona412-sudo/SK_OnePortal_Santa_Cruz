<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\Notifications\NewLocationLoginNotification;
use App\Modules\Authentication\Notifications\SessionTakeoverOtpNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    public const TAKEOVER_PENDING_SESSION_KEY = 'sk_official_takeover_pending';

    public function __construct(
        protected TenantContextService $tenantContextService,
        protected FeatureFlagService $featureFlagService,
        protected LoginSecurityService $loginSecurityService,
        protected EmailVerificationDeviceService $emailVerificationDeviceService,
        protected TrustedDeviceService $trustedDeviceService,
        protected SuspiciousLoginService $suspiciousLoginService,
        protected AuthAuditLogService $auditLogService,
    ) {}

    public function authenticate(Request $request): ?User
    {
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'invalid_credentials']);
            $this->loginSecurityService->applyFailureLockout($user, $email, $request);
            $this->auditLogService->log(
                event: 'login_failed',
                user: $user,
                request: $request,
                metadata: ['reason' => 'invalid_credentials'],
                outcome: AuthAuditLogService::OUTCOME_FAILED,
                resourceType: 'authentication',
                resourceId: $user?->getKey(),
            );

            return null;
        }

        if ($this->loginSecurityService->isLocked($user)) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'lockout_active']);
            $this->auditLogService->log(
                event: 'login_blocked_lockout',
                user: $user,
                request: $request,
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'authentication',
                resourceId: $user->getKey(),
            );

            return null;
        }

        $tenantId = $this->tenantContextService->tenantId();
        $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);

        if (
            ! $user->hasRole($requiredRole)
            || $tenantId === null
            || (int) ($user->tenant_id ?? 0) !== $tenantId
            || ! $user->isActiveOfficial()
        ) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'role_tenant_or_status_mismatch']);
            $this->auditLogService->log(
                event: 'login_blocked_scope',
                user: $user,
                request: $request,
                metadata: [
                    'required_role' => $requiredRole,
                    'status' => $user->status ?? null,
                    'tenant_id' => $user->tenant_id ?? null,
                    'barangay_id' => $user->barangay_id ?? null,
                ],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'authentication',
                resourceId: $user->getKey(),
            );

            return null;
        }

        $deviceFingerprint = $this->emailVerificationDeviceService->fingerprintFromRequest($request);

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

        if ($this->hasActiveSessionConflict($user, $request)) {
            $this->startTakeoverWait($user, $email, $request);

            return null;
        }

        if ($this->featureFlagService->enabled('features.device_verification') && ! $this->trustedDeviceService->isTrusted($user, $request)) {
            $this->trustedDeviceService->trust($user, $request);
            $this->auditLogService->log(
                event: 'login_trusted_device_registered',
                user: $user,
                request: $request,
                metadata: ['fingerprint' => $deviceFingerprint],
                outcome: AuthAuditLogService::OUTCOME_SUCCESS,
                resourceType: 'trusted_device',
                resourceId: $deviceFingerprint,
            );
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
        $this->auditLogService->log(
            event: 'login_success',
            user: $user,
            request: $request,
            metadata: ['suspicious' => $suspicious],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'authentication',
            resourceId: $user->getKey(),
        );

        $user->recordLogin((string) $request->ip());
        $this->emailVerificationDeviceService->upsertCurrentDevice($user, $request);
        $user->forceFill([
            'active_device' => $this->userAgent($request),
            'last_ip' => (string) $request->ip(),
        ])->save();

        return $user;
    }

    public function claimCurrentSession(User $user, Request $request): void
    {
        $user->forceFill([
            'active_session_id' => $request->session()->getId(),
            'last_seen' => now(),
            'active_device' => $this->userAgent($request),
            'last_ip' => (string) $request->ip(),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    public function completeEmailVerificationLogin(User $user, Request $request, array $pending): void
    {
        Auth::login($user);
        $request->session()->regenerate();

        $this->claimCurrentSession($user, $request);
        $this->emailVerificationDeviceService->markVerifiedDeviceFromPending($user, $pending);
        $this->loginSecurityService->clearAfterSuccess($user);
        $this->loginSecurityService->recordAttempt($user, (string) $user->email, true, $request, ['reason' => 'email_verification_completed']);
        $this->auditLogService->log(
            event: 'login_success_email_verified',
            user: $user,
            request: $request,
            metadata: ['verification_flow' => 'email_link'],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'authentication',
            resourceId: $user->getKey(),
        );
        $user->recordLogin((string) $request->ip());
    }

    public function recordHeartbeat(Request $request): void
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            throw new AuthenticationException;
        }

        $currentSessionId = $request->session()->getId();
        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '') {
            $this->claimCurrentSession($user, $request);

            return;
        }

        if ($activeSessionId !== $currentSessionId) {
            throw new AuthenticationException;
        }

        $user->forceFill([
            'last_seen' => now(),
            'last_ip' => (string) $request->ip(),
        ])->save();
    }

    public function showTakeoverWaitData(Request $request): array|RedirectResponse
    {
        $pending = $request->session()->get(self::TAKEOVER_PENDING_SESSION_KEY);

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No secure takeover session is currently pending.',
            ]);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $request->session()->forget(self::TAKEOVER_PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'auth' => 'Secure takeover session expired. Please sign in again.',
            ]);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0));
        $cooldownSeconds = $this->resendCooldownSecondsRemaining($user);

        return [
            'email' => (string) ($pending['email'] ?? ''),
            'resendLocked' => $cooldownSeconds > 0,
            'cooldownSeconds' => $cooldownSeconds,
        ];
    }

    public function sendTakeoverOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::TAKEOVER_PENDING_SESSION_KEY);

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No secure takeover session is currently pending.',
            ]);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0));

        if ($user === null || $user->email !== ($pending['email'] ?? null)) {
            $request->session()->forget(self::TAKEOVER_PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'auth' => 'Secure takeover session is no longer valid.',
            ]);
        }

        $cooldownSeconds = $this->resendCooldownSecondsRemaining($user);

        if ($cooldownSeconds > 0) {
            return redirect()->route('sk_official.takeover.wait')->withErrors([
                'auth' => "Please wait {$cooldownSeconds} seconds before requesting another code.",
            ]);
        }

        $otpCode = $this->generateOtpCode();
        $expiresAt = now()->addMinutes((int) config('sk_official_auth.single_session.otp_expiration_minutes', 5));

        $user->forceFill([
            'otp_code' => Hash::make($otpCode),
            'otp_expires_at' => $expiresAt,
            'otp_attempts' => 0,
            'otp_last_sent_at' => now(),
        ])->save();

        $user->notify(new SessionTakeoverOtpNotification($otpCode));
        $this->auditLogService->log(
            event: 'session_takeover_otp_sent',
            user: $user,
            request: $request,
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'session_takeover',
            resourceId: $user->getKey(),
        );

        return redirect()->route('sk_official.takeover.wait')->with('status', 'Verification code sent to your email.');
    }

    public function verifyTakeoverOtp(Request $request, string $otpCode): RedirectResponse
    {
        $pending = $request->session()->get(self::TAKEOVER_PENDING_SESSION_KEY);

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No secure takeover session is currently pending.',
            ]);
        }

        $userId = (int) ($pending['user_id'] ?? 0);
        $takeoverApproved = false;

        DB::transaction(function () use ($userId, $otpCode, $request, &$takeoverApproved): void {
            $user = User::query()->lockForUpdate()->find($userId);

            if ($user === null) {
                return;
            }

            $maxAttempts = (int) config('sk_official_auth.single_session.otp_max_attempts', 5);

            if ((int) ($user->otp_attempts ?? 0) >= $maxAttempts) {
                return;
            }

            if ($user->otp_expires_at === null || $user->otp_expires_at->isPast() || empty($user->otp_code)) {
                return;
            }

            if (! Hash::check($otpCode, (string) $user->otp_code)) {
                $user->forceFill([
                    'otp_attempts' => ((int) ($user->otp_attempts ?? 0)) + 1,
                ])->save();

                $this->auditLogService->log(
                    event: 'session_takeover_otp_invalid',
                    user: $user,
                    request: $request,
                    metadata: [
                        'otp_attempts' => (int) ($user->otp_attempts ?? 0),
                        'max_attempts' => $maxAttempts,
                    ],
                    outcome: AuthAuditLogService::OUTCOME_FAILED,
                    resourceType: 'session_takeover',
                    resourceId: $user->getKey(),
                );

                return;
            }

            $this->deleteSessionById((string) ($user->active_session_id ?? ''));

            $user->forceFill([
                'active_session_id' => null,
                'last_seen' => null,
                'otp_code' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_last_sent_at' => null,
            ])->save();

            $takeoverApproved = true;
        });

        if (! $takeoverApproved) {
            return redirect()->route('sk_official.takeover.wait')->withErrors([
                'auth' => 'Invalid or expired verification code. Request a new one and try again.',
            ]);
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            $request->session()->forget(self::TAKEOVER_PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'auth' => 'Unable to complete secure takeover.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->claimCurrentSession($user, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $this->auditLogService->log(
            event: 'session_takeover_approved',
            user: $user,
            request: $request,
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'session_takeover',
            resourceId: $user->getKey(),
        );
        $request->session()->forget(self::TAKEOVER_PENDING_SESSION_KEY);

        return redirect()->route('dashboard');
    }

    public function clearSessionOwnershipOnLogout(User $user, Request $request): void
    {
        $this->auditLogService->log(
            event: 'logout',
            user: $user,
            request: $request,
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'session',
            resourceId: $request->session()->getId(),
        );

        $currentSessionId = $request->session()->getId();
        $this->deleteSessionById($currentSessionId);

        if ((string) ($user->active_session_id ?? '') !== (string) $currentSessionId) {
            return;
        }

        $user->forceFill([
            'active_session_id' => null,
            'last_seen' => null,
            'active_device' => null,
            'last_ip' => null,
        ])->save();
    }

    public function isSessionActive(User $user): bool
    {
        if (empty($user->active_session_id) || $user->last_seen === null) {
            return false;
        }

        $timeoutSeconds = (int) config('sk_official_auth.single_session.heartbeat_timeout_seconds', 120);

        if ($user->last_seen->copy()->addSeconds($timeoutSeconds)->isPast()) {
            return false;
        }

        if ((string) config('session.driver') !== 'database') {
            return true;
        }

        return DB::table('sessions')
            ->where('id', (string) $user->active_session_id)
            ->where('user_id', $user->getKey())
            ->where('last_activity', '>=', now()->subSeconds($timeoutSeconds)->timestamp)
            ->exists();
    }

    protected function hasActiveSessionConflict(User $user, Request $request): bool
    {
        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '' || $activeSessionId === $request->session()->getId()) {
            return false;
        }

        if ($this->isSessionActive($user)) {
            return true;
        }

        $this->deleteSessionById($activeSessionId);
        $user->forceFill([
            'active_session_id' => null,
            'last_seen' => null,
            'active_device' => null,
            'last_ip' => null,
        ])->save();

        return false;
    }

    protected function startTakeoverWait(User $user, string $email, Request $request): void
    {
        $waitMinutes = (int) config('sk_official_auth.verification.wait_minutes', 15);

        $request->session()->put(self::TAKEOVER_PENDING_SESSION_KEY, [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'started_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes($waitMinutes)->toIso8601String(),
            'ip' => (string) $request->ip(),
            'user_agent' => $this->userAgent($request),
        ]);

        $this->loginSecurityService->recordAttempt($user, $email, false, $request, ['reason' => 'single_session_conflict']);
        $this->auditLogService->log(
            event: 'login_blocked_single_session_conflict',
            user: $user,
            request: $request,
            metadata: ['active_session_id' => $user->active_session_id],
            outcome: AuthAuditLogService::OUTCOME_BLOCKED,
            resourceType: 'authentication',
            resourceId: $user->getKey(),
        );
        $request->session()->flash('status', 'Account currently active on another device. Verify ownership to continue.');
        $request->session()->flash('takeover_wait', true);
    }

    protected function startEmailVerificationWait(
        User $user,
        string $email,
        Request $request,
        string $reason,
        string $message,
    ): void {
        $waitMinutes = (int) config('sk_official_auth.verification.wait_minutes', 15);
        $requiresFreshVerification = $reason === 'email_device_changed';

        $request->session()->put('sk_official_email_verification_pending', [
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
        $this->auditLogService->log(
            event: 'login_blocked_'.$reason,
            user: $user,
            request: $request,
            metadata: ['reason' => $reason],
            outcome: AuthAuditLogService::OUTCOME_BLOCKED,
            resourceType: 'authentication',
            resourceId: $user->getKey(),
        );
        $request->session()->flash('status', $message);
        $request->session()->flash('verification_wait', true);
    }

    protected function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function resendCooldownSecondsRemaining(?User $user): int
    {
        if ($user === null || $user->otp_last_sent_at === null) {
            return 0;
        }

        $cooldownSeconds = (int) config('sk_official_auth.single_session.otp_resend_cooldown_seconds', 60);
        $availableAt = $user->otp_last_sent_at->copy()->addSeconds($cooldownSeconds);

        return max(0, now()->diffInSeconds($availableAt, false));
    }

    protected function userAgent(Request $request): string
    {
        return substr((string) ($request->userAgent() ?? 'unknown'), 0, 255);
    }

    protected function deleteSessionById(string $sessionId): void
    {
        if ($sessionId === '' || (string) config('session.driver') !== 'database') {
            return;
        }

        DB::table('sessions')->where('id', $sessionId)->delete();
    }
}
