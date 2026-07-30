<?php

namespace App\Modules\Program_Accomplishment\Services;

class BudgetComputationService
{
    public function computeRemaining(float $allocated, float $expense): float
    {
        return round($allocated - $expense, 2);
    }

    public function computeUtilizationPercent(float $allocated, float $expense): float
    {
        if ($allocated <= 0) {
            return 0.00;
        }

        return round(($expense / $allocated) * 100, 2);
    }

    public function validate(float $allocated, float $expense): array
    {
        $errors = [];

        if ($allocated < 0) {
            $errors[] = 'Budget allocated cannot be negative.';
        }

        if ($expense < 0) {
            $errors[] = 'Actual expense cannot be negative.';
        }

        if ($expense > $allocated) {
            $errors[] = 'Actual expense cannot exceed the allocated budget.';
        }

        return $errors;
    }
}
