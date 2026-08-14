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
        if ($request->routeIs(
            'change-password',
            'password.change',
            'password.change.update',
            'change-password.verify',
            'change-password.verify.status',
            'change-password.resend',
            'change-password.cancel',
            'change-email',
            'change-email.request',
            'change-email.verify',
            'change-email.verify.status',
            'change-email.resend',
            'change-email.cancel',
            'profile',
            'logout',
            'logout.fallback',
        )) {
            return $next($request);
        }

        return redirect()->route('change-password');
    }
}
