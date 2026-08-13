<?php

namespace App\Modules\Barangay_ABYIP\Services\Persistence;

use App\Models\Abyip;
use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;
use App\Modules\Barangay_ABYIP\Services\Parsing\AbyipBudgetExtractor;
use Illuminate\Support\Facades\Log;

class AbyipLineWriter
{
    private ?int $skYouthProgramId = null;

    public function __construct(
        private readonly AbyipNumericNormalizer $normalizer,
        private readonly AbyipBudgetExtractor $budgetExtractor,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @param  list<array<string, mixed>>  $youthPrograms
     */
    public function syncLines(
        Abyip $document,
        array $lineItems,
        array $youthPrograms,
        callable $isNonProgramLineItem,
        callable $rowHasContent,
        callable $isValidYouthProgramLetter,
        callable $isValidYouthActivityRecord,
    ): void {
        $this->skYouthProgramId = null;
        $sortOrder = 0;

        $sortOrder = $this->syncExpenditureLines(
            $document,
            $lineItems,
            $sortOrder,
            $isNonProgramLineItem,
            $rowHasContent,
        );

        $this->syncYouthPrograms(
            $document,
            $youthPrograms,
            $sortOrder,
            $isValidYouthProgramLetter,
            $isValidYouthActivityRecord,
        );
    }

    /**
     * Writes the plain expenditure line items (everything outside the SK Youth
     * Development section). Returns the next available sort order so the
     * youth-program rows that follow continue the same sequence.
     *
     * @param  list<array<string, mixed>>  $lineItems
     */
    private function syncExpenditureLines(
        Abyip $document,
        array $lineItems,
        int $sortOrder,
        callable $isNonProgramLineItem,
        callable $rowHasContent,
    ): int {
        $categoryId = null;
        $programId = null;

        foreach ($lineItems as $item) {
            $rowType = (string) ($item['row_type'] ?? 'data');
            $section = (string) ($item['program_section'] ?? '');

            if (in_array($rowType, ['category', Abyip::ROW_CATEGORY], true)) {
                $isProgram = $this->isProgramHeadingItem($item);
                $categoryRow = $this->createLineRow($document, $item, $sortOrder++, [
                    'code' => $item['code'] ?? null,
                    'row_type' => Abyip::ROW_CATEGORY,
                    'parent_id' => $isProgram ? null : $programId,
                ]);

                if ($isProgram) {
                    $programId = $categoryRow?->id;
                    $categoryId = $programId;
                    if ($this->isYouthDevelopmentHeading($item)) {
                        $this->skYouthProgramId = $programId;
                    }
                } else {
                    $categoryId = $categoryRow?->id ?? $categoryId;
                }

                continue;
            }

            if ($section === 'SK Youth Development and Empowerment Programs') {
                continue;
            }

            if ($rowType !== 'data' && $rowType !== Abyip::ROW_EXPENDITURE) {
                continue;
            }

            if (! $rowHasContent($item)) {
                continue;
            }

            if ($isNonProgramLineItem($item)) {
                continue;
            }

            $this->createLineRow($document, $item, $sortOrder++, [
                'code' => $item['code'] ?? null,
                'row_type' => Abyip::ROW_EXPENDITURE,
                'parent_id' => $categoryId,
            ]);
        }

        return $sortOrder;
    }

    /**
     * Writes the SK Youth Development program rows and their nested activity
     * rows, continuing the sort order handed in from the expenditure lines.
     *
     * @param  list<array<string, mixed>>  $youthPrograms
     */
    private function syncYouthPrograms(
        Abyip $document,
        array $youthPrograms,
        int $sortOrder,
        callable $isValidYouthProgramLetter,
        callable $isValidYouthActivityRecord,
    ): void {
        foreach ($youthPrograms as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            $name = trim((string) ($program['name'] ?? ''));

            if ($letter === '' || ! $isValidYouthProgramLetter($letter) || $name === '') {
                continue;
            }

            $meta = $program['_meta'] ?? [];
            $parentProgram = trim((string) ($program['parent_program'] ?? $meta['parent_program'] ?? ''));
            $sectionName = $this->youthSectionName($letter, $name);
            $programRow = $this->createLineRow($document, array_merge($meta, [
                'ppa_name' => $sectionName,
                'program_name' => $sectionName,
                'category' => $parentProgram !== '' ? $parentProgram : ($meta['category'] ?? null),
                'code' => $letter,
            ]), $sortOrder++, [
                'code' => $letter,
                'row_type' => Abyip::ROW_YOUTH_PROGRAM,
                'parent_id' => $this->skYouthProgramId,
            ]);

            if ($programRow === null) {
                continue;
            }

            $sortOrder = $this->syncYouthActivities(
                $document,
                $program['activities'] ?? [],
                $programRow,
                $sortOrder,
                $isValidYouthActivityRecord,
            );
        }
    }

    /**
     * Writes the activity rows nested under a single youth program row.
     *
     * @param  list<array<string, mixed>>  $activities
     */
    private function syncYouthActivities(
        Abyip $document,
        array $activities,
        Abyip $programRow,
        int $sortOrder,
        callable $isValidYouthActivityRecord,
    ): int {
        foreach ($activities as $activity) {
            if (! $isValidYouthActivityRecord($activity)) {
                continue;
            }

            $activity['program_name'] = $activity['program_name']
                ?? $programRow->category
                ?? $programRow->program_name;
            $activity['category'] = $activity['category'] ?? $programRow->program_name;
            $activity['activity_name'] = $activity['activity_name'] ?? $activity['ppa_name'] ?? null;

            $this->createLineRow(
                $document,
                $activity,
                $sortOrder++,
                [
                    'row_type' => Abyip::ROW_ACTIVITY,
                    'parent_id' => $programRow->id,
                ]
            );
        }

        return $sortOrder;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $defaults
     */
    public function createLineRow(
        Abyip $document,
        array $item,
        int $sortOrder,
        array $defaults = []
    ): ?Abyip {
        $rowType = (string) ($defaults['row_type'] ?? Abyip::ROW_EXPENDITURE);
        $isActivity = $rowType === Abyip::ROW_ACTIVITY;

        [$programName, $activityName] = $this->resolveProgramName($item, $isActivity, $rowType);

        if ($programName === '') {
            return null;
        }

        $linePayload = $this->buildPayload($document, $item, $defaults, [
            'row_type' => $rowType,
            'program_name' => $programName,
            'activity_name' => $activityName,
            'sort_order' => $sortOrder,
        ]);

        Log::info('ABYIP program insert payload', $linePayload);

        return Abyip::create($linePayload);
    }

    /**
     * Maps Excel-style columns onto abyip rows:
     * program → program_name, category_section → category, activity_ppa → activity_name.
     *
     * @param  array<string, mixed>  $item
     * @return array{0: string, 1: ?string} [$programName, $activityName]
     */
    private function resolveProgramName(array $item, bool $isActivity, string $rowType = ''): array
    {
        if ($isActivity || $rowType === Abyip::ROW_EXPENDITURE) {
            $activityName = trim((string) ($item['activity_name'] ?? $item['ppa_name'] ?? ''));
            $programName = trim((string) ($item['program_name'] ?? ''));

            return [$programName !== '' ? $programName : $activityName, $activityName !== '' ? $activityName : null];
        }

        $programName = trim((string) ($item['program_name'] ?? $item['ppa_name'] ?? ''));
        $activityName = trim((string) ($item['activity_name'] ?? ''));

        return [$programName, $activityName !== '' ? $activityName : null];
    }

    private function youthSectionName(string $letter, string $name): string
    {
        $name = trim($name);
        $letter = strtoupper(trim($letter));
        if ($name === '' || $name === $letter) {
            return $letter;
        }

        if (preg_match('/^[A-J]\.\s+/i', $name) === 1) {
            return $name;
        }

        return $letter.'. '.$name;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isProgramHeadingItem(array $item): bool
    {
        $level = strtolower((string) ($item['hierarchy_level'] ?? ''));
        if ($level === 'program') {
            return true;
        }
        if ($level === 'category') {
            return false;
        }

        return $this->isYouthDevelopmentHeading($item)
            || (bool) preg_match('/GENERAL ADMINISTRATION PROGRAM/i', $this->headingName($item));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isYouthDevelopmentHeading(array $item): bool
    {
        return (bool) preg_match('/SK YOUTH DEVELOPMENT AND EMPOWERMENT/i', $this->headingName($item));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function headingName(array $item): string
    {
        return trim((string) ($item['ppa_name'] ?? $item['program_name'] ?? $item['category'] ?? ''));
    }

    /**
     * Assembles the full Abyip::create() payload for a single line row.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $defaults
     * @param  array{row_type: string, program_name: string, activity_name: ?string, sort_order: int}  $resolved
     * @return array<string, mixed>
     */
    private function buildPayload(Abyip $document, array $item, array $defaults, array $resolved): array
    {
        $budgets = $this->normalizeBudget($item);

        return [
            'document_id' => $document->id,
            'tenant_id' => $document->tenant_id,
            'barangay_id' => $document->barangay_id,
            'created_by' => $document->created_by,
            'fiscal_year' => $document->fiscal_year,
            'row_type' => $resolved['row_type'],
            'parent_id' => $defaults['parent_id'] ?? null,
            'code' => $defaults['code'] ?? ($item['code'] ?? null),
            'category' => $this->nullableText($item['category'] ?? null),
            'program_name' => $resolved['program_name'],
            'activity_name' => $this->nullableText($resolved['activity_name']),
            'description' => $this->nullableText($item['description'] ?? null),
            'expected_result' => $this->nullableText($item['expected_result'] ?? null),
            'performance_indicator' => $this->nullableText($item['performance_indicator'] ?? null),
            'implementation_start' => $item['implementation_start'] ?? $item['period_start'] ?? null,
            'implementation_end' => $item['implementation_end'] ?? $item['period_end'] ?? null,
            'person_responsible' => $this->nullableText(
                $this->normalizePersonResponsible($item['person_responsible'] ?? null)
            ),
            'mooe' => $this->normalizer->numericAmountOrNull($budgets['budget_mooe']),
            'co' => $this->normalizer->numericAmountOrNull($budgets['budget_co']),
            'total' => $this->normalizer->numericAmountOrNull($budgets['budget_total']),
            'sort_order' => $resolved['sort_order'],
            'progress_percent' => $item['progress_percent'] ?? null,
            'accomplishment_status' => $this->nullableText($item['accomplishment_status'] ?? null),
            'target_date' => $item['target_date'] ?? null,
            'completed_at' => $item['completed_at'] ?? null,
            'submitted_at' => $item['submitted_at'] ?? null,
            'approved_at' => $item['approved_at'] ?? null,
            'rejected_at' => $item['rejected_at'] ?? null,
            'source_text' => $this->nullableText($item['source_text'] ?? $item['SOURCE'] ?? null),
            'page_number' => $item['page_number'] ?? $item['PAGE'] ?? null,
            'extraction_confidence' => $item['extraction_confidence'] ?? null,
            'extraction_status' => $item['extraction_status'] ?? 'extracted',
            'manual_review_required' => filter_var($item['manual_review_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'validation_status' => $this->nullableText($item['validation_status'] ?? null),
            'validation_message' => $this->nullableText($item['validation_message'] ?? null),
        ];
    }

    /**
     * Normalizes the mooe/co/total budget fields for a row via the shared
     * numeric normalizer, honoring the legacy 'budget' fallback key.
     *
     * @param  array<string, mixed>  $item
     * @return array{budget_mooe: string, budget_co: string, budget_total: string}
     */
    private function normalizeBudget(array $item): array
    {
        if (! empty($item['grouped_budget'])) {
            return [
                'budget_mooe' => null,
                'budget_co' => null,
                'budget_total' => null,
            ];
        }

        return $this->normalizer->normalizeBudgetFields([
            'budget_mooe' => $item['budget_mooe'] ?? null,
            'budget_co' => $item['budget_co'] ?? null,
            'budget_total' => $item['budget_total'] ?? $item['budget'] ?? null,
        ]);
    }

    /**
     * Normalizes the free-text "person responsible" value via the shared
     * budget extractor (strips labels/noise picked up during PDF parsing).
     */
    private function normalizePersonResponsible(mixed $value): ?string
    {
        return $this->budgetExtractor->extractPersonResponsibleFromValue($value);
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || in_array($text, ['-', '—', '–', 'n/a', 'N/A', 'NA', 'none'], true)) {
            return null;
        }

        return $text;
    }
}
