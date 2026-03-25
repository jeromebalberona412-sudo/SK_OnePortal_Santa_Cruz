document.addEventListener('DOMContentLoaded', () => {
    initializeProgramsUI();
});

function initializeProgramsUI() {
    const tbody = document.getElementById('programTableBody');
    const searchInput = document.getElementById('programSearch');
    const committeeFilter = document.getElementById('programCommitteeFilter');
    const statusFilter = document.getElementById('programStatusFilter');

    const addBtn = document.getElementById('addProgramBtn');
    const modal = document.getElementById('programModal');
    const titleInput = document.getElementById('programTitleInput');
    const programNameInput = document.getElementById('programNameInput');
    const committeeInput = document.getElementById('programCommitteeInput');
    const budgetInput = document.getElementById('programBudgetInput');
    const startInput = document.getElementById('programStartInput');
    const endInput = document.getElementById('programEndInput');
    const statusInput = document.getElementById('programStatusInput');
    const saveBtn = document.getElementById('programSaveBtn');
    const successModal = document.getElementById('programSuccessModal');
    const successMessage = document.getElementById('programSuccessMessage');
    const viewModal = document.getElementById('programViewModal');
    const viewProgramType = document.getElementById('viewProgramType');
    const viewProgramName = document.getElementById('viewProgramName');
    const viewProgramTitle = document.getElementById('viewProgramTitle');
    const viewProgramBudget = document.getElementById('viewProgramBudget');
    const viewProgramDuration = document.getElementById('viewProgramDuration');
    const viewProgramStatus = document.getElementById('viewProgramStatus');

    const summaryTotal = document.getElementById('summaryTotalPrograms');
    const summaryPlanned = document.getElementById('summaryPlanned');
    const summaryOngoing = document.getElementById('summaryOngoing');
    const summaryCompleted = document.getElementById('summaryCompleted');

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

    // Start empty; programs appear only after "Add Program"
    const programs = [];
    let editingIndex = -1;

    let currentQuery = '';
    let currentCommittee = '';
    let currentStatus = '';

    function formatBudget(value) {
        return '₱ ' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDuration(start, end) {
        const opts = { month: 'short', day: '2-digit', year: 'numeric' };
        const s = new Date(start).toLocaleDateString(undefined, opts);
        const e = new Date(end).toLocaleDateString(undefined, opts);
        return `${s} – ${e}`;
    }

    function render() {
        tbody.innerHTML = '';

        const filtered = programs.filter((p) => {
            const matchesSearch =
                !currentQuery ||
                p.title.toLowerCase().includes(currentQuery) ||
                p.committee.toLowerCase().includes(currentQuery);

            const matchesCommittee =
                !currentCommittee ||
                p.committee.toLowerCase().includes(currentCommittee);

            const matchesStatus =
                !currentStatus || p.status === currentStatus;

            return matchesSearch && matchesCommittee && matchesStatus;
        });

        if (filtered.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 6;
            td.textContent = 'No programs added yet. Use "+ Add Program" to register your first program (UI only).';
            td.style.textAlign = 'center';
            td.style.fontSize = '13px';
            td.style.color = '#6b7280';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            filtered.forEach((p) => {
                const sourceIndex = programs.indexOf(p);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="program-title-cell">${p.title}</td>
                    <td>${p.committee}</td>
                    <td class="program-budget">${formatBudget(p.budget)}</td>
                    <td class="program-duration">${formatDuration(p.startDate, p.endDate)}</td>
                    <td>
                        <span class="status-pill ${p.status}">
                            ${p.status.charAt(0).toUpperCase() + p.status.slice(1)}
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

    function updateSummary(list) {
        if (!summaryTotal) return;

        const total = list.length;
        const planned = list.filter((p) => p.status === 'planned').length;
        const ongoing = list.filter((p) => p.status === 'ongoing').length;
        const completed = list.filter((p) => p.status === 'completed').length;

        summaryTotal.textContent = total;
        if (summaryPlanned) summaryPlanned.textContent = planned;
        if (summaryOngoing) summaryOngoing.textContent = ongoing;
        if (summaryCompleted) summaryCompleted.textContent = completed;
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

    // Modal helpers
    function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        resetModalMaximize(modal);
        editingIndex = -1;
        if (saveBtn) saveBtn.textContent = 'Save';
        if (titleInput) titleInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        resetModalMaximize(modal);
        if (titleInput) titleInput.value = '';
        if (programNameInput) programNameInput.value = '';
        if (committeeInput) committeeInput.value = '';
        if (budgetInput) budgetInput.value = '';
        if (startInput) startInput.value = '';
        if (endInput) endInput.value = '';
        if (statusInput) statusInput.value = 'planned';
    }

    function openSuccessModal(message) {
        if (!successModal) return;
        if (successMessage) {
            successMessage.textContent = message || 'Add successful.';
        }
        successModal.style.display = 'flex';
    }

    function closeSuccessModal() {
        if (!successModal) return;
        successModal.style.display = 'none';
    }

    if (addBtn) {
        addBtn.addEventListener('click', openModal);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close') || e.target.hasAttribute('data-modal-cancel')) {
                closeModal();
            }
        });
    }

    if (budgetInput) {
        budgetInput.addEventListener('input', () => {
            const digitsOnly = budgetInput.value.replace(/[^\d]/g, '');
            if (!digitsOnly) {
                budgetInput.value = '';
                return;
            }
            budgetInput.value = Number(digitsOnly).toLocaleString('en-PH');
        });
    }

    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const action = target.getAttribute('data-action');
            if (action !== 'view' && action !== 'edit') return;
            const index = Number(target.getAttribute('data-index'));
            if (Number.isNaN(index) || !programs[index]) return;

            const program = programs[index];
            if (action === 'edit') {
                editingIndex = index;
                resetModalMaximize(modal);
                if (committeeInput) committeeInput.value = program.committee;
                if (programNameInput) programNameInput.value = program.programName || '';
                if (titleInput) titleInput.value = program.title;
                if (budgetInput) budgetInput.value = Number(program.budget || 0).toLocaleString('en-PH');
                if (startInput) startInput.value = program.startDate;
                if (endInput) endInput.value = program.endDate;
                if (statusInput) statusInput.value = program.status;
                if (saveBtn) saveBtn.textContent = 'Update';
                if (modal) modal.style.display = 'flex';
                return;
            }

            if (viewProgramType) viewProgramType.value = program.committee;
            if (viewProgramName) viewProgramName.value = program.programName || '-';
            if (viewProgramTitle) viewProgramTitle.value = program.title;
            if (viewProgramBudget) viewProgramBudget.value = formatBudget(program.budget);
            if (viewProgramDuration) viewProgramDuration.value = formatDuration(program.startDate, program.endDate);
            if (viewProgramStatus) {
                viewProgramStatus.value = program.status.charAt(0).toUpperCase() + program.status.slice(1);
            }
            resetModalMaximize(viewModal);
            if (viewModal) viewModal.style.display = 'flex';
        });
    }

    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal || e.target.hasAttribute('data-view-close')) {
                resetModalMaximize(viewModal);
                viewModal.style.display = 'none';
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const title = (titleInput?.value || '').trim();
            const programName = (programNameInput?.value || '').trim();
            const committee = (committeeInput?.value || '').trim();
            const budgetVal = (budgetInput?.value || '').trim().replaceAll(',', '');
            const startDate = (startInput?.value || '').trim();
            const endDate = (endInput?.value || '').trim();
            const status = (statusInput?.value || 'planned').trim();

            if (!title || !programName || !committee || !budgetVal || !startDate || !endDate) {
                alert('Please complete required fields (program type, name, title, budget, dates).');
                return;
            }

            const budget = Number(budgetVal) || 0;
            if (budget < 0) {
                alert('Budget cannot be negative.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                const payload = {
                    title,
                    programName,
                    committee,
                    budget,
                    startDate,
                    endDate,
                    status: status || 'planned',
                };
                if (editingIndex >= 0 && programs[editingIndex]) {
                    programs[editingIndex] = payload;
                } else {
                    programs.push(payload);
                }

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal(editingIndex >= 0 ? 'Update successful.' : 'Add successful.');
                editingIndex = -1;
            }, 600);
        });
    }

    if (successModal) {
        successModal.addEventListener('click', (e) => {
            if (e.target === successModal || e.target.hasAttribute('data-success-close')) {
                closeSuccessModal();
            }
        });
    }

    render();

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(modal);
    wireModalToggle(viewModal);
}

