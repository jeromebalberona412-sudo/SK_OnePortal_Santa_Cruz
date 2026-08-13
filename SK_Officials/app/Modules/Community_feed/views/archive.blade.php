<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deleted Posts - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Layout/css/table-row-actions-menu.css',
        'app/Modules/Community_feed/assets/css/community-feed-archive.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container archive-posts-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Deleted Posts</h1>
                <p class="page-subtitle">Archived community feed posts. You can restore records within 30 days. After 30 days, they will be automatically deleted.</p>
            </div>
            <div class="page-header-right">
                <input type="search" id="archiveSearch" class="filter-input" placeholder="Search archived posts…" autocomplete="off">
            </div>
        </section>

        <div class="restore-success-banner" id="archiveSuccessBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="archiveSuccessText"></span>
        </div>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Archived Posts</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="ap-archive-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Posted By</th>
                                <th>Posted Date</th>
                                <th>Posted Time</th>
                                <th>Archived Date</th>
                                <th>Archived Time</th>
                                <th>Days Left</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="archiveTableBody">
                            <tr class="archive-loading-row">
                                <td colspan="8">Loading archived posts…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container" id="archivePagination">
                    <div class="pagination-info">
                        <span id="archivePageInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" class="pagination-btn" id="archivePrevBtn" disabled>Previous</button>
                        <div class="pagination-numbers" id="archivePageNumbers"></div>
                        <button type="button" class="pagination-btn" id="archiveNextBtn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

{{-- View post details --}}
<div id="viewPostModal" class="archive-view-backdrop" style="display:none;">
    <div class="archive-view-modal" role="dialog" aria-modal="true" aria-labelledby="archiveViewTitle">
        <div class="archive-view-header">
            <div>
                <h2 class="archive-view-title" id="archiveViewTitle">Post Details</h2>
                <p class="archive-view-subtitle" id="archiveViewSubtitle">Archived community post</p>
            </div>
            <button type="button" class="archive-view-close" id="archiveViewClose" aria-label="Close">&times;</button>
        </div>
        <div class="archive-view-body" id="archiveViewBody"></div>
        <div class="archive-view-footer">
            <button type="button" class="archive-btn archive-btn-muted" id="archiveViewCloseBtn">Close</button>
            <button type="button" class="archive-btn archive-btn-primary" id="archiveViewRestoreBtn">Restore Post</button>
        </div>
    </div>
</div>

{{-- Image lightbox --}}
<div id="archiveLightbox" class="archive-lightbox" aria-hidden="true">
    <button type="button" class="archive-lightbox-close" id="archiveLightboxClose" aria-label="Close">&times;</button>
    <button type="button" class="archive-lightbox-nav archive-lightbox-prev" id="archiveLightboxPrev" aria-label="Previous">&#8249;</button>
    <div class="archive-lightbox-stage">
        <img id="archiveLightboxImage" src="" alt="Post image">
        <span class="archive-lightbox-counter" id="archiveLightboxCounter"></span>
    </div>
    <button type="button" class="archive-lightbox-nav archive-lightbox-next" id="archiveLightboxNext" aria-label="Next">&#8250;</button>
</div>

{{-- Restore confirmation --}}
<div id="restoreModal" class="archive-modal">
    <div class="archive-modal-overlay" data-close-modal></div>
    <div class="archive-modal-box">
        <h2>Restore Post</h2>
        <p>This post will return to the active Community Feed immediately. All images will remain intact.</p>
        <div class="archive-modal-actions">
            <button type="button" class="archive-btn archive-btn-muted" data-close-modal>Cancel</button>
            <button type="button" class="archive-btn archive-btn-primary" id="confirmRestoreBtn">Restore Post</button>
        </div>
    </div>
</div>

<script>
    window.ArchiveConfig = {
        dataUrl: @json(route('community-feed.archive.data')),
        showUrl: (id) => @json(url('/community-feed/archive')) + '/' + id,
        restoreUrl: (id) => @json(url('/community-feed/archive')) + '/' + id + '/restore',
        csrf: @json(csrf_token()),
    };
</script>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Layout/js/table-row-actions-menu.js',
    'app/Modules/Community_feed/assets/js/community-feed-archive.js',
])

</body>
</html>
