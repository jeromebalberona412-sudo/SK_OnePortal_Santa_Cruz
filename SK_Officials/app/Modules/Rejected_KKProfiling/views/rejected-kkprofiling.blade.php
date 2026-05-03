<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected KK Profiling - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
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
                <p class="page-subtitle">KK Profiling requests that were rejected.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="rejectedKKSearch" class="filter-input" placeholder="Search by name…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="module-stats-grid" id="rkkStatsRow"></div>

        <!-- Restore Success Banner -->
        <div class="restore-success-banner" id="rkkRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rkkRestoreBannerText"></span>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs-row">
            <button class="filter-tab active" data-filter="all">All Rejected</button>
            <button class="filter-tab" data-filter="today">Rejected Today</button>
            <button class="filter-tab" data-filter="week">This Week</button>
            <button class="filter-tab" data-filter="month">This Month</button>
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

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="rkkRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this record back to KK Profiling?</p>
            <p class="restore-modal-name" id="rkkRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="rkkRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="rkkRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="restore-modal-backdrop" id="rkkViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="rkkViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Record Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="rkkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="rkkViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="rkkViewModalBody"></div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="rkkToast"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
