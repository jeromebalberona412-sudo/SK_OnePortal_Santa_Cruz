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
                
                // Redirect to 2FA challenge
                return redirect()->route('two-factor.login');
            }
            
            // No 2FA, proceed with normal login
            $request->session()->regenerate();
            $this->auditService->logLoginSuccess($user);
            $user->recordLogin($request->ip());

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
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
}
