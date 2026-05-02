<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Kabataan Monitoring - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kabataan-monitoring.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
    <script>
        (function() {
            window.history.pushState(null, '', window.location.href);
            window.onpopstate = function() { window.history.pushState(null, '', window.location.href); };
        })();
    </script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string) $user->role)) : 'SK Official';
    @endphp

    <nav class="navbar">
        <div class="navbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <i class="fas fa-bars toggle-icon-expand"></i>
                <i class="fas fa-ellipsis-v toggle-icon-collapse"></i>
            </button>
            <div class="navbar-brand">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed Logo" class="brand-logo">
                <span class="brand-name">SK Federations</span>
            </div>
        </div>
        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input id="km-search" type="text" placeholder="Search name, barangay, or focus area..." aria-label="Search kabataan">
        </div>
        <div class="navbar-right">
            <button class="notif-btn" onclick="toggleNotifPopover(event)" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge"></span>
            </button>
            <div class="profile-dropdown-wrapper">
                <button class="profile-btn" onclick="toggleProfileDropdown(event)" aria-label="Profile menu">
                    <img src="{{ $avatar }}" alt="Profile" class="nav-avatar">
                    <span class="nav-name">{{ $user->name ?? 'User' }}</span>
                    <i class="fas fa-chevron-down nav-chevron"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="dd-name">{{ $user->name ?? 'User' }}</div>
                        <div class="dd-email">{{ $user->email ?? '' }}</div>
                    </div>
                    <a href="{{ route('profile') }}" class="dd-item" id="nav-profile-link"><i class="fas fa-user"></i> Profile</a>
                    <a href="{{ route('password.request') }}" class="dd-item" id="nav-change-pw-link"><i class="fas fa-lock"></i> Change Password</a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="notif-popover" id="notifPopover">
        <div class="notif-popover-header">
            <h4>Notifications</h4>
            <button class="notif-mark-all">Mark all as read</button>
        </div>
        <div class="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                No notifications yet
            </div>
        </div>
    </div>

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link" id="sidebar-profile-link">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>
        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard" id="nav-dashboard-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed"><i class="fas fa-rss"></i><span>SK Community Feed</span></a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring"><i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span></a>
            <a href="{{ route('reports') }}" class="menu-item" data-tooltip="Reports"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item active" data-tooltip="Kabataan Monitoring"><i class="fas fa-users"></i><span>Kabataan Monitoring</span></a>
            <a href="javascript:void(0);" class="menu-item" onclick="document.getElementById('archiveSubmenu').style.display = document.getElementById('archiveSubmenu').style.display === 'block' ? 'none' : 'block'; document.getElementById('archiveChevron').style.transform = document.getElementById('archiveSubmenu').style.display === 'block' ? 'rotate(180deg)' : 'rotate(0deg)'; return false;" data-tooltip="Archive">
                <i class="fas fa-archive"></i><span>Archive</span>
                <i class="fas fa-chevron-down" id="archiveChevron" style="margin-left:auto;font-size:12px;transition:transform 0.3s ease;"></i>
            </a>
            <div id="archiveSubmenu" style="display:none;padding-left:20px;border-left:2px solid #e2e8f0;margin-left:10px;">
                <a href="{{ route('archive') }}" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-trash"></i><span>Deleted Reports</span>
                </a>
                <a href="{{ route('archive') }}" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-box"></i><span>Archived Reports</span>
                </a>
            </div>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
        </nav>
    </aside>

    <main class="main-content km-main" data-detail-base="{{ url('/kabataan-monitoring') }}">
        <div class="km-container">

            <section class="km-hero">
                <img src="{{ url('/modules/kabataan-monitoring/images/sk-fed-logo.png') }}" alt="SK Federation logo" class="km-hero-logo">
                <div class="km-hero-copy">
                    <h1>Kabataan Monitoring</h1>
                    <p>KKK Profiling Masterlist — Track youth engagement, participation status, and support interventions across all barangays of Santa Cruz, Laguna.</p>
                </div>
            </section>

            {{-- Summary Cards --}}
            <section class="km-summary-grid" aria-label="Summary statistics">
                <article class="km-summary-card km-summary-total">
                    <div class="km-summary-icon"><i class="fas fa-users"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Total Kabataan</div>
                        <div class="km-summary-value" id="km-kpi-total">0</div>
                        <div class="km-summary-note">Registered youth profiles</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-active">
                    <div class="km-summary-icon"><i class="fas fa-user-check"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Active Youth</div>
                        <div class="km-summary-value" id="km-kpi-active">0</div>
                        <div class="km-summary-note">High &amp; moderate engagement</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-inactive">
                    <div class="km-summary-icon"><i class="fas fa-user-times"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Inactive Youth</div>
                        <div class="km-summary-value" id="km-kpi-inactive">0</div>
                        <div class="km-summary-note">Needs follow-up &amp; intervention</div>
                    </div>
                </article>
                <article class="km-summary-card km-summary-rate">
                    <div class="km-summary-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="km-summary-body">
                        <div class="km-summary-label">Participation Rate</div>
                        <div class="km-summary-value" id="km-kpi-rate">0%</div>
                        <div class="km-summary-note">Active vs total registered</div>
                    </div>
                </article>
            </section>

            {{-- Masterlist --}}
            <section class="km-masterlist-top">
                <div class="km-masterlist-topbar">
                    <div>
                        <h2><i class="fas fa-list-alt" style="color:#213F99;margin-right:8px;"></i>KKK Profiling Masterlist</h2>
                        <p>Youth profiling records grouped by barangay</p>
                    </div>
                    <div class="km-masterlist-actions">
                        <button class="km-export-btn" onclick="exportCSV()">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                </div>
                <div class="km-filter-row">
                    <div class="km-chip-row" id="km-status-filter">
                        <button type="button" class="km-chip active" data-status="all">All</button>
                        <button type="button" class="km-chip" data-status="active">Active</button>
                        <button type="button" class="km-chip" data-status="moderate">Moderate</button>
                        <button type="button" class="km-chip" data-status="inactive">Inactive</button>
                    </div>
                    <div class="km-result-count" id="km-result-count"></div>
                </div>
            </section>

            {{-- Per-barangay cards --}}
            <div id="km-brgy-cards"></div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

        </div>
    </main>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";
        window.kmPageMode  = 'index';

        document.getElementById('sidebar-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-change-pw-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
    </script>
</body>
</html>
