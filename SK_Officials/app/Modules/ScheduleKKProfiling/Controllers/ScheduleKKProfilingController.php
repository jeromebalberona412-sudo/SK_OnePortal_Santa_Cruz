<?php

namespace App\Modules\ScheduleKKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KKProfilingSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ScheduleKKProfilingController extends Controller
{
    private const MAX_RANGE_DAYS = 366;
    public function index()
    {
        return view('ScheduleKKProfiling::schedule-kkprofiling');
    }

    public function data()
    {
        $user = Auth::user();

        if (!$user || !$user->barangay_id) {
            return response()->json(['data' => [], 'stats' => $this->emptyStats()]);
        }

        $schedules = KKProfilingSchedule::where('barangay_id', $user->barangay_id)
            ->orderBy('date_start', 'desc')
            ->get(['id', 'date_start', 'date_expiry', 'link', 'status', 'created_at']);

        $stats = [
            'Upcoming'  => $schedules->where('status', 'Upcoming')->count(),
            'Ongoing'   => $schedules->where('status', 'Ongoing')->count(),
            'Completed' => $schedules->where('status', 'Completed')->count(),
            'Cancelled' => $schedules->where('status', 'Cancelled')->count(),
        ];

        return response()->json(['data' => $schedules, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'date_start'  => 'required|date',
            'date_expiry' => 'required|date|after_or_equal:date_start',
            'link'        => 'nullable|url|max:300',
            'status'      => 'required|in:Upcoming,Ongoing,Completed,Cancelled',
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateCurrentYear($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear($user->barangay_id, $validated['date_start']);

        $schedule = KKProfilingSchedule::create([
            'tenant_id'   => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by'  => $user->id,
            'date_start'  => $validated['date_start'],
            'date_expiry' => $validated['date_expiry'],
            'link'        => $validated['link'] ?? null,
            'status'      => $validated['status'],
        ]);

        return response()->json($schedule, 201);
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        $schedule = KKProfilingSchedule::where('id', $id)
            ->where('barangay_id', $user->barangay_id)
            ->firstOrFail();

        $validated = $request->validate([
            'date_start'  => 'required|date',
            'date_expiry' => 'required|date|after_or_equal:date_start',
            'link'        => 'nullable|url|max:300',
            'status'      => 'required|in:Upcoming,Ongoing,Completed,Cancelled',
        ]);

        $this->validateScheduleDateWindow($validated['date_start'], $validated['date_expiry']);
        $this->validateCurrentYear($validated['date_start'], $validated['date_expiry']);
        $this->validateOneSchedulePerYear($user->barangay_id, $validated['date_start'], $schedule->id);

        $schedule->update($validated);

        return response()->json($schedule);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        KKProfilingSchedule::where('id', $id)
            ->where('barangay_id', $user->barangay_id)
            ->firstOrFail()
            ->delete();

        return response()->json(['ok' => true]);
    }

    private function emptyStats(): array
    {
        return [
            'Upcoming' => 0, 'Ongoing' => 0, 'Completed' => 0, 'Cancelled' => 0,
        ];
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

    private function validateCurrentYear(string $dateStart, string $dateExpiry): void
    {
        $tz = config('app.timezone', 'Asia/Manila');
        $currentYear = Carbon::now($tz)->year;
        $startYear = Carbon::parse($dateStart, $tz)->year;
        $expiryYear = Carbon::parse($dateExpiry, $tz)->year;

        if ($startYear !== $currentYear || $expiryYear !== $currentYear) {
            throw ValidationException::withMessages([
                'date_start' => 'Schedule dates must be within the current year.',
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
