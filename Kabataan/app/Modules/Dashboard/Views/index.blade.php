<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
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
        'app/Modules/Layout/assets/css/kabataan-header.css',
        'app/Modules/Layout/assets/css/kabataan-logout.css',
        'app/Modules/Layout/assets/js/kabataan-header.js',
        'app/Modules/Layout/assets/js/kabataan-logout.js',
        'app/Modules/Dashboard/assets/css/dashboard.css',
        'app/Modules/Dashboard/assets/js/dashboard.js',
        'app/Modules/Programs/assets/js/programs.js',
        'app/Modules/Dashboard/assets/css/chatbot.css',
        'app/Modules/Dashboard/assets/js/chatbot.js',
        'app/Modules/Dashboard/assets/css/notif.css',
        'app/Modules/Dashboard/assets/js/notif.js',
        'app/Modules/KKProfiling/assets/css/kkprofiling.css',
        'app/Modules/KKProfiling/assets/css/kk-profiling-update.css',
        'app/Modules/KKProfiling/assets/js/kkprofiling.js',
        'app/Modules/KKProfiling/assets/js/kk-profiling-update.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="youth-dashboard">
    @include('dashboard::loading')
    @include('layout::kabataan-header', ['user' => $user ?? auth()->user(), 'showSearch' => true])

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
                        <div class="program-category" onclick="openEducationModal()" style="cursor: pointer;">
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
                        <div class="program-category" data-category="anti-drugs" onclick="openAntiDrugsModal()" style="cursor: pointer;">
                            <div class="category-icon anti-drugs">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Anti-Drugs</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Agriculture -->
                        <div class="program-category" data-category="agriculture" onclick="openAgricultureModal()" style="cursor: pointer;">
                            <div class="category-icon agriculture">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Agriculture</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Disaster Preparedness -->
                        <div class="program-category" data-category="disaster" onclick="openDisasterModal()" style="cursor: pointer;">
                            <div class="category-icon disaster">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Disaster Preparedness</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Sports Development -->
                        <div class="program-category" data-category="sports" onclick="openSportsModal()" style="cursor: pointer;">
                            <div class="category-icon sports">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Sports Development</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Gender and Development -->
                        <div class="program-category" data-category="gender" onclick="openGenderModal()" style="cursor: pointer;">
                            <div class="category-icon gender">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Gender and Development</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Health -->
                        <div class="program-category" data-category="health" onclick="openHealthModal()" style="cursor: pointer;">
                            <div class="category-icon health">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Health</h3>
                                <p>1 active program</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>

                        <!-- Others -->
                        <div class="program-category" data-category="others" onclick="openOthersModal()" style="cursor: pointer;">
                            <div class="category-icon others">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="category-content">
                                <h3>Others</h3>
                                <p>1 active program</p>
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
        <div class="modal-container" style="max-width: 900px;">
            <div class="modal-header">
                <h2>Education Programs</h2>
                <button class="modal-close" onclick="closeEducationModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body" style="padding: 32px;">
                <!-- Modern Program Card -->
                <div class="modern-program-card">
                    <!-- Header with gradient -->
                    <div class="program-card-header">
                        <div class="program-title-row">
                            <div>
                                <span class="program-category-tag">🎓 Education</span>
                                <h3 class="program-card-title">SK Scholarship Assistance Program 2026</h3>
                            </div>
                            <span class="program-status-badge status-active">
                                <span class="status-dot"></span>
                                Active
                            </span>
                        </div>
                    </div>

                    <!-- Program Details Grid -->
                    <div class="program-details-grid">
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Committee Handled By</span>
                                <span class="detail-value">Committee on Education and Youth Development</span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Program Status</span>
                                <span class="detail-value">Active</span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Participant Quantity</span>
                                <span class="detail-value">50 Students</span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Starting Date</span>
                                <span class="detail-value">January 15, 2026</span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">End Date</span>
                                <span class="detail-value">March 31, 2026</span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Venue</span>
                                <span class="detail-value">SK Office, Barangay Hall, Santa Cruz, Laguna</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="program-description-section">
                        <h4 class="section-heading">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Description
                        </h4>
                        <p class="description-text">
                            The SK Scholarship Assistance Program provides financial support to deserving youth pursuing higher education. This program aims to help students from low-income families achieve their academic goals by covering tuition fees, books, and other educational expenses. Priority will be given to students with excellent academic records and demonstrated financial need.
                        </p>
                    </div>

                    <!-- Terms & Conditions Expandable -->
                    <div class="terms-section">
                        <button class="terms-toggle" onclick="toggleTerms(); event.stopPropagation();" id="termsToggle" type="button">
                            <div class="terms-toggle-header">
                                <h4 class="section-heading">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                    </svg>
                                    Terms & Conditions
                                </h4>
                                <svg class="chevron-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                        <div class="terms-content" id="termsContent">
                            <ul class="terms-list">
                                <li>Applicant must be a bonafide resident of Santa Cruz, Laguna</li>
                                <li>Must be enrolled or planning to enroll in an accredited educational institution</li>
                                <li>Must maintain a general weighted average (GWA) of at least 85% or equivalent</li>
                                <li>Must submit all required documents including Certificate of Enrollment, Certificate of Indigency, and valid ID</li>
                                <li>Scholarship grant is non-transferable and non-convertible to cash</li>
                                <li>Recipients must attend mandatory orientation and community service activities</li>
                                <li>Must submit progress reports every semester</li>
                                <li>Failure to comply with requirements may result in scholarship termination</li>
                                <li>Scholarship covers one academic year and may be renewed upon reapplication</li>
                                <li>Recipients must not be receiving similar scholarships from other government agencies</li>
                            </ul>
                            
                            <!-- Agreement Checkbox -->
                            <div class="terms-agreement">
                                <label class="agreement-checkbox" onclick="event.stopPropagation();">
                                    <input type="checkbox" id="agreeTerms" onchange="toggleApplyButton()" onclick="event.stopPropagation();">
                                    <span class="checkbox-custom"></span>
                                    <span class="agreement-text">
                                        I have read and agree to the Terms & Conditions
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="program-action">
                        <button class="apply-now-button" id="applyNowBtn" onclick="goToScholarshipApplication()" disabled>
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            Apply Now
                        </button>
                        <p class="apply-note">Please read and agree to the Terms & Conditions to continue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Anti-Drugs Modal -->
    <div id="antiDrugsModal" class="program-modal">
        <div class="modal-overlay"></div>
        <div class="modal-container" style="max-width: 900px;">
            <div class="modal-header">
                <h2>Anti-Drugs Programs</h2>
                <button class="modal-close" onclick="closeAntiDrugsModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 32px; overflow-y: auto; max-height: calc(90vh - 80px); min-height: 400px;">
                <div class="modern-program-card">
                    <div class="program-card-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        <div class="program-title-row">
                            <div>
                                <span class="program-category-tag">🚫 Anti-Drugs</span>
                                <h3 class="program-card-title">Drug-Free Youth Campaign 2026</h3>
                            </div>
                            <span class="program-status-badge status-active"><span class="status-dot"></span>Active</span>
                        </div>
                    </div>
                    <div class="program-details-grid">
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg></div>
                            <div class="detail-content"><span class="detail-label">Committee Handled By</span><span class="detail-value">Committee on Anti-Drug Abuse</span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg></div>
                            <div class="detail-content"><span class="detail-label">Program Status</span><span class="detail-value">Active</span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg></div>
                            <div class="detail-content"><span class="detail-label">Participant Quantity</span><span class="detail-value">100 Youth</span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg></div>
                            <div class="detail-content"><span class="detail-label">Starting Date</span><span class="detail-value">February 1, 2026</span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg></div>
                            <div class="detail-content"><span class="detail-label">End Date</span><span class="detail-value">April 30, 2026</span></div>
                        </div>
                        <div class="detail-card">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg></div>
                            <div class="detail-content"><span class="detail-label">Venue</span><span class="detail-value">Barangay Covered Court, Santa Cruz</span></div>
                        </div>
                    </div>
                    <div class="program-description-section">
                        <h4 class="section-heading"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>Description</h4>
                        <p class="description-text">Join our Drug-Free Youth Campaign to promote awareness and prevention of drug abuse among young people. This program includes seminars, workshops, and community activities designed to educate youth about the dangers of drugs and empower them to make healthy life choices.</p>
                    </div>
                    <div class="terms-section">
                        <button class="terms-toggle" onclick="toggleTermsAntiDrugs(); event.stopPropagation();" id="termsToggleAntiDrugs" type="button">
                            <div class="terms-toggle-header">
                                <h4 class="section-heading"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>Terms & Conditions</h4>
                                <svg class="chevron-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </div>
                        </button>
                        <div class="terms-content" id="termsContentAntiDrugs">
                            <ul class="terms-list">
                                <li>Participant must be a resident of Santa Cruz, Laguna aged 15-30</li>
                                <li>Must attend all scheduled sessions and activities</li>
                                <li>Must sign a pledge to remain drug-free</li>
                                <li>Participants will receive a certificate of completion</li>
                                <li>Must actively participate in community outreach activities</li>
                            </ul>
                            <div class="terms-agreement">
                                <label class="agreement-checkbox" onclick="event.stopPropagation();">
                                    <input type="checkbox" id="agreeTermsAntiDrugs" onchange="toggleApplyButtonAntiDrugs()" onclick="event.stopPropagation();">
                                    <span class="checkbox-custom"></span>
                                    <span class="agreement-text">I have read and agree to the Terms & Conditions</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="program-action">
                        <button class="apply-now-button" id="applyNowBtnAntiDrugs" onclick="goToPreSurvey('anti-drugs')" disabled>
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                            Apply Now
                        </button>
                        <p class="apply-note">Please read and agree to the Terms & Conditions to continue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scholarship Application Form Modal (New PDF-style) -->
    @include('dashboard::remaining_modals')


    <!-- Programs Drawer Backdrop -->
    <div class="programs-drawer-backdrop" id="programsDrawerBackdrop"></div>

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

    // ── Education Modal Functions ──
    window.openEducationModal = function() {
        // Directly open the education modal (don't use programsModule to avoid "no program" issue)
        document.getElementById('educationModal').classList.add('active');
    };

    window.closeEducationModal = function() {
        document.getElementById('educationModal').classList.remove('active');
        
        // Reset terms agreement when closing
        const checkbox = document.getElementById('agreeTerms');
        const content = document.getElementById('termsContent');
        const toggle = document.getElementById('termsToggle');
        const chevron = toggle?.querySelector('.chevron-icon');
        
        if (checkbox) {
            checkbox.checked = false;
            toggleApplyButton();
        }
        
        // Collapse terms section
        if (content) {
            content.classList.remove('expanded');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    };

    // ── Anti-Drugs Modal Functions ──
    window.openAntiDrugsModal = function() {
        document.getElementById('antiDrugsModal').classList.add('active');
    };

    window.closeAntiDrugsModal = function() {
        document.getElementById('antiDrugsModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsAntiDrugs');
        const content = document.getElementById('termsContentAntiDrugs');
        const toggle = document.getElementById('termsToggleAntiDrugs');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonAntiDrugs(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsAntiDrugs = function() {
        const content = document.getElementById('termsContentAntiDrugs');
        const toggle = document.getElementById('termsToggleAntiDrugs');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonAntiDrugs = function() {
        const checkbox = document.getElementById('agreeTermsAntiDrugs');
        const applyBtn = document.getElementById('applyNowBtnAntiDrugs');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    // Attach overlay close events
    document.getElementById('antiDrugsModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeAntiDrugsModal);

    // ── Agriculture Modal Functions ──
    window.openAgricultureModal = function() {
        document.getElementById('agricultureModal').classList.add('active');
    };

    window.closeAgricultureModal = function() {
        document.getElementById('agricultureModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsAgriculture');
        const content = document.getElementById('termsContentAgriculture');
        const toggle = document.getElementById('termsToggleAgriculture');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonAgriculture(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsAgriculture = function() {
        const content = document.getElementById('termsContentAgriculture');
        const toggle = document.getElementById('termsToggleAgriculture');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonAgriculture = function() {
        const checkbox = document.getElementById('agreeTermsAgriculture');
        const applyBtn = document.getElementById('applyNowBtnAgriculture');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('agricultureModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeAgricultureModal);

    // ── Disaster Modal Functions ──
    window.openDisasterModal = function() {
        document.getElementById('disasterModal').classList.add('active');
    };

    window.closeDisasterModal = function() {
        document.getElementById('disasterModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsDisaster');
        const content = document.getElementById('termsContentDisaster');
        const toggle = document.getElementById('termsToggleDisaster');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonDisaster(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsDisaster = function() {
        const content = document.getElementById('termsContentDisaster');
        const toggle = document.getElementById('termsToggleDisaster');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonDisaster = function() {
        const checkbox = document.getElementById('agreeTermsDisaster');
        const applyBtn = document.getElementById('applyNowBtnDisaster');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('disasterModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeDisasterModal);

    // ── Sports Modal Functions ──
    window.openSportsModal = function() {
        document.getElementById('sportsModal').classList.add('active');
    };

    window.closeSportsModal = function() {
        document.getElementById('sportsModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsSports');
        const content = document.getElementById('termsContentSports');
        const toggle = document.getElementById('termsToggleSports');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonSports(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsSports = function() {
        const content = document.getElementById('termsContentSports');
        const toggle = document.getElementById('termsToggleSports');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonSports = function() {
        const checkbox = document.getElementById('agreeTermsSports');
        const applyBtn = document.getElementById('applyNowBtnSports');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('sportsModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeSportsModal);

    // ── Gender Modal Functions ──
    window.openGenderModal = function() {
        document.getElementById('genderModal').classList.add('active');
    };

    window.closeGenderModal = function() {
        document.getElementById('genderModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsGender');
        const content = document.getElementById('termsContentGender');
        const toggle = document.getElementById('termsToggleGender');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonGender(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsGender = function() {
        const content = document.getElementById('termsContentGender');
        const toggle = document.getElementById('termsToggleGender');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonGender = function() {
        const checkbox = document.getElementById('agreeTermsGender');
        const applyBtn = document.getElementById('applyNowBtnGender');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('genderModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeGenderModal);

    // ── Health Modal Functions ──
    window.openHealthModal = function() {
        document.getElementById('healthModal').classList.add('active');
    };

    window.closeHealthModal = function() {
        document.getElementById('healthModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsHealth');
        const content = document.getElementById('termsContentHealth');
        const toggle = document.getElementById('termsToggleHealth');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonHealth(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsHealth = function() {
        const content = document.getElementById('termsContentHealth');
        const toggle = document.getElementById('termsToggleHealth');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonHealth = function() {
        const checkbox = document.getElementById('agreeTermsHealth');
        const applyBtn = document.getElementById('applyNowBtnHealth');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('healthModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeHealthModal);

    // ── Others Modal Functions ──
    window.openOthersModal = function() {
        document.getElementById('othersModal').classList.add('active');
    };

    window.closeOthersModal = function() {
        document.getElementById('othersModal').classList.remove('active');
        const checkbox = document.getElementById('agreeTermsOthers');
        const content = document.getElementById('termsContentOthers');
        const toggle = document.getElementById('termsToggleOthers');
        const chevron = toggle?.querySelector('.chevron-icon');
        if (checkbox) { checkbox.checked = false; toggleApplyButtonOthers(); }
        if (content) { content.classList.remove('expanded'); if (chevron) chevron.style.transform = 'rotate(0deg)'; }
    };

    window.toggleTermsOthers = function() {
        const content = document.getElementById('termsContentOthers');
        const toggle = document.getElementById('termsToggleOthers');
        const chevron = toggle.querySelector('.chevron-icon');
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    window.toggleApplyButtonOthers = function() {
        const checkbox = document.getElementById('agreeTermsOthers');
        const applyBtn = document.getElementById('applyNowBtnOthers');
        const note = applyBtn?.nextElementSibling;
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    document.getElementById('othersModal')?.querySelector('.modal-overlay')?.addEventListener('click', closeOthersModal);

    // ── Close No Program Modal ──
    window.closeNoProgramModal = function() {
        const modal = document.getElementById('noProgramModal');
        if (modal) {
            modal.classList.remove('active');
        }
    };

    // Attach close event listeners to No Program modal
    const noProgramModal = document.getElementById('noProgramModal');
    if (noProgramModal) {
        const closeBtn = noProgramModal.querySelector('.modal-close');
        const overlay = noProgramModal.querySelector('.modal-overlay');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeNoProgramModal);
        }
        if (overlay) {
            overlay.addEventListener('click', closeNoProgramModal);
        }
    }

    // ── Terms & Conditions Toggle ──
    window.toggleTerms = function() {
        const content = document.getElementById('termsContent');
        const toggle = document.getElementById('termsToggle');
        const chevron = toggle.querySelector('.chevron-icon');
        
        if (content.classList.contains('expanded')) {
            content.classList.remove('expanded');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('expanded');
            chevron.style.transform = 'rotate(180deg)';
        }
    };

    // ── Toggle Apply Button based on Terms Agreement ──
    window.toggleApplyButton = function() {
        const checkbox = document.getElementById('agreeTerms');
        const applyBtn = document.getElementById('applyNowBtn');
        const note = document.querySelector('.apply-note');
        
        if (checkbox && applyBtn) {
            if (checkbox.checked) {
                applyBtn.disabled = false;
                applyBtn.classList.add('enabled');
                if (note) note.style.display = 'none';
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.remove('enabled');
                if (note) note.style.display = 'block';
            }
        }
    };

    // ── Program application redirects (frontend only — see programs.js) ──
    window.scholarshipApplyUrl = @json(route('scholarship.apply'));
    window.sportsApplyUrl = @json(route('sports.apply'));

    const educationModal = document.getElementById('educationModal');
    if (educationModal) {
        educationModal.querySelector('.modal-overlay')?.addEventListener('click', closeEducationModal);
    }
    </script>

    <script>
    // Disqualify page from bfcache — back button will always hit the server
    window.addEventListener('unload', function () {});
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
        const items = data.data ?? [];
        if (reset && items.length === 0) {
            document.getElementById('feed-posts').innerHTML =
                '<div class="post-card" style="text-align:center;color:#64748b;padding:32px;">No posts yet. Announcements, events, and activities from your barangay will appear here.</div>';
            const btn = document.getElementById('load-more-btn');
            if (btn) btn.style.display = 'none';
            return;
        }
        items.forEach(p => {
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

    @if(!empty($kkUpdateBarangay))
        @include('kkprofiling::kk-profiling-update')
    @endif
    <script>
        window.__SHOW_KK_UPDATE_MODAL = @json($showKkUpdateModal ?? false);
    </script>
</body>
</html>
