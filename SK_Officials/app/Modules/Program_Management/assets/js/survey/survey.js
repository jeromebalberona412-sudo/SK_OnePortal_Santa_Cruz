/**
 * Program Management — Survey (Forms, Results, Analytics)
 * Committee/program context from #surveyProgramConfig (server) + localStorage per program key.
 */
import Chart from 'chart.js/auto';

const COMMITTEE_CONFIG_FALLBACK = {
    environmental: {
        title: 'Environmental Protection',
        skHead: 'Juan dela Cruz',
        activities: ['Clean-Up Drive', 'Payroll for Laborer', 'Tree Planting'],
        description: 'Create surveys for environmental programs and review Kabataan feedback.',
    },
    disaster: {
        title: 'Disaster Risk Reduction and Resiliency',
        skHead: 'Carlo Reyes',
        activities: [
            'Training on Disaster Preparedness for Youth Volunteer Groups',
            'Distribution of Relief Goods for KK Members',
        ],
        description: 'Manage disaster preparedness surveys and youth volunteer responses.',
    },
    livelihood: {
        title: 'Youth Employment and Livelihood',
        skHead: 'Ana Villanueva',
        activities: ['Livelihood Training', 'Food and Other Supplies'],
        description: 'Track livelihood program surveys and participant feedback.',
    },
    medicines: {
        title: 'Medicines',
        skHead: 'Jose Mendoza',
        activities: ['Medicines / Medical Equipment'],
        description: 'Manage health-related surveys for medicine distribution programs.',
    },
    antidrug: {
        title: 'Anti-Drug and Peace and Order',
        skHead: 'Ramon Garcia',
        activities: ['Orientation for Anti-Drug and Physical Abuse', 'Foods and Accommodations'],
        description: 'Collect feedback from anti-drug and peace and order orientations.',
    },
    gender: {
        title: 'Gender Sensitivity',
        skHead: 'Liza Torres',
        activities: ['Orientation on GAD and VAWC', 'Foods and Accommodations'],
        description: 'Review GAD and VAWC orientation survey responses from youth.',
    },
    feeding: {
        title: 'Feeding Program for KK Members',
        skHead: 'Kristine Bautista',
        activities: [
            'Improve health and physique of children',
            'Youth and Children in the vicinity of Barangay',
        ],
        description: 'Monitor feeding program surveys and community feedback.',
    },
    others: {
        title: 'Other Programs',
        skHead: 'Patricia Flores',
        activities: [
            'Katipunan ng Kabataan (KK) General Assembly',
            'Barangay Day Celebration',
            'Youth Week',
        ],
        description: 'Manage surveys for KK assemblies and community celebrations.',
    },
};

const QUESTION_TYPES = [
    { value: 'radio', label: 'Multiple Choice' },
    { value: 'checkbox', label: 'Checkboxes' },
    { value: 'text', label: 'Short Answer' },
    { value: 'paragraph', label: 'Paragraph' },
    { value: 'number', label: 'Number' },
];

let committee = 'environmental';
let activeTab = 'forms';
let editingSurveyId = null;
let chartInstances = [];

function getPageProgramConfig() {
    const el = document.getElementById('surveyProgramConfig');
    if (!el) return {};
    try {
        return JSON.parse(el.textContent || '{}');
    } catch {
        return {};
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const page = getPageProgramConfig();
    committee = document.body.dataset.committee || page.committee || 'environmental';
    activeTab = document.body.dataset.surveyTab || page.activeTab || 'forms';

    seedSampleData();
    bindFormsTab();
    bindResultsTab();
    bindAnalyticsTab();

    if (activeTab === 'forms') renderFormsTable();
    if (activeTab === 'results') renderResultsTable();
    if (activeTab === 'analytics') renderAnalytics();
});

function storageKey(type) {
    return `sk_survey_${committee}_${type}`;
}

function loadSurveys() {
    try {
        return JSON.parse(localStorage.getItem(storageKey('forms')) || '[]');
    } catch {
        return [];
    }
}

function saveSurveys(list) {
    localStorage.setItem(storageKey('forms'), JSON.stringify(list));
}

