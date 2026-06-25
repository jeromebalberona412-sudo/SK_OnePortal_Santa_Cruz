@php
    $activeTab = $activeTab ?? 'forms';
    $committee = $committee ?? 'environmental';
    $routePrefix = $committee . '.survey.';
@endphp

<nav class="scholarship-tab-bar" aria-label="Survey sections">
    <a href="{{ route($routePrefix . 'forms') }}"
       class="scholarship-tab {{ $activeTab === 'forms' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Survey Forms
    </a>
    <a href="{{ route($routePrefix . 'results') }}"
       class="scholarship-tab {{ $activeTab === 'results' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
            <path d="M16 3.13a4 4 0 010 7.75"/>
        </svg>
        Survey Response
    </a>
    <a href="{{ route($routePrefix . 'analytics') }}"
       class="scholarship-tab {{ $activeTab === 'analytics' ? 'active' : '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Survey Analytics
    </a>
</nav>
