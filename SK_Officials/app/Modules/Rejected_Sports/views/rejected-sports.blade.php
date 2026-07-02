<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejected Sports Applications - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
        'app/Modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
        'app/Modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css'
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rejected-kk-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Rejected Sports Applications</h1>
                <p class="page-subtitle">Sports applications that were revoked or rejected. You can restore records within 30 days. After 30 days, they will be automatically deleted.</p>
            </div>
        </section>

        <div class="module-stats-grid" id="rspStatsRow"></div>

        <div class="restore-success-banner" id="rspRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rspRestoreBannerText"></span>
        </div>

        <div class="schol-filters-row saf-sports-filters" style="margin-bottom:14px;">
            <select id="rspSportFilter" class="schol-filter-input" style="min-width:150px;">
                <option value="all">All Sports</option>
                <option value="basketball">Basketball</option>
                <option value="volleyball">Volleyball</option>
                <option value="other">Other</option>
            </select>
            <div class="schol-search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="rspTeamSearch" class="schol-search-input" placeholder="Filter by team name...">
            </div>
            <div class="schol-search-wrap" style="flex:1;min-width:200px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="rejectedSportsSearch" class="schol-search-input" placeholder="Search by name or program...">
            </div>
        </div>

        <div class="filter-tabs-row filter-tabs-row--with-archive">
            <div class="filter-tabs-group">
                <button class="filter-tab active" data-filter="all">All Rejected</button>
                <button class="filter-tab" data-filter="today">Rejected Today</button>
                <button class="filter-tab" data-filter="week">This Week</button>
                <button class="filter-tab" data-filter="month">This Month</button>
            </div>
            @include('layout::partials.archive-show-filter')
        </div>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title" id="rspSectionLabel">All Rejected Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="rkk-table">
                        <thead>
                            <tr>
                                <th>Full Name<div class="column-hint">LN, FN, MN</div></th>
                                <th>Sport</th>
                                <th>Team</th>
                                <th>Program</th>
                                <th>Rejection Reason</th>
                                <th>Rejected Date</th>
                                <th>Rejected Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rejectedSportsTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="rejectedSportsPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="rejectedSportsPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="rejectedSportsPageNumbers"></div>
                        <button type="button" id="rejectedSportsNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<div class="restore-modal-backdrop" id="rspRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Application</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this sports application back to Sports Program Requests?</p>
            <p class="restore-modal-name" id="rspRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="rspRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="rspRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<div class="restore-modal-backdrop" id="rspViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="rspViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Application Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="rspViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="rspViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="rspViewModalBody"></div>
    </div>
</div>

<div class="dk-toast" id="rspToast"></div>

<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Rejected_Sports/assets/js/rejected-sports.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
