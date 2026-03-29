<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityFeedController extends Controller
{
    public function index(Request $request): View
    {
        return view('community_feed::index', ['user' => $request->user()]);
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
        $barangays = [
            'alipit'         => ['name' => 'Alipit',         'color' => '#4CAF50'],
            'bagumbayan'     => ['name' => 'Bagumbayan',     'color' => '#2196F3'],
            'bubukal'        => ['name' => 'Bubukal',        'color' => '#9C27B0'],
            'duhat'          => ['name' => 'Duhat',          'color' => '#FF9800'],
            'gatid'          => ['name' => 'Gatid',          'color' => '#009688'],
            'labuin'         => ['name' => 'Labuin',         'color' => '#f44336'],
            'pagsawitan'     => ['name' => 'Pagsawitan',     'color' => '#673AB7'],
            'san-jose'       => ['name' => 'San Jose',       'color' => '#0450a8'],
            'santisima-cruz' => ['name' => 'Santisima Cruz', 'color' => '#FF5722'],
        ];

        if (!isset($barangays[$slug])) {
            abort(404);
        }

        $name  = $barangays[$slug]['name'];
        $color = $barangays[$slug]['color'];

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
            'officers' => $officers,
            'posts'    => $posts,
        ]);
    }
}
