let userDistributionPieChart;

window.pieChartFilter = function pieChartFilter(distribution) {
    const defaultDistribution = distribution || { federation: 0, officials: 0 };

    return {
        showFederation: true,
        showOfficials: true,
        counts: {
            federation: defaultDistribution.federation ?? 0,
            officials: defaultDistribution.officials ?? 0,
        },

        init() {
            this.$nextTick(() => this.updateChart());
        },

        formatCount(count) {
            return Number(count ?? 0).toLocaleString();
        },

        updateChart() {
            if (typeof window.updatePieChartData === 'function') {
                window.updatePieChartData(this.showFederation, this.showOfficials, this.counts);
            }
        },
    };
};

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
            labels: ['SK Federation', 'SK Officials'],
            datasets: [{
                data: [0, 0],
                backgroundColor: ['#ef4444', '#eab308'],
                borderColor: ['#ef4444', '#eab308'],
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        },
                    },
                },
            },
        },
    });
}

window.updatePieChartData = function (showFederation, showOfficials, counts = {}) {
    if (!userDistributionPieChart) {
        return;
    }

    const labels = [];
    const data = [];
    const colors = [];

    if (showFederation) {
        labels.push('SK Federation');
        data.push(Number(counts.federation ?? 0));
        colors.push('#ef4444');
    }

    if (showOfficials) {
        labels.push('SK Officials');
        data.push(Number(counts.officials ?? 0));
        colors.push('#eab308');
    }

    userDistributionPieChart.data.labels = labels;
    userDistributionPieChart.data.datasets[0].data = data;
    userDistributionPieChart.data.datasets[0].backgroundColor = colors;
    userDistributionPieChart.data.datasets[0].borderColor = colors;
    userDistributionPieChart.update();
};

function canBootPieChart() {
    return typeof window.Chart !== 'undefined';
}

function tryBootPieChart() {
    if (!document.getElementById('userDistributionPieChart') || !canBootPieChart()) {
        return;
    }

    initUserDistributionPieChart();

    if (window.__USER_DISTRIBUTION__) {
        window.updatePieChartData(true, true, window.__USER_DISTRIBUTION__);
    }
}

window.addEventListener('sk:frontend-deps-ready', tryBootPieChart);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootPieChart);
} else {
    tryBootPieChart();
}
