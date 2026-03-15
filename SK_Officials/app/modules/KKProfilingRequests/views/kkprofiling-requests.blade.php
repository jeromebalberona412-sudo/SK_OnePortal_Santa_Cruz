<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KK Profiling Requests - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/KKProfilingRequests/assets/css/kkprofiling-requests.css'
    ])
</head>
<body>

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
            <div class="page-header-right page-header-right-with-search">
                <div class="header-search-wrap">
                    <input type="text" id="kkSearch" class="filter-input kk-search-input" placeholder="Search" maxlength="80">
                </div>
                <div class="status-tabs" id="kkStatusTabs">
                    <button type="button" class="status-tab active" data-status-filter="All">All</button>
                    <button type="button" class="status-tab" data-status-filter="Pending">Pending</button>
                    <button type="button" class="status-tab" data-status-filter="Approved">Approved</button>
                    <button type="button" class="status-tab" data-status-filter="Rejected">Rejected</button>
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
                                <th class="col-fullname">FN, MN, LN, Jr.</th>
                                <th class="col-age">Age</th>
                                <th class="col-purok">Purok / Sitio</th>
                                <th class="col-contact">Contact</th>
                                <th class="col-status">Status</th>
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

<!-- View Details Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkViewModal" style="display:none;">
    <div class="modal-box kk-modal-box kk-modal-animate kk-modal-no-border">
        <div class="modal-header">
            <h2 class="modal-title">KK Profiling Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kk-view-modal-body">
            <div class="modal-columns kk-view-columns">
                <div class="modal-column">
                    <div class="kk-view-row"><span class="kk-view-label">First Name:</span><span class="kk-view-value" id="kkViewFirstName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Middle Name:</span><span class="kk-view-value" id="kkViewMiddleName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Last Name:</span><span class="kk-view-value" id="kkViewLastName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Suffix:</span><span class="kk-view-value" id="kkViewSuffix"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Date of Birth:</span><span class="kk-view-value" id="kkViewDateOfBirth"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Age:</span><span class="kk-view-value" id="kkViewAge"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Sex Assigned at Birth:</span><span class="kk-view-value" id="kkViewSex"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Civil Status:</span><span class="kk-view-value" id="kkViewCivilStatus"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Region:</span><span class="kk-view-value" id="kkViewRegion"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Province:</span><span class="kk-view-value" id="kkViewProvince"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">City/Municipality:</span><span class="kk-view-value" id="kkViewCity"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Purok / Sitio:</span><span class="kk-view-value" id="kkViewPurokSitio"></span></div>
                </div>
                <div class="modal-column">
                    <div class="kk-view-row"><span class="kk-view-label">Address:</span><span class="kk-view-value" id="kkViewAddress"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Email:</span><span class="kk-view-value" id="kkViewEmail"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Contact Number:</span><span class="kk-view-value" id="kkViewContact"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Highest Educational Attainment:</span><span class="kk-view-value" id="kkViewHighestEducation"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Currently Studying:</span><span class="kk-view-value" id="kkViewCurrentlyStudying"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Work Status:</span><span class="kk-view-value" id="kkViewWorkStatus"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Occupation:</span><span class="kk-view-value" id="kkViewOccupation"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Registered Voter:</span><span class="kk-view-value" id="kkViewRegisteredVoter"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Voted in Last Election:</span><span class="kk-view-value" id="kkViewVotedLastElection"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Youth Classification:</span><span class="kk-view-value" id="kkViewYouthClassification"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Status:</span><span class="kk-view-value" id="kkViewStatus"></span></div>
                </div>
            </div>
            <div class="kk-view-rejection-wrap" id="kkViewRejectionWrap" style="display:none;">
                <span class="kk-view-label">Rejection reason:</span>
                <p class="kk-view-rejection-text" id="kkViewRejectionText"></p>
            </div>
        </div>
        <div class="modal-footer kk-view-modal-footer">
            <button type="button" class="btn btn-approve" id="kkViewApproveBtn">Approve</button>
            <button type="button" class="btn btn-reject" id="kkViewRejectBtn">Reject</button>
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
    <div class="modal-box kk-modal-box kk-modal-animate kk-modal-no-border">
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
                    <input type="checkbox" class="kk-reject-reason" value="Incomplete information">
                    <span>Incomplete information</span>
                </label>
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
                    <input type="checkbox" class="kk-reject-reason" value="Illegible or missing required fields">
                    <span>Illegible or missing required fields</span>
                </label>
                <label class="reject-reason-item">
                    <input type="checkbox" class="kk-reject-reason kk-reject-other-checkbox" value="Other" id="kkRejectOtherCheckbox">
                    <span>Other</span>
                </label>
                <div class="reject-reason-item other-reason kk-reject-other-wrap" id="kkRejectOtherWrap" style="display:none;">
                    <label for="kkRejectOtherReason">Specify reason:</label>
                    <textarea id="kkRejectOtherReason" class="kk-reject-other-textarea" rows="3" placeholder="Type your reason here..."></textarea>
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
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/KKProfilingRequests/assets/js/kkprofiling-requests.js'
])

</body>
</html>

