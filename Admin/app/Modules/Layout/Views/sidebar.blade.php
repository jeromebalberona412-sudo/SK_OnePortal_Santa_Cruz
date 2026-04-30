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

            {{-- Manage Accounts Dropdown --}}
            @php
                $isManageAccountActive = request()->routeIs('accounts.*');
                $isFederationActive    = request()->routeIs('accounts.federation.index') || (request()->routeIs('accounts.manage') && (request('account_type', 'sk_federation') === 'sk_federation'));
                $isOfficialsActive     = request()->routeIs('accounts.officials.index');
            @endphp
            <button type="button"
                class="menu-item manage-account-btn {{ $isManageAccountActive ? 'active' : '' }}"
                data-nav-key="manage-account"
                data-tooltip="Manage Accounts"
                aria-label="Manage Accounts"
                aria-expanded="{{ $isManageAccountActive ? 'true' : 'false' }}"
                onclick="toggleAccountDropdown(this)">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Manage Accounts</span>
                <svg class="dropdown-chevron {{ $isManageAccountActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="dropdown-submenu {{ $isManageAccountActive ? 'open' : '' }}" id="accountDropdown">
                <a href="{{ route('accounts.federation.index') }}"
                   class="menu-item submenu-item {{ $isFederationActive ? 'active' : '' }}"
                   data-nav-key="accounts-federation"
                   data-tooltip="SK Federation"
                   aria-label="SK Federation">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4l3 3"></path>
                    </svg>
                    <span>SK Federation</span>
                </a>
                <a href="{{ route('accounts.officials.index') }}"
                   class="menu-item submenu-item {{ $isOfficialsActive ? 'active' : '' }}"
                   data-nav-key="accounts-officials"
                   data-tooltip="SK Officials"
                   aria-label="SK Officials">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span>SK Officials</span>
                </a>
            </div>

            <a href="{{ route('barangay-logos.index') }}" class="menu-item nav-link barangay-logos-btn" data-nav-key="barangay-logos" data-tooltip="SK Barangay Logos" aria-label="SK Barangay Logos">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span>SK Barangay Logos</span>
            </a>

            <a href="{{ route('contact.manage') }}" class="menu-item nav-link contact-btn {{ request()->routeIs('contact.manage') ? 'active' : '' }}" data-nav-key="manage-contacts" data-tooltip="Manage Contacts" aria-label="Manage Contacts">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <span>Manage Contacts</span>
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

            {{-- Archived Dropdown --}}
            @php
                $isArchivedActive = request()->routeIs('archived.*');
            @endphp
            <div class="menu-section-label">Archived</div>
            <button type="button"
                class="menu-item archived-dropdown-btn {{ $isArchivedActive ? 'active' : '' }}"
                data-nav-key="archived"
                data-tooltip="Archived"
                aria-label="Archived"
                aria-expanded="{{ $isArchivedActive ? 'true' : 'false' }}"
                onclick="toggleArchivedDropdown(this)">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="21 8 21 21 3 21 3 8"></polyline>
                    <rect x="1" y="3" width="22" height="5"></rect>
                    <line x1="10" y1="12" x2="14" y2="12"></line>
                </svg>
                <span>Archived</span>
                <svg class="dropdown-chevron {{ $isArchivedActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="dropdown-submenu {{ $isArchivedActive ? 'open' : '' }}" id="archivedDropdown">
                <a href="{{ route('archived.deleted-sk-federation') }}"
                   class="menu-item submenu-item {{ request()->routeIs('archived.deleted-sk-federation') ? 'active' : '' }}"
                   data-nav-key="archived-deleted-sk-federation"
                   data-tooltip="Deleted SK Federation"
                   aria-label="Deleted SK Federation">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4l3 3"></path>
                    </svg>
                    <span>Deleted SK Federation</span>
                </a>
                <a href="{{ route('archived.deleted-sk-officials') }}"
                   class="menu-item submenu-item {{ request()->routeIs('archived.deleted-sk-officials') ? 'active' : '' }}"
                   data-nav-key="archived-deleted-sk-officials"
                   data-tooltip="Deleted SK Officials"
                   aria-label="Deleted SK Officials">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span>Deleted SK Officials</span>
                </a>
                <div class="submenu-section-label">Archived Data</div>
                <a href="{{ route('archived.sk-federation-records') }}"
                   class="menu-item submenu-item {{ request()->routeIs('archived.sk-federation-records') ? 'active' : '' }}"
                   data-nav-key="archived-sk-federation-records"
                   data-tooltip="SK Federation Records"
                   aria-label="SK Federation Records">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>SK Federation Records</span>
                </a>
                <a href="{{ route('archived.sk-officials-records') }}"
                   class="menu-item submenu-item {{ request()->routeIs('archived.sk-officials-records') ? 'active' : '' }}"
                   data-nav-key="archived-sk-officials-records"
                   data-tooltip="SK Officials Records"
                   aria-label="SK Officials Records">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    <span>SK Officials Records</span>
                </a>
                <div class="archived-dropdown-spacer"></div>
            </div>
        </nav>
    </nav>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
@include('layout::logout-modal')
@endauth
