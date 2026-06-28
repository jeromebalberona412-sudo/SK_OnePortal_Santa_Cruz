<?php

namespace App\Modules\Program_Management\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program_Management\Services\ScholarshipSchoolYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScholarshipSchoolYearController extends Controller
{
    public function __construct(private readonly ScholarshipSchoolYearService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->listLabelsForBarangay($request->user()),
            'can_manage' => $this->service->canManageSchoolYears($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $record = $this->service->store($request->user(), $request->all());

            return response()->json([
                'message' => 'School year added successfully.',
                'data' => $record,
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}
