<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget & Finance - SK Officials Portal</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/BudgetFinance/assets/css/budget-finance.css'
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container budget-finance-page">
        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Budget & Finance</h1>
                <p class="page-subtitle">
                    Track allocated budgets, expenses, and remaining funds per program.
                </p>
            </div>
            <div class="page-header-right no-print">
                <button type="button" class="btn primary-btn" id="printReportBtn">
                    Print Report
                </button>
            </div>
        </section>

        <section class="budget-summary-section no-print">
            <div class="summary-cards">
                <div class="summary-card blue">
                    <div class="summary-card-label">Total Budget</div>
                    <div class="summary-card-value" id="summaryTotalBudget">₱0</div>
                </div>
                <div class="summary-card red">
                    <div class="summary-card-label">Total Expenses</div>
                    <div class="summary-card-value" id="summaryTotalExpenses">₱0</div>
                </div>
                <div class="summary-card green">
                    <div class="summary-card-label">Remaining Budget</div>
                    <div class="summary-card-value" id="summaryRemainingBudget">₱0</div>
                </div>
            </div>
        </section>

        <section class="page-content-section no-print">
            <div class="section-heading-row">
                <h2 class="section-title">Budget Table</h2>
                <p class="section-description">
                    Events represent the actual implementation of SK programs—so budgets, expenses, and remaining funds should be monitored per program.
                </p>
                <p class="section-description">
                    About Events: track trainings, drives, and activities where these budgets are used.
                </p>
            </div>

            <div class="table-wrapper budget-table-wrapper">
                <table class="budget-table">
                    <thead>
                        <tr>
                            <th>Program Title</th>
                            <th>Allocated Budget</th>
                            <th>Expenses</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="budgetTableBody">
                        <!-- Rendered by budget-finance.js -->
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- Add Budget Modal -->
<div class="modal-backdrop" id="addBudgetModal" style="display:none;">
    <div class="modal-box budget-modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Add Budget</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="budgetProgramSelect">Program Title</label>
                <select id="budgetProgramSelect">
                    <!-- Populated by budget-finance.js -->
                </select>
            </div>

            <div class="modal-field">
                <label for="allocatedBudgetInput">Allocated Budget</label>
                <input type="text" id="allocatedBudgetInput" inputmode="numeric" placeholder="e.g. 50,000">
            </div>

            <div class="modal-field">
                <label for="budgetDescriptionInput">Description (optional)</label>
                <textarea id="budgetDescriptionInput" rows="3" maxlength="255" placeholder="e.g. For training and materials..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-modal-cancel>Cancel</button>
            <button type="button" class="btn primary-btn" id="budgetSaveBtn">Save</button>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal-backdrop" id="addExpenseModal" style="display:none;">
    <div class="modal-box budget-modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="addExpenseModalTitle">Add Expense</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label for="expenseNameInput">Expense Name</label>
                <input type="text" id="expenseNameInput" maxlength="80" placeholder="e.g. Snacks and Materials">
            </div>

            <div class="modal-field">
                <label for="expenseAmountInput">Amount</label>
                <input type="text" id="expenseAmountInput" inputmode="numeric" placeholder="e.g. 30,000">
            </div>

            <div class="modal-field">
                <label for="expenseDateInput">Date</label>
                <input type="date" id="expenseDateInput">
            </div>

            <div class="modal-field">
                <label for="expenseRemarksInput">Remarks</label>
                <textarea id="expenseRemarksInput" rows="3" maxlength="255" placeholder="Optional remarks..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" data-modal-cancel>Cancel</button>
            <button type="button" class="btn primary-btn" id="expenseSaveBtn">Save</button>
        </div>
    </div>
</div>

<!-- Expense Details Modal -->
<div class="modal-backdrop" id="expenseViewModal" style="display:none;">
    <div class="modal-box budget-modal-box budget-expense-view-box" id="expenseViewModalBox">
        <div class="modal-header">
            <h2 class="modal-title">
                Expense Details
                <span class="muted" id="expenseViewProgramTitle"></span>
            </h2>
            <div class="modal-window-controls">
                <button type="button" class="modal-toggle-btn" id="expenseViewMaximizeBtn" aria-label="Maximize">□</button>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
            </div>
        </div>
        <div class="modal-body">
            <div class="expense-details-table-wrapper">
                <table class="expense-details-table">
                    <thead>
                        <tr>
                            <th>Expense Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="expenseDetailsTableBody">
                        <!-- Rendered by budget-finance.js -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print-only Area -->
<div id="budgetPrintArea" class="print-only">
    <h2 class="print-title">Budget & Finance Report</h2>
    <div class="print-summary">
        <div class="print-summary-item blue">
            <div class="print-summary-label">Total Budget</div>
            <div class="print-summary-value" id="printTotalBudget">₱0</div>
        </div>
        <div class="print-summary-item red">
            <div class="print-summary-label">Total Expenses</div>
            <div class="print-summary-value" id="printTotalExpenses">₱0</div>
        </div>
        <div class="print-summary-item green">
            <div class="print-summary-label">Remaining Budget</div>
            <div class="print-summary-value" id="printRemainingBudget">₱0</div>
        </div>
    </div>

    <div class="print-table-wrapper">
        <table class="budget-table">
            <thead>
                <tr>
                    <th>Program Title</th>
                    <th>Allocated Budget</th>
                    <th>Expenses</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="budgetPrintTableBody">
                <!-- Rendered by budget-finance.js -->
            </tbody>
        </table>
    </div>
</div>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/BudgetFinance/assets/js/budget-finance.js'
])
</body>
</html>

