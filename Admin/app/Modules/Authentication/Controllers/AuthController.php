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
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We cannot find a user with that email address.',
        ]);

        // For now, just show a success message (UI-only implementation)
        return back()->with('status', 'Password reset link has been sent to your email address.');
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
}
