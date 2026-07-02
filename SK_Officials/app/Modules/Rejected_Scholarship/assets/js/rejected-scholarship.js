'use strict';

document.addEventListener('DOMContentLoaded', () => initRejectedScholarship());

const DATA_URL = '/rejected-scholars/data';
const RESTORE_URL = (id) => `/rejected-scholars/${id}/restore`;
const PROGRAM_LETTER = 'A';

let rsAllRecords = [];
let rsFiltered = [];
let rsCurrentPage = 1;
let rsRecordsPerPage = 10;
let rsTablePagination = null;
let rsPendingRestoreId = null;
let rsActiveFilter = 'all';
let rsArchiveTerm = '';
let rsSearchQuery = '';
let rsIsLoading = false;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function rsShowToast(message) {
    if (typeof window.showScholarshipToast === 'function') {
        window.showScholarshipToast(message);
    }
}

function initRejectedScholarship() {
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
    bindRejectedTableActions();

    if (typeof window.bindTablePageFooter === 'function') {
        rsTablePagination = window.bindTablePageFooter({
            prefix: 'scholRej',
            getTotalRecords: () => rsFiltered.length,
            getCurrentPage: () => rsCurrentPage,
            setCurrentPage: (page) => { rsCurrentPage = page; },
            getRecordsPerPage: () => rsRecordsPerPage,
            setRecordsPerPage: (value) => { rsRecordsPerPage = value; },
            onPageChange: () => renderTable(),
        });
    }

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rsArchiveTerm = termId;
            applyClientFilters();
            rsCurrentPage = 1;
            renderTable();
        }).then(() => loadData());
        return;
    }

    loadData();
}

async function loadData() {
    if (rsIsLoading) return;
    rsIsLoading = true;
    setTableLoading(true);

    const params = new URLSearchParams();
    if (rsSearchQuery) params.set('search', rsSearchQuery);
    if (rsActiveFilter !== 'all') params.set('filter', rsActiveFilter);

    try {
        const res = await fetch(`${DATA_URL}?${params}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('Failed to load rejected records.');
        const json = await res.json();
        rsAllRecords = (json.data || []).map(normalizeRecord);
        renderStats(json.stats || {});
        applyClientFilters();
        rsCurrentPage = 1;
        renderTable();
    } catch (err) {
        rsAllRecords = [];
        rsFiltered = [];
        renderStats({ total: 0, today: 0, month: 0 });
        renderTable();
        rsShowToast(err.message || 'Failed to load rejected records.');
    } finally {
        rsIsLoading = false;
        setTableLoading(false);
    }
}

function normalizeRecord(r) {
    return {
        ...r,
        _rejectedTs: r.rejected_at ? new Date(r.rejected_at) : null,
        skTerm: window.SkArchive && r.rejected_at
            ? SkArchive.inferTermFromDate(r.rejected_at)
            : (window.SkArchive?.getActiveTermId?.() || ''),
    };
}

function applyClientFilters() {
    let list = rsAllRecords.slice();
    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, rsArchiveTerm, ['_rejectedTs', 'rejected_at']);
    }
    list.sort((a, b) => {
        const ln = (a.last_name || '').localeCompare(b.last_name || '');
        if (ln !== 0) return ln;
        return (a.first_name || '').localeCompare(b.first_name || '');
    });
    rsFiltered = list;
}

function setTableLoading(loading) {
    const tbody = document.getElementById('rejectedScholTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML = '<tr class="empty-state-row"><td colspan="6">Loading rejected records…</td></tr>';
}

function renderStats(stats) {
    const row = document.getElementById('rsStatsRow');
    if (!row) return;
    const total = stats.total ?? 0;
    const month = stats.month ?? 0;
    const today = stats.today ?? 0;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Rejected</span>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-top">
                <span class="stat-card-value">${month}</span>
                <div class="stat-card-icon stat-icon-orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
            <span class="stat-card-label">This Month</span>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-top">
                <span class="stat-card-value">${today}</span>
                <div class="stat-card-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <span class="stat-card-label">Today</span>
        </div>`;
}

function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            rsActiveFilter = this.dataset.filter;

            const labels = {
                all: 'All Rejected Records',
                today: 'Rejected Today',
                week: 'Rejected This Week',
                month: 'Rejected This Month',
            };
            const label = document.getElementById('rsSectionLabel');
            if (label) label.textContent = labels[rsActiveFilter] || 'Rejected Records';

            loadData();
        });
    });
}

