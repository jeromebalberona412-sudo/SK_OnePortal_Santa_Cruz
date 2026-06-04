<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Scholarship Forms - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sports/sports_requests.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship-schedule.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_report.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <style>
        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
        }
        .toggle-input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
            border-radius: 24px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        .toggle-input:checked + .toggle-slider {
            background-color: #10b981;
        }
        .toggle-input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }
    </style>
</head>
<body>
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">
    <a href="/schedule-programs" class="schol-back-top">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Schedule Programs
    </a>

    <!-- ── Program Header ── -->
    <section class="program-header-section">
        <div class="program-header-content">
            <h1 class="program-header-title">Equitable Access to Quality Education</h1>
            <p class="program-header-description">Manage scholarship programs, track applications, and evaluate scholar performance to ensure quality education access for all youth.</p>
        </div>
    </section>

    @include('schedule_programs::scholarship.partials.tabs', ['activeTab' => 'form'])

    <section class="saf-page-header-row">
        <div class="saf-page-header-text">
            <h1 class="schol-page-title">Scholarship Schedule</h1>
            <p class="schol-page-subtitle">Create Google Form–style questionnaires and schedule application windows for Kabataan scholarship applicants.</p>
        </div>
        <div class="saf-page-header-actions">
            <button type="button" class="schol-btn schol-btn-save" id="safOpenFormBtn" data-has-active="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Scholarship Program
            </button>
        </div>
    </section>

    <!-- Filter Section -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding:0 4px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:13px;font-weight:600;color:#374151;">Filter:</label>
            <select id="programFilter" class="schol-input" style="width:auto;min-width:150px;padding:6px 12px;font-size:13px;">
                <option value="all">All Programs</option>
                <option value="recent">Recent (Last 7 Days)</option>
                <option value="monthly">This Month</option>
                <option value="yearly">This Year</option>
            </select>
        </div>
        <div style="font-size:13px;color:#6b7280;">
            Total: <span id="programCount" style="font-weight:600;color:#111827;">0</span> programs
        </div>
    </div>

    <div class="saf-forms-table-card">
        <div class="saf-table-wrap">
            <table class="saf-forms-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Type</th>
                        <th>Committee</th>
                        <th>Participants</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="safFormsTableBody">
                    <tr>
                        <td colspan="8" class="saf-table-empty">No closed programs yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
</main>

{{-- Removed: @include('schedule_programs::partials.scholar-form-builder-modal', ['formId' => $formId ?? '']) --}}
{{-- Using comprehensive modal below instead --}}

