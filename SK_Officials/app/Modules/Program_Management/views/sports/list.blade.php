<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Approved Participants - SK Officials Portal</title>
    @include('layout::favicon')
    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
        'app/Modules/Program_Management/assets/css/sports/sports_list.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
</head>
<body data-program-key="sports">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap has-table-page-footer">

    @include('Program_Management::partials.program-page-top', [
        'activeTab'          => 'list',
        'pageTitle'          => 'Approved Participants',
        'pageSubtitle'       => 'List of approved sports program participants.',
        'programType'        => 'sports',
        'programTitle'       => 'Sports Development',
        'programDescription' => 'Manage sports development programs, track athletic activities, and evaluate youth participation.',
    ])

    <!-- ── Summary Cards ── -->
    <div class="spl-stats-grid">
        <div class="spl-stat-card spl-stat-blue">
            <div class="spl-stat-top">
                <span class="spl-stat-value" id="slStatTotal">0</span>
                <div class="spl-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
            <span class="spl-stat-label">Total Participants</span>
        </div>
        <div class="spl-stat-card spl-stat-green">
            <div class="spl-stat-top">
                <span class="spl-stat-value" id="slStatPaid">0</span>
                <div class="spl-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>
            <span class="spl-stat-label">Paid</span>
        </div>
        <div class="spl-stat-card spl-stat-orange">
            <div class="spl-stat-top">
                <span class="spl-stat-value" id="slStatPending">0</span>
                <div class="spl-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            <span class="spl-stat-label">Unpaid</span>
        </div>
        <div class="spl-stat-card" style="border-color:#6366f1;">
            <div class="spl-stat-top">
                <span class="spl-stat-value" id="slStatSports">0</span>
                <div class="spl-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                        <path d="M2 12h20"/>
                    </svg>
                </div>
            </div>
            <span class="spl-stat-label">Sports Types</span>
        </div>
    </div>

    <!-- ── Filters ── -->
    <div class="schol-filters-row saf-sports-filters" style="margin-bottom:16px;">
        <select id="slDateFilter" class="schol-filter-input" style="min-width:150px;">
            <option value="all">All Applications</option>
            <option value="recent">Recent (Last 7 Days)</option>
            <option value="monthly">This Month</option>
            <option value="yearly">This Year</option>
        </select>
        <select id="slSportFilter" class="schol-filter-input" style="min-width:150px;">
            <option value="all">All Sports</option>
            <option value="basketball">Basketball</option>
            <option value="volleyball">Volleyball</option>
            <option value="other">Other</option>
        </select>
        <select id="slPaymentFilter" class="schol-filter-input" style="min-width:150px;">
            <option value="">All Payment Status</option>
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
        </select>
        <div class="schol-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="slTeamSearch" class="schol-search-input" placeholder="Filter by team name...">
        </div>
        <div class="schol-search-wrap" style="flex:1;min-width:200px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="slSearchInput" class="schol-search-input" placeholder="Search by name or program...">
        </div>
        <button type="button" id="slExportCsvBtn" class="spl-btn spl-btn-green" style="flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
        </button>
    </div>

    <!-- ── Participants Table ── -->
    <div class="spl-table-card spl-approved-table-card">
        <div class="spl-table-wrapper">
            <table class="spl-table spl-approved-table">
                <thead>
                    <tr>
                        <th style="text-align:center;">
                            FULL NAME
                            <div class="spl-col-hint">Last, First, Middle</div>
                        </th>
                        <th>SPORT</th>
                        <th>TEAM</th>
                        <th>PROGRAM</th>
                        <th>AGE</th>
                        <th>CONTACT</th>
                        <th>DATE APPROVED</th>
                        <th>PAYMENT</th>
                        <th>STATUS</th>
                        <th class="col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="slTableBody">
                    <tr>
                        <td colspan="10" class="spl-empty">Loading participants...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="sl-page-footer table-page-footer pagination-footer" aria-label="Table pagination">
        <div class="pagination-footer-nav">
            <button type="button" class="pagination-arrow" id="slPrevBtn" disabled aria-label="Previous page">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <span class="pagination-page-label">Page</span>
            <input type="number" class="pagination-page-input" id="slPageInput" value="1" min="1" aria-label="Current page">
            <span class="pagination-page-of">of <span id="slTotalPages">1</span></span>
            <button type="button" class="pagination-arrow" id="slNextBtn" disabled aria-label="Next page">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
        <div class="pagination-footer-right">
            <select id="slRowsPerPageSelect" class="pagination-rows-select" aria-label="Rows per page">
                <option value="10">10 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
            <span class="pagination-record-count" id="slPaginationInfo">0 records</span>
        </div>
    </div>

