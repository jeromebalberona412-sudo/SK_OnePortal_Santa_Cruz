<!-- Application Form Builder Section -->
<div class="schol-schedule-card" style="margin-bottom:20px;">
    <h4 class="schol-schedule-title" style="margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Application Form Builder
    </h4>
    
    <!-- Announcement Section -->
    <div style="margin-bottom:20px;">
        <label style="display:block;font-size:13px;font-weight:600;color:#111827;margin-bottom:6px;">
            Announcement <span style="color:#ef4444;">*</span>
        </label>
        <p style="font-size:12px;color:#6b7280;margin:0 0 8px;">
            This message will be shown to Kabataan members when they open the application form.
        </p>
        <textarea 
            id="spfbAnnouncement" 
            class="schol-input" 
            rows="3" 
            maxlength="500" 
            placeholder="Enter announcement or instructions for applicants..."
            style="resize:vertical;min-height:80px;"></textarea>
        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right;">
            <span id="spfbAnnouncementCount">0</span>/500 characters
        </div>
    </div>

    <!-- Custom Questions Builder -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span style="font-size:14px;font-weight:600;color:#111827;">Custom Questions</span>
                <span id="spfbQuestionCount" style="display:inline-flex;align-items:center;padding:2px 8px;background:#f3f4f6;border-radius:12px;font-size:11px;font-weight:600;color:#6b7280;">0 questions</span>
            </div>
        </div>
        <p style="font-size:12px;color:#6b7280;margin:0 0 16px;">
            Add custom questions that Kabataan members will answer when applying.
        </p>

        <div id="spfbQuestionList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;">
            <div id="spfbEmptyState" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;background:#fafafa;border:2px dashed #e5e7eb;border-radius:8px;text-align:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin-bottom:12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p style="font-size:13px;color:#6b7280;margin:0;">No questions yet. Click <strong style="color:#374151;">Add Question</strong> to start building your custom form.</p>
            </div>
        </div>

        <button type="button" id="spfbAddQuestionBtn" class="schol-btn schol-btn-save" style="width:100%;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Question
        </button>
    </div>
</div>
