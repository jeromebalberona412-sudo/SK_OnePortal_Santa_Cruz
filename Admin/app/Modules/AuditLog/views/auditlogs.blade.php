@extends('layouts.app')

@section('title', 'Audit Logs')

@section('head')
    @vite(['app/Modules/AuditLog/assets/css/auditlogs.css'])
@endsection

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')

<div class="main-content-modern auditlog-page" id="mainContent">
    <div class="auditlog-container auditlog-shell auditlog-prototype">
        <!-- Page Header with Search and Filters -->
        <div class="page-header-modern-with-button">
            <div class="page-header-left">
                <h1 class="page-title-modern">Audit Logs</h1>
                <p class="page-subtitle-modern">Monitor and track system activities</p>
            </div>
            <div class="page-header-right">
                <form method="GET" action="{{ route('auditlogs.index') }}" class="search-add-container audit-filter-grid">
                    <div class="filter-dropdown-container event-control">
                        <select id="eventFilter" class="filter-dropdown" name="event">
                            <option value="">All Events</option>
                            @foreach($events as $eventOption)
                                <option value="{{ $eventOption }}" {{ ($filters['event'] ?? '') === $eventOption ? 'selected' : '' }}>
                                    {{ $eventOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-dropdown-container outcome-control">
                        <select id="outcomeFilter" class="filter-dropdown" name="outcome">
                            <option value="">All Outcomes</option>
                            @foreach(($outcomes ?? collect()) as $outcomeOption)
                                <option value="{{ $outcomeOption }}" {{ ($filters['outcome'] ?? '') === $outcomeOption ? 'selected' : '' }}>
                                    {{ strtoupper($outcomeOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="date-range-container audit-date-control">
                        <div class="date-field-wrapper">
                            <label for="dateFrom" class="date-range-label">From</label>
                            <input
                                type="date"
                                id="dateFrom"
                                name="date_from"
                                class="date-input"
                                value="{{ $filters['date_from'] ?? '' }}"
                            >
                        </div>
                        <span class="date-separator">to</span>
                        <div class="date-field-wrapper">
                            <label for="dateTo" class="date-range-label">To</label>
                            <input
                                type="date"
                                id="dateTo"
                                name="date_to"
                                class="date-input"
                                value="{{ $filters['date_to'] ?? '' }}"
                            >
                        </div>
                    </div>
                    <div class="search-container audit-search-control">
                        <input type="text" id="searchInput" name="search" class="search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search by actor, event, resource, IP, or user agent...">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                        <a href="{{ route('auditlogs.index') }}" class="reset-inline">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <section class="audit-metrics-grid" aria-label="Audit summary prototype cards">
            <article class="metric-card">
                <div class="metric-meta">
                    <span class="material-symbols-outlined metric-icon">key</span>
                    <span class="metric-trend up">+12%</span>
                </div>
                <p class="metric-label">Successful Logins</p>
                <h2 class="metric-value">1,284</h2>
                <div class="metric-foot">
                    <span class="metric-caption">7-day baseline</span>
                    <div class="metric-bar"><span style="width: 74%"></span></div>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-meta">
                    <span class="material-symbols-outlined metric-icon">gpp_maybe</span>
                    <span class="metric-trend alert">+2</span>
                </div>
                <p class="metric-label">Security Anomalies</p>
                <h2 class="metric-value">14</h2>
                <div class="metric-foot">
                    <span class="metric-caption">Threat index sample</span>
                    <div class="metric-bar danger"><span style="width: 31%"></span></div>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-meta">
                    <span class="material-symbols-outlined metric-icon">database</span>
                    <span class="metric-trend">Stable</span>
                </div>
                <p class="metric-label">Resource Requests</p>
                <h2 class="metric-value">42.8k</h2>
                <div class="metric-foot">
                    <span class="metric-caption">Cluster load sample</span>
                    <div class="metric-bar"><span style="width: 62%"></span></div>
                </div>
            </article>
            <article class="metric-card metric-card-primary">
                <div class="metric-meta">
                    <span class="material-symbols-outlined metric-icon">verified</span>
                </div>
                <p class="metric-label">Integrity Score</p>
                <h2 class="metric-value">99.99%</h2>
                <div class="metric-foot">
                    <span class="metric-caption">Verification sample</span>
                    <div class="metric-bar"><span style="width: 96%"></span></div>
                </div>
            </article>
        </section>

        <div class="audit-content-grid">
            <section class="audit-log-column">
                <div class="table-card-modern">
                    <div class="table-card-header">
                        <h2 class="table-card-title">User Authentication</h2>
                        <button type="button" class="table-card-action">Download CSV</button>
                    </div>
                    <div class="table-responsive">
                        <table class="accounts-table">
                            <thead>
                                <tr>
                                    <th>Timestamp (UTC)</th>
                                    <th>Actor / User</th>
                                    <th>Action / Event</th>
                                    <th>Affected Resource</th>
                                    <th>Outcome</th>
                                    <th>Source</th>
                                </tr>
                            </thead>
                            <tbody id="auditlogsTableBody">
                                @forelse($logs as $log)
                                    @php
                                        $eventName = (string) $log->event;
                                        $eventClass = str_contains($eventName, 'logout')
                                            ? 'logout'
                                            : (str_contains($eventName, 'blocked')
                                                ? 'blocked'
                                                : ((str_contains($eventName, 'failed') || str_contains($eventName, 'invalid'))
                                                    ? 'failed'
                                                    : (str_contains($eventName, 'login') || str_contains($eventName, 'session') ? 'login' : 'view')));

                                        $actorLabel = $log->actor_email
                                            ?: ($log->user?->email ?: ($log->user_id ? 'User #'.$log->user_id : 'System'));

                                        $resourceType = $log->resource_type ?: '-';

                                        $outcomeName = strtolower((string) ($log->outcome ?? ''));
                                        if ($outcomeName === '') {
                                            $outcomeName = str_contains($eventName, 'blocked')
                                                ? 'blocked'
                                                : ((str_contains($eventName, 'failed') || str_contains($eventName, 'invalid')) ? 'failed' : 'success');
                                        }

                                        if (! in_array($outcomeName, ['success', 'failed', 'blocked'], true)) {
                                            $outcomeName = 'unknown';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ optional($log->created_at)->timezone('UTC')->format('Y-m-d H:i:s') ?? '-' }}</td>
                                        <td>{{ $actorLabel }}</td>
                                        <td><span class="event-badge {{ $eventClass }}">{{ $eventName }}</span></td>
                                        <td>
                                            <div class="resource-label">{{ $resourceType }}</div>
                                        </td>
                                        <td><span class="outcome-badge {{ $outcomeName }}">{{ strtoupper($outcomeName) }}</span></td>
                                        <td>
                                            <div class="source-ip">{{ $log->ip_address ?: '-' }}</div>
                                            <div class="source-agent">{{ $log->user_agent ?: '-' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No auth audit logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Custom Pagination -->
                <div class="pagination-container">
                    <div class="pagination-wrapper">
                        <nav class="pagination-nav" aria-label="Table pagination">
                            @if($logs->onFirstPage())
                                <button type="button" class="pagination-btn pagination-btn-prev" disabled>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 18l-6-6 6-6"/>
                                    </svg>
                                    Previous
                                </button>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}" class="pagination-btn pagination-btn-prev">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 18l-6-6 6-6"/>
                                    </svg>
                                    Previous
                                </a>
                            @endif

                            <div class="pagination-numbers">
                                @php
                                    $currentPage = $logs->currentPage();
                                    $lastPage = $logs->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp

                                @if($startPage > 1)
                                    <a href="{{ $logs->url(1) }}" class="pagination-btn pagination-number">1</a>
                                    @if($startPage > 2)
                                        <span class="pagination-dots">...</span>
                                    @endif
                                @endif

                                @for($page = $startPage; $page <= $endPage; $page++)
                                    @if($page === $currentPage)
                                        <span class="pagination-btn pagination-number active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $logs->url($page) }}" class="pagination-btn pagination-number">{{ $page }}</a>
                                    @endif
                                @endfor

                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <span class="pagination-dots">...</span>
                                    @endif
                                    <a href="{{ $logs->url($lastPage) }}" class="pagination-btn pagination-number">{{ $lastPage }}</a>
                                @endif
                            </div>

                            @if($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}" class="pagination-btn pagination-btn-next">
                                    Next
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"/>
                                    </svg>
                                </a>
                            @else
                                <button type="button" class="pagination-btn pagination-btn-next" disabled>
                                    Next
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"/>
                                    </svg>
                                </button>
                            @endif
                        </nav>

                        <div class="pagination-info">
                            <span class="pagination-text" id="paginationInfo">
                                Showing <strong>{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ $logs->total() }}</strong> logs
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="security-alerts-card" aria-label="Security alerts prototype">
                <div class="security-alerts-header">
                    <h3>Security Alerts</h3>
                    <p>Frontend prototype feed</p>
                </div>

                <div class="alerts-stack">
                    <article class="alert-item critical">
                        <div class="alert-meta">
                            <span class="alert-level">Critical</span>
                            <span class="alert-time">2m ago</span>
                        </div>
                        <h4>SQL Injection Attempt</h4>
                        <p>Source: 192.168.1.104</p>
                        <div class="alert-actions">
                            <button type="button" class="alert-action primary" data-dismiss-alert>Block IP</button>
                            <button type="button" class="alert-action" data-dismiss-alert>Ignore</button>
                        </div>
                    </article>

                    <article class="alert-item warning">
                        <div class="alert-meta">
                            <span class="alert-level">Warning</span>
                            <span class="alert-time">14m ago</span>
                        </div>
                        <h4>Unauthorized File Access</h4>
                        <p>Source: Internal/Dev-Cluster</p>
                    </article>

                    <article class="alert-item notice">
                        <div class="alert-meta">
                            <span class="alert-level">Notice</span>
                            <span class="alert-time">1h ago</span>
                        </div>
                        <h4>SSL Certificate Expiry</h4>
                        <p>Domain: *.api.sentinel.io</p>
                    </article>
                </div>
            </aside>
        </div>

        <section class="prototype-access-card" aria-label="Data access prototype records">
            <header class="prototype-access-header">
                <h3>Data Access Records</h3>
                <p>Prototype panel for extended audit visibility</p>
            </header>
            <div class="table-responsive">
                <table class="prototype-access-table">
                    <thead>
                        <tr>
                            <th>Resource</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Integrity Hash</th>
                            <th class="text-right">Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>db_cluster_01/customer_vault</td>
                            <td>READ_SECRET</td>
                            <td>System_Automator_SRV</td>
                            <td>sha256:7f8e...3a1b</td>
                            <td class="text-right"><span class="verification-pill">Validated</span></td>
                        </tr>
                        <tr>
                            <td>storage_bucket/financial_reports</td>
                            <td>WRITE_OBJECT</td>
                            <td>Finance_Module_02</td>
                            <td>sha256:4d2a...9c0f</td>
                            <td class="text-right"><span class="verification-pill">Validated</span></td>
                        </tr>
                        <tr>
                            <td>auth_service/config_v2</td>
                            <td>UPDATE_ENV</td>
                            <td>Admin_Root</td>
                            <td>sha256:1a9c...7b5e</td>
                            <td class="text-right"><span class="verification-pill">Validated</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['app/Modules/AuditLog/assets/js/auditlogs.js'])
@endsection
