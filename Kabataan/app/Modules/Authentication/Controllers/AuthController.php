<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin(Request $request)
    {
        if ($request->session()->has('prototype_authenticated')) {
            return redirect()->route('dashboard');
        }

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

        // PROTOTYPE MODE: create a local session and go straight to dashboard.
        $name = explode('@', $credentials['email'])[0] ?? 'Youth User';

        $request->session()->regenerate();
        $request->session()->put('verification_email', $credentials['email']);
        $request->session()->put('prototype_user_name', $name);
        $request->session()->put('prototype_authenticated', true);
        $request->session()->put('prototype_user', [
            'id' => 1,
            'name' => ucfirst($name),
            'email' => $credentials['email'],
            'barangay' => 'Barangay 1',
        ]);

        return redirect()->route('dashboard');

        /* PRODUCTION CODE (commented for prototype):
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
        */
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget([
            'prototype_authenticated',
            'prototype_user',
            'verification_email',
            'prototype_user_name'
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('homepage')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
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

        return back()->with('status', 'Password reset link has been sent to your email address.');
    }

    /**
     * Show the password reset form
     */
    public function showResetPassword($token)
    {
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

        return redirect('/login')->with('status', 'Your password has been reset successfully.');
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        return view('authentication::register');
    }

    /**
     * Handle registration request (Prototype - not yet implemented)
     */
    public function register(Request $request)
    {
        // TODO: Implement registration logic
        return back()->with('info', 'Registration feature is coming soon!');
    }

    /**
     * Show email verification page
     */
    public function showEmailVerification(Request $request)
    {
        // Get email from query parameter or session
        $email = $request->query('email') ?? $request->session()->get('verification_email', 'your email');
        
        // Store in session for future use
        if ($request->query('email')) {
            $request->session()->put('verification_email', $request->query('email'));
        }
        
        return view('authentication::email-verification', [
            'email' => $email
        ]);
    }

    /**
     * Send verification email (Prototype - simulated)
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Store email in session for display
        $request->session()->put('verification_email', $request->email);

        // In real implementation, send actual email here
        // For prototype, just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully'
        ]);
    }

    /**
     * Resend verification email (Prototype - simulated)
     */
    public function resendVerificationEmail(Request $request)
    {
        $email = $request->session()->get('verification_email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'No email found in session'
            ], 400);
        }

        // In real implementation, resend actual email here
        // For prototype, just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Verification email resent successfully'
        ]);
    }

    /**
     * Check verification status (Prototype - simulated)
     */
    public function checkVerificationStatus(Request $request)
    {
        // In real implementation, check database for verification status
        // For prototype, return not verified (frontend will simulate verification)
        
        return response()->json([
            'verified' => false
        ]);
    }

    /**
     * Verify email with token (Prototype - simulated)
     */
    public function verifyEmail($token, Request $request)
    {
        // PROTOTYPE MODE: Create a fake authenticated session
        // In production, this would validate the token and mark email as verified
        
        // Create a fake user object for prototype
        $email = session('verification_email', 'youth@skportal.com');
        $name = session('prototype_user_name', 'Youth User');
        
        // Store fake user data in session to simulate authentication
        session([
            'prototype_authenticated' => true,
            'prototype_user' => [
                'id' => 1,
                'name' => ucfirst($name),
                'email' => $email,
                'barangay' => 'Barangay 1',
            ]
        ]);
        
        // If it's an AJAX request, return JSON (for email verification page)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'redirect' => route('dashboard')
            ]);
        }
        
        // For direct access, show the verify-success page (no redirect)
        return view('authentication::verify-success');
    }

    /**
     * Show test page for email verification (Development only)
     */
    public function showTestEmailVerification()
    {
        return view('authentication::test-email-verification');
    }
}
