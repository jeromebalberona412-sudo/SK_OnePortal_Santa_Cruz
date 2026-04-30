// Deleted SK Federation Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedSkFederation();
});

const dsfRecords = [
    {
        id: 'dsf-001',
        firstName: 'Jerome',
        middleName: 'Sanicoa',
        lastName: 'Balberona',
        suffix: 'Jr.',
        position: 'Chairman',
        barangay: 'Poblacion III',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09081137312',
        email: 'jeromebalbepanget@gmail.com',
        dateOfBirth: 'Jul 24, 2015',
        age: 10,
        emailVerification: '04/24/2026 02:57 PM',
        termStart: 'Feb 9, 2023',
        termEnd: 'Apr 24, 2026',
        accountStatus: 'ACTIVE',
        termStatus: 'ACTIVE',
        deletedDate: 'Apr 24, 2026',
        deletedTime: '03:00 PM',
        _deletedTs: new Date('2026-04-24T15:00:00'),
    },
];

let dsfFiltered = [...dsfRecords];
let dsfCurrentPage = 1;
const dsfPerPage = 10;
let dsfPendingId = null;
let dsfActiveFilter = 'all';

// ── Date helpers ──────────────────────────────────────────────────────────────
function dsfNow() { return new Date('2026-04-26T12:00:00'); }

function dsfIsToday(ts) {
    const n = dsfNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function dsfIsThisWeek(ts) {
    const n = dsfNow();
    const start = new Date(n);
    start.setDate(n.getDate() - n.getDay());
    start.setHours(0, 0, 0, 0);
    return ts >= start;
}

function dsfIsThisMonth(ts) {
    const n = dsfNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function dsfApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => dsfIsToday(r._deletedTs));
    if (filter === 'week')  return records.filter(r => dsfIsThisWeek(r._deletedTs));
    if (filter === 'month') return records.filter(r => dsfIsThisMonth(r._deletedTs));
    return records;
}

