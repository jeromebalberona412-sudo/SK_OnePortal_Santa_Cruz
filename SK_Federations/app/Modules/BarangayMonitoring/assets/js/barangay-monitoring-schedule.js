(function () {
    'use strict';

    const config = window.barangayMonitoringScheduleConfig || {};

    function csrfToken() {
        return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function closeModals() {
        document.querySelectorAll('.bm-modal').forEach(function (modal) {
            modal.hidden = true;
        });
    }

    async function apiRequest(url, method, body) {
        const response = await fetch(url, {
            method: method,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : undefined,
        });

        const payload = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            throw new Error(payload.message || 'Request failed.');
        }

        return payload;
    }

    function openScheduleModal(mode) {
        const modal = document.getElementById('scheduleModal');
        const title = document.getElementById('scheduleModalTitle');
        const reasonWrap = document.getElementById('scheduleReasonWrap');
        const schedule = config.currentSchedule || {};

        document.getElementById('scheduleId').value = mode === 'edit' ? (schedule.id || '') : '';
        document.getElementById('scheduleFiscalYear').value = mode === 'edit' ? (schedule.fiscal_year || new Date().getFullYear()) : new Date().getFullYear();
        document.getElementById('scheduleTitle').value = mode === 'edit' ? (schedule.title || 'ABYIP Submission') : 'ABYIP Submission';
        document.getElementById('scheduleDateStart').value = mode === 'edit' ? (schedule.date_start_raw || '') : '';
        document.getElementById('scheduleDeadline').value = mode === 'edit' ? (schedule.deadline_raw || '') : '';
        document.getElementById('scheduleAllowLateExtension').checked = !!schedule.allow_late_extension;
        document.getElementById('scheduleReason').value = '';

        if (title) title.textContent = mode === 'edit' ? 'Edit ABYIP Schedule' : 'Create ABYIP Schedule';
        if (reasonWrap) reasonWrap.hidden = mode !== 'edit';
        if (modal) modal.hidden = false;
    }

    function openExtendModal() {
        const schedule = config.currentSchedule || {};
        const modal = document.getElementById('extendModal');
        document.getElementById('extendScheduleId').value = schedule.id || '';
        document.getElementById('extendNewDeadline').value = '';
        document.getElementById('extendReason').value = '';
        if (modal) modal.hidden = false;
    }

    async function saveSchedule() {
        const id = document.getElementById('scheduleId').value;
        const payload = {
            fiscal_year: parseInt(document.getElementById('scheduleFiscalYear').value, 10),
            title: document.getElementById('scheduleTitle').value.trim(),
            date_start: document.getElementById('scheduleDateStart').value,
            deadline: document.getElementById('scheduleDeadline').value,
            allow_late_extension: document.getElementById('scheduleAllowLateExtension').checked,
            reason: document.getElementById('scheduleReason').value.trim() || null,
        };

        if (id) {
            await apiRequest(config.updateUrl + '/' + id, 'PUT', payload);
        } else {
            await apiRequest(config.storeUrl, 'POST', payload);
        }

        window.location.reload();
    }

    async function extendSchedule() {
        const id = document.getElementById('extendScheduleId').value;
        const payload = {
            new_deadline: document.getElementById('extendNewDeadline').value,
            reason: document.getElementById('extendReason').value.trim() || null,
        };

        await apiRequest(config.updateUrl + '/' + id + '/extend', 'POST', payload);
        window.location.reload();
    }

    async function cancelSchedule() {
        const schedule = config.currentSchedule || {};
        if (!schedule.id) return;

        const reason = window.prompt('Reason for cancelling this schedule (optional):');
        if (reason === null) return;

        await apiRequest(config.updateUrl + '/' + schedule.id + '/cancel', 'POST', { reason: reason || null });
        window.location.reload();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnCreateSchedule')?.addEventListener('click', function () {
            openScheduleModal('create');
        });

        document.getElementById('btnEditSchedule')?.addEventListener('click', function () {
            openScheduleModal('edit');
        });

        document.getElementById('btnExtendSchedule')?.addEventListener('click', openExtendModal);
        document.getElementById('btnCancelSchedule')?.addEventListener('click', function () {
            if (window.confirm('Cancel the current ABYIP submission schedule?')) {
                cancelSchedule().catch(function (error) {
                    alert(error.message);
                });
            }
        });

        document.getElementById('scheduleSaveBtn')?.addEventListener('click', function () {
            saveSchedule().catch(function (error) {
                alert(error.message);
            });
        });

        document.getElementById('extendSaveBtn')?.addEventListener('click', function () {
            extendSchedule().catch(function (error) {
                alert(error.message);
            });
        });

        document.querySelectorAll('[data-schedule-close]').forEach(function (btn) {
            btn.addEventListener('click', closeModals);
        });
    });
})();
