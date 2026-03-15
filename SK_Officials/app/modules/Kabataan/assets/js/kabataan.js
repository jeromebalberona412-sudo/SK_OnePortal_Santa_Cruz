document.addEventListener('DOMContentLoaded', () => {
    initializeKabataanUI();
});

function initializeKabataanUI() {
    const tbody = document.getElementById('kabataanTableBody');
    const searchInput = document.getElementById('kabataanSearch');
    const genderFilter = document.getElementById('kabataanGenderFilter');
    const purokFilter = document.getElementById('kabataanPurokFilter');

    const addBtn = document.getElementById('addKabataanBtn');
    const modal = document.getElementById('kabataanModal');
    const modalBox = modal ? modal.querySelector('.kabataan-modal-box') : null;
    const modalTitle = document.getElementById('kabataanModalTitle');
    const saveBtn = document.getElementById('kabataanSaveBtn');
    const toggleBtn = document.getElementById('kabataanModalToggle');
    const fileInput = document.getElementById('kabataanFileInput');
    const pasteInput = document.getElementById('kabataanPasteInput');
    const importPasteBtn = document.getElementById('kabataanImportPasteBtn');

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
        'kabataanFirstName', 'kabataanMiddleName', 'kabataanLastName', 'kabataanSuffix',
        'kabataanDob', 'kabataanAgeInput', 'kabataanSex', 'kabataanCivilStatus',
        'kabataanRegion', 'kabataanProvince', 'kabataanCity', 'kabataanBarangay', 'kabataanAddress',
        'kabataanEmail', 'kabataanContactInput',
        'kabataanHighestEducation', 'kabataanCurrentlyStudying',
        'kabataanWorkStatus', 'kabataanOccupation',
        'kabataanRegisteredVoter', 'kabataanVotedLastElection', 'kabataanYouthClassification'
    ];

    const viewFieldLabels = [
        ['First Name', 'firstName'],
        ['Middle Name', 'middleName'],
        ['Last Name', 'lastName'],
        ['Suffix', 'suffix'],
        ['Date of Birth', 'dob'],
        ['Age', 'age'],
        ['Sex Assigned at Birth', 'sex'],
        ['Civil Status', 'civilStatus'],
        ['Region', 'region'],
        ['Province', 'province'],
        ['City/Municipality', 'city'],
        ['Barangay', 'barangay'],
        ['Address', 'address'],
        ['Email', 'email'],
        ['Contact Number', 'contactNumber'],
        ['Highest Educational Attainment', 'highestEducation'],
        ['Currently Studying', 'currentlyStudying'],
        ['Work Status', 'workStatus'],
        ['Occupation', 'occupation'],
        ['Registered Voter', 'registeredVoter'],
        ['Voted in Last Election', 'votedLastElection'],
        ['Youth Classification', 'youthClassification'],
    ];

    if (!tbody) return;

    function getField(id) { return document.getElementById(id); }

    function getFormData() {
        const o = {};
        const map = {
            kabataanFirstName: 'firstName', kabataanMiddleName: 'middleName', kabataanLastName: 'lastName', kabataanSuffix: 'suffix',
            kabataanDob: 'dob', kabataanAgeInput: 'age', kabataanSex: 'sex', kabataanCivilStatus: 'civilStatus',
            kabataanRegion: 'region', kabataanProvince: 'province', kabataanCity: 'city', kabataanBarangay: 'barangay', kabataanAddress: 'address',
            kabataanEmail: 'email', kabataanContactInput: 'contactNumber',
            kabataanHighestEducation: 'highestEducation', kabataanCurrentlyStudying: 'currentlyStudying',
            kabataanWorkStatus: 'workStatus', kabataanOccupation: 'occupation',
            kabataanRegisteredVoter: 'registeredVoter', kabataanVotedLastElection: 'votedLastElection', kabataanYouthClassification: 'youthClassification',
        };
        fieldIds.forEach((id) => {
            const el = getField(id);
            if (!el) return;
            const key = map[id];
            if (key) o[key] = el.type === 'number' ? (el.value === '' ? '' : Number(el.value)) : el.value;
        });
        return o;
    }

    function setFormData(k) {
        if (!k) return;
        const map = {
            firstName: 'kabataanFirstName', middleName: 'kabataanMiddleName', lastName: 'kabataanLastName', suffix: 'kabataanSuffix',
            dob: 'kabataanDob', age: 'kabataanAgeInput', sex: 'kabataanSex', civilStatus: 'kabataanCivilStatus',
            region: 'kabataanRegion', province: 'kabataanProvince', city: 'kabataanCity', barangay: 'kabataanBarangay', address: 'kabataanAddress',
            email: 'kabataanEmail', contactNumber: 'kabataanContactInput',
            highestEducation: 'kabataanHighestEducation', currentlyStudying: 'kabataanCurrentlyStudying',
            workStatus: 'kabataanWorkStatus', occupation: 'kabataanOccupation',
            registeredVoter: 'kabataanRegisteredVoter', votedLastElection: 'kabataanVotedLastElection', youthClassification: 'kabataanYouthClassification',
        };
        Object.keys(map).forEach((key) => {
            const id = map[key];
            const el = getField(id);
            if (!el) return;
            const val = k[key];
            el.value = val === null || val === undefined ? '' : String(val);
        });
    }

    function clearForm() {
        setFormData({
            firstName: '', middleName: '', lastName: '', suffix: '',
            dob: '', age: '', sex: 'Male', civilStatus: 'Single',
            region: '', province: '', city: '', barangay: '', address: '',
            email: '', contactNumber: '',
            highestEducation: '', currentlyStudying: 'Yes',
            workStatus: '', occupation: '',
            registeredVoter: 'Yes', votedLastElection: 'No', youthClassification: 'ISY',
        });
    }

    function fullNameFrom(k) {
        const parts = [k.firstName, k.middleName, k.lastName].filter(Boolean);
        return parts.length ? parts.join(' ') + (k.suffix ? ' ' + k.suffix : '') : '-';
    }

    const defaultRecord = () => ({
        firstName: '', middleName: '', lastName: '', suffix: '',
        dob: '', age: 0, sex: 'Male', civilStatus: 'Single',
        region: '', province: '', city: '', barangay: '', address: '',
        email: '', contactNumber: '',
        highestEducation: '', currentlyStudying: 'Yes',
        workStatus: '', occupation: '',
        registeredVoter: 'Yes', votedLastElection: 'No', youthClassification: 'ISY',
    });

    const kabataan = [
        { ...defaultRecord(), firstName: 'Juan', middleName: 'Dela', lastName: 'Cruz', suffix: 'Jr.', age: 21, sex: 'Male', barangay: 'Purok 3', highestEducation: 'College', workStatus: 'Student', contactNumber: '09171234567' },
        { ...defaultRecord(), firstName: 'Maria', middleName: 'M.', lastName: 'Santos', age: 19, sex: 'Female', barangay: 'Purok 2', highestEducation: 'High School', workStatus: 'Student', contactNumber: '09187654321' },
        { ...defaultRecord(), firstName: 'Kevin', middleName: 'R.', lastName: 'Cruz', age: 23, sex: 'Male', barangay: 'Purok 1', highestEducation: 'College', workStatus: '', contactNumber: '09201234567' },
        { ...defaultRecord(), firstName: 'Angelica', middleName: 'L.', lastName: 'Reyes', suffix: '', age: 20, sex: 'Female', barangay: 'Purok 4', highestEducation: 'College', workStatus: 'Student', contactNumber: '09169876543' },
    ];

    let currentQuery = '';
    let currentGender = '';
    let currentPurok = '';
    let editingIndex = null;

    function render() {
        tbody.innerHTML = '';

        const filtered = kabataan.filter((k) => {
            const q = currentQuery;
            const full = fullNameFrom(k).toLowerCase();
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (k.highestEducation && k.highestEducation.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            return matchSearch && matchGender && matchPurok;
        });

        if (filtered.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 6;
            td.textContent = 'No kabataan match the current filters.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        filtered.forEach((k) => {
            const index = kabataan.indexOf(k);
            const full = fullNameFrom(k);
            const subText = `FN, MN, LN, ${k.suffix || 'None'}`;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="kabataan-fullname-cell">
                    <span class="kabataan-fullname">${full}</span>
                    <small class="kabataan-fullname-sub">${subText}</small>
                </td>
                <td>${k.age || '-'}</td>
                <td>${k.sex || '-'}</td>
                <td>${k.barangay || '-'}</td>
                <td>${k.highestEducation || '-'}</td>
                <td>
                    <div class="kabataan-actions">
                        <button type="button" class="btn-action-view" data-action="view" data-index="${index}">View</button>
                        <button type="button" class="btn-action-edit" data-action="edit" data-index="${index}">Edit</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    if (searchInput) searchInput.addEventListener('input', () => { currentQuery = searchInput.value.trim().toLowerCase(); render(); });
    if (genderFilter) genderFilter.addEventListener('change', () => { currentGender = genderFilter.value; render(); });
    if (purokFilter) purokFilter.addEventListener('change', () => { currentPurok = purokFilter.value; render(); });

    function populateViewRows(k) {
        if (!viewColumnLeft || !viewColumnRight) return;

        // Clear both columns
        viewColumnLeft.innerHTML = '';
        viewColumnRight.innerHTML = '';

        // Split the fields into two groups
        const leftFields = viewFieldLabels.slice(0, 11); // First 11 fields
        const rightFields = viewFieldLabels.slice(11); // Remaining fields

        // Populate left column
        leftFields.forEach(([label, key]) => {
            const row = document.createElement('div');
            row.className = 'kabataan-view-row';
            const val = k[key];
            row.innerHTML = `<span class="kabataan-view-label">${label}:</span><span class="kabataan-view-value">${val === null || val === undefined || val === '' ? '-' : val}</span>`;
            viewColumnLeft.appendChild(row);
        });

        // Populate right column
        rightFields.forEach(([label, key]) => {
            const row = document.createElement('div');
            row.className = 'kabataan-view-row';
            const val = k[key];
            row.innerHTML = `<span class="kabataan-view-label">${label}:</span><span class="kabataan-view-value">${val === null || val === undefined || val === '' ? '-' : val}</span>`;
            viewColumnRight.appendChild(row);
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
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const d = getFormData();
            const firstName = (d.firstName || '').trim();
            if (!firstName) {
                alert('First Name is required.');
                return;
            }

            const record = { ...defaultRecord(), ...d };
            record.age = record.age === '' ? 0 : Number(record.age) || 0;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            setTimeout(() => {
                if (editingIndex !== null && kabataan[editingIndex]) {
                    kabataan[editingIndex] = record;
                } else {
                    kabataan.push(record);
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
            const i = headers.findIndex((h) => String(h).toLowerCase().replace(/\s/g, '').includes(name.toLowerCase().replace(/\s/g, '')));
            return i >= 0 && row[i] !== undefined ? String(row[i]).trim() : '';
        };
        const firstName = get('firstname') || get('fname') || row[0] || '';
        const middleName = get('middlename') || get('mname') || row[1] || '';
        const lastName = get('lastname') || get('lname') || row[2] || '';
        const suffix = get('suffix') || row[3] || '';
        const age = parseInt(get('age') || row[4], 10) || 0;
        const sex = (get('sex') || row[5] || 'Male').toLowerCase().startsWith('f') ? 'Female' : 'Male';
        const barangay = get('barangay') || row[6] || '';
        const highestEducation = get('highesteducation') || get('education') || row[7] || '';
        const contactNumber = get('contact') || row[8] || '';
        return { ...defaultRecord(), firstName, middleName, lastName, suffix, age, sex, barangay, highestEducation, contactNumber };
    }

    function importRows(rows) {
        if (!rows || rows.length < 2) return;
        const headers = rows[0].map((h) => String(h).trim());
        for (let i = 1; i < rows.length; i++) {
            const obj = rowToKabataan(headers, rows[i]);
            if (obj.firstName) kabataan.push(obj);
        }
        render();
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const name = (file.name || '').toLowerCase();
            if (name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = (ev) => { const rows = parseCsvTsv(ev.target.result); importRows(rows); e.target.value = ''; };
                reader.readAsText(file, 'UTF-8');
            } else if (name.endsWith('.xlsx') || name.endsWith('.xls')) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    try {
                        if (typeof XLSX === 'undefined') { alert('Excel support not loaded. Use CSV.'); e.target.value = ''; return; }
                        const wb = XLSX.read(new Uint8Array(ev.target.result), { type: 'array' });
                        const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, defval: '' });
                        importRows(rows);
                    } catch (err) { alert('Could not read Excel. Try CSV.'); }
                    e.target.value = '';
                };
                reader.readAsArrayBuffer(file);
            } else { alert('Use .csv or .xlsx'); e.target.value = ''; }
        });
    }

    if (importPasteBtn && pasteInput) {
        importPasteBtn.addEventListener('click', () => {
            const text = pasteInput.value.trim();
            if (!text) { alert('Paste data first.'); return; }
            const rows = parseCsvTsv(text);
            importRows(rows);
            pasteInput.value = '';
        });
    }

    render();
}
