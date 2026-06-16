// Deleted SK Officials — live data from users archive

document.addEventListener('DOMContentLoaded', function () {
    initDeletedSkOfficials();
});

function formatRecordName(record) {
    return [record.lastName, record.firstName, record.middleName, record.suffix]
        .filter((part) => part && String(part).trim() !== '')
        .join(', ');
}

const DSO_API = {
    data: '/archived/deleted-sk-officials/data',
    restore: (id) => `/archived/deleted-sk-officials/${id}/restore`,
};

let dsoRecords = [];
let dsoFiltered = [];
let dsoCurrentPage = 1;
const dsoPerPage = 10;
let dsoPendingId = null;
let dsoActiveFilter = 'all';
let dsoActiveBarangay = '';
let dsoActivePosition = '';
let dsoActiveTerm = '';
let dsoYearFilter = 'all';
let dsoIsLoading = false;

const DSO_POLL_MS = 20000;

function initDeletedSkOfficials() {
    bindDsoSearch();
    bindDsoFilterTabs();
    bindDsoDropdowns();
    bindDsoRestoreModal();
    bindDsoViewModal();
    loadDsoRecords();
    startDsoRealtimeRefresh();
}

function startDsoRealtimeRefresh() {
    window.setInterval(() => {
        if (!document.hidden) {
            loadDsoRecords();
        }
    }, DSO_POLL_MS);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            loadDsoRecords();
        }
    });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function loadDsoRecords() {
    if (dsoIsLoading) return;
    dsoIsLoading = true;
    renderDsoLoadingState();

    const params = new URLSearchParams({
        filter: dsoActiveFilter,
        year: dsoYearFilter,
        search: document.getElementById('dsoSearch')?.value?.trim() || '',
        barangay: dsoActiveBarangay,
        position: dsoActivePosition,
        term: dsoActiveTerm,
    });

    try {
        const response = await fetch(`${DSO_API.data}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok) throw new Error('Failed to load deleted records.');

        dsoRecords = (payload.data || []).map(normalizeDsoRecord);
        dsoFiltered = [...dsoRecords];
        populateDsoDropdowns(payload.filters || {});
        renderDsoStats(payload.stats || {});
        dsoCurrentPage = 1;
        renderDsoTable();
    } catch (error) {
        dsoRecords = [];
        dsoFiltered = [];
        renderDsoStats({ total: 0, today: 0, month: 0 });
        renderDsoErrorState(error.message || 'Unable to load deleted SK Officials records.');
    } finally {
        dsoIsLoading = false;
    }
}

function normalizeDsoRecord(record) {
    return {
        ...record,
        _deletedTs: record.deleted_at ? new Date(record.deleted_at) : null,
    };
}

function renderDsoLoadingState() {
    const tbody = document.getElementById('dsoTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr class="dso-empty-row"><td colspan="8">Loading deleted records...</td></tr>';
    }
}

function renderDsoErrorState(message) {
    const tbody = document.getElementById('dsoTableBody');
    const info = document.getElementById('dsoPaginationInfo');
    if (tbody) tbody.innerHTML = `<tr class="dso-empty-row"><td colspan="8">${message}</td></tr>`;
    if (info) info.textContent = 'No records found';
    renderDsoPagination(0);
}

function renderDsoStats(stats) {
    const row = document.getElementById('dsoStatsRow');
    if (!row) return;

    row.innerHTML = `
        <div class="dso-stat-card dso-stat-card-red">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${stats.total ?? 0}</span>
                <div class="dso-stat-icon dso-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                </div>
            </div>
            <span class="dso-stat-label">Total Deleted</span>
        </div>
        <div class="dso-stat-card dso-stat-card-orange">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${stats.month ?? 0}</span>
                <div class="dso-stat-icon dso-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>
            <span class="dso-stat-label">This Month</span>
        </div>
        <div class="dso-stat-card dso-stat-card-blue">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${stats.today ?? 0}</span>
                <div class="dso-stat-icon dso-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="dso-stat-label">Today</span>
        </div>`;
}

function populateDsoDropdowns(filters) {
    fillSelectOptions('dsoFilterBarangay', 'All Barangays', filters.barangays || [], dsoActiveBarangay);
    fillSelectOptions('dsoFilterPosition', 'All Positions', filters.positions || [], dsoActivePosition);
    fillSelectOptions('dsoFilterTerm', 'All Terms', filters.terms || [], dsoActiveTerm);
    fillSelectOptions('dsoYearFilter', 'All Years', (filters.years || []).map(String), dsoYearFilter === 'all' ? '' : dsoYearFilter, true);
}

function fillSelectOptions(selectId, defaultLabel, values, selectedValue, isYear = false) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const current = selectedValue || (isYear ? dsoYearFilter : '') || '';
    select.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = isYear ? 'all' : '';
    defaultOpt.textContent = defaultLabel;
    if ((isYear && dsoYearFilter === 'all') || (!isYear && !current)) {
        defaultOpt.selected = true;
    }
    select.appendChild(defaultOpt);

    values.forEach((value) => {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = value;
        if (String(current) === String(value)) opt.selected = true;
        select.appendChild(opt);
    });
}

function bindDsoDropdowns() {
    document.getElementById('dsoFilterBarangay')?.addEventListener('change', function () {
        dsoActiveBarangay = this.value;
        loadDsoRecords();
    });
    document.getElementById('dsoFilterPosition')?.addEventListener('change', function () {
        dsoActivePosition = this.value;
        loadDsoRecords();
    });
    document.getElementById('dsoFilterTerm')?.addEventListener('change', function () {
        dsoActiveTerm = this.value;
        loadDsoRecords();
    });
}

function bindDsoFilterTabs() {
    document.querySelectorAll('.dso-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dso-tab').forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            dsoActiveFilter = this.dataset.filter || 'all';
            dsoCurrentPage = 1;
            loadDsoRecords();
        });
    });
}

function renderDsoTable() {
    const tbody = document.getElementById('dsoTableBody');
    const info = document.getElementById('dsoPaginationInfo');
    if (!tbody) return;

    dsoFiltered = [...dsoRecords];
    const start = (dsoCurrentPage - 1) * dsoPerPage;
    const end = start + dsoPerPage;
    const page = dsoFiltered.slice(start, end);

    if (dsoFiltered.length === 0) {
        tbody.innerHTML = '<tr class="dso-empty-row"><td colspan="8">No deleted SK Officials records found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderDsoPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r) => {
        const fullName = formatRecordName(r);
        return `
        <tr>
            <td class="dso-name-cell">${fullName}</td>
            <td>${r.position || '—'}</td>
            <td>${r.barangay || '—'}</td>
            <td>${r.municipality || '—'}</td>
            <td>${r.term || '—'}</td>
            <td><span class="dso-deleted-badge">${r.deletedDate}</span></td>
            <td><span class="dso-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="dso-action-btns">
                    <button type="button" class="dso-btn-view" data-id="${r.id}">View</button>
                    <button type="button" class="dso-btn-restore" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, dsoFiltered.length)} of ${dsoFiltered.length} records`;
    renderDsoPagination(dsoFiltered.length);

    tbody.querySelectorAll('.dso-btn-restore').forEach((btn) => {
        btn.addEventListener('click', () => openDsoRestoreModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.dso-btn-view').forEach((btn) => {
        btn.addEventListener('click', () => openDsoViewModal(btn.dataset.id));
    });
}

function renderDsoPagination(total) {
    const pages = Math.ceil(total / dsoPerPage) || 1;
    const nums = document.getElementById('dsoPageNumbers');
    const prev = document.getElementById('dsoPrevBtn');
    const next = document.getElementById('dsoNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button type="button" class="dso-page-btn ${i + 1 === dsoCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.dso-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dsoCurrentPage = i + 1; renderDsoTable(); });
        });
    }
    if (prev) {
        prev.disabled = dsoCurrentPage === 1;
        prev.onclick = () => { if (dsoCurrentPage > 1) { dsoCurrentPage--; renderDsoTable(); } };
    }
    if (next) {
        next.disabled = dsoCurrentPage >= pages || total === 0;
        next.onclick = () => { if (dsoCurrentPage < pages) { dsoCurrentPage++; renderDsoTable(); } };
    }
}

function bindDsoSearch() {
    let timer = null;
    document.getElementById('dsoSearch')?.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            dsoCurrentPage = 1;
            loadDsoRecords();
        }, 300);
    });
    document.getElementById('dsoYearFilter')?.addEventListener('change', function () {
        dsoYearFilter = this.value || 'all';
        dsoCurrentPage = 1;
        loadDsoRecords();
    });
}

function openDsoViewModal(id) {
    const r = dsoRecords.find((x) => String(x.id) === String(id));
    if (!r) return;

    const body = document.getElementById('dsoViewBody');
    if (!body) return;

    const statusBadge = (val) => {
        const color = val === 'ACTIVE' ? 'dso-badge-green' : 'dso-badge-gray';
        return `<span class="dso-badge ${color}">${val || '—'}</span>`;
    };

    body.innerHTML = `
        <div class="dso-view-section-block">
            <div class="dso-view-section-header">
                <span class="dso-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                </span>
                <span class="dso-view-section-label">Personal Information</span>
            </div>
            <div class="dso-view-info-grid">
                <div class="dso-view-field"><span class="dso-view-label">Full Name</span><span class="dso-view-value dso-view-fullname">${r.firstName} ${r.middleName || ''} ${r.lastName}${r.suffix ? ' ' + r.suffix : ''}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Email Address</span><span class="dso-view-value">${r.email || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Sex</span><span class="dso-view-value">${r.sex || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Date of Birth</span><span class="dso-view-value">${r.dateOfBirth || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Age</span><span class="dso-view-value">${r.age ?? '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Contact Number</span><span class="dso-view-value">${r.contactNumber || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Email Verification</span><span class="dso-view-value">${r.emailVerification || '—'}</span></div>
            </div>
        </div>
        <div class="dso-view-section-block">
            <div class="dso-view-section-header">
                <span class="dso-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <span class="dso-view-section-label">Location Information</span>
            </div>
            <div class="dso-view-info-grid">
                <div class="dso-view-field"><span class="dso-view-label">Barangay</span><span class="dso-view-value">${r.barangay || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Municipality</span><span class="dso-view-value">${r.municipality || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Province</span><span class="dso-view-value">${r.province || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Region</span><span class="dso-view-value">${r.region || '—'}</span></div>
            </div>
        </div>
        <div class="dso-view-section-block">
            <div class="dso-view-section-header">
                <span class="dso-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </span>
                <span class="dso-view-section-label">Term Information</span>
            </div>
            <div class="dso-view-info-grid">
                <div class="dso-view-field"><span class="dso-view-label">Position</span><span class="dso-view-value">${r.position || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Term Start</span><span class="dso-view-value">${r.termStart || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Term End</span><span class="dso-view-value">${r.termEnd || '—'}</span></div>
                <div class="dso-view-field"><span class="dso-view-label">Account Status</span>${statusBadge(r.accountStatus)}</div>
                <div class="dso-view-field"><span class="dso-view-label">Term Status</span>${statusBadge(r.termStatus)}</div>
                <div class="dso-view-field"><span class="dso-view-label">Date Deleted</span><span class="dso-view-value">${r.deletedDate || '—'} ${r.deletedTime || ''}</span></div>
            </div>
        </div>`;

    const modal = document.getElementById('dsoViewModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('dso-modal-open');
    }
}

function bindDsoViewModal() {
    const modal = document.getElementById('dsoViewModal');
    const box = document.getElementById('dsoViewModalBox');
    const closeBtn = document.getElementById('dsoViewClose');
    const closeFooterBtn = document.getElementById('dsoViewCloseFooter');
    const toggleBtn = document.getElementById('dsoViewToggle');
    const toggleIcon = document.getElementById('dsoViewToggleIcon');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('dso-maximized');
        }
        if (box) box.classList.remove('dso-maximized');
        if (toggleBtn) toggleBtn.title = 'Maximize';
        if (toggleIcon) toggleIcon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
        document.body.classList.remove('dso-modal-open');
    };

    closeBtn?.addEventListener('click', close);
    closeFooterBtn?.addEventListener('click', close);
    modal?.addEventListener('click', (e) => { if (e.target === modal) close(); });

    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const isMax = !box?.classList.contains('dso-maximized');
        modal?.classList.toggle('dso-maximized', isMax);
        box?.classList.toggle('dso-maximized', isMax);
        toggleBtn.title = isMax ? 'Restore Down' : 'Maximize';
        if (toggleIcon) {
            toggleIcon.innerHTML = isMax
                ? '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>'
                : '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
        }
    });
}

function openDsoRestoreModal(id) {
    const record = dsoRecords.find((r) => String(r.id) === String(id));
    if (!record) return;
    dsoPendingId = id;
    const nameEl = document.getElementById('dsoRestoreName');
    if (nameEl) nameEl.textContent = formatRecordName(record);
    const modal = document.getElementById('dsoRestoreModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('dso-modal-open');
    }
}

function closeDsoRestoreModal() {
    dsoPendingId = null;
    const modal = document.getElementById('dsoRestoreModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('dso-modal-open');
}

function bindDsoRestoreModal() {
    const cancelBtn = document.getElementById('dsoRestoreCancelBtn');
    const confirmBtn = document.getElementById('dsoRestoreConfirmBtn');
    const modal = document.getElementById('dsoRestoreModal');

    cancelBtn?.addEventListener('click', closeDsoRestoreModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeDsoRestoreModal(); });

    confirmBtn?.addEventListener('click', async function () {
        if (!dsoPendingId) return;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Restoring...';

        try {
            const response = await fetch(DSO_API.restore(dsoPendingId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to restore account.');
            }
            closeDsoRestoreModal();
            showDsoToast(data.message || 'Account restored successfully.');
            await loadDsoRecords();
        } catch (error) {
            alert(error.message || 'Failed to restore account. Please try again.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Restore';
        }
    });
}

function showDsoToast(message) {
    const existing = document.getElementById('dso-toast');
    if (existing) {
        clearTimeout(existing._timer);
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'dso-toast';
    toast.setAttribute('role', 'status');
    toast.textContent = message;
    Object.assign(toast.style, {
        position: 'fixed',
        top: '72px',
        left: '50%',
        transform: 'translateX(-50%)',
        zIndex: '100000',
        padding: '11px 28px',
        borderRadius: '8px',
        fontSize: '0.875rem',
        fontWeight: '600',
        color: '#fff',
        background: '#16a34a',
        boxShadow: '0 4px 18px rgba(0,0,0,.18)',
    });
    document.body.appendChild(toast);
    toast._timer = setTimeout(() => toast.remove(), 3000);
}
