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
     * Portal signups that still need email/account verification.
     *
     * @return list<string>
     */
    public static function unverifiedAccountStatuses(): array
    {
        return ['pending_verification', 'email_verified'];
    }

    public static function hasVerifiedAccount(KabataanRegistration $registration): bool
    {
        $attrs = $registration->getAttributes();

        if (in_array((string) ($attrs['status'] ?? ''), self::unverifiedAccountStatuses(), true)) {
            return false;
        }

        if (! empty($attrs['user_id'])) {
            return ! empty($attrs['email_verified_at']) && ! empty($attrs['password_set_at']);
        }

        return empty($attrs['email_verified_at']) && empty($attrs['password_set_at']);
    }

    /**
     * Approved Kabataan records — with or without uploaded supporting documents.
     *
     * @param  Builder<KabataanRegistration>  $query
     */
    public static function applyKabataanListScope(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', array_merge(
                self::rejectedRegistrationStatuses(),
                self::unverifiedAccountStatuses(),
            ))
            ->whereIn('evaluation_status', self::evaluationStatuses())
            ->where(function (Builder $account) {
                $account
                    ->where(function (Builder $portal) {
                        $portal->whereNotNull('user_id')
                            ->whereNotNull('email_verified_at')
                            ->whereNotNull('password_set_at');
                    })
                    ->orWhere(function (Builder $walkIn) {
                        $walkIn->whereNull('user_id')
                            ->whereNull('email_verified_at')
                            ->whereNull('password_set_at');
                    });
            });
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
            ->where(function (Builder $notRejectedEval) {
                $notRejectedEval
                    ->whereNull('evaluation_status')
                    ->orWhere('evaluation_status', '')
                    ->orWhereNotIn('evaluation_status', self::rejectedEvaluationStatuses());
            })
            ->where(function (Builder $pending) {
                $pending
                    ->whereIn('status', self::unverifiedAccountStatuses())
                    ->orWhereNull('evaluation_status')
                    ->orWhere('evaluation_status', '')
                    ->orWhereIn('evaluation_status', self::pendingEvaluationStatuses());
            });
    }

    public static function isListedInKabataan(KabataanRegistration $registration): bool
    {
        if (in_array($registration->status, self::rejectedRegistrationStatuses(), true)) {
            return false;
        }

        if (! self::hasVerifiedAccount($registration)) {
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
