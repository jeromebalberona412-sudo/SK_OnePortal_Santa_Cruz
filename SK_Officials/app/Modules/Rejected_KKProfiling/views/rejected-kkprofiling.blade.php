<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected KK Profiling - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/KKProfilingRequests/assets/css/kkprofiling-requests.css',
        'app/Modules/Rejected_KKProfiling/assets/css/rejected-kkprofiling.css'
    ])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/sk-archive-terms.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rejected-kk-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Rejected KK Profiling</h1>
                <p class="page-subtitle">KK Profiling requests that were rejected.</p>
            </div>
            <div class="page-header-right">
                <input type="text" id="rejectedKKSearch" class="filter-input" placeholder="Search by name…">
            </div>
        </section>

        <!-- Stats Cards -->
        <div class="module-stats-grid" id="rkkStatsRow"></div>

        <!-- Restore Success Banner -->
        <div class="restore-success-banner" id="rkkRestoreBanner" style="display:none;">
            <span class="restore-banner-icon">✓</span>
            <span class="restore-banner-text" id="rkkRestoreBannerText"></span>
        </div>

        <!-- Filter Tabs + Show Archive -->
        <div class="filter-tabs-row filter-tabs-row--with-archive">
            <div class="filter-tabs-group">
                <button class="filter-tab active" data-filter="all">All Rejected</button>
                <button class="filter-tab" data-filter="today">Rejected Today</button>
                <button class="filter-tab" data-filter="week">This Week</button>
                <button class="filter-tab" data-filter="month">This Month</button>
            </div>
            @include('layout::partials.archive-show-filter')
        </div>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title" id="rkkSectionLabel">All Rejected Records</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="rkk-table">
                        <thead>
                            <tr>
                                <th>Full Name<div class="column-hint">LN, FN, MN, Suffix</div></th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Zone</th>
                                <th>Youth Classification</th>
                                <th>Rejection Reason</th>
                                <th>Rejected Date</th>
                                <th>Rejected Time</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rejectedKKTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="rejectedKKPaginationInfo">No records found</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="rejectedKKPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="rejectedKKPageNumbers"></div>
                        <button type="button" id="rejectedKKNextBtn" class="pagination-btn" disabled>Next</button>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Restore Confirmation Modal -->
<div class="restore-modal-backdrop" id="rkkRestoreModal" style="display:none;">
    <div class="restore-modal-box">
        <div class="restore-modal-header">
            <h2 class="restore-modal-title">Restore Record</h2>
        </div>
        <div class="restore-modal-body">
            <p class="restore-modal-message">Restore this record back to KK Profiling?</p>
            <p class="restore-modal-name" id="rkkRestoreName"></p>
        </div>
        <div class="restore-modal-footer">
            <button type="button" class="btn-cancel-restore" id="rkkRestoreCancelBtn">Cancel</button>
            <button type="button" class="btn-confirm-restore" id="rkkRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="restore-modal-backdrop" id="rkkViewModal" style="display:none;">
    <div class="restore-modal-box view-modal-box" id="rkkViewModalBox">
        <div class="restore-modal-header view-modal-header">
            <h2 class="restore-modal-title">Record Details</h2>
            <div class="view-modal-controls">
                <button type="button" class="view-modal-toggle" id="rkkViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="view-modal-close" id="rkkViewModalClose">&times;</button>
            </div>
        </div>
        <div class="view-modal-body" id="rkkViewModalBody"></div>
    </div>
</div>

