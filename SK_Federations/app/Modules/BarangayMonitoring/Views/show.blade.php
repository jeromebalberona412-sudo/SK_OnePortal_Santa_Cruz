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
            <a class="bm-back-link" href="{{ route('barangay-monitoring') }}" style="margin-bottom:16px;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-arrow-left"></i> Back to All Barangays</a>

            {{-- ── PROFILE CARD (Kabataan-style) ── --}}
            @php
            $brgyColors = [
                'brgy-1-poblacion'=>'#213F99','brgy-2-poblacion'=>'#2196F3','brgy-3-poblacion'=>'#9C27B0',
                'brgy-4-poblacion'=>'#FF9800','brgy-5-poblacion'=>'#009688','labuin'=>'#f44336',
                'pagsawitan'=>'#673AB7','san-jose'=>'#0450a8','santisima-cruz'=>'#FF5722',
            ];
            $brgyColor = $brgyColors[$barangayData['slug'] ?? ''] ?? '#213F99';
            $initials  = strtoupper(substr($barangayData['name'], 0, 2));
            @endphp
            <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(33,63,153,.08);margin-bottom:24px;">
                {{-- Cover --}}
                <div style="height:140px;background:linear-gradient(135deg,{{ $brgyColor }} 0%,#0d1b4b 100%);position:relative;overflow:hidden;">
                    <div style="position:absolute;inset:0;background-image:url('/modules/authentication/images/Background.png');background-size:cover;background-position:center;background-repeat:no-repeat;opacity:.08;mix-blend-mode:luminosity;"></div>
                </div>
                {{-- Info row --}}
                <div style="padding:0 28px 20px;display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap;">
                    <div style="margin-top:-44px;flex-shrink:0;">
                        <div style="width:88px;height:88px;border-radius:50%;background:{{ $brgyColor }};border:4px solid #fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.15);">{{ $initials }}</div>
                    </div>
                    <div style="flex:1;padding-top:12px;">
                        <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(33,63,153,.08);color:#213F99;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:3px 10px;border-radius:999px;margin-bottom:4px;">
                            <i class="fas fa-check-circle" style="font-size:10px;"></i> Sangguniang Kabataan
                        </div>
                        <h2 style="font-size:20px;font-weight:800;color:#0d1b4b;margin-bottom:2px;">SK {{ $barangayData['name'] }}</h2>
                        <p style="font-size:13px;color:#64748b;"><i class="fas fa-map-marker-alt" style="margin-right:4px;color:#213F99;"></i>{{ $barangayData['name'] }}, {{ $barangayData['municipality'] }}</p>
                        <div style="display:flex;gap:20px;margin-top:8px;flex-wrap:wrap;">
                            <div style="text-align:center;"><strong style="display:block;font-size:18px;font-weight:800;color:#213F99;">{{ $barangayData['active_programs'] }}</strong><span style="font-size:11px;color:#94a3b8;">Programs</span></div>
                            <div style="text-align:center;"><strong style="display:block;font-size:18px;font-weight:800;color:#213F99;">{{ count($barangayData['officials']) }}</strong><span style="font-size:11px;color:#94a3b8;">Officers</span></div>
                            <div style="text-align:center;"><strong style="display:block;font-size:18px;font-weight:800;color:#213F99;">{{ $barangayData['compliance_score'] }}%</strong><span style="font-size:11px;color:#94a3b8;">Compliance</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── VIEW TABS ── --}}
            <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;">
                <button onclick="switchTab('profile')" id="tab-profile" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-id-badge"></i> Profile
                </button>
                <button onclick="switchTab('monitoring')" id="tab-monitoring" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-chart-bar"></i> Monitoring
                </button>
            </div>

            {{-- ── PROFILE TAB ── --}}
            <div id="section-profile">
                <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
                    {{-- Left: Officers + Contact --}}
                    <div style="display:flex;flex-direction:column;gap:16px;">
                        <div class="bm-card">
                            <div class="bm-card-head"><h3><i class="fas fa-users" style="color:#213F99;margin-right:6px;"></i>SK Officers</h3></div>
                            <div style="padding:0 20px 16px;">
                                @foreach($barangayData['officials'] as $o)
                                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:{{ $brgyColor }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">
                                        {{ strtoupper(substr($o['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <p style="font-size:13px;font-weight:700;color:#1e293b;">{{ $o['name'] }}</p>
                                        <p style="font-size:11px;color:#94a3b8;">{{ $o['role'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="bm-card">
                            <div class="bm-card-head"><h3><i class="fas fa-phone" style="color:#213F99;margin-right:6px;"></i>Contact Information</h3></div>
                            <div style="padding:0 20px 16px;">
                                @foreach([['fas fa-phone','Phone','[SK Contact Number]'],['fas fa-envelope','Email','[SK Email Address]'],['fas fa-map-marker-alt','Address',$barangayData['name'].', Santa Cruz, Laguna'],['fas fa-clock','Office Hours','Mon–Fri, 8:00 AM – 5:00 PM']] as $row)
                                <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                                    <div style="width:30px;height:30px;border-radius:8px;background:#eff3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="{{ $row[0] }}" style="font-size:12px;color:#213F99;"></i>
                                    </div>
                                    <div>
                                        <p style="font-size:11px;color:#94a3b8;">{{ $row[1] }}</p>
                                        <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $row[2] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    {{-- Right: Posts --}}
                    <div class="bm-card">
                        <div class="bm-card-head"><h3><i class="fas fa-newspaper" style="color:#213F99;margin-right:6px;"></i>Posts from {{ $barangayData['name'] }}</h3></div>
                        <div style="padding:0 20px 16px;display:flex;flex-direction:column;gap:14px;">
                            @foreach($barangayData['programs']['current_programs'] as $prog)
                            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $brgyColor }};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">{{ $initials }}</div>
                                    <div>
                                        <p style="font-size:13px;font-weight:700;color:#0d1b4b;">SK {{ $barangayData['name'] }}</p>
                                        <p style="font-size:11px;color:#94a3b8;"><span style="background:#dbeafe;color:#1d4ed8;padding:2px 7px;border-radius:8px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:4px;">Program</span>{{ $prog['timeline'] }}</p>
                                    </div>
                                </div>
                                <h4 style="font-size:15px;font-weight:700;color:#0d1b4b;margin-bottom:4px;">{{ $prog['title'] }}</h4>
                                <p style="font-size:13px;color:#475569;">{{ $prog['sector'] }} program · {{ $prog['participants'] }} participants</p>
                                <div style="display:flex;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;">
                                    <button style="flex:1;padding:7px;border:none;background:none;border-radius:8px;font-size:12px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i class="fas fa-thumbs-up"></i> Like</button>
                                    <button style="flex:1;padding:7px;border:none;background:none;border-radius:8px;font-size:12px;color:#64748b;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i class="fas fa-comment"></i> Comment</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── MONITORING TAB ── --}}
            <div id="section-monitoring" style="display:none;">
            @php $ps = $barangayData['program_stats']; $perf = $barangayData['performance_summary']; @endphp

            {{-- ── OVERVIEW KPIs ── --}}
            <section class="bm-kpi-grid" style="grid-template-columns:repeat(6,minmax(0,1fr));margin-bottom:18px;">
                <article class="bm-kpi-card" title="Total Youth Population">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-users" style="color:#213F99;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Youth Pop.</span></div>
                    <div class="bm-kpi-value">{{ number_format($ps['total_youth_population']) }}</div>
                    <div class="bm-kpi-note">Registered</div>
                </article>
                <article class="bm-kpi-card" title="Total Programs Created">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-tasks" style="color:#8b5cf6;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Programs</span></div>
                    <div class="bm-kpi-value" style="color:#8b5cf6;">{{ $ps['total_programs_created'] }}</div>
                    <div class="bm-kpi-note">All time</div>
                </article>
                <article class="bm-kpi-card" title="Total Ongoing Programs">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-spinner" style="color:#f59e0b;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Ongoing</span></div>
                    <div class="bm-kpi-value" style="color:#f59e0b;">{{ $ps['total_ongoing'] }}</div>
                    <div class="bm-kpi-note">This quarter</div>
                </article>
                <article class="bm-kpi-card" title="Total Completed Programs">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-check-circle" style="color:#10b981;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Completed</span></div>
                    <div class="bm-kpi-value" style="color:#10b981;">{{ $ps['total_completed'] }}</div>
                    <div class="bm-kpi-note">Finished</div>
                </article>
                <article class="bm-kpi-card" title="Total Participants">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-user-check" style="color:#3b82f6;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Participants</span></div>
                    <div class="bm-kpi-value" style="color:#3b82f6;">{{ number_format($ps['total_participants']) }}</div>
                    <div class="bm-kpi-note">All programs</div>
                </article>
                <article class="bm-kpi-card" title="Overall Performance Rating">
                    <div class="bm-kpi-label" style="text-align:center;"><i class="fas fa-star" style="color:#f97316;font-size:18px;display:block;margin-bottom:4px;"></i><span class="bm-kpi-text" style="font-size:11px;">Rating</span></div>
                    <div class="bm-kpi-value" style="font-size:14px;color:#f97316;font-weight:700;">{{ $ps['overall_performance'] }}</div>
                    <div class="bm-kpi-note">Federation</div>
                </article>
            </section>

            {{-- ── PERFORMANCE SUMMARY ── --}}
            <section class="bm-card" style="margin-bottom:18px;">
                <div class="bm-card-head">
                    <h3><i class="fas fa-chart-line" style="color:#213F99;margin-right:6px;"></i>Performance Summary</h3>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;padding:0 20px 16px;">
                    @php
                    $perfCards = [
                        ['Completion', $perf['completion_rate'].'%', '#10b981', 'fas fa-check-double', 'Program Completion Rate'],
                        ['Attendance', $perf['attendance_rate'].'%', '#3b82f6', 'fas fa-user-friends', 'Participant Attendance Rate'],
                        ['Budget Eff.', $perf['budget_efficiency'].'%', '#8b5cf6', 'fas fa-coins', 'Budget Efficiency'],
                    ];
                    @endphp
                    @foreach($perfCards as $pc)
                    <div style="background:#f8fafc;border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;" title="{{ $pc[4] }}">
                        <div style="width:44px;height:44px;border-radius:12px;background:{{ $pc[2] }}1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="{{ $pc[3] }}" style="font-size:18px;color:{{ $pc[2] }};"></i>
                        </div>
                        <div style="min-width:0;">
                            <p style="font-size:11px;color:#64748b;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $pc[0] }}</p>
                            <p style="font-size:20px;font-weight:800;color:{{ $pc[2] }};line-height:1;">{{ $pc[1] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:0 20px 20px;">
                    <div style="background:#f8fafc;border-radius:12px;padding:12px;" title="Most Active Sector">
                        <p style="font-size:10px;color:#64748b;margin-bottom:4px;"><i class="fas fa-trophy" style="color:#f59e0b;margin-right:3px;"></i><span>Top Sector</span></p>
                        <p style="font-size:14px;font-weight:700;color:#0d1b4b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $perf['most_active_sector'] }}</p>
                    </div>
                    <div style="background:#f8fafc;border-radius:12px;padding:12px;" title="Most Successful Program">
                        <p style="font-size:10px;color:#64748b;margin-bottom:4px;"><i class="fas fa-medal" style="color:#10b981;margin-right:3px;"></i><span>Top Program</span></p>
                        <p style="font-size:13px;font-weight:700;color:#0d1b4b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $perf['most_successful_program'] }}</p>
                    </div>
                    <div style="background:#fff3e0;border-radius:12px;padding:12px;border:1px solid #fed7aa;" title="Programs with Low Participation">
                        <p style="font-size:10px;color:#92400e;margin-bottom:4px;"><i class="fas fa-exclamation-triangle" style="margin-right:3px;"></i><span>Low Participation</span></p>
                        <p style="font-size:20px;font-weight:800;color:#f97316;">{{ $perf['low_participation_count'] }}</p>
                    </div>
                </div>
            </section>

            {{-- ── PROGRAM LIST ── --}}
            <section class="bm-card" style="margin-bottom:18px;">
                <div class="bm-card-head" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3><i class="fas fa-list-alt" style="color:#213F99;margin-right:6px;"></i>Programs List</h3>
                    <div style="display:flex;gap:8px;">
                        <select id="programStatusFilter" onchange="filterPrograms()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                            <option value="all">All Status</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Planned">Planned</option>
                        </select>
                    </div>
                </div>
                <div class="bm-table-wrap">
                    <table class="bm-table" id="programTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-alt" title="Program Name"></i> <span class="bm-th-text">Program Name</span></th>
                                <th><i class="fas fa-tag" title="Category / Sector"></i> <span class="bm-th-text">Category</span></th>
                                <th><i class="fas fa-circle" title="Status"></i> <span class="bm-th-text">Status</span></th>
                                <th><i class="fas fa-users" title="Participants"></i> <span class="bm-th-text">Participants</span></th>
                                <th><i class="fas fa-wallet" title="Budget Allocated"></i> <span class="bm-th-text">Allocated</span></th>
                                <th><i class="fas fa-receipt" title="Budget Used"></i> <span class="bm-th-text">Used</span></th>
                                <th><i class="fas fa-calendar" title="Timeline"></i> <span class="bm-th-text">Timeline</span></th>
                                <th><i class="fas fa-paperclip" title="Reports"></i> <span class="bm-th-text">Reports</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barangayData['program_list'] as $prog)
                            @php
                            $statusClass = match(strtolower($prog['status'])) {
                                'completed' => 'completed',
                                'ongoing'   => 'ongoing',
                                'planned'   => 'planned',
                                default     => 'planned',
                            };
                            @endphp
                            <tr data-status="{{ $prog['status'] }}">
                                <td style="font-weight:600;color:#0d1b4b;">{{ $prog['title'] }}</td>
                                <td>{{ $prog['sector'] }}</td>
                                <td><span class="bm-inline-tag {{ $statusClass }}">{{ $prog['status'] }}</span></td>
                                <td>{{ $prog['participants'] }}</td>
                                <td>{{ $prog['budget_allocated'] }}</td>
                                <td>{{ $prog['budget_used'] }}</td>
                                <td style="font-size:12px;color:#64748b;">{{ $prog['timeline'] }}</td>
                                <td>
                                    @if(!empty($prog['reports']))
                                        @foreach($prog['reports'] as $report)
                                        <a href="#" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#213F99;text-decoration:none;background:#eff3ff;padding:3px 8px;border-radius:6px;">
                                            <i class="fas fa-file-pdf"></i> {{ $report }}
                                        </a>
                                        @endforeach
                                    @else
                                        <span style="font-size:11px;color:#94a3b8;">No reports yet</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            </div>{{-- end section-monitoring --}}
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

        function switchTab(tab) {
            document.getElementById('section-profile').style.display    = tab === 'profile'    ? 'block' : 'none';
            document.getElementById('section-monitoring').style.display = tab === 'monitoring' ? 'block' : 'none';
            const pBtn = document.getElementById('tab-profile');
            const mBtn = document.getElementById('tab-monitoring');
            pBtn.style.color       = tab === 'profile'    ? '#213F99' : '#64748b';
            pBtn.style.borderColor = tab === 'profile'    ? '#213F99' : 'transparent';
            mBtn.style.color       = tab === 'monitoring' ? '#213F99' : '#64748b';
            mBtn.style.borderColor = tab === 'monitoring' ? '#213F99' : 'transparent';
        }

        function filterPrograms() {
            var filter = document.getElementById('programStatusFilter').value;
            document.querySelectorAll('#programTable tbody tr').forEach(function(row) {
                row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        }

        // Responsive: stack profile grid on small screens
        function adjustProfileGrid() {
            const grid = document.querySelector('#section-profile > div');
            if (!grid) return;
            grid.style.gridTemplateColumns = window.innerWidth < 900 ? '1fr' : '300px 1fr';
        }
        window.addEventListener('resize', adjustProfileGrid);
        adjustProfileGrid();
    </script>
</body>
</html>
