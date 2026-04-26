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

    <div class="topbar-right navbar-right">
        <!-- Theme Toggle -->
        <button
            data-theme-toggle
            class="topbar-theme-btn"
            aria-label="Switch to dark mode"
            title="Switch to dark mode"
        >
            <span class="theme-icon-dark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
                </svg>
            </span>
            <span class="theme-icon-light" aria-hidden="true" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                </svg>
            </span>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true">
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M5.5 21a8.38 8.38 0 0 1 13 0"></path>
                    </svg>
                    <span>Profile</span>
                </a>
                <a href="{{ route('profile.change-password') }}" class="topbar-user-menu-link dd-item" id="nav-change-pw-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Change Password</span>
                </a>
                <div class="dd-divider"></div>
                <button type="button" class="topbar-user-menu-link dd-item danger" onclick="showLogoutModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15" aria-hidden="true">
                        <path d="M9 6V4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9v-2"></path>
                        <polyline points="12,8 8,12 12,16"></polyline>
                        <line x1="8" y1="12" x2="20" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</header>
@endauth
