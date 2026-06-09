<?php

namespace App\Services;

use App\Models\ProgramApplication;
use App\Models\RejectedScholarship;
use App\Models\RejectedSports;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RejectedProgramApplicationService
{
    public function recordRejection(ProgramApplication $application, User $reviewer, string $letter): Model
    {
        $application->loadMissing('scheduleProgram');
        $schedule = $application->scheduleProgram;

        $payload = [
            'tenant_id' => $schedule?->tenant_id,
            'barangay_id' => $schedule?->barangay_id ?? $reviewer->barangay_id,
            'rejected_by_user_id' => $reviewer->id,
            'rejection_reason' => $application->rejection_reason,
            'rejection_reasons' => $application->rejection_reasons,
            'rejected_at' => $application->reviewed_at ?? now(),
            'restored_at' => null,
        ];

        $model = $this->modelForLetter($letter);

        return $model::query()->updateOrCreate(
            ['program_application_id' => $application->id],
            $payload
        );
    }

    public function markRestored(ProgramApplication $application, string $letter): void
    {
        $this->modelForLetter($letter)::query()
            ->where('program_application_id', $application->id)
            ->whereNull('restored_at')
            ->update(['restored_at' => now()]);
    }

    /**
     * @return class-string<RejectedScholarship|RejectedSports>
     */
    public function modelForLetter(string $letter): string
    {
        return strtoupper(trim($letter)) === 'I'
            ? RejectedSports::class
            : RejectedScholarship::class;
    }

    public function tableForLetter(string $letter): string
    {
        return strtoupper(trim($letter)) === 'I'
            ? 'rejected_sports'
            : 'rejected_scholarships';
    }
}
