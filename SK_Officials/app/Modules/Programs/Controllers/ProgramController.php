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
        $year = (int) now()->year;
        $yearStart = sprintf('%d-01-01', $year);
        $yearEnd = sprintf('%d-12-31', $year);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:'.$yearStart, 'before_or_equal:'.$yearEnd],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:'.$yearEnd],
        ], [
            'start_date.after_or_equal' => 'Start date must be within the current year (January 1 – December 31).',
            'start_date.before_or_equal' => 'Start date must be within the current year (January 1 – December 31).',
            'end_date.before_or_equal' => 'End date must be within the current year (January 1 – December 31).',
        ]);

        $user = $request->user();

        if ($user->barangay_id === null) {
            return response()->json([
                'message' => 'Your account is not linked to a barangay.',
            ], 422);
        }

        if (! $this->catalogService->programBelongsToBarangay((int) $user->barangay_id, $programId)) {
            return response()->json([
                'message' => 'Program not found for your barangay.',
            ], 422);
        }

        $duration = $this->catalogService->upsertProgramDuration(
            (int) $user->barangay_id,
            $programId,
            $validated['start_date'],
            $validated['end_date'],
        );

        return response()->json([
            'message' => 'Program duration updated.',
            'data' => [
                'id' => $programId,
                'startDate' => $duration['startDate'],
                'endDate' => $duration['endDate'],
                'status' => $duration['status'],
            ],
        ]);
    }
}
