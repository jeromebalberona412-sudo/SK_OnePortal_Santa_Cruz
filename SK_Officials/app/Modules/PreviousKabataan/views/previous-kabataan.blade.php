<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Previous Kabataan Records - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/PreviousKabataan/assets/css/previous-kabataan.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <!-- Page Header -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Previous Kabataan Records</h1>
                <p class="page-subtitle">Manage and view historical census records for youth profiling across all barangays.</p>
            </div>
            <div class="page-header-actions">
                <div class="abyip-search-inline">
                    <label for="prevKabSearch" class="abyip-sr-only">Search records</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="prevKabSearch" class="abyip-filter-search-inline" placeholder="Search by name..." autocomplete="off">
                    </div>
                </div>
                <button type="button" class="btn primary-btn" id="prevKabUploadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Upload Previous Kabataan
                </button>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="page-filters-section">
            <div class="table-action-bar">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="prevKabYearFilter" class="filter-label">Previous Kabataan</label>
                    <select id="prevKabYearFilter" class="filter-select">
                        <option value="">All Years</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="prevKabPurokFilter" class="filter-label">Purok/Zone</label>
                    <select id="prevKabPurokFilter" class="filter-select">
                        <option value="">All Puroks</option>
                        <option value="Bayside">Bayside</option>
                        <option value="Villa Gracia">Villa Gracia</option>
                        <option value="Imelda">Imelda</option>
                        <option value="Lupang Pangako">Lupang Pangako</option>
                        <option value="Damayan">Damayan</option>
                        <option value="Marcelo">Marcelo</option>
                        <option value="Bigayan Villa Rosa">Bigayan Villa Rosa</option>
                        <option value="Phase 3">Phase 3</option>
                        <option value="Bigayan San Luis">Bigayan San Luis</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="prevKabVoterFilter" class="filter-label">Voter Status</label>
                    <select id="prevKabVoterFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Content Section -->
        <section class="page-content-section">
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="prev-kab-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>
                                    Full Name
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Birthday</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Youth Classification</th>
                                <th>Youth Age Group</th>
                                <th>Contact #</th>
                                <th>Home Address</th>
                                <th>Education</th>
                                <th>Work Status</th>
                                <th>Registered Voter?</th>
                                <th>Voted Last Election?</th>
                                <th>Attended KK Assembly?</th>
                                <th>If Yes, How Many Times?</th>
                                <th>Barangay</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>City/Municipality</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="prevKabTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info" id="prevKabPaginationInfo">Showing 0–0 of 0 records</div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevKabPrevBtn">Previous</button>
                    <div class="pagination-numbers" id="prevKabPaginationNums"></div>
                    <button class="pagination-btn" id="prevKabNextBtn">Next</button>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Delete Reason Modal -->
<div class="modal-backdrop delete-modal-backdrop" id="prevKabDeleteModal" style="display:none;">
    <div class="modal-box delete-modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Delete Record</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <p style="margin-bottom: 16px; color: #374151; font-size: 14px;">Please provide a reason for deleting this record:</p>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Reason</label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151;">
                        <input type="checkbox" name="deleteReason" value="Duplicate Record"> Duplicate Record
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151;">
                        <input type="checkbox" name="deleteReason" value="Incorrect Information"> Incorrect Information
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151;">
                        <input type="checkbox" name="deleteReason" value="Other"> Other
                    </label>
                </div>
            </div>
            
            <div class="form-group" id="otherReasonGroup" style="margin-bottom: 16px; display: none;">
                <label for="otherReason" class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Other Reason</label>
                <textarea id="otherReason" class="form-textarea other-reason-textarea" maxlength="500" rows="3" placeholder="Please specify the reason (max 500 characters)"></textarea>
                <div style="text-align: right; font-size: 11px; color: #6b7280; margin-top: 4px;"><span id="charCount">0</span>/500</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn outline-btn" data-modal-close>Cancel</button>
            <button type="button" class="btn danger-btn" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal-backdrop upload-modal-backdrop" id="prevKabUploadModal" style="display:none;">
    <div class="modal-box upload-modal-box upload-modal-box--wide">
        <div class="modal-header">
            <h2 class="modal-title">Upload Previous Kabataan Records</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="prevKabUploadModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding:18px;">

            <!-- Drop zone — hidden once file is selected -->
            <div class="upload-zone" id="prevKabUploadZone">
                <div class="upload-zone-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div class="upload-zone-title">Click to upload or drag and drop</div>
                <div class="upload-zone-hint">Excel files only (.xlsx, .xls)</div>
                <div class="upload-zone-limit">Maximum file size: 10MB</div>
            </div>

            <input type="file" id="prevKabFileInput" class="upload-file-input" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">

            <!-- Selected file bar -->
            <div class="upload-selected-file" id="prevKabSelectedFile">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="prevKabSelectedFileName">filename.xlsx</span>
                <button type="button" class="upload-remove-file" id="prevKabRemoveFile">&times;</button>
            </div>

            <!-- Upload Progress Indicator -->
            <div class="upload-progress-container" id="prevKabUploadProgress" style="display:none;">
                <div class="upload-progress-header">
                    <div class="upload-progress-spinner">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spinner-icon"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </div>
                    <div class="upload-progress-text">
                        <div class="upload-progress-title">Uploading records...</div>
                        <div class="upload-progress-status" id="prevKabProgressStatus">0 / 0 records uploaded</div>
                    </div>
                </div>
                <div class="upload-progress-bar-container">
                    <div class="upload-progress-bar" id="prevKabProgressBar"></div>
                </div>
                <div class="upload-progress-percentage" id="prevKabProgressPercentage">0%</div>
            </div>

            <!-- Inline preview — shown after file selected -->
            <div id="prevKabInlinePreview" style="display:none;">
                <div class="preview-info-bar" style="margin-bottom:12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Preview: <strong id="prevKabPreviewCount">0</strong> record(s) — review before saving.</span>
                </div>
                <div class="preview-table-wrap">
                    <table class="preview-table preview-table--full">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>
                                    Full Name
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Birthday</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Youth Classification</th>
                                <th>Youth Age Group</th>
                                <th>Contact #</th>
                                <th>Home Address</th>
                                <th>Education</th>
                                <th>Work Status</th>
                                <th>Registered Voter?</th>
                                <th>Voted Last Election?</th>
                                <th>Attended KK Assembly?</th>
                                <th>If Yes, How Many Times?</th>
                                <th>Barangay</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>City/Municipality</th>
                            </tr>
                        </thead>
                        <tbody id="prevKabPreviewTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn outline-btn" data-modal-close>Cancel</button>
            <button type="button" class="btn primary-btn" id="prevKabConfirmUpload" disabled>Upload & Preview</button>
            <button type="button" class="btn primary-btn" id="prevKabConfirmSave" style="display:none;">Confirm &amp; Save</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/PreviousKabataan/assets/js/previous-kabataan.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>

<!-- Toast Notification Container -->
<div class="toast-container" id="prevKabToastContainer"></div>
</body>
</html>
