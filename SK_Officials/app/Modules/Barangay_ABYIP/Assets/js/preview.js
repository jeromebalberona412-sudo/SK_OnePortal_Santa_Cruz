import { formatPeso, validateBudgetTriple, normalizeCurrency } from './validation.js';

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function displayValue(value) {
    const text = String(value ?? '').trim();
    return text === '' ? '—' : escapeHtml(text);
}

function periodDisplay(row) {
    if (row.implementation_period) {
        return row.implementation_period;
    }

    const start = row.implementation_start || '';
    const end = row.implementation_end || '';
    if (start && end) {
        return `${start} – ${end}`;
    }

    return start || end || '';
}

function mooeDisplay(row) {
    if (row.grouped_budget || /^Included in/i.test(String(row.validation_message || row.included_in || ''))) {
        return row.included_in || row.validation_message || '';
    }

    return row.mooe || '';
}

function statusBadge(row) {
    if (row.grouped_budget) {
        return '<span class="abyip-preview-status is-valid">Grouped</span>';
    }

    if (row.manual_review_required || row.validation_status === 'warning') {
        return '<span class="abyip-preview-status is-warning">Review</span>';
    }

    return '<span class="abyip-preview-status is-valid">Valid</span>';
}

function documentField(label, value) {
    return `<div class="abyip-preview-meta-item"><span>${escapeHtml(label)}</span><strong>${displayValue(value)}</strong></div>`;
}

export function renderImportPreview(payload, container) {
    if (!container) {
        return;
    }

    const document = payload.document || {};
    const rows = payload.rows || [];
    const stats = payload.stats || {};
    const tableRows = rows.map((row, index) => {
        const editable = row.row_type === 'expenditure' || row.row_type === 'activity';
        const grouped = Boolean(row.grouped_budget);
        const budgetDisabled = editable && !grouped ? '' : 'readonly';

        return `
            <tr class="abyip-preview-row ${row.manual_review_required ? 'needs-review' : ''}" data-index="${index}" data-row-type="${escapeHtml(row.row_type || '')}" data-grouped="${grouped ? '1' : '0'}">
                <td><input type="text" data-field="program_name" value="${escapeHtml(row.program_name || '')}" ${editable ? '' : 'readonly'}></td>
                <td><input type="text" data-field="category" value="${escapeHtml(row.category || '')}" ${editable ? '' : 'readonly'}></td>
                <td>
                    <input type="text" data-field="activity_name" value="${escapeHtml(row.activity_name || '')}" ${editable ? '' : 'readonly'}>
                    ${row.page_number ? `<small class="abyip-source-page">Source: Page ${escapeHtml(row.page_number)}</small>` : ''}
                </td>
                <td><textarea data-field="description" ${editable ? '' : 'readonly'}>${escapeHtml(row.description || '')}</textarea></td>
                <td><textarea data-field="expected_result" ${editable ? '' : 'readonly'}>${escapeHtml(row.expected_result || '')}</textarea></td>
                <td><textarea data-field="performance_indicator" ${editable ? '' : 'readonly'}>${escapeHtml(row.performance_indicator || '')}</textarea></td>
                <td><input type="text" data-field="implementation_period" value="${escapeHtml(periodDisplay(row))}" ${editable ? '' : 'readonly'}></td>
                <td><input type="text" class="abyip-budget-input" data-field="mooe" value="${escapeHtml(mooeDisplay(row))}" ${budgetDisabled}></td>
                <td><input type="text" class="abyip-budget-input" data-field="co" value="${escapeHtml(grouped ? '' : (row.co || ''))}" ${budgetDisabled}></td>
                <td><input type="text" class="abyip-budget-input" data-field="total" value="${escapeHtml(grouped ? '' : (row.total || ''))}" ${budgetDisabled}></td>
                <td><input type="text" data-field="person_responsible" value="${escapeHtml(row.person_responsible || '')}" ${editable ? '' : 'readonly'}></td>
                <td class="abyip-preview-status-cell">${statusBadge(row)}</td>
            </tr>
        `;
    }).join('');

    container.innerHTML = `
        <section class="abyip-import-preview" id="abyipImportPreview">
            <header class="abyip-import-preview-header">
                <h3>ABYIP extracted table</h3>
                <p>Each row is one Program / Category / Activity (PPA), matching the ABYIP one-table format.</p>
            </header>
            <div class="abyip-preview-meta">
                ${documentField('Country', document.country)}
                ${documentField('Region', document.region)}
                ${documentField('Province', document.province)}
                ${documentField('Municipality', document.municipality)}
                ${documentField('Barangay', document.barangay_name)}
                ${documentField('Organization', document.organization || document.sk_council_name)}
                ${documentField('Document', document.document_title)}
                ${documentField('Fiscal Year', document.fiscal_year)}
                ${documentField('Barangay Estimated Budget', document.barangay_estimated_budget ? `₱${formatPeso(document.barangay_estimated_budget)}` : null)}
                ${documentField('SK Fund', document.sk_fund_amount ? `₱${formatPeso(document.sk_fund_amount)}` : null)}
                ${documentField('Prepared By', document.prepared_by_name || document.prepared_by)}
                ${documentField('Prepared Position', document.prepared_by_position || document.prepared_position)}
                ${documentField('Approved By', document.approved_by_name || document.approved_by)}
                ${documentField('Approved Position', document.approved_by_position || document.approved_position)}
            </div>
            <div class="abyip-preview-stats">
                <span>${stats.pages || 0} pages processed</span>
                <span>${stats.rowsDetected || rows.length} rows detected</span>
                <span>${stats.rowsRequiringReview || 0} require review</span>
            </div>
            <div class="table-wrapper abyip-preview-table-wrap">
                <table class="abyip-preview-table" id="abyipPreviewTable">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Category / Section</th>
                            <th>Activity / PPA</th>
                            <th>Description</th>
                            <th>Expected Result</th>
                            <th>Performance Indicator</th>
                            <th>Period</th>
                            <th>MOOE</th>
                            <th>CO</th>
                            <th>Total</th>
                            <th>Person Responsible</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows || '<tr><td colspan="12">No table rows were extracted. The PDF text was preserved for manual review.</td></tr>'}</tbody>
                </table>
            </div>
        </section>
    `;

    container.querySelectorAll('.abyip-budget-input').forEach((input) => {
        input.addEventListener('input', () => revalidatePreviewRow(input.closest('tr')));
    });
}

