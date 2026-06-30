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

    function currentYearBounds() {
        const now = new Date();
        const year = now.getFullYear();
        return {
            year: year,
            calendarYear: year + 1,
            today: formatDateInput(now),
            yearEnd: `${year}-12-31`,
        };
    }

    function applyScheduleDateConstraints(mode) {
        const bounds = currentYearBounds();
        const isCreate = mode !== 'edit';
        const startInput = document.getElementById('scheduleDateStart');
        const deadlineInput = document.getElementById('scheduleDeadline');

        if (startInput) {
            startInput.min = bounds.today;
            startInput.max = isCreate ? bounds.today : bounds.yearEnd;
            startInput.readOnly = isCreate;
            if (isCreate) {
                startInput.value = bounds.today;
            }
        }

        if (deadlineInput) {
            const startValue = startInput?.value || bounds.today;
            deadlineInput.min = startValue > bounds.today ? startValue : bounds.today;
            deadlineInput.max = bounds.yearEnd;
        }

        return bounds;
    }

    function clearScheduleFieldErrors() {
        ['scheduleDateStartError', 'scheduleDeadlineError'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = '';
                el.hidden = true;
            }
        });
    }

    function showScheduleFieldError(fieldId, message) {
        const el = document.getElementById(fieldId);
        if (!el) return;
        el.textContent = message;
        el.hidden = false;
    }

    function validateScheduleForm(mode) {
        clearScheduleFieldErrors();
        const bounds = currentYearBounds();
        const isCreate = mode !== 'edit';
        const title = document.getElementById('scheduleTitle')?.value.trim() || '';
        const dateStart = document.getElementById('scheduleDateStart')?.value || '';
        const deadline = document.getElementById('scheduleDeadline')?.value || '';
        let valid = true;

        if (!title) {
            alert('Title is required.');
            return false;
        }

        if (!dateStart) {
            showScheduleFieldError('scheduleDateStartError', 'Start date is required.');
            valid = false;
        }

        if (!deadline) {
            showScheduleFieldError('scheduleDeadlineError', 'Deadline is required.');
            valid = false;
        }

        if (!valid) {
            return false;
        }

        if (dateStart < bounds.today || dateStart > bounds.yearEnd) {
            showScheduleFieldError(
                'scheduleDateStartError',
                'Start date must be between today and December 31, ' + bounds.year + '.'
            );
            valid = false;
        } else if (isCreate && dateStart !== bounds.today) {
            showScheduleFieldError('scheduleDateStartError', 'Start date must be today when creating a schedule.');
            valid = false;
        }

        if (deadline < bounds.today || deadline > bounds.yearEnd) {
            showScheduleFieldError(
                'scheduleDeadlineError',
                'Deadline must be between today and December 31, ' + bounds.year + '.'
            );
            valid = false;
        } else if (deadline < dateStart) {
            showScheduleFieldError('scheduleDeadlineError', 'Deadline must be on or after the start date.');
            valid = false;
        }

        return valid;
    }

    function updateScheduleFieldHints(mode) {
        const bounds = currentYearBounds();
        const isCreate = mode !== 'edit';
        const startHint = document.getElementById('scheduleDateStartHint');
        const deadlineHint = document.getElementById('scheduleDeadlineHint');

        if (startHint) {
            startHint.textContent = isCreate
                ? 'Must be today (' + bounds.today + ').'
                : 'Must be today through December 31, ' + bounds.year + '.';
        }

        if (deadlineHint) {
            deadlineHint.textContent = 'Must be on or after the start date and on or before December 31, ' + bounds.year + '.';
        }
    }

    function defaultCreateDates(bounds) {
        return {
            start: bounds.today,
            end: bounds.yearEnd,
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
        const bounds = applyScheduleDateConstraints(mode);
        const defaults = defaultCreateDates(bounds);
        const calendarYearInput = document.getElementById('scheduleFiscalYear');

        document.getElementById('scheduleId').value = mode === 'edit' ? (schedule.id || '') : '';
        if (calendarYearInput) {
            calendarYearInput.value = mode === 'edit'
                ? (schedule.fiscal_year || bounds.calendarYear)
                : bounds.calendarYear;
            calendarYearInput.readOnly = true;
        }
        document.getElementById('scheduleTitle').value = mode === 'edit'
            ? (schedule.title || 'ABYIP Submission')
            : 'ABYIP Submission';
        document.getElementById('scheduleDateStart').value = mode === 'edit'
            ? (schedule.date_start_raw || bounds.today)
            : defaults.start;
        document.getElementById('scheduleDeadline').value = mode === 'edit'
            ? (schedule.deadline_raw || '')
            : defaults.end;

        if (title) title.textContent = mode === 'edit' ? 'Edit ABYIP Schedule' : 'Create ABYIP Schedule';
        clearScheduleFieldErrors();
        updateScheduleFieldHints(mode);
        if (modal) modal.hidden = false;
    }

    function openExtendModal() {
        const schedule = config.currentSchedule || {};
        const modal = document.getElementById('extendModal');
        const bounds = currentYearBounds();
        const extendInput = document.getElementById('extendNewDeadline');
        document.getElementById('extendScheduleId').value = schedule.id || '';
        if (extendInput) {
            extendInput.min = bounds.today;
            extendInput.max = bounds.yearEnd;
            extendInput.value = '';
        }
        if (modal) modal.hidden = false;
    }

    async function saveSchedule() {
        const saveBtn = document.getElementById('scheduleSaveBtn');
        const id = document.getElementById('scheduleId').value;
        const mode = id ? 'edit' : 'create';

        if (!validateScheduleForm(mode)) {
            return;
        }

        const bounds = currentYearBounds();
        const calendarYear = bounds.calendarYear;
        const title = document.getElementById('scheduleTitle').value.trim();
        const dateStart = document.getElementById('scheduleDateStart').value;
        const deadline = document.getElementById('scheduleDeadline').value;

        if (!title) throw new Error('Title is required.');
        if (!dateStart || !deadline) throw new Error('Start date and deadline are required.');

        const payload = {
            fiscal_year: calendarYear,
            title: title,
            date_start: dateStart,
            deadline: deadline,
        };

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }

        if (typeof window.showLoading === 'function') {
            window.showLoading('Saving ABYIP Schedule', 'Please wait...');
        }

        try {
            if (id) {
                await apiRequest(config.updateUrl + '/' + id, 'PUT', payload);
            } else {
                await apiRequest(config.storeUrl, 'POST', payload);
            }
            window.location.reload();
        } finally {
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
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

        if (typeof window.showLoading === 'function') {
            window.showLoading('Extending Deadline', 'Please wait...');
        }

        try {
            await apiRequest(config.updateUrl + '/' + id + '/extend', 'POST', {
                new_deadline: newDeadline,
            });
            window.location.reload();
        } finally {
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
        }
    }

    async function cancelSchedule() {
        const schedule = config.currentSchedule || {};
        if (!schedule.id) return;

        const reason = window.prompt('Reason for cancelling this schedule (optional):');
        if (reason === null) return;

        if (typeof window.showLoading === 'function') {
            window.showLoading('Cancelling Schedule', 'Please wait...');
        }

        try {
            await apiRequest(config.updateUrl + '/' + schedule.id + '/cancel', 'POST', { reason: reason || null });
            window.location.reload();
        } finally {
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnCreateSchedule')?.addEventListener('click', function () {
            openScheduleModal('create');
        });

        document.getElementById('btnEditSchedule')?.addEventListener('click', function () {
            openScheduleModal('edit');
        });

        document.getElementById('scheduleDateStart')?.addEventListener('change', function () {
            const bounds = currentYearBounds();
            const deadlineInput = document.getElementById('scheduleDeadline');
            if (!deadlineInput) return;
            const startValue = this.value || bounds.today;
            deadlineInput.min = startValue > bounds.today ? startValue : bounds.today;
            deadlineInput.max = bounds.yearEnd;
            clearScheduleFieldErrors();
        });

        document.getElementById('scheduleDeadline')?.addEventListener('change', function () {
            clearScheduleFieldErrors();
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
