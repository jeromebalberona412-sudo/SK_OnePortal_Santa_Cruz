<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $browserTitle ?? 'Program Schedule - SK Officials Portal' }}</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_application_from.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-program-key="{{ $programType ?? 'general' }}">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">

    @include('schedule_programs::partials.program-page-top', [
        'activeTab' => 'schedule',
        'pageTitle' => 'Program Schedule',
        'pageSubtitle' => $scheduleSubtitle ?? 'Create and schedule program activities for Kabataan members.',
        'programType' => $programType ?? 'general',
        'programTitle' => $programTitle ?? 'Program Management',
        'programDescription' => $programDescription ?? 'Manage programs, track applications, and evaluate participants.',
    ])

    <!-- Filter Section -->
    <div id="activeProgramCard" style="display:none;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border-radius:12px;padding:24px;margin-bottom:24px;box-shadow:0 4px 12px rgba(102,126,234,0.3);color:white;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:300px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
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
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding:0 4px;flex-wrap:wrap;gap:12px;">
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

    <!-- Programs Table -->
    <div class="saf-forms-table-card">
        <div class="saf-table-wrap">
            <table class="saf-forms-table">
                <thead>
                    <tr>
                        <th>Program Name</th>
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
                        <td colspan="7" class="saf-table-empty">No programs yet. Click <strong>Create Program</strong> to get started.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- Program Creation/Edit Modal -->
<div class="schol-modal-overlay" id="scholarProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholarProgramBox">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                <span id="modalTitle">Create Program</span>
            </h3>
            <button type="button" class="schol-modal-close" id="scholarProgramClose" title="Close">&times;</button>
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
                        <label for="programCommittee">Committee <span class="schol-req">*</span></label>
                        <select id="programCommittee" class="schol-input" required>
                            <option value="">Select Committee</option>
                            <option value="Education">Education Committee</option>
                            <option value="Health">Health Committee</option>
                            <option value="Sports">Sports Committee</option>
                            <option value="Environment">Environment Committee</option>
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
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Save Program
            </button>
        </div>
    </div>
</div>

<!-- View Program Details Modal -->
<div class="schol-modal-overlay" id="viewProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-lg">
        <div class="schol-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Program Details
            </h3>
            <button type="button" class="schol-modal-close" id="viewProgramClose">&times;</button>
        </div>
        <div class="schol-modal-body" id="viewProgramBody" style="max-height:calc(100vh - 200px);overflow-y:auto;">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="viewProgramCloseBtn">Close</button>
        </div>
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
                <li>Kabataan members will no longer be able to participate</li>
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

<!-- Toast -->
<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_application_from.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_schedule.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script>
// Initialize character counters
document.addEventListener('DOMContentLoaded', () => {
    const counters = [
        { input: 'programName', counter: 'programNameCount' },
        { input: 'programVenue', counter: 'venueCount' },
        { input: 'programDescription', counter: 'descriptionCount' }
    ];
    
    counters.forEach(({ input, counter }) => {
        const inputEl = document.getElementById(input);
        const counterEl = document.getElementById(counter);
        if (inputEl && counterEl) {
            inputEl.addEventListener('input', () => {
                counterEl.textContent = inputEl.value.length;
            });
        }
    });
    
    // Date validation
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    if (startDate) {
        const today = new Date().toISOString().split('T')[0];
        startDate.setAttribute('min', today);
        startDate.addEventListener('change', () => {
            if (endDate && startDate.value) {
                endDate.setAttribute('min', startDate.value);
                if (endDate.value && endDate.value < startDate.value) {
                    endDate.value = startDate.value;
                }
            }
        });
    }
});
</script>
</body>
</html>
