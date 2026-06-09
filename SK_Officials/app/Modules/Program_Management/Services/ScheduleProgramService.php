<?php

namespace App\Modules\Program_Management\Services;

use App\Models\Abyip;
use App\Models\Committee;
use App\Models\ScheduleProgram;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScheduleProgramService
{
    public const LETTER_EDUCATION = 'A';

    public const LETTER_SPORTS = 'I';

    /** @var array<string, array{default_type: string, default_committee: string, committee_like: string}> */
    private const LETTER_CONFIG = [
        self::LETTER_EDUCATION => [
            'default_type' => 'Equitable Access to Quality Education',
            'default_committee' => 'Education Committee',
            'committee_like' => '%education%',
        ],
        self::LETTER_SPORTS => [
            'default_type' => 'Sports Development',
            'default_committee' => 'Sports Committee',
            'committee_like' => '%sport%',
        ],
    ];

    /** @var list<string> */
    private const ALLOWED_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix', 'full_name',
        'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
        'region', 'province', 'city', 'barangay', 'purok_zone',
        'youth_classification', 'youth_age_group', 'education', 'current_school',
        'course_strand', 'work_status', 'sk_voter', 'sk_voted',
    ];

    public function __construct(private readonly AbyipProgramCatalogService $catalogService)
    {
    }

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveEducationProgramMeta(User $user): array
    {
        return $this->resolveProgramMeta($user, self::LETTER_EDUCATION);
    }

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveSportsProgramMeta(User $user): array
    {
        return $this->resolveProgramMeta($user, self::LETTER_SPORTS);
    }

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveProgramMeta(User $user, string $letter): array
    {
        $letter = strtoupper(trim($letter));
        if (! isset(self::LETTER_CONFIG[$letter])) {
            throw ValidationException::withMessages([
                'program_letter' => ['Unsupported program letter.'],
            ]);
        }

        $config = self::LETTER_CONFIG[$letter];
        $programType = $config['default_type'];
        $abyip = $this->catalogService->getLatestAbyip($user->barangay_id);

        if ($abyip !== null) {
            $program = Abyip::query()
                ->where('document_id', $abyip->id)
                ->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                ->where('code', $letter)
                ->first();

            if ($program !== null && trim((string) $program->program_name) !== '') {
                $programType = trim((string) $program->program_name);
            }
        }

        $committee = $config['default_committee'];
        if ($user->barangay_id !== null) {
            $committeeRow = Committee::query()
                ->whereHas('head', function ($query) use ($user) {
                    $query->where('barangay_id', $user->barangay_id);
                })
                ->whereRaw('LOWER(committee_name) LIKE ?', [$config['committee_like']])
                ->value('committee_name');

            if (is_string($committeeRow) && trim($committeeRow) !== '') {
                $committee = trim($committeeRow);
            }
        }

        return [
            'program_type' => $programType,
            'committee' => $committee,
            'program_name' => $programType,
            'program_letter' => $letter,
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

        $query = ScheduleProgram::query()
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        if ($letter !== null && $letter !== '') {
            $query->where('program_letter', strtoupper($letter));
        }

        return $query->get()->map(fn (ScheduleProgram $program) => $this->formatProgram($program));
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $programId): array
    {
        return $this->formatProgram($this->findModel($user, $programId));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function store(User $user, array $data, string $letter = self::LETTER_EDUCATION): array
    {
        $validated = $this->validatePayload($data);
        $meta = $this->resolveProgramMeta($user, $letter);

        $program = ScheduleProgram::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'program_letter' => $meta['program_letter'],
            'program_type' => $meta['program_type'],
            'committee' => $meta['committee'],
            'program_name' => $meta['program_name'],
            'participation_quantity' => $validated['participation_quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'announcement' => $validated['announcement'],
            'kk_profiling_fields' => $validated['kk_profiling_fields'],
            'custom_questions' => $validated['custom_questions'],
        ]);

        return $this->formatProgram($program);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, int $programId, array $data, ?string $letter = null): array
    {
        $program = $this->findModel($user, $programId);
        $validated = $this->validatePayload($data);
        $meta = $this->resolveProgramMeta($user, $letter ?? (string) ($program->program_letter ?? self::LETTER_EDUCATION));

        $program->update([
            'program_letter' => $meta['program_letter'],
            'program_type' => $meta['program_type'],
            'committee' => $meta['committee'],
            'program_name' => $meta['program_name'],
            'participation_quantity' => $validated['participation_quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'announcement' => $validated['announcement'],
            'kk_profiling_fields' => $validated['kk_profiling_fields'],
            'custom_questions' => $validated['custom_questions'],
        ]);

        return $this->formatProgram($program->fresh());
    }

    public function delete(User $user, int $programId): void
    {
        $this->findModel($user, $programId)->delete();
    }

    protected function findModel(User $user, int $programId): ScheduleProgram
    {
        $program = ScheduleProgram::query()
            ->where('id', $programId)
            ->where('barangay_id', $user->barangay_id)
            ->first();

        if ($program === null) {
            throw ValidationException::withMessages([
                'program' => ['Schedule program not found.'],
            ]);
        }

        return $program;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     participation_quantity: ?int,
     *     start_date: string,
     *     end_date: string,
     *     status: string,
     *     announcement: ?string,
     *     kk_profiling_fields: list<string>,
     *     custom_questions: list<array<string, mixed>>
     * }
     */
    protected function validatePayload(array $data): array
    {
        $startDate = (string) ($data['start_date'] ?? '');
        $endDate = (string) ($data['end_date'] ?? '');
        $status = (string) ($data['status'] ?? ScheduleProgram::STATUS_OPEN);

        if ($startDate === '' || $endDate === '') {
            throw ValidationException::withMessages([
                'start_date' => ['Start date and end date are required.'],
            ]);
        }

        if ($endDate < $startDate) {
            throw ValidationException::withMessages([
                'end_date' => ['End date cannot be before start date.'],
            ]);
        }

        if (! in_array($status, [ScheduleProgram::STATUS_OPEN, ScheduleProgram::STATUS_CLOSED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Status must be open or closed.'],
            ]);
        }

        $qtyRaw = $data['participation_quantity'] ?? null;
        $participationQuantity = null;
        if ($qtyRaw !== null && $qtyRaw !== '') {
            $qty = (int) $qtyRaw;
            if ($qty < 0 || $qty > 500) {
                throw ValidationException::withMessages([
                    'participation_quantity' => ['Participation quantity must be between 0 and 500.'],
                ]);
            }
            $participationQuantity = $qty;
        }

        $kkFields = array_values(array_filter(
            (array) ($data['kk_profiling_fields'] ?? []),
            fn ($field) => in_array((string) $field, self::ALLOWED_KK_FIELDS, true)
        ));

        $customQuestions = $this->sanitizeCustomQuestions((array) ($data['custom_questions'] ?? []));

        return [
            'participation_quantity' => $participationQuantity,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'announcement' => $this->nullableString($data['announcement'] ?? null),
            'kk_profiling_fields' => $kkFields,
            'custom_questions' => $customQuestions,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    protected function sanitizeCustomQuestions(array $questions): array
    {
        $allowedTypes = ['text', 'paragraph', 'number', 'checkbox', 'radio', 'file'];
        $sanitized = [];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $label = trim((string) ($question['label'] ?? $question['question'] ?? ''));
            if ($label === '') {
                continue;
            }

            $type = (string) ($question['type'] ?? 'text');
            if (! in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $options = [];
            if (in_array($type, ['checkbox', 'radio'], true)) {
                $options = array_values(array_filter(array_map(
                    fn ($option) => trim((string) $option),
                    (array) ($question['options'] ?? [])
                )));
                if ($options === []) {
                    $options = ['Option 1'];
                }
            }

            $sanitized[] = [
                'id' => (string) ($question['id'] ?? ('q_'.uniqid())),
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'required' => (bool) ($question['required'] ?? false),
            ];
        }

        return $sanitized;
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatProgram(ScheduleProgram $program): array
    {
        return [
            'id' => $program->id,
            'program_letter' => $program->program_letter,
            'program_name' => $program->program_name,
            'program_type' => $program->program_type,
            'committee' => $program->committee,
            'participation_quantity' => $program->participation_quantity,
            'participationQty' => $program->participation_quantity,
            'start_date' => $program->start_date?->toDateString(),
            'end_date' => $program->end_date?->toDateString(),
            'startDate' => $program->start_date?->toDateString(),
            'endDate' => $program->end_date?->toDateString(),
            'status' => $program->status,
            'announcement' => $program->announcement,
            'kk_profiling_fields' => $program->kk_profiling_fields ?? [],
            'kkProfilingFields' => $program->kk_profiling_fields ?? [],
            'custom_questions' => $program->custom_questions ?? [],
            'customQuestions' => $program->custom_questions ?? [],
            'created_at' => $program->created_at?->toIso8601String(),
            'updated_at' => $program->updated_at?->toIso8601String(),
        ];
    }
}
