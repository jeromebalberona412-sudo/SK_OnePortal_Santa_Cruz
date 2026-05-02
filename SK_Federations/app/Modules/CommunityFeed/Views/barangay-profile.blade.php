<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>SK Barangay {{ $name }} - SK Federations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/community_feed/css/community-feed.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        /* ── Barangay Profile Page ── */
        .bfp-wrap { padding: 24px; }

        .bfp-header-card {
            background: #fff; border-radius: 16px;
            overflow: hidden; box-shadow: 0 2px 12px rgba(33,63,153,.08);
            margin-bottom: 24px;
        }

        .bfp-cover {
            height: 160px;
            background: linear-gradient(135deg, var(--brgy-color, #213F99) 0%, #0d1b4b 100%);
            position: relative; overflow: hidden;
        }

        .bfp-cover::after {
            content: ''; position: absolute; inset: 0;
            background-image: url('/modules/authentication/images/Background.png');
            background-size: cover; background-position: center;
            background-repeat: no-repeat; opacity: .08;
            mix-blend-mode: luminosity;
        }

        .bfp-info-row {
            padding: 0 28px 22px;
            display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap;
        }

        .bfp-avatar-wrap { margin-top: -50px; flex-shrink: 0; }

        .bfp-avatar {
            width: 100px; height: 100px; border-radius: 50%;
            background: var(--brgy-color, #213F99); border: 4px solid #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 900; color: #fff;
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
        }

        .bfp-meta { flex: 1; padding-top: 12px; }

        .bfp-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(33,63,153,.08); color: #213F99;
            font-size: 10px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; padding: 3px 10px; border-radius: 999px; margin-bottom: 4px;
        }

        .bfp-name { font-size: 20px; font-weight: 800; color: #0d1b4b; margin-bottom: 2px; }
        .bfp-loc  { font-size: 13px; color: #64748b; margin-bottom: 8px; }

        .bfp-stats { display: flex; gap: 20px; flex-wrap: wrap; }
        .bfp-stat strong { display: block; font-size: 18px; font-weight: 800; color: #213F99; }
        .bfp-stat span   { font-size: 11px; color: #94a3b8; }

        /* Grid — left sticky, right scrolls freely with page */
        .bfp-grid {
            display: grid; grid-template-columns: 300px 1fr;
            gap: 20px; align-items: start;
        }

        .bfp-left {
            display: flex; flex-direction: column; gap: 16px;
            position: sticky;
            top: calc(var(--navbar-height, 64px) + 16px);
            max-height: calc(100vh - var(--navbar-height, 64px) - 32px);
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(33,63,153,0.2) transparent;
        }
        .bfp-left::-webkit-scrollbar { width: 5px; }
        .bfp-left::-webkit-scrollbar-thumb { background: rgba(33,63,153,0.2); border-radius: 4px; }

        .bfp-right {
            display: flex; flex-direction: column; gap: 16px;
        }

        .bfp-card {
            background: #fff; border-radius: 14px; padding: 20px 22px;
            box-shadow: 0 2px 8px rgba(33,63,153,.07);
        }

        .bfp-card-title {
            font-size: 14px; font-weight: 700; color: #0d1b4b;
            margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }

        .bfp-card-title i { color: #213F99; }

        .bfp-officer-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid #f1f5f9;
        }
        .bfp-officer-item:last-child { border-bottom: none; }

        .bfp-officer-dot {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--brgy-color, #213F99);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0;
        }

        .bfp-officer-name { font-size: 13px; font-weight: 700; color: #1e293b; }
        .bfp-officer-role { font-size: 11px; color: #94a3b8; }

        .bfp-councilor-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px;
        }

        .bfp-councilor-chip {
            display: flex; align-items: center; gap: 8px;
            background: #f5f7fa; border-radius: 10px; padding: 8px 10px;
        }

        .bfp-councilor-dot {
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--brgy-color, #213F99); opacity: .85;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0;
        }

        .bfp-councilor-name { font-size: 11px; font-weight: 600; color: #444; }

        .bfp-contact-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;
        }
        .bfp-contact-row:last-child { border-bottom: none; }

        .bfp-contact-icon {
            width: 30px; height: 30px; border-radius: 8px;
            background: #eff3ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .bfp-contact-icon i { font-size: 12px; color: #213F99; }
        .bfp-contact-label { font-size: 11px; color: #94a3b8; }
        .bfp-contact-value { font-size: 13px; font-weight: 600; color: #1e293b; }

        /* Feed */
        .bfp-feed-tabs {
            display: flex; gap: 4px; margin-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .bfp-tab {
            padding: 9px 16px; border: none; background: none;
            font-size: 13px; font-weight: 600; color: #64748b;
            cursor: pointer; border-bottom: 2px solid transparent;
            margin-bottom: -2px; transition: all .2s;
        }

        .bfp-tab.active { color: #213F99; border-bottom-color: #213F99; }
        .bfp-tab:hover  { color: #213F99; }

        .bfp-post {
            border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;
            margin-bottom: 14px;
        }

        .bfp-post-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }

        .bfp-post-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--brgy-color, #213F99);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0;
        }

        .bfp-post-author { font-size: 13px; font-weight: 700; color: #0d1b4b; }
        .bfp-post-meta   { font-size: 11px; color: #94a3b8; margin-top: 2px; }

        .bfp-post-type {
            display: inline-block; padding: 2px 7px; border-radius: 8px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; margin-right: 4px;
        }
        .bfp-post-type.event        { background: #fef3c7; color: #92400e; }
        .bfp-post-type.announcement { background: #dbeafe; color: #1d4ed8; }
        .bfp-post-type.activity     { background: #dcfce7; color: #15803d; }

        .bfp-post-title { font-size: 15px; font-weight: 700; color: #0d1b4b; margin-bottom: 6px; }
        .bfp-post-text  { font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 10px; }

        .bfp-post-detail {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: #64748b; margin-bottom: 4px;
        }
        .bfp-post-detail i { color: #213F99; font-size: 11px; }

        .bfp-post-actions {
            display: flex; gap: 8px; margin-top: 12px;
            padding-top: 10px; border-top: 1px solid #f1f5f9;
        }

        .bfp-action-btn {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 7px; border: none; background: none; border-radius: 8px;
            font-size: 12px; color: #64748b; cursor: pointer; transition: background .2s;
        }
        .bfp-action-btn:hover { background: #f1f5f9; color: #213F99; }

        @media (max-width: 1024px) {
            .bfp-grid { grid-template-columns: 1fr; }
            .bfp-left { position: static !important; max-height: none !important; overflow: visible !important; }
        }
        @media (max-width: 640px) {
            .bfp-wrap { padding: 24px; }
            .bfp-info-row { padding: 0 16px 18px; }
            .bfp-councilor-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body style="--brgy-color: {{ $color }}">
    <script>(function(){window.history.pushState(null,'',window.location.href);window.onpopstate=function(){window.history.pushState(null,'',window.location.href);}})();</script>

    @php
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode((string)($user->name ?? 'User')) . '&background=213F99&color=fff&size=120';
        $formattedRole = $user->role ? ucwords(str_replace('_', ' ', (string)$user->role)) : 'SK Official';
    @endphp

    {{-- NAVBAR --}}
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
                <i class="fas fa-bell"></i><span class="notif-badge"></span>
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
                    <a href="{{ route('profile') }}" class="dd-item"><i class="fas fa-user"></i> Profile</a>
                    <a href="{{ route('password.request') }}" class="dd-item"><i class="fas fa-lock"></i> Change Password</a>
                    <div class="dd-divider"></div>
                    <button class="dd-item danger" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="notif-popover" id="notifPopover">
        <div class="notif-popover-header"><h4>Notifications</h4><button class="notif-mark-all">Mark all as read</button></div>
        <div class="notif-list"><div class="notif-empty"><i class="fas fa-bell-slash" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>No notifications yet</div></div>
    </div>
    <div class="sidebar-overlay"></div>

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <a href="{{ route('profile') }}" class="sidebar-profile sidebar-profile-link">
            <img src="{{ $avatar }}" alt="Profile" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="s-name">{{ $user->name ?? 'User' }}</div>
                <div class="s-role">{{ $formattedRole }}</div>
            </div>
        </a>
        <nav class="sidebar-nav">
            <div class="menu-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item" data-tooltip="Dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <div class="menu-section-label">Modules</div>
            <a href="{{ route('community-feed') }}" class="menu-item active" data-tooltip="SK Community Feed"><i class="fas fa-rss"></i><span>SK Community Feed</span></a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring"><i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span></a>
            <a href="{{ route('reports') }}" class="menu-item" data-tooltip="Reports"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring"><i class="fas fa-users"></i><span>Kabataan Monitoring</span></a>
            <div class="menu-divider"></div>
        </nav>
    </aside>

    {{-- MAIN --}}
    <main class="main-content">
        <div class="bfp-wrap">

            {{-- Back link --}}
            <a href="{{ route('community-feed') }}" style="display:inline-flex;align-items:center;gap:6px;color:#213F99;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:16px;">
                <i class="fas fa-arrow-left"></i> Back to Community Feed
            </a>

            {{-- HEADER CARD --}}
            <div class="bfp-header-card">
                <div class="bfp-cover"></div>
                <div class="bfp-info-row">
                    <div class="bfp-avatar-wrap">
                        <div class="bfp-avatar">{{ strtoupper(substr($name, 0, 2)) }}</div>
                    </div>
                    <div class="bfp-meta">
                        <div class="bfp-badge"><i class="fas fa-check-circle" style="font-size:10px;"></i> Sangguniang Kabataan</div>
                        <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                        <p class="bfp-loc"><i class="fas fa-map-marker-alt" style="color:#213F99;margin-right:4px;"></i>Barangay {{ $name }}, Santa Cruz, Laguna</p>
                        <div class="bfp-stats">
                            <div class="bfp-stat"><strong>{{ count($posts) }}</strong><span>Posts</span></div>
                            <div class="bfp-stat"><strong>{{ count($officers['councilors']) + 5 }}</strong><span>Officers</span></div>
                            <div class="bfp-stat"><strong>2023–2026</strong><span>SK Term</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTENT GRID --}}
            <div class="bfp-grid">

                {{-- LEFT --}}
                <div class="bfp-left">

                    {{-- Officers --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-users"></i> SK Officers</div>
                        @php
                        $officerList = [
                            ['name'=>$officers['chairman'], 'role'=>'SK Chairman'],
                            ['name'=>$officers['vice'],     'role'=>'Vice Chairman'],
                            ['name'=>$officers['secretary'],'role'=>'Secretary'],
                            ['name'=>$officers['treasurer'],'role'=>'Treasurer'],
                            ['name'=>$officers['auditor'],  'role'=>'Auditor'],
                            ['name'=>$officers['pro'],      'role'=>'Public Relations Officer'],
                        ];
                        @endphp
                        @foreach($officerList as $o)
                        <div class="bfp-officer-item">
                            <div class="bfp-officer-dot">{{ strtoupper(substr(trim($o['name'],'[]'),0,2)) }}</div>
                            <div>
                                <p class="bfp-officer-name">{{ $o['name'] }}</p>
                                <p class="bfp-officer-role">{{ $o['role'] }}</p>
                            </div>
                        </div>
                        @endforeach
                        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:14px 0 8px;">SK Councilors</p>
                        <div class="bfp-councilor-grid">
                            @foreach($officers['councilors'] as $c)
                            <div class="bfp-councilor-chip">
                                <div class="bfp-councilor-dot">{{ strtoupper(substr(trim($c,'[]'),0,2)) }}</div>
                                <span class="bfp-councilor-name">{{ $c }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Barangay Info --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-info-circle"></i> Barangay Information</div>
                        @foreach([['Barangay',$name],['Municipality','Santa Cruz'],['Province','Laguna'],['Region','Region IV-A (CALABARZON)'],['SK Term','2023 – 2026'],['Total Officers',count($officers['councilors'])+5]] as $row)
                        <div class="bfp-contact-row">
                            <div><p class="bfp-contact-label">{{ $row[0] }}</p><p class="bfp-contact-value">{{ $row[1] }}</p></div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Contact --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-phone"></i> Contact Information</div>
                        @foreach([['fas fa-phone','Phone','[SK Contact Number]'],['fas fa-envelope','Email','[SK Email Address]'],['fas fa-map-marker-alt','Office Address','Barangay '.$name.' Hall, Santa Cruz, Laguna'],['fas fa-clock','Office Hours','Mon–Fri, 8:00 AM – 5:00 PM']] as $row)
                        <div class="bfp-contact-row">
                            <div class="bfp-contact-icon"><i class="{{ $row[0] }}"></i></div>
                            <div><p class="bfp-contact-label">{{ $row[1] }}</p><p class="bfp-contact-value">{{ $row[2] }}</p></div>
                        </div>
                        @endforeach
                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="bfp-right">
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-newspaper"></i> Posts from Barangay {{ $name }}</div>

                        <div class="bfp-feed-tabs" id="bfpTabs">
                            <button class="bfp-tab active" data-tab="all">All</button>
                            <button class="bfp-tab" data-tab="event">Events</button>
                            <button class="bfp-tab" data-tab="announcement">Announcements</button>
                            <button class="bfp-tab" data-tab="activity">Activities</button>
                        </div>

                        <div id="bfpFeed">
                            @foreach($posts as $post)
                            <div class="bfp-post" data-post-type="{{ $post['type_class'] }}">
                                <div class="bfp-post-header">
                                    <div class="bfp-post-avatar">{{ strtoupper(substr($name,0,2)) }}</div>
                                    <div>
                                        <p class="bfp-post-author">{{ $post['author'] }}</p>
                                        <p class="bfp-post-meta">
                                            <span class="bfp-post-type {{ $post['type_class'] }}">{{ $post['type'] }}</span>
                                            {{ $post['posted_at'] }}
                                        </p>
                                    </div>
                                </div>
                                <h3 class="bfp-post-title">{{ $post['title'] }}</h3>
                                <p class="bfp-post-text">{{ $post['text'] }}</p>
                                <div class="bfp-post-detail"><i class="fas fa-calendar-alt"></i> {{ $post['date'] }} | {{ $post['time'] }}</div>
                                <div class="bfp-post-detail"><i class="fas fa-map-marker-alt"></i> {{ $post['venue'] }}</div>
                                <div class="bfp-post-detail"><i class="fas fa-users"></i> {{ $post['audience'] }}</div>
                                <div class="bfp-post-actions">
                                    <button class="bfp-action-btn"><i class="fas fa-thumbs-up"></i> Like</button>
                                    <button class="bfp-action-btn"><i class="fas fa-comment"></i> Comment</button>
                                    <button class="bfp-action-btn"><i class="fas fa-share"></i> Share</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
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

    document.querySelectorAll('.bfp-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.bfp-tab').forEach(function(t) {
                t.classList.remove('active');
                t.style.color = '#64748b';
                t.style.borderColor = 'transparent';
            });
            tab.classList.add('active');
            tab.style.color = '#213F99';
            tab.style.borderColor = '#213F99';
            var filter = tab.dataset.tab;
            document.querySelectorAll('#bfpFeed .bfp-post').forEach(function(post) {
                post.style.display = (filter === 'all' || post.dataset.postType === filter) ? 'block' : 'none';
            });
        });
    });
    </script>
</body>
</html>
