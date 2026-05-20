@extends('layouts.app')

@section('title', 'Deleted Barangay')

@section('head')
@vite(['app/Modules/Archive_Management/assets/css/deleted-barangay.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern db-page">

    <div class="db-page-header">
        <div class="db-header-left">
            <h1 class="db-page-title">Deleted Barangay</h1>
            <p class="db-page-subtitle">Soft-deleted barangay records — view, restore, or permanently delete.</p>
        </div>
        <div class="db-header-right">
            <div class="db-search-wrap">
                <svg class="db-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="dbSearch" class="db-search-input" placeholder="Search by Barangay name…" autocomplete="off">
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="db-stats-row" id="dbStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="db-filter-tabs" id="dbFilterTabs">
        <button class="db-tab active" data-filter="all">All Deleted</button>
        <button class="db-tab" data-filter="today">Deleted Today</button>
        <button class="db-tab" data-filter="week">This Week</button>
        <button class="db-tab" data-filter="month">This Month</button>
    </div>

    <!-- Section label -->
    <div class="db-section-heading">
        <h2 class="db-section-title" id="dbSectionLabel">All Deleted Records</h2>
    </div>

    <!-- Table Card -->
    <div class="db-table-card">
        <div class="db-table-wrapper">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>Barangay Name</th>
                        <th>Municipality</th>
                        <th>Province</th>
                        <th>Region</th>
                        <th>Total Purok</th>
                        <th>Total Sitio</th>
                        <th>Deleted Date</th>
                        <th>Deleted Time</th>
                        <th class="db-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="dbTableBody">
                    <!-- Sample data will be injected by JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="db-pagination">
            <span class="db-pagination-info" id="dbPaginationInfo">No records found</span>
            <div class="db-pagination-controls">
                <button type="button" id="dbPrevBtn" class="db-page-btn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    Previous
                </button>
                <div id="dbPageNumbers" class="db-page-numbers"></div>
                <button type="button" id="dbNextBtn" class="db-page-btn" disabled>
                    Next
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="db-modal-backdrop" id="dbViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="dbViewModalTitle">
    <div class="db-modal-box db-view-modal-box" id="dbViewModalBox">
        <div class="db-modal-header db-view-modal-header">
            <h2 class="db-modal-title" id="dbViewModalTitle">Barangay Details</h2>
            <div class="db-view-controls">
                <button type="button" class="db-view-toggle" id="dbViewToggleBtn" aria-label="Maximize">□</button>
                <button type="button" class="db-view-close" id="dbViewCloseBtn" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="db-view-body" id="dbViewBody"></div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="db-modal-backdrop db-confirm-modal" id="dbRestoreModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="dbRestoreModalTitle">
    <div class="db-modal-box db-confirm-modal-box">
        <div class="db-confirm-icon db-confirm-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <h2 class="db-confirm-title" id="dbRestoreModalTitle">Restore Barangay?</h2>
        <p class="db-confirm-message" id="dbRestoreModalMessage">Are you sure you want to restore this barangay? It will be moved back to the active list.</p>
        <div class="db-confirm-actions">
            <button type="button" class="db-btn-confirm-cancel" id="dbRestoreCancelBtn">Cancel</button>
            <button type="button" class="db-btn-confirm-restore" id="dbRestoreConfirmBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Restore Barangay
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="dbToast" class="db-toast" role="status" aria-live="polite">
    <span id="dbToastMsg"></span>
</div>

@vite(['app/Modules/Archive_Management/assets/js/deleted-barangay.js'])
@endsection
