<?php

namespace App\Services;

use App\Models\KabataanProfilingHistory;
use App\Models\KabataanRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KkProfilingScheduleService
{
    public function timezone(): string
    {
        return (string) config('app.timezone', 'Asia/Manila');
    }

    public function expectedProfilingYear(): int
    {
        return (int) now($this->timezone())->format('Y');
    }

    /**
     * Active KK profiling schedule for yearly kabataan updates.
     * Accepts any profiling_year (for Supabase/manual test data). SK Officials UI
     * still only creates current-year schedules.
     *
     * date_start is not required so manual DB inserts can be tested without exact dates.
     */
    public function activeUpdateSchedule(int $barangayId): ?object
    {
        if (! Schema::hasTable('kk_profiling_schedules')) {
            return null;
        }

        $today = now($this->timezone())->toDateString();

        return DB::table('kk_profiling_schedules')
            ->where('barangay_id', $barangayId)
            ->where('status', 'Ongoing')
            ->where('date_expiry', '>=', $today)
            ->orderByDesc('profiling_year')
            ->orderByDesc('date_start')
            ->first();
    }

    public function hasActiveProfilingSchedule(int $barangayId): bool
    {
        return $this->activeUpdateSchedule($barangayId) !== null;
    }

    public function lastCompletedProfilingYear(KabataanRegistration $registration): int
    {
        $years = [];

        $formData = is_array($registration->form_data) ? $registration->form_data : [];
        if (! empty($formData['profile_updated_year'])) {
            $years[] = (int) $formData['profile_updated_year'];
        }

        if (Schema::hasTable('kabataan_profiling_history')) {
            $historyYear = KabataanProfilingHistory::query()
                ->where('kabataan_registration_id', $registration->id)
                ->max('profiling_year');

            if ($historyYear) {
                $years[] = (int) $historyYear;
            }
        }

        return $years === [] ? 0 : max($years);
    }

    public function hasCompletedProfilingForYear(KabataanRegistration $registration, int $year): bool
    {
        $formData = is_array($registration->form_data) ? $registration->form_data : [];
        if (! empty($formData['profile_updated_year']) && (int) $formData['profile_updated_year'] >= $year) {
            return true;
        }

        if (! Schema::hasTable('kabataan_profiling_history')) {
            return false;
        }

        return KabataanProfilingHistory::query()
            ->where('kabataan_registration_id', $registration->id)
            ->where('profiling_year', '>=', $year)
            ->exists();
    }

    public function scheduleProfilingYear(?object $schedule): int
    {
        if ($schedule === null) {
            return $this->expectedProfilingYear();
        }

        return (int) ($schedule->profiling_year ?? $this->expectedProfilingYear());
    }

    public function requiresProfilingUpdate(?KabataanRegistration $registration): bool
    {
        if ($registration === null || ! $registration->user_id) {
            return false;
        }

        if (! in_array((string) $registration->status, ['active', 'email_verified', 'password_set'], true)) {
            return false;
        }

        $schedule = $this->activeUpdateSchedule((int) $registration->barangay_id);
        if ($schedule === null) {
            return false;
        }

        if ($registration->submitted_at === null) {
            return false;
        }

        $targetYear = $this->scheduleProfilingYear($schedule);

        return ! $this->hasCompletedProfilingForYear($registration, $targetYear);
    }

    public function targetProfilingYearForRegistration(KabataanRegistration $registration): ?int
    {
        $schedule = $this->activeUpdateSchedule((int) $registration->barangay_id);

        return $schedule ? $this->scheduleProfilingYear($schedule) : null;
    }
}
