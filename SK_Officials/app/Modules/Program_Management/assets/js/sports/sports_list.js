// ── Sports List Page JavaScript ───────────────────────────────────────────

const SPL_PROGRAM_LETTER = 'I';

document.addEventListener('DOMContentLoaded', () => {
    (async () => {
        try {
            await initSportsList();
        } catch (error) {
            const tbody = document.getElementById('slTableBody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="10" class="spl-empty">Unable to load approved participants.</td></tr>';
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function sportBadgeClass(sportKey) {
    const key = String(sportKey || '').toLowerCase();
    return key === 'other' ? 'saf-sport-badge is-other' : 'saf-sport-badge';
}

function mapApprovedParticipant(app) {
    const approvedAt = app.reviewed_at
        ? new Date(app.reviewed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
        : (app.date_submitted || '—');

    return {
        id: app.id,
        fullName: app.full_name || '',
        lastName: app.last_name || '',
        firstName: app.first_name || '',
        middleName: app.middle_name || '',
        suffix: app.suffix || '',
        age: app.age,
        contact: app.contact_number || '—',
        email: app.email || '—',
        sportKey: app.sport_key || 'other',
        sportLabel: app.sport_label || '—',
        teamName: app.team_name || '—',
        programName: app.program_name || '—',
        dateApproved: approvedAt,
        createdAt: app.created_at || null,
        reviewedAt: app.reviewed_at || null,
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
    if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="spl-empty">Loading participants…</td></tr>';

    let approvedList = await reloadApprovedList();
    let filtered = [...approvedList];
    const applicationDetailsCache = new Map();

    const searchInput = document.getElementById('slSearchInput');
    const teamSearch = document.getElementById('slTeamSearch');
    const filterSport = document.getElementById('slSportFilter');
    const filterPayment = document.getElementById('slPaymentFilter');
    const dateFilter = document.getElementById('slDateFilter');

    // ── Pagination State ──────────────────────────────────────────────────────
    let currentPage = 1;
    let recordsPerPage = parseInt(document.getElementById('slRowsPerPageSelect')?.value || '10', 10);

    function getTotalPages(totalRecords) {
        return Math.max(1, Math.ceil(totalRecords / recordsPerPage) || 1);
    }

    function updatePaginationFooter(totalRecords) {
        const totalPages = getTotalPages(totalRecords);
        const pageInput = document.getElementById('slPageInput');
        const totalPagesEl = document.getElementById('slTotalPages');
        const prevBtn = document.getElementById('slPrevBtn');
        const nextBtn = document.getElementById('slNextBtn');
        const info = document.getElementById('slPaginationInfo');

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        if (pageInput) {
            pageInput.value = String(currentPage);
            pageInput.min = '1';
            pageInput.max = String(totalPages);
        }

        if (totalPagesEl) {
            totalPagesEl.textContent = String(totalPages);
        }

        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        if (info) {
            info.textContent = `${totalRecords} record${totalRecords === 1 ? '' : 's'}`;
        }
    }

    function goToPage(page, list) {
        const totalPages = getTotalPages(list.length);
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable(list);
        }
    }

    // ── Render ──────────────────────────────────────────────────────────────
    renderTable(filtered);
    updateStats(approvedList);

    // ── Filters ─────────────────────────────────────────────────────────────
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (teamSearch) teamSearch.addEventListener('input', applyFilters);
    if (filterSport) filterSport.addEventListener('change', applyFilters);
    if (filterPayment) filterPayment.addEventListener('change', applyFilters);
    if (dateFilter) dateFilter.addEventListener('change', applyFilters);

    const rowsPerPageSelect = document.getElementById('slRowsPerPageSelect');
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', () => {
            recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
            currentPage = 1;
            renderTable(filtered);
        });
    }

    const prevBtn = document.getElementById('slPrevBtn');
    const nextBtn = document.getElementById('slNextBtn');
    const pageInput = document.getElementById('slPageInput');

    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1, filtered));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1, filtered));
    if (pageInput) {
        pageInput.addEventListener('change', () => {
            const page = parseInt(pageInput.value, 10);
            if (!Number.isNaN(page)) {
                goToPage(page, filtered);
            }
        });
    }

    function parseApprovedDate(participant) {
        const raw = participant.reviewedAt || participant.createdAt;
        if (!raw) return null;
        const date = new Date(raw);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function matchesDateFilter(participant) {
        const filter = dateFilter?.value || 'all';
        if (filter === 'all') return true;

        const approved = parseApprovedDate(participant);
        if (!approved) return true;

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        if (filter === 'recent') {
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            return approved >= weekAgo;
        }

        if (filter === 'monthly') {
            return approved.getFullYear() === now.getFullYear() && approved.getMonth() === now.getMonth();
        }

        if (filter === 'yearly') {
            return approved.getFullYear() === now.getFullYear();
        }

        return true;
    }

    function applyFilters() {
        const search = (searchInput?.value || '').trim().toLowerCase();
        const teamQuery = (teamSearch?.value || '').trim().toLowerCase();
        const sportFilter = (filterSport?.value || 'all').toLowerCase();
        const payment = filterPayment?.value || '';

        filtered = approvedList.filter((a) => {
            if (!matchesDateFilter(a)) return false;

            const appSportKey = String(a.sportKey || 'other').toLowerCase();
            if (sportFilter !== 'all' && appSportKey !== sportFilter) return false;

            const teamName = String(a.teamName || '').toLowerCase();
            if (teamQuery && !teamName.includes(teamQuery)) return false;

            if (payment && a.paymentStatus !== payment) return false;

            if (!search) return true;

            const name = formatName(a).toLowerCase();
            return name.includes(search)
                || a.programName.toLowerCase().includes(search)
                || a.sportLabel.toLowerCase().includes(search)
                || teamName.includes(search)
                || String(a.contact || '').includes(search);
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
    const editTeamInfo = document.getElementById('slEditTeamInfo');
    const editTeamName = document.getElementById('slEditTeamName');
    const editTeamCount = document.getElementById('slEditTeamCount');

    function normalizeTeamKey(name) {
        const value = String(name || '').trim().toLowerCase();
        return value && value !== '—' ? value : '';
    }

    function getTeamMembers(participant) {
        const teamKey = normalizeTeamKey(participant?.teamName);
        if (!teamKey) return [participant];
        return approvedList.filter((item) => normalizeTeamKey(item.teamName) === teamKey);
    }

    function openPaymentsModal(participant) {
        if (!participant || !editModal) return;
        if (editParticipantId) editParticipantId.value = String(participant.id);
        if (editParticipantName) editParticipantName.textContent = formatName(participant);
        if (editPaymentSelect) editPaymentSelect.value = participant.paymentStatus === 'Paid' ? 'Paid' : 'Unpaid';

        const teamMembers = getTeamMembers(participant);
        if (editTeamInfo) {
            if (teamMembers.length > 1) {
                editTeamInfo.style.display = 'block';
                if (editTeamName) editTeamName.textContent = participant.teamName || '—';
                if (editTeamCount) editTeamCount.textContent = String(teamMembers.length);
            } else {
                editTeamInfo.style.display = 'none';
            }
        }

        editModal.style.display = 'flex';
    }

    function closeEditModal() {
        if (editModal) editModal.style.display = 'none';
        if (editBox) editBox.classList.remove('spl-modal-maximized');
        if (editModal) editModal.classList.remove('spl-overlay-maximized');
        if (editMaximize) { editMaximize.textContent = '□'; editMaximize.title = 'Maximize'; }
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

            const teamMembers = getTeamMembers(approvedList.find((item) => Number(item.id) === id) || { id });
            const applyToTeam = teamMembers.length > 1;
            const defaultHtml = editSave.innerHTML;
            editSave.disabled = true;
            if (typeof window.showLoading === 'function') {
                window.showLoading('Saving payment');
            }

            try {
                const result = await splApiFetch(`/api/program-applications/${id}/payment?letter=${SPL_PROGRAM_LETTER}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        payment_status: paymentStatus,
                        apply_to_team: applyToTeam,
                        letter: SPL_PROGRAM_LETTER,
                    }),
                });
                approvedList = await reloadApprovedList();
                applyFilters();
                updateStats(approvedList);
                closeEditModal();
                const count = Number(result.updated_count || 1);
                showToast(count > 1
                    ? `Payment updated for ${count} team members successfully!`
                    : 'Payment status updated successfully!');
                broadcastSportsEvent('payment-updated', id);
            } catch (error) {
                showToast(error.message || 'Failed to update payment status.', 'error');
            } finally {
                editSave.disabled = false;
                editSave.innerHTML = defaultHtml;
                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }
            }
        });
    }

    // ── Revoke Modal ─────────────────────────────────────────────────────────
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeName = document.getElementById('slRevokeName');
    const revokeCancel = document.getElementById('slRevokeCancel');
    const revokeConfirm = document.getElementById('slRevokeConfirm');
    const revokeConfirmText = document.getElementById('slRevokeConfirmText');
    const revokeConfirmError = document.getElementById('slRevokeConfirmError');
    let pendingRevokeIdx = null;

    function resetRevokeConfirmButton() {
        if (!revokeConfirm) return;
        revokeConfirm.disabled = true;
        revokeConfirm.classList.remove('is-enabled');
        revokeConfirm.classList.add('is-disabled');
    }

    function syncRevokeConfirmButton() {
        if (!revokeConfirm) return;
        const value = revokeConfirmText?.value?.trim() || '';
        const matched = value === 'Confirm';
        revokeConfirm.disabled = !matched;
        revokeConfirm.classList.toggle('is-enabled', matched);
        revokeConfirm.classList.toggle('is-disabled', !matched);
    }

    function openRevokeModal(idx) {
        pendingRevokeIdx = idx;
        if (revokeName) revokeName.textContent = formatName(filtered[idx]);
        if (revokeConfirmText) revokeConfirmText.value = '';
        if (revokeConfirmError) {
            revokeConfirmError.style.display = 'none';
            revokeConfirmError.textContent = '';
        }
        resetRevokeConfirmButton();
        if (revokeModal) revokeModal.style.display = 'flex';
    }

    function closeRevokeModal() {
        pendingRevokeIdx = null;
        if (revokeModal) revokeModal.style.display = 'none';
    }

    if (revokeCancel) revokeCancel.addEventListener('click', closeRevokeModal);
    if (revokeModal) revokeModal.addEventListener('click', (e) => { if (e.target === revokeModal) closeRevokeModal(); });

    if (revokeConfirmText) {
        revokeConfirmText.addEventListener('input', () => {
            if (revokeConfirmError) {
                revokeConfirmError.style.display = 'none';
                revokeConfirmError.textContent = '';
            }
            syncRevokeConfirmButton();
        });
    }

    if (revokeConfirm) {
        revokeConfirm.addEventListener('click', async () => {
            if (pendingRevokeIdx === null) return;
            const target = filtered[pendingRevokeIdx];
            if (!target?.id) return;

            if ((revokeConfirmText?.value?.trim() || '') !== 'Confirm') {
                if (revokeConfirmError) {
                    revokeConfirmError.textContent = 'Please type Confirm to revoke this approval.';
                    revokeConfirmError.style.display = 'block';
                }
                return;
            }

            const defaultHtml = revokeConfirm.innerHTML;
            revokeConfirm.disabled = true;
            if (typeof window.showLoading === 'function') {
                window.showLoading('Revoking');
            }

            try {
                await splApiFetch(`/api/program-applications/${target.id}/status?letter=${SPL_PROGRAM_LETTER}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        status: 'pending',
                        letter: SPL_PROGRAM_LETTER,
                    }),
                });

                approvedList = approvedList.filter((item) => Number(item.id) !== Number(target.id));
                applyFilters();
                updateStats(approvedList);
                closeRevokeModal();
                showToast('Participant revoked successfully! Returned to Sports Program Requests.');
                broadcastSportsEvent('restored', target.id);
            } catch (error) {
                showToast(error.message || 'Failed to revoke participant.', 'error');
            } finally {
                syncRevokeConfirmButton();
                revokeConfirm.innerHTML = defaultHtml;
                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }
            }
        });
    }

    function renderActionMenuCell(participant, globalIdx) {
        return `
            <td class="col-actions">
                <div class="row-actions-menu">
                    <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
                    <div class="row-actions-dropdown" role="menu">
                        <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-idx="${globalIdx}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>View</span>
                        </button>
                        <button type="button" class="row-actions-item row-actions-item-edit" data-action="payments" data-idx="${globalIdx}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            <span>Payments</span>
                        </button>
                        <button type="button" class="row-actions-item row-actions-item-danger" data-action="revoke" data-idx="${globalIdx}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <span>Revoke</span>
                        </button>
                    </div>
                </div>
            </td>`;
    }

    function bindTableActions() {
        if (!tbody || tbody.dataset.slActionsBound === '1') return;
        tbody.dataset.slActionsBound = '1';

        if (typeof window.bindRowActionsTable === 'function') {
            window.bindRowActionsTable(tbody);
        }

        tbody.addEventListener('click', (event) => {
            const actionBtn = event.target.closest('.row-actions-item[data-action]');
            if (!actionBtn) return;

            const action = actionBtn.getAttribute('data-action');
            const idx = parseInt(actionBtn.getAttribute('data-idx'), 10);
            if (Number.isNaN(idx)) return;

            if (typeof window.closeAllRowActionMenus === 'function') {
                window.closeAllRowActionMenus();
            }

            const participant = filtered[idx];
            if (!participant) return;

            if (action === 'view') {
                openViewModal(participant);
            } else if (action === 'payments') {
                openPaymentsModal(participant);
            } else if (action === 'revoke') {
                openRevokeModal(idx);
            }
        });
    }

    // ── Render Table ─────────────────────────────────────────────────────────
    function renderTable(list) {
        if (!tbody) return;

        const start = (currentPage - 1) * recordsPerPage;
        const page = list.slice(start, start + recordsPerPage);

        updatePaginationFooter(list.length);

        if (page.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:40px 20px;color:#6b7280;font-size:14px;">No approved sports participants found.</td></tr>';
            return;
        }

        tbody.innerHTML = page.map((a, i) => {
            const name = escapeHtml(formatName(a));
            const globalIdx = start + i;
            const paymentStatus = a.paymentStatus || 'Unpaid';
            const paymentBadge = paymentStatus === 'Paid'
                ? '<span class="spl-badge spl-badge-paid">PAID</span>'
                : '<span class="spl-badge spl-badge-notpaid">UNPAID</span>';
            return `
            <tr data-participant-id="${a.id}">
                <td class="spl-td-name">${name}</td>
                <td><span class="${sportBadgeClass(a.sportKey)}">${escapeHtml(a.sportLabel)}</span></td>
                <td><span class="saf-team-name">${escapeHtml(a.teamName)}</span></td>
                <td>${escapeHtml(a.programName)}</td>
                <td>${escapeHtml(a.age ?? '—')}</td>
                <td>${escapeHtml(a.contact)}</td>
                <td>${escapeHtml(a.dateApproved)}</td>
                <td>${paymentBadge}</td>
                <td><span class="spl-badge spl-badge-approved">APPROVED</span></td>
                ${renderActionMenuCell(a, globalIdx)}
            </tr>`;
        }).join('');

        bindTableActions();
    }

    // ── Open View Modal ──────────────────────────────────────────────────────
    async function fetchApplicationDetails(id) {
        const numericId = Number(id);
        if (applicationDetailsCache.has(numericId)) {
            return applicationDetailsCache.get(numericId);
        }
        const data = await splApiFetch(`/api/program-applications/${numericId}?letter=${SPL_PROGRAM_LETTER}`);
        const app = data?.data ?? null;
        if (!app) throw new Error('Application details not found.');
        applicationDetailsCache.set(numericId, app);
        return app;
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

    function renderFormAnswers(customAnswers, customQuestions = []) {
        const answersById = {};
        (customAnswers || []).forEach((item) => {
            if (!item || typeof item !== 'object') return;
            const qid = String(item.question_id ?? item.id ?? '');
            if (qid) answersById[qid] = item;
        });

        const items = (customQuestions || []).length
            ? customQuestions.map((question, index) => {
                const qid = String(question.id ?? '');
                const stored = answersById[qid];
                return {
                    question: question.label || stored?.question_label || `Question ${index + 1}`,
                    question_type: question.type || stored?.question_type || '',
                    answer: stored?.answer ?? '—',
                };
            })
            : (customAnswers || []).map((item, index) => ({
                question: item?.question_label || item?.label || `Question ${index + 1}`,
                question_type: item?.question_type || '',
                answer: item?.answer ?? '—',
            }));

        if (!items.length) {
            return '<p style="color:#94a3b8;">No application questions answered.</p>';
        }

        return items.map((item, idx) => {
            const isFile = item.question_type === 'file' || isDocumentAnswer(item.answer);
            const answerHtml = isFile
                ? renderDocumentCard(item.answer)
                : `<div style="font-size:14px;color:#111827;line-height:1.6;padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #213F99;">${escapeHtml(formatAnswerText(item.answer))}</div>`;
            return `
                <div style="margin-bottom:12px;padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
                    <div style="font-weight:600;margin-bottom:8px;">${idx + 1}. ${escapeHtml(item.question)}</div>
                    ${answerHtml}
                </div>`;
        }).join('');
    }

    async function openViewModal(participant) {
        const body = document.getElementById('slViewBody');
        if (!modal || !body || !participant) return;

        body.innerHTML = '<p style="padding:24px;color:#6b7280;">Loading participant details...</p>';
        modal.style.display = 'flex';

        try {
            const app = await fetchApplicationDetails(participant.id);
            const program = app.schedule_program || {};
            const sportLabel = app.sport_label || participant.sportLabel || '—';
            const docs = Array.isArray(app.required_documents)
                ? app.required_documents
                : (app.required_documents ? Object.values(app.required_documents) : []);
            const docsHtml = docs.length
                ? docs.map((doc) => renderDocumentCard(doc)).join('')
                : '<span style="font-size:14px;color:#9ca3af;">No documents uploaded</span>';
            const answersHtml = renderFormAnswers(app.custom_answers, program.custom_questions || []);
            const paymentStatus = participant.paymentStatus || 'Unpaid';
            const isPaid = paymentStatus === 'Paid';

            body.innerHTML = `
                <div style="background:#fff;border:2px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;">Participant Summary</h4>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                        <div><strong>Applicant</strong><br>${escapeHtml(app.full_name || formatName(participant))}</div>
                        <div><strong>Sport</strong><br>${escapeHtml(sportLabel)}</div>
                        <div><strong>Team</strong><br>${escapeHtml(app.team_name || participant.teamName || '—')}</div>
                        <div><strong>Program</strong><br>${escapeHtml(app.program_name || participant.programName || '—')}</div>
                        <div><strong>Age</strong><br>${escapeHtml(app.age ?? '—')}</div>
                        <div><strong>Contact</strong><br>${escapeHtml(app.contact_number || '—')}</div>
                        <div><strong>Email</strong><br>${escapeHtml(app.email || '—')}</div>
                        <div><strong>Date Approved</strong><br>${escapeHtml(participant.dateApproved || '—')}</div>
                        <div><strong>Payment</strong><br>${isPaid ? 'Paid' : 'Unpaid'}</div>
                    </div>
                </div>
                <h4 style="margin:0 0 12px;">Sports Application Responses</h4>
                <div style="margin-bottom:20px;">${answersHtml}</div>
                <h4 style="margin:0 0 12px;">Uploaded Documents</h4>
                <div>${docsHtml}</div>
            `;
        } catch (error) {
            body.innerHTML = `<p style="padding:24px;color:#b91c1c;">${escapeHtml(error.message || 'Failed to load participant details.')}</p>`;
            showToast(error.message || 'Failed to load participant details.', 'error');
        }
    }

    // ── Update Stats ─────────────────────────────────────────────────────────
    function updateStats(list) {
        const total = list.length;
        const unpaid = list.filter((a) => a.paymentStatus !== 'Paid').length;
        const paid = list.filter((a) => a.paymentStatus === 'Paid').length;
        const sportTypes = new Set(list.map((a) => String(a.sportKey || 'other').toLowerCase())).size;

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('slStatTotal', total);
        set('slStatPending', unpaid);
        set('slStatPaid', paid);
        set('slStatSports', sportTypes);
    }

    // ── Export CSV ───────────────────────────────────────────────────────────
    function exportCsv(list) {
        if (list.length === 0) { showToast('No data to export.', 'error'); return; }
        const headers = ['Full Name', 'Sport', 'Team', 'Program', 'Age', 'Contact', 'Email', 'Date Approved', 'Payment Status'];
        const rows = list.map((a) => [
            formatName(a),
            a.sportLabel || '',
            a.teamName || '',
            a.programName || '',
            a.age ?? '',
            a.contact || '',
            a.email || '',
            a.dateApproved || '',
            a.paymentStatus || '',
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
        if (a.fullName) return a.fullName;
        let name = `${a.lastName || ''}, ${a.firstName || ''}`;
        if (a.middleName) name += ` ${a.middleName.charAt(0)}.`;
        if (a.suffix) name += ` ${a.suffix}`;
        return name.trim();
    }

    // ── Realtime sync (same tab + other tabs) ───────────────────────────────
    listenSportsEvents((payload) => {
        if (payload.type === 'approved') {
            applicationDetailsCache.clear();
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
        if (payload.type === 'restored') {
            approvedList = approvedList.filter((item) => Number(item.id) !== Number(payload.applicationId));
            applyFilters();
            updateStats(approvedList);
        }
    });

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('slToast');
        const msgEl = document.getElementById('slToastMsg');
        if (!toast) return;
        if (msgEl) msgEl.textContent = msg;
        toast.className = 'schol-toast schol-toast-show' + (type === 'error' ? ' schol-toast-error' : '');
        toast.style.display = 'flex';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
            toast.classList.remove('schol-toast-show');
            setTimeout(() => { toast.style.display = 'none'; }, 300);
        }, 3000);
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
