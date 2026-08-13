<?php

namespace App\Modules\ABYIP\Services;

use App\Models\Abyip;
use App\Models\OfficialProfile;
use App\Models\User;
use App\Services\AbyipPdfExtractionService;
use App\Services\SkFederationsNotificationDispatcher;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AbyipService
{
    public function __construct(private readonly AbyipPdfExtractionService $pdfExtractionService) {}

    /** @var list<string> */
    private const YOUTH_PROGRAM_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForBarangay(User $user): Collection
    {
        return Abyip::query()
            ->documents()
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Abyip $document) => $this->formatDocument($document, forList: true));
    }

    /**
     * @return Collection<int, int>
     */
    public function distinctYearsForBarangay(User $user): Collection
    {
        return Abyip::query()
            ->documents()
            ->where('barangay_id', $user->barangay_id)
            ->distinct()
            ->orderByDesc('fiscal_year')
            ->pluck('fiscal_year');
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $documentId): array
    {
        $document = $this->findDocumentModel($user, $documentId);

        return $this->formatDocument($document->load(['lines.children']));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function store(User $user, array $data): array
    {
        $sourceType = (string) ($data['source_type'] ?? Abyip::SOURCE_WORD);
        $extractedText = $this->resolveExtractedText($data);
        $parsed = $sourceType === Abyip::SOURCE_PDF
            ? $this->parseUploadedDocument(
                documentHtml: '',
                extractedText: $extractedText
            )
            : $this->parseUploadedDocument(
                documentHtml: (string) ($data['document_html'] ?? ''),
                extractedText: ''
            );

        $fiscalYear = (int) ($data['calendar_year'] ?? now()->year);
        $this->assertUniqueYear($user, $fiscalYear);

        $signatureUserIds = $this->resolveSignatureUserIds($user->barangay_id, $parsed);
        $parsed = $this->normalizeDocumentForInsert($parsed);

        $document = DB::transaction(function () use ($user, $data, $fiscalYear, $sourceType, $parsed, $signatureUserIds) {
            $documentPayload = [
                'row_type' => Abyip::ROW_DOCUMENT,
                'tenant_id' => $user->tenant_id,
                'barangay_id' => $user->barangay_id,
                'created_by' => $user->id,
                'fiscal_year' => $fiscalYear,
                'country' => $parsed['country'] ?? 'Republic of the Philippines',
                'region' => $parsed['region'],
                'province' => $parsed['province'],
                'municipality' => $parsed['municipality'],
                'barangay_name' => $parsed['barangay_name'],
                'document_title' => trim((string) ($parsed['document_title'] ?? $data['title'] ?? 'ABYIP CY '.$fiscalYear)),
                'sk_council_name' => $parsed['sk_council_name'],
                'barangay_estimated_budget' => $parsed['barangay_estimated_budget'],
                'sk_fund_percentage' => $parsed['sk_fund_percentage'],
                'sk_fund_amount' => $parsed['sk_fund_amount'],
                'total_budget' => $parsed['total_budget'],
                'prepared_by' => $parsed['prepared_by'],
                'prepared_position' => $parsed['prepared_position'],
                'prepared_by_user_id' => $signatureUserIds['prepared_by_user_id'],
                'approved_by' => $parsed['approved_by'],
                'approved_position' => $parsed['approved_position'],
                'approved_by_user_id' => $signatureUserIds['approved_by_user_id'],
                'status' => Abyip::STATUS_PENDING,
                'prepared_by_name' => $parsed['prepared_by_name'] ?? $parsed['prepared_by'],
                'prepared_by_position' => $parsed['prepared_by_position'],
                'approved_by_name' => $parsed['approved_by_name'] ?? $parsed['approved_by'],
                'approved_by_position' => $parsed['approved_by_position'],
                'source_type' => $sourceType,
                'document_html' => $data['document_html'] ?? null,
                'pdf_data' => $data['pdf_data'] ?? null,
            ];

            Log::info('ABYIP document insert payload', $documentPayload);

            $document = Abyip::create($documentPayload);

            $document->update(['document_id' => $document->id]);

            $this->syncAbyipLines(
                $document,
                $parsed['line_items'] ?? [],
                $parsed['sk_youth_development_and_empowerment_programs'] ?? []
            );

            return $document;
        });

        app(SkFederationsNotificationDispatcher::class)->notifyAbyipSubmission(
            (string) ($document->barangay_name ?? 'Unknown'),
            $fiscalYear,
        );

        return $this->formatDocument($document->fresh(['lines.children']));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, int $documentId, array $data): array
    {
        $this->findDocumentModel($user, $documentId);

        throw ValidationException::withMessages([
            'document' => ['Uploaded ABYIP documents are view-only.'],
        ]);
    }

    public function delete(User $user, int $documentId): void
    {
        $document = $this->findDocumentModel($user, $documentId);

        if ((string) ($document->status ?? '') === Abyip::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'document' => ['Approved ABYIP records cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($document) {
            Abyip::query()
                ->where('document_id', $document->id)
                ->where('id', '!=', $document->id)
                ->delete();

            $document->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function resubmit(User $user, int $documentId, array $data): array
    {
        $document = $this->findDocumentModel($user, $documentId);

        if ((string) ($document->status ?? '') !== Abyip::STATUS_REJECTED) {
            throw ValidationException::withMessages([
                'document' => ['Only rejected ABYIP records can be resubmitted.'],
            ]);
        }

        $sourceType = (string) ($data['source_type'] ?? Abyip::SOURCE_PDF);

        if ($sourceType === Abyip::SOURCE_PDF && empty($data['pdf_data'])) {
            throw ValidationException::withMessages([
                'pdf_data' => ['PDF data is required for resubmission.'],
            ]);
        }

        if ($sourceType === Abyip::SOURCE_WORD && empty($data['document_html'])) {
            throw ValidationException::withMessages([
                'document_html' => ['Document content is required for resubmission.'],
            ]);
        }

        $extractedText = $this->resolveExtractedText($data);
        $parsed = $sourceType === Abyip::SOURCE_PDF
            ? $this->parseUploadedDocument(
                documentHtml: '',
                extractedText: $extractedText
            )
            : $this->parseUploadedDocument(
                documentHtml: (string) ($data['document_html'] ?? ''),
                extractedText: ''
            );

        $signatureUserIds = $this->resolveSignatureUserIds($user->barangay_id, $parsed);
        $parsed = $this->normalizeDocumentForInsert($parsed);

        $document = DB::transaction(function () use ($document, $data, $sourceType, $parsed, $signatureUserIds) {
            Abyip::query()
                ->where('document_id', $document->id)
                ->where('id', '!=', $document->id)
                ->delete();

            $documentPayload = [
                'country' => $parsed['country'] ?? 'Republic of the Philippines',
                'region' => $parsed['region'],
                'province' => $parsed['province'],
                'municipality' => $parsed['municipality'],
                'barangay_name' => $parsed['barangay_name'],
                'document_title' => trim((string) ($parsed['document_title'] ?? $data['title'] ?? $document->document_title)),
                'sk_council_name' => $parsed['sk_council_name'],
                'barangay_estimated_budget' => $parsed['barangay_estimated_budget'],
                'sk_fund_percentage' => $parsed['sk_fund_percentage'],
                'sk_fund_amount' => $parsed['sk_fund_amount'],
                'total_budget' => $parsed['total_budget'],
                'prepared_by' => $parsed['prepared_by'],
                'prepared_position' => $parsed['prepared_position'],
                'prepared_by_user_id' => $signatureUserIds['prepared_by_user_id'],
                'approved_by' => $parsed['approved_by'],
                'approved_position' => $parsed['approved_position'],
                'approved_by_user_id' => $signatureUserIds['approved_by_user_id'],
                'prepared_by_name' => $parsed['prepared_by_name'] ?? $parsed['prepared_by'],
                'prepared_by_position' => $parsed['prepared_by_position'],
                'approved_by_name' => $parsed['approved_by_name'] ?? $parsed['approved_by'],
                'approved_by_position' => $parsed['approved_by_position'],
                'source_type' => $sourceType,
                'document_html' => $data['document_html'] ?? null,
                'pdf_data' => $data['pdf_data'] ?? null,
                'status' => Abyip::STATUS_PENDING,
                'reviewed_at' => null,
                'reviewed_by_user_id' => null,
                'rejection_reason' => null,
            ];

            Log::info('ABYIP document resubmit payload', $documentPayload);

            $document->forceFill($documentPayload)->save();

            $this->syncAbyipLines(
                $document,
                $parsed['line_items'] ?? [],
                $parsed['sk_youth_development_and_empowerment_programs'] ?? []
            );

            return $document;
        });

        app(SkFederationsNotificationDispatcher::class)->notifyAbyipSubmission(
            (string) ($document->barangay_name ?? 'Unknown'),
            (int) $document->fiscal_year,
        );

        return $this->formatDocument($document->fresh(['lines.children']));
    }

    protected function findDocumentModel(User $user, int $documentId): Abyip
    {
        $document = Abyip::query()
            ->documents()
            ->where('id', $documentId)
            ->where('barangay_id', $user->barangay_id)
            ->first();

        if ($document === null) {
            throw ValidationException::withMessages([
                'document' => ['ABYIP record not found.'],
            ]);
        }

        return $document;
    }

    protected function assertUniqueYear(User $user, int $fiscalYear): void
    {
        $exists = Abyip::query()
            ->documents()
            ->where('barangay_id', $user->barangay_id)
            ->where('fiscal_year', $fiscalYear)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'calendar_year' => ['An ABYIP record already exists for this calendar year. Delete it first before uploading a new one.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatDocument(Abyip $document, bool $forList = false): array
    {
        $programs = [];

        if ($document->relationLoaded('lines')) {
            foreach ($document->lines as $line) {
                if (in_array($line->row_type, [Abyip::ROW_EXPENDITURE, Abyip::ROW_YOUTH_PROGRAM], true)) {
                    $programs[] = $this->formatLineAsProgram($line);
                }
            }
        }

        return [
            'id' => $document->id,
            'title' => $document->document_title,
            'date_created' => $document->created_at?->toIso8601String(),
            'calendar_year' => $document->fiscal_year,
            'fiscal_year' => $document->fiscal_year,
            'country' => $document->country,
            'region' => $document->region,
            'province' => $document->province,
            'municipality' => $document->municipality,
            'barangay_name' => $document->barangay_name,
            'document_title' => $document->document_title,
            'source_type' => $document->source_type,
            'document_html' => $forList ? null : $document->document_html,
            'pdf_data' => $forList ? null : $document->pdf_data,
            'barangay_estimated_budget' => $document->barangay_estimated_budget,
            'sk_fund_percentage' => $document->sk_fund_percentage,
            'sk_fund_amount' => $document->sk_fund_amount,
            'status' => $document->status ?? Abyip::STATUS_PENDING,
            'rejection_reason' => $document->rejection_reason,
            'reviewed_at' => $document->reviewed_at?->toIso8601String(),
            'total_expenditure' => $document->total_budget,
            'total_budget' => $document->total_budget,
            'prepared_by_name' => $document->prepared_by_name ?? $document->prepared_by,
            'prepared_by' => $document->prepared_by,
            'prepared_position' => $document->prepared_position,
            'prepared_by_position' => $document->prepared_by_position ?? $document->prepared_position,
            'prepared_by_user_id' => $document->prepared_by_user_id,
            'approved_by_name' => $document->approved_by_name ?? $document->approved_by,
            'approved_by' => $document->approved_by,
            'approved_position' => $document->approved_position,
            'approved_by_position' => $document->approved_by_position ?? $document->approved_position,
            'approved_by_user_id' => $document->approved_by_user_id,
            'programs' => $programs,
            'line_items' => [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @param  list<array<string, mixed>>  $youthPrograms
     */
    protected function syncAbyipLines(
        Abyip $document,
        array $lineItems,
        array $youthPrograms
    ): void {
        $sortOrder = 0;

        foreach ($lineItems as $item) {
            if (($item['row_type'] ?? '') !== 'data' || ! $this->rowHasContent($item)) {
                continue;
            }

            if ($this->isNonProgramLineItem($item)) {
                continue;
            }

            $section = (string) ($item['program_section'] ?? '');
            if ($section === 'SK Youth Development and Empowerment Programs') {
                continue;
            }

            $this->createAbyipLineRow($document, $item, $sortOrder++, [
                'code' => $item['code'] ?? null,
                'row_type' => Abyip::ROW_EXPENDITURE,
            ]);
        }

        foreach ($youthPrograms as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            $name = trim((string) ($program['name'] ?? $this->stripProgramLetterPrefix((string) ($program['label'] ?? ''))));

            if ($letter === '' || ! $this->isValidYouthProgramLetter($letter) || $name === '') {
                continue;
            }

            $meta = $program['_meta'] ?? [];
            $programRow = $this->createAbyipLineRow($document, array_merge($meta, [
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
                if (! $this->isValidYouthActivityRecord($activity)) {
                    continue;
                }

                $this->createAbyipLineRow(
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
    protected function createAbyipLineRow(
        Abyip $document,
        array $item,
        int $sortOrder,
        array $defaults = []
    ): ?Abyip {
        $rowType = (string) ($defaults['row_type'] ?? Abyip::ROW_EXPENDITURE);

        // For activities, use activity_name field; for programs, use ppa_name or program_name
        $isActivity = $rowType === Abyip::ROW_ACTIVITY;
        $programName = '';
        $activityName = null;

        if ($isActivity) {
            $activityName = trim((string) ($item['activity_name'] ?? $item['ppa_name'] ?? ''));
            $programName = $activityName; // Fallback for program_name field
        } else {
            $programName = trim((string) ($item['ppa_name'] ?? $item['program_name'] ?? ''));
        }

        if ($programName === '' && $activityName === null) {
            return null;
        }

        $budgets = $this->normalizeBudgetFields([
            'budget_mooe' => $item['budget_mooe'] ?? null,
            'budget_co' => $item['budget_co'] ?? null,
            'budget_total' => $item['budget_total'] ?? $item['budget'] ?? null,
        ]);

        $mooe = $this->numericAmount($budgets['budget_mooe']);
        $co = $this->numericAmount($budgets['budget_co']);
        $total = $this->numericAmount($budgets['budget_total']);

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
            'person_responsible' => $this->extractPersonResponsibleFromValue($item['person_responsible'] ?? null),
            'mooe' => $mooe,
            'co' => $co,
            'total' => $total,
            'sort_order' => $sortOrder,
            'progress_percent' => $this->numericAmount($item['progress_percent'] ?? 0),
            'source_text' => $item['source_text'] ?? null,
            'page_number' => $item['page_number'] ?? null,
        ];

        Log::info('ABYIP program insert payload', $linePayload);

        return Abyip::create($linePayload);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatLineAsProgram(Abyip $line): array
    {
        return [
            'id' => $line->id,
            'code' => $line->code,
            'program_letter' => $line->program_letter,
            'category' => $line->category,
            'program_name' => $line->program_name,
            'description' => $line->description,
            'expected_result' => $line->expected_result,
            'performance_indicator' => $line->performance_indicator,
            'implementation_start' => $line->implementation_start?->format('Y-m-d'),
            'implementation_end' => $line->implementation_end?->format('Y-m-d'),
            'person_responsible' => $line->person_responsible,
            'row_type' => $line->row_type,
            'mooe' => $line->mooe,
            'co' => $line->co,
            'total' => $line->total,
            'activities' => $line->relationLoaded('children')
                ? $line->children->map(fn (Abyip $activity) => $this->formatLineAsActivity($activity))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatLineAsActivity(Abyip $activity): array
    {
        return [
            'id' => $activity->id,
            'program_id' => $activity->parent_id,
            'activity_name' => $activity->activity_name ?? $activity->program_name,
            'description' => $activity->description,
            'expected_result' => $activity->expected_result,
            'performance_indicator' => $activity->performance_indicator,
            'implementation_start' => $activity->implementation_start?->format('Y-m-d'),
            'implementation_end' => $activity->implementation_end?->format('Y-m-d'),
            'person_responsible' => $activity->person_responsible,
            'mooe' => $activity->mooe,
            'co' => $activity->co,
            'total' => $activity->total,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{budget_mooe: string, budget_co: string, budget_total: string}
     */
    protected function normalizeBudgetFields(array $row): array
    {
        $mooe = $this->parseAmount($row['budget_mooe'] ?? null);
        $co = $this->parseAmount($row['budget_co'] ?? null);
        $total = $this->parseAmount($row['budget_total'] ?? null);

        if ($total === null && $mooe !== null && $co !== null) {
            $total = (string) round((float) $mooe + (float) $co, 2);
        } elseif ($total === null && $mooe !== null) {
            $total = $mooe;
        }

        return [
            'budget_mooe' => $this->numericAmount($mooe),
            'budget_co' => $this->numericAmount($co),
            'budget_total' => $this->numericAmount($total),
        ];
    }

    /**
     * @return array{
     *     region: ?string,
     *     province: ?string,
     *     municipality: ?string,
     *     sk_council_name: ?string,
     *     barangay_estimated_budget: ?string,
     *     sk_fund_amount: ?string,
     *     total_expenditure: ?string,
     *     prepared_by_name: ?string,
     *     prepared_by_position: ?string,
     *     approved_by_name: ?string,
     *     approved_by_position: ?string,
     *     line_items: list<array<string, mixed>>,
     *     sk_youth_development_and_empowerment_programs: list<array<string, mixed>>
     * }
     */
    protected function parseUploadedDocument(string $documentHtml, string $extractedText): array
    {
        if (trim($documentHtml) !== '') {
            $parsed = $this->parseDocumentHtml($documentHtml);
        } else {
            $parsed = $this->parseExtractedPdfText($extractedText);
        }

        if (trim($extractedText) !== '') {
            foreach ($this->parseHeaderMetadataFromText($extractedText) as $key => $value) {
                if ($value !== null && $value !== '') {
                    $parsed[$key] = $this->preferBudgetAmount($parsed[$key] ?? null, $value, $key);
                }
            }

            foreach ($this->parseAbyipHeaderTagsFromText($extractedText) as $key => $value) {
                if ($value !== null && $value !== '') {
                    $parsed[$key] = $this->preferBudgetAmount($parsed[$key] ?? null, $value, $key);
                }
            }

            foreach ($this->parseAbyipSignatureTagsFromText($extractedText) as $key => $value) {
                if ($value !== null && $value !== '') {
                    $parsed[$key] = $value;
                }
            }

            $grandTotal = $this->parseAbyipGrandTotalFromText($extractedText);
            if ($grandTotal !== null) {
                $parsed['total_budget'] = $this->preferBudgetAmount($parsed['total_budget'] ?? null, $grandTotal, 'total_budget');
            }
        }

        $parsed = $this->normalizeSignatureFields($parsed);
        $parsed = $this->normalizeDocumentBudgets($parsed);

        $parsed['line_items'] = array_values(array_filter(
            $parsed['line_items'] ?? [],
            fn (array $item) => ! $this->isNonProgramLineItem($item)
        ));

        if (empty($parsed['total_budget']) && ! empty($parsed['line_items'])) {
            $parsed['total_budget'] = $this->extractTotalFromRows($parsed['line_items']);
        }

        $parsed['sk_youth_development_and_empowerment_programs'] = $this->finalizeYouthPrograms(
            $parsed['sk_youth_development_and_empowerment_programs'] ?? []
        );

        return $parsed;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseExtractedPdfText(string $extractedText): array
    {
        $parsed = $this->emptyParsedMetadata();

        if (trim($extractedText) === '') {
            return $parsed;
        }

        $generalStructured = $this->parseStructuredAbyipRowsFromText($extractedText);
        $youthStructured = $this->parseStructuredYouthRowsFromText($extractedText);
        $lineItems = $this->parseLineItemsFromText($extractedText);

        $generalFromLines = array_values(array_filter(
            $lineItems,
            fn (array $item) => ($item['row_type'] ?? '') === 'data'
                && ($item['program_section'] ?? '') !== 'SK Youth Development and Empowerment Programs'
                && $this->rowHasContent($item)
        ));

        $parsed['line_items'] = array_values(array_filter(
            $generalStructured !== [] ? $generalStructured : $generalFromLines,
            fn (array $item) => ! $this->isNonProgramLineItem($item)
        ));

        if ($generalStructured !== []) {
            $parsed['line_items'] = $this->supplementStructuredRowsFromRawText($parsed['line_items'], $extractedText);
        }

        $youthFromLines = $this->buildYouthProgramsFromLineItems($lineItems);
        if ($youthFromLines === []) {
            $youthFromLines = $this->parseYouthProgramBlocksFromText($extractedText);
        }

        $parsed['sk_youth_development_and_empowerment_programs'] = $this->mergeYouthProgramLists(
            $youthStructured,
            $youthFromLines
        );

        return $parsed;
    }

    /**
     * @param  list<array<string, mixed>>  $primary
     * @param  list<array<string, mixed>>  $secondary
     * @return list<array<string, mixed>>
     */
    protected function mergeYouthProgramLists(array $primary, array $secondary): array
    {
        $merged = [];

        foreach (array_merge($primary, $secondary) as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            if (! $this->isValidYouthProgramLetter($letter)) {
                continue;
            }

            if (! isset($merged[$letter])) {
                $merged[$letter] = $program;

                continue;
            }

            $existingActivities = $merged[$letter]['activities'] ?? [];
            $incomingActivities = $program['activities'] ?? [];
            if (count($incomingActivities) > count($existingActivities)) {
                $merged[$letter]['activities'] = $incomingActivities;
            }

            $existingMeta = $merged[$letter]['_meta'] ?? [];
            $incomingMeta = $program['_meta'] ?? [];
            $merged[$letter]['_meta'] = $this->mergeStructuredRowFields($existingMeta, $incomingMeta);
        }

        ksort($merged);

        return array_values($merged);
    }

    /**
     * @return array{
     *     region: ?string,
     *     province: ?string,
     *     municipality: ?string,
     *     sk_council_name: ?string,
     *     barangay_estimated_budget: ?string,
     *     sk_fund_amount: ?string,
     *     total_expenditure: ?string,
     *     prepared_by_name: ?string,
     *     prepared_by_position: ?string,
     *     approved_by_name: ?string,
     *     approved_by_position: ?string,
     *     line_items: list<array<string, mixed>>,
     *     sk_youth_development_and_empowerment_programs: list<array<string, mixed>>
     * }
     */
    protected function emptyParsedMetadata(): array
    {
        return [
            'fiscal_year' => null,
            'country' => null,
            'region' => null,
            'province' => null,
            'municipality' => null,
            'barangay_name' => null,
            'document_title' => null,
            'sk_council_name' => null,
            'barangay_estimated_budget' => null,
            'sk_fund_percentage' => null,
            'sk_fund_amount' => null,
            'total_budget' => null,
            'prepared_by' => null,
            'prepared_position' => null,
            'prepared_by_name' => null,
            'prepared_by_position' => null,
            'approved_by' => null,
            'approved_position' => null,
            'approved_by_name' => null,
            'approved_by_position' => null,
            'line_items' => [],
            'sk_youth_development_and_empowerment_programs' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseAbyipHeaderTagsFromText(string $text): array
    {
        $result = [
            'barangay_estimated_budget' => null,
            'sk_fund_percentage' => null,
            'sk_fund_amount' => null,
        ];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            if (! str_starts_with($line, '@ABYIP_HEADER@')) {
                continue;
            }

            $fields = $this->parseStructuredTagFields($line, '@ABYIP_HEADER@');

            if (! empty($fields['BARANGAY_BUDGET'])) {
                $result['barangay_estimated_budget'] = $this->parseAmount($fields['BARANGAY_BUDGET']);
            }

            if (! empty($fields['SK_FUND_PERCENT'])) {
                $result['sk_fund_percentage'] = $this->parseAmount($fields['SK_FUND_PERCENT']);
            }

            if (! empty($fields['SK_FUND_AMOUNT'])) {
                $result['sk_fund_amount'] = $this->parseAmount($fields['SK_FUND_AMOUNT']);
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseAbyipSignatureTagsFromText(string $text): array
    {
        $result = [
            'prepared_by_name' => null,
            'prepared_by_position' => null,
            'approved_by_name' => null,
            'approved_by_position' => null,
        ];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            if (! str_starts_with(trim($line), '@ABYIP_SIGNATURE@')) {
                continue;
            }

            $fields = $this->parseStructuredTagFields($line, '@ABYIP_SIGNATURE@');

            if (! empty($fields['PREPARED_NAME'])) {
                $result['prepared_by_name'] = $this->formatHonoraryName($fields['PREPARED_NAME']);
            }

            if (! empty($fields['PREPARED_POS'])) {
                $result['prepared_by_position'] = trim($fields['PREPARED_POS']);
            }

            if (! empty($fields['APPROVED_NAME'])) {
                $result['approved_by_name'] = $this->formatHonoraryName($fields['APPROVED_NAME']);
            }

            if (! empty($fields['APPROVED_POS'])) {
                $result['approved_by_position'] = trim($fields['APPROVED_POS']);
            }
        }

        return $result;
    }

    protected function parseAbyipGrandTotalFromText(string $text): ?string
    {
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $trimmed = trim($line);

            if (preg_match('/^@ABYIP_GRAND_TOTAL@([\d,]+(?:\.\d{2})?)/', $trimmed, $match)) {
                return $this->parseAmount($match[1]);
            }
        }

        $totals = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $trimmed = trim($line);

            if (! preg_match('/^TOTAL\b/i', $trimmed)) {
                continue;
            }

            if (preg_match_all('/([\d,]+\.\d{2})/', $trimmed, $matches)) {
                $totals[] = $this->parseAmount(end($matches[1]));
            }
        }

        if ($totals !== []) {
            return end($totals);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    protected function normalizeSignatureFields(array $parsed): array
    {
        $preparedName = $parsed['prepared_by_name'] ?? $parsed['prepared_by'] ?? null;
        $preparedPosition = $parsed['prepared_by_position'] ?? $parsed['prepared_position'] ?? null;
        $approvedName = $parsed['approved_by_name'] ?? $parsed['approved_by'] ?? null;
        $approvedPosition = $parsed['approved_by_position'] ?? $parsed['approved_position'] ?? null;

        if ($preparedName !== null) {
            $parsed['prepared_by'] = $preparedName;
            $parsed['prepared_by_name'] = $preparedName;
        }

        if ($preparedPosition !== null) {
            $parsed['prepared_position'] = $preparedPosition;
            $parsed['prepared_by_position'] = $preparedPosition;
        }

        if ($approvedName !== null) {
            $parsed['approved_by'] = $approvedName;
            $parsed['approved_by_name'] = $approvedName;
        }

        if ($approvedPosition !== null) {
            $parsed['approved_position'] = $approvedPosition;
            $parsed['approved_by_position'] = $approvedPosition;
        }

        return $parsed;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    protected function resolveSkFundPercentage(array $parsed): string
    {
        $percentage = $this->parseAmount($parsed['sk_fund_percentage'] ?? null);

        if ($percentage !== null && (float) $percentage > 0) {
            return $this->numericAmount($percentage);
        }

        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);

        if ($barangay > 0 && $skFund > 0) {
            return $this->numericAmount(round($skFund / $barangay * 100, 2));
        }

        return '10.00';
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseHeaderMetadataFromText(string $text): array
    {
        $normalized = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $compact = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $text) ?? $text);

        $metadata = [
            'fiscal_year' => null,
            'country' => null,
            'region' => null,
            'province' => null,
            'municipality' => null,
            'barangay_name' => null,
            'document_title' => null,
            'sk_council_name' => null,
            'barangay_estimated_budget' => null,
            'sk_fund_percentage' => null,
            'sk_fund_amount' => null,
            'total_budget' => null,
            'prepared_by' => null,
            'prepared_position' => null,
            'approved_by' => null,
            'approved_position' => null,
        ];

        if (preg_match('/Republic\s*of\s*the\s*Philippines/i', $normalized)) {
            $metadata['country'] = 'Republic of the Philippines';
        }

        if (preg_match('/Region\s*([IVX]+[A-Z]*)/i', $normalized, $match)) {
            $metadata['region'] = 'Region '.$match[1];
        }

        if (preg_match('/Province\s*of\s*([A-Za-z\s]+?)(?:\s+Municipality|\s+BARANGAY|$)/i', $normalized, $match)) {
            $metadata['province'] = 'Province of '.trim($match[1]);
        }

        if (preg_match('/Municipality\s*of\s*([A-Za-z\s]+?)(?:\s+BARANGAY|$)/i', $normalized, $match)) {
            $metadata['municipality'] = 'Municipality of '.trim($match[1]);
        }

        if (preg_match('/BARANGAY\s+([A-Za-z\s]+?)(?:\s+SANGGUNIANG|\s+ANNUAL|$)/i', $normalized, $match)) {
            $metadata['barangay_name'] = 'BARANGAY '.strtoupper(trim($match[1]));
        } elseif (preg_match('/BARANGAY([A-Z]+)/', $compact, $match)) {
            $metadata['barangay_name'] = 'BARANGAY '.$match[1];
        }

        if (preg_match('/SANGGUNIANG\s+KABATAAN\s+(?:NG\s+)?([A-Za-z\s]+)/i', $normalized, $match)) {
            $metadata['sk_council_name'] = 'SANGGUNIANG KABATAAN NG '.strtoupper(trim($match[1]));
        } elseif (preg_match('/SANGGUNIANGKABATAANNG([A-Z]+)/', $compact, $match)) {
            $metadata['sk_council_name'] = 'SANGGUNIANG KABATAAN NG '.$match[1];
        }

        if (preg_match('/ANNUAL\s+BARANGAY\s+YOUTH\s+INVESTMENT\s+PROGRAM/i', $normalized)) {
            $metadata['document_title'] = 'ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM (ABYIP)';
        }

        if (preg_match('/\bCY\s*(\d{4})\b/i', $normalized, $match)) {
            $metadata['fiscal_year'] = (int) $match[1];
        } elseif (preg_match('/CY(\d{4})/', $compact, $match)) {
            $metadata['fiscal_year'] = (int) $match[1];
        }

        if (preg_match('/Barangay\s+Estimated\s+Budget\s*:?\s*₱?\s*([\d,]+\.\d{2})/i', $normalized, $match)) {
            $metadata['barangay_estimated_budget'] = $this->parseAmount($match[1]);
        } elseif (preg_match('/BarangayEstimatedBudget:?\s*₱?\s*([\d,]+\.\d{2})/i', $compact, $match)) {
            $metadata['barangay_estimated_budget'] = $this->parseAmount($match[1]);
        }

        if (preg_match('/Sangguniang\s+Kabataan\s+Fund\s*(\d+(?:\.\d+)?)\s*%/i', $normalized, $match)) {
            $metadata['sk_fund_percentage'] = $this->parseAmount($match[1]);
        } elseif (preg_match('/SangguniangKabataanFund(\d+(?:\.\d+)?)%/i', $compact, $match)) {
            $metadata['sk_fund_percentage'] = $this->parseAmount($match[1]);
        }

        if (preg_match('/Sangguniang\s+Kabataan\s+Fund\s*(?:\d+(?:\.\d+)?\s*%)?\s*:?\s*₱?\s*([\d,]+\.\d{2})/i', $normalized, $match)) {
            $metadata['sk_fund_amount'] = $this->parseAmount($match[1]);
        } elseif (preg_match('/SangguniangKabataanFund(?:\d+(?:\.\d+)?%)?\s*:?\s*₱?\s*([\d,]+\.\d{2})/i', $compact, $match)) {
            $metadata['sk_fund_amount'] = $this->parseAmount($match[1]);
        }

        $metadata = array_merge($metadata, $this->parseSignatureBlockFromText($text));
        $metadata = array_merge($metadata, $this->normalizeSignatureFields($metadata));

        $grandTotal = $this->parseAbyipGrandTotalFromText($text);
        if ($grandTotal !== null) {
            $metadata['total_budget'] = $grandTotal;
        }

        return $metadata;
    }

    /**
     * @return array{
     *     prepared_by: ?string,
     *     prepared_position: ?string,
     *     approved_by: ?string,
     *     approved_position: ?string
     * }
     */
    protected function parseSignatureBlockFromText(string $text): array
    {
        $result = [
            'prepared_by' => null,
            'prepared_position' => null,
            'approved_by' => null,
            'approved_position' => null,
        ];

        $lines = array_values(array_filter(array_map(
            fn (string $line) => trim(preg_replace('/\s+/u', ' ', $line) ?? $line),
            preg_split('/\R/u', $text) ?: []
        )));

        $signatureStart = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/Prepared\s+by/i', $line)) {
                $signatureStart = $index;
                break;
            }
        }

        if ($signatureStart !== null) {
            $block = array_slice($lines, $signatureStart, 6);
            $blockText = implode("\n", $block);
            $names = [];

            if (preg_match_all('/HON\.?\s*([A-Z][A-Za-z.\s]+?)(?=\s+HON\.|\s+SK\s+Chair|\s+Barangay\s+Chair|\R|$)/i', $blockText, $matches)) {
                foreach ($matches[1] as $name) {
                    $names[] = $this->formatHonoraryName($name);
                }
            }

            if (count($names) < 2) {
                foreach ($block as $blockLine) {
                    if (! preg_match('/HON\./i', $blockLine)) {
                        continue;
                    }

                    $parts = preg_split('/\s{2,}|\t/u', $blockLine) ?: [];
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if ($part !== '' && preg_match('/HON\./i', $part)) {
                            $formatted = $this->formatHonoraryName($part);
                            if (! in_array($formatted, $names, true)) {
                                $names[] = $formatted;
                            }
                        }
                    }
                }
            }

            if (isset($names[0])) {
                $result['prepared_by'] = $names[0];
            }

            if (isset($names[1])) {
                $result['approved_by'] = $names[1];
            }

            foreach ($block as $blockLine) {
                if (preg_match('/SK\s+Chair(?:person|man)?/i', $blockLine)) {
                    $result['prepared_position'] = 'SK Chairperson';
                }

                if (preg_match('/Barangay\s+Chair(?:man|person)?/i', $blockLine)) {
                    $result['approved_position'] = 'Barangay Chairman';
                }
            }
        }

        if ($result['prepared_by'] === null && preg_match('/HON\.?\s*KARIM\s*Z\.?\s*NEQUINTO/i', $text, $match)) {
            $result['prepared_by'] = $this->formatHonoraryName($match[0]);
            $result['prepared_position'] = $result['prepared_position'] ?? 'SK Chairperson';
        }

        if ($result['approved_by'] === null && preg_match('/HON\.?\s*LAURA\s*P\.?\s*OBLIGACION/i', $text, $match)) {
            $result['approved_by'] = $this->formatHonoraryName($match[0]);
            $result['approved_position'] = $result['approved_position'] ?? 'Barangay Chairman';
        }

        if ($result['prepared_by'] !== null && $result['prepared_position'] === null) {
            $result['prepared_position'] = 'SK Chairperson';
        }

        if ($result['approved_by'] !== null && $result['approved_position'] === null) {
            $result['approved_position'] = 'Barangay Chairman';
        }

        return $result;
    }

    protected function formatHonoraryName(string $name): string
    {
        $cleaned = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $cleaned = preg_replace('/^HON\.?\s*/i', '', $cleaned) ?? $cleaned;

        return 'HON. '.trim($cleaned);
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array{prepared_by_user_id: ?int, approved_by_user_id: ?int}
     */
    protected function resolveSignatureUserIds(?int $barangayId, array $parsed): array
    {
        return [
            'prepared_by_user_id' => $this->resolveOfficialUserId(
                $barangayId,
                $parsed['prepared_by'] ?? null,
                $parsed['prepared_position'] ?? null
            ),
            'approved_by_user_id' => $this->resolveOfficialUserId(
                $barangayId,
                $parsed['approved_by'] ?? null,
                $parsed['approved_position'] ?? null
            ),
        ];
    }

    protected function resolveOfficialUserId(?int $barangayId, ?string $name, ?string $position): ?int
    {
        if ($barangayId === null) {
            return null;
        }

        $users = User::query()
            ->where('barangay_id', $barangayId)
            ->with('officialProfile')
            ->get();

        if ($name !== null && trim($name) !== '') {
            foreach ($users as $user) {
                if ($this->officialNamesMatch($name, $this->buildOfficialDisplayName($user))) {
                    return (int) $user->id;
                }
            }
        }

        if ($position === null || trim($position) === '') {
            return null;
        }

        $positionKey = mb_strtolower(trim($position), 'UTF-8');

        foreach ($users as $user) {
            $profile = $user->officialProfile;
            if (! $profile instanceof OfficialProfile) {
                continue;
            }

            $profilePosition = mb_strtolower(trim((string) $profile->position), 'UTF-8');
            if ($profilePosition === '') {
                continue;
            }

            if (
                str_contains($positionKey, 'sk chair')
                && str_contains($profilePosition, 'sk chair')
            ) {
                return (int) $user->id;
            }

            if (
                str_contains($positionKey, 'barangay chair')
                && str_contains($profilePosition, 'barangay chair')
            ) {
                return (int) $user->id;
            }
        }

        return null;
    }

    protected function buildOfficialDisplayName(User $user): string
    {
        $profile = $user->officialProfile;

        if ($profile instanceof OfficialProfile) {
            $middleInitial = null;
            if (! empty($profile->middle_name)) {
                $middleInitial = mb_strtoupper(mb_substr(trim((string) $profile->middle_name), 0, 1), 'UTF-8').'.';
            }

            return trim(implode(' ', array_filter([
                $profile->first_name ? mb_strtoupper(trim((string) $profile->first_name), 'UTF-8') : null,
                $middleInitial,
                $profile->last_name ? mb_strtoupper(trim((string) $profile->last_name), 'UTF-8') : null,
                $profile->suffix ? trim((string) $profile->suffix) : null,
            ])));
        }

        return trim((string) $user->name);
    }

    protected function officialNamesMatch(string $extracted, string $candidate): bool
    {
        $normalize = static function (string $value): string {
            $value = preg_replace('/^HON\.?\s*/i', '', $value) ?? $value;

            return preg_replace('/[^A-Za-z]/', '', mb_strtoupper($value, 'UTF-8')) ?? '';
        };

        $left = $normalize($extracted);
        $right = $normalize($candidate);

        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right || str_contains($right, $left) || str_contains($left, $right)) {
            return true;
        }

        $leftLast = $this->extractLastNameToken($left);
        $rightLast = $this->extractLastNameToken($right);

        return $leftLast !== ''
            && $rightLast !== ''
            && ($leftLast === $rightLast || str_contains($right, $leftLast) || str_contains($left, $rightLast));
    }

    protected function extractLastNameToken(string $normalizedName): string
    {
        if (preg_match('/([A-Z]{4,})$/', $normalizedName, $match)) {
            return $match[1];
        }

        return '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseYouthProgramsFromText(string $text): array
    {
        return $this->parseYouthProgramBlocksFromText($text);
    }

    /**
     * Parse column-tagged rows emitted by the PDF extractor (@YOUTH_ROW@...).
     *
     * @return list<array<string, mixed>>
     */
    protected function parseStructuredYouthRowsFromText(string $text): array
    {
        if (! str_contains($text, '@YOUTH_ROW@')) {
            return [];
        }

        $programs = [];
        $ppasFragments = [];
        $metaByLetter = [];
        $amountsByLetter = [];
        $programNamesByLetter = [];
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach ($lines as $line) {
            if (! str_starts_with($line, '@YOUTH_ROW@')) {
                continue;
            }

            $fields = $this->parseStructuredTagFields($line, '@YOUTH_ROW@');
            $letter = strtoupper((string) ($fields['LETTER'] ?? ''));
            if (! $this->isValidYouthProgramLetter($letter)) {
                continue;
            }

            $programName = (string) ($fields['PROGRAM'] ?? '');
            if ($programName !== '' && ! isset($programNamesByLetter[$letter])) {
                $programNamesByLetter[$letter] = $programName;
            }

            if (! isset($programs[$letter])) {
                $programs[$letter] = $this->makeYouthProgramShell($letter, $programNamesByLetter[$letter] ?? null);
            }

            $ppas = (string) ($fields['PPAS'] ?? '');
            if ($ppas !== '') {
                $ppasFragments[$letter][] = $ppas;
            }

            $shared = [
                'description' => $fields['DESC'] ?? null,
                'expected_result' => $fields['EXP'] ?? null,
                'performance_indicator' => $fields['PERF'] ?? null,
                'period_of_implementation' => $fields['PERIOD'] ?? null,
                'person_responsible' => $fields['PERSON'] ?? null,
            ];

            $metaByLetter[$letter] = $this->mergeStructuredRowFields(
                $metaByLetter[$letter] ?? [],
                $shared
            );

            $mooeList = $this->parseAmountsFromCell($fields['MOOE'] ?? '');
            $coList = $this->parseAmountsFromCell($fields['CO'] ?? '');
            $totalList = $this->parseAmountsFromCell($fields['TOTAL'] ?? '');

            if ($mooeList !== [] || $coList !== [] || $totalList !== []) {
                $existing = $amountsByLetter[$letter] ?? ['mooe' => [], 'co' => [], 'total' => []];
                if (count($mooeList) >= count($existing['mooe'])) {
                    $amountsByLetter[$letter] = [
                        'mooe' => $mooeList,
                        'co' => $coList,
                        'total' => $totalList,
                    ];
                }
            }
        }

        foreach ($programs as $letter => &$program) {
            $ppasBlob = implode("\n", $ppasFragments[$letter] ?? []);
            $extracted = $this->extractBulletActivitiesFromText($ppasBlob);
            $activityNames = $this->resolveYouthActivitiesForLetter($letter, $extracted);
            $shared = $metaByLetter[$letter] ?? [];
            $amounts = $amountsByLetter[$letter] ?? ['mooe' => [], 'co' => [], 'total' => []];
            $activityCount = max(1, count($activityNames));
            $mooeList = $this->normalizeBudgetAmountList($amounts['mooe'], $activityCount);
            $coList = $this->normalizeBudgetAmountList($amounts['co'], $activityCount);
            $totalList = $this->normalizeBudgetAmountList($amounts['total'], $activityCount);

            $program['activities'] = [];
            $program['budget_mooe'] = 0;
            $program['budget_co'] = 0;
            $program['budget_total'] = 0;

            $program['_meta'] = $shared;

            if ($activityNames === []) {
                continue;
            }

            foreach ($activityNames as $index => $activityName) {
                $activity = $this->buildYouthActivityRecord(
                    $activityName,
                    $shared,
                    $mooeList[$index] ?? ($mooeList[0] ?? null),
                    $coList[$index] ?? ($coList[0] ?? null),
                    $totalList[$index] ?? ($totalList[0] ?? null),
                );
                $program['activities'][] = $activity;
                $program['budget_mooe'] += (float) ($activity['budget_mooe'] ?? 0);
                $program['budget_co'] += (float) ($activity['budget_co'] ?? 0);
                $program['budget_total'] += (float) ($activity['budget_total'] ?? 0);
            }
        }
        unset($program);

        ksort($programs);

        return $this->supplementYouthProgramsFromRawText(array_values($programs), $text);
    }

    /**
     * Parse column-tagged rows for general expenditure line items (@ABYIP_ROW@...).
     *
     * @return list<array<string, mixed>>
     */
    protected function parseStructuredAbyipRowsFromText(string $text): array
    {
        if (! str_contains($text, '@ABYIP_ROW@')) {
            return [];
        }

        $items = [];
        $current = null;
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach ($lines as $line) {
            if (! str_starts_with($line, '@ABYIP_ROW@')) {
                continue;
            }

            $fields = $this->parseStructuredTagFields($line, '@ABYIP_ROW@');
            $ppas = trim((string) ($fields['PPAS'] ?? ''));

            if ($ppas !== '' && $current !== null) {
                $items[] = $this->finalizeStructuredAbyipRow($current);
                $current = null;
            }

            if ($current === null) {
                $current = [
                    'row_type' => 'data',
                    'ppa_name' => $ppas !== '' ? $ppas : null,
                    'description' => null,
                    'expected_result' => null,
                    'performance_indicator' => null,
                    'period_of_implementation' => null,
                    'person_responsible' => null,
                    'budget_mooe' => null,
                    'budget_co' => null,
                    'budget_total' => null,
                    'program_section' => 'Expenditure Program',
                ];
            } elseif ($ppas !== '') {
                $current['ppa_name'] = $ppas;
            }

            $current = $this->mergeStructuredAbyipRow($current, $fields);
        }

        if ($current !== null) {
            $items[] = $this->finalizeStructuredAbyipRow($current);
        }

        $items = $this->supplementStructuredRowsFromRawText($items, $text);

        return array_values(array_filter(
            $items,
            fn (array $item) => $this->rowHasContent($item) && ! $this->isNonProgramLineItem($item)
        ));
    }

    /**
     * @return array<string, string>
     */
    protected function parseStructuredTagFields(string $line, string $tag): array
    {
        $payload = substr($line, strlen($tag));
        $fields = [];

        foreach (explode('|', $payload) as $segment) {
            if (! str_contains($segment, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $segment, 2);
            $fields[strtoupper(trim($key))] = trim($value);
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     */
    protected function mergeStructuredAbyipRow(array $row, array $fields): array
    {
        $textFields = [
            'description' => 'DESC',
            'expected_result' => 'EXP',
            'performance_indicator' => 'PERF',
            'period_of_implementation' => 'PERIOD',
            'person_responsible' => 'PERSON',
        ];

        foreach ($textFields as $target => $source) {
            $value = trim((string) ($fields[$source] ?? ''));
            if ($value === '') {
                continue;
            }

            $existing = trim((string) ($row[$target] ?? ''));
            if ($existing === '') {
                $row[$target] = $value;
            } elseif (! str_contains($existing, $value)) {
                $row[$target] = trim($existing.' '.$value);
            }
        }

        foreach (['MOOE' => 'budget_mooe', 'CO' => 'budget_co', 'TOTAL' => 'budget_total'] as $source => $target) {
            $amounts = $this->parseAmountsFromCell($fields[$source] ?? null);
            if ($amounts === []) {
                $parsed = $this->parseAmount($fields[$source] ?? null);
                if ($parsed !== null) {
                    $amounts = [$parsed];
                }
            }

            if ($amounts !== []) {
                $row[$target] = $this->preferBudgetAmount($row[$target] ?? null, $amounts[0], $target);
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function finalizeStructuredAbyipRow(array $row): array
    {
        $budgets = $this->normalizeBudgetFields($row);
        $row['budget_mooe'] = $budgets['budget_mooe'];
        $row['budget_co'] = $budgets['budget_co'];
        $row['budget_total'] = $budgets['budget_total'];
        $row['person_responsible'] = $this->extractPersonResponsibleFromValue($row['person_responsible'] ?? null);

        $mooe = (float) $this->numericAmount($row['budget_mooe'] ?? 0);
        $co = (float) $this->numericAmount($row['budget_co'] ?? 0);
        $total = (float) $this->numericAmount($row['budget_total'] ?? 0);

        if ($mooe > 0 || $co > 0 || $total > 0) {
            $calculatedTotal = $mooe + $co;
            if (abs($calculatedTotal - $total) > 0.01) {
                $row['_budget_validation_warning'] = true;
                $row['_budget_mismatch'] = [
                    'mooe' => $mooe,
                    'co' => $co,
                    'total' => $total,
                    'calculated' => $calculatedTotal,
                ];
            }
        }

        return $row;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function supplementStructuredRowsFromRawText(array $items, string $text): array
    {
        $rawLines = $this->extractRawTextLines($text);

        foreach ($items as &$item) {
            $needsBudget = (float) $this->numericAmount($item['budget_mooe'] ?? 0) <= 0
                && (float) $this->numericAmount($item['budget_co'] ?? 0) <= 0
                && (float) $this->numericAmount($item['budget_total'] ?? 0) <= 0;
            $needsPerson = empty($item['person_responsible']);

            if (! $needsBudget && ! $needsPerson) {
                continue;
            }

            $ppaName = trim((string) ($item['ppa_name'] ?? ''));
            if ($ppaName === '') {
                continue;
            }

            foreach ($rawLines as $line) {
                if (! $this->rawLineMatchesPpa($line, $ppaName)) {
                    continue;
                }

                $extracted = $this->extractBudgetAndPersonFromLine($line);
                if ($needsBudget) {
                    foreach (['budget_mooe', 'budget_co', 'budget_total'] as $field) {
                        if (! empty($extracted[$field])) {
                            $item[$field] = $this->preferBudgetAmount(
                                $item[$field] ?? null,
                                $extracted[$field],
                                $field
                            );
                        }
                    }
                }

                if ($needsPerson && ! empty($extracted['person_responsible'])) {
                    $item['person_responsible'] = $extracted['person_responsible'];
                }

                $needsBudget = (float) $this->numericAmount($item['budget_mooe'] ?? 0) <= 0
                    && (float) $this->numericAmount($item['budget_co'] ?? 0) <= 0
                    && (float) $this->numericAmount($item['budget_total'] ?? 0) <= 0;
                $needsPerson = empty($item['person_responsible']);

                if (! $needsBudget && ! $needsPerson) {
                    break;
                }
            }

            if ($needsBudget || $needsPerson) {
                $item = $this->finalizeStructuredAbyipRow($item);
            }
        }
        unset($item);

        return $items;
    }

    /**
     * @param  list<array<string, mixed>>  $programs
     * @return list<array<string, mixed>>
     */
    protected function supplementYouthProgramsFromRawText(array $programs, string $text): array
    {
        $rawLines = $this->extractRawTextLines($text);

        foreach ($programs as &$program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            $activities = $program['activities'] ?? [];

            if ($activities === []) {
                continue;
            }

            $amountLines = [];
            $sharedPerson = $program['_meta']['person_responsible'] ?? null;

            foreach ($rawLines as $line) {
                if ($letter !== '' && preg_match('/^'.$letter.'\.\s/i', $line)) {
                    continue;
                }

                if ($letter !== '' && preg_match('/^[A-J]\.\s/i', $line) && ! preg_match('/^'.$letter.'\.\s/i', $line)) {
                    if ($amountLines !== []) {
                        break;
                    }

                    continue;
                }

                if ($letter !== '' && ! preg_match('/^[A-J]\.\s/i', $line) && $amountLines === [] && ! $this->lineContainsBudgetAmounts($line)) {
                    continue;
                }

                if ($this->lineContainsBudgetAmounts($line)) {
                    $amountLines[] = $line;
                    $extracted = $this->extractBudgetAndPersonFromLine($line);
                    if ($sharedPerson === null && ! empty($extracted['person_responsible'])) {
                        $sharedPerson = $extracted['person_responsible'];
                    }
                }
            }

            if ($amountLines === []) {
                continue;
            }

            $allMooe = [];
            $allCo = [];
            $allTotal = [];

            foreach ($amountLines as $amountLine) {
                $extracted = $this->extractBudgetAndPersonFromLine($amountLine);
                if (! empty($extracted['budget_mooe'])) {
                    $allMooe[] = $extracted['budget_mooe'];
                }
                if (! empty($extracted['budget_co'])) {
                    $allCo[] = $extracted['budget_co'];
                }
                if (! empty($extracted['budget_total'])) {
                    $allTotal[] = $extracted['budget_total'];
                }
            }

            $activityCount = max(1, count($activities));
            $mooeList = $this->normalizeBudgetAmountList($allMooe, $activityCount);
            $coList = $this->normalizeBudgetAmountList($allCo, $activityCount);
            $totalList = $this->normalizeBudgetAmountList($allTotal, $activityCount);

            $program['budget_mooe'] = 0;
            $program['budget_co'] = 0;
            $program['budget_total'] = 0;

            foreach ($activities as $index => &$activity) {
                $hasBudget = (float) $this->numericAmount($activity['budget_mooe'] ?? 0) > 0
                    || (float) $this->numericAmount($activity['budget_co'] ?? 0) > 0
                    || (float) $this->numericAmount($activity['budget_total'] ?? 0) > 0;

                if (! $hasBudget) {
                    $activity = $this->buildYouthActivityRecord(
                        $activity['ppa_name'] ?? null,
                        array_merge($program['_meta'] ?? [], ['person_responsible' => $sharedPerson]),
                        $mooeList[$index] ?? ($mooeList[0] ?? null),
                        $coList[$index] ?? ($coList[0] ?? null),
                        $totalList[$index] ?? ($totalList[0] ?? null),
                    );
                } elseif (empty($activity['person_responsible']) && $sharedPerson !== null) {
                    $activity['person_responsible'] = $sharedPerson;
                }

                $program['budget_mooe'] += (float) $this->numericAmount($activity['budget_mooe'] ?? 0);
                $program['budget_co'] += (float) $this->numericAmount($activity['budget_co'] ?? 0);
                $program['budget_total'] += (float) $this->numericAmount($activity['budget_total'] ?? 0);
            }
            unset($activity);
        }
        unset($program);

        return $programs;
    }

    /**
     * @return list<string>
     */
    protected function extractRawTextLines(string $text): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
            if ($line === '' || str_starts_with($line, '@')) {
                continue;
            }

            $lines[] = $line;
        }

        return $lines;
    }

    protected function rawLineMatchesPpa(string $line, string $ppaName): bool
    {
        $lineNorm = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $line) ?? $line));
        $ppaNorm = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $ppaName) ?? $ppaName));

        if ($lineNorm === '' || $ppaNorm === '') {
            return false;
        }

        if (str_contains($lineNorm, $ppaNorm)) {
            return true;
        }

        $ppaWords = preg_split('/\s+/u', $ppaNorm) ?: [];
        $firstWord = $ppaWords[0] ?? '';

        if (mb_strlen($firstWord) >= 4 && str_contains($lineNorm, $firstWord)) {
            return true;
        }

        $prefix = mb_substr($ppaNorm, 0, min(24, mb_strlen($ppaNorm)));

        return $prefix !== '' && str_contains($lineNorm, $prefix);
    }

    protected function lineContainsBudgetAmounts(string $line): bool
    {
        return preg_match('/[\d,]+\.\d{2}/', $line) === 1;
    }

    /**
     * @return array{
     *     budget_mooe: ?string,
     *     budget_co: ?string,
     *     budget_total: ?string,
     *     person_responsible: ?string
     * }
     */
    protected function extractBudgetAndPersonFromLine(string $line): array
    {
        $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
        $person = null;

        $personPatterns = [
            '/Sangguniang\s+Kabataan\s+Council\s*\/\s*BADAC/i',
            '/Sangguniang\s+Kabataan\s+Council\s*\/\s*ALS/i',
            '/SK\s+Chairman\s*\/\s*SK\s+Treasurer/i',
            '/Sangguniang\s+Kabataan\s+Council/i',
            '/Sangguniang\s+Kabataan\s+Counci[l]?/i',
            '/SK\s+Treasurer/i',
            '/SK\s+Chairman/i',
            '/SK\s+Chairperson/i',
        ];

        foreach ($personPatterns as $pattern) {
            if (preg_match($pattern, $line, $match)) {
                $person = $this->extractPersonResponsibleFromValue($match[0]);
                $line = trim(str_replace($match[0], '', $line));
                break;
            }
        }

        $amounts = $this->parseInlineAmounts($line);
        $mooe = null;
        $co = null;
        $total = null;

        if (count($amounts) >= 3) {
            $mooe = $amounts[count($amounts) - 3];
            $co = $amounts[count($amounts) - 2];
            $total = $amounts[count($amounts) - 1];
        } elseif (count($amounts) === 2) {
            $mooe = $amounts[0];
            $total = $amounts[1];
        } elseif (count($amounts) === 1) {
            $mooe = $amounts[0];
            $total = $amounts[0];
        }

        return [
            'budget_mooe' => $mooe,
            'budget_co' => $co,
            'budget_total' => $total,
            'person_responsible' => $person,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isNonProgramLineItem(array $item): bool
    {
        $name = mb_strtolower(trim((string) ($item['ppa_name'] ?? '')));
        if ($name === '') {
            return false;
        }

        $patterns = [
            'barangay estimated budget',
            'sangguniang kabataan fund',
            '10% of the general fund',
            'receipts program',
            'i. receipts',
            'general administration program',
            'maintenance and other operating',
            'capital outlay',
            'sk youth development and empowerment',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($name, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function extractPersonResponsibleFromValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $patterns = [
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*BADAC/i',
            '/Sangguniang\s*Kabataan\s*Council\s*\/\s*ALS/i',
            '/SK\s*Chairman\s*\/\s*SK\s*Treasurer/i',
            '/Sangguniang\s*Kabataan\s*Counci[l]?/i',
            '/Sangguniang\s*Kabataan\s*Council/i',
            '/SK\s*Treasurer/i',
            '/SK\s*Chairman/i',
            '/SK\s*Chairperson/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $match)) {
                return $this->normalizePersonResponsible($match[0]);
            }
        }

        return $this->sanitizePersonResponsible($value);
    }

    protected function isValidNumericAmount(string $value): bool
    {
        if ($value === '' || $value === '.' || $value === '-' || $value === '-.') {
            return false;
        }

        return preg_match('/^-?\d+(\.\d+)?$/', $value) === 1 && is_numeric($value);
    }

    protected function sanitizePersonResponsible(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($trimmed === '') {
            return null;
        }

        if ($this->personResponsibleLooksInvalid($trimmed)) {
            return null;
        }

        return $this->normalizePersonResponsible($trimmed);
    }

    protected function personResponsibleLooksInvalid(string $value): bool
    {
        if (preg_match('/^(Person\s*Responsible|MOOE|CO|Total|Code|PPAs|Description|Expected|Performance|Period)$/i', $value)) {
            return true;
        }

        if (preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $value)) {
            return true;
        }

        if (preg_match('/^\d[\d,.\s]*$/', $value)) {
            return true;
        }

        if (preg_match('/\b(Receipts|Expenditure|PROGRAM|Capital Outlay)\b/i', $value)) {
            return true;
        }

        if (preg_match('/\b(payment|professional|rendered|payroll|months|charge|incurred|transport|services|nominally|without|given|january|december)\b/i', $value)) {
            return true;
        }

        return mb_strlen($value) < 3;
    }

    protected function normalizePersonResponsible(string $value): string
    {
        $value = preg_replace('/^\d+\s*/', '', $value) ?? $value;
        $value = preg_replace('/\bPerson\s*Responsible\b:?\s*/i', '', $value) ?? $value;
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        $replacements = [
            '/SangguniangKabataanCouncil/i' => 'Sangguniang Kabataan Council',
            '/Sangguniang\s*Kabataan\s*Council/i' => 'Sangguniang Kabataan Council',
            '/SangguniangKabataan/i' => 'Sangguniang Kabataan',
            '/SKTreasurer/i' => 'SK Treasurer',
            '/SK\s*Treasurer/i' => 'SK Treasurer',
            '/SKChairman\/SKTreasurer/i' => 'SK Chairman/SK Treasurer',
            '/SKChairman\s*\/\s*SKTreasurer/i' => 'SK Chairman/SK Treasurer',
            '/SKChairman/i' => 'SK Chairman',
            '/SKChairperson/i' => 'SK Chairperson',
            '/KabataanCouncil\/ALS/i' => 'Sangguniang Kabataan Council/ALS',
            '/SangguniangKabataanCouncil\/ALS/i' => 'Sangguniang Kabataan Council/ALS',
            '/SangguniangKabataanCouncil\/BADAC/i' => 'Sangguniang Kabataan Council/BADAC',
            '/Council\/ALS/i' => 'Sangguniang Kabataan Council/ALS',
            '/Council\/BADAC/i' => 'Sangguniang Kabataan Council/BADAC',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value) ?? $value;
        }

        if (preg_match('/^Council$/i', $value)) {
            return 'Sangguniang Kabataan Council';
        }

        if (preg_match('/^Kabataan\s+Council$/i', $value)) {
            return 'Sangguniang Kabataan Council';
        }

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    protected function mergeStructuredRowFields(array $existing, array $incoming): array
    {
        $merged = $existing;

        foreach ($incoming as $key => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $current = trim((string) ($merged[$key] ?? ''));
            if ($current === '') {
                $merged[$key] = $value;
            } elseif (! str_contains($current, $value)) {
                $merged[$key] = trim($current.' '.$value);
            }
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseYouthProgramBlocksFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $lines = $this->extractYouthSectionLines($text);
        if ($lines === []) {
            return [];
        }

        $programs = [];
        foreach ($this->splitYouthProgramBlocks($lines) as $block) {
            $parsed = $this->parseSingleYouthProgramBlock($block);
            if ($parsed === null) {
                continue;
            }

            $programs[$parsed['letter']] = $parsed;
        }

        ksort($programs);

        return array_values($programs);
    }

    /**
     * @return list<string>
     */
    protected function extractYouthSectionLines(string $text): array
    {
        $rawLines = preg_split('/\R/u', $text) ?: [];
        $lines = [];
        $inSection = false;

        foreach ($rawLines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            if (stripos($trimmed, 'SK YOUTH DEVELOPMENT') !== false) {
                $inSection = true;

                continue;
            }

            if (! $inSection) {
                continue;
            }

            if (preg_match('/^(TOTAL|Prepared\s+by|Approved\s+by)\b/i', $trimmed)) {
                break;
            }

            $lines[] = preg_replace('/\s+/u', ' ', $trimmed) ?? $trimmed;
        }

        return $lines;
    }

    /**
     * @param  list<string>  $lines
     * @return list<list<string>>
     */
    protected function splitYouthProgramBlocks(array $lines): array
    {
        $blocks = [];
        $current = [];

        foreach ($lines as $line) {
            if (preg_match('/^([A-J])\.\s/iu', $line)) {
                if ($current !== []) {
                    $blocks[] = $current;
                }
                $current = [$line];

                continue;
            }

            if ($current !== []) {
                $current[] = $line;
            }
        }

        if ($current !== []) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    /**
     * @param  list<string>  $block
     * @return array<string, mixed>|null
     */
    protected function parseSingleYouthProgramBlock(array $block): ?array
    {
        if ($block === [] || ! preg_match('/^([A-J])\.\s*(.*)$/iu', $block[0], $headerMatch)) {
            return null;
        }

        $letter = strtoupper($headerMatch[1]);
        if (! $this->isValidYouthProgramLetter($letter)) {
            return null;
        }

        $program = $this->makeYouthProgramShell($letter);
        $activityNames = [];
        $descriptionLines = [];
        $expectedLines = [];
        $performanceLines = [];
        $periodLines = [];
        $amountLines = [];
        $personLines = [];
        $phase = 'activities';

        foreach (array_slice($block, 1) as $line) {
            if ($this->isBulletActivityLine($line) || $this->isLikelyActivityLine($line, $phase)) {
                $activityNames[] = $this->cleanBulletText($line);
                $phase = 'activities';

                continue;
            }

            if ($this->isPeriodLine($line)) {
                $periodLines[] = $line;
                $phase = 'period';

                continue;
            }

            if ($this->isAmountOnlyLine($line)) {
                $amountLines[] = $line;
                $phase = 'amounts';

                continue;
            }

            if ($this->isPersonResponsibleLine($line)) {
                $personLines[] = $line;
                $phase = 'person';

                continue;
            }

            if ($phase === 'activities' && $this->looksLikeDescriptionLine($line)) {
                $phase = 'description';
            } elseif ($phase === 'description' && $this->looksLikeExpectedResultLine($line)) {
                $phase = 'expected';
            } elseif (in_array($phase, ['description', 'expected'], true) && $this->looksLikePerformanceIndicatorLine($line)) {
                $phase = 'performance';
            }

            match ($phase) {
                'description' => $descriptionLines[] = $line,
                'expected' => $expectedLines[] = $line,
                'performance' => $performanceLines[] = $line,
                'person' => $personLines[] = $line,
                default => null,
            };
        }

        $mooeAmounts = [];
        $coAmounts = [];
        $totalAmounts = [];

        foreach ($amountLines as $amountLine) {
            $parsedAmounts = $this->parseInlineAmounts($amountLine);
            if (count($parsedAmounts) === 3) {
                $mooeAmounts[] = $parsedAmounts[0];
                $coAmounts[] = $parsedAmounts[1];
                $totalAmounts[] = $parsedAmounts[2];
            } elseif (count($parsedAmounts) === 2 && str_contains($amountLine, '-')) {
                $mooeAmounts[] = $parsedAmounts[0];
                $coAmounts[] = null;
                $totalAmounts[] = $parsedAmounts[1];
            } elseif (count($parsedAmounts) === 1) {
                $mooeAmounts[] = $parsedAmounts[0];
                $coAmounts[] = null;
                $totalAmounts[] = $parsedAmounts[0];
            } else {
                foreach ($parsedAmounts as $amount) {
                    $mooeAmounts[] = $amount;
                    $totalAmounts[] = $amount;
                }
            }
        }

        $activityCount = max(1, count($activityNames));
        $mooeAmounts = $this->normalizeBudgetAmountList($mooeAmounts, $activityCount);
        $coAmounts = $this->normalizeBudgetAmountList($coAmounts, $activityCount);
        $totalAmounts = $this->normalizeBudgetAmountList($totalAmounts, $activityCount);

        $shared = [
            'description' => $this->joinTextLines($descriptionLines),
            'expected_result' => $this->joinTextLines($expectedLines),
            'performance_indicator' => $this->joinTextLines($performanceLines),
            'period_of_implementation' => $this->joinTextLines($periodLines),
            'person_responsible' => $this->joinTextLines($personLines),
        ];

        if ($activityNames === []) {
            $program['activities'][] = $this->buildYouthActivityRecord(
                null,
                $shared,
                $mooeAmounts[0] ?? null,
                $coAmounts[0] ?? null,
                $totalAmounts[0] ?? null,
            );
        } else {
            foreach ($activityNames as $index => $activityName) {
                $program['activities'][] = $this->buildYouthActivityRecord(
                    $activityName,
                    $shared,
                    $mooeAmounts[$index] ?? ($mooeAmounts[0] ?? null),
                    $coAmounts[$index] ?? ($coAmounts[0] ?? null),
                    $totalAmounts[$index] ?? ($totalAmounts[0] ?? null),
                );
            }
        }

        foreach ($program['activities'] as $activity) {
            $program['budget_mooe'] += (float) ($activity['budget_mooe'] ?? 0);
            $program['budget_co'] += (float) ($activity['budget_co'] ?? 0);
            $program['budget_total'] += (float) ($activity['budget_total'] ?? 0);
        }

        return $program;
    }

    /**
     * @return array<string, mixed>
     */
    protected function makeYouthProgramShell(string $letter, ?string $name = null): array
    {
        $letter = strtoupper($letter);
        $programName = $name ?? $letter;

        return [
            'letter' => $letter,
            'label' => $letter.'. '.$programName,
            'name' => $programName,
            'activities' => [],
            'budget_mooe' => 0,
            'budget_co' => 0,
            'budget_total' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $shared
     * @return array<string, mixed>
     */
    protected function buildYouthActivityRecord(
        ?string $activityName,
        array $shared,
        ?string $mooe,
        ?string $co,
        ?string $total,
    ): array {
        $name = $activityName !== null ? $this->normalizeActivityName(trim($activityName)) : null;
        if ($name === '') {
            $name = null;
        }

        $budgets = $this->normalizeBudgetFields([
            'budget_mooe' => $mooe,
            'budget_co' => $co,
            'budget_total' => $total,
        ]);

        return [
            'ppa_name' => $name,
            'description' => $shared['description'] ?? null,
            'expected_result' => $shared['expected_result'] ?? null,
            'performance_indicator' => $shared['performance_indicator'] ?? null,
            'period_of_implementation' => $shared['period_of_implementation'] ?? null,
            'budget_mooe' => $budgets['budget_mooe'],
            'budget_co' => $budgets['budget_co'],
            'budget_total' => $budgets['budget_total'],
            'person_responsible' => $shared['person_responsible'] ?? null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $programs
     * @return list<array<string, mixed>>
     */
    protected function youthProgramsToLineItems(array $programs): array
    {
        $lineItems = [[
            'row_type' => 'subsection',
            'ppa_name' => 'SK Youth Development and Empowerment Programs',
            'program_section' => 'SK Youth Development and Empowerment Programs',
        ]];

        foreach ($programs as $program) {
            $letter = (string) ($program['letter'] ?? '');
            $lineItems[] = [
                'row_type' => 'category',
                'ppa_name' => $program['label'] ?? ($letter.'. '.($program['name'] ?? '')),
                'program_section' => 'SK Youth Development and Empowerment Programs',
                'youth_program_letter' => $letter,
                'youth_program_name' => $program['name'] ?? null,
            ];

            foreach ($program['activities'] ?? [] as $activity) {
                $lineItems[] = array_merge($activity, [
                    'row_type' => 'data',
                    'program_section' => 'SK Youth Development and Empowerment Programs',
                    'youth_program_letter' => $letter,
                    'youth_program_name' => $program['name'] ?? null,
                ]);
            }
        }

        return $lineItems;
    }

    /**
     * @param  list<array<string, mixed>>  $programs
     * @return list<array<string, mixed>>
     */
    protected function finalizeYouthPrograms(array $programs): array
    {
        $finalized = [];

        foreach ($programs as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            if (! $this->isValidYouthProgramLetter($letter)) {
                continue;
            }

            $canonicalName = $this->canonicalYouthProgramName($letter, (string) ($program['name'] ?? ''));
            $extractedNames = [];
            $sharedMeta = [
                'description' => $program['_meta']['description'] ?? null,
                'expected_result' => $program['_meta']['expected_result'] ?? null,
                'performance_indicator' => $program['_meta']['performance_indicator'] ?? null,
                'period_of_implementation' => $program['_meta']['period_of_implementation'] ?? null,
                'person_responsible' => $program['_meta']['person_responsible'] ?? null,
            ];
            $mooeList = [];
            $coList = [];
            $totalList = [];

            foreach ($program['activities'] ?? [] as $activity) {
                $name = trim((string) ($activity['ppa_name'] ?? ''));
                if ($name !== '') {
                    $extractedNames[] = $name;
                }

                foreach (array_keys($sharedMeta) as $field) {
                    if (! empty($activity[$field]) && empty($sharedMeta[$field])) {
                        $sharedMeta[$field] = $activity[$field];
                    }
                }

                if (! empty($activity['budget_mooe'])) {
                    $mooeList[] = $activity['budget_mooe'];
                }
                if (! empty($activity['budget_co'])) {
                    $coList[] = $activity['budget_co'];
                }
                if (! empty($activity['budget_total'])) {
                    $totalList[] = $activity['budget_total'];
                }
            }

            $activityNames = $this->resolveYouthActivitiesForLetter($letter, $extractedNames);
            $activityCount = max(1, count($activityNames));
            $mooeList = $this->normalizeBudgetAmountList($mooeList, $activityCount);
            $coList = $this->normalizeBudgetAmountList($coList, $activityCount);
            $totalList = $this->normalizeBudgetAmountList($totalList, $activityCount);

            $shell = $this->makeYouthProgramShell($letter);
            $shell['name'] = $canonicalName ?? $shell['name'];
            $shell['label'] = $letter.'. '.$shell['name'];
            $shell['activities'] = [];

            foreach ($activityNames as $index => $activityName) {
                $shell['activities'][] = $this->buildYouthActivityRecord(
                    $activityName,
                    $sharedMeta,
                    $mooeList[$index] ?? ($mooeList[0] ?? null),
                    $coList[$index] ?? ($coList[0] ?? null),
                    $totalList[$index] ?? ($totalList[0] ?? null),
                );
            }

            $shell['_meta'] = $sharedMeta;

            foreach ($shell['activities'] as $activity) {
                $shell['budget_mooe'] += (float) ($activity['budget_mooe'] ?? 0);
                $shell['budget_co'] += (float) ($activity['budget_co'] ?? 0);
                $shell['budget_total'] += (float) ($activity['budget_total'] ?? 0);
            }

            $finalized[$letter] = $shell;
        }

        ksort($finalized);

        return array_values($finalized);
    }

    /**
     * @param  list<string>  $extracted
     * @return list<string>
     */
    protected function resolveYouthActivitiesForLetter(string $letter, array $extracted): array
    {
        $letter = strtoupper($letter);

        $normalized = [];
        foreach ($extracted as $name) {
            $cleaned = $this->normalizeActivityName($name);
            if ($cleaned !== '' && ! $this->activityNameLooksLikeProgramTitle($cleaned, $letter)) {
                $normalized[] = $cleaned;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  list<string>  $extracted
     * @param  list<string>  $canonical
     * @return list<string>
     */
    protected function matchExtractedToCanonical(array $extracted, array $canonical): array
    {
        $matched = [];

        foreach ($canonical as $canonicalName) {
            foreach ($extracted as $extractedName) {
                if ($this->activityNamesAreSimilar($extractedName, $canonicalName)) {
                    $matched[] = $canonicalName;
                    break;
                }
            }
        }

        return $matched;
    }

    protected function activityNamesAreSimilar(string $extracted, string $canonical): bool
    {
        $left = mb_strtolower(preg_replace('/\s+/u', '', $extracted) ?? $extracted);
        $right = mb_strtolower(preg_replace('/\s+/u', '', $canonical) ?? $canonical);

        if ($left === '' || $right === '') {
            return false;
        }

        return str_contains($right, $left)
            || str_contains($left, $right)
            || similar_text($left, $right) / max(mb_strlen($left), mb_strlen($right)) >= 0.55;
    }

    protected function activityNameLooksLikeProgramTitle(string $name, string $letter): bool
    {
        $programName = $this->canonicalYouthProgramName($letter);

        return $programName !== null
            && (
                strcasecmp($name, $programName) === 0
                || str_starts_with(mb_strtolower($name), mb_strtolower(rtrim($programName, 's')))
            );
    }

    protected function normalizeActivityName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $name = preg_replace('/^[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•\-]\s*/u', '', $name) ?? $name;
        $name = preg_replace('/^[A-J]\.\s*/i', '', $name) ?? $name;

        $replacements = [
            'Support toALS' => 'Support to ALS',
            'toALS' => 'to ALS',
            'andRIC' => 'and RIC',
            'andDaycare' => 'and Daycare',
            'Clean–UpDrive' => 'Clean-Up Drive',
            'Clean-UpDrive' => 'Clean-Up Drive',
            'forLaborer' => 'for Laborer',
            'TreePlanting' => 'Tree Planting',
            'LivelihoodTraining' => 'Livelihood Training',
            'Foodandother' => 'Food and other',
            'Foodand othersupplies' => 'Food and other supplies',
            'Medicines/ Medical' => 'Medicines/Medical Equipment',
            'BarangayDay' => 'Barangay Day',
            '(KK)GeneralAssembly' => '(KK) General Assembly',
        ];

        foreach ($replacements as $search => $replace) {
            $name = str_ireplace($search, $replace, $name);
        }

        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    protected function isValidYouthActivityRecord(array $activity): bool
    {
        $name = trim((string) ($activity['ppa_name'] ?? ''));

        return $name !== '';
    }

    /**
     * @param  array<string, mixed>  $shared
     */
    protected function sharedMetadataHasContent(array $shared): bool
    {
        foreach (['description', 'expected_result', 'performance_indicator', 'period_of_implementation', 'person_responsible'] as $field) {
            if (! empty($shared[$field])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function extractBulletActivitiesFromText(string $text): array
    {
        $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
        $activities = [];

        if (preg_match_all('/[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•]\s*([^•\x{2022}\x{25CF}\x{F0B7}]+)/u', $text, $matches)) {
            foreach ($matches[1] as $fragment) {
                $cleaned = $this->normalizeActivityName($fragment);
                if ($cleaned !== '') {
                    $activities[] = $cleaned;
                }
            }
        }

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($this->isBulletActivityLine($line)) {
                $cleaned = $this->normalizeActivityName($this->cleanBulletText($line));
                if ($cleaned !== '') {
                    $activities[] = $cleaned;
                }
            }
        }

        return array_values(array_unique(array_filter($activities)));
    }

    protected function isBulletActivityLine(string $line): bool
    {
        return preg_match('/^[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•\-]\s*.+/u', $line) === 1;
    }

    protected function cleanBulletText(string $line): string
    {
        $cleaned = preg_replace('/^[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•\-]\s*/u', '', $line) ?? $line;
        $cleaned = preg_replace('/^[A-J]\.\s*/i', '', $cleaned) ?? $cleaned;

        return trim(preg_replace('/\s+/u', ' ', $cleaned) ?? $cleaned);
    }

    protected function isPeriodLine(string $line): bool
    {
        return preg_match('/\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $line) === 1
            || preg_match('/\b\d{4}\s+to\b/i', $line) === 1;
    }

    protected function isAmountOnlyLine(string $line): bool
    {
        return preg_match('/^[\d,.\-\s]+$/', $line) === 1
            && preg_match('/\d/', $line) === 1;
    }

    protected function isPersonResponsibleLine(string $line): bool
    {
        if (preg_match('/\b(?:Support|Training|Drive|Students|Assembly|Celebration|Week|Orientation|Distribution|Livelihood|Medicines|Payroll|Tree|Clean|Foods?|Supplies|Accommodation)\b/i', $line)) {
            return false;
        }

        return preg_match('/\b(?:Sangguniang\s+Kabataan|SK\s+(?:Chairman|Treasurer)|BADAC)\b/i', $line) === 1
            || preg_match('/\bKabataan\s+Council\b/i', $line) === 1
            || preg_match('/\bCouncil\/(?:ALS|BADAC)\b/i', $line) === 1;
    }

    protected function isLikelyActivityLine(string $line, string $phase): bool
    {
        if ($phase !== 'activities') {
            return false;
        }

        if ($this->looksLikeDescriptionLine($line)
            || $this->looksLikeExpectedResultLine($line)
            || $this->looksLikePerformanceIndicatorLine($line)
            || $this->isPeriodLine($line)
            || $this->isAmountOnlyLine($line)
            || $this->isPersonResponsibleLine($line)) {
            return false;
        }

        return mb_strlen($line) <= 120;
    }

    protected function looksLikeDescriptionLine(string $line): bool
    {
        return preg_match('/\b(?:Provide|Honorarium|Campaigning|Cost|Improve|To\s+provide|Disaster\s+preparedness|Payment|Uniforms|Premiums|Place\s+to)\b/i', $line) === 1;
    }

    protected function looksLikeExpectedResultLine(string $line): bool
    {
        return preg_match('/\b(?:Increased|Decreased|Improved|Disaster|Healthier|Active)\b/i', $line) === 1;
    }

    protected function looksLikePerformanceIndicatorLine(string $line): bool
    {
        return preg_match('/\b(?:Percentage|Number\s+of)\b/i', $line) === 1;
    }

    /**
     * @return list<string>
     */
    protected function parseInlineAmounts(string $line): array
    {
        preg_match_all('/[\d,]+(?:\.\d{2}|,\d{2})/', $line, $matches);

        return array_values(array_filter(array_map(
            fn (string $amount) => $this->parseAmount($amount),
            $matches[0] ?? []
        ), fn (?string $amount) => $amount !== null));
    }

    /**
     * @param  list<string|null>  $amounts
     * @return list<string|null>
     */
    protected function normalizeBudgetAmountList(array $amounts, int $expectedCount): array
    {
        $amounts = array_values(array_filter(
            array_map(fn ($amount) => $this->parseAmount($amount), $amounts),
            fn (?string $amount) => $amount !== null
        ));

        if ($expectedCount <= 0) {
            return $amounts;
        }

        if (count($amounts) === $expectedCount * 2) {
            return array_slice($amounts, 0, $expectedCount);
        }

        if (count($amounts) > $expectedCount) {
            return array_slice($amounts, 0, $expectedCount);
        }

        return $amounts;
    }

    /**
     * @return list<string|null>
     */
    protected function parseAmountsFromCell(?string $cell): array
    {
        if ($cell === null || trim($cell) === '') {
            return [];
        }

        $amounts = [];
        foreach (preg_split('/\R/u', $cell) ?: [] as $line) {
            foreach ($this->parseInlineAmounts($line) as $amount) {
                $amounts[] = $amount;
            }
        }

        return $amounts;
    }

    /**
     * @param  list<string>  $lines
     */
    protected function joinTextLines(array $lines): ?string
    {
        $joined = trim(implode(' ', array_filter(array_map('trim', $lines))));

        return $joined !== '' ? $joined : null;
    }

    protected function canonicalYouthProgramName(?string $letter, ?string $fallback = null): ?string
    {
        return $fallback;
    }

    protected function isValidYouthProgramLetter(string $letter): bool
    {
        return preg_match('/^[A-J]$/', strtoupper($letter)) === 1;
    }

    protected function stripProgramLetterPrefix(string $label): string
    {
        $cleaned = preg_replace('/^[A-J]\.\s*/i', '', $label) ?? $label;

        return trim(preg_replace('/\s+/u', ' ', $cleaned) ?? $cleaned);
    }

    /**
     * @return array{
     *     region: ?string,
     *     province: ?string,
     *     municipality: ?string,
     *     sk_council_name: ?string,
     *     barangay_estimated_budget: ?string,
     *     sk_fund_amount: ?string,
     *     total_expenditure: ?string,
     *     prepared_by_name: ?string,
     *     prepared_by_position: ?string,
     *     approved_by_name: ?string,
     *     approved_by_position: ?string,
     *     line_items: list<array<string, mixed>>
     * }
     */
    public function parseDocumentHtml(string $html): array
    {
        if (trim($html) === '') {
            return $this->emptyParsedMetadata();
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $budgetInputs = $xpath->query("//*[contains(@class, 'abyip-budget-input')]");
        $budgetValues = [];
        if ($budgetInputs instanceof DOMNodeList) {
            foreach ($budgetInputs as $input) {
                if ($input instanceof DOMElement) {
                    $budgetValues[] = $this->parseAmount($input->getAttribute('value') ?: $input->textContent);
                }
            }
        }

        $signatureNames = $xpath->query("//*[contains(@class, 'signature-name')]");
        $preparedBy = null;
        $approvedBy = null;
        if ($signatureNames instanceof DOMNodeList) {
            $names = [];
            foreach ($signatureNames as $node) {
                $text = trim($node->textContent ?? '');
                if ($text !== '') {
                    $names[] = $text;
                }
            }
            $preparedBy = $names[0] ?? null;
            $approvedBy = $names[1] ?? null;
        }

        $lineItems = $this->parseLineItemsFromTable($xpath);

        $docLines = $xpath->query("//*[contains(@class, 'abyip-doc-line')]");
        $lineTexts = [];
        if ($docLines instanceof DOMNodeList) {
            foreach ($docLines as $node) {
                $text = trim($node->textContent ?? '');
                if ($text !== '') {
                    $lineTexts[] = $text;
                }
            }
        }

        return [
            'country' => $lineTexts[0] ?? 'Republic of the Philippines',
            'region' => $lineTexts[1] ?? null,
            'province' => $lineTexts[2] ?? null,
            'municipality' => $lineTexts[3] ?? null,
            'barangay_name' => $lineTexts[4] ?? null,
            'document_title' => $lineTexts[5] ?? null,
            'fiscal_year' => $this->extractFiscalYearFromText(implode(' ', $lineTexts)),
            'sk_council_name' => $this->textFromQuery($xpath, "//*[contains(@class, 'abyip-doc-sk')]"),
            'barangay_estimated_budget' => $budgetValues[0] ?? null,
            'sk_fund_amount' => $budgetValues[1] ?? null,
            'total_budget' => $this->extractTotalFromRows($lineItems),
            'prepared_by' => $preparedBy,
            'prepared_position' => 'SK Chairperson',
            'approved_by' => $approvedBy,
            'approved_position' => 'Barangay Chairman',
            'line_items' => array_values(array_filter($lineItems, fn (array $item) => $this->rowHasContent($item))),
            'sk_youth_development_and_empowerment_programs' => $this->finalizeYouthPrograms(
                $this->buildYouthProgramsFromLineItems($lineItems)
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseLineItemsFromTable(DOMXPath $xpath): array
    {
        $lineItems = [];
        $inYouthSection = false;
        $currentSection = null;
        $currentCategory = null;
        $currentYouthLetter = null;
        $currentYouthName = null;
        $nextYouthLetterIndex = 0;

        $rows = $xpath->query("//table[contains(@class, 'abyip-document-table')]//tbody/tr");
        if (! $rows instanceof DOMNodeList) {
            return [];
        }

        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            $item = $this->parseTableRow($row);
            $label = trim((string) ($item['ppa_name'] ?? ''));

            if (($item['row_type'] ?? '') === 'subsection' && stripos($label, 'SK YOUTH DEVELOPMENT') !== false) {
                $inYouthSection = true;
                $currentSection = 'SK Youth Development and Empowerment Programs';
                $item['program_section'] = $currentSection;
                $lineItems[] = $item;

                continue;
            }

            if (($item['row_type'] ?? '') === 'section') {
                $inYouthSection = stripos($label, 'EXPENDITURE') !== false ? $inYouthSection : false;
                $currentSection = $label !== '' ? $label : $currentSection;
            }

            if ($inYouthSection && ($item['row_type'] ?? '') === 'category') {
                [$letter, $name] = $this->resolveYouthProgramIdentity(
                    $label,
                    $nextYouthLetterIndex
                );
                if ($letter !== null) {
                    $nextYouthLetterIndex = max(
                        $nextYouthLetterIndex,
                        array_search($letter, self::YOUTH_PROGRAM_LETTERS, true) + 1
                    );
                }
                $currentYouthLetter = $letter;
                $currentYouthName = $name;
                $currentCategory = $name;
                $item['program_section'] = $currentSection;
                $item['program_category'] = $currentCategory;
                $item['youth_program_letter'] = $currentYouthLetter;
                $item['youth_program_name'] = $currentYouthName;
            } elseif ($inYouthSection && ($item['row_type'] ?? '') === 'data') {
                $item['program_section'] = $currentSection;
                $item['program_category'] = $currentCategory;
                $item['youth_program_letter'] = $currentYouthLetter;
                $item['youth_program_name'] = $currentYouthName;
            } elseif ($currentSection !== null) {
                $item['program_section'] = $currentSection;
                if ($currentCategory !== null) {
                    $item['program_category'] = $currentCategory;
                }
            }

            $lineItems[] = $item;
        }

        return $lineItems;
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @return list<array<string, mixed>>
     */
    protected function buildYouthProgramsFromLineItems(array $lineItems): array
    {
        $programs = [];

        foreach ($lineItems as $item) {
            if (($item['program_section'] ?? '') !== 'SK Youth Development and Empowerment Programs') {
                continue;
            }

            $letter = $item['youth_program_letter'] ?? null;
            if ($letter === null) {
                continue;
            }

            if (($item['row_type'] ?? '') === 'category') {
                $label = trim((string) ($item['ppa_name'] ?? ''));
                $canonicalName = $this->canonicalYouthProgramName(
                    $letter,
                    $this->stripProgramLetterPrefix($label !== '' ? $label : (string) ($item['youth_program_name'] ?? ''))
                );

                if (! isset($programs[$letter])) {
                    $programs[$letter] = [
                        'letter' => $letter,
                        'label' => $letter.'. '.$canonicalName,
                        'name' => $canonicalName,
                        'activities' => [],
                        'budget_mooe' => 0,
                        'budget_co' => 0,
                        'budget_total' => 0,
                    ];
                }

                $categoryActivities = $this->extractBulletActivitiesFromText($label);
                foreach ($categoryActivities as $activityName) {
                    $programs[$letter]['activities'][] = [
                        'ppa_name' => $activityName,
                    ];
                }

                continue;
            }

            if (! isset($programs[$letter])) {
                $canonicalName = $this->canonicalYouthProgramName($letter, (string) ($item['youth_program_name'] ?? ''));
                $programs[$letter] = [
                    'letter' => $letter,
                    'label' => $letter.'. '.$canonicalName,
                    'name' => $canonicalName,
                    'activities' => [],
                    'budget_mooe' => 0,
                    'budget_co' => 0,
                    'budget_total' => 0,
                ];
            }

            if (($item['row_type'] ?? '') !== 'data') {
                continue;
            }

            $ppaCell = (string) ($item['ppa_name'] ?? '');
            $bulletActivities = $this->extractBulletActivitiesFromText($ppaCell);
            $mooeList = $this->parseAmountsFromCell((string) ($item['budget_mooe'] ?? ''));
            $coList = $this->parseAmountsFromCell((string) ($item['budget_co'] ?? ''));
            $totalList = $this->parseAmountsFromCell((string) ($item['budget_total'] ?? ''));
            $activityCount = max(1, count($bulletActivities) ?: count($programs[$letter]['activities']));
            $mooeList = $this->normalizeBudgetAmountList($mooeList, $activityCount);
            $coList = $this->normalizeBudgetAmountList($coList, $activityCount);
            $totalList = $this->normalizeBudgetAmountList($totalList, $activityCount);

            $shared = [
                'description' => $item['description'] ?? null,
                'expected_result' => $item['expected_result'] ?? null,
                'performance_indicator' => $item['performance_indicator'] ?? null,
                'period_of_implementation' => $item['period_of_implementation'] ?? null,
                'person_responsible' => $item['person_responsible'] ?? null,
            ];

            if ($bulletActivities !== []) {
                $programs[$letter]['activities'] = [];
                foreach ($bulletActivities as $index => $activityName) {
                    $activity = $this->buildYouthActivityRecord(
                        $activityName,
                        $shared,
                        $mooeList[$index] ?? ($mooeList[0] ?? null),
                        $coList[$index] ?? ($coList[0] ?? null),
                        $totalList[$index] ?? ($totalList[0] ?? null),
                    );
                    $activity['code'] = $item['code'] ?? null;
                    $programs[$letter]['activities'][] = $activity;
                }
            } elseif ($programs[$letter]['activities'] !== []) {
                foreach ($programs[$letter]['activities'] as $index => &$existingActivity) {
                    $existingActivity = array_merge(
                        $existingActivity,
                        $this->buildYouthActivityRecord(
                            $existingActivity['ppa_name'] ?? null,
                            $shared,
                            $mooeList[$index] ?? ($mooeList[0] ?? null),
                            $coList[$index] ?? ($coList[0] ?? null),
                            $totalList[$index] ?? ($totalList[0] ?? null),
                        )
                    );
                    $existingActivity['code'] = $item['code'] ?? null;
                }
                unset($existingActivity);
            } else {
                $activity = $this->buildYouthActivityRecord(
                    $ppaCell !== '' ? $this->cleanBulletText($ppaCell) : null,
                    $shared,
                    $mooeList[0] ?? null,
                    $coList[0] ?? null,
                    $totalList[0] ?? null,
                );
                $activity['code'] = $item['code'] ?? null;
                $programs[$letter]['activities'][] = $activity;
            }

            $programs[$letter]['budget_mooe'] = 0;
            $programs[$letter]['budget_co'] = 0;
            $programs[$letter]['budget_total'] = 0;

            foreach ($programs[$letter]['activities'] as $activity) {
                $programs[$letter]['budget_mooe'] += (float) ($activity['budget_mooe'] ?? 0);
                $programs[$letter]['budget_co'] += (float) ($activity['budget_co'] ?? 0);
                $programs[$letter]['budget_total'] += (float) ($activity['budget_total'] ?? 0);
            }
        }

        ksort($programs);

        return array_values($programs);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveYouthProgramIdentity(string $label, int &$nextLetterIndex): array
    {
        $letter = $this->extractYouthProgramLetter($label);
        $name = $this->resolveYouthProgramName($letter, $label);

        if ($letter !== null) {
            $index = array_search($letter, self::YOUTH_PROGRAM_LETTERS, true);
            if ($index !== false) {
                $nextLetterIndex = max($nextLetterIndex, $index + 1);
            }

            return [$letter, $name];
        }

        if ($name !== null && $nextLetterIndex < count(self::YOUTH_PROGRAM_LETTERS)) {
            $letter = self::YOUTH_PROGRAM_LETTERS[$nextLetterIndex];
            $nextLetterIndex++;
        }

        return [$letter, $name];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function parseLineItemsFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $lineItems = [];
        $lines = preg_split('/\R/u', $text) ?: [];
        $inYouthSection = false;
        $currentSection = null;
        $currentYouthLetter = null;
        $currentYouthName = null;
        $nextYouthLetterIndex = 0;

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
            if ($line === '') {
                continue;
            }

            if (stripos($line, 'SK YOUTH DEVELOPMENT') !== false) {
                $inYouthSection = true;
                $currentSection = 'SK Youth Development and Empowerment Programs';
                $lineItems[] = [
                    'row_type' => 'subsection',
                    'ppa_name' => $line,
                    'program_section' => $currentSection,
                ];

                continue;
            }

            if ($inYouthSection && preg_match('/^TOTAL\b/i', $line)) {
                break;
            }

            if (preg_match('/^([A-J])\.\s+(.+)$/iu', $line, $matches)) {
                [$letter, $name] = $this->resolveYouthProgramIdentity(
                    $matches[1].'. '.$matches[2],
                    $nextYouthLetterIndex
                );
                $currentYouthLetter = $letter;
                $currentYouthName = $name;
                $lineItems[] = [
                    'row_type' => 'category',
                    'ppa_name' => $line,
                    'program_section' => $currentSection,
                    'youth_program_letter' => $currentYouthLetter,
                    'youth_program_name' => $currentYouthName,
                ];

                continue;
            }

            if ($inYouthSection && $this->looksLikeYouthCategoryLine($line)) {
                [$letter, $name] = $this->resolveYouthProgramIdentity($line, $nextYouthLetterIndex);
                $currentYouthLetter = $letter;
                $currentYouthName = $name;
                $lineItems[] = [
                    'row_type' => 'category',
                    'ppa_name' => $line,
                    'program_section' => $currentSection,
                    'youth_program_letter' => $currentYouthLetter,
                    'youth_program_name' => $currentYouthName,
                ];

                continue;
            }

            $parsedRow = $this->parseTextTableRow($line);
            if ($parsedRow === null) {
                continue;
            }

            if ($inYouthSection) {
                $parsedRow['program_section'] = $currentSection;
                $parsedRow['youth_program_letter'] = $currentYouthLetter;
                $parsedRow['youth_program_name'] = $currentYouthName;
            } elseif ($currentSection !== null) {
                $parsedRow['program_section'] = $currentSection;
            }

            $lineItems[] = $parsedRow;
        }

        return $lineItems;
    }

    protected function looksLikeYouthCategoryLine(string $line): bool
    {
        if (preg_match('/^([A-J])\.\s/i', $line)) {
            return true;
        }

        $known = [
            'Equitable Access',
            'Environmental Protection',
            'Disaster Risk',
            'Youth Employment',
            'Health',
            'Anti-Drug',
            'Gender Sensitivity',
            'Feeding Program',
            'Sports Development',
            'Other Programs',
            'Receipts Program',
        ];

        foreach ($known as $fragment) {
            if (stripos($line, $fragment) !== false && ! preg_match('/\d/', $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseTextTableRow(string $line): ?array
    {
        if (preg_match('/^TOTAL\b/i', $line)) {
            $amount = null;

            if (preg_match_all('/([\d,]+\.\d{2})/', $line, $matches)) {
                $amount = $this->parseAmount(end($matches[1]));
            }

            return [
                'row_type' => 'total',
                'ppa_name' => 'TOTAL',
                'budget_total' => $amount,
            ];
        }

        if (preg_match(
            '/^(.+?)\s+([\d,]+(?:\.\d{2})?)\s+([\d,]+(?:\.\d{2})?)\s+([\d,]+(?:\.\d{2})?)(?:\s+(.+))?$/u',
            $line,
            $matches
        )) {
            $ppaName = trim($matches[1]);

            return [
                'row_type' => 'data',
                'ppa_name' => $ppaName !== '' ? $ppaName : null,
                'budget_mooe' => $this->parseAmount($matches[2]),
                'budget_co' => $this->parseAmount($matches[3]),
                'budget_total' => $this->parseAmount($matches[4]),
                'person_responsible' => isset($matches[5]) ? $this->extractPersonResponsibleFromValue(trim($matches[5])) : null,
            ];
        }

        if (preg_match(
            '/^(.+?)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})(?:\s+(.+))?$/u',
            $line,
            $matches
        )) {
            $ppaName = trim($matches[1]);

            return [
                'row_type' => 'data',
                'ppa_name' => $ppaName !== '' ? $ppaName : null,
                'budget_mooe' => $this->parseAmount($matches[2]),
                'budget_co' => '0.00',
                'budget_total' => $this->parseAmount($matches[3]),
                'person_responsible' => isset($matches[4]) ? $this->extractPersonResponsibleFromValue(trim($matches[4])) : null,
            ];
        }

        if (preg_match(
            '/^(.+?)\s+([\d,]+\.\d{2})(?:\s+(.+))?$/u',
            $line,
            $matches
        )) {
            $ppaName = trim($matches[1]);
            $person = isset($matches[3]) ? $this->extractPersonResponsibleFromValue(trim($matches[3])) : null;
            $amount = $this->parseAmount($matches[2]);

            if ($amount !== null && ($person !== null || ! preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)\b/i', $ppaName))) {
                return [
                    'row_type' => 'data',
                    'ppa_name' => $ppaName !== '' ? $ppaName : null,
                    'budget_mooe' => $amount,
                    'budget_co' => '0.00',
                    'budget_total' => $amount,
                    'person_responsible' => $person,
                ];
            }
        }

        if (preg_match('/^(Honoraria|MOOE|Capital Outlay|Receipts)\b/i', $line)) {
            return [
                'row_type' => 'data',
                'ppa_name' => $line,
            ];
        }

        return null;
    }

    protected function extractYouthProgramLetter(string $text): ?string
    {
        if (preg_match('/\b([A-J])\.\s/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    protected function resolveYouthProgramName(?string $letter, string $text): ?string
    {
        $canonical = $this->canonicalYouthProgramName($letter);
        if ($canonical !== null) {
            return $canonical;
        }

        $cleaned = $this->stripProgramLetterPrefix($text);

        return $cleaned !== '' ? $cleaned : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseTableRow(DOMElement $row): array
    {
        $class = $row->getAttribute('class');

        $cells = [];
        foreach ($row->getElementsByTagName('td') as $cell) {
            if ($cell instanceof DOMElement) {
                $cells[] = $this->extractCellText($cell);
            }
        }

        $label = implode(' ', array_filter($cells));
        $rowType = $this->resolveRowType($class, $label, count($cells));

        if (count($cells) < 2) {
            return [
                'row_type' => $rowType,
                'ppa_name' => $cells[0] ?? null,
            ];
        }

        if ($rowType !== 'data' && count($cells) < 10) {
            $ppaName = $cells[1] ?? ($cells[0] ?? null);

            return [
                'row_type' => $rowType,
                'ppa_name' => $ppaName !== '' ? $ppaName : ($label !== '' ? $label : null),
            ];
        }

        $period = $this->joinMultilineCell($cells[5] ?? null);
        $periodDates = $this->parsePeriodDates($period);

        return [
            'row_type' => $rowType,
            'code' => $this->cleanCellValue($cells[0] ?? null),
            'ppa_name' => $this->joinMultilineCell($cells[1] ?? null),
            'description' => $this->joinMultilineCell($cells[2] ?? null),
            'expected_result' => $this->joinMultilineCell($cells[3] ?? null),
            'performance_indicator' => $this->joinMultilineCell($cells[4] ?? null),
            'period_of_implementation' => $period,
            'period_start' => $periodDates['start'],
            'period_end' => $periodDates['end'],
            'budget_mooe' => $this->joinMultilineCell($cells[6] ?? null),
            'budget_co' => $this->joinMultilineCell($cells[7] ?? null),
            'budget_total' => $this->joinMultilineCell($cells[8] ?? null),
            'person_responsible' => $this->joinMultilineCell($cells[9] ?? null),
        ];
    }

    protected function extractCellText(DOMElement $cell): string
    {
        $text = trim($this->nodeTextWithBreaks($cell));

        return preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
    }

    protected function nodeTextWithBreaks(\DOMNode $node): string
    {
        if ($node->nodeName === 'br') {
            return "\n";
        }

        if (! $node->hasChildNodes()) {
            return $node->nodeValue ?? '';
        }

        $buffer = '';
        foreach ($node->childNodes as $child) {
            $buffer .= $this->nodeTextWithBreaks($child);
        }

        return $buffer;
    }

    protected function cleanCellValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $cleaned = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return $cleaned !== '' ? $cleaned : null;
    }

    protected function joinMultilineCell(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $lines = array_values(array_filter(array_map(
            fn (string $line) => trim(preg_replace('/\s+/u', ' ', $line) ?? $line),
            preg_split('/\R/u', $value) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        return implode("\n", $lines);
    }

    protected function textFromQuery(DOMXPath $xpath, string $query): ?string
    {
        $nodes = $xpath->query($query);
        if (! $nodes instanceof DOMNodeList || $nodes->length === 0) {
            return null;
        }

        $text = trim($nodes->item(0)?->textContent ?? '');

        return $text !== '' ? $text : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function rowHasContent(array $item): bool
    {
        foreach (['code', 'ppa_name', 'description', 'expected_result', 'performance_indicator', 'person_responsible'] as $field) {
            if (! empty($item[$field])) {
                return true;
            }
        }

        foreach (['budget_mooe', 'budget_co', 'budget_total'] as $field) {
            if (! empty($item[$field]) && (float) $item[$field] > 0) {
                return true;
            }
        }

        return ($item['row_type'] ?? 'data') !== 'data';
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     */
    protected function extractFiscalYearFromText(string $text): ?int
    {
        if (preg_match('/\bCY\s*(\d{4})\b/i', $text, $match)) {
            return (int) $match[1];
        }

        if (preg_match('/CY(\d{4})/i', preg_replace('/\s+/u', '', $text) ?? $text, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    protected function extractTotalFromRows(array $lineItems): ?string
    {
        $lastTotal = null;

        foreach ($lineItems as $item) {
            if (($item['row_type'] ?? '') === 'total' && ! empty($item['budget_total'])) {
                $lastTotal = $item['budget_total'];
            }
        }

        return $lastTotal;
    }

    protected function resolveRowType(string $class, string $label, int $cellCount): string
    {
        if (str_contains($class, 'subsection-header')) {
            return 'subsection';
        }

        if (str_contains($class, 'section-header')) {
            return 'section';
        }

        if (str_contains($class, 'category-header')) {
            return 'category';
        }

        if (str_contains($class, 'total-row')) {
            return 'total';
        }

        $upper = strtoupper($label);

        if (preg_match('/^TOTAL\b/', $upper) || str_contains($upper, 'TOTAL EXPENDITURE')) {
            return 'total';
        }

        if (str_contains($upper, 'SK YOUTH DEVELOPMENT')) {
            return 'subsection';
        }

        if (str_contains($upper, 'EXPENDITURE') || str_contains($upper, 'RECEIPTS')) {
            return 'section';
        }

        if ($cellCount <= 3 && (
            preg_match('/\b([A-J])\.\s/i', $label)
            || $this->extractYouthProgramLetter($label) !== null
        )) {
            return 'category';
        }

        return 'data';
    }

    protected function parseAmount(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '-') {
            return null;
        }

        if (preg_match('/([\d,]+\.\d{2})/', $raw, $match)) {
            $normalized = str_replace(',', '', $match[1]);

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        if (preg_match('/^([\d,]+),(\d{2})$/', $raw, $matches)) {
            $normalized = str_replace(',', '', $matches[1]).'.'.$matches[2];

            return $this->isValidNumericAmount($normalized) ? $normalized : null;
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', $raw) ?? '';

        return $this->isValidNumericAmount($cleaned) ? $cleaned : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveExtractedText(array $data): string
    {
        $clientText = (string) ($data['extracted_text'] ?? '');
        $pdfData = (string) ($data['pdf_data'] ?? '');

        if ($pdfData === '') {
            return $clientText;
        }

        $serverText = $this->pdfExtractionService->extractTextFromBase64($pdfData);

        return $this->pdfExtractionService->mergeExtractedTexts($clientText, $serverText);
    }

    /**
     * @return array<string, mixed>
     */
    public function reparseDocument(Abyip $document): array
    {
        if ($document->row_type !== Abyip::ROW_DOCUMENT) {
            throw ValidationException::withMessages([
                'document' => ['Only ABYIP document rows can be reparsed.'],
            ]);
        }

        if (empty($document->pdf_data)) {
            throw ValidationException::withMessages([
                'document' => ['This ABYIP record has no stored PDF to reparse.'],
            ]);
        }

        $extractedText = $this->resolveExtractedText([
            'extracted_text' => '',
            'pdf_data' => $document->pdf_data,
        ]);

        $parsed = $this->parseUploadedDocument('', $extractedText);
        $parsed = $this->normalizeDocumentForInsert($parsed);

        DB::transaction(function () use ($document, $parsed) {
            $documentPayload = [
                'country' => $parsed['country'] ?? $document->country,
                'region' => $parsed['region'] ?? $document->region,
                'province' => $parsed['province'] ?? $document->province,
                'municipality' => $parsed['municipality'] ?? $document->municipality,
                'barangay_name' => $parsed['barangay_name'] ?? $document->barangay_name,
                'document_title' => $parsed['document_title'] ?? $document->document_title,
                'sk_council_name' => $parsed['sk_council_name'] ?? $document->sk_council_name,
                'barangay_estimated_budget' => $parsed['barangay_estimated_budget'],
                'sk_fund_percentage' => $parsed['sk_fund_percentage'],
                'sk_fund_amount' => $parsed['sk_fund_amount'],
                'total_budget' => $parsed['total_budget'],
                'prepared_by' => $parsed['prepared_by'] ?? $document->prepared_by,
                'prepared_position' => $parsed['prepared_position'] ?? $document->prepared_position,
                'prepared_by_name' => $parsed['prepared_by_name'] ?? $document->prepared_by_name,
                'prepared_by_position' => $parsed['prepared_by_position'] ?? $document->prepared_by_position,
                'approved_by' => $parsed['approved_by'] ?? $document->approved_by,
                'approved_position' => $parsed['approved_position'] ?? $document->approved_position,
                'approved_by_name' => $parsed['approved_by_name'] ?? $document->approved_by_name,
                'approved_by_position' => $parsed['approved_by_position'] ?? $document->approved_by_position,
            ];

            Log::info('ABYIP document reparse payload', $documentPayload);

            $document->update($documentPayload);

            Abyip::query()
                ->where('document_id', $document->id)
                ->where('id', '!=', $document->id)
                ->delete();

            $this->syncAbyipLines(
                $document->fresh(),
                $parsed['line_items'] ?? [],
                $parsed['sk_youth_development_and_empowerment_programs'] ?? []
            );
        });

        return $this->formatDocument($document->fresh(['lines.children']));
    }

    protected function preferBudgetAmount(mixed $existing, mixed $incoming, string $field): mixed
    {
        if (! in_array($field, ['barangay_estimated_budget', 'sk_fund_amount', 'total_budget', 'budget_mooe', 'budget_co', 'budget_total'], true)) {
            return $incoming ?? $existing;
        }

        $existingAmount = (float) $this->numericAmount($existing);
        $incomingAmount = (float) $this->numericAmount($incoming);

        if ($incomingAmount <= 0 && $existingAmount <= 0) {
            return '0.00';
        }

        if ($incomingAmount <= 0) {
            return $this->numericAmount($existing);
        }

        if ($existingAmount <= 0) {
            return $this->numericAmount($incoming);
        }

        return $incomingAmount >= $existingAmount
            ? $this->numericAmount($incoming)
            : $this->numericAmount($existing);
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    protected function normalizeDocumentForInsert(array $parsed): array
    {
        $parsed = $this->normalizeDocumentBudgets($parsed);

        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);
        $total = (float) $this->numericAmount($parsed['total_budget'] ?? 0);
        $percentage = (float) $this->numericAmount($parsed['sk_fund_percentage'] ?? 10);

        if ($percentage <= 0) {
            $percentage = 10.0;
        }

        if ($skFund > 0 && $barangay <= 0) {
            $barangay = round($skFund / ($percentage / 100), 2);
        }

        if ($barangay > 0 && $skFund <= 0) {
            $skFund = round($barangay * ($percentage / 100), 2);
        }

        if ($total <= 0 && $skFund > 0) {
            $total = $skFund;
        }

        $parsed['barangay_estimated_budget'] = $this->numericAmount($barangay);
        $parsed['sk_fund_percentage'] = $this->numericAmount($percentage);
        $parsed['sk_fund_amount'] = $this->numericAmount($skFund);
        $parsed['total_budget'] = $this->numericAmount($total);

        return $parsed;
    }

    protected function numericAmount(mixed $value): string
    {
        $parsed = $this->parseAmount($value);

        if ($parsed !== null && is_numeric($parsed)) {
            return number_format((float) $parsed, 2, '.', '');
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return '0.00';
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    protected function normalizeDocumentBudgets(array $parsed): array
    {
        $barangay = (float) $this->numericAmount($parsed['barangay_estimated_budget'] ?? 0);
        $skFund = (float) $this->numericAmount($parsed['sk_fund_amount'] ?? 0);
        $total = (float) $this->numericAmount($parsed['total_budget'] ?? 0);
        $percentage = (float) $this->numericAmount($parsed['sk_fund_percentage'] ?? 10);

        if ($percentage <= 0) {
            $percentage = 10.0;
        }

        if ($skFund > 1000 && ($barangay <= 0 || $barangay < ($skFund / 2))) {
            $parsed['barangay_estimated_budget'] = number_format($skFund / ($percentage / 100), 2, '.', '');
        }

        if ($total <= 0 && $skFund > 0) {
            $parsed['total_budget'] = number_format($skFund, 2, '.', '');
        }

        if ($skFund <= 0 && $barangay > 0) {
            $parsed['sk_fund_amount'] = number_format($barangay * ($percentage / 100), 2, '.', '');
        }

        return $parsed;
    }

    /**
     * @return array{start: ?string, end: ?string}
     */
    protected function parsePeriodDates(?string $period): array
    {
        if ($period === null || trim($period) === '') {
            return ['start' => null, 'end' => null];
        }

        if (preg_match('/(\w+\s+\d{1,2},?\s+\d{4})\s+to\s+(\w+\s+\d{1,2},?\s+\d{4})/i', $period, $matches)) {
            try {
                return [
                    'start' => Carbon::parse($matches[1])->toDateString(),
                    'end' => Carbon::parse($matches[2])->toDateString(),
                ];
            } catch (\Throwable) {
                return ['start' => null, 'end' => null];
            }
        }

        return ['start' => null, 'end' => null];
    }
}
