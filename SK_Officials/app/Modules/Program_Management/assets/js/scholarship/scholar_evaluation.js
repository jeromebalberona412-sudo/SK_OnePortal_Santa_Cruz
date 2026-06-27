/* ═══════════════════════════════════════════════════════════════════════════
   PROGRAM EVALUATION — DB-backed with GForm builder (scholarship, sports, etc.)
   ═══════════════════════════════════════════════════════════════════════════ */

const PROGRAM_LETTER_BY_KEY = {
    scholarship: 'A',
    sports: 'I',
};

function resolveProgramLetter() {
    const body = document.body;
    const explicitLetter = body?.dataset?.programLetter?.trim().toUpperCase();
    if (explicitLetter) {
        return explicitLetter;
    }

    const programKey = body?.dataset?.programKey?.trim().toLowerCase() || 'scholarship';
    return PROGRAM_LETTER_BY_KEY[programKey] || 'A';
}

function resolveProgramKey() {
    return document.body?.dataset?.programKey?.trim().toLowerCase() || 'scholarship';
}

const PROGRAM_LETTER = resolveProgramLetter();
const PROGRAM_KEY = resolveProgramKey();
let evaluations = [];
let programContext = { program: null, can_create: false };
let editingEvaluationId = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function evalApiFetch(path, options = {}) {
    const response = await fetch(path, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
        ...options,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = payload.message
            || (payload.errors && Object.values(payload.errors).flat()[0])
            || 'Request failed.';
        throw new Error(message);
    }

    return payload;
}

function showEvalToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `eval-toast eval-toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('eval-toast-show'));
    setTimeout(() => {
        toast.classList.remove('eval-toast-show');
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}

window.showEvalToast = showEvalToast;

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatProgramPeriod(item) {
    const start = item.start_date_display || formatDate(item.start_date);
    const end = item.end_date_display || formatDate(item.end_date);
    if (start === '—' && end === '—') return '—';
    return `${start} – ${end}`;
}

function getStatusBadge(status) {
    const normalized = String(status || 'open').toLowerCase();
    const classMap = {
        open: 'eval-status-progress',
        closed: 'eval-status-completed',
        draft: 'eval-status-pending',
        active: 'eval-status-progress',
    };
    const labelMap = {
        open: 'Open',
        closed: 'Closed',
        draft: 'Open',
        active: 'Open',
    };
    const className = classMap[normalized] || 'eval-status-progress';
    const label = labelMap[normalized] || status;
    return `<span class="eval-status-badge ${className}">${label}</span>`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getCurrentEvaluationYear() {
    return programContext.calendar_year || new Date().getFullYear();
}

function hasEvaluationForCurrentYear() {
    const year = getCurrentEvaluationYear();
    return evaluations.some((item) => {
        const itemYear = item.evaluation_year
            || (item.start_date ? new Date(item.start_date).getFullYear() : null);
        return itemYear === year;
    }) || Boolean(programContext.has_evaluation_for_year);
}

function updateCreateButtonState() {
    const btn = document.getElementById('btnCreateEvaluation');
    if (!btn) return;

    const blocked = !programContext.can_create || hasEvaluationForCurrentYear();
    btn.disabled = blocked;
    btn.title = blocked
        ? (programContext.create_blocked_reason
            || `An evaluation for ${getCurrentEvaluationYear()} already exists or the program period has not ended.`)
        : '';
    btn.style.opacity = blocked ? '0.55' : '';
    btn.style.cursor = blocked ? 'not-allowed' : '';
}

function renderProgramFields(program = null) {
    const titleEl = document.getElementById('evalTitleDisplay');
    const programIdEl = document.getElementById('evalAbyipProgramId');
    const startEl = document.getElementById('evalStartDate');
    const endEl = document.getElementById('evalEndDate');

    const resolved = program || programContext.program;

    if (!resolved) {
        if (titleEl) titleEl.textContent = 'No ABYIP program detected. Upload ABYIP first.';
        if (programIdEl) programIdEl.value = '';
        if (startEl) startEl.value = '';
        if (endEl) endEl.value = '';
        return;
    }

    if (titleEl) titleEl.textContent = resolved.program_name || 'Program';
    if (programIdEl) programIdEl.value = String(resolved.id || '');
    if (startEl) startEl.value = resolved.start_date || '';
    if (endEl) endEl.value = resolved.end_date || '';
}

function getFilteredEvaluations() {
    const status = document.getElementById('evalFilterStatus')?.value || '';
    const query = (document.getElementById('evalSearchInput')?.value || '').trim().toLowerCase();

    return evaluations.filter((item) => {
        const normalizedStatus = ['draft', 'active'].includes(String(item.status).toLowerCase())
            ? 'open'
            : String(item.status).toLowerCase();
        const matchStatus = !status || normalizedStatus === status;
        const haystack = [
            item.evaluation_code,
            item.title,
            item.program_name,
            item.created_by_name,
        ].join(' ').toLowerCase();
        const matchSearch = !query || haystack.includes(query);
        return matchStatus && matchSearch;
    });
}

function renderEvaluations(list = getFilteredEvaluations()) {
    const tbody = document.getElementById('evalTableBody');
    if (!tbody) return;

    if (!list.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="eval-empty-cell">
                    No evaluations found. Click "Create Evaluation" to get started.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = list.map((item) => `
        <tr>
            <td><strong>${escapeHtml(item.evaluation_code)}</strong></td>
            <td>${escapeHtml(item.title)}</td>
            <td>${escapeHtml(item.program_name)}</td>
            <td>${escapeHtml(item.date_created_display || formatDate(item.date_created))}</td>
            <td>${escapeHtml(formatProgramPeriod(item))}</td>
            <td>${getStatusBadge(item.status)}</td>
            <td>${item.questions_count ?? 0}</td>
            <td class="col-actions">
                <button type="button" class="sl-action-btn sl-action-view" data-action="view" data-id="${item.id}" title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <button type="button" class="sl-action-btn sl-action-edit" data-action="edit" data-id="${item.id}" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="sl-action-btn sl-action-delete" data-action="delete" data-id="${item.id}" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </button>
            </td>
        </tr>
    `).join('');
}

function updateStatCards(stats = {}) {
    const set = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = String(value ?? 0);
    };

    set('evalStatTotal', stats.total ?? 0);
    set('evalStatOpen', stats.open ?? 0);
    set('evalStatClosed', stats.closed ?? 0);
}

async function loadProgramContext() {
    const payload = await evalApiFetch(`/api/program-evaluations/meta?letter=${PROGRAM_LETTER}`);
    programContext = payload.data || { program: null, can_create: false };
    renderProgramFields();
    updateCreateButtonState();
}

async function loadEvaluations() {
    const payload = await evalApiFetch(`/api/program-evaluations?letter=${PROGRAM_LETTER}`);
    evaluations = payload.data || [];
    updateStatCards(payload.stats || {});
    renderEvaluations();
    updateCreateButtonState();
}

function resetCreateEvalModalSize() {
    const overlay = document.getElementById('createEvalModal');
    const box = document.getElementById('createEvalModalBox');
    const maximizeBtn = document.getElementById('createEvalMaximize');

    overlay?.classList.remove('sl-overlay-maximized');
    box?.classList.remove('sl-modal-maximized');

    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
        maximizeBtn.setAttribute('aria-label', 'Maximize');
    }
}

function resetCreateForm() {
    editingEvaluationId = null;
    document.getElementById('createEvalForm')?.reset();
    document.getElementById('evalId').value = '';
    document.getElementById('createEvalModalTitle').textContent = 'Create Evaluation';
    document.getElementById('btnSaveEval').textContent = 'Save Evaluation';
    document.getElementById('evalStatus').value = 'open';
    renderProgramFields();
    window.GFormBuilder?.reset();
    resetCreateEvalModalSize();
}

function openCreateModal() {
    if (!programContext.can_create || hasEvaluationForCurrentYear()) {
        showEvalToast(
            programContext.create_blocked_reason
                || `Cannot create evaluation for ${getCurrentEvaluationYear()}.`,
            'error',
        );
        return;
    }

    resetCreateForm();
    document.getElementById('createEvalModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createEvalModal').style.display = 'none';
    resetCreateForm();
}

function closeViewModal() {
    document.getElementById('viewEvalModal').style.display = 'none';
}

async function openEditModal(id) {
    resetCreateEvalModalSize();
    const payload = await evalApiFetch(`/api/program-evaluations/${id}?letter=${PROGRAM_LETTER}`);
    const item = payload.data;
    if (!item) return;

    editingEvaluationId = id;
    document.getElementById('evalId').value = String(id);
    document.getElementById('evalInstructions').value = item.instructions || '';
    document.getElementById('evalStatus').value = ['closed'].includes(String(item.status).toLowerCase()) ? 'closed' : 'open';

    renderProgramFields({
        id: item.abyip_program_id,
        program_name: item.title || item.program_name,
        start_date: item.start_date,
        end_date: item.end_date,
    });

    window.GFormBuilder?.setQuestions(item.custom_questions || []);
    document.getElementById('createEvalModalTitle').textContent = 'Edit Evaluation';
    document.getElementById('btnSaveEval').textContent = 'Update Evaluation';
    document.getElementById('createEvalModal').style.display = 'flex';
}

async function viewEvaluation(id) {
    const payload = await evalApiFetch(`/api/program-evaluations/${id}?letter=${PROGRAM_LETTER}`);
    const item = payload.data;
    if (!item) return;

    const questions = (item.custom_questions || []).map((question, index) => `
        <div class="eval-view-question">
            <strong>${index + 1}. ${escapeHtml(question.label || 'Untitled question')}</strong>
            <span>${escapeHtml(question.type || 'text')}${question.required ? ' • Required' : ''}</span>
        </div>
    `).join('') || '<p class="eval-view-empty">No questions added yet.</p>';

    document.getElementById('viewEvalBody').innerHTML = `
        <div class="eval-view-grid">
            <div><label>Evaluation ID</label><p>${escapeHtml(item.evaluation_code)}</p></div>
            <div><label>Title</label><p>${escapeHtml(item.title)}</p></div>
            <div><label>Program</label><p>${escapeHtml(item.program_name)}</p></div>
            <div><label>Status</label><p>${getStatusBadge(item.status)}</p></div>
            <div><label>Date Created</label><p>${escapeHtml(item.date_created_display || formatDate(item.date_created))}</p></div>
            <div><label>Program Period</label><p>${escapeHtml(formatProgramPeriod(item))}</p></div>
            <div class="eval-view-full"><label>Instructions</label><p>${escapeHtml(item.instructions || 'No instructions provided.')}</p></div>
            <div class="eval-view-full"><label>Questions</label>${questions}</div>
        </div>
    `;

    document.getElementById('viewEvalModal').style.display = 'flex';
}

async function deleteEvaluation(id) {
    if (!confirm('Delete this evaluation form? This cannot be undone.')) return;

    try {
        await evalApiFetch(`/api/program-evaluations/${id}?letter=${PROGRAM_LETTER}`, { method: 'DELETE' });
        showEvalToast('Evaluation deleted successfully.');
        await loadEvaluations();
        await loadProgramContext();
    } catch (error) {
        showEvalToast(error.message, 'error');
    }
}

async function handleCreateEvaluation(event) {
    event.preventDefault();

    const programId = document.getElementById('evalAbyipProgramId')?.value || '';
    if (!programId && !editingEvaluationId) {
        showEvalToast('No ABYIP program detected for this committee. Upload ABYIP first.', 'error');
        return;
    }

    const body = {
        instructions: document.getElementById('evalInstructions')?.value.trim() || null,
        status: document.getElementById('evalStatus')?.value || 'open',
        custom_questions: window.GFormBuilder?.getQuestions() || [],
        program_letter: PROGRAM_LETTER,
        letter: PROGRAM_LETTER,
    };

    try {
        if (editingEvaluationId) {
            await evalApiFetch(`/api/program-evaluations/${editingEvaluationId}?letter=${PROGRAM_LETTER}`, {
                method: 'PUT',
                body: JSON.stringify(body),
            });
            showEvalToast('Evaluation updated successfully.');
        } else {
            await evalApiFetch(`/api/program-evaluations?letter=${PROGRAM_LETTER}`, {
                method: 'POST',
                body: JSON.stringify(body),
            });
            showEvalToast('Evaluation created successfully.');
        }

        closeCreateModal();
        await loadEvaluations();
        await loadProgramContext();
    } catch (error) {
        showEvalToast(error.message, 'error');
    }
}

function exportEvaluations() {
    const rows = getFilteredEvaluations();
    if (!rows.length) {
        showEvalToast('No evaluations to export.', 'error');
        return;
    }

    const header = ['Evaluation ID', 'Title', 'Program', 'Date Created', 'Program Start', 'Program End', 'Status', 'Questions'];
    const csvRows = [header.join(',')].concat(rows.map((item) => [
        item.evaluation_code,
        `"${String(item.title || '').replace(/"/g, '""')}"`,
        `"${String(item.program_name || '').replace(/"/g, '""')}"`,
        item.date_created || '',
        item.start_date || '',
        item.end_date || '',
        item.status || '',
        item.questions_count ?? 0,
    ].join(',')));

    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${PROGRAM_KEY}-evaluations.csv`;
    link.click();
    URL.revokeObjectURL(url);
}

document.addEventListener('DOMContentLoaded', async () => {
    if (window.GFormBuilder) {
        window.GFormBuilder.init({
            showToast: (message, type) => {
                if (typeof window.showEvalToast === 'function') {
                    window.showEvalToast(message, type);
                }
            },
        });
    }

    document.getElementById('btnCreateEvaluation')?.addEventListener('click', openCreateModal);
    document.getElementById('createEvalClose')?.addEventListener('click', closeCreateModal);
    document.getElementById('btnCancelEval')?.addEventListener('click', closeCreateModal);
    document.getElementById('createEvalForm')?.addEventListener('submit', handleCreateEvaluation);
    document.getElementById('viewEvalClose')?.addEventListener('click', closeViewModal);
    document.getElementById('btnExportEvaluations')?.addEventListener('click', exportEvaluations);
    document.getElementById('evalFilterStatus')?.addEventListener('change', () => renderEvaluations());
    document.getElementById('evalSearchInput')?.addEventListener('input', () => renderEvaluations());

    document.getElementById('createEvalModal')?.addEventListener('click', (event) => {
        if (event.target.id === 'createEvalModal') closeCreateModal();
    });
    document.getElementById('viewEvalModal')?.addEventListener('click', (event) => {
        if (event.target.id === 'viewEvalModal') closeViewModal();
    });

    const createEvalMaximize = document.getElementById('createEvalMaximize');
    const createEvalModal = document.getElementById('createEvalModal');
    const createEvalModalBox = document.getElementById('createEvalModalBox');

    if (createEvalMaximize && createEvalModal && createEvalModalBox) {
        createEvalMaximize.addEventListener('click', (event) => {
            event.stopPropagation();
            const isMax = !createEvalModalBox.classList.contains('sl-modal-maximized');
            createEvalModal.classList.toggle('sl-overlay-maximized', isMax);
            createEvalModalBox.classList.toggle('sl-modal-maximized', isMax);
            createEvalMaximize.textContent = isMax ? '⧉' : '□';
            createEvalMaximize.title = isMax ? 'Restore Down' : 'Maximize';
            createEvalMaximize.setAttribute('aria-label', isMax ? 'Restore down' : 'Maximize');
        });
    }

    document.getElementById('evalTableBody')?.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.dataset.id;
        const action = button.dataset.action;
        if (action === 'view') viewEvaluation(id);
        if (action === 'edit') openEditModal(id);
        if (action === 'delete') deleteEvaluation(id);
    });

    try {
        if (typeof window.showLoading === 'function') window.showLoading();
        await Promise.all([loadProgramContext(), loadEvaluations()]);
    } catch (error) {
        showEvalToast(error.message || 'Failed to load evaluations.', 'error');
        renderEvaluations([]);
    } finally {
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
});

window.viewEvaluation = viewEvaluation;
window.deleteEvaluation = deleteEvaluation;
