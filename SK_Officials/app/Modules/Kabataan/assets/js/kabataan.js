document.addEventListener('DOMContentLoaded', () => {
    initializeKabataanUI();
});

/**
 * kkfSingleCheck — makes checkboxes behave like radio buttons (only one checked at a time)
 * and syncs the selected value to a hidden input by id.
 */
function kkfSingleCheck(el, hiddenId) {
    const group = document.querySelectorAll('input[type="checkbox"][name="' + el.name + '"]');
    group.forEach(function(chk) {
        if (chk !== el) chk.checked = false;
    });
    const hidden = document.getElementById(hiddenId);
    if (hidden) hidden.value = el.checked ? el.value : '';
}

function initializeKabataanUI() {
    const tbody = document.getElementById('kabataanTableBody');
    const searchInput = document.getElementById('kabataanSearch');
    const genderFilter = document.getElementById('kabataanGenderFilter');
    const purokFilter = document.getElementById('kabataanPurok / SitioFilter');
    const educationFilter = document.getElementById('kabataanEducationFilter');

    const addBtn = document.getElementById('addKabataanBtn');
    const modal = document.getElementById('kabataanModal');
    const modalBox = modal ? modal.querySelector('.kabataan-modal-box') : null;
    const modalTitle = document.getElementById('kabataanModalTitle');
    const saveBtn = document.getElementById('kabataanSaveBtn');
    const toggleBtn = document.getElementById('kabataanModalToggle');

    const modeSelector = document.getElementById('kabataanAddModeSelector');
    const panelBulk = document.getElementById('kabataanPanelBulk');
    const panelManual = document.getElementById('kabataanPanelManual');
    const viewDetails = document.getElementById('kabataanViewDetails');
    const viewRowsContainer = document.getElementById('kabataanViewRows');
    const viewColumnsContainer = document.getElementById('kabataanViewColumns');
    const viewColumnLeft = document.getElementById('kabataanViewColumnLeft');
    const viewColumnRight = document.getElementById('kabataanViewColumnRight');
    const manualBlockTitle = document.getElementById('kabataanManualBlockTitle');
    const manualBlockHint = document.getElementById('kabataanManualBlockHint');

    const fieldIds = [
        'kabataanRespondentNumber', 'kabataanDate',
        'kabataanLastName', 'kabataanFirstName', 'kabataanMiddleName', 'kabataanSuffix',
        'kabataanRegion', 'kabataanProvince', 'kabataanCity', 'kabataanBarangay', 'kabataanPurokZone',
        'kabataanSex', 'kabataanAgeInput', 'kabataanDob', 'kabataanEmail', 'kabataanContactInput',
        'kabataanCivilStatus', 'kabataanYouthClassification', 'kabataanYouthAgeGroup',
        'kabataanWorkStatus', 'kabataanEducationalBackground',
        'kabataanRegisteredSKVoter', 'kabataanRegisteredNationalVoter',
        'kabataanVotingHistory', 'kabataanVotingFrequency', 'kabataanVotingReason',
        'kabataanAttendedKKAssembly',
        'kabataanFacebookAccount', 'kabataanWillingToJoinGroupChat',
        'kabataanSignature',
    ];

    const viewFieldLabels = [
        ['Respondent #', 'respondentNumber'],
        ['Date', 'date'],
        ['Last Name', 'lastName'],
        ['First Name', 'firstName'],
        ['Middle Name', 'middleName'],
        ['Suffix', 'suffix'],
        ['Region', 'region'],
        ['Province', 'province'],
        ['City / Municipality', 'city'],
        ['Barangay', 'barangay'],
        ['Purok / Zone', 'purokZone'],
        ['Sex Assigned at Birth', 'sex'],
        ['Age', 'age'],
        ['Birthday (dd/mm/yy)', 'birthday'],
        ['E-mail Address', 'emailAddress'],
        ['Contact #', 'contactNumber'],
        ['Civil Status', 'civilStatus'],
        ['Youth Classification', 'youthClassification'],
        ['Youth Age Group', 'youthAgeGroup'],
        ['Work Status', 'workStatus'],
        ['Educational Background', 'educationalBackground'],
        ['Registered SK Voter', 'registeredSKVoter'],
        ['Registered National Voter', 'registeredNationalVoter'],
        ['Did you vote last SK?', 'votingHistory'],
        ['If Yes, How many times?', 'votingFrequency'],
        ['If No, Why?', 'votingReason'],
        ['Attended KK Assembly?', 'attendedKKAssembly'],
        ['FB Account', 'facebookAccount'],
        ['Willing to join group chat?', 'willingToJoinGroupChat'],
    ];

    if (!tbody) return;

    function getField(id) { return document.getElementById(id); }

    function getFormData() {
        const o = {};
        const map = {
            kabataanRespondentNumber: 'respondentNumber',
            kabataanDate: 'date',
            kabataanLastName: 'lastName', kabataanFirstName: 'firstName', kabataanMiddleName: 'middleName', kabataanSuffix: 'suffix',
            kabataanRegion: 'region', kabataanProvince: 'province', kabataanCity: 'city', kabataanBarangay: 'barangay', kabataanPurokZone: 'purokZone',
            kabataanSex: 'sex', kabataanAgeInput: 'age', kabataanDob: 'birthday',
            kabataanEmail: 'emailAddress', kabataanContactInput: 'contactNumber',
            kabataanCivilStatus: 'civilStatus',
            kabataanYouthClassification: 'youthClassification',
            kabataanYouthAgeGroup: 'youthAgeGroup',
            kabataanWorkStatus: 'workStatus',
            kabataanEducationalBackground: 'educationalBackground',
            kabataanRegisteredSKVoter: 'registeredSKVoter',
            kabataanRegisteredNationalVoter: 'registeredNationalVoter',
            kabataanVotingHistory: 'votingHistory',
            kabataanVotingFrequency: 'votingFrequency',
            kabataanVotingReason: 'votingReason',
            kabataanAttendedKKAssembly: 'attendedKKAssembly',
            kabataanFacebookAccount: 'facebookAccount',
            kabataanWillingToJoinGroupChat: 'willingToJoinGroupChat',
            kabataanSignature: 'signature',
        };
        fieldIds.forEach((id) => {
            const el = getField(id);
            if (!el) return;
            const key = map[id];
            if (key) o[key] = el.type === 'number' ? (el.value === '' ? '' : Number(el.value)) : el.value;
        });
        // Legacy compatibility aliases
        o.email = o.emailAddress;
        o.dob = o.birthday;
        return o;
    }

    function setFormData(k) {
        if (!k) return;
        const map = {
            respondentNumber: 'kabataanRespondentNumber',
            date: 'kabataanDate',
            lastName: 'kabataanLastName', firstName: 'kabataanFirstName', middleName: 'kabataanMiddleName', suffix: 'kabataanSuffix',
            region: 'kabataanRegion', province: 'kabataanProvince', city: 'kabataanCity', barangay: 'kabataanBarangay', purokZone: 'kabataanPurokZone',
            sex: 'kabataanSex', age: 'kabataanAgeInput',
            emailAddress: 'kabataanEmail', contactNumber: 'kabataanContactInput',
            civilStatus: 'kabataanCivilStatus',
            youthClassification: 'kabataanYouthClassification',
            youthAgeGroup: 'kabataanYouthAgeGroup',
            workStatus: 'kabataanWorkStatus',
            educationalBackground: 'kabataanEducationalBackground',
            registeredSKVoter: 'kabataanRegisteredSKVoter',
            registeredNationalVoter: 'kabataanRegisteredNationalVoter',
            votingHistory: 'kabataanVotingHistory',
            votingFrequency: 'kabataanVotingFrequency',
            votingReason: 'kabataanVotingReason',
            attendedKKAssembly: 'kabataanAttendedKKAssembly',
            facebookAccount: 'kabataanFacebookAccount',
            willingToJoinGroupChat: 'kabataanWillingToJoinGroupChat',
            signature: 'kabataanSignature',
        };
        // birthday field — stored as 'birthday', form id is kabataanDob
        const birthdayEl = getField('kabataanDob');
        if (birthdayEl) birthdayEl.value = k.birthday || k.dob || '';

        Object.keys(map).forEach((key) => {
            const id = map[key];
            const el = getField(id);
            if (!el) return;
            // email fallback
            const val = k[key] !== undefined ? k[key] : (key === 'emailAddress' ? k.email : undefined);
            el.value = val === null || val === undefined ? '' : String(val);
        });
    }

    function clearForm() {
        setFormData({
            respondentNumber: '', date: '',
            lastName: '', firstName: '', middleName: '', suffix: '',
            region: '', province: '', city: '', barangay: '', purokZone: '',
            sex: 'Male', age: '', birthday: '',
            emailAddress: '', contactNumber: '',
            civilStatus: 'Single',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Child Youth (15-17 yrs old)',
            workStatus: '',
            educationalBackground: '',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '',
            votingReason: '',
            attendedKKAssembly: 'Yes',
            facebookAccount: '',
            willingToJoinGroupChat: 'Yes',
            signature: '',
        });
    }

    function fullNameFrom(k) {
        const parts = [k.firstName, k.middleName].filter(Boolean);
        const firstMiddle = parts.length ? parts.join(' ') : '';
        const last = k.lastName || '';
        const suffix = k.suffix ? ' ' + k.suffix : '';

        if (last && firstMiddle) {
            return `${last}, ${firstMiddle}${suffix}`;
        } else if (last) {
            return `${last}${suffix}`;
        } else if (firstMiddle) {
            return `${firstMiddle}${suffix}`;
        } else {
            return '-';
        }
    }

    const defaultRecord = () => ({
        respondentNumber: '',
        date: '',
        firstName: '', middleName: '', lastName: '', suffix: '',
        region: '', province: '', city: '', barangay: '', purokZone: '',
        sex: 'Male', age: 0, birthday: '',
        emailAddress: '', contactNumber: '',
        civilStatus: 'Single',
        youthClassification: 'In School Youth',
        youthAgeGroup: 'Child Youth (15-17 yrs old)',
        workStatus: '',
        educationalBackground: '',
        registeredSKVoter: 'Yes',
        registeredNationalVoter: 'Yes',
        votingHistory: 'Yes',
        votingFrequency: '',
        votingReason: '',
        attendedKKAssembly: 'Yes',
        facebookAccount: '',
        willingToJoinGroupChat: 'Yes',
        signature: '',
        // Legacy aliases for compatibility
        email: '', dob: '', address: '', highestEducation: '',
    });

    const kabataan = [];

    // Sort kabataan array alphabetically by last name (professional standard)
    function sortKabataanAlphabetically() {
        return kabataan.sort((a, b) => {
            const lastNameA = (a.lastName || '').toLowerCase();
            const lastNameB = (b.lastName || '').toLowerCase();

            if (lastNameA < lastNameB) return -1;
            if (lastNameA > lastNameB) return 1;

            // If last names are the same, sort by first name
            const firstNameA = (a.firstName || '').toLowerCase();
            const firstNameB = (b.firstName || '').toLowerCase();

            if (firstNameA < firstNameB) return -1;
            if (firstNameA > firstNameB) return 1;

            return 0;
        });
    }

    // Initial sort
    sortKabataanAlphabetically();

    let currentQuery = '';
    let currentGender = '';
    let currentPurok = '';
    let currentEducation = '';
    let editingIndex = null;

    function render() {
        tbody.innerHTML = '';

        const filtered = kabataan.filter((k) => {
            const q = currentQuery;
            const full = fullNameFrom(k).toLowerCase();
            const education = k.educationalBackground || k.highestEducation || '';
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (education && education.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            const matchEducation = !currentEducation || education === currentEducation;
            return matchSearch && matchGender && matchPurok && matchEducation;
        });

        if (filtered.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 7;
            td.textContent = 'No kabataan match current filters.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        filtered.forEach((k) => {
            const index = kabataan.indexOf(k);
            const full = fullNameFrom(k);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${k.respondentNumber || '-'}</td>
                <td class="kabataan-fullname-cell">
                    <span class="kabataan-fullname">${full}</span>
                </td>
                <td>${k.age || '-'}</td>
                <td>${k.sex || '-'}</td>
                <td>${k.barangay || '-'}</td>
                <td>${k.educationalBackground || k.highestEducation || '-'}</td>
                <td>
                    <div class="kabataan-actions">
                        <button type="button" class="btn-action-view" data-action="view" data-index="${index}">View</button>
                        <button type="button" class="btn-action-edit" data-action="edit" data-index="${index}">Edit</button>
                        <button type="button" class="btn-action-delete" data-action="delete" data-index="${index}">Delete</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function populateViewRows(k) {
        // Populate text/span fields
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || ''; };
        setVal('vRespondentNumber', k.respondentNumber);
        setVal('vDate', k.date);
        setVal('vLastName', k.lastName);
        setVal('vFirstName', k.firstName);
        setVal('vMiddleName', k.middleName);
        setVal('vSuffix', k.suffix);
        setVal('vRegion', k.region);
        setVal('vProvince', k.province);
        setVal('vCity', k.city);
        setVal('vBarangay', k.barangay);
        setVal('vPurokZone', k.purokZone);
        setVal('vAge', k.age);
        setVal('vDob', k.birthday || k.dob);
        setVal('vEmail', k.emailAddress || k.email);
        setVal('vContact', k.contactNumber);
        setVal('vFacebook', k.facebookAccount);
        
        // Handle signature display - show image overlaid on name
        const vSignatureImg = document.getElementById('vSignature');
        const vSignatureOverlay = document.getElementById('vSignatureOverlay');
        const vSignatureText = document.getElementById('vSignatureText');
        
        if (k.signature && k.signature.startsWith('data:image')) {
            // It's a base64 image - display signature overlay on name
            if (vSignatureImg && vSignatureOverlay) {
                vSignatureImg.src = k.signature;
                vSignatureOverlay.style.display = 'flex';
            }
            if (vSignatureText) {
                // Get the full name from the record (FirstName MiddleName LastName Suffix)
                const nameParts = [];
                if (k.firstName) nameParts.push(k.firstName);
                if (k.middleName) nameParts.push(k.middleName);
                if (k.lastName) nameParts.push(k.lastName);
                if (k.suffix && k.suffix !== 'None') nameParts.push(k.suffix);
                
                const fullName = nameParts.join(' ');
                vSignatureText.textContent = fullName;
                vSignatureText.style.display = 'block';
            }
        } else {
            // No signature - just display name
            if (vSignatureOverlay) {
                vSignatureOverlay.style.display = 'none';
            }
            if (vSignatureText) {
                // Get the full name from the record
                const nameParts = [];
                if (k.firstName) nameParts.push(k.firstName);
                if (k.middleName) nameParts.push(k.middleName);
                if (k.lastName) nameParts.push(k.lastName);
                if (k.suffix && k.suffix !== 'None') nameParts.push(k.suffix);
                
                const fullName = nameParts.join(' ');
                vSignatureText.textContent = fullName || '';
                vSignatureText.style.display = 'block';
            }
        }

        // Populate view checkboxes — tick the one matching the stored value
        const viewChks = document.querySelectorAll('.kkf-view-chk');
        viewChks.forEach(chk => {
            const field = chk.dataset.viewField;
            const fieldMap = {
                vSex: k.sex,
                vCivilStatus: k.civilStatus,
                vYouthAgeGroup: k.youthAgeGroup,
                vEducation: k.educationalBackground || k.highestEducation,
                vYouthClassification: k.youthClassification,
                vWorkStatus: k.workStatus,
                vSKVoter: k.registeredSKVoter,
                vVotingHistory: k.votingHistory,
                vVotingFrequency: k.votingFrequency,
                vNatVoter: k.registeredNationalVoter,
                vKKAssembly: k.attendedKKAssembly,
                vVotingReason: k.votingReason,
                vGroupChat: k.willingToJoinGroupChat,
            };
            const stored = fieldMap[field] || '';
            chk.checked = stored.trim().toLowerCase() === chk.value.trim().toLowerCase();
        });
    }

    function setModalReadonly(readonly) {
        if (saveBtn) saveBtn.style.display = readonly ? 'none' : '';
        if (manualBlockTitle) manualBlockTitle.textContent = readonly ? 'Details' : 'Add entry manually';
        if (manualBlockHint) manualBlockHint.style.display = readonly ? 'none' : '';
        if (viewDetails) viewDetails.style.display = readonly ? 'block' : 'none';
        if (panelManual) panelManual.style.display = readonly ? 'none' : 'block';
    }

    function showAddPanels(showSelector, showBulk, showManual) {
        if (modeSelector) modeSelector.style.display = showSelector ? 'block' : 'none';
        if (panelBulk) panelBulk.style.display = showBulk ? 'block' : 'none';
        if (panelManual) panelManual.style.display = showManual ? 'block' : 'none';
        if (viewDetails) viewDetails.style.display = 'none';
    }

    function openModal(mode, index) {
        if (!modal) return;
        editingIndex = index ?? null;
        if (toggleBtn && modalBox) {
            modal.classList.remove('modal-maximized');
            modalBox.classList.remove('modal-maximized');
            toggleBtn.textContent = '□';
        }

        if (mode === 'view' && typeof index === 'number') {
            const k = kabataan[index];
            if (!k) return;
            modalTitle.textContent = 'Kabataan Details';
            showAddPanels(false, false, false);
            viewDetails.style.display = 'block';
            populateViewRows(k);
            setModalReadonly(true);
        } else if (mode === 'edit' && typeof index === 'number') {
            const k = kabataan[index];
            if (!k) return;
            modalTitle.textContent = 'Edit Kabataan';
            showAddPanels(false, false, true);
            setFormData(k);
            setModalReadonly(false);
        } else {
            modalTitle.textContent = 'Add Kabataan';
            showAddPanels(true, false, true);
            clearForm();
            setModalReadonly(false);
        }

        modal.style.display = 'flex';
        const firstInput = getField('kabataanFirstName');
        if (firstInput && mode !== 'view') firstInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.remove('modal-maximized');
        if (modalBox) modalBox.classList.remove('modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    if (addBtn) addBtn.addEventListener('click', () => openModal('add', null));

    // Pagination event listeners
    const prevBtn = document.getElementById('kabataanPrevBtn');
    const nextBtn = document.getElementById('kabataanNextBtn');

    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    // Filter event listeners
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentQuery = searchInput.value.trim().toLowerCase();
            currentPage = 1;
            render();
        });
    }

    if (genderFilter) {
        genderFilter.addEventListener('change', () => {
            currentGender = genderFilter.value;
            currentPage = 1;
            render();
        });
    }

    if (purokFilter) {
        purokFilter.addEventListener('change', () => {
            currentPurok = purokFilter.value;
            currentPage = 1;
            render();
        });
    }

    if (educationFilter) {
        educationFilter.addEventListener('change', () => {
            currentEducation = educationFilter.value;
            currentPage = 1;
            render();
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close')) closeModal();
        });
    }

    if (toggleBtn && modalBox) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = modal.classList.toggle('modal-maximized');
            modalBox.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    if (modeSelector) {
        modeSelector.querySelectorAll('.kabataan-mode-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-mode');
                modeSelector.querySelectorAll('.kabataan-mode-btn').forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                if (mode === 'bulk') {
                    panelBulk.style.display = 'block';
                    panelManual.style.display = 'none';
                } else {
                    panelBulk.style.display = 'none';
                    panelManual.style.display = 'block';
                }
            });
        });
    }

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;
        const index = parseInt(btn.dataset.index, 10);
        if ((action === 'view' || action === 'edit') && !Number.isNaN(index)) openModal(action, index);
        if (action === 'delete' && !Number.isNaN(index)) openDeleteConfirm(index);
    });

    // ── Delete confirmation modal ──
    const deleteModal = document.getElementById('kabataanDeleteModal');
    const deleteModalName = document.getElementById('kabataanDeleteName');
    const deleteConfirmBtn = document.getElementById('kabataanDeleteConfirmBtn');
    const deleteCancelBtn = document.getElementById('kabataanDeleteCancelBtn');
    let pendingDeleteIndex = null;

    function openDeleteConfirm(index) {
        const k = kabataan[index];
        if (!k) return;
        pendingDeleteIndex = index;
        if (deleteModalName) deleteModalName.textContent = fullNameFrom(k);
        if (deleteModal) deleteModal.style.display = 'flex';
    }

    function closeDeleteConfirm() {
        pendingDeleteIndex = null;
        if (deleteModal) deleteModal.style.display = 'none';
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) closeDeleteConfirm();
        });
    }

    if (deleteCancelBtn) deleteCancelBtn.addEventListener('click', closeDeleteConfirm);

    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', () => {
            if (pendingDeleteIndex === null) return;
            kabataan.splice(pendingDeleteIndex, 1);
            closeDeleteConfirm();
            currentPage = 1;
            render();
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const d = getFormData();
            const lastName  = (d.lastName  || '').trim();
            const firstName = (d.firstName || '').trim();
            if (!lastName && !firstName) {
                alert('First Name or Last Name is required.');
                return;
            }

            const record = { ...defaultRecord(), ...d };
            record.age = record.age === '' ? 0 : Number(record.age) || 0;
            // Keep legacy aliases in sync
            record.email = record.emailAddress;
            record.dob   = record.birthday;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            setTimeout(() => {
                if (editingIndex !== null && kabataan[editingIndex]) {
                    kabataan[editingIndex] = record;
                    sortKabataanAlphabetically();
                } else {
                    kabataan.push(record);
                    sortKabataanAlphabetically();
                }
                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
            }, 500);
        });
    }

    function parseCsvTsv(text) {
        const lines = text.trim().split(/\r?\n/).filter(Boolean);
        return lines.map((line) => {
            const row = [];
            let current = '';
            let inQuotes = false;
            for (let i = 0; i < line.length; i++) {
                const c = line[i];
                if (c === '"') inQuotes = !inQuotes;
                else if ((c === ',' || c === '\t') && !inQuotes) { row.push(current.trim()); current = ''; }
                else current += c;
            }
            row.push(current.trim());
            return row;
        });
    }

    function rowToKabataan(headers, row) {
        const get = (name) => {
            const i = headers.findIndex((h) => String(h).toLowerCase().replace(/[\s/]/g, '').includes(name.toLowerCase().replace(/[\s/]/g, '')));
            return i >= 0 && row[i] !== undefined ? String(row[i]).trim() : '';
        };
        const respondentNumber = get('respondent') || get('respondentno') || get('respondent#') || '';
        const date = get('date') || '';
        const lastName  = get('lastname')  || get('lname')  || row[0] || '';
        const firstName = get('firstname') || get('fname')  || row[1] || '';
        const middleName = get('middlename') || get('mname') || row[2] || '';
        const suffix = get('suffix') || row[3] || '';
        const region = get('region') || '';
        const province = get('province') || '';
        const city = get('city') || get('municipality') || '';
        const barangay = get('barangay') || row[4] || '';
        const purokZone = get('purok') || get('zone') || get('purokzone') || '';
        const sex = (get('sex') || get('gender') || row[5] || 'Male').toLowerCase().startsWith('f') ? 'Female' : 'Male';
        const age = parseInt(get('age') || row[6], 10) || 0;
        const birthday = get('birthday') || get('birthdate') || get('dob') || '';
        const emailAddress = get('email') || get('emailaddress') || '';
        const contactNumber = get('contact') || get('contactnumber') || get('contact#') || '';
        const civilStatus = get('civilstatus') || get('civil') || 'Single';
        const youthClassification = get('youthclassification') || get('classification') || 'In School Youth';
        const youthAgeGroup = get('youthagegroup') || get('agegroup') || '';
        const workStatus = get('workstatus') || get('work') || '';
        const educationalBackground = get('educationalbackground') || get('education') || get('educational') || '';
        const registeredSKVoter = get('registeredskvoter') || get('skvoter') || 'Yes';
        const registeredNationalVoter = get('registerednationalvoter') || get('nationalvoter') || 'Yes';
        const votingHistory = get('votinghistory') || get('votedlastsk') || 'Yes';
        const votingFrequency = get('votingfrequency') || get('howmanytimes') || '';
        const votingReason = get('votingreason') || get('ifnowhy') || '';
        const attendedKKAssembly = get('attendedkkassembly') || get('kkassembly') || 'Yes';
        const facebookAccount = get('facebook') || get('fbaccount') || '';
        const willingToJoinGroupChat = get('willingtojoin') || get('groupchat') || 'Yes';
        const signature = get('signature') || '';
        return {
            ...defaultRecord(),
            respondentNumber, date,
            lastName, firstName, middleName, suffix,
            region, province, city, barangay, purokZone,
            sex, age, birthday,
            emailAddress, email: emailAddress, contactNumber,
            civilStatus, youthClassification, youthAgeGroup,
            workStatus, educationalBackground,
            registeredSKVoter, registeredNationalVoter,
            votingHistory, votingFrequency, votingReason,
            attendedKKAssembly,
            facebookAccount, willingToJoinGroupChat,
            signature,
        };
    }

    // ── Import Preview State ──────────────────────────────────────────────────
    let importPreviewRows = [];   // { record, issues[], valid }
    let importPreviewPage = 1;
    const importPreviewPerPage = 10;

    function validateImportRecord(rec) {
        const issues = [];
        if (!rec.lastName && !rec.firstName) issues.push('Missing name');
        const age = Number(rec.age);
        if (!age || age < 15 || age > 30) issues.push('Age must be 15–30');
        if (!rec.sex || !['Male','Female'].includes(rec.sex)) issues.push('Invalid sex');
        return issues;
    }

    function buildPreviewRows(rows) {
        if (!rows || rows.length < 2) return [];
        const headers = rows[0].map((h) => String(h).trim());
        const result = [];
        for (let i = 1; i < rows.length; i++) {
            if (rows[i].every(c => !String(c).trim())) continue; // skip blank rows
            const record = rowToKabataan(headers, rows[i]);
            const issues = validateImportRecord(record);
            result.push({ record, issues, valid: issues.length === 0 });
        }
        return result;
    }

    function renderImportPreview() {
        const previewSection = document.getElementById('kabImportPreviewSection');
        const uploadSection  = document.getElementById('kabImportUploadSection');
        const tbody          = document.getElementById('kabImportPreviewBody');
        const pageInfo       = document.getElementById('kabImportPageInfo');
        const prevBtn        = document.getElementById('kabImportPrevBtn');
        const nextBtn        = document.getElementById('kabImportNextBtn');
        const pageNumbers    = document.getElementById('kabImportPageNumbers');
        const badgeValid     = document.getElementById('kabImportBadgeValid');
        const badgeInvalid   = document.getElementById('kabImportBadgeInvalid');
        const badgeTotal     = document.getElementById('kabImportBadgeTotal');
        const errorBtn       = document.getElementById('kabImportErrorBtn');

        if (!previewSection || !tbody) return;

        const total   = importPreviewRows.length;
        const valid   = importPreviewRows.filter(r => r.valid).length;
        const invalid = total - valid;

        if (badgeValid)   badgeValid.textContent   = `✅ ${valid} Valid`;
        if (badgeInvalid) badgeInvalid.textContent = `⚠️ ${invalid} Invalid`;
        if (badgeTotal)   badgeTotal.textContent   = `📋 ${total} Total`;
        if (errorBtn)     errorBtn.style.display   = invalid > 0 ? 'inline-flex' : 'none';

        const totalPages = Math.max(1, Math.ceil(total / importPreviewPerPage));
        importPreviewPage = Math.min(importPreviewPage, totalPages);
        const start = (importPreviewPage - 1) * importPreviewPerPage;
        const end   = Math.min(start + importPreviewPerPage, total);
        const slice = importPreviewRows.slice(start, end);

        if (pageInfo) pageInfo.textContent = total === 0 ? 'No rows' : `Showing ${start + 1}–${end} of ${total} rows`;
        if (prevBtn)  prevBtn.disabled = importPreviewPage === 1;
        if (nextBtn)  nextBtn.disabled = importPreviewPage === totalPages;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let p = 1; p <= totalPages; p++) {
                const btn = document.createElement('button');
                btn.className = 'kab-import-page-num' + (p === importPreviewPage ? ' active' : '');
                btn.textContent = p;
                btn.onclick = () => { importPreviewPage = p; renderImportPreview(); };
                pageNumbers.appendChild(btn);
            }
        }

        tbody.innerHTML = '';
        if (slice.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="31" style="text-align:center;color:#6b7280;padding:20px;">No data to preview.</td>`;
            tbody.appendChild(tr);
        } else {
            slice.forEach((item, idx) => {
                const r = item.record;
                const rowNum = start + idx + 1;
                const statusHtml = item.valid
                    ? `<span class="kab-import-status kab-status-valid">✅ Valid</span>`
                    : `<span class="kab-import-status kab-status-invalid">⚠️ Invalid</span>`;
                const issuesHtml = item.issues.length
                    ? `<span class="kab-import-issues">${item.issues.join('; ')}</span>`
                    : `<span style="color:#6b7280;font-size:11px;">—</span>`;
                const tr = document.createElement('tr');
                tr.className = item.valid ? '' : 'kab-import-row-invalid';
                tr.innerHTML = `
                    <td>${rowNum}</td>
                    <td>${statusHtml}</td>
                    <td>${r.lastName || '—'}</td>
                    <td>${r.firstName || '—'}</td>
                    <td>${r.middleName || '—'}</td>
                    <td>${r.suffix || '—'}</td>
                    <td>${r.region || '—'}</td>
                    <td>${r.province || '—'}</td>
                    <td>${r.city || '—'}</td>
                    <td>${r.barangay || '—'}</td>
                    <td>${r.purokZone || '—'}</td>
                    <td>${r.sex || '—'}</td>
                    <td>${r.age || '—'}</td>
                    <td>${r.dateOfBirth || '—'}</td>
                    <td>${r.email || '—'}</td>
                    <td>${r.contactNumber || '—'}</td>
                    <td>${r.civilStatus || '—'}</td>
                    <td>${r.youthAgeGroup || '—'}</td>
                    <td>${r.youthClassification || '—'}</td>
                    <td>${r.educationalBackground || '—'}</td>
                    <td>${r.workStatus || '—'}</td>
                    <td>${r.registeredSKVoter || '—'}</td>
                    <td>${r.votingHistory || '—'}</td>
                    <td>${r.votingFrequency || '—'}</td>
                    <td>${r.votingReason || '—'}</td>
                    <td>${r.registeredNationalVoter || '—'}</td>
                    <td>${r.attendedKKAssembly || '—'}</td>
                    <td>${r.facebookAccount || '—'}</td>
                    <td>${r.willingToJoinGroupChat || '—'}</td>
                    <td>${r.signature || '—'}</td>
                    <td>${issuesHtml}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        if (uploadSection)  uploadSection.style.display  = 'none';
        if (previewSection) previewSection.style.display = 'block';
    }

    function showImportUpload() {
        const previewSection = document.getElementById('kabImportPreviewSection');
        const uploadSection  = document.getElementById('kabImportUploadSection');
        if (previewSection) previewSection.style.display = 'none';
        if (uploadSection)  uploadSection.style.display  = 'block';
        importPreviewRows = [];
        importPreviewPage = 1;
        const fileNameEl = document.getElementById('kabImportFileName');
        if (fileNameEl) fileNameEl.textContent = 'No file selected';
        const fileInput2 = document.getElementById('kabataanFileInput');
        if (fileInput2) fileInput2.value = '';
        const pasteInput2 = document.getElementById('kabataanPasteInput');
        if (pasteInput2) pasteInput2.value = '';
    }

    function importRows(rows) {
        importPreviewRows = buildPreviewRows(rows);
        importPreviewPage = 1;
        renderImportPreview();
    }

    // File input handler
    const fileInput = document.getElementById('kabataanFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const fileNameEl = document.getElementById('kabImportFileName');
            if (fileNameEl) fileNameEl.textContent = file.name;
            const name = (file.name || '').toLowerCase();
            if (name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = (ev) => { importRows(parseCsvTsv(ev.target.result)); };
                reader.readAsText(file, 'UTF-8');
            } else if (name.endsWith('.xlsx') || name.endsWith('.xls')) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    try {
                        if (typeof XLSX === 'undefined') { alert('Excel support not loaded. Use CSV.'); e.target.value = ''; return; }
                        const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array' });
                        const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, defval: '' });
                        importRows(rows);
                    } catch (err) { alert('Could not read Excel file. Try CSV instead.'); }
                };
                reader.readAsArrayBuffer(file);
            } else {
                alert('Unsupported format. Please use .csv, .xlsx, or .xls');
                e.target.value = '';
            }
        });
    }

    // Paste handler
    const importPasteBtn = document.getElementById('kabataanImportPasteBtn');
    const pasteInput = document.getElementById('kabataanPasteInput');
    if (importPasteBtn && pasteInput) {
        importPasteBtn.addEventListener('click', () => {
            const text = pasteInput.value.trim();
            if (!text) { alert('Paste data first.'); return; }
            importRows(parseCsvTsv(text));
        });
    }

    // Drag & drop
    const dropZone = document.getElementById('kabImportDropZone');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('kab-import-drag-over'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('kab-import-drag-over'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('kab-import-drag-over');
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const fileNameEl = document.getElementById('kabImportFileName');
            if (fileNameEl) fileNameEl.textContent = file.name;
            const name = (file.name || '').toLowerCase();
            if (name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = (ev) => importRows(parseCsvTsv(ev.target.result));
                reader.readAsText(file, 'UTF-8');
            } else if (name.endsWith('.xlsx') || name.endsWith('.xls')) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    try {
                        if (typeof XLSX === 'undefined') { alert('Excel support not loaded. Use CSV.'); return; }
                        const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array' });
                        const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, defval: '' });
                        importRows(rows);
                    } catch (err) { alert('Could not read Excel file.'); }
                };
                reader.readAsArrayBuffer(file);
            } else {
                alert('Unsupported format. Use .csv, .xlsx, or .xls');
            }
        });
    }

    // Cancel button
    const importCancelBtn = document.getElementById('kabImportCancelBtn');
    if (importCancelBtn) {
        importCancelBtn.addEventListener('click', showImportUpload);
    }

    // Pagination
    const importPrevBtn = document.getElementById('kabImportPrevBtn');
    const importNextBtn = document.getElementById('kabImportNextBtn');
    if (importPrevBtn) importPrevBtn.addEventListener('click', () => { importPreviewPage--; renderImportPreview(); });
    if (importNextBtn) importNextBtn.addEventListener('click', () => { importPreviewPage++; renderImportPreview(); });

    // Download error report
    const importErrorBtn = document.getElementById('kabImportErrorBtn');
    if (importErrorBtn) {
        importErrorBtn.addEventListener('click', () => {
            const invalid = importPreviewRows.filter(r => !r.valid);
            if (!invalid.length) return;
            const headers = ['Row','Last Name','First Name','Middle Name','Suffix','Region','Province','City/Municipality','Barangay','Purok/Zone','Sex','Age','Birthday','Email','Contact #','Civil Status','Youth Age Group','Youth Classification','Educational Background','Work Status','SK Voter','Voted Last SK','Voting Frequency','Voting Reason','National Voter','KK Assembly','FB Account','Group Chat','Signature','Issues'];
            const lines = [headers.join(',')];
            invalid.forEach((item, idx) => {
                const r = item.record;
                const row = [
                    idx + 1,
                    `"${r.lastName || ''}"`,
                    `"${r.firstName || ''}"`,
                    `"${r.middleName || ''}"`,
                    `"${r.suffix || ''}"`,
                    `"${r.region || ''}"`,
                    `"${r.province || ''}"`,
                    `"${r.city || ''}"`,
                    `"${r.barangay || ''}"`,
                    `"${r.purokZone || ''}"`,
                    `"${r.sex || ''}"`,
                    r.age || '',
                    `"${r.dateOfBirth || ''}"`,
                    `"${r.email || ''}"`,
                    `"${r.contactNumber || ''}"`,
                    `"${r.civilStatus || ''}"`,
                    `"${r.youthAgeGroup || ''}"`,
                    `"${r.youthClassification || ''}"`,
                    `"${r.educationalBackground || ''}"`,
                    `"${r.workStatus || ''}"`,
                    `"${r.registeredSKVoter || ''}"`,
                    `"${r.votingHistory || ''}"`,
                    `"${r.votingFrequency || ''}"`,
                    `"${r.votingReason || ''}"`,
                    `"${r.registeredNationalVoter || ''}"`,
                    `"${r.attendedKKAssembly || ''}"`,
                    `"${r.facebookAccount || ''}"`,
                    `"${r.willingToJoinGroupChat || ''}"`,
                    `"${r.signature || ''}"`,
                    `"${item.issues.join('; ')}"`,
                ];
                lines.push(row.join(','));
            });
            const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'kabataan_import_errors.csv';
            a.click();
            URL.revokeObjectURL(url);
        });
    }

    // Confirm import
    const importConfirmBtn = document.getElementById('kabImportConfirmBtn');
    if (importConfirmBtn) {
        importConfirmBtn.addEventListener('click', () => {
            const validRows = importPreviewRows.filter(r => r.valid);
            if (!validRows.length) { alert('No valid rows to import.'); return; }
            validRows.forEach(item => kabataan.push(item.record));
            sortKabataanAlphabetically();
            closeModal();
            render();
            showImportUpload();
            alert(`✅ Successfully imported ${validRows.length} record(s).`);
        });
    }

    render();
}


