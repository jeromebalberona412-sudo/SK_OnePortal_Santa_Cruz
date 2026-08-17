/**
 * Edit mode for KK Profiling requests — uses the dedicated paper-form panel.
 */

import {
    formatDisplaySuffix,
    getSuffixOther,
    resolveSuffixForEdit,
} from './kk-profiling-view-populate.js';

function kkEditSingleCheck(el, hiddenId) {
    const group = document.querySelectorAll(`input[type="checkbox"][name="${el.name}"]`);
    group.forEach((chk) => {
        if (chk !== el) {
            chk.checked = false;
        }
    });
    const hidden = document.getElementById(hiddenId);
    if (hidden) {
        hidden.value = el.checked ? el.value : '';
    }
    if (hiddenId === 'kkEditAttendedKKAssembly') {
        syncEditAssemblyFollowUp();
    }
}

if (typeof window !== 'undefined') {
    window.kkEditSingleCheck = kkEditSingleCheck;
}

function getEditField(id) {
    return document.getElementById(id);
}

function normalizeCompare(value) {
    return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
}

function valuesMatch(stored, candidate) {
    const left = normalizeCompare(stored);
    const right = normalizeCompare(candidate);
    return left !== '' && left === right;
}

function formatMiddleNameDisplay(value) {
    const normalized = String(value || '').trim();
    if (!normalized || normalized === '—' || normalized.toLowerCase() === 'none') {
        return 'None';
    }
    return normalized;
}

function formatMiddleNameForTable(value) {
    const normalized = String(value || '').trim();
    if (!normalized || normalized.toLowerCase() === 'none') {
        return '';
    }
    return normalized;
}

function youthAgeGroupFromAge(age) {
    const n = parseInt(age, 10);
    if (n >= 15 && n <= 17) return 'Child Youth (15-17 yrs old)';
    if (n >= 18 && n <= 24) return 'Core Youth (18-24 yrs old)';
    if (n >= 25 && n <= 30) return 'Young Adult (15-30 yrs old)';
    return '';
}

function formatBirthdayForInput(value) {
    if (!value || value === '—') {
        return '';
    }
    const text = String(value).trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
        return text;
    }
    const slash = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(text);
    if (slash) {
        return `${slash[3]}-${String(slash[1]).padStart(2, '0')}-${String(slash[2]).padStart(2, '0')}`;
    }
    return '';
}

function formatPrintedName(request) {
    const firstName = request.firstName || '';
    const middleName = request.middleName || '';
    const lastName = request.lastName || '';
    const suffix = formatDisplaySuffix(request.suffix, getSuffixOther(request));
    const parts = [
        firstName,
        middleName ? `${middleName.charAt(0)}.` : null,
        lastName,
        suffix && suffix !== 'None' ? suffix : null,
    ].filter(Boolean);
    return parts.join(' ');
}

function populateSignature(request) {
    const nameEl = getEditField('kkEditSignatureName');
    const preview = getEditField('kkEditSignaturePreview');
    const overlay = getEditField('kkEditSignatureOverlay');
    if (nameEl) {
        nameEl.value = formatPrintedName(request);
    }
    if (!preview || !overlay) {
        return;
    }
    const signature = request.signature || '';
    if (String(signature).startsWith('data:image')) {
        preview.src = signature;
        overlay.style.display = 'flex';
    } else {
        preview.removeAttribute('src');
        overlay.style.display = 'none';
    }
}

function syncEditCustomSuffixVisibility() {
    const suffixEl = getEditField('kkEditSuffix');
    const wrap = getEditField('kkEditCustomSuffixWrap');
    const customEl = getEditField('kkEditCustomSuffix');
    if (!suffixEl || !wrap) {
        return;
    }
    const isOthers = suffixEl.value === 'Others';
    wrap.classList.toggle('show', isOthers);
    if (customEl) {
        customEl.required = isOthers;
        if (!isOthers) {
            customEl.value = '';
        }
    }
}

function populateEditSuffixFields(request) {
    const suffixEl = getEditField('kkEditSuffix');
    const customEl = getEditField('kkEditCustomSuffix');
    if (!suffixEl) {
        return;
    }

    const resolved = resolveSuffixForEdit(request);
    suffixEl.value = resolved.select;
    if (customEl) {
        customEl.value = resolved.custom;
    }
    syncEditCustomSuffixVisibility();
}

