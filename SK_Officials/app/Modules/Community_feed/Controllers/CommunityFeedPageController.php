<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\Barangay;
use App\Modules\Community_feed\Services\BarangayProfileService;
use App\Services\BarangayLogoUrlService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityFeedPageController extends Controller
{
    public function __construct(private readonly BarangayProfileService $barangayProfileService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $brgy = Barangay::find($user->barangay_id);
        $slug = $brgy ? Str::slug($brgy->name) : 'san-jose';
        $name = $brgy?->name ?? 'Your Barangay';
        $color = '#f5c518';
        $barangayProfiles = $this->barangayProfileService->listForTenant((int) $user->tenant_id);
        $barangayLogoUrl = app(BarangayLogoUrlService::class)->resolve($user->barangay_id);
        $profilePreview = null;

        if ($brgy !== null) {
            $built = $this->barangayProfileService->buildProfile($brgy);
            $profilePreview = [
                'name' => $built['name'],
                'location' => $built['location'],
                'logo_url' => $built['logo_url'],
                'term_label' => $built['term_label'],
                'post_count' => $built['post_count'],
                'officials' => $built['officials'],
                'posts' => collect($built['posts'])->take(5)->values()->all(),
            ];
        }

        return view('Community_feed::community-feed', compact(
            'slug',
            'name',
            'color',
            'user',
            'barangayProfiles',
            'barangayLogoUrl',
            'profilePreview',
        ));
    }
}
