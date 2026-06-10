<?php

namespace App\Modules\Authentication\Services;

use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\Authentication\Notifications\AdminPasswordSetupNotification;
use App\Modules\Authentication\Support\PasswordHelper;
use App\Modules\Shared\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PasswordSetupService
{
    public function __construct(
        protected AuditLogInterface $auditService,
    ) {}

    public function sendSetupLink(User $user): void
    {
        $this->broker()->sendResetLink(
            ['email' => $user->email],
            function (CanResetPassword $notifiable, #[\SensitiveParameter] string $token): void {
                if ($notifiable instanceof User) {
                    $notifiable->notify(new AdminPasswordSetupNotification($token));
                }
            },
        );
    }

    public function hasValidToken(?User $user, string $email, string $token): bool
    {
        if ($email === '' || $token === '') {
            return false;
        }

        $resolvedUser = $user ?? $this->findUserByEmail(strtolower(trim($email)));

        if ($resolvedUser === null || ! $resolvedUser->isAdmin()) {
            return false;
        }

        return $this->broker()->tokenExists($resolvedUser, $token);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function completeSetup(Request $request, array $credentials): User
    {
        $email = (string) ($credentials['email'] ?? '');
        $token = (string) ($credentials['token'] ?? '');
        $password = (string) ($credentials['password'] ?? '');

        $user = $this->findUserByEmail(strtolower(trim($email)));

        if ($user === null || ! $user->isAdmin()) {
            $this->throwInvalidSetupLink();
        }

        if (! $this->broker()->tokenExists($user, $token)) {
            $this->throwInvalidSetupLink();
        }

        if (PasswordHelper::matches($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $user->forceFill([
            'password' => $password,
            'email_verified_at' => now(),
            'remember_token' => null,
        ])->save();

        $user->clearMustChangePassword();

        $this->broker()->deleteToken($user);
        $this->invalidateUserSessions($user);

        event(new PasswordReset($user));

        $this->auditService->logPasswordSetup($user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $user->recordLogin($request->ip());
        $this->auditService->logLoginSuccess($user);

        return $user;
    }

    protected function findUserByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    protected function broker(): PasswordBroker
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker(config('auth.defaults.passwords', 'users'));

        return $broker;
    }

    protected function invalidateUserSessions(User $user): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable) {
            // Best effort.
        }
    }

    protected function throwInvalidSetupLink(): never
    {
        throw ValidationException::withMessages([
            'token' => 'This password setup link is invalid or has expired.',
        ]);
    }
}
