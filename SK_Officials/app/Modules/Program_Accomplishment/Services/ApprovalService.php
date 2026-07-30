<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\ProgramAccomplishmentReport;
use Carbon\Carbon;

class ApprovalService
{
    public function approve(ProgramAccomplishmentReport $report): ProgramAccomplishmentReport
    {
        $report->update([
            'accomplishment_status' => 'Approved',
            'approved_at' => Carbon::now(),
        ]);

        return $report->fresh();
    }

    public function reject(ProgramAccomplishmentReport $report, ?string $reason = null): ProgramAccomplishmentReport
    {
        $report->update([
            'accomplishment_status' => 'Rejected',
            'remarks' => $reason ?? $report->remarks,
            'approved_at' => null,
            'published_at' => null,
        ]);

        return $report->fresh();
    }
}
