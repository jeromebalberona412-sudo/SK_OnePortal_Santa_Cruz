<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Dashboard/assets/css/dashboard.css',
        'app/Modules/Dashboard/assets/js/dashboard.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="youth-dashboard">
    @include('dashboard::loading')
    <!-- Top Navigation Bar -->
    <nav class="top-navbar">
        <div class="navbar-container">
            <div class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">Kabataan<small>SK OnePortal Santa Cruz</small></span>
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
                <button class="nav-icon-btn programs-drawer-btn" id="programsDrawerBtn" title="Programs" style="display:none;">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                        <path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </button>

                <button class="nav-icon-btn" title="Home">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </button>
                
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
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            My Profile
                        </a>
                        <a href="{{ route('settings') }}" class="dropdown-item">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-container">
            <!-- Left Sidebar (Optional - for future use) -->
            
            <!-- Center Content - Social Feed -->
            <div class="feed-section">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert alert-success">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px; display: inline-block; margin-right: 8px;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="feed-header">
                    <div>
                        <h1>SK Community Feed</h1>
                        <p>See the latest posts, events, and programs from SK officials.</p>
                    </div>
                </div>

                <div class="feed-filter-bar" style="display:flex;gap:4px;margin-bottom:12px;border-bottom:2px solid var(--border);padding-bottom:0;">
                    <button class="feed-tab active" onclick="setFeedFilter(this,'all')">All</button>
                    <button class="feed-tab" onclick="setFeedFilter(this,'announcement')">Announcements</button>
                    <button class="feed-tab" onclick="setFeedFilter(this,'event')">Events</button>
                    <button class="feed-tab" onclick="setFeedFilter(this,'activity')">Activities</button>
                    <button class="feed-tab" onclick="setFeedFilter(this,'program')">Programs</button>
                </div>

                <div id="feed-posts"></div>

                <div style="text-align:center;padding:8px 0 16px;">
                    <button class="view-details-btn" id="load-more-btn" onclick="loadMorePosts()" style="display:none;">
                        Load More
                    </button>
                </div>
            </div>

            <!-- Right Sidebar -->
            <aside class="programs-sidebar">
                <div class="sidebar-card">
                    <h2 class="sidebar-title">Programs in Your Barangay</h2>
                    <p class="sidebar-subtitle">Available programs in Barangay {{ $user->barangay ?? '1' }}</p>
                    
                    <div class="program-categories">
                        <!-- Education -->
                        <div class="program-category" data-category="education">
                            <div class="category-icon education">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Education</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Anti-Drugs -->
                        <div class="program-category" data-category="anti-drugs">
                            <div class="category-icon anti-drugs">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Anti-Drugs</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Agriculture -->
                        <div class="program-category" data-category="agriculture">
                            <div class="category-icon agriculture">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Agriculture</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Disaster Preparedness -->
                        <div class="program-category" data-category="disaster">
                            <div class="category-icon disaster">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Disaster Preparedness</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Sports Development -->
                        <div class="program-category" data-category="sports">
                            <div class="category-icon sports">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Sports Development</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Gender and Development -->
                        <div class="program-category" data-category="gender">
                            <div class="category-icon gender">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Gender and Development</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Health -->
                        <div class="program-category" data-category="health">
                            <div class="category-icon health">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Health</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Others -->
                        <div class="program-category" data-category="others">
                            <div class="category-icon others">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Others</h3>
                                <p>0 active programs</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Barangay SK Profiles --}}
                <div class="sidebar-card" style="margin-top:16px;">
                    <h2 class="sidebar-title">Barangay SK Profiles</h2>
                    <p class="sidebar-subtitle">Browse SK officials from each barangay.</p>
                    <div class="barangay-profiles-list">
                        @php
                        $brgyList = [
                            ['name'=>'Alipit',        'chairman'=>'[SK Chairman]','color'=>'#4CAF50','programs'=>2,'events'=>3,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Bagumbayan',    'chairman'=>'[SK Chairman]','color'=>'#2196F3','programs'=>1,'events'=>2,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Bubukal',       'chairman'=>'[SK Chairman]','color'=>'#9C27B0','programs'=>0,'events'=>1,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Duhat',         'chairman'=>'[SK Chairman]','color'=>'#FF9800','programs'=>1,'events'=>2,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Gatid',         'chairman'=>'[SK Chairman]','color'=>'#009688','programs'=>1,'events'=>1,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Labuin',        'chairman'=>'[SK Chairman]','color'=>'#f44336','programs'=>2,'events'=>2,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Pagsawitan',    'chairman'=>'[SK Chairman]','color'=>'#673AB7','programs'=>1,'events'=>3,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'San Jose',      'chairman'=>'[SK Chairman]','color'=>'#0450a8','programs'=>0,'events'=>2,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                            ['name'=>'Santisima Cruz','chairman'=>'[SK Chairman]','color'=>'#FF5722','programs'=>2,'events'=>1,'members'=>['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]','[Councilor 7]']],
                        ];
                        @endphp
                        @foreach ($brgyList as $brgy)
                        <div class="brgy-profile-item"
                            data-brgy-name="{{ $brgy['name'] }}"
                            data-brgy-chairman="{{ $brgy['chairman'] }}"
                            data-brgy-members="{{ implode('|', $brgy['members']) }}"
                            data-brgy-color="{{ $brgy['color'] }}"
                            data-brgy-programs="{{ $brgy['programs'] }}"
                            data-brgy-events="{{ $brgy['events'] }}"
                            style="cursor:pointer;"
                        >
                            <div class="brgy-avatar" style="background:{{ $brgy['color'] }};">
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

    <div id="educationModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2>Education Programs</h2>
                <button class="modal-close">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="program-item">
                    <div class="program-header">
                        <h3>🎓 Scholarship Assistance Program</h3>
                        <span class="program-status active">Active</span>
                    </div>
                    <p class="program-description">Financial assistance for deserving students pursuing higher education. Covers tuition fees and other educational expenses.</p>
                    <div class="program-meta">
                        <span>📅 Deadline: March 31, 2026</span>
                        <span>👥 50 slots available</span>
                    </div>
                    <button class="apply-btn">Apply Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scholarship Application Form Modal -->
    <div id="scholarshipFormModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container large">
            <div class="modal-header">
                <h2>Scholarship Application Form</h2>
                <button class="modal-close">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <form class="scholarship-form">
                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" required placeholder="Juan Dela Cruz">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth *</label>
                                <input type="date" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" required placeholder="juan@example.com">
                            </div>
                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="tel" required placeholder="09XX XXX XXXX">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Complete Address *</label>
                            <textarea rows="2" required placeholder="House No., Street, Barangay, Santa Cruz, Laguna"></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Educational Background</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>School/University *</label>
                                <input type="text" required placeholder="Name of Institution">
                            </div>
                            <div class="form-group">
                                <label>Course/Program *</label>
                                <input type="text" required placeholder="e.g., BS Computer Science">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Year Level *</label>
                                <select required>
                                    <option value="">Select Year Level</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                    <option value="5">5th Year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>GPA/General Average *</label>
                                <input type="text" required placeholder="e.g., 1.75 or 90%">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Family Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Parent/Guardian Name *</label>
                                <input type="text" required placeholder="Full Name">
                            </div>
                            <div class="form-group">
                                <label>Occupation *</label>
                                <input type="text" required placeholder="Occupation">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Monthly Family Income *</label>
                                <select required>
                                    <option value="">Select Income Range</option>
                                    <option value="below-10k">Below ₱10,000</option>
                                    <option value="10k-20k">₱10,000 - ₱20,000</option>
                                    <option value="20k-30k">₱20,000 - ₱30,000</option>
                                    <option value="30k-50k">₱30,000 - ₱50,000</option>
                                    <option value="above-50k">Above ₱50,000</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Number of Siblings *</label>
                                <input type="number" required min="0" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Required Documents</h3>
                        <div class="form-group">
                            <label>Certificate of Enrollment/Registration *</label>
                            <input type="file" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="form-group">
                            <label>Grade Report (Previous Semester) *</label>
                            <input type="file" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="form-group">
                            <label>Barangay Certificate of Indigency *</label>
                            <input type="file" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Essay</h3>
                        <div class="form-group">
                            <label>Why do you deserve this scholarship? (200-500 words) *</label>
                            <textarea rows="6" required placeholder="Share your story, goals, and why you need this scholarship..."></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary modal-close">Cancel</button>
                        <button type="submit" class="btn-primary">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- No Available Program Modal -->
    <div id="noProgramModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="noProgramModalTitle">Programs</h2>
                <button class="modal-close">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div style="text-align: center; padding: 40px 20px;">
                    <!-- Empty Box Icon -->
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin: 0 auto 20px;">
                        <!-- Box outline -->
                        <rect x="3" y="6" width="18" height="14" rx="2" stroke="#CCCCCC" stroke-width="2" fill="none"/>
                        <!-- Box lid -->
                        <path d="M3 10 L12 6 L21 10" stroke="#CCCCCC" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Empty indicator lines -->
                        <line x1="8" y1="13" x2="16" y2="13" stroke="#E0E0E0" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="10" y1="16" x2="14" y2="16" stroke="#E0E0E0" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <h3 style="font-size: 20px; color: #333; margin-bottom: 12px;">No Programs Available</h3>
                    <p style="color: #666; font-size: 15px; line-height: 1.6;">
                        There are currently no active programs in this category. Please check back later or explore other categories.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- Programs Drawer Backdrop -->
    <div class="programs-drawer-backdrop" id="programsDrawerBackdrop"></div>

    <!-- Logout Confirm Modal -->
    <div id="logoutConfirmModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container" style="max-width:420px;">
            <div class="modal-header">
                <h2>Confirm Logout</h2>
                <button class="modal-close" onclick="closeLogoutModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="text-align:center; padding: 32px 24px;">
                <div style="width:64px;height:64px;background:#fff3e0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:32px;height:32px;color:#f97316;">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 style="font-size:18px;color:#333;margin-bottom:8px;">Are you sure you want to logout?</h3>
                <p style="color:#999;font-size:14px;margin-bottom:28px;">You will be redirected to the login page.</p>
                <div style="display:flex;gap:12px;justify-content:center;">
                    <button class="btn-secondary" onclick="closeLogoutModal()" style="min-width:100px;">Cancel</button>
                    <button class="btn-primary" id="confirmLogoutBtn" style="min-width:100px;background:linear-gradient(135deg,#f44336,#d32f2f);box-shadow:0 4px 12px rgba(244,67,54,0.3);">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Registration Success Modal -->
    <div id="programSuccessModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container" style="max-width:420px;">
            <div class="modal-body" style="text-align:center; padding: 48px 32px;">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#22c55e,#16a34a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(34,197,94,0.35);">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:white;">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 style="font-size:22px;font-weight:700;color:#333;margin-bottom:10px;">Application Submitted!</h3>
                <p style="color:#666;font-size:15px;line-height:1.6;margin-bottom:8px;">Your application has been successfully submitted. We will review it and contact you soon.</p>
                <p style="color:#94a3b8;font-size:13px;">This window will close automatically.</p>
                <div style="margin-top:8px;height:3px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                    <div id="successProgressBar" style="height:100%;background:linear-gradient(135deg,#22c55e,#16a34a);width:100%;transition:width 5s linear;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ── Logout Modal ──────────────────────────────────────────────────────────
    (function () {
        const logoutBtn = document.querySelector('.logout-btn');
        const logoutForm = logoutBtn?.closest('form');
        const modal = document.getElementById('logoutConfirmModal');
        const confirmBtn = document.getElementById('confirmLogoutBtn');

        if (logoutBtn && logoutForm) {
            logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                modal.classList.add('active');
            });
        }

        if (confirmBtn && logoutForm) {
            confirmBtn.addEventListener('click', function () {
                logoutForm.submit();
            });
        }

        // Close on overlay click
        modal?.querySelector('.modal-overlay')?.addEventListener('click', closeLogoutModal);
    })();

    function closeLogoutModal() {
        document.getElementById('logoutConfirmModal')?.classList.remove('active');
    }

    // ── Program Success Modal ─────────────────────────────────────────────────
    function showProgramSuccessModal() {
        const modal = document.getElementById('programSuccessModal');
        const bar   = document.getElementById('successProgressBar');
        modal.classList.add('active');

        // Reset and animate progress bar
        bar.style.transition = 'none';
        bar.style.width = '100%';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bar.style.transition = 'width 5s linear';
                bar.style.width = '0%';
            });
        });

        setTimeout(() => modal.classList.remove('active'), 5000);
    }

    // Close on overlay click
    document.getElementById('programSuccessModal')
        ?.querySelector('.modal-overlay')
        ?.addEventListener('click', () => {
            document.getElementById('programSuccessModal').classList.remove('active');
        });
    </script>

    <script>
    // Disqualify page from bfcache — back button will always hit the server
    window.addEventListener('unload', function () {});
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { window.location.replace('/dashboard'); }
    });
    </script>

    <script>
    // ── Community Feed ────────────────────────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let feedPage = 1, feedLastPage = 1, feedFilter = 'all';

    async function apiFeed(url, opts = {}) {
        const { headers: extraHeaders, ...rest } = opts;
        const r = await fetch(url, {
            ...rest,
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', ...(extraHeaders ?? {}) },
        });
        if (!r.ok) throw new Error(await r.text());
        return r.json();
    }

    async function loadFeed(reset = true) {
        if (reset) { feedPage = 1; document.getElementById('feed-posts').innerHTML = ''; }
        const params = new URLSearchParams({ page: feedPage, filter: feedFilter });
        const data   = await apiFeed(`/api/feed?${params}`).catch(e => { console.error('Feed error:', e); return null; });
        if (!data) return;
        feedLastPage = data.last_page;
        (data.data ?? []).forEach(p => {
            const el = document.createElement('article');
            el.className = 'post-card';
            el.dataset.postId = p.id;
            el.innerHTML = buildFeedPost(p);
            document.getElementById('feed-posts').appendChild(el);
        });
        const btn = document.getElementById('load-more-btn');
        if (btn) btn.style.display = feedPage >= feedLastPage ? 'none' : 'inline-flex';
    }

    function loadMorePosts() { feedPage++; loadFeed(false); }

    function setFeedFilter(btn, filter) {
        feedFilter = filter;
        document.querySelectorAll('.feed-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        loadFeed(true);
    }

    function buildFeedPost(p) {
        const avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent('SK ' + (p.barangay_name ?? ''))}&background=0450a8&color=fff`;
        const media  = p.image_url ? `<div class="post-image"><img src="${p.image_url}" loading="lazy"></div>` : '';
        const link   = p.link_url  ? `<a href="${p.link_url}" target="_blank" rel="noopener" class="post-link-preview">${p.link_url}</a>` : '';
        const comments = (p.comments ?? []).map(c =>
            `<div class="comment-item">
               <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.author_name)}&background=667eea&color=fff" alt="${c.author_name}">
               <div class="comment-content">
                 <p class="comment-author">${c.author_name}</p>
                 <p class="comment-text">${c.body}</p>
                 <span class="comment-time">${c.time}</span>
               </div>
             </div>`
        ).join('');
        return `
          <div class="post-header">
            <img src="${avatar}" alt="${p.barangay_name}" class="post-avatar">
            <div class="post-info">
              <h3 class="post-author">${p.author_name ?? ('SK Brgy. ' + (p.barangay_name ?? ''))}</h3>
              <p class="post-meta">
                <span class="post-type ${p.type}">${p.type}</span>
                <span class="post-time">${p.time}</span>
              </p>
            </div>
          </div>
          <div class="post-content">
            ${p.title ? `<h2 class="post-title">${p.title}</h2>` : ''}
            <p class="post-text">${p.body}</p>
            ${media}${link}
          </div>
          <div class="post-actions">
            <button class="action-btn${p.liked ? ' liked' : ''}" onclick="feedToggleLike(${p.id}, this)">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
              <span id="feed-like-${p.id}">Like (${p.likes})</span>
            </button>
            <button class="action-btn comment-btn" onclick="feedToggleComments(${p.id})">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
              <span id="feed-comment-count-${p.id}">Comment (${(p.comments ?? []).length})</span>
            </button>
          </div>
          <div class="comments-section" id="feed-comments-${p.id}" style="display:none;">
            <div id="feed-comments-list-${p.id}">${comments}</div>
            <div class="comment-input-wrapper">
              <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=667eea&color=fff" alt="You">
              <input type="text" class="comment-input" placeholder="Write a comment..."
                     onkeydown="if(event.key==='Enter')feedSubmitComment(${p.id},this)">
              <button class="send-comment-btn" onclick="feedSubmitComment(${p.id},this.previousElementSibling)">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
              </button>
            </div>
          </div>`;
    }

    async function feedToggleLike(id, btn) {
        const data = await apiFeed(`/api/feed/${id}/react`, { method: 'POST' }).catch(() => null);
        if (!data) return;
        btn.classList.toggle('liked', data.liked);
        const el = document.getElementById(`feed-like-${id}`);
        if (el) el.textContent = `Like (${data.count})`;
    }

    function feedToggleComments(id) {
        const s = document.getElementById(`feed-comments-${id}`);
        if (s) s.style.display = s.style.display === 'none' ? 'block' : 'none';
    }

    async function feedSubmitComment(id, input) {
        const text = input.value.trim();
        if (!text) return;
        const c = await apiFeed(`/api/feed/${id}/comment`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: text }),
        }).catch(() => null);
        if (!c) return;
        input.value = '';
        const list = document.getElementById(`feed-comments-list-${id}`);
        if (list) list.insertAdjacentHTML('beforeend',
            `<div class="comment-item">
               <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.author_name)}&background=667eea&color=fff" alt="${c.author_name}">
               <div class="comment-content">
                 <p class="comment-author">${c.author_name}</p>
                 <p class="comment-text">${c.body}</p>
                 <span class="comment-time">${c.time}</span>
               </div>
             </div>`
        );
        const cnt = document.getElementById(`feed-comment-count-${id}`);
        if (cnt) { const n = parseInt(cnt.textContent.match(/\d+/)?.[0] ?? '0'); cnt.textContent = `Comment (${n + 1})`; }
    }

    document.addEventListener('DOMContentLoaded', () => loadFeed(true));
    </script>
</body>
</html>
