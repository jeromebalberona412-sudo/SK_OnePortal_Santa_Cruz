let userDistributionPieChart;

window.pieChartFilter = function pieChartFilter(distribution) {
    const defaultDistribution = distribution || { federation: 0, officials: 0, kabataan: 0 };
    
    return {
        showFederation: true,
        showOfficials: true,
        showKabataan: true,
        counts: {
            federation: defaultDistribution.federation,
            officials: defaultDistribution.officials,
            kabataan: defaultDistribution.kabataan
        },

        formatCount(count) {
            return count.toLocaleString();
        },

        updateChart() {
            if (typeof window.updatePieChartData === 'function') {
                window.updatePieChartData(this.showFederation, this.showOfficials, this.showKabataan);
            }
        }
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';
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

function canBootPieChart() {
    return typeof window.Chart !== 'undefined';
}

function tryBootPieChart() {
    if (!document.getElementById('userDistributionPieChart') || !canBootPieChart()) {
        return;
    }

    initUserDistributionPieChart();
}

window.addEventListener('sk:frontend-deps-ready', tryBootPieChart);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootPieChart);
} else {
    tryBootPieChart();
}
