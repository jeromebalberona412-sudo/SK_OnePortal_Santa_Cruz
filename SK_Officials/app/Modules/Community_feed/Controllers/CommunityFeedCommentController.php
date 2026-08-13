<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedComment;
use App\Models\User;
use App\Modules\Community_feed\Services\CommunityFeedPresenter;
use App\Services\FeedCommentRateLimiter;
use App\Services\SkFederationsNotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CommunityFeedCommentController extends Controller
{
    /** @var list<string> */
    private const SK_COMMENT_ROLES = [
        User::ROLE_SK_OFFICIAL,
        User::ROLE_SK_FED,
        User::ROLE_ADMIN,
    ];

    public function __construct(
        private readonly CommunityFeedPresenter $presenter,
    ) {
    }

    public function store(Request $request, int $id): JsonResponse
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

        $validated = $request->validate([
            'body' => 'required|string|max:'.FeedCommentRateLimiter::MAX_BODY_LENGTH,
            'parent_id' => 'nullable|integer',
        ]);

        $post = CommunityFeed::query()->findOrFail($id);

        if (! in_array($user->role, self::SK_COMMENT_ROLES, true)) {
            return response()->json(['message' => 'Only SK Officials may comment on this feed.'], 403);
        }

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            $parent = CommunityFeedComment::query()
                ->where('id', (int) $parentId)
                ->where('community_feed_id', $id)
                ->firstOrFail();

            if ($parent->parent_id !== null) {
                $parentId = $parent->parent_id;
            }
        }

        $comment = CommunityFeedComment::create([
            'community_feed_id' => $id,
            'parent_id' => $parentId,
            'user_id' => $user->id,
            'user_type' => 'sk_official',
            'author_name' => $user->name,
            'body' => $validated['body'],
        ]);

        if ($post->is_federation_wide && (int) $post->user_id !== (int) $user->id) {
            $dispatcher = app(SkFederationsNotificationDispatcher::class);
            $dispatcher->notifyFederationPortalCommunityFeedComment(
                (int) $user->id,
                (string) $user->name,
                $dispatcher->postLabel($post->title, $post->body),
                $validated['body'],
            );
        }

        $limiter->hit('sk_official', (int) $user->id);

        return response()->json(
            $this->presenter->formatComment($comment->load(['user', 'reactions']), $user->id, 'sk_official', $post->barangay_id),
            201
        );
    }

    public function update(Request $request, int $id, int $commentId): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'body' => 'required|string|max:'.FeedCommentRateLimiter::MAX_BODY_LENGTH,
        ]);

        $comment = $this->ownedComment($id, $commentId, $user);
        $comment->update(['body' => $validated['body']]);

        $post = CommunityFeed::query()->findOrFail($id);

        return response()->json(
            $this->presenter->formatComment($comment->fresh(['user', 'reactions']), $user->id, 'sk_official', $post->barangay_id)
        );
    }

    public function destroy(int $id, int $commentId): JsonResponse
    {
        $user = Auth::user();
        $comment = CommunityFeedComment::query()
            ->where('community_feed_id', $id)
            ->where('id', $commentId)
            ->firstOrFail();

        $isOwner = (int) $comment->user_id === (int) $user->id && $comment->user_type === 'sk_official';
        $isModerator = in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SK_FED], true);

        if (! $isOwner && ! $isModerator) {
            return response()->json(['message' => 'You cannot delete this comment.'], 403);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    private function ownedComment(int $feedId, int $commentId, User $user): CommunityFeedComment
    {
        return CommunityFeedComment::query()
            ->where('community_feed_id', $feedId)
            ->where('id', $commentId)
            ->where('user_id', $user->id)
            ->where('user_type', 'sk_official')
            ->firstOrFail();
    }
}
