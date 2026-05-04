<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar Application - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship_application_form.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container">

    <!-- Page Header -->
    <section class="schol-page-header">
        <div class="schol-page-header-left">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <a href="/scholar-list" class="schol-btn schol-btn-back-list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Back to Scholar List
                </a>
            </div>
            <h1 class="schol-page-title">Scholar Application</h1>
            <p class="schol-page-subtitle">Manage scholarship programs and review submitted applications from Kabataan members.</p>
        </div>
        <div class="schol-header-actions">
            <button type="button" class="schol-btn schol-btn-outline" id="btnViewScheduleList">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Scheduled History
            </button>
            <button type="button" class="schol-btn schol-btn-save" id="btnMakeForm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Schedule a Scholarship Application Form
            </button>
        </div>
    </section>

    <!-- Scheduled Application Info -->
    <div id="scheduledAppInfo" style="display:none;background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:16px 20px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:300px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2c2c3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div style="font-size:15px;font-weight:700;color:#111827;">Application Window Schedule</div>
                    <div id="scheduleStatusBadge" style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;"></div>
                </div>
                <div id="scheduleInfoText" style="font-size:13px;color:#374151;line-height:1.6;"></div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="schol-btn" id="btnEditSchedule" style="font-size:12px;padding:7px 14px;background:#fbbf24;color:#78350f;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Schedule
                </button>
            </div>
        </div>
    </div>

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
        <input type="date" id="scholStartDate" class="schol-filter-input" placeholder="Start Date">
        <input type="date" id="scholEndDate" class="schol-filter-input" placeholder="End Date">
        <input type="time" id="scholStartTime" class="schol-filter-input" placeholder="Start Time">
        <input type="time" id="scholEndTime" class="schol-filter-input" placeholder="End Time">
        <div class="schol-search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="scholSearch" class="schol-search-input" placeholder="Search by name or school...">
        </div>
    </div>

    <!-- Applications Table -->
    <div class="schol-table-card">
        <div class="schol-table-wrap">
            <table class="schol-table">
                <thead>
                    <tr>
                        <th>FULL NAME<div style="font-size:9px;font-weight:400;color:rgba(255,255,255,0.75);text-transform:none;letter-spacing:0.02em;margin-top:2px;">LN, FN, MN, Suffix</div></th>
                        <th>School</th>
                        <th>Year / Level</th>
                        <th>Purpose</th>
                        <th>Requirements</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Time Submitted</th>
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
    <div class="schol-modal-box schol-modal-xl" id="makeFormBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Schedule a Scholarship Application Form
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="makeFormMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="makeFormClose" title="Close">&times;</button>
            </div>
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
                        <span class="schol-field-error" id="schedOpenDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                    <div class="schol-field">
                        <label for="schedOpenTime">Open Time</label>
                        <input type="time" id="schedOpenTime" class="schol-input" value="08:00">
                    </div>
                    <div class="schol-field">
                        <label for="schedCloseDate">Close Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedCloseDate" class="schol-input">
                        <span class="schol-field-error" id="schedCloseDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                    <div class="schol-field">
                        <label for="schedCloseTime">Close Time</label>
                        <input type="time" id="schedCloseTime" class="schol-input" value="17:00">
                    </div>
                    <div class="schol-field">
                        <label for="schedStatus">Status</label>
                        <select id="schedStatus" class="schol-input">
                            <option value="auto">Auto (Based on Date/Time)</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="upcoming">Upcoming</option>
                        </select>
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
                    <!-- Picture Here — static display only, not clickable -->
                    <div class="schol-pdf-picture-box">
                        <span>Picture<br>Here</span>
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

                        <!-- Static checkboxes like PDF -->
                        <div class="schol-pdf-check-list" style="margin-top:10px;">
                            <div class="schol-pdf-check-item">
                                <span class="schol-pdf-checkbox"></span> COR – CERTIFIED TRUE COPY
                            </div>
                            <div class="schol-pdf-check-item">
                                <span class="schol-pdf-checkbox"></span> PHOTO COPY OF ID (FRONT AND BACK)
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

        <!-- Footer: Save Schedule only -->
        <div class="schol-modal-footer">
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

