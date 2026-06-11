@extends('layouts.app')

@section('title', 'Audit Logs')

@section('head')
    @vite(['app/Modules/AuditLog/assets/css/auditlogs.css'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

@php
    $statCards = [
        ['label' => 'Total Audit Logs', 'metricKey' => 'total_logs', 'value' => $stats['total_logs'] ?? 0, 'tone' => 'azure', 'icon' => 'activity'],
        ['label' => "Today's Activities", 'metricKey' => 'today_activities', 'value' => $stats['today_activities'] ?? 0, 'tone' => 'teal', 'icon' => 'users'],
        ['label' => 'Security Events', 'metricKey' => 'security_events', 'value' => $stats['security_events'] ?? 0, 'tone' => 'red', 'icon' => 'reject'],
        ['label' => 'Active Users Logged Today', 'metricKey' => 'active_users_today', 'value' => $stats['active_users_today'] ?? 0, 'tone' => 'violet', 'icon' => 'officials'],
    ];
@endphp

<div id="mainContent" class="gov-dashboard auditlog-shell">
<div id="auditLogApp" data-audit-routes='@json($routes)'>
    <div class="dash-page-header">
        <div class="dash-page-header-left">
            <h1 class="dash-page-title">Audit Logs</h1>
            <p class="dash-page-welcome">Centralized activity trail across Admin, Federation, Officials, and Kabataan portals</p>
        </div>
        <div class="audit-header-actions">
            <button type="button" class="audit-btn audit-btn-primary" id="exportCsvBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    <section class="stats-grid stats-grid--top audit-stats-grid" aria-label="Audit statistics">
        @foreach ($statCards as $card)
            @include('dashboard::components.statcard', $card)
        @endforeach
    </section>

    <div class="audit-layout audit-layout--full">
            <section class="audit-filters-card" aria-label="Audit log filters">
                <div class="audit-filters-grid">
                    <div class="audit-filter-field audit-filter-field--search">
                        <label for="auditSearch">Search</label>
                        <input type="search" id="auditSearch" class="audit-input" placeholder="Search user, action, entity, IP…">
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditDateFrom">Date From</label>
                        <input type="date" id="auditDateFrom" class="audit-input">
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditDateTo">Date To</label>
                        <input type="date" id="auditDateTo" class="audit-input">
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditUser">User</label>
                        <select id="auditUser" class="audit-input">
                            <option value="">All Users</option>
                            @foreach ($filterOptions['users'] as $userOption)
                                <option value="{{ $userOption['id'] }}">{{ $userOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditRole">Role</label>
                        <select id="auditRole" class="audit-input">
                            <option value="">All Roles</option>
                            @foreach ($filterOptions['roles'] as $roleOption)
                                <option value="{{ $roleOption['value'] }}">{{ $roleOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditBarangay">Barangay</label>
                        <select id="auditBarangay" class="audit-input">
                            <option value="">All Barangays</option>
                            @foreach ($filterOptions['barangays'] as $barangayOption)
                                <option value="{{ $barangayOption['id'] }}">{{ $barangayOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="audit-filter-field">
                        <label for="auditEventType">Event Type</label>
                        <select id="auditEventType" class="audit-input">
                            <option value="">All Event Types</option>
                            @foreach ($filterOptions['event_types'] as $eventType)
                                <option value="{{ $eventType }}">{{ $eventType }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="audit-table-card" aria-label="Audit logs table">
                <div class="audit-table-header">
                    <div>
                        <h2 class="audit-table-title">Activity Records</h2>
                        <p class="audit-table-subtitle" id="auditTableSubtitle">Loading audit logs…</p>
                    </div>
                    <div class="audit-per-page">
                        <label for="auditPerPage">Rows</label>
                        <select id="auditPerPage" class="audit-input audit-input--compact">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                <div class="audit-table-wrap" id="auditTableWrap">
                    <div class="audit-loading" id="auditLoadingState">
                        <span class="audit-spinner" aria-hidden="true"></span>
                        <span>Loading audit logs…</span>
                    </div>

                    <div class="table-responsive">
                        <table class="audit-table" id="auditLogsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Event Type</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>Operating System</th>
                                    <th>IP Address</th>
                                    <th class="audit-col-actions">View</th>
                                </tr>
                            </thead>
                            <tbody id="auditLogsTableBody"></tbody>
                        </table>
                    </div>

                </div>

                <div class="audit-pagination" id="auditPagination">
                    <button type="button" class="audit-page-btn" id="auditPrevBtn" disabled>Previous</button>
                    <div class="audit-page-numbers" id="auditPageNumbers"></div>
                    <button type="button" class="audit-page-btn" id="auditNextBtn" disabled>Next</button>
                    <span class="audit-page-info" id="auditPageInfo"></span>
                </div>
            </section>
    </div>
</div>
</div>

<div class="audit-modal" id="auditDetailsModal" hidden>
    <div class="audit-modal-backdrop" data-close-modal></div>
    <div class="audit-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="auditModalTitle">
        <div class="audit-modal-header">
            <div>
                <h3 id="auditModalTitle">Audit Log Details</h3>
                <p id="auditModalSubtitle"></p>
            </div>
            <button type="button" class="audit-modal-close" data-close-modal aria-label="Close details">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="audit-modal-body">
            <div class="audit-detail-grid" id="auditDetailGrid"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['app/Modules/AuditLog/assets/js/auditlogs.js'])
@endsection
