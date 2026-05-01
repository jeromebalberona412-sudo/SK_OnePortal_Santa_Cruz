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
            <a href="{{ route('homepage') }}" class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
            </a>

            <div class="navbar-links">
                <a href="{{ route('homepage') }}" class="nav-link active">Home</a>
                <a href="{{ route('about') }}" class="nav-link">About</a>
                <a href="{{ route('about') }}#services" class="nav-link">Services</a>
                <a href="{{ route('about') }}#barangay" class="nav-link">Barangay</a>
                <a href="{{ route('about') }}#contact" class="nav-link">Contact</a>
            </div>

            <div class="navbar-right">
                <button type="button" class="nav-btn solid" id="navLoginBtn">Login</button>
                <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    {{-- Mobile drawer --}}
    <div class="nav-drawer" id="navDrawer">
        <a href="{{ route('homepage') }}" class="nav-link active">Home</a>
        <a href="{{ route('about') }}" class="nav-link">About</a>
        <a href="{{ route('about') }}#services" class="nav-link">Services</a>
        <a href="{{ route('about') }}#barangay" class="nav-link">Barangay</a>
        <a href="{{ route('about') }}#contact" class="nav-link">Contact</a>
        <div class="nav-drawer-actions">
            <button type="button" class="nav-btn solid" id="navDrawerLoginBtn">Login</button>
        </div>
    </div>

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
                {{-- DEMO POST WITH IMAGE COLLAGE - TEAM BUILDING --}}
                <article class="post-card" data-post-type="activity" data-post-category="activity" id="demoTeamBuildingPost">
                    <div class="post-header">
                        <img src="https://ui-avatars.com/api/?name=Maria+Santos&background=FF9800&color=fff" alt="Maria Santos" class="post-avatar">
                        <div class="post-info">
                            <h3 class="post-author">Maria Santos</h3>
                            <p class="post-meta">
                                <span class="post-type activity">Activity</span>
                                <span class="post-time">2 hours ago</span>
                            </p>
                        </div>
                    </div>

                    <div class="post-content">
                        <h2 class="post-title">Team Building Activity 2026 - Unforgettable Bonding!</h2>
                        <p class="post-text">What an amazing day with our SK team! We participated in various team building activities including trust falls, relay races, and group challenges. Great teamwork and camaraderie from everyone! 🤝💪🎉</p>

                        {{-- Image Collage (4 images) --}}
                        <div class="image-collage collage-4">
                            <div class="collage-item">
                                <img src="{{ asset('modules/homepage/image/1.png') }}" alt="Team building activity 1">
                            </div>
                            <div class="collage-item">
                                <img src="{{ asset('modules/homepage/image/2.png') }}" alt="Team building activity 2">
                            </div>
                            <div class="collage-item">
                                <img src="{{ asset('modules/homepage/image/3.png') }}" alt="Team building activity 3">
                            </div>
                            <div class="collage-item more-overlay" data-count="+2">
                                <div class="collage-more-text">+2</div>
                                <img src="{{ asset('modules/homepage/image/4.png') }}" alt="Team building activity 4" style="display:none;">
                                <img src="{{ asset('modules/homepage/image/5.png') }}" alt="Team building activity 5" style="display:none;">
                            </div>
                        </div>

                        <div class="post-details">
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                                <span>May 1, 2026 | 10:00 AM</span>
                            </div>
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                <span>Barangay Community Center</span>
                            </div>
                            <div class="detail-item">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                <span>45+ participants</span>
                            </div>
                        </div>
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
        <div class="modal-container" style="max-width:440px;overflow:hidden;">
            <div class="community-modal-banner">
                <div style="position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;opacity:0.08;pointer-events:none;border-radius:20px 20px 0 0;"></div>
                <button class="modal-close" id="loginRequiredClose">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div class="community-modal-emoji">🎉</div>
                <h2 class="community-modal-title">Join the Community!</h2>
                <p class="community-modal-sub">Be part of the SK OnePortal youth community in Santa Cruz, Laguna.</p>
            </div>
            <div class="modal-body community-modal-body">
                <p class="community-modal-desc">
                    Want to explore <strong id="loginRequiredCategory" class="community-modal-highlight"></strong>? Login to get full access.
                </p>
                <div class="community-modal-perks">
                    <div class="community-perk-item">
                        <span class="community-perk-icon">✅</span>
                        Apply for SK programs and track your applications
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">💬</span>
                        Like and comment on community posts
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">🏘️</span>
                        Browse barangay SK profiles and officers
                    </div>
                </div>
                <a href="{{ route('login') }}" class="community-modal-login-btn">Login</a>
            </div>
        </div>
    </div>

    {{-- ── ACTION LOGIN MODAL (like / comment) ── --}}
    <div id="actionLoginModal" class="program-modal">
        <div class="modal-overlay" id="actionLoginOverlay"></div>
        <div class="modal-container" style="max-width:440px;overflow:hidden;">
            <div class="community-modal-banner">
                <div style="position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;opacity:0.08;pointer-events:none;border-radius:20px 20px 0 0;"></div>
                <button class="modal-close" id="actionLoginClose">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div class="community-modal-emoji">🎉</div>
                <h2 class="community-modal-title">Join the Community!</h2>
                <p class="community-modal-sub">Be part of the SK OnePortal youth community in Santa Cruz, Laguna.</p>
            </div>
            <div class="modal-body community-modal-body">
                <p class="community-modal-desc">
                    Login to like posts, drop comments, and connect with SK officials and fellow youth in Santa Cruz.
                </p>
                <div class="community-modal-perks">
                    <div class="community-perk-item">
                        <span class="community-perk-icon">✅</span>
                        Apply for SK programs and track your applications
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">💬</span>
                        Like and comment on community posts
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">🏘️</span>
                        Browse barangay SK profiles and officers
                    </div>
                </div>
                <a href="{{ route('login') }}" class="community-modal-login-btn">Login</a>
            </div>
        </div>
    </div>

    {{-- ── PROGRAM APPLY MODAL ── --}}
    <div id="programApplyModal" class="program-modal">
        <div class="modal-overlay" id="programApplyOverlay"></div>
        <div class="modal-container" style="max-width:440px;overflow:hidden;">
            <div class="community-modal-banner">
                <div style="position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;opacity:0.08;pointer-events:none;border-radius:20px 20px 0 0;"></div>
                <button class="modal-close" id="programApplyClose">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div class="community-modal-emoji">🎉</div>
                <h2 class="community-modal-title">Join the Community!</h2>
                <p class="community-modal-sub">Be part of the SK OnePortal youth community in Santa Cruz, Laguna.</p>
            </div>
            <div class="modal-body community-modal-body">
                <p class="community-modal-desc">
                    You're one step away from applying for <strong id="programApplyName" class="community-modal-highlight"></strong>. Login to submit your application.
                </p>
                <div class="community-modal-perks">
                    <div class="community-perk-item">
                        <span class="community-perk-icon">✅</span>
                        Apply for SK programs and track your applications
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">💬</span>
                        Like and comment on community posts
                    </div>
                    <div class="community-perk-item">
                        <span class="community-perk-icon">🏘️</span>
                        Browse barangay SK profiles and officers
                    </div>
                </div>
                <a href="{{ route('login') }}" class="community-modal-login-btn">Login</a>
            </div>
        </div>
    </div>

    {{-- ── IMAGE GALLERY MODAL ── --}}
    <div id="imageGalleryModal" class="image-gallery-modal">
        <div class="gallery-container">
            <div class="gallery-header">
                <img src="" alt="User avatar" class="gallery-user-avatar">
                <div class="gallery-user-info">
                    <h3 class="gallery-user-name"></h3>
                    <p class="gallery-post-time"></p>
                </div>
                <button class="gallery-close" id="galleryClose">✕</button>
            </div>
            <div class="gallery-caption"></div>
            <div class="gallery-main">
                <img src="" alt="Gallery image">
                <div class="gallery-nav">
                    <button class="gallery-btn" id="galleryPrev">❮</button>
                    <button class="gallery-btn" id="galleryNext">❯</button>
                </div>
                <div class="gallery-counter"></div>
            </div>
            <div class="gallery-thumbnails" id="galleryThumbnails"></div>
        </div>
    </div>

    <script>
    (function() {
        var btn    = document.getElementById('navHamburger');
        var drawer = document.getElementById('navDrawer');
        if (!btn || !drawer) return;
        btn.addEventListener('click', function() { drawer.classList.toggle('open'); });
        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !drawer.contains(e.target)) {
                drawer.classList.remove('open');
            }
        });
    })();
    </script>
</body>
</html>
