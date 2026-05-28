@php
    $activeTab = $activeTab ?? 'requests';
    $pageTitle = $pageTitle ?? '';
    $pageSubtitle = $pageSubtitle ?? '';
    $programType = $programType ?? 'scholarship';
    $programTitle = $programTitle ?? null;
    $programDescription = $programDescription ?? null;
@endphp
<a href="/schedule-programs" class="schol-back-top">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Back to Schedule Programs
</a>

<!-- ── Program Header ── -->
<section class="program-header-section">
    <div class="program-header-content">
        <h1 class="program-header-title">
            {{ $programTitle ?? (
                $programType === 'environmental' ? 'Environmental Protection' :
                ($programType === 'disaster' ? 'Disaster Risk Reduction and Resiliency' :
                ($programType === 'livelihood' ? 'Youth Employment and Livelihood' :
                ($programType === 'medicines' ? 'Medicines' :
                ($programType === 'antidrug' ? 'Anti-Drug and Peace and Order' :
                ($programType === 'gender' ? 'Gender Sensitivity' :
                ($programType === 'feeding' ? 'Feeding Program for KK Members' :
                ($programType === 'sports' ? 'Sports Development' :
                ($programType === 'others' ? 'Others' : 'Equitable Access to Quality Education')))))))))
            }}
        </h1>
        <p class="program-header-description">
            {{ $programDescription ?? (
                $programType === 'environmental' ? 'Manage environmental programs, track participation, and promote sustainable practices to protect our environment for future generations.' :
                ($programType === 'disaster' ? 'Manage disaster preparedness programs, track volunteers, and strengthen community resilience through youth-led initiatives.' :
                ($programType === 'livelihood' ? 'Manage livelihood and employment programs, track participants, and support youth skills development and entrepreneurship.' :
                ($programType === 'medicines' ? 'Manage health support programs, track requests, and ensure timely access to medicines and medical assistance for Kabataan members.' :
                ($programType === 'antidrug' ? 'Manage anti-drug and peace and order programs, track participation, and support youth-led community safety initiatives.' :
                ($programType === 'gender' ? 'Manage gender sensitivity programs, track participation, and promote inclusive and respectful communities for all youth.' :
                ($programType === 'feeding' ? 'Manage feeding programs, track beneficiaries, and support the health and well-being of Kabataan members.' :
                ($programType === 'sports' ? 'Manage sports development programs, track applications, and support youth participation in sports activities and events.' :
                ($programType === 'others' ? 'Manage other youth development programs, track activities, and coordinate community-wide events for Kabataan members.' :
                'Manage scholarship programs, track applications, and evaluate scholar performance to ensure quality education access for all youth.')))))))))
            }}
        </p>
    </div>
</section>

@include('schedule_programs::partials.program-tabs', [
    'activeTab' => $activeTab,
    'programType' => $programType
])

@if($pageTitle)
<section class="schol-page-header">
    <div class="schol-page-header-left">
        <h1 class="schol-page-title">{{ $pageTitle }}</h1>
        @if($pageSubtitle)
            <p class="schol-page-subtitle">{{ $pageSubtitle }}</p>
        @endif
    </div>
</section>
@endif