export function revalidatePreviewRow(rowEl) {
    if (!rowEl) {
        return;
    }

    if (rowEl.getAttribute('data-grouped') === '1') {
        const cell = rowEl.querySelector('.abyip-preview-status-cell');
        if (cell) {
            cell.innerHTML = '<span class="abyip-preview-status is-valid">Grouped</span>';
        }
        rowEl.classList.remove('needs-review');
        return;
    }

    const mooe = rowEl.querySelector('[data-field="mooe"]')?.value;
    const co = rowEl.querySelector('[data-field="co"]')?.value;
    const total = rowEl.querySelector('[data-field="total"]')?.value;
    const result = validateBudgetTriple(mooe, co, total);
    const cell = rowEl.querySelector('.abyip-preview-status-cell');

    rowEl.classList.toggle('needs-review', result.manualReviewRequired);
    if (cell) {
        cell.innerHTML = result.manualReviewRequired
            ? `<span class="abyip-preview-status is-warning" title="${escapeHtml(result.message)}">Review</span>`
            : '<span class="abyip-preview-status is-valid">Valid</span>';
    }
}

export function collectPreviewEdits(container, originalRows) {
    const rows = (originalRows || []).slice();
    const tableRows = container?.querySelectorAll('.abyip-preview-row') || [];

    tableRows.forEach((rowEl) => {
        const index = Number.parseInt(rowEl.getAttribute('data-index'), 10);
        if (!Number.isFinite(index) || !rows[index]) {
            return;
        }

        const fields = {};
        rowEl.querySelectorAll('[data-field]').forEach((input) => {
            fields[input.getAttribute('data-field')] = input.value.trim();
        });

        if (rows[index].grouped_budget) {
            rows[index] = {
                ...rows[index],
                program_name: fields.program_name || rows[index].program_name,
                category: fields.category || rows[index].category,
                activity_name: fields.activity_name || rows[index].activity_name,
                description: fields.description || null,
                expected_result: fields.expected_result || null,
                performance_indicator: fields.performance_indicator || null,
                implementation_period: fields.implementation_period || rows[index].implementation_period,
                person_responsible: fields.person_responsible || null,
                mooe: null,
                co: null,
                total: null,
                validation_status: 'valid',
                validation_message: rows[index].included_in || rows[index].validation_message,
                manual_review_required: false,
            };
            return;
        }

        const mooe = normalizeCurrency(fields.mooe);
        const co = normalizeCurrency(fields.co);
        const total = normalizeCurrency(fields.total);
        const budget = validateBudgetTriple(mooe, co, total);
        const name = fields.activity_name || rows[index].activity_name || rows[index].program_name;

        rows[index] = {
            ...rows[index],
            program_name: fields.program_name || rows[index].program_name,
            category: fields.category || rows[index].category,
            activity_name: rows[index].row_type === 'activity' || rows[index].row_type === 'expenditure' ? name : rows[index].activity_name,
            description: fields.description || null,
            expected_result: fields.expected_result || null,
            performance_indicator: fields.performance_indicator || null,
            implementation_period: fields.implementation_period || rows[index].implementation_period,
            person_responsible: fields.person_responsible || null,
            mooe,
            co,
            total,
            validation_status: budget.status,
            validation_message: budget.message || null,
            manual_review_required: budget.manualReviewRequired,
        };
    });

    return rows;
}

export function collectDocumentEdits(payload) {
    return { ...(payload.document || {}) };
}
