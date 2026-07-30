<?php

namespace App\Modules\Program_Accomplishment\Services;

use App\Models\ProgramAccomplishmentReport;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function annualStats(int $barangayId, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $rows = ProgramAccomplishmentReport::forBarangay($barangayId)
            ->whereYear('date_completed', $year)
            ->select(
                DB::raw('COUNT(*) as completed_count'),
                DB::raw('COALESCE(SUM(budget_allocated), 0) as total_budget_allocated'),
                DB::raw('COALESCE(SUM(actual_expense), 0) as total_actual_expense'),
                DB::raw('COALESCE(SUM(participants_count), 0) as total_participants')
            )
            ->first();

        $allocated = (float) ($rows->total_budget_allocated ?? 0);
        $expense = (float) ($rows->total_actual_expense ?? 0);
        $remaining = $allocated - $expense;
        $utilization = $allocated > 0 ? round(($expense / $allocated) * 100, 2) : 0;

        return [
            'year' => $year,
            'completed_programs' => (int) ($rows->completed_count ?? 0),
            'total_budget_allocated' => $allocated,
            'total_actual_expense' => $expense,
            'remaining_budget' => max(0, $remaining),
            'total_participants' => (int) ($rows->total_participants ?? 0),
            'budget_utilization_percent' => $utilization,
        ];
    }
}
