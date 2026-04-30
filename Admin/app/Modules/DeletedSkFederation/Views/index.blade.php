@extends('layouts.app')

@section('title', 'Deleted SK Federation')

@section('head')
@vite(['app/Modules/DeletedSkFederation/assets/css/deleted-sk-federation.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern deleted-sk-fed-page">

    <div class="dsf-page-header">
        <div class="dsf-header-left">
            <h1 class="dsf-page-title">Deleted SK Federation</h1>
            <p class="dsf-page-subtitle">Records removed from the SK Federation list.</p>
        </div>
        <div class="dsf-header-right">
            <input type="text" id="dsfSearch" class="dsf-search-input" placeholder="Search by name or barangay…">
        </div>
    </div>

    <!-- Stats -->
    <div class="dsf-stats-row" id="dsfStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="dsf-filter-tabs">
        <button class="dsf-tab active" data-filter="all">All Deleted</button>
        <button class="dsf-tab" data-filter="today">Deleted Today</button>
        <button class="dsf-tab" data-filter="week">This Week</button>
        <button class="dsf-tab" data-filter="month">This Month</button>
    </div>

    <!-- Table -->
    <div class="dsf-table-card">
        <div class="dsf-section-heading">
            <h2 class="dsf-section-title" id="dsfSectionLabel">All Deleted Records</h2>
        </div>
        <div class="dsf-table-wrapper">
            <table class="dsf-table">
                <thead>
                    <tr>
                        <th>Full Name<div class="dsf-col-hint">LN, FN, MN</div></th>
                        <th>Position</th>
                        <th>Barangay / Municipality</th>
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
                <button type="button" class="dsf-view-toggle" id="dsfViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="dsf-view-close" id="dsfViewClose">&times;</button>
            </div>
        </div>
        <div class="dsf-view-body" id="dsfViewBody"></div>
    </div>
</div>

@vite(['app/Modules/DeletedSkFederation/assets/js/deleted-sk-federation.js'])
@endsection
