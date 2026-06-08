<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\KkSurveyResponse;
use App\Models\User;
use Carbon\Carbon;

class KkSurveyResponseService
{
    public function syncFromRegistration(KabataanRegistration $registration, string $status = 'pending'): KkSurveyResponse
    {
        $payload = $this->mapFromRegistration($registration, $status);

        $response = KkSurveyResponse::query()->firstOrNew([
            'kabataan_registration_id' => $registration->id,
        ]);
        $response->fill($payload);
        $response->save();

        return $response;
    }

    public function syncStatus(KabataanRegistration $registration, string $status): void
    {
        $existing = KkSurveyResponse::query()
            ->where('kabataan_registration_id', $registration->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $status,
                'respondent_number' => $registration->respondent_number ?? $existing->respondent_number,
            ]);

            return;
        }

        $this->syncFromRegistration($registration->fresh(), $status);
    }

    public function restoreUserAccount(KabataanRegistration $registration, ?string $previousUserStatus = null): void
    {
        if (! $registration->user_id) {
            return;
        }

        $user = User::find($registration->user_id);
        if (! $user) {
            return;
        }

        $user->update([
            'status' => $previousUserStatus ?: User::STATUS_PENDING_APPROVAL,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFromRegistration(KabataanRegistration $registration, string $status): array
    {
        $registration->loadMissing('barangay');
        $formData = $registration->form_data ?? [];

        $value = function (string $key) use ($formData) {
            $raw = $formData[$key] ?? null;

            if (is_array($raw)) {
                return $raw[0] ?? null;
            }

            return $raw;
        };

        $yesNo = fn (?string $input) => in_array(strtolower(trim((string) $input)), ['yes', 'true', '1'], true);

        $birthday = $value('birthday');
        $birthdate = null;
        if ($birthday) {
            try {
                $birthdate = Carbon::parse($birthday)->toDateString();
            } catch (\Throwable) {
                $birthdate = null;
            }
        }

        $barangay = $registration->barangay;

        return [
            'tenant_id' => $registration->tenant_id,
            'barangay_id' => $registration->barangay_id,
            'kabataan_registration_id' => $registration->id,
            'respondent_number' => $registration->respondent_number,
            'survey_date' => ($registration->submitted_at ?? now())->toDateString(),
            'last_name' => $registration->last_name,
            'first_name' => $registration->first_name,
            'middle_name' => $registration->middle_name,
            'suffix' => $registration->suffix,
            'region' => $barangay?->region ?? 'Region IV-A (CALABARZON)',
            'province' => $barangay?->province ?? 'Laguna',
            'municipality' => $barangay?->municipality ?? 'Santa Cruz',
            'barangay' => $barangay?->name,
            'purok_zone' => $value('purok_zone'),
            'sex_assigned_at_birth' => $value('sex'),
            'age' => is_numeric($value('age')) ? (int) $value('age') : null,
            'birthdate' => $birthdate,
            'email' => $registration->email,
            'contact_number' => $registration->contact_number,
            'civil_status' => $value('civil_status'),
            'youth_age_group' => $value('youth_age_group'),
            'educational_background' => $value('education'),
            'youth_classification' => $value('youth_classification'),
            'work_status' => $value('work_status'),
            'registered_sk_voter' => $yesNo($value('sk_voter')),
            'registered_national_voter' => $yesNo($value('national_voter')),
            'attended_kk_assembly' => $yesNo($value('kk_assembly')),
            'voted_last_sk' => $yesNo($value('sk_voted')),
            'kk_assembly_attendance_count' => $value('kk_times'),
            'kk_assembly_non_attendance_reason' => $value('kk_reason'),
            'facebook_account' => $value('facebook'),
            'willing_to_join_group_chat' => $yesNo($value('group_chat')),
            'participant_signature' => $value('signature'),
            'consent_given' => true,
            'status' => $status,
        ];
    }
}
