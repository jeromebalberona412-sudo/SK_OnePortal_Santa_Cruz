<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rejected Scholarship Applications - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Rejected_Scholarship/assets/css/rejected-scholarship.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body class="has-table-page-footer">

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rejected-schol-page schol-page-container">

        @include('Program_Management::scholarship.partials.page-top', [
            'activeTab' => 'rejected',
            'pageTitle' => 'Rejected Scholars',
            'pageSubtitle' => 'Scholarship applications that were rejected. You can restore records within 30 days. After 30 days, they will be automatically deleted.',
        ])

        <section class="page-header-section rs-toolbar-row">
            <div class="page-header-right rs-search-wrap">
                <input type="text" id="rejectedScholSearch" class="filter-input" placeholder="Search by name or school…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="module-stats-grid" id="rsStatsRow"></div>

        <!-- Restore Success Banner (legacy — toast used instead) -->
        <div class="restore-success-banner" id="rsRestoreBanner" style="display:none;" aria-hidden="true">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rsRestoreBannerText"></span>
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
                @include('Program_Management::scholarship.partials.table-pagination', ['prefix' => 'scholRej'])
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
<div class="scholarship-toast" id="scholarshipToast" style="display:none;" role="status" aria-live="polite">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="scholarshipToastMsg"></span>
</div>

<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Layout/js/table-row-actions-menu.js',
    'app/Modules/Layout/js/table-page-footer.js',
    'app/Modules/Program_Management/assets/css/scholarship/scholarship-toast.css',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-toast.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-system-fields.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-view-shared.js',
    'app/Modules/Rejected_Scholarship/assets/js/rejected-scholarship.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
