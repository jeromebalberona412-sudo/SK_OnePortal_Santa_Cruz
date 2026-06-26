@extends('layout::app')

@section('title', 'Dashboard')

@section('content')
{{-- Page Header --}}
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, {{ $user->name ?? 'SK Official' }}</p>
        </div>

        {{-- ── STAT CARDS ── --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:16px;margin-bottom:24px;">
            <a href="{{ route('kabataan-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ number_format($totalKabataanRegistered ?? 0) }}</div>
                    <div class="stat-label">Total Kabataan Registered</div>
                </div>
            </a>
            <a href="{{ route('accounts.officials.index') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon indigo"><i class="fas fa-user-tie"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ number_format($totalSkOfficials ?? 0) }}</div>
                    <div class="stat-label">Total SK Officials</div>
                </div>
            </a>
            <a href="{{ route('accounts.federation.index') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon teal"><i class="fas fa-crown"></i></div>
                <div class="stat-info">
                    <div class="stat-value">{{ number_format($totalSkChairpersons ?? 0) }}</div>
                    <div class="stat-label">Total SK Chairpersons</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon yellow"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Programs This Year</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Active Programs</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Completed Programs</div>
                </div>
            </a>
            <a href="{{ route('barangay-monitoring') }}" class="stat-card stat-card-link stat-card-clickable">
                <div class="stat-icon orange"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-info">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Barangays Reporting</div>
                </div>
            </a>
        </div>
        
        <style>
            @media (max-width: 1400px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(4, 1fr) !important;
                }
            }
            @media (max-width: 992px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
            }
            @media (max-width: 768px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            @media (max-width: 480px) {
                .stats-grid, div[style*="grid-template-columns:repeat(7,1fr)"] {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.stat-card-clickable').forEach(function(card) {
                    card.addEventListener('click', function(e) {
                        e.preventDefault();
                        const label = this.querySelector('.stat-label').textContent.trim();
                        if (typeof LoadingScreen !== 'undefined') {
                            LoadingScreen.show('Loading ' + label, 'Please wait...');
                        }
                        setTimeout(() => { window.location.href = this.href; }, 300);
                    });
                });
            });
        </script>

        {{-- ── QUICK ACTIONS ── --}}
        <div class="content-card" style="margin-bottom:24px;">
            <div class="card-header">
                <h3><i class="fas fa-bolt" style="color:#213F99;margin-right:8px;"></i>Quick Actions</h3>
            </div>
            <div class="card-body" style="padding:20px;overflow-x:auto;">
                <div style="display:flex;gap:12px;flex-wrap:wrap;min-width:fit-content;">
                    <a href="{{ route('profile') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#8b5cf6;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(139,92,246,0.3);">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="{{ route('barangay-monitoring') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(37,99,235,0.3);">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Barangay Monitoring</span>
                    </a>
                    <a href="{{ route('kabataan-monitoring') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#0ea5e9;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(14,165,233,0.3);">
                        <i class="fas fa-users"></i>
                        <span>Kabataan Monitoring</span>
                    </a>
                    <a href="{{ route('community-feed') }}" class="qa-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;background:#10b981;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:500;white-space:nowrap;transition:all 0.2s ease;box-shadow:0 1px 3px rgba(16,185,129,0.3);">
                        <i class="fas fa-rss"></i>
                        <span>SK Community Feed</span>
                    </a>
                </div>
            </div>
        </div>
        
        <style>
            .qa-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                opacity: 0.9;
            }
            
            @media (max-width: 768px) {
                .card-body > div[style*="display:flex"] {
                    flex-wrap: nowrap !important;
                    overflow-x: auto !important;
                    padding-bottom: 8px;
                }
                .qa-btn {
                    flex-shrink: 0;
                }
            }
        </style>

        {{-- ── ROW: Sex Distribution + Federation Officers ── --}}
        <div class="dash-row">
            <div class="content-card dash-col-5">
                <div class="card-header">
                    <h3><i class="fas fa-venus-mars" style="color:#213F99;margin-right:8px;"></i>Sex Distribution</h3>
                </div>
                <div class="card-body chart-body dash-sex-chart-body">
                    <canvas id="sexChart"></canvas>
                </div>
            </div>

            <div class="content-card dash-col-7" id="federation-section">
                <div class="card-header">
                    <h3><i class="fas fa-sitemap" style="color:#213F99;margin-right:8px;"></i>Federation Officers</h3>
                </div>
                <div class="card-body federation-officers-body">
                    <table class="federation-officers-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>
                                    Full Name
                                    <div class="table-col-hint">LN, FN, MN, Suffix</div>
                                </th>
                                <th>Barangay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($federationOfficers as $officer)
                                <tr class="{{ $officer['name'] === 'Vacant' ? 'is-vacant' : '' }}">
                                    <td>{{ $officer['position'] }}</td>
                                    <td>{{ $officer['name'] }}</td>
                                    <td>{{ $officer['barangay'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── ROW: Top 5 Barangays + KK Profiling ── --}}
        <div class="dash-row">
            <div class="content-card dash-col-4" id="top-barangays-section">
                <div class="card-header">
                    <h3><i class="fas fa-trophy" style="color:#213F99;margin-right:8px;"></i>Top 5 Barangays by Youth Population</h3>
                </div>
                <div class="card-body top-barangays-body">
                    @forelse ($topBarangays as $row)
                        <div class="top-barangay-item">
                            <div class="top-barangay-rank">{{ $row['rank'] }}</div>
                            <div class="top-barangay-info">
                                <div class="top-barangay-name">{{ $row['barangay'] }}</div>
                                <div class="top-barangay-count">{{ number_format($row['count']) }} youth registered</div>
                            </div>
                            <div class="top-barangay-bar-wrap">
                                @php
                                    $maxCount = max(1, $topBarangays[0]['count'] ?? 1);
                                    $width = round(($row['count'] / $maxCount) * 100);
                                @endphp
                                <div class="top-barangay-bar" style="width: {{ $width }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="dash-empty-note">No youth registration data available yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="content-card dash-col-8" id="kk-profiling-section">
                <div class="card-header kk-chart-section-header">
                    <div>
                        <h3><i class="fas fa-chart-line" style="color:#213F99;margin-right:8px;"></i>KK Profiling by Month</h3>
                        <p class="kk-chart-subtitle" id="kkProfilingChartSubtitle">Approved, pending, and rejected submissions</p>
                    </div>
                    <div class="kk-chart-header-actions">
                        <select id="kkProfilingBarangayFilter" class="kk-barangay-select" aria-label="Filter by barangay">
                            <option value="all">All Barangays</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                            @endforeach
                        </select>
                        <select id="kkProfilingPeriodFilter" class="kk-barangay-select" aria-label="Filter by period">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                        </select>
                        <select id="kkProfilingMonthFilter" class="kk-barangay-select kk-month-filter" hidden aria-label="Filter by month">
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
                        <button type="button" class="kk-export-btn" id="kkProfilingExportBtn">Export CSV</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="kk-chart-canvas-wrap">
                        <canvas id="kkProfilingMonthlyChart"></canvas>
                    </div>
                    <div class="kk-chart-filter-row">
                        <label class="kk-chart-filter-check kk-chart-filter-check--approved">
                            <input type="checkbox" id="filterKkApproved" checked>
                            <span class="kk-chart-filter-dot kk-chart-filter-dot--approved"></span>
                            <span>Approved</span>
                        </label>
                        <label class="kk-chart-filter-check kk-chart-filter-check--pending">
                            <input type="checkbox" id="filterKkPending" checked>
                            <span class="kk-chart-filter-dot kk-chart-filter-dot--pending"></span>
                            <span>Pending</span>
                        </label>
                        <label class="kk-chart-filter-check kk-chart-filter-check--rejected">
                            <input type="checkbox" id="filterKkRejected" checked>
                            <span class="kk-chart-filter-dot kk-chart-filter-dot--rejected"></span>
                            <span>Rejected</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Recent Activity + Upcoming Events ── --}}
        @include('dashboard::partials.dashboard-recent-activity')

        {{-- ── Audit Logs Table (compact) ── --}}
        <div class="dash-audit-row dash-audit-row--compact">
            @include('dashboard::partials.dashboard-audit-table')
        </div>
