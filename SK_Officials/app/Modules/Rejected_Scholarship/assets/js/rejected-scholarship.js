'use strict';

document.addEventListener('DOMContentLoaded', () => initRejectedScholarship());

const DATA_URL = '/rejected-scholars/data';
const RESTORE_URL = (id) => `/rejected-scholars/${id}/restore`;

let rsAllRecords = [];
let rsFiltered = [];
let rsCurrentPage = 1;
const rsPerPage = 10;
let rsPendingRestoreId = null;
let rsActiveFilter = 'all';
let rsArchiveTerm = '2025-2027';
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

function initRejectedScholarship() {
    bindSearch();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rsArchiveTerm = termId;
            applyClientFilters();
            rsCurrentPage = 1;
            renderTable();
        });
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
        alert(err.message || 'Failed to load rejected records.');
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
            : '2025-2027',
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

function renderTable() {
    const tbody = document.getElementById('rejectedScholTableBody');
    const info = document.getElementById('rejectedScholPaginationInfo');
    if (!tbody) return;

    applyClientFilters();
    const start = (rsCurrentPage - 1) * rsPerPage;
    const end = start + rsPerPage;
    const page = rsFiltered.slice(start, end);

    if (rsFiltered.length === 0) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="6">No rejected scholarship applications found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r, idx) => {
        const name = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}`;
        const canRestore = window.SkArchive
            ? SkArchive.canRestoreRecord(r, ['_rejectedTs', 'rejected_at'])
            : true;

        return `
        <tr>
            <td>${start + idx + 1}</td>
            <td style="text-align:left;font-weight:600;color:#111827;">${escapeHtml(name)}</td>
            <td style="text-align:left;font-size:12px;">${escapeHtml(r.school_name || '—')}</td>
            <td><span class="rs-status-pill">Rejected</span></td>
            <td><span class="rs-date-badge">${escapeHtml(r.date_submitted || '—')}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-action="view" data-id="${r.id}">View</button>
                    ${canRestore
                        ? `<button class="btn-restore-action" data-action="restore" data-id="${r.id}">Restore</button>`
                        : '<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>'}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, rsFiltered.length)} of ${rsFiltered.length} records`;
    renderPagination(rsFiltered.length);

    tbody.querySelectorAll('.btn-view-action').forEach((btn) => {
        btn.addEventListener('click', () => openViewModal(parseInt(btn.dataset.id, 10)));
    });
    tbody.querySelectorAll('.btn-restore-action:not(.is-disabled)').forEach((btn) => {
        btn.addEventListener('click', () => openRestoreModal(parseInt(btn.dataset.id, 10)));
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / rsPerPage) || 1;
    const nums = document.getElementById('rejectedScholPageNumbers');
    const prev = document.getElementById('rejectedScholPrevBtn');
    const next = document.getElementById('rejectedScholNextBtn');

    if (nums) {
        nums.innerHTML = Array.from({ length: pages }, (_, i) =>
            `<button class="pagination-btn ${i + 1 === rsCurrentPage ? 'active' : ''}">${i + 1}</button>`
        ).join('');
        nums.querySelectorAll('.pagination-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => { rsCurrentPage = i + 1; renderTable(); });
        });
    }
    if (prev) { prev.disabled = rsCurrentPage === 1; prev.onclick = () => { rsCurrentPage--; renderTable(); }; }
    if (next) { next.disabled = rsCurrentPage >= pages || pages === 0; next.onclick = () => { rsCurrentPage++; renderTable(); }; }
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

function openViewModal(id) {
    const r = rsAllRecords.find((x) => x.id === id);
    if (!r) return;

    const body = document.getElementById('rsViewModalBody');
    if (!body) return;

    const fullName = r.full_name || `${r.last_name || ''}, ${r.first_name || ''}`.replace(/^,\s*/, '');
    const answers = (r.custom_answers || []).map((item, index) => `
        <div style="margin-bottom:12px;padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
            <div style="font-weight:600;margin-bottom:4px;">${index + 1}. ${escapeHtml(item.question_label || item.label || 'Question')}</div>
            <div style="color:#475569;">${escapeHtml(Array.isArray(item.answer) ? item.answer.join(', ') : (item.answer ?? '—'))}</div>
        </div>
    `).join('');

    body.innerHTML = `
        <div class="record-view-profile-layout">
            <p class="record-view-fullname">${escapeHtml(fullName)}</p>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 20px;margin-top:16px;">
                <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;">Program</div><div style="font-weight:600;">${escapeHtml(r.program_name || '—')}</div></div>
                <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;">School</div><div style="font-weight:600;">${escapeHtml(r.school_name || '—')}</div></div>
                <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;">Grade / Course</div><div style="font-weight:600;">${escapeHtml([r.grade_level, r.course].filter(Boolean).join(' · ') || '—')}</div></div>
                <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;">Contact</div><div style="font-weight:600;">${escapeHtml(r.contact_number || '—')}</div></div>
            </div>
            <div style="margin-top:20px;padding:14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;">
                <div style="font-size:12px;font-weight:700;color:#b91c1c;margin-bottom:8px;">Rejection Details</div>
                <div><strong>Reason:</strong> ${escapeHtml(formatRejectionReason(r))}</div>
                <div style="margin-top:6px;"><strong>Rejected:</strong> ${escapeHtml(r.rejected_date || '—')} ${escapeHtml(r.rejected_time || '')}</div>
            </div>
            ${answers ? `<div style="margin-top:18px;"><div style="font-weight:700;margin-bottom:10px;">Form Answers</div>${answers}</div>` : ''}
        </div>`;

    document.getElementById('rsViewModal').style.display = 'flex';
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
        alert('This record is from a past SK term and cannot be restored.');
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
            showRestoreBanner(`${data.full_name || 'Record'} has been restored to Scholarship Applications.`);
            loadData();
        } catch (err) {
            alert(err.message || 'Restore failed.');
        } finally {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Restore'; }
        }
    });
}

function showRestoreBanner(message) {
    const banner = document.getElementById('rsRestoreBanner');
    const text = document.getElementById('rsRestoreBannerText');
    if (!banner || !text) return;
    text.textContent = message;
    banner.style.display = 'flex';
    banner.classList.add('show');
    setTimeout(() => {
        banner.classList.remove('show');
        setTimeout(() => { banner.style.display = 'none'; }, 400);
    }, 4000);
}
