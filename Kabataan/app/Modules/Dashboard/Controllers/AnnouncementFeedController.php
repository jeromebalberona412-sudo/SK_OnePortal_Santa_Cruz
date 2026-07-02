<?php

namespace App\Modules\Dashboard\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementReaction;
use App\Models\KabataanRegistration;
use App\Models\User;
use App\Modules\Profile\Services\ProfileImageService;
use App\Services\BarangayLogoUrlService;
use App\Services\CloudinaryService;
use App\Services\FeedCommentRateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AnnouncementFeedController extends Controller
{
    public function __construct(
        private readonly ProfileImageService $profileImages,
        private readonly BarangayLogoUrlService $logoUrls,
        private readonly CloudinaryService $cloudinary,
    ) {}

    public function feed(Request $request): JsonResponse
    {
        $user = Auth::user();
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();
        $barangayId = $registration?->barangay_id ?? $user->barangay_id;

        if (! $barangayId) {
            $registration = KabataanRegistration::where('email', $user->email)->latest()->first();
            $barangayId = $registration?->barangay_id;
        }

        if (! $barangayId) {
            return response()->json([
                'data' => [], 'current_page' => 1, 'last_page' => 1, 'user_id' => $user->id,
                '_debug' => 'no barangay_id found for user '.$user->id.' / '.$user->email,
            ]);
        }

        $query = Announcement::with([
            'barangay',
            'user',
            'images',
            'comments' => fn ($q) => $q->with('user')->orderBy('created_at'),
            'reactions' => fn ($q) => $q->with('user')->latest()->limit(12),
        ])
            ->withCount('reactions')
            ->where(function ($q) {
                $q->whereRaw('"is_archived" = false')->orWhereNull('is_archived');
            })
            ->where(function ($q) use ($barangayId) {
                $q->where('barangay_id', $barangayId)
                    ->orWhereRaw('"is_federation_wide" = true');
            })
            ->orderByDesc('created_at');

        if ($request->filter && $request->filter !== 'all') {
            $query->where('type', $request->filter);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('title ILIKE ?', [$term])
                    ->orWhereRaw('body ILIKE ?', [$term]);
            });
        }

        $posts = $query->paginate(10);

        return response()->json([
            'data' => collect($posts->items())->map(fn ($p) => $this->formatPost($p, $user->id)),
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'user_id' => $user->id,
            '_debug' => 'barangay_id='.$barangayId.' total='.$posts->total(),
        ]);
    }

    public function react(int $id): JsonResponse
    {
        $user = Auth::user();
        $existing = AnnouncementReaction::where([
            'announcement_id' => $id,
            'user_id' => $user->id,
            'user_type' => 'kabataan',
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            AnnouncementReaction::create([
                'announcement_id' => $id,
                'user_id' => $user->id,
                'user_type' => 'kabataan',
            ]);
            $liked = true;
        }

        $post = Announcement::find($id);
        $reactions = AnnouncementReaction::with('user')
            ->where('announcement_id', $id)
            ->latest()
            ->limit(12)
            ->get();

        $registrations = $this->kabataanRegistrationsForReactions($reactions);

        $count = AnnouncementReaction::where('announcement_id', $id)->count();

        return response()->json([
            'liked' => $liked,
            'count' => $count,
            'reactions_summary' => $this->formatReactionsSummary(
                $reactions,
                $count,
                $post?->barangay_id,
                $registrations,
            ),
        ]);
    }

    public function comment(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $limiter = app(FeedCommentRateLimiter::class);
        $cooldown = $limiter->check('kabataan', (int) $user->id);

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

        if ($request->filled('parent_id')) {
            AnnouncementComment::query()
                ->where('id', (int) $request->parent_id)
                ->where('announcement_id', $id)
                ->firstOrFail();
        }
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();
        $authorName = $registration
            ? trim($registration->first_name.' '.$registration->last_name)
            : $user->name;

        $comment = AnnouncementComment::create([
            'announcement_id' => $id,
            'parent_id' => $request->input('parent_id'),
            'user_id' => $user->id,
            'user_type' => 'kabataan',
            'author_name' => $authorName,
            'body' => $request->body,
        ]);

        $comment->load('user');
        $post = Announcement::find($id);
        $limiter->hit('kabataan', (int) $user->id);

        return response()->json([
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'author_name' => $comment->author_name,
            'author_avatar_url' => $this->resolveCommentAvatar($comment, $post?->barangay_id),
            'body' => $comment->body,
            'time' => $comment->created_at->diffForHumans(),
        ], 201);
    }

    private function formatPost(Announcement $post, int $userId): array
    {
        $liked = AnnouncementReaction::where([
            'announcement_id' => $post->id,
            'user_id' => $userId,
            'user_type' => 'kabataan',
        ])->exists();

        $authorName = $post->user?->name
            ?? ($post->is_federation_wide ? 'SK Federation' : ('SK Brgy. '.($post->barangay?->name ?? '')));

        $registrations = $this->kabataanRegistrationsForPost($post);

        // Handle multiple images from announcement_images table
        $imageRecords = $post->relationLoaded('images') ? $post->images : collect();
        $images = $imageRecords
            ->map(fn ($img) => $this->cloudinary->normalizeUrl($img->image_url))
            ->filter(fn ($url) => ! empty($url))
            ->values()
            ->all();
        
        // If no images in announcement_images, check legacy image_url field
        if (empty($images) && $post->image_url) {
            $normalized = $this->cloudinary->normalizeUrl($post->image_url);
            if ($normalized) {
                $images = [$normalized];
            }
        }

        return [
            'id' => $post->id,
            'type' => $post->type,
            'title' => $post->title,
            'body' => $post->body,
            'image_url' => $images[0] ?? null,
            'images' => $images,
            'link_url' => $post->link_url,
            'is_federation_wide' => (bool) $post->is_federation_wide,
            'barangay_name' => $post->barangay?->name,
            'barangay_logo_url' => $this->logoUrls->resolve($post->barangay_id),
            'author_name' => $authorName,
            'author_avatar_url' => $this->resolvePostAuthorAvatar($post),
            'likes' => $post->reactions_count,
            'liked' => $liked,
            'time' => $post->created_at->diffForHumans(),
            'reactions_summary' => $this->formatReactionsSummary(
                $post->reactions,
                (int) $post->reactions_count,
                $post->barangay_id,
                $registrations,
            ),
            'comments' => $this->formatThreadedComments($post->comments, $post->barangay_id),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AnnouncementComment>  $comments
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function formatThreadedComments(Collection $comments, ?int $barangayId): Collection
    {
        $items = [];

        foreach ($comments as $comment) {
            $items[$comment->id] = [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'author_name' => $comment->author_name,
                'author_avatar_url' => $this->resolveCommentAvatar($comment, $barangayId),
                'body' => $comment->body,
                'time' => $comment->created_at->diffForHumans(),
                'replies' => [],
            ];
        }

        $roots = [];

        foreach ($items as $id => &$item) {
            $parentId = $item['parent_id'] ?? null;
            if ($parentId && isset($items[$parentId])) {
                $items[$parentId]['replies'][] = &$item;
            } else {
                $roots[] = &$item;
            }
        }
        unset($item);

        return collect($roots);
    }

    /**
     * @param  Collection<int, AnnouncementReaction>  $reactions
     * @return array{count: int, names_label: string, reactors: list<array{name: string, avatar_url: string}>}
     */
    private function formatReactionsSummary(
        Collection $reactions,
        int $totalCount,
        ?int $barangayId,
        Collection $registrations,
    ): array {
        $reactors = $reactions->map(fn (AnnouncementReaction $reaction) => [
            'name' => $this->resolveReactorName($reaction, $registrations),
            'avatar_url' => $this->resolveReactorAvatar($reaction, $barangayId),
        ])->values();

        $count = max(0, $totalCount);
        $names = $reactors->pluck('name')->filter()->values();

        $namesLabel = match (true) {
            $count === 0 => '',
            $count === 1 => (string) $names->get(0),
            $count === 2 => $names->get(0).' and '.$names->get(1),
            default => $names->get(0).', '.$names->get(1).' and '.($count - 2).' others',
        };

        return [
            'count' => $count,
            'names_label' => $namesLabel,
            'reactors' => $reactors->take(8)->all(),
        ];
    }

    private function resolvePostAuthorAvatar(Announcement $post): string
    {
        if ($post->user) {
            return $this->profileImages->resolveDisplayUrl(
                $post->user,
                $post->user->name ?? ($post->is_federation_wide ? 'SK Federation' : 'SK Official')
            );
        }

        $logo = $this->logoUrls->resolve($post->barangay_id);
        if ($logo) {
            return $logo;
        }

        $name = $post->is_federation_wide ? 'SK Federation' : ('SK '.($post->barangay?->name ?? ''));

        return $this->uiAvatarUrl($name);
    }

    private function resolveCommentAvatar(AnnouncementComment $comment, ?int $barangayId): string
    {
        if ($comment->user_type === 'kabataan' && $comment->user) {
            return $this->profileImages->resolveDisplayUrl($comment->user, $comment->author_name);
        }

        if (in_array($comment->user_type, ['sk_official', 'sk_fed'], true) && $comment->user) {
            $logo = $this->logoUrls->resolve($comment->user->barangay_id ?? $barangayId);
            if ($logo) {
                return $logo;
            }
        }

        return $this->uiAvatarUrl($comment->author_name);
    }

    private function resolveReactorAvatar(AnnouncementReaction $reaction, ?int $barangayId): string
    {
        if ($reaction->user_type === 'kabataan' && $reaction->user) {
            return $this->profileImages->resolveDisplayUrl($reaction->user);
        }

        if (in_array($reaction->user_type, ['sk_official', 'sk_fed'], true) && $reaction->user) {
            $logo = $this->logoUrls->resolve($reaction->user->barangay_id ?? $barangayId);
            if ($logo) {
                return $logo;
            }
        }

        $name = $reaction->user?->name ?? 'Member';

        return $this->uiAvatarUrl($name);
    }

    private function resolveReactorName(AnnouncementReaction $reaction, Collection $registrations): string
    {
        if ($reaction->user_type === 'kabataan' && $reaction->user) {
            return $this->kabataanDisplayName($reaction->user, $registrations);
        }

        return $reaction->user?->name ?? 'Member';
    }

    private function kabataanDisplayName(?User $user, Collection $registrations): string
    {
        if (! $user) {
            return 'Youth Member';
        }

        $registration = $registrations->get($user->id);
        if ($registration) {
            $name = trim($registration->first_name.' '.$registration->last_name);

            return $name !== '' ? $name : ($user->name ?: 'Youth Member');
        }

        return $user->name ?: 'Youth Member';
    }

    /**
     * @param  Collection<int, AnnouncementReaction>  $reactions
     * @return Collection<int, KabataanRegistration>
     */
    private function kabataanRegistrationsForReactions(Collection $reactions): Collection
    {
        $userIds = $reactions
            ->where('user_type', 'kabataan')
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return KabataanRegistration::query()
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');
    }

    /**
     * @return Collection<int, KabataanRegistration>
     */
    private function kabataanRegistrationsForPost(Announcement $post): Collection
    {
        $userIds = $post->reactions
            ->where('user_type', 'kabataan')
            ->pluck('user_id')
            ->merge(
                $post->comments
                    ->where('user_type', 'kabataan')
                    ->pluck('user_id')
            )
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return KabataanRegistration::query()
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');
    }

    private function uiAvatarUrl(string $name): string
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=1a56db&color=fff&size=80';
    }
}
