import 'datatables.net-dt/css/dataTables.dataTables.css';

const dashboardMetrics = {
    totalUsers: { value: '0', status: 'System Stable', delta: '+2.7%', statusTone: 'healthy' },
    federationAccounts: { value: '0', status: 'Within Quota', delta: '+1.2%', statusTone: 'healthy' },
    officialAccounts: { value: '0', status: 'Active Review', delta: '+3.1%', statusTone: 'warning' },
    currentActiveAccounts: { value: '0', status: 'Online Now', delta: '+5.4%', statusTone: 'healthy' },
    kabataanAccounts: { value: '0', status: 'Registered Youth', delta: '+4.8%', statusTone: 'healthy' },
    totalBarangay: { value: '0', status: 'Locations', delta: '0%', statusTone: 'healthy' },
    deletedSkFederation: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    deletedSkOfficials: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    archivedData: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skFederationRecords: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skOfficialsRecords: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    skOfficialsArchive: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    deletedKabataan: { value: '0', status: 'Archived', delta: '0%', statusTone: 'critical' },
    rejectedKkProfiling: { value: '0', status: 'Rejected', delta: '0%', statusTone: 'critical' },
    rejectedScholarships: { value: '0', status: 'Rejected', delta: '0%', statusTone: 'critical' },
};

const usersActiveLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
const activeFederationSeries = [184, 201, 218, 242, 266, 281];
const activeOfficialSeries = [622, 648, 671, 698, 724, 748];
const activeKabataanSeries = [312, 338, 360, 385, 409, 428];

const auditEvents = [
    {
        timestamp: '2026-03-12 08:14:22',
        actor: 'admin.roxas',
        event: 'Reset official password',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 08:02:01',
        actor: 'admin.roxas',
        event: 'Federation account sync',
        outcome: 'success',
        ipAddress: '10.40.2.18',
        device: 'Chrome / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 07:38:19',
        actor: 'admin.delacruz',
        event: 'Failed role assignment',
        outcome: 'failure',
        ipAddress: '10.40.2.23',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 07:11:54',
        actor: 'admin.reyes',
        event: 'Created federation account',
        outcome: 'success',
        ipAddress: '10.40.2.9',
        device: 'Firefox / Linux',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 06:48:40',
        actor: 'admin.klein',
        event: 'Audit export requested',
        outcome: 'warning',
        ipAddress: '10.40.2.31',
        device: 'Safari / macOS',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-12 06:12:03',
        actor: 'admin.santos',
        event: 'Bulk status update',
        outcome: 'success',
        ipAddress: '10.40.2.42',
        device: 'Chrome / Windows',
        actionLabel: 'Open',
    },
    {
        timestamp: '2026-03-11 23:57:02',
        actor: 'admin.romero',
        event: 'Account import validation error',
        outcome: 'failure',
        ipAddress: '10.40.2.65',
        device: 'Edge / Windows',
        actionLabel: 'Open',
    },
];

const barangayDistribution = [
    { barangay: 'Aplaya', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 48 },
    { barangay: 'Alipit', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 45 },
    { barangay: 'Bagumbayan', skFederationAssigned: true, skOfficialsAssigned: 10, accountCount: 43 },
    { barangay: 'Bubukal', skFederationAssigned: false, skOfficialsAssigned: 8, accountCount: 31 },
    { barangay: 'Calios', skFederationAssigned: true, skOfficialsAssigned: 9, accountCount: 38 },
    { barangay: 'Duhat', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 44 },
    { barangay: 'Gatid', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 46 },
    { barangay: 'Labuin', skFederationAssigned: false, skOfficialsAssigned: 0, accountCount: 19 },
    { barangay: 'Malinao', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 47 },
    { barangay: 'Poblacion 1', skFederationAssigned: true, skOfficialsAssigned: 11, accountCount: 41 },
    { barangay: 'Poblacion 2', skFederationAssigned: true, skOfficialsAssigned: 12, accountCount: 42 },
    { barangay: 'San Pablo Norte', skFederationAssigned: true, skOfficialsAssigned: 9, accountCount: 38 },
];

