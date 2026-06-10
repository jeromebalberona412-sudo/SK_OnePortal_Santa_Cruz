<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! Schema::hasColumn('users', 'must_change_password') || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'setup-password',
            'setup-password.store',
            'setup-password.resend',
            'logout',
        )) {
            return $next($request);
        }

        return redirect()
            ->route('setup-password')
            ->with('status', 'password-setup-required');
    }
}
