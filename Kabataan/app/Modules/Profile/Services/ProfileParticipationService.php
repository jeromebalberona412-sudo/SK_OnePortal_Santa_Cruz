<?php

namespace App\Modules\Profile\Services;

use App\Models\ProgramApplication;
use App\Models\ScheduleProgram;
use App\Models\User;
use App\Modules\Programs\Services\KabataanProgramService;
use App\Modules\Programs\Services\KabataanProgramSurveyService;
use Carbon\Carbon;

class ProfileParticipationService
{
    /** @var array<string, string> */
    private const LETTER_CATEGORIES = [
        'A' => 'Education',
        'B' => 'Environmental Protection',
        'C' => 'Disaster Preparedness',
        'D' => 'Youth Employment and Livelihood',
        'E' => 'Health',
        'F' => 'Anti-Drug and Peace and Order',
        'G' => 'Gender and Development',
        'H' => 'Feeding',
        'I' => 'Sports Development',
        'J' => 'Others',
    ];

    public function __construct(
        private readonly KabataanProgramService $programService,
        private readonly KabataanProgramSurveyService $surveyService,
    ) {
    }

    /**
     * @return array{
     *     programs: list<object>,
     *     summary: array<string, int>,
     *     calendar_events: array<string, list<array<string, mixed>>>,
     *     abyip_programs: list<array<string, mixed>>,
     *     participation_details: array<string, array<string, mixed>>
     * }
     */
    public function getParticipationData(User $user): array
    {
        $applications = $this->programService->listUserApplications($user, true);
        $surveyResponses = $this->surveyService->listUserResponseDetails($user);
        $dashboardPayload = $this->programService->getDashboardPayload($user);
        $abyipPrograms = $dashboardPayload['abyip_programs'] ?? [];

        $participationDetails = [];
        $programs = [];

        foreach ($applications as $application) {
            $status = $this->mapApplicationStatus((string) ($application['status'] ?? 'pending'));
            $detailKey = 'application-'.($application['id'] ?? 0);
            $participationDetails[$detailKey] = $application;

            $answerPreview = $this->buildAnswerPreview($application['answers'] ?? []);

            $programs[] = (object) [
                'id' => $detailKey,
                'source' => 'application',
                'source_id' => (int) ($application['id'] ?? 0),
                'name' => $application['program_name'] ?? 'Program Application',
                'category' => $application['program_type'] ?? ($application['committee'] ?? 'Program'),
                'status' => $status,
                'created_at' => $application['submitted_at_iso'] ?? now()->toIso8601String(),
                'description' => $application['program_period'] ?? '',
                'answer_count' => count($application['answers'] ?? []),
                'answers_preview' => $answerPreview,
            ];
        }

        foreach ($surveyResponses as $response) {
            $detailKey = 'survey-'.($response['id'] ?? 0);
            $participationDetails[$detailKey] = $response;
            $answerPreview = $this->buildSurveyAnswerPreview($response['answers'] ?? []);

            $programs[] = (object) [
                'id' => $detailKey,
                'source' => 'survey',
                'source_id' => (int) ($response['id'] ?? 0),
                'name' => $response['program_name'] ?? 'Program Survey',
                'category' => 'Survey Response',
                'status' => 'completed',
                'created_at' => $response['submitted_at_iso'] ?? now()->toIso8601String(),
                'description' => $response['survey_period'] ?? '',
                'answer_count' => count($response['answers'] ?? []),
                'answers_preview' => $answerPreview,
            ];
        }

        usort($programs, fn ($a, $b) => strcmp((string) $b->created_at, (string) $a->created_at));

        $summary = $this->buildSummary($programs);
        $calendarEvents = $this->buildCalendarEvents($user, $abyipPrograms, $programs, $dashboardPayload['schedule_programs'] ?? []);

        return [
            'programs' => $programs,
            'summary' => $summary,
            'calendar_events' => $calendarEvents,
            'abyip_programs' => $abyipPrograms,
            'participation_details' => $participationDetails,
        ];
    }

    /**
     * @param  list<object>  $programs
     * @return array<string, int>
     */
    private function buildSummary(array $programs): array
    {
        return [
            'total' => count($programs),
            'approved' => count(array_filter($programs, fn ($p) => $p->status === 'approved')),
            'evaluation' => count(array_filter($programs, fn ($p) => $p->status === 'evaluation')),
            'completed' => count(array_filter($programs, fn ($p) => $p->status === 'completed')),
            'pending' => count(array_filter($programs, fn ($p) => $p->status === 'pending')),
        ];
    }

    private function mapApplicationStatus(string $status): string
    {
        return match ($status) {
            ProgramApplication::STATUS_APPROVED => 'approved',
            ProgramApplication::STATUS_REJECTED => 'declined',
            ProgramApplication::STATUS_CANCELLED => 'declined',
            default => 'pending',
        };
    }

