let usersActiveChart;

const usersActiveLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
const activeFederationSeries = [184, 201, 218, 242, 266, 281];
const activeOfficialSeries = [622, 648, 671, 698, 724, 748];
const activeKabataanSeries = [312, 338, 360, 385, 409, 428];

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

function canBootTrendChart() {
    return typeof window.Chart !== 'undefined';
}

function tryBootTrendChart() {
    if (!document.getElementById('usersActiveChart') || !canBootTrendChart()) {
        return;
    }

    initUsersActiveChart();
}

window.addEventListener('sk:frontend-deps-ready', tryBootTrendChart);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootTrendChart);
} else {
    tryBootTrendChart();
}
