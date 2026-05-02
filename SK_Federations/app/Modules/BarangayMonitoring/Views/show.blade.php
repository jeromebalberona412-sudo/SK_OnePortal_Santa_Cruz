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
            <input type="text" id="navbarSearchInput" placeholder="Search..." aria-label="Search" onkeyup="performSearch(event)">
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
            <button type="button" class="menu-item" onclick="switchMainSection('reports')" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </button>
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

            {{-- ── BARANGAY HEADER ── --}}
            <div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                    <div>
                        <h2 style="font-size:28px;font-weight:800;color:#0d1b4b;margin-bottom:4px;">{{ $barangayData['name'] }}</h2>
                        <p style="font-size:14px;color:#64748b;"><i class="fas fa-map-marker-alt" style="margin-right:6px;color:#213F99;"></i>{{ $barangayData['name'] }}, {{ $barangayData['municipality'] }}</p>
                    </div>
                    {{-- Compliance Status Badge --}}
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:600;color:#64748b;">Status:</span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:700;
                        @if($barangayData['compliance_status'] === 'compliant')
                            background:#dcfce7;color:#15803d;
                        @elseif($barangayData['compliance_status'] === 'partial')
                            background:#fef3c7;color:#b45309;
                        @else
                            background:#fee2e2;color:#dc2626;
                        @endif
                        ">
                            @if($barangayData['compliance_status'] === 'compliant')
                                <i class="fas fa-check-circle"></i> Compliant
                            @elseif($barangayData['compliance_status'] === 'partial')
                                <i class="fas fa-exclamation-circle"></i> Partial
                            @else
                                <i class="fas fa-times-circle"></i> Non-Compliant
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Warning Notification --}}
            @if(!empty($barangayData['warnings']))
                @foreach($barangayData['warnings'] as $warning)
                <div style="margin-bottom:20px;padding:16px;border-radius:12px;border-left:4px solid;
                @if($warning['type'] === 'critical')
                    background:#fee2e2;border-color:#dc2626;
                @else
                    background:#fef3c7;border-color:#f59e0b;
                @endif
                ">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="font-size:20px;
                        @if($warning['type'] === 'critical')
                            color:#dc2626;
                        @else
                            color:#f59e0b;
                        @endif
                        ">
                            @if($warning['type'] === 'critical')
                                <i class="fas fa-exclamation-triangle"></i>
                            @else
                                <i class="fas fa-exclamation-circle"></i>
                            @endif
                        </div>
                        <div style="flex:1;">
                            <h4 style="margin:0 0 4px 0;font-size:14px;font-weight:700;
                            @if($warning['type'] === 'critical')
                                color:#991b1b;
                            @else
                                color:#92400e;
                            @endif
                            ">{{ $warning['title'] }}</h4>
                            <p style="margin:0 0 12px 0;font-size:13px;
                            @if($warning['type'] === 'critical')
                                color:#7f1d1d;
                            @else
                                color:#78350f;
                            @endif
                            ">{{ $warning['message'] }}</p>
                            <button onclick="openWarningModal('{{ $warning['type'] }}', '{{ $warning['title'] }}')" style="padding:6px 12px;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;
                            @if($warning['type'] === 'critical')
                                background:#dc2626;color:#fff;
                            @else
                                background:#f59e0b;color:#fff;
                            @endif
                            ">
                                <i class="fas fa-bell"></i> Send Warning
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            {{-- Mobile Dropdown for Summary Cards --}}
            <div id="summaryDropdownMobile" style="display:none;margin-bottom:20px;">
                <button onclick="toggleSummaryDropdown()" style="width:100%;padding:12px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;font-size:14px;font-weight:600;color:#213F99;cursor:pointer;display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Summary Cards</span>
                    <i class="fas fa-chevron-down" id="summaryDropdownIcon"></i>
                </button>
                <div id="summaryDropdownContent" style="display:none;margin-top:8px;"></div>
            </div>

            {{-- Summary Cards (Desktop) --}}
            <div id="summaryCardsDesktop" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
                {{-- Total Kabataan --}}
                <div style="background:linear-gradient(135deg,#213F99 0%,#1a2f7a 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(33,63,153,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-users" style="margin-right:6px;"></i>Total Kabataan</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">{{ number_format($barangayData['program_stats']['total_youth_population'] ?? 0) }}</p>
                </div>

                {{-- Total Programs --}}
                <div style="background:linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(139,92,246,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-tasks" style="margin-right:6px;"></i>Total Programs</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['program_stats']['total_programs_created'] ?? 0 }}</p>
                </div>

                {{-- Compliant Rate --}}
                <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-check-circle" style="margin-right:6px;"></i>Compliant Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['compliance_score'] ?? 0 }}%</p>
                </div>

                {{-- Non-Compliant Rate --}}
                <div style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(249,115,22,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Non-Compliant Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">{{ 100 - ($barangayData['compliance_score'] ?? 0) }}%</p>
                </div>

                {{-- Performance Rate --}}
                <div style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(245,158,11,.15);">
                    <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-star" style="margin-right:6px;"></i>Performance Rate</p>
                    <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['program_stats']['overall_performance'] ?? 'N/A' }}</p>
                </div>
            </div>

            {{-- Summary Cards (Mobile) --}}
            <div id="summaryCardsMobile" style="display:none;"></div>

            {{-- ── VIEW TABS ── --}}
            <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0;">
                <button onclick="switchTab('profile')" id="tab-profile" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-id-badge"></i> Profile
                </button>
                <button onclick="switchTab('abyip')" id="tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-file-invoice-dollar"></i> Barangay ABYIP
                </button>
                <button onclick="switchTab('accomplishment')" id="tab-accomplishment" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                    <i class="fas fa-trophy"></i> Accomplishment
                </button>
            </div>

            {{-- ── PROFILE TAB ── --}}
            @php
            $brgyColors = [
                'brgy-1-poblacion'=>'#213F99','brgy-2-poblacion'=>'#2196F3','brgy-3-poblacion'=>'#9C27B0',
                'brgy-4-poblacion'=>'#FF9800','brgy-5-poblacion'=>'#009688','labuin'=>'#f44336',
                'pagsawitan'=>'#673AB7','san-jose'=>'#0450a8','santisima-cruz'=>'#FF5722',
            ];
            $brgyColor = $brgyColors[$barangayData['slug'] ?? ''] ?? '#213F99';
            $initials  = strtoupper(substr($barangayData['name'], 0, 2));
            @endphp
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
                {{-- ABYIP Header --}}
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:24px;font-weight:800;color:#0d1b4b;margin-bottom:4px;"><i class="fas fa-file-invoice-dollar" style="color:#213F99;margin-right:8px;"></i>Barangay ABYIP</h2>
                    <p style="font-size:14px;color:#64748b;">Annual Budget and Work Plan - Summary and Reports</p>
                </div>

                {{-- Mobile Dropdown for ABYIP Summary Cards --}}
                <div id="abyipSummaryDropdownMobile" style="display:none;margin-bottom:20px;">
                    <button onclick="toggleAbyipSummaryDropdown()" style="width:100%;padding:12px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;font-size:14px;font-weight:600;color:#213F99;cursor:pointer;display:flex;align-items:center;justify-content:space-between;">
                        <span><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Summary Cards</span>
                        <i class="fas fa-chevron-down" id="abyipSummaryDropdownIcon"></i>
                    </button>
                    <div id="abyipSummaryDropdownContent" style="display:none;margin-top:8px;"></div>
                </div>

                {{-- ABYIP Summary Cards (Desktop) --}}
                <div id="abyipSummaryCardsDesktop" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
                    {{-- Annual Budget Utilization Rate --}}
                    <div style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-chart-pie" style="margin-right:6px;"></i>Annual Budget Utilization</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['abyip']['budget_utilization'] ?? '0' }}%</p>
                    </div>

                    {{-- Remaining Balance --}}
                    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-wallet" style="margin-right:6px;"></i>Remaining Balance</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">₱{{ number_format($barangayData['abyip']['remaining_balance'] ?? 0, 2) }}</p>
                    </div>

                    {{-- Project Count --}}
                    <div style="background:linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(139,92,246,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-tasks" style="margin-right:6px;"></i>Project Count</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['abyip']['project_count'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- ABYIP Summary Cards (Mobile) --}}
                <div id="abyipSummaryCardsMobile" style="display:none;"></div>

                {{-- ABYIP Reports Table --}}
                <section class="bm-card" style="margin-bottom:18px;">
                    <div class="bm-card-head" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3><i class="fas fa-file-invoice-dollar" style="color:#213F99;margin-right:6px;"></i>Submitted ABYIP Reports</h3>
                        <div style="display:flex;gap:8px;">
                            <select id="abyipFilterRecent" onchange="filterAbyipRecent()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Reports</option>
                                <option value="recent">Recently Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-table-wrap">
                        <table class="bm-table" id="abyipTable">
                            <thead>
                                <tr>
                                    <th><span class="bm-th-text">Report Name</span></th>
                                    <th><span class="bm-th-text">Date Submitted</span></th>
                                    <th><span class="bm-th-text">Submitted By</span></th>
                                    <th><span class="bm-th-text">Report</span></th>
                                    <th><span class="bm-th-text">Status</span></th>
                                    <th><span class="bm-th-text">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangayData['abyip']['reports'] ?? [] as $report)
                                <tr data-date="{{ strtotime($report['date_submitted'] ?? '') }}" data-report-id="{{ $report['id'] ?? '' }}" data-report-name="{{ $report['name'] ?? '' }}" data-barangay="{{ $barangayData['name'] ?? '' }}" data-type="abyip">
                                    <td style="font-weight:600;color:#0d1b4b;">{{ $report['name'] ?? 'N/A' }}</td>
                                    <td style="font-size:13px;color:#64748b;">{{ $report['date_submitted'] ?? 'N/A' }}</td>
                                    <td style="font-size:13px;color:#64748b;">{{ $report['submitted_by'] ?? 'N/A' }}</td>
                                    <td>
                                        @if(!empty($report['file']))
                                        <a href="{{ $report['file'] }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#213F99;text-decoration:none;background:#eff3ff;padding:4px 10px;border-radius:6px;">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                        @else
                                        <span style="font-size:11px;color:#94a3b8;">No file</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select onchange="updateReportStatus(this, '{{ $report['id'] ?? '' }}', 'abyip', '{{ $barangayData['name'] ?? '' }}')" style="padding:4px 8px;border:1px solid #e2e8f0;border-radius:6px;font-size:11px;color:#475569;background:#fff;cursor:pointer;">
                                            <option value="pending" {{ ($report['status'] ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="complied" {{ ($report['status'] ?? '') === 'complied' ? 'selected' : '' }}>Complied</option>
                                            <option value="rejected" {{ ($report['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button onclick="printReport('{{ $report['name'] ?? '' }}')" style="padding:4px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">No ABYIP reports submitted yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #e2e8f0;flex-wrap:wrap;gap:8px;">
                        <span style="font-size:12px;color:#64748b;white-space:nowrap;">Showing <span id="abyipStart">1</span>-<span id="abyipEnd">5</span> of <span id="abyipTotal">{{ count($barangayData['abyip']['reports'] ?? []) }}</span></span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <button onclick="prevAbyipPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <span id="abyipPageInfo" style="padding:6px 10px;font-size:11px;color:#64748b;white-space:nowrap;">Page 1</span>
                            <button onclick="nextAbyipPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            {{-- ── ACCOMPLISHMENT TAB ── --}}
            <div id="section-accomplishment" style="display:none;">
                {{-- Accomplishment Header --}}
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:24px;font-weight:800;color:#0d1b4b;margin-bottom:4px;"><i class="fas fa-trophy" style="color:#213F99;margin-right:8px;"></i>Program Accomplishment</h2>
                    <p style="font-size:14px;color:#64748b;">Summary of program performance and achievements</p>
                </div>

                {{-- Mobile Dropdown for Accomplishment Summary Cards --}}
                <div id="accomplishmentSummaryDropdownMobile" style="display:none;margin-bottom:20px;">
                    <button onclick="toggleAccomplishmentSummaryDropdown()" style="width:100%;padding:12px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;font-size:14px;font-weight:600;color:#213F99;cursor:pointer;display:flex;align-items:center;justify-content:space-between;">
                        <span><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Summary Cards</span>
                        <i class="fas fa-chevron-down" id="accomplishmentSummaryDropdownIcon"></i>
                    </button>
                    <div id="accomplishmentSummaryDropdownContent" style="display:none;margin-top:8px;"></div>
                </div>

                {{-- Accomplishment Summary Cards (Desktop) --}}
                <div id="accomplishmentSummaryCardsDesktop" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;margin-bottom:24px;">
                    {{-- Total Programs --}}
                    <div style="background:linear-gradient(135deg,#213F99 0%,#1a2f7a 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(33,63,153,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-tasks" style="margin-right:6px;"></i>Total Programs</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['program_stats']['total_programs_created'] ?? 0 }}</p>
                    </div>

                    {{-- Ongoing Programs --}}
                    <div style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(245,158,11,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-spinner" style="margin-right:6px;"></i>Ongoing Programs</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['program_stats']['total_ongoing'] ?? 0 }}</p>
                    </div>

                    {{-- Participation Rate --}}
                    <div style="background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-user-check" style="margin-right:6px;"></i>Participation Rate</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['performance_summary']['attendance_rate'] ?? 0 }}%</p>
                    </div>

                    {{-- Attendance Rate --}}
                    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(16,185,129,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-calendar-check" style="margin-right:6px;"></i>Attendance Rate</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['performance_summary']['attendance_rate'] ?? 0 }}%</p>
                    </div>

                    {{-- Evaluation Rate --}}
                    <div style="background:linear-gradient(135deg,#8b5cf6 0%,#6d28d9 100%);border-radius:12px;padding:20px;color:#fff;box-shadow:0 4px 12px rgba(139,92,246,.15);">
                        <p style="font-size:12px;color:rgba(255,255,255,.8);margin-bottom:8px;"><i class="fas fa-star" style="margin-right:6px;"></i>Evaluation Rate</p>
                        <p style="font-size:28px;font-weight:800;line-height:1;">{{ $barangayData['performance_summary']['completion_rate'] ?? 0 }}%</p>
                    </div>
                </div>

                {{-- Accomplishment Summary Cards (Mobile) --}}
                <div id="accomplishmentSummaryCardsMobile" style="display:none;"></div>

                {{-- Programs Report Table --}}
                <section class="bm-card" style="margin-bottom:18px;">
                    <div class="bm-card-head" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3><i class="fas fa-chart-bar" style="color:#213F99;margin-right:6px;"></i>Programs Report</h3>
                        <div style="display:flex;gap:8px;">
                            <select id="programFilterRecent" onchange="filterProgramRecent()" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;color:#475569;">
                                <option value="all">All Programs</option>
                                <option value="recent">Recently Added</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-table-wrap">
                        <table class="bm-table" id="accomplishmentTable">
                            <thead>
                                <tr>
                                    <th><span class="bm-th-text">Program Name</span></th>
                                    <th><span class="bm-th-text">Date</span></th>
                                    <th><span class="bm-th-text">Registered</span></th>
                                    <th><span class="bm-th-text">Attendance</span></th>
                                    <th><span class="bm-th-text">Evaluation</span></th>
                                    <th><span class="bm-th-text">Report</span></th>
                                    <th><span class="bm-th-text">View Detail</span></th>
                                    <th><span class="bm-th-text">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($barangayData['program_list'] ?? [] as $prog)
                                <tr data-date="{{ strtotime($prog['timeline'] ?? '') }}" data-sector="{{ $prog['sector'] ?? 'N/A' }}" data-description="{{ $prog['description'] ?? 'N/A' }}">
                                    <td style="font-weight:600;color:#0d1b4b;">{{ $prog['title'] ?? 'N/A' }}</td>
                                    <td style="font-size:13px;color:#64748b;">{{ $prog['timeline'] ?? 'N/A' }}</td>
                                    <td style="text-align:center;font-weight:600;">{{ $prog['participants'] ?? 0 }}</td>
                                    <td style="text-align:center;">
                                        <span style="background:#dbeafe;color:#1d4ed8;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">{{ rand(70, 95) }}%</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span style="background:#dcfce7;color:#15803d;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">{{ rand(75, 98) }}%</span>
                                    </td>
                                    <td>
                                        @if(!empty($prog['reports']))
                                            @foreach($prog['reports'] as $report)
                                            <a href="#" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#213F99;text-decoration:none;background:#eff3ff;padding:4px 8px;border-radius:6px;">
                                                <i class="fas fa-file-pdf"></i> View
                                            </a>
                                            @endforeach
                                        @else
                                            <span style="font-size:11px;color:#94a3b8;">No report</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button onclick="viewProgramDetail('{{ $prog['title'] ?? '' }}')" style="padding:4px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                    <td>
                                        <button onclick="printProgram('{{ $prog['title'] ?? '' }}')" style="padding:4px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:20px;color:#94a3b8;">No programs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid #e2e8f0;flex-wrap:wrap;gap:8px;">
                        <span style="font-size:12px;color:#64748b;white-space:nowrap;">Showing <span id="progStart">1</span>-<span id="progEnd">5</span> of <span id="progTotal">{{ count($barangayData['program_list'] ?? []) }}</span></span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <button onclick="prevProgPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <span id="progPageInfo" style="padding:6px 10px;font-size:11px;color:#64748b;white-space:nowrap;">Page 1</span>
                            <button onclick="nextProgPage()" style="padding:6px 10px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:11px;color:#213F99;cursor:pointer;transition:background .2s;white-space:nowrap;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    {{-- ── PROGRAM DETAIL MODAL ── --}}
    <div id="programDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:600px;width:100%;max-height:90vh;overflow-y:auto;animation:slideIn .3s ease-out;">
            {{-- Modal Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:24px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h2 id="modalProgramName" style="font-size:20px;font-weight:800;color:#0d1b4b;margin:0;"></h2>
                <button onclick="closeProgramDetailModal()" style="border:none;background:none;font-size:24px;color:#64748b;cursor:pointer;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                {{-- Program Sector --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#f3e8ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-tag" style="color:#7c3aed;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Program Sector</p>
                        <p id="modalProgramSector" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                {{-- Program Description --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-file-alt" style="color:#d97706;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Description</p>
                        <p id="modalProgramDescription" style="font-size:14px;color:#0d1b4b;margin:0;line-height:1.5;"></p>
                    </div>
                </div>

                {{-- Program Date --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#eff3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-calendar" style="color:#213F99;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Program Date</p>
                        <p id="modalProgramDate" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                {{-- Participants Joined --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-users" style="color:#1d4ed8;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Participants Joined</p>
                        <p id="modalProgramRegistered" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                {{-- Attendance Rate --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-chart-pie" style="color:#15803d;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Attendance Rate</p>
                        <p id="modalProgramAttendance" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>

                {{-- Evaluation Rate --}}
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-star" style="color:#d97706;font-size:18px;"></i>
                    </div>
                    <div>
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;text-transform:uppercase;">Evaluation Rate</p>
                        <p id="modalProgramEvaluation" style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;"></p>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="display:flex;gap:8px;padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;justify-content:flex-end;">
                <button onclick="printProgramDetail()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;font-weight:600;color:#213F99;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-print"></i> Print
                </button>
                <button onclick="closeProgramDetailModal()" style="padding:8px 16px;border:none;background:#213F99;border-radius:6px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#1a2f7a'" onmouseout="this.style.background='#213F99'">
                    <i class="fas fa-check"></i> Close
                </button>
            </div>
        </div>
    </div>

    {{-- Add CSS animation for modal --}}
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    {{-- ── WARNING NOTIFICATION MODAL ── --}}
    <div id="warningModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:500px;width:100%;animation:slideIn .3s ease-out;">
            {{-- Modal Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:24px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <h2 id="warningModalTitle" style="font-size:18px;font-weight:800;color:#0d1b4b;margin:0;"></h2>
                <button onclick="closeWarningModal()" style="border:none;background:none;font-size:24px;color:#64748b;cursor:pointer;padding:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                <input type="hidden" id="warningTypeInput">
                
                {{-- Reason Selection --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#0d1b4b;margin-bottom:8px;">
                        <i class="fas fa-exclamation-circle" style="margin-right:6px;color:#dc2626;"></i>Select Reason for Warning
                    </label>
                    <select id="warningReasonSelect" onchange="handleReasonChange()" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="">-- Choose a reason --</option>
                        <option value="Missing ABYIP Report">Missing ABYIP Report</option>
                        <option value="Missing Accomplishment Report">Missing Accomplishment Report</option>
                        <option value="Delayed Submission">Delayed Submission</option>
                        <option value="Incomplete Documentation">Incomplete Documentation</option>
                        <option value="Low Participation Rate">Low Participation Rate</option>
                        <option value="Budget Mismanagement">Budget Mismanagement</option>
                        <option value="other">Other (Please specify)</option>
                    </select>
                </div>

                {{-- Other Reason Input --}}
                <div id="otherReasonInput" style="display:none;">
                    <input type="text" placeholder="Please specify the reason..." style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;box-sizing:border-box;">
                </div>

                {{-- Additional Message --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#0d1b4b;margin-bottom:8px;">
                        <i class="fas fa-comment" style="margin-right:6px;color:#213F99;"></i>Additional Message (Optional)
                    </label>
                    <textarea id="warningMessage" placeholder="Add any additional details or instructions..." style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;font-family:inherit;resize:vertical;min-height:80px;box-sizing:border-box;"></textarea>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="display:flex;gap:8px;padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;justify-content:flex-end;">
                <button onclick="closeWarningModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;font-weight:600;color:#213F99;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                    Cancel
                </button>
                <button onclick="sendWarning()" style="padding:8px 16px;border:none;background:#dc2626;border-radius:6px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:background .2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                    <i class="fas fa-paper-plane"></i> Send Warning
                </button>
            </div>
        </div>
    </div>

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
            document.getElementById('section-profile').style.display = tab === 'profile' ? 'block' : 'none';
            document.getElementById('section-abyip').style.display = tab === 'abyip' ? 'block' : 'none';
            document.getElementById('section-accomplishment').style.display = tab === 'accomplishment' ? 'block' : 'none';
            const pBtn = document.getElementById('tab-profile');
            const aBtn = document.getElementById('tab-abyip');
            const cBtn = document.getElementById('tab-accomplishment');
            pBtn.style.color       = tab === 'profile' ? '#213F99' : '#64748b';
            pBtn.style.borderColor = tab === 'profile' ? '#213F99' : 'transparent';
            aBtn.style.color       = tab === 'abyip' ? '#213F99' : '#64748b';
            aBtn.style.borderColor = tab === 'abyip' ? '#213F99' : 'transparent';
            cBtn.style.color       = tab === 'accomplishment' ? '#213F99' : '#64748b';
            cBtn.style.borderColor = tab === 'accomplishment' ? '#213F99' : 'transparent';
        }

        function switchMainSection(section) {
            // Hide all main sections
            const profileSection = document.getElementById('section-profile');
            const reportsSection = document.getElementById('section-reports');
            
            if (section === 'reports') {
                if (profileSection) profileSection.style.display = 'none';
                if (reportsSection) reportsSection.style.display = 'block';
            } else {
                if (profileSection) profileSection.style.display = 'block';
                if (reportsSection) reportsSection.style.display = 'none';
                switchTab('profile');
            }
        }

        function filterPrograms() {
            var filter = document.getElementById('programStatusFilter').value;
            var table = document.getElementById('programTable');
            var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                var status = rows[i].getAttribute('data-status');
                rows[i].style.display = (filter === 'all' || status === filter) ? '' : 'none';
            }
        }

        function printReport(reportName) {
            window.print();
        }

        function viewProgramDetail(programName) {
            // Find the program data from the table
            const table = document.getElementById('accomplishmentTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let programData = null;
            
            for (let row of rows) {
                const titleCell = row.getElementsByTagName('td')[0];
                if (titleCell && titleCell.textContent.trim() === programName) {
                    // Extract data from the row
                    const cells = row.getElementsByTagName('td');
                    programData = {
                        title: cells[0].textContent.trim(),
                        date: cells[1].textContent.trim(),
                        registered: cells[2].textContent.trim(),
                        attendance: cells[3].textContent.trim(),
                        evaluation: cells[4].textContent.trim(),
                        sector: row.getAttribute('data-sector') || 'N/A',
                        description: row.getAttribute('data-description') || 'No description available',
                    };
                    break;
                }
            }
            
            if (!programData) {
                alert('Program not found');
                return;
            }
            
            // Populate and show modal
            const modal = document.getElementById('programDetailModal');
            document.getElementById('modalProgramName').textContent = programData.title;
            document.getElementById('modalProgramSector').textContent = programData.sector;
            document.getElementById('modalProgramDescription').textContent = programData.description;
            document.getElementById('modalProgramDate').textContent = programData.date;
            document.getElementById('modalProgramRegistered').textContent = programData.registered + ' participants';
            document.getElementById('modalProgramAttendance').textContent = programData.attendance;
            document.getElementById('modalProgramEvaluation').textContent = programData.evaluation;
            
            modal.style.display = 'flex';
        }
        
        function closeProgramDetailModal() {
            const modal = document.getElementById('programDetailModal');
            modal.style.display = 'none';
        }
        
        function printProgramDetail() {
            window.print();
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('programDetailModal');
            if (event.target === modal) {
                closeProgramDetailModal();
            }
        });

        function printProgram(programName) {
            window.print();
        }

        // ── PAGINATION & FILTER FUNCTIONS ──
        const itemsPerPage = 5;
        let abyipCurrentPage = 1;
        let progCurrentPage = 1;

        function paginateTable(tableId, pageNum, itemsPerPage) {
            const table = document.getElementById(tableId);
            const rows = Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr')).filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            
            if (pageNum < 1) pageNum = 1;
            if (pageNum > totalPages) pageNum = totalPages;
            
            const start = (pageNum - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            return { currentPage: pageNum, totalPages: totalPages, start: start + 1, end: Math.min(end, rows.length), total: rows.length };
        }

        function updateAbyipPagination() {
            const result = paginateTable('abyipTable', abyipCurrentPage, itemsPerPage);
            document.getElementById('abyipStart').textContent = result.total > 0 ? result.start : 0;
            document.getElementById('abyipEnd').textContent = result.end;
            document.getElementById('abyipPageInfo').textContent = `Page ${result.currentPage} of ${result.totalPages}`;
        }

        function updateProgPagination() {
            const result = paginateTable('accomplishmentTable', progCurrentPage, itemsPerPage);
            document.getElementById('progStart').textContent = result.total > 0 ? result.start : 0;
            document.getElementById('progEnd').textContent = result.end;
            document.getElementById('progPageInfo').textContent = `Page ${result.currentPage} of ${result.totalPages}`;
        }

        function nextAbyipPage() {
            abyipCurrentPage++;
            updateAbyipPagination();
        }

        function prevAbyipPage() {
            if (abyipCurrentPage > 1) abyipCurrentPage--;
            updateAbyipPagination();
        }

        function nextProgPage() {
            progCurrentPage++;
            updateProgPagination();
        }

        function prevProgPage() {
            if (progCurrentPage > 1) progCurrentPage--;
            updateProgPagination();
        }

        function filterAbyipRecent() {
            const filter = document.getElementById('abyipFilterRecent').value;
            const table = document.getElementById('abyipTable');
            const rows = Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
            
            if (filter === 'recent') {
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();
                const monthStart = new Date(currentYear, currentMonth, 1).getTime() / 1000;
                const monthEnd = new Date(currentYear, currentMonth + 1, 0, 23, 59, 59).getTime() / 1000;
                
                rows.forEach(row => {
                    const rowDate = parseInt(row.dataset.date) || 0;
                    row.style.display = (rowDate >= monthStart && rowDate <= monthEnd) ? '' : 'none';
                });
                
                rows.sort((a, b) => (parseInt(b.dataset.date) || 0) - (parseInt(a.dataset.date) || 0));
            } else {
                rows.forEach(row => row.style.display = '');
                rows.sort((a, b) => (parseInt(b.dataset.date) || 0) - (parseInt(a.dataset.date) || 0));
            }
            
            rows.forEach(row => table.getElementsByTagName('tbody')[0].appendChild(row));
            abyipCurrentPage = 1;
            updateAbyipPagination();
        }

        function filterProgramRecent() {
            const filter = document.getElementById('programFilterRecent').value;
            const table = document.getElementById('accomplishmentTable');
            const rows = Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'));
            
            if (filter === 'recent') {
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();
                const monthStart = new Date(currentYear, currentMonth, 1).getTime() / 1000;
                const monthEnd = new Date(currentYear, currentMonth + 1, 0, 23, 59, 59).getTime() / 1000;
                
                rows.forEach(row => {
                    const rowDate = parseInt(row.dataset.date) || 0;
                    row.style.display = (rowDate >= monthStart && rowDate <= monthEnd) ? '' : 'none';
                });
                
                rows.sort((a, b) => (parseInt(b.dataset.date) || 0) - (parseInt(a.dataset.date) || 0));
            } else {
                rows.forEach(row => row.style.display = '');
                rows.sort((a, b) => (parseInt(b.dataset.date) || 0) - (parseInt(a.dataset.date) || 0));
            }
            
            rows.forEach(row => table.getElementsByTagName('tbody')[0].appendChild(row));
            progCurrentPage = 1;
            updateProgPagination();
        }

        // Initialize pagination on page load
        window.addEventListener('load', function() {
            updateAbyipPagination();
            updateProgPagination();
        });

        // ── SEARCH FUNCTIONALITY ──
        function performSearch(event) {
            const searchTerm = document.getElementById('navbarSearchInput').value.toLowerCase();
            
            // Get active tab
            const profileTab = document.getElementById('section-profile').style.display !== 'none';
            const abyipTab = document.getElementById('section-abyip').style.display !== 'none';
            const accomplishmentTab = document.getElementById('section-accomplishment').style.display !== 'none';
            
            if (profileTab) {
                searchInProfileTab(searchTerm);
            } else if (abyipTab) {
                searchInAbyipTable(searchTerm);
            } else if (accomplishmentTab) {
                searchInProgramTable(searchTerm);
            }
        }

        function searchInProfileTab(term) {
            // Always show SK Officers and Contact Info cards
            const profileSection = document.getElementById('section-profile');
            const cards = profileSection.querySelectorAll('.bm-card');
            
            cards.forEach((card, index) => {
                // First two cards are SK Officers and Contact Info - always show
                if (index < 2) {
                    card.style.display = 'block';
                } else {
                    // Search only in Posts card - match exact words/letters
                    if (term === '') {
                        card.style.display = 'block';
                    } else {
                        const postItems = card.querySelectorAll('div[style*="border:1px solid"]');
                        let hasMatch = false;
                        
                        postItems.forEach(post => {
                            const title = post.querySelector('h4')?.textContent.toLowerCase() || '';
                            const sector = post.textContent.toLowerCase();
                            
                            if (title.includes(term) || sector.includes(term)) {
                                post.style.display = '';
                                hasMatch = true;
                            } else {
                                post.style.display = 'none';
                            }
                        });
                        
                        card.style.display = hasMatch || postItems.length === 0 ? 'block' : 'block';
                    }
                }
            });
        }

        function searchInAbyipTable(term) {
            const table = document.getElementById('abyipTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;
            
            Array.from(rows).forEach(row => {
                if (term === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    const cells = row.getElementsByTagName('td');
                    let matches = false;
                    
                    // Check each cell for exact match
                    Array.from(cells).forEach(cell => {
                        const text = cell.textContent.toLowerCase();
                        if (text.includes(term)) {
                            matches = true;
                        }
                    });
                    
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                }
            });
            
            abyipCurrentPage = 1;
            updateAbyipPagination();
        }

        function searchInProgramTable(term) {
            const table = document.getElementById('accomplishmentTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;
            
            Array.from(rows).forEach(row => {
                if (term === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    const cells = row.getElementsByTagName('td');
                    let matches = false;
                    
                    // Check each cell for exact match
                    Array.from(cells).forEach(cell => {
                        const text = cell.textContent.toLowerCase();
                        if (text.includes(term)) {
                            matches = true;
                        }
                    });
                    
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                }
            });
            
            progCurrentPage = 1;
            updateProgPagination();
        }

        // Responsive: stack profile grid on small screens
        function adjustProfileGrid() {
            const grid = document.querySelector('#section-profile > div');
            if (!grid) return;
            grid.style.gridTemplateColumns = window.innerWidth < 900 ? '1fr' : '300px 1fr';
        }
        window.addEventListener('resize', adjustProfileGrid);
        adjustProfileGrid();

        // ── MOBILE RESPONSIVENESS ──
        function toggleSummaryDropdown() {
            const content = document.getElementById('summaryDropdownContent');
            const icon = document.getElementById('summaryDropdownIcon');
            const isOpen = content.style.display === 'grid';
            content.style.display = isOpen ? 'none' : 'grid';
            icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function handleMobileResponsiveness() {
            const isMobile = window.innerWidth < 768;
            const desktopCards = document.getElementById('summaryCardsDesktop');
            const mobileDropdown = document.getElementById('summaryDropdownMobile');
            const navbarSearch = document.querySelector('.navbar-search');

            if (isMobile) {
                // Hide desktop summary cards
                desktopCards.style.display = 'none';
                mobileDropdown.style.display = 'block';

                // Populate dropdown with only the 5 summary cards
                const dropdownContent = document.getElementById('summaryDropdownContent');
                dropdownContent.innerHTML = '';
                dropdownContent.style.display = 'none';
                dropdownContent.style.gridTemplateColumns = '1fr';
                dropdownContent.style.gap = '8px';
                
                // Get the 5 summary card divs from desktop
                const summaryCardDivs = desktopCards.querySelectorAll('div[style*="background:linear-gradient"]');
                
                summaryCardDivs.forEach(card => {
                    const clone = card.cloneNode(true);
                    clone.style.padding = '12px';
                    clone.style.fontSize = '12px';
                    
                    // Adjust font sizes for mobile
                    const label = clone.querySelector('p:first-of-type');
                    const value = clone.querySelector('p:last-of-type');
                    
                    if (label) label.style.fontSize = '10px';
                    if (value) value.style.fontSize = '18px';
                    
                    dropdownContent.appendChild(clone);
                });

                // Make search visible on mobile
                if (navbarSearch) {
                    navbarSearch.style.display = 'flex';
                }

                // Make tables scrollable on mobile with full content visible
                const tables = document.querySelectorAll('.bm-table-wrap');
                tables.forEach(table => {
                    table.style.overflowX = 'auto';
                    table.style.webkitOverflowScrolling = 'touch';
                    table.style.display = 'block';
                    table.style.width = '100%';
                });

                // Adjust table styles for mobile - show all columns
                const tableElements = document.querySelectorAll('.bm-table');
                tableElements.forEach(table => {
                    table.style.width = '100%';
                    table.style.minWidth = '600px';
                    table.style.borderCollapse = 'collapse';
                    
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td, th');
                        cells.forEach(cell => {
                            cell.style.display = 'table-cell';
                            cell.style.fontSize = '12px';
                            cell.style.padding = '10px 8px';
                            cell.style.whiteSpace = 'normal';
                            cell.style.wordWrap = 'break-word';
                            cell.style.minWidth = '80px';
                            cell.style.textAlign = 'left';
                        });
                    });

                    // Style headers for mobile
                    const headers = table.querySelectorAll('th');
                    headers.forEach(header => {
                        header.style.fontWeight = '700';
                        header.style.backgroundColor = '#f1f5f9';
                        header.style.color = '#0d1b4b';
                        header.style.fontSize = '12px';
                        header.style.padding = '12px 8px';
                    });
                });
            } else {
                // Show desktop summary cards
                desktopCards.style.display = 'grid';
                mobileDropdown.style.display = 'none';
                
                // Make search visible on desktop
                if (navbarSearch) {
                    navbarSearch.style.display = 'flex';
                }

                // Reset table styles
                const tables = document.querySelectorAll('.bm-table-wrap');
                tables.forEach(table => {
                    table.style.overflowX = 'auto';
                    table.style.display = '';
                    table.style.width = '';
                });

                const tableElements = document.querySelectorAll('.bm-table');
                tableElements.forEach(table => {
                    table.style.width = '';
                    table.style.minWidth = '';
                    
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td, th');
                        cells.forEach(cell => {
                            cell.style.display = '';
                            cell.style.fontSize = '';
                            cell.style.padding = '';
                            cell.style.whiteSpace = '';
                            cell.style.wordWrap = '';
                            cell.style.minWidth = '';
                            cell.style.textAlign = '';
                        });
                    });

                    const headers = table.querySelectorAll('th');
                    headers.forEach(header => {
                        header.style.fontWeight = '';
                        header.style.backgroundColor = '';
                        header.style.color = '';
                        header.style.fontSize = '';
                        header.style.padding = '';
                    });
                });
            }
        }

        // Handle responsiveness on load and resize
        window.addEventListener('load', handleMobileResponsiveness);
        window.addEventListener('resize', handleMobileResponsiveness);

        // ── ABYIP & ACCOMPLISHMENT DROPDOWN FUNCTIONS ──
        function toggleAbyipSummaryDropdown() {
            const content = document.getElementById('abyipSummaryDropdownContent');
            const icon = document.getElementById('abyipSummaryDropdownIcon');
            const isOpen = content.style.display === 'grid';
            content.style.display = isOpen ? 'none' : 'grid';
            icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function toggleAccomplishmentSummaryDropdown() {
            const content = document.getElementById('accomplishmentSummaryDropdownContent');
            const icon = document.getElementById('accomplishmentSummaryDropdownIcon');
            const isOpen = content.style.display === 'grid';
            content.style.display = isOpen ? 'none' : 'grid';
            icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function handleAbyipAccomplishmentResponsiveness() {
            const isMobile = window.innerWidth < 768;

            // ABYIP Summary Cards
            const abyipDesktop = document.getElementById('abyipSummaryCardsDesktop');
            const abyipMobileDropdown = document.getElementById('abyipSummaryDropdownMobile');
            const abyipDropdownContent = document.getElementById('abyipSummaryDropdownContent');

            // Accomplishment Summary Cards
            const accomplishmentDesktop = document.getElementById('accomplishmentSummaryCardsDesktop');
            const accomplishmentMobileDropdown = document.getElementById('accomplishmentSummaryDropdownMobile');
            const accomplishmentDropdownContent = document.getElementById('accomplishmentSummaryDropdownContent');

            if (isMobile) {
                // Hide desktop, show mobile dropdowns
                abyipDesktop.style.display = 'none';
                abyipMobileDropdown.style.display = 'block';
                accomplishmentDesktop.style.display = 'none';
                accomplishmentMobileDropdown.style.display = 'block';

                // Populate ABYIP dropdown
                abyipDropdownContent.innerHTML = '';
                abyipDropdownContent.style.gridTemplateColumns = '1fr';
                abyipDropdownContent.style.gap = '8px';
                
                const abyipCards = abyipDesktop.querySelectorAll('div[style*="background:linear-gradient"]');
                abyipCards.forEach(card => {
                    const clone = card.cloneNode(true);
                    clone.style.padding = '12px';
                    const label = clone.querySelector('p:first-of-type');
                    const value = clone.querySelector('p:last-of-type');
                    if (label) label.style.fontSize = '10px';
                    if (value) value.style.fontSize = '18px';
                    abyipDropdownContent.appendChild(clone);
                });

                // Populate Accomplishment dropdown
                accomplishmentDropdownContent.innerHTML = '';
                accomplishmentDropdownContent.style.gridTemplateColumns = '1fr';
                accomplishmentDropdownContent.style.gap = '8px';
                
                const accomplishmentCards = accomplishmentDesktop.querySelectorAll('div[style*="background:linear-gradient"]');
                accomplishmentCards.forEach(card => {
                    const clone = card.cloneNode(true);
                    clone.style.padding = '12px';
                    const label = clone.querySelector('p:first-of-type');
                    const value = clone.querySelector('p:last-of-type');
                    if (label) label.style.fontSize = '10px';
                    if (value) value.style.fontSize = '18px';
                    accomplishmentDropdownContent.appendChild(clone);
                });
            } else {
                // Show desktop, hide mobile dropdowns
                abyipDesktop.style.display = 'grid';
                abyipMobileDropdown.style.display = 'none';
                accomplishmentDesktop.style.display = 'grid';
                accomplishmentMobileDropdown.style.display = 'none';
            }
        }

        // Handle ABYIP & Accomplishment responsiveness on load and resize
        window.addEventListener('load', handleAbyipAccomplishmentResponsiveness);
        window.addEventListener('resize', handleAbyipAccomplishmentResponsiveness);

        // ── WARNING NOTIFICATION SYSTEM ──
        function openWarningModal(warningType, warningTitle) {
            const modal = document.getElementById('warningModal');
            const reasonSelect = document.getElementById('warningReasonSelect');
            const otherReasonInput = document.getElementById('otherReasonInput');
            
            // Set warning type and title
            document.getElementById('warningModalTitle').textContent = warningTitle;
            document.getElementById('warningTypeInput').value = warningType;
            
            // Reset form
            reasonSelect.value = '';
            otherReasonInput.style.display = 'none';
            otherReasonInput.value = '';
            document.getElementById('warningMessage').value = '';
            
            modal.style.display = 'flex';
        }

        function closeWarningModal() {
            const modal = document.getElementById('warningModal');
            modal.style.display = 'none';
        }

        function handleReasonChange() {
            const reasonSelect = document.getElementById('warningReasonSelect');
            const otherReasonInput = document.getElementById('otherReasonInput');
            
            if (reasonSelect.value === 'other') {
                otherReasonInput.style.display = 'block';
                otherReasonInput.focus();
            } else {
                otherReasonInput.style.display = 'none';
            }
        }

        function sendWarning() {
            const barangayName = '{{ $barangayData['name'] }}';
            const warningType = document.getElementById('warningTypeInput').value;
            const reason = document.getElementById('warningReasonSelect').value;
            const otherReason = document.getElementById('otherReasonInput').value;
            const message = document.getElementById('warningMessage').value;
            
            if (!reason) {
                alert('Please select a reason');
                return;
            }
            
            if (reason === 'other' && !otherReason.trim()) {
                alert('Please specify the reason');
                return;
            }
            
            const finalReason = reason === 'other' ? otherReason : reason;
            
            // Show loading
            LoadingScreen.show('Sending Warning', 'Please wait...');
            
            // Simulate sending warning (in real implementation, this would be an API call)
            setTimeout(() => {
                LoadingScreen.hide();
                
                // Show success message
                alert(`Warning sent to ${barangayName}\n\nReason: ${finalReason}\n\nMessage: ${message || 'No additional message'}`);
                
                closeWarningModal();
            }, 1500);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('warningModal');
            if (event.target === modal) {
                closeWarningModal();
            }
        });
    </script>
</body>
</html>
