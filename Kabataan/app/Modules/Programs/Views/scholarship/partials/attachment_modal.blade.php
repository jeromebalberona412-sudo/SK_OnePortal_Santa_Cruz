<div class="sch-att-modal" id="schAttachmentModal" hidden role="dialog" aria-modal="true" aria-labelledby="schAttModalTitle">
    <div class="sch-att-modal-backdrop" data-close-modal></div>
    <div class="sch-att-modal-box">
        <div class="sch-att-modal-header">
            <h2 id="schAttModalTitle">View Attachment</h2>
            <button type="button" class="sch-att-modal-close" data-close-modal aria-label="Close">&times;</button>
        </div>
        <div class="sch-att-modal-body">
            <p class="sch-att-doc-name" id="schAttDocName"></p>
            <span class="sch-req-badge" id="schAttStatusBadge"></span>
            <p class="sch-att-remarks" id="schAttRemarks" hidden></p>
            <div class="sch-att-preview" id="schAttPreview">
                <div class="sch-att-preview-icon" aria-hidden="true">📄</div>
                <p>Preview your document below. Download a copy first, then open when ready.</p>
            </div>
        </div>
        <div class="sch-att-modal-footer">
            <button type="button" class="sch-req-btn sch-req-btn-view" id="schAttDownloadBtn">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                Download
            </button>
            <button type="button" class="sch-req-btn sch-req-btn-open" id="schAttOpenBtn" disabled>
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/></svg>
                Open Attachment
            </button>
        </div>
        <p class="sch-att-modal-note">You must download the file before opening it.</p>
    </div>
</div>
