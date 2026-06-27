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
    const teamNameSearch = document.getElementById('teamNameSearch');
    const dateFilter = document.getElementById('scholFilter');
    const sportTypeFilter = document.getElementById('sportTypeFilter');
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
    const rejectReasonCancel = document.getElementById('rejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('rejectReasonConfirm');
    const rejectOtherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
    const rejectOtherField = document.getElementById('rejectReasonOtherField');
    const rejectOtherReason = document.getElementById('rejectReasonOtherText');
    const rejectConfirmText = document.getElementById('rejectReasonConfirmText');
    const rejectConfirmError = document.getElementById('rejectReasonConfirmError');
    const approveConfirmModal = document.getElementById('approveConfirmModal');
    const approveConfirmClose = document.getElementById('approveConfirmClose');
    const approveConfirmCancel = document.getElementById('approveConfirmCancel');
    const approveConfirmBtn = document.getElementById('approveConfirmBtn');
    const sportsToast = document.getElementById('sportsToast');
    const sportsToastMsg = document.getElementById('sportsToastMsg');

    let applications = [];
    let summary = { total: 0, pending: 0, approved: 0, rejected: 0 };
    let currentApplicationId = null;
    let pendingApproveId = null;
    let isReviewing = false;
    const applicationDetailsCache = new Map();

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
            applicationDetailsCache.delete(Number(id));
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
        if (!sportsToast) return;
        if (sportsToastMsg) sportsToastMsg.textContent = message;
        sportsToast.className = 'schol-toast schol-toast-show' + (type === 'error' ? ' schol-toast-error' : '');
        sportsToast.style.display = 'flex';
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => {
            sportsToast.classList.remove('schol-toast-show');
            setTimeout(() => { sportsToast.style.display = 'none'; }, 300);
        }, 3000);
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
            if (!item || typeof item !== 'object') return;
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

    function sportBadgeClass(sportKey) {
        const key = String(sportKey || '').toLowerCase();
        return key === 'other' ? 'saf-sport-badge is-other' : 'saf-sport-badge';
    }

    function statusClass(status) {
        switch (status) {
            case 'approved': return 'schol-pill-approved';
            case 'rejected': return 'schol-pill-rejected';
            case 'cancelled': return 'schol-pill-cancelled';
            default: return 'schol-pill-pending';
        }
    }

    function parseSubmittedDate(app) {
        if (!app?.created_at) return null;
        const date = new Date(app.created_at);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function matchesDateFilter(app) {
        const filter = dateFilter?.value || 'all';
        if (filter === 'all') return true;

        const submitted = parseSubmittedDate(app);
        if (!submitted) return true;

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        if (filter === 'recent') {
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            return submitted >= weekAgo;
        }

        if (filter === 'monthly') {
            return submitted.getFullYear() === now.getFullYear() && submitted.getMonth() === now.getMonth();
        }

        if (filter === 'yearly') {
            return submitted.getFullYear() === now.getFullYear();
        }

        return true;
    }

    function filteredApplications() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        const teamQuery = (teamNameSearch?.value || '').trim().toLowerCase();
        const sportFilter = (sportTypeFilter?.value || 'all').toLowerCase();

        return applications.filter((app) => {
            if (!matchesDateFilter(app)) return false;

            const appSportKey = String(app.sport_key || 'other').toLowerCase();
            if (sportFilter !== 'all' && appSportKey !== sportFilter) return false;

            const teamName = String(app.team_name || '').toLowerCase();
            if (teamQuery && !teamName.includes(teamQuery)) return false;

            if (!query) return true;

            return app.full_name?.toLowerCase().includes(query)
                || app.program_name?.toLowerCase().includes(query)
                || app.sport_label?.toLowerCase().includes(query)
                || teamName.includes(query)
                || app.contact_number?.includes(query);
        });
    }

    function canReviewApp(app) {
        return app?.can_review !== false;
    }

    function reviewBlockedMessage(app) {
        const endLabel = app?.schedule_end_date
            ? new Date(app.schedule_end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
            : 'the application period ends';
        return `Applications can only be approved or rejected after the application period ends on ${endLabel}.`;
    }

    function renderActionMenuCell(app) {
        const isPending = app.status === 'pending';
        const canReview = canReviewApp(app);
        const approveReject = isPending && canReview ? `
            <button type="button" class="row-actions-item row-actions-item-approve" data-action="approve" data-id="${app.id}" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Approve</span>
            </button>
            <button type="button" class="row-actions-item row-actions-item-danger" data-action="reject" data-id="${app.id}" role="menuitem">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                <span>Reject</span>
            </button>
        ` : '';

        return `
            <td class="col-actions">
                <div class="row-actions-menu">
                    <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
                    <div class="row-actions-dropdown" role="menu">
                        <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-id="${app.id}" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>View</span>
                        </button>
                        ${approveReject}
                    </div>
                </div>
            </td>`;
    }

    function renderTable() {
        if (!tbody) return;
        const rows = filteredApplications();

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">No sports applications found.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((app) => `
            <tr data-app-id="${app.id}">
                <td>${escapeHtml(app.full_name)}</td>
                <td><span class="${sportBadgeClass(app.sport_key)}">${escapeHtml(app.sport_label || '—')}</span></td>
                <td><span class="saf-team-name">${escapeHtml(app.team_name || '—')}</span></td>
                <td>${escapeHtml(app.program_name || '—')}</td>
                <td>${escapeHtml(app.age ?? '—')}</td>
                <td>${escapeHtml(app.date_submitted)}</td>
                <td><span class="schol-pill ${statusClass(app.status)}">${escapeHtml(app.status_label)}</span></td>
                ${renderActionMenuCell(app)}
            </tr>
        `).join('');
    }

    function renderStats(stats) {
        if (statTotal) statTotal.textContent = String(stats.total ?? 0);
        if (statPending) statPending.textContent = String(stats.pending ?? 0);
        if (statApproved) statApproved.textContent = String(stats.approved ?? 0);
        if (statRejected) statRejected.textContent = String(stats.rejected ?? 0);
    }

    async function loadApplications(showOverlay = false) {
        if (showOverlay && typeof window.showLoading === 'function') window.showLoading();
        try {
            const data = await apiFetch(`/api/program-applications?letter=${PROGRAM_LETTER}&status=pending`);
            applications = Array.isArray(data.data) ? data.data : [];
            summary = data.summary || summary;
            applicationDetailsCache.clear();
            renderStats(summary);
            renderTable();
        } finally {
            if (showOverlay && typeof window.hideLoading === 'function') window.hideLoading();
        }
    }

    async function fetchApplicationDetails(id) {
        const numericId = Number(id);
        if (applicationDetailsCache.has(numericId)) {
            return applicationDetailsCache.get(numericId);
        }

        const data = await apiFetch(`/api/program-applications/${numericId}?letter=${PROGRAM_LETTER}`);
        const app = data?.data ?? null;
        if (!app) {
            throw new Error('Application details not found.');
        }

        applicationDetailsCache.set(numericId, app);
        return app;
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
            if (loading) {
                rejectReasonConfirm.dataset.defaultHtml = rejectReasonConfirm.innerHTML;
                rejectReasonConfirm.innerHTML = '<span class="schol-save-spinner"></span> Rejecting...';
                rejectReasonConfirm.disabled = true;
                rejectReasonConfirm.classList.remove('is-enabled');
                rejectReasonConfirm.classList.add('is-disabled');
            } else {
                rejectReasonConfirm.innerHTML = rejectReasonConfirm.dataset.defaultHtml || 'Reject';
                syncRejectConfirmButton();
            }
        }
    }

    function renderViewModalContent(app) {
        if (!viewModalBody || !app) return;

        const program = app.schedule_program || {};
        const sportsDetails = program.sports_details || {};
        const sportLabel = app.sport_label || sportsDetails.sport_label || 'Sports';
        const docsHtml = renderUploadedDocumentsSection(app.required_documents);
        const kkProfileHtml = renderKkProfileSection(app);
        const answersHtml = renderFormAnswers(app.custom_answers, program.custom_questions || []);

        viewModalBody.innerHTML = `
            ${kkProfileHtml}
            <div style="background:#fff;border:2px solid #e5e7eb;border-radius:12px;padding:24px;margin-bottom:20px;">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;">Application Summary</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    <div><strong>Applicant</strong><br>${escapeHtml(app.full_name)}</div>
                    <div><strong>Sport</strong><br>${escapeHtml(sportLabel)}</div>
                    <div><strong>Team</strong><br>${escapeHtml(app.team_name || '—')}</div>
                    <div><strong>Program</strong><br>${escapeHtml(app.program_name || '—')}</div>
                    <div><strong>Age</strong><br>${escapeHtml(app.age ?? '—')}</div>
                    <div><strong>Status</strong><br>${escapeHtml(app.status_label)}</div>
                    <div><strong>Contact</strong><br>${escapeHtml(app.contact_number || '—')}</div>
                    <div><strong>Email</strong><br>${escapeHtml(app.email || '—')}</div>
                    <div><strong>Submitted</strong><br>${escapeHtml(app.date_submitted || '—')} ${escapeHtml(app.submitted_time || '')}</div>
                </div>
            </div>
            <h4 style="margin:0 0 12px;">Sports Application Responses</h4>
            <div style="margin-bottom:20px;">${answersHtml}</div>
            <h4 style="margin:0 0 12px;">Uploaded Documents</h4>
            <div style="margin-bottom:20px;">${docsHtml}</div>
        `;

        const isPending = app.status === 'pending';
        const canReview = canReviewApp(app);
        if (btnApprove) {
            btnApprove.style.display = isPending && canReview ? 'inline-flex' : 'none';
            btnApprove.disabled = !canReview;
        }
        if (btnReject) {
            btnReject.style.display = isPending && canReview ? 'inline-flex' : 'none';
            btnReject.disabled = !canReview;
        }

        if (isPending && !canReview) {
            viewModalBody.insertAdjacentHTML('afterbegin', `
                <div style="margin-bottom:16px;padding:12px 16px;background:#fff7ed;border:1px solid #fdba74;border-radius:8px;color:#9a3412;font-size:13px;line-height:1.5;">
                    ${escapeHtml(reviewBlockedMessage(app))}
                </div>`);
        }
    }

    async function openViewModal(id) {
        const numericId = Number(id);
        currentApplicationId = numericId;
        if (!viewModal || !viewModalBody) return;

        viewModalBody.innerHTML = '<p style="padding:24px;color:#6b7280;">Loading application details...</p>';
        viewModal.style.display = 'flex';
        if (btnApprove) btnApprove.style.display = 'none';
        if (btnReject) btnReject.style.display = 'none';

        try {
            const app = await fetchApplicationDetails(numericId);
            renderViewModalContent(app);
        } catch (error) {
            viewModalBody.innerHTML = `<p style="padding:24px;color:#b91c1c;">${escapeHtml(error.message || 'Failed to load application details.')}</p>`;
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
        currentApplicationId = Number(id);
        if (rejectOtherReason) rejectOtherReason.value = '';
        if (rejectConfirmText) rejectConfirmText.value = '';
        if (rejectConfirmError) {
            rejectConfirmError.style.display = 'none';
            rejectConfirmError.textContent = '';
        }
        if (rejectOtherField) rejectOtherField.style.display = 'none';
        document.querySelectorAll('.reject-reason-checkbox, #rejectReasonOtherCheckbox').forEach((input) => { input.checked = false; });
        resetRejectConfirmButton();
        if (rejectReasonModal) {
            rejectReasonModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function resetRejectConfirmButton() {
        if (!rejectReasonConfirm) return;
        rejectReasonConfirm.disabled = true;
        rejectReasonConfirm.classList.remove('is-enabled');
        rejectReasonConfirm.classList.add('is-disabled');
    }

    function syncRejectConfirmButton() {
        if (!rejectReasonConfirm) return;
        const value = rejectConfirmText?.value?.trim() || '';
        const matched = value === 'Confirm';
        rejectReasonConfirm.disabled = !matched;
        rejectReasonConfirm.classList.toggle('is-enabled', matched);
        rejectReasonConfirm.classList.toggle('is-disabled', !matched);
    }

    function openApproveConfirmModal(id) {
        pendingApproveId = Number(id);
        if (approveConfirmModal) {
            approveConfirmModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeApproveConfirmModal() {
        pendingApproveId = null;
        if (approveConfirmModal) approveConfirmModal.style.display = 'none';
        if (!rejectReasonModal || rejectReasonModal.style.display === 'none') {
            document.body.style.overflow = '';
        }
    }

    function collectRejectionReasons() {
        const reasons = [];
        document.querySelectorAll('.reject-reason-checkbox:checked').forEach((el) => {
            if (el.value) reasons.push(el.value);
        });
        const otherText = rejectOtherReason?.value?.trim() || '';
        if (rejectOtherCheckbox?.checked) {
            if (!otherText) return { error: 'Please specify the other rejection reason.' };
            reasons.push(otherText);
        }
        if (!reasons.length) {
            return { error: 'Please select a rejection reason.' };
        }
        if ((rejectConfirmText?.value?.trim() || '') !== 'Confirm') {
            return { error: 'Please type Confirm to reject this application.' };
        }
        return { reasons, rejectionReason: otherText || reasons[0] };
    }

    function closeRejectModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
        if (!approveConfirmModal || approveConfirmModal.style.display === 'none') {
            document.body.style.overflow = '';
        }
    }

    async function updateStatus(id, status, rejectionReasons = null, rejectionReason = null) {
        if (isReviewing) return;
        isReviewing = true;
        setReviewButtonsLoading(true);

        const loadingMessage = status === 'approved'
            ? 'Approving'
            : (status === 'rejected' ? 'Rejecting' : null);
        if (loadingMessage && typeof window.showLoading === 'function') {
            window.showLoading(loadingMessage);
        }

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
            const successMessage = status === 'approved'
                ? 'Application approved successfully!'
                : 'Application rejected successfully!';
            showToast(successMessage);
            closeViewModal();
            closeRejectModal();
            closeApproveConfirmModal();
            bumpSummaryAfterReview(status);
            removeApplicationFromTable(id);
            broadcastSportsEvent(status === 'approved' ? 'approved' : 'rejected', id);
        } finally {
            isReviewing = false;
            setReviewButtonsLoading(false);
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
        }
    }

    function bindTableActions() {
        if (!tbody || tbody.dataset.sportsActionsBound === '1') return;
        tbody.dataset.sportsActionsBound = '1';

        if (typeof window.bindRowActionsTable === 'function') {
            window.bindRowActionsTable(tbody);
        }

        tbody.addEventListener('click', async (event) => {
            const actionBtn = event.target.closest('.row-actions-item[data-action]');
            if (!actionBtn) return;

            const action = actionBtn.getAttribute('data-action');
            const id = Number(actionBtn.getAttribute('data-id'));
            if (!id) return;

            if (typeof window.closeAllRowActionMenus === 'function') {
                window.closeAllRowActionMenus();
            }

            if (action === 'view') {
                openViewModal(id);
                return;
            }

            if (action === 'approve') {
                const record = applications.find((item) => Number(item.id) === id);
                if (record && !canReviewApp(record)) {
                    showToast(reviewBlockedMessage(record), 'error');
                    return;
                }
                openApproveConfirmModal(id);
                return;
            }

            if (action === 'reject') {
                const record = applications.find((item) => Number(item.id) === id);
                if (record && !canReviewApp(record)) {
                    showToast(reviewBlockedMessage(record), 'error');
                    return;
                }
                openRejectModal(id);
            }
        });
    }

    [searchInput, teamNameSearch, dateFilter, sportTypeFilter].forEach((el) => {
        el?.addEventListener('input', renderTable);
        el?.addEventListener('change', renderTable);
    });

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

    if (rejectConfirmText) {
        rejectConfirmText.addEventListener('input', () => {
            if (rejectConfirmError) {
                rejectConfirmError.style.display = 'none';
                rejectConfirmError.textContent = '';
            }
            syncRejectConfirmButton();
        });
    }

    if (btnApprove) {
        btnApprove.addEventListener('click', () => {
            if (!currentApplicationId || isReviewing) return;
            const record = applicationDetailsCache.get(currentApplicationId)
                || applications.find((item) => Number(item.id) === Number(currentApplicationId));
            if (record && !canReviewApp(record)) {
                showToast(reviewBlockedMessage(record), 'error');
                return;
            }
            openApproveConfirmModal(currentApplicationId);
        });
    }

    if (btnReject) {
        btnReject.addEventListener('click', () => {
            if (!currentApplicationId) return;
            openRejectModal(currentApplicationId);
        });
    }

    if (rejectReasonCancel) rejectReasonCancel.addEventListener('click', closeRejectModal);
    if (rejectReasonModal) {
        rejectReasonModal.addEventListener('click', (event) => {
            if (event.target === rejectReasonModal) closeRejectModal();
        });
    }
    if (approveConfirmClose) approveConfirmClose.addEventListener('click', closeApproveConfirmModal);
    if (approveConfirmCancel) approveConfirmCancel.addEventListener('click', closeApproveConfirmModal);
    if (approveConfirmModal) {
        approveConfirmModal.addEventListener('click', (event) => {
            if (event.target === approveConfirmModal) closeApproveConfirmModal();
        });
    }
    if (approveConfirmBtn) {
        approveConfirmBtn.addEventListener('click', async () => {
            if (!pendingApproveId || isReviewing) return;
            try {
                await updateStatus(pendingApproveId, 'approved');
                closeApproveConfirmModal();
            } catch (error) {
                showToast(error.message || 'Failed to approve application.', 'error');
            }
        });
    }
    if (rejectReasonConfirm) {
        resetRejectConfirmButton();
        rejectReasonConfirm.addEventListener('click', async () => {
            const collected = collectRejectionReasons();
            if (collected.error) {
                if (rejectConfirmError) {
                    rejectConfirmError.textContent = collected.error;
                    rejectConfirmError.style.display = 'block';
                } else {
                    showToast(collected.error, 'error');
                }
                return;
            }
            try {
                await updateStatus(
                    currentApplicationId,
                    'rejected',
                    collected.reasons,
                    collected.rejectionReason,
                );
            } catch (error) {
                showToast(error.message || 'Failed to reject application.', 'error');
            }
        });
    }

    listenSportsEvents((payload) => {
        if (payload.type === 'restored') loadApplications(false).catch(() => {});
    });

    bindTableActions();

    (async () => {
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">Loading applications…</td></tr>';
        try {
            await loadApplications(false);
        } catch (error) {
            showToast(error.message || 'Failed to load applications.', 'error');
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">Unable to load applications.</td></tr>';
        }
    })();
});
