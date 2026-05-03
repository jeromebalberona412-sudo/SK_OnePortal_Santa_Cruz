<?php

namespace App\Modules\ScheduleKKProfiling\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KKProfilingSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleKKProfilingController extends Controller
{
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
            'Upcoming'    => $schedules->where('status', 'Upcoming')->count(),
            'Ongoing'     => $schedules->where('status', 'Ongoing')->count(),
            'Completed'   => $schedules->where('status', 'Completed')->count(),
            'Cancelled'   => $schedules->where('status', 'Cancelled')->count(),
            'Rescheduled' => $schedules->where('status', 'Rescheduled')->count(),
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
            'status'      => 'required|in:Upcoming,Ongoing,Completed,Cancelled,Rescheduled',
        ]);

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
            'status'      => 'required|in:Upcoming,Ongoing,Completed,Cancelled,Rescheduled',
        ]);

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
            'Upcoming' => 0, 'Ongoing' => 0, 'Completed' => 0,
            'Cancelled' => 0, 'Rescheduled' => 0,
        ];
    }
}
