// Deleted SK Officials Module

document.addEventListener('DOMContentLoaded', function () {
    initDeletedSkOfficials();
});

const dsoRecords = [
    {
        id: 'dso-001',
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
    {
        id: 'dso-002',
        firstName: 'Ramon',
        middleName: 'Jose',
        lastName: 'Villanueva',
        suffix: '',
        position: 'SK Secretary',
        barangay: 'Damayan',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09187654321',
        email: 'ramon.villanueva@example.com',
        dateOfBirth: 'Mar 10, 2000',
        age: 26,
        emailVerification: '01/10/2026 10:00 AM',
        termStart: 'Jan 2022',
        termEnd: 'Dec 2025',
        accountStatus: 'ACTIVE',
        termStatus: 'INACTIVE',
        deletedDate: 'Apr 20, 2026',
        deletedTime: '09:45 AM',
        _deletedTs: new Date('2026-04-20T09:45:00'),
    },
    {
        id: 'dso-003',
        firstName: 'Carla',
        middleName: 'Mae',
        lastName: 'Mendoza',
        suffix: '',
        position: 'SK Secretary',
        barangay: 'Imelda',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09201234567',
        email: 'carla.mendoza@example.com',
        dateOfBirth: 'Jun 5, 2001',
        age: 24,
        emailVerification: '02/15/2026 08:30 AM',
        termStart: 'Jan 2022',
        termEnd: 'Dec 2025',
        accountStatus: 'ACTIVE',
        termStatus: 'INACTIVE',
        deletedDate: 'Apr 15, 2026',
        deletedTime: '03:00 PM',
        _deletedTs: new Date('2026-04-15T15:00:00'),
    },
    {
        id: 'dso-004',
        firstName: 'Bong',
        middleName: 'Ramos',
        lastName: 'Garcia',
        suffix: 'III',
        position: 'SK Treasurer',
        barangay: 'Gatid',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09159876543',
        email: 'bong.garcia@example.com',
        dateOfBirth: 'Sep 18, 1999',
        age: 26,
        emailVerification: '03/01/2026 11:00 AM',
        termStart: 'Jan 2022',
        termEnd: 'Dec 2025',
        accountStatus: 'INACTIVE',
        termStatus: 'INACTIVE',
        deletedDate: 'Mar 30, 2026',
        deletedTime: '11:20 AM',
        _deletedTs: new Date('2026-03-30T11:20:00'),
    },
];

let dsoFiltered = [...dsoRecords];
let dsoCurrentPage = 1;
const dsoPerPage = 10;
let dsoPendingId = null;
let dsoActiveFilter = 'all';
let dsoActiveBarangay = '';
let dsoActivePosition = '';

// ── Date helpers ──────────────────────────────────────────────────────────────
function dsoNow() { return new Date('2026-04-26T12:00:00'); }

function dsoIsToday(ts) {
    const n = dsoNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function dsoIsThisWeek(ts) {
    const n = dsoNow();
    const start = new Date(n);
    start.setDate(n.getDate() - n.getDay());
    start.setHours(0, 0, 0, 0);
    return ts >= start;
}

function dsoIsThisMonth(ts) {
    const n = dsoNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function dsoApplyFilter(records, filter) {
    if (filter === 'today') return records.filter(r => dsoIsToday(r._deletedTs));
    if (filter === 'week')  return records.filter(r => dsoIsThisWeek(r._deletedTs));
    if (filter === 'month') return records.filter(r => dsoIsThisMonth(r._deletedTs));
    return records;
}

function initDeletedSkOfficials() {
    renderDsoStats();
    populateDsoDropdowns();
    renderDsoTable();
    bindDsoSearch();
    bindDsoFilterTabs();
    bindDsoDropdowns();
    bindDsoRestoreModal();
    bindDsoViewModal();
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderDsoStats() {
    const row = document.getElementById('dsoStatsRow');
    if (!row) return;
    const total = dsoRecords.length;
    const month = dsoRecords.filter(r => dsoIsThisMonth(r._deletedTs)).length;
    const today = dsoRecords.filter(r => dsoIsToday(r._deletedTs)).length;

    row.innerHTML = `
        <div class="dso-stat-card dso-stat-card-red">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${total}</span>
                <div class="dso-stat-icon dso-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                </div>
            </div>
            <span class="dso-stat-label">Total Deleted</span>
        </div>
        <div class="dso-stat-card dso-stat-card-orange">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${month}</span>
                <div class="dso-stat-icon dso-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="16" y1="2" x2="16" y2="6"></line></svg>
                </div>
            </div>
            <span class="dso-stat-label">This Month</span>
        </div>
        <div class="dso-stat-card dso-stat-card-blue">
            <div class="dso-stat-top">
                <span class="dso-stat-value">${today}</span>
                <div class="dso-stat-icon dso-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <span class="dso-stat-label">Today</span>
        </div>`;
}

// ── Populate Dropdowns ────────────────────────────────────────────────────────
function populateDsoDropdowns() {
    const barangaySelect = document.getElementById('dsoFilterBarangay');
    if (barangaySelect) {
        const barangays = [...new Set(dsoRecords.map(r => r.barangay).filter(Boolean))].sort();
        barangays.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b; opt.textContent = b;
            barangaySelect.appendChild(opt);
        });
    }
    const positionSelect = document.getElementById('dsoFilterPosition');
    if (positionSelect) {
        const positions = [...new Set(dsoRecords.map(r => r.position).filter(Boolean))].sort();
        positions.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p; opt.textContent = p;
            positionSelect.appendChild(opt);
        });
    }
}

