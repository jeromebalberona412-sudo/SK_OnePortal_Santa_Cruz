<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Schedule KK Profiling - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/ScheduleKKProfiling/assets/css/schedule-kkprofiling.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container schedule-kkp-page has-table-page-footer">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Schedule KK Profiling</h1>
                <p class="page-subtitle">
                    Manage and track KK Profiling schedule sessions.
                </p>
            </div>
            <div class="page-header-actions">
                <div class="filter-item filter-item-inline skkp-year-filter-wrap">
                    <label for="skkpYearFilter" class="abyip-sr-only">Profiling Year</label>
                    <select id="skkpYearFilter" class="filter-select filter-select-inline skkp-year-filter" aria-label="Profiling Year"></select>
                </div>
                <div class="abyip-search-inline">
                    <label for="skkpSearch" class="abyip-sr-only">Search schedules</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="skkpSearch" class="abyip-filter-search-inline" placeholder="Search schedules..." maxlength="80" autocomplete="off">
                    </div>
                </div>
                <button type="button" id="skkpCreateBtn" class="btn primary-btn btn-create">
                    + Create Schedule
                </button>
            </div>
        </section>

        <!-- ── Table ── -->
        <section class="page-content-section">
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="skkp-table">
                        <thead>
                            <tr>
                                <th>Profiling Year</th>
                                <th>Date Start</th>
                                <th>Date Expiry</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="skkpTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="skkp-page-footer table-page-footer pagination-footer" aria-label="Table pagination">
                <div class="pagination-footer-nav">
                    <button type="button" class="pagination-arrow" id="skkpPrevBtn" disabled aria-label="Previous page">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <span class="pagination-page-label">Page</span>
                    <input type="number" class="pagination-page-input" id="skkpPageInput" value="1" min="1" aria-label="Current page">
                    <span class="pagination-page-of">of <span id="skkpTotalPages">1</span></span>
                    <button type="button" class="pagination-arrow" id="skkpNextBtn" disabled aria-label="Next page">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
                <div class="pagination-footer-right">
                    <select id="skkpRowsPerPageSelect" class="pagination-rows-select" aria-label="Rows per page">
                        <option value="10">10 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                    </select>
                    <span class="pagination-record-count" id="skkpPaginationInfo">0 records</span>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- ── Create / Edit Schedule Modal ── -->
<div class="modal-backdrop skkp-modal-backdrop" id="skkpFormModal" style="display:none;">
    <div class="modal-box skkp-form-modal-box skkp-modal-animate">
        <div class="modal-header">
            <h2 class="modal-title" id="skkpFormModalTitle">Create Schedule</h2>
            <div class="modal-header-actions">
                <button type="button" class="modal-restore-btn" id="skkpFormRestoreBtn" title="Maximize / Restore">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                </button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body skkp-form-body">
            <input type="hidden" id="skkpEditId">
            <div class="skkp-form-grid">
                <div class="modal-field modal-field-full">
                    <label for="skkpFormProfilingYear">Profiling Year</label>
                    <input type="text" id="skkpFormProfilingYear" class="skkp-input skkp-input-readonly" readonly tabindex="-1" aria-readonly="true">
                    <span class="skkp-field-note">Auto-set to the current calendar year. Cannot be edited.</span>
                </div>
                <div class="modal-field">
                    <label for="skkpFormDateStartMd">Date Start <span class="required">*</span></label>
                    <div class="skkp-date-input-wrap">
                        <input type="text" id="skkpFormDateStartMd" class="skkp-input skkp-date-mdy" placeholder="MM/DD/YYYY" maxlength="10" autocomplete="off" inputmode="numeric" aria-label="Date Start">
                        <button type="button" class="skkp-date-calendar-btn" id="skkpFormDateStartPick" aria-label="Open calendar for Date Start" title="Pick date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </button>
                        <input type="date" id="skkpFormDateStartNative" class="skkp-date-native" tabindex="-1" aria-hidden="true">
                    </div>
                    <span class="skkp-field-error" id="skkpDateStartError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="modal-field">
                    <label for="skkpFormDateExpiryMd">Date Expiry <span class="required">*</span></label>
                    <div class="skkp-date-input-wrap">
                        <input type="text" id="skkpFormDateExpiryMd" class="skkp-input skkp-date-mdy" placeholder="MM/DD/YYYY" maxlength="10" autocomplete="off" inputmode="numeric" aria-label="Date Expiry">
                        <button type="button" class="skkp-date-calendar-btn" id="skkpFormDateExpiryPick" aria-label="Open calendar for Date Expiry" title="Pick date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </button>
                        <input type="date" id="skkpFormDateExpiryNative" class="skkp-date-native" tabindex="-1" aria-hidden="true">
                    </div>
                    <span class="skkp-field-error" id="skkpDateExpiryError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="modal-field modal-field-full">
                    <label for="skkpFormLink">Link</label>
                    <input type="url" id="skkpFormLink" class="skkp-input" placeholder="https://example.com/form" maxlength="300">
                    <span class="skkp-field-error" id="skkpLinkError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="modal-field">
                    <label for="skkpFormStatus">Status <span class="required">*</span></label>
                    <select id="skkpFormStatus" class="skkp-select" required>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Close">Close</option>
                    </select>
                    <div id="skkpStatusHint" class="skkp-status-hint"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel-form" id="skkpFormCancelBtn">Cancel</button>
            <button type="button" class="btn primary-btn" id="skkpFormSaveBtn">Save Schedule</button>
        </div>
    </div>
</div>

<!-- ── View Schedule Modal ── -->
<div class="modal-backdrop skkp-modal-backdrop" id="skkpViewModal" style="display:none;">
    <div class="modal-box skkp-view-modal-box skkp-modal-animate">
        <div class="modal-header">
            <h2 class="modal-title">Schedule Details</h2>
            <div class="modal-header-actions">
                <button type="button" class="modal-restore-btn" id="skkpViewRestoreBtn" title="Maximize / Restore">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                </button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body skkp-view-body">
            <div class="skkp-view-grid">
                <div class="skkp-view-row">
                    <span class="skkp-view-label">Profiling Year</span>
                    <span class="skkp-view-value" id="skkpViewProfilingYear">—</span>
                </div>
                <div class="skkp-view-row">
                    <span class="skkp-view-label">Date Start</span>
                    <span class="skkp-view-value" id="skkpViewDateStart">—</span>
                </div>
                <div class="skkp-view-row">
                    <span class="skkp-view-label">Date Expiry</span>
                    <span class="skkp-view-value" id="skkpViewDateExpiry">—</span>
                </div>
                <div class="skkp-view-row">
                    <span class="skkp-view-label">Link</span>
                    <span class="skkp-view-value" id="skkpViewLink">—</span>
                </div>
                <div class="skkp-view-row">
                    <span class="skkp-view-label">Status</span>
                    <span class="skkp-view-value" id="skkpViewStatus">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/ScheduleKKProfiling/assets/js/schedule-kkprofiling.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
<!-- PLACEHOLDER_KABATAAN_MODAL -->
