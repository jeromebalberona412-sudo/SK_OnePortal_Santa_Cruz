let schedulePrograms = [];
let programMeta = null;
let editingProgramId = null;
let pendingDeleteProgramId = null;
let reqGroupCounter = 0;
let docReqCounter = 0;

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
}

function bindBuilderTabs() {
    document.querySelectorAll('.sch-program-tab').forEach((btn) => {
        btn.addEventListener('click', () => switchBuilderTab(btn.dataset.schTab));
    });
}

function createDocRequirementCard(req = {}) {
    const id = req.id || `doc_req_${++docReqCounter}`;
    const name = req.name || '';
    const fileType = req.file_type || 'pdf';
    const maxSize = req.max_size_mb ?? 5;
    const description = req.description || '';
    const required = req.required !== false;

    return `
        <div class="sch-doc-req-card" data-doc-req="${id}">
            <div class="sch-doc-req-head">
                <strong>Document Requirement</strong>
                <button type="button" class="sch-req-remove-group sch-doc-req-remove">Remove</button>
            </div>
            <div class="schol-schedule-grid schol-schedule-grid-2">
                <div class="schol-field schol-field-full">
                    <label>Requirement Name <span class="schol-req">*</span></label>
                    <input type="text" class="schol-input sch-doc-req-name" value="${escapeHtml(name)}" placeholder="e.g. Certificate of Enrollment">
                </div>
                <div class="schol-field">
                    <label>Requirement Type</label>
                    <select class="schol-input sch-doc-req-type">
                        <option value="pdf" ${fileType === 'pdf' ? 'selected' : ''}>PDF</option>
                        <option value="image" ${fileType === 'image' ? 'selected' : ''}>Image</option>
                        <option value="pdf_or_image" ${fileType === 'pdf_or_image' ? 'selected' : ''}>PDF or Image</option>
                    </select>
                </div>
                <div class="schol-field">
                    <label>Max File Size (MB)</label>
                    <input type="number" class="schol-input sch-doc-req-size" value="${escapeHtml(String(maxSize))}" min="1" max="10" step="1">
                </div>
                <div class="schol-field schol-field-full">
                    <label>Description</label>
                    <input type="text" class="schol-input sch-doc-req-desc" value="${escapeHtml(description)}" placeholder="Optional instructions for applicants">
                </div>
                <div class="schol-field">
                    <label class="schol-checkbox-label"><input type="checkbox" class="sch-doc-req-required" ${required ? 'checked' : ''}> Required</label>
                </div>
            </div>
        </div>`;
}

function renderDocumentRequirements(list = []) {
    const container = document.getElementById('schDocReqContainer');
    if (!container) return;
    docReqCounter = 0;
    const items = list.length ? list : [{ name: '', file_type: 'pdf', required: true, max_size_mb: 5 }];
    container.innerHTML = items.map((item) => createDocRequirementCard(item)).join('');
    bindDocumentRequirementEvents(container);
}

function bindDocumentRequirementEvents(container) {
    container.querySelectorAll('.sch-doc-req-remove').forEach((btn) => {
        btn.onclick = () => {
            const cards = container.querySelectorAll('[data-doc-req]');
            if (cards.length <= 1) {
                showToast('At least one requirement card must remain.', 'error');
                return;
            }
            btn.closest('[data-doc-req]')?.remove();
        };
    });
}

function collectDocumentRequirements() {
    const items = [];
    document.querySelectorAll('#schDocReqContainer [data-doc-req]').forEach((card) => {
        const name = card.querySelector('.sch-doc-req-name')?.value?.trim() || '';
        if (!name) return;
        items.push({
            id: card.dataset.docReq || `doc_req_${Date.now()}`,
            name,
            file_type: card.querySelector('.sch-doc-req-type')?.value || 'pdf',
            max_size_mb: Number(card.querySelector('.sch-doc-req-size')?.value || 5),
            required: Boolean(card.querySelector('.sch-doc-req-required')?.checked),
            description: card.querySelector('.sch-doc-req-desc')?.value?.trim() || '',
        });
    });
    return items;
}

