<!-- Sidebar Navigation -->
@auth
@php
    $displayName = auth()->user()?->name ?? 'Admin Name';
    $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if ($initials === '') {
        $initials = 'AN';
    }
@endphp
<aside id="layoutSidenav_nav" aria-label="Sidebar navigation">
    <nav class="sb-sidenav sidebar" id="sidenavAccordion" aria-label="Primary navigation">
        <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()" aria-label="Close sidebar">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link" id="sidebar-profile-link">
            <span class="sidebar-avatar" aria-hidden="true">
                <img src="{{ asset('Images/image.png') }}" alt="Profile" class="sidebar-avatar-img">
            </span>
            <div class="sidebar-user-info">
                <div class="s-name">{{ $displayName }}</div>
                <div class="s-role">Admin Member</div>
            </div>
        </a>

        <nav class="sidebar-nav" aria-label="Sidebar menu">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item nav-link dashboard-btn" data-nav-key="dashboard" data-tooltip="Dashboard" aria-label="Dashboard">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z"></path>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('profile') }}" class="menu-item nav-link profile-nav-btn" data-nav-key="profile" data-tooltip="My Profile" aria-label="My Profile">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="7" r="4"></circle>
                    <path d="M5.5 21a8.38 8.38 0 0 1 13 0"></path>
                </svg>
                <span>My Profile</span>
            </a>

            <div class="menu-section-label">Modules</div>

            <a href="{{ route('manage-kabataan.index') }}" class="menu-item nav-link manage-kabataan-btn {{ request()->routeIs('manage-kabataan.*') ? 'active' : '' }}" data-nav-key="manage-kabataan" data-tooltip="Kabataan" aria-label="Kabataan">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Kabataan</span>
            </a>
        </nav>
    </nav>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
@include('layout::logout-modal')
@endauth
