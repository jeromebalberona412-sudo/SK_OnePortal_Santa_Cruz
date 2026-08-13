@extends('layout::app')

@section('title', 'SK Barangay ' . ($name ?? '') . ' - SK OnePortal')

@push('styles')
    @php
        $communityFeedCssPath = base_path('app/Modules/CommunityFeed/assets/css/community-feed.css');
        $communityFeedCssVersion = file_exists($communityFeedCssPath) ? filemtime($communityFeedCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ url('/modules/community-feed/css/community-feed.css') }}?v={{ $communityFeedCssVersion }}">
@endpush

@push('navbar-center')
    <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search..." aria-label="Search">
        </div>
@endpush

@section('content')
<div class="bfp-wrap">

            {{-- Back link --}}
            <a href="{{ route('community-feed') }}" style="display:inline-flex;align-items:center;gap:6px;color:#213F99;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:16px;">
                <i class="fas fa-arrow-left"></i> Back to Community Feed
            </a>

            {{-- HEADER CARD --}}
            <div class="bfp-header-card">
                <div class="bfp-cover"></div>
                <div class="bfp-info-row">
                    <div class="bfp-avatar-wrap">
                        <div class="bfp-avatar">
                            @if(!empty($profile['logo_url']))
                                <img src="{{ $profile['logo_url'] }}" alt="SK Barangay {{ $name }} logo">
                            @else
                                {{ $profile['initials'] ?? strtoupper(substr($name, 0, 2)) }}
                            @endif
                        </div>
                    </div>
                    <div class="bfp-meta">
                        <div class="bfp-badge"><i class="fas fa-check-circle" style="font-size:10px;"></i> Sangguniang Kabataan</div>
                        <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                        <p class="bfp-loc"><i class="fas fa-map-marker-alt" style="color:#213F99;margin-right:4px;"></i>Barangay {{ $name }}, Santa Cruz, Laguna</p>
                        <div class="bfp-stats">
                            <div class="bfp-stat"><strong>{{ count($posts) }}</strong><span>Posts</span></div>
                            <div class="bfp-stat"><strong>{{ count($officials) }}</strong><span>Officers</span></div>
                            <div class="bfp-stat"><strong>2023–2026</strong><span>SK Term</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CONTENT GRID --}}
            <div class="bfp-grid">

                {{-- LEFT --}}
                <div class="bfp-left">

                    {{-- Officers --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-users"></i> SK Officers</div>
                        @forelse($officials as $o)
                        <div class="bfp-officer-item">
                            <div class="bfp-officer-dot">{{ $o['initials'] }}</div>
                            <div>
                                <p class="bfp-officer-name">{{ $o['name'] }}</p>
                                <p class="bfp-officer-role">{{ $o['role'] }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="bfp-empty">
                            <i class="fas fa-user-friends"></i>
                            No SK officials found for this barangay.
                        </div>
                        @endforelse
                    </div>

                    {{-- Barangay Info --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-info-circle"></i> Barangay Information</div>
                        @foreach([['Barangay',$name],['Municipality','Santa Cruz'],['Province','Laguna'],['Region','Region IV-A (CALABARZON)'],['SK Term','2023 – 2026'],['Total Officers',count($officials)]] as $row)
                        <div class="bfp-contact-row">
                            <div><p class="bfp-contact-label">{{ $row[0] }}</p><p class="bfp-contact-value">{{ $row[1] }}</p></div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Contact --}}
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-phone"></i> Contact Information</div>
                        @foreach([['fas fa-phone','Phone','[SK Contact Number]'],['fas fa-envelope','Email','[SK Email Address]'],['fas fa-map-marker-alt','Office Address','Barangay '.$name.' Hall, Santa Cruz, Laguna'],['fas fa-clock','Office Hours','Mon–Fri, 8:00 AM – 5:00 PM']] as $row)
                        <div class="bfp-contact-row">
                            <div class="bfp-contact-icon"><i class="{{ $row[0] }}"></i></div>
                            <div><p class="bfp-contact-label">{{ $row[1] }}</p><p class="bfp-contact-value">{{ $row[2] }}</p></div>
                        </div>
                        @endforeach
                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="bfp-right">
                    <div class="bfp-card">
                        <div class="bfp-card-title"><i class="fas fa-newspaper"></i> Posts from Barangay {{ $name }}</div>

                        <div class="bfp-feed-tabs" id="bfpTabs">
                            <button class="bfp-tab active" data-tab="all">All</button>
                            <button class="bfp-tab" data-tab="event">Events</button>
                            <button class="bfp-tab" data-tab="announcement">Announcements</button>
                            <button class="bfp-tab" data-tab="activity">Activities</button>
                        </div>

                        <div id="bfpFeed">
                            @forelse($posts as $post)
                            <div class="bfp-post" data-post-type="{{ $post['type_class'] }}">
                                <div class="bfp-post-header">
                                    <div class="bfp-post-avatar">
                                        @if(!empty($profile['logo_url']))
                                            <img src="{{ $profile['logo_url'] }}" alt="">
                                        @else
                                            {{ $profile['initials'] ?? strtoupper(substr($name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="bfp-post-author">{{ $post['author'] }}</p>
                                        <p class="bfp-post-meta">
                                            <span class="bfp-post-type {{ $post['type_class'] }}">{{ $post['type'] }}</span>
                                            {{ $post['posted_at'] }}
                                        </p>
                                    </div>
                                </div>
                                @if(!empty($post['title']))
                                    <h3 class="bfp-post-title">{{ $post['title'] }}</h3>
                                @endif
                                @if(!empty($post['text']))
                                    <p class="bfp-post-text">{{ $post['text'] }}</p>
                                @endif
                                @if(!empty($post['image_url']))
                                    <img src="{{ $post['image_url'] }}" alt="" class="bfp-post-image">
                                @endif
                                <div class="bfp-post-stats">
                                    <span><i class="fas fa-thumbs-up"></i> {{ $post['likes'] }}</span>
                                    <span><i class="fas fa-comment"></i> {{ $post['comments'] }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="bfp-empty">
                                <i class="fas fa-newspaper"></i>
                                No posts yet from this barangay.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.bfp-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.bfp-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var filter = tab.dataset.tab;
        document.querySelectorAll('#bfpFeed .bfp-post').forEach(function(post) {
            post.style.display = (filter === 'all' || post.dataset.postType === filter) ? 'block' : 'none';
        });
    });
});
</script>
@endpush