// ── Bind Dropdowns ────────────────────────────────────────────────────────────
function bindDsoDropdowns() {
    const barangaySelect = document.getElementById('dsoFilterBarangay');
    if (barangaySelect) {
        barangaySelect.addEventListener('change', function () {
            dsoActiveBarangay = this.value;
            dsoCurrentPage = 1;
            dsoApplyAllFilters();
        });
    }
    const positionSelect = document.getElementById('dsoFilterPosition');
    if (positionSelect) {
        positionSelect.addEventListener('change', function () {
            dsoActivePosition = this.value;
            dsoCurrentPage = 1;
            dsoApplyAllFilters();
        });
    }
}

function dsoApplyAllFilters() {
    let result = dsoApplyFilter(dsoRecords, dsoActiveFilter);
    if (dsoActiveBarangay) result = result.filter(r => r.barangay === dsoActiveBarangay);
    if (dsoActivePosition) result = result.filter(r => r.position === dsoActivePosition);
    const q = (document.getElementById('dsoSearch')?.value || '').toLowerCase();
    if (q) result = result.filter(r =>
        `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase().includes(q) ||
        (r.barangay || '').toLowerCase().includes(q) ||
        (r.position || '').toLowerCase().includes(q)
    );
    dsoFiltered = result;
    renderDsoTable();
}

// ── Filter Tabs ───────────────────────────────────────────────────────────────
function bindDsoFilterTabs() {
    document.querySelectorAll('.dso-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dso-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            dsoActiveFilter = this.dataset.filter;
            const label = document.getElementById('dsoSectionLabel');
            if (label) {
                const labels = { all: 'All Deleted Records', today: 'Deleted Today', week: 'Deleted This Week', month: 'Deleted This Month' };
                label.textContent = labels[dsoActiveFilter] || 'Deleted Records';
            }
            dsoCurrentPage = 1;
            dsoApplyAllFilters();
        });
    });
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderDsoTable() {
    const tbody = document.getElementById('dsoTableBody');
    const info  = document.getElementById('dsoPaginationInfo');
    if (!tbody) return;

    const start = (dsoCurrentPage - 1) * dsoPerPage;
    const end   = start + dsoPerPage;
    const page  = dsoFiltered.slice(start, end);

    if (dsoFiltered.length === 0) {
        tbody.innerHTML = `<tr class="dso-empty-row"><td colspan="7">No deleted SK Officials records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderDsoPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return `
        <tr>
            <td class="dso-name-cell">${fullName}</td>
            <td>${r.position || '—'}</td>
            <td>${r.barangay || '—'}</td>
            <td>${r.municipality || '—'}</td>
            <td><span class="dso-deleted-badge">${r.deletedDate}</span></td>
            <td><span class="dso-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="dso-action-btns">
                    <button class="dso-btn-view" data-id="${r.id}">View</button>
                    <button class="dso-btn-restore" data-id="${r.id}">Restore</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, dsoFiltered.length)} of ${dsoFiltered.length} records`;

    renderDsoPagination(dsoFiltered.length);

    tbody.querySelectorAll('.dso-btn-restore').forEach(btn => {
        btn.addEventListener('click', function () { openDsoRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.dso-btn-view').forEach(btn => {
        btn.addEventListener('click', function () { openDsoViewModal(this.dataset.id); });
    });
}

function renderDsoPagination(total) {
    const pages = Math.ceil(total / dsoPerPage);
    const nums  = document.getElementById('dsoPageNumbers');
    const prev  = document.getElementById('dsoPrevBtn');
    const next  = document.getElementById('dsoNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="dso-page-btn ${i + 1 === dsoCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.dso-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { dsoCurrentPage = i + 1; renderDsoTable(); });
        });
    }
    if (prev) { prev.disabled = dsoCurrentPage === 1; prev.onclick = () => { dsoCurrentPage--; renderDsoTable(); }; }
    if (next) { next.disabled = dsoCurrentPage >= pages || pages === 0; next.onclick = () => { dsoCurrentPage++; renderDsoTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindDsoSearch() {
    const input = document.getElementById('dsoSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        dsoCurrentPage = 1;
        dsoApplyAllFilters();
    });
}

// ── View Modal ────────────────────────────────────────────────────────────────
function openDsoViewModal(id) {
    const r = dsoRecords.find(x => x.id === id);
    if (!r) return;
    const body = document.getElementById('dsoViewBody');
    if (body) {
        const statusBadge = (val) => {
            const color = val === 'ACTIVE' ? 'dso-badge-green' : 'dso-badge-gray';
            return `<span class="dso-badge ${color}">${val}</span>`;
        };
        body.innerHTML = `
            <!-- Personal Information -->
            <div class="dso-view-section-block">
                <div class="dso-view-section-header">
                    <span class="dso-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                    </span>
                    <span class="dso-view-section-label">Personal Information</span>
                </div>
                <div class="dso-view-info-grid">
                    <div class="dso-view-field">
                        <span class="dso-view-label">Full Name</span>
                        <span class="dso-view-value dso-view-fullname">${r.firstName} ${r.middleName || ''} ${r.lastName}${r.suffix ? ' ' + r.suffix : ''}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Email Address</span>
                        <span class="dso-view-value">${r.email || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Date of Birth</span>
                        <span class="dso-view-value">${r.dateOfBirth || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Age</span>
                        <span class="dso-view-value">${r.age || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Contact Number</span>
                        <span class="dso-view-value">${r.contactNumber || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Email Verification</span>
                        <span class="dso-view-value">${r.emailVerification || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="dso-view-section-block">
                <div class="dso-view-section-header">
                    <span class="dso-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span class="dso-view-section-label">Location Information</span>
                </div>
                <div class="dso-view-info-grid">
                    <div class="dso-view-field">
                        <span class="dso-view-label">Barangay</span>
                        <span class="dso-view-value">${r.barangay || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Municipality</span>
                        <span class="dso-view-value">${r.municipality || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Province</span>
                        <span class="dso-view-value">${r.province || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Region</span>
                        <span class="dso-view-value">${r.region || '—'}</span>
                    </div>
                </div>
            </div>

            <!-- Term Information -->
            <div class="dso-view-section-block">
                <div class="dso-view-section-header">
                    <span class="dso-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span class="dso-view-section-label">Term Information</span>
                </div>
                <div class="dso-view-info-grid">
                    <div class="dso-view-field">
                        <span class="dso-view-label">Position</span>
                        <span class="dso-view-value">${r.position || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Term Start</span>
                        <span class="dso-view-value">${r.termStart || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Term End</span>
                        <span class="dso-view-value">${r.termEnd || '—'}</span>
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Account Status</span>
                        ${statusBadge(r.accountStatus || '—')}
                    </div>
                    <div class="dso-view-field">
                        <span class="dso-view-label">Term Status</span>
                        ${statusBadge(r.termStatus || '—')}
                    </div>
                </div>
            </div>`;
    }
    const modal = document.getElementById('dsoViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindDsoViewModal() {
    const modal    = document.getElementById('dsoViewModal');
    const box      = document.getElementById('dsoViewModalBox');
    const closeBtn = document.getElementById('dsoViewClose');
    const toggleBtn = document.getElementById('dsoViewToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('dso-maximized'); }
        if (box) box.classList.remove('dso-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('dso-maximized');
            modal.classList.toggle('dso-maximized', isMax);
            box.classList.toggle('dso-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

// ── Restore Modal ─────────────────────────────────────────────────────────────
function openDsoRestoreModal(id) {
    const record = dsoRecords.find(r => r.id === id);
    if (!record) return;
    dsoPendingId = id;
    const nameEl = document.getElementById('dsoRestoreName');
    if (nameEl) nameEl.textContent = `${record.lastName}, ${record.firstName}${record.middleName ? ' ' + record.middleName : ''}`;
    const modal = document.getElementById('dsoRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeDsoRestoreModal() {
    dsoPendingId = null;
    const modal = document.getElementById('dsoRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindDsoRestoreModal() {
    const cancelBtn  = document.getElementById('dsoRestoreCancelBtn');
    const confirmBtn = document.getElementById('dsoRestoreConfirmBtn');
    const modal      = document.getElementById('dsoRestoreModal');

    if (cancelBtn)  cancelBtn.addEventListener('click', closeDsoRestoreModal);
    if (modal)      modal.addEventListener('click', e => { if (e.target === modal) closeDsoRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!dsoPendingId) return;
            const record = dsoRecords.find(r => r.id === dsoPendingId);
            const name = record ? `${record.lastName}, ${record.firstName}` : 'Record';
            const idx = dsoRecords.findIndex(r => r.id === dsoPendingId);
            if (idx !== -1) dsoRecords.splice(idx, 1);
            dsoFiltered = dsoApplyFilter(dsoRecords, dsoActiveFilter);
            closeDsoRestoreModal();
            dsoCurrentPage = 1;
            renderDsoStats();
            renderDsoTable();
            showDsoToast(`${name} has been restored to the SK Officials list.`);
        });
    }
}

// ── Toast Notification (top-center, slide-down) ──────────────────────────────
function showDsoToast(message) {
    const existing = document.getElementById('dso-toast');
    if (existing) {
        clearTimeout(existing._timer);
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'dso-toast';
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
