<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabataan List - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Kabataan/assets/css/kabataan.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container kabataan-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Kabataan List</h1>
                <p class="page-subtitle">
                    Central registry of youth in the Purok / Sitio for SK programs and events.
                </p>
            </div>
            <div class="page-header-right page-header-right-with-search">
                <div class="header-search-wrap kabataan-header-search">
                    <input type="text" id="kabataanSearch" class="filter-input kabataan-search-input" placeholder="Search">
                </div>
                <button type="button" class="btn primary-btn" id="addKabataanBtn">+ Add Kabataan</button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="kabataanGenderFilter" class="filter-label">Sex</label>
                    <select id="kabataanGenderFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="kabataanPurok / SitioFilter" class="filter-label">Purok / Sitio</label>
                    <select id="kabataanPurok / SitioFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="BAYSIDE">BAYSIDE</option>
                        <option value="VILLA GRACIA">VILLA GRACIA</option>
                        <option value="IMELDA">IMELDA</option>
                        <option value="LUPANG PANGAKO">LUPANG PANGAKO</option>
                        <option value="DAMAYAN">DAMAYAN</option>
                        <option value="MARCELO">MARCELO</option>
                        <option value="BIGAYANVILLA ROSA">BIGAYANVILLA ROSA</option>
                        <option value="PHASE3">PHASE3</option>
                        <option value="BIGAYANSANLUIS">BIGAYANSANLUIS</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="kabataanEducationFilter" class="filter-label">Highest Education</label>
                    <select id="kabataanEducationFilter" class="filter-select">
                        <option value="">All</option>
                        <option value="No Formal Education">No Formal Education</option>
                        <option value="Elementary Level">Elementary Level</option>
                        <option value="Elementary Graduate">Elementary Graduate</option>
                        <option value="High School Level">High School Level</option>
                        <option value="High School Graduate">High School Graduate</option>
                        <option value="College Level">College Level</option>
                        <option value="College Graduate">College Graduate</option>
                        <option value="Vocational">Vocational</option>
                        <option value="Postgraduate">Postgraduate</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Registered Youth</h2>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="kabataan-table">
                        <thead>
                            <tr>
                                <th>
                                    Full Name
                                    <div class="column-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok / Sitio</th>
                                <th>Highest Education</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="kabataanTableBody">
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span id="kabataanPaginationInfo">Showing 1-10 of 12 records</span>
                    </div>
                    <div class="pagination-controls">
                        <button type="button" id="kabataanPrevBtn" class="pagination-btn" disabled>Previous</button>
                        <div class="pagination-numbers" id="kabataanPageNumbers"></div>
                        <button type="button" id="kabataanNextBtn" class="pagination-btn">Next</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Kabataan Modal -->
