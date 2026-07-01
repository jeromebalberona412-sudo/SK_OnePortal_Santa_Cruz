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
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container prev-kab-page has-table-page-footer">

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
                    </select>
                </div>
                <div class="filter-item">
                    <label for="prevKabPurokFilter" class="filter-label">Purok / Sitio</label>
                    <select id="prevKabPurokFilter" class="filter-select">
                        <option value="">All</option>
                        @include('layout::partials.barangay-zone-options')
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

        <div class="table-external-actions prev-kab-table-actions" id="prevKabTableActions" hidden>
            <button type="button" class="btn-float-delete" id="prevKabBulkDeleteBtn" hidden>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span id="prevKabBulkDeleteLabel">Delete Rows</span>
            </button>
        </div>

        <!-- Content Section -->
        <section class="page-content-section">
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="prev-kab-table">
                        <thead>
                            <tr>
                                <th class="th-checkbox">
                                    <input type="checkbox" class="prev-kab-checkbox prev-kab-checkbox-header" id="prevKabSelectAll" aria-label="Select all visible rows">
                                </th>
                                <th>#</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Suffix</th>
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
                        <tbody id="prevKabTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="prev-kab-page-footer table-page-footer pagination-footer" aria-label="Table pagination">
                <div class="pagination-footer-nav">
                    <button type="button" class="pagination-arrow" id="prevKabPrevBtn" disabled aria-label="Previous page">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <span class="pagination-page-label">Page</span>
                    <input type="number" class="pagination-page-input" id="prevKabPageInput" value="1" min="1" aria-label="Current page">
                    <span class="pagination-page-of">of <span id="prevKabTotalPages">1</span></span>
                    <button type="button" class="pagination-arrow" id="prevKabNextBtn" disabled aria-label="Next page">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
                <div class="pagination-footer-right">
                    <select id="prevKabRowsPerPageSelect" class="pagination-rows-select" aria-label="Rows per page">
                        <option value="10">10 rows</option>
                        <option value="50">50 rows</option>
                        <option value="100">100 rows</option>
                    </select>
                    <span class="pagination-record-count" id="prevKabPaginationInfo">0 records</span>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop prev-kab-delete-backdrop" id="prevKabDeleteModal" style="display:none;">
    <div class="sk-type-confirm-modal prev-kab-delete-modal" role="dialog" aria-modal="true" aria-labelledby="prevKabDeleteTitle">
        <div class="sk-type-confirm-header">
            <h3 id="prevKabDeleteTitle">Delete Record</h3>
        </div>
        <div class="sk-type-confirm-body">
            <p class="sk-type-confirm-message" id="prevKabDeleteMessage">Are you sure you want to delete the selected record(s)?</p>
            <p class="sk-type-confirm-desc">This action cannot be undone.</p>
            <label class="sk-type-confirm-label" for="prevKabDeleteConfirmInput">Confirmation Required</label>
            <input type="text" id="prevKabDeleteConfirmInput" class="sk-type-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
            <p class="sk-type-confirm-hint sk-type-confirm-hint-error" id="prevKabDeleteConfirmError" style="display:none;"></p>
        </div>
        <div class="sk-type-confirm-footer">
            <button type="button" class="sk-btn-cancel-confirm" id="prevKabDeleteCancelBtn">Cancel</button>
            <button type="button" class="sk-btn-action-confirm is-disabled" id="prevKabDeleteConfirmBtn" disabled>Confirm Delete</button>
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
        <div class="modal-body upload-modal-body">

            <button type="button" class="download-sample-link" id="prevKabDownloadSample">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download sample Excel template
            </button>

            <div class="prev-kab-upload-replace-notice" role="note">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p><strong>Important:</strong> Uploading a new file will <strong>replace all existing Previous Kabataan records</strong> for your barangay. Old data will be removed and only the new upload will remain.</p>
            </div>

            <!-- Drop zone — hidden once file is selected -->
            <div class="upload-zone" id="prevKabUploadZone">
                <div class="upload-zone-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div class="upload-zone-title">Click to upload or drag and drop</div>
                <div class="upload-zone-hint">Excel files only (.xlsx, .xls)</div>
                <div class="upload-zone-limit">Maximum file size: 10MB · Maximum 5,000 rows per upload</div>
            </div>

            <input type="file" id="prevKabFileInput" class="upload-file-input" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">

            <!-- Selected file bar -->
            <div class="upload-selected-file" id="prevKabSelectedFile">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="prevKabSelectedFileName">filename.xlsx</span>
                <button type="button" class="upload-remove-file" id="prevKabRemoveFile">&times;</button>
            </div>

            <!-- Upload Progress Indicator -->
            <div class="upload-progress-container" id="prevKabUploadProgress" style="display:none;" aria-live="polite">
                <div class="upload-progress-header">
                    <div class="upload-progress-spinner" aria-hidden="true">
                        <span class="upload-progress-ring"></span>
                    </div>
                    <div class="upload-progress-text">
                        <div class="upload-progress-title">Uploading records...</div>
                        <div class="upload-progress-status" id="prevKabProgressStatus">0 / 0 records uploaded</div>
                    </div>
                </div>
                <div class="upload-progress-bar-container">
                    <div class="upload-progress-bar" id="prevKabProgressBar">
                        <div class="upload-progress-bar-fill" id="prevKabProgressBarFill"></div>
                    </div>
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
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Suffix</th>
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

<!-- Toast (top-center) -->
<div class="prev-kab-toast" id="prevKabToast" style="display:none;" role="status" aria-live="polite">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="prevKabToastMsg"></span>
</div>
</body>
</html>
