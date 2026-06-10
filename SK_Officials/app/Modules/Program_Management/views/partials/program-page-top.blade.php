@php
    $activeTab = $activeTab ?? 'schedule';
    $programType = $programType ?? 'general';
    $pageTitle = $pageTitle ?? 'Program Schedule';
    $pageSubtitle = $pageSubtitle ?? 'Create and schedule program activities for Kabataan members.';
    
    // Map program types to their full names
    $programTitles = [
        'environmental' => 'Environmental Protection',
        'disaster' => 'Disaster Risk Reduction and Resiliency',
        'livelihood' => 'Youth Employment and Livelihood',
        'medicines' => 'Medicines',
        'antidrug' => 'Anti-Drug and Peace and Order',
        'gender' => 'Gender Sensitivity',
        'feeding' => 'Feeding Program for KK Members',
        'sports' => 'Sports Development',
        'others' => 'Others',
        'scholarship' => 'Equitable Access to Quality Education',
    ];
    
    $programDescriptions = [
        'environmental' => 'Manage environmental protection programs, track initiatives, and evaluate impact to ensure sustainable practices for all youth.',
        'disaster' => 'Manage disaster risk reduction programs, track preparedness activities, and evaluate community resilience initiatives.',
        'livelihood' => 'Manage youth employment and livelihood programs, track opportunities, and evaluate skills development initiatives.',
        'medicines' => 'Manage medicine distribution programs, track health initiatives, and evaluate community wellness activities.',
        'antidrug' => 'Manage anti-drug and peace programs, track awareness campaigns, and evaluate community safety initiatives.',
        'gender' => 'Manage gender sensitivity programs, track equality initiatives, and evaluate awareness activities.',
        'feeding' => 'Manage feeding programs for KK members, track nutrition initiatives, and evaluate community health impact.',
        'sports' => 'Manage sports development programs, track athletic activities, and evaluate youth participation.',
        'others' => 'Manage other community programs, track various initiatives, and evaluate youth engagement.',
        'scholarship' => 'Manage scholarship programs, track applications, and evaluate scholar performance to ensure quality education access for all youth.',
    ];
    
    $programTitle = $programTitle ?? ($programTitles[$programType] ?? 'Program Management');
    $programDescription = $programDescription ?? ($programDescriptions[$programType] ?? 'Manage programs, track applications, and evaluate participants.');
@endphp

<a href="/schedule-programs" class="schol-back-top">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Schedule Programs
</a>

<!-- ── Program Header ── -->
<section class="program-header-section">
    <div class="program-header-content">
        <h1 class="program-header-title">{{ $programTitle }}</h1>
        <p class="program-header-description">{{ $programDescription }}</p>
    </div>
</section>

@include('Program_Management::partials.program-tabs', ['activeTab' => $activeTab, 'programType' => $programType])

<section class="saf-page-header-row">
    <div class="saf-page-header-text">
        <h1 class="schol-page-title">{{ $pageTitle }}</h1>
        <p class="schol-page-subtitle">{{ $pageSubtitle }}</p>
    </div>
    <div class="saf-page-header-actions">
        @if(in_array($activeTab, ['schedule', 'form'], true))
        <button type="button" class="schol-btn schol-btn-save" id="safOpenFormBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Program
        </button>
        @endif
    </div>
</section>
