@extends('layouts.app')

@section('title', 'Rejected KK Profiling')

@section('head')
@vite(['app/Modules/Archive_Management/assets/css/rejected-kk-profiling.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern adrkk-page">

    <div class="adrkk-page-header">
        <div class="adrkk-header-left">
            <h1 class="adrkk-page-title">Rejected KK Profiling</h1>
            <p class="adrkk-page-subtitle">KK Profiling requests rejected by SK Officials — read-only monitoring view.</p>
        </div>
        <div class="adrkk-header-right">
            <input type="text" id="adrkkSearch" class="adrkk-search-input" placeholder="Search by name or reason…">
        </div>
    </div>

    <!-- Stats -->
    <div class="adrkk-stats-row" id="adrkkStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="adrkk-filter-tabs" id="adrkkFilterTabs">
        <button class="adrkk-tab active" data-filter="all">All Rejected</button>
        <button class="adrkk-tab" data-filter="today">Rejected Today</button>
        <button class="adrkk-tab" data-filter="week">This Week</button>
        <button class="adrkk-tab" data-filter="month">This Month</button>
    </div>

    <!-- Section label -->
    <div class="adrkk-section-heading">
        <h2 class="adrkk-section-title" id="adrkkSectionLabel">All Rejected Records</h2>
    </div>

    <!-- Table Card -->
    <div class="adrkk-table-card">
        <div class="adrkk-table-wrapper">
            <table class="adrkk-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="adrkk-col-hint">LN, FN, MN, Suffix</div></th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Purok / Zone</th>
                        <th>Youth Classification</th>
                        <th>Rejection Reason</th>
                        <th>Rejected Date</th>
                        <th>Rejected Time</th>
                        <th class="adrkk-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="adrkkTableBody"></tbody>
            </table>
        </div>
        <div class="adrkk-pagination">
            <span class="adrkk-pagination-info" id="adrkkPaginationInfo">No records found</span>
            <div class="adrkk-pagination-controls">
                <button type="button" id="adrkkPrevBtn" class="adrkk-page-btn" disabled>Previous</button>
                <div id="adrkkPageNumbers" class="adrkk-page-numbers"></div>
                <button type="button" id="adrkkNextBtn" class="adrkk-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="adrkk-modal-backdrop" id="adrkkViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="adrkkViewModalTitle">
    <div class="adrkk-modal-box adrkk-view-modal-box" id="adrkkViewModalBox">
        <div class="adrkk-modal-header adrkk-view-modal-header">
            <h2 class="adrkk-modal-title" id="adrkkViewModalTitle">Record Details</h2>
            <div class="adrkk-view-controls">
                <button type="button" class="adrkk-view-toggle" id="adrkkViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="adrkk-view-close" id="adrkkViewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="adrkk-view-body" id="adrkkViewBody"></div>
    </div>
</div>

@vite(['app/Modules/Archive_Management/assets/js/rejected-kk-profiling.js'])
@endsection
