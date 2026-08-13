<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedComment;
use App\Models\CommunityFeedCommentReaction;
use App\Models\CommunityFeedReaction;
use App\Modules\Community_feed\Services\CommunityFeedPresenter;
use App\Services\SkFederationsNotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommunityFeedReactionController extends Controller
{
    public function __construct(
        private readonly CommunityFeedPresenter $presenter,
    ) {
    }

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
        ]);

        $post = CommunityFeed::query()->findOrFail($id);
        $type = $validated['reaction_type'];

        $existing = CommunityFeedReaction::query()
            ->where('community_feed_id', $id)
            ->where('user_id', $user->id)
            ->where('user_type', 'sk_official')
            ->first();

        if ($existing && $existing->reaction_type === $type) {
            $existing->delete();

            return $this->postReactionPayload($id, null);
        }

        if ($existing) {
            $existing->update(['reaction_type' => $type]);
        } else {
            CommunityFeedReaction::create([
                'community_feed_id' => $id,
                'user_id' => $user->id,
                'user_type' => 'sk_official',
                'reaction_type' => $type,
            ]);

            if ($post->is_federation_wide && (int) $post->user_id !== (int) $user->id) {
                $dispatcher = app(SkFederationsNotificationDispatcher::class);
                $dispatcher->notifyFederationPortalCommunityFeedLike(
                    (int) $user->id,
                    (string) $user->name,
                    $dispatcher->postLabel($post->title, $post->body),
                );
            }
        }

        return $this->postReactionPayload($id, $type);
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
        ]);

        CommunityFeed::query()->findOrFail($id);
        $comment = $this->commentOnPost($id, $commentId);
        $type = $validated['reaction_type'];

        $existing = CommunityFeedCommentReaction::query()
            ->where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->where('user_type', 'sk_official')
            ->first();

        if ($existing && $existing->reaction_type === $type) {
            $existing->delete();

            return $this->commentReactionPayload($comment->id, null);
        }

        if ($existing) {
            $existing->update(['reaction_type' => $type]);
        } else {
            CommunityFeedCommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'user_type' => 'sk_official',
                'reaction_type' => $type,
            ]);
        }

        return $this->commentReactionPayload($comment->id, $type);
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
     * @param  \Illuminate\Database\Eloquent\Builder  $countQuery
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
     * @param  \Illuminate\Support\Collection<string, mixed>  $grouped
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