function bindEditSuffixControls() {
    const suffixEl = getEditField('kkEditSuffix');
    if (!suffixEl || suffixEl.dataset.kkEditBound === '1') {
        return;
    }
    suffixEl.dataset.kkEditBound = '1';
    suffixEl.addEventListener('change', syncEditCustomSuffixVisibility);
}

function normalizeKkReason(value) {
    const text = String(value || '').trim();
    if (!text) {
        return '';
    }
    const lower = text.toLowerCase();
    if (lower.includes('no kk assembly')) {
        return 'There was no KK Assembly Meeting';
    }
    if (lower.includes('not interested')) {
        return 'Not interested to Attend';
    }
    return text;
}

function setEditAssemblyFollowupState(cell, enabled) {
    if (!cell) {
        return;
    }
    cell.classList.toggle('kkp-assembly-followup--inactive', !enabled);
    cell.classList.toggle('kkp-assembly-followup--active', enabled);
    cell.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.disabled = !enabled;
        if (!enabled) {
            checkbox.checked = false;
        }
    });
    const hidden = cell.querySelector('input[type="hidden"]');
    if (hidden && !enabled) {
        hidden.value = '';
    }
}

function syncEditAssemblyFollowUp() {
    const assemblyVal = getEditField('kkEditAttendedKKAssembly')?.value || '';
    const yesCell = document.getElementById('kkEditAssemblyYesCell');
    const noCell = document.getElementById('kkEditAssemblyNoCell');
    const arrowYes = document.querySelector('#kkEditAssemblyQuestion .kkp-assembly-arrow--yes');
    const arrowNo = document.querySelector('#kkEditAssemblyQuestion .kkp-assembly-arrow--no');

    if (assemblyVal === 'Yes') {
        setEditAssemblyFollowupState(yesCell, true);
        setEditAssemblyFollowupState(noCell, false);
        arrowYes?.classList.add('kkp-assembly-arrow--on');
        arrowNo?.classList.remove('kkp-assembly-arrow--on');
        return;
    }
    if (assemblyVal === 'No') {
        setEditAssemblyFollowupState(yesCell, false);
        setEditAssemblyFollowupState(noCell, true);
        arrowYes?.classList.remove('kkp-assembly-arrow--on');
        arrowNo?.classList.add('kkp-assembly-arrow--on');
        return;
    }
    setEditAssemblyFollowupState(yesCell, false);
    setEditAssemblyFollowupState(noCell, false);
    arrowYes?.classList.remove('kkp-assembly-arrow--on');
    arrowNo?.classList.remove('kkp-assembly-arrow--on');
}

function normalizeMiddleNameForSave(value) {
    const normalized = String(value || '').trim();
    if (!normalized || normalized.toLowerCase() === 'none') {
        return '';
    }
    return normalized;
}

function setCheckboxGroupByHiddenId(hiddenId, value) {
    const hidden = getEditField(hiddenId);
    if (!hidden) {
        return;
    }

    hidden.value = value || '';
    const block = hidden.closest('.kkf-demo-block, .kkf-sex-block, .kkp-sex-block, .kkf-voter-cell, .kkf-footer-chat, .kkp-demo-block');
    if (!block) {
        return;
    }

    block.querySelectorAll('input.kkf-sq-chk, input.kkp-sq-chk').forEach((checkbox) => {
        checkbox.checked = value !== '' && valuesMatch(value, checkbox.value);
    });
}

function ensurePurokOption(select, value) {
    if (!select || !value) {
        return;
    }
    const exists = Array.from(select.options).some((option) => option.value === value);
    if (!exists) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        select.appendChild(option);
    }
    select.value = value;
}

