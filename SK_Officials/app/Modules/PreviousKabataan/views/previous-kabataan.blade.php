<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Previous Kabataan Records - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/PreviousKabataan/assets/css/previous-kabataan.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <!-- Page Header -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Previous Kabataan Records</h1>
                <p class="page-subtitle">Manage and view historical census records for youth profiling across all barangays.</p>
            </div>
            <div class="page-header-actions">
                <div class="abyip-search-inline">
                    <label for="prevKabSearch" class="abyip-sr-only">Search records</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="prevKabSearch" class="abyip-filter-search-inline" placeholder="Search by name..." autocomplete="off">
                    </div>
                </div>
                <button type="button" class="btn primary-btn" id="prevKabUploadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Previous Kabataan
                </button>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="page-filters-section">
            <div class="table-action-bar">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="prevKabYearFilter" class="filter-label">Previous Kabataan</label>
                    <select id="prevKabYearFilter" class="filter-select">
                        <option value="">All Years</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="prevKabPurokFilter" class="filter-label">Purok/Zone</label>
                    <select id="prevKabPurokFilter" class="filter-select">
                        <option value="">All Puroks</option>
                        <option value="Bayside">Bayside</option>
                        <option value="Villa Gracia">Villa Gracia</option>
                        <option value="Imelda">Imelda</option>
                        <option value="Lupang Pangako">Lupang Pangako</option>
                        <option value="Damayan">Damayan</option>
                        <option value="Marcelo">Marcelo</option>
                        <option value="Bigayan Villa Rosa">Bigayan Villa Rosa</option>
                        <option value="Phase 3">Phase 3</option>
                        <option value="Bigayan San Luis">Bigayan San Luis</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="prevKabVoterFilter" class="filter-label">Voter Status</label>
                    <select id="prevKabVoterFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Records List</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="prev-kab-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Barangay</th>
                                <th>Home Address</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Youth Classification</th>
                                <th>Youth Age Group</th>
                                <th>Education</th>
                                <th>Work Status</th>
                                <th>Registered Voter</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="prevKabTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info" id="prevKabPaginationInfo">Showing 0–0 of 0 records</div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevKabPrevBtn">Previous</button>
                    <div class="pagination-numbers" id="prevKabPaginationNums"></div>
                    <button class="pagination-btn" id="prevKabNextBtn">Next</button>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- View Modal -->
