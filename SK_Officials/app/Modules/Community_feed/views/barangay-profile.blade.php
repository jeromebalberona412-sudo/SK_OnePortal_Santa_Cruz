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
        'app/Modules/Community_feed/assets/css/community-feed-comment-preview.css',
    ])
    <style>
        html:has(body.bfp-page) {
            overflow: hidden;
            height: 100%;
            max-width: 100%;
        }
        body.bfp-page {
            height: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .bfp-page .main-content {
            height: calc(100vh - var(--navbar-height, 64px));
            max-width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 0 20px;
        }
        .bfp-wrap {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            padding: 0;
            flex: 1;
            min-height: 0;
        }
        .bfp-back {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            width: auto;
            max-width: max-content;
            gap: 6px;
            padding: 6px 11px;
            margin: 12px 0;
            border-radius: 8px;
            flex-shrink: 0;
            background: #fff;
            border: 1px solid rgba(4,80,168,.16);
            box-shadow: 0 1px 4px rgba(4,80,168,.08);
            color: #0450a8;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
            text-decoration: none;
            white-space: nowrap;
            transition: background .2s, color .2s;
        }
        .bfp-back svg { width: 12px; height: 12px; flex-shrink: 0; }
        .bfp-back:hover { background:#f8faff; color:#033b7a; }
        .bfp-grid {
            display: grid;
            grid-template-columns: minmax(0,1fr) 320px;
            gap: 20px;
            align-items: start;
            width: 100%;
            min-width: 0;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        .bfp-main,.bfp-sidebar { display:flex;flex-direction:column;gap:16px;min-width:0; }
        .bfp-main {
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .bfp-main::-webkit-scrollbar { display: none; }
        .bfp-sidebar {
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .bfp-card { background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(44,44,62,.07); }
        .bfp-profile-head { display:flex;align-items:center;gap:14px;margin-bottom:16px; }
        .bfp-profile-copy { min-width:0; flex:1; }
        .bfp-avatar {
            width:72px;height:72px;border-radius:50%;
            background:#fff;
            border:3px solid #fff;
            box-shadow:0 4px 16px rgba(0,0,0,.12);
            display:flex;align-items:center;justify-content:center;
            font-size:24px;font-weight:900;color:#fff;
            overflow:hidden;flex-shrink:0;
        }
        .bfp-avatar img { width:100%;height:100%;object-fit:contain;display:block;background:#fff;padding:4px; }
        .bfp-name { font-size:18px;font-weight:800;color:#022a54;margin:0 0 4px;line-height:1.3;overflow-wrap:anywhere;word-break:break-word; }
        .bfp-loc  { font-size:12px;color:#64748b;margin:0;line-height:1.45; }
        .bfp-loc svg { color:#0450a8;width:14px;height:14px;vertical-align:-2px;margin-right:4px; }
        .bfp-stats { display:grid;grid-template-columns:minmax(0,0.85fr) minmax(0,1fr) minmax(0,1.4fr);gap:8px; }
        .bfp-stat { text-align:center;padding:10px 6px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;min-width:0; }
        .bfp-stat strong { display:block;font-size:16px;font-weight:800;color:#0450a8;line-height:1.2;white-space:normal;overflow-wrap:anywhere; }
        .bfp-stat--term strong { font-size:13px;white-space:nowrap;letter-spacing:-0.02em; }
        .bfp-stat span { display:block;font-size:10px;color:#64748b;margin-top:4px;text-transform:uppercase;letter-spacing:.04em; }

        /* Posts header */
        .bfp-posts-header {
            display:flex;align-items:center;gap:10px;
            padding:18px 22px 8px;border-bottom:none;
        }
        .bfp-posts-header h2 {
            display:flex;align-items:center;gap:8px;
            font-size:18px;font-weight:800;color:#022a54;margin:0;
            min-width:0;overflow-wrap:anywhere;
        }
        .bfp-posts-header h2 svg { width:20px;height:20px;color:#0450a8; }

        /* Feed filter tabs with icons */
        .bfp-feed-filter-bar {
            display:flex;flex-wrap:nowrap;gap:2px;
            margin:0 0 16px;border-bottom:2px solid #e2e8f0;
            padding:4px 0 0;width:100%;
            background:#fff;position:sticky;top:0;z-index:40;
            overflow:hidden;justify-content:stretch;
        }
        .bfp-feed-tab {
            display:inline-flex;flex-direction:column;align-items:center;justify-content:center;
            gap:4px;flex:1 1 0;min-width:0;
            padding:8px 4px 10px;
            background:none;border:none;
            border-bottom:3px solid transparent;
            color:#64748b;font-size:15px;font-weight:600;
            cursor:pointer;transition:all .2s;font-family:inherit;
            text-align:center;position:relative;bottom:0;
        }
        .bfp-feed-tab-icon {
            display:inline-flex;align-items:center;justify-content:center;
        }
        .bfp-feed-tab-icon svg { width:22px;height:22px;color:inherit;transition:color .2s,transform .2s; }
        .bfp-feed-tab-text {
            font-size:12px;line-height:1.2;font-weight:700;
            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;
        }
        .bfp-feed-tab:hover { color:#0450a8;background:#f8fafc; }
        .bfp-feed-tab:hover .bfp-feed-tab-icon svg { color:#1e40af;transform:translateY(-1px); }
        .bfp-feed-tab.active { color:#0450a8;border-bottom-color:#0450a8; }
        .bfp-feed-tab.active .bfp-feed-tab-icon svg { color:#1e40af; }

        /* Post cards */
        .bfp-post-card {
            background:#fff;border-radius:12px;padding:16px;
            border:1px solid #e8eef5;margin-bottom:14px;
            transition:box-shadow .2s;overflow:visible;
        }
        .bfp-post-card:hover { box-shadow:0 4px 16px rgba(44,44,62,.08); }
        .bfp-post-header { display:flex;align-items:center;gap:10px;margin-bottom:10px; }
        .bfp-post-avatar {
            width:44px;height:44px;border-radius:50%;flex-shrink:0;
            overflow:hidden;display:flex;align-items:center;justify-content:center;
            font-size:14px;font-weight:800;color:#fff;
        }
        .bfp-post-avatar img { width:100%;height:100%;object-fit:contain;display:block;background:#fff;padding:2px; }
        .bfp-post-info { flex:1;min-width:0; }
        .bfp-post-author { font-size:14px;font-weight:700;color:#1a1a2e;margin:0;overflow-wrap:anywhere; }
        .bfp-post-meta   { display:flex;align-items:center;gap:8px;font-size:12px;color:#94a3b8;margin-top:2px; }
        .bfp-post-type {
            display:inline-block;padding:3px 9px;border-radius:999px;
            font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
        }
        .bfp-post-type.event        { background:#fef3c7;color:#92400e; }
        .bfp-post-type.announcement { background:#dbeafe;color:#1d4ed8; }
        .bfp-post-type.activity     { background:#dcfce7;color:#15803d; }
        .bfp-post-type.program      { background:#f3e8ff;color:#7e22ce; }
        .bfp-post-type.update       { background:#e0f2fe;color:#0369a1; }
        .bfp-post-title { font-size:16px;font-weight:700;color:#1a1a2e;margin-bottom:6px;overflow-wrap:anywhere; }
        .bfp-post-text  { font-size:14px;color:#475569;line-height:1.6;margin-bottom:10px;white-space:pre-wrap;overflow-wrap:anywhere; }
        .bfp-post-image { width:100%;max-height:320px;object-fit:cover;border-radius:10px;margin-bottom:10px; }

        /* Engagement row */
        .bfp-post-engage {
            margin-top:2px;display:flex;align-items:center;justify-content:space-between;
            padding:8px 2px 10px;border-bottom:1px solid #f1f5f9;
        }
        .bfp-reaction-summary { display:inline-flex;align-items:center;gap:6px;font-size:14px;color:#65676b;border:none;background:none;font-family:inherit;cursor:pointer;padding:2px 6px;border-radius:6px;transition:background .15s; }
        button.bfp-reaction-summary:hover { background:#f0f2f5; }
        .bfp-reaction-faces { display:inline-flex;align-items:center; }
        .bfp-reaction-face { width:20px;height:20px;font-size:16px;line-height:1;display:inline-flex;align-items:center;justify-content:center; }
        .bfp-reaction-face + .bfp-reaction-face { margin-left:-4px; }
        .bfp-comment-count { font-size:14px;color:#65676b;cursor:pointer;border:none;background:none;font-family:inherit;padding:2px 4px;border-radius:6px;transition:background .15s; }
        .bfp-comment-count:hover { text-decoration:underline;background:#f0f2f5; }

        /* Action buttons */
        .bfp-post-actions {
            display:flex;align-items:center;gap:4px;padding-top:4px;
        }
        .bfp-action-btn {
            flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;
            padding:10px 8px;border:none;background:none;border-radius:8px;
            color:#65676b;font-size:14px;font-weight:600;cursor:pointer;
            font-family:inherit;transition:background .15s,color .15s;
        }
        .bfp-action-btn svg { width:18px;height:18px;flex-shrink:0; }
        .bfp-action-btn:hover { background:#f5f7fa;color:#1a1d25; }

        /* Officers card */
        .bfp-officers-card { background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(2,42,84,.06); }
        .bfp-officers-title {
            display:flex;align-items:center;gap:8px;
            font-size:14px;font-weight:700;color:#022a54;margin-bottom:14px;
        }
        .bfp-officers-title svg { width:20px;height:20px;color:#0450a8; }
        .bfp-officer-item { display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f0f0f0; }
        .bfp-officer-item:last-child { border-bottom:none; }
        .bfp-officer-dot {
            width:42px;height:42px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            font-size:13px;font-weight:800;color:#fff;flex-shrink:0;
            overflow:hidden;border:1px solid #e2e8f0;
        }
        .bfp-officer-dot.has-logo { background:#fff; }
        .bfp-officer-dot img { width:100%;height:100%;object-fit:contain;display:block;background:#fff; }
        .bfp-officer-name { font-size:14px;font-weight:700;color:#1a1d25;overflow-wrap:anywhere; }
        .bfp-officer-role { font-size:12px;color:#64748b;margin-top:1px;overflow-wrap:anywhere; }
        .bfp-empty { text-align:center;padding:28px 16px;color:#94a3b8;font-size:13px; }
        .bfp-empty i,.bfp-empty svg { font-size:28px;margin-bottom:10px;display:block;color:#cbd5e1; }
        .bfp-empty svg { width:28px;height:28px;margin:0 auto 10px; }

        .bfp-feed-empty { text-align:center;padding:28px 16px;color:#94a3b8;font-size:13px; }

        .bfp-sidebar-close,.bfp-sidebar-backdrop,.bfp-sidebar-fab { display:none; }

        /* Profile card */
        .bfp-profile-card { background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(2,42,84,.06); }

        @media (max-width:1024px) {
            html:has(body.bfp-page),
            html, body.bfp-page { height: auto; overflow-x: clip; overflow-y: visible !important; }
            html:has(body.bfp-page) { overflow-y: scroll !important; }
            .bfp-page .main-content { height: auto; overflow: visible; display: block; padding: 12px 12px 88px; }
            .bfp-wrap { display: flex; flex-direction: column; align-items: stretch; overflow: visible; height: auto; }
            .bfp-back { align-self: flex-start; width: auto; max-width: max-content; justify-content: flex-start; padding: 6px 10px; font-size: 12px; margin: 0 0 12px; }
            .bfp-grid { grid-template-columns:1fr; overflow: visible; height: auto; flex: none; }
            .bfp-main { overflow: visible; height: auto; scrollbar-width: auto; }
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
                background:linear-gradient(135deg,#0450a8,#667eea);color:#fff;
                font-size:13px;font-weight:700;font-family:inherit;
                box-shadow:0 4px 16px rgba(4,80,168,.35);cursor:pointer;
            }
            .bfp-profile-card { position:relative;padding-top:36px; }

            .bfp-feed-filter-bar { top:0;gap:0;padding:0;overflow:hidden;flex-wrap:nowrap; }
            .bfp-feed-tab { flex:1 1 0;min-width:0;max-width:none;padding:6px 1px 8px;gap:2px; }
            .bfp-feed-tab-icon svg { width:14px;height:14px; }
            .bfp-feed-tab-text { font-size:8px;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
            .bfp-post-actions { flex-wrap:nowrap; }
            .bfp-action-btn { font-size:13px;padding:8px 6px;min-width:0; }
            .bfp-posts-header { padding:14px 14px 4px; }
            .bfp-posts-header h2 { font-size:16px; }
        }
        @media (max-width:640px) {
            .bfp-wrap { padding: 0; }
            .bfp-back { align-self: flex-start; width: auto; max-width: max-content; justify-content: flex-start; }
            .bfp-sidebar-fab { bottom:18px; right:12px; padding:11px 14px; font-size:12px; }
        }
        @media (max-width:375px) {
            .bfp-avatar { width:56px;height:56px;font-size:18px; }
            .bfp-name { font-size:15px; }
            .bfp-stat strong { font-size:13px; }
            .bfp-stat--term strong { font-size:11px;white-space:normal; }
            .bfp-stat span { font-size:9px; }
            .bfp-feed-tab { padding:5px 0 7px; }
            .bfp-feed-tab-text { font-size:7px; }
            .bfp-feed-tab-icon svg { width:18px;height:18px; }
        }
    </style>
</head>
<body class="bfp-page">

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="bfp-wrap">
        <a href="{{ route('community-feed.index') }}" class="bfp-back" data-no-loading>
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span>Back to Community Feed</span>
        </a>

        <div class="bfp-grid">
            <div class="bfp-main">
                <div class="bfp-card">
                    <div class="bfp-posts-header">
                        <h2>
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/><path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/></svg>
                            Posts from Barangay {{ $name }}
                        </h2>
                    </div>

                    <div class="bfp-feed-filter-bar" id="bfpTabs">
                        <button type="button" class="bfp-feed-tab active" data-tab="all">
                            <span class="bfp-feed-tab-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            </span>
                            <span class="bfp-feed-tab-text">All</span>
                        </button>
                        <button type="button" class="bfp-feed-tab" data-tab="announcement">
                            <span class="bfp-feed-tab-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11 13v8a2 2 0 004 0v-6"/></svg>
                            </span>
                            <span class="bfp-feed-tab-text">Announcements</span>
                        </button>
                        <button type="button" class="bfp-feed-tab" data-tab="event">
                            <span class="bfp-feed-tab-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            </span>
                            <span class="bfp-feed-tab-text">Events</span>
                        </button>
                        <button type="button" class="bfp-feed-tab" data-tab="activity">
                            <span class="bfp-feed-tab-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z"/></svg>
                            </span>
                            <span class="bfp-feed-tab-text">Activities</span>
                        </button>
                        <button type="button" class="bfp-feed-tab" data-tab="program">
                            <span class="bfp-feed-tab-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                            </span>
                            <span class="bfp-feed-tab-text">Programs</span>
                        </button>
                    </div>

                    <div id="bfpFeed">
                        @forelse($posts as $post)
                            @php
                                $rc = $post['reaction_counts'] ?? [];
                                $reactionEmojis = ['like' => '👍', 'love' => '❤️', 'haha' => '😂', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'];
                                $topFaces = collect($rc)->filter(fn($c) => $c > 0)->sortDesc()->take(3)->keys()->all();
                                $commentCount = (int) ($post['comment_count'] ?? 0);
                            @endphp
                            <article class="bfp-post-card" data-post-type="{{ $post['type_class'] }}" data-post-id="{{ $post['id'] }}">
                                <div class="bfp-post-header">
                                    <div class="bfp-post-avatar" @if(empty($logo_url)) style="background:linear-gradient(135deg,#0450a8,#667eea);" @endif>
                                        @if(!empty($logo_url))
                                            <img src="{{ $logo_url }}" alt="">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div class="bfp-post-info">
                                        <p class="bfp-post-author">{{ $post['author_name'] ?? ('SK Brgy. '.$name) }}</p>
                                        <p class="bfp-post-meta">
                                            <span class="bfp-post-type {{ $post['type_class'] }}">{{ $post['type_label'] }}</span>
                                            <span>{{ $post['posted_time'] ?? $post['posted_at'] }}</span>
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
                                @if($post['likes'] > 0 || $commentCount > 0)
                                    <div class="bfp-post-engage">
                                        @if($post['likes'] > 0)
                                            <button type="button" class="bfp-reaction-summary bfp-view-reactions" data-post-id="{{ $post['id'] }}">
                                                <span class="bfp-reaction-faces">
                                                    @foreach($topFaces as $face)
                                                        <span class="bfp-reaction-face">{{ $reactionEmojis[$face] ?? '👍' }}</span>
                                                    @endforeach
                                                </span>
                                                <span>{{ $post['likes'] }}</span>
                                            </button>
                                        @else
                                            <div class="bfp-reaction-summary"></div>
                                        @endif
                                        @if($commentCount > 0)
                                            <button type="button" class="bfp-comment-count bfp-open-comments" data-post-id="{{ $post['id'] }}">{{ $commentCount }} {{ $commentCount === 1 ? 'comment' : 'comments' }}</button>
                                        @endif
                                    </div>
                                @endif
                                <div class="bfp-post-actions">
                                    <button type="button" class="bfp-action-btn bfp-view-reactions" data-post-id="{{ $post['id'] }}" {{ $post['likes'] > 0 ? '' : 'disabled' }}>
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                        <span>Like{{ $post['likes'] > 0 ? ' ('.$post['likes'].')' : '' }}</span>
                                    </button>
                                    <button type="button" class="bfp-action-btn bfp-open-comments" data-post-id="{{ $post['id'] }}">
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                        <span>View comments{{ $commentCount > 0 ? ' ('.$commentCount.')' : '' }}</span>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="bfp-feed-empty">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:28px;height:28px;margin:0 auto 10px;display:block;color:#cbd5e1;"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/><path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/></svg>
                                No posts yet from this barangay.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="bfp-sidebar" id="bfpSidebar">
                <button type="button" class="bfp-sidebar-close" id="bfpSidebarClose" aria-label="Close barangay profile">&times;</button>

                <div class="bfp-profile-card">
                    <div class="bfp-profile-head">
                        <div class="bfp-avatar" @if(empty($logo_url)) style="background:linear-gradient(135deg,#0450a8,#667eea);" @endif>
                            @if(!empty($logo_url))
                                <img src="{{ $logo_url }}" alt="SK Barangay {{ $name }} logo">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="bfp-profile-copy">
                            <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                            <p class="bfp-loc">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                {{ $location }}
                            </p>
                        </div>
                    </div>
                    <div class="bfp-stats">
                        <div class="bfp-stat"><strong>{{ $post_count }}</strong><span>Posts</span></div>
                        <div class="bfp-stat"><strong>{{ $officer_count }}</strong><span>Officers</span></div>
                        <div class="bfp-stat bfp-stat--term"><strong>{{ $term_label }}</strong><span>SK Term</span></div>
                    </div>
                </div>

                <div class="bfp-officers-card">
                    <div class="bfp-officers-title">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                        SK Officers
                    </div>
                    @forelse($officials as $official)
                        <div class="bfp-officer-item">
                            <div class="bfp-officer-dot{{ !empty($official['logo_url']) ? ' has-logo' : '' }}" @if(empty($official['logo_url'])) style="background:linear-gradient(135deg,#0450a8,#667eea);" @endif>
                                @if(!empty($official['logo_url']))
                                    <img src="{{ $official['logo_url'] }}" alt="">
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
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                            No SK officials found for this barangay.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>

        <div class="bfp-sidebar-backdrop" id="bfpSidebarBackdrop"></div>
        <button type="button" class="bfp-sidebar-fab" id="bfpSidebarFab" aria-label="View barangay profile and officers">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
            <span>Barangay Info</span>
        </button>
    </div>
</main>

@include('Community_feed::comment-preview')

<script>
window.BarangayProfilePosts = @json($posts ?? []);
window.CommentPreviewConfig = {
    post: @json($commentPreviewPost ?? null),
    defaultLogo: @json(asset('images/logo.png')),
    barangayLogo: @json($barangayLogoUrl ?? ''),
    userAvatar: @json($barangayLogoUrl ?: asset('images/SK_OnePortal_logo.png')),
    userDisplayName: @json($user->name ?? 'SK Official'),
    feedUrl: @json(route('community-feed.index')),
    viewOnly: @json(!($isOwnBarangay ?? false)),
};
</script>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Community_feed/assets/js/community-feed-comment-preview.js',
])

<script>
document.querySelectorAll('.bfp-feed-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.bfp-feed-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var filter = tab.dataset.tab;
        document.querySelectorAll('#bfpFeed .bfp-post-card').forEach(function(post) {
            post.style.display = (filter === 'all' || post.dataset.postType === filter) ? '' : 'none';
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

(function() {
    var posts = window.BarangayProfilePosts || [];
    function findPost(id) {
        return posts.find(function(p) { return Number(p.id) === Number(id); });
    }

    document.querySelectorAll('.bfp-open-comments').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var postId = this.dataset.postId;
            var post = findPost(postId);
            if (post && typeof window.openCommentPreview === 'function') {
                window.openCommentPreview(post);
            } else if (postId) {
                window.location.href = '/community-feed/comments/' + postId;
            }
        });
    });

    document.querySelectorAll('.bfp-view-reactions').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var postId = this.dataset.postId;
            var post = findPost(postId);
            if (post && typeof window.openReactionViewer === 'function') {
                window.openReactionViewer(post);
            }
        });
    });
})();
</script>
</body>
</html>
