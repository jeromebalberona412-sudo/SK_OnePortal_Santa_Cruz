<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Refresh user to get latest session data
        $user = $user->fresh();

        if ($user === null) {
            return $next($request);
        }

        if (! Schema::hasColumn('users', 'active_session_id')) {
            return $next($request);
        }

        $currentSessionId = $request->session()->getId();
        $activeSessionId = (string) ($user->active_session_id ?? '');

        // If no active session or this IS the active session, claim it and continue
        if ($activeSessionId === '' || $activeSessionId === $currentSessionId) {
            $this->authenticationService->claimCurrentSession($user, $request);
            return $next($request);
        }

        // Check if we should reclaim for same device BEFORE checking if session is active
        if ($this->authenticationService->shouldReclaimSessionForSameDevice($user, $request)) {
            $this->authenticationService->claimCurrentSession($user, $request);
            return $next($request);
        }

        // Only logout if the other session is actually active
        if (
            ! $this->authenticationService->activeSessionExists($activeSessionId)
            || ! $this->authenticationService->isSessionActive($user)
        ) {
            $this->authenticationService->claimCurrentSession($user, $request);
            return $next($request);
        }

        // Another active session exists - logout this one
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'session' => 'Your session ended because your account was accessed from another device.',
        ]);
    }
}
