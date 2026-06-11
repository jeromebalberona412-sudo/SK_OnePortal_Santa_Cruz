<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
    <script>
        (function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() { window.history.pushState(null, "", window.location.href); };
        })();
    </script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string) ($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string) $user->role)) : 'SK Official';
    @endphp

    {{-- ── NAVBAR ── --}}
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

    {{-- Notification Popover --}}
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

    {{-- ── SIDEBAR ── --}}
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
            <a href="{{ route('dashboard') }}" class="menu-item active" data-tooltip="Dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('calendar') }}" class="menu-item" data-tooltip="Calendar">
                <i class="fas fa-calendar-alt"></i><span>Calendar</span>
            </a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed" id="sidebar-community-feed-link">
                <i class="fas fa-rss"></i><span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
            </a>
            <a href="{{ route('reports') }}" class="menu-item" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring">
                <i class="fas fa-users"></i><span>Kabataan Monitoring</span>
            </a>
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
        </nav>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main class="main-content">

        {{-- Page Header --}}
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, {{ $user->name ?? 'SK Official' }}</p>
        </div>

        {{-- ── STAT CARDS ── --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:16px;margin-bottom:24px;">
            <a href="{{ route('kabataan-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Kabataan Registered</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon indigo"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Programs This Year</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Active Programs</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Completed Programs</div>
                </div>
            </a>
            <a href="{{ route('archive') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon purple"><i class="fas fa-archive"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Archived Reports</div>
                </div>
            </a>
            <a href="{{ route('archive') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon red"><i class="fas fa-trash-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Deleted Reports</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon orange"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Barangays Reporting</div>
                </div>
            </a>
        </div>
        
        <style>
            @media (max-width: 1400px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(4, 1fr) !important;
                }
            }
            @media (max-width: 992px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
            }
            @media (max-width: 768px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            @media (max-width: 480px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
        
        <script>
            // Add loading screen to stat card clicks
            document.querySelectorAll('.stat-card-clickable').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    const label = this.querySelector('.stat-label').textContent.trim();
                    LoadingScreen.show('Loading ' + label, 'Please wait...');
                    setTimeout(() => { window.location.href = this.href; }, 300);
                });
            });
        </script>

        {{-- ── QUICK ACTIONS ── --}}
        <div class="content-card" style="margin-bottom:24px;">
            <div class="card-header">
                <h3><i class="fas fa-bolt" style="color:#213F99;margin-right:8px;"></i>Quick Actions</h3>
            </div>
            <div class="card-body" style="padding:20px;overflow-x:auto;">
                <div style="display:flex;gap:12px;flex-wrap:wrap;min-width:fit-content;">
                    <a href="{{ route('profile') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#8b5cf6;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(139,92,246,0.3);">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="{{ route('barangay-monitoring') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(37,99,235,0.3);">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Barangay Monitoring</span>
                    </a>
                    <a href="{{ route('kabataan-monitoring') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#0ea5e9;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(14,165,233,0.3);">
                        <i class="fas fa-users"></i>
                        <span>Kabataan Monitoring</span>
                    </a>
                    <a href="{{ route('reports') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#06b6d4;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(6,182,212,0.3);">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('community-feed') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#10b981;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(16,185,129,0.3);">
                        <i class="fas fa-rss"></i>
                        <span>SK Community Feed</span>
                    </a>
                    <a href="{{ route('archive') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#f59e0b;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(245,158,11,0.3);">
                        <i class="fas fa-archive"></i>
                        <span>Archive</span>
                    </a>
                </div>
            </div>
        </div>
        
        <style>
            .qa-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                opacity: 0.9;
            }
            
            @media (max-width: 768px) {
                .card-body > div[style*="display:flex"] {
                    flex-wrap: nowrap !important;
                    overflow-x: auto !important;
                    padding-bottom: 8px;
                }
                .qa-btn {
                    flex-shrink: 0;
                }
            }
        </style>

        {{-- ── ROW: Charts ── --}}
        <div class="dash-row chart-row">
            <div class="content-card dash-col-5">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie" style="color:#213F99;margin-right:8px;"></i>Programs by Sector</h3>
                </div>
                <div class="card-body chart-body">
                    <canvas id="sectorChart" height="240"></canvas>
                </div>
            </div>
            <div class="content-card dash-col-7">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar" style="color:#213F99;margin-right:8px;"></i>Programs by Barangay</h3>
                </div>
                <div class="card-body chart-body" style="padding-bottom:0;">
                    <canvas id="barangayChart"></canvas>
                    <div class="pagination-bar" id="barangay-chart-pagination"></div>
                </div>
            </div>
        </div>

        {{-- ── ROW: Compliance + Kabataan Stats ── --}}
        <div class="dash-row">
            {{-- Barangay Compliance Status --}}
            <div class="content-card dash-col-6" id="barangay-monitoring">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-check" style="color:#213F99;margin-right:8px;"></i>Barangay Compliance Status</h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div class="compliance-scroll-wrap">
                        <div class="compliance-table-head">
                            <span class="ct-barangay">Barangay</span>
                            <span class="ct-programs">Programs Created</span>
                            <span class="ct-reports">Reports Submitted</span>
                            <span class="ct-status">Status</span>
                        </div>
                        <div id="compliance-list"></div>
                    </div>
                    <div class="pagination-bar" id="compliance-pagination"></div>
                </div>
            </div>

            {{-- Kabataan Participation Stats --}}
            <div class="content-card dash-col-6" id="kabataan-stats">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line" style="color:#213F99;margin-right:8px;"></i>Kabataan Participation Stats</h3>
                </div>
                <div class="card-body">
                    <div class="kab-stats-summary">
                        <div class="kab-stat-mini blue">
                            <div class="kab-stat-mini-value">0</div>
                            <div class="kab-stat-mini-label">Total Kabataan Registered</div>
                        </div>
                        <div class="kab-stat-mini green">
                            <div class="kab-stat-mini-value">0</div>
                            <div class="kab-stat-mini-label">Active Participants</div>
                        </div>
                    </div>
                    {{-- Month navigator --}}
                    <div class="month-nav">
                        <button class="month-nav-btn" id="month-prev" onclick="changeMonth(-1)" aria-label="Previous month">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="month-nav-label" id="month-label"></span>
                        <button class="month-nav-btn" id="month-next" onclick="changeMonth(1)" aria-label="Next month">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <canvas id="participationChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </main>

    @include('dashboard::logout-modal')

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        // ── Charts ──
        const fedBlue = '#213F99', fedRed = '#d0242b', fedYellow = '#F7D31E';
        const palette = ['#213F99','#d0242b','#F7D31E','#10b981','#8b5cf6','#f97316','#06b6d4','#ec4899'];

        new Chart(document.getElementById('sectorChart'), {
            type: 'doughnut',
            data: {
                labels: ['Education','Anti-Drugs','Agriculture','Disaster Preparedness','Sports Development','Gender & Development','Health','Others'],
                datasets: [{ data: [0,0,0,0,0,0,0,0], backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right', labels: { font: { family: 'Inter', size: 14 }, padding: 18 } } },
                cutout: '60%',
            }
        });

        // ── Programs by Barangay — paginated chart ──
        const brgyChartData = [];

        const BRGY_PER_PAGE = 6;
        let brgyPage = 1;
        const brgyTotalPages = Math.ceil(brgyChartData.length / BRGY_PER_PAGE);

        const brgyChart = new Chart(document.getElementById('barangayChart'), {
            type: 'bar',
            data: { labels: [], datasets: [
                { label: 'Active',    data: [], backgroundColor: fedBlue,    borderRadius: 4 },
                { label: 'Completed', data: [], backgroundColor: '#10b981', borderRadius: 4 },
            ]},
            options: {
                indexAxis: 'y', responsive: true,
                plugins: { legend: { position: 'top', labels: { font: { family: 'Inter', size: 13 } } } },
                scales: {
                    x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter', size: 13 } } },
                    y: { stacked: true, grid: { display: false }, ticks: { font: { family: 'Inter', size: 13 } } }
                }
            }
        });

        function renderBrgyChart() {
            const start = (brgyPage - 1) * BRGY_PER_PAGE;
            const slice = brgyChartData.slice(start, start + BRGY_PER_PAGE);
            brgyChart.data.labels = slice.map(b => b.name);
            brgyChart.data.datasets[0].data = slice.map(b => b.active);
            brgyChart.data.datasets[1].data = slice.map(b => b.completed);
            brgyChart.options.scales.y.ticks.font.size = 13;
            brgyChart.options.scales.x.ticks.font.size = 13;
            brgyChart.update();

            // pagination bar
            const pEl = document.getElementById('barangay-chart-pagination');
            let html = `<div class="pg-info">Page ${brgyPage} of ${brgyTotalPages} &nbsp;·&nbsp; ${brgyChartData.length} barangays</div>`;
            html += `<div class="pg-btns">`;
            html += `<button class="pg-btn" ${brgyPage===1?'disabled':''} onclick="brgyPageGo(${brgyPage-1})"><i class="fas fa-chevron-left"></i></button>`;
            for (let i = 1; i <= brgyTotalPages; i++) {
                html += `<button class="pg-btn ${i===brgyPage?'active':''}" onclick="brgyPageGo(${i})">${i}</button>`;
            }
            html += `<button class="pg-btn" ${brgyPage===brgyTotalPages?'disabled':''} onclick="brgyPageGo(${brgyPage+1})"><i class="fas fa-chevron-right"></i></button>`;
            html += `</div>`;
            pEl.innerHTML = html;
        }

        function brgyPageGo(p) { brgyPage = p; renderBrgyChart(); }
        renderBrgyChart();

        // ── Kabataan Monthly Participation Chart ──
        const monthlyData = {
            2025: {
                Jan:[0,0], Feb:[0,0], Mar:[0,0], Apr:[0,0], May:[0,0],
                Jun:[0,0], Jul:[0,0], Aug:[0,0], Sep:[0,0], Oct:[0,0], Nov:[0,0], Dec:[0,0]
            },
            2026: {
                Jan:[0,0], Feb:[0,0], Mar:[0,0], Apr:[0,0], May:[0,0],
                Jun:[0,0], Jul:[0,0], Aug:[0,0], Sep:[0,0], Oct:[0,0], Nov:[0,0], Dec:[0,0]
            }
        };
        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const today = new Date();
        let currentYear = today.getFullYear();
        let currentMonth = today.getMonth(); // 0-indexed

        const partCtx = document.getElementById('participationChart');
        const partChart = new Chart(partCtx, {
            type: 'bar',
            data: { labels: ['Male','Female'], datasets: [{ data: [0,0], backgroundColor: [fedBlue, fedRed], borderRadius: 6 }] },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 13 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter' } }, beginAtZero: true }
                }
            }
        });

        function updateMonthChart() {
            const yearData = monthlyData[currentYear] || monthlyData[2026];
            const key = monthNames[currentMonth];
            const vals = yearData[key] || [0, 0];
            partChart.data.datasets[0].data = vals;
            partChart.update();

            const label = document.getElementById('month-label');
            label.textContent = monthNames[currentMonth] + ' ' + currentYear;

            // Disable prev if before Jan 2025, disable next if beyond current real month
            const minYear = 2025, minMonth = 0;
            const maxYear = today.getFullYear(), maxMonth = today.getMonth();
            document.getElementById('month-prev').disabled =
                (currentYear === minYear && currentMonth === minMonth);
            document.getElementById('month-next').disabled =
                (currentYear === maxYear && currentMonth === maxMonth);
        }

        function changeMonth(dir) {
            currentMonth += dir;
            if (currentMonth < 0)  { currentMonth = 11; currentYear--; }
            if (currentMonth > 11) { currentMonth = 0;  currentYear++; }
            updateMonthChart();
        }

        updateMonthChart();
    </script>

    <script>
        // ── PAGINATION DATA ──
        const barangays = [];

        // ── Generic paginator ──
        function makePaginator(data, perPage, renderFn, listId, paginationId) {
            let page = 1;
            const totalPages = () => Math.ceil(data.length / perPage);

            function render() {
                const start = (page - 1) * perPage;
                const slice = data.slice(start, start + perPage);
                document.getElementById(listId).innerHTML = renderFn(slice);

                const tp = totalPages();
                const pEl = document.getElementById(paginationId);
                if (tp <= 1) { pEl.innerHTML = ''; return; }

                let html = `<div class="pg-info">Page ${page} of ${tp} &nbsp;·&nbsp; ${data.length} total</div>`;
                html += `<div class="pg-btns">`;
                html += `<button class="pg-btn" ${page===1?'disabled':''} onclick="paginators['${listId}'].go(${page-1})"><i class="fas fa-chevron-left"></i></button>`;
                for (let i = 1; i <= tp; i++) {
                    if (tp > 7 && i > 2 && i < tp - 1 && Math.abs(i - page) > 1) {
                        if (i === 3 || i === tp - 2) html += `<span class="pg-ellipsis">…</span>`;
                        continue;
                    }
                    html += `<button class="pg-btn ${i===page?'active':''}" onclick="paginators['${listId}'].go(${i})">${i}</button>`;
                }
                html += `<button class="pg-btn" ${page===tp?'disabled':''} onclick="paginators['${listId}'].go(${page+1})"><i class="fas fa-chevron-right"></i></button>`;
                html += `</div>`;
                pEl.innerHTML = html;
            }

            return { go: (p) => { page = p; render(); }, init: render };
        }

        window.paginators = {};

        // Compliance renderer
        function renderCompliance(items) {
            return items.map(b => {
                const label = b.status === 'compliant' ? 'Compliant' : b.status === 'partial' ? 'Partial' : 'Non-Compliant';
                return `<div class="compliance-item">
                    <span class="ct-barangay compliance-name"><i class="fas fa-map-marker-alt" style="color:#94a3b8;margin-right:5px;font-size:11px;"></i>Brgy. ${b.name}</span>
                    <span class="ct-programs compliance-count">${b.programs}</span>
                    <span class="ct-reports compliance-count">${b.reports}</span>
                    <span class="ct-status"><span class="compliance-badge ${b.status}">${label}</span></span>
                </div>`;
            }).join('');
        }

        paginators['compliance-list'] = makePaginator(barangays, 10, renderCompliance, 'compliance-list', 'compliance-pagination');
        paginators['compliance-list'].init();
    </script>

    <script>
        // ── QUICK ACTION MODAL ──
        const qaForms = {
            'new-program': {
                title: 'New Program',
                body: `
                    <div class="qa-form">
                        <div class="qa-field"><label>Program Title</label><input type="text" placeholder="e.g. Livelihood Training 2026"></div>
                        <div class="qa-field"><label>Sector</label>
                            <select><option>Education</option><option>Anti-Drugs</option><option>Agriculture</option>
                            <option>Disaster Preparedness</option><option>Sports Development</option>
                            <option>Gender & Development</option><option>Health</option><option>Others</option></select>
                        </div>
                        <div class="qa-row">
                            <div class="qa-field"><label>Start Date</label><input type="date"></div>
                            <div class="qa-field"><label>End Date</label><input type="date"></div>
                        </div>
                        <div class="qa-field"><label>Target Barangay</label><input type="text" placeholder="e.g. Brgy. Poblacion"></div>
                        <div class="qa-field"><label>Expected Participants</label><input type="number" placeholder="e.g. 100"></div>
                        <div class="qa-field"><label>Description</label><textarea rows="3" placeholder="Brief description of the program..."></textarea></div>
                    </div>`
            },
            'submit-report': {
                title: 'Submit Report',
                body: `
                    <div class="qa-form">
                        <div class="qa-field"><label>Report Type</label>
                            <select><option>Monthly Activity Report</option><option>Program Completion Report</option>
                            <option>Barangay Compliance Report</option><option>Financial Report</option></select>
                        </div>
                        <div class="qa-field"><label>Reporting Period</label>
                            <div class="qa-row">
                                <div class="qa-field"><label>From</label><input type="date"></div>
                                <div class="qa-field"><label>To</label><input type="date"></div>
                            </div>
                        </div>
                        <div class="qa-field"><label>Barangay</label><input type="text" placeholder="e.g. Brgy. Poblacion"></div>
                        <div class="qa-field"><label>Summary</label><textarea rows="3" placeholder="Brief summary of the report..."></textarea></div>
                        <div class="qa-field"><label>Attach File</label><input type="file" accept=".pdf,.doc,.docx,.xlsx"></div>
                    </div>`
            },
            'post-announcement': {
                title: 'Post Announcement',
                body: `
                    <div class="qa-form">
                        <div class="qa-field"><label>Title</label><input type="text" placeholder="e.g. SK General Assembly Notice"></div>
                        <div class="qa-field"><label>Audience</label>
                            <select><option>All Barangays</option><option>Specific Barangay</option><option>SK Officials Only</option></select>
                        </div>
                        <div class="qa-field"><label>Message</label><textarea rows="4" placeholder="Write your announcement here..."></textarea></div>
                        <div class="qa-field"><label>Post Date</label><input type="date"></div>
                        <div class="qa-field"><label>Attach Image (optional)</label><input type="file" accept="image/*"></div>
                    </div>`
            },
            'schedule-event': {
                title: 'Schedule Event',
                body: `
                    <div class="qa-form">
                        <div class="qa-field"><label>Event Name</label><input type="text" placeholder="e.g. SK Sportsfest 2026"></div>
                        <div class="qa-field"><label>Event Type</label>
                            <select><option>Sports</option><option>Cultural</option><option>Seminar</option>
                            <option>Assembly</option><option>Community Service</option><option>Others</option></select>
                        </div>
                        <div class="qa-row">
                            <div class="qa-field"><label>Date</label><input type="date"></div>
                            <div class="qa-field"><label>Time</label><input type="time"></div>
                        </div>
                        <div class="qa-field"><label>Venue</label><input type="text" placeholder="e.g. Municipal Covered Court"></div>
                        <div class="qa-field"><label>Expected Attendees</label><input type="number" placeholder="e.g. 200"></div>
                        <div class="qa-field"><label>Notes</label><textarea rows="2" placeholder="Additional notes..."></textarea></div>
                    </div>`
            },
            'export-data': {
                title: 'Export Data',
                body: `
                    <div class="qa-form">
                        <div class="qa-field"><label>Data Type</label>
                            <select><option>Kabataan Registry</option><option>Program List</option>
                            <option>Barangay Compliance</option><option>Participation Stats</option><option>All Data</option></select>
                        </div>
                        <div class="qa-field"><label>Date Range</label>
                            <div class="qa-row">
                                <div class="qa-field"><label>From</label><input type="date"></div>
                                <div class="qa-field"><label>To</label><input type="date"></div>
                            </div>
                        </div>
                        <div class="qa-field"><label>Format</label>
                            <select><option>Excel (.xlsx)</option><option>CSV (.csv)</option><option>PDF (.pdf)</option></select>
                        </div>
                        <div class="qa-field"><label>Include Barangay</label>
                            <select><option>All Barangays</option><option>Specific Barangay</option></select>
                        </div>
                    </div>`
            }
        };

        function showQuickActionModal(type) {
            const form = qaForms[type];
            if (!form) return;
            document.getElementById('qa-modal-title').textContent = form.title;
            document.getElementById('qa-modal-body').innerHTML = form.body;
            document.getElementById('quickActionModal').classList.add('show');
        }

        function closeQuickActionModal() {
            document.getElementById('quickActionModal').classList.remove('show');
        }

        document.getElementById('quickActionModal').addEventListener('click', function(e) {
            if (e.target === this) closeQuickActionModal();
        });

        document.getElementById('qa-modal-submit').addEventListener('click', function() {
            alert('Submitted! (Connect to backend when ready.)');
            closeQuickActionModal();
        });
    </script>
    <script>
        (() => {
            const heartbeatMs = {{ (int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) }} * 1000;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let id = null;
            async function beat() {
                try {
                    await fetch("{{ route('skfed.heartbeat') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin', body: JSON.stringify({}),
                    });
                } catch (_) {}
            }
            beat();
            id = setInterval(beat, heartbeatMs);
            window.addEventListener('beforeunload', () => clearInterval(id));
        })();
    </script>
</body>
</html>
