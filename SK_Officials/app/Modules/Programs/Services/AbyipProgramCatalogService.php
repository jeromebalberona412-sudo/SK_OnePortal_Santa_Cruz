<?php

namespace App\Modules\Programs\Services;

use App\Models\Abyip;
use App\Models\AbyipProgramDuration;
use App\Models\Committee;
use App\Models\User;
use App\Modules\Committees\Services\CommitteeService;
use Carbon\Carbon;

class AbyipProgramCatalogService
{
    /** @var list<string> */
    private const YOUTH_PROGRAM_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    public const STATUS_PLANNED = 'planned';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_COMPLETED = 'completed';

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

    public function __construct(private readonly CommitteeService $committeeService) {}

    public function getLatestAbyip(?int $barangayId): ?Abyip
    {
        return $this->getLatestApprovedAbyip($barangayId);
    }

    public function getLatestApprovedAbyip(?int $barangayId): ?Abyip
    {
        if ($barangayId === null) {
            return null;
        }

        return Abyip::query()
            ->documents()
            ->where('barangay_id', $barangayId)
            ->where('status', Abyip::STATUS_APPROVED)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('id')
            ->first();
    }

    public function hasApprovedAbyip(?int $barangayId): bool
    {
        return $this->getLatestApprovedAbyip($barangayId) !== null;
    }

