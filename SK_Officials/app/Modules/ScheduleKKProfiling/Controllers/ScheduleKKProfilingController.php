<?php

namespace App\Modules\ScheduleKKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KKProfilingSchedule;
use App\Services\KkProfilingSignupLinkService;
use App\Services\SkOfficialActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ScheduleKKProfilingController extends Controller
{
    private const MAX_RANGE_DAYS = 366;

    private const ALLOWED_STATUSES = ['Ongoing', 'Completed', 'Close'];

    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly KkProfilingSignupLinkService $signupLinkService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $signupLink = $this->signupLinkService->forBarangay($user?->barangay);

        return view('ScheduleKKProfiling::schedule-kkprofiling', [
            'signupLink' => $signupLink,
            'barangayName' => $user?->barangay?->name,
        ]);
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'years' => [], 'signup_link' => null]);
        }

        $signupLink = $this->signupLinkService->forBarangay($user->barangay);
        $query = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->orderBy('date_start', 'desc');

        if ($request->filled('year')) {
            $query->where('profiling_year', (int) $request->year);
        }

        $schedules = $query->get([
            'id',
            'profiling_year',
            'date_start',
            'date_expiry',
            'status',
            'link',
            'allow_existing_update',
            'created_at',
        ])->map(function (KKProfilingSchedule $schedule) use ($signupLink) {
            return [
                'id' => $schedule->id,
                'profiling_year' => $schedule->profiling_year,
                'date_start' => optional($schedule->date_start)->format('Y-m-d'),
                'date_expiry' => optional($schedule->date_expiry)->format('Y-m-d'),
                'status' => $schedule->status,
                'link' => $schedule->link ?: $signupLink,
                'allow_existing_update' => (bool) $schedule->allow_existing_update,
                'created_at' => optional($schedule->created_at)->toIso8601String(),
            ];
        })->values();

        $currentYear = $this->resolveProfilingYear();
        $years = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->whereNotNull('profiling_year')
            ->select('profiling_year')
            ->distinct()
            ->orderByDesc('profiling_year')
            ->pluck('profiling_year')
            ->map(fn ($year) => (int) $year)
            ->values();

        $yearOptions = collect([$currentYear, $currentYear - 1, $currentYear - 2])
            ->merge($years)
            ->unique()
            ->sortDesc()
            ->values();

        $currentYearSchedule = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->where('profiling_year', $currentYear)
            ->first();

        return response()->json([
            'data' => $schedules,
            'years' => $yearOptions,
            'expected_profiling_year' => $currentYear,
            'signup_link' => $signupLink,
            'barangay_name' => $user->barangay?->name,
            'has_current_year_schedule' => $currentYearSchedule !== null,
            'has_existing_update_for_year' => (bool) ($currentYearSchedule?->allow_existing_update),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date_start' => 'required|date',
            'date_expiry' => 'required|date|after_or_equal:date_start',
            'status' => 'required|in:'.implode(',', self::ALLOWED_STATUSES),
            'allow_existing_update' => 'sometimes|boolean',
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear($user->barangay_id, $this->resolveProfilingYear());

        $allowExistingUpdate = $request->boolean('allow_existing_update');
        $this->validateOneExistingUpdatePerYear($user->barangay_id, $this->resolveProfilingYear(), $allowExistingUpdate);

        $schedule = KKProfilingSchedule::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'profiling_year' => $this->resolveProfilingYear(),
            'created_by' => $user->id,
            'date_start' => $validated['date_start'],
            'date_expiry' => $validated['date_expiry'],
            'link' => $this->signupLinkService->forBarangay($user->barangay),
            'status' => $validated['status'],
            'allow_existing_update' => $allowExistingUpdate,
        ]);
        $schedule->refresh();

        $this->activityService->log(
            $user,
            'kk_schedule.create',
            'Created KK Profiling schedule ('.$validated['date_start'].' to '.$validated['date_expiry'].') with status '.$validated['status'].'.'
        );

        return response()->json($schedule, 201);
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        $schedule = KKProfilingSchedule::where('id', $id)
            ->where('barangay_id', $user->barangay_id)
            ->firstOrFail();

        $validated = $request->validate([
            'date_start' => 'required|date',
            'date_expiry' => 'required|date|after_or_equal:date_start',
            'status' => 'required|in:'.implode(',', self::ALLOWED_STATUSES),
            'allow_existing_update' => 'sometimes|boolean',
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear(
            $user->barangay_id,
            (int) ($schedule->profiling_year ?? $this->resolveProfilingYear()),
            $schedule->id,
        );

        $allowExistingUpdate = $request->boolean('allow_existing_update');
        $this->validateOneExistingUpdatePerYear(
            $user->barangay_id,
            (int) ($schedule->profiling_year ?? $this->resolveProfilingYear()),
            $allowExistingUpdate,
            $schedule,
        );

        $previousStatus = $schedule->status;
        $schedule->update([
            'date_start' => $validated['date_start'],
            'date_expiry' => $validated['date_expiry'],
            'status' => $validated['status'],
            'link' => $schedule->link ?: $this->signupLinkService->forBarangay($user->barangay),
            'allow_existing_update' => $allowExistingUpdate,
        ]);
        $schedule->refresh();

        $description = $previousStatus !== $validated['status']
            ? 'Updated KK Profiling schedule and changed status from '.$previousStatus.' to '.$validated['status'].'.'
            : 'Updated KK Profiling schedule ('.$validated['date_start'].' to '.$validated['date_expiry'].').';

        $this->activityService->log($user, 'kk_schedule.update', $description);

        return response()->json($schedule);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        $schedule = KKProfilingSchedule::where('id', $id)
            ->where('barangay_id', $user->barangay_id)
            ->firstOrFail();

        $schedule->delete();

        $this->activityService->log(
            $user,
            'kk_schedule.delete',
            'Deleted KK Profiling schedule ('.$schedule->date_start.' to '.$schedule->date_expiry.').'
        );

        return response()->json(['ok' => true]);
    }

    private function validateScheduleDateWindow(string $dateStart, string $dateExpiry): void
    {
        $tz = config('app.timezone', 'Asia/Manila');
        $today = Carbon::now($tz)->startOfDay();
        $start = Carbon::parse($dateStart, $tz)->startOfDay();
        $expiry = Carbon::parse($dateExpiry, $tz)->startOfDay();

        if ($start->lt($today)) {
            throw ValidationException::withMessages([
                'date_start' => 'Past dates are not allowed.',
            ]);
        }

        if ($expiry->lt($today)) {
            throw ValidationException::withMessages([
                'date_expiry' => 'Past dates are not allowed.',
            ]);
        }

        if ($start->diffInDays($expiry) > self::MAX_RANGE_DAYS) {
            throw ValidationException::withMessages([
                'date_expiry' => 'Date range cannot exceed one year.',
            ]);
        }
    }

    private function validateOneSchedulePerYear(int $barangayId, int $profilingYear, ?int $excludeId = null): void
    {
        $query = KKProfilingSchedule::where('barangay_id', $barangayId)
            ->where('profiling_year', $profilingYear);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'date_start' => 'KK profiling this year is already created.',
            ]);
        }
    }

    private function validateOneExistingUpdatePerYear(
        int $barangayId,
        int $profilingYear,
        bool $allowExistingUpdate,
        ?KKProfilingSchedule $current = null,
    ): void {
        if (! $allowExistingUpdate) {
            return;
        }

        if ($current?->allow_existing_update) {
            return;
        }

        $query = KKProfilingSchedule::where('barangay_id', $barangayId)
            ->where('profiling_year', $profilingYear)
            ->whereRaw('allow_existing_update IS TRUE');

        if ($current) {
            $query->where('id', '!=', $current->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'allow_existing_update' => 'An existing-kabataan KK Profiling update is already scheduled for this year.',
            ]);
        }
    }

    private function resolveProfilingYear(?string $dateStart = null): int
    {
        return (int) Carbon::now(config('app.timezone', 'Asia/Manila'))->format('Y');
    }
}
