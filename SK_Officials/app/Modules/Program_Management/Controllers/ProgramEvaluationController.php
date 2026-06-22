<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ProgramEvaluationService;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgramEvaluationController extends Controller
{
    public function __construct(private readonly ProgramEvaluationService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $letter = $this->resolveLetter($request);

        return response()->json([
            'data' => $this->service->listForBarangay($request->user(), $letter)->values()->all(),
            'stats' => $this->service->summarizeForBarangay($request->user(), $letter),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->service->findForBarangay($request->user(), $id, $this->resolveLetter($request)),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $letter = $this->resolveLetter($request);
            $evaluation = $this->service->store($request->user(), $request->all(), $letter);

            return response()->json([
                'message' => 'Evaluation created successfully.',
                'data' => $evaluation,
            ], 201);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $evaluation = $this->service->update(
                $request->user(),
                $id,
                $request->all(),
                $this->resolveLetter($request)
            );

            return response()->json([
                'message' => 'Evaluation updated successfully.',
                'data' => $evaluation,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->delete($request->user(), $id, $this->resolveLetter($request));

            return response()->json([
                'message' => 'Evaluation deleted successfully.',
                'ok' => true,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    private function resolveLetter(Request $request): string
    {
        $letter = strtoupper((string) ($request->input('program_letter') ?? $request->query('letter', ScheduleProgramService::LETTER_EDUCATION)));

        return $letter !== '' ? $letter : ScheduleProgramService::LETTER_EDUCATION;
    }
}
