<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Management - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/schedule-programs.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/abyip-pending-notice.css') }}">
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
                <h1 class="page-title">Program Management</h1>
                <p class="page-subtitle">Manage SK youth programs, surveys, scholarship, and sports.</p>
            </div>
        </section>

        @include('layout::partials.abyip-pending-notice', ['abyipGate' => $abyipGate ?? null])

        <!-- ── SK Programs Section ── -->
        <section class="committees-section">
            <div class="section-heading-row">
                <h2 class="section-title">SK Youth Development and Empowerment Programs</h2>
                <p class="section-subtitle">Select a program — survey committees use Forms, Results, and Analytics tabs</p>
            </div>

            <div class="committees-grid" id="programManagementGrid">
                @forelse($managementPrograms ?? [] as $program)
                    <a href="{{ url($program['href'] ?? '#') }}" class="committee-card" data-committee="{{ $program['committee_key'] ?? 'other' }}" style="text-decoration:none;color:inherit;">
                        <h3 class="committee-title">{{ $program['title'] ?? 'Untitled Program' }}</h3>
                        <ul class="committee-activities">
                            @forelse($program['activities'] ?? [] as $activity)
                                <li>{{ $activity }}</li>
                            @empty
                                <li>No activities listed</li>
                            @endforelse
                        </ul>
                        <p class="committee-head">SK Head: <span>{{ $program['sk_head_display'] ?? '' }}</span></p>
                    </a>
                @empty
                    @if(is_array($abyipGate ?? null) && ($abyipGate['status'] ?? null) === 'pending')
                        <div class="abyip-pending-empty" style="grid-column:1/-1;">
                            <strong>ABYIP Pending</strong>
                            {{ $abyipGate['pending_message'] ?? 'Pending — waiting for SK Federation President to approve your ABYIP.' }}
                        </div>
                    @else
                        <p class="section-subtitle" style="grid-column:1/-1;">No ABYIP programs found. Upload your ABYIP document first.</p>
                    @endif
                @endforelse
            </div>
            <script type="application/json" id="programManagementData">@json($managementPrograms ?? [])</script>
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
                    <a href="/scholarship-application-request" id="spScholarshipLink" class="sp-scholarship-link" style="display:none;">
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
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Program_Management/assets/js/schedule-programs.js'
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
