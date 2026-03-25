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
            <input id="km-search" type="text" placeholder="Search kabataan, barangay, or focus area..." aria-label="Search kabataan">
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
                    <a href="{{ route('profile') }}" class="dd-item" id="nav-profile-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('password.request') }}" class="dd-item" id="nav-change-pw-link">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
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
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard" id="nav-dashboard-link">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed">
                <i class="fas fa-rss"></i><span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
            </a>
            <a href="#" class="menu-item is-disabled" data-tooltip="Program Monitoring (Temporarily Disabled)" aria-disabled="true" tabindex="-1" onclick="return false;">
                <i class="fas fa-tasks"></i><span>Program Monitoring</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item active" data-tooltip="Kabataan Monitoring">
                <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
            </a>
            <a href="#" class="menu-item" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </aside>

    <main class="main-content km-main" data-detail-base="{{ url('/kabataan-monitoring') }}">
        <div class="km-container">
            <section class="km-hero">
                <div class="km-hero-overlay"></div>
                <img src="{{ url('/modules/kabataan-monitoring/images/sk-fed-logo.png') }}" alt="SK Federation logo" class="km-hero-logo">
                <div class="km-hero-copy">
                    <h1>Kabataan Monitoring</h1>
                    <p>Prototype dashboard for tracking youth engagement, participation status, and support interventions across Santa Cruz barangays.</p>
                </div>
            </section>

            <section class="km-kpi-grid" aria-label="Kabataan monitoring summary">
                <article class="km-kpi-card">
                    <div class="km-kpi-label">Total Kabataan Profiles</div>
                    <div class="km-kpi-value" id="km-kpi-total">0</div>
                    <div class="km-kpi-note">Prototype records currently monitored</div>
                </article>
                <article class="km-kpi-card">
                    <div class="km-kpi-label">High Participation</div>
                    <div class="km-kpi-value" id="km-kpi-high">0</div>
                    <div class="km-kpi-note">Youth with sustained attendance</div>
                </article>
                <article class="km-kpi-card">
                    <div class="km-kpi-label">Needs Follow-up</div>
                    <div class="km-kpi-value" id="km-kpi-followup">0</div>
                    <div class="km-kpi-note">Profiles tagged for intervention</div>
                </article>
                <article class="km-kpi-card">
                    <div class="km-kpi-label">Avg. Engagement Score</div>
                    <div class="km-kpi-value" id="km-kpi-score">0</div>
                    <div class="km-kpi-note">Based on participation and attendance</div>
                </article>
            </section>

            <section class="km-panel" aria-label="Kabataan by barangay">
                <div class="km-panel-head">
                    <h2>Barangay Kabataan Lists</h2>
                    <p>Each barangay contains its kabataan roster. Use search and participation status filters, then open details per person.</p>
                </div>
                <div class="km-filter-row">
                    <div class="km-chip-row" id="km-status-filter">
                        <button type="button" class="km-chip active" data-status="all">All</button>
                        <button type="button" class="km-chip" data-status="high">High</button>
                        <button type="button" class="km-chip" data-status="moderate">Moderate</button>
                        <button type="button" class="km-chip" data-status="low">Low</button>
                    </div>
                </div>
                <div id="km-card-grid" class="km-barangay-grid"></div>
                <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>
            </section>

            <section class="km-panel" aria-label="Monitoring notes">
                <div class="km-panel-head">
                    <h2>Monitoring Focus This Quarter</h2>
                </div>
                <div class="km-focus-grid">
                    <article class="km-focus-card">
                        <h3>School Continuity</h3>
                        <p>Track enrolled youth with declining attendance and route them to peer tutoring and mentoring support.</p>
                    </article>
                    <article class="km-focus-card">
                        <h3>Civic Participation</h3>
                        <p>Identify barangays with low youth volunteer turnout and coordinate youth council activities.</p>
                    </article>
                    <article class="km-focus-card">
                        <h3>Health and Wellness</h3>
                        <p>Surface at-risk profiles and connect them to wellness caravans and local health outreach programs.</p>
                    </article>
                </div>
            </section>
        </div>
    </main>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute = "{{ route('login') }}";
        window.kmPageMode = 'index';

        document.getElementById('sidebar-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        document.getElementById('nav-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading Profile', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        document.getElementById('nav-change-pw-link')?.addEventListener('click', function(e) {
            e.preventDefault();
            LoadingScreen.show('Loading', 'Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
    </script>
</body>
</html>
