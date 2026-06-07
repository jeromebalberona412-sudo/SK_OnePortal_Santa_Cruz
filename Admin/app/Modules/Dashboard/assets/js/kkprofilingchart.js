const KK_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const KK_BASE_APPROVED = [8, 12, 14, 11, 15, 18, 16, 14, 13, 12, 10, 13];
const KK_BASE_REJECTED = [2, 3, 2, 4, 3, 2, 3, 2, 2, 1, 2, 2];

let kkProfilingMonthlyChart = null;
let kkProfilingCurrentData = null;
let kkProfilingFiltersWired = false;
let kkProfilingBarangayWired = false;

function getMonthlyPending(approved, rejected) {
    return approved.map((value, index) => {
        return Math.max(0, Math.round((value + rejected[index]) * 0.18));
    });
}

function buildSingleBarangaySample(barangayId) {
    const seed = Number.parseInt(String(barangayId), 10) || 1;
    const scale = 0.35 + ((seed * 7) % 10) / 10;
    const offset = seed % 5;

    return {
        monthlyApproved: KK_BASE_APPROVED.map((value, index) => {
            const bump = index % 3 === offset % 3 ? 1 : 0;
            return Math.max(0, Math.round(value * scale) + bump);
        }),
        monthlyRejected: KK_BASE_REJECTED.map((value, index) => {
            const bump = index % 4 === offset % 4 ? 1 : 0;
            return Math.max(0, Math.round(value * scale * 0.8) + bump);
        }),
    };
}

function buildKkProfilingData(barangayId) {
    if (barangayId === 'all') {
        const barangays = window.__KK_BARANGAYS__ || [];

        if (barangays.length === 0) {
            return {
                monthlyApproved: [...KK_BASE_APPROVED],
                monthlyRejected: [...KK_BASE_REJECTED],
            };
        }

        const monthlyApproved = Array(12).fill(0);
        const monthlyRejected = Array(12).fill(0);

        barangays.forEach((barangay) => {
            const sample = buildSingleBarangaySample(barangay.id);
            sample.monthlyApproved.forEach((value, index) => {
                monthlyApproved[index] += value;
            });
            sample.monthlyRejected.forEach((value, index) => {
                monthlyRejected[index] += value;
            });
        });

        return { monthlyApproved, monthlyRejected };
    }

    return buildSingleBarangaySample(barangayId);
}

function getSelectedBarangayName(barangayId) {
    if (barangayId === 'all') {
        return 'All Barangays';
    }

    const barangays = window.__KK_BARANGAYS__ || [];
    const match = barangays.find((barangay) => String(barangay.id) === String(barangayId));

    return match?.name || 'Barangay';
}

function applyKkProfilingChartData(rawData, barangayName) {
    if (!kkProfilingMonthlyChart) {
        return;
    }

    const monthlyPending = getMonthlyPending(rawData.monthlyApproved, rawData.monthlyRejected);

    kkProfilingCurrentData = {
        barangayName,
        monthlyApproved: rawData.monthlyApproved,
        monthlyRejected: rawData.monthlyRejected,
        monthlyPending,
    };

    kkProfilingMonthlyChart.data.datasets[0].data = rawData.monthlyApproved;
    kkProfilingMonthlyChart.data.datasets[1].data = rawData.monthlyRejected;
    kkProfilingMonthlyChart.data.datasets[2].data = monthlyPending;
    kkProfilingMonthlyChart.update();
}

function refreshKkProfilingChartForBarangay(barangayId) {
    const rawData = buildKkProfilingData(barangayId);
    applyKkProfilingChartData(rawData, getSelectedBarangayName(barangayId));
}

