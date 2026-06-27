/**
 * Sports Program Requests — DB-backed via /api/program-applications?letter=I
 */
document.addEventListener('DOMContentLoaded', () => {
    const PROGRAM_LETTER = 'I';
    const ICON_MAX = '\u25A1';
    const ICON_RESTORE = '\u29C9';

    const DEFAULT_SPORTS_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix',
        'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
        'region', 'province', 'city', 'barangay', 'purok_zone',
        'youth_classification', 'youth_age_group',
    ];

    const KK_FIELD_LABELS = {
        last_name: 'Last Name',
        first_name: 'First Name',
        middle_name: 'Middle Name',
        suffix: 'Suffix',
        birthday: 'Birthday',
        age: 'Age',
        sex: 'Sex',
        civil_status: 'Civil Status',
        contact_number: 'Contact Number',
        email: 'Email Address',
        region: 'Region',
        province: 'Province',
        city: 'City/Municipality',
        barangay: 'Barangay',
        purok_zone: 'Purok/Zone',
        youth_classification: 'Youth Classification',
        youth_age_group: 'Youth Age Group',
    };

    const tbody = document.getElementById('sportsTableBody');
    const searchInput = document.getElementById('scholSearch');
    const statTotal = document.getElementById('statTotal');
    const statPending = document.getElementById('statPending');
    const statApproved = document.getElementById('statApproved');
    const statRejected = document.getElementById('statRejected');
    const viewModal = document.getElementById('viewModal');
    const viewModalBody = document.getElementById('viewModalBody');
    const viewBox = document.getElementById('viewBox');
    const viewClose = document.getElementById('viewClose');
    const viewMaximize = document.getElementById('viewMaximize');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');
    const rejectReasonModal = document.getElementById('rejectReasonModal');
    const rejectReasonClose = document.getElementById('rejectReasonClose');
    const rejectReasonCancel = document.getElementById('rejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('rejectReasonConfirm');
    const rejectOtherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
    const rejectOtherField = document.getElementById('rejectReasonOtherField');
    const rejectOtherReason = document.getElementById('rejectReasonOtherText');
    const sportsToast = document.getElementById('sportsToast');
    const sportsToastMsg = document.getElementById('sportsToastMsg');

    let applications = [];
    let summary = { total: 0, pending: 0, approved: 0, rejected: 0 };
    let currentApplicationId = null;
    let isReviewing = false;

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

    function bumpSummaryAfterReview(status) {
        summary.pending = Math.max(0, (summary.pending ?? 0) - 1);
        if (status === 'approved') summary.approved = (summary.approved ?? 0) + 1;
        if (status === 'rejected') summary.rejected = (summary.rejected ?? 0) + 1;
        renderStats(summary);
    }

    function removeApplicationFromTable(id) {
        const row = tbody?.querySelector(`tr[data-app-id="${id}"]`);
        if (row) {
            row.style.transition = 'opacity 0.2s ease';
            row.style.opacity = '0';
        }
        setTimeout(() => {
            applications = applications.filter((app) => Number(app.id) !== Number(id));
            renderTable();
        }, row ? 180 : 0);
    }

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

    function setMaximizeButton(btn, isMax) {
        if (!btn) return;
        btn.textContent = isMax ? ICON_RESTORE : ICON_MAX;
        btn.title = isMax ? 'Restore Down' : 'Maximize';
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

    function normalizeDocuments(docs) {
        if (!docs) return [];
        if (Array.isArray(docs)) return docs;
        if (typeof docs === 'object') return Object.values(docs);
        return [];
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
            <div style="display:flex;gap:14px;align-items:flex-start;padding:14px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
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

    function renderUploadedDocumentsSection(documents) {
        const docs = normalizeDocuments(documents);
        if (!docs.length) {
            return '<span style="font-size:14px;color:#9ca3af;">No documents uploaded</span>';
        }
        return docs.map((doc) => renderDocumentCard(doc)).join('');
    }

    function renderFormAnswers(customAnswers, customQuestions = []) {
        const answersById = {};
        (customAnswers || []).forEach((item) => {
            const id = String(item.question_id ?? item.id ?? '');
            if (id) answersById[id] = item;
        });

        const items = (customQuestions || []).length
            ? customQuestions.map((question, index) => {
                const id = String(question.id ?? '');
                const stored = answersById[id];
                return {
                    question: question.label || stored?.question_label || `Question ${index + 1}`,
                    question_type: question.type || stored?.question_type || '',
                    answer: stored?.answer ?? '—',
                };
            })
            : (customAnswers || []).map((item, index) => ({
                question: item.question_label || item.label || `Question ${index + 1}`,
                question_type: item.question_type || '',
                answer: item.answer ?? '—',
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

    function renderKkProfileSection(app) {
        const kkData = app.kk_profile_data || {};
        const program = app.schedule_program || {};
        const kkFields = (program.kk_profiling_fields?.length ? program.kk_profiling_fields : DEFAULT_SPORTS_KK_FIELDS)
            .filter((field) => field !== 'full_name');

        const fieldHtml = kkFields.map((field) => {
            const value = kkData[field];
            if (!value) return '';
            const label = KK_FIELD_LABELS[field] || field.replace(/_/g, ' ');
            return `
                <div>
                    <label style="font-size:13px;font-weight:600;color:#0369a1;margin-bottom:6px;display:block;">${escapeHtml(label)}</label>
                    <div style="font-size:15px;color:#111827;padding:10px 14px;background:#fff;border-radius:6px;border:1px solid #bae6fd;">${escapeHtml(value)}</div>
                </div>`;
        }).filter(Boolean).join('');

        if (!fieldHtml) {
            return '';
        }

        return `
            <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:24px;margin-bottom:20px;">
                <h4 style="font-size:16px;font-weight:700;color:#0369a1;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                    KK Profile Information
                    <span style="margin-left:auto;font-size:12px;font-weight:600;color:#64748b;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #0ea5e9;">Auto-filled from KK Profile</span>
                </h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    ${fieldHtml}
                </div>
            </div>`;
    }

    function formatRequirementsCell(app) {
        const labels = app.document_labels || [];
        if (labels.length) {
            return labels.map((label) => escapeHtml(label)).join(', ');
        }
        const count = app.documents_count ?? 0;
        return count > 0 ? `${count} file(s)` : '0 file(s)';
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
            <tr data-app-id="${app.id}">
                <td>${escapeHtml(app.full_name)}</td>
                <td>${escapeHtml(app.program_name || '—')}</td>
                <td>${escapeHtml(app.age ?? '—')}</td>
                <td>${formatRequirementsCell(app)}</td>
                <td>${escapeHtml(app.date_submitted)}</td>
                <td><span class="schol-pill ${statusClass(app.status)}">${escapeHtml(app.status_label)}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-view="${app.id}">View</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.querySelectorAll('[data-view]').forEach((btn) => {
            btn.addEventListener('click', () => openViewModal(btn.getAttribute('data-view')));
        });
    }

    function renderStats(summary) {
        if (statTotal) statTotal.textContent = String(summary.total ?? 0);
        if (statPending) statPending.textContent = String(summary.pending ?? 0);
        if (statApproved) statApproved.textContent = String(summary.approved ?? 0);
        if (statRejected) statRejected.textContent = String(summary.rejected ?? 0);
    }

    async function loadApplications(showOverlay = false) {
        if (showOverlay && typeof window.showLoading === 'function') window.showLoading();
        try {
            const data = await apiFetch(`/api/program-applications?letter=${PROGRAM_LETTER}&status=pending`);
            applications = Array.isArray(data.data) ? data.data : [];
            summary = data.summary || summary;
            renderStats(summary);
            renderTable();
        } finally {
            if (showOverlay && typeof window.hideLoading === 'function') window.hideLoading();
        }
    }

    function setReviewButtonsLoading(loading) {
        if (btnApprove) {
            btnApprove.disabled = loading;
            btnApprove.innerHTML = loading
                ? '<span class="schol-save-spinner"></span> Approving...'
                : `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Approve`;
        }
        if (btnReject) btnReject.disabled = loading;
        if (rejectReasonConfirm) {
            rejectReasonConfirm.disabled = loading;
            if (loading) {
                rejectReasonConfirm.dataset.defaultHtml = rejectReasonConfirm.innerHTML;
                rejectReasonConfirm.innerHTML = '<span class="schol-save-spinner"></span> Rejecting...';
            } else {
                rejectReasonConfirm.innerHTML = rejectReasonConfirm.dataset.defaultHtml || 'Confirm Rejection';
            }
        }
    }

    function renderViewModalContent(app) {
        if (!viewModalBody || !app) return;

        const program = app.schedule_program || {};
        const docsHtml = renderUploadedDocumentsSection(app.required_documents);
        const kkProfileHtml = renderKkProfileSection(app);
        const answersHtml = renderFormAnswers(app.custom_answers, program.custom_questions || []);

        viewModalBody.innerHTML = `
            ${kkProfileHtml}
            <div style="background:#fff;border:2px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;">Application Summary</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    <div><strong>Applicant</strong><br>${escapeHtml(app.full_name)}</div>
                    <div><strong>Program</strong><br>${escapeHtml(app.program_name || '—')}</div>
                    <div><strong>Age</strong><br>${escapeHtml(app.age ?? '—')}</div>
                    <div><strong>Status</strong><br>${escapeHtml(app.status_label)}</div>
                    <div><strong>Contact</strong><br>${escapeHtml(app.contact_number || '—')}</div>
                    <div><strong>Email</strong><br>${escapeHtml(app.email || '—')}</div>
                </div>
            </div>
            <h4 style="margin:0 0 12px;">Sports Application Responses</h4>
            <div style="margin-bottom:20px;">${answersHtml}</div>
            <h4 style="margin:0 0 12px;">Uploaded Documents</h4>
            <div style="margin-bottom:20px;">${docsHtml}</div>
        `;

        const isPending = app.status === 'pending';
        if (btnApprove) btnApprove.style.display = isPending ? 'inline-flex' : 'none';
        if (btnReject) btnReject.style.display = isPending ? 'inline-flex' : 'none';
    }

    async function openViewModal(id) {
        currentApplicationId = id;
        if (!viewModal || !viewModalBody) return;

        viewModalBody.innerHTML = '<p style="padding:24px;color:#6b7280;">Loading application details...</p>';
        viewModal.style.display = 'flex';
        if (btnApprove) btnApprove.style.display = 'none';
        if (btnReject) btnReject.style.display = 'none';

        try {
            const data = await apiFetch(`/api/program-applications/${id}?letter=${PROGRAM_LETTER}`);
            renderViewModalContent(data.data);
        } catch (error) {
            closeViewModal();
            showToast(error.message || 'Failed to load application.', 'error');
        }
    }

    function closeViewModal() {
        currentApplicationId = null;
        if (viewModal) {
            viewModal.style.display = 'none';
            viewModal.classList.remove('schol-modal-maximized');
        }
        if (viewBox) viewBox.classList.remove('schol-modal-maximized');
        setMaximizeButton(viewMaximize, false);
    }

    function openRejectModal(id) {
        currentApplicationId = id;
        if (rejectOtherReason) rejectOtherReason.value = '';
        if (rejectOtherField) rejectOtherField.style.display = 'none';
        document.querySelectorAll('.reject-reason-checkbox, #rejectReasonOtherCheckbox').forEach((input) => { input.checked = false; });
        if (rejectReasonModal) rejectReasonModal.style.display = 'flex';
    }

    function closeRejectModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
    }

    async function updateStatus(id, status, rejectionReasons = null, rejectionReason = null) {
        if (isReviewing) return;
        isReviewing = true;
        setReviewButtonsLoading(true);

        try {
            await apiFetch(`/api/program-applications/${id}/status?letter=${PROGRAM_LETTER}`, {
                method: 'PUT',
                body: JSON.stringify({
                    status,
                    rejection_reasons: rejectionReasons,
                    rejection_reason: rejectionReason,
                    letter: PROGRAM_LETTER,
                }),
            });
            showToast(status === 'approved' ? 'Application approved and moved to Approved Participants.' : 'Application rejected and moved to Rejected Sports.');
            closeViewModal();
            closeRejectModal();
            bumpSummaryAfterReview(status);
            removeApplicationFromTable(id);
            broadcastSportsEvent(status === 'approved' ? 'approved' : 'rejected', id);
        } finally {
            isReviewing = false;
            setReviewButtonsLoading(false);
        }
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);
    if (viewClose) viewClose.addEventListener('click', closeViewModal);
    if (viewModal) viewModal.addEventListener('click', (e) => { if (e.target === viewModal) closeViewModal(); });

    if (viewMaximize && viewBox) {
        viewMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !viewBox.classList.contains('schol-modal-maximized');
            viewModal.classList.toggle('schol-modal-maximized', isMax);
            viewBox.classList.toggle('schol-modal-maximized', isMax);
            setMaximizeButton(viewMaximize, isMax);
        });
    }

    if (rejectOtherCheckbox && rejectOtherField) {
        rejectOtherCheckbox.addEventListener('change', () => {
            rejectOtherField.style.display = rejectOtherCheckbox.checked ? 'block' : 'none';
            if (!rejectOtherCheckbox.checked && rejectOtherReason) rejectOtherReason.value = '';
        });
    }

    if (btnApprove) {
        btnApprove.addEventListener('click', async () => {
            if (!currentApplicationId || isReviewing) return;
            try {
                await updateStatus(currentApplicationId, 'approved');
            } catch (error) {
                showToast(error.message || 'Failed to approve application.', 'error');
            }
        });
    }

    if (btnReject) {
        btnReject.addEventListener('click', () => {
            if (!currentApplicationId) return;
            openRejectModal(currentApplicationId);
        });
    }

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
                showToast(error.message || 'Failed to reject application.', 'error');
            }
        });
    }

    listenSportsEvents((payload) => {
        if (payload.type === 'restored') loadApplications(false).catch(() => {});
    });

    (async () => {
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="saf-table-empty">Loading applications…</td></tr>';
        try {
            await loadApplications(false);
        } catch (error) {
            showToast(error.message || 'Failed to load applications.', 'error');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="saf-table-empty">Unable to load applications.</td></tr>';
        }
    })();
});
