<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedImage;
use App\Modules\Community_feed\Services\CommunityFeedArchiveService;
use App\Modules\Community_feed\Services\CommunityFeedPresenter;
use App\Modules\Community_feed\Services\CloudinaryService;
use App\Services\SkOfficialActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class CommunityFeedController extends Controller
{
    private const MAX_BODY_LENGTH = 2000;

    private const MAX_IMAGES = 20;

    public function __construct(
        private readonly SkOfficialActivityService $activityService,
        private readonly CloudinaryService $cloudinary,
        private readonly CommunityFeedArchiveService $archiveService,
        private readonly CommunityFeedPresenter $presenter,
    ) {
    }

    public function feed(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = CommunityFeed::query()
            ->select('community_feeds.*')
            ->active()
            ->with(['barangay', 'user', 'images', 'reactions.user'])
            ->withCount(['reactions', 'comments'])
            ->where(function ($q) use ($user) {
                $q->where('barangay_id', $user->barangay_id)
                  ->orWhereRaw('"is_federation_wide" = true');
            })
            ->orderByDesc('community_feeds.created_at');

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
            'data'         => collect($posts->items())->map(fn ($p) => $this->presenter->formatPost($p, $user->id, 'sk_official')),
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
            'user_id'      => $user->id,
            'barangay_id'  => $user->barangay_id,
        ]);
    }

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

        $post = CommunityFeed::create([
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
            'community_feed.create',
            'Posted '.$validated['type'].': '.($title ?: mb_substr($validated['body'], 0, 80)),
            ['community_feed_id' => $post->id]
        );

        return response()->json(
            $this->presenter->formatPost(
                $post->fresh(['barangay', 'comments.user', 'comments.reactions.user', 'user', 'images', 'reactions.user']),
                $user->id,
                'sk_official'
            ),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $post = CommunityFeed::query()
            ->active()
            ->with(['barangay', 'comments.user', 'comments.reactions.user', 'user', 'images', 'reactions.user'])
            ->withCount('reactions')
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('barangay_id', $user->barangay_id)
                    ->orWhereRaw('"is_federation_wide" = true');
            })
            ->firstOrFail();

        return response()->json($this->presenter->formatPost($post, $user->id, 'sk_official'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = CommunityFeed::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

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
            'community_feed.update',
            'Updated community feed post: '.($post->title ?: 'Post #'.$id),
            ['community_feed_id' => $id]
        );

        return response()->json($this->presenter->formatPost(
            $post->load(['barangay', 'comments.user', 'comments.reactions.user', 'images', 'reactions.user']),
            Auth::id(),
            'sk_official'
        ));
    }

    public function destroy(int $id): JsonResponse
    {
        $post = CommunityFeed::query()
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
    private function storePostImages(CommunityFeed $post, Request $request): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $files = $request->file('images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $uploaded = [];
        $sort = $post->images()->max('sort_order') ?? -1;
        $now = now();

        foreach (array_slice($files, 0, self::MAX_IMAGES) as $file) {
            if ($file === null) {
                continue;
            }

            try {
                $publicId = 'post_'.$post->id.'_'.Str::random(10);
                $result = $this->cloudinary->upload($file, $publicId);
                $sort++;

                CommunityFeedImage::create([
                    'community_feed_id' => $post->id,
                    'image_url' => $result['url'],
                    'public_id' => $result['public_id'],
                    'sort_order' => $sort,
                    'created_at' => $now,
                ]);

                $uploaded[] = ['url' => $result['url'], 'public_id' => $result['public_id']];
            } catch (Throwable) {
                continue;
            }
        }

        return $uploaded;
    }
}
