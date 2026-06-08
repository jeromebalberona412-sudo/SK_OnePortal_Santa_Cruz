<?php

namespace App\Modules\ABYIP\Controllers;

use App\Modules\ABYIP\Services\AbyipService;
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
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->abyipService->listForBarangay($request->user()),
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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(['word', 'pdf'])],
            'calendar_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'document_html' => ['nullable', 'string'],
            'pdf_data' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
        ]);

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
            'message' => 'ABYIP record saved.',
            'data' => $document,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'document_html' => ['required', 'string'],
        ]);

        try {
            $document = $this->abyipService->update($request->user(), $id, $validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'ABYIP updated successfully.',
            'data' => $document,
        ]);
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
}
