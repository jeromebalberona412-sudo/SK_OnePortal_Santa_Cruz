<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationEvaluationService
{
    /**
     * @return list<string>
     */
    public static function autoApprovedStatuses(): array
    {
        return ['Auto Approved', 'ID Verified'];
    }

    public static function isAutoApprovedStatus(?string $status): bool
    {
        return in_array($status, self::autoApprovedStatuses(), true);
    }

    /**
     * Evaluate a registration against ID OCR and approved KK survey history.
     * Returns true if auto-approved.
     *
     * @param  array<string, mixed>|null  $idVerification
     */
    public function evaluate(KabataanRegistration $registration, ?array $idVerification = null): bool
    {
        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        $formData = $registration->form_data ?? [];
        $documents = is_array($formData['supporting_documents'] ?? null)
            ? $formData['supporting_documents']
            : [];
        $hasSupportingDocuments = $documents !== [];

        $verificationService = app(IdVerificationService::class);
        $resolvedVerification = $this->resolveVerificationPayload($registration, $idVerification);

        if ($hasSupportingDocuments || $resolvedVerification !== null) {
            if ($verificationService->wasAlreadyProcessed($resolvedVerification)
                && $this->evaluateFromIdVerification($registration, $resolvedVerification)) {
                return true;
            }

            if ($this->evaluateFromIdVerification($registration, $resolvedVerification)) {
                return true;
            }

            if ($hasSupportingDocuments) {
                $registrationFields = array_merge($formData, [
                    'first_name' => $registration->first_name,
                    'middle_name' => $registration->middle_name,
                    'last_name' => $registration->last_name,
                    'suffix' => $registration->suffix,
                    'registration_id' => $registration->id,
                ]);

                $reverified = $verificationService->verifySupportingDocuments(
                    $documents,
                    (int) $registration->barangay_id,
                    $registrationFields,
                );

                if ($this->evaluateFromIdVerification($registration, $reverified)) {
                    return true;
                }
            }
        }

        return $this->markPendingSkReview($registration, $hasSupportingDocuments);
    }

    private function markPendingSkReview(KabataanRegistration $registration, bool $hasSupportingDocuments): bool
    {
        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        $message = $hasSupportingDocuments
            ? 'Supporting documents uploaded but identity checks did not fully pass. Awaiting SK Officials review.'
            : 'No supporting documents uploaded. Please wait for SK Officials to verify your account.';

        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes' => ['message' => $message],
            'status' => 'password_set',
        ]);

        return false;
    }

    /**
     * @param  array<string, mixed>|null  $idVerification
     * @return array<string, mixed>|null
     */
    private function resolveVerificationPayload(KabataanRegistration $registration, ?array $idVerification): ?array
    {
        if (is_array($idVerification)) {
            return $idVerification;
        }

        $formData = $registration->form_data ?? [];
        $stored = $formData['id_verification'] ?? null;

        return is_array($stored) ? $stored : null;
    }

    /**
     * @param  array<string, mixed>|null  $idVerification
     */
    public function evaluateFromIdVerification(KabataanRegistration $registration, ?array $idVerification): bool
    {
        if ($idVerification === null) {
            return false;
        }

        if ($idVerification['duplicate_detected'] ?? false) {
            $this->markDuplicate($registration, KkProfilingValidationMessages::DUPLICATE_IDENTITY);

            return false;
        }

        if (! ($idVerification['name_match'] ?? false)) {
            return false;
        }

        if (($idVerification['source'] ?? '') === 'complete_id_upload') {
            return false;
        }

        if (! ($idVerification['barangay_match'] ?? false)) {
            return false;
        }

        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        return $this->applyAutoApproval(
            $registration,
            'Your School ID name and barangay match your KK Profiling form. All identity verification checks passed.',
            [
                'source' => 'id_ocr',
                'evaluation_status' => 'ID Verified',
                'detected_name' => $idVerification['detected_name'] ?? null,
                'form_name' => $idVerification['form_name'] ?? null,
                'address' => $idVerification['address'] ?? null,
                'detected_barangay' => $idVerification['barangay'] ?? null,
                'detected_city' => $idVerification['city'] ?? null,
                'detected_province' => $idVerification['province'] ?? null,
                'match_score' => $idVerification['match_score'] ?? null,
                'match_reason' => $idVerification['match_reason'] ?? null,
                'ocr' => $idVerification['ocr'] ?? null,
            ],
        );
    }

    public function evaluateFromSurveyHistory(KabataanRegistration $registration): bool
    {
        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        $subLast = strtoupper(trim($registration->last_name));
        $subFirst = strtoupper(trim($registration->first_name));
        $subMid = strtoupper(trim($registration->middle_name ?? ''));

        $duplicate = KabataanRegistration::where('barangay_id', $registration->barangay_id)
            ->where('id', '!=', $registration->id)
            ->where('status', 'active')
            ->where(DB::raw('UPPER(last_name)'), $subLast)
            ->where(DB::raw('UPPER(first_name)'), $subFirst)
            ->first();

        if ($duplicate) {
            $this->markDuplicate($registration, 'Already exists as an active Kabataan member.');

            return false;
        }

        $previous = DB::table('kk_survey_responses')
            ->where('barangay_id', $registration->barangay_id)
            ->where('status', 'approved')
            ->get();

        $bestMatch = null;
        $bestMismatches = [];
        $bestNameScore = 0;

        foreach ($previous as $prev) {
            $prevLast = strtoupper(trim($prev->last_name ?? ''));
            $prevFirst = strtoupper(trim($prev->first_name ?? ''));

            $nameSimilar = strlen($subLast) >= 3
                && strlen($subFirst) >= 2
                && $this->isSimilar($subLast, $prevLast)
                && $this->isSimilar($subFirst, $prevFirst);

            if (! $nameSimilar) {
                continue;
            }

            $mismatches = [];

            $subAge = (string) ($registration->form_data['age'] ?? '');
            $prevAge = (string) ($prev->age ?? '');
            if ($subAge && $prevAge && ! $this->isSimilar($subAge, $prevAge)) {
                $mismatches[] = ['field' => 'age', 'submitted' => $subAge, 'previous' => $prevAge];
            }

            $subBday = $this->normalizeDate($registration->form_data['birthday'] ?? '');
            $prevBday = $this->normalizeDate($prev->birthdate ?? '');
            if ($subBday && $prevBday && ! $this->isSimilar($subBday, $prevBday)) {
                $mismatches[] = ['field' => 'birthday', 'submitted' => $subBday, 'previous' => $prevBday];
            }

            $subSex = strtoupper($registration->form_data['sex'] ?? '');
            $prevSex = strtoupper($prev->sex_assigned_at_birth ?? '');
            if ($subSex && $prevSex && ! $this->isSimilar($subSex, $prevSex)) {
                $mismatches[] = ['field' => 'sex', 'submitted' => $subSex, 'previous' => $prevSex];
            }

            $nameScore = similar_text(
                strtoupper(trim("$prevLast $prevFirst")),
                strtoupper(trim("$subLast $subFirst $subMid"))
            );

            if ($bestMatch === null || count($mismatches) < count($bestMismatches) || $nameScore > $bestNameScore) {
                $bestMatch = $prev;
                $bestMismatches = $mismatches;
                $bestNameScore = $nameScore;
            }
        }

        if ($bestMatch !== null && count($bestMismatches) === 0) {
            return $this->applyAutoApproval(
                $registration,
                'Matched an approved KK profiling record.',
                ['source' => 'kk_survey_history'],
            );
        }

        if ($bestMatch !== null && count($bestMismatches) <= 2) {
            $registration->update([
                'evaluation_status' => 'Wrong Credentials',
                'evaluation_notes' => [
                    'message' => 'Similar name found in KK profiling history but some fields do not match.',
                    'mismatches' => $bestMismatches,
                ],
            ]);

            return false;
        }

        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes' => ['message' => 'No matching name found in KK profiling history.'],
        ]);

        return false;
    }

    /**
     * @param  array<string, mixed>  $notes
     */
    private function applyAutoApproval(KabataanRegistration $registration, string $message, array $notes = []): bool
    {
        $evaluationStatus = $notes['evaluation_status'] ?? 'Auto Approved';
        unset($notes['evaluation_status']);

        $registration->update([
            'evaluation_status' => $evaluationStatus,
            'evaluation_notes' => array_merge(['message' => $message], $notes),
            'status' => 'active',
            'reviewed_at' => now(),
        ]);

        if ($registration->user_id) {
            User::query()
                ->where('id', $registration->user_id)
                ->update(['status' => User::STATUS_ACTIVE]);
        }

        try {
            (new RespondentNumberService)->assignToRegistration($registration->fresh());
            (new KkSurveyResponseService)->syncFromRegistration($registration->fresh(), 'approved');
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            (new SkOfficialsNotificationDispatcher)->notifyKkProfilingAutoApproved(
                (int) $registration->barangay_id,
                $registration->full_name,
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return true;
    }

    private function rejectDuplicateIfNeeded(KabataanRegistration $registration): bool
    {
        $duplicateChecker = app(DuplicateKabataanRegistrationService::class);
        $fields = array_merge($registration->form_data ?? [], [
            'first_name' => $registration->first_name,
            'middle_name' => $registration->middle_name,
            'last_name' => $registration->last_name,
            'suffix' => $registration->suffix,
        ]);

        if (! $duplicateChecker->hasApprovedDuplicate((int) $registration->barangay_id, $fields, $registration->id)) {
            return false;
        }

        $this->markDuplicate($registration, KkProfilingValidationMessages::DUPLICATE_IDENTITY);

        return true;
    }

    private function markDuplicate(KabataanRegistration $registration, string $message): void
    {
        $registration->update([
            'evaluation_status' => 'Duplicate',
            'evaluation_notes' => ['message' => $message],
        ]);
    }

    private function isSimilar(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        similar_text($a, $b, $percent);

        return $percent >= 80;
    }

    private function normalizeDate(string $date): string
    {
        if (! $date) {
            return '';
        }

        $ts = strtotime($date);

        return $ts ? date('Y-m-d', $ts) : $date;
    }
}
