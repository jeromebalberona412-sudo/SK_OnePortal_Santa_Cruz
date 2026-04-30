<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule KK Profiling - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/ScheduleKKProfiling/assets/css/schedule-kkprofiling.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container schedule-kkp-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Schedule KK Profiling</h1>
                <p class="page-subtitle">
                    Manage and track KK Profiling schedule sessions.
                </p>
            </div>
        </section>

        <!-- ── Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="skkpStatUpcoming">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Upcoming</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="skkpStatOngoing">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Ongoing</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="skkpStatCompleted">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Completed</span>
            </div>
            <div class="stat-card stat-card-red">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="skkpStatCancelled">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Cancelled</span>
            </div>
            <div class="stat-card stat-card-purple">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="skkpStatRescheduled">0</span>
                    <div class="stat-card-icon stat-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Rescheduled</span>
            </div>
        </div>

        <!-- ── Action Bar ── -->
        <section class="page-filters-section">
            <div class="table-action-bar">
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
            <div class="section-heading-row">
                <h2 class="section-title">KK Profiling Schedules</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="skkp-table">
                        <thead>
                            <tr>
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
        </section>
    </div>
</main>

<!-- Pagination -->
<div class="pagination-container">
    <div class="pagination-info">
        <span id="skkpPaginationInfo">No records found</span>
    </div>
    <div class="pagination-controls">
        <button type="button" id="skkpPrevBtn" class="pagination-btn" disabled>Previous</button>
        <div class="pagination-numbers" id="skkpPageNumbers"></div>
        <button type="button" id="skkpNextBtn" class="pagination-btn">Next</button>
    </div>
</div>

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
                <div class="modal-field">
                    <label for="skkpFormDateStart">Date Start <span class="required">*</span></label>
                    <input type="date" id="skkpFormDateStart" class="skkp-input" required>
                </div>
                <div class="modal-field">
                    <label for="skkpFormDateExpiry">Date Expiry <span class="required">*</span></label>
                    <input type="date" id="skkpFormDateExpiry" class="skkp-input" required>
                </div>
                <div class="modal-field modal-field-full">
                    <label for="skkpFormLink">Link</label>
                    <input type="url" id="skkpFormLink" class="skkp-input" placeholder="https://example.com/form" maxlength="300">
                </div>
                <div class="modal-field">
                    <label for="skkpFormStatus">Status <span class="required">*</span></label>
                    <select id="skkpFormStatus" class="skkp-select" required>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Rescheduled">Rescheduled</option>
                    </select>
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

<!-- ── Delete Confirmation Modal ── -->
<div class="modal-backdrop skkp-modal-backdrop" id="skkpDeleteModal" style="display:none;">
    <div class="modal-box skkp-delete-modal-box skkp-modal-animate-small">
        <div class="modal-header skkp-delete-header">
            <h2 class="modal-title">Delete Schedule</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="skkp-delete-msg">Are you sure you want to delete this schedule? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel-form" id="skkpDeleteCancelBtn">Cancel</button>
            <button type="button" class="btn btn-danger" id="skkpDeleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/ScheduleKKProfiling/assets/js/schedule-kkprofiling.js'
])

</body>
</html>
<!-- PLACEHOLDER_KABATAAN_MODAL -->
