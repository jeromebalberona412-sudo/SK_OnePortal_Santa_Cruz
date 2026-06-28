let schedulePrograms = [];
let programMeta = null;
let editingProgramId = null;
let pendingDeleteProgramId = null;
let reqGroupCounter = 0;
let usedSemesters = [];

function switchBuilderTab(tabId) {
    document.querySelectorAll('.sch-program-tab').forEach((btn) => {
        btn.classList.toggle('is-active', btn.dataset.schTab === tabId);
    });
    document.querySelectorAll('.sch-program-tab-panel').forEach((panel) => {
        const active = panel.dataset.schPanel === tabId;
        panel.hidden = !active;
        panel.classList.toggle('is-active', active);
    });
    if (tabId === 'preview') {
        renderInlineProgramPreview();
    }
    if (tabId === 'custom-questions' && window.SpfbFormBuilder?.renderQuestionList) {
        window.SpfbFormBuilder.renderQuestionList();
    }
}

function bindBuilderTabs() {
    document.querySelectorAll('.sch-program-tab').forEach((btn) => {
        btn.addEventListener('click', () => switchBuilderTab(btn.dataset.schTab));
    });
}

function getDefaultKkFields() {
    return window.ScholarshipSystemFields?.DEFAULT_KK_FIELDS || [];
}

function setApplicationTypeAvailability(renewalEnabled) {
    document.querySelectorAll('input[name="applicationType"]').forEach((radio) => {
        const isRenewalOption = radio.value === 'renewal_only' || radio.value === 'both';
        radio.disabled = isRenewalOption && !renewalEnabled;
        radio.closest('.schol-radio-label')?.classList.toggle('schol-radio-disabled', isRenewalOption && !renewalEnabled);
    });

    if (!renewalEnabled) {
        const current = getApplicationTypeValue();
        if (current === 'renewal_only' || current === 'both') {
            setApplicationTypeValue('new_only');
        }
    }
}

function getDefaultEligibility() {
    return programMeta?.default_eligibility || {
        youth_classifications: ['In School Youth'],
        youth_age_groups: [],
        education_levels: ['High School Level', 'High School Grad', 'College Level'],
    };
}

const SCHOLARSHIP_TARGET_LEVELS = {
    senior_high: {
        label: 'Senior High',
        education_levels: ['High School Level'],
    },
    college: {
        label: 'College',
        education_levels: ['College Level'],
    },
};

function getScholarshipTargetLevels() {
    return Array.from(document.querySelectorAll('input[name="scholarshipTargetLevel"]:checked'))
        .map((input) => input.value)
        .filter((value) => value === 'senior_high' || value === 'college');
}

function updateScholarshipBothButtonState() {
    const bothBtn = document.getElementById('schLevelBothBtn');
    if (!bothBtn) return;

    const levels = getScholarshipTargetLevels();
    const bothActive = levels.includes('senior_high') && levels.includes('college');
    bothBtn.classList.toggle('is-active', bothActive);
    bothBtn.setAttribute('aria-pressed', bothActive ? 'true' : 'false');
}

function setScholarshipTargetLevels(levels) {
    const selected = new Set(Array.isArray(levels) ? levels : []);
    if (!Array.isArray(levels) && typeof levels === 'string' && levels !== '') {
        if (levels === 'both') {
            selected.add('senior_high');
            selected.add('college');
        } else {
            selected.add(levels);
        }
    }

    document.querySelectorAll('input[name="scholarshipTargetLevel"]').forEach((input) => {
        input.checked = selected.has(input.value);
    });
    updateScholarshipBothButtonState();
}

function bindScholarshipLevelControls() {
    const bothBtn = document.getElementById('schLevelBothBtn');
    if (bothBtn && !bothBtn.dataset.bound) {
        bothBtn.dataset.bound = '1';
        bothBtn.addEventListener('click', (event) => {
            event.preventDefault();
            document.querySelectorAll('input[name="scholarshipTargetLevel"]').forEach((input) => {
                input.checked = true;
            });
            updateScholarshipBothButtonState();
        });
        bothBtn.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                bothBtn.click();
            }
        });
    }

    document.querySelectorAll('input[name="scholarshipTargetLevel"]').forEach((input) => {
        if (input.dataset.levelBound === '1') return;
        input.dataset.levelBound = '1';
        input.addEventListener('change', updateScholarshipBothButtonState);
    });
}

function bindParticipationQtyInput() {
    const input = document.getElementById('participationQty');
    if (!input || input.dataset.bound === '1') return;
    input.dataset.bound = '1';

    const sanitize = () => {
        const digits = input.value.replace(/\D/g, '');
        if (digits === '') {
            input.value = '';
            return;
        }
        let num = parseInt(digits, 10);
        if (Number.isNaN(num)) {
            input.value = '';
            return;
        }
        if (num > 1000) num = 1000;
        input.value = String(num);
    };

    input.addEventListener('input', sanitize);
    input.addEventListener('paste', (event) => {
        event.preventDefault();
        const pasted = (event.clipboardData || window.clipboardData)?.getData('text') || '';
        input.value = pasted.replace(/\D/g, '');
        sanitize();
    });
    input.addEventListener('keydown', (event) => {
        if (['e', 'E', '+', '-', '.', ','].includes(event.key)) {
            event.preventDefault();
        }
    });
}

function parseParticipationQuantity(raw) {
    const trimmed = String(raw ?? '').trim();
    if (trimmed === '') {
        return { valid: true, value: null };
    }
    if (!/^\d+$/.test(trimmed)) {
        return {
            valid: false,
            message: 'Maximum beneficiaries must contain whole numbers only (0–1000).',
        };
    }
    const qtyNum = parseInt(trimmed, 10);
    if (qtyNum < 0 || qtyNum > 1000) {
        return {
            valid: false,
            message: 'Maximum beneficiaries must be between 0 and 1000.',
        };
    }
    return { valid: true, value: qtyNum };
}

