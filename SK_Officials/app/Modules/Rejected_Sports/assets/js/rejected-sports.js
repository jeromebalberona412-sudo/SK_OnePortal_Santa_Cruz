'use strict';

document.addEventListener('DOMContentLoaded', () => initRejectedSports());

const DATA_URL = '/rejected-sports/data';
const RESTORE_URL = (id) => `/rejected-sports/${id}/restore`;

let rspAllRecords = [];
let rspFiltered = [];
let rspCurrentPage = 1;
const rspPerPage = 10;
let rspPendingRestoreId = null;
let rspActiveFilter = 'all';
let rspArchiveTerm = '2025-2027';
let rspSearchQuery = '';
let rspTeamQuery = '';
let rspSportFilter = 'all';
let rspIsLoading = false;

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

function rspFormatName(r) {
    if (r.full_name) return r.full_name;
    const parts = [r.last_name, r.first_name, r.middle_name].filter(Boolean);
    return parts.join(', ');
}

function broadcastSportsEvent(type, applicationId) {
    const payload = {
        type,
        applicationId: Number(applicationId),
        at: Date.now(),
    };
    try {
        sessionStorage.setItem('sports-app-event', JSON.stringify(payload));
    } catch (_) { /* ignore */ }
    window.dispatchEvent(new CustomEvent('sports-app-event', { detail: payload }));
}

function listenSportsEvents(handler) {
    window.addEventListener('storage', (event) => {
        if (event.key !== 'sports-app-event' || !event.newValue) return;
        try { handler(JSON.parse(event.newValue)); } catch (_) { /* ignore */ }
    });
    window.addEventListener('sports-app-event', (event) => {
        if (event.detail) handler(event.detail);
    });
}

function initRejectedSports() {
    bindSearch();
    bindSportTeamFilters();
    bindFilterTabs();
    bindRestoreModal();
    bindViewModal();

    listenSportsEvents((payload) => {
        if (payload.type === 'rejected' || payload.type === 'revoked') {
            loadData(true);
        }
    });

    if (window.SkArchive) {
        SkArchive.mountShowArchiveFilter((termId) => {
            rspArchiveTerm = termId;
            applyClientFilters();
            rspCurrentPage = 1;
            renderTable();
        });
    }

    loadData();
}

async function loadData(silent = false) {
    if (rspIsLoading) return;
    rspIsLoading = true;
    if (!silent) setTableLoading(true);

    const params = new URLSearchParams();
    if (rspSearchQuery) params.set('search', rspSearchQuery);
    if (rspActiveFilter !== 'all') params.set('filter', rspActiveFilter);

    try {
        const res = await fetch(`${DATA_URL}?${params}`, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('Failed to load rejected records.');
        const json = await res.json();
        rspAllRecords = (json.data || []).map(normalizeRecord);
        renderStats(json.stats || {});
        applyClientFilters();
        rspCurrentPage = 1;
        renderTable();
    } catch (err) {
        rspAllRecords = [];
        rspFiltered = [];
        renderStats({ total: 0, today: 0, month: 0 });
        renderTable();
        alert(err.message || 'Failed to load rejected records.');
    } finally {
        rspIsLoading = false;
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
    let list = rspAllRecords.slice();

    const sportFilter = String(rspSportFilter || 'all').toLowerCase();
    const teamQuery = rspTeamQuery.trim().toLowerCase();

    if (sportFilter !== 'all') {
        list = list.filter((r) => String(r.sport_key || 'other').toLowerCase() === sportFilter);
    }

    if (teamQuery) {
        list = list.filter((r) => String(r.team_name || '').toLowerCase().includes(teamQuery));
    }

    if (window.SkArchive) {
        list = SkArchive.filterByArchiveTerm(list, rspArchiveTerm, ['_rejectedTs', 'rejected_at']);
    }
    list.sort((a, b) => {
        const ln = (a.last_name || '').localeCompare(b.last_name || '');
        if (ln !== 0) return ln;
        return (a.first_name || '').localeCompare(b.first_name || '');
    });
    rspFiltered = list;
}

function setTableLoading(loading) {
    const tbody = document.getElementById('rejectedSportsTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML = '<tr class="empty-state-row"><td colspan="8">Loading rejected records…</td></tr>';
}

function renderStats(stats) {
    const row = document.getElementById('rspStatsRow');
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
            rspActiveFilter = this.dataset.filter;

            const labels = {
                all: 'All Rejected Records',
                today: 'Rejected Today',
                week: 'Rejected This Week',
                month: 'Rejected This Month',
            };
            const label = document.getElementById('rspSectionLabel');
            if (label) label.textContent = labels[rspActiveFilter] || 'Rejected Records';

            loadData();
        });
    });
}

