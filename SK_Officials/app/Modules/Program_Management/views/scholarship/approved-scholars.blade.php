<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        <div class="schol-toolbar-pro schol-toolbar-pro--scholars">
            <div class="sl-payment-filter-tabs" role="tablist" aria-label="Payment status filter">
                <button type="button" class="sl-payment-tab active" data-payment-filter="all">All</button>
                <button type="button" class="sl-payment-tab" data-payment-filter="Claimed">Claimed</button>
                <button type="button" class="sl-payment-tab" data-payment-filter="Unclaimed">Unclaimed</button>
            </div>
            <div class="schol-toolbar-pro-actions">
                <select id="slYearFilter" class="schol-filter-input schol-filter-input--compact" aria-label="Scholarship year">
                    <option value="">All Years</option>
                    <option value="2026">Scholarship 2026</option>
                    <option value="2025" selected>Scholarship 2025</option>
                    <option value="2024">Scholarship 2024</option>
                    <option value="2023">Scholarship 2023</option>
                </select>
                <div class="schol-search-wrap schol-search-wrap--pro">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="search" id="slSearchInput" class="schol-search-input" placeholder="Search scholars..." aria-label="Search scholars">
                </div>
                <button type="button" id="slExportCsvBtn" class="schol-btn schol-btn-save schol-btn-export">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>

        <!-- ── Scholar Table ── -->
        <div class="sl-table-card">
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead id="slTableHead">
                        <tr>
                            <th class="schol-col-name">FULL NAME
                                <div class="schol-col-hint">LN, FN, MN, Suffix</div>
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
            </div>
        </div>

    </div>
</main>

<!-- ── Scholar View Modal ── -->
<div class="sl-modal-overlay" id="slViewModal" style="display:none;">
    <div class="sl-modal-box sl-modal-box--view" id="slViewBox">
        <div class="sl-modal-header">
            <h3>Application Details</h3>
            <div class="sl-modal-header-actions">
                <button type="button" class="sl-modal-close sl-modal-maximize-btn" id="slViewMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body" id="slViewBody"></div>
    </div>
</div>

<!-- ── Edit Status Modal ── -->
<div class="sl-modal-overlay" id="slEditModal" style="display:none;">
    <div class="sl-modal-box sl-modal-box--edit" id="slEditBox">
        <div class="sl-modal-header">
            <h3>Edit Payment Status</h3>
            <div class="sl-modal-header-actions">
                <button type="button" class="sl-modal-close sl-modal-maximize-btn" id="slEditMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slEditClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body sl-modal-body--form">
            <input type="hidden" id="editScholarIndex">
            <div id="slEditSummary"></div>

            <div class="sl-edit-field">
                <label for="editPaymentStatus" class="sl-edit-label">Payment Status <span style="color:#ef4444;">*</span></label>
                <select id="editPaymentStatus" class="sl-edit-input" required>
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
    <div class="sl-modal-box sl-modal-box--revoke" id="slRevokeBox">
        <div class="sl-modal-header sl-modal-header-danger">
            <h3>Revoke Scholar Approval</h3>
            <div class="sl-modal-header-actions">
                <button type="button" class="sl-modal-close sl-modal-maximize-btn" id="slRevokeMaximize" title="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="slRevokeClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body sl-modal-body--form">
            <input type="hidden" id="revokeScholarIndex">
            <div id="slRevokeSummary"></div>

            <p class="sl-revoke-notice">You are about to revoke the approval of this scholarship beneficiary. Please provide a reason for revoking the approval. This action will move the record from Approved Scholars to Rejected Scholars.</p>

            <div class="sl-edit-field">
                <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Select Revocation Reason:</label>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="radio" name="revokeReason" value="Mistakenly Approved" checked>
                        <span>Mistakenly Approved</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;">
                        <input type="radio" name="revokeReason" value="other" id="slRevokeOtherRadio">
                        <span>Other Reason</span>
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
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-view-shared.js',
    'app/Modules/Program_Management/assets/js/scholarship/approved-scholars.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
