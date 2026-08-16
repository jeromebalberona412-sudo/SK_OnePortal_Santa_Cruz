<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SK Barangay {{ $name }} - SK OnePortal</title>
    @vite([
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Layout/assets/css/kabataan-logout.css',
        'app/Modules/Layout/assets/js/kabataan-logout.js',
        'app/Modules/Profile/assets/css/profile.css',
        'app/Modules/Dashboard/assets/css/dashboard.css',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        body.youth-profile {
            background: linear-gradient(135deg,#f5f7fa 0%,#e8eef5 100%);
            overflow-x: hidden;
            overflow-y: auto;
            height: auto;
        }
        .profile-header-card { transform: none !important; }
        .brgy-header-card .profile-info-section {
            padding: 28px 32px 28px;
            align-items: center;
        }
        .brgy-header-card .profile-avatar-wrapper {
            margin-top: 0;
        }

        /* Normal page scroll ù left column sticky, right column scrolls with page */
        .profile-main {
            position: static !important;
            overflow: visible !important;
            padding-top: 88px !important;
            height: auto !important;
        }
        .profile-container {
            height: auto !important;
            overflow: visible !important;
            display: block !important;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px 40px !important;
        }
        .profile-content-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
            height: auto !important;
            overflow: visible !important;
        }
        .profile-left-column {
            position: sticky;
            top: 88px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(102,126,234,0.3) transparent;
        }
        .profile-left-column::-webkit-scrollbar { width: 5px; }
        .profile-left-column::-webkit-scrollbar-thumb { background: rgba(102,126,234,0.3); border-radius: 4px; }
        .profile-right-column {
            overflow: visible !important;
            height: auto !important;
        }

        @media (max-width: 1100px) {
            .profile-content-grid { grid-template-columns: 1fr !important; }
            .profile-left-column { position: static !important; max-height: none !important; overflow: visible !important; }
        }
        @media (max-width: 768px) {
            .profile-container { padding: 0 16px 32px !important; }
        }
        .brgy-header-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 24px rgba(2, 42, 84, 0.06);
        }
        .brgy-header-card .profile-name {
            font-size: 28px;
            color: #022a54;
            margin: 4px 0 6px;
        }
        .brgy-header-card .profile-location {
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }
        .brgy-header-card .profile-location svg {
            color: #0450a8;
        }
        .brgy-posts-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 22px 8px;
            border-bottom: none;
        }
        .brgy-posts-header h2 {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 800;
            color: #022a54;
            margin: 0;
        }
        .brgy-posts-header h2 svg {
            width: 20px;
            height: 20px;
            color: #0450a8;
        }
        .brgy-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #eff6ff; border: 1px solid #dbeafe;
            color: #0450a8; font-size: 11px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 999px; margin-bottom: 6px;
        }
        .brgy-stat-row {
            display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap;
        }
        .brgy-stat-item {
            text-align: left;
            min-width: 108px;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .brgy-stat-item strong { display: block; font-size: 18px; font-weight: 800; color: #0450a8; line-height: 1.2; }
        .brgy-stat-item span { font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #64748b; }
        .officer-list { display: flex; flex-direction: column; gap: 0; }
        .officer-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 0; border-bottom: 1px solid #f0f0f0;
        }
        .officer-item:last-child { border-bottom: none; }
        .officer-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0;
            overflow: hidden;
        }
        .officer-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .post-avatar-lg {
            width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: #fff;
        }
        .post-avatar-lg img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .post-image-wrap img {
            width: 100%; max-height: 320px; object-fit: cover;
            border-radius: 10px; margin-top: 12px;
        }
        .brgy-empty-state {
            text-align: center; padding: 28px 16px; color: #94a3b8; font-size: 13px;
        }
        .officer-details { flex: 1; }
        .officer-name  { font-size: 14px; font-weight: 700; color: #1a1d25; }
        .officer-role  { font-size: 12px; color: #999; margin-top: 1px; }
        .councilor-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px;
        }
        .councilor-chip {
            display: flex; align-items: center; gap: 8px;
            background: #f5f7fa; border-radius: 10px; padding: 8px 10px;
        }
        .councilor-dot {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .councilor-name { font-size: 11px; font-weight: 600; color: #444; }
        .contact-row {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px;
        }
        .contact-row:last-child { border-bottom: none; }
        .contact-icon {
            width: 34px; height: 34px; border-radius: 10px;
            background: #f0f4ff; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon svg { width: 16px; height: 16px; color: #667eea; }
        .contact-label { font-size: 11px; color: #999; }
        .contact-value { font-size: 13px; font-weight: 600; color: #333; }
        .feed-tab-bar {
            display: flex; gap: 8px; margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .feed-tab {
            display: inline-flex; align-items: center; gap: 6px;
            flex: 0 0 auto; bottom: auto;
            padding: 8px 14px; border: 1px solid #e2e8f0; background: #fff;
            font-size: 13px; font-weight: 600; color: #64748b;
            cursor: pointer; border-radius: 999px; transition: all 0.2s;
            border-bottom: 1px solid #e2e8f0;
        }
        .feed-tab svg { width: 15px; height: 15px; flex-shrink: 0; }
        .feed-tab.active { color: #fff; background: #0450a8; border-color: #0450a8; }
        .feed-tab:hover:not(.active) { color: #0450a8; border-color: #0450a8; }
    </style>
</head>
<body class="youth-profile">
    @include('dashboard::loading')

    {{-- KABATAAN HEADER (consistent across all pages) --}}
    @include('layout::kabataan-header')

    {{-- MAIN --}}
    <main class="profile-main">
        <div class="profile-container">

            {{-- PROFILE HEADER CARD --}}
            <div class="profile-header-card brgy-header-card">
                <div class="profile-info-section">
                    <div class="profile-avatar-wrapper">
                        <div style="width:150px;height:150px;border-radius:50%;background:{{ $color }};border:5px solid white;display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:900;color:#fff;box-shadow:0 4px 20px rgba(0,0,0,0.15);overflow:hidden;">
                            @if(!empty($logo_url))
                                <img src="{{ $logo_url }}" alt="Brgy. {{ $name }} logo" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ strtoupper(substr($name, 0, 2)) }}
                            @endif
                        </div>
                    </div>
                    <div class="profile-header-info">
                        <div class="brgy-badge">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:12px;height:12px;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Sangguniang Kabataan
                        </div>
                        <h1 class="profile-name">SK Barangay {{ $name }}</h1>
                        <p class="profile-location">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            {{ $location ?? ('Barangay ' . $name . ', Santa Cruz, Laguna') }}
                        </p>
                        <div class="brgy-stat-row">
                            <div class="brgy-stat-item"><strong>{{ $post_count ?? 0 }}</strong><span>Posts</span></div>
                            <div class="brgy-stat-item"><strong>{{ $officer_count ?? 0 }}</strong><span>Officers</span></div>
                            <div class="brgy-stat-item"><strong>{{ $term_label ?? 'ù' }}</strong><span>SK Term</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTENT GRID --}}
            <div class="profile-content-grid">

                {{-- LEFT COLUMN --}}
                <div class="profile-left-column">

                    {{-- SK Officers --}}
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                SK Officers
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="officer-list">
                            @forelse ($officials as $official)
                            <div class="officer-item">
                                <div class="officer-avatar" style="background:{{ $color }};">
                                    @if(!empty($official['logo_url']))
                                        <img src="{{ $official['logo_url'] }}" alt="{{ $official['name'] }}">
                                    @else
                                        {{ $official['initials'] }}
                                    @endif
                                </div>
                                <div class="officer-details">
                                    <p class="officer-name">{{ $official['name'] }}</p>
                                    <p class="officer-role">{{ $official['role'] }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="brgy-empty-state">No SK officials found for this barangay.</div>
                            @endforelse
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN ù FEED --}}
                <div class="profile-right-column">
                    <div class="info-card">
                        <div class="card-header brgy-posts-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/><path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/></svg>
                                Posts from Barangay {{ $name }}
                            </h2>
                        </div>
                        <div class="card-body" style="padding-top:0;">
                            <div class="feed-tab-bar" id="feedTabBar">
                                <button type="button" class="feed-tab active" data-tab="all">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                    All
                                </button>
                                <button type="button" class="feed-tab" data-tab="event">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    Events
                                </button>
                                <button type="button" class="feed-tab" data-tab="announcement">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11 13v8a2 2 0 004 0v-6"/></svg>
                                    Announcements
                                </button>
                                <button type="button" class="feed-tab" data-tab="activity">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z"/></svg>
                                    Activities
                                </button>
                            </div>

                            <div id="brgyFeed" style="display:flex;flex-direction:column;gap:16px;">
                                @forelse ($posts as $post)
                                <article class="post-card" data-post-type="{{ $post['type_class'] }}" style="box-shadow:none;border:1px solid #e8eef5;padding:18px;">
                                    <div class="post-header">
                                        <div class="post-avatar-lg" style="background:{{ $color }};">
                                            @if(!empty($post['logo_url']))
                                                <img src="{{ $post['logo_url'] }}" alt="SK Brgy. {{ $name }}">
                                            @else
                                                {{ $initials ?? strtoupper(substr($name, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="post-info">
                                            <h3 class="post-author">SK Brgy. {{ $name }}</h3>
                                            <p class="post-meta">
                                                <span class="post-type {{ $post['type_class'] }}">{{ $post['type_label'] ?? $post['type'] }}</span>
                                                <span class="post-time">{{ $post['posted_at'] }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="post-content">
                                        @if(!empty($post['title']))
                                            <h2 class="post-title">{{ $post['title'] }}</h2>
                                        @endif
                                        @if(!empty($post['text']))
                                            <p class="post-text">{{ $post['text'] }}</p>
                                        @endif
                                        @if(!empty($post['image_url']))
                                            <div class="post-image-wrap">
                                                <img src="{{ $post['image_url'] }}" alt="" loading="lazy">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="post-actions">
                                        <button class="action-btn" type="button" disabled style="opacity:0.7;cursor:default;">
                                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                            <span>Like ({{ $post['likes'] ?? 0 }})</span>
                                        </button>
                                        <button class="action-btn comment-btn" type="button" onclick="toggleBrgyComments({{ $post['id'] }})">
                                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                            <span>Comment ({{ $post['comment_count'] ?? count($post['comments'] ?? []) }})</span>
                                        </button>
                                    </div>
                                    @if(!empty($post['comments']))
                                    <div class="comments-section" id="brgy-comments-{{ $post['id'] }}" style="display:none;">
                                        @foreach ($post['comments'] as $comment)
                                        <div class="comment-item">
                                            <img src="{{ $comment['avatar_url'] }}" alt="{{ $comment['author_name'] }}">
                                            <div class="comment-content">
                                                <p class="comment-author">{{ $comment['author_name'] }}</p>
                                                <p class="comment-text">{{ $comment['body'] }}</p>
                                                @if(!empty($comment['time']))
                                                    <span class="comment-time">{{ $comment['time'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </article>
                                @empty
                                <div class="brgy-empty-state">No posts yet from this barangay.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- Logout Modal --}}
    <div id="logoutConfirmModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container" style="max-width:420px;">
            <div class="modal-header"><h2>Confirm Logout</h2>
                <button class="modal-close" onclick="document.getElementById('logoutConfirmModal').classList.remove('active')">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;padding:32px 24px;">
                <h3 style="font-size:18px;color:#333;margin-bottom:8px;">Are you sure you want to logout?</h3>
                <p style="color:#999;font-size:14px;margin-bottom:28px;">You will be redirected to the login page.</p>
                <div style="display:flex;gap:12px;justify-content:center;">
                    <button class="btn-secondary" onclick="document.getElementById('logoutConfirmModal').classList.remove('active')" style="min-width:100px;">Cancel</button>
                    <button class="btn-primary" id="confirmLogoutBtn" style="min-width:100px;background:linear-gradient(135deg,#f44336,#d32f2f);">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const logoutBtn  = document.querySelector('.logout-btn');
        const logoutForm = logoutBtn?.closest('form');
        const modal      = document.getElementById('logoutConfirmModal');
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        logoutBtn?.addEventListener('click', (e) => { e.preventDefault(); modal.classList.add('active'); });
        confirmBtn?.addEventListener('click', async () => {
            modal.classList.remove('active');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const logoutUrl = logoutForm?.getAttribute('action') || '/logout';
            try {
                await fetch(logoutUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
            } catch (error) {
                // Redirect to login even if the request fails
            }
            window.location.replace('/login');
        });
        modal?.querySelector('.modal-overlay')?.addEventListener('click', () => modal.classList.remove('active'));

        window.toggleBrgyComments = function (id) {
            const section = document.getElementById(`brgy-comments-${id}`);
            if (section) {
                section.style.display = section.style.display === 'none' ? 'block' : 'none';
            }
        };

        // Feed tab filter
        document.querySelectorAll('.feed-tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.feed-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const filter = tab.dataset.tab;
                document.querySelectorAll('#brgyFeed .post-card').forEach((card) => {
                    card.hidden = filter !== 'all' && card.dataset.postType !== filter;
                });
            });
        });
    })();
    </script>
    <script>
    window.addEventListener('unload', function () {});
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { window.location.replace(window.location.href); }
    });
    </script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
