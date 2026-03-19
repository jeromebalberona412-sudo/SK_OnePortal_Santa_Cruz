<!-- Header -->
@auth
@php
    $displayName = auth()->user()?->name ?? 'Henry Klein';
    $displayEmail = auth()->user()?->email ?? '';
    $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if ($initials === '') {
        $initials = 'HK';
    }
@endphp
<header class="top-nav navbar" id="topNav">
    <div class="navbar-left topbar-left">
        <button class="sidebar-toggle-btn menu-toggle" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <g class="toggle-icon-expand">
                    <line x1="4" y1="7" x2="20" y2="7" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="17" x2="20" y2="17" />
                </g>
                <g class="toggle-icon-collapse" transform="translate(0 1)">
                    <circle cx="12" cy="5" r="1.6" fill="currentColor" stroke="none"></circle>
                    <circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"></circle>
                    <circle cx="12" cy="19" r="1.6" fill="currentColor" stroke="none"></circle>
                </g>
            </svg>
        </button>

        <div class="navbar-brand">
            <img src="{{ asset('Images/image.png') }}" alt="OnePortal Logo" class="brand-logo">
            <span class="brand-name">ONEPORTAL</span>
        </div>
    </div>

    <div class="topbar-search-wrap navbar-search">
        <label for="topbarSearch" class="sr-only">Search</label>
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
        </svg>
        <input id="topbarSearch" class="topbar-search-input" type="text" placeholder="Search...">
    </div>

    <div class="topbar-right navbar-right">
        <button
            type="button"
            class="topbar-icon-btn notif-btn notification-btn"
            id="notifToggle"
            aria-label="Open notifications"
            aria-expanded="false"
            aria-controls="notifPopover"
            onclick="toggleNotifPopover(event)"
        >
            <span class="badge-dot notif-badge" aria-hidden="true"></span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 4a4 4 0 0 0-4 4v2.5c0 .8-.3 1.6-.9 2.2L6 14h12l-1.1-1.3a3.2 3.2 0 0 1-.9-2.2V8a4 4 0 0 0-4-4z"></path>
                <path d="M10 17a2 2 0 0 0 4 0"></path>
            </svg>
        </button>

        <div class="profile-dropdown-wrapper topbar-user" id="topbarUser">
            <button
                type="button"
                class="topbar-user-btn profile-btn"
                id="topbarUserToggle"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="topbarUserMenu"
                onclick="toggleProfileDropdown(event)"
            >
                <span class="topbar-user-avatar nav-avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="topbar-user-name nav-name">{{ $displayName }}</span>
                <svg class="topbar-user-caret nav-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="7,10 12,15 17,10"></polyline>
                </svg>
            </button>

            <div class="topbar-user-menu profile-dropdown" id="topbarUserMenu" hidden>
                <div class="profile-dropdown-header">
                    <div class="dd-name">{{ $displayName }}</div>
                    <div class="dd-email">{{ $displayEmail }}</div>
                </div>
                <a href="{{ route('profile') }}" class="topbar-user-menu-link dd-item" id="nav-profile-link">
                    <span>Profile</span>
                </a>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="topbar-user-menu-link dd-item" id="nav-change-pw-link">
                        <span>Change Password</span>
                    </a>
                @endif
                <div class="dd-divider"></div>
                <button type="button" class="topbar-user-menu-link dd-item danger" onclick="showLogoutModal()">
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</header>

<div class="notif-popover" id="notifPopover" hidden>
    <div class="notif-popover-header">
        <h4>Notifications</h4>
        <button type="button" class="notif-mark-all">Mark all as read</button>
    </div>
    <div class="notif-list">
        <div class="notif-empty">
            No notifications yet
        </div>
    </div>
</div>
@endauth