<!-- Comprehensive Scholarship Program Modal -->
<div class="schol-modal-overlay" id="scholarProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl schol-modal-maximized" id="scholarProgramBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Scholarship Program
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scholarProgramMaximize" title="Restore Down" style="font-size:16px;padding:2px 8px;opacity:0.85;">⧉</button>
                <button type="button" class="schol-modal-close" id="scholarProgramClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" style="background:#f0f1f5;max-height:calc(100vh - 180px);overflow-y:auto;">
            
            <!-- Program Information Section -->
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Program Information
                </h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field" style="grid-column:1/-1;">
                        <label for="programName">Program Name <span class="schol-req">*</span></label>
                        <input type="text" id="programName" class="schol-input" placeholder="Enter program name" maxlength="200" required>
                        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="programNameCount">0</span>/200 characters</div>
                    </div>
                    <div class="schol-field schol-field-program-type">
                        <label for="programType">Program Type</label>
                        <input type="text" id="programType" class="schol-input schol-input-program-type" value="Equitable Access to Quality Education" readonly>
                    </div>
                    <div class="schol-field">
                        <label for="programCommittee">Committee</label>
                        <input type="text" id="programCommittee" class="schol-input" value="Education Committee" readonly>
                    </div>
                    <div class="schol-field">
                        <label for="participationQty">Participation Quantity</label>
                        <input type="number" id="participationQty" class="schol-input" placeholder="Number of participants" min="0" max="500" step="1">
                    </div>
                    <div class="schol-field" style="grid-column:1/-1;">
                        <label for="programVenue">Venue</label>
                        <input type="text" id="programVenue" class="schol-input" placeholder="Enter venue location" maxlength="500">
                        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="venueCount">0</span>/500 characters</div>
                    </div>
                    <div class="schol-field" style="grid-column:1/-1;">
                        <label for="programDescription">Description</label>
                        <textarea id="programDescription" class="schol-input" rows="3" placeholder="Enter program description" maxlength="500" style="resize:none;"></textarea>
                        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="descriptionCount">0</span>/500 characters</div>
                    </div>
                    <div class="schol-field" style="grid-column:1/-1;">
                        <label for="programTerms">Terms and Conditions</label>
                        <textarea id="programTerms" class="schol-input" rows="4" placeholder="Enter terms and conditions" maxlength="500" style="resize:none;"></textarea>
                        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="termsCount">0</span>/500 characters</div>
                    </div>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Application Window Schedule
                </h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field">
                        <label for="schedStartDate">Start Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedStartDate" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="schedStartTime">Start Time <span class="schol-req">*</span></label>
                        <select id="schedStartTime" class="schol-input" required>
                            <option value="">Select Time</option>
                            <option value="00:00">12:00 AM</option>
                            <option value="00:30">12:30 AM</option>
                            <option value="01:00">1:00 AM</option>
                            <option value="01:30">1:30 AM</option>
                            <option value="02:00">2:00 AM</option>
                            <option value="02:30">2:30 AM</option>
                            <option value="03:00">3:00 AM</option>
                            <option value="03:30">3:30 AM</option>
                            <option value="04:00">4:00 AM</option>
                            <option value="04:30">4:30 AM</option>
                            <option value="05:00">5:00 AM</option>
                            <option value="05:30">5:30 AM</option>
                            <option value="06:00">6:00 AM</option>
                            <option value="06:30">6:30 AM</option>
                            <option value="07:00">7:00 AM</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00" selected>8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                            <option value="20:30">8:30 PM</option>
                            <option value="21:00">9:00 PM</option>
                            <option value="21:30">9:30 PM</option>
                            <option value="22:00">10:00 PM</option>
                            <option value="22:30">10:30 PM</option>
                            <option value="23:00">11:00 PM</option>
                            <option value="23:30">11:30 PM</option>
                        </select>
                    </div>
                    <div class="schol-field">
                        <label for="schedEndDate">End Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedEndDate" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="schedEndTime">End Time <span class="schol-req">*</span></label>
                        <select id="schedEndTime" class="schol-input" required>
                            <option value="">Select Time</option>
                            <option value="00:00">12:00 AM</option>
                            <option value="00:30">12:30 AM</option>
                            <option value="01:00">1:00 AM</option>
                            <option value="01:30">1:30 AM</option>
                            <option value="02:00">2:00 AM</option>
                            <option value="02:30">2:30 AM</option>
                            <option value="03:00">3:00 AM</option>
                            <option value="03:30">3:30 AM</option>
                            <option value="04:00">4:00 AM</option>
                            <option value="04:30">4:30 AM</option>
                            <option value="05:00">5:00 AM</option>
                            <option value="05:30">5:30 AM</option>
                            <option value="06:00">6:00 AM</option>
                            <option value="06:30">6:30 AM</option>
                            <option value="07:00">7:00 AM</option>
                            <option value="07:30">7:30 AM</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="13:30">1:30 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="14:30">2:30 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="15:30">3:30 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="16:30">4:30 PM</option>
                            <option value="17:00" selected>5:00 PM</option>
                            <option value="17:30">5:30 PM</option>
                            <option value="18:00">6:00 PM</option>
                            <option value="18:30">6:30 PM</option>
                            <option value="19:00">7:00 PM</option>
                            <option value="19:30">7:30 PM</option>
                            <option value="20:00">8:00 PM</option>
                            <option value="20:30">8:30 PM</option>
                            <option value="21:00">9:00 PM</option>
                            <option value="21:30">9:30 PM</option>
                            <option value="22:00">10:00 PM</option>
                            <option value="22:30">10:30 PM</option>
                            <option value="23:00">11:00 PM</option>
                            <option value="23:30">11:30 PM</option>
                        </select>
                    </div>
                    <div class="schol-field">
                        <label for="programStatus">Status</label>
                        <select id="programStatus" class="schol-input">
                            <option value="open" selected>Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Application Form Section -->
            <div class="schol-schedule-card">
                <h4 class="schol-schedule-title" style="margin-bottom:16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Application Form Builder
                </h4>
                
                <!-- Announcement Section -->
                <div class="spfb-announcement-section">
                    <label class="spfb-announcement-label">Announcement <span style="color:#ef4444;">*</span></label>
                    <p class="spfb-announcement-hint">This message will be shown to Kabataan members when they open the application form.</p>
                    <textarea id="spfbAnnouncement" class="spfb-announcement-textarea" maxlength="500" placeholder="Enter announcement or instructions for applicants..."></textarea>
                    <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
                </div>

                <!-- Custom Questions Builder -->
                <div class="spfb-section-card spfb-section-builder">
                    <div class="spfb-section-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Custom Questions
                        <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
                    </div>
                    <p class="spfb-builder-hint">Add custom questions that Kabataan members will answer when applying.</p>

                    <div id="spfbQuestionList" class="spfb-question-list">
                        <div class="spfb-empty-state" id="spfbEmptyState">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <p>No questions yet. Click <strong>Add Question</strong> to start building your custom form.</p>
                        </div>
                    </div>

                    <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Question
                    </button>
                </div>

            </div>

        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="btnCancelProgram">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="btnSaveProgram">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Save Program
            </button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="sports-modal-overlay" id="safPreviewModal" style="display:none;">
    <div class="sports-modal-box" style="max-width:560px;">
        <div class="sports-modal-header">
            <h3>Form Preview</h3>
            <button type="button" class="sports-modal-close" id="safPreviewClose">&times;</button>
        </div>
        <div class="sports-modal-body" id="safPreviewBody"></div>
    </div>