</div>
</main>

<!-- ── View Modal ── -->
<div class="spl-modal-overlay" id="slViewModal" style="display:none;">
    <div class="spl-modal-box" id="slViewBox" style="max-width:700px;">
        <div class="spl-modal-header">
            <h3>Participant Details</h3>
            <div style="display:flex;align-items:center;gap:4px;">
                <button type="button" class="spl-modal-close" id="slViewMaximize" title="Maximize" style="font-size:15px;padding:2px 8px;">□</button>
                <button type="button" class="spl-modal-close" id="slViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="spl-modal-body" id="slViewBody"></div>
    </div>
</div>

<!-- ── Payments Modal ── -->
<div class="spl-modal-overlay" id="slEditModal" style="display:none;">
    <div class="spl-modal-box" id="slEditBox" style="max-width:480px;">
        <div class="spl-modal-header spl-modal-header-payments">
            <h3>Payments</h3>
            <div style="display:flex;align-items:center;gap:4px;">
                <button type="button" class="spl-modal-close" id="slEditMaximize" title="Maximize" style="font-size:15px;padding:2px 8px;">□</button>
                <button type="button" class="spl-modal-close" id="slEditClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="spl-modal-body" style="padding:24px;background:#fff;">
            <input type="hidden" id="slEditParticipantId">
            <p id="slEditParticipantName" style="font-size:14px;font-weight:600;color:#111827;margin-bottom:16px;"></p>
            <div id="slEditTeamInfo" style="display:none;margin-bottom:16px;padding:12px 14px;background:#fefce8;border:1px solid #fde047;border-radius:8px;">
                <p style="font-size:13px;color:#854d0e;margin:0;">
                    Team: <strong id="slEditTeamName"></strong>
                    (<span id="slEditTeamCount">0</span> members)
                </p>
                <p style="font-size:12px;color:#a16207;margin-top:8px;margin-bottom:0;">
                    Payment status will automatically apply to all participants with the same team name.
                </p>
            </div>
            <label for="slEditPaymentStatus" style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Payment Status</label>
            <select id="slEditPaymentStatus" class="spl-filter-input" style="width:100%;max-width:none;">
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
            </select>
            <p style="font-size:12px;color:#6b7280;margin-top:8px;">Update whether the participant has already paid or is still unpaid.</p>
        </div>
        <div class="spl-modal-footer spl-payments-modal-footer">
            <button type="button" id="slEditCancel" class="spl-btn-cancel">Cancel</button>
            <button type="button" id="slEditSave" class="spl-btn-payments-save">Save Payment</button>
        </div>
    </div>
</div>

<!-- ── Revoke Confirmation Modal ── -->
<div class="spl-modal-overlay" id="slRevokeModal" style="display:none;">
    <div class="spl-modal-box sk-type-confirm-modal" style="max-width:420px;padding:0;overflow:hidden;">
        <div class="sk-type-confirm-header">
            <h3>Revoke Participant</h3>
        </div>
        <div class="sk-type-confirm-body">
            <p class="sk-type-confirm-message">
                Are you sure you want to revoke approval for <strong id="slRevokeName"></strong>?
            </p>
            <p class="sk-type-confirm-desc">
                This will return the application to Sports Program Requests as pending. This action cannot be undone.
            </p>
            <label class="sk-type-confirm-label" for="slRevokeConfirmText">Confirmation Required</label>
            <input type="text" id="slRevokeConfirmText" class="sk-type-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
            <p class="sk-type-confirm-hint sk-type-confirm-hint-error" id="slRevokeConfirmError" style="display:none;"></p>
        </div>
        <div class="sk-type-confirm-footer">
            <button type="button" id="slRevokeCancel" class="sk-btn-cancel-confirm">Cancel</button>
            <button type="button" id="slRevokeConfirm" class="sk-btn-action-confirm is-disabled" disabled>Confirm Revoke</button>
        </div>
    </div>
</div>

<!-- ── Toast (top-center) ── -->
<div class="schol-toast" id="slToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="slToastMsg"></span>
</div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Layout/js/table-row-actions-menu.js',
    'app/Modules/Program_Management/assets/js/sports/sports_list.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