function initDeletedSkFederation() {
    renderDsfStats();
    renderDsfTable();
    bindDsfSearch();
    bindDsfFilterTabs();
    bindDsfRestoreModal();
    bindDsfViewModal();
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderDsfStats() {
    const row = document.getElementById('dsfStatsRow');
    if (!row) return;
    const total = dsfRecords.length;
    const month = dsfRecords.filter(r => dsfIsThisMonth(r._deletedTs)).length;
    const today = dsfRecords.filter(r => dsfIsToday(r._deletedTs)).length;

    row.innerHTML = `
        <div class="dsf-stat-card dsf-stat-card-red">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${total}</span>
                <div class="dsf-stat-icon dsf-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                </div>
            </div>
            <span class="dsf-stat-label">Total Deleted</span>
        </div>
        <div class="dsf-stat-card dsf-stat-card-orange">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${month}</span>
                <div class="dsf-stat-icon dsf-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
                </div>
            </div>
            <span class="dsf-stat-label">This Month</span>
        </div>
        <div class="dsf-stat-card dsf-stat-card-blue">
            <div class="dsf-stat-top">
                <span class="dsf-stat-value">${today}</span>
                <div class="dsf-stat-icon dsf-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="dsf-stat-label">Today</span>
        </div>`;
}

// ── Filter Tabs ───────────────────────────────────────────────────────────────
function bindDsfFilterTabs() {
    document.querySelectorAll('.dsf-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dsf-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            dsfActiveFilter = this.dataset.filter;
            const labels = { all: 'All Deleted Records', today: 'Deleted Today', week: 'Deleted This Week', month: 'Deleted This Month' };
            const label = document.getElementById('dsfSectionLabel');
            if (label) label.textContent = labels[dsfActiveFilter] || 'Deleted Records';
            dsfFiltered = dsfApplyFilter(dsfRecords, dsfActiveFilter);
            dsfCurrentPage = 1;
            renderDsfTable();
        });
    });
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderDsfTable() {
    const tbody = document.getElementById('dsfTableBody');
    const info  = document.getElementById('dsfPaginationInfo');
    if (!tbody) return;

    const start = (dsfCurrentPage - 1) * dsfPerPage;
    const end   = start + dsfPerPage;
    const page  = dsfFiltered.slice(start, end);

    if (dsfFiltered.length === 0) {
        tbody.innerHTML = `<tr class="dsf-empty-row"><td colspan="6">No deleted SK Federation records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderDsfPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return `
        <tr>
            <td style="font-weight:600;color:#111827;">${fullName}</td>
            <td>${r.position || '—'}</td>
            <td>${r.barangay ? r.barangay + (r.municipality ? ', ' + r.municipality : '') : '—'}</td>
            <td><span class="dsf-deleted-badge">${r.deletedDate}</span></td>
            <td><span class="dsf-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="dsf-action-btns">
                    <button class="dsf-btn-view" data-id="${r.id}">View</button>
                    <button class="dsf-btn-restore" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, dsfFiltered.length)} of ${dsfFiltered.length} records`;

    renderDsfPagination(dsfFiltered.length);

    tbody.querySelectorAll('.dsf-btn-restore').forEach(btn => {
        btn.addEventListener('click', function () { openDsfRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.dsf-btn-view').forEach(btn => {
        btn.addEventListener('click', function () { openDsfViewModal(this.dataset.id); });
    });
}

function renderDsfPagination(total) {
    const pages = Math.ceil(total / dsfPerPage);
    const nums  = document.getElementById('dsfPageNumbers');
    const prev  = document.getElementById('dsfPrevBtn');
    const next  = document.getElementById('dsfNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="dsf-page-btn ${i + 1 === dsfCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.dsf-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dsfCurrentPage = i + 1; renderDsfTable(); });
        });
    }
    if (prev) { prev.disabled = dsfCurrentPage === 1; prev.onclick = () => { dsfCurrentPage--; renderDsfTable(); }; }
    if (next) { next.disabled = dsfCurrentPage >= pages || pages === 0; next.onclick = () => { dsfCurrentPage++; renderDsfTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindDsfSearch() {
    const input = document.getElementById('dsfSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = dsfApplyFilter(dsfRecords, dsfActiveFilter);
        dsfFiltered = base.filter(r =>
            `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
            (r.barangay || '').toLowerCase().includes(q) ||
            (r.position || '').toLowerCase().includes(q)
        );
        dsfCurrentPage = 1;
        renderDsfTable();
    });
}

