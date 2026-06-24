<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Services\KkSurveyResponseService;
use Illuminate\Support\Facades\DB;

class RegistrationEvaluationService
{
    /**
     * Evaluate a registration against approved KK survey history and active users.
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

        // 2. Search approved KK survey responses in the same barangay
        $previous = DB::table('kk_survey_responses')
            ->where('barangay_id', $registration->barangay_id)
            ->where('status', 'approved')
            ->get();

        $bestMatch      = null;
        $bestMismatches = [];
        $bestNameScore  = 0;

        foreach ($previous as $prev) {
            $prevLast  = strtoupper(trim($prev->last_name ?? ''));
            $prevFirst = strtoupper(trim($prev->first_name ?? ''));

            $nameSimilar = strlen($subLast) >= 3
                && strlen($subFirst) >= 2
                && $this->isSimilar($subLast, $prevLast)
                && $this->isSimilar($subFirst, $prevFirst);

            if (!$nameSimilar) {
                continue;
            }

            $mismatches = [];

            $subAge  = (string) ($registration->form_data['age'] ?? '');
            $prevAge = (string) ($prev->age ?? '');
            if ($subAge && $prevAge && !$this->isSimilar($subAge, $prevAge)) {
                $mismatches[] = ['field' => 'age', 'submitted' => $subAge, 'previous' => $prevAge];
            }

            $subBday  = $this->normalizeDate($registration->form_data['birthday'] ?? '');
            $prevBday = $this->normalizeDate($prev->birthdate ?? '');
            if ($subBday && $prevBday && !$this->isSimilar($subBday, $prevBday)) {
                $mismatches[] = ['field' => 'birthday', 'submitted' => $subBday, 'previous' => $prevBday];
            }

            $subSex  = strtoupper($registration->form_data['sex'] ?? '');
            $prevSex = strtoupper($prev->sex_assigned_at_birth ?? '');
            if ($subSex && $prevSex && !$this->isSimilar($subSex, $prevSex)) {
                $mismatches[] = ['field' => 'sex', 'submitted' => $subSex, 'previous' => $prevSex];
            }

            $nameScore = similar_text(
                strtoupper(trim("$prevLast $prevFirst")),
                strtoupper(trim("$subLast $subFirst $subMid"))
            );

            if ($bestMatch === null || count($mismatches) < count($bestMismatches) || $nameScore > $bestNameScore) {
                $bestMatch      = $prev;
                $bestMismatches = $mismatches;
                $bestNameScore  = $nameScore;
            }
        }

        // 3. Evaluate result
        if ($bestMatch !== null && count($bestMismatches) === 0) {
            $registration->update([
                'evaluation_status' => 'Auto Approved',
                'evaluation_notes'  => ['message' => 'Matched an approved KK profiling record.'],
                'status'            => 'active',
                'reviewed_at'       => now(),
            ]);

            try {
                (new RespondentNumberService())->assignToRegistration($registration->fresh());
                (new KkSurveyResponseService())->syncFromRegistration($registration->fresh(), 'approved');
            } catch (\Throwable $e) {
                report($e);
            }

            return true;
        }

        if ($bestMatch !== null && count($bestMismatches) <= 2) {
            $registration->update([
                'evaluation_status' => 'Wrong Credentials',
                'evaluation_notes'  => [
                    'message'    => 'Similar name found in KK profiling history but some fields do not match.',
                    'mismatches' => $bestMismatches,
                ],
            ]);
            return false;
        }

        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes'  => ['message' => 'No matching name found in KK profiling history.'],
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
