<?php

namespace App\Modules\Calendar\Controllers;

use App\Modules\Shared\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Modules\Shared\Models\User;
use App\Services\SkFedActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function __construct(private readonly SkFedActivityService $activityService)
    {
    }

    /**
     * Display the calendar page.
     */
    public function page(Request $request): View
    {
        return view('Calendar::calendar', ['user' => $request->user()]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $events = CalendarEvent::query()
            ->visibleToFederation()
            ->whereBetween('event_date', [$start, $end])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (CalendarEvent $event) => $this->formatEvent($event));

        return response()->json(['data' => $events]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'task_type' => ['required', 'in:Event,Meeting,Training,Reminder,Workshop,Seminar,Conference,Activity'],
            'status' => ['required', 'in:Pending,Complete,Cancel'],
            'target_audience' => ['required', 'in:All SK Officials,SK Fed'],
        ]);

        $this->assertCanModifyDate($validated['event_date']);
        $this->assertValidTimeRange($validated['start_time'], $validated['end_time']);

        // Check for time overlap on the same date
        $overlap = CalendarEvent::query()
            ->visibleToFederation()
            ->whereDate('event_date', $validated['event_date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => ['This time slot overlaps with an existing event.'],
            ]);
        }

        $event = CalendarEvent::create([
            'barangay_id' => $user->barangay_id,
            'user_id' => $user->id,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'task_type' => $validated['task_type'],
            'status' => $validated['status'],
            'target_audience' => $validated['target_audience'],
        ]);

        $this->activityService->log(
            $user,
            'calendar.create',
            'Added calendar event: '.$validated['title'],
            ['event_id' => $event->id, 'event_date' => $validated['event_date']]
        );

        return response()->json([
            'message' => 'Event saved.',
            'data' => $this->formatEvent($event),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $event = $this->findEvent($id);

        $this->assertCanModifyDate($event->event_date->toDateString());

        $validated = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'task_type' => ['required', 'in:Event,Meeting,Training,Reminder,Workshop,Seminar,Conference,Activity'],
            'status' => ['required', 'in:Pending,Complete,Cancel'],
            'target_audience' => ['required', 'in:All SK Officials,SK Fed'],
        ]);

        $this->assertValidTimeRange($validated['start_time'], $validated['end_time']);

        // Check for time overlap (excluding current event)
        $overlap = CalendarEvent::query()
            ->visibleToFederation()
            ->whereDate('event_date', $event->event_date)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                          ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => ['This time slot overlaps with an existing event.'],
            ]);
        }

        $event->update($validated);

        $this->activityService->log(
            $user,
            'calendar.update',
            'Updated calendar event: '.$validated['title'],
            ['event_id' => $event->id]
        );

        return response()->json([
            'message' => 'Event updated.',
            'data' => $this->formatEvent($event->fresh()),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $event = $this->findEvent($id);

        $this->assertCanModifyDate($event->event_date->toDateString());

        $title = $event->title;
        $event->delete();

        $this->activityService->log(
            $user,
            'calendar.delete',
            'Deleted calendar event: '.$title,
            ['event_id' => $id]
        );

        return response()->json(['message' => 'Event deleted.']);
    }

    protected function findEvent(int $id): CalendarEvent
    {
        $event = CalendarEvent::query()->visibleToFederation()->find($id);

        if ($event === null) {
            throw ValidationException::withMessages([
                'event' => ['Calendar event not found.'],
            ]);
        }

        return $event;
    }

    protected function assertValidTimeRange(string $startTime, string $endTime): void
    {
        [$startHour, $startMin] = explode(':', $startTime);
        [$endHour, $endMin] = explode(':', $endTime);

        $startHour = (int) $startHour;
        $endHour = (int) $endHour;

        // Check if start time is between 7:00 and 22:00
        if ($startHour < 7 || $startHour > 22 || ($startHour === 22 && (int) $startMin > 0)) {
            throw ValidationException::withMessages([
                'start_time' => ['Start time must be between 7:00 AM and 10:00 PM.'],
            ]);
        }

        // Check if end time is between 7:00 and 22:00
        if ($endHour < 7 || $endHour > 22 || ($endHour === 22 && (int) $endMin > 0)) {
            throw ValidationException::withMessages([
                'end_time' => ['End time must be between 7:00 AM and 10:00 PM.'],
            ]);
        }
    }

    protected function assertCanModifyDate(string $date): void
    {
        $currentYear = (int) now()->year;
        $eventYear = (int) \Carbon\Carbon::parse($date)->year;

        if ($eventYear < $currentYear) {
            throw ValidationException::withMessages([
                'event_date' => ['Events cannot be added or edited for past years.'],
            ]);
        }

        if ($eventYear > $currentYear) {
            throw ValidationException::withMessages([
                'event_date' => ['Events cannot be added or edited for next year or beyond.'],
            ]);
        }
    }

    protected function formatEvent(CalendarEvent $event): array
    {
        return [
            'id' => $event->id,
            'event_date' => $event->event_date->format('Y-m-d'),
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'title' => $event->title,
            'description' => $event->description,
            'task_type' => $event->task_type,
            'status' => $event->status,
            'target_audience' => $event->target_audience,
            'created_at' => $event->created_at?->toIso8601String(),
            'updated_at' => $event->updated_at?->toIso8601String(),
        ];
    }
}
