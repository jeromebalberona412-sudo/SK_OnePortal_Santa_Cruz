@extends('layout::app')

@section('title', 'SK Community Feed - SK OnePortal')

@push('body-attributes')
    class="sk-fed-feed"
@endpush

@push('styles')
    @php
        $communityFeedCssPath = app_path('Modules/CommunityFeed/assets/css/community-feed.css');
        $communityFeedCssVersion = file_exists($communityFeedCssPath) ? filemtime($communityFeedCssPath) : time();
    @endphp
    <link rel="stylesheet" href="{{ url('/modules/community-feed/css/community-feed.css') }}?v={{ $communityFeedCssVersion }}">
@endpush

@push('navbar-center')
    <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search posts..." aria-label="Search" id="feed-search-input">
        </div>
@endpush

@section('content')
<div class="cf-container">

            {{-- ── CENTER: Feed ── --}}
            <div class="feed-section">

                {{-- Compose Post --}}
                <div class="post-card compose-card">
                    <div class="compose-row">
                        <img src="{{ $avatar }}" alt="Avatar" class="post-avatar">
                        <button class="compose-trigger" onclick="openComposeModal()">
                            What's happening in your barangay?
                        </button>
                    </div>
                    <div class="compose-actions">
                        <button class="compose-action-btn" onclick="openComposeModal('announcement')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z"/></svg>
                            Announcement
                        </button>
                        <button class="compose-action-btn" onclick="openComposeModal('event')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                            Event
                        </button>
                        <button class="compose-action-btn" onclick="openComposeModal('photo')">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                            Photo
                        </button>
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="feed-filter-bar">
                    <button class="feed-tab active" data-filter="all" onclick="setFeedFilter(this,'all')">All</button>
                    <button class="feed-tab" data-filter="announcement" onclick="setFeedFilter(this,'announcement')">Announcements</button>
                    <button class="feed-tab" data-filter="event" onclick="setFeedFilter(this,'event')">Events</button>
                    <button class="feed-tab" data-filter="activity" onclick="setFeedFilter(this,'activity')">Activities</button>
                    <button class="feed-tab" data-filter="program" onclick="setFeedFilter(this,'program')">Programs</button>
                </div>

                {{-- Feed Posts --}}
                <div id="feed-posts"></div>
            </div>

            {{-- ── RIGHT: Barangay SK Profiles Sidebar ── --}}
            <aside class="feed-sidebar" id="feedSidebar">
                <div class="sidebar-card cf-barangay-profiles-card">
                    <h2 class="sidebar-title">Barangay SK Profiles</h2>
                    <p class="sidebar-subtitle">Browse SK officials from each barangay ({{ count($barangayProfiles ?? []) }} barangays).</p>
                    <div class="cf-barangay-profiles-list">
                        @forelse($barangayProfiles ?? [] as $brgy)
                        <a href="{{ route('skfed.barangay-profile', ['slug' => $brgy['slug']]) }}" class="cf-barangay-profile-link">
                            @if(!empty($brgy['logo_url']))
                                <img src="{{ $brgy['logo_url'] }}" alt="Brgy. {{ $brgy['name'] }}" class="cf-barangay-profile-logo">
                            @else
                                <div class="cf-barangay-profile-avatar">
                                    {{ $brgy['initials'] }}
                                </div>
                            @endif
                            <div class="cf-barangay-profile-text">
                                <p class="cf-barangay-profile-name">Brgy. {{ $brgy['name'] }}</p>
                                <p class="cf-barangay-profile-sub">SK Officials</p>
                            </div>
                            <svg class="chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                        @empty
                        <p class="cf-barangay-profiles-empty">No barangay profiles available.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
@endsection

@push('scripts')
    {{-- ── COMPOSE / EDIT POST MODAL ── --}}
    <div id="composeModal" class="program-modal">
        <div class="modal-overlay" onclick="closeComposeModal()"></div>
        <div class="modal-container" style="max-width:560px;">
            <div class="modal-header">
                <h2 id="compose-modal-title">Create Post</h2>
                <button class="modal-close" onclick="closeComposeModal()">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-post-id" value="">
                <div class="compose-author-row">
                    <img src="{{ $avatar }}" alt="Avatar" class="compose-avatar-sm">
                    <div>
                        <div class="compose-author-name">{{ $user->name ?? 'SK Official' }}</div>
                        <select class="compose-visibility-select" id="compose-visibility" aria-label="Visibility">
                            <option value="public">🌐 Public</option>
                            <option value="federation">🔒 Federation Only</option>
                        </select>
                    </div>
                </div>
                <div class="compose-type-row">
                    <label class="compose-type-label">Post Type:</label>
                    <select class="compose-type-select" id="compose-type" aria-label="Post type">
                        <option value="update">Update</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                        <option value="activity">Activity</option>
                        <option value="program">Youth Program</option>
                    </select>
                </div>
                <textarea class="compose-textarea" id="compose-content" placeholder="Write something..." rows="4"></textarea>
                <div class="compose-attach-row">
                    <label class="compose-attach-btn" for="compose-image-input" title="Upload Image">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                        Photo
                    </label>
                    <input type="file" id="compose-image-input" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <label class="compose-attach-btn" id="compose-link-btn" onclick="toggleLinkInput()" title="Add Link">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                        Link
                    </label>
                </div>
                <div id="compose-image-preview" style="display:none;margin-top:10px;position:relative;">
                    <img id="compose-preview-img" src="" alt="Preview" style="width:100%;border-radius:10px;max-height:220px;object-fit:cover;">
                    <button onclick="removeImagePreview()" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:28px;height:28px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
                <div id="compose-link-input-wrap" style="display:none;margin-top:10px;">
                    <input type="url" id="compose-link-input" class="compose-link-field" placeholder="Paste a link (https://...)">
                </div>
            </div>
            <div class="modal-footer-btns">
                <button class="btn-secondary" onclick="closeComposeModal()">Cancel</button>
                <button class="btn-primary" onclick="submitPost()">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                    Post
                </button>
            </div>
        </div>
    </div>

    {{-- ── LIKES MODAL ── --}}
    <div id="likesModal" class="cf-likes-modal" aria-hidden="true">
        <div class="modal-overlay" onclick="closeLikesModal()"></div>
        <div class="cf-likes-dialog" role="dialog" aria-labelledby="likesModalTitle">
            <div class="cf-likes-header">
                <h3 id="likesModalTitle">Reactions</h3>
                <button type="button" class="modal-close" onclick="closeLikesModal()" aria-label="Close">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>
            <div class="cf-likes-tabs">
                <button type="button" class="cf-likes-tab active" aria-current="true">
                    All <span id="likesModalCount">0</span>
                </button>
                <button type="button" class="cf-likes-tab" tabindex="-1" aria-hidden="true" style="cursor:default;">
                    <span class="cf-likes-tab-icon">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                    </span>
                    Like
                </button>
            </div>
            <div class="cf-likes-list" id="likesModalList">
                <div class="cf-likes-loading">Loading...</div>
            </div>
        </div>
    </div>

    <script>
        window.currentAvatar = @json($avatar ?? '');
    </script>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @php
        $communityFeedJsPath = app_path('Modules/CommunityFeed/assets/js/community-feed.js');
        $communityFeedJsVersion = file_exists($communityFeedJsPath) ? filemtime($communityFeedJsPath) : time();
    @endphp
    <script src="{{ url('/modules/community-feed/js/community-feed.js') }}?v={{ $communityFeedJsVersion }}"></script>
@endpush
