<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Program List - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_application_from.css',
        'app/Modules/schedule_programs/assets/css/sports/sports_requests.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-program-key="sports">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">

    @include('schedule_programs::partials.program-page-top', [
        'activeTab' => 'list',
        'pageTitle' => 'Sports Program List',
        'pageSubtitle' => 'Approved participants for sports programs.',
        'programType' => 'sports',
        'programTitle' => 'Sports Committee',
        'programDescription' => 'Manage sports development programs, track athletic activities, and evaluate youth participation.',
    ])

        <!-- ── Summary Cards ── -->
        <div class="sl-stats-grid">
            <div class="sl-stat-card sl-stat-blue">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatTotal">0</span>
                    <div class="sl-stat-icon sl-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Total Participants</span>
            </div>
            <div class="sl-stat-card sl-stat-orange">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatPending">0</span>
                    <div class="sl-stat-icon sl-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Pending Confirmation</span>
            </div>
            <div class="sl-stat-card sl-stat-green">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatPaid">0</span>
                    <div class="sl-stat-icon sl-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Confirmed</span>
            </div>
            <div class="sl-stat-card sl-stat-red">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatCancelled">0</span>
                    <div class="sl-stat-icon sl-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Cancelled</span>
            </div>
        </div>

        <div class="schol-page-toolbar">
            <div class="sl-filter-group">
                <div class="sl-filter-wrapper">
                    <select id="slYearFilter" class="sl-filter-select">
                        <option value="">All Years</option>
                        <option value="2026">Sports Program 2026</option>
                        <option value="2025">Sports Program 2025</option>
                        <option value="2024">Sports Program 2024</option>
                        <option value="2023">Sports Program 2023</option>
                    </select>
                </div>
                <div class="sl-search-wrapper">
                    <svg class="sl-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="slSearchInput" class="sl-search-input" placeholder="Search participants...">
                </div>
            </div>
            <button type="button" id="slExportCsvBtn" class="sl-btn sl-btn-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export to CSV
            </button>
        </div>

        <!-- ── Scholar Table ── -->
        <div class="sl-table-card">
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead id="slTableHead">
                        <tr>
                            <th>FULL NAME
                                <div class="sl-col-hint">Last, First, Middle</div>
                            </th>
                            <th>SPORTS TYPE
                                <div class="sl-col-hint">Basketball, Volleyball, etc.</div>
                            </th>
                            <th>AGE</th>
                            <th>REQUIREMENTS
                                <div class="sl-col-hint">Submitted Documents</div>
                            </th>
                            <th>DATE APPROVED</th>
                            <th>STATUS</th>
                            <th class="col-actions">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="slTableBody"></tbody>
                </table>
            </div>
            
            <!-- ── Pagination ── -->
            <div class="sl-pagination-wrapper">
                <div class="sl-pagination-info">
                    Showing <span id="slShowingStart">0</span> to <span id="slShowingEnd">0</span> of <span id="slTotalRecords">0</span> participants
                </div>
                <div class="sl-pagination-controls">
                    <button type="button" class="sl-page-btn" id="slFirstPage" title="First Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"/>
                            <polyline points="18 17 13 12 18 7"/>
                        </svg>
                    </button>
                    <button type="button" class="sl-page-btn" id="slPrevPage" title="Previous Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                    <div class="sl-page-numbers" id="slPageNumbers"></div>
                    <button type="button" class="sl-page-btn" id="slNextPage" title="Next Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    <button type="button" class="sl-page-btn" id="slLastPage" title="Last Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"/>
                            <polyline points="6 17 11 12 6 7"/>
                        </svg>
                    </button>
                </div>
                <div class="sl-per-page">
                    <label for="slPerPage">Per page:</label>
                    <select id="slPerPage" class="sl-per-page-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- ── Scholar View Modal ── -->
<div class="sl-modal-overlay" id="slViewModal" style="display:none;">
    <div class="sl-modal-box" id="slViewBox">
        <div class="sl-modal-header">
            <h3>Application Details — PDF View</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sl-modal-close" id="slViewMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body" id="slViewBody"></div>
    </div>
</div>

<!-- ── Edit Status Modal ── -->
<div class="sl-modal-overlay" id="slEditModal" style="display:none;">
    <div class="sl-modal-box" style="max-width:500px;">
        <div class="sl-modal-header">
            <h3>Edit Participant Status</h3>
            <button type="button" class="sl-modal-close" id="slEditClose">&times;</button>
        </div>
        <div class="sl-modal-body" style="padding:24px;">
            <input type="hidden" id="editScholarIndex">
            
            <div class="sl-edit-field">
                <label for="editScholarName" class="sl-edit-label">Participant Name</label>
                <input type="text" id="editScholarName" class="sl-edit-input" readonly style="background:#f3f4f6;cursor:not-allowed;">
            </div>

            <div class="sl-edit-field">
                <label for="editStatus" class="sl-edit-label">Status <span style="color:#ef4444;">*</span></label>
                <select id="editStatus" class="sl-edit-input" required>
                    <option value="">— Select Status —</option>
                    <option value="Pending Confirmation">Pending Confirmation</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div class="sl-edit-field" id="cancelReasonField" style="display:none;">
                <label for="editCancelReason" class="sl-edit-label">Cancellation Reason <span style="color:#ef4444;">*</span></label>
                <select id="editCancelReason" class="sl-edit-input">
                    <option value="">— Select Reason —</option>
                    <option value="Incomplete requirements">Incomplete requirements</option>
                    <option value="Failed to meet criteria">Failed to meet criteria</option>
                    <option value="Duplicate application">Duplicate application</option>
                    <option value="Student request">Student request</option>
                    <option value="Budget constraints">Budget constraints</option>
                    <option value="Other">Other (specify below)</option>
                </select>
            </div>

            <div class="sl-edit-field" id="otherReasonField" style="display:none;">
                <label for="editOtherReason" class="sl-edit-label">Specify Other Reason <span style="color:#ef4444;">*</span></label>
                <textarea id="editOtherReason" class="sl-edit-textarea" rows="3" placeholder="Please specify the cancellation reason..."></textarea>
            </div>

            <div class="sl-edit-actions">
                <button type="button" class="sl-edit-btn sl-edit-btn-cancel" id="btnCancelEdit">Cancel</button>
                <button type="button" class="sl-edit-btn sl-edit-btn-save" id="btnSaveEdit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_list.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
