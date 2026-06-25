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
        <div class="schol-modal-body" style="background:#f0f1f5;max-height:calc(100vh - 180px);overflow-y:auto;">
            
            <!-- Program Information Section -->
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Program Information
                </h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field schol-field-program-type">
                        <label for="programType">Program Type</label>
                        <input type="text" id="programType" class="schol-input schol-input-program-type" value="" readonly placeholder="Loading from ABYIP...">
                    </div>
                    <div class="schol-field">
                        <label for="programCommittee">Committee</label>
                        <input type="text" id="programCommittee" class="schol-input" value="" readonly placeholder="Loading from committees...">
                    </div>
                    <div class="schol-field">
                        <label for="participationQty">Participation Quantity</label>
                        <input type="number" id="participationQty" class="schol-input" placeholder="Number of participants" min="0" max="500" step="1">
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
                        <label for="schedEndDate">End Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedEndDate" class="schol-input" required>
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
                
                @include('GForm_Builder::partials.announcement-field')

                <div class="schol-schedule-card schol-details-card" style="margin-bottom:20px;">
                    <h4 class="schol-schedule-title">Program Announcements &amp; Requirements</h4>
                    <p class="schol-details-hint">These details appear on the Kabataan apply page — requirement lists and important dates.</p>
                    <div id="schReqGroupsContainer" class="sch-req-groups"></div>
                    <button type="button" class="schol-btn schol-btn-secondary" id="schAddReqGroupBtn" style="margin-bottom:20px;">
                        + Add Requirement Group
                    </button>
                    <div class="schol-schedule-grid">
                        <div class="schol-field">
                            <label for="schSubmissionStart">Submission Period — Start</label>
                            <input type="date" id="schSubmissionStart" class="schol-input">
                        </div>
                        <div class="schol-field">
                            <label for="schSubmissionEnd">Submission Period — End</label>
                            <input type="date" id="schSubmissionEnd" class="schol-input">
                        </div>
                        <div class="schol-field">
                            <label for="schVerificationStart">Assessment/Verification — Start</label>
                            <input type="date" id="schVerificationStart" class="schol-input">
                        </div>
                        <div class="schol-field">
                            <label for="schVerificationEnd">Assessment/Verification — End</label>
                            <input type="date" id="schVerificationEnd" class="schol-input">
                        </div>
                    </div>
                </div>

                <div id="scholSystemFieldsBuilder" class="schol-system-fields-builder"></div>
                
                <!-- KK Profiling Integration Section -->
                <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:20px;margin-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        <h5 style="margin:0;font-size:16px;font-weight:700;color:#0369a1;">1. Personal Information (KK Profiling)</h5>
                    </div>
                    <p style="font-size:13px;color:#475569;margin-bottom:16px;line-height:1.6;">
                        Select KK Profiling fields to automatically include in scholarship applications. Selected fields will be auto-filled from the applicant's KK Profile and displayed as read-only.
                    </p>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="last_name" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Last Name</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="first_name" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>First Name</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="middle_name" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Middle Name</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="suffix" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Suffix</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="birthday" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Birthday</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="age" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Age</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="sex" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Sex</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="civil_status" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Civil Status</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="contact_number" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Contact Number</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="email" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Email Address</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="region" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Region</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="province" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Province</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="city" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>City/Municipality</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="barangay" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Barangay</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="purok_zone" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Purok/Zone</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="youth_classification" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Youth Classification</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="youth_age_group" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Youth Age Group</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="education" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Educational Attainment</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="current_school" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Current School</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="course_strand" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Course / Strand</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="work_status" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Work Status</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="sk_voter" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Registered SK Voter</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                            <input type="checkbox" class="kk-profiling-field" value="sk_voted" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                            <span>Voted Last Election</span>
                        </label>
                    </div>
                    
                    <div style="margin-top:16px;display:flex;gap:12px;">
                        <button type="button" id="selectAllKKFields" style="padding:8px 16px;font-size:13px;font-weight:600;color:#0369a1;background:#e0f2fe;border:1px solid #0ea5e9;border-radius:6px;cursor:pointer;transition:all 0.2s;">Select All</button>
                        <button type="button" id="clearAllKKFields" style="padding:8px 16px;font-size:13px;font-weight:600;color:#64748b;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;transition:all 0.2s;">Clear All</button>
                    </div>
                </div>
                
                @include('GForm_Builder::partials.custom-questions-builder', [
                    'sectionTitle' => '5. Uploading of Requirements',
                    'hint' => 'Add PDF file upload requirements only. Each question becomes a document Kabataan must submit (max 5MB per file).',
                    'emptyMessage' => 'No requirements yet. Click <strong>Add Question</strong> and choose <strong>File Upload</strong>.',
                ])

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
        {{-- Close button intentionally removed from footer; use the × button in header --}}
    </div>
</div>

<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
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
