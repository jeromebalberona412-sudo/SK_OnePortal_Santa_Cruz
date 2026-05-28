<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environmental Protection Programs - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/sports/sports_requests.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_application_from.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_report.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
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
            <h1 class="program-header-title">Environmental Protection</h1>
            <p class="program-header-description">Manage environmental programs, track participation, and promote sustainable practices to protect our environment for future generations.</p>
        </div>
    </section>

    @include('schedule_programs::partials.program-tabs', ['activeTab' => 'form', 'programType' => 'environmental'])

    <section class="saf-page-header-row">
        <div class="saf-page-header-text">
            <h1 class="schol-page-title">Program Schedule</h1>
            <p class="schol-page-subtitle">Create and schedule environmental protection programs for Kabataan members.</p>
        </div>
        <div class="saf-page-header-actions">
            <a href="{{ url('/reports/ckeditor?source=environmental') }}" class="schol-btn saf-report-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Make Report
            </a>
            <button type="button" class="schol-btn schol-btn-save" id="safOpenFormBtn" data-has-active="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Environmental Program
            </button>
        </div>
    </section>

    <!-- Active Program Card -->
    <div id="activeProgramCard" style="display:none;background:linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 4px 12px rgba(76,175,80,0.3);color:white;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:300px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div style="font-size:18px;font-weight:700;">Active Program</div>
                    <div id="activeProgramStatusBadge" style="display:inline-flex;align-items:center;padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:rgba(255,255,255,0.25);"></div>
                </div>
                <h3 id="activeProgramName" style="font-size:20px;font-weight:700;margin:0 0 8px;"></h3>
                <div id="activeProgramInfo" style="font-size:14px;opacity:0.95;line-height:1.6;"></div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="schol-btn" id="btnViewActiveProgram" style="font-size:12px;padding:8px 16px;background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View Details
                </button>
                <button type="button" class="schol-btn" id="btnEditActiveProgram" style="font-size:12px;padding:8px 16px;background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </button>
                <button type="button" class="schol-btn" id="btnCloseActiveProgram" style="font-size:12px;padding:8px 16px;background:rgba(239,68,68,0.9);color:white;border:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Close Program
                </button>
            </div>
        </div>
    </div>

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
                        <td colspan="8" class="saf-table-empty">No programs yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<!-- Program Creation Modal -->
<div class="schol-modal-overlay" id="scholarProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholarProgramBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Environmental Program
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
                    <div class="schol-field" style="grid-column:1/-1;">
                        <label for="programName">Program Name <span class="schol-req">*</span></label>
                        <input type="text" id="programName" class="schol-input" placeholder="Enter program name" maxlength="200" required>
                        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;"><span id="programNameCount">0</span>/200 characters</div>
                    </div>
                    <div class="schol-field">
                        <label for="programType">Program Type</label>
                        <input type="text" id="programType" class="schol-input" value="Environmental Protection" readonly style="background:#f9fafb;">
                    </div>
                    <div class="schol-field">
                        <label for="programCommittee">Committee <span class="schol-req">*</span></label>
                        <select id="programCommittee" class="schol-input" required>
                            <option value="">Select Committee</option>
                            <option value="Environment">Environment Committee</option>
                            <option value="Education">Education Committee</option>
                            <option value="Health">Health Committee</option>
                            <option value="Sports">Sports Committee</option>
                        </select>
                    </div>
                    <div class="schol-field">
                        <label for="participationQty">Participation Quantity</label>
                        <input type="number" id="participationQty" class="schol-input" placeholder="Number of participants" min="1" max="500">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Program Schedule
                </h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field">
                        <label for="schedStartDate">Start Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedStartDate" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="schedStartTime">Start Time <span class="schol-req">*</span></label>
                        <input type="time" id="schedStartTime" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="schedEndDate">End Date <span class="schol-req">*</span></label>
                        <input type="date" id="schedEndDate" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="schedEndTime">End Time <span class="schol-req">*</span></label>
                        <input type="time" id="schedEndTime" class="schol-input" required>
                    </div>
                    <div class="schol-field">
                        <label for="programStatus">Status</label>
                        <select id="programStatus" class="schol-input">
                            <option value="auto">Auto (Based on Date/Time)</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="upcoming">Upcoming</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="btnCancelProgram">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="btnSaveProgram">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Save Program
            </button>
        </div>
    </div>
</div>

<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/environmental_program.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
