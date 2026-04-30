<article class="gov-card stat-card stat-card--{{ $tone }}">
    <header class="stat-card__top">
        <p class="stat-card__label">{{ $label }}</p>

        <span class="stat-card__icon stat-card__icon--{{ $tone }}" aria-hidden="true">
            @switch($icon ?? '')
                @case('users')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M16 20a4 4 0 0 0-8 0" />
                        <circle cx="12" cy="10" r="3" />
                        <path d="M7 20a3.5 3.5 0 0 0-3-2" />
                        <path d="M17 18a3.5 3.5 0 0 1 3 2" />
                    </svg>
                    @break
                @case('federation')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="7" height="7" rx="1.4" />
                        <rect x="14" y="4" width="7" height="7" rx="1.4" />
                        <rect x="8.5" y="14" width="7" height="7" rx="1.4" />
                        <path d="M10 7.5h4" />
                        <path d="M12 11v3" />
                    </svg>
                    @break
                @case('officials')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 4l8 4v2H4V8l8-4z" />
                        <path d="M5 10v7" />
                        <path d="M9 10v7" />
                        <path d="M15 10v7" />
                        <path d="M19 10v7" />
                        <path d="M3 19h18" />
                    </svg>
                    @break
                @case('kabataan')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    @break
                @default
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 13a8 8 0 1 1 16 0" />
                        <path d="M6 13h2" />
                        <path d="M16 13h2" />
                        <path d="M12 13l3-3" />
                    </svg>
            @endswitch
        </span>
    </header>

    <p class="stat-card__value" x-text="dashboardMetrics.{{ $metricKey }}.value"></p>

    <div class="stat-card__meta">
        <span class="status-dot" :class="'status-dot--' + dashboardMetrics.{{ $metricKey }}.statusTone" aria-hidden="true"></span>
        <span class="stat-card__status" x-text="dashboardMetrics.{{ $metricKey }}.status"></span>
        <span class="stat-card__delta" x-text="dashboardMetrics.{{ $metricKey }}.delta"></span>
    </div>
</article>
