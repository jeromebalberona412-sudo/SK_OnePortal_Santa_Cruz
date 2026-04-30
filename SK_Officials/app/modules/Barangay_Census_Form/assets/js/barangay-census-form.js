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
    let activeCensusId = null;

    if (!tbody) return;

    // Sample data loaded from JSON (storage/app/sample-data/barangay-census.json)
    let censusData = [];

    let currentSearchQuery = '';
    let currentPurokFilter = '';
    let currentOwnershipFilter = '';
    let currentCivilStatusFilter = '';
    let currentPage = 1;
    const recordsPerPage = 10;

    function updateStats() {
        const total = censusData.length;
        const owners = censusData.filter(c => c.presentAddress.ownership === 'Owner').length;
        const rentees = censusData.filter(c => c.presentAddress.ownership === 'Boarder/Rentee').length;
        
        document.getElementById('censusStatTotal').textContent = total;
        document.getElementById('censusStatOwners').textContent = owners;
        document.getElementById('censusStatRentees').textContent = rentees;
    }

    function sortCensusAlphabetically() {
        censusData.sort((a, b) => {
            const la = (a.lastName || '').toLowerCase();
            const lb = (b.lastName || '').toLowerCase();
            if (la < lb) return -1;
            if (la > lb) return 1;
            const fa = (a.firstName || '').toLowerCase();
            const fb = (b.firstName || '').toLowerCase();
            if (fa < fb) return -1;
            if (fa > fb) return 1;
            return 0;
        });
    }

    function formatCensusFullName(c) {
        const parts = [];
        if (c.lastName)   parts.push(c.lastName);
        if (c.firstName)  parts.push(c.firstName);
        if (c.middleName) parts.push(c.middleName);
        if (c.suffix)     parts.push(c.suffix);
        return parts.join(', ');
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
            td.colSpan = 31;
            td.textContent = 'No census records found. Upload an Excel file to add records.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationInfo(0, 0, 1);
            return;
        }

        paginatedData.forEach((c) => {
            const tr = document.createElement('tr');
            const fullName = formatCensusFullName(c);
            const pa  = c.presentAddress    || {};
            const pra = c.provincialAddress || {};
            const ea  = c.educationalAttainment || {};
        const v   = (val) => (val && val !== 'N/A') ? val : '—';
            tr.innerHTML = `
                <td>${v(c.formNo)}</td>
                <td>${v(c.controlNumber)}</td>
                <td>${v(c.cy)}</td>
                <td class="census-fullname-cell"><span class="census-fullname">${fullName}</span></td>
                <td>${v(pa.houseNo)}</td>
                <td>${v(pa.street)}</td>
                <td>${v(pa.barangay)}</td>
                <td>${v(pa.city)}</td>
                <td>${v(pa.province)}</td>
                <td>${v(pa.ownership)}</td>
                <td>${v(pra.houseNo)}</td>
                <td>${v(pra.street)}</td>
                <td>${v(pra.barangay)}</td>
                <td>${v(pra.city)}</td>
                <td>${v(pra.province)}</td>
                <td>${v(pra.lengthOfStay)}</td>
                <td>${v(c.sex)}</td>
                <td>${v(c.civilStatus)}</td>
                <td>${v(c.dateOfBirth)}</td>
                <td>${v(c.placeOfBirth)}</td>
                <td>${v(c.height)}</td>
                <td>${v(c.weight)}</td>
                <td>${v(c.contactNumber)}</td>
                <td>${v(c.email)}</td>
                <td>${v(c.religion)}</td>
                <td>${v(c.educationLevel)}</td>
                <td>${v(ea.elementary)}</td>
                <td>${v(ea.highSchool)}</td>
                <td>${v(ea.vocational)}</td>
                <td>${v(ea.college)}</td>
                <td>${v(c.signature)}</td>
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

        // Show Cancel + Preview buttons only after file selected
        const cancelBtn = document.getElementById('censusUploadCancelBtn');
        if (cancelBtn) cancelBtn.style.display = '';
        if (previewBtn) { previewBtn.style.display = ''; previewBtn.disabled = false; }
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            selectedFile = null;
            if (fileInput) fileInput.value = '';
            if (filePreview) filePreview.style.display = 'none';
            // Hide buttons again
            const cancelBtn = document.getElementById('censusUploadCancelBtn');
            if (cancelBtn) cancelBtn.style.display = 'none';
            if (previewBtn) { previewBtn.style.display = 'none'; previewBtn.disabled = true; }
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
        // Mock data for demonstration — each record mirrors the full census data structure
        return [
            {
                row: 1, valid: true,
                formNo: 'CMP-04-006', controlNumber: 'CN-2026-006', cy: '2026',
                lastName: 'Cruz', firstName: 'Pedro', middleName: 'Santos',
                presentAddress: { houseNo: '12', street: 'Purok 1', barangay: 'Calios', city: 'Santa Cruz', province: 'Laguna', ownership: 'Owner' },
                provincialAddress: { houseNo: '12', street: 'Purok 1', barangay: 'Calios', city: 'Santa Cruz', province: 'Laguna', lengthOfStay: '10 years' },
                sex: 'Male', civilStatus: 'Married', dateOfBirth: '03/12/1990', placeOfBirth: 'Santa Cruz, Laguna',
                height: '5\'7"', weight: '155 lbs', contactNumber: '09171112222', email: 'pedro.cruz@email.com',
                religion: 'Roman Catholic', educationLevel: 'College Graduate',
                educationalAttainment: { elementary: 'Calios Elementary', highSchool: 'Santa Cruz NHS', vocational: 'N/A', college: 'LSPU - BS Education' },
                employmentRecords: [{ duration: '2015-Present', company: 'DepEd', address: 'Santa Cruz, Laguna' }],
                houseOccupants: [{ name: 'Cruz, Ana, S.', position: 'Wife', age: 30, birthDate: '05/20/1994', civilStatus: 'Married', occupation: 'Nurse' }],
                characterReferences: [{ name: 'Santos, Maria', address: 'Purok 1, Calios', contactNumber: '09171234567' }],
                vehicles: [{ model: 'Honda Beat', color: 'Red', kind: 'Motorcycle', plateNumber: 'MNO 1234' }],
                signature: 'Pedro S. Cruz'
            },
            {
                row: 2, valid: true,
                formNo: 'CMP-04-007', controlNumber: 'CN-2026-007', cy: '2026',
                lastName: 'Lopez', firstName: 'Maria', middleName: 'Garcia',
                presentAddress: { houseNo: '34', street: 'Purok 2', barangay: 'Calios', city: 'Santa Cruz', province: 'Laguna', ownership: 'Boarder/Rentee' },
                provincialAddress: { houseNo: '56', street: 'Poblacion', barangay: 'Pagsanjan', city: 'Pagsanjan', province: 'Laguna', lengthOfStay: '3 years' },
                sex: 'Female', civilStatus: 'Single', dateOfBirth: '07/08/1998', placeOfBirth: 'Pagsanjan, Laguna',
                height: '5\'3"', weight: '115 lbs', contactNumber: '09281112222', email: 'maria.lopez@email.com',
                religion: 'Roman Catholic', educationLevel: 'College Level',
                educationalAttainment: { elementary: 'Pagsanjan Elementary', highSchool: 'Pagsanjan NHS', vocational: 'N/A', college: 'LSPU - BS Nursing (Ongoing)' },
                employmentRecords: [],
                houseOccupants: [],
                characterReferences: [{ name: 'Garcia, Rosa', address: 'Purok 2, Calios', contactNumber: '09181234567' }],
                vehicles: [],
                signature: 'Maria G. Lopez'
            },
            {
                row: 3, valid: false,
                formNo: '', controlNumber: 'CN-2026-008', cy: '2026',
                lastName: 'Ramos', firstName: 'Jose', middleName: 'Mendoza',
                presentAddress: { houseNo: '78', street: 'Purok 3', barangay: 'Calios', city: 'Santa Cruz', province: 'Laguna', ownership: 'Owner' },
                provincialAddress: { houseNo: '78', street: 'Purok 3', barangay: 'Calios', city: 'Santa Cruz', province: 'Laguna', lengthOfStay: '5 years' },
                sex: 'Male', civilStatus: 'Married', dateOfBirth: '11/22/1985', placeOfBirth: 'Santa Cruz, Laguna',
                height: '5\'9"', weight: '160 lbs', contactNumber: '09391112222', email: 'jose.ramos@email.com',
                religion: 'Iglesia ni Cristo', educationLevel: 'High School Graduate',
                educationalAttainment: { elementary: 'Calios Elementary', highSchool: 'Santa Cruz NHS', vocational: 'N/A', college: 'N/A' },
                employmentRecords: [{ duration: '2005-Present', company: 'Ramos Construction', address: 'Calios, Santa Cruz' }],
                houseOccupants: [],
                characterReferences: [],
                vehicles: [{ model: 'Isuzu Crosswind', color: 'Black', kind: 'SUV', plateNumber: 'PQR 5678' }],
                signature: 'Jose M. Ramos'
            }
        ];
    }

    function populatePreviewModal(data) {
        const previewBody = document.getElementById('censusPreviewBody');
        if (!previewBody) return;

        const v = (val) => (val !== undefined && val !== null && val !== '' && val !== 'N/A') ? val : '—';

        // Build one row per record; sub-arrays (occupants, refs, vehicles, employment) are joined with line breaks
        const rows = data.map(record => {
            const pa  = record.presentAddress    || {};
            const pra = record.provincialAddress || {};
            const ea  = record.educationalAttainment || {};

            const rowClass = record.valid ? '' : 'census-preview-row-invalid';

            const empText = (record.employmentRecords || []).length
                ? record.employmentRecords.map(e => `${v(e.company)} | ${v(e.duration)} | ${v(e.address)}`).join('\n')
                : '—';
            const empPosition = (record.employmentRecords || []).length
                ? record.employmentRecords.map(e => v(e.position || e.duration)).join('\n')
                : '—';
            const empDuration = (record.employmentRecords || []).length
                ? record.employmentRecords.map(e => v(e.duration)).join('\n')
                : '—';
            const empCompany = (record.employmentRecords || []).length
                ? record.employmentRecords.map(e => v(e.company)).join('\n')
                : '—';
            const empAddress = (record.employmentRecords || []).length
                ? record.employmentRecords.map(e => v(e.address)).join('\n')
                : '—';

            const occName = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.name)).join('\n') : '—';
            const occPos = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.position)).join('\n') : '—';
            const occBirth = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.birthDate)).join('\n') : '—';
            const occCS = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.civilStatus)).join('\n') : '—';
            const occAge = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.age)).join('\n') : '—';
            const occOcc = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.occupation)).join('\n') : '—';
            const occEmp = (record.houseOccupants || []).length
                ? record.houseOccupants.map(o => v(o.employerName || '—')).join('\n') : '—';

            const refName = (record.characterReferences || []).length
                ? record.characterReferences.map(r => v(r.name)).join('\n') : '—';
            const refAddr = (record.characterReferences || []).length
                ? record.characterReferences.map(r => v(r.address)).join('\n') : '—';
            const refContact = (record.characterReferences || []).length
                ? record.characterReferences.map(r => v(r.contactNumber)).join('\n') : '—';

            const vehModel = (record.vehicles || []).length
                ? record.vehicles.map(veh => v(veh.model)).join('\n') : '—';
            const vehColor = (record.vehicles || []).length
                ? record.vehicles.map(veh => v(veh.color)).join('\n') : '—';
            const vehKind = (record.vehicles || []).length
                ? record.vehicles.map(veh => v(veh.kind)).join('\n') : '—';
            const vehPlate = (record.vehicles || []).length
                ? record.vehicles.map(veh => v(veh.plateNumber)).join('\n') : '—';

            const cell = (text) => `<td class="census-preview-td">${text.replace(/\n/g, '<br>')}</td>`;

            return `<tr class="${rowClass}">
                ${cell(v(record.formNo))}
                ${cell(v(record.controlNumber))}
                ${cell(v(record.cy))}
                ${cell(v(record.lastName))}
                ${cell(v(record.firstName))}
                ${cell(v(record.middleName))}
                ${cell(v(pa.houseNo))}
                ${cell(v(pa.street))}
                ${cell(v(pa.barangay))}
                ${cell(v(pa.city))}
                ${cell(v(pa.province))}
                ${cell(v(pa.ownership))}
                ${cell(v(pra.houseNo))}
                ${cell(v(pra.street))}
                ${cell(v(pra.barangay))}
                ${cell(v(pra.city))}
                ${cell(v(pra.province))}
                ${cell(v(pra.lengthOfStay))}
                ${cell(v(record.sex))}
                ${cell(v(record.civilStatus))}
                ${cell(v(record.dateOfBirth))}
                ${cell(v(record.height))}
                ${cell(v(record.weight))}
                ${cell(v(record.placeOfBirth))}
                ${cell(v(record.contactNumber))}
                ${cell(v(record.email))}
                ${cell(v(record.religion))}
                ${cell(v(record.educationLevel))}
                ${cell(v(ea.elementary))}
                ${cell(v(ea.elementaryAddress || '—'))}
                ${cell(v(ea.highSchool))}
                ${cell(v(ea.highSchoolAddress || '—'))}
                ${cell(v(ea.vocational))}
                ${cell(v(ea.college))}
                ${cell(empCompany)}
                ${cell(empPosition)}
                ${cell(empDuration)}
                ${cell(empAddress)}
                ${cell(occName)}
                ${cell(occPos)}
                ${cell(occBirth)}
                ${cell(occCS)}
                ${cell(occAge)}
                ${cell(occOcc)}
                ${cell(occEmp)}
                ${cell(refName)}
                ${cell(refAddr)}
                ${cell(refContact)}
                ${cell(vehModel)}
                ${cell(vehColor)}
                ${cell(vehKind)}
                ${cell(vehPlate)}
                ${cell(v(record.signature))}
            </tr>`;
        }).join('');

        const colGroups = [
            { label: 'Form Info',            cols: ['Form No.', 'Control Number', 'CY'] },
            { label: 'Name',                 cols: ['Last Name', 'First Name', 'Middle Name'] },
            { label: 'Present Address',      cols: ['House/Block/Lot No.', 'Street/Purok/Sitio/Subdivision', 'Barangay', 'City/Municipality', 'Province', 'Residency Type'] },
            { label: 'Provincial Address',   cols: ['House/Block/Lot No.', 'Street/Purok/Sitio/Subdivision', 'Barangay', 'City/Municipality', 'Province', 'Length of Stay'] },
            { label: 'Personal Info',        cols: ['Sex', 'Civil Status', 'Date of Birth', 'Height', 'Weight', 'Place of Birth', 'Contact Number', 'Email Address', 'Religion'] },
            { label: 'Education',            cols: ['Level of Education', 'Elementary School Name', 'Elementary School Address', 'High School Name', 'High School Address', 'Vocational Course', 'College Course'] },
            { label: 'Employment Record',    cols: ['Company/Employer Name', 'Position', 'Duration', 'Work Address'] },
            { label: 'Household Members',    cols: ['Member Name', 'Relationship/Position', 'Birth Date', 'Civil Status', 'Age', 'Occupation', 'Employer Name'] },
            { label: 'Character References', cols: ['Reference Name', 'Address', 'Contact Number'] },
            { label: 'Vehicles',             cols: ['Model', 'Color', 'Kind', 'Plate Number'] },
            { label: 'Declaration',          cols: ['Signature (Head of Family / Representative)'] },
        ];

        const groupHeaders = colGroups.map(g =>
            `<th class="census-preview-group-th" colspan="${g.cols.length}">${g.label}</th>`
        ).join('');

        const colHeaders = colGroups.flatMap(g => g.cols).map(col =>
            `<th class="census-preview-th">${col}</th>`
        ).join('');

        previewBody.innerHTML = `
            <p class="census-preview-instruction">Review the data before uploading</p>
            <div class="census-preview-table-wrap">
                <table class="census-preview-table">
                    <thead>
                        <tr class="census-preview-group-row">${groupHeaders}</tr>
                        <tr>${colHeaders}</tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>`;
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

    function populateViewModal(census) {
        // Placeholder for view modal population
        // This function would populate a view modal with census details
        console.log('View census record:', census);
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

    // Load sample data from JSON then render
    fetch('/sample-data/barangay-census.json')
        .then(r => {
            if (!r.ok) {
                throw new Error('Failed to load census data');
            }
            return r.json();
        })
        .then(data => {
            console.log('Census data loaded:', data.length, 'records');
            censusData.push(...data);
            sortCensusAlphabetically();
            updateStats();
            renderTable();
        })
        .catch((error) => {
            console.error('Error loading census data:', error);
            // JSON unavailable — render empty state
            updateStats();
            renderTable();
        });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initializeCensusModule();
});