// ── View Modal ────────────────────────────────────────────────────────────────
function openDsfViewModal(id) {
    const r = dsfRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('dsfViewBody');
    if (body) {
        const statusBadge = (val) => {
            const color = val === 'ACTIVE' ? 'dsf-badge-green' : 'dsf-badge-gray';
            return `<span class="dsf-badge ${color}">${val}</span>`;
        };
        body.innerHTML = `
            <!-- Personal Information -->
            <div class="dsf-view-section-block">
                <div class="dsf-view-section-header">
                    <span class="dsf-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                    </span>
                    <span class="dsf-view-section-label">Personal Information</span>
                </div>
                <div class="dsf-view-info-grid">
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Full Name</span>
                        <span class="dsf-view-value dsf-view-fullname">${r.firstName} ${r.middleName || ''} ${r.lastName}${r.suffix ? ' ' + r.suffix : ''}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Email Address</span>
                        <span class="dsf-view-value">${r.email || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Date of Birth</span>
                        <span class="dsf-view-value">${r.dateOfBirth || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Age</span>
                        <span class="dsf-view-value">${r.age || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Contact Number</span>
                        <span class="dsf-view-value">${r.contactNumber || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Email Verification</span>
                        <span class="dsf-view-value">${r.emailVerification || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="dsf-view-section-block">
                <div class="dsf-view-section-header">
                    <span class="dsf-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span class="dsf-view-section-label">Location Information</span>
                </div>
                <div class="dsf-view-info-grid">
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Barangay</span>
                        <span class="dsf-view-value">${r.barangay || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Municipality</span>
                        <span class="dsf-view-value">${r.municipality || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Province</span>
                        <span class="dsf-view-value">${r.province || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Region</span>
                        <span class="dsf-view-value">${r.region || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Term Information -->
            <div class="dsf-view-section-block">
                <div class="dsf-view-section-header">
                    <span class="dsf-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span class="dsf-view-section-label">Term Information</span>
                </div>
                <div class="dsf-view-info-grid">
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Position</span>
                        <span class="dsf-view-value">${r.position || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Term Start</span>
                        <span class="dsf-view-value">${r.termStart || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Term End</span>
                        <span class="dsf-view-value">${r.termEnd || '—'}</span>
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Account Status</span>
                        ${statusBadge(r.accountStatus || '—')}
                    </div>
                    <div class="dsf-view-field">
                        <span class="dsf-view-label">Term Status</span>
                        ${statusBadge(r.termStatus || '—')}
                    </div>
                </div>
            </div>`;
    }
    const modal = document.getElementById('dsfViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindDsfViewModal() {
    const modal    = document.getElementById('dsfViewModal');
    const box      = document.getElementById('dsfViewModalBox');
    const closeBtn = document.getElementById('dsfViewClose');
    const toggleBtn = document.getElementById('dsfViewToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('dsf-maximized'); }
        if (box) box.classList.remove('dsf-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('dsf-maximized');
            modal.classList.toggle('dsf-maximized', isMax);
            box.classList.toggle('dsf-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

// ── Restore Modal ─────────────────────────────────────────────────────────────
function openDsfRestoreModal(id) {
    const record = dsfRecords.find(r => r.id === id);
    if (!record) return;
    dsfPendingId = id;
    const nameEl = document.getElementById('dsfRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('dsfRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeDsfRestoreModal() {
    dsfPendingId = null;
    const modal = document.getElementById('dsfRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindDsfRestoreModal() {
    const cancelBtn  = document.getElementById('dsfRestoreCancelBtn');
    const confirmBtn = document.getElementById('dsfRestoreConfirmBtn');
    const modal      = document.getElementById('dsfRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeDsfRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeDsfRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!dsfPendingId) return;
            const record = dsfRecords.find(r => r.id === dsfPendingId);
            const name = record ? `${record.lastName}, ${record.firstName}` : 'Record';
            const idx = dsfRecords.findIndex(r => r.id === dsfPendingId);
            if (idx !== -1) dsfRecords.splice(idx, 1);
            dsfFiltered = dsfApplyFilter(dsfRecords, dsfActiveFilter);
            closeDsfRestoreModal();
            dsfCurrentPage = 1;
            renderDsfStats();
            renderDsfTable();
            showDsfToast(`${name} has been restored to the SK Federation list.`);
        });
    }
}

// ── Toast Notification (top-center, slide-down) ──────────────────────────────
function showDsfToast(message) {
    const existing = document.getElementById('dsf-toast');
    if (existing) {
        clearTimeout(existing._timer);
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'dsf-toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    Object.assign(toast.style, {
        position:      'fixed',
        top:           '72px',
        left:          '50%',
        transform:     'translateX(-50%) translateY(-18px)',
        zIndex:        '99999',
        padding:       '11px 28px',
        borderRadius:  '8px',
        fontSize:      '0.875rem',
        fontWeight:    '600',
        fontFamily:    'inherit',
        color:         '#fff',
        background:    '#16a34a',
        boxShadow:     '0 4px 18px rgba(0,0,0,.18)',
        opacity:       '0',
        whiteSpace:    'nowrap',
        pointerEvents: 'none',
        transition:    'opacity 0.22s ease, transform 0.22s ease',
    });

    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.opacity   = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
        });
    });

    toast._timer = setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 250);
    }, 3000);
}
