<?php

namespace App\Modules\Program_Accomplishments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Services\PublicBarangayAccomplishmentsService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ProgramAccomplishmentsController extends Controller
{
    public function __construct(private readonly PublicBarangayAccomplishmentsService $accomplishmentsService) {}

    public function index(): View
    {
        try {
            $barangays = Barangay::query()
                ->withExists([
                    'accomplishmentDocuments as accomplishments_exists',
                ])
                ->orderBy('name')
                ->get();
        } catch (QueryException $e) {
            Log::warning('Accomplishments table unavailable, falling back to basic barangay list: '.$e->getMessage());

            $barangays = Barangay::query()
                ->orderBy('name')
                ->get()
                ->each(fn ($b) => $b->setAttribute('accomplishments_exists', false));
        }

        return view('program_accomplishments::barangays.index', [
            'barangays' => $barangays,
            'hideFooter' => true,
        ]);
    }

    public function show(Barangay $barangay): View
    {
        try {
            $accomplishment = $this->accomplishmentsService->latestForBarangay($barangay);
        } catch (QueryException $e) {
            Log::warning('Accomplishments table unavailable for barangay '.$barangay->id.': '.$e->getMessage());
            $accomplishment = null;
        }

        return view('program_accomplishments::barangays.show', [
            'barangay' => $barangay,
            'accomplishment' => $accomplishment,
            'hideFooter' => true,
        ]);
    }
}
