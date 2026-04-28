<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Programs - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/schedule_programs/assets/css/schedule-programs.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <!-- ── Page Header ── -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Program Scheduling</h1>
                <p class="page-subtitle">Manage and track scheduled programs and activities.</p>
            </div>
        </section>

        <!-- ── Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatUpcoming">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Upcoming</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatOngoing">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Ongoing</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatCompleted">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Completed</span>
            </div>
            <div class="stat-card stat-card-red">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatCancelled">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Cancelled</span>
            </div>
            <div class="stat-card stat-card-purple">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatRescheduled">0</span>
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
                <div class="sp-search-inline">
                    <div class="sp-search-wrapper">
                        <span class="sp-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="spSearch" class="sp-search-input" placeholder="Search schedules..." maxlength="80" autocomplete="off">
                    </div>
                </div>
                <select id="spStatusFilter" class="filter-select-inline">
                    <option value="">All Statuses</option>
                    <option value="Upcoming">Upcoming</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Rescheduled">Rescheduled</option>
                </select>
                <button type="button" id="spAddBtn" class="btn btn-add-schedule">
                    + Add Schedule Program
                </button>
            </div>
        </section>

        <!-- ── Table ── -->
        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Program Schedules</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="sp-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Activity Type</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="spTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span id="spPaginationInfo">No records found</span>
                </div>
                <div class="pagination-controls">
                    <button type="button" id="spPrevBtn" class="pagination-btn" disabled>Previous</button>
                    <div class="pagination-numbers" id="spPageNumbers"></div>
                    <button type="button" id="spNextBtn" class="pagination-btn">Next</button>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- ── Add / Edit Schedule Modal ── -->
<div class="sp-modal-overlay" id="spFormOverlay" style="display:none;">
    <div class="sp-modal-box">
        <div class="sp-modal-header">
            <h3 id="spModalTitle">Add Schedule Program</h3>
            <button type="button" class="sp-modal-close" id="spModalClose" aria-label="Close">&times;</button>
        </div>
        <div class="sp-modal-body">
            <input type="hidden" id="spHiddenId">

            <div class="sp-form-grid">
                <div class="sp-form-field">
                    <label for="spProgram">Program <span class="required">*</span></label>
                    <select id="spProgram" class="sp-select">
                        <option value="">— Select Program —</option>
                        <option value="Youth Leadership Training 2026">Youth Leadership Training 2026</option>
                        <option value="Sports Fest 2026">Sports Fest 2026</option>
                        <option value="Clean and Green Campaign">Clean and Green Campaign</option>
                        <option value="Health and Wellness Drive">Health and Wellness Drive</option>
                        <option value="Skills Training Workshop">Skills Training Workshop</option>
                        <option value="Livelihood Skills Development">Livelihood Skills Development</option>
                        <option value="Community Health Program">Community Health Program</option>
                        <option value="Environmental Awareness Program">Environmental Awareness Program</option>
                    </select>
                </div>
                <div class="sp-form-field">
                    <label for="spActivityType">Activity Type <span class="required">*</span></label>
                    <select id="spActivityType" class="sp-select">
                        <option value="">— Select Type —</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Clean-up">Clean-up</option>
                        <option value="Sports">Sports</option>
                        <option value="Feeding">Feeding</option>
                    </select>
                </div>
                <div class="sp-form-field">
                    <label for="spDate">Date Planned <span class="required">*</span></label>
                    <input type="date" id="spDate" class="sp-input">
                </div>
                <div class="sp-form-field">
                    <label for="spStartTime">Start Time <span class="required">*</span></label>
                    <input type="time" id="spStartTime" class="sp-input">
                </div>
                <div class="sp-form-field">
                    <label for="spEndTime">End Time</label>
                    <input type="time" id="spEndTime" class="sp-input">
                </div>
                <div class="sp-form-field">
                    <label for="spVenue">Venue <span class="required">*</span></label>
                    <input type="text" id="spVenue" class="sp-input" placeholder="e.g. Barangay Hall" maxlength="150">
                </div>
                <div class="sp-form-field">
                    <label for="spOfficials">Assigned Officials</label>
                    <input type="text" id="spOfficials" class="sp-input" placeholder="e.g. Juan dela Cruz, Maria Santos" maxlength="200">
                </div>
                <div class="sp-form-field">
                    <label for="spParticipants">Expected Participants</label>
                    <input type="number" id="spParticipants" class="sp-input" placeholder="e.g. 50" min="0">
                </div>
                <div class="sp-form-field">
                    <label for="spStatus">Status <span class="required">*</span></label>
                    <select id="spStatus" class="sp-select">
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Rescheduled">Rescheduled</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button type="button" class="btn btn-clear" id="spBtnClear">Clear</button>
            <button type="button" class="btn btn-save" id="spBtnSave">Save</button>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation ── -->
<div class="sp-modal-overlay" id="spDeleteOverlay" style="display:none;">
    <div class="sp-delete-box">
        <div class="sp-delete-header">
            <h3>Delete Schedule</h3>
            <button type="button" class="sp-modal-close" id="spDeleteCancel" aria-label="Close">&times;</button>
        </div>
        <div class="sp-delete-body">
            Are you sure you want to delete this schedule? This action cannot be undone.
        </div>
        <div class="sp-delete-footer">
            <button type="button" class="btn btn-clear" id="spDeleteCancelFooter">Cancel</button>
            <button type="button" class="btn btn-danger" id="spDeleteConfirm">Delete</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/schedule_programs/assets/js/schedule-programs.js'
])

</body>
</html>
