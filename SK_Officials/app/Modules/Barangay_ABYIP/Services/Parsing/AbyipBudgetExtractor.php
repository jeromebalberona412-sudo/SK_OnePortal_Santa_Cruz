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
    public function extractBudgetAndPersonFromLine(string $line): array
    {
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
        $person = null;

        $personPatterns = [
            '/Sangguniang\s+Kabataan\s+Council\s*\/\s*BADAC/i',
            '/Sangguniang\s+Kabataan\s+Council\s*\/\s*ALS/i',
            '/SK\s+Chairman\s*\/\s*SK\s+Treasurer/i',
            '/Sangguniang\s+Kabataan\s+Council/i',
            '/Sangguniang\s+Kabataan\s+Counci[l]?/i',
            '/SK\s+Treasurer/i',
            '/SK\s+Chairman/i',
            '/SK\s+Chairperson/i',
        ];

        foreach ($personPatterns as $pattern) {
            if (preg_match($pattern, $line, $match)) {
                $person = $this->extractPersonResponsibleFromValue($match[0]);
                $line = trim(str_replace($match[0], '', $line));
                break;
            }
        }

        $amounts = $this->normalizer->parseInlineAmounts($line);
        $mooe = null;
        $co = null;
        $total = null;

        if (count($amounts) >= 3) {
            $mooe = $amounts[count($amounts) - 3];
            $co = $amounts[count($amounts) - 2];
            $total = $amounts[count($amounts) - 1];
        } elseif (count($amounts) === 2) {
            $mooe = $amounts[0];
            $total = $amounts[1];
        } elseif (count($amounts) === 1) {
            $mooe = $amounts[0];
            $total = $amounts[0];
        }

        return [
            'budget_mooe' => $mooe,
            'budget_co' => $co,
            'budget_total' => $total,
            'person_responsible' => $person,
        ];
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

        if ($lineNorm === '' || $ppaNorm === '') {
            return false;
        }

        if (str_contains($lineNorm, $ppaNorm)) {
            return true;
        }

        $ppaWords = preg_split('/\s+/u', $ppaNorm) ?: [];
        $firstWord = $ppaWords[0] ?? '';

        if (mb_strlen($firstWord) >= 4 && str_contains($lineNorm, $firstWord)) {
            return true;
        }

        $prefix = mb_substr($ppaNorm, 0, min(24, mb_strlen($ppaNorm)));

        return $prefix !== '' && str_contains($lineNorm, $prefix);
    }

    public function lineContainsBudgetAmounts(string $line): bool
    {
        return preg_match('/[\d,]+\.\d{2}/', $line) === 1;
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
        if (preg_match('/^(Person\s*Responsible|MOOE|CO|Total|Code|PPAs|Description|Expected|Performance|Period)$/i', $value)) {
            return true;
        }

        if (preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $value)) {
            return true;
        }

        if (preg_match('/^\d[\d,.\s]*$/', $value)) {
            return true;
        }

        if (preg_match('/\b(Receipts|Expenditure|PROGRAM|Capital Outlay)\b/i', $value)) {
            return true;
        }

        if (preg_match('/\b(payment|professional|rendered|payroll|months|charge|incurred|transport|services|nominally|without|given|january|december)\b/i', $value)) {
            return true;
        }

        return mb_strlen($value) < 3;
    }

    protected function normalizePersonResponsible(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return str_replace(
            ['SangguniangKabataanCouncil', 'SKTreasurer', 'SKChairman'],
            ['Sangguniang Kabataan Council', 'SK Treasurer', 'SK Chairman'],
            $value
        );
    }
}
