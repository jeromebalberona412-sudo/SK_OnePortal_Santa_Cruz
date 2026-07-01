<?php

namespace App\Modules\Announcement\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementImage;
use App\Models\AnnouncementReaction;
use App\Models\User;
use App\Modules\Announcement\Services\AnnouncementArchiveService;
use App\Modules\Announcement\Services\CloudinaryService;
use App\Services\BarangayLogoUrlService;
use App\Services\FeedCommentRateLimiter;
use App\Services\SkFederationsNotificationDispatcher;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class AnnouncementController extends Controller
{
    private const MAX_BODY_LENGTH = 2000;

    private const MAX_IMAGES = 20;

    /** @var list<string> */
    private const SK_COMMENT_ROLES = [
        User::ROLE_SK_OFFICIAL,
        User::ROLE_SK_FED,
        User::ROLE_ADMIN,
    ];

    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly CloudinaryService $cloudinary,
        private readonly BarangayLogoUrlService $barangayLogoUrlService,
        private readonly AnnouncementArchiveService $archiveService,
    ) {
    }

    // GET /api/announcements?filter=all&page=1
    public function feed(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Announcement::query()
            ->select('announcements.*')
            ->active()
            ->with(['barangay', 'comments.user', 'user', 'images'])
            ->withCount('reactions')
            ->where(function ($q) use ($user) {
                $q->where('barangay_id', $user->barangay_id)
                  ->orWhereRaw('"is_federation_wide" = true');
            })
            ->orderByDesc('announcements.created_at');

        if ($request->filter && $request->filter !== 'all') {
            $query->where('type', $request->filter);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%')
                    ->orWhere('type', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $posts = $query->paginate(10);

        return response()->json([
            'data'         => collect($posts->items())->map(fn ($p) => $this->formatPost($p, $user->id, 'sk_official')),
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
            'user_id'      => $user->id,
            'barangay_id'  => $user->barangay_id,
        ]);
    }

    // POST /api/announcements
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'      => 'required|in:announcement,event,activity,program,update',
            'title'     => 'nullable|string|max:255',
            'body'      => 'required|string|max:'.self::MAX_BODY_LENGTH,
            'link_url'  => 'nullable|url',
            'images'    => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*'  => 'image|max:5120',
        ]);

        $user = Auth::user();
        $title = $validated['title'] ?? null;

        $post = Announcement::create([
            'type'        => $validated['type'],
            'title'       => $title,
            'body'        => $validated['body'],
            'link_url'    => $validated['link_url'] ?? null,
            'user_id'     => $user->id,
            'barangay_id' => $user->barangay_id,
        ]);

        $this->storePostImages($post, $request);

        $this->activityService->log(
            $user,
            'announcement.create',
            'Posted '.$validated['type'].': '.($title ?: mb_substr($validated['body'], 0, 80)),
            ['announcement_id' => $post->id]
        );

        return response()->json(
            $this->formatPost($post->fresh(['barangay', 'comments', 'user', 'images']), $user->id, 'sk_official'),
            201
        );
    }

    // GET /api/announcements/{id}
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $post = Announcement::query()
            ->active()
            ->with(['barangay', 'comments.user', 'user', 'images'])
            ->withCount('reactions')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json($this->formatPost($post, $user->id, 'sk_official'));
    }

    // PUT /api/announcements/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Announcement::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'type'      => 'sometimes|in:announcement,event,activity,program,update',
            'title'     => 'nullable|string|max:255',
            'body'      => 'sometimes|string|max:'.self::MAX_BODY_LENGTH,
            'link_url'  => 'nullable|url',
            'images'    => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*'  => 'image|max:5120',
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
            $this->storePostImages($post, $request);
        }

        $this->activityService->log(
            Auth::user(),
            'announcement.update',
            'Updated announcement: '.($post->title ?: 'Post #'.$id),
            ['announcement_id' => $id]
        );

        return response()->json($this->formatPost($post->load(['barangay', 'comments', 'images']), Auth::id(), 'sk_official'));
    }

    // DELETE /api/announcements/{id} — archives post (30-day retention)
    public function destroy(int $id): JsonResponse
    {
        $post = Announcement::query()
            ->active()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->archiveService->archive($post, Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Post moved to archive.',
        ]);
    }

    // POST /api/announcements/{id}/react
    public function react(int $id): JsonResponse
    {
        $user = Auth::user();
        $post = Announcement::query()->findOrFail($id);

        $existing = AnnouncementReaction::where([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'sk_official',
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            AnnouncementReaction::create([
                'announcement_id' => $id,
                'user_id'         => $user->id,
                'user_type'       => 'sk_official',
            ]);
            $liked = true;

            if ($post->is_federation_wide && (int) $post->user_id !== (int) $user->id) {
                $dispatcher = app(SkFederationsNotificationDispatcher::class);
                $dispatcher->notifyCommunityFeedLike(
                    (int) $post->user_id,
                    (string) $user->name,
                    $dispatcher->postLabel($post->title, $post->body),
                );
            }
        }

        $count = AnnouncementReaction::where('announcement_id', $id)->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    // POST /api/announcements/{id}/comment
    public function comment(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $limiter = app(FeedCommentRateLimiter::class);
        $cooldown = $limiter->check('sk_official', (int) $user->id);

        if (! $cooldown['allowed']) {
            return response()->json([
                'message' => $cooldown['message'],
                'retry_after' => $cooldown['retry_after'],
            ], 429);
        }

        $request->validate(['body' => 'required|string|max:'.FeedCommentRateLimiter::MAX_BODY_LENGTH]);
        $post = Announcement::query()->findOrFail($id);

        if (! in_array($user->role, self::SK_COMMENT_ROLES, true)) {
            return response()->json(['message' => 'Only SK Officials may comment on this feed.'], 403);
        }

        $comment = AnnouncementComment::create([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'sk_official',
            'author_name'     => $user->name,
            'body'            => $request->body,
        ]);

        if ($post->is_federation_wide && (int) $post->user_id !== (int) $user->id) {
            $dispatcher = app(SkFederationsNotificationDispatcher::class);
            $dispatcher->notifyCommunityFeedComment(
                (int) $post->user_id,
                (string) $user->name,
                $dispatcher->postLabel($post->title, $post->body),
                $request->body,
            );
        }

        $limiter->hit('sk_official', (int) $user->id);

        return response()->json($this->formatComment($comment->load('user'), $post->barangay_id), 201);
    }

    // POST /api/announcements/upload-image (legacy single upload)
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);

        try {
            $publicId = 'post_'.Auth::id().'_'.Str::random(8);
            $result   = $this->cloudinary->upload($request->file('image'), $publicId);

            return response()->json(['url' => $result['url'], 'public_id' => $result['public_id']]);
        } catch (Throwable) {
            return response()->json(['message' => 'Upload failed.'], 500);
        }
    }

    /**
     * @return list<array{url: string, public_id: string|null}>
     */
    private function storePostImages(Announcement $post, Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $files = $request->file('images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $uploaded = [];
        $sort = 0;
        $now = now();

        foreach (array_slice($files, 0, self::MAX_IMAGES) as $file) {
            if ($file === null) {
                continue;
            }

            try {
                $publicId = 'post_'.$post->id.'_'.Str::random(10);
                $result = $this->cloudinary->upload($file, $publicId);

                AnnouncementImage::create([
                    'announcement_id' => $post->id,
                    'image_url' => $result['url'],
                    'public_id' => $result['public_id'],
                    'sort_order' => $sort,
                    'created_at' => $now,
                ]);

                $uploaded[] = ['url' => $result['url'], 'public_id' => $result['public_id']];
                $sort++;
            } catch (Throwable) {
                continue;
            }
        }

        return $uploaded;
    }

    private function formatPost(Announcement $post, int $userId, string $userType): array
    {
        $liked = AnnouncementReaction::where([
            'announcement_id' => $post->id,
            'user_id'         => $userId,
            'user_type'       => $userType,
        ])->exists();

        $authorName = $post->user?->name
            ?? ($post->is_federation_wide ? 'SK Federation' : ('SK Brgy. '.($post->barangay?->name ?? '')));

        $normalizer = app(\App\Services\CloudinaryService::class);
        $imageRecords = $post->relationLoaded('images') ? $post->images : collect();
        $images = $imageRecords->map(fn ($img) => $normalizer->normalizeUrl($img->image_url))->values()->all();
        $images = array_values(array_unique(array_filter($images)));
        $imageItems = $imageRecords->map(fn ($img) => [
            'id' => $img->id,
            'url' => $normalizer->normalizeUrl($img->image_url),
        ])->values()->all();

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
            'barangay_id'        => $post->barangay_id,
            'barangay_logo_url'  => $this->barangayLogoUrlService->resolve($post->barangay_id),
            'author_name'        => $authorName,
            'owned'              => $post->user_id === $userId && ! $post->is_federation_wide,
            'likes'              => $post->reactions_count ?? $post->reactions()->count(),
            'liked'              => $liked,
            'time'               => $post->created_at->diffForHumans(),
            'created_at'         => $post->created_at->toIso8601String(),
            'comments'           => $post->comments->map(fn ($c) => $this->formatComment($c, $post->barangay_id))->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatComment(AnnouncementComment $comment, ?int $barangayId = null): array
    {
        $avatarUrl = $this->resolveCommentAvatar($comment, $barangayId);
        $logoUrl = null;

        if (
            in_array($comment->user_type, ['sk_official', 'sk_fed'], true)
            && $comment->user
        ) {
            $logoUrl = $this->barangayLogoUrlService->resolve($comment->user->barangay_id ?? $barangayId);
        }

        return [
            'id'                => $comment->id,
            'author_name'       => $comment->author_name,
            'body'              => $comment->body,
            'time'              => $comment->created_at->diffForHumans(),
            'user_type'         => $comment->user_type,
            'author_avatar_url' => $avatarUrl,
            'barangay_logo_url' => $logoUrl,
        ];
    }

    private function resolveCommentAvatar(AnnouncementComment $comment, ?int $barangayId): string
    {
        if ($comment->user_type === 'kabataan' && $comment->user) {
            $profileUrl = trim((string) ($comment->user->profile_image_url ?? ''));
            if ($profileUrl !== '') {
                return $profileUrl;
            }
        }

        if (in_array($comment->user_type, ['sk_official', 'sk_fed'], true) && $comment->user) {
            $logo = $this->barangayLogoUrlService->resolve($comment->user->barangay_id ?? $barangayId);
            if ($logo) {
                return $logo;
            }
        }

        if ($comment->user_type === 'sk_fed') {
            return asset('images/logo.png');
        }

        $name = $comment->author_name ?? 'Member';

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=2c2c3e&color=f5c518&size=80';
    }
}
