<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ProgramApplicationReviewService;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgramApplicationController extends Controller
{
    public function __construct(private readonly ProgramApplicationReviewService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_SPORTS));

        return response()->json([
            'data' => $this->service->listForBarangay($request->user(), $letter),
            'summary' => $this->service->summarizeForBarangay($request->user(), $letter),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_SPORTS));

            return response()->json([
                'data' => $this->service->findForBarangay($request->user(), $id, $letter),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'rejection_reasons' => ['nullable', 'array'],
            'rejection_reasons.*' => ['string', 'max:255'],
            'letter' => ['nullable', 'string', 'size:1'],
        ]);

        try {
            $letter = strtoupper((string) ($validated['letter'] ?? $request->query('letter', ScheduleProgramService::LETTER_SPORTS)));
            $application = $this->service->updateStatus(
                $request->user(),
                $id,
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

    private function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => collect($exception->errors())->flatten()->first(),
            'errors' => $exception->errors(),
        ], 422);
    }
}
