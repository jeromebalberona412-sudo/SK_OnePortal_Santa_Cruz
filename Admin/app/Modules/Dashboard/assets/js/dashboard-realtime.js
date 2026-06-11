const DASHBOARD_POLL_MS = 20000;
let dashboardTermFilterCache = { years: [], terms: [] };

function getDashboardFilterParams() {
    const yearSelect = document.getElementById('yearSelect');
    const termSelect = document.getElementById('termSelect');

    return {
        year: yearSelect?.value || 'all',
        term: termSelect?.value || 'all',
    };
}

function termsForYear(terms, year) {
    if (!year || year === 'all') {
        return terms;
    }

    return terms.filter((term) => {
        const [start, end] = String(term.value || '').split('|');
        const startYear = start?.slice(0, 4);
        const endYear = end?.slice(0, 4);

        return startYear === year || endYear === year;
    });
}

function populateDashboardTermFilters(filters, preserveSelections = true) {
    const yearSelect = document.getElementById('yearSelect');
    const termSelect = document.getElementById('termSelect');
    dashboardTermFilterCache = {
        years: Array.isArray(filters?.years) ? filters.years : [],
        terms: Array.isArray(filters?.terms) ? filters.terms : [],
    };

    if (yearSelect && dashboardTermFilterCache.years.length) {
        const currentYear = preserveSelections ? yearSelect.value : 'all';
        const yearOptions = ['<option value="all">All Years</option>']
            .concat(dashboardTermFilterCache.years.map((year) => `<option value="${year}">${year}</option>`));
        yearSelect.innerHTML = yearOptions.join('');

        if ([...yearSelect.options].some((option) => option.value === currentYear)) {
            yearSelect.value = currentYear;
        } else if (dashboardTermFilterCache.years.includes(String(new Date().getFullYear()))) {
            yearSelect.value = String(new Date().getFullYear());
        }
    }

    if (termSelect) {
        const currentTerm = preserveSelections ? termSelect.value : 'all';
        const scopedTerms = termsForYear(dashboardTermFilterCache.terms, yearSelect?.value || 'all');
        const termOptions = ['<option value="all">All Terms</option>']
            .concat(scopedTerms.map((term) => `<option value="${term.value}">${term.label}</option>`));
        termSelect.innerHTML = termOptions.join('');

        if ([...termSelect.options].some((option) => option.value === currentTerm)) {
            termSelect.value = currentTerm;
        } else {
            termSelect.value = 'all';
        }
    }
}

function updateDashboardMetrics(metrics) {
    if (!metrics) {
        return;
    }

    const labelMap = {
        totalUsers: 'Total Users',
        federationAccounts: 'Total SK Federations',
        officialAccounts: 'Total SK Officials',
        kabataanAccounts: 'Total Kabataan',
        deletedSkFederation: 'Deleted SK Federation',
        deletedSkOfficials: 'Deleted SK Officials',
        skFederationRecords: 'SK Federation Records',
        skOfficialsRecords: 'SK Officials Records',
    };

    Object.entries(labelMap).forEach(([key, label]) => {
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card) => {
            const labelEl = card.querySelector('.stat-card-label');
            if (!labelEl || labelEl.textContent.trim() !== label) {
                return;
            }

            const valueEl = card.querySelector('.stat-card-value');
            if (valueEl && metrics[key] !== undefined) {
                valueEl.textContent = Number(metrics[key]).toLocaleString();
            }
        });
    });
}

async function fetchDashboardData() {
    const endpoint = window.__DASHBOARD_DATA_URL__;
    if (!endpoint) {
        return null;
    }

    const params = new URLSearchParams(getDashboardFilterParams());

    try {
        const response = await fetch(`${endpoint}?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return null;
        }

        return await response.json();
    } catch (error) {
        return null;
    }
}

async function refreshDashboardPanels() {
    const payload = await fetchDashboardData();
    if (!payload) {
        return;
    }

    if (payload.termFilters) {
        populateDashboardTermFilters(payload.termFilters);
    }

    if (payload.metrics) {
        updateDashboardMetrics(payload.metrics);
    }

    if (payload.recentAuditActivity && typeof window.refreshDashboardAuditTable === 'function') {
        window.refreshDashboardAuditTable(payload.recentAuditActivity);
    }

    if (payload.barangayDistribution && typeof window.refreshBarangayDistribution === 'function') {
        window.refreshBarangayDistribution(payload.barangayDistribution);
    }

    if (typeof window.refreshKkProfilingChart === 'function') {
        window.refreshKkProfilingChart();
    }
}

function wireDashboardFilterControls() {
    const yearSelect = document.getElementById('yearSelect');
    const termSelect = document.getElementById('termSelect');

    yearSelect?.addEventListener('change', () => {
        populateDashboardTermFilters(dashboardTermFilterCache);
        refreshDashboardPanels();
    });

    termSelect?.addEventListener('change', () => {
        refreshDashboardPanels();
    });
}

function initDashboardRealtime() {
    if (!document.getElementById('mainContent')?.classList.contains('dashboard-shell')) {
        return;
    }

    populateDashboardTermFilters(window.__DASHBOARD_TERM_FILTERS__ || { years: [], terms: [] });
    wireDashboardFilterControls();
    refreshDashboardPanels();
    window.setInterval(() => {
        if (!document.hidden) {
            refreshDashboardPanels();
        }
    }, DASHBOARD_POLL_MS);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshDashboardPanels();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardRealtime);
} else {
    initDashboardRealtime();
}
