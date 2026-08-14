
<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Community Feed - SK Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Community_feed/assets/css/community-feed.css',
        'app/Modules/Community_feed/assets/css/community-feed-comment-preview.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
    <link rel="preload" href="{{ url('/sounds/reactions_ux.mp3') }}" as="audio" type="audio/mpeg">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content ann-main">
    <div class="ann-container">

        {{-- CENTER: Feed --}}
        <div class="feed-section">

            {{-- SK Officials Info Card (with compose embedded) --}}
            <div class="sk-fed-card">
                <div class="sk-fed-card-banner">
                    <img src="{{ $barangayLogoUrl ?? asset('images/logo.png') }}" alt="SK Barangay {{ $name }} logo" class="sk-fed-card-logo">
                    <div class="sk-fed-card-info">
                        <h2 class="sk-fed-card-name">SK Barangay {{ $name }}</h2>
                        <p class="sk-fed-card-sub">SK Officials Portal ? Santa Cruz, Laguna</p>
                        <a href="{{ route('community-feed.barangay', ['slug' => $slug]) }}" style="font-size:11px;color:rgba(255,255,255,0.85);font-weight:600;margin-top:4px;text-decoration:none;display:inline-block;">View Your Barangay Profile ?</a>
                    </div>
                </div>
                {{-- Create Post button --}}
                <div style="padding:12px 16px;background:#fff;border-top:1px solid #f0f0f0;">
                    <button onclick="openComposeModal()" style="width:100%;padding:9px;background:linear-gradient(135deg,#f5c518,#e6a800);color:#1a1a2e;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">
                        + Create Post
                    </button>
                </div>
            </div>

            {{-- Filter Tabs (sticky while scrolling) --}}
            <div class="feed-sticky-toolbar">
            <div class="feed-header">
                <div class="feed-header__intro">
                    <h1>SK Community Feed</h1>
                </div>
                <div class="feed-header__search">
                    <svg class="feed-header__search-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input type="search" id="feedSearchInput" class="feed-header__search-input" placeholder="Search posts, programs, announcements..." autocomplete="off" aria-label="Search community feed">
                </div>
            </div>

            <div class="feed-filter-bar">
                <button type="button" class="feed-tab feed-tab--icon active" data-filter="all" aria-label="All">
                    <span class="feed-tab-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                    <span class="feed-tab-text">All</span>
                </button>
                <button type="button" class="feed-tab feed-tab--icon" data-filter="announcement" aria-label="Announcements">
                    <span class="feed-tab-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11 13v8a2 2 0 004 0v-6"/></svg>
                    </span>
                    <span class="feed-tab-text">Announcements</span>
                </button>
                <button type="button" class="feed-tab feed-tab--icon" data-filter="event" aria-label="Events">
                    <span class="feed-tab-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <span class="feed-tab-text">Events</span>
                </button>
                <button type="button" class="feed-tab feed-tab--icon" data-filter="activity" aria-label="Activities">
                    <span class="feed-tab-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z"/></svg>
                    </span>
                    <span class="feed-tab-text">Activities</span>
                </button>
                <button type="button" class="feed-tab feed-tab--icon" data-filter="program" aria-label="Programs">
                    <span class="feed-tab-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                    </span>
                    <span class="feed-tab-text">Programs</span>
                </button>
            </div>
            </div>

            <div id="feed-posts"></div>

            <div style="text-align:center;padding:8px 0 16px;">
                <button class="load-more-btn" id="load-more-btn" onclick="loadMorePosts()">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                    Load More
                </button>
            </div>
        </div>

        {{-- RIGHT: Barangay Profiles Sidebar --}}
        <aside class="programs-sidebar" id="programsSidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title">Barangay SK Profiles</h2>
                <p class="sidebar-subtitle">Browse SK officials from each barangay.</p>
                <div class="brgy-link-list" id="brgyLinkList">
                    @forelse($barangayProfiles as $brgy)
                    <a href="{{ route('community-feed.barangay', ['slug' => $brgy['slug']]) }}" class="brgy-link-item">
                        <div class="brgy-link-dot">
                            @if(!empty($brgy['logo_url']))
                                <img src="{{ $brgy['logo_url'] }}" alt="Brgy. {{ $brgy['name'] }} logo" class="brgy-link-logo">
                            @else
                                <span class="brgy-link-initials">{{ $brgy['initials'] }}</span>
                            @endif
                        </div>
                        <div class="brgy-link-info">
                            <p class="brgy-link-name">Brgy. {{ $brgy['name'] }}</p>
                            <p class="brgy-link-sub">SK Officials</p>
                        </div>
                        <svg style="width:14px;height:14px;color:#bbb;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                    @empty
                    <p class="brgy-link-empty">No barangays found for your municipality.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</main>

<div class="programs-drawer-backdrop" id="programsDrawerBackdrop"></div>
<button class="programs-fab" id="programsFab" aria-label="View Barangay Profiles"><i class="fas fa-users"></i></button>