function formatTargetLevelsLabel(levels) {
    const normalized = Array.isArray(levels) ? levels : [];
    if (normalized.includes('senior_high') && normalized.includes('college')) {
        return 'Both (Senior High & College)';
    }
    const labels = normalized
        .map((level) => SCHOLARSHIP_TARGET_LEVELS[level]?.label)
        .filter(Boolean);
    return labels.join(', ');
}

function buildEligibilityForTargetLevels(targetLevels) {
    const educationLevels = [];
    (Array.isArray(targetLevels) ? targetLevels : []).forEach((level) => {
        const config = SCHOLARSHIP_TARGET_LEVELS[level];
        if (config) {
            educationLevels.push(...config.education_levels);
        }
    });

    if (educationLevels.length === 0) {
        return getDefaultEligibility();
    }

    return {
        youth_classifications: ['In School Youth'],
        youth_age_groups: [],
        education_levels: [...new Set(educationLevels)],
    };
}

const QUICK_GUIDELINE_MAX_CHARS = 2000;
const QUICK_GUIDELINE_MAX_STEPS = 10;

function renderQuickGuidelineCharCount(value) {
    const length = [...String(value ?? '')].length;
    return `${length} / ${QUICK_GUIDELINE_MAX_CHARS}`;
}

function updateQuickGuidelineCharCount(textarea) {
    const counter = textarea?.closest('.sch-qg-edit-field')?.querySelector('.sch-qg-char-count');
    if (!counter || !textarea) return;
    const length = [...textarea.value].length;
    counter.textContent = `${length} / ${QUICK_GUIDELINE_MAX_CHARS}`;
    counter.classList.toggle('is-over', length > QUICK_GUIDELINE_MAX_CHARS);
}

function bindQuickGuidelinesFieldEvents() {
    document.querySelectorAll('#schQuickGuidelinesBuilder textarea').forEach((textarea) => {
        if (textarea.dataset.qgBound === '1') return;
        textarea.dataset.qgBound = '1';
        textarea.maxLength = QUICK_GUIDELINE_MAX_CHARS;
        textarea.addEventListener('input', () => {
            if ([...textarea.value].length > QUICK_GUIDELINE_MAX_CHARS) {
                textarea.value = [...textarea.value].slice(0, QUICK_GUIDELINE_MAX_CHARS).join('');
            }
            updateQuickGuidelineCharCount(textarea);
        });
        updateQuickGuidelineCharCount(textarea);
    });
}

function snapshotQuickGuidelinesFromDom() {
    const cards = document.querySelectorAll('#schQuickGuidelinesBuilder .sch-qg-edit-card');
    return Array.from(cards).map((card) => ({
        en: card.querySelector('.sch-qg-en')?.value ?? '',
        tl: card.querySelector('.sch-qg-tl')?.value ?? '',
    }));
}

function updateQuickGuidelinesActions() {
    const addBtn = document.getElementById('schAddQuickGuidelineBtn');
    const stepCount = document.querySelectorAll('#schQuickGuidelinesBuilder .sch-qg-edit-card').length;
    if (addBtn) {
        addBtn.disabled = stepCount >= QUICK_GUIDELINE_MAX_STEPS;
        addBtn.title = stepCount >= QUICK_GUIDELINE_MAX_STEPS
            ? `Maximum of ${QUICK_GUIDELINE_MAX_STEPS} steps reached`
            : '';
    }
}

function renderQuickGuidelinesBuilder(steps = null) {
    const container = document.getElementById('schQuickGuidelinesBuilder');
    if (!container) return;

    const guidelines = Array.isArray(steps) ? steps : [];
    if (!guidelines.length) {
        container.innerHTML = '<p class="sch-qg-empty">No quick guideline steps yet. This section is optional — click <strong>Add Step</strong> to create up to 10 bilingual steps for Kabataan applicants.</p>';
        updateQuickGuidelinesActions();
        return;
    }

    container.innerHTML = guidelines.map((step, index) => `
        <div class="sch-qg-edit-card" data-qg-step="${index}">
            <div class="sch-qg-edit-card__header">
                <p class="sch-qg-edit-card__title">Step #${index + 1}</p>
                <button type="button" class="sch-qg-delete-btn" data-qg-delete="${index}" aria-label="Delete step ${index + 1}">Delete</button>
            </div>
            <div class="sch-qg-edit-field">
                <label>English</label>
                <textarea class="schol-input sch-qg-en" rows="4" maxlength="${QUICK_GUIDELINE_MAX_CHARS}">${escapeHtml(step.en || '')}</textarea>
                <span class="sch-qg-char-count">${renderQuickGuidelineCharCount(step.en || '')}</span>
            </div>
            <div class="sch-qg-edit-field">
                <label>Tagalog</label>
                <textarea class="schol-input sch-qg-tl" rows="4" maxlength="${QUICK_GUIDELINE_MAX_CHARS}">${escapeHtml(step.tl || '')}</textarea>
                <span class="sch-qg-char-count">${renderQuickGuidelineCharCount(step.tl || '')}</span>
            </div>
        </div>
    `).join('');
    bindQuickGuidelinesFieldEvents();
    bindQuickGuidelinesDeleteEvents();
    updateQuickGuidelinesActions();
}

function bindQuickGuidelinesDeleteEvents() {
    document.querySelectorAll('#schQuickGuidelinesBuilder [data-qg-delete]').forEach((button) => {
        if (button.dataset.qgDeleteBound === '1') return;
        button.dataset.qgDeleteBound = '1';
        button.addEventListener('click', () => {
            const index = Number.parseInt(button.getAttribute('data-qg-delete') || '', 10);
            if (Number.isNaN(index)) return;
            deleteQuickGuidelineStep(index);
        });
    });
}

function addQuickGuidelineStep() {
    const current = snapshotQuickGuidelinesFromDom();
    if (current.length >= QUICK_GUIDELINE_MAX_STEPS) return;
    current.push({ en: '', tl: '' });
    renderQuickGuidelinesBuilder(current);
}

function deleteQuickGuidelineStep(index) {
    const current = snapshotQuickGuidelinesFromDom();
    if (index < 0 || index >= current.length) return;
    current.splice(index, 1);
    renderQuickGuidelinesBuilder(current);
}