const pulseBaseData = [94, 95, 96, 97, 96, 98, 97, 99, 98, 97, 98, 99];
const pulseLabels = ['-55s', '-50s', '-45s', '-40s', '-35s', '-30s', '-25s', '-20s', '-15s', '-10s', '-5s', 'Now'];

const platformHealthTelemetry = {
    score: 97,
    statusLabel: 'Live: Healthy',
    statusTone: 'healthy',
    metrics: [
        { key: 'uptime', label: 'Uptime', value: '99.93%' },
        { key: 'latency', label: 'Latency', value: '116 ms' },
        { key: 'jobs', label: 'Queue Success', value: '98.7%' },
    ],
};

window.dashboardMetrics = dashboardMetrics;
window.auditEvents = auditEvents;
window.barangayDistribution = barangayDistribution;
window.platformHealthTelemetry = platformHealthTelemetry;

window.dashboardConsole = function dashboardConsole() {
    return {
        dashboardMetrics,
        platformHealthTelemetry,
        barangayDistribution,

        federationBadge(row) {
            return row.skFederationAssigned ? 'pill--healthy' : 'pill--critical';
        },

        federationLabel(row) {
            return row.skFederationAssigned ? 'Assigned' : 'Missing';
        },

        overallGap(row) {
            return row.skFederationAssigned && row.skOfficialsAssigned > 0;
        },

        refreshHealthTelemetry() {
            if (typeof window.skRefreshPlatformHealth === 'function') {
                window.skRefreshPlatformHealth();
            }
        },
    };
};

window.pieChartFilter = function pieChartFilter() {
    return {
        showFederation: true,
        showOfficials: true,
        showKabataan: true,

        updateChart() {
            if (typeof window.updatePieChartData === 'function') {
                window.updatePieChartData(this.showFederation, this.showOfficials, this.showKabataan);
            }
        }
    };
};

let usersActiveChart;
let platformHealthGaugeChart;
let platformHealthPulseChart;
let userDistributionPieChart;
let healthPulseIntervalId;

function computeHealthTone(score) {
    if (score >= 96) {
        return { tone: 'healthy', label: 'Live: Healthy' };
    }

    if (score >= 90) {
        return { tone: 'warning', label: 'Live: Degraded' };
    }

    return { tone: 'critical', label: 'Live: Critical' };
}

