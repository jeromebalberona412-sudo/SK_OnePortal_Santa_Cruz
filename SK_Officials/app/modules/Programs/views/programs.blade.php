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

            <div class="content-wrapper">
                {{-- LEFT: Programs Table --}}
                <div class="main-content-area">
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

                {{-- RIGHT: Program Overview Sidebar --}}
                <aside class="programs-sidebar">
                    <div class="sidebar-card">
                        <h2 class="sidebar-title">Program Overview</h2>
                        <p class="sidebar-subtitle">Summary of all SK programs</p>
                        <div class="program-summary">
                            <div class="summary-item">
                                <div class="summary-icon total">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100 4h2a1 1 0 100 2 2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Total Programs</h3>
                                    <p class="summary-value" id="summaryTotalPrograms">0</p>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-icon planned">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Planned</h3>
                                    <p class="summary-value" id="summaryPlanned">0</p>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-icon ongoing">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Ongoing</h3>
                                    <p class="summary-value" id="summaryOngoing">0</p>
                                </div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-icon completed">
                                    <svg viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Completed</h3>
                                    <p class="summary-value" id="summaryCompleted">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</main>

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

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Programs/assets/js/programs.js'
])

</body>
</html>

