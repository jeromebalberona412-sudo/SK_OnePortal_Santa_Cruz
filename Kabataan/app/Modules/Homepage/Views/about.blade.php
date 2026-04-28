<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SK OnePortal Kabataan</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/css/about.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="homepage-body">

    {{-- ── LOADING OVERLAY ── --}}
    @include('dashboard::loading')

    {{-- ── NAVBAR ── --}}
    <nav class="top-navbar">
        <div class="navbar-container">
            <a href="{{ route('homepage') }}" class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
            </a>
            <div class="navbar-links">
                <a href="{{ route('homepage') }}" class="nav-link">Home</a>
                <a href="{{ route('about') }}" class="nav-link active">About</a>
                <a href="#services" class="nav-link">Services</a>
                <a href="#barangay" class="nav-link">Barangay</a>
                <a href="#contact" class="nav-link">Contact</a>
            </div>
            <div class="navbar-right">
                <button type="button" class="nav-btn solid" onclick="document.getElementById('joinCommunityModal').classList.add('active')">Login</button>
                <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>
    <div class="nav-drawer" id="navDrawer">
        <a href="{{ route('homepage') }}" class="nav-link">Home</a>
        <a href="{{ route('about') }}" class="nav-link active">About</a>
        <a href="#services" class="nav-link">Services</a>
        <a href="#barangay" class="nav-link">Barangay</a>
        <a href="#contact" class="nav-link">Contact</a>
        <div class="nav-drawer-actions">
            <button type="button" class="nav-btn solid" onclick="document.getElementById('joinCommunityModal').classList.add('active');document.getElementById('navDrawer').classList.remove('open')">Login</button>
        </div>
    </div>

    <main class="about-main">

        {{-- ── HERO ── --}}
        <section class="about-hero">
            <div class="about-hero-inner">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Logo" class="about-hero-logo">
                <div class="about-hero-badge">SK OnePortal Kabataan</div>
                <h1>Empowering the Youth of<br>Santa Cruz, Laguna</h1>
                <p>A unified digital platform connecting the Sangguniang Kabataan with the youth it serves — making programs, events, and community updates accessible to every kabataan.</p>
                <div class="about-hero-actions">
                    <a href="{{ route('homepage') }}" class="btn-hero-primary">View Community Feed</a>
                    <a href="{{ route('login') }}" class="btn-hero-ghost">Login</a>
                </div>
            </div>
        </section>

        {{-- ── WHAT IS SK ── --}}
        <section class="about-section">
            <div class="about-section-inner">
                <div class="section-eyebrow">What is the SK?</div>
                <h2>Sangguniang Kabataan</h2>
                <p class="section-lead">The Sangguniang Kabataan (SK) is the official youth council of every barangay in the Philippines, established under Republic Act 10742 or the Sangguniang Kabataan Reform Act of 2015.</p>

                <div class="sk-cards">
                    <div class="sk-card">
                        <div class="sk-card-icon blue">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/></svg>
                        </div>
                        <h3>Youth Governance</h3>
                        <p>The SK gives Filipino youth aged 15–30 a formal voice in local governance, allowing them to participate in decision-making that directly affects their communities.</p>
                    </div>
                    <div class="sk-card">
                        <div class="sk-card-icon green">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                        </div>
                        <h3>Community Programs</h3>
                        <p>SK officials plan and implement programs covering education, health, livelihood, sports, environment, and more — all tailored to the needs of local youth.</p>
                    </div>
                    <div class="sk-card">
                        <div class="sk-card-icon gold">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        </div>
                        <h3>Leadership Development</h3>
                        <p>The SK serves as a training ground for future leaders, building civic responsibility, teamwork, and public service values among the youth of every barangay.</p>
                    </div>
                    <div class="sk-card">
                        <div class="sk-card-icon purple">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                        </div>
                        <h3>Inclusive Participation</h3>
                        <p>Every registered youth in the barangay can participate in SK activities, vote in SK elections, and benefit from programs designed for their age group and needs.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ABOUT THE PORTAL ── --}}
        <section class="about-section alt-bg" id="services">
            <div class="about-section-inner">
                <div class="section-eyebrow">The Platform</div>
                <h2>What is SK OnePortal?</h2>
                <p class="section-lead">SK OnePortal is a web-based management system built to digitize and streamline the operations of the Sangguniang Kabataan in Santa Cruz, Laguna.</p>

                <div class="portal-grid">
                    <div class="portal-feature">
                        <div class="portal-feature-num">01</div>
                        <h3>Centralized Information</h3>
                        <p>All SK programs, events, announcements, and activities from every barangay in Santa Cruz are accessible in one place — no more scattered bulletins or missed updates.</p>
                    </div>
                    <div class="portal-feature">
                        <div class="portal-feature-num">02</div>
                        <h3>Program Applications</h3>
                        <p>Youth can browse available programs by category and apply directly through the portal. Applications are tracked and managed digitally, reducing paperwork and delays.</p>
                    </div>
                    <div class="portal-feature">
                        <div class="portal-feature-num">03</div>
                        <h3>Transparent Governance</h3>
                        <p>SK officials can post updates, manage programs, and communicate with constituents openly. Youth can see what their SK is doing and hold them accountable.</p>
                    </div>
                    <div class="portal-feature">
                        <div class="portal-feature-num">04</div>
                        <h3>Youth Engagement</h3>
                        <p>The community feed keeps youth informed and engaged with barangay-level activities, encouraging participation and a stronger sense of community identity.</p>
                    </div>
                    <div class="portal-feature">
                        <div class="portal-feature-num">05</div>
                        <h3>Multi-Portal System</h3>
                        <p>SK OnePortal has three portals: Kabataan (for youth), SK Federations (for federation officers), and Admin (for system management) — each with role-appropriate access.</p>
                    </div>
                    <div class="portal-feature">
                        <div class="portal-feature-num">06</div>
                        <h3>Secure & Accessible</h3>
                        <p>Built with modern security practices including email verification, session management, and role-based access control to protect all users' data and privacy.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── SK FEDERATION OFFICERS ── --}}
        <section class="about-section">
            <div class="about-section-inner">
                <div class="section-eyebrow">Leadership</div>
                <h2>SK Federation Officers</h2>
                <p class="section-lead">The SK Federation of Santa Cruz, Laguna oversees and coordinates the activities of all barangay-level SK councils in the municipality.</p>

                <div class="officers-grid">
                    <div class="officer-card featured">
                        <div class="officer-avatar chairman">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <div class="officer-badge">SK Federation Chairman</div>
                        <h3>[SK Federation Chairman]</h3>
                        <p>Leads the SK Federation of Santa Cruz, Laguna. Responsible for coordinating all barangay SK councils, representing youth interests at the municipal level, and overseeing the implementation of youth programs across all barangays.</p>
                    </div>
                </div>

                <div class="officers-row">
                    <div class="officer-card-sm">
                        <div class="officer-avatar-sm blue">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <h4>[Vice Chairman]</h4>
                        <span>Vice Chairman</span>
                    </div>
                    <div class="officer-card-sm">
                        <div class="officer-avatar-sm green">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <h4>[Secretary]</h4>
                        <span>Secretary</span>
                    </div>
                    <div class="officer-card-sm">
                        <div class="officer-avatar-sm gold">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <h4>[Treasurer]</h4>
                        <span>Treasurer</span>
                    </div>
                    <div class="officer-card-sm">
                        <div class="officer-avatar-sm purple">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <h4>[Auditor]</h4>
                        <span>Auditor</span>
                    </div>
                    <div class="officer-card-sm">
                        <div class="officer-avatar-sm red">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <h4>[PRO]</h4>
                        <span>Public Relations Officer</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── DEVELOPMENT TEAM ── --}}
        <section class="about-section alt-bg">
            <div class="about-section-inner">
                <div class="section-eyebrow">The Team</div>
                <h2>Meet the Developers</h2>
                <p class="section-lead">SK OnePortal was built by a dedicated team of student developers from Santa Cruz, Laguna as a capstone project to modernize SK operations.</p>

                <div class="dev-grid">
                    <div class="dev-card leader">
                        <div class="dev-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2);">JT</div>
                        <div class="dev-info">
                            <h3>Juana Paula Talabis</h3>
                            <div class="dev-roles">
                                <span class="dev-role leader-role">Team Leader</span>
                                <span class="dev-role frontend-role">Front-End</span>
                                <span class="dev-role docs-role">Documentation</span>
                            </div>
                            <p>Leads the project direction, oversees UI/UX design across all three portals, and maintains project documentation and progress tracking.</p>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-avatar" style="background: linear-gradient(135deg, #2196F3, #03A9F4);">JB</div>
                        <div class="dev-info">
                            <h3>Jerome Balberona</h3>
                            <div class="dev-roles">
                                <span class="dev-role frontend-role">Front-End</span>
                                <span class="dev-role backend-role">Back-End</span>
                            </div>
                            <p>Develops and integrates front-end interfaces with back-end logic, handles API development, database design, and server-side functionality.</p>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-avatar" style="background: linear-gradient(135deg, #4CAF50, #009688);">BF</div>
                        <div class="dev-info">
                            <h3>Frankien Belangoy</h3>
                            <div class="dev-roles">
                                <span class="dev-role frontend-role">Front-End</span>
                                <span class="dev-role backend-role">Back-End</span>
                            </div>
                            <p>Builds responsive UI components and implements back-end features including authentication flows, module logic, and database operations.</p>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-avatar" style="background: linear-gradient(135deg, #FF9800, #FF5722);">GG</div>
                        <div class="dev-info">
                            <h3>Gabriel Garcia</h3>
                            <div class="dev-roles">
                                <span class="dev-role docs-role">Documentation</span>
                                <span class="dev-role frontend-role">Front-End</span>
                            </div>
                            <p>Handles technical documentation, system design write-ups, and contributes to front-end development and UI implementation across modules.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── BARANGAY SECTION ── --}}
        <section class="about-section" id="barangay">
            <div class="about-section-inner">
                <div class="section-eyebrow">Explore</div>
                <h2>Select a Barangay</h2>
                <p class="section-lead">Choose a barangay to view its SK officials, accomplishments, programs, and budget transparency.</p>

                <div class="brgy-search-wrapper">
                    <svg class="brgy-search-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" id="brgySearch" class="brgy-search-input" placeholder="Search barangay...">
                </div>

                @php
                $barangays = [
                    ['name'=>'Alipit','color'=>'#44a53e'],['name'=>'Bagumbayan','color'=>'#0450a8'],
                    ['name'=>'Barangay I (Poblacion I)','color'=>'#fdc020'],['name'=>'Barangay II (Poblacion II)','color'=>'#e53e3e'],
                    ['name'=>'Barangay III (Poblacion III)','color'=>'#9c27b0'],['name'=>'Barangay IV (Poblacion IV)','color'=>'#00897b'],
                    ['name'=>'Barangay V (Poblacion V)','color'=>'#f57c00'],['name'=>'Bubukal','color'=>'#1565c0'],
                    ['name'=>'Calios','color'=>'#2e7d32'],['name'=>'Duhat','color'=>'#6a1b9a'],
                    ['name'=>'Gatid','color'=>'#00838f'],['name'=>'Jasaan','color'=>'#c62828'],
                    ['name'=>'Labuin','color'=>'#558b2f'],['name'=>'Malinao','color'=>'#1976d2'],
                    ['name'=>'Oogong','color'=>'#6d4c41'],['name'=>'Pagsawitan','color'=>'#4527a0'],
                    ['name'=>'Palasan','color'=>'#00695c'],['name'=>'Patimbao','color'=>'#e65100'],
                    ['name'=>'San Jose','color'=>'#0277bd'],['name'=>'San Juan','color'=>'#283593'],
                    ['name'=>'San Pablo Norte','color'=>'#33691e'],['name'=>'San Pablo Sur','color'=>'#827717'],
                    ['name'=>'Santisima Cruz','color'=>'#bf360c'],['name'=>'Santo Angel Central','color'=>'#37474f'],
                    ['name'=>'Santo Angel Norte','color'=>'#4e342e'],['name'=>'Santo Angel Sur','color'=>'#1a237e'],
                ];
                @endphp

                <div class="about-brgy-grid" id="brgyGrid">
                    @foreach($barangays as $brgy)
                    <button class="about-brgy-card" data-brgy="{{ $brgy['name'] }}" data-color="{{ $brgy['color'] }}" onclick="selectBarangay(this)">
                        <div class="about-brgy-avatar" style="background:{{ $brgy['color'] }};">{{ strtoupper(substr($brgy['name'],0,2)) }}</div>
                        <span class="about-brgy-name">{{ $brgy['name'] }}</span>
                        <svg class="about-brgy-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </button>
                    @endforeach
                </div>
                <p class="brgy-no-results" id="brgyNoResults" style="display:none;">No barangay found.</p>
            </div>
        </section>

        {{-- ── BARANGAY DETAILS ── --}}
        <section class="about-section alt-bg about-brgy-details" id="brgyDetails" style="display:none;">
            <div class="about-section-inner">
                <div class="about-brgy-details-header">
                    <div class="about-brgy-details-avatar" id="brgyDetailsAvatar">AL</div>
                    <div class="about-brgy-details-info">
                        <div class="section-eyebrow">Barangay Profile</div>
                        <h2 class="about-brgy-details-name" id="brgyDetailsName">Alipit</h2>
                        <p id="brgyDetailsDesc">A barangay in Santa Cruz, Laguna under the Sangguniang Kabataan.</p>
                    </div>
                    <button class="about-brgy-close" onclick="closeBarangayDetails()" title="Close">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
                <div class="about-brgy-stats">
                    <div class="about-brgy-stat"><span class="about-brgy-stat-num" id="statKabataan">—</span><span class="about-brgy-stat-lbl">Registered Kabataan</span></div>
                    <div class="about-brgy-stat"><span class="about-brgy-stat-num" id="statPrograms">—</span><span class="about-brgy-stat-lbl">Active Programs</span></div>
                    <div class="about-brgy-stat"><span class="about-brgy-stat-num" id="statCompleted">—</span><span class="about-brgy-stat-lbl">Completed Projects</span></div>
                    <div class="about-brgy-stat"><span class="about-brgy-stat-num" id="statBudget">—</span><span class="about-brgy-stat-lbl">Total Budget</span></div>
                </div>
                <div class="about-details-tabs">
                    <button class="about-details-tab active" data-tab="accomplishments">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        Accomplishments
                    </button>
                    <button class="about-details-tab" data-tab="budget">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        Budget Transparency
                    </button>
                    <button class="about-details-tab" data-tab="kk-profiling">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        KK Profiling
                    </button>
                </div>
                <div class="about-tab-panel active" id="tab-accomplishments">
                    <div class="about-empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                        <h3>No Data Available Yet</h3>
                        <p>Accomplishments and programs will appear here once SK officials submit their records.</p>
                    </div>
                </div>
                <div class="about-tab-panel" id="tab-budget">
                    <div class="about-empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        <h3>No Budget Data Yet</h3>
                        <p>Budget breakdown and project allocations will be displayed here once records are submitted.</p>
                    </div>
                </div>

                <div class="about-tab-panel" id="tab-kk-profiling">
                    <div class="about-kk-cta">
                        <div class="about-kk-cta-left">
                            <div class="about-kk-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <div>
                                <h3>KK Profiling</h3>
                                <p>Register your Katipunan ng Kabataan profile to participate in programs and activities. No login required.</p>
                            </div>
                        </div>
                        <a id="kkpFormLink" href="#" class="btn-hero-primary" style="white-space:nowrap;flex-shrink:0;">
                            Fill Out KK Form ?
                        </a>
                    </div>
                </div>            </div>
        </section>

        {{-- ── CONTACT SECTION ── --}}
        <section class="about-section alt-bg" id="contact">
            <div class="about-section-inner">
                <div class="section-eyebrow">Get in Touch</div>
                <h2>Contact Us</h2>
                <p class="section-lead">Have questions about SK OnePortal or want to know more about SK programs in Santa Cruz, Laguna? Reach out to us.</p>
                <div class="about-contact-grid">
                    <div class="about-contact-card">
                        <div class="about-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                        <h3>Address</h3>
                        <p>Municipal Hall, Santa Cruz, Laguna, Philippines</p>
                    </div>
                    <div class="about-contact-card">
                        <div class="about-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></div>
                        <h3>Phone</h3>
                        <p>+63 (049) 000-0000</p>
                    </div>
                    <div class="about-contact-card">
                        <div class="about-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                        <h3>Email</h3>
                        <p>sk@santacruz.gov.ph</p>
                    </div>
                    <div class="about-contact-card">
                        <div class="about-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                        <h3>Office Hours</h3>
                        <p>Mon–Fri: 8:00 AM – 5:00 PM</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── CTA ── --}}
        <section class="about-cta">
            <div class="about-cta-inner">
                <h2>Ready to get involved?</h2>
                <p>Explore programs, events, and activities from SK officials across Santa Cruz, Laguna.</p>
                <div class="about-hero-actions">
                    <a href="{{ route('login') }}" class="btn-hero-primary">Login Now</a>
                    <a href="{{ route('homepage') }}" class="btn-hero-ghost">Browse the Feed</a>
                </div>
            </div>
        </section>

    </main>

    {{-- ── JOIN COMMUNITY MODAL ── --}}
    <div id="joinCommunityModal" class="about-modal-wrap">
        <div class="about-modal-overlay" id="joinModalOverlay"></div>
        <div class="about-modal-box">
            <div class="about-modal-banner">
                <button class="about-modal-close" id="joinModalClose">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
                <div style="font-size:48px;margin-bottom:12px;position:relative;z-index:1;">🎉</div>
                <h2 style="color:#fff;font-size:22px;font-weight:800;margin-bottom:6px;position:relative;z-index:1;">Join the Community!</h2>
                <p style="color:rgba(255,255,255,0.8);font-size:14px;position:relative;z-index:1;">Be part of the SK OnePortal youth community in Santa Cruz, Laguna.</p>
            </div>
            <div class="about-modal-body">
                <div class="about-modal-perks">
                    <div class="about-modal-perk">✅ Apply for SK programs and track your applications</div>
                    <div class="about-modal-perk">💬 Like and comment on community posts</div>
                    <div class="about-modal-perk">🏘️ Browse barangay SK profiles and officers</div>
                </div>
                <div class="about-modal-actions">
                    <a href="{{ route('login') }}" class="btn-hero-primary" style="flex:1;text-align:center;padding:13px 0;font-size:15px;">Login</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="about-footer">
        <div class="about-footer-inner">
            <div class="footer-brand">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="footer-logo">
                <div>
                    <p class="footer-title">SK OnePortal Kabataan</p>
                    <p class="footer-sub">Santa Cruz, Laguna</p>
                </div>
            </div>
            <p class="footer-copy">&copy; {{ date('Y') }} SK OnePortal. Built for the youth of Santa Cruz, Laguna.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal=document.getElementById('joinCommunityModal');
        const overlay=document.getElementById('joinModalOverlay');
        const close=document.getElementById('joinModalClose');
        const shut=()=>modal?.classList.remove('active');
        close?.addEventListener('click',shut);
        overlay?.addEventListener('click',shut);
        document.addEventListener('keydown',(e)=>{if(e.key==='Escape')shut();});
        const hamburger=document.getElementById('navHamburger');
        const drawer=document.getElementById('navDrawer');
        if(hamburger&&drawer){
            hamburger.addEventListener('click',()=>drawer.classList.toggle('open'));
            document.addEventListener('click',(e)=>{if(!hamburger.contains(e.target)&&!drawer.contains(e.target))drawer.classList.remove('open');});
        }
        document.querySelectorAll('a[href^="#"]').forEach(a=>{
            a.addEventListener('click',function(e){
                const target=document.querySelector(this.getAttribute('href'));
                if(target){e.preventDefault();target.scrollIntoView({behavior:'smooth',block:'start'});drawer?.classList.remove('open');}
            });
        });
    });
    window.selectBarangay=function(btn){
        document.querySelectorAll('.about-brgy-card').forEach(c=>c.classList.remove('active'));
        btn.classList.add('active');
        const name=btn.dataset.brgy;
        const color=btn.dataset.color;
        const avatar=document.getElementById('brgyDetailsAvatar');
        const nameEl=document.getElementById('brgyDetailsName');
        const descEl=document.getElementById('brgyDetailsDesc');
        if(avatar){avatar.textContent=name.substring(0,2).toUpperCase();avatar.style.background=color;}
        if(nameEl)nameEl.textContent='Barangay '+name;
        if(descEl)descEl.textContent='A barangay in Santa Cruz, Laguna under the Sangguniang Kabataan. Data will be available once SK officials submit their records.';
        ['statKabataan','statPrograms','statCompleted','statBudget'].forEach(id=>{const el=document.getElementById(id);if(el)el.textContent='—';});
        const details=document.getElementById('brgyDetails');
        if(details){
            details.style.display='block';
            if(name==='Palasan')switchBrgyTab('kk-profiling');else switchBrgyTab('accomplishments');
            setTimeout(()=>details.scrollIntoView({behavior:'smooth',block:'start'}),100);
        }
        // Set KK Profiling form link
        const kkpLink=document.getElementById('kkpFormLink');
        if(kkpLink){
            const slug=name.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
            kkpLink.href='/kkprofiling/'+slug;
            kkpLink.textContent='Fill Out KK Form for '+name;
        }
    };
    window.closeBarangayDetails=function(){
        const details=document.getElementById('brgyDetails');
        if(details)details.style.display='none';
        document.querySelectorAll('.about-brgy-card').forEach(c=>c.classList.remove('active'));
    };
    function switchBrgyTab(tabName){
        document.querySelectorAll('.about-details-tab').forEach(t=>t.classList.toggle('active',t.dataset.tab===tabName));
        document.querySelectorAll('.about-tab-panel').forEach(p=>p.classList.toggle('active',p.id==='tab-'+tabName));
    }
    document.querySelectorAll('.about-details-tab').forEach(tab=>{tab.addEventListener('click',()=>switchBrgyTab(tab.dataset.tab));});
    const brgySearch=document.getElementById('brgySearch');
    const brgyGrid=document.getElementById('brgyGrid');
    const brgyNoRes=document.getElementById('brgyNoResults');
    if(brgySearch&&brgyGrid){
        brgySearch.addEventListener('input',()=>{
            const q=brgySearch.value.trim().toLowerCase();
            let visible=0;
            brgyGrid.querySelectorAll('.about-brgy-card').forEach(card=>{
                const show=card.dataset.brgy.toLowerCase().includes(q);
                card.style.display=show?'':'none';
                if(show)visible++;
            });
            if(brgyNoRes)brgyNoRes.style.display=visible===0?'block':'none';
        });
    }
    </script>
</body>
</html>
