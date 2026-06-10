// ── Sports List Page JavaScript ───────────────────────────────────────────

const SPL_PROGRAM_LETTER = 'I';

document.addEventListener('DOMContentLoaded', () => {
    (async () => {
        try {
            await initSportsList();
        } catch (error) {
            const tbody = document.getElementById('slTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="9" class="spl-empty">Unable to load approved participants.</td></tr>';
            }
            alert(error.message || 'Failed to load approved participants.');
        }
    })();
});

function splCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function splApiFetch(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': splCsrfToken(),
            ...(options.headers || {}),
        },
        ...options,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Request failed.');
    return data;
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

function normalizePaymentStatus(value) {
    const raw = String(value || '').toLowerCase();
    if (raw === 'paid' || raw === 'claimed') return 'Paid';
    return 'Unpaid';
}

function mapApprovedParticipant(app) {
    const approvedAt = app.reviewed_at
        ? new Date(app.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
        : (app.date_submitted || '—');

    return {
        id: app.id,
        lastName: app.last_name || '',
        firstName: app.first_name || '',
        middleName: app.middle_name || '',
        age: app.age,
        contact: app.contact_number || '—',
        email: app.email || '—',
        sport: app.program_name || '—',
        division: '—',
        dateApplied: approvedAt,
        paymentStatus: normalizePaymentStatus(app.payment_status),
        status: 'Approved',
        address: app.barangay || '—',
        dateOfBirth: app.birthdate || '—',
        raw: app,
    };
}

async function reloadApprovedList() {
    const data = await splApiFetch(`/api/program-applications?letter=${SPL_PROGRAM_LETTER}&status=approved`);
    return (data.data || []).map(mapApprovedParticipant);
}

async function initSportsList() {
    const tbody = document.getElementById('slTableBody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="9" class="spl-empty">Loading participants…</td></tr>';

    let approvedList = await reloadApprovedList();

    let filtered = [...approvedList];

    const searchInput   = document.getElementById('slSearchInput');
    const filterSport   = document.getElementById('slSportFilter');
    const filterPayment = document.getElementById('slPaymentFilter');

    // ── Pagination State ──────────────────────────────────────────────────────
    let currentPage = 1;
    let perPage = parseInt(document.getElementById('slPerPage')?.value || '25', 10);

    // ── Render ──────────────────────────────────────────────────────────────
    renderTable(filtered);
    updateStats(approvedList);

    // ── Filters ─────────────────────────────────────────────────────────────
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (filterSport) filterSport.addEventListener('change', applyFilters);
    if (filterPayment) filterPayment.addEventListener('change', applyFilters);

    const perPageSel = document.getElementById('slPerPage');
    if (perPageSel) {
        perPageSel.addEventListener('change', () => {
            perPage = parseInt(perPageSel.value, 10);
            currentPage = 1;
            renderTable(filtered);
        });
    }

    function applyFilters() {
        const search = (searchInput?.value || '').toLowerCase();
        const sport  = filterSport?.value || '';
        const payment = filterPayment?.value || '';

        filtered = approvedList.filter(a => {
            const name = formatName(a).toLowerCase();
            if (search && !name.includes(search) && !a.sport.toLowerCase().includes(search)) return false;
            if (sport && a.sport !== sport) return false;
            if (payment && a.paymentStatus !== payment && !(payment === 'Not Paid' && a.paymentStatus === 'Unpaid')) return false;
            return true;
        });

        currentPage = 1;
        renderTable(filtered);
    }

    // ── Export CSV ───────────────────────────────────────────────────────────
    const exportBtn = document.getElementById('slExportCsvBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => exportCsv(filtered));
    }

    // ── View Modal ───────────────────────────────────────────────────────────
    const modal    = document.getElementById('slViewModal');
    const modalBox = document.getElementById('slViewBox');
    const closeBtn = document.getElementById('slViewClose');
    const maxBtn   = document.getElementById('slViewMaximize');

    if (closeBtn) closeBtn.addEventListener('click', closeViewModal);
    if (modal)    modal.addEventListener('click', e => { if (e.target === modal) closeViewModal(); });

    if (maxBtn && modalBox) {
        maxBtn.addEventListener('click', () => {
            modalBox.classList.toggle('spl-modal-maximized');
            const isMax = modalBox.classList.contains('spl-modal-maximized');
            maxBtn.textContent = isMax ? '⧉' : '□';
            maxBtn.title = isMax ? 'Restore Down' : 'Maximize';
            if (modal) modal.classList.toggle('spl-overlay-maximized', isMax);
        });
    }

    function closeViewModal() {
        if (modal) modal.style.display = 'none';
        if (modalBox) modalBox.classList.remove('spl-modal-maximized');
        if (modal) modal.classList.remove('spl-overlay-maximized');
        if (maxBtn) { maxBtn.textContent = '□'; maxBtn.title = 'Maximize'; }
    }

    // ── Edit Payment Modal ───────────────────────────────────────────────────
    const editModal = document.getElementById('slEditModal');
    const editBox = document.getElementById('slEditBox');
    const editClose = document.getElementById('slEditClose');
    const editCancel = document.getElementById('slEditCancel');
    const editSave = document.getElementById('slEditSave');
    const editMaximize = document.getElementById('slEditMaximize');
    const editPaymentSelect = document.getElementById('slEditPaymentStatus');
    const editParticipantId = document.getElementById('slEditParticipantId');
    const editParticipantName = document.getElementById('slEditParticipantName');

    function closeEditModal() {
        if (editModal) editModal.style.display = 'none';
        if (editBox) editBox.classList.remove('spl-modal-maximized');
        if (editModal) editModal.classList.remove('spl-overlay-maximized');
        if (editMaximize) { editMaximize.textContent = '□'; editMaximize.title = 'Maximize'; }
    }

    function openEditModal(participant) {
        if (!participant || !editModal) return;
        if (editParticipantId) editParticipantId.value = String(participant.id);
        if (editParticipantName) editParticipantName.textContent = formatName(participant);
        if (editPaymentSelect) editPaymentSelect.value = participant.paymentStatus === 'Paid' ? 'Paid' : 'Unpaid';
        editModal.style.display = 'flex';
    }

    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    if (editModal) editModal.addEventListener('click', (e) => { if (e.target === editModal) closeEditModal(); });
    if (editMaximize && editBox) {
        editMaximize.addEventListener('click', () => {
            editBox.classList.toggle('spl-modal-maximized');
            const isMax = editBox.classList.contains('spl-modal-maximized');
            editMaximize.textContent = isMax ? '⧉' : '□';
            editMaximize.title = isMax ? 'Restore Down' : 'Maximize';
            if (editModal) editModal.classList.toggle('spl-overlay-maximized', isMax);
        });
    }

    if (editSave) {
        editSave.addEventListener('click', async () => {
            const id = Number(editParticipantId?.value);
            const paymentStatus = editPaymentSelect?.value;
            if (!id || !paymentStatus) return;

            const defaultHtml = editSave.innerHTML;
            editSave.disabled = true;
            editSave.innerHTML = '<span class="schol-save-spinner"></span> Saving...';

            try {
                const result = await splApiFetch(`/api/program-applications/${id}/payment?letter=${SPL_PROGRAM_LETTER}`, {
                    method: 'PUT',
                    body: JSON.stringify({ payment_status: paymentStatus, letter: SPL_PROGRAM_LETTER }),
                });
                const updated = mapApprovedParticipant(result.data);
                const idx = approvedList.findIndex((item) => Number(item.id) === id);
                if (idx !== -1) approvedList[idx] = updated;
                applyFilters();
                updateStats(approvedList);
                closeEditModal();
                showToast('Payment status updated.');
                broadcastSportsEvent('payment-updated', id);
            } catch (error) {
                showToast(error.message || 'Failed to update payment status.', 'error');
            } finally {
                editSave.disabled = false;
                editSave.innerHTML = defaultHtml;
            }
        });
    }

    // ── Delete Modal ─────────────────────────────────────────────────────────
    const deleteModal   = document.getElementById('slDeleteModal');
    const deleteName    = document.getElementById('slDeleteName');
    const deleteClose   = document.getElementById('slDeleteClose');
    const deleteCancel  = document.getElementById('slDeleteCancel');
    const deleteConfirm = document.getElementById('slDeleteConfirm');
    let pendingDeleteIdx = null;

    function openDeleteModal(idx) {
        pendingDeleteIdx = idx;
        if (deleteName) deleteName.textContent = formatName(filtered[idx]);
        if (deleteModal) deleteModal.style.display = 'flex';
    }

    function closeDeleteModal() {
        pendingDeleteIdx = null;
        if (deleteModal) deleteModal.style.display = 'none';
    }

    if (deleteClose)  deleteClose.addEventListener('click', closeDeleteModal);
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
    if (deleteModal)  deleteModal.addEventListener('click', e => { if (e.target === deleteModal) closeDeleteModal(); });

    if (deleteConfirm) {
        deleteConfirm.addEventListener('click', async () => {
            if (pendingDeleteIdx === null) return;
            const target = filtered[pendingDeleteIdx];
            if (!target?.id) return;

            const defaultHtml = deleteConfirm.innerHTML;
            deleteConfirm.disabled = true;
            deleteConfirm.innerHTML = '<span class="schol-save-spinner"></span> Revoking...';

            try {
                await splApiFetch(`/api/program-applications/${target.id}/status?letter=${SPL_PROGRAM_LETTER}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        status: 'rejected',
                        rejection_reasons: ['Revoked by SK Official'],
                        rejection_reason: 'Revoked by SK Official',
                        letter: SPL_PROGRAM_LETTER,
                    }),
                });

                approvedList = approvedList.filter((item) => Number(item.id) !== Number(target.id));
                applyFilters();
                updateStats(approvedList);
                closeDeleteModal();
                showToast('Participant revoked and moved to Rejected Sports.');
                broadcastSportsEvent('revoked', target.id);
            } catch (error) {
                showToast(error.message || 'Failed to revoke participant.', 'error');
            } finally {
                deleteConfirm.disabled = false;
                deleteConfirm.innerHTML = defaultHtml;
            }
        });
    }

    // ── Pagination Controls ──────────────────────────────────────────────────
    const firstBtn = document.getElementById('slFirstPage');
    const prevBtn  = document.getElementById('slPrevPage');
    const nextBtn  = document.getElementById('slNextPage');
    const lastBtn  = document.getElementById('slLastPage');

    if (firstBtn) firstBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage = 1; renderTable(filtered); } });
    if (prevBtn)  prevBtn.addEventListener('click',  () => { if (currentPage > 1) { currentPage--; renderTable(filtered); } });
    if (nextBtn)  nextBtn.addEventListener('click',  () => { const tp = Math.ceil(filtered.length/perPage); if (currentPage < tp) { currentPage++; renderTable(filtered); } });
    if (lastBtn)  lastBtn.addEventListener('click',  () => { const tp = Math.ceil(filtered.length/perPage); if (currentPage < tp) { currentPage = tp; renderTable(filtered); } });

    // ── Render Table ─────────────────────────────────────────────────────────
    function renderTable(list) {
        if (!tbody) return;

        // Pagination
        const totalPages = Math.max(1, Math.ceil(list.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const page  = list.slice(start, start + perPage);

        // Update pagination info
        const showStart = document.getElementById('slShowingStart');
        const showEnd   = document.getElementById('slShowingEnd');
        const totalRec  = document.getElementById('slTotalRecords');
        if (showStart) showStart.textContent = list.length ? start + 1 : 0;
        if (showEnd)   showEnd.textContent   = Math.min(start + perPage, list.length);
        if (totalRec)  totalRec.textContent  = list.length;

        // Page numbers
        const pageNums = document.getElementById('slPageNumbers');
        if (pageNums) {
            let html = '';
            for (let p = 1; p <= Math.min(totalPages, 5); p++) {
                html += `<button type="button" class="sl-page-btn${p === currentPage ? ' active' : ''}" data-page="${p}">${p}</button>`;
            }
            pageNums.innerHTML = html;
            pageNums.querySelectorAll('[data-page]').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentPage = parseInt(btn.getAttribute('data-page'), 10);
                    renderTable(list);
                });
            });
        }

        if (page.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px 20px;color:#6b7280;font-size:14px;">No approved sports participants found.</td></tr>`;
            return;
        }

        tbody.innerHTML = page.map((a, i) => {
            const name = formatName(a);
            const globalIdx = start + i;
            const paymentStatus = a.paymentStatus || 'Not Paid';
            const paymentBadge = paymentStatus === 'Paid'
                ? '<span class="spl-badge spl-badge-paid">PAID</span>'
                : '<span class="spl-badge spl-badge-notpaid">UNPAID</span>';
            return `
            <tr data-participant-id="${a.id}">
                <td class="spl-td-name">${name}</td>
                <td>${a.sport || '—'}</td>
                <td>${a.division || '—'}</td>
                <td>${a.age || '—'}</td>
                <td>${a.contact || '—'}</td>
                <td>${a.dateApplied || '—'}</td>
                <td>${paymentBadge}</td>
                <td><span class="spl-badge spl-badge-approved">APPROVED</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-action="view" data-idx="${globalIdx}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-action="edit" data-idx="${globalIdx}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-action="delete" data-idx="${globalIdx}">Revoke</button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-idx'), 10);
                const action = this.getAttribute('data-action');

                if (action === 'delete') {
                    openDeleteModal(idx);
                } else if (action === 'edit') {
                    openEditModal(filtered[idx]);
                } else {
                    openViewModal(filtered[idx]);
                }
            });
        });
    }

    // ── Open View Modal ──────────────────────────────────────────────────────
    function openViewModal(a) {
        const body = document.getElementById('slViewBody');
        if (!modal || !body) return;

        const file = a.requirementFile || { name: 'requirements.pdf', size: '4.2 MB' };
        const reqFileHTML = `
            <a href="#" download="${file.name}" class="spl-req-file-card" title="Click to download ${file.name}">
                <div class="spl-req-file-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                </div>
                <div class="spl-req-file-info">
                    <div class="spl-req-file-name">${file.name}</div>
                    <div class="spl-req-file-meta">${file.size} &nbsp;·&nbsp; Max 10 MB</div>
                </div>
                <span class="spl-req-file-badge">Uploaded</span>
            </a>`;

        const paymentStatus = a.paymentStatus || 'Not Paid';
        const isPaid = paymentStatus === 'Paid';

        body.innerHTML = `
        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Personal Information</div>
            <div class="spl-detail-grid spl-grid-3">
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Last Name</span>
                    <span class="spl-detail-value">${a.lastName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">First Name</span>
                    <span class="spl-detail-value">${a.firstName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Middle Name</span>
                    <span class="spl-detail-value">${a.middleName || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Date of Birth</span>
                    <span class="spl-detail-value">${a.dateOfBirth || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Age</span>
                    <span class="spl-detail-value">${a.age || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Contact No.</span>
                    <span class="spl-detail-value">${a.contact || '—'}</span>
                </div>
                <div class="spl-detail-item spl-detail-full">
                    <span class="spl-detail-label">Address</span>
                    <span class="spl-detail-value">${a.address || '—'}</span>
                </div>
                <div class="spl-detail-item spl-detail-full">
                    <span class="spl-detail-label">Email</span>
                    <span class="spl-detail-value">${a.email || '—'}</span>
                </div>
            </div>
        </div>

        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Sports Information</div>
            <div class="spl-detail-grid">
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Sport</span>
                    <span class="spl-detail-value">${a.sport || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Division</span>
                    <span class="spl-detail-value">${a.division || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Date Applied</span>
                    <span class="spl-detail-value">${a.dateApplied || '—'}</span>
                </div>
                <div class="spl-detail-item">
                    <span class="spl-detail-label">Status</span>
                    <span class="spl-detail-value"><span class="spl-badge spl-badge-approved">APPROVED</span></span>
                </div>
            </div>
        </div>

        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Submitted Requirements</div>
            ${reqFileHTML}
        </div>

        <div class="spl-detail-card">
            <div class="spl-detail-section-title">Payment Status</div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <span class="spl-payment-pill ${isPaid ? 'spl-payment-paid' : 'spl-payment-paid-off'}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Paid
                </span>
                <span class="spl-payment-pill ${!isPaid ? 'spl-payment-notpaid' : 'spl-payment-notpaid-off'}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Not Paid
                </span>
            </div>
        </div>
        `;

        modal.style.display = 'flex';
    }

    // ── Update Stats ─────────────────────────────────────────────────────────
    function updateStats(list) {
        const total     = list.length;
        const pending   = list.filter(a => !a.paymentStatus || a.paymentStatus === 'Unpaid' || a.paymentStatus === 'Not Paid').length;
        const confirmed = list.filter(a => a.paymentStatus === 'Paid').length;
        const cancelled = 0; // Cancelled handled separately if needed

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('slStatTotal',     total);
        set('slStatPending',   pending);
        set('slStatPaid',      confirmed);
        set('slStatCancelled', cancelled);
    }

    // ── Export CSV ───────────────────────────────────────────────────────────
    function exportCsv(list) {
        if (list.length === 0) { showToast('No data to export.', 'error'); return; }
        const headers = ['Full Name', 'Sport', 'Division', 'Contact', 'Email', 'Date Applied', 'Status'];
        const rows = list.map(a => [
            formatName(a),
            a.sport || '',
            a.division || '',
            a.contact || '',
            a.email || '',
            a.dateApplied || '',
            'Approved'
        ]);
        let csv = headers.join(',') + '\n';
        rows.forEach(r => { csv += r.map(c => `"${c}"`).join(',') + '\n'; });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `approved_participants_${new Date().toISOString().split('T')[0]}.csv`;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function formatName(a) {
        let name = `${a.lastName || ''}, ${a.firstName || ''}`;
        if (a.middleName) name += ` ${a.middleName.charAt(0)}.`;
        if (a.suffix) name += ` ${a.suffix}`;
        return name.trim();
    }

    // ── Realtime sync (same tab + other tabs) ───────────────────────────────
    listenSportsEvents((payload) => {
        if (payload.type === 'approved') {
            reloadApprovedList().then((items) => {
                approvedList = items;
                applyFilters();
                updateStats(approvedList);
            }).catch(() => {});
            return;
        }
        if (payload.type === 'revoked') {
            approvedList = approvedList.filter((item) => Number(item.id) !== Number(payload.applicationId));
            applyFilters();
            updateStats(approvedList);
        }
    });

    function showToast(msg, type) {
        // Reuse existing toast element on page or create one
        let toast = document.getElementById('slToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'slToast';
            toast.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:700;color:#fff;z-index:9999;display:none;align-items:center;gap:8px;box-shadow:0 4px 14px rgba(0,0,0,.2);';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.background = type === 'error' ? '#ef4444' : '#22c55e';
        toast.style.display = 'flex';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => { toast.style.display = 'none'; }, 2800);
    }
}

// ── Sample Approved Data (fallback) ──────────────────────────────────────────
function getSampleApprovedData() {
    return [
        {
            id: 3001,
            lastName: 'Dela Cruz', firstName: 'Juan', middleName: 'Santos', suffix: '',
            dateOfBirth: '2003-05-15', age: 22,
            contact: '09171234567', email: 'juan.delacruz@email.com',
            address: '123 Main St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Basketball', division: 'Youth Division (18-21)',
            dateApplied: 'Apr 28, 2026',
            requirementFile: { name: 'requirements.pdf', size: '4.2 MB' },
            paymentStatus: 'Paid',
            status: 'Approved'
        },
        {
            id: 3002,
            lastName: 'Santos', firstName: 'Maria', middleName: 'Reyes', suffix: '',
            dateOfBirth: '2001-08-22', age: 24,
            contact: '09281234567', email: 'maria.santos@email.com',
            address: '456 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Volleyball', division: 'Senior Division (22-25)',
            dateApplied: 'Apr 29, 2026',
            requirementFile: { name: 'requirements.pdf', size: '3.8 MB' },
            paymentStatus: 'Paid',
            status: 'Approved'
        },
        {
            id: 3003,
            lastName: 'Reyes', firstName: 'Pedro', middleName: 'Garcia', suffix: 'Jr.',
            dateOfBirth: '2008-03-10', age: 18,
            contact: '09391234567', email: 'pedro.reyes@email.com',
            address: '789 Bonifacio St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Basketball', division: 'Junior Division (15-17)',
            dateApplied: 'Apr 30, 2026',
            requirementFile: { name: 'requirements.pdf', size: '5.1 MB' },
            paymentStatus: 'Not Paid',
            status: 'Approved'
        },
        {
            id: 3004,
            lastName: 'Garcia', firstName: 'Ana', middleName: 'Lopez', suffix: '',
            dateOfBirth: '2005-11-20', age: 20,
            contact: '09461234567', email: 'ana.garcia@email.com',
            address: '101 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Volleyball', division: 'Youth Division (18-21)',
            dateApplied: 'May 2, 2026',
            requirementFile: { name: 'requirements.pdf', size: '3.5 MB' },
            paymentStatus: 'Paid',
            status: 'Approved'
        },
        {
            id: 3005,
            lastName: 'Villanueva', firstName: 'Carlo', middleName: 'Cruz', suffix: '',
            dateOfBirth: '2000-03-05', age: 26,
            contact: '09551234567', email: 'carlo.villanueva@email.com',
            address: '222 Aguinaldo Blvd., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Swimming', division: 'Open Division (26-30)',
            dateApplied: 'May 5, 2026',
            requirementFile: { name: 'requirements.pdf', size: '4.7 MB' },
            paymentStatus: 'Paid',
            status: 'Approved'
        },
        {
            id: 3006,
            lastName: 'Mendoza', firstName: 'Rosa', middleName: 'Bautista', suffix: '',
            dateOfBirth: '2007-07-14', age: 18,
            contact: '09671234567', email: 'rosa.mendoza@email.com',
            address: '333 Del Pilar St., Brgy. Calios, Santa Cruz, Laguna',
            sport: 'Badminton', division: 'Junior Division (15-17)',
            dateApplied: 'May 8, 2026',
            requirementFile: { name: 'requirements.pdf', size: '2.9 MB' },
            paymentStatus: 'Not Paid',
            status: 'Approved'
        },
    ];
}
