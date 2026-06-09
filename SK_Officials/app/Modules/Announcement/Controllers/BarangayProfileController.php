<?php

namespace App\Modules\Announcement\Controllers;

use App\Modules\Announcement\Services\BarangayProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class BarangayProfileController extends Controller
{
    public function __construct(private readonly BarangayProfileService $barangayProfileService)
    {
    }

    public function show(Request $request, string $slug): View
    {
        $tenantId = $request->user()?->tenant_id;
        $barangay = $this->barangayProfileService->findBySlug($slug, $tenantId);

        abort_if($barangay === null, 404);

        $profile = $this->barangayProfileService->buildProfile($barangay);

        return view('Announcement::barangay-profile', $profile);
    }
}
