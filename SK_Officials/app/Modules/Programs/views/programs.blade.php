<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Programs/assets/css/programs.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/abyip-pending-notice.css') }}">
</head>
<body>

@include('loading')
<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="page-container programs-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Programs</h1>
                <p class="page-subtitle">
                    Plan and track major SK initiatives and timelines.
                </p>
            </div>
        </section>

        @include('layout::partials.abyip-pending-notice', ['abyipGate' => $abyipGate ?? null])

        <!-- ── Programs Stat Cards ── -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="progStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Programs</span>
            </div>
            <div class="stat-card stat-card-teal">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="progStatOngoing">0</span>
                    <div class="stat-card-icon stat-icon-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Ongoing</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="progStatCompleted">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Completed</span>
            </div>
        </div>

        <section class="page-filters-section">
            <div class="filters-row">
                <!-- Committee -->
                <div class="filter-item">
                    <label for="programCommitteeFilter" class="filter-label">Committee</label>
                    <select id="programCommitteeFilter" class="filter-select">
                        <option value="">All committees</option>
                        <option value="Equitable Access to Quality Education">Equitable Access to Quality Education</option>
                        <option value="Environmental Protection">Environmental Protection</option>
                        <option value="Disaster Risk Reduction and Resiliency">Disaster Risk Reduction and Resiliency</option>
                        <option value="Youth Employment and Livelihood">Youth Employment and Livelihood</option>
                        <option value="Health">Health</option>
                        <option value="Anti-Drug and Peace and Order">Anti-Drug and Peace and Order</option>
                        <option value="Feeding Program for KK Members">Feeding Program for KK Members</option>
                        <option value="Sports Development">Sports Development</option>
                        <option value="Other Programs">Other Programs</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="filter-item">
                    <label for="programStatusFilter" class="filter-label">Status</label>
                    <select id="programStatusFilter" class="filter-select">
                        <option value="">All statuses</option>
                        <option value="planned">Planned</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <!-- Search (right side) -->
                <div class="filter-item filter-item-search">
                    <label for="programSearch" class="filter-label">Search</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="programSearch" class="abyip-filter-search-inline" placeholder="Search by title or description" autocomplete="off">
                    </div>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="content-wrapper content-wrapper--full">
                <div class="main-content-area">
                    <div class="table-wrapper">
                        <table class="programs-table">
                            <thead>
                                <tr>
                                    <th>Program Title</th>
                                    <th>Description</th>
                                    <th>Committee</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="programTableBody">
                                <!-- Programs rendered by programs.js (UI-only, mock data) -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Program View Modal -->
<div class="modal-backdrop" id="programViewModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Program Summary</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-view-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label>Program Type / Committee</label>
                <input type="text" id="viewProgramType" readonly>
            </div>
            <div class="modal-field">
                <label>Program Title</label>
                <input type="text" id="viewProgramTitle" readonly>
            </div>
            <div class="modal-field">
                <label>Description</label>
                <textarea id="viewProgramName" readonly rows="4" style="width:100%;border-radius:10px;border:1px solid #d1d5db;padding:8px 10px;font-size:13px;font-family:inherit;color:#374151;background:#f9fafb;resize:none;line-height:1.6;"></textarea>
            </div>
            <div class="modal-field">
                <label>Duration</label>
                <input type="text" id="viewProgramDuration" readonly>
            </div>
            <div class="modal-field">
                <label>Status</label>
                <input type="text" id="viewProgramStatus" readonly>
            </div>
        </div>
        <!-- Footer intentionally removed (use top-right close button) -->
    </div>
</div>

<!-- Edit Duration Modal -->
<div class="modal-backdrop" id="editDurationModal" style="display:none;">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Duration</h2>
            <button type="button" class="modal-close" id="editDurationClose" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" style="padding-bottom:16px;">
            <input type="hidden" id="editDurationIndex">
            <div class="modal-field">
                <label for="editStartDate">Start Date <span style="color:#ef4444;">*</span></label>
                <input type="date" id="editStartDate" class="modal-date-input">
                <span class="prog-field-error" id="editStartDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
            </div>
            <div class="modal-field">
                <label for="editEndDate">End Date <span style="color:#ef4444;">*</span></label>
                <input type="date" id="editEndDate" class="modal-date-input">
                <span class="prog-field-error" id="editEndDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" style="background:#fff;color:#374151;border:1.5px solid #d1d5db;" id="editDurationCancel">Cancel</button>
            <button type="button" class="btn primary-btn" id="editDurationSave">Update</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Programs/assets/js/programs.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/shared/js/abyip-pending-notice.js') }}"></script>
<script>
    window.programsAbyipGate = @json($abyipGate ?? null);
</script>
<script>
// Inline date validation for Programs
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('editStartDate');
    const endDateInput = document.getElementById('editEndDate');
    const startDateError = document.getElementById('editStartDateError');
    const endDateError = document.getElementById('editEndDateError');
    const saveBtn = document.getElementById('editDurationSave');

    function getTodayDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function validateStartDate() {
        const value = startDateInput.value;
        const today = getTodayDate();
        
        if (!value) {
            startDateError.textContent = '';
            startDateError.style.display = 'none';
            return true;
        }
        
        if (value < today) {
            startDateError.textContent = 'Bawal yung past dates';
            startDateError.style.display = 'block';
            return false;
        }
        
        startDateError.textContent = '';
        startDateError.style.display = 'none';
        return true;
    }

    function validateEndDate() {
        const startValue = startDateInput.value;
        const endValue = endDateInput.value;
        const today = getTodayDate();
        
        if (!endValue) {
            endDateError.textContent = '';
            endDateError.style.display = 'none';
            return true;
        }
        
        if (endValue < today) {
            endDateError.textContent = 'Bawal yung past dates';
            endDateError.style.display = 'block';
            return false;
        }
        
        if (startValue && endValue && endValue < startValue) {
            endDateError.textContent = 'End Date must be after Start Date';
            endDateError.style.display = 'block';
            return false;
        }
        
        endDateError.textContent = '';
        endDateError.style.display = 'none';
        return true;
    }

    if (startDateInput) {
        startDateInput.addEventListener('input', function() {
            validateStartDate();
            validateEndDate();
        });
    }

    if (endDateInput) {
        endDateInput.addEventListener('input', validateEndDate);
    }

    if (saveBtn) {
        const originalSaveHandler = saveBtn.onclick;
        saveBtn.onclick = function(e) {
            const isStartValid = validateStartDate();
            const isEndValid = validateEndDate();
            
            if (!isStartValid || !isEndValid) {
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

