<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Profile\Services\EmailChangeService;
use App\Modules\Profile\Services\PasswordChangeService;
use App\Modules\Profile\Support\ProfilePasswordChangeState;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly PasswordChangeService $passwordChangeService,
        private readonly EmailChangeService $emailChangeService,
    ) {}

    public function index(Request $request): View
    {
        return view('profile::profile', ['user' => $request->user()]);
    }

    public function showChangePassword(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if ($this->passwordChangeService->hasPendingChange($user)) {
            return redirect()->route('profile.change-password.verify');
        }

        return view('profile::change-password', [
            'user' => $user,
        ]);
    }

    public function sendChangePasswordLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $this->passwordChangeService->requestChange(
                $request->user()->fresh(),
                $validated['email'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $request->session()->put('password_change_verify_active', true);

        return redirect()
            ->route('profile.change-password.verify')
            ->with('status', 'Password reset link sent to your email.');
    }

    public function showChangePasswordVerify(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if (! $this->passwordChangeService->hasPendingChange($user)) {
            if ($this->wasPasswordChangeConfirmed($request, $user)) {
                return $this->finishPasswordChangeLogout($request, $user);
            }

            $request->session()->forget('password_change_verify_active');

            return redirect()->route('profile.change-password');
        }

        $request->session()->put('password_change_verify_active', true);

        return view('profile::change-password-verify', [
            'user' => $user,
            'resendCooldown' => $this->passwordChangeService->resendCooldownRemaining($user),
        ]);
    }

    public function checkChangePasswordVerifyStatus(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();

        if ($this->passwordChangeService->hasPendingChange($user)) {
            return response()->json([
                'state' => 'pending',
                'resend_cooldown' => $this->passwordChangeService->resendCooldownRemaining($user),
            ]);
        }

        if ($this->wasPasswordChangeConfirmed($request, $user)) {
            $request->session()->forget('password_change_verify_active');
            ProfilePasswordChangeState::forgetConfirmed($user->id);

            return response()->json([
                'state' => 'confirmed',
                'redirect' => route('login'),
                'message' => 'Password changed successfully.',
            ]);
        }

        return response()->json([
            'state' => 'cancelled',
            'redirect' => route('profile.change-password'),
            'message' => 'Password change request is no longer active.',
        ]);
    }

    public function resendChangePassword(Request $request): RedirectResponse
    {
        try {
            $this->passwordChangeService->resend($request->user()->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Password reset link resent.');
    }

    public function cancelChangePassword(Request $request): RedirectResponse
    {
        $this->passwordChangeService->cancel($request->user()->fresh());
        $request->session()->forget('password_change_verify_active');
        ProfilePasswordChangeState::forgetConfirmed($request->user()->id);

        return redirect()
            ->route('profile.change-password')
            ->with('status', 'Password change request cancelled.');
    }

    public function showChangeEmail(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if ($this->emailChangeService->hasPendingChange($user)) {
            return redirect()->route('profile.change-email.verify');
        }

        return view('profile::change-email', [
            'user' => $user,
        ]);
    }

    public function requestChangeEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_email' => ['required', 'email', 'max:255'],
            'new_email' => ['required', 'email', 'max:255', 'different:current_email'],
            'password' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->emailChangeService->requestChange(
                $request->user()->fresh(),
                $validated['current_email'],
                $validated['new_email'],
                $validated['password'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('profile.change-email.verify')
            ->with('status', 'Verification link sent to your new email address.');
    }

    public function showChangeEmailVerify(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if (! $this->emailChangeService->hasPendingChange($user)) {
            return redirect()->route('profile.change-email');
        }

        return view('profile::change-email-verify', [
            'user' => $user,
            'resendCooldown' => $this->emailChangeService->resendCooldownRemaining($user),
        ]);
    }

    public function resendChangeEmail(Request $request): RedirectResponse
    {
        try {
            $this->emailChangeService->resend($request->user()->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Verification email resent.');
    }

    public function cancelChangeEmail(Request $request): RedirectResponse
    {
        $this->emailChangeService->cancel($request->user()->fresh());

        return redirect()
            ->route('profile.change-email')
            ->with('status', 'Email change request cancelled.');
    }

    public function confirmChangeEmail(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $result = $this->emailChangeService->confirm($id, $token);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors($exception->errors());
        }

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('profile.change-email.set-password', [
                'id' => $result['user']->id,
                'token' => $result['set_password_token'],
            ])
            ->with('status', 'Email changed to '.$result['user']->email.'. Set a new password to finish.');
    }

    public function showSetPasswordAfterEmailChange(Request $request, int $id, string $token): View|RedirectResponse
    {
        try {
            $user = $this->emailChangeService->validateSetPasswordToken($id, $token);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors($exception->errors());
        }

        return view('profile::set-password-after-email-change', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function updateSetPasswordAfterEmailChange(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->emailChangeService->validateSetPasswordToken($id, $token);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors($exception->errors());
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:64',
                PasswordRule::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $this->emailChangeService->completePasswordSet($user, (string) $validated['password']);

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'password-changed');
    }

    protected function wasPasswordChangeConfirmed(Request $request, User $user): bool
    {
        return ProfilePasswordChangeState::wasConfirmed($user->id);
    }

    protected function finishPasswordChangeLogout(Request $request, User $user): RedirectResponse
    {
        $request->session()->forget('password_change_verify_active');
        ProfilePasswordChangeState::forgetConfirmed($user->id);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Password changed successfully. Sign in with your new password.');
    }
}
