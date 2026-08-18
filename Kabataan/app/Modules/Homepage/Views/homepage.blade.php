@extends('homepage::layout')

@section('title', $municipality['portal'])

@section('content')
        <section class="kabataan-hero" id="hero">
            <div class="container kabataan-shell kabataan-hero-grid">
                <div class="kabataan-hero-copy">
                    <span class="kabataan-eyebrow">Official youth portal · Santa Cruz, Laguna</span>
                    <h1>Your Voice. Your Programs. Your Barangay.</h1>
                    <p class="kabataan-hero-text">
                        Kabataan is the public youth portal of SK OnePortal. KK members aged 15–30 can learn about Sangguniang Kabataan programs, read barangay updates, and join community activities in one place.
                    </p>

                    <div class="kabataan-hero-actions">
                        <a href="{{ route('register') }}" class="kabataan-button kabataan-button-primary">Create Account</a>
                        <a href="{{ route('homepage') }}#about" class="kabataan-button kabataan-button-secondary">Learn More</a>
                    </div>
                </div>

                <div class="kabataan-hero-visual">
                    <div class="kabataan-hero-panel">
                        <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Kabataan logo" class="kabataan-hero-logo">
                        <h2>SK OnePortal Kabataan</h2>
                        <p>A clear, official way to stay connected with your barangay SK — without searching across separate pages.</p>
                        <ul class="kabataan-hero-points">
                            <li>Discover scholarships, sports, health, and livelihood programs</li>
                            <li>Read public ABYIP and program accomplishment records</li>
                            <li>Sign in to apply, track participation, and get announcements</li>
                        </ul>
                        <dl class="kabataan-hero-facts">
                            <div>
                                <dt>26</dt>
                                <dd>barangays</dd>
                            </div>
                            <div>
                                <dt>15–30</dt>
                                <dd>KK age range</dd>
                            </div>
                            <div>
                                <dt>Free</dt>
                                <dd>to join and use</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>

        <section class="kabataan-section" id="how-it-helps" aria-labelledby="valueHeading">
            <div class="kabataan-shell">
                <div class="kabataan-section-heading">
                    <span class="kabataan-eyebrow">What you can do</span>
                    <h2 id="valueHeading">Youth services in one portal</h2>
                    <p>Browse public information now. Create an account when you are ready to apply and participate.</p>
                </div>

                <div class="kabataan-value-grid">
                    @foreach ($valueProps as $prop)
                        <article class="kabataan-value-card">
                            <div class="kabataan-value-icon" aria-hidden="true">{!! $prop['icon'] !!}</div>
                            <h3>{{ $prop['title'] }}</h3>
                            <p>{{ $prop['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="kabataan-section kabataan-section-alt" id="explore" aria-labelledby="exploreHeading">
            <div class="kabataan-shell">
                <div class="kabataan-section-heading">
                    <span class="kabataan-eyebrow">Public pages</span>
                    <h2 id="exploreHeading">Explore Santa Cruz youth services</h2>
                    <p>These pages are available even before you sign in.</p>
                </div>

                <div class="kabataan-explore-grid">
                    @foreach ($publicLinks as $link)
                        <a href="{{ $link['href'] }}" class="kabataan-explore-card">
                            <h3>{{ $link['title'] }}</h3>
                            <p>{{ $link['text'] }}</p>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @include('homepage::about')
        @include('homepage::faqs')
@endsection
