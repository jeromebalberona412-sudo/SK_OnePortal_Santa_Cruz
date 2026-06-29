<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deleted Posts - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Announcement/assets/css/announcement-archive.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container archive-posts-page">

        <header class="archive-page-header">
            <div>
                <h1 class="archive-page-title">Deleted Posts</h1>
                <p class="archive-page-subtitle">Archived community feed posts. You can restore records within 30 days. After 30 days, they will be automatically deleted.</p>
            </div>
            <a href="{{ route('announcements') }}" class="archive-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to Community Feed
            </a>
        </header>

        <div class="archive-stats-row" id="archiveStatsRow"></div>

        <div class="archive-success-banner" id="archiveSuccessBanner" style="display:none;">
            <span class="archive-banner-icon">✓</span>
            <span id="archiveSuccessText"></span>
        </div>

        <div class="archive-toolbar">
            <input type="search" id="archiveSearch" class="archive-search-input" placeholder="Search archived posts…">
        </div>

        <div class="archive-list" id="archiveList">
            <div class="archive-loading">Loading archived posts…</div>
        </div>

        <div class="archive-pagination" id="archivePagination" style="display:none;">
            <button type="button" class="archive-page-btn" id="archivePrevBtn">Previous</button>
            <span class="archive-page-info" id="archivePageInfo"></span>
            <button type="button" class="archive-page-btn" id="archiveNextBtn">Next</button>
        </div>
    </div>
</main>

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
        dataUrl: @json(route('announcements.archive.data')),
        restoreUrl: (id) => @json(url('/announcements/archive')) + '/' + id + '/restore',
        csrf: @json(csrf_token()),
    };
</script>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Authentication/assets/js/loader.js',
    'app/Modules/Announcement/assets/js/announcement-archive.js',
])

</body>
</html>
