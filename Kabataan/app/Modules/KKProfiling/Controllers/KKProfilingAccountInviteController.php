<?php

namespace App\Modules\KKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Services\KkProfilingAccountInviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KKProfilingAccountInviteController extends Controller
{
    public function __construct(
        private readonly KkProfilingAccountInviteService $inviteService,
    ) {}

    public function show(int $registration, string $token): View
    {
        try {
            $record = $this->inviteService->findValidRegistration($registration, $token);
        } catch (ValidationException $e) {
            return view('kkprofiling::account-activate-error', [
                'message' => collect($e->errors())->flatten()->first() ?: 'This activation link is invalid.',
            ]);
        }

        return view('kkprofiling::account-activate', [
            'registration' => $record,
            'token' => $token,
            'email' => $record->email,
            'barangay' => $record->barangay?->name ?? 'your barangay',
        ]);
    }

    public function activate(Request $request, int $registration, string $token): RedirectResponse|View
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        try {
            $record = $this->inviteService->findValidRegistration($registration, $token);
            $this->inviteService->activate($record, $token, (string) $request->input('password'));
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?: 'Unable to activate this account.';

            if ($request->boolean('from_form') || $request->has('password')) {
                return back()->withErrors(['password' => $message])->withInput();
            }

            return view('kkprofiling::account-activate-error', [
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('sign-in')
            ->with('success', 'Your Kabataan account is ready. You can now sign in.');
    }
}
