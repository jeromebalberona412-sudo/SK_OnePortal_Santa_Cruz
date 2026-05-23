@php
    $activeTab = $activeTab ?? 'requests';
@endphp
<nav class="scholarship-tab-bar" aria-label="Scholarship sections">
    <a href="{{ route('scholarship.application-request') }}"
       class="scholarship-tab {{ $activeTab === 'requests' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>
        </svg>
        Scholarship Application Request
    </a>
    <a href="{{ route('scholar.list') }}"
       class="scholarship-tab {{ $activeTab === 'list' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        Scholarship List
    </a>
    <a href="{{ route('scholar.application-form') }}"
       class="scholarship-tab {{ $activeTab === 'form' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Published Scholarship Forms
    </a>
</nav>
