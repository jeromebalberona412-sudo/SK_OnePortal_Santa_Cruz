<?php

namespace App\Modules\Community_feed\Controllers;

use App\Models\CommunityFeed;
use App\Models\CommunityFeedImage;
use App\Modules\Community_feed\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class CommunityFeedImageController extends Controller
{
    private const MAX_IMAGES = 20;

    public function __construct(
        private readonly CloudinaryService $cloudinary,
    ) {
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $post = CommunityFeed::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'images' => 'required|array|max:'.self::MAX_IMAGES,
            'images.*' => 'image|max:5120',
        ]);

        $remaining = self::MAX_IMAGES - $post->images()->count();
        if ($remaining <= 0) {
            return response()->json(['message' => 'This post already has the maximum number of images.'], 422);
        }

        $files = $request->file('images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $uploaded = [];
        $sort = $post->images()->max('sort_order') ?? -1;
        $now = now();

        foreach (array_slice($files, 0, $remaining) as $file) {
            if ($file === null) {
                continue;
            }

            try {
                $publicId = 'post_'.$post->id.'_'.Str::random(10);
                $result = $this->cloudinary->upload($file, $publicId);
                $sort++;

                $image = CommunityFeedImage::create([
                    'community_feed_id' => $post->id,
                    'image_url' => $result['url'],
                    'public_id' => $result['public_id'],
                    'sort_order' => $sort,
                    'created_at' => $now,
                ]);

                $uploaded[] = [
                    'id' => $image->id,
                    'url' => $this->cloudinary->normalizeUrl($image->image_url),
                ];
            } catch (Throwable) {
                continue;
            }
        }

        return response()->json(['images' => $uploaded], 201);
    }

    public function destroy(int $id, int $imageId): JsonResponse
    {
        $post = CommunityFeed::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $image = CommunityFeedImage::query()
            ->where('community_feed_id', $post->id)
            ->where('id', $imageId)
            ->firstOrFail();

        $publicId = $image->public_id ?: $this->cloudinary->extractPublicIdFromUrl($image->image_url);
        if ($publicId && $this->cloudinary->isConfigured()) {
            try {
                $this->cloudinary->delete($publicId);
            } catch (Throwable) {
                // Keep deleting the DB row even if remote cleanup fails.
            }
        }

        $image->delete();

        return response()->json(['success' => true]);
    }
}
