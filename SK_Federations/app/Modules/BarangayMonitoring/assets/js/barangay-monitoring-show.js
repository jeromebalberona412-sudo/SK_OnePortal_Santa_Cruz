(function () {
    'use strict';

    const config = window.barangayMonitoringShowConfig || {};
    let currentSubmissionId = null;
    let revokeSubmissionId = null;
    const submissions = Array.isArray(config.abyipReports) ? config.abyipReports : [];

    function csrfToken() {
        return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function filterTableRows(tableId, matchers) {
        const table = document.getElementById(tableId);
        if (!table) return;

        table.querySelectorAll('tbody tr').forEach(function (row) {
            if (row.classList.contains('bm-empty-row-tr') || row.querySelector('.bm-empty-row')) return;
            const visible = matchers.every(function (matcher) {
                return matcher(row);
            });
            row.hidden = !visible;
        });
    }

    function initAbyipFilters() {
        const search = document.getElementById('abyipSearchInput');
        const year = document.getElementById('abyipFilterYear');
        const status = document.getElementById('abyipFilterStatus');

        function apply() {
            const term = (search?.value || '').toLowerCase();
            const yearVal = year?.value || 'all';
            const statusVal = status?.value || 'all';

            filterTableRows('abyipTable', [
                function (row) {
                    if (!term) return true;
                    return row.textContent.toLowerCase().includes(term);
                },
                function (row) {
                    return yearVal === 'all' || row.dataset.year === yearVal;
                },
                function (row) {
                    return statusVal === 'all' || row.dataset.status === statusVal;
                },
            ]);
        }

        search?.addEventListener('input', apply);
        year?.addEventListener('change', apply);
        status?.addEventListener('change', apply);
    }

    async function apiFetch(url, options) {
        const response = await fetch(url, {
            method: (options && options.method) || 'GET',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: options && options.body ? JSON.stringify(options.body) : undefined,
        });

        const payload = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(payload.message || 'Request failed.');
        }

        return payload;
    }

    function statusClass(status) {
        const normalized = String(status || 'pending').toLowerCase();
        if (normalized === 'approved') return 'status-approved';
        if (normalized === 'rejected') return 'status-rejected';
        return 'status-pending';
    }

    function statusLabel(status) {
        const normalized = String(status || 'pending').toLowerCase();
        return normalized.charAt(0).toUpperCase() + normalized.slice(1);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDateSubmitted(raw) {
        if (!raw) return '-';
        const date = new Date(raw);
        if (Number.isNaN(date.getTime())) return raw;
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatSubmittedTime(raw) {
        if (!raw) return '-';
        const date = new Date(raw);
        if (Number.isNaN(date.getTime())) return '-';
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    function populateModalFields(item) {
        document.getElementById('modalFiscalYear').textContent = item.fiscal_year ? String(item.fiscal_year) : '-';
        document.getElementById('modalDateSubmitted').textContent = item.date_submitted
            ? formatDateSubmitted(item.date_submitted)
            : (item.date_submitted_raw || '-');
        document.getElementById('modalSubmittedBy').textContent = item.submitted_by || '-';
        const roleEl = document.getElementById('modalSubmittedRole');
        if (roleEl) {
            roleEl.textContent = item.submitted_by_role || '-';
        }
        document.getElementById('modalTitle').textContent = item.title || item.name || '-';
        document.getElementById('modalSubmittedTime').textContent = item.submitted_time
            || formatSubmittedTime(item.date_submitted);

        const statusBadge = document.getElementById('modalStatus');
        statusBadge.textContent = statusLabel(item.status);
        statusBadge.className = 'status-badge ' + statusClass(item.status);

        const modalActions = document.getElementById('modalActions');
        const normalizedStatus = String(item.status || 'pending').toLowerCase();

        if (modalActions) {
            modalActions.style.display = normalizedStatus === 'pending' ? 'flex' : 'none';
        }

        const rejectionNotice = document.getElementById('modalRejectionReason');
        if (rejectionNotice) {
            const reason = String(item.rejection_reason || '').trim();
            if (String(item.status).toLowerCase() === 'rejected' && reason) {
                rejectionNotice.textContent = reason;
                rejectionNotice.style.display = 'block';
            } else {
                rejectionNotice.textContent = '';
                rejectionNotice.style.display = 'none';
            }
        }
    }

    function renderSubmissionPreview(item) {
        const preview = document.getElementById('abyipPreviewMount');
        if (!preview) return;

        if (item.document_html) {
            preview.innerHTML = '<div class="abyip-html-preview">' + item.document_html + '</div>';
            return;
        }

        const fileUrl = item.file_url || item.file;
        if ((item.has_pdf || fileUrl) && fileUrl) {
            preview.innerHTML =
                '<iframe src="' + escapeHtml(fileUrl) + '#toolbar=1&navpanes=0" class="abyip-pdf-frame" title="ABYIP PDF"></iframe>';
            return;
        }

        preview.innerHTML = '<p class="preview-empty">No preview available for this submission.</p>';
    }

    function findCachedSubmission(id) {
        return submissions.find(function (item) {
            return String(item.id) === String(id);
        }) || {};
    }

    function resetRevokeForm() {
        document.getElementById('abyipRevokeConfirm').value = '';
        document.getElementById('abyipRevokeReason').value = '';
        document.getElementById('revokeConfirmError').textContent = '';
        document.getElementById('revokeReasonError').textContent = '';
        const accidental = document.getElementById('revokeReasonAccidental');
        const other = document.getElementById('revokeReasonOther');
        if (accidental) accidental.checked = false;
        if (other) other.checked = false;
        const wrap = document.getElementById('revokeReasonFieldWrap');
        if (wrap) wrap.style.display = 'none';
    }

    function openViewModal(id) {
        if (!id) return;

        currentSubmissionId = id;
        const cached = findCachedSubmission(id);
        populateModalFields(cached);
        document.getElementById('abyipPreviewMount').innerHTML = '<p class="preview-loading">Loading document preview...</p>';
        hideRejectForm();
        document.getElementById('viewModal').classList.add('active');
        document.body.style.overflow = 'hidden';

        apiFetch((config.showUrl || '/api/barangay-abyip/__ID__').replace('__ID__', id))
            .then(function (payload) {
                const item = payload.data || {};
                populateModalFields(item);
                renderSubmissionPreview(item);
            })
            .catch(function (error) {
                document.getElementById('abyipPreviewMount').innerHTML =
                    '<p class="preview-empty">' + escapeHtml(error.message || 'Unable to load submission.') + '</p>';
                showToast(error.message || 'Unable to load submission details.', 'error');
            });
    }

    function openRevokeModal(id) {
        if (!id) return;

        revokeSubmissionId = id;
        resetRevokeForm();
        document.getElementById('revokeModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    window.closeRevokeModal = function () {
        document.getElementById('revokeModal').classList.remove('active');
        revokeSubmissionId = null;
        resetRevokeForm();
        if (!document.getElementById('viewModal').classList.contains('active')) {
            document.body.style.overflow = '';
        }
    };

    window.showRejectForm = function () {
        document.getElementById('modalActions').style.display = 'none';
        document.getElementById('rejectForm').style.display = 'block';
    };

    window.hideRejectForm = function () {
        document.getElementById('rejectForm').style.display = 'none';
        document.getElementById('abyipRejectReason').value = '';
        document.getElementById('rejectReasonError').textContent = '';
        const item = findCachedSubmission(currentSubmissionId);
        const normalizedStatus = item ? String(item.status).toLowerCase() : '';
        document.getElementById('modalActions').style.display =
            normalizedStatus === 'pending' ? 'flex' : 'none';
    };

    function bindRevokeReasonControls() {
        const accidental = document.getElementById('revokeReasonAccidental');
        const other = document.getElementById('revokeReasonOther');
        const wrap = document.getElementById('revokeReasonFieldWrap');
        const reasonInput = document.getElementById('abyipRevokeReason');

        function syncRevokeReasonField() {
            const showField = Boolean(other?.checked);
            if (wrap) {
                wrap.style.display = showField ? 'block' : 'none';
            }
            if (!showField && reasonInput) {
                reasonInput.value = '';
            }
        }

        accidental?.addEventListener('change', syncRevokeReasonField);
        other?.addEventListener('change', syncRevokeReasonField);
    }

    window.submitRevocation = async function () {
        if (!revokeSubmissionId) return;

        const confirmInput = document.getElementById('abyipRevokeConfirm');
        const confirmError = document.getElementById('revokeConfirmError');
        const reasonError = document.getElementById('revokeReasonError');
        const accidental = document.getElementById('revokeReasonAccidental');
        const other = document.getElementById('revokeReasonOther');
        const reasonInput = document.getElementById('abyipRevokeReason');

        confirmError.textContent = '';
        reasonError.textContent = '';

        if ((confirmInput?.value || '').trim() !== 'Confirm to revoked') {
            confirmError.textContent = 'Type Confirm to revoked to continue.';
            return;
        }

        if (!accidental?.checked && !other?.checked) {
            reasonError.textContent = 'Select a revoke reason.';
            return;
        }

        let reason = '';
        if (accidental?.checked) {
            reason = 'Accidentally approved';
        }
        if (other?.checked) {
            const customReason = (reasonInput?.value || '').trim();
            if (!customReason) {
                reasonError.textContent = 'Please provide a revoke reason.';
                return;
            }
            reason = reason ? reason + ': ' + customReason : customReason;
        }

        reason = reason.slice(0, 100);

        const revokeBtn = document.querySelector('.revoke-submit-btn');
        if (revokeBtn) revokeBtn.disabled = true;

        try {
            await apiFetch((config.revokeUrl || '/api/barangay-abyip/__ID__/revoke').replace('__ID__', revokeSubmissionId), {
                method: 'POST',
                body: { reason: reason },
            });
            showToast('ABYIP approval revoked. Status is now Pending.', 'success');
            closeRevokeModal();
            window.location.reload();
        } catch (error) {
            showToast(error.message || 'Revocation failed.', 'error');
        } finally {
            if (revokeBtn) revokeBtn.disabled = false;
        }
    };

    window.submitApproval = async function () {
        if (!currentSubmissionId) return;

        const approveBtn = document.querySelector('#modalActions .approve-btn');
        if (approveBtn) approveBtn.disabled = true;

        try {
            await apiFetch((config.approveUrl || '/api/barangay-abyip/__ID__/approve').replace('__ID__', currentSubmissionId), {
                method: 'POST',
                body: {},
            });
            showToast('Submission successfully approved!', 'success');
            closeViewModal();
            window.location.reload();
        } catch (error) {
            showToast(error.message || 'Approval failed.', 'error');
        } finally {
            if (approveBtn) approveBtn.disabled = false;
        }
    };

    window.submitRejection = async function () {
        if (!currentSubmissionId) return;

        const reason = document.getElementById('abyipRejectReason').value.trim();
        const reasonError = document.getElementById('rejectReasonError');
        reasonError.textContent = '';

        if (!reason) {
            reasonError.textContent = 'Please provide a rejection reason';
            return;
        }

        const rejectBtn = document.querySelector('.reject-submit-btn');
        if (rejectBtn) rejectBtn.disabled = true;

        try {
            await apiFetch((config.rejectUrl || '/api/barangay-abyip/__ID__/reject').replace('__ID__', currentSubmissionId), {
                method: 'POST',
                body: { reason: reason },
            });
            showToast('Submission successfully rejected!', 'error');
            closeViewModal();
            window.location.reload();
        } catch (error) {
            showToast(error.message || 'Rejection failed.', 'error');
        } finally {
            if (rejectBtn) rejectBtn.disabled = false;
        }
    };

    window.closeViewModal = function () {
        document.getElementById('viewModal').classList.remove('active', 'fullscreen');
        if (!document.getElementById('revokeModal').classList.contains('active')) {
            document.body.style.overflow = '';
        }
        hideRejectForm();
        currentSubmissionId = null;
        document.getElementById('abyipPreviewMount').innerHTML = '';
    };

    window.toggleFullscreen = function () {
        document.getElementById('viewModal').classList.toggle('fullscreen');
    };

    window.showToast = function (message, type) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.querySelector('.toast-message').textContent = message;
        toast.className = 'toast show ' + (type || 'success');
        setTimeout(function () { toast.classList.remove('show'); }, 3000);
    };

    function initAbyipViewButtons() {
        document.querySelectorAll('[data-abyip-view]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openViewModal(btn.getAttribute('data-abyip-view'));
            });
        });

        document.querySelectorAll('[data-abyip-revoke]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openRevokeModal(btn.getAttribute('data-abyip-revoke'));
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAbyipFilters();
        initAbyipViewButtons();
        bindRevokeReasonControls();
    });
})();
