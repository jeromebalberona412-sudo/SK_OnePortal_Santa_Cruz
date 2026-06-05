<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar List - SK Officials Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/scholarship/approved-scholars.css',
        'app/Modules/Dashboard/assets/css/dashboard.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="sl-page-container schol-page-container">

        @include('Program_Management::scholarship.partials.page-top', [
            'activeTab' => 'list',
            'pageTitle' => 'Approved Scholars',
            'pageSubtitle' => 'Manage approved scholars and track scholarship payment release status.',
        ])

        <!-- ── Summary Cards (dashboard style) ── -->
        <div class="sl-stats-grid dash-stats-row">
            <div class="stat-card stat-card-blue sl-dash-stat">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="slStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Scholars</span>
            </div>
            <div class="stat-card stat-card-orange sl-dash-stat">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="slStatPending">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Pending Release</span>
            </div>
            <div class="stat-card stat-card-green sl-dash-stat">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="slStatPaid">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Claimed</span>
            </div>
            <div class="stat-card stat-card-red sl-dash-stat">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="slStatUnclaimed">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Unclaimed</span>
            </div>
        </div>

        <div class="sl-toolbar-row">
            <div class="sl-toolbar-left">
                <div class="sl-payment-filter-tabs" role="tablist" aria-label="Payment status filter">
                    <button type="button" class="sl-payment-tab active" data-payment-filter="all">All</button>
                    <button type="button" class="sl-payment-tab" data-payment-filter="Pending Release">Pending Release</button>
                    <button type="button" class="sl-payment-tab" data-payment-filter="Claimed">Claimed</button>
                    <button type="button" class="sl-payment-tab" data-payment-filter="Unclaimed">Unclaimed</button>
                </div>
                <div class="sl-filter-group sl-filter-group-inline">
                    <div class="sl-filter-wrapper">
                        <select id="slYearFilter" class="sl-filter-select">
                            <option value="">All Years</option>
                            <option value="2026">Scholarship 2026</option>
                            <option value="2025">Scholarship 2025</option>
                            <option value="2024">Scholarship 2024</option>
                            <option value="2023">Scholarship 2023</option>
                        </select>
                    </div>
                    <div class="sl-search-wrapper">
                        <svg class="sl-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" id="slSearchInput" class="sl-search-input" placeholder="Search scholars...">
                    </div>
                </div>
            </div>
            <button type="button" id="slExportCsvBtn" class="sl-btn sl-btn-export">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </button>
        </div>

        <!-- ── Scholar Table ── -->
        <div class="sl-table-card">
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead id="slTableHead">
                        <tr>
                            <th>FULL NAME
                                <div class="sl-col-hint">LN, FN, MN, Suffix</div>
                            </th>
                            <th>School</th>
                            <th>Year / Level</th>
                            <th>Program / Strand</th>
                            <th>Purpose</th>
                            <th>Date Approved</th>
                            <th>Payment Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slTableBody"></tbody>
                </table>
            </div>
            
            <!-- ── Pagination ── -->
            <div class="sl-pagination-wrapper">
                <div class="sl-pagination-info">
                    Showing <span id="slShowingStart">0</span> to <span id="slShowingEnd">0</span> of <span id="slTotalRecords">0</span> scholars
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
    <div class="sl-modal-box" id="slViewBox" style="max-width:800px;">
        <div class="sl-modal-header">
            <h3>Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sl-modal-close" id="slViewMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body" id="slViewBody" style="background:#f9fafb;"></div>
    </div>
</div>

