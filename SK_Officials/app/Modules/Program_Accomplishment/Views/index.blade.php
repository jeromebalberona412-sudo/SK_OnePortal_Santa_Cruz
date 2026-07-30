<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Program Accomplishment Reports - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Accomplishment/Assets/css/program-accomplishment.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/abyip-pending-notice.css') }}">
    <script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
    <link rel="stylesheet" href="{{ url('/shared/css/modal.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container accomplishment-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Program Accomplishment Reports</h1>
                <p class="page-subtitle">Create and manage accomplishment reports for completed ABYIP programs.</p>
            </div>
            <div class="page-header-right page-header-right-desktop">
                <button type="button" id="openCreateModalBtn" class="btn primary-btn">Create Report</button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="statusFilter" class="filter-label">Status</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Draft">Draft</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Published">Published</option>
                    </select>
                </div>
                <div class="filter-item filter-item-search">
                    <label for="searchInput" class="filter-label">Search</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="searchInput" class="abyip-filter-search-inline" placeholder="Search reports..." autocomplete="off">
                    </div>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Reports</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Program</th>
                                <th>Date Completed</th>
                                <th>Budget Allocated</th>
                                <th>Actual Expense</th>
                                <th>Utilization</th>
                                <th>Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reportsTableBody">
                            <tr>
                                <td colspan="8" class="empty-state">Loading reports...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer" id="tableFooter"></div>
            </div>
        </section>
    </div>
</main>

