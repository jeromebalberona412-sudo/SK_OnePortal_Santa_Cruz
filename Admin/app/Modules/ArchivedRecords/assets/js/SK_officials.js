// Archived SK Officials Records Module

document.addEventListener('DOMContentLoaded', function () {
    initArchivedSkOfficials();
});

// ── Sample data (completed terms only) ───────────────────────────────────────
const aroffRecords = [
    {
        id: 'aroff-001',
        firstName: 'Carlos',
        middleName: 'Bautista',
        lastName: 'Mendoza',
        suffix: '',
        position: 'SK Chairman',
        barangay: 'Bubukal',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09171112233',
        email: 'carlos.mendoza@example.com',
        dateOfBirth: 'Apr 10, 1999',
        age: 27,
        termStart: 'Jan 1, 2019',
        termEnd: 'Dec 31, 2021',
        termStatus: 'Completed Term',
    },
    {
        id: 'aroff-002',
        firstName: 'Liza',
        middleName: 'Ramos',
        lastName: 'Torres',
        suffix: '',
        position: 'SK Kagawad',
        barangay: 'Calios',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09282223344',
        email: 'liza.torres@example.com',
        dateOfBirth: 'Jul 22, 2000',
        age: 26,
        termStart: 'Jan 1, 2019',
        termEnd: 'Dec 31, 2021',
        termStatus: 'Completed Term',
    },
    {
        id: 'aroff-003',
        firstName: 'Roberto',
        middleName: 'Flores',
        lastName: 'Villanueva',
        suffix: 'III',
        position: 'SK Secretary',
        barangay: 'Dingin',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09393334455',
        email: 'roberto.villanueva@example.com',
        dateOfBirth: 'Nov 3, 1998',
        age: 28,
        termStart: 'Jan 1, 2022',
        termEnd: 'Dec 31, 2024',
        termStatus: 'Completed Term',
    },
    {
        id: 'aroff-004',
        firstName: 'Patricia',
        middleName: 'Navarro',
        lastName: 'Castillo',
        suffix: '',
        position: 'SK Treasurer',
        barangay: 'Labuin',
        municipality: 'Santa Cruz',
        province: 'Laguna',
        region: 'IV-A CALABARZON',
        contactNumber: '09504445566',
        email: 'patricia.castillo@example.com',
        dateOfBirth: 'Feb 14, 2001',
        age: 25,
        termStart: 'Jan 1, 2022',
        termEnd: 'Dec 31, 2024',
        termStatus: 'Completed Term',
    },
];

let aroffFiltered = [...aroffRecords];
let aroffCurrentPage = 1;
const aroffPerPage = 10;
let aroffSearchQ = '';
let aroffFilterPosition = '';
let aroffFilterTerm = '';

// ── Init ──────────────────────────────────────────────────────────────────────
function initArchivedSkOfficials() {
    renderAroffStats();
    populateAroffFilters();
    applyAroffFilters();
    bindAroffSearch();
    bindAroffFilters();
    bindAroffViewModal();
}

// ── Populate filter dropdowns ─────────────────────────────────────────────────
function populateAroffFilters() {
    const positions = [...new Set(aroffRecords.map(r => r.position))].sort();
    const terms     = [...new Set(aroffRecords.map(r => `${r.termStart} – ${r.termEnd}`))].sort();

    const posEl  = document.getElementById('aroffFilterPosition');
    const termEl = document.getElementById('aroffFilterTerm');

    if (posEl) {
        positions.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p; opt.textContent = p;
            posEl.appendChild(opt);
        });
    }
    if (termEl) {
        terms.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t; opt.textContent = t;
            termEl.appendChild(opt);
        });
    }
}

