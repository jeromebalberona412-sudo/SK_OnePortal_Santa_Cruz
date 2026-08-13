import { validateBudgetTriple, normalizeCurrency } from './validation.js';

function readField(rowEl, field) {
    return rowEl.querySelector(`[data-field="${field}"]`)?.value ?? '';
}

export function revalidateEditorRow(rowEl) {
    if (!rowEl) {
        return { status: 'valid', manualReviewRequired: false, message: '' };
    }

    const result = validateBudgetTriple(
        readField(rowEl, 'mooe'),
        readField(rowEl, 'co'),
        readField(rowEl, 'total'),
    );

    rowEl.classList.toggle('needs-review', result.manualReviewRequired);
    const statusEl = rowEl.querySelector('[data-budget-status]');
    if (statusEl) {
        statusEl.textContent = result.manualReviewRequired ? 'Review' : 'Valid';
        statusEl.classList.toggle('is-warning', result.manualReviewRequired);
        statusEl.classList.toggle('is-valid', !result.manualReviewRequired);
        statusEl.title = result.message || '';
    }

    return result;
}

export function bindBudgetEditors(root) {
    if (!root) {
        return;
    }

    root.addEventListener('input', (event) => {
        if (!event.target.matches('[data-field="mooe"], [data-field="co"], [data-field="total"]')) {
            return;
        }

        revalidateEditorRow(event.target.closest('tr'));
    });
}

export function collectEditorLinePayload(rowEl) {
    const mooe = normalizeCurrency(readField(rowEl, 'mooe'));
    const co = normalizeCurrency(readField(rowEl, 'co'));
    const total = normalizeCurrency(readField(rowEl, 'total'));
    const budget = validateBudgetTriple(mooe, co, total);

    return {
        id: Number.parseInt(rowEl.getAttribute('data-line-id'), 10) || null,
        code: readField(rowEl, 'code') || null,
        program_name: readField(rowEl, 'program_name') || null,
        category: readField(rowEl, 'category') || null,
        activity_name: readField(rowEl, 'activity_name') || null,
        description: readField(rowEl, 'description') || null,
        expected_result: readField(rowEl, 'expected_result') || null,
        performance_indicator: readField(rowEl, 'performance_indicator') || null,
        implementation_start: readField(rowEl, 'implementation_start') || null,
        implementation_end: readField(rowEl, 'implementation_end') || null,
        person_responsible: readField(rowEl, 'person_responsible') || null,
        mooe,
        co,
        total,
        validation_status: budget.status,
        validation_message: budget.message || null,
        manual_review_required: budget.manualReviewRequired,
    };
}

export function editorHasBudgetWarnings(root) {
    return Array.from(root?.querySelectorAll('tr[data-line-id]') || [])
        .some((rowEl) => revalidateEditorRow(rowEl).manualReviewRequired);
}
