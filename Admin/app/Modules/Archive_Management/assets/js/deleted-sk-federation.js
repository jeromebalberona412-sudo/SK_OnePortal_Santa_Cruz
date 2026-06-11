// Deleted SK Federation — live data from users archive

document.addEventListener('DOMContentLoaded', function () {
    initDeletedSkFederation();
});

function formatRecordName(record) {
    return [record.lastName, record.firstName, record.middleName, record.suffix]
        .filter((part) => part && String(part).trim() !== '')
        .join(', ');
}

const DSF_API = {
    data: '/archived/deleted-sk-federation/data',
    restore: (id) => `/archived/deleted-sk-federation/${id}/restore`,
};

let dsfRecords = [];
let dsfFiltered = [];
let dsfCurrentPage = 1;
const dsfPerPage = 10;
let dsfPendingId = null;
let dsfActiveFilter = 'all';
let dsfActiveBarangay = '';
let dsfActiveTerm = '';
let dsfYearFilter = 'all';
let dsfIsLoading = false;

const DSF_POLL_MS = 20000;

function initDeletedSkFederation() {
    bindDsfSearch();
    bindDsfFilterTabs();
    bindDsfDropdowns();
    bindDsfRestoreModal();
    bindDsfViewModal();
    loadDsfRecords();
    startDsfRealtimeRefresh();
}

