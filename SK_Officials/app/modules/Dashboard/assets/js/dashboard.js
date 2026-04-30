/**
 * SK Officials Dashboard — dashboard.js
 * Chart.js imported via npm (bundled by Vite)
 * Year filter: 2023–2026 with per-year data
 */

import Chart from 'chart.js/auto';

/* ══════════════════════════════════════════════════════════
   PER-YEAR DATA STORE
   2023 = first real year; 2024–2026 = progressively more data
   Years with no data will show zeros / empty charts.
══════════════════════════════════════════════════════════ */
const YEAR_DATA = {
    2023: {
        stats: { kabataan: 0, abyip: 0, pending: 0, approved: 0, rejected: 0, programs: 0, budget: '₱0.00' },
        purokLabels:  ['Bayside','Villa Gracia','Imelda','Lupang Pangako','Damayan','Marcelo','Bigayan Villa Rosa','Bigayan San Luis','Phase 3','Maligaya'],
        purokCounts:  [22, 18, 15, 20, 14, 19, 17, 16, 21, 16],
        monthlyApproved: [4,6,5,8,7,10,9,11,10,12,8,7],
        monthlyRejected: [1,2,1,2,1,3,2,3,1,2,2,1],
        abyipStatus: { labels:['Active','Inactive','Pending','Completed'], values:[18,6,4,6] },
        genderDist: { labels:['Male','Female'], values:[82, 76] },
        budgetPrograms: { labels:['Education','Sports','Health','Environment','Livelihood','Others'], values:[140000,80000,70000,40000,50000,40000] },
        activity: [
            { type:'add',     text:'New Kabataan record added',       who:'Maria Santos',    time:'Jan 5, 2023' },
            { type:'approve', text:'KK Profiling request approved',   who:'Juan Dela Cruz',  time:'Feb 12, 2023' },
            { type:'reject',  text:'KK Profiling request rejected',   who:'Pedro Reyes',     time:'Mar 3, 2023' },
        ],
        announcements: [
            { title:'SK Orientation — Jan 10, 2023',   date:'Jan 8, 2023' },
            { title:'ABYIP Kick-off Meeting',           date:'Feb 1, 2023' },
        ],
        events: [
            { day:'10', mon:'Jan', title:'SK Orientation',         time:'9:00 AM' },
            { day:'01', mon:'Feb', title:'ABYIP Kick-off',         time:'8:00 AM' },
        ],
    },
    2024: {
        stats: { kabataan: 0, abyip: 0, pending: 0, approved: 0, rejected: 0, programs: 0, budget: '₱0.00' },
        purokLabels:  ['Bayside','Villa Gracia','Imelda','Lupang Pangako','Damayan','Marcelo','Bigayan Villa Rosa','Bigayan San Luis','Phase 3','Maligaya'],
        purokCounts:  [30, 25, 22, 28, 20, 27, 24, 23, 29, 19],
        monthlyApproved: [8,10,9,13,11,16,14,18,15,20,17,12],
        monthlyRejected: [2,3,2,3,2,4,3,5,2,4,3,2],
        abyipStatus: { labels:['Active','Inactive','Pending','Completed'], values:[30,9,6,13] },
        genderDist: { labels:['Male','Female'], values:[118, 109] },
        budgetPrograms: { labels:['Education','Sports','Health','Environment','Livelihood','Others'], values:[220000,120000,100000,65000,80000,60000] },
        activity: [
            { type:'approve', text:'KK Profiling request approved',  who:'Ana Lim',         time:'Jan 15, 2024' },
            { type:'add',     text:'New ABYIP member added',          who:'Liza Mendoza',    time:'Mar 20, 2024' },
            { type:'delete',  text:'Kabataan record deleted',         who:'Carlo Bautista',  time:'Jun 8, 2024' },
            { type:'restore', text:'Deleted record restored',         who:'Mark Villanueva', time:'Aug 14, 2024' },
        ],
        announcements: [
            { title:'Youth Congress 2024',              date:'Mar 5, 2024' },
            { title:'KK Profiling Drive — Q2',          date:'Apr 10, 2024' },
            { title:'Sports Festival Registration',     date:'Jul 1, 2024' },
        ],
        events: [
            { day:'05', mon:'Mar', title:'Youth Congress 2024',    time:'9:00 AM' },
            { day:'10', mon:'Apr', title:'KK Profiling Drive',     time:'8:00 AM' },
            { day:'01', mon:'Jul', title:'Sports Festival',        time:'7:00 AM' },
        ],
    },
    2025: {
        stats: { kabataan: 0, abyip: 0, pending: 0, approved: 0, rejected: 0, programs: 0, budget: '₱0.00' },
        purokLabels:  ['Bayside','Villa Gracia','Imelda','Lupang Pangako','Damayan','Marcelo','Bigayan Villa Rosa','Bigayan San Luis','Phase 3','Maligaya'],
        purokCounts:  [36, 30, 27, 34, 25, 32, 29, 28, 35, 24],
        monthlyApproved: [10,13,12,17,14,21,18,24,20,27,23,19],
        monthlyRejected: [2,4,3,4,3,5,4,6,3,5,4,3],
        abyipStatus: { labels:['Active','Inactive','Pending','Completed'], values:[40,10,7,17] },
        genderDist: { labels:['Male','Female'], values:[152, 136] },
        budgetPrograms: { labels:['Education','Sports','Health','Environment','Livelihood','Others'], values:[280000,155000,130000,80000,100000,75000] },
        activity: [
            { type:'approve', text:'Program budget approved',         who:'SK Treasurer',    time:'Feb 3, 2025' },
            { type:'add',     text:'New Kabataan record added',       who:'Maria Santos',    time:'Apr 11, 2025' },
            { type:'reject',  text:'KK Profiling request rejected',   who:'Pedro Reyes',     time:'Jul 22, 2025' },
            { type:'approve', text:'KK Profiling request approved',   who:'Juan Dela Cruz',  time:'Oct 5, 2025' },
            { type:'delete',  text:'ABYIP record deleted',            who:'Ana Lim',         time:'Nov 30, 2025' },
        ],
        announcements: [
            { title:'SK General Assembly — Feb 2025',  date:'Jan 28, 2025' },
            { title:'ABYIP Budget Review Meeting',      date:'Mar 15, 2025' },
            { title:'KK Profiling Deadline Extended',   date:'Jun 10, 2025' },
        ],
        events: [
            { day:'10', mon:'Feb', title:'SK General Assembly',    time:'9:00 AM' },
            { day:'15', mon:'Mar', title:'ABYIP Budget Review',    time:'2:00 PM' },
            { day:'10', mon:'Jun', title:'KK Profiling Drive',     time:'8:00 AM' },
        ],
    },
    2026: {
        stats: { kabataan: 0, abyip: 0, pending: 0, approved: 0, rejected: 0, programs: 0, budget: '₱0.00' },
        purokLabels:  ['Bayside','Villa Gracia','Imelda','Lupang Pangako','Damayan','Marcelo','Bigayan Villa Rosa','Bigayan San Luis','Phase 3','Maligaya'],
        purokCounts:  [42, 35, 31, 38, 28, 36, 33, 32, 40, 27],
        monthlyApproved: [12,18,15,22,19,28,24,31,27,35,30,38],
        monthlyRejected: [3, 5, 4, 6, 3, 7, 5, 8, 4, 6, 5, 4],
        abyipStatus: { labels:['Active','Inactive','Pending','Completed'], values:[52,14,9,25] },
        genderDist: { labels:['Male','Female'], values:[178, 164] },
        budgetPrograms: { labels:['Education','Sports','Health','Environment','Livelihood','Others'], values:[340000,190000,160000,100000,130000,90000] },
        activity: [
            { type:'approve', text:'KK Profiling request approved',  who:'Juan Dela Cruz',  time:'2 min ago' },
            { type:'add',     text:'New Kabataan record added',       who:'Maria Santos',    time:'15 min ago' },
            { type:'reject',  text:'KK Profiling request rejected',   who:'Pedro Reyes',     time:'1 hr ago' },
            { type:'delete',  text:'Kabataan record deleted',         who:'Ana Lim',         time:'2 hrs ago' },
            { type:'restore', text:'Deleted record restored',         who:'Carlo Bautista',  time:'3 hrs ago' },
            { type:'approve', text:'Program budget approved',         who:'SK Treasurer',    time:'5 hrs ago' },
            { type:'delete',  text:'ABYIP record deleted',            who:'Mark Villanueva', time:'Yesterday' },
        ],
        announcements: [
            { title:'SK General Assembly — May 5, 2026',    date:'Apr 22, 2026' },
            { title:'ABYIP Budget Review Meeting',           date:'Apr 20, 2026' },
            { title:'KK Profiling Deadline Extended',        date:'Apr 18, 2026' },
            { title:'Youth Sports Festival Registration',    date:'Apr 15, 2026' },
        ],
        events: [
            { day:'05', mon:'May', title:'SK General Assembly',       time:'9:00 AM' },
            { day:'10', mon:'May', title:'Youth Leadership Summit',   time:'8:00 AM' },
            { day:'15', mon:'May', title:'ABYIP Budget Review',       time:'2:00 PM' },
            { day:'20', mon:'May', title:'Community Outreach Day',    time:'7:00 AM' },
        ],
    },
};

