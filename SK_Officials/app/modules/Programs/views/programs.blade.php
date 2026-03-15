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
                    <div class="table-header-row">
                        <span class="table-title">All Programs</span>
                    </div>

                    <div class="table-wrapper">
                        <table class="programs-table">
                            <thead>
                                <tr>
                                    <th>Program Title</th>
                                    <th>Committee</th>
                                    <th>Budget</th>
                                    <th>Duration</th>
                                    <th>Status</th>
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
                    <p class="summary-note">
                        This is a UI-only view. Data will later be connected to your Laravel backend and database.
                    </p>
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
            <button type="button" class="modal-close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="programTitleInput">Program Title</label>
                <input type="text" id="programTitleInput" placeholder="e.g. Youth Leadership Training">
            </div>
            <div class="modal-field">
                <label for="programCommitteeInput">Committee Responsible</label>
                <input type="text" id="programCommitteeInput" placeholder="e.g. Education">
            </div>
            <div class="modal-field">
                <label for="programBudgetInput">Budget (₱)</label>
                <input type="number" id="programBudgetInput" min="0" step="1" placeholder="e.g. 50000">
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

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Programs/assets/js/programs.js'
])

</body>
</html>

