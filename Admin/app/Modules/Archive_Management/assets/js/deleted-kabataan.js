// Admin Archive — Deleted Kabataan
// Read-only monitoring view of deleted Kabataan records from SK Officials.

document.addEventListener('DOMContentLoaded', function () {
    initAdminDeletedKabataan();
});

// ── Sample data mirroring SK Officials Deleted_Kabataan records ───────────────
// In production, replace with an API/AJAX call to the shared database.
const adkabRecords = [
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
        deletedDate: 'Apr 20, 2023',
        deletedTime: '09:45 AM',
        _deletedTs: new Date('2023-04-20T09:45:00'),
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
        deletedDate: 'Apr 12, 2024',
        deletedTime: '02:30 PM',
        _deletedTs: new Date('2024-04-12T14:30:00'),
    },
    {
        id: 'dk-003',
        respondentNumber: '015',
        firstName: 'Marco',
        middleName: 'Luis',
        lastName: 'Reyes',
        suffix: 'Jr.',
        sex: 'Male',
        age: 23,
        barangay: 'POBLACION',
        purokZone: 'Zone 1',
        educationalBackground: 'College Graduate',
        youthClassification: 'Working Youth',
        workStatus: 'Employed',
        civilStatus: 'Single',
        contactNumber: '09201234567',
        deletedDate: 'May 10, 2024',
        deletedTime: '11:15 AM',
        _deletedTs: new Date('2024-05-10T11:15:00'),
    },
    {
        id: 'dk-004',
        respondentNumber: '018',
        firstName: 'Ana',
        middleName: 'Grace',
        lastName: 'Cruz',
        suffix: '',
        sex: 'Female',
        age: 19,
        barangay: 'VILLA GRACIA',
        purokZone: 'Zone 2',
        educationalBackground: 'College Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09301234567',
        deletedDate: 'May 15, 2025',
        deletedTime: '03:00 PM',
        _deletedTs: new Date('2025-05-15T15:00:00'),
    },
    {
        id: 'dk-005',
        respondentNumber: '022',
        firstName: 'Pedro',
        middleName: 'Santos',
        lastName: 'Gonzales',
        suffix: '',
        sex: 'Male',
        age: 21,
        barangay: 'LUPANG PANGAKO',
        purokZone: 'Zone 4',
        educationalBackground: 'College Level',
        youthClassification: 'In School Youth',
        workStatus: 'Student',
        civilStatus: 'Single',
        contactNumber: '09411234567',
        deletedDate: 'Jun 20, 2025',
        deletedTime: '10:30 AM',
        _deletedTs: new Date('2025-06-20T10:30:00'),
    },
    {
        id: 'dk-006',
        respondentNumber: '025',
        firstName: 'Sofia',
        middleName: 'Cruz',
        lastName: 'Mendoza',
        suffix: '',
        sex: 'Female',
        age: 18,
        barangay: 'BIGAYAN',
        purokZone: 'Zone 6',
        educationalBackground: 'High School Graduate',
        youthClassification: 'Out of School Youth',
        workStatus: 'Unemployed',
        civilStatus: 'Single',
        contactNumber: '09521234567',
        deletedDate: 'Jul 10, 2026',
        deletedTime: '02:15 PM',
        _deletedTs: new Date('2026-07-10T14:15:00'),
    },
];

let adkabFiltered = [...adkabRecords];
let adkabCurrentPage = 1;
const adkabPerPage = 10;
let adkabActiveFilter = 'all';
let adkabSearchQ = '';
let adkabYearFilter = 'all';
let adkabTermFilter = 'all';

// ── Date helpers ──────────────────────────────────────────────────────────────
function adkabNow() { return new Date(); }

function adkabIsToday(ts) {
    const n = adkabNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth() && ts.getDate() === n.getDate();
}

