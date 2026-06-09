/**
 * Program Management — Survey (Forms, Results, Analytics)
 * Committee/program context from #surveyProgramConfig + database API.
 */
import Chart from 'chart.js/auto';

const QUESTION_TYPES = [
    { value: 'radio', label: 'Multiple Choice' },
    { value: 'checkbox', label: 'Checkboxes' },
    { value: 'dropdown', label: 'Dropdown' },
    { value: 'text', label: 'Short Answer' },
    { value: 'paragraph', label: 'Paragraph' },
    { value: 'number', label: 'Number' },
    { value: 'date', label: 'Date' },
];

let committee = 'environmental';
let activeTab = 'forms';
let editingSurveyId = null;
let chartInstances = [];
let committeeContext = { programs: [] };
let surveys = [];
let responses = [];

function getPageProgramConfig() {
    const el = document.getElementById('surveyProgramConfig');
    if (!el) return {};
    try {
        return JSON.parse(el.textContent || '{}');
    } catch {
        return {};
    }
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function apiBase() {
    return `/api/program-surveys/${encodeURIComponent(committee)}`;
}

async function apiFetch(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
}

document.addEventListener('DOMContentLoaded', async () => {
    const page = getPageProgramConfig();
    committee = document.body.dataset.committee || page.committee || 'environmental';
    activeTab = document.body.dataset.surveyTab || page.activeTab || 'forms';

    bindFormsTab();
    bindResultsTab();
    bindAnalyticsTab();

    try {
        await loadCommitteeContext();
        await loadSurveys();
        if (activeTab === 'results' || activeTab === 'analytics') {
            await loadResponses();
        }
    } catch (error) {
        showToast(error.message || 'Failed to load survey data.', 'error');
    }

    if (activeTab === 'forms') renderFormsTable();
    if (activeTab === 'results') renderResultsTable();
    if (activeTab === 'analytics') renderAnalytics();
});

async function loadCommitteeContext() {
    const result = await apiFetch(`${apiBase()}/meta`);
    committeeContext = result.data || { programs: [] };
}

async function loadSurveys() {
    const result = await apiFetch(apiBase());
    surveys = result.data || [];
}

async function loadResponses() {
    const result = await apiFetch(`${apiBase()}/responses`);
    responses = result.data || [];
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(msg, type = 'success') {
    const el = document.getElementById('surveyToast');
    if (!el) return;
    el.textContent = msg;
    el.style.display = 'flex';
    el.style.background = type === 'error' ? '#ef4444' : '#22c55e';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { el.style.display = 'none'; }, 2800);
}

let surveySaveButtonDefaultHtml = '';

function setSurveySaveButtonLoading(isLoading) {
    const saveBtn = document.getElementById('surveyFormSave');
    if (!saveBtn) return;

    if (!surveySaveButtonDefaultHtml) {
        surveySaveButtonDefaultHtml = saveBtn.innerHTML;
    }

    if (isLoading) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="survey-save-btn-content"><span class="survey-save-spinner"></span> Saving...</span>';
        return;
    }

    saveBtn.disabled = false;
    saveBtn.innerHTML = surveySaveButtonDefaultHtml;
}

function getConfig() {
    const page = getPageProgramConfig();
    return {
        title: page.title || committeeContext.programs?.[0]?.program_name || 'Program Survey',
        description: page.description || '',
    };
}

function countResponsesForSurvey(surveyId) {
    const survey = surveys.find(s => String(s.id) === String(surveyId));
    if (survey && typeof survey.response_count === 'number') {
        return survey.response_count;
    }
    return responses.filter(r => String(r.surveyId || r.survey_id) === String(surveyId)).length;
}

