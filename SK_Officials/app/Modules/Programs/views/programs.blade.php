<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Programs - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Programs/assets/css/programs.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/abyip-pending-notice.css') }}">
</head>
<body>

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

        <!-- -- Programs Stat Cards -- -->
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
            <div class="stat-card stat-card-purple">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="progStatPlanned">0</span>
                    <div class="stat-card-icon stat-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Planned</span>
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
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">?</button>
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
            <div class="modal-field">
                <label>Status</label>
                <div id="editDurationStatus" class="prog-edit-status-wrap">
                    <span class="status-pill planned" id="editDurationStatusPill">Planned</span>
                    <span class="prog-edit-status-hint">Auto-detected from the dates above.</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" style="background:#fff;color:#374151;border:1.5px solid #d1d5db;" id="editDurationCancel">Cancel</button>
            <button type="button" class="btn primary-btn" id="editDurationSave">Update</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Programs/assets/js/programs.js'
])
<script src="{{ url('/shared/js/abyip-pending-notice.js') }}"></script>
<script>
    window.programsAbyipGate = @json($abyipGate ?? null);
</script>
</body>
</html>

