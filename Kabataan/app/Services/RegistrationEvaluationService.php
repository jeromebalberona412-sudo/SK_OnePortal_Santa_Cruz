<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Models\PreviousKabataan;
use App\Models\User;
use App\Notifications\KabataanRegistrationApprovedEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

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
     * Evaluate a registration. Always marks the record for manual SK Officials review.
     * (Previous automatic approval via matching "previous kabataan" records is disabled —
     *  every new profile goes through Not Profiled / pending manual verification.)
     *
     * Callers are responsible for notifications, respondent number assignment,
     * and survey response sync based on the returned `false` (not-auto-approved) result.
     *
     * @param  array<string, mixed>|null  $idVerification
     */
    public function evaluate(KabataanRegistration $registration, ?array $idVerification = null): bool
    {
        unset($idVerification);

        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        $formData = $registration->form_data ?? [];
        $documents = is_array($formData['supporting_documents'] ?? null)
            ? $formData['supporting_documents']
            : [];

        $message = $documents !== []
            ? 'Supporting documents uploaded. No matching previous KK profiling record was found. Awaiting SK Officials review.'
            : 'No matching previous KK profiling record was found. Please wait for SK Officials to verify your account.';

        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes' => ['message' => $message],
            'status' => 'password_set',
        ]);

        return false;
    }

    private function markPendingSkReview(KabataanRegistration $registration, bool $hasSupportingDocuments): bool
    {
        if ($this->rejectDuplicateIfNeeded($registration)) {
            return false;
        }

        $message = $hasSupportingDocuments
            ? 'Supporting documents uploaded. No matching previous KK profiling record was found. Awaiting SK Officials review.'
            : 'No matching previous KK profiling record was found. Please wait for SK Officials to verify your account.';

        $registration->update([
            'evaluation_status' => 'Not Profiled',
            'evaluation_notes' => ['message' => $message],
            'status' => 'password_set',
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

        if ($registration->user_id) {
            try {
                $registration->loadMissing('barangay');
                $youthUser = User::query()->find($registration->user_id);
                if ($youthUser) {
                    app(KabataanNotificationService::class)->notifyRegistrationApproved(
                        $youthUser,
                        $registration->barangay?->name ?? 'your barangay',
                    );
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($registration->email) {
            try {
                $registration->loadMissing('barangay');
                $loginUrl = URL::to(route('sign-in', [], false));

                Notification::route('mail', $registration->email)
                    ->notify(new KabataanRegistrationApprovedEmail(
                        $registration->full_name,
                        $registration->barangay?->name ?? 'your barangay',
                        $loginUrl,
                    ));

                Log::info('KK profiling auto-approval email sent', [
                    'registration_id' => $registration->id,
                    'email' => $registration->email,
                ]);
            } catch (\Throwable $e) {
                report($e);
                Log::warning('KK profiling auto-approval email failed', [
                    'registration_id' => $registration->id,
                    'email' => $registration->email,
                    'error' => $e->getMessage(),
                ]);
            }
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
}