function loadResponses() {
    try {
        return JSON.parse(localStorage.getItem(storageKey('responses')) || '[]');
    } catch {
        return [];
    }
}

function saveResponses(list) {
    localStorage.setItem(storageKey('responses'), JSON.stringify(list));
}

function uid(prefix) {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
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

function getConfig() {
    const page = getPageProgramConfig();
    const fallback = COMMITTEE_CONFIG_FALLBACK[committee] || COMMITTEE_CONFIG_FALLBACK.environmental;
    if (page.title) {
        return {
            title: page.title,
            skHead: page.skHead || fallback.skHead,
            activities: page.activities?.length ? page.activities : fallback.activities,
            description: page.description || fallback.description,
        };
    }
    return fallback;
}

function countResponsesForSurvey(surveyId) {
    return loadResponses().filter(r => r.surveyId === surveyId).length;
}

function seedSampleData() {
    const seedKey = storageKey('seeded_v3_scheduling');
    const existing = loadSurveys();
    const isOldAutoSample = existing.length === 1 && (
        (existing[0].title || '').includes('Youth Feedback Survey') ||
        (existing[0].questions || []).length > 1
    );

    if (localStorage.getItem(seedKey) && !isOldAutoSample) return;
    if (existing.length && !isOldAutoSample) {
        localStorage.setItem(seedKey, '1');
        return;
    }

    const cfg = getConfig();
    const activity = cfg.activities[0] || 'Program Activity';
    const surveyId = isOldAutoSample ? existing[0].id : uid('srv');
    const questionId = 'q_attendance';

    // Set dates for sample survey (open for 30 days)
    const now = new Date();
    const openDate = new Date(now);
    openDate.setDate(openDate.getDate() - 7); // Opened 7 days ago
    const closeDate = new Date(now);
    closeDate.setDate(closeDate.getDate() + 23); // Closes in 23 days

    const sampleSurvey = {
        id: surveyId,
        title: `${activity} — Attendance Survey`,
        activity,
        description: 'Pakit sagot kung nakadalo ka sa programang ito. Isang tanong lang — Oo o Hindi.',
        openDate: openDate.toISOString().split('T')[0],
        closeDate: closeDate.toISOString().split('T')[0],
        status: 'open',
        questions: [
            {
                id: questionId,
                label: 'Nakadalo ka ba sa programang ito?',
                type: 'radio',
                options: ['Oo', 'Hindi'],
                required: true,
            },
        ],
        createdAt: isOldAutoSample && existing[0]?.createdAt
            ? existing[0].createdAt
            : new Date().toISOString(),
        updatedAt: new Date().toISOString(),
    };

    const respondents = [
        { name: 'Maria Santos', barangay: 'Calios' },
        { name: 'Juan Dela Cruz', barangay: 'Poblacion' },
        { name: 'Ana Villanueva', barangay: 'Calios' },
        { name: 'Carlo Reyes', barangay: 'Santo Angel' },
        { name: 'Liza Torres', barangay: 'Calios' },
    ];

    const attendanceAnswers = ['Oo', 'Hindi', 'Oo', 'Oo', 'Hindi'];

    const responses = respondents.map((r, i) => {
        const daysAgo = i * 3;
        const d = new Date();
        d.setDate(d.getDate() - daysAgo);
        return {
            id: uid('resp'),
            surveyId,
            respondentName: r.name,
            barangay: r.barangay,
            answers: {
                [questionId]: attendanceAnswers[i],
            },
            submittedAt: d.toISOString(),
        };
    });

    saveSurveys([sampleSurvey]);
    saveResponses(responses);
    localStorage.setItem(seedKey, '1');
    localStorage.removeItem(storageKey('seeded_v1'));
    localStorage.removeItem(storageKey('seeded_v2_attendance'));
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
    
    // Character count for survey description
    const surveyDesc = document.getElementById('surveyDescription');
    const surveyDescCount = document.getElementById('surveyDescCount');
    if (surveyDesc && surveyDescCount) {
        surveyDesc.addEventListener('input', () => {
            surveyDescCount.textContent = surveyDesc.value.length;
        });
    }
    
    // Set min date for date pickers to today
    const today = new Date().toISOString().split('T')[0];
    const openDateInput = document.getElementById('surveyOpenDate');
    const closeDateInput = document.getElementById('surveyCloseDate');
    if (openDateInput) openDateInput.setAttribute('min', today);
    if (closeDateInput) closeDateInput.setAttribute('min', today);
}

function openSurveyModal(survey) {
    editingSurveyId = survey?.id || null;
    const cfg = getConfig();
    const activityEl = document.getElementById('surveyActivity');
    if (activityEl) {
        activityEl.innerHTML = cfg.activities.map(a => `<option value="${escapeHtml(a)}">${escapeHtml(a)}</option>`).join('');
    }

    document.getElementById('surveyFormModalTitle').textContent = survey ? 'Edit Survey Form' : 'Create Survey Form';
    document.getElementById('surveyTitle').value = survey?.title || '';
    document.getElementById('surveyActivity').value = survey?.activity || cfg.activities[0] || '';
    document.getElementById('surveyDescription').value = survey?.description || '';
    document.getElementById('surveyOpenDate').value = survey?.openDate || '';
    document.getElementById('surveyCloseDate').value = survey?.closeDate || '';
    document.getElementById('surveyStatus').value = survey?.status || 'scheduled';
    
    // Update character count
    const surveyDescCount = document.getElementById('surveyDescCount');
    if (surveyDescCount) {
        surveyDescCount.textContent = (survey?.description || '').length;
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
    if (window.GFormBuilder) window.GFormBuilder.reset();
}

function saveSurveyForm() {
    const title = document.getElementById('surveyTitle')?.value?.trim();
    if (!title) {
        showToast('Survey title is required.', 'error');
        return;
    }
    const questions = (window.GFormBuilder?.getQuestions() || []).filter(q => (q.label || '').trim());
    if (!questions.length) {
        showToast('Add at least one question with a label.', 'error');
        return;
    }
    
    const openDate = document.getElementById('surveyOpenDate')?.value || '';
    const closeDate = document.getElementById('surveyCloseDate')?.value || '';
    
    // Validate dates
    if (openDate && closeDate && new Date(closeDate) <= new Date(openDate)) {
        showToast('Close date must be later than open date.', 'error');
        return;
    }
    
    if (!openDate || !closeDate) {
        showToast('Open date and close date are required.', 'error');
        return;
    }

    // Auto-update status based on dates
    let status = document.getElementById('surveyStatus')?.value || 'pending';
    status = autoUpdateStatus(status, openDate, closeDate);

    const payload = {
        id: editingSurveyId || uid('srv'),
        title,
        activity: document.getElementById('surveyActivity')?.value || '',
        description: document.getElementById('surveyDescription')?.value?.trim() || '',
        openDate,
        closeDate,
        status,
        questions,
        createdAt: editingSurveyId
            ? (loadSurveys().find(s => s.id === editingSurveyId)?.createdAt || new Date().toISOString())
            : new Date().toISOString(),
        updatedAt: new Date().toISOString(),
    };

    let surveys = loadSurveys();
    const idx = surveys.findIndex(s => s.id === payload.id);
    if (idx >= 0) surveys[idx] = payload;
    else surveys.unshift(payload);
    saveSurveys(surveys);
    closeSurveyModal();
    renderFormsTable();
    showToast('Survey saved successfully.');
}

function autoUpdateStatus(currentStatus, openDate, closeDate) {
    const now = new Date();
    const open = new Date(openDate);
    const close = new Date(closeDate);
    
    // Auto-determine status based on dates
    if (now < open) {
        return 'scheduled';
    } else if (now >= open && now <= close) {
        return 'open';
    } else {
        return 'closed';
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
    let surveys = loadSurveys();
    if (q) {
        surveys = surveys.filter(s =>
            (s.title || '').toLowerCase().includes(q) ||
            (s.activity || '').toLowerCase().includes(q)
        );
    }

    if (!surveys.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="saf-table-empty">
            <div class="survey-empty-state">
                <p><strong>No survey forms yet</strong></p>
                <p>Create questions for <em>${escapeHtml(getConfig().title)}</em> activities. Kabataan responses appear under Survey Results and Survey Analytics.</p>
            </div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = surveys.map(s => {
        const respCount = countResponsesForSurvey(s.id);
        const statusCls = getStatusClass(s.status);
        const statusLabel = getStatusLabel(s.status);
        const qCount = (s.questions || []).length;
        const openDateDisplay = s.openDate ? formatDate(s.openDate) : '—';
        const closeDateDisplay = s.closeDate ? formatDate(s.closeDate) : '—';
        return `
            <tr>
                <td class="survey-col-title">
                    <div class="survey-cell-title">${escapeHtml(s.title)}</div>
                </td>
                <td data-label="Activity">${escapeHtml(s.activity || '—')}</td>
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
            const s = loadSurveys().find(x => x.id === btn.dataset.viewSurvey);
            if (s) openViewSurveyModal(s);
        });
    });
    tbody.querySelectorAll('[data-edit-survey]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = loadSurveys().find(x => x.id === btn.dataset.editSurvey);
            if (s) openSurveyModal(s);
        });
    });
    tbody.querySelectorAll('[data-delete-survey]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!confirm('Delete this survey and all its responses?')) return;
            const id = btn.dataset.deleteSurvey;
            saveSurveys(loadSurveys().filter(s => s.id !== id));
            saveResponses(loadResponses().filter(r => r.surveyId !== id));
            renderFormsTable();
            showToast('Survey deleted.');
        });
    });
}

function getStatusClass(status) {
    switch(status) {
        case 'scheduled': return 'schol-pill-scheduled';
        case 'open': return 'schol-pill-approved';
        case 'closed': return 'schol-pill-rejected';
        default: return 'schol-pill-rejected';
    }
}

function getStatusLabel(status) {
    switch(status) {
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
        day: 'numeric'
    });
}

function openViewSurveyModal(survey) {
    const body = document.getElementById('viewSurveyBody');
    const respCount = countResponsesForSurvey(survey.id);
    body.innerHTML = `
        <div class="gform-preview-header">
            <h4>${escapeHtml(survey.title)}</h4>
            <div class="gform-preview-info">
                <div><strong>Activity:</strong> ${escapeHtml(survey.activity || '—')}</div>
                <div><strong>Open Date:</strong> ${formatDate(survey.openDate)}</div>
                <div><strong>Close Date:</strong> ${formatDate(survey.closeDate)}</div>
                <div><strong>Status:</strong> ${getStatusLabel(survey.status)}</div>
                <div><strong>Responses:</strong> ${respCount}</div>
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
    sel.innerHTML = first + loadSurveys().map(s =>
        `<option value="${s.id}">${escapeHtml(s.title)}</option>`
    ).join('');
    if (current) sel.value = current;
}

function filterResponses() {
    const surveyId = document.getElementById('resultsSurveyFilter')?.value || '';
    const from = document.getElementById('resultsDateFrom')?.value || '';
    const to = document.getElementById('resultsDateTo')?.value || '';
    const search = (document.getElementById('resultsSearch')?.value || '').toLowerCase();

    return loadResponses().filter(r => {
        if (surveyId && r.surveyId !== surveyId) return false;
        const d = new Date(r.submittedAt);
        if (from && d < new Date(from + 'T00:00:00')) return false;
        if (to && d > new Date(to + 'T23:59:59')) return false;
        if (search) {
            const name = (r.respondentName || '').toLowerCase();
            const brgy = (r.barangay || '').toLowerCase();
            if (!name.includes(search) && !brgy.includes(search)) return false;
        }
        return true;
    });
}

function renderResultsTable() {
    populateSurveyFilters('resultsSurveyFilter');
    const tbody = document.getElementById('surveyResultsTableBody');
    if (!tbody) return;

    const surveys = loadSurveys();
    const rows = filterResponses();

    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">No survey responses yet.</td></tr>';
        return;
    }

    tbody.innerHTML = rows.map(r => {
        const survey = surveys.find(s => s.id === r.surveyId);
        const date = formatSurveyDate(r.submittedAt);
        const answerPreview = getResponseAnswerPreview(r, survey);
        return `
            <tr>
                <td class="survey-col-title">
                    <div class="survey-cell-title">${escapeHtml(r.respondentName)}</div>
                    <div class="survey-cell-meta">${escapeHtml(r.barangay || '—')}</div>
                </td>
                <td data-label="Survey">${escapeHtml(survey?.title || '—')}</td>
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
            const r = loadResponses().find(x => x.id === btn.dataset.viewResponse);
            const survey = loadSurveys().find(s => s.id === r?.surveyId);
            if (r && survey) openResponseModal(r, survey);
        });
    });
}

function getResponseAnswerPreview(response, survey) {
    const questions = survey?.questions || [];
    if (!questions.length) return '—';
    const q = questions[0];
    let ans = response.answers?.[q.id];
    if (Array.isArray(ans)) ans = ans.join(', ');
    if (ans === undefined || ans === null || ans === '') return '—';
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
            <h4>${escapeHtml(response.respondentName)}</h4>
            <div class="gform-preview-info">
                <div><strong>Survey:</strong> ${escapeHtml(survey.title)}</div>
                <div><strong>Barangay:</strong> ${escapeHtml(response.barangay || '—')}</div>
                <div><strong>Date Submitted:</strong> ${formatSurveyDate(response.submittedAt)}</div>
            </div>
        </div>
        ${answersHtml}`;
    document.getElementById('viewResponseModal').style.display = 'flex';
}

function exportResultsCsv() {
    const surveys = loadSurveys();
    const rows = filterResponses();
    if (!rows.length) {
        showToast('No data to export.', 'error');
        return;
    }
    const lines = ['Respondent,Barangay,Survey,Submitted At,Question,Answer'];
    rows.forEach(r => {
        const survey = surveys.find(s => s.id === r.surveyId);
        const date = new Date(r.submittedAt).toISOString();
        (survey?.questions || []).forEach(q => {
            let ans = r.answers?.[q.id];
            if (Array.isArray(ans)) ans = ans.join('; ');
            lines.push([
                csvCell(r.respondentName),
                csvCell(r.barangay),
                csvCell(survey?.title),
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
    ['analyticsSurveyFilter', 'analyticsDateFrom', 'analyticsDateTo', 'analyticsQuestionFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', renderAnalytics);
    });
    document.getElementById('btnExportAnalytics')?.addEventListener('click', exportAnalyticsCsv);
}

function getFilteredResponsesForAnalytics() {
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const from = document.getElementById('analyticsDateFrom')?.value || '';
    const to = document.getElementById('analyticsDateTo')?.value || '';

    return loadResponses().filter(r => {
        if (surveyId && r.surveyId !== surveyId) return false;
        const d = new Date(r.submittedAt);
        if (from && d < new Date(from + 'T00:00:00')) return false;
        if (to && d > new Date(to + 'T23:59:59')) return false;
        return true;
    });
}

function destroyCharts() {
    chartInstances.forEach(c => c.destroy());
    chartInstances = [];
}

function renderAnalytics() {
    populateSurveyFilters('analyticsSurveyFilter');

    const surveys = loadSurveys();
    const allResponses = loadResponses();
    const filtered = getFilteredResponsesForAnalytics();
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const survey = surveys.find(s => s.id === surveyId);

    const uniqueRespondents = new Set(filtered.map(r => r.respondentName)).size;

    document.getElementById('analyticsStatsRow').innerHTML = `
        <div class="survey-stat-card"><div class="stat-value">${surveys.length}</div><div class="stat-label">Total Surveys Created</div></div>
        <div class="survey-stat-card"><div class="stat-value">${uniqueRespondents}</div><div class="stat-label">Total Respondents</div></div>
        <div class="survey-stat-card"><div class="stat-value">${filtered.length}</div><div class="stat-label">Total Responses Submitted</div></div>
        <div class="survey-stat-card"><div class="stat-value">${allResponses.length}</div><div class="stat-label">All-Time Responses</div></div>
    `;

    const qFilter = document.getElementById('analyticsQuestionFilter');
    if (qFilter && survey) {
        const prev = qFilter.value;
        qFilter.innerHTML = '<option value="">All questions</option>' +
            (survey.questions || []).map((q, i) =>
                `<option value="${q.id}">${i + 1}. ${escapeHtml(q.label)}</option>`
            ).join('');
        qFilter.value = prev;
    }

    const container = document.getElementById('analyticsQuestionsContainer');
    destroyCharts();

    if (!surveyId || !survey) {
        container.innerHTML = `<div class="survey-empty-state survey-empty-state-lg">
            <p><strong>Select a survey</strong></p>
            <p>Choose a survey from the dropdown to see how many Kabataan answered each choice, with bar and pie charts.</p>
        </div>`;
        return;
    }

    const questionIdFilter = document.getElementById('analyticsQuestionFilter')?.value || '';
    let questions = survey.questions || [];
    if (questionIdFilter) questions = questions.filter(q => q.id === questionIdFilter);

    if (!questions.length) {
        container.innerHTML = '<p class="saf-table-empty">No questions in this survey.</p>';
        return;
    }

    container.innerHTML = questions.map((q, idx) => buildQuestionAnalyticsBlock(q, survey, filtered, idx)).join('');

    questions.forEach((q, idx) => {
        if (['radio', 'checkbox'].includes(q.type)) {
            initChartsForQuestion(q, survey, filtered, idx);
        }
    });
}

function buildQuestionAnalyticsBlock(q, survey, responses, idx) {
    const surveyResponses = responses.filter(r => r.surveyId === survey.id);
    const withAnswer = surveyResponses.filter(r => {
        const a = r.answers?.[q.id];
        return a !== undefined && a !== null && a !== '' && !(Array.isArray(a) && !a.length);
    });

    const total = withAnswer.length;

    if (['radio', 'checkbox'].includes(q.type)) {
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

        const rows = Object.entries(counts).map(([opt, count]) => {
            const pct = total ? Math.round((count / total) * 100) : 0;
            return `
                <div class="analytics-choice-row">
                    <div class="analytics-choice-label">${escapeHtml(opt)}</div>
                    <div class="analytics-choice-bar-wrap">
                        <div class="analytics-choice-bar" style="width:${pct}%"></div>
                    </div>
                    <div class="analytics-choice-stats">${count} (${pct}%)</div>
                </div>`;
        }).join('');

        return `
            <div class="analytics-question-block">
                <h4>Question ${idx + 1}: ${escapeHtml(q.label)}</h4>
                <div class="analytics-meta">Total responses: ${total} · Type: ${q.type === 'checkbox' ? 'Checkboxes' : 'Multiple Choice'}</div>
                ${rows}
                <div class="analytics-charts-row">
                    <div class="analytics-chart-box"><h5>Bar Chart</h5><canvas id="chartBar_${q.id}"></canvas></div>
                    <div class="analytics-chart-box"><h5>Pie Chart — Response Distribution</h5><canvas id="chartPie_${q.id}"></canvas></div>
                </div>
            </div>`;
    }

    const textAnswers = withAnswer.map(r => {
        let a = r.answers[q.id];
        if (Array.isArray(a)) a = a.join(', ');
        return `<div class="analytics-text-answer-item">${escapeHtml(String(a))}</div>`;
    }).join('') || '<p style="color:#9ca3af;font-size:14px;">No text responses yet.</p>';

    return `
        <div class="analytics-question-block">
            <h4>Question ${idx + 1}: ${escapeHtml(q.label)}</h4>
            <div class="analytics-meta">Total responses: ${total} · Type: Text</div>
            <div class="analytics-text-answers">${textAnswers}</div>
        </div>`;
}

function initChartsForQuestion(q, survey, responses, idx) {
    const surveyResponses = responses.filter(r => r.surveyId === survey.id);
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
            options: { responsive: true },
        }));
    }
}

function exportAnalyticsCsv() {
    const surveyId = document.getElementById('analyticsSurveyFilter')?.value || '';
    const survey = loadSurveys().find(s => s.id === surveyId);
    if (!survey) {
        showToast('Select a survey first.', 'error');
        return;
    }
    const responses = getFilteredResponsesForAnalytics().filter(r => r.surveyId === surveyId);
    const lines = ['Question,Answer Choice,Count,Percentage'];

    (survey.questions || []).forEach(q => {
        if (!['radio', 'checkbox'].includes(q.type)) return;
        const counts = {};
        (q.options || []).forEach(o => { counts[o] = 0; });
        responses.forEach(r => {
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