function validateQuickGuidelines() {
    const cards = document.querySelectorAll('#schQuickGuidelinesBuilder .sch-qg-edit-card');
    if (cards.length > QUICK_GUIDELINE_MAX_STEPS) {
        return {
            valid: false,
            message: `Quick Guidelines cannot exceed ${QUICK_GUIDELINE_MAX_STEPS} steps.`,
        };
    }

    for (let index = 0; index < cards.length; index += 1) {
        const card = cards[index];
        const fields = [
            { selector: '.sch-qg-en', label: 'English' },
            { selector: '.sch-qg-tl', label: 'Tagalog' },
        ];
        for (const field of fields) {
            const textarea = card.querySelector(field.selector);
            const length = [...(textarea?.value || '')].length;
            if (length > QUICK_GUIDELINE_MAX_CHARS) {
                return {
                    valid: false,
                    message: `Quick Guidelines step #${index + 1} (${field.label}) exceeds ${QUICK_GUIDELINE_MAX_CHARS} characters.`,
                };
            }
        }
    }
    return { valid: true, message: '' };
}

function collectQuickGuidelines() {
    return snapshotQuickGuidelinesFromDom()
        .map(({ en, tl }) => ({ en: en.trim(), tl: tl.trim() }))
        .filter(({ en, tl }) => en || tl)
        .map(({ en, tl }) => ({ en: en || tl, tl: tl || en }));
}

function applyCommitteeHeadDisplay(savedHead = '') {
    const headEl = document.getElementById('committeeHeadDisplay');
    if (!headEl) return;
    headEl.value = savedHead || programMeta?.committee_head || '';
    headEl.placeholder = headEl.value ? '' : 'Assign Education Committee head in Committees';
}

function applyAutoSchoolYear() {
    const schoolYearEl = document.getElementById('schoolYear');
    if (!schoolYearEl) return;

    const schoolYear = programMeta?.school_year || '';
    schoolYearEl.value = schoolYear;
    schoolYearEl.placeholder = schoolYear ? '' : 'Set program duration in Programs first';
}

function updateSemesterAvailability(currentSemester = '') {
    const semesterEl = document.getElementById('programSemester');
    if (!semesterEl) return;

    const blocked = new Set(
        (Array.isArray(usedSemesters) ? usedSemesters : [])
            .filter((semester) => semester && semester !== currentSemester),
    );

    semesterEl.querySelectorAll('option').forEach((option) => {
        if (!option.value) return;
        const isBlocked = blocked.has(option.value);
        option.disabled = isBlocked;
        option.hidden = isBlocked;
    });

    if (semesterEl.value && blocked.has(semesterEl.value)) {
        semesterEl.value = '';
    }
}

function getApplicationTypeValue() {
    return document.querySelector('input[name="applicationType"]:checked')?.value || 'new_only';
}

function setApplicationTypeValue(value) {
    const radio = document.querySelector(`input[name="applicationType"][value="${value}"]`);
    if (radio) {
        radio.disabled = false;
        radio.checked = true;
    }
}

function renderInlineProgramPreview() {
    const panel = document.getElementById('schProgramPreviewPanel');
    if (!panel) return;

    let customQuestions = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customQuestions = window.SpfbFormBuilder.getQuestions().filter((question) => question.type !== 'file');
    }

    const fakeProgram = {
        program_name: document.getElementById('programName')?.value || programMeta?.program_name || 'Scholarship Program',
        participation_quantity: document.getElementById('participationQty')?.value || '',
        kk_profiling_fields: getDefaultKkFields(),
        custom_questions: customQuestions,
        scholarship_details: collectScholarshipDetails(),
    };
    panel.innerHTML = renderProgramViewHtml(fakeProgram);
}

function createRequirementGroupCard(group = {}) {
    const groupId = `req_group_${++reqGroupCounter}`;
    const items = Array.isArray(group.items) ? group.items : [''];
    const itemsHtml = items.map((item, index) => `
        <div class="sch-req-item-row" data-req-item>
            <input type="text" class="schol-input sch-req-item-input" value="${escapeHtml(item)}" placeholder="Requirement ${index + 1}">
            <button type="button" class="sch-req-remove-item" title="Remove item">&times;</button>
        </div>
    `).join('');

    return `
        <div class="sch-req-group-card" data-req-group="${groupId}">
            <div class="sch-req-group-head">
                <input type="text" class="schol-input sch-req-group-title" value="${escapeHtml(group.title || '')}" placeholder="e.g. Scholarship Grant for 1st Semester 2025 - 2026 Requirements">
                <button type="button" class="sch-req-remove-group" title="Remove group">Remove</button>
            </div>
            <div class="sch-req-items" data-req-items>${itemsHtml}</div>
            <button type="button" class="sch-req-add-item">+ Add requirement item</button>
        </div>`;
}

function renderRequirementGroups(groups = []) {
    const container = document.getElementById('schReqGroupsContainer');
    if (!container) return;
    reqGroupCounter = 0;
    const list = groups.length ? groups : [{ title: '', items: [''] }];
    container.innerHTML = list.map((group) => createRequirementGroupCard(group)).join('');
    bindRequirementGroupEvents(container);
}

function bindRequirementGroupEvents(container) {
    if (!container) return;

    container.querySelectorAll('.sch-req-add-item').forEach((btn) => {
        btn.addEventListener('click', () => {
            const itemsWrap = btn.closest('[data-req-group]')?.querySelector('[data-req-items]');
            if (!itemsWrap) return;
            const row = document.createElement('div');
            row.className = 'sch-req-item-row';
            row.setAttribute('data-req-item', '');
            row.innerHTML = `
                <input type="text" class="schol-input sch-req-item-input" placeholder="Requirement item">
                <button type="button" class="sch-req-remove-item" title="Remove item">&times;</button>`;
            itemsWrap.appendChild(row);
            bindRequirementGroupEvents(container);
        });
    });

    container.querySelectorAll('.sch-req-remove-item').forEach((btn) => {
        btn.onclick = () => {
            const row = btn.closest('[data-req-item]');
            const itemsWrap = btn.closest('[data-req-items]');
            if (row && itemsWrap && itemsWrap.querySelectorAll('[data-req-item]').length > 1) {
                row.remove();
            }
        };
    });

    container.querySelectorAll('.sch-req-remove-group').forEach((btn) => {
        btn.onclick = () => {
            const card = btn.closest('[data-req-group]');
            const all = container.querySelectorAll('[data-req-group]');
            if (card && all.length > 1) card.remove();
        };
    });
}

