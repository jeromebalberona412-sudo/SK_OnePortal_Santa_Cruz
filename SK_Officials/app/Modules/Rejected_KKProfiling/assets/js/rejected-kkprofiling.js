import { populateKkProfilingView, mapRegistrationToKkView } from '../../../KKProfilingRequests/assets/js/kk-profiling-view-populate.js';

document.addEventListener('DOMContentLoaded', function () {
    initRejectedKK();
});

let rkkRecords = [];
let rkkFiltered = [];
let rkkCurrentPage = 1;
const rkkPerPage = 10;
let rkkPendingRestoreId = null;
let rkkActiveFilter = 'all';
let rkkArchiveTerm = '2025-2027';
let rkkSearchQuery = '';
let rkkIsLoading = false;

function initRejectedKK() {
    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rkkArchiveTerm = termId;
            rkkApplyClientFilters();
            rkkCurrentPage = 1;
            renderTable();
        });
    }
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();
    loadData();
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function loadData() {
    if (rkkIsLoading) return;
    rkkIsLoading = true;
    setTableLoading(true);

    const params = new URLSearchParams();
    if (rkkSearchQuery) params.set('search', rkkSearchQuery);
    if (rkkActiveFilter !== 'all') params.set('filter', rkkActiveFilter);

    try {
        const res = await fetch(`/rejected-kkprofiling/data?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('Failed to load rejected records.');
        const json = await res.json();
        rkkRecords = (json.data || []).map(normalizeRecord);
        renderStats(json.stats || {});
        rkkApplyClientFilters();
        rkkCurrentPage = 1;
        renderTable();
    } catch (err) {
        showToast(err.message || 'Failed to load rejected records.', 'error');
        rkkRecords = [];
        rkkFiltered = [];
        renderStats({ total: 0, today: 0, month: 0 });
        renderTable();
    } finally {
        rkkIsLoading = false;
        setTableLoading(false);
    }
}

function normalizeRecord(r) {
    return {
        ...r,
        _rejectedTs: r.rejected_at ? new Date(r.rejected_at) : null,
        skTerm: window.SkArchive && r.rejected_at
            ? SkArchive.inferTermFromDate(r.rejected_at)
            : '2025-2027',
    };
}

function rkkApplyClientFilters() {
    let list = rkkRecords.slice();
    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, rkkArchiveTerm, ['_rejectedTs', 'rejected_at']);
    }
    rkkFiltered = list;
}

function setTableLoading(loading) {
    const tbody = document.getElementById('rejectedKKTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML = '<tr class="empty-state-row"><td colspan="10">Loading rejected records…</td></tr>';
}

function renderStats(stats) {
    const row = document.getElementById('rkkStatsRow');
    if (!row) return;
    const total = stats.total ?? 0;
    const month = stats.month ?? 0;
    const today = stats.today ?? 0;

    row.innerHTML = `
        <div class="stat-card stat-card-red">
            <div class="stat-card-top">
                <span class="stat-card-value">${total}</span>
                <div class="stat-card-icon stat-icon-red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <span class="stat-card-label">Total Rejected</span>
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
    document.querySelectorAll('.filter-tab').forEach((btn) => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach((b) => b.classList.remove('active'));
            this.classList.add('active');
            rkkActiveFilter = this.dataset.filter;
            const labels = {
                all: 'All Rejected Records',
                today: 'Rejected Today',
                week: 'Rejected This Week',
                month: 'Rejected This Month',
            };
            const label = document.getElementById('rkkSectionLabel');
            if (label) label.textContent = labels[rkkActiveFilter] || 'Rejected Records';
            loadData();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedKKTableBody');
    const info = document.getElementById('rejectedKKPaginationInfo');
    if (!tbody) return;

    const start = (rkkCurrentPage - 1) * rkkPerPage;
    const end = start + rkkPerPage;
    const page = rkkFiltered.slice(start, end);

    if (rkkFiltered.length === 0) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="10">No rejected KK Profiling records found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r) => {
        const fullName = `${r.last_name}, ${r.first_name}${r.middle_name ? ' ' + r.middle_name : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        const canRestore = window.SkArchive ? SkArchive.canRestoreRecord(r, ['_rejectedTs', 'rejected_at']) : true;
        const restoreBtn = canRestore
            ? `<button type="button" class="btn-restore-action" data-id="${r.id}">Restore</button>`
            : `<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>`;

        return `
        <tr>
            <td>${esc(r.respondent_display || '—')}</td>
            <td style="font-weight:600;color:#111827;">${esc(fullName)}</td>
            <td>${esc(r.age || '—')}</td>
            <td>${esc(r.sex || '—')}</td>
            <td>${esc(r.purok_zone || '—')}</td>
            <td>${esc(r.youth_classification || '—')}</td>
            <td><span class="rejection-reason-cell" title="${esc(r.rejection_reason || '')}">${esc(r.rejection_reason || '—')}</span></td>
            <td><span class="deleted-at-badge">${esc(r.rejected_date || '—')}</span></td>
            <td><span class="deleted-time-badge">${esc(r.rejected_time || '—')}</span></td>
            <td>
                <div class="action-btns">
                    <button type="button" class="btn-view-action" data-id="${r.id}">View</button>
                    ${restoreBtn}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) {
        info.textContent = `Showing ${start + 1}–${Math.min(end, rkkFiltered.length)} of ${rkkFiltered.length} records`;
    }

    renderPagination(rkkFiltered.length);

    tbody.querySelectorAll('.btn-restore-action:not(.is-disabled)').forEach((btn) => {
        btn.addEventListener('click', function () { openRestoreModal(this.dataset.id); });
    });
    tbody.querySelectorAll('.btn-view-action').forEach((btn) => {
        btn.addEventListener('click', function () { openViewModal(this.dataset.id); });
    });
}

function esc(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function renderPagination(total) {
    const pages = Math.ceil(total / rkkPerPage) || 0;
    const nums = document.getElementById('rejectedKKPageNumbers');
    const prev = document.getElementById('rejectedKKPrevBtn');
    const next = document.getElementById('rejectedKKNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) => `
            <button type="button" class="pagination-btn ${i + 1 === rkkCurrentPage ? 'active' : ''}">${i + 1}</button>
        `).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { rkkCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) {
        prev.disabled = rkkCurrentPage === 1;
        prev.onclick = () => { if (rkkCurrentPage > 1) { rkkCurrentPage--; renderTable(); } };
    }
    if (next) {
        next.disabled = rkkCurrentPage >= pages || pages === 0;
        next.onclick = () => { if (rkkCurrentPage < pages) { rkkCurrentPage++; renderTable(); } };
    }
}

function bindSearch() {
    const input = document.getElementById('rejectedKKSearch');
    if (!input) return;

    let debounce = null;
    input.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            rkkSearchQuery = this.value.trim();
            loadData();
        }, 300);
    });
}

function openViewModal(id) {
    const record = rkkRecords.find((r) => String(r.id) === String(id));
    if (!record) return;

    const reasonEl = document.getElementById('rkkViewRejectionReason');
    const dateEl = document.getElementById('rkkViewRejectedDate');
    const timeEl = document.getElementById('rkkViewRejectedTime');
    if (reasonEl) reasonEl.textContent = record.rejection_reason || '—';
    if (dateEl) dateEl.textContent = record.rejected_date || '—';
    if (timeEl) timeEl.textContent = record.rejected_time || '—';

    const viewData = mapRegistrationToKkView(record);
    populateKkProfilingView(viewData, {
        showRejection: true,
        rejectionReason: record.rejection_reason || '',
    });

    const modal = document.getElementById('rkkViewModal');
    if (modal) modal.style.display = 'flex';
}

function bindViewModal() {
    const modal = document.getElementById('rkkViewModal');
    const box = document.getElementById('rkkViewModalBox');
    const closeBtn = document.getElementById('rkkViewModalClose');
    const toggleBtn = document.getElementById('rkkViewModalToggle');

    const close = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('view-modal-maximized');
        }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    };

    if (closeBtn) closeBtn.addEventListener('click', close);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

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
    const record = rkkRecords.find((r) => String(r.id) === String(id));
    if (!record) return;

    if (window.SkArchive && !SkArchive.canRestoreRecord(record, ['_rejectedTs', 'rejected_at'])) {
        showToast('This record is from a past SK term and cannot be restored. View-only archive.', 'error');
        return;
    }

    rkkPendingRestoreId = id;
    const nameEl = document.getElementById('rkkRestoreName');
    if (nameEl) {
        nameEl.textContent = `${record.last_name}, ${record.first_name}${record.middle_name ? ' ' + record.middle_name : ''}`;
    }
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'flex';
}

function closeRestoreModal() {
    rkkPendingRestoreId = null;
    const modal = document.getElementById('rkkRestoreModal');
    if (modal) modal.style.display = 'none';
}

function bindRestoreModal() {
    const cancelBtn = document.getElementById('rkkRestoreCancelBtn');
    const confirmBtn = document.getElementById('rkkRestoreConfirmBtn');
    const modal = document.getElementById('rkkRestoreModal');

    if (cancelBtn) cancelBtn.addEventListener('click', closeRestoreModal);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeRestoreModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            if (!rkkPendingRestoreId || confirmBtn.disabled) return;

            const record = rkkRecords.find((r) => String(r.id) === String(rkkPendingRestoreId));
            const name = record ? `${record.last_name}, ${record.first_name}` : 'Record';
            const defaultHtml = confirmBtn.innerHTML;

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="calendar-action-spinner"></span> Restoring…';

            try {
                const res = await fetch(`/rejected-kkprofiling/${rkkPendingRestoreId}/restore`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                });
                const json = await res.json();
                if (!res.ok || !json.success) {
                    throw new Error(json.message || 'Failed to restore record.');
                }

                closeRestoreModal();
                showRestoreBanner(`${name} has been restored to KK Profiling Requests.`);
                await loadData();
            } catch (err) {
                showToast(err.message || 'Failed to restore record.', 'error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = defaultHtml;
            }
        });
    }
}

function showRestoreBanner(message) {
    const banner = document.getElementById('rkkRestoreBanner');
    const text = document.getElementById('rkkRestoreBannerText');
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}

function showToast(message, type) {
    const toast = document.getElementById('rkkToast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'dk-toast show' + (type === 'error' ? ' dk-toast-error' : '');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}
