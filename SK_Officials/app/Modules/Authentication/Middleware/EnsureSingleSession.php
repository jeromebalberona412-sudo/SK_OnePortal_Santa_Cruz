<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        // Use the already-loaded user rather than issuing an extra SELECT.
        // fresh() is only needed when another process may have changed the
        // session — we rely on the Login event handler to keep active_session_id
        // up-to-date, so a fresh() on every request is unnecessary.

        // Cache the schema check so it doesn't hit the DB on every request
        $hasColumn = (bool) Cache::rememberForever('schema_col:users.active_session_id', function () {
            try {
                return Schema::hasColumn('users', 'active_session_id');
            } catch (\Throwable) {
                return false;
            }
        });

        if (! $hasColumn) {
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

        // Another active session exists — logout this one
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'session' => 'Your session ended because your account was accessed from another device.',
        ]);
    }
}