/* Pending requests are always current — not year-filtered */
const PENDING_REQUESTS = [
    { id:1, name:'Jose Rizal',       purok:'Purok 3', date:'Apr 20, 2026' },
    { id:2, name:'Andres Bonifacio', purok:'Purok 1', date:'Apr 21, 2026' },
    { id:3, name:'Emilio Aguinaldo', purok:'Purok 5', date:'Apr 22, 2026' },
];

const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

/* Active chart instances — destroyed and rebuilt on year change */
let chartBar    = null;
let chartLine   = null;
let chartPie    = null;
let chartDonut  = null;

/* Currently selected year */
let selectedYear = 2026;

/* ── Chart.js global defaults ────────────────────────────── */
function applyChartDefaults() {
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#6b7280';
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1f2937';
    Chart.defaults.plugins.tooltip.titleColor       = '#f9fafb';
    Chart.defaults.plugins.tooltip.bodyColor        = '#d1d5db';
    Chart.defaults.plugins.tooltip.padding          = 10;
    Chart.defaults.plugins.tooltip.cornerRadius     = 8;
}

/* ── Boot ────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    applyChartDefaults();
    renderAll(selectedYear);
    initYearFilter();
    initModals();
    renderReminder();
    renderCommittees();
});

/* ── Year filter ─────────────────────────────────────────── */
function initYearFilter() {
    const sel = document.getElementById('yearSelect');
    if (!sel) return;
    sel.value = String(selectedYear);
    sel.addEventListener('change', function () {
        selectedYear = Number(sel.value);
        renderAll(selectedYear);
    });
}

