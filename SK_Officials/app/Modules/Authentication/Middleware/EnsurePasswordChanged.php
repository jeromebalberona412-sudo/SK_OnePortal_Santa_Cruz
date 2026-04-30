<?php

namespace App\Modules\Authentication\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Skip if the column doesn't exist
        if (! Schema::hasColumn('users', 'must_change_password')) {
            return $next($request);
        }

        if (! $user->must_change_password) {
            return $next($request);
        }

        // Already on the change-password page — don't redirect in a loop
        if ($request->routeIs('password.change') || $request->routeIs('password.change.update') || $request->routeIs('logout') || $request->routeIs('logout.fallback')) {
            return $next($request);
        }

        return redirect()->route('password.change')->withErrors([
            'password' => 'You must change your password before continuing.',
        ]);
    }
}
