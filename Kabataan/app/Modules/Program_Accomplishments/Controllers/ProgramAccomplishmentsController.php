<?php

namespace App\Modules\Program_Accomplishments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Services\PublicBarangayAccomplishmentsService;
use Illuminate\Contracts\View\View;

class ProgramAccomplishmentsController extends Controller
{
    public function __construct(private readonly PublicBarangayAccomplishmentsService $accomplishmentsService) {}

    public function index(): View
    {
        $barangays = Barangay::query()
            ->withExists([
                'accomplishmentDocuments as accomplishments_exists',
            ])
            ->orderBy('name')
            ->get();

        return view('program_accomplishments::barangays.index', compact('barangays'));
    }

    public function show(Barangay $barangay): View
    {
        $accomplishment = $this->accomplishmentsService->latestForBarangay($barangay);

        return view('program_accomplishments::barangays.show', compact('barangay', 'accomplishment'));
    }
}
