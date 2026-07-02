<?php

namespace App\Http\Controllers;

use App\Services\ArchiveTermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArchiveTermController extends Controller
{
    public function index(Request $request, ArchiveTermService $archiveTermService): JsonResponse
    {
        $terms = $archiveTermService->termsForUser($request->user());

        return response()->json([
            'terms' => $terms,
            'active_term_id' => $archiveTermService->activeTermId($terms),
        ]);
    }
}
