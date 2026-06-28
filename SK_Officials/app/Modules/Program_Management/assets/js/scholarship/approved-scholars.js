// ── Scholar List (Approved Scholars) ───────────────────────────────────────

const SL_PAYMENT_STATUSES = ['Claimed', 'Unclaimed'];

function slFormatFullName(record) {
    if (window.ScholarshipViewShared?.formatScholarshipFullName) {
        return window.ScholarshipViewShared.formatScholarshipFullName(record);
    }
    const ln = (record?.last_name || '').toUpperCase();
    const fn = (record?.first_name || '').toUpperCase();
    const mn = (record?.middle_name || '').toUpperCase();
    const suffix = (record?.suffix || '').toUpperCase();
    const parts = [fn, mn].filter(Boolean);
    const firstMiddle = parts.join(',');
    const suffixPart = suffix ? `,${suffix}` : '';
    if (ln && firstMiddle) return `${ln},${firstMiddle}${suffixPart}`;
    if (ln) return `${ln}${suffixPart}`;
    return firstMiddle || '—';
}

function slNormalizePaymentStatus(scholar) {
    const raw = String(scholar?.payment_status || '').trim();
    if (raw === 'Paid' || raw === 'Claimed') return 'Claimed';
    if (raw === 'Unpaid' || raw === 'Unclaimed') return 'Unclaimed';
    if (raw && SL_PAYMENT_STATUSES.includes(raw)) return raw;
    return 'Unclaimed';
}

function slEnsurePaymentStatuses() {
    SL_SCHOLARS.forEach(s => {
        s.payment_status = slNormalizePaymentStatus(s);
    });
}

function slGetInitials(record) {
    const fn = (record?.first_name || '').charAt(0);
    const ln = (record?.last_name || '').charAt(0);
    return `${fn}${ln}`.toUpperCase() || '—';
}

function slPaymentStatusMeta(status) {
    const label = String(status || 'Unclaimed').toUpperCase();
    if (status === 'Claimed') return { bg: '#dcfce7', text: '#166534', label };
    if (status === 'Pending Release') return { bg: '#fef3c7', text: '#92400e', label };
    return { bg: '#fee2e2', text: '#991b1b', label };
}

function slRenderScholarSummaryHtml(scholar) {
    const fullName = slFormatFullName(scholar);
    const initials = slGetInitials(scholar);
    const paymentStatus = slNormalizePaymentStatus(scholar);
    const meta = slPaymentStatusMeta(paymentStatus);
    const approvedAt = scholar.approved_at || '—';

    return `
        <div class="sl-scholar-summary">
            <div class="sl-scholar-summary-top">
                <div class="sl-scholar-summary-identity">
                    <div class="sl-scholar-summary-avatar">${initials}</div>
                    <div class="sl-scholar-summary-name">${escapeSl(fullName)}</div>
                </div>
                <span class="sl-scholar-summary-badge" style="background:${meta.bg};color:${meta.text};">${meta.label}</span>
            </div>
            <div class="sl-scholar-summary-payment">
                <div class="sl-scholar-summary-payment-title">Payment Status</div>
                <div class="sl-scholar-summary-grid">
                    <div class="sl-scholar-summary-item">
                        <span class="sl-scholar-summary-label">Current Status</span>
                        <span class="sl-scholar-summary-status" style="background:${meta.bg};color:${meta.text};">${meta.label}</span>
                    </div>
                    <div class="sl-scholar-summary-item">
                        <span class="sl-scholar-summary-label">Date Approved</span>
                        <span class="sl-scholar-summary-value">${escapeSl(approvedAt)}</span>
                    </div>
                </div>
            </div>
        </div>`;
}

function slResetModalMaximize(overlay, box, maxBtn) {
    if (box) box.classList.remove('sl-modal-maximized');
    if (overlay) overlay.classList.remove('sl-overlay-maximized');
    if (maxBtn) {
        maxBtn.textContent = '□';
        maxBtn.title = 'Maximize';
    }
}

function slToggleModalMaximize(overlay, box, maxBtn, e) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }
    if (!box) return;
    box.classList.toggle('sl-modal-maximized');
    const isMax = box.classList.contains('sl-modal-maximized');
    if (maxBtn) {
        maxBtn.textContent = isMax ? '⧉' : '□';
        maxBtn.title = isMax ? 'Restore Down' : 'Maximize';
    }
    if (overlay) overlay.classList.toggle('sl-overlay-maximized', isMax);
}

