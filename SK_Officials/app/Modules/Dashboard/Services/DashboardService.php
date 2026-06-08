<?php

namespace App\Modules\Dashboard\Services;

use App\Models\AbyipDocument;
use App\Models\AbyipProgram;
use App\Models\AbyipProgramActivity;
use App\Models\CalendarNote;
use App\Models\KabataanRegistration;
use App\Models\RejectedKkProfiling;
use App\Models\SkOfficialActivity;
use App\Models\User;
use App\Modules\Committees\Services\CommitteeService;
use App\Services\SkOfficialPresenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private readonly CommitteeService $committeeService,
        private readonly SkOfficialPresenceService $presenceService,
    ) {
    }

    /**
     * Fast payload for stat cards, officials, and recent activity.
     *
     * @return array<string, mixed>
     */
    public function getSummary(User $user, int $year): array
    {
        $barangayId = $user->barangay_id;

        if ($barangayId === null) {
            return $this->emptyPayload();
        }

        $abyip = $this->resolveAbyipDocument($barangayId, $year);
        $kkStats = $this->kkProfileStats($barangayId);
        $budgetStats = $this->budgetStats($abyip);

        return [
            'year' => $year,
            'user_name' => $this->resolveUserDisplayName($user),
            'stats' => array_merge($kkStats, $budgetStats, [
                'active_programs' => $this->activeProgramCount($abyip),
                'deleted_kabataan' => $this->deletedKabataanCount($barangayId),
                'rejected_items' => $kkStats['rejected'],
            ]),
            'officials' => $this->officialsStatus($user),
            'upcoming_events' => $this->upcomingCalendarNotes($barangayId),
            'today_reminder' => $this->todayReminder($barangayId),
            'recent_activity' => $this->recentActivity($barangayId),
            'available_years' => $this->availableYears($barangayId),
        ];
    }

    /**
     * Chart-only payload for deferred dashboard rendering.
     *
     * @return array<string, mixed>
     */
    public function getChartData(User $user, int $year, string $granularity = 'monthly', ?int $month = null): array
    {
        $barangayId = $user->barangay_id;

        if ($barangayId === null) {
            return [
                'purok_labels' => [],
                'purok_counts' => [],
                'kk_requests_chart' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    'approved' => array_fill(0, 12, 0),
                    'pending' => array_fill(0, 12, 0),
                    'rejected' => array_fill(0, 12, 0),
                ],
                'gender_distribution' => ['labels' => ['Male', 'Female'], 'values' => [0, 0]],
                'budget_programs' => ['labels' => [], 'values' => []],
            ];
        }

        $abyip = $this->resolveAbyipDocument($barangayId, $year);
        $activeFormData = $this->activeRegistrationsFormData($barangayId);
        $purok = $this->purokDistributionFromRecords($activeFormData);
        $kkChart = $granularity === 'weekly' && $month !== null
            ? $this->weeklyKkRequestStats($barangayId, $year, $month)
            : $this->monthlyKkRequestStats($barangayId, $year);

        return [
            'purok_labels' => $purok['labels'],
            'purok_counts' => $purok['counts'],
            'kk_requests_chart' => $kkChart,
            'gender_distribution' => $this->genderDistributionFromRecords($activeFormData),
            'budget_programs' => $this->budgetProgramBreakdown($abyip),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(User $user, int $year, string $granularity = 'monthly', ?int $month = null): array
    {
        return array_merge(
            $this->getSummary($user, $year),
            $this->getChartData($user, $year, $granularity, $month),
        );
    }

    private function resolveAbyipDocument(int $barangayId, int $year): ?AbyipDocument
    {
        $abyip = AbyipDocument::query()
            ->where('barangay_id', $barangayId)
            ->where('fiscal_year', $year)
            ->orderByDesc('id')
            ->first();

        if ($abyip !== null) {
            return $abyip;
        }

        return AbyipDocument::query()
            ->where('barangay_id', $barangayId)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'year' => (int) now()->year,
            'user_name' => 'SK Official',
            'stats' => [
                'total_kk' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'active_programs' => 0,
                'budget' => 0,
                'expenses' => 0,
                'remaining' => 0,
                'deleted_kabataan' => 0,
                'rejected_items' => 0,
            ],
            'purok_labels' => [],
            'purok_counts' => [],
            'kk_requests_chart' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'approved' => array_fill(0, 12, 0),
                'pending' => array_fill(0, 12, 0),
                'rejected' => array_fill(0, 12, 0),
            ],
            'gender_distribution' => ['labels' => ['Male', 'Female'], 'values' => [0, 0]],
            'budget_programs' => ['labels' => [], 'values' => []],
            'officials' => [],
            'upcoming_events' => [],
            'today_reminder' => null,
            'recent_activity' => [],
            'available_years' => [(int) now()->year],
        ];
    }

    /**
     * @return array{total_kk: int, pending: int, approved: int, rejected: int}
     */
    private function kkProfileStats(int $barangayId): array
    {
        $base = KabataanRegistration::forBarangay($barangayId);

        $approved = (clone $base)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->count();

        $pending = (clone $base)
            ->whereNotIn('status', ['rejected'])
            ->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials'])
            ->count();

        $rejected = RejectedKkProfiling::forBarangay($barangayId)->active()->count()
            + (clone $base)
                ->where('evaluation_status', 'Duplicate')
                ->where('status', '!=', 'rejected')
                ->count();

        return [
            'total_kk' => (clone $base)->count(),
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];
    }

    /**
     * @return array{budget: float, expenses: float, remaining: float}
     */
    private function budgetStats(?AbyipDocument $abyip): array
    {
        if ($abyip === null) {
            return ['budget' => 0, 'expenses' => 0, 'remaining' => 0];
        }

        $allocation = (float) ($abyip->sk_fund_amount ?? 0);

        $expenses = (float) AbyipProgramActivity::query()
            ->where('abyip_id', $abyip->id)
            ->sum(DB::raw('COALESCE(total, budget, mooe, 0)'));

        if ($expenses <= 0 && $abyip->total_budget !== null) {
            $expenses = (float) $abyip->total_budget;
        }

        return [
            'budget' => $allocation,
            'expenses' => $expenses,
            'remaining' => max(0, $allocation - $expenses),
        ];
    }

    private function activeProgramCount(?AbyipDocument $abyip): int
    {
        if ($abyip === null) {
            return 0;
        }

        return AbyipProgram::query()
            ->where('abyip_id', $abyip->id)
            ->where(function ($query) {
                $query->where('row_type', 'youth_program')
                    ->orWhereIn('code', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
            })
            ->count();
    }

    private function deletedKabataanCount(int $barangayId): int
    {
        return KabataanRegistration::onlyTrashed()
            ->forBarangay($barangayId)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->count();
    }

    /**
     * @return \Illuminate\Support\Collection<int, KabataanRegistration>
     */
    private function activeRegistrationsFormData(int $barangayId)
    {
        return KabataanRegistration::forBarangay($barangayId)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->get(['form_data']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, KabataanRegistration>  $records
     * @return array{labels: list<string>, counts: list<int>}
     */
    private function purokDistributionFromRecords($records): array
    {
        $counts = [];

        foreach ($records as $record) {
            $purok = $this->formValue($record->form_data ?? [], 'purok_zone');
            if ($purok === '' || $purok === '—') {
                $purok = 'Unspecified';
            }
            $counts[$purok] = ($counts[$purok] ?? 0) + 1;
        }

        arsort($counts);

        return [
            'labels' => array_keys($counts),
            'counts' => array_values($counts),
        ];
    }

    /**
     * @return array{labels: list<string>, approved: list<int>, pending: list<int>, rejected: list<int>}
     */
    private function monthlyKkRequestStats(int $barangayId, int $year): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $approved = array_fill(0, 12, 0);
        $pending = array_fill(0, 12, 0);
        $rejected = array_fill(0, 12, 0);

        $approvedRows = KabataanRegistration::forBarangay($barangayId)
            ->where('status', 'active')
            ->whereIn('evaluation_status', ['active', 'Auto Approved'])
            ->whereYear('reviewed_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM reviewed_at)::int AS month_num, COUNT(*) AS total')
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        foreach ($approvedRows as $month => $count) {
            $approved[(int) $month - 1] = (int) $count;
        }

        $rejectedRows = RejectedKkProfiling::forBarangay($barangayId)
            ->whereYear('rejected_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM rejected_at)::int AS month_num, COUNT(*) AS total')
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        foreach ($rejectedRows as $month => $count) {
            $rejected[(int) $month - 1] = (int) $count;
        }

        $pendingRows = KabataanRegistration::forBarangay($barangayId)
            ->whereNotIn('status', ['rejected'])
            ->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials'])
            ->whereYear('submitted_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM submitted_at)::int AS month_num, COUNT(*) AS total')
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        foreach ($pendingRows as $month => $count) {
            $pending[(int) $month - 1] = (int) $count;
        }

        return compact('labels', 'approved', 'pending', 'rejected');
    }

    /**
     * @return array{labels: list<string>, approved: list<int>, pending: list<int>, rejected: list<int>}
     */
    private function weeklyKkRequestStats(int $barangayId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $labels = [];
        $approved = [];
        $pending = [];
        $rejected = [];

        $cursor = $start->copy();
        $weekIndex = 1;

        while ($cursor->lte($end)) {
            $weekEnd = $cursor->copy()->addDays(6);
            if ($weekEnd->gt($end)) {
                $weekEnd = $end->copy();
            }

            $labels[] = 'W'.$weekIndex;
            $approved[] = $this->kkCountBetween($barangayId, 'approved', $cursor, $weekEnd);
            $rejected[] = $this->kkCountBetween($barangayId, 'rejected', $cursor, $weekEnd);
            $pending[] = $this->kkCountBetween($barangayId, 'pending', $cursor, $weekEnd);

            $cursor = $weekEnd->copy()->addDay();
            $weekIndex++;
        }

        return compact('labels', 'approved', 'pending', 'rejected');
    }

    private function kkCountBetween(int $barangayId, string $type, Carbon $start, Carbon $end): int
    {
        $query = KabataanRegistration::forBarangay($barangayId);

        return match ($type) {
            'approved' => $query
                ->where('status', 'active')
                ->whereIn('evaluation_status', ['active', 'Auto Approved'])
                ->whereBetween('reviewed_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->count(),
            'rejected' => $query
                ->where('status', 'rejected')
                ->whereBetween('reviewed_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->count(),
            default => $query
                ->whereNotIn('status', ['rejected'])
                ->whereIn('evaluation_status', ['Not Profiled', 'Wrong Credentials'])
                ->whereBetween('submitted_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->count(),
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, KabataanRegistration>  $records
     * @return array{labels: list<string>, values: list<int>}
     */
    private function genderDistributionFromRecords($records): array
    {
        $male = 0;
        $female = 0;

        foreach ($records as $record) {
            $sex = mb_strtolower($this->formValue($record->form_data ?? [], 'sex'));
            if (str_contains($sex, 'male') && ! str_contains($sex, 'female')) {
                $male++;
            } elseif (str_contains($sex, 'female')) {
                $female++;
            }
        }

        return [
            'labels' => ['Male', 'Female'],
            'values' => [$male, $female],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function budgetProgramBreakdown(?AbyipDocument $abyip): array
    {
        if ($abyip === null) {
            return ['labels' => [], 'values' => []];
        }

        $programs = AbyipProgram::query()
            ->where('abyip_id', $abyip->id)
            ->where(function ($query) {
                $query->where('row_type', 'youth_program')
                    ->orWhereIn('code', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']);
            })
            ->orderBy('sort_order')
            ->get(['id', 'code', 'program_name']);

        $programIds = $programs->pluck('id')->all();
        $totalsByProgram = $programIds === []
            ? collect()
            : AbyipProgramActivity::query()
                ->whereIn('program_id', $programIds)
                ->selectRaw('program_id, SUM(COALESCE(total, budget, mooe, 0)) AS activity_total')
                ->groupBy('program_id')
                ->pluck('activity_total', 'program_id');

        $labels = [];
        $values = [];

        foreach ($programs as $program) {
            $total = (float) ($totalsByProgram[$program->id] ?? 0);

            if ($total <= 0) {
                continue;
            }

            $label = trim((string) ($program->code ? $program->code.'. ' : '').$program->program_name);
            $labels[] = $label;
            $values[] = $total;
        }

        if ($labels === []) {
            $general = AbyipProgram::query()
                ->where('abyip_id', $abyip->id)
                ->where('row_type', 'expenditure')
                ->orderBy('sort_order')
                ->get(['program_name']);

            foreach ($general as $program) {
                $labels[] = $program->program_name;
                $values[] = 0;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return list<array{name: string, position: string, status: string}>
     */
    private function officialsStatus(User $user): array
    {
        $this->presenceService->syncStaleOfflineStatuses();

        return User::query()
            ->with('officialProfile')
            ->where('barangay_id', $user->barangay_id)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function (User $official) {
                return [
                    'name' => $this->committeeService->buildOfficialFullName($official),
                    'position' => $official->officialProfile?->position ?? 'SK Official',
                    'status' => $this->presenceService->isOnline($official) ? 'Online' : 'Offline',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{day: string, month_label: string, title: string}>
     */
    private function upcomingCalendarNotes(int $barangayId): array
    {
        return CalendarNote::forBarangay($barangayId)
            ->whereDate('note_date', '>=', now()->toDateString())
            ->orderBy('note_date')
            ->limit(6)
            ->get()
            ->map(function (CalendarNote $note) {
                $date = $note->note_date;

                return [
                    'day' => $date->format('j'),
                    'month_label' => strtoupper($date->format('M')),
                    'title' => $note->title,
                ];
            })
            ->all();
    }

    /**
     * @return array{title: string, date_label: string}|null
     */
    private function todayReminder(int $barangayId): ?array
    {
        $note = CalendarNote::forBarangay($barangayId)
            ->whereDate('note_date', now()->toDateString())
            ->first();

        if ($note === null) {
            return null;
        }

        return [
            'title' => $note->title,
            'date_label' => now()->format('F j'),
        ];
    }

    /**
     * @return list<array{text: string, who: string, time: string}>
     */
    private function recentActivity(int $barangayId): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('sk_official_activities')) {
            return [];
        }

        return SkOfficialActivity::query()
            ->with(['user.officialProfile'])
            ->forBarangay($barangayId)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function (SkOfficialActivity $activity) {
                $officialName = $activity->user
                    ? $this->committeeService->buildOfficialFullName($activity->user)
                    : 'SK Official';
                $position = $activity->user?->officialProfile?->position ?? 'SK Official';

                return [
                    'text' => $activity->description,
                    'who' => $officialName,
                    'position' => $position,
                    'time' => $activity->created_at?->diffForHumans() ?? '—',
                ];
            })
            ->all();
    }

    /**
     * @return list<int>
     */
    private function availableYears(int $barangayId): array
    {
        $years = AbyipDocument::query()
            ->where('barangay_id', $barangayId)
            ->pluck('fiscal_year')
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $current = (int) now()->year;
        if (! in_array($current, $years, true)) {
            array_unshift($years, $current);
        }

        return $years !== [] ? $years : [$current];
    }

    private function resolveUserDisplayName(User $user): string
    {
        $profile = $user->officialProfile;
        if ($profile !== null) {
            return $this->committeeService->buildOfficialFullName($user);
        }

        return trim((string) $user->name) ?: 'SK Official';
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function formValue(array $formData, string $key): string
    {
        $value = $formData[$key] ?? '';

        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        return trim((string) $value);
    }
}