<div class="modal-backdrop kabataan-modal-backdrop" id="kabataanModal" style="display:none;">
    <div class="modal-box kabataan-modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="kabataanModalTitle">Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kabataanModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kabataan-modal-body">
            <!-- Add mode selector (only when adding) -->
            <div class="kabataan-add-mode-selector" id="kabataanAddModeSelector" style="display:none;">
                <p class="kabataan-mode-label">How do you want to add?</p>
                <div class="kabataan-mode-buttons">
                    <button type="button" class="btn kabataan-mode-btn active" data-mode="manual">Manual entry</button>
                    <button type="button" class="btn kabataan-mode-btn" data-mode="bulk">Bulk upload (file)</button>
                </div>
            </div>

            <!-- Panel: Bulk upload -->
            <div class="kabataan-mode-panel kabataan-panel-bulk" id="kabataanPanelBulk" style="display:none;">
                <h3 class="kabataan-block-title">Import from file</h3>
                <p class="kabataan-block-hint">Upload Excel/CSV or paste from Google Sheets. First row = headers.</p>
                <div class="kabataan-import-options">
                    <div class="kabataan-import-option">
                        <label for="kabataanFileInput" class="import-label">Upload Excel or CSV file</label>
                        <input type="file" id="kabataanFileInput" class="import-file-input" accept=".csv,.xlsx,.xls">
                    </div>
                    <div class="kabataan-import-option">
                        <label for="kabataanPasteInput" class="import-label">Or paste from Google Sheets (CSV / tab-separated)</label>
                        <textarea id="kabataanPasteInput" class="import-paste-input" rows="3" placeholder="Paste here..."></textarea>
                        <button type="button" class="btn primary-btn btn-import-paste" id="kabataanImportPasteBtn">Import to table</button>
                    </div>
                </div>
            </div>

            <!-- View-only: label-value rows (like KK Profiling Requests) -->
            <div class="kabataan-view-details" id="kabataanViewDetails" style="display:none;">
                <div class="kabataan-view-columns kabataan-view-columns" id="kabataanViewColumns">
                    <div class="kabataan-view-column-left" id="kabataanViewColumnLeft">
                        <div class="kabataan-view-section-title">Personal Information</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">First Name:</span><span class="kabataan-view-value" id="kabataanViewFirstName"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Middle Name:</span><span class="kabataan-view-value" id="kabataanViewMiddleName"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Last Name:</span><span class="kabataan-view-value" id="kabataanViewLastName"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Suffix:</span><span class="kabataan-view-value" id="kabataanViewSuffix"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Age:</span><span class="kabataan-view-value" id="kabataanViewAge"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Date of Birth:</span><span class="kabataan-view-value" id="kabataanViewDateOfBirth"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Sex:</span><span class="kabataan-view-value" id="kabataanViewSex"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Civil Status:</span><span class="kabataan-view-value" id="kabataanViewCivilStatus"></span></div>
                        
                        <div class="kabataan-view-section-title">Location Information</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Region:</span><span class="kabataan-view-value" id="kabataanViewRegion"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Province:</span><span class="kabataan-view-value" id="kabataanViewProvince"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">City/Municipality:</span><span class="kabataan-view-value" id="kabataanViewCity"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Barangay:</span><span class="kabataan-view-value" id="kabataanViewBarangay"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Purok / Sitio:</span><span class="kabataan-view-value" id="kabataanViewPurokSitio"></span></div>
                    </div>
                    <div class="kabataan-view-column-right" id="kabataanViewColumnRight">
                        <div class="kabataan-view-section-title">Youth Classification / Education</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Youth Classification (ISY/OSY/NEET):</span><span class="kabataan-view-value" id="kabataanViewYouthClassification"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Age Group:</span><span class="kabataan-view-value" id="kabataanViewAgeGroup"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Contact Number:</span><span class="kabataan-view-value" id="kabataanViewContactNumber"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Highest Educational Attainment:</span><span class="kabataan-view-value" id="kabataanViewHighestEducation"></span></div>
                        
                        <div class="kabataan-view-section-title">Work / Other Info</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Work Status:</span><span class="kabataan-view-value" id="kabataanViewWorkStatus"></span></div>
                        
                        <div class="kabataan-view-section-title">Civic Participation</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Registered Voter:</span><span class="kabataan-view-value" id="kabataanViewRegisteredVoter"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Voted Last Election:</span><span class="kabataan-view-value" id="kabataanViewVotedLastElection"></span></div>
                        
                        <div class="kabataan-view-section-title">Additional</div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">SK Participation:</span><span class="kabataan-view-value" id="kabataanViewSKParticipation"></span></div>
                        <div class="kabataan-view-row"><span class="kabataan-view-label">Record ID:</span><span class="kabataan-view-value" id="kabataanViewRecordId"></span></div>
                    </div>
                </div>
            </div>

            <!-- Panel: Manual entry form -->
            <div class="kabataan-mode-panel kabataan-panel-manual" id="kabataanPanelManual">
                <h3 class="kabataan-block-title" id="kabataanManualBlockTitle">Add entry manually</h3>
                <p class="kabataan-block-hint" id="kabataanManualBlockHint">Fill in the fields below.</p>
                <div class="kabataan-form-scroll" id="kabataanFormScroll">
                    <div class="modal-columns kabataan-form-columns">
                        <div class="modal-column">
                            <div class="modal-field"><label for="kabataanFirstName">First Name</label><input type="text" id="kabataanFirstName" placeholder="First name"></div>
                            <div class="modal-field"><label for="kabataanMiddleName">Middle Name</label><input type="text" id="kabataanMiddleName" placeholder="Middle name"></div>
                            <div class="modal-field"><label for="kabataanLastName">Last Name</label><input type="text" id="kabataanLastName" placeholder="Last name"></div>
                            <div class="modal-field"><label for="kabataanSuffix">Suffix</label><select id="kabataanSuffix"><option value="">None</option><option value="Jr.">Jr.</option><option value="Sr.">Sr.</option><option value="II">II</option><option value="III">III</option></select></div>
                            <div class="modal-field"><label for="kabataanDob">Date of Birth</label><input type="text" id="kabataanDob" placeholder="Month/Day/Year"></div>
                            <div class="modal-field"><label for="kabataanAgeInput">Age</label><input type="number" id="kabataanAgeInput" min="15" max="30" placeholder="Age"></div>
                            <div class="modal-field"><label for="kabataanSex">Sex Assigned at Birth</label><select id="kabataanSex"><option value="Male">Male</option><option value="Female">Female</option></select></div>
                            <div class="modal-field"><label for="kabataanCivilStatus">Civil Status</label><select id="kabataanCivilStatus"><option value="Single">Single</option><option value="Married">Married</option><option value="Other">Other</option></select></div>
                            <div class="modal-field"><label for="kabataanRegion">Region</label><input type="text" id="kabataanRegion" placeholder="Region"></div>
                            <div class="modal-field"><label for="kabataanProvince">Province</label><input type="text" id="kabataanProvince" placeholder="Province"></div>
                            <div class="modal-field"><label for="kabataanCity">City/Municipality</label><input type="text" id="kabataanCity" placeholder="City/Municipality"></div>
                            <div class="modal-field"><label for="kabataanPurok / Sitio">Purok / Sitio</label><select id="kabataanPurok / Sitio"><option value="">Select Purok / Sitio</option><option value="Sitio 1">Sitio 1</option><option value="Sitio 2">Sitio 2</option><option value="Sitio 3">Sitio 3</option><option value="Sitio 4">Sitio 4</option><option value="Sitio 5">Sitio 5</option><option value="Sitio 6">Sitio 6</option><option value="Sitio 7">Sitio 7</option><option value="Purok Villa Gracias">Purok Villa Gracias</option><option value="Bayside Calios">Bayside Calios</option></select></div>
                            <div class="modal-field"><label for="kabataanAddress">Address</label><input type="text" id="kabataanAddress" placeholder="Full address"></div>
                        </div>
                        <div class="modal-column">
                            <div class="modal-field"><label for="kabataanEmail">Email</label><input type="email" id="kabataanEmail" placeholder="Email"></div>
                            <div class="modal-field"><label for="kabataanContactInput">Contact Number</label><input type="text" id="kabataanContactInput" placeholder="Contact number"></div>
                            <div class="modal-field"><label for="kabataanHighestEducation">Highest Educational Attainment</label><input type="text" id="kabataanHighestEducation" placeholder="e.g. College"></div>
                            <div class="modal-field"><label for="kabataanCurrentlyStudying">Currently Studying</label><select id="kabataanCurrentlyStudying"><option value="Yes">Yes</option><option value="No">No</option></select></div>
                            <div class="modal-field"><label for="kabataanWorkStatus">Work Status</label><select id="kabataanWorkStatus"><option value="">None</option><option value="Student">Student</option><option value="Employed">Employed</option><option value="Unemployed">Unemployed</option><option value="NEET">NEET</option></select></div>
                            <div class="modal-field"><label for="kabataanOccupation">Occupation</label><select id="kabataanOccupation"><option value="">None</option><option value="Student">Student</option><option value="Employed">Employed</option><option value="Other">Other</option></select></div>
                            <div class="modal-field"><label for="kabataanRegisteredVoter">Registered Voter</label><select id="kabataanRegisteredVoter"><option value="Yes">Yes</option><option value="No">No</option></select></div>
                            <div class="modal-field"><label for="kabataanVotedLastElection">Voted in Last Election</label><select id="kabataanVotedLastElection"><option value="Yes">Yes</option><option value="No">No</option></select></div>
                            <div class="modal-field"><label for="kabataanYouthClassification">Youth Classification</label><select id="kabataanYouthClassification"><option value="ISY">ISY</option><option value="OSY">OSY</option><option value="NEET">NEET</option></select></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer kabataan-modal-footer">
            <button type="button" class="btn primary-btn" id="kabataanSaveBtn">Save</button>
        </div>
    </div>
