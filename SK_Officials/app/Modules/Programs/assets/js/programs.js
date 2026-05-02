document.addEventListener('DOMContentLoaded', () => {
    initializeProgramsUI();
});

// ── Toast ──────────────────────────────────────────────────────────────────
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

function initializeProgramsUI() {
    const tbody = document.getElementById('programTableBody');
    const searchInput = document.getElementById('programSearch');
    const committeeFilter = document.getElementById('programCommitteeFilter');
    const statusFilter = document.getElementById('programStatusFilter');

    const viewModal = document.getElementById('programViewModal');
    const viewProgramType = document.getElementById('viewProgramType');
    const viewProgramName = document.getElementById('viewProgramName');
    const viewProgramTitle = document.getElementById('viewProgramTitle');
    const viewProgramBudget = document.getElementById('viewProgramBudget');
    const viewProgramDuration = document.getElementById('viewProgramDuration');
    const viewProgramStatus = document.getElementById('viewProgramStatus');

    const editDurationModal  = document.getElementById('editDurationModal');
    const editDurationClose  = document.getElementById('editDurationClose');
    const editDurationCancel = document.getElementById('editDurationCancel');
    const editDurationSave   = document.getElementById('editDurationSave');
    const editDurationIndex  = document.getElementById('editDurationIndex');
    const editStartDate      = document.getElementById('editStartDate');
    const editEndDate        = document.getElementById('editEndDate');

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

    // ABYIP SK Youth Development and Empowerment Programs data
    const programs = [
        {
            title: "Equitable Access to Quality Education",
            description: "Provide school supplies to ALS Students and elementary, high school and college Students. Support to ALS and RIC, 150 Students for Educational Assistance, Support to Elementary and Daycare.",
            committee: "Equitable Access to Quality Education",
            budget: 175000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "ongoing"
        },
        {
            title: "Environmental Protection",
            description: "Honorarium for services rendered in the Clean Up Drive. Activities include Clean-Up Drive, Payroll for Laborer, and Tree Planting.",
            committee: "Environmental Protection",
            budget: 60000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "ongoing"
        },
        {
            title: "Disaster Risk Reduction and Resiliency",
            description: "Disaster preparedness measures to prepare for and reduce the effects of disaster. Includes Training on Disaster Preparedness for Youth Volunteer Groups and Distribution of Relief Goods for KK Members.",
            committee: "Disaster Risk Reduction and Resiliency",
            budget: 30000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "planned"
        },
        {
            title: "Youth Employment and Livelihood",
            description: "Increased number of skilled and employed youth. Includes Livelihood Training and provision of food and other supplies.",
            committee: "Youth Employment and Livelihood",
            budget: 20000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "planned"
        },
        {
            title: "Health",
            description: "Campaigning Materials for Anti-Drugs such as Leaflets, posters, and tarpaulins. Provision of Medicines and Medical Equipment to decrease drug-dependent youth.",
            committee: "Health",
            budget: 30000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "ongoing"
        },
        {
            title: "Anti-Drug and Peace and Order",
            description: "Orientation for Anti-Drug and Physical Abuse with Foods and Accommodations. Aims to decrease number of drug dependent youth and youth who tried using illegal drugs.",
            committee: "Anti-Drug and Peace and Order",
            budget: 10000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "planned"
        },
        {
            title: "Feeding Program for KK Members",
            description: "Improve the health and physique of the children. Program targets youth and children in the vicinity of the Barangay.",
            committee: "Feeding Program for KK Members",
            budget: 15000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "ongoing"
        },
        {
            title: "Sports Development",
            description: "Provide sports and recreational activities in the Barangay to promote Sportsmanship. Includes Supplies and Materials, Food and Accommodation, and Officiating fees.",
            committee: "Sports Development",
            budget: 250000,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "ongoing"
        },
        {
            title: "Other Programs",
            description: "Cost of expenditures for simultaneous General Assembly of the Barangay. Includes Katipunan ng Kabataan (KK) General Assembly, Barangay Day Celebration, and Youth Week.",
            committee: "Other Programs",
            budget: 67547.67,
            startDate: "2025-01-01",
            endDate: "2025-12-31",
            status: "planned"
        }
    ];

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
            const td = document.createElement('td');
            td.colSpan = 7;
            td.textContent = 'No programs found matching the current filters.';
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
                    <td class="program-desc-cell">${p.description}</td>
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

        // Update stat cards
        const statTotal = document.getElementById('progStatTotal');
        const statOngoing = document.getElementById('progStatOngoing');
        const statCompleted = document.getElementById('progStatCompleted');
        const statBudget = document.getElementById('progStatBudget');
        if (statTotal) statTotal.textContent = total;
        if (statOngoing) statOngoing.textContent = ongoing;
        if (statCompleted) statCompleted.textContent = completed;
        if (statBudget) {
            const totalBudget = list.reduce((sum, p) => sum + p.budget, 0);
            statBudget.textContent = '₱' + totalBudget.toLocaleString('en-PH');
        }
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
                if (viewProgramBudget) viewProgramBudget.value = formatBudget(program.budget);
                if (viewProgramDuration) viewProgramDuration.value = formatDuration(program.startDate, program.endDate);
                if (viewProgramStatus) {
                    viewProgramStatus.value = program.status.charAt(0).toUpperCase() + program.status.slice(1);
                }
                resetModalMaximize(viewModal);
                if (viewModal) viewModal.style.display = 'flex';

            } else if (action === 'edit') {
                // Open Edit Duration modal
                if (editDurationIndex) editDurationIndex.value = index;
                if (editStartDate) editStartDate.value = program.startDate;
                if (editEndDate) editEndDate.value = program.endDate;
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

    if (editDurationSave) {
        editDurationSave.addEventListener('click', () => {
            const idx   = parseInt(editDurationIndex.value, 10);
            const start = editStartDate.value;
            const end   = editEndDate.value;

            if (!start || !end) {
                showProgramToast('Both start and end dates are required.', 'error');
                return;
            }

            if (end < start) {
                showProgramToast('End date must be after start date.', 'error');
                return;
            }

            programs[idx].startDate = start;
            programs[idx].endDate   = end;
            closeEditDurationModal();
            render();
            showProgramToast('Edited successfully!');
        });
    }

    render();

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);
}

