<?php

namespace App\Modules\Programs\Services;

use App\Models\Abyip;
use App\Models\KabataanRegistration;
use App\Models\ProgramApplication;
use App\Models\ScheduleProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class KabataanProgramService
{
    public function __construct(
        private readonly ProgramDocumentService $documentService,
        private readonly KabataanProgramSurveyService $surveyService,
    ) {
    }
    /** @var list<string> */
    private const YOUTH_PROGRAM_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    /**
     * @var array<string, array{category_key: string, modal_key: string, type: string, emoji: string, short_label: string}>
     */
    private const LETTER_META = [
        'A' => ['category_key' => 'education', 'modal_key' => 'education', 'type' => 'education', 'emoji' => '🎓', 'short_label' => 'Education'],
        'B' => ['category_key' => 'environment', 'modal_key' => 'environment', 'type' => 'other', 'emoji' => '🌿', 'short_label' => 'Environment'],
        'C' => ['category_key' => 'disaster', 'modal_key' => 'disaster', 'type' => 'other', 'emoji' => '🛡️', 'short_label' => 'Disaster Preparedness'],
        'D' => ['category_key' => 'agriculture', 'modal_key' => 'agriculture', 'type' => 'other', 'emoji' => '🌱', 'short_label' => 'Livelihood'],
        'E' => ['category_key' => 'health', 'modal_key' => 'health', 'type' => 'other', 'emoji' => '❤️', 'short_label' => 'Health'],
        'F' => ['category_key' => 'anti-drugs', 'modal_key' => 'anti-drugs', 'type' => 'other', 'emoji' => '🚫', 'short_label' => 'Anti-Drugs'],
        'G' => ['category_key' => 'gender', 'modal_key' => 'gender', 'type' => 'other', 'emoji' => '💜', 'short_label' => 'Gender and Development'],
        'H' => ['category_key' => 'feeding', 'modal_key' => 'others', 'type' => 'other', 'emoji' => '🍽️', 'short_label' => 'Feeding'],
        'I' => ['category_key' => 'sports', 'modal_key' => 'sports', 'type' => 'sports', 'emoji' => '⚽', 'short_label' => 'Sports Development'],
        'J' => ['category_key' => 'others', 'modal_key' => 'others', 'type' => 'other', 'emoji' => '📋', 'short_label' => 'Others'],
    ];

    /** @var array<string, string> */
    private const KK_FIELD_LABELS = [
        'last_name' => 'Last Name',
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'suffix' => 'Suffix',
        'full_name' => 'Full Name',
        'birthday' => 'Birthday',
        'age' => 'Age',
        'sex' => 'Sex',
        'civil_status' => 'Civil Status',
        'contact_number' => 'Contact Number',
        'email' => 'Email Address',
        'region' => 'Region',
        'province' => 'Province',
        'city' => 'City/Municipality',
        'barangay' => 'Barangay',
        'purok_zone' => 'Purok / Zone',
        'youth_classification' => 'Youth Classification',
        'youth_age_group' => 'Youth Age Group',
        'education' => 'Education',
        'current_school' => 'Current School',
        'course_strand' => 'Course / Strand',
        'work_status' => 'Work Status',
        'sk_voter' => 'Registered SK Voter',
        'sk_voted' => 'Voted in Last SK Election',
        'home_address' => 'Home Address',
        'year_level' => 'Year Level',
        'city_municipality' => 'City/Municipality',
    ];

    public function getLatestAbyipDocument(?int $barangayId): ?Abyip
    {
        if ($barangayId === null) {
            return null;
        }

        return Abyip::query()
            ->documents()
            ->where('barangay_id', $barangayId)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function getDashboardPayload(User $user): array
    {
        $barangayId = $user->barangay_id;
        $tenantId = $user->tenant_id;
        $document = $this->getLatestAbyipDocument($barangayId);

        $abyipPrograms = [];
        if ($document !== null) {
            $abyipPrograms = Abyip::query()
                ->where('document_id', $document->id)
                ->where(function ($query) {
                    $query->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                        ->orWhereIn('code', self::YOUTH_PROGRAM_LETTERS);
                })
                ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (Abyip $program) => $this->formatAbyipProgram($program, $user))
                ->values()
                ->all();
        }

        $schedulePrograms = $this->scheduleProgramsQuery($user)
            ->get()
            ->map(fn (ScheduleProgram $program) => $this->formatScheduleProgram($program, $user))
            ->values()
            ->all();

        return [
            'calendar_year' => $document?->fiscal_year,
            'abyip_programs' => $abyipPrograms,
            'schedule_programs' => $schedulePrograms,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getScheduleProgramForUser(int $scheduleProgramId, User $user): ?array
    {
        $program = $this->scheduleProgramsQuery($user)
            ->where('id', $scheduleProgramId)
            ->first();

        if ($program === null) {
            return null;
        }

        $application = $this->findUserApplication($user->id, $scheduleProgramId);
        $formatted = $this->formatScheduleProgram($program, $user, true);
        $formatted['kk_profile'] = $this->resolveKkProfile($user, $program->kk_profiling_fields ?? []);
        $formatted['application'] = $this->formatUserApplication($application, true);
        $formatted['uploaded_documents'] = $this->resolveUploadedDocuments(
            $user,
            $scheduleProgramId,
            $program->custom_questions ?? [],
            $application,
        );

        return $formatted;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listUserApplications(User $user): array
    {
        return ProgramApplication::query()
            ->with(['scheduleProgram'])
            ->where('kabataan_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ProgramApplication $app) => $this->formatUserApplication($app, false))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getUserApplication(User $user, int $applicationId): array
    {
        $application = ProgramApplication::query()
            ->with(['scheduleProgram'])
            ->whereKey($applicationId)
            ->where('kabataan_id', $user->id)
            ->first();

        if ($application === null) {
            throw ValidationException::withMessages([
                'application_id' => ['Application not found.'],
            ]);
        }

        return $this->formatUserApplication($application, true, $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelApplication(User $user, int $applicationId, string $cancelReason): array
    {
        $application = ProgramApplication::query()
            ->with(['scheduleProgram'])
            ->whereKey($applicationId)
            ->where('kabataan_id', $user->id)
            ->first();

        if ($application === null) {
            throw ValidationException::withMessages([
                'application_id' => ['Application not found.'],
            ]);
        }

        if ($application->status !== ProgramApplication::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'application_id' => ['Only pending applications can be cancelled.'],
            ]);
        }

        $reason = trim($cancelReason);
        if ($reason === '') {
            throw ValidationException::withMessages([
                'cancel_reason' => ['Please provide a reason for cancelling your application.'],
            ]);
        }

        $application->update([
            'status' => ProgramApplication::STATUS_CANCELLED,
            'cancel_reason' => $reason,
        ]);

        return $this->formatUserApplication($application->fresh(['scheduleProgram']), true);
    }

    /**
     * @param  list<array<string, mixed>>  $answers
     * @return array<string, mixed>
     */
    public function submitApplication(User $user, int $scheduleProgramId, array $answers): array
    {
        $program = $this->scheduleProgramsQuery($user)
            ->where('id', $scheduleProgramId)
            ->first();

        if ($program === null) {
            throw ValidationException::withMessages([
                'schedule_program_id' => ['Program not found or not available.'],
            ]);
        }

        if ($program->status !== ScheduleProgram::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'schedule_program_id' => ['This program is no longer accepting applications.'],
            ]);
        }

        $activeApplication = $this->findUserApplication($user->id, $scheduleProgramId);
        if ($activeApplication !== null) {
            throw ValidationException::withMessages([
                'schedule_program_id' => ['You have already applied for this program.'],
            ]);
        }

        $questions = collect($program->custom_questions ?? []);
        $this->validateAnswers($questions->all(), $answers);

        $registration = KabataanRegistration::query()
            ->with('barangay')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if ($registration === null) {
            throw ValidationException::withMessages([
                'kabataan_id' => ['KK Profiling registration is required before applying.'],
            ]);
        }

        $customAnswers = $this->buildCustomAnswersPayload($answers, $questions);
        $profileData = $this->buildApplicationProfileData($user, $registration);
        $existingRecord = $this->findUserApplicationRecord($user->id, $scheduleProgramId);
        $isResubmit = $existingRecord !== null
            && $existingRecord->status === ProgramApplication::STATUS_CANCELLED;

        if ($existingRecord !== null && ! $isResubmit) {
            throw ValidationException::withMessages([
                'schedule_program_id' => ['You have already applied for this program.'],
            ]);
        }

        $applicationPayload = array_merge($profileData, [
            'program_id' => $program->id,
            'kabataan_id' => $user->id,
            'custom_answers' => $customAnswers,
            'status' => ProgramApplication::STATUS_PENDING,
            'cancel_reason' => null,
            'rejection_reason' => null,
            'rejection_reasons' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        if ($isResubmit) {
            $existingRecord->update($applicationPayload);
            $application = $existingRecord->fresh();
        } else {
            $application = ProgramApplication::query()->create($applicationPayload);
        }

        try {
            $application->update([
                'required_documents' => $this->documentService->finalizeDraftDocuments($user, $application, $answers),
            ]);
        } catch (\Throwable $exception) {
            if (! $isResubmit) {
                $application->delete();
            }

            throw $exception;
        }

        $application->load(['scheduleProgram']);

        return $this->formatUserApplication($application, true);
    }

    private function scheduleProgramsQuery(User $user)
    {
        return ScheduleProgram::query()
            ->when($user->tenant_id, fn ($q, $tenantId) => $q->where('tenant_id', $tenantId))
            ->when($user->barangay_id, fn ($q, $barangayId) => $q->where('barangay_id', $barangayId))
            ->where('status', ScheduleProgram::STATUS_OPEN)
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    private function findUserApplication(int $userId, int $scheduleProgramId): ?ProgramApplication
    {
        return ProgramApplication::query()
            ->with(['scheduleProgram'])
            ->where('kabataan_id', $userId)
            ->where('program_id', $scheduleProgramId)
            ->whereNot('status', ProgramApplication::STATUS_CANCELLED)
            ->first();
    }

    private function findUserApplicationRecord(int $userId, int $scheduleProgramId): ?ProgramApplication
    {
        return ProgramApplication::query()
            ->where('kabataan_id', $userId)
            ->where('program_id', $scheduleProgramId)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAbyipProgram(Abyip $program, User $user): array
    {
        $letter = strtoupper(trim((string) ($program->program_letter ?? $program->code ?? '')));
        $meta = self::LETTER_META[$letter] ?? [
            'category_key' => 'others',
            'modal_key' => 'others',
            'type' => 'other',
            'emoji' => '📋',
            'short_label' => 'Program',
        ];

        $activities = $program->children
            ->map(fn ($activity) => trim((string) $activity->program_name))
            ->filter()
            ->values()
            ->all();

        $description = trim((string) ($program->description ?? ''));
        if ($description === '' && $activities !== []) {
            $description = 'Activities: '.implode(', ', $activities);
        }
        if ($description === '') {
            $description = 'No activities listed.';
        }

        $scheduleCount = ScheduleProgram::query()
            ->when($user->tenant_id, fn ($q, $tenantId) => $q->where('tenant_id', $tenantId))
            ->when($user->barangay_id, fn ($q, $barangayId) => $q->where('barangay_id', $barangayId))
            ->where('status', ScheduleProgram::STATUS_OPEN)
            ->where('program_type', trim((string) $program->program_name))
            ->count();

        $survey = $this->surveyService->summarizeOpenSurveyForProgram($user, (int) $program->id);

        if ($meta['type'] === 'education') {
            $activeCount = $scheduleCount;
        } elseif ($meta['type'] === 'sports') {
            $activeCount = $scheduleCount;
        } else {
            $activeCount = $survey !== null ? 1 : 0;
        }

        return [
            'id' => $program->id,
            'letter' => $letter,
            'title' => $program->program_name,
            'description' => $description,
            'activities' => $activities,
            'category_key' => $meta['category_key'],
            'modal_key' => $meta['modal_key'],
            'type' => $meta['type'],
            'emoji' => $meta['emoji'],
            'short_label' => $meta['short_label'],
            'active_count' => $activeCount,
            'schedule_count' => $scheduleCount,
            'survey' => $survey,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatScheduleProgram(ScheduleProgram $program, User $user, bool $includeQuestions = false): array
    {
        $application = $this->findUserApplication($user->id, $program->id);

        $availableSlots = $this->calculateAvailableSlots($program);

        $payload = [
            'id' => $program->id,
            'program_type' => $program->program_type,
            'program_name' => $program->program_name,
            'committee' => $program->committee,
            'participation_quantity' => $program->participation_quantity,
            'available_slots' => $availableSlots,
            'submitted_applications_count' => $this->countActiveApplications($program->id),
            'start_date' => $program->start_date?->format('Y-m-d'),
            'start_date_display' => $this->formatDate($program->start_date),
            'end_date' => $program->end_date?->format('Y-m-d'),
            'end_date_display' => $this->formatDate($program->end_date),
            'status' => $program->status,
            'announcement' => $program->announcement,
            'has_applied' => $application !== null,
            'application_status' => $application?->status,
            'application_id' => $application?->id,
        ];

        if ($includeQuestions) {
            $payload['kk_profiling_fields'] = $program->kk_profiling_fields ?? [];
            $payload['custom_questions'] = $program->custom_questions ?? [];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatUserApplication(?ProgramApplication $application, bool $withAnswers = false, ?User $user = null): ?array
    {
        if ($application === null) {
            return null;
        }

        $displayDate = $application->updated_at && $application->created_at
            && $application->updated_at->gt($application->created_at)
            ? $application->updated_at
            : $application->created_at;

        $scheduleProgram = $application->scheduleProgram;

        $payload = [
            'id' => $application->id,
            'schedule_program_id' => $application->program_id,
            'program_name' => $scheduleProgram?->program_name ?? 'Program',
            'program_type' => $scheduleProgram?->program_type,
            'committee' => $scheduleProgram?->committee,
            'program_period' => $this->formatProgramPeriod($scheduleProgram),
            'status' => $application->status,
            'status_display' => ucfirst((string) $application->status),
            'submitted_at' => $displayDate?->format('M j, Y'),
            'submitted_at_iso' => $displayDate?->toIso8601String(),
            'cancel_reason' => $application->cancel_reason,
            'can_cancel' => $application->status === ProgramApplication::STATUS_PENDING,
        ];

        if ($withAnswers) {
            if ($user !== null) {
                $payload['personal_info'] = $this->formatApplicationPersonalInfo($application, $user);
            }

            $documents = $this->documentService->listApplicationDocuments($application);

            $payload['answers'] = collect($application->custom_answers ?? [])
                ->map(function (array $answer) use ($documents, $application) {
                    $questionId = (string) ($answer['question_id'] ?? '');
                    $decoded = $answer['answer'] ?? null;
                    if (is_string($decoded)) {
                        $parsed = json_decode($decoded, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $decoded = $parsed;
                        }
                    }

                    if (($answer['question_type'] ?? '') === 'file' && isset($documents[$questionId])) {
                        $decoded = $documents[$questionId];
                    }

                    return [
                        'question_id' => $questionId,
                        'question_label' => $answer['question_label'] ?? null,
                        'question_type' => $answer['question_type'] ?? null,
                        'answer' => $decoded,
                    ];
                })
                ->values()
                ->all();

            $payload['uploaded_documents'] = $documents;
        }

        return $payload;
    }

    /**
     * @param  list<array<string, mixed>>  $answers
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    private function buildCustomAnswersPayload(array $answers, $questions): array
    {
        return collect($answers)
            ->map(function (array $answer) use ($questions) {
                $questionId = (string) ($answer['question_id'] ?? '');
                $question = $questions->firstWhere('id', $questionId);

                $questionType = $question['type'] ?? ($answer['question_type'] ?? null);
                $answerValue = $answer['answer'] ?? null;

                if ($questionType === 'file') {
                    $storedAnswer = $this->sanitizeFileAnswerForStorage($answerValue);
                } else {
                    $storedAnswer = $this->normalizeAnswerText($answerValue);
                }

                return [
                    'question_id' => $questionId,
                    'question_label' => $question['label'] ?? ($answer['question_label'] ?? null),
                    'question_type' => $questionType,
                    'answer' => $storedAnswer,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $selectedFields
     * @return array<string, string>
     */
    private function resolveKkProfile(User $user, array $selectedFields): array
    {
        $registration = KabataanRegistration::query()
            ->with('barangay')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $formData = $registration?->form_data ?? [];

        $fullName = trim(implode(' ', array_filter([
            $registration?->first_name ?? ($formData['first_name'] ?? ''),
            $registration?->middle_name ?? ($formData['middle_name'] ?? ''),
            $registration?->last_name ?? ($formData['last_name'] ?? ''),
            $this->resolveSuffixFromRegistration($registration, $formData),
        ], fn ($part) => trim((string) $part) !== '')));

        $mapped = [
            'last_name' => $registration?->last_name ?? ($formData['last_name'] ?? ''),
            'first_name' => $registration?->first_name ?? ($formData['first_name'] ?? ''),
            'middle_name' => $registration?->middle_name ?? ($formData['middle_name'] ?? ''),
            'suffix' => $this->resolveSuffixFromRegistration($registration, $formData),
            'full_name' => $fullName !== '' ? $fullName : ($user->name ?? ''),
            'birthday' => $formData['birthday'] ?? ($formData['date_of_birth'] ?? ''),
            'age' => (string) ($formData['age'] ?? ''),
            'sex' => $formData['sex'] ?? ($formData['gender'] ?? ''),
            'civil_status' => $formData['civil_status'] ?? '',
            'contact_number' => $registration?->contact_number ?? ($formData['contact_number'] ?? ''),
            'email' => $registration?->email ?? ($user->email ?? ''),
            'region' => $formData['region'] ?? '',
            'province' => $formData['province'] ?? '',
            'city' => $formData['city'] ?? ($formData['city_municipality'] ?? ''),
            'city_municipality' => $formData['city_municipality'] ?? ($formData['city'] ?? ''),
            'barangay' => $registration?->barangay?->name ?? ($formData['barangay'] ?? ''),
            'purok_zone' => $formData['purok_zone'] ?? ($formData['purok'] ?? ''),
            'youth_classification' => $formData['youth_classification'] ?? '',
            'youth_age_group' => $formData['youth_age_group'] ?? '',
            'education' => $formData['education'] ?? '',
            'current_school' => $formData['current_school'] ?? ($formData['school'] ?? ''),
            'course_strand' => $formData['course_strand'] ?? ($formData['course'] ?? ''),
            'work_status' => $formData['work_status'] ?? '',
            'sk_voter' => $formData['sk_voter'] ?? '',
            'sk_voted' => $formData['sk_voted'] ?? '',
            'home_address' => $formData['home_address'] ?? ($formData['address'] ?? ''),
            'year_level' => $formData['year_level'] ?? '',
        ];

        if ($selectedFields === []) {
            return array_filter($mapped, fn ($value) => trim((string) $value) !== '');
        }

        $result = [];
        foreach ($selectedFields as $field) {
            $key = (string) $field;
            $value = trim((string) ($mapped[$key] ?? ''));
            if ($value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @param  list<array<string, mixed>>  $answers
     */
    private function validateAnswers(array $questions, array $answers): void
    {
        $answersById = collect($answers)->keyBy('question_id');

        foreach ($questions as $question) {
            if (! ($question['required'] ?? false)) {
                continue;
            }

            $questionId = (string) ($question['id'] ?? '');
            $answer = $answersById->get($questionId);
            $value = $answer['answer'] ?? null;

            if (($question['type'] ?? '') === 'file') {
                $hasFile = is_array($value) && ! empty($value['path']);
                if (! $hasFile) {
                    $label = $question['label'] ?? 'A required document';
                    throw ValidationException::withMessages([
                        "answers.{$questionId}" => ["{$label} is required."],
                    ]);
                }

                continue;
            }

            if ($this->isEmptyAnswer($value)) {
                $label = $question['label'] ?? 'A required question';
                throw ValidationException::withMessages([
                    "answers.{$questionId}" => ["{$label} is required."],
                ]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return array<string, array<string, mixed>>
     */
    private function resolveUploadedDocuments(
        User $user,
        int $scheduleProgramId,
        array $questions,
        ?ProgramApplication $application,
    ): array {
        if ($application !== null) {
            return $this->documentService->listApplicationDocuments($application);
        }

        $fileQuestionIds = collect($questions)
            ->filter(fn (array $question) => ($question['type'] ?? '') === 'file')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return $this->documentService->listDraftDocuments($user, $scheduleProgramId, $fileQuestionIds);
    }

    private function isEmptyAnswer(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return trim((string) $value) === '';
    }

    private function normalizeAnswerText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sanitizeFileAnswerForStorage(mixed $answerValue): ?array
    {
        $meta = is_array($answerValue) ? $answerValue : null;

        if ($meta === null && is_string($answerValue)) {
            $decoded = json_decode($answerValue, true);
            $meta = is_array($decoded) ? $decoded : null;
        }

        if ($meta === null || empty($meta['path'])) {
            return null;
        }

        return [
            'question_id' => $meta['question_id'] ?? null,
            'path' => str_replace('\\', '/', (string) $meta['path']),
            'original_name' => $meta['original_name'] ?? null,
            'size' => isset($meta['size']) ? (int) $meta['size'] : null,
            'mime' => $meta['mime'] ?? 'application/pdf',
            'uploaded_at' => $meta['uploaded_at'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildApplicationProfileData(User $user, KabataanRegistration $registration): array
    {
        $formData = $registration->form_data ?? [];
        $birthdate = $this->parseBirthdate($formData['birthday'] ?? ($formData['date_of_birth'] ?? null));

        if ($birthdate === null) {
            throw ValidationException::withMessages([
                'birthdate' => ['Birthdate is required from your KK Profile before applying.'],
            ]);
        }

        return [
            'first_name' => $this->nullableString($registration->first_name ?? ($formData['first_name'] ?? null)),
            'middle_name' => $this->nullableString($registration->middle_name ?? ($formData['middle_name'] ?? null)),
            'last_name' => $this->nullableString($registration->last_name ?? ($formData['last_name'] ?? null)),
            'suffix' => $this->resolveSuffixForStorage($registration, $formData),
            'birthdate' => $birthdate,
            'age' => isset($formData['age']) ? (int) $formData['age'] : null,
            'sex' => $this->nullableString($formData['sex'] ?? ($formData['gender'] ?? null)),
            'civil_status' => $this->nullableString($formData['civil_status'] ?? null),
            'purok' => $this->nullableString($formData['purok_zone'] ?? ($formData['purok'] ?? null)),
            'barangay' => $this->nullableString($registration->barangay?->name ?? ($formData['barangay'] ?? null)),
            'email' => $this->nullableString($registration->email ?? $user->email),
            'contact_number' => $this->nullableString($registration->contact_number ?? ($formData['contact_number'] ?? null)),
            'school_name' => $this->nullableString($formData['current_school'] ?? ($formData['school'] ?? null)),
            'grade_level' => $this->nullableString($formData['year_level'] ?? null),
            'course' => $this->nullableString($formData['course_strand'] ?? ($formData['course'] ?? null)),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '' || in_array(strtolower($trimmed), ['null', 'n/a'], true)) {
            return null;
        }

        return $trimmed;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveSuffixFromRegistration(?KabataanRegistration $registration, array $formData): string
    {
        $suffix = trim((string) ($registration?->suffix ?? ($formData['suffix'] ?? '')));

        if ($suffix === 'Others') {
            return trim((string) ($formData['custom_suffix'] ?? ''));
        }

        return $suffix;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveSuffixForStorage(KabataanRegistration $registration, array $formData): ?string
    {
        $suffix = $this->resolveSuffixFromRegistration($registration, $formData);

        return $suffix === '' ? null : $suffix;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveSuffixForDisplay(?string $storedSuffix, ?KabataanRegistration $registration, array $formData): string
    {
        $suffix = trim((string) ($storedSuffix ?? ''));
        if ($suffix === '') {
            $suffix = $this->resolveSuffixFromRegistration($registration, $formData);
        }

        if ($suffix === '') {
            return '—';
        }

        return $suffix;
    }

    private function countActiveApplications(int $scheduleProgramId): int
    {
        return ProgramApplication::query()
            ->where('program_id', $scheduleProgramId)
            ->whereNot('status', ProgramApplication::STATUS_CANCELLED)
            ->count();
    }

    private function calculateAvailableSlots(ScheduleProgram $program): ?int
    {
        if ($program->participation_quantity === null) {
            return null;
        }

        $used = $this->countActiveApplications($program->id);

        return max(0, $program->participation_quantity - $used);
    }

    private function formatProgramPeriod(?ScheduleProgram $program): string
    {
        if ($program === null) {
            return '—';
        }

        return $this->formatDate($program->start_date).' - '.$this->formatDate($program->end_date);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function formatApplicationPersonalInfo(ProgramApplication $application, User $user): array
    {
        $registration = KabataanRegistration::query()
            ->with('barangay')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $formData = $registration?->form_data ?? [];
        $selectedFields = $application->scheduleProgram?->kk_profiling_fields ?? [];
        $displayDate = $application->updated_at && $application->created_at
            && $application->updated_at->gt($application->created_at)
            ? $application->updated_at
            : $application->created_at;

        $mapped = [
            'last_name' => (string) ($application->last_name ?? ''),
            'first_name' => (string) ($application->first_name ?? ''),
            'middle_name' => (string) ($application->middle_name ?? ''),
            'suffix' => $this->resolveSuffixForDisplay($application->suffix, $registration, $formData),
            'full_name' => trim(implode(' ', array_filter([
                $application->first_name,
                $application->middle_name,
                $application->last_name,
                $this->resolveSuffixForDisplay($application->suffix, $registration, $formData),
            ], fn ($part) => $part !== null && trim((string) $part) !== '' && trim((string) $part) !== '—'))),
            'birthday' => $application->birthdate?->format('F j, Y') ?? ($formData['birthday'] ?? ''),
            'age' => $application->age !== null ? (string) $application->age : (string) ($formData['age'] ?? ''),
            'sex' => (string) ($application->sex ?? ($formData['sex'] ?? ($formData['gender'] ?? ''))),
            'civil_status' => (string) ($application->civil_status ?? ($formData['civil_status'] ?? '')),
            'contact_number' => (string) ($application->contact_number ?? ($formData['contact_number'] ?? '')),
            'email' => (string) ($application->email ?? ($registration?->email ?? $user->email ?? '')),
            'region' => (string) ($formData['region'] ?? ''),
            'province' => (string) ($formData['province'] ?? ''),
            'city' => (string) ($formData['city'] ?? ($formData['city_municipality'] ?? '')),
            'city_municipality' => (string) ($formData['city_municipality'] ?? ($formData['city'] ?? '')),
            'barangay' => (string) ($application->barangay ?? ($registration?->barangay?->name ?? ($formData['barangay'] ?? ''))),
            'purok_zone' => (string) ($application->purok ?? ($formData['purok_zone'] ?? ($formData['purok'] ?? ''))),
            'youth_classification' => (string) ($formData['youth_classification'] ?? ''),
            'youth_age_group' => (string) ($formData['youth_age_group'] ?? ''),
            'education' => (string) ($formData['education'] ?? ''),
            'current_school' => (string) ($application->school_name ?? ($formData['current_school'] ?? ($formData['school'] ?? ''))),
            'course_strand' => (string) ($application->course ?? ($formData['course_strand'] ?? ($formData['course'] ?? ''))),
            'work_status' => (string) ($formData['work_status'] ?? ''),
            'sk_voter' => (string) ($formData['sk_voter'] ?? ''),
            'sk_voted' => (string) ($formData['sk_voted'] ?? ''),
            'home_address' => (string) ($formData['home_address'] ?? ($formData['address'] ?? '')),
            'year_level' => (string) ($application->grade_level ?? ($formData['year_level'] ?? '')),
        ];

        $items = [
            ['label' => 'Program Name', 'value' => (string) ($application->scheduleProgram?->program_name ?? 'Program')],
            ['label' => 'Application Period', 'value' => $this->formatProgramPeriod($application->scheduleProgram)],
            ['label' => 'Application Date', 'value' => $displayDate?->format('F j, Y g:i A') ?? '—'],
            ['label' => 'Status', 'value' => ucfirst((string) $application->status)],
        ];

        if ($application->scheduleProgram?->committee) {
            $items[] = ['label' => 'Committee', 'value' => (string) $application->scheduleProgram->committee];
        }

        $fieldsToRender = $selectedFields !== []
            ? $selectedFields
            : array_keys(array_filter($mapped, fn ($value) => trim((string) $value) !== '' && trim((string) $value) !== '—'));

        foreach ($fieldsToRender as $field) {
            $key = (string) $field;
            $value = trim((string) ($mapped[$key] ?? ''));
            if ($value === '' || $value === '—') {
                continue;
            }

            $items[] = [
                'label' => self::KK_FIELD_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }

        return $items;
    }

    private function parseBirthdate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatDate(?Carbon $date): string
    {
        return $date?->format('F j, Y') ?? '—';
    }

    /**
     * @return array<string, string>
     */
    public function kkFieldLabels(): array
    {
        return self::KK_FIELD_LABELS;
    }
}
