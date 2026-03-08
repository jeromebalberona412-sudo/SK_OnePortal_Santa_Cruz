<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\TurnstileService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyTurnstile
{
    public function __construct(
        protected TurnstileService $turnstileService,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! (bool) config('services.turnstile.enabled', true)) {
            return $next($request);
        }

        $responseToken = (string) $request->input('cf-turnstile-response', '');

        if (! $this->turnstileService->verify($responseToken, $request->ip())) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Bot verification failed.',
            ]);
        }

        return $next($request);
    }
}
