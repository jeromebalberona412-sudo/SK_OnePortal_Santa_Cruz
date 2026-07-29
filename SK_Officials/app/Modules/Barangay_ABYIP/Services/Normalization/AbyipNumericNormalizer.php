<?php

namespace App\Modules\Barangay_ABYIP\Services\Normalization;

class AbyipNumericNormalizer
{
    public function parseAmount(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '-') {
            return null;
        }

        if (preg_match('/([\d,]+\.\d{2})/', $raw, $match)) {
            $normalized = str_replace(',', '', $match[1]);

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        if (preg_match('/^([\d,]+),(\d{2})$/', $raw, $matches)) {
            $normalized = str_replace(',', '', $matches[1]).'.'.$matches[2];

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';

        return $this->isValidNumericAmount($cleaned) ? $cleaned : null;
    }

    public function numericAmount(mixed $value): string
    {
        $parsed = $this->parseAmount($value);

        if ($parsed !== null && is_numeric($parsed)) {
            return number_format((float) $parsed, 2, '.', '');
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return '0.00';
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{budget_mooe: string, budget_co: string, budget_total: string}
     */
    public function normalizeBudgetFields(array $row): array
    {
        $mooe = $this->parseAmount($row['budget_mooe'] ?? null);
        $co = $this->parseAmount($row['budget_co'] ?? null);
        $total = $this->parseAmount($row['budget_total'] ?? null);

        if ($total === null && $mooe !== null && $co !== null) {
            $total = (string) round((float) $mooe + (float) $co, 2);
        } elseif ($total === null && $mooe !== null) {
            $total = $mooe;
        }

        return [
            'budget_mooe' => $this->numericAmount($mooe),
            'budget_co' => $this->numericAmount($co),
            'budget_total' => $this->numericAmount($total),
        ];
    }

    public function preferBudgetAmount(mixed $existing, mixed $incoming, string $field): mixed
    {
        if (! in_array($field, ['barangay_estimated_budget', 'sk_fund_amount', 'total_budget', 'budget_mooe', 'budget_co', 'budget_total'], true)) {
            return $incoming ?? $existing;
        }

        $existingAmount = (float) $this->numericAmount($existing);
        $incomingAmount = (float) $this->numericAmount($incoming);

        if ($incomingAmount <= 0 && $existingAmount <= 0) {
            return '0.00';
        }

        if ($incomingAmount <= 0) {
            return $this->numericAmount($existing);
        }

        if ($existingAmount <= 0) {
            return $this->numericAmount($incoming);
        }

        return $incomingAmount >= $existingAmount
            ? $this->numericAmount($incoming)
            : $this->numericAmount($existing);
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    public function normalizeDocumentForInsert(array $parsed): array
    {
        $parsed = $this->normalizeDocumentBudgets($parsed);

        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);
        $total = (float) $this->numericAmount($parsed['total_budget'] ?? 0);
        $percentage = (float) $this->numericAmount($parsed['sk_fund_percentage'] ?? 10);

        if ($percentage <= 0) {
            $percentage = 10.0;
        }

        if ($skFund > 0 && $barangay <= 0) {
            $barangay = round($skFund / ($percentage / 100), 2);
        }

        if ($barangay > 0 && $skFund <= 0) {
            $skFund = round($barangay * ($percentage / 100), 2);
        }

        if ($total <= 0 && $skFund > 0) {
            $total = $skFund;
        }

        $parsed['barangay_estimated_budget'] = $this->numericAmount($barangay);
        $parsed['sk_fund_percentage'] = $this->numericAmount($percentage);
        $parsed['sk_fund_amount'] = $this->numericAmount($skFund);
        $parsed['total_budget'] = $this->numericAmount($total);

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    public function normalizeDocumentBudgets(array $parsed): array
    {
        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);
        $total = (float) $this->numericAmount($parsed['total_budget'] ?? 0);
        $percentage = (float) $this->numericAmount($parsed['sk_fund_percentage'] ?? 10);

        if ($percentage <= 0) {
            $percentage = 10.0;
        }

        if ($skFund > 1000 && ($barangay <= 0 || $barangay < ($skFund / 2))) {
            $parsed['barangay_estimated_budget'] = number_format($skFund / ($percentage / 100), 2, '.', '');
        }

        if ($total <= 0 && $skFund > 0) {
            $parsed['total_budget'] = number_format($skFund, 2, '.', '');
        }

        if ($skFund <= 0 && $barangay > 0) {
            $parsed['sk_fund_amount'] = number_format($barangay * ($percentage / 100), 2, '.', '');
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    public function resolveSkFundPercentage(array $parsed): string
    {
        $percentage = $this->parseAmount($parsed['sk_fund_percentage'] ?? null);

        if ($percentage !== null && (float) $percentage > 0) {
            return $this->numericAmount($percentage);
        }

        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);

        if ($barangay > 0 && $skFund > 0) {
            return $this->numericAmount(round($skFund / $barangay * 100, 2));
        }

        return '10.00';
    }

    /**
     * @param  list<string|null>  $amounts
     * @return list<string|null>
     */
    public function normalizeBudgetAmountList(array $amounts, int $expectedCount): array
    {
        $amounts = array_values(array_filter(
            array_map(fn ($amount) => $this->parseAmount($amount), $amounts),
            fn (?string $amount) => $amount !== null
        ));

        if ($expectedCount <= 0) {
            return $amounts;
        }

        if (count($amounts) === $expectedCount * 2) {
            return array_slice($amounts, 0, $expectedCount);
        }

        if (count($amounts) > $expectedCount) {
            return array_slice($amounts, 0, $expectedCount);
        }

        return $amounts;
    }

    /**
     * @return list<string>
     */
    public function parseInlineAmounts(string $line): array
    {
        preg_match_all('/[\d,]+(?:\.\d{2}|,\d{2})/', $line, $matches);

        return array_values(array_filter(array_map(
            fn (string $amount) => $this->parseAmount($amount),
            $matches[0] ?? []
        ), fn (?string $amount) => $amount !== null));
    }

    /**
     * @return list<string>
     */
    public function parseAmountsFromCell(?string $cell): array
    {
        if ($cell === null || trim($cell) === '') {
            return [];
        }

        $amounts = [];
        foreach (preg_split('/\R/u', $cell) ?: [] as $line) {
            foreach ($this->parseInlineAmounts($line) as $amount) {
                $amounts[] = $amount;
            }
        }

        return $amounts;
    }

    public function isValidNumericAmount(string $value): bool
    {
        if ($value === '' || $value === '.' || $value === '-' || $value === '-.') {
            return false;
        }

        return preg_match('/^-?\d+(\.\d+)?$/', $value) === 1 && is_numeric($value);
    }
}
