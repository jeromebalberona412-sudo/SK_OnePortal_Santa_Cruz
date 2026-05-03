<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Kabataan - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Deleted_Kabataan/assets/css/deleted-kabataan.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container deleted-kabataan-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Deleted Kabataan</h1>
                <p class="page-subtitle">Records that have been removed from the Kabataan list.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="deletedKabataanSearch" class="filter-input" placeholder="Search by name or purok…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="module-stats-grid" id="dkStatsRow"></div>

        <!-- Restore Success Banner -->
        <div class="restore-success-banner" id="dkRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="dkRestoreBannerText"></span>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs-row">
            <button class="filter-tab active" data-filter="all">All Deleted</button>
            <button class="filter-tab" data-filter="today">Deleted Today</button>
            <button class="filter-tab" data-filter="week">This Week</button>
            <button class="filter-tab" data-filter="month">This Month</button>
        </div>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title" id="dkSectionLabel">All Deleted Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="dk-table">
                        <thead>
                            <tr>
                                <th>Full Name<div class="column-hint">LN, FN, MN, Suffix</div></th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Sitio</th>
                                <th>Highest Education</th>
                                <th>Deleted Date</th>
                                <th>Deleted Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deletedKabataanTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="deletedKabataanPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="deletedKabataanPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="deletedKabataanPageNumbers"></div>
                        <button type="button" id="deletedKabataanNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="dkRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this record back to the Kabataan list?</p>
            <p class="restore-modal-name" id="dkRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="dkRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="dkRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="restore-modal-backdrop" id="dkViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="dkViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Record Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="dkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="dkViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="dkViewModalBody"></div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="dkToast"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Deleted_Kabataan/assets/js/deleted-kabataan.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
