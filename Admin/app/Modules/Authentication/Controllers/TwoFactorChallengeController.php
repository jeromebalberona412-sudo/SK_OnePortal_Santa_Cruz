<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    protected $auditService;

    public function __construct(AuditLogInterface $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Show the two-factor challenge page
     */
    public function show(Request $request)
    {
        // Check if user has passed password authentication
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        // Check if session has expired (10 minutes)
        $loginTime = $request->session()->get('login.time');
        if (!$loginTime || now()->diffInMinutes($loginTime) >= 10) {
            $request->session()->forget(['login.id', 'login.remember', 'login.time']);
            return redirect()->route('login')
                ->withErrors(['email' => 'Your session has expired. Please login again.']);
        }

        return view('authentication::two-factor-challenge');
    }

    /**
     * Verify the two-factor authentication code
     */
    public function verify(Request $request)
    {
        // Check if user has passed password authentication
        if (!$request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        // Check if session has expired (10 minutes)
        $loginTime = $request->session()->get('login.time');
        if (!$loginTime || now()->diffInMinutes($loginTime) >= 10) {
            $request->session()->forget(['login.id', 'login.remember', 'login.time']);
            return redirect()->route('login')
                ->withErrors(['email' => 'Your session has expired. Please login again.']);
        }

        $userId = $request->session()->get('login.id');
        $user = User::findOrFail($userId);

        // Check if using recovery code
        if ($request->filled('recovery_code')) {
            return $this->verifyRecoveryCode($request, $user);
        }

        // Validate 2FA code
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => 'The provided code is invalid.',
            ]);
        }

        // Code is valid, complete the login
        return $this->completeLogin($request, $user);
    }

    /**
     * Verify recovery code
     */
    protected function verifyRecoveryCode(Request $request, User $user)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        $code = str_replace('-', '', $request->recovery_code);
        
        if (!in_array($code, $recoveryCodes)) {
            throw ValidationException::withMessages([
                'recovery_code' => 'The provided recovery code is invalid.',
            ]);
        }

        // Remove used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$code]);
        $user->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
        $user->save();

        // Complete the login
        return $this->completeLogin($request, $user);
    }

    /**
     * Complete the login process after 2FA verification
     */
    protected function completeLogin(Request $request, User $user)
    {
        $remember = $request->session()->get('login.remember', false);
        
        // Clear the temporary session data
        $request->session()->forget(['login.id', 'login.remember', 'login.time']);
        
        // Log the user in
        Auth::login($user, $remember);
        $request->session()->regenerate();
        
        // Log successful login
        $this->auditService->logLoginSuccess($user);
        $user->recordLogin($request->ip());

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }
}
