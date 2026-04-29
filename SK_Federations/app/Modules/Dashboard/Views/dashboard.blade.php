<!DOCTYPE html>
<html lang="en">
<head>
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
        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search..." aria-label="Search">
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
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed" id="sidebar-community-feed-link">
                <i class="fas fa-rss"></i><span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
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

    {{-- ── MAIN CONTENT ── --}}
    <main class="main-content">

        {{-- Page Header --}}
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, {{ $user->name ?? 'SK Official' }}</p>
        </div>

        {{-- ── STAT CARDS ── --}}
        <div class="stats-grid stats-grid-6">
            <a href="#" class="stat-card stat-card-link" data-section="kabataan-monitoring">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value">1,248</div>
                    <div class="stat-label">Total Kabataan Registered</div>
                </div>
            </a>
            <a href="#" class="stat-card stat-card-link" data-section="program-monitoring">
                <div class="stat-icon indigo"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">34</div>
                    <div class="stat-label">Total Programs This Year</div>
                </div>
            </a>
            <a href="#" class="stat-card stat-card-link" data-section="program-monitoring">
                <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">12</div>
                    <div class="stat-label">Active Programs</div>
                </div>
            </a>
            <a href="#" class="stat-card stat-card-link" data-section="program-monitoring">
                <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">22</div>
                    <div class="stat-label">Completed Programs</div>
                </div>
            </a>
            <a href="#" class="stat-card stat-card-link" data-section="kabataan-stats">
                <div class="stat-icon yellow"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value">3,540</div>
                    <div class="stat-label">Total Participants</div>
                </div>
            </a>
            <a href="#" class="stat-card stat-card-link" data-section="barangay-monitoring">
                <div class="stat-icon red"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">26</div>
                    <div class="stat-label">Barangays Reporting</div>
                </div>
            </a>
        </div>

        {{-- ── ROW: Upcoming Events + Calendar ── --}}
        <div class="dash-row" style="align-items:stretch;">
            {{-- Upcoming Programs --}}
            <div class="content-card dash-col-6" style="display:flex;flex-direction:column;">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-week" style="color:#213F99;margin-right:8px;"></i>Upcoming Events</h3>
                    <button onclick="clearAllCalEvents()" style="background:none;border:none;color:#94a3b8;font-size:12px;cursor:pointer;padding:4px 8px;border-radius:6px;transition:color 0.15s;" title="Clear all events" onmouseover="this.style.color='#d0242b'" onmouseout="this.style.color='#94a3b8'">
                        <i class="fas fa-trash-alt"></i> Clear all
                    </button>
                </div>
                <div class="card-body" style="padding:0;flex:1;overflow-y:auto;">
                    <div class="upcoming-list" id="upcoming-list"></div>
                    <div class="pagination-bar" id="upcoming-pagination"></div>
                </div>
            </div>

            {{-- Calendar --}}
            <div class="content-card dash-col-6" style="display:flex;flex-direction:column;">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt" style="color:#213F99;margin-right:8px;"></i>My Schedule</h3>
                    <button class="btn-add-event" onclick="openAddEventModal()">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <div class="card-body" style="padding:12px 16px;flex:1;">
                    <div class="cal-nav">
                        <button class="cal-nav-btn" id="cal-prev" onclick="calChangeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                        <span class="cal-month-label" id="cal-month-label"></span>
                        <button class="cal-nav-btn" id="cal-next" onclick="calChangeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="cal-grid" id="cal-grid"></div>
                </div>
            </div>
        </div>

        {{-- ── Recent Program Activities (full width) ── --}}
        <div class="content-card" style="margin-bottom:24px;">
            <div class="card-header">
                <h3><i class="fas fa-history" style="color:#213F99;margin-right:8px;"></i>Recent Program Activities</h3>
                <span class="card-badge">Live</span>
            </div>
            <div class="card-body" style="padding:0;">
                <div class="activity-list" id="activity-list"></div>
                <div class="pagination-bar" id="activity-pagination"></div>
            </div>
        </div>

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
                            <div class="kab-stat-mini-value">3,450</div>
                            <div class="kab-stat-mini-label">Total Kabataan Registered</div>
                        </div>
                        <div class="kab-stat-mini green">
                            <div class="kab-stat-mini-value">1,250</div>
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

    {{-- ── CALENDAR EVENT MODAL ── --}}
    <div class="modal" id="calEventModal">
        <div class="modal-content" style="max-width:480px;">
            <div class="modal-header">
                <h3 id="cal-modal-title">Add Event</h3>
                <button class="modal-close-btn" onclick="closeCalEventModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Event Title <span style="color:#d0242b;">*</span></label>
                    <input type="text" id="cal-event-title" class="form-input" placeholder="e.g. SK Meeting, Program Launch..." maxlength="100">
                    <div class="cal-field-error" id="err-title"></div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Date <span style="color:#d0242b;">*</span></label>
                    <input type="date" id="cal-event-date" class="form-input">
                    <div class="cal-field-error" id="err-date"></div>
                </div>
                <div style="display:flex;gap:12px;margin-bottom:16px;">
                    <div class="form-group" style="flex:1;">
                        <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Start Time</label>
                        <input type="time" id="cal-event-start" class="form-input" min="07:00" max="22:00">
                        <div class="cal-field-error" id="err-start"></div>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">End Time</label>
                        <input type="time" id="cal-event-end" class="form-input" min="07:00" max="22:00">
                        <div class="cal-field-error" id="err-end"></div>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Type</label>
                    <select id="cal-event-type" class="form-input">
                        <option value="event">📅 Event</option>
                        <option value="task">✅ Task</option>
                        <option value="meeting">🤝 Meeting</option>
                        <option value="deadline">⚠️ Deadline</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Location</label>
                    <input type="text" id="cal-event-location" class="form-input" placeholder="e.g. Barangay Hall, Municipal Hall..." maxlength="150">
                </div>
                <div class="form-group" id="cal-status-group" style="margin-bottom:16px;display:none;">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Status</label>
                    <select id="cal-event-status" class="form-input">
                        <option value="upcoming">🔵 Upcoming</option>
                        <option value="done">✅ Done</option>
                        <option value="cancelled">❌ Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size:13px;font-weight:600;color:#334155;display:block;margin-bottom:6px;">Notes</label>
                    <textarea id="cal-event-notes" class="form-input" rows="2" placeholder="Optional notes..." maxlength="300" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeCalEventModal()">Cancel</button>
                <button class="btn-primary" onclick="saveCalEvent()">Save Event</button>
            </div>
        </div>
    </div>

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
                datasets: [{ data: [6,5,4,4,7,3,5,6], backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right', labels: { font: { family: 'Inter', size: 14 }, padding: 18 } } },
                cutout: '60%',
            }
        });

        // ── Programs by Barangay — paginated chart ──
        const brgyChartData = [
            { name:'Alipit',                active:2, completed:1 },
            { name:'Bagumbayan',            active:1, completed:1 },
            { name:'Bubukal',               active:2, completed:1 },
            { name:'Calios',                active:1, completed:1 },
            { name:'Duhat',                 active:0, completed:1 },
            { name:'Gatid',                 active:1, completed:2 },
            { name:'Jasaan',                active:1, completed:1 },
            { name:'Labuin',                active:2, completed:1 },
            { name:'Malinao',               active:1, completed:1 },
            { name:'Oogong',                active:0, completed:1 },
            { name:'Pagsawitan',            active:1, completed:2 },
            { name:'Palasan',               active:1, completed:1 },
            { name:'Patimbao',              active:1, completed:1 },
            { name:'Brgy. I (Poblacion)',   active:3, completed:1 },
            { name:'Brgy. II (Poblacion)',  active:2, completed:2 },
            { name:'Brgy. III (Poblacion)', active:2, completed:1 },
            { name:'Brgy. IV (Poblacion)',  active:1, completed:2 },
            { name:'Brgy. V (Poblacion)',   active:1, completed:1 },
            { name:'San Jose',              active:1, completed:1 },
            { name:'San Juan',              active:2, completed:1 },
            { name:'San Pablo Norte',       active:1, completed:1 },
            { name:'San Pablo Sur',         active:1, completed:1 },
            { name:'Santisima Cruz',        active:0, completed:1 },
            { name:'Santo Angel Central',   active:2, completed:1 },
            { name:'Santo Angel Norte',     active:1, completed:1 },
            { name:'Santo Angel Sur',       active:1, completed:0 },
        ];

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
                Jan:[95,88], Feb:[102,96], Mar:[110,105], Apr:[98,92], May:[120,115],
                Jun:[135,128], Jul:[148,140], Aug:[130,122], Sep:[142,138], Oct:[155,148], Nov:[138,130], Dec:[160,152]
            },
            2026: {
                Jan:[120,110], Feb:[145,130], Mar:[160,150], Apr:[130,125], May:[175,165],
                Jun:[190,180], Jul:[210,195], Aug:[185,170], Sep:[200,185], Oct:[220,210], Nov:[195,180], Dec:[230,215]
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
        const activities = [
            { dot:'green',  title:'Livelihood Training — Brgy. I (Poblacion)',      meta:'Completed · 120 participants · Mar 14, 2026', tag:'Done',      tagColor:'green' },
            { dot:'blue',   title:'Sports Fest 2026 — Municipal Level',              meta:'Ongoing · 340 participants · Mar 10–20, 2026', tag:'Active',   tagColor:'blue' },
            { dot:'yellow', title:'Leadership Summit — Brgy. Labuin',               meta:'Upcoming · 80 slots · Mar 25, 2026',           tag:'Soon',     tagColor:'yellow' },
            { dot:'green',  title:'Environmental Drive — Brgy. Bubukal',            meta:'Completed · 95 participants · Mar 8, 2026',    tag:'Done',     tagColor:'green' },
            { dot:'red',    title:'Health & Wellness Seminar — Brgy. Gatid',        meta:'Cancelled · Mar 5, 2026',                      tag:'Cancelled',tagColor:'red' },
            { dot:'blue',   title:'Anti-Drug Awareness — Brgy. San Juan',           meta:'Ongoing · 60 participants · Mar 12, 2026',     tag:'Active',   tagColor:'blue' },
            { dot:'green',  title:'Disaster Preparedness Drill — Brgy. Alipit',     meta:'Completed · 200 participants · Mar 1, 2026',   tag:'Done',     tagColor:'green' },
            { dot:'yellow', title:'Youth Congress Prep — Brgy. Malinao',            meta:'Upcoming · 150 slots · Mar 28, 2026',          tag:'Soon',     tagColor:'yellow' },
            { dot:'green',  title:'Livelihood Seminar — Brgy. Calios',              meta:'Completed · 55 participants · Feb 28, 2026',   tag:'Done',     tagColor:'green' },
            { dot:'blue',   title:'Gender & Dev. Forum — Brgy. Santo Angel Central',meta:'Ongoing · 40 participants · Mar 15, 2026',     tag:'Active',   tagColor:'blue' },
            { dot:'green',  title:'Tree Planting — Brgy. Pagsawitan',               meta:'Completed · 80 participants · Feb 20, 2026',   tag:'Done',     tagColor:'green' },
            { dot:'yellow', title:'SK Assembly — Brgy. II (Poblacion)',             meta:'Upcoming · 300 slots · Apr 5, 2026',           tag:'Soon',     tagColor:'yellow' },
        ];

        const upcomingPrograms = [];

        const barangays = [
            { name:'Alipit',                programs:3, reports:3, status:'compliant' },
            { name:'Bagumbayan',            programs:2, reports:2, status:'compliant' },
            { name:'Bubukal',               programs:3, reports:2, status:'partial' },
            { name:'Calios',                programs:2, reports:2, status:'compliant' },
            { name:'Duhat',                 programs:1, reports:0, status:'non-compliant' },
            { name:'Gatid',                 programs:3, reports:3, status:'compliant' },
            { name:'Jasaan',                programs:2, reports:1, status:'partial' },
            { name:'Labuin',                programs:3, reports:3, status:'compliant' },
            { name:'Malinao',               programs:2, reports:2, status:'compliant' },
            { name:'Oogong',                programs:1, reports:0, status:'non-compliant' },
            { name:'Pagsawitan',            programs:3, reports:2, status:'partial' },
            { name:'Palasan',               programs:2, reports:2, status:'compliant' },
            { name:'Patimbao',              programs:2, reports:1, status:'partial' },
            { name:'Brgy. I (Poblacion)',   programs:4, reports:4, status:'compliant' },
            { name:'Brgy. II (Poblacion)',  programs:4, reports:4, status:'compliant' },
            { name:'Brgy. III (Poblacion)', programs:3, reports:3, status:'compliant' },
            { name:'Brgy. IV (Poblacion)',  programs:3, reports:2, status:'partial' },
            { name:'Brgy. V (Poblacion)',   programs:2, reports:2, status:'compliant' },
            { name:'San Jose',              programs:2, reports:2, status:'compliant' },
            { name:'San Juan',              programs:3, reports:3, status:'compliant' },
            { name:'San Pablo Norte',       programs:2, reports:1, status:'partial' },
            { name:'San Pablo Sur',         programs:2, reports:2, status:'compliant' },
            { name:'Santisima Cruz',        programs:1, reports:0, status:'non-compliant' },
            { name:'Santo Angel Central',   programs:3, reports:3, status:'compliant' },
            { name:'Santo Angel Norte',     programs:2, reports:2, status:'compliant' },
            { name:'Santo Angel Sur',       programs:1, reports:1, status:'compliant' },
        ];

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

        // Activity renderer
        function renderActivities(items) {
            return items.map(a => `
                <div class="activity-item">
                    <div class="activity-dot ${a.dot}"></div>
                    <div class="activity-content">
                        <p class="activity-title">${a.title}</p>
                        <span class="activity-meta">${a.meta}</span>
                    </div>
                    <span class="activity-tag ${a.tagColor}">${a.tag}</span>
                </div>`).join('');
        }

        // Upcoming renderer — handles both static programs and calendar events
        function renderUpcoming(items) {
            if (!items.length) {
                return '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;"><i class="fas fa-calendar-check" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.4;"></i>No upcoming events.<br>Add one from the calendar.</div>';
            }
            var typeIcons = {event:'📅',task:'✅',meeting:'🤝',deadline:'⚠️'};
            var statusBadge = {
                upcoming:  '<span class="ev-badge ev-badge-upcoming">Upcoming</span>',
                done:      '<span class="ev-badge ev-badge-done">Done</span>',
                cancelled: '<span class="ev-badge ev-badge-cancelled">Cancelled</span>'
            };
            var rows = items.map(function(u) {
                var icon = u.isCalEvent ? (typeIcons[u.type] || '📅') : '📋';
                var isPast = u.status === 'done' || u.status === 'cancelled';
                var status = u.isCalEvent ? (statusBadge[u.status] || statusBadge.upcoming) : '';
                var editBtn = (u.isCalEvent && !isPast)
                    ? '<button onclick="openEditEventModal(\'' + u.id + '\')" class="ev-action-btn" title="Edit"><i class="fas fa-pen"></i></button>'
                    : (isPast ? '<span style="color:#cbd5e1;font-size:11px;">—</span>' : '');
                var deleteBtn = u.isCalEvent
                    ? '<button onclick="deleteCalEvent(\'' + u.id + '\')" class="ev-action-btn ev-action-del" title="Delete"><i class="fas fa-trash-alt"></i></button>'
                    : '';
                var opacity = isPast ? 'opacity:0.55;' : '';
                var date = u.isCalEvent && u.fullDate ? u.fullDate : (u.day + ' ' + u.month);
                var time = u.isCalEvent && u.time ? u.time : (u.sub || '—');
                return '<tr style="' + opacity + '">' +
                    '<td class="ev-td">' + u.title + '</td>' +
                    '<td class="ev-td ev-td-sm">' + u.type.charAt(0).toUpperCase() + u.type.slice(1) + '</td>' +
                    '<td class="ev-td ev-td-sm">' + date + '</td>' +
                    '<td class="ev-td ev-td-sm">' + time + '</td>' +
                    '<td class="ev-td ev-td-sm">' + (u.isCalEvent && u.location ? '<i class="fas fa-map-marker-alt" style="color:#94a3b8;margin-right:3px;font-size:10px;"></i>' + u.location : '—') + '</td>' +
                    '<td class="ev-td ev-td-sm">' + status + '</td>' +
                    '<td class="ev-td ev-td-action">' + editBtn + deleteBtn + '</td>' +
                    '</tr>';
            }).join('');
            return '<div class="ev-table-wrap"><table class="ev-table"><thead><tr>' +
                '<th class="ev-th">Name</th>' +
                '<th class="ev-th ev-td-sm">Type</th>' +
                '<th class="ev-th ev-td-sm">Date</th>' +
                '<th class="ev-th ev-td-sm">Time</th>' +
                '<th class="ev-th ev-td-sm">Location</th>' +
                '<th class="ev-th ev-td-sm">Status</th>' +
                '<th class="ev-th ev-td-action">Action</th>' +
                '</tr></thead><tbody>' + rows + '</tbody></table></div>';
        }

        // Merge calendar events into upcoming list
        function buildUpcomingList() {
            const calEvents = JSON.parse(localStorage.getItem('skfed_calendar_events') || '[]');
            const today = new Date(); today.setHours(0,0,0,0);
            const monthAbbr = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
            const fullMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            const calItems = calEvents
                .filter(e => { const d = new Date(e.date + 'T00:00:00'); return d >= today; })
                .sort((a,b) => a.date.localeCompare(b.date))
                .map(e => {
                    const d = new Date(e.date + 'T00:00:00');
                    var fullDate = fullMonths[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
                    var time = e.start ? e.start + (e.end ? ' – ' + e.end : '') : '';
                    return {
                        id: e.id,
                        day: String(d.getDate()).padStart(2,'0'),
                        month: monthAbbr[d.getMonth()],
                        title: e.title,
                        fullDate: fullDate,
                        time: time,
                        location: e.location || '',
                        type: e.type,
                        status: e.status || 'upcoming',
                        isCalEvent: true
                    };
                });
            return [...calItems, ...upcomingPrograms];
        }

        window.refreshUpcomingList = function() {
            paginators['upcoming-list'] = makePaginator(buildUpcomingList(), 10, renderUpcoming, 'upcoming-list', 'upcoming-pagination');
            paginators['upcoming-list'].init();
        };

        window.clearAllCalEvents = function() {
            if (!confirm('Remove all scheduled events?')) return;
            localStorage.removeItem('skfed_calendar_events');
            if (window.refreshUpcomingList) window.refreshUpcomingList();
            if (typeof renderCalendar === 'function') renderCalendar();
        };

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

        paginators['activity-list']   = makePaginator(activities,       10, renderActivities, 'activity-list',   'activity-pagination');
        paginators['upcoming-list']   = makePaginator(buildUpcomingList(), 10, renderUpcoming,   'upcoming-list',   'upcoming-pagination');
        paginators['compliance-list'] = makePaginator(barangays,        10, renderCompliance, 'compliance-list', 'compliance-pagination');

        paginators['activity-list'].init();
        paginators['upcoming-list'].init();
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
