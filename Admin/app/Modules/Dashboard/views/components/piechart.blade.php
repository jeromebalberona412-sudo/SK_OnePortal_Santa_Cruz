<div class="dash-section-card" x-data="pieChartFilter()">
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
				<span class="legend-value">1</span>
			</label>
			<label class="legend-checkbox-item">
				<input type="checkbox" x-model="showOfficials" @change="updateChart()">
				<span class="legend-color" style="background-color: #eab308;"></span>
				<span class="legend-label">SK Officials</span>
				<span class="legend-value">260</span>
			</label>
			<label class="legend-checkbox-item">
				<input type="checkbox" x-model="showKabataan" @change="updateChart()">
				<span class="legend-color" style="background-color: #22c55e;"></span>
				<span class="legend-label">Kabataan</span>
				<span class="legend-value">40,000</span>
			</label>
		</div>
	</div>
</div>
