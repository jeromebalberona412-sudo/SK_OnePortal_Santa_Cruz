<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Services\AccountActivationService;
use App\Modules\Authentication\Services\TurnstileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountActivationController extends Controller
{
    public function __construct(
        protected AccountActivationService $accountActivationService,
        protected TurnstileService $turnstileService,
    ) {}

    public function showRequestForm(): View
    {
        return view('authentication::verify-account');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        if ($fail = $this->turnstileService->requestFailed($request)) {
            return back()
                ->withErrors(['captcha' => $fail])
                ->withInput();
        }

        if (config('fortify.lowercase_usernames') && $request->has('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:100'],
        ]);

        $email = Str::lower(trim((string) $validated['email']));
        $outcome = $this->accountActivationService->requestNewLink(
            $request,
            $email,
        );

        if ($outcome === AccountActivationService::OUTCOME_THROTTLED) {
            if ($request->session()->has(AccountActivationService::SESSION_EMAIL_KEY)) {
                return redirect()->route('account.activation.sent');
            }

            return back()
                ->withErrors(['email' => AccountActivationService::THROTTLED_MESSAGE])
                ->withInput();
        }

        if ($outcome === AccountActivationService::OUTCOME_ALREADY_ACTIVE) {
            $request->session()->forget(AccountActivationService::SESSION_EMAIL_KEY);

            return redirect()
                ->route('account.activation.already-active');
        }

        if ($outcome === AccountActivationService::OUTCOME_INVALID) {
            return back()
                ->withErrors(['email' => AccountActivationService::INVALID_MESSAGE])
                ->withInput();
        }

        $request->session()->put(AccountActivationService::SESSION_EMAIL_KEY, $email);

        return redirect()->route('account.activation.sent');
    }

    public function showSent(Request $request): View|RedirectResponse
    {
        $email = Str::lower(trim((string) $request->session()->get(AccountActivationService::SESSION_EMAIL_KEY, '')));

        if ($email === '') {
            return redirect()->route('account.activation.request');
        }

        return view('authentication::verify-account-sent', [
            'email' => $email,
            'message' => AccountActivationService::SENT_MESSAGE,
            'cooldownSeconds' => $this->accountActivationService->cooldownSeconds(),
            'cooldownRemaining' => $this->accountActivationService->cooldownRemaining($email),
        ]);
    }

    public function showAlreadyActive(): View
    {
        return view('authentication::activate-account-status', [
            'title' => 'Account Already Activated',
            'message' => AccountActivationService::ALREADY_ACTIVE_MESSAGE,
            'showVerifyButton' => false,
        ]);
    }

    public function showActivateForm(Request $request, string $token): View|RedirectResponse
    {
        $email = Str::lower(trim((string) $request->query('email', '')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return view('authentication::activate-account-status', [
                'title' => 'Activation Link Unavailable',
                'message' => 'This activation link is no longer valid.',
                'showVerifyButton' => true,
            ]);
        }

        $inspection = $this->accountActivationService->inspectToken($email, $token);

        if ($inspection['status'] === AccountActivationService::TOKEN_ALREADY_ACTIVE) {
            return redirect()->route('account.activation.already-active');
        }

        if ($inspection['status'] === AccountActivationService::TOKEN_EXPIRED) {
            return view('authentication::activate-account-status', [
                'title' => 'Activation Link Expired',
                'message' => 'Your activation link is no longer valid. You can request a new activation link from the login page.',
                'showVerifyButton' => true,
            ]);
        }

        if ($inspection['status'] !== AccountActivationService::TOKEN_VALID) {
            return view('authentication::activate-account-status', [
                'title' => 'Activation Link Unavailable',
                'message' => 'This activation link is no longer valid.',
                'showVerifyButton' => true,
            ]);
        }

        return view('authentication::activate-account', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function activate(Request $request): RedirectResponse|View
    {
        if (config('fortify.lowercase_usernames') && $request->has('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'password' => [
                'required',
                'string',
                'max:'.(int) config('sk_official_auth.password_reset.password.max_length', 64),
                'confirmed',
                PasswordRule::min((int) config('sk_official_auth.password_reset.password.min_length', 8))
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        try {
            $this->accountActivationService->activate($request, $validated);
        } catch (ValidationException $exception) {
            $emailError = $exception->errors()['email'][0] ?? null;

            if ($emailError === 'expired') {
                return view('authentication::activate-account-status', [
                    'title' => 'Activation Link Expired',
                    'message' => 'Your activation link is no longer valid. You can request a new activation link from the login page.',
                    'showVerifyButton' => true,
                ]);
            }

            if ($emailError === 'invalid') {
                return view('authentication::activate-account-status', [
                    'title' => 'Activation Link Unavailable',
                    'message' => 'This activation link is no longer valid.',
                    'showVerifyButton' => true,
                ]);
            }

            if ($emailError === AccountActivationService::ALREADY_ACTIVE_MESSAGE) {
                return redirect()->route('account.activation.already-active');
            }

            return back()->withErrors($exception->errors())->withInput($request->except('password', 'password_confirmation'));
        }

        return redirect()
            ->route('login')
            ->with('status', 'Your account has been activated successfully. You can now sign in.');
    }
}
