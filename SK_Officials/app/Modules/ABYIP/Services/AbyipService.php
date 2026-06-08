<?php

namespace App\Modules\ABYIP\Services;

use App\Models\AbyipDocument;
use App\Models\AbyipProgram;
use App\Models\AbyipProgramActivity;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AbyipService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForBarangay(User $user): Collection
    {
        return AbyipDocument::query()
            ->where('barangay_id', $user->barangay_id)
            ->orderByDesc('calendar_year')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AbyipDocument $document) => $this->formatDocument($document, forList: true));
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $documentId): array
    {
        $document = $this->findDocumentModel($user, $documentId);

        return $this->formatDocument($document->load(['programs.activities', 'activities']));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function store(User $user, array $data): array
    {
        $calendarYear = (int) ($data['calendar_year'] ?? now()->year);
        $this->assertUniqueYear($user, $calendarYear);

        $sourceType = (string) ($data['source_type'] ?? AbyipDocument::SOURCE_WORD);
        $parsed = $sourceType === AbyipDocument::SOURCE_PDF
            ? $this->parseUploadedDocument(
                documentHtml: '',
                extractedText: (string) ($data['extracted_text'] ?? '')
            )
            : $this->parseUploadedDocument(
                documentHtml: (string) ($data['document_html'] ?? ''),
                extractedText: ''
            );

        $document = AbyipDocument::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'title' => trim((string) ($data['title'] ?? 'ABYIP CY '.$calendarYear)),
            'calendar_year' => $calendarYear,
            'region' => $parsed['region'] ?? 'IV-A CALABARZON',
            'province' => $parsed['province'] ?? 'Laguna',
            'municipality' => $parsed['municipality'] ?? 'Santa Cruz',
            'sk_council_name' => $parsed['sk_council_name'],
            'barangay_estimated_budget' => $parsed['barangay_estimated_budget'],
            'sk_fund_amount' => $parsed['sk_fund_amount'],
            'total_expenditure' => $parsed['total_expenditure'],
            'prepared_by_name' => $parsed['prepared_by_name'],
            'prepared_by_position' => $parsed['prepared_by_position'] ?? 'SK Chairperson',
            'approved_by_name' => $parsed['approved_by_name'],
            'approved_by_position' => $parsed['approved_by_position'] ?? 'Barangay Chairman',
            'source_type' => $sourceType,
            'document_html' => $data['document_html'] ?? null,
            'pdf_data' => $data['pdf_data'] ?? null,
        ]);

        $this->syncProgramsAndActivities(
            $document,
            $parsed['line_items'] ?? [],
            $parsed['sk_youth_development_and_empowerment_programs'] ?? []
        );

        return $this->formatDocument($document->fresh(['programs.activities', 'activities']));
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
        $this->findDocumentModel($user, $documentId)->delete();
    }

    protected function findDocumentModel(User $user, int $documentId): AbyipDocument
    {
        $document = AbyipDocument::query()
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

    protected function assertUniqueYear(User $user, int $calendarYear): void
    {
        $exists = AbyipDocument::query()
            ->where('barangay_id', $user->barangay_id)
            ->where('calendar_year', $calendarYear)
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
    protected function formatDocument(AbyipDocument $document, bool $forList = false): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'date_created' => $document->created_at?->toIso8601String(),
            'calendar_year' => $document->calendar_year,
            'source_type' => $document->source_type,
            'document_html' => $forList ? null : $document->document_html,
            'pdf_data' => $forList ? null : $document->pdf_data,
            'barangay_estimated_budget' => $document->barangay_estimated_budget,
            'sk_fund_amount' => $document->sk_fund_amount,
            'total_expenditure' => $document->total_expenditure,
            'prepared_by_name' => $document->prepared_by_name,
            'approved_by_name' => $document->approved_by_name,
            'programs' => $document->relationLoaded('programs')
                ? $document->programs->map(fn (AbyipProgram $program) => [
                    'id' => $program->id,
                    'program_letter' => $program->program_letter,
                    'program_name' => $program->program_name,
                    'activities' => $program->relationLoaded('activities')
                        ? $program->activities->map(fn (AbyipProgramActivity $activity) => $this->formatActivity($activity))->values()->all()
                        : [],
                ])->values()->all()
                : [],
            'line_items' => $document->relationLoaded('activities')
                ? $document->activities
                    ->whereNull('program_id')
                    ->map(fn (AbyipProgramActivity $activity) => $this->formatActivity($activity))
                    ->values()
                    ->all()
                : [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @param  list<array<string, mixed>>  $youthPrograms
     */
    protected function syncProgramsAndActivities(
        AbyipDocument $document,
        array $lineItems,
        array $youthPrograms
    ): void {
        $programMap = [];
        $sortOrder = 0;

        foreach ($youthPrograms as $program) {
            $letter = strtoupper((string) ($program['letter'] ?? ''));
            $name = trim((string) ($program['name'] ?? $this->stripProgramLetterPrefix((string) ($program['label'] ?? ''))));

            if ($letter === '' || ! $this->isValidYouthProgramLetter($letter) || $name === '') {
                continue;
            }

            $model = AbyipProgram::create([
                'abyip_id' => $document->id,
                'program_letter' => $letter,
                'program_name' => $name,
                'sort_order' => $sortOrder++,
            ]);

            $programMap[$letter] = $model->id;

            foreach ($program['activities'] ?? [] as $activity) {
                $this->createActivityRow($document->id, $model->id, $activity, $sortOrder++, [
                    'program_section' => 'SK Youth Development and Empowerment Programs',
                    'row_type' => 'data',
                ]);
            }
        }

        foreach ($lineItems as $item) {
            if (($item['row_type'] ?? '') !== 'data') {
                continue;
            }

            if (($item['program_section'] ?? '') === 'SK Youth Development and Empowerment Programs') {
                continue;
            }

            $this->createActivityRow($document->id, null, $item, $sortOrder++, [
                'program_section' => $item['program_section'] ?? null,
                'row_type' => $item['row_type'] ?? 'data',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $defaults
     */
    protected function createActivityRow(
        int $abyipId,
        ?int $programId,
        array $item,
        int $sortOrder,
        array $defaults = []
    ): void {
        $ppaName = trim((string) ($item['ppa_name'] ?? ''));
        $activityName = $programId !== null ? $ppaName : null;

        AbyipProgramActivity::create([
            'abyip_id' => $abyipId,
            'program_id' => $programId,
            'activity_name' => $activityName !== '' ? $activityName : null,
            'code' => $item['code'] ?? null,
            'ppas' => $ppaName !== '' ? $ppaName : null,
            'description' => $item['description'] ?? null,
            'expected_result' => $item['expected_result'] ?? null,
            'performance_indicator' => $item['performance_indicator'] ?? null,
            'period_of_implementation' => $item['period_of_implementation'] ?? null,
            'budget' => $item['budget_total'] ?? $item['budget'] ?? null,
            'person_responsible' => $item['person_responsible'] ?? null,
            'mooe' => $item['budget_mooe'] ?? null,
            'co' => $item['budget_co'] ?? null,
            'total' => $item['budget_total'] ?? null,
            'row_type' => $defaults['row_type'] ?? ($item['row_type'] ?? null),
            'program_section' => $defaults['program_section'] ?? ($item['program_section'] ?? null),
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatActivity(AbyipProgramActivity $activity): array
    {
        return [
            'id' => $activity->id,
            'program_id' => $activity->program_id,
            'activity_name' => $activity->activity_name,
            'code' => $activity->code,
            'ppas' => $activity->ppas,
            'description' => $activity->description,
            'expected_result' => $activity->expected_result,
            'performance_indicator' => $activity->performance_indicator,
            'period_of_implementation' => $activity->period_of_implementation,
            'budget' => $activity->budget,
            'person_responsible' => $activity->person_responsible,
            'mooe' => $activity->mooe,
            'co' => $activity->co,
            'total' => $activity->total,
            'row_type' => $activity->row_type,
            'program_section' => $activity->program_section,
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
            return $this->parseDocumentHtml($documentHtml);
        }

        $parsed = $this->emptyParsedMetadata();
        $parsed['sk_youth_development_and_empowerment_programs'] = $this->parseYouthProgramsFromText($extractedText);

        return $parsed;
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
            'region' => null,
            'province' => null,
            'municipality' => null,
            'sk_council_name' => null,
            'barangay_estimated_budget' => null,
            'sk_fund_amount' => null,
            'total_expenditure' => null,
            'prepared_by_name' => null,
            'prepared_by_position' => null,
            'approved_by_name' => null,
            'approved_by_position' => null,
            'line_items' => [],
            'sk_youth_development_and_empowerment_programs' => [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseYouthProgramsFromText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $programs = [];
        $lines = preg_split('/\R/u', $text) ?: [];
        $inYouthSection = false;

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?? $line);
            if ($line === '') {
                continue;
            }

            if (stripos($line, 'SK YOUTH DEVELOPMENT') !== false) {
                $inYouthSection = true;
                continue;
            }

            if ($inYouthSection && preg_match('/^TOTAL\b/i', $line)) {
                break;
            }

            if (! $inYouthSection && ! preg_match('/\b([A-J])\.\s/i', $line)) {
                continue;
            }

            if (preg_match('/^([A-J])\.\s*(.+)$/iu', $line, $matches)) {
                $this->appendDetectedYouthProgram($programs, $matches[1], $matches[2]);
            }
        }

        if ($programs === []) {
            $normalized = preg_replace('/\s+/u', ' ', $text) ?? $text;
            $searchText = $normalized;

            if (preg_match('/SK\s+YOUTH\s+DEVELOPMENT/iu', $normalized, $sectionMatch, PREG_OFFSET_CAPTURE)) {
                $searchText = substr($normalized, (int) $sectionMatch[0][1]);
            }

            if (preg_match('/\bTOTAL\s+EXPENDITURE\b/iu', $searchText, $endMatch, PREG_OFFSET_CAPTURE)) {
                $searchText = substr($searchText, 0, (int) $endMatch[0][1]);
            }

            preg_match_all(
                '/\b([A-J])\.\s*([^•]+?)(?=\s+[A-J]\.\s|\s+TOTAL\b|$)/iu',
                $searchText,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $this->appendDetectedYouthProgram($programs, $match[1], $match[2]);
            }
        }

        ksort($programs);

        return array_values($programs);
    }

    /**
     * @param  array<string, array<string, mixed>>  $programs
     */
    protected function appendDetectedYouthProgram(array &$programs, string $letter, string $rawName): void
    {
        $letter = strtoupper($letter);
        if (! $this->isValidYouthProgramLetter($letter) || isset($programs[$letter])) {
            return;
        }

        $name = trim(preg_replace('/\s+/u', ' ', preg_replace('/\s*•.*$/u', '', $rawName) ?? $rawName) ?? '');
        if ($name === '') {
            return;
        }

        $programs[$letter] = [
            'letter' => $letter,
            'label' => $letter.'. '.$name,
            'name' => $name,
            'activities' => [],
            'budget_mooe' => 0,
            'budget_co' => 0,
            'budget_total' => 0,
        ];
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

        $dom = new DOMDocument();
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

        return [
            'region' => $this->textFromQuery($xpath, "//*[contains(@class, 'abyip-doc-line-sm')][4]"),
            'province' => $this->textFromQuery($xpath, "//*[contains(@class, 'abyip-doc-line-sm')][3]"),
            'municipality' => $this->textFromQuery($xpath, "//*[contains(@class, 'abyip-doc-line-sm')][4]"),
            'sk_council_name' => $this->textFromQuery($xpath, "//*[contains(@class, 'abyip-doc-sk')]"),
            'barangay_estimated_budget' => $budgetValues[0] ?? null,
            'sk_fund_amount' => $budgetValues[1] ?? null,
            'total_expenditure' => $this->extractTotalFromRows($lineItems),
            'prepared_by_name' => $preparedBy,
            'prepared_by_position' => 'SK Chairperson',
            'approved_by_name' => $approvedBy,
            'approved_by_position' => 'Barangay Chairman',
            'line_items' => array_values(array_filter($lineItems, fn (array $item) => $this->rowHasContent($item))),
            'sk_youth_development_and_empowerment_programs' => $this->buildYouthProgramsFromLineItems($lineItems),
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
                $letter = $this->extractYouthProgramLetter($label);
                $name = $this->resolveYouthProgramName($letter, $label);
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

            if (! isset($programs[$letter])) {
                $label = trim((string) ($item['ppa_name'] ?? ''));
                if ($label === '' && ! empty($item['youth_program_name'])) {
                    $label = $letter.'. '.trim((string) $item['youth_program_name']);
                }

                $programs[$letter] = [
                    'letter' => $letter,
                    'label' => $label,
                    'name' => $this->stripProgramLetterPrefix($label !== '' ? $label : (string) ($item['youth_program_name'] ?? '')),
                    'activities' => [],
                    'budget_mooe' => 0,
                    'budget_co' => 0,
                    'budget_total' => 0,
                ];
            }

            if (($item['row_type'] ?? '') !== 'data') {
                continue;
            }

            $programs[$letter]['activities'][] = [
                'code' => $item['code'] ?? null,
                'ppa_name' => $item['ppa_name'] ?? null,
                'description' => $item['description'] ?? null,
                'expected_result' => $item['expected_result'] ?? null,
                'performance_indicator' => $item['performance_indicator'] ?? null,
                'period_of_implementation' => $item['period_of_implementation'] ?? null,
                'budget_mooe' => (float) ($item['budget_mooe'] ?? 0),
                'budget_co' => (float) ($item['budget_co'] ?? 0),
                'budget_total' => (float) ($item['budget_total'] ?? 0),
                'person_responsible' => $item['person_responsible'] ?? null,
            ];

            $programs[$letter]['budget_mooe'] += (float) ($item['budget_mooe'] ?? 0);
            $programs[$letter]['budget_co'] += (float) ($item['budget_co'] ?? 0);
            $programs[$letter]['budget_total'] += (float) ($item['budget_total'] ?? 0);
        }

        ksort($programs);

        return array_values($programs);
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
            $cells[] = trim(preg_replace('/\s+/', ' ', $cell->textContent ?? '') ?? '');
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
            return [
                'row_type' => $rowType,
                'ppa_name' => $label !== '' ? $label : null,
            ];
        }

        $period = $cells[5] ?? null;
        $periodDates = $this->parsePeriodDates($period);

        return [
            'row_type' => $rowType,
            'code' => $cells[0] ?? null,
            'ppa_name' => $cells[1] ?? null,
            'description' => $cells[2] ?? null,
            'expected_result' => $cells[3] ?? null,
            'performance_indicator' => $cells[4] ?? null,
            'period_of_implementation' => $period,
            'period_start' => $periodDates['start'],
            'period_end' => $periodDates['end'],
            'budget_mooe' => $this->parseAmount($cells[6] ?? null),
            'budget_co' => $this->parseAmount($cells[7] ?? null),
            'budget_total' => $this->parseAmount($cells[8] ?? null),
            'person_responsible' => $cells[9] ?? null,
        ];
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
    protected function extractTotalFromRows(array $lineItems): ?string
    {
        foreach ($lineItems as $item) {
            if (($item['row_type'] ?? '') === 'total') {
                return $item['budget_total'] ?? null;
            }
        }

        return null;
    }

    protected function resolveRowType(string $class, string $label, int $cellCount): string
    {
        if (str_contains($class, 'section-header')) {
            return 'section';
        }

        if (str_contains($class, 'subsection-header')) {
            return 'subsection';
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

        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value) ?? '';

        return $cleaned !== '' ? $cleaned : null;
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
                    'start' => \Carbon\Carbon::parse($matches[1])->toDateString(),
                    'end' => \Carbon\Carbon::parse($matches[2])->toDateString(),
                ];
            } catch (\Throwable) {
                return ['start' => null, 'end' => null];
            }
        }

        return ['start' => null, 'end' => null];
    }
}
