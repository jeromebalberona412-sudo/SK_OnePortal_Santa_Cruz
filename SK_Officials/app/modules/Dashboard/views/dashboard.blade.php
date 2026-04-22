<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Dashboard/assets/css/dashboard.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content" id="mainContent">
<div class="dashboard-container">

    <!-- ══ Page Header ══════════════════════════════════════ -->
    <div class="dash-page-header">
        <div class="dash-page-header-left">
            <h1 class="dash-page-title">Dashboard</h1>
            <p class="dash-page-sub">Welcome back, <strong>Calios</strong> &mdash; SK Official</p>
        </div>
        <div class="dash-year-filter">
            <label for="yearSelect" class="dash-year-label">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                </svg>
                Year
            </label>
            <select id="yearSelect" class="dash-year-select">
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
            </select>
        </div>
    </div>

    <!-- ══ Stat Cards ════════════════════════════════════════ -->
    <div class="stats-cards-row">

        <!-- Total Kabataan -->
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon stat-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statKabataan">342</span>
                <span class="stat-card-label">Total Kabataan Registered</span>
            </div>
        </div>

        <!-- Total ABYIP Members -->
        <div class="stat-card stat-card-purple">
            <div class="stat-card-icon stat-icon-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statAbyip">87</span>
                <span class="stat-card-label">Total ABYIP Members</span>
            </div>
        </div>

        <!-- Pending KK Requests -->
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon stat-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statPending">14</span>
                <span class="stat-card-label">Pending KK Requests</span>
            </div>
        </div>

        <!-- Approved Requests -->
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon stat-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statApproved">198</span>
                <span class="stat-card-label">Approved Requests</span>
            </div>
        </div>

        <!-- Rejected Requests -->
        <div class="stat-card stat-card-red">
            <div class="stat-card-icon stat-icon-red">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statRejected">23</span>
                <span class="stat-card-label">Rejected Requests</span>
            </div>
        </div>

        <!-- Active Programs -->
        <div class="stat-card stat-card-teal">
            <div class="stat-card-icon stat-icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statPrograms">9</span>
                <span class="stat-card-label">Active Programs</span>
            </div>
        </div>

        <!-- Total Budget -->
        <div class="stat-card stat-card-yellow">
            <div class="stat-card-icon stat-icon-yellow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1v22"></path>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-card-body">
                <span class="stat-card-value" id="statBudget">₱1.42M</span>
                <span class="stat-card-label">Total Budget Allocated</span>
            </div>
        </div>

    </div><!-- /stats-cards-row -->

    <!-- ══ Quick Actions ═════════════════════════════════════ -->
    <div class="dash-section-card">
        <div class="dash-section-header">
            <div>
                <h2 class="dash-section-title">Quick Actions</h2>
                <p class="dash-section-sub">Perform common tasks instantly</p>
            </div>
        </div>
        <div class="quick-actions-grid">
            <button class="qa-btn qa-green" id="qaApproveRequests">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Approve Requests
            </button>
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
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Monthly KK Requests</h2>
                    <p class="dash-section-sub">Approved vs Rejected over time</p>
                </div>
                <span class="dash-chart-badge">Line Chart</span>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="chartMonthlyRequests"></canvas>
            </div>
        </div>

    </div>

    <!-- ══ Charts Row 2: Pie + Donut ═════════════════════════ -->
    <div class="charts-grid-2">

        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">ABYIP Status Distribution</h2>
                    <p class="dash-section-sub">Active, Inactive, Pending breakdown</p>
                </div>
                <span class="dash-chart-badge">Pie Chart</span>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--pie">
                <canvas id="chartAbyipStatus"></canvas>
            </div>
            <div class="pie-legend-row" id="abyipLegend">
                <div class="pie-legend-item">
                    <span class="pie-legend-dot" style="background:#22c55e;"></span>
                    <span class="pie-legend-label">Active</span>
                    <span class="pie-legend-pct">62%</span>
                </div>
                <div class="pie-legend-item">
                    <span class="pie-legend-dot" style="background:#f59e0b;"></span>
                    <span class="pie-legend-label">Pending</span>
                    <span class="pie-legend-pct">24%</span>
                </div>
                <div class="pie-legend-item">
                    <span class="pie-legend-dot" style="background:#ef4444;"></span>
                    <span class="pie-legend-label">Inactive</span>
                    <span class="pie-legend-pct">14%</span>
                </div>
            </div>
        </div>

        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Budget Allocation</h2>
                    <p class="dash-section-sub">Per program breakdown</p>
                </div>
                <span class="dash-chart-badge">Donut</span>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--pie">
                <canvas id="chartBudgetDonut"></canvas>
            </div>
            <div class="donut-legend" id="donutLegend"></div>
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
            </div>
            <div class="activity-list" id="activityList"></div>
        </div>

        <!-- Recent Announcements -->
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div>
                    <h2 class="dash-section-title">Announcements</h2>
                    <p class="dash-section-sub">Latest notices</p>
                </div>
                <a href="{{ route('announcements') }}" class="dash-view-all">View all</a>
            </div>
            <div class="announcements-list" id="announcementsList"></div>
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
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Dashboard/assets/js/dashboard.js'
])

</body>
</html>