{{-- Create Report Modal --}}
<div id="createReportModal" class="modal-overlay" style="display:none;">
    <div class="modal-container modal-large">
        <div class="modal-header">
            <h2 class="modal-title">Create Accomplishment Report</h2>
            <button type="button" class="modal-close" id="modalCloseBtn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="accomplishmentForm" class="cform">
                @csrf
                <input type="hidden" name="program_id" id="program_id" value="">

                <div class="cform-card">
                    <div class="cform-card-header">
                        <span class="cform-step">1</span>
                        <div>
                            <h3 class="cform-card-title">Select Program</h3>
                            <p class="cform-card-desc">Choose a completed program for this report.</p>
                        </div>
                    </div>
                    <div class="cform-card-body">
                        <div class="program-search-wrap">
                            <svg class="program-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" id="program_search" class="program-search-input" placeholder="Search completed programs..." autocomplete="off">
                            <div id="programSearchResults" class="search-results-dropdown"></div>
                        </div>
                        <div id="selectedProgramInfo" class="program-selected-card" style="display:none;">
                            <div class="program-selected-header">
                                <span class="program-badge program-badge-program">PROGRAM</span>
                                <button type="button" id="changeProgramBtn" class="btn-change-program">Change</button>
                            </div>
                            <h4 id="selectedProgramName" class="program-selected-name"></h4>
                            <div class="program-selected-meta">
                                <span id="selectedProgramCategory"></span>
                                <span class="meta-sep">&bull;</span>
                                <span>&#8369;<span id="selectedProgramBudget">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cform-card" id="autoDetailsCard" style="display:none;">
                    <div class="cform-card-header">
                        <span class="cform-step">2</span>
                        <div>
                            <h3 class="cform-card-title">Program Details</h3>
                            <p class="cform-card-desc">Auto-fetched from the ABYIP record.</p>
                        </div>
                    </div>
                    <div class="cform-card-body">
                        <div class="auto-grid">
                            <div class="auto-field">
                                <span class="auto-label">Description</span>
                                <span class="auto-value" id="auto_description">—</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">Expected Result</span>
                                <span class="auto-value" id="auto_expected_result">—</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">Date Started</span>
                                <span class="auto-value" id="auto_date_started">—</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">Date Completed</span>
                                <span class="auto-value" id="auto_date_completed">—</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">Person Responsible</span>
                                <span class="auto-value" id="auto_person_responsible">—</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">MOOE</span>
                                <span class="auto-value" id="auto_mooe">&#8369;0.00</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">CO</span>
                                <span class="auto-value" id="auto_co">&#8369;0.00</span>
                            </div>
                            <div class="auto-field">
                                <span class="auto-label">Total Budget</span>
                                <span class="auto-value auto-value-highlight" id="auto_budget_allocated">&#8369;0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="title" id="field_title" value="">
                <input type="hidden" name="description" id="field_description" value="">
                <input type="hidden" name="objectives" id="field_objectives" value="">
                <input type="hidden" name="date_started" id="field_date_started" value="">
                <input type="hidden" name="date_completed" id="field_date_completed" value="">
                <input type="hidden" name="person_responsible" id="field_person_responsible" value="">
                <input type="hidden" name="budget_allocated" id="field_budget_allocated" value="0">

                <div class="cform-card" id="manualInputsCard" style="display:none;">
                    <div class="cform-card-header">
                        <span class="cform-step">3</span>
                        <div>
                            <h3 class="cform-card-title">Report Details</h3>
                            <p class="cform-card-desc">Fill in the remaining details.</p>
                        </div>
                    </div>
                    <div class="cform-card-body">
                        <div class="mform-grid">
                            <div class="mform-group">
                                <label class="mform-label">Actual Expense (&#8369;) <span class="required">*</span></label>
                                <input type="number" name="actual_expense" id="actual_expense" class="mform-control" step="0.01" min="0" required value="0.00" placeholder="0.00">
                            </div>
                            <div class="mform-group">
                                <label class="mform-label">Participants <span class="required">*</span></label>
                                <input type="number" name="participants_count" id="participants_count" class="mform-control" min="0" required value="0" placeholder="0">
                            </div>
                            <div class="mform-group">
                                <label class="mform-label">Venue</label>
                                <input type="text" name="venue" id="venue" class="mform-control" maxlength="255" placeholder="e.g., Barangay Hall">
                            </div>
                        </div>

                        <div class="budget-utilization-bar">
                            <div class="util-row">
                                <span>Allocated: <strong>&#8369;<span id="util_allocated">0.00</span></strong></span>
                                <span>Expense: <strong>&#8369;<span id="util_expense">0.00</span></strong></span>
                                <span>Remaining: <strong>&#8369;<span id="util_remaining">0.00</span></strong></span>
                                <span>Utilization: <strong><span id="util_percent">0.00</span>%</strong></span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" id="util_progress_fill" style="width:0%;"></div>
                            </div>
                            <div id="budgetError" class="util-error" style="display:none;"></div>
                        </div>

                        <div class="mform-group" style="margin-top:16px;">
                            <label class="mform-label">Implementation Summary</label>
                            <textarea name="implementation_summary" id="implementation_summary" class="mform-control mform-textarea" rows="4" placeholder="Describe how the program was implemented."></textarea>
                        </div>

                        <div class="mform-row-2">
                            <div class="mform-group">
                                <label class="mform-label">Lessons Learned</label>
                                <textarea name="lessons_learned" id="lessons_learned" class="mform-control mform-textarea" rows="3" placeholder="Lessons learned."></textarea>
                            </div>
                            <div class="mform-group">
                                <label class="mform-label">Recommendations</label>
                                <textarea name="recommendations" id="recommendations" class="mform-control mform-textarea" rows="3" placeholder="Recommendations."></textarea>
                            </div>
                        </div>

                        <div class="mform-group">
                            <label class="mform-label">Remarks</label>
                            <textarea name="remarks" id="remarks" class="mform-control mform-textarea" rows="2" placeholder="Additional remarks."></textarea>
                        </div>
                    </div>
                </div>

                <div class="cform-card" id="mediaUploadCard" style="display:none;">
                    <div class="cform-card-header">
                        <span class="cform-step">4</span>
                        <div>
                            <h3 class="cform-card-title">Media</h3>
                            <p class="cform-card-desc">Upload photo and document (optional).</p>
                        </div>
                    </div>
                    <div class="cform-card-body">
                        <div class="media-grid">
                            <div class="media-upload-box">
                                <div class="media-upload-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                                <h4 class="media-upload-title">Photo</h4>
                                <input type="hidden" name="image_name" id="image_name" value="">
                                <input type="hidden" name="image_path" id="image_path" value="">
                                <input type="hidden" name="image_type" id="image_type" value="">
                                <input type="hidden" name="image_size" id="image_size" value="">
                                <input type="hidden" name="image_caption" id="image_caption" value="">
                                <button type="button" class="btn-upload" data-cld-upload="image" data-cloud-name="{{ config('services.cloudinary.cloud_name') }}" data-upload-preset="Accomplishment_Report">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload
                                </button>
                                <div id="imagePreviewContainer" class="media-preview"></div>
                            </div>
                            <div class="media-upload-box">
                                <div class="media-upload-icon">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                </div>
                                <h4 class="media-upload-title">Document</h4>
                                <input type="hidden" name="file_name" id="file_name" value="">
                                <input type="hidden" name="file_path" id="file_path" value="">
                                <input type="hidden" name="file_type" id="file_type" value="">
                                <input type="hidden" name="file_size" id="file_size" value="">
                                <button type="button" class="btn-upload" data-cld-upload="file" data-cloud-name="{{ config('services.cloudinary.cloud_name') }}" data-upload-preset="Accomplishment_Report">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload
                                </button>
                                <div id="filePreviewContainer" class="media-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" id="formActions" style="display:none;">
            <button type="button" class="btn btn-outline" id="modalCancelBtn">Cancel</button>
            <button type="submit" class="btn btn-primary btn-with-icon" id="modalSaveBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save as Draft
            </button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Program_Accomplishment/Assets/js/program-accomplishment.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
<script src="{{ url('/shared/js/abyip-pending-notice.js') }}"></script>
</body>
</html>