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

            {{-- ── SUMMARY CARDS ── --}}
            <section class="bm-kpi-grid" style="grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:20px;">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-users" style="color:#213F99;font-size:16px;display:block;margin-bottom:4px;"></i>Total Kabataan</div>
                    <div class="bm-kpi-value">{{ number_format($barangayData['total_kabataan']) }}</div>
                    <div class="bm-kpi-note">Registered youth</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-tasks" style="color:#8b5cf6;font-size:16px;display:block;margin-bottom:4px;"></i>Total Program</div>
                    <div class="bm-kpi-value" style="color:#8b5cf6;">{{ $barangayData['program_stats']['total_programs_created'] }}</div>
                    <div class="bm-kpi-note">All programs</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-check-circle" style="color:#10b981;font-size:16px;display:block;margin-bottom:4px;"></i>Compliant Rate</div>
                    <div class="bm-kpi-value" style="color:#10b981;">{{ $barangayData['compliance_score'] }}%</div>
                    <div class="bm-kpi-note">Compliance score</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-times-circle" style="color:#ef4444;font-size:16px;display:block;margin-bottom:4px;"></i>Non-Compliant Rate</div>
                    <div class="bm-kpi-value" style="color:#ef4444;">{{ 100 - $barangayData['compliance_score'] }}%</div>
                    <div class="bm-kpi-note">Non-compliance</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-chart-line" style="color:#f59e0b;font-size:16px;display:block;margin-bottom:4px;"></i>Performance Rate</div>
                    <div class="bm-kpi-value" style="color:#f59e0b;">{{ $barangayData['performance_summary']['completion_rate'] }}%</div>
                    <div class="bm-kpi-note">Program completion</div>
                </article>
            </section>

            {{-- ── VIEW TABS ── --}}
            <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;align-items:center;justify-content:space-between;">
                <div style="display:flex;gap:4px;">
                    <button onclick="switchTab('profile')" id="tab-profile" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-id-badge"></i> Profile
                    </button>
                    <button onclick="switchTab('abyip')" id="tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-file-invoice-dollar"></i> ABYIP
                    </button>
                    <button onclick="switchTab('programs')" id="tab-programs" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-tasks"></i> Programs
                    </button>
                </div>
                <div style="display:flex;align-items:center;gap:8px;padding-bottom:2px;position:relative;">
                    <div style="position:relative;display:flex;align-items:center;">
                        <i class="fas fa-search" style="position:absolute;left:12px;color:#94a3b8;font-size:13px;pointer-events:none;"></i>
                        <input type="text" id="tabSearchInput" placeholder="Search..." style="padding:8px 12px 8px 36px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;width:200px;transition:all .2s;" onfocus="this.style.borderColor='#213F99';this.style.boxShadow='0 0 0 3px rgba(33,63,153,.1)'" onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'" oninput="performTabSearch(this.value)">
                        <button onclick="clearTabSearch()" id="clearSearchBtn" style="position:absolute;right:8px;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:16px;padding:4px 8px;display:none;transition:color .2s;" onmouseover="this.style.color='#64748b'" onmouseout="this.style.color='#94a3b8'" title="Clear search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
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

            {{-- ── ABYIP TAB ── --}}
            <div id="section-abyip" style="display:none;">
            @php $abyip = $barangayData['abyip_data']; @endphp

            {{-- ── ABYIP HEADER ── --}}
            <h2 style="font-size:18px;font-weight:800;color:#0d1b4b;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-file-invoice-dollar" style="color:#213F99;font-size:20px;"></i>
                ABYIP
            </h2>

            {{-- ── ABYIP SUMMARY CARDS ── --}}
            <section class="bm-kpi-grid" style="grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:20px;">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-percentage" style="color:#3b82f6;font-size:16px;display:block;margin-bottom:4px;"></i>Annual Budget Utilization Rate</div>
                    <div class="bm-kpi-value" style="color:#3b82f6;">{{ number_format($abyip['budget_utilization_rate'], 2) }}%</div>
                    <div class="bm-kpi-note">Budget efficiency</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-wallet" style="color:#10b981;font-size:16px;display:block;margin-bottom:4px;"></i>Remaining Balance</div>
                    <div class="bm-kpi-value" style="color:#10b981;">₱{{ number_format($abyip['remaining_balance']) }}</div>
                    <div class="bm-kpi-note">Available funds</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-tasks" style="color:#f59e0b;font-size:16px;display:block;margin-bottom:4px;"></i>Project Count</div>
                    <div class="bm-kpi-value" style="color:#f59e0b;">{{ $abyip['project_count'] }}</div>
                    <div class="bm-kpi-note">Active projects</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-coins" style="color:#8b5cf6;font-size:16px;display:block;margin-bottom:4px;"></i>Total Budget</div>
                    <div class="bm-kpi-value" style="color:#8b5cf6;">₱{{ number_format($abyip['annual_budget']) }}</div>
                    <div class="bm-kpi-note">Annual allocation</div>
                </article>
            </section>

            {{-- ── ABYIP REPORTS TABLE ── --}}
            <section class="bm-card">
                <div class="bm-card-head">
                    <h3><i class="fas fa-file-alt" style="color:#213F99;margin-right:6px;"></i>ABYIP Reports - Submitted</h3>
                </div>
                <div class="bm-table-wrap">
                    <table class="bm-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-alt" title="Report Name"></i> <span class="bm-th-text">Report Name</span></th>
                                <th><i class="fas fa-calendar" title="Date Submitted"></i> <span class="bm-th-text">Date Submitted</span></th>
                                <th><i class="fas fa-user" title="Submitted By"></i> <span class="bm-th-text">Submitted By</span></th>
                                <th><i class="fas fa-cog" title="Actions"></i> <span class="bm-th-text">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abyip['reports'] as $report)
                            <tr>
                                <td style="font-weight:600;color:#0d1b4b;">{{ $report['name'] }}</td>
                                <td>{{ $report['date_submitted'] }}</td>
                                <td>{{ $report['submitted_by'] }}</td>
                                <td>
                                    <div style="display:flex;gap:8px;align-items:center;">
                                        <a href="#" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#213F99;text-decoration:none;background:#eff3ff;padding:6px 12px;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff3ff'">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                        <button style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#64748b;background:#f1f5f9;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            </div>{{-- end section-abyip --}}

            {{-- ── PROGRAMS TAB ── --}}
            <div id="section-programs" style="display:none;">
            @php $programs = $barangayData['programs_data']; @endphp

            {{-- ── PROGRAMS HEADER ── --}}
            <h2 style="font-size:18px;font-weight:800;color:#0d1b4b;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-tasks" style="color:#8b5cf6;font-size:20px;"></i>
                Programs
            </h2>

            {{-- ── PROGRAMS SUMMARY CARDS ── --}}
            <section class="bm-kpi-grid" style="grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:20px;">
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-list-check" style="color:#213F99;font-size:16px;display:block;margin-bottom:4px;"></i>Total Program</div>
                    <div class="bm-kpi-value" style="color:#213F99;">{{ $programs['total_programs'] }}</div>
                    <div class="bm-kpi-note">All programs</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-spinner" style="color:#f59e0b;font-size:16px;display:block;margin-bottom:4px;"></i>Ongoing Program</div>
                    <div class="bm-kpi-value" style="color:#f59e0b;">{{ $programs['ongoing_programs'] }}</div>
                    <div class="bm-kpi-note">Currently active</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-users" style="color:#3b82f6;font-size:16px;display:block;margin-bottom:4px;"></i>Participation Rate</div>
                    <div class="bm-kpi-value" style="color:#3b82f6;">{{ $programs['participation_rate'] }}%</div>
                    <div class="bm-kpi-note">Youth involvement</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-user-check" style="color:#10b981;font-size:16px;display:block;margin-bottom:4px;"></i>Attendance Rate</div>
                    <div class="bm-kpi-value" style="color:#10b981;">{{ $programs['attendance_rate'] }}%</div>
                    <div class="bm-kpi-note">Actual attendance</div>
                </article>
                <article class="bm-kpi-card">
                    <div class="bm-kpi-label"><i class="fas fa-star" style="color:#f97316;font-size:16px;display:block;margin-bottom:4px;"></i>Evaluation Rate</div>
                    <div class="bm-kpi-value" style="color:#f97316;">{{ $programs['evaluation_rate'] }}%</div>
                    <div class="bm-kpi-note">Program quality</div>
                </article>
            </section>

            {{-- ── PROGRAMS REPORT TABLE ── --}}
            <section class="bm-card">
                <div class="bm-card-head">
                    <h3><i class="fas fa-chart-bar" style="color:#8b5cf6;margin-right:6px;"></i>Programs Report</h3>
                </div>
                <div class="bm-table-wrap">
                    <table class="bm-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-alt" title="Program Name"></i> <span class="bm-th-text">Program Name</span></th>
                                <th><i class="fas fa-calendar" title="Date"></i> <span class="bm-th-text">Date</span></th>
                                <th><i class="fas fa-users" title="Participants Registered"></i> <span class="bm-th-text">Registered</span></th>
                                <th><i class="fas fa-user-check" title="Attendance"></i> <span class="bm-th-text">Attendance</span></th>
                                <th><i class="fas fa-star" title="Evaluation"></i> <span class="bm-th-text">Evaluation</span></th>
                                <th><i class="fas fa-file-pdf" title="Report Submitted"></i> <span class="bm-th-text">Report</span></th>
                                <th><i class="fas fa-cog" title="Actions"></i> <span class="bm-th-text">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programs['reports'] as $prog)
                            <tr>
                                <td style="font-weight:600;color:#0d1b4b;">{{ $prog['name'] }}</td>
                                <td>{{ $prog['date'] }}</td>
                                <td>{{ $prog['participants_registered'] }}</td>
                                <td>{{ $prog['attendance'] }}</td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#f97316;background:#fff3e0;padding:4px 10px;border-radius:6px;">
                                        <i class="fas fa-star"></i> {{ $prog['evaluation'] }}%
                                    </span>
                                </td>
                                <td>
                                    @if($prog['report_submitted'] === 'Yes' && $prog['file'])
                                        <a href="#" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#213F99;text-decoration:none;background:#eff3ff;padding:4px 10px;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff3ff'" title="Download PDF report">
                                            <i class="fas fa-file-pdf"></i> {{ $prog['file'] }}
                                        </a>
                                    @elseif($prog['report_submitted'] === 'Yes')
                                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#10b981;background:#ecfdf5;padding:4px 10px;border-radius:6px;">
                                            <i class="fas fa-check-circle"></i> Submitted
                                        </span>
                                    @else
                                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#f59e0b;background:#fef3c7;padding:4px 10px;border-radius:6px;">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <button onclick="openProgramModal({{ json_encode($prog) }})" style="display:inline-flex;align-items:center;gap:3px;font-size:11px;color:#64748b;text-decoration:none;background:#f1f5f9;border:none;padding:5px 10px;border-radius:6px;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'" title="View program details">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            </div>{{-- end section-programs --}}

            {{-- ── PROGRAM DETAIL MODAL ── --}}
            <div id="programModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
                <div style="background:#fff;border-radius:16px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);">
                    {{-- Modal Header --}}
                    <div style="padding:24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                        <h2 id="modalTitle" style="font-size:18px;font-weight:800;color:#0d1b4b;margin:0;"></h2>
                        <button onclick="closeProgramModal()" style="background:none;border:none;font-size:24px;color:#64748b;cursor:pointer;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    {{-- Modal Body --}}
                    <div style="padding:24px;">
                        <div style="margin-bottom:20px;">
                            <p style="font-size:12px;color:#64748b;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Description</p>
                            <p id="modalDescription" style="font-size:14px;color:#475569;margin:0;line-height:1.6;"></p>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                            <div style="background:#f8fafc;border-radius:12px;padding:16px;">
                                <p style="font-size:11px;color:#64748b;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Date Happened</p>
                                <p id="modalDateHappened" style="font-size:14px;font-weight:600;color:#0d1b4b;margin:0;"></p>
                            </div>
                            <div style="background:#f8fafc;border-radius:12px;padding:16px;">
                                <p style="font-size:11px;color:#64748b;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Participants Joined</p>
                                <p id="modalParticipantsJoined" style="font-size:14px;font-weight:600;color:#0d1b4b;margin:0;"></p>
                            </div>
                        </div>
                        <div style="margin-bottom:20px;">
                            <p style="font-size:12px;color:#64748b;margin:0 0 8px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Objectives</p>
                            <ul id="modalObjectives" style="margin:0;padding-left:20px;color:#475569;font-size:13px;line-height:1.6;"></ul>
                        </div>
                        <div style="margin-bottom:20px;">
                            <p style="font-size:12px;color:#64748b;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Outcomes</p>
                            <p id="modalOutcomes" style="font-size:14px;color:#475569;margin:0;line-height:1.6;"></p>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div style="background:#eff3ff;border-radius:12px;padding:16px;border-left:4px solid #213F99;">
                                <p style="font-size:11px;color:#213F99;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Registered</p>
                                <p id="modalRegistered" style="font-size:18px;font-weight:800;color:#213F99;margin:0;"></p>
                            </div>
                            <div style="background:#ecfdf5;border-radius:12px;padding:16px;border-left:4px solid #10b981;">
                                <p style="font-size:11px;color:#10b981;margin:0 0 4px 0;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Attendance</p>
                                <p id="modalAttendance" style="font-size:18px;font-weight:800;color:#10b981;margin:0;"></p>
                            </div>
                        </div>
                    </div>
                    {{-- Modal Footer --}}
                    <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                        <button onclick="closeProgramModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            Close
                        </button>
                    </div>
                </div>
            </div>
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
            document.getElementById('section-abyip').style.display      = tab === 'abyip'      ? 'block' : 'none';
            document.getElementById('section-programs').style.display   = tab === 'programs'   ? 'block' : 'none';
            
            const pBtn = document.getElementById('tab-profile');
            const aBtn = document.getElementById('tab-abyip');
            const prBtn = document.getElementById('tab-programs');
            
            pBtn.style.color       = tab === 'profile'    ? '#213F99' : '#64748b';
            pBtn.style.borderColor = tab === 'profile'    ? '#213F99' : 'transparent';
            aBtn.style.color       = tab === 'abyip'      ? '#213F99' : '#64748b';
            aBtn.style.borderColor = tab === 'abyip'      ? '#213F99' : 'transparent';
            prBtn.style.color      = tab === 'programs'   ? '#213F99' : '#64748b';
            prBtn.style.borderColor = tab === 'programs'  ? '#213F99' : 'transparent';
            
            // Clear search when switching tabs
            clearTabSearch();
        }

        function performTabSearch(query) {
            const searchTerm = query.toLowerCase().trim();
            const clearBtn = document.getElementById('clearSearchBtn');
            
            if (searchTerm) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }
            
            // Search in ABYIP tab
            const abyipReports = document.querySelectorAll('#section-abyip .bm-table tbody tr');
            abyipReports.forEach(row => {
                const reportName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const submittedBy = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const matches = reportName.includes(searchTerm) || submittedBy.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
            
            // Search in Programs tab
            const programRows = document.querySelectorAll('#section-programs .bm-table tbody tr');
            programRows.forEach(row => {
                const programName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const matches = programName.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
            });
        }

        function clearTabSearch() {
            document.getElementById('tabSearchInput').value = '';
            document.getElementById('clearSearchBtn').style.display = 'none';
            
            // Show all rows
            document.querySelectorAll('#section-abyip .bm-table tbody tr').forEach(row => {
                row.style.display = '';
            });
            document.querySelectorAll('#section-programs .bm-table tbody tr').forEach(row => {
                row.style.display = '';
            });
        }

        function openProgramModal(program) {
            document.getElementById('modalTitle').textContent = program.name;
            document.getElementById('modalDescription').textContent = program.description;
            document.getElementById('modalDateHappened').textContent = program.date_happened;
            document.getElementById('modalParticipantsJoined').textContent = program.participants_joined;
            document.getElementById('modalRegistered').textContent = program.participants_registered;
            document.getElementById('modalAttendance').textContent = program.attendance;
            
            const objectivesList = document.getElementById('modalObjectives');
            objectivesList.innerHTML = '';
            program.objectives.forEach(obj => {
                const li = document.createElement('li');
                li.textContent = obj;
                objectivesList.appendChild(li);
            });
            
            document.getElementById('modalOutcomes').textContent = program.outcomes;
            
            const modal = document.getElementById('programModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeProgramModal() {
            document.getElementById('programModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.getElementById('programModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProgramModal();
            }
        });

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