/**
 * ═══════════════════════════════════════════════════════════════
 * SIGNATURE PAD OVERLAY FUNCTIONALITY
 * ═══════════════════════════════════════════════════════════════
 */
(function initializeSignaturePad() {
    const overlay = document.getElementById('signaturePadOverlay');
    const triggerBtn = document.getElementById('kabataanSignatureTrigger');
    const closeBtn = document.getElementById('signaturePadClose');
    const cancelBtn = document.getElementById('signaturePadCancel');
    const clearBtn = document.getElementById('signaturePadClear');
    const saveBtn = document.getElementById('signaturePadSave');
    const canvas = document.getElementById('signaturePadCanvas');
    const placeholder = document.getElementById('signatureCanvasPlaceholder');
    const signatureInput = document.getElementById('kabataanSignature');
    const signaturePreview = document.getElementById('kabataanSignaturePreview');
    
    // New elements
    const nameInput = document.getElementById('kabataanSignatureName');
    const signatureDisplayContainer = document.getElementById('kabataanSignatureDisplayContainer');
    const signaturePrintedName = document.getElementById('kabataanSignaturePrintedName');
    
    if (!canvas || !overlay) return;
    
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasSignature = false;
    let strokes = [];
    let currentStroke = [];
    
    // Show sign button only after name is entered and no signature yet
    if (nameInput && triggerBtn) {
        nameInput.addEventListener('input', function() {
            const nameValue = this.value.trim();
            const hasSignatureValue = signatureInput && signatureInput.value;
            
            if (nameValue.length > 0 && !hasSignatureValue) {
                triggerBtn.style.display = 'inline-flex';
            } else {
                triggerBtn.style.display = 'none';
            }
        });
    }
    
    // Setup canvas
    function setupCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    
    // Open signature pad
    function openSignaturePad() {
        overlay.style.display = 'flex';
        setupCanvas();
        
        // Restore existing signature if any
        if (signatureInput.value) {
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                hasSignature = true;
                hidePlaceholder();
            };
            img.src = signatureInput.value;
        }
    }
    
    // Close signature pad
    function closeSignaturePad() {
        overlay.style.display = 'none';
        clearCanvas();
    }
    
    // Clear canvas
    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        strokes = [];
        currentStroke = [];
        hasSignature = false;
        showPlaceholder();
    }
    
    // Hide placeholder
    function hidePlaceholder() {
        if (placeholder) placeholder.style.display = 'none';
    }
    
    // Show placeholder
    function showPlaceholder() {
        if (placeholder && !hasSignature) placeholder.style.display = 'block';
    }
    
    // Get mouse/touch position
    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }
    
    // Start drawing
    function startDrawing(e) {
        isDrawing = true;
        hasSignature = true;
        currentStroke = [];
        const pos = getPosition(e);
        currentStroke.push(pos);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        hidePlaceholder();
    }
    
    // Draw
    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const pos = getPosition(e);
        currentStroke.push(pos);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    
    // Stop drawing
    function stopDrawing() {
        if (isDrawing && currentStroke.length > 0) {
            strokes.push([...currentStroke]);
        }
        isDrawing = false;
    }
    
    // Save signature
    function saveSignature() {
        if (!hasSignature) {
            alert('Please provide a signature before saving.');
            return;
        }
        
        // Convert canvas to base64
        const signatureData = canvas.toDataURL('image/png');
        
        // Save to hidden input
        signatureInput.value = signatureData;
        
        // Show signature overlay (centered on top of name)
        const signatureOverlay = document.getElementById('kabataanSignatureOverlay');
        if (signaturePreview && signatureOverlay) {
            signaturePreview.src = signatureData;
            signatureOverlay.style.display = 'flex';
        }
        
        // Hide the sign button
        if (triggerBtn) {
            triggerBtn.style.display = 'none';
        }
        
        // Close modal
        closeSignaturePad();
    }
    
    // Event listeners
    if (triggerBtn) {
        triggerBtn.addEventListener('click', openSignaturePad);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSignaturePad);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeSignaturePad);
    }
    
    if (clearBtn) {
        clearBtn.addEventListener('click', clearCanvas);
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', saveSignature);
    }
    
    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeSignaturePad();
            }
        });
    }
    
    // Canvas drawing events
    if (canvas) {
        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);
        
        // Touch events
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            startDrawing(e);
        });
        
        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            draw(e);
        });
        
        canvas.addEventListener('touchend', function(e) {
            e.preventDefault();
            stopDrawing();
        });
    }
    
    // Window resize handler
    window.addEventListener('resize', function() {
        if (overlay.style.display === 'flex') {
            setupCanvas();
        }
    });
})();

