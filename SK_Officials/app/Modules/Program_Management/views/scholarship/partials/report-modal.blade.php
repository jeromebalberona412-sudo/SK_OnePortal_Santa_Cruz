<div class="sports-modal-overlay" id="scholarReportModal" style="display:none;">
    <div class="sports-modal-box scholar-report-modal-box" id="scholarReportModalBox">
        <div class="sports-modal-header">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span id="scholarReportModalTitle">Make Report</span>
            </h3>
            <button type="button" class="sports-modal-close" id="scholarReportClose">&times;</button>
        </div>

        <div class="word-editor-meta">
            <select id="scholarReportType" class="scholar-report-select" aria-label="Report type">
                <option value="scholarship">Scholarship Program Report</option>
                <option value="activity">Activity Report</option>
                <option value="resolution">SK Resolution</option>
                <option value="minutes">Meeting Minutes</option>
                <option value="custom">Custom Document</option>
            </select>
            <input type="text" id="scholarReportTitle" class="scholar-report-title-input" placeholder="Document title..." maxlength="200">
        </div>

        @include('Program_Management::partials.word-report-shell', [
            'shellId' => 'scholarReportWordShell',
            'editorId' => 'scholarReportEditor',
            'pageId' => 'scholarReportPage',
            'paperSelectId' => 'scholarReportPaper',
            'imageInputId' => 'scholarReportImageInput',
            'cropBtnId' => 'scholarReportCropBtn',
            'deleteImgBtnId' => 'scholarReportDeleteImgBtn',
            'placeholder' => 'Type your SK report here, or click Generate for a template...',
            'showGenerate' => true,
            'generateId' => 'scholarReportGenerate',
            'showPrint' => true,
            'printId' => 'scholarReportPrint',
        ])

        <div class="sports-modal-footer">
            <button type="button" class="sports-btn sports-btn-outline" id="scholarReportCancel">Cancel</button>
            <button type="button" class="sports-btn sports-btn-primary" id="scholarReportSave">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Save Report
            </button>
        </div>
    </div>
</div>
