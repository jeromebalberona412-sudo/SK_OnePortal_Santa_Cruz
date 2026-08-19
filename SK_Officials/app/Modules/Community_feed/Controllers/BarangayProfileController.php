<?php

namespace App\Modules\Community_feed\Controllers;

use App\Modules\Community_feed\Services\BarangayProfileService;
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
        $user = $request->user();
        $tenantId = $user?->tenant_id;
        $barangay = $this->barangayProfileService->findBySlug($slug, $tenantId);

        abort_if($barangay === null, 404);

        $profile = $this->barangayProfileService->buildProfile($barangay);

        $isOwnBarangay = $user && (int) $user->barangay_id === (int) $barangay->id;

        $barangayLogoUrl = $profile['logo_url'] ?? '';

        return view('Community_feed::barangay-profile', array_merge($profile, [
            'user' => $user,
            'slug' => $profile['slug'],
            'isOwnBarangay' => $isOwnBarangay,
            'barangayLogoUrl' => $barangayLogoUrl,
            'commentPreviewPost' => null,
        ]));
    }
}
