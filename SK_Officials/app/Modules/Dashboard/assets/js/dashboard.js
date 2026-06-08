/**
 * SK Officials Dashboard — live data from /api/dashboard/stats
 */

import Chart from 'chart.js/auto';

let dashboardData = null;
let selectedYear = new Date().getFullYear();
let kkChartGranularity = 'monthly';
let kkChartMonth = new Date().getMonth() + 1;

let chartBar = null;
let chartLine = null;
let chartPie = null;
let chartDonut = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function apiFetch(url, options = {}) {
    const { headers: extraHeaders, body, ...rest } = options;
    const headers = {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json',
        ...extraHeaders,
    };

    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, {
        ...rest,
        headers,
        body,
        credentials: 'same-origin',
    });

    if (!response.ok) {
        const payload = await response.json().catch(() => ({}));
        throw new Error(payload.message || 'Request failed.');
    }

    return response.json();
}

function applyChartDefaults() {
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1f2937';
    Chart.defaults.plugins.tooltip.titleColor = '#f9fafb';
    Chart.defaults.plugins.tooltip.bodyColor = '#d1d5db';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
}

function formatCurrency(value) {
    const amount = Number(value || 0);
    return '₱' + amount.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

document.addEventListener('DOMContentLoaded', function () {
    applyChartDefaults();
    initYearFilter();
    initKkChartFilters();
    initModals();
    initStatCardLinks();
    loadDashboard();
    window.setInterval(refreshLiveSections, 45000);
});

function initYearFilter() {
    const sel = document.getElementById('yearSelect');
    if (!sel) return;

    sel.addEventListener('change', function () {
        selectedYear = Number(sel.value);
        loadDashboard();
    });
}

function initStatCardLinks() {
    document.querySelectorAll('.stat-card[data-href]').forEach(function (card) {
        card.setAttribute('role', 'link');
        card.setAttribute('tabindex', '0');
        card.addEventListener('click', function () {
            const href = card.getAttribute('data-href');
            if (href) window.location.href = href;
        });
        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const href = card.getAttribute('data-href');
                if (href) window.location.href = href;
            }
        });
    });
}

function initKkChartFilters() {
    const granularitySel = document.getElementById('kkChartGranularity');
    const monthSel = document.getElementById('kkChartMonth');
    const monthWrap = document.getElementById('kkChartMonthWrap');

    if (granularitySel) {
        granularitySel.addEventListener('change', function () {
            kkChartGranularity = granularitySel.value;
            if (monthWrap) {
                monthWrap.hidden = kkChartGranularity !== 'weekly';
            }
            loadDashboard();
        });
    }

    if (monthSel) {
        monthSel.value = String(kkChartMonth);
        monthSel.addEventListener('change', function () {
            kkChartMonth = Number(monthSel.value);
            loadDashboard();
        });
    }
}

function buildDashboardParams() {
    const params = new URLSearchParams({
        year: String(selectedYear),
        granularity: kkChartGranularity,
    });

    if (kkChartGranularity === 'weekly') {
        params.set('month', String(kkChartMonth));
    }

    return params;
}

async function loadDashboard() {
    const params = buildDashboardParams().toString();

    try {
        const summaryResponse = await apiFetch('/api/dashboard/stats?' + params + '&summary=1');
        dashboardData = summaryResponse.data || null;
        if (!dashboardData) return;

        populateYearOptions(dashboardData.available_years || [selectedYear]);
        renderSummary(dashboardData);

        apiFetch('/api/dashboard/stats?' + params + '&charts=1')
            .then(function (chartsResponse) {
                const chartData = chartsResponse.data;
                if (!chartData) return;
                dashboardData = Object.assign({}, dashboardData, chartData);
                renderCharts(chartData);
            })
            .catch(function (error) {
                console.error('Dashboard charts load failed:', error);
            });
    } catch (error) {
        console.error('Dashboard load failed:', error);
        renderEmptyState();
    }
}

async function refreshLiveSections() {
    if (!document.getElementById('committeesList')) {
        return;
    }

    try {
        const response = await apiFetch('/api/dashboard/stats?' + buildDashboardParams().toString() + '&summary=1');
        const data = response.data;
        if (!data) return;

        renderActivity(data.recent_activity || []);
        renderCommittees(data.officials || []);
    } catch (_) {
        // Keep current view on transient failures.
    }
}

