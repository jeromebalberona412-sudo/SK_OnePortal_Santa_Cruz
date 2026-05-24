<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $auditService;

    public function __construct(AuditLogInterface $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('authentication::login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user has 2FA enabled
            if ($user->two_factor_secret) {
                // Store that user passed password check but needs 2FA
                $request->session()->put([
                    'login.id' => $user->id,
                    'login.remember' => $remember,
                    'login.time' => now(),
                ]);
                
                // Logout temporarily until 2FA is verified
                Auth::logout();
                
                // Handle AJAX request for 2FA
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('two-factor.login')
                    ]);
                }
                
                // Redirect to 2FA challenge
                return redirect()->route('two-factor.login');
            }
            
            +
            
            // No 2FA, proceed with normal login
            $request->session()->regenerate();
            $this->auditService->logLoginSuccess($user);
            $user->recordLogin($request->ip());

            // Handle AJAX request for successful login
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('dashboard'),
                    'message' => 'Welcome back, ' . $user->name . '!'
                ]);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Handle AJAX request for failed login
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle logout
     */
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

    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('authentication::forgot-password');
    }

    /**
     * Send password reset link / OTP code — redirects to OTP page
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We cannot find a user with that email address.',
        ]);

        // Store email in session for OTP step
        $request->session()->put('fp.email', $request->email);
        $request->session()->forget('fp.otp_verified');

        // Redirect to OTP page
        return redirect()->route('password.otp');
    }

    /**
     * Show the OTP verification page (/forgot-password/otp)
     */
    public function showOtp(Request $request)
    {
        // Must have an email in session to reach this page
        if (!$request->session()->has('fp.email')) {
            return redirect()->route('password.request');
        }

        return view('authentication::forgot-password-otp', [
            'email' => $request->session()->get('fp.email'),
        ]);
    }

    /**
     * Verify the 6-digit OTP (demo: 123456) — redirects to set-new-password
     */
    public function verifyOtp(Request $request)
    {
        if (!$request->session()->has('fp.email')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        // Demo: accept 123456
        if ($request->code !== '123456') {
            return back()->withErrors(['code' => 'Incorrect code. Please try again.']);
        }

        $request->session()->put('fp.otp_verified', true);

        return redirect()->route('password.set-new-password');
    }

    /**
     * Show the set new password page (/forgot-password/set-new-password)
     */
    public function showSetNewPassword(Request $request)
    {
        // Must have verified OTP to reach this page
        if (!$request->session()->get('fp.otp_verified')) {
            return redirect()->route('password.request');
        }

        return view('authentication::forgot-password-set');
    }

    /**
     * Set the new password after OTP verification
     */
    public function setNewPassword(Request $request)
    {
        if (!$request->session()->get('fp.otp_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password'              => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        // TODO: actually update the user's password
        // $email = $request->session()->get('fp.email');
        // User::where('email', $email)->update(['password' => Hash::make($request->password)]);

        $request->session()->forget(['fp.email', 'fp.otp_verified']);

        return redirect()->route('login')->with('status', 'Your password has been reset successfully. Please log in.');
    }

    /**
     * Show the password reset form
     */
    public function showResetPassword($token)
    {
        // For now, just show a simple message (UI-only implementation)
        return view('authentication::reset-password', ['token' => $token]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // For now, just show a success message (UI-only implementation)
        return redirect('/login')->with('status', 'Your password has been reset successfully.');
    }

    /**
     * Show the email verification notice
     */
    public function showVerifyEmail()
    {
        return view('authentication::verify-email');
    }

    /**
     * Send email verification notification
     */
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
