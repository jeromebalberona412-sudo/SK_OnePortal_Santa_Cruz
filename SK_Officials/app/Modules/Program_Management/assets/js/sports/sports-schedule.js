let schedulePrograms = [];
const PROGRAM_LETTER = 'I';
let programMeta = null;
let abyipGate = window.sportsAbyipGate || null;
let editingProgramId = null;
let pendingDeleteProgramId = null;
let availableTerms = [];
let scheduleFilterTermId = '';
let scheduleFilterYear = '';
let scheduleSearchQuery = '';

const SPORTS_EXCLUDED_KK_FIELDS = [
    'education',
    'current_school',
    'course_strand',
    'work_status',
    'sk_voter',
    'sk_voted',
];

const DEFAULT_SPORTS_KK_FIELDS = [
    'last_name', 'first_name', 'middle_name', 'suffix',
    'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
    'region', 'province', 'city', 'barangay', 'purok_zone',
    'youth_classification', 'youth_age_group',
];

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
};

function resolveDefaultAgeClassifications(sportKey) {
    if (window.SportsAgeClassifications?.getDefaultAgeClassificationsForSport) {
        return window.SportsAgeClassifications.getDefaultAgeClassificationsForSport(sportKey);
    }

    return [];
}

function getActiveSportKey() {
    if (window.SportsAgeClassifications?.getSelectedSportKey) {
        return window.SportsAgeClassifications.getSelectedSportKey();
    }

    return document.getElementById('sportsDisciplineKey')?.value?.trim() || '';
}

const DEFAULT_TEAM_NAME_QUESTION = {
    id: 'sys_team_name',
    label: 'Team Name',
    type: 'text',
    options: [],
    required: true,
    system_default: true,
    field_key: 'team_name',
};

const SPORT_OPTIONS = {
    basketball: 'Basketball',
    volleyball: 'Volleyball',
    other: 'Other',
};

const SPORT_KEYS = Object.keys(SPORT_OPTIONS);

const SPORTS_AGE_MIN = 15;
const SPORTS_AGE_MAX = 30;

function generateClassificationId(name) {
    return `cls_${String(name || 'division').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '')}_${Date.now()}`;
}

function clampSportsAge(rawValue) {
    if (rawValue === '' || rawValue === null || rawValue === undefined) {
        return null;
    }

    const num = parseInt(String(rawValue), 10);
    if (Number.isNaN(num)) {
        return null;
    }

    return Math.min(SPORTS_AGE_MAX, Math.max(SPORTS_AGE_MIN, num));
}

function formatClassificationAgeValue(value) {
    if (value === '' || value === null || value === undefined) {
        return '';
    }

    return String(value);
}

function getAgeClassificationsEmptyMessage() {
    if (getActiveSportKey() === 'other') {
        return `No age classifications yet. Click "+ Add Classification" to create your own brackets (ages ${SPORTS_AGE_MIN}–${SPORTS_AGE_MAX}).`;
    }

    return 'No age classifications yet. Click "Use Default Age Brackets" or add one.';
}

