@extends('layouts.app')

@section('title', 'Audit Logs')

@section('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endsection

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
                            <option value="Login">Login</option>
                            <option value="Logout">Logout</option>
                            <option value="Create">Create</option>
                            <option value="Update">Update</option>
                            <option value="Delete">Delete</option>
                            <option value="View">View</option>
                        </select>
                    </div>
                    <div class="search-container">
                        <input type="text" id="searchInput" name="search" class="search-input" value="{{ request('search') }}" placeholder="Search by user ID or event...">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="date-range-container">
                        <div class="date-field-wrapper">
                            <label class="date-range-label" for="dateFrom">Start date</label>
                            <input type="date" id="dateFrom" name="date_from" class="date-input" value="{{ request('date_from') }}" aria-label="Start date MM/DD/YYYY">
                        </div>
                        <span class="date-separator">to</span>
                        <div class="date-field-wrapper">
                            <label class="date-range-label" for="dateTo">End date</label>
                            <input type="date" id="dateTo" name="date_to" class="date-input" value="{{ request('date_to') }}" aria-label="End date MM/DD/YYYY">
                        </div>
                    </div>
                </form>
                
                <!-- Export Button -->
                <div class="export-section">
                    <button class="export-btn" onclick="exportAuditLogs()" style="display: flex; align-items: center; gap: 10px; padding: 14px 24px; background: #16a34a; color: #ffffff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3); margin-left: auto;">
                        <i class="bi bi-download" style="font-size: 18px;"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <span id="summaryText">Showing 1-10 of 120 logs</span>
        </div>

        <!-- Table Card -->
        <div class="table-card-modern">
            <div class="table-responsive">
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Event</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Metadata</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody id="auditlogsTableBody">
                        <!-- Real data from Supabase -->
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_blocked_email_unverified</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td></td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_success</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td>{"suspicious":{"is_suspicious":false}</td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_success</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td>{"suspicious":{"is_suspicious":false}</td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_blocked_email_device_changed</span></td>
                            <td>192.168.8.40</td>
                            <td>Mozilla/5.0 (Linux; Android 10; K) AppleWebKit</td>
                            <td></td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_blocked_email_device_changed</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td></td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_blocked_email_device_changed</span></td>
                            <td>192.168.8.40</td>
                            <td>Mozilla/5.0 (Linux; Android 10; K) AppleWebKit</td>
                            <td></td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_blocked_email_device_changed</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td></td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_success</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td>{"suspicious":{"is_suspicious":false}</td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="event-badge login">login_success</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td>{"suspicious":{"is_suspicious":false}</td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td><span class="event-badge login">login_blocked_email_unverified</span></td>
                            <td>192.168.8.37</td>
                            <td>Mozilla/5.0 (X11; Linux x86_64; rv:148.0)</td>
                            <td>{"suspicious":{"is_suspicious":true}</td>
                            <td>2026-02-28 06:07:08</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Custom Pagination -->
        <div class="pagination-container">
            <div class="pagination-wrapper">
                <nav class="pagination-nav" aria-label="Table pagination">
                    <button type="button" class="pagination-btn pagination-btn-prev" id="prevBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"/>
                        </svg>
                        Previous
                    </button>
                    
                    <div class="pagination-numbers" id="paginationNumbers">
                        <button type="button" class="pagination-btn pagination-number active" onclick="goToPage(1)">1</button>
                        <button type="button" class="pagination-btn pagination-number" onclick="goToPage(2)">2</button>
                        <button type="button" class="pagination-btn pagination-number" onclick="goToPage(3)">3</button>
                        <span class="pagination-dots">...</span>
                        <button type="button" class="pagination-btn pagination-number" onclick="goToPage(12)">12</button>
                    </div>
                    
                    <button type="button" class="pagination-btn pagination-btn-next" id="nextBtn">
                        Next
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </button>
                </nav>
                
                <div class="pagination-info">
                    <span class="pagination-text" id="paginationInfo">Showing <strong>1-10</strong> of <strong>120</strong> logs</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ Vite::asset('app/Modules/AuditLog/assets/css/auditlogs.css') }}">
<style>
.export-section {
    display: flex !important;
    justify-content: flex-end !important;
    margin-top: 16px !important;
    padding-top: 16px !important;
    border-top: 1px solid #e5e7eb !important;
    width: 100% !important;
}

.export-btn {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 14px 24px !important;
    background: #16a34a !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3) !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    margin-left: auto !important;
}

.export-btn:hover {
    background: #15803d !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.4) !important;
}

.export-btn i {
    font-size: 18px !important;
}

/* Date range - same background & border as search and dropdown */
.date-range-container {
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    padding: 0.75rem 1rem !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    background: #ffffff !important;
    transition: all 0.2s ease !important;
    min-width: 150px !important;
}

.date-range-container:focus-within {
    outline: none !important;
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}

.date-field-wrapper {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.35rem !important;
    flex: 1 !important;
    min-width: 0 !important;
}

.date-range-label {
    font-size: 12px !important;
    font-weight: 500 !important;
    color: #374151 !important;
    display: block !important;
}

.date-input {
    flex: 1 !important;
    padding: 0.75rem 1rem !important;
    border: 1px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 14px !important;
    color: #374151 !important;
    background: #ffffff !important;
    transition: all 0.2s ease !important;
    min-width: 140px !important;
    outline: none !important;
}

.date-input:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}

.date-separator {
    color: #6b7280 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    align-self: center !important;
    margin: 0 4px !important;
    padding: 0 4px !important;
}

@media (max-width: 768px) {
    .date-range-container {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .date-separator {
        display: block !important;
        text-align: center !important;
        margin: 0 !important;
    }
    .date-range-label {
        display: block !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ Vite::asset('app/Modules/AuditLog/assets/js/auditlogs.js') }}"></script>
@endpush