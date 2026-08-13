<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\CommunityFeed\Services\CommunityFeedService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityFeedController extends Controller
{
    public function __construct(private readonly CommunityFeedService $feedService) {}

    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id;

        return view('community_feed::index', [
            'user' => $request->user(),
            'barangayProfiles' => $this->feedService->listBarangayProfiles($tenantId),
            'commentPreviewPost' => null,
        ]);
    }

    public function comments(Request $request, int $id): View
    {
        $user = $request->user();
        $post = Announcement::query()
            ->active()
            ->with([
                'barangay',
                'comments.user',
                'comments.reactions',
                'user',
                'images',
                'reactions.user',
            ])
            ->withCount('reactions')
            ->findOrFail($id);

        return view('community_feed::index', [
            'user' => $user,
            'barangayProfiles' => $this->feedService->listBarangayProfiles($user?->tenant_id),
            'commentPreviewPost' => app(CommunityFeedPostController::class)->formatPostForPage($post, (int) $user->id),
        ]);
    }

    public function skFedProfile(Request $request): View
    {
        return view('community_feed::sk-fed-profile', ['user' => $request->user()]);
    }

    public function createPost(Request $request): RedirectResponse
    {
        // Prototype: just redirect back with success
        return redirect()->route('sk-fed-profile')->with('success', 'Post created successfully.');
    }

    public function barangayProfile(Request $request, string $slug): View
    {
        $tenantId = $request->user()?->tenant_id;
        $profile = $this->feedService->resolveBarangayProfile($slug, $tenantId);

        if ($profile === null) {
            abort(404);
        }

        $name = $profile['name'];
        $color = $profile['color'];
        $officials = $this->feedService->listOfficialsForBarangay($profile['id'] ?? null);
        $posts = $this->feedService->listPostsForBarangay($profile['id'] ?? null);

        return view('community_feed::barangay-profile', [
            'user' => $request->user(),
            'slug' => $slug,
            'name' => $name,
            'color' => $color,
            'profile' => $profile,
            'officials' => $officials,
            'posts' => $posts,
        ]);
    }
}