<!-- KK Profiling Questionnaire (read-only, same layout as Kabataan / KK Profiling Requests) -->
<div class="modal-backdrop kk-modal-backdrop" id="rkkKkFormModal" style="display:none;">
    <div class="modal-box kk-modal-box kk-modal-no-border kk-view-modal-wide" id="rkkKkFormModalBox">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">KK Survey Questionnaire</h2>
                <span class="kk-modal-subtitle">Submitted KK Profiling Form</span>
            </div>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="rkkKkFormToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" id="rkkKkFormClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kk-qs-body" id="rkkKkFormBody">
            <div class="kk-qs-scroll-wrapper">
            <div class="kk-qs-general-row">
                <div class="kk-qs-general-field"><span class="kk-qs-field-label">Respondent #:</span><span class="kk-qs-field-value" id="rkkKkViewRespondentNumber"></span></div>
                <div class="kk-qs-general-field"><span class="kk-qs-field-label">Date:</span><span class="kk-qs-field-value" id="rkkKkViewDate"></span></div>
            </div>
            <div class="kk-qs-section-title">I. PROFILE</div>
            <div class="kk-qs-row-label">Name of Respondent:</div>
            <div class="kk-qs-name-row">
                <div class="kk-qs-name-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewLastName"></span><span class="kk-qs-col-label">Last Name</span></div>
                <div class="kk-qs-name-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewFirstName"></span><span class="kk-qs-col-label">First Name</span></div>
                <div class="kk-qs-name-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewMiddleName"></span><span class="kk-qs-col-label">Middle Name</span></div>
                <div class="kk-qs-name-col kk-qs-name-col--suffix"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewSuffix"></span><span class="kk-qs-col-label">Suffix</span></div>
            </div>
            <div class="kk-qs-row-label">Location:</div>
            <div class="kk-qs-location-row">
                <div class="kk-qs-loc-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewRegion"></span><span class="kk-qs-col-label">Region</span></div>
                <div class="kk-qs-loc-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewProvince"></span><span class="kk-qs-col-label">Province</span></div>
                <div class="kk-qs-loc-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewCity"></span><span class="kk-qs-col-label">City/Municipality</span></div>
                <div class="kk-qs-loc-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewBarangay"></span><span class="kk-qs-col-label">Barangay</span></div>
                <div class="kk-qs-loc-col"><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewPurokZone"></span><span class="kk-qs-col-label">Purok/Zone</span></div>
            </div>
            <div class="kk-qs-personal-row">
                <div class="kk-qs-sex-box"><span class="kk-qs-box-label">Sex Assigned by Birth:</span><span class="kk-qs-field-value" id="rkkKkViewSexAssignedAtBirth"></span></div>
                <div class="kk-qs-age-group">
                    <div class="kk-qs-inline-field"><span class="kk-qs-field-label">Age:</span><span class="kk-qs-field-value kk-qs-underline kk-qs-short" id="rkkKkViewAge"></span></div>
                    <div class="kk-qs-inline-field"><span class="kk-qs-field-label">Birthday:</span><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewBirthday"></span><span class="kk-qs-hint">(dd/mm/yy)</span></div>
                </div>
                <div class="kk-qs-contact-group">
                    <div class="kk-qs-inline-field"><span class="kk-qs-field-label">E-mail address:</span><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewEmailAddress"></span></div>
                    <div class="kk-qs-inline-field"><span class="kk-qs-field-label">Contact #:</span><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewContactNumber"></span></div>
                </div>
            </div>
            <div class="kk-qs-section-title">II. DEMOGRAPHIC CHARACTERISTICS</div>
            <p class="kk-qs-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>
            <div class="kk-qs-demo-grid">
                <div class="kk-qs-demo-left">
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Civil Status</div>
                        <div class="kk-qs-demo-options kk-qs-options-2col">
                            <div class="kk-qs-options-col">
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Single">☐ Single</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Married">☐ Married</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Widowed">☐ Widowed</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Divorced">☐ Divorced</span>
                            </div>
                            <div class="kk-qs-options-col">
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Separated">☐ Separated</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Annulled">☐ Annulled</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Unknown">☐ Unknown</span>
                                <span class="kk-qs-check-item" id="rkkKkViewCS_Livein">☐ Live-in</span>
                            </div>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Youth Age Group</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="rkkKkViewYAG_Child">☐ Child Youth (15-17 yrs old)</span>
                            <span class="kk-qs-check-item" id="rkkKkViewYAG_Core">☐ Core Youth (18-24 yrs old)</span>
                            <span class="kk-qs-check-item" id="rkkKkViewYAG_Young">☐ Young Adult (15-30 yrs old)</span>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Educational Background</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="rkkKkViewEB_ElemLevel">☐ Elementary Level</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_ElemGrad">☐ Elementary Grad</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_HSLevel">☐ High school Level</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_HSGrad">☐ High school Grad</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_VocGrad">☐ Vocational Grad</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_ColLevel">☐ College Level</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_ColGrad">☐ College Grad</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_MasLevel">☐ Masters Level</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_MasGrad">☐ Masters Grad</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_DocLevel">☐ Doctorate Level</span>
                            <span class="kk-qs-check-item" id="rkkKkViewEB_DocGrad">☐ Doctorate Graduate</span>
                        </div>
                    </div>
                </div>
                <div class="kk-qs-demo-right">
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Youth Classification</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="rkkKkViewYC_ISY">☐ In school Youth</span>
                            <span class="kk-qs-check-item" id="rkkKkViewYC_OSY">☐ Out of School Youth</span>
                            <span class="kk-qs-check-item" id="rkkKkViewYC_Working">☐ Working Youth</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="rkkKkViewYC_PWD">☐ Person w/ Disability</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="rkkKkViewYC_CICL">☐ Children In Conflict w/ Law</span>
                            <span class="kk-qs-check-item kk-qs-indent" id="rkkKkViewYC_IP">☐ Indigenous People</span>
                        </div>
                    </div>
                    <div class="kk-qs-demo-block">
                        <div class="kk-qs-demo-block-label">Work Status</div>
                        <div class="kk-qs-demo-options">
                            <span class="kk-qs-check-item" id="rkkKkViewWS_Employed">☐ Employed</span>
                            <span class="kk-qs-check-item" id="rkkKkViewWS_Unemployed">☐ Unemployed</span>
                            <span class="kk-qs-check-item" id="rkkKkViewWS_SelfEmployed">☐ Self-Employed</span>
                            <span class="kk-qs-check-item" id="rkkKkViewWS_Looking">☐ Currently looking for a Job</span>
                            <span class="kk-qs-check-item" id="rkkKkViewWS_NotInterested">☐ Not Interested Looking for a Job</span>
                        </div>
                    </div>
                    <div class="kk-qs-voter-grid">
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">Registered SK Voter?</div><span class="kk-qs-check-item" id="rkkKkViewSKV_Yes">☐ Yes</span><span class="kk-qs-check-item" id="rkkKkViewSKV_No">☐ No</span></div>
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">Did you vote last SK elections?</div><span class="kk-qs-check-item" id="rkkKkViewVH_Yes">☐ Yes</span><span class="kk-qs-check-item" id="rkkKkViewVH_No">☐ No</span></div>
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">If Yes, How many times?</div><span class="kk-qs-check-item" id="rkkKkViewVF_12">☐ 1-2 Times</span><span class="kk-qs-check-item" id="rkkKkViewVF_34">☐ 3-4 Times</span><span class="kk-qs-check-item" id="rkkKkViewVF_5">☐ 5 and above</span></div>
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">Registered National Voter?</div><span class="kk-qs-check-item" id="rkkKkViewNV_Yes">☐ Yes</span><span class="kk-qs-check-item" id="rkkKkViewNV_No">☐ No</span></div>
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">Have you already attended a KK Assembly?</div><span class="kk-qs-check-item" id="rkkKkViewKK_Yes">☐ Yes</span><span class="kk-qs-check-item" id="rkkKkViewKK_No">☐ No</span></div>
                        <div class="kk-qs-voter-block"><div class="kk-qs-voter-label">If No, Why?</div><span class="kk-qs-check-item" id="rkkKkViewVR_NoKK">☐ There was no KK Assembly Meeting</span><span class="kk-qs-check-item" id="rkkKkViewVR_NotInt">☐ Not interested to Attend</span></div>
                    </div>
                </div>
            </div>
            <div class="kk-qs-social-row">
                <div class="kk-qs-inline-field"><span class="kk-qs-field-label">FB Account:</span><span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewFacebookAccount"></span></div>
                <div class="kk-qs-inline-field"><span class="kk-qs-field-label">Willing to join the group chat?</span><span class="kk-qs-check-item" id="rkkKkViewGC_Yes">☐ Yes</span><span class="kk-qs-check-item" id="rkkKkViewGC_No">☐ No</span></div>
            </div>
            <div class="kk-qs-signature-row">
                <div class="kk-qs-sig-field">
                    <div class="kk-qs-sig-container">
                        <div class="kk-qs-sig-overlay" id="rkkKkViewSignatureOverlay">
                            <svg class="kk-qs-sig-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 60" fill="none" stroke="#1a1a1a" stroke-width="2"><path d="M10,40 Q20,20 30,35 T50,30 Q60,25 70,40"/><path d="M75,25 L85,45 M80,35 L95,35"/><path d="M100,25 Q110,40 120,25 Q130,10 140,25"/><path d="M145,30 Q155,20 165,30 L175,45"/></svg>
                        </div>
                        <span class="kk-qs-field-value kk-qs-underline" id="rkkKkViewSignature"></span>
                    </div>
                    <span class="kk-qs-col-label">Name and Signature of Participant</span>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="dk-toast" id="rkkToast"></div>

<script src="{{ url('/shared/js/sk-archive-terms.js') }}"></script>
@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Rejected_KKProfiling/assets/js/rejected-kkprofiling.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
