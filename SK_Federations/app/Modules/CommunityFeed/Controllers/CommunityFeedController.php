<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\CommunityFeed\Services\CommunityFeedService;
use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityFeedController extends Controller
{
    public function __construct(private readonly CommunityFeedService $feedService)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id;

        return view('community_feed::index', [
            'user' => $request->user(),
            'barangayProfiles' => $this->feedService->listBarangayProfiles($tenantId),
        ]);
    }

    public function skFedProfile(Request $request): View
    {
        return view('community_feed::sk-fed-profile', ['user' => $request->user()]);
    }

    public function createPost(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Prototype: just redirect back with success
        return redirect()->route('sk-fed-profile')->with('success', 'Post created successfully.');
    }
    public function barangayProfile(Request $request, string $slug): View
    {
        $tenantId = $request->user()?->tenant_id;
        $profile = $this->feedService->resolveBarangayProfile($slug, $tenantId);

        if ($profile === null) {
            abort(404);
        }

        $name = $profile['name'];
        $color = $profile['color'];

        $officers = [
            'chairman'   => '[SK Chairman]',
            'vice'       => '[Vice Chairman]',
            'secretary'  => '[Secretary]',
            'treasurer'  => '[Treasurer]',
            'auditor'    => '[Auditor]',
            'pro'        => '[PRO]',
            'councilors' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]'],
        ];

        $posts = [
            [
                'id' => 1, 'type' => 'Event', 'type_class' => 'event',
                'author'    => "SK Barangay {$name}",
                'posted_at' => '2 hours ago',
                'title'     => "Career Readiness Session — {$name}",
                'text'      => 'A career orientation and CV workshop for youth preparing for college and employment pathways.',
                'date'      => 'March 30, 2026', 'time' => '1:00 PM – 4:00 PM',
                'venue'     => "{$name} Barangay Hall", 'audience' => 'Ages 16 to 24',
            ],
            [
                'id' => 2, 'type' => 'Announcement', 'type_class' => 'announcement',
                'author'    => "SK Barangay {$name}",
                'posted_at' => '1 day ago',
                'title'     => '📢 SK Monthly Meeting — Schedule Update',
                'text'      => 'Our monthly meeting has been moved to next Friday. Please mark your calendars.',
                'date'      => 'April 4, 2026', 'time' => '3:00 PM',
                'venue'     => "{$name} Multipurpose Hall", 'audience' => 'All SK Members',
            ],
            [
                'id' => 3, 'type' => 'Activity', 'type_class' => 'activity',
                'author'    => "SK Barangay {$name}",
                'posted_at' => '3 days ago',
                'title'     => 'Community Clean-Up Drive 🌱',
                'text'      => "Join us for our monthly community clean-up drive. Let's keep our barangay clean and green!",
                'date'      => 'April 6, 2026', 'time' => '6:30 AM – 10:00 AM',
                'venue'     => "{$name} Riverside Area", 'audience' => 'Ages 13 and above',
            ],
        ];

        return view('community_feed::barangay-profile', [
            'user'     => $request->user(),
            'slug'     => $slug,
            'name'     => $name,
            'color'    => $color,
            'profile'  => $profile,
            'officers' => $officers,
            'posts'    => $posts,
        ]);
    }
}
