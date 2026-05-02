<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use Illuminate\Support\Facades\DB;

class RegistrationEvaluationService
{
    /**
     * Evaluate a registration against previous_kabataan and active users.
     * Returns true if auto-approved.
     */
    public function evaluate(KabataanRegistration $registration): bool
    {
        $subLast  = strtoupper(trim($registration->last_name));
        $subFirst = strtoupper(trim($registration->first_name));
        $subMid   = strtoupper(trim($registration->middle_name ?? ''));

        // 1. Check for duplicate — already active in kabataan_registrations
        $duplicate = KabataanRegistration::where('barangay_id', $registration->barangay_id)
            ->where('id', '!=', $registration->id)
            ->where('status', 'active')
            ->where(DB::raw('UPPER(last_name)'), $subLast)
            ->where(DB::raw('UPPER(first_name)'), $subFirst)
            ->first();

        if ($duplicate) {
            $registration->update([
                'evaluation_status' => 'Duplicate',
                'evaluation_notes'  => ['message' => 'Already exists as an active Kabataan member.'],
            ]);
            return false;
        }

        // 2. Search previous_kabataan — NAME must be similar first
        $previous = DB::table('previous_kabataan')
            ->where('barangay_id', $registration->barangay_id)
            ->get();

        $bestMatch      = null;
        $bestMismatches = [];
        $bestNameScore  = 0;

        foreach ($previous as $prev) {
            $fd = is_string($prev->form_data)
                ? json_decode($prev->form_data, true)
                : (array) $prev->form_data;

            // Parse previous name — could be single NAME field or split
            $prevName = strtoupper(trim($fd['name'] ?? ''));
            if ($prevName) {
                // Single name field — require last name AND first name both appear in it
                // with meaningful length (avoid single-letter false matches)
                $nameSimilar = strlen($subLast) >= 3
                    && strlen($subFirst) >= 2
                    && (
                        $this->isSimilar(trim("$subLast $subFirst"), $prevName)
                        || (str_contains($prevName, $subLast) && str_contains($prevName, $subFirst))
                    );
            } else {
                $prevLast  = strtoupper(trim($prev->last_name ?? ''));
                $prevFirst = strtoupper(trim($prev->first_name ?? ''));
                $nameSimilar = strlen($subLast) >= 3
                    && strlen($subFirst) >= 2
                    && $this->isSimilar($subLast, $prevLast)
                    && $this->isSimilar($subFirst, $prevFirst);
            }

            // Name must be similar — skip if not
            if (!$nameSimilar) continue;

            // Name matched — now check other fields for mismatches
            $mismatches = [];

            $subAge      = (string) ($registration->form_data['age'] ?? '');
            $prevAge     = (string) ($fd['age'] ?? '');
            if ($subAge && $prevAge && !$this->isSimilar($subAge, $prevAge)) {
                $mismatches[] = ['field' => 'age', 'submitted' => $subAge, 'previous' => $prevAge];
            }

            $subBday  = $this->normalizeDate($registration->form_data['birthday'] ?? '');
            $prevBday = $this->normalizeDate($fd['birthday'] ?? '');
            if ($subBday && $prevBday && !$this->isSimilar($subBday, $prevBday)) {
                $mismatches[] = ['field' => 'birthday', 'submitted' => $subBday, 'previous' => $prevBday];
            }

            $subSex  = strtoupper($registration->form_data['sex'] ?? '');
            $prevSex = strtoupper($fd['sex'] ?? '');
            if ($subSex && $prevSex && !$this->isSimilar($subSex, $prevSex)) {
                $mismatches[] = ['field' => 'sex', 'submitted' => $subSex, 'previous' => $prevSex];
            }

            // Track best match (fewest mismatches)
            $nameScore = $prevName
                ? similar_text($prevName, strtoupper(trim("$subLast $subFirst $subMid")))
                : 0;

            if ($bestMatch === null || count($mismatches) < count($bestMismatches) || $nameScore > $bestNameScore) {
                $bestMatch      = $prev;
                $bestMismatches = $mismatches;
                $bestNameScore  = $nameScore;
            }
        }

        // 3. Evaluate result
        if ($bestMatch !== null && count($bestMismatches) === 0) {
            // Perfect match — auto approve
            $registration->update([
                'evaluation_status' => 'Auto Approved',
                'evaluation_notes'  => ['message' => 'Matched previous kabataan record.'],
                'status'            => 'active',
                'reviewed_at'       => now(),
            ]);
            return true;
        }

        if ($bestMatch !== null && count($bestMismatches) <= 2) {
            // Name matched but ≤2 other fields differ — Wrong Credentials
            $registration->update([
                'evaluation_status' => 'Wrong Credentials',
                'evaluation_notes'  => [
                    'message'    => 'Similar name found in previous kabataan but some fields do not match.',
                    'mismatches' => $bestMismatches,
                ],
            ]);
            return false;
        }

        // No name match at all — Not Profiled
        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes'  => ['message' => 'No matching name found in previous kabataan records.'],
        ]);
        return false;
    }

    private function isSimilar(string $a, string $b): bool
    {
        if ($a === $b) return true;
        similar_text($a, $b, $percent);
        return $percent >= 80;
    }

    private function normalizeDate(string $date): string
    {
        if (!$date) return '';
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : $date;
    }
}
