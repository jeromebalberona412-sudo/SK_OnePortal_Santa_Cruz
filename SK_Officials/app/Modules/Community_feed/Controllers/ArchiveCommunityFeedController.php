<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\User;
use App\Modules\Community_feed\Services\CommunityFeedArchiveService;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArchiveCommunityFeedController extends Controller
{
    public function __construct(
        private readonly CommunityFeedArchiveService $archiveService,
        private readonly CloudinaryService $cloudinary,
    ) {
    }

    public function index(): View
    {
        $this->archiveService->purgeExpired();

        return view('Community_feed::archive');
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);
        $this->archiveService->purgeExpired();

        $query = CommunityFeed::query()
            ->archived()
            ->with(['user', 'barangay', 'images'])
            ->orderByDesc('archived_at');

        $this->archiveService->applyRetentionFilter($query);
        $this->scopeBarangay($query, $user);

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 50, 100], true)) {
            $perPage = 10;
        }

        $posts = $query->paginate($perPage);

        $items = collect($posts->items())
            ->map(fn (CommunityFeed $post) => $this->formatArchivedPost($post))
            ->values();

        $allArchived = CommunityFeed::query()->archived();
        $this->archiveService->applyRetentionFilter($allArchived);
        $this->scopeBarangay($allArchived, $user);

        $stats = [
            'total' => (clone $allArchived)->count(),
            'expiring_soon' => (clone $allArchived)
                ->where('archived_at', '<=', now()->subDays(CommunityFeedArchiveService::RETENTION_DAYS - 7))
                ->count(),
        ];

        return response()->json([
            'data'         => $items,
            'stats'        => $stats,
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);
        $this->archiveService->purgeExpired();

        $post = $this->findArchivedPost($id, $user);
        $post->load(['user', 'barangay', 'images']);

        if (! $this->archiveService->userCanAccess($post, $user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => $this->formatArchivedPost($post),
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $post = $this->findArchivedPost($id, $user);

        if (! $this->archiveService->userCanAccess($post, $user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->archiveService->restore($post, $user);

        return response()->json([
            'success' => true,
            'message' => 'Post restored successfully.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $post = $this->findArchivedPost($id, $user);

        if (! $this->archiveService->userCanAccess($post, $user)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->archiveService->permanentlyDelete($post, $user);

        return response()->json([
            'success' => true,
            'message' => 'Post permanently deleted.',
        ]);
    }

    private function ensureAuthorized(User $user): void
    {
        if (! in_array($user->role, [User::ROLE_SK_OFFICIAL, User::ROLE_SK_FED, User::ROLE_ADMIN], true)) {
            abort(403, 'Only SK Officials may access archived posts.');
        }
    }

    private function scopeBarangay($query, User $user): void
    {
        if ($user->role === User::ROLE_SK_OFFICIAL) {
            $query->where('barangay_id', $user->barangay_id);
        }
    }

    private function findArchivedPost(int $id, User $user): CommunityFeed
    {
        $query = CommunityFeed::query()->archived()->where('id', $id);
        $this->archiveService->applyRetentionFilter($query);
        $this->scopeBarangay($query, $user);

        return $query->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatArchivedPost(CommunityFeed $post): array
    {
        $daysRemaining = $this->archiveService->daysRemaining($post->archived_at);
        $tier = $this->archiveService->daysRemainingTier($daysRemaining);

        $images = $post->images
            ->map(fn ($img) => $this->cloudinary->normalizeUrl($img->image_url))
            ->filter()
            ->values()
            ->all();

        $bodyPreview = mb_strlen($post->body) > 160
            ? mb_substr($post->body, 0, 160).'…'
            : $post->body;

        return [
            'id'               => $post->id,
            'type'             => $post->type,
            'type_label'       => ucfirst($post->type),
            'title'            => $post->title ?? '',
            'body'             => $post->body ?? '',
            'body_preview'     => $bodyPreview,
            'link_url'         => $post->link_url,
            'author_name'      => $post->user?->name
                ?? ('SK Brgy. '.($post->barangay?->name ?? '')),
            'barangay_name'    => $post->barangay?->name,
            'is_federation_wide' => (bool) $post->is_federation_wide,
            'created_at'       => $post->created_at?->toIso8601String(),
            'posted_ago'       => $post->created_at?->diffForHumans() ?? '—',
            'archived_at'      => $post->archived_at?->toIso8601String(),
            'archived_ago'     => $post->archived_at?->diffForHumans() ?? '—',
            'days_remaining'   => $daysRemaining,
            'days_tier'        => $tier,
            'auto_delete_label'=> 'Auto delete in '.$daysRemaining.' day'.($daysRemaining === 1 ? '' : 's'),
            'image_count'      => count($images),
            'images'           => $images,
            'thumbnail_url'    => $images[0] ?? null,
        ];
    }
}
