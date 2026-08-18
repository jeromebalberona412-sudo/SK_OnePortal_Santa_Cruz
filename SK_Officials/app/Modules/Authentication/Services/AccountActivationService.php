<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\Notifications\SkOfficialAccountActivationNotification;
use Illuminate\Auth\Passwords\PasswordBroker as LaravelPasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountActivationService
{
    public const OUTCOME_SENT = 'sent';

    public const OUTCOME_INVALID = 'invalid';

    public const OUTCOME_ALREADY_ACTIVE = 'already_active';

    public const OUTCOME_THROTTLED = 'throttled';

    public const SESSION_EMAIL_KEY = 'account_activation_email';

    public const SENT_MESSAGE = 'Your activation link has been sent. Please check your inbox and spam/junk folder.';

    public const INVALID_MESSAGE = 'Invalid email or no email.';

    public const TOKEN_VALID = 'valid';

    public const TOKEN_EXPIRED = 'expired';

    public const TOKEN_INVALID = 'invalid';

    public const TOKEN_ALREADY_ACTIVE = 'already_active';

    public const ALREADY_ACTIVE_MESSAGE = 'This account has already been activated. Please sign in or use Forgot Password if you cannot access your account.';

    public const THROTTLED_MESSAGE = 'Please wait before requesting another activation email.';

    protected array $columnCache = [];

    public function __construct(
        protected TenantContextService $tenantContextService,
        protected AuthAuditLogService $auditLogService,
    ) {}

    public function expireMinutes(): int
    {
        return max(1, (int) config('sk_official_auth.account_activation.expire_minutes', 1440));
    }

    public function cooldownSeconds(): int
    {
        return max(1, (int) config('sk_official_auth.account_activation.cooldown_seconds', 60));
    }

    public function cooldownRemaining(string $email): int
    {
        $expiresAt = (int) Cache::get($this->cooldownCacheKey($this->emailHash(Str::lower(trim($email)))), 0);

        if ($expiresAt <= 0) {
            return 0;
        }

        return max(0, $expiresAt - time());
    }

    public function requestNewLink(Request $request, string $email): string
    {
        $normalizedEmail = Str::lower(trim($email));
        $emailHash = $this->emailHash($normalizedEmail);

        if ($this->isCoolingDown($emailHash)) {
            $this->auditLogService->log(
                event: 'account_activation_requested',
                user: null,
                request: $request,
                metadata: ['email_hash' => $emailHash, 'reason' => 'cooldown'],
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
                resourceType: 'account_activation',
                resourceId: $emailHash,
            );

            return self::OUTCOME_THROTTLED;
        }

        $lock = Cache::lock('sk-official-activation-send:'.$emailHash, 15);

        if (! $lock->get()) {
            return self::OUTCOME_THROTTLED;
        }

        try {
            $user = $this->findOfficialByEmail($normalizedEmail);

            $this->auditLogService->log(
                event: 'account_activation_requested',
                user: $user,
                request: $request,
                metadata: [
                    'email_hash' => $emailHash,
                    'eligible' => $user !== null && $this->isEligibleForActivation($user),
                    'already_active' => $user !== null && $this->isActivatedOfficial($user),
                ],
                outcome: AuthAuditLogService::OUTCOME_SUCCESS,
                resourceType: 'account_activation',
                resourceId: $user?->getKey() ?? $emailHash,
            );

            if ($user !== null && $this->isActivatedOfficial($user)) {
                return self::OUTCOME_ALREADY_ACTIVE;
            }

            if ($user === null || ! $this->isEligibleForActivation($user)) {
                return self::OUTCOME_INVALID;
            }

            $this->broker()->deleteToken($user);
            $token = $this->broker()->createToken($user);

            try {
                $user->notify(new SkOfficialAccountActivationNotification($token));
            } catch (\Throwable $exception) {
                report($exception);
            }

            $this->markCooldown($emailHash);

            return self::OUTCOME_SENT;
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{status: string, user: ?User}
     */
    public function inspectToken(string $email, string $token): array
    {
        $normalizedEmail = Str::lower(trim($email));
        $user = $this->findOfficialByEmail($normalizedEmail);

        if ($user === null || ! $this->isInScopeOfficial($user)) {
            return ['status' => self::TOKEN_INVALID, 'user' => null];
        }

        if ($this->isActivatedOfficial($user)) {
            return ['status' => self::TOKEN_ALREADY_ACTIVE, 'user' => $user];
        }

        if (! $this->isEligibleForActivation($user)) {
            return ['status' => self::TOKEN_INVALID, 'user' => $user];
        }

        if ($token === '' || ! $this->broker()->tokenExists($user, $token)) {
            return ['status' => $this->missingTokenStatus($normalizedEmail), 'user' => $user];
        }

        if ($this->activationWindowExpired($normalizedEmail)) {
            return ['status' => self::TOKEN_EXPIRED, 'user' => $user];
        }

        return ['status' => self::TOKEN_VALID, 'user' => $user];
    }

    public function isPendingOfficial(?User $user): bool
    {
        return $user !== null && $this->isEligibleForActivation($user);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function activate(Request $request, array $credentials): User
    {
        $normalizedEmail = Str::lower(trim((string) ($credentials['email'] ?? '')));
        $token = (string) ($credentials['token'] ?? '');
        $password = (string) ($credentials['password'] ?? '');
        $inspection = $this->inspectToken($normalizedEmail, $token);

        if ($inspection['status'] === self::TOKEN_ALREADY_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => self::ALREADY_ACTIVE_MESSAGE,
            ]);
        }

        if ($inspection['status'] === self::TOKEN_EXPIRED) {
            throw ValidationException::withMessages([
                'email' => 'expired',
            ]);
        }

        if ($inspection['status'] !== self::TOKEN_VALID || $inspection['user'] === null) {
            throw ValidationException::withMessages([
                'email' => 'invalid',
            ]);
        }

        $user = $inspection['user'];

        if (Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $userUpdates = [
            'password' => Hash::make($password),
            'remember_token' => null,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ];

        if ($this->usersTableHasColumn('active_session_id')) {
            $userUpdates['active_session_id'] = null;
        }

        $user->forceFill($userUpdates)->save();

        try {
            if ($this->usersTableHasColumn('must_change_password')) {
                $driver = (string) config('database.connections.'.config('database.default').'.driver');
                $value = $driver === 'pgsql' ? DB::raw("'false'::boolean") : false;
                User::query()
                    ->whereKey($user->getKey())
                    ->update(['must_change_password' => $value]);
            }
        } catch (\Throwable) {
            // Best-effort: activation still completes if this flag cannot be cleared.
        }

        $this->broker()->deleteToken($user);
        $this->invalidateUserSessions($user);

        $this->auditLogService->log(
            event: 'account_activation_completed',
            user: $user,
            request: $request,
            metadata: ['email_hash' => $this->emailHash($normalizedEmail)],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'account_activation',
            resourceId: $user->getKey(),
        );

        return $user->fresh() ?? $user;
    }

    protected function findOfficialByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    protected function isInScopeOfficial(User $user): bool
    {
        $tenantId = $this->tenantContextService->tenantId();

        if ($tenantId === null) {
            return false;
        }

        $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);

        if ($this->usersTableHasColumn('role') && ! $user->hasRole($requiredRole)) {
            return false;
        }

        if ($this->usersTableHasColumn('tenant_id') && (int) ($user->tenant_id ?? 0) !== $tenantId) {
            return false;
        }

        if (method_exists($user, 'trashed') && $user->trashed()) {
            return false;
        }

        return true;
    }

    protected function isActivatedOfficial(User $user): bool
    {
        if (! $this->isInScopeOfficial($user)) {
            return false;
        }

        if ($this->usersTableHasColumn('status') && (string) $user->status !== User::STATUS_ACTIVE) {
            return false;
        }

        return $user->email_verified_at !== null
            && (! $this->usersTableHasColumn('must_change_password') || ! (bool) $user->must_change_password);
    }

    protected function isEligibleForActivation(User $user): bool
    {
        if (! $this->isInScopeOfficial($user)) {
            return false;
        }

        if (! $this->usersTableHasColumn('status')) {
            return $user->email_verified_at === null;
        }

        $status = (string) $user->status;

        if (in_array($status, [User::STATUS_INACTIVE, User::STATUS_SUSPENDED], true)) {
            return false;
        }

        if ($status === User::STATUS_ACTIVE && $user->email_verified_at !== null
            && (! $this->usersTableHasColumn('must_change_password') || ! (bool) $user->must_change_password)) {
            return false;
        }

        return $status === User::STATUS_PENDING_APPROVAL
            || $user->email_verified_at === null
            || ($this->usersTableHasColumn('must_change_password') && (bool) $user->must_change_password);
    }

    protected function activationWindowExpired(string $normalizedEmail): bool
    {
        $record = $this->tokenRecord($normalizedEmail);

        if ($record === null || empty($record->created_at)) {
            return true;
        }

        try {
            return Carbon::parse((string) $record->created_at)
                ->addMinutes($this->expireMinutes())
                ->isPast();
        } catch (\Throwable) {
            return true;
        }
    }

    protected function missingTokenStatus(string $normalizedEmail): string
    {
        $record = $this->tokenRecord($normalizedEmail);

        if ($record === null) {
            return self::TOKEN_INVALID;
        }

        return $this->activationWindowExpired($normalizedEmail)
            ? self::TOKEN_EXPIRED
            : self::TOKEN_INVALID;
    }

    protected function tokenRecord(string $normalizedEmail): ?object
    {
        try {
            return DB::table($this->passwordResetTable())->where('email', $normalizedEmail)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function broker(): LaravelPasswordBroker
    {
        /** @var LaravelPasswordBroker $broker */
        $broker = Password::broker((string) config('fortify.passwords', config('auth.defaults.passwords', 'users')));

        return $broker;
    }

    protected function passwordResetTable(): string
    {
        $brokerName = (string) config('fortify.passwords', config('auth.defaults.passwords', 'users'));

        return (string) config('auth.passwords.'.$brokerName.'.table', 'password_reset_tokens');
    }

    protected function usersTableHasColumn(string $column): bool
    {
        return $this->tableHasColumn('users', $column);
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        $cacheKey = $table.'.'.$column;

        if (array_key_exists($cacheKey, $this->columnCache)) {
            return $this->columnCache[$cacheKey];
        }

        try {
            return $this->columnCache[$cacheKey] = Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return $this->columnCache[$cacheKey] = false;
        }
    }

    protected function emailHash(string $email): string
    {
        return hash('sha256', $email);
    }

    protected function cooldownCacheKey(string $emailHash): string
    {
        return 'sk-official-activation-cooldown:'.$emailHash;
    }

    protected function isCoolingDown(string $emailHash): bool
    {
        return Cache::has($this->cooldownCacheKey($emailHash));
    }

    protected function markCooldown(string $emailHash): void
    {
        $seconds = $this->cooldownSeconds();
        Cache::put($this->cooldownCacheKey($emailHash), time() + $seconds, $seconds);
    }

    protected function invalidateUserSessions(User $user): void
    {
        if (! Schema::hasTable('sessions') || ! $this->tableHasColumn('sessions', 'user_id')) {
            return;
        }

        try {
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
        } catch (\Throwable) {
            // Session invalidation is best effort if the runtime session schema differs.
        }
    }
}