function populateProgramSelect(selectedId) {
    const activityEl = document.getElementById('surveyActivity');
    const hintEl = document.getElementById('surveyProgramHint');
    if (!activityEl) return;

    const programs = committeeContext.programs || [];

    if (!programs.length) {
        activityEl.innerHTML = '<option value="">No ABYIP program available</option>';
        activityEl.disabled = true;
        if (hintEl) {
            hintEl.textContent = 'Upload ABYIP for your barangay before creating a survey.';
        }
        return;
    }

    activityEl.disabled = false;
    activityEl.innerHTML = programs.map(program => (
        `<option value="${program.id}">${escapeHtml(program.program_name)}</option>`
    )).join('');

    if (selectedId) {
        activityEl.value = String(selectedId);
    }

    if (hintEl) {
        const selected = programs.find(p => String(p.id) === String(activityEl.value));
        hintEl.textContent = selected
            ? `Survey title: ${selected.program_name}`
            : 'Survey title uses the selected ABYIP program name automatically.';
    }
}

// ── Forms Tab ─────────────────────────────────────────────────────────────

function bindFormsTab() {
    document.getElementById('btnCreateSurvey')?.addEventListener('click', () => openSurveyModal());
    document.getElementById('surveyFormModalClose')?.addEventListener('click', closeSurveyModal);
    document.getElementById('surveyFormCancel')?.addEventListener('click', closeSurveyModal);
    document.getElementById('surveyFormSave')?.addEventListener('click', saveSurveyForm);
    document.getElementById('formsSearch')?.addEventListener('input', renderFormsTable);
    document.getElementById('viewSurveyClose')?.addEventListener('click', () => {
        document.getElementById('viewSurveyModal').style.display = 'none';
    });
    document.getElementById('viewSurveyMaximize')?.addEventListener('click', toggleViewSurveyMaximize);
    document.getElementById('surveyFormMaximize')?.addEventListener('click', toggleSurveyFormMaximize);

    const surveyDesc = document.getElementById('surveyDescription');
    const surveyDescCount = document.getElementById('surveyDescCount');
    if (surveyDesc && surveyDescCount) {
        surveyDesc.addEventListener('input', () => {
            surveyDescCount.textContent = surveyDesc.value.length;
        });
    }

    document.getElementById('surveyActivity')?.addEventListener('change', () => {
        populateProgramSelect(document.getElementById('surveyActivity')?.value);
    });

    const today = new Date().toISOString().split('T')[0];
    const openDateInput = document.getElementById('surveyOpenDate');
    const closeDateInput = document.getElementById('surveyCloseDate');
    if (openDateInput) openDateInput.setAttribute('min', today);
    if (closeDateInput) closeDateInput.setAttribute('min', today);
}

function openSurveyModal(survey) {
    editingSurveyId = survey?.id || null;
    populateProgramSelect(survey?.abyip_program_id || survey?.abyipProgramId);

    document.getElementById('surveyFormModalTitle').textContent = survey ? 'Edit Survey Form' : 'Create Survey Form';
    document.getElementById('surveyDescription').value = survey?.announcement || survey?.description || '';
    document.getElementById('surveyOpenDate').value = survey?.openDate || survey?.open_date || '';
    document.getElementById('surveyCloseDate').value = survey?.closeDate || survey?.close_date || '';
    document.getElementById('surveyStatus').value = survey?.status || 'scheduled';

    const surveyDescCount = document.getElementById('surveyDescCount');
    if (surveyDescCount) {
        surveyDescCount.textContent = (survey?.announcement || survey?.description || '').length;
    }

    if (window.GFormBuilder) {
        window.GFormBuilder.reset();
        if (survey?.questions?.length) {
            window.GFormBuilder.setQuestions(survey.questions);
        }
    }

    document.getElementById('surveyFormModal').style.display = 'flex';
}

function closeSurveyModal() {
    document.getElementById('surveyFormModal').style.display = 'none';
    editingSurveyId = null;
    setSurveySaveButtonLoading(false);
    if (window.GFormBuilder) window.GFormBuilder.reset();
}

