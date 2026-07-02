<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Archived Sports Programs - SK Officials Portal</title>
    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship-schedule.css',
        'app/Modules/Sports_Programs/assets/css/archived-sports-programs.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body>
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container asp-page asp-page-wrap">

        <section class="page-header-section asp-page-header">
            <div class="page-header-left">
                <h1 class="page-title">Archived Sports Programs</h1>
                <p class="page-subtitle">Sports programs removed from the active schedule. You can restore records within 30 days. After 30 days, they will be automatically deleted.</p>
            </div>
            <div class="page-header-right">
                <a href="{{ route('schedule-programs.sports-application-form') }}" class="schol-btn schol-btn-save">Back to Sports Programs</a>
            </div>
        </section>

        <div class="module-stats-grid asp-stats-row">
            <div class="stat-card stat-card-blue asp-stat-card">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="aspStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Archived</span>
            </div>
            <div class="stat-card stat-card-orange asp-stat-card asp-stat-warning">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="aspStatExpiring">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Expiring Soon</span>
            </div>
        </div>

        <section class="page-header-section asp-toolbar-row">
            <div class="page-header-right asp-search-wrap">
                <input type="text" id="aspSearch" class="filter-input" placeholder="Search by program name or type…">
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Archived Programs</h2>
            </div>
            <div class="table-card saf-forms-table-card">
                <div class="table-wrapper saf-table-wrap">
                    <table class="saf-forms-table asp-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Program Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Archived Date</th>
                                <th>Days Remaining</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="aspTableBody">
                            <tr><td colspan="7" class="saf-table-empty">Loading archived programs…</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="aspPaginationInfo">Loading archived programs…</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="aspPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="aspPageNumbers"></div>
                        <button type="button" id="aspNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<div class="schol-modal-overlay" id="aspRestoreModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header">
            <h3>Restore Program</h3>
            <button type="button" class="schol-modal-close" id="aspRestoreClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p>Restore this sports program back to the active schedule list?</p>
            <p class="asp-modal-name" id="aspRestoreName"></p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="aspRestoreCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="aspRestoreConfirm">Restore</button>
        </div>
    </div>
</div>

<div class="sports-toast" id="aspToast" style="display:none;"></div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Sports_Programs/assets/js/archived-sports-programs.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