function renderTable() {
    const tbody = document.getElementById('rejectedSportsTableBody');
    const info = document.getElementById('rejectedSportsPaginationInfo');
    if (!tbody) return;

    applyClientFilters();
    const start = (rspCurrentPage - 1) * rspPerPage;
    const end = start + rspPerPage;
    const page = rspFiltered.slice(start, end);

    if (rspFiltered.length === 0) {
        tbody.innerHTML = '<tr class="empty-state-row"><td colspan="8">No rejected sports applications found.</td></tr>';
        if (info) info.textContent = 'No records found';
        renderPagination(0);
        return;
    }

    tbody.innerHTML = page.map((r) => {
        const reason = formatRejectionReason(r);
        const canRestore = window.SkArchive
            ? SkArchive.canRestoreRecord(r, ['_rejectedTs', 'rejected_at'])
            : true;

        return `
        <tr>
            <td style="font-weight:600;color:#111827;">${escapeHtml(rspFormatName(r))}</td>
            <td>${escapeHtml(r.sport_label || '—')}</td>
            <td>${escapeHtml(r.team_name || '—')}</td>
            <td>${escapeHtml(r.program_name || '—')}</td>
            <td><span class="rejection-reason-cell" title="${escapeHtml(reason)}">${escapeHtml(reason)}</span></td>
            <td><span class="deleted-at-badge">${escapeHtml(r.rejected_date || '—')}</span></td>
            <td><span class="deleted-time-badge">${escapeHtml(r.rejected_time || '—')}</span></td>
            <td>
                <div class="action-btns">
                    <button class="btn-view-action" data-id="${r.id}">View</button>
                    ${canRestore
                        ? `<button class="btn-restore-action" data-id="${r.id}">Restore</button>`
                        : '<button type="button" class="btn-restore-action is-disabled" disabled title="Past term — view only">Restore</button>'}
                </div>
            </td>
        </tr>`;
    }).join('');

    if (info) info.textContent = `Showing ${start + 1}–${Math.min(end, rspFiltered.length)} of ${rspFiltered.length} records`;
    renderPagination(rspFiltered.length);

    tbody.querySelectorAll('.btn-view-action').forEach((btn) => {
        btn.addEventListener('click', () => openViewModal(parseInt(btn.dataset.id, 10)));
    });
    tbody.querySelectorAll('.btn-restore-action:not(.is-disabled)').forEach((btn) => {
        btn.addEventListener('click', () => openRestoreModal(parseInt(btn.dataset.id, 10)));
    });
}

function renderPagination(total) {
    const pages = Math.ceil(total / rspPerPage) || 1;
    const nums = document.getElementById('rejectedSportsPageNumbers');
    const prev = document.getElementById('rejectedSportsPrevBtn');
    const next = document.getElementById('rejectedSportsNextBtn');

    if (prev) prev.disabled = rspCurrentPage <= 1;
    if (next) next.disabled = rspCurrentPage >= pages;

    if (!nums) return;
    nums.innerHTML = '';
    for (let i = 1; i <= pages; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pagination-number' + (i === rspCurrentPage ? ' active' : '');
        btn.textContent = String(i);
        btn.addEventListener('click', () => { rspCurrentPage = i; renderTable(); });
        nums.appendChild(btn);
    }
}

function bindSearch() {
    const input = document.getElementById('rejectedSportsSearch');
    if (!input) return;
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            rspSearchQuery = this.value.trim();
            loadData();
        }, 300);
    });
}

function bindSportTeamFilters() {
    const sportFilter = document.getElementById('rspSportFilter');
    const teamSearch = document.getElementById('rspTeamSearch');

    if (sportFilter) {
        sportFilter.addEventListener('change', () => {
            rspSportFilter = sportFilter.value || 'all';
            rspCurrentPage = 1;
            applyClientFilters();
            renderTable();
        });
    }

    if (teamSearch) {
        let timer;
        teamSearch.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => {
                rspTeamQuery = this.value.trim();
                rspCurrentPage = 1;
                applyClientFilters();
                renderTable();
            }, 200);
        });
    }
}

