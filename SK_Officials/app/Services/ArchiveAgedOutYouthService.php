<?php

namespace App\Services;

use App\Models\KabataanRegistration;
use App\Support\KabataanApprovedStatuses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArchiveAgedOutYouthService
{
    public const ARCHIVE_REASON_AGED_OUT = 'aged_out';

    public const MAX_YOUTH_AGE = 30;

    public function archiveEligibleRegistrations(?int $barangayId = null, ?Carbon $asOf = null): int
    {
        $asOf = ($asOf ?? Carbon::today())->startOfDay();
        $archived = 0;

        $query = KabataanRegistration::query()
            ->where('status', 'active');
        KabataanApprovedStatuses::applyKabataanListScope($query);

        if ($barangayId !== null) {
            $query->forBarangay($barangayId);
        }

        $query->orderBy('id')->chunkById(100, function ($registrations) use ($asOf, &$archived) {
            foreach ($registrations as $registration) {
                if (! $this->shouldArchiveForAgeOut($registration, $asOf)) {
                    continue;
                }

                DB::transaction(function () use ($registration) {
                    $registration->update([
                        'status' => 'archived',
                        'archived_at' => now(),
                        'archive_reason' => self::ARCHIVE_REASON_AGED_OUT,
                    ]);
                });

                $archived++;
            }
        });

        return $archived;
    }

    public function shouldArchiveForAgeOut(KabataanRegistration $registration, ?Carbon $asOf = null): bool
    {
        $asOf = ($asOf ?? Carbon::today())->startOfDay();
        $age = $this->resolveAgeAsOf($registration, $asOf);

        return $age !== null && $age > self::MAX_YOUTH_AGE;
    }

    public function resolveAgeAsOf(KabataanRegistration $registration, Carbon $asOf): ?int
    {
        $formData = $registration->form_data ?? [];
        $birthday = $formData['birthday'] ?? null;

        if (is_array($birthday)) {
            $birthday = $birthday[0] ?? null;
        }

        if (is_string($birthday) && trim($birthday) !== '') {
            try {
                return Carbon::parse($birthday)->startOfDay()->diffInYears($asOf);
            } catch (\Throwable) {
                // Fall through to stored age.
            }
        }

        $age = $formData['age'] ?? null;

        if (is_array($age)) {
            $age = $age[0] ?? null;
        }

        return is_numeric($age) ? (int) $age : null;
    }
}
