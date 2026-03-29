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
            <div class="navbar-left">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="navbar-logo">
                <span class="navbar-title">SK OnePortal</span>
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
                    <a href="{{ route('register') }}" class="btn-hero-ghost">Create an Account</a>
                </div>
            </div>
        </section>

        {{-- ── WHAT IS SK ── --}}
        <section class="about-section">
            <div class="about-section-inner">
                <div class="section-label">What is the SK?</div>
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
        <section class="about-section alt-bg">
            <div class="about-section-inner">
                <div class="section-label">The Platform</div>
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
                <div class="section-label">Leadership</div>
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
                <div class="section-label">The Team</div>
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

        {{-- ── CTA ── --}}
        <section class="about-cta">
            <div class="about-cta-inner">
                <h2>Ready to get involved?</h2>
                <p>Create your account and start exploring programs, events, and activities from SK officials across Santa Cruz, Laguna.</p>
                <div class="about-hero-actions">
                    <a href="{{ route('register') }}" class="btn-hero-primary">Sign Up Now</a>
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
                    <a href="{{ route('register') }}" class="btn-hero-primary" style="flex:1;text-align:center;padding:13px 0;font-size:15px;">Sign Up — It's Free!</a>
                    <a href="{{ route('login') }}" class="btn-hero-ghost" style="flex:1;text-align:center;padding:13px 0;font-size:15px;border-color:#0450a8;color:#0450a8;">Login</a>
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
        const modal   = document.getElementById('joinCommunityModal');
        const overlay = document.getElementById('joinModalOverlay');
        const close   = document.getElementById('joinModalClose');
        const open    = () => modal.classList.add('active');
        const shut    = () => modal.classList.remove('active');

        document.getElementById('navLoginBtn')?.addEventListener('click', open);
        document.getElementById('navSignupBtn')?.addEventListener('click', open);
        close?.addEventListener('click', shut);
        overlay?.addEventListener('click', shut);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') shut(); });
    });
    </script>
</body>
</html>
