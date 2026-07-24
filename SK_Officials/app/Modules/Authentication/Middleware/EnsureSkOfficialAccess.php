<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSkOfficialAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        // If not authenticated, let auth middleware handle redirect
        if ($user === null) {
            return redirect()->route('login');
        }

        // Refresh user to get latest data
        $user = $user->fresh();

        if ($user === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);

        if (! $user->hasRole($requiredRole)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'access' => 'You do not have permission to access this portal.',
            ]);
        }

        // Check if user is active official (avoid logging out on first check)
        if ($user->hasRole($requiredRole) && ! $user->isActiveOfficial()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('access_denied', [
                'title' => 'Access Denied',
                'message' => 'Your SK official term has already ended. Login access is no longer available for this account.',
            ]);
        }

        return $next($request);
    }
}
