<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $barangayData['name'] }} - Barangay Monitoring</title>
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
            <input type="text" placeholder="Search..." aria-label="Search" disabled>
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
            <a href="#" class="menu-item is-disabled" data-tooltip="Program Monitoring (Temporarily Disabled)" aria-disabled="true" tabindex="-1" onclick="return false;">
                <i class="fas fa-tasks"></i><span>Program Monitoring</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring">
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

    <main class="main-content bm-main">
        <div class="bm-container">
            <section class="bm-header bm-detail-header">
                <div class="bm-title">
                    <a class="bm-back-link" href="{{ route('barangay-monitoring') }}"><i class="fas fa-arrow-left"></i> Back to All Barangays</a>
                    <h1>{{ $barangayData['name'] }}</h1>
                    <p>{{ $barangayData['municipality'] }} · Last submission: {{ $barangayData['latest_submission'] }}</p>
                </div>
                <div class="bm-controls">
                    <span class="bm-status {{ $barangayData['status'] }}">{{ ucfirst(str_replace('-', ' ', $barangayData['status'])) }}</span>
                </div>
            </section>

            <section class="bm-kpi-grid bm-kpi-grid-4" aria-label="Barangay detail summary">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Compliance Score</div>
                    <div class="bm-kpi-value">{{ $barangayData['compliance_score'] }}%</div>
                    <div class="bm-kpi-note">Current system-based rating</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Participation Rate</div>
                    <div class="bm-kpi-value">{{ $barangayData['participation_rate'] }}%</div>
                    <div class="bm-kpi-note">Average youth turnout</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Verified Accounts</div>
                    <div class="bm-kpi-value">{{ $barangayData['verified_accounts'] }}</div>
                    <div class="bm-kpi-note">Trusted device verified users</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label">Active Programs</div>
                    <div class="bm-kpi-value">{{ $barangayData['active_programs'] }}</div>
                    <div class="bm-kpi-note">Programs running this quarter</div>
                </article>
            </section>

            <section class="bm-detail-grid">
                <article class="bm-card">
                    <div class="bm-card-head"><h3>Programs - Previous</h3></div>
                    <div class="bm-program-list">
                        @foreach ($barangayData['programs']['previous_programs'] as $program)
                            <div class="bm-program-item">
                                <h4>{{ $program['title'] }}</h4>
                                <p>{{ $program['sector'] }} · {{ $program['timeline'] }} · {{ $program['participants'] }} participants</p>
                                <span class="bm-inline-tag completed">{{ $program['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="bm-card">
                    <div class="bm-card-head"><h3>Programs - Current</h3></div>
                    <div class="bm-program-list">
                        @foreach ($barangayData['programs']['current_programs'] as $program)
                            <div class="bm-program-item">
                                <h4>{{ $program['title'] }}</h4>
                                <p>{{ $program['sector'] }} · {{ $program['timeline'] }} · {{ $program['participants'] }} participants</p>
                                <span class="bm-inline-tag ongoing">{{ $program['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="bm-card">
                    <div class="bm-card-head"><h3>Programs - Future</h3></div>
                    <div class="bm-program-list">
                        @foreach ($barangayData['programs']['future_programs'] as $program)
                            <div class="bm-program-item">
                                <h4>{{ $program['title'] }}</h4>
                                <p>{{ $program['sector'] }} · {{ $program['timeline'] }} · {{ $program['participants'] }} participants</p>
                                <span class="bm-inline-tag planned">{{ $program['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="bm-card">
                <div class="bm-card-head"><h3>SK Officials</h3></div>
                <div class="bm-table-wrap">
                    <table class="bm-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Term Start</th>
                                <th>Term End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barangayData['officials'] as $official)
                                <tr>
                                    <td>{{ $official['name'] }}</td>
                                    <td>{{ $official['role'] }}</td>
                                    <td>
                                        <span class="bm-inline-tag {{ strtolower(str_replace(' ', '-', $official['status'])) === 'active' ? 'ongoing' : 'planned' }}">
                                            {{ $official['status'] }}
                                        </span>
                                    </td>
                                    <td>{{ $official['term_start'] }}</td>
                                    <td>{{ $official['term_end'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="bm-system-grid">
                <article class="bm-card">
                    <div class="bm-card-head"><h3>System Data</h3></div>
                    <div class="bm-system-list">
                        <div class="bm-system-row"><span>Report Cycles Submitted</span><strong>{{ $barangayData['system_data']['report_cycles_submitted'] }}</strong></div>
                        <div class="bm-system-row"><span>Pending Reports</span><strong>{{ $barangayData['system_data']['pending_reports'] }}</strong></div>
                        <div class="bm-system-row"><span>Device Trust Rate</span><strong>{{ $barangayData['system_data']['device_trust_rate'] }}</strong></div>
                        <div class="bm-system-row"><span>Coverage</span><strong>{{ $barangayData['coverage'] }}</strong></div>
                        <div class="bm-system-row"><span>Total Kabataan</span><strong>{{ $barangayData['total_kabataan'] }}</strong></div>
                    </div>
                </article>
                <article class="bm-card">
                    <div class="bm-card-head"><h3>Audit Notes</h3></div>
                    <div class="bm-audit-box">
                        <p><strong>Latest Audit Result:</strong> {{ $barangayData['system_data']['last_audit_result'] }}</p>
                        <p><strong>Top Issue:</strong> {{ $barangayData['system_data']['top_issue'] }}</p>
                    </div>
                </article>
            </section>
        </div>
    </main>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
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
