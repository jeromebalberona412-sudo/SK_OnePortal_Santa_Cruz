/**
 * Sports Program Requests — DB-backed via /api/program-applications?letter=I
 */
document.addEventListener('DOMContentLoaded', () => {
    const PROGRAM_LETTER = 'I';
    const tbody = document.getElementById('sportsTableBody');
    const searchInput = document.getElementById('scholSearch');
    const statTotal = document.getElementById('statTotal');
    const statPending = document.getElementById('statPending');
    const statApproved = document.getElementById('statApproved');
    const statRejected = document.getElementById('statRejected');
    const viewModal = document.getElementById('viewModal');
    const viewModalBody = document.getElementById('viewModalBody');
    const viewClose = document.getElementById('viewClose');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');
    const rejectReasonModal = document.getElementById('rejectReasonModal');
    const rejectReasonClose = document.getElementById('rejectReasonClose');
    const rejectReasonCancel = document.getElementById('rejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('rejectReasonConfirm');
    const rejectOtherReason = document.getElementById('rejectReasonOtherText');
    const sportsToast = document.getElementById('sportsToast');
    const sportsToastMsg = document.getElementById('sportsToastMsg');

    let applications = [];
    let currentApplicationId = null;

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

    function showToast(message, type = 'success') {
        if (!sportsToast || !sportsToastMsg) return;
        sportsToastMsg.textContent = message;
        sportsToast.style.display = 'flex';
        sportsToast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => { sportsToast.style.display = 'none'; }, 2800);
    }

    async function apiFetch(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                ...(options.headers || {}),
            },
            ...options,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Request failed.');
        return data;
    }

    function statusClass(status) {
        switch (status) {
            case 'approved': return 'schol-pill-approved';
            case 'rejected': return 'schol-pill-rejected';
            case 'cancelled': return 'schol-pill-cancelled';
            default: return 'schol-pill-pending';
        }
    }

    function filteredApplications() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        if (!query) return applications;
        return applications.filter((app) =>
            app.full_name?.toLowerCase().includes(query)
            || app.program_name?.toLowerCase().includes(query)
            || app.contact_number?.includes(query)
        );
    }

    function renderTable() {
        if (!tbody) return;
        const rows = filteredApplications();

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="saf-table-empty">No sports applications yet.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((app) => `
            <tr>
                <td>${escapeHtml(app.full_name)}</td>
                <td>${escapeHtml(app.program_name || '—')}</td>
                <td>${escapeHtml(app.age ?? '—')}</td>
                <td>${escapeHtml((app.required_documents?.length ?? 0) + ' file(s)')}</td>
                <td>${escapeHtml(app.date_submitted)}</td>
                <td><span class="schol-pill ${statusClass(app.status)}">${escapeHtml(app.status_label)}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-view="${app.id}">View</button>
                        ${app.status === 'pending' ? `
                            <button type="button" class="prog-btn prog-btn-edit" data-approve="${app.id}">Approve</button>
                            <button type="button" class="prog-btn prog-btn-delete" data-reject="${app.id}">Reject</button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.querySelectorAll('[data-view]').forEach((btn) => {
            btn.addEventListener('click', () => openViewModal(btn.getAttribute('data-view')));
        });
        tbody.querySelectorAll('[data-approve]').forEach((btn) => {
            btn.addEventListener('click', () => updateStatus(btn.getAttribute('data-approve'), 'approved'));
        });
        tbody.querySelectorAll('[data-reject]').forEach((btn) => {
            btn.addEventListener('click', () => openRejectModal(btn.getAttribute('data-reject')));
        });
    }

    function renderStats(summary) {
        if (statTotal) statTotal.textContent = String(summary.total ?? 0);
        if (statPending) statPending.textContent = String(summary.pending ?? 0);
        if (statApproved) statApproved.textContent = String(summary.approved ?? 0);
        if (statRejected) statRejected.textContent = String(summary.rejected ?? 0);
    }

    async function loadApplications() {
        const data = await apiFetch(`/api/program-applications?letter=${PROGRAM_LETTER}`);
        applications = Array.isArray(data.data) ? data.data : [];
        renderStats(data.summary || {});
        renderTable();
    }

    async function openViewModal(id) {
        currentApplicationId = id;
        const data = await apiFetch(`/api/program-applications/${id}?letter=${PROGRAM_LETTER}`);
        const app = data.data;
        if (!viewModalBody || !app) return;

        const answers = (app.custom_answers || []).map((item, index) => `
            <div style="margin-bottom:12px;padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="font-weight:600;margin-bottom:4px;">${index + 1}. ${escapeHtml(item.question_label || item.label || 'Question')}</div>
                <div style="color:#475569;">${escapeHtml(Array.isArray(item.answer) ? item.answer.join(', ') : (item.answer ?? '—'))}</div>
            </div>
        `).join('');

        viewModalBody.innerHTML = `
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px;">
                <div><strong>Name</strong><br>${escapeHtml(app.full_name)}</div>
                <div><strong>Program</strong><br>${escapeHtml(app.program_name || '—')}</div>
                <div><strong>Age</strong><br>${escapeHtml(app.age ?? '—')}</div>
                <div><strong>Contact</strong><br>${escapeHtml(app.contact_number || '—')}</div>
                <div><strong>Email</strong><br>${escapeHtml(app.email || '—')}</div>
                <div><strong>Status</strong><br>${escapeHtml(app.status_label)}</div>
            </div>
            <h4 style="margin:0 0 12px;">Application Answers</h4>
            ${answers || '<p style="color:#94a3b8;">No custom answers submitted.</p>'}
        `;

        if (viewModal) viewModal.style.display = 'flex';
        if (btnApprove) btnApprove.style.display = app.status === 'pending' ? 'inline-flex' : 'none';
        if (btnReject) btnReject.style.display = app.status === 'pending' ? 'inline-flex' : 'none';
    }

    function closeViewModal() {
        currentApplicationId = null;
        if (viewModal) viewModal.style.display = 'none';
    }

    function openRejectModal(id) {
        currentApplicationId = id;
        if (rejectOtherReason) rejectOtherReason.value = '';
        document.querySelectorAll('.reject-reason-checkbox, #rejectReasonOtherCheckbox').forEach((input) => { input.checked = false; });
        if (rejectReasonModal) rejectReasonModal.style.display = 'flex';
    }

    function closeRejectModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
    }

    async function updateStatus(id, status, rejectionReasons = null, rejectionReason = null) {
        await apiFetch(`/api/program-applications/${id}/status?letter=${PROGRAM_LETTER}`, {
            method: 'PUT',
            body: JSON.stringify({ status, rejection_reasons: rejectionReasons, rejection_reason: rejectionReason, letter: PROGRAM_LETTER }),
        });
        showToast(status === 'approved' ? 'Application approved.' : 'Application rejected.');
        closeViewModal();
        closeRejectModal();
        await loadApplications();
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);
    if (viewClose) viewClose.addEventListener('click', closeViewModal);
    if (viewModal) viewModal.addEventListener('click', (e) => { if (e.target === viewModal) closeViewModal(); });
    if (btnApprove) btnApprove.addEventListener('click', () => currentApplicationId && updateStatus(currentApplicationId, 'approved'));
    if (btnReject) btnReject.addEventListener('click', () => currentApplicationId && openRejectModal(currentApplicationId));
    if (rejectReasonClose) rejectReasonClose.addEventListener('click', closeRejectModal);
    if (rejectReasonCancel) rejectReasonCancel.addEventListener('click', closeRejectModal);
    if (rejectReasonConfirm) {
        rejectReasonConfirm.addEventListener('click', async () => {
            const reasons = Array.from(document.querySelectorAll('.reject-reason-checkbox:checked, #rejectReasonOtherCheckbox:checked'))
                .map((el) => el.value);
            const other = rejectOtherReason?.value?.trim();
            if (other) reasons.push(other);
            if (!reasons.length) {
                showToast('Please select or enter a rejection reason.', 'error');
                return;
            }
            try {
                await updateStatus(currentApplicationId, 'rejected', reasons, other || reasons[0]);
            } catch (error) {
                showToast(error.message, 'error');
            }
        });
    }

    (async () => {
        try {
            if (typeof window.showLoading === 'function') window.showLoading();
            await loadApplications();
        } catch (error) {
            showToast(error.message || 'Failed to load applications.', 'error');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="saf-table-empty">Unable to load applications.</td></tr>';
        } finally {
            if (typeof window.hideLoading === 'function') window.hideLoading();
        }
    })();
});
