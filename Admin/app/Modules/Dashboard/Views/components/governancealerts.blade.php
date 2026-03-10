<section class="gov-card panel governance-alerts-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Governance Alerts</h2>
            <p class="gov-card__subtitle">Policy, assignment, and term watchlist</p>
        </div>
    </header>

    <ul class="alert-list">
        <template x-for="alert in governanceAlerts" :key="alert.key">
            <li class="alert-item" :class="`severity-${alert.severity}`">
                <span class="alert-item__label" x-text="alert.label"></span>
                <span class="alert-item__value" x-text="alert.value"></span>
            </li>
        </template>
    </ul>
</section>
