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
    const committeeInput = document.getElementById('programCommitteeInput');
    const otherProgramField = document.getElementById('otherProgramField');
    const otherProgramInput = document.getElementById('otherProgramInput');
    const budgetInput = document.getElementById('programBudgetInput');
    const startInput = document.getElementById('programStartInput');
    const endInput = document.getElementById('programEndInput');
    const statusInput = document.getElementById('programStatusInput');
    const saveBtn = document.getElementById('programSaveBtn');
    const successModal = document.getElementById('programSuccessModal');
    const successMessage = document.getElementById('programSuccessMessage');

    const summaryTotal = document.getElementById('summaryTotalPrograms');
    const summaryPlanned = document.getElementById('summaryPlanned');
    const summaryOngoing = document.getElementById('summaryOngoing');
    const summaryCompleted = document.getElementById('summaryCompleted');

    if (!tbody) return;

    // Start empty; programs appear only after "Add Program"
    const programs = [];

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
                            <a class="program-action-btn" href="/budget-finance?program=${encodeURIComponent(
                                p.title
                            )}">Budget &amp; Finance</a>
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
        if (titleInput) titleInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        if (titleInput) titleInput.value = '';
        if (committeeInput) committeeInput.value = '';
        if (otherProgramInput) otherProgramInput.value = '';
        if (otherProgramField) otherProgramField.style.display = 'none';
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

    // Committee dropdown change event
    if (committeeInput) {
        committeeInput.addEventListener('change', () => {
            if (otherProgramField && otherProgramInput) {
                if (committeeInput.value === 'Other') {
                    otherProgramField.style.display = 'block';
                } else {
                    otherProgramField.style.display = 'none';
                    otherProgramInput.value = '';
                }
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const title = (titleInput?.value || '').trim();
            let committee = (committeeInput?.value || '').trim();
            const otherProgram = (otherProgramInput?.value || '').trim();
            const budgetVal = (budgetInput?.value || '').trim();
            const startDate = (startInput?.value || '').trim();
            const endDate = (endInput?.value || '').trim();
            const status = (statusInput?.value || 'planned').trim();

            // Handle Other program option
            if (committee === 'Other' && otherProgram) {
                committee = otherProgram;
            } else if (committee === 'Other') {
                alert('Please specify program name.');
                return;
            }

            if (!title || !committee || !budgetVal || !startDate || !endDate) {
                alert('Please complete required fields (title, committee, budget, dates). UI only.');
                return;
            }

            const budget = Number(budgetVal) || 0;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                programs.push({
                    title,
                    committee,
                    budget,
                    startDate,
                    endDate,
                    status: status || 'planned',
                });

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal('Add successful.');
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
}

