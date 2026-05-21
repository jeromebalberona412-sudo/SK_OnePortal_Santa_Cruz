@extends('layouts.app')

@section('title', 'Rejected Scholarships')

@section('head')
@vite(['app/Modules/Archive_Management/assets/css/rejected-scholarships.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="main-content-modern adrsc-page">

    <div class="adrsc-page-header">
        <div class="adrsc-header-left">
            <h1 class="adrsc-page-title">Rejected Scholarships</h1>
            <p class="adrsc-page-subtitle">Scholarship applications rejected by SK Officials — read-only monitoring view.</p>
        </div>
        <div class="adrsc-header-right">
            <select id="adrscYearFilter" class="adrsc-year-filter">
                <option value="all">All Years</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
            <select id="adrscTermFilter" class="adrsc-term-filter">
                <option value="all">All Terms</option>
                <option value="2023-2026">2023 - 2026</option>
                <option value="2024-2027">2024 - 2027</option>
                <option value="2025-2028">2025 - 2028</option>
            </select>
            <input type="text" id="adrscSearch" class="adrsc-search-input" placeholder="Search by name or school…">
        </div>
    </div>

    <!-- Stats -->
    <div class="adrsc-stats-row" id="adrscStatsRow"></div>

    <!-- Filter Tabs -->
    <div class="adrsc-filter-tabs" id="adrscFilterTabs">
        <button class="adrsc-tab active" data-filter="all">All Rejected</button>
        <button class="adrsc-tab" data-filter="today">Rejected Today</button>
        <button class="adrsc-tab" data-filter="week">This Week</button>
        <button class="adrsc-tab" data-filter="month">This Month</button>
    </div>

    <!-- Section label -->
    <div class="adrsc-section-heading">
        <h2 class="adrsc-section-title" id="adrscSectionLabel">All Rejected Records</h2>
    </div>

    <!-- Table Card -->
    <div class="adrsc-table-card">
        <div class="adrsc-table-wrapper">
            <table class="adrsc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>School</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th class="adrsc-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="adrscTableBody"></tbody>
            </table>
        </div>
        <div class="adrsc-pagination">
            <span class="adrsc-pagination-info" id="adrscPaginationInfo">No records found</span>
            <div class="adrsc-pagination-controls">
                <button type="button" id="adrscPrevBtn" class="adrsc-page-btn" disabled>Previous</button>
                <div id="adrscPageNumbers" class="adrsc-page-numbers"></div>
                <button type="button" id="adrscNextBtn" class="adrsc-page-btn" disabled>Next</button>
            </div>
        </div>
    </div>

</div>

<!-- View Modal -->
<div class="adrsc-modal-backdrop" id="adrscViewModal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="adrscViewModalTitle">
    <div class="adrsc-modal-box adrsc-view-modal-box" id="adrscViewModalBox">
        <div class="adrsc-modal-header adrsc-view-modal-header">
            <h2 class="adrsc-modal-title" id="adrscViewModalTitle">Application Details</h2>
            <div class="adrsc-view-controls">
                <button type="button" class="adrsc-view-toggle" id="adrscViewToggle" aria-label="Maximize">□</button>
                <button type="button" class="adrsc-view-close" id="adrscViewClose" aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="adrsc-view-body" id="adrscViewBody"></div>
    </div>
</div>

@vite(['app/Modules/Archive_Management/assets/js/rejected-scholarships.js'])
@endsection
