<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabataan List - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Kabataan/assets/css/kabataan.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container kabataan-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Kabataan List</h1>
                <p class="page-subtitle">
                    Central registry of youth in the Purok / Sitio for SK programs and events.
                </p>
            </div>
        </section>

        <!-- ── Kabataan Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kabStatApproved">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Approved Kabataan Records</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kabStatPending">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Pending Kabataan Records</span>
            </div>
            <div class="stat-card stat-card-red">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kabStatRejected">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Rejected Kabataan Records</span>
            </div>
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="kabStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Kabataan Submissions</span>
            </div>
        </div>

        <section class="page-filters-section">
            <!-- ── Action Bar: Search + Add Kabataan ── -->
            <div class="table-action-bar">
                <div class="abyip-search-inline">
                    <label for="kabataanSearch" class="abyip-sr-only">Search kabataan records</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="kabataanSearch" class="abyip-filter-search-inline" placeholder="Search kabataan..." autocomplete="off">
                    </div>
                </div>
                <button type="button" class="btn primary-btn" id="addKabataanBtn">Add Kabataan</button>
            </div>
            <div class="filters-row">
                <div class="filter-item">
                    <label for="kabataanGenderFilter" class="filter-label">Sex</label>
                    <select id="kabataanGenderFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="kabataanPurok / SitioFilter" class="filter-label">Purok / Sitio</label>
                    <select id="kabataanPurok / SitioFilter" class="filter-select">
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
                    <label for="kabataanEducationFilter" class="filter-label">Highest Education</label>
                    <select id="kabataanEducationFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="No Formal Education">No Formal Education</option>
                        <option value="Elementary Level">Elementary Level</option>
                        <option value="Elementary Graduate">Elementary Graduate</option>
                        <option value="High School Level">High School Level</option>
                        <option value="High School Graduate">High School Graduate</option>
                        <option value="College Level">College Level</option>
                        <option value="College Graduate">College Graduate</option>
                        <option value="Vocational">Vocational</option>
                        <option value="Postgraduate">Postgraduate</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Registered Youth</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="kabataan-table">
                        <thead>
                            <tr>
                                <th>Respondent #</th>
                                <th>
                                    Full Name
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Sitio</th>
                                <th>Highest Education</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="kabataanTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Kabataan Modal -->
