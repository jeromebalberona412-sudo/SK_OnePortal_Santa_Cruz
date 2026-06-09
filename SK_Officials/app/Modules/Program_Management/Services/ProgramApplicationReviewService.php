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
    ) {
    }
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBarangay(User $user, string $letter): array
    {
        if ($user->barangay_id === null) {
            return [];
        }

        $letter = strtoupper(trim($letter));

        return ProgramApplication::query()
            ->with(['scheduleProgram', 'kabataan'])
            ->whereHas('scheduleProgram', function ($query) use ($user, $letter) {
                $query->where('barangay_id', $user->barangay_id)
                    ->where('program_letter', $letter);
            })
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

        if ($application->status !== ProgramApplication::STATUS_PENDING) {
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
        $applications = collect($this->listForBarangay($user, $letter));

        return [
            'total' => $applications->count(),
            'pending' => $applications->where('status', ProgramApplication::STATUS_PENDING)->count(),
            'approved' => $applications->where('status', ProgramApplication::STATUS_APPROVED)->count(),
            'rejected' => $applications->where('status', ProgramApplication::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatApplicationModel(ProgramApplication $application, bool $withDetails = false): array
    {
        return $this->formatApplication($application, $withDetails);
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
            'created_at' => $application->created_at?->toIso8601String(),
            'rejection_reason' => $application->rejection_reason,
            'rejection_reasons' => $application->rejection_reasons ?? [],
        ];

        if ($withDetails) {
            $formatted['birthdate'] = $application->birthdate?->toDateString();
            $formatted['civil_status'] = $application->civil_status;
            $formatted['purok'] = $application->purok;
            $formatted['school_name'] = $application->school_name;
            $formatted['grade_level'] = $application->grade_level;
            $formatted['course'] = $application->course;
            $formatted['custom_answers'] = $application->custom_answers ?? [];
            $formatted['required_documents'] = $application->required_documents ?? [];
            $formatted['schedule_program'] = $application->scheduleProgram ? [
                'id' => $application->scheduleProgram->id,
                'program_type' => $application->scheduleProgram->program_type,
                'custom_questions' => $application->scheduleProgram->custom_questions ?? [],
            ] : null;
        }

        return $formatted;
    }
}
