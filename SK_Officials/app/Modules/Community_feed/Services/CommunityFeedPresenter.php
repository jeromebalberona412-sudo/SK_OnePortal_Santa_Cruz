<?php

namespace App\Modules\Community_feed\Services;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedComment;
use App\Models\CommunityFeedCommentReaction;
use App\Models\CommunityFeedReaction;
use App\Services\BarangayLogoUrlService;
use App\Services\CloudinaryService;
use Illuminate\Support\Collection;

class CommunityFeedPresenter
{
    public function __construct(
        private readonly BarangayLogoUrlService $barangayLogoUrlService,
        private readonly CloudinaryService $cloudinary,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function formatPost(CommunityFeed $post, int $userId, string $userType): array
    {
        $userReaction = $this->userReactionType(
            $post->relationLoaded('reactions') ? $post->reactions : null,
            $userId,
            $userType,
            fn () => CommunityFeedReaction::query()
                ->where('community_feed_id', $post->id)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->value('reaction_type')
        );

        $reactionCounts = $this->reactionCounts($post);
        $authorName = $post->user?->name
            ?? ($post->is_federation_wide ? 'SK Federation' : ('SK Brgy. '.($post->barangay?->name ?? '')));

        $imageRecords = $post->relationLoaded('images') ? $post->images : collect();
        $images = $imageRecords->map(fn ($img) => $this->cloudinary->normalizeUrl($img->image_url))->values()->all();
        $images = array_values(array_unique(array_filter($images)));
        $imageItems = $imageRecords->map(fn ($img) => [
            'id' => $img->id,
            'url' => $this->cloudinary->normalizeUrl($img->image_url),
        ])->values()->all();

        $comments = $post->relationLoaded('comments') ? $post->comments : collect();

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
            'likes'              => array_sum($reactionCounts),
            'liked'              => $userReaction !== null,
            'reaction_type'      => $userReaction,
            'reaction_counts'    => $reactionCounts,
            'reactors'           => $post->relationLoaded('reactions')
                ? $this->formatReactors($post->reactions, $post->barangay_id)
                : [],
            'time'               => $post->created_at->diffForHumans(),
            'created_at'         => $post->created_at->toIso8601String(),
            'comment_count'      => $post->relationLoaded('comments')
                ? $comments->count()
                : (int) ($post->comments_count ?? 0),
            'comments_loaded'    => $post->relationLoaded('comments'),
            'comments'           => $post->relationLoaded('comments')
                ? $this->formatCommentTree($comments, $userId, $userType, $post->barangay_id)
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatComment(CommunityFeedComment $comment, int $userId, string $userType, ?int $barangayId = null): array
    {
        $avatarUrl = $this->resolveCommentAvatar($comment, $barangayId);
        $logoUrl = null;

        if (in_array($comment->user_type, ['sk_official', 'sk_fed'], true) && $comment->user) {
            $logoUrl = $this->barangayLogoUrlService->resolve($comment->user->barangay_id ?? $barangayId);
        }

        $reactionCounts = $this->commentReactionCounts($comment);
        $userReaction = $this->userReactionType(
            $comment->relationLoaded('reactions') ? $comment->reactions : null,
            $userId,
            $userType,
            fn () => CommunityFeedCommentReaction::query()
                ->where('comment_id', $comment->id)
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->value('reaction_type')
        );

        return [
            'id'                => $comment->id,
            'parent_id'         => $comment->parent_id,
            'author_name'       => $comment->author_name,
            'body'              => $comment->body,
            'time'              => $comment->created_at->diffForHumans(),
            'user_type'         => $comment->user_type,
            'user_id'           => $comment->user_id,
            'owned'             => (int) $comment->user_id === $userId && $comment->user_type === $userType,
            'author_avatar_url' => $avatarUrl,
            'barangay_logo_url' => $logoUrl,
            'likes'             => array_sum($reactionCounts),
            'liked'             => $userReaction !== null,
            'reaction_type'     => $userReaction,
            'reaction_counts'   => $reactionCounts,
            'reactors'          => $comment->relationLoaded('reactions')
                ? $this->formatReactors($comment->reactions, $barangayId)
                : [],
            'reply_count'       => $comment->relationLoaded('replies') ? $comment->replies->count() : 0,
            'replies'           => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn ($reply) => $this->formatComment($reply, $userId, $userType, $barangayId))->values()->all()
                : [],
        ];
    }

    /**
     * @param  Collection<int, CommunityFeedReaction|CommunityFeedCommentReaction>  $reactions
     * @return list<array<string, mixed>>
     */
    public function formatReactors(Collection $reactions, ?int $barangayId = null): array
    {
        return $reactions->map(function ($reaction) use ($barangayId) {
            $name = trim((string) ($reaction->user?->name ?? 'Member'));

            return [
                'user_id' => (int) $reaction->user_id,
                'user_type' => (string) $reaction->user_type,
                'name' => $name !== '' ? $name : 'Member',
                'avatar_url' => $this->resolveReactorAvatar($reaction, $barangayId),
                'reaction_type' => (string) $reaction->reaction_type,
            ];
        })->values()->all();
    }

    /**
     * @return array<string, int>
     */
    public function emptyReactionCounts(): array
    {
        return array_fill_keys(CommunityFeedReaction::TYPES, 0);
    }

    /**
     * @param  Collection<int, CommunityFeedComment>  $comments
     * @return list<array<string, mixed>>
     */
    public function formatCommentTree(Collection $comments, int $userId, string $userType, ?int $barangayId = null): array
    {
        $byParent = $comments->groupBy(fn (CommunityFeedComment $comment) => $comment->parent_id ?? 0);

        return $byParent->get(0, collect())
            ->map(function (CommunityFeedComment $comment) use ($byParent, $userId, $userType, $barangayId) {
                $comment->setRelation('replies', $byParent->get($comment->id, collect())->values());

                return $this->formatComment($comment, $userId, $userType, $barangayId);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function reactionCounts(CommunityFeed $post): array
    {
        if ($post->relationLoaded('reactions')) {
            return $this->countsFromReactions($post->reactions);
        }

        return $this->countsFromQuery(
            CommunityFeedReaction::query()
                ->where('community_feed_id', $post->id)
                ->selectRaw('reaction_type, COUNT(*) as aggregate')
                ->groupBy('reaction_type')
                ->pluck('aggregate', 'reaction_type')
        );
    }

    /**
     * @return array<string, int>
     */
    private function commentReactionCounts(CommunityFeedComment $comment): array
    {
        if ($comment->relationLoaded('reactions')) {
            return $this->countsFromReactions($comment->reactions);
        }

        return $this->countsFromQuery(
            CommunityFeedCommentReaction::query()
                ->where('comment_id', $comment->id)
                ->selectRaw('reaction_type, COUNT(*) as aggregate')
                ->groupBy('reaction_type')
                ->pluck('aggregate', 'reaction_type')
        );
    }

    /**
     * @param  Collection<int, object>  $reactions
     * @return array<string, int>
     */
    private function countsFromReactions(Collection $reactions): array
    {
        $counts = $this->emptyReactionCounts();
        foreach ($reactions as $reaction) {
            $type = (string) $reaction->reaction_type;
            if (isset($counts[$type])) {
                $counts[$type]++;
            }
        }

        return $counts;
    }

    /**
     * @param  Collection<string, mixed>  $grouped
     * @return array<string, int>
     */
    private function countsFromQuery(Collection $grouped): array
    {
        $counts = $this->emptyReactionCounts();
        foreach ($grouped as $type => $count) {
            if (isset($counts[$type])) {
                $counts[$type] = (int) $count;
            }
        }

        return $counts;
    }

    /**
     * @param  Collection<int, object>|null  $reactions
     */
    private function userReactionType(?Collection $reactions, int $userId, string $userType, callable $fallback): ?string
    {
        if ($reactions === null) {
            $value = $fallback();

            return $value !== null ? (string) $value : null;
        }

        $match = $reactions->first(
            fn ($reaction) => (int) $reaction->user_id === $userId && $reaction->user_type === $userType
        );

        return $match?->reaction_type !== null ? (string) $match->reaction_type : null;
    }

    private function resolveReactorAvatar(object $reaction, ?int $barangayId): string
    {
        $user = $reaction->user ?? null;
        $userType = (string) ($reaction->user_type ?? '');

        if ($userType === 'kabataan' && $user) {
            $profileUrl = trim((string) ($user->profile_image_url ?? ''));
            if ($profileUrl !== '') {
                return $profileUrl;
            }
        }

        if (in_array($userType, ['sk_official', 'sk_fed'], true) && $user) {
            $logo = $this->barangayLogoUrlService->resolve($user->barangay_id ?? $barangayId);
            if ($logo) {
                return $logo;
            }
        }

        $name = $user?->name ?? 'Member';

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=2c2c3e&color=f5c518&size=80';
    }

    private function resolveCommentAvatar(CommunityFeedComment $comment, ?int $barangayId): string
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
