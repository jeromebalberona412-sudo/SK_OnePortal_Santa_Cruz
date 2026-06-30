<?php

namespace App\Services;

use App\Models\KabataanProfilingHistory;
use App\Models\KabataanRegistration;
use App\Support\KabataanApprovedStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class KabataanProfilingHistoryService
{
    public function currentProfilingYear(): int
    {
        return (int) now(config('app.timezone', 'Asia/Manila'))->format('Y');
    }

    /**
     * @return list<int>
     */
    public function availableYears(int $barangayId): array
    {
        $years = collect();

        if (Schema::hasTable('kabataan_profiling_history')) {
            $years = $years->merge(
                KabataanProfilingHistory::query()
                    ->whereHas('registration', fn ($q) => $q->where('barangay_id', $barangayId))
                    ->distinct()
                    ->orderByDesc('profiling_year')
                    ->pluck('profiling_year')
            );
        }

        $registrationYears = KabataanRegistration::query()
            ->forBarangay($barangayId)
            ->whereNotNull('submitted_at')
            ->selectRaw('EXTRACT(YEAR FROM submitted_at)::integer as registration_year')
            ->distinct()
            ->pluck('registration_year');

        $years = $years->merge($registrationYears)->map(fn ($y) => (int) $y)->unique()->sortDesc()->values();

        if ($years->isEmpty()) {
            return [(int) now()->format('Y')];
        }

        return $years->all();
    }

    public function isHistoricalYear(int $year): bool
    {
        return $year < $this->currentProfilingYear();
    }

    /**
     * @return Collection<int, KabataanRegistration|KabataanProfilingHistory>
     */
    public function recordsForYear(int $barangayId, int $year): Collection
    {
        if (Schema::hasTable('kabataan_profiling_history')) {
            $history = KabataanProfilingHistory::query()
                ->with('registration.barangay')
                ->where('profiling_year', $year)
                ->whereHas('registration', function ($q) use ($barangayId) {
                    $q->where('barangay_id', $barangayId)->whereNull('deleted_at');
                    KabataanApprovedStatuses::applyKabataanListScope($q);
                })
                ->get();

            if ($history->isNotEmpty()) {
                return $history;
            }
        }

        if ($this->isHistoricalYear($year)) {
            return KabataanRegistration::query()
                ->with('barangay')
                ->forBarangay($barangayId)
                ->whereNull('deleted_at')
                ->whereYear('submitted_at', $year)
                ->tap(fn ($q) => KabataanApprovedStatuses::applyKabataanListScope($q))
                ->get();
        }

        return KabataanRegistration::query()
            ->with('barangay')
            ->forBarangay($barangayId)
            ->whereNull('deleted_at')
            ->tap(fn ($q) => KabataanApprovedStatuses::applyKabataanListScope($q))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
