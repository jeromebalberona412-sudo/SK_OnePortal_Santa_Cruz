@extends('layouts.app')

@section('title', 'Deleted Kabataan')

@section('head')
@vite(['app/Modules/Archive_Management/assets/css/deleted-kabataan.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern adkab-page">

    <div class="adkab-page-header">
        <div class="adkab-header-left">
            <h1 class="adkab-page-title">Deleted Kabataan</h1>
            <p class="adkab-page-subtitle">Records removed from the Kabataan list by SK Officials — read-only monitoring view.</p>
        </div>
        <div class="adkab-header-right">
            <input type="text" id="adkabSearch" class="adkab-search-input" placeholder="Search by name or barangay…">
        </div>
    </div>

    <!-- Stats -->
    <div class="adkab-stats-row" id="adkabStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="adkab-filter-tabs" id="adkabFilterTabs">
        <button class="adkab-tab active" data-filter="all">All Deleted</button>
        <button class="adkab-tab" data-filter="today">Deleted Today</button>
        <button class="adkab-tab" data-filter="week">This Week</button>
        <button class="adkab-tab" data-filter="month">This Month</button>
    </div>

    <!-- Section label -->
    <div class="adkab-section-heading">
        <h2 class="adkab-section-title" id="adkabSectionLabel">All Deleted Records</h2>
    </div>

    <!-- Table Card -->
    <div class="adkab-table-card">
        <div class="adkab-table-wrapper">
            <table class="adkab-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="adkab-col-hint">LN, FN, MN, Suffix</div></th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Barangay</th>
                        <th>Highest Education</th>
                        <th>Deleted Date</th>
                        <th>Deleted Time</th>
                        <th class="adkab-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="adkabTableBody"></tbody>
            </table>
        </div>
        <div class="adkab-pagination">
            <span class="adkab-pagination-info" id="adkabPaginationInfo">No records found</span>
            <div class="adkab-pagination-controls">
                <button type="button" id="adkabPrevBtn" class="adkab-page-btn" disabled>Previous</button>
                <div id="adkabPageNumbers" class="adkab-page-numbers"></div>
                <button type="button" id="adkabNextBtn" class="adkab-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="adkab-modal-backdrop" id="adkabViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="adkabViewModalTitle">
    <div class="adkab-modal-box adkab-view-modal-box" id="adkabViewModalBox">
        <div class="adkab-modal-header adkab-view-modal-header">
            <h2 class="adkab-modal-title" id="adkabViewModalTitle">Record Details</h2>
            <div class="adkab-view-controls">
                <button type="button" class="adkab-view-toggle" id="adkabViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="adkab-view-close" id="adkabViewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="adkab-view-body" id="adkabViewBody"></div>
    </div>
</div>

@vite(['app/Modules/Archive_Management/assets/js/deleted-kabataan.js'])
@endsection
