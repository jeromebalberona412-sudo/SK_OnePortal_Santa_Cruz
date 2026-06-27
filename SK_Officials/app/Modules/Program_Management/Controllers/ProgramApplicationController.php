<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ProgramApplicationReviewService;
use App\Modules\Program_Management\Services\ProgramDocumentService;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgramApplicationController extends Controller
{
    public function __construct(
        private readonly ProgramApplicationReviewService $service,
        private readonly ProgramDocumentService $documentService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_SPORTS));
        $status = $request->query('status');

        return response()->json([
            'data' => $this->service->listForBarangay(
                $request->user(),
                $letter,
                is_string($status) ? $status : null,
            ),
            'summary' => $this->service->summarizeForBarangay($request->user(), $letter),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $applicationId = $this->parseApplicationId($id);
            $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_SPORTS));

            return response()->json([
                'data' => $this->service->findForBarangay($request->user(), $applicationId, $letter),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function showDocument(Request $request, string $id, string $questionId)
    {
        try {
            $applicationId = $this->parseApplicationId($id);
            $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_SPORTS));
            $application = $this->service->findModelForBarangay($request->user(), $applicationId, $letter);
            $download = $request->boolean('download');

            return $this->documentService->streamForOfficial($application, $questionId, $download);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function updatePaymentStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'string', 'max:50'],
            'apply_to_team' => ['nullable', 'boolean'],
            'letter' => ['nullable', 'string', 'size:1'],
        ]);

        try {
            $applicationId = $this->parseApplicationId($id);
            $letter = strtoupper((string) ($validated['letter'] ?? $request->query('letter', ScheduleProgramService::LETTER_SPORTS)));
            $result = $this->service->updatePaymentStatus(
                $request->user(),
                $applicationId,
                $letter,
                $validated['payment_status'],
                (bool) ($validated['apply_to_team'] ?? false),
            );

            return response()->json([
                'message' => 'Payment status updated.',
                'data' => $result['data'],
                'updated_count' => $result['updated_count'],
                'updated_ids' => $result['updated_ids'],
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'rejection_reasons' => ['nullable', 'array'],
            'rejection_reasons.*' => ['string', 'max:255'],
            'letter' => ['nullable', 'string', 'size:1'],
        ]);

        try {
            $applicationId = $this->parseApplicationId($id);
            $letter = strtoupper((string) ($validated['letter'] ?? $request->query('letter', ScheduleProgramService::LETTER_SPORTS)));
            $application = $this->service->updateStatus(
                $request->user(),
                $applicationId,
                $validated['status'],
                $letter,
                $validated['rejection_reason'] ?? null,
                $validated['rejection_reasons'] ?? null,
            );

            return response()->json([
                'message' => 'Application status updated.',
                'data' => $application,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    private function parseApplicationId(string $id): int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            throw ValidationException::withMessages([
                'application_id' => ['Invalid application id.'],
            ]);
        }

        return (int) $id;
    }

    private function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => collect($exception->errors())->flatten()->first(),
            'errors' => $exception->errors(),
        ], 422);
    }
}
