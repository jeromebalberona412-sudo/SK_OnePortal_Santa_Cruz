<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        return view('dashboard::index', [
            'user' => $user
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
    public function barangay(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $barangays = [
            'alipit'         => 'Alipit',
            'bagumbayan'     => 'Bagumbayan',
            'bubukal'        => 'Bubukal',
            'duhat'          => 'Duhat',
            'gatid'          => 'Gatid',
            'labuin'         => 'Labuin',
            'pagsawitan'     => 'Pagsawitan',
            'san-jose'       => 'San Jose',
            'santisima-cruz' => 'Santisima Cruz',
        ];

        $name = $barangays[$slug] ?? ucfirst(str_replace('-', ' ', $slug));

        $colors = [
            'alipit'         => '#4CAF50',
            'bagumbayan'     => '#2196F3',
            'bubukal'        => '#9C27B0',
            'duhat'          => '#FF9800',
            'gatid'          => '#009688',
            'labuin'         => '#f44336',
            'pagsawitan'     => '#673AB7',
            'san-jose'       => '#0450a8',
            'santisima-cruz' => '#FF5722',
        ];

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
                'date'      => 'March 30, 2026',
                'time'      => '1:00 PM – 4:00 PM',
                'venue'     => "{$name} Barangay Hall",
                'audience'  => 'Ages 16 to 24',
            ],
            [
                'id' => 2, 'type' => 'Announcement', 'type_class' => 'announcement',
                'author'    => "SK Barangay {$name}",
                'posted_at' => '1 day ago',
                'title'     => '📢 SK Monthly Meeting — Schedule Update',
                'text'      => 'Our monthly meeting has been moved to next Friday. Please mark your calendars and ensure attendance.',
                'date'      => 'April 4, 2026',
                'time'      => '3:00 PM',
                'venue'     => "{$name} Multipurpose Hall",
                'audience'  => 'All SK Members',
            ],
            [
                'id' => 3, 'type' => 'Activity', 'type_class' => 'activity',
                'author'    => "SK Barangay {$name}",
                'posted_at' => '3 days ago',
                'title'     => 'Community Clean-Up Drive 🌱',
                'text'      => "Join us for our monthly community clean-up drive. Let's keep our barangay clean and green!",
                'date'      => 'April 6, 2026',
                'time'      => '6:30 AM – 10:00 AM',
                'venue'     => "{$name} Riverside Area",
                'audience'  => 'Ages 13 and above',
            ],
        ];

        return view('dashboard::barangay', [
            'user'     => $user,
            'slug'     => $slug,
            'name'     => $name,
            'color'    => $colors[$slug] ?? '#667eea',
            'officers' => $officers,
            'posts'    => $posts,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}

