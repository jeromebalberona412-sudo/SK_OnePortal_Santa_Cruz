<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ProgramEvaluation;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProgramEvaluationService
{
    /** @var list<string> */
    private const ALLOWED_STATUSES = [
        ProgramEvaluation::STATUS_OPEN,
        ProgramEvaluation::STATUS_CLOSED,
    ];

    public function __construct(private readonly AbyipProgramCatalogService $catalogService) {}

    /**
     * @return array<string, mixed>
     */
    public function resolveProgramContext(User $user, string $letter): array
    {
        $letter = strtoupper(trim($letter));
        $document = $this->catalogService->getLatestAbyip($user->barangay_id);
        $calendarYear = $document?->fiscal_year !== null ? (int) $document->fiscal_year : null;
        $program = $this->catalogService->findYouthProgramByLetter($user->barangay_id, $letter);

        if ($program === null) {
            return [
                'program_letter' => $letter,
                'calendar_year' => $calendarYear,
                'abyip_id' => $document?->id,
                'program' => null,
                'can_create' => false,
                'create_blocked_reason' => 'No ABYIP program found for this committee. Upload ABYIP first.',
                'has_evaluation_for_year' => false,
            ];
        }

        $evaluationYear = $calendarYear ?? (int) date('Y');
        $hasEvaluationForYear = $this->hasEvaluationForProgramYear(
            (int) $user->barangay_id,
            (int) $program['id'],
            $evaluationYear,
        );

        $programCompleted = $this->isProgramCompleted($program['end_date']);
        $canCreate = $programCompleted && ! $hasEvaluationForYear;

        $createBlockedReason = null;
        if (! $programCompleted) {
            $createBlockedReason = 'Evaluation can only be created after the program period ends on '
                .Carbon::parse($program['end_date'])->format('M j, Y').'.';
        } elseif ($hasEvaluationForYear) {
            $createBlockedReason = "An evaluation for {$program['program_name']} already exists for {$evaluationYear}.";
        }

        return [
            'program_letter' => $letter,
            'calendar_year' => $calendarYear,
            'abyip_id' => $document?->id,
            'program' => $program,
            'can_create' => $canCreate,
            'create_blocked_reason' => $createBlockedReason,
            'has_evaluation_for_year' => $hasEvaluationForYear,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForBarangay(User $user, ?string $letter = null): Collection
    {
        if ($user->barangay_id === null) {
            return collect();
        }

        $letter = $letter ? strtoupper(trim($letter)) : null;

        $query = ProgramEvaluation::query()
            ->with(['abyipProgram', 'scheduleProgram', 'creator'])
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('created_at');

        if ($letter !== null) {
            $query->where('program_letter', $letter);
        }

        return $query->get()->map(fn (ProgramEvaluation $evaluation) => $this->format($evaluation));
    }

    /**
     * @return array{total: int, open: int, closed: int}
     */
    public function summarizeForBarangay(User $user, ?string $letter = null): array
    {
        if ($user->barangay_id === null) {
            return ['total' => 0, 'open' => 0, 'closed' => 0];
        }

        $letter = $letter ? strtoupper(trim($letter)) : null;

        $baseQuery = ProgramEvaluation::query()->where('barangay_id', $user->barangay_id);

        if ($letter !== null) {
            $baseQuery->where('program_letter', $letter);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', ProgramEvaluation::STATUS_OPEN)->count(),
            'closed' => (clone $baseQuery)->where('status', ProgramEvaluation::STATUS_CLOSED)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $id, ?string $letter = null): array
    {
        return $this->format($this->findModel($user, $id, $letter), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function store(User $user, array $payload, string $letter): array
    {
        $letter = strtoupper(trim($letter));
        $context = $this->resolveProgramContext($user, $letter);
        $program = $context['program'] ?? null;

        if ($program === null) {
            throw ValidationException::withMessages([
                'program' => [$context['create_blocked_reason'] ?? 'Program not found.'],
            ]);
        }

        if (! $context['can_create']) {
            throw ValidationException::withMessages([
                'evaluation' => [$context['create_blocked_reason'] ?? 'Cannot create evaluation at this time.'],
            ]);
        }

        $validated = $this->validatePayload($payload, $program);
        $evaluationYear = (int) Carbon::parse($program['start_date'])->format('Y');

        $this->assertOneEvaluationPerProgramYear(
            (int) $user->barangay_id,
            (int) $program['id'],
            $evaluationYear,
        );

        $status = $this->resolveStatus(
            $validated['status'],
            $program['start_date'],
            $program['end_date'],
        );

        $evaluation = ProgramEvaluation::query()->create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'program_letter' => $letter,
            'abyip_program_id' => (int) $program['id'],
            'title' => $program['program_name'],
            'instructions' => $validated['instructions'] ?? null,
            'custom_questions' => $validated['custom_questions'] ?? [],
            'status' => $status,
            'start_date' => $program['start_date'],
            'end_date' => $program['end_date'],
            'due_date' => $program['end_date'],
        ]);

        return $this->format($evaluation->fresh(['abyipProgram', 'creator']), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(User $user, int $id, array $payload, ?string $letter = null): array
    {
        $evaluation = $this->findModel($user, $id, $letter);
        $context = $this->resolveProgramContext($user, $evaluation->program_letter);
        $program = $context['program'] ?? null;

        if ($program === null) {
            throw ValidationException::withMessages([
                'program' => ['Program not found for this evaluation.'],
            ]);
        }

        $validated = $this->validatePayload($payload, $program, true);

        $status = $this->resolveStatus(
            $validated['status'],
            $program['start_date'],
            $program['end_date'],
        );

        $evaluation->update([
            'title' => $program['program_name'],
            'instructions' => array_key_exists('instructions', $validated)
                ? $validated['instructions']
                : $evaluation->instructions,
            'custom_questions' => array_key_exists('custom_questions', $validated)
                ? ($validated['custom_questions'] ?? [])
                : $evaluation->custom_questions,
            'status' => $status,
            'start_date' => $program['start_date'],
            'end_date' => $program['end_date'],
            'due_date' => $program['end_date'],
        ]);

        return $this->format($evaluation->fresh(['abyipProgram', 'creator']), true);
    }

    public function delete(User $user, int $id, ?string $letter = null): void
    {
        $this->findModel($user, $id, $letter)->delete();
    }

    protected function findModel(User $user, int $id, ?string $letter = null): ProgramEvaluation
    {
        if ($user->barangay_id === null) {
            throw ValidationException::withMessages([
                'evaluation_id' => ['Evaluation not found.'],
            ]);
        }

        $query = ProgramEvaluation::query()
            ->whereKey($id)
            ->where('barangay_id', $user->barangay_id);

        if ($letter !== null && trim($letter) !== '') {
            $query->where('program_letter', strtoupper(trim($letter)));
        }

        $evaluation = $query->first();

        if ($evaluation === null) {
            throw ValidationException::withMessages([
                'evaluation_id' => ['Evaluation not found.'],
            ]);
        }

        return $evaluation;
    }

    /**
     * @param  array<string, mixed>  $program
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function validatePayload(array $payload, array $program, bool $isUpdate = false): array
    {
        $status = strtolower(trim((string) ($payload['status'] ?? ProgramEvaluation::STATUS_OPEN)));

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Status must be open or closed.'],
            ]);
        }

        $customQuestions = $payload['custom_questions'] ?? [];
        if (! is_array($customQuestions)) {
            throw ValidationException::withMessages([
                'custom_questions' => ['Evaluation questions must be a valid list.'],
            ]);
        }

        return [
            'instructions' => isset($payload['instructions']) ? trim((string) $payload['instructions']) : null,
            'custom_questions' => array_values($customQuestions),
            'status' => $status,
        ];
    }

    private function assertOneEvaluationPerProgramYear(
        int $barangayId,
        int $programId,
        int $year,
        ?int $ignoreEvaluationId = null,
    ): void {
        if ($this->hasEvaluationForProgramYear($barangayId, $programId, $year, $ignoreEvaluationId)) {
            throw ValidationException::withMessages([
                'evaluation' => "An evaluation already exists for this program in {$year}.",
            ]);
        }
    }

    private function hasEvaluationForProgramYear(
        int $barangayId,
        int $programId,
        int $year,
        ?int $ignoreEvaluationId = null,
    ): bool {
        return ProgramEvaluation::query()
            ->where('barangay_id', $barangayId)
            ->where('abyip_program_id', $programId)
            ->whereYear('start_date', $year)
            ->when($ignoreEvaluationId !== null, fn ($query) => $query->where('id', '!=', $ignoreEvaluationId))
            ->exists();
    }

    private function isProgramCompleted(string $endDate): bool
    {
        return now()->startOfDay()->gt(Carbon::parse($endDate));
    }

    private function resolveStatus(string $requestedStatus, string $startDate, string $endDate): string
    {
        $requestedStatus = strtolower(trim($requestedStatus));
        if (! in_array($requestedStatus, self::ALLOWED_STATUSES, true)) {
            $requestedStatus = ProgramEvaluation::STATUS_OPEN;
        }

        if ($requestedStatus === ProgramEvaluation::STATUS_CLOSED) {
            return ProgramEvaluation::STATUS_CLOSED;
        }

        $now = Carbon::now();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($now->gt($end)) {
            return ProgramEvaluation::STATUS_CLOSED;
        }

        return ProgramEvaluation::STATUS_OPEN;
    }

    /**
     * @return array<string, mixed>
     */
    protected function format(ProgramEvaluation $evaluation, bool $withDetails = false): array
    {
        $questions = is_array($evaluation->custom_questions) ? $evaluation->custom_questions : [];

        $programName = trim((string) ($evaluation->abyipProgram?->program_name ?? ''));
        if ($programName === '') {
            $programName = $evaluation->scheduleProgram?->program_name
                ?? $evaluation->scheduleProgram?->program_type
                ?? $evaluation->title
                ?? 'Program';
        }

        $startDate = $evaluation->start_date?->toDateString()
            ?? $evaluation->scheduleProgram?->start_date?->toDateString();
        $endDate = $evaluation->end_date?->toDateString()
            ?? $evaluation->scheduleProgram?->end_date?->toDateString()
            ?? $evaluation->due_date?->toDateString();

        $normalizedStatus = in_array($evaluation->status, self::ALLOWED_STATUSES, true)
            ? $evaluation->status
            : ProgramEvaluation::STATUS_OPEN;

        $formatted = [
            'id' => $evaluation->id,
            'evaluation_code' => 'EVAL-'.str_pad((string) $evaluation->id, 4, '0', STR_PAD_LEFT),
            'title' => $programName,
            'program_letter' => $evaluation->program_letter,
            'program_name' => $programName,
            'abyip_program_id' => $evaluation->abyip_program_id,
            'schedule_program_id' => $evaluation->schedule_program_id,
            'status' => $normalizedStatus,
            'status_label' => ucfirst($normalizedStatus),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_date_display' => $startDate ? Carbon::parse($startDate)->format('M j, Y') : '—',
            'end_date_display' => $endDate ? Carbon::parse($endDate)->format('M j, Y') : '—',
            'due_date' => $endDate,
            'due_date_display' => $endDate ? Carbon::parse($endDate)->format('M j, Y') : '—',
            'date_created' => $evaluation->created_at?->toDateString(),
            'date_created_display' => $evaluation->created_at?->format('M j, Y') ?? '—',
            'evaluation_year' => $startDate ? (int) Carbon::parse($startDate)->format('Y') : null,
            'questions_count' => count($questions),
            'created_by_name' => $evaluation->creator?->name ?? 'SK Official',
        ];

        if ($withDetails) {
            $formatted['instructions'] = $evaluation->instructions;
            $formatted['custom_questions'] = $questions;
        }

        return $formatted;
    }
}