<!-- ── Rejection Reason Modal ── -->
<div class="schol-modal-overlay" id="scholRejectReasonModal" style="display:none;">
    <div class="schol-modal-box schol-modal-md">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Rejection Reason</h3>
            <button type="button" class="schol-modal-close" id="scholRejectReasonClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:16px;">Please select the reason(s) for rejecting this application:</p>
            
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Incomplete Requirements" style="cursor:pointer;width:16px;height:16px;">
                    <span>Incomplete Requirements</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents" style="cursor:pointer;width:16px;height:16px;">
                    <span>Invalid Documents</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Does Not Meet Eligibility Criteria" style="cursor:pointer;width:16px;height:16px;">
                    <span>Does Not Meet Eligibility Criteria</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Duplicate Application" style="cursor:pointer;width:16px;height:16px;">
                    <span>Duplicate Application</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Late Submission" style="cursor:pointer;width:16px;height:16px;">
                    <span>Late Submission</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;color:#374151;">
                    <input type="checkbox" id="rejectReasonOtherCheckbox" value="Other" style="cursor:pointer;width:16px;height:16px;">
                    <span>Other</span>
                </label>
            </div>

            <div id="rejectReasonOtherField" style="display:none;">
                <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block;">Please specify:</label>
                <textarea id="rejectReasonOtherText" class="schol-input" rows="3" placeholder="Enter other reason..." style="width:100%;resize:vertical;"></textarea>
            </div>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="scholRejectReasonCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="scholRejectReasonConfirm">Confirm Rejection</button>
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

<!-- ── Schedule List Modal ── -->
<div class="schol-modal-overlay" id="scheduleListModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scheduleListBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Scheduled History List
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scheduleListMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="scheduleListClose">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body">
            <div class="schol-table-card">
                <div class="schol-table-wrap">
                    <table class="schol-table">
                        <thead>
                            <tr>
                                <th>Schedule ID</th>
                                <th>Open Date</th>
                                <th>Open Time</th>
                                <th>Close Date</th>
                                <th>Close Time</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleListTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="schol-modal-footer">
        </div>
    </div>
</div>

<!-- ── Activate Schedule Confirmation Modal ── -->
<div class="schol-modal-overlay" id="activateScheduleModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header">
            <h3>Activate Schedule</h3>
            <button type="button" class="schol-modal-close" id="activateScheduleClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;">Are you sure you want to activate this schedule? This will replace the current active schedule.</p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="activateScheduleCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="activateScheduleConfirm">Activate</button>
        </div>
    </div>
</div>

<!-- ── Delete Schedule Confirmation Modal ── -->
<div class="schol-modal-overlay" id="deleteScheduleModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Delete Schedule</h3>
            <button type="button" class="schol-modal-close" id="deleteScheduleClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;">Are you sure you want to delete this schedule? This action cannot be undone.</p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="deleteScheduleCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="deleteScheduleConfirm">Delete</button>
        </div>
    </div>
</div>

<!-- ── View Schedule Details Modal ── -->
<div class="schol-modal-overlay" id="viewScheduleModal" style="display:none;">
    <div class="schol-modal-box schol-modal-md">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Schedule Details
            </h3>
            <button type="button" class="schol-modal-close" id="viewScheduleClose">&times;</button>
        </div>
        <div class="schol-modal-body" id="viewScheduleBody">
            <!-- Schedule details will be populated here -->
        </div>
        <div class="schol-modal-footer">
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
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
// Inline date validation for Scholarship Requests
document.addEventListener('DOMContentLoaded', function() {
    const openDateInput = document.getElementById('schedOpenDate');
    const closeDateInput = document.getElementById('schedCloseDate');
    const openDateError = document.getElementById('schedOpenDateError');
    const closeDateError = document.getElementById('schedCloseDateError');
    const saveBtn = document.getElementById('btnSaveSchedule');

    function getTodayDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function validateOpenDate() {
        const value = openDateInput.value;
        const today = getTodayDate();
        
        if (!value) {
            openDateError.textContent = '';
            openDateError.style.display = 'none';
            return true;
        }
        
        if (value < today) {
            openDateError.textContent = 'Bawal yung past dates';
            openDateError.style.display = 'block';
            return false;
        }
        
        openDateError.textContent = '';
        openDateError.style.display = 'none';
        return true;
    }

    function validateCloseDate() {
        const openValue = openDateInput.value;
        const closeValue = closeDateInput.value;
        const today = getTodayDate();
        
        if (!closeValue) {
            closeDateError.textContent = '';
            closeDateError.style.display = 'none';
            return true;
        }
        
        if (closeValue < today) {
            closeDateError.textContent = 'Bawal yung past dates';
            closeDateError.style.display = 'block';
            return false;
        }
        
        if (openValue && closeValue && closeValue < openValue) {
            closeDateError.textContent = 'Close Date must be after Open Date';
            closeDateError.style.display = 'block';
            return false;
        }
        
        closeDateError.textContent = '';
        closeDateError.style.display = 'none';
        return true;
    }

    if (openDateInput) {
        openDateInput.addEventListener('input', function() {
            validateOpenDate();
            validateCloseDate();
        });
    }

    if (closeDateInput) {
        closeDateInput.addEventListener('input', validateCloseDate);
    }

    if (saveBtn) {
        const originalSaveHandler = saveBtn.onclick;
        saveBtn.onclick = function(e) {
            const isOpenValid = validateOpenDate();
            const isCloseValid = validateCloseDate();
            
            if (!isOpenValid || !isCloseValid) {
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
