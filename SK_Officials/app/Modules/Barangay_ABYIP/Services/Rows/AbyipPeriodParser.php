<?php

namespace App\Modules\Barangay_ABYIP\Services\Rows;

use Carbon\Carbon;

class AbyipPeriodParser
{
    /**
     * @return array{start: ?string, end: ?string}
     */
    public function parse(?string $period): array
    {
        if ($period === null || trim($period) === '') {
            return ['start' => null, 'end' => null];
        }

        $normalizedPeriod = preg_replace('/(?<=[A-Za-z])(?=\d)/', ' ', $period) ?? $period;
        $normalizedPeriod = preg_replace('/(?<=\d)(?=[A-Za-z])/', ' ', $normalizedPeriod) ?? $normalizedPeriod;
        $normalizedPeriod = preg_replace('/\s+to\s+/i', ' to ', $normalizedPeriod) ?? $normalizedPeriod;

        if (preg_match('/([A-Za-z]+\s+\d{1,2},?\s+\d{4})\s*(?:to|-)\s*([A-Za-z]+\s+\d{1,2},?\s+\d{4})/i', $normalizedPeriod, $matches)) {
            try {
                return [
                    'start' => Carbon::parse($matches[1])->toDateString(),
                    'end' => Carbon::parse($matches[2])->toDateString(),
                ];
            } catch (\Throwable) {
                return ['start' => null, 'end' => null];
            }
        }

        if (preg_match('/([A-Za-z]+)\.?\s*(\d{1,2})\s*[-–—to]+\s*([A-Za-z]+)\.?\s*(\d{1,2}),?\s*(\d{4})/i', $normalizedPeriod, $matches)) {
            try {
                return [
                    'start' => Carbon::parse($matches[1].' '.$matches[2].' '.$matches[5])->toDateString(),
                    'end' => Carbon::parse($matches[3].' '.$matches[4].' '.$matches[5])->toDateString(),
                ];
            } catch (\Throwable) {
                return ['start' => null, 'end' => null];
            }
        }

        return ['start' => null, 'end' => null];
    }
}
