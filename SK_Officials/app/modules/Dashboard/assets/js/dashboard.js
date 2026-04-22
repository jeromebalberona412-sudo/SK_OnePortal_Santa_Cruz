/**
 * SK Officials Dashboard — dashboard.js
 * Chart.js imported via npm (bundled by Vite)
 */

import Chart from 'chart.js/auto';

/* ── Sample data (swap with real API calls) ──────────────── */
const DATA = {
    stats: {
        kabataan: 342,
        abyip:    87,
        pending:  14,
        approved: 198,
        rejected: 23,
        programs: 9,
        budget:   '₱1.42M',
    },
    barangayLabels: ['Calios','Bagong Silang','Maligaya','Pag-asa','Rizal','Sampaguita'],
    barangayCounts: [342, 215, 189, 267, 143, 198],
    monthlyLabels:  ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    monthlyApproved:[12,18,15,22,19,28,24,31,27,35,30,38],
    monthlyRejected:[3, 5, 4, 6, 3, 7, 5, 8, 4, 6, 5, 4],
    abyipStatus: {
        labels: ['Active','Inactive','Pending','Completed'],
        values: [45, 12, 8, 22],
    },
    budgetPrograms: {
        labels: ['Education','Sports','Health','Environment','Livelihood','Others'],
        values: [320000, 180000, 150000, 95000, 120000, 85000],
    },
    activity: [
        { type:'approve', text:'KK Profiling request approved',  who:'Juan Dela Cruz',  time:'2 min ago' },
        { type:'add',     text:'New Kabataan record added',       who:'Maria Santos',    time:'15 min ago' },
        { type:'reject',  text:'KK Profiling request rejected',   who:'Pedro Reyes',     time:'1 hr ago' },
        { type:'delete',  text:'Kabataan record deleted',         who:'Ana Lim',         time:'2 hrs ago' },
        { type:'restore', text:'Deleted record restored',         who:'Carlo Bautista',  time:'3 hrs ago' },
        { type:'approve', text:'Program budget approved',         who:'SK Treasurer',    time:'5 hrs ago' },
        { type:'add',     text:'New ABYIP member added',          who:'Liza Mendoza',    time:'Yesterday' },
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
    pendingRequests: [
        { id:1, name:'Jose Rizal',       purok:'Purok 3', date:'Apr 20, 2026' },
        { id:2, name:'Andres Bonifacio', purok:'Purok 1', date:'Apr 21, 2026' },
        { id:3, name:'Emilio Aguinaldo', purok:'Purok 5', date:'Apr 22, 2026' },
    ],
};

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
    renderDate();
    renderStats();
    renderActivity();
    renderAnnouncements();
    renderEvents();
    renderPendingRequests();
    renderBarChart();
    renderLineChart();
    renderPieChart();
    renderDonutChart();
    initModals();
});

/* ── Date badge ──────────────────────────────────────────── */
function renderDate() {
    const el = document.getElementById('dashDateText');
    if (!el) return;
    el.textContent = new Date().toLocaleDateString('en-PH', {
        weekday:'short', year:'numeric', month:'long', day:'numeric'
    });
}

