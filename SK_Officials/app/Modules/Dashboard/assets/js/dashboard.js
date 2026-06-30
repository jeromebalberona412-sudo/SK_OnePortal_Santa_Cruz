/**
 * SK Officials Dashboard — live data from /api/dashboard/stats
 */

import Chart from 'chart.js/auto';

let dashboardData = null;
let selectedYear = new Date().getFullYear();
let selectedTermId = '';
let availableTerms = [];
let kkChartGranularity = 'monthly';
let kkChartMonth = new Date().getMonth() + 1;
let kkChartZone = 'all';
let genderChartFilter = 'all';
let employmentChartFilter = 'all';
let lastGenderDistribution = { labels: ['Male', 'Female'], values: [0, 0] };
let lastEmploymentDistribution = { items: [], total: 0 };

let chartBar = null;
let chartLine = null;
let chartPie = null;
let chartEmployment = null;
let chartsLoading = false;

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

document.addEventListener('DOMContentLoaded', function () {
    applyChartDefaults();
    initKkChartFilters();
    initGenderEmploymentFilters();
    initModals();
    initStatCardLinks();
    listenKkProfileEvents(function () {
        refreshDashboard();
    });

    loadDashboard();
    window.setInterval(refreshDashboard, 45000);
});

function refreshDashboard() {
    loadDashboard({ silent: true });
}

function listenKkProfileEvents(handler) {
    window.addEventListener('storage', function (event) {
        if (event.key !== 'kk-profile-event' || !event.newValue) return;
        handler();
    });
    window.addEventListener('kk-profile-event', function () {
        handler();
    });
}

function initTermFilter() {
    const sel = document.getElementById('termSelect');
    if (!sel) return;

    sel.addEventListener('change', function () {
        selectedTermId = sel.value;
        const term = availableTerms.find(function (item) { return item.id === selectedTermId; });
        if (term) {
            populateYearOptions(buildYearsForTerm(term), selectedYear);
        }
        loadDashboard();
    });
}

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
    const zoneSel = document.getElementById('kkChartZone');

    if (zoneSel) {
        zoneSel.addEventListener('change', function () {
            kkChartZone = zoneSel.value || 'all';
            loadDashboard({ chartsOnly: true });
        });
    }

    if (granularitySel) {
        granularitySel.addEventListener('change', function () {
            kkChartGranularity = granularitySel.value;
            if (monthWrap) {
                monthWrap.hidden = kkChartGranularity !== 'weekly';
            }
            updateKkChartSubtitle();
            loadDashboard({ chartsOnly: true });
        });
    }

    if (monthSel) {
        monthSel.value = String(kkChartMonth);
        monthSel.addEventListener('change', function () {
            kkChartMonth = Number(monthSel.value);
            updateKkChartSubtitle();
            loadDashboard({ chartsOnly: true });
        });
    }

    updateKkChartSubtitle();
}

function initGenderEmploymentFilters() {
    const genderSel = document.getElementById('genderChartFilter');
    const employmentSel = document.getElementById('employmentChartFilter');

    if (genderSel) {
        genderSel.addEventListener('change', function () {
            genderChartFilter = genderSel.value || 'all';
            renderPieChart(lastGenderDistribution);
        });
    }

    if (employmentSel) {
        employmentSel.addEventListener('change', function () {
            employmentChartFilter = employmentSel.value || 'all';
            renderEmploymentChart(lastEmploymentDistribution);
        });
    }
}

function updateKkChartSubtitle(chartData) {
    const subtitle = document.getElementById('kkChartSubtitle');
    if (!subtitle) return;

    if (kkChartGranularity === 'weekly') {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthLabel = monthNames[(kkChartMonth || 1) - 1] || 'Selected month';
        subtitle.textContent = 'Weekly submissions for ' + monthLabel + ' — approved, pending, and rejected';
        return;
    }

    subtitle.textContent = 'Approved, pending, and rejected submissions';
}

