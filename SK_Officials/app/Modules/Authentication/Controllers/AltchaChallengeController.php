<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Services\AltchaService;
use Illuminate\Http\JsonResponse;

class AltchaChallengeController extends Controller
{
    public function __construct(
        protected AltchaService $altchaService,
    ) {}

    public function __invoke(): JsonResponse
    {
        if (! $this->altchaService->enabled()) {
            return response()->json([
                'message' => 'Security verification is not available.',
            ], 503);
        }

        $challenge = $this->altchaService->createChallenge();

        return response()->json($challenge->toArray());
    }
}
