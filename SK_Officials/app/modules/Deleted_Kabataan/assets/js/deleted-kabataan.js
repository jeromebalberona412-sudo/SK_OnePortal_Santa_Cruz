// Deleted Kabataan Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedKabataan();
});

const deletedKabataanRecords = [
    {
        id: 'dk-001',
        respondentNumber: '009',
        firstName: 'Ramon',
        middleName: 'Jose',
        lastName: 'Villanueva',
        suffix: '',
        sex: 'Male',
        age: 20,
        barangay: 'DAMAYAN',
        purokZone: 'Zone 5',
        educationalBackground: 'College Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09187654321',
        deletedDate: 'Apr 20, 2026',
        deletedTime: '09:45 AM',
        _deletedTs: new Date('2026-04-20T09:45:00'),
    },
    {
        id: 'dk-002',
        respondentNumber: '010',
        firstName: 'Liza',
        middleName: 'Mae',
        lastName: 'Santos',
        suffix: '',
        sex: 'Female',
        age: 17,
        barangay: 'IMELDA',
        purokZone: 'Zone 3',
        educationalBackground: 'High School Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09198765432',
        deletedDate: 'Apr 12, 2026',
        deletedTime: '02:30 PM',
        _deletedTs: new Date('2026-04-12T14:30:00'),
    },
];

let dkFiltered = [...deletedKabataanRecords];
let dkCurrentPage = 1;
const dkPerPage = 10;
let dkPendingRestoreId = null;
let dkActiveFilter = 'all';

// ── Date helpers ──────────────────────────────────────────────────────────────
function dkNow() { return new Date('2026-04-20T12:00:00'); } // simulated "now"

function dkIsToday(ts) {
    const n = dkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function dkIsThisWeek(ts) {
    const n = dkNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function dkIsThisMonth(ts) {
    const n = dkNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function dkApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => dkIsToday(r._deletedTs));
    if (filter === 'week')  return records.filter(r => dkIsThisWeek(r._deletedTs));
    if (filter === 'month') return records.filter(r => dkIsThisMonth(r._deletedTs));
    return records;
}

function initDeletedKabataan() {
    renderStats();
    renderTable();
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
}

// ── Stats cards ───────────────────────────────────────────────────────────────
function renderStats() {
    const row = document.getElementById('dkStatsRow');
    if (!row) return;
    const total   = deletedKabataanRecords.length;
    const month   = deletedKabataanRecords.filter(r => dkIsThisMonth(r._deletedTs)).length;
    const today   = deletedKabataanRecords.filter(r => dkIsToday(r._deletedTs)).length;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Deleted</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
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

// ── Filter tabs ───────────────────────────────────────────────────────────────
function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            dkActiveFilter = this.dataset.filter;
            const labels = { all: 'All Deleted Records', today: 'Deleted Today', week: 'Deleted This Week', month: 'Deleted This Month' };
            const label = document.getElementById('dkSectionLabel');
            if (label) label.textContent = labels[dkActiveFilter] || 'Deleted Records';
            dkFiltered = dkApplyFilter(deletedKabataanRecords, dkActiveFilter);
            dkCurrentPage = 1;
            renderTable();
        });
    });
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('deletedKabataanTableBody');
    const info  = document.getElementById('deletedKabataanPaginationInfo');
    if (!tbody) return;

    const start = (dkCurrentPage - 1) * dkPerPage;
    const end   = start + dkPerPage;
    const page  = dkFiltered.slice(start, end);

    if (dkFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No deleted Kabataan records found.</td></tr>`;
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
            <td>${r.barangay || '—'}</td>
            <td>${r.educationalBackground || '—'}</td>
            <td><span class="deleted-at-badge">${r.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, dkFiltered.length)} of ${dkFiltered.length} records`;

    renderPagination(dkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / dkPerPage);
    const nums  = document.getElementById('deletedKabataanPageNumbers');
    const prev  = document.getElementById('deletedKabataanPrevBtn');
    const next  = document.getElementById('deletedKabataanNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === dkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dkCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = dkCurrentPage === 1; prev.onclick = () => { dkCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = dkCurrentPage >= pages || pages === 0; next.onclick = () => { dkCurrentPage++; renderTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('deletedKabataanSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = dkApplyFilter(deletedKabataanRecords, dkActiveFilter);
        dkFiltered = base.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.barangay || '').toLowerCase().includes(q)
        );
        dkCurrentPage = 1;
        renderTable();
    });
}

// ── View modal ────────────────────────────────────────────────────────────────
function openViewModal(id) {
    const r = deletedKabataanRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('dkViewModalBody');
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
                        <div class="view-field"><span class="view-field-label">Civil Status</span><span class="view-field-value">${r.civilStatus || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Purok / Zone</span><span class="view-field-value">${r.purokZone || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Barangay</span><span class="view-field-value">${r.barangay || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Contact Number</span><span class="view-field-value">${r.contactNumber || '—'}</span></div>
                    </div>
                </div>
                <div class="view-modal-column">
                    <div class="view-section-card">
                        <h3 class="view-section-title">Classification & Status</h3>
                        <div class="view-field"><span class="view-field-label">Youth Classification</span><span class="view-badge view-badge-blue">${r.youthClassification || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Work Status</span><span class="view-badge view-badge-green">${r.workStatus || '—'}</span></div>
                        <div class="view-field"><span class="view-field-label">Education</span><span class="view-field-value">${r.educationalBackground || '—'}</span></div>
                    </div>
                </div>
            </div>
            <div class="view-deletion-section">
                <h3 class="view-section-title view-section-title-danger">Deletion Information</h3>
                <div class="view-field-group-inline">
                    <div class="view-field"><span class="view-field-label">Deleted Date</span><span class="view-field-value-danger">${r.deletedDate}</span></div>
                    <div class="view-field"><span class="view-field-label">Deleted Time</span><span class="view-field-value-danger">${r.deletedTime}</span></div>
                </div>
            </div>`;
    }
    const modal = document.getElementById('dkViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('dkViewModal');
    const box      = document.getElementById('dkViewModalBox');
    const closeBtn = document.getElementById('dkViewModalClose');
    const toggleBtn = document.getElementById('dkViewModalToggle');

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

// ── Restore modal ─────────────────────────────────────────────────────────────
function openRestoreModal(id) {
    const record = deletedKabataanRecords.find(r => r.id === id);
    if (!record) return;
    dkPendingRestoreId = id;
    const nameEl = document.getElementById('dkRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    dkPendingRestoreId = null;
    const modal = document.getElementById('dkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('dkRestoreCancelBtn');
    const confirmBtn = document.getElementById('dkRestoreConfirmBtn');
    const modal      = document.getElementById('dkRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!dkPendingRestoreId) return;
            const record = deletedKabataanRecords.find(r => r.id === dkPendingRestoreId);
            const name = record ? `${record.lastName}, ${record.firstName}` : 'Record';
            const idx = deletedKabataanRecords.findIndex(r => r.id === dkPendingRestoreId);
            if (idx !== -1) deletedKabataanRecords.splice(idx, 1);
            dkFiltered = dkApplyFilter(deletedKabataanRecords, dkActiveFilter);
            closeRestoreModal();
            dkCurrentPage = 1;
            renderStats();
            renderTable();
            showRestoreBanner('dkRestoreBanner', 'dkRestoreBannerText', `${name} has been restored to the Kabataan list.`);
        });
    }
}

// ── Restore success banner ────────────────────────────────────────────────────
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

// ── Toast (kept for compatibility) ───────────────────────────────────────────
function showToast(toastId, message) {
    const toast = document.getElementById(toastId);
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
