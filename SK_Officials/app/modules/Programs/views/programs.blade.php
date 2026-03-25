<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Programs/assets/css/programs.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="page-container programs-page">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Programs</h1>
                <p class="page-subtitle">
                    Plan and track major SK initiatives, budgets, and timelines.
                </p>
            </div>
            <div class="page-header-right">
                <button type="button" class="btn primary-btn" id="addProgramBtn">
                    + Add Program
                </button>
            </div>
        </section>

        <section class="page-filters-section">
            <div class="filters-row">
                <div class="filter-item">
                    <label for="programSearch" class="filter-label">Search program</label>
                    <div class="filter-input-wrapper">
                        <input type="text" id="programSearch" class="filter-input" placeholder="Search by title or objective">
                    </div>
                </div>

                <div class="filter-item">
                    <label for="programCommitteeFilter" class="filter-label">Committee</label>
                    <select id="programCommitteeFilter" class="filter-select">
                        <option value="">All committees</option>
                        <option value="education">Education</option>
                        <option value="health">Health</option>
                        <option value="sports">Sports Development</option>
                        <option value="environment">Environment</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="programStatusFilter" class="filter-label">Status</label>
                    <select id="programStatusFilter" class="filter-select">
                        <option value="">All statuses</option>
                        <option value="planned">Planned</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Program Portfolio</h2>
                <p class="section-description">
                    Programs represent long-term youth development initiatives implemented by the SK.
                </p>
            </div>

            <div class="programs-layout">
                <div class="programs-table-card">
                    <div class="table-wrapper">
                        <table class="programs-table">
                            <thead>
                                <tr>
                                    <th>Program Title</th>
                                    <th>Committee</th>
                                    <th>Budget</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="programTableBody">
                                <!-- Programs rendered by programs.js (UI-only, mock data) -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="programs-summary-card">
                    <h3 class="summary-title">Program Overview</h3>
                    <ul class="summary-list">
                        <li>
                            <span class="summary-label">Total Programs</span>
                            <span class="summary-value" id="summaryTotalPrograms">0</span>
                        </li>
                        <li>
                            <span class="summary-label">Planned</span>
                            <span class="summary-pill planned" id="summaryPlanned">0</span>
                        </li>
                        <li>
                            <span class="summary-label">Ongoing</span>
                            <span class="summary-pill ongoing" id="summaryOngoing">0</span>
                        </li>
                        <li>
                            <span class="summary-label">Completed</span>
                            <span class="summary-pill completed" id="summaryCompleted">0</span>
                        </li>
                    </ul>
                </aside>
            </div>
        </section>
    </div>
</main>

<!-- Programs Modal (UI-only, handled by programs.js) -->
<div class="modal-backdrop" id="programModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Add Program</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="programCommitteeInput">Program Type</label>
                <select id="programCommitteeInput">
                    <option value="" selected disabled>Select Program Type</option>
                    <option value="Youth Development Program">Youth Development Program</option>
                    <option value="Education Support Program">Education Support Program</option>
                    <option value="Sports Development Program">Sports Development Program</option>
                    <option value="Environmental Program">Environmental Program</option>
                    <option value="Health and Wellness Program">Health and Wellness Program</option>
                    <option value="Livelihood and Employment Program">Livelihood and Employment Program</option>
                    <option value="Gender and Development (GAD) Program">Gender and Development (GAD) Program</option>
                    <option value="Peace and Order Program">Peace and Order Program</option>
                    <option value="Culture and Arts Program">Culture and Arts Program</option>
                    <option value="ICT and Digital Literacy Program">ICT and Digital Literacy Program</option>
                    <option value="Disaster Risk Reduction Program">Disaster Risk Reduction Program</option>
                    <option value="Volunteerism Program">Volunteerism Program</option>
                    <option value="Entrepreneurship Program">Entrepreneurship Program</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="modal-field">
                <label for="programNameInput">Specify Program Name</label>
                <input type="text" id="programNameInput" placeholder="Enter program name">
            </div>
            <div class="modal-field">
                <label for="programTitleInput">Program Title/Theme</label>
                <input type="text" id="programTitleInput" placeholder="e.g. Youth Leadership Training">
            </div>
            <div class="modal-field">
                <label for="programBudgetInput">Budget (₱)</label>
                <input type="text" id="programBudgetInput" inputmode="numeric" placeholder="e.g. 50,000">
            </div>
            <div class="modal-field">
                <label for="programStartInput">Start Date</label>
                <input type="date" id="programStartInput">
            </div>
            <div class="modal-field">
                <label for="programEndInput">End Date</label>
                <input type="date" id="programEndInput">
            </div>
            <div class="modal-field">
                <label for="programStatusInput">Status</label>
                <select id="programStatusInput">
                    <option value="planned">Planned</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-modal-cancel>Cancel</button>
            <button type="button" class="btn primary-btn" id="programSaveBtn">Save</button>
        </div>
    </div>
</div>

<!-- Program View Modal -->
<div class="modal-backdrop" id="programViewModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Program Summary</h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" data-modal-toggle aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-view-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label>Program Type</label>
                <input type="text" id="viewProgramType" readonly>
            </div>
            <div class="modal-field">
                <label>Program Name</label>
                <input type="text" id="viewProgramName" readonly>
            </div>
            <div class="modal-field">
                <label>Program Title/Theme</label>
                <input type="text" id="viewProgramTitle" readonly>
            </div>
            <div class="modal-field">
                <label>Budget</label>
                <input type="text" id="viewProgramBudget" readonly>
            </div>
            <div class="modal-field">
                <label>Duration</label>
                <input type="text" id="viewProgramDuration" readonly>
            </div>
            <div class="modal-field">
                <label>Status</label>
                <input type="text" id="viewProgramStatus" readonly>
            </div>
        </div>
        <!-- Footer intentionally removed (use top-right close button) -->
    </div>
</div>

<!-- Success Modal -->
<div class="modal-backdrop" id="programSuccessModal" style="display:none;">
    <div class="modal-box success-modal-box">
        <div class="modal-header success-modal-header">
            <h2 class="modal-title">Success</h2>
            <button type="button" class="modal-close" data-success-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="success-modal-message" id="programSuccessMessage">Add successful.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn primary-btn" data-success-close>OK</button>
        </div>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Programs/assets/js/programs.js'
])

</body>
</html>

