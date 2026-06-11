@php
    $barangayOptions = ($barangays ?? collect())->map(fn ($barangay) => [
        'id' => $barangay->id,
        'name' => $barangay->name,
    ])->values();
@endphp
<script>
    window.__KK_BARANGAYS__ = @json($barangayOptions);
    window.__KK_PROFILING_DATA_URL__ = @json(route('dashboard.kk-profiling-data'));
</script>
<div class="dash-section-card">
	<div class="dash-section-header kk-chart-section-header">
		<div>
			<h2 class="dash-section-title">KK Profiling by Month</h2>
			<p class="dash-section-subtitle" id="kkProfilingChartSubtitle">Approved, pending, and rejected submissions</p>
		</div>
		<div class="kk-chart-header-actions">
			<select id="kkProfilingBarangayFilter" class="kk-barangay-select" aria-label="Filter by barangay">
				<option value="all">All Barangays</option>
				@foreach ($barangays ?? [] as $barangay)
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
			<button type="button" class="panel-action-btn panel-action-btn--export" id="kkProfilingExportBtn">Export CSV</button>
		</div>
	</div>
	<div class="chart-canvas-wrap">
		<canvas id="kkProfilingMonthlyChart" aria-label="KK Profiling chart"></canvas>
	</div>
	<div class="kk-chart-filter-row">
		<label class="kk-chart-filter-check kk-chart-filter-check--approved">
			<input type="checkbox" id="filterKkApproved" checked>
			<span class="kk-chart-filter-dot kk-chart-filter-dot--approved" aria-hidden="true"></span>
			<span>Approved</span>
		</label>
		<label class="kk-chart-filter-check kk-chart-filter-check--pending">
			<input type="checkbox" id="filterKkPending" checked>
			<span class="kk-chart-filter-dot kk-chart-filter-dot--pending" aria-hidden="true"></span>
			<span>Pending</span>
		</label>
		<label class="kk-chart-filter-check kk-chart-filter-check--rejected">
			<input type="checkbox" id="filterKkRejected" checked>
			<span class="kk-chart-filter-dot kk-chart-filter-dot--rejected" aria-hidden="true"></span>
			<span>Rejected</span>
		</label>
	</div>
</div>
