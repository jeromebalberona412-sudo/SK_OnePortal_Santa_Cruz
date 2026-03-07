@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')

<div class="main-content-modern" id="mainContent">
    <div class="manage-account-container">
        <!-- Page Header with Search and Filters -->
        <div class="page-header-modern-with-button">
            <div class="page-header-left">
                <h1 class="page-title-modern">Audit Logs</h1>
                <p class="page-subtitle-modern">Monitor and track system activities</p>
            </div>
            <div class="page-header-right">
                <form method="GET" action="{{ route('auditlogs.index') }}" class="search-add-container">
                    <div class="filter-dropdown-container">
                        <select id="eventFilter" class="filter-dropdown" name="event">
                            <option value="">All Events</option>
                            @foreach($events as $eventOption)
                                <option value="{{ $eventOption }}" {{ ($filters['event'] ?? '') === $eventOption ? 'selected' : '' }}>
                                    {{ $eventOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="date-range-container">
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
                    <div class="search-container">
                        <input type="text" id="searchInput" name="search" class="search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search by user ID, event, IP, or user agent...">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <a href="{{ route('auditlogs.index') }}" class="pagination-btn">Reset</a>
                </form>
            </div>
        </div>
        <!-- Table Card -->
        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Metadata</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="auditlogsTableBody">
                        @forelse($logs as $log)
                            @php
                                $eventName = (string) $log->event;
                                $eventClass = str_contains($eventName, 'logout')
                                    ? 'logout'
                                    : (str_contains($eventName, 'login') ? 'login' : 'view');

                                $metadataText = '-';
                                if (is_array($log->metadata) && $log->metadata !== []) {
                                    $metadataText = json_encode($log->metadata, JSON_UNESCAPED_SLASHES);
                                }
                            @endphp
                            <tr>
                                <td>{{ $log->id ?? '-' }}</td>
                                <td><span class="event-badge {{ $eventClass }}">{{ $eventName }}</span></td>
                                <td>{{ $log->ip_address ?: '-' }}</td>
                                <td>{{ $log->user_agent ?: '-' }}</td>
                                <td>{{ $metadataText }}</td>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
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
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ Vite::asset('app/Modules/AuditLog/assets/css/auditlogs.css') }}">
@endpush

@push('scripts')
<script src="{{ Vite::asset('app/Modules/AuditLog/assets/js/auditlogs.js') }}"></script>
@endpush