function renderAuditRows(tableElement) {
    const body = tableElement.querySelector('tbody');
    if (!body) {
        return;
    }

    body.innerHTML = auditEvents
        .map((event) => {
            const outcomeClassByState = {
                success: 'audit-outcome--success',
                warning: 'audit-outcome--warning',
                failure: 'audit-outcome--failure',
            };

            const outcomeClass = outcomeClassByState[event.outcome] || 'audit-outcome--warning';
            const channelLabel = event.device.split('/')[0].trim();

            return `
                <tr>
                    <td>${event.timestamp}</td>
                    <td>${event.actor}</td>
                    <td>${event.event}</td>
                    <td><span class="audit-outcome ${outcomeClass}">${event.outcome.toUpperCase()}</span></td>
                    <td>${event.ipAddress}</td>
                    <td>${channelLabel}</td>
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
        columnDefs: [{ orderable: false, targets: 6 }],
    });

    tableElement.dataset.enhanced = 'true';
}

function initUsersActiveChart() {
    const canvas = document.getElementById('usersActiveChart');
    if (!canvas || !window.Chart) {
        return;
    }

    if (usersActiveChart) {
        usersActiveChart.destroy();
    }

    usersActiveChart = new window.Chart(canvas, {
        type: 'line',
        data: {
            labels: usersActiveLabels,
            datasets: [
                {
                    label: 'Federation Active Users',
                    data: activeFederationSeries,
                    borderColor: '#4dc5ff',
                    backgroundColor: 'rgba(77, 197, 255, 0.16)',
                    pointBackgroundColor: '#4dc5ff',
                    pointBorderColor: '#4dc5ff',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.34,
                    fill: true,
                },
                {
                    label: 'Official Active Users',
                    data: activeOfficialSeries,
                    borderColor: '#9d8bff',
                    backgroundColor: 'rgba(157, 139, 255, 0.14)',
                    pointBackgroundColor: '#9d8bff',
                    pointBorderColor: '#9d8bff',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.34,
                    fill: true,
                },
                {
                    label: 'Kabataan Active Users',
                    data: activeKabataanSeries,
                    borderColor: '#2de2ce',
                    backgroundColor: 'rgba(45, 226, 206, 0.12)',
                    pointBackgroundColor: '#2de2ce',
                    pointBorderColor: '#2de2ce',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.34,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 17, 34, 0.96)',
                    borderColor: 'rgba(88, 130, 222, 0.7)',
                    borderWidth: 1,
                    titleColor: '#e8f1ff',
                    bodyColor: '#d8e6ff',
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: '#9eb7df',
                        maxTicksLimit: 6,
                    },
                    grid: {
                        color: 'rgba(95, 131, 211, 0.2)',
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#9eb7df',
                    },
                    grid: {
                        color: 'rgba(95, 131, 211, 0.2)',
                    },
                },
            },
        },
    });
}

function initUserDistributionPieChart() {
    const canvas = document.getElementById('userDistributionPieChart');
    if (!canvas || !window.Chart) {
        return;
    }

    if (userDistributionPieChart) {
        userDistributionPieChart.destroy();
    }

    userDistributionPieChart = new window.Chart(canvas, {
        type: 'pie',
        data: {
            labels: ['SK Federation', 'SK Officials', 'Kabataan'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: [
                    '#ef4444',
                    '#eab308',
                    '#22c55e'
                ],
                borderColor: [
                    '#ef4444',
                    '#eab308',
                    '#22c55e'
                ],
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 17, 34, 0.96)',
                    borderColor: 'rgba(88, 130, 222, 0.7)',
                    borderWidth: 1,
                    titleColor: '#e8f1ff',
                    bodyColor: '#d8e6ff',
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                },
            },
        },
    });
}

window.updatePieChartData = function (showFederation, showOfficials, showKabataan) {
    if (!userDistributionPieChart) {
        return;
    }

    const labels = [];
    const data = [];
    const colors = [];

    if (showFederation) {
        labels.push('SK Federation');
        data.push(0);
        colors.push('#ef4444');
    }

    if (showOfficials) {
        labels.push('SK Officials');
        data.push(0);
        colors.push('#eab308');
    }

    if (showKabataan) {
        labels.push('Kabataan');
        data.push(0);
        colors.push('#22c55e');
    }

    userDistributionPieChart.data.labels = labels;
    userDistributionPieChart.data.datasets[0].data = data;
    userDistributionPieChart.data.datasets[0].backgroundColor = colors;
    userDistributionPieChart.data.datasets[0].borderColor = colors;
    userDistributionPieChart.update();
};

function syncHealthScore(score) {
    const rounded = Math.max(0, Math.min(100, Math.round(score)));
    const tone = computeHealthTone(rounded);

    platformHealthTelemetry.score = rounded;
    platformHealthTelemetry.statusTone = tone.tone;
    platformHealthTelemetry.statusLabel = tone.label;
    platformHealthTelemetry.metrics = [
        { key: 'uptime', label: 'Uptime', value: `${(99 + rounded / 100).toFixed(2)}%` },
        { key: 'latency', label: 'Latency', value: `${Math.round(180 - rounded)} ms` },
        { key: 'jobs', label: 'Queue Success', value: `${Math.min(99.9, 92 + rounded / 12).toFixed(1)}%` },
    ];

    if (platformHealthGaugeChart) {
        platformHealthGaugeChart.data.datasets[0].data = [rounded, 100 - rounded];
        platformHealthGaugeChart.data.datasets[0].backgroundColor = [
            rounded >= 96 ? '#23cf88' : rounded >= 90 ? '#f4b429' : '#ff5669',
            'rgba(70, 95, 150, 0.2)',
        ];
        platformHealthGaugeChart.update('none');
    }
}

function initPlatformHealthCharts() {
    const gaugeCanvas = document.getElementById('platformHealthGaugeChart');
    const pulseCanvas = document.getElementById('platformHealthPulseChart');

    if (!gaugeCanvas || !pulseCanvas || !window.Chart) {
        return;
    }

    if (platformHealthGaugeChart) {
        platformHealthGaugeChart.destroy();
    }

    if (platformHealthPulseChart) {
        platformHealthPulseChart.destroy();
    }

    platformHealthGaugeChart = new window.Chart(gaugeCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Health Score', 'Remaining'],
            datasets: [{
                data: [platformHealthTelemetry.score, 100 - platformHealthTelemetry.score],
                backgroundColor: ['#23cf88', 'rgba(70, 95, 150, 0.2)'],
                borderColor: ['rgba(35, 207, 136, 0.25)', 'rgba(70, 95, 150, 0)'],
                borderWidth: 2,
                circumference: 220,
                rotation: 250,
                cutout: '75%',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: false,
                },
            },
        },
    });

    platformHealthPulseChart = new window.Chart(pulseCanvas, {
        type: 'line',
        data: {
            labels: pulseLabels,
            datasets: [{
                label: 'Health Pulse',
                data: [...pulseBaseData],
                borderColor: '#4fb3ff',
                backgroundColor: 'rgba(79, 179, 255, 0.2)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.32,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(10, 17, 34, 0.96)',
                    borderColor: 'rgba(88, 130, 222, 0.7)',
                    borderWidth: 1,
                    titleColor: '#e8f1ff',
                    bodyColor: '#d8e6ff',
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: '#9eb7df',
                        maxTicksLimit: 4,
                    },
                    grid: {
                        color: 'rgba(95, 131, 211, 0.18)',
                    },
                },
                y: {
                    min: 82,
                    max: 100,
                    ticks: {
                        color: '#9eb7df',
                    },
                    grid: {
                        color: 'rgba(95, 131, 211, 0.18)',
                    },
                },
            },
        },
    });

    syncHealthScore(platformHealthTelemetry.score);
}

function mutateHealthPulse() {
    if (!platformHealthPulseChart) {
        return;
    }

    const dataset = platformHealthPulseChart.data.datasets[0];
    const prior = dataset.data[dataset.data.length - 1] || 96;
    const jitter = (Math.random() - 0.5) * 3.5;
    const nextPoint = Math.max(84, Math.min(100, Number(prior) + jitter));

    dataset.data.push(Number(nextPoint.toFixed(1)));
    dataset.data.shift();

    const labels = platformHealthPulseChart.data.labels;
    const stamp = new Date().toLocaleTimeString([], { minute: '2-digit', second: '2-digit' });
    labels.push(stamp);
    labels.shift();

    platformHealthPulseChart.update('none');

    const average = dataset.data.reduce((sum, point) => sum + Number(point), 0) / dataset.data.length;
    syncHealthScore(average);
}

function startHealthTelemetryLoop() {
    if (healthPulseIntervalId) {
        window.clearInterval(healthPulseIntervalId);
    }

    healthPulseIntervalId = window.setInterval(() => {
        mutateHealthPulse();
    }, 2500);
}

window.skRefreshPlatformHealth = function skRefreshPlatformHealth() {
    mutateHealthPulse();
};

function bootDashboardWidgets() {
    initAuditDataTable();
    initUsersActiveChart();
    initUserDistributionPieChart();
    initPlatformHealthCharts();
    startHealthTelemetryLoop();

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
