<?php

namespace App\Modules\Community_feed\Services;

use App\Models\CommunityFeed;
use App\Models\User;
use App\Services\CloudinaryService;
use App\Services\SkOfficialActivityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommunityFeedArchiveService
{
    public const RETENTION_DAYS = 30;

    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly SkOfficialActivityService $activityService,
    ) {
    }

    public function archive(CommunityFeed $post, User $user): CommunityFeed
    {
        $now = now();

        DB::table('community_feeds')
            ->where('id', $post->id)
            ->update([
                'is_archived' => DB::raw('true'),
                'archived_at' => $now,
                'deleted_at'  => null,
                'updated_at'  => $now,
            ]);

        $this->activityService->log(
            $user,
            'community_feed.archive',
            'Archived community feed post: '.($post->title ?: 'Post #'.$post->id),
            ['community_feed_id' => $post->id]
        );

        return $post->fresh(['user', 'barangay', 'images']);
    }

    public function restore(CommunityFeed $post, User $user): CommunityFeed
    {
        $now = now();

        DB::table('community_feeds')
            ->where('id', $post->id)
            ->update([
                'is_archived' => DB::raw('false'),
                'archived_at' => null,
                'deleted_at'  => null,
                'updated_at'  => $now,
            ]);

        $this->activityService->log(
            $user,
            'community_feed.restore',
            'Restored community feed post: '.($post->title ?: 'Post #'.$post->id),
            ['community_feed_id' => $post->id]
        );

        return $post->fresh(['user', 'barangay', 'images']);
    }

    public function permanentlyDelete(CommunityFeed $post, User $user): void
    {
        DB::transaction(function () use ($post, $user) {
            $post->loadMissing('images');
            $title = $post->title ?: 'Post #'.$post->id;
            $postId = $post->id;

            $this->deleteCloudinaryAssets($post);

            $post->images()->delete();
            $post->comments()->delete();
            $post->reactions()->delete();
            $post->delete();

            $this->activityService->log(
                $user,
                'community_feed.permanent_delete',
                'Permanently deleted community feed post: '.$title,
                ['community_feed_id' => $postId]
            );
        });
    }

    public function purgeExpired(): int
    {
        $cutoff = now()->subDays(self::RETENTION_DAYS);
        $purged = 0;

        CommunityFeed::query()
            ->whereRaw('"is_archived" = true')
            ->whereNotNull('archived_at')
            ->where('archived_at', '<=', $cutoff)
            ->with('images')
            ->orderBy('id')
            ->chunkById(50, function ($posts) use (&$purged) {
                foreach ($posts as $post) {
                    try {
                        DB::transaction(function () use ($post) {
                            $this->deleteCloudinaryAssets($post);
                            $post->images()->delete();
                            $post->comments()->delete();
                            $post->reactions()->delete();
                            $post->delete();
                        });
                        $purged++;
                    } catch (Throwable) {
                        continue;
                    }
                }
            });

        return $purged;
    }

    public function daysRemaining(?Carbon $archivedAt): int
    {
        if (! $archivedAt) {
            return self::RETENTION_DAYS;
        }

        $elapsed = $archivedAt->diffInDays(now());

        return max(0, self::RETENTION_DAYS - $elapsed);
    }

    public function daysRemainingTier(int $daysRemaining): string
    {
        if ($daysRemaining >= 15) {
            return 'green';
        }

        if ($daysRemaining >= 7) {
            return 'orange';
        }

        return 'red';
    }

    public function userCanAccess(CommunityFeed $post, User $user): bool
    {
        if (! in_array($user->role, [User::ROLE_SK_OFFICIAL, User::ROLE_SK_FED, User::ROLE_ADMIN], true)) {
            return false;
        }

        if ($user->role === User::ROLE_ADMIN || $user->role === User::ROLE_SK_FED) {
            return true;
        }

        return (int) $post->barangay_id === (int) $user->barangay_id;
    }

    private function deleteCloudinaryAssets(CommunityFeed $post): void
    {
        foreach ($post->images as $image) {
            $publicId = $image->public_id;

            if (! $publicId && $image->image_url) {
                $publicId = $this->cloudinary->extractPublicIdFromUrl($image->image_url);
            }

            if (! $publicId || ! $this->cloudinary->isConfigured()) {
                continue;
            }

            try {
                $this->cloudinary->delete($publicId);
            } catch (Throwable) {
                continue;
            }
        }
    }
}
