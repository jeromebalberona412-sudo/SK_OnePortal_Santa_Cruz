<?php

namespace App\Modules\Authentication\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) { return $next($request); }

        if ($request->is('user/two-factor-authentication') ||
            $request->is('user/two-factor-qr-code') ||
            $request->is('user/two-factor-recovery-codes') ||
            $request->is('user/confirmed-two-factor-authentication') ||
            $request->is('logout')) {
            return $next($request);
        }

        if (!$user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup')
                ->with('error', 'You must enable Two-Factor Authentication before accessing the dashboard.');
        }
        return $next($request);
    }
}
