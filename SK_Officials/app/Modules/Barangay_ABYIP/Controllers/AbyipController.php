<?php

namespace App\Modules\Barangay_ABYIP\Controllers;

use App\Modules\Barangay_ABYIP\Services\AbyipService;
use App\Services\AbyipSubmissionScheduleGate;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AbyipController extends Controller
{
    public function __construct(
        private readonly AbyipService $abyipService,
        private readonly SkOfficialActivityService $activityService,
        private readonly AbyipSubmissionScheduleGate $scheduleGate,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->abyipService->listForBarangay($request->user()),
            'years' => $this->abyipService->distinctYearsForBarangay($request->user())->values()->all(),
            'submission' => $this->scheduleGate->submissionStatus($request->user()),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $document = $this->abyipService->findForBarangay($request->user(), $id);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 404);
        }

        return response()->json(['data' => $document]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge($this->importPayloadRules(), [
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(['word', 'pdf'])],
            'calendar_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'document_html' => ['nullable', 'string'],
            'pdf_data' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
        ]));

        if ($validated['source_type'] === 'pdf' && empty($validated['pdf_data'])) {
            return response()->json([
                'message' => 'PDF data is required for PDF uploads.',
            ], 422);
        }

        if ($validated['source_type'] === 'word' && empty($validated['document_html'])) {
            return response()->json([
                'message' => 'Document content is required for Word uploads.',
            ], 422);
        }

        try {
            $this->scheduleGate->assertCanSubmit($request->user());
            $document = $this->abyipService->store($request->user(), $validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $this->activityService->log(
            $request->user(),
            'abyip.upload',
            'Uploaded ABYIP document: '.($document['title'] ?? $validated['title']),
            ['document_id' => $document['id'] ?? null, 'fiscal_year' => $document['fiscal_year'] ?? null]
        );

        return response()->json([
            'message' => 'ABYIP imported successfully.',
            'data' => $document,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->abyipService->delete($request->user(), $id);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 404);
        }

        $this->activityService->log(
            $request->user(),
            'abyip.delete',
            'Deleted ABYIP record #'.$id
        );

        return response()->json([
            'message' => 'ABYIP deleted successfully.',
        ]);
    }

    public function resubmit(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(array_merge($this->importPayloadRules(), [
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(['word', 'pdf'])],
            'document_html' => ['nullable', 'string'],
            'pdf_data' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
        ]));

        if ($validated['source_type'] === 'pdf' && empty($validated['pdf_data'])) {
            return response()->json([
                'message' => 'PDF data is required for resubmission.',
            ], 422);
        }

        if ($validated['source_type'] === 'word' && empty($validated['document_html'])) {
            return response()->json([
                'message' => 'Document content is required for resubmission.',
            ], 422);
        }

        try {
            $this->scheduleGate->assertCanSubmit($request->user());
            $document = $this->abyipService->resubmit($request->user(), $id, $validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        $this->activityService->log(
            $request->user(),
            'abyip.resubmit',
            'Resubmitted ABYIP document: '.($document['title'] ?? $validated['title']),
            ['document_id' => $document['id'] ?? null, 'fiscal_year' => $document['fiscal_year'] ?? null]
        );

        return response()->json([
            'message' => 'ABYIP resubmitted for review.',
            'data' => $document,
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function importPayloadRules(): array
    {
        return [
            'confirm_budget_warnings' => ['nullable', 'boolean'],
            'document' => ['nullable', 'array'],
            'document.fiscal_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'document.barangay_estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'document.sk_fund_percentage' => ['nullable', 'numeric', 'min:0'],
            'document.sk_fund_amount' => ['nullable', 'numeric', 'min:0'],
            'rows' => ['nullable', 'array'],
            'rows.*.row_type' => ['nullable', Rule::in(['document', 'category', 'expenditure', 'youth_program', 'activity', 'data'])],
            'rows.*.hierarchy_level' => ['nullable', Rule::in(['program', 'category'])],
            'rows.*.grouped_budget' => ['nullable', 'boolean'],
            'rows.*.implementation_period' => ['nullable', 'string', 'max:255'],
            'rows.*.code' => ['nullable', 'string', 'max:20'],
            'rows.*.program_name' => ['nullable', 'string', 'max:255'],
            'rows.*.category' => ['nullable', 'string', 'max:255'],
            'rows.*.activity_name' => ['nullable', 'string', 'max:255'],
            'rows.*.description' => ['nullable', 'string'],
            'rows.*.expected_result' => ['nullable', 'string'],
            'rows.*.performance_indicator' => ['nullable', 'string'],
            'rows.*.implementation_start' => ['nullable', 'date'],
            'rows.*.implementation_end' => ['nullable', 'date'],
            'rows.*.person_responsible' => ['nullable', 'string', 'max:255'],
            'rows.*.mooe' => ['nullable', 'numeric', 'min:0'],
            'rows.*.co' => ['nullable', 'numeric', 'min:0'],
            'rows.*.total' => ['nullable', 'numeric', 'min:0'],
            'rows.*.page_number' => ['nullable', 'integer', 'min:1'],
            'rows.*.source_text' => ['nullable', 'string'],
            'rows.*.progress_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
