<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK Barangay {{ $name }} - SK Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Community_feed/assets/css/community-feed.css',
    ])
    <style>
        html:has(body.bfp-page) {
            overflow-x: clip;
            overflow-y: scroll;
            height: auto;
            max-width: 100%;
        }
        html, body.bfp-page {
            height: auto;
            max-width: 100%;
            overflow-x: clip;
            overflow-y: visible;
        }
        .bfp-page .main-content {
            height: auto;
            min-height: calc(100vh - var(--navbar-height, 64px));
            max-width: 100%;
            overflow: visible;
            display: block;
            padding: 16px 20px 24px;
        }
        .bfp-wrap {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            min-width: 0;
            max-width: 100%;
            overflow: visible;
            padding: 0;
        }
        .bfp-back {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            width: auto;
            max-width: max-content;
            gap: 6px;
            padding: 6px 11px;
            margin: 0 0 14px;
            border-radius: 8px;
            background: #fff;
            border: 1px solid rgba(44,44,62,.12);
            box-shadow: 0 1px 4px rgba(44,44,62,.08);
            color: #b88600;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;
            white-space: nowrap;
            transition: background .2s, color .2s;
        }
        .bfp-back:hover { background:#fffbeb; color:#9a7200; }
        .bfp-grid {
            display: grid;
            grid-template-columns: minmax(0,1fr) 320px;
            gap: 20px;
            align-items: start;
            width: 100%;
            min-width: 0;
            overflow: visible;
        }
        .bfp-main,.bfp-sidebar { display:flex;flex-direction:column;gap:16px;min-width:0; }
        .bfp-main { overflow: visible; }
        .bfp-sidebar {
            position: sticky;
            top: calc(var(--navbar-height, 64px) + 12px);
            max-height: calc(100vh - var(--navbar-height, 64px) - 24px);
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .bfp-card { background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(44,44,62,.07); }
        .bfp-profile-head { display:flex;align-items:center;gap:14px;margin-bottom:16px; }
        .bfp-profile-copy { min-width:0; flex:1; }
        .bfp-avatar { width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);border:3px solid #f5c518;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#f5c518;box-shadow:0 4px 16px rgba(0,0,0,.16);overflow:hidden;flex-shrink:0; }
        .bfp-avatar img { width:100%;height:100%;object-fit:contain;display:block;background:#fff;padding:4px; }
        .bfp-name { font-size:18px;font-weight:800;color:#1a1a2e;margin:0 0 4px;line-height:1.3; }
        .bfp-loc  { font-size:12px;color:#64748b;margin:0;line-height:1.45; }
        .bfp-stats { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px; }
        .bfp-stat { text-align:center;padding:10px 8px;border-radius:10px;background:#f8fafc;border:1px solid #eef2f7; }
        .bfp-stat strong { display:block;font-size:17px;font-weight:800;color:#2c2c3e;line-height:1.2; }
        .bfp-stat span { display:block;font-size:10px;color:#94a3b8;margin-top:4px;text-transform:uppercase;letter-spacing:.04em; }
        .bfp-card-title { font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px; }
        .bfp-card-title i { color:#b88600; }
        .bfp-officer-item { display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9; }
        .bfp-officer-item:last-child { border-bottom:none; }
        .bfp-officer-dot { width:36px;height:36px;border-radius:50%;background:#fff;border:2px solid rgba(245,197,24,.35);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#2c2c3e;flex-shrink:0;overflow:hidden; }
        .bfp-officer-dot img { width:100%;height:100%;object-fit:contain;display:block;background:#fff;padding:2px; }
        .bfp-officer-name { font-size:13px;font-weight:700;color:#1e293b;margin:0; }
        .bfp-officer-role { font-size:11px;color:#94a3b8;margin:2px 0 0; }
        .bfp-empty { text-align:center;padding:28px 16px;color:#94a3b8;font-size:13px; }
        .bfp-empty i { font-size:28px;margin-bottom:10px;display:block;color:#cbd5e1; }
        .bfp-feed-tabs { display:flex;flex-wrap:nowrap;gap:0;margin-bottom:16px;border-bottom:2px solid #e2e8f0;overflow:hidden;width:100%; }
        .bfp-tab { flex:1 1 0;min-width:0;padding:9px 8px;border:none;background:none;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;font-family:inherit;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .bfp-tab.active { color:#b88600;border-bottom-color:#f5c518; }
        .bfp-tab:hover  { color:#b88600; }
        .bfp-post { border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px;transition:box-shadow .2s; }
        .bfp-post:hover { box-shadow:0 4px 16px rgba(44,44,62,.08); }
        .bfp-post-header { display:flex;align-items:center;gap:10px;margin-bottom:10px; }
        .bfp-post-avatar { width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#f5c518;flex-shrink:0;overflow:hidden; }
        .bfp-post-avatar img { width:100%;height:100%;object-fit:contain;display:block;background:#fff;padding:2px; }
        .bfp-post-author { font-size:13px;font-weight:700;color:#1a1a2e;margin:0; }
        .bfp-post-meta   { font-size:11px;color:#94a3b8;margin-top:2px; }
        .bfp-post-type { display:inline-block;padding:2px 7px;border-radius:8px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:4px; }
        .bfp-post-type.event        { background:#fef3c7;color:#92400e; }
        .bfp-post-type.announcement { background:rgba(245,197,24,.15);color:#b88600; }
        .bfp-post-type.activity     { background:#dcfce7;color:#15803d; }
        .bfp-post-type.program      { background:#ede9fe;color:#6d28d9; }
        .bfp-post-type.update       { background:#e0f2fe;color:#0369a1; }
        .bfp-post-title { font-size:15px;font-weight:700;color:#1a1a2e;margin-bottom:6px; }
        .bfp-post-text  { font-size:13px;color:#475569;line-height:1.6;margin-bottom:10px;white-space:pre-wrap; }
        .bfp-post-image { width:100%;max-height:320px;object-fit:cover;border-radius:10px;margin-bottom:10px; }
        .bfp-post-stats { display:flex;gap:16px;margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:12px;color:#64748b; }
        .bfp-post-stats span { display:inline-flex;align-items:center;gap:5px; }
        .bfp-sidebar-close,.bfp-sidebar-backdrop,.bfp-sidebar-fab { display:none; }
        @media (max-width:1024px) {
            html:has(body.bfp-page),
            html, body.bfp-page { height: auto; overflow-x: clip; overflow-y: visible; }
            html:has(body.bfp-page) { overflow-y: scroll; }
            .bfp-page .main-content { height: auto; overflow: visible; display: block; padding: 12px 12px 88px; }
            .bfp-wrap { display: flex; flex-direction: column; align-items: stretch; overflow: visible; height: auto; }
            .bfp-back { align-self: flex-start; width: auto; max-width: max-content; justify-content: flex-start; padding: 6px 10px; font-size: 12px; margin: 0 0 12px; }
            .bfp-grid { grid-template-columns:1fr; overflow: visible; height: auto; }
            .bfp-main { overflow: visible; height: auto; }
            .bfp-sidebar {
                position: fixed;
                top: var(--navbar-height, 64px);
                right: 0;
                width: min(320px, 92vw);
                height: calc(100vh - var(--navbar-height, 64px));
                max-height: none;
                padding: 16px 14px 24px;
                background: #f5f7fb;
                box-shadow: -4px 0 24px rgba(0, 0, 0, 0.14);
                z-index: 1060;
                transform: translateX(110%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
            }
            .bfp-sidebar.drawer-open { transform: translateX(0); }
            .bfp-sidebar-close {
                display:flex;align-items:center;justify-content:center;
                position:absolute;top:10px;right:10px;width:32px;height:32px;
                border:none;border-radius:8px;background:#fff;color:#64748b;
                font-size:22px;line-height:1;cursor:pointer;
                box-shadow:0 2px 8px rgba(0,0,0,.08);z-index:2;
            }
            .bfp-sidebar-backdrop { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1050;backdrop-filter:blur(2px); }
            .bfp-sidebar-backdrop.active { display:block; }
            .bfp-sidebar-fab {
                display:inline-flex;position:fixed;bottom:24px;right:16px;z-index:1040;
                align-items:center;gap:8px;padding:12px 16px;border:none;border-radius:999px;
                background:linear-gradient(135deg,#2c2c3e,#3a3a4a);color:#f5c518;
                font-size:13px;font-weight:700;font-family:inherit;
                box-shadow:0 4px 16px rgba(44,44,62,.35);cursor:pointer;
            }
            .bfp-profile-card { position:relative;padding-top:36px; }
        }
        @media (max-width:640px) {
            .bfp-wrap { padding: 0; }
            .bfp-back { align-self: flex-start; width: auto; max-width: max-content; justify-content: flex-start; }
            .bfp-feed-tabs { overflow:hidden; flex-wrap:nowrap; gap:0; }
            .bfp-tab { flex:1 1 0; min-width:0; text-align:center; padding:6px 2px; font-size:9px; }
            .bfp-sidebar-fab { bottom:18px; right:12px; padding:11px 14px; font-size:12px; }
        }
    </style>
</head>
<body class="bfp-page">

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="bfp-wrap">
        <a href="{{ route('community-feed.index') }}" class="bfp-back" data-no-loading>
            <i class="fas fa-arrow-left"></i> Back to Community Feed
        </a>

        <div class="bfp-grid">
            <div class="bfp-main">
                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-newspaper"></i> Posts from Barangay {{ $name }}</div>
                    <div class="bfp-feed-tabs" id="bfpTabs">
                        <button type="button" class="bfp-tab active" data-tab="all">All</button>
                        <button type="button" class="bfp-tab" data-tab="event">Events</button>
                        <button type="button" class="bfp-tab" data-tab="announcement">Announcements</button>
                        <button type="button" class="bfp-tab" data-tab="activity">Activities</button>
                    </div>
                    <div id="bfpFeed">
                        @forelse($posts as $post)
                            <div class="bfp-post" data-post-type="{{ $post['type_class'] }}">
                                <div class="bfp-post-header">
                                    <div class="bfp-post-avatar">
                                        @if(!empty($logo_url))
                                            <img src="{{ $logo_url }}" alt="">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="bfp-post-author">SK Brgy. {{ $name }}</p>
                                        <p class="bfp-post-meta">
                                            <span class="bfp-post-type {{ $post['type_class'] }}">{{ $post['type_label'] }}</span>
                                            {{ $post['posted_at'] }}
                                        </p>
                                    </div>
                                </div>
                                @if(!empty($post['title']))
                                    <h3 class="bfp-post-title">{{ $post['title'] }}</h3>
                                @endif
                                @if(!empty($post['body']))
                                    <p class="bfp-post-text">{{ $post['body'] }}</p>
                                @endif
                                @if(!empty($post['image_url']))
                                    <img src="{{ $post['image_url'] }}" alt="" class="bfp-post-image">
                                @endif
                                <div class="bfp-post-stats">
                                    <span><i class="fas fa-thumbs-up"></i> {{ $post['likes'] }}</span>
                                    <span><i class="fas fa-comment"></i> {{ $post['comments'] }}</span>
                                    @if(!empty($post['posted_time']))
                                        <span><i class="fas fa-clock"></i> {{ $post['posted_time'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="bfp-empty">
                                <i class="fas fa-newspaper"></i>
                                No posts yet from this barangay.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="bfp-sidebar" id="bfpSidebar">
                <button type="button" class="bfp-sidebar-close" id="bfpSidebarClose" aria-label="Close barangay profile">&times;</button>

                <div class="bfp-card bfp-profile-card">
                    <div class="bfp-profile-head">
                        <div class="bfp-avatar">
                            @if(!empty($logo_url))
                                <img src="{{ $logo_url }}" alt="SK Barangay {{ $name }} logo">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="bfp-profile-copy">
                            <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                            <p class="bfp-loc"><i class="fas fa-map-marker-alt" style="color:#b88600;margin-right:4px;"></i>{{ $location }}</p>
                        </div>
                    </div>
                    <div class="bfp-stats">
                        <div class="bfp-stat"><strong>{{ $post_count }}</strong><span>Posts</span></div>
                        <div class="bfp-stat"><strong>{{ $officer_count }}</strong><span>Officers</span></div>
                        <div class="bfp-stat"><strong>{{ $term_label }}</strong><span>SK Term</span></div>
                    </div>
                </div>

                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-users"></i> SK Officers</div>
                    @forelse($officials as $official)
                        <div class="bfp-officer-item">
                            <div class="bfp-officer-dot">
                                @if(!empty($official['logo_url']))
                                    <img src="{{ $official['logo_url'] }}" alt="Barangay {{ $name }} logo">
                                @else
                                    {{ $official['initials'] }}
                                @endif
                            </div>
                            <div>
                                <p class="bfp-officer-name">{{ $official['name'] }}</p>
                                <p class="bfp-officer-role">{{ $official['role'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="bfp-empty">
                            <i class="fas fa-user-friends"></i>
                            No SK officials found for this barangay.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>

        <div class="bfp-sidebar-backdrop" id="bfpSidebarBackdrop"></div>
        <button type="button" class="bfp-sidebar-fab" id="bfpSidebarFab" aria-label="View barangay profile and officers">
            <i class="fas fa-id-card"></i>
            <span>Barangay Info</span>
        </button>
    </div>
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
])

<script>
document.querySelectorAll('.bfp-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.bfp-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var filter = tab.dataset.tab;
        document.querySelectorAll('#bfpFeed .bfp-post').forEach(function(post) {
            post.style.display = (filter === 'all' || post.dataset.postType === filter) ? 'block' : 'none';
        });
    });
});

(function () {
    var fab = document.getElementById('bfpSidebarFab');
    var sidebar = document.getElementById('bfpSidebar');
    var backdrop = document.getElementById('bfpSidebarBackdrop');
    var closeBtn = document.getElementById('bfpSidebarClose');
    if (!fab || !sidebar || !backdrop) return;

    function openDrawer() {
        sidebar.classList.add('drawer-open');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        sidebar.classList.remove('drawer-open');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    fab.addEventListener('click', openDrawer);
    closeBtn?.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });
})();
</script>
</body>
</html>
