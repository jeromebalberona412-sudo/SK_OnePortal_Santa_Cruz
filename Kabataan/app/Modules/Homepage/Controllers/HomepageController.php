<?php

namespace App\Modules\Homepage\Controllers;

use App\Http\Controllers\Controller;

class HomepageController extends Controller
{
    public function index()
    {
        $municipality = [
            'name' => 'Santa Cruz, Laguna',
            'portal' => 'SK OnePortal Kabataan',
            'description' => 'View-only youth community updates from barangay SK officials.',
        ];

        $barangays = [
            'Alipit',
            'Bagumbayan',
            'Bubukal',
            'Duhat',
            'Gatid',
            'Labuin',
            'Pagsawitan',
            'San Jose',
            'Santisima Cruz',
        ];

        $feedItems = [
            [
                'id' => 1,
                'type' => 'Event',
                'category' => 'Education',
                'barangay' => 'Pagsawitan',
                'author' => 'SK Barangay Pagsawitan',
                'posted_at' => '2 hours ago',
                'title' => 'Career Readiness Session for Senior High',
                'summary' => 'A career orientation and CV workshop for youth preparing for college and employment pathways.',
                'event_date' => 'March 30, 2026',
                'event_time' => '1:00 PM to 4:00 PM',
                'venue' => 'Pagsawitan Barangay Hall',
                'audience' => 'Ages 16 to 24',
                'details' => 'Topics include scholarship opportunities, interview practice, and local internship matching.',
            ],
            [
                'id' => 2,
                'type' => 'Program',
                'category' => 'Sports Development',
                'barangay' => 'Labuin',
                'author' => 'SK Barangay Labuin',
                'posted_at' => '5 hours ago',
                'title' => 'Weekend Basketball Skills Clinic',
                'summary' => 'Open clinic focused on fundamentals, teamwork, and youth fitness monitoring.',
                'event_date' => 'April 2, 2026',
                'event_time' => '8:00 AM to 11:00 AM',
                'venue' => 'Labuin Covered Court',
                'audience' => 'Ages 13 to 21',
                'details' => 'Participants will be grouped by age bracket. Bring water, proper shoes, and extra shirt.',
            ],
            [
                'id' => 3,
                'type' => 'Event',
                'category' => 'Health',
                'barangay' => 'San Jose',
                'author' => 'SK Barangay San Jose',
                'posted_at' => '1 day ago',
                'title' => 'Mental Health Peer Support Circle',
                'summary' => 'A guided peer support activity and stress management session for kabataan.',
                'event_date' => 'April 4, 2026',
                'event_time' => '3:00 PM to 5:00 PM',
                'venue' => 'San Jose Multipurpose Room',
                'audience' => 'Ages 15 to 30',
                'details' => 'Facilitated by youth volunteers and municipal health staff with confidential sharing spaces.',
            ],
            [
                'id' => 4,
                'type' => 'Program',
                'category' => 'Environment',
                'barangay' => 'Gatid',
                'author' => 'SK Barangay Gatid',
                'posted_at' => '2 days ago',
                'title' => 'River Cleanup and Waste Segregation Drive',
                'summary' => 'Community cleanup initiative with hands-on orientation on proper waste segregation.',
                'event_date' => 'April 6, 2026',
                'event_time' => '6:30 AM to 10:00 AM',
                'venue' => 'Gatid Riverside Area',
                'audience' => 'Ages 13 and above',
                'details' => 'Cleaning tools and sacks are available on site. Attendance certificates for volunteers are provided.',
            ],
            [
                'id' => 5,
                'type' => 'Event',
                'category' => 'Livelihood',
                'barangay' => 'Santisima Cruz',
                'author' => 'SK Barangay Santisima Cruz',
                'posted_at' => '3 days ago',
                'title' => 'Youth Digital Freelancing Orientation',
                'summary' => 'Introductory orientation on online freelancing, portfolio basics, and responsible digital work habits.',
                'event_date' => 'April 8, 2026',
                'event_time' => '9:00 AM to 12:00 PM',
                'venue' => 'Santisima Cruz Learning Hub',
                'audience' => 'Ages 17 to 30',
                'details' => 'Bring a notebook. Limited slots are prioritized for first-time participants.',
            ],
        ];

        $highlights = [
            ['label' => 'Total Barangays Featured', 'value' => '9'],
            ['label' => 'Active Event Posts', 'value' => '3'],
            ['label' => 'Active Program Posts', 'value' => '2'],
            ['label' => 'Coverage', 'value' => 'Santa Cruz, Laguna'],
        ];

        $categories = [
            'All',
            'Education',
            'Sports Development',
            'Health',
            'Environment',
            'Livelihood',
        ];

        return view('homepage::index', [
            'municipality' => $municipality,
            'barangays' => $barangays,
            'feedItems' => $feedItems,
            'highlights' => $highlights,
            'categories' => $categories,
        ]);
    }
}