function rsRenderActionMenuCell(record) {
    const canRestore = window.SkArchive
        ? SkArchive.canRestoreRecord(record, ['_rejectedTs', 'rejected_at'])
        : true;

    const restoreItem = canRestore
        ? `<button type="button" class="row-actions-item row-actions-item-restore" data-action="restore" data-id="${record.id}" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                <span>Restore</span>
           </button>`
        : `<button type="button" class="row-actions-item is-disabled" disabled title="Past term — view only" role="menuitem">
                <span>Restore</span>
           </button>`;

    return `
        <div class="row-actions-menu">
            <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
            <div class="row-actions-dropdown" role="menu">
                <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-id="${record.id}" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                </button>
                ${restoreItem}
            </div>
        </div>`;
}

function bindRejectedTableActions() {
    const tbody = document.getElementById('rejectedScholTableBody');
    if (!tbody || tbody.dataset.rsActionsBound === '1') return;
    tbody.dataset.rsActionsBound = '1';

    if (typeof window.bindRowActionsTable === 'function') {
        window.bindRowActionsTable(tbody);
    }

    tbody.addEventListener('click', (event) => {
        const btn = event.target.closest('.row-actions-item[data-action]');
        if (!btn || btn.disabled) return;

        const action = btn.getAttribute('data-action');
        const id = parseInt(btn.getAttribute('data-id'), 10);
        if (!id) return;

        if (typeof window.closeAllRowActionMenus === 'function') {
            window.closeAllRowActionMenus();
        }

        if (action === 'view') {
            openViewModal(id);
            return;
        }

        if (action === 'restore') {
            openRestoreModal(id);
        }
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedScholTableBody');
    if (!tbody) return;

    applyClientFilters();
    const pageRows = typeof window.paginateSlice === 'function'
        ? window.paginateSlice(rsFiltered, rsCurrentPage, rsRecordsPerPage)
        : rsFiltered;

    if (rsFiltered.length === 0) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="6">No rejected scholarship applications found.</td></tr>';
        if (rsTablePagination) rsTablePagination.updateFooter();
        return;
    }

    const start = (rsCurrentPage - 1) * rsRecordsPerPage;
    tbody.innerHTML = pageRows.map((r, idx) => {
        const name = r.full_name || `${r.last_name || ''}, ${r.first_name || ''}`.replace(/^,\s*/, '');

        return `
        <tr>
            <td>${start + idx + 1}</td>
            <td style="text-align:left;font-weight:600;color:#111827;">${escapeHtml(name)}</td>
            <td style="text-align:left;font-size:12px;">${escapeHtml(r.school_name || '—')}</td>
            <td><span class="rs-status-pill">Rejected</span></td>
            <td><span class="rs-date-badge">${escapeHtml(r.date_submitted || '—')}</span></td>
            <td class="col-actions">${rsRenderActionMenuCell(r)}</td>
        </tr>`;
    }).join('');

    if (rsTablePagination) rsTablePagination.updateFooter();
}

function bindSearch() {
    const input = document.getElementById('rejectedScholSearch');
    if (!input) return;
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            rsSearchQuery = this.value.trim();
            loadData();
        }, 300);
    });
}

function formatRejectionReason(r) {
    if (Array.isArray(r.rejection_reasons) && r.rejection_reasons.length) {
        return r.rejection_reasons.join(', ');
    }
    return r.rejection_reason || '—';
}

