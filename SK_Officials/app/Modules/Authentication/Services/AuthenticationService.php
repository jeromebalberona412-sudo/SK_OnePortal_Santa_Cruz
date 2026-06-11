<?php

namespace App\Modules\Authentication\Services;

use App\Models\OfficialTerm;
use App\Models\User;
use App\Services\SkOfficialPresenceService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        protected SkOfficialPresenceService $presenceService,
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

        if ($this->hasEndedTerm($user)) {
            $this->auditLogService->log(
                event: 'login_blocked_term_ended',
                user: $user,
                request: $request,
                metadata: ['reason' => 'term_ended'],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'auth',
                resourceId: $user->getKey(),
            );

            throw new HttpResponseException(
                redirect()->route('login')->with('access_denied', [
                    'title' => 'Access Denied',
                    'message' => 'Your SK official term has already ended. Login access is no longer available for this account.',
                ])
            );
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

        $rememberDevice = $request->boolean('remember');
        $isTrustedDevice = $this->trustedDeviceService->isTrusted($user, $request);

        if (! $user->hasVerifiedEmail()) {
            $this->startEmailVerificationWait(
                user: $user,
                email: $email,
                request: $request,
                reason: 'email_unverified',
                message: 'A verification email has been sent. Complete verification to continue.',
                rememberDevice: $rememberDevice,
            );

            return null;
        }

        if ($this->shouldRequireEmailVerification($user, $request, $isTrustedDevice)) {
            $this->startEmailVerificationWait(
                user: $user,
                email: $email,
                request: $request,
                reason: 'email_device_changed',
                message: 'New device detected. Email verification is required again for security.',
                rememberDevice: $rememberDevice,
            );

            return null;
        }

        $this->resolveActiveSessionConflict($user, $request);

        if ($isTrustedDevice) {
            $this->trustedDeviceService->refreshRememberCookieIfPresent($user, $request);
        } elseif ($rememberDevice) {
            $this->trustedDeviceService->rememberDevice($user, $request);
        }

        $this->loginSecurityService->recordAttempt($user, $email, true, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $user->recordLogin((string) $request->ip());

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
        $rememberDevice = ! empty($pending['remember_device']);

        Auth::login($user, $rememberDevice);

        $this->loginSecurityService->recordAttempt($user, (string) $user->email, true, $request);
        $this->loginSecurityService->clearAfterSuccess($user);
        $user->recordLogin((string) $request->ip());

        $this->claimCurrentSession($user, $request);

        try {
            $this->trustedDeviceService->trust($user, $request);

            if ($rememberDevice) {
                $this->trustedDeviceService->rememberDevice(
                    $user,
                    $request,
                    (string) ($pending['fingerprint'] ?? ''),
                );
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->auditLogService->log(
            event: 'login_success',
            user: $user,
            request: $request,
            metadata: [
                'via' => 'email_verification',
                'remember_device' => ! empty($pending['remember_device']),
            ],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
        );
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    public function verificationSessionKey(array $pending): string
    {
        return sha1((string) ($pending['started_at'] ?? '').'|'.(string) ($pending['email'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    public function storeVerificationWatch(array $pending): void
    {
        $sessionKey = $this->verificationSessionKey($pending);
        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->addHour()->toIso8601String()));

        Cache::put(
            $this->verificationWatchCacheKey($sessionKey),
            $pending,
            $expiresAt,
        );

        Cache::put(
            $this->verificationWatchUserCacheKey((int) ($pending['user_id'] ?? 0)),
            $pending,
            $expiresAt,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function retrieveVerificationWatch(string $sessionKey): ?array
    {
        if ($sessionKey === '') {
            return null;
        }

        $pending = Cache::get($this->verificationWatchCacheKey($sessionKey));

        return is_array($pending) ? $pending : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function retrieveVerificationWatchByUserId(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $pending = Cache::get($this->verificationWatchUserCacheKey($userId));

        return is_array($pending) ? $pending : null;
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    public function clearVerificationWatch(array $pending): void
    {
        Cache::forget($this->verificationWatchCacheKey($this->verificationSessionKey($pending)));
        Cache::forget($this->verificationWatchUserCacheKey((int) ($pending['user_id'] ?? 0)));
    }

    protected function verificationWatchUserCacheKey(int $userId): string
    {
        return 'sk_official_verify_user:'.$userId;
    }

    protected function verificationWatchCacheKey(string $sessionKey): string
    {
        return 'sk_official_verify_watch:'.$sessionKey;
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

        $currentSessionId = $request->session()->getId();
        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '') {
            $this->claimCurrentSession($user, $request);

            return;
        }

        if ($activeSessionId !== $currentSessionId) {
            return;
        }

        $this->presenceService->syncStaleOfflineStatuses();
        $this->presenceService->markOnline($user);
    }

    public function claimCurrentSession(User $user, Request $request): void
    {
        if (! $this->hasColumn('users', 'active_session_id')) {
            return;
        }

        $updates = [
            'active_session_id' => $request->session()->getId(),
        ];

        if ($this->hasColumn('users', 'last_seen')) {
            $updates['last_seen'] = now();
        }

        if ($this->hasColumn('users', 'active_device')) {
            $updates['active_device'] = $this->userAgent($request);
        }

        if ($this->hasColumn('users', 'last_ip')) {
            $updates['last_ip'] = (string) $request->ip();
        }

        $user->forceFill($updates)->save();
        $this->presenceService->markOnline($user);
    }

    public function isSessionActive(User $user): bool
    {
        if (empty($user->active_session_id) || $user->last_seen === null) {
            return false;
        }

        if (! $this->activeSessionExists((string) $user->active_session_id)) {
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

    public function activeSessionExists(string $sessionId): bool
    {
        if ($sessionId === '' || ! Schema::hasTable('sessions')) {
            return false;
        }

        return DB::table('sessions')->where('id', $sessionId)->exists();
    }

    public function shouldReclaimSessionForSameDevice(User $user, Request $request): bool
    {
        return $this->trustedDeviceService->isTrusted($user->fresh(), $request);
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

        $this->presenceService->markOffline($user);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    protected function resolveActiveSessionConflict(User $user, Request $request): void
    {
        if (! $this->hasColumn('users', 'active_session_id')) {
            return;
        }

        $activeSessionId = (string) ($user->active_session_id ?? '');

        if ($activeSessionId === '' || $activeSessionId === $request->session()->getId()) {
            return;
        }

        if (! $this->isSessionActive($user)) {
            $this->clearStaleSessionOwnership($user);

            return;
        }

        $this->invalidatePreviousSession($user);
        $this->clearStaleSessionOwnership($user);

        $this->auditLogService->log(
            event: 'login_replaced_active_session',
            user: $user,
            request: $request,
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'auth',
            resourceId: $user->getKey(),
            metadata: [
                'previous_session_id' => $activeSessionId,
            ],
        );
    }

    protected function clearStaleSessionOwnership(User $user): void
    {
        $user->forceFill([
            'active_session_id' => null,
            'last_seen' => null,
            'active_device' => null,
            'last_ip' => null,
        ])->save();
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

    protected function shouldRequireEmailVerification(User $user, Request $request, bool $isTrustedDevice): bool
    {
        if ($isTrustedDevice) {
            return false;
        }

        return $this->featureFlagService->deviceVerificationEnabled();
    }

    protected function startEmailVerificationWait(
        User $user,
        string $email,
        Request $request,
        string $reason,
        string $message,
        bool $rememberDevice = false,
    ): void {
        $waitMinutes = (int) config('sk_official_auth.verification.wait_minutes', 15);

        $sentAt = Carbon::now();

        $pending = [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'started_at' => $sentAt->toIso8601String(),
            'expires_at' => $sentAt->copy()->addMinutes($waitMinutes)->toIso8601String(),
            'fingerprint' => $this->deviceFingerprintService->fingerprint($request),
            'ip' => (string) $request->ip(),
            'user_agent' => $this->userAgent($request),
            'requires_fresh_verification' => $reason === 'email_device_changed',
            'verified_at_snapshot' => $user->email_verified_at?->toIso8601String(),
            'remember_device' => $rememberDevice,
        ];

        $request->session()->put('sk_official_email_verification_pending', $pending);
        $this->storeVerificationWatch($pending);

        $user->sendEmailVerificationNotification();
        $this->loginSecurityService->recordAttempt($user, $email, false, $request);
        $this->auditLogService->log(
            event: 'login_blocked_'.$reason,
            user: $user,
            request: $request,
            outcome: AuthAuditLogService::OUTCOME_BLOCKED,
            resourceType: 'auth',
            resourceId: $user->getKey(),
            metadata: [
                'reason' => $reason,
            ],
        );
        $request->session()->flash('status', $message);
        $request->session()->flash('verification_wait', true);
    }

    protected function userAgent(Request $request): string
    {
        return substr((string) ($request->userAgent() ?? 'unknown'), 0, 255);
    }

    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function hasEndedTerm(User $user): bool
    {
        $user->loadMissing('officialProfile.terms');

        $activeTerm = $user->officialProfile?->terms()
            ->where('status', OfficialTerm::STATUS_ACTIVE)
            ->orderByDesc('term_end')
            ->first();

        if ($activeTerm !== null) {
            return $activeTerm->term_end->lte(now()->startOfDay());
        }

        $latestTerm = $user->officialProfile?->terms()
            ->orderByDesc('term_end')
            ->first();

        return $latestTerm !== null
            && $latestTerm->term_end->lte(now()->startOfDay());
    }
}
