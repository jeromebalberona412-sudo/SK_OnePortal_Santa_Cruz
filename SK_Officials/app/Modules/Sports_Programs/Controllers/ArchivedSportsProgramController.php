<?php

namespace App\Modules\Sports_Programs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ScheduleProgram;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use App\Modules\Sports_Programs\Services\SportsProgramArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArchivedSportsProgramController extends Controller
{
    public function __construct(
        private readonly SportsProgramArchiveService $archiveService,
        private readonly ScheduleProgramService $scheduleService,
    ) {
    }

    public function index(): View
    {
        return view('Sports_Programs::archived.archived-sports-programs');
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user === null || $user->barangay_id === null) {
            return response()->json(['data' => [], 'stats' => ['total' => 0, 'expiring_soon' => 0]]);
        }

        $query = ScheduleProgram::query()
            ->where('barangay_id', $user->barangay_id)
            ->where('program_letter', ScheduleProgramService::LETTER_SPORTS)
            ->archived()
            ->orderByDesc('archived_at')
            ->orderByDesc('id');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('program_name', 'ilike', "%{$search}%")
                    ->orWhere('program_type', 'ilike', "%{$search}%");
            });
        }

        $programs = $query->get()->map(fn (ScheduleProgram $program) => $this->formatArchivedProgram($program));

        $allArchived = ScheduleProgram::query()
            ->where('barangay_id', $user->barangay_id)
            ->where('program_letter', ScheduleProgramService::LETTER_SPORTS)
            ->archived();

        return response()->json([
            'data' => $programs,
            'stats' => [
                'total' => (clone $allArchived)->count(),
                'expiring_soon' => (clone $allArchived)
                    ->whereNotNull('archived_at')
                    ->where('archived_at', '<=', now()->subDays(SportsProgramArchiveService::RETENTION_DAYS - 7))
                    ->count(),
            ],
        ]);
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        try {
            $programId = $this->parseProgramId($id);
            $program = $this->findSportsProgram($request->user(), $programId);

            $validated = $request->validate([
                'deleted_reason' => ['nullable', 'string', 'max:500'],
            ]);

            $archived = $this->archiveService->archive(
                $request->user(),
                $program,
                $validated['deleted_reason'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Sports program moved to archive.',
                'data' => $this->scheduleService->formatProgramPublic($archived),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function restore(Request $request, string $id): JsonResponse
    {
        try {
            $programId = $this->parseProgramId($id);
            $program = $this->findSportsProgram($request->user(), $programId, archived: true);
            $restored = $this->archiveService->restore($request->user(), $program);

            return response()->json([
                'success' => true,
                'message' => 'Sports program restored successfully.',
                'data' => $this->scheduleService->formatProgramPublic($restored),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $programId = $this->parseProgramId($id);
            $program = $this->findSportsProgram($request->user(), $programId, archived: true);
            $this->archiveService->permanentlyDelete($request->user(), $program);

            return response()->json([
                'success' => true,
                'message' => 'Sports program permanently deleted.',
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    protected function findSportsProgram($user, int $programId, bool $archived = false): ScheduleProgram
    {
        $query = ScheduleProgram::query()
            ->whereKey($programId)
            ->where('barangay_id', $user->barangay_id)
            ->where('program_letter', ScheduleProgramService::LETTER_SPORTS);

        if ($archived) {
            $query->archived();
        } else {
            $query->active();
        }

        $program = $query->first();
        if ($program === null) {
            throw ValidationException::withMessages([
                'program' => ['Sports program not found.'],
            ]);
        }

        return $program;
    }

    protected function formatArchivedProgram(ScheduleProgram $program): array
    {
        $formatted = $this->scheduleService->formatProgramPublic($program);

        return array_merge($formatted, [
            'archived_at' => $program->archived_at?->toIso8601String(),
            'archived_date' => $program->archived_at?->format('M j, Y') ?? '—',
            'archived_time' => $program->archived_at?->format('g:i A') ?? '—',
            'days_remaining' => $this->archiveService->daysRemaining($program),
            'can_permanently_delete' => ! $this->archiveService->hasHistoricalRecords($program),
            'has_historical_records' => $this->archiveService->hasHistoricalRecords($program),
        ]);
    }

    protected function parseProgramId(string $id): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            throw ValidationException::withMessages([
                'program' => ['Invalid program id.'],
            ]);
        }

        return (int) $id;
    }

    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => collect($exception->errors())->flatten()->first(),
            'errors' => $exception->errors(),
        ], 422);
    }
}
