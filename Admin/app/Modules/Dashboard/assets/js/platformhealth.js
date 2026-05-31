let platformHealthGaugeChart;
let platformHealthPulseChart;
let healthPulseIntervalId;

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

window.platformHealthTelemetry = platformHealthTelemetry;

function computeHealthTone(score) {
    if (score >= 96) {
        return { tone: 'healthy', label: 'Live: Healthy' };
    }

    if (score >= 90) {
        return { tone: 'warning', label: 'Live: Degraded' };
    }

    return { tone: 'critical', label: 'Live: Critical' };
}

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

function canBootPlatformHealth() {
    return typeof window.Chart !== 'undefined';
}

function tryBootPlatformHealth() {
    if (!document.getElementById('platformHealthGaugeChart') || !canBootPlatformHealth()) {
        return;
    }

    initPlatformHealthCharts();
    startHealthTelemetryLoop();
}

window.addEventListener('sk:frontend-deps-ready', tryBootPlatformHealth);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootPlatformHealth);
} else {
    tryBootPlatformHealth();
}
