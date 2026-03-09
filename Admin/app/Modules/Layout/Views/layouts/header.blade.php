<!-- Header -->
@auth
@php
    $displayName = auth()->user()?->name ?? 'Henry Klein';
    $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if ($initials === '') {
        $initials = 'HK';
    }
@endphp
<header class="top-nav" id="topNav">
    <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <line x1="4" y1="7" x2="20" y2="7" />
                <line x1="4" y1="12" x2="20" y2="12" />
                <line x1="4" y1="17" x2="20" y2="17" />
            </svg>
        </button>
    </div>

    <div class="topbar-search-wrap">
        <label for="topbarSearch" class="sr-only">Search</label>
        <input id="topbarSearch" class="topbar-search-input" type="text" placeholder="Search">
    </div>

    <div class="topbar-right">
        <button type="button" class="topbar-icon-btn" aria-label="Open messages">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 6h16v12H4z"></path>
                <path d="m4 7 8 6 8-6"></path>
            </svg>
        </button>

        <button type="button" class="topbar-icon-btn notification-btn" aria-label="Open notifications">
            <span class="badge-dot" aria-hidden="true"></span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 4a4 4 0 0 0-4 4v2.5c0 .8-.3 1.6-.9 2.2L6 14h12l-1.1-1.3a3.2 3.2 0 0 1-.9-2.2V8a4 4 0 0 0-4-4z"></path>
                <path d="M10 17a2 2 0 0 0 4 0"></path>
            </svg>
        </button>

        <div class="topbar-user" id="topbarUser">
            <button type="button" class="topbar-user-btn" id="topbarUserToggle" aria-haspopup="true" aria-expanded="false" aria-controls="topbarUserMenu">
                <span class="topbar-user-avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="topbar-user-name">{{ $displayName }}</span>
                <svg class="topbar-user-caret" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="7,10 12,15 17,10"></polyline>
                </svg>
            </button>

            <div class="topbar-user-menu" id="topbarUserMenu" hidden>
                <a href="{{ route('profile') }}" class="topbar-user-menu-link">Profile</a>
            </div>
        </div>
    </div>
</header>
@endauth