// ── Apply all active filters ──────────────────────────────────────────────────
function applyAroffFilters() {
    aroffFiltered = aroffRecords.filter(r => {
        const fullName = `${r.firstName} ${r.middleName || ''} ${r.lastName}`.toLowerCase();
        const term = `${r.termStart} – ${r.termEnd}`;
        const matchSearch   = !aroffSearchQ || fullName.includes(aroffSearchQ) || (r.position || '').toLowerCase().includes(aroffSearchQ) || (r.barangay || '').toLowerCase().includes(aroffSearchQ);
        const matchPosition = !aroffFilterPosition || r.position === aroffFilterPosition;
        const matchTerm     = !aroffFilterTerm || term === aroffFilterTerm;
        return matchSearch && matchPosition && matchTerm;
    });
    aroffCurrentPage = 1;
    renderAroffTable();
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderAroffStats() {
    const row = document.getElementById('aroffStatsRow');
    if (!row) return;

    const total     = aroffRecords.length;
    const positions = [...new Set(aroffRecords.map(r => r.position))].length;
    const barangays = [...new Set(aroffRecords.map(r => r.barangay))].length;

    row.innerHTML = `
        <div class="aroff-stat-card aroff-stat-card-teal">
            <div class="aroff-stat-top">
                <span class="aroff-stat-value">${total}</span>
                <div class="aroff-stat-icon aroff-icon-teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <span class="aroff-stat-label">Total Archived</span>
        </div>
        <div class="aroff-stat-card aroff-stat-card-green">
            <div class="aroff-stat-top">
                <span class="aroff-stat-value">${positions}</span>
                <div class="aroff-stat-icon aroff-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <span class="aroff-stat-label">Positions</span>
        </div>
        <div class="aroff-stat-card aroff-stat-card-purple">
            <div class="aroff-stat-top">
                <span class="aroff-stat-value">${barangays}</span>
                <div class="aroff-stat-icon aroff-icon-purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
            </div>
            <span class="aroff-stat-label">Barangays</span>
        </div>`;
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderAroffTable() {
    const tbody = document.getElementById('aroffTableBody');
    const info  = document.getElementById('aroffPaginationInfo');
    if (!tbody) return;

    const start = (aroffCurrentPage - 1) * aroffPerPage;
    const end   = start + aroffPerPage;
    const page  = aroffFiltered.slice(start, end);

    if (aroffFiltered.length === 0) {
        tbody.innerHTML = `<tr class="aroff-empty-row"><td colspan="5">No archived SK Officials records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderAroffPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = `${r.lastName}, ${r.firstName}${r.middleName ? ' ' + r.middleName : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        const term = `${r.termStart} – ${r.termEnd}`;
        return `
        <tr>
            <td class="aroff-name-cell">${fullName}</td>
            <td style="text-align:center;">${r.position || '—'}</td>
            <td style="text-align:center;"><span class="aroff-term-badge">${term}</span></td>
            <td style="text-align:center;"><span class="aroff-completed-badge">Completed Term</span></td>
            <td>
                <div class="aroff-action-btns">
                    <button class="aroff-btn-view" data-id="${r.id}" aria-label="View details for ${fullName}">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, aroffFiltered.length)} of ${aroffFiltered.length} records`;

    renderAroffPagination(aroffFiltered.length);

    tbody.querySelectorAll('.aroff-btn-view').forEach(btn => {
        btn.addEventListener('click', function () { openAroffViewModal(this.dataset.id); });
    });
}

function renderAroffPagination(total) {
    const pages = Math.ceil(total / aroffPerPage);
    const nums  = document.getElementById('aroffPageNumbers');
    const prev  = document.getElementById('aroffPrevBtn');
    const next  = document.getElementById('aroffNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="aroff-page-btn ${i + 1 === aroffCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.aroff-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { aroffCurrentPage = i + 1; renderAroffTable(); });
        });
    }
    if (prev) { prev.disabled = aroffCurrentPage === 1; prev.onclick = () => { aroffCurrentPage--; renderAroffTable(); }; }
    if (next) { next.disabled = aroffCurrentPage >= pages || pages === 0; next.onclick = () => { aroffCurrentPage++; renderAroffTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindAroffSearch() {
    const input = document.getElementById('aroffSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        aroffSearchQ = this.value.toLowerCase();
        applyAroffFilters();
    });
}

// ── Filter dropdowns ──────────────────────────────────────────────────────────
function bindAroffFilters() {
    const posEl  = document.getElementById('aroffFilterPosition');
    const termEl = document.getElementById('aroffFilterTerm');
    if (posEl)  posEl.addEventListener('change',  function () { aroffFilterPosition = this.value; applyAroffFilters(); });
    if (termEl) termEl.addEventListener('change', function () { aroffFilterTerm = this.value; applyAroffFilters(); });
}

// ── View Modal ────────────────────────────────────────────────────────────────
function openAroffViewModal(id) {
    const r = aroffRecords.find(x => x.id === id);
    if (!r) return;

    const body = document.getElementById('aroffViewBody');
    if (body) {
        body.innerHTML = `
            <div class="aroff-view-section-block">
                <div class="aroff-view-section-header">
                    <span class="aroff-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                    </span>
                    <span class="aroff-view-section-label">Personal Information</span>
                </div>
                <div class="aroff-view-info-grid">
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Full Name</span>
                        <span class="aroff-view-value aroff-view-fullname">${r.firstName} ${r.middleName || ''} ${r.lastName}${r.suffix ? ' ' + r.suffix : ''}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Email Address</span>
                        <span class="aroff-view-value">${r.email || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Date of Birth</span>
                        <span class="aroff-view-value">${r.dateOfBirth || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Age</span>
                        <span class="aroff-view-value">${r.age || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Contact Number</span>
                        <span class="aroff-view-value">${r.contactNumber || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="aroff-view-section-block">
                <div class="aroff-view-section-header">
                    <span class="aroff-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span class="aroff-view-section-label">Location Information</span>
                </div>
                <div class="aroff-view-info-grid">
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Barangay</span>
                        <span class="aroff-view-value">${r.barangay || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Municipality</span>
                        <span class="aroff-view-value">${r.municipality || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Province</span>
                        <span class="aroff-view-value">${r.province || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Region</span>
                        <span class="aroff-view-value">${r.region || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="aroff-view-section-block">
                <div class="aroff-view-section-header">
                    <span class="aroff-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span class="aroff-view-section-label">Term Information</span>
                </div>
                <div class="aroff-view-info-grid">
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Position</span>
                        <span class="aroff-view-value">${r.position || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Term Start</span>
                        <span class="aroff-view-value">${r.termStart || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Term End</span>
                        <span class="aroff-view-value">${r.termEnd || '—'}</span>
                    </div>
                    <div class="aroff-view-field">
                        <span class="aroff-view-label">Term Status</span>
                        <span class="aroff-badge aroff-badge-green">${r.termStatus || 'Completed Term'}</span>
                    </div>
                </div>
            </div>`;
    }

    const modal = document.getElementById('aroffViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindAroffViewModal() {
    const modal     = document.getElementById('aroffViewModal');
    const box       = document.getElementById('aroffViewModalBox');
    const closeBtn  = document.getElementById('aroffViewClose');
    const toggleBtn = document.getElementById('aroffViewToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('aroff-maximized'); }
        if (box)   box.classList.remove('aroff-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('aroff-maximized');
            modal.classList.toggle('aroff-maximized', isMax);
            box.classList.toggle('aroff-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') close();
    });
}
