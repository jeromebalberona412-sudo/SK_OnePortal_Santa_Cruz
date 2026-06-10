<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\Authentication\Rules\StrongPassword;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Authentication\Services\PasswordSetupService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuditLogInterface $auditService,
        protected AuthenticationService $authenticationService,
        protected PasswordSetupService $passwordSetupService,
        protected PasswordResetService $passwordResetService,
    ) {}

    public function showLogin()
    {
        return view('authentication::login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->authenticationService->authenticate($request);

        if ($user === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials do not match our records.',
                ], 422);
            }

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->must_change_password) {
            $this->auditService->logFirstLogin($user);
            $this->passwordSetupService->sendSetupLink($user);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('setup-password'),
                    'message' => 'Please check your email to complete password setup.',
                ]);
            }

            return redirect()
                ->route('setup-password')
                ->with('status', 'password-setup-required');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard'),
                'message' => 'Welcome back, '.$user->name.'!',
            ]);
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Welcome back, '.$user->name.'!');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $this->auditService->logLogout($user);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('message', 'You have been logged out successfully.');
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

        $this->passwordResetService->sendResetLink($request, $request->input('email'));

        return back()
            ->with('status', 'reset-link-sent')
            ->with('fp_email', $request->input('email'));
    }

    public function showResetPassword($token)
    {
        return view('authentication::reset-password', [
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $this->passwordResetService->resetPassword($request, $validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password successfully updated. Welcome to SK One Portal Administrator Dashboard.');
    }

    public function showVerifyEmail()
    {
        return view('authentication::verify-email');
    }

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
