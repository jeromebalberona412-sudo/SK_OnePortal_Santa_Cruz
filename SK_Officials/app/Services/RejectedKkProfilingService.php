<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\RejectedKkProfiling;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectedKkProfilingService
{
    public function recordRejection(
        KabataanRegistration $registration,
        User $reviewer,
        string $reasons,
        ?string $previousUserStatus = null,
        ?string $previousRegistrationStatus = null,
        ?string $previousEvaluationStatus = null,
    ): RejectedKkProfiling {
        return RejectedKkProfiling::query()->updateOrCreate(
            [
                'kabataan_registration_id' => $registration->id,
            ],
            [
                'tenant_id' => $registration->tenant_id,
                'barangay_id' => $registration->barangay_id,
                'rejected_by_user_id' => $reviewer->id,
                'rejection_reason' => $reasons,
                'previous_registration_status' => $previousRegistrationStatus ?? $registration->status,
                'previous_evaluation_status' => $previousEvaluationStatus ?? $registration->evaluation_status,
                'previous_user_status' => $previousUserStatus,
                'rejected_at' => now(),
                'restored_at' => null,
            ]
        );
    }

    /**
     * @return array{status: string, evaluation_status: ?string, user_status: ?string}
     */
    public function resolveRestoreState(RejectedKkProfiling $row, KabataanRegistration $registration): array
    {
        $status = $row->previous_registration_status;
        $evaluation = $row->previous_evaluation_status;
        $userStatus = $row->previous_user_status;

        if (! $status || $status === 'rejected') {
            $status = $registration->user_id ? 'active' : 'pending_verification';
        }

        if (! $evaluation || in_array($evaluation, ['active', 'Auto Approved', 'ID Verified'], true)) {
            $evaluation = 'Not Profiled';
        }

        if (! $userStatus || $userStatus === 'REJECTED') {
            $userStatus = $registration->user_id ? User::STATUS_PENDING_APPROVAL : null;
        }

        return [
            'status' => $status,
            'evaluation_status' => $evaluation,
            'user_status' => $userStatus,
        ];
    }

    public function markRestored(KabataanRegistration $registration): void
    {
        RejectedKkProfiling::query()
            ->where('kabataan_registration_id', $registration->id)
            ->whereNull('restored_at')
            ->update(['restored_at' => now()]);
    }

    public function isAlreadyRejected(KabataanRegistration $registration): bool
    {
        if ($registration->status === 'rejected') {
            return true;
        }

        return RejectedKkProfiling::query()
            ->where('kabataan_registration_id', $registration->id)
            ->whereNull('restored_at')
            ->exists();
    }
}