/* ── Render everything for a given year ──────────────────── */
function renderAll(year) {
    const d = YEAR_DATA[year];
    if (!d) return;
    renderStats(d);
    renderActivity(d);
    renderAnnouncements(d);
    renderEvents(d);
    renderPendingRequests();
    renderBarChart(d);
    renderLineChart(d);
    renderPieChart(d);
    renderDonutChart(d);
}

/* ── Stat cards ──────────────────────────────────────────── */
function renderStats(d) {
    animateCount('statKabataan', d.stats.kabataan);
    animateCount('statKkTotal',  d.stats.kkTotal  || 0);
    animateCount('statPending',  d.stats.pending);
    animateCount('statApproved', d.stats.approved);
    animateCount('statRejected', d.stats.rejected);
    animateCount('statPrograms', d.stats.programs);
    animateCount('statActivePrograms', d.stats.activePrograms || 0);
    animateCount('statDeletedKabataan', d.stats.deletedKabataan || 0);
    animateCount('statDeletedAbyip',    d.stats.deletedAbyip    || 0);
    animateCount('statRejectedItems',   d.stats.rejectedItems   || 0);
    animateCount('statRejectedKK',      d.stats.rejectedKK      || 0);
    const budgetEl    = document.getElementById('statBudget');
    const expensesEl  = document.getElementById('statExpenses');
    const remainingEl = document.getElementById('statRemaining');
    if (budgetEl)    budgetEl.textContent    = d.stats.budget    || '₱0.00';
    if (expensesEl)  expensesEl.textContent  = d.stats.expenses  || '₱0.00';
    if (remainingEl) remainingEl.textContent = d.stats.remaining || '₱0.00';
}

function animateCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let current = 0;
    const step  = Math.ceil(target / 40);
    const timer = setInterval(function () {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString();
        if (current >= target) clearInterval(timer);
    }, 28);
}

/* ── Activity timeline ───────────────────────────────────── */
const ICONS = {
    approve: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
    reject:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`,
    delete:  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path></svg>`,
    restore: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 .49-4.95"></path></svg>`,
    add:     `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>`,
};

function renderActivity(d) {
    const list = document.getElementById('activityList');
    if (!list) return;
    if (!d.activity || !d.activity.length) {
        list.innerHTML = '<p class="dash-empty-msg">No activity recorded for this year.</p>';
        return;
    }
    list.innerHTML = d.activity.map(function (item) {
        return `<div class="activity-item">
            <div class="activity-dot ${item.type}">${ICONS[item.type] || ''}</div>
            <div class="activity-body">
                <strong>${esc(item.text)}</strong>
                <span>${esc(item.who)}</span>
            </div>
            <div class="activity-time">${esc(item.time)}</div>
        </div>`;
    }).join('');
}

/* ── Announcements ───────────────────────────────────────── */
function renderAnnouncements(d) {
    const list = document.getElementById('announcementsList');
    if (!list) return;
    if (!d.announcements || !d.announcements.length) {
        list.innerHTML = '<p class="dash-empty-msg">No announcements for this year.</p>';
        return;
    }
    list.innerHTML = d.announcements.map(function (a) {
        return `<div class="announcement-item">
            <div class="announcement-item-title">${esc(a.title)}</div>
            <div class="announcement-item-meta">${esc(a.date)}</div>
        </div>`;
    }).join('');
}

/* ── Events ──────────────────────────────────────────────── */
function renderEvents(d) {
    const list = document.getElementById('eventsList');
    if (!list) return;
    if (!d.events || !d.events.length) {
        list.innerHTML = '<p class="dash-empty-msg">No events for this year.</p>';
        return;
    }
    list.innerHTML = d.events.map(function (e) {
        return `<div class="event-item">
            <div class="event-date-box">
                <span class="event-date-day">${esc(e.day)}</span>
                <span class="event-date-mon">${esc(e.mon)}</span>
            </div>
            <div class="event-body">
                <div class="event-title">${esc(e.title)}</div>
                <div class="event-meta">${esc(e.time)}</div>
            </div>
        </div>`;
    }).join('');
}

/* ── Pending requests (modal) ────────────────────────────── */
function renderPendingRequests() {
    const container = document.getElementById('pendingRequestsList');
    if (!container) return;
    if (!PENDING_REQUESTS.length) {
        container.innerHTML = '<p style="font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">No pending requests.</p>';
        return;
    }
    container.innerHTML = PENDING_REQUESTS.map(function (r) {
        return `<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb;margin-bottom:8px;">
            <div>
                <div style="font-size:13px;font-weight:700;color:#111827;">${esc(r.name)}</div>
                <div style="font-size:12px;color:#6b7280;">${esc(r.purok)} &bull; ${esc(r.date)}</div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button data-approve="${r.id}" style="padding:6px 14px;border-radius:8px;border:none;background:#22c55e;color:#fff;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">Approve</button>
                <button data-reject="${r.id}"  style="padding:6px 14px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">Reject</button>
            </div>
        </div>`;
    }).join('');

    container.querySelectorAll('[data-approve]').forEach(function (btn) {
        btn.addEventListener('click', function () { handleApprove(Number(btn.dataset.approve)); });
    });
    container.querySelectorAll('[data-reject]').forEach(function (btn) {
        btn.addEventListener('click', function () { handleReject(Number(btn.dataset.reject)); });
    });
}

