<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\CommunityFeed\Services\CloudinaryService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Announcement;
use App\Modules\Shared\Models\AnnouncementComment;
use App\Modules\Shared\Models\AnnouncementReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class CommunityFeedPostController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinary)
    {
    }

    // GET /api/community-feed?filter=all&page=1
    public function feed(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $query = Announcement::with([
            'barangay',
            'comments',
            'user',
            'reactions' => fn ($q) => $q->with('user')->latest()->limit(12),
        ])
            ->withCount('reactions')
            ->orderByDesc('created_at');

        $perPage = min(100, max(1, (int) $request->get('per_page', 100)));

        if ($request->filter && $request->filter !== 'all') {
            $query->where('type', $request->filter);
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
        $validated = $request->validate([
            'type'      => 'required|in:announcement,event,activity,program,update',
            'title'     => 'nullable|string|max:255',
            'body'      => 'required|string',
            'image_url' => 'nullable|string|max:4096',
            'link_url'  => 'nullable|url|max:4096',
        ]);

        $user = Auth::user();
        $post = Announcement::create(array_merge($validated, [
            'user_id'            => $user->id,
            'barangay_id'        => null,
            'is_federation_wide' => true,
        ]));

        return response()->json($this->formatPost(
            $post->load(['comments', 'user', 'reactions' => fn ($q) => $q->with('user')->latest()->limit(12)])
                ->loadCount('reactions'),
            $user->id
        ), 201);
    }

    // PUT /api/community-feed/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Announcement::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereRaw('"is_federation_wide" = true')
            ->firstOrFail();

        $validated = $request->validate([
            'type'      => 'sometimes|in:announcement,event,activity,program,update',
            'title'     => 'nullable|string|max:255',
            'body'      => 'sometimes|string',
            'image_url' => 'nullable|string|max:4096',
            'link_url'  => 'nullable|url|max:4096',
        ]);

        $post->update($validated);

        return response()->json($this->formatPost(
            $post->load(['barangay', 'comments', 'user', 'reactions' => fn ($q) => $q->with('user')->latest()->limit(12)])
                ->loadCount('reactions'),
            Auth::id()
        ));
    }

    // DELETE /api/community-feed/{id}
    public function destroy(int $id): JsonResponse
    {
        Announcement::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereRaw('"is_federation_wide" = true')
            ->firstOrFail()
            ->delete();

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
        $request->validate(['body' => 'required|string|max:1000']);

        $user    = Auth::user();
        $comment = AnnouncementComment::create([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'sk_fed',
            'author_name'     => $user->name,
            'body'            => $request->body,
        ]);

        return response()->json([
            'id'          => $comment->id,
            'author_name' => $comment->author_name,
            'body'        => $comment->body,
            'time'        => $comment->created_at->diffForHumans(),
        ], 201);
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

        return [
            'id'                 => $post->id,
            'type'               => $post->type,
            'title'              => $post->title,
            'body'               => $post->body,
            'image_url'          => app(\App\Services\CloudinaryService::class)->normalizeUrl($post->image_url),
            'link_url'           => $post->link_url,
            'is_federation_wide' => (bool) $post->is_federation_wide,
            'barangay_name'      => $post->barangay?->name,
            'author_name'        => $authorName,
            'owned'              => $post->user_id === $userId && $post->is_federation_wide,
            'likes'              => $post->reactions_count ?? $post->reactions()->count(),
            'liked'              => $liked,
            'time'               => $post->created_at->diffForHumans(),
            'reactions_summary'  => $this->formatReactionsSummary(
                $post->relationLoaded('reactions') ? $post->reactions : collect(),
                (int) ($post->reactions_count ?? 0),
            ),
            'comments'           => $post->comments->map(fn($c) => [
                'id'          => $c->id,
                'author_name' => $c->author_name,
                'body'        => $c->body,
                'time'        => $c->created_at->diffForHumans(),
            ])->values(),
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
        $name = $this->resolveReactorName($reaction);

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=213F99&color=fff&size=80';
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
}
