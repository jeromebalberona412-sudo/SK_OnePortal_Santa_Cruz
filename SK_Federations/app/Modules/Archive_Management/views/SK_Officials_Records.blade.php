@extends('layout::app')

@section('title', 'Archived SK Officials Records')

@php
    $cssVersion = @filemtime(app_path('Modules/Archive_Management/assets/css/SK_officials.css')) ?: time();
    $jsVersion = @filemtime(app_path('Modules/Archive_Management/assets/js/SK_officials.js')) ?: time();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/archive-management/css/SK_officials.css') }}?v={{ $cssVersion }}">
@endpush

@section('content')

<div id="mainContent" class="main-content-modern aroff-page container-fluid">

    <div class="aroff-page-header row">
        <div class="aroff-header-left col-md-6">
            <h1 class="aroff-page-title">Archived SK Officials Records</h1>
            <p class="aroff-page-subtitle">Completed term records — read-only historical archive.</p>
        </div>
        <div class="aroff-header-right col-md-6">
            <a href="{{ route('archived.deleted-sk-officials') }}" class="archive-goto-btn">Go to Deleted SK Officials</a>
            <select id="aroffYearFilter" class="aroff-year-filter form-select">
                <option value="all">All Years</option>
            </select>
            <select id="aroffTermFilter" class="aroff-term-filter form-select">
                <option value="all">All Terms</option>
            </select>
            <input type="text" id="aroffSearch" class="aroff-search-input form-control" placeholder="Search by name or position…">
        </div>
    </div>

    <!-- Stats -->
    <div class="aroff-stats-row" id="aroffStatsRow"></div>

    <!-- Table Card -->
    <div class="aroff-table-card">
        <div class="aroff-table-wrapper">
            <table class="aroff-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="aroff-col-hint">LN, FN, MN, Suffix</div></th>
                        <th>Position</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th class="aroff-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="aroffTableBody"></tbody>
            </table>
        </div>
        <div class="aroff-pagination">
            <span class="aroff-pagination-info" id="aroffPaginationInfo">No records found</span>
            <div class="aroff-pagination-controls">
                <button type="button" id="aroffPrevBtn" class="aroff-page-btn" disabled>Previous</button>
                <div id="aroffPageNumbers" class="aroff-page-numbers"></div>
                <button type="button" id="aroffNextBtn" class="aroff-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="aroff-modal-backdrop" id="aroffViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="aroffViewModalTitle">
    <div class="aroff-modal-box aroff-view-modal-box" id="aroffViewModalBox">
        <div class="aroff-modal-header aroff-view-modal-header">
            <h2 class="aroff-modal-title" id="aroffViewModalTitle">View Details</h2>
            <div class="aroff-view-controls">
                <button type="button" class="aroff-view-toggle" id="aroffViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="aroff-view-close" id="aroffViewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="aroff-view-body" id="aroffViewBody"></div>
        <div class="aroff-modal-footer">
            <button type="button" class="aroff-page-btn" id="aroffViewCloseFooter" aria-label="Close">&times;</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ url('/modules/archive-management/js/SK_officials.js') }}?v={{ $jsVersion }}"></script>
@endpush
