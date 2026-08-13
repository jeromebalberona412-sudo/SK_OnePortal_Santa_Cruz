<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\Barangay;
use App\Models\CommunityFeed;
use App\Modules\Community_feed\Services\BarangayProfileService;
use App\Modules\Community_feed\Services\CommunityFeedPresenter;
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
        return view('Community_feed::community-feed', $this->feedPageData($request));
    }

    public function comments(Request $request, int $id): View
    {
        $user = $request->user();
        $post = CommunityFeed::query()
            ->active()
            ->with(['barangay', 'comments.user', 'comments.reactions', 'user', 'images', 'reactions'])
            ->withCount('reactions')
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->orWhereRaw('"is_federation_wide" = true');
            })
            ->firstOrFail();

        return view('Community_feed::community-feed', array_merge($this->feedPageData($request), [
            'commentPreviewPost' => app(CommunityFeedPresenter::class)->formatPost($post, $user->id, 'sk_official'),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function feedPageData(Request $request): array
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

        return [
            'slug' => $slug,
            'name' => $name,
            'color' => $color,
            'user' => $user,
            'barangayProfiles' => $barangayProfiles,
            'barangayLogoUrl' => $barangayLogoUrl,
            'profilePreview' => $profilePreview,
            'commentPreviewPost' => null,
        ];
    }
}
