<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Rules\StrongPassword;
use App\Modules\Authentication\Services\PasswordSetupService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordSetupController extends Controller
{
    public function __construct(
        protected PasswordSetupService $passwordSetupService,
    ) {}

    public function show(Request $request)
    {
        $user = $request->user();
        $token = (string) $request->query('token', '');
        $email = (string) $request->query('email', $user?->email ?? '');

        if ($user && ! $user->must_change_password) {
            return redirect()->route('dashboard');
        }

        $hasValidToken = $this->passwordSetupService->hasValidToken($user, $email, $token);

        return view('authentication::setup-password', [
            'email' => $email,
            'token' => $token,
            'hasValidToken' => $hasValidToken,
            'awaitingEmail' => $user?->must_change_password && ! $hasValidToken,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $this->passwordSetupService->completeSetup($request, $validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Password successfully configured. Welcome to SK One Portal Administrator Dashboard.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user === null || ! $user->must_change_password) {
            return redirect()->route('login');
        }

        $this->passwordSetupService->sendSetupLink($user);

        return back()->with('status', 'setup-link-sent');
    }
}
