<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Prototype - {{ $municipality['portal'] }}</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Homepage/assets/js/homepage.js',
    ])
</head>
<body class="homepage-body">
    <header class="home-nav">
        <div class="home-nav-inner">
            <div class="brand-block">
                <img src="/images/skoneportal_logo.webp" alt="SK OnePortal Logo" class="brand-logo">
                <div>
                    <p class="brand-overline">Prototype Page</p>
                    <h1>{{ $municipality['portal'] }}</h1>
                    <p class="brand-subtitle">{{ $municipality['name'] }}</p>
                </div>
            </div>
            <div class="home-nav-actions">
                <a href="{{ route('login') }}" class="nav-link ghost">Login</a>
                <a href="{{ route('register') }}" class="nav-link solid">Register</a>
            </div>
        </div>
    </header>

    <main class="home-main">
        <section class="hero-panel">
            <div>
                <p class="hero-label">Community Updates</p>
                <h2>Barangay Events and Programs in Santa Cruz, Laguna</h2>
                <p>{{ $municipality['description'] }}</p>
            </div>
            <div class="hero-chips" id="categoryFilterWrap">
                @foreach ($categories as $category)
                    <button
                        type="button"
                        class="chip-btn {{ $loop->first ? 'active' : '' }}"
                        data-filter-category="{{ strtolower($category) }}"
                    >
                        {{ $category }}
                    </button>
                @endforeach
            </div>
        </section>

        <div class="layout-grid">
            <aside class="left-panel">
                <h3>Barangays</h3>
                <p class="panel-caption">Filter feed by barangay.</p>
                <div class="barangay-list" id="barangayFilterWrap">
                    <button type="button" class="barangay-btn active" data-filter-barangay="all">All Barangays</button>
                    @foreach ($barangays as $barangay)
                        <button type="button" class="barangay-btn" data-filter-barangay="{{ strtolower($barangay) }}">
                            {{ $barangay }}
                        </button>
                    @endforeach
                </div>
            </aside>

            <section class="feed-panel" id="homepageFeed">
                @foreach ($feedItems as $item)
                    <article
                        class="feed-card"
                        data-item-category="{{ strtolower($item['category']) }}"
                        data-item-barangay="{{ strtolower($item['barangay']) }}"
                    >
                        <header class="feed-card-header">
                            <div class="avatar-badge">{{ strtoupper(substr($item['barangay'], 0, 1)) }}</div>
                            <div>
                                <h3>{{ $item['author'] }}</h3>
                                <p>
                                    <span class="type-pill">{{ $item['type'] }}</span>
                                    <span class="dot">&bull;</span>
                                    <span>{{ $item['posted_at'] }}</span>
                                </p>
                            </div>
                        </header>

                        <div class="feed-card-body">
                            <h4>{{ $item['title'] }}</h4>
                            <p class="summary">{{ $item['summary'] }}</p>

                            <div class="image-placeholder" role="img" aria-label="Image placeholder for {{ $item['title'] }}">
                                <p>Image Placeholder</p>
                                <span>No image uploaded yet</span>
                            </div>

                            <div class="details-grid">
                                <div>
                                    <p class="detail-label">Category</p>
                                    <p>{{ $item['category'] }}</p>
                                </div>
                                <div>
                                    <p class="detail-label">Barangay</p>
                                    <p>{{ $item['barangay'] }}</p>
                                </div>
                                <div>
                                    <p class="detail-label">Date</p>
                                    <p>{{ $item['event_date'] }}</p>
                                </div>
                                <div>
                                    <p class="detail-label">Time</p>
                                    <p>{{ $item['event_time'] }}</p>
                                </div>
                                <div>
                                    <p class="detail-label">Venue</p>
                                    <p>{{ $item['venue'] }}</p>
                                </div>
                                <div>
                                    <p class="detail-label">Audience</p>
                                    <p>{{ $item['audience'] }}</p>
                                </div>
                            </div>

                            <button type="button" class="toggle-more" data-details-toggle>
                                View Full Details
                            </button>
                            <p class="more-details" data-details-text hidden>{{ $item['details'] }}</p>
                        </div>
                    </article>
                @endforeach

                <div class="empty-state" id="emptyState" hidden>
                    <h3>No posts found</h3>
                    <p>Try a different category or barangay filter.</p>
                </div>
            </section>

            <aside class="right-panel">
                <h3>Santa Cruz Snapshot</h3>
                <div class="highlights-list">
                    @foreach ($highlights as $highlight)
                        <div class="highlight-item">
                            <p class="highlight-label">{{ $highlight['label'] }}</p>
                            <p class="highlight-value">{{ $highlight['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="read-only-box">
                    <h4>View-Only Prototype</h4>
                    <p>This page is for reviewing homepage layout and content presentation only.</p>
                    <ul>
                        <li>No post creation</li>
                        <li>No comment submission</li>
                        <li>No data updates</li>
                    </ul>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
