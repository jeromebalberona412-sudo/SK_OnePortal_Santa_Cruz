/**
 * Scholarship Landing Page — applications table + view modal
 */
(function () {
    'use strict';

    const scheduleProgramId = Number(window.__scheduleProgramId || 0);
    const startApplicationBtn = document.getElementById('startApplicationBtn');
    const previousApplicationsTable = document.getElementById('previousApplicationsTable');
    const applicationViewModal = document.getElementById('applicationViewModal');
    const applicationViewContainer = document.getElementById('applicationViewContainer');
    const applicationViewClose = document.getElementById('applicationViewClose');
    const applicationViewMaximize = document.getElementById('applicationViewMaximize');
    const applicationCancelModal = document.getElementById('applicationCancelModal');
    const applicationCancelModalBox = document.getElementById('applicationCancelModalBox');
    const applicationCancelClose = document.getElementById('applicationCancelClose');
    const applicationCancelDismissBtn = document.getElementById('applicationCancelDismissBtn');
    const applicationViewTitle = document.getElementById('applicationViewTitle');
    const applicationViewMeta = document.getElementById('applicationViewMeta');
    const applicationViewPersonalInfo = document.getElementById('applicationViewPersonalInfo');
    const applicationViewAnswers = document.getElementById('applicationViewAnswers');
    const applicationViewCancelledInfo = document.getElementById('applicationViewCancelledInfo');
    const applicationViewCancelledType = document.getElementById('applicationViewCancelledType');
    const applicationViewCancelledReason = document.getElementById('applicationViewCancelledReason');
    const applicationCancelType = document.getElementById('applicationCancelType');
    const applicationCancelReason = document.getElementById('applicationCancelReason');
    const applicationCancelCharCount = document.getElementById('applicationCancelCharCount');
    const applicationCancelError = document.getElementById('applicationCancelError');
    const applicationCancelBtn = document.getElementById('applicationCancelBtn');

    const CANCEL_REASON_MAX = 500;

    let currentApplications = [];
    let activeViewApplicationId = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function getStatusClass(status) {
        switch (String(status || '').toLowerCase()) {
            case 'pending':
                return 'sl-status-pending';
            case 'approved':
                return 'sl-status-approved';
            case 'rejected':
                return 'sl-status-rejected';
            case 'cancelled':
                return 'sl-status-cancelled';
            default:
                return 'sl-status-pending';
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isActiveApplication(application) {
        return application && application.status !== 'cancelled';
    }

    async function fetchScheduleProgram() {
        if (!scheduleProgramId) return null;
        const response = await fetch(`/api/kabataan/programs/schedule/${scheduleProgramId}`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            credentials: 'same-origin',
        });
        if (!response.ok) return null;
        return response.json();
    }

    async function fetchApplications() {
        const response = await fetch('/api/kabataan/programs/applications?letter=A', {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            credentials: 'same-origin',
        });
        if (!response.ok) return [];
        const data = await response.json();
        return data.applications || [];
    }

    async function fetchApplication(applicationId) {
        const response = await fetch(`/api/kabataan/programs/applications/${applicationId}`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            credentials: 'same-origin',
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Unable to load application.';
            throw new Error(message);
        }
        return data.application;
    }

    function renderProgramInfo(program) {
        const nameEl = document.getElementById('slProgramName');
        const descEl = document.getElementById('slProgramDescription');
        const periodEl = document.getElementById('slApplicationPeriod');
        const slotsEl = document.getElementById('slAvailableSlots');
        const announcementEl = document.getElementById('slAnnouncement');
        const committeeEl = document.getElementById('slCommittee');
        const statusBadge = document.getElementById('slProgramStatusBadge');

        const availableSlots = program.available_slots ?? program.participation_quantity;

        if (nameEl) nameEl.textContent = program.program_name || 'Scholarship Program';
        if (committeeEl) committeeEl.textContent = program.committee || '—';
        if (descEl) descEl.textContent = program.announcement || program.program_type || 'Educational assistance program for qualified Kabataan members.';
        if (periodEl) periodEl.textContent = `${program.start_date_display || '—'} - ${program.end_date_display || '—'}`;
        if (slotsEl) slotsEl.textContent = availableSlots !== null && availableSlots !== undefined ? String(availableSlots) : '—';
        if (announcementEl) announcementEl.textContent = program.announcement || 'No announcement posted.';
        if (statusBadge) {
            statusBadge.textContent = program.status === 'open' ? 'Open' : 'Closed';
            statusBadge.className = `sl-status-badge ${program.status === 'open' ? 'sl-status-open' : 'sl-status-closed'}`;
        }
    }

    function updateStartButton(applications) {
        if (!startApplicationBtn) return;

        const currentApplication = scheduleProgramId
            ? applications.find((app) => Number(app.schedule_program_id) === scheduleProgramId)
            : null;
        const hasActiveApplication = isActiveApplication(currentApplication);

        if (!scheduleProgramId) {
            startApplicationBtn.disabled = true;
            startApplicationBtn.textContent = 'Start Scholarship Application';
            startApplicationBtn.style.opacity = '0.5';
            startApplicationBtn.style.cursor = 'not-allowed';
            return;
        }

        if (hasActiveApplication) {
            startApplicationBtn.disabled = true;
            startApplicationBtn.textContent = 'Already Applied';
            startApplicationBtn.style.opacity = '0.5';
            startApplicationBtn.style.cursor = 'not-allowed';
            return;
        }

        startApplicationBtn.disabled = false;
        startApplicationBtn.textContent = 'Start Scholarship Application';
        startApplicationBtn.style.opacity = '';
        startApplicationBtn.style.cursor = '';
    }

    function renderPersonalInfoItems(items) {
        if (!items || !items.length) {
            return '<p class="sl-view-empty">No personal information available.</p>';
        }

        return items.map((item) => `
            <div class="sl-view-personal-item">
                <span class="sl-view-personal-label">${escapeHtml(item.label)}</span>
                <span class="sl-view-personal-value">${escapeHtml(item.value || '—')}</span>
            </div>
        `).join('');
    }

    function renderAnswerItems(answers) {
        if (!answers.length) {
            return '<p class="sl-view-empty">No answers submitted for this application.</p>';
        }

        return answers.map((answer) => {
            if (answer.question_type === 'file' && answer.answer && typeof answer.answer === 'object') {
                const file = answer.answer;
                return `
                    <div class="sl-answer-item">
                        <p class="sl-info-label">${escapeHtml(answer.question_label || 'Document')}</p>
                        <a href="${escapeHtml(file.preview_url || file.download_url || '#')}" target="_blank" rel="noopener" class="sl-file-card-link">
                            <span class="sl-file-icon">📄</span>
                            <span>
                                <span class="sl-file-name">${escapeHtml(file.original_name || 'Uploaded PDF')}</span>
                                <span class="sl-file-meta">${escapeHtml(file.size_display || '')}</span>
                            </span>
                        </a>
                    </div>
                `;
            }

            const displayAnswer = Array.isArray(answer.answer)
                ? answer.answer.join(', ')
                : (typeof answer.answer === 'object' ? JSON.stringify(answer.answer) : (answer.answer || '—'));

            return `
                <div class="sl-answer-item">
                    <p class="sl-info-label">${escapeHtml(answer.question_label || 'Question')}</p>
                    <p class="sl-info-value">${escapeHtml(displayAnswer)}</p>
                </div>
            `;
        }).join('');
    }

    function parseCancelDetails(cancelReason) {
        const raw = String(cancelReason || '').trim();
        const match = raw.match(/^\[([^\]]+)\]\s*(.*)$/s);
        if (!match) {
            return { type: 'Cancelled', reason: raw || 'No reason provided.' };
        }
        return {
            type: match[1] || 'Cancelled',
            reason: match[2]?.trim() || 'No reason provided.',
        };
    }

    function renderPreviousApplications(applications) {
        if (!previousApplicationsTable) return;

        currentApplications = applications;

        if (!applications.length) {
            previousApplicationsTable.innerHTML = '<tr class="sl-empty-row"><td colspan="4">No previous applications found.</td></tr>';
            updateStartButton(applications);
            return;
        }

        previousApplicationsTable.innerHTML = applications.map((app) => {
            const canCancel = app.status === 'pending' && app.can_cancel;
            return `
            <tr>
                <td>${escapeHtml(app.program_name || 'Program')}</td>
                <td>${escapeHtml(app.submitted_at || '—')}</td>
                <td><span class="sl-status-badge ${getStatusClass(app.status)}">${escapeHtml(app.status_display || app.status)}</span></td>
                <td>
                    <div class="sl-table-actions">
                        <button type="button" class="sl-btn-action sl-btn-view" data-view-application="${app.id}">View</button>
                        ${canCancel ? `<button type="button" class="sl-btn-action sl-btn-cancel" data-cancel-application="${app.id}">Cancel</button>` : ''}
                    </div>
                </td>
            </tr>
        `;
        }).join('');

        previousApplicationsTable.querySelectorAll('[data-view-application]').forEach((button) => {
            button.addEventListener('click', () => {
                const applicationId = Number(button.getAttribute('data-view-application'));
                if (!applicationId) return;
                openApplicationView(applicationId);
            });
        });

        previousApplicationsTable.querySelectorAll('[data-cancel-application]').forEach((button) => {
            button.addEventListener('click', () => {
                const applicationId = Number(button.getAttribute('data-cancel-application'));
                if (!applicationId) return;
                activeViewApplicationId = applicationId;
                openCancelReasonModal();
            });
        });

        updateStartButton(applications);
    }

    function setViewModalMaximized(isMaximized) {
        if (!applicationViewContainer) return;
        applicationViewContainer.classList.toggle('is-fullscreen', isMaximized);
        if (applicationViewMaximize) {
            applicationViewMaximize.title = isMaximized ? 'Restore Down' : 'Maximize';
            applicationViewMaximize.setAttribute('aria-label', isMaximized ? 'Restore Down' : 'Maximize');
        }
    }

    function openCancelReasonModal() {
        if (applicationCancelType) applicationCancelType.value = '';
        if (applicationCancelReason) applicationCancelReason.value = '';
        updateCancelReasonCharCount();
        if (applicationCancelError) {
            applicationCancelError.hidden = true;
            applicationCancelError.textContent = '';
        }
        if (applicationCancelModal) applicationCancelModal.hidden = false;
    }

    function updateCancelReasonCharCount() {
        if (!applicationCancelCharCount || !applicationCancelReason) return;
        const length = applicationCancelReason.value.length;
        applicationCancelCharCount.textContent = `${length} / ${CANCEL_REASON_MAX} characters`;
        applicationCancelCharCount.classList.toggle('is-limit', length >= CANCEL_REASON_MAX);
    }

    function closeCancelReasonModal() {
        if (applicationCancelModal) applicationCancelModal.hidden = true;
    }

    function openApplicationView(applicationId) {
        activeViewApplicationId = applicationId;
        if (applicationCancelReason) applicationCancelReason.value = '';
        if (applicationCancelError) {
            applicationCancelError.hidden = true;
            applicationCancelError.textContent = '';
        }
        setViewModalMaximized(false);

        if (applicationViewAnswers) {
            applicationViewAnswers.innerHTML = '<p class="sl-view-empty">Loading application details...</p>';
        }
        if (applicationViewPersonalInfo) {
            applicationViewPersonalInfo.innerHTML = '<p class="sl-view-empty">Loading personal information...</p>';
        }

        if (applicationViewModal) {
            applicationViewModal.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        fetchApplication(applicationId)
            .then((application) => {
                if (applicationViewTitle) {
                    applicationViewTitle.textContent = application.program_name || 'Application Details';
                }
                if (applicationViewMeta) {
                    applicationViewMeta.textContent = `${application.program_period || '—'} • Submitted: ${application.submitted_at || '—'} • Status: ${application.status_display || application.status}`;
                }
                if (applicationViewPersonalInfo) {
                    applicationViewPersonalInfo.innerHTML = renderPersonalInfoItems(application.personal_info || []);
                }
                if (applicationViewAnswers) {
                    applicationViewAnswers.innerHTML = renderAnswerItems(application.answers || []);
                }

                if (applicationViewCancelledInfo) {
                    const isCancelled = application.status === 'cancelled';
                    applicationViewCancelledInfo.hidden = !isCancelled;
                    if (isCancelled) {
                        const details = parseCancelDetails(application.cancel_reason);
                        if (applicationViewCancelledType) {
                            applicationViewCancelledType.textContent = `Cancel Type: ${details.type}`;
                        }
                        if (applicationViewCancelledReason) {
                            applicationViewCancelledReason.textContent = details.reason;
                        }
                    }
                }
            })
            .catch((error) => {
                closeApplicationView();
                alert(error.message || 'Unable to load application.');
            });
    }

    function closeApplicationView() {
        activeViewApplicationId = null;
        setViewModalMaximized(false);
        closeCancelReasonModal();
        if (applicationCancelFab) applicationCancelFab.hidden = true;
        if (applicationViewModal) applicationViewModal.hidden = true;
        document.body.style.overflow = '';
    }

    async function cancelApplication(applicationId, cancelReason) {
        const response = await fetch(`/api/kabataan/programs/applications/${applicationId}/cancel`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ cancel_reason: cancelReason }),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Unable to cancel application.';
            throw new Error(message);
        }

        return data;
    }

    async function handleCancelApplication() {
        if (!activeViewApplicationId || !applicationCancelReason) return;

        const cancelType = applicationCancelType?.value?.trim() || '';
        const reason = applicationCancelReason.value.trim();

        if (!cancelType) {
            if (applicationCancelError) {
                applicationCancelError.textContent = 'Please select a cancel type.';
                applicationCancelError.hidden = false;
            }
            return;
        }

        if (reason.length < 3) {
            if (applicationCancelError) {
                applicationCancelError.textContent = 'Please type your cancel reason (at least 3 characters).';
                applicationCancelError.hidden = false;
            }
            return;
        }
        if (reason.length > CANCEL_REASON_MAX) {
            if (applicationCancelError) {
                applicationCancelError.textContent = `Cancel reason must not exceed ${CANCEL_REASON_MAX} characters.`;
                applicationCancelError.hidden = false;
            }
            return;
        }

        const payloadReason = `[${cancelType}] ${reason}`;

        if (applicationCancelBtn) {
            applicationCancelBtn.disabled = true;
            applicationCancelBtn.textContent = 'Cancelling...';
        }
        if (typeof showLoading === 'function') {
            showLoading('Cancelling application...');
        }

        try {
            await cancelApplication(activeViewApplicationId, payloadReason);
            closeCancelReasonModal();
            if (applicationViewModal && !applicationViewModal.hidden) {
                closeApplicationView();
            }
            await init();
        } catch (error) {
            if (applicationCancelError) {
                applicationCancelError.textContent = error.message || 'Unable to cancel application.';
                applicationCancelError.hidden = false;
            }
        } finally {
            if (applicationCancelBtn) {
                applicationCancelBtn.disabled = false;
                applicationCancelBtn.textContent = 'Confirm Cancel';
            }
            if (typeof hideLoading === 'function') {
                hideLoading();
            }
        }
    }

    async function init() {
        const [program, applications] = await Promise.all([
            scheduleProgramId ? fetchScheduleProgram() : Promise.resolve(null),
            fetchApplications(),
        ]);

        if (program) {
            renderProgramInfo(program);
        }

        renderPreviousApplications(applications);
    }

    function handleStartApplication() {
        if (!scheduleProgramId) return;
        window.location.href = `/scholarship/apply/form?schedule=${encodeURIComponent(scheduleProgramId)}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        init();

        if (startApplicationBtn) {
            startApplicationBtn.addEventListener('click', handleStartApplication);
        }

        if (applicationViewClose) {
            applicationViewClose.addEventListener('click', closeApplicationView);
        }

        if (applicationViewMaximize) {
            applicationViewMaximize.addEventListener('click', (event) => {
                event.stopPropagation();
                const isMaximized = applicationViewContainer?.classList.contains('is-fullscreen');
                setViewModalMaximized(!isMaximized);
            });
        }

        if (applicationViewModal) {
            applicationViewModal.querySelector('.sl-view-modal-overlay')?.addEventListener('click', closeApplicationView);
        }

        if (applicationCancelBtn) {
            applicationCancelBtn.addEventListener('click', handleCancelApplication);
        }

        if (applicationCancelReason) {
            applicationCancelReason.addEventListener('input', updateCancelReasonCharCount);
        }

        if (applicationCancelClose) {
            applicationCancelClose.addEventListener('click', closeCancelReasonModal);
        }

        if (applicationCancelDismissBtn) {
            applicationCancelDismissBtn.addEventListener('click', closeCancelReasonModal);
        }

        if (applicationCancelModal) {
            applicationCancelModal.querySelector('.sl-cancel-modal-overlay')?.addEventListener('click', closeCancelReasonModal);
        }
    });
})();
