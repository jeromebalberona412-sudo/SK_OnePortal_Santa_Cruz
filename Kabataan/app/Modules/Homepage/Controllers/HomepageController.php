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

        $heroStats = [];

        $valueProps = [
            [
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l8 4v5c0 5-3.6 8.7-8 9-4.4-.3-8-4-8-9V7l8-4z"/><path d="M9 12l2 2 4-5"/></svg>',
                'title' => 'Discover Opportunities',
                'description' => 'Browse education, health, sports, agriculture, and livelihood programs happening in your barangay.',
            ],
            [
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 12h10M7 8h10M7 16h6"/><path d="M4 6h16v12H4z"/></svg>',
                'title' => 'Connect & Participate',
                'description' => 'Join activities, react to announcements, and stay close to the initiatives shaping your community.',
            ],
            [
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18"/><path d="M17 7H9.5a3.5 3.5 0 000 7H14a3 3 0 010 6H7"/></svg>',
                'title' => 'See Where Money Goes',
                'description' => 'Transparent budgets and impact metrics make it easier to understand how youth programs are funded.',
            ],
            [
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3 6 7 .8-5.2 4.6 1.6 6.8L12 16.9 5.6 20.2l1.6-6.8L2 8.8 9 8l3-6z"/></svg>',
                'title' => 'Build Your Profile',
                'description' => 'Earn badges, track accomplishments, and grow a digital record of youth leadership and service.',
            ],
        ];

        $heroImages = [
            asset('modules/homepage/image/1.png'),
            asset('modules/homepage/image/2.png'),
            asset('modules/homepage/image/3.png'),
            asset('modules/homepage/image/4.png'),
            asset('modules/homepage/image/5.png'),
        ];

        $featuredPrograms = [];

        $barangayTabs = [
            ['key' => 'all', 'label' => 'All Barangays'],
            ['key' => 'pagsawitan', 'label' => 'Pagsawitan'],
            ['key' => 'san-jose', 'label' => 'San Jose'],
            ['key' => 'gatid', 'label' => 'Gatid'],
            ['key' => 'labuin', 'label' => 'Labuin'],
            ['key' => 'alipit', 'label' => 'Alipit'],
            ['key' => 'santisima-cruz', 'label' => 'Santisima Cruz'],
            ['key' => 'duhat', 'label' => 'Duhat'],
        ];

        $barangayCards = [];

        $feedItems = [];

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

        return view('homepage::homepage', [
            'municipality'      => $municipality,
            'heroStats'         => $heroStats,
            'valueProps'        => $valueProps,
            'heroImages'        => $heroImages,
            'featuredPrograms'  => $featuredPrograms,
            'barangayTabs'      => $barangayTabs,
            'barangayCards'     => $barangayCards,
            'feedItems'         => $feedItems,
            'highlights'        => $highlights,
            'programCategories' => $programCategories,
            'barangayProfiles'  => $barangayProfiles,
            'faqs'              => $this->getFaqs(),
        ]);
    }

    public function programs()
    {
        return view('homepage::programs');
    }

    private function getFaqs(): array
    {
        return cache()->remember('kabataan_faqs_v3', 3600, function () {
            return [
                [
                    'id' => 1,
                    'category' => 'general',
                    'question' => 'What is SKonePortal?',
                    'answer' => 'SKonePortal is the official digital platform of the Municipality of Santa Cruz that connects the Kabataan, Sangguniang Kabataan (SK) Officials, SK Federation, and the Local Youth Development Office (LYDO). It provides online access to youth programs, scholarship applications, events, announcements, surveys, profiling, and other SK-related services in one centralized system.',
                ],
                [
                    'id' => 2,
                    'category' => 'account',
                    'question' => 'How do I create an account?',
                    'answer' => 'Click the Sign Up button on the homepage and complete the registration form with accurate personal information. Verify your email address if required, then submit the form. Once your registration is approved or verified, you can sign in and access the system.',
                ],
                [
                    'id' => 3,
                    'category' => 'account',
                    'question' => 'How do I sign in to my account?',
                    'answer' => 'Click the Sign In button, enter your registered email address and password, then click Login. If you forget your password, use the Forgot Password option to receive a reset link by email.',
                ],
                [
                    'id' => 4,
                    'category' => 'services',
                    'question' => 'What services can I access through SKonePortal?',
                    'answer' => 'Registered users can complete the KK Profiling Form, apply for scholarship programs, join events and activities, receive announcements, answer surveys, submit required documents, track application status, and access other youth-related services offered by the Municipality of Santa Cruz.',
                ],
                [
                    'id' => 5,
                    'category' => 'general',
                    'question' => 'Who can use SKonePortal?',
                    'answer' => 'SKonePortal is intended for Kabataan residing in the Municipality of Santa Cruz, SK Officials, SK Federation members, the Local Youth Development Office (LYDO), and other authorized municipal personnel, depending on their assigned roles and permissions.',
                ],
            ];
        });
    }
}
