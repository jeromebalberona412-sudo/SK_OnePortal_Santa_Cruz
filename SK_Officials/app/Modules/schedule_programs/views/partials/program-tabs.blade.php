@php
    $activeTab = $activeTab ?? 'form';
    $programType = $programType ?? 'scholarship';
    
    // Program configuration
    $programs = [
        'scholarship' => [
            'name' => 'Equitable Access to Quality Education',
            'routes' => [
                'form' => 'scholar.application-form',
                'requests' => 'scholarship.application-request',
                'list' => 'scholar.list',
                'evaluation' => 'scholar.evaluation'
            ]
        ],
        'environmental' => [
            'name' => 'Environmental Protection',
            'routes' => [
                'form' => 'environmental.schedule',
                'requests' => 'environmental.requests',
                'list' => 'environmental.list',
                'evaluation' => 'environmental.evaluation'
            ]
        ],
        'disaster' => [
            'name' => 'Disaster Risk Reduction',
            'routes' => [
                'form' => 'disaster.schedule',
                'requests' => 'disaster.requests',
                'list' => 'disaster.list',
                'evaluation' => 'disaster.evaluation'
            ]
        ],
        'livelihood' => [
            'name' => 'Youth Employment and Livelihood',
            'routes' => [
                'form' => 'livelihood.schedule',
                'requests' => 'livelihood.requests',
                'list' => 'livelihood.list',
                'evaluation' => 'livelihood.evaluation'
            ]
        ],
        'antidrug' => [
            'name' => 'Anti-Drug and Peace and Order',
            'routes' => [
                'form' => 'antidrug.schedule',
                'requests' => 'antidrug.requests',
                'list' => 'antidrug.list',
                'evaluation' => 'antidrug.evaluation'
            ]
        ],
        'gender' => [
            'name' => 'Gender Sensitivity',
            'routes' => [
                'form' => 'gender.schedule',
                'requests' => 'gender.requests',
                'list' => 'gender.list',
                'evaluation' => 'gender.evaluation'
            ]
        ],
        'feeding' => [
            'name' => 'Feeding Program',
            'routes' => [
                'form' => 'feeding.schedule',
                'requests' => 'feeding.requests',
                'list' => 'feeding.list',
                'evaluation' => 'feeding.evaluation'
            ]
        ],
        'sports' => [
            'name' => 'Sports Development',
            'routes' => [
                'form' => 'sports-application-form',
                'requests' => 'sports-requests',
                'list' => 'sport.list',
                'evaluation' => 'sports.evaluation'
            ]
        ],
        'others' => [
            'name' => 'Others',
            'routes' => [
                'form' => 'others.schedule',
                'requests' => 'others.requests',
                'list' => 'others.list',
                'evaluation' => 'others.evaluation'
            ]
        ]
    ];
    
    $currentProgram = $programs[$programType] ?? $programs['scholarship'];
@endphp

<nav class="scholarship-tab-bar" aria-label="{{ $currentProgram['name'] }} sections">
    <a href="{{ route($currentProgram['routes']['form']) }}"
       class="scholarship-tab {{ $activeTab === 'form' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
        </svg>
        Program Schedule
    </a>
    <a href="{{ route($currentProgram['routes']['requests']) }}"
       class="scholarship-tab {{ $activeTab === 'requests' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>
        </svg>
        Program Request
    </a>
    <a href="{{ route($currentProgram['routes']['list']) }}"
       class="scholarship-tab {{ $activeTab === 'list' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        Program List
    </a>
    <a href="{{ route($currentProgram['routes']['evaluation']) }}"
       class="scholarship-tab {{ $activeTab === 'evaluation' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
        Evaluation
    </a>
</nav>
