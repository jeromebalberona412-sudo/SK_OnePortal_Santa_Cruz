const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function apiFetch(url, options = {}) {
    const { headers: extraHeaders, body, ...rest } = options;
    const headers = {
        'X-CSRF-TOKEN': csrfToken(),
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extraHeaders,
    };

    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(url, {
        ...rest,
        headers,
        body: body && !(body instanceof FormData) ? JSON.stringify(body) : body,
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        const message = data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed.';
        throw new Error(message);
    }

    return data;
}

document.addEventListener('DOMContentLoaded', () => {
    initializeProgramsUI();
});

function showProgramToast(message, type) {
    const existing = document.querySelector('.prog-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'prog-toast prog-toast-show' + (type === 'error' ? ' prog-toast-error' : '');
    toast.innerHTML = '<span>' + (type === 'error' ? '✕' : '✓') + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('prog-toast-show');
        toast.classList.add('prog-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function resolveProgramStatus(startDate, endDate) {
    if (!startDate || !endDate) {
        return 'planned';
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const start = new Date(`${startDate}T00:00:00`);
    const end = new Date(`${endDate}T00:00:00`);

    if (today < start) {
        return 'planned';
    }

    if (today > end) {
        return 'completed';
    }

    return 'ongoing';
}

function formatStatusLabel(status) {
    if (!status) return 'Planned';
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function initializeProgramsUI() {
    const tbody = document.getElementById('programTableBody');
    const searchInput = document.getElementById('programSearch');
    const committeeFilter = document.getElementById('programCommitteeFilter');
    const statusFilter = document.getElementById('programStatusFilter');

    const viewModal = document.getElementById('programViewModal');
    const viewProgramType = document.getElementById('viewProgramType');
    const viewProgramName = document.getElementById('viewProgramName');
    const viewProgramTitle = document.getElementById('viewProgramTitle');
    const viewProgramDuration = document.getElementById('viewProgramDuration');
    const viewProgramStatus = document.getElementById('viewProgramStatus');

    const editDurationModal  = document.getElementById('editDurationModal');
    const editDurationClose  = document.getElementById('editDurationClose');
    const editDurationCancel = document.getElementById('editDurationCancel');
    const editDurationSave   = document.getElementById('editDurationSave');
    const editDurationIndex  = document.getElementById('editDurationIndex');
    const editStartDate      = document.getElementById('editStartDate');
    const editEndDate        = document.getElementById('editEndDate');
    const editDurationStatusPill = document.getElementById('editDurationStatusPill');

    // Modal maximize/minimize (restore) controls
    function resetModalMaximize(backdropEl) {
        if (!backdropEl) return;
        backdropEl.classList.remove('modal-maximized');
        const box = backdropEl.querySelector('.modal-box');
        if (box) box.classList.remove('modal-maximized');
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    function wireModalToggle(backdropEl) {
        if (!backdropEl) return;
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        const box = backdropEl.querySelector('.modal-box');
        if (!toggleBtn || !box) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willMaximize = !box.classList.contains('modal-maximized');
            backdropEl.classList.toggle('modal-maximized', willMaximize);
            box.classList.toggle('modal-maximized', willMaximize);
            toggleBtn.textContent = willMaximize ? '⧉' : '□';
        });
    }

    if (!tbody) return;

    let programs = [];
    let abyipGate = window.programsAbyipGate || null;

    let currentQuery = '';
    let currentCommittee = '';
    let currentStatus = '';

    function formatDuration(start, end) {
        const opts = { month: 'short', day: '2-digit', year: 'numeric' };
        const s = new Date(start).toLocaleDateString(undefined, opts);
        const e = new Date(end).toLocaleDateString(undefined, opts);
        return `${s} – ${e}`;
    }

    function updateEditDurationStatusPreview(start, end) {
        if (!editDurationStatusPill) return;

        const status = resolveProgramStatus(start, end);
        editDurationStatusPill.className = `status-pill ${status}`;
        editDurationStatusPill.textContent = formatStatusLabel(status);
    }

    function render() {
        tbody.innerHTML = '';

        const filtered = programs.filter((p) => {
            const matchesSearch =
                !currentQuery ||
                p.title.toLowerCase().includes(currentQuery) ||
                p.description.toLowerCase().includes(currentQuery) ||
                p.committee.toLowerCase().includes(currentQuery);

            const matchesCommittee =
                !currentCommittee ||
                p.committee === currentCommittee;

            const matchesStatus =
                !currentStatus || p.status === currentStatus;

            return matchesSearch && matchesCommittee && matchesStatus;
        });

        if (filtered.length === 0) {
            const tr = document.createElement('tr');
            if (window.SkAbyipNotice?.isPending(abyipGate) && programs.length === 0) {
                tr.innerHTML = window.SkAbyipNotice.renderEmptyRow(6, abyipGate);
            } else {
                const td = document.createElement('td');
                td.colSpan = 6;
                td.textContent = 'No programs found matching the current filters.';
                td.style.textAlign = 'center';
                td.style.fontSize = '13px';
                td.style.color = '#6b7280';
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        } else {
            filtered.forEach((p) => {
                const sourceIndex = programs.indexOf(p);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="program-title-cell">${p.title}</td>
                    <td class="program-desc-cell">${p.description}</td>
                    <td>${p.committee}</td>
                    <td class="program-duration">${formatDuration(p.startDate, p.endDate)}</td>
                    <td>
                        <span class="status-pill ${p.status}">
                            ${formatStatusLabel(p.status)}
                        </span>
                    </td>
                    <td>
                        <div class="program-actions">
                            <button type="button" class="program-action-btn" data-action="view" data-index="${sourceIndex}">
                                View
                            </button>
                            <button type="button" class="program-action-btn edit-btn" data-action="edit" data-index="${sourceIndex}">
                                Edit
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updateSummary(programs);
    }

    function populateCommitteeFilter() {
        if (!committeeFilter) return;

        const uniqueCommittees = [...new Set(programs.map((p) => p.committee))].sort();
        committeeFilter.innerHTML = '<option value="">All Committees</option>';
        uniqueCommittees.forEach((name) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            committeeFilter.appendChild(option);
        });
    }

    async function loadPrograms() {
        const response = await apiFetch('/api/programs');
        programs = response.data?.programs || [];
        if (response.abyip_gate) {
            abyipGate = response.abyip_gate;
        }
        populateCommitteeFilter();
        render();
    }

    function updateSummary(list) {
        const total = list.length;
        const planned = list.filter((p) => p.status === 'planned').length;
        const ongoing = list.filter((p) => p.status === 'ongoing').length;
        const completed = list.filter((p) => p.status === 'completed').length;

        const statTotal = document.getElementById('progStatTotal');
        const statPlanned = document.getElementById('progStatPlanned');
        const statOngoing = document.getElementById('progStatOngoing');
        const statCompleted = document.getElementById('progStatCompleted');
        if (statTotal) statTotal.textContent = total;
        if (statPlanned) statPlanned.textContent = planned;
        if (statOngoing) statOngoing.textContent = ongoing;
        if (statCompleted) statCompleted.textContent = completed;
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentQuery = searchInput.value.trim().toLowerCase();
            render();
        });
    }

    if (committeeFilter) {
        committeeFilter.addEventListener('change', () => {
            currentCommittee = committeeFilter.value;
            render();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            currentStatus = statusFilter.value;
            render();
        });
    }

    // ── Table click handler ─────────────────────────────────────────────────
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const action = target.getAttribute('data-action');
            const index = Number(target.getAttribute('data-index'));
            if (Number.isNaN(index) || !programs[index]) return;

            const program = programs[index];

            if (action === 'view') {
                if (viewProgramType) viewProgramType.value = program.committee;
                if (viewProgramName) viewProgramName.value = program.description || '-';
                if (viewProgramTitle) viewProgramTitle.value = program.title;
                if (viewProgramDuration) viewProgramDuration.value = formatDuration(program.startDate, program.endDate);
                if (viewProgramStatus) {
                    viewProgramStatus.value = formatStatusLabel(program.status);
                }
                resetModalMaximize(viewModal);
                if (viewModal) viewModal.style.display = 'flex';

            } else if (action === 'edit') {
                if (editDurationIndex) editDurationIndex.value = index;
                if (editStartDate) editStartDate.value = program.startDate;
                if (editEndDate) editEndDate.value = program.endDate;
                updateEditDurationStatusPreview(program.startDate, program.endDate);
                clearDurationErrors();
                if (editDurationModal) editDurationModal.style.display = 'flex';
            }
        });
    }

    // ── View modal close ────────────────────────────────────────────────────
    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal || e.target.hasAttribute('data-view-close')) {
                resetModalMaximize(viewModal);
                viewModal.style.display = 'none';
            }
        });
    }

    // ── Edit Duration modal ─────────────────────────────────────────────────
    function closeEditDurationModal() {
        if (editDurationModal) editDurationModal.style.display = 'none';
    }

    if (editDurationClose)  editDurationClose.addEventListener('click', closeEditDurationModal);
    if (editDurationCancel) editDurationCancel.addEventListener('click', closeEditDurationModal);

    if (editDurationModal) {
        editDurationModal.addEventListener('click', (e) => {
            if (e.target === editDurationModal) closeEditDurationModal();
        });
    }

    function clearDurationErrors() {
        const startDateError = document.getElementById('editStartDateError');
        const endDateError = document.getElementById('editEndDateError');
        if (startDateError) {
            startDateError.textContent = '';
            startDateError.style.display = 'none';
        }
        if (endDateError) {
            endDateError.textContent = '';
            endDateError.style.display = 'none';
        }
    }

    function validateDurationDates(start, end) {
        const startDateError = document.getElementById('editStartDateError');
        const endDateError = document.getElementById('editEndDateError');
        clearDurationErrors();

        if (!start || !end) {
            showProgramToast('Both start and end dates are required.', 'error');
            return false;
        }

        if (end < start) {
            if (endDateError) {
                endDateError.textContent = 'End date must be on or after start date.';
                endDateError.style.display = 'block';
            }
            return false;
        }

        return true;
    }

    if (editStartDate) {
        editStartDate.addEventListener('input', () => {
            validateDurationDates(editStartDate.value, editEndDate?.value || '');
            updateEditDurationStatusPreview(editStartDate.value, editEndDate?.value || '');
        });
    }

    if (editEndDate) {
        editEndDate.addEventListener('input', () => {
            validateDurationDates(editStartDate?.value || '', editEndDate.value);
            updateEditDurationStatusPreview(editStartDate?.value || '', editEndDate.value);
        });
    }

    if (editDurationSave) {
        editDurationSave.addEventListener('click', async () => {
            const idx   = parseInt(editDurationIndex.value, 10);
            const start = editStartDate.value;
            const end   = editEndDate.value;
            const program = programs[idx];

            if (!validateDurationDates(start, end)) {
                return;
            }

            if (!program) {
                showProgramToast('Program not found.', 'error');
                return;
            }

            editDurationSave.disabled = true;

            try {
                const response = await apiFetch(`/api/programs/${program.id}/duration`, {
                    method: 'PUT',
                    body: {
                        start_date: start,
                        end_date: end,
                    },
                });

                const status = response.data?.status || resolveProgramStatus(start, end);
                programs[idx].startDate = start;
                programs[idx].endDate = end;
                programs[idx].status = status;
                closeEditDurationModal();
                clearDurationErrors();
                render();
                showProgramToast(`Duration updated. Status: ${formatStatusLabel(status)}.`);
            } catch (error) {
                showProgramToast(error.message || 'Failed to update duration.', 'error');
            } finally {
                editDurationSave.disabled = false;
            }
        });
    }

    loadPrograms().catch((error) => {
        showProgramToast(error.message || 'Failed to load programs.', 'error');
        render();
    });

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);
}

