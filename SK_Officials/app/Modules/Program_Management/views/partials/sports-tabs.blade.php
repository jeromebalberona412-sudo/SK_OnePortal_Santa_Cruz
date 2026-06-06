@php
    $activeTab = $activeTab ?? 'form';
@endphp

<nav class="sports-tab-bar" aria-label="Sports Development sections">
    <a href="{{ route('sports-application-form') }}"
       class="sports-tab {{ $activeTab === 'form' ? 'active' : '' }}"
       data-tab="form">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
        </svg>
        Sports Schedule
    </a>
    <a href="{{ route('sports-requests') }}"
       class="sports-tab {{ $activeTab === 'requests' ? 'active' : '' }}"
       data-tab="requests">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>
        </svg>
        Sports Applications
    </a>
    <a href="{{ route('sport.list') }}"
       class="sports-tab {{ $activeTab === 'list' ? 'active' : '' }}"
       data-tab="list">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        Approved Participants
    </a>
    <a href="{{ route('sports.evaluation') }}"
       class="sports-tab {{ $activeTab === 'evaluation' ? 'active' : '' }}"
       data-tab="evaluation">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
        Evaluation
    </a>
</nav>
