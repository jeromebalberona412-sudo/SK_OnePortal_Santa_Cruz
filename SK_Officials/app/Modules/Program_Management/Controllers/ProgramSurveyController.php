<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ProgramSurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProgramSurveyController extends Controller
{
    public function __construct(private readonly ProgramSurveyService $service)
    {
    }

    public function meta(Request $request, string $committee): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->service->resolveCommitteeContext($request->user(), $committee),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function index(Request $request, string $committee): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listForCommittee($request->user(), $committee)->values()->all(),
        ]);
    }

    public function show(Request $request, string $committee, int $id): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->service->findForCommittee($request->user(), $committee, $id),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function store(Request $request, string $committee): JsonResponse
    {
        try {
            $survey = $this->service->store($request->user(), $committee, $request->all());

            return response()->json([
                'message' => 'Survey saved successfully.',
                'data' => $survey,
            ], 201);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'program_surveys_barangay_program_year_unique')) {
                return response()->json([
                    'message' => 'A survey already exists for this program for the selected year.',
                ], 422);
            }

            throw $exception;
        }
    }

    public function update(Request $request, string $committee, int $id): JsonResponse
    {
        try {
            $survey = $this->service->update($request->user(), $committee, $id, $request->all());

            return response()->json([
                'message' => 'Survey updated successfully.',
                'data' => $survey,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'program_surveys_barangay_program_year_unique')) {
                return response()->json([
                    'message' => 'A survey already exists for this program for the selected year.',
                ], 422);
            }

            throw $exception;
        }
    }

    public function destroy(Request $request, string $committee, int $id): JsonResponse
    {
        try {
            $this->service->delete($request->user(), $committee, $id);

            return response()->json([
                'message' => 'Survey deleted successfully.',
                'ok' => true,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationErrorResponse($exception);
        }
    }

    public function responses(Request $request, string $committee): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listResponses($request->user(), $committee)->values()->all(),
        ]);
    }

    private function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => collect($exception->errors())->flatten()->first(),
            'errors' => $exception->errors(),
        ], 422);
    }
}
