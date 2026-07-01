<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — SK Officials Portal</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Dashboard/assets/css/dashboard.css'
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content" id="mainContent">
<div class="dashboard-container">

    <!-- ══ Page Header ══════════════════════════════════════ -->
    <div class="dash-page-header">
        <div class="dash-page-header-left">
            <h1 class="dash-page-title">Dashboard</h1>
            <p class="dash-page-sub">Welcome back, <strong id="dashUserName">{{ $userFirstName ?? 'SK Official' }}</strong> &mdash; SK Official</p>
        </div>
    </div>

    <!-- ══ Calendar Reminder Banner ═════════════════════════ -->
    <div id="calendarReminderBanner" class="dash-reminder-banner d-none mb-3">
        <div class="dash-reminder-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="10" x2="21" y2="10"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="16" y1="2" x2="16" y2="6"></line>
            </svg>
        </div>
        <div class="dash-reminder-body">
            <span class="dash-reminder-label">Today's Reminder</span>
            <span class="dash-reminder-text" id="reminderText"></span>
        </div>
        <a href="{{ route('calendar') }}" class="dash-reminder-link">View Calendar</a>
    </div>

    <!-- ══ Stat Cards ═══════════════════════════════════════ -->
    <div class="stats-2row-grid mb-3">

        <div class="stat-card stat-card-blue" data-href="{{ route('kabataan') }}" title="View Kabataan records">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statKabataan">0</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>
            <span class="stat-card-label">Total KK Profiles</span>
        </div>

        <div class="stat-card stat-card-green" data-href="{{ route('kabataan') }}" title="View approved KK profiles">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statApproved">0</span>
                <div class="stat-card-icon stat-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Total KK Profiling Approved</span>
        </div>

        <div class="stat-card stat-card-yellow" data-href="{{ route('kk-profiling-requests') }}" title="View pending KK profiling requests">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statPending">0</span>
                <div class="stat-card-icon stat-icon-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Total KK Profiling Pending</span>
        </div>

        <div class="stat-card stat-card-rose" data-href="{{ route('rejected-kkprofiling') }}" title="View rejected KK profiling records">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statRejected">0</span>
                <div class="stat-card-icon stat-icon-rose">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total KK Profiling Rejected</span>
        </div>

        <div class="stat-card stat-card-teal" data-href="{{ route('programs') }}" title="View programs">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statActivePrograms">0</span>
                <div class="stat-card-icon stat-icon-teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Programs</span>
        </div>

        <div class="stat-card stat-card-green" data-href="{{ route('scholarship.applications') }}?status=approved" title="View approved scholarship applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statScholarshipsApproved">0</span>
                <div class="stat-card-icon stat-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Scholarships Approved</span>
        </div>

        <div class="stat-card stat-card-yellow" data-href="{{ route('scholarship.applications') }}?status=pending" title="View pending scholarship applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statScholarshipsPending">0</span>
                <div class="stat-card-icon stat-icon-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Scholarships Pending</span>
        </div>

        <div class="stat-card stat-card-rose" data-href="{{ route('rejected-scholars') }}" title="View rejected scholarship applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statScholarshipsRejected">0</span>
                <div class="stat-card-icon stat-icon-rose">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Scholarships Rejected</span>
        </div>

        <div class="stat-card stat-card-green" data-href="{{ route('sports-requests') }}?status=approved" title="View approved sports applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statSportsApproved">0</span>
                <div class="stat-card-icon stat-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Sports Approved</span>
        </div>

        <div class="stat-card stat-card-yellow" data-href="{{ route('sports-requests') }}?status=pending" title="View pending sports applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statSportsPending">0</span>
                <div class="stat-card-icon stat-icon-yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Sports Pending</span>
        </div>

        <div class="stat-card stat-card-rose" data-href="{{ route('rejected-sports') }}" title="View rejected sports applications">
            <div class="stat-card-top">
                <span class="stat-card-value" id="statSportsRejected">0</span>
                <div class="stat-card-icon stat-icon-rose">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Sports Rejected</span>
        </div>

    </div><!-- /stats-2row-grid -->

    <!-- ══ Quick Actions ═════════════════════════════════════ -->
    <div class="dash-section-card">
        <div class="dash-section-header">
            <div>
                <h2 class="dash-section-title">Quick Actions</h2>
            </div>
        </div>
        <div class="quick-actions-scroll">
            <a href="{{ route('schedule-kk-profiling') }}" class="qa-btn qa-tone-1">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Schedule KK Profiling
            </a>
            <a href="{{ route('scholarship.schedule') }}" class="qa-btn qa-tone-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                Create Scholarship Program
            </a>
            <a href="{{ route('sports-application-form') }}" class="qa-btn qa-tone-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                    <path d="M2 12h20"></path>
                </svg>
                Create Sports Program
            </a>
            <a href="{{ route('announcements') }}" class="qa-btn qa-tone-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l18-5v6l-18 5z"></path>
                    <path d="M6 21v-5.5"></path>
                </svg>
                Post Community Announcement
            </a>
            <a href="{{ route('scholarship.applications') }}" class="qa-btn qa-tone-5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                Review Scholarship Applications
            </a>
            <a href="{{ route('kabataan') }}" class="qa-btn qa-tone-6">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Generate Reports
            </a>
            <a href="{{ route('calendar') }}" class="qa-btn qa-tone-7">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Add Calendar Note
            </a>
        </div>
    </div>

    <!-- ══ Charts Row 1: Bar + Line ══════════════════════════ -->
    <div class="charts-grid-2">

        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Kabataan per Purok/Sitio</h2>
                    <p class="dash-section-sub">Youth population distribution</p>
                </div>
                <span class="dash-chart-badge">Bar Chart</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="chartKabataanBarangay"></canvas>
            </div>
        </div>

        <div class="dash-section-card">
            <div class="dash-section-header dash-section-header--wrap dash-section-header--chart-filters">
                <div>
                    <h2 class="dash-section-title">KK Profiling by Month</h2>
                    <p class="dash-section-sub" id="kkChartSubtitle">Approved, pending, and rejected submissions</p>
                </div>
                <div class="dash-chart-header-actions">
                    <div class="line-chart-select-group">
                        <label for="kkChartZone" class="dash-filter-label">Zone/Sitio</label>
                        <select id="kkChartZone" class="dash-filter-select">
                            <option value="all">All Zones</option>
                        </select>
                    </div>
                    <div class="line-chart-select-group">
                        <label for="kkChartGranularity" class="dash-filter-label">View</label>
                        <select id="kkChartGranularity" class="dash-filter-select">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                    <div class="line-chart-select-group" id="kkChartMonthWrap" hidden>
                        <label for="kkChartMonth" class="dash-filter-label">Month</label>
                        <select id="kkChartMonth" class="dash-filter-select">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="kk-chart-canvas-wrap chart-canvas-wrap chart-canvas-wrap--line">
                <canvas id="chartMonthlyRequests"></canvas>
            </div>
            <div class="kk-chart-filter-row line-chart-filter-row">
                <label class="line-chart-checkbox">
                    <input type="checkbox" id="filterApproved" checked>
                    <span class="line-chart-checkbox-box" style="background:#22c55e;"></span>
                    <span>Approved</span>
                </label>
                <label class="line-chart-checkbox">
                    <input type="checkbox" id="filterPending" checked>
                    <span class="line-chart-checkbox-box" style="background:#f59e0b;"></span>
                    <span>Pending</span>
                </label>
                <label class="line-chart-checkbox">
                    <input type="checkbox" id="filterRejected" checked>
                    <span class="line-chart-checkbox-box" style="background:#ef4444;"></span>
                    <span>Rejected</span>
                </label>
            </div>
        </div>

    </div>

    <!-- ══ Charts Row 2: Pie + Donut ═════════════════════════ -->
    <div class="charts-grid-2">

        <div class="dash-section-card">
            <div class="dash-section-header dash-section-header--wrap">
                <div>
                    <h2 class="dash-section-title">Kabataan Sex Distribution</h2>
                    <p class="dash-section-sub">Male vs Female registered youth</p>
                </div>
                <div class="dash-chart-header-actions">
                    <select id="genderChartFilter" class="dash-filter-select" aria-label="Filter by sex">
                        <option value="all" selected>All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--pie">
                <canvas id="chartGenderPie"></canvas>
            </div>
            <div class="pie-legend-row" id="genderLegend"></div>
        </div>

        <div class="dash-section-card" id="employmentStatusCard">
            <div class="dash-section-header dash-section-header--wrap">
                <div>
                    <h2 class="dash-section-title">Employment Status Distribution</h2>
                    <p class="dash-section-sub">Current employment profile of registered Kabataan</p>
                </div>
                <div class="dash-chart-header-actions">
                    <select id="employmentChartFilter" class="dash-filter-select" aria-label="Filter employment status">
                        <option value="all" selected>All Statuses</option>
                        <option value="Employed">Employed</option>
                        <option value="Unemployed">Unemployed</option>
                        <option value="Self-Employed">Self-Employed</option>
                        <option value="Currently looking for a Job">Currently looking for a Job</option>
                        <option value="Not Interested Looking for a Job">Not Interested Looking for a Job</option>
                    </select>
                </div>
            </div>
            <div id="employmentChartSkeleton" class="dash-chart-skeleton" aria-hidden="false">
                <div class="dash-skeleton-bar" style="width:72%"></div>
                <div class="dash-skeleton-bar" style="width:55%"></div>
                <div class="dash-skeleton-bar" style="width:40%"></div>
                <div class="dash-skeleton-bar" style="width:28%"></div>
                <div class="dash-skeleton-bar" style="width:18%"></div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--pie" id="employmentChartWrap" hidden>
                <canvas id="chartEmploymentStatus"></canvas>
            </div>
            <div class="donut-legend" id="employmentLegend"></div>
            <p class="dash-empty-msg d-none" id="employmentChartEmpty">No employment data available.</p>
        </div>

    </div>

    <!-- ══ Account Status Section ═══════════════════════════ -->
    <div class="dash-section-card mb-3">
        <div class="dash-section-header">
            <div>
                <h2 class="dash-section-title">Account Status</h2>
                <p class="dash-section-sub">SK Officials account overview and status</p>
            </div>
            <span class="dash-chart-badge">Overview</span>
        </div>
        <div class="row g-2" id="committeesList">
            <!-- Rendered by JS -->
        </div>
    </div>

    <!-- ══ Bottom Row: Activity + Announcements + Events ═════ -->
    <div class="charts-grid-3">

        <!-- Activity Timeline -->
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Recent Activity</h2>
                    <p class="dash-section-sub">Latest system actions</p>
                </div>
                <a href="#" class="dash-view-all" id="viewAllActivity">View all</a>
            </div>
            <div class="activity-list" id="activityList"></div>
        </div>

        <!-- Upcoming Events -->
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Upcoming Events</h2>
                    <p class="dash-section-sub">Calendar preview</p>
                </div>
                <a href="{{ route('calendar') }}" class="dash-view-all">View all</a>
            </div>
            <div class="events-list" id="eventsList"></div>
        </div>

    </div>

