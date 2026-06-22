<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ProgramEvaluation;
use App\Models\ScheduleProgram;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProgramEvaluationService
{
    /** @var list<string> */
    private const ALLOWED_STATUSES = [
        ProgramEvaluation::STATUS_DRAFT,
        ProgramEvaluation::STATUS_ACTIVE,
        ProgramEvaluation::STATUS_CLOSED,
    ];

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
            ->with(['scheduleProgram', 'creator'])
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('created_at');

        if ($letter !== null) {
            $query->where('program_letter', $letter);
        }

        return $query->get()->map(fn (ProgramEvaluation $evaluation) => $this->format($evaluation));
    }

    /**
     * @return array{total: int, draft: int, active: int, closed: int}
     */
    public function summarizeForBarangay(User $user, ?string $letter = null): array
    {
        if ($user->barangay_id === null) {
            return ['total' => 0, 'draft' => 0, 'active' => 0, 'closed' => 0];
        }

        $letter = $letter ? strtoupper(trim($letter)) : null;

        $baseQuery = ProgramEvaluation::query()->where('barangay_id', $user->barangay_id);

        if ($letter !== null) {
            $baseQuery->where('program_letter', $letter);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', ProgramEvaluation::STATUS_DRAFT)->count(),
            'active' => (clone $baseQuery)->where('status', ProgramEvaluation::STATUS_ACTIVE)->count(),
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
        $validated = $this->validatePayload($payload, $user, $letter);

        $evaluation = ProgramEvaluation::query()->create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'program_letter' => strtoupper(trim($letter)),
            'schedule_program_id' => $validated['schedule_program_id'] ?? null,
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'custom_questions' => $validated['custom_questions'] ?? [],
            'status' => $validated['status'] ?? ProgramEvaluation::STATUS_DRAFT,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return $this->format($evaluation->fresh(['scheduleProgram', 'creator']), true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function update(User $user, int $id, array $payload, ?string $letter = null): array
    {
        $evaluation = $this->findModel($user, $id, $letter);
        $validated = $this->validatePayload($payload, $user, $evaluation->program_letter, $evaluation);

        $evaluation->update([
            'schedule_program_id' => array_key_exists('schedule_program_id', $validated)
                ? $validated['schedule_program_id']
                : $evaluation->schedule_program_id,
            'title' => $validated['title'] ?? $evaluation->title,
            'instructions' => array_key_exists('instructions', $validated)
                ? $validated['instructions']
                : $evaluation->instructions,
            'custom_questions' => array_key_exists('custom_questions', $validated)
                ? ($validated['custom_questions'] ?? [])
                : $evaluation->custom_questions,
            'status' => $validated['status'] ?? $evaluation->status,
            'due_date' => array_key_exists('due_date', $validated)
                ? $validated['due_date']
                : $evaluation->due_date,
        ]);

        return $this->format($evaluation->fresh(['scheduleProgram', 'creator']), true);
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
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function validatePayload(
        array $payload,
        User $user,
        string $letter,
        ?ProgramEvaluation $existing = null,
    ): array {
        $title = trim((string) ($payload['title'] ?? ''));

        if ($title === '') {
            throw ValidationException::withMessages([
                'title' => ['Evaluation title is required.'],
            ]);
        }

        $status = strtolower(trim((string) ($payload['status'] ?? ProgramEvaluation::STATUS_DRAFT)));

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => ['Status must be draft, active, or closed.'],
            ]);
        }

        $scheduleProgramId = $payload['schedule_program_id'] ?? null;
        if ($scheduleProgramId !== null && $scheduleProgramId !== '') {
            $scheduleProgramId = (int) $scheduleProgramId;
            $programExists = ScheduleProgram::query()
                ->whereKey($scheduleProgramId)
                ->where('barangay_id', $user->barangay_id)
                ->where('program_letter', strtoupper(trim($letter)))
                ->exists();

            if (! $programExists) {
                throw ValidationException::withMessages([
                    'schedule_program_id' => ['Selected program is invalid for this barangay.'],
                ]);
            }
        } else {
            $scheduleProgramId = null;
        }

        $customQuestions = $payload['custom_questions'] ?? [];
        if (! is_array($customQuestions)) {
            throw ValidationException::withMessages([
                'custom_questions' => ['Evaluation questions must be a valid list.'],
            ]);
        }

        $dueDate = $payload['due_date'] ?? null;
        if ($dueDate !== null && trim((string) $dueDate) === '') {
            $dueDate = null;
        }

        return [
            'schedule_program_id' => $scheduleProgramId,
            'title' => $title,
            'instructions' => isset($payload['instructions']) ? trim((string) $payload['instructions']) : null,
            'custom_questions' => array_values($customQuestions),
            'status' => $status,
            'due_date' => $dueDate,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function format(ProgramEvaluation $evaluation, bool $withDetails = false): array
    {
        $questions = is_array($evaluation->custom_questions) ? $evaluation->custom_questions : [];

        $formatted = [
            'id' => $evaluation->id,
            'evaluation_code' => 'EVAL-' . str_pad((string) $evaluation->id, 4, '0', STR_PAD_LEFT),
            'title' => $evaluation->title,
            'program_letter' => $evaluation->program_letter,
            'program_name' => $evaluation->scheduleProgram?->program_name
                ?? $evaluation->scheduleProgram?->program_type
                ?? 'General Program',
            'schedule_program_id' => $evaluation->schedule_program_id,
            'status' => $evaluation->status,
            'status_label' => ucfirst($evaluation->status),
            'due_date' => $evaluation->due_date?->toDateString(),
            'due_date_display' => $evaluation->due_date?->format('M j, Y') ?? '—',
            'date_created' => $evaluation->created_at?->toDateString(),
            'date_created_display' => $evaluation->created_at?->format('M j, Y') ?? '—',
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
