document.addEventListener('DOMContentLoaded', () => {
    initializeProgramsUI();
});

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

    // Sample programs data
    const programs = [
        {
            title: "Youth Leadership Training 2026",
            programName: "Leadership Development Initiative",
            committee: "Education",
            budget: 50000,
            startDate: "2026-01-15",
            endDate: "2026-03-15",
            status: "ongoing"
        },
        {
            title: "Sports Fest 2026",
            programName: "Sports Development Program",
            committee: "Sports Development",
            budget: 75000,
            startDate: "2026-02-01",
            endDate: "2026-04-30",
            status: "ongoing"
        },
        {
            title: "Clean and Green Campaign",
            programName: "Environmental Awareness Program",
            committee: "Environment",
            budget: 30000,
            startDate: "2026-01-01",
            endDate: "2026-12-31",
            status: "ongoing"
        },
        {
            title: "Health and Wellness Drive",
            programName: "Community Health Program",
            committee: "Health",
            budget: 45000,
            startDate: "2026-03-01",
            endDate: "2026-05-31",
            status: "planned"
        },
        {
            title: "Skills Training Workshop",
            programName: "Livelihood Skills Development",
            committee: "Education",
            budget: 60000,
            startDate: "2025-09-01",
            endDate: "2025-11-30",
            status: "completed"
        }
    ];
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

    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            const action = target.getAttribute('data-action');
            if (action !== 'view') return;
            const index = Number(target.getAttribute('data-index'));
            if (Number.isNaN(index) || !programs[index]) return;

            const program = programs[index];
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


    render();

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);
}

