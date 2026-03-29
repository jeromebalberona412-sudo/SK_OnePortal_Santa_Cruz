<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>SK Federation Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/modules/dashboard/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/community_feed/css/community-feed.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .skfp-main{padding:0;max-width:none;margin:0}
        .skfp-header-card{background:#fff;border-radius:0;overflow:hidden;box-shadow:0 2px 12px rgba(33,63,153,.08);margin-bottom:0}
        .skfp-cover{height:180px;background:linear-gradient(135deg,#0d1b4b 0%,#213F99 55%,#3a5fd9 100%);position:relative;overflow:hidden}
        .skfp-cover::after{content:'';position:absolute;inset:0;background-image:url('/modules/authentication/images/Background.png');background-size:cover;background-position:center;background-repeat:no-repeat;opacity:.08;mix-blend-mode:luminosity}
        .skfp-info-section{padding:0 28px 24px;display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap}
        .skfp-avatar-wrap{margin-top:-50px;flex-shrink:0;position:relative}
        .skfp-avatar{width:100px;height:100px;border-radius:50%;border:4px solid #fff;box-shadow:0 4px 16px rgba(33,63,153,.2);object-fit:cover}
        .skfp-avatar-edit{position:absolute;bottom:2px;right:2px;width:28px;height:28px;border-radius:50%;background:#213F99;border:2px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:11px}
        .skfp-meta{flex:1;padding-top:12px}
        .skfp-name{font-size:22px;font-weight:800;color:#0d1b4b;margin-bottom:2px}
        .skfp-sub{font-size:13px;color:#64748b;margin-bottom:10px}
        .skfp-stats{display:flex;gap:20px;flex-wrap:wrap}
        .skfp-stat strong{display:block;font-size:18px;font-weight:800;color:#213F99}
        .skfp-stat span{font-size:11px;color:#94a3b8}
        .skfp-actions{display:flex;gap:10px;align-items:center;padding-top:12px}
        .skfp-btn-primary{padding:9px 20px;background:#213F99;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s}
        .skfp-btn-primary:hover{background:#1a3280}
        .skfp-btn-ghost{padding:9px 20px;background:transparent;color:#213F99;border:2px solid #213F99;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s}
        .skfp-btn-ghost:hover{background:#213F99;color:#fff}
        .skfp-grid{display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start}
        .skfp-left,.skfp-right{display:flex;flex-direction:column;gap:16px}
        .skfp-content-wrap{padding:24px}
        .skfp-card{background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(33,63,153,.07)}
        .skfp-card-title{font-size:14px;font-weight:700;color:#0d1b4b;margin-bottom:14px;display:flex;align-items:center;gap:8px}
        .skfp-card-title i{color:#213F99}
        .skfp-info-row{display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
        .skfp-info-row:last-child{border-bottom:none}
        .skfp-info-icon{width:30px;height:30px;border-radius:8px;background:#eff3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .skfp-info-icon i{font-size:12px;color:#213F99}
        .skfp-info-label{font-size:11px;color:#94a3b8}
        .skfp-info-value{font-size:13px;font-weight:600;color:#1e293b}
        .skfp-officer-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9}
        .skfp-officer-item:last-child{border-bottom:none}
        .skfp-officer-dot{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#213F99,#3a5fd9);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
        .skfp-officer-name{font-size:13px;font-weight:700;color:#1e293b}
        .skfp-officer-role{font-size:11px;color:#94a3b8}
        .skfp-compose{background:#fff;border-radius:14px;padding:18px 22px;box-shadow:0 2px 8px rgba(33,63,153,.07)}
        .skfp-compose-row{display:flex;gap:12px;align-items:center;margin-bottom:12px}
        .skfp-compose-input{flex:1;padding:10px 16px;border:2px solid #e2e8f0;border-radius:24px;font-size:14px;font-family:inherit;cursor:pointer;background:#f8fafc;color:#64748b;transition:border-color .2s}
        .skfp-compose-input:focus{outline:none;border-color:#213F99;background:#fff}
        .skfp-compose-actions{display:flex;gap:8px;flex-wrap:wrap}
        .skfp-compose-type{padding:6px 14px;border:1px solid #e2e8f0;border-radius:20px;font-size:12px;font-weight:600;color:#64748b;background:#f8fafc;cursor:pointer;transition:all .2s}
        .skfp-compose-type:hover,.skfp-compose-type.active{background:#213F99;color:#fff;border-color:#213F99}
        .skfp-post{background:#fff;border-radius:14px;padding:18px 22px;box-shadow:0 2px 8px rgba(33,63,153,.07)}
        .skfp-post-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}
        .skfp-post-avatar{width:42px;height:42px;border-radius:50%;object-fit:cover}
        .skfp-post-author{font-size:14px;font-weight:700;color:#0d1b4b}
        .skfp-post-meta{font-size:12px;color:#94a3b8;margin-top:2px}
        .skfp-post-type{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:6px}
        .skfp-post-type.announcement{background:#dbeafe;color:#1d4ed8}
        .skfp-post-type.event{background:#fef3c7;color:#92400e}
        .skfp-post-type.activity{background:#dcfce7;color:#15803d}
        .skfp-post-type.program{background:#f3e8ff;color:#7e22ce}
        .skfp-post-title{font-size:16px;font-weight:700;color:#0d1b4b;margin-bottom:6px}
        .skfp-post-text{font-size:14px;color:#475569;line-height:1.6}
        .skfp-post-actions{display:flex;gap:8px;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9}
        .skfp-post-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:8px;border:none;background:none;border-radius:8px;font-size:13px;color:#64748b;cursor:pointer;transition:background .2s}
        .skfp-post-btn:hover{background:#f1f5f9;color:#213F99}
        .skfp-post-edit-btn{padding:6px 14px;background:#eff3ff;color:#213F99;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s}
        .skfp-post-edit-btn:hover{background:#213F99;color:#fff}
        .skfp-modal-wrap{display:none;position:fixed;inset:0;z-index:2000;align-items:center;justify-content:center}
        .skfp-modal-wrap.active{display:flex}
        .skfp-modal-overlay{position:absolute;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px)}
        .skfp-modal-box{position:relative;background:#fff;border-radius:16px;width:90%;max-width:560px;max-height:90vh;overflow-y:auto}
        .skfp-modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #e2e8f0}
        .skfp-modal-header h3{font-size:17px;font-weight:700;color:#0d1b4b}
        .skfp-modal-close{width:32px;height:32px;border:none;background:#f1f5f9;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center}
        .skfp-modal-body{padding:20px 22px}
        .skfp-form-group{margin-bottom:16px}
        .skfp-form-group label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em}
        .skfp-form-group input,.skfp-form-group select,.skfp-form-group textarea{width:100%;padding:10px 14px;border:2px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;transition:border-color .2s}
        .skfp-form-group input:focus,.skfp-form-group select:focus,.skfp-form-group textarea:focus{outline:none;border-color:#213F99}
        .skfp-form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .skfp-modal-footer{display:flex;gap:10px;justify-content:flex-end;padding:16px 22px;border-top:1px solid #e2e8f0}
        @media(max-width:1024px){.skfp-grid{grid-template-columns:1fr}.skfp-content-wrap{padding:16px}}
        @media(max-width:640px){.skfp-info-section{flex-direction:column;align-items:flex-start}.skfp-form-row{grid-template-columns:1fr}.skfp-info-section{padding:0 16px 20px}.skfp-actions{flex-wrap:wrap}}
    </style>
</head>
<body>
    <script>(function(){window.history.pushState(null,"",window.location.href);window.onpopstate=function(){window.history.pushState(null,"",window.location.href);}})();</script>
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
            <a href="{{ route('community-feed') }}" class="menu-item" data-tooltip="SK Community Feed"><i class="fas fa-rss"></i><span>SK Community Feed</span></a>
            <a href="{{ route('barangay-monitoring') }}" class="menu-item" data-tooltip="Barangay Monitoring"><i class="fas fa-map-marker-alt"></i><span>Barangay Monitoring</span></a>
            <a href="{{ route('kabataan-monitoring') }}" class="menu-item" data-tooltip="Kabataan Monitoring"><i class="fas fa-users"></i><span>Kabataan Monitoring</span></a>
            <div class="menu-divider"></div>
            <button type="button" class="menu-item logout-item" onclick="showLogoutModal()"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
        </nav>
    </aside>

    {{-- MAIN --}}
    <main class="main-content">

        {{-- HEADER CARD --}}
        <div class="skfp-header-card">
            <div class="skfp-cover"></div>
            <div class="skfp-info-section">
                <div class="skfp-avatar-wrap">
                    <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed" class="skfp-avatar">
                    <button class="skfp-avatar-edit" onclick="openEditModal()" title="Change photo"><i class="fas fa-camera"></i></button>
                </div>
                <div class="skfp-meta">
                    <h1 class="skfp-name">SK Federation Santa Cruz</h1>
                    <p class="skfp-sub">Sangguniang Kabataan Federation · Santa Cruz, Laguna</p>
                    <div class="skfp-stats">
                        <div class="skfp-stat"><strong>26</strong><span>Barangays</span></div>
                        <div class="skfp-stat"><strong>12</strong><span>Officers</span></div>
                        <div class="skfp-stat"><strong>2023–2026</strong><span>SK Term</span></div>
                    </div>
                </div>
                <div class="skfp-actions">
                    <button class="skfp-btn-primary" onclick="openPostModal()"><i class="fas fa-plus"></i> Create Post</button>
                    <button class="skfp-btn-ghost" onclick="openEditModal()"><i class="fas fa-edit"></i> Edit Profile</button>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="skfp-content-wrap" style="padding-bottom:0;">
        <div style="background:#dcfce7;color:#15803d;padding:12px 18px;border-radius:10px;margin-bottom:16px;font-size:14px;font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        </div>
        @endif

        {{-- CONTENT GRID --}}
        <div class="skfp-content-wrap">
        <div class="skfp-grid">

            {{-- LEFT --}}
            <div class="skfp-left">

                {{-- About --}}
                <div class="skfp-card">
                    <div class="skfp-card-title"><i class="fas fa-info-circle"></i> About</div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div><p class="skfp-info-label">Municipality</p><p class="skfp-info-value">Santa Cruz, Laguna</p></div>
                    </div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div><p class="skfp-info-label">SK Term</p><p class="skfp-info-value">2023 – 2026</p></div>
                    </div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-phone"></i></div>
                        <div><p class="skfp-info-label">Contact</p><p class="skfp-info-value">[SK Fed Contact Number]</p></div>
                    </div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-envelope"></i></div>
                        <div><p class="skfp-info-label">Email</p><p class="skfp-info-value">[SK Fed Email]</p></div>
                    </div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-clock"></i></div>
                        <div><p class="skfp-info-label">Office Hours</p><p class="skfp-info-value">Mon–Fri, 8:00 AM – 5:00 PM</p></div>
                    </div>
                    <div class="skfp-info-row">
                        <div class="skfp-info-icon"><i class="fas fa-building"></i></div>
                        <div><p class="skfp-info-label">Office Address</p><p class="skfp-info-value">Santa Cruz Municipal Hall, Laguna</p></div>
                    </div>
                </div>

                {{-- Officers --}}
                <div class="skfp-card">
                    <div class="skfp-card-title"><i class="fas fa-users"></i> SK Federation Officers</div>
                    @php
                    $officers = [
                        ['name'=>'[SK Federation Chairman]','role'=>'Chairman'],
                        ['name'=>'[Vice Chairman]','role'=>'Vice Chairman'],
                        ['name'=>'[Secretary]','role'=>'Secretary'],
                        ['name'=>'[Treasurer]','role'=>'Treasurer'],
                        ['name'=>'[Auditor]','role'=>'Auditor'],
                        ['name'=>'[PRO]','role'=>'Public Relations Officer'],
                    ];
                    @endphp
                    @foreach($officers as $o)
                    <div class="skfp-officer-item">
                        <div class="skfp-officer-dot">{{ strtoupper(substr(trim($o['name'],'[]'),0,2)) }}</div>
                        <div>
                            <p class="skfp-officer-name">{{ $o['name'] }}</p>
                            <p class="skfp-officer-role">{{ $o['role'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="skfp-right">

                {{-- Compose --}}
                <div class="skfp-compose">
                    <div class="skfp-compose-row">
                        <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed" class="skfp-post-avatar">
                        <input type="text" class="skfp-compose-input" placeholder="What's happening in SK Federation?" readonly onclick="openPostModal()">
                    </div>
                    <div class="skfp-compose-actions">
                        <button class="skfp-compose-type" onclick="openPostModal('announcement')"><i class="fas fa-bullhorn"></i> Announcement</button>
                        <button class="skfp-compose-type" onclick="openPostModal('event')"><i class="fas fa-calendar"></i> Event</button>
                        <button class="skfp-compose-type" onclick="openPostModal('activity')"><i class="fas fa-running"></i> Activity</button>
                        <button class="skfp-compose-type" onclick="openPostModal('program')"><i class="fas fa-tasks"></i> Program</button>
                    </div>
                </div>

                {{-- Posts --}}
                <div class="skfp-post">
                    <div class="skfp-post-header">
                        <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed" class="skfp-post-avatar">
                        <div>
                            <p class="skfp-post-author">SK Federation Santa Cruz</p>
                            <p class="skfp-post-meta"><span class="skfp-post-type announcement">Announcement</span> Mar 16, 2026 · 9:00 AM</p>
                        </div>
                        <button class="skfp-post-edit-btn" style="margin-left:auto;" onclick="openEditPostModal()"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <h3 class="skfp-post-title">Quarterly Assembly — April 5, 2026</h3>
                    <p class="skfp-post-text">The SK Federation Santa Cruz will hold its quarterly assembly on April 5, 2026 at the Municipal Hall. All SK officials are required to attend. Please confirm your attendance by March 30.</p>
                    <div class="skfp-post-actions">
                        <button class="skfp-post-btn"><i class="fas fa-thumbs-up"></i> Like (24)</button>
                        <button class="skfp-post-btn"><i class="fas fa-comment"></i> Comment (3)</button>
                        <button class="skfp-post-btn"><i class="fas fa-share"></i> Share</button>
                    </div>
                </div>

                <div class="skfp-post">
                    <div class="skfp-post-header">
                        <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Fed" class="skfp-post-avatar">
                        <div>
                            <p class="skfp-post-author">SK Federation Santa Cruz</p>
                            <p class="skfp-post-meta"><span class="skfp-post-type announcement">Announcement</span> Mar 10, 2026 · 8:00 AM</p>
                        </div>
                        <button class="skfp-post-edit-btn" style="margin-left:auto;" onclick="openEditPostModal()"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <h3 class="skfp-post-title">Q1 2026 Report Submission Deadline</h3>
                    <p class="skfp-post-text">Reminder: Submission of Barangay Program Reports for Q1 2026 is due on March 31. Please coordinate with your respective SK Chairpersons.</p>
                    <div class="skfp-post-actions">
                        <button class="skfp-post-btn"><i class="fas fa-thumbs-up"></i> Like (15)</button>
                        <button class="skfp-post-btn"><i class="fas fa-comment"></i> Comment (0)</button>
                        <button class="skfp-post-btn"><i class="fas fa-share"></i> Share</button>
                    </div>
                </div>

            </div>
        </div>
        </div>{{-- end skfp-content-wrap --}}
    </main>

    {{-- CREATE POST MODAL --}}
    <div class="skfp-modal-wrap" id="postModal">
        <div class="skfp-modal-overlay" onclick="closePostModal()"></div>
        <div class="skfp-modal-box">
            <div class="skfp-modal-header">
                <h3>Create Post</h3>
                <button class="skfp-modal-close" onclick="closePostModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('sk-fed-profile.post') }}">
                @csrf
                <div class="skfp-modal-body">
                    <div class="skfp-form-group">
                        <label>Post Type</label>
                        <select name="type" id="postTypeSelect">
                            <option value="announcement">Announcement</option>
                            <option value="event">Event</option>
                            <option value="activity">Activity</option>
                            <option value="program">Program</option>
                        </select>
                    </div>
                    <div class="skfp-form-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Post title..." required>
                    </div>
                    <div class="skfp-form-group">
                        <label>Content</label>
                        <textarea name="content" rows="4" placeholder="What's happening?" required></textarea>
                    </div>
                    <div class="skfp-form-row">
                        <div class="skfp-form-group">
                            <label>Date</label>
                            <input type="date" name="event_date">
                        </div>
                        <div class="skfp-form-group">
                            <label>Time</label>
                            <input type="time" name="event_time">
                        </div>
                    </div>
                    <div class="skfp-form-group">
                        <label>Venue / Location</label>
                        <input type="text" name="venue" placeholder="e.g. Municipal Hall">
                    </div>
                </div>
                <div class="skfp-modal-footer">
                    <button type="button" class="skfp-btn-ghost" onclick="closePostModal()">Cancel</button>
                    <button type="submit" class="skfp-btn-primary"><i class="fas fa-paper-plane"></i> Publish Post</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT PROFILE MODAL --}}
    <div class="skfp-modal-wrap" id="editModal">
        <div class="skfp-modal-overlay" onclick="closeEditModal()"></div>
        <div class="skfp-modal-box">
            <div class="skfp-modal-header">
                <h3>Edit SK Federation Profile</h3>
                <button class="skfp-modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="skfp-modal-body">
                <div class="skfp-form-group">
                    <label>Federation Name</label>
                    <input type="text" value="SK Federation Santa Cruz">
                </div>
                <div class="skfp-form-row">
                    <div class="skfp-form-group">
                        <label>Municipality</label>
                        <input type="text" value="Santa Cruz" readonly>
                    </div>
                    <div class="skfp-form-group">
                        <label>Province</label>
                        <input type="text" value="Laguna" readonly>
                    </div>
                </div>
                <div class="skfp-form-group">
                    <label>Contact Number</label>
                    <input type="text" placeholder="[SK Fed Contact Number]">
                </div>
                <div class="skfp-form-group">
                    <label>Email Address</label>
                    <input type="email" placeholder="[SK Fed Email]">
                </div>
                <div class="skfp-form-group">
                    <label>Office Address</label>
                    <input type="text" value="Santa Cruz Municipal Hall, Laguna">
                </div>
                <div class="skfp-form-group">
                    <label>About / Description</label>
                    <textarea rows="3" placeholder="Brief description of the SK Federation..."></textarea>
                </div>
                <p style="font-size:12px;color:#94a3b8;margin-top:4px;">Note: Profile editing is in prototype mode. Changes will not be saved to the database.</p>
            </div>
            <div class="skfp-modal-footer">
                <button type="button" class="skfp-btn-ghost" onclick="closeEditModal()">Cancel</button>
                <button type="button" class="skfp-btn-primary" onclick="closeEditModal()"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </div>

    {{-- LOGOUT MODAL --}}
    <div class="modal-overlay-logout" id="logoutModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:32px 28px;max-width:380px;width:90%;text-align:center;">
            <div style="width:56px;height:56px;background:#fff3e0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-sign-out-alt" style="color:#f97316;font-size:22px;"></i></div>
            <h3 style="font-size:18px;color:#0d1b4b;margin-bottom:8px;">Confirm Logout</h3>
            <p style="color:#64748b;font-size:14px;margin-bottom:24px;">Are you sure you want to logout?</p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button onclick="hideLogoutModal()" style="padding:10px 24px;border:2px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="padding:10px 24px;background:linear-gradient(135deg,#f44336,#d32f2f);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ url('/modules/dashboard/js/dashboard.js') }}"></script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    <script>
    function openPostModal(type) {
        if (type) document.getElementById('postTypeSelect').value = type;
        document.getElementById('postModal').classList.add('active');
    }
    function closePostModal() { document.getElementById('postModal').classList.remove('active'); }
    function openEditModal()  { document.getElementById('editModal').classList.add('active'); }
    function closeEditModal() { document.getElementById('editModal').classList.remove('active'); }
    function openEditPostModal() { openEditModal(); }
    function showLogoutModal() { const m = document.getElementById('logoutModal'); m.style.display = 'flex'; }
    function hideLogoutModal() { const m = document.getElementById('logoutModal'); m.style.display = 'none'; }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closePostModal(); closeEditModal(); hideLogoutModal(); }
    });
    </script>
</body>
</html>
