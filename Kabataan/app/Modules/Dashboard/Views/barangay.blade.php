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
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <style>
        body.youth-profile {
            background: linear-gradient(135deg,#f5f7fa 0%,#e8eef5 100%);
            overflow-x: hidden;
            overflow-y: auto;
            height: auto;
        }
        .profile-cover {
            animation: none !important;
            background: linear-gradient(135deg, {{ $color }} 0%, #022a54 100%) !important;
        }
        .cover-gradient { display: none; }
        .profile-header-card { transform: none !important; }

        /* Normal page scroll — left column sticky, right column scrolls with page */
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
        .brgy-cover-overlay {
            position: absolute; inset: 0;
            background-image: url('/images/Background.png');
            background-size: cover; background-position: center;
            opacity: 0.08; pointer-events: none;
        }
        .brgy-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35);
            color: #fff; font-size: 11px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            padding: 4px 12px; border-radius: 999px; margin-bottom: 6px;
        }
        .brgy-stat-row {
            display: flex; gap: 20px; margin-top: 8px; flex-wrap: wrap;
        }
        .brgy-stat-item { text-align: center; }
        .brgy-stat-item strong { display: block; font-size: 20px; font-weight: 800; color: #022a54; }
        .brgy-stat-item span { font-size: 12px; color: #999; }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: #667eea; font-size: 13px; font-weight: 600;
            text-decoration: none; margin-bottom: 16px;
            transition: color 0.2s;
        }
        .back-link:hover { color: #022a54; }
        .back-link svg { width: 16px; height: 16px; }
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
            display: flex; gap: 4px; margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0; padding-bottom: 0;
        }
        .feed-tab {
            padding: 10px 18px; border: none; background: none;
            font-size: 14px; font-weight: 600; color: #999;
            cursor: pointer; border-bottom: 2px solid transparent;
            margin-bottom: -2px; transition: all 0.2s;
        }
        .feed-tab.active { color: #667eea; border-bottom-color: #667eea; }
        .feed-tab:hover { color: #667eea; }
    </style>
</head>
<body class="youth-profile">
    @include('dashboard::loading')

    {{-- KABATAAN HEADER (consistent across all pages) --}}
    @include('layout::kabataan-header', ['showSearch' => true])

    {{-- MAIN --}}
    <main class="profile-main">
        <div class="profile-container">

            {{-- Back link --}}
            <a href="{{ route('dashboard') }}" class="back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to Dashboard
            </a>

            {{-- PROFILE HEADER CARD --}}
            <div class="profile-header-card">
                <div class="profile-cover" style="position:relative;">
                    <div class="cover-gradient"></div>
                    <div class="brgy-cover-overlay"></div>
                </div>
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
                            <div class="brgy-stat-item"><strong>{{ $term_label ?? '—' }}</strong><span>SK Term</span></div>
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

                {{-- RIGHT COLUMN — FEED --}}
                <div class="profile-right-column">
                    <div class="info-card">
                        <div class="card-header" style="border-bottom:none;padding-bottom:0;">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/><path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/></svg>
                                Posts from Barangay {{ $name }}
                            </h2>
                        </div>
                        <div class="card-body" style="padding-top:0;">
                            <div class="feed-tab-bar" id="feedTabBar">
                                <button class="feed-tab active" data-tab="all">All</button>
                                <button class="feed-tab" data-tab="event">Events</button>
                                <button class="feed-tab" data-tab="announcement">Announcements</button>
                                <button class="feed-tab" data-tab="activity">Activities</button>
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
        confirmBtn?.addEventListener('click', () => logoutForm.submit());
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
</body>
</html>
