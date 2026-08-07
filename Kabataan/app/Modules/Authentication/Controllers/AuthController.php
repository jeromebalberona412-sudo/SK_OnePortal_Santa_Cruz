<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Services\KabataanAuthService;
use App\Services\KkProfilingScheduleService;
use App\Services\RegistrationEvaluationService;
use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly KabataanAuthService $kabataanAuthService,
        private readonly TurnstileService $turnstileService,
    ) {}

    public function showLogin()
    {
        if (Auth::check()) {
            if ($this->kabataanAuthService->canAccessPortal(Auth::user())) {
                return redirect()->route('dashboard');
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('authentication::login');
    }

    public function login(Request $request)
    {
        $startTime = microtime(true);
        $logData = [];

        // Enable query logging for this request
        \DB::enableQueryLog();

        // Verify Turnstile token if enabled
        $turnstileStart = microtime(true);
        if (config('services.turnstile.enabled')) {
            $token = (string) $request->input('cf-turnstile-response', '');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pakumpleto ang seguridad na pagpapatunay.',
                ], 422);
            }

            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'Pakumpleto ang seguridad na pagpapatunay.');
        }

        if (! $this->turnstileService->verify($token, $request->ip())) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nabigo ang seguridad na pagpapatunay. Pakisubukang muli.',
                ], 422);
            }

            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'Nabigo ang seguridad na pagpapatunay. Pakisubukang muli.');
        }
    }

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $credentials['email'])->first();

    if (
        ! $user
        || ! Hash::check($credentials['password'], $user->password)
        || ! $this->kabataanAuthService->canAccessPortal($user)
    ) {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => KabataanAuthService::LOGIN_DENIED_MESSAGE,
            ], 422);
        }

        return back()
            ->withInput($request->only('email'))
            ->with('login_error', KabataanAuthService::LOGIN_DENIED_MESSAGE);
    }

    // Load registration data once for all status checks
    $registration = null;
    if (in_array($user->status, [User::STATUS_PENDING_APPROVAL, 'REJECTED'], true)) {
        $registration = KabataanRegistration::where('user_id', $user->id)->latest('id')->first();
    }

    if ($user->status === User::STATUS_PENDING_APPROVAL) {
        if ($registration && RegistrationEvaluationService::isAutoApprovedStatus($registration->evaluation_status)) {
            $user->update(['status' => User::STATUS_ACTIVE]);
        } else {
            $msg = 'Please wait for SK officials to verify your account. You will receive an email once your registration has been approved.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withInput($request->only('email'))->with('login_error', $msg);
        }
    }

    if ($user->status === 'REJECTED') {
        $reason = $registration?->review_notes
            ? 'Reason: '.$registration->review_notes
            : 'Please contact your SK officials for more information.';
        $msg = 'Your KK Profiling registration has been rejected. '.$reason;
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        return back()->withInput($request->only('email'))->with('login_error', $msg);
    }

    if ($user->status === 'INACTIVE') {
        $msg = 'Your account has been deactivated. Please contact your SK officials.';
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        return back()->withInput($request->only('email'))->with('login_error', $msg);
    }

    Auth::login($user, $request->boolean('remember'));
    $request->session()->regenerate();

    if ($registration === null) {
        $registration = KabataanRegistration::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    if ($registration && app(KkProfilingScheduleService::class)->requiresProfilingUpdate($registration)) {
        $request->session()->flash('show_kk_profiling_update', true);
        $request->session()->flash('kk_profiling_update_required', true);
        $request->session()->put('kk_profiling_update_required', true);
        $request->session()->put('kabataan_registration_id', $registration->id);
    } else {
        $request->session()->put('kk_profiling_update_required', false);
    }

    $redirectUrl = redirect()->intended(route('dashboard'))->getTargetUrl();

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl,
        ]);
    }

    return redirect()->intended(route('dashboard'));
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
    }

    public function showForgotPassword()
    {
        return view('authentication::forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $this->kabataanAuthService->canAccessPortal($user)) {
            return back()
                ->withInput($request->only('email'))
                ->with('forgot_password_error', 'No account found with this email address. Please check your email and try again.');
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'A password reset link has been sent to your email address.');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('forgot_password_error', __($status));
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('authentication::reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $this->kabataanAuthService->canAccessPortal($user)) {
            throw ValidationException::withMessages([
                'email' => 'We could not reset the password for this account.',
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $resetUser, string $password) {
                if (! $this->kabataanAuthService->canAccessPortal($resetUser)) {
                    return;
                }

                $resetUser->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Your password has been reset. You can now log in.');
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    // Keep these for route compatibility (prototype routes still registered)
    public function showRegister()
    {
        return redirect()->route('kkprofiling.signup');
    }

    public function showEmailVerification(Request $request)
    {
        return view('authentication::email-verification', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function sendVerificationEmail(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function resendVerificationEmail(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function checkVerificationStatus(Request $request)
    {
        return response()->json(['verified' => false]);
    }

    public function verifyEmail($token, Request $request)
    {
        return view('authentication::verify-success');
    }

    public function showTestEmailVerification()
    {
        return view('authentication::test-email-verification');
    }
}
