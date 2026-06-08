<?php

namespace App\Modules\Programs\Services;

use App\Models\AbyipDocument;
use App\Models\AbyipProgram;
use App\Models\Committee;
use App\Models\User;
use App\Modules\Committees\Services\CommitteeService;
use Illuminate\Support\Collection;

class AbyipProgramCatalogService
{
    /** @var list<string> */
    private const YOUTH_PROGRAM_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    /**
     * @var array<string, array{committee_key: string, href: string, type: string}>
     */
    private const LETTER_META = [
        'A' => ['committee_key' => 'education', 'href' => '/scholar-list', 'type' => 'education'],
        'B' => ['committee_key' => 'environment', 'href' => '/environmental-survey-forms', 'type' => 'other'],
        'C' => ['committee_key' => 'disaster', 'href' => '/disaster-survey-forms', 'type' => 'other'],
        'D' => ['committee_key' => 'livelihood', 'href' => '/livelihood-survey-forms', 'type' => 'other'],
        'E' => ['committee_key' => 'health', 'href' => '/medicines-survey-forms', 'type' => 'other'],
        'F' => ['committee_key' => 'anti-drug', 'href' => '/antidrug-survey-forms', 'type' => 'other'],
        'G' => ['committee_key' => 'gender', 'href' => '/gender-survey-forms', 'type' => 'other'],
        'H' => ['committee_key' => 'feeding', 'href' => '/feeding-survey-forms', 'type' => 'other'],
        'I' => ['committee_key' => 'sports', 'href' => '/sports-application-form', 'type' => 'sports'],
        'J' => ['committee_key' => 'other', 'href' => '/others-survey-forms', 'type' => 'other'],
    ];

    public function __construct(private readonly CommitteeService $committeeService)
    {
    }

    public function getLatestAbyip(?int $barangayId): ?AbyipDocument
    {
        if ($barangayId === null) {
            return null;
        }

        return AbyipDocument::query()
            ->where('barangay_id', $barangayId)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return list<string>
     */
    public function listProgramNames(User $user): array
    {
        $abyip = $this->getLatestAbyip($user->barangay_id);

        if ($abyip === null) {
            return [];
        }

        return $this->youthProgramsQuery($abyip->id)
            ->pluck('program_name')
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{programs: list<array<string, mixed>>, calendar_year: int|null}
     */
    public function listForProgramsPage(User $user): array
    {
        $abyip = $this->getLatestAbyip($user->barangay_id);

        if ($abyip === null) {
            return [
                'programs' => [],
                'calendar_year' => null,
            ];
        }

        $heads = $this->committeeHeadsByProgramName($user);
        [$defaultStart, $defaultEnd] = $this->defaultDurationForYear((int) $abyip->fiscal_year);

        $programs = $this->youthProgramsQuery($abyip->id)
            ->get()
            ->map(fn (AbyipProgram $program) => $this->formatProgramForList(
                $program,
                $heads,
                $defaultStart,
                $defaultEnd,
            ))
            ->values()
            ->all();

        return [
            'programs' => $programs,
            'calendar_year' => (int) $abyip->fiscal_year,
        ];
    }

    /**
     * @return array{programs: list<array<string, mixed>>, calendar_year: int|null}
     */
    public function listForManagement(User $user): array
    {
        $abyip = $this->getLatestAbyip($user->barangay_id);

        if ($abyip === null) {
            return [
                'programs' => [],
                'calendar_year' => null,
            ];
        }

        $heads = $this->committeeHeadsByProgramName($user);

        $programs = $this->youthProgramsQuery($abyip->id)
            ->get()
            ->map(fn (AbyipProgram $program) => $this->formatProgramForManagement($program, $heads))
            ->values()
            ->all();

        return [
            'programs' => $programs,
            'calendar_year' => (int) $abyip->fiscal_year,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function committeeHeadsByProgramName(User $user): array
    {
        $map = [];

        $committees = Committee::query()
            ->whereHas('head', function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id);
            })
            ->with(['head.officialProfile'])
            ->get();

        foreach ($committees as $committee) {
            $key = mb_strtolower(trim((string) $committee->committee_name), 'UTF-8');

            if ($key === '' || $committee->head === null) {
                continue;
            }

            $map[$key] = $this->committeeService->buildOfficialFullName($committee->head);
        }

        return $map;
    }

    private function youthProgramsQuery(int $abyipId)
    {
        return AbyipProgram::query()
            ->where('abyip_id', $abyipId)
            ->where(function ($query) {
                $query->where('row_type', 'youth_program')
                    ->orWhereIn('code', self::YOUTH_PROGRAM_LETTERS);
            })
            ->with(['activities' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @param  array<string, string>  $heads
     * @return array<string, mixed>
     */
    private function formatProgramForList(
        AbyipProgram $program,
        array $heads,
        string $defaultStart,
        string $defaultEnd,
    ): array {
        $nameKey = mb_strtolower(trim((string) $program->program_name), 'UTF-8');
        $head = $heads[$nameKey] ?? null;

        return [
            'id' => $program->id,
            'title' => $program->program_name,
            'description' => $this->buildProgramDescription($program),
            'committee' => $program->program_name,
            'startDate' => $defaultStart,
            'endDate' => $defaultEnd,
            'status' => 'planned',
            'letter' => $program->program_letter,
            'sk_head' => $head,
            'sk_head_display' => $head ?? '',
        ];
    }

    /**
     * @param  array<string, string>  $heads
     * @return array<string, mixed>
     */
    private function formatProgramForManagement(AbyipProgram $program, array $heads): array
    {
        $letter = strtoupper(trim((string) ($program->program_letter ?? $program->code ?? '')));
        $meta = self::LETTER_META[$letter] ?? [
            'committee_key' => 'other',
            'href' => '#',
            'type' => 'other',
        ];

        $activities = $program->activities
            ->map(fn ($activity) => trim((string) $activity->activity_name))
            ->filter()
            ->values()
            ->all();

        $nameKey = mb_strtolower(trim((string) $program->program_name), 'UTF-8');
        $head = $heads[$nameKey] ?? null;

        return [
            'id' => $program->id,
            'letter' => $letter,
            'title' => $program->program_name,
            'activities' => $activities,
            'sk_head' => $head,
            'sk_head_display' => $head ?? '',
            'href' => $meta['href'],
            'committee_key' => $meta['committee_key'],
            'type' => $meta['type'],
        ];
    }

    private function buildProgramDescription(AbyipProgram $program): string
    {
        $description = trim((string) ($program->description ?? ''));
        if ($description !== '') {
            return $description;
        }

        $activities = $program->activities;
        if ($activities->isEmpty()) {
            return 'No activities listed.';
        }

        $names = $activities
            ->map(fn ($activity) => trim((string) $activity->activity_name))
            ->filter()
            ->values();

        return $names->isNotEmpty()
            ? 'Activities: '.$names->join(', ')
            : 'No activities listed.';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function defaultDurationForYear(int $year): array
    {
        $safeYear = $year > 0 ? $year : (int) date('Y');

        return [
            sprintf('%d-01-01', $safeYear),
            sprintf('%d-12-31', $safeYear),
        ];
    }
}
