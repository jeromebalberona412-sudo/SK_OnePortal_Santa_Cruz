<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sports Program Requests - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholar_application_from.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-program-key="sports">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">

    @include('Program_Management::partials.program-page-top', [
        'activeTab' => 'requests',
        'pageTitle' => 'Sports Program Requests',
        'pageSubtitle' => 'Manage sports program requests and review submitted applications from Kabataan members.',
        'programType' => 'sports',
        'programTitle' => 'Sports Committee',
        'programDescription' => 'Manage sports development programs, track athletic activities, and evaluate youth participation.',
    ])



    <!-- Stat Cards -->
    <div class="schol-stats-grid">
        <div class="schol-stat-card schol-stat-blue">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statTotal">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Total Applicants</span>
        </div>
        <div class="schol-stat-card schol-stat-yellow">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statPending">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Pending</span>
        </div>
        <div class="schol-stat-card schol-stat-green">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statApproved">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Approved</span>
        </div>
        <div class="schol-stat-card schol-stat-red">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statRejected">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Rejected</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="schol-filters-row saf-sports-filters">
        <select id="scholFilter" class="schol-filter-input" style="min-width:150px;">
            <option value="all">All Applications</option>
            <option value="recent">Recent (Last 7 Days)</option>
            <option value="monthly">This Month</option>
            <option value="yearly">This Year</option>
        </select>
        <select id="sportTypeFilter" class="schol-filter-input" style="min-width:150px;">
            <option value="all">All Sports</option>
            <option value="basketball">Basketball</option>
            <option value="volleyball">Volleyball</option>
            <option value="other">Other</option>
        </select>
        <div class="schol-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="teamNameSearch" class="schol-search-input" placeholder="Filter by team name...">
        </div>
        <div class="schol-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="scholSearch" class="schol-search-input" placeholder="Search by name or program...">
        </div>
    </div>

    <!-- Applications Table -->
    <div class="schol-table-card">
        <div class="schol-table-wrap">
            <table class="schol-table">
                <thead>
                    <tr>
                        <th>NAME<div style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">Last, First, Middle</div></th>
                        <th>SPORT</th>
                        <th>TEAM</th>
                        <th>PROGRAM</th>
                        <th>AGE</th>
                        <th>DATE SUBMITTED</th>
                        <th>STATUS</th>
                        <th class="col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="sportsTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- ══════════════════════════════════════════════════════════════
     View Application Modal — Simple Participant Details
     ══════════════════════════════════════════════════════════════ -->
<div class="schol-modal-overlay" id="viewModal" style="display:none;">
    <div class="schol-modal-box schol-modal-lg" id="viewBox">
        <div class="schol-modal-header">
            <h3>Participant Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="viewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="viewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" id="viewModalBody" style="background:#f9fafb;padding:32px;"></div>
        <!-- Footer: Approve + Reject only -->
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-approve" id="btnApprove">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
            <button type="button" class="schol-btn schol-btn-reject" id="btnReject">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
        </div>
    </div>
</div>

<!-- ── Rejection Reason Modal ── -->
<div class="schol-modal-overlay" id="rejectReasonModal" style="display:none;">
    <div class="schol-modal-box sk-type-confirm-modal sports-reject-modal-box">
        <div class="sk-type-confirm-header">
            <h3>Reject Application</h3>
        </div>
        <div class="sk-type-confirm-body sports-reject-modal-body">
            <p class="sk-type-confirm-message">Please select a reason for rejecting this application:</p>

            <div class="sports-reject-reasons">
                <label class="sports-reject-reason-item">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents">
                    <span>Invalid Documents</span>
                </label>
                <label class="sports-reject-reason-item">
                    <input type="checkbox" id="rejectReasonOtherCheckbox" value="Other">
                    <span>Other</span>
                </label>
            </div>

            <div id="rejectReasonOtherField" class="sports-reject-other-field" style="display:none;">
                <label class="sk-type-confirm-label" for="rejectReasonOtherText">Please specify:</label>
                <textarea id="rejectReasonOtherText" class="schol-input" rows="3" placeholder="Enter other reason..." style="width:100%;resize:none;"></textarea>
            </div>

            <div class="sk-type-confirm-section">
                <label class="sk-type-confirm-label" for="rejectReasonConfirmText">Confirmation Required</label>
                <input type="text" id="rejectReasonConfirmText" class="sk-type-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
                <p class="sk-type-confirm-hint sk-type-confirm-hint-error" id="rejectReasonConfirmError" style="display:none;"></p>
            </div>
        </div>
        <div class="sk-type-confirm-footer">
            <button type="button" class="sk-btn-cancel-confirm" id="rejectReasonCancel">Cancel</button>
            <button type="button" class="sk-btn-action-confirm is-disabled" id="rejectReasonConfirm" disabled>Reject</button>
        </div>
    </div>
</div>

<!-- ── Approve Confirmation Modal ── -->
<div class="schol-modal-overlay" id="approveConfirmModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header">
            <h3>Approve Application</h3>
            <button type="button" class="schol-modal-close" id="approveConfirmClose">&times;</button>
        </div>
        <div class="schol-modal-body" style="overflow:visible;">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">
                Are you sure you want to approve this application?
            </p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="approveConfirmCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-approve" id="approveConfirmBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

<!-- Toast — top-center (matches scholarship / calendar style) -->
<div class="schol-toast" id="sportsToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="sportsToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/layout/js/table-row-actions-menu.js',
    'app/Modules/Program_Management/assets/js/sports/sports_requests.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
