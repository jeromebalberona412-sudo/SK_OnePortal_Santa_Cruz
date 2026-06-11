// Archived SK Federation Records Module

document.addEventListener('DOMContentLoaded', function () {
    initArchivedSkFederation();
});

function formatRecordName(record) {
    return [record.lastName, record.firstName, record.middleName, record.suffix]
        .filter((part) => part && String(part).trim() !== '')
        .join(', ');
}

const ARFED_API = {
    data: '/manage-archive/sk-federation-records/data',
};

let arfedRecords = [];
let arfedFiltered = [];
let arfedIsLoading = false;
let arfedCurrentPage = 1;
const arfedPerPage = 10;
let arfedSearchQ = '';
let arfedYearFilter = 'all';
let arfedTermFilter = 'all';

// ── Init ──────────────────────────────────────────────────────────────────────
const ARFED_POLL_MS = 20000;

function initArchivedSkFederation() {
    bindArfedSearch();
    bindArfedViewModal();
    loadArfedRecords();
    startArfedRealtimeRefresh();
}

function startArfedRealtimeRefresh() {
    window.setInterval(() => {
        if (!document.hidden) {
            loadArfedRecords();
        }
    }, ARFED_POLL_MS);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            loadArfedRecords();
        }
    });
}

async function loadArfedRecords() {
    if (arfedIsLoading) return;
    arfedIsLoading = true;

    const params = new URLSearchParams({
        search: arfedSearchQ,
        year: arfedYearFilter,
        term: arfedTermFilter,
    });

    try {
        const response = await fetch(`${ARFED_API.data}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        if (!response.ok) throw new Error('Failed to load archived records.');

        arfedRecords = payload.data || [];
        arfedFiltered = [...arfedRecords];
        renderArfedStats(payload.stats || {});
        populateArfedFilters(payload.filters || {});
        arfedCurrentPage = 1;
        renderArfedTable();
    } catch (error) {
        arfedRecords = [];
        arfedFiltered = [];
        renderArfedStats({ total: 0, positions: 0, barangays: 0 });
        renderArfedTable();
    } finally {
        arfedIsLoading = false;
    }
}

function populateArfedFilters(filters) {
    const yearSelect = document.getElementById('arfedYearFilter');
    const termSelect = document.getElementById('arfedTermFilter');

    if (yearSelect && Array.isArray(filters.years)) {
        const currentYear = yearSelect.value;
        const yearOptions = ['<option value="all">All Years</option>']
            .concat(filters.years.map((year) => `<option value="${year}">${year}</option>`));
        yearSelect.innerHTML = yearOptions.join('');
        if ([...yearSelect.options].some((option) => option.value === currentYear)) {
            yearSelect.value = currentYear;
        }
    }

    if (!termSelect || !Array.isArray(filters.terms)) {
        return;
    }

    const current = termSelect.value;
    const options = ['<option value="all">All Terms</option>']
        .concat(filters.terms.map((term) => {
            const value = typeof term === 'string' ? term : term.value;
            const label = typeof term === 'string' ? term.replace('-', ' - ') : term.label;
            return `<option value="${value}">${label}</option>`;
        }));
    termSelect.innerHTML = options.join('');
    if ([...termSelect.options].some((option) => option.value === current)) {
        termSelect.value = current;
    }
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderArfedStats(stats = null) {
    const row = document.getElementById('arfedStatsRow');
    if (!row) return;

    const total = stats?.total ?? arfedRecords.length;
    const positions = stats?.positions ?? [...new Set(arfedRecords.map(r => r.position))].length;
    const terms = [...new Set(arfedRecords.map(r => r.termStart + '–' + r.termEnd))].length;

    row.innerHTML = `
        <div class="arfed-stat-card arfed-stat-card-blue">
            <div class="arfed-stat-top">
                <span class="arfed-stat-value">${total}</span>
                <div class="arfed-stat-icon arfed-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <span class="arfed-stat-label">Total Archived</span>
        </div>
        <div class="arfed-stat-card arfed-stat-card-green">
            <div class="arfed-stat-top">
                <span class="arfed-stat-value">${positions}</span>
                <div class="arfed-stat-icon arfed-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <span class="arfed-stat-label">Positions</span>
        </div>
        <div class="arfed-stat-card arfed-stat-card-indigo">
            <div class="arfed-stat-top">
                <span class="arfed-stat-value">${terms}</span>
                <div class="arfed-stat-icon arfed-icon-indigo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                </div>
            </div>
            <span class="arfed-stat-label">Terms</span>
        </div>`;
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderArfedTable() {
    const tbody = document.getElementById('arfedTableBody');
    const info = document.getElementById('arfedPaginationInfo');
    if (!tbody) return;

    const start = (arfedCurrentPage - 1) * arfedPerPage;
    const end = start + arfedPerPage;
    const page = arfedFiltered.slice(start, end);

    if (arfedFiltered.length === 0) {
        tbody.innerHTML = `<tr class="arfed-empty-row"><td colspan="5">No archived SK Federation records found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderArfedPagination(0);
        return;
    }

    tbody.innerHTML = page.map(r => {
        const fullName = formatRecordName(r);
        const term = `${r.termStart} – ${r.termEnd}`;
        return `
        <tr>
            <td class="arfed-name-cell">${fullName}</td>
            <td style="text-align:center;">${r.position || '—'}</td>
            <td style="text-align:center;"><span class="arfed-term-badge">${term}</span></td>
            <td style="text-align:center;"><span class="arfed-completed-badge">Completed Term</span></td>
            <td>
                <div class="arfed-action-btns">
                    <button type="button" class="arfed-btn-view" data-id="${r.id}" aria-label="View details for ${fullName}">View</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, arfedFiltered.length)} of ${arfedFiltered.length} records`;

    renderArfedPagination(arfedFiltered.length);

    tbody.querySelectorAll('.arfed-btn-view').forEach(btn => {
        btn.addEventListener('click', function () { openArfedViewModal(this.dataset.id); });
    });
}

function renderArfedPagination(total) {
    const pages = Math.ceil(total / arfedPerPage);
    const nums = document.getElementById('arfedPageNumbers');
    const prev = document.getElementById('arfedPrevBtn');
    const next = document.getElementById('arfedNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="arfed-page-btn ${i + 1 === arfedCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.arfed-page-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { arfedCurrentPage = i + 1; renderArfedTable(); });
        });
    }
    if (prev) { prev.disabled = arfedCurrentPage === 1; prev.onclick = () => { arfedCurrentPage--; renderArfedTable(); }; }
    if (next) { next.disabled = arfedCurrentPage >= pages || pages === 0; next.onclick = () => { arfedCurrentPage++; renderArfedTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindArfedSearch() {
    const input = document.getElementById('arfedSearch');
    const yearSelect = document.getElementById('arfedYearFilter');
    const termSelect = document.getElementById('arfedTermFilter');
    let searchTimer = null;

    if (input) {
        input.addEventListener('input', function () {
            arfedSearchQ = this.value.trim();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => loadArfedRecords(), 300);
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            arfedYearFilter = this.value;
            loadArfedRecords();
        });
    }

    if (termSelect) {
        termSelect.addEventListener('change', function () {
            arfedTermFilter = this.value;
            loadArfedRecords();
        });
    }
}

// ── View Modal ────────────────────────────────────────────────────────────────
function openArfedViewModal(id) {
    const r = arfedRecords.find((x) => String(x.id) === String(id));
    if (!r) return;

    const body = document.getElementById('arfedViewBody');
    if (body) {
        body.innerHTML = `
            <div class="arfed-view-section-block">
                <div class="arfed-view-section-header">
                    <span class="arfed-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                    </span>
                    <span class="arfed-view-section-label">Personal Information</span>
                </div>
                <div class="arfed-view-info-grid">
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Full Name</span>
                        <span class="arfed-view-value arfed-view-fullname">${formatRecordName(r)}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Email Address</span>
                        <span class="arfed-view-value">${r.email || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Date of Birth</span>
                        <span class="arfed-view-value">${r.dateOfBirth || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Age</span>
                        <span class="arfed-view-value">${r.age || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Contact Number</span>
                        <span class="arfed-view-value">${r.contactNumber || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="arfed-view-section-block">
                <div class="arfed-view-section-header">
                    <span class="arfed-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <span class="arfed-view-section-label">Location Information</span>
                </div>
                <div class="arfed-view-info-grid">
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Barangay</span>
                        <span class="arfed-view-value">${r.barangay || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Municipality</span>
                        <span class="arfed-view-value">${r.municipality || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Province</span>
                        <span class="arfed-view-value">${r.province || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Region</span>
                        <span class="arfed-view-value">${r.region || '—'}</span>
                    </div>
                </div>
            </div>
            <div class="arfed-view-section-block">
                <div class="arfed-view-section-header">
                    <span class="arfed-view-section-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span class="arfed-view-section-label">Term Information</span>
                </div>
                <div class="arfed-view-info-grid">
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Position</span>
                        <span class="arfed-view-value">${r.position || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Term Start</span>
                        <span class="arfed-view-value">${r.termStart || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Term End</span>
                        <span class="arfed-view-value">${r.termEnd || '—'}</span>
                    </div>
                    <div class="arfed-view-field">
                        <span class="arfed-view-label">Term Status</span>
                        <span class="arfed-badge arfed-badge-green">${r.termStatus || 'Completed Term'}</span>
                    </div>
                </div>
            </div>`;
    }

    const modal = document.getElementById('arfedViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindArfedViewModal() {
    const modal = document.getElementById('arfedViewModal');
    const box = document.getElementById('arfedViewModalBox');
    const closeBtn = document.getElementById('arfedViewClose');
    const closeFooterBtn = document.getElementById('arfedViewCloseFooter');
    const toggleBtn = document.getElementById('arfedViewToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('arfed-maximized'); }
        if (box) box.classList.remove('arfed-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (closeFooterBtn) closeFooterBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isMax = !box.classList.contains('arfed-maximized');
            modal.classList.toggle('arfed-maximized', isMax);
            box.classList.toggle('arfed-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') close();
    });
}
