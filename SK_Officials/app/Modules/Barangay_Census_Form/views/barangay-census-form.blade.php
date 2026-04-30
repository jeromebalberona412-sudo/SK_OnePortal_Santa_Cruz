<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Census Form - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Barangay_Census_Form/assets/css/barangay-census-form.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container census-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Barangay Census Form</h1>
                <p class="page-subtitle">
                    Manage and view barangay census records (CMP-04)
                </p>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn census-upload-btn-green" id="censusUploadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Upload Census
                </button>
            </div>
        </section>

        <!-- Census Stats -->
        <div class="module-stats-grid">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="censusStatTotal">0</span>
                    <div class="stat-card-icon stat-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <span class="stat-card-label">Total Census Records</span>
            </div>
            <div class="stat-card stat-card-green">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="censusStatOwners">0</span>
                    <div class="stat-card-icon stat-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </div>
                </div>
                <span class="stat-card-label">Property Owners</span>
            </div>
            <div class="stat-card stat-card-orange">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="censusStatRentees">0</span>
                    <div class="stat-card-icon stat-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                </div>
                <span class="stat-card-label">Boarders/Rentees</span>
            </div>
            <div class="stat-card stat-card-purple">
                <div class="stat-card-top">
                    <span class="stat-card-value" id="censusStatYear">2026</span>
                    <div class="stat-card-icon stat-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                </div>
                <span class="stat-card-label">Current Year</span>
            </div>
        </div>

        <section class="page-filters-section">
            <!-- Search Bar -->
            <div class="table-action-bar">
                <div class="abyip-search-inline">
                    <label for="censusSearch" class="abyip-sr-only">Search census records</label>
                    <div class="abyip-search-wrapper">
                        <span class="abyip-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="censusSearch" class="abyip-filter-search-inline" placeholder="Search census records..." maxlength="80" autocomplete="off">
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-row">
                <div class="filter-item">
                    <label for="censusPurokFilter" class="filter-label">Purok/Sitio</label>
                    <select id="censusPurokFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Purok 1">Purok 1</option>
                        <option value="Purok 2">Purok 2</option>
                        <option value="Purok 3">Purok 3</option>
                        <option value="Purok 4">Purok 4</option>
                        <option value="Purok 5">Purok 5</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="censusOwnershipFilter" class="filter-label">Ownership Status</label>
                    <select id="censusOwnershipFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Owner">Owner</option>
                        <option value="Boarder/Rentee">Boarder/Rentee</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="censusCivilStatusFilter" class="filter-label">Civil Status</label>
                    <select id="censusCivilStatusFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widow/er">Widow/er</option>
                        <option value="Separated">Separated</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Census Records</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper census-table-scroll">
                    <table class="census-table">
                        <thead>
                            <tr>
                                <th>Form No.</th>
                                <th>Control No.</th>
                                <th>CY</th>
                                <th>
                                    FULL NAME
                                    <div style="font-size:10px;font-weight:400;opacity:0.75;margin-top:2px;">LN, FN, MN, Suffix</div>
                                </th>
                                <th>House/Block/Lot No.</th>
                                <th>Street/Purok/Sitio</th>
                                <th>Barangay</th>
                                <th>City/Municipality</th>
                                <th>Province</th>
                                <th>Ownership</th>
                                <th>Prov. House No.</th>
                                <th>Prov. Street/Purok</th>
                                <th>Prov. Barangay</th>
                                <th>Prov. City</th>
                                <th>Prov. Province</th>
                                <th>Length of Stay</th>
                                <th>Sex</th>
                                <th>Civil Status</th>
                                <th>Date of Birth</th>
                                <th>Place of Birth</th>
                                <th>Height</th>
                                <th>Weight</th>
                                <th>Contact No.</th>
                                <th>Email</th>
                                <th>Religion</th>
                                <th>Education Level</th>
                                <th>Elementary</th>
                                <th>High School</th>
                                <th>Vocational</th>
                                <th>College</th>
                                <th>Signature</th>
                            </tr>
                        </thead>
                        <tbody id="censusTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Pagination Controls -->
