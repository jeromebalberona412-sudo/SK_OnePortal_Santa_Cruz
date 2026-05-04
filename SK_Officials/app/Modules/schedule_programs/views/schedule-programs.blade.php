<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Programs - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/schedule-programs.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <!-- ── Page Header ── -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Program Scheduling</h1>
                <p class="page-subtitle">Manage and track scheduled programs and activities.</p>
            </div>
        </section>

        <!-- ── Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatUpcoming">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Upcoming</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatOngoing">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Ongoing</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatCompleted">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Completed</span>
            </div>
            <div class="stat-card stat-card-red">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatCancelled">0</span>
                    <div class="stat-card-icon stat-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Cancelled</span>
            </div>
            <div class="stat-card stat-card-purple">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="spStatRescheduled">0</span>
                    <div class="stat-card-icon stat-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Rescheduled</span>
            </div>
        </div>

        <!-- ── SK Programs Section ── -->
        <section class="committees-section">
            <div class="section-heading-row">
                <h2 class="section-title">SK Youth Development and Empowerment Programs</h2>
                <p class="section-subtitle">Select a program to manage schedules and activities</p>
            </div>

            <div class="committees-grid">

                <!-- A. Equitable Access to Quality Education -->
                <a href="/scholar-list" class="committee-card" data-committee="education" style="text-decoration:none;color:inherit;">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </div>
                    <h3 class="committee-title">Equitable Access to Quality Education</h3>
                    <ul class="committee-activities">
                        <li>Support to ALS and RIC</li>
                        <li>150 Students for Educational Assistance</li>
                        <li>Support to Elementary and Daycare</li>
                    </ul>
                </a>

                <!-- B. Environmental Protection -->
                <div class="committee-card" data-committee="environment">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 2v20M2 12h20"/></svg>
                    </div>
                    <h3 class="committee-title">Environmental Protection</h3>
                    <ul class="committee-activities">
                        <li>Clean-Up Drive</li>
                        <li>Payroll for Laborer</li>
                        <li>Tree Planting</li>
                    </ul>
                </div>

                <!-- C. Disaster Risk Reduction and Resiliency -->
                <div class="committee-card" data-committee="disaster">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3 class="committee-title">Disaster Risk Reduction and Resiliency</h3>
                    <ul class="committee-activities">
                        <li>Training on Disaster Preparedness for Youth Volunteer Groups</li>
                        <li>Distribution of Relief Goods for KK Members</li>
                    </ul>
                </div>

                <!-- D. Youth Employment and Livelihood -->
                <div class="committee-card" data-committee="livelihood">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <h3 class="committee-title">Youth Employment and Livelihood</h3>
                    <ul class="committee-activities">
                        <li>Livelihood Training</li>
                        <li>Food and Other Supplies</li>
                    </ul>
                </div>

                <!-- E. Health -->
                <div class="committee-card" data-committee="health">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <h3 class="committee-title">Health</h3>
                    <ul class="committee-activities">
                        <li>Medicines / Medical Equipment</li>
                        <li>Campaigning Materials for Anti-Drugs (Leaflets, Posters, Tarpaulins)</li>
                    </ul>
                </div>

                <!-- F. Anti-Drug and Peace and Order -->
                <div class="committee-card" data-committee="anti-drug">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </div>
                    <h3 class="committee-title">Anti-Drug and Peace and Order</h3>
                    <ul class="committee-activities">
                        <li>Orientation for Anti-Drug and Physical Abuse</li>
                        <li>Foods and Accommodations</li>
                    </ul>
                </div>

                <!-- G. Gender Sensitivity -->
                <div class="committee-card" data-committee="gender">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="committee-title">Gender Sensitivity</h3>
                    <ul class="committee-activities">
                        <li>Orientation on GAD and VAWC</li>
                        <li>Foods and Accommodations</li>
                    </ul>
                </div>

                <!-- H. Feeding Program for KK Members -->
                <div class="committee-card" data-committee="feeding">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                    </div>
                    <h3 class="committee-title">Feeding Program for KK Members</h3>
                    <ul class="committee-activities">
                        <li>Improve health and physique of children</li>
                        <li>Youth and Children in the vicinity of Barangay</li>
                    </ul>
                </div>

                <!-- I. Sports Development -->
                <a href="/sport_list" class="committee-card" data-committee="sports" style="text-decoration:none;color:inherit;">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                    <h3 class="committee-title">Sports Development</h3>
                    <ul class="committee-activities">
                        <li>Supplies and Materials</li>
                        <li>Food and Accommodation</li>
                        <li>Officiating Fees</li>
                    </ul>
                </a>

                <!-- J. Other Programs -->
                <div class="committee-card" data-committee="other">
                    <div class="committee-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <h3 class="committee-title">Other Programs</h3>
                    <ul class="committee-activities">
                        <li>Katipunan ng Kabataan (KK) General Assembly</li>
                        <li>Barangay Day Celebration</li>
                        <li>Youth Week</li>
                    </ul>
                </div>

            </div>
        </section>

        <!-- Old Sports Development table section removed - now using Approved Sports Applications table only -->

        <!-- ── Activity Buttons Panel (shown after clicking a committee card) ── -->
        <section class="page-content-section" id="spActivityButtonsPanel" style="display:none;margin-top:20px;">
            <div class="section-heading-row" style="margin-bottom:12px;">
                <h2 class="section-title" id="spActivityPanelTitle"></h2>
                <p class="section-subtitle">Select an activity below to view its records.</p>
            </div>
            <div id="spActivityBtnGroup" style="display:flex;flex-wrap:wrap;gap:10px;"></div>
        </section>

        <!-- ── Passed Scholars Table (shown on click of education card) ── -->
        <section class="page-content-section" id="spPassedSection" style="display:none;margin-top:20px;">
            <div class="section-heading-row" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div>
                    <h2 class="section-title" id="spPassedSectionTitle">Passed Scholars</h2>
                    <p class="section-subtitle" id="spPassedSectionSubtitle">List of approved scholarship applicants with a Passed result.</p>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <button type="button" id="spExportCsvBtn" class="sp-scholarship-link" style="background:#22c55e;box-shadow:0 4px 12px rgba(34,197,94,0.3);border:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export to CSV
                    </button>
                    <a href="/scholarship" id="spScholarshipLink" class="sp-scholarship-link" style="display:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        Go to Scholarship Application List
                    </a>
                </div>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="sp-table">
                        <thead id="spPassedTableHead">
                            <tr>
                                <th>FULL NAME<div class="column-hint" style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                                <th>School</th>
                                <th>Year / Level</th>
                                <th>Program / Strand</th>
                                <th>Purpose</th>
                                <th>Date Approved</th>
                                <th>Result</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="spPassedTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ── Approved Sports Applications Table (shown on click of sports card) ── -->
        <section class="page-content-section" id="spSportsSection" style="display:none;margin-top:20px;">
            <div class="section-heading-row" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <div>
                    <h2 class="section-title" id="spSportsSectionTitle">Approved Sports Applications</h2>
                    <p class="section-subtitle" id="spSportsSectionSubtitle">List of approved sports program applications with Paid status.</p>
                </div>
                <a href="/sports-requests" class="sp-scholarship-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    Go to Sports Application Requests
                </a>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="sp-table">
                        <thead>
                            <tr>
                                <th>FULL NAME<div class="column-hint" style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                                <th>Program Name</th>
                                <th>Sport</th>
                                <th>Division</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="spSportsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- ── Passed Scholar View Modal ── -->
