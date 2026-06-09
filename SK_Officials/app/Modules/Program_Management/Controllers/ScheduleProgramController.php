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
        return response()->json([
            'data' => $this->service->resolveEducationProgramMeta($request->user()),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listForBarangay($request->user())->values()->all(),
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
            $program = $this->service->store($request->user(), $request->all());

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
            $program = $this->service->update($request->user(), $id, $request->all());

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
