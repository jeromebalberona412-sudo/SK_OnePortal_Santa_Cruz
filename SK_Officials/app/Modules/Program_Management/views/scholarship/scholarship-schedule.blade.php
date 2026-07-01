<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Published Scholarship Forms - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
        'app/Modules/GForm_Builder/assets/css/gform-builder.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship-schedule.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholar_report.css'
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

    @include('Program_Management::scholarship.partials.tabs', ['activeTab' => 'form'])

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

    <div class="saf-schedule-meta">
        <span class="saf-schedule-count">Total: <strong id="programCount">0</strong> programs</span>
    </div>

    <div class="saf-forms-table-card">
        <div class="saf-table-wrap">
            <table class="saf-forms-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Participants</th>
                        <th>Submission Start</th>
                        <th>Submission End</th>
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

{{-- Removed: @include('Program_Management::partials.scholar-form-builder-modal', ['formId' => $formId ?? '']) --}}
{{-- Using comprehensive modal below instead --}}

<!-- Comprehensive Scholarship Program Modal -->
<div class="schol-modal-overlay" id="scholarProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholarProgramBox">
        <div class="schol-modal-header">
            <h3 id="scholarProgramModalTitle">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Scholarship Program
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scholarProgramMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="scholarProgramClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body schol-program-builder-body">
            <nav class="sch-program-builder-tabs" id="schProgramBuilderTabs" aria-label="Scholarship program builder">
                <button type="button" class="sch-program-tab is-active" data-sch-tab="details">Program Details</button>
                <button type="button" class="sch-program-tab" data-sch-tab="application-form">Application Form</button>
                <button type="button" class="sch-program-tab" data-sch-tab="custom-questions">Custom Questions</button>
                <button type="button" class="sch-program-tab" data-sch-tab="quick-guidelines">Quick Guidelines</button>
                <button type="button" class="sch-program-tab" data-sch-tab="preview">Preview</button>
            </nav>

            <div class="sch-program-tab-panels">
                {{-- Program Details --}}
                <div class="sch-program-tab-panel is-active" data-sch-panel="details">
                    <div class="schol-schedule-card">
                        <h4 class="schol-schedule-title">Program Details</h4>
                        <div class="schol-schedule-grid schol-schedule-grid-2">
                            <div class="schol-field schol-field-full">
                                <label for="programName">Program Name <span class="schol-req">*</span></label>
                                <input type="text" id="programName" class="schol-input" readonly placeholder="Loading from ABYIP...">
                            </div>
                            <div class="schol-field">
                                <label for="schoolYear">School Year <span class="schol-req">*</span></label>
                                <input type="text" id="schoolYear" class="schol-input" readonly placeholder="From Programs duration">
                                <p class="schol-details-hint sch-school-year-hint">Auto-detected from the Equitable Access to Quality Education program duration.</p>
                            </div>
                            <div class="schol-field">
                                <label for="programSemester">Semester <span class="schol-req">*</span></label>
                                <select id="programSemester" class="schol-input" required>
                                    <option value="">Select semester</option>
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                </select>
                            </div>
                            <div class="schol-field">
                                <label for="participationQty">Maximum Beneficiaries <span class="schol-req">*</span></label>
                                <input type="text" id="participationQty" class="schol-input" placeholder="e.g. 100" inputmode="numeric" autocomplete="off" maxlength="4" required>
                            </div>
                            <div class="schol-field">
                                <label for="programStatus">Status <span class="schol-req">*</span></label>
                                <select id="programStatus" class="schol-input">
                                    <option value="open" selected>Open</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>

                        <div class="schol-field schol-field-full schol-target-level-group">
                            <label>Scholarship Level <span class="schol-req">*</span></label>
                            <div class="schol-target-level-row" role="group" aria-label="Scholarship level">
                                <label class="schol-level-option">
                                    <input type="checkbox" name="scholarshipTargetLevel" value="senior_high">
                                    <span>Senior High</span>
                                </label>
                                <label class="schol-level-option">
                                    <input type="checkbox" name="scholarshipTargetLevel" value="college">
                                    <span>College</span>
                                </label>
                                <label class="schol-level-option schol-level-both-option" id="schLevelBothBtn" role="button" tabindex="0" aria-pressed="false">
                                    <span>Both</span>
                                </label>
                            </div>
                            <p class="schol-details-hint sch-target-level-hint">Select Senior High, College, or click Both to include both levels. Open to In School Youth only.</p>
                        </div>

                        <div class="schol-field schol-field-full">
                            <label for="committeeHeadDisplay">Committee Head</label>
                            <input type="text" id="committeeHeadDisplay" class="schol-input" readonly placeholder="From Education Committee assignment">
                            <p class="schol-details-hint">Auto-filled from the Education Committee head assigned in Committees.</p>
                        </div>

                        <div class="schol-field schol-field-full schol-application-type-group">
                            <label>Application Type <span class="schol-req">*</span></label>
                            <div class="schol-radio-row">
                                <label class="schol-radio-label"><input type="radio" name="applicationType" value="new_only" checked> New Applicants Only</label>
                                <label class="schol-radio-label schol-radio-disabled"><input type="radio" name="applicationType" value="renewal_only" disabled> Renewal Only</label>
                                <label class="schol-radio-label schol-radio-disabled"><input type="radio" name="applicationType" value="both" disabled> New Applicants + Renewal</label>
                            </div>
                            <p class="schol-details-hint sch-application-type-hint">Renewal options unlock after at least one scholar has applied to this program.</p>
                        </div>
                    </div>

                    <div class="schol-schedule-card schol-details-card">
                        <h4 class="schol-schedule-title">Program Announcements</h4>
                        <p class="schol-details-hint">Requirement lists and important dates shown on the Kabataan apply page.</p>
                        <div id="schReqGroupsContainer" class="sch-req-groups"></div>
                        <button type="button" class="schol-btn schol-btn-secondary" id="schAddReqGroupBtn">+ Add Requirement Group</button>
                        <div class="schol-schedule-grid schol-schedule-grid-2" style="margin-top:16px;">
                            <div class="schol-field">
                                <label for="schSubmissionStart">Submission Period — Start <span class="schol-req">*</span></label>
                                <input type="date" id="schSubmissionStart" class="schol-input" required>
                            </div>
                            <div class="schol-field">
                                <label for="schSubmissionEnd">Submission Period — End <span class="schol-req">*</span></label>
                                <input type="date" id="schSubmissionEnd" class="schol-input" required>
                            </div>
                            <div class="schol-field">
                                <label for="schVerificationStart">Assessment/Verification — Start <span class="schol-req">*</span></label>
                                <input type="date" id="schVerificationStart" class="schol-input" required>
                            </div>
                            <div class="schol-field">
                                <label for="schVerificationEnd">Assessment/Verification — End <span class="schol-req">*</span></label>
                                <input type="date" id="schVerificationEnd" class="schol-input" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Application Form (locked template) --}}
                <div class="sch-program-tab-panel" data-sch-panel="application-form" hidden>
                    <div class="schol-system-template-banner">
                        <span class="schol-system-template-badge">SYSTEM TEMPLATE — LOCKED</span>
                        <p>Default scholarship application sections. KK Profile fields auto-fill Personal Information. System-required fields cannot be removed.</p>
                    </div>
                    <div id="scholSystemFieldsBuilder" class="schol-system-fields-builder"></div>
                </div>

                {{-- Custom Questions --}}
                <div class="sch-program-tab-panel" data-sch-panel="custom-questions" hidden>
                    <div class="schol-schedule-card">
                        <p class="schol-details-hint">Add optional questions after the default application form. Supported: Text, Textarea, Number, Radio, Checkbox, Dropdown, and File Upload (max 15).</p>
                        @include('GForm_Builder::partials.custom-questions-builder', [
                            'sectionTitle' => 'Custom Questions',
                            'hint' => 'Supported: Text, Textarea, Number, Radio, Checkbox, Dropdown, File Upload.',
                            'emptyMessage' => 'No custom questions yet. Click <strong>Add Question</strong> to add one.',
                        ])
                    </div>
                </div>

                {{-- Quick Guidelines --}}
                <div class="sch-program-tab-panel" data-sch-panel="quick-guidelines" hidden>
                    <div class="schol-schedule-card schol-quick-guidelines-card">
                        <h4 class="schol-schedule-title">Quick Guidelines</h4>
                        <p class="schol-details-hint">Bilingual quick guide for Kabataan applicants (English + Tagalog). Add up to 10 steps. Both English and Tagalog fields are required for each step you add.</p>
                        <div id="schQuickGuidelinesBuilder" class="sch-qg-builder"></div>
                        <button type="button" class="schol-btn schol-btn-secondary" id="schAddQuickGuidelineBtn">+ Add Step</button>
                    </div>
                </div>

                {{-- Preview --}}
                <div class="sch-program-tab-panel" data-sch-panel="preview" hidden>
                    <div class="schol-schedule-card">
                        <h4 class="schol-schedule-title">Applicant Preview</h4>
                        <p class="schol-details-hint">How Kabataan will see this scholarship program.</p>
                        <div id="schProgramPreviewPanel" class="sch-program-preview-panel">
                            <p class="sch-view-muted">Open this tab to generate a preview.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="btnCancelProgram">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="btnSaveProgram">
                <span class="schol-save-btn-content">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Program
                </span>
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
    <div class="schol-modal-box sk-type-confirm-modal saf-delete-modal" style="max-width:420px;padding:0;overflow:hidden;">
        <div class="sk-type-confirm-header">
            <h3>Delete Program</h3>
            <button type="button" class="schol-modal-close" id="deleteProgramClose" aria-label="Close" style="position:absolute;right:12px;top:10px;color:#fff;opacity:0.9;">&times;</button>
        </div>
        <div class="sk-type-confirm-body">
            <p class="sk-type-confirm-message">Are you sure you want to delete <strong id="deleteProgramName"></strong>?</p>
            <p class="sk-type-confirm-desc">This will permanently remove the scholarship program and its schedule. This action cannot be undone.</p>
            <label class="sk-type-confirm-label" for="deleteProgramConfirmText">Confirmation Required</label>
            <input type="text" id="deleteProgramConfirmText" class="sk-type-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
            <p class="sk-type-confirm-hint sk-type-confirm-hint-error" id="deleteProgramConfirmError" style="display:none;"></p>
        </div>
        <div class="sk-type-confirm-footer">
            <button type="button" class="sk-btn-cancel-confirm" id="deleteProgramCancel">Cancel</button>
            <button type="button" class="sk-btn-action-confirm is-disabled" id="deleteProgramConfirm" disabled>Delete Program</button>
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
        {{-- Close button intentionally removed from footer; use the × button in header --}}
    </div>
</div>

<div class="scholarship-toast" id="scholarshipToast" style="display:none;" role="status" aria-live="polite">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="scholarshipToastMsg"></span>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/layout/css/table-row-actions-menu.css',
    'app/Modules/layout/js/table-row-actions-menu.js',
    'app/Modules/Program_Management/assets/css/scholarship/scholarship-toast.css',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-toast.js',
    'app/Modules/GForm_Builder/assets/js/gform-builder.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-system-fields.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-view-shared.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholarship-schedule.js',
    'app/Modules/Program_Management/assets/js/scholarship/scholar_schedule.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
// Initialize form builder when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.GFormBuilder) {
        window.GFormBuilder.init({
            excludeTypes: ['date'],
            maxFileQuestions: 15,
            showToast: (msg) => {
                if (typeof window.showScholarshipToast === 'function') {
                    window.showScholarshipToast(msg);
                }
            }
        });
        console.log('Form builder initialized and button bound');
    }
    
    setupProgramFilter();
});

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
