<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\KkSurveyResponse;
use Illuminate\Support\Collection;

class KkProfilingRequestDataService
{
    /**
     * Only explicit Yes/No answers count. Unanswered must stay empty — never default to Yes.
     */
    public function normalizeYesNoAnswer(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        $raw = trim((string) ($value ?? ''));

        if ($raw === '' || $raw === '—' || $raw === '-') {
            return null;
        }

        if (strcasecmp($raw, 'yes') === 0) {
            return 'Yes';
        }

        if (strcasecmp($raw, 'no') === 0) {
            return 'No';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function listPayloadKeys(): array
    {
        return [
            'id',
            'respondent_number',
            'respondent_sequence',
            'respondent_display',
            'last_name',
            'first_name',
            'middle_name',
            'suffix',
            'suffix_raw',
            'suffix_other',
            'age',
            'birthday',
            'sex',
            'email',
            'has_email',
            'has_account',
            'contact_number',
            'purok_zone',
            'sk_voter',
            'national_voter',
            'civil_status',
            'youth_classification',
            'youth_age_group',
            'work_status',
            'education',
            'sk_voted',
            'kk_assembly',
            'kk_times',
            'kk_reason',
            'facebook',
            'group_chat',
            'barangay',
            'region',
            'province',
            'city',
            'barangay_logo_url',
            'status',
            'evaluation_status',
            'evaluation_notes',
            'review_notes',
            'submitted_at',
        ];
    }

    /**
     * Survey columns needed for the table and View/Edit forms, without signature blobs.
     *
     * @return list<string>
     */
    public function listSurveyColumns(): array
    {
        return [
            'id',
            'kabataan_registration_id',
            'barangay_id',
            'respondent_number',
            'survey_date',
            'last_name',
            'first_name',
            'middle_name',
            'suffix',
            'region',
            'province',
            'municipality',
            'barangay',
            'purok_zone',
            'sex_assigned_at_birth',
            'age',
            'birthdate',
            'email',
            'contact_number',
            'civil_status',
            'youth_age_group',
            'educational_background',
            'youth_classification',
            'work_status',
            'registered_sk_voter',
            'registered_national_voter',
            'attended_kk_assembly',
            'voted_last_sk',
            'kk_assembly_attendance_count',
            'kk_assembly_non_attendance_reason',
            'facebook_profile_url',
            'status',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatListRow(KabataanRegistration $registration, ?KkSurveyResponse $survey, ?string $barangayLogoUrl = null): array
    {
        $payload = $this->buildRegistrationPayload($registration, $survey, $barangayLogoUrl, false);
        $payload['has_email'] = filled($payload['email'] ?? null);

        return array_intersect_key($payload, array_flip($this->listPayloadKeys()));
    }

    /**
     * @param  list<array<string, mixed>>  $supportingDocuments
     * @return array<string, mixed>
     */
    public function formatDetailRow(
        KabataanRegistration $registration,
        ?KkSurveyResponse $survey,
        ?string $barangayLogoUrl,
        array $supportingDocuments = [],
    ): array {
        $payload = $this->buildRegistrationPayload($registration, $survey, $barangayLogoUrl, true);
        $payload['supporting_documents'] = $supportingDocuments;

        return $payload;
    }

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
     * Pending surveys whose registration is not already in the pending list.
     *
     * @param  list<int>  $registrationIds
     * @return Collection<int, KkSurveyResponse>
     */
    public function unmatchedPendingSurveys(int $barangayId, array $registrationIds): Collection
    {
        return KkSurveyResponse::query()
            ->select($this->listSurveyColumns())
            ->with(['registration.barangay'])
            ->forBarangay($barangayId)
            ->where('status', 'pending')
            ->when($registrationIds !== [], function ($query) use ($registrationIds) {
                $query->where(function ($inner) use ($registrationIds) {
                    $inner->whereNull('kabataan_registration_id')
                        ->orWhereNotIn('kabataan_registration_id', $registrationIds);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public function resolveSuffixForDisplay(?string $suffix, array $formData): ?string
    {
        return $this->resolveDisplaySuffix($suffix, $formData);
    }

    /**
     * Prefer structured kk_survey_responses values when present.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function mergeSurveyIntoRegistrationPayload(array $payload, ?KkSurveyResponse $survey, bool $includeHeavyFields = true): array
    {
        if ($survey === null) {
            return $payload;
        }

        $bool = static fn (?bool $value): string => $value ? 'Yes' : 'No';
        $formData = is_array($payload['form_data'] ?? null) ? $payload['form_data'] : [];

        $merged = [
            'respondent_number' => $survey->respondent_number ?? $payload['respondent_number'] ?? null,
            'respondent_display' => RespondentNumberService::displaySequence(
                isset($payload['respondent_sequence']) ? (int) $payload['respondent_sequence'] : null,
                $survey->respondent_number ?? ($payload['respondent_number'] ?? null),
            ),
            'last_name' => $survey->last_name ?: ($payload['last_name'] ?? null),
            'first_name' => $survey->first_name ?: ($payload['first_name'] ?? null),
            'middle_name' => $survey->middle_name ?: ($payload['middle_name'] ?? null),
            'suffix' => $this->resolveDisplaySuffix(
                $survey->suffix ?: ($payload['suffix'] ?? null),
                $formData,
            ),
            'suffix_raw' => $survey->suffix ?: ($payload['suffix_raw'] ?? null),
            'suffix_other' => $this->extractFormValue($formData, 'custom_suffix')
                ?: $this->extractFormValue($formData, 'suffix_other'),
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
            'group_chat' => $this->resolveGroupChat($payload),
            'submitted_at' => $survey->survey_date?->format('m/d/Y') ?? ($payload['submitted_at'] ?? null),
            'survey_response_id' => $survey->id,
            'survey_status' => $survey->status,
        ];

        if ($includeHeavyFields) {
            $merged['signature'] = $survey->participant_signature ?: ($payload['signature'] ?? null);
        }

        return array_merge($payload, array_filter($merged, static fn ($value) => $value !== null && $value !== ''));
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
        $val = static function (string $key) use ($formData) {
            $raw = $formData[$key] ?? null;
            if (is_array($raw)) {
                $raw = $raw[0] ?? null;
            }
            if ($raw === null || $raw === '' || $raw === '—') {
                return null;
            }

            return $raw;
        };

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
            'middle_name' => $registration->middle_name ?: $this->extractFormValue($formData, 'middle_name'),
            'suffix' => $this->resolveDisplaySuffix($registration->suffix, $formData),
            'suffix_raw' => $registration->suffix,
            'suffix_other' => $this->extractFormValue($formData, 'custom_suffix')
                ?: $this->extractFormValue($formData, 'suffix_other'),
            'form_data' => $formData,
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
            'group_chat' => $this->normalizeYesNoAnswer($val('group_chat')),
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

    /**
     * @param  array<string, mixed>  $formData
     */
    private function extractFormValue(array $formData, string $key): ?string
    {
        $raw = $formData[$key] ?? null;

        if (is_array($raw)) {
            $raw = $raw[0] ?? null;
        }

        $value = trim((string) ($raw ?? ''));

        return $value !== '' && $value !== '—' ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveDisplaySuffix(?string $suffix, array $formData): ?string
    {
        $normalized = trim((string) ($suffix ?? ''));

        if ($normalized === '' || strcasecmp($normalized, 'none') === 0) {
            return 'None';
        }

        if (in_array(strtolower($normalized), ['other', 'others'], true)) {
            $custom = $this->extractFormValue($formData, 'custom_suffix')
                ?: $this->extractFormValue($formData, 'suffix_other');

            return $custom ?: 'None';
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveGroupChat(array $payload): ?string
    {
        return $this->normalizeYesNoAnswer($payload['group_chat'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildRegistrationPayload(
        KabataanRegistration $registration,
        ?KkSurveyResponse $survey,
        ?string $barangayLogoUrl,
        bool $includeHeavyFields = true,
    ): array {
        $formData = $registration->form_data ?? [];
        $val = function (string $key) use ($formData) {
            return $this->extractFormValue($formData, $key);
        };

        $idVerification = is_array($formData['id_verification'] ?? null)
            ? $formData['id_verification']
            : null;

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
            'middle_name' => $registration->middle_name ?: $val('middle_name'),
            'suffix' => $this->resolveDisplaySuffix($registration->suffix, $formData),
            'suffix_raw' => $registration->suffix,
            'suffix_other' => $this->extractFormValue($formData, 'custom_suffix')
                ?: $this->extractFormValue($formData, 'suffix_other'),
            'full_name' => $registration->full_name,
            'age' => $val('age'),
            'birthday' => $val('birthday'),
            'sex' => $val('sex'),
            'email' => $registration->email,
            'contact_number' => $registration->contact_number,
            'barangay' => $registration->barangay?->name ?? '—',
            'region' => $registration->barangay?->region ?? 'Region IV-A (CALABARZON)',
            'province' => $registration->barangay?->province ?? 'Laguna',
            'city' => $registration->barangay?->municipality ?? 'Santa Cruz',
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
            'group_chat' => $this->normalizeYesNoAnswer($val('group_chat')),
            'status' => $registration->status,
            'evaluation_status' => $registration->evaluation_status,
            'evaluation_notes' => $registration->evaluation_notes,
            'submitted_at' => $registration->submitted_at?->format('m/d/Y'),
            'review_notes' => $registration->review_notes,
            'has_email' => filled($registration->email),
            'has_account' => ! empty($registration->user_id) && ! empty($registration->password_set_at),
            'barangay_logo_url' => $barangayLogoUrl,
            'id_verification' => $idVerification ? [
                'name_match' => (bool) ($idVerification['name_match'] ?? false),
                'barangay_match' => (bool) ($idVerification['barangay_match'] ?? false),
                'duplicate_detected' => (bool) ($idVerification['duplicate_detected'] ?? false),
                'message' => $idVerification['message'] ?? null,
                'match_reason' => $idVerification['match_reason'] ?? null,
                'matched_barangay' => $idVerification['matched_barangay'] ?? null,
            ] : null,
        ];

        if ($includeHeavyFields) {
            $payload['signature'] = $formData['signature'] ?? null;
        }

        return $this->mergeSurveyIntoRegistrationPayload($payload, $survey, $includeHeavyFields);
    }
}
