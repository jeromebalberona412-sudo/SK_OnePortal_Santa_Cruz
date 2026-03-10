<section class="gov-card panel security-metrics-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Security Metrics</h2>
            <p class="gov-card__subtitle">Authentication and account protection</p>
        </div>

        <div aria-label="Security range toggle">
            <button
                type="button"
                class="range-toggle"
                :class="{ 'is-active': securityRange === '24h' }"
                @click="setSecurityRange('24h')"
            >
                24h
            </button>
            <button
                type="button"
                class="range-toggle"
                :class="{ 'is-active': securityRange === '7d' }"
                @click="setSecurityRange('7d')"
            >
                7d
            </button>
        </div>
    </header>

    <div class="metric-grid">
        <div class="metric-item">
            <span class="metric-item__label">Login success</span>
            <span class="metric-item__value" x-text="activeSecurityMetrics.loginSuccess"></span>
        </div>

        <div class="metric-item">
            <span class="metric-item__label">Login failure</span>
            <span class="metric-item__value" x-text="activeSecurityMetrics.loginFailure"></span>
        </div>

        <div class="metric-item">
            <span class="metric-item__label">Locked accounts</span>
            <span class="metric-item__value" x-text="activeSecurityMetrics.lockedAccounts"></span>
        </div>

        <div class="metric-item">
            <span class="metric-item__label">Admin with 2FA</span>
            <span class="metric-item__value" x-text="activeSecurityMetrics.twoFactorEnabled"></span>
        </div>

        <div class="metric-item">
            <span class="metric-item__label">Password reset requests</span>
            <span class="metric-item__value" x-text="activeSecurityMetrics.passwordResetRequests"></span>
        </div>
    </div>
</section>