function documentRequirementsToFileQuestions(requirements) {
    return requirements.map((req) => ({
        id: req.id || `q_${Date.now()}_${Math.random().toString(36).slice(2, 7)}`,
        label: req.name,
        type: 'file',
        required: req.required !== false,
        options: [],
        file_type: req.file_type || 'pdf',
        max_size_mb: req.max_size_mb || 5,
        description: req.description || '',
    }));
}

function fileQuestionsToDocumentRequirements(questions) {
    return (questions || []).filter((q) => q.type === 'file').map((q) => ({
        id: q.id,
        name: q.label || q.name || 'Document',
        file_type: q.file_type || 'pdf',
        required: q.required !== false,
        max_size_mb: q.max_size_mb || 5,
        description: q.description || '',
    }));
}

function getApplicationTypeValue() {
    return document.querySelector('input[name="applicationType"]:checked')?.value || 'new_only';
}

function setApplicationTypeValue(value) {
    const radio = document.querySelector(`input[name="applicationType"][value="${value}"]`);
    if (radio) radio.checked = true;
}

function renderInlineProgramPreview() {
    const panel = document.getElementById('schProgramPreviewPanel');
    if (!panel) return;

    let customNonFile = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customNonFile = window.SpfbFormBuilder.getQuestions().filter((question) => question.type !== 'file');
    }

    const documentRequirements = collectDocumentRequirements();
    const fakeProgram = {
        program_name: document.getElementById('programName')?.value || programMeta?.program_name || 'Scholarship Program',
        program_type: document.getElementById('programType')?.value || '',
        committee: document.getElementById('programCommittee')?.value || '',
        participation_quantity: document.getElementById('participationQty')?.value || '',
        start_date: document.getElementById('schedStartDate')?.value || '',
        end_date: document.getElementById('schedEndDate')?.value || '',
        announcement: document.getElementById('spfbAnnouncement')?.value || '',
        kk_profiling_fields: Array.from(document.querySelectorAll('.kk-profiling-field:checked')).map((el) => el.value),
        custom_questions: [
            ...documentRequirementsToFileQuestions(documentRequirements),
            ...customNonFile,
        ],
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

function collectCheckedValues(selector) {
    const values = [];
    document.querySelectorAll(`${selector}:checked`).forEach((checkbox) => {
        values.push(checkbox.value);
    });
    return values;
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

    const youthClassifications = collectCheckedValues('.sch-eligibility-classification');
    const youthAgeGroups = collectCheckedValues('.sch-eligibility-age-group');
    const educationLevels = collectCheckedValues('.sch-eligibility-education');

    if (youthClassifications.length || youthAgeGroups.length || educationLevels.length) {
        details.eligibility = {
            youth_classifications: youthClassifications,
            youth_age_groups: youthAgeGroups,
            education_levels: educationLevels,
        };
    }

    const schoolYear = document.getElementById('schoolYear')?.value?.trim() || '';
    const semester = document.getElementById('programSemester')?.value?.trim() || '';
    const applicationType = getApplicationTypeValue();
    const programDescription = document.getElementById('programDescription')?.value?.trim() || '';
    const documentRequirements = collectDocumentRequirements();

    if (schoolYear) details.school_year = schoolYear;
    if (semester) details.semester = semester;
    if (applicationType) details.application_type = applicationType;
    if (programDescription) details.program_description = programDescription;
    if (documentRequirements.length) details.document_requirements = documentRequirements;

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

    const eligibility = data.eligibility || {};
    const setEligibility = (selector, values) => {
        const allowed = new Set(Array.isArray(values) ? values : []);
        document.querySelectorAll(selector).forEach((checkbox) => {
            checkbox.checked = allowed.has(checkbox.value);
        });
    };

    setEligibility('.sch-eligibility-classification', eligibility.youth_classifications);
    setEligibility('.sch-eligibility-age-group', eligibility.youth_age_groups);
    setEligibility('.sch-eligibility-education', eligibility.education_levels);

    const schoolYearEl = document.getElementById('schoolYear');
    const semesterEl = document.getElementById('programSemester');
    const descEl = document.getElementById('programDescription');
    if (schoolYearEl) schoolYearEl.value = data.school_year || '';
    if (semesterEl) semesterEl.value = data.semester || '';
    if (descEl) descEl.value = data.program_description || '';
    setApplicationTypeValue(data.application_type || 'new_only');

    const docReqs = data.document_requirements?.length
        ? data.document_requirements
        : fileQuestionsToDocumentRequirements(customQuestions);
    renderDocumentRequirements(docReqs);
}

function resetScholarshipDetailsForm() {
    renderRequirementGroups([]);
    ['schSubmissionStart', 'schSubmissionEnd', 'schVerificationStart', 'schVerificationEnd'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.querySelectorAll('.sch-eligibility-classification, .sch-eligibility-age-group, .sch-eligibility-education').forEach((checkbox) => {
        checkbox.checked = false;
    });
    const schoolYearEl = document.getElementById('schoolYear');
    const semesterEl = document.getElementById('programSemester');
    const descEl = document.getElementById('programDescription');
    if (schoolYearEl) schoolYearEl.value = '';
    if (semesterEl) semesterEl.value = '';
    if (descEl) descEl.value = '';
    setApplicationTypeValue('new_only');
    renderDocumentRequirements([]);
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
    work_status: 'Work Status',
    sk_voter: 'Registered SK Voter',
    sk_voted: 'Voted Last Election',
};

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

function showToast(msg, type) {
    const toastEl = document.getElementById('safToast');
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.style.display = 'flex';
    toastEl.style.background = type === 'error' ? '#ef4444' : '#22c55e';
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { toastEl.style.display = 'none'; }, 2800);
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

    const typeEl = document.getElementById('programType');
    const nameEl = document.getElementById('programName');
    const committeeEl = document.getElementById('programCommittee');

    if (typeEl) {
        typeEl.value = programMeta?.program_type || 'Equitable Access to Quality Education';
    }
    if (nameEl) {
        nameEl.value = programMeta?.program_name || programMeta?.program_type || '';
    }
    if (committeeEl) {
        committeeEl.value = programMeta?.committee || 'Education Committee';
    }
}

async function loadPrograms() {
    const response = await apiFetch('/api/schedule-programs?letter=A');
    schedulePrograms = Array.isArray(response.data) ? response.data : [];
    renderFormsTable();
}

function resetModalForm() {
    editingProgramId = null;

    const participationQty = document.getElementById('participationQty');
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const status = document.getElementById('programStatus');
    const announcement = document.getElementById('spfbAnnouncement');
    const announcementCount = document.getElementById('spfbAnnouncementCount');

    if (participationQty) participationQty.value = '';
    if (startDate) startDate.value = '';
    if (endDate) endDate.value = '';
    if (status) status.value = 'open';
    if (announcement) announcement.value = '';
    if (announcementCount) announcementCount.textContent = '0';

    document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
        const defaults = window.ScholarshipSystemFields?.DEFAULT_KK_FIELDS || [];
        checkbox.checked = defaults.includes(checkbox.value);
    });

    if (window.SpfbFormBuilder) {
        window.SpfbFormBuilder.reset();
    }

    resetScholarshipDetailsForm();

    if (programMeta) {
        const typeEl = document.getElementById('programType');
        const nameEl = document.getElementById('programName');
        const committeeEl = document.getElementById('programCommittee');
        if (typeEl) typeEl.value = programMeta.program_type || '';
        if (nameEl) nameEl.value = programMeta.program_name || programMeta.program_type || '';
        if (committeeEl) committeeEl.value = programMeta.committee || '';
    }

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
        if (schedulePrograms.length > 0) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            showToast('A scholarship program already exists. Edit the existing program instead.', 'error');
            return;
        }

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

        return `
            <tr>
                <td>${escapeHtml(program.program_name)}</td>
                <td>${escapeHtml(program.program_type)}</td>
                <td>${escapeHtml(program.committee)}</td>
                <td>${escapeHtml(program.participation_quantity ?? 'N/A')}</td>
                <td>${escapeHtml(program.start_date)}</td>
                <td>${escapeHtml(program.end_date)}</td>
                <td><span class="schol-pill ${statusClass}">${formatStatusLabel(status)}</span></td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button type="button" class="prog-btn prog-btn-view" data-form-view="${program.id}">View</button>
                        <button type="button" class="prog-btn prog-btn-edit" data-form-edit="${program.id}">Edit</button>
                        <button type="button" class="prog-btn prog-btn-delete" data-form-delete="${program.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    tableBody.querySelectorAll('[data-form-view]').forEach((btn) => {
        btn.addEventListener('click', () => openFormPreview(btn.getAttribute('data-form-view')));
    });
    tableBody.querySelectorAll('[data-form-edit]').forEach((btn) => {
        btn.addEventListener('click', () => editProgram(btn.getAttribute('data-form-edit')));
    });
    tableBody.querySelectorAll('[data-form-delete]').forEach((btn) => {
        btn.addEventListener('click', () => openDeleteProgramModal(btn.getAttribute('data-form-delete')));
    });
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
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const status = document.getElementById('programStatus');
    const typeEl = document.getElementById('programType');
    const nameEl = document.getElementById('programName');
    const committeeEl = document.getElementById('programCommittee');
    const announcementEl = document.getElementById('spfbAnnouncement');
    const announcementCountEl = document.getElementById('spfbAnnouncementCount');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (participationQty) participationQty.value = program.participation_quantity ?? '';
    if (startDate) startDate.value = program.start_date || '';
    if (endDate) endDate.value = program.end_date || '';
    if (status) status.value = resolveProgramStatus(program);
    if (typeEl) typeEl.value = program.program_type || '';
    if (nameEl) nameEl.value = program.program_name || '';
    if (committeeEl) committeeEl.value = program.committee || '';
    if (announcementEl) {
        announcementEl.value = program.announcement || '';
        if (announcementCountEl) announcementCountEl.textContent = String(announcementEl.value.length);
    }

    const allQuestions = program.custom_questions || [];
    const customOnly = allQuestions.filter((question) => question.type !== 'file');

    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.setQuestions === 'function') {
        window.SpfbFormBuilder.setQuestions(customOnly);
    }

    populateScholarshipDetails(program.scholarship_details || null, allQuestions);

    const kkFields = program.kk_profiling_fields || [];
    document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
        checkbox.checked = kkFields.includes(checkbox.value);
    });

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

const FILE_TYPE_LABELS = {
    pdf: 'PDF',
    image: 'Image',
    pdf_or_image: 'PDF or Image',
};

function renderProgramViewHtml(program) {
    const status = resolveProgramStatus(program);
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
    };
    const statusStyle = statusColors[status] || statusColors.open;
    const kkFields = program.kk_profiling_fields || [];
    const allQuestions = program.custom_questions || [];
    const details = program.scholarship_details || {};
    const groups = Array.isArray(details.requirement_groups) ? details.requirement_groups : [];
    const fileQuestions = allQuestions.filter((question) => question.type === 'file');
    const customQuestions = allQuestions.filter((question) => question.type !== 'file');
    const documentRequirements = details.document_requirements?.length
        ? details.document_requirements
        : fileQuestionsToDocumentRequirements(fileQuestions);

    const kkList = kkFields.length
        ? `<ul class="sch-view-req-list">${kkFields.map((field) => `<li>${escapeHtml(KK_FIELD_LABELS[field] || field)}</li>`).join('')}</ul>`
        : '<p class="sch-view-muted">No KK Profiling fields selected.</p>';

    let announcementsHtml = '';
    if (program.announcement) {
        announcementsHtml += renderViewAnnouncementCard(
            'Announcement',
            `<p class="sch-view-announcement-text">${escapeHtml(program.announcement)}</p>`
        );
    }

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
    const eligibility = details.eligibility || {};
    const eligibilityClassifications = (eligibility.youth_classifications || []).filter(Boolean);
    const eligibilityAgeGroups = (eligibility.youth_age_groups || []).filter(Boolean);
    const eligibilityEducation = (eligibility.education_levels || []).filter(Boolean);
    const formatEligibilityList = (items, fallback) => items.length
        ? `<ul class="sch-view-req-list">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
        : `<p class="sch-view-muted">${fallback}</p>`;

    const questionsHtml = documentRequirements.length
        ? documentRequirements.map((req, index) => `
            <div class="sch-view-question-card">
                <div class="sch-view-question-label">
                    ${index + 1}. ${escapeHtml(req.name)}
                    ${req.required !== false ? '<span class="sch-view-required">*</span>' : ''}
                </div>
                <div class="sch-view-question-type">Type: ${escapeHtml(FILE_TYPE_LABELS[req.file_type] || req.file_type || 'PDF')}${req.max_size_mb ? ` · Max ${escapeHtml(String(req.max_size_mb))} MB` : ''}</div>
                ${req.description ? `<div class="sch-view-muted">${escapeHtml(req.description)}</div>` : ''}
            </div>
        `).join('')
        : '<div class="sch-view-empty-box">No upload requirements added.</div>';

    const customQuestionsHtml = customQuestions.length
        ? customQuestions.map((question, index) => `
            <div class="sch-view-question-card">
                <div class="sch-view-question-label">
                    ${index + 1}. ${escapeHtml(question.label)}
                    ${question.required ? '<span class="sch-view-required">*</span>' : ''}
                </div>
                <div class="sch-view-question-type">Type: ${escapeHtml(getTypeLabel(question.type))}</div>
            </div>
        `).join('')
        : '';

    const quickGuidelinesHtml = `
        <div class="schol-schedule-card sch-view-section">
            <h4 class="schol-schedule-title">Quick Guidelines</h4>
            <p class="sch-view-muted">Built-in 6-step guide shown to all applicants.</p>
            <ol class="sch-qg-official-simple-list">
                <li>Complete the scholarship application form.</li>
                <li>Upload all required documents.</li>
                <li>Review your information.</li>
                <li>Submit your application.</li>
                <li>Wait for evaluation.</li>
                <li>Monitor your application status.</li>
            </ol>
        </div>`;

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
                        <label>Program Type</label>
                        <div class="sch-view-value">${escapeHtml(program.program_type)}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>Committee</label>
                        <div class="sch-view-value">${escapeHtml(program.committee || '—')}</div>
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
                    <div class="sch-view-field">
                        <label>Maximum Beneficiaries</label>
                        <div class="sch-view-value">${escapeHtml(program.participation_quantity ?? 'N/A')}</div>
                    </div>
                    ${details.program_description ? `
                    <div class="sch-view-field sch-view-field-full">
                        <label>Program Description</label>
                        <div class="sch-view-value sch-view-value-pre">${escapeHtml(details.program_description)}</div>
                    </div>` : ''}
                </div>
            </div>

            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Application Window Schedule</h4>
                <div class="sch-view-grid">
                    <div class="sch-view-field">
                        <label>Start Date</label>
                        <div class="sch-view-value">${formatIsoDateDisplay(program.start_date)}</div>
                    </div>
                    <div class="sch-view-field">
                        <label>End Date</label>
                        <div class="sch-view-value">${formatIsoDateDisplay(program.end_date)}</div>
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
                        ${formatEligibilityList(eligibilityClassifications, 'Any classification (Senior High and College only).')}
                    </div>
                    <div class="sch-view-field sch-view-field-full">
                        <label>Youth Age Group</label>
                        ${formatEligibilityList(eligibilityAgeGroups, 'Any age group (Senior High and College only).')}
                    </div>
                    <div class="sch-view-field sch-view-field-full">
                        <label>Educational Background</label>
                        ${formatEligibilityList(eligibilityEducation, 'Senior High School and College Level.')}
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">1. Personal Information (KK Profiling)</h4>
                <p class="sch-view-kk-note">Selected fields are auto-filled from the applicant's KK Profile and displayed as read-only.</p>
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

            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Uploading of Requirements</h4>
                <div class="sch-view-questions">${questionsHtml}</div>
            </div>

            ${customQuestionsHtml ? `
            <div class="schol-schedule-card sch-view-section">
                <h4 class="schol-schedule-title">Custom Questions</h4>
                <div class="sch-view-questions">${customQuestionsHtml}</div>
            </div>` : ''}

            ${quickGuidelinesHtml}
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
    if (nameEl) {
        nameEl.textContent = program ? `"${program.program_name}"` : '';
    }
    if (deleteModal) deleteModal.style.display = 'flex';
}

