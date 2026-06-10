<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Services\LoginEmailVerificationService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly LoginEmailVerificationService $loginEmailVerificationService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('authentication::verify-email', [
            'user' => $user,
            'resendCooldown' => $this->loginEmailVerificationService->resendCooldownRemaining($user),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user()?->fresh();

        if ($user === null) {
            return response()->json([
                'state' => 'unauthenticated',
                'redirect' => route('login'),
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'state' => 'verified',
                'redirect' => route('dashboard'),
                'message' => 'Email verified. Redirecting to dashboard...',
            ]);
        }

        return response()->json([
            'state' => 'pending',
            'resend_cooldown' => $this->loginEmailVerificationService->resendCooldownRemaining($user),
        ]);
    }

    public function resend(Request $request): RedirectResponse
    {
        try {
            $this->loginEmailVerificationService->resend($request->user()->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'verification-link-sent');
    }

    public function verify(Request $request, int $id, string $token): RedirectResponse
    {
        try {
            $user = $this->loginEmailVerificationService->confirm($id, $token);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors($exception->errors());
        }

        if ($request->user() !== null && (int) $request->user()->id === (int) $user->id) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Email verified successfully. Welcome back!');
        }

        return redirect()
            ->route('login')
            ->with('status', 'email-verified');
    }
}