function populateKkEditForm(request) {
    if (!request) {
        return;
    }

    const logo = getEditField('kkEditBarangayLogo');
    if (logo && request.barangayLogoUrl) {
        logo.src = request.barangayLogoUrl;
    }

    const textMap = {
        kkEditRespondentNumber: request.respondentNumber || 'Auto-generated',
        kkEditDate: request.date || '—',
        kkEditLastName: request.lastName || '',
        kkEditFirstName: request.firstName || '',
        kkEditMiddleName: formatMiddleNameDisplay(request.middleName),
        kkEditRegion: request.region || 'Region IV-A (CALABARZON)',
        kkEditProvince: request.province || 'Laguna',
        kkEditCity: request.city || 'Santa Cruz',
        kkEditBarangay: request.barangay || '',
        kkEditAgeInput: request.age ?? '',
        kkEditDob: formatBirthdayForInput(request.birthday),
        kkEditEmail: request.emailAddress && request.emailAddress !== 'No email' ? request.emailAddress : '',
        kkEditContactInput: request.contactNumber || '',
        kkEditFacebookAccount: request.facebookAccount || '',
    };

    Object.entries(textMap).forEach(([id, value]) => {
        const el = getEditField(id);
        if (el) {
            el.value = value;
        }
    });

    const suffixEl = getEditField('kkEditSuffix');
    populateEditSuffixFields(request);
    bindEditSuffixControls();

    const purokEl = getEditField('kkEditPurokZone');
    if (purokEl) {
        ensurePurokOption(purokEl, request.purokZone || '');
    }

    const emailEl = getEditField('kkEditEmail');
    if (emailEl) {
        emailEl.readOnly = Boolean(request.has_account);
        emailEl.classList.toggle('kkp-readonly', Boolean(request.has_account));
        if (request.has_account) {
            emailEl.tabIndex = -1;
        } else {
            emailEl.removeAttribute('tabindex');
        }
    }

    setCheckboxGroupByHiddenId('kkEditSex', request.sex || '');
    setCheckboxGroupByHiddenId('kkEditCivilStatus', request.civilStatus || '');
    setCheckboxGroupByHiddenId('kkEditEducationalBackground', request.educationalBackground || '');
    setCheckboxGroupByHiddenId('kkEditYouthClassification', request.youthClassification || '');
    setCheckboxGroupByHiddenId('kkEditWorkStatus', request.workStatus || '');
    setCheckboxGroupByHiddenId('kkEditRegisteredSKVoter', request.registeredSKVoter || '');
    setCheckboxGroupByHiddenId('kkEditRegisteredNationalVoter', request.registeredNationalVoter || '');
    setCheckboxGroupByHiddenId('kkEditVotingHistory', request.votingHistory || '');
    setCheckboxGroupByHiddenId('kkEditAttendedKKAssembly', request.attendedKKAssembly || '');
    setCheckboxGroupByHiddenId('kkEditVotingFrequency', request.kkTimes || '');
    setCheckboxGroupByHiddenId('kkEditVotingReason', normalizeKkReason(request.kkReason));
    setCheckboxGroupByHiddenId('kkEditWillingToJoinGroupChat', request.willingToJoinGroupChat || '');

    const ageGroup = youthAgeGroupFromAge(request.age) || request.youthAgeGroup || '';
    setCheckboxGroupByHiddenId('kkEditYouthAgeGroup', ageGroup);
    syncEditAssemblyFollowUp();
    populateSignature(request);
}

function bindAgeGroupAutoUpdate() {
    const ageInput = getEditField('kkEditAgeInput');
    if (!ageInput || ageInput.dataset.kkEditBound === '1') {
        return;
    }
    ageInput.dataset.kkEditBound = '1';
    ageInput.addEventListener('input', () => {
        setCheckboxGroupByHiddenId('kkEditYouthAgeGroup', youthAgeGroupFromAge(ageInput.value));
    });
    ageInput.addEventListener('change', () => {
        setCheckboxGroupByHiddenId('kkEditYouthAgeGroup', youthAgeGroupFromAge(ageInput.value));
    });
}

function showEditPanel(showEdit) {
    const viewRoot = document.querySelector('#kkViewModal .kk-profiling-view-root');
    const editRoot = document.getElementById('kkProfilingEditRoot');
    if (viewRoot) {
        viewRoot.hidden = showEdit;
    }
    if (editRoot) {
        editRoot.hidden = !showEdit;
    }
}

export function enterKkProfilingEditMode(request) {
    const footer = document.getElementById('kkViewEditFooter');
    const title = document.querySelector('#kkViewModal .modal-title');

    showEditPanel(true);
    populateKkEditForm(request);
    bindAgeGroupAutoUpdate();

    if (footer) {
        footer.hidden = false;
    }
    if (title) {
        title.textContent = 'Edit KK Profiling';
    }
}

export function exitKkProfilingEditMode() {
    const footer = document.getElementById('kkViewEditFooter');
    const title = document.querySelector('#kkViewModal .modal-title');

    showEditPanel(false);

    if (footer) {
        footer.hidden = true;
    }
    if (title) {
        title.textContent = 'Kabataan Details';
    }
}