function handleApprove(id) {
    const idx = PENDING_REQUESTS.findIndex(function (r) { return r.id === id; });
    if (idx !== -1) PENDING_REQUESTS.splice(idx, 1);
    renderPendingRequests();
    const d = YEAR_DATA[selectedYear];
    d.stats.approved++;
    d.stats.pending = Math.max(0, d.stats.pending - 1);
    setText('statApproved', d.stats.approved.toLocaleString());
    setText('statPending',  d.stats.pending.toLocaleString());
}

function handleReject(id) {
    const idx = PENDING_REQUESTS.findIndex(function (r) { return r.id === id; });
    if (idx !== -1) PENDING_REQUESTS.splice(idx, 1);
    renderPendingRequests();
    const d = YEAR_DATA[selectedYear];
    d.stats.rejected++;
    d.stats.pending = Math.max(0, d.stats.pending - 1);
    setText('statRejected', d.stats.rejected.toLocaleString());
    setText('statPending',  d.stats.pending.toLocaleString());
}

/* ── Bar Chart ───────────────────────────────────────────── */
function renderBarChart(d) {
    const ctx = document.getElementById('chartKabataanBarangay');
    if (!ctx) return;
    if (chartBar) { chartBar.destroy(); chartBar = null; }
    chartBar = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: d.purokLabels,
            datasets: [{
                label: 'Kabataan',
                data: d.purokCounts,
                backgroundColor: [
                    'rgba(59,130,246,.85)',
                    'rgba(34,197,94,.85)',
                    'rgba(244,194,13,.85)',
                    'rgba(239,68,68,.85)',
                    'rgba(168,85,247,.85)',
                    'rgba(20,184,166,.85)',
                    'rgba(249,115,22,.85)',
                    'rgba(99,102,241,.85)',
                    'rgba(236,72,153,.85)',
                    'rgba(16,185,129,.85)',
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
                tooltip: { callbacks: { label: function (c) { return ' ' + c.parsed.y + ' youth'; } } }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 35,
                        minRotation: 25,
                    }
                },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 10 } },
            },
        },
    });
}

