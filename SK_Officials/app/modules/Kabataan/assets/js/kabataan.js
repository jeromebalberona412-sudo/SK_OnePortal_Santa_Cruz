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

        // Map email to emailAddress for compatibility
        if (o.email) o.emailAddress = o.email;

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
            // Use emailAddress if email is not available (new data structure)
            const val = k[key] !== undefined ? k[key] : (key === 'email' ? k.emailAddress : undefined);
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
        respondentNumber: '',
        date: '',
        firstName: '', middleName: '', lastName: '', suffix: '',
        region: '', province: '', city: '', barangay: '', purokZone: '',
        sex: 'Male', age: 0, birthday: '',
        emailAddress: '', contactNumber: '',
        civilStatus: 'Single',
        youthClassification: '',
        youthAgeGroup: '',
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
        // Legacy fields for compatibility
        dob: '', address: '', highestEducation: '', currentlyStudying: 'Yes',
        occupation: '', registeredVoter: 'Yes', votedLastElection: 'No',
    });

    const kabataan = [
        {
            respondentNumber: '001',
            date: '2026-03-12',
            firstName: 'Juan',
            middleName: 'Miguel',
            lastName: 'Reyes',
            suffix: 'Jr.',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'BAYSIDE',
            purokZone: 'Zone 1',
            sex: 'Male',
            age: 21,
            birthday: '15/04/2005',
            emailAddress: 'juan.reyes@email.com',
            contactNumber: '09123456789',
            civilStatus: 'Single',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Core Youth (18–24 yrs old)',
            workStatus: 'Student',
            educationalBackground: 'College Level',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '1–2 times',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'juan.reyes.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Juan Reyes'
        },
        {
            respondentNumber: '002',
            date: '2026-03-13',
            firstName: 'Maria',
            middleName: 'Beatriz',
            lastName: 'Cruz',
            suffix: '',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'VILLA GRACIA',
            purokZone: 'Zone 2',
            sex: 'Female',
            age: 19,
            birthday: '22/08/2006',
            emailAddress: 'maria.cruz@email.com',
            contactNumber: '09123456790',
            civilStatus: 'Single',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Core Youth (18–24 yrs old)',
            workStatus: 'Student',
            educationalBackground: 'College Level',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'No',
            votingHistory: 'No',
            votingReason: 'Not interested to attend',
            attendedKKAssembly: 'No',
            facebookAccount: 'maria.cruz.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Maria Cruz'
        },
        {
            respondentNumber: '003',
            date: '2026-03-14',
            firstName: 'Antonio',
            middleName: 'Carlos',
            lastName: 'Garcia',
            suffix: 'Sr.',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'IMELDA',
            purokZone: 'Zone 3',
            sex: 'Male',
            age: 23,
            birthday: '10/12/2002',
            emailAddress: 'antonio.garcia@email.com',
            contactNumber: '09123456791',
            civilStatus: 'Single',
            youthClassification: 'Working Youth',
            youthAgeGroup: 'Core Youth (18–24 yrs old)',
            workStatus: 'Employed',
            educationalBackground: 'College Graduate',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '3–4 times',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'antonio.garcia.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Antonio Garcia'
        },
        {
            respondentNumber: '004',
            date: '2026-03-15',
            firstName: 'Angelica',
            middleName: 'Sofia',
            lastName: 'Santillan',
            suffix: '',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'LUPANG PANGAKO',
            purokZone: 'Zone 4',
            sex: 'Female',
            age: 20,
            birthday: '05/06/2005',
            emailAddress: 'angelica.santillan@email.com',
            contactNumber: '09123456792',
            civilStatus: 'Single',
            youthClassification: 'Out of School Youth',
            youthAgeGroup: 'Core Youth (18–24 yrs old)',
            workStatus: 'Unemployed',
            educationalBackground: 'High School Graduate',
            registeredSKVoter: 'No',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '1–2 times',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'angelica.santillan.fb',
            willingToJoinGroupChat: 'No',
            signature: 'Angelica Santillan'
        },
        {
            respondentNumber: '005',
            date: '2026-03-16',
            firstName: 'Carlos',
            middleName: 'Domingo',
            lastName: 'Mendoza',
            suffix: 'Jr.',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'DAMAYAN',
            purokZone: 'Zone 5',
            sex: 'Male',
            age: 22,
            birthday: '18/09/2003',
            emailAddress: 'carlos.mendoza@email.com',
            contactNumber: '09123456793',
            civilStatus: 'Married',
            youthClassification: 'Working Youth',
            youthAgeGroup: 'Core Youth (18–24 yrs old)',
            workStatus: 'Self-Employed',
            educationalBackground: 'College Level',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '5 and above',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'carlos.mendoza.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Carlos Mendoza'
        },
        {
            respondentNumber: '006',
            date: '2026-03-17',
            firstName: 'Patricia',
            middleName: 'Rosa',
            lastName: 'Del Rosario',
            suffix: '',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'MARCELO',
            purokZone: 'Zone 6',
            sex: 'Female',
            age: 18,
            birthday: '25/02/2008',
            emailAddress: 'patricia.rosario@email.com',
            contactNumber: '09123456794',
            civilStatus: 'Single',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Child Youth (15–17 yrs old)',
            workStatus: 'Student',
            educationalBackground: 'High School Level',
            registeredSKVoter: 'No',
            registeredNationalVoter: 'No',
            votingHistory: 'No',
            votingReason: 'There was no KK Assembly',
            attendedKKAssembly: 'No',
            facebookAccount: 'patricia.rosario.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Patricia Del Rosario'
        },
        {
            respondentNumber: '007',
            date: '2026-03-18',
            firstName: 'Miguel',
            middleName: 'Antonio',
            lastName: 'Fernandez',
            suffix: 'III',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'BIGAYANVILLA ROSA',
            purokZone: 'Zone 7',
            sex: 'Male',
            age: 24,
            birthday: '30/11/2001',
            emailAddress: 'miguel.fernandez@email.com',
            contactNumber: '09123456795',
            civilStatus: 'Single',
            youthClassification: 'Working Youth',
            youthAgeGroup: 'Young Adult (25–30 yrs old)',
            workStatus: 'Employed',
            educationalBackground: 'College Graduate',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '3–4 times',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'miguel.fernandez.fb',
            willingToJoinGroupChat: 'Yes',
            signature: 'Miguel Fernandez'
        },
        {
            respondentNumber: '008',
            date: '2026-03-19',
            firstName: 'Sofia',
            middleName: 'Isabel',
            lastName: 'Castillo',
            suffix: '',
            region: '4A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'PHASE3',
            purokZone: 'Zone 8',
            sex: 'Female',
            age: 17,
            birthday: '14/07/2008',
            emailAddress: 'sofia.castillo@email.com',
            contactNumber: '09123456796',
            civilStatus: 'Single',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Child Youth (15–17 yrs old)',
            workStatus: 'Student',
            educationalBackground: 'High School Level',
            registeredSKVoter: 'No',
            registeredNationalVoter: 'No',
            votingHistory: 'No',
            votingReason: 'Not interested to attend',
            attendedKKAssembly: 'No',
            facebookAccount: 'sofia.castillo.fb',
            willingToJoinGroupChat: 'No',
            signature: 'Sofia Castillo'
        }
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
            const education = k.educationalBackground || k.highestEducation || '';
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (education && education.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            const matchEducation = !currentEducation || education === currentEducation;
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

        updatePaginationInfo(startIndex + 1, endIndex, currentPage, totalPages);
        updatePaginationControls(currentPage, totalPages);
    }

    function updatePaginationInfo(start, end, page, totalPages) {
        const info = document.getElementById('kabataanPaginationInfo');
        if (info) {
            const total = kabataan.filter((k) => {
                const q = currentQuery;
                const full = fullNameFrom(k).toLowerCase();
                const education = k.educationalBackground || k.highestEducation || '';
                const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (education && education.toLowerCase().includes(q));
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
        const total = kabataan.filter((k) => {
            const q = currentQuery;
            const full = fullNameFrom(k).toLowerCase();
            const education = k.educationalBackground || k.highestEducation || '';
            const matchSearch = !q || full.includes(q) || (k.barangay && k.barangay.toLowerCase().includes(q)) || (education && education.toLowerCase().includes(q));
            const matchGender = !currentGender || k.sex === currentGender;
            const matchPurok = !currentPurok || k.barangay === currentPurok;
            return matchSearch && matchGender && matchPurok;
        }).length / recordsPerPage;

        const totalPages = Math.ceil(total);

        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            render();
        }
    }

    function populateViewRows(k) {
        if (!viewColumnLeft || !viewColumnRight) return;

        // Add section titles and fields to left column
        viewColumnLeft.innerHTML = `
            <div class="kabataan-view-section-title">General Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Respondent Number:</span><span class="kabataan-view-value">${k.respondentNumber || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Date:</span><span class="kabataan-view-value">${k.date || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Profile - Name of Respondent</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Last Name:</span><span class="kabataan-view-value">${k.lastName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">First Name:</span><span class="kabataan-view-value">${k.firstName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Middle Name:</span><span class="kabataan-view-value">${k.middleName || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Suffix:</span><span class="kabataan-view-value">${k.suffix || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Location</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Region:</span><span class="kabataan-view-value">${k.region || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Province:</span><span class="kabataan-view-value">${k.province || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">City / Municipality:</span><span class="kabataan-view-value">${k.city || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Barangay:</span><span class="kabataan-view-value">${k.barangay || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Purok / Zone:</span><span class="kabataan-view-value">${k.purokZone || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Personal Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Sex Assigned at Birth:</span><span class="kabataan-view-value">${k.sex || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Age:</span><span class="kabataan-view-value">${k.age || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Birthday:</span><span class="kabataan-view-value">${k.birthday || k.dob || '-'}</span></div>
        `;

        // Add section titles and fields to right column
        viewColumnRight.innerHTML = `
            <div class="kabataan-view-section-title">Contact Information</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Email Address:</span><span class="kabataan-view-value">${k.emailAddress || k.email || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Contact Number:</span><span class="kabataan-view-value">${k.contactNumber || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Demographic Characteristics</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Civil Status:</span><span class="kabataan-view-value">${k.civilStatus || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Youth Classification:</span><span class="kabataan-view-value">${k.youthClassification || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Youth Age Group:</span><span class="kabataan-view-value">${k.youthAgeGroup || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Work Status:</span><span class="kabataan-view-value">${k.workStatus || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Educational Background:</span><span class="kabataan-view-value">${k.educationalBackground || k.highestEducation || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Voter & Participation Info</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Registered SK Voter:</span><span class="kabataan-view-value">${k.registeredSKVoter || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Registered National Voter:</span><span class="kabataan-view-value">${k.registeredNationalVoter || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Voting History (Last SK Election):</span><span class="kabataan-view-value">${k.votingHistory || '-'}</span></div>
            ${k.votingHistory === 'Yes' ? `
                <div class="kabataan-view-row"><span class="kabataan-view-label">Voting Frequency:</span><span class="kabataan-view-value">${k.votingFrequency || '-'}</span></div>
            ` : k.votingReason ? `
                <div class="kabataan-view-row"><span class="kabataan-view-label">Reason:</span><span class="kabataan-view-value">${k.votingReason || '-'}</span></div>
            ` : ''}
            <div class="kabataan-view-row"><span class="kabataan-view-label">Attended KK Assembly:</span><span class="kabataan-view-value">${k.attendedKKAssembly || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Social / Community</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Facebook Account:</span><span class="kabataan-view-value">${k.facebookAccount || '-'}</span></div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Willing to join group chat:</span><span class="kabataan-view-value">${k.willingToJoinGroupChat || '-'}</span></div>
            
            <div class="kabataan-view-section-title">Signature</div>
            <div class="kabataan-view-row"><span class="kabataan-view-label">Name and Signature:</span><span class="kabataan-view-value">${k.signature || '-'}</span></div>
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
