import 'datatables.net-dt/css/dataTables.dataTables.css';

const dashboardMetrics = {
    totalManagedUsers: { value: 328, status: 'Stable load', severity: 'healthy' },
    skFederationAccounts: { value: 12, status: 'All assigned', severity: 'healthy' },
    skOfficialAccounts: { value: 312, status: '4 pending onboard', severity: 'warning' },
    activeAccounts: { value: 289, status: 'Within target', severity: 'healthy' },
    inactiveAccounts: { value: 21, status: 'Review needed', severity: 'warning' },
    pendingAccounts: { value: 8, status: 'Needs action', severity: 'warning' },
    suspendedAccounts: { value: 6, status: 'Open incidents', severity: 'critical' },
    unassignedAccounts: { value: 3, status: 'Role gap', severity: 'critical' },
};

const securityMetrics = {
    '24h': {
        loginSuccess: 486,
        loginFailure: 19,
        lockedAccounts: 3,
        twoFactorEnabled: 4,
        passwordResetRequests: 11,
    },
    '7d': {
        loginSuccess: 3329,
        loginFailure: 146,
        lockedAccounts: 12,
        twoFactorEnabled: 4,
        passwordResetRequests: 58,
    },
};

const governanceAlerts = [
    { key: 'terms30', label: 'Terms expiring in 30 days', value: 4, severity: 'critical' },
    { key: 'terms60', label: 'Terms expiring in 60 days', value: 11, severity: 'warning' },
    { key: 'pwdChange', label: 'Accounts requiring password change', value: 9, severity: 'warning' },
    { key: 'inactiveRoles', label: 'Inactive accounts with assigned roles', value: 2, severity: 'critical' },
    { key: 'missingBarangay', label: 'Missing barangay assignments', value: 3, severity: 'critical' },
    { key: 'cleanBarangay', label: 'Barangay records healthy', value: 23, severity: 'healthy' },
];

