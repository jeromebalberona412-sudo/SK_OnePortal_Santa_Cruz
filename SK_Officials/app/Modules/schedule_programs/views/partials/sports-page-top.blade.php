@php
    $activeTab = $activeTab ?? 'list';
    $pageTitle = $pageTitle ?? '';
    $pageSubtitle = $pageSubtitle ?? '';
@endphp
<a href="{{ route('schedule-programs') }}" class="schol-back-top">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Back to Schedule Programs
</a>

@include('schedule_programs::partials.sports-tabs', ['activeTab' => $activeTab])

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
