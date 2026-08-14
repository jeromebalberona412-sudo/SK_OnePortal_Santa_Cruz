<?php

namespace App\Modules\Profile\Controllers;

use App\Models\User;
use App\Modules\Profile\Services\EmailChangeService;
use App\Modules\Profile\Services\PasswordChangeService;
use App\Modules\Profile\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly EmailChangeService $emailChangeService,
        private readonly PasswordChangeService $passwordChangeService,
    ) {
    }

    public function index(Request $request): View
    {
        $profile = $this->profileService->getDisplayData($request->user());

        return view('Profile::profile', [
            'user' => $request->user(),
            'profile' => $profile,
        ]);
    }

    public function showChangeEmail(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if ($this->emailChangeService->hasPendingChange($user)) {
            return redirect()->route('change-email.verify');
        }

        return view('Profile::change-email', [
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
                $request->user(),
                $validated['current_email'],
                $validated['new_email'],
                $validated['password'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('change-email.verify')
            ->with('status', 'Verification link sent to your new email address.');
    }

    public function showChangeEmailVerify(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if (! $this->emailChangeService->hasPendingChange($user)) {
            return redirect()->route('change-email');
        }

        $request->session()->put('email_change_verify_active', true);

        return view('Profile::change-email-verify', [
            'user' => $user,
            'resendCooldown' => $this->emailChangeService->resendCooldownRemaining($user),
        ]);
    }

    public function checkChangeEmailVerifyStatus(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();

        if ($this->emailChangeService->hasPendingChange($user)) {
            return response()->json(['state' => 'pending']);
        }

        if ($request->session()->pull('email_change_verify_active')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'state' => 'confirmed',
                'redirect' => route('login'),
                'message' => 'Email changed successfully. Sign in with your new email and current password.',
            ]);
        }

        return response()->json([
            'state' => 'cancelled',
            'redirect' => route('change-email'),
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
        $request->session()->forget('email_change_verify_active');
        $this->emailChangeService->cancel($request->user()->fresh());

        return redirect()
            ->route('change-email')
            ->with('status', 'Email change request cancelled.');
    }

    public function confirmChangeEmail(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $this->emailChangeService->confirm($id, $token);
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
            ->route('login')
            ->with('status', 'Email changed successfully. Sign in with your new email and current password.');
    }

    public function showSetPasswordAfterEmailChange(Request $request, int $id, string $token): RedirectResponse
    {
        return $this->finishLegacyEmailChangeSetPassword($request, $id, $token);
    }

    public function updateSetPasswordAfterEmailChange(Request $request, int $id, string $token): RedirectResponse
    {
        return $this->finishLegacyEmailChangeSetPassword($request, $id, $token);
    }

    protected function finishLegacyEmailChangeSetPassword(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->emailChangeService->validateSetPasswordToken($id, $token);
            $this->emailChangeService->applyConfirmedEmail($user);
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
            ->route('login')
            ->with('status', 'Email changed successfully. Sign in with your new email and current password.');
    }

    public function showChangePasswordVerify(Request $request): View|RedirectResponse
    {
        $user = $request->user()->fresh();

        if (! $this->passwordChangeService->hasPendingChange($user)) {
            if ($this->wasPasswordChangeConfirmed($request, $user)) {
                return $this->finishPasswordChangeLogout($request, $user);
            }

            $request->session()->forget('password_change_verify_active');

            return redirect()->route('change-password');
        }

        $request->session()->put('password_change_verify_active', true);

        return view('Profile::change-password-verify', [
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
            $this->passwordChangeService->forgetRecentlyConfirmed($user->id);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'state' => 'confirmed',
                'redirect' => route('login'),
                'message' => 'Password changed successfully. Please sign in with your new password.',
            ]);
        }

        return response()->json([
            'state' => 'cancelled',
            'redirect' => route('change-password'),
            'message' => 'Password change request is no longer active.',
        ]);
    }

    public function resendChangePassword(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->passwordChangeService->resend($request->user()->fresh());
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?: 'Unable to resend verification email. Please try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'cooldown' => $this->passwordChangeService->resendCooldownRemaining($request->user()->fresh()),
                ], 422);
            }

            return back()->withErrors($exception->errors());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Verification email resent.',
                'cooldown' => 60,
            ]);
        }

        return back()->with('status', 'Verification email resent.');
    }

    public function cancelChangePassword(Request $request): RedirectResponse
    {
        $this->passwordChangeService->cancel($request->user()->fresh());
        $request->session()->forget('password_change_verify_active');
        $this->passwordChangeService->forgetRecentlyConfirmed($request->user()->id);

        return redirect()
            ->route('change-password')
            ->with('status', 'Password change request cancelled.');
    }

    protected function wasPasswordChangeConfirmed(Request $request, User $user): bool
    {
        return $this->passwordChangeService->wasRecentlyConfirmed($user->id)
            || (bool) $request->session()->get('password_change_verify_active', false);
    }

    protected function finishPasswordChangeLogout(Request $request, User $user): RedirectResponse
    {
        $request->session()->forget('password_change_verify_active');
        $this->passwordChangeService->forgetRecentlyConfirmed($user->id);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Password changed successfully. Please sign in with your new password.');
    }

    public function confirmChangePassword(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->passwordChangeService->confirm($id, $token);
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
            ->route('login')
            ->with('status', 'Password changed successfully for '.$user->email.'. Please sign in with your new password.');
    }
}
