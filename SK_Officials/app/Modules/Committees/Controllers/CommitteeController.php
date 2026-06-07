<?php

namespace App\Modules\Committees\Controllers;

use App\Modules\Committees\Services\CommitteeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class CommitteeController extends Controller
{
    public function __construct(private readonly CommitteeService $committeeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->committeeService->listForBarangay($request->user()),
        ]);
    }

    public function officials(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->committeeService->listSkOfficials($request->user()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'committee_name' => ['required', 'string', 'max:255'],
            'committee_head_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $committee = $this->committeeService->create($request->user(), $validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Committee assigned successfully.',
            'data' => $committee,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'committee_name' => ['required', 'string', 'max:255'],
            'committee_head_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $committee = $this->committeeService->findCommitteeForBarangay($request->user(), $id);
            $updated = $this->committeeService->update($request->user(), $committee, $validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Committee updated successfully.',
            'data' => $updated,
        ]);
    }
}