const PROGRAM_LETTER = 'A';
let SL_SCHOLARS = [];

function slShowToast(message) {
    if (typeof window.showScholarshipToast === 'function') {
        window.showScholarshipToast(message);
    }
}

let currentPage = 1;
let recordsPerPage = 10;
let activePaymentFilter = 'all';
let filteredScholars = [];
let tablePagination = null;
let revokeScholarId = null;
let editScholarId = null;

function slCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function slApiFetch(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': slCsrfToken(),
            ...(options.headers || {}),
        },
        ...options,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Request failed.');
    return data;
}

function slDeriveScholarshipYear(app) {
    const dateStr = app.reviewed_at || app.created_at;
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return '';
    return String(d.getFullYear());
}

function mapApprovedScholar(app) {
    const approvedAt = app.reviewed_at
        ? new Date(app.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
        : (app.date_submitted || '-');
    return {
        id: app.id,
        last_name: app.last_name,
        first_name: app.first_name,
        middle_name: app.middle_name,
        suffix: app.suffix,
        contact_no: app.contact_number,
        email: app.email,
        school_name: app.school_name,
        year_level: app.year_level || app.grade_level,
        program_strand: app.course,
        purpose: app.purpose || '-',
        approved_at: approvedAt,
        scholarship_year: slDeriveScholarshipYear(app),
        payment_status: slNormalizePaymentStatus({ payment_status: app.payment_status }),
        sex: app.sex,
        age: app.age,
        barangay: app.barangay,
    };
}

async function loadApprovedScholars() {
    const data = await slApiFetch(`/api/program-applications?letter=${PROGRAM_LETTER}&status=approved`);
    SL_SCHOLARS = (data.data || []).map(mapApprovedScholar);
    slEnsurePaymentStatuses();
    applyFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    initializeExportButton();
    initializeModal();
    initializeFilters();
    initializePaymentFilterTabs();
    initializeEditModal();
    initializeRevokeModal();
    bindApprovedTableActions();

    if (typeof window.bindTablePageFooter === 'function') {
        tablePagination = window.bindTablePageFooter({
            prefix: 'scholAppr',
            getTotalRecords: () => filteredScholars.length,
            getCurrentPage: () => currentPage,
            setCurrentPage: (page) => { currentPage = page; },
            getRecordsPerPage: () => recordsPerPage,
            setRecordsPerPage: (value) => { recordsPerPage = value; },
            onPageChange: () => renderScholarTable(),
        });
    }

    (async () => {
        try {
            await loadApprovedScholars();
        } catch (error) {
            const tbody = document.getElementById('slTableBody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="sl-empty">Unable to load approved scholars.</td></tr>';
            alert(error.message || 'Failed to load approved scholars.');
        }
    })();

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            loadApprovedScholars().catch(() => {});
        }
    });
});