/* ── Line Chart ──────────────────────────────────────────── */
function renderLineChart(d) {
    const ctx = document.getElementById('chartMonthlyRequests');
    if (!ctx) return;
    if (chartLine) { chartLine.destroy(); chartLine = null; }

    /* Derive pending = approved - rejected as a rough estimate */
    const monthlyPending = d.monthlyApproved.map(function (a, i) {
        return Math.max(0, Math.round((a + d.monthlyRejected[i]) * 0.18));
    });

    chartLine = new Chart(ctx, {
        type: 'line',
        data: {
            labels: MONTHS,
            datasets: [
                {
                    label: 'Approved',
                    data: d.monthlyApproved,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,.1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#22c55e',
                    tension: 0.4,
                    fill: true,
                    hidden: false,
                },
                {
                    label: 'Rejected',
                    data: d.monthlyRejected,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
                    tension: 0.4,
                    fill: true,
                    hidden: false,
                },
                {
                    label: 'Pending',
                    data: monthlyPending,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    tension: 0.4,
                    fill: true,
                    hidden: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
            },
        },
    });

    /* Wire up checkbox filters */
    const cbApproved = document.getElementById('filterApproved');
    const cbRejected = document.getElementById('filterRejected');
    const cbPending  = document.getElementById('filterPending');

    function applyFilter() {
        if (cbApproved) chartLine.data.datasets[0].hidden = !cbApproved.checked;
        if (cbRejected) chartLine.data.datasets[1].hidden = !cbRejected.checked;
        if (cbPending)  chartLine.data.datasets[2].hidden = !cbPending.checked;
        chartLine.update();
    }

    [cbApproved, cbRejected, cbPending].forEach(function (cb) {
        if (cb) { cb.checked = true; cb.addEventListener('change', applyFilter); }
    });
}

/* ── Pie Chart — Kabataan Sex Distribution ──────────────── */
function renderPieChart(d) {
    const ctx = document.getElementById('chartGenderPie');
    if (!ctx) return;
    if (chartPie) { chartPie.destroy(); chartPie = null; }

    const PIE_COLORS = ['#3b82f6', '#ec4899'];
    const total = d.genderDist.values.reduce(function (a, b) { return a + b; }, 0);

    /* Inline label plugin — draws label+% directly on each slice */
    const pieLabelsPlugin = {
        id: 'pieLabels',
        afterDraw: function (chart) {
            const ctx2 = chart.ctx;
            chart.data.datasets.forEach(function (dataset, di) {
                const meta = chart.getDatasetMeta(di);
                meta.data.forEach(function (arc, i) {
                    const val = dataset.data[i];
                    if (!val) return;
                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                    const label = chart.data.labels[i];
                    const angle = (arc.startAngle + arc.endAngle) / 2;
                    const r = (arc.innerRadius + arc.outerRadius) / 2;
                    const x = arc.x + Math.cos(angle) * r;
                    const y = arc.y + Math.sin(angle) * r;
                    ctx2.save();
                    ctx2.textAlign = 'center';
                    ctx2.textBaseline = 'middle';
                    ctx2.fillStyle = '#fff';
                    ctx2.font = 'bold 11px Segoe UI, sans-serif';
                    ctx2.fillText(label, x, y - 7);
                    ctx2.font = 'bold 12px Segoe UI, sans-serif';
                    ctx2.fillText(pct + '%', x, y + 7);
                    ctx2.restore();
                });
            });
        }
    };

    chartPie = new Chart(ctx, {
        type: 'pie',
        plugins: [pieLabelsPlugin],
        data: {
            labels: d.genderDist.labels,
            datasets: [{
                data: d.genderDist.values,
                backgroundColor: PIE_COLORS,
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
                        label: function (c) {
                            const t = c.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                            return ' ' + c.label + ': ' + c.parsed + ' (' + ((c.parsed / t) * 100).toFixed(1) + '%)';
                        }
                    }
                }
            },
        },
    });

    /* Update HTML legend */
    const legend = document.getElementById('genderLegend');
    if (legend) {
        legend.innerHTML = d.genderDist.labels.map(function (label, i) {
            return `<div class="pie-legend-item">
                <span class="pie-legend-box" style="background:${PIE_COLORS[i]};"></span>
                <span class="pie-legend-label">${esc(label)}</span>
            </div>`;
        }).join('');
    }
}

/* ── Donut Chart ─────────────────────────────────────────── */
function renderDonutChart(d) {
    const ctx = document.getElementById('chartBudgetDonut');
    if (!ctx) return;
    if (chartDonut) { chartDonut.destroy(); chartDonut = null; }
    const colors = ['#3b82f6','#22c55e','#ef4444','#14b8a6','#f97316','#a855f7'];
    const total  = d.budgetPrograms.values.reduce(function (a, b) { return a + b; }, 0);

    /* Inline label plugin for donut — draws % on each segment */
    const donutLabelsPlugin = {
        id: 'donutLabels',
        afterDraw: function (chart) {
            const ctx2 = chart.ctx;
            chart.data.datasets.forEach(function (dataset, di) {
                const meta = chart.getDatasetMeta(di);
                meta.data.forEach(function (arc, i) {
                    const val = dataset.data[i];
                    if (!val) return;
                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                    if (pct < 5) return; /* skip tiny slices */
                    const angle = (arc.startAngle + arc.endAngle) / 2;
                    const r = (arc.innerRadius + arc.outerRadius) / 2;
                    const x = arc.x + Math.cos(angle) * r;
                    const y = arc.y + Math.sin(angle) * r;
                    ctx2.save();
                    ctx2.textAlign = 'center';
                    ctx2.textBaseline = 'middle';
                    ctx2.fillStyle = '#fff';
                    ctx2.font = 'bold 11px Segoe UI, sans-serif';
                    ctx2.fillText(pct + '%', x, y);
                    ctx2.restore();
                });
            });
        }
    };

    chartDonut = new Chart(ctx, {
        type: 'doughnut',
        plugins: [donutLabelsPlugin],
        data: {
            labels: d.budgetPrograms.labels,
            datasets: [{
                data: d.budgetPrograms.values,
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
                        label: function (c) {
                            return ' ₱' + c.parsed.toLocaleString() + ' (' + ((c.parsed / total) * 100).toFixed(1) + '%)';
                        }
                    }
                }
            },
        },
    });

    /* Horizontal legend — square box + label only, no % */
    const legend = document.getElementById('donutLegend');
    if (!legend) return;
    legend.innerHTML = d.budgetPrograms.labels.map(function (label, i) {
        return `<div class="donut-legend-item">
            <div class="donut-legend-box" style="background:${colors[i]};"></div>
            <span class="donut-legend-label">${esc(label)}</span>
        </div>`;
    }).join('');
}

