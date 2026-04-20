<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted ABYIP - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Deleted_Abyip/assets/css/deleted-abyip.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container deleted-abyip-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Deleted ABYIP</h1>
                <p class="page-subtitle">ABYIP records that have been removed.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="deletedAbyipSearch" class="filter-input" placeholder="Search by title…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="stats-cards-row" id="daStatsRow"></div>

        <!-- Restore Success Banner -->
        <div class="restore-success-banner" id="daRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="daRestoreBannerText"></span>
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
                <h2 class="section-title" id="daSectionLabel">All Deleted Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="da-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date Created</th>
                                <th>Time Created</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Deleted Date</th>
                                <th>Deleted Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="deletedAbyipTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="deletedAbyipPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="deletedAbyipPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="deletedAbyipPageNumbers"></div>
                        <button type="button" id="deletedAbyipNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="daRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this ABYIP record back to the active list?</p>
            <p class="restore-modal-name" id="daRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="daRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="daRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="restore-modal-backdrop" id="daViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="daViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Record Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="daViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="daViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="daViewModalBody"></div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="daToast"></div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Deleted_Abyip/assets/js/deleted-abyip.js'
])

</body>
</html>