</div><!-- /dashboard-container -->
</main>

<!-- ══════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════ -->

<!-- Add Kabataan -->
<div class="dash-modal-backdrop" id="modalAddKabataan" role="dialog" aria-modal="true" aria-labelledby="mTitleKabataan">
    <div class="dash-modal-box">
        <div class="dash-modal-header">
            <h3 id="mTitleKabataan">Add Kabataan</h3>
            <button class="dash-modal-close" data-close="modalAddKabataan" aria-label="Close">&times;</button>
        </div>
        <div class="dash-modal-body">
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Last Name</label>
                    <input type="text" placeholder="e.g. Dela Cruz">
                </div>
                <div class="dash-form-group">
                    <label>First Name</label>
                    <input type="text" placeholder="e.g. Juan">
                </div>
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Middle Name</label>
                    <input type="text" placeholder="Optional">
                </div>
                <div class="dash-form-group">
                    <label>Suffix</label>
                    <select>
                        <option value="">None</option>
                        <option>Jr.</option><option>Sr.</option><option>III</option>
                    </select>
                </div>
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Date of Birth</label>
                    <input type="date">
                </div>
                <div class="dash-form-group">
                    <label>Sex</label>
                    <select>
                        <option value="">Select</option>
                        <option>Male</option><option>Female</option>
                    </select>
                </div>
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Purok / Sitio</label>
                    <input type="text" placeholder="e.g. Purok 1">
                </div>
                <div class="dash-form-group">
                    <label>Highest Education</label>
                    <select>
                        <option value="">Select</option>
                        <option>Elementary</option>
                        <option>High School</option>
                        <option>Senior High School</option>
                        <option>College</option>
                        <option>Vocational</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="dash-modal-footer">
            <button class="dash-btn-cancel" data-close="modalAddKabataan">Cancel</button>
            <button class="dash-btn-save">Save Kabataan</button>
        </div>
    </div>
