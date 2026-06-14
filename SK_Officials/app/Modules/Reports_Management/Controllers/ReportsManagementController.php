<?php

namespace App\Modules\Reports_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use App\Modules\Reports_Management\Services\ReportsManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsManagementController extends Controller
{
    public function __construct(private readonly ReportsManagementService $service) {}

    public function index(): View
    {
        $user = Auth::user();
        $catalog = app(AbyipProgramCatalogService::class);

        return view('Reports_Management::reports-management', [
            'programs' => $this->service->programsCatalog($user),
            'abyipGate' => $catalog->resolveAccessGate($user?->barangay_id),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user?->barangay_id) {
            return response()->json(['data' => []]);
        }

        $reports = $this->service->listForBarangay(
            (int) $user->barangay_id,
            $request->query('search'),
            $request->query('program')
        );

        return response()->json(['data' => $reports]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user?->barangay_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'program_code' => ['required', 'string', 'max:10'],
            'activity_name' => ['required', 'string', 'max:255'],
            'report_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        try {
            $report = $this->service->upload(
                $user,
                $validated['program_code'],
                $validated['activity_name'],
                $validated['report_file']
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'message' => 'Report uploaded successfully.',
            'data' => [
                'id' => $report->id,
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user?->barangay_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->service->deleteForBarangay((int) $user->barangay_id, $id);

        return response()->json(['message' => 'Report deleted.']);
    }

    public function download(int $id): StreamedResponse
    {
        $user = Auth::user();
        if (! $user?->barangay_id) {
            abort(403);
        }

        return $this->service->streamForBarangay(
            (int) $user->barangay_id,
            $id,
            request()->boolean('download')
        );
    }
}
