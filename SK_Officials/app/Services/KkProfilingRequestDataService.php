<?php

namespace App\Services;

use App\Models\KkSurveyResponse;
use Illuminate\Support\Collection;

class KkProfilingRequestDataService
{
    /**
     * @return Collection<int, KkSurveyResponse>
     */
    public function pendingSurveysForBarangay(int $barangayId): Collection
    {
        return KkSurveyResponse::query()
            ->with(['registration.barangay'])
            ->forBarangay($barangayId)
            ->where('status', 'pending')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @return Collection<int, KkSurveyResponse>
     */
    public function approvedSurveysForBarangay(int $barangayId): Collection
    {
        return KkSurveyResponse::query()
            ->forBarangay($barangayId)
            ->where('status', 'approved')
            ->get();
    }

    /**
     * @param  Collection<int, KkSurveyResponse>  $surveys
     * @return array<int, KkSurveyResponse>
     */
    public function surveysKeyedByRegistrationId(Collection $surveys): array
    {
        $keyed = [];

        foreach ($surveys as $survey) {
            if ($survey->kabataan_registration_id) {
                $keyed[(int) $survey->kabataan_registration_id] = $survey;
            }
        }

        return $keyed;
    }

    /**
     * Prefer structured kk_survey_responses values when present.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function mergeSurveyIntoRegistrationPayload(array $payload, ?KkSurveyResponse $survey): array
    {
        if ($survey === null) {
            return $payload;
        }

        $bool = static fn (?bool $value): string => $value ? 'Yes' : 'No';

        return array_merge($payload, array_filter([
            'respondent_number' => $survey->respondent_number ?? $payload['respondent_number'] ?? null,
            'respondent_display' => RespondentNumberService::displaySequence(
                isset($payload['respondent_sequence']) ? (int) $payload['respondent_sequence'] : null,
                $survey->respondent_number ?? ($payload['respondent_number'] ?? null),
            ),
            'last_name' => $survey->last_name ?: ($payload['last_name'] ?? null),
            'first_name' => $survey->first_name ?: ($payload['first_name'] ?? null),
            'middle_name' => $survey->middle_name ?: ($payload['middle_name'] ?? null),
            'suffix' => $survey->suffix ?: ($payload['suffix'] ?? null),
            'age' => $survey->age ?? ($payload['age'] ?? null),
            'birthday' => $survey->birthdate?->format('m/d/Y') ?? ($payload['birthday'] ?? null),
            'sex' => $survey->sex_assigned_at_birth ?: ($payload['sex'] ?? null),
            'email' => $survey->email ?: ($payload['email'] ?? null),
            'contact_number' => $survey->contact_number ?: ($payload['contact_number'] ?? null),
            'purok_zone' => $survey->purok_zone ?: ($payload['purok_zone'] ?? null),
            'sk_voter' => $survey->registered_sk_voter !== null
                ? $bool($survey->registered_sk_voter)
                : ($payload['sk_voter'] ?? null),
            'national_voter' => $survey->registered_national_voter !== null
                ? $bool($survey->registered_national_voter)
                : ($payload['national_voter'] ?? null),
            'civil_status' => $survey->civil_status ?: ($payload['civil_status'] ?? null),
            'youth_classification' => $survey->youth_classification ?: ($payload['youth_classification'] ?? null),
            'youth_age_group' => $survey->youth_age_group ?: ($payload['youth_age_group'] ?? null),
            'work_status' => $survey->work_status ?: ($payload['work_status'] ?? null),
            'education' => $survey->educational_background ?: ($payload['education'] ?? null),
            'sk_voted' => $survey->voted_last_sk !== null
                ? $bool($survey->voted_last_sk)
                : ($payload['sk_voted'] ?? null),
            'kk_assembly' => $survey->attended_kk_assembly !== null
                ? $bool($survey->attended_kk_assembly)
                : ($payload['kk_assembly'] ?? null),
            'kk_times' => $survey->kk_assembly_attendance_count ?: ($payload['kk_times'] ?? null),
            'kk_reason' => $survey->kk_assembly_non_attendance_reason ?: ($payload['kk_reason'] ?? null),
            'facebook' => $survey->facebook_profile_url ?: ($payload['facebook'] ?? null),
            'group_chat' => $survey->willing_to_join_group_chat !== null
                ? $bool($survey->willing_to_join_group_chat)
                : ($payload['group_chat'] ?? null),
            'signature' => $survey->participant_signature ?: ($payload['signature'] ?? null),
            'submitted_at' => $survey->survey_date?->format('m/d/Y') ?? ($payload['submitted_at'] ?? null),
            'survey_response_id' => $survey->id,
            'survey_status' => $survey->status,
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function registrationPayloadFromSurvey(KkSurveyResponse $survey): ?array
    {
        $registration = $survey->registration;

        if ($registration === null) {
            return null;
        }

        $formData = $registration->form_data ?? [];
        $val = static fn (string $key) => is_array($formData[$key] ?? null)
            ? ($formData[$key][0] ?? '—')
            : ($formData[$key] ?? '—');

        $payload = [
            'id' => $registration->id,
            'respondent_number' => $registration->respondent_number,
            'respondent_sequence' => $registration->respondent_sequence,
            'respondent_display' => RespondentNumberService::displaySequence(
                $registration->respondent_sequence,
                $registration->respondent_number
            ),
            'last_name' => $registration->last_name,
            'first_name' => $registration->first_name,
            'middle_name' => $registration->middle_name,
            'suffix' => $registration->suffix,
            'full_name' => $registration->full_name,
            'age' => $val('age'),
            'birthday' => $val('birthday'),
            'sex' => $val('sex'),
            'email' => $registration->email,
            'contact_number' => $registration->contact_number,
            'barangay' => $registration->barangay?->name ?? $survey->barangay ?? '—',
            'region' => $registration->barangay?->region ?? $survey->region ?? 'Region IV-A (CALABARZON)',
            'province' => $registration->barangay?->province ?? $survey->province ?? 'Laguna',
            'city' => $registration->barangay?->municipality ?? $survey->municipality ?? 'Santa Cruz',
            'purok_zone' => $val('purok_zone'),
            'sk_voter' => $val('sk_voter'),
            'national_voter' => $val('national_voter'),
            'civil_status' => $val('civil_status'),
            'youth_classification' => $val('youth_classification'),
            'youth_age_group' => $val('youth_age_group'),
            'work_status' => $val('work_status'),
            'education' => $val('education'),
            'sk_voted' => $val('sk_voted'),
            'kk_assembly' => $val('kk_assembly'),
            'kk_times' => $val('kk_times'),
            'kk_reason' => $val('kk_reason'),
            'facebook' => $val('facebook_profile_url') ?: $val('facebook'),
            'group_chat' => $val('group_chat'),
            'signature' => $formData['signature'] ?? '—',
            'status' => $registration->status,
            'evaluation_status' => $registration->evaluation_status,
            'evaluation_notes' => $registration->evaluation_notes,
            'submitted_at' => $registration->submitted_at?->format('m/d/Y'),
            'review_notes' => $registration->review_notes,
            'barangay_logo_url' => app(BarangayLogoUrlService::class)->resolve($registration->barangay_id),
        ];

        return $this->mergeSurveyIntoRegistrationPayload($payload, $survey);
    }
}
