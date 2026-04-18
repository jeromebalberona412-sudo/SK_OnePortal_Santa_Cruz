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

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="kkBarangayFilter" class="filter-label">Purok/Sitio</label>
                    <select id="kkBarangayFilter" class="filter-select">
                        <option value="">Prok/Sitio</option>
                        <option value="BAYSIDE">BAYSIDE</option>
                        <option value="VILLA GRACIA">VILLA GRACIA</option>
                        <option value="IMELDA">IMELDA</option>
                        <option value="LUPANG PANGAKO">LUPANG PANGAKO</option>
                        <option value="DAMAYAN">DAMAYAN</option>
                        <option value="MARCELO">MARCELO</option>
                        <option value="BIGAYANVILLA ROSA">BIGAYANVILLA ROSA</option>
                        <option value="PHASE3">PHASE3</option>
                        <option value="BIGAYANSANLUIS">BIGAYANSANLUIS</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="kkVoterFilter" class="filter-label">Registered Voter</label>
                    <select id="kkVoterFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="filter-item">
                    <!-- KK Profiling Schedule Button -->
                    <button type="button" id="kkProfilingScheduleBtn" class="kk-schedule-btn">
                        KK Profiling Schedule
                    </button>
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
                                <th>
                                    FULLNAME
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Barangay</th>
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
                    <div class="kk-view-section-title">GENERAL INFORMATION</div>
                    <div class="kk-view-row"><span class="kk-view-label">Respondent Number:</span><span class="kk-view-value" id="kkViewRespondentNumber"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Date:</span><span class="kk-view-value" id="kkViewDate"></span></div>
                    
                    <div class="kk-view-section-title">I. PROFILE - Name of Respondent</div>
                    <div class="kk-view-row"><span class="kk-view-label">Last Name:</span><span class="kk-view-value" id="kkViewLastName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">First Name:</span><span class="kk-view-value" id="kkViewFirstName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Middle Name:</span><span class="kk-view-value" id="kkViewMiddleName"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Suffix:</span><span class="kk-view-value" id="kkViewSuffix"></span></div>
                    
                    <div class="kk-view-section-title">Location</div>
                    <div class="kk-view-row"><span class="kk-view-label">Region:</span><span class="kk-view-value" id="kkViewRegion"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Province:</span><span class="kk-view-value" id="kkViewProvince"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">City / Municipality:</span><span class="kk-view-value" id="kkViewCity"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Barangay:</span><span class="kk-view-value" id="kkViewBarangay"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Purok / Zone:</span><span class="kk-view-value" id="kkViewPurokZone"></span></div>
                    
                    <div class="kk-view-section-title">Personal Information</div>
                    <div class="kk-view-row"><span class="kk-view-label">Sex Assigned at Birth:</span><span class="kk-view-value" id="kkViewSexAssignedAtBirth"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Age:</span><span class="kk-view-value" id="kkViewAge"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Birthday (dd/mm/yy):</span><span class="kk-view-value" id="kkViewBirthday"></span></div>
                    
                    <div class="kk-view-section-title">Contact Information</div>
                    <div class="kk-view-row"><span class="kk-view-label">Email Address:</span><span class="kk-view-value" id="kkViewEmailAddress"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Contact Number:</span><span class="kk-view-value" id="kkViewContactNumber"></span></div>
                </div>
                <div class="modal-column">
                    <div class="kk-view-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
                    <div class="kk-view-row"><span class="kk-view-label">Civil Status:</span><span class="kk-view-value" id="kkViewCivilStatus"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Youth Classification:</span><span class="kk-view-value" id="kkViewYouthClassification"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Youth Age Group:</span><span class="kk-view-value" id="kkViewYouthAgeGroup"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Work Status:</span><span class="kk-view-value" id="kkViewWorkStatus"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Educational Background:</span><span class="kk-view-value" id="kkViewEducationalBackground"></span></div>
                    
                    <div class="kk-view-section-title">VOTER & PARTICIPATION INFO</div>
                    <div class="kk-view-row"><span class="kk-view-label">Registered SK Voter:</span><span class="kk-view-value" id="kkViewRegisteredSKVoter"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Registered National Voter:</span><span class="kk-view-value" id="kkViewRegisteredNationalVoter"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Voting History (Last SK Election):</span><span class="kk-view-value" id="kkViewVotingHistory"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label" id="kkViewVotingFrequencyLabel">If YES, how many times:</span><span class="kk-view-value" id="kkViewVotingFrequency"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label" id="kkViewVotingReasonLabel">If NO, reason:</span><span class="kk-view-value" id="kkViewVotingReason"></span></div>
                    
                    <div class="kk-view-section-title">Participation</div>
                    <div class="kk-view-row"><span class="kk-view-label">Have you already attended a KK Assembly?</span><span class="kk-view-value" id="kkViewAttendedKKAssembly"></span></div>
                    
                    <div class="kk-view-section-title">SOCIAL / COMMUNITY</div>
                    <div class="kk-view-row"><span class="kk-view-label">Facebook Account:</span><span class="kk-view-value" id="kkViewFacebookAccount"></span></div>
                    <div class="kk-view-row"><span class="kk-view-label">Willing to join the group chat?</span><span class="kk-view-value" id="kkViewWillingToJoinGroupChat"></span></div>
                    
                    <div class="kk-view-section-title">SIGNATURE</div>
                    <div class="kk-view-row"><span class="kk-view-label">Name and Signature of Participant:</span><span class="kk-view-value" id="kkViewSignature"></span></div>
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