function escapeSl(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function slPaymentBadgeClass(status) {
    if (status === 'Claimed') return 'sl-badge-claimed';
    if (status === 'Unclaimed') return 'sl-badge-unclaimed';
    return 'sl-badge-default';
}

function slRenderActionMenuCell(scholar) {
    return `
        <div class="row-actions-menu">
            <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
            <div class="row-actions-dropdown" role="menu">
                <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-id="${scholar.id}" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                </button>
                <button type="button" class="row-actions-item row-actions-item-edit" data-action="edit" data-id="${scholar.id}" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit</span>
                </button>
                <button type="button" class="row-actions-item row-actions-item-danger" data-action="revoke" data-id="${scholar.id}" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Revoke</span>
                </button>
            </div>
        </div>`;
}

function renderScholarTable() {
    const tbody = document.getElementById('slTableBody');
    if (!tbody) return;

    const pageRows = typeof window.paginateSlice === 'function'
        ? window.paginateSlice(filteredScholars, currentPage, recordsPerPage)
        : filteredScholars;

    if (pageRows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="sl-empty">No scholars found.</td></tr>`;
        if (tablePagination) tablePagination.updateFooter();
        return;
    }

    tbody.innerHTML = pageRows.map((r) => {
        const fullName = slFormatFullName(r);
        const displayProgram = r.program_strand || '—';
        const paymentStatus = slNormalizePaymentStatus(r);
        const statusBadge = `<span class="sl-badge ${slPaymentBadgeClass(paymentStatus)}">${escapeSl(paymentStatus.toUpperCase())}</span>`;

        return `
        <tr>
            <td class="schol-fullname-cell"><span class="schol-fullname">${escapeSl(fullName)}</span></td>
            <td class="sl-td-center">${escapeSl(r.school_name || '—')}</td>
            <td class="sl-td-center">${escapeSl(r.year_level || '—')}</td>
            <td class="sl-td-center sl-td-program">${escapeSl(displayProgram)}</td>
            <td class="sl-td-center">${escapeSl(r.approved_at || '—')}</td>
            <td class="sl-td-center">${statusBadge}</td>
            <td class="sl-td-center sl-actions col-actions">${slRenderActionMenuCell(r)}</td>
        </tr>`;
    }).join('');

    if (tablePagination) tablePagination.updateFooter();
}

function bindApprovedTableActions() {
    const tbody = document.getElementById('slTableBody');
    if (!tbody || tbody.dataset.slActionsBound === '1') return;
    tbody.dataset.slActionsBound = '1';

    if (typeof window.bindRowActionsTable === 'function') {
        window.bindRowActionsTable(tbody);
    }

    tbody.addEventListener('click', async (event) => {
        const btn = event.target.closest('.row-actions-item[data-action]');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const scholar = SL_SCHOLARS.find((item) => item.id === id);
        if (!scholar) return;

        if (typeof window.closeAllRowActionMenus === 'function') {
            window.closeAllRowActionMenus();
        }

        if (action === 'view') {
            await openScholarModal(scholar);
            return;
        }

        if (action === 'edit') {
            openEditModal(scholar);
            return;
        }

        if (action === 'revoke') {
            openRevokeModal(scholar);
        }
    });
}

function initializeExportButton() {
    const exportBtn = document.getElementById('slExportCsvBtn');
    if (!exportBtn) return;
    exportBtn.addEventListener('click', () => exportToCsv(filteredScholars));
}

function openRevokeModal(scholar) {
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeBox = document.getElementById('slRevokeBox');
    const revokeMaximize = document.getElementById('slRevokeMaximize');
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeReasonField = document.getElementById('slRevokeReasonField');
    const revokeConfirmText = document.getElementById('slRevokeConfirmText');
    const revokeConfirmError = document.getElementById('slRevokeConfirmError');
    const revokeConfirmBtn = document.getElementById('btnConfirmRevoke');

    if (!revokeModal || !scholar) return;

    revokeScholarId = scholar.id;
    slResetModalMaximize(revokeModal, revokeBox, revokeMaximize);
    if (revokeReasonInput) revokeReasonInput.value = '';
    if (revokeReasonField) revokeReasonField.style.display = 'none';
    if (revokeConfirmText) revokeConfirmText.value = '';
    if (revokeConfirmError) {
        revokeConfirmError.style.display = 'none';
        revokeConfirmError.textContent = '';
    }

    document.querySelectorAll('input[name="revokeReason"]').forEach((rb) => {
        rb.checked = rb.value === 'Mistakenly Approved';
    });

    slResetRevokeConfirmButton();
    revokeModal.style.display = 'flex';
}

function slResetRevokeConfirmButton() {
    const revokeConfirmBtn = document.getElementById('btnConfirmRevoke');
    if (!revokeConfirmBtn) return;
    revokeConfirmBtn.disabled = true;
    revokeConfirmBtn.classList.remove('is-enabled');
    revokeConfirmBtn.classList.add('is-disabled');
}

function slSyncRevokeConfirmButton() {
    const revokeConfirmBtn = document.getElementById('btnConfirmRevoke');
    const revokeConfirmText = document.getElementById('slRevokeConfirmText');
    if (!revokeConfirmBtn) return;
    const matched = (revokeConfirmText?.value?.trim() || '') === 'Confirm';
    revokeConfirmBtn.disabled = !matched;
    revokeConfirmBtn.classList.toggle('is-enabled', matched);
    revokeConfirmBtn.classList.toggle('is-disabled', !matched);
}

function closeRevokeModal() {
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeBox = document.getElementById('slRevokeBox');
    const revokeMaximize = document.getElementById('slRevokeMaximize');
    if (revokeModal) revokeModal.style.display = 'none';
    slResetModalMaximize(revokeModal, revokeBox, revokeMaximize);
}

async function confirmRevokeApproval() {
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeConfirmText = document.getElementById('slRevokeConfirmText');
    const revokeConfirmError = document.getElementById('slRevokeConfirmError');
    const revokeConfirmBtn = document.getElementById('btnConfirmRevoke');

    if (!revokeScholarId) return;

    if ((revokeConfirmText?.value?.trim() || '') !== 'Confirm') {
        if (revokeConfirmError) {
            revokeConfirmError.textContent = 'Please type Confirm to revoke this approval.';
            revokeConfirmError.style.display = 'block';
        } else {
            alert('Please type Confirm to revoke this approval.');
        }
        return;
    }

    let reason = '';
    const selectedRadio = document.querySelector('input[name="revokeReason"]:checked');
    if (selectedRadio) {
        if (selectedRadio.value === 'other') {
            reason = revokeReasonInput ? revokeReasonInput.value.trim() : '';
            if (!reason) {
                alert('Please enter a revocation reason when selecting "Other Reason".');
                return;
            }
        } else {
            reason = selectedRadio.value;
        }
    }

    if (!reason) {
        alert('Please select a revocation reason.');
        return;
    }

    const defaultHtml = revokeConfirmBtn ? revokeConfirmBtn.innerHTML : 'Revoke';
    try {
        if (revokeConfirmBtn) {
            revokeConfirmBtn.disabled = true;
            revokeConfirmBtn.textContent = 'Revoking…';
        }
        if (typeof window.showLoading === 'function') window.showLoading();

        await slApiFetch(`/api/program-applications/${revokeScholarId}/status?letter=${PROGRAM_LETTER}`, {
            method: 'PUT',
            body: JSON.stringify({
                status: 'pending',
                rejection_reason: reason,
                rejection_reasons: [reason],
                letter: PROGRAM_LETTER,
            }),
        });
        closeRevokeModal();
        revokeScholarId = null;
        await loadApprovedScholars();
        slShowToast('Scholar approval revoked. The application has been returned to Scholarship Applications.');
    } catch (error) {
        alert(error.message || 'Failed to revoke approval.');
    } finally {
        if (revokeConfirmBtn) {
            revokeConfirmBtn.innerHTML = defaultHtml;
            slSyncRevokeConfirmButton();
        }
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
}

function exportToCsv(scholars) {
    if (scholars.length === 0) { alert('No scholars to export.'); return; }
    const headers = ['Full Name', 'School', 'Year/Level', 'Program/Strand', 'Date Approved', 'Payment Status'];
    const rows = scholars.map(r => {
        const fullName = slFormatFullName(r);
        return [fullName, r.school_name || '', r.year_level || '', r.program_strand || '', r.approved_at || '', slNormalizePaymentStatus(r)];
    });
    let csv = headers.join(',') + '\n';
    rows.forEach(row => { csv += row.map(c => `"${c}"`).join(',') + '\n'; });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `approved_scholars_${new Date().toISOString().split('T')[0]}.csv`;
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function initializeModal() {
    const modal = document.getElementById('slViewModal');
    const closeBtn = document.getElementById('slViewClose');
    const maxBtn = document.getElementById('slViewMaximize');
    const modalBox = document.getElementById('slViewBox');

    const closeView = () => {
        modal.style.display = 'none';
        slResetModalMaximize(modal, modalBox, maxBtn);
    };

    if (closeBtn) closeBtn.addEventListener('click', closeView);
    if (modal) {
        modal.addEventListener('click', e => {
            if (e.target === modal) closeView();
        });
    }
    if (maxBtn && modalBox) {
        maxBtn.addEventListener('click', (e) => {
            slToggleModalMaximize(modal, modalBox, maxBtn, e);
        });
    }
}

function openScholarModal(scholar) {
    return openScholarModalAsync(scholar);
}

async function openScholarModalAsync(scholar) {
    const modal = document.getElementById('slViewModal');
    const modalBox = document.getElementById('slViewBox');
    const maxBtn = document.getElementById('slViewMaximize');
    const body = document.getElementById('slViewBody');
    if (!modal || !body || !scholar?.id) return;

    try {
        const data = await slApiFetch(`/api/program-applications/${scholar.id}?letter=${PROGRAM_LETTER}`);
        const SV = window.ScholarshipViewShared;
        const detail = SV?.mapScholarshipApplicationDetail
            ? SV.mapScholarshipApplicationDetail(data.data || {})
            : data.data;
        body.innerHTML = SV?.renderApplicationViewBody
            ? SV.renderApplicationViewBody(detail)
            : '';
        slResetModalMaximize(modal, modalBox, maxBtn);
        modal.style.display = 'flex';
    } catch (error) {
        alert(error.message || 'Failed to load application details.');
    }
}

function slDownloadPlaceholderPdf(filename) {
    const pdfContent = `%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer<</Size 4/Root 1 0 R>>
startxref
190
%%EOF`;
    const blob = new Blob([pdfContent], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
window.slDownloadPlaceholderPdf = slDownloadPlaceholderPdf;

function updateSummaryCards() {
    const total = filteredScholars.length;
    const pending = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Pending Release').length;
    const claimed = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Claimed').length;
    const unclaimed = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Unclaimed').length;

    const elTotal = document.getElementById('slStatTotal');
    const elPending = document.getElementById('slStatPending');
    const elPaid = document.getElementById('slStatPaid');
    const elUnclaimed = document.getElementById('slStatUnclaimed');
    if (elTotal) elTotal.textContent = total;
    if (elPending) elPending.textContent = pending;
    if (elPaid) elPaid.textContent = claimed;
    if (elUnclaimed) elUnclaimed.textContent = unclaimed;
}

function initializePaymentFilterTabs() {
    document.querySelectorAll('.sl-payment-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sl-payment-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activePaymentFilter = this.dataset.paymentFilter || 'all';
            applyFilters();
        });
    });
}

function initializeFilters() {
    const yearFilter = document.getElementById('slYearFilter');
    const searchInput = document.getElementById('slSearchInput');
    if (yearFilter) yearFilter.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('input', applyFilters);
}

function applyFilters() {
    const yearFilter = document.getElementById('slYearFilter')?.value || '';
    const searchQuery = document.getElementById('slSearchInput')?.value.toLowerCase() || '';

    filteredScholars = SL_SCHOLARS.filter(scholar => {
        const paymentStatus = slNormalizePaymentStatus(scholar);
        if (activePaymentFilter !== 'all' && paymentStatus !== activePaymentFilter) {
            return false;
        }
        if (yearFilter && scholar.scholarship_year !== yearFilter) {
            return false;
        }
        if (searchQuery) {
            const fullName = `${scholar.last_name} ${scholar.first_name} ${scholar.middle_name}`.toLowerCase();
            const school = (scholar.school_name || '').toLowerCase();
            const program = (scholar.program_strand || '').toLowerCase();
            const payment = paymentStatus.toLowerCase();
            if (!fullName.includes(searchQuery) &&
                !school.includes(searchQuery) &&
                !program.includes(searchQuery) &&
                !payment.includes(searchQuery)) {
                return false;
            }
        }
        return true;
    });

    // Sort filtered scholars alphabetically by last name, then first name
    filteredScholars.sort((a, b) => {
        const lastNameA = (a.last_name || '').toLowerCase();
        const lastNameB = (b.last_name || '').toLowerCase();
        if (lastNameA !== lastNameB) {
            return lastNameA.localeCompare(lastNameB);
        }
        const firstNameA = (a.first_name || '').toLowerCase();
        const firstNameB = (b.first_name || '').toLowerCase();
        return firstNameA.localeCompare(firstNameB);
    });

    currentPage = 1;
    updateSummaryCards();
    renderScholarTable();
}

function initializeRevokeModal() {
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeClose = document.getElementById('slRevokeClose');
    const revokeCancel = document.getElementById('btnCancelRevoke');
    const revokeConfirm = document.getElementById('btnConfirmRevoke');
    const revokeMaximize = document.getElementById('slRevokeMaximize');
    const revokeBox = document.getElementById('slRevokeBox');
    const revokeOtherRadio = document.getElementById('slRevokeOtherRadio');
    const revokeReasonField = document.getElementById('slRevokeReasonField');
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeConfirmText = document.getElementById('slRevokeConfirmText');
    const revokeConfirmError = document.getElementById('slRevokeConfirmError');

    const closeRevoke = () => {
        revokeScholarId = null;
        if (revokeModal) revokeModal.style.display = 'none';
        slResetModalMaximize(revokeModal, revokeBox, revokeMaximize);
    };

    if (revokeClose) revokeClose.addEventListener('click', closeRevoke);
    if (revokeCancel) revokeCancel.addEventListener('click', closeRevoke);
    if (revokeModal) {
        revokeModal.addEventListener('click', (e) => {
            if (e.target === revokeModal) closeRevoke();
        });
    }
    if (revokeMaximize && revokeBox) {
        revokeMaximize.addEventListener('click', (e) => {
            slToggleModalMaximize(revokeModal, revokeBox, revokeMaximize, e);
        });
    }
    if (revokeOtherRadio && revokeReasonField) {
        revokeOtherRadio.addEventListener('change', function () {
            revokeReasonField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked && revokeReasonInput) revokeReasonInput.value = '';
        });
        document.querySelectorAll('input[name="revokeReason"]').forEach((rb) => {
            if (rb.value !== 'other') {
                rb.addEventListener('change', () => {
                    if (revokeReasonField) revokeReasonField.style.display = 'none';
                    if (revokeReasonInput) revokeReasonInput.value = '';
                });
            }
        });
    }
    if (revokeConfirmText) {
        revokeConfirmText.addEventListener('input', () => {
            if (revokeConfirmError) {
                revokeConfirmError.style.display = 'none';
                revokeConfirmError.textContent = '';
            }
            slSyncRevokeConfirmButton();
        });
    }
    if (revokeConfirm) revokeConfirm.addEventListener('click', confirmRevokeApproval);
}

function openEditModal(scholar) {
    const modal = document.getElementById('slEditModal');
    const editBox = document.getElementById('slEditBox');
    const editMaxBtn = document.getElementById('slEditMaximize');
    const paymentSelect = document.getElementById('editPaymentStatus');

    if (!modal || !scholar?.id) return;

    editScholarId = scholar.id;
    slResetModalMaximize(modal, editBox, editMaxBtn);
    if (paymentSelect) paymentSelect.value = slNormalizePaymentStatus(scholar);
    modal.style.display = 'flex';
}

function closeEditModal() {
    const modal = document.getElementById('slEditModal');
    const editBox = document.getElementById('slEditBox');
    const editMaxBtn = document.getElementById('slEditMaximize');
    editScholarId = null;
    if (modal) modal.style.display = 'none';
    slResetModalMaximize(modal, editBox, editMaxBtn);
}

async function saveEditStatus() {
    const paymentStatus = document.getElementById('editPaymentStatus')?.value;
    const saveBtn = document.getElementById('btnSaveEdit');

    if (!editScholarId) {
        alert('Scholar not found.');
        return;
    }

    if (!SL_PAYMENT_STATUSES.includes(paymentStatus)) {
        alert('Please select a valid payment status.');
        return;
    }

    const defaultHtml = saveBtn ? saveBtn.innerHTML : 'Save Changes';
    try {
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving…';
        }
        if (typeof window.showLoading === 'function') window.showLoading();

        await slApiFetch(`/api/program-applications/${editScholarId}/payment?letter=${PROGRAM_LETTER}`, {
            method: 'PUT',
            body: JSON.stringify({
                payment_status: paymentStatus,
                letter: PROGRAM_LETTER,
            }),
        });

        editScholarId = null;
        await loadApprovedScholars();
        closeEditModal();
        slShowToast('Payment status updated successfully.');
    } catch (error) {
        slShowToast(error.message || 'Failed to update payment status.');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = defaultHtml;
        }
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
}

function initializeEditModal() {
    const editModal = document.getElementById('slEditModal');
    const editClose = document.getElementById('slEditClose');
    const editMaxBtn = document.getElementById('slEditMaximize');
    const editBox = document.getElementById('slEditBox');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    const btnSaveEdit = document.getElementById('btnSaveEdit');

    const closeEdit = () => {
        editModal.style.display = 'none';
        slResetModalMaximize(editModal, editBox, editMaxBtn);
    };

    if (editClose) editClose.addEventListener('click', closeEdit);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);
    if (btnSaveEdit) btnSaveEdit.addEventListener('click', () => { saveEditStatus(); });
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) closeEdit();
        });
    }
    if (editMaxBtn && editBox) {
        editMaxBtn.addEventListener('click', (e) => {
            slToggleModalMaximize(editModal, editBox, editMaxBtn, e);
        });
    }
}
