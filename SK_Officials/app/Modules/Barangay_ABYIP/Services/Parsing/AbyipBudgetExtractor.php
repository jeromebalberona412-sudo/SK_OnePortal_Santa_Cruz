<?php

namespace App\Modules\Barangay_ABYIP\Services\Parsing;

use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;

class AbyipBudgetExtractor
{
    public function __construct(private readonly AbyipNumericNormalizer $normalizer) {}

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function supplementStructuredRows(array $items, string $text, callable $finalizeRow): array
    {
        $rawLines = $this->extractRawTextLines($text);

        foreach ($items as &$item) {
            $needsBudget = (float) $this->normalizer->numericAmount($item['budget_mooe'] ?? 0) <= 0
                && (float) $this->normalizer->numericAmount($item['budget_co'] ?? 0) <= 0
                && (float) $this->normalizer->numericAmount($item['budget_total'] ?? 0) <= 0;
            $needsPerson = empty($item['person_responsible']);

            if (! $needsBudget && ! $needsPerson) {
                continue;
            }

            $ppaName = trim((string) ($item['ppa_name'] ?? ''));
            if ($ppaName === '') {
                continue;
            }

            foreach ($rawLines as $line) {
                if (! $this->rawLineMatchesPpa($line, $ppaName)) {
                    continue;
                }

                $extracted = $this->extractBudgetAndPersonFromLine($line);
                if ($needsBudget) {
                    foreach (['budget_mooe', 'budget_co', 'budget_total'] as $field) {
                        if (! empty($extracted[$field])) {
                            $item[$field] = $this->normalizer->preferBudgetAmount(
                                $item[$field] ?? null,
                                $extracted[$field],
                                $field
                            );
                        }
                    }
                }

                if ($needsPerson && ! empty($extracted['person_responsible'])) {
                    $item['person_responsible'] = $extracted['person_responsible'];
                }

                $needsBudget = (float) $this->normalizer->numericAmount($item['budget_mooe'] ?? 0) <= 0
                    && (float) $this->normalizer->numericAmount($item['budget_co'] ?? 0) <= 0
                    && (float) $this->normalizer->numericAmount($item['budget_total'] ?? 0) <= 0;
                $needsPerson = empty($item['person_responsible']);

                if (! $needsBudget && ! $needsPerson) {
                    break;
                }
            }

            if ($needsBudget || $needsPerson) {
                $item = $finalizeRow($item);
            }
        }
        unset($item);

        return $items;
    }

    /**
     * @return array{
     *     budget_mooe: ?string,
     *     budget_co: ?string,
     *     budget_total: ?string,
     *     person_responsible: ?string
     * }
     */
    public function extractBudgetAndPersonFromLine(string $line, string $budgetColumn = 'mooe'): array
    {
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
        $person = null;

        // \s* (not \s+): PDF/OCR extraction sometimes drops the space
        // between words (e.g. "SangguniangKabataan Council", "SKChairman/SK
        // Treasurer"). Using \s+ here missed those variants even though
        // extractPersonResponsibleFromValue() below already tolerated them -
        // that inconsistency is why some rows lost their Person Responsible
        // during line-based extraction.
        $personPatterns = [
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*BADAC/i',
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*ALS/i',
            '/SK\s*Chairman\s*\/\s*SK\s*Treasurer/i',
            '/Sangguniang\s*Kabataan\s*Counci[l]?/i',
            '/Sangguniang\s*Kabataan\s*Council/i',
            '/SK\s*Treasurer/i',
            '/SK\s*Chairman/i',
            '/SK\s*Chairperson/i',
        ];

        foreach ($personPatterns as $pattern) {
            if (preg_match($pattern, $line, $match)) {
                $person = $this->extractPersonResponsibleFromValue($match[0]);
                $line = trim(str_replace($match[0], '', $line));
                break;
            }
        }

        // parseInlineAmounts() already ignores page numbers, row numbers, and
        // bare calendar years (see AbyipNumericNormalizer::looksLikeBareYear),
        // since it only accepts decimal-formatted (xx.xx / xx,xx) tokens.
        $amounts = $this->normalizer->parseInlineAmounts($line);
        $mooe = null;
        $co = null;
        $total = null;

        if (count($amounts) >= 3) {
            [$mooe, $co, $total] = $this->resolveBudgetTripleFromAmounts($amounts);
        } elseif (count($amounts) === 2) {
            if ($budgetColumn === 'co') {
                $co = $amounts[0];
            } else {
                $mooe = $amounts[0];
            }
            $total = $amounts[1];
        } elseif (count($amounts) === 1) {
            if ($budgetColumn === 'co') {
                $co = $amounts[0];
            } else {
                $mooe = $amounts[0];
            }
            $total = $amounts[0];
        }

        return [
            'budget_mooe' => $mooe,
            'budget_co' => $co,
            'budget_total' => $total,
            'person_responsible' => $person,
        ];
    }

