<?php

namespace App\Modules\ScheduleKKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KKProfilingSchedule;
use App\Services\SkOfficialActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ScheduleKKProfilingController extends Controller
{
    private const MAX_RANGE_DAYS = 366;

    private const ALLOWED_STATUSES = ['Ongoing', 'Completed', 'Close'];

    public function __construct(private readonly SkOfficialActivityService $activityService)
    {
    }

    public function index()
    {
        return view('ScheduleKKProfiling::schedule-kkprofiling');
    }

    public function data(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->barangay_id) {
            return response()->json(['data' => [], 'years' => []]);
        }

        $query = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->orderBy('date_start', 'desc');

        if ($request->filled('year')) {
            $query->whereYear('date_start', (int) $request->year);
        }

        $schedules = $query->get(['id', 'date_start', 'date_expiry', 'link', 'status', 'created_at']);

        $years = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM date_start)::int AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return response()->json([
            'data' => $schedules,
            'years' => $years,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date_start' => 'required|date',
            'date_expiry' => 'required|date|after_or_equal:date_start',
            'link' => 'nullable|url|max:300',
            'status' => 'required|in:'.implode(',', self::ALLOWED_STATUSES),
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear($user->barangay_id, $validated['date_start']);

        $schedule = KKProfilingSchedule::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'date_start' => $validated['date_start'],
            'date_expiry' => $validated['date_expiry'],
            'link' => $validated['link'] ?? null,
            'status' => $validated['status'],
        ]);

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
            'link' => 'nullable|url|max:300',
            'status' => 'required|in:'.implode(',', self::ALLOWED_STATUSES),
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear($user->barangay_id, $validated['date_start'], $schedule->id);

        $previousStatus = $schedule->status;
        $schedule->update($validated);

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

    private function validateOneSchedulePerYear(int $barangayId, string $dateStart, ?int $excludeId = null): void
    {
        $year = Carbon::parse($dateStart)->year;

        $query = KKProfilingSchedule::where('barangay_id', $barangayId)
            ->whereYear('date_start', $year);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'date_start' => "A KK Profiling schedule for {$year} already exists.",
            ]);
        }
    }
}