/* ── Calendar Reminder ───────────────────────────────────── */
function renderReminder() {
    /* Read today's note from localStorage (set by the Calendar module).
       Key format: calNote_YYYY_M_D  e.g. calNote_2026_4_22
       Falls back to sample notes so the banner is always visible as a demo. */
    const today = new Date();
    const key   = 'calNote_' + today.getFullYear() + '_' + (today.getMonth() + 1) + '_' + today.getDate();
    const stored = localStorage.getItem(key);

    /* Sample notes shown when no real note is saved */
    const SAMPLE_NOTES = [
        'SK Meeting at 3:00 PM',
        'Submit ABYIP documents',
        'KK Profiling deadline today',
    ];

    const noteText = (stored && stored.trim())
        ? stored.trim()
        : SAMPLE_NOTES.join('  •  ');

    const banner = document.getElementById('calendarReminderBanner');
    const textEl = document.getElementById('reminderText');
    if (banner && textEl) {
        textEl.textContent = noteText;
        banner.classList.remove('d-none');
    }
}

/* ── Committees ──────────────────────────────────────────── */
const COMMITTEES = [
    { name:'Education Committee',       head:'Maria Santos',    status:'Active' },
    { name:'Sports Committee',          head:'Juan Dela Cruz',  status:'Active' },
    { name:'Health Committee',          head:'Ana Lim',         status:'Active' },
    { name:'Environment Committee',     head:'Pedro Reyes',     status:'Active' },
    { name:'Livelihood Committee',      head:'Liza Mendoza',    status:'Active' },
    { name:'Peace & Order Committee',   head:'Carlo Bautista',  status:'Active' },
];

function renderCommittees() {
    const container = document.getElementById('committeesList');
    if (!container) return;
    container.innerHTML = COMMITTEES.map(function (c) {
        return `<div class="col-12 col-sm-6 col-lg-4">
            <div class="committee-card">
                <div class="committee-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="committee-card-body">
                    <div class="committee-card-name">${esc(c.name)}</div>
                    <div class="committee-card-meta">Head: ${esc(c.head)}</div>
                </div>
                <span class="committee-card-badge">${esc(c.status)}</span>
            </div>
        </div>`;
    }).join('');
}

/* ── Modal system ────────────────────────────────────────── */
function initModals() {
    const map = {
        qaAddKabataan:     'modalAddKabataan',
        qaApproveRequests: 'modalApproveRequests',
        qaCreateProgram:   'modalCreateProgram',
        qaAddAbyip:        'modalAddAbyip',
    };

    Object.keys(map).forEach(function (btnId) {
        const btn = document.getElementById(btnId);
        if (btn) btn.addEventListener('click', function () { openModal(map[btnId]); });
    });

    document.querySelectorAll('[data-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(btn.getAttribute('data-close')); });
    });

    document.querySelectorAll('.dash-modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeModal(backdrop.id);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dash-modal-backdrop.open').forEach(function (m) {
                closeModal(m.id);
            });
        }
    });
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

/* ── Utilities ───────────────────────────────────────────── */
function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}