{{-- Compose Modal --}}
<div id="composeModal" class="program-modal compose-modal">
    <div class="modal-overlay" onclick="closeComposeModal()"></div>
    <div class="modal-container compose-modal-container" id="composeModalContainer">
        <div class="modal-header">
            <h2 id="compose-modal-title">Create Post</h2>
            <div class="compose-modal-window-actions">
                <button type="button" id="composeFullscreenBtn" class="compose-window-btn" onclick="toggleComposeFullscreen()" title="Full screen" aria-label="Full screen">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M3 3h5v2H5v3H3V3zm9 0h5v5h-2V5h-3V3zM3 12h2v3h3v2H3v-5zm12 0h2v5h-5v-2h3v-3z"/></svg>
                </button>
                <button class="modal-close" onclick="closeComposeModal()" aria-label="Close"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
            </div>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-post-id" value="">
            <div class="compose-type-row">
                <label class="compose-type-label">Post Type:</label>
                <select class="compose-type-select" id="compose-type">
                    <option value="update">Update</option>
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                    <option value="activity">Activity</option>
                    <option value="program">Youth Program</option>
                </select>
            </div>
            <textarea class="compose-textarea" id="compose-content" placeholder="What's on your mind?" rows="6" maxlength="2000"></textarea>
            <div class="compose-char-row">
                <span id="compose-char-count" class="compose-char-count">0 / 2000</span>
            </div>
            <div class="compose-attach-row">
                <label class="compose-attach-btn" for="compose-image-input"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg> Photo</label>
                <input type="file" id="compose-image-input" accept="image/*" multiple style="display:none;" onchange="previewImages(this)">
                <label class="compose-attach-btn" onclick="toggleLinkInput()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg> Link</label>
            </div>
            <p id="compose-images-meta" class="compose-images-meta"></p>
            <div id="compose-images-preview" class="compose-images-preview"></div>
            <div id="compose-link-input-wrap" style="display:none;margin-top:10px;">
                <input type="url" id="compose-link-input" class="compose-link-field" placeholder="Paste a link (https://...)">
            </div>
        </div>
        <div class="modal-footer-btns">
            <button class="btn-secondary" onclick="closeComposeModal()">Cancel</button>
            <button class="btn-primary" id="compose-post-btn" onclick="submitPost()"><svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg> Post</button>
        </div>
    </div>
</div>

<div id="feedToast" class="feed-toast" role="status" aria-live="polite"></div>

{{-- Image Lightbox --}}
<div id="imageLightbox" class="image-lightbox" aria-hidden="true">
    <button type="button" id="lightboxClose" class="lightbox-close" aria-label="Close">&times;</button>
    <div class="lightbox-toolbar">
        <button type="button" id="lightboxZoomOut" class="lightbox-tool-btn" aria-label="Zoom out">-</button>
        <span id="lightboxZoomLevel" class="lightbox-zoom-level">100%</span>
        <button type="button" id="lightboxZoomIn" class="lightbox-tool-btn" aria-label="Zoom in">+</button>
        <button type="button" id="lightboxZoomReset" class="lightbox-tool-btn lightbox-reset-btn" aria-label="Reset zoom">Reset</button>
    </div>
    <button type="button" id="lightboxPrev" class="lightbox-nav lightbox-prev" aria-label="Previous">&#10094;</button>
    <div class="lightbox-viewport" id="lightboxViewport">
        <img id="lightboxImage" src="" alt="Full size photo" draggable="false">
    </div>
    <button type="button" id="lightboxNext" class="lightbox-nav lightbox-next" aria-label="Next">&#10095;</button>
    <div id="lightboxCounter" class="lightbox-counter"></div>
</div>

{{-- Who reacted --}}
<div id="reactionViewerModal" class="reaction-viewer" aria-hidden="true">
    <div class="reaction-viewer-overlay" onclick="closeReactionViewer()"></div>
    <div class="reaction-viewer-panel" role="dialog" aria-label="People who reacted">
        <div class="reaction-viewer-header">
            <div class="reaction-viewer-tabs" id="reactionViewerTabs"></div>
            <button type="button" class="reaction-viewer-close" onclick="closeReactionViewer()" aria-label="Close">&times;</button>
        </div>
        <div class="reaction-viewer-list" id="reactionViewerList"></div>
    </div>
</div>

{{-- Archive Post confirmation --}}
<div id="archivePostModal" class="program-modal">
    <div class="modal-overlay" onclick="closeArchiveModal()"></div>
    <div class="modal-container" style="max-width:440px;">
        <div class="modal-header">
            <h2>Archive Post</h2>
            <button class="modal-close" onclick="closeArchiveModal()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:#555;line-height:1.65;margin:0;">
                This post will be moved to Archive.<br><br>
                It can be restored within 30 days.<br><br>
                After 30 days, the post and all uploaded images will be permanently deleted.
            </p>
        </div>
        <div class="modal-footer-btns">
            <button class="btn-secondary" onclick="closeArchiveModal()">Cancel</button>
            <button class="btn-primary" id="confirmArchiveBtn" onclick="confirmArchivePost()">Archive Post</button>
        </div>
    </div>
