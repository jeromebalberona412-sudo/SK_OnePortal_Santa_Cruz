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
                'type' => 'Announcement',
                'category' => 'General',
                'barangay' => 'San Jose',
                'author' => 'SK Barangay San Jose',
                'posted_at' => '4 hours ago',
                'title' => '📢 SK Monthly Meeting Rescheduled',
                'summary' => 'Reminder to all SK members: our monthly meeting has been moved to next Friday. Please mark your calendars and ensure your attendance.',
                'event_date' => 'April 4, 2026',
                'event_time' => '3:00 PM',
                'venue' => 'San Jose Multipurpose Room',
                'audience' => 'All SK Members',
                'details' => 'Agenda includes upcoming projects and budget allocation for Q2 2026.',
            ],
            [
                'id' => 3,
                'type' => 'Activity',
                'category' => 'Environment',
                'barangay' => 'Gatid',
                'author' => 'SK Barangay Gatid',
                'posted_at' => '1 day ago',
                'title' => 'River Cleanup and Waste Segregation Drive',
                'summary' => 'Community cleanup initiative with hands-on orientation on proper waste segregation for kabataan volunteers.',
                'event_date' => 'April 6, 2026',
                'event_time' => '6:30 AM to 10:00 AM',
                'venue' => 'Gatid Riverside Area',
                'audience' => 'Ages 13 and above',
                'details' => 'Cleaning tools and sacks are available on site. Attendance certificates for volunteers are provided.',
            ],
            [
                'id' => 4,
                'type' => 'Program',
                'category' => 'Sports Development',
                'barangay' => 'Labuin',
                'author' => 'SK Barangay Labuin',
                'posted_at' => '2 days ago',
                'title' => 'Weekend Basketball Skills Clinic',
                'summary' => 'Open clinic focused on fundamentals, teamwork, and youth fitness monitoring. All skill levels welcome.',
                'event_date' => 'April 12, 2026',
                'event_time' => '8:00 AM to 11:00 AM',
                'venue' => 'Labuin Covered Court',
                'audience' => 'Ages 13 to 21',
                'details' => 'Participants will be grouped by age bracket. Bring water, proper shoes, and extra shirt.',
            ],
            [
                'id' => 5,
                'type' => 'Activity',
                'category' => 'Health',
                'barangay' => 'Alipit',
                'author' => 'SK Barangay Alipit',
                'posted_at' => '2 days ago',
                'title' => 'Mental Health Peer Support Circle',
                'summary' => 'A guided peer support activity and stress management session for kabataan facilitated by youth volunteers.',
                'event_date' => 'April 10, 2026',
                'event_time' => '3:00 PM to 5:00 PM',
                'venue' => 'Alipit Barangay Hall',
                'audience' => 'Ages 15 to 30',
                'details' => 'Facilitated by youth volunteers and municipal health staff with confidential sharing spaces.',
            ],
            [
                'id' => 6,
                'type' => 'Program',
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
            [
                'id' => 7,
                'type' => 'Announcement',
                'category' => 'General',
                'barangay' => 'Duhat',
                'author' => 'SK Barangay Duhat',
                'posted_at' => '4 days ago',
                'title' => '🎓 Scholarship Applications Now Open',
                'summary' => 'The Education Assistance Program is now accepting applications for the upcoming semester. Financial support for deserving youth.',
                'event_date' => 'Deadline: March 31, 2026',
                'event_time' => 'Office hours',
                'venue' => 'Duhat Barangay Hall',
                'audience' => 'College students, Ages 18 to 24',
                'details' => 'Submit requirements at the barangay hall. Slots are limited — apply early.',
            ],
        ];

        $highlights = [
            ['label' => 'Total Barangays Featured', 'value' => '9'],
            ['label' => 'Active Event Posts', 'value' => '3'],
            ['label' => 'Active Program Posts', 'value' => '2'],
            ['label' => 'Coverage', 'value' => 'Santa Cruz, Laguna'],
        ];

        $programCategories = [
            ['key' => 'education',   'label' => 'Education',            'count' => 1, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/><path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg>'],
            ['key' => 'anti-drugs',  'label' => 'Anti-Drugs',           'count' => 0, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>'],
            ['key' => 'agriculture', 'label' => 'Agriculture',          'count' => 0, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg>'],
            ['key' => 'disaster',    'label' => 'Disaster Preparedness', 'count' => 0, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg>'],
            ['key' => 'sports',      'label' => 'Sports Development',   'count' => 1, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>'],
            ['key' => 'gender',      'label' => 'Gender and Development','count' => 0, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>'],
            ['key' => 'health',      'label' => 'Health',               'count' => 1, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>'],
            ['key' => 'environment', 'label' => 'Environment',          'count' => 1, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16A8 8 0 0010 2zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"/></svg>'],
            ['key' => 'livelihood',  'label' => 'Livelihood',           'count' => 1, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/><path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/></svg>'],
            ['key' => 'others',      'label' => 'Others',               'count' => 0, 'icon' => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>'],
        ];

        $barangayProfiles = [
            ['name' => 'Alipit',         'chairman' => '[SK Chairman]', 'color' => '#4CAF50', 'programs' => 2, 'events' => 3, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Bagumbayan',     'chairman' => '[SK Chairman]', 'color' => '#2196F3', 'programs' => 1, 'events' => 2, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Bubukal',        'chairman' => '[SK Chairman]', 'color' => '#9C27B0', 'programs' => 0, 'events' => 1, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Duhat',          'chairman' => '[SK Chairman]', 'color' => '#FF9800', 'programs' => 1, 'events' => 2, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Gatid',          'chairman' => '[SK Chairman]', 'color' => '#009688', 'programs' => 1, 'events' => 1, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Labuin',         'chairman' => '[SK Chairman]', 'color' => '#f44336', 'programs' => 2, 'events' => 2, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Pagsawitan',     'chairman' => '[SK Chairman]', 'color' => '#673AB7', 'programs' => 1, 'events' => 3, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'San Jose',       'chairman' => '[SK Chairman]', 'color' => '#0450a8', 'programs' => 0, 'events' => 2, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
            ['name' => 'Santisima Cruz', 'chairman' => '[SK Chairman]', 'color' => '#FF5722', 'programs' => 2, 'events' => 1, 'members' => ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
        ];

        return view('homepage::index', [
            'municipality'      => $municipality,
            'feedItems'         => $feedItems,
            'highlights'        => $highlights,
            'programCategories' => $programCategories,
            'barangayProfiles'  => $barangayProfiles,
        ]);
    }

    public function about()
    {
        return view('homepage::about');
    }

    public function kkProfiling(string $barangay)
    {
        $validBarangays = [
            'alipit', 'bagumbayan', 'barangay-i', 'barangay-ii', 'barangay-iii',
            'barangay-iv', 'barangay-v', 'bubukal', 'calios', 'duhat', 'gatid',
            'jasaan', 'labuin', 'malinao', 'oogong', 'pagsawitan', 'palasan',
            'patimbao', 'san-jose', 'san-juan', 'san-pablo-norte', 'san-pablo-sur',
            'santisima-cruz', 'santo-angel-central', 'santo-angel-norte', 'santo-angel-sur',
        ];

        $slug = strtolower($barangay);

        if (!in_array($slug, $validBarangays)) {
            abort(404);
        }

        // Convert slug to display name
        $displayName = ucwords(str_replace('-', ' ', $slug));
        // Fix special cases
        $displayName = str_replace(['Barangay I', 'Barangay Ii', 'Barangay Iii', 'Barangay Iv', 'Barangay V'],
                                   ['Barangay I (Poblacion I)', 'Barangay II (Poblacion II)', 'Barangay III (Poblacion III)', 'Barangay IV (Poblacion IV)', 'Barangay V (Poblacion V)'],
                                   $displayName);

        return view('homepage::kkprofiling', [
            'barangay' => $displayName,
            'slug'     => $slug,
        ]);
    }

    public function kkProfilingSubmit(\Illuminate\Http\Request $request, string $barangay)
    {
        $request->validate([
            'last_name'    => 'required|string|max:100',
            'first_name'   => 'required|string|max:100',
            'age'          => 'required|integer|min:15|max:30',
            'birthday'     => 'required|date',
            'sex'          => 'required|in:Male,Female',
            'civil_status' => 'required|string',
            'purok_zone'   => 'required|string|max:100',
        ]);

        // Prototype: just flash success and redirect back
        return redirect()
            ->route('kkprofiling', ['barangay' => $barangay])
            ->with('success', 'Your KK Profiling submission has been received! It will be reviewed by your barangay SK officials.');
    }
}
