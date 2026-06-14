<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sports Program Schedule - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/Program_Management/assets/css/sports/sports_requests.css',
        'app/Modules/GForm_Builder/assets/css/gform-builder.css',
        'app/Modules/Program_Management/assets/css/scholarship/scholarship-schedule.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/abyip-pending-notice.css') }}">
</head>
<body data-program-key="sports">
@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
<div class="schol-page-container saf-page-wrap">

    @include('layout::partials.abyip-pending-notice', ['abyipGate' => $abyipGate ?? null])

    @include('Program_Management::partials.program-page-top', [
        'activeTab' => 'form',
        'pageTitle' => 'Sports Program Schedule',
        'pageSubtitle' => 'Create and schedule sports development programs for Kabataan members.',
        'programType' => 'sports',
    ])

    <div class="saf-schedule-meta">
        <span class="saf-schedule-count">Total: <strong id="programCount">0</strong> programs</span>
    </div>

    <div class="saf-forms-table-card">
        <div class="saf-table-wrap">
            <table class="saf-forms-table">
                <thead>
                    <tr>
                        <th>Program Type</th>
                        <th>Participants</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="safFormsTableBody">
                    <tr>
                        <td colspan="6" class="saf-table-empty">Loading programs…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<div class="schol-modal-overlay" id="scholarProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-xl" id="scholarProgramBox">
        <div class="schol-modal-header">
            <h3 id="scholarProgramModalTitle">Create Sports Program</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="scholarProgramMaximize" title="Maximize">□</button>
                <button type="button" class="schol-modal-close" id="scholarProgramClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" style="background:#f0f1f5;max-height:calc(100vh - 180px);overflow-y:auto;">
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Program Information</h4>
                <div class="schol-schedule-grid">
                    <div class="schol-field schol-field-program-type">
                        <label for="programType">Program Type</label>
                        <input type="text" id="programType" class="schol-input schol-input-program-type" readonly placeholder="Loading from ABYIP...">
                    </div>
                    <div class="schol-field">
                        <label for="participationQty">Participation Quantity</label>
                        <input type="number" id="participationQty" class="schol-input" placeholder="Number of participants" min="0" max="500" step="1">
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Program Schedule</h4>
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

            <div class="schol-schedule-card">
                <h4 class="schol-schedule-title" style="margin-bottom:16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Application Form Builder
                </h4>
                @include('GForm_Builder::partials.announcement-field')
                @include('Program_Management::partials.kk-profiling-fields', ['programLabel' => 'sports program'])
                @include('GForm_Builder::partials.custom-questions-builder', ['hint' => 'Add custom questions for sports applicants.'])
            </div>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="btnCancelProgram">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="btnSaveProgram">Save Program</button>
        </div>
    </div>
</div>

<div class="schol-modal-overlay" id="viewProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-lg" id="viewProgramBox">
        <div class="schol-modal-header">
            <h3>Program Details</h3>
            <button type="button" class="schol-modal-close" id="viewProgramClose">&times;</button>
        </div>
        <div class="schol-modal-body" id="viewProgramBody"></div>
    </div>
</div>

<div class="schol-modal-overlay" id="deleteProgramModal" style="display:none;">
    <div class="schol-modal-box schol-modal-sm saf-delete-modal">
        <div class="schol-modal-header schol-modal-header-danger">
            <h3>Archive Program</h3>
            <button type="button" class="schol-modal-close" id="deleteProgramClose">&times;</button>
        </div>
        <div class="schol-modal-body">
            <p class="saf-delete-lead">This sports program will be moved to Archive.</p>
            <p class="saf-delete-detail">You can restore it within 30 days. After 30 days it will be permanently deleted.</p>
            <p class="saf-delete-name" id="deleteProgramName"></p>
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="deleteProgramCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-danger" id="deleteProgramConfirm">Archive Program</button>
        </div>
    </div>
</div>

<div class="sports-toast" id="safToast" style="display:none;"></div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/GForm_Builder/assets/js/gform-builder.js',
    'app/Modules/Program_Management/assets/js/sports/sports-schedule.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/shared/js/abyip-pending-notice.js') }}"></script>
<script>
    window.sportsAbyipGate = @json($abyipGate ?? null);
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.GFormBuilder) {
        window.GFormBuilder.init({
            showToast: (msg, type) => {
                const toast = document.getElementById('safToast');
                if (!toast) return;
                toast.textContent = msg;
                toast.style.display = 'flex';
                toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
                setTimeout(() => { toast.style.display = 'none'; }, 2800);
            }
        });
    }

    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    if (startDate) {
        startDate.setAttribute('min', new Date().toISOString().split('T')[0]);
        startDate.addEventListener('change', () => {
            if (endDate && startDate.value) {
                endDate.setAttribute('min', startDate.value);
                if (endDate.value && endDate.value < startDate.value) endDate.value = startDate.value;
            }
        });
    }
});
</script>
</body>
</html>