function formatRejectionReason(r) {
    if (Array.isArray(r.rejection_reasons) && r.rejection_reasons.length) {
        return r.rejection_reasons.join(', ');
    }
    return r.rejection_reason || '—';
}

function openRestoreModal(id) {
    const record = rspAllRecords.find((r) => r.id === id);
    if (!record) return;
    rspPendingRestoreId = id;
    document.getElementById('rspRestoreName').textContent = rspFormatName(record);
    document.getElementById('rspRestoreModal').style.display = 'flex';
}

function bindRestoreModal() {
    const modal = document.getElementById('rspRestoreModal');
    document.getElementById('rspRestoreCancelBtn')?.addEventListener('click', () => {
        rspPendingRestoreId = null;
        if (modal) modal.style.display = 'none';
    });

    document.getElementById('rspRestoreConfirmBtn')?.addEventListener('click', async () => {
        if (rspPendingRestoreId == null) return;
        const confirmBtn = document.getElementById('rspRestoreConfirmBtn');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'Restoring…'; }

        try {
            const res = await fetch(RESTORE_URL(rspPendingRestoreId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Restore failed.');

            rspPendingRestoreId = null;
            if (modal) modal.style.display = 'none';

            const banner = document.getElementById('rspRestoreBanner');
            const bannerText = document.getElementById('rspRestoreBannerText');
            if (banner && bannerText) {
                bannerText.textContent = `${data.full_name || 'Applicant'} was restored to Sports Program Requests.`;
                banner.style.display = 'flex';
                setTimeout(() => { banner.style.display = 'none'; }, 4000);
            }

            broadcastSportsEvent('restored', rspPendingRestoreId);
            loadData();
        } catch (err) {
            alert(err.message || 'Restore failed.');
        } finally {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Restore'; }
        }
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            rspPendingRestoreId = null;
            modal.style.display = 'none';
        }
    });
}

function isDocumentAnswer(answer) {
    return answer && typeof answer === 'object' && !Array.isArray(answer)
        && (answer.original_name || answer.preview_url || answer.download_url || answer.path);
}

function formatAnswerText(answer) {
    if (answer === null || answer === undefined || answer === '') return '—';
    if (isDocumentAnswer(answer)) return String(answer.original_name || 'Uploaded PDF');
    if (Array.isArray(answer)) return answer.join(', ');
    if (typeof answer === 'object') return answer.original_name ? String(answer.original_name) : '—';
    return String(answer);
}

function renderDocumentCard(answer) {
    const file = answer && typeof answer === 'object' ? answer : {};
    const previewUrl = file.preview_url || file.download_url || '#';
    const downloadUrl = file.download_url || previewUrl;
    const fileName = file.original_name || 'Uploaded PDF';
    const meta = [file.size_display, file.question_label].filter(Boolean).join(' • ');

    return `
        <div style="display:flex;gap:14px;align-items:flex-start;padding:14px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;">
            <div style="width:44px;height:44px;border-radius:8px;background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">PDF</div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:600;color:#111827;word-break:break-word;">${escapeHtml(fileName)}</div>
                ${meta ? `<div style="font-size:12px;color:#6b7280;margin-top:4px;">${escapeHtml(meta)}</div>` : ''}
                <div style="display:flex;gap:12px;margin-top:10px;flex-wrap:wrap;">
                    <a href="${escapeHtml(previewUrl)}" target="_blank" rel="noopener" style="font-size:13px;font-weight:600;color:#213F99;text-decoration:none;">Preview</a>
                    <a href="${escapeHtml(downloadUrl)}" target="_blank" rel="noopener" style="font-size:13px;font-weight:600;color:#213F99;text-decoration:none;">Download</a>
                </div>
            </div>
        </div>`;
}

function renderFormAnswers(customAnswers) {
    const answers = customAnswers || [];
    if (!answers.length) return '';

    return answers.map((item, index) => {
        const isFile = item.question_type === 'file' || isDocumentAnswer(item.answer);
        const answerHtml = isFile
            ? renderDocumentCard(item.answer)
            : `<div style="color:#475569;">${escapeHtml(formatAnswerText(item.answer))}</div>`;
        return `
            <div style="margin-bottom:12px;padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="font-weight:600;margin-bottom:4px;">${index + 1}. ${escapeHtml(item.question_label || item.label || 'Question')}</div>
                ${answerHtml}
            </div>`;
    }).join('');
}

