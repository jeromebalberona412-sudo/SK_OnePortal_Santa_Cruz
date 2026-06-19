{{-- Embedded batch upload panel (used inside Add modals) --}}
@php
    $prefix = $prefix ?? 'official';
    $templateType = $templateType ?? ($prefix === 'fed' ? 'federation' : 'officials');
@endphp
<div class="form-section-light batch-upload-section">
    <h4 class="section-title-light">
        <i class="fa-solid fa-file-arrow-down"></i>
        Template
    </h4>
    <div class="batch-upload-actions">
        <a
            href="{{ route('accounts.batch-template', ['type' => $templateType]) }}"
            class="btn-template-download"
            id="{{ $prefix }}_batchTemplateDownloadLink"
            download
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download Excel Template
        </a>
    </div>
    <p class="batch-hint">Use the Excel template columns exactly. All fields are required except Middle Name (can be blank). Upload your own filled file — no sample rows are added by the system. Maximum <strong>{{ \App\Modules\Accounts\Requests\BatchStoreAccountsRequest::MAX_ACCOUNTS }}</strong> accounts per upload.</p>
</div>

<div class="form-section-light">
    <h4 class="section-title-light">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        Upload File
    </h4>
    <div class="batch-dropzone" id="{{ $prefix }}_batchDropzone">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <p class="dropzone-text">Drop file here or <label for="{{ $prefix }}_batchFileInput" class="dropzone-browse">browse</label></p>
        <p class="dropzone-sub" id="{{ $prefix }}_batchFileName">Supported: .xlsx, .xls</p>
        <input type="file" id="{{ $prefix }}_batchFileInput" accept=".xlsx,.xls" style="display:none;">
    </div>

    <div id="{{ $prefix }}_batchPreview" style="display:none;" class="batch-preview-always"></div>
    <div id="{{ $prefix }}_batchErrorReport" style="display:none;" class="batch-error-report"></div>
</div>
