// Rejected Sports Applications Module

document.addEventListener('DOMContentLoaded', function () {
    initRejectedSports();
});

const RSP_SAMPLE_REJECTED = [
    {
        id: 2101,
        lastName: 'Mendoza',
        firstName: 'Angelo',
        middleName: 'L.',
        suffix: '',
        sport: 'Basketball',
        division: 'Youth Division (18-21)',
        dateApplied: 'Apr 12, 2026',
        rejectedDate: 'Apr 14, 2026',
        rejectedTime: '09:30 AM',
        rejectionReason: 'Incomplete medical clearance documents',
        status: 'Rejected',
        _rejectedTs: new Date('2026-04-14T09:30:00'),
        skTerm: '2025-2027',
    },
    {
        id: 2102,
        lastName: 'Torres',
        firstName: 'Bianca',
        middleName: 'R.',
        suffix: '',
        sport: 'Volleyball',
        division: 'Young Adult (22-25)',
        dateApplied: 'Apr 08, 2026',
        rejectedDate: 'Apr 10, 2026',
        rejectedTime: '02:15 PM',
        rejectionReason: 'Did not meet division age requirements',
        status: 'Rejected',
        _rejectedTs: new Date('2026-04-10T14:15:00'),
        skTerm: '2025-2027',
    },
    {
        id: 2103,
        lastName: 'Villanueva',
        firstName: 'Carlos',
        middleName: 'D.',
        suffix: 'Jr.',
        sport: 'Football',
        division: 'Cadet Division (15-17)',
        dateApplied: 'Apr 05, 2026',
        rejectedDate: 'Apr 06, 2026',
        rejectedTime: '11:45 AM',
        rejectionReason: 'Revoked due to falsified requirement submission',
        status: 'Rejected',
        _rejectedTs: new Date('2026-04-06T11:45:00'),
        skTerm: '2025-2027',
    },
];

let rspAllRecords = [];
let rspFiltered = [];
let rspCurrentPage = 1;
const rspPerPage = 10;
let rspPendingRestoreId = null;
let rspActiveFilter = 'all';
let rspArchiveTerm = '2025-2027';

function rspNow() { return new Date(); }

function rspIsToday(ts) {
    const n = rspNow();
    return ts.getFullYear() === n.getFullYear()
        && ts.getMonth() === n.getMonth()
        && ts.getDate() === n.getDate();
}

function rspIsThisWeek(ts) {
    const n = rspNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function rspIsThisMonth(ts) {
    const n = rspNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function rspApplyTabFilter(records, filter) {
    if (filter === 'today') return records.filter(r => rspIsToday(r._rejectedTs));
    if (filter === 'week') return records.filter(r => rspIsThisWeek(r._rejectedTs));
    if (filter === 'month') return records.filter(r => rspIsThisMonth(r._rejectedTs));
    return records;
}

function rspFormatName(r) {
    const parts = [r.lastName, r.firstName, r.middleName].filter(Boolean);
    return parts.join(', ');
}

function rspSeedSamples(list) {
    let records = Array.isArray(list) ? [...list] : [];
    RSP_SAMPLE_REJECTED.forEach((sample) => {
        const idx = records.findIndex(r => r.id === sample.id);
        if (idx === -1) {
            records.unshift({ ...sample });
        } else {
            records[idx] = { ...records[idx], ...sample, status: 'Rejected' };
        }
    });
    localStorage.setItem('sports_rejected', JSON.stringify(records));
    localStorage.setItem('sports_rejected_seeded_v1', '1');
    return records;
}

function rspLoadRecords() {
    let all = JSON.parse(localStorage.getItem('sports_rejected') || '[]');
    if (!localStorage.getItem('sports_rejected_seeded_v1') || all.length === 0) {
        all = rspSeedSamples(all);
    } else {
        all = rspSeedSamples(all);
    }

    rspAllRecords = all.map(r => ({
        ...r,
        _rejectedTs: r._rejectedTs ? new Date(r._rejectedTs) : new Date(r.rejectedDate || 0),
        skTerm: r.skTerm || (window.SkArchive
            ? SkArchive.inferTermFromDate(new Date(r.rejectedDate || 0))
            : '2025-2027'),
    }));

    rspFiltered = rspApplyAllFilters();
}

function rspApplyAllFilters() {
    const search = (document.getElementById('rejectedSportsSearch')?.value || '').trim().toLowerCase();
    let list = rspApplyTabFilter(rspAllRecords, rspActiveFilter);

    if (search) {
        list = list.filter(r => {
            const hay = [
                r.lastName, r.firstName, r.middleName, r.sport, r.division, r.rejectionReason,
            ].join(' ').toLowerCase();
            return hay.includes(search);
        });
    }

    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, rspArchiveTerm, ['_rejectedTs', 'rejectedDate']);
    }

    list.sort((a, b) => {
        const ln = (a.lastName || '').localeCompare(b.lastName || '');
        if (ln !== 0) return ln;
        return (a.firstName || '').localeCompare(b.firstName || '');
    });

    return list;
}