function initKkProfilingMonthlyChart() {
    const canvas = document.getElementById('kkProfilingMonthlyChart');
    if (!canvas || !window.Chart) {
        return;
    }

    if (kkProfilingMonthlyChart) {
        kkProfilingMonthlyChart.destroy();
    }

    const barangayFilter = document.getElementById('kkProfilingBarangayFilter');
    const selectedBarangay = barangayFilter?.value || 'all';
    const initialData = buildKkProfilingData(selectedBarangay);
    const monthlyPending = getMonthlyPending(initialData.monthlyApproved, initialData.monthlyRejected);

    kkProfilingMonthlyChart = new window.Chart(canvas, {
        type: 'line',
        data: {
            labels: KK_MONTHS,
            datasets: [
                {
                    label: 'Approved',
                    data: initialData.monthlyApproved,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#22c55e',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Rejected',
                    data: initialData.monthlyRejected,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    tension: 0.4,
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
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#6b7280' },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { color: '#6b7280' },
                },
            },
        },
    });

    kkProfilingCurrentData = {
        barangayName: getSelectedBarangayName(selectedBarangay),
        monthlyApproved: initialData.monthlyApproved,
        monthlyRejected: initialData.monthlyRejected,
        monthlyPending,
    };

    wireKkProfilingFilters();
    wireKkProfilingBarangayFilter();
    wireKkProfilingExport();
}

function wireKkProfilingBarangayFilter() {
    if (kkProfilingBarangayWired) {
        return;
    }

    const barangayFilter = document.getElementById('kkProfilingBarangayFilter');
    if (!barangayFilter) {
        return;
    }

    barangayFilter.addEventListener('change', () => {
        refreshKkProfilingChartForBarangay(barangayFilter.value || 'all');
    });

    kkProfilingBarangayWired = true;
}

function wireKkProfilingExport() {
    const exportBtn = document.getElementById('kkProfilingExportBtn');
    if (!exportBtn || exportBtn.dataset.wired === 'true') {
        return;
    }

    exportBtn.addEventListener('click', () => {
        window.exportKkProfilingChart();
    });
    exportBtn.dataset.wired = 'true';
}

function wireKkProfilingFilters() {
    if (kkProfilingFiltersWired) {
        return;
    }

    const cbApproved = document.getElementById('filterKkApproved');
    const cbRejected = document.getElementById('filterKkRejected');
    const cbPending = document.getElementById('filterKkPending');

    function applyDatasetFilter() {
        if (!kkProfilingMonthlyChart) {
            return;
        }

        if (cbApproved) {
            kkProfilingMonthlyChart.data.datasets[0].hidden = !cbApproved.checked;
        }
        if (cbRejected) {
            kkProfilingMonthlyChart.data.datasets[1].hidden = !cbRejected.checked;
        }
        if (cbPending) {
            kkProfilingMonthlyChart.data.datasets[2].hidden = !cbPending.checked;
        }

        kkProfilingMonthlyChart.update();
    }

    [cbApproved, cbRejected, cbPending].forEach((checkbox) => {
        if (!checkbox) {
            return;
        }

        checkbox.checked = true;
        checkbox.addEventListener('change', applyDatasetFilter);
    });

    kkProfilingFiltersWired = true;
}

window.exportKkProfilingChart = function exportKkProfilingChart() {
    if (!kkProfilingCurrentData) {
        return;
    }

    const { barangayName, monthlyApproved, monthlyRejected, monthlyPending } = kkProfilingCurrentData;
    const headers = ['Month', 'Barangay', 'Approved', 'Pending', 'Rejected'];
    let csvContent = `${headers.join(',')}\n`;

    KK_MONTHS.forEach((month, index) => {
        csvContent += [
            month,
            `"${barangayName}"`,
            monthlyApproved[index],
            monthlyPending[index],
            monthlyRejected[index],
        ].join(',');
        csvContent += '\n';
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const slug = barangayName.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', `kk_profiling_${slug || 'data'}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

function canBootKkProfilingChart() {
    return typeof window.Chart !== 'undefined';
}

function tryBootKkProfilingChart() {
    if (!document.getElementById('kkProfilingMonthlyChart') || !canBootKkProfilingChart()) {
        return;
    }

    initKkProfilingMonthlyChart();
}

window.addEventListener('sk:frontend-deps-ready', tryBootKkProfilingChart);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryBootKkProfilingChart);
} else {
    tryBootKkProfilingChart();
}
