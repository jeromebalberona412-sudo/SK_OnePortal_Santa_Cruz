@extends('homepage::layout')

@section('title', $municipality['portal'])

@section('content')
        <section class="kabataan-hero" id="hero">
            <div class="container kabataan-shell kabataan-hero-grid">
                <div class="kabataan-hero-copy">
                    <span class="kabataan-eyebrow">Pre-auth public homepage</span>
                    <h1>Your Voice. Your Programs. Your Barangay.</h1>
                    <p class="kabataan-hero-text">
                        Connect with your community through transparent programs and cross-barangay discovery for youth ages 15-30.
                    </p>

                    <div class="kabataan-hero-actions">
                        <a href="{{ route('sign-in') }}" class="kabataan-button kabataan-button-primary">Get Started</a>
                        <a href="{{ route('homepage') }}#about" class="kabataan-button kabataan-button-secondary">Learn More</a>
                    </div>

                    <div class="kabataan-hero-stats" aria-label="Platform highlights" style="display: none;">
                        @foreach ($heroStats as $stat)
                            <article class="kabataan-stat-pill">
                                <span>{{ $stat['value'] }}</span>
                                <small>{{ $stat['label'] }}</small>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="kabataan-hero-visual" aria-label="Kabataan community collage">
                    <div class="kabataan-hero-collage">
                        @foreach ($heroImages as $image)
                            <figure class="kabataan-collage-item collage-slot-{{ $loop->iteration }}">
                                <img src="{{ $image }}" alt="Kabataan community collage image {{ $loop->iteration }}">
                            </figure>
                        @endforeach
                    </div>

                    <div class="kabataan-float-card" style="display: none;">
                        <div class="kabataan-float-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20"/><path d="M5 9l7-7 7 7"/></svg>
                        </div>
                        <div>
                            <span>Active youth</span>
                            <strong>12,450+</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="kabataan-section" id="programs" style="display: none;">
            <div class="kabataan-shell">
                <div class="kabataan-section-heading kabataan-section-heading-row">
                    <div>
                        <span class="kabataan-eyebrow">Featured programs</span>
                        <h2>What's Happening This Month?</h2>
                        <p>Programs and activities will be shown here once they are available.</p>
                    </div>
                </div>

                <div class="kabataan-program-grid">
                    @if(count($featuredPrograms) === 0)
                        <p style="text-align: center; padding: 2rem; color: #666;">No programs available at this time.</p>
                    @else
                        @foreach ($featuredPrograms as $program)
                            <article class="kabataan-program-card">
                                <div class="kabataan-program-media">
                                    <img src="{{ $program['image'] }}" alt="{{ $program['title'] }}">
                                    <span class="kabataan-badge kabataan-badge-{{ strtolower($program['badge']) }}">{{ $program['badge'] }}</span>
                                </div>

                                <div class="kabataan-program-body">
                                    <p class="kabataan-program-location">📍 {{ $program['barangay'] }}</p>
                                    <h3>{{ $program['title'] }}</h3>
                                    <p>{{ $program['summary'] }}</p>

                                    <div class="kabataan-program-meta">
                                        <span>👥 {{ $program['joined'] }}</span>
                                        <span>💰 {{ $program['budget'] }}</span>
                                    </div>

                                    <div class="kabataan-progress">
                                        <div class="kabataan-progress-bar" style="width: {{ $program['progress'] }}%;"></div>
                                    </div>

                                    <div class="kabataan-program-actions">
                                        <a href="{{ route('sign-in') }}" class="kabataan-text-link">Learn More →</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>

        <section class="kabataan-section kabataan-section-alt" id="announcements" style="display: none;">
            <div class="kabataan-shell">
                <div class="kabataan-section-heading">
                    <span class="kabataan-eyebrow">Cross-barangay discovery</span>
                    <h2>Discover What's Happening Across Santa Cruz</h2>
                    <p>
                        Browse activities across barangays when they become available.
                    </p>
                </div>

                @if(count($barangayCards) > 0)
                    <div class="kabataan-discovery-toolbar">
                        <div class="kabataan-tab-row" role="tablist" aria-label="Barangay filters">
                            @foreach ($barangayTabs as $tab)
                                <button
                                    type="button"
                                    class="kabataan-tab {{ $loop->first ? 'active' : '' }}"
                                    data-filter="{{ $tab['key'] }}"
                                >
                                    {{ $tab['label'] }}
                                </button>
                            @endforeach
                        </div>

                        <label class="kabataan-search">
                            <span aria-hidden="true">🔍</span>
                            <input type="search" id="barangaySearch" placeholder="Search barangays, programs, or activities...">
                        </label>
                    </div>

                    <div class="kabataan-discovery-summary">
                        <span id="barangayResultLabel">Showing {{ count($barangayCards) }} highlights</span>
                        <span>Across 26 barangays</span>
                    </div>

                    <div class="kabataan-barangay-grid" id="barangayGrid">
                        @foreach ($barangayCards as $card)
                            <article
                                class="kabataan-barangay-card"
                                data-barangay="{{ strtolower($card['barangay']) }}"
                                data-category="{{ strtolower($card['category']) }}"
                                data-type="{{ strtolower($card['type']) }}"
                            >
                                <div class="kabataan-barangay-top">
                                    <div>
                                        <span class="kabataan-barangay-name">{{ $card['barangay'] }}</span>
                                        <span class="kabataan-barangay-type {{ $card['badgeClass'] }}">{{ $card['type'] }}</span>
                                    </div>
                                    <span class="kabataan-barangay-category">{{ $card['category'] }}</span>
                                </div>

                                <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}">

                                <div class="kabataan-barangay-body">
                                    <h3>{{ $card['title'] }}</h3>
                                    <p>{{ $card['summary'] }}</p>

                                    <div class="kabataan-barangay-meta">
                                        <span>📅 {{ $card['date'] }}</span>
                                        <span>👥 {{ $card['interested'] }}</span>
                                        <span>💰 {{ $card['budget'] }}</span>
                                    </div>

                                    <div class="kabataan-barangay-actions">
                                        <a href="{{ route('sign-in') }}" class="kabataan-button kabataan-button-primary kabataan-button-sm">View Details</a>
                                        <button type="button" class="kabataan-chip-button">Save</button>
                                        <button type="button" class="kabataan-chip-button">Share</button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="kabataan-discovery-footer">
                        <p>Showing {{ count($barangayCards) }} activities across 26 barangays.</p>
                        <a href="{{ route('register') }}" class="kabataan-text-link">Create an account to follow barangays →</a>
                    </div>
                @else
                    <p style="text-align: center; padding: 2rem; color: #666;">No activities available at this time.</p>
                @endif
            </div>
        </section>

        @include('homepage::about')
        @include('homepage::faqs')
@endsection
