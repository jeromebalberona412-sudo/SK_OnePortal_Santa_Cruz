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
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .bfp-wrap { padding: 24px; }
        .bfp-back { display:inline-flex;align-items:center;gap:6px;color:#b88600;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:16px;transition:color .2s; }
        .bfp-back:hover { color:#f5c518; }
        .bfp-header-card { background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(44,44,62,.10);margin-bottom:24px; }
        .bfp-cover { height:160px;background:linear-gradient(135deg,#2c2c3e 0%,#3a3a4a 100%);position:relative;overflow:hidden;border-bottom:3px solid #f5c518; }
        .bfp-cover::after { content:'';position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;background-position:center;opacity:.08; }
        .bfp-info-row { padding:0 28px 22px;display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap; }
        .bfp-avatar-wrap { margin-top:-50px;flex-shrink:0; }
        .bfp-avatar { width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);border:4px solid #f5c518;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:900;color:#f5c518;box-shadow:0 4px 16px rgba(0,0,0,.2);overflow:hidden; }
        .bfp-avatar img { width:100%;height:100%;object-fit:cover;display:block; }
        .bfp-meta { flex:1;padding-top:12px; }
        .bfp-badge { display:inline-flex;align-items:center;gap:5px;background:rgba(245,197,24,.12);color:#b88600;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:3px 10px;border-radius:999px;margin-bottom:4px;border:1px solid rgba(245,197,24,.25); }
        .bfp-name { font-size:20px;font-weight:800;color:#1a1a2e;margin-bottom:2px; }
        .bfp-loc  { font-size:13px;color:#64748b;margin-bottom:8px; }
        .bfp-stats { display:flex;gap:20px;flex-wrap:wrap; }
        .bfp-stat strong { display:block;font-size:18px;font-weight:800;color:#2c2c3e; }
        .bfp-stat span   { font-size:11px;color:#94a3b8; }
        .bfp-grid { display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start; }
        .bfp-left,.bfp-right { display:flex;flex-direction:column;gap:16px; }
        .bfp-card { background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(44,44,62,.07); }
        .bfp-card-title { font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px; }
        .bfp-card-title i { color:#b88600; }
        .bfp-officer-item { display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9; }
        .bfp-officer-item:last-child { border-bottom:none; }
        .bfp-officer-dot { width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#f5c518;flex-shrink:0; }
        .bfp-officer-name { font-size:13px;font-weight:700;color:#1e293b; }
        .bfp-officer-role { font-size:11px;color:#94a3b8; }
        .bfp-empty { text-align:center;padding:28px 16px;color:#94a3b8;font-size:13px; }
        .bfp-empty i { font-size:28px;margin-bottom:10px;display:block;color:#cbd5e1; }
        .bfp-feed-tabs { display:flex;gap:4px;margin-bottom:16px;border-bottom:2px solid #e2e8f0; }
        .bfp-tab { padding:9px 16px;border:none;background:none;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;font-family:inherit; }
        .bfp-tab.active { color:#b88600;border-bottom-color:#f5c518; }
        .bfp-tab:hover  { color:#b88600; }
        .bfp-post { border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px;transition:box-shadow .2s; }
        .bfp-post:hover { box-shadow:0 4px 16px rgba(44,44,62,.08); }
        .bfp-post-header { display:flex;align-items:center;gap:10px;margin-bottom:10px; }
        .bfp-post-avatar { width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#f5c518;flex-shrink:0;overflow:hidden; }
        .bfp-post-avatar img { width:100%;height:100%;object-fit:cover;display:block; }
        .bfp-post-author { font-size:13px;font-weight:700;color:#1a1a2e; }
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
        @media (max-width:1024px) { .bfp-grid { grid-template-columns:1fr; } }
        @media (max-width:640px) { .bfp-wrap{padding:16px;} .bfp-info-row{padding:0 16px 18px;} }
    </style>
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="bfp-wrap">
        <a href="{{ route('community-feed.index') }}" class="bfp-back">
            <i class="fas fa-arrow-left"></i> Back to Community Feed
        </a>

        <div class="bfp-header-card">
            <div class="bfp-cover"></div>
            <div class="bfp-info-row">
                <div class="bfp-avatar-wrap">
                    <div class="bfp-avatar">
                        @if(!empty($logo_url))
                            <img src="{{ $logo_url }}" alt="SK Barangay {{ $name }} logo">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                </div>
                <div class="bfp-meta">
                    <div class="bfp-badge"><i class="fas fa-check-circle" style="font-size:10px;"></i> Sangguniang Kabataan</div>
                    <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                    <p class="bfp-loc">
                        <i class="fas fa-map-marker-alt" style="color:#b88600;margin-right:4px;"></i>{{ $location }}
                    </p>
                    <div class="bfp-stats">
                        <div class="bfp-stat"><strong>{{ $post_count }}</strong><span>Posts</span></div>
                        <div class="bfp-stat"><strong>{{ $officer_count }}</strong><span>Officers</span></div>
                        <div class="bfp-stat"><strong>{{ $term_label }}</strong><span>SK Term</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bfp-grid">
            <div class="bfp-left">
                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-users"></i> SK Officers</div>
                    @forelse($officials as $official)
                        <div class="bfp-officer-item">
                            <div class="bfp-officer-dot">{{ $official['initials'] }}</div>
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
            </div>

            <div class="bfp-right">
                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-newspaper"></i> Posts from Barangay {{ $name }}</div>
                    <div class="bfp-feed-tabs">
                        <button class="bfp-tab active" data-tab="all">All</button>
                        <button class="bfp-tab" data-tab="event">Events</button>
                        <button class="bfp-tab" data-tab="announcement">Announcements</button>
                        <button class="bfp-tab" data-tab="activity">Activities</button>
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
        </div>
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
</script>
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
