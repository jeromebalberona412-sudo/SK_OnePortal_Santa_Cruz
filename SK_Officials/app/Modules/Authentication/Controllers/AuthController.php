<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Authentication\Services\TenantContextService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
        protected TenantContextService $tenantContextService,
        protected PasswordResetService $passwordResetService,
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
        $pending = $request->session()->get('sk_official_email_verification_pending');

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'verification' => 'No verification session is currently pending.',
            ]);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $request->session()->forget('sk_official_email_verification_pending');

            return redirect()->route('login')->withErrors([
                'verification' => 'Verification session expired. Please sign in again.',
            ]);
        }

        return view('authentication::verify-wait', [
            'email' => (string) ($pending['email'] ?? ''),
            'expiresAtIso' => $expiresAt->toIso8601String(),
            'waitMinutes' => (int) config('sk_official_auth.verification.wait_minutes', 15),
        ]);
    }

    public function checkVerificationStatus(Request $request): JsonResponse
    {
        $pending = $request->session()->get('sk_official_email_verification_pending');

        if (! is_array($pending)) {
            return response()->json([
                'state' => 'missing',
                'message' => 'No verification session is currently pending.',
            ], 404);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $request->session()->forget('sk_official_email_verification_pending');

            return response()->json([
                'state' => 'expired',
                'message' => 'Verification session expired.',
            ], 410);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0));

        if ($user === null) {
            $request->session()->forget('sk_official_email_verification_pending');

            return response()->json([
                'state' => 'missing',
                'message' => 'User not found for this verification session.',
            ], 404);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'state' => 'pending',
                'expires_at' => $expiresAt->toIso8601String(),
                'seconds_remaining' => max(0, now()->diffInSeconds($expiresAt, false)),
            ]);
        }

        if ($this->requiresFreshVerification($pending) && ! $this->hasFreshVerification($user, $pending)) {
            return response()->json([
                'state' => 'pending',
                'expires_at' => $expiresAt->toIso8601String(),
                'seconds_remaining' => max(0, now()->diffInSeconds($expiresAt, false)),
            ]);
        }

        $this->authenticationService->completeEmailVerificationLogin($user, $request, $pending);
        $request->session()->forget('sk_official_email_verification_pending');

        return response()->json([
            'state' => 'verified',
            'redirect' => route('dashboard'),
        ]);
    }

    public function showTakeoverWait(Request $request): View|RedirectResponse
    {
        $takeoverData = $this->authenticationService->showTakeoverWaitData($request);

        if ($takeoverData instanceof RedirectResponse) {
            return $takeoverData;
        }

        return view('authentication::takeover-wait', [
            'email' => (string) ($takeoverData['email'] ?? ''),
            'resendLocked' => (bool) ($takeoverData['resendLocked'] ?? false),
            'cooldownSeconds' => (int) ($takeoverData['cooldownSeconds'] ?? 0),
        ]);
    }

    public function sendTakeoverOtp(Request $request): RedirectResponse
    {
        return $this->authenticationService->sendTakeoverOtp($request);
    }

    public function verifyTakeoverOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ]);

        return $this->authenticationService->verifyTakeoverOtp($request, (string) $validated['otp_code']);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $this->authenticationService->recordHeartbeat($request);

        return response()->json([
            'ok' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $tenantId = $this->tenantContextService->tenantId();

        $user = User::query()
            ->where('email', $validated['email'])
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->first();

        if ($user && $user->hasRole(User::ROLE_SK_OFFICIAL)) {
            $user->sendEmailVerificationNotification();
        }

        return back();
    }

    public function showVerificationSuccess(): View
    {
        return view('authentication::verify-success');
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

        return redirect()->route('sk_official.verification.success');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

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

        $this->passwordResetService->sendResetLink($request, (string) $validated['email']);

        return back()->with('status', 'A password reset link has been sent');
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
                PasswordRule::min((int) config('sk_official_auth.password_reset.password.min_length', 12))
                    ->letters()
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

    public function showChangePassword(): View
    {
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
                PasswordRule::min((int) config('sk_official_auth.password_reset.password.min_length', 12))
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if (Hash::check((string) $validated['password'], (string) $user->password)) {
            return back()->withErrors([
                'password' => 'Your new password must be different from your current password.',
            ]);
        }

        $user->password = (string) $validated['password'];
        $user->remember_token = null;
        $user->save();

        // Update must_change_password to false using raw query with PostgreSQL casting
        User::query()
            ->whereKey($user->getKey())
            ->update(['must_change_password' => \DB::raw("'false'::boolean")]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Password changed successfully. Please log in again.');
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
