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
            <!-- ── Action Bar: Search + Schedule KK ── -->
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
                <button type="button" id="kkProfilingScheduleBtn" class="btn primary-btn">
                    Schedule KK
                </button>
            </div>
            <div class="filters-row">
                <div class="filter-item">
                    <label for="kkStatusFilter" class="filter-label">Status</label>
                    <select id="kkStatusFilter" class="filter-select" onchange="document.querySelectorAll('.status-tab').forEach(t=>t.classList.remove('active')); document.querySelector('[data-status-filter=\'' + this.value + '\']')?.classList.add('active'); document.querySelector('[data-status-filter=\'' + this.value + '\']')?.click();">
                        <option value="All">All</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    {{-- Hidden tabs still used by JS logic --}}
                    <div class="status-tabs d-none" id="kkStatusTabs" style="display:none!important;">
                        <button type="button" class="status-tab active" data-status-filter="All">All</button>
                        <button type="button" class="status-tab" data-status-filter="Pending">Pending</button>
                        <button type="button" class="status-tab" data-status-filter="Approved">Approved</button>
                        <button type="button" class="status-tab" data-status-filter="Rejected">Rejected</button>
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
                        <option value="BIGAYANVILLA ROSA">BIGAYANVILLA ROSA</option>
                        <option value="PHASE3">PHASE3</option>
                        <option value="BIGAYANSANLUIS">BIGAYANSANLUIS</option>
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
    <div class="modal-box kk-modal-box kk-modal-animate kk-modal-no-border kk-view-modal-wide">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">KK Survey Questionnaire</h2>
                <span class="kk-modal-subtitle">KK Profiling Submission Details</span>
            </div>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kk-view-modal-body kk-qs-body">

            {{-- General Info --}}
            <div class="kk-qs-general-row">
                <div class="kk-qs-general-field">
                    <span class="kk-qs-field-label">Respondent #:</span>
                    <span class="kk-qs-field-value" id="kkViewRespondentNumber"></span>
                </div>
                <div class="kk-qs-general-field">
                    <span class="kk-qs-field-label">Date:</span>
                    <span class="kk-qs-field-value" id="kkViewDate"></span>
                </div>
            </div>

            {{-- I. PROFILE --}}
            <div class="kk-qs-section-title">I. PROFILE</div>

            <div class="kk-qs-row-label">Name of Respondent:</div>
            <div class="kk-qs-name-row">
                <div class="kk-qs-name-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewLastName"></span>
                    <span class="kk-qs-col-label">Last Name</span>
                </div>
                <div class="kk-qs-name-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewFirstName"></span>
                    <span class="kk-qs-col-label">First Name</span>
                </div>
                <div class="kk-qs-name-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewMiddleName"></span>
                    <span class="kk-qs-col-label">Middle Name</span>
                </div>
                <div class="kk-qs-name-col kk-qs-name-col--suffix">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewSuffix"></span>
                    <span class="kk-qs-col-label">Suffix</span>
                </div>
            </div>

            <div class="kk-qs-row-label">Location:</div>
            <div class="kk-qs-location-row">
                <div class="kk-qs-loc-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewRegion"></span>
                    <span class="kk-qs-col-label">Region</span>
                </div>
                <div class="kk-qs-loc-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewProvince"></span>
                    <span class="kk-qs-col-label">Province</span>
                </div>
                <div class="kk-qs-loc-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewCity"></span>
                    <span class="kk-qs-col-label">City/Municipality</span>
                </div>
                <div class="kk-qs-loc-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewBarangay"></span>
                    <span class="kk-qs-col-label">Barangay</span>
                </div>
                <div class="kk-qs-loc-col">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewPurokZone"></span>
                    <span class="kk-qs-col-label">Purok/Zone</span>
                </div>
            </div>

            <div class="kk-qs-personal-row">
                <div class="kk-qs-sex-box">
                    <span class="kk-qs-box-label">Sex Assigned by Birth:</span>
                    <span class="kk-qs-field-value" id="kkViewSexAssignedAtBirth"></span>
                </div>
                <div class="kk-qs-age-group">
                    <div class="kk-qs-inline-field">
                        <span class="kk-qs-field-label">Age:</span>
                        <span class="kk-qs-field-value kk-qs-underline kk-qs-short" id="kkViewAge"></span>
                    </div>
                    <div class="kk-qs-inline-field">
                        <span class="kk-qs-field-label">Birthday:</span>
                        <span class="kk-qs-field-value kk-qs-underline" id="kkViewBirthday"></span>
                        <span class="kk-qs-hint">(dd/mm/yy)</span>
                    </div>
                </div>
                <div class="kk-qs-contact-group">
                    <div class="kk-qs-inline-field">
                        <span class="kk-qs-field-label">E-mail address:</span>
                        <span class="kk-qs-field-value kk-qs-underline" id="kkViewEmailAddress"></span>
                    </div>
                    <div class="kk-qs-inline-field">
                        <span class="kk-qs-field-label">Contact #:</span>
                        <span class="kk-qs-field-value kk-qs-underline" id="kkViewContactNumber"></span>
                    </div>
                </div>
            </div>

            {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
            <div class="kk-qs-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
            <p class="kk-qs-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

            <div class="kk-qs-demo-grid">
                <div class="kk-qs-demo-left">
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Civil Status</div>
                        <div class="kk-qs-demo-options kk-qs-options-2col">
                            <div class="kk-qs-options-col">
                                <span class="kk-qs-check-item" id="kkViewCS_Single">☐ Single</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Married">☐ Married</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Widowed">☐ Widowed</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Divorced">☐ Divorced</span>
                            </div>
                            <div class="kk-qs-options-col">
                                <span class="kk-qs-check-item" id="kkViewCS_Separated">☐ Separated</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Annulled">☐ Annulled</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Unknown">☐ Unknown</span>
                                <span class="kk-qs-check-item" id="kkViewCS_Livein">☐ Live-in</span>
                            </div>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Youth Age Group</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="kkViewYAG_Child">☐ Child Youth (15-17 yrs old)</span>
                            <span class="kk-qs-check-item" id="kkViewYAG_Core">☐ Core Youth (18-24 yrs old)</span>
                            <span class="kk-qs-check-item" id="kkViewYAG_Young">☐ Young Adult (15-30 yrs old)</span>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Educational Background</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="kkViewEB_ElemLevel">☐ Elementary Level</span>
                            <span class="kk-qs-check-item" id="kkViewEB_ElemGrad">☐ Elementary Grad</span>
                            <span class="kk-qs-check-item" id="kkViewEB_HSLevel">☐ High school Level</span>
                            <span class="kk-qs-check-item" id="kkViewEB_HSGrad">☐ High school Grad</span>
                            <span class="kk-qs-check-item" id="kkViewEB_VocGrad">☐ Vocational Grad</span>
                            <span class="kk-qs-check-item" id="kkViewEB_ColLevel">☐ College Level</span>
                            <span class="kk-qs-check-item" id="kkViewEB_ColGrad">☐ College Grad</span>
                            <span class="kk-qs-check-item" id="kkViewEB_MasLevel">☐ Masters Level</span>
                            <span class="kk-qs-check-item" id="kkViewEB_MasGrad">☐ Masters Grad</span>
                            <span class="kk-qs-check-item" id="kkViewEB_DocLevel">☐ Doctorate Level</span>
                            <span class="kk-qs-check-item" id="kkViewEB_DocGrad">☐ Doctorate Graduate</span>
                        </div>
                    </div>
                </div>

                <div class="kk-qs-demo-right">
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Youth Classification</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="kkViewYC_ISY">☐ In school Youth</span>
                            <span class="kk-qs-check-item" id="kkViewYC_OSY">☐ Out of School Youth</span>
                            <span class="kk-qs-check-item" id="kkViewYC_Working">☐ Working Youth</span>
                            <span class="kk-qs-check-item" id="kkViewYC_Specific">☐ Youth w/ Specific needs:</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="kkViewYC_PWD">☐ Person w/ Disability</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="kkViewYC_CICL">☐ Children In Conflict w/ Law</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="kkViewYC_IP">☐ Indigenous People</span>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Work Status</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="kkViewWS_Employed">☐ Employed</span>
                            <span class="kk-qs-check-item" id="kkViewWS_Unemployed">☐ Unemployed</span>
                            <span class="kk-qs-check-item" id="kkViewWS_SelfEmployed">☐ Self-Employed</span>
                            <span class="kk-qs-check-item" id="kkViewWS_Looking">☐ Currently looking for a Job</span>
                            <span class="kk-qs-check-item" id="kkViewWS_NotInterested">☐ Not Interested Looking for a Job</span>
                        </div>
                    </div>

                    <div class="kk-qs-voter-grid">
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">Registered SK Voter?</div>
                            <span class="kk-qs-check-item" id="kkViewSKV_Yes">☐ Yes</span>
                            <span class="kk-qs-check-item" id="kkViewSKV_No">☐ No</span>
                        </div>
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">Did you vote last SK elections?</div>
                            <span class="kk-qs-check-item" id="kkViewVH_Yes">☐ Yes</span>
                            <span class="kk-qs-check-item" id="kkViewVH_No">☐ No</span>
                        </div>
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">If Yes, How many times?</div>
                            <span class="kk-qs-check-item" id="kkViewVF_12">☐ 1-2 Times</span>
                            <span class="kk-qs-check-item" id="kkViewVF_34">☐ 3-4 Times</span>
                            <span class="kk-qs-check-item" id="kkViewVF_5">☐ 5 and above</span>
                        </div>
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">Registered National Voter?</div>
                            <span class="kk-qs-check-item" id="kkViewNV_Yes">☐ Yes</span>
                            <span class="kk-qs-check-item" id="kkViewNV_No">☐ No</span>
                        </div>
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">Have you already attended a KK Assembly?</div>
                            <span class="kk-qs-check-item" id="kkViewKK_Yes">☐ Yes</span>
                            <span class="kk-qs-check-item" id="kkViewKK_No">☐ No</span>
                        </div>
                        <div class="kk-qs-voter-block">
                            <div class="kk-qs-voter-label">If No, Why?</div>
                            <span class="kk-qs-check-item" id="kkViewVR_NoKK">☐ There was no KK Assembly Meeting</span>
                            <span class="kk-qs-check-item" id="kkViewVR_NotInt">☐ Not interested to Attend</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social / Signature --}}
            <div class="kk-qs-social-row">
                <div class="kk-qs-inline-field">
                    <span class="kk-qs-field-label">FB Account:</span>
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewFacebookAccount"></span>
                </div>
                <div class="kk-qs-inline-field">
                    <span class="kk-qs-field-label">Willing to join the group chat?</span>
                    <span class="kk-qs-check-item" id="kkViewGC_Yes">☐ Yes</span>
                    <span class="kk-qs-check-item" id="kkViewGC_No">☐ No</span>
                </div>
            </div>

            <div class="kk-qs-signature-row">
                <div class="kk-qs-sig-field">
                    <span class="kk-qs-field-value kk-qs-underline" id="kkViewSignature"></span>
                    <span class="kk-qs-col-label">Name and Signature of Participant</span>
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
                        <div class="calendar-legend-inline">
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
            
            <!-- Legend removed — Jump to date is in the calendar header -->
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

