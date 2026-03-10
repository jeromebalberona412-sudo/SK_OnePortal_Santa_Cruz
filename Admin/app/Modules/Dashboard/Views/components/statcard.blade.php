<article class="gov-card stat-card stat-card--{{ $tone }}">
    <p class="stat-card__label">{{ $label }}</p>
    <p class="stat-card__value" x-text="dashboardMetrics.{{ $metricKey }}.value"></p>
    <div class="stat-card__meta">
        <span class="status-dot status-dot--{{ $tone }}" aria-hidden="true"></span>
        <span x-text="dashboardMetrics.{{ $metricKey }}.status"></span>
    </div>
</article>