<div class="modal-backdrop kabataan-modal-backdrop" id="kabataanModal" style="display:none;">
    <div class="modal-box kabataan-modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="kabataanModalTitle">Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kabataanModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kabataan-modal-body">
            <!-- Add mode selector (only when adding) -->
            <div class="kabataan-add-mode-selector" id="kabataanAddModeSelector" style="display:none;">
                <p class="kabataan-mode-label">How do you want to add?</p>
                <div class="kabataan-mode-buttons">
                    <button type="button" class="btn kabataan-mode-btn active" data-mode="manual">Manual entry</button>
                    <button type="button" class="btn kabataan-mode-btn" data-mode="bulk">Batch Upload</button>
                </div>
            </div>

            <!-- Panel: Bulk upload -->
            <div class="kabataan-mode-panel kabataan-panel-bulk" id="kabataanPanelBulk" style="display:none;">

                {{-- Step 1: Upload Section --}}
                <div class="kab-import-section" id="kabImportUploadSection">

                    <p class="kab-import-section-desc">Upload Excel/CSV or paste from Google Sheets. First row must be headers. Supported: <strong>.xlsx, .xls, .csv</strong></p>

                    <details class="kab-import-columns-toggle">
                        <summary>Expected columns <span class="kab-import-col-count">(28)</span></summary>
                        <p class="kab-import-columns-list">Last Name, First Name, Middle Name, Suffix, Region, Province, City/Municipality, Barangay, Purok/Zone, Sex, Age, Birthday, Email, Contact Number, Civil Status, Youth Classification, Youth Age Group, Work Status, Educational Background, Registered SK Voter, Registered National Voter, Voting History, Voting Frequency, Voting Reason, Attended KK Assembly, Facebook Account, Willing to Join Group Chat, Signature</p>
                    </details>

                    <div class="kab-import-upload-box" id="kabImportDropZone">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p class="kab-import-upload-text">Drop file here or <label for="kabataanFileInput" class="kab-import-choose-link">browse</label></p>
                        <input type="file" id="kabataanFileInput" class="kab-import-file-hidden" accept=".csv,.xlsx,.xls">
                        <p class="kab-import-file-name" id="kabImportFileName">No file selected</p>
                    </div>

                    <div class="kab-import-divider"><span>or</span></div>

                    <textarea id="kabataanPasteInput" class="kab-import-paste-input" rows="3" placeholder="Paste from Google Sheets (CSV / tab-separated)…"></textarea>
                    <button type="button" class="btn kab-import-parse-btn" id="kabataanImportPasteBtn">Parse &amp; Preview</button>

                </div>

                {{-- Step 2: Preview Table (hidden until data is parsed) --}}
                <div class="kab-import-preview-section" id="kabImportPreviewSection" style="display:none;">
                    <div class="kab-import-preview-header">
                        <div class="kab-import-preview-title-row">
                            <h4 class="kab-import-section-title">📊 Import Preview</h4>
                            <div class="kab-import-summary" id="kabImportSummary">
                                <span class="kab-import-badge kab-badge-valid" id="kabImportBadgeValid">✅ 0 Valid</span>
                                <span class="kab-import-badge kab-badge-invalid" id="kabImportBadgeInvalid">⚠️ 0 Invalid</span>
                                <span class="kab-import-badge kab-badge-total" id="kabImportBadgeTotal">📋 0 Total</span>
                            </div>
                        </div>
                        <p class="kab-import-preview-note">Review all imported records below. Only <strong>Valid</strong> rows will be committed on Confirm Import.</p>
                    </div>

                    <div class="kab-import-table-wrap">
                        <table class="kab-import-table" id="kabImportPreviewTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Suffix</th>
                                    <th>Region</th>
                                    <th>Province</th>
                                    <th>City/Municipality</th>
                                    <th>Barangay</th>
                                    <th>Purok/Zone</th>
                                    <th>Sex</th>
                                    <th>Age</th>
                                    <th>Birthday</th>
                                    <th>Email</th>
                                    <th>Contact #</th>
                                    <th>Civil Status</th>
                                    <th>Youth Age Group</th>
                                    <th>Youth Classification</th>
                                    <th>Educational Background</th>
                                    <th>Work Status</th>
                                    <th>SK Voter</th>
                                    <th>Voted Last SK</th>
                                    <th>Voting Frequency</th>
                                    <th>Voting Reason</th>
                                    <th>National Voter</th>
                                    <th>KK Assembly</th>
                                    <th>FB Account</th>
                                    <th>Group Chat</th>
                                    <th>Signature</th>
                                    <th>Issues</th>
                                </tr>
                            </thead>
                            <tbody id="kabImportPreviewBody">
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="kab-import-pagination" id="kabImportPagination">
                        <span class="kab-import-page-info" id="kabImportPageInfo">Showing 1–10 of 0 rows</span>
                        <div class="kab-import-page-controls">
                            <button type="button" class="kab-import-page-btn" id="kabImportPrevBtn" disabled>Previous</button>
                            <div class="kab-import-page-numbers" id="kabImportPageNumbers"></div>
                            <button type="button" class="kab-import-page-btn" id="kabImportNextBtn" disabled>Next</button>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="kab-import-actions">
                        <button type="button" class="btn kab-import-cancel-btn" id="kabImportCancelBtn">
                            ✕ Cancel
                        </button>
                        <button type="button" class="btn kab-import-error-btn" id="kabImportErrorBtn" style="display:none;">
                            ⬇ Download Error Report
                        </button>
                        <button type="button" class="btn kab-import-confirm-btn" id="kabImportConfirmBtn">
                            ✔ Confirm Import
                        </button>
                    </div>
                </div>

            </div>

            <!-- View-only: paper form layout -->
            <div class="kabataan-view-details" id="kabataanViewDetails" style="display:none;">
                <div class="kabataan-form-scroll">

                    <div class="kkf-header">
                        <div class="kkf-header-title-col">
                            <div class="kkf-main-title">KK Survey Questionnaire</div>
                            <div class="kkf-main-hint">View-only record.</div>
                        </div>
                        <div class="kkf-header-fields-col">
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Respondent #:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="vRespondentNumber"></span>
                            </div>
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Date:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="vDate"></span>
                            </div>
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
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vLastName"></span><label class="kkf-col-label">Last Name</label></div>
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vFirstName"></span><label class="kkf-col-label">First Name</label></div>
                        <div class="kkf-name-col"><span class="kkf-view-val kkf-uline" id="vMiddleName"></span><label class="kkf-col-label">Middle Name</label></div>
                        <div class="kkf-name-col kkf-name-col--sm"><span class="kkf-view-val kkf-uline" id="vSuffix"></span><label class="kkf-col-label">Suffix</label></div>
                    </div>

                    <div class="kkf-row-label">Location:</div>
                    <div class="kkf-loc-row">
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vRegion"></span><label class="kkf-col-label">Region</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vProvince"></span><label class="kkf-col-label">Province</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vCity"></span><label class="kkf-col-label">City/Municipality</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vBarangay"></span><label class="kkf-col-label">Barangay</label></div>
                        <div class="kkf-loc-col"><span class="kkf-view-val kkf-uline" id="vPurokZone"></span><label class="kkf-col-label">Purok/Zone</label></div>
                    </div>

                    <div class="kkf-personal-row">
                        <div class="kkf-personal-left">
                            <div class="kkf-sex-block">
                                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Male" disabled> Male</label>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Female" disabled> Female</label>
                            </div>
                            <div class="kkf-age-dob-row">
                                <div class="kkf-inline-pair"><span class="kkf-inline-label">Age:</span><span class="kkf-view-val kkf-uline kkf-uline-short" id="vAge"></span></div>
                                <div class="kkf-inline-pair"><span class="kkf-inline-label">Birthday:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vDob"></span><span class="kkf-hint">(dd/mm/yy)</span></div>
                            </div>
                        </div>
                        <div class="kkf-personal-right">
                            <div class="kkf-inline-pair"><span class="kkf-inline-label">E-mail address:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vEmail"></span></div>
                            <div class="kkf-inline-pair"><span class="kkf-inline-label">Contact #:</span><span class="kkf-view-val kkf-uline kkf-uline-med" id="vContact"></span></div>
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
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Single" disabled> Single</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Married" disabled> Married</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Widowed" disabled> Widowed</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Divorced" disabled> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Separated" disabled> Separated</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Annulled" disabled> Annulled</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Unknown" disabled> Unknown</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Live-in" disabled> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Age Group</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Child Youth (15-17 yrs old)" disabled> Child Youth (15-17 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Core Youth (18-24 yrs old)" disabled> Core Youth (18-24 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Young Adult (15-30 yrs old)" disabled> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Educational Background</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Level" disabled> Elementary Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Grad" disabled> Elementary Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Level" disabled> High school level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Grad" disabled> High school Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Vocational Grad" disabled> Vocational Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Level" disabled> College Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Grad" disabled> College Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Level" disabled> Masters Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Grad" disabled> Masters Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Level" disabled> Doctorate Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Graduate" disabled> Doctorate Graduate</label>
                                </div>
                            </div>
                        </div>
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Classification</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="In School Youth" disabled> In school Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Out of School Youth" disabled> Out of School Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Working Youth" disabled> Working Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Youth w/ Specific Needs" disabled> Youth w/ Specific needs:</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Person w/ Disability" disabled> Person w/ Disability</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Children in Conflict w/ Law" disabled> Children In Conflict w/ Law</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Indigenous People" disabled> Indigenous People</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Work Status</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Employed" disabled> Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Unemployed" disabled> Unemployed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Self-Employed" disabled> Self-Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Currently looking for a Job" disabled> Currently looking for a Job</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Not Interested Looking for a Job" disabled> Not Interested Looking for a Job</label>
                                </div>
                            </div>
                            <div class="kkf-voter-section">
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="1-2 Times" disabled> 1-2 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="3-4 Times" disabled> 3-4 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="5 and above" disabled> 5 and above</label>
                                    </div>
                                </div>
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If No, Why?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="There was no KK Assembly" disabled> There was no KK Assembly</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="Not Interested to Attend" disabled> Not Interested to Attend</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kkf-footer-row">
                        <div class="kkf-footer-fb">
                            <span class="kkf-inline-label">FB Account:</span>
                            <span class="kkf-view-val kkf-uline kkf-uline-fb" id="vFacebook"></span>
                        </div>
                        <div class="kkf-footer-chat">
                            <span class="kkf-inline-label">Willing to join the group chat?</span>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="Yes" disabled> Yes</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="No" disabled> No</label>
                        </div>
                    </div>

                    <div class="kkf-sig-section">
                        <div class="kkf-sig-container">
                            <!-- Signature overlay (centered on top of name) -->
                            <div class="kkf-sig-overlay" id="vSignatureOverlay" style="display: none;">
                                <img id="vSignature" class="kkf-sig-overlay-img" alt="Signature">
                            </div>
                            
                            <!-- Name field with underline -->
                            <div class="kkf-sig-name-wrapper">
                                <span id="vSignatureText" class="kkf-sig-name-input" style="display: block;"></span>
                            </div>
                            
                            <!-- Label below the underline -->
                            <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Manual entry form -->
            <div class="kabataan-mode-panel kabataan-panel-manual" id="kabataanPanelManual">
                <div class="kabataan-form-scroll" id="kabataanFormScroll">

                    {{-- PAPER FORM HEADER --}}
                    <div class="kkf-header">
                        <div class="kkf-header-title-col">
                            <div class="kkf-main-title">ADD ENTRY MANUALLY</div>
                            <div class="kkf-main-hint">KK Survey Questionnaire &mdash; Fill in all applicable fields. Fields marked * are required.</div>
                        </div>
                        <div class="kkf-header-fields-col">
                            <div class="kkf-header-field">
                                <label class="kkf-hdr-label" for="kabataanRespondentNumber">Respondent #:</label>
                                <input type="text" id="kabataanRespondentNumber" class="kkf-hdr-input" placeholder="___________">
                            </div>
                            <div class="kkf-header-field">
                                <label class="kkf-hdr-label" for="kabataanDate">Date:</label>
                                <input type="date" id="kabataanDate" class="kkf-hdr-input">
                            </div>
                        </div>
                    </div>

                    {{-- TO THE RESPONDENT NOTICE --}}
                    <div class="kkf-notice-box">
                        <div class="kkf-notice-title">TO THE RESPONDENT:</div>
                        <p class="kkf-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking your time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="kkf-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="kkf-section-title">I. Profile</div>

                    {{-- Name Row --}}
                    <div class="kkf-row-label">Name of Respondent:</div>
                    <div class="kkf-name-row">
                        <div class="kkf-name-col">
                            <input type="text" id="kabataanLastName" class="kkf-uline" placeholder=" " required>
                            <label for="kabataanLastName" class="kkf-col-label">Last Name *</label>
                        </div>
                        <div class="kkf-name-col">
                            <input type="text" id="kabataanFirstName" class="kkf-uline" placeholder=" " required>
                            <label for="kabataanFirstName" class="kkf-col-label">First Name *</label>
                        </div>
                        <div class="kkf-name-col">
                            <input type="text" id="kabataanMiddleName" class="kkf-uline" placeholder=" " required>
                            <label for="kabataanMiddleName" class="kkf-col-label">Middle Name *</label>
                        </div>
                        <div class="kkf-name-col kkf-name-col--sm">
                            <select id="kabataanSuffix" class="kkf-uline kkf-uline-select">
                                <option value="">None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                            <label for="kabataanSuffix" class="kkf-col-label">Suffix</label>
                        </div>
                    </div>

                    {{-- Location Row --}}
                    <div class="kkf-row-label">Location:</div>
                    <div class="kkf-loc-row">
                        <div class="kkf-loc-col">
                            <input type="text" id="kabataanRegion" class="kkf-uline" placeholder=" ">
                            <label for="kabataanRegion" class="kkf-col-label">Region</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kabataanProvince" class="kkf-uline" placeholder=" ">
                            <label for="kabataanProvince" class="kkf-col-label">Province</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kabataanCity" class="kkf-uline" placeholder=" ">
                            <label for="kabataanCity" class="kkf-col-label">City/Municipality</label>
                        </div>
                        <div class="kkf-loc-col">
                            <input type="text" id="kabataanBarangay" class="kkf-uline" placeholder=" ">
                            <label for="kabataanBarangay" class="kkf-col-label">Barangay</label>
                        </div>
                        <div class="kkf-loc-col">
                            <select id="kabataanPurokZone" class="kkf-uline kkf-uline-select">
                                <option value="">Select</option>
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
                            <label for="kabataanPurokZone" class="kkf-col-label">Purok/Zone</label>
                        </div>
                    </div>

                    {{-- Personal Info Row: Sex+Age+Birthday | Email+Contact --}}
                    <div class="kkf-personal-row">
                        <div class="kkf-personal-left">
                            <div class="kkf-sex-block">
                                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanSexChk" value="Male" onchange="kkfSingleCheck(this,'kabataanSex')"> Male</label>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanSexChk" value="Female" onchange="kkfSingleCheck(this,'kabataanSex')"> Female</label>
                                <input type="hidden" id="kabataanSex" value="">
                            </div>
                            <div class="kkf-age-dob-row">
                                <div class="kkf-inline-pair">
                                    <label class="kkf-inline-label" for="kabataanAgeInput">Age: *</label>
                                    <input type="number" id="kabataanAgeInput" min="15" max="30" class="kkf-uline kkf-uline-short" placeholder=" ">
                                </div>
                                <div class="kkf-inline-pair">
                                    <label class="kkf-inline-label" for="kabataanDob">Birthday:</label>
                                    <input type="text" id="kabataanDob" class="kkf-uline kkf-uline-med" placeholder="dd/mm/yy">
                                    <span class="kkf-hint">(dd/mm/yy)</span>
                                </div>
                            </div>
                        </div>
                        <div class="kkf-personal-right">
                            <div class="kkf-inline-pair">
                                <label class="kkf-inline-label" for="kabataanEmail">E-mail address:</label>
                                <input type="email" id="kabataanEmail" class="kkf-uline kkf-uline-med" placeholder=" ">
                            </div>
                            <div class="kkf-inline-pair">
                                <label class="kkf-inline-label" for="kabataanContactInput">Contact #:</label>
                                <input type="text" id="kabataanContactInput" class="kkf-uline kkf-uline-med" placeholder=" ">
                            </div>
                        </div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="kkf-section-title">II. Demographic Characteristics</div>
                    <p class="kkf-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

                    <div class="kkf-demo-grid">
                        {{-- LEFT COLUMN --}}
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Civil Status</div>
                                <div class="kkf-demo-block-options">
                                    <div class="kkf-demo-options-2col">
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Single" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Single</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Married" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Married</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Widowed" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Widowed</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Divorced" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Separated" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Separated</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Annulled" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Annulled</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Unknown" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Unknown</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanCivilStatus" value="Live-in" onchange="kkfSingleCheck(this,'kabataanCivilStatus')"> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="kabataanCivilStatus" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Age Group</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthAgeGroup" value="Child Youth (15-17 yrs old)" onchange="kkfSingleCheck(this,'kabataanYouthAgeGroup')"> Child Youth (15-17 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthAgeGroup" value="Core Youth (18-24 yrs old)" onchange="kkfSingleCheck(this,'kabataanYouthAgeGroup')"> Core Youth (18-24 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthAgeGroup" value="Young Adult (15-30 yrs old)" onchange="kkfSingleCheck(this,'kabataanYouthAgeGroup')"> Young Adult (15-30 yrs old)</label>
                                </div>
                                <input type="hidden" id="kabataanYouthAgeGroup" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Educational Background</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Elementary Level" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Elementary Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Elementary Grad" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Elementary Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="High School Level" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> High school level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="High School Grad" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> High school Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Vocational Grad" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Vocational Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="College Level" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> College Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="College Grad" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> College Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Masters Level" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Masters Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Masters Grad" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Masters Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Doctorate Level" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Doctorate Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanEducationalBackground" value="Doctorate Graduate" onchange="kkfSingleCheck(this,'kabataanEducationalBackground')"> Doctorate Graduate</label>
                                </div>
                                <input type="hidden" id="kabataanEducationalBackground" value="">
                            </div>
                        </div>
                        {{-- RIGHT COLUMN --}}
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Classification</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="In School Youth" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> In school Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Out of School Youth" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Out of School Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Working Youth" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Working Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Youth w/ Specific Needs" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Youth w/ Specific needs:</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Person w/ Disability" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Person w/ Disability</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Children in Conflict w/ Law" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Children In Conflict w/ Law</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk" name="kabataanYouthClassification" value="Indigenous People" onchange="kkfSingleCheck(this,'kabataanYouthClassification')"> Indigenous People</label>
                                </div>
                                <input type="hidden" id="kabataanYouthClassification" value="">
                            </div>

                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Work Status</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWorkStatus" value="Employed" onchange="kkfSingleCheck(this,'kabataanWorkStatus')"> Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWorkStatus" value="Unemployed" onchange="kkfSingleCheck(this,'kabataanWorkStatus')"> Unemployed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWorkStatus" value="Self-Employed" onchange="kkfSingleCheck(this,'kabataanWorkStatus')"> Self-Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWorkStatus" value="Currently looking for a Job" onchange="kkfSingleCheck(this,'kabataanWorkStatus')"> Currently looking for a Job</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWorkStatus" value="Not Interested Looking for a Job" onchange="kkfSingleCheck(this,'kabataanWorkStatus')"> Not Interested Looking for a Job</label>
                                </div>
                                <input type="hidden" id="kabataanWorkStatus" value="">
                            </div>

                            {{-- Voter / Civic Participation inline rows --}}
                            <div class="kkf-voter-section">
                                {{-- Row 1: SK Voter | Vote Last SK | How many times --}}
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanRegisteredSKVoter" value="Yes" onchange="kkfSingleCheck(this,'kabataanRegisteredSKVoter')"> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanRegisteredSKVoter" value="No" onchange="kkfSingleCheck(this,'kabataanRegisteredSKVoter')"> No</label>
                                        <input type="hidden" id="kabataanRegisteredSKVoter" value="">
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingHistory" value="Yes" onchange="kkfSingleCheck(this,'kabataanVotingHistory')"> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingHistory" value="No" onchange="kkfSingleCheck(this,'kabataanVotingHistory')"> No</label>
                                        <input type="hidden" id="kabataanVotingHistory" value="">
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingFrequency" value="1-2 Times" onchange="kkfSingleCheck(this,'kabataanVotingFrequency')"> 1-2 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingFrequency" value="3-4 Times" onchange="kkfSingleCheck(this,'kabataanVotingFrequency')"> 3-4 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingFrequency" value="5 and above" onchange="kkfSingleCheck(this,'kabataanVotingFrequency')"> 5 and above</label>
                                        <input type="hidden" id="kabataanVotingFrequency" value="">
                                    </div>
                                </div>
                                {{-- Row 2: National Voter | KK Assembly | If No Why --}}
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanRegisteredNationalVoter" value="Yes" onchange="kkfSingleCheck(this,'kabataanRegisteredNationalVoter')"> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanRegisteredNationalVoter" value="No" onchange="kkfSingleCheck(this,'kabataanRegisteredNationalVoter')"> No</label>
                                        <input type="hidden" id="kabataanRegisteredNationalVoter" value="">
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanAttendedKKAssembly" value="Yes" onchange="kkfSingleCheck(this,'kabataanAttendedKKAssembly')"> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanAttendedKKAssembly" value="No" onchange="kkfSingleCheck(this,'kabataanAttendedKKAssembly')"> No</label>
                                        <input type="hidden" id="kabataanAttendedKKAssembly" value="">
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If No, Why?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingReason" value="There was no KK Assembly" onchange="kkfSingleCheck(this,'kabataanVotingReason')"> There was no KK Assembly</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanVotingReason" value="Not Interested to Attend" onchange="kkfSingleCheck(this,'kabataanVotingReason')"> Not Interested to Attend</label>
                                        <input type="hidden" id="kabataanVotingReason" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER: FB + Group Chat --}}
                    <div class="kkf-footer-row">
                        <div class="kkf-footer-fb">
                            <label class="kkf-inline-label" for="kabataanFacebookAccount">FB Account:</label>
                            <input type="text" id="kabataanFacebookAccount" class="kkf-uline kkf-uline-fb" placeholder=" ">
                        </div>
                        <div class="kkf-footer-chat">
                            <span class="kkf-inline-label">Willing to join the group chat?</span>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWillingToJoinGroupChat" value="Yes" onchange="kkfSingleCheck(this,'kabataanWillingToJoinGroupChat')"> Yes</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk" name="kabataanWillingToJoinGroupChat" value="No" onchange="kkfSingleCheck(this,'kabataanWillingToJoinGroupChat')"> No</label>
                            <input type="hidden" id="kabataanWillingToJoinGroupChat" value="">
                        </div>
                    </div>

                    {{-- SIGNATURE --}}
                    <div class="kkf-sig-section">
                        <div class="kkf-sig-container">
                            <!-- Signature overlay (centered on top of name) -->
                            <div class="kkf-sig-overlay" id="kabataanSignatureOverlay" style="display: none;">
                                <img id="kabataanSignaturePreview" class="kkf-sig-overlay-img" alt="Signature">
                            </div>
                            
                            <!-- Name field with underline -->
                            <div class="kkf-sig-name-wrapper">
                                <input type="text" id="kabataanSignatureName" placeholder="" readonly class="kkf-sig-name-input">
                            </div>
                            
                            <!-- Label below the underline -->
                            <div class="kkf-sig-label-bottom">Name and Signature of Participant</div>
                            
                            <!-- Sign button (shown when no signature) -->
                            <button type="button" class="kkf-sig-trigger-btn" id="kabataanSignatureTrigger" title="Sign here">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Sign
                            </button>
                            
                            <!-- Hidden signature input -->
                            <input type="hidden" id="kabataanSignature" value="">
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="modal-footer kabataan-modal-footer" style="display: flex; justify-content: flex-end; align-items: center;">
            <button type="button" class="btn primary-btn" id="kabataanSaveBtn">Save</button>