function closeDeleteProgramModal() {
    pendingDeleteProgramId = null;
    const deleteModal = document.getElementById('deleteProgramModal');
    if (deleteModal) deleteModal.style.display = 'none';
}

async function confirmDeleteProgram() {
    if (!pendingDeleteProgramId) return;

    const confirmBtn = document.getElementById('deleteProgramConfirm');
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Delete Program';

    if (confirmBtn) {
        confirmBtn.disabled = true;
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
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = defaultHtml;
        }
    }
}

async function handleSave() {
    const startDate = document.getElementById('schedStartDate')?.value?.trim();
    const endDate = document.getElementById('schedEndDate')?.value?.trim();
    const status = document.getElementById('programStatus')?.value || 'open';
    const participationQtyRaw = document.getElementById('participationQty')?.value?.trim();
    const announcement = document.getElementById('spfbAnnouncement')?.value?.trim() || '';
    const schoolYear = document.getElementById('schoolYear')?.value?.trim();
    const semester = document.getElementById('programSemester')?.value?.trim();

    if (!schoolYear) {
        showToast('Please select a school year.', 'error');
        switchBuilderTab('details');
        return;
    }

    if (!semester) {
        showToast('Please select a semester (1st or 2nd only).', 'error');
        switchBuilderTab('details');
        return;
    }

    if (!startDate || !endDate) {
        showToast('Please select application start and end dates.', 'error');
        switchBuilderTab('details');
        return;
    }

    let participationQuantity = null;
    if (participationQtyRaw !== '') {
        const qtyNum = parseInt(participationQtyRaw, 10);
        if (Number.isNaN(qtyNum) || qtyNum < 0) {
            showToast('Maximum beneficiaries cannot be negative.', 'error');
            return;
        }
        participationQuantity = qtyNum;
    }

    const documentRequirements = collectDocumentRequirements();
    if (!documentRequirements.length) {
        showToast('Add at least one document requirement in the Requirements tab.', 'error');
        switchBuilderTab('requirements');
        return;
    }

    let customQuestionsNonFile = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customQuestionsNonFile = window.SpfbFormBuilder.getQuestions().filter((question) => question.type !== 'file');
    }

    const fileQuestions = documentRequirementsToFileQuestions(documentRequirements);
    const customQuestions = [...fileQuestions, ...customQuestionsNonFile];

    const kkProfilingFields = [];
    document.querySelectorAll('.kk-profiling-field:checked').forEach((checkbox) => {
        kkProfilingFields.push(checkbox.value);
    });

    const payload = {
        start_date: startDate,
        end_date: endDate,
        status,
        participation_quantity: participationQuantity,
        announcement,
        scholarship_details: collectScholarshipDetails(),
        kk_profiling_fields: kkProfilingFields,
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
    renderDocumentRequirements([]);

    const addDocReqBtn = document.getElementById('schAddDocReqBtn');
    if (addDocReqBtn) {
        addDocReqBtn.addEventListener('click', () => {
            const container = document.getElementById('schDocReqContainer');
            if (!container) return;
            container.insertAdjacentHTML('beforeend', createDocRequirementCard({ name: '', file_type: 'pdf', required: true, max_size_mb: 5 }));
            bindDocumentRequirementEvents(container);
        });
    }

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

    const addReqGroupBtn = document.getElementById('schAddReqGroupBtn');
    if (addReqGroupBtn) {
        addReqGroupBtn.addEventListener('click', () => {
            const container = document.getElementById('schReqGroupsContainer');
            if (!container) return;
            container.insertAdjacentHTML('beforeend', createRequirementGroupCard({ title: '', items: [''] }));
            bindRequirementGroupEvents(container);
        });
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

    const selectAllKKBtn = document.getElementById('selectAllKKFields');
    const clearAllKKBtn = document.getElementById('clearAllKKFields');
    if (selectAllKKBtn) {
        selectAllKKBtn.addEventListener('click', () => {
            document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
                checkbox.checked = true;
            });
        });
    }
    if (clearAllKKBtn) {
        clearAllKKBtn.addEventListener('click', () => {
            document.querySelectorAll('.kk-profiling-field').forEach((checkbox) => {
                checkbox.checked = false;
            });
        });
    }

    const deleteClose = document.getElementById('deleteProgramClose');
    const deleteCancel = document.getElementById('deleteProgramCancel');
    const deleteConfirm = document.getElementById('deleteProgramConfirm');
    const deleteModal = document.getElementById('deleteProgramModal');
    if (deleteClose) deleteClose.addEventListener('click', closeDeleteProgramModal);
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteProgramModal);
    if (deleteConfirm) deleteConfirm.addEventListener('click', confirmDeleteProgram);
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
