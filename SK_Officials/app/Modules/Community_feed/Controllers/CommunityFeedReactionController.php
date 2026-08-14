<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedComment;
use App\Models\CommunityFeedCommentReaction;
use App\Models\CommunityFeedReaction;
use App\Modules\Community_feed\Services\CommunityFeedPresenter;
use App\Services\SkFederationsNotificationDispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommunityFeedReactionController extends Controller
{
    public function __construct(
        private readonly CommunityFeedPresenter $presenter,
    ) {}

    public function index(Request $request, int $id): JsonResponse
    {
        $post = CommunityFeed::query()->findOrFail($id);
        $type = $this->optionalType($request);

        $query = CommunityFeedReaction::query()
            ->with('user')
            ->where('community_feed_id', $id)
            ->orderByDesc('updated_at');

        if ($type) {
            $query->where('reaction_type', $type);
        }

        $reactions = $query->get();

        return $this->reactorsPayload(
            $reactions,
            CommunityFeedReaction::query()->where('community_feed_id', $id),
            $post->barangay_id
        );
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'reaction_type' => ['required', 'string', Rule::in(CommunityFeedReaction::TYPES)],
            'client_seq' => ['sometimes', 'integer', 'min:1'],
        ]);

        $post = CommunityFeed::query()->findOrFail($id);
        $type = $validated['reaction_type'];
        $seq = (int) ($validated['client_seq'] ?? 0);
        $seqKey = "community-feed-reaction-seq:{$user->id}:post:{$id}";
        $lock = Cache::lock("community-feed-reaction:{$user->id}:post:{$id}", 8);

        $result = $lock->block(5, function () use ($id, $user, $type, $seq, $seqKey) {
            return DB::transaction(function () use ($id, $user, $type, $seq, $seqKey) {
                $existing = CommunityFeedReaction::query()
                    ->where('community_feed_id', $id)
                    ->where('user_id', $user->id)
                    ->where('user_type', 'sk_official')
                    ->lockForUpdate()
                    ->first();

                if ($this->isStaleReactionSeq($seqKey, $seq)) {
                    return ['reaction' => $existing?->reaction_type, 'created' => false];
                }

                if ($existing && $existing->reaction_type === $type) {
                    $existing->delete();

                    return ['reaction' => null, 'created' => false];
                }

                if ($existing) {
                    $existing->update(['reaction_type' => $type]);

                    return ['reaction' => $type, 'created' => false];
                }

                CommunityFeedReaction::query()->create([
                    'community_feed_id' => $id,
                    'user_id' => $user->id,
                    'user_type' => 'sk_official',
                    'reaction_type' => $type,
                ]);

                return ['reaction' => $type, 'created' => true];
            });
        });

        if ($result['created'] && $post->is_federation_wide && (int) $post->user_id !== (int) $user->id) {
            $dispatcher = app(SkFederationsNotificationDispatcher::class);
            $dispatcher->notifyFederationPortalCommunityFeedLike(
                (int) $user->id,
                (string) $user->name,
                $dispatcher->postLabel($post->title, $post->body),
            );
        }

        return $this->postReactionPayload($id, $result['reaction']);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        CommunityFeed::query()->findOrFail($id);

        CommunityFeedReaction::query()
            ->where('community_feed_id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', 'sk_official')
            ->delete();

        return $this->postReactionPayload($id, null);
    }

    public function commentIndex(Request $request, int $id, int $commentId): JsonResponse
    {
        $post = CommunityFeed::query()->findOrFail($id);
        $comment = $this->commentOnPost($id, $commentId);
        $type = $this->optionalType($request);

        $query = CommunityFeedCommentReaction::query()
            ->with('user')
            ->where('comment_id', $comment->id)
            ->orderByDesc('updated_at');

        if ($type) {
            $query->where('reaction_type', $type);
        }

        $reactions = $query->get();

        return $this->reactorsPayload(
            $reactions,
            CommunityFeedCommentReaction::query()->where('comment_id', $comment->id),
            $post->barangay_id
        );
    }

    public function commentStore(Request $request, int $id, int $commentId): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'reaction_type' => ['required', 'string', Rule::in(CommunityFeedReaction::TYPES)],
            'client_seq' => ['sometimes', 'integer', 'min:1'],
        ]);

        CommunityFeed::query()->findOrFail($id);
        $comment = $this->commentOnPost($id, $commentId);
        $type = $validated['reaction_type'];
        $seq = (int) ($validated['client_seq'] ?? 0);
        $seqKey = "community-feed-reaction-seq:{$user->id}:comment:{$comment->id}";
        $lock = Cache::lock("community-feed-reaction:{$user->id}:comment:{$comment->id}", 8);

        $userReaction = $lock->block(5, function () use ($comment, $user, $type, $seq, $seqKey) {
            return DB::transaction(function () use ($comment, $user, $type, $seq, $seqKey) {
                $existing = CommunityFeedCommentReaction::query()
                    ->where('comment_id', $comment->id)
                    ->where('user_id', $user->id)
                    ->where('user_type', 'sk_official')
                    ->lockForUpdate()
                    ->first();

                if ($this->isStaleReactionSeq($seqKey, $seq)) {
                    return $existing?->reaction_type;
                }

                if ($existing && $existing->reaction_type === $type) {
                    $existing->delete();

                    return null;
                }

                if ($existing) {
                    $existing->update(['reaction_type' => $type]);

                    return $type;
                }

                CommunityFeedCommentReaction::query()->create([
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                    'user_type' => 'sk_official',
                    'reaction_type' => $type,
                ]);

                return $type;
            });
        });

        return $this->commentReactionPayload($comment->id, $userReaction);
    }

    private function isStaleReactionSeq(string $cacheKey, int $seq): bool
    {
        if ($seq <= 0) {
            return false;
        }

        $last = (int) Cache::get($cacheKey, 0);
        if ($seq < $last) {
            return true;
        }

        Cache::put($cacheKey, $seq, now()->addMinutes(10));

        return false;
    }

    private function commentOnPost(int $feedId, int $commentId): CommunityFeedComment
    {
        return CommunityFeedComment::query()
            ->where('community_feed_id', $feedId)
            ->where('id', $commentId)
            ->firstOrFail();
    }

    private function optionalType(Request $request): ?string
    {
        $type = strtolower(trim((string) $request->query('type', '')));
        if ($type === '' || $type === 'all') {
            return null;
        }

        return in_array($type, CommunityFeedReaction::TYPES, true) ? $type : null;
    }

    private function postReactionPayload(int $feedId, ?string $userReaction): JsonResponse
    {
        $counts = $this->countsFromQuery(
            CommunityFeedReaction::query()
                ->where('community_feed_id', $feedId)
                ->selectRaw('reaction_type, COUNT(*) as aggregate')
                ->groupBy('reaction_type')
                ->pluck('aggregate', 'reaction_type')
        );

        return response()->json([
            'reaction_type' => $userReaction,
            'reaction_counts' => $counts,
            'count' => array_sum($counts),
            'liked' => $userReaction !== null,
        ]);
    }

    private function commentReactionPayload(int $commentId, ?string $userReaction): JsonResponse
    {
        $counts = $this->countsFromQuery(
            CommunityFeedCommentReaction::query()
                ->where('comment_id', $commentId)
                ->selectRaw('reaction_type, COUNT(*) as aggregate')
                ->groupBy('reaction_type')
                ->pluck('aggregate', 'reaction_type')
        );

        return response()->json([
            'reaction_type' => $userReaction,
            'reaction_counts' => $counts,
            'count' => array_sum($counts),
            'liked' => $userReaction !== null,
        ]);
    }

    /**
     * @param  Builder  $countQuery
     */
    private function reactorsPayload($reactions, $countQuery, ?int $barangayId): JsonResponse
    {
        $counts = $this->countsFromQuery(
            (clone $countQuery)
                ->selectRaw('reaction_type, COUNT(*) as aggregate')
                ->groupBy('reaction_type')
                ->pluck('aggregate', 'reaction_type')
        );

        return response()->json([
            'count' => array_sum($counts),
            'reaction_counts' => $counts,
            'reactors' => $this->presenter->formatReactors($reactions, $barangayId),
        ]);
    }

    /**
     * @param  Collection<string, mixed>  $grouped
     * @return array<string, int>
     */
    private function countsFromQuery($grouped): array
    {
        $counts = $this->presenter->emptyReactionCounts();
        foreach ($grouped as $type => $count) {
            if (isset($counts[$type])) {
                $counts[$type] = (int) $count;
            }
        }

        return $counts;
    }
}
