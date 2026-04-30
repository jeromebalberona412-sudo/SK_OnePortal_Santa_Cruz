<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthenticationService
{
    public function __construct(
        protected TenantContextService $tenantContextService,
        protected LoginSecurityService $loginSecurityService,
        protected SuspiciousLoginService $suspiciousLoginService,
        protected AuthAuditLogService $auditLogService,
        protected FeatureFlagService $featureFlagService,
        protected EmailVerificationDeviceService $emailVerificationDeviceService,
        protected TrustedDeviceService $trustedDeviceService,
        protected DeviceFingerprintService $deviceFingerprintService,
    ) {}

    /**
     * Attempt to authenticate a user from a Fortify login request.
     * Returns the User on success, or null on failure.
     */
    public function authenticate(Request $request): ?User
    {
        $email    = Str::lower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');

        $tenantId = $this->tenantContextService->tenantId();

        $user = User::query()
            ->where('email', $email)
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->first();

        // Record failed attempt and apply lockout on bad credentials
        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            $this->loginSecurityService->recordAttempt($user, $email, false, $request);
            $this->loginSecurityService->applyFailureLockout($user, $email, $request);

            $this->auditLogService->log(
                event: 'login_failed',
                user: $user,
                request: $request,
                metadata: ['reason' => $user === null ? 'unknown_email' : 'bad_password', 'email' => $email],
                outcome: AuthAuditLogService::OUTCOME_FAILED,
                resourceType: 'auth',
                resourceId: $email,
            );

            return null;
        }

        // Role check
        $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);
        if (! $user->hasRole($requiredRole)) {
            $this->auditLogService->log(
                event: 'login_failed',
                user: $user,
                request: $request,
                metadata: ['reason' => 'wrong_role'],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            return null;
        }

        // Lockout check
        if ($this->loginSecurityService->isLocked($user)) {
            $this->auditLogService->log(
                event: 'login_failed',
                user: $user,
                request: $request,
                metadata: ['reason' => 'account_locked'],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            return null;
        }

        // Single-session takeover check
        if ($this->hasActiveSession($user, $request)) {
            $this->storeTakeoverPending($user, $request);

            $this->auditLogService->log(
                event: 'login_takeover_initiated',
                user: $user,
                request: $request,
                metadata: [],
                outcome: AuthAuditLogService::OUTCOME_SUCCESS,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            // Redirect to takeover flow — Fortify will receive null and throw ValidationException,
            // but we redirect before that via a session flag checked in the login view.
            // Store credentials in session so the takeover flow can complete login.
            $request->session()->put('sk_official_takeover_pending', [
                'user_id'    => $user->getKey(),
                'email'      => $user->email,
                'started_at' => now()->toIso8601String(),
            ]);

            // Signal to Fortify's authenticateUsing callback to redirect instead of failing
            $request->session()->put('sk_official_redirect_takeover', true);

            return null;
        }

        // Email verification check
        if ($this->featureFlagService->deviceVerificationEnabled() && ! $user->hasVerifiedEmail()) {
            $this->emailVerificationDeviceService->storePendingVerification($user, $request);

            $this->auditLogService->log(
                event: 'login_email_verification_required',
                user: $user,
                request: $request,
                metadata: [],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            return null;
        }

        // Successful login
        $this->loginSecurityService->recordAttempt($user, $email, true, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $user->recordLogin((string) $request->ip());

        $this->assignSessionOwnership($user, $request);

        $this->auditLogService->log(
            event: 'login_success',
            user: $user,
            request: $request,
            metadata: [],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
        );

        return $user;
    }

    /**
     * Complete login after email verification is confirmed.
     *
     * @param  array<string, mixed>  $pending
     */
    public function completeEmailVerificationLogin(User $user, Request $request, array $pending): void
    {
        Auth::login($user);
        $request->session()->regenerate();

        $this->loginSecurityService->recordAttempt($user, (string) $user->email, true, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $user->recordLogin((string) $request->ip());

        $this->assignSessionOwnership($user, $request);

        $this->auditLogService->log(
            event: 'login_success',
            user: $user,
            request: $request,
            metadata: ['via' => 'email_verification'],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
        );
    }

    /**
     * Return data needed for the takeover-wait view, or a RedirectResponse if session is invalid.
     *
     * @return array<string, mixed>|RedirectResponse
     */
    public function showTakeoverWaitData(Request $request): array|RedirectResponse
    {
        $pending = $request->session()->get('sk_official_takeover_pending');

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'verification' => 'No takeover session is currently pending.',
            ]);
        }

        $cooldownSeconds = 0;
        $resendLocked    = false;

        $userId = (int) ($pending['user_id'] ?? 0);
        $user   = User::query()->find($userId);

        if ($user !== null && $this->hasColumn('users', 'otp_last_sent_at') && $user->otp_last_sent_at !== null) {
            $cooldown = (int) config('sk_official_auth.single_session.otp_resend_cooldown_seconds', 60);
            $elapsed  = now()->diffInSeconds($user->otp_last_sent_at, false);

            if ($elapsed < $cooldown) {
                $resendLocked    = true;
                $cooldownSeconds = (int) ($cooldown - $elapsed);
            }
        }

        return [
            'email'           => (string) ($pending['email'] ?? ''),
            'resendLocked'    => $resendLocked,
            'cooldownSeconds' => $cooldownSeconds,
        ];
    }

    /**
     * Send a takeover OTP to the user's email.
     */
    public function sendTakeoverOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('sk_official_takeover_pending');

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'verification' => 'No takeover session is currently pending.',
            ]);
        }

        $userId = (int) ($pending['user_id'] ?? 0);
        $user   = User::query()->find($userId);

        if ($user === null) {
            return redirect()->route('login')->withErrors([
                'verification' => 'User not found for this takeover session.',
            ]);
        }

        $cooldown = (int) config('sk_official_auth.single_session.otp_resend_cooldown_seconds', 60);

        if ($this->hasColumn('users', 'otp_last_sent_at') && $user->otp_last_sent_at !== null) {
            $elapsed = now()->diffInSeconds($user->otp_last_sent_at, false);

            if ($elapsed < $cooldown) {
                return back()->withErrors([
                    'otp' => 'Please wait before requesting another OTP.',
                ]);
            }
        }

        $otp        = (string) random_int(100000, 999999);
        $expiration = (int) config('sk_official_auth.single_session.otp_expiration_minutes', 5);

        $updates = [];

        if ($this->hasColumn('users', 'otp_code')) {
            $updates['otp_code'] = bcrypt($otp);
        }
        if ($this->hasColumn('users', 'otp_expires_at')) {
            $updates['otp_expires_at'] = now()->addMinutes($expiration);
        }
        if ($this->hasColumn('users', 'otp_attempts')) {
            $updates['otp_attempts'] = 0;
        }
        if ($this->hasColumn('users', 'otp_last_sent_at')) {
            $updates['otp_last_sent_at'] = now();
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }

        // Send OTP via notification
        $user->notify(new \App\Modules\Authentication\Notifications\TakeoverOtpNotification($otp));

        $this->auditLogService->log(
            event: 'takeover_otp_sent',
            user: $user,
            request: $request,
            metadata: [],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
        );

        return back()->with('status', 'OTP sent to your email.');
    }

    /**
     * Verify the takeover OTP and complete the session takeover login.
     */
    public function verifyTakeoverOtp(Request $request, string $otpCode): RedirectResponse
    {
        $pending = $request->session()->get('sk_official_takeover_pending');

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'otp_code' => 'No takeover session is currently pending.',
            ]);
        }

        $userId = (int) ($pending['user_id'] ?? 0);
        $user   = User::query()->find($userId);

        if ($user === null) {
            return redirect()->route('login')->withErrors([
                'otp_code' => 'User not found for this takeover session.',
            ]);
        }

        $maxAttempts = (int) config('sk_official_auth.single_session.otp_max_attempts', 5);

        if ($this->hasColumn('users', 'otp_attempts') && (int) $user->otp_attempts >= $maxAttempts) {
            $request->session()->forget('sk_official_takeover_pending');

            return redirect()->route('login')->withErrors([
                'otp_code' => 'Too many failed OTP attempts. Please log in again.',
            ]);
        }

        // Check expiry
        if ($this->hasColumn('users', 'otp_expires_at') && ($user->otp_expires_at === null || $user->otp_expires_at->isPast())) {
            return back()->withErrors([
                'otp_code' => 'The OTP has expired. Please request a new one.',
            ]);
        }

        // Verify OTP
        $storedHash = $this->hasColumn('users', 'otp_code') ? (string) $user->otp_code : '';

        if ($storedHash === '' || ! Hash::check($otpCode, $storedHash)) {
            if ($this->hasColumn('users', 'otp_attempts')) {
                $user->forceFill(['otp_attempts' => ((int) $user->otp_attempts) + 1])->save();
            }

            $this->auditLogService->log(
                event: 'takeover_otp_failed',
                user: $user,
                request: $request,
                metadata: ['reason' => 'invalid_otp'],
                outcome: AuthAuditLogService::OUTCOME_FAILED,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            return back()->withErrors([
                'otp_code' => 'Invalid OTP. Please try again.',
            ]);
        }

        // OTP valid — clear OTP fields and complete login
        $clearUpdates = [];
        foreach (['otp_code', 'otp_expires_at', 'otp_attempts', 'otp_last_sent_at'] as $col) {
            if ($this->hasColumn('users', $col)) {
                $clearUpdates[$col] = null;
            }
        }
        if ($clearUpdates !== []) {
            $user->forceFill($clearUpdates)->save();
        }

        $request->session()->forget('sk_official_takeover_pending');

        // Invalidate the previous session owner
        $this->invalidatePreviousSession($user);

        Auth::login($user);
        $request->session()->regenerate();

        $this->loginSecurityService->recordAttempt($user, (string) $user->email, true, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $user->recordLogin((string) $request->ip());

        $this->assignSessionOwnership($user, $request);

        $this->auditLogService->log(
            event: 'login_success',
            user: $user,
            request: $request,
            metadata: ['via' => 'takeover_otp'],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Record a heartbeat for the current session owner.
     */
    public function recordHeartbeat(Request $request): void
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return;
        }

        if ($this->hasColumn('users', 'last_seen')) {
            $user->forceFill(['last_seen' => now()])->save();
        }
    }

    /**
     * Clear session ownership when a user logs out.
     */
    public function clearSessionOwnershipOnLogout(User $user, Request $request): void
    {
        if ($this->hasColumn('users', 'active_session_id')) {
            $currentSessionId = $request->session()->getId();

            // Only clear if this session is the active owner
            if ((string) $user->active_session_id === $currentSessionId) {
                $user->forceFill(['active_session_id' => null])->save();
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    protected function hasActiveSession(User $user, Request $request): bool
    {
        if (! $this->hasColumn('users', 'active_session_id')) {
            return false;
        }

        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '') {
            return false;
        }

        // Same session — not a conflict
        if ($activeSessionId === $request->session()->getId()) {
            return false;
        }

        // Check if the session is still alive (heartbeat within timeout)
        $timeoutSeconds = (int) config('sk_official_auth.single_session.heartbeat_timeout_seconds', 120);

        if ($this->hasColumn('users', 'last_seen') && $user->last_seen !== null) {
            return $user->last_seen->diffInSeconds(now()) <= $timeoutSeconds;
        }

        // If no last_seen column, treat any active_session_id as a live session
        return true;
    }

    protected function storeTakeoverPending(User $user, Request $request): void
    {
        $request->session()->put('sk_official_takeover_pending', [
            'user_id'    => $user->getKey(),
            'email'      => $user->email,
            'started_at' => now()->toIso8601String(),
        ]);
    }

    protected function assignSessionOwnership(User $user, Request $request): void
    {
        if (! $this->hasColumn('users', 'active_session_id')) {
            return;
        }

        $user->forceFill(['active_session_id' => $request->session()->getId()])->save();
    }

    protected function invalidatePreviousSession(User $user): void
    {
        if (! $this->hasColumn('users', 'active_session_id')) {
            return;
        }

        $previousSessionId = (string) ($user->active_session_id ?? '');

        if ($previousSessionId === '' || ! Schema::hasTable('sessions')) {
            return;
        }

        try {
            DB::table('sessions')->where('id', $previousSessionId)->delete();
        } catch (\Throwable) {
            // Best effort
        }
    }

    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
