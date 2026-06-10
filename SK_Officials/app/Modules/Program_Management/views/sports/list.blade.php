<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Approved Participants - SK Officials Portal</title>
    @include('layout::favicon')
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
        'app/Modules/Program_Management/assets/css/sports/sports_list.css'
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

    <!-- ── Toolbar ── -->
    <div class="schol-page-toolbar" style="margin-bottom:16px;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;flex:1;">
            <div class="spl-search-wrap" style="flex:1;min-width:200px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="slSearchInput" class="spl-search-input" placeholder="Search by name or sport...">
            </div>
            <select id="slSportFilter" class="spl-filter-input" style="min-width:130px;max-width:130px;flex:0 0 auto;">
                <option value="">All Sports</option>
                <option value="Basketball">Basketball</option>
                <option value="Volleyball">Volleyball</option>
                <option value="Swimming">Swimming</option>
                <option value="Badminton">Badminton</option>
                <option value="Table Tennis">Table Tennis</option>
                <option value="Track and Field">Track and Field</option>
            </select>
            <select id="slPaymentFilter" class="spl-filter-input" style="min-width:120px;max-width:120px;flex:0 0 auto;">
                <option value="">All Payment Status</option>
                <option value="Paid">Paid</option>
                <option value="Not Paid">Not Paid</option>
            </select>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-shrink:0;">
            <button type="button" id="slExportCsvBtn" class="spl-btn spl-btn-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <!-- ── Participants Table ── -->
    <div class="spl-table-card">
        <div class="spl-table-wrapper">
            <table class="spl-table">
                <thead>
                    <tr>
                        <th style="text-align:center;">
                            FULL NAME
                            <div class="spl-col-hint">Last, First, Middle</div>
                        </th>
                        <th>SPORT</th>
                        <th>DIVISION</th>
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
                        <td colspan="9" class="spl-empty">Loading participants...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Pagination ── -->
        <div class="sl-pagination-wrapper">
            <div class="sl-pagination-info">
                Showing <span id="slShowingStart">0</span>–<span id="slShowingEnd">0</span> of <span id="slTotalRecords">0</span> participants
            </div>
            <div class="sl-pagination-controls">
                <button type="button" class="sl-page-btn" id="slFirstPage" title="First">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                </button>
                <button type="button" class="sl-page-btn" id="slPrevPage" title="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="sl-page-numbers" id="slPageNumbers"></div>
                <button type="button" class="sl-page-btn" id="slNextPage" title="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button type="button" class="sl-page-btn" id="slLastPage" title="Last">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                </button>
            </div>
            <div class="sl-per-page">
                <label for="slPerPage">Per page:</label>
                <select id="slPerPage" class="sl-per-page-select">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                </select>
            </div>
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

<!-- ── Edit Payment Status Modal ── -->
<div class="spl-modal-overlay" id="slEditModal" style="display:none;">
    <div class="spl-modal-box" id="slEditBox" style="max-width:480px;">
        <div class="spl-modal-header">
            <h3>Edit Payment Status</h3>
            <div style="display:flex;align-items:center;gap:4px;">
                <button type="button" class="spl-modal-close" id="slEditMaximize" title="Maximize" style="font-size:15px;padding:2px 8px;">□</button>
                <button type="button" class="spl-modal-close" id="slEditClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="spl-modal-body" style="padding:24px;background:#fff;">
            <input type="hidden" id="slEditParticipantId">
            <p id="slEditParticipantName" style="font-size:14px;font-weight:600;color:#111827;margin-bottom:16px;"></p>
            <label for="slEditPaymentStatus" style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Payment Status</label>
            <select id="slEditPaymentStatus" class="spl-filter-input" style="width:100%;max-width:none;">
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
            </select>
            <p style="font-size:12px;color:#6b7280;margin-top:8px;">Update whether the participant has already paid or is still unpaid.</p>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #f1f5f9;background:#fff;border-radius:0 0 14px 14px;">
            <button type="button" id="slEditCancel" class="spl-action-btn" style="background:#fff;border:1.5px solid #d1d5db;color:#374151;">Cancel</button>
            <button type="button" id="slEditSave" class="spl-action-btn" style="background:#22c55e;color:#fff;border:none;">Save Changes</button>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation Modal ── -->
<div class="spl-modal-overlay" id="slDeleteModal" style="display:none;">
    <div class="spl-modal-box" style="max-width:440px;">
        <div class="spl-modal-header" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                Delete Participant
            </h3>
            <button type="button" class="spl-modal-close" id="slDeleteClose">&times;</button>
        </div>
        <div class="spl-modal-body" style="padding:24px 26px;background:#fff;">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:4px;">
                Are you sure you want to remove <strong id="slDeleteName" style="color:#111827;"></strong> from the approved participants list?
            </p>
            <p style="font-size:13px;color:#9ca3af;">This action cannot be undone.</p>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #f1f5f9;background:#fff;border-radius:0 0 14px 14px;">
            <button type="button" id="slDeleteCancel"
                style="padding:9px 22px;border-radius:8px;border:1.5px solid #d1d5db;background:#fff;color:#374151;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:background 0.2s;">
                Cancel
            </button>
            <button type="button" id="slDeleteConfirm"
                style="padding:9px 22px;border-radius:8px;border:none;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,.3);transition:all 0.2s;">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- ── Toast ── -->
<div id="slToast"
     style="position:fixed;bottom:22px;right:22px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;z-index:9999;display:none;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(0,0,0,.2);">
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Program_Management/assets/js/sports/sports_list.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