const auditEvents = [
    {
        timestamp: '2026-03-10 08:14:22',
        actor: 'admin.roxas',
        event: 'Reset SKOfficial password',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-10 08:02:01',
        actor: 'admin.roxas',
        event: 'Suspended account',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-10 07:38:19',
        actor: 'admin.dela.cruz',
        event: 'Failed role assignment',
        outcome: 'failure',
        ipAddress: '10.40.2.23',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-10 07:11:54',
        actor: 'admin.reyes',
        event: 'Created SKFederation account',
        outcome: 'success',
        ipAddress: '10.40.2.9',
        device: 'Firefox / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 22:48:40',
        actor: 'admin.reyes',
        event: 'Updated term assignment',
        outcome: 'success',
        ipAddress: '10.40.2.9',
        device: 'Firefox / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 21:26:33',
        actor: 'admin.klein',
        event: '2FA challenge failed',
        outcome: 'failure',
        ipAddress: '10.40.2.31',
        device: 'Safari / macOS',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 21:03:09',
        actor: 'admin.klein',
        event: 'Unlocked account',
        outcome: 'success',
        ipAddress: '10.40.2.31',
        device: 'Safari / macOS',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 20:41:17',
        actor: 'admin.santos',
        event: 'Bulk status update',
        outcome: 'success',
        ipAddress: '10.40.2.42',
        device: 'Chrome / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 20:22:43',
        actor: 'admin.santos',
        event: 'Forced password rotation',
        outcome: 'success',
        ipAddress: '10.40.2.42',
        device: 'Chrome / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-09 19:57:02',
        actor: 'admin.romero',
        event: 'Account import validation error',
        outcome: 'failure',
        ipAddress: '10.40.2.65',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
];

const platformHealth = [
    { key: 'supabase', label: 'Supabase database connection', status: 'healthy' },
    { key: 'redis', label: 'Redis cache', status: 'warning' },
    { key: 'failedJobs', label: 'Failed jobs queue', status: 'failure' },
    { key: 'scheduler', label: 'Scheduler heartbeat', status: 'healthy' },
];

const trendLabels = Array.from({ length: 30 }, (_, idx) => `Day ${idx + 1}`);
const createdSeries = [6, 8, 11, 7, 10, 9, 13, 12, 11, 14, 10, 15, 16, 14, 12, 11, 13, 12, 15, 16, 14, 12, 11, 13, 10, 9, 12, 14, 13, 11];
const deactivatedSeries = [1, 2, 1, 3, 2, 1, 2, 1, 2, 3, 2, 2, 1, 3, 2, 2, 3, 2, 1, 2, 3, 2, 1, 2, 3, 2, 2, 1, 2, 3];

const barangayDistribution = [
    { barangay: 'Aplaya', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 48 },
    { barangay: 'Alipit', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 45 },
    { barangay: 'Bagumbayan', skFederationAssigned: true, skOfficialsAssigned: 10, accountCount: 43 },
    { barangay: 'Bubukal', skFederationAssigned: false, skOfficialsAssigned: 8, accountCount: 31 },
    { barangay: 'Calios', skFederationAssigned: true, skOfficialsAssigned: 0, accountCount: 24 },
    { barangay: 'Duhat', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 44 },
    { barangay: 'Gatid', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 46 },
    { barangay: 'Labuin', skFederationAssigned: false, skOfficialsAssigned: 0, accountCount: 19 },
    { barangay: 'Malinao', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 47 },
    { barangay: 'Poblacion 1', skFederationAssigned: true, skOfficialsAssigned: 11, accountCount: 41 },
    { barangay: 'Poblacion 2', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 42 },
    { barangay: 'San Pablo Norte', skFederationAssigned: true, skOfficialsAssigned: 9, accountCount: 38 },
];

window.dashboardMetrics = dashboardMetrics;
window.securityMetrics = securityMetrics;
window.auditEvents = auditEvents;
window.barangayDistribution = barangayDistribution;

window.dashboardConsole = function dashboardConsole() {
    return {
        dashboardMetrics,
        securityMetrics,
        governanceAlerts,
        platformHealth,
        barangayDistribution,
        securityRange: '24h',

        get activeSecurityMetrics() {
            return this.securityMetrics[this.securityRange];
        },

        setSecurityRange(range) {
            this.securityRange = range;
        },

        federationBadge(row) {
            return row.skFederationAssigned ? 'pill--healthy' : 'pill--critical';
        },

        federationLabel(row) {
            return row.skFederationAssigned ? 'Assigned' : 'Missing';
        },

        overallGap(row) {
            return row.skFederationAssigned && row.skOfficialsAssigned > 0;
        },
    };
};

let accountsTrendChart;

function renderAuditRows(tableElement) {
    const body = tableElement.querySelector('tbody');
    if (!body) {
        return;
    }

    body.innerHTML = auditEvents
        .map((event) => {
            const outcomeClass = event.outcome === 'success' ? 'audit-outcome--success' : 'audit-outcome--failure';
            const outcomeLabel = event.outcome.toUpperCase();

            return `
                <tr>
                    <td>${event.timestamp}</td>
                    <td>${event.actor}</td>
                    <td>${event.event}</td>
                    <td><span class="audit-outcome ${outcomeClass}">${outcomeLabel}</span></td>
                    <td>${event.ipAddress}</td>
                    <td>${event.device}</td>
                    <td><a class="audit-action-link" href="#">${event.actionLabel}</a></td>
                </tr>
            `;
        })
        .join('');
}

function initAuditDataTable() {
    const tableElement = document.getElementById('auditActivityTable');
    if (!tableElement) {
        return;
    }

    renderAuditRows(tableElement);

    if (!window.DataTable || tableElement.dataset.enhanced === 'true') {
        return;
    }

    new window.DataTable(tableElement, {
        pageLength: 5,
        lengthChange: false,
        searching: false,
        info: false,
        order: [[0, 'desc']],
        pagingType: 'simple_numbers',
        columnDefs: [
            { orderable: false, targets: 6 },
        ],
    });

    tableElement.dataset.enhanced = 'true';
}

function initTrendChart() {
    const canvas = document.getElementById('accountsTrendChart');
    if (!canvas || !window.Chart) {
        return;
    }

    if (accountsTrendChart) {
        accountsTrendChart.destroy();
    }

    accountsTrendChart = new window.Chart(canvas, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Created accounts',
                    data: createdSeries,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.18)',
                    borderWidth: 2,
                    tension: 0.2,
                    pointRadius: 1.7,
                },
                {
                    label: 'Deactivated accounts',
                    data: deactivatedSeries,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.16)',
                    borderWidth: 2,
                    tension: 0.2,
                    pointRadius: 1.7,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#d7e5f8',
                        boxWidth: 14,
                        font: { family: 'JetBrains Mono, IBM Plex Mono, monospace', size: 11 },
                    },
                },
            },
            scales: {
                x: {
                    ticks: { color: '#9fb4d3', maxTicksLimit: 10 },
                    grid: { color: 'rgba(130, 157, 198, 0.16)' },
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#9fb4d3' },
                    grid: { color: 'rgba(130, 157, 198, 0.16)' },
                },
            },
        },
    });
}

function bootDashboardWidgets() {
    initAuditDataTable();
    initTrendChart();

    window.skDashboardRuntime = {
        alpineReady: typeof window.Alpine !== 'undefined',
        chartReady: typeof window.Chart !== 'undefined',
        dataTableReady: typeof window.DataTable !== 'undefined',
    };
}

let dashboardWidgetsBooted = false;

function canBootDashboardWidgets() {
    return typeof window.Chart !== 'undefined' && typeof window.DataTable !== 'undefined';
}

function tryBootDashboardWidgets() {
    if (dashboardWidgetsBooted) {
        return;
    }

    if (!document.getElementById('mainContent') || !canBootDashboardWidgets()) {
        return;
    }

    bootDashboardWidgets();
    dashboardWidgetsBooted = true;
}

window.addEventListener('sk:frontend-deps-ready', tryBootDashboardWidgets);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootDashboardWidgets);
} else {
    tryBootDashboardWidgets();
}
