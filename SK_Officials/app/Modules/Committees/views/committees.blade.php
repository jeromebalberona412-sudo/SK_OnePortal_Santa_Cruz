<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committees - SK Officials Portal</title>

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Committees/assets/css/committees.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
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
            <div class="page-header-right page-header-right-desktop">
                <button type="button" class="btn primary-btn" id="addCommitteeBtn">
                    Assign Committee
                </button>
            </div>
        </section>

        <section class="page-filters-section">
            <!-- Mobile: Button and Search on top -->
            <div class="mobile-top-row">
                <button type="button" class="btn primary-btn mobile-assign-btn" id="addCommitteeBtnMobile">
                    Assign Committee
                </button>
                <input type="text" id="committeeSearchMobile" class="filter-input mobile-search" placeholder="Search">
            </div>
            
            <!-- Filters row -->
            <div class="filters-row">
                <div class="filter-item">
                    <label for="committeeNameFilter" class="filter-label">Committee Name</label>
                    <select id="committeeNameFilter" class="filter-select">
                        <option value="">All Committees</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="committeeHeadFilter" class="filter-label">Assigned To</label>
                    <select id="committeeHeadFilter" class="filter-select">
                        <option value="">All Members</option>
                    </select>
                </div>
                <div class="filter-item filter-item-search">
                    <label for="committeeSearch" class="filter-label">Search</label>
                    <div class="filter-input-wrapper">
                        <input type="text" id="committeeSearch" class="filter-input" placeholder="Search by name, member, or description">
                    </div>
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

            <div class="table-card">
                <div class="table-wrapper">
                    <table class="committees-table">
                        <thead>
                            <tr>
                                <th>Committee Name</th>
                                <th>Assigned To</th>
                                <th>Assigned Date</th>
                                <th>Assigned Time</th>
                                <th>Description</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="committeeGrid"></tbody>
                    </table>
                </div>
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
            <div class="modal-field">
                <label for="committeeDescriptionInput">Description</label>
                <textarea id="committeeDescriptionInput" rows="3" placeholder="Enter committee purpose and responsibilities..."></textarea>
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
    <div class="modal-box committee-view-box">
        <div class="modal-header">
            <h2 class="modal-title">Committee Summary</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-view-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body committee-view-body">

            {{-- Committee name banner --}}
            <div class="cv-banner">
                <div class="cv-banner-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="cv-banner-text">
                    <div class="cv-committee-name" id="viewCommitteeName">—</div>
                    <div class="cv-committee-status-row">
                        <span class="cv-status-badge" id="viewCommitteeStatus">Active</span>
                        <span class="cv-date-created" id="viewCommitteeDateCreated"></span>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="cv-info-grid">
                <div class="cv-info-card">
                    <div class="cv-info-label">Committee Head</div>
                    <div class="cv-info-value" id="viewCommitteeHead">—</div>
                </div>
                <div class="cv-info-card">
                    <div class="cv-info-label">Status</div>
                    <div class="cv-info-value" id="viewCommitteeStatusText">Active</div>
                </div>
                <div class="cv-info-card">
                    <div class="cv-info-label">Date Assigned</div>
                    <div class="cv-info-value" id="viewCommitteeDateAssigned">—</div>
                </div>
            </div>

            {{-- Description --}}
            <div class="cv-section">
                <div class="cv-section-title">Description</div>
                <p class="cv-section-body" id="viewCommitteeDescription">—</p>
            </div>

            {{-- Responsibilities --}}
            <div class="cv-section">
                <div class="cv-section-title">Responsibilities</div>
                <p class="cv-section-body" id="viewCommitteeResponsibilities">—</p>
            </div>

        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Committees/assets/js/committees.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>

