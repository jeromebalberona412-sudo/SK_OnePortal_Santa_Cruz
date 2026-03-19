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
            <span class="sidebar-avatar" aria-hidden="true">{{ $initials }}</span>
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

            <div class="menu-section-label">Modules</div>
            <a href="{{ route('accounts.manage') }}" class="menu-item nav-link manage-account-btn" data-nav-key="manage-account" data-tooltip="Accounts" aria-label="Accounts">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="8" r="3"></circle>
                    <path d="M5 19c1.2-3 3.7-5 7-5s5.8 2 7 5"></path>
                </svg>
                <span>Accounts</span>
            </a>
            <a href="{{ route('auditlogs.index') }}" class="menu-item nav-link auditlogs-btn" data-nav-key="audit-logs" data-tooltip="Audit Log" aria-label="Audit Log">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,3 14,8 19,8"></polyline>
                    <line x1="9" y1="13" x2="15" y2="13"></line>
                    <line x1="9" y1="17" x2="15" y2="17"></line>
                </svg>
                <span>Audit Log</span>
            </a>

            <div class="menu-divider"></div>
            <button type="button" class="menu-item nav-link logout-item" data-tooltip="Logout" aria-label="Logout" onclick="showLogoutModal()">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M9 6V4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9v-2"></path>
                    <polyline points="12,8 8,12 12,16"></polyline>
                    <line x1="8" y1="12" x2="20" y2="12"></line>
                </svg>
                <span>Logout</span>
            </button>
        </nav>
    </nav>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
@include('layout::layouts.logout-modal')
@endauth
