document.addEventListener('DOMContentLoaded', () => {
    initializeKabataanUI();
});

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
        firstName: '', middleName: '', lastName: '', suffix: '',
        dob: '', age: 0, sex: 'Male', civilStatus: 'Single',
        region: '', province: '', city: '', barangay: '', address: '',
        email: '', contactNumber: '',
        highestEducation: '', currentlyStudying: 'Yes',
        workStatus: '', occupation: '',
        registeredVoter: 'Yes', votedLastElection: 'No', youthClassification: 'ISY',
    });

    const kabataan = [
        { ...defaultRecord(), firstName: 'Juan', middleName: 'Miguel', lastName: 'Reyes', suffix: 'Jr.', age: 21, sex: 'Male', barangay: 'Purok 1', highestEducation: 'College Level', workStatus: 'Student', contactNumber: '09123456789', email: 'juan.reyes@email.com' },
        { ...defaultRecord(), firstName: 'Maria', middleName: 'Beatriz', lastName: 'Cruz', age: 19, sex: 'Female', barangay: 'Sitio 2', highestEducation: 'High School Graduate', workStatus: 'Student', contactNumber: '09123456790', email: 'maria.cruz@email.com' },
        { ...defaultRecord(), firstName: 'Antonio', middleName: 'Carlos', lastName: 'Garcia', age: 23, sex: 'Male', barangay: 'Villa Gracias', highestEducation: 'College Graduate', workStatus: 'Employed', contactNumber: '09123456791', email: 'antonio.garcia@email.com' },
        { ...defaultRecord(), firstName: 'Angelica', middleName: 'Sofia', lastName: 'Santillan', suffix: '', age: 20, sex: 'Female', barangay: 'Bayside Calios', highestEducation: 'College Level', workStatus: 'Student', contactNumber: '09123456792', email: 'angelica.santillan@email.com' },
        { ...defaultRecord(), firstName: 'Carlos', middleName: 'Domingo', lastName: 'Mendoza', suffix: 'Sr.', age: 22, sex: 'Male', barangay: 'Purok 3', highestEducation: 'College Level', workStatus: 'Student', contactNumber: '09123456793', email: 'carlos.mendoza@email.com' },
        { ...defaultRecord(), firstName: 'Patricia', middleName: 'Rosa', lastName: 'Del Rosario', age: 18, sex: 'Female', barangay: 'Sitio 1', highestEducation: 'High School', workStatus: 'Student', contactNumber: '09123456794', email: 'patricia.rosario@email.com' },
        { ...defaultRecord(), firstName: 'Miguel', middleName: 'Antonio', lastName: 'Fernandez', suffix: 'III', age: 24, sex: 'Male', barangay: 'Purok 4', highestEducation: 'College Graduate', workStatus: 'Employed', contactNumber: '09123456795', email: 'miguel.fernandez@email.com' },
        { ...defaultRecord(), firstName: 'Sofia', middleName: 'Isabel', lastName: 'Castillo', age: 17, sex: 'Female', barangay: 'Sitio 3', highestEducation: 'High School', workStatus: 'Student', contactNumber: '09123456796', email: 'sofia.castillo@email.com' },
        { ...defaultRecord(), firstName: 'Jose', middleName: 'Luis', lastName: 'Rivera', age: 20, sex: 'Male', barangay: 'Purok 5', highestEducation: 'College Level', workStatus: 'NEET', contactNumber: '09123456797', email: 'jose.rivera@email.com' },
        { ...defaultRecord(), firstName: 'Ana', middleName: 'Katrina', lastName: 'Villanueva', age: 19, sex: 'Female', barangay: 'Sitio 4', highestEducation: 'College Level', workStatus: 'Student', contactNumber: '09123456798', email: 'ana.villanueva@email.com' },
        { ...defaultRecord(), firstName: 'Roberto', middleName: 'Francisco', lastName: 'Santos', age: 21, sex: 'Male', barangay: 'Purok 6', highestEducation: 'College Level', workStatus: 'Employed', contactNumber: '09123456799', email: 'roberto.santos@email.com' },
        { ...defaultRecord(), firstName: 'Catherine', middleName: 'Mae', lastName: 'Lopez', age: 18, sex: 'Female', barangay: 'Sitio 5', highestEducation: 'High School', workStatus: 'Student', contactNumber: '09123456800', email: 'catherine.lopez@email.com' }
    ];

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

    // Pagination variables
    let currentPage = 1;
    const recordsPerPage = 10;

    function render() {
        tbody.innerHTML = '';

        const filtered = kabataan.filter((k) => {
            const q = currentQuery;
            const full = fullNameFrom(k).toLowerCase();
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (k.highestEducation && k.highestEducation.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            const matchEducation = !currentEducation || k.highestEducation === currentEducation;
            return matchSearch && matchGender && matchPurok && matchEducation;
        });

        // Calculate pagination
        const totalPages = Math.ceil(filtered.length / recordsPerPage);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, filtered.length);
        const paginatedData = filtered.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 6;
            td.textContent = 'No kabataan match current filters.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationInfo(0, 0, 1);
            return;
        }

        paginatedData.forEach((k) => {
            const index = kabataan.indexOf(k);
            const full = fullNameFrom(k);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="kabataan-fullname-cell">
                    <span class="kabataan-fullname">${full}</span>
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

        updatePaginationInfo(startIndex + 1, endIndex, currentPage, totalPages);
        updatePaginationControls(currentPage, totalPages);
    }

    function updatePaginationInfo(start, end, page, totalPages) {
        const info = document.getElementById('kabataanPaginationInfo');
        if (info) {
            const total = kabataan.filter((k) => {
                const q = currentQuery;
                const full = fullNameFrom(k).toLowerCase();
                const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (k.highestEducation && k.highestEducation.toLowerCase().includes(q));
                const matchGender = !currentGender || k.sex === currentGender;
                const matchPurok = !currentPurok || k.barangay === currentPurok;
                return matchSearch && matchGender && matchPurok;
            }).length;

            info.textContent = total === 0 ? 'No records found' : `Showing ${start}-${end} of ${total} records`;
        }
    }

    function updatePaginationControls(page, totalPages) {
        const prevBtn = document.getElementById('kabataanPrevBtn');
        const nextBtn = document.getElementById('kabataanNextBtn');
        const pageNumbers = document.getElementById('kabataanPageNumbers');

        if (prevBtn) prevBtn.disabled = page === 1;
        if (nextBtn) nextBtn.disabled = page === totalPages;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';

            // Show max 5 page numbers
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);

            // Adjust if we're near the beginning
            if (endPage - startPage < 5) {
                endPage = Math.min(5, totalPages);
                startPage = 1;
            }

            // Adjust if we're near the end
            if (endPage - startPage < 5 && page > totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
                endPage = totalPages;
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `page-number ${i === page ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToPage(i);
                pageNumbers.appendChild(pageBtn);
            }
        }
    }

    function goToPage(page) {
        const totalPages = Math.ceil(kabataan.filter((k) => {
            const q = currentQuery;
            const full = fullNameFrom(k).toLowerCase();
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (k.highestEducation && k.highestEducation.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            return matchSearch && matchGender && matchPurok;
        }).length / recordsPerPage);

        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            render();
        }
    }

    function populateViewRows(k) {
        if (!viewColumnLeft || !viewColumnRight) return;

        // Add section titles and fields to left column
        viewColumnLeft.innerHTML = `
            <div class="kabataan-view-section-title">Personal Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">First Name:</span><span class="kabataan-view-value">${k.firstName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Middle Name:</span><span class="kabataan-view-value">${k.middleName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Last Name:</span><span class="kabataan-view-value">${k.lastName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Suffix:</span><span class="kabataan-view-value">${k.suffix || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">AGE:</span><span class="kabataan-view-value">${k.age || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">BIRTHDAY:</span><span class="kabataan-view-value">${k.dob || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">SEX ASSIGNED AT BIRTH:</span><span class="kabataan-view-value">${k.sex || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">CIVIL STATUS:</span><span class="kabataan-view-value">${k.civilStatus || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Location Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">REGION:</span><span class="kabataan-view-value">${k.region || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">PROVINCE:</span><span class="kabataan-view-value">${k.province || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">CITY/MUNICIPALITY:</span><span class="kabataan-view-value">${k.city || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">BARANGAY:</span><span class="kabataan-view-value">${k.barangay || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">HOME ADDRESS:</span><span class="kabataan-view-value">SAN LUIS</span></div>
        `;

        // Add section titles and fields to right column
        viewColumnRight.innerHTML = `
            <div class="kabataan-view-section-title">Youth Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">YOUTH CLASSIFICATION:</span><span class="kabataan-view-value">${k.youthClassification || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">YOUTH AGE GROUP:</span><span class="kabataan-view-value">${k.age || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">CONTACT NUMBER:</span><span class="kabataan-view-value">${k.contactNumber || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">HIGHEST EDUCATIONAL ATTAINMENT:</span><span class="kabataan-view-value">${k.highestEducation || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">WORK STATUS:</span><span class="kabataan-view-value">${k.workStatus || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Civic Participation</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">REGISTERED VOTER?:</span><span class="kabataan-view-value">${k.registeredVoter || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">VOTED LAST ELECTION?:</span><span class="kabataan-view-value">${k.votedLastElection || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">ATTENDED KK ASSEMBLY?:</span><span class="kabataan-view-value">${k.currentlyStudying || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">IF YES, HOW MANY TIMES?:</span><span class="kabataan-view-value">${k.id || '-'}</span></div>
        `;
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
                    // Re-sort after editing to maintain alphabetical order
                    sortKabataanAlphabetically();
                } else {
                    kabataan.push(record);
                    // Re-sort after adding to maintain alphabetical order
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
        // Re-sort after importing to maintain alphabetical order
        sortKabataanAlphabetically();
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
