<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $browserTitle ?? 'Program Evaluation - SK Officials Portal' }}</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_list.css',
        'app/Modules/schedule_programs/assets/css/scholar_evaluation.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-program-key="{{ $programType ?? 'scholarship' }}">

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="sl-page-container schol-page-container">

        @include('schedule_programs::partials.program-page-top', [
            'activeTab' => 'evaluation',
            'pageTitle' => 'Evaluation',
            'pageSubtitle' => $pageSubtitle ?? 'Create and monitor evaluations.',
            'programType' => $programType ?? 'scholarship',
            'programTitle' => $programTitle ?? null,
            'programDescription' => $programDescription ?? null,
        ])

        <!-- Summary Cards -->
        <div class="eval-stats-grid">
            <div class="eval-stat-card eval-stat-blue">
                <div class="eval-stat-top">
                    <span class="eval-stat-value" id="evalStatTotal">0</span>
                    <div class="eval-stat-icon eval-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                </div>
                <span class="eval-stat-label">Total Evaluations</span>
            </div>
            <div class="eval-stat-card eval-stat-orange">
                <div class="eval-stat-top">
                    <span class="eval-stat-value" id="evalStatPending">0</span>
                    <div class="eval-stat-icon eval-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <span class="eval-stat-label">Pending</span>
            </div>
            <div class="eval-stat-card eval-stat-green">
                <div class="eval-stat-top">
                    <span class="eval-stat-value" id="evalStatCompleted">0</span>
                    <div class="eval-stat-icon eval-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
                <span class="eval-stat-label">Completed</span>
            </div>
            <div class="eval-stat-card eval-stat-purple">
                <div class="eval-stat-top">
                    <span class="eval-stat-value" id="evalStatInProgress">0</span>
                    <div class="eval-stat-icon eval-icon-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                    </div>
                </div>
                <span class="eval-stat-label">In Progress</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="eval-action-bar">
            <button type="button" id="btnCreateEvaluation" class="eval-btn eval-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Evaluation
            </button>
            <button type="button" id="btnExportEvaluations" class="eval-btn eval-btn-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export to CSV
            </button>
        </div>

        <!-- Table -->
        <div class="sl-table-card">
            <div class="eval-table-header">
                <h3 class="eval-table-title">Monitor Evaluations</h3>
                <div class="eval-filter-group">
                    <select id="evalFilterStatus" class="eval-filter-select">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <input type="text" id="evalSearchInput" class="eval-search-input" placeholder="Search evaluations...">
                </div>
            </div>
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>Evaluation ID</th>
                            <th>Name</th>
                            <th>Evaluation Type</th>
                            <th>Date Created</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Evaluator</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="evalTableBody"></tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Create Evaluation Modal -->
<div class="sl-modal-overlay" id="createEvalModal" style="display:none;">
    <div class="sl-modal-box" style="max-width:700px;">
        <div class="sl-modal-header">
            <h3>Create New Evaluation</h3>
            <button type="button" class="sl-modal-close" id="createEvalClose">&times;</button>
        </div>
        <div class="sl-modal-body" style="padding:24px;">
            <form id="createEvalForm">
                <input type="hidden" id="evalId">
                <div class="eval-form-grid">
                    <div class="eval-form-field">
                        <label for="evalScholar">Select <span style="color:#ef4444;">*</span></label>
                        <select id="evalScholar" class="eval-input" required>
                            <option value="">— Select —</option>
                        </select>
                    </div>
                    <div class="eval-form-field">
                        <label for="evalType">Evaluation Type <span style="color:#ef4444;">*</span></label>
                        <select id="evalType" class="eval-input" required>
                            <option value="">— Select Type —</option>
                            <option value="Performance">Performance</option>
                            <option value="Attendance">Attendance</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Post-Activity Review">Post-Activity Review</option>
                        </select>
                    </div>
                    <div class="eval-form-field">
                        <label for="evalDueDate">Due Date <span style="color:#ef4444;">*</span></label>
                        <input type="date" id="evalDueDate" class="eval-input" required>
                    </div>
                    <div class="eval-form-field">
                        <label for="evalEvaluator">Assigned Evaluator <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="evalEvaluator" class="eval-input" placeholder="e.g. Juan dela Cruz" required>
                    </div>
                    <div class="eval-form-field eval-form-full">
                        <label for="evalNotes">Notes / Instructions</label>
                        <textarea id="evalNotes" class="eval-textarea" rows="4" placeholder="Enter notes..."></textarea>
                    </div>
                </div>
                <div class="eval-form-actions">
                    <button type="button" class="eval-btn eval-btn-secondary" id="btnCancelEval">Cancel</button>
                    <button type="submit" class="eval-btn eval-btn-primary" id="btnSaveEval">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Evaluation Modal -->
<div class="sl-modal-overlay" id="viewEvalModal" style="display:none;">
    <div class="sl-modal-box" style="max-width:700px;">
        <div class="sl-modal-header">
            <h3>Evaluation Details</h3>
            <button type="button" class="sl-modal-close" id="viewEvalClose">&times;</button>
        </div>
        <div class="sl-modal-body" id="viewEvalBody" style="padding:24px;"></div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_evaluation.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>

