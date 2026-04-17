<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committees - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Committees/assets/css/committees.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="page-container committees-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Committees</h1>
                <p class="page-subtitle">
                    View the official SK committee structure and member assignments.
                </p>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn primary-btn" id="addCommitteeBtn">
                    + Assign Committee
                </button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="committeeSearch" class="filter-label">Search committee</label>
                    <div class="filter-input-wrapper">
                        <input type="text" id="committeeSearch" class="filter-input" placeholder="Search by name or member">
                    </div>
                </div>
                <div class="filter-item">
                    <label for="committeeHeadFilter" class="filter-label">Committee head</label>
                    <select id="committeeHeadFilter" class="filter-select">
                        <option value="">All heads</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Committee Directory</h2>
                <p class="section-description">
                    Each committee represents a core area of youth development in the barangay.
                </p>
            </div>

            <div class="table-wrapper">
                <table class="committee-table">
                    <thead>
                        <tr>
                            <th>Committee Name</th>
                            <th>Assigned To</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="committeeGrid"></tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- Committees Modal (UI-only, handled by committees.js) -->
<div class="modal-backdrop" id="committeeModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Assign Committee</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="committeeNameInput">Committee Name</label>
                <select id="committeeNameInput">
                    <option value="">Select Committee</option>
                    <option value="Committee on Peace and Order">Committee on Peace and Order</option>
                    <option value="Committee on Health">Committee on Health</option>
                    <option value="Committee on Education">Committee on Education</option>
                    <option value="Committee on Environment">Committee on Environment</option>
                    <option value="Committee on Social Services">Committee on Social Services</option>
                    <option value="Committee on Livelihood / Employment">Committee on Livelihood / Employment</option>
                    <option value="Committee on Infrastructure">Committee on Infrastructure</option>
                    <option value="Committee on Budget and Finance">Committee on Budget and Finance</option>
                    <option value="Committee on Women and Family">Committee on Women and Family</option>
                    <option value="Committee on Youth and Sports Development">Committee on Youth and Sports Development</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="modal-field" id="otherCommitteeField" style="display: none;">
                <label for="otherCommitteeInput">Specify Committee Name</label>
                <input type="text" id="otherCommitteeInput" placeholder="Enter committee name">
            </div>
            <div class="modal-field">
                <label for="committeeHeadInput">Committee Head</label>
                <select id="committeeHeadInput">
                    <option value="">Select Committee Head</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-modal-cancel>Cancel</button>
            <button type="button" class="btn primary-btn" id="committeeSaveBtn">Save</button>
        </div>
    </div>
</div>

<!-- Committee View Modal -->
<div class="modal-backdrop" id="committeeViewModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Committee Summary</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-view-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-field"><label>Assigned To</label><input type="text" id="viewCommitteeHead" readonly></div>
        </div>
        <!-- Footer intentionally removed (use top-right close button) -->
    </div>
</div>

<!-- Success Modal -->
<div class="modal-backdrop" id="committeeSuccessModal" style="display:none;">
    <div class="modal-box success-modal-box">
        <div class="modal-header success-modal-header">
            <h2 class="modal-title">Success</h2>
            <button type="button" class="modal-close" data-success-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="success-modal-message" id="committeeSuccessMessage">Add successful.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn primary-btn" data-success-close>OK</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Committees/assets/js/committees.js'
])

</body>
</html>

