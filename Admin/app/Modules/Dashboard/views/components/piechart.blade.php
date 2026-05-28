@php
    $distribution = $userDistribution ?? ['federation' => 0, 'officials' => 0, 'kabataan' => 0];
@endphp
<div class="dash-section-card" x-data="pieChartFilter(@js($distribution))">
	<div class="dash-section-header">
		<div>
			<h2 class="dash-section-title">User Distribution</h2>
			<p class="dash-section-subtitle">Federation, SK Officials, and Kabataan overview</p>
		</div>
	</div>
	<div class="pie-chart-wrapper">
		<div class="pie-chart-container-small">
			<canvas id="userDistributionPieChart"></canvas>
		</div>
		<div class="pie-chart-legend-horizontal">
			<label class="legend-checkbox-item">
				<input type="checkbox" x-model="showFederation" @change="updateChart()">
				<span class="legend-color" style="background-color: #ef4444;"></span>
				<span class="legend-label">SK Federation</span>
				<span class="legend-value" x-text="formatCount(counts.federation)">0</span>
			</label>
			<label class="legend-checkbox-item">
				<input type="checkbox" x-model="showOfficials" @change="updateChart()">
				<span class="legend-color" style="background-color: #eab308;"></span>
				<span class="legend-label">SK Officials</span>
				<span class="legend-value" x-text="formatCount(counts.officials)">0</span>
			</label>
			<label class="legend-checkbox-item">
				<input type="checkbox" x-model="showKabataan" @change="updateChart()">
				<span class="legend-color" style="background-color: #22c55e;"></span>
				<span class="legend-label">Kabataan</span>
				<span class="legend-value" x-text="formatCount(counts.kabataan)">0</span>
			</label>
		</div>
	</div>
</div>
