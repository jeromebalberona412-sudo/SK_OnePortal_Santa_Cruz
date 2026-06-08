<?php

namespace App\Modules\Calendar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CalendarNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $notes = CalendarNote::forBarangay($user->barangay_id)
            ->whereBetween('note_date', [$start, $end])
            ->orderBy('note_date')
            ->get()
            ->map(fn (CalendarNote $note) => $this->formatNote($note));

        return response()->json(['data' => $notes]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'note_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:500'],
        ]);

        $this->assertCanModifyDate($validated['note_date']);

        $exists = CalendarNote::forBarangay($user->barangay_id)
            ->whereDate('note_date', $validated['note_date'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'note_date' => ['A note already exists for this date.'],
            ]);
        }

        $note = CalendarNote::create([
            'barangay_id' => $user->barangay_id,
            'user_id' => $user->id,
            'note_date' => $validated['note_date'],
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Note saved.',
            'data' => $this->formatNote($note),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $note = $this->findNote($user->barangay_id, $id);

        $this->assertCanModifyDate($note->note_date->toDateString());

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:500'],
        ]);

        $note->update($validated);

        return response()->json([
            'message' => 'Note updated.',
            'data' => $this->formatNote($note->fresh()),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $note = $this->findNote($user->barangay_id, $id);

        $this->assertCanModifyDate($note->note_date->toDateString());

        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    protected function findNote(int $barangayId, int $id): CalendarNote
    {
        $note = CalendarNote::forBarangay($barangayId)->find($id);

        if ($note === null) {
            throw ValidationException::withMessages([
                'note' => ['Calendar note not found.'],
            ]);
        }

        return $note;
    }

    protected function assertCanModifyDate(string $date): void
    {
        $today = now()->startOfDay();
        $noteDate = \Carbon\Carbon::parse($date)->startOfDay();

        if ($noteDate->year !== $today->year) {
            throw ValidationException::withMessages([
                'note_date' => ['Notes can only be added or edited for the current year.'],
            ]);
        }

        if (! $noteDate->isSameDay($today)) {
            throw ValidationException::withMessages([
                'note_date' => ['Notes can only be added or edited on today\'s date.'],
            ]);
        }
    }

    protected function formatNote(CalendarNote $note): array
    {
        return [
            'id' => $note->id,
            'note_date' => $note->note_date->format('Y-m-d'),
            'title' => $note->title,
            'content' => $note->content,
            'created_at' => $note->created_at?->toIso8601String(),
            'updated_at' => $note->updated_at?->toIso8601String(),
        ];
    }
}