</div>

{{-- Edit comment --}}
<div id="editCommentModal" class="program-modal comment-action-modal">
    <div class="modal-overlay" onclick="closeEditCommentModal()"></div>
    <div class="modal-container" style="max-width:440px;">
        <div class="modal-header">
            <h2>Edit Comment</h2>
            <button type="button" class="modal-close" onclick="closeEditCommentModal()" aria-label="Close"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
        <div class="modal-body">
            <textarea id="editCommentBody" class="edit-comment-textarea" maxlength="500" placeholder="Write a comment..."></textarea>
        </div>
        <div class="modal-footer-btns">
            <button type="button" class="btn-secondary" onclick="closeEditCommentModal()">Cancel</button>
            <button type="button" class="btn-primary" id="confirmEditCommentBtn" onclick="confirmEditComment()">Save</button>
        </div>
    </div>
</div>

{{-- Delete comment --}}
<div id="deleteCommentModal" class="program-modal comment-action-modal">
    <div class="modal-overlay" onclick="closeDeleteCommentModal()"></div>
    <div class="modal-container" style="max-width:440px;">
        <div class="modal-header">
            <h2>Delete Comment</h2>
            <button type="button" class="modal-close" onclick="closeDeleteCommentModal()" aria-label="Close"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:#555;line-height:1.65;margin:0;">
                Delete this comment? This cannot be undone.
            </p>
        </div>
        <div class="modal-footer-btns">
            <button type="button" class="btn-secondary" onclick="closeDeleteCommentModal()">Cancel</button>
            <button type="button" class="btn-danger" id="confirmDeleteCommentBtn" onclick="confirmDeleteComment()">Delete</button>
        </div>
    </div>
</div>

{{-- Education Program Modal --}}
{{-- removed program modals --}}

@include('Community_feed::comment-preview')

<script>
window.CommunityFeedConfig = {
    defaultLogo: @json(asset('images/logo.png')),
    barangayLogo: @json($barangayLogoUrl),
    userAvatar: @json($barangayLogoUrl ?: asset('images/SK_OnePortal_logo.png')),
    userDisplayName: @json($user->name ?? 'SK Official'),
    feedPollMs: 30000,
    profilePreview: @json($profilePreview ?? null),
    commentsPageUrl: @json(url('/community-feed/__ID__/comments')),
};
window.CommentPreviewConfig = {
    post: @json($commentPreviewPost ?? null),
    defaultLogo: @json(asset('images/logo.png')),
    barangayLogo: @json($barangayLogoUrl),
    userAvatar: @json($barangayLogoUrl ?: asset('images/SK_OnePortal_logo.png')),
    userDisplayName: @json($user->name ?? 'SK Official'),
    feedUrl: @json(route('community-feed.index')),
};
</script>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Community_feed/assets/js/community-feed.js',
    'app/Modules/Community_feed/assets/js/community-feed-comment-preview.js',
])

<script>
function showFeedToast(message, type) {
    const el = document.getElementById('feedToast');
    if (!el) return;
    el.textContent = message;
    el.className = 'feed-toast feed-toast--' + (type || 'success') + ' is-visible';
    clearTimeout(window._feedToastTimer);
    window._feedToastTimer = setTimeout(() => el.classList.remove('is-visible'), 3200);
}
window.showFeedToast = showFeedToast;
</script>

{{-- Barangay Profile Preview Modal --}}
<div class="restore-modal-backdrop" id="profilePreviewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="profilePreviewModalBox">
        <div class="restore-modal-header view-modal-header">
            <div>
                <p class="profile-preview-kicker">Preview ? What Kabataan Sees</p>
                <h2 class="restore-modal-title" id="profilePreviewTitle">SK Barangay {{ $name }}</h2>
                <span class="dk-view-subtitle" id="profilePreviewLocation">Barangay {{ $name }}, Santa Cruz, Laguna</span>
            </div>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="profilePreviewToggle" aria-label="Full screen">?</button>
                <button type="button" class="view-modal-close" id="profilePreviewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="view-modal-body">
            <div class="record-view-profile-layout" id="profilePreviewContent">
                <div class="profile-field-group">
                    <div class="profile-field-group-label">Overview</div>
                    <div class="profile-field-row">
                        <div class="profile-field"><label>Posts</label><p id="profilePreviewPostCount">?</p></div>
                        <div class="profile-field"><label>SK Term</label><p id="profilePreviewTerm">?</p></div>
                        <div class="profile-field"><label>Officials</label><p id="profilePreviewOfficialCount">?</p></div>
                    </div>
                </div>
                <div class="profile-field-group">
                    <div class="profile-field-group-label">SK Officials</div>
                    <div id="profilePreviewOfficials" class="profile-preview-list"></div>
                </div>
                <div class="profile-field-group">
                    <div class="profile-field-group-label">Recent Posts</div>
                    <div id="profilePreviewPosts" class="profile-preview-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