    /**
     * @return array{
     *     status: 'approved'|'pending'|'rejected'|'none',
     *     has_approved: bool,
     *     fiscal_year: int|null,
     *     pending_message: string,
     *     no_abyip_message: string
     * }
     */
    public function resolveAccessGate(?int $barangayId): array
    {
        $pendingMessage = 'Pending — waiting for SK Federation President to approve your ABYIP.';
        $noAbyipMessage = 'No ABYIP document found. Upload your ABYIP first.';

        if ($barangayId === null) {
            return [
                'status' => 'none',
                'has_approved' => false,
                'fiscal_year' => null,
                'pending_message' => $pendingMessage,
                'no_abyip_message' => $noAbyipMessage,
            ];
        }

        if ($this->hasApprovedAbyip($barangayId)) {
            $approved = $this->getLatestApprovedAbyip($barangayId);

            return [
                'status' => 'approved',
                'has_approved' => true,
                'fiscal_year' => $approved?->fiscal_year !== null ? (int) $approved->fiscal_year : null,
                'pending_message' => $pendingMessage,
                'no_abyip_message' => $noAbyipMessage,
            ];
        }

        $latest = $this->getLatestAbyipDocument($barangayId);

        if ($latest === null) {
            return [
                'status' => 'none',
                'has_approved' => false,
                'fiscal_year' => null,
                'pending_message' => $pendingMessage,
                'no_abyip_message' => $noAbyipMessage,
            ];
        }

        $status = strtolower(trim((string) ($latest->status ?? Abyip::STATUS_PENDING)));

        return [
            'status' => in_array($status, ['pending', 'rejected'], true) ? $status : 'pending',
            'has_approved' => false,
            'fiscal_year' => $latest->fiscal_year !== null ? (int) $latest->fiscal_year : null,
            'pending_message' => $pendingMessage,
            'no_abyip_message' => $noAbyipMessage,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listApprovedProgramsForBarangay(int $barangayId): array
    {
        $abyip = $this->getLatestApprovedAbyip($barangayId);
        if ($abyip === null) {
            return [];
        }

        return $this->youthProgramsQuery($abyip->id)
            ->get()
            ->map(function (Abyip $program) {
                $activities = $program->children
                    ->map(fn ($activity) => trim((string) $activity->program_name))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'code' => strtoupper(trim((string) ($program->program_letter ?? $program->code ?? ''))),
                    'name' => $program->program_name,
                    'activities' => $activities,
                ];
            })
            ->filter(fn (array $program) => $program['code'] !== '')
            ->values()
            ->all();
    }

    /**
     * @deprecated Use getLatestApprovedAbyip() for downstream modules.
     */
    public function getLatestAbyipDocument(?int $barangayId): ?Abyip
    {
        if ($barangayId === null) {
            return null;
        }

        return Abyip::query()
            ->documents()
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
        $fiscalYear = (int) $abyip->fiscal_year;

        $programs = $this->youthProgramsQuery($abyip->id)
            ->get()
            ->map(fn (Abyip $program) => $this->formatProgramForList(
                $program,
                $heads,
                $user->barangay_id,
                $fiscalYear,
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
            ->map(fn (Abyip $program) => $this->formatProgramForManagement($program, $heads))
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

    private function youthProgramsQuery(int $documentId)
    {
        return Abyip::query()
            ->where('document_id', $documentId)
            ->where(function ($query) {
                $query->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                    ->orWhereIn('code', self::YOUTH_PROGRAM_LETTERS);
            })
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return array{start_date: string, end_date: string, startDate: string, endDate: string}
     */
    public function resolveProgramDuration(?int $barangayId, int $abyipProgramId, int $fiscalYear): array
    {
        [$defaultStart, $defaultEnd] = $this->defaultDurationForYear($fiscalYear);

        if ($barangayId === null) {
            return [
                'start_date' => $defaultStart,
                'end_date' => $defaultEnd,
                'startDate' => $defaultStart,
                'endDate' => $defaultEnd,
            ];
        }

        $stored = AbyipProgramDuration::query()
            ->where('barangay_id', $barangayId)
            ->where('abyip_program_id', $abyipProgramId)
            ->first();

        if ($stored === null) {
            return [
                'start_date' => $defaultStart,
                'end_date' => $defaultEnd,
                'startDate' => $defaultStart,
                'endDate' => $defaultEnd,
            ];
        }

        $start = $stored->start_date->toDateString();
        $end = $stored->end_date->toDateString();

        return [
            'start_date' => $start,
            'end_date' => $end,
            'startDate' => $start,
            'endDate' => $end,
        ];
    }

    /**
     * @return array{start_date: string, end_date: string, startDate: string, endDate: string, status: string}
     */
    public function upsertProgramDuration(
        int $barangayId,
        int $abyipProgramId,
        string $startDate,
        string $endDate,
    ): array {
        AbyipProgramDuration::query()->updateOrCreate(
            [
                'barangay_id' => $barangayId,
                'abyip_program_id' => $abyipProgramId,
            ],
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        );

        $status = $this->resolveProgramStatus($startDate, $endDate);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
        ];
    }

    public function resolveProgramStatus(string $startDate, string $endDate, ?Carbon $reference = null): string
    {
        $today = ($reference ?? now())->copy()->startOfDay();
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($today->lt($start)) {
            return self::STATUS_PLANNED;
        }

        if ($today->gt($end)) {
            return self::STATUS_COMPLETED;
        }

        return self::STATUS_ONGOING;
    }

    /**
     * @return array{id: int, program_name: string, letter: string, start_date: string, end_date: string}|null
     */
    public function findYouthProgramByLetter(?int $barangayId, string $letter): ?array
    {
        $document = $this->getLatestAbyip($barangayId);

        if ($document === null) {
            return null;
        }

        $program = $this->youthProgramsQuery($document->id)
            ->where('code', strtoupper(trim($letter)))
            ->first();

        if ($program === null) {
            return null;
        }

        $duration = $this->resolveProgramDuration(
            $barangayId,
            (int) $program->id,
            (int) $document->fiscal_year,
        );

        return [
            'id' => (int) $program->id,
            'program_name' => trim((string) $program->program_name),
            'letter' => strtoupper(trim((string) ($program->program_letter ?? $program->code ?? $letter))),
            'start_date' => $duration['start_date'],
            'end_date' => $duration['end_date'],
        ];
    }

    public function programBelongsToBarangay(?int $barangayId, int $programId): bool
    {
        $document = $this->getLatestAbyip($barangayId);

        if ($document === null) {
            return false;
        }

        return $this->youthProgramsQuery($document->id)
            ->whereKey($programId)
            ->exists();
    }

    /**
     * @param  array<string, string>  $heads
     * @return array<string, mixed>
     */
    private function formatProgramForList(
        Abyip $program,
        array $heads,
        ?int $barangayId,
        int $fiscalYear,
    ): array {
        $nameKey = mb_strtolower(trim((string) $program->program_name), 'UTF-8');
        $head = $heads[$nameKey] ?? null;
        $duration = $this->resolveProgramDuration($barangayId, (int) $program->id, $fiscalYear);

        return [
            'id' => $program->id,
            'title' => $program->program_name,
            'description' => $this->buildProgramDescription($program),
            'committee' => $program->program_name,
            'startDate' => $duration['startDate'],
            'endDate' => $duration['endDate'],
            'status' => $this->resolveProgramStatus($duration['startDate'], $duration['endDate']),
            'letter' => $program->program_letter,
            'sk_head' => $head,
            'sk_head_display' => $head ?? '',
        ];
    }

    /**
     * @param  array<string, string>  $heads
     * @return array<string, mixed>
     */
    private function formatProgramForManagement(Abyip $program, array $heads): array
    {
        $letter = strtoupper(trim((string) ($program->program_letter ?? $program->code ?? '')));
        $meta = self::LETTER_META[$letter] ?? [
            'committee_key' => 'other',
            'href' => '#',
            'type' => 'other',
        ];

        $activities = $program->children
            ->map(fn ($activity) => trim((string) $activity->program_name))
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

    private function buildProgramDescription(Abyip $program): string
    {
        $description = trim((string) ($program->description ?? ''));
        if ($description !== '') {
            return $description;
        }

        $activities = $program->children;
        if ($activities->isEmpty()) {
            return 'No activities listed.';
        }

        $names = $activities
            ->map(fn ($activity) => trim((string) $activity->program_name))
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