async function saveSurveyForm() {
    const programId = document.getElementById('surveyActivity')?.value || '';
    if (!programId) {
        showToast('Select a program activity from ABYIP.', 'error');
        return;
    }

    const questions = (window.GFormBuilder?.getQuestions() || []).filter(q => (q.label || '').trim());
    if (!questions.length) {
        showToast('Add at least one question with a label.', 'error');
        return;
    }

    const openDate = document.getElementById('surveyOpenDate')?.value || '';
    const closeDate = document.getElementById('surveyCloseDate')?.value || '';

    if (!openDate || !closeDate) {
        showToast('Open date and close date are required.', 'error');
        return;
    }

    if (new Date(closeDate) <= new Date(openDate)) {
        showToast('Close date must be later than open date.', 'error');
        return;
    }

    const payload = {
        abyip_program_id: Number(programId),
        announcement: document.getElementById('surveyDescription')?.value?.trim() || '',
        open_date: openDate,
        close_date: closeDate,
        status: document.getElementById('surveyStatus')?.value || 'scheduled',
        questions,
    };

    const saveBtn = document.getElementById('surveyFormSave');
    setSurveySaveButtonLoading(true);

    try {
        if (editingSurveyId) {
            await apiFetch(`${apiBase()}/${editingSurveyId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
        } else {
            await apiFetch(apiBase(), {
                method: 'POST',
                body: JSON.stringify(payload),
            });
        }

        await loadSurveys();
        closeSurveyModal();
        renderFormsTable();
        showToast('Survey saved successfully.');
    } catch (error) {
        showToast(error.message || 'Failed to save survey.', 'error');
    } finally {
        setSurveySaveButtonLoading(false);
    }
}

function formatSurveyDate(iso) {
    return new Date(iso).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function renderFormsTable() {
    const tbody = document.getElementById('surveyFormsTableBody');
    if (!tbody) return;

    const q = (document.getElementById('formsSearch')?.value || '').toLowerCase();
    let rows = [...surveys];

    if (q) {
        rows = rows.filter(s =>
            (s.title || s.program_name || '').toLowerCase().includes(q)
        );
    }

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="saf-table-empty">
            <div class="survey-empty-state">
                <p><strong>No survey forms yet</strong></p>
                <p>Create questions for <em>${escapeHtml(getConfig().title)}</em>. Kabataan responses appear under Survey Results and Survey Analytics.</p>
            </div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map(s => {
        const respCount = countResponsesForSurvey(s.id);
        const statusCls = getStatusClass(s.status);
        const statusLabel = getStatusLabel(s.status);
        const qCount = (s.questions || []).length;
        const openDateDisplay = s.openDate || s.open_date ? formatDate(s.openDate || s.open_date) : '—';
        const closeDateDisplay = s.closeDate || s.close_date ? formatDate(s.closeDate || s.close_date) : '—';
        const title = s.title || s.program_name || '—';

        return `
            <tr>
                <td class="survey-col-title">
                    <div class="survey-cell-title">${escapeHtml(title)}</div>
                    <div class="survey-cell-meta">${qCount} question${qCount === 1 ? '' : 's'}</div>
                </td>
                <td data-label="Open Date">${openDateDisplay}</td>
                <td data-label="Close Date">${closeDateDisplay}</td>
                <td data-label="Status"><span class="schol-pill ${statusCls}">${statusLabel}</span></td>
                <td data-label="Responses">${respCount}</td>
                <td class="col-actions" data-label="Actions">
                    <div class="schol-tbl-actions prog-tbl-actions">
                        <button type="button" class="schol-tbl-btn schol-tbl-btn-view prog-btn prog-btn-view" data-view-survey="${s.id}">View</button>
                        <button type="button" class="schol-tbl-btn schol-tbl-btn-edit prog-btn prog-btn-edit" data-edit-survey="${s.id}">Edit</button>
                        <button type="button" class="schol-tbl-btn schol-tbl-btn-delete prog-btn prog-btn-delete" data-delete-survey="${s.id}">Delete</button>
                    </div>
                </td>
            </tr>`;
    }).join('');

    tbody.querySelectorAll('[data-view-survey]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = surveys.find(x => String(x.id) === String(btn.dataset.viewSurvey));
            if (s) openViewSurveyModal(s);
        });
    });
    tbody.querySelectorAll('[data-edit-survey]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = surveys.find(x => String(x.id) === String(btn.dataset.editSurvey));
            if (s) openSurveyModal(s);
        });
    });
    tbody.querySelectorAll('[data-delete-survey]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this survey and all its responses?')) return;
            try {
                await apiFetch(`${apiBase()}/${btn.dataset.deleteSurvey}`, { method: 'DELETE' });
                await loadSurveys();
                renderFormsTable();
                showToast('Deleted successfully.');
            } catch (error) {
                showToast(error.message || 'Failed to delete survey.', 'error');
            }
        });
    });
}

function getStatusClass(status) {
    switch (status) {
        case 'scheduled': return 'schol-pill-scheduled';
        case 'open': return 'schol-pill-approved';
        case 'closed': return 'schol-pill-rejected';
        default: return 'schol-pill-rejected';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'scheduled': return 'Scheduled';
        case 'open': return 'Open';
        case 'closed': return 'Closed';
        default: return 'Unknown';
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function openViewSurveyModal(survey) {
    const body = document.getElementById('viewSurveyBody');
    const respCount = countResponsesForSurvey(survey.id);
    const title = survey.title || survey.program_name || 'Survey';

    body.innerHTML = `
        <div class="gform-preview-header">
            <h4>${escapeHtml(title)}</h4>
            <div class="gform-preview-info">
                <div><strong>Open Date:</strong> ${formatDate(survey.openDate || survey.open_date)}</div>
                <div><strong>Close Date:</strong> ${formatDate(survey.closeDate || survey.close_date)}</div>
                <div><strong>Status:</strong> ${getStatusLabel(survey.status)}</div>
                <div><strong>Responses:</strong> ${respCount}</div>
                <div><strong>Instructions:</strong> ${escapeHtml(survey.announcement || survey.description || '—')}</div>
            </div>
        </div>
        ${(survey.questions || []).map((q, i) => `
            <div class="gform-preview-q">
                <div style="font-weight:500;margin-bottom:6px;">${i + 1}. ${escapeHtml(q.label)}${q.required ? '<span style="color:#d93025"> *</span>' : ''}</div>
                <div style="font-size:12px;color:#5f6368;">Type: ${escapeHtml(QUESTION_TYPES.find(t => t.value === q.type)?.label || q.type)}</div>
                ${q.options?.length ? `<ul style="margin:8px 0 0;padding-left:20px;font-size:13px;">${q.options.map(o => `<li>${escapeHtml(o)}</li>`).join('')}</ul>` : ''}
            </div>
        `).join('')}`;
    document.getElementById('viewSurveyModal').style.display = 'flex';
}

function toggleViewSurveyMaximize() {
    const modal = document.getElementById('viewSurveyModal');
    const box = document.getElementById('viewSurveyBox');
    const btn = document.getElementById('viewSurveyMaximize');

    if (modal.classList.contains('schol-modal-maximized')) {
        modal.classList.remove('schol-modal-maximized');
        box.classList.remove('schol-modal-maximized');
        btn.textContent = '□';
        btn.title = 'Maximize';
    } else {
        modal.classList.add('schol-modal-maximized');
        box.classList.add('schol-modal-maximized');
        btn.textContent = '⧉';
        btn.title = 'Restore Down';
    }
}

function toggleSurveyFormMaximize() {
    const modal = document.getElementById('surveyFormModal');
    const box = document.getElementById('surveyFormBox');
    const btn = document.getElementById('surveyFormMaximize');

    if (modal.classList.contains('schol-modal-maximized')) {
        modal.classList.remove('schol-modal-maximized');
        box.classList.remove('schol-modal-maximized');
        btn.textContent = '□';
        btn.title = 'Maximize';
    } else {
        modal.classList.add('schol-modal-maximized');
        box.classList.add('schol-modal-maximized');
        btn.textContent = '⧉';
        btn.title = 'Restore Down';
    }
}

// ── Results Tab ───────────────────────────────────────────────────────────

function bindResultsTab() {
    ['resultsSurveyFilter', 'resultsDateFrom', 'resultsDateTo', 'resultsSearch'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', renderResultsTable);
        document.getElementById(id)?.addEventListener('input', renderResultsTable);
    });
    document.getElementById('btnExportResults')?.addEventListener('click', exportResultsCsv);
    document.getElementById('viewResponseClose')?.addEventListener('click', () => {
        document.getElementById('viewResponseModal').style.display = 'none';
    });
}

function populateSurveyFilters(selectId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    const current = sel.value;
    const first = selectId === 'analyticsSurveyFilter'
        ? '<option value="">Select survey…</option>'
        : '<option value="">All Surveys</option>';
    sel.innerHTML = first + surveys.map(s =>
        `<option value="${s.id}">${escapeHtml(s.title || s.program_name || 'Survey')}</option>`
    ).join('');
    if (current) sel.value = current;
}

function filterResponses() {
    const surveyId = document.getElementById('resultsSurveyFilter')?.value || '';
    const from = document.getElementById('resultsDateFrom')?.value || '';
    const to = document.getElementById('resultsDateTo')?.value || '';
    const search = (document.getElementById('resultsSearch')?.value || '').toLowerCase();

    return responses.filter(r => {
        const rSurveyId = String(r.surveyId || r.survey_id || '');
        if (surveyId && rSurveyId !== String(surveyId)) return false;
        const d = new Date(r.submittedAt || r.submitted_at);
        if (from && d < new Date(`${from}T00:00:00`)) return false;
        if (to && d > new Date(`${to}T23:59:59`)) return false;
        if (search) {
            const name = (r.respondentName || r.respondent_name || '').toLowerCase();
            const brgy = (r.barangay || '').toLowerCase();
            if (!name.includes(search) && !brgy.includes(search)) return false;
        }
        return true;
    });
}

async function renderResultsTable() {
    populateSurveyFilters('resultsSurveyFilter');
    const tbody = document.getElementById('surveyResultsTableBody');
    if (!tbody) return;

    if (!responses.length) {
        try {
            await loadResponses();
        } catch (error) {
            showToast(error.message || 'Failed to load responses.', 'error');
        }
    }

    const rows = filterResponses();

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">No survey responses yet.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(r => {
        const survey = r.survey || surveys.find(s => String(s.id) === String(r.surveyId || r.survey_id));
        const date = formatSurveyDate(r.submittedAt || r.submitted_at);
        const answerPreview = getResponseAnswerPreview(r, survey);

        return `
            <tr>
                <td class="survey-col-title">
                    <div class="survey-cell-title">${escapeHtml(r.respondentName || r.respondent_name)}</div>
                    <div class="survey-cell-meta">${escapeHtml(r.barangay || '—')}</div>
                </td>
                <td data-label="Survey">${escapeHtml(survey?.title || survey?.program_name || '—')}</td>
                <td data-label="Barangay">${escapeHtml(r.barangay || '—')}</td>
                <td data-label="Answer"><span class="survey-answer-pill">${escapeHtml(answerPreview)}</span></td>
                <td data-label="Date Submitted">${date}</td>
                <td class="col-actions" data-label="Actions">
                    <div class="schol-tbl-actions prog-tbl-actions">
                        <button type="button" class="schol-tbl-btn schol-tbl-btn-view prog-btn prog-btn-view" data-view-response="${r.id}">View</button>
                    </div>
                </td>
            </tr>`;
    }).join('');

    tbody.querySelectorAll('[data-view-response]').forEach(btn => {
        btn.addEventListener('click', () => {
            const r = responses.find(x => String(x.id) === String(btn.dataset.viewResponse));
            const survey = r?.survey || surveys.find(s => String(s.id) === String(r?.surveyId || r?.survey_id));
            if (r && survey) openResponseModal(r, survey);
        });
    });
}

function getResponseAnswerPreview(response, survey) {
    const questions = survey?.questions || [];
    if (!questions.length) return '—';
    const q = questions[0];
    let ans = response.answers?.[q.id];
    if (ans === undefined || ans === null || ans === '') return '—';
    if (Array.isArray(ans)) ans = ans.join(', ');
    return String(ans);
}

function openResponseModal(response, survey) {
    const body = document.getElementById('viewResponseBody');
    const answersHtml = (survey.questions || []).map((q, i) => {
        let ans = response.answers?.[q.id];
        if (Array.isArray(ans)) ans = ans.join(', ');
        if (ans === undefined || ans === null || ans === '') ans = '—';
        return `
            <div class="gform-preview-q">
                <div style="font-weight:500;margin-bottom:8px;">${i + 1}. ${escapeHtml(q.label)}</div>
                <div style="padding:10px 12px;background:#f1f3f4;border-radius:6px;border-left:3px solid #673ab7;">${escapeHtml(String(ans))}</div>
            </div>`;
    }).join('');

    body.innerHTML = `
        <div class="gform-preview-header">
            <h4>${escapeHtml(response.respondentName || response.respondent_name)}</h4>
            <div class="gform-preview-info">
                <div><strong>Survey:</strong> ${escapeHtml(survey.title || survey.program_name || '—')}</div>
                <div><strong>Barangay:</strong> ${escapeHtml(response.barangay || '—')}</div>
                <div><strong>Date Submitted:</strong> ${formatSurveyDate(response.submittedAt || response.submitted_at)}</div>
            </div>
        </div>
        ${answersHtml}`;
    document.getElementById('viewResponseModal').style.display = 'flex';
}

function exportResultsCsv() {
    const rows = filterResponses();
    if (!rows.length) {
        showToast('No data to export.', 'error');
        return;
    }
    const lines = ['Respondent,Barangay,Survey,Submitted At,Question,Answer'];
    rows.forEach(r => {
        const survey = r.survey || surveys.find(s => String(s.id) === String(r.surveyId || r.survey_id));
        const date = new Date(r.submittedAt || r.submitted_at).toISOString();
        (survey?.questions || []).forEach(q => {
            let ans = r.answers?.[q.id];
            if (Array.isArray(ans)) ans = ans.join('; ');
            lines.push([
                csvCell(r.respondentName || r.respondent_name),
                csvCell(r.barangay),
                csvCell(survey?.title || survey?.program_name),
                csvCell(date),
                csvCell(q.label),
                csvCell(ans),
            ].join(','));
        });
    });
    downloadCsv(lines.join('\n'), `${committee}-survey-results.csv`);
}

function csvCell(v) {
    const s = String(v ?? '');
    return `"${s.replace(/"/g, '""')}"`;
}

function downloadCsv(content, filename) {
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    a.click();
    URL.revokeObjectURL(a.href);
}

// ── Analytics Tab ─────────────────────────────────────────────────────────

function bindAnalyticsTab() {
    ['analyticsSurveyFilter', 'analyticsDateFrom', 'analyticsDateTo'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', renderAnalytics);
    });
    document.getElementById('btnExportAnalytics')?.addEventListener('click', exportAnalyticsCsv);
}

