<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>SK Barangay {{ $name }} - SK OnePortal</title>
    @vite([
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

    {{-- NAVBAR --}}
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
            </div>
            <div class="navbar-center">
                <div class="search-bar">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" placeholder="Search posts, programs, announcements..." class="search-input">
                </div>
            </div>
            <div class="navbar-right">
                @include('dashboard::notification')
                @include('dashboard::chatbot')
                <div class="user-menu">
                    <button class="user-avatar-btn">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=667eea&color=fff" alt="User Avatar">
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=667eea&color=fff" alt="User Avatar">
                            <div>
                                <p class="user-name">{{ $user->name ?? 'Youth User' }}</p>
                                <p class="user-email">{{ $user->email ?? 'youth@skportal.com' }}</p>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            My Profile
                        </a>
                        <a href="{{ route('settings') }}" class="dropdown-item">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

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
                        <div style="width:150px;height:150px;border-radius:50%;background:{{ $color }};border:5px solid white;display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:900;color:#fff;box-shadow:0 4px 20px rgba(0,0,0,0.15);">
                            {{ strtoupper(substr($name, 0, 2)) }}
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
                            Barangay {{ $name }}, Santa Cruz, Laguna
                        </p>
                        <div class="brgy-stat-row">
                            <div class="brgy-stat-item"><strong>{{ count($posts) }}</strong><span>Posts</span></div>
                            <div class="brgy-stat-item"><strong>{{ count($officers['councilors']) + 5 }}</strong><span>Officers</span></div>
                            <div class="brgy-stat-item"><strong>2023–2026</strong><span>SK Term</span></div>
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
                            @php
                            $officerList = [
                                ['name' => $officers['chairman'],  'role' => 'SK Chairman',              'opacity' => '1'],
                                ['name' => $officers['vice'],      'role' => 'Vice Chairman',             'opacity' => '0.85'],
                                ['name' => $officers['secretary'], 'role' => 'Secretary',                 'opacity' => '0.8'],
                                ['name' => $officers['treasurer'], 'role' => 'Treasurer',                 'opacity' => '0.8'],
                                ['name' => $officers['auditor'],   'role' => 'Auditor',                   'opacity' => '0.8'],
                                ['name' => $officers['pro'],       'role' => 'Public Relations Officer',  'opacity' => '0.8'],
                            ];
                            @endphp
                            <div class="officer-list">
                                @foreach ($officerList as $o)
                                <div class="officer-item">
                                    <div class="officer-avatar" style="background:{{ $color }};opacity:{{ $o['opacity'] }};">
                                        {{ strtoupper(substr(trim($o['name'], '[]'), 0, 2)) }}
                                    </div>
                                    <div class="officer-details">
                                        <p class="officer-name">{{ $o['name'] }}</p>
                                        <p class="officer-role">{{ $o['role'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#999;margin:16px 0 8px;">SK Councilors</p>
                            <div class="councilor-grid">
                                @foreach ($officers['councilors'] as $c)
                                <div class="councilor-chip">
                                    <div class="councilor-dot" style="background:{{ $color }};">
                                        {{ strtoupper(substr(trim($c, '[]'), 0, 2)) }}
                                    </div>
                                    <span class="councilor-name">{{ $c }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Barangay Info --}}
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                Barangay Information
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-item"><label>Barangay</label><p>{{ $name }}</p></div>
                                <div class="info-item"><label>Municipality</label><p>Santa Cruz</p></div>
                            </div>
                            <div class="info-row">
                                <div class="info-item"><label>Province</label><p>Laguna</p></div>
                                <div class="info-item"><label>Region</label><p>Region IV-A (CALABARZON)</p></div>
                            </div>
                            <div class="info-row">
                                <div class="info-item"><label>SK Term</label><p>2023 – 2026</p></div>
                                <div class="info-item"><label>Total Officers</label><p>{{ count($officers['councilors']) + 5 }}</p></div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="info-card">
                        <div class="card-header">
                            <h2>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                                Contact Information
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="contact-row">
                                <div class="contact-icon"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg></div>
                                <div><p class="contact-label">Phone</p><p class="contact-value">[SK Contact Number]</p></div>
                            </div>
                            <div class="contact-row">
                                <div class="contact-icon"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg></div>
                                <div><p class="contact-label">Email</p><p class="contact-value">[SK Email Address]</p></div>
                            </div>
                            <div class="contact-row">
                                <div class="contact-icon"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg></div>
                                <div><p class="contact-label">Office Address</p><p class="contact-value">Barangay {{ $name }} Hall, Santa Cruz, Laguna</p></div>
                            </div>
                            <div class="contact-row">
                                <div class="contact-icon"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg></div>
                                <div><p class="contact-label">Office Hours</p><p class="contact-value">Monday – Friday, 8:00 AM – 5:00 PM</p></div>
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
                                @foreach ($posts as $post)
                                <article class="post-card" data-post-type="{{ $post['type_class'] }}" style="box-shadow:none;border:1px solid #e8eef5;padding:18px;">
                                    <div class="post-header">
                                        <div style="width:44px;height:44px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0;">
                                            {{ strtoupper(substr($name, 0, 2)) }}
                                        </div>
                                        <div class="post-info">
                                            <h3 class="post-author">{{ $post['author'] }}</h3>
                                            <p class="post-meta">
                                                <span class="post-type {{ $post['type_class'] }}">{{ $post['type'] }}</span>
                                                <span class="post-time">{{ $post['posted_at'] }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="post-content">
                                        <h2 class="post-title">{{ $post['title'] }}</h2>
                                        <p class="post-text">{{ $post['text'] }}</p>
                                        <div class="post-details">
                                            <div class="detail-item">
                                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                                <span>{{ $post['date'] }} | {{ $post['time'] }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                                <span>{{ $post['venue'] }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                                <span>{{ $post['audience'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="post-actions">
                                        <button class="action-btn"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg><span>Like</span></button>
                                        <button class="action-btn"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg><span>Comment</span></button>
                                    </div>
                                </article>
                                @endforeach
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