function startDsfRealtimeRefresh() {
    window.setInterval(() => {
        if (!document.hidden) {
            loadDsfRecords();
        }
    }, DSF_POLL_MS);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            loadDsfRecords();
        }
    });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function loadDsfRecords() {
    if (dsfIsLoading) return;
    dsfIsLoading = true;
    renderDsfLoadingState();

    const params = new URLSearchParams({
        filter: dsfActiveFilter,
        year: dsfYearFilter,
        search: document.getElementById('dsfSearch')?.value?.trim() || '',
        barangay: dsfActiveBarangay,
        term: dsfActiveTerm,
    });

    try {
        const response = await fetch(`${DSF_API.data}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok) throw new Error('Failed to load deleted records.');

        dsfRecords = (payload.data || []).map(normalizeDsfRecord);
        dsfFiltered = [...dsfRecords];
        populateDsfDropdowns(payload.filters || {});
        renderDsfStats(payload.stats || {});
        dsfCurrentPage = 1;
        renderDsfTable();
    } catch (error) {
        dsfRecords = [];
        dsfFiltered = [];
        renderDsfStats({ total: 0, today: 0, month: 0 });
        renderDsfErrorState(error.message || 'Unable to load deleted SK Federation records.');
    } finally {
        dsfIsLoading = false;
    }
}

function normalizeDsfRecord(record) {
    return {
        ...record,
        _deletedTs: record.deleted_at ? new Date(record.deleted_at) : null,
    };
}

function renderDsfLoadingState() {
    const tbody = document.getElementById('dsfTableBody');
    if (tbody) tbody.innerHTML = '<tr class="dsf-empty-row"><td colspan="8">Loading deleted records...</td></tr>';
}

function renderDsfErrorState(message) {
    const tbody = document.getElementById('dsfTableBody');
    const info = document.getElementById('dsfPaginationInfo');
    if (tbody) tbody.innerHTML = `<tr class="dsf-empty-row"><td colspan="8">${message}</td></tr>`;
    if (info) info.textContent = 'No records found';
    renderDsfPagination(0);
}

function renderDsfStats(stats) {
    const row = document.getElementById('dsfStatsRow');
    if (!row) return;

    row.innerHTML = `
        <div class="dsf-stat-card dsf-stat-card-red">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${stats.total ?? 0}</span>
                <div class="dsf-stat-icon dsf-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path></svg>
                </div>
            </div>
            <span class="dsf-stat-label">Total Deleted</span>
        </div>
        <div class="dsf-stat-card dsf-stat-card-orange">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${stats.month ?? 0}</span>
                <div class="dsf-stat-icon dsf-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
            </div>
            <span class="dsf-stat-label">This Month</span>
        </div>
        <div class="dsf-stat-card dsf-stat-card-blue">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${stats.today ?? 0}</span>
                <div class="dsf-stat-icon dsf-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="dsf-stat-label">Today</span>
        </div>`;
}

function populateDsfDropdowns(filters) {
    fillSelectOptions('dsfFilterBarangay', 'All Barangays', filters.barangays || [], dsfActiveBarangay);
    fillSelectOptions('dsfFilterTerm', 'All Terms', filters.terms || [], dsfActiveTerm);
    fillSelectOptions('dsfYearFilter', 'All Years', (filters.years || []).map(String), dsfYearFilter === 'all' ? '' : dsfYearFilter, true);
}

function fillSelectOptions(selectId, defaultLabel, values, selectedValue, isYear = false) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const current = selectedValue || (isYear ? dsfYearFilter : '') || '';
    select.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = isYear ? 'all' : '';
    defaultOpt.textContent = defaultLabel;
    if ((isYear && dsfYearFilter === 'all') || (!isYear && !current)) {
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

function bindDsfDropdowns() {
    document.getElementById('dsfFilterBarangay')?.addEventListener('change', function () {
        dsfActiveBarangay = this.value;
        loadDsfRecords();
    });
    document.getElementById('dsfFilterTerm')?.addEventListener('change', function () {
        dsfActiveTerm = this.value;
        loadDsfRecords();
    });
}

function bindDsfFilterTabs() {
    document.querySelectorAll('.dsf-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dsf-tab').forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            dsfActiveFilter = this.dataset.filter || 'all';
            dsfCurrentPage = 1;
            loadDsfRecords();
        });
    });
}

function renderDsfTable() {
    const tbody = document.getElementById('dsfTableBody');
    const info = document.getElementById('dsfPaginationInfo');
    if (!tbody) return;

    dsfFiltered = [...dsfRecords];
    const start = (dsfCurrentPage - 1) * dsfPerPage;
    const end = start + dsfPerPage;
    const page = dsfFiltered.slice(start, end);

    if (dsfFiltered.length === 0) {
        tbody.innerHTML = '<tr class="dsf-empty-row"><td colspan="8">No deleted SK Federation records found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderDsfPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r) => {
        const fullName = formatRecordName(r);
        const statusClass = r.accountStatus === 'ACTIVE' ? 'dsf-badge-green' : 'dsf-badge-gray';
        return `
        <tr>
            <td class="dsf-name-cell">${fullName}</td>
            <td>${r.position || '—'}</td>
            <td>${r.barangay || '—'}</td>
            <td>${r.municipality || '—'}</td>
            <td><span class="dsf-badge ${statusClass}">${r.accountStatus || '—'}</span></td>
            <td><span class="dsf-deleted-badge">${r.deletedDate}</span></td>
            <td><span class="dsf-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="dsf-action-btns">
                    <button type="button" class="dsf-btn-view" data-id="${r.id}">View</button>
                    <button type="button" class="dsf-btn-restore" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, dsfFiltered.length)} of ${dsfFiltered.length} records`;
    renderDsfPagination(dsfFiltered.length);

    tbody.querySelectorAll('.dsf-btn-restore').forEach((btn) => {
        btn.addEventListener('click', () => openDsfRestoreModal(btn.dataset.id));
    });
    tbody.querySelectorAll('.dsf-btn-view').forEach((btn) => {
        btn.addEventListener('click', () => openDsfViewModal(btn.dataset.id));
    });
}

function renderDsfPagination(total) {
    const pages = Math.ceil(total / dsfPerPage) || 1;
    const nums = document.getElementById('dsfPageNumbers');
    const prev = document.getElementById('dsfPrevBtn');
    const next = document.getElementById('dsfNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button type="button" class="dsf-page-btn ${i + 1 === dsfCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.dsf-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dsfCurrentPage = i + 1; renderDsfTable(); });
        });
    }
    if (prev) {
        prev.disabled = dsfCurrentPage === 1;
        prev.onclick = () => { if (dsfCurrentPage > 1) { dsfCurrentPage--; renderDsfTable(); } };
    }
    if (next) {
        next.disabled = dsfCurrentPage >= pages || total === 0;
        next.onclick = () => { if (dsfCurrentPage < pages) { dsfCurrentPage++; renderDsfTable(); } };
    }
}

function bindDsfSearch() {
    let timer = null;
    document.getElementById('dsfSearch')?.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            dsfCurrentPage = 1;
            loadDsfRecords();
        }, 300);
    });
    document.getElementById('dsfYearFilter')?.addEventListener('change', function () {
        dsfYearFilter = this.value || 'all';
        dsfCurrentPage = 1;
        loadDsfRecords();
    });
}

function openDsfViewModal(id) {
    const r = dsfRecords.find((x) => String(x.id) === String(id));
    if (!r) return;

    const body = document.getElementById('dsfViewBody');
    if (!body) return;

    const statusBadge = (val) => {
        const color = val === 'ACTIVE' ? 'dsf-badge-green' : 'dsf-badge-gray';
        return `<span class="dsf-badge ${color}">${val || '—'}</span>`;
    };

    body.innerHTML = `
        <div class="dsf-view-section-block">
            <div class="dsf-view-section-header">
                <span class="dsf-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                </span>
                <span class="dsf-view-section-label">Personal Information</span>
            </div>
            <div class="dsf-view-info-grid">
                <div class="dsf-view-field"><span class="dsf-view-label">Full Name</span><span class="dsf-view-value dsf-view-fullname">${r.firstName} ${r.middleName || ''} ${r.lastName}${r.suffix ? ' ' + r.suffix : ''}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Email Address</span><span class="dsf-view-value">${r.email || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Sex</span><span class="dsf-view-value">${r.sex || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Date of Birth</span><span class="dsf-view-value">${r.dateOfBirth || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Age</span><span class="dsf-view-value">${r.age ?? '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Contact Number</span><span class="dsf-view-value">${r.contactNumber || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Email Verification</span><span class="dsf-view-value">${r.emailVerification || '—'}</span></div>
            </div>
        </div>
        <div class="dsf-view-section-block">
            <div class="dsf-view-section-header">
                <span class="dsf-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
                <span class="dsf-view-section-label">Location Information</span>
            </div>
            <div class="dsf-view-info-grid">
                <div class="dsf-view-field"><span class="dsf-view-label">Barangay</span><span class="dsf-view-value">${r.barangay || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Municipality</span><span class="dsf-view-value">${r.municipality || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Province</span><span class="dsf-view-value">${r.province || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Region</span><span class="dsf-view-value">${r.region || '—'}</span></div>
            </div>
        </div>
        <div class="dsf-view-section-block">
            <div class="dsf-view-section-header">
                <span class="dsf-view-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </span>
                <span class="dsf-view-section-label">Term Information</span>
            </div>
            <div class="dsf-view-info-grid">
                <div class="dsf-view-field"><span class="dsf-view-label">Position</span><span class="dsf-view-value">${r.position || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Term Start</span><span class="dsf-view-value">${r.termStart || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Term End</span><span class="dsf-view-value">${r.termEnd || '—'}</span></div>
                <div class="dsf-view-field"><span class="dsf-view-label">Account Status</span>${statusBadge(r.accountStatus)}</div>
                <div class="dsf-view-field"><span class="dsf-view-label">Term Status</span>${statusBadge(r.termStatus)}</div>
                <div class="dsf-view-field"><span class="dsf-view-label">Date Deleted</span><span class="dsf-view-value">${r.deletedDate || '—'} ${r.deletedTime || ''}</span></div>
            </div>
        </div>`;

    const modal = document.getElementById('dsfViewModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('dsf-modal-open');
    }
}

function bindDsfViewModal() {
    const modal = document.getElementById('dsfViewModal');
    const box = document.getElementById('dsfViewModalBox');
    const closeBtn = document.getElementById('dsfViewClose');
    const closeFooterBtn = document.getElementById('dsfViewCloseFooter');
    const toggleBtn = document.getElementById('dsfViewToggle');
    const toggleIcon = document.getElementById('dsfViewToggleIcon');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('dsf-maximized');
        }
        if (box) box.classList.remove('dsf-maximized');
        if (toggleBtn) toggleBtn.title = 'Maximize';
        if (toggleIcon) toggleIcon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
        document.body.classList.remove('dsf-modal-open');
    };

    closeBtn?.addEventListener('click', close);
    closeFooterBtn?.addEventListener('click', close);
    modal?.addEventListener('click', (e) => { if (e.target === modal) close(); });

    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const isMax = !box?.classList.contains('dsf-maximized');
        modal?.classList.toggle('dsf-maximized', isMax);
        box?.classList.toggle('dsf-maximized', isMax);
        toggleBtn.title = isMax ? 'Restore Down' : 'Maximize';
        if (toggleIcon) {
            toggleIcon.innerHTML = isMax
                ? '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>'
                : '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
        }
    });
}

function openDsfRestoreModal(id) {
    const record = dsfRecords.find((r) => String(r.id) === String(id));
    if (!record) return;
    dsfPendingId = id;
    const nameEl = document.getElementById('dsfRestoreName');
    if (nameEl) nameEl.textContent = formatRecordName(record);
    const modal = document.getElementById('dsfRestoreModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('dsf-modal-open');
    }
}

function closeDsfRestoreModal() {
    dsfPendingId = null;
    const modal = document.getElementById('dsfRestoreModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('dsf-modal-open');
}

function bindDsfRestoreModal() {
    const cancelBtn = document.getElementById('dsfRestoreCancelBtn');
    const confirmBtn = document.getElementById('dsfRestoreConfirmBtn');
    const modal = document.getElementById('dsfRestoreModal');

    cancelBtn?.addEventListener('click', closeDsfRestoreModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeDsfRestoreModal(); });

    confirmBtn?.addEventListener('click', async function () {
        if (!dsfPendingId) return;
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Restoring...';

        try {
            const response = await fetch(DSF_API.restore(dsfPendingId), {
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
            closeDsfRestoreModal();
            showDsfToast(data.message || 'Account restored successfully.');
            await loadDsfRecords();
        } catch (error) {
            alert(error.message || 'Failed to restore account. Please try again.');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Restore';
        }
    });
}

function showDsfToast(message) {
    const existing = document.getElementById('dsf-toast');
    if (existing) {
        clearTimeout(existing._timer);
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'dsf-toast';
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
