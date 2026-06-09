<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\EmailVerificationDeviceService;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Authentication\Services\TenantContextService;
use App\Modules\Profile\Services\PasswordChangeService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
        protected TenantContextService $tenantContextService,
        protected PasswordResetService $passwordResetService,
        protected PasswordChangeService $passwordChangeService,
        protected EmailVerificationDeviceService $emailVerificationDeviceService,
    ) {}

    public function showLogin(): View
    {
        return view('authentication::login');
    }

    public function showVerifyNotice(Request $request): View
    {
        return view('authentication::verify-notice', [
            'email' => $request->query('email'),
        ]);
    }

    public function showVerificationWait(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $this->clearVerificationSession($request);

            return redirect()->intended(route('dashboard'));
        }

        $pending = $this->resolveVerificationPending($request);

        if ($pending === null) {
            return redirect()->route('login')->withErrors([
                'verification' => 'No verification session is currently pending.',
            ]);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $this->clearVerificationSession($request, $pending);

            return redirect()->route('login')->withErrors([
                'verification' => 'Verification session expired. Please sign in again.',
            ]);
        }

        if ($this->canCompleteVerifiedLogin($pending)) {
            $user = User::query()->find((int) ($pending['user_id'] ?? 0))?->fresh();

            if ($user !== null) {
                $this->authenticationService->completeEmailVerificationLogin($user, $request, $pending);
                $this->clearVerificationSession($request, $pending);

                return redirect()->intended(route('dashboard'));
            }
        }

        $showNotification = (bool) session('resend_started');

        if (! $showNotification && ! ($pending['device_notified'] ?? false)) {
            $showNotification = true;
            $pending['device_notified'] = true;
            $request->session()->put('sk_official_email_verification_pending', $pending);
        }

        $resendStarted = (bool) session('resend_started');

        return view('authentication::verify-wait', [
            'email' => (string) ($pending['email'] ?? ''),
            'userId' => (int) ($pending['user_id'] ?? 0),
            'sessionKey' => sha1((string) ($pending['started_at'] ?? '').'|'.(string) ($pending['email'] ?? '')),
            'resendCooldown' => $resendStarted
                ? $this->emailVerificationDeviceService->resendCooldownRemaining($pending)
                : 0,
            'resendStarted' => $resendStarted,
            'showNotification' => $showNotification,
            'notificationBody' => $resendStarted
                ? 'Verification email resent. Check your inbox.'
                : 'Verification email sent. Check your inbox.',
        ]);
    }

    public function checkVerificationStatus(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $this->clearVerificationSession($request);

            return response()->json([
                'state' => 'verified',
                'redirect' => route('dashboard'),
            ]);
        }

        $pending = $this->resolveVerificationPending($request);

        if ($pending === null) {
            return response()->json([
                'state' => 'pending',
                'message' => 'Waiting for email verification...',
            ]);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $this->clearVerificationSession($request, $pending);

            return response()->json([
                'state' => 'expired',
                'message' => 'Verification session expired.',
            ], 410);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0))?->fresh();

        if ($user === null) {
            $this->clearVerificationSession($request, $pending);

            return response()->json([
                'state' => 'expired',
                'message' => 'User not found for this verification session.',
            ], 404);
        }

        if (! $this->canCompleteVerifiedLogin($pending, $user)) {
            return response()->json([
                'state' => 'pending',
                'expires_at' => $expiresAt->toIso8601String(),
                'seconds_remaining' => max(0, now()->diffInSeconds($expiresAt, false)),
                'resend_cooldown' => $this->emailVerificationDeviceService->resendCooldownRemaining($pending),
            ]);
        }

        $this->authenticationService->completeEmailVerificationLogin($user, $request, $pending);
        $this->clearVerificationSession($request, $pending);

        return response()->json([
            'state' => 'verified',
            'redirect' => route('dashboard'),
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $this->authenticationService->recordHeartbeat($request);

        return response()->json([
            'ok' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function resendVerification(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'session_key' => ['nullable', 'string', 'max:64'],
        ]);

        if ($request->filled('session_key')) {
            $request->query->set('session_key', (string) $validated['session_key']);
        }

        $pending = $this->resolveVerificationPending($request);

        if ($pending === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No verification session is currently pending.',
                ], 404);
            }

            return redirect()->route('login')->withErrors([
                'verification' => 'No verification session is currently pending.',
            ]);
        }

        $remaining = $this->emailVerificationDeviceService->resendCooldownRemaining($pending);

        if ($remaining > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => "Please wait {$remaining} seconds before resending.",
                    'resend_cooldown' => $remaining,
                ], 429);
            }

            return redirect()
                ->route('sk_official.verification.wait')
                ->withErrors([
                    'email' => "Please wait {$remaining} seconds before resending.",
                ]);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0));

        if ($user === null || strtolower((string) $user->email) !== strtolower((string) $validated['email'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Unable to resend verification for this session.',
                ], 422);
            }

            return redirect()
                ->route('sk_official.verification.wait')
                ->withErrors([
                    'email' => 'Unable to resend verification for this session.',
                ]);
        }

        if ($this->canCompleteVerifiedLogin($pending, $user)) {
            $this->authenticationService->completeEmailVerificationLogin($user, $request, $pending);
            $this->clearVerificationSession($request, $pending);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'state' => 'verified',
                    'message' => 'Email is already verified. Redirecting to dashboard...',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Unable to send verification email. Please try again.',
                ], 500);
            }

            return redirect()
                ->route('sk_official.verification.wait')
                ->withErrors([
                    'email' => 'Unable to send verification email. Please try again.',
                ]);
        }

        $pending['resend_last_sent_at'] = now()->toIso8601String();
        $request->session()->put('sk_official_email_verification_pending', $pending);

        $cooldownSeconds = $this->emailVerificationDeviceService->resendCooldownRemaining($pending);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Verification email resent.',
                'resend_cooldown' => max(1, $cooldownSeconds),
            ]);
        }

        return redirect()
            ->route('sk_official.verification.wait')
            ->with('status', 'Verification email resent.')
            ->with('resend_started', true);
    }

    public function showVerificationSuccess(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('authentication::verify-success', [
            'redirectUrl' => route('dashboard'),
        ]);
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('login')->withErrors([
                'verification' => 'The verification link is invalid or expired.',
            ]);
        }

        $user = User::query()->find($id);

        if ($user === null || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->withErrors([
                'verification' => 'The verification link is invalid or expired.',
            ]);
        }

        $wasVerified = $user->hasVerifiedEmail();

        if (! $wasVerified) {
            $user->markEmailAsVerified();
        } else {
            $currentVerifiedAt = $user->email_verified_at;
            $refreshedVerifiedAt = now();

            if ($currentVerifiedAt !== null && $refreshedVerifiedAt->lessThanOrEqualTo($currentVerifiedAt)) {
                $refreshedVerifiedAt = $currentVerifiedAt->copy()->addSecond();
            }

            $user->forceFill(['email_verified_at' => $refreshedVerifiedAt])->save();
        }

        event(new Verified($user));

        $pending = $request->session()->get('sk_official_email_verification_pending');

        if (! is_array($pending) || (int) ($pending['user_id'] ?? 0) !== (int) $user->getKey()) {
            $pending = ['remember_device' => false];
        }

        $this->authenticationService->completeEmailVerificationLogin($user, $request, $pending);
        $this->clearVerificationSession($request, is_array($pending) ? $pending : null);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            $this->authenticationService->clearSessionOwnershipOnLogout($user, $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View
    {
        return view('authentication::forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        if (config('fortify.lowercase_usernames') && $request->has('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:100'],
        ]);

        try {
            $this->passwordResetService->sendResetLink($request, (string) $validated['email']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'A password reset link has been sent to your email address.');
    }

    public function showResetPassword(Request $request, string $token): View|RedirectResponse
    {
        $email = Str::lower((string) $request->query('email', ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'The password reset link is invalid or incomplete.',
            ]);
        }

        return view('authentication::reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (config('fortify.lowercase_usernames') && $request->has('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'password' => [
                'required',
                'string',
                'max:'.(int) config('sk_official_auth.password_reset.password.max_length', 64),
                'confirmed',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $this->passwordResetService->resetPassword($request, $validated);

        return redirect()->route('password.reset.success')->with('status', 'Your password has been updated successfully. Please log in again.');
    }

    public function showPasswordResetSuccess(): View
    {
        return view('authentication::password-reset-success');
    }

    public function showChangePassword(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if ($this->passwordChangeService->hasPendingChange($user)) {
            return redirect()->route('change-password.verify');
        }

        return view('Profile::change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:'.(int) config('sk_official_auth.password_reset.password.max_length', 64),
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        try {
            $this->passwordChangeService->requestChange($user, (string) $validated['password']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('change-password.verify')
            ->with('status', 'Verification link sent to your email address.');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveVerificationPending(Request $request): ?array
    {
        $pending = $request->session()->get('sk_official_email_verification_pending');

        if (is_array($pending)) {
            return $pending;
        }

        $sessionKey = (string) $request->query('session_key', '');
        $userId = (int) $request->query('user_id', 0);

        if ($sessionKey !== '') {
            $pending = $this->authenticationService->retrieveVerificationWatch($sessionKey);

            if (is_array($pending)) {
                return $this->restoreVerificationPending($request, $pending);
            }
        }

        if ($userId > 0) {
            $pending = $this->authenticationService->retrieveVerificationWatchByUserId($userId);

            if (is_array($pending)) {
                return $this->restoreVerificationPending($request, $pending);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $pending
     * @return array<string, mixed>
     */
    protected function restoreVerificationPending(Request $request, array $pending): array
    {
        $request->session()->put('sk_official_email_verification_pending', $pending);

        return $pending;
    }

    /**
     * @param  array<string, mixed>|null  $pending
     */
    protected function clearVerificationSession(Request $request, ?array $pending = null): void
    {
        $pending ??= $request->session()->get('sk_official_email_verification_pending');

        if (is_array($pending)) {
            $this->authenticationService->clearVerificationWatch($pending);
        }

        $request->session()->forget('sk_official_email_verification_pending');
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    protected function canCompleteVerifiedLogin(array $pending, ?User $user = null): bool
    {
        $user ??= User::query()->find((int) ($pending['user_id'] ?? 0))?->fresh();

        if ($user === null || ! $user->hasVerifiedEmail() || $user->email_verified_at === null) {
            return false;
        }

        $startedAt = Carbon::parse((string) ($pending['started_at'] ?? now()->toIso8601String()));

        return $user->email_verified_at->greaterThanOrEqualTo($startedAt->copy()->subSeconds(5));
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    protected function requiresFreshVerification(array $pending): bool
    {
        return (bool) ($pending['requires_fresh_verification'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    protected function hasFreshVerification(User $user, array $pending): bool
    {
        if ($user->email_verified_at === null) {
            return false;
        }

        $snapshot = (string) ($pending['verified_at_snapshot'] ?? '');

        if ($snapshot === '') {
            $startedAt = Carbon::parse((string) ($pending['started_at'] ?? now()->toIso8601String()));

            return $user->email_verified_at->greaterThanOrEqualTo($startedAt);
        }

        return $user->email_verified_at->greaterThan(Carbon::parse($snapshot));
    }
}