function adkabIsThisWeek(ts) {
    const n = adkabNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function adkabIsThisMonth(ts) {
    const n = adkabNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

function adkabApplyTabFilter(records, filter) {
    if (filter === 'today') return records.filter(r => adkabIsToday(r._deletedTs));
    if (filter === 'week')  return records.filter(r => adkabIsThisWeek(r._deletedTs));
    if (filter === 'month') return records.filter(r => adkabIsThisMonth(r._deletedTs));
    return records;
}

// ── Init ──────────────────────────────────────────────────────────────────────
function initAdminDeletedKabataan() {
    renderAdkabStats();
    applyAdkabFilters();
    bindAdkabSearch();
    bindAdkabFilterTabs();
    bindAdkabViewModal();
}

// ── Apply all filters ─────────────────────────────────────────────────────────
function applyAdkabFilters() {
    let base = adkabApplyTabFilter(adkabRecords, adkabActiveFilter);
    
    // Year filter
    if (adkabYearFilter !== 'all') {
        base = base.filter(r => r._deletedTs.getFullYear() === parseInt(adkabYearFilter, 10));
    }
    
    // Term filter
    if (adkabTermFilter !== 'all') {
        const [termStart, termEnd] = adkabTermFilter.split('-').map(y => parseInt(y, 10));
        base = base.filter(r => {
            const year = r._deletedTs.getFullYear();
            return year >= termStart && year <= termEnd;
        });
    }
    
    // Search filter
    if (adkabSearchQ) {
        base = base.filter(r => {
            const name = `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase();
            return name.includes(adkabSearchQ) || (r.barangay || '').toLowerCase().includes(adkabSearchQ);
        });
    }
    adkabFiltered = base;
    adkabCurrentPage = 1;
    renderAdkabTable();
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderAdkabStats() {
    const row = document.getElementById('adkabStatsRow');
    if (!row) return;

    const total = adkabRecords.length;
    const month = adkabRecords.filter(r => adkabIsThisMonth(r._deletedTs)).length;
    const today = adkabRecords.filter(r => adkabIsToday(r._deletedTs)).length;

    row.innerHTML = `
        <div class="adkab-stat-card adkab-stat-card-red">
            <div class="adkab-stat-top">
                <span class="adkab-stat-value">${total}</span>
                <div class="adkab-stat-icon adkab-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </div>
            </div>
            <span class="adkab-stat-label">Total Deleted</span>
        </div>
        <div class="adkab-stat-card adkab-stat-card-orange">
            <div class="adkab-stat-top">
                <span class="adkab-stat-value">${month}</span>
                <div class="adkab-stat-icon adkab-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                </div>
            </div>
            <span class="adkab-stat-label">This Month</span>
        </div>
        <div class="adkab-stat-card adkab-stat-card-blue">
            <div class="adkab-stat-top">
                <span class="adkab-stat-value">${today}</span>
                <div class="adkab-stat-icon adkab-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="adkab-stat-label">Today</span>
        </div>`;
}

// ── Filter Tabs ───────────────────────────────────────────────────────────────
function bindAdkabFilterTabs() {
    document.querySelectorAll('.adkab-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.adkab-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            adkabActiveFilter = this.dataset.filter;
            const labels = { all: 'All Deleted Records', today: 'Deleted Today', week: 'Deleted This Week', month: 'Deleted This Month' };
            const label = document.getElementById('adkabSectionLabel');
            if (label) label.textContent = labels[adkabActiveFilter] || 'Deleted Records';
            applyAdkabFilters();
        });
    });
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindAdkabSearch() {
    const input = document.getElementById('adkabSearch');
    const yearSelect = document.getElementById('adkabYearFilter');
    const termSelect = document.getElementById('adkabTermFilter');
    
    if (input) {
        input.addEventListener('input', function () {
            adkabSearchQ = this.value.toLowerCase();
            applyAdkabFilters();
        });
    }
    
    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            adkabYearFilter = this.value;
            applyAdkabFilters();
        });
    }
    
    if (termSelect) {
        termSelect.addEventListener('change', function () {
            adkabTermFilter = this.value;
            applyAdkabFilters();
        });
    }
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderAdkabTable() {
    const tbody = document.getElementById('adkabTableBody');
    const info  = document.getElementById('adkabPaginationInfo');
    if (!tbody) return;

    const start = (adkabCurrentPage - 1) * adkabPerPage;
    const end   = start + adkabPerPage;
    const page  = adkabFiltered.slice(start, end);

    if (adkabFiltered.length === 0) {
        tbody.innerHTML = `<tr class="adkab-empty-row"><td colspan="8">No deleted Kabataan records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderAdkabPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return `
        <tr>
            <td style="font-weight:600;color:#111827;text-align:left;">${fullName}</td>
            <td>${r.age || '—'}</td>
            <td>${r.sex || '—'}</td>
            <td>${r.barangay || '—'}</td>
            <td>${r.educationalBackground || '—'}</td>
            <td><span class="adkab-deleted-badge">${r.deletedDate}</span></td>
            <td><span class="adkab-time-badge">${r.deletedTime}</span></td>
            <td>
                <div class="adkab-action-btns">
                    <button class="adkab-btn-view" data-id="${r.id}" aria-label="View details">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, adkabFiltered.length)} of ${adkabFiltered.length} records`;

    renderAdkabPagination(adkabFiltered.length);

    tbody.querySelectorAll('.adkab-btn-view').forEach(btn => {
        btn.addEventListener('click', function () { openAdkabViewModal(this.dataset.id); });
    });
}

// ── Pagination ────────────────────────────────────────────────────────────────
function renderAdkabPagination(total) {
    const pages = Math.ceil(total / adkabPerPage);
    const nums  = document.getElementById('adkabPageNumbers');
    const prev  = document.getElementById('adkabPrevBtn');
    const next  = document.getElementById('adkabNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="adkab-page-btn ${i + 1 === adkabCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.adkab-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { adkabCurrentPage = i + 1; renderAdkabTable(); });
        });
    }
    if (prev) { prev.disabled = adkabCurrentPage === 1; prev.onclick = () => { adkabCurrentPage--; renderAdkabTable(); }; }
    if (next) { next.disabled = adkabCurrentPage >= pages || pages === 0; next.onclick = () => { adkabCurrentPage++; renderAdkabTable(); }; }
}

// ── View Modal ────────────────────────────────────────────────────────────────
function openAdkabViewModal(id) {
    const r = adkabRecords.find(x => x.id === id);
    if (!r) return;

    const body = document.getElementById('adkabViewBody');
    if (body) {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        body.innerHTML = `
            <div class="adkab-readonly-notice">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Admin view only — this record was deleted by SK Officials.
            </div>
            <div class="adkab-view-section-block">
                <div class="adkab-view-section-header">
                    <span class="adkab-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                    </span>
                    <span class="adkab-view-section-label">Personal Information</span>
                </div>
                <div class="adkab-view-info-grid">
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Full Name</span>
                        <span class="adkab-view-value adkab-view-fullname">${fullName}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Age</span>
                        <span class="adkab-view-value">${r.age || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Sex</span>
                        <span class="adkab-view-value">${r.sex || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Civil Status</span>
                        <span class="adkab-view-value">${r.civilStatus || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Purok / Zone</span>
                        <span class="adkab-view-value">${r.purokZone || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Barangay</span>
                        <span class="adkab-view-value">${r.barangay || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Contact Number</span>
                        <span class="adkab-view-value">${r.contactNumber || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="adkab-view-section-block">
                <div class="adkab-view-section-header">
                    <span class="adkab-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/></svg>
                    </span>
                    <span class="adkab-view-section-label">Classification & Status</span>
                </div>
                <div class="adkab-view-info-grid">
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Youth Classification</span>
                        <span class="adkab-badge adkab-badge-blue">${r.youthClassification || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Work Status</span>
                        <span class="adkab-badge adkab-badge-green">${r.workStatus || '—'}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Education</span>
                        <span class="adkab-view-value">${r.educationalBackground || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="adkab-view-section-block">
                <div class="adkab-view-section-header" style="background:#fef2f2;border-bottom-color:#fecaca;">
                    <span class="adkab-view-section-icon" style="background:#fee2e2;color:#dc2626;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
                    </span>
                    <span class="adkab-view-section-label" style="color:#b91c1c;">Deletion Information</span>
                </div>
                <div class="adkab-view-info-grid">
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Deleted Date</span>
                        <span class="adkab-view-value-danger">${r.deletedDate}</span>
                    </div>
                    <div class="adkab-view-field">
                        <span class="adkab-view-label">Deleted Time</span>
                        <span class="adkab-view-value-danger">${r.deletedTime}</span>
                    </div>
                </div>
            </div>`;
    }

    const modal = document.getElementById('adkabViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindAdkabViewModal() {
    const modal     = document.getElementById('adkabViewModal');
    const box       = document.getElementById('adkabViewModalBox');
    const closeBtn  = document.getElementById('adkabViewClose');
    const toggleBtn = document.getElementById('adkabViewToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('adkab-maximized'); }
        if (box)   box.classList.remove('adkab-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('adkab-maximized');
            modal.classList.toggle('adkab-maximized', isMax);
            box.classList.toggle('adkab-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') close();
    });
}