<div class="modal-backdrop pkab-modal-backdrop" id="prevKabViewModal" style="display:none;">
    <div class="modal-box pkab-modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Previous Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="prevKabViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body pkab-modal-body">
            <div class="pkab-view-details">
                <div class="pkab-form-scroll">

                    <div class="kkf-header">
                        <div class="kkf-header-title-col">
                            <div class="kkf-main-title">KK Survey Questionnaire</div>
                            <div class="kkf-main-hint">Previous census record (view-only).</div>
                        </div>
                        <div class="kkf-header-center-col">
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Respondent #:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="pvRespondentNumber"></span>
                            </div>
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Date:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="pvDate"></span>
                            </div>
                        </div>
                        <div class="kkf-header-logo-col">
                            <img src="{{ asset('images/barangay_logo.png') }}" alt="Barangay Calios Logo" class="kkf-brgy-logo">
                        </div>
                    </div>

                    <div class="kkf-notice-box">
                        <div class="kkf-notice-title">TO THE RESPONDENT:</div>
                        <p class="kkf-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking your time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="kkf-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    <div class="kkf-section-title">I. Profile</div>

                    <div class="kkf-row-label">Name of Respondent:</div>
                    <div class="kkf-name-row">
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="pvLastName"></span><label class="kkf-col-label">Last Name</label></div>
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="pvFirstName"></span><label class="kkf-col-label">First Name</label></div>
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="pvMiddleName"></span><label class="kkf-col-label">Middle Name</label></div>
                        <div class="kkf-name-col kkf-name-col--sm"><span class="kkf-view-val kkf-uline" id="pvSuffix"></span><label class="kkf-col-label">Suffix</label></div>
                    </div>

                    <div class="kkf-row-label">Location:</div>
                    <div class="kkf-loc-row">
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="pvRegion"></span><label class="kkf-col-label">Region</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="pvProvince"></span><label class="kkf-col-label">Province</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="pvCity"></span><label class="kkf-col-label">City/Municipality</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="pvBarangay"></span><label class="kkf-col-label">Barangay</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="pvPurokZone"></span><label class="kkf-col-label">Purok/Zone</label></div>
                    </div>

                    <div class="kkf-personal-row">
                        <div class="kkf-personal-left">
                            <div class="kkf-sex-block">
                                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvSex" value="Male" disabled> Male</label>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvSex" value="Female" disabled> Female</label>
                            </div>
                            <div class="kkf-age-dob-row">
                                <div class="kkf-inline-pair"><span class="kkf-inline-label">Age:</span><span class="kkf-view-val kkf-uline kkf-uline-short" id="pvAge"></span></div>
                                <div class="kkf-inline-pair"><span class="kkf-inline-label">Birthday:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="pvDob"></span><span class="kkf-hint">(dd/mm/yy)</span></div>
                            </div>
                        </div>
                        <div class="kkf-personal-right">
                            <div class="kkf-inline-pair"><span class="kkf-inline-label">E-mail address:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="pvEmail"></span></div>
                            <div class="kkf-inline-pair"><span class="kkf-inline-label">Contact #:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="pvContact"></span></div>
                        </div>
                    </div>

                    <div class="kkf-section-title">II. Demographic Characteristics</div>
                    <p class="kkf-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

                    <div class="kkf-demo-grid">
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Civil Status</div>
                                <div class="kkf-demo-block-options">
                                    <div class="kkf-demo-options-2col">
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Single" disabled> Single</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Married" disabled> Married</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Widowed" disabled> Widowed</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Divorced" disabled> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Separated" disabled> Separated</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Annulled" disabled> Annulled</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Unknown" disabled> Unknown</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvCivilStatus" value="Live-in" disabled> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Age Group</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthAgeGroup" value="Child Youth (15-17 yrs old)" disabled> Child Youth (15-17 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthAgeGroup" value="Core Youth (18-24 yrs old)" disabled> Core Youth (18-24 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthAgeGroup" value="Young Adult (15-30 yrs old)" disabled> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Educational Background</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Elementary Level" disabled> Elementary Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Elementary Grad" disabled> Elementary Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="High School Level" disabled> High school level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="High School Grad" disabled> High school Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Vocational Grad" disabled> Vocational Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="College Level" disabled> College Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="College Grad" disabled> College Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Masters Level" disabled> Masters Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Masters Grad" disabled> Masters Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Doctorate Level" disabled> Doctorate Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvEducation" value="Doctorate Graduate" disabled> Doctorate Graduate</label>
                                </div>
                            </div>
                        </div>
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Classification</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="In School Youth" disabled> In school Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Out of School Youth" disabled> Out of School Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Working Youth" disabled> Working Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Youth w/ Specific Needs" disabled> Youth w/ Specific needs:</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Person w/ Disability" disabled> Person w/ Disability</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Children in Conflict w/ Law" disabled> Children In Conflict w/ Law</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvYouthClassification" value="Indigenous People" disabled> Indigenous People</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Work Status</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvWorkStatus" value="Employed" disabled> Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvWorkStatus" value="Unemployed" disabled> Unemployed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvWorkStatus" value="Self-Employed" disabled> Self-Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvWorkStatus" value="Currently looking for a Job" disabled> Currently looking for a Job</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvWorkStatus" value="Not Interested Looking for a Job" disabled> Not Interested Looking for a Job</label>
                                </div>
                            </div>
                            <div class="kkf-voter-section">
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvSKVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvSKVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingHistory" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingHistory" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingFrequency" value="1-2 Times" disabled> 1-2 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingFrequency" value="3-4 Times" disabled> 3-4 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingFrequency" value="5 and above" disabled> 5 and above</label>
                                    </div>
                                </div>
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvNatVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvNatVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvKKAssembly" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvKKAssembly" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If No, Why?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingReason" value="There was no KK Assembly" disabled> There was no KK Assembly</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvVotingReason" value="Not Interested to Attend" disabled> Not Interested to Attend</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kkf-footer-row">
                        <div class="kkf-footer-fb">
                            <span class="kkf-inline-label">FB Account:</span>
                            <span class="kkf-view-val kkf-uline kkf-uline-fb" id="pvFacebook"></span>
                        </div>
                        <div class="kkf-footer-chat">
                            <span class="kkf-inline-label">Willing to join the group chat?</span>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvGroupChat" value="Yes" disabled> Yes</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" data-view-field="pvGroupChat" value="No" disabled> No</label>
                        </div>
                    </div>

                    <div class="kkf-sig-section">
                        <div class="kkf-sig-container">
                            <div class="kkf-sig-overlay-wrap">
                                <svg class="kkf-sig-svg" id="pvSignatureSvg" viewBox="0 0 220 50" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <!-- signature path injected by JS -->
                                </svg>
                            </div>
                            <div class="kkf-sig-name-wrapper">
                                <span id="pvSignatureText" class="kkf-sig-name-input"></span>
                            </div>
                            <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal-backdrop upload-modal-backdrop" id="prevKabUploadModal" style="display:none;">
    <div class="modal-box upload-modal-box upload-modal-box--wide">
        <div class="modal-header">
            <h2 class="modal-title">Upload Previous Kabataan Records</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="prevKabUploadModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding:18px;">

            <!-- Drop zone — hidden once file is selected -->
            <div class="upload-zone" id="prevKabUploadZone">
                <div class="upload-zone-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div class="upload-zone-title">Click to upload or drag and drop</div>
                <div class="upload-zone-hint">Excel files only (.xlsx, .xls)</div>
                <div class="upload-zone-limit">Maximum file size: 10MB</div>
            </div>

            <input type="file" id="prevKabFileInput" class="upload-file-input" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">

            <!-- Selected file bar -->
            <div class="upload-selected-file" id="prevKabSelectedFile">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="prevKabSelectedFileName">filename.xlsx</span>
                <button type="button" class="upload-remove-file" id="prevKabRemoveFile">&times;</button>
            </div>

            <!-- Inline preview — shown after file selected -->
            <div id="prevKabInlinePreview" style="display:none;">
                <div class="preview-info-bar" style="margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Preview: <strong id="prevKabPreviewCount">0</strong> record(s) — review before saving.</span>
                </div>
                <div class="preview-table-wrap">
                    <table class="preview-table preview-table--full">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Birthday</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Youth Classification</th>
                                <th>Youth Age Group</th>
                                <th>Contact #</th>
                                <th>Home Address</th>
                                <th>Education</th>
                                <th>Work Status</th>
                                <th>Registered Voter?</th>
                                <th>Voted Last Election?</th>
                                <th>Attended KK Assembly?</th>
                                <th>If Yes, How Many Times?</th>
                                <th>Barangay</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>City/Municipality</th>
                            </tr>
                        </thead>
                        <tbody id="prevKabPreviewTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn outline-btn" data-modal-close>Cancel</button>
            <button type="button" class="btn primary-btn" id="prevKabConfirmUpload" disabled>Upload & Preview</button>
            <button type="button" class="btn primary-btn" id="prevKabConfirmSave" style="display:none;">Confirm &amp; Save</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/PreviousKabataan/assets/js/previous-kabataan.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
