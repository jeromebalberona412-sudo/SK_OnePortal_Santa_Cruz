<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\CommunityFeed\Services\CloudinaryService;
use App\Modules\Shared\Controllers\Controller;
use App\Services\CommunityFeedAvatarService;
use App\Services\FeedCommentRateLimiter;
use App\Services\SkFederationsNotificationService;
use App\Modules\Shared\Models\Announcement;
use App\Modules\Shared\Models\AnnouncementComment;
use App\Modules\Shared\Models\AnnouncementReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CommunityFeedPostController extends Controller
{
    private const MAX_IMAGES = 20;

    private const MAX_BODY_LENGTH = 2000;

    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly SkFederationsNotificationService $notificationService,
        private readonly CommunityFeedAvatarService $avatarService,
    ) {
    }

    // GET /api/community-feed?filter=all&page=1
    public function feed(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $query = Announcement::with([
            'barangay',
            'comments.user',
            'user',
            'images',
            'reactions' => fn ($q) => $q->with('user')->latest()->limit(12),
        ])
            ->withCount('reactions');

        if (Schema::hasColumn((new Announcement)->getTable(), 'is_archived')) {
            $query->active();
        }

        $query->orderByDesc('created_at');

        $perPage = min(100, max(1, (int) $request->get('per_page', 100)));

        if ($request->filter && $request->filter !== 'all') {
            $query->where('type', $request->filter);
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%')
                    ->orWhere('type', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $posts = $query->paginate($perPage);

        return response()->json([
            'data'         => collect($posts->items())->map(fn($p) => $this->formatPost($p, $user->id)),
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
            'user_id'      => $user->id,
        ]);
    }

    // POST /api/community-feed
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'link_url' => filled($request->input('link_url')) ? $request->input('link_url') : null,
            'title'    => filled($request->input('title')) ? $request->input('title') : null,
        ]);

        $validated = $request->validate([
            'type'      => 'required|in:announcement,event,activity,program,update',
            'title'     => 'nullable|string|max:255',
            'body'      => 'required|string|max:'.self::MAX_BODY_LENGTH,
            'link_url'  => 'nullable|url|max:4096',
            'images'    => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*'  => 'image|max:5120',
        ]);

        $user = Auth::user();
        $post = Announcement::create([
            'type'               => $validated['type'],
            'title'              => $validated['title'] ?? null,
            'body'               => $validated['body'],
            'link_url'           => $validated['link_url'] ?? null,
            'user_id'            => $user->id,
            'barangay_id'        => null,
        ]);

        DB::table('announcements')
            ->where('id', $post->id)
            ->update(['is_federation_wide' => DB::raw('true')]);

        $post->refresh();

        $this->storePostImages($post, $request);

        $fresh = Announcement::with([
            'barangay',
            'comments.user',
            'user',
            'images',
            'reactions' => fn ($q) => $q->with('user')->latest()->limit(12),
        ])
            ->withCount('reactions')
            ->findOrFail($post->id);

        return response()->json($this->formatPost($fresh, $user->id), 201);
    }

    // PUT /api/community-feed/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Announcement::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereRaw('"is_federation_wide" = true')
            ->firstOrFail();

        $request->merge([
            'link_url' => filled($request->input('link_url')) ? $request->input('link_url') : null,
            'title'    => filled($request->input('title')) ? $request->input('title') : null,
        ]);

        $validated = $request->validate([
            'type'              => 'sometimes|in:announcement,event,activity,program,update',
            'title'             => 'nullable|string|max:255',
            'body'              => 'sometimes|string|max:'.self::MAX_BODY_LENGTH,
            'link_url'          => 'nullable|url|max:4096',
            'images'            => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*'          => 'image|max:5120',
            'removed_image_ids'   => 'nullable|array',
            'removed_image_ids.*' => 'integer',
        ]);

        $post->update(collect($validated)->except(['images', 'removed_image_ids'])->all());

        $removedIds = array_values(array_filter(array_map('intval', (array) $request->input('removed_image_ids', []))));
        if ($removedIds !== []) {
            $post->images()->whereIn('id', $removedIds)->delete();
        }

        $currentCount = $post->images()->count();
        if ($request->hasFile('images') && $currentCount < self::MAX_IMAGES) {
            $remaining = self::MAX_IMAGES - $currentCount;
            $this->storePostImages($post, $request, $currentCount, $remaining);
        }

        return response()->json($this->formatPost(
            $post->load(['barangay', 'comments.user', 'user', 'images', 'reactions' => fn ($q) => $q->with('user')->latest()->limit(12)])
                ->loadCount('reactions'),
            Auth::id()
        ));
    }

    // DELETE /api/community-feed/{id}
    public function destroy(int $id): JsonResponse
    {
        $post = Announcement::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereRaw('"is_federation_wide" = true')
            ->firstOrFail();

        app(\App\Modules\Archive_Management\Services\FederationPostArchiveService::class)
            ->archive($post, Auth::user());

        return response()->json(['success' => true]);
    }

    // GET /api/community-feed/{id}/likes
    public function likes(int $id): JsonResponse
    {
        Announcement::query()->findOrFail($id);

        $reactions = AnnouncementReaction::query()
            ->with('user')
            ->where('announcement_id', $id)
            ->latest()
            ->get();

        $reactors = $reactions->map(fn (AnnouncementReaction $reaction) => [
            'name' => $this->resolveReactorName($reaction),
            'avatar_url' => $this->resolveReactorAvatar($reaction),
            'role_label' => $this->resolveReactorRoleLabel($reaction),
        ])->values();

        return response()->json([
            'count' => $reactors->count(),
            'reactors' => $reactors,
        ]);
    }

    // POST /api/community-feed/{id}/react
    public function react(int $id): JsonResponse
    {
        $user     = Auth::user();
        $post     = Announcement::query()->findOrFail($id);
        $existing = AnnouncementReaction::where([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'sk_fed',
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            AnnouncementReaction::create([
                'announcement_id' => $id,
                'user_id'         => $user->id,
                'user_type'       => 'sk_fed',
            ]);
            $liked = true;

            $postLabel = $this->notificationService->postLabel($post->title, $post->body);
            if ($post->is_federation_wide) {
                $this->notificationService->notifyFederationPortalUsersExcept(
                    [(int) $user->id],
                    SkFederationsNotificationService::CATEGORY_COMMUNITY_FEED,
                    "{$user->name} liked a federation post",
                    $postLabel,
                    '/community-feed',
                );
            } elseif ((int) $post->user_id !== (int) $user->id) {
                $this->notificationService->notifyCommunityFeedLike(
                    (int) $post->user_id,
                    (string) $user->name,
                    $postLabel,
                    $post->id,
                );
            }
        }

        $reactions = AnnouncementReaction::with('user')
            ->where('announcement_id', $id)
            ->latest()
            ->limit(12)
            ->get();

        $count = AnnouncementReaction::where('announcement_id', $id)->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
            'reactions_summary' => $this->formatReactionsSummary($reactions, $count),
        ]);
    }

    // POST /api/community-feed/{id}/comment
    public function comment(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $limiter = app(FeedCommentRateLimiter::class);
        $cooldown = $limiter->check('sk_fed', (int) $user->id);

        if (! $cooldown['allowed']) {
            return response()->json([
                'message' => $cooldown['message'],
                'retry_after' => $cooldown['retry_after'],
            ], 429);
        }

        $request->validate([
            'body' => 'required|string|max:'.FeedCommentRateLimiter::MAX_BODY_LENGTH,
            'parent_id' => 'nullable|integer',
        ]);

        $post    = Announcement::query()->findOrFail($id);

        if ($request->filled('parent_id')) {
            AnnouncementComment::query()
                ->where('id', (int) $request->parent_id)
                ->where('announcement_id', $id)
                ->firstOrFail();
        }

        $comment = AnnouncementComment::create([
            'announcement_id' => $id,
            'parent_id' => $request->input('parent_id'),
            'user_id'         => $user->id,
            'user_type'       => 'sk_fed',
            'author_name'     => $user->name,
            'body'            => $request->body,
        ]);

        if ((int) $post->user_id !== (int) $user->id) {
            $postLabel = $this->notificationService->postLabel($post->title, $post->body);
            if ($post->is_federation_wide) {
                $this->notificationService->notifyFederationPortalUsersExcept(
                    [(int) $user->id],
                    SkFederationsNotificationService::CATEGORY_COMMUNITY_FEED,
                    "{$user->name} commented on a federation post",
                    Str::limit(trim((string) $request->body), 160) ?: $postLabel,
                    '/community-feed',
                );
            } else {
                $this->notificationService->notifyCommunityFeedComment(
                    (int) $post->user_id,
                    (string) $user->name,
                    $postLabel,
                    $post->id,
                    $request->body,
                );
            }
        }

        $limiter->hit('sk_fed', (int) $user->id);

        return response()->json($this->formatComment($comment->load('user')), 201);
    }

    // POST /api/community-feed/upload-image
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);

        try {
            $publicId = 'fed_post_' . Auth::id() . '_' . Str::random(8);
            $result   = $this->cloudinary->upload($request->file('image'), $publicId);
            return response()->json(['url' => $result['url']]);
        } catch (Throwable $e) {
            \Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function formatPost(Announcement $post, int $userId): array
    {
        $liked = AnnouncementReaction::where([
            'announcement_id' => $post->id,
            'user_id'         => $userId,
            'user_type'       => 'sk_fed',
        ])->exists();

        $authorName = $post->user?->name
            ?? ($post->is_federation_wide ? 'SK Federation' : ('SK Brgy. ' . ($post->barangay?->name ?? '')));

        $imageRecords = $post->relationLoaded('images') ? $post->images : collect();
        $imageItems = $imageRecords
            ->map(fn ($img) => [
                'id'  => $img->id,
                'url' => $this->cloudinary->normalizeUrl($img->image_url),
            ])
            ->filter(fn ($item) => ! empty($item['url']))
            ->values()
            ->all();
        $images = array_values(array_unique(array_column($imageItems, 'url')));

        return [
            'id'                 => $post->id,
            'type'               => $post->type,
            'title'              => $post->title,
            'body'               => $post->body,
            'image_url'          => $images[0] ?? null,
            'images'             => $images,
            'image_items'        => $imageItems,
            'link_url'           => $post->link_url,
            'is_federation_wide' => (bool) $post->is_federation_wide,
            'barangay_name'      => $post->barangay?->name,
            'author_name'        => $authorName,
            'author_avatar_url'  => $this->avatarService->resolveForPost($post),
            'owned'              => $post->user_id === $userId && $post->is_federation_wide,
            'likes'              => $post->reactions_count ?? $post->reactions()->count(),
            'liked'              => $liked,
            'time'               => $post->created_at?->diffForHumans() ?? 'Just now',
            'reactions_summary'  => $this->formatReactionsSummary(
                $post->relationLoaded('reactions') ? $post->reactions : collect(),
                (int) ($post->reactions_count ?? 0),
            ),
            'comments'           => $post->comments->map(fn ($c) => $this->formatComment($c))->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatComment(AnnouncementComment $comment): array
    {
        return [
            'id'          => $comment->id,
            'author_name' => $comment->author_name,
            'body'        => $comment->body,
            'time'        => $comment->created_at?->diffForHumans() ?? 'Just now',
            'user_type'   => $comment->user_type,
            'avatar_url'  => $this->avatarService->resolveForComment($comment),
        ];
    }

    /**
     * @param  Collection<int, AnnouncementReaction>  $reactions
     * @return array{count: int, reactors: list<array{name: string, avatar_url: string, role_label: string}>}
     */
    private function formatReactionsSummary(Collection $reactions, int $totalCount): array
    {
        $reactors = $reactions->map(fn (AnnouncementReaction $reaction) => [
            'name' => $this->resolveReactorName($reaction),
            'avatar_url' => $this->resolveReactorAvatar($reaction),
            'role_label' => $this->resolveReactorRoleLabel($reaction),
        ])->values();

        return [
            'count' => max(0, $totalCount),
            'reactors' => $reactors->take(8)->all(),
        ];
    }

    private function resolveReactorName(AnnouncementReaction $reaction): string
    {
        return $reaction->user?->name ?: 'Member';
    }

    private function resolveReactorAvatar(AnnouncementReaction $reaction): string
    {
        return $this->avatarService->resolveForReaction($reaction);
    }

    private function resolveReactorRoleLabel(AnnouncementReaction $reaction): string
    {
        return match ($reaction->user_type) {
            'sk_fed' => 'SK Federation',
            'sk_official' => 'SK Official',
            'kabataan' => 'Kabataan Member',
            default => 'Member',
        };
    }

    /**
     * @return list<array{url: string, public_id: string}>
     */
    private function storePostImages(Announcement $post, Request $request, int $sortStart = 0, ?int $maxNew = null): array
    {
        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = [$files];
        }

        $files = array_values(array_filter($files));
        if ($maxNew !== null) {
            $files = array_slice($files, 0, max(0, $maxNew));
        }

        $uploaded = [];
        $sort = $sortStart;
        $now = now();

        foreach ($files as $file) {
            if ($file === null || $sort >= self::MAX_IMAGES) {
                continue;
            }

            try {
                $publicId = 'fed_post_'.$post->id.'_'.Str::random(10);
                $result = $this->cloudinary->upload($file, $publicId);

                \App\Modules\Shared\Models\AnnouncementImage::create([
                    'announcement_id' => $post->id,
                    'image_url'       => $result['url'],
                    'public_id'       => $result['public_id'],
                    'sort_order'      => $sort,
                    'created_at'      => $now,
                ]);

                $uploaded[] = ['url' => $result['url'], 'public_id' => $result['public_id']];
                $sort++;
            } catch (Throwable) {
                continue;
            }
        }

        return $uploaded;
    }
}