function populateYearOptions(years) {
    const sel = document.getElementById('yearSelect');
    if (!sel) return;

    const uniqueYears = Array.from(new Set(years.map(Number))).sort((a, b) => b - a);
    sel.innerHTML = uniqueYears.map(function (year) {
        return '<option value="' + year + '">' + year + '</option>';
    }).join('');

    if (!uniqueYears.includes(selectedYear)) {
        selectedYear = uniqueYears[0] || new Date().getFullYear();
    }

    sel.value = String(selectedYear);
}

function renderSummary(data) {
    const userNameEl = document.getElementById('dashUserName');
    if (userNameEl && data.user_name) {
        const firstName = String(data.user_name).split(' ')[0];
        userNameEl.textContent = firstName || 'SK Official';
    }

    renderStats(data.stats || {});
    renderActivity(data.recent_activity || []);
    renderEvents(data.upcoming_events || []);
    renderReminder(data.today_reminder);
    renderCommittees(data.officials || []);
}

function renderCharts(data) {
    renderBarChart(data);
    renderLineChart(data.kk_requests_chart || {});
    renderPieChart(data.gender_distribution || { labels: ['Male', 'Female'], values: [0, 0] });
    renderDonutChart(data.budget_programs || { labels: [], values: [] });
}

function renderAll(data) {
    renderSummary(data);
    renderCharts(data);
}

function renderEmptyState() {
    renderStats({});
    renderActivity([]);
    renderEvents([]);
    renderReminder(null);
    renderCommittees([]);
    renderBarChart({ purok_labels: [], purok_counts: [] });
    renderLineChart({ labels: [], approved: [], pending: [], rejected: [] });
    renderPieChart({ labels: ['Male', 'Female'], values: [0, 0] });
    renderDonutChart({ labels: [], values: [] });
}

function renderStats(stats) {
    setCount('statKabataan', stats.total_kk || 0);
    setCount('statPending', stats.pending || 0);
    setCount('statApproved', stats.approved || 0);
    setCount('statRejected', stats.rejected || 0);
    setCount('statActivePrograms', stats.active_programs || 0);
    setCount('statDeletedKabataan', stats.deleted_kabataan || 0);
    setCount('statRejectedItems', stats.rejected_items || 0);

    setText('statBudget', formatCurrency(stats.budget || 0));
    setText('statExpenses', formatCurrency(stats.expenses || 0));
    setText('statRemaining', formatCurrency(stats.remaining || 0));
}

function setCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = Number(target || 0).toLocaleString();
}

function renderActivity(items) {
    const list = document.getElementById('activityList');
    if (!list) return;

    if (!items.length) {
        list.innerHTML = '<p class="dash-empty-msg">No recent activity recorded.</p>';
        return;
    }

    list.innerHTML = items.map(function (item) {
        const whoLine = item.position
            ? esc(item.who) + ' · ' + esc(item.position)
            : esc(item.who);

        return '<div class="activity-item activity-item-no-icon">' +
            '<div class="activity-body">' +
            '<strong>' + esc(item.text) + '</strong>' +
            '<span>' + whoLine + '</span>' +
            '</div>' +
            '<div class="activity-time">' + esc(item.time) + '</div>' +
            '</div>';
    }).join('');
}

