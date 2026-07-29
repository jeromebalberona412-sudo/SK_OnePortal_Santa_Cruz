<?php

namespace App\Modules\Barangay_ABYIP\Services\Persistence;

use App\Models\Abyip;
use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;
use App\Modules\Barangay_ABYIP\Services\Parsing\AbyipBudgetExtractor;
use Illuminate\Support\Facades\Log;

class AbyipLineWriter
{
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
        $sortOrder = 0;

        foreach ($lineItems as $item) {
            if (($item['row_type'] ?? '') !== 'data' || ! $rowHasContent($item)) {
                continue;
            }

            if ($isNonProgramLineItem($item)) {
                continue;
            }

            $section = (string) ($item['program_section'] ?? '');
            if ($section === 'SK Youth Development and Empowerment Programs') {
                continue;
            }

            $this->createLineRow($document, $item, $sortOrder++, [
                'code' => $item['code'] ?? null,
                'row_type' => Abyip::ROW_EXPENDITURE,
            ]);
        }

        foreach ($youthPrograms as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            $name = trim((string) ($program['name'] ?? ''));

            if ($letter === '' || ! $isValidYouthProgramLetter($letter) || $name === '') {
                continue;
            }

            $meta = $program['_meta'] ?? [];
            $programRow = $this->createLineRow($document, array_merge($meta, [
                'ppa_name' => $name,
                'code' => $letter,
            ]), $sortOrder++, [
                'code' => $letter,
                'row_type' => Abyip::ROW_YOUTH_PROGRAM,
            ]);

            if ($programRow === null) {
                continue;
            }

            foreach ($program['activities'] ?? [] as $activity) {
                if (! $isValidYouthActivityRecord($activity)) {
                    continue;
                }

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
        }
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
        $programName = '';
        $activityName = null;

        if ($isActivity) {
            $activityName = trim((string) ($item['activity_name'] ?? $item['ppa_name'] ?? ''));
            $programName = $activityName;
        } else {
            $programName = trim((string) ($item['ppa_name'] ?? $item['program_name'] ?? ''));
        }

        if ($programName === '' && $activityName === null) {
            return null;
        }

        $budgets = $this->normalizer->normalizeBudgetFields([
            'budget_mooe' => $item['budget_mooe'] ?? null,
            'budget_co' => $item['budget_co'] ?? null,
            'budget_total' => $item['budget_total'] ?? $item['budget'] ?? null,
        ]);

        $linePayload = [
            'document_id' => $document->id,
            'tenant_id' => $document->tenant_id,
            'barangay_id' => $document->barangay_id,
            'created_by' => $document->created_by,
            'fiscal_year' => $document->fiscal_year,
            'row_type' => $rowType,
            'parent_id' => $defaults['parent_id'] ?? null,
            'code' => $defaults['code'] ?? ($item['code'] ?? null),
            'category' => $item['category'] ?? null,
            'program_name' => $programName,
            'activity_name' => $activityName,
            'description' => $item['description'] ?? null,
            'expected_result' => $item['expected_result'] ?? null,
            'performance_indicator' => $item['performance_indicator'] ?? null,
            'implementation_start' => $item['implementation_start'] ?? null,
            'implementation_end' => $item['implementation_end'] ?? null,
            'person_responsible' => $this->budgetExtractor->extractPersonResponsibleFromValue($item['person_responsible'] ?? null),
            'mooe' => $this->normalizer->numericAmount($budgets['budget_mooe']),
            'co' => $this->normalizer->numericAmount($budgets['budget_co']),
            'total' => $this->normalizer->numericAmount($budgets['budget_total']),
            'sort_order' => $sortOrder,
            'progress_percent' => $this->normalizer->numericAmount($item['progress_percent'] ?? 0),
            'accomplishment_status' => $item['accomplishment_status'] ?? 'Not Started',
        ];

        Log::info('ABYIP program insert payload', $linePayload);

        return Abyip::create($linePayload);
    }
}
