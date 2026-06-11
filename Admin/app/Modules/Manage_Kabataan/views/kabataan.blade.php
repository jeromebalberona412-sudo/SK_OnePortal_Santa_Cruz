@extends('layouts.app')

@section('title', 'Manage Kabataan')

@section('head')
    @vite([
        'app/Modules/Manage_Kabataan/assets/css/manage-kabataan.css',
        'app/Modules/Manage_Kabataan/assets/css/kk-questionnaire-view.css',
    ])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

<div id="mainContent" class="gov-dashboard mk-page container-fluid">
<div id="manageKabataanApp" data-mk-routes='@json($routes)'>
    <div class="dash-page-header mk-page-header">
        <div class="dash-page-header-left">
            <h1 class="dash-page-title">Kabataan</h1>
            <p class="dash-page-welcome">View KK profiling submissions across all barangays</p>
        </div>
        <div class="mk-header-actions">
            <select id="mkBarangayFilter" class="mk-filter-select form-select" aria-label="Filter by barangay">
                <option value="all">All Barangays</option>
                @foreach ($barangays as $barangay)
                    <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                @endforeach
            </select>
            <input type="search" id="mkSearch" class="mk-search-input form-control" placeholder="Search name, email, respondent #…" aria-label="Search kabataan">
        </div>
    </div>

    <section class="stats-grid stats-grid--top mk-stats-grid" aria-label="Kabataan statistics">
        <div class="stat-card stat-card-cyan">
            <div class="stat-card-top"><span class="stat-card-value" id="mkStatTotal">0</span></div>
            <span class="stat-card-label">Total KK Profiles</span>
        </div>
        <div class="stat-card stat-card-emerald">
            <div class="stat-card-top"><span class="stat-card-value" id="mkStatApproved">0</span></div>
            <span class="stat-card-label">Approved</span>
        </div>
        <div class="stat-card stat-card-azure">
            <div class="stat-card-top"><span class="stat-card-value" id="mkStatPending">0</span></div>
            <span class="stat-card-label">Pending</span>
        </div>
        <div class="stat-card stat-card-red">
            <div class="stat-card-top"><span class="stat-card-value" id="mkStatRejected">0</span></div>
            <span class="stat-card-label">Rejected</span>
        </div>
    </section>

    <section class="mk-table-card" aria-label="Kabataan records">
        <div class="mk-table-header">
            <div>
                <h2 class="mk-table-title">KK Profiling Records</h2>
                <p class="mk-table-subtitle" id="mkTableSubtitle">Loading records…</p>
            </div>
        </div>
        <div class="mk-table-wrap">
            <table class="mk-table table table-sm mb-0" id="mkKabataanTable">
                <thead>
                    <tr>
                        <th>Respondent #</th>
                        <th>Full Name</th>
                        <th>Barangay</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="mk-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="mkTableBody"></tbody>
            </table>
        </div>
    </section>
</div>
</div>

<div class="mk-modal" id="mkViewModal" hidden>
    <div class="mk-modal-backdrop" data-close-mk-modal></div>
    <div class="mk-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="mkViewModalTitle">
        <div class="mk-modal-header">
            <div>
                <h3 id="mkViewModalTitle">KK Profiling Form</h3>
                <p id="mkViewModalSubtitle"></p>
            </div>
            <button type="button" class="mk-modal-close" data-close-mk-modal aria-label="Close view">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="mk-modal-body kk-view-modal-body kk-qs-body">
            <div class="kk-qs-scroll-wrapper">
                @include('manage-kabataan::partials.kk-profiling-view', [
                    'barangayLogoUrl' => asset('images/SK_OnePortal_logo.png'),
                    'barangayName' => 'Barangay',
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['app/Modules/Manage_Kabataan/assets/js/manage-kabataan.js'])
@endsection
