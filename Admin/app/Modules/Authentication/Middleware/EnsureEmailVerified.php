<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            return $next($request);
        }

        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->routeIs(
            'verification.notice',
            'verification.send',
            'verification.status',
            'verification.verify',
            'logout',
            'setup-password',
            'setup-password.store',
            'setup-password.resend',
        )) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
