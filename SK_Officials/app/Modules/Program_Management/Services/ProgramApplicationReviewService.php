<?php

namespace App\Modules\Program_Management\Services;

use App\Models\KabataanRegistration;
use App\Models\KkSurveyResponse;
use App\Models\ProgramApplication;
use App\Models\User;
use App\Services\RejectedProgramApplicationService;
use Illuminate\Validation\ValidationException;

class ProgramApplicationReviewService
{
    public function __construct(
        private readonly RejectedProgramApplicationService $rejectedService,
        private readonly ProgramDocumentService $documentService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listForBarangay(User $user, string $letter, ?string $status = null): array
    {
        if ($user->barangay_id === null) {
            return [];
        }

        $letter = strtoupper(trim($letter));

        $query = ProgramApplication::query()
            ->with(['scheduleProgram', 'kabataan'])
            ->whereHas('scheduleProgram', function ($builder) use ($user, $letter) {
                $builder->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $letter);
            });

        if ($status !== null && trim($status) !== '') {
            $query->where('status', strtolower(trim($status)));
        }

        return $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ProgramApplication $application) => $this->formatApplication($application))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $applicationId, string $letter): array
    {
        return $this->formatApplication($this->findModel($user, $applicationId, $letter), true);
    }

    /**
     * @param  list<string>|null  $rejectionReasons
     * @return array<string, mixed>
     */
    public function updateStatus(
        User $user,
        int $applicationId,
        string $status,
        string $letter,
        ?string $rejectionReason = null,
        ?array $rejectionReasons = null,
    ): array {
        $application = $this->findModel($user, $applicationId, $letter);

        $isRestoreToPending = $status === ProgramApplication::STATUS_PENDING
            && $application->status === ProgramApplication::STATUS_APPROVED;

        if ($isRestoreToPending) {
            if (strtoupper($letter) !== ScheduleProgramService::LETTER_SPORTS) {
                throw ValidationException::withMessages([
                    'status' => ['Only sports applications can be restored to pending.'],
                ]);
            }

            $application->update([
                'status' => ProgramApplication::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'rejection_reasons' => null,
            ]);

            return $this->formatApplication($application->fresh(['scheduleProgram', 'kabataan']), true);
        }

        if (! in_array($status, [ProgramApplication::STATUS_APPROVED, ProgramApplication::STATUS_REJECTED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Status must be approved or rejected.'],
            ]);
        }

        $isRevoke = $status === ProgramApplication::STATUS_REJECTED
            && $application->status === ProgramApplication::STATUS_APPROVED;

        if (! $isRevoke && $application->status !== ProgramApplication::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => ['Only pending applications can be reviewed.'],
            ]);
        }

        if (! $isRevoke && ! $this->canReviewApplication($application)) {
            $schedule = $application->scheduleProgram;
            $endDate = $schedule?->end_date?->format('F j, Y') ?? 'the scheduled end date';

            throw ValidationException::withMessages([
                'status' => ["Applications can only be approved or rejected after the application period ends on {$endDate}."],
            ]);
        }

        $payload = [
            'status' => $status,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ];

        if ($status === ProgramApplication::STATUS_REJECTED) {
            $reasons = array_values(array_filter(array_map(
                fn ($reason) => trim((string) $reason),
                $rejectionReasons ?? []
            )));

            if ($reasons === [] && ($rejectionReason === null || trim($rejectionReason) === '')) {
                throw ValidationException::withMessages([
                    'rejection_reason' => ['Please provide a rejection reason.'],
                ]);
            }

            $payload['rejection_reasons'] = $reasons !== [] ? $reasons : null;
            $payload['rejection_reason'] = $rejectionReason !== null && trim($rejectionReason) !== ''
                ? trim($rejectionReason)
                : ($reasons[0] ?? null);
        } else {
            $payload['rejection_reason'] = null;
            $payload['rejection_reasons'] = null;

            if (strtoupper($letter) === ScheduleProgramService::LETTER_SPORTS
                && ($application->payment_status === null || trim((string) $application->payment_status) === '')) {
                $payload['payment_status'] = 'Unpaid';
            }
        }

        $application->update($payload);

        if ($status === ProgramApplication::STATUS_REJECTED) {
            $this->rejectedService->recordRejection(
                $application->fresh(['scheduleProgram']),
                $user,
                $letter
            );
        }

        return $this->formatApplication($application->fresh(['scheduleProgram', 'kabataan']), true);
    }

    /**
     * @return array{total: int, pending: int, approved: int, rejected: int}
     */
    public function summarizeForBarangay(User $user, string $letter): array
    {
        if ($user->barangay_id === null) {
            return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        }

        $letter = strtoupper(trim($letter));

        $baseQuery = ProgramApplication::query()
            ->whereHas('scheduleProgram', function ($builder) use ($user, $letter) {
                $builder->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $letter);
            });

        return [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', ProgramApplication::STATUS_PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status', ProgramApplication::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status', ProgramApplication::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function updatePaymentStatus(User $user, int $applicationId, string $letter, string $paymentStatus, bool $applyToTeam = false): array
    {
        $application = $this->findModel($user, $applicationId, $letter);

        if ($application->status !== ProgramApplication::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'payment_status' => ['Only approved participants can have payment status updated.'],
            ]);
        }

        $normalized = match (strtolower(trim($paymentStatus))) {
            'paid', 'claimed' => 'Paid',
            'unpaid', 'unclaimed', 'not paid' => 'Unpaid',
            default => throw ValidationException::withMessages([
                'payment_status' => ['Payment status must be Paid or Unpaid.'],
            ]),
        };

        $idsToUpdate = collect([$application->id]);

        if ($applyToTeam) {
            $teamName = $this->extractTeamName($application);
            if ($teamName !== null && trim($teamName) !== '') {
                $idsToUpdate = ProgramApplication::query()
                    ->with(['scheduleProgram'])
                    ->where('status', ProgramApplication::STATUS_APPROVED)
                    ->whereHas('scheduleProgram', function ($query) use ($user, $letter) {
                        $query->where('barangay_id', $user->barangay_id)
                            ->where('program_letter', strtoupper(trim($letter)));
                    })
                    ->get()
                    ->filter(fn (ProgramApplication $app) => $this->extractTeamName($app) === $teamName)
                    ->pluck('id')
                    ->values();
            }
        }

        ProgramApplication::query()
            ->whereIn('id', $idsToUpdate->all())
            ->update(['payment_status' => $normalized]);

        return [
            'data' => $this->formatApplication($application->fresh(['scheduleProgram', 'kabataan'])),
            'updated_count' => $idsToUpdate->count(),
            'updated_ids' => $idsToUpdate->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatApplicationModel(ProgramApplication $application, bool $withDetails = false): array
    {
        return $this->formatApplication($application, $withDetails);
    }

    public function findModelForBarangay(User $user, int $applicationId, string $letter): ProgramApplication
    {
        return $this->findModel($user, $applicationId, $letter);
    }

    protected function findModel(User $user, int $applicationId, string $letter): ProgramApplication
    {
        $letter = strtoupper(trim($letter));

        $application = ProgramApplication::query()
            ->with(['scheduleProgram', 'kabataan'])
            ->whereKey($applicationId)
            ->whereHas('scheduleProgram', function ($query) use ($user, $letter) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $letter);
            })
            ->first();

        if ($application === null) {
            throw ValidationException::withMessages([
                'application_id' => ['Application not found.'],
            ]);
        }

        return $application;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatApplication(ProgramApplication $application, bool $withDetails = false): array
    {
        $fullName = trim(implode(' ', array_filter([
            $application->last_name,
            $application->first_name,
            $application->middle_name,
            $application->suffix,
        ])));

        $sportsMeta = $this->resolveSportsMeta($application);
        $teamName = $this->extractTeamName($application);

        $formatted = [
            'id' => $application->id,
            'program_id' => $application->program_id,
            'program_name' => $application->scheduleProgram?->program_name
                ?? $application->scheduleProgram?->program_type,
            'sport_key' => $sportsMeta['sport_key'],
            'sport_label' => $sportsMeta['sport_label'],
            'team_name' => $teamName,
            'full_name' => $fullName !== '' ? $fullName : 'Applicant',
            'last_name' => $application->last_name,
            'first_name' => $application->first_name,
            'middle_name' => $application->middle_name,
            'suffix' => $application->suffix,
            'age' => $application->age,
            'sex' => $application->sex,
            'contact_number' => $application->contact_number,
            'email' => $application->email,
            'barangay' => $application->barangay,
            'status' => $application->status,
            'status_label' => ucfirst((string) $application->status),
            'date_submitted' => $application->created_at?->format('M j, Y') ?? '—',
            'submitted_time' => $application->created_at?->format('g:i A') ?? '—',
            'created_at' => $application->created_at?->toIso8601String(),
            'reviewed_at' => $application->reviewed_at?->toIso8601String(),
            'school_name' => $application->school_name,
            'grade_level' => $application->grade_level,
            'year_level' => $application->grade_level,
            'course' => $application->course,
            'purpose' => $application->purpose,
            'payment_status' => $application->payment_status,
            'rejection_reason' => $application->rejection_reason,
            'rejection_reasons' => $application->rejection_reasons ?? [],
            'schedule_start_date' => $application->scheduleProgram?->start_date?->toDateString(),
            'schedule_end_date' => $application->scheduleProgram?->end_date?->toDateString(),
            'can_review' => $this->canReviewApplication($application),
            'documents_count' => count($normalizedDocs = $this->normalizeRequiredDocuments($application->required_documents ?? [])),
            'document_labels' => collect($normalizedDocs)
                ->map(fn (array $doc) => $this->stringifyValue(
                    $doc['question_label'] ?? $doc['label'] ?? $doc['original_name'] ?? 'Uploaded PDF'
                ))
                ->filter()
                ->values()
                ->all(),
        ];

        if ($withDetails) {
            $formatted['birthdate'] = $application->birthdate?->toDateString();
            $formatted['civil_status'] = $application->civil_status;
            $formatted['purok'] = $application->purok;
            $formatted['school_name'] = $application->school_name;
            $formatted['grade_level'] = $application->grade_level;
            $formatted['course'] = $application->course;
            $normalizedDocuments = $this->normalizeRequiredDocuments($application->required_documents ?? []);
            $formatted['required_documents'] = $this->documentService->enrichDocumentsForApplication(
                $application,
                $normalizedDocuments
            );

            $documentsByQuestionId = collect($formatted['required_documents'])->keyBy('question_id');
            $formatted['custom_answers'] = $this->enrichCustomAnswers(
                $application,
                collect($application->custom_answers ?? [])
                    ->filter(fn ($answer) => is_array($answer))
                    ->map(function (array $answer) use ($documentsByQuestionId) {
                        $questionId = (string) ($answer['question_id'] ?? '');
                        if (($answer['question_type'] ?? '') === 'file' && $documentsByQuestionId->has($questionId)) {
                            $answer['answer'] = $documentsByQuestionId->get($questionId);
                        } elseif (($answer['question_type'] ?? '') !== 'file' && array_key_exists('answer', $answer)) {
                            $answer['answer'] = $this->stringifyValue($answer['answer']);
                        }

                        return $answer;
                    })
                    ->values()
                    ->all()
            );
            $kkFields = $application->scheduleProgram?->kk_profiling_fields ?? [];
            if ($kkFields === [] && ($application->scheduleProgram?->program_letter ?? '') === 'I') {
                $kkFields = ScheduleProgramService::DEFAULT_SPORTS_KK_FIELDS;
            }
            $formatted['kk_profile_data'] = $this->buildKkProfileData($application, $kkFields);
            $formatted['schedule_program'] = $application->scheduleProgram ? [
                'id' => $application->scheduleProgram->id,
                'program_name' => $application->scheduleProgram->program_name,
                'program_type' => $application->scheduleProgram->program_type,
                'custom_questions' => $application->scheduleProgram->custom_questions ?? [],
                'kk_profiling_fields' => $kkFields,
                'sports_details' => $application->scheduleProgram->sports_details ?? [],
            ] : null;
        }

        return $formatted;
    }

    /**
     * @return array{sport_key: string, sport_label: string}
     */
    protected function resolveSportsMeta(ProgramApplication $application): array
    {
        $details = is_array($application->scheduleProgram?->sports_details)
            ? $application->scheduleProgram->sports_details
            : [];
        $key = strtolower(trim((string) ($details['sport_key'] ?? 'other')));
        if (! in_array($key, ['basketball', 'volleyball', 'other'], true)) {
            $key = 'other';
        }

        $label = trim((string) ($details['sport_label'] ?? ''));
        if ($label === '') {
            $label = match ($key) {
                'basketball' => 'Basketball',
                'volleyball' => 'Volleyball',
                default => 'Other',
            };
        }

        return [
            'sport_key' => $key,
            'sport_label' => $label,
        ];
    }

    protected function extractTeamName(ProgramApplication $application): ?string
    {
        foreach ($application->custom_answers ?? [] as $answer) {
            if (! is_array($answer)) {
                continue;
            }

            $label = strtolower(trim((string) ($answer['question_label'] ?? '')));
            if (! str_contains($label, 'team')) {
                continue;
            }

            $text = $this->stringifyValue($answer['answer'] ?? '');
            if ($text !== '') {
                return $text;
            }
        }

        foreach ($application->custom_answers ?? [] as $answer) {
            if (! is_array($answer)) {
                continue;
            }

            if (($answer['question_type'] ?? '') === 'file') {
                continue;
            }

            $text = $this->stringifyValue($answer['answer'] ?? '');
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    protected function stringifyValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->stringifyValue($item))
                ->filter(fn ($item) => $item !== '')
                ->implode(', ');
        }

        return trim((string) $value);
    }

    /**
     * @param  list<array<string, mixed>>  $customAnswers
     * @return list<array<string, mixed>>
     */
    protected function enrichCustomAnswers(ProgramApplication $application, array $customAnswers): array
    {
        return collect($customAnswers)
            ->map(function (array $answer) use ($application) {
                if (($answer['question_type'] ?? '') !== 'file') {
                    return $answer;
                }

                $fileAnswer = $answer['answer'] ?? null;
                if (! is_array($fileAnswer) || empty($fileAnswer['path']) || ! empty($fileAnswer['preview_url'])) {
                    return $answer;
                }

                $questionId = (string) ($answer['question_id'] ?? '');
                $enriched = $this->documentService->enrichDocumentsForApplication($application, [[
                    'question_id' => $questionId,
                    'path' => $fileAnswer['path'],
                    'original_name' => $fileAnswer['original_name'] ?? 'Uploaded PDF',
                    'size' => $fileAnswer['size'] ?? 0,
                    'question_label' => $answer['question_label'] ?? '',
                ]]);

                if (($enriched[0] ?? null) !== null) {
                    $answer['answer'] = $enriched[0];
                }

                return $answer;
            })
            ->values()
            ->all();
    }

    protected function canReviewApplication(ProgramApplication $application): bool
    {
        $letter = strtoupper((string) ($application->scheduleProgram?->program_letter ?? ''));

        // Sports officials can approve/reject pending applications anytime.
        if ($letter === ScheduleProgramService::LETTER_SPORTS) {
            return true;
        }

        $schedule = $application->scheduleProgram;

        if ($schedule === null || $schedule->end_date === null) {
            return true;
        }

        return now()->startOfDay()->gt($schedule->end_date);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function normalizeRequiredDocuments(mixed $documents): array
    {
        if (! is_array($documents)) {
            return [];
        }

        if ($documents === []) {
            return [];
        }

        if (array_is_list($documents)) {
            return $documents;
        }

        return array_values($documents);
    }

    protected function buildKkProfileData(ProgramApplication $application, array $fields): array
    {
        $profileExtras = $this->resolveKkProfileExtras($application);

        $birthday = $application->birthdate?->format('m/d/Y')
            ?? ($profileExtras['birthday'] ?? '');

        $mapped = [
            'last_name' => $this->stringifyValue($application->last_name ?? $profileExtras['last_name'] ?? ''),
            'first_name' => $this->stringifyValue($application->first_name ?? $profileExtras['first_name'] ?? ''),
            'middle_name' => $this->stringifyValue($application->middle_name ?? $profileExtras['middle_name'] ?? ''),
            'suffix' => $this->stringifyValue($application->suffix ?? $profileExtras['suffix'] ?? ''),
            'birthday' => $this->stringifyValue($birthday),
            'age' => $this->stringifyValue($application->age ?? $profileExtras['age'] ?? ''),
            'sex' => $this->stringifyValue($application->sex ?? $profileExtras['sex'] ?? ''),
            'civil_status' => $this->stringifyValue($application->civil_status ?? $profileExtras['civil_status'] ?? ''),
            'contact_number' => $this->stringifyValue($application->contact_number ?? $profileExtras['contact_number'] ?? ''),
            'email' => $this->stringifyValue($application->email ?? $profileExtras['email'] ?? ''),
            'region' => $this->stringifyValue($profileExtras['region'] ?? ''),
            'province' => $this->stringifyValue($profileExtras['province'] ?? ''),
            'city' => $this->stringifyValue($profileExtras['city'] ?? ''),
            'barangay' => $this->stringifyValue($application->barangay ?? $profileExtras['barangay'] ?? ''),
            'purok_zone' => $this->stringifyValue($application->purok ?? $profileExtras['purok_zone'] ?? ''),
            'youth_classification' => $this->stringifyValue($profileExtras['youth_classification'] ?? ''),
            'youth_age_group' => $this->stringifyValue($profileExtras['youth_age_group'] ?? ''),
            'home_address' => trim(implode(', ', array_filter([
                $this->stringifyValue($application->purok ?? $profileExtras['purok_zone'] ?? ''),
                $this->stringifyValue($application->barangay ?? $profileExtras['barangay'] ?? ''),
            ]))),
            'current_school' => $this->stringifyValue($application->school_name ?? ''),
            'year_level' => $this->stringifyValue($application->grade_level ?? ''),
            'course_strand' => $this->stringifyValue($application->course ?? ''),
        ];

        $mapped['full_name'] = trim(implode(' ', array_filter([
            $mapped['first_name'],
            $mapped['middle_name'],
            $mapped['last_name'],
            $mapped['suffix'],
        ], fn ($part) => trim($part) !== '')));

        if ($fields === []) {
            return array_filter($mapped, fn ($value) => $this->stringifyValue($value) !== '');
        }

        $result = [];
        foreach ($fields as $field) {
            $key = (string) $field;
            $value = $this->stringifyValue($mapped[$key] ?? '');
            if ($value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    protected function resolveKkProfileExtras(ProgramApplication $application): array
    {
        if (! $application->kabataan_id) {
            return [];
        }

        $registration = KabataanRegistration::query()
            ->with('barangay')
            ->where('user_id', $application->kabataan_id)
            ->first();

        if ($registration === null) {
            return [];
        }

        $formData = $registration->form_data ?? [];

        $survey = KkSurveyResponse::query()
            ->where('kabataan_registration_id', $registration->id)
            ->first();

        if ($survey !== null) {
            $formData = array_merge($formData, array_filter([
                'last_name' => $survey->last_name,
                'first_name' => $survey->first_name,
                'middle_name' => $survey->middle_name,
                'suffix' => $survey->suffix,
                'birthday' => $survey->birthdate?->format('m/d/Y'),
                'age' => $survey->age !== null ? (string) $survey->age : null,
                'sex' => $survey->sex_assigned_at_birth,
                'civil_status' => $survey->civil_status,
                'contact_number' => $survey->contact_number,
                'email' => $survey->email,
                'region' => $survey->region,
                'province' => $survey->province,
                'city' => $survey->municipality,
                'barangay' => $survey->barangay,
                'purok_zone' => $survey->purok_zone,
                'youth_classification' => $survey->youth_classification,
                'youth_age_group' => $survey->youth_age_group,
            ], fn ($value) => $this->stringifyValue($value) !== ''));
        }

        if (empty($formData['region']) && $registration->barangay) {
            $formData['region'] = $registration->barangay->region ?? '';
            $formData['province'] = $registration->barangay->province ?? '';
            $formData['city'] = $registration->barangay->municipality ?? '';
            $formData['barangay'] = $registration->barangay->name ?? '';
        }

        return collect($formData)
            ->mapWithKeys(fn ($value, $key) => [(string) $key => $this->stringifyValue($value)])
            ->filter(fn ($value) => $value !== '')
            ->all();
    }
}
