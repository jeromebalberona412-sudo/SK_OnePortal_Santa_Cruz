@php
    $barangayOptions = ($barangays ?? collect())->map(fn ($barangay) => [
        'id' => $barangay->id,
        'name' => $barangay->name,
    ])->values();
@endphp
<script>window.__KK_BARANGAYS__ = @json($barangayOptions);</script>
<div class="dash-section-card">
	<div class="dash-section-header kk-chart-section-header">
		<div>
			<h2 class="dash-section-title">KK Profiling by Month</h2>
			<p class="dash-section-subtitle">Approved, pending, and rejected submissions</p>
		</div>
		<div class="kk-chart-header-actions">
			<label class="kk-barangay-filter" for="kkProfilingBarangayFilter">
				<span class="kk-barangay-filter-label">Barangay</span>
				<select id="kkProfilingBarangayFilter" class="kk-barangay-select" aria-label="Filter by barangay">
					<option value="all">All Barangays</option>
					@foreach ($barangays ?? [] as $barangay)
						<option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
					@endforeach
				</select>
			</label>
			<button type="button" class="panel-action-btn panel-action-btn--export" id="kkProfilingExportBtn">Export</button>
		</div>
	</div>
	<div class="chart-canvas-wrap">
		<canvas id="kkProfilingMonthlyChart" aria-label="KK Profiling monthly chart"></canvas>
	</div>
	<div class="line-chart-filter-row">
		<label class="line-chart-checkbox">
			<input type="checkbox" id="filterKkApproved" checked>
			<span class="line-chart-checkbox-box" style="background:#22c55e;"></span>
			<span>Approved</span>
		</label>
		<label class="line-chart-checkbox">
			<input type="checkbox" id="filterKkPending" checked>
			<span class="line-chart-checkbox-box" style="background:#f59e0b;"></span>
			<span>Pending</span>
		</label>
		<label class="line-chart-checkbox">
			<input type="checkbox" id="filterKkRejected" checked>
			<span class="line-chart-checkbox-box" style="background:#ef4444;"></span>
			<span>Rejected</span>
		</label>
	</div>
</div>
