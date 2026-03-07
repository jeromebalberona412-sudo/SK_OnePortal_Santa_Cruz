<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\EmailVerificationDeviceService;
use App\Modules\Authentication\Services\TenantContextService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
        protected TenantContextService $tenantContextService,
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
        $pending = $request->session()->get('sk_fed_email_verification_pending');

        if (! is_array($pending)) {
            return redirect()->route('login')->withErrors([
                'verification' => 'No verification session is currently pending.',
            ]);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $request->session()->forget('sk_fed_email_verification_pending');

            return redirect()->route('login')->withErrors([
                'verification' => 'Verification session expired. Please sign in again.',
            ]);
        }

        return view('authentication::verify-wait', [
            'email' => (string) ($pending['email'] ?? ''),
            'expiresAtIso' => $expiresAt->toIso8601String(),
            'waitMinutes' => (int) config('sk_fed_auth.verification.wait_minutes', 15),
        ]);
    }

    public function checkVerificationStatus(Request $request): JsonResponse
    {
        $pending = $request->session()->get('sk_fed_email_verification_pending');

        if (! is_array($pending)) {
            return response()->json([
                'state' => 'missing',
                'message' => 'No verification session is currently pending.',
            ], 404);
        }

        $expiresAt = Carbon::parse((string) ($pending['expires_at'] ?? now()->toIso8601String()));

        if ($expiresAt->isPast()) {
            $request->session()->forget('sk_fed_email_verification_pending');

            return response()->json([
                'state' => 'expired',
                'message' => 'Verification session expired.',
            ], 410);
        }

        $user = User::query()->find((int) ($pending['user_id'] ?? 0));

        if ($user === null) {
            $request->session()->forget('sk_fed_email_verification_pending');

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

        Auth::login($user);
        $request->session()->regenerate();
        $this->authenticationService->claimCurrentSession($user, $request);
        $this->emailVerificationDeviceService->markVerifiedDeviceFromPending($user, $pending);
        $request->session()->forget('sk_fed_email_verification_pending');

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

        if ($user && $user->hasRole(User::ROLE_SK_FED)) {
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

        return redirect()->route('skfed.verification.success');
    }

    public function logout(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user !== null) {
            $this->authenticationService->clearSessionOwnershipOnLogout($user, request());
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forgot password form (Prototype - UI Only)
     */
    public function showForgotPassword(): View
    {
        return view('authentication::forgot-password');
    }

    /**
     * Show reset password form (Prototype - UI Only)
     */
    public function showResetPassword(): View
    {
        return view('authentication::reset-password');
    }

    /**
     * Show password reset success page (Prototype - UI Only)
     */
    public function showPasswordResetSuccess(): View
    {
        return view('authentication::password-reset-success');
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

        $snapshotAt = Carbon::parse($snapshot);

        return $user->email_verified_at->greaterThan($snapshotAt);
    }
}