<div class="sp-modal-overlay" id="spPassedViewModal" style="display:none;">
    <div class="sp-modal-box" id="spPassedViewBox" style="max-width:680px;">
        <div class="sp-modal-header">
            <h3>Scholar Details — Application Form</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sp-modal-close" id="spPassedViewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sp-modal-close" id="spPassedViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sp-modal-body" id="spPassedViewBody" style="background:#f0f1f5;padding:20px;"></div>
    </div>
</div>

<!-- ── Sports Application View Modal ── -->
<div class="sp-modal-overlay" id="spSportsViewModal" style="display:none;">
    <div class="sp-modal-box" id="spSportsViewBox" style="max-width:680px;">
        <div class="sp-modal-header">
            <h3>Sports Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sp-modal-close" id="spSportsViewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sp-modal-close" id="spSportsViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sp-modal-body" id="spSportsViewBody" style="background:#f0f1f5;padding:20px;"></div>
    </div>
</div>

<!-- ── Add / Edit Schedule Modal ── -->
<div class="sp-modal-overlay" id="spFormOverlay" style="display:none;">
    <div class="sp-modal-box">
        <div class="sp-modal-header">
            <h3 id="spModalTitle">Add Schedule Program</h3>
            <button type="button" class="sp-modal-close" id="spModalClose" aria-label="Close">&times;</button>
        </div>
        <div class="sp-modal-body">
            <input type="hidden" id="spHiddenId">

            <div class="sp-form-grid">
                <div class="sp-form-field">
                    <label for="spProgram">Program <span class="required">*</span></label>
                    <select id="spProgram" class="sp-select">
                        <option value="">— Select Program —</option>
                        <option value="Youth Leadership Training 2026">Youth Leadership Training 2026</option>
                        <option value="Sports Fest 2026">Sports Fest 2026</option>
                        <option value="Clean and Green Campaign">Clean and Green Campaign</option>
                        <option value="Health and Wellness Drive">Health and Wellness Drive</option>
                        <option value="Skills Training Workshop">Skills Training Workshop</option>
                        <option value="Livelihood Skills Development">Livelihood Skills Development</option>
                        <option value="Community Health Program">Community Health Program</option>
                        <option value="Environmental Awareness Program">Environmental Awareness Program</option>
                    </select>
                </div>
                <div class="sp-form-field">
                    <label for="spActivityType">Activity Type <span class="required">*</span></label>
                    <select id="spActivityType" class="sp-select">
                        <option value="">— Select Type —</option>
                        <option value="Seminar">Seminar</option>
                        <option value="Clean-up">Clean-up</option>
                        <option value="Sports">Sports</option>
                        <option value="Feeding">Feeding</option>
                    </select>
                </div>
                <div class="sp-form-field">
                    <label for="spDate">Date Planned <span class="required">*</span></label>
                    <input type="date" id="spDate" class="sp-input">
                    <span class="sp-field-error" id="spDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="sp-form-field">
                    <label for="spStartTime">Start Time <span class="required">*</span></label>
                    <input type="time" id="spStartTime" class="sp-input">
                </div>
                <div class="sp-form-field">
                    <label for="spEndTime">End Time</label>
                    <input type="time" id="spEndTime" class="sp-input">
                </div>
                <div class="sp-form-field">
                    <label for="spVenue">Venue <span class="required">*</span></label>
                    <input type="text" id="spVenue" class="sp-input" placeholder="e.g. Barangay Hall" maxlength="150">
                    <span class="sp-field-error" id="spVenueError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="sp-form-field">
                    <label for="spOfficials">Assigned Officials</label>
                    <input type="text" id="spOfficials" class="sp-input" placeholder="e.g. Juan dela Cruz, Maria Santos" maxlength="200">
                    <span class="sp-field-error" id="spOfficialsError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>
                <div class="sp-form-field">
                    <label for="spParticipants">Expected Participants</label>
                    <input type="number" id="spParticipants" class="sp-input" placeholder="e.g. 50" min="0">
                </div>
                <div class="sp-form-field">
                    <label for="spStatus">Status <span class="required">*</span></label>
                    <select id="spStatus" class="sp-select">
                        <option value="Upcoming">Upcoming</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Rescheduled">Rescheduled</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="sp-modal-footer">
            <button type="button" class="btn btn-clear" id="spBtnClear">Clear</button>
            <button type="button" class="btn btn-save" id="spBtnSave">Save</button>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation ── -->
