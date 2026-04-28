<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Census Form - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Barangay_Census_Form/assets/css/barangay-census-form.css'
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
                <button type="button" class="btn primary-btn" id="censusUploadBtn">
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
                <div class="table-wrapper">
                    <table class="census-table">
                        <thead>
                            <tr>
                                <th>Form No.</th>
                                <th>Control Number</th>
                                <th>Full Name</th>
                                <th>Purok/Sitio</th>
                                <th>Ownership</th>
                                <th>Civil Status</th>
                                <th>Date of Birth</th>
                                <th class="col-actions">Actions</th>
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
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="button" class="btn primary-btn" id="censusPreviewBtn" disabled>Preview Data</button>
        </div>
    </div>
</div>

<!-- Preview Census Data Modal -->
<div class="modal-backdrop census-modal-backdrop" id="censusPreviewModal" style="display:none;">
    <div class="modal-box census-modal-box census-modal-animate census-modal-no-border" style="max-width: 1200px;">
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
        <div class="modal-body census-view-modal-body">
            <div id="censusPreviewStats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="padding: 16px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <p style="font-size: 13px; color: #6b7280; margin: 0 0 4px 0;">Total Records</p>
                    <p id="previewTotalRecords" style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0;">0</p>
                </div>
                <div style="padding: 16px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #22c55e;">
                    <p style="font-size: 13px; color: #6b7280; margin: 0 0 4px 0;">Valid Records</p>
                    <p id="previewValidRecords" style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0;">0</p>
                </div>
                <div style="padding: 16px; background: #fef2f2; border-radius: 8px; border-left: 4px solid #ef4444;">
                    <p style="font-size: 13px; color: #6b7280; margin: 0 0 4px 0;">Invalid Records</p>
                    <p id="previewInvalidRecords" style="font-size: 24px; font-weight: 700; color: #1f2937; margin: 0;">0</p>
                </div>
            </div>
            
            <div class="table-card">
                <div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
                    <table class="census-table">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Form No.</th>
                                <th>Control Number</th>
                                <th>Full Name</th>
                                <th>Purok/Sitio</th>
                                <th>Ownership</th>
                                <th>Civil Status</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="censusPreviewTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="button" class="btn primary-btn" id="censusUploadConfirmBtn">Upload Data</button>
        </div>
    </div>
</div>

