<?php

namespace App\Services;

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

    public function activeUpdateSchedule(int $barangayId): ?object
    {
        if (! Schema::hasTable('kk_profiling_schedules')) {
            return null;
        }

        $today = now($this->timezone())->toDateString();

        return DB::table('kk_profiling_schedules')
            ->where('barangay_id', $barangayId)
            ->where('status', 'Ongoing')
            ->where('date_start', '<=', $today)
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
        $formData = is_array($registration->form_data) ? $registration->form_data : [];

        if (! empty($formData['profile_updated_year'])) {
            return (int) $formData['profile_updated_year'];
        }

        if ($registration->submitted_at) {
            $submittedDate = $registration->submitted_at
                ->timezone($this->timezone())
                ->toDateString();
            $schedule = $this->activeUpdateSchedule((int) $registration->barangay_id);

            // Initial KK registration completed during the active schedule window
            // already satisfies that profiling cycle.
            if ($schedule !== null
                && $submittedDate >= (string) $schedule->date_start
                && $submittedDate <= (string) $schedule->date_expiry
            ) {
                return $this->scheduleProfilingYear($schedule);
            }

            return (int) $registration->submitted_at
                ->timezone($this->timezone())
                ->format('Y');
        }

        return 0;
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

        return $this->lastCompletedProfilingYear($registration) < $targetYear;
    }

    public function targetProfilingYearForRegistration(KabataanRegistration $registration): ?int
    {
        $schedule = $this->activeUpdateSchedule((int) $registration->barangay_id);

        return $schedule ? $this->scheduleProfilingYear($schedule) : null;
    }
}