function collectScholarshipDetails() {
    const groups = [];
    document.querySelectorAll('#schReqGroupsContainer [data-req-group]').forEach((card) => {
        const title = card.querySelector('.sch-req-group-title')?.value?.trim() || '';
        const items = [];
        card.querySelectorAll('.sch-req-item-input').forEach((input) => {
            const value = input.value?.trim();
            if (value) items.push(value);
        });
        if (title || items.length) {
            groups.push({ title: title || 'Requirements', items });
        }
    });

    const submissionStart = document.getElementById('schSubmissionStart')?.value?.trim() || '';
    const submissionEnd = document.getElementById('schSubmissionEnd')?.value?.trim() || '';
    const verificationStart = document.getElementById('schVerificationStart')?.value?.trim() || '';
    const verificationEnd = document.getElementById('schVerificationEnd')?.value?.trim() || '';

    const details = { requirement_groups: groups };

    if (submissionStart || submissionEnd) {
        details.submission_period = { start: submissionStart || null, end: submissionEnd || null };
    }
    if (verificationStart || verificationEnd) {
        details.verification_period = { start: verificationStart || null, end: verificationEnd || null };
    }

    details.eligibility = buildEligibilityForTargetLevels(getScholarshipTargetLevels());

    const schoolYear = document.getElementById('schoolYear')?.value?.trim() || programMeta?.school_year || '';
    const semester = document.getElementById('programSemester')?.value?.trim() || '';
    const applicationType = getApplicationTypeValue();
    const targetLevels = getScholarshipTargetLevels();

    if (schoolYear) details.school_year = schoolYear;
    if (semester) details.semester = semester;
    if (applicationType) details.application_type = applicationType;
    if (targetLevels.length) details.scholarship_target_levels = targetLevels;
    details.quick_guidelines = collectQuickGuidelines();
    details.committee_head = document.getElementById('committeeHeadDisplay')?.value?.trim()
        || programMeta?.committee_head
        || '';

    return details;
}

function populateScholarshipDetails(details, customQuestions = []) {
    const data = details || {};
    renderRequirementGroups(data.requirement_groups || []);

    const setDate = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value || '';
    };

    setDate('schSubmissionStart', data.submission_period?.start);
    setDate('schSubmissionEnd', data.submission_period?.end);
    setDate('schVerificationStart', data.verification_period?.start);
    setDate('schVerificationEnd', data.verification_period?.end);

    const schoolYearEl = document.getElementById('schoolYear');
    const semesterEl = document.getElementById('programSemester');
    applyAutoSchoolYear();
    if (schoolYearEl && data.school_year) schoolYearEl.value = data.school_year;
    if (semesterEl) semesterEl.value = data.semester || '';
    updateSemesterAvailability(data.semester || '');
    setApplicationTypeValue(data.application_type || 'new_only');
    const targetLevels = Array.isArray(data.scholarship_target_levels) && data.scholarship_target_levels.length
        ? data.scholarship_target_levels
        : (data.scholarship_target_level ? [data.scholarship_target_level] : []);
    setScholarshipTargetLevels(targetLevels);
    renderQuickGuidelinesBuilder(data.quick_guidelines || []);
    applyCommitteeHeadDisplay(data.committee_head || '');
}

function resetScholarshipDetailsForm() {
    renderRequirementGroups([]);
    ['schSubmissionStart', 'schSubmissionEnd', 'schVerificationStart', 'schVerificationEnd'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    const semesterEl = document.getElementById('programSemester');
    applyAutoSchoolYear();
    if (semesterEl) semesterEl.value = '';
    updateSemesterAvailability('');
    setApplicationTypeValue('new_only');
    setScholarshipTargetLevels([]);
    renderQuickGuidelinesBuilder([]);
    applyCommitteeHeadDisplay();
    setApplicationTypeAvailability(false);
}

const KK_FIELD_LABELS = {
    last_name: 'Last Name',
    first_name: 'First Name',
    middle_name: 'Middle Name',
    suffix: 'Suffix',
    birthday: 'Birthday',
    age: 'Age',
    sex: 'Sex',
    civil_status: 'Civil Status',
    contact_number: 'Contact Number',
    email: 'Email Address',
    region: 'Region',
    province: 'Province',
    city: 'City/Municipality',
    barangay: 'Barangay',
    purok_zone: 'Purok/Zone',
    youth_classification: 'Youth Classification',
    youth_age_group: 'Youth Age Group',
    education: 'Educational Attainment',
    current_school: 'Current School',
    course_strand: 'Course / Strand',
};

const SCHOLARSHIP_EXCLUDED_KK_FIELDS = ['work_status', 'sk_voter', 'sk_voted'];

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
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

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(msg) {
    if (typeof window.showScholarshipToast === 'function') {
        window.showScholarshipToast(msg);
    }
}

function setSaveButtonLoading(isLoading) {
    const saveBtn = document.getElementById('btnSaveProgram');
    if (!saveBtn) return;

    if (isLoading) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="schol-save-btn-content"><span class="schol-save-spinner"></span> Saving...</span>';
        return;
    }

    saveBtn.disabled = false;
    saveBtn.innerHTML = `
        <span class="schol-save-btn-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Save Program
        </span>
    `;
}

function resolveProgramStatus(program) {
    return program?.status === 'closed' ? 'closed' : 'open';
}

function formatStatusLabel(status) {
    return status === 'closed' ? 'Closed' : 'Open';
}

function getTypeLabel(type) {
    const map = {
        text: 'Short Answer',
        paragraph: 'Paragraph',
        number: 'Number',
        checkbox: 'Checkboxes',
        radio: 'Multiple Choice',
        file: 'File Upload',
    };
    return map[type] || type || 'Short Answer';
}