function renderEvents(events) {
    const list = document.getElementById('eventsList');
    if (!list) return;

    if (!events.length) {
        list.innerHTML = '<p class="dash-empty-msg">No upcoming calendar notes.</p>';
        return;
    }

    list.innerHTML = events.map(function (event) {
        return '<div class="event-item">' +
            '<div class="event-date-box">' +
            '<span class="event-date-day">' + esc(event.day) + '</span>' +
            '<span class="event-date-mon">' + esc(event.month_label) + '</span>' +
            '</div>' +
            '<div class="event-body">' +
            '<div class="event-title">' + esc(event.title) + '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

function renderReminder(reminder) {
    const banner = document.getElementById('calendarReminderBanner');
    const textEl = document.getElementById('reminderText');
    if (!banner || !textEl) return;

    if (!reminder || !reminder.title) {
        banner.classList.add('d-none');
        textEl.textContent = '';
        return;
    }

    const dateLabel = reminder.date_label ? reminder.date_label + ' — ' : '';
    textEl.textContent = dateLabel + reminder.title;
    banner.classList.remove('d-none');
}

function renderCommittees(officials) {
    const container = document.getElementById('committeesList');
    if (!container) return;

    if (!officials.length) {
        container.innerHTML = '<div class="col-12"><p class="dash-empty-msg mb-0 text-center py-2">No SK officials found for this barangay.</p></div>';
        return;
    }

    const sorted = officials.slice().sort(function (a, b) {
        if (a.status === 'Online' && b.status === 'Offline') return -1;
        if (a.status === 'Offline' && b.status === 'Online') return 1;
        return 0;
    });

    container.innerHTML = sorted.map(function (official) {
        const badgeClass = official.status === 'Online'
            ? 'committee-card-badge-online'
            : 'committee-card-badge-offline';

        return '<div class="col-12 col-sm-6 col-lg-4">' +
            '<div class="committee-card committee-card-text-only">' +
            '<div class="committee-card-body">' +
            '<div class="committee-card-name">' + esc(official.name) + '</div>' +
            '<div class="committee-card-meta">Position: ' + esc(official.position) + '</div>' +
            '</div>' +
            '<div class="committee-card-actions">' +
            '<span class="committee-card-badge ' + badgeClass + '">' + esc(official.status) + '</span>' +
            '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

function renderBarChart(data) {
    const ctx = document.getElementById('chartKabataanBarangay');
    if (!ctx) return;
    if (chartBar) {
        chartBar.destroy();
        chartBar = null;
    }

    chartBar = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.purok_labels || [],
            datasets: [{
                label: 'Kabataan',
                data: data.purok_counts || [],
                backgroundColor: [
                    'rgba(59,130,246,.85)',
                    'rgba(34,197,94,.85)',
                    'rgba(244,194,13,.85)',
                    'rgba(239,68,68,.85)',
                    'rgba(168,85,247,.85)',
                    'rgba(20,184,166,.85)',
                    'rgba(249,115,22,.85)',
                    'rgba(99,102,241,.85)',
                ],
                borderRadius: 8,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return ' ' + context.parsed.y + ' youth';
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 35,
                        minRotation: 25,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,.05)' },
                    ticks: { stepSize: 1 },
                },
            },
        },
    });
}

function renderLineChart(chartData) {
    const ctx = document.getElementById('chartMonthlyRequests');
    if (!ctx) return;
    if (chartLine) {
        chartLine.destroy();
        chartLine = null;
    }

    chartLine = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels || [],
            datasets: [
                {
                    label: 'Approved',
                    data: chartData.approved || [],
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,.1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#22c55e',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Rejected',
                    data: chartData.rejected || [],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pending',
                    data: chartData.pending || [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.08)',
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
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 1 } },
            },
        },
    });

    wireLineChartFilters();
}

function wireLineChartFilters() {
    const cbApproved = document.getElementById('filterApproved');
    const cbRejected = document.getElementById('filterRejected');
    const cbPending = document.getElementById('filterPending');

    function applyFilter() {
        if (!chartLine) return;
        if (cbApproved) chartLine.data.datasets[0].hidden = !cbApproved.checked;
        if (cbRejected) chartLine.data.datasets[1].hidden = !cbRejected.checked;
        if (cbPending) chartLine.data.datasets[2].hidden = !cbPending.checked;
        chartLine.update();
    }

    [cbApproved, cbRejected, cbPending].forEach(function (checkbox) {
        if (!checkbox) return;
        checkbox.onchange = applyFilter;
    });

    applyFilter();
}

