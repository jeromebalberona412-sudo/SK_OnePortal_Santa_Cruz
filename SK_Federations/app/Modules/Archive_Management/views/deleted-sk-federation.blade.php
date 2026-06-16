@extends('layout::app')

@section('title', 'Deleted SK Federation')

@php
    $cssVersion = @filemtime(app_path('Modules/Archive_Management/assets/css/deleted-sk-federation.css')) ?: time();
    $jsVersion = @filemtime(app_path('Modules/Archive_Management/assets/js/deleted-sk-federation.js')) ?: time();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/archive-management/css/deleted-sk-federation.css') }}?v={{ $cssVersion }}">
@endpush

@section('content')

<div id="mainContent" class="main-content-modern deleted-sk-fed-page container-fluid">

    <div class="dsf-page-header row">
        <div class="dsf-header-left col-md-6">
            <h1 class="dsf-page-title">Deleted SK Federation</h1>
            <p class="dsf-page-subtitle">Records removed from the SK Federation list.</p>
        </div>
        <div class="dsf-header-right col-md-6">
            <a href="{{ route('archived.sk-federation-records') }}" class="archive-goto-btn">Go to SK Federation Records</a>
            <select id="dsfYearFilter" class="dsf-year-filter form-select">
                <option value="all">All Years</option>
            </select>
            <select id="dsfFilterTerm" class="dsf-term-filter form-select">
                <option value="">All Terms</option>
            </select>
            <input type="text" id="dsfSearch" class="dsf-search-input form-control" placeholder="Search by name or barangay…">
        </div>
    </div>

    <!-- Stats -->
    <div class="dsf-stats-row" id="dsfStatsRow"></div>

    <!-- Filter Tabs + Dropdowns -->
    <div class="dsf-filter-bar">
        <div class="dsf-filter-tabs">
            <button class="dsf-tab active" data-filter="all">All Deleted</button>
            <button class="dsf-tab" data-filter="today">Deleted Today</button>
            <button class="dsf-tab" data-filter="week">This Week</button>
            <button class="dsf-tab" data-filter="month">This Month</button>
        </div>
        <div class="dsf-filter-dropdowns">
            <select id="dsfFilterBarangay" class="dsf-filter-select">
                <option value="">All Barangays</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="dsf-table-card">
        <div class="dsf-table-wrapper">
            <table class="dsf-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="dsf-col-hint">LN, FN, MN, Suffix</div></th>
                        <th>Position</th>
                        <th>Barangay</th>
                        <th>Municipality</th>
                        <th>Status</th>
                        <th>Date Deleted</th>
                        <th>Time Deleted</th>
                        <th class="dsf-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="dsfTableBody"></tbody>
            </table>
        </div>
        <div class="dsf-pagination">
            <span class="dsf-pagination-info" id="dsfPaginationInfo">No records found</span>
            <div class="dsf-pagination-controls">
                <button type="button" id="dsfPrevBtn" class="dsf-page-btn" disabled>Previous</button>
                <div id="dsfPageNumbers" class="dsf-page-numbers"></div>
                <button type="button" id="dsfNextBtn" class="dsf-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- Restore Confirmation Modal -->
<div class="dsf-modal-backdrop" id="dsfRestoreModal" style="display:none;">
    <div class="dsf-modal-box">
        <div class="dsf-modal-header">
            <h2 class="dsf-modal-title">Restore Record</h2>
        </div>
        <div class="dsf-modal-body">
            <p class="dsf-modal-message">Restore this record back to the SK Federation list?</p>
            <p class="dsf-modal-name" id="dsfRestoreName"></p>
        </div>
        <div class="dsf-modal-footer">
            <button type="button" class="dsf-btn-cancel" id="dsfRestoreCancelBtn">Cancel</button>
            <button type="button" class="dsf-btn-confirm" id="dsfRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="dsf-modal-backdrop" id="dsfViewModal" style="display:none;">
    <div class="dsf-modal-box dsf-view-modal-box" id="dsfViewModalBox">
        <div class="dsf-modal-header dsf-view-modal-header">
            <h2 class="dsf-modal-title">View Details</h2>
            <div class="dsf-view-controls">
                <button type="button" class="dsf-view-toggle modal-win-btn modal-win-btn-maximize" id="dsfViewToggle" title="Maximize" aria-label="Maximize">
                    <svg id="dsfViewToggleIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                </button>
                <button type="button" class="dsf-view-close modal-win-btn modal-win-btn-close" id="dsfViewClose" title="Close" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="dsf-view-body" id="dsfViewBody"></div>
        <div class="dsf-modal-footer">
            <button type="button" class="dsf-btn-cancel" id="dsfViewCloseFooter">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ url('/modules/archive-management/js/deleted-sk-federation.js') }}?v={{ $jsVersion }}"></script>
@endpush
