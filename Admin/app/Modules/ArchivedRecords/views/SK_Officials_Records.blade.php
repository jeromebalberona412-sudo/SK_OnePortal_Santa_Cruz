@extends('layouts.app')

@section('title', 'Archived SK Officials Records')

@section('head')
@vite(['app/Modules/ArchivedRecords/assets/css/SK_officials.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern aroff-page">

    <div class="aroff-page-header">
        <div class="aroff-header-left">
            <h1 class="aroff-page-title">Archived SK Officials Records</h1>
            <p class="aroff-page-subtitle">Completed term records — read-only historical archive.</p>
        </div>
        <div class="aroff-header-right">
            <input type="text" id="aroffSearch" class="aroff-search-input" placeholder="Search by name or position…">
        </div>
    </div>

    <!-- Stats -->
    <div class="aroff-stats-row" id="aroffStatsRow"></div>

    <!-- Table Card -->
    <div class="aroff-table-card">
        <div class="aroff-section-heading">
            <div class="aroff-heading-row">
                <h2 class="aroff-section-title">SK Officials — Completed Terms</h2>
                <div class="aroff-filters">
                    <select id="aroffFilterPosition" class="aroff-filter-select">
                        <option value="">All Positions</option>
                    </select>
                    <select id="aroffFilterTerm" class="aroff-filter-select">
                        <option value="">All Terms</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="aroff-table-wrapper">
            <table class="aroff-table">
                <thead>
                    <tr>
                        <th>Name</th>
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
    </div>
</div>

@vite(['app/Modules/ArchivedRecords/assets/js/SK_officials.js'])
@endsection
