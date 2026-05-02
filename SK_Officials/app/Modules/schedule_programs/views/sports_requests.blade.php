<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Application Requests - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/sports_requests.css'
    ])
</head>
<body>
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-page-container">

    <!-- Page Header -->
    <section class="sports-page-header">
        <div class="sports-page-header-left">
            <h1 class="sports-page-title">Sports Application Requests</h1>
            <p class="sports-page-subtitle">Manage sports development programs and review submitted applications from Kabataan members.</p>
        </div>
        <div class="sports-header-actions">
            <button type="button" class="sports-btn sports-btn-primary" id="btnCreateProgram">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Sports Program
            </button>
        </div>
    </section>

    <!-- Stat Cards -->
    <div class="sports-stats-grid">
        <div class="sports-stat-card sports-stat-blue">
            <div class="sports-stat-top">
                <span class="sports-stat-value" id="statTotal">0</span>
                <div class="sports-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                </div>
            </div>
            <span class="sports-stat-label">Total Applications</span>
        </div>
        <div class="sports-stat-card sports-stat-yellow">
            <div class="sports-stat-top">
                <span class="sports-stat-value" id="statPending">0</span>
                <div class="sports-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="sports-stat-label">Pending</span>
        </div>
        <div class="sports-stat-card sports-stat-green">
            <div class="sports-stat-top">
                <span class="sports-stat-value" id="statApproved">0</span>
                <div class="sports-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <span class="sports-stat-label">Approved</span>
        </div>
        <div class="sports-stat-card sports-stat-red">
            <div class="sports-stat-top">
                <span class="sports-stat-value" id="statRejected">0</span>
                <div class="sports-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </div>
            <span class="sports-stat-label">Rejected</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="sports-filters-row">
        <select id="filterSport" class="sports-filter-input">
            <option value="">All Sports</option>
            <option value="Basketball">Basketball</option>
            <option value="Volleyball">Volleyball</option>
            <option value="Other">Other</option>
        </select>
        <select id="filterDivision" class="sports-filter-input">
            <option value="">All Divisions</option>
            <option value="Cadet Division (15-17)">Cadet Division</option>
            <option value="Youth Division (18-21)">Youth Division</option>
            <option value="Young Adult (22-25)">Young Adult</option>
            <option value="Senior Division (26-30)">Senior Division</option>
        </select>
        <select id="filterStatus" class="sports-filter-input">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
        </select>
        <div class="sports-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="sportsSearch" class="sports-search-input" placeholder="Search by name, sport, or division...">
        </div>
    </div>

    <!-- Applications Table -->
    <div class="sports-table-card">
        <div class="sports-table-wrap">
            <table class="sports-table">
                <thead>
                    <tr>
                        <th>FULL NAME<div style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                        <th>Sport</th>
                        <th>Division</th>
                        <th>Contact</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="sportsTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- ══════════════════════════════════════════════════════════════
     Create Sports Program Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="createProgramModal" style="display:none;">
    <div class="sports-modal-box sports-modal-form-builder">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Sports Program
            </h3>
            <button type="button" class="sports-modal-close" id="createProgramClose">&times;</button>
        </div>

        <div class="sports-modal-body spfb-modal-body">

            <!-- ── Section 1: Program Basic Fields ── -->
            <div class="spfb-section-card spfb-section-basic">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    Program Details
                </div>

                <div class="sports-field" style="margin-bottom:14px;">
                    <label for="programName">Program Name <span class="sports-req">*</span></label>
                    <input type="text" id="programName" class="sports-input" placeholder="e.g., Basketball Tournament 2026" maxlength="150">
                </div>

                <div class="sports-field-row" style="margin-bottom:14px;">
                    <div class="sports-field">
                        <label for="startDate">Start Date <span class="sports-req">*</span></label>
                        <div class="spfb-date-wrap">
                            <input type="text" id="startDate" class="sports-input spfb-date-input" placeholder="mm/dd/yyyy" maxlength="10" autocomplete="off">
                            <svg class="spfb-date-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                        </div>
                    </div>
                    <div class="sports-field">
                        <label for="endDate">End Date <span class="sports-req">*</span></label>
                        <div class="spfb-date-wrap">
                            <input type="text" id="endDate" class="sports-input spfb-date-input" placeholder="mm/dd/yyyy" maxlength="10" autocomplete="off">
                            <svg class="spfb-date-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                        </div>
                    </div>
                </div>

                <div class="sports-field-row">
                    <div class="sports-field">
                        <label for="startTime">Start Time <span class="sports-req">*</span></label>
                        <div class="spfb-time-wrap">
                            <input type="text" id="startTime" class="sports-input spfb-time-input" placeholder="e.g., 08:00 AM" maxlength="8" autocomplete="off">
                            <svg class="spfb-time-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                    </div>
                    <div class="sports-field">
                        <label for="endTime">End Time <span class="sports-req">*</span></label>
                        <div class="spfb-time-wrap">
                            <input type="text" id="endTime" class="sports-input spfb-time-input" placeholder="e.g., 05:00 PM" maxlength="8" autocomplete="off">
                            <svg class="spfb-time-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Section 2: Question Builder ── -->
            <div class="spfb-section-card spfb-section-builder">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Application Form Builder
                    <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
                </div>
                <p class="spfb-builder-hint">Build the application form that Kabataan members will fill out when applying for this program.</p>

                <!-- Question List -->
                <div id="spfbQuestionList" class="spfb-question-list">
                    <div class="spfb-empty-state" id="spfbEmptyState">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <p>No questions yet. Click <strong>Add Question</strong> to start building your form.</p>
                    </div>
                </div>

                <!-- Add Question Button -->
                <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Question
                </button>
            </div>

        </div><!-- /.spfb-modal-body -->

        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="programCancelBtn">Cancel</button>
            <button type="button" class="sports-btn sports-btn-primary" id="programSaveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Create Program
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     View Application Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="viewModal" style="display:none;">
    <div class="sports-modal-box sports-modal-xl" id="viewBox">
        <div class="sports-modal-header">
            <h3>Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="viewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="viewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sports-modal-body" id="viewModalBody" style="background:#f0f1f5;">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-danger" id="btnReject">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
            <button type="button" class="sports-btn sports-btn-success" id="btnApprove">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     Rejection Reason Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="rejectReasonModal" style="display:none;">
    <div class="sports-modal-box sports-modal-md">
        <div class="sports-modal-header sports-modal-header-danger">
            <h3>Rejection Reason</h3>
            <button type="button" class="sports-modal-close" id="rejectReasonClose">&times;</button>
        </div>
        <div class="sports-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:16px;">Please select the reason(s) for rejecting this application:</p>
            
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Incomplete Requirements" style="cursor:pointer;width:16px;height:16px;">
                    <span>Incomplete Requirements</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents" style="cursor:pointer;width:16px;height:16px;">
                    <span>Invalid Documents</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Does Not Meet Age Criteria" style="cursor:pointer;width:16px;height:16px;">
                    <span>Does Not Meet Age Criteria</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Duplicate Application" style="cursor:pointer;width:16px;height:16px;">
                    <span>Duplicate Application</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Late Submission" style="cursor:pointer;width:16px;height:16px;">
                    <span>Late Submission</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" id="rejectReasonOtherCheckbox" value="Other" style="cursor:pointer;width:16px;height:16px;">
                    <span>Other</span>
                </label>
            </div>

            <div id="rejectReasonOtherField" style="display:none;">
                <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block;">Please specify:</label>
                <textarea id="rejectReasonOtherText" class="sports-input" rows="3" placeholder="Enter other reason..." style="width:100%;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="rejectReasonCancel">Cancel</button>
            <button type="button" class="sports-btn sports-btn-danger" id="rejectReasonConfirm">Confirm Rejection</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="sports-toast" id="sportsToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="sportsToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_requests.js'
])
</body>
</html>
