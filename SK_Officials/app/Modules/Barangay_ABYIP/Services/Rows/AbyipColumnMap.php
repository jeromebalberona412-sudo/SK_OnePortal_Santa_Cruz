<?php

namespace App\Modules\Barangay_ABYIP\Services\Rows;

class AbyipColumnMap
{
    public const DEFAULT_ORDER = [
        'code',
        'ppa_name',
        'description',
        'expected_result',
        'performance_indicator',
        'period_of_implementation',
        'budget_mooe',
        'budget_co',
        'budget_total',
        'person_responsible',
    ];

    /**
     * @param  list<string>  $headerCells
     * @return list<string>
     */
    public function detect(array $headerCells): array
    {
        $map = [];

        foreach ($headerCells as $index => $cell) {
            $key = $this->normalizeHeader($cell);
            if ($key !== null) {
                $map[$index] = $key;
            }
        }

        return $map === [] ? self::DEFAULT_ORDER : $this->fillMissing($map, count($headerCells));
    }

    /**
     * Map row cells by detected or default column positions. Empty cells stay empty.
     *
     * @param  list<string>  $cells
     * @param  list<string>|null  $columnKeys
     * @return array<string, ?string>
     */
    public function mapCells(array $cells, ?array $columnKeys = null): array
    {
        $keys = $columnKeys ?: self::DEFAULT_ORDER;
        $mapped = [];

        foreach ($keys as $index => $key) {
            $mapped[$key] = array_key_exists($index, $cells) ? $cells[$index] : null;
        }

        return $mapped;
    }

    public function normalizeHeader(string $header): ?string
    {
        $normalized = strtolower(preg_replace('/\s+/u', ' ', trim($header)) ?? $header);
        $normalized = str_replace(['mo o e', 'm.o.o.e'], 'mooe', $normalized);

        return match (true) {
            str_contains($normalized, 'code') && ! str_contains($normalized, 'postal') => 'code',
            str_contains($normalized, 'ppa') || str_contains($normalized, 'program') => 'ppa_name',
            str_contains($normalized, 'description') => 'description',
            str_contains($normalized, 'expected') => 'expected_result',
            str_contains($normalized, 'performance') || str_contains($normalized, 'indicator') => 'performance_indicator',
            str_contains($normalized, 'period') => 'period_of_implementation',
            $normalized === 'mooe' || str_contains($normalized, 'mooe') => 'budget_mooe',
            $normalized === 'co' || preg_match('/^co\b/', $normalized) === 1 => 'budget_co',
            $normalized === 'total' || str_starts_with($normalized, 'total') => 'budget_total',
            str_contains($normalized, 'person') || str_contains($normalized, 'responsible') => 'person_responsible',
            default => null,
        };
    }

    /**
     * @param  array<int, string>  $map
     * @return list<string>
     */
    private function fillMissing(array $map, int $count): array
    {
        $keys = [];
        $used = array_flip($map);

        for ($index = 0; $index < max($count, count(self::DEFAULT_ORDER)); $index++) {
            if (isset($map[$index])) {
                $keys[$index] = $map[$index];

                continue;
            }

            $fallback = self::DEFAULT_ORDER[$index] ?? null;
            if ($fallback !== null && ! isset($used[$fallback])) {
                $keys[$index] = $fallback;
            }
        }

        ksort($keys);

        return array_values($keys);
    }
}
