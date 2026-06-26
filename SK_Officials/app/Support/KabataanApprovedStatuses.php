<?php

namespace App\Support;

use App\Models\KabataanRegistration;
use Illuminate\Database\Eloquent\Builder;

final class KabataanApprovedStatuses
{
    /**
     * Evaluation values that mean the youth is approved for the Kabataan list.
     *
     * @return list<string>
     */
    public static function evaluationStatuses(): array
    {
        return ['active', 'Auto Approved', 'ID Verified'];
    }

    /**
     * Still in KK Profiling review — show only in KK Profiling Requests.
     *
     * @return list<string>
     */
    public static function pendingEvaluationStatuses(): array
    {
        return ['Not Profiled', 'Wrong Credentials'];
    }

    /**
     * @return list<string>
     */
    public static function rejectedEvaluationStatuses(): array
    {
        return ['Duplicate'];
    }

    /**
     * @return list<string>
     */
    public static function rejectedRegistrationStatuses(): array
    {
        return ['rejected', 'archived'];
    }

    /**
     * Approved Kabataan records — with or without uploaded supporting documents.
     *
     * @param  Builder<KabataanRegistration>  $query
     */
    public static function applyKabataanListScope(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', self::rejectedRegistrationStatuses())
            ->whereIn('evaluation_status', self::evaluationStatuses());
    }

    /**
     * Pending KK Profiling requests — not yet in the Kabataan list.
     *
     * @param  Builder<KabataanRegistration>  $query
     */
    public static function applyPendingProfilingScope(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', self::rejectedRegistrationStatuses())
            ->where(function (Builder $pending) {
                $pending
                    ->whereIn('evaluation_status', self::pendingEvaluationStatuses())
                    ->orWhere(function (Builder $workflow) {
                        $workflow
                            ->whereIn('status', ['pending_verification', 'email_verified', 'password_set', 'active'])
                            ->where(function (Builder $eval) {
                                $eval->whereNull('evaluation_status')
                                    ->orWhere('evaluation_status', '')
                                    ->orWhereIn('evaluation_status', self::pendingEvaluationStatuses());
                            });
                    });
            })
            ->whereNotIn('evaluation_status', self::rejectedEvaluationStatuses())
            ->whereNotIn('evaluation_status', self::evaluationStatuses());
    }

    public static function isListedInKabataan(KabataanRegistration $registration): bool
    {
        if (in_array($registration->status, self::rejectedRegistrationStatuses(), true)) {
            return false;
        }

        $evaluation = $registration->evaluation_status;

        if (in_array($evaluation, self::pendingEvaluationStatuses(), true)
            || in_array($evaluation, self::rejectedEvaluationStatuses(), true)) {
            return false;
        }

        return in_array($evaluation, self::evaluationStatuses(), true);
    }

    public static function isPendingInKkProfiling(KabataanRegistration $registration): bool
    {
        if (in_array($registration->status, self::rejectedRegistrationStatuses(), true)) {
            return false;
        }

        if (in_array($registration->evaluation_status, self::rejectedEvaluationStatuses(), true)) {
            return false;
        }

        return ! self::isListedInKabataan($registration);
    }

    public static function countListedForBarangay(int $barangayId): int
    {
        $query = KabataanRegistration::forBarangay($barangayId);
        self::applyKabataanListScope($query);

        return $query->count();
    }
}
