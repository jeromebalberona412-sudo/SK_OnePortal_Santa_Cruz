<section class="gov-card panel platform-health-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Platform Health: Real-Time Display</h2>
            <p class="gov-card__subtitle">Live uptime score and service pulse monitor</p>
        </div>

        <span class="live-pill" :class="'live-pill--' + platformHealthTelemetry.statusTone" x-text="platformHealthTelemetry.statusLabel"></span>
    </header>

    <div class="health-graph-grid">
        <div class="health-gauge-wrap">
            <canvas id="platformHealthGaugeChart" aria-label="Platform health gauge"></canvas>

            <div class="health-gauge-center">
                <span class="health-gauge-center__label">HEALTH SCORE</span>
                <strong class="health-gauge-center__value" x-text="platformHealthTelemetry.score + '%'">0%</strong>
            </div>
        </div>

        <div class="health-pulse-wrap">
            <canvas id="platformHealthPulseChart" aria-label="Platform health pulse"></canvas>
        </div>
    </div>

    <ul class="health-metric-list">
        <template x-for="metric in platformHealthTelemetry.metrics" :key="metric.key">
            <li class="health-metric-item">
                <span class="health-metric-item__label" x-text="metric.label"></span>
                <strong class="health-metric-item__value" x-text="metric.value"></strong>
            </li>
        </template>
    </ul>
</section>
