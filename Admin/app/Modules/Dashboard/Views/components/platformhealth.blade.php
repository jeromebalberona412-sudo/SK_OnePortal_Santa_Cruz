<section class="gov-card panel platform-health-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Platform Health</h2>
            <p class="gov-card__subtitle">Operational dependency checklist</p>
        </div>
    </header>

    <ul class="health-list">
        <template x-for="check in platformHealth" :key="check.key">
            <li class="health-item" :class="`status-${check.status}`">
                <span class="health-item__label" x-text="check.label"></span>
                <span class="health-item__status" x-text="check.status.toUpperCase()"></span>
            </li>
        </template>
    </ul>
</section>
