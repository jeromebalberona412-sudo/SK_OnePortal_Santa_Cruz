<?php

/**
 * Survey program committees — single config drives routes, UI, and sample data per program.
 */
return [
    'environmental' => [
        'title' => 'Environmental Protection',
        'sk_head' => 'Juan dela Cruz',
        'activities' => ['Clean-Up Drive', 'Payroll for Laborer', 'Tree Planting'],
        'description' => 'Create survey questions for environmental programs and review Kabataan feedback with analytics.',
    ],
    'disaster' => [
        'title' => 'Disaster Risk Reduction and Resiliency',
        'sk_head' => 'Carlo Reyes',
        'activities' => [
            'Training on Disaster Preparedness for Youth Volunteer Groups',
            'Distribution of Relief Goods for KK Members',
        ],
        'description' => 'Manage disaster preparedness surveys and youth volunteer feedback.',
    ],
    'livelihood' => [
        'title' => 'Youth Employment and Livelihood',
        'sk_head' => 'Ana Villanueva',
        'activities' => ['Livelihood Training', 'Food and Other Supplies'],
        'description' => 'Track livelihood program surveys and participant responses.',
    ],
    'medicines' => [
        'title' => 'Medicines',
        'sk_head' => 'Jose Mendoza',
        'activities' => ['Medicines / Medical Equipment'],
        'description' => 'Manage health-related surveys for medicine distribution programs.',
    ],
    'antidrug' => [
        'title' => 'Anti-Drug and Peace and Order',
        'sk_head' => 'Ramon Garcia',
        'activities' => ['Orientation for Anti-Drug and Physical Abuse', 'Foods and Accommodations'],
        'description' => 'Collect feedback from anti-drug and peace and order orientations.',
    ],
    'gender' => [
        'title' => 'Gender Sensitivity',
        'sk_head' => 'Liza Torres',
        'activities' => ['Orientation on GAD and VAWC', 'Foods and Accommodations'],
        'description' => 'Review GAD and VAWC orientation survey responses from youth.',
    ],
    'feeding' => [
        'title' => 'Feeding Program for KK Members',
        'sk_head' => 'Kristine Bautista',
        'activities' => [
            'Improve health and physique of children',
            'Youth and Children in the vicinity of Barangay',
        ],
        'description' => 'Monitor feeding program surveys and community feedback.',
    ],
    'others' => [
        'title' => 'Other Programs',
        'sk_head' => 'Patricia Flores',
        'activities' => [
            'Katipunan ng Kabataan (KK) General Assembly',
            'Barangay Day Celebration',
            'Youth Week',
        ],
        'description' => 'Manage surveys for KK assemblies and community celebrations.',
    ],
];
