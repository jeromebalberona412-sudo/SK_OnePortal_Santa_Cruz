<?php

namespace App\Modules\Programs\Controllers;

use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProgramController extends Controller
{
    public function __construct(private readonly AbyipProgramCatalogService $catalogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->catalogService->listForProgramsPage($request->user()),
        ]);
    }

    public function management(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->catalogService->listForManagement($request->user()),
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
