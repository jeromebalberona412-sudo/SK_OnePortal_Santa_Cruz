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
                        <option value="Purok 1">Purok 1</option>
                        <option value="Purok 2">Purok 2</option>
                        <option value="Purok 3">Purok 3</option>
                        <option value="Purok 4">Purok 4</option>
                        <option value="Purok 5">Purok 5</option>
                        <option value="Purok 6">Purok 6</option>
                        <option value="Purok 7">Purok 7</option>
                        <option value="Sitio 1">Sitio 1</option>
                        <option value="Sitio 2">Sitio 2</option>
                        <option value="Sitio 3">Sitio 3</option>
                        <option value="Sitio 4">Sitio 4</option>
                        <option value="Sitio 5">Sitio 5</option>
                        <option value="Sitio 6">Sitio 6</option>
                        <option value="Sitio 7">Sitio 7</option>
                        <option value="Villa Gracias">Villa Gracias</option>
                        <option value="Bayside Calios">Bayside Calios</option>
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
                                    <div class="column-hint">FN, MN, LN, Suffix</div>
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

            <!-- View-only: label-value rows (like image) -->
            <div class="kabataan-view-details" id="kabataanViewDetails" style="display:none;">
                <div class="kabataan-view-columns" id="kabataanViewColumns">
                    <div class="kabataan-view-column-left" id="kabataanViewColumnLeft"></div>
                    <div class="kabataan-view-column-right" id="kabataanViewColumnRight"></div>
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
            <h2 class="modal-title" id="kabataanModalTitle">Kabataan Details</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="kabataanModalToggle" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body kabataan-modal-body">
            <div class="kabataan-view-columns" id="kabataanViewColumns">
                <div class="kabataan-view-column-left" id="kabataanViewColumnLeft"></div>
                <div class="kabataan-view-column-right" id="kabataanViewColumnRight"></div>
            </div>
        </div>
        <div class="modal-footer kabataan-modal-footer">
            <button type="button" class="btn primary-btn" id="kabataanSaveBtn">Save</button>
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
