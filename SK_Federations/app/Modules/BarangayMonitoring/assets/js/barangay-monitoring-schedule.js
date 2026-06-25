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

    function formatDateInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function defaultCreateDates() {
        const start = new Date();
        const end = new Date();
        end.setDate(end.getDate() + 30);
        return {
            start: formatDateInput(start),
            end: formatDateInput(end),
        };
    }

    function extractErrorMessage(payload) {
        if (payload?.message) return payload.message;
        if (payload?.errors) {
            const flat = Object.values(payload.errors).flat().filter(Boolean);
            if (flat.length) return flat.join(' ');
        }
        return 'Request failed.';
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
            throw new Error(extractErrorMessage(payload));
        }

        return payload;
    }

    function openScheduleModal(mode) {
        const modal = document.getElementById('scheduleModal');
        const title = document.getElementById('scheduleModalTitle');
        const schedule = config.currentSchedule || {};
        const defaults = defaultCreateDates();

        document.getElementById('scheduleId').value = mode === 'edit' ? (schedule.id || '') : '';
        document.getElementById('scheduleFiscalYear').value = mode === 'edit'
            ? (schedule.fiscal_year || new Date().getFullYear())
            : new Date().getFullYear();
        document.getElementById('scheduleTitle').value = mode === 'edit'
            ? (schedule.title || 'ABYIP Submission')
            : 'ABYIP Submission';
        document.getElementById('scheduleDateStart').value = mode === 'edit'
            ? (schedule.date_start_raw || '')
            : defaults.start;
        document.getElementById('scheduleDeadline').value = mode === 'edit'
            ? (schedule.deadline_raw || '')
            : defaults.end;

        if (title) title.textContent = mode === 'edit' ? 'Edit ABYIP Schedule' : 'Create ABYIP Schedule';
        if (modal) modal.hidden = false;
    }

    function openExtendModal() {
        const schedule = config.currentSchedule || {};
        const modal = document.getElementById('extendModal');
        document.getElementById('extendScheduleId').value = schedule.id || '';
        document.getElementById('extendNewDeadline').value = '';
        if (modal) modal.hidden = false;
    }

    async function saveSchedule() {
        const saveBtn = document.getElementById('scheduleSaveBtn');
        const id = document.getElementById('scheduleId').value;
        const fiscalYear = parseInt(document.getElementById('scheduleFiscalYear').value, 10);
        const title = document.getElementById('scheduleTitle').value.trim();
        const dateStart = document.getElementById('scheduleDateStart').value;
        const deadline = document.getElementById('scheduleDeadline').value;

        if (!title) throw new Error('Title is required.');
        if (!dateStart || !deadline) throw new Error('Start date and deadline are required.');
        if (!Number.isFinite(fiscalYear)) throw new Error('Fiscal year is required.');

        const payload = {
            fiscal_year: fiscalYear,
            title: title,
            date_start: dateStart,
            deadline: deadline,
        };

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }

        try {
            if (id) {
                await apiRequest(config.updateUrl + '/' + id, 'PUT', payload);
            } else {
                await apiRequest(config.storeUrl, 'POST', payload);
            }
            window.location.reload();
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Schedule';
            }
        }
    }

    async function extendSchedule() {
        const id = document.getElementById('extendScheduleId').value;
        const newDeadline = document.getElementById('extendNewDeadline').value;

        if (!newDeadline) throw new Error('New deadline is required.');

        await apiRequest(config.updateUrl + '/' + id + '/extend', 'POST', {
            new_deadline: newDeadline,
        });
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
