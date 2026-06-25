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

function getCurrentYear() {
    return new Date().getFullYear();
}

function getYearDefaultDates(year = getCurrentYear()) {
    return {
        open: `${year}-01-01`,
        close: `${year}-12-31`,
    };
}

function resolveSkTermLabel(year) {
    const start = 2023 + Math.floor(Math.max(0, year - 2023) / 3) * 3;
    return `${start}-${start + 2}`;
}

function getSurveyYear(survey) {
    if (survey?.survey_year) return Number(survey.survey_year);
    const date = survey?.openDate || survey?.open_date;
    if (!date) return getCurrentYear();
    return new Date(date).getFullYear();
}

function populateYearTermFilters() {
    const years = [...new Set(surveys.map(getSurveyYear))].sort((a, b) => b - a);
    const currentYear = getCurrentYear();
    if (!years.includes(currentYear)) years.unshift(currentYear);

    const terms = [...new Set(years.map(resolveSkTermLabel))].sort().reverse();

    ['formsYearFilter', 'responsesYearFilter'].forEach((id) => {
        const sel = document.getElementById(id);
        if (!sel) return;
        const current = sel.value;
        sel.innerHTML = '<option value="">All Years</option>'
            + years.map((y) => `<option value="${y}">${y}</option>`).join('');
        if (current) sel.value = current;
    });

    ['formsTermFilter', 'responsesTermFilter'].forEach((id) => {
        const sel = document.getElementById(id);
        if (!sel) return;
        const current = sel.value;
        sel.innerHTML = '<option value="">All Terms</option>'
            + terms.map((t) => `<option value="${t}">SK Term ${t}</option>`).join('');
        if (current) sel.value = current;
    });
}

function passesYearTermFilter(itemYear, prefix) {
    const yearFilter = document.getElementById(`${prefix}YearFilter`)?.value || '';
    const termFilter = document.getElementById(`${prefix}TermFilter`)?.value || '';
    if (yearFilter && String(itemYear) !== String(yearFilter)) return false;
    if (termFilter && resolveSkTermLabel(itemYear) !== termFilter) return false;
    return true;
}

function hasSurveyForYear(year) {
    return surveys.some((s) => getSurveyYear(s) === year);
}

function updateCreateButtonState() {
    const btn = document.getElementById('btnCreateSurvey');
    if (!btn) return;
    const exists = hasSurveyForYear(getCurrentYear());
    btn.disabled = exists;
    btn.title = exists
        ? `A survey form for ${getCurrentYear()} already exists. Only one survey per year is allowed.`
        : '';
    btn.style.opacity = exists ? '0.55' : '';
    btn.style.cursor = exists ? 'not-allowed' : '';
}

function getResponseYear(response) {
    const survey = response.survey
        || surveys.find((s) => String(s.id) === String(response.surveyId || response.survey_id));
    if (survey) return getSurveyYear(survey);
    const submitted = response.submittedAt || response.submitted_at;
    return submitted ? new Date(submitted).getFullYear() : getCurrentYear();
}

