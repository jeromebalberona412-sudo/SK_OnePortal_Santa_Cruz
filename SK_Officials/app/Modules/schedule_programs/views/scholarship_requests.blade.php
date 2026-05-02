<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Scholarships - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css'
    ])
</head>
<body>
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container">

    <!-- Page Header -->
    <section class="schol-page-header">
        <div class="schol-page-header-left">
            <h1 class="schol-page-title">Schedule Scholarships</h1>
            <p class="schol-page-subtitle">Manage scholarship programs and review submitted applications from Kabataan members.</p>
        </div>
        <div class="schol-header-actions">
            <button type="button" class="schol-btn schol-btn-save" id="btnMakeForm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Schedule a Scholarship Application Form
            </button>
        </div>
    </section>

    <!-- Stat Cards -->
    <div class="schol-stats-grid">
        <div class="schol-stat-card schol-stat-blue">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statTotal">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Total Applicants</span>
        </div>
        <div class="schol-stat-card schol-stat-yellow">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statPending">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Pending</span>
        </div>
        <div class="schol-stat-card schol-stat-green">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statApproved">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Approved</span>
        </div>
        <div class="schol-stat-card schol-stat-red">
            <div class="schol-stat-top">
                <span class="schol-stat-value" id="statRejected">0</span>
                <div class="schol-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </div>
            <span class="schol-stat-label">Rejected</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="schol-filters-row">
        <div class="schol-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="scholSearch" class="schol-search-input" placeholder="Search by name or school...">
        </div>
        <select id="scholStatusFilter" class="schol-filter-select">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
        </select>
    </div>

    <!-- Applications Table -->
    <div class="schol-table-card">
        <div class="schol-table-wrap">
            <table class="schol-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>School</th>
                        <th>Year / Level</th>
                        <th>Purpose</th>
                        <th>Requirements</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="scholTableBody"></tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- ══════════════════════════════════════════════════════════════
     Schedule a Scholarship Application Form Modal (SK Officials)
     ══════════════════════════════════════════════════════════════ -->
