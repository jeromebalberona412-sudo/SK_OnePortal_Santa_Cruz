<?php

namespace App\Modules\Program_Management\Services;

use App\Models\ProgramApplication;
use App\Models\User;
use App\Services\RejectedProgramApplicationService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProgramApplicationReviewService
{
    public function __construct(
        private readonly RejectedProgramApplicationService $rejectedService,
        private readonly ProgramDocumentService $documentService,
    ) {
    }
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
    public function updatePaymentStatus(User $user, int $applicationId, string $letter, string $paymentStatus): array
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

        $application->update(['payment_status' => $normalized]);

        return $this->formatApplication($application->fresh(['scheduleProgram', 'kabataan']));
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

        $formatted = [
            'id' => $application->id,
            'program_id' => $application->program_id,
            'program_name' => $application->scheduleProgram?->program_type ?? $application->scheduleProgram?->program_name,
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
            'documents_count' => count($normalizedDocs = $this->normalizeRequiredDocuments($application->required_documents ?? [])),
            'document_labels' => collect($normalizedDocs)
                ->map(fn (array $doc) => (string) ($doc['question_label'] ?? $doc['label'] ?? $doc['original_name'] ?? 'Uploaded PDF'))
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
            $formatted['custom_answers'] = collect($application->custom_answers ?? [])
                ->map(function (array $answer) use ($documentsByQuestionId) {
                    $questionId = (string) ($answer['question_id'] ?? '');
                    if (($answer['question_type'] ?? '') === 'file' && $documentsByQuestionId->has($questionId)) {
                        $answer['answer'] = $documentsByQuestionId->get($questionId);
                    }

                    return $answer;
                })
                ->values()
                ->all();
            $kkFields = $application->scheduleProgram?->kk_profiling_fields ?? [];
            $formatted['kk_profile_data'] = $this->buildKkProfileData($application, $kkFields);
            $formatted['schedule_program'] = $application->scheduleProgram ? [
                'id' => $application->scheduleProgram->id,
                'program_type' => $application->scheduleProgram->program_type,
                'custom_questions' => $application->scheduleProgram->custom_questions ?? [],
                'kk_profiling_fields' => $kkFields,
            ] : null;
        }

        return $formatted;
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, string>
     */
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
        $mapped = [
            'last_name' => (string) ($application->last_name ?? ''),
            'first_name' => (string) ($application->first_name ?? ''),
            'middle_name' => (string) ($application->middle_name ?? ''),
            'suffix' => (string) ($application->suffix ?? ''),
            'birthday' => $application->birthdate?->format('Y-m-d') ?? '',
            'age' => (string) ($application->age ?? ''),
            'sex' => (string) ($application->sex ?? ''),
            'civil_status' => (string) ($application->civil_status ?? ''),
            'contact_number' => (string) ($application->contact_number ?? ''),
            'email' => (string) ($application->email ?? ''),
            'barangay' => (string) ($application->barangay ?? ''),
            'purok_zone' => (string) ($application->purok ?? ''),
            'home_address' => trim(implode(', ', array_filter([
                (string) ($application->purok ?? ''),
                (string) ($application->barangay ?? ''),
            ]))),
            'current_school' => (string) ($application->school_name ?? ''),
            'year_level' => (string) ($application->grade_level ?? ''),
            'course_strand' => (string) ($application->course ?? ''),
        ];

        $mapped['full_name'] = trim(implode(' ', array_filter([
            $mapped['first_name'],
            $mapped['middle_name'],
            $mapped['last_name'],
            $mapped['suffix'],
        ], fn ($part) => trim($part) !== '')));

        if ($fields === []) {
            return array_filter($mapped, fn ($value) => trim((string) $value) !== '');
        }

        $result = [];
        foreach ($fields as $field) {
            $key = (string) $field;
            $value = trim((string) ($mapped[$key] ?? ''));
            if ($value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
