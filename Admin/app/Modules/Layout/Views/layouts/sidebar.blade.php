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
    <nav class="sb-sidenav" id="sidenavAccordion" aria-label="Primary navigation">
        <div class="sb-sidenav-menu">
            <div class="sidebar-brand-wrap">
                <span class="sidebar-brand-full">
                    <img src="{{ asset('Images/image.png') }}" alt="OnePortal" class="sidebar-brand-logo">
                    <span class="sidebar-brand-text">ONEPORTAL</span>
                </span>
                <span class="sidebar-brand-mini" aria-hidden="true">
                    <img src="{{ asset('Images/image.png') }}" alt="" class="sidebar-brand-logo-mini">
                </span>
            </div>

            <button class="sidebar-close-btn" id="sidebarCloseBtn" onclick="toggleSidebar()" aria-label="Close sidebar">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div class="sidebar-user-card">
                <span class="sidebar-user-avatar" aria-hidden="true">{{ $initials }}</span>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $displayName }}</div>
                    <div class="sidebar-user-role">Admin Member</div>
                </div>
                <button type="button" class="sidebar-kebab-btn" aria-label="Open user options">...</button>
            </div>

            <div class="sidebar-section-label">Navigation</div>

            <ul class="sidebar-nav-list">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link dashboard-btn" data-nav-key="dashboard" data-tooltip="Dashboard" aria-label="Dashboard">
                        <span class="nav-icon-circle icon-dashboard" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M12 8v5l3 2"></path>
                            </svg>
                        </span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('accounts.manage') }}" class="nav-link manage-account-btn" data-nav-key="manage-account" data-tooltip="Accounts" aria-label="Accounts">
                        <span class="nav-icon-circle icon-manage" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="3"></circle>
                                <path d="M5 19c1.2-3 3.7-5 7-5s5.8 2 7 5"></path>
                            </svg>
                        </span>
                        <span class="nav-label">Accounts</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('auditlogs.index') }}" class="nav-link auditlogs-btn" data-nav-key="audit-logs" data-tooltip="Audit Log" aria-label="Audit Log">
                        <span class="nav-icon-circle icon-audit" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,3 14,8 19,8"></polyline>
                                <line x1="9" y1="13" x2="15" y2="13"></line>
                                <line x1="9" y1="17" x2="15" y2="17"></line>
                            </svg>
                        </span>
                        <span class="nav-label">Audit Log</span>
                    </a>
                </li>

                <li class="nav-item nav-item-logout">
                    <form method="POST" action="{{ route('logout') }}" class="logout-nav-form">
                        @csrf
                        <button type="submit" class="nav-link logout-btn" data-tooltip="Logout" aria-label="Logout">
                            <span class="nav-icon-circle icon-logout" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M9 6V4h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9v-2"></path>
                                    <polyline points="12,8 8,12 12,16"></polyline>
                                    <line x1="8" y1="12" x2="20" y2="12"></line>
                                </svg>
                            </span>
                            <span class="nav-label">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
@endauth