<!-- ── Edit Status Modal ── -->
<div class="sl-modal-overlay" id="slEditModal" style="display:none;">
    <div class="sl-modal-box" style="max-width:500px;">
        <div class="sl-modal-header">
            <h3>Edit Payment Status</h3>
            <button type="button" class="sl-modal-close" id="slEditClose">&times;</button>
        </div>
        <div class="sl-modal-body" style="padding:24px;">
            <input type="hidden" id="editScholarIndex">
            
            <div class="sl-edit-field">
                <label for="editScholarName" class="sl-edit-label">Scholar Name</label>
                <input type="text" id="editScholarName" class="sl-edit-input" readonly style="background:#f3f4f6;cursor:not-allowed;">
            </div>

            <div class="sl-edit-field">
                <label for="editPaymentStatus" class="sl-edit-label">Payment Status <span style="color:#ef4444;">*</span></label>
                <select id="editPaymentStatus" class="sl-edit-input" required>
                    <option value="Pending Release">Pending Release</option>
                    <option value="Claimed">Claimed</option>
                    <option value="Unclaimed">Unclaimed</option>
                </select>
                <p class="sl-edit-hint">Update when the scholar has received or has not yet claimed the scholarship assistance.</p>
            </div>

            <div class="sl-edit-actions">
                <button type="button" class="sl-edit-btn sl-edit-btn-cancel" id="btnCancelEdit">Cancel</button>
                <button type="button" class="sl-edit-btn sl-edit-btn-save" id="btnSaveEdit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Revoke Approval Modal ── -->
<div class="sl-modal-overlay" id="slRevokeModal" style="display:none;">
    <div class="sl-modal-box" style="max-width:550px;" id="slRevokeBox">
        <div class="sl-modal-header sl-modal-header-danger">
            <h3>Revoke Scholar Approval</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sl-modal-close" id="slRevokeMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sl-modal-close" id="slRevokeClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body" style="padding:24px;">
            <input type="hidden" id="revokeScholarIndex">
            
            <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:16px;">You are about to revoke the approval of this scholarship beneficiary. Please provide a reason for revoking the approval. This action will move the record from Approved Scholars to Rejected Scholars.</p>
            
            <div class="sl-edit-field">
                <label for="revokeScholarName" class="sl-edit-label">Scholar Name</label>
                <input type="text" id="revokeScholarName" class="sl-edit-input" readonly style="background:#f3f4f6;cursor:not-allowed;">
            </div>

            <div class="sl-edit-field" style="margin-top:16px;">
                <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Select Revocation Reason:</label>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" value="Your application has been rejected due to an approval error during the review process.">
                        <span>Approval error during review process</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" value="Your application has been rejected because it was mistakenly approved during evaluation.">
                        <span>Mistakenly approved during evaluation</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" value="Your application has been rejected because the submitted requirements did not meet the program requirements upon further review.">
                        <span>Requirements did not meet program standards</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" value="Your application has been rejected because additional verification revealed incomplete eligibility requirements.">
                        <span>Incomplete eligibility requirements</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" value="We apologize for the inconvenience. Your application was mistakenly approved and has been returned to the rejected applications list for proper evaluation.">
                        <span>Mistakenly approved - returned for evaluation</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="checkbox" class="sl-revoke-checkbox" id="slRevokeOtherCheckbox" value="other">
                        <span>Other (specify below)</span>
                    </label>
                </div>
            </div>

            <div class="sl-edit-field" id="slRevokeReasonField" style="margin-top:16px;display:none;">
                <label for="revokeReason" class="sl-edit-label">Revocation Reason <span style="color:#ef4444;">*</span></label>
                <textarea id="revokeReason" class="sl-edit-input" rows="4" placeholder="Enter the reason for revoking approval..." maxlength="500" style="resize:none;"></textarea>
                <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="revokeReasonCount">0</span>/500 characters</div>
                <p class="sl-edit-hint">This reason will be stored and displayed in the Rejected Scholars details page.</p>
            </div>

            <div class="sl-edit-actions">
                <button type="button" class="sl-edit-btn sl-edit-btn-cancel" id="btnCancelRevoke" style="background-color:#9ca3af;color:#fff;border:none;">Cancel</button>
                <button type="button" class="sl-edit-btn" id="btnConfirmRevoke" style="background-color:#ef4444;color:#fff;border:none;">Revoke</button>
            </div>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Program_Management/assets/js/scholarship/approved-scholars.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
