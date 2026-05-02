<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Scholarship Applications - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Rejected_Scholarship/assets/css/rejected-scholarship.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rejected-schol-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Rejected Scholarship Applications</h1>
                <p class="page-subtitle">Scholarship applications that were rejected. Restore to move them back to active.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="rejectedScholSearch" class="filter-input" placeholder="Search by name or school…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="module-stats-grid" id="rsStatsRow"></div>

        <!-- Restore Success Banner -->
        <div class="restore-success-banner" id="rsRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rsRestoreBannerText"></span>
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
                <h2 class="section-title" id="rsSectionLabel">All Rejected Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="rs-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>School</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rejectedScholTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="rejectedScholPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="rejectedScholPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="rejectedScholPageNumbers"></div>
                        <button type="button" id="rejectedScholNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="rsRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Application</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this application back to the active scholarship list?</p>
            <p class="restore-modal-name" id="rsRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="rsRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="rsRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal — PDF layout -->
<div class="restore-modal-backdrop" id="rsViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="rsViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Application Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="rsViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="rsViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="rsViewModalBody"></div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="rsToast"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Rejected_Scholarship/assets/js/rejected-scholarship.js'
])

</body>
</html>