<div class="schol-modal-overlay" id="makeFormModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Schedule a Scholarship Application Form
            </h3>
            <button type="button" class="schol-modal-close" id="makeFormClose">&times;</button>
        </div>
        <div class="schol-modal-body" style="background:#f0f1f5;">

            <!-- Schedule Settings -->
            <div class="schol-schedule-card">
                <h4 class="schol-schedule-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Application Window Schedule
                </h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field">
                        <label for="schedOpenDate">Open Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedOpenDate" class="schol-input">
                    </div>
                    <div class="schol-field">
                        <label for="schedOpenTime">Open Time</label>
                        <input type="time" id="schedOpenTime" class="schol-input" value="08:00">
                    </div>
                    <div class="schol-field">
                        <label for="schedCloseDate">Close Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedCloseDate" class="schol-input">
                    </div>
                    <div class="schol-field">
                        <label for="schedCloseTime">Close Time</label>
                        <input type="time" id="schedCloseTime" class="schol-input" value="17:00">
                    </div>
                </div>
                <div class="schol-schedule-status" id="schedStatusBadge" style="display:none;"></div>
            </div>

            <!-- PDF Form Preview — exact match to PDF layout -->
            <div class="schol-pdf-form">

                <!-- Header: Logo left, Title center, Picture box right -->
                <div class="schol-pdf-header">
                    <img src="/images/barangay_logo.png" alt="Barangay Calios" class="schol-pdf-logo-img">
                    <h2 class="schol-pdf-title">SCHOLARSHIP APPLICATION FORM</h2>
                    <!-- Picture Here — clickable image uploader -->
                    <div class="schol-pdf-picture-upload" id="pictureUploadBox" title="Click to upload photo">
                        <img id="picturePreviewImg" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:2px;">
                        <div id="pictureUploadPlaceholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span>Picture<br>Here</span>
                        </div>
                        <input type="file" id="pictureUploadInput" accept="image/*" style="display:none;">
                    </div>
                </div>

                <!-- APPLICANT'S PERSONAL INFORMATION -->
                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">APPLICANT'S PERSONAL INFORMATION:</p>

                    <!-- Last Name / First Name / Middle Name inline -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Last Name:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                        <span class="schol-pdf-inline-label">First Name:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                        <span class="schol-pdf-inline-label">Middle Name:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                    </div>

                    <!-- DOB / Gender / Age / Contact inline -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Date of Birth:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-sm"></span>
                        <span class="schol-pdf-inline-label">Gender:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-sm"></span>
                        <span class="schol-pdf-inline-label">Age:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-xs"></span>
                        <span class="schol-pdf-inline-label">Contact No:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                    </div>

                    <!-- Complete Address full width -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Complete Address:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-full"></span>
                    </div>
                    <div class="schol-pdf-inline-row" style="margin-top:2px;">
                        <span class="schol-pdf-inline-line schol-pdf-line-full"></span>
                    </div>

                    <!-- Email Address -->
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Email Address:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-lg"></span>
                    </div>
                </div>

                <!-- ACADEMIC INFORMATION -->
                <div class="schol-pdf-section">
                    <p class="schol-pdf-inline-title">ACADEMIC INFORMATION:</p>

                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Name of School:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-full"></span>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">School Address:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-full"></span>
                    </div>
                    <div class="schol-pdf-inline-row">
                        <span class="schol-pdf-inline-label">Year/Grade Level:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                        <span class="schol-pdf-inline-label" style="margin-left:24px;">Program/Strand:</span>
                        <span class="schol-pdf-inline-line schol-pdf-line-md"></span>
                    </div>
                </div>

                <!-- SCHOLARSHIP INFO + SUBMITTED REQUIREMENTS side by side -->
                <div class="schol-pdf-section schol-pdf-bottom-section">
                    <div class="schol-pdf-bottom-left">
                        <p class="schol-pdf-inline-title">SCHOLARSHIP INFORMATION:</p>
                        <p class="schol-pdf-purpose-label">Purpose of Scholarship:</p>
                        <div class="schol-pdf-check-list">
                            <div class="schol-pdf-check-item"><span class="schol-pdf-checkbox"></span> Tuition Fees</div>
                            <div class="schol-pdf-check-item"><span class="schol-pdf-checkbox"></span> Books/Equipments</div>
                            <div class="schol-pdf-check-item"><span class="schol-pdf-checkbox"></span> Living Expenses</div>
                            <div class="schol-pdf-check-item">
                                <span class="schol-pdf-checkbox"></span> Others (Specify):
                                <span class="schol-pdf-inline-line" style="width:100px;display:inline-block;vertical-align:bottom;margin-left:4px;"></span>
                            </div>
                        </div>
                    </div>
                    <div class="schol-pdf-bottom-right">
                        <p class="schol-pdf-inline-title">SUBMITTED REQUIRMENTS: <span style="font-weight:400;font-size:10px;">Note: To be filled out by sk officials</span></p>

                        <!-- COR Upload -->
                        <div class="schol-upload-field" style="margin-top:10px;">
                            <label class="schol-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                COR – CERTIFIED TRUE COPY
                                <span class="schol-upload-hint">PDF / Image, max 10MB</span>
                            </label>
                            <div class="schol-upload-drop" id="corUploadDrop">
                                <input type="file" id="corUploadInput" accept=".pdf,image/*" style="display:none;">
                                <div class="schol-upload-drop-inner" id="corDropInner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                    <span>Click or drag file here</span>
                                </div>
                                <div class="schol-upload-preview" id="corPreview" style="display:none;"></div>
                            </div>
                        </div>

                        <!-- Photo ID Upload -->
                        <div class="schol-upload-field" style="margin-top:12px;">
                            <label class="schol-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                PHOTO COPY OF ID (FRONT AND BACK)
                                <span class="schol-upload-hint">PDF / Image, max 10MB</span>
                            </label>
                            <div class="schol-upload-drop" id="photoIdUploadDrop">
                                <input type="file" id="photoIdUploadInput" accept=".pdf,image/*" style="display:none;">
                                <div class="schol-upload-drop-inner" id="photoIdDropInner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                    <span>Click or drag file here</span>
                                </div>
                                <div class="schol-upload-preview" id="photoIdPreview" style="display:none;"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Signature -->
                <div class="schol-pdf-sig-section">
                    <div class="schol-pdf-sig-line"></div>
                    <p class="schol-pdf-sig-label">Signature over printed name</p>
                </div>

            </div>
        </div>

        <!-- Footer: Apply Scholar (preview/test) + Save Schedule — NO Close button -->
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-preview" style="margin-right:auto;" onclick="window.location.href='/scholarship/apply'">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Apply Scholar (Preview)
            </button>
            <button type="button" class="schol-btn schol-btn-save" id="btnSaveSchedule">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Save Schedule
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     View Application Modal — PDF layout, no Close button
     ══════════════════════════════════════════════════════════════ -->
<div class="schol-modal-overlay" id="scholViewModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholViewBox">
        <div class="schol-modal-header">
            <h3>Application Details — PDF View</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scholViewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="scholViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" id="scholViewBody" style="background:#f0f1f5;"></div>
        <!-- Footer: Approve + Reject only, no Close -->
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-approve" id="scholApproveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
            <button type="button" class="schol-btn schol-btn-reject" id="scholRejectBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ── -->
<div class="schol-modal-overlay" id="scholDeleteModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Delete Application</h3>
            <button type="button" class="schol-modal-close" id="scholDeleteClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;">Are you sure you want to delete this application? This action cannot be undone.</p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="scholDeleteCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="scholDeleteConfirm">Delete</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="schol-toast" id="scholToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="scholToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship_requests.js'
])
</body>
</html>