async function loadProgramMeta() {
    const response = await apiFetch('/api/schedule-programs/meta?letter=A');
    programMeta = response.data || null;

    const nameEl = document.getElementById('programName');

    if (nameEl) {
        nameEl.value = programMeta?.program_name || programMeta?.program_type || '';
    }

    usedSemesters = Array.isArray(programMeta?.used_semesters) ? programMeta.used_semesters : [];
    applyAutoSchoolYear();
    updateSemesterAvailability();
    applyCommitteeHeadDisplay();
    renderQuickGuidelinesBuilder([]);
}

async function loadPrograms() {
    const response = await apiFetch('/api/schedule-programs?letter=A');
    schedulePrograms = Array.isArray(response.data) ? response.data : [];
    renderFormsTable();
}

function resetModalForm() {
    editingProgramId = null;

    const participationQty = document.getElementById('participationQty');
    const status = document.getElementById('programStatus');

    if (participationQty) participationQty.value = '';
    if (status) status.value = 'open';

    if (window.SpfbFormBuilder) {
        window.SpfbFormBuilder.reset();
    }

    resetScholarshipDetailsForm();

    if (programMeta) {
        const nameEl = document.getElementById('programName');
        if (nameEl) nameEl.value = programMeta.program_name || programMeta.program_type || '';
    }

    setApplicationTypeAvailability(false);
    switchBuilderTab('details');
}

function openModal(forEditId) {
    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    modal.classList.remove('schol-modal-maximized');
    if (modalBox) modalBox.classList.remove('schol-modal-maximized');
    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
    }

    if (!forEditId) {
        resetModalForm();
        if (modalTitle) {
            modalTitle.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Scholarship Program
            `;
        }
    }
}

function closeModal() {
    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');

    document.body.style.overflow = '';
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('schol-modal-maximized');
    }
    if (modalBox) modalBox.classList.remove('schol-modal-maximized');
    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
    }
    resetModalForm();
}

function renderActionMenuCell(programId) {
    return `
        <td class="col-actions">
            <div class="row-actions-menu">
                <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
                <div class="row-actions-dropdown" role="menu">
                    <button type="button" class="row-actions-item row-actions-item-view" data-action="view" data-program-id="${programId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>View</span>
                    </button>
                    <button type="button" class="row-actions-item row-actions-item-edit" data-action="edit" data-program-id="${programId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Edit</span>
                    </button>
                    <button type="button" class="row-actions-item row-actions-item-danger" data-action="delete" data-program-id="${programId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </td>
    `;
}

function resetDeleteProgramConfirmButton() {
    const confirmBtn = document.getElementById('deleteProgramConfirm');
    if (!confirmBtn) return;
    confirmBtn.disabled = true;
    confirmBtn.classList.remove('is-enabled');
    confirmBtn.classList.add('is-disabled');
}

function syncDeleteProgramConfirmButton() {
    const confirmBtn = document.getElementById('deleteProgramConfirm');
    const confirmInput = document.getElementById('deleteProgramConfirmText');
    if (!confirmBtn) return;
    const matched = (confirmInput?.value?.trim() || '') === 'Confirm';
    confirmBtn.disabled = !matched;
    confirmBtn.classList.toggle('is-enabled', matched);
    confirmBtn.classList.toggle('is-disabled', !matched);
}

function renderFormsTable() {
    const tableBody = document.getElementById('safFormsTableBody');
    const countEl = document.getElementById('programCount');
    if (!tableBody) return;

    const forms = [...schedulePrograms].sort((a, b) => {
        const nameA = (a.program_name || '').toLowerCase();
        const nameB = (b.program_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
    });

    if (countEl) countEl.textContent = String(forms.length);

    if (!forms.length) {
        tableBody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">No scholarship programs yet. Click Create Scholarship Program to add one.</td></tr>';
        return;
    }

    tableBody.innerHTML = forms.map((program) => {
        const status = resolveProgramStatus(program);
        const statusClass = status === 'open' ? 'schol-pill-approved' : 'schol-pill-rejected';
        const details = program.scholarship_details || {};
        const submissionStart = details.submission_period?.start || program.start_date || '—';
        const submissionEnd = details.submission_period?.end || program.end_date || '—';

        return `
            <tr>
                <td>${escapeHtml(program.program_name)}</td>
                <td>${escapeHtml(details.school_year || '—')}</td>
                <td>${escapeHtml(details.semester || '—')}</td>
                <td>${escapeHtml(program.participation_quantity ?? 'N/A')}</td>
                <td>${escapeHtml(submissionStart)}</td>
                <td>${escapeHtml(submissionEnd)}</td>
                <td><span class="schol-pill ${statusClass}">${formatStatusLabel(status)}</span></td>
                ${renderActionMenuCell(program.id)}
            </tr>
        `;
    }).join('');

    if (typeof window.bindRowActionsTable === 'function') {
        window.bindRowActionsTable(tableBody);
    }
}

function editProgram(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    if (!program) {
        showToast('Program not found.', 'error');
        return;
    }

    editingProgramId = program.id;
    openModal(program.id);

    const participationQty = document.getElementById('participationQty');
    const status = document.getElementById('programStatus');
    const nameEl = document.getElementById('programName');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (participationQty) participationQty.value = program.participation_quantity ?? '';
    if (status) status.value = resolveProgramStatus(program);
    if (nameEl) nameEl.value = program.program_name || '';

    const allQuestions = program.custom_questions || [];
    const customOnly = allQuestions.filter((question) => question.type !== 'file');

    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.setQuestions === 'function') {
        window.SpfbFormBuilder.setQuestions(customOnly);
    }

    populateScholarshipDetails(program.scholarship_details || null, allQuestions);
    usedSemesters = schedulePrograms
        .filter((item) => String(item.id) !== String(program.id))
        .map((item) => item.scholarship_details?.semester)
        .filter(Boolean);
    updateSemesterAvailability(program.scholarship_details?.semester || '');
    setApplicationTypeAvailability(Boolean(program.renewal_options_enabled));

    switchBuilderTab('details');

    if (modalTitle) {
        modalTitle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Scholarship Program
        `;
    }
}