</div>

<!-- Kabataan View Modal -->
<div class="modal-backdrop kabataan-modal-backdrop" id="kabataanViewModal" style="display:none;">
    <div class="modal-box kabataan-modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="kabataanViewModalTitle">Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kabataanViewModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kabataan-modal-body">
            <div class="kabataan-view-columns kabataan-view-columns" id="kabataanViewModalColumns">
                <div class="kabataan-view-column-left" id="kabataanViewModalColumnLeft">
                    <div class="kabataan-view-section-title">Personal Information</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">First Name:</span><span class="kabataan-view-value" id="kabataanViewModalFirstName"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Middle Name:</span><span class="kabataan-view-value" id="kabataanViewModalMiddleName"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Last Name:</span><span class="kabataan-view-value" id="kabataanViewModalLastName"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Suffix:</span><span class="kabataan-view-value" id="kabataanViewModalSuffix"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Age:</span><span class="kabataan-view-value" id="kabataanViewModalAge"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Date of Birth:</span><span class="kabataan-view-value" id="kabataanViewModalDateOfBirth"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Sex:</span><span class="kabataan-view-value" id="kabataanViewModalSex"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Civil Status:</span><span class="kabataan-view-value" id="kabataanViewModalCivilStatus"></span></div>
                    
                    <div class="kabataan-view-section-title">Location Information</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Region:</span><span class="kabataan-view-value" id="kabataanViewModalRegion"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Province:</span><span class="kabataan-view-value" id="kabataanViewModalProvince"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">City/Municipality:</span><span class="kabataan-view-value" id="kabataanViewModalCity"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Barangay:</span><span class="kabataan-view-value" id="kabataanViewModalBarangay"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Purok / Sitio:</span><span class="kabataan-view-value" id="kabataanViewModalPurokSitio"></span></div>
                </div>
                <div class="kabataan-view-column-right" id="kabataanViewModalColumnRight">
                    <div class="kabataan-view-section-title">Youth Classification / Education</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Youth Classification (ISY/OSY/NEET):</span><span class="kabataan-view-value" id="kabataanViewModalYouthClassification"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Age Group:</span><span class="kabataan-view-value" id="kabataanViewModalAgeGroup"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Contact Number:</span><span class="kabataan-view-value" id="kabataanViewModalContactNumber"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Highest Educational Attainment:</span><span class="kabataan-view-value" id="kabataanViewModalHighestEducation"></span></div>
                    
                    <div class="kabataan-view-section-title">Work / Other Info</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Work Status:</span><span class="kabataan-view-value" id="kabataanViewModalWorkStatus"></span></div>
                    
                    <div class="kabataan-view-section-title">Civic Participation</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Registered Voter:</span><span class="kabataan-view-value" id="kabataanViewModalRegisteredVoter"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Voted Last Election:</span><span class="kabataan-view-value" id="kabataanViewModalVotedLastElection"></span></div>
                    
                    <div class="kabataan-view-section-title">Additional</div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">SK Participation:</span><span class="kabataan-view-value" id="kabataanViewModalSKParticipation"></span></div>
                    <div class="kabataan-view-row"><span class="kabataan-view-label">Record ID:</span><span class="kabataan-view-value" id="kabataanViewModalRecordId"></span></div>
                </div>
            </div>
        </div>
        <div class="modal-footer kabataan-modal-footer">
            <button type="button" class="btn primary-btn" id="kabataanViewModalClose">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop kabataan-delete-backdrop" id="kabataanDeleteModal" style="display:none;">
    <div class="kabataan-delete-box">
        <div class="kabataan-delete-header">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="kabataan-delete-icon">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6l-1 14H6L5 6"></path>
                <path d="M10 11v6"></path>
                <path d="M14 11v6"></path>
                <path d="M9 6V4h6v2"></path>
            </svg>
            <h2 class="kabataan-delete-title">Delete Record</h2>
        </div>
        <div class="kabataan-delete-body">
            <p class="kabataan-delete-message">Are you sure you want to delete</p>
            <p class="kabataan-delete-name" id="kabataanDeleteName"></p>
            <p class="kabataan-delete-warning">This action cannot be undone.</p>
        </div>
        <div class="kabataan-delete-footer">
            <button type="button" class="btn kabataan-cancel-btn" id="kabataanDeleteCancelBtn">Cancel</button>
            <button type="button" class="btn kabataan-confirm-delete-btn" id="kabataanDeleteConfirmBtn">Delete</button>
        </div>
    </div>
</div>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Kabataan/assets/js/kabataan.js'
])

</body>
</html>