@endsection

@push('scripts')
@php
    $kkProfilingJsVersion = @filemtime(app_path('Modules/Dashboard/assets/js/kkprofilingchart.js')) ?: time();
    $dashAuditJsVersion = @filemtime(app_path('Modules/Dashboard/assets/js/dashboard-audit.js')) ?: time();
    $dashActivityJsVersion = @filemtime(app_path('Modules/Dashboard/assets/js/dashboard-activity.js')) ?: time();
@endphp
<script>
    window.__KK_BARANGAYS__ = @json($kkBarangayOptions);
    window.__KK_PROFILING_DATA_URL__ = @json(route('dashboard.kk-profiling-data'));
    window.__DASHBOARD_FEED__ = {
        recent_activity: @json($recentActivity ?? []),
        upcoming_events: @json($upcomingEvents ?? []),
        activities_url: @json(route('dashboard.recent-activities')),
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ url('/modules/dashboard/js/kkprofilingchart.js') }}?v={{ $kkProfilingJsVersion }}"></script>
<script src="{{ url('/modules/dashboard/js/dashboard-activity.js') }}?v={{ $dashActivityJsVersion }}"></script>
<script src="{{ url('/modules/dashboard/js/dashboard-audit.js') }}?v={{ $dashAuditJsVersion }}"></script>
<script>
        const fedBlue = '#213F99', fedRed = '#d0242b';
        const sexDistribution = @json($sexDistribution);

        new Chart(document.getElementById('sexChart'), {
            type: 'pie',
            data: {
                labels: sexDistribution.labels || ['Male', 'Female'],
                datasets: [{
                    data: sexDistribution.values || [0, 0],
                    backgroundColor: [fedBlue, fedRed],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 13 }, padding: 16 },
                    },
                },
            },
        });
</script>
<script>
        (() => {
            const heartbeatMs = {{ (int) config('sk_fed_auth.single_session.heartbeat_interval_seconds', 30) }} * 1000;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            let id = null;
            async function beat() {
                try {
                    await fetch("{{ route('skfed.heartbeat') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin', body: JSON.stringify({}),
                    });
                } catch (_) {}
            }
            beat();
            id = setInterval(beat, heartbeatMs);
            window.addEventListener('beforeunload', () => clearInterval(id));
        })();
    </script>
@endpush