<div class="sp-modal-overlay" id="spDeleteOverlay" style="display:none;">
    <div class="sp-delete-box">
        <div class="sp-delete-header">
            <h3>Delete Schedule</h3>
            <button type="button" class="sp-modal-close" id="spDeleteCancel" aria-label="Close">&times;</button>
        </div>
        <div class="sp-delete-body">
            Are you sure you want to delete this schedule? This action cannot be undone.
        </div>
        <div class="sp-delete-footer">
            <button type="button" class="btn btn-clear" id="spDeleteCancelFooter">Cancel</button>
            <button type="button" class="btn btn-danger" id="spDeleteConfirm">Delete</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/schedule-programs.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
// Inline date validation for Schedule Programs
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('spDate');
    const dateError = document.getElementById('spDateError');
    const saveBtn = document.getElementById('spBtnSave');

    function getTodayDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function validateDate() {
        const value = dateInput.value;
        const today = getTodayDate();
        
        if (!value) {
            dateError.textContent = '';
            dateError.style.display = 'none';
            return true;
        }
        
        if (value < today) {
            dateError.textContent = 'Bawal yung past dates';
            dateError.style.display = 'block';
            return false;
        }
        
        dateError.textContent = '';
        dateError.style.display = 'none';
        return true;
    }

    if (dateInput) {
        dateInput.addEventListener('input', validateDate);
    }

    if (saveBtn) {
        const originalSaveHandler = saveBtn.onclick;
        saveBtn.onclick = function(e) {
            const isValid = validateDate();
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            if (originalSaveHandler) {
                return originalSaveHandler.call(this, e);
            }
        };
    }
});
</script>
</body>
</html>
