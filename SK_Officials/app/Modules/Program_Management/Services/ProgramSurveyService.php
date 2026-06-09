<?php

namespace App\Modules\Program_Management\Services;

use App\Models\Abyip;
use App\Models\ProgramSurvey;
use App\Models\ProgramSurveyQuestion;
use App\Models\ProgramSurveyResponse;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgramSurveyService
{
    /** @var array<string, string> */
    private const COMMITTEE_LETTERS = [
        'environmental' => 'B',
        'disaster' => 'C',
        'livelihood' => 'D',
        'medicines' => 'E',
        'antidrug' => 'F',
        'gender' => 'G',
        'feeding' => 'H',
        'others' => 'J',
    ];

    /** @var list<string> */
    private const ALLOWED_STATUSES = ['scheduled', 'open', 'closed'];

    /** @var list<string> */
    private const ALLOWED_INPUT_TYPES = [
        'text',
        'paragraph',
        'radio',
        'checkbox',
        'dropdown',
        'date',
        'number',
    ];

    public function __construct(private readonly AbyipProgramCatalogService $catalogService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveCommitteeContext(User $user, string $committeeKey): array
    {
        $letter = self::COMMITTEE_LETTERS[$committeeKey] ?? null;

        if ($letter === null) {
            throw ValidationException::withMessages([
                'committee' => 'Unknown survey committee.',
            ]);
        }

        $document = $this->catalogService->getLatestAbyip($user->barangay_id);

        if ($document === null) {
            return [
                'committee' => $committeeKey,
                'letter' => $letter,
                'calendar_year' => null,
                'abyip_id' => null,
                'programs' => [],
            ];
        }

        $programs = Abyip::query()
            ->where('document_id', $document->id)
            ->where('code', $letter)
            ->where(function ($query) {
                $query->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                    ->orWhere(function ($inner) {
                        $inner->where('row_type', '!=', Abyip::ROW_DOCUMENT)
                            ->whereNull('parent_id');
                    });
            })
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Abyip $program) => [
                'id' => $program->id,
                'program_name' => trim((string) $program->program_name),
                'activities' => $program->children
                    ->map(fn (Abyip $activity) => trim((string) $activity->program_name))
                    ->filter()
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $program) => $program['program_name'] !== '')
            ->values()
            ->all();

        return [
            'committee' => $committeeKey,
            'letter' => $letter,
            'calendar_year' => (int) $document->fiscal_year,
            'abyip_id' => $document->id,
            'programs' => $programs,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForCommittee(User $user, string $committeeKey): Collection
    {
        $context = $this->resolveCommitteeContext($user, $committeeKey);
        $programIds = collect($context['programs'])->pluck('id')->all();

        if ($programIds === [] || $user->barangay_id === null) {
            return collect();
        }

        return ProgramSurvey::query()
            ->with(['abyipProgram', 'questions'])
            ->withCount('responses')
            ->where('barangay_id', $user->barangay_id)
            ->whereIn('abyip_program_id', $programIds)
            ->orderByDesc('open_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProgramSurvey $survey) => $this->formatSurvey($survey));
    }

    /**
     * @return array<string, mixed>
     */
    public function findForCommittee(User $user, string $committeeKey, int $surveyId): array
    {
        return $this->formatSurvey($this->findModel($user, $committeeKey, $surveyId));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function store(User $user, string $committeeKey, array $data): array
    {
        $validated = $this->validatePayload($data, false);
        $context = $this->resolveCommitteeContext($user, $committeeKey);
        $program = $this->resolveProgramFromContext($context, (int) $validated['abyip_program_id']);

        $this->assertRequiredUserContext($user);
        $this->assertOneSurveyPerProgramYear(
            (int) $user->barangay_id,
            $program['id'],
            $validated['open_date'],
        );

        $status = $this->resolveStatus(
            $validated['status'],
            $validated['open_date'],
            $validated['close_date'],
        );

        $programName = $program['program_name'];

        return DB::transaction(function () use ($user, $context, $program, $validated, $status, $programName) {
            $survey = ProgramSurvey::create([
                'tenant_id' => (int) $user->tenant_id,
                'barangay_id' => (int) $user->barangay_id,
                'abyip_id' => (int) $context['abyip_id'],
                'abyip_program_id' => (int) $program['id'],
                'announcement' => $validated['announcement'],
                'instructions' => $validated['instructions'] !== ''
                    ? $validated['instructions']
                    : 'Complete the survey for '.$programName.'.',
                'open_date' => $validated['open_date'],
                'close_date' => $validated['close_date'],
                'status' => $status,
                'created_by' => (int) $user->id,
            ]);

            $this->syncQuestions($survey, $validated['questions']);

            return $this->formatSurvey($survey->fresh(['abyipProgram', 'questions'])->loadCount('responses'));
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, string $committeeKey, int $surveyId, array $data): array
    {
        $survey = $this->findModel($user, $committeeKey, $surveyId);
        $validated = $this->validatePayload($data, true);
        $context = $this->resolveCommitteeContext($user, $committeeKey);
        $program = $this->resolveProgramFromContext($context, (int) $validated['abyip_program_id']);

        $openYear = (int) Carbon::parse($validated['open_date'])->format('Y');
        $existingYear = (int) $survey->open_date->format('Y');

        if ($openYear !== $existingYear || (int) $program['id'] !== (int) $survey->abyip_program_id) {
            $this->assertOneSurveyPerProgramYear(
                (int) $user->barangay_id,
                $program['id'],
                $validated['open_date'],
                $survey->id,
            );
        }

        $status = $this->resolveStatus(
            $validated['status'],
            $validated['open_date'],
            $validated['close_date'],
        );

        $programName = $program['program_name'];

        return DB::transaction(function () use ($survey, $validated, $status, $programName) {
            $survey->update([
                'abyip_program_id' => (int) $validated['abyip_program_id'],
                'announcement' => $validated['announcement'],
                'instructions' => $validated['instructions'] !== ''
                    ? $validated['instructions']
                    : 'Complete the survey for '.$programName.'.',
                'open_date' => $validated['open_date'],
                'close_date' => $validated['close_date'],
                'status' => $status,
            ]);

            $survey->questions()->delete();
            $this->syncQuestions($survey, $validated['questions']);

            return $this->formatSurvey($survey->fresh(['abyipProgram', 'questions'])->loadCount('responses'));
        });
    }

    public function delete(User $user, string $committeeKey, int $surveyId): void
    {
        $this->findModel($user, $committeeKey, $surveyId)->delete();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listResponses(User $user, string $committeeKey): Collection
    {
        $context = $this->resolveCommitteeContext($user, $committeeKey);
        $programIds = collect($context['programs'])->pluck('id')->all();

        if ($programIds === [] || $user->barangay_id === null) {
            return collect();
        }

        return ProgramSurveyResponse::query()
            ->with([
                'survey.abyipProgram',
                'survey.questions',
                'registration',
                'answers.question',
            ])
            ->whereHas('survey', function ($query) use ($user, $programIds) {
                $query->where('barangay_id', $user->barangay_id)
                    ->whereIn('abyip_program_id', $programIds);
            })
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProgramSurveyResponse $response) => $this->formatResponse($response));
    }

    private function findModel(User $user, string $committeeKey, int $surveyId): ProgramSurvey
    {
        if ($user->barangay_id === null) {
            throw ValidationException::withMessages([
                'barangay' => 'Your account is not linked to a barangay.',
            ]);
        }

        $context = $this->resolveCommitteeContext($user, $committeeKey);
        $programIds = collect($context['programs'])->pluck('id')->all();

        $survey = ProgramSurvey::query()
            ->with(['abyipProgram', 'questions'])
            ->withCount('responses')
            ->where('barangay_id', $user->barangay_id)
            ->whereIn('abyip_program_id', $programIds)
            ->find($surveyId);

        if ($survey === null) {
            throw ValidationException::withMessages([
                'survey' => 'Survey not found.',
            ]);
        }

        return $survey;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{id: int, program_name: string}
     */
    private function resolveProgramFromContext(array $context, int $programId): array
    {
        if ($context['abyip_id'] === null) {
            throw ValidationException::withMessages([
                'abyip' => 'No ABYIP document found for your barangay. Upload ABYIP first.',
            ]);
        }

        $program = collect($context['programs'])->firstWhere('id', $programId);

        if ($program === null) {
            throw ValidationException::withMessages([
                'abyip_program_id' => 'Selected program is not available for this committee.',
            ]);
        }

        return $program;
    }

    private function assertRequiredUserContext(User $user): void
    {
        if ($user->barangay_id === null) {
            throw ValidationException::withMessages([
                'barangay' => 'Your account is not linked to a barangay.',
            ]);
        }

        if ($user->tenant_id === null) {
            throw ValidationException::withMessages([
                'tenant' => 'Your account is not linked to a tenant.',
            ]);
        }
    }

    private function assertOneSurveyPerProgramYear(
        int $barangayId,
        int $programId,
        string $openDate,
        ?int $ignoreSurveyId = null,
    ): void {
        $year = (int) Carbon::parse($openDate)->format('Y');

        $exists = ProgramSurvey::query()
            ->where('barangay_id', $barangayId)
            ->where('abyip_program_id', $programId)
            ->whereYear('open_date', $year)
            ->when($ignoreSurveyId !== null, fn ($query) => $query->where('id', '!=', $ignoreSurveyId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'open_date' => 'A survey already exists for this program for the selected year.',
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     */
    private function syncQuestions(ProgramSurvey $survey, array $questions): void
    {
        foreach ($questions as $index => $question) {
            ProgramSurveyQuestion::create([
                'survey_id' => $survey->id,
                'question_label' => $question['label'],
                'input_type' => $question['type'],
                'is_required' => (bool) $question['required'],
                'options' => in_array($question['type'], ['radio', 'checkbox', 'dropdown'], true)
                    ? array_values($question['options'] ?? [])
                    : null,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validatePayload(array $data, bool $isUpdate): array
    {
        $questions = collect($data['questions'] ?? [])
            ->filter(fn ($question) => is_array($question) && trim((string) ($question['label'] ?? '')) !== '')
            ->values();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'questions' => 'Add at least one question with a label.',
            ]);
        }

        $openDate = trim((string) ($data['open_date'] ?? $data['openDate'] ?? ''));
        $closeDate = trim((string) ($data['close_date'] ?? $data['closeDate'] ?? ''));

        if ($openDate === '' || $closeDate === '') {
            throw ValidationException::withMessages([
                'open_date' => 'Open date and close date are required.',
            ]);
        }

        if (Carbon::parse($closeDate)->lte(Carbon::parse($openDate))) {
            throw ValidationException::withMessages([
                'close_date' => 'Close date must be later than open date.',
            ]);
        }

        $programId = (int) ($data['abyip_program_id'] ?? $data['abyipProgramId'] ?? 0);
        if ($programId <= 0) {
            throw ValidationException::withMessages([
                'abyip_program_id' => 'Program activity is required.',
            ]);
        }

        $status = strtolower(trim((string) ($data['status'] ?? 'scheduled')));
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid survey status.',
            ]);
        }

        $announcement = trim((string) ($data['announcement'] ?? $data['description'] ?? ''));
        if ($announcement === '') {
            $announcement = 'Please complete this survey honestly.';
        }

        $validatedQuestions = [];

        foreach ($questions as $question) {
            $type = strtolower(trim((string) ($question['type'] ?? 'text')));
            if (! in_array($type, self::ALLOWED_INPUT_TYPES, true)) {
                throw ValidationException::withMessages([
                    'questions' => 'Invalid question input type.',
                ]);
            }

            $label = trim((string) ($question['label'] ?? ''));
            $options = collect($question['options'] ?? [])
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->values()
                ->all();

            if (in_array($type, ['radio', 'checkbox', 'dropdown'], true) && $options === []) {
                throw ValidationException::withMessages([
                    'questions' => 'Choice-based questions must include at least one option.',
                ]);
            }

            $validatedQuestions[] = [
                'label' => $label,
                'type' => $type,
                'required' => (bool) ($question['required'] ?? false),
                'options' => $options,
            ];
        }

        return [
            'abyip_program_id' => $programId,
            'announcement' => $announcement,
            'instructions' => trim((string) ($data['instructions'] ?? '')),
            'open_date' => $openDate,
            'close_date' => $closeDate,
            'status' => $status,
            'questions' => $validatedQuestions,
        ];
    }

    private function resolveStatus(string $requestedStatus, string $openDate, string $closeDate): string
    {
        $now = Carbon::now()->startOfDay();
        $open = Carbon::parse($openDate)->startOfDay();
        $close = Carbon::parse($closeDate)->endOfDay();

        if ($now->lt($open)) {
            return 'scheduled';
        }

        if ($now->lte($close)) {
            return 'open';
        }

        return 'closed';
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSurvey(ProgramSurvey $survey): array
    {
        $programName = trim((string) ($survey->abyipProgram?->program_name ?? ''));

        return [
            'id' => $survey->id,
            'title' => $programName,
            'program_name' => $programName,
            'abyip_id' => $survey->abyip_id,
            'abyip_program_id' => $survey->abyip_program_id,
            'announcement' => $survey->announcement,
            'instructions' => $survey->instructions,
            'description' => $survey->announcement,
            'open_date' => $survey->open_date->format('Y-m-d'),
            'openDate' => $survey->open_date->format('Y-m-d'),
            'close_date' => $survey->close_date->format('Y-m-d'),
            'closeDate' => $survey->close_date->format('Y-m-d'),
            'status' => $survey->status,
            'response_count' => (int) ($survey->responses_count ?? $survey->responses()->count()),
            'questions' => $survey->questions
                ->map(fn (ProgramSurveyQuestion $question) => [
                    'id' => $question->id,
                    'label' => $question->question_label,
                    'type' => $question->input_type,
                    'required' => $question->is_required,
                    'options' => $question->options ?? [],
                ])
                ->values()
                ->all(),
            'created_at' => $survey->created_at?->toIso8601String(),
            'updated_at' => $survey->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatResponse(ProgramSurveyResponse $response): array
    {
        $registration = $response->registration;
        $survey = $response->survey;
        $answers = [];

        foreach ($response->answers as $answer) {
            if ($answer->question !== null) {
                $answers[(string) $answer->question_id] = $answer->answer;
            }
        }

        $respondentName = trim(implode(' ', array_filter([
            $registration?->first_name,
            $registration?->middle_name,
            $registration?->last_name,
            $registration?->suffix,
        ])));

        if ($respondentName === '' && $registration !== null) {
            $respondentName = trim((string) ($registration->full_name ?? 'Kabataan Member'));
        }

        return [
            'id' => $response->id,
            'survey_id' => $response->survey_id,
            'surveyId' => $response->survey_id,
            'respondent_name' => $respondentName,
            'respondentName' => $respondentName,
            'barangay' => $registration?->barangay ?? '—',
            'submitted_at' => $response->submitted_at?->toIso8601String(),
            'submittedAt' => $response->submitted_at?->toIso8601String(),
            'answers' => $answers,
            'survey' => $survey !== null ? $this->formatSurvey($survey) : null,
        ];
    }
}
