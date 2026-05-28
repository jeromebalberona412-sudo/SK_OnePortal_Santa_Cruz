<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Application Requests - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sk-report-editor.css',
        'app/Modules/schedule_programs/assets/css/scholar_application_from.css',
        'app/Modules/schedule_programs/assets/css/scholar_report.css',
        'app/Modules/schedule_programs/assets/css/sports_requests.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-sports-page="requests">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="sports-page-container schol-page-container">

    @include('schedule_programs::partials.sports-page-top', [
        'activeTab' => 'requests',
        'pageTitle' => 'Sports Application Requests',
        'pageSubtitle' => 'Manage sports development programs and review submitted applications from Kabataan members.',
    ])

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
        <select id="filterSport" class="sports-filter-input sports-filter-wide">
            <option value="">All Sports</option>
            <option value="Basketball">Basketball</option>
            <option value="Volleyball">Volleyball</option>
            <option value="Other">Other</option>
        </select>
        <select id="filterDivision" class="sports-filter-input sports-filter-wide">
            <option value="">All Age Categories</option>
            <option value="Youth Beginner (15-17)">Youth Beginner — 15–17</option>
            <option value="Youth Competitive (18-21)">Youth Competitive — 18–21</option>
            <option value="Young Adult (22-25)">Young Adult — 22–25</option>
            <option value="Adult Competitive (26-28)">Adult Competitive — 26–28</option>
            <option value="Senior Youth (29-30)">Senior Youth / Open — 29–30</option>
        </select>
        <div class="sports-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="sportsSearch" class="sports-search-input" placeholder="Search by name or sport...">
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
                    </tr>                </thead>
                <tbody id="sportsTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
</main>

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
            <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:14px;">Select the reason(s) for rejecting this application. Selecting <strong>Other</strong> will clear all other selections.</p>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Incomplete Requirements" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Incomplete Requirements</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Invalid Documents</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Does Not Meet Age Criteria" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Does Not Meet Age Criteria</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Duplicate Application" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Duplicate Application</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Late Submission" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Late Submission</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #ef4444;background:#fff5f5;transition:border-color 0.15s;">
                    <input type="checkbox" id="rejectReasonOtherCheckbox" class="reject-reason-checkbox" value="Other" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span style="font-weight:600;color:#b91c1c;">Other (specify below)</span>
                </label>
            </div>

            <div id="rejectReasonOtherField" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.04em;">Specify Reason <span style="color:#ef4444;">*</span></label>
                    <span id="rejectOtherCharCount" style="font-size:11px;color:#9ca3af;font-weight:600;">0 / 500</span>
                </div>
                <textarea id="rejectReasonOtherText" class="sports-input" maxlength="500" placeholder="Enter reason for rejection..." style="width:100%;resize:none;height:90px;min-height:90px;max-height:90px;overflow-y:auto;"></textarea>
            </div>

            <!-- Inline validation error -->
            <div id="rejectInlineError" style="display:none;margin-top:10px;padding:9px 12px;background:#fee2e2;border:1.5px solid #fca5a5;border-radius:8px;font-size:12px;font-weight:600;color:#b91c1c;">
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
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
