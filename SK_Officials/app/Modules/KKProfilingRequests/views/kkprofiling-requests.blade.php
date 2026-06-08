<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KK Profiling Requests - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
        'app/Modules/KKProfilingRequests/assets/css/kk-questionnaire-view.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container kkprofiling-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">KK Profiling Requests</h1>
                <p class="page-subtitle">
                    Review, approve, or reject KK Profiling submissions from kabataan.
                </p>
            </div>
        </section>

        <!-- ── KK Profiling Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kkStatApproved">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Approved KK Profiling Requests</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kkStatPending">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Pending KK Profiling Requests</span>
            </div>
            <div class="stat-card stat-card-red">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kkStatRejected">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Rejected KK Profiling Requests</span>
            </div>
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kkStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Total KK Profiling Submissions</span>
            </div>
        </div>

        <section class="page-filters-section">
            <!-- ── Action Bar: Search + Compare ── -->
            <div class="table-action-bar">
                <div class="abyip-search-inline">
                    <label for="kkSearch" class="abyip-sr-only">Search KK profiling records</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="kkSearch" class="abyip-filter-search-inline" placeholder="Search KK profiling..." maxlength="80" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="filters-row">
                <div class="filter-item">
                    <label for="kkStatusFilter" class="filter-label">Kabataan Status</label>
                    <select id="kkStatusFilter" class="filter-select" onchange="document.querySelectorAll('.status-tab').forEach(t=>t.classList.remove('active')); document.querySelector('[data-status-filter=\'' + this.value + '\']')?.classList.add('active'); document.querySelector('[data-status-filter=\'' + this.value + '\']')?.click();">
                        <option value="All">All Pending</option>
                        <option value="Not Profiled">Not Profiled</option>
                        <option value="Wrong Credentials">Wrong Credentials</option>
                        <option value="Duplicate">Duplicate</option>
                    </select>
                    {{-- Hidden tabs still used by JS logic --}}
                    <div class="status-tabs d-none" id="kkStatusTabs" style="display:none!important;">
                        <button type="button" class="status-tab active" data-status-filter="All">All Pending</button>
                        <button type="button" class="status-tab" data-status-filter="Not Profiled">Not Profiled</button>
                        <button type="button" class="status-tab" data-status-filter="Wrong Credentials">Wrong Credentials</button>
                        <button type="button" class="status-tab" data-status-filter="Duplicate">Duplicate</button>
                    </div>
                </div>
                <div class="filter-item">
                    <label for="kkBarangayFilter" class="filter-label">Purok/Sitio</label>
                    <select id="kkBarangayFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="BAYSIDE">BAYSIDE</option>
                        <option value="VILLA GRACIA">VILLA GRACIA</option>
                        <option value="IMELDA">IMELDA</option>
                        <option value="LUPANG PANGAKO">LUPANG PANGAKO</option>
                        <option value="DAMAYAN">DAMAYAN</option>
                        <option value="MARCELO">MARCELO</option>
                        <option value="BIGAYAN VILLA ROSA">BIGAYAN VILLA ROSA</option>
                        <option value="PHASE 3">PHASE 3</option>
                        <option value="BIGAYAN SAN LUIS">BIGAYAN SAN LUIS</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="kkVoterFilter" class="filter-label">Voter</label>
                    <select id="kkVoterFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">KK Profiling Requests</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="kk-table">
                        <thead>
                            <tr>
                                <th>Respondent #</th>
                                <th>
                                    FULLNAME
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Barangay</th>
                                <th>Purok/Zone</th>
                                <th>Registered Voter</th>
                                <th>Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="kkRequestsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Pagination Controls -->
<div class="pagination-container">
    <div class="pagination-info">
        <span id="kkPaginationInfo">Showing 1-8 of 8 records</span>
    </div>
    <div class="pagination-controls">
        <button type="button" id="kkPrevBtn" class="pagination-btn" disabled>Previous</button>
        <div class="pagination-numbers" id="kkPageNumbers"></div>
        <button type="button" id="kkNextBtn" class="pagination-btn">Next</button>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkViewModal" style="display:none;">
    <div class="modal-box kk-modal-box kk-modal-animate kk-modal-no-border kk-view-modal-wide">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">KK Survey Questionnaire</h2>
                <span class="kk-modal-subtitle">KK Profiling Submission Details</span>
            </div>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kk-view-modal-body kk-qs-body">
            <div class="kk-qs-scroll-wrapper">
                @include('KKProfilingRequests::partials.kk-profiling-view-questionnaire', [
                    'barangayLogoUrl' => $barangayLogoUrl ?? null,
                    'barangayName' => $barangayName ?? null,
                ])
            </div>
        </div>
        <div class="modal-footer kk-view-modal-footer">
            <button type="button" class="btn btn-reject" id="kkViewRejectBtn">Reject</button>
            <button type="button" class="btn btn-approve" id="kkViewApproveBtn">Approve</button>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkApproveModal" style="display:none;">
    <div class="modal-box kk-modal-box kk-modal-animate-small kk-modal-no-border">
        <div class="modal-header">
            <h2 class="modal-title">Approve KK Profiling Submission</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to approve this KK Profiling submission?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-approve-confirm primary-btn" id="kkApproveConfirmBtn">Confirm Approve</button>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkRejectModal" style="display:none;">
    <div class="modal-box kk-modal-box kk-modal-animate kk-modal-no-border kk-reject-modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Reject KK Profiling Submission</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="reject-description">
                Select one or more reasons for rejection.
            </p>
            <div class="reject-reasons">
                <label class="reject-reason-item">
                    <input type="checkbox" class="kk-reject-reason" value="Invalid birthdate / age mismatch">
                    <span>Invalid birthdate / age mismatch</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" class="kk-reject-reason" value="Incorrect Purok / Address">
                    <span>Incorrect Purok / Address</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" class="kk-reject-reason" value="Duplicate submission">
                    <span>Duplicate submission</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" class="kk-reject-reason kk-reject-other-checkbox" value="Other" id="kkRejectOtherCheckbox">
                    <span>Other</span>
                </label>
                <div class="reject-reason-item other-reason kk-reject-other-wrap" id="kkRejectOtherWrap" style="display:none;">
                    <label for="kkRejectOtherReason">Specify reason:</label>
                    <textarea id="kkRejectOtherReason" class="kk-reject-other-textarea" rows="3" maxlength="255" placeholder="Type your reason here..."></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-reject-confirm primary-btn" id="kkRejectConfirmBtn">Confirm Reject</button>
        </div>
    </div>
</div>

<!-- Success Modal - Removed and replaced with toast notification -->

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/KKProfilingRequests/assets/js/kkprofiling-requests.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>