function getWeekDateRange(month) {
    const now = new Date();
    const year = now.getFullYear();
    
    // Get the current date
    const currentDate = new Date();
    
    // Get the week number (ISO week number)
    const weekNum = getISOWeek(currentDate);
    
    // Get the start (Monday) and end (Sunday) of the current week
    const dayOfWeek = currentDate.getDay();
    const startOfWeek = new Date(currentDate);
    startOfWeek.setDate(currentDate.getDate() - dayOfWeek + 1); // Monday
    
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6); // Sunday
    
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    return 'W' + weekNum + ' (' + monthNames[startOfWeek.getMonth()] + ' ' + startOfWeek.getDate() + ' - ' + 
           monthNames[endOfWeek.getMonth()] + ' ' + endOfWeek.getDate() + ', ' + year + ')';
}

function getISOWeek(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function populateZoneOptions(zones) {
    const zoneSel = document.getElementById('kkChartZone');
    if (!zoneSel) return;

    const current = kkChartZone || 'all';
    const options = ['<option value="all">All Zones</option>']
        .concat((zones || []).map(function (zone) {
            return '<option value="' + esc(zone) + '">' + esc(zone) + '</option>';
        }));

    zoneSel.innerHTML = options.join('');
    zoneSel.value = Array.from(zoneSel.options).some(function (opt) { return opt.value === current; })
        ? current
        : 'all';
    kkChartZone = zoneSel.value;
}

function buildDashboardParams() {
    const params = new URLSearchParams({
        year: String(selectedYear),
        granularity: kkChartGranularity,
    });

    if (selectedTermId) {
        params.set('term_id', selectedTermId);
    }

    if (kkChartGranularity === 'weekly') {
        params.set('month', String(kkChartMonth));
    }

    if (kkChartZone && kkChartZone !== 'all') {
        params.set('zone', kkChartZone);
    }

    return params;
}

function buildYearsForTerm(term) {
    const years = [];
    for (let year = term.end_year; year >= term.start_year; year--) {
        years.push(year);
    }
    return years.length ? years : [new Date().getFullYear()];
}

async function loadDashboard(options = {}) {
    const silent = options.silent === true;
    const chartsOnly = options.chartsOnly === true;
    const params = buildDashboardParams().toString();
    const query = chartsOnly
        ? params + '&charts=1'
        : params;

    if (chartsOnly) {
        if (chartsLoading) return;
        chartsLoading = true;
        setEmploymentChartLoading(true);
    }

    try {
        const response = await apiFetch('/api/dashboard/stats?' + query);
        const data = response.data || null;
        if (!data) return;

        if (chartsOnly) {
            dashboardData = Object.assign({}, dashboardData || {}, data);
            populateZoneOptions(data.zone_options || []);
            renderCharts(data);
            return;
        }

        dashboardData = data;

        if (dashboardData.term_id) {
            selectedTermId = dashboardData.term_id;
        }
        if (dashboardData.year) {
            selectedYear = Number(dashboardData.year);
        }

        populateTermOptions(dashboardData.available_terms || []);
        populateYearOptions(dashboardData.available_years || [selectedYear], selectedYear);
        populateZoneOptions(dashboardData.zone_options || []);
        renderAll(dashboardData);
    } catch (error) {
        if (!silent) {
            console.error('Dashboard load failed:', error);
            renderEmptyState();
        }
    } finally {
        if (chartsOnly) {
            chartsLoading = false;
        }
    }
}

async function loadDashboardCharts() {
    return loadDashboard({ chartsOnly: true });
}

function populateTermOptions(terms) {
    const sel = document.getElementById('termSelect');
    if (!sel) return;

    availableTerms = Array.isArray(terms) ? terms.slice() : [];
    sel.innerHTML = availableTerms.map(function (term) {
        return '<option value="' + esc(term.id) + '">' + esc(term.label) + '</option>';
    }).join('');

    if (dashboardData && dashboardData.term_id) {
        selectedTermId = dashboardData.term_id;
    } else if (!selectedTermId || !availableTerms.some(function (term) { return term.id === selectedTermId; })) {
        const activeTerm = availableTerms.find(function (term) { return term.is_active; });
        selectedTermId = activeTerm ? activeTerm.id : (availableTerms[0] ? availableTerms[0].id : '');
    }

    if (selectedTermId) {
        sel.value = selectedTermId;
    }
}

function populateYearOptions(years, preferredYear) {
    const sel = document.getElementById('yearSelect');
    if (!sel) return;

    const uniqueYears = Array.from(new Set(years.map(Number))).sort(function (a, b) { return b - a; });
    sel.innerHTML = uniqueYears.map(function (year) {
        return '<option value="' + year + '">' + year + '</option>';
    }).join('');

    const targetYear = preferredYear !== undefined ? Number(preferredYear) : selectedYear;
    if (!uniqueYears.includes(targetYear)) {
        selectedYear = uniqueYears[0] || new Date().getFullYear();
    } else {
        selectedYear = targetYear;
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
    lastGenderDistribution = data.gender_distribution || { labels: ['Male', 'Female'], values: [0, 0] };
    lastEmploymentDistribution = data.employment_status_distribution || { items: [], total: 0 };
    renderPieChart(lastGenderDistribution);
    renderEmploymentChart(lastEmploymentDistribution);
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
    renderEmploymentChart({ items: defaultEmploymentItems(), total: 0 });
}

function renderStats(stats) {
    setCount('statKabataan', stats.total_kk || 0);
    setCount('statPending', stats.pending || 0);
    setCount('statApproved', stats.approved || 0);
    setCount('statRejected', stats.rejected || 0);
    setCount('statActivePrograms', stats.active_programs || 0);
    setCount('statScholarshipsApproved', stats.scholarships?.approved || 0);
    setCount('statScholarshipsPending', stats.scholarships?.pending || 0);
    setCount('statScholarshipsRejected', stats.scholarships?.rejected || 0);
    setCount('statSportsApproved', stats.sports?.approved || 0);
    setCount('statSportsPending', stats.sports?.pending || 0);
    setCount('statSportsRejected', stats.sports?.rejected || 0);
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

    list.innerHTML = items.slice(0, 5).map(function (item) {
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

function renderActivityFull(items) {
    const list = document.getElementById('activityListFull');
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

    const xLabels = (kkChartGranularity === 'weekly' && (chartData.week_ranges?.length || chartData.labels?.length))
        ? (chartData.week_ranges || chartData.labels)
        : (chartData.labels || []);

    chartLine = new Chart(ctx, {
        type: 'line',
        data: {
            labels: xLabels,
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
            plugins: {
                legend: { display: false },
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
                    ticks: {
                        font: { size: 11 },
                        color: '#6b7280',
                        maxRotation: 45,
                        minRotation: 0,
                        autoSkip: false,
                    },
                },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 1, color: '#6b7280', precision: 0 } },
            },
        },
    });

    updateKkChartSubtitle(chartData);
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

    const sourceLabels = genderDist.labels || ['Male', 'Female'];
    const sourceValues = genderDist.values || [0, 0];
    const labels = [];
    const values = [];
    const colors = [];

    sourceLabels.forEach(function (label, index) {
        const key = String(label).toLowerCase();
        const include = genderChartFilter === 'all'
            || (genderChartFilter === 'male' && key.includes('male') && !key.includes('female'))
            || (genderChartFilter === 'female' && key.includes('female'));

        if (!include) return;

        labels.push(label);
        values.push(Number(sourceValues[index] || 0));
        colors.push(key.includes('female') ? '#ec4899' : '#3b82f6');
    });

    if (!labels.length) {
        labels.push('No data');
        values.push(0);
        colors.push('#d1d5db');
    }

    const total = values.reduce(function (sum, value) {
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
            labels: labels,
            datasets: [{
                data: values,
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
        legend.innerHTML = labels.map(function (label, index) {
            return '<div class="pie-legend-item">' +
                '<span class="pie-legend-box" style="background:' + colors[index] + ';"></span>' +
                '<span class="pie-legend-label">' + esc(label) + ' (' + values[index].toLocaleString() + ')</span>' +
                '</div>';
        }).join('');
    }
}

function setEmploymentChartLoading(loading) {
    const skeleton = document.getElementById('employmentChartSkeleton');
    const wrap = document.getElementById('employmentChartWrap');
    const empty = document.getElementById('employmentChartEmpty');
    const legend = document.getElementById('employmentLegend');

    if (skeleton) skeleton.hidden = !loading;
    if (loading) {
        if (wrap) wrap.hidden = true;
        if (empty) empty.classList.add('d-none');
        if (legend) legend.innerHTML = '';
    }
}

function defaultEmploymentItems() {
    return [
        { status: 'Employed', count: 0 },
        { status: 'Unemployed', count: 0 },
        { status: 'Self-Employed', count: 0 },
        { status: 'Currently looking for a Job', count: 0 },
        { status: 'Not Interested Looking for a Job', count: 0 },
    ];
}

function renderEmploymentChart(distribution) {
    const ctx = document.getElementById('chartEmploymentStatus');
    const wrap = document.getElementById('employmentChartWrap');
    const empty = document.getElementById('employmentChartEmpty');
    const legend = document.getElementById('employmentLegend');
    const allItems = (distribution.items && distribution.items.length)
        ? distribution.items
        : defaultEmploymentItems();
    const items = employmentChartFilter === 'all'
        ? allItems
        : allItems.filter(function (item) { return item.status === employmentChartFilter; });
    const total = items.reduce(function (sum, item) {
        return sum + Number(item.count || 0);
    }, 0);

    setEmploymentChartLoading(false);

    if (!ctx) return;

    if (chartEmployment) {
        chartEmployment.destroy();
        chartEmployment = null;
    }

    if (wrap) wrap.hidden = false;
    if (empty) empty.classList.toggle('d-none', total > 0);

    const labels = items.map(function (item) { return item.status; });
    const values = items.map(function (item) { return Number(item.count || 0); });
    const colors = ['#3b82f6', '#22c55e', '#f4c20d', '#ef4444', '#a855f7', '#14b8a6', '#f97316', '#6366f1'];

    const donutLabelsPlugin = {
        id: 'employmentDonutLabels',
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

    chartEmployment = new Chart(ctx, {
        type: 'doughnut',
        plugins: [donutLabelsPlugin],
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: labels.map(function (_label, index) {
                    return colors[index % colors.length];
                }),
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
                            return ' ' + context.label + ': ' + context.parsed + ' (' + pct + '%)';
                        },
                    },
                },
            },
        },
    });

    if (legend) {
        legend.hidden = employmentChartFilter !== 'all';
        legend.innerHTML = employmentChartFilter === 'all'
            ? labels.map(function (label, index) {
                return '<div class="donut-legend-item">' +
                    '<div class="donut-legend-box" style="background:' + colors[index % colors.length] + ';"></div>' +
                    '<span class="donut-legend-label">' + esc(label) + ' (' + values[index].toLocaleString() + ')</span>' +
                    '</div>';
            }).join('')
            : '';
    }
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

    // View All Activity button
    const viewAllActivityBtn = document.getElementById('viewAllActivity');
    if (viewAllActivityBtn) {
        viewAllActivityBtn.addEventListener('click', function (event) {
            event.preventDefault();
            openModal('modalViewAllActivity');
            renderActivityFull(dashboardData?.recent_activity || []);
        });
    }
}

function closeModal(id) {
    const element = document.getElementById(id);
    if (element) {
        element.classList.remove('open');
    }
}

function openModal(id) {
    const element = document.getElementById(id);
    if (element) {
        element.classList.add('open');
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
