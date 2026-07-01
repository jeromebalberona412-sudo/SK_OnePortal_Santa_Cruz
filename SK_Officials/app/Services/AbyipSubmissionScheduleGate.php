<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AbyipSubmissionScheduleGate
{
    /** @var list<string> */
    private const ALLOWED_POSITIONS = ['Chairperson', 'Secretary', 'Treasurer'];

    /**
     * @return array<string, mixed>
     */
    public function submissionStatus(?User $user = null): array
    {
        $calendarYear = (int) date('Y');

        if ($user !== null && ! $this->userCanSubmitByPosition($user)) {
            return [
                'can_submit' => false,
                'fiscal_year' => $calendarYear,
                'message' => 'Only SK Chairperson, Secretary, and Treasurer can submit ABYIP documents.',
                'schedule' => null,
            ];
        }

        $schedule = $this->activeSchedule();

        if ($schedule === null) {
            return [
                'can_submit' => false,
                'fiscal_year' => $calendarYear,
                'message' => 'No ABYIP submission schedule has been set by SK Federation. Please contact SK Federation.',
                'schedule' => null,
            ];
        }

        $tz = config('app.timezone', 'Asia/Manila');
        $today = Carbon::now($tz)->startOfDay();
        $start = Carbon::parse($schedule->date_start, $tz)->startOfDay();
        $end = Carbon::parse($schedule->deadline, $tz)->startOfDay();

        if ($today->lt($start)) {
            return [
                'can_submit' => false,
                'fiscal_year' => $calendarYear,
                'message' => 'ABYIP submission opens on '.$start->format('M j, Y').'.',
                'schedule' => $this->formatSchedule($schedule),
            ];
        }

        if ($today->gt($end)) {
            return [
                'can_submit' => false,
                'fiscal_year' => $calendarYear,
                'message' => 'The ABYIP submission deadline has passed.',
                'schedule' => $this->formatSchedule($schedule),
            ];
        }

        return [
            'can_submit' => true,
            'fiscal_year' => $calendarYear,
            'message' => null,
            'schedule' => $this->formatSchedule($schedule),
        ];
    }

    public function assertCanSubmit(?User $user = null): void
    {
        if ($user !== null && ! $this->userCanSubmitByPosition($user)) {
            throw ValidationException::withMessages([
                'position' => ['Only SK Chairperson, Secretary, and Treasurer can submit ABYIP documents.'],
            ]);
        }

        $status = $this->submissionStatus($user);

        if (! $status['can_submit']) {
            throw ValidationException::withMessages([
                'schedule' => [$status['message'] ?? 'ABYIP submission is not available.'],
            ]);
        }
    }

    private function userCanSubmitByPosition(User $user): bool
    {
        $user->loadMissing('officialProfile');

        $position = $user->officialProfile?->position;

        return in_array($position, self::ALLOWED_POSITIONS, true);
    }

    private function activeSchedule(): ?object
    {
        if (! Schema::hasTable('abyip_submission_schedules')) {
            return null;
        }

        return DB::table('abyip_submission_schedules')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('fiscal_year')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSchedule(object $schedule): array
    {
        return [
            'calendar_year' => (int) $schedule->fiscal_year,
            'fiscal_year' => (int) $schedule->fiscal_year,
            'title' => (string) ($schedule->title ?? 'ABYIP Submission'),
            'date_start' => (string) $schedule->date_start,
            'deadline' => (string) $schedule->deadline,
            'status' => (string) ($schedule->status ?? ''),
        ];
    }
}
