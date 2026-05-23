<div class="mr-modal-backdrop" id="mrModalPreview" hidden>
    <div class="mr-modal mr-modal-lg">
        <div class="mr-modal-head">
            <h3>Print Preview</h3>
            <div class="mr-modal-head-actions">
                <select id="mrPreviewZoom" class="mr-select-sm">
                    <option value="0.75">75%</option>
                    <option value="1" selected>100%</option>
                    <option value="1.25">125%</option>
                </select>
                <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrPreviewPrint">Print</button>
                <button type="button" class="mr-btn mr-btn-outline mr-btn-sm" id="mrPreviewPdf">Download PDF</button>
                <button type="button" class="mr-modal-close" data-close-modal aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="mr-preview-body" id="mrPreviewBody"></div>
    </div>
</div>

<div class="mr-modal-backdrop" id="mrModalExport" hidden>
    <div class="mr-modal">
        <div class="mr-modal-head">
            <h3>Export Report</h3>
            <button type="button" class="mr-modal-close" data-close-modal aria-label="Close">&times;</button>
        </div>
        <div class="mr-modal-body">
            <button type="button" class="mr-export-option" data-export="pdf">
                <span class="mr-export-icon mr-export-icon--pdf">PDF</span>
                <div><strong>Download PDF</strong><small>CKEditor export (A4)</small></div>
            </button>
            <button type="button" class="mr-export-option" data-export="word">
                <span class="mr-export-icon mr-export-icon--doc">DOC</span>
                <div><strong>Download Word</strong><small>.docx via CKEditor</small></div>
            </button>
            <button type="button" class="mr-export-option" data-export="print">
                <span class="mr-export-icon mr-export-icon--print">PRT</span>
                <div><strong>Print</strong><small>Browser print</small></div>
            </button>
            <button type="button" class="mr-export-option" data-export="draft">
                <span class="mr-export-icon mr-export-icon--save">SAV</span>
                <div><strong>Save as Draft</strong><small>Local storage</small></div>
            </button>
        </div>
    </div>
</div>

<div class="mr-modal-backdrop" id="mrModalSubmit" hidden>
    <div class="mr-modal mr-modal-sm">
        <div class="mr-modal-body mr-modal-center">
            <div class="mr-modal-icon-badge">Submit</div>
            <h3>Submit this report?</h3>
            <p class="mr-muted">Status will change to Pending for review.</p>
            <div class="mr-modal-actions">
                <button type="button" class="mr-btn mr-btn-outline" data-close-modal>Cancel</button>
                <button type="button" class="mr-btn mr-btn-primary" id="mrConfirmSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="mr-modal-backdrop" id="mrModalDelete" hidden>
    <div class="mr-modal mr-modal-sm">
        <div class="mr-modal-body mr-modal-center">
            <div class="mr-modal-icon-badge mr-modal-icon-badge--danger">Delete</div>
            <h3>Delete report?</h3>
            <p class="mr-muted">This cannot be undone.</p>
            <div class="mr-modal-actions">
                <button type="button" class="mr-btn mr-btn-outline" data-close-modal>Cancel</button>
                <button type="button" class="mr-btn mr-btn-danger" id="mrConfirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="mr-toast" id="mrToast" hidden></div>