/* ── Stat cards ──────────────────────────────────────────── */
function renderStats() {
    const d = DATA.stats;
    animateCount('statKabataan', d.kabataan);
    animateCount('statAbyip',    d.abyip);
    animateCount('statPending',  d.pending);
    animateCount('statApproved', d.approved);
    animateCount('statRejected', d.rejected);
    animateCount('statPrograms', d.programs);
    const budgetEl = document.getElementById('statBudget');
    if (budgetEl) budgetEl.textContent = d.budget;
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

function renderActivity() {
    const list = document.getElementById('activityList');
    if (!list) return;
    list.innerHTML = DATA.activity.map(function (item) {
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
function renderAnnouncements() {
    const list = document.getElementById('announcementsList');
    if (!list) return;
    list.innerHTML = DATA.announcements.map(function (a) {
        return `<div class="announcement-item">
            <div class="announcement-item-title">${esc(a.title)}</div>
            <div class="announcement-item-meta">${esc(a.date)}</div>
        </div>`;
    }).join('');
}

/* ── Events ──────────────────────────────────────────────── */
function renderEvents() {
    const list = document.getElementById('eventsList');
    if (!list) return;
    list.innerHTML = DATA.events.map(function (e) {
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
    if (!DATA.pendingRequests.length) {
        container.innerHTML = '<p style="font-size:13px;color:#9ca3af;text-align:center;padding:20px 0;">No pending requests.</p>';
        return;
    }
    container.innerHTML = DATA.pendingRequests.map(function (r) {
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
    DATA.pendingRequests = DATA.pendingRequests.filter(function (r) { return r.id !== id; });
    renderPendingRequests();
    DATA.stats.approved++;
    DATA.stats.pending = Math.max(0, DATA.stats.pending - 1);
    setText('statApproved', DATA.stats.approved.toLocaleString());
    setText('statPending',  DATA.stats.pending.toLocaleString());
}

function handleReject(id) {
    DATA.pendingRequests = DATA.pendingRequests.filter(function (r) { return r.id !== id; });
    renderPendingRequests();
    DATA.stats.rejected++;
    DATA.stats.pending = Math.max(0, DATA.stats.pending - 1);
    setText('statRejected', DATA.stats.rejected.toLocaleString());
    setText('statPending',  DATA.stats.pending.toLocaleString());
}

/* ── Bar Chart ───────────────────────────────────────────── */
function renderBarChart() {
    const ctx = document.getElementById('chartKabataanBarangay');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: DATA.barangayLabels,
            datasets: [{
                label: 'Kabataan',
                data: DATA.barangayCounts,
                backgroundColor: [
                    'rgba(244,194,13,.85)',
                    'rgba(59,130,246,.85)',
                    'rgba(34,197,94,.85)',
                    'rgba(168,85,247,.85)',
                    'rgba(249,115,22,.85)',
                    'rgba(20,184,166,.85)',
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
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 50 } },
            },
        },
    });
}

/* ── Line Chart ──────────────────────────────────────────── */
function renderLineChart() {
    const ctx = document.getElementById('chartMonthlyRequests');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: DATA.monthlyLabels,
            datasets: [
                {
                    label: 'Approved',
                    data: DATA.monthlyApproved,
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
                    data: DATA.monthlyRejected,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.08)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444',
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
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 12, padding: 14, font: { size: 11 } },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
            },
        },
    });
}

/* ── Pie Chart ───────────────────────────────────────────── */
function renderPieChart() {
    const ctx = document.getElementById('chartAbyipStatus');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: DATA.abyipStatus.labels,
            datasets: [{
                data: DATA.abyipStatus.values,
                backgroundColor: ['#22c55e','#f97316','#f4c20d','#3b82f6'],
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: { boxWidth: 12, padding: 12, font: { size: 11 } },
                },
                tooltip: {
                    callbacks: {
                        label: function (c) {
                            const total = c.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                            return ' ' + c.label + ': ' + c.parsed + ' (' + ((c.parsed / total) * 100).toFixed(1) + '%)';
                        }
                    }
                }
            },
        },
    });
}

/* ── Donut Chart ─────────────────────────────────────────── */
function renderDonutChart() {
    const ctx = document.getElementById('chartBudgetDonut');
    if (!ctx) return;
    const colors = ['#3b82f6','#22c55e','#ef4444','#14b8a6','#f97316','#a855f7'];
    const d      = DATA.budgetPrograms;
    const total  = d.values.reduce(function (a, b) { return a + b; }, 0);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
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

    const legend = document.getElementById('donutLegend');
    if (!legend) return;
    legend.innerHTML = d.labels.map(function (label, i) {
        return `<div class="donut-legend-item">
            <div class="donut-legend-dot" style="background:${colors[i]};"></div>
            <span class="donut-legend-label">${esc(label)}</span>
            <span class="donut-legend-value">${((d.values[i] / total) * 100).toFixed(0)}%</span>
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
