<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Barangay Monitoring - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/barangay-monitoring/css/barangay-monitoring.css') }}">
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
            <input id="bm-search" type="text" placeholder="Search barangay..." aria-label="Search barangay">
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
            <a href="{{ route('barangay-monitoring') }}" class="menu-item active" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring">
                <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
            </a>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </aside>

    <main class="main-content bm-main">
        <div class="bm-container">
            <section class="bm-kpi-grid" aria-label="Monitoring summary">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Total Barangay</div>
                    <div class="bm-kpi-value">{{ $stats['total_barangays'] }}</div>
                    <div class="bm-kpi-note">Configured barangays in this prototype</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Total Programs</div>
                    <div class="bm-kpi-value">{{ $stats['active_programs'] }}</div>
                    <div class="bm-kpi-note">Cross-barangay total</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Average Participation Rate</div>
                    <div class="bm-kpi-value">{{ $stats['reporting_rate'] }}%</div>
                    <div class="bm-kpi-note">Based on total barangays in municipality</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Compliance Rate</div>
                    <div class="bm-kpi-value">{{ $stats['compliant'] }}%</div>
                    <div class="bm-kpi-note">Compliant barangays</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Non-Compliance Rate</div>
                    <div class="bm-kpi-value">{{ $stats['non_compliant'] }}%</div>
                    <div class="bm-kpi-note">Non-compliant barangays</div>
                </article>
            </section>

            <section class="bm-card" aria-label="All barangays list">
                <div class="bm-card-head">
                    <h3>All Barangays</h3>
                    <div class="bm-chip-row">
                        <button class="bm-chip active" data-status="all" type="button" onclick="bmSetStatus('all', this)">All</button>
                        <button class="bm-chip" data-status="compliant" type="button" onclick="bmSetStatus('compliant', this)">Compliant</button>
                        <button class="bm-chip" data-status="partial" type="button" onclick="bmSetStatus('partial', this)">Partial</button>
                        <button class="bm-chip" data-status="non-compliant" type="button" onclick="bmSetStatus('non-compliant', this)">Non-compliant</button>
                    </div>
                </div>
                <div class="bm-card-body">
                    <div class="bm-list-grid" id="bm-list-grid">
                        @foreach ($barangays as $barangay)
                            <a
                                href="{{ route('barangay-monitoring.show', ['barangay' => $barangay['slug']]) }}"
                                class="bm-list-item"
                                data-status="{{ $barangay['status'] }}"
                                data-name="{{ strtolower($barangay['name']) }}"
                            >
                                <div class="bm-list-head">
                                    <h4>{{ $barangay['name'] }}</h4>
                                    <span class="bm-status {{ $barangay['status'] }}">{{ ucfirst(str_replace('-', ' ', $barangay['status'])) }}</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-user-tie"></i> <strong>SK Chairman:</strong> {{ $barangay['sk_chairman'] }}</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-chart-pie"></i> <strong>Annual Program:</strong> {{ $barangay['active_programs'] }} active</span>
                                    <span><i class="fas fa-users"></i> <strong>Participation Rate:</strong> {{ $barangay['participation_rate'] }}%</span>
                                </div>
                                <div class="bm-list-meta">
                                    <span><i class="fas fa-file-check"></i> <strong>Report Rate:</strong> {{ $barangay['report_rate'] }}%</span>
                                </div>
                                <div class="bm-list-foot">
                                    <span>Last Update: {{ $barangay['last_update'] }}</span>
                                    <span class="bm-link-cta">View full details <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <p class="bm-empty" id="bm-empty" hidden>No barangays match the current search/filter.</p>
                </div>
            </section>
        </div>
    </main>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/modules/barangay-monitoring/js/barangay-monitoring.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";

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
