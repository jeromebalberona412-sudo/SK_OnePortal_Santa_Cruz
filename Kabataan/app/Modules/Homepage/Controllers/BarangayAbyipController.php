<?php

namespace App\Modules\Homepage\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Services\PublicBarangayAbyipService;
use Illuminate\Contracts\View\View;

class BarangayAbyipController extends Controller
{
    public function __construct(private readonly PublicBarangayAbyipService $abyipService) {}

    public function index(): View
    {
        $barangays = Barangay::query()
            ->withExists([
                'abyipDocuments as abyips_exists',
            ])
            ->orderBy('name')
            ->get();

        return view('homepage::barangays.index', compact('barangays'));
    }

    public function show(Barangay $barangay): View
    {
        $abyip = $this->abyipService->latestForBarangay($barangay);

        return view('homepage::barangays.show', compact('barangay', 'abyip'));
    }
}
