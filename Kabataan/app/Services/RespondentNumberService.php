<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use Illuminate\Support\Facades\DB;

class RespondentNumberService
{
    public function assignToRegistration(KabataanRegistration $registration): string
    {
        if ($registration->respondent_number) {
            return $registration->respondent_number;
        }

        $tenantId = $registration->tenant_id;
        $barangayId = $registration->barangay_id;

        if (!$tenantId || !$barangayId) {
            throw new \RuntimeException('Cannot assign respondent number without tenant and barangay.');
        }

        $row = DB::selectOne(
            'SELECT generate_respondent_number(?, ?) AS respondent_number',
            [$tenantId, $barangayId]
        );

        $respondentNumber = $row->respondent_number ?? null;
        if (!$respondentNumber) {
            throw new \RuntimeException('Failed to generate respondent number.');
        }

        $sequence = (int) substr($respondentNumber, strrpos($respondentNumber, '-') + 1);

        $formData = $registration->form_data ?? [];
        $formData['respondent_number'] = $respondentNumber;

        $registration->update([
            'respondent_number'   => $respondentNumber,
            'respondent_sequence' => $sequence,
            'form_data'           => $formData,
        ]);

        return $respondentNumber;
    }
}
