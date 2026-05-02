<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports List - SK Officials Portal</title>
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/schedule_programs/assets/css/sports_list.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="spl-page-container">

        <!-- ── Page Header ── -->
        <section class="spl-page-header">
            <div class="spl-page-header-left">
                <a href="/schedule-programs" class="spl-btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Back to Schedule Programs
                </a>
                <h1 class="spl-page-title">Sports List</h1>
                <p class="spl-page-subtitle">Approved sports program participants under Sports Development</p>
            </div>
            <div class="spl-header-actions">
                <button type="button" id="splExportCsvBtn" class="spl-btn spl-btn-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export to CSV
                </button>
                <a href="/sports-requests" class="spl-btn spl-btn-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                        <path d="M2 12h20"/>
                    </svg>
                    Go to Sports Requests
                </a>
            </div>
        </section>

        <!-- ── Stat Cards ── -->
        <div class="spl-stats-grid">
            <div class="spl-stat-card spl-stat-blue">
                <div class="spl-stat-top">
                    <span class="spl-stat-value" id="splStatTotal">0</span>
                    <div class="spl-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
                    </div>
                </div>
                <span class="spl-stat-label">Total Approved</span>
            </div>
            <div class="spl-stat-card spl-stat-orange">
                <div class="spl-stat-top">
                    <span class="spl-stat-value" id="splStatBasketball">0</span>
                    <div class="spl-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                </div>
                <span class="spl-stat-label">Basketball</span>
            </div>
            <div class="spl-stat-card spl-stat-green">
                <div class="spl-stat-top">
                    <span class="spl-stat-value" id="splStatVolleyball">0</span>
                    <div class="spl-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                </div>
                <span class="spl-stat-label">Volleyball</span>
            </div>
            <div class="spl-stat-card spl-stat-purple">
                <div class="spl-stat-top">
                    <span class="spl-stat-value" id="splStatOther">0</span>
                    <div class="spl-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                </div>
                <span class="spl-stat-label">Other Sports</span>
            </div>
        </div>

        <!-- ── Filters ── -->
        <div class="spl-filters-row">
            <select id="splFilterSport" class="spl-filter-input">
                <option value="">All Sports</option>
                <option value="Basketball">Basketball</option>
                <option value="Volleyball">Volleyball</option>
                <option value="Other">Other</option>
            </select>
            <select id="splFilterDivision" class="spl-filter-input">
                <option value="">All Divisions</option>
                <option value="Junior Division (15-17)">Junior Division — 15–17</option>
                <option value="Youth Division (18-21)">Youth Division — 18–21</option>
                <option value="Senior Division (22-25)">Senior Division — 22–25</option>
                <option value="Open Division (26-30)">Open Division — 26–30</option>
            </select>
            <div class="spl-search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="splSearch" class="spl-search-input" placeholder="Search by name or sport...">
            </div>
        </div>

        <!-- ── Sports List Table ── -->
        <div class="spl-table-card">
            <div class="spl-table-wrapper">
                <table class="spl-table">
                    <thead>
                        <tr>
                            <th>FULL NAME
                                <div class="spl-col-hint">LN, FN, MN, Suffix</div>
                            </th>
                            <th>Sport</th>
                            <th>Division</th>
                            <th>Contact</th>
                            <th>Date Applied</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="splTableBody"></tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ── View Modal ── -->
<div class="spl-modal-overlay" id="splViewModal" style="display:none;">
    <div class="spl-modal-box" id="splViewBox">
        <div class="spl-modal-header">
            <h3>Sports Application Details</h3>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="spl-modal-close" id="splViewMaximize" title="Maximize">□</button>
                <button type="button" class="spl-modal-close" id="splViewClose" title="Close">&times;</button>
            </div>
        </div>
        <div class="spl-modal-body" id="splViewBody"></div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/schedule_programs/assets/js/sports_list.js'
])

</body>
</html>
