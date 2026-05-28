<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $browserTitle ?? 'Program List - SK Officials Portal' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholarship_application_form.css',
        'app/Modules/schedule_programs/assets/css/scholarship/scholar_list.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body data-program-key="{{ $programType ?? 'scholarship' }}">

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="sl-page-container schol-page-container">

        @include('schedule_programs::partials.program-page-top', [
            'activeTab' => 'list',
            'pageTitle' => 'Program List',
            'pageSubtitle' => $pageSubtitle ?? 'Approved participants for this program.',
            'programType' => $programType ?? 'scholarship',
            'programTitle' => $programTitle ?? null,
            'programDescription' => $programDescription ?? null,
        ])

        <!-- Summary Cards -->
        <div class="sl-stats-grid">
            <div class="sl-stat-card sl-stat-blue">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatTotal">0</span>
                    <div class="sl-stat-icon sl-icon-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Total</span>
            </div>
            <div class="sl-stat-card sl-stat-orange">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatPending">0</span>
                    <div class="sl-stat-icon sl-icon-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Pending</span>
            </div>
            <div class="sl-stat-card sl-stat-green">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatPaid">0</span>
                    <div class="sl-stat-icon sl-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Completed</span>
            </div>
            <div class="sl-stat-card sl-stat-red">
                <div class="sl-stat-top">
                    <span class="sl-stat-value" id="slStatCancelled">0</span>
                    <div class="sl-stat-icon sl-icon-red">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>
                <span class="sl-stat-label">Cancelled</span>
            </div>
        </div>

        <div class="schol-page-toolbar">
            <div class="sl-filter-group">
                <div class="sl-filter-wrapper">
                    <select id="slYearFilter" class="sl-filter-select">
                        <option value="">All</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <div class="sl-search-wrapper">
                    <svg class="sl-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="slSearchInput" class="sl-search-input" placeholder="Search...">
                </div>
            </div>
            <button type="button" id="slExportCsvBtn" class="sl-btn sl-btn-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export to CSV
            </button>
        </div>

        <!-- Table -->
        <div class="sl-table-card">
            <div class="sl-table-wrapper">
                <table class="sl-table">
                    <thead id="slTableHead">
                        <tr>
                            <th>FULL NAME<div class="sl-col-hint">LN, FN, MN, Suffix</div></th>
                            <th>Program / Activity</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slTableBody"></tbody>
                </table>
            </div>
            <div class="sl-pagination-wrapper">
                <div class="sl-pagination-info">
                    Showing <span id="slShowingStart">0</span> to <span id="slShowingEnd">0</span> of <span id="slTotalRecords">0</span>
                </div>
                <div class="sl-pagination-controls">
                    <button type="button" class="sl-page-btn" id="slFirstPage" title="First Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"/>
                            <polyline points="18 17 13 12 18 7"/>
                        </svg>
                    </button>
                    <button type="button" class="sl-page-btn" id="slPrevPage" title="Previous Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                    <div class="sl-page-numbers" id="slPageNumbers"></div>
                    <button type="button" class="sl-page-btn" id="slNextPage" title="Next Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    <button type="button" class="sl-page-btn" id="slLastPage" title="Last Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="13 17 18 12 13 7"/>
                            <polyline points="6 17 11 12 6 7"/>
                        </svg>
                    </button>
                </div>
                <div class="sl-per-page">
                    <label for="slPerPage">Per page:</label>
                    <select id="slPerPage" class="sl-per-page-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

    </div>
</main>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/scholarship/scholar_list.js'
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>

