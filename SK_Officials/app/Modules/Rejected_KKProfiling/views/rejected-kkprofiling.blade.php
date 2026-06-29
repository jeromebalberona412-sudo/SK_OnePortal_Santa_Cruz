<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejected KK Profiling - SK Officials Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
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
                <h1 class="page-title">Rejected KK Profiling</h1>
                <p class="page-subtitle">KK Profiling requests that were rejected. You can restore records within 30 days. After 30 days, they will be automatically deleted.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="rejectedKKSearch" class="filter-input" placeholder="Search by name or respondent #…">
            </div>
        </section>

        <div class="module-stats-grid" id="rkkStatsRow"></div>

        <div class="restore-success-banner" id="rkkRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rkkRestoreBannerText"></span>
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
                <h2 class="section-title" id="rkkSectionLabel">All Rejected Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="rkk-table">
                        <thead>
                            <tr>
                                <th>Respondent #</th>
                                <th>Full Name<div class="column-hint">LN, FN, MN, Suffix</div></th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Zone</th>
                                <th>Youth Classification</th>
                                <th>Rejection Reason</th>
                                <th>Rejected Date</th>
                                <th>Rejected Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rejectedKKTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="rejectedKKPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="rejectedKKPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="rejectedKKPageNumbers"></div>
                        <button type="button" id="rejectedKKNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<div class="restore-modal-backdrop" id="rkkRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this record back to KK Profiling Requests?</p>
            <p class="restore-modal-name" id="rkkRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="rkkRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="rkkRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<div class="restore-modal-backdrop dk-view-backdrop" id="rkkViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box dk-view-modal-box kk-view-modal-wide" id="rkkViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <div>
                <h2 class="restore-modal-title">KK Survey Questionnaire</h2>
                <span class="dk-view-subtitle">Rejected KK Profiling Record</span>
            </div>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="rkkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="rkkViewModalClose">&times;</button>
            </div>
        </div>
        <div class="dk-deleted-meta rkk-rejected-meta" id="rkkRejectedMeta">
            <div class="dk-deleted-meta-item">
                <span class="dk-deleted-meta-label">Rejection Reason</span>
                <span class="dk-deleted-meta-value" id="rkkViewRejectionReason">—</span>
            </div>
            <div class="dk-deleted-meta-item">
                <span class="dk-deleted-meta-label">Rejected Date</span>
                <span class="dk-deleted-meta-value" id="rkkViewRejectedDate">—</span>
            </div>
            <div class="dk-deleted-meta-item">
                <span class="dk-deleted-meta-label">Rejected Time</span>
                <span class="dk-deleted-meta-value" id="rkkViewRejectedTime">—</span>
            </div>
        </div>
        <div class="view-modal-body dk-view-modal-body kk-qs-body">
            <div class="kk-qs-scroll-wrapper">
                @include('KKProfilingRequests::partials.kk-profiling-view-questionnaire', [
                    'barangayLogoUrl' => $barangayLogoUrl ?? null,
                    'barangayName' => $barangayName ?? null,
                ])
            </div>
        </div>
    </div>
</div>

<div class="dk-toast" id="rkkToast"></div>

<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