<div class="pagination-container">
    <div class="pagination-info">
        <span id="censusPaginationInfo">Showing 1-5 of 5 records</span>
    </div>
    <div class="pagination-controls">
        <button type="button" id="censusPrevBtn" class="pagination-btn" disabled>Previous</button>
        <div class="pagination-numbers" id="censusPageNumbers"></div>
        <button type="button" id="censusNextBtn" class="pagination-btn">Next</button>
    </div>
</div>

<!-- Upload Census Modal -->
<div class="modal-backdrop census-modal-backdrop" id="censusUploadModal" style="display:none;">
    <div class="modal-box census-modal-box census-modal-animate census-modal-no-border" style="max-width: 700px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #2c2c3e 0%, #3a3a4a 100%); padding: 20px 24px; border-radius: 12px 12px 0 0;">
            <h2 class="modal-title" style="color: white; margin: 0; font-size: 20px; font-weight: 600;">Upload Barangay Census Form</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close" style="color: white; opacity: 0.9;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 32px 24px;">
            <p class="modal-description" style="color: #6b7280; font-size: 14px; margin-bottom: 24px;">
                Upload an Excel file (.xlsx, .xls) containing barangay census data (CMP-04 format).
            </p>
            
            <div class="upload-area" id="censusUploadArea" style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 40px 24px; text-align: center; background: #f9fafb; cursor: pointer; transition: all 0.3s ease;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #6366f1; margin: 0 auto 16px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <p style="font-size: 16px; font-weight: 600; color: #1f2937; margin-bottom: 8px;">Click to upload or drag and drop</p>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 16px;">Excel files only (.xlsx, .xls)</p>
                <input type="file" id="censusFileInput" accept=".xlsx,.xls" class="file-input" style="display: none;">
                <button type="button" class="btn btn-outline" id="censusBrowseBtn" style="margin-top: 8px;">Browse Files</button>
            </div>
            
            <div id="censusFilePreview" style="display: none; margin-top: 20px; padding: 16px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #16a34a; flex-shrink: 0;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <div style="flex: 1; min-width: 0;">
                        <p id="censusFileName" style="font-weight: 600; color: #1f2937; margin: 0 0 4px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></p>
                        <p id="censusFileSize" style="font-size: 13px; color: #6b7280; margin: 0;"></p>
                    </div>
                    <button type="button" id="censusRemoveFileBtn" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="modal-field" style="margin-top: 20px;">
                <p style="font-size: 12px; color: #6b7280; margin: 0; padding: 12px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #6366f1;">
                    <strong style="color: #1f2937;">Required columns:</strong> Form No., Control Number, CY, Last Name, First Name, Middle Name, Present Address, Ownership Status, Civil Status, Date of Birth, etc.
                </p>
            </div>
        </div>
        <div class="modal-footer" style="padding: 16px 24px; background: #f9fafb; border-radius: 0 0 12px 12px;">
            <button type="button" class="btn btn-outline" id="censusUploadCancelBtn" data-modal-close style="display:none;">Cancel</button>
            <button type="button" class="btn census-preview-btn-green" id="censusPreviewBtn" style="display:none;" disabled>Preview Data</button>
        </div>
    </div>
</div>

<!-- Preview Census Data Modal -->
<div class="modal-backdrop census-modal-backdrop" id="censusPreviewModal" style="display:none;">
    <div class="modal-box census-modal-box census-modal-animate census-modal-no-border census-view-modal-wide">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Preview Census Data</h2>
                <span class="census-modal-subtitle">Review the data before uploading</span>
            </div>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body census-view-modal-body" id="censusPreviewBody" style="padding: 20px 24px;">
            {{-- Preview table rendered by JS --}}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="button" class="btn census-upload-btn-green" id="censusUploadConfirmBtn">Upload Data</button>
        </div>
    </div>
</div>


@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Barangay_Census_Form/assets/js/barangay-census-form.js'
])

</body>
</html>