document.addEventListener('DOMContentLoaded', async () => {
    if (typeof showLoading === 'function') showLoading('Loading surveys');

    const page = getPageProgramConfig();
    committee = document.body.dataset.committee || page.committee || 'environmental';
    activeTab = document.body.dataset.surveyTab || page.activeTab || 'forms';

    bindFormsTab();
    bindResultsTab();
    bindAnalyticsTab();

    try {
        await loadCommitteeContext();
        await loadSurveys();
        populateYearTermFilters();
        updateCreateButtonState();
        if (activeTab === 'results' || activeTab === 'analytics') {
            await loadResponses();
        }
    } catch (error) {
        showToast(error.message || 'Failed to load survey data.', 'error');
    } finally {
        if (typeof hideLoading === 'function') hideLoading();
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

function formatBarangay(value) {
    if (value === null || value === undefined || value === '') return '—';
    if (typeof value === 'object') {
        return String(value.name || value.barangay_name || '—');
    }
    return String(value);
}

function normalizeAnswerValue(value) {
    if (value === null || value === undefined || value === '') return value;
    if (typeof value === 'string') {
        const trimmed = value.trim();
        if (trimmed.startsWith('[') || trimmed.startsWith('{')) {
            try {
                const parsed = JSON.parse(trimmed);
                return parsed;
            } catch {
                return value;
            }
        }
    }
    return value;
}

function getResponseAnswer(response, questionId) {
    const raw = response.answers?.[questionId] ?? response.answers?.[String(questionId)];
    return normalizeAnswerValue(raw);
}

function getAutoSelectedSurvey() {
    if (!surveys.length) return null;
    return surveys[0];
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

function getAutoProgram(selectedId = null) {
    const programs = committeeContext.programs || [];
    if (!programs.length) return null;

    if (selectedId) {
        const matched = programs.find((program) => String(program.id) === String(selectedId));
        if (matched) return matched;
    }

    return programs[0];
}

function renderAutoProgram(selectedId = null) {
    const hiddenEl = document.getElementById('surveyActivity');
    const nameEl = document.getElementById('surveyProgramName');
    if (!hiddenEl || !nameEl) return;

    const program = getAutoProgram(selectedId);

    if (!program) {
        hiddenEl.value = '';
        nameEl.textContent = 'No ABYIP program available';
        return;
    }

    hiddenEl.value = String(program.id);
    nameEl.textContent = program.program_name;
}

// ── Forms Tab ─────────────────────────────────────────────────────────────

function bindFormsTab() {
    document.getElementById('btnCreateSurvey')?.addEventListener('click', () => openSurveyModal());
    document.getElementById('surveyFormModalClose')?.addEventListener('click', closeSurveyModal);
    document.getElementById('surveyFormCancel')?.addEventListener('click', closeSurveyModal);
    document.getElementById('surveyFormSave')?.addEventListener('click', saveSurveyForm);
    document.getElementById('formsSearch')?.addEventListener('input', renderFormsTable);
    ['formsYearFilter', 'formsTermFilter'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', renderFormsTable);
    });
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
}

function openSurveyModal(survey) {
    if (!survey && hasSurveyForYear(getCurrentYear())) {
        showToast(`A survey form for ${getCurrentYear()} already exists. Only one per year is allowed.`, 'error');
        return;
    }

    editingSurveyId = survey?.id || null;
    renderAutoProgram(survey?.abyip_program_id || survey?.abyipProgramId);

    document.getElementById('surveyFormModalTitle').textContent = survey ? 'Edit Survey Form' : 'Create Survey Form';
    document.getElementById('surveyDescription').value = survey?.announcement || survey?.description || '';

    const defaults = getYearDefaultDates();
    document.getElementById('surveyOpenDate').value = survey?.openDate || survey?.open_date || defaults.open;
    document.getElementById('surveyCloseDate').value = survey?.closeDate || survey?.close_date || defaults.close;
    document.getElementById('surveyStatus').value = survey?.status === 'closed' ? 'closed' : 'open';

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
        showToast('No ABYIP program detected for this committee. Upload ABYIP first.', 'error');
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
        status: document.getElementById('surveyStatus')?.value || 'open',
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
        populateYearTermFilters();
        updateCreateButtonState();
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

    rows = rows.filter((s) => passesYearTermFilter(getSurveyYear(s), 'forms'));

    if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="saf-table-empty">
            <div class="survey-empty-state">
                <p><strong>No survey forms yet</strong></p>
                <p>Create questions for <em>${escapeHtml(getConfig().title)}</em>. Kabataan responses appear under Survey Response and Survey Analytics.</p>
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
                populateYearTermFilters();
                updateCreateButtonState();
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
        case 'open': return 'schol-pill-approved';
        case 'closed': return 'schol-pill-rejected';
        default: return 'schol-pill-rejected';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'open': return 'Open';
        case 'closed': return 'Closed';
        case 'scheduled': return 'Open';
        default: return 'Closed';
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
    ['responsesYearFilter', 'responsesTermFilter', 'resultsDateFrom', 'resultsDateTo', 'resultsSearch'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', renderResultsTable);
        document.getElementById(id)?.addEventListener('input', renderResultsTable);
    });
    document.getElementById('btnExportResults')?.addEventListener('click', exportResultsCsv);
    document.getElementById('viewResponseClose')?.addEventListener('click', () => {
        document.getElementById('viewResponseModal').style.display = 'none';
    });
    document.getElementById('viewResponseMaximize')?.addEventListener('click', toggleViewResponseMaximize);
}

function filterResponses() {
    const from = document.getElementById('resultsDateFrom')?.value || '';
    const to = document.getElementById('resultsDateTo')?.value || '';
    const search = (document.getElementById('resultsSearch')?.value || '').toLowerCase();

    return responses.filter((r) => {
        if (!passesYearTermFilter(getResponseYear(r), 'responses')) return false;
        const d = new Date(r.submittedAt || r.submitted_at);
        if (from && d < new Date(`${from}T00:00:00`)) return false;
        if (to && d > new Date(`${to}T23:59:59`)) return false;
        if (search) {
            const name = (r.respondentName || r.respondent_name || '').toLowerCase();
            const brgy = formatBarangay(r.barangay).toLowerCase();
            if (!name.includes(search) && !brgy.includes(search)) return false;
        }
        return true;
    });
}

async function renderResultsTable() {
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
        tbody.innerHTML = '<tr><td colspan="5" class="saf-table-empty">No survey responses yet.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map((r) => {
        const survey = r.survey || surveys.find(s => String(s.id) === String(r.surveyId || r.survey_id));
        const date = formatSurveyDate(r.submittedAt || r.submitted_at);
        const answerPreview = getResponseAnswerPreview(r, survey);

        return `
            <tr>
                <td class="survey-col-title" data-label="Full Name">
                    <div class="survey-cell-title">${escapeHtml(r.respondentName || r.respondent_name)}</div>
                </td>
                <td data-label="Barangay">${escapeHtml(formatBarangay(r.barangay))}</td>
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
    let ans = getResponseAnswer(response, q.id);
    if (ans === undefined || ans === null || ans === '') return '—';
    if (Array.isArray(ans)) ans = ans.join(', ');
    return String(ans);
}

function renderResponseModalContent(response, survey) {
    const body = document.getElementById('viewResponseBody');
    const answersHtml = (survey.questions || []).map((q, i) => {
        let ans = getResponseAnswer(response, q.id);
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
                <div><strong>Barangay:</strong> ${escapeHtml(formatBarangay(response.barangay))}</div>
                <div><strong>Date Submitted:</strong> ${formatSurveyDate(response.submittedAt || response.submitted_at)}</div>
            </div>
        </div>
        ${answersHtml}`;
}

function openResponseModal(response, survey) {
    renderResponseModalContent(response, survey);
    document.getElementById('viewResponseModal').style.display = 'flex';
}

function toggleViewResponseMaximize() {
    const modal = document.getElementById('viewResponseModal');
    const box = document.getElementById('viewResponseBox');
    const btn = document.getElementById('viewResponseMaximize');

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
            let ans = getResponseAnswer(r, q.id);
            if (Array.isArray(ans)) ans = ans.join('; ');
            lines.push([
                csvCell(r.respondentName || r.respondent_name),
                csvCell(formatBarangay(r.barangay)),
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
    ['analyticsDateFrom', 'analyticsDateTo'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', renderAnalytics);
    });
    document.getElementById('btnExportAnalytics')?.addEventListener('click', exportAnalyticsCsv);
}

function getFilteredResponsesForAnalytics(surveyId = null) {
    const activeSurveyId = surveyId ?? getAutoSelectedSurvey()?.id;
    const from = document.getElementById('analyticsDateFrom')?.value || '';
    const to = document.getElementById('analyticsDateTo')?.value || '';

    return responses.filter(r => {
        const rSurveyId = String(r.surveyId || r.survey_id || '');
        if (activeSurveyId && rSurveyId !== String(activeSurveyId)) return false;
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
    if (!responses.length) {
        try {
            await loadResponses();
        } catch (error) {
            showToast(error.message || 'Failed to load responses.', 'error');
        }
    }

    const survey = getAutoSelectedSurvey();
    const programDisplay = document.getElementById('analyticsProgramDisplay');
    const container = document.getElementById('analyticsQuestionsContainer');
    destroyCharts();

    if (!survey) {
        if (programDisplay) {
            programDisplay.textContent = 'No survey form created for this program yet.';
        }
        container.innerHTML = `<div class="survey-empty-state survey-empty-state-lg">
            <p><strong>No survey available</strong></p>
            <p>Create a survey form first. Kabataan responses will appear here automatically once submitted.</p>
        </div>`;
        return;
    }

    const surveyTitle = survey.title || survey.program_name || 'Program Survey';
    if (programDisplay) {
        programDisplay.textContent = surveyTitle;
    }

    const filtered = getFilteredResponsesForAnalytics(survey.id);
    const questions = survey.questions || [];

    if (!questions.length) {
        container.innerHTML = '<p class="saf-table-empty">No questions in this survey.</p>';
        return;
    }

    container.innerHTML = questions.map((q, idx) => buildQuestionAnalyticsBlock(q, survey, filtered, idx)).join('');

    requestAnimationFrame(() => {
        questions.forEach((q, idx) => {
            initChartsForQuestion(q, survey, filtered, idx);
        });
    });
}

const CHOICE_QUESTION_TYPES = ['radio', 'checkbox', 'dropdown'];

function questionTypeLabel(type) {
    const map = {
        radio: 'Multiple Choice',
        checkbox: 'Checkboxes',
        dropdown: 'Dropdown',
        text: 'Short Answer',
        paragraph: 'Paragraph',
        number: 'Number',
        date: 'Date',
    };
    return map[type] || 'Response';
}

function getResponsesWithAnswer(survey, responseRows, questionId) {
    const surveyResponses = responseRows.filter(r => String(r.surveyId || r.survey_id) === String(survey.id));
    return surveyResponses.filter(r => {
        const a = getResponseAnswer(r, questionId);
        return a !== undefined && a !== null && a !== '' && !(Array.isArray(a) && !a.length);
    });
}

function buildAnswerCounts(q, withAnswer) {
    if (CHOICE_QUESTION_TYPES.includes(q.type)) {
        const counts = {};
        (q.options || []).forEach(opt => { counts[opt] = 0; });
        withAnswer.forEach(r => {
            let val = getResponseAnswer(r, q.id);
            if (q.type === 'checkbox' && Array.isArray(val)) {
                val.forEach(v => { counts[v] = (counts[v] || 0) + 1; });
            } else {
                counts[val] = (counts[val] || 0) + 1;
            }
        });
        return counts;
    }

    const counts = {};
    withAnswer.forEach(r => {
        let val = getResponseAnswer(r, q.id);
        if (Array.isArray(val)) val = val.join(', ');
        const key = String(val ?? '').trim() || '(empty)';
        counts[key] = (counts[key] || 0) + 1;
    });
    return counts;
}

function buildAnalyticsChartsHtml(chartKey, total) {
    const chartEmptyNote = total === 0
        ? '<p class="analytics-chart-empty">No responses yet for this question.</p>'
        : '';

    return `
        <div class="analytics-charts-row">
            <div class="analytics-chart-box">
                <h5>Bar Chart</h5>
                <div class="analytics-chart-canvas-wrap">
                    ${chartEmptyNote}
                    <canvas id="chartBar_${chartKey}"></canvas>
                </div>
            </div>
            <div class="analytics-chart-box">
                <h5>Pie Chart — Response Distribution</h5>
                <div class="analytics-chart-canvas-wrap">
                    ${chartEmptyNote}
                    <canvas id="chartPie_${chartKey}"></canvas>
                </div>
            </div>
        </div>`;
}

function buildQuestionAnalyticsBlock(q, survey, responseRows, idx) {
    const withAnswer = getResponsesWithAnswer(survey, responseRows, q.id);
    const total = withAnswer.length;
    const chartKey = `q${q.id}`;
    const counts = buildAnswerCounts(q, withAnswer);
    const typeLabel = questionTypeLabel(q.type);

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

    const detailSection = CHOICE_QUESTION_TYPES.includes(q.type)
        ? `<div class="analytics-choices">${rows}</div>`
        : `<div class="analytics-choices">${rows || '<p style="color:#9ca3af;font-size:14px;">No responses yet.</p>'}</div>`;

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
            ${detailSection}
            ${buildAnalyticsChartsHtml(chartKey, total)}
        </article>`;
}

function initChartsForQuestion(q, survey, responseRows, idx) {
    const withAnswer = getResponsesWithAnswer(survey, responseRows, q.id);
    const counts = buildAnswerCounts(q, withAnswer);
    const labels = Object.keys(counts);
    const countValues = labels.map(label => counts[label] || 0);
    const chartKey = `q${q.id}`;

    const totalCount = countValues.reduce((sum, count) => sum + count, 0);
    if (totalCount === 0) return;

    const colors = ['#213F99', '#4f6fd6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'];
    const chartHeight = Math.max(260, labels.length * 48);

    const barCanvas = document.getElementById(`chartBar_${chartKey}`);
    const barWrap = barCanvas?.closest('.analytics-chart-canvas-wrap');
    if (barWrap) {
        barWrap.style.height = `${chartHeight}px`;
        barWrap.querySelector('.analytics-chart-empty')?.remove();
    }
    if (barCanvas) {
        chartInstances.push(new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Responses',
                    data: countValues,
                    backgroundColor: colors.slice(0, labels.length),
                    borderRadius: 6,
                    maxBarThickness: 48,
                }],
            },
            options: {
                indexAxis: labels.length > 6 ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const value = labels.length > 6 ? ctx.parsed.x : ctx.parsed.y;
                                return `${value} response${value === 1 ? '' : 's'}`;
                            },
                        },
                    },
                },
                scales: labels.length > 6 ? {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#eef2f7' },
                    },
                    y: {
                        ticks: { autoSkip: false, font: { size: 12 } },
                        grid: { display: false },
                    },
                } : {
                    x: {
                        ticks: { autoSkip: false, font: { size: 12 } },
                        grid: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#eef2f7' },
                    },
                },
            },
        }));
    }

    const pieCanvas = document.getElementById(`chartPie_${chartKey}`);
    const pieWrap = pieCanvas?.closest('.analytics-chart-canvas-wrap');
    if (pieWrap) {
        pieWrap.style.height = '260px';
        pieWrap.querySelector('.analytics-chart-empty')?.remove();
    }
    if (pieCanvas) {
        chartInstances.push(new Chart(pieCanvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: countValues,
                    backgroundColor: colors.slice(0, labels.length),
                    borderColor: '#fff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '42%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 16,
                            font: { size: 12 },
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const value = ctx.parsed || 0;
                                const pct = totalCount ? Math.round((value / totalCount) * 100) : 0;
                                return `${ctx.label}: ${value} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        }));
    }
}

function exportAnalyticsCsv() {
    const survey = getAutoSelectedSurvey();
    if (!survey) {
        showToast('No survey available to export.', 'error');
        return;
    }
    const filteredRows = getFilteredResponsesForAnalytics(survey.id);
    const lines = ['Question,Answer Choice,Count,Percentage'];

    (survey.questions || []).forEach(q => {
        if (!['radio', 'checkbox', 'dropdown'].includes(q.type)) return;
        const counts = {};
        (q.options || []).forEach(o => { counts[o] = 0; });
        filteredRows.forEach(r => {
            let val = getResponseAnswer(r, q.id);
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
