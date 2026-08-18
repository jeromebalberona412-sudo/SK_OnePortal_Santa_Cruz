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

        if ($user === null) {
            return redirect()->route('login');
        }

        // isActiveOfficial() already checks role + status + barangay_id internally,
        // so we don't need a separate hasRole() call first.
        // We also skip fresh() — the auth session keeps the user model current.
        // A fresh() on every single request adds one SELECT per page load.
        if (! $user->isActiveOfficial()) {
            // Only do a fresh() when the quick check fails, to verify it's not
            // a stale in-memory model before logging the user out.
            $freshUser = $user->fresh();

            if ($freshUser === null || ! $freshUser->isActiveOfficial()) {
                if ($freshUser !== null) {
                    app(\App\Modules\Authentication\Services\TrustedDeviceService::class)
                        ->revokeAllForUser($freshUser);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $requiredRole = (string) config('sk_official_auth.required_role', User::ROLE_SK_OFFICIAL);
                $hasRole = $freshUser !== null && $freshUser->hasRole($requiredRole);

                if (! $hasRole) {
                    return redirect()->route('login')->withErrors([
                        'access' => 'You do not have permission to access this portal.',
                    ]);
                }

                return redirect()->route('login')->with('access_denied', [
                    'title' => 'Access Denied',
                    'message' => 'Your SK official term has already ended. Login access is no longer available for this account.',
                ]);
            }
        }

        return $next($request);
    }
}