    /**
     * Picks the MOOE/CO/TOTAL triple out of a list of decimal amounts found
     * on a line. The ABYIP table always lists these three columns in that
     * order, so rather than blindly grabbing the last three numbers (which
     * breaks when an unrelated amount trails the row, or when OCR spacing
     * merges/splits columns), this scans the candidate triples from the end
     * of the line backwards and prefers the first one where MOOE + CO equals
     * TOTAL. If none match exactly, it falls back to the right-most triple,
     * which remains the safest default.
     *
     * @param  list<string>  $amounts
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveBudgetTripleFromAmounts(array $amounts): array
    {
        $count = count($amounts);

        for ($start = $count - 3; $start >= 0; $start--) {
            [$mooe, $co, $total] = array_slice($amounts, $start, 3);

            if ($this->budgetTripleIsConsistent($mooe, $co, $total)) {
                return [$mooe, $co, $total];
            }
        }

        return array_slice($amounts, -3);
    }

    /**
     * Whether MOOE + CO equals TOTAL, within a small rounding tolerance.
     */
    private function budgetTripleIsConsistent(string $mooe, string $co, string $total): bool
    {
        $sum = round((float) $mooe + (float) $co, 2);

        return abs($sum - (float) $total) < 0.01;
    }

    public function extractPersonResponsibleFromValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $patterns = [
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*BADAC/i',
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*ALS/i',
            '/SK\s*Chairman\s*\/\s*SK\s*Treasurer/i',
            '/Sangguniang\s*Kabataan\s*Counci[l]?/i',
            '/Sangguniang\s*Kabataan\s*Council/i',
            '/SK\s*Treasurer/i',
            '/SK\s*Chairman/i',
            '/SK\s*Chairperson/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $match)) {
                return $this->normalizePersonResponsible($match[0]);
            }
        }

        return $this->sanitizePersonResponsible($value);
    }

    /**
     * @return list<string>
     */
    public function extractRawTextLines(string $text): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
            if ($line === '' || str_starts_with($line, '@')) {
                continue;
            }

            $lines[] = $line;
        }

        return $lines;
    }

    public function rawLineMatchesPpa(string $line, string $ppaName): bool
    {
        $lineNorm = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $line) ?? $line));
        $ppaNorm = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $ppaName) ?? $ppaName));

        if ($lineNorm === '' || $ppaNorm === '' || ! $this->lineContainsBudgetAmounts($line)) {
            return false;
        }

        if (str_contains($lineNorm, $ppaNorm)) {
            return true;
        }

        $ppaWords = array_values(array_filter(
            preg_split('/\s+/u', $ppaNorm) ?: [],
            fn (string $word) => mb_strlen($word) >= 3
        ));

        if ($ppaWords === []) {
            return false;
        }

        $matchedWords = 0;
        foreach ($ppaWords as $word) {
            if (str_contains($lineNorm, $word)) {
                $matchedWords++;
            }
        }

        $requiredMatches = (int) ceil(count($ppaWords) * 0.8);

        return $matchedWords >= max(2, $requiredMatches);
    }

    public function lineContainsBudgetAmounts(string $line): bool
    {
        return preg_match('/[\d,]+\.\d{2}/', $line) === 1;
    }

    /**
     * Parse expenditure rows from server-side PDF text where PPA names,
     * descriptions, budget amounts, and person responsible span multiple
     * lines (common in Smalot PDF output).
     *
     * @return list<array<string, mixed>>
     */
    public function parseMultilineExpenditureRows(array $lines, string $currentBudgetColumn = 'mooe'): array
    {
        $items = [];
        $pending = null;
        $inExpenditure = false;

        foreach ($lines as $index => $rawLine) {
            $line = trim(preg_replace('/\s+/u', ' ', (string) $rawLine) ?? (string) $rawLine);
            if ($line === '' || str_starts_with($line, '@')) {
                continue;
            }

            if (preg_match('/^I\.\s*Receipts/i', $line) || preg_match('/^I\.Receipts/i', $line)) {
                $inExpenditure = false;
                $pending = null;

                continue;
            }

            if (preg_match('/^II\.\s*Expenditure/i', $line) || preg_match('/^II\.Expenditure/i', $line)) {
                $inExpenditure = true;
                $pending = null;

                continue;
            }

            if (preg_match('/SK\s+YOUTH\s+DEVELOPMENT/i', $line) || preg_match('/^TOTAL\b/i', $line)) {
                $this->flushPendingExpenditureRow($items, $pending);
                $pending = null;
                $inExpenditure = false;

                continue;
            }

            if (! $inExpenditure || $this->isAbyipTableNoiseLine($line)) {
                continue;
            }

            if (preg_match('/Maintenance and Other Operating Expenses/i', $line)) {
                $currentBudgetColumn = 'mooe';

                continue;
            }

            if (preg_match('/^Capital Outlay\b/i', $line) && ! preg_match('/[\d,]+\.\d{2}/', $line)) {
                $currentBudgetColumn = 'co';

                continue;
            }

            if ($this->lineIsBudgetAmountRow($line)) {
                $budget = $this->extractBudgetFromAmountLine($line, $currentBudgetColumn);
                $person = $budget['person_responsible']
                    ?? $this->collectPersonFromFollowingLines($lines, $index);

                if ($pending !== null) {
                    $row = array_merge($pending, $budget);
                    $row['person_responsible'] = $this->extractPersonResponsibleFromValue($person ?? $row['person_responsible'] ?? null);
                    $items[] = $row;
                    $pending = null;

                    continue;
                }

                $items[] = [
                    'row_type' => 'data',
                    'ppa_name' => null,
                    'budget_mooe' => $budget['budget_mooe'],
                    'budget_co' => $budget['budget_co'],
                    'budget_total' => $budget['budget_total'],
                    'person_responsible' => $this->extractPersonResponsibleFromValue($person),
                    'program_section' => 'Expenditure Program',
                ];

                continue;
            }

            if ($this->lineLooksLikePersonFragment($line)) {
                if ($pending !== null) {
                    $pending['person_responsible'] = $this->mergePersonFragment(
                        (string) ($pending['person_responsible'] ?? ''),
                        $line
                    );
                } elseif ($items !== []) {
                    $lastIndex = count($items) - 1;
                    $items[$lastIndex]['person_responsible'] = $this->mergePersonFragment(
                        (string) ($items[$lastIndex]['person_responsible'] ?? ''),
                        $line
                    );
                }

                continue;
            }

            if ($this->lineLooksLikeNewPpaStart($line)) {
                $this->flushPendingExpenditureRow($items, $pending);
                $pending = [
                    'row_type' => 'data',
                    'ppa_name' => $line,
                    'description' => null,
                    'expected_result' => null,
                    'performance_indicator' => null,
                    'period_of_implementation' => null,
                    'person_responsible' => null,
                    'budget_mooe' => null,
                    'budget_co' => null,
                    'budget_total' => null,
                    'program_section' => 'Expenditure Program',
                ];

                continue;
            }

            if ($pending !== null) {
                $pending['description'] = trim(((string) ($pending['description'] ?? '')).' '.$line);
            }
        }

        $this->flushPendingExpenditureRow($items, $pending);

        return $items;
    }

    public function lineIsBudgetAmountRow(string $line): bool
    {
        if (! preg_match('/[\d,]+\.\d{2}/', $line)) {
            return false;
        }

        $amounts = $this->normalizer->parseInlineAmounts($line);

        return count($amounts) >= 1 && count($amounts) <= 4;
    }

    /**
     * @return array{budget_mooe: ?string, budget_co: ?string, budget_total: ?string, person_responsible: ?string}
     */
    public function extractBudgetFromAmountLine(string $line, string $budgetColumn = 'mooe'): array
    {
        $extracted = $this->extractBudgetAndPersonFromLine($line);
        $amounts = $this->normalizer->parseInlineAmounts($line);

        if (count($amounts) >= 3) {
            [$mooe, $co, $total] = $this->resolveBudgetTripleFromAmountsList($amounts);

            return [
                'budget_mooe' => $mooe,
                'budget_co' => $co,
                'budget_total' => $total,
                'person_responsible' => $extracted['person_responsible'],
            ];
        }

        if (count($amounts) === 2) {
            $primaryField = $budgetColumn === 'co' ? 'budget_co' : 'budget_mooe';
            $otherField = $budgetColumn === 'co' ? 'budget_mooe' : 'budget_co';

            return [
                'budget_mooe' => $primaryField === 'budget_mooe' ? $amounts[0] : null,
                'budget_co' => $primaryField === 'budget_co' ? $amounts[0] : null,
                'budget_total' => $amounts[1],
                'person_responsible' => $extracted['person_responsible'],
            ];
        }

        if (count($amounts) === 1) {
            $primaryField = $budgetColumn === 'co' ? 'budget_co' : 'budget_mooe';

            return [
                'budget_mooe' => $primaryField === 'budget_mooe' ? $amounts[0] : null,
                'budget_co' => $primaryField === 'budget_co' ? $amounts[0] : null,
                'budget_total' => $amounts[0],
                'person_responsible' => $extracted['person_responsible'],
            ];
        }

        return [
            'budget_mooe' => $extracted['budget_mooe'],
            'budget_co' => $extracted['budget_co'],
            'budget_total' => $extracted['budget_total'],
            'person_responsible' => $extracted['person_responsible'],
        ];
    }

    /**
     * @param  list<string>  $lines
     */
    public function collectPersonFromFollowingLines(array $lines, int $startIndex, int $lookahead = 4): ?string
    {
        $parts = [];

        for ($offset = 1; $offset <= $lookahead; $offset++) {
            $next = trim((string) ($lines[$startIndex + $offset] ?? ''));
            if ($next === '' || str_starts_with($next, '@')) {
                break;
            }

            if ($this->lineIsBudgetAmountRow($next) || $this->lineLooksLikeNewPpaStart($next)) {
                break;
            }

            if ($this->isAbyipTableNoiseLine($next)) {
                continue;
            }

            if ($this->lineLooksLikePersonFragment($next) || $this->extractPersonResponsibleFromValue($next)) {
                $parts[] = $next;

                continue;
            }

            if ($parts !== []) {
                break;
            }
        }

        if ($parts === []) {
            return null;
        }

        return $this->extractPersonResponsibleFromValue(implode(' ', $parts));
    }

    public function isAbyipTableNoiseLine(string $line): bool
    {
        if (preg_match('/^(Code|PPAs|Description|Expected|Performance|Period|Budget|Person|MOOE|CO|Total|Responsible|n)$/i', $line)) {
            return true;
        }

        if (preg_match('/^MOOE\s+CO\s+Total$/i', $line)) {
            return true;
        }

        if (preg_match('/^(I\.Receipts|II\.Expenditure)/i', $line)) {
            return false;
        }

        if (preg_match('/^(I\.\s*Receipts|II\.\s*Expenditure|10%|Prepared\s+by|Approved\s+by)/i', $line)) {
            return true;
        }

        if (preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $line)
            && preg_match('/\d{4}/', $line)) {
            return false;
        }

        if (preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\d{2},?$/i', $line)) {
            return false;
        }

        if (preg_match('/^\d{4}$/', $line)) {
            return false;
        }

        if (preg_match('/^(to|and|of|the|in|for|a|an|is|are|be|with|without|months?|payroll|year|calendaryear|numberof|incurred|attendanc|conduct|participation|professional|rendered|nominally|charge|given|movement|transport|government|officers|employees|within|Country|Twelve|ReceiptsProgram)$/i', $line)) {
            return true;
        }

        return preg_match('/^(Costsincurred|Paymentisgiven|professionalservices|thatarerendered|nominallywithout|monthsof|Numberofincurred|traininginthe|calendaryear|movement\/transportof|governmentofficers|employeeswithin|Costsincurredfor|participation\/attendanc|einandconductof|training,conventions|seminars\/workshops|Costincurredfor)$/i', $line);
    }

    public function lineLooksLikeNewPpaStart(string $line): bool
    {
        if ($this->lineIsBudgetAmountRow($line) || $this->isAbyipTableNoiseLine($line)) {
            return false;
        }

        if ($this->lineLooksLikePersonFragment($line)) {
            return false;
        }

        if (preg_match('/^([A-J])\.\s/i', $line)) {
            return false;
        }

        if (mb_strlen($line) < 4) {
            return false;
        }

        return (bool) preg_match(
            '/^(Honoraria|Travel\s+Expenses|Training(?:\s+and\s+Seminar\s+Expenses)?|Laptop(?:\/Computer)?|Computer|Support|Feeding|Sports|Tree|Distribution|Barangay|Livelihood|Food|Medicines|Educational|Youth|Leadership|Seminar|Workshop|Program|Project|Activity|Expenses|MOOE|Capital\s+Outlay)\b/i',
            $line
        );
    }

    public function lineLooksLikePersonFragment(string $line): bool
    {
        return preg_match('/^(Sangguniang|Kabataan|Council|SK\s|SKTreasurer|SKChairman|Treasurer|Chairman|Chairperson|BADAC|ALS)/i', $line) === 1;
    }

    private function mergePersonFragment(string $existing, string $fragment): ?string
    {
        $merged = trim($existing.' '.$fragment);

        return $this->extractPersonResponsibleFromValue($merged) ?? ($merged !== '' ? $merged : null);
    }

    /**
     * @param  list<string>  $amounts
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function resolveBudgetTripleFromAmountsList(array $amounts): array
    {
        $count = count($amounts);

        for ($start = $count - 3; $start >= 0; $start--) {
            [$mooe, $co, $total] = array_slice($amounts, $start, 3);
            $sum = round((float) $mooe + (float) $co, 2);

            if (abs($sum - (float) $total) < 0.01) {
                return [$mooe, $co, $total];
            }
        }

        $slice = array_slice($amounts, -3);

        return [
            $slice[0] ?? null,
            $slice[1] ?? null,
            $slice[2] ?? null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>|null  $pending
     */
    private function flushPendingExpenditureRow(array &$items, ?array $pending): void
    {
        if ($pending === null) {
            return;
        }

        $hasName = trim((string) ($pending['ppa_name'] ?? '')) !== '';
        $hasBudget = (float) $this->normalizer->numericAmount($pending['budget_mooe'] ?? 0) > 0
            || (float) $this->normalizer->numericAmount($pending['budget_co'] ?? 0) > 0
            || (float) $this->normalizer->numericAmount($pending['budget_total'] ?? 0) > 0;

        if ($hasName || $hasBudget) {
            $items[] = $pending;
        }
    }

    protected function sanitizePersonResponsible(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($trimmed === '') {
            return null;
        }

        if ($this->personResponsibleLooksInvalid($trimmed)) {
            return null;
        }

        return $this->normalizePersonResponsible($trimmed);
    }

    protected function personResponsibleLooksInvalid(string $value): bool
    {
        // Exact header/column label matches.
        if (preg_match('/^(Person\s*Responsible|MOOE|CO|Total|Code|PPAs|Description|Expected|Performance|Period)$/i', $value)) {
            return true;
        }

        // Month names (date noise).
        if (preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $value)) {
            return true;
        }

        // Pure numeric values (can't be a person name).
        if (preg_match('/^\d[\d,.\s]*$/', $value)) {
            return true;
        }

        // Section/program labels that aren't person names.
        if (preg_match('/\b(Receipts|Expenditure|PROGRAM|Capital Outlay|Youth|Development|Empowerment)\b/i', $value)) {
            return true;
        }

        // OCR noise patterns - strings that look like merged/partial words
        // from the table headers or descriptions, not person names.
        $noiseWords = [
            'payment', 'professional', 'rendered', 'payroll', 'months?',
            'charge', 'incurred', 'transport', 'services', 'nominally',
            'without', 'given', 'january', 'december', 'movement',
            'government', 'officers', 'employees', 'Calendaryear',
            'Costsincurred', 'participat', 'Costs?', 'incurredfor',
            'within', 'numberof', 'training', 'conduct', 'support',
            'year', 'month', 'attend', 'participation',
            'thatare', 'professionalservices', 'therendered',
            'attendance', 'einandconductof', 'trainingconventions',
            'seminars', 'workshops', 'Costincurredfor',
        ];
        foreach ($noiseWords as $word) {
            if (preg_match('/\b'.$word.'\b/i', str_replace(['.', '/'], ' ', $value))) {
                return true;
            }
        }

        return mb_strlen($value) < 3;
    }

    protected function normalizePersonResponsible(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        // OCR/PDF extraction sometimes merges adjacent words with no space
        // at all, and not always the same way (e.g. "SangguniangKabataan
        // Council" vs "Sangguniang KabataanCouncil" vs "SKChairman"). Insert
        // the missing space at each known word boundary rather than relying
        // on an exhaustive whitelist of exact fully-merged strings, so any
        // combination of merged/partially-merged words comes out canonical.
        $value = preg_replace('/(Sangguniang)(Kabataan)/i', '$1 $2', $value) ?? $value;
        $value = preg_replace('/(Kabataan)(Council|Counci\b)/i', '$1 $2', $value) ?? $value;
        $value = preg_replace('/\b(SK)(Chairman|Treasurer|Chairperson)/i', '$1 $2', $value) ?? $value;

        return trim($value);
    }
}
