<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\ProgramAccomplishmentReport;
use Carbon\Carbon;

class PublishingService
{
    public function publish(ProgramAccomplishmentReport $report): ProgramAccomplishmentReport
    {
        if ($report->accomplishment_status !== 'Approved') {
            throw new \RuntimeException('Only approved reports can be published.');
        }

        $report->update([
            'accomplishment_status' => 'Published',
            'published_at' => Carbon::now(),
        ]);

        return $report->fresh();
    }

    public function unpublish(ProgramAccomplishmentReport $report): ProgramAccomplishmentReport
    {
        if ($report->accomplishment_status !== 'Published') {
            throw new \RuntimeException('Only published reports can be unpublished.');
        }

        $report->update([
            'accomplishment_status' => 'Approved',
            'published_at' => null,
        ]);

        return $report->fresh();
    }
}