function getFilteredResponsesForAnalytics() {
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const from = document.getElementById('analyticsDateFrom')?.value || '';
    const to = document.getElementById('analyticsDateTo')?.value || '';

    return responses.filter(r => {
        const rSurveyId = String(r.surveyId || r.survey_id || '');
        if (surveyId && rSurveyId !== String(surveyId)) return false;
        const d = new Date(r.submittedAt || r.submitted_at);
        if (from && d < new Date(`${from}T00:00:00`)) return false;
        if (to && d > new Date(`${to}T23:59:59`)) return false;
        return true;
    });
}

function destroyCharts() {
    chartInstances.forEach(c => c.destroy());
    chartInstances = [];
}

async function renderAnalytics() {
    populateSurveyFilters('analyticsSurveyFilter');

    if (!responses.length) {
        try {
            await loadResponses();
        } catch (error) {
            showToast(error.message || 'Failed to load responses.', 'error');
        }
    }

    const filtered = getFilteredResponsesForAnalytics();
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const survey = surveys.find(s => String(s.id) === String(surveyId));

    const uniqueRespondents = new Set(filtered.map(r => r.respondentName || r.respondent_name)).size;

    document.getElementById('analyticsStatsRow').innerHTML = `
        <div class="analytics-stat-card analytics-stat-blue">
            <div class="analytics-stat-value">${surveys.length}</div>
            <div class="analytics-stat-label">Total Surveys Created</div>
        </div>
        <div class="analytics-stat-card analytics-stat-green">
            <div class="analytics-stat-value">${uniqueRespondents}</div>
            <div class="analytics-stat-label">Total Respondents</div>
        </div>
        <div class="analytics-stat-card analytics-stat-yellow">
            <div class="analytics-stat-value">${filtered.length}</div>
            <div class="analytics-stat-label">Total Responses Submitted</div>
        </div>
        <div class="analytics-stat-card analytics-stat-purple">
            <div class="analytics-stat-value">${responses.length}</div>
            <div class="analytics-stat-label">All-Time Responses</div>
        </div>
    `;

    const container = document.getElementById('analyticsQuestionsContainer');
    destroyCharts();

    if (!surveyId || !survey) {
        container.innerHTML = `<div class="survey-empty-state survey-empty-state-lg">
            <p><strong>Select a survey</strong></p>
            <p>Choose a survey from the dropdown to see how many Kabataan answered each choice, with bar and pie charts.</p>
        </div>`;
        return;
    }

    const questions = survey.questions || [];

    if (!questions.length) {
        container.innerHTML = '<p class="saf-table-empty">No questions in this survey.</p>';
        return;
    }

    container.innerHTML = questions.map((q, idx) => buildQuestionAnalyticsBlock(q, survey, filtered, idx)).join('');

    questions.forEach((q, idx) => {
        if (['radio', 'checkbox', 'dropdown'].includes(q.type)) {
            initChartsForQuestion(q, survey, filtered, idx);
        }
    });
}

