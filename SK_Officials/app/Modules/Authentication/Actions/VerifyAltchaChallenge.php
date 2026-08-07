<?php

namespace App\Modules\Authentication\Actions;

use App\Modules\Authentication\Services\AltchaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyAltchaChallenge
{
    public function __construct(
        protected AltchaService $altchaService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(Request $request, callable $next): mixed
    {
        if (! $this->altchaService->enabled()) {
            return $next($request);
        }

        $result = $this->altchaService->verify($request->input('altcha'));

        if ($result->verified) {
            return $next($request);
        }

        throw ValidationException::withMessages([
            'altcha' => [$result->message()],
        ]);
    }
}
