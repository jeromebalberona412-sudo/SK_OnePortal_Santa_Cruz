<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('authentication::login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'The provided credentials do not match our records.');
        }

        if ($user->status === 'PENDING_APPROVAL') {
            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'Your account is pending approval by SK officials.');
        }

        if ($user->status === 'REJECTED') {
            // Fetch rejection reason from registration
            $registration = \App\Models\KabataanRegistration::where('user_id', $user->id)->latest()->first();
            $reason = $registration?->review_notes
                ? 'Reason: ' . $registration->review_notes
                : 'Please contact your SK officials for more information.';

            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'Your KK Profiling registration has been rejected. ' . $reason);
        }

        if ($user->status === 'INACTIVE') {
            return back()
                ->withInput($request->only('email'))
                ->with('login_error', 'Your account has been deactivated. Please contact your SK officials.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('homepage')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
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

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'A password reset link has been sent to your email address.');
        }

        // Don't reveal whether the email exists
        return back()->with('status', 'If that email is registered, a reset link has been sent.');
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
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
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