function buildQuestionAnalyticsBlock(q, survey, responseRows, idx) {
    const surveyResponses = responseRows.filter(r => String(r.surveyId || r.survey_id) === String(survey.id));
    const withAnswer = surveyResponses.filter(r => {
        const a = r.answers?.[q.id];
        return a !== undefined && a !== null && a !== '' && !(Array.isArray(a) && !a.length);
    });

    const total = withAnswer.length;

    if (['radio', 'checkbox', 'dropdown'].includes(q.type)) {
        const counts = {};
        (q.options || []).forEach(opt => { counts[opt] = 0; });
        withAnswer.forEach(r => {
            let val = r.answers[q.id];
            if (q.type === 'checkbox' && Array.isArray(val)) {
                val.forEach(v => { counts[v] = (counts[v] || 0) + 1; });
            } else {
                counts[val] = (counts[val] || 0) + 1;
            }
        });

        const typeLabel = q.type === 'checkbox' ? 'Checkboxes' : (q.type === 'dropdown' ? 'Dropdown' : 'Multiple Choice');
        const rows = Object.entries(counts).map(([opt, count]) => {
            const pct = total ? Math.round((count / total) * 100) : 0;
            return `
                <div class="analytics-choice-row">
                    <div class="analytics-choice-label">${escapeHtml(opt)}</div>
                    <div class="analytics-choice-bar-wrap">
                        <div class="analytics-choice-bar" style="width:${pct}%"></div>
                    </div>
                    <div class="analytics-choice-stats"><span class="analytics-choice-count">${count}</span> <span class="analytics-choice-pct">(${pct}%)</span></div>
                </div>`;
        }).join('');

        return `
            <article class="analytics-question-block">
                <header class="analytics-question-head">
                    <div class="analytics-question-head-main">
                        <span class="analytics-q-badge">Q${idx + 1}</span>
                        <h4 class="analytics-question-title">${escapeHtml(q.label)}</h4>
                    </div>
                    <div class="analytics-question-meta">
                        <span class="analytics-meta-pill">${typeLabel}</span>
                        <span class="analytics-meta-text">${total} response${total === 1 ? '' : 's'}</span>
                    </div>
                </header>
                <div class="analytics-choices">${rows}</div>
                <div class="analytics-charts-row">
                    <div class="analytics-chart-box">
                        <h5>Bar Chart</h5>
                        <div class="analytics-chart-canvas-wrap"><canvas id="chartBar_${q.id}"></canvas></div>
                    </div>
                    <div class="analytics-chart-box">
                        <h5>Pie Chart — Response Distribution</h5>
                        <div class="analytics-chart-canvas-wrap"><canvas id="chartPie_${q.id}"></canvas></div>
                    </div>
                </div>
            </article>`;
    }

    const textAnswers = withAnswer.map(r => {
        let a = r.answers[q.id];
        if (Array.isArray(a)) a = a.join(', ');
        return `<div class="analytics-text-answer-item">${escapeHtml(String(a))}</div>`;
    }).join('') || '<p style="color:#9ca3af;font-size:14px;">No text responses yet.</p>';

    return `
        <article class="analytics-question-block">
            <header class="analytics-question-head">
                <div class="analytics-question-head-main">
                    <span class="analytics-q-badge">Q${idx + 1}</span>
                    <h4 class="analytics-question-title">${escapeHtml(q.label)}</h4>
                </div>
                <div class="analytics-question-meta">
                    <span class="analytics-meta-pill">Text</span>
                    <span class="analytics-meta-text">${total} response${total === 1 ? '' : 's'}</span>
                </div>
            </header>
            <div class="analytics-text-answers">${textAnswers}</div>
        </article>`;
}

