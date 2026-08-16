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
        if ($raw === '' || in_array($raw, ['-', '—', '–', 'n/a', 'N/A', 'NA'], true)) {
            return null;
        }

        $raw = preg_replace('/^₱\s*/u', '', $raw) ?? $raw;

        // A bare 4-digit value such as "2025" or "2026" is virtually always a
        // fiscal/calendar year, page number, or document revision year picked
        // up from surrounding text/OCR noise - never a peso amount. Reject it
        // outright here so it can never be reinterpreted as a decimal further
        // down (e.g. mistaken for "20.25").
        if ($this->looksLikeBareYear($raw)) {
            return null;
        }

        if (preg_match('/([\d,]+\.\d{2})/', $raw, $match)) {
            $normalized = str_replace(',', '', $match[1]);

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        if (preg_match('/^([\d,]+),(\d{2})$/', $raw, $matches)) {
            $wholePart = str_replace(',', '', $matches[1]);

            // Guard against OCR/spacing artifacts where a 4-digit year like
            // "2025" picks up a stray comma (e.g. "20,25") and would
            // otherwise be misread as the decimal amount "20.25".
            if ($this->looksLikeBareYear($wholePart)) {
                return null;
            }

            $normalized = $wholePart.'.'.$matches[2];

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';

        if ($this->looksLikeBareYear($cleaned)) {
            return null;
        }

        return $this->isValidNumericAmount($cleaned) ? $cleaned : null;
    }

    /**
     * Detects a bare 4-digit token that looks like a calendar year
     * (1900-2099) with no thousands separator and no decimal component.
     * These show up throughout ABYIP PDFs as fiscal years, revision years,
     * or page headers and must never be treated as budget amounts.
     */
    public function looksLikeBareYear(string $digitsOnly): bool
    {
        return preg_match('/^(19|20)\d{2}$/', $digitsOnly) === 1;
    }

    /**
     * Like numericAmount(), but preserves null instead of coercing a
     * genuinely missing value to '0.00'. Use this for fields (like CO)
     * where "no value was present in the source" is a real, distinct
     * state from "the value is zero" and the database column should
     * store NULL rather than a fake 0.00.
     */
    public function numericAmountOrNull(mixed $value): ?string
    {
        $parsed = $this->parseAmount($value);

        return $parsed !== null ? $this->numericAmount($parsed) : null;
    }

    public function numericAmount(mixed $value): string
    {
        $parsed = $this->parseAmount($value);

        if ($parsed !== null && is_numeric($parsed)) {
            return number_format((float) $parsed, 2, '.', '');
        }

        if (is_scalar($value)) {
            $digitsOnly = preg_replace('/[^0-9]/', '', (string) $value) ?? '';

            // parseAmount() already rejected this value (returned null). If
            // that rejection was because it looks like a bare calendar year,
            // do not let the raw is_numeric() fallback below undo that
            // protection and format it as a peso amount anyway.
            if ($digitsOnly !== '' && $this->looksLikeBareYear($digitsOnly)) {
                return '0.00';
            }
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return '0.00';
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{budget_mooe: ?string, budget_co: ?string, budget_total: ?string}
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
        } elseif ($total === null && $co !== null) {
            // Mirrors the MOOE-only fallback above. Rows under the "Capital
            // Outlay" sub-heading (see AbyipService::parseLineItemsFromText())
            // are parsed with ONLY budget_co populated - MOOE is genuinely
            // absent there, not zero. Without this branch, TOTAL was left
            // NULL for every CO-only row even though TOTAL is effectively
            // always present/derivable in the source table.
            $total = $co;
        }

        if ($mooe !== null && $total !== null && $co === null
            && abs((float) $mooe - (float) $total) < 0.01) {
            $co = '0.00';
        } elseif ($co !== null && $total !== null && $mooe === null
            && abs((float) $co - (float) $total) < 0.01) {
            $mooe = '0.00';
        }

        // Use the null-preserving formatter here: if a field genuinely has
        // no value in the source (e.g. CO left blank in the ABYIP table),
        // that should be stored as NULL, not a fabricated '0.00'.
        return [
            'budget_mooe' => $this->numericAmountOrNull($mooe),
            'budget_co' => $this->numericAmountOrNull($co),
            'budget_total' => $this->numericAmountOrNull($total),
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

        return $incomingAmount <= $existingAmount
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

        $amounts = $this->collapseDuplicatedAmountStack($amounts);

        if ($expectedCount <= 0) {
            return $amounts;
        }

        if (count($amounts) > $expectedCount) {
            return array_slice($amounts, 0, $expectedCount);
        }

        return $amounts;
    }

    /**
     * ABYIP tables print MOOE and Total as two identical stacks. Keep one copy
     * so later rows are not treated as extra activity budgets.
     *
     * @param  list<string>  $amounts
     * @return list<string>
     */
    public function collapseDuplicatedAmountStack(array $amounts): array
    {
        $count = count($amounts);
        if ($count < 2 || $count % 2 !== 0) {
            return $amounts;
        }

        $half = intdiv($count, 2);
        $first = array_slice($amounts, 0, $half);
        $second = array_slice($amounts, $half);

        return $first === $second ? $first : $amounts;
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