</div>

<!-- Add ABYIP Member -->
<div class="dash-modal-backdrop" id="modalAddAbyip" role="dialog" aria-modal="true" aria-labelledby="mTitleAbyip">
    <div class="dash-modal-box">
        <div class="dash-modal-header">
            <h3 id="mTitleAbyip">Add ABYIP Member</h3>
            <button class="dash-modal-close" data-close="modalAddAbyip" aria-label="Close">&times;</button>
        </div>
        <div class="dash-modal-body">
            <div class="dash-form-group">
                <label>Full Name</label>
                <input type="text" placeholder="Last Name, First Name, M.I.">
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Program / PPA</label>
                    <input type="text" placeholder="e.g. Digital Literacy Training">
                </div>
                <div class="dash-form-group">
                    <label>Status</label>
                    <select>
                        <option value="">Select</option>
                        <option>Active</option><option>Inactive</option><option>Completed</option>
                    </select>
                </div>
            </div>
            <div class="dash-form-group">
                <label>Remarks</label>
                <textarea rows="3" placeholder="Optional remarks…"></textarea>
            </div>
        </div>
        <div class="dash-modal-footer">
            <button class="dash-btn-cancel" data-close="modalAddAbyip">Cancel</button>
            <button class="dash-btn-save">Save Member</button>
        </div>
    </div>
