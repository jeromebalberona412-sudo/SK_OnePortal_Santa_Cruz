{{-- Shared evaluation UI for scholarship, sports, and other schedule-based programs --}}

<div class="eval-stats-grid">
    <div class="eval-stat-card eval-stat-blue">
        <div class="eval-stat-top">
            <span class="eval-stat-value" id="evalStatTotal">0</span>
            <div class="eval-stat-icon eval-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
        </div>
        <span class="eval-stat-label">Total Evaluations</span>
    </div>
    <div class="eval-stat-card eval-stat-orange">
        <div class="eval-stat-top">
            <span class="eval-stat-value" id="evalStatDraft">0</span>
            <div class="eval-stat-icon eval-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <span class="eval-stat-label">Draft</span>
    </div>
    <div class="eval-stat-card eval-stat-green">
        <div class="eval-stat-top">
            <span class="eval-stat-value" id="evalStatActive">0</span>
            <div class="eval-stat-icon eval-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
        <span class="eval-stat-label">Active</span>
    </div>
    <div class="eval-stat-card eval-stat-purple">
        <div class="eval-stat-top">
            <span class="eval-stat-value" id="evalStatClosed">0</span>
            <div class="eval-stat-icon eval-icon-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
            </div>
        </div>
        <span class="eval-stat-label">Closed</span>
    </div>
</div>

<div class="eval-action-bar">
    <button type="button" id="btnCreateEvaluation" class="eval-btn eval-btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Create Evaluation
    </button>
    <button type="button" id="btnExportEvaluations" class="eval-btn eval-btn-green">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export to CSV
    </button>
</div>

<div class="eval-table-toolbar">
    <div class="eval-filter-group">
        <select id="evalFilterStatus" class="eval-filter-select" aria-label="Filter by status">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="closed">Closed</option>
        </select>
        <input type="text" id="evalSearchInput" class="eval-search-input" placeholder="Search evaluations..." aria-label="Search evaluations">
    </div>
</div>

<div class="sl-table-card">
    <div class="sl-table-wrapper">
        <table class="sl-table">
            <thead>
                <tr>
                    <th>Evaluation ID</th>
                    <th>Title</th>
                    <th>Program</th>
                    <th>Date Created</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Questions</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="evalTableBody"></tbody>
        </table>
    </div>
</div>

<div class="sl-modal-overlay" id="createEvalModal" style="display:none;">
    <div class="sl-modal-box eval-create-modal-box" id="createEvalModalBox">
        <div class="sl-modal-header">
            <h3 id="createEvalModalTitle">Create Evaluation</h3>
            <div class="eval-modal-window-controls">
                <button type="button" class="sl-modal-close eval-modal-toggle-btn" id="createEvalMaximize" title="Maximize" aria-label="Maximize">□</button>
                <button type="button" class="sl-modal-close" id="createEvalClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="sl-modal-body eval-create-modal-body">
            <form id="createEvalForm">
                <input type="hidden" id="evalId">

                <div class="eval-form-grid">
                    <div class="eval-form-field eval-form-full">
                        <label for="evalTitle">Evaluation Title <span class="eval-required">*</span></label>
                        <input type="text" id="evalTitle" class="eval-input" placeholder="e.g. Midterm Program Evaluation" required>
                    </div>

                    <div class="eval-form-field">
                        <label for="evalProgram">Linked Program</label>
                        <select id="evalProgram" class="eval-input">
                            <option value="">— General / No specific program —</option>
                        </select>
                    </div>

                    <div class="eval-form-field">
                        <label for="evalDueDate">Due Date</label>
                        <input type="date" id="evalDueDate" class="eval-input">
                    </div>

                    <div class="eval-form-field">
                        <label for="evalStatus">Status</label>
                        <select id="evalStatus" class="eval-input">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>

                    <div class="eval-form-field eval-form-full">
                        <label for="evalInstructions">Instructions</label>
                        <textarea id="evalInstructions" class="eval-textarea" rows="3" placeholder="Instructions for evaluators or participants..."></textarea>
                    </div>
                </div>

                <div class="eval-gform-section">
                    @include('GForm_Builder::partials.custom-questions-builder', [
                        'sectionTitle' => 'Evaluation Questions',
                        'hint' => 'Build your evaluation form the same way as program application forms.',
                    ])
                </div>

                <div class="eval-form-actions">
                    <button type="button" class="eval-btn eval-btn-secondary" id="btnCancelEval">Cancel</button>
                    <button type="submit" class="eval-btn eval-btn-primary" id="btnSaveEval">Save Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="sl-modal-overlay" id="viewEvalModal" style="display:none;">
    <div class="sl-modal-box eval-create-modal-box">
        <div class="sl-modal-header">
            <h3>Evaluation Details</h3>
            <button type="button" class="sl-modal-close" id="viewEvalClose" aria-label="Close">&times;</button>
        </div>
        <div class="sl-modal-body" id="viewEvalBody" style="padding:24px;"></div>
    </div>
</div>
