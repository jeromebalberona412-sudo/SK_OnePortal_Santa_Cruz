<?php

namespace App\Modules\Program_Accomplishment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Accomplishment\Services\ProgramAccomplishmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramAccomplishmentController extends Controller
{
    public function __construct(
        private readonly ProgramAccomplishmentService $accomplishmentService
    ) {}

    public function index()
    {
        $user = Auth::user();
        
        if (!$user || !$user->barangay_id) {
            return redirect()->route('dashboard')->with('error', 'Unable to load accomplishment reports.');
        }

        return view('Program_Accomplishment::Program_Accomplishment');
    }

    public function data(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                \Log::warning('Program Accomplishment data fetch: No authenticated user');
                return response()->json([
                    'programs' => [],
                    'accomplishmentReports' => [],
                    'images' => [],
                    'error' => 'No authenticated user'
                ], 401);
            }

            if (!$user->barangay_id) {
                \Log::warning('Program Accomplishment data fetch: User has no barangay_id', [
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'programs' => [],
                    'accomplishmentReports' => [],
                    'images' => [],
                    'error' => 'User has no barangay assigned'
                ], 403);
            }

            $programs = $this->accomplishmentService->getCompletedPrograms((int) $user->barangay_id, $user);
            $accomplishmentReports = $this->accomplishmentService->getAccomplishmentReports($user->barangay_id);
            $images = $this->accomplishmentService->getAllImages($user->barangay_id);

            return response()->json([
                'programs' => $programs,
                'accomplishmentReports' => $accomplishmentReports,
                'images' => $images,
            ]);
        } catch (\Exception $e) {
            \Log::error('Program Accomplishment data fetch error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'programs' => [],
                'accomplishmentReports' => [],
                'images' => [],
                'error' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $data = $request->validate([
                'program_id' => 'required|integer|exists:schedule_programs,id',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'implementation_summary' => 'nullable|string',
                'actual_result' => 'nullable|string',
                'lessons_learned' => 'nullable|string',
                'recommendations' => 'nullable|string',
                'participants_count' => 'nullable|integer|min:0',
                'target_beneficiaries' => 'nullable|integer|min:0',
                'actual_expense' => 'nullable|numeric|min:0',
                'actual_implementation_date' => 'nullable|date',
                'actual_completion_date' => 'nullable|date',
                'remarks' => 'nullable|string',
                'save_as' => 'nullable|in:draft,submitted',
                'images' => 'nullable|array',
                'images.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:10240',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
                'document_types' => 'nullable|array',
                'document_types.*' => 'nullable|string|max:80',
            ]);

            $report = $this->accomplishmentService->createReport(
                $user->barangay_id,
                $user->id,
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Accomplishment submitted successfully.',
                'report' => $report,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $report = $this->accomplishmentService->getReportById($id);
            $reportBarangayId = (int) ($report['barangay_id'] ?? $report['program']['barangay_id'] ?? 0);

            if ($reportBarangayId > 0 && $reportBarangayId !== (int) $user->barangay_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Report not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $data = $request->validate([
                'title' => 'sometimes|nullable|string|max:255',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'implementation_summary' => 'sometimes|nullable|string',
                'actual_result' => 'nullable|string',
                'lessons_learned' => 'nullable|string',
                'recommendations' => 'nullable|string',
                'participants_count' => 'sometimes|nullable|integer|min:0',
                'target_beneficiaries' => 'nullable|integer|min:0',
                'actual_expense' => 'sometimes|nullable|numeric|min:0',
                'actual_implementation_date' => 'nullable|date',
                'actual_completion_date' => 'nullable|date',
                'remarks' => 'nullable|string',
                'save_as' => 'nullable|in:draft,submitted',
                'new_images' => 'nullable|array',
                'new_images.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:10240',
                'delete_images' => 'nullable|array',
                'delete_images.*' => 'integer',
                'update_images' => 'nullable|array',
                'update_images.*.id' => 'required|integer',
                'update_images.*.caption' => 'nullable|string',
                'update_images.*.sort_order' => 'sometimes|required|integer',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
                'document_types' => 'nullable|array',
                'document_types.*' => 'nullable|string|max:80',
                'delete_documents' => 'nullable|array',
                'delete_documents.*' => 'integer',
            ]);

            $report = $this->accomplishmentService->updateReport(
                $id,
                $user->barangay_id,
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Accomplishment report updated successfully.',
                'report' => $report,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Report not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $this->accomplishmentService->deleteReport($id, $user->barangay_id);

            return response()->json([
                'success' => true,
                'message' => 'Accomplishment report deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Report not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function prepareFromCatalog(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'abyip_program_id' => 'required|integer',
        ]);

        try {
            $prepared = $this->accomplishmentService->prepareFromCatalog(
                (int) $user->barangay_id,
                (int) $user->id,
                (int) $data['abyip_program_id'],
            );

            return response()->json([
                'success' => true,
                'data' => $prepared,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    public function publish(int $id)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $report = $this->accomplishmentService->publishReport($id, (int) $user->barangay_id);

            return response()->json([
                'success' => true,
                'message' => 'Accomplishment published to the public homepage.',
                'report' => $report,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Report not found.',
            ], 404);
        }
    }

    public function edit(int $id)
    {
        return redirect()->route('program-accomplishment.index', ['edit' => $id]);
    }
}