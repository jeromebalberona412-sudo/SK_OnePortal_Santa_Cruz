<?php

namespace App\Modules\Authentication\Middleware;

use App\Modules\Authentication\Services\AltchaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class VerifyAltcha
{
    public function __construct(
        protected AltchaService $altchaService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->altchaService->enabled()) {
            return $next($request);
        }

        $result = $this->altchaService->verify($request->input('altcha'));

        if ($result->verified) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $result->message(),
                'errors' => [
                    'altcha' => [$result->message()],
                ],
            ], 422);
        }

        throw ValidationException::withMessages([
            'altcha' => [$result->message()],
        ]);
    }
}
