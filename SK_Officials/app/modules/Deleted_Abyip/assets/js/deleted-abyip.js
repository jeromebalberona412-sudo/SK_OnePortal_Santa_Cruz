// Deleted ABYIP Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedAbyip();
});

const deletedAbyipRecords = [
    {
        id: 'da-001',
        title: 'ABYIP CY 2024',
        dateCreated: 'Jan 15, 2025',
        timeCreated: '10:30 AM',
        status: 'Draft',
        remarks: 'Superseded by 2025 version',
        deletedDate: 'Apr 20, 2026',
        deletedTime: '11:15 AM',
        _deletedTs: new Date('2026-04-20T11:15:00'),
    },
    {
        id: 'da-002',
        title: 'ABYIP CY 2023',
        dateCreated: 'Feb 02, 2024',
        timeCreated: '02:15 PM',
        status: 'Approved',
        remarks: 'Archived after audit',
        deletedDate: 'Apr 01, 2026',
        deletedTime: '03:45 PM',
        _deletedTs: new Date('2026-04-01T15:45:00'),
    },
];

let daFiltered = [...deletedAbyipRecords];
let daCurrentPage = 1;
const daPerPage = 10;
let daPendingRestoreId = null;
let daActiveFilter = 'all';

function daNow() { return new Date('2026-04-20T12:00:00'); }

function daIsToday(ts) {
    const n = daNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function daIsThisWeek(ts) {
    const n = daNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function daIsThisMonth(ts) {
    const n = daNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function daApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => daIsToday(r._deletedTs));
    if (filter === 'week')  return records.filter(r => daIsThisWeek(r._deletedTs));
    if (filter === 'month') return records.filter(r => daIsThisMonth(r._deletedTs));
    return records;
}

function initDeletedAbyip() {
    renderStats();
    renderTable();
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
}

function renderStats() {
    const row = document.getElementById('daStatsRow');
    if (!row) return;
    const total = deletedAbyipRecords.length;
    const month = deletedAbyipRecords.filter(r => daIsThisMonth(r._deletedTs)).length;
    const today = deletedAbyipRecords.filter(r => daIsToday(r._deletedTs)).length;

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

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            daActiveFilter = this.dataset.filter;
            const labels = { all: 'All Deleted Records', today: 'Deleted Today', week: 'Deleted This Week', month: 'Deleted This Month' };
            const label = document.getElementById('daSectionLabel');
            if (label) label.textContent = labels[daActiveFilter] || 'Deleted Records';
            daFiltered = daApplyFilter(deletedAbyipRecords, daActiveFilter);
            daCurrentPage = 1;
            renderTable();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('deletedAbyipTableBody');
    const info  = document.getElementById('deletedAbyipPaginationInfo');
    if (!tbody) return;

    const start = (daCurrentPage - 1) * daPerPage;
    const end   = start + daPerPage;
    const page  = daFiltered.slice(start, end);

    if (daFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="8">No deleted ABYIP records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => `
        <tr>
            <td style="font-weight:600;color:#111827;text-align:left;">${r.title}</td>
            <td>${r.dateCreated}</td>
            <td>${r.timeCreated}</td>
            <td><span class="status-pill ${r.status.toLowerCase()}">${r.status}</span></td>
            <td style="text-align:left;font-size:12px;color:#6b7280;">${r.remarks || '—'}</td>
            <td><span class="deleted-at-badge">${r.deletedDate}</span></td>
            <td><span class="deleted-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    <button class="btn-restore-action" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>
    `).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, daFiltered.length)} of ${daFiltered.length} records`;

    renderPagination(daFiltered.length);

    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / daPerPage);
    const nums  = document.getElementById('deletedAbyipPageNumbers');
    const prev  = document.getElementById('deletedAbyipPrevBtn');
    const next  = document.getElementById('deletedAbyipNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === daCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { daCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = daCurrentPage === 1; prev.onclick = () => { daCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = daCurrentPage >= pages || pages === 0; next.onclick = () => { daCurrentPage++; renderTable(); }; }
}

function bindSearch() {
    const input = document.getElementById('deletedAbyipSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = daApplyFilter(deletedAbyipRecords, daActiveFilter);
        daFiltered = base.filter(r =>
            r.title.toLowerCase().includes(q) ||
            (r.remarks || '').toLowerCase().includes(q)
        );
        daCurrentPage = 1;
        renderTable();
    });
}

function openViewModal(id) {
    const r = deletedAbyipRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('daViewModalBody');
    if (body) {
        body.innerHTML = `
            <div class="view-modal-grid">
                <div class="view-modal-column">
                    <div class="view-section-card">
                        <h3 class="view-section-title">Document Information</h3>
                        <div class="view-fullname">${r.title}</div>
                        <div class="view-field-group">
                            <div class="view-field"><span class="view-field-label">Date Created</span><span class="view-field-value">${r.dateCreated}</span></div>
                            <div class="view-field"><span class="view-field-label">Time Created</span><span class="view-field-value">${r.timeCreated}</span></div>
                        </div>
                        <div class="view-field"><span class="view-field-label">Status</span><span class="view-badge view-badge-${r.status.toLowerCase()}">${r.status}</span></div>
                    </div>
                </div>
                <div class="view-modal-column">
                    <div class="view-section-card">
                        <h3 class="view-section-title">Additional Details</h3>
                        <div class="view-field"><span class="view-field-label">Remarks</span><span class="view-field-value">${r.remarks || '—'}</span></div>
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
    const modal = document.getElementById('daViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal    = document.getElementById('daViewModal');
    const box      = document.getElementById('daViewModalBox');
    const closeBtn = document.getElementById('daViewModalClose');
    const toggleBtn = document.getElementById('daViewModalToggle');

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
    const record = deletedAbyipRecords.find(r => r.id === id);
    if (!record) return;
    daPendingRestoreId = id;
    const nameEl = document.getElementById('daRestoreName');
    if (nameEl) nameEl.textContent = record.title;
    const modal = document.getElementById('daRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    daPendingRestoreId = null;
    const modal = document.getElementById('daRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('daRestoreCancelBtn');
    const confirmBtn = document.getElementById('daRestoreConfirmBtn');
    const modal      = document.getElementById('daRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!daPendingRestoreId) return;
            const record = deletedAbyipRecords.find(r => r.id === daPendingRestoreId);
            const title = record ? record.title : 'Record';
            const idx = deletedAbyipRecords.findIndex(r => r.id === daPendingRestoreId);
            if (idx !== -1) deletedAbyipRecords.splice(idx, 1);
            daFiltered = daApplyFilter(deletedAbyipRecords, daActiveFilter);
            closeRestoreModal();
            daCurrentPage = 1;
            renderStats();
            renderTable();
            showRestoreBanner('daRestoreBanner', 'daRestoreBannerText', `"${title}" has been restored to the ABYIP list.`);
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
