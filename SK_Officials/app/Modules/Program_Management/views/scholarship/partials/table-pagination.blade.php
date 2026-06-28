@php
    $prefix = $prefix ?? 'scholPg';
@endphp
<div class="schol-table-page-footer table-page-footer pagination-footer" aria-label="Table pagination">
    <div class="pagination-footer-nav">
        <button type="button" class="pagination-arrow" id="{{ $prefix }}PrevBtn" disabled aria-label="Previous page">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <span class="pagination-page-label">Page</span>
        <input type="number" class="pagination-page-input" id="{{ $prefix }}PageInput" value="1" min="1" aria-label="Current page">
        <span class="pagination-page-of">of <span id="{{ $prefix }}TotalPages">1</span></span>
        <button type="button" class="pagination-arrow" id="{{ $prefix }}NextBtn" disabled aria-label="Next page">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>
    <div class="pagination-footer-right">
        <select id="{{ $prefix }}RowsPerPageSelect" class="pagination-rows-select" aria-label="Rows per page">
            <option value="10">10 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
        </select>
        <span class="pagination-record-count" id="{{ $prefix }}PaginationInfo">0 records</span>
    </div>
</div>
