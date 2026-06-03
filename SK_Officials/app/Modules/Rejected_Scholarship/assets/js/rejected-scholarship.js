// Rejected Scholarship Module
// Reads from the same localStorage key used by scholarship_requests.js

document.addEventListener('DOMContentLoaded', function () {
    initRejectedScholarship();
});

// ── State ─────────────────────────────────────────────────────────────────────
let rsAllRecords  = [];   // all rejected records (from localStorage)
let rsFiltered    = [];   // after tab + search filter
let rsCurrentPage = 1;
const rsPerPage   = 10;
let rsPendingRestoreId = null;
let rsActiveFilter     = 'all';
let rsArchiveTerm      = '2025-2027';

// ── Simulated "now" — uses real Date so filter tabs work live ─────────────────
function rsNow() { return new Date(); }

function rsIsToday(ts) {
    const n = rsNow();
    return ts.getFullYear() === n.getFullYear()
        && ts.getMonth()    === n.getMonth()
        && ts.getDate()     === n.getDate();
}

function rsIsThisWeek(ts) {
    const n = rsNow();
    const startOfWeek = new Date(n);
    startOfWeek.setDate(n.getDate() - n.getDay());
    startOfWeek.setHours(0, 0, 0, 0);
    return ts >= startOfWeek;
}

function rsIsThisMonth(ts) {
    const n = rsNow();
    return ts.getFullYear() === n.getFullYear() && ts.getMonth() === n.getMonth();
}

// Parse a submitted_at string like "Jan 10, 2025" into a Date
function rsParseDate(str) {
    if (!str) return new Date(0);
    const d = new Date(str);
    return isNaN(d.getTime()) ? new Date(0) : d;
}

function rsApplyTabFilter(records, filter) {
    if (filter === 'today') return records.filter(r => rsIsToday(rsParseDate(r.submitted_at)));
    if (filter === 'week')  return records.filter(r => rsIsThisWeek(rsParseDate(r.submitted_at)));
    if (filter === 'month') return records.filter(r => rsIsThisMonth(rsParseDate(r.submitted_at)));
    return records;
}

// ── Init ──────────────────────────────────────────────────────────────────────
function rsApplyAllFilters() {
    const byTab = rsApplyTabFilter(rsAllRecords, rsActiveFilter);
    return window.SkArchive
        ? SkArchive.filterByArchiveTerm(byTab, rsArchiveTerm, ['submitted_at', 'rejected_at'])
        : byTab;
}

function initRejectedScholarship() {
    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rsArchiveTerm = termId;
            loadRecords();
            renderStats();
            renderTable();
        });
    } else {
        loadRecords();
        renderStats();
        renderTable();
    }
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
}

// ── Load from localStorage ────────────────────────────────────────────────────
function loadRecords() {
    const all = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    rsAllRecords = all.filter(r => r.status === 'Rejected').map(r => ({
        ...r,
        skTerm: r.skTerm || (window.SkArchive
            ? SkArchive.inferTermFromDate(rsParseDate(r.submitted_at || r.rejected_at))
            : '2025-2027'),
    }));
    rsFiltered = rsApplyAllFilters();
}

function saveRecords(records) {
    localStorage.setItem('scholarship_requests', JSON.stringify(records));
}

// ── Stats ─────────────────────────────────────────────────────────────────────
function renderStats() {
    const row = document.getElementById('rsStatsRow');
    if (!row) return;

    const total = rsAllRecords.length;
    const month = rsAllRecords.filter(r => rsIsThisMonth(rsParseDate(r.submitted_at))).length;
    const today = rsAllRecords.filter(r => rsIsToday(rsParseDate(r.submitted_at))).length;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
            </div>
            <span class="stat-card-label">Total Rejected</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>
                    </svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

// ── Filter Tabs ───────────────────────────────────────────────────────────────
function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            rsActiveFilter = this.dataset.filter;

            const labels = {
                all:   'All Rejected Records',
                today: 'Rejected Today',
                week:  'Rejected This Week',
                month: 'Rejected This Month',
            };
            const label = document.getElementById('rsSectionLabel');
            if (label) label.textContent = labels[rsActiveFilter] || 'Rejected Records';

            rsFiltered = rsApplyAllFilters();
            rsCurrentPage = 1;
            renderTable();
        });
    });
}

