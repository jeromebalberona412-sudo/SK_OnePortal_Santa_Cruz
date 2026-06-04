@php
    $activeTab = $activeTab ?? 'form';
    $programType = $programType ?? 'scholarship';
    
    // Program configuration
    $programs = [
        'scholarship' => [
            'name' => 'Equitable Access to Quality Education',
            'routes' => [
                'form' => 'scholarship.schedule',
                'requests' => 'scholarship.applications',
                'list' => 'approved-scholars',
                'rejected' => 'rejected-scholars',
                'evaluation' => 'scholar.evaluation'
            ],
            'labels' => [
                'form' => 'Scholarship Schedule',
                'requests' => 'Scholarship Applications',
                'list' => 'Approved Scholars',
                'rejected' => 'Rejected Scholars',
                'evaluation' => 'Evaluation'
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
    ];
    
    $currentProgram = $programs[$programType] ?? $programs['scholarship'];
    $tabLabels = $currentProgram['labels'] ?? [
        'form' => 'Program Schedule',
        'requests' => 'Program Request',
        'list' => 'Program List',
        'evaluation' => 'Evaluation'
    ];
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
        {{ $tabLabels['form'] }}
    </a>
    <a href="{{ route($currentProgram['routes']['requests']) }}"
       class="scholarship-tab {{ $activeTab === 'requests' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>
        </svg>
        {{ $tabLabels['requests'] }}
    </a>
    <a href="{{ route($currentProgram['routes']['list']) }}"
       class="scholarship-tab {{ $activeTab === 'list' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        {{ $tabLabels['list'] }}
    </a>
    @if(!empty($currentProgram['routes']['rejected']))
    <a href="{{ route($currentProgram['routes']['rejected']) }}"
       class="scholarship-tab {{ $activeTab === 'rejected' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        {{ $tabLabels['rejected'] ?? 'Rejected Scholars' }}
    </a>
    @endif
    <a href="{{ route($currentProgram['routes']['evaluation']) }}"
       class="scholarship-tab {{ $activeTab === 'evaluation' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
        {{ $tabLabels['evaluation'] }}
    </a>
</nav>