/**
 * ═══════════════════════════════════════════════════════════════
 * AUTO-POPULATE SIGNATURE NAME FROM RESPONDENT NAME
 * ═══════════════════════════════════════════════════════════════
 */
(function autoPopulateSignatureName() {
    const firstNameInput = document.getElementById('kabataanFirstName');
    const middleNameInput = document.getElementById('kabataanMiddleName');
    const lastNameInput = document.getElementById('kabataanLastName');
    const suffixSelect = document.getElementById('kabataanSuffix');
    const signatureNameInput = document.getElementById('kabataanSignatureName');
    
    if (!firstNameInput || !middleNameInput || !lastNameInput || !suffixSelect || !signatureNameInput) {
        return;
    }
    
    function updateSignatureName() {
        const firstName = firstNameInput.value.trim();
        const middleName = middleNameInput.value.trim();
        const lastName = lastNameInput.value.trim();
        const suffix = suffixSelect.value.trim();
        
        // Format: FirstName MiddleName LastName Suffix (if suffix exists)
        let fullName = '';
        
        if (firstName) {
            fullName += firstName;
        }
        
        if (middleName) {
            fullName += (fullName ? ' ' : '') + middleName;
        }
        
        if (lastName) {
            fullName += (fullName ? ' ' : '') + lastName;
        }
        
        if (suffix && suffix !== 'None' && suffix !== '') {
            fullName += (fullName ? ' ' : '') + suffix;
        }
        
        signatureNameInput.value = fullName;
        
        // Trigger the input event to show/hide the Sign button
        const event = new Event('input', { bubbles: true });
        signatureNameInput.dispatchEvent(event);
    }
    
    // Add event listeners to all name fields
    firstNameInput.addEventListener('input', updateSignatureName);
    middleNameInput.addEventListener('input', updateSignatureName);
    lastNameInput.addEventListener('input', updateSignatureName);
    suffixSelect.addEventListener('change', updateSignatureName);
})();