// ── Render Table ──────────────────────────────────────────────────────────────
function renderTable() {
    const tbody = document.getElementById('rejectedScholTableBody');
    const info  = document.getElementById('rejectedScholPaginationInfo');
    if (!tbody) return;

    const start = (rsCurrentPage - 1) * rsPerPage;
    const end   = start + rsPerPage;
    const page  = rsFiltered.slice(start, end);

    if (rsFiltered.length === 0) {
        tbody.innerHTML = `<tr class="empty-state-row"><td colspan="6">No rejected scholarship applications found.</td></tr>`;
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r, idx) => {
        const name = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}`;
        return `
        <tr>
            <td>${start + idx + 1}</td>
            <td style="text-align:left;font-weight:600;color:#111827;">${name}</td>
            <td style="text-align:left;font-size:12px;">${r.school_name || '—'}</td>
            <td><span class="rs-status-pill">Rejected</span></td>
            <td><span class="rs-date-badge">${r.submitted_at || '—'}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-action="view" data-id="${r.id}">View</button>
                    ${(window.SkArchive && SkArchive.canRestoreRecord(r, ['submitted_at', 'rejected_at']))
                        ? `<button class="btn-restore-action" data-action="restore" data-id="${r.id}">Restore</button>`
                        : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, rsFiltered.length)} of ${rsFiltered.length} records`;

    renderPagination(rsFiltered.length);

    tbody.querySelectorAll('.btn-view-action').forEach(btn => {
        btn.addEventListener('click', function () { openViewModal(parseInt(this.dataset.id, 10)); });
    });
    tbody.querySelectorAll('.btn-restore-action').forEach(btn => {
        btn.addEventListener('click', function () { openRestoreModal(parseInt(this.dataset.id, 10)); });
    });
}

// ── Pagination ────────────────────────────────────────────────────────────────
function renderPagination(total) {
    const pages = Math.ceil(total / rsPerPage);
    const nums  = document.getElementById('rejectedScholPageNumbers');
    const prev  = document.getElementById('rejectedScholPrevBtn');
    const next  = document.getElementById('rejectedScholNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button class="pagination-btn ${i + 1 === rsCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { rsCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = rsCurrentPage === 1; prev.onclick = () => { rsCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = rsCurrentPage >= pages || pages === 0; next.onclick = () => { rsCurrentPage++; renderTable(); }; }
}

// ── Search ────────────────────────────────────────────────────────────────────
function bindSearch() {
    const input = document.getElementById('rejectedScholSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        const base = rsApplyAllFilters();
        rsFiltered = base.filter(r => {
            const name   = `${r.last_name || ''} ${r.first_name || ''}`.toLowerCase();
            const school = (r.school_name || '').toLowerCase();
            return name.includes(q) || school.includes(q);
        });
        rsCurrentPage = 1;
        renderTable();
    });
}

// ── View Modal ────────────────────────────────────────────────────────────────
function rsFormatPurpose(r) {
    const list = r.purpose_list || [];
    if (!list.length) return r.purpose || '—';
    return list.map(p => {
        if (String(p).toLowerCase() === 'others' && r.purpose_others) {
            return `Others (${r.purpose_others})`;
        }
        return p;
    }).join(', ');
}

function rsFormatRejectionReason(r) {
    if (Array.isArray(r.rejection_reasons) && r.rejection_reasons.length) {
        return r.rejection_reasons.join(', ');
    }
    return r.rejection_reason || r.review_notes || '—';
}

function openViewModal(id) {
    const all = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    const r   = all.find(x => x.id === id);
    if (!r) return;

    const body = document.getElementById('rsViewModalBody');
    const L = window.SkRecordViewLayout;
    if (!body || !L) return;

    const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name : ''}`.replace(/^,\s*/, '');
    const rejectedDate = r.rejected_at || r.submitted_at;
    const rejectedTime = r.rejected_time || r.submitted_time;
    const F = L.profileField;
    const R = L.profileRow;
    const S = L.profileSection;

    body.innerHTML = `
        <div class="record-view-profile-layout">
            ${S('Personal Information', 'fa-solid fa-user', `
                <p class="record-view-fullname">${L.escHtml(fullName)}</p>
                ${R(
                    F('Date of Birth', r.date_of_birth, 'fa-solid fa-calendar-day') +
                    F('Gender', r.gender, 'fa-solid fa-venus-mars') +
                    F('Age', r.age) +
                    F('Contact Number', r.contact_no, 'fa-solid fa-mobile-screen')
                )}
                ${R(
                    F('Complete Address', r.address, 'fa-solid fa-location-dot') +
                    F('Email Address', r.email, 'fa-solid fa-envelope')
                )}
            `)}
            ${S('Academic Information', 'fa-solid fa-graduation-cap', `
                ${R(F('Name of School', r.school_name))}
                ${R(F('School Address', r.school_address))}
                ${R(
                    F('Year / Grade Level', r.year_level) +
                    F('Program / Strand', r.program_strand)
                )}
            `)}
            ${S('Scholarship Information', 'fa-solid fa-file-lines', R(
                F('Purpose of Scholarship', rsFormatPurpose(r)) +
                F('COR – Certified True Copy', r.cor_certified ? 'Submitted' : 'Not submitted') +
                F('Photo Copy of ID', r.photo_id ? 'Submitted' : 'Not submitted')
            ))}
            ${L.profileMetaSection('Rejection Details', 'fa-solid fa-circle-xmark', `
                ${R(F('Reason', rsFormatRejectionReason(r)))}
                ${R(
                    F('Rejected Date', rejectedDate, 'fa-solid fa-calendar-day') +
                    F('Rejected Time', rejectedTime, 'fa-solid fa-clock')
                )}
            `)}
        </div>`;

    const modal = document.getElementById('rsViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal     = document.getElementById('rsViewModal');
    const box       = document.getElementById('rsViewModalBox');
    const closeBtn  = document.getElementById('rsViewModalClose');
    const toggleBtn = document.getElementById('rsViewModalToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('view-modal-maximized'); }
        if (box)   box.classList.remove('view-modal-maximized');
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

// ── Restore Modal ─────────────────────────────────────────────────────────────
function openRestoreModal(id) {
    const all    = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
    const record = all.find(r => r.id === id);
    if (!record) return;
    const termId = window.SkArchive
        ? SkArchive.resolveRecordTerm(record, ['submitted_at', 'rejected_at'])
        : '2025-2027';
    if (window.SkArchive && SkArchive.isArchivedTerm(termId)) {
        alert('This record is from a past SK term and cannot be restored. View-only archive.');
        return;
    }

    rsPendingRestoreId = id;
    const nameEl = document.getElementById('rsRestoreName');
    if (nameEl) nameEl.textContent = `${record.last_name || ''}, ${record.first_name || ''}`;

    const modal = document.getElementById('rsRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    rsPendingRestoreId = null;
    const modal = document.getElementById('rsRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn  = document.getElementById('rsRestoreCancelBtn');
    const confirmBtn = document.getElementById('rsRestoreConfirmBtn');
    const modal      = document.getElementById('rsRestoreModal');

    if (cancelBtn) cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal)     modal.addEventListener('click', e => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!rsPendingRestoreId) return;

            // Update status in localStorage
            const all = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
            const idx = all.findIndex(r => r.id === rsPendingRestoreId);
            let name  = 'Record';

            if (idx !== -1) {
                name = `${all[idx].last_name || ''}, ${all[idx].first_name || ''}`;
                all[idx].status = 'Pending';
                saveRecords(all);
            }

            closeRestoreModal();

            // Reload and re-render
            loadRecords();
            rsCurrentPage = 1;
            renderStats();
            renderTable();

            showRestoreBanner(`${name} has been restored to the active scholarship list.`);
        });
    }
}

// ── Restore Success Banner ────────────────────────────────────────────────────
function showRestoreBanner(message) {
    const banner = document.getElementById('rsRestoreBanner');
    const text   = document.getElementById('rsRestoreBannerText');
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}
