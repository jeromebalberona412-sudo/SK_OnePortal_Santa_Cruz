<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // If the users table doesn't have active_session_id, skip enforcement
        if (! Schema::hasColumn('users', 'active_session_id')) {
            return $next($request);
        }

        $activeSessionId  = (string) ($user->active_session_id ?? '');
        $currentSessionId = $request->session()->getId();

        // No active session recorded yet — claim it
        if ($activeSessionId === '') {
            $user->forceFill(['active_session_id' => $currentSessionId])->save();

            return $next($request);
        }

        // Current session matches the active session — all good
        if ($activeSessionId === $currentSessionId) {
            return $next($request);
        }

        // Another session is active — check if it's still alive via heartbeat
        $timeoutSeconds = (int) config('sk_official_auth.single_session.heartbeat_timeout_seconds', 120);

        if (Schema::hasColumn('users', 'last_seen') && $user->last_seen !== null) {
            $elapsed = $user->last_seen->diffInSeconds(now());

            if ($elapsed <= $timeoutSeconds) {
                // Active session is still alive — redirect to takeover flow
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'session' => 'Your account is already active in another session.',
                ]);
            }
        }

        // Previous session timed out — claim this session
        $user->forceFill(['active_session_id' => $currentSessionId])->save();

        return $next($request);
    }
}
