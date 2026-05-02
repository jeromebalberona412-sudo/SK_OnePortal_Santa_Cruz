<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SK Community Feed - SK Federation</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/community_feed/css/community-feed.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="sk-fed-feed">
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

    {{-- ── NAVBAR (SK Fed style) ── --}}
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
            <input type="text" placeholder="Search posts, programs..." aria-label="Search" id="feed-search-input">
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

    {{-- ── SIDEBAR (SK Fed style) ── --}}
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
            <a href="{{ route('community-feed') }}" class="menu-item active" data-tooltip="SK Community Feed">
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
            <button type="button" class="menu-item logout-item" data-tooltip="Logout" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </nav>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <main class="main-content cf-main">
        <div class="cf-container">

            {{-- ── CENTER: Feed ── --}}
            <div class="feed-section">

                {{-- SK Federation Info Card --}}
                <div class="sk-fed-card" style="cursor:pointer;" onclick="window.location.href='{{ route('sk-fed-profile') }}'">
                    <div class="sk-fed-card-banner">
                        <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed Logo" class="sk-fed-card-logo">
                        <div class="sk-fed-card-info">
                            <h2 class="sk-fed-card-name">SK Federation Santa Cruz</h2>
                            <p class="sk-fed-card-sub">Sangguniang Kabataan Federation · Santa Cruz, Laguna</p>
                            <p style="font-size:11px;color:#ffffff;font-weight:600;margin-top:4px;">View Profile →</p>
                        </div>
                    </div>
                </div>

                {{-- Compose Post --}}
                <div class="post-card compose-card">
                    <div class="compose-row">
                        <img src="{{ $avatar }}" alt="Avatar" class="post-avatar">
                        <button class="compose-trigger" onclick="openComposeModal()">
                            What's happening in your barangay?
                        </button>
                    </div>
                    <div class="compose-actions">
                        <button class="compose-action-btn" onclick="openComposeModal('announcement')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z"/></svg>
                            Announcement
                        </button>
                        <button class="compose-action-btn" onclick="openComposeModal('event')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                            Event
                        </button>
                        <button class="compose-action-btn" onclick="openComposeModal('photo')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                            Photo
                        </button>
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="feed-filter-bar">
                    <button class="feed-tab active" data-filter="all" onclick="setFeedFilter(this,'all')">All</button>
                    <button class="feed-tab" data-filter="announcement" onclick="setFeedFilter(this,'announcement')">Announcements</button>
                    <button class="feed-tab" data-filter="event" onclick="setFeedFilter(this,'event')">Events</button>
                    <button class="feed-tab" data-filter="activity" onclick="setFeedFilter(this,'activity')">Activities</button>
                    <button class="feed-tab" data-filter="program" onclick="setFeedFilter(this,'program')">Programs</button>
                </div>

                {{-- Feed Posts --}}
                <div id="feed-posts"></div>

                <div style="text-align:center;padding:8px 0 16px;">
                    <button class="load-more-btn" id="load-more-btn" onclick="loadMorePosts()">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                        Load More
                    </button>
                </div>
            </div>

            {{-- ── RIGHT: Program Categories Sidebar ── --}}
            <aside class="programs-sidebar" id="programsSidebar">
                <div class="sidebar-card">
                    <h2 class="sidebar-title">Programs in Your Barangay</h2>
                    <p class="sidebar-subtitle">Available programs for SK officials</p>
                    <div class="program-categories">
                        <div class="program-category" data-category="education" onclick="openProgramModal('education')">
                            <div class="category-icon education"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/><path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg></div>
                            <div class="category-content"><h3>Education</h3><p>1 active program</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="anti-drugs" onclick="openProgramModal('anti-drugs')">
                            <div class="category-icon anti-drugs"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Anti-Drugs</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="agriculture" onclick="openProgramModal('agriculture')">
                            <div class="category-icon agriculture"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Agriculture</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="disaster" onclick="openProgramModal('disaster')">
                            <div class="category-icon disaster"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Disaster Preparedness</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="sports" onclick="openProgramModal('sports')">
                            <div class="category-icon sports"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Sports Development</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="gender" onclick="openProgramModal('gender')">
                            <div class="category-icon gender"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg></div>
                            <div class="category-content"><h3>Gender and Development</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="health" onclick="openProgramModal('health')">
                            <div class="category-icon health"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Health</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="program-category" data-category="others" onclick="openProgramModal('others')">
                            <div class="category-icon others"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg></div>
                            <div class="category-content"><h3>Others</h3><p>0 active programs</p></div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Barangay SK Profiles --}}
                <div class="sidebar-card" style="margin-top:16px;">
                    <h2 class="sidebar-title">Barangay SK Profiles</h2>
                    <p class="sidebar-subtitle">Browse SK officials from each barangay.</p>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        @php
                        $brgyList = [
                            ['name'=>'Alipit',        'slug'=>'alipit',        'color'=>'#4CAF50'],
                            ['name'=>'Bagumbayan',    'slug'=>'bagumbayan',    'color'=>'#2196F3'],
                            ['name'=>'Bubukal',       'slug'=>'bubukal',       'color'=>'#9C27B0'],
                            ['name'=>'Duhat',         'slug'=>'duhat',         'color'=>'#FF9800'],
                            ['name'=>'Gatid',         'slug'=>'gatid',         'color'=>'#009688'],
                            ['name'=>'Labuin',        'slug'=>'labuin',        'color'=>'#f44336'],
                            ['name'=>'Pagsawitan',    'slug'=>'pagsawitan',    'color'=>'#673AB7'],
                            ['name'=>'San Jose',      'slug'=>'san-jose',      'color'=>'#0450a8'],
                            ['name'=>'Santisima Cruz','slug'=>'santisima-cruz','color'=>'#FF5722'],
                        ];
                        @endphp
                        @foreach($brgyList as $brgy)
                        <a href="{{ route('skfed.barangay-profile', ['slug' => $brgy['slug']]) }}"
                           style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f5f7fa;border-radius:12px;text-decoration:none;transition:background 0.2s;"
                           onmouseover="this.style.background='#eef1fb'" onmouseout="this.style.background='#f5f7fa'">
                            <div style="width:38px;height:38px;border-radius:50%;background:{{ $brgy['color'] }};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($brgy['name'], 0, 2)) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:13px;font-weight:700;color:#1a1d25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Brgy. {{ $brgy['name'] }}</p>
                                <p style="font-size:11px;color:#999;">SK Officials</p>
                            </div>
                            <svg style="width:14px;height:14px;color:#bbb;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </main>

    {{-- Programs Drawer Backdrop --}}
    <div class="programs-drawer-backdrop" id="programsDrawerBackdrop"></div>

    {{-- Mobile FAB: open programs sidebar --}}
    <button class="programs-fab" id="programsFab" aria-label="View Programs">
        <i class="fas fa-th-list"></i>
    </button>

    {{-- ── COMPOSE / EDIT POST MODAL ── --}}
    <div id="composeModal" class="program-modal">
        <div class="modal-overlay" onclick="closeComposeModal()"></div>
        <div class="modal-container" style="max-width:560px;">
            <div class="modal-header">
                <h2 id="compose-modal-title">Create Post</h2>
                <button class="modal-close" onclick="closeComposeModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-post-id" value="">
                <div class="compose-author-row">
                    <img src="{{ $avatar }}" alt="Avatar" class="compose-avatar-sm">
                    <div>
                        <div class="compose-author-name">{{ $user->name ?? 'SK Official' }}</div>
                        <select class="compose-visibility-select" id="compose-visibility" aria-label="Visibility">
                            <option value="public">🌐 Public</option>
                            <option value="federation">🔒 Federation Only</option>
                        </select>
                    </div>
                </div>
                <div class="compose-type-row">
                    <label class="compose-type-label">Post Type:</label>
                    <select class="compose-type-select" id="compose-type" aria-label="Post type">
                        <option value="update">Update</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                        <option value="activity">Activity</option>
                        <option value="program">Youth Program</option>
                    </select>
                </div>
                <textarea class="compose-textarea" id="compose-content" placeholder="Write something..." rows="4"></textarea>
                <div class="compose-attach-row">
                    <label class="compose-attach-btn" for="compose-image-input" title="Upload Image">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                        Photo
                    </label>
                    <input type="file" id="compose-image-input" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <label class="compose-attach-btn" id="compose-link-btn" onclick="toggleLinkInput()" title="Add Link">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                        Link
                    </label>
                </div>
                <div id="compose-image-preview" style="display:none;margin-top:10px;position:relative;">
                    <img id="compose-preview-img" src="" alt="Preview" style="width:100%;border-radius:10px;max-height:220px;object-fit:cover;">
                    <button onclick="removeImagePreview()" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:28px;height:28px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
                <div id="compose-link-input-wrap" style="display:none;margin-top:10px;">
                    <input type="url" id="compose-link-input" class="compose-link-field" placeholder="Paste a link (https://...)">
                </div>
            </div>
            <div class="modal-footer-btns">
                <button class="btn-secondary" onclick="closeComposeModal()">Cancel</button>
                <button class="btn-primary" onclick="submitPost()">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                    Post
                </button>
            </div>
        </div>
    </div>

    {{-- ── EDUCATION PROGRAM MODAL ── --}}
    <div id="educationModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2>Education Programs</h2>
                <button class="modal-close" onclick="closeProgramModal('educationModal')">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="program-item">
                    <div class="program-header">
                        <h3>🎓 Scholarship Assistance Program</h3>
                        <span class="program-status active">Active</span>
                    </div>
                    <p class="program-description">Financial assistance for deserving students pursuing higher education. Covers tuition fees and other educational expenses.</p>
                    <div class="program-meta">
                        <span>📅 Deadline: March 31, 2026</span>
                        <span>👥 50 slots available</span>
                    </div>
                    <button class="apply-btn" onclick="openScholarshipForm()">Apply Now</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── NO PROGRAM MODAL ── --}}
    <div id="noProgramModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="noProgramModalTitle">Programs</h2>
                <button class="modal-close" onclick="closeProgramModal('noProgramModal')">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;padding:40px 20px;">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" style="margin:0 auto 16px;display:block;">
                    <rect x="3" y="6" width="18" height="14" rx="2" stroke="#ccc" stroke-width="2" fill="none"/>
                    <path d="M3 10 L12 6 L21 10" stroke="#ccc" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <line x1="8" y1="13" x2="16" y2="13" stroke="#e0e0e0" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="10" y1="16" x2="14" y2="16" stroke="#e0e0e0" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <h3 style="font-size:18px;color:#333;margin-bottom:8px;">No Programs Available</h3>
                <p style="color:#999;font-size:14px;line-height:1.6;">There are currently no active programs in this category. Please check back later.</p>
            </div>
        </div>
    </div>

    {{-- ── SCHOLARSHIP FORM MODAL ── --}}
    <div id="scholarshipFormModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container large">
            <div class="modal-header">
                <h2>Scholarship Application Form</h2>
                <button class="modal-close" onclick="closeProgramModal('scholarshipFormModal')">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <form class="scholarship-form" onsubmit="submitScholarship(event)">
                    <div class="form-section"><h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group"><label>Full Name *</label><input type="text" required placeholder="Juan Dela Cruz"></div>
                            <div class="form-group"><label>Date of Birth *</label><input type="date" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Email Address *</label><input type="email" required placeholder="juan@example.com"></div>
                            <div class="form-group"><label>Contact Number *</label><input type="tel" required placeholder="09XX XXX XXXX"></div>
                        </div>
                        <div class="form-group"><label>Complete Address *</label><textarea rows="2" required placeholder="House No., Street, Barangay, Santa Cruz, Laguna"></textarea></div>
                    </div>
                    <div class="form-section"><h3>Educational Background</h3>
                        <div class="form-row">
                            <div class="form-group"><label>School/University *</label><input type="text" required placeholder="Name of Institution"></div>
                            <div class="form-group"><label>Course/Program *</label><input type="text" required placeholder="e.g., BS Computer Science"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Year Level *</label><select required><option value="">Select Year Level</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option><option>5th Year</option></select></div>
                            <div class="form-group"><label>GPA/General Average *</label><input type="text" required placeholder="e.g., 1.75 or 90%"></div>
                        </div>
                    </div>
                    <div class="form-section"><h3>Required Documents</h3>
                        <div class="form-group"><label>Certificate of Enrollment *</label><input type="file" required accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="form-group"><label>Grade Report (Previous Semester) *</label><input type="file" required accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="form-group"><label>Barangay Certificate of Indigency *</label><input type="file" required accept=".pdf,.jpg,.jpeg,.png"></div>
                    </div>
                    <div class="form-section"><h3>Essay</h3>
                        <div class="form-group"><label>Why do you deserve this scholarship? (200-500 words) *</label><textarea rows="6" required placeholder="Share your story, goals, and why you need this scholarship..."></textarea></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeProgramModal('scholarshipFormModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── SUCCESS MODAL ── --}}
    <div id="programSuccessModal" class="program-modal">
        <div class="modal-overlay" onclick="document.getElementById('programSuccessModal').classList.remove('active')"></div>
        <div class="modal-container" style="max-width:420px;">
            <div class="modal-body" style="text-align:center;padding:48px 32px;">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(34,197,94,0.35);">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:white;"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <h3 style="font-size:22px;font-weight:700;color:#333;margin-bottom:10px;">Application Submitted!</h3>
                <p style="color:#666;font-size:15px;line-height:1.6;margin-bottom:8px;">Your application has been successfully submitted. We will review it and contact you soon.</p>
                <p style="color:#94a3b8;font-size:13px;">This window will close automatically.</p>
                <div style="margin-top:12px;height:3px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                    <div id="successProgressBar" style="height:100%;background:linear-gradient(135deg,#22c55e,#16a34a);width:100%;transition:width 5s linear;"></div>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard::logout-modal')

    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/modules/community_feed/js/community-feed.js') }}"></script>
    <script>
        window.logoutRoute  = "{{ route('logout') }}";
        window.loginRoute   = "{{ route('login') }}";
        window.currentUser  = "{{ $user->name ?? 'SK Official' }}";
        window.currentAvatar = "{{ $avatar }}";

        document.getElementById('sidebar-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile','Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-profile-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Profile','Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-change-pw-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading','Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });
        document.getElementById('nav-dashboard-link')?.addEventListener('click', function(e) {
            e.preventDefault(); LoadingScreen.show('Loading Dashboard','Please wait...');
            setTimeout(() => { window.location.href = this.href; }, 300);
        });

        // Program modals
        function openProgramModal(cat) {
            if (cat === 'education') { document.getElementById('educationModal').classList.add('active'); return; }
            const titles = { 'anti-drugs':'Anti-Drugs','agriculture':'Agriculture','disaster':'Disaster Preparedness','sports':'Sports Development','gender':'Gender and Development','health':'Health','others':'Others' };
            document.getElementById('noProgramModalTitle').textContent = (titles[cat] || cat) + ' Programs';
            document.getElementById('noProgramModal').classList.add('active');
        }
        function closeProgramModal(id) { document.getElementById(id).classList.remove('active'); }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', function() {
                this.closest('.program-modal')?.classList.remove('active');
            });
        });
        function openScholarshipForm() {
            document.getElementById('educationModal').classList.remove('active');
            document.getElementById('scholarshipFormModal').classList.add('active');
        }
        function submitScholarship(e) {
            e.preventDefault();
            document.getElementById('scholarshipFormModal').classList.remove('active');
            const modal = document.getElementById('programSuccessModal');
            const bar   = document.getElementById('successProgressBar');
            modal.classList.add('active');
            bar.style.transition = 'none'; bar.style.width = '100%';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                bar.style.transition = 'width 5s linear'; bar.style.width = '0%';
            }));
            setTimeout(() => modal.classList.remove('active'), 5000);
        }
    </script>
</body>
</html>