export function collectKkProfilingEditPayload() {
    const age = getEditField('kkEditAgeInput')?.value?.trim() || '';
    const assembly = getEditField('kkEditAttendedKKAssembly')?.value || '';
    const suffixSelect = getEditField('kkEditSuffix')?.value?.trim() || 'None';
    const customSuffix = getEditField('kkEditCustomSuffix')?.value?.trim() || '';
    let suffix = suffixSelect;
    if (suffixSelect === 'Others') {
        suffix = customSuffix || 'None';
    }

    const payload = {
        last_name: getEditField('kkEditLastName')?.value?.trim() || '',
        first_name: getEditField('kkEditFirstName')?.value?.trim() || '',
        middle_name: normalizeMiddleNameForSave(getEditField('kkEditMiddleName')?.value),
        suffix,
        custom_suffix: suffixSelect === 'Others' ? customSuffix : '',
        age,
        birthday: getEditField('kkEditDob')?.value?.trim() || '',
        email: getEditField('kkEditEmail')?.value?.trim() || '',
        contact_number: getEditField('kkEditContactInput')?.value?.trim() || '',
        purok_zone: getEditField('kkEditPurokZone')?.value?.trim() || '',
        sex: getEditField('kkEditSex')?.value || '',
        civil_status: getEditField('kkEditCivilStatus')?.value || '',
        youth_classification: getEditField('kkEditYouthClassification')?.value || '',
        youth_age_group: youthAgeGroupFromAge(age) || getEditField('kkEditYouthAgeGroup')?.value || '',
        work_status: getEditField('kkEditWorkStatus')?.value || '',
        education: getEditField('kkEditEducationalBackground')?.value || '',
        sk_voter: getEditField('kkEditRegisteredSKVoter')?.value || '',
        national_voter: getEditField('kkEditRegisteredNationalVoter')?.value || '',
        sk_voted: getEditField('kkEditVotingHistory')?.value || '',
        kk_assembly: assembly,
        kk_times: assembly === 'Yes' ? (getEditField('kkEditVotingFrequency')?.value || '') : '',
        kk_reason: assembly === 'No' ? (getEditField('kkEditVotingReason')?.value || '') : '',
        facebook: getEditField('kkEditFacebookAccount')?.value?.trim() || '',
        facebook_profile_url: getEditField('kkEditFacebookAccount')?.value?.trim() || '',
        group_chat: getEditField('kkEditWillingToJoinGroupChat')?.value || '',
    };

    return payload;
}

export function bindKkProfilingEdit({
    findRequestById,
    populateView,
    openModal,
    closeModal,
    viewModal,
    loadData,
    showToast,
    resetMaximize,
}) {
    const tbody = document.getElementById('kkRequestsTableBody');
    const saveBtn = document.getElementById('kkViewEditSaveBtn');
    const cancelBtn = document.getElementById('kkViewEditCancelBtn');
    let editingId = null;
    let saving = false;

    function openEdit(request) {
        if (!request?.id) {
            showToast('This record cannot be edited.', 'error');
            return;
        }
        editingId = request.id;
        resetMaximize?.(viewModal);
        populateView(request);
        enterKkProfilingEditMode(request);
        openModal(viewModal);
    }

    tbody?.addEventListener('click', (event) => {
        const btn = event.target.closest('button[data-action="edit"]');
        if (!btn) {
            return;
        }
        const request = findRequestById(btn.getAttribute('data-id'));
        if (request) {
            openEdit(request);
        }
    });

    cancelBtn?.addEventListener('click', () => {
        exitKkProfilingEditMode();
        closeModal(viewModal);
        editingId = null;
    });

    saveBtn?.addEventListener('click', async () => {
        if (!editingId || saving) {
            return;
        }

        const payload = collectKkProfilingEditPayload();
        if (!payload.last_name || !payload.first_name) {
            showToast('First name and last name are required.', 'error');
            return;
        }

        saving = true;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(`/kk-profiling-requests/${editingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to save KK Profiling record.');
            }
            showToast(data.message || 'KK Profiling record updated.', 'success');
            exitKkProfilingEditMode();
            closeModal(viewModal);
            editingId = null;
            loadData();
        } catch (error) {
            showToast(error.message || 'Failed to save KK Profiling record.', 'error');
        } finally {
            saving = false;
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
        }
    });
}
