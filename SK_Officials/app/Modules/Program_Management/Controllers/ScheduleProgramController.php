<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleProgramController extends Controller
{
    public function __construct(private readonly ScheduleProgramService $service)
    {
    }

    public function meta(Request $request): JsonResponse
    {
        $letter = strtoupper((string) $request->query('letter', ScheduleProgramService::LETTER_EDUCATION));

        return response()->json([
            'data' => $this->service->resolveProgramMeta($request->user(), $letter),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $letter = $request->query('letter');
        $letter = is_string($letter) && $letter !== '' ? strtoupper($letter) : null;

        return response()->json([
            'data' => $this->service->listForBarangay($request->user(), $letter)->values()->all(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->service->findForBarangay($request->user(), $id),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $letter = strtoupper((string) ($request->input('program_letter') ?? $request->query('letter', ScheduleProgramService::LETTER_EDUCATION)));
            $program = $this->service->store($request->user(), $request->all(), $letter);

            return response()->json([
                'message' => 'Schedule program saved successfully.',
                'data' => $program,
            ], 201);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $letter = $request->input('program_letter') ?? $request->query('letter');
            $letter = is_string($letter) && $letter !== '' ? strtoupper($letter) : null;
            $program = $this->service->update($request->user(), $id, $request->all(), $letter);

            return response()->json([
                'message' => 'Schedule program updated successfully.',
                'data' => $program,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->service->delete($request->user(), $id);

            return response()->json([
                'message' => 'Schedule program deleted successfully.',
                'ok' => true,
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
