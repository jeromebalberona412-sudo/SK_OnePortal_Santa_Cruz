<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Profile\Services\EmailChangeService;
use App\Modules\Profile\Services\PasswordChangeService;
use App\Modules\Profile\Services\ProfileService;
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
        private readonly ProfileService $profileService,
        private readonly EmailChangeService $emailChangeService,
        private readonly PasswordChangeService $passwordChangeService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = Auth::user();
        $display = $this->profileService->getDisplayData($user);

        $programs = collect([
            (object) ['id' => 1, 'name' => 'SK Scholarship Program', 'category' => 'Education', 'status' => 'pending', 'created_at' => '2026-03-10', 'description' => 'Education Assistance Program for deserving youth'],
            (object) ['id' => 2, 'name' => 'Youth Leadership Training', 'category' => 'Leadership Development', 'status' => 'approved', 'created_at' => '2026-02-15', 'description' => 'Develop leadership skills for SK youth'],
            (object) ['id' => 3, 'name' => 'Community Service Program', 'category' => 'Community Development', 'status' => 'evaluation', 'created_at' => '2026-01-20', 'description' => 'Volunteer program for community improvement'],
            (object) ['id' => 4, 'name' => 'Sports Development Program', 'category' => 'Sports & Recreation', 'status' => 'completed', 'created_at' => '2025-12-05', 'description' => 'Sports training and development for youth athletes'],
        ]);

        return view('profile::profile', [
            'user' => $user,
            'profile' => $display,
            'kabataanRegistration' => $display['registration'],
            'barangayName' => $display['barangayName'],
            'barangayLogoUrl' => $display['barangayLogoUrl'],
            'fullName' => $display['fullName'],
            'programs' => $programs,
            'totalPrograms' => $programs->count(),
            'approvedPrograms' => $programs->where('status', 'approved')->count(),
            'evaluationPrograms' => $programs->where('status', 'evaluation')->count(),
            'completedPrograms' => $programs->where('status', 'completed')->count(),
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }

    public function showChangeEmail(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = $request->user()->fresh();

        if ($this->emailChangeService->hasPendingChange($user) || $this->emailChangeService->hasPendingPasswordSet($user)) {
            return redirect()->route('change-email.verify');
        }

        return view('profile::change-email', ['user' => $user])->withHeaders($this->noCacheHeaders());
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
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = $request->user()->fresh();

        if (! $this->emailChangeService->hasPendingChange($user) && ! $this->emailChangeService->hasPendingPasswordSet($user)) {
            return redirect()->route('change-email');
        }

        return view('profile::change-email-verify', [
            'user' => $user,
            'resendCooldown' => $this->emailChangeService->resendCooldownRemaining($user),
            'awaitingPassword' => $this->emailChangeService->hasPendingPasswordSet($user),
        ])->withHeaders($this->noCacheHeaders());
    }

    public function checkChangeEmailVerifyStatus(Request $request): JsonResponse
    {
        $user = $request->user()->fresh();

        if ($this->emailChangeService->hasPendingChange($user)) {
            return response()->json([
                'state' => 'pending',
                'resend_cooldown' => $this->emailChangeService->resendCooldownRemaining($user),
            ]);
        }

        if ($this->emailChangeService->hasPendingPasswordSet($user)) {
            return response()->json([
                'state' => 'awaiting_password',
                'pending_email' => $user->pending_email,
                'message' => 'Email verified. Set your new password on the other tab to finish.',
            ]);
        }

        if ($this->emailChangeService->wasRecentlyCompleted($user->id)) {
            $this->emailChangeService->forgetRecentlyCompleted($user->id);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'state' => 'completed',
                'redirect' => route('login'),
                'message' => 'Email and password updated. Please sign in with your new credentials.',
            ]);
        }

        return response()->json([
            'state' => 'cancelled',
            'redirect' => route('change-email'),
            'message' => 'Email change request is no longer active.',
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
            ->route('change-email')
            ->with('status', 'Email change request cancelled.');
    }

    public function confirmChangeEmail(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $result = $this->emailChangeService->confirm($id, $token);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        return redirect()
            ->route('change-email.set-password', [
                'id' => $result['user']->id,
                'token' => $result['set_password_token'],
            ])
            ->with('status', 'Email verified. Set a new password to complete the change.');
    }

    public function showSetPasswordAfterEmailChange(Request $request, int $id, string $token): View|RedirectResponse
    {
        try {
            $user = $this->emailChangeService->validateSetPasswordToken($id, $token);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        return view('profile::set-password', [
            'user' => $user,
            'token' => $token,
        ])->withHeaders($this->noCacheHeaders());
    }

    public function updateSetPasswordAfterEmailChange(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->emailChangeService->validateSetPasswordToken($id, $token);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:64',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        try {
            $this->emailChangeService->completePasswordSet($user, (string) $validated['password']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $this->emailChangeService->markRecentlyCompleted($user->id);

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Email and password updated. Sign in with your new credentials.');
    }

    public function showChangePassword(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = $request->user()->fresh();

        if ($this->passwordChangeService->hasPendingChange($user)) {
            return redirect()->route('change-password.verify');
        }

        return view('profile::change-password', ['user' => $user])->withHeaders($this->noCacheHeaders());
    }

    public function requestChangePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:64',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        try {
            $this->passwordChangeService->requestChange($request->user(), (string) $validated['password']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('change-password.verify')
            ->with('status', 'Verification link sent to your email address.');
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

        return view('profile::change-password-verify', [
            'user' => $user,
            'resendCooldown' => $this->passwordChangeService->resendCooldownRemaining($user),
        ])->withHeaders($this->noCacheHeaders());
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

    public function resendChangePassword(Request $request): RedirectResponse
    {
        try {
            $this->passwordChangeService->resend($request->user()->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
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

    public function confirmChangePassword(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->passwordChangeService->confirm($id, $token);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
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

    public function uploadProfilePicture(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();

        try {
            $file = $request->file('profile_picture');
            $filename = 'profile_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('profile-pictures', $filename, 'public');

            session(['user_profile_picture' => '/storage/'.$path]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'picture_url' => '/storage/'.$path,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload profile picture: '.$e->getMessage(),
            ], 500);
        }
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

    /**
     * @return array<string, string>
     */
    protected function noCacheHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ];
    }
}