    /**
     * @param  list<array<string, mixed>>  $answers
     */
    private function buildAnswerPreview(array $answers): string
    {
        if ($answers === []) {
            return 'No answers recorded';
        }

        $first = $answers[0]['answer'] ?? null;
        $questionType = (string) ($answers[0]['question_type'] ?? '');

        if ($questionType === 'file' && is_array($first)) {
            return trim((string) ($first['original_name'] ?? 'Document uploaded'));
        }

        if (is_array($first)) {
            if (isset($first['original_name'])) {
                return trim((string) $first['original_name']);
            }

            $first = implode(', ', array_map(
                fn ($value) => is_scalar($value) ? (string) $value : '',
                $first
            ));
        }

        return trim((string) ($first ?? 'Submitted'));
    }

    /**
     * @param  list<array<string, mixed>>  $answers
     */
    private function buildSurveyAnswerPreview(array $answers): string
    {
        if ($answers === []) {
            return 'Survey submitted';
        }

        $first = $answers[0]['answer'] ?? null;
        if (is_array($first)) {
            $first = implode(', ', $first);
        }

        return trim((string) ($first ?? 'Survey submitted'));
    }

    /**
     * @param  list<array<string, mixed>>  $abyipPrograms
     * @param  list<object>  $participationPrograms
     * @param  list<array<string, mixed>>  $schedulePrograms
     * @return array<string, list<array<string, mixed>>>
     */
    private function buildCalendarEvents(User $user, array $abyipPrograms, array $participationPrograms, array $schedulePrograms): array
    {
        $events = [];

        foreach ($participationPrograms as $program) {
            $date = Carbon::parse($program->created_at)->format('Y-m-d');
            $events[$date][] = [
                'name' => $program->name,
                'category' => $program->category,
                'status' => $program->status,
                'type' => $program->source,
            ];
        }

        foreach ($schedulePrograms as $schedule) {
            $start = $schedule['start_date'] ?? null;
            $end = $schedule['end_date'] ?? $start;
            if ($start === null) {
                continue;
            }

            $this->addDateRangeEvent($events, $start, $end, [
                'name' => $schedule['program_name'] ?? 'Scheduled Program',
                'category' => $schedule['program_type'] ?? 'Program Schedule',
                'status' => ($schedule['status'] ?? 'open') === 'open' ? 'ongoing' : 'scheduled',
                'type' => 'schedule',
            ]);
        }

        $barangaySchedules = ScheduleProgram::query()
            ->when($user->tenant_id, fn ($q) => $q->where('tenant_id', $user->tenant_id))
            ->when($user->barangay_id, fn ($q) => $q->where('barangay_id', $user->barangay_id))
            ->whereNotNull('start_date')
            ->orderBy('start_date')
            ->get();

        foreach ($barangaySchedules as $schedule) {
            $this->addDateRangeEvent($events, $schedule->start_date->format('Y-m-d'), $schedule->end_date?->format('Y-m-d') ?? $schedule->start_date->format('Y-m-d'), [
                'name' => $schedule->program_name,
                'category' => $schedule->program_type ?? 'Program Schedule',
                'status' => $schedule->status === ScheduleProgram::STATUS_OPEN ? 'ongoing' : 'scheduled',
                'type' => 'schedule',
            ]);
        }

        foreach ($abyipPrograms as $abyipProgram) {
            $letter = strtoupper((string) ($abyipProgram['letter'] ?? ''));
            $category = self::LETTER_CATEGORIES[$letter] ?? 'Youth Program';
            $survey = $abyipProgram['survey'] ?? null;

            if (is_array($survey)) {
                $this->addDateRangeEvent($events, $survey['open_date'] ?? null, $survey['close_date'] ?? null, [
                    'name' => ($abyipProgram['title'] ?? 'Program').' Survey',
                    'category' => $category,
                    'status' => ($survey['has_responded'] ?? false) ? 'completed' : 'scheduled',
                    'type' => 'survey',
                ]);
            }

            if ($calendarYear = (int) (Carbon::now()->year)) {
                $yearKey = $calendarYear.'-01-15';
                $events[$yearKey][] = [
                    'name' => $abyipProgram['title'] ?? 'Youth Program',
                    'category' => $category,
                    'status' => 'ongoing',
                    'type' => 'abyip',
                ];
            }
        }

        ksort($events);

        return $events;
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $events
     * @param  array<string, mixed>  $payload
     */
    private function addDateRangeEvent(array &$events, ?string $start, ?string $end, array $payload): void
    {
        if ($start === null || $start === '') {
            return;
        }

        try {
            $from = Carbon::parse($start)->startOfDay();
            $to = Carbon::parse($end ?? $start)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        for ($date = $from->copy(); $date->lte($to) && $date->diffInDays($from) <= 60; $date->addDay()) {
            $key = $date->format('Y-m-d');
            $events[$key][] = $payload;
        }
    }
}
