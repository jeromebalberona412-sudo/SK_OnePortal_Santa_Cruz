@extends('homepage::layout')

@section('title', 'About Kabataan - SK OnePortal')

{{-- fonts are loaded globally in layout --}}

@section('content')
<main class="about-page">
    <section class="about-hero">
        <div class="about-shell about-hero-grid">
            <div class="about-hero-copy">
                <span class="about-eyebrow">SK OnePortal Kabataan</span>
                <h1>Empowering the youth of Santa Cruz through clear, connected service.</h1>
                <p>
                    Kabataan is the youth-facing portal of SK_ONEPORTAL for Santa Cruz, Laguna.
                    It helps young residents discover programs, follow barangay updates, and understand how local youth governance works.
                </p>
                <div class="about-hero-actions">
                    <a href="{{ route('register') }}" class="about-btn about-btn-primary">Get Started</a>
                    <a href="{{ route('homepage') }}" class="about-btn about-btn-secondary">View Homepage</a>
                </div>
            </div>

            <div class="about-hero-panel">
                <div class="about-hero-badge-row">
                    <span class="about-hero-badge">Ages 13-30</span>
                    <span class="about-hero-badge muted">26 barangays</span>
                </div>
                <div class="about-hero-card">
                    <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Kabataan logo" class="about-hero-logo">
                    <h2>Built for youth discovery and participation</h2>
                    <p>
                        Browse opportunities, see budgets and impact, and stay informed about what your SK is doing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-shell">
            <div class="about-section-heading">
                <span class="about-eyebrow">Overview</span>
                <h2>What Kabataan is for</h2>
                <p>
                    The portal is designed around transparency, participation, and access so youth can quickly understand what matters in their barangay.
                </p>
            </div>

            <div class="about-stat-grid">
                <article class="about-stat-card">
                    <div class="about-stat-icon">1</div>
                    <h3>Discover Opportunities</h3>
                    <p>Find education, health, sports, agriculture, livelihood, and other youth programs in one place.</p>
                </article>
                <article class="about-stat-card">
                    <div class="about-stat-icon">2</div>
                    <h3>Participate Easily</h3>
                    <p>Join activities, react to announcements, and follow the latest updates from your barangay SK.</p>
                </article>
                <article class="about-stat-card">
                    <div class="about-stat-icon">3</div>
                    <h3>See Transparent Budgets</h3>
                    <p>Understand how youth projects are funded and track progress across activities and programs.</p>
                </article>
                <article class="about-stat-card">
                    <div class="about-stat-icon">4</div>
                    <h3>Build Your Profile</h3>
                    <p>Keep a digital record of accomplishments, engagement, and youth leadership participation.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section about-section-alt">
        <div class="about-shell about-two-col">
            <div>
                <span class="about-eyebrow">Mission & Vision</span>
                <h2>Why the portal exists</h2>
                <p class="about-lead">
                    Our mission is to make local youth programs discoverable, participation easy, and program funding transparent.
                </p>
            </div>
            <div class="about-mission-stack">
                <article class="about-info-card">
                    <h3>Mission</h3>
                    <p>To empower youth across Santa Cruz by making local programs discoverable, participation easy, and program funding transparent.</p>
                </article>
                <article class="about-info-card">
                    <h3>Vision</h3>
                    <p>An engaged, informed, and empowered generation of barangay youth who shape their communities through participation, accountability, and service.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-shell">
            <div class="about-section-heading">
                <span class="about-eyebrow">Who Can Join</span>
                <h2>Who the platform is built for</h2>
                <p>Residents and partners can use the portal based on their role and participation in the SK ecosystem.</p>
            </div>

            <div class="about-join-grid">
                <article class="about-join-card youth">
                    <h3>Youth residents</h3>
                    <p>Young people aged 13 to 30 living in Santa Cruz who want to discover programs and stay connected.</p>
                </article>
                <article class="about-join-card volunteer">
                    <h3>SK volunteers and trainees</h3>
                    <p>Youth helping with events, community activities, and barangay-based coordination.</p>
                </article>
                <article class="about-join-card partner">
                    <h3>Community partners</h3>
                    <p>Verified partners and supporters contributing to youth programs, awareness campaigns, and outreach.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section about-section-alt">
        <div class="about-shell">
            <div class="about-section-heading">
                <span class="about-eyebrow">Key Features</span>
                <h2>What Kabataan lets you do</h2>
                <p>The core experience matches the homepage: discover, follow, and participate without losing the bigger picture.</p>
            </div>

            <div class="about-feature-grid">
                <article class="about-feature-card">
                    <h3>Program discovery</h3>
                    <p>Browse events and programs by category or barangay to quickly find what fits your interests.</p>
                </article>
                <article class="about-feature-card">
                    <h3>Social participation</h3>
                    <p>React to announcements, save activities, and engage with what your local SK is sharing.</p>
                </article>
                <article class="about-feature-card">
                    <h3>Transparent budgets</h3>
                    <p>Review allocated budgets, progress metrics, and program outcomes in a clear presentation.</p>
                </article>
                <article class="about-feature-card">
                    <h3>Recognition and profile</h3>
                    <p>Build a visible record of community involvement, leadership, and accomplishments over time.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-shell about-two-col about-safety-block">
            <div>
                <span class="about-eyebrow">Governance & Safety</span>
                <h2>Designed for trusted public service</h2>
                <p class="about-lead">
                    Kabataan sits inside the broader SK_ONEPORTAL ecosystem and keeps role boundaries clear while supporting open, youth-friendly information access.
                </p>
            </div>
            <div class="about-safety-list">
                <div class="about-safety-item">Secure sign-up and transport-level encryption</div>
                <div class="about-safety-item">Content moderation and official response mechanisms</div>
                <div class="about-safety-item">Privacy-first defaults and limited data collection</div>
                <div class="about-safety-item">Role-scoped access for barangay and federation operations</div>
            </div>
        </div>
    </section>

    <section class="about-section about-section-alt">
        <div class="about-shell">
            <div class="about-section-heading">
                <span class="about-eyebrow">Impact</span>
                <h2>Early targets and outcomes</h2>
                <p>These targets keep the portal focused on real public value instead of surface-level activity.</p>
            </div>

            <div class="about-impact-grid">
                <article class="about-impact-card">
                    <strong>10,000+</strong>
                    <span>target youth across Santa Cruz</span>
                </article>
                <article class="about-impact-card">
                    <strong>50+</strong>
                    <span>active programs platform-wide</span>
                </article>
                <article class="about-impact-card">
                    <strong>26</strong>
                    <span>barangays connected through one portal</span>
                </article>
                <article class="about-impact-card">
                    <strong>100%</strong>
                    <span>public-facing clarity on opportunities and updates</span>
                </article>
            </div>
        </div>
    </section>

    <section class="about-section about-cta-section">
        <div class="about-shell about-cta-card">
            <div>
                <span class="about-eyebrow">Get Involved</span>
                <h2>Start with the homepage feed or jump straight into programs.</h2>
                <p>
                    Use Home for the live community feed, Programs for discovery, FAQs for support, and Contact if you need help.
                </p>
            </div>
            <div class="about-hero-actions">
                <a href="{{ route('homepage') }}" class="about-btn about-btn-primary">Home</a>
                <a href="{{ route('faqs') }}" class="about-btn about-btn-secondary">FAQs</a>
                <a href="{{ route('contact') }}" class="about-btn about-btn-secondary">Contact</a>
            </div>
        </div>
    </section>
</main>
@endsection