function renderPieChart(genderDist) {
    const ctx = document.getElementById('chartGenderPie');
    if (!ctx) return;
    if (chartPie) {
        chartPie.destroy();
        chartPie = null;
    }

    const colors = ['#3b82f6', '#ec4899'];
    const total = (genderDist.values || []).reduce(function (sum, value) {
        return sum + Number(value || 0);
    }, 0);

    const pieLabelsPlugin = {
        id: 'pieLabels',
        afterDraw: function (chart) {
            const context = chart.ctx;
            chart.data.datasets.forEach(function (dataset, datasetIndex) {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach(function (arc, index) {
                    const value = dataset.data[index];
                    if (!value) return;
                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                    const label = chart.data.labels[index];
                    const angle = (arc.startAngle + arc.endAngle) / 2;
                    const radius = (arc.innerRadius + arc.outerRadius) / 2;
                    const x = arc.x + Math.cos(angle) * radius;
                    const y = arc.y + Math.sin(angle) * radius;
                    context.save();
                    context.textAlign = 'center';
                    context.textBaseline = 'middle';
                    context.fillStyle = '#fff';
                    context.font = 'bold 11px Segoe UI, sans-serif';
                    context.fillText(label, x, y - 7);
                    context.font = 'bold 12px Segoe UI, sans-serif';
                    context.fillText(pct + '%', x, y + 7);
                    context.restore();
                });
            });
        },
    };

    chartPie = new Chart(ctx, {
        type: 'pie',
        plugins: [pieLabelsPlugin],
        data: {
            labels: genderDist.labels || ['Male', 'Female'],
            datasets: [{
                data: genderDist.values || [0, 0],
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const sum = context.dataset.data.reduce(function (a, b) {
                                return a + Number(b || 0);
                            }, 0);
                            const pct = sum > 0 ? ((context.parsed / sum) * 100).toFixed(1) : '0.0';
                            return ' ' + context.label + ': ' + context.parsed + ' (' + pct + '%)';
                        },
                    },
                },
            },
        },
    });

    const legend = document.getElementById('genderLegend');
    if (legend) {
        legend.innerHTML = (genderDist.labels || []).map(function (label, index) {
            return '<div class="pie-legend-item">' +
                '<span class="pie-legend-box" style="background:' + colors[index] + ';"></span>' +
                '<span class="pie-legend-label">' + esc(label) + '</span>' +
                '</div>';
        }).join('');
    }
}

function renderDonutChart(budgetPrograms) {
    const ctx = document.getElementById('chartBudgetDonut');
    if (!ctx) return;
    if (chartDonut) {
        chartDonut.destroy();
        chartDonut = null;
    }

    const colors = ['#3b82f6', '#22c55e', '#ef4444', '#14b8a6', '#f97316', '#a855f7', '#ec4899', '#6366f1', '#0ea5e9', '#84cc16'];
    const labels = budgetPrograms.labels || [];
    const values = budgetPrograms.values || [];
    const total = values.reduce(function (sum, value) {
        return sum + Number(value || 0);
    }, 0);

    const donutLabelsPlugin = {
        id: 'donutLabels',
        afterDraw: function (chart) {
            const context = chart.ctx;
            chart.data.datasets.forEach(function (dataset, datasetIndex) {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach(function (arc, index) {
                    const value = dataset.data[index];
                    if (!value) return;
                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                    if (pct < 5) return;
                    const angle = (arc.startAngle + arc.endAngle) / 2;
                    const radius = (arc.innerRadius + arc.outerRadius) / 2;
                    const x = arc.x + Math.cos(angle) * radius;
                    const y = arc.y + Math.sin(angle) * radius;
                    context.save();
                    context.textAlign = 'center';
                    context.textBaseline = 'middle';
                    context.fillStyle = '#fff';
                    context.font = 'bold 11px Segoe UI, sans-serif';
                    context.fillText(pct + '%', x, y);
                    context.restore();
                });
            });
        },
    };

    chartDonut = new Chart(ctx, {
        type: 'doughnut',
        plugins: [donutLabelsPlugin],
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                            return ' ₱' + Number(context.parsed).toLocaleString() + ' (' + pct + '%)';
                        },
                    },
                },
            },
        },
    });

    const legend = document.getElementById('donutLegend');
    if (!legend) return;

    if (!labels.length) {
        legend.innerHTML = '<p class="dash-empty-msg mb-0">No ABYIP program budget data yet.</p>';
        return;
    }

    legend.innerHTML = labels.map(function (label, index) {
        return '<div class="donut-legend-item">' +
            '<div class="donut-legend-box" style="background:' + colors[index % colors.length] + ';"></div>' +
            '<span class="donut-legend-label">' + esc(label) + '</span>' +
            '</div>';
    }).join('');
}

function initModals() {
    document.querySelectorAll('[data-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal(button.getAttribute('data-close'));
        });
    });

    document.querySelectorAll('.dash-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                closeModal(backdrop.id);
            }
        });
    });
}

function closeModal(id) {
    const element = document.getElementById(id);
    if (element) {
        element.classList.remove('open');
    }
}

function esc(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}
