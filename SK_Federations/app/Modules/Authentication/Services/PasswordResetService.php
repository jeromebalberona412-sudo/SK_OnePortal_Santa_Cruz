<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Shared\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker as LaravelPasswordBroker;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * @var array<string, bool>
     */
    protected array $columnCache = [];

    public function __construct(
        protected TenantContextService $tenantContextService,
        protected AuthAuditLogService $auditLogService,
    ) {}

    public function sendResetLink(Request $request, string $email): void
    {
        $normalizedEmail = Str::lower(trim($email));
        $user = $this->findUserByEmail($normalizedEmail);
        $emailHash = $this->emailHash($normalizedEmail);

        $isResettable = $user !== null && $this->isResettableUser($user);

        $this->auditLogService->log(
            event: 'password_reset_requested',
            user: $user,
            request: $request,
            metadata: [
                'email' => $normalizedEmail,
                'email_hash' => $emailHash,
                'is_resettable' => $isResettable,
            ],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'password_reset',
            resourceId: $user?->getKey() ?? $emailHash,
        );

        if ($user === null) {
            return;
        }

        if (! $isResettable) {
            $this->logResetFailure(
                request: $request,
                user: $user,
                normalizedEmail: $normalizedEmail,
                reason: 'blocked_scope',
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
            );

            return;
        }

        $status = $this->broker()->sendResetLink(
            ['email' => $normalizedEmail],
            function (CanResetPasswordContract $notifiable, #[\SensitiveParameter] string $token): void {
                if ($notifiable instanceof User) {
                    $notifiable->sendPasswordResetNotification($token);
                }
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            $reason = $status === Password::RESET_THROTTLED ? 'throttled' : 'broker_'.$status;
            $outcome = $status === Password::RESET_THROTTLED
                ? AuthAuditLogService::OUTCOME_BLOCKED
                : AuthAuditLogService::OUTCOME_FAILED;

            $this->logResetFailure(
                request: $request,
                user: $user,
                normalizedEmail: $normalizedEmail,
                reason: $reason,
                outcome: $outcome,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function resetPassword(Request $request, array $credentials): void
    {
        $normalizedEmail = Str::lower(trim((string) ($credentials['email'] ?? '')));
        $token = (string) ($credentials['token'] ?? '');
        $password = (string) ($credentials['password'] ?? '');
        $user = $this->findUserByEmail($normalizedEmail);

        if ($user === null) {
            $this->logResetFailure(
                request: $request,
                user: null,
                normalizedEmail: $normalizedEmail,
                reason: 'unknown_email',
            );

            $this->throwInvalidResetLink();
        }

        if (! $this->isResettableUser($user)) {
            $this->logResetFailure(
                request: $request,
                user: $user,
                normalizedEmail: $normalizedEmail,
                reason: 'blocked_scope',
                outcome: AuthAuditLogService::OUTCOME_BLOCKED,
            );

            $this->throwInvalidResetLink();
        }

        if (! $this->broker()->tokenExists($user, $token)) {
            $this->logResetFailure(
                request: $request,
                user: $user,
                normalizedEmail: $normalizedEmail,
                reason: $this->resolveInvalidTokenReason($normalizedEmail),
            );

            $this->throwInvalidResetLink();
        }

        if (Hash::check($password, (string) $user->password)) {
            $this->logResetFailure(
                request: $request,
                user: $user,
                normalizedEmail: $normalizedEmail,
                reason: 'password_reuse',
            );

            throw ValidationException::withMessages([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $userUpdates = [
            'password' => Hash::make($password),
            'remember_token' => null,
        ];

        if ($this->usersTableHasColumn('active_session_id')) {
            $userUpdates['active_session_id'] = null;
        }

        $user->forceFill($userUpdates)->save();

        // Clear the "must_change_password" flag so the user can sign in
        // normally after completing the reset. Use a raw boolean cast
        // for PostgreSQL to avoid datatype mismatch errors.
        try {
            if ($this->usersTableHasColumn('must_change_password')) {
                \App\Modules\Shared\Models\User::query()
                    ->whereKey($user->getKey())
                    ->update(['must_change_password' => \DB::raw("'false'::boolean")]);
            }
        } catch (\Throwable $e) {
            // Best-effort: continue even if clearing the flag fails.
        }

        $this->broker()->deleteToken($user);
        $this->invalidateUserSessions($user);

        event(new PasswordReset($user));

        $this->auditLogService->log(
            event: 'password_reset_completed',
            user: $user,
            request: $request,
            metadata: [
                'email' => $normalizedEmail,
                'email_hash' => $this->emailHash($normalizedEmail),
            ],
            outcome: AuthAuditLogService::OUTCOME_SUCCESS,
            resourceType: 'password_reset',
            resourceId: $user->getKey(),
        );
    }

    protected function findUserByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    protected function isResettableUser(User $user): bool
    {
        $tenantId = $this->tenantContextService->tenantId();

        if ($tenantId === null) {
            return false;
        }

        $requiredRole = (string) config('sk_fed_auth.required_role', User::ROLE_SK_FED);

        if ($this->usersTableHasColumn('role') && ! $user->hasRole($requiredRole)) {
            return false;
        }

        if ($this->usersTableHasColumn('tenant_id') && (int) ($user->tenant_id ?? 0) !== $tenantId) {
            return false;
        }

        return true;
    }

    protected function broker(): LaravelPasswordBroker
    {
        /** @var LaravelPasswordBroker $broker */
        $broker = Password::broker((string) config('fortify.passwords', config('auth.defaults.passwords', 'users')));

        return $broker;
    }

    protected function usersTableHasColumn(string $column): bool
    {
        return $this->tableHasColumn('users', $column);
    }

    protected function emailHash(string $email): string
    {
        return hash('sha256', $email);
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

    protected function passwordResetTable(): string
    {
        $brokerName = (string) config('fortify.passwords', config('auth.defaults.passwords', 'users'));

        return (string) config('auth.passwords.'.$brokerName.'.table', 'password_reset_tokens');
    }

    protected function resolveInvalidTokenReason(string $normalizedEmail): string
    {
        $table = $this->passwordResetTable();

        try {
            $record = DB::table($table)
                ->where('email', $normalizedEmail)
                ->first();
        } catch (\Throwable) {
            return 'invalid_token';
        }

        if ($record === null) {
            return 'invalid_token';
        }

        $createdAt = (string) ($record->created_at ?? '');

        if ($createdAt === '') {
            return 'invalid_token';
        }

        try {
            $expiresAt = Carbon::parse($createdAt)
                ->addMinutes((int) config('auth.passwords.'.(string) config('fortify.passwords', config('auth.defaults.passwords', 'users')).'.expire', 60));
        } catch (\Throwable) {
            return 'invalid_token';
        }

        return $expiresAt->isPast() ? 'expired_token' : 'invalid_token';
    }

    protected function invalidateUserSessions(User $user): void
    {
        if (! Schema::hasTable('sessions') || ! $this->tableHasColumn('sessions', 'user_id')) {
            return;
        }

        try {
            DB::table('sessions')
                ->where('user_id', $user->getKey())
                ->delete();
        } catch (\Throwable) {
            // Session invalidation is best effort if the runtime session schema differs.
        }
    }

    protected function logResetFailure(
        Request $request,
        ?User $user,
        string $normalizedEmail,
        string $reason,
        string $outcome = AuthAuditLogService::OUTCOME_FAILED,
    ): void {
        $emailHash = $this->emailHash($normalizedEmail);

        $this->auditLogService->log(
            event: 'password_reset_failed',
            user: $user,
            request: $request,
            metadata: [
                'reason' => $reason,
                'email' => $normalizedEmail,
                'email_hash' => $emailHash,
            ],
            outcome: $outcome,
            resourceType: 'password_reset',
            resourceId: $user?->getKey() ?? $emailHash,
        );
    }

    protected function throwInvalidResetLink(): never
    {
        throw ValidationException::withMessages([
            'email' => 'This password reset link is invalid or has expired.',
        ]);
    }
}