// Module-level toast function
function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');
    const icon = type === 'error' ? '✕' : '✓';
    toast.innerHTML = '<span class="app-toast-icon">' + icon + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function initializeCensusModule() {
    const tbody = document.getElementById('censusTableBody');
    const searchInput = document.getElementById('censusSearch');
    const purokFilter = document.getElementById('censusPurokFilter');
    const ownershipFilter = document.getElementById('censusOwnershipFilter');
    const civilStatusFilter = document.getElementById('censusCivilStatusFilter');
    const uploadBtn = document.getElementById('censusUploadBtn');
    const uploadModal = document.getElementById('censusUploadModal');
    const uploadArea = document.getElementById('censusUploadArea');
    const browseBtn = document.getElementById('censusBrowseBtn');
    const previewBtn = document.getElementById('censusPreviewBtn');
    const fileInput = document.getElementById('censusFileInput');
    const filePreview = document.getElementById('censusFilePreview');
    const fileName = document.getElementById('censusFileName');
    const fileSize = document.getElementById('censusFileSize');
    const removeFileBtn = document.getElementById('censusRemoveFileBtn');
    const previewModal = document.getElementById('censusPreviewModal');
    const uploadConfirmBtn = document.getElementById('censusUploadConfirmBtn');
    const viewModal = document.getElementById('censusViewModal');

    let selectedFile = null;
    let previewData = [];

    if (!tbody) return;

    // Sample census data for Calios, Santa Cruz, Laguna
    const censusData = [
        {
            id: 1,
            formNo: 'CMP-04-001',
            controlNumber: 'CN-2026-001',
            cy: '2026',
            lastName: 'Santos',
            firstName: 'Maria',
            middleName: 'Garcia',
            presentAddress: {
                houseNo: '123',
                street: 'Purok 1',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                ownership: 'Owner'
            },
            provincialAddress: {
                houseNo: '123',
                street: 'Purok 1',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                lengthOfStay: '15 years'
            },
            sex: 'Female',
            civilStatus: 'Married',
            dateOfBirth: '05/15/1985',
            placeOfBirth: 'Santa Cruz, Laguna',
            height: '5\'4"',
            weight: '120 lbs',
            contactNumber: '09171234567',
            email: 'maria.santos@email.com',
            religion: 'Roman Catholic',
            educationLevel: 'College Graduate',
            educationalAttainment: {
                elementary: 'Calios Elementary School, Calios, Santa Cruz, Laguna',
                highSchool: 'Santa Cruz National High School, Santa Cruz, Laguna',
                vocational: 'N/A',
                college: 'Laguna State Polytechnic University - Bachelor of Science in Business Administration'
            },
            employmentRecords: [
                { duration: '2010-2015', company: 'ABC Corporation', address: 'Manila, Metro Manila' },
                { duration: '2015-Present', company: 'XYZ Company', address: 'Santa Cruz, Laguna' }
            ],
            houseOccupants: [
                { name: 'Santos, Juan, M., Jr.', position: 'Husband', age: 42, birthDate: '03/20/1984', civilStatus: 'Married', occupation: 'Engineer / ABC Engineering' },
                { name: 'Santos, Ana, M.', position: 'Daughter', age: 15, birthDate: '08/10/2011', civilStatus: 'Single', occupation: 'Student / Santa Cruz NHS' },
                { name: 'Santos, Pedro, M.', position: 'Son', age: 12, birthDate: '11/25/2014', civilStatus: 'Single', occupation: 'Student / Calios Elementary' }
            ],
            characterReferences: [
                { name: 'Dela Cruz, Rosa', address: 'Purok 2, Calios, Santa Cruz, Laguna', contactNumber: '09181234567' },
                { name: 'Reyes, Carlos', address: 'Purok 1, Calios, Santa Cruz, Laguna', contactNumber: '09191234567' }
            ],
            vehicles: [
                { model: 'Toyota Vios 2020', color: 'White', kind: 'Car', plateNumber: 'ABC 1234' }
            ],
            signature: 'Maria G. Santos'
        },
        {
            id: 2,
            formNo: 'CMP-04-002',
            controlNumber: 'CN-2026-002',
            cy: '2026',
            lastName: 'Reyes',
            firstName: 'Juan',
            middleName: 'Cruz',
            presentAddress: {
                houseNo: '456',
                street: 'Purok 2',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                ownership: 'Boarder/Rentee'
            },
            provincialAddress: {
                houseNo: '789',
                street: 'Barangay Proper',
                barangay: 'Pagsanjan',
                city: 'Pagsanjan',
                province: 'Laguna',
                lengthOfStay: '2 years'
            },
            sex: 'Male',
            civilStatus: 'Single',
            dateOfBirth: '08/20/1995',
            placeOfBirth: 'Pagsanjan, Laguna',
            height: '5\'8"',
            weight: '150 lbs',
            contactNumber: '09281234567',
            email: 'juan.reyes@email.com',
            religion: 'Iglesia ni Cristo',
            educationLevel: 'College Level',
            educationalAttainment: {
                elementary: 'Pagsanjan Elementary School, Pagsanjan, Laguna',
                highSchool: 'Pagsanjan National High School, Pagsanjan, Laguna',
                vocational: 'N/A',
                college: 'Laguna State Polytechnic University - BS Information Technology (Ongoing)'
            },
            employmentRecords: [
                { duration: '2020-Present', company: 'Tech Solutions Inc.', address: 'Santa Cruz, Laguna' }
            ],
            houseOccupants: [],
            characterReferences: [
                { name: 'Lopez, Ana', address: 'Purok 3, Calios, Santa Cruz, Laguna', contactNumber: '09201234567' }
            ],
            vehicles: [
                { model: 'Honda Click 150i', color: 'Black', kind: 'Motorcycle', plateNumber: 'XYZ 789' }
            ],
            signature: 'Juan C. Reyes'
        },
        {
            id: 3,
            formNo: 'CMP-04-003',
            controlNumber: 'CN-2026-003',
            cy: '2026',
            lastName: 'Dela Cruz',
            firstName: 'Ana',
            middleName: 'Lopez',
            presentAddress: {
                houseNo: '789',
                street: 'Purok 3',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                ownership: 'Owner'
            },
            provincialAddress: {
                houseNo: '789',
                street: 'Purok 3',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                lengthOfStay: '30 years'
            },
            sex: 'Female',
            civilStatus: 'Widow/er',
            dateOfBirth: '12/10/1970',
            placeOfBirth: 'Santa Cruz, Laguna',
            height: '5\'2"',
            weight: '110 lbs',
            contactNumber: '09391234567',
            email: 'ana.delacruz@email.com',
            religion: 'Born Again Christian',
            educationLevel: 'High School Graduate',
            educationalAttainment: {
                elementary: 'Calios Elementary School, Calios, Santa Cruz, Laguna',
                highSchool: 'Santa Cruz National High School, Santa Cruz, Laguna',
                vocational: 'N/A',
                college: 'N/A'
            },
            employmentRecords: [
                { duration: '1990-2010', company: 'Local Sari-Sari Store', address: 'Calios, Santa Cruz, Laguna' }
            ],
            houseOccupants: [
                { name: 'Dela Cruz, Pedro, L.', position: 'Son', age: 28, birthDate: '05/15/1998', civilStatus: 'Single', occupation: 'Driver / Private Company' }
            ],
            characterReferences: [
                { name: 'Santos, Maria', address: 'Purok 1, Calios, Santa Cruz, Laguna', contactNumber: '09171234567' },
                { name: 'Bautista, Jose', address: 'Purok 4, Calios, Santa Cruz, Laguna', contactNumber: '09211234567' }
            ],
            vehicles: [],
            signature: 'Ana L. Dela Cruz'
        },
        {
            id: 4,
            formNo: 'CMP-04-004',
            controlNumber: 'CN-2026-004',
            cy: '2026',
            lastName: 'Bautista',
            firstName: 'Jose',
            middleName: 'Mendoza',
            presentAddress: {
                houseNo: '321',
                street: 'Purok 4',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                ownership: 'Owner'
            },
            provincialAddress: {
                houseNo: '321',
                street: 'Purok 4',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                lengthOfStay: '20 years'
            },
            sex: 'Male',
            civilStatus: 'Married',
            dateOfBirth: '03/25/1980',
            placeOfBirth: 'Santa Cruz, Laguna',
            height: '5\'10"',
            weight: '165 lbs',
            contactNumber: '09451234567',
            email: 'jose.bautista@email.com',
            religion: 'Roman Catholic',
            educationLevel: 'Vocational Graduate',
            educationalAttainment: {
                elementary: 'Calios Elementary School, Calios, Santa Cruz, Laguna',
                highSchool: 'Santa Cruz National High School, Santa Cruz, Laguna',
                vocational: 'TESDA - Automotive Servicing NC II',
                college: 'N/A'
            },
            employmentRecords: [
                { duration: '2005-Present', company: 'Bautista Auto Repair Shop', address: 'Calios, Santa Cruz, Laguna' }
            ],
            houseOccupants: [
                { name: 'Bautista, Linda, R.', position: 'Wife', age: 42, birthDate: '07/18/1984', civilStatus: 'Married', occupation: 'Housewife' },
                { name: 'Bautista, Mark, L.', position: 'Son', age: 18, birthDate: '09/12/2008', civilStatus: 'Single', occupation: 'Student / LSPU' },
                { name: 'Bautista, Lisa, L.', position: 'Daughter', age: 16, birthDate: '11/30/2010', civilStatus: 'Single', occupation: 'Student / Santa Cruz NHS' }
            ],
            characterReferences: [
                { name: 'Garcia, Roberto', address: 'Purok 5, Calios, Santa Cruz, Laguna', contactNumber: '09221234567' }
            ],
            vehicles: [
                { model: 'Mitsubishi L300', color: 'Silver', kind: 'Van', plateNumber: 'DEF 5678' },
                { model: 'Yamaha Mio', color: 'Blue', kind: 'Motorcycle', plateNumber: 'GHI 9012' }
            ],
            signature: 'Jose M. Bautista'
        },
        {
            id: 5,
            formNo: 'CMP-04-005',
            controlNumber: 'CN-2026-005',
            cy: '2026',
            lastName: 'Garcia',
            firstName: 'Roberto',
            middleName: 'Ramos',
            presentAddress: {
                houseNo: '654',
                street: 'Purok 5',
                barangay: 'Calios',
                city: 'Santa Cruz',
                province: 'Laguna',
                ownership: 'Boarder/Rentee'
            },
            provincialAddress: {
                houseNo: '111',
                street: 'Poblacion',
                barangay: 'Lumban',
                city: 'Lumban',
                province: 'Laguna',
                lengthOfStay: '1 year'
            },
            sex: 'Male',
            civilStatus: 'Separated',
            dateOfBirth: '06/08/1988',
            placeOfBirth: 'Lumban, Laguna',
            height: '5\'6"',
            weight: '145 lbs',
            contactNumber: '09561234567',
            email: 'roberto.garcia@email.com',
            religion: 'Roman Catholic',
            educationLevel: 'College Graduate',
            educationalAttainment: {
                elementary: 'Lumban Elementary School, Lumban, Laguna',
                highSchool: 'Lumban National High School, Lumban, Laguna',
                vocational: 'N/A',
                college: 'University of the Philippines Los Baños - BS Agriculture'
            },
            employmentRecords: [
                { duration: '2012-2018', company: 'Department of Agriculture', address: 'Los Baños, Laguna' },
                { duration: '2018-Present', company: 'Private Agricultural Consultant', address: 'Santa Cruz, Laguna' }
            ],
            houseOccupants: [],
            characterReferences: [
                { name: 'Bautista, Jose', address: 'Purok 4, Calios, Santa Cruz, Laguna', contactNumber: '09451234567' },
                { name: 'Mendoza, Clara', address: 'Purok 5, Calios, Santa Cruz, Laguna', contactNumber: '09231234567' }
            ],
            vehicles: [
                { model: 'Suzuki Raider 150', color: 'Red', kind: 'Motorcycle', plateNumber: 'JKL 3456' }
            ],
            signature: 'Roberto R. Garcia'
        }
    ];

    let currentSearchQuery = '';
    let currentPurokFilter = '';
    let currentOwnershipFilter = '';
    let currentCivilStatusFilter = '';
    let currentPage = 1;
    const recordsPerPage = 10;
    let activeCensusId = null;

    function updateStats() {
        const total = censusData.length;
        const owners = censusData.filter(c => c.presentAddress.ownership === 'Owner').length;
        const rentees = censusData.filter(c => c.presentAddress.ownership === 'Boarder/Rentee').length;
        
        document.getElementById('censusStatTotal').textContent = total;
        document.getElementById('censusStatOwners').textContent = owners;
        document.getElementById('censusStatRentees').textContent = rentees;
    }

    function renderTable() {
        tbody.innerHTML = '';
        const filtered = censusData.filter((c) => {
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = `${c.lastName} ${c.firstName} ${c.middleName}`.toLowerCase();
                const match = fullName.includes(q) || 
                             c.formNo.toLowerCase().includes(q) || 
                             c.controlNumber.toLowerCase().includes(q);
                if (!match) return false;
            }
            if (currentPurokFilter && c.presentAddress.street !== currentPurokFilter) return false;
            if (currentOwnershipFilter && c.presentAddress.ownership !== currentOwnershipFilter) return false;
            if (currentCivilStatusFilter && c.civilStatus !== currentCivilStatusFilter) return false;
            return true;
        });

        const totalPages = Math.ceil(filtered.length / recordsPerPage);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, filtered.length);
        const paginatedData = filtered.slice(startIndex, endIndex);

        if (paginatedData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 8;
            td.textContent = 'No census records found. Upload an Excel file to add records.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationInfo(0, 0, 1);
            return;
        }

        paginatedData.forEach((c) => {
            const tr = document.createElement('tr');
            const fullName = `${c.lastName}, ${c.firstName} ${c.middleName}`;
            tr.innerHTML = `
                <td>${c.formNo}</td>
                <td>${c.controlNumber}</td>
                <td>${fullName}</td>
                <td>${c.presentAddress.street}</td>
                <td>${c.presentAddress.ownership}</td>
                <td>${c.civilStatus}</td>
                <td>${c.dateOfBirth}</td>
                <td class="col-actions">
                    <button type="button" class="census-btn-view" data-action="view" data-id="${c.id}">View</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updatePaginationInfo(startIndex + 1, endIndex, currentPage, totalPages);
        updatePaginationControls(currentPage, totalPages);
    }

    function updatePaginationInfo(start, end, page, totalPages) {
        const info = document.getElementById('censusPaginationInfo');
        if (info) {
            const total = censusData.filter((c) => {
                if (currentSearchQuery) {
                    const q = currentSearchQuery.toLowerCase();
                    const fullName = `${c.lastName} ${c.firstName} ${c.middleName}`.toLowerCase();
                    const match = fullName.includes(q) || c.formNo.toLowerCase().includes(q);
                    if (!match) return false;
                }
                if (currentPurokFilter && c.presentAddress.street !== currentPurokFilter) return false;
                if (currentOwnershipFilter && c.presentAddress.ownership !== currentOwnershipFilter) return false;
                if (currentCivilStatusFilter && c.civilStatus !== currentCivilStatusFilter) return false;
                return true;
            }).length;
            info.textContent = total === 0 ? 'No records found' : `Showing ${start}-${end} of ${total} records`;
        }
    }

    function updatePaginationControls(page, totalPages) {
        const prevBtn = document.getElementById('censusPrevBtn');
        const nextBtn = document.getElementById('censusNextBtn');
        const pageNumbers = document.getElementById('censusPageNumbers');
        
        if (prevBtn) prevBtn.disabled = page === 1;
        if (nextBtn) nextBtn.disabled = page === totalPages || totalPages === 0;
        
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `page-number ${i === page ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToPage(i);
                pageNumbers.appendChild(pageBtn);
            }
        }
    }

    function goToPage(page) {
        currentPage = page;
        renderTable();
    }

    function populateViewModal(census) {
        const setVal = (id, val) => { 
            const el = document.getElementById(id); 
            if (el) el.textContent = val ?? '—'; 
        };

        // Header
        setVal('viewFormNo', census.formNo);
        setVal('viewControlNumber', census.controlNumber);
        setVal('viewCY', census.cy);

        // Name
        setVal('viewLastName', census.lastName);
        setVal('viewFirstName', census.firstName);
        setVal('viewMiddleName', census.middleName);

        // Present Address
        setVal('viewHouseNo', census.presentAddress.houseNo);
        setVal('viewStreet', census.presentAddress.street);
        setVal('viewBarangay', census.presentAddress.barangay);
        setVal('viewCity', census.presentAddress.city);
        setVal('viewProvince', census.presentAddress.province);
        setVal('viewOwnership', census.presentAddress.ownership);

        // Provincial Address
        setVal('viewProvHouseNo', census.provincialAddress.houseNo);
        setVal('viewProvStreet', census.provincialAddress.street);
        setVal('viewProvBarangay', census.provincialAddress.barangay);
        setVal('viewProvCity', census.provincialAddress.city);
        setVal('viewProvProvince', census.provincialAddress.province);
        setVal('viewLengthOfStay', census.provincialAddress.lengthOfStay);

        // Personal Details
        setVal('viewSex', census.sex);
        setVal('viewCivilStatus', census.civilStatus);
        setVal('viewDateOfBirth', census.dateOfBirth);
        setVal('viewPlaceOfBirth', census.placeOfBirth);
        setVal('viewHeight', census.height);
        setVal('viewWeight', census.weight);
        setVal('viewContactNumber', census.contactNumber);
        setVal('viewEmail', census.email);
        setVal('viewReligion', census.religion);
        setVal('viewEducationLevel', census.educationLevel);

        // Educational Attainment
        setVal('viewElementary', census.educationalAttainment.elementary);
        setVal('viewHighSchool', census.educationalAttainment.highSchool);
        setVal('viewVocational', census.educationalAttainment.vocational);
        setVal('viewCollege', census.educationalAttainment.college);

        // Employment Records
        const employmentEl = document.getElementById('viewEmploymentRecords');
        if (employmentEl) {
            employmentEl.innerHTML = census.employmentRecords.length > 0 
                ? census.employmentRecords.map(e => `
                    <div class="census-list-item">
                        <strong>${e.duration}</strong> - ${e.company}, ${e.address}
                    </div>
                `).join('')
                : '<div class="census-list-item">No employment records</div>';
        }

        // House Occupants
        const occupantsEl = document.getElementById('viewHouseOccupants');
        if (occupantsEl) {
            occupantsEl.innerHTML = census.houseOccupants.length > 0
                ? census.houseOccupants.map(o => `
                    <div class="census-list-item">
                        <strong>${o.name}</strong> (${o.position}) - Age: ${o.age}, DOB: ${o.birthDate}, ${o.civilStatus}, ${o.occupation}
                    </div>
                `).join('')
                : '<div class="census-list-item">No other house occupants</div>';
        }

        // Character References
        const referencesEl = document.getElementById('viewCharacterReferences');
        if (referencesEl) {
            referencesEl.innerHTML = census.characterReferences.length > 0
                ? census.characterReferences.map(r => `
                    <div class="census-list-item">
                        <strong>${r.name}</strong> - ${r.address}, ${r.contactNumber}
                    </div>
                `).join('')
                : '<div class="census-list-item">No character references</div>';
        }

        // Vehicles
        const vehiclesEl = document.getElementById('viewVehicles');
        if (vehiclesEl) {
            vehiclesEl.innerHTML = census.vehicles.length > 0
                ? census.vehicles.map(v => `
                    <div class="census-list-item">
                        <strong>${v.model}</strong> (${v.color} ${v.kind}) - Plate: ${v.plateNumber}
                    </div>
                `).join('')
                : '<div class="census-list-item">No vehicles</div>';
        }

        // Signature
        setVal('viewSignature', census.signature);
    }

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }

    function resetModalMaximize(backdropEl) {
        if (!backdropEl) return;
        backdropEl.classList.remove('modal-maximized');
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    function wireModalToggle(backdropEl) {
        if (!backdropEl) return;
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMaximized = backdropEl.classList.toggle('modal-maximized');
            toggleBtn.textContent = isMaximized ? '⧉' : '□';
        });
    }

    // Event Listeners
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentSearchQuery = searchInput.value.trim();
            currentPage = 1;
            renderTable();
        });
    }

    if (purokFilter) {
        purokFilter.addEventListener('change', () => {
            currentPurokFilter = purokFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    if (ownershipFilter) {
        ownershipFilter.addEventListener('change', () => {
            currentOwnershipFilter = ownershipFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    if (civilStatusFilter) {
        civilStatusFilter.addEventListener('change', () => {
            currentCivilStatusFilter = civilStatusFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    const prevBtn = document.getElementById('censusPrevBtn');
    const nextBtn = document.getElementById('censusNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    // Upload Census
    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => {
            selectedFile = null;
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
            if (previewBtn) previewBtn.disabled = true;
            openModal(uploadModal);
        });
    }

    // File upload area interactions
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#6366f1';
            uploadArea.style.background = '#eef2ff';
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#d1d5db';
            uploadArea.style.background = '#f9fafb';
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#d1d5db';
            uploadArea.style.background = '#f9fafb';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
    }

    if (browseBtn && fileInput) {
        browseBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                handleFileSelect(file);
            }
        });
    }

    function handleFileSelect(file) {
        const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
            showToast('Please upload a valid Excel file (.xlsx or .xls)', 'error');
            return;
        }

        selectedFile = file;
        
        // Show file preview
        if (fileName) fileName.textContent = file.name;
        if (fileSize) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            fileSize.textContent = `${sizeInMB} MB`;
        }
        if (filePreview) filePreview.style.display = 'block';
        if (previewBtn) previewBtn.disabled = false;
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            selectedFile = null;
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
            if (previewBtn) previewBtn.disabled = true;
        });
    }

    if (previewBtn) {
        previewBtn.addEventListener('click', () => {
            if (!selectedFile) {
                showToast('Please select a file first', 'error');
                return;
            }
            
            // Simulate reading Excel file and generating preview data
            // In real implementation, you would use a library like SheetJS to parse Excel
            previewData = generateMockPreviewData();
            
            populatePreviewModal(previewData);
            closeModal(uploadModal);
            resetModalMaximize(previewModal);
            openModal(previewModal);
        });
    }

    function generateMockPreviewData() {
        // Mock data for demonstration
        return [
            { row: 1, formNo: 'CMP-04-006', controlNumber: 'CN-2026-006', lastName: 'Cruz', firstName: 'Pedro', middleName: 'Santos', purok: 'Purok 1', ownership: 'Owner', civilStatus: 'Married', valid: true },
            { row: 2, formNo: 'CMP-04-007', controlNumber: 'CN-2026-007', lastName: 'Lopez', firstName: 'Maria', middleName: 'Garcia', purok: 'Purok 2', ownership: 'Boarder/Rentee', civilStatus: 'Single', valid: true },
            { row: 3, formNo: '', controlNumber: 'CN-2026-008', lastName: 'Ramos', firstName: 'Jose', middleName: 'Mendoza', purok: 'Purok 3', ownership: 'Owner', civilStatus: 'Married', valid: false },
            { row: 4, formNo: 'CMP-04-009', controlNumber: 'CN-2026-009', lastName: 'Torres', firstName: 'Ana', middleName: 'Reyes', purok: 'Purok 4', ownership: 'Owner', civilStatus: 'Widow/er', valid: true },
            { row: 5, formNo: 'CMP-04-010', controlNumber: '', lastName: 'Fernandez', firstName: 'Carlos', middleName: 'Diaz', purok: 'Purok 5', ownership: 'Boarder/Rentee', civilStatus: 'Single', valid: false },
        ];
    }

    function populatePreviewModal(data) {
        const totalRecords = data.length;
        const validRecords = data.filter(d => d.valid).length;
        const invalidRecords = totalRecords - validRecords;
        
        document.getElementById('previewTotalRecords').textContent = totalRecords;
        document.getElementById('previewValidRecords').textContent = validRecords;
        document.getElementById('previewInvalidRecords').textContent = invalidRecords;
        
        const tbody = document.getElementById('censusPreviewTableBody');
        tbody.innerHTML = '';
        
        data.forEach(record => {
            const tr = document.createElement('tr');
            tr.style.background = record.valid ? 'transparent' : '#fef2f2';
            const fullName = `${record.lastName}, ${record.firstName} ${record.middleName}`;
            tr.innerHTML = `
                <td>${record.row}</td>
                <td>${record.formNo || '<span style="color: #dc2626;">Missing</span>'}</td>
                <td>${record.controlNumber || '<span style="color: #dc2626;">Missing</span>'}</td>
                <td>${fullName}</td>
                <td>${record.purok}</td>
                <td>${record.ownership}</td>
                <td>${record.civilStatus}</td>
                <td>
                    ${record.valid 
                        ? '<span style="color: #16a34a; font-weight: 600;">✓ Valid</span>' 
                        : '<span style="color: #dc2626; font-weight: 600;">✗ Invalid</span>'}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    if (uploadConfirmBtn) {
        uploadConfirmBtn.addEventListener('click', () => {
            if (previewData.length === 0) {
                showToast('No data to upload', 'error');
                return;
            }
            
            const validRecords = previewData.filter(d => d.valid);
            if (validRecords.length === 0) {
                showToast('No valid records to upload', 'error');
                return;
            }

            closeModal(previewModal);
            showToast(`Successfully uploaded ${validRecords.length} census record(s)`, 'success');
            renderTable();
            updateStats();
        });
    }

    // View Census
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action="view"]');
            if (!btn) return;
            const id = parseInt(btn.getAttribute('data-id') || '', 10);
            if (Number.isNaN(id)) return;
            const census = censusData.find((c) => c.id === id);
            if (!census) return;
            
            activeCensusId = id;
            populateViewModal(census);
            resetModalMaximize(viewModal);
            openModal(viewModal);
        });
    }

    // Close modals
    [uploadModal, previewModal, viewModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                resetModalMaximize(modal);
                closeModal(modal);
            }
        });
    });

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);
    wireModalToggle(previewModal);

    // Close modals
    [uploadModal, previewModal, viewModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                resetModalMaximize(modal);
                closeModal(modal);
            }
        });
    });

    // Initial render
    updateStats();
    renderTable();
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initializeCensusModule();
});
