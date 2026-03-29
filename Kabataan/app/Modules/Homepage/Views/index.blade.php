<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $municipality['portal'] }}</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/js/homepage.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="homepage-body">

    {{-- ── LOADING OVERLAY ── --}}
    @include('dashboard::loading')

    {{-- ── TOP NAVBAR ── --}}
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
                    <input type="text" id="searchInput" placeholder="Search posts, programs, announcements..." class="search-input">
                </div>
            </div>

            <div class="navbar-right">
                <a href="{{ route('homepage') }}" class="nav-icon-btn" title="Home" aria-label="Home">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </a>
                <a href="{{ route('about') }}" class="nav-icon-btn" title="About" aria-label="About">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </a>
                <button type="button" class="nav-auth-btn solid" id="navLoginBtn">Login / Sign Up</button>
            </div>
        </div>
    </nav>

    {{-- ── MAIN ── --}}
    <main class="dashboard-main">
        <div class="dashboard-container">

            {{-- ── FEED ── --}}
            <div class="feed-section">
                <div class="feed-header">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div>
                            <h1>SK Community Feed</h1>
                            <p>Latest posts, events, and programs from SK officials in {{ $municipality['name'] }}.</p>
                        </div>
                    </div>

                    {{-- Filter tabs --}}
                    <div class="feed-filter-tabs" id="feedFilterTabs">
                        <button class="filter-tab active" data-filter="all">All</button>
                        <button class="filter-tab" data-filter="announcement">Announcements</button>
                        <button class="filter-tab" data-filter="event">Events</button>
                        <button class="filter-tab" data-filter="activity">Activities</button>
                        <button class="filter-tab" data-filter="program">Programs</button>
                    </div>
                </div>

                {{-- Feed cards --}}
                @foreach ($feedItems as $item)
                @php
                    $typeClass = match(strtolower($item['type'])) {
                        'event'        => 'event',
                        'activity'     => 'activity',
                        'announcement' => 'announcement',
                        'program'      => 'program',
                        default        => 'announcement',
                    };
                    $avatarColors = ['4CAF50','FF9800','2196F3','9C27B0','f44336','00BCD4','FF5722','607D8B'];
                    $color = $avatarColors[crc32($item['barangay']) % count($avatarColors)];
                @endphp
                <article
                    class="post-card"
                    data-post-type="{{ strtolower($item['type']) }}"
                    data-post-category="{{ strtolower($item['category']) }}"
                >
                    <div class="post-header">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($item['author']) }}&background={{ $color }}&color=fff" alt="{{ $item['author'] }}" class="post-avatar">
                        <div class="post-info">
                            <h3 class="post-author">{{ $item['author'] }}</h3>
                            <p class="post-meta">
                                <span class="post-type {{ $typeClass }}">{{ $item['type'] }}</span>
                                <span class="post-time">{{ $item['posted_at'] }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="post-content">
                        <h2 class="post-title">{{ $item['title'] }}</h2>
                        <p class="post-text">{{ $item['summary'] }}</p>

                        <div class="post-image">
                            <div class="image-placeholder" role="img" aria-label="Image placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                                <span>No image uploaded yet</span>
                            </div>
                        </div>

                        <div class="post-details">
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                <span>{{ $item['event_date'] }} | {{ $item['event_time'] }}</span>
                            </div>
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                <span>{{ $item['venue'] }}</span>
                            </div>
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                <span>{{ $item['audience'] }}</span>
                            </div>
                        </div>

                        @if(strtolower($item['type']) === 'program')
                        <div class="program-info">
                            <div class="info-badge">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
                                <span>{{ $item['category'] }}</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="view-details-btn program-apply-btn"
                            data-program-name="{{ $item['title'] }}"
                            data-program-category="{{ $item['category'] }}"
                        >
                            View Program Details & Apply
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        @endif
                    </div>

                    <div class="post-actions">
                        <button class="action-btn like-btn" data-liked="false">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                            <span>Like</span>
                        </button>
                        <button class="action-btn comment-btn">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                            <span>Comment</span>
                        </button>
                    </div>
                </article>
                @endforeach

                <div class="empty-state" id="emptyState" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin:0 auto 12px;display:block;color:#ccc;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3>No posts found</h3>
                    <p>Try a different filter.</p>
                </div>
            </div>

            {{-- ── RIGHT SIDEBAR ── --}}
            <aside class="programs-sidebar">
                <div class="sidebar-card">
                    <h2 class="sidebar-title">Programs by Category</h2>
                    <p class="sidebar-subtitle">Click a category to learn more. Login required to apply.</p>

                    <div class="program-categories">
                        @foreach ($programCategories as $cat)
                        <div class="program-category" data-category="{{ strtolower($cat['key']) }}" data-count="{{ $cat['count'] }}">
                            <div class="category-icon {{ strtolower($cat['key']) }}">
                                {!! $cat['icon'] !!}
                            </div>
                            <div class="category-content">
                                <h3>{{ $cat['label'] }}</h3>
                                <p>{{ $cat['count'] }} active {{ $cat['count'] === 1 ? 'program' : 'programs' }}</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Barangay Profiles --}}
                <div class="sidebar-card" style="margin-top:16px;">
                    <h2 class="sidebar-title">Barangay SK Profiles</h2>
                    <p class="sidebar-subtitle">Browse SK officials from each barangay.</p>
                    <div class="barangay-profiles-list">
                        @foreach ($barangayProfiles as $brgy)
                        <div class="brgy-profile-item"
                            data-brgy-name="{{ $brgy['name'] }}"
                            data-brgy-chairman="{{ $brgy['chairman'] }}"
                            data-brgy-members="{{ implode('|', $brgy['members']) }}"
                            data-brgy-color="{{ $brgy['color'] }}"
                            data-brgy-programs="{{ $brgy['programs'] }}"
                            data-brgy-events="{{ $brgy['events'] }}"
                            style="cursor:pointer;"
                        >
                            <div class="brgy-avatar" style="background: {{ $brgy['color'] }};">
                                {{ strtoupper(substr($brgy['name'], 0, 2)) }}
                            </div>
                            <div class="brgy-info">
                                <p class="brgy-name">Brgy. {{ $brgy['name'] }}</p>
                                <p class="brgy-chair">{{ $brgy['chairman'] }}</p>
                            </div>
                            <svg style="width:16px;height:16px;color:#bbb;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </main>

    {{-- ── LOGIN REQUIRED MODAL (programs / barangay) ── --}}
    <div id="loginRequiredModal" class="program-modal">
        <div class="modal-overlay" id="loginRequiredOverlay"></div>
        <div class="modal-container" style="max-width:460px;overflow:hidden;">
            {{-- Gradient banner --}}
            <div style="background:linear-gradient(135deg,#022a54 0%,#0450a8 55%,#667eea 100%);padding:32px 24px 24px;text-align:center;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;opacity:0.07;pointer-events:none;"></div>
                <button class="modal-close" id="loginRequiredClose" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,0.15);">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div style="font-size:48px;margin-bottom:12px;position:relative;z-index:1;">🎉</div>
                <h2 style="color:#fff;font-size:22px;font-weight:800;margin-bottom:6px;position:relative;z-index:1;">Join the Community!</h2>
                <p style="color:rgba(255,255,255,0.8);font-size:14px;position:relative;z-index:1;">Be part of the SK OnePortal youth community in Santa Cruz, Laguna.</p>
            </div>
            <div class="modal-body" style="padding:24px;">
                <p style="text-align:center;color:#555;font-size:15px;line-height:1.65;margin-bottom:8px;">
                    Want to explore <strong id="loginRequiredCategory" style="color:#0450a8;"></strong>? Login or create a free account to get full access.
                </p>
                <div style="display:flex;flex-direction:column;gap:10px;margin:20px 0;">
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#555;">
                        <span style="width:28px;height:28px;border-radius:50%;background:#e8f0ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">✅</span>
                        Apply for SK programs and track your applications
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#555;">
                        <span style="width:28px;height:28px;border-radius:50%;background:#e8f0ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">💬</span>
                        Like and comment on community posts
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#555;">
                        <span style="width:28px;height:28px;border-radius:50%;background:#e8f0ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">🏘️</span>
                        Browse barangay SK profiles and officers
                    </div>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('register') }}" class="btn-primary" style="text-decoration:none;padding:13px 0;flex:1;text-align:center;font-size:15px;">Sign Up — It's Free!</a>
                    <a href="{{ route('login') }}" class="btn-secondary" style="text-decoration:none;padding:13px 0;flex:1;text-align:center;font-size:15px;">Login</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ACTION LOGIN MODAL (like / comment) ── --}}
    <div id="actionLoginModal" class="program-modal">
        <div class="modal-overlay" id="actionLoginOverlay"></div>
        <div class="modal-container" style="max-width:420px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:28px 24px 20px;text-align:center;position:relative;">
                <button class="modal-close" id="actionLoginClose" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,0.15);">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div style="font-size:44px;margin-bottom:10px;">💬</div>
                <h2 style="color:#fff;font-size:20px;font-weight:800;margin-bottom:4px;">Join the Conversation!</h2>
                <p style="color:rgba(255,255,255,0.8);font-size:13px;">Your voice matters in the community.</p>
            </div>
            <div class="modal-body" style="padding:22px 24px;text-align:center;">
                <p style="color:#555;font-size:14px;line-height:1.65;margin-bottom:20px;">
                    Login or sign up to like posts, drop comments, and connect with SK officials and fellow youth in Santa Cruz.
                </p>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('register') }}" class="btn-primary" style="text-decoration:none;padding:12px 0;flex:1;text-align:center;">Sign Up Free</a>
                    <a href="{{ route('login') }}" class="btn-secondary" style="text-decoration:none;padding:12px 0;flex:1;text-align:center;">Login</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PROGRAM APPLY MODAL ── --}}
    <div id="programApplyModal" class="program-modal">
        <div class="modal-overlay" id="programApplyOverlay"></div>
        <div class="modal-container" style="max-width:460px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#16a34a 0%,#0d9488 100%);padding:28px 24px 20px;text-align:center;position:relative;">
                <button class="modal-close" id="programApplyClose" style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,0.15);">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div style="font-size:44px;margin-bottom:10px;">🎓</div>
                <h2 style="color:#fff;font-size:20px;font-weight:800;margin-bottom:4px;">Ready to Apply?</h2>
                <p style="color:rgba(255,255,255,0.85);font-size:13px;">One account, all SK programs at your fingertips.</p>
            </div>
            <div class="modal-body" style="padding:22px 24px;text-align:center;">
                <p style="color:#555;font-size:14px;line-height:1.65;margin-bottom:6px;">
                    You're one step away from applying for
                </p>
                <p id="programApplyName" style="font-size:16px;font-weight:700;color:#0450a8;margin-bottom:16px;"></p>
                <p style="color:#888;font-size:13px;margin-bottom:20px;">
                    Create a free account or login to submit your application and track its status anytime.
                </p>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('register') }}" class="btn-primary" style="text-decoration:none;padding:12px 0;flex:1;text-align:center;">Sign Up — It's Free!</a>
                    <a href="{{ route('login') }}" class="btn-secondary" style="text-decoration:none;padding:12px 0;flex:1;text-align:center;">Login</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
