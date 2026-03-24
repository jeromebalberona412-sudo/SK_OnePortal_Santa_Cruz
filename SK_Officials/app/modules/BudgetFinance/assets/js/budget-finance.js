document.addEventListener('DOMContentLoaded', () => {
    initializeBudgetFinanceUI();
});

function initializeBudgetFinanceUI() {
    const tableBody = document.getElementById('budgetTableBody');
    const printTableBody = document.getElementById('budgetPrintTableBody');

    const summaryTotalBudget = document.getElementById('summaryTotalBudget');
    const summaryTotalExpenses = document.getElementById('summaryTotalExpenses');
    const summaryRemainingBudget = document.getElementById('summaryRemainingBudget');

    const printTotalBudget = document.getElementById('printTotalBudget');
    const printTotalExpenses = document.getElementById('printTotalExpenses');
    const printRemainingBudget = document.getElementById('printRemainingBudget');

    const addBudgetBtn = document.getElementById('addBudgetBtn');
    const addBudgetModal = document.getElementById('addBudgetModal');
    const budgetProgramSelect = document.getElementById('budgetProgramSelect');
    const allocatedBudgetInput = document.getElementById('allocatedBudgetInput');
    const budgetDescriptionInput = document.getElementById('budgetDescriptionInput');
    const budgetSaveBtn = document.getElementById('budgetSaveBtn');

    const addExpenseModal = document.getElementById('addExpenseModal');
    const addExpenseModalTitle = document.getElementById('addExpenseModalTitle');
    const expenseNameInput = document.getElementById('expenseNameInput');
    const expenseAmountInput = document.getElementById('expenseAmountInput');
    const expenseDateInput = document.getElementById('expenseDateInput');
    const expenseRemarksInput = document.getElementById('expenseRemarksInput');
    const expenseSaveBtn = document.getElementById('expenseSaveBtn');

    const expenseViewModal = document.getElementById('expenseViewModal');
    const expenseViewModalBox = document.getElementById('expenseViewModalBox');
    const expenseViewProgramTitle = document.getElementById('expenseViewProgramTitle');
    const expenseDetailsTableBody = document.getElementById('expenseDetailsTableBody');

    const printBtn = document.getElementById('printReportBtn');
    const expenseViewMaximizeBtn = document.getElementById('expenseViewMaximizeBtn');
    if (!tableBody) return;

    // UI-only seed data (mirrors the Programs page style/theme)
    const seedPrograms = [
        {
            title: 'Youth Leadership Training',
            allocatedBudget: 50000,
            description: 'Training and materials for youth leadership.',
            status: 'Ongoing',
            expenses: [
                { name: 'Snacks & Supplies', amount: 30000, date: '2026-02-10', remarks: 'Community volunteers' },
            ],
        },
        {
            title: 'Tree Planting Project',
            allocatedBudget: 20000,
            description: 'Tree seedlings and logistics.',
            status: 'Completed',
            expenses: [
                { name: 'Seedlings', amount: 20000, date: '2026-01-25', remarks: 'All funds used' },
            ],
        },
        {
            title: 'Sports Program',
            allocatedBudget: 80000,
            description: 'Sports events, jerseys, and equipment.',
            status: 'Ongoing',
            expenses: [
                { name: 'Uniforms & Equipment', amount: 30000, date: '2026-02-18', remarks: 'Partially funded' },
            ],
        },
    ];

    // Copy seed into editable state (so UI interactions can update without backend)
    let budgets = seedPrograms.map((p) => ({
        title: p.title,
        allocatedBudget: Number(p.allocatedBudget) || 0,
        description: p.description || '',
        status: p.status || 'Ongoing',
        expenses: (p.expenses || []).map((e) => ({
            name: e.name,
            amount: Number(e.amount) || 0,
            date: e.date,
            remarks: e.remarks || '',
        })),
    }));

    // Link from Programs page: ?program=Program+Title
    const urlParams = new URLSearchParams(window.location.search);
    let currentProgramFilter = urlParams.get('program') || '';
    currentProgramFilter = currentProgramFilter.trim();

    // Internal selections for modals
    let modalExpenseProgramTitle = '';
    let modalViewProgramTitle = '';

    const programTitleOptions = Array.from(new Set(budgets.map((p) => p.title)));

    // ---------- Helpers ----------
    function formatMoney(value) {
        const n = Number(value) || 0;
        return '₱' + n.toLocaleString('en-PH', { maximumFractionDigits: 0 });
    }

    function formatDateISOToReadable(isoDate) {
        if (!isoDate) return '';
        const d = new Date(isoDate);
        if (Number.isNaN(d.getTime())) return isoDate;
        const opts = { month: 'short', day: '2-digit', year: 'numeric' };
        return d.toLocaleDateString(undefined, opts);
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function computeTotalsForProgram(program) {
        const expensesTotal = (program.expenses || []).reduce((sum, e) => sum + (Number(e.amount) || 0), 0);
        const remaining = (Number(program.allocatedBudget) || 0) - expensesTotal;
        return { expensesTotal, remaining };
    }

    function computeDisplayStatus(program, remaining) {
        if (remaining <= 0) return 'Completed';
        // Keep seed status as baseline, but remaining rule wins.
        return program.status === 'Completed' ? 'Completed' : 'Ongoing';
    }

    function getDisplayedPrograms() {
        if (!currentProgramFilter) return budgets;
        return budgets.filter((p) => p.title === currentProgramFilter);
    }

    function setPrintAreaTotals(totals) {
        if (printTotalBudget) printTotalBudget.textContent = formatMoney(totals.totalBudget);
        if (printTotalExpenses) printTotalExpenses.textContent = formatMoney(totals.totalExpenses);
        if (printRemainingBudget) printRemainingBudget.textContent = formatMoney(totals.remainingBudget);
    }

    // ---------- Modal open/close ----------
    function openModal(modalEl) {
        if (!modalEl) return;
        modalEl.style.display = 'flex';
    }

    function closeModal(modalEl) {
        if (!modalEl) return;
        modalEl.style.display = 'none';
    }

    function wireCloseOnBackdrop(modalEl) {
        if (!modalEl) return;
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl || e.target.hasAttribute('data-modal-close') || e.target.hasAttribute('data-modal-cancel')) {
                closeModal(modalEl);
            }
        });
    }

    wireCloseOnBackdrop(addBudgetModal);
    wireCloseOnBackdrop(expenseViewModal);

    // Reset minimize/maximize state when closing the expense details modal.
    if (expenseViewModal) {
        expenseViewModal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === expenseViewModal || target.hasAttribute('data-modal-close')) {
                resetExpenseViewModalState();
            }
        });
    }

    // ---------- Populate dropdown ----------
    if (budgetProgramSelect) {
        // Ensure the linked program is present in dropdown even if user filtered to it
        const linkedTitle = currentProgramFilter ? currentProgramFilter : '';
        const mergedTitles = new Set([...programTitleOptions]);
        if (linkedTitle) mergedTitles.add(linkedTitle);

        budgetProgramSelect.innerHTML = '';
        Array.from(mergedTitles).forEach((title) => {
            const opt = document.createElement('option');
            opt.value = title;
            opt.textContent = title;
            budgetProgramSelect.appendChild(opt);
        });

        // Prefill dropdown with linked program
        if (linkedTitle) budgetProgramSelect.value = linkedTitle;
    }

    // ---------- Render ----------
    function render() {
        const displayed = getDisplayedPrograms();

        // Summary totals (for displayed list)
        const totals = displayed.reduce(
            (acc, p) => {
                const { expensesTotal, remaining } = computeTotalsForProgram(p);
                acc.totalBudget += Number(p.allocatedBudget) || 0;
                acc.totalExpenses += expensesTotal;
                acc.remainingBudget += remaining;
                return acc;
            },
            { totalBudget: 0, totalExpenses: 0, remainingBudget: 0 }
        );

        if (summaryTotalBudget) summaryTotalBudget.textContent = formatMoney(totals.totalBudget);
        if (summaryTotalExpenses) summaryTotalExpenses.textContent = formatMoney(totals.totalExpenses);
        if (summaryRemainingBudget) summaryRemainingBudget.textContent = formatMoney(totals.remainingBudget);

        if (printTotalBudget || printTotalExpenses || printRemainingBudget) {
            setPrintAreaTotals(totals);
        }

        tableBody.innerHTML = '';
        printTableBody && (printTableBody.innerHTML = '');

        if (displayed.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 6;
            td.textContent = 'No budgets added yet for this program (UI only).';
            td.style.textAlign = 'center';
            td.style.fontSize = '13px';
            td.style.color = '#6b7280';
            tr.appendChild(td);
            tableBody.appendChild(tr);

            if (printTableBody) {
                const pr = document.createElement('tr');
                const pd = document.createElement('td');
                pd.colSpan = 5;
                pd.textContent = 'No budgets available for printing (UI only).';
                pd.style.textAlign = 'center';
                pd.style.fontSize = '13px';
                pd.style.color = '#6b7280';
                pr.appendChild(pd);
                printTableBody.appendChild(pr);
            }
            return;
        }

        displayed.forEach((p) => {
            const { expensesTotal, remaining } = computeTotalsForProgram(p);
            const statusLabel = computeDisplayStatus(p, remaining);
            const statusClass = statusLabel === 'Completed' ? 'Completed' : 'Ongoing';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(p.title)}</td>
                <td class="budget-row-budget">${formatMoney(p.allocatedBudget)}</td>
                <td class="budget-row-expenses">${formatMoney(expensesTotal)}</td>
                <td class="budget-row-remaining">${formatMoney(remaining)}</td>
                <td>
                    <span class="status-pill ${statusClass}">${statusLabel}</span>
                </td>
                <td>
                    <div class="budget-actions">
                        <button type="button" class="budget-view-btn" data-action="view-expense" data-program="${encodeURIComponent(
                p.title
            )}">View</button>
                    </div>
                </td>
            `;
            tableBody.appendChild(tr);

            if (printTableBody) {
                const prTr = document.createElement('tr');
                prTr.innerHTML = `
                    <td>${escapeHtml(p.title)}</td>
                    <td class="budget-row-budget">${formatMoney(p.allocatedBudget)}</td>
                    <td class="budget-row-expenses">${formatMoney(expensesTotal)}</td>
                    <td class="budget-row-remaining">${formatMoney(remaining)}</td>
                    <td>
                        <span class="status-pill ${statusClass}">${statusLabel}</span>
                    </td>
                `;
                printTableBody.appendChild(prTr);
            }
        });
    }

    render();

    // ---------- Add Budget ----------
    if (addBudgetBtn) {
        addBudgetBtn.addEventListener('click', () => {
            // Default modal selection to current program filter if present
            if (budgetProgramSelect && currentProgramFilter) {
                budgetProgramSelect.value = currentProgramFilter;
            }

            if (allocatedBudgetInput) allocatedBudgetInput.value = '';
            if (budgetDescriptionInput) budgetDescriptionInput.value = '';

            openModal(addBudgetModal);
        });
    }

    if (budgetSaveBtn) {
        budgetSaveBtn.addEventListener('click', () => {
            const selectedProgram = (budgetProgramSelect?.value || '').trim();
            const allocatedBudget = Number((allocatedBudgetInput?.value || '').trim()) || 0;
            const desc = (budgetDescriptionInput?.value || '').trim();

            if (!selectedProgram) {
                alert('Please select a program.');
                return;
            }
            if (allocatedBudget <= 0) {
                alert('Allocated Budget must be greater than 0.');
                return;
            }

            budgetSaveBtn.disabled = true;
            budgetSaveBtn.textContent = 'Saving...';

            setTimeout(() => {
                const existing = budgets.find((b) => b.title === selectedProgram);
                if (existing) {
                    existing.allocatedBudget = allocatedBudget;
                    existing.description = desc;
                } else {
                    budgets.push({
                        title: selectedProgram,
                        allocatedBudget,
                        description: desc,
                        status: 'Ongoing',
                        expenses: [],
                    });
                }

                closeModal(addBudgetModal);
                budgetSaveBtn.disabled = false;
                budgetSaveBtn.textContent = 'Save';

                // If user added a budget to the currently-filtered program, keep it visible.
                // Otherwise, leave filter as-is.
                if (currentProgramFilter && currentProgramFilter !== selectedProgram) {
                    // no-op
                } else {
                    currentProgramFilter = selectedProgram;
                    if (budgetProgramSelect) budgetProgramSelect.value = selectedProgram;
                }

                render();
                alert('Budget saved (UI only, no backend yet).');
            }, 450);
        });
    }

    // ---------- Add Expense + View Expense (event delegation) ----------
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;

            const action = target.getAttribute('data-action');
            const program = target.getAttribute('data-program');
            if (!action || !program) return;

            const decodedProgram = decodeURIComponent(program);

            if (action === 'view-expense') {
                modalViewProgramTitle = decodedProgram;
                const programObj = budgets.find((b) => b.title === decodedProgram);
                if (expenseViewProgramTitle) expenseViewProgramTitle.textContent = ` - ${decodedProgram}`;

                // Reset view modal state (in case user minimized/maximized before).
                if (expenseViewModalBox) {
                    expenseViewModalBox.classList.remove('modal-minimized', 'modal-maximized');
                }
                if (expenseViewModal) {
                    expenseViewModal.classList.remove('modal-maximized');
                }
                if (expenseViewMaximizeBtn) expenseViewMaximizeBtn.textContent = '□';

                expenseDetailsTableBody.innerHTML = '';
                if (!programObj || (programObj.expenses || []).length === 0) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 4;
                    td.textContent = 'No expenses yet for this program (UI only).';
                    td.style.textAlign = 'center';
                    td.style.fontSize = '13px';
                    td.style.color = '#6b7280';
                    tr.appendChild(td);
                    expenseDetailsTableBody.appendChild(tr);
                } else {
                    programObj.expenses.forEach((ex) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(ex.name)}</td>
                            <td>${formatMoney(ex.amount)}</td>
                            <td>${escapeHtml(formatDateISOToReadable(ex.date))}</td>
                            <td>${escapeHtml(ex.remarks || '')}</td>
                        `;
                        expenseDetailsTableBody.appendChild(tr);
                    });
                }

                openModal(expenseViewModal);
            }
        });
    }

    // ---------- Print ----------
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            render(); // ensure print table is up to date
            window.print();
        });
    }

    function resetExpenseViewModalState() {
        if (expenseViewModalBox) expenseViewModalBox.classList.remove('modal-maximized');
        if (expenseViewModal) expenseViewModal.classList.remove('modal-maximized');
        if (expenseViewMaximizeBtn) expenseViewMaximizeBtn.textContent = '□';
    }

    // ---------- Expense View Modal Controls ----------
    // Calendar-style maximize/restore with X close.
    function applyExpenseViewMaximized(isMax) {
        if (expenseViewModal) expenseViewModal.classList.toggle('modal-maximized', isMax);
        if (expenseViewModalBox) expenseViewModalBox.classList.toggle('modal-maximized', isMax);
    }

    if (expenseViewMaximizeBtn && expenseViewModalBox) {
        expenseViewMaximizeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willMaximize = !expenseViewModalBox.classList.contains('modal-maximized');
            applyExpenseViewMaximized(willMaximize);
            expenseViewMaximizeBtn.textContent = willMaximize ? '⧉' : '□';
        });
    }
}

