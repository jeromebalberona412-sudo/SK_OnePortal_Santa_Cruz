<!-- ══════════════════════════════════════════════════════════════
     Create Sports Program Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="createProgramModal" style="display:none;">
    <div class="sports-modal-box sports-modal-form-builder">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Sports Program
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="createProgramMaximize" title="Fullscreen" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="createProgramClose">&times;</button>
            </div>
        </div>

        <div class="sports-modal-body spfb-modal-body">

            <!-- ── Section 1: Program Basic Fields ── -->
            <div class="spfb-section-card spfb-section-basic">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                    Program Details
                </div>

                <div class="sports-field" style="margin-bottom:14px;">
                    <label for="programName">Program Name <span class="sports-req">*</span></label>
                    <select id="programName" class="sports-input" style="cursor:pointer;">
                        <option value="" disabled selected>Select program</option>
                        <option value="Basketball">Basketball</option>
                        <option value="Volleyball">Volleyball</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="text" id="programNameOther" class="sports-input" placeholder="Enter program name..." maxlength="150" style="display:none;margin-top:8px;">
                    <span class="spfb-field-error" id="programNameError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                </div>

                <div class="sports-field-row" style="margin-bottom:14px;">
                    <div class="sports-field">
                        <label for="startDate">Start Date <span class="sports-req">*</span></label>
                        <input type="date" id="startDate" class="sports-input" autocomplete="off">
                        <span class="spfb-field-error" id="startDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                    <div class="sports-field">
                        <label for="endDate">End Date <span class="sports-req">*</span></label>
                        <input type="date" id="endDate" class="sports-input" autocomplete="off">
                        <span class="spfb-field-error" id="endDateError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                </div>

                <div class="sports-field-row">
                    <div class="sports-field">
                        @include('schedule_programs::partials.time-dropdown-fields', [
                            'prefix' => 'startTime',
                            'label' => 'Start Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                        <span class="spfb-field-error" id="startTimeError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                    <div class="sports-field">
                        @include('schedule_programs::partials.time-dropdown-fields', [
                            'prefix' => 'endTime',
                            'label' => 'End Time',
                            'required' => true,
                            'inputClass' => 'sports-input',
                        ])
                        <span class="spfb-field-error" id="endTimeError" style="display:none;font-size:11px;color:#ef4444;margin-top:3px;"></span>
                    </div>
                </div>
            </div>

            <!-- ── Section 1.5: Announcement ── -->
            <div class="spfb-announcement-section">
                <label class="spfb-announcement-label">Announcement <span style="color:#ef4444;">*</span></label>
                <p class="spfb-announcement-hint">This message will be shown to Kabataan members when they open the application form.</p>
                <textarea id="spfbAnnouncement" class="spfb-announcement-textarea" maxlength="500" placeholder="Enter announcement or instructions for applicants..."></textarea>
                <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
            </div>

            <!-- ── Section 2: Question Builder ── -->
            <div class="spfb-section-card spfb-section-builder">
                <div class="spfb-section-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Application Form Builder
                    <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
                </div>
                <p class="spfb-builder-hint">Build the application form that Kabataan members will fill out when applying for this program.</p>

                <!-- Question List -->
                <div id="spfbQuestionList" class="spfb-question-list">
                    <div class="spfb-empty-state" id="spfbEmptyState">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <p>No questions yet. Click <strong>Add Question</strong> to start building your form.</p>
                    </div>
                </div>

                <!-- Add Question Button -->
                <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Question
                </button>
            </div>

        </div><!-- /.spfb-modal-body -->

        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="programCancelBtn">Cancel</button>
            <button type="button" class="sports-btn sports-btn-primary" id="programSaveBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Create Program
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     View Application Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="viewModal" style="display:none;">
    <div class="sports-modal-box sports-modal-xl" id="viewBox">
        <div class="sports-modal-header">
            <h3>Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="viewMaximize" title="Maximize" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="viewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sports-modal-body" id="viewModalBody" style="background:#f0f1f5;">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-danger" id="btnReject">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
            <button type="button" class="sports-btn sports-btn-success" id="btnApprove">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     Rejection Reason Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="rejectReasonModal" style="display:none;">
    <div class="sports-modal-box sports-modal-md">
        <div class="sports-modal-header sports-modal-header-danger">
            <h3>Rejection Reason</h3>
            <button type="button" class="sports-modal-close" id="rejectReasonClose">&times;</button>
        </div>
        <div class="sports-modal-body">
            <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:14px;">Select the reason(s) for rejecting this application. Selecting <strong>Other</strong> will clear all other selections.</p>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Incomplete Requirements" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Incomplete Requirements</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Invalid Documents" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Invalid Documents</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Does Not Meet Age Criteria" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Does Not Meet Age Criteria</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Duplicate Application" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Duplicate Application</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;transition:border-color 0.15s;">
                    <input type="checkbox" class="reject-reason-checkbox" value="Late Submission" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span>Late Submission</span>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;color:#374151;padding:8px 12px;border-radius:8px;border:1.5px solid #ef4444;background:#fff5f5;transition:border-color 0.15s;">
                    <input type="checkbox" id="rejectReasonOtherCheckbox" class="reject-reason-checkbox" value="Other" style="cursor:pointer;width:15px;height:15px;flex-shrink:0;">
                    <span style="font-weight:600;color:#b91c1c;">Other (specify below)</span>
                </label>
            </div>

            <div id="rejectReasonOtherField" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                    <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.04em;">Specify Reason <span style="color:#ef4444;">*</span></label>
                    <span id="rejectOtherCharCount" style="font-size:11px;color:#9ca3af;font-weight:600;">0 / 500</span>
                </div>
                <textarea id="rejectReasonOtherText" class="sports-input" maxlength="500" placeholder="Enter reason for rejection..." style="width:100%;resize:none;height:90px;min-height:90px;max-height:90px;overflow-y:auto;"></textarea>
            </div>

            <!-- Inline validation error -->
            <div id="rejectInlineError" style="display:none;margin-top:10px;padding:9px 12px;background:#fee2e2;border:1.5px solid #fca5a5;border-radius:8px;font-size:12px;font-weight:600;color:#b91c1c;">
            </div>
        </div>
        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="rejectReasonCancel">Cancel</button>
            <button type="button" class="sports-btn sports-btn-danger" id="rejectReasonConfirm">Confirm Rejection</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     Created Sports Programs List Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="createdProgramsModal" style="display:none;">
    <div class="sports-modal-box sports-modal-xl" id="createdProgramsBox">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                Created Sports Programs
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="createdProgramsMaximize" title="Fullscreen" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="createdProgramsClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sports-modal-body" style="background:#f0f1f5;padding:20px;">
            <div class="sports-table-card">
                <div class="sports-table-wrap">
                    <table class="sports-table">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Questions</th>
                                <th>Date Created</th>
                                <th>Time Created</th>
                                <th class="col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="createdProgramsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="sports-modal-footer"></div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     View Program Details Modal
     ══════════════════════════════════════════════════════════════ -->
<div class="sports-modal-overlay" id="viewProgramModal" style="display:none;">
    <div class="sports-modal-box sports-modal-xl" id="viewProgramBox">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Program Details
            </h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="sports-modal-close" id="viewProgramMaximize" title="Fullscreen" style="font-size:16px;padding:2px 8px;opacity:0.85;">□</button>
                <button type="button" class="sports-modal-close" id="viewProgramClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="sports-modal-body" id="viewProgramBody" style="background:#f0f1f5;padding:20px;"></div>
        <div class="sports-modal-footer"></div>
    </div>
</div>

<!-- Toast Notification -->
<div class="sports-toast" id="sportsToast" style="display:none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="sportsToastMsg"></span>
</div>
