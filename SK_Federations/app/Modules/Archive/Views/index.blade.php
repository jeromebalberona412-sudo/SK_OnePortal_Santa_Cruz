<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Archive - SK Federations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/archive/css/archive.css') }}">
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
            <input type="text" id="archiveSearchInput" placeholder="Search..." onkeyup="performArchiveSearch()" aria-label="Search archive">
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
                <a href="#" onclick="switchArchiveTab('deleted')" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-trash"></i><span>Deleted Reports</span>
                </a>
                <a href="#" onclick="switchArchiveTab('archived')" class="menu-item" style="font-size:13px;">
                    <i class="fas fa-box"></i><span>Archived Reports</span>
                </a>
            </div>
            <div class="menu-divider"></div>
            <div class="menu-divider"></div>
            <div class="menu-divider"></div>
            <div class="menu-divider"></div>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </aside>

    <main class="main-content">
        <div style="padding:24px;max-width:1400px;margin:0 auto;">
            <h2 style="font-size:28px;font-weight:800;color:#0d1b4b;margin-bottom:8px;" id="archivePageTitle">Archive Reports</h2>
            <p style="font-size:14px;color:#64748b;margin-bottom:24px;" id="archivePageDescription">Manage deleted and archived reports</p>

            {{-- Deleted Reports Tab --}}
            <div id="section-deleted" style="display:block;">
                {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
                    <select id="deletedStatusFilter" onchange="filterDeletedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Status</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select id="deletedBarangayFilter" onchange="filterDeletedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
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
                    <select id="deletedTypeFilter" onchange="filterDeletedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Types</option>
                        <option value="abyip">ABYIP</option>
                        <option value="accomplishment">Accomplishment</option>
                    </select>
                    <select id="deletedTimeFilter" onchange="filterDeletedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="14days">Last 14 Days</option>
                        <option value="30days">Last 30 Days</option>
                    </select>
                </div>

                {{-- Deleted Reports Table --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report Name</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Type</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Barangay</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Submitted</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report (PDF)</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Days Left</th>
                                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deletedReportsTable">
                                @forelse($deletedReports as $report)
                                <tr class="deleted-row" data-status="{{ $report['status'] }}" data-barangay="{{ strtolower($report['barangay']) }}" data-type="{{ $report['type'] }}" data-date="{{ $report['timestamp_deleted'] }}" style="border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;font-weight:600;">{{ $report['name'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;">
                                        <span style="background:#eff3ff;color:#213F99;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;">{{ $report['type'] }}</span>
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;">{{ $report['barangay'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#64748b;">{{ $report['date_submitted'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;text-align:center;">
                                        @if(isset($report['pdf_path']))
                                            <a href="{{ $report['pdf_path'] }}" target="_blank" style="color:#213F99;text-decoration:none;font-weight:600;" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;">
                                        <span style="background:#fee2e2;color:#dc2626;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">{{ ucfirst($report['status']) }}</span>
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;font-weight:600;">
                                        @if($report['days_until_permanent_delete'] > 0)
                                            <span style="background:#fef3c7;color:#b45309;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">{{ $report['days_until_permanent_delete'] }} days</span>
                                        @else
                                            <span style="background:#fee2e2;color:#dc2626;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">Expired</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        <button onclick="recoverReport({{ $report['id'] }}, 'deleted')" style="padding:6px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;color:#213F99;cursor:pointer;margin-right:4px;transition:background .2s;font-size:14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'" title="Recover Report">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button onclick="permanentlyDeleteReport({{ $report['id'] }})" style="padding:6px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;color:#dc2626;cursor:pointer;transition:background .2s;font-size:14px;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'" title="Delete Report">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="padding:20px;text-align:center;color:#94a3b8;">No deleted reports</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Deleted Reports Pagination --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:13px;color:#64748b;">
                        Showing <span id="deletedStart">1</span> to <span id="deletedEnd">5</span> of <span id="deletedTotal">{{ count($deletedReports) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="previousDeletedPage()" id="deletedPrevBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextDeletedPage()" id="deletedNextBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Archived Reports Tab --}}
            <div id="section-archived" style="display:none;">
                {{-- Filters --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
                    <select id="archivedStatusFilter" onchange="filterArchivedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Status</option>
                        <option value="compliant">Compliant</option>
                    </select>
                    <select id="archivedBarangayFilter" onchange="filterArchivedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
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
                    <select id="archivedTypeFilter" onchange="filterArchivedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Types</option>
                        <option value="abyip">ABYIP</option>
                        <option value="accomplishment">Accomplishment</option>
                    </select>
                    <select id="archivedTimeFilter" onchange="filterArchivedReports()" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#475569;background:#fff;cursor:pointer;">
                        <option value="all">All Time</option>
                        <option value="3months">Last 3 Months</option>
                        <option value="6months">Last 6 Months</option>
                        <option value="1year">Last Year</option>
                    </select>
                </div>

                {{-- Archived Reports Table --}}
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report Name</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Type</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Barangay</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Date Submitted</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Report (PDF)</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Status</th>
                                    <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:700;color:#0d1b4b;">Archived By</th>
                                    <th style="padding:12px 16px;text-align:center;font-size:12px;font-weight:700;color:#0d1b4b;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="archivedReportsTable">
                                @forelse($archivedReports as $report)
                                <tr class="archived-row" data-status="{{ $report['status'] }}" data-barangay="{{ strtolower($report['barangay']) }}" data-type="{{ $report['type'] }}" data-date="{{ strtotime($report['date_archived']) }}" style="border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;font-weight:600;">{{ $report['name'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;">
                                        <span style="background:#eff3ff;color:#213F99;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;">{{ $report['type'] }}</span>
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;color:#0d1b4b;">{{ $report['barangay'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;color:#64748b;">{{ $report['date_submitted'] }}</td>
                                    <td style="padding:12px 16px;font-size:13px;text-align:center;">
                                        @if(isset($report['pdf_path']))
                                            <a href="{{ $report['pdf_path'] }}" target="_blank" style="color:#213F99;text-decoration:none;font-weight:600;" title="Download PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;">
                                        <span style="background:#dcfce7;color:#15803d;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;">{{ ucfirst($report['status']) }}</span>
                                    </td>
                                    <td style="padding:12px 16px;font-size:13px;color:#64748b;">{{ $report['archived_by'] }}</td>
                                    <td style="padding:12px 16px;text-align:center;">
                                        <button onclick="unarchiveReport({{ $report['id'] }})" style="padding:6px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;color:#213F99;cursor:pointer;margin-right:4px;transition:background .2s;font-size:14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'" title="Unarchive Report">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button onclick="deleteArchivedReport({{ $report['id'] }})" style="padding:6px 8px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;color:#dc2626;cursor:pointer;transition:background .2s;font-size:14px;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'" title="Delete Report">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="padding:20px;text-align:center;color:#94a3b8;">No archived reports</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Archived Reports Pagination --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:13px;color:#64748b;">
                        Showing <span id="archivedStart">1</span> to <span id="archivedEnd">5</span> of <span id="archivedTotal">{{ count($archivedReports) }}</span> reports
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="previousArchivedPage()" id="archivedPrevBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button onclick="nextArchivedPage()" id="archivedNextBtn" style="padding:8px 12px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;font-size:13px;color:#213F99;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">
                            Next <i class="fas fa-chevron-right"></i>
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

        function toggleArchiveMenu() {
            const submenu = document.getElementById('archiveSubmenu');
            const chevron = document.getElementById('archiveChevron');
            const isOpen = submenu.style.display === 'block';
            submenu.style.display = isOpen ? 'none' : 'block';
            chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function switchArchiveTab(tab) {
            document.getElementById('section-deleted').style.display = tab === 'deleted' ? 'block' : 'none';
            document.getElementById('section-archived').style.display = tab === 'archived' ? 'block' : 'none';
            document.getElementById('archivePageTitle').textContent = tab === 'deleted' ? 'Delete Reports' : 'Archive Reports';
            document.getElementById('archivePageDescription').textContent = tab === 'deleted' ? 'Reports deleted within the last 30 days. After 30 days, they will be permanently deleted.' : 'Long-term saved reports for historical reference and compliance records.';
        }

        function filterDeletedReports() {
            const statusFilter = document.getElementById('deletedStatusFilter').value;
            const barangayFilter = document.getElementById('deletedBarangayFilter').value;
            const typeFilter = document.getElementById('deletedTypeFilter').value;
            const timeFilter = document.getElementById('deletedTimeFilter').value;
            const rows = document.querySelectorAll('.deleted-row');
            const now = Math.floor(Date.now() / 1000);

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const barangay = row.getAttribute('data-barangay');
                const type = row.getAttribute('data-type');
                const date = parseInt(row.getAttribute('data-date'));
                
                let statusMatch = statusFilter === 'all' || status === statusFilter;
                let barangayMatch = barangayFilter === 'all' || barangay === barangayFilter.toLowerCase();
                let typeMatch = typeFilter === 'all' || type === typeFilter;
                
                let timeMatch = true;
                if (timeFilter !== 'all') {
                    const daysDiff = Math.floor((now - date) / (24 * 60 * 60));
                    if (timeFilter === '7days') timeMatch = daysDiff <= 7;
                    else if (timeFilter === '14days') timeMatch = daysDiff <= 14;
                    else if (timeFilter === '30days') timeMatch = daysDiff <= 30;
                }
                
                row.style.display = (statusMatch && barangayMatch && typeMatch && timeMatch) ? '' : 'none';
            });
            
            deletedCurrentPage = 1;
            updateDeletedPagination();
        }

        function filterArchivedReports() {
            const statusFilter = document.getElementById('archivedStatusFilter').value;
            const barangayFilter = document.getElementById('archivedBarangayFilter').value;
            const typeFilter = document.getElementById('archivedTypeFilter').value;
            const timeFilter = document.getElementById('archivedTimeFilter').value;
            const rows = document.querySelectorAll('.archived-row');
            const now = Math.floor(Date.now() / 1000);

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const barangay = row.getAttribute('data-barangay');
                const type = row.getAttribute('data-type');
                const date = parseInt(row.getAttribute('data-date'));
                
                let statusMatch = statusFilter === 'all' || status === statusFilter;
                let barangayMatch = barangayFilter === 'all' || barangay === barangayFilter.toLowerCase();
                let typeMatch = typeFilter === 'all' || type === typeFilter;
                
                let timeMatch = true;
                if (timeFilter !== 'all') {
                    const monthsDiff = Math.floor((now - date) / (30 * 24 * 60 * 60));
                    if (timeFilter === '3months') timeMatch = monthsDiff <= 3;
                    else if (timeFilter === '6months') timeMatch = monthsDiff <= 6;
                    else if (timeFilter === '1year') timeMatch = monthsDiff <= 12;
                }
                
                row.style.display = (statusMatch && barangayMatch && typeMatch && timeMatch) ? '' : 'none';
            });
            
            archivedCurrentPage = 1;
            updateArchivedPagination();
        }

        function recoverReport(id, type) {
            LoadingScreen.show('Recovering Report', 'Please wait...');
            setTimeout(() => {
                LoadingScreen.hide();
                alert('Report recovered successfully!');
            }, 1500);
        }

        function permanentlyDeleteReport(id) {
            if (confirm('Are you sure you want to permanently delete this report? This action cannot be undone.')) {
                LoadingScreen.show('Deleting Report', 'Please wait...');
                setTimeout(() => {
                    LoadingScreen.hide();
                    alert('Report permanently deleted!');
                }, 1500);
            }
        }

        function unarchiveReport(id) {
            LoadingScreen.show('Unarchiving Report', 'Please wait...');
            setTimeout(() => {
                LoadingScreen.hide();
                alert('Report unarchived successfully!');
            }, 1500);
        }

        function deleteArchivedReport(id) {
            if (confirm('Are you sure you want to delete this archived report?')) {
                LoadingScreen.show('Deleting Report', 'Please wait...');
                setTimeout(() => {
                    LoadingScreen.hide();
                    alert('Archived report deleted!');
                }, 1500);
            }
        }

        // ── Pagination Variables ──
        let deletedCurrentPage = 1;
        let archivedCurrentPage = 1;
        const itemsPerPage = 5;

        // ── Deleted Reports Pagination ──
        function updateDeletedPagination() {
            const rows = Array.from(document.querySelectorAll('.deleted-row')).filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            const start = (deletedCurrentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });

            document.getElementById('deletedStart').textContent = rows.length > 0 ? start + 1 : 0;
            document.getElementById('deletedEnd').textContent = Math.min(end, rows.length);
            document.getElementById('deletedTotal').textContent = rows.length;
            document.getElementById('deletedPrevBtn').disabled = deletedCurrentPage === 1;
            document.getElementById('deletedNextBtn').disabled = deletedCurrentPage >= totalPages;
            document.getElementById('deletedPrevBtn').style.opacity = deletedCurrentPage === 1 ? '0.5' : '1';
            document.getElementById('deletedNextBtn').style.opacity = deletedCurrentPage >= totalPages ? '0.5' : '1';
        }

        function nextDeletedPage() {
            const rows = Array.from(document.querySelectorAll('.deleted-row')).filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            if (deletedCurrentPage < totalPages) {
                deletedCurrentPage++;
                updateDeletedPagination();
            }
        }

        function previousDeletedPage() {
            if (deletedCurrentPage > 1) {
                deletedCurrentPage--;
                updateDeletedPagination();
            }
        }

        // ── Archived Reports Pagination ──
        function updateArchivedPagination() {
            const rows = Array.from(document.querySelectorAll('.archived-row')).filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            const start = (archivedCurrentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });

            document.getElementById('archivedStart').textContent = rows.length > 0 ? start + 1 : 0;
            document.getElementById('archivedEnd').textContent = Math.min(end, rows.length);
            document.getElementById('archivedTotal').textContent = rows.length;
            document.getElementById('archivedPrevBtn').disabled = archivedCurrentPage === 1;
            document.getElementById('archivedNextBtn').disabled = archivedCurrentPage >= totalPages;
            document.getElementById('archivedPrevBtn').style.opacity = archivedCurrentPage === 1 ? '0.5' : '1';
            document.getElementById('archivedNextBtn').style.opacity = archivedCurrentPage >= totalPages ? '0.5' : '1';
        }

        function nextArchivedPage() {
            const rows = Array.from(document.querySelectorAll('.archived-row')).filter(row => row.style.display !== 'none');
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            if (archivedCurrentPage < totalPages) {
                archivedCurrentPage++;
                updateArchivedPagination();
            }
        }

        function previousArchivedPage() {
            if (archivedCurrentPage > 1) {
                archivedCurrentPage--;
                updateArchivedPagination();
            }
        }

        function performArchiveSearch() {
            const searchTerm = document.getElementById('archiveSearchInput').value.toLowerCase();
            const deletedRows = document.querySelectorAll('.deleted-row');
            const archivedRows = document.querySelectorAll('.archived-row');

            deletedRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });

            archivedRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            deletedCurrentPage = 1;
            archivedCurrentPage = 1;
            updateDeletedPagination();
            updateArchivedPagination();
        }

        // Initialize pagination on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDeletedPagination();
            updateArchivedPagination();
        });
    </script>
</body>
</html>