</div>

<!-- View All Activity -->
<div class="dash-modal-backdrop" id="modalViewAllActivity" role="dialog" aria-modal="true" aria-labelledby="mTitleActivity">
    <div class="dash-modal-box dash-modal-box-large">
        <div class="dash-modal-header">
            <h3 id="mTitleActivity">All Recent Activity</h3>
            <button class="dash-modal-close" data-close="modalViewAllActivity" aria-label="Close">&times;</button>
        </div>
        <div class="dash-modal-body">
            <div class="activity-list-full" id="activityListFull"></div>
        </div>
        <div class="dash-modal-footer">
            <button class="dash-btn-cancel" data-close="modalViewAllActivity">Close</button>
        </div>
    </div>
</div>

<!-- Create Program -->
<div class="dash-modal-backdrop" id="modalCreateProgram" role="dialog" aria-modal="true" aria-labelledby="mTitleProgram">
    <div class="dash-modal-box">
        <div class="dash-modal-header">
            <h3 id="mTitleProgram">Create Program</h3>
            <button class="dash-modal-close" data-close="modalCreateProgram" aria-label="Close">&times;</button>
        </div>
        <div class="dash-modal-body">
            <div class="dash-form-group">
                <label>Program Title</label>
                <input type="text" placeholder="e.g. Youth Leadership Summit">
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Start Date</label>
                    <input type="date">
                </div>
                <div class="dash-form-group">
                    <label>End Date</label>
                    <input type="date">
                </div>
            </div>
            <div class="dash-form-row">
                <div class="dash-form-group">
                    <label>Budget (₱)</label>
                    <input type="number" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="dash-form-group">
                    <label>Status</label>
                    <select>
                        <option value="">Select</option>
                        <option>Planned</option><option>Ongoing</option><option>Completed</option>
                    </select>
                </div>
            </div>
            <div class="dash-form-group">
                <label>Description</label>
                <textarea rows="3" placeholder="Brief description…"></textarea>
            </div>
        </div>
        <div class="dash-modal-footer">
            <button class="dash-btn-cancel" data-close="modalCreateProgram">Cancel</button>
            <button class="dash-btn-save">Save Program</button>
        </div>
    </div>
</div>

<!-- Approve Requests -->
<div class="dash-modal-backdrop" id="modalApproveRequests" role="dialog" aria-modal="true" aria-labelledby="mTitleApprove">
    <div class="dash-modal-box">
        <div class="dash-modal-header">
            <h3 id="mTitleApprove">Approve KK Profiling Requests</h3>
            <button class="dash-modal-close" data-close="modalApproveRequests" aria-label="Close">&times;</button>
        </div>
        <div class="dash-modal-body">
            <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Review and act on pending requests below.</p>
            <div id="pendingRequestsList"></div>
        </div>
        <div class="dash-modal-footer">
            <button class="dash-btn-cancel" data-close="modalApproveRequests">Close</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Dashboard/assets/js/dashboard.js'
])

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
