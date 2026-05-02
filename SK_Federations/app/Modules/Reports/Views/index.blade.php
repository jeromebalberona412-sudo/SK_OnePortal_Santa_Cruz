<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports - SK Federations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/reports/css/reports.css') }}">
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
            <input type="text" id="reportsSearchInput" placeholder="Search..." onkeyup="performReportsSearch()" aria-label="Search reports">
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
                    <a href="{{ route('profile') }}" class="dd-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="{{ route('password.request') }}" class="dd-item">
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

    <div class="sidebar-overlay"></div>

    <aside class="sidebar">
        <a href="{{ route('profile') }}" class="sidebar-profile">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>
        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed">
                <i class="fas fa-rss"></i><span>SK Community Feed</span>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring">
                <i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span>
            </a>
            <a href="{{ route('reports') }}" class="menu-item active" data-tooltip="Reports">
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
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </aside>

    <main class="main-content">
        <div class="reports-container">
            {{-- Reports Header with Filter --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
                <div>
                    <h2 style="font-size:28px;font-weight:800;color:#0d1b4b;margin-bottom:4px;">Reports</h2>
                    <p style="font-size:14px;color:#64748b;"><i class="fas fa-file-chart-line" style="margin-right:6px;color:#213F99;"></i>Barangay compliance and submission reports overview</p>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;margin-bottom:24px;" id="summaryCardsDesktop">
                {{-- Barangay Compliance Card (Left) --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:0;">Barangay Compliance</h3>
                        <i class="fas fa-info-circle" style="color:#94a3b8;cursor:help;"></i>
                    </div>
                    <div style="position:relative;width:180px;height:180px;margin:0 auto;">
                        <svg viewBox="0 0 180 180" style="width:100%;height:100%;transform:rotate(-90deg);">
                            <!-- Background circle -->
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#e2e8f0" stroke-width="20"/>
                            <!-- Compliant (75%) - Green -->
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#10b981" stroke-width="20" stroke-dasharray="330 440" stroke-linecap="round"/>
                            <!-- Partial (15%) - Yellow -->
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#f59e0b" stroke-width="20" stroke-dasharray="66 440" stroke-dashoffset="-330" stroke-linecap="round"/>
                            <!-- Non-compliant (10%) - Red -->
                            <circle cx="90" cy="90" r="70" fill="none" stroke="#ef4444" stroke-width="20" stroke-dasharray="44 440" stroke-dashoffset="-396" stroke-linecap="round"/>
                        </svg>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                            <p style="font-size:32px;font-weight:800;color:#0d1b4b;margin:0;">{{ $reportsData['summary']['total_barangays'] }}</p>
                            <p style="font-size:11px;color:#94a3b8;margin:4px 0 0 0;font-weight:600;">TOTAL LGU</p>
                        </div>
                    </div>
                    <div style="margin-top:24px;display:flex;flex-direction:column;gap:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:12px;height:12px;background:#10b981;border-radius:2px;"></div>
                            <span style="font-size:13px;color:#0d1b4b;flex:1;">Compliant</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['compliant'] }}%</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:12px;height:12px;background:#f59e0b;border-radius:2px;"></div>
                            <span style="font-size:13px;color:#0d1b4b;flex:1;">Partially Compliant</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['partial'] }}%</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:12px;height:12px;background:#ef4444;border-radius:2px;"></div>
                            <span style="font-size:13px;color:#0d1b4b;flex:1;">Non-compliant</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['non_compliant'] }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Submission Rates Card (Right) --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <h3 style="font-size:16px;font-weight:700;color:#0d1b4b;margin:0;">Submission Rates</h3>
                        <div style="background:#e0e7ff;color:#213F99;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;">Quarterly Cycle: Q2 2026</div>
                    </div>
                    
                    {{-- Accomplishment Reports --}}
                    <div style="margin-bottom:24px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:600;color:#0d1b4b;">Accomplishment Reports</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['accomplishment_rate'] }}%</span>
                        </div>
                        <div style="width:100%;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                            <div style="width:{{ $reportsData['summary']['accomplishment_rate'] }}%;height:100%;background:#3b82f6;border-radius:4px;"></div>
                        </div>
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">{{ $reportsData['summary']['accomplishment_submitted'] }} submitted, {{ $reportsData['summary']['accomplishment_not_submitted'] }} not submitted</div>
                    </div>

                    {{-- ABYIP Reports --}}
                    <div style="margin-bottom:24px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:600;color:#0d1b4b;">ABYIP (Annual Barangay Youth Investment Plan)</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['abyip_rate'] }}%</span>
                        </div>
                        <div style="width:100%;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                            <div style="width:{{ $reportsData['summary']['abyip_rate'] }}%;height:100%;background:#3b82f6;border-radius:4px;"></div>
                        </div>
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">{{ $reportsData['summary']['abyip_submitted'] }} submitted, {{ $reportsData['summary']['abyip_not_submitted'] }} not submitted</div>
                    </div>

                    {{-- KK Profiling --}}
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:600;color:#0d1b4b;">KK Profiling</span>
                            <span style="font-size:13px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['kk_profiling_rate'] }}%</span>
                        </div>
                        <div style="width:100%;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                            <div style="width:{{ $reportsData['summary']['kk_profiling_rate'] }}%;height:100%;background:#3b82f6;border-radius:4px;"></div>
                        </div>
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">{{ $reportsData['summary']['kk_profiling_submitted'] }} submitted, {{ $reportsData['summary']['kk_profiling_not_submitted'] }} not submitted</div>
                    </div>
                </div>
            </div>

            {{-- Mobile Summary Dropdowns --}}
            <div style="display:none;" id="summaryCardsMobile">
                {{-- Combined Summary Dropdown --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:12px;">
                    <button onclick="toggleMobileSummary('all')" style="width:100%;padding:16px;border:none;background:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-weight:600;color:#0d1b4b;">
                        <span><i class="fas fa-chart-bar" style="margin-right:8px;"></i>Summary Overview</span>
                        <i class="fas fa-chevron-down" id="allChevron"></i>
                    </button>
                    <div id="allContent" style="display:none;padding:0 16px 16px 16px;border-top:1px solid #e2e8f0;">
                        {{-- Compliance Section --}}
                        <div style="margin-bottom:20px;">
                            <h4 style="font-size:12px;font-weight:700;color:#0d1b4b;margin:0 0 12px 0;text-transform:uppercase;letter-spacing:0.5px;">Barangay Compliance</h4>
                            <div style="display:flex;flex-direction:column;gap:10px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:12px;height:12px;background:#10b981;border-radius:2px;"></div>
                                    <span style="font-size:12px;color:#0d1b4b;flex:1;">Compliant</span>
                                    <span style="font-size:12px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['compliant'] }}%</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:12px;height:12px;background:#f59e0b;border-radius:2px;"></div>
                                    <span style="font-size:12px;color:#0d1b4b;flex:1;">Partially Compliant</span>
                                    <span style="font-size:12px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['partial'] }}%</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:12px;height:12px;background:#ef4444;border-radius:2px;"></div>
                                    <span style="font-size:12px;color:#0d1b4b;flex:1;">Non-compliant</span>
                                    <span style="font-size:12px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['non_compliant'] }}%</span>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div style="height:1px;background:#e2e8f0;margin:16px 0;"></div>

                        {{-- Submission Rates Section --}}
                        <div>
                            <h4 style="font-size:12px;font-weight:700;color:#0d1b4b;margin:0 0 12px 0;text-transform:uppercase;letter-spacing:0.5px;">Submission Rates</h4>
                            <div style="margin-bottom:14px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <span style="font-size:11px;font-weight:600;color:#0d1b4b;">Accomplishment</span>
                                    <span style="font-size:11px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['accomplishment_rate'] }}%</span>
                                </div>
                                <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                    <div style="width:{{ $reportsData['summary']['accomplishment_rate'] }}%;height:100%;background:#3b82f6;border-radius:3px;"></div>
                                </div>
                                <div style="font-size:10px;color:#64748b;margin-top:3px;">{{ $reportsData['summary']['accomplishment_submitted'] }} submitted, {{ $reportsData['summary']['accomplishment_not_submitted'] }} not</div>
                            </div>
                            <div style="margin-bottom:14px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <span style="font-size:11px;font-weight:600;color:#0d1b4b;">ABYIP</span>
                                    <span style="font-size:11px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['abyip_rate'] }}%</span>
                                </div>
                                <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                    <div style="width:{{ $reportsData['summary']['abyip_rate'] }}%;height:100%;background:#3b82f6;border-radius:3px;"></div>
                                </div>
                                <div style="font-size:10px;color:#64748b;margin-top:3px;">{{ $reportsData['summary']['abyip_submitted'] }} submitted, {{ $reportsData['summary']['abyip_not_submitted'] }} not</div>
                            </div>
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <span style="font-size:11px;font-weight:600;color:#0d1b4b;">KK Profiling</span>
                                    <span style="font-size:11px;font-weight:700;color:#0d1b4b;">{{ $reportsData['summary']['kk_profiling_rate'] }}%</span>
                                </div>
                                <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                    <div style="width:{{ $reportsData['summary']['kk_profiling_rate'] }}%;height:100%;background:#3b82f6;border-radius:3px;"></div>
                                </div>
                                <div style="font-size:10px;color:#64748b;margin-top:3px;">{{ $reportsData['summary']['kk_profiling_submitted'] }} submitted, {{ $reportsData['summary']['kk_profiling_not_submitted'] }} not</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                @media (max-width: 768px) {
                    #summaryCardsDesktop {
                        display: none !important;
                    }
                    #summaryCardsMobile {
                        display: block !important;
                    }
                }
            </style>

            {{-- Reports Tabs with Filters --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:2px solid #e2e8f0;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;gap:4px;">
                    <button onclick="switchReportsTab('accomplishment')" id="reports-tab-accomplishment" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#213F99;border-bottom:2px solid #213F99;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-trophy"></i> Accomplishment
                    </button>
                    <button onclick="switchReportsTab('abyip')" id="reports-tab-abyip" style="padding:10px 20px;border:none;background:none;font-size:14px;font-weight:600;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;">
                        <i class="fas fa-file-invoice-dollar"></i> ABYIP
                    </button>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <select id="tabTimeFilter" onchange="filterTabReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;white-space:nowrap;">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                    <select id="tabStatusFilter" onchange="filterTabReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;white-space:nowrap;">
                        <option value="all">All Status</option>
                        <option value="compliant">Compliant</option>
                        <option value="partial">Partial</option>
                        <option value="rejected">Rejected</option>
                        <option value="non-compliant">Non-Compliant</option>
                    </select>
                    <select id="tabBarangayFilter" onchange="filterTabReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;white-space:nowrap;">
                        <option value="all">All Barangays</option>
                        <option value="Alipit">Alipit</option>
                        <option value="Bagumbayan">Bagumbayan</option>
                        <option value="Bubukal">Bubukal</option>
                        <option value="Calios">Calios</option>
                        <option value="Duhat">Duhat</option>
                        <option value="Gatid">Gatid</option>
                        <option value="Jasaan">Jasaan</option>
                        <option value="Labuin">Labuin</option>
                        <option value="Malinao">Malinao</option>
                        <option value="Oogong">Oogong</option>
                        <option value="Pagsawitan">Pagsawitan</option>
                        <option value="Palasan">Palasan</option>
                        <option value="Patimbao">Patimbao</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Poblacion V">Poblacion V</option>
                        <option value="San Jose">San Jose</option>
                        <option value="San Juan">San Juan</option>
                        <option value="San Pablo Norte">San Pablo Norte</option>
                        <option value="San Pablo Sur">San Pablo Sur</option>
                        <option value="Santisima Cruz">Santisima Cruz</option>
                        <option value="Santo Angel Central">Santo Angel Central</option>
                        <option value="Santo Angel Norte">Santo Angel Norte</option>
                        <option value="Santo Angel Sur">Santo Angel Sur</option>
                    </select>
                </div>
            </div>

            {{-- Accomplishment Reports Tab --}}
            <div id="reports-accomplishment" style="display:block;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Program Name</th>
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Barangay</th>
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Date Registered</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Attendance</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Evaluation</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Report</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Status</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="accomplishmentTableBody">
                            @foreach($reportsData['accomplishment_reports'] as $report)
                            <tr class="accomplishment-row" data-id="{{ $report['id'] }}" data-status="{{ $report['status'] }}" data-barangay="{{ $report['barangay'] }}" data-program="{{ strtolower($report['program_name']) }}" data-program-name="{{ $report['program_name'] }}" data-attendance="{{ $report['attendance'] }}" data-evaluation="{{ $report['evaluation'] }}" data-date="{{ $report['date_registered'] }}" data-rejection-reason="{{ $report['rejection_reason'] }}" style="border-bottom:1px solid #e2e8f0;">
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['program_name'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['barangay'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['date_registered'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;text-align:center;border-right:1px solid #e2e8f0;">{{ $report['attendance'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;text-align:center;border-right:1px solid #e2e8f0;">{{ $report['evaluation'] }}/5</td>
                                <td style="padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                                    <a href="#" style="color:#213F99;text-decoration:none;font-size:12px;font-weight:600;">
                                        <i class="fas fa-file-pdf" style="margin-right:4px;"></i>{{ $report['report'] }}
                                    </a>
                                </td>
                                <td style="padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:4px 8px;border-radius:6px;
                                    @if($report['status'] === 'compliant')
                                        background:#dcfce7;color:#15803d;
                                    @elseif($report['status'] === 'partial')
                                        background:#fef3c7;color:#b45309;
                                    @elseif($report['status'] === 'rejected')
                                        background:#fee2e2;color:#dc2626;
                                    @else
                                        background:#fee2e2;color:#dc2626;
                                    @endif
                                    ">
                                        <i class="fas fa-circle" style="font-size:6px;"></i>
                                        {{ ucfirst($report['status']) }}
                                    </span>
                                </td>
                                <td style="padding:12px 16px;text-align:center;">
                                    <button onclick="viewAccomplishmentDetail({{ $report['id'] }})" style="background:none;border:none;color:#213F99;cursor:pointer;font-size:13px;margin-right:8px;" title="View Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editAccomplishmentStatus({{ $report['id'] }})" style="background:none;border:none;color:#8b5cf6;cursor:pointer;font-size:13px;" title="Edit Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;">
                    <div style="font-size:12px;color:#64748b;">
                        Showing <span id="accomplishmentStart">1</span> to <span id="accomplishmentEnd">10</span> of <span id="accomplishmentTotal">{{ count($reportsData['accomplishment_reports']) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="prevAccomplishmentPage()" style="padding:6px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:12px;color:#475569;font-weight:600;transition:all 0.2s;">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextAccomplishmentPage()" style="padding:6px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:12px;color:#475569;font-weight:600;transition:all 0.2s;">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ABYIP Reports Tab --}}
            <div id="reports-abyip" style="display:none;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Report Name</th>
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Barangay</th>
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Date Submitted</th>
                                <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Submitted By</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Report (PDF)</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;border-right:1px solid #e2e8f0;">Status</th>
                                <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="abyipTableBody">
                            @foreach($reportsData['abyip_reports'] as $report)
                            <tr class="abyip-row" data-id="{{ $report['id'] }}" data-status="{{ $report['status'] }}" data-barangay="{{ $report['barangay'] }}" data-report="{{ strtolower($report['report_name']) }}" data-report-name="{{ $report['report_name'] }}" data-submitted-by="{{ $report['submitted_by'] }}" data-date="{{ $report['date_submitted'] }}" data-rejection-reason="{{ $report['rejection_reason'] }}" style="border-bottom:1px solid #e2e8f0;">
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['report_name'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['barangay'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['date_submitted'] }}</td>
                                <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;border-right:1px solid #e2e8f0;">{{ $report['submitted_by'] }}</td>
                                <td style="padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                                    <a href="#" style="color:#213F99;text-decoration:none;font-size:12px;font-weight:600;">
                                        <i class="fas fa-file-pdf" style="margin-right:4px;"></i>{{ $report['report_file'] }}
                                    </a>
                                </td>
                                <td style="padding:12px 16px;text-align:center;border-right:1px solid #e2e8f0;">
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:4px 8px;border-radius:6px;
                                    @if($report['status'] === 'compliant')
                                        background:#dcfce7;color:#15803d;
                                    @elseif($report['status'] === 'partial')
                                        background:#fef3c7;color:#b45309;
                                    @elseif($report['status'] === 'rejected')
                                        background:#fee2e2;color:#dc2626;
                                    @else
                                        background:#fee2e2;color:#dc2626;
                                    @endif
                                    ">
                                        <i class="fas fa-circle" style="font-size:6px;"></i>
                                        {{ ucfirst($report['status']) }}
                                    </span>
                                </td>
                                <td style="padding:12px 16px;text-align:center;">
                                    <button onclick="editAbyipStatus({{ $report['id'] }})" style="background:none;border:none;color:#8b5cf6;cursor:pointer;font-size:13px;" title="Edit Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;">
                    <div style="font-size:12px;color:#64748b;">
                        Showing <span id="abyipStart">1</span> to <span id="abyipEnd">10</span> of <span id="abyipTotal">{{ count($reportsData['abyip_reports']) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="prevAbyipPage()" style="padding:6px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:12px;color:#475569;font-weight:600;transition:all 0.2s;">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextAbyipPage()" style="padding:6px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:12px;color:#475569;font-weight:600;transition:all 0.2s;">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('dashboard::logout-modal')

    {{-- Validation Modal for Edit Action --}}
    <div id="editValidationModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:450px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
            <div style="padding:24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-exclamation" style="color:#b45309;font-size:20px;"></i>
                </div>
                <h3 style="margin:0;font-size:18px;font-weight:700;color:#0d1b4b;">Confirm Changes</h3>
            </div>
            <div style="padding:24px;">
                <p style="font-size:14px;color:#475569;margin:0 0 16px 0;line-height:1.6;">Are you sure you want to update the status? This action will change the report status and cannot be easily undone.</p>
                <div style="background:#f8fafc;border-left:4px solid #213F99;padding:12px;border-radius:4px;margin-bottom:16px;">
                    <p style="font-size:12px;color:#64748b;margin:0;"><strong>Report:</strong> <span id="validationReportName" style="color:#0d1b4b;font-weight:600;"></span></p>
                    <p style="font-size:12px;color:#64748b;margin:8px 0 0 0;"><strong>New Status:</strong> <span id="validationNewStatus" style="color:#0d1b4b;font-weight:600;"></span></p>
                </div>
            </div>
            <div style="padding:20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                <button onclick="cancelEditValidation()" style="padding:10px 20px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;color:#475569;font-weight:600;transition:all 0.2s;">
                    Cancel
                </button>
                <button onclick="confirmEditValidation()" style="padding:10px 20px;border:none;background:#213F99;border-radius:6px;cursor:pointer;font-size:13px;color:#fff;font-weight:600;transition:all 0.2s;">
                    <i class="fas fa-check" style="margin-right:6px;"></i>Confirm
                </button>
            </div>
        </div>
    </div>

    {{-- View Accomplishment Detail Modal --}}
    <div id="viewAccomplishmentModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:500px;max-height:80vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);">
            <div style="padding:20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:18px;font-weight:700;color:#0d1b4b;">Program Details</h3>
                <button onclick="closeViewAccomplishmentModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:20px;">
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Program Name</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="modalProgramName"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Barangay</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="modalBarangay"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Date Registered</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="modalDate"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Attendance</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="modalAttendance"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Evaluation</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="modalEvaluation"></p>
                </div>
            </div>
            <div style="padding:20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                <button onclick="closeViewAccomplishmentModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;color:#475569;font-weight:600;">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- Edit Accomplishment Status Modal --}}
    <div id="editAccomplishmentModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
            <div style="padding:20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:18px;font-weight:700;color:#0d1b4b;">Edit Status</h3>
                <button onclick="closeEditAccomplishmentModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:20px;">
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Program Name</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="editModalProgramName"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Status</label>
                    <select id="editStatusSelect" onchange="handleAccomplishmentStatusChange()" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;">
                        <option value="compliant">Compliant</option>
                        <option value="partial">Partial</option>
                        <option value="rejected">Rejected</option>
                        <option value="non-compliant">Non-Compliant</option>
                    </select>
                </div>
                <div id="accomplishmentRejectionReasonDiv" style="margin-bottom:16px;display:none;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Reason for Rejection</label>
                    <select id="accomplishmentRejectionReason" onchange="handleAccomplishmentReasonChange()" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;">
                        <option value="">Select a reason</option>
                        <option value="Missing Attachment">Missing Attachment</option>
                        <option value="Invalid File Format">Invalid File Format</option>
                        <option value="Corrupted File">Corrupted File</option>
                        <option value="Unreadable Document">Unreadable Document</option>
                        <option value="Others">Other reason (type)</option>
                    </select>
                </div>
                <div id="accomplishmentOtherReasonDiv" style="margin-bottom:16px;display:none;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Please specify the reason</label>
                    <input type="text" id="accomplishmentOtherReasonText" placeholder="Enter your reason..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;box-sizing:border-box;">
                </div>
            </div>
            <div style="padding:20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                <button onclick="closeEditAccomplishmentModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;color:#475569;font-weight:600;">
                    Cancel
                </button>
                <button onclick="saveAccomplishmentStatus()" style="padding:8px 16px;border:none;background:#213F99;border-radius:6px;cursor:pointer;font-size:13px;color:#fff;font-weight:600;">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    {{-- Edit ABYIP Status Modal --}}
    <div id="editAbyipModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
            <div style="padding:20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:18px;font-weight:700;color:#0d1b4b;">Edit Status</h3>
                <button onclick="closeEditAbyipModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="padding:20px;">
                <div style="margin-bottom:16px;">
                    <p style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;">Report Name</p>
                    <p style="font-size:14px;color:#0d1b4b;margin:0;font-weight:600;" id="editAbyipModalReportName"></p>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Status</label>
                    <select id="editAbyipStatusSelect" onchange="handleAbyipStatusChange()" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;">
                        <option value="compliant">Compliant</option>
                        <option value="partial">Partial</option>
                        <option value="rejected">Rejected</option>
                        <option value="non-compliant">Non-Compliant</option>
                    </select>
                </div>
                <div id="abyipRejectionReasonDiv" style="margin-bottom:16px;display:none;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Reason for Rejection</label>
                    <select id="abyipRejectionReason" onchange="handleAbyipReasonChange()" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;">
                        <option value="">Select a reason</option>
                        <option value="Missing Attachment">Missing Attachment</option>
                        <option value="Invalid File Format">Invalid File Format</option>
                        <option value="Corrupted File">Corrupted File</option>
                        <option value="Unreadable Document">Unreadable Document</option>
                        <option value="Others">Other reason (type)</option>
                    </select>
                </div>
                <div id="abyipOtherReasonDiv" style="margin-bottom:16px;display:none;">
                    <label style="font-size:11px;color:#94a3b8;margin:0 0 4px 0;font-weight:600;display:block;">Please specify the reason</label>
                    <input type="text" id="abyipOtherReasonText" placeholder="Enter your reason..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;color:#475569;box-sizing:border-box;">
                </div>
            </div>
            <div style="padding:20px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                <button onclick="closeEditAbyipModal()" style="padding:8px 16px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;font-size:13px;color:#475569;font-weight:600;">
                    Cancel
                </button>
                <button onclick="saveAbyipStatus()" style="padding:8px 16px;border:none;background:#213F99;border-radius:6px;cursor:pointer;font-size:13px;color:#fff;font-weight:600;">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script>
        window.logoutRoute = "{{ route('logout') }}";
        window.loginRoute  = "{{ route('login') }}";

        const itemsPerPage = 10;
        let accomplishmentCurrentPage = 1;
        let abyipCurrentPage = 1;
        let currentEditAccomplishmentId = null;
        let currentEditAbyipId = null;
        let pendingValidation = null; // Store pending validation data

        function switchReportsTab(tab) {
            document.getElementById('reports-accomplishment').style.display = tab === 'accomplishment' ? 'block' : 'none';
            document.getElementById('reports-abyip').style.display = tab === 'abyip' ? 'block' : 'none';
            const accBtn = document.getElementById('reports-tab-accomplishment');
            const abyipBtn = document.getElementById('reports-tab-abyip');
            accBtn.style.color = tab === 'accomplishment' ? '#213F99' : '#64748b';
            accBtn.style.borderColor = tab === 'accomplishment' ? '#213F99' : 'transparent';
            abyipBtn.style.color = tab === 'abyip' ? '#213F99' : '#64748b';
            abyipBtn.style.borderColor = tab === 'abyip' ? '#213F99' : 'transparent';
            document.getElementById('tabTimeFilter').value = 'all';
            document.getElementById('tabStatusFilter').value = 'all';
            document.getElementById('tabBarangayFilter').value = 'all';
            document.getElementById('reportsSearchInput').value = '';
            accomplishmentCurrentPage = 1;
            abyipCurrentPage = 1;
            filterTabReports();
        }

        function filterReportsByTime() {
            const timeFilter = document.getElementById('reportsTimeFilter').value;
            console.log('Filtering reports by time:', timeFilter);
        }

        function getVisibleRows(selector) {
            const rows = document.querySelectorAll(selector);
            return Array.from(rows).filter(row => row.style.display !== 'none');
        }

        function displayAccomplishmentPage() {
            const visibleRows = getVisibleRows('.accomplishment-row');
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (accomplishmentCurrentPage > totalPages && totalPages > 0) accomplishmentCurrentPage = totalPages;
            if (accomplishmentCurrentPage < 1) accomplishmentCurrentPage = 1;

            const allRows = document.querySelectorAll('.accomplishment-row');
            allRows.forEach(row => row.style.display = 'none');

            visibleRows.forEach((row, index) => {
                const pageStart = (accomplishmentCurrentPage - 1) * itemsPerPage;
                const pageEnd = pageStart + itemsPerPage;
                if (index >= pageStart && index < pageEnd) {
                    row.style.display = '';
                }
            });

            const pageStart = (accomplishmentCurrentPage - 1) * itemsPerPage + 1;
            const pageEnd = Math.min(accomplishmentCurrentPage * itemsPerPage, visibleRows.length);
            document.getElementById('accomplishmentStart').textContent = visibleRows.length > 0 ? pageStart : 0;
            document.getElementById('accomplishmentEnd').textContent = pageEnd;
            
            // Disable/enable buttons
            const prevBtn = document.querySelector('[onclick="prevAccomplishmentPage()"]');
            const nextBtn = document.querySelector('[onclick="nextAccomplishmentPage()"]');
            if (prevBtn) prevBtn.disabled = accomplishmentCurrentPage === 1;
            if (nextBtn) nextBtn.disabled = accomplishmentCurrentPage >= totalPages;
        }

        function displayAbyipPage() {
            const visibleRows = getVisibleRows('.abyip-row');
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (abyipCurrentPage > totalPages && totalPages > 0) abyipCurrentPage = totalPages;
            if (abyipCurrentPage < 1) abyipCurrentPage = 1;

            const allRows = document.querySelectorAll('.abyip-row');
            allRows.forEach(row => row.style.display = 'none');

            visibleRows.forEach((row, index) => {
                const pageStart = (abyipCurrentPage - 1) * itemsPerPage;
                const pageEnd = pageStart + itemsPerPage;
                if (index >= pageStart && index < pageEnd) {
                    row.style.display = '';
                }
            });

            const pageStart = (abyipCurrentPage - 1) * itemsPerPage + 1;
            const pageEnd = Math.min(abyipCurrentPage * itemsPerPage, visibleRows.length);
            document.getElementById('abyipStart').textContent = visibleRows.length > 0 ? pageStart : 0;
            document.getElementById('abyipEnd').textContent = pageEnd;
            
            // Disable/enable buttons
            const prevBtn = document.querySelectorAll('[onclick="prevAbyipPage()"]')[0];
            const nextBtn = document.querySelectorAll('[onclick="nextAbyipPage()"]')[0];
            if (prevBtn) prevBtn.disabled = abyipCurrentPage === 1;
            if (nextBtn) nextBtn.disabled = abyipCurrentPage >= totalPages;
        }

        function nextAccomplishmentPage() {
            const visibleRows = getVisibleRows('.accomplishment-row');
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (accomplishmentCurrentPage < totalPages) {
                accomplishmentCurrentPage++;
                displayAccomplishmentPage();
            }
        }

        function prevAccomplishmentPage() {
            if (accomplishmentCurrentPage > 1) {
                accomplishmentCurrentPage--;
                displayAccomplishmentPage();
            }
        }

        function nextAbyipPage() {
            const visibleRows = getVisibleRows('.abyip-row');
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (abyipCurrentPage < totalPages) {
                abyipCurrentPage++;
                displayAbyipPage();
            }
        }

        function prevAbyipPage() {
            if (abyipCurrentPage > 1) {
                abyipCurrentPage--;
                displayAbyipPage();
            }
        }

        function filterTabReports() {
            const timeFilter = document.getElementById('tabTimeFilter').value;
            const statusFilter = document.getElementById('tabStatusFilter').value;
            const barangayFilter = document.getElementById('tabBarangayFilter').value;
            const searchTerm = document.getElementById('reportsSearchInput').value.toLowerCase();
            const activeTab = document.getElementById('reports-accomplishment').style.display === 'block' ? 'accomplishment' : 'abyip';
            
            if (activeTab === 'accomplishment') {
                const rows = document.querySelectorAll('.accomplishment-row');
                rows.forEach(row => {
                    const rowStatus = row.getAttribute('data-status');
                    const rowBarangay = row.getAttribute('data-barangay');
                    const rowProgram = row.getAttribute('data-program');
                    const rowDate = row.getAttribute('data-date');
                    
                    const statusMatch = (statusFilter === 'all' || rowStatus === statusFilter);
                    const barangayMatch = (barangayFilter === 'all' || rowBarangay === barangayFilter);
                    const searchMatch = rowProgram.includes(searchTerm) || rowBarangay.toLowerCase().includes(searchTerm);
                    const timeMatch = isDateInTimeRange(rowDate, timeFilter);
                    
                    row.style.display = (statusMatch && barangayMatch && searchMatch && timeMatch) ? '' : 'none';
                });
                accomplishmentCurrentPage = 1;
                displayAccomplishmentPage();
            } else {
                const rows = document.querySelectorAll('.abyip-row');
                rows.forEach(row => {
                    const rowStatus = row.getAttribute('data-status');
                    const rowBarangay = row.getAttribute('data-barangay');
                    const rowReport = row.getAttribute('data-report');
                    const rowDate = row.getAttribute('data-date');
                    
                    const statusMatch = (statusFilter === 'all' || rowStatus === statusFilter);
                    const barangayMatch = (barangayFilter === 'all' || rowBarangay === barangayFilter);
                    const searchMatch = rowReport.includes(searchTerm) || rowBarangay.toLowerCase().includes(searchTerm);
                    const timeMatch = isDateInTimeRange(rowDate, timeFilter);
                    
                    row.style.display = (statusMatch && barangayMatch && searchMatch && timeMatch) ? '' : 'none';
                });
                abyipCurrentPage = 1;
                displayAbyipPage();
            }
        }

        function isDateInTimeRange(dateStr, timeFilter) {
            if (timeFilter === 'all') return true;
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const rowDate = new Date(dateStr);
            rowDate.setHours(0, 0, 0, 0);
            
            const daysDiff = Math.floor((today - rowDate) / (1000 * 60 * 60 * 24));
            
            switch(timeFilter) {
                case 'today':
                    return daysDiff === 0;
                case 'week':
                    return daysDiff >= 0 && daysDiff < 7;
                case 'month':
                    return daysDiff >= 0 && daysDiff < 30;
                case 'year':
                    return daysDiff >= 0 && daysDiff < 365;
                default:
                    return true;
            }
        }

        function performReportsSearch() {
            filterTabReports();
        }

        function viewAccomplishmentDetail(id) {
            const row = document.querySelector(`.accomplishment-row[data-id="${id}"]`);
            if (row) {
                document.getElementById('modalProgramName').textContent = row.getAttribute('data-program-name');
                document.getElementById('modalBarangay').textContent = row.getAttribute('data-barangay');
                document.getElementById('modalDate').textContent = row.getAttribute('data-date');
                document.getElementById('modalAttendance').textContent = row.getAttribute('data-attendance');
                document.getElementById('modalEvaluation').textContent = row.getAttribute('data-evaluation') + '/5';
                document.getElementById('viewAccomplishmentModal').style.display = 'flex';
            }
        }

        function closeViewAccomplishmentModal() {
            document.getElementById('viewAccomplishmentModal').style.display = 'none';
        }

        function editAccomplishmentStatus(id) {
            const row = document.querySelector(`.accomplishment-row[data-id="${id}"]`);
            if (row) {
                currentEditAccomplishmentId = id;
                document.getElementById('editModalProgramName').textContent = row.getAttribute('data-program-name');
                document.getElementById('editStatusSelect').value = row.getAttribute('data-status');
                document.getElementById('editAccomplishmentModal').style.display = 'flex';
            }
        }

        function closeEditAccomplishmentModal() {
            document.getElementById('editAccomplishmentModal').style.display = 'none';
            currentEditAccomplishmentId = null;
        }

        function saveAccomplishmentStatus() {
            const newStatus = document.getElementById('editStatusSelect').value;
            let rejectionReason = '';
            
            if (newStatus === 'rejected') {
                rejectionReason = document.getElementById('accomplishmentRejectionReason').value;
                if (!rejectionReason) {
                    alert('Please select a reason for rejection');
                    return;
                }
                if (rejectionReason === 'Others') {
                    const otherReason = document.getElementById('accomplishmentOtherReasonText').value.trim();
                    if (!otherReason) {
                        alert('Please specify the rejection reason');
                        return;
                    }
                    rejectionReason = otherReason;
                }
            }
            
            // Close edit modal first
            document.getElementById('editAccomplishmentModal').style.display = 'none';
            
            // Show validation modal instead of saving directly
            const row = document.querySelector(`.accomplishment-row[data-id="${currentEditAccomplishmentId}"]`);
            if (row) {
                const programName = row.getAttribute('data-program-name');
                const statusText = document.querySelector(`#editStatusSelect option[value="${newStatus}"]`).textContent;
                
                pendingValidation = {
                    type: 'accomplishment',
                    id: currentEditAccomplishmentId,
                    status: newStatus,
                    rejectionReason: rejectionReason,
                    programName: programName
                };
                
                document.getElementById('validationReportName').textContent = programName;
                document.getElementById('validationNewStatus').textContent = statusText;
                document.getElementById('editValidationModal').style.display = 'flex';
            }
        }

        function handleAccomplishmentStatusChange() {
            const status = document.getElementById('editStatusSelect').value;
            const reasonDiv = document.getElementById('accomplishmentRejectionReasonDiv');
            if (status === 'rejected') {
                reasonDiv.style.display = 'block';
            } else {
                reasonDiv.style.display = 'none';
                document.getElementById('accomplishmentOtherReasonDiv').style.display = 'none';
            }
        }

        function handleAccomplishmentReasonChange() {
            const reason = document.getElementById('accomplishmentRejectionReason').value;
            const otherReasonDiv = document.getElementById('accomplishmentOtherReasonDiv');
            if (reason === 'Others') {
                otherReasonDiv.style.display = 'block';
                document.getElementById('accomplishmentOtherReasonText').value = '';
            } else {
                otherReasonDiv.style.display = 'none';
            }
        }

        function editAbyipStatus(id) {
            const row = document.querySelector(`.abyip-row[data-id="${id}"]`);
            if (row) {
                currentEditAbyipId = id;
                document.getElementById('editAbyipModalReportName').textContent = row.getAttribute('data-report-name');
                document.getElementById('editAbyipStatusSelect').value = row.getAttribute('data-status');
                document.getElementById('editAbyipModal').style.display = 'flex';
            }
        }

        function closeEditAbyipModal() {
            document.getElementById('editAbyipModal').style.display = 'none';
            currentEditAbyipId = null;
        }

        function saveAbyipStatus() {
            const newStatus = document.getElementById('editAbyipStatusSelect').value;
            let rejectionReason = '';
            
            if (newStatus === 'rejected') {
                rejectionReason = document.getElementById('abyipRejectionReason').value;
                if (!rejectionReason) {
                    alert('Please select a reason for rejection');
                    return;
                }
                if (rejectionReason === 'Others') {
                    const otherReason = document.getElementById('abyipOtherReasonText').value.trim();
                    if (!otherReason) {
                        alert('Please specify the rejection reason');
                        return;
                    }
                    rejectionReason = otherReason;
                }
            }
            
            // Close edit modal first
            document.getElementById('editAbyipModal').style.display = 'none';
            
            // Show validation modal instead of saving directly
            const row = document.querySelector(`.abyip-row[data-id="${currentEditAbyipId}"]`);
            if (row) {
                const reportName = row.getAttribute('data-report-name');
                const statusText = document.querySelector(`#editAbyipStatusSelect option[value="${newStatus}"]`).textContent;
                
                pendingValidation = {
                    type: 'abyip',
                    id: currentEditAbyipId,
                    status: newStatus,
                    rejectionReason: rejectionReason,
                    reportName: reportName
                };
                
                document.getElementById('validationReportName').textContent = reportName;
                document.getElementById('validationNewStatus').textContent = statusText;
                document.getElementById('editValidationModal').style.display = 'flex';
            }
        }

        function handleAbyipStatusChange() {
            const status = document.getElementById('editAbyipStatusSelect').value;
            const reasonDiv = document.getElementById('abyipRejectionReasonDiv');
            if (status === 'rejected') {
                reasonDiv.style.display = 'block';
            } else {
                reasonDiv.style.display = 'none';
                document.getElementById('abyipOtherReasonDiv').style.display = 'none';
            }
        }

        function handleAbyipReasonChange() {
            const reason = document.getElementById('abyipRejectionReason').value;
            const otherReasonDiv = document.getElementById('abyipOtherReasonDiv');
            if (reason === 'Others') {
                otherReasonDiv.style.display = 'block';
                document.getElementById('abyipOtherReasonText').value = '';
            } else {
                otherReasonDiv.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            displayAccomplishmentPage();
            displayAbyipPage();
        });

        function toggleMobileSummary(section) {
            const content = document.getElementById(section + 'Content');
            const chevron = document.getElementById(section + 'Chevron');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                chevron.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        function cancelEditValidation() {
            document.getElementById('editValidationModal').style.display = 'none';
            pendingValidation = null;
        }

        function confirmEditValidation() {
            if (!pendingValidation) return;
            
            // Show loading screen
            LoadingScreen.show('Updating Status', 'Please wait...');
            
            // Simulate processing delay
            setTimeout(() => {
                const statusColors = {
                    'compliant': { bg: '#dcfce7', color: '#15803d', text: 'Compliant' },
                    'partial': { bg: '#fef3c7', color: '#b45309', text: 'Partial' },
                    'rejected': { bg: '#fee2e2', color: '#dc2626', text: 'Rejected' },
                    'non-compliant': { bg: '#fee2e2', color: '#dc2626', text: 'Non-Compliant' }
                };
                
                if (pendingValidation.type === 'accomplishment') {
                    const row = document.querySelector(`.accomplishment-row[data-id="${pendingValidation.id}"]`);
                    if (row) {
                        row.setAttribute('data-status', pendingValidation.status);
                        row.setAttribute('data-rejection-reason', pendingValidation.rejectionReason);
                        const statusCell = row.querySelector('span');
                        const colors = statusColors[pendingValidation.status];
                        statusCell.style.background = colors.bg;
                        statusCell.style.color = colors.color;
                        statusCell.textContent = colors.text;
                    }
                } else if (pendingValidation.type === 'abyip') {
                    const row = document.querySelector(`.abyip-row[data-id="${pendingValidation.id}"]`);
                    if (row) {
                        row.setAttribute('data-status', pendingValidation.status);
                        row.setAttribute('data-rejection-reason', pendingValidation.rejectionReason);
                        const statusCell = row.querySelector('span');
                        const colors = statusColors[pendingValidation.status];
                        statusCell.style.background = colors.bg;
                        statusCell.style.color = colors.color;
                        statusCell.textContent = colors.text;
                    }
                }
                
                // Hide loading screen
                LoadingScreen.hide();
                
                // Close validation modal
                document.getElementById('editValidationModal').style.display = 'none';
                
                // Show success message
                alert('Status updated successfully!');
                
                // Clear pending validation
                pendingValidation = null;
            }, 1500);
        }
    </script>
</body>
</html>
