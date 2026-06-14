<?php

namespace App\Modules\Programs\Controllers;

use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProgramController extends Controller
{
    public function __construct(private readonly AbyipProgramCatalogService $catalogService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->catalogService->listForProgramsPage($user),
            'abyip_gate' => $this->catalogService->resolveAccessGate($user->barangay_id),
        ]);
    }

    public function management(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->catalogService->listForManagement($user),
            'abyip_gate' => $this->catalogService->resolveAccessGate($user->barangay_id),
        ]);
    }

    public function updateDuration(Request $request, int $programId): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        return response()->json([
            'message' => 'Program duration updated.',
            'data' => [
                'id' => $programId,
                'startDate' => $validated['start_date'],
                'endDate' => $validated['end_date'],
            ],
        ]);
    }
}