<!-- View Census Modal -->
<div class="modal-backdrop census-modal-backdrop" id="censusViewModal" style="display:none;">
    <div class="modal-box census-modal-box census-modal-animate census-modal-no-border census-view-modal-wide">
        <div class="modal-header">
            <div>
                <h2 class="modal-title">Barangay Census Form (CMP-04)</h2>
                <span class="census-modal-subtitle">Please write LEGIBLY (Pakisulat ng Mababasa)</span>
            </div>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body census-view-modal-body census-form-body">
            
            {{-- Header Info --}}
            <div class="census-form-header">
                <div class="census-form-field-inline">
                    <span class="census-label">Form No.:</span>
                    <span class="census-value" id="viewFormNo"></span>
                </div>
                <div class="census-form-field-inline">
                    <span class="census-label">Control Number:</span>
                    <span class="census-value" id="viewControlNumber"></span>
                </div>
                <div class="census-form-field-inline">
                    <span class="census-label">CY:</span>
                    <span class="census-value" id="viewCY"></span>
                </div>
            </div>

            {{-- Name Section --}}
            <div class="census-section-title">NAME</div>
            <div class="census-name-row">
                <div class="census-name-col">
                    <span class="census-value census-underline" id="viewLastName"></span>
                    <span class="census-col-label">Last Name</span>
                </div>
                <div class="census-name-col">
                    <span class="census-value census-underline" id="viewFirstName"></span>
                    <span class="census-col-label">First Name</span>
                </div>
                <div class="census-name-col">
                    <span class="census-value census-underline" id="viewMiddleName"></span>
                    <span class="census-col-label">Middle Name</span>
                </div>
            </div>

            {{-- Present Address --}}
            <div class="census-section-title">PRESENT ADDRESS</div>
            <div class="census-address-row">
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewHouseNo"></span>
                    <span class="census-col-label">House/Block/Lot No.</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewStreet"></span>
                    <span class="census-col-label">St./Purok/Sitio/Subd.</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewBarangay"></span>
                    <span class="census-col-label">Barangay</span>
                </div>
            </div>
            <div class="census-address-row">
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewCity"></span>
                    <span class="census-col-label">City/Municipality</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvince"></span>
                    <span class="census-col-label">Province</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewOwnership"></span>
                    <span class="census-col-label">Owner / Boarder/Rentee</span>
                </div>
            </div>

            {{-- Provincial Address --}}
            <div class="census-section-title">PROVINCIAL ADDRESS</div>
            <div class="census-address-row">
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvHouseNo"></span>
                    <span class="census-col-label">House/Block/Lot No.</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvStreet"></span>
                    <span class="census-col-label">St./Purok/Sitio/Subd.</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvBarangay"></span>
                    <span class="census-col-label">Barangay</span>
                </div>
            </div>
            <div class="census-address-row">
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvCity"></span>
                    <span class="census-col-label">City/Municipality</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewProvProvince"></span>
                    <span class="census-col-label">Province</span>
                </div>
                <div class="census-address-col">
                    <span class="census-value census-underline" id="viewLengthOfStay"></span>
                    <span class="census-col-label">Length of Stay</span>
                </div>
            </div>

            {{-- Personal Details --}}
            <div class="census-section-title">PERSONAL DETAILS</div>
            <div class="census-details-grid">
                <div class="census-detail-field">
                    <span class="census-label">Sex:</span>
                    <span class="census-value" id="viewSex"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Civil Status:</span>
                    <span class="census-value" id="viewCivilStatus"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Date of Birth:</span>
                    <span class="census-value" id="viewDateOfBirth"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Place of Birth:</span>
                    <span class="census-value" id="viewPlaceOfBirth"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Height:</span>
                    <span class="census-value" id="viewHeight"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Weight:</span>
                    <span class="census-value" id="viewWeight"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Contact Number:</span>
                    <span class="census-value" id="viewContactNumber"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">E-Mail Address:</span>
                    <span class="census-value" id="viewEmail"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Religion:</span>
                    <span class="census-value" id="viewReligion"></span>
                </div>
                <div class="census-detail-field">
                    <span class="census-label">Level of Education:</span>
                    <span class="census-value" id="viewEducationLevel"></span>
                </div>
            </div>

            {{-- Educational Attainment --}}
            <div class="census-section-title">EDUCATIONAL ATTAINMENT</div>
            <div class="census-education-grid">
                <div class="census-education-item">
                    <span class="census-label">Elementary:</span>
                    <span class="census-value" id="viewElementary"></span>
                </div>
                <div class="census-education-item">
                    <span class="census-label">High School:</span>
                    <span class="census-value" id="viewHighSchool"></span>
                </div>
                <div class="census-education-item">
                    <span class="census-label">Vocational:</span>
                    <span class="census-value" id="viewVocational"></span>
                </div>
                <div class="census-education-item">
                    <span class="census-label">College/Course:</span>
                    <span class="census-value" id="viewCollege"></span>
                </div>
            </div>

            {{-- Employment Record --}}
            <div class="census-section-title">EMPLOYMENT RECORD</div>
            <div id="viewEmploymentRecords" class="census-employment-list">
            </div>

            {{-- Other House Occupants --}}
            <div class="census-section-title">OTHER HOUSE OCCUPANTS</div>
            <div id="viewHouseOccupants" class="census-occupants-list">
            </div>

            {{-- Character References --}}
            <div class="census-section-title">CHARACTER REFERENCES</div>
            <div id="viewCharacterReferences" class="census-references-list">
            </div>

            {{-- Vehicles --}}
            <div class="census-section-title">VEHICLE/S</div>
            <div id="viewVehicles" class="census-vehicles-list">
            </div>

            {{-- Declaration --}}
            <div class="census-declaration">
                <p>I swear under the penalty of perjury that all information written above are true and correct and in my own free will.</p>
                <div class="census-signature-field">
                    <span class="census-value census-underline" id="viewSignature"></span>
                    <span class="census-col-label">Head of the Family/Representative's Signature over Printed Name</span>
                </div>
            </div>

        </div>
        <div class="modal-footer">
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Barangay_Census_Form/assets/js/barangay-census-form.js'
])

</body>
</html>