function formatIsoDateDisplay(iso) {
    if (!iso) return '—';
    const date = new Date(`${iso}T00:00:00`);
    if (Number.isNaN(date.getTime())) return escapeHtml(iso);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatPeriodRange(period, program) {
    if (!period) return '';
    const start = formatIsoDateDisplay(period.start || program?.start_date);
    const end = formatIsoDateDisplay(period.end || program?.end_date);
    if (start !== '—' && end !== '—') return `${start} – ${end}`;
    return start !== '—' ? start : end;
}

function renderViewAnnouncementCard(title, bodyHtml) {
    return `
        <div class="sch-view-announcement-card">
            <div class="sch-view-announcement-card__title">${escapeHtml(title || 'Announcement')}</div>
            <div class="sch-view-announcement-card__body">${bodyHtml}</div>
        </div>`;
}

const APPLICATION_TYPE_LABELS = {
    new_only: 'New Applicants Only',
    renewal_only: 'Renewal Only',
    both: 'New Applicants + Renewal',
};

function renderProgramViewHtml(program) {
    const status = resolveProgramStatus(program);
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
    };
    const statusStyle = statusColors[status] || statusColors.open;
    const kkFields = (program.kk_profiling_fields?.length ? program.kk_profiling_fields : getDefaultKkFields())
        .filter((field) => !SCHOLARSHIP_EXCLUDED_KK_FIELDS.includes(field));
    const allQuestions = (program.custom_questions || []).filter((question) => question.type !== 'file');
    const details = program.scholarship_details || {};
    const groups = Array.isArray(details.requirement_groups) ? details.requirement_groups : [];
    const targetLevels = Array.isArray(details.scholarship_target_levels) && details.scholarship_target_levels.length
        ? details.scholarship_target_levels
        : (details.scholarship_target_level ? [details.scholarship_target_level] : []);
    const targetLevelLabel = formatTargetLevelsLabel(targetLevels);
    const committeeHead = details.committee_head || programMeta?.committee_head || '';

    const kkList = kkFields.length
        ? `<ul class="sch-view-req-list">${kkFields.map((field) => `<li>${escapeHtml(KK_FIELD_LABELS[field] || field)}</li>`).join('')}</ul>`
        : '<p class="sch-view-muted">All KK Profiling fields are included automatically.</p>';

    let announcementsHtml = '';
    groups.forEach((group) => {
        const items = (group.items || []).filter((item) => String(item || '').trim());
        if (!group.title && !items.length) return;
        const listHtml = items.length
            ? `<ul class="sch-view-req-list">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
            : '<p class="sch-view-muted">No requirements listed.</p>';
        announcementsHtml += renderViewAnnouncementCard(group.title || 'Requirements', listHtml);
    });

    const submissionLabel = formatPeriodRange(details.submission_period, program);
    const verificationLabel = formatPeriodRange(details.verification_period, program);
    const eligibility = details.eligibility || getDefaultEligibility();
    const eligibilityClassifications = (eligibility.youth_classifications || []).filter(Boolean);
    const eligibilityAgeGroups = (eligibility.youth_age_groups || []).filter(Boolean);
    const eligibilityEducation = (eligibility.education_levels || []).filter(Boolean);
    const formatEligibilityList = (items, fallback) => items.length
        ? `<ul class="sch-view-req-list">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
        : `<p class="sch-view-muted">${fallback}</p>`;

    const customQuestionsHtml = allQuestions.length
        ? allQuestions.map((question, index) => `
            <div class="sch-view-question-card">
                <div class="sch-view-question-label">
                    ${index + 1}. ${escapeHtml(question.label)}
                    ${question.required ? '<span class="sch-view-required">*</span>' : ''}
                </div>
                <div class="sch-view-question-type">Type: ${escapeHtml(getTypeLabel(question.type))}</div>
            </div>
        `).join('')
        : '';

    return `
        <div class="sch-view-program-wrap">
            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Program Information</h4>
                <div class="sch-view-grid">
                    <div class="sch-view-field sch-view-field-full">
                        <label>Program</label>
                        <div class="sch-view-value">${escapeHtml(program.program_name)}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>School Year</label>
                        <div class="sch-view-value">${escapeHtml(details.school_year || '—')}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>Semester</label>
                        <div class="sch-view-value">${escapeHtml(details.semester || '—')}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>Application Type</label>
                        <div class="sch-view-value">${escapeHtml(APPLICATION_TYPE_LABELS[details.application_type] || details.application_type || '—')}</div>
                    </div>
                    ${targetLevelLabel ? `
                    <div class="sch-view-field">
                        <label>Scholarship Level</label>
                        <div class="sch-view-value">${escapeHtml(targetLevelLabel)}</div>
                    </div>` : ''}
                    ${committeeHead ? `
                    <div class="sch-view-field">
                        <label>Committee Head</label>
                        <div class="sch-view-value">${escapeHtml(committeeHead)}</div>
                    </div>` : ''}
                    <div class="sch-view-field">
                        <label>Maximum Beneficiaries</label>
                        <div class="sch-view-value">${escapeHtml(program.participation_quantity ?? 'N/A')}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>Status</label>
                        <span class="sch-view-status" style="background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Applicant Eligibility</h4>
                <div class="sch-view-grid">
                    <div class="sch-view-field sch-view-field-full">
                        <label>Youth Classification</label>
                        ${formatEligibilityList(eligibilityClassifications, 'In School Youth (automatic).')}
                    </div>
                    ${eligibilityAgeGroups.length ? `
                    <div class="sch-view-field sch-view-field-full">
                        <label>Youth Age Group</label>
                        ${formatEligibilityList(eligibilityAgeGroups, 'Any age group.')}
                    </div>` : ''}
                    <div class="sch-view-field sch-view-field-full">
                        <label>Educational Background</label>
                        ${formatEligibilityList(eligibilityEducation, targetLevelLabel ? `Based on ${targetLevelLabel} selection.` : 'High School Level or College Level (In School Youth only).')}
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">1. Personal Information (KK Profiling)</h4>
                <p class="sch-view-kk-note">All KK Profiling fields are automatically included and auto-filled from the applicant's KK Profile as read-only.</p>
                ${kkList}
            </div>

            ${announcementsHtml ? `<div class="sch-view-announcements">${announcementsHtml}</div>` : ''}

            ${submissionLabel ? `
                <div class="schol-schedule-card sch-view-section">
                    <h4 class="schol-schedule-title">Period for the Submission of Requirements</h4>
                    <div class="sch-view-value">${escapeHtml(submissionLabel)}</div>
                </div>` : ''}

            ${verificationLabel ? `
                <div class="schol-schedule-card sch-view-section">
                    <h4 class="schol-schedule-title">Period for the Assessment/Verification of Scholar Profile and Requirements</h4>
                    <div class="sch-view-value">${escapeHtml(verificationLabel)}</div>
                </div>` : ''}

            ${customQuestionsHtml ? `
            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Custom Questions</h4>
                <div class="sch-view-questions">${customQuestionsHtml}</div>
            </div>` : ''}
        </div>`;
}

function openFormPreview(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    const viewProgramBody = document.getElementById('viewProgramBody');
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (!program || !viewProgramBody || !viewProgramModal) return;

    resetViewProgramModalSize();
    viewProgramBody.innerHTML = renderProgramViewHtml(program);
    viewProgramModal.style.display = 'flex';
}

function resetViewProgramModalSize() {
    const viewProgramBox = document.getElementById('viewProgramBox');
    const viewProgramModal = document.getElementById('viewProgramModal');
    const viewProgramMaximize = document.getElementById('viewProgramMaximize');

    if (viewProgramBox) {
        viewProgramBox.classList.remove('schol-modal-maximized');
    }
    if (viewProgramModal) {
        viewProgramModal.classList.remove('schol-modal-overlay-maximized');
    }
    if (viewProgramMaximize) {
        viewProgramMaximize.textContent = '□';
        viewProgramMaximize.title = 'Maximize';
    }
}

function openDeleteProgramModal(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    pendingDeleteProgramId = programId;
    const deleteModal = document.getElementById('deleteProgramModal');
    const nameEl = document.getElementById('deleteProgramName');
    const confirmInput = document.getElementById('deleteProgramConfirmText');
    const confirmError = document.getElementById('deleteProgramConfirmError');

    if (nameEl) {
        nameEl.textContent = program ? program.program_name || 'this program' : 'this program';
    }
    if (confirmInput) confirmInput.value = '';
    if (confirmError) {
        confirmError.style.display = 'none';
        confirmError.textContent = '';
    }
    resetDeleteProgramConfirmButton();
    if (deleteModal) deleteModal.style.display = 'flex';
}

function closeDeleteProgramModal() {
    pendingDeleteProgramId = null;
    const deleteModal = document.getElementById('deleteProgramModal');
    const confirmInput = document.getElementById('deleteProgramConfirmText');
    const confirmError = document.getElementById('deleteProgramConfirmError');

    if (confirmInput) confirmInput.value = '';
    if (confirmError) {
        confirmError.style.display = 'none';
        confirmError.textContent = '';
    }
    resetDeleteProgramConfirmButton();
    if (deleteModal) deleteModal.style.display = 'none';
}

async function confirmDeleteProgram() {
    if (!pendingDeleteProgramId) return;

    const confirmInput = document.getElementById('deleteProgramConfirmText');
    const confirmError = document.getElementById('deleteProgramConfirmError');
    if ((confirmInput?.value?.trim() || '') !== 'Confirm') {
        if (confirmError) {
            confirmError.textContent = 'Please type Confirm to delete this program.';
            confirmError.style.display = 'block';
        }
        return;
    }

    const confirmBtn = document.getElementById('deleteProgramConfirm');
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Delete Program';

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.classList.remove('is-enabled');
        confirmBtn.classList.add('is-disabled');
        confirmBtn.innerHTML = '<span class="schol-save-spinner"></span> Deleting...';
    }

    try {
        await apiFetch(`/api/schedule-programs/${pendingDeleteProgramId}`, { method: 'DELETE' });
        closeDeleteProgramModal();
        await loadPrograms();
        showToast('Program deleted successfully.', 'success');
    } catch (error) {
        showToast(error.message || 'Failed to delete program.', 'error');
    } finally {
        if (confirmBtn) {
            confirmBtn.innerHTML = defaultHtml;
            syncDeleteProgramConfirmButton();
        }
    }
}

async function handleSave() {
    const submissionStart = document.getElementById('schSubmissionStart')?.value?.trim();
    const submissionEnd = document.getElementById('schSubmissionEnd')?.value?.trim();
    const status = document.getElementById('programStatus')?.value || 'open';
    const participationQtyRaw = document.getElementById('participationQty')?.value?.trim();
    const schoolYear = document.getElementById('schoolYear')?.value?.trim();
    const semester = document.getElementById('programSemester')?.value?.trim();

    if (!schoolYear) {
        showToast('School year is unavailable. Set the program duration in Programs first.', 'error');
        switchBuilderTab('details');
        return;
    }

    if (!semester) {
        showToast('Please select a semester (1st or 2nd only).', 'error');
        switchBuilderTab('details');
        return;
    }

    const targetLevels = getScholarshipTargetLevels();
    if (!targetLevels.length) {
        showToast('Please select at least one scholarship level: Senior High, College, or Both.', 'error');
        switchBuilderTab('details');
        return;
    }

    if (!submissionStart || !submissionEnd) {
        showToast('Please set the submission period start and end dates.', 'error');
        switchBuilderTab('details');
        return;
    }

    let participationQuantity = null;
    const participationCheck = parseParticipationQuantity(participationQtyRaw);
    if (!participationCheck.valid) {
        showToast(participationCheck.message, 'error');
        switchBuilderTab('details');
        return;
    }
    participationQuantity = participationCheck.value;

    const quickGuidelinesCheck = validateQuickGuidelines();
    if (!quickGuidelinesCheck.valid) {
        showToast(quickGuidelinesCheck.message, 'error');
        switchBuilderTab('quick-guidelines');
        return;
    }

    let customQuestions = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customQuestions = window.SpfbFormBuilder.getQuestions().filter((question) => question.type !== 'file');
    }

    const payload = {
        status,
        participation_quantity: participationQuantity,
        scholarship_details: collectScholarshipDetails(),
        kk_profiling_fields: getDefaultKkFields(),
        custom_questions: customQuestions,
    };

    setSaveButtonLoading(true);

    try {
        if (editingProgramId) {
            await apiFetch(`/api/schedule-programs/${editingProgramId}?letter=A`, {
                method: 'PUT',
                body: JSON.stringify({ ...payload, program_letter: 'A' }),
            });
            showToast('Program updated successfully!', 'success');
        } else {
            await apiFetch('/api/schedule-programs?letter=A', {
                method: 'POST',
                body: JSON.stringify({ ...payload, program_letter: 'A' }),
            });
            showToast('Program saved successfully!', 'success');
        }

        closeModal();
        await loadPrograms();
        await loadProgramMeta();
    } catch (error) {
        showToast(error.message || 'Failed to save program.', 'error');
    } finally {
        setSaveButtonLoading(false);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    if (window.ScholarshipSystemFields) {
        window.ScholarshipSystemFields.renderBuilder(document.getElementById('scholSystemFieldsBuilder'));
    }

    renderRequirementGroups([]);

    bindBuilderTabs();
    bindScholarshipLevelControls();
    bindParticipationQtyInput();

    const tableBody = document.getElementById('safFormsTableBody');
    if (!tableBody) return;

    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const openBtn = document.getElementById('safOpenFormBtn');
    const closeBtn = document.getElementById('scholarProgramClose');
    const cancelBtn = document.getElementById('btnCancelProgram');
    const saveBtn = document.getElementById('btnSaveProgram');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');

    if (openBtn) openBtn.addEventListener('click', () => openModal());
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (saveBtn) saveBtn.addEventListener('click', handleSave);

    tableBody.addEventListener('click', (event) => {
        const actionItem = event.target.closest('.row-actions-item');
        if (!actionItem) return;

        const programId = actionItem.getAttribute('data-program-id');
        const action = actionItem.getAttribute('data-action');
        if (!programId || !action) return;

        if (action === 'view') openFormPreview(programId);
        if (action === 'edit') editProgram(programId);
        if (action === 'delete') openDeleteProgramModal(programId);
    });

    const deleteConfirmInput = document.getElementById('deleteProgramConfirmText');
    if (deleteConfirmInput) {
        deleteConfirmInput.addEventListener('input', () => {
            const confirmError = document.getElementById('deleteProgramConfirmError');
            if (confirmError) {
                confirmError.style.display = 'none';
                confirmError.textContent = '';
            }
            syncDeleteProgramConfirmButton();
        });
    }

    const addReqGroupBtn = document.getElementById('schAddReqGroupBtn');
    if (addReqGroupBtn) {
        addReqGroupBtn.addEventListener('click', () => {
            const container = document.getElementById('schReqGroupsContainer');
            if (!container) return;
            container.insertAdjacentHTML('beforeend', createRequirementGroupCard({ title: '', items: [''] }));
            bindRequirementGroupEvents(container);
        });
    }

    const addQgBtn = document.getElementById('schAddQuickGuidelineBtn');
    if (addQgBtn) {
        addQgBtn.addEventListener('click', () => addQuickGuidelineStep());
    }

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
    }

    if (maximizeBtn && modalBox && modal) {
        maximizeBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            modalBox.classList.toggle('schol-modal-maximized');
            modal.classList.toggle('schol-modal-maximized', modalBox.classList.contains('schol-modal-maximized'));
            const isMax = modalBox.classList.contains('schol-modal-maximized');
            maximizeBtn.textContent = isMax ? '⧉' : '□';
            maximizeBtn.title = isMax ? 'Restore Down' : 'Maximize';
        });
    }

    const deleteClose = document.getElementById('deleteProgramClose');
    const deleteCancel = document.getElementById('deleteProgramCancel');
    const deleteConfirm = document.getElementById('deleteProgramConfirm');
    const deleteModal = document.getElementById('deleteProgramModal');
    if (deleteClose) deleteClose.addEventListener('click', closeDeleteProgramModal);
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteProgramModal);
    if (deleteConfirm) {
        resetDeleteProgramConfirmButton();
        deleteConfirm.addEventListener('click', confirmDeleteProgram);
    }
    if (deleteModal) {
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) closeDeleteProgramModal();
        });
    }

    const viewProgramClose = document.getElementById('viewProgramClose');
    const viewProgramMaximize = document.getElementById('viewProgramMaximize');
    const viewProgramBox = document.getElementById('viewProgramBox');
    const viewProgramModal = document.getElementById('viewProgramModal');

    if (viewProgramMaximize && viewProgramBox) {
        viewProgramMaximize.addEventListener('click', (event) => {
            event.stopPropagation();
            viewProgramBox.classList.toggle('schol-modal-maximized');
            const isMax = viewProgramBox.classList.contains('schol-modal-maximized');
            viewProgramMaximize.textContent = isMax ? '⧉' : '□';
            viewProgramMaximize.title = isMax ? 'Restore Down' : 'Maximize';
            if (viewProgramModal) {
                viewProgramModal.classList.toggle('schol-modal-overlay-maximized', isMax);
            }
        });
    }

    if (viewProgramClose && viewProgramModal) {
        viewProgramClose.addEventListener('click', () => {
            viewProgramModal.style.display = 'none';
            resetViewProgramModalSize();
        });
        viewProgramModal.addEventListener('click', (event) => {
            if (event.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
                resetViewProgramModalSize();
            }
        });
    }

    try {
        if (typeof window.showLoading === 'function') window.showLoading();
        await loadProgramMeta();
        await loadPrograms();
    } catch (error) {
        showToast(error.message || 'Failed to load schedule programs.', 'error');
        tableBody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">Unable to load schedule programs.</td></tr>';
    } finally {
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
});

window.editScholarshipProgram = editProgram;
