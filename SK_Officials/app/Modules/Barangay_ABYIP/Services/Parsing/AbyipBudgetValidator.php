<?php

namespace App\Modules\Barangay_ABYIP\Services\Parsing;

use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;

class AbyipBudgetValidator
{
    public const STATUS_VALID = 'valid';

    public const STATUS_WARNING = 'warning';

    public function __construct(private readonly AbyipNumericNormalizer $normalizer) {}

    /**
     * Validate MOOE + CO = Total without mutating the source values.
     *
     * @param  array<string, mixed>  $row
     * @return array{
     *     validation_status: string,
     *     validation_message: ?string,
     *     manual_review_required: bool,
     *     mooe: ?string,
     *     co: ?string,
     *     total: ?string
     * }
     */
    public function validate(array $row): array
    {
        $mooe = $this->normalizer->numericAmountOrNull($row['budget_mooe'] ?? $row['mooe'] ?? null);
        $co = $this->normalizer->numericAmountOrNull($row['budget_co'] ?? $row['co'] ?? null);
        $total = $this->normalizer->numericAmountOrNull($row['budget_total'] ?? $row['total'] ?? null);

        $mooeValue = $mooe !== null ? (float) $mooe : 0.0;
        $coValue = $co !== null ? (float) $co : 0.0;
        $totalValue = $total !== null ? (float) $total : 0.0;

        $hasAny = $mooe !== null || $co !== null || $total !== null;

        if (! $hasAny) {
            return [
                'validation_status' => self::STATUS_WARNING,
                'validation_message' => 'Budget values were not found in the source PDF.',
                'manual_review_required' => true,
                'mooe' => $mooe,
                'co' => $co,
                'total' => $total,
            ];
        }

        if ($total === null) {
            return [
                'validation_status' => self::STATUS_WARNING,
                'validation_message' => 'Total is missing. Source MOOE/CO values were not changed.',
                'manual_review_required' => true,
                'mooe' => $mooe,
                'co' => $co,
                'total' => $total,
            ];
        }

        $sum = round($mooeValue + $coValue, 2);

        if (abs($sum - $totalValue) > 0.01) {
            return [
                'validation_status' => self::STATUS_WARNING,
                'validation_message' => sprintf(
                    'MOOE (%s) + CO (%s) = %s, but Total is %s.',
                    $mooe ?? '0.00',
                    $co ?? '0.00',
                    number_format($sum, 2, '.', ''),
                    $total
                ),
                'manual_review_required' => true,
                'mooe' => $mooe,
                'co' => $co,
                'total' => $total,
            ];
        }

        return [
            'validation_status' => self::STATUS_VALID,
            'validation_message' => null,
            'manual_review_required' => false,
            'mooe' => $mooe,
            'co' => $co,
            'total' => $total,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public function annotate(array $rows): array
    {
        foreach ($rows as &$row) {
            $rowType = (string) ($row['row_type'] ?? '');
            if (! in_array($rowType, ['data', 'activity', 'expenditure'], true)) {
                continue;
            }

            $result = $this->validate($row);
            $row['validation_status'] = $result['validation_status'];
            $row['validation_message'] = $result['validation_message'];
            $row['manual_review_required'] = $result['manual_review_required']
                || (bool) ($row['manual_review_required'] ?? false);
        }
        unset($row);

        return $rows;
    }
}
