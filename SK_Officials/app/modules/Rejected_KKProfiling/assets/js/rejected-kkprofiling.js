// Rejected KK Profiling Module

document.addEventListener('DOMContentLoaded', function () {
    initRejectedKK();
});

const rejectedKKRecords = [
    {
        id: 'rkk-001',
        respondentNumber: '011',
        firstName: 'Benito',
        middleName: 'Cruz',
        lastName: 'Aquino',
        suffix: 'Jr.',
        sex: 'Male',
        age: 22,
        purokZone: 'Zone 2',
        barangay: 'VILLA GRACIA',
        youthClassification: 'Out of School Youth',
        workStatus: 'Unemployed',
        educationalBackground: 'High School Graduate',
        registeredSKVoter: 'No',
        rejectionReason: 'Incomplete requirements submitted',
        rejectedDate: 'Apr 20, 2026',
        rejectedTime: '10:20 AM',
        _rejectedTs: new Date('2026-04-20T10:20:00'),
    },
    {
        id: 'rkk-002',
        respondentNumber: '012',
        firstName: 'Carla',
        middleName: 'Reyes',
        lastName: 'Bautista',
        suffix: '',
        sex: 'Female',
        age: 19,
        purokZone: 'Zone 4',
        barangay: 'LUPANG PANGAKO',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        educationalBackground: 'College Level',
        registeredSKVoter: 'Yes',
        rejectionReason: 'Age does not meet eligibility criteria',
        rejectedDate: 'Apr 08, 2026',
        rejectedTime: '01:55 PM',
        _rejectedTs: new Date('2026-04-08T13:55:00'),
    },
    {
        id: 'rkk-003',
        respondentNumber: '013',
        firstName: 'Dante',
        middleName: '',
        lastName: 'Flores',
        suffix: '',
        sex: 'Male',
        age: 25,
        purokZone: 'Zone 7',
        barangay: 'BIGAYANVILLA ROSA',
        youthClassification: 'Working Youth',
        workStatus: 'Employed',
        educationalBackground: 'College Graduate',
        registeredSKVoter: 'Yes',
        rejectionReason: 'Duplicate submission detected',
        rejectedDate: 'Apr 14, 2026',
        rejectedTime: '04:10 PM',
        _rejectedTs: new Date('2026-04-14T16:10:00'),
    },
];

let rkkFiltered = [...rejectedKKRecords];
let rkkCurrentPage = 1;
const rkkPerPage = 10;
let rkkPendingRestoreId = null;
let rkkActiveFilter = 'all';

function rkkNow() { return new Date('2026-04-20T12:00:00'); }