function renderAgeClassificationsTable(classifications = []) {
    const tbody = document.getElementById('sportsAgeClassificationsBody');
    if (!tbody) return;

    if (!classifications.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="saf-table-empty">${getAgeClassificationsEmptyMessage()}</td></tr>`;
        return;
    }

    tbody.innerHTML = classifications.map((item) => `
        <tr data-classification-id="${escapeHtml(item.id)}">
            <td><input type="text" class="sports-cls-name" value="${escapeHtml(item.name)}" placeholder="Classification name"></td>
            <td><input type="number" class="sports-cls-min" value="${escapeHtml(formatClassificationAgeValue(item.min_age))}" min="${SPORTS_AGE_MIN}" max="${SPORTS_AGE_MAX}" placeholder="${SPORTS_AGE_MIN}"></td>
            <td><input type="number" class="sports-cls-max" value="${escapeHtml(formatClassificationAgeValue(item.max_age))}" min="${SPORTS_AGE_MIN}" max="${SPORTS_AGE_MAX}" placeholder="${SPORTS_AGE_MAX}"></td>
            <td><input type="checkbox" class="sports-cls-open" ${item.is_open ? 'checked' : ''}></td>
            <td><button type="button" class="sports-age-remove-btn" data-remove-classification="${escapeHtml(item.id)}">Remove</button></td>
        </tr>
    `).join('');
}

function normalizeRowAgeInputs(row) {
    const minInput = row.querySelector('.sports-cls-min');
    const maxInput = row.querySelector('.sports-cls-max');
    if (!minInput || !maxInput) return;

    if (minInput.value !== '') {
        const clampedMin = clampSportsAge(minInput.value);
        if (clampedMin !== null) {
            minInput.value = String(clampedMin);
        }
    }

    if (maxInput.value !== '') {
        const clampedMax = clampSportsAge(maxInput.value);
        if (clampedMax !== null) {
            maxInput.value = String(clampedMax);
        }
    }

    if (minInput.value !== '' && maxInput.value !== '') {
        const minAge = parseInt(minInput.value, 10);
        const maxAge = parseInt(maxInput.value, 10);
        if (!Number.isNaN(minAge) && !Number.isNaN(maxAge) && minAge > maxAge) {
            maxInput.value = String(minAge);
        }
    }
}

function validateAgeClassifications(classifications) {
    if (!classifications.length) {
        return 'Please add at least one age classification.';
    }

    for (const item of classifications) {
        if (item.min_age === null || item.max_age === null) {
            return `Please set minimum and maximum age (15–30) for "${item.name}".`;
        }

        if (item.min_age < SPORTS_AGE_MIN || item.min_age > SPORTS_AGE_MAX) {
            return `"${item.name}": minimum age must be between ${SPORTS_AGE_MIN} and ${SPORTS_AGE_MAX}.`;
        }

        if (item.max_age < SPORTS_AGE_MIN || item.max_age > SPORTS_AGE_MAX) {
            return `"${item.name}": maximum age must be between ${SPORTS_AGE_MIN} and ${SPORTS_AGE_MAX}.`;
        }

        if (item.min_age > item.max_age) {
            return `"${item.name}": minimum age cannot exceed maximum age.`;
        }
    }

    return null;
}

function getAgeClassificationsFromForm() {
    const rows = document.querySelectorAll('#sportsAgeClassificationsBody tr[data-classification-id]');
    const classifications = [];

    rows.forEach((row) => {
        const name = row.querySelector('.sports-cls-name')?.value?.trim() || '';
        const minRaw = row.querySelector('.sports-cls-min')?.value ?? '';
        const maxRaw = row.querySelector('.sports-cls-max')?.value ?? '';
        const isOpen = row.querySelector('.sports-cls-open')?.checked ?? true;

        if (!name) return;

        classifications.push({
            id: row.getAttribute('data-classification-id') || generateClassificationId(name),
            name,
            min_age: clampSportsAge(minRaw),
            max_age: clampSportsAge(maxRaw),
            is_open: isOpen,
        });
    });

    return classifications;
}

function setAgeClassificationsForm(details) {
    const maxTeamEl = document.getElementById('sportsMaxTeamMembers');
    const sportKey = details?.sport_key || getActiveSportKey();
    const classifications = details?.age_classifications?.length
        ? details.age_classifications
        : resolveDefaultAgeClassifications(sportKey);

    if (maxTeamEl) {
        maxTeamEl.value = String(details?.max_team_members ?? 12);
    }

    renderAgeClassificationsTable(classifications);
}

function resetAgeClassificationsForm() {
    const sportKey = getActiveSportKey();
    setAgeClassificationsForm({
        sport_key: sportKey,
        max_team_members: 12,
        age_classifications: resolveDefaultAgeClassifications(sportKey),
    });
}

function ensureDefaultTeamNameQuestion(questions) {
    const list = Array.isArray(questions) ? [...questions] : [];
    const hasTeamName = list.some((question) => {
        const fieldKey = String(question?.field_key || '');
        const label = String(question?.label || '').trim().toLowerCase();
        return fieldKey === 'team_name' || label === 'team name';
    });

    if (!hasTeamName) {
        list.unshift({ ...DEFAULT_TEAM_NAME_QUESTION });
    }

    return list;
}

function loadDefaultAgeBrackets() {
    const sportKey = getActiveSportKey();

    if (sportKey === 'other') {
        renderAgeClassificationsTable([]);
        showToast('Other sports have no default brackets. Add your own classifications below.', 'error');
        return;
    }

    renderAgeClassificationsTable(resolveDefaultAgeClassifications(sportKey));
}

function openAllClassifications() {
    document.querySelectorAll('#sportsAgeClassificationsBody .sports-cls-open').forEach((checkbox) => {
        checkbox.checked = true;
    });
}

function getSportsDetailsPayload() {
    const maxTeamRaw = document.getElementById('sportsMaxTeamMembers')?.value?.trim() || '12';
    let maxTeamMembers = parseInt(maxTeamRaw, 10);
    if (Number.isNaN(maxTeamMembers) || maxTeamMembers < 1) maxTeamMembers = 1;
    if (maxTeamMembers > 12) maxTeamMembers = 12;

    const classifications = getAgeClassificationsFromForm();
    const openAll = classifications.length > 0 && classifications.every((item) => item.is_open);
    const sportKey = document.getElementById('sportsDisciplineKey')?.value?.trim() || '';
    const otherSportName = document.getElementById('sportsOtherName')?.value?.trim() || '';

    return {
        sport_key: sportKey,
        sport_label: sportKey === 'other' ? otherSportName : (SPORT_OPTIONS[sportKey] || ''),
        other_sport_name: sportKey === 'other' ? otherSportName : null,
        open_all: openAll,
        max_team_members: maxTeamMembers,
        min_team_members: 1,
        age_classifications: classifications,
    };
}

function getProgramSportLabel(program) {
    return program?.sport_label
        || program?.sports_details?.sport_label
        || SPORT_OPTIONS[program?.sports_details?.sport_key]
        || program?.program_type
        || 'Sports Program';
}

function getProgramYear(program) {
    if (!program?.start_date) return new Date().getFullYear();
    return new Date(program.start_date).getFullYear();
}

function getTermById(termId) {
    return availableTerms.find((term) => term.id === termId) || null;
}

function yearsForTerm(term) {
    if (!term) return [];
    const years = [];
    for (let year = term.end_year; year >= term.start_year; year -= 1) {
        years.push(year);
    }
    return years.length ? years : [new Date().getFullYear()];
}

function programMatchesTerm(program, termId) {
    if (!termId) return true;
    const term = getTermById(termId);
    if (!term) return true;
    const year = getProgramYear(program);
    return year >= term.start_year && year <= term.end_year;
}

function programMatchesYear(program, yearValue) {
    if (!yearValue) return true;
    return getProgramYear(program) === Number(yearValue);
}

function programMatchesSearch(program, query) {
    const needle = String(query || '').trim().toLowerCase();
    if (!needle) return true;

    const status = resolveProgramStatus(program);
    const haystack = [
        getProgramSportLabel(program),
        program.participation_quantity,
        program.start_date,
        program.end_date,
        formatStatusLabel(status),
        program.program_type,
    ].map((value) => String(value ?? '').toLowerCase()).join(' ');

    return haystack.includes(needle);
}

function getFilteredSchedulePrograms() {
    return schedulePrograms.filter((program) => (
        programMatchesTerm(program, scheduleFilterTermId)
        && programMatchesYear(program, scheduleFilterYear)
        && programMatchesSearch(program, scheduleSearchQuery)
    ));
}

function populateScheduleTermFilter() {
    const select = document.getElementById('sportsScheduleTermFilter');
    if (!select) return;

    const previous = select.value || scheduleFilterTermId;
    select.innerHTML = '<option value="">All Terms</option>' + availableTerms.map((term) => (
        `<option value="${escapeHtml(term.id)}">${escapeHtml(term.label)}</option>`
    )).join('');

    const activeTerm = availableTerms.find((term) => term.is_active);
    const fallback = previous || '';
    scheduleFilterTermId = availableTerms.some((term) => term.id === previous) ? previous : fallback;
    select.value = scheduleFilterTermId;
}

function populateScheduleYearFilter() {
    const select = document.getElementById('sportsScheduleYearFilter');
    if (!select) return;

    const previous = select.value || scheduleFilterYear;
    const term = scheduleFilterTermId ? getTermById(scheduleFilterTermId) : null;
    const years = term
        ? yearsForTerm(term)
        : [...new Set(availableTerms.flatMap((item) => yearsForTerm(item)))].sort((a, b) => b - a);

    select.innerHTML = '<option value="">All Years</option>' + years.map((year) => (
        `<option value="${year}">${year}</option>`
    )).join('');

    scheduleFilterYear = years.includes(Number(previous)) ? previous : '';
    select.value = scheduleFilterYear;
}

async function loadScheduleFilterMeta() {
    const response = await apiFetch('/api/dashboard/stats?summary=1');
    const data = response?.data || {};
    availableTerms = Array.isArray(data.available_terms) ? data.available_terms : [];
    populateScheduleTermFilter();
    populateScheduleYearFilter();
}

function bindScheduleFilters() {
    const termFilter = document.getElementById('sportsScheduleTermFilter');
    const yearFilter = document.getElementById('sportsScheduleYearFilter');
    const searchInput = document.getElementById('sportsScheduleSearch');

    termFilter?.addEventListener('change', () => {
        scheduleFilterTermId = termFilter.value || '';
        populateScheduleYearFilter();
        renderFormsTable();
        updateCreateButtonState();
    });

    yearFilter?.addEventListener('change', () => {
        scheduleFilterYear = yearFilter.value || '';
        renderFormsTable();
        updateCreateButtonState();
    });

    searchInput?.addEventListener('input', () => {
        scheduleSearchQuery = searchInput.value || '';
        renderFormsTable();
    });
}

function getUsedSportKeysForYear(year) {
    return schedulePrograms
        .filter((program) => getProgramYear(program) === year)
        .map((program) => String(program.sports_details?.sport_key || program.sport_key || '').toLowerCase())
        .filter(Boolean);
}

function allSportsCreatedForYear(year = new Date().getFullYear()) {
    const used = new Set(getUsedSportKeysForYear(year));
    return SPORT_KEYS.every((key) => used.has(key));
}

function resolveCreateButtonYear() {
    if (scheduleFilterYear) {
        return Number(scheduleFilterYear);
    }
    if (scheduleFilterTermId) {
        const term = getTermById(scheduleFilterTermId);
        if (term) {
            const currentYear = new Date().getFullYear();
            if (currentYear >= term.start_year && currentYear <= term.end_year) {
                return currentYear;
            }
            return term.end_year;
        }
    }
    return new Date().getFullYear();
}

function updateCreateButtonState() {
    const btn = document.getElementById('safOpenFormBtn');
    if (!btn) return;

    const blocked = allSportsCreatedForYear(resolveCreateButtonYear());
    btn.disabled = blocked;
    btn.title = blocked
        ? 'Basketball, Volleyball, and Other programs already exist for this year.'
        : '';
    btn.style.opacity = blocked ? '0.55' : '';
    btn.style.cursor = blocked ? 'not-allowed' : '';
}

function populateSportSelect({ locked = false, selectedKey = '', otherName = '' } = {}) {
    const select = document.getElementById('sportsDisciplineKey');
    const otherWrap = document.getElementById('sportsOtherNameWrap');
    const otherInput = document.getElementById('sportsOtherName');
    if (!select) return;

    const year = new Date().getFullYear();
    const usedKeys = new Set(getUsedSportKeysForYear(year));

    select.innerHTML = '<option value="">Select sport...</option>' + SPORT_KEYS.map((key) => {
        const taken = !locked && usedKeys.has(key);
        const disabled = taken ? ' disabled' : '';
        const suffix = taken ? ' (already created)' : '';
        return `<option value="${key}"${disabled}>${SPORT_OPTIONS[key]}${suffix}</option>`;
    }).join('');

    select.value = selectedKey || '';
    select.disabled = locked;

    if (otherInput) {
        otherInput.value = otherName || '';
        otherInput.disabled = locked;
    }

    if (otherWrap) {
        otherWrap.style.display = (selectedKey === 'other' || select.value === 'other') ? 'block' : 'none';
    }
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
                    <button type="button" class="row-actions-item row-actions-item-danger" data-action="archive" data-program-id="${programId}" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Archive</span>
                    </button>
                </div>
            </div>
        </td>
    `;
}

function renderAgeClassificationsPreview(details) {
    const classifications = details?.age_classifications || [];
    if (!classifications.length) {
        return '<div style="font-size:14px;color:#6b7280;">No age classifications configured.</div>';
    }

    return `
        <div style="display:grid;gap:10px;">
            ${classifications.map((item) => `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
                    <div>
                        <div style="font-weight:600;color:#111827;">${escapeHtml(item.name)}</div>
                        <div style="font-size:13px;color:#6b7280;">Ages ${escapeHtml(String(item.min_age))}–${escapeHtml(String(item.max_age))}</div>
                    </div>
                    <span style="font-size:12px;font-weight:700;padding:4px 12px;border-radius:999px;background:${item.is_open ? '#dcfce7' : '#fee2e2'};color:${item.is_open ? '#166534' : '#991b1b'};">
                        ${item.is_open ? 'Open' : 'Closed'}
                    </span>
                </div>
            `).join('')}
        </div>
        <div style="margin-top:12px;font-size:13px;color:#6b7280;">Max team members: ${escapeHtml(String(details?.max_team_members ?? 12))}</div>
    `;
}

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
    const response = await apiFetch(`/api/schedule-programs/meta?letter=${PROGRAM_LETTER}`);
    programMeta = response.data || null;
    if (response.abyip_gate) {
        abyipGate = response.abyip_gate;
    }

    if (programMeta?.sports_age_classifications) {
        window.SPORTS_AGE_CLASSIFICATIONS = programMeta.sports_age_classifications;
    }

    const typeEl = document.getElementById('programType');

    if (typeEl) {
        typeEl.value = programMeta?.program_type || 'Sports Development';
    }
}

