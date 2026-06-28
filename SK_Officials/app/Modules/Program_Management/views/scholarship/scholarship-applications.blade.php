<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholar Application - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
</head>
<body class="has-table-page-footer">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container">

    @include('Program_Management::scholarship.partials.page-top', [
        'activeTab' => 'requests',
        'pageTitle' => 'Scholarship Applications',
        'pageSubtitle' => 'Manage scholarship programs and review submitted applications from Kabataan members.',
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

    <!-- Search toolbar -->
    <div class="schol-toolbar-pro">
        <div class="schol-search-wrap schol-search-wrap--pro">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="scholSearch" class="schol-search-input" placeholder="Search by name or school..." aria-label="Search applications">
        </div>
        <select id="scholFilter" class="schol-filter-input schol-filter-input--compact" aria-label="Filter applications">
            <option value="all">All Applications</option>
            <option value="recent">Recent (Last 7 Days)</option>
            <option value="monthly">This Month</option>
            <option value="yearly">This Year</option>
        </select>
    </div>

    <!-- Applications Table -->
    <div class="schol-table-card">
        <div class="schol-table-wrap">
            <table class="schol-table">
                <thead>
                    <tr>
                        <th class="schol-col-name">FULL NAME<div class="schol-col-hint">LN, FN, MN, Suffix</div></th>
                        <th>School</th>
                        <th>Year / Level</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Time Submitted</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="scholTableBody"></tbody>
            </table>
        </div>
    </div>

    @include('Program_Management::scholarship.partials.table-pagination', ['prefix' => 'scholReq'])

</div>
</main>

<!-- ══════════════════════════════════════════════════════════════
     View Application Modal — PDF layout, no Close button
     ══════════════════════════════════════════════════════════════ -->
<div class="schol-modal-overlay" id="scholViewModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholViewBox">
        <div class="schol-modal-header">
            <h3>Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scholViewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="scholViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" id="scholViewBody" style="background:#f0f1f5;"></div>
    </div>
</div>

<!-- ── Rejection Reason Modal ── -->
<div class="schol-modal-overlay" id="scholRejectReasonModal" style="display:none;">
    <div class="schol-modal-box schol-modal-md sk-type-confirm-modal">
        <div class="sk-type-confirm-header">
            <h3>Reject Application</h3>
            <button type="button" class="schol-modal-close" id="scholRejectReasonClose">&times;</button>
        </div>
        <div class="sk-type-confirm-body">
            <p class="sk-type-confirm-message">Please select the reason(s) for rejecting this application:</p>

            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents" style="cursor:pointer;width:16px;height:16px;">
                    <span>Invalid Documents</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Incorrect Information Provided" style="cursor:pointer;width:16px;height:16px;">
                    <span>Incorrect Information Provided</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" id="rejectReasonOther" value="Other" style="cursor:pointer;width:16px;height:16px;">
                    <span>Other</span>
                </label>
                <textarea id="rejectReasonOtherInput" class="schol-input" placeholder="Please specify the reason (max 500 characters)" maxlength="500" rows="3" style="display:none;margin-top:8px;resize:none;"></textarea>
                <div style="font-size:11px;color:#6b7280;text-align:right;margin-top:4px;display:none;" id="rejectReasonOtherCount">0/500 characters</div>
            </div>

            <div class="sk-type-confirm-section">
                <label class="sk-type-confirm-label" for="scholRejectConfirmText">Confirmation Required</label>
                <input type="text" id="scholRejectConfirmText" class="sk-type-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
                <p class="sk-type-confirm-hint sk-type-confirm-hint-error" id="scholRejectConfirmError" style="display:none;"></p>
            </div>
        </div>
        <div class="sk-type-confirm-footer">
            <button type="button" class="sk-btn-cancel-confirm" id="scholRejectReasonCancel">Cancel</button>
            <button type="button" class="sk-btn-action-confirm is-disabled" id="scholRejectReasonConfirm" disabled>Reject</button>
        </div>
    </div>
</div>

<!-- ── Approve Confirmation Modal ── -->
<div class="schol-modal-overlay" id="scholApproveConfirmModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header">
            <h3>Approve Application</h3>
            <button type="button" class="schol-modal-close" id="scholApproveConfirmClose">&times;</button>
        </div>
        <div class="schol-modal-body" style="overflow:visible;">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">Are you sure you want to approve this application?</p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="scholApproveConfirmCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-approve" id="scholApproveConfirmBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ── -->
<div class="schol-modal-overlay" id="scholDeleteModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Delete Application</h3>
            <button type="button" class="schol-modal-close" id="scholDeleteClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;">Are you sure you want to delete this application? This action cannot be undone.</p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="scholDeleteCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="scholDeleteConfirm">Delete</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="scholarship-toast" id="scholarshipToast" style="display:none;" role="status" aria-live="polite">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="scholarshipToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/layout/js/table-row-actions-menu.js',
    'app/Modules/layout/js/table-page-footer.js',
    'app/Modules/Program_Management/assets/css/scholarship/scholarship-toast.css',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-toast.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-system-fields.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-view-shared.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-applications.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