function rkkIsToday(ts) {
    const n = rkkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function rkkIsThisWeek(ts) {
    const n = rkkNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function rkkIsThisMonth(ts) {
    const n = rkkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function rkkApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => rkkIsToday(r._rejectedTs));
    if (filter === 'week')  return records.filter(r => rkkIsThisWeek(r._rejectedTs));
    if (filter === 'month') return records.filter(r => rkkIsThisMonth(r._rejectedTs));
    return records;
}

function initRejectedKK() {
    renderStats();
    renderTable();
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
}

function renderStats() {
    const row = document.getElementById('rkkStatsRow');
    if (!row) return;
    const total = rejectedKKRecords.length;
    const month = rejectedKKRecords.filter(r => rkkIsThisMonth(r._rejectedTs)).length;
    const today = rejectedKKRecords.filter(r => rkkIsToday(r._rejectedTs)).length;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-body">
                <div class="stat-card-label">Total Rejected</div>
                <div class="stat-card-value">${total}</div>
            </div>
            <div class="stat-card-icon stat-icon-red">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            </div>
        </div>
        <div class="stat-card stat-card-yellow">
            <div class="stat-card-body">
                <div class="stat-card-label">This Month</div>
                <div class="stat-card-value">${month}</div>
            </div>
            <div class="stat-card-icon stat-icon-yellow">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
            </div>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-body">
                <div class="stat-card-label">Today</div>
                <div class="stat-card-value">${today}</div>
            </div>
            <div class="stat-card-icon stat-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>`;
}

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            rkkActiveFilter = this.dataset.filter;
            const labels = { all: 'All Rejected Records', today: 'Rejected Today', week: 'Rejected This Week', month: 'Rejected This Month' };
            const label = document.getElementById('rkkSectionLabel');
            if (label) label.textContent = labels[rkkActiveFilter] || 'Rejected Records';
            rkkFiltered = rkkApplyFilter(rejectedKKRecords, rkkActiveFilter);
            rkkCurrentPage = 1;
            renderTable();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedKKTableBody');
    const info  = document.getElementById('rejectedKKPaginationInfo');
    if (!tbody) return;

    const start = (rkkCurrentPage - 1) * rkkPerPage;
    const end   = start + rkkPerPage;
    const page  = rkkFiltered.slice(start, end);

    if (rkkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="9">No rejected KK Profiling records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return `
        <tr>
            <td style="font-weight:600;color:#111827;">${fullName}</td>
            <td>${r.age || '—'}</td>
            <td>${r.sex || '—'}</td>
            <td>${r.purokZone || '—'}</td>
            <td>${r.youthClassification || '—'}</td>
            <td><span class="rejection-reason-cell" title="${r.rejectionReason}">${r.rejectionReason}</span></td>
            <td><span class="deleted-at-badge">${r.rejectedDate}</span></td>
            <td><span class="deleted-time-badge">${r.rejectedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;

    renderPagination(rkkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / rkkPerPage);
    const nums  = document.getElementById('rejectedKKPageNumbers');
    const prev  = document.getElementById('rejectedKKPrevBtn');
    const next  = document.getElementById('rejectedKKNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === rkkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { rkkCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = rkkCurrentPage === 1; prev.onclick = () => { rkkCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = rkkCurrentPage >= pages || pages === 0; next.onclick = () => { rkkCurrentPage++; renderTable(); }; }
}

function bindSearch() {
    const input = document.getElementById('rejectedKKSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = rkkApplyFilter(rejectedKKRecords, rkkActiveFilter);
        rkkFiltered = base.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.purokZone || '').toLowerCase().includes(q) ||
            (r.rejectionReason || '').toLowerCase().includes(q)
        );
        rkkCurrentPage = 1;
        renderTable();
    });
}

function openViewModal(id) {
    const r = rejectedKKRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('rkkViewModalBody');
    if (body) {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        body.innerHTML = `
            <div class="view-modal-grid">
                <div class="view-modal-column">
                    <div class="view-section-card">
                        <h3 class="view-section-title">Personal Information</h3>
                        <div class="view-fullname">${fullName}</div>
                        <div class="view-field-group">
                            <div class="view-field"><span class="view-field-label">Age</span><span class="view-field-value">${r.age || '—'}</span></div>
                            <div class="view-field"><span class="view-field-label">Sex</span><span class="view-field-value">${r.sex || '—'}</span></div>
                        </div>
                        <div class="view-field"><span class="view-field-label">Purok / Zone</span><span class="view-field-value">${r.purokZone || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Barangay</span><span class="view-field-value">${r.barangay || '—'}</span></div>
                    </div>
                </div>
                <div class="view-modal-column">
                    <div class="view-section-card">
                        <h3 class="view-section-title">Classification & Status</h3>
                        <div class="view-field"><span class="view-field-label">Youth Classification</span><span class="view-badge view-badge-blue">${r.youthClassification || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Work Status</span><span class="view-badge view-badge-green">${r.workStatus || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Education</span><span class="view-field-value">${r.educationalBackground || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Registered SK Voter</span><span class="view-field-value">${r.registeredSKVoter || '—'}</span></div>
                    </div>
                </div>
            </div>
            <div class="view-rejection-section">
                <h3 class="view-section-title view-section-title-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="view-section-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Rejection Details
                </h3>
                <div class="view-rejection-reason">
                    <span class="view-field-label">Reason</span>
                    <p class="view-rejection-text">${r.rejectionReason || '—'}</p>
                </div>
                <div class="view-field-group-inline">
                    <div class="view-field"><span class="view-field-label">Rejected Date</span><span class="view-field-value-danger">${r.rejectedDate}</span></div>
                    <div class="view-field"><span class="view-field-label">Rejected Time</span><span class="view-field-value-danger">${r.rejectedTime}</span></div>
                </div>
            </div>`;
    }
    const modal = document.getElementById('rkkViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('rkkViewModal');
    const box      = document.getElementById('rkkViewModalBox');
    const closeBtn = document.getElementById('rkkViewModalClose');
    const toggleBtn = document.getElementById('rkkViewModalToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('view-modal-maximized'); }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('view-modal-maximized');
            modal.classList.toggle('view-modal-maximized', isMax);
            box.classList.toggle('view-modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

function openRestoreModal(id) {
    const record = rejectedKKRecords.find(r => r.id === id);
    if (!record) return;
    rkkPendingRestoreId = id;
    const nameEl = document.getElementById('rkkRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    rkkPendingRestoreId = null;
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('rkkRestoreCancelBtn');
    const confirmBtn = document.getElementById('rkkRestoreConfirmBtn');
    const modal      = document.getElementById('rkkRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!rkkPendingRestoreId) return;
            const record = rejectedKKRecords.find(r => r.id === rkkPendingRestoreId);
            const name = record ? `${record.lastName}, ${record.firstName}` : 'Record';
            const idx = rejectedKKRecords.findIndex(r => r.id === rkkPendingRestoreId);
            if (idx !== -1) rejectedKKRecords.splice(idx, 1);
            rkkFiltered = rkkApplyFilter(rejectedKKRecords, rkkActiveFilter);
            closeRestoreModal();
            rkkCurrentPage = 1;
            renderStats();
            renderTable();
            showRestoreBanner('rkkRestoreBanner', 'rkkRestoreBannerText', `${name} has been restored to KK Profiling.`);
        });
    }
}

function showRestoreBanner(bannerId, textId, message) {
    const banner = document.getElementById(bannerId);
    const text   = document.getElementById(textId);
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}

function showToast(toastId, message) {
    const toast = document.getElementById(toastId);
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
