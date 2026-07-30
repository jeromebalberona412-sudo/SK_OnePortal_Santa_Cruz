<?php

namespace App\Modules\Program_Accomplishment\Controllers;

use App\Models\Abyip;
use App\Models\AbyipProgramDuration;
use App\Modules\Program_Accomplishment\Requests\StoreProgramAccomplishmentRequest;
use App\Modules\Program_Accomplishment\Services\ProgramAccomplishmentService;
use App\Modules\Program_Accomplishment\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class ProgramAccomplishmentController extends Controller
{
    public function __construct(
        private readonly ProgramAccomplishmentService $service,
        private readonly StatisticsService $statisticsService,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('Program_Accomplishment::index');
    }

    public function create(Request $request): \Illuminate\View\View
    {
        return view('Program_Accomplishment::create', [
            'program_id' => $request->query('program_id'),
        ]);
    }

    public function edit(int $id): \Illuminate\View\View
    {
        $report = $this->service->findById($id);
        return view('Program_Accomplishment::edit', compact('report'));
    }

    public function show(int $id): \Illuminate\View\View
    {
        $report = $this->service->findById($id);
        return view('Program_Accomplishment::show', compact('report'));
    }

    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->service->listForBarangay(
            $user->barangay_id,
            $request->query('status'),
            $request->query('search'),
            $request->query('year') ? (int) $request->query('year') : null
        );

        return response()->json($result);
    }

    public function showApi(int $id): JsonResponse
    {
        $report = $this->service->findById($id);
        return response()->json(['data' => $report]);
    }

    public function store(StoreProgramAccomplishmentRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $report = $this->service->create(
                array_merge($request->validated(), [
                    'barangay_id' => $user->barangay_id,
                    'created_by' => $user->id,
                ])
            );

            return response()->json([
                'message' => 'Accomplishment report created successfully.',
                'data' => $report,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'budget' => [$e->getMessage()],
            ]);
        }
    }

    public function update(StoreProgramAccomplishmentRequest $request, int $id): JsonResponse
    {
        try {
            $report = $this->service->update($id, $request->validated());

            return response()->json([
                'message' => 'Accomplishment report updated successfully.',
                'data' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'budget' => [$e->getMessage()],
            ]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Accomplishment report deleted successfully.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function submit(int $id): JsonResponse
    {
        try {
            $report = $this->service->submit($id);
            return response()->json([
                'message' => 'Accomplishment report submitted successfully.',
                'data' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        $report = $this->service->approve($id);
        return response()->json([
            'message' => 'Accomplishment report approved successfully.',
            'data' => $report,
        ]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:5000']);
        $report = $this->service->reject($id, $request->input('reason'));
        return response()->json([
            'message' => 'Accomplishment report rejected.',
            'data' => $report,
        ]);
    }

    public function publish(int $id): JsonResponse
    {
        try {
            $report = $this->service->publish($id);
            return response()->json([
                'message' => 'Accomplishment report published successfully.',
                'data' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function unpublish(int $id): JsonResponse
    {
        try {
            $report = $this->service->unpublish($id);
            return response()->json([
                'message' => 'Accomplishment report unpublished.',
                'data' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = $request->query('year', now()->year);
        $stats = $this->statisticsService->annualStats($user->barangay_id, (int) $year);

        return response()->json(['data' => $stats]);
    }

    public function publicList(Request $request, string $barangaySlug): JsonResponse
    {
        $barangay = \App\Models\Barangay::where('slug', $barangaySlug)->firstOrFail();
        $reports = $this->service->getPublished($barangay->id, 20);

        return response()->json(['data' => $reports]);
    }

    public function publicShow(int $id): \Illuminate\View\View
    {
        $report = $this->service->getPublishedById($id);
        return view('Program_Accomplishment::public.show', compact('report'));
    }

    public function transparency(): \Illuminate\View\View
    {
        return view('Program_Accomplishment::public.transparency');
    }

    public function transparencyData(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = $request->query('year', now()->year);
        $stats = $this->statisticsService->annualStats($user->barangay_id, (int) $year);

        return response()->json(['data' => $stats]);
    }

    public function pendingPrograms(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = \Carbon\Carbon::today();

        $existingIds = \App\Models\ProgramAccomplishmentReport::forBarangay($user->barangay_id)
            ->pluck('program_id');

        $completedViaDuration = AbyipProgramDuration::where('end_date', '<', $today)
            ->pluck('abyip_program_id');

        $programs = Abyip::where('barangay_id', $user->barangay_id)
            ->whereIn('row_type', ['expenditure', 'youth_program', 'activity'])
            ->whereNotIn('id', $existingIds)
            ->where(function ($q) use ($today, $completedViaDuration) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('implementation_end')
                       ->where('implementation_end', '<', $today);
                })->orWhereIn('id', $completedViaDuration);
            })
            ->orderBy('sort_order')
            ->limit(10)
            ->get(['id', 'program_name', 'activity_name', 'description', 'category', 'total', 'mooe', 'co', 'row_type', 'implementation_start', 'implementation_end', 'person_responsible', 'expected_result', 'performance_indicator']);

        return response()->json(['data' => $programs]);
    }

    public function searchPrograms(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->query('search', '');
        $today = \Carbon\Carbon::today();

        $completedViaDuration = AbyipProgramDuration::where('end_date', '<', $today)
            ->pluck('abyip_program_id');

        $query = Abyip::where('barangay_id', $user->barangay_id)
            ->whereIn('row_type', ['expenditure', 'youth_program', 'activity'])
            ->where(function ($q) use ($today, $completedViaDuration) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('implementation_end')
                       ->where('implementation_end', '<', $today);
                })->orWhereIn('id', $completedViaDuration);
            })
            ->where(function ($q) use ($search) {
                $q->where('program_name', 'ilike', "%{$search}%")
                  ->orWhere('activity_name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->limit(20)
            ->get(['id', 'program_name', 'activity_name', 'description', 'category', 'total', 'mooe', 'co', 'row_type', 'implementation_start', 'implementation_end', 'person_responsible', 'expected_result', 'performance_indicator']);

        return response()->json(['data' => $query]);
    }
}
