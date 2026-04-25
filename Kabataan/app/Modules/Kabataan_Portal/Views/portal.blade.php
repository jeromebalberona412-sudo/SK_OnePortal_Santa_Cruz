<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabataan Portal – Santa Cruz, Laguna</title>
    @vite([
        'app/Modules/Kabataan_Portal/assets/css/portal.css',
        'app/Modules/Kabataan_Portal/assets/js/portal.js',
    ])
</head>
<body class="portal-body">

    {{-- ══════════════════════════════════════
         HEADER / NAVBAR
    ══════════════════════════════════════ --}}
    <header class="portal-header" id="portalHeader">
        <div class="header-container">
            {{-- Left: Logo + Name --}}
            <a href="#" class="header-brand">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Logo" class="header-logo">
                <span class="header-title">SK OnePortal</span>
            </a>

            {{-- Center: Nav Links --}}
            <nav class="header-nav" id="headerNav">
                <a href="#about"    class="nav-link">About</a>
                <a href="#services" class="nav-link">Services</a>
                <a href="#barangay" class="nav-link">Barangays</a>
                <a href="#contact"  class="nav-link">Contact</a>
            </nav>

            {{-- Right: Login only --}}
            <div class="header-actions">
                <a href="{{ route('login') }}" class="btn-login">Login</a>
                {{-- Mobile hamburger --}}
                <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    {{-- ══════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════ --}}
    <section class="hero-section" id="home">
        <div class="hero-bg">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content fade-in">
        <span class="hero-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Santa Cruz, Laguna
        </span>
            <h1 class="hero-title">Kabataan Portal</h1>
            <p class="hero-subtitle">
                Explore your barangay, view accomplishments,<br>and stay informed.
            </p>
            <div class="hero-cta-group">
                <a href="#barangay" class="btn-hero-primary">Explore Barangays</a>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         STATS STRIP
    ══════════════════════════════════════ --}}
    <section class="stats-strip fade-in-up">
        <div class="container-xl py-4">
            <div class="row justify-content-center text-center g-0">
                <div class="col-6 col-md-3 stat-item">
                    <span class="stat-number">26</span>
                    <span class="stat-label d-block">Barangays</span>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <span class="stat-number">—</span>
                    <span class="stat-label d-block">Registered Kabataan</span>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <span class="stat-number">—</span>
                    <span class="stat-label d-block">Active Programs</span>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <span class="stat-number">—</span>
                    <span class="stat-label d-block">Projects Completed</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         ABOUT SECTION
    ══════════════════════════════════════ --}}
    <section class="about-section" id="about">
        <div class="container-xl py-5">
            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-8 text-center">
                    <div class="section-label section-reveal">About SK OnePortal</div>
                    <h2 class="section-title section-reveal">Empowering the Youth of Santa Cruz</h2>
                    <p class="section-desc section-reveal mx-auto mb-0">
                        SK OnePortal is the official digital platform of the Sangguniang Kabataan of Santa Cruz, Laguna.
                        It serves as a transparent, accessible hub for youth programs, barangay accomplishments,
                        and budget information — connecting every kabataan to their local government.
                    </p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-12 col-md-4">
                    <div class="about-card fade-in-up h-100">
                        <div class="about-icon green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </div>
                        <h3>Youth Representation</h3>
                        <p>Every barangay's SK officials are represented, ensuring all voices are heard across Santa Cruz.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="about-card fade-in-up h-100">
                        <div class="about-icon blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <path d="M8 21h8M12 17v4"/>
                            </svg>
                        </div>
                        <h3>Digital Transparency</h3>
                        <p>Budget allocations, project statuses, and accomplishments are publicly accessible for full accountability.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="about-card fade-in-up h-100">
                        <div class="about-icon yellow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <h3>Community Programs</h3>
                        <p>From education to livelihood, SK programs are designed to uplift and empower every young Filipino.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         SERVICES SECTION
    ══════════════════════════════════════ --}}
    <section class="services-section" id="services">
        <div class="container-xl py-5">
            <div class="section-label section-reveal">What We Offer</div>
            <h2 class="section-title section-reveal">Portal Services</h2>
            <p class="section-desc section-reveal">Access a range of youth-focused services through SK OnePortal.</p>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </div>
                        <h3>Barangay Information</h3>
                        <p>View detailed profiles of all 26 barangays in Santa Cruz, including SK officials and community data.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                        </div>
                        <h3>Accomplishments Tracker</h3>
                        <p>Monitor completed and ongoing programs and projects per barangay with real-time status updates.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                            </svg>
                        </div>
                        <h3>Budget Transparency</h3>
                        <p>Full breakdown of budget allocations per project — promoting accountability and public trust.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                        <h3>KK Profiling</h3>
                        <p>Register and update your Katipunan ng Kabataan profile to participate in SK programs and activities.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8h1a4 4 0 010 8h-1"/>
                                <path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>
                                <line x1="6" y1="1" x2="6" y2="4"/>
                                <line x1="10" y1="1" x2="10" y2="4"/>
                                <line x1="14" y1="1" x2="14" y2="4"/>
                            </svg>
                        </div>
                        <h3>Community Feed</h3>
                        <p>Stay updated with the latest announcements, events, and activities from SK officials across all barangays.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card fade-in-up h-100">
                        <div class="service-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </div>
                        <h3>Program Directory</h3>
                        <p>Browse all SK programs by category — education, health, sports, livelihood, environment, and more.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         BARANGAY SELECTION SECTION
    ══════════════════════════════════════ --}}
    <section class="barangay-section" id="barangay">
        <div class="container-xl py-5">
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-lg-8 text-center">
                    <div class="section-label section-reveal">Explore</div>
                    <h2 class="section-title section-reveal">Select a Barangay</h2>
                    <p class="section-desc section-reveal mx-auto mb-0">Choose a barangay to view its accomplishments, programs, and budget transparency.</p>
                </div>
            </div>

            {{-- Search --}}
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-md-6">
                    <div class="brgy-search-wrapper mb-0">
                        <svg class="brgy-search-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" id="brgySearch" class="brgy-search-input" placeholder="Search barangay...">
                    </div>
                </div>
            </div>

            {{-- Barangay Grid --}}
            <div class="brgy-grid" id="brgyGrid">
                @php
                $barangays = [
                    'Alipit','Bagumbayan','Barangay I (Poblacion I)','Barangay II (Poblacion II)',
                    'Barangay III (Poblacion III)','Barangay IV (Poblacion IV)','Barangay V (Poblacion V)',
                    'Bubukal','Calios','Duhat','Gatid','Jasaan','Labuin','Malinao','Oogong',
                    'Pagsawitan','Palasan','Patimbao','San Jose','San Juan','San Pablo Norte',
                    'San Pablo Sur','Santisima Cruz','Santo Angel Central','Santo Angel Norte','Santo Angel Sur',
                ];
                $colors = ['#44a53e','#0450a8','#fdc020','#e53e3e','#9c27b0','#00897b','#f57c00','#1565c0','#2e7d32','#6a1b9a'];
                @endphp

                @foreach($barangays as $i => $brgy)
                @php $color = $colors[$i % count($colors)]; @endphp
                <button
                    class="brgy-card"
                    data-brgy="{{ $brgy }}"
                    data-color="{{ $color }}"
                    onclick="selectBarangay(this)"
                >
                    <div class="brgy-card-avatar" style="background: {{ $color }};">
                        {{ strtoupper(substr($brgy, 0, 2)) }}
                    </div>
                    <span class="brgy-card-name">{{ $brgy }}</span>
                    <svg class="brgy-card-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
                @endforeach
            </div>

            <p class="brgy-no-results" id="brgyNoResults" style="display:none;">No barangay found.</p>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         BARANGAY DETAILS SECTION (Dynamic)
    ══════════════════════════════════════ --}}
    <section class="brgy-details-section" id="brgyDetails" style="display:none;">
        <div class="container-xl py-5">

            {{-- Details Header --}}
            <div class="brgy-details-header">
                <div class="brgy-details-avatar" id="brgyDetailsAvatar">AL</div>
                <div class="brgy-details-info">
                    <div class="section-label">Barangay Profile</div>
                    <h2 class="brgy-details-name" id="brgyDetailsName">Alipit</h2>
                    <p class="brgy-details-desc" id="brgyDetailsDesc">
                        A barangay in Santa Cruz, Laguna under the jurisdiction of the Sangguniang Kabataan.
                    </p>
                </div>
                <button class="brgy-details-close" onclick="closeBarangayDetails()" title="Close">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            {{-- Stats Row --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="brgy-stat-card">
                        <span class="brgy-stat-num" id="statKabataan">—</span>
                        <span class="brgy-stat-lbl">Registered Kabataan</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="brgy-stat-card">
                        <span class="brgy-stat-num" id="statPrograms">—</span>
                        <span class="brgy-stat-lbl">Active Programs</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="brgy-stat-card">
                        <span class="brgy-stat-num" id="statCompleted">—</span>
                        <span class="brgy-stat-lbl">Completed Projects</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="brgy-stat-card">
                        <span class="brgy-stat-num" id="statBudget">—</span>
                        <span class="brgy-stat-lbl">Total Budget</span>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="details-tabs">
                <button class="details-tab active" data-tab="accomplishments">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Accomplishments
                </button>
                <button class="details-tab" data-tab="budget">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                    </svg>
                    Budget Transparency
                </button>
            </div>

            {{-- Accomplishments Tab --}}
            <div class="tab-panel active" id="tab-accomplishments">
                <div class="empty-details-state">
                    <div class="empty-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <line x1="9" y1="12" x2="15" y2="12"/>
                            <line x1="9" y1="16" x2="13" y2="16"/>
                        </svg>
                    </div>
                    <h3>No Data Available Yet</h3>
                    <p>Accomplishments and programs for this barangay will appear here once data is added by SK officials.</p>
                </div>
            </div>

            {{-- Budget Tab --}}
            <div class="tab-panel" id="tab-budget">
                <div class="empty-details-state">
                    <div class="empty-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <h3>No Budget Data Yet</h3>
                    <p>Budget breakdown and project allocations will be displayed here once records are submitted.</p>
                </div>
            </div>

            {{-- KK Profiling CTA --}}
            <div class="kk-cta-banner">
                <div class="kk-cta-content">
                    <div class="kk-cta-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <h3>KK Profiling</h3>
                        <p>Register your Katipunan ng Kabataan profile to participate in programs and activities.</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="btn-kk-profiling" id="kkProfilingBtn">
                    KK Profiling
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>

        </div>
    </section>

    {{-- ══════════════════════════════════════
         CONTACT SECTION
    ══════════════════════════════════════ --}}
    <section class="contact-section" id="contact">
        <div class="container-xl py-5">
            <div class="section-label section-reveal">Get in Touch</div>
            <h2 class="section-title section-reveal">Contact Us</h2>
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="contact-card fade-in-up h-100">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <h3>Address</h3>
                        <p>Municipal Hall, Santa Cruz, Laguna, Philippines</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="contact-card fade-in-up h-100">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                        </div>
                        <h3>Phone</h3>
                        <p>+63 (049) 000-0000</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="contact-card fade-in-up h-100">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <h3>Email</h3>
                        <p>sk@santacruz.gov.ph</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="contact-card fade-in-up h-100">
                        <div class="contact-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h3>Office Hours</h3>
                        <p>Mon–Fri: 8:00 AM – 5:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         FOOTER
    ══════════════════════════════════════ --}}
    <footer class="portal-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal" class="footer-logo">
                <div>
                    <div class="footer-brand-name">SK OnePortal</div>
                    <div class="footer-brand-sub">Kabataan Portal – Santa Cruz, Laguna</div>
                </div>
            </div>

            <div class="footer-links-group">
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <a href="#home">Home</a>
                    <a href="#about">About</a>
                    <a href="#services">Services</a>
                    <a href="#barangay">Barangays</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="footer-col">
                    <h4>Services</h4>
                    <a href="#">Barangay Profiles</a>
                    <a href="#">Accomplishments</a>
                    <a href="#">Budget Transparency</a>
                    <a href="#">KK Profiling</a>
                    <a href="#">Community Feed</a>
                </div>
                <div class="footer-col">
                    <h4>Contact</h4>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>Santa Cruz, Laguna
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .84h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>+63 (049) 000-0000
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>sk@santacruz.gov.ph
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>Mon–Fri 8AM–5PM
                    </span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} SK OnePortal – Santa Cruz, Laguna. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