</content>
</invoke>
<invoke name="strReplace">
<parameter name="newStr">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kabataan View Modal -->
<div class="modal-backdrop kabataan-modal-backdrop" id="kabataanViewModal" style="display:none;">
    <div class="modal-box kabataan-modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="kabataanViewModalTitle">Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kabataanViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kabataan-modal-body">
            <div class="kabataan-form-scroll">

                    {{-- VIEW HEADER --}}
                    <div class="kkf-header">
                        <div class="kkf-header-title-col">
                            <div class="kkf-main-title">KK Survey Questionnaire</div>
                            <div class="kkf-main-hint">View-only record.</div>
                        </div>
                        <div class="kkf-header-fields-col">
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Respondent #:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="vRespondentNumber"></span>
                            </div>
                            <div class="kkf-header-field">
                                <span class="kkf-hdr-label">Date:</span>
                                <span class="kkf-view-val kkf-hdr-input" id="vDate"></span>
                            </div>
                        </div>
                    </div>

                    {{-- NOTICE BOX --}}
                    <div class="kkf-notice-box">
                        <div class="kkf-notice-title">TO THE RESPONDENT:</div>
                        <p class="kkf-notice-body">We are currently conducting a study that focuses on assessing the demographic information of the Katipunan ng Kabataan. We would like to ask your participation by taking your time to answer this questionnaire. Please read the questions carefully and answer them accurately.</p>
                        <p class="kkf-notice-confidential">REST ASSURED THAT ALL INFORMATION GATHERED FROM THIS STUDY WILL BE TREATED WITH UTMOST CONFIDENTIALITY.</p>
                    </div>

                    {{-- I. PROFILE --}}
                    <div class="kkf-section-title">I. Profile</div>

                    <div class="kkf-row-label">Name of Respondent:</div>
                    <div class="kkf-name-row">
                        <div class="kkf-name-col">
                            <span class="kkf-view-val kkf-uline" id="vLastName"></span>
                            <label class="kkf-col-label">Last Name</label>
                        </div>
                        <div class="kkf-name-col">
                            <span class="kkf-view-val kkf-uline" id="vFirstName"></span>
                            <label class="kkf-col-label">First Name</label>
                        </div>
                        <div class="kkf-name-col">
                            <span class="kkf-view-val kkf-uline" id="vMiddleName"></span>
                            <label class="kkf-col-label">Middle Name</label>
                        </div>
                        <div class="kkf-name-col kkf-name-col--sm">
                            <span class="kkf-view-val kkf-uline" id="vSuffix"></span>
                            <label class="kkf-col-label">Suffix</label>
                        </div>
                    </div>

                    <div class="kkf-row-label">Location:</div>
                    <div class="kkf-loc-row">
                        <div class="kkf-loc-col">
                            <span class="kkf-view-val kkf-uline" id="vRegion"></span>
                            <label class="kkf-col-label">Region</label>
                        </div>
                        <div class="kkf-loc-col">
                            <span class="kkf-view-val kkf-uline" id="vProvince"></span>
                            <label class="kkf-col-label">Province</label>
                        </div>
                        <div class="kkf-loc-col">
                            <span class="kkf-view-val kkf-uline" id="vCity"></span>
                            <label class="kkf-col-label">City/Municipality</label>
                        </div>
                        <div class="kkf-loc-col">
                            <span class="kkf-view-val kkf-uline" id="vBarangay"></span>
                            <label class="kkf-col-label">Barangay</label>
                        </div>
                        <div class="kkf-loc-col">
                            <span class="kkf-view-val kkf-uline" id="vPurokZone"></span>
                            <label class="kkf-col-label">Purok/Zone</label>
                        </div>
                    </div>

                    <div class="kkf-personal-row">
                        <div class="kkf-personal-left">
                            <div class="kkf-sex-block">
                                <span class="kkf-sex-label">Sex Assigned by Birth:</span>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Male" disabled> Male</label>
                                <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSex" value="Female" disabled> Female</label>
                            </div>
                            <div class="kkf-age-dob-row">
                                <div class="kkf-inline-pair">
                                    <span class="kkf-inline-label">Age:</span>
                                    <span class="kkf-view-val kkf-uline kkf-uline-short" id="vAge"></span>
                                </div>
                                <div class="kkf-inline-pair">
                                    <span class="kkf-inline-label">Birthday:</span>
                                    <span class="kkf-view-val kkf-uline kkf-uline-med" id="vDob"></span>
                                    <span class="kkf-hint">(dd/mm/yy)</span>
                                </div>
                            </div>
                        </div>
                        <div class="kkf-personal-right">
                            <div class="kkf-inline-pair">
                                <span class="kkf-inline-label">E-mail address:</span>
                                <span class="kkf-view-val kkf-uline kkf-uline-med" id="vEmail"></span>
                            </div>
                            <div class="kkf-inline-pair">
                                <span class="kkf-inline-label">Contact #:</span>
                                <span class="kkf-view-val kkf-uline kkf-uline-med" id="vContact"></span>
                            </div>
                        </div>
                    </div>

                    {{-- II. DEMOGRAPHIC CHARACTERISTICS --}}
                    <div class="kkf-section-title">II. Demographic Characteristics</div>
                    <p class="kkf-instruction">Please put a Check mark next to the word or Phrase that matches your response.</p>

                    <div class="kkf-demo-grid">
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Civil Status</div>
                                <div class="kkf-demo-block-options">
                                    <div class="kkf-demo-options-2col">
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Single" disabled> Single</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Married" disabled> Married</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Widowed" disabled> Widowed</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Divorced" disabled> Divorced</label>
                                        </div>
                                        <div>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Separated" disabled> Separated</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Annulled" disabled> Annulled</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Unknown" disabled> Unknown</label>
                                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vCivilStatus" value="Live-in" disabled> Live-in</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Age Group</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Child Youth (15-17 yrs old)" disabled> Child Youth (15-17 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Core Youth (18-24 yrs old)" disabled> Core Youth (18-24 yrs old)</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthAgeGroup" value="Young Adult (15-30 yrs old)" disabled> Young Adult (15-30 yrs old)</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Educational Background</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Level" disabled> Elementary Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Elementary Grad" disabled> Elementary Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Level" disabled> High school level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="High School Grad" disabled> High school Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Vocational Grad" disabled> Vocational Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Level" disabled> College Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="College Grad" disabled> College Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Level" disabled> Masters Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Masters Grad" disabled> Masters Grad</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Level" disabled> Doctorate Level</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vEducation" value="Doctorate Graduate" disabled> Doctorate Graduate</label>
                                </div>
                            </div>
                        </div>
                        <div class="kkf-demo-col">
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Youth Classification</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="In School Youth" disabled> In school Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Out of School Youth" disabled> Out of School Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Working Youth" disabled> Working Youth</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Youth w/ Specific Needs" disabled> Youth w/ Specific needs:</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Person w/ Disability" disabled> Person w/ Disability</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Children in Conflict w/ Law" disabled> Children In Conflict w/ Law</label>
                                    <label class="kkf-chk-lbl kkf-chk-indent"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vYouthClassification" value="Indigenous People" disabled> Indigenous People</label>
                                </div>
                            </div>
                            <div class="kkf-demo-block">
                                <div class="kkf-demo-block-label">Work Status</div>
                                <div class="kkf-demo-block-options">
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Employed" disabled> Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Unemployed" disabled> Unemployed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Self-Employed" disabled> Self-Employed</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Currently looking for a Job" disabled> Currently looking for a Job</label>
                                    <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vWorkStatus" value="Not Interested Looking for a Job" disabled> Not Interested Looking for a Job</label>
                                </div>
                            </div>
                            <div class="kkf-voter-section">
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered SK Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vSKVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Did you vote last SK?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingHistory" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If Yes, How many times?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="1-2 Times" disabled> 1-2 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="3-4 Times" disabled> 3-4 Times</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingFrequency" value="5 and above" disabled> 5 and above</label>
                                    </div>
                                </div>
                                <div class="kkf-voter-row">
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Registered National Voter?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vNatVoter" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">Have you attended a KK Assembly?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="Yes" disabled> Yes</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vKKAssembly" value="No" disabled> No</label>
                                    </div>
                                    <div class="kkf-voter-cell">
                                        <div class="kkf-voter-cell-label">If No, Why?</div>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="There was no KK Assembly" disabled> There was no KK Assembly</label>
                                        <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vVotingReason" value="Not Interested to Attend" disabled> Not Interested to Attend</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="kkf-footer-row">
                        <div class="kkf-footer-fb">
                            <span class="kkf-inline-label">FB Account:</span>
                            <span class="kkf-view-val kkf-uline kkf-uline-fb" id="vFacebook"></span>
                        </div>
                        <div class="kkf-footer-chat">
                            <span class="kkf-inline-label">Willing to join the group chat?</span>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="Yes" disabled> Yes</label>
                            <label class="kkf-chk-lbl"><input type="checkbox" class="kkf-sq-chk kkf-view-chk" data-view-field="vGroupChat" value="No" disabled> No</label>
                        </div>
                    </div>

                    {{-- SIGNATURE --}}
                    <div class="kkf-sig-row">
                        <span class="kkf-view-val kkf-uline kkf-uline-sig" id="vSignature"></span>
                    </div>

            </div>
        </div>
        <div class="modal-footer kabataan-modal-footer">
            <button type="button" class="btn primary-btn" id="kabataanViewModalClose">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop kabataan-delete-backdrop" id="kabataanDeleteModal" style="display:none;">
    <div class="kabataan-delete-box">
        <div class="kabataan-delete-header">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="kabataan-delete-icon">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6l-1 14H6L5 6"></path>
                <path d="M10 11v6"></path>
                <path d="M14 11v6"></path>
                <path d="M9 6V4h6v2"></path>
            </svg>
            <h2 class="kabataan-delete-title">Delete Record</h2>
        </div>
        <div class="kabataan-delete-body">
            <p class="kabataan-delete-message">Are you sure you want to delete</p>
            <p class="kabataan-delete-name" id="kabataanDeleteName"></p>
            <p class="kabataan-delete-warning">This action cannot be undone.</p>
        </div>
        <div class="kabataan-delete-footer">
            <button type="button" class="btn kabataan-cancel-btn" id="kabataanDeleteCancelBtn">Cancel</button>
            <button type="button" class="btn kabataan-confirm-delete-btn" id="kabataanDeleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Signature Pad Modal -->
<div class="signature-pad-overlay" id="signaturePadOverlay" style="display:none;">
    <div class="signature-pad-modal">
        <div class="signature-pad-header">
            <h3 class="signature-pad-title">✍️ Please Sign Here</h3>
            <button type="button" class="signature-pad-close" id="signaturePadClose" aria-label="Close">×</button>
        </div>
        <div class="signature-pad-body">
            <div class="signature-canvas-container">
                <canvas id="signaturePadCanvas" class="signature-canvas"></canvas>
                <div class="signature-canvas-placeholder" id="signatureCanvasPlaceholder">Sign here with your mouse or finger</div>
            </div>
        </div>
        <div class="signature-pad-footer">
            <button type="button" class="btn signature-btn-clear" id="signaturePadClear">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Clear
            </button>
            <button type="button" class="btn signature-btn-save" id="signaturePadSave">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Save Signature
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Kabataan/assets/js/kabataan.js'
])

</body>
</html>
