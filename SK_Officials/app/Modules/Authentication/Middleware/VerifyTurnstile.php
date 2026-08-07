<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function __construct(
        protected TurnstileService $turnstileService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // If Turnstile is disabled in config, skip verification
        if (! config('services.turnstile.enabled', false)) {
            return $next($request);
        }

        $token = (string) $request->input('cf-turnstile-response', '');

        if ($token === '') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please complete the security verification.',
                ], 422);
            }

            return back()->withErrors([
                'captcha' => 'Please complete the security verification.',
            ]);
        }

        if (! $this->turnstileService->verify($token, $request->ip())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Security verification failed. Please try again.',
                ], 422);
            }

            return back()->withErrors([
                'captcha' => 'Security verification failed. Please try again.',
            ]);
        }

        return $next($request);
    }
}