<!-- KK Profiling Schedule Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkScheduleModal" style="display:none;">
    <div class="modal-box kk-schedule-modal-box kk-modal-animate">
        <div class="modal-header">
            <h2 class="modal-title">KK Profiling Schedule</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kkScheduleModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kk-schedule-modal-body">
            <!-- Instructions -->
            <div class="schedule-instructions">
                <p class="instruction-text">
                    <strong>Instructions:</strong> Select 1-week periods throughout the year when KK Profiling will be open. 
                    Click on dates to mark/unmark them as profiling periods.
                </p>
            </div>
            
            <!-- Calendar Container -->
            <div class="kk-calendar-container">
                <div class="kk-calendar-grid">
                    <!-- Calendar Header -->
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button type="button" class="calendar-nav-btn" id="calendarPrev">&lt;</button>
                            <span class="calendar-month-year" id="calendarMonthYear">March 2026</span>
                            <button type="button" class="calendar-nav-btn" id="calendarNext">&gt;</button>
                            <button type="button" class="btn btn-jump-date" id="scheduleJumpBtn">Jump to date</button>
                        </div>
                    </div>
                    
                    <!-- Week Days -->
                    <div class="calendar-weekdays">
                        <div class="calendar-weekday">Sun</div>
                        <div class="calendar-weekday">Mon</div>
                        <div class="calendar-weekday">Tue</div>
                        <div class="calendar-weekday">Wed</div>
                        <div class="calendar-weekday">Thu</div>
                        <div class="calendar-weekday">Fri</div>
                        <div class="calendar-weekday">Sat</div>
                    </div>
                    
                    <!-- Calendar Days -->
                    <div class="calendar-days" id="calendarDays">
                        <!-- Days will be generated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-color profiling"></div>
                    <span>Profiling Period</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color today"></div>
                    <span>Today</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color other-month"></div>
                    <span>Other Month</span>
                </div>
            </div>
            <div class="schedule-bottom-actions">
                <button type="button" class="btn btn-secondary" id="clearScheduleBtn">Clear All</button>
                <button type="button" class="btn btn-primary" id="saveScheduleBtn">Save Schedule</button>
            </div>
        </div>
    </div>
</div>

<!-- Clear All Schedules Confirmation Modal -->
<div class="modal-backdrop kk-modal-backdrop" id="kkClearScheduleModal" style="display:none;">
    <div class="modal-box kk-clear-schedule-modal-box kk-modal-animate-small">
        <div class="modal-header kk-clear-schedule-header">
            <div class="kk-clear-schedule-icon">⚠️</div>
            <h2 class="modal-title">Clear All Profiling Schedules</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body kk-clear-schedule-body">
            <p class="kk-clear-schedule-message">
                Are you sure you want to clear all profiling schedules?
            </p>
            <p class="kk-clear-schedule-warning">
                This action cannot be undone. All scheduled profiling periods will be permanently removed.
            </p>
            <div class="kk-clear-schedule-details">
                <div class="kk-schedule-count">
                    <span class="kk-count-label">Total scheduled periods:</span>
                    <span class="kk-count-value" id="scheduleCount">0</span>
                </div>
            </div>
        </div>
        <div class="modal-footer kk-clear-schedule-footer">
            <button type="button" class="btn btn-cancel-clear" id="cancelClearBtn">Cancel</button>
            <button type="button" class="btn btn-confirm-clear" id="confirmClearBtn">Clear All Schedules</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/KKProfilingRequests/assets/js/kkprofiling-requests.js'
])

</body>
</html>