function renderRejectedViewContent(record, app) {
    const docs = Array.isArray(app?.required_documents)
        ? app.required_documents
        : (app?.required_documents ? Object.values(app.required_documents) : []);
    const docsHtml = docs.length
        ? docs.map((doc) => renderDocumentCard(doc)).join('')
        : '<span style="color:#9ca3af;">No documents uploaded</span>';
    const answersHtml = renderFormAnswers(app?.custom_answers || record.custom_answers);

    return `
        <div class="kk-rejection-details-section">
            <div class="kk-rejection-details-title">Rejection Details</div>
            <div class="kk-rejection-details-grid">
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejection Reason:</span>
                    <span class="kk-rejection-detail-value">${escapeHtml(formatRejectionReason(record))}</span>
                </div>
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejected Date:</span>
                    <span class="kk-rejection-detail-value">${escapeHtml(record.rejected_date || '—')}</span>
                </div>
                <div class="kk-rejection-detail-item">
                    <span class="kk-rejection-detail-label">Rejected Time:</span>
                    <span class="kk-rejection-detail-value">${escapeHtml(record.rejected_time || '—')}</span>
                </div>
            </div>
        </div>
        <div style="margin-top:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 20px;">
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Full Name</div><div style="font-weight:600;color:#111827;">${escapeHtml(rspFormatName(record))}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Sport</div><div style="font-weight:600;color:#111827;">${escapeHtml(record.sport_label || app?.sport_label || '—')}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Team</div><div style="font-weight:600;color:#111827;">${escapeHtml(record.team_name || app?.team_name || '—')}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Program</div><div style="font-weight:600;color:#111827;">${escapeHtml(record.program_name || app?.program_name || '—')}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Age / Sex</div><div style="font-weight:600;color:#111827;">${escapeHtml([record.age, record.sex].filter(Boolean).join(' · ') || '—')}</div></div>
            <div><div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Date Applied</div><div style="font-weight:600;color:#111827;">${escapeHtml(record.date_submitted || '—')}</div></div>
        </div>
        <div style="margin-top:18px;">
            <div style="font-weight:700;margin-bottom:10px;">Uploaded Documents</div>
            ${docsHtml}
        </div>
        ${answersHtml ? `<div style="margin-top:18px;"><div style="font-weight:700;margin-bottom:10px;">Form Answers</div>${answersHtml}</div>` : ''}`;
}

async function openViewModal(id) {
    const record = rspAllRecords.find((r) => r.id === id);
    if (!record) return;

    const body = document.getElementById('rspViewModalBody');
    const modal = document.getElementById('rspViewModal');
    if (!body || !modal) return;

    body.innerHTML = '<p style="padding:16px;color:#6b7280;">Loading application details...</p>';
    modal.style.display = 'flex';

    try {
        const res = await fetch(`/api/program-applications/${id}?letter=I`, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Failed to load application.');
        body.innerHTML = renderRejectedViewContent(record, data.data);
    } catch (error) {
        body.innerHTML = renderRejectedViewContent(record, null);
        body.insertAdjacentHTML('afterbegin', `<p style="color:#b91c1c;margin-bottom:12px;">${escapeHtml(error.message || 'Could not load full details.')}</p>`);
    }
}

function bindViewModal() {
    const modal = document.getElementById('rspViewModal');
    const close = document.getElementById('rspViewModalClose');
    const toggle = document.getElementById('rspViewModalToggle');
    const box = document.getElementById('rspViewModalBox');

    const closeModal = () => {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('view-modal-maximized');
        }
        if (box) box.classList.remove('view-modal-maximized');
        if (toggle) toggle.textContent = '□';
    };

    close?.addEventListener('click', closeModal);

    if (toggle && box) {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !box.classList.contains('view-modal-maximized');
            modal?.classList.toggle('view-modal-maximized', isMax);
            box.classList.toggle('view-modal-maximized', isMax);
            toggle.textContent = isMax ? '⧉' : '□';
        });
    }

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

document.getElementById('rejectedSportsPrevBtn')?.addEventListener('click', () => {
    if (rspCurrentPage > 1) { rspCurrentPage--; renderTable(); }
});

document.getElementById('rejectedSportsNextBtn')?.addEventListener('click', () => {
    const pages = Math.ceil(rspFiltered.length / rspPerPage);
    if (rspCurrentPage < pages) { rspCurrentPage++; renderTable(); }
});