</div>

<!-- Close Program Confirmation Modal -->
<div class="schol-modal-overlay" id="closeProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Close Program</h3>
            <button type="button" class="schol-modal-close" id="closeProgramClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p style="font-size:14px;color:#374151;line-height:1.6;margin-bottom:16px;">Are you sure you want to close this program? Once closed:</p>
            <ul style="font-size:13px;color:#6b7280;line-height:1.8;margin:0;padding-left:20px;">
                <li>Kabataan members will no longer be able to submit applications</li>
                <li>The program will be moved to history</li>
                <li>You can create a new program after closing this one</li>
            </ul>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="closeProgramCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="closeProgramConfirm">Close Program</button>
        </div>
    </div>
</div>

<!-- Schedule List Modal -->
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
    </div>
</div>

<!-- Activate Schedule Modal -->
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

<!-- Delete Schedule Modal -->
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

<!-- Delete Program Confirmation Modal -->
<div class="schol-modal-overlay" id="deleteProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm saf-delete-modal">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Delete Program</h3>
            <button type="button" class="schol-modal-close" id="deleteProgramClose" aria-label="Close">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p class="saf-delete-lead">Delete this program?</p>
            <p class="saf-delete-detail">This will permanently remove the scholarship program and its schedule. This action cannot be undone.</p>
            <p class="saf-delete-name" id="deleteProgramName"></p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="deleteProgramCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="deleteProgramConfirm">Delete Program</button>
        </div>
    </div>
</div>

<!-- View Program Details Modal -->
<div class="schol-modal-overlay" id="viewProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-lg" id="viewProgramBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Program Details
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="viewProgramMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="viewProgramClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" id="viewProgramBody" style="max-height:calc(100vh - 200px);overflow-y:auto;">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="viewProgramCloseBtn">Close</button>
        </div>
    </div>
</div>

<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/shared/spfb-form-builder.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholarship-view-shared.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholarship-schedule.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_schedule.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
// Initialize form builder when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.SpfbFormBuilder) {
        window.SpfbFormBuilder.init({
            showToast: (msg, type) => {
                const toast = document.getElementById('safToast');
                if (toast) {
                    toast.textContent = msg;
                    toast.style.display = 'flex';
                    toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
                    setTimeout(() => { toast.style.display = 'none'; }, 2800);
                }
            }
        });
        console.log('Form builder initialized and button bound');
    }
    
    // Setup character counters
    setupCharacterCounter('programName', 'programNameCount');
    setupCharacterCounter('programVenue', 'venueCount');
    setupCharacterCounter('programDescription', 'descriptionCount');
    setupCharacterCounter('programTerms', 'termsCount');
    
    // Setup date validation
    setupDateValidation();
    
    // Setup filter
    setupProgramFilter();
});

function setupCharacterCounter(inputId, counterId) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (input && counter) {
        input.addEventListener('input', () => {
            counter.textContent = input.value.length;
        });
    }
}

function setupDateValidation() {
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const startTime = document.getElementById('schedStartTime');
    const endTime = document.getElementById('schedEndTime');
    
    if (startDate) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDate.setAttribute('min', today);
        
        startDate.addEventListener('change', () => {
            // Update end date minimum to start date
            if (endDate && startDate.value) {
                endDate.setAttribute('min', startDate.value);
                
                // Validate if end date is before start date
                if (endDate.value && endDate.value < startDate.value) {
                    endDate.value = startDate.value;
                }
            }
        });
    }
    
    if (endDate) {
        endDate.addEventListener('change', () => {
            // Validate end date is not before start date
            if (startDate && startDate.value && endDate.value < startDate.value) {
                alert('End date cannot be before start date');
                endDate.value = startDate.value;
            }
            
            // If same day, validate time
            if (startDate && startDate.value === endDate.value) {
                validateSameDayTime();
            }
        });
    }
    
    if (startTime) {
        startTime.addEventListener('change', () => {
            if (startDate && endDate && startDate.value === endDate.value) {
                validateSameDayTime();
            }
        });
    }
    
    if (endTime) {
        endTime.addEventListener('change', () => {
            if (startDate && endDate && startDate.value === endDate.value) {
                validateSameDayTime();
            }
        });
    }
    
    function validateSameDayTime() {
        if (startTime && endTime && startTime.value && endTime.value) {
            if (endTime.value <= startTime.value) {
                alert('End time must be after start time on the same day');
                endTime.value = '';
            }
        }
    }
}

function setupProgramFilter() {
    const filterSelect = document.getElementById('programFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', () => {
            // This will be handled by the main JavaScript file
            console.log('Filter changed to:', filterSelect.value);
        });
    }
}

</script>
</body>
</html>
