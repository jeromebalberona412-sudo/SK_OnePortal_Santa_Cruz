{{-- Scholar application form builder modal (same pattern as sports_requests) --}}
<div class="sports-modal-overlay" id="safCreateFormModal" style="display:none;">
    <div class="sports-modal-box sports-modal-form-builder" id="safCreateFormModalBox">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span id="safModalTitleText">Create Scholar Application Form</span>
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="safFormMaximize" title="Fullscreen" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="safFormClose">&times;</button>
            </div>
        </div>

        <div class="sports-modal-body spfb-modal-body">
            <input type="hidden" id="safEditFormId" value="{{ $formId ?? '' }}">

            <div class="spfb-section-card spfb-section-basic">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    Form Details
                </div>

                <div class="sports-field" style="margin-bottom:14px;">
                    <label for="safFormTitleInput">Form Title <span class="sports-req">*</span></label>
                    <input type="text" id="safFormTitleInput" class="sports-input" maxlength="120"
                           placeholder="e.g., SK Educational Assistance Application 2026">
                </div>

                @include('schedule_programs::partials.committee-head-field', ['inputId' => 'safCommitteeHead'])

                <div class="sports-field-row" style="margin-bottom:14px;">
                    <div class="sports-field">
                        <label for="safStartDate">Start Date <span class="sports-req">*</span></label>
                        <input type="date" id="safStartDate" class="sports-input" autocomplete="off">
                    </div>
                    <div class="sports-field">
                        <label for="safEndDate">End Date <span class="sports-req">*</span></label>
                        <input type="date" id="safEndDate" class="sports-input" autocomplete="off">
                    </div>
                </div>

                <div class="sports-field-row">
                    <div class="sports-field">
                        @include('schedule_programs::partials.time-dropdown-fields', [
                            'prefix' => 'safStartTime',
                            'label' => 'Start Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                    </div>
                    <div class="sports-field">
                        @include('schedule_programs::partials.time-dropdown-fields', [
                            'prefix' => 'safEndTime',
                            'label' => 'End Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                    </div>
                </div>
            </div>

            <div class="spfb-announcement-section">
                <label class="spfb-announcement-label">Announcement <span style="color:#ef4444;">*</span></label>
                <p class="spfb-announcement-hint">This message will be shown to Kabataan members when they open the application form.</p>
                <textarea id="spfbAnnouncement" class="spfb-announcement-textarea" maxlength="500" placeholder="Enter announcement or instructions for applicants..."></textarea>
                <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
            </div>

            <div class="spfb-section-card spfb-section-builder">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Application Form Builder
                    <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
                </div>
                <p class="spfb-builder-hint">Build the application form that Kabataan members will fill out when applying for this scholarship.</p>

                <div id="spfbQuestionList" class="spfb-question-list">
                    <div class="spfb-empty-state" id="spfbEmptyState">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p>No questions yet. Click <strong>Add Question</strong> to start building your form.</p>
                    </div>
                </div>

                <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Question
                </button>
            </div>
        </div>

        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="safFormCancelBtn">Cancel</button>
            <button type="button" class="sports-btn sports-btn-primary" id="safFormSaveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span id="safFormSaveBtnText">Save Form</span>
            </button>
        </div>
    </div>
</div>
