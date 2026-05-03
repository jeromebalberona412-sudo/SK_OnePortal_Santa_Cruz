@extends('layouts.app')

@section('title', 'Deleted SK Officials')

@section('head')
@vite(['app/Modules/DeletedSkOfficials/assets/css/deleted-sk-officials.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern deleted-sk-off-page">

    <div class="dso-page-header">
        <div class="dso-header-left">
            <h1 class="dso-page-title">Deleted SK Officials</h1>
            <p class="dso-page-subtitle">Records removed from the SK Officials list.</p>
        </div>
        <div class="dso-header-right">
            <input type="text" id="dsoSearch" class="dso-search-input" placeholder="Search by name or barangay…">
        </div>
    </div>

    <!-- Stats -->
    <div class="dso-stats-row" id="dsoStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="dso-filter-tabs">
        <button class="dso-tab active" data-filter="all">All Deleted</button>
        <button class="dso-tab" data-filter="today">Deleted Today</button>
        <button class="dso-tab" data-filter="week">This Week</button>
        <button class="dso-tab" data-filter="month">This Month</button>
    </div>

    <!-- Table -->
    <div class="dso-table-card">
        <div class="dso-table-wrapper">
            <table class="dso-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="dso-col-hint">LN, FN, MN</div></th>
                        <th>Position</th>
                        <th>Barangay / Municipality</th>
                        <th>Date Deleted</th>
                        <th>Time Deleted</th>
                        <th class="dso-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="dsoTableBody"></tbody>
            </table>
        </div>
        <div class="dso-pagination">
            <span class="dso-pagination-info" id="dsoPaginationInfo">No records found</span>
            <div class="dso-pagination-controls">
                <button type="button" id="dsoPrevBtn" class="dso-page-btn" disabled>Previous</button>
                <div id="dsoPageNumbers" class="dso-page-numbers"></div>
                <button type="button" id="dsoNextBtn" class="dso-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- Restore Confirmation Modal -->
<div class="dso-modal-backdrop" id="dsoRestoreModal" style="display:none;">
    <div class="dso-modal-box">
        <div class="dso-modal-header">
            <h2 class="dso-modal-title">Restore Record</h2>
        </div>
        <div class="dso-modal-body">
            <p class="dso-modal-message">Restore this record back to the SK Officials list?</p>
            <p class="dso-modal-name" id="dsoRestoreName"></p>
        </div>
        <div class="dso-modal-footer">
            <button type="button" class="dso-btn-cancel" id="dsoRestoreCancelBtn">Cancel</button>
            <button type="button" class="dso-btn-confirm" id="dsoRestoreConfirmBtn">Restore</button>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="dso-modal-backdrop" id="dsoViewModal" style="display:none;">
    <div class="dso-modal-box dso-view-modal-box" id="dsoViewModalBox">
        <div class="dso-modal-header dso-view-modal-header">
            <h2 class="dso-modal-title">View Details</h2>
            <div class="dso-view-controls">
                <button type="button" class="dso-view-toggle" id="dsoViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="dso-view-close" id="dsoViewClose">&times;</button>
            </div>
        </div>
        <div class="dso-view-body" id="dsoViewBody"></div>
    </div>
</div>

@vite(['app/Modules/DeletedSkOfficials/assets/js/deleted-sk-officials.js'])
@endsection
