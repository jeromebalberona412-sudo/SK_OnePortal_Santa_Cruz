@extends('layouts.app')

@section('title', 'Archived SK Federation Records')

@section('head')
@vite(['app/Modules/ArchivedRecords/assets/css/SK_federation.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern arfed-page">

    <div class="arfed-page-header">
        <div class="arfed-header-left">
            <h1 class="arfed-page-title">Archived SK Federation Records</h1>
            <p class="arfed-page-subtitle">Completed term records — read-only historical archive.</p>
        </div>
        <div class="arfed-header-right">
            <input type="text" id="arfedSearch" class="arfed-search-input" placeholder="Search by name or position…">
        </div>
    </div>

    <!-- Stats -->
    <div class="arfed-stats-row" id="arfedStatsRow"></div>

    <!-- Table Card -->
    <div class="arfed-table-card">
        <div class="arfed-table-wrapper">
            <table class="arfed-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="arfed-col-hint">FN, MN, LN, Suffix</div></th>
                        <th>Position</th>
                        <th>Term</th>
                        <th>Status</th>
                        <th class="arfed-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="arfedTableBody"></tbody>
            </table>
        </div>
        <div class="arfed-pagination">
            <span class="arfed-pagination-info" id="arfedPaginationInfo">No records found</span>
            <div class="arfed-pagination-controls">
                <button type="button" id="arfedPrevBtn" class="arfed-page-btn" disabled>Previous</button>
                <div id="arfedPageNumbers" class="arfed-page-numbers"></div>
                <button type="button" id="arfedNextBtn" class="arfed-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="arfed-modal-backdrop" id="arfedViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="arfedViewModalTitle">
    <div class="arfed-modal-box arfed-view-modal-box" id="arfedViewModalBox">
        <div class="arfed-modal-header arfed-view-modal-header">
            <h2 class="arfed-modal-title" id="arfedViewModalTitle">View Details</h2>
            <div class="arfed-view-controls">
                <button type="button" class="arfed-view-toggle" id="arfedViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="arfed-view-close" id="arfedViewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="arfed-view-body" id="arfedViewBody"></div>
    </div>
</div>

@vite(['app/Modules/ArchivedRecords/assets/js/SK_federation.js'])
@endsection
