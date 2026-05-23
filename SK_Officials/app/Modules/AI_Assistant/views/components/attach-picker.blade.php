@php
    $pickerId = $pickerId ?? 'aiAttachPicker';
@endphp
<div class="ai-composer-attach-wrap" data-ai-attach-picker id="{{ $pickerId }}Wrap">
    <input type="file" id="{{ $pickerId }}File" class="ai-file-input-hidden" multiple hidden>
    <button type="button" class="ai-composer-attach-btn" id="{{ $pickerId }}" data-ai-attach-trigger aria-label="Attach files or photos" title="Attach files or photos">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
    </button>
    <div class="ai-attach-type-menu" role="menu" aria-label="Upload type">
        <button type="button" class="ai-attach-type-option" data-attach-type="pdf" role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            PDF
        </button>
        <button type="button" class="ai-attach-type-option" data-attach-type="word" role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            MS Word
        </button>
        <button type="button" class="ai-attach-type-option" data-attach-type="photos" role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            Photos
        </button>
    </div>
</div>