async function openViewModal(id) {
    const r = rsAllRecords.find((x) => x.id === id);
    if (!r) return;

    const body = document.getElementById('rsViewModalBody');
    const modal = document.getElementById('rsViewModal');
    if (!body || !modal) return;

    const SV = window.ScholarshipViewShared;
    const esc = (value) => escapeHtml(value);

    try {
        const res = await fetch(`/api/program-applications/${id}?letter=${PROGRAM_LETTER}`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || 'Failed to load application details.');

        const app = json.data || r;
        const detail = SV?.mapScholarshipApplicationDetail
            ? SV.mapScholarshipApplicationDetail(app)
            : app;
        const rejectionHtml = `
            <section class="sch-app-view-section" style="margin-bottom:14px;">
                <div class="sch-app-view-section-head" style="background:linear-gradient(90deg,#b91c1c 0%,#dc2626 100%);">
                    <span class="sch-app-view-step">!</span>
                    <h4 class="sch-app-view-section-title">Rejection Details</h4>
                </div>
                <div class="sch-app-view-section-body">
                    <div class="sch-app-view-grid sch-app-view-grid-2">
                        <div class="sch-app-view-field">
                            <span class="sch-app-view-label">Reason</span>
                            <span class="sch-app-view-value">${esc(formatRejectionReason(r))}</span>
                        </div>
                        <div class="sch-app-view-field">
                            <span class="sch-app-view-label">Rejected On</span>
                            <span class="sch-app-view-value">${esc([r.rejected_date, r.rejected_time].filter(Boolean).join(' ') || '—')}</span>
                        </div>
                    </div>
                </div>
            </section>`;

        body.innerHTML = SV?.renderApplicationViewBody
            ? SV.renderApplicationViewBody(detail, { extraHtml: rejectionHtml })
            : rejectionHtml;

        modal.style.display = 'flex';
    } catch (err) {
        rsShowToast(err.message || 'Failed to load application details.');
    }
}

function bindViewModal() {
    const modal = document.getElementById('rsViewModal');
    const box = document.getElementById('rsViewModalBox');
    const closeBtn = document.getElementById('rsViewModalClose');
    const toggleBtn = document.getElementById('rsViewModalToggle');

    const close = () => {
        if (modal) { modal.style.display = 'none'; modal.classList.remove('view-modal-maximized'); }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

    if (toggleBtn && box) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !box.classList.contains('view-modal-maximized');
            modal.classList.toggle('view-modal-maximized', isMax);
            box.classList.toggle('view-modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }
}

function openRestoreModal(id) {
    const record = rsAllRecords.find((r) => r.id === id);
    if (!record) return;

    if (window.SkArchive && SkArchive.isArchivedTerm(record.skTerm)) {
        rsShowToast('This record is from a past SK term and cannot be restored.');
        return;
    }

    rsPendingRestoreId = id;
    const nameEl = document.getElementById('rsRestoreName');
    if (nameEl) nameEl.textContent = record.full_name || `${record.last_name || ''}, ${record.first_name || ''}`;
    document.getElementById('rsRestoreModal').style.display = 'flex';
}

function closeRestoreModal() {
    rsPendingRestoreId = null;
    const modal = document.getElementById('rsRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    document.getElementById('rsRestoreCancelBtn')?.addEventListener('click', closeRestoreModal);
    document.getElementById('rsRestoreModal')?.addEventListener('click', (e) => {
        if (e.target?.id === 'rsRestoreModal') closeRestoreModal();
    });

    document.getElementById('rsRestoreConfirmBtn')?.addEventListener('click', async () => {
        if (!rsPendingRestoreId) return;

        const confirmBtn = document.getElementById('rsRestoreConfirmBtn');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'Restoring…'; }

        try {
            const res = await fetch(RESTORE_URL(rsPendingRestoreId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Restore failed.');

            closeRestoreModal();
            rsShowToast(`${data.full_name || 'Record'} has been restored to Scholarship Applications.`);
            loadData();
        } catch (err) {
            rsShowToast(err.message || 'Restore failed.');
        } finally {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Restore'; }
        }
    });
}
