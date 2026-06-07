<div class="schol-modal-overlay" id="surveyFormModal" style="display:none;">
    <div class="schol-modal-box schol-modal-lg" id="surveyFormBox">
        <div class="schol-modal-header">
            <h3 id="surveyFormModalTitle">Create Survey Form</h3>
            <div class="schol-modal-header-actions">
                <button type="button" class="schol-modal-close" id="surveyFormMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="surveyFormModalClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body">
            <div class="schol-form-group survey-form-title-group">
                <label class="schol-label" for="surveyTitle">Survey Title <span class="required">*</span></label>
                <input type="text" id="surveyTitle" class="schol-input" maxlength="200" placeholder="e.g. Clean-Up Drive Feedback Survey">
            </div>
            <div class="schol-form-group">
                <label class="schol-label" for="surveyActivity">Program Activity</label>
                <select id="surveyActivity" class="schol-input"></select>
            </div>
            <div class="schol-form-group">
                <label class="schol-label" for="surveyDescription">Instructions for Kabataan</label>
                <textarea id="surveyDescription" class="schol-input schol-textarea-fixed" rows="2" maxlength="500" placeholder="Announcement shown when youth open the survey…"></textarea>
                <div class="schol-char-count"><span id="surveyDescCount">0</span>/500</div>
            </div>
            <div class="schol-form-row survey-form-dates-row">
                <div class="schol-form-group">
                    <label class="schol-label" for="surveyOpenDate">Survey Open Date <span class="required">*</span></label>
                    <input type="date" id="surveyOpenDate" class="schol-input">
                </div>
                <div class="schol-form-group">
                    <label class="schol-label" for="surveyCloseDate">Survey Close Date <span class="required">*</span></label>
                    <input type="date" id="surveyCloseDate" class="schol-input">
                </div>
                <div class="schol-form-group survey-status-group">
                    <label class="schol-label" for="surveyStatus">Status</label>
                    <select id="surveyStatus" class="schol-input">
                        <option value="scheduled">Scheduled</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
            @include('GForm_Builder::partials.custom-questions-builder', [
                'sectionTitle' => 'Survey Questions',
                'hint' => 'Add questions that Kabataan members will answer when they open this survey.',
                'emptyMessage' => 'No questions yet. Click <strong>Add Question</strong> to start building your survey.',
            ])
        </div>
        <div class="schol-modal-footer">
            <button type="button" class="schol-btn schol-btn-outline" id="surveyFormCancel">Cancel</button>
            <button type="button" class="schol-btn schol-btn-save" id="surveyFormSave">Save Survey</button>
        </div>
    </div>
</div>

<div class="schol-modal-overlay" id="viewSurveyModal" style="display:none;">
    <div class="schol-modal-box schol-modal-md" id="viewSurveyBox">
        <div class="schol-modal-header">
            <h3>Survey Questions Preview</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="schol-modal-close" id="viewSurveyMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="schol-modal-close" id="viewSurveyClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="schol-modal-body" id="viewSurveyBody"></div>
    </div>
</div>

<div class="schol-modal-overlay" id="viewResponseModal" style="display:none;">
    <div class="schol-modal-box schol-modal-md">
        <div class="schol-modal-header">
            <h3>Kabataan Response</h3>
            <button type="button" class="schol-modal-close" id="viewResponseClose" aria-label="Close">&times;</button>
        </div>
        <div class="schol-modal-body" id="viewResponseBody"></div>
    </div>
</div>
