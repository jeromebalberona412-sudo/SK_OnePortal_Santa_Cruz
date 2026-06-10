<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Management - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Reports_Management/assets/css/reports-management.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container rm-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Reports Management</h1>
                <p class="page-subtitle">
                    Upload barangay program and activity reports for SK Federations review.
                </p>
            </div>
            <div class="page-header-right">
                <button type="button" class="rm-primary-btn" id="rmOpenUploadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Upload Report
                </button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="rmSearchInput" class="filter-label">Search</label>
                    <input type="search" id="rmSearchInput" class="filter-input" placeholder="Search program, activity, or file name...">
                </div>
                <div class="filter-item">
                    <label for="rmProgramFilter" class="filter-label">Program</label>
                    <select id="rmProgramFilter" class="filter-select">
                        <option value="">All programs</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Uploaded Reports</h2>
                <p class="section-note">Reports stay <strong>Pending</strong> until SK Federations verifies and approves them.</p>
            </div>

            <div class="table-card rm-records-card">
                <div class="table-wrapper rm-records-wrap">
                    <table class="rm-records-table" id="rmRecordsTable">
                        <thead>
                            <tr>
                                <th scope="col">Program</th>
                                <th scope="col">Activity</th>
                                <th scope="col">File Name</th>
                                <th scope="col">Date Uploaded</th>
                                <th scope="col" class="rm-actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="rmRecordsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

<div class="rm-modal-overlay" id="rmUploadModal" hidden>
    <div class="rm-modal" role="dialog" aria-modal="true" aria-labelledby="rmUploadModalTitle">
        <div class="rm-modal-header">
            <h2 id="rmUploadModalTitle">Upload Program Report</h2>
            <button type="button" class="rm-modal-close" id="rmCloseUploadBtn" aria-label="Close">&times;</button>
        </div>
        <div class="rm-modal-body">
            <p class="rm-modal-intro">
                Select the program and activity, then upload a Word or PDF report file for your barangay.
            </p>

            <div class="rm-form-grid">
                <div class="rm-form-group">
                    <label for="rmProgramSelect" class="rm-label">Program <span class="rm-required">*</span></label>
                    <select id="rmProgramSelect" class="rm-select" required>
                        <option value="">Select program</option>
                    </select>
                </div>

                <div class="rm-form-group">
                    <label for="rmActivitySelect" class="rm-label">Activity <span class="rm-required">*</span></label>
                    <select id="rmActivitySelect" class="rm-select" required disabled>
                        <option value="">Select program first</option>
                    </select>
                </div>
            </div>

            <div class="rm-upload-zone" id="rmUploadZone">
                <input type="file" id="rmFileInput" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" hidden>
                <div class="rm-upload-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                </div>
                <p class="rm-upload-title">Drag &amp; drop your report here</p>
                <p class="rm-upload-sub">or click to browse — Word (.doc, .docx) or PDF (.pdf)</p>
                <p class="rm-upload-file" id="rmSelectedFileName">No file selected</p>
            </div>
        </div>
        <div class="rm-modal-footer">
            <button type="button" class="rm-secondary-btn" id="rmCancelUploadBtn">Cancel</button>
            <button type="button" class="rm-primary-btn" id="rmSubmitUploadBtn" disabled>Submit Report</button>
        </div>
    </div>
</div>

<div class="rm-modal-overlay" id="rmPreviewModal" hidden>
    <div class="rm-modal rm-preview-modal" role="dialog" aria-modal="true" aria-labelledby="rmPreviewModalTitle">
        <div class="rm-modal-header">
            <h2 id="rmPreviewModalTitle">Report Details</h2>
            <button type="button" class="rm-modal-close" id="rmClosePreviewBtn" aria-label="Close">&times;</button>
        </div>
        <div class="rm-modal-body" id="rmPreviewBody"></div>
        <div class="rm-modal-footer">
            <button type="button" class="rm-secondary-btn" id="rmClosePreviewFooterBtn">Close</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Reports_Management/assets/js/reports-management.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
