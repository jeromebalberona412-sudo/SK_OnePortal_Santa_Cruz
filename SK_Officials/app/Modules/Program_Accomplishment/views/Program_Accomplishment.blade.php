<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Program Accomplishments - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Layout/css/table-row-actions-menu.css',
        'app/Modules/Program_Accomplishment/Assets/css/program-accomplishment.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    <link rel="stylesheet" href="{{ url('/shared/css/table-page-footer.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container program-accomplishment-page has-table-page-footer">

        <!-- Page Header -->
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Program Accomplishments</h1>
                <p class="page-subtitle">
                    Create and manage accomplishment reports for completed SK programs.
                </p>
            </div>
        </section>

        <!-- Statistics Cards -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="paStatTotalPrograms">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Completed Programs</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="paStatReports">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Accomplishment Reports</span>
            </div>
            <div class="stat-card stat-card-yellow">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="paStatPending">0</span>
                    <div class="stat-card-icon stat-icon-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Pending Reports</span>
            </div>
            <div class="stat-card stat-card-teal">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="paStatImages">0</span>
                    <div class="stat-card-icon stat-icon-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Proof Images</span>
            </div>
        </div>

        <!-- Filters Section -->
        <section class="page-filters-section">
            <div class="table-action-bar">
                <div class="pa-search-inline">
                    <label for="paSearch" class="pa-sr-only">Search programs</label>
                    <div class="pa-search-wrapper">
                        <span class="pa-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text" id="paSearch" class="pa-filter-search-inline" placeholder="Search programs..." autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="filters-row">
                <div class="filter-item">
                    <label for="paCategoryFilter" class="filter-label">Category</label>
                    <select id="paCategoryFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Youth Development">Youth Development</option>
                        <option value="Sports Development">Sports Development</option>
                        <option value="Education">Education</option>
                        <option value="Health">Health</option>
                        <option value="Environment">Environment</option>
                        <option value="Culture & Arts">Culture & Arts</option>
                        <option value="Livelihood">Livelihood</option>
                        <option value="Community Service">Community Service</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="paDateFromFilter" class="filter-label">Date From</label>
                    <input type="date" id="paDateFromFilter" class="filter-input">
                </div>
                <div class="filter-item">
                    <label for="paDateToFilter" class="filter-label">Date To</label>
                    <input type="date" id="paDateToFilter" class="filter-input">
                </div>
                <div class="filter-item">
                    <label for="paReportStatusFilter" class="filter-label">Report Status</label>
                    <select id="paReportStatusFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="With Report">With Report</option>
                        <option value="Without Report">Without Report</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Table Section -->
        <section class="page-content-section">
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Program Type</th>
                                <th>Committee</th>
                                <th>Date Started</th>
                                <th>Date Completed</th>
                                <th>Budget Allocated</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Report Status</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="paTableBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Table Footer -->
        <div class="table-page-footer">
            <div class="table-footer-info">
                <span id="paShowingInfo">Showing 0 of 0 programs</span>
            </div>
            <div class="table-footer-pagination">
                <button type="button" class="footer-page-btn" id="paPrevBtn" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    Previous
                </button>
                <span class="footer-page-info" id="paPageInfo">Page 1 of 1</span>
                <button type="button" class="footer-page-btn" id="paNextBtn" disabled>
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Accomplishment Report Modal -->
<div class="pa-modal" id="paModal">
    <div class="pa-modal-overlay" id="paModalOverlay"></div>
    <div class="pa-modal-container">
        <div class="pa-modal-header">
            <h2 class="pa-modal-title" id="paModalTitle">Create Accomplishment Report</h2>
            <button type="button" class="pa-modal-close" id="paModalClose">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="pa-modal-body">
            <form id="paForm" data-uploader-name="{{ auth()->user()->name ?? '' }}">
                @csrf

                <div class="pa-form-section">
                    <h3 class="pa-section-title">Program Details</h3>
                    <p class="pa-section-note">These details are loaded automatically from the completed program.</p>
                    <div class="pa-form-grid">
                        <div class="pa-form-group pa-form-group-full">
                            <label class="pa-form-label">Program Name</label>
                            <input type="text" id="paProgram" class="pa-form-input" readonly>
                        </div>
                        <div class="pa-form-group">
                            <label class="pa-form-label">Date Started</label>
                            <input type="text" id="paDateStarted" class="pa-form-input" readonly>
                        </div>
                        <div class="pa-form-group">
                            <label class="pa-form-label">Date Completed</label>
                            <input type="text" id="paDateCompleted" class="pa-form-input" readonly>
                        </div>
                        <div class="pa-form-group">
                            <label class="pa-form-label">Status</label>
                            <input type="text" id="paProgramStatus" class="pa-form-input" readonly>
                        </div>
                        <div class="pa-form-group">
                            <label class="pa-form-label">Uploaded by</label>
                            <input type="text" id="paUploadedBy" class="pa-form-input" readonly>
                        </div>
                    </div>
                </div>

                <div class="pa-form-section">
                    <h3 class="pa-section-title">MS Word Report <span class="pa-required">*</span></h3>
                    <p class="pa-section-note">Upload 1 Word file only (.doc or .docx), maximum 10MB.</p>
                    <label class="pa-word-upload" for="paDocumentInput">
                        <span class="pa-word-upload-title">Choose Word file</span>
                        <span class="pa-word-upload-hint">.doc or .docx only</span>
                    </label>
                    <input type="file" id="paDocumentInput" class="pa-word-input" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    <div id="paExistingDocuments" class="pa-document-preview"></div>
                    <div id="paDocumentPreview" class="pa-document-preview"></div>
                    <div class="pa-image-validation" id="paDocumentValidation"></div>
                </div>

                <div class="pa-form-section">
                    <h3 class="pa-section-title">Proof Images</h3>
                    <div class="pa-image-upload">
                        <div class="pa-upload-area" id="paUploadArea">
                            <div class="pa-upload-content">
                                <svg class="pa-upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p class="pa-upload-text">Drag & drop images here or click to browse</p>
                                <p class="pa-upload-hint">Maximum 50 images (JPG, JPEG, PNG, WEBP) - 10MB each</p>
                            </div>
                            <input type="file" id="paImageInput" class="pa-image-input" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
                        </div>
                        <div class="pa-image-preview" id="paImagePreview"></div>
                        <div class="pa-upload-progress" id="paUploadProgress"></div>
                    </div>
                    <div class="pa-image-validation" id="paImageValidation"></div>
                </div>

                <div class="pa-form-section" id="paExistingImagesSection" style="display: none;">
                    <h3 class="pa-section-title">Existing Images</h3>
                    <div class="pa-existing-images" id="paExistingImages"></div>
                </div>
            </form>
        </div>
        <div class="pa-modal-footer">
            <button type="button" class="pa-btn pa-btn-secondary" id="paCancelBtn">Cancel</button>
            <button type="button" class="pa-btn pa-btn-primary" id="paSaveBtn">
                <span class="pa-btn-label">Submit</span>
                <span class="pa-btn-spinner" hidden aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="pa-modal" id="paViewModal">
    <div class="pa-modal-overlay" id="paViewModalOverlay"></div>
    <div class="pa-modal-container pa-view-modal-container">
        <div class="pa-modal-header">
            <h2 class="pa-modal-title" id="paViewModalTitle">Accomplishment Report</h2>
            <button type="button" class="pa-modal-close" id="paViewModalClose">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="pa-modal-body pa-view-modal-body">
            <div class="pa-view-content" id="paViewContent">
                <!-- Dynamic view content -->
            </div>
        </div>
        <div class="pa-modal-footer" id="paViewModalFooter" hidden>
            <button type="button" class="pa-btn pa-btn-primary" id="paPublishBtn" hidden>Publish</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="pa-modal" id="paDeleteModal">
    <div class="pa-modal-overlay" id="paDeleteModalOverlay"></div>
    <div class="pa-modal-container pa-delete-modal-container">
        <div class="pa-delete-header">
            <h2 class="pa-modal-title">Delete Accomplishment Report</h2>
        </div>
        <div class="pa-delete-body">
            <p class="pa-delete-text">You are about to delete the report for:</p>
            <p class="pa-delete-program" id="paDeleteProgramTitle"></p>
            <p class="pa-delete-warning">This will also delete <span id="paDeleteImageCount">0</span> uploaded images. This cannot be undone.</p>
            <label class="pa-delete-confirm-label" for="paDeleteConfirmInput">Type Confirm to continue</label>
            <input type="text" id="paDeleteConfirmInput" class="pa-delete-confirm-input" placeholder="Type Confirm to confirm" autocomplete="off" spellcheck="false">
            <p class="pa-delete-confirm-hint" id="paDeleteConfirmHintError" hidden>Please type "Confirm" exactly.</p>
        </div>
        <div class="pa-delete-footer">
            <button type="button" class="pa-btn pa-btn-secondary" id="paDeleteCancelBtn">Cancel</button>
            <button type="button" class="pa-btn pa-btn-danger is-disabled" id="paDeleteConfirmBtn" disabled>Delete</button>
        </div>
    </div>
</div>

<!-- Image Lightbox -->
<div class="pa-lightbox" id="paLightbox">
    <div class="pa-lightbox-overlay" id="paLightboxOverlay"></div>
    <button type="button" class="pa-lightbox-close" id="paLightboxClose">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    <button type="button" class="pa-lightbox-prev" id="paLightboxPrev">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>
    <button type="button" class="pa-lightbox-next" id="paLightboxNext">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>
    <div class="pa-lightbox-content">
        <img src="" alt="" class="pa-lightbox-image" id="paLightboxImage">
        <div class="pa-lightbox-caption" id="paLightboxCaption"></div>
    </div>
    <button type="button" class="pa-lightbox-download" id="paLightboxDownload" title="Download">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
    </button>
</div>

<!-- Toast Container -->
<div class="pa-toast-container" id="paToastContainer"></div>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Layout/js/table-row-actions-menu.js',
    'app/Modules/Program_Accomplishment/Assets/js/program-accomplishment.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>

</body>
</html>