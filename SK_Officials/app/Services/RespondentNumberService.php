<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use Illuminate\Support\Facades\DB;

class RespondentNumberService
{
    /**
     * Assign the next respondent number for a barangay (per year sequence).
     * Format: {BARANGAY_PREFIX}-{YEAR}-{0001}
     */
    public function assignToRegistration(KabataanRegistration $registration): string
    {
        if ($registration->respondent_number) {
            return $registration->respondent_number;
        }

        $tenantId = $registration->tenant_id;
        $barangayId = $registration->barangay_id;

        if (! $tenantId || ! $barangayId) {
            throw new \RuntimeException('Cannot assign respondent number without tenant and barangay.');
        }

        $row = DB::selectOne(
            'SELECT generate_respondent_number(?, ?) AS respondent_number',
            [$tenantId, $barangayId]
        );

        $respondentNumber = $row->respondent_number ?? null;
        if (! $respondentNumber) {
            throw new \RuntimeException('Failed to generate respondent number.');
        }

        $sequence = (int) substr($respondentNumber, strrpos($respondentNumber, '-') + 1);

        $formData = $registration->form_data ?? [];
        $formData['respondent_number'] = $respondentNumber;

        $registration->update([
            'respondent_number' => $respondentNumber,
            'respondent_sequence' => $sequence,
            'form_data' => $formData,
        ]);

        return $respondentNumber;
    }

    /** Display-only: trailing sequence without barangay/year prefix (0001, 0002, ...). */
    public static function displaySequence(?int $sequence, ?string $fullNumber = null): string
    {
        if ($fullNumber) {
            $last = strrpos($fullNumber, '-') !== false
                ? substr($fullNumber, strrpos($fullNumber, '-') + 1)
                : $fullNumber;

            if ($last !== '') {
                return $last;
            }
        }

        if ($sequence) {
            return self::formatQueueNumber($sequence);
        }

        return 'Auto-generated';
    }

    public function ensureAssigned(KabataanRegistration $registration): string
    {
        return $this->assignToRegistration($registration->fresh());
    }

    /** Queue position for pending requests (first submitted = 01). */
    public static function formatQueueNumber(?int $position): string
    {
        if (! $position || $position < 1) {
            return '—';
        }

        return str_pad((string) $position, 4, '0', STR_PAD_LEFT);
    }
}