async function loadPrograms() {
    const response = await apiFetch(`/api/schedule-programs?letter=${PROGRAM_LETTER}`);
    schedulePrograms = Array.isArray(response.data) ? response.data : [];
    renderFormsTable();
    updateCreateButtonState();
}

function collectKkProfilingFields() {
    return [...DEFAULT_SPORTS_KK_FIELDS];
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

    if (window.SpfbFormBuilder) {
        window.SpfbFormBuilder.reset();
        window.SpfbFormBuilder.setQuestions(ensureDefaultTeamNameQuestion([]));
    }

    resetAgeClassificationsForm();
    populateSportSelect();

    if (programMeta) {
        const typeEl = document.getElementById('programType');
        const committeeEl = document.getElementById('programCommittee');
        if (typeEl) typeEl.value = programMeta.program_type || '';
    }
}

function openModal(forEditId) {
    const modal = document.getElementById('scholarProgramModal');
    const modalBox = document.getElementById('scholarProgramBox');
    const maximizeBtn = document.getElementById('scholarProgramMaximize');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (!modal) return;

    if (!forEditId) {
        if (allSportsCreatedForYear()) {
            showToast('Basketball, Volleyball, and Other programs already exist for this year.', 'error');
            return;
        }

        resetModalForm();
        if (modalTitle) {
            modalTitle.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                Create Sports Program
            `;
        }
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    modal.classList.remove('schol-modal-maximized');
    if (modalBox) modalBox.classList.remove('schol-modal-maximized');
    if (maximizeBtn) {
        maximizeBtn.textContent = '□';
        maximizeBtn.title = 'Maximize';
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

    const filtered = getFilteredSchedulePrograms();
    const forms = [...filtered].sort((a, b) => {
        const nameA = getProgramSportLabel(a).toLowerCase();
        const nameB = getProgramSportLabel(b).toLowerCase();
        return nameA.localeCompare(nameB);
    });

    if (countEl) countEl.textContent = String(forms.length);

    if (!forms.length) {
        if (schedulePrograms.length && (scheduleFilterTermId || scheduleFilterYear || scheduleSearchQuery)) {
            tableBody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">No sports programs match the selected filters.</td></tr>';
            return;
        }
        if (window.SkAbyipNotice?.isPending(abyipGate)) {
            tableBody.innerHTML = '<tr>' + window.SkAbyipNotice.renderEmptyRow(6, abyipGate) + '</tr>';
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">No sports programs yet. Click Create Program to add one.</td></tr>';
        }
        return;
    }

    tableBody.innerHTML = forms.map((program) => {
        const status = resolveProgramStatus(program);
        const statusClass = status === 'open' ? 'schol-pill-approved' : 'schol-pill-rejected';

        return `
            <tr>
                <td>${escapeHtml(getProgramSportLabel(program))}</td>
                <td>${escapeHtml(program.participation_quantity ?? 'N/A')}</td>
                <td>${escapeHtml(program.start_date)}</td>
                <td>${escapeHtml(program.end_date)}</td>
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
    const startDate = document.getElementById('schedStartDate');
    const endDate = document.getElementById('schedEndDate');
    const status = document.getElementById('programStatus');
    const typeEl = document.getElementById('programType');
    const announcementEl = document.getElementById('spfbAnnouncement');
    const announcementCountEl = document.getElementById('spfbAnnouncementCount');
    const modalTitle = document.getElementById('scholarProgramModalTitle');

    if (participationQty) participationQty.value = program.participation_quantity ?? '';
    if (startDate) startDate.value = program.start_date || '';
    if (endDate) endDate.value = program.end_date || '';
    if (status) status.value = resolveProgramStatus(program);
    if (typeEl) typeEl.value = program.program_type || '';
    if (announcementEl) {
        announcementEl.value = program.announcement || '';
        if (announcementCountEl) announcementCountEl.textContent = String(announcementEl.value.length);
    }

    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.setQuestions === 'function') {
        window.SpfbFormBuilder.setQuestions(ensureDefaultTeamNameQuestion(program.custom_questions || []));
    }

    setAgeClassificationsForm(program.sports_details || null);

    const sportKey = program.sports_details?.sport_key || program.sport_key || '';
    const otherName = sportKey === 'other'
        ? (program.sports_details?.sport_label || program.sport_label || '')
        : '';
    populateSportSelect({
        locked: true,
        selectedKey: sportKey,
        otherName,
    });

    if (modalTitle) {
        modalTitle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Sports Program
        `;
    }
}

function openFormPreview(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    const viewProgramBody = document.getElementById('viewProgramBody');
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (!program || !viewProgramBody || !viewProgramModal) return;

    const status = resolveProgramStatus(program);
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
    };
    const statusStyle = statusColors[status] || statusColors.open;
    const kkFields = (program.kk_profiling_fields?.length ? program.kk_profiling_fields : DEFAULT_SPORTS_KK_FIELDS)
        .filter((field) => !SPORTS_EXCLUDED_KK_FIELDS.includes(field));
    const customQuestions = program.custom_questions || [];
    const sportsDetails = program.sports_details || null;

    viewProgramBody.innerHTML = `
        <div style="padding:24px;background:#f0f1f5;">
            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Program Information</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Sport</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(getProgramSportLabel(program))}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.program_name)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program Type</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.program_type)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Committee</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.committee)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Participation Quantity</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.participation_quantity ?? 'N/A')}</div>
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Application Window Schedule</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Start Date</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.start_date)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">End Date</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;">${escapeHtml(program.end_date)}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Status</label>
                        <span style="display:inline-flex;align-items:center;padding:8px 20px;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                    </div>
                </div>
            </div>

            <div class="schol-schedule-card" style="margin-bottom:20px;">
                <h4 class="schol-schedule-title">Age Classifications</h4>
                ${renderAgeClassificationsPreview(sportsDetails)}
            </div>

            <div class="schol-schedule-card">
                <h4 class="schol-schedule-title">Application Form Builder</h4>
                <div style="background:#fff;border-radius:8px;padding:20px;margin-bottom:20px;border:2px solid #e5e7eb;">
                    <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Announcement</label>
                    <div style="font-size:15px;color:#374151;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;min-height:80px;white-space:pre-wrap;">${escapeHtml(program.announcement || 'No announcement set')}</div>
                </div>

                <div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin:0 0 16px;font-size:16px;font-weight:700;color:#0369a1;">Include KK Profiling Data</h5>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                        ${Object.entries(KK_FIELD_LABELS).map(([value, label]) => `
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid ${kkFields.includes(value) ? '#0ea5e9' : '#e2e8f0'};border-radius:6px;">
                                <input type="checkbox" ${kkFields.includes(value) ? 'checked' : ''} disabled style="width:18px;height:18px;">
                                <span>${label}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>

                ${customQuestions.length ? `
                    <div style="background:#f8f9fa;border-radius:12px;padding:24px;border:2px solid #e5e7eb;">
                        ${customQuestions.map((question, index) => `
                            <div style="background:white;border-radius:8px;padding:24px;margin-bottom:16px;border:1px solid #e5e7eb;">
                                <div style="font-size:15px;color:#202124;font-weight:500;margin-bottom:10px;">
                                    ${index + 1}. ${escapeHtml(question.label)}
                                    ${question.required ? '<span style="color:#d93025;">*</span>' : ''}
                                </div>
                                <div style="font-size:13px;color:#5f6368;font-style:italic;">Type: ${escapeHtml(getTypeLabel(question.type))}</div>
                            </div>
                        `).join('')}
                    </div>
                ` : `
                    <div style="background:#fff3cd;border:2px solid #ffc107;border-radius:12px;padding:24px;text-align:center;">
                        <div style="font-size:16px;color:#856404;font-weight:600;">No custom questions added</div>
                    </div>
                `}
            </div>
        </div>
    `;

    viewProgramModal.style.display = 'flex';
}

function openDeleteProgramModal(programId) {
    const program = schedulePrograms.find((item) => String(item.id) === String(programId));
    pendingDeleteProgramId = programId;
    const deleteModal = document.getElementById('deleteProgramModal');
    const nameEl = document.getElementById('deleteProgramName');
    if (nameEl) {
        nameEl.textContent = program ? `"${getProgramSportLabel(program)}"` : '';
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
    const defaultHtml = confirmBtn ? confirmBtn.innerHTML : 'Archive Program';

    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="schol-save-spinner"></span> Archiving...';
    }

    try {
        await apiFetch(`/sports-programs/archive/${pendingDeleteProgramId}`, { method: 'POST' });
        closeDeleteProgramModal();
        await loadPrograms();
        showToast('Program moved to Archive. You can restore it within 30 days.', 'success');
    } catch (error) {
        showToast(error.message || 'Failed to archive program.', 'error');
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

    if (!startDate || !endDate) {
        showToast('Please select start and end dates.', 'error');
        return;
    }

    let participationQuantity = null;
    if (participationQtyRaw !== '') {
        const qtyNum = parseInt(participationQtyRaw, 10);
        if (Number.isNaN(qtyNum) || qtyNum < 0) {
            showToast('Participation quantity cannot be negative.', 'error');
            return;
        }
        participationQuantity = qtyNum;
    }

    let customQuestions = [];
    if (window.SpfbFormBuilder && typeof window.SpfbFormBuilder.getQuestions === 'function') {
        customQuestions = ensureDefaultTeamNameQuestion(window.SpfbFormBuilder.getQuestions());
    }

    const sportsDetails = getSportsDetailsPayload();

    if (!sportsDetails.sport_key) {
        showToast('Please select a sport (Basketball, Volleyball, or Other).', 'error');
        return;
    }

    if (sportsDetails.sport_key === 'other' && !sportsDetails.sport_label) {
        showToast('Please enter the sport name for Other.', 'error');
        return;
    }

    const ageClassifications = sportsDetails.age_classifications;
    const ageValidationError = validateAgeClassifications(ageClassifications);
    if (ageValidationError) {
        showToast(ageValidationError, 'error');
        return;
    }

    const kkProfilingFields = collectKkProfilingFields();

    const payload = {
        start_date: startDate,
        end_date: endDate,
        status,
        participation_quantity: participationQuantity,
        announcement,
        kk_profiling_fields: kkProfilingFields,
        custom_questions: customQuestions,
        sports_details: sportsDetails,
    };

    setSaveButtonLoading(true);

    try {
        if (editingProgramId) {
            await apiFetch(`/api/schedule-programs/${editingProgramId}?letter=${PROGRAM_LETTER}`, {
                method: 'PUT',
                body: JSON.stringify({ ...payload, program_letter: PROGRAM_LETTER }),
            });
            showToast('Program updated successfully!', 'success');
        } else {
            await apiFetch(`/api/schedule-programs?letter=${PROGRAM_LETTER}`, {
                method: 'POST',
                body: JSON.stringify({ ...payload, program_letter: PROGRAM_LETTER }),
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
        if (action === 'archive') openDeleteProgramModal(programId);
    });

    const sportsDisciplineKey = document.getElementById('sportsDisciplineKey');
    if (sportsDisciplineKey) {
        sportsDisciplineKey.addEventListener('change', () => {
            const otherWrap = document.getElementById('sportsOtherNameWrap');
            if (otherWrap) {
                otherWrap.style.display = sportsDisciplineKey.value === 'other' ? 'block' : 'none';
            }
            if (sportsDisciplineKey.value && !editingProgramId) {
                loadDefaultAgeBrackets();
            }
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

    const useDefaultAgeBtn = document.getElementById('sportsUseDefaultAgeBtn');
    const openAllBtn = document.getElementById('sportsOpenAllBtn');
    const addClassificationBtn = document.getElementById('sportsAddClassificationBtn');
    const ageClassificationsBody = document.getElementById('sportsAgeClassificationsBody');

    if (useDefaultAgeBtn) {
        useDefaultAgeBtn.addEventListener('click', loadDefaultAgeBrackets);
    }

    if (openAllBtn) {
        openAllBtn.addEventListener('click', openAllClassifications);
    }

    if (addClassificationBtn) {
        addClassificationBtn.addEventListener('click', () => {
            const current = getAgeClassificationsFromForm();
            current.push({
                id: generateClassificationId('new_division'),
                name: '',
                min_age: '',
                max_age: '',
                is_open: true,
            });
            renderAgeClassificationsTable(current);
        });
    }

    if (ageClassificationsBody) {
        ageClassificationsBody.addEventListener('input', (event) => {
            const input = event.target;
            if (!input.classList.contains('sports-cls-min') && !input.classList.contains('sports-cls-max')) {
                return;
            }

            const row = input.closest('tr[data-classification-id]');
            if (row) {
                normalizeRowAgeInputs(row);
            }
        });

        ageClassificationsBody.addEventListener('blur', (event) => {
            const input = event.target;
            if (!input.classList.contains('sports-cls-min') && !input.classList.contains('sports-cls-max')) {
                return;
            }

            const row = input.closest('tr[data-classification-id]');
            if (row) {
                normalizeRowAgeInputs(row);
            }
        }, true);

        ageClassificationsBody.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('[data-remove-classification]');
            if (!removeBtn) return;
            const id = removeBtn.getAttribute('data-remove-classification');
            const current = getAgeClassificationsFromForm().filter((item) => item.id !== id);
            renderAgeClassificationsTable(current);
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
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (viewProgramClose && viewProgramModal) {
        viewProgramClose.addEventListener('click', () => {
            viewProgramModal.style.display = 'none';
        });
        viewProgramModal.addEventListener('click', (event) => {
            if (event.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
            }
        });
    }

    try {
        if (typeof window.showLoading === 'function') window.showLoading();
        bindScheduleFilters();
        await loadScheduleFilterMeta();
        await loadProgramMeta();
        await loadPrograms();
    } catch (error) {
        showToast(error.message || 'Failed to load schedule programs.', 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="saf-table-empty">Unable to load schedule programs.</td></tr>';
    } finally {
        if (typeof window.hideLoading === 'function') window.hideLoading();
    }
});

window.editSportsProgram = editProgram;