function initRejectedSports() {
    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rspArchiveTerm = termId;
            rspLoadRecords();
            renderStats();
            renderTable();
        });
    } else {
        rspLoadRecords();
        renderStats();
        renderTable();
    }
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
}

function renderStats() {
    const row = document.getElementById('rspStatsRow');
    if (!row) return;

    const total = rspAllRecords.length;
    const month = rspAllRecords.filter(r => rspIsThisMonth(r._rejectedTs)).length;
    const today = rspAllRecords.filter(r => rspIsToday(r._rejectedTs)).length;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Rejected</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            rspActiveFilter = this.dataset.filter;

            const labels = {
                all: 'All Rejected Records',
                today: 'Rejected Today',
                week: 'Rejected This Week',
                month: 'Rejected This Month',
            };
            const label = document.getElementById('rspSectionLabel');
            if (label) label.textContent = labels[rspActiveFilter] || 'Rejected Records';

            rspFiltered = rspApplyAllFilters();
            rspCurrentPage = 1;
            renderTable();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedSportsTableBody');
    const info = document.getElementById('rejectedSportsPaginationInfo');
    if (!tbody) return;

    rspFiltered = rspApplyAllFilters();
    const start = (rspCurrentPage - 1) * rspPerPage;
    const end = start + rspPerPage;
    const page = rspFiltered.slice(start, end);

    if (rspFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="7">No rejected sports applications found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const canRestore = window.SkArchive
            ? SkArchive.canRestoreRecord(r, ['_rejectedTs', 'rejectedDate'])
            : true;
        const restoreBtn = canRestore
            ? `<button class="btn-restore-action" data-id="${r.id}">Restore</button>`
            : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`;

        return `
        <tr>
            <td style="font-weight:600;color:#111827;">${rspFormatName(r)}</td>
            <td>${r.sport || '—'}</td>
            <td>${r.division || '—'}</td>
            <td><span class="rejection-reason-cell" title="${r.rejectionReason || ''}">${r.rejectionReason || '—'}</span></td>
            <td><span class="deleted-at-badge">${r.rejectedDate || '—'}</span></td>
            <td><span class="deleted-time-badge">${r.rejectedTime || '—'}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    ${restoreBtn}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, rspFiltered.length)} of ${rspFiltered.length} records`;
    }

    renderPagination(rspFiltered.length);

    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () {
            openViewModal(parseInt(this.dataset.id, 10));
        });
    });
    tbody.querySelectorAll('.btn-restore-action:not(.is-disabled)').forEach(btn => {
        btn.addEventListener('click', function () {
            openRestoreModal(parseInt(this.dataset.id, 10));
        });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / rspPerPage) || 1;
    const nums = document.getElementById('rejectedSportsPageNumbers');
    const prev = document.getElementById('rejectedSportsPrevBtn');
    const next = document.getElementById('rejectedSportsNextBtn');

    if (prev) prev.disabled = rspCurrentPage <= 1;
    if (next) next.disabled = rspCurrentPage >= pages;

    if (!nums) return;
    nums.innerHTML = '';

    for (let i = 1; i <= pages; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pagination-number' + (i === rspCurrentPage ? ' active' : '');
        btn.textContent = String(i);
        btn.addEventListener('click', function () {
            rspCurrentPage = i;
            renderTable();
        });
        nums.appendChild(btn);
    }
}

function bindSearch() {
    const input = document.getElementById('rejectedSportsSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        rspCurrentPage = 1;
        renderTable();
    });
}

function openRestoreModal(id) {
    const record = rspAllRecords.find(r => r.id === id);
    if (!record) return;
    rspPendingRestoreId = id;
    const nameEl = document.getElementById('rspRestoreName');
    if (nameEl) nameEl.textContent = rspFormatName(record);
    const modal = document.getElementById('rspRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function bindRestoreModal() {
    const modal = document.getElementById('rspRestoreModal');
    const cancel = document.getElementById('rspRestoreCancelBtn');
    const confirm = document.getElementById('rspRestoreConfirmBtn');

    if (cancel) {
        cancel.addEventListener('click', function () {
            rspPendingRestoreId = null;
            if (modal) modal.style.display = 'none';
        });
    }

    if (confirm) {
        confirm.addEventListener('click', function () {
            if (rspPendingRestoreId == null) return;
            restoreRecord(rspPendingRestoreId);
            rspPendingRestoreId = null;
            if (modal) modal.style.display = 'none';
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                rspPendingRestoreId = null;
                modal.style.display = 'none';
            }
        });
    }
}

function restoreRecord(id) {
    const rejected = JSON.parse(localStorage.getItem('sports_rejected') || '[]');
    const idx = rejected.findIndex(r => r.id === id);
    if (idx === -1) return;

    const record = { ...rejected[idx] };
    rejected.splice(idx, 1);
    localStorage.setItem('sports_rejected', JSON.stringify(rejected));

    const active = JSON.parse(localStorage.getItem('sports_applications') || '[]');
    active.unshift({
        ...record,
        status: 'Pending',
        rejectionReasons: [],
        rejectionReason: '',
        rejectedDate: null,
        rejectedTime: null,
        paymentStatus: null,
    });
    localStorage.setItem('sports_applications', JSON.stringify(active));

    rspLoadRecords();
    renderStats();
    renderTable();

    const banner = document.getElementById('rspRestoreBanner');
    const bannerText = document.getElementById('rspRestoreBannerText');
    if (banner && bannerText) {
        bannerText.textContent = `${rspFormatName(record)} was restored to Sports Program Requests.`;
        banner.style.display = 'flex';
        setTimeout(() => { banner.style.display = 'none'; }, 4000);
    }

    showToast('Application restored successfully.');
}

function openViewModal(id) {
    const record = rspAllRecords.find(r => r.id === id);
    if (!record) return;

    const body = document.getElementById('rspViewModalBody');
    const modal = document.getElementById('rspViewModal');
    if (!body || !modal) return;

    body.innerHTML = `
        <div class="kk-rejection-details-section">
            <div class="kk-rejection-details-title">Rejection Details</div>
            <div class="kk-rejection-details-grid">
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejection Reason:</span>
                    <span class="kk-rejection-detail-value">${record.rejectionReason || '—'}</span>
                </div>
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejected Date:</span>
                    <span class="kk-rejection-detail-value">${record.rejectedDate || '—'}</span>
                </div>
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejected Time:</span>
                    <span class="kk-rejection-detail-value">${record.rejectedTime || '—'}</span>
                </div>
            </div>
        </div>
        <div style="margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 20px;">
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Full Name</div><div style="font-weight:600;color:#111827;">${rspFormatName(record)}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Sport</div><div style="font-weight:600;color:#111827;">${record.sport || '—'}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Division</div><div style="font-weight:600;color:#111827;">${record.division || '—'}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Date Applied</div><div style="font-weight:600;color:#111827;">${record.dateApplied || '—'}</div></div>
        </div>`;

    modal.style.display = 'flex';
}

function bindViewModal() {
    const modal = document.getElementById('rspViewModal');
    const close = document.getElementById('rspViewModalClose');
    const toggle = document.getElementById('rspViewModalToggle');
    const box = document.getElementById('rspViewModalBox');

    if (close) {
        close.addEventListener('click', function () {
            if (modal) modal.style.display = 'none';
            if (box) box.classList.remove('maximized');
        });
    }

    if (toggle && box) {
        toggle.addEventListener('click', function () {
            box.classList.toggle('maximized');
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                if (box) box.classList.remove('maximized');
            }
        });
    }
}

function showToast(msg) {
    const toast = document.getElementById('rspToast');
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

const prevBtn = document.getElementById('rejectedSportsPrevBtn');
const nextBtn = document.getElementById('rejectedSportsNextBtn');

if (prevBtn) {
    prevBtn.addEventListener('click', function () {
        if (rspCurrentPage > 1) {
            rspCurrentPage--;
            renderTable();
        }
    });
}

if (nextBtn) {
    nextBtn.addEventListener('click', function () {
        const pages = Math.ceil(rspFiltered.length / rspPerPage);
        if (rspCurrentPage < pages) {
            rspCurrentPage++;
            renderTable();
        }
    });
}
