<?php

namespace App\Modules\Dashboard\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementReaction;
use App\Models\KabataanRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AnnouncementFeedController extends Controller
{
    // GET /api/feed?filter=all&page=1
    public function feed(Request $request): JsonResponse
    {
        $user         = Auth::user();
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();
        $barangayId   = $registration?->barangay_id ?? $user->barangay_id;

        // If no registration found, try matching by email across the shared DB
        if (!$barangayId) {
            $registration = KabataanRegistration::where('email', $user->email)->latest()->first();
            $barangayId   = $registration?->barangay_id;
        }

        if (!$barangayId) {
            return response()->json([
                'data' => [], 'current_page' => 1, 'last_page' => 1, 'user_id' => $user->id,
                '_debug' => 'no barangay_id found for user ' . $user->id . ' / ' . $user->email,
            ]);
        }

        $query = Announcement::with(['barangay', 'comments', 'user'])
            ->withCount('reactions')
            ->where(function ($q) use ($barangayId) {
                $q->where('barangay_id', $barangayId)
                  ->orWhereRaw('"is_federation_wide" = true');
            })
            ->orderByDesc('created_at');

        if ($request->filter && $request->filter !== 'all') {
            $query->where('type', $request->filter);
        }

        $posts = $query->paginate(10);

        return response()->json([
            'data'         => collect($posts->items())->map(fn($p) => $this->formatPost($p, $user->id)),
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'user_id'      => $user->id,
            '_debug'       => 'barangay_id=' . $barangayId . ' total=' . $posts->total(),
        ]);
    }

    // POST /api/feed/{id}/react
    public function react(int $id): JsonResponse
    {
        $user     = Auth::user();
        $existing = AnnouncementReaction::where([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'kabataan',
        ])->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            AnnouncementReaction::create([
                'announcement_id' => $id,
                'user_id'         => $user->id,
                'user_type'       => 'kabataan',
            ]);
            $liked = true;
        }

        $count = AnnouncementReaction::where('announcement_id', $id)->count();
        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    // POST /api/feed/{id}/comment
    public function comment(Request $request, int $id): JsonResponse
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $user         = Auth::user();
        $registration = KabataanRegistration::where('user_id', $user->id)->latest()->first();
        $authorName   = $registration
            ? trim($registration->first_name . ' ' . $registration->last_name)
            : $user->name;

        $comment = AnnouncementComment::create([
            'announcement_id' => $id,
            'user_id'         => $user->id,
            'user_type'       => 'kabataan',
            'author_name'     => $authorName,
            'body'            => $request->body,
        ]);

        return response()->json([
            'id'          => $comment->id,
            'author_name' => $comment->author_name,
            'body'        => $comment->body,
            'time'        => $comment->created_at->diffForHumans(),
        ], 201);
    }

    private function formatPost(Announcement $post, int $userId): array
    {
        $liked = AnnouncementReaction::where([
            'announcement_id' => $post->id,
            'user_id'         => $userId,
            'user_type'       => 'kabataan',
        ])->exists();

        $authorName = $post->user?->name
            ?? ($post->is_federation_wide ? 'SK Federation' : ('SK Brgy. ' . ($post->barangay?->name ?? '')));

        return [
            'id'                 => $post->id,
            'type'               => $post->type,
            'title'              => $post->title,
            'body'               => $post->body,
            'image_url'          => $post->image_url,
            'link_url'           => $post->link_url,
            'is_federation_wide' => (bool) $post->is_federation_wide,
            'barangay_name'      => $post->barangay?->name,
            'author_name'        => $authorName,
            'likes'              => $post->reactions_count,
            'liked'              => $liked,
            'time'               => $post->created_at->diffForHumans(),
            'comments'           => $post->comments->map(fn($c) => [
                'id'          => $c->id,
                'author_name' => $c->author_name,
                'body'        => $c->body,
                'time'        => $c->created_at->diffForHumans(),
            ])->values(),
        ];
    }
}
