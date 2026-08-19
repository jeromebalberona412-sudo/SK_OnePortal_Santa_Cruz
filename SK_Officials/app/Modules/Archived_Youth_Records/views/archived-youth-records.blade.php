<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Archived Youth Records - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Archived_Youth_Records/assets/css/archived-youth-records.css',
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container archived-youth-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Archived Youth Records</h1>
                <p class="page-subtitle">Youth who aged out of KK Profiling eligibility (over 30) but may still sign in to view their account. These records are kept permanently and are not auto-deleted.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="ayrSearch" class="filter-input" placeholder="Search by name or respondent #…">
            </div>
        </section>

        <div class="module-stats-grid" id="ayrStatsRow"></div>

        <div class="filter-tabs-row filter-tabs-row--with-archive">
            <div class="filter-tabs-group">
                <button class="filter-tab active" data-filter="all">All Archived</button>
                <button class="filter-tab" data-filter="today">Archived Today</button>
                <button class="filter-tab" data-filter="week">This Week</button>
                <button class="filter-tab" data-filter="month">This Month</button>
            </div>
            @include('layout::partials.archive-show-filter')
        </div>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title" id="ayrSectionLabel">All Archived Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="ayr-table">
                        <thead>
                            <tr>
                                <th>Full Name<div class="column-hint">LN, FN, MN, Suffix</div></th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>City / Municipality</th>
                                <th>Purok / Sitio</th>
                                <th>Highest Education</th>
                                <th>Archived Date</th>
                                <th>Archived Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ayrTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="ayrPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="ayrPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="ayrPageNumbers"></div>
                        <button type="button" id="ayrNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<div class="restore-modal-backdrop ayr-view-backdrop" id="ayrViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box ayr-view-modal-box kk-view-modal-wide" id="ayrViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <div>
                <h2 class="restore-modal-title">KK Survey Questionnaire</h2>
                <span class="ayr-view-subtitle">Archived Youth Record</span>
            </div>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="ayrViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="ayrViewModalClose">&times;</button>
            </div>
        </div>
        <div class="ayr-archived-meta" id="ayrArchivedMeta">
            <div class="ayr-archived-meta-item">
                <span class="ayr-archived-meta-label">Archived Date</span>
                <span class="ayr-archived-meta-value" id="ayrViewArchivedDate">—</span>
            </div>
            <div class="ayr-archived-meta-item">
                <span class="ayr-archived-meta-label">Archived Time</span>
                <span class="ayr-archived-meta-value" id="ayrViewArchivedTime">—</span>
            </div>
            <div class="ayr-archived-meta-item">
                <span class="ayr-archived-meta-label">Reason</span>
                <span class="ayr-archived-meta-value" id="ayrViewArchiveReason">—</span>
            </div>
        </div>
        <div class="view-modal-body ayr-view-modal-body kk-qs-body">
            <div class="kk-qs-scroll-wrapper">
                @include('KKProfilingRequests::partials.kk-profiling-view-questionnaire', [
                    'barangayLogoUrl' => $barangayLogoUrl ?? null,
                    'barangayName' => $barangayName ?? null,
                ])
            </div>
        </div>
    </div>
</div>

<div class="ayr-toast" id="ayrToast"></div>

<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Archived_Youth_Records/assets/js/archived-youth-records.js'
])
</body>
</html>