function initChartsForQuestion(q, survey, responseRows, idx) {
    const surveyResponses = responseRows.filter(r => String(r.surveyId || r.survey_id) === String(survey.id));
    const labels = q.options || [];
    const counts = labels.map(opt => {
        let c = 0;
        surveyResponses.forEach(r => {
            const val = r.answers?.[q.id];
            if (q.type === 'checkbox' && Array.isArray(val)) {
                if (val.includes(opt)) c++;
            } else if (val === opt) {
                c++;
            }
        });
        return c;
    });

    const colors = ['#213F99', '#4f6fd6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'];

    const barCanvas = document.getElementById(`chartBar_${q.id}`);
    if (barCanvas) {
        chartInstances.push(new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Responses',
                    data: counts,
                    backgroundColor: colors.slice(0, labels.length),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        }));
    }

    const pieCanvas = document.getElementById(`chartPie_${q.id}`);
    if (pieCanvas) {
        chartInstances.push(new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors.slice(0, labels.length),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 14, font: { size: 11 } },
                    },
                },
            },
        }));
    }
}

function exportAnalyticsCsv() {
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const survey = surveys.find(s => String(s.id) === String(surveyId));
    if (!survey) {
        showToast('Select a survey first.', 'error');
        return;
    }
    const filteredRows = getFilteredResponsesForAnalytics().filter(r => String(r.surveyId || r.survey_id) === String(surveyId));
    const lines = ['Question,Answer Choice,Count,Percentage'];

    (survey.questions || []).forEach(q => {
        if (!['radio', 'checkbox', 'dropdown'].includes(q.type)) return;
        const counts = {};
        (q.options || []).forEach(o => { counts[o] = 0; });
        filteredRows.forEach(r => {
            let val = r.answers?.[q.id];
            if (q.type === 'checkbox' && Array.isArray(val)) {
                val.forEach(v => { counts[v] = (counts[v] || 0) + 1; });
            } else if (val) {
                counts[val] = (counts[val] || 0) + 1;
            }
        });
        const total = Object.values(counts).reduce((a, b) => a + b, 0) || 1;
        Object.entries(counts).forEach(([opt, count]) => {
            lines.push([
                csvCell(q.label),
                csvCell(opt),
                count,
                `${Math.round((count / total) * 100)}%`,
            ].join(','));
        });
    });

    downloadCsv(lines.join('\n'), `${committee}-survey-analytics.csv`);
}
