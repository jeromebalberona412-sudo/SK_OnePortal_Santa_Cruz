/* ============================================================
   Previous Kabataan Module — JS
   ============================================================ */

/* ── Unique SVG Signature Paths per respondent ── */
const SIGNATURES = [
    // 1 - flowing cursive
    'M8 35 C15 12, 28 10, 38 28 C44 38, 50 42, 60 28 C68 18, 76 14, 86 30 C92 40, 98 44, 110 28 C118 16, 126 12, 136 28 C142 38, 148 42, 160 26 C168 16, 176 12, 186 30 C192 40, 200 44, 212 30',
    // 2 - sharp angular
    'M10 40 L25 15 L40 38 L55 18 L70 40 L85 20 L100 38 L115 16 L130 40 L145 18 L160 38 L175 20 L190 40 L205 22 L215 38',
    // 3 - loopy
    'M10 30 C18 10, 30 8, 38 25 C42 35, 46 40, 55 28 C62 18, 70 12, 80 28 C88 40, 94 44, 106 25 C114 12, 124 8, 134 28 C140 40, 146 44, 158 25 C166 12, 176 8, 186 28 C192 40, 200 44, 212 28',
    // 4 - wide sweeping
    'M5 38 Q30 5, 55 35 Q80 5, 105 35 Q130 5, 155 35 Q180 5, 215 35',
    // 5 - tight zigzag
    'M8 38 C14 20, 22 18, 30 32 C36 42, 42 44, 50 30 C56 18, 64 16, 72 32 C78 42, 84 44, 92 28 C98 16, 106 14, 114 30 C120 42, 126 44, 134 28 C140 16, 148 14, 156 30 C162 42, 168 44, 176 28 C182 16, 190 14, 200 30 C206 40, 212 42, 218 32',
    // 6 - bold strokes
    'M10 42 C20 8, 35 6, 45 30 C50 42, 56 46, 68 28 C76 14, 86 10, 96 30 C102 44, 108 48, 122 26 C130 12, 140 8, 150 28 C156 42, 162 46, 174 26 C182 12, 192 8, 202 28 C208 40, 214 44, 218 34',
];

function getSignaturePath(id) {
    return SIGNATURES[(id - 1) % SIGNATURES.length];
}

/* ── Inline SVG signature for table/preview cells ── */
function makeSignatureSvg(index) {
    const path = SIGNATURES[index % SIGNATURES.length];
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 50" width="110" height="25" style="display:block;margin:0 auto;" aria-label="Signature">
        <path d="${path}" fill="none" stroke="#1a1a1a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`;
}
const PREV_KAB_DATA = [
    {
        id: 1, year: 2023, respondentNo: 'PK-2023-001',
        lastName: 'Dela Cruz', firstName: 'Juan', middleName: 'Santos', suffix: '',
        age: 20, barangay: 'Calios', purokZone: 'Bayside',
        registeredVoter: 'Yes',
        sex: 'Male', birthday: '15/06/2003', email: 'juan.delacruz@email.com', contact: '09171234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'College Level', skVoter: 'Yes', natVoter: 'Yes',
        votingHistory: 'Yes', votingFrequency: '1-2 Times',
        kkAssembly: 'Yes', votingReason: '',
        facebook: 'juan.delacruz', groupChat: 'Yes', date: '2023-01-15',
    },
    {
        id: 2, year: 2023, respondentNo: 'PK-2023-002',
        lastName: 'Reyes', firstName: 'Maria', middleName: 'Lopez', suffix: '',
        age: 17, barangay: 'Calios', purokZone: 'Villa Gracia',
        registeredVoter: 'No',
        sex: 'Female', birthday: '22/09/2006', email: 'maria.reyes@email.com', contact: '09281234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Child Youth (15-17 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'High School Level', skVoter: 'No', natVoter: 'No',
        votingHistory: 'No', votingFrequency: '',
        kkAssembly: 'No', votingReason: 'Not Interested to Attend',
        facebook: 'maria.reyes', groupChat: 'No', date: '2023-01-15',
    },
    {
        id: 3, year: 2024, respondentNo: 'PK-2024-001',
        lastName: 'Garcia', firstName: 'Pedro', middleName: 'Bautista', suffix: 'Jr.',
        age: 24, barangay: 'Calios', purokZone: 'Imelda',
        registeredVoter: 'Yes',
        sex: 'Male', birthday: '03/03/2000', email: 'pedro.garcia@email.com', contact: '09391234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'Working Youth', workStatus: 'Employed',
        education: 'College Grad', skVoter: 'Yes', natVoter: 'Yes',
        votingHistory: 'Yes', votingFrequency: '3-4 Times',
        kkAssembly: 'Yes', votingReason: '',
        facebook: 'pedro.garcia', groupChat: 'Yes', date: '2024-02-10',
    },
    {
        id: 4, year: 2024, respondentNo: 'PK-2024-002',
        lastName: 'Santos', firstName: 'Ana', middleName: 'Cruz', suffix: '',
        age: 19, barangay: 'Calios', purokZone: 'Lupang Pangako',
        registeredVoter: 'Yes',
        sex: 'Female', birthday: '11/11/2004', email: 'ana.santos@email.com', contact: '09501234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'College Level', skVoter: 'Yes', natVoter: 'No',
        votingHistory: 'Yes', votingFrequency: '1-2 Times',
        kkAssembly: 'Yes', votingReason: '',
        facebook: 'ana.santos', groupChat: 'Yes', date: '2024-02-10',
    },
    {
        id: 5, year: 2025, respondentNo: 'PK-2025-001',
        lastName: 'Mendoza', firstName: 'Carlo', middleName: 'Ramos', suffix: '',
        age: 22, barangay: 'Calios', purokZone: 'Damayan',
        registeredVoter: 'Yes',
        sex: 'Male', birthday: '07/07/2002', email: 'carlo.mendoza@email.com', contact: '09611234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'Out of School Youth', workStatus: 'Self-Employed',
        education: 'High School Grad', skVoter: 'Yes', natVoter: 'Yes',
        votingHistory: 'Yes', votingFrequency: '1-2 Times',
        kkAssembly: 'No', votingReason: 'There was no KK Assembly',
        facebook: 'carlo.mendoza', groupChat: 'Yes', date: '2025-03-05',
    },
    {
        id: 6, year: 2025, respondentNo: 'PK-2025-002',
        lastName: 'Torres', firstName: 'Liza', middleName: 'Villanueva', suffix: '',
        age: 16, barangay: 'Calios', purokZone: 'Marcelo',
        registeredVoter: 'No',
        sex: 'Female', birthday: '30/04/2008', email: 'liza.torres@email.com', contact: '09721234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Child Youth (15-17 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'High School Level', skVoter: 'No', natVoter: 'No',
        votingHistory: 'No', votingFrequency: '',
        kkAssembly: 'Yes', votingReason: '',
        facebook: 'liza.torres', groupChat: 'No', date: '2025-03-05',
    },
    {
        id: 7, year: 2026, respondentNo: 'PK-2026-001',
        lastName: 'Bautista', firstName: 'Marco', middleName: 'Reyes', suffix: '',
        age: 21, barangay: 'Calios', purokZone: 'Bigayan Villa Rosa',
        registeredVoter: 'Yes',
        sex: 'Male', birthday: '12/03/2004', email: 'marco.bautista@email.com', contact: '09831234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'College Level', skVoter: 'Yes', natVoter: 'Yes',
        votingHistory: 'Yes', votingFrequency: '1-2 Times',
        kkAssembly: 'Yes', votingReason: '',
        facebook: 'marco.bautista', groupChat: 'Yes', date: '2026-01-20',
    },
    {
        id: 8, year: 2026, respondentNo: 'PK-2026-002',
        lastName: 'Villanueva', firstName: 'Rosa', middleName: 'Dela Torre', suffix: '',
        age: 18, barangay: 'Calios', purokZone: 'Phase 3',
        registeredVoter: 'No',
        sex: 'Female', birthday: '05/08/2007', email: 'rosa.villanueva@email.com', contact: '09941234567',
        region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz',
        civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
        youthClassification: 'In School Youth', workStatus: 'Unemployed',
        education: 'High School Grad', skVoter: 'No', natVoter: 'No',
        votingHistory: 'No', votingFrequency: '',
        kkAssembly: 'No', votingReason: 'Not Interested to Attend',
        facebook: 'rosa.villanueva', groupChat: 'Yes', date: '2026-01-20',
    },
];

/* ── State ── */
let filteredData    = [...PREV_KAB_DATA];
let currentPage     = 1;
const ROWS_PER_PAGE = 10;
let uploadedRows    = [];

/* ── DOM refs ── */
const tableBody      = document.getElementById('prevKabTableBody');
const searchInput    = document.getElementById('prevKabSearch');
const yearFilter     = document.getElementById('prevKabYearFilter');
const purokFilter    = document.getElementById('prevKabPurokFilter');
const voterFilter    = document.getElementById('prevKabVoterFilter');
const paginationInfo = document.getElementById('prevKabPaginationInfo');
const paginationNums = document.getElementById('prevKabPaginationNums');
const prevBtn        = document.getElementById('prevKabPrevBtn');
const nextBtn        = document.getElementById('prevKabNextBtn');

/* ── Helpers ── */
function fullName(r) {
    const parts = [r.lastName, r.firstName, r.middleName].filter(Boolean).join(', ');
    return r.suffix ? `${parts} ${r.suffix}` : parts;
}

/* ── Filter & Render ── */
function applyFilters() {
    const q      = (searchInput?.value || '').toLowerCase().trim();
    const yr     = yearFilter?.value || '';
    const purok  = purokFilter?.value || '';
    const voter  = voterFilter?.value || '';

    filteredData = PREV_KAB_DATA.filter(r => {
        const matchQ     = !q     || fullName(r).toLowerCase().includes(q) || r.respondentNo.toLowerCase().includes(q);
        const matchYr    = !yr    || String(r.year) === yr;
        const matchPurok = !purok || r.purokZone === purok;
        const matchVoter = !voter || r.registeredVoter === voter;
        return matchQ && matchYr && matchPurok && matchVoter;
    });

    currentPage = 1;
    renderTable();
}

function renderTable() {
    const total = filteredData.length;
    const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    if (currentPage > pages) currentPage = pages;

    const start = (currentPage - 1) * ROWS_PER_PAGE;
    const slice = filteredData.slice(start, start + ROWS_PER_PAGE);

    if (!tableBody) return;

    if (slice.length === 0) {
        tableBody.innerHTML = `<tr class="empty-state-row"><td colspan="7">No records found.</td></tr>`;
    } else {
        tableBody.innerHTML = slice.map(r => `
            <tr>
                <td>${r.respondentNo}</td>
                <td class="fullname-cell">${fullName(r)}</td>
                <td>${r.age}</td>
                <td>${r.barangay}</td>
                <td>${r.purokZone}</td>
                <td><span class="voter-badge ${r.registeredVoter === 'Yes' ? 'yes' : 'no'}">${r.registeredVoter}</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-action-view" onclick="openViewModal(${r.id})">View</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    if (paginationInfo) {
        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(start + ROWS_PER_PAGE, total);
        paginationInfo.textContent = `Showing ${from}–${to} of ${total} record${total !== 1 ? 's' : ''}`;
    }

    renderPagination(pages);
}

function renderPagination(pages) {
    if (!paginationNums) return;

    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= pages;

    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage   = Math.min(pages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

    let html = '';
    if (startPage > 1) html += `<button class="page-number" onclick="goToPage(1)">1</button>${startPage > 2 ? '<span style="padding:0 4px;color:#9ca3af">…</span>' : ''}`;
    for (let p = startPage; p <= endPage; p++) {
        html += `<button class="page-number ${p === currentPage ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
    }
    if (endPage < pages) html += `${endPage < pages - 1 ? '<span style="padding:0 4px;color:#9ca3af">…</span>' : ''}<button class="page-number" onclick="goToPage(${pages})">${pages}</button>`;

    paginationNums.innerHTML = html;
}

window.goToPage = function(p) {
    currentPage = p;
    renderTable();
};

/* ── View Modal ── */
window.openViewModal = function(id) {
    const r = PREV_KAB_DATA.find(rec => rec.id === id);
    if (!r) return;

    setText('pvRespondentNumber', r.respondentNo);
    setText('pvDate',       r.date);
    setText('pvLastName',   r.lastName);
    setText('pvFirstName',  r.firstName);
    setText('pvMiddleName', r.middleName);
    setText('pvSuffix',     r.suffix);
    setText('pvRegion',     r.region);
    setText('pvProvince',   r.province);
    setText('pvCity',       r.city);
    setText('pvBarangay',   r.barangay);
    setText('pvPurokZone',  r.purokZone);
    setText('pvAge',        r.age);
    setText('pvDob',        r.birthday);
    setText('pvEmail',      r.email);
    setText('pvContact',    r.contact);
    setText('pvFacebook',   r.facebook);

    setCheckboxes('pvSex',                 r.sex);
    setCheckboxes('pvCivilStatus',         r.civilStatus);
    setCheckboxes('pvYouthAgeGroup',       r.youthAgeGroup);
    setCheckboxes('pvYouthClassification', r.youthClassification);
    setCheckboxes('pvWorkStatus',          r.workStatus);
    setCheckboxes('pvEducation',           r.education);
    setCheckboxes('pvSKVoter',             r.skVoter);
    setCheckboxes('pvNatVoter',            r.natVoter);
    setCheckboxes('pvVotingHistory',       r.votingHistory);
    setCheckboxes('pvVotingFrequency',     r.votingFrequency);
    setCheckboxes('pvKKAssembly',          r.kkAssembly);
    setCheckboxes('pvVotingReason',        r.votingReason);
    setCheckboxes('pvGroupChat',           r.groupChat);

    setText('pvSignatureText', `${r.firstName} ${r.lastName}`);

    // Inject unique signature SVG path for this record
    const sigSvg = document.getElementById('pvSignatureSvg');
    if (sigSvg) {
        sigSvg.innerHTML = `<path d="${getSignaturePath(r.id)}" fill="none" stroke="#1a1a1a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>`;
    }

    document.getElementById('prevKabViewModal').style.display = 'flex';
};

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '';
}

function setCheckboxes(field, value) {
    document.querySelectorAll(`[data-view-field="${field}"]`).forEach(chk => {
        chk.checked = chk.value === value;
    });
}

/* ── Modal close / maximize ── */
document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('prevKabViewModal').style.display   = 'none';
        document.getElementById('prevKabUploadModal').style.display = 'none';
        resetUploadModal();
    });
});

const viewBackdrop = document.getElementById('prevKabViewModal');
if (viewBackdrop) {
    viewBackdrop.addEventListener('click', e => {
        if (e.target === viewBackdrop) viewBackdrop.style.display = 'none';
    });
}

const viewToggleBtn = document.getElementById('prevKabViewModalToggle');
if (viewToggleBtn) {
    viewToggleBtn.addEventListener('click', () => {
        viewBackdrop?.classList.toggle('modal-maximized');
        viewToggleBtn.textContent = viewBackdrop?.classList.contains('modal-maximized') ? '❐' : '□';
    });
}

/* ── Upload Modal ── */
const uploadBtn       = document.getElementById('prevKabUploadBtn');
const uploadModal     = document.getElementById('prevKabUploadModal');
const uploadZone      = document.getElementById('prevKabUploadZone');
const fileInput       = document.getElementById('prevKabFileInput');
const selectedFileBar = document.getElementById('prevKabSelectedFile');
const selectedFileName= document.getElementById('prevKabSelectedFileName');
const removeFileBtn   = document.getElementById('prevKabRemoveFile');
const confirmUploadBtn= document.getElementById('prevKabConfirmUpload');
const confirmSaveBtn  = document.getElementById('prevKabConfirmSave');
const inlinePreview   = document.getElementById('prevKabInlinePreview');

if (uploadBtn)   uploadBtn.addEventListener('click', () => { uploadModal.style.display = 'flex'; });
if (uploadModal) uploadModal.addEventListener('click', e => {
    if (e.target === uploadModal) { uploadModal.style.display = 'none'; resetUploadModal(); }
});

if (uploadZone) {
    uploadZone.addEventListener('click', () => fileInput?.click());
    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        const file = e.dataTransfer?.files?.[0];
        if (file) handleFileSelected(file);
    });
}

if (fileInput) {
    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (file) handleFileSelected(file);
    });
}

function handleFileSelected(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx','xls'].includes(ext)) { alert('Please upload a valid Excel file (.xlsx or .xls).'); return; }
    if (file.size > 10 * 1024 * 1024) { alert('File size exceeds 10MB limit.'); return; }

    if (selectedFileName) selectedFileName.textContent = file.name;
    if (selectedFileBar)  selectedFileBar.classList.add('visible');
    if (confirmUploadBtn) confirmUploadBtn.disabled = false;

    // Immediately show inline preview with mock full-detail data
    uploadedRows = generateFullMockRows();
    renderFullPreviewTable(uploadedRows);

    // Expand modal to wide mode and show preview
    if (inlinePreview) inlinePreview.style.display = 'block';
    if (uploadZone)    uploadZone.style.display = 'none';
    if (confirmSaveBtn) confirmSaveBtn.style.display = 'inline-flex';
    if (confirmUploadBtn) confirmUploadBtn.style.display = 'none';

    // Widen the modal
    const modalBox = uploadModal?.querySelector('.upload-modal-box--wide');
    if (modalBox) modalBox.classList.add('is-previewing');
}

if (removeFileBtn) {
    removeFileBtn.addEventListener('click', () => {
        if (fileInput) fileInput.value = '';
        resetUploadModal();
    });
}

function resetUploadModal() {
    uploadedRows = [];
    if (selectedFileBar)  selectedFileBar.classList.remove('visible');
    if (confirmUploadBtn) { confirmUploadBtn.disabled = true; confirmUploadBtn.style.display = 'inline-flex'; }
    if (confirmSaveBtn)   confirmSaveBtn.style.display = 'none';
    if (inlinePreview)    inlinePreview.style.display = 'none';
    if (uploadZone)       uploadZone.style.display = 'block';
    const modalBox = uploadModal?.querySelector('.upload-modal-box--wide');
    if (modalBox) modalBox.classList.remove('is-previewing');
}

/* ── Generate full mock rows for preview ── */
function generateFullMockRows() {
    return [
        {
            respondentNo: 'PK-UPLOAD-001', date: '2026-01-15',
            lastName: 'Aquino', firstName: 'Jose', middleName: 'Rizal', suffix: '',
            region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz', barangay: 'Calios', purokZone: 'Bayside',
            sex: 'Male', age: 21, birthday: '12/03/2004', email: 'jose.aquino@email.com', contact: '09831234567',
            civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
            youthClassification: 'In School Youth', workStatus: 'Unemployed',
            education: 'College Level', skVoter: 'Yes', natVoter: 'Yes',
            votingHistory: 'Yes', votingFrequency: '1-2 Times',
            kkAssembly: 'Yes', votingReason: '',
            facebook: 'jose.aquino', groupChat: 'Yes',
            registeredVoter: 'Yes',
        },
        {
            respondentNo: 'PK-UPLOAD-002', date: '2026-01-15',
            lastName: 'Bonifacio', firstName: 'Andres', middleName: 'Procopio', suffix: '',
            region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz', barangay: 'Calios', purokZone: 'Villa Gracia',
            sex: 'Male', age: 18, birthday: '05/08/2007', email: 'andres.bonifacio@email.com', contact: '09941234567',
            civilStatus: 'Single', youthAgeGroup: 'Core Youth (18-24 yrs old)',
            youthClassification: 'Out of School Youth', workStatus: 'Unemployed',
            education: 'High School Grad', skVoter: 'No', natVoter: 'No',
            votingHistory: 'No', votingFrequency: '',
            kkAssembly: 'No', votingReason: 'Not Interested to Attend',
            facebook: 'andres.bonifacio', groupChat: 'No',
            registeredVoter: 'No',
        },
        {
            respondentNo: 'PK-UPLOAD-003', date: '2026-01-15',
            lastName: 'Luna', firstName: 'Antonio', middleName: 'Narciso', suffix: 'Jr.',
            region: 'Region IV-A', province: 'Laguna', city: 'Santa Cruz', barangay: 'Calios', purokZone: 'Imelda',
            sex: 'Male', age: 25, birthday: '20/11/2000', email: 'antonio.luna@email.com', contact: '09051234567',
            civilStatus: 'Married', youthAgeGroup: 'Core Youth (18-24 yrs old)',
            youthClassification: 'Working Youth', workStatus: 'Employed',
            education: 'College Grad', skVoter: 'Yes', natVoter: 'Yes',
            votingHistory: 'Yes', votingFrequency: '3-4 Times',
            kkAssembly: 'Yes', votingReason: '',
            facebook: 'antonio.luna', groupChat: 'Yes',
            registeredVoter: 'Yes',
        },
    ];
}

/* ── Render full preview table ── */
function renderFullPreviewTable(rows) {
    const tbody = document.getElementById('prevKabPreviewTableBody');
    const count = document.getElementById('prevKabPreviewCount');
    if (count) count.textContent = rows.length;
    if (!tbody) return;

    const chk = (val, match) => val === match ? '✔' : '';

    tbody.innerHTML = rows.map((r, idx) => `
        <tr>
            <td>${r.respondentNo}</td>
            <td>${r.date}</td>
            <td>${r.lastName}</td>
            <td>${r.firstName}</td>
            <td>${r.middleName}</td>
            <td>${r.suffix}</td>
            <td>${r.region}</td>
            <td>${r.province}</td>
            <td>${r.city}</td>
            <td>${r.barangay}</td>
            <td>${r.purokZone}</td>
            <td>${r.sex}</td>
            <td>${r.age}</td>
            <td>${r.birthday}</td>
            <td>${r.email}</td>
            <td>${r.contact}</td>
            <td>${chk(r.civilStatus,'Single')}</td>
            <td>${chk(r.civilStatus,'Married')}</td>
            <td>${chk(r.civilStatus,'Widowed')}</td>
            <td>${chk(r.civilStatus,'Divorced')}</td>
            <td>${chk(r.civilStatus,'Separated')}</td>
            <td>${chk(r.civilStatus,'Annulled')}</td>
            <td>${chk(r.civilStatus,'Unknown')}</td>
            <td>${chk(r.civilStatus,'Live-in')}</td>
            <td>${chk(r.youthAgeGroup,'Child Youth (15-17 yrs old)')}</td>
            <td>${chk(r.youthAgeGroup,'Core Youth (18-24 yrs old)')}</td>
            <td>${chk(r.youthAgeGroup,'Young Adult (15-30 yrs old)')}</td>
            <td>${chk(r.education,'Elementary Level')}</td>
            <td>${chk(r.education,'Elementary Grad')}</td>
            <td>${chk(r.education,'High School Level')}</td>
            <td>${chk(r.education,'High School Grad')}</td>
            <td>${chk(r.education,'Vocational Grad')}</td>
            <td>${chk(r.education,'College Level')}</td>
            <td>${chk(r.education,'College Grad')}</td>
            <td>${chk(r.education,'Masters Level')}</td>
            <td>${chk(r.education,'Masters Grad')}</td>
            <td>${chk(r.education,'Doctorate Level')}</td>
            <td>${chk(r.education,'Doctorate Graduate')}</td>
            <td>${chk(r.youthClassification,'In School Youth')}</td>
            <td>${chk(r.youthClassification,'Out of School Youth')}</td>
            <td>${chk(r.youthClassification,'Working Youth')}</td>
            <td>${chk(r.youthClassification,'Person w/ Disability')}</td>
            <td>${chk(r.youthClassification,'Children in Conflict w/ Law')}</td>
            <td>${chk(r.youthClassification,'Indigenous People')}</td>
            <td>${chk(r.youthClassification,'Youth w/ Specific Needs')}</td>
            <td>${chk(r.workStatus,'Employed')}</td>
            <td>${chk(r.workStatus,'Unemployed')}</td>
            <td>${chk(r.workStatus,'Self-Employed')}</td>
            <td>${chk(r.workStatus,'Currently looking for a Job')}</td>
            <td>${chk(r.workStatus,'Not Interested Looking for a Job')}</td>
            <td>${r.skVoter}</td>
            <td>${r.votingHistory}</td>
            <td>${r.votingFrequency}</td>
            <td>${r.natVoter}</td>
            <td>${r.kkAssembly}</td>
            <td>${r.votingReason}</td>
            <td>${r.facebook}</td>
            <td>${r.groupChat}</td>
            <td style="min-width:120px;padding:4px 8px;">${makeSignatureSvg(idx)}</td>
        </tr>
    `).join('');
}

/* ── Confirm Save ── */
if (confirmSaveBtn) {
    confirmSaveBtn.addEventListener('click', () => {
        const saved = uploadedRows.length;
        const nextId = PREV_KAB_DATA.length + 1;
        const thisYear = new Date().getFullYear();

        uploadedRows.forEach((row, i) => {
            PREV_KAB_DATA.push({
                id: nextId + i, year: thisYear,
                respondentNo: row.respondentNo,
                lastName: row.lastName, firstName: row.firstName,
                middleName: row.middleName, suffix: row.suffix,
                age: row.age, barangay: row.barangay, purokZone: row.purokZone,
                registeredVoter: row.registeredVoter,
                sex: row.sex, birthday: row.birthday, email: row.email, contact: row.contact,
                region: row.region, province: row.province, city: row.city,
                civilStatus: row.civilStatus, youthAgeGroup: row.youthAgeGroup,
                youthClassification: row.youthClassification,
                workStatus: row.workStatus, education: row.education,
                skVoter: row.skVoter, natVoter: row.natVoter,
                votingHistory: row.votingHistory, votingFrequency: row.votingFrequency,
                kkAssembly: row.kkAssembly, votingReason: row.votingReason,
                facebook: row.facebook, groupChat: row.groupChat,
                date: row.date,
            });
        });

        uploadModal.style.display = 'none';
        resetUploadModal();
        applyFilters();
        showToast(`${saved} record(s) saved successfully.`);
    });
}

/* ── Toast ── */
function showToast(msg) {
    let toast = document.getElementById('prevKabToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'prevKabToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(34,197,94,.4);transition:opacity .3s ease;';
        document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.style.opacity = '1';
    setTimeout(() => { toast.style.opacity = '0'; }, 3000);
}

/* ── Event Listeners ── */
if (searchInput)    searchInput.addEventListener('input', applyFilters);
if (yearFilter)     yearFilter.addEventListener('change', applyFilters);
if (purokFilter)    purokFilter.addEventListener('change', applyFilters);
if (voterFilter)    voterFilter.addEventListener('change', applyFilters);
if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderTable(); } });
if (nextBtn) nextBtn.addEventListener('click', () => {
    const pages = Math.ceil(filteredData.length / ROWS_PER_PAGE);
    if (currentPage < pages) { currentPage++; renderTable(); }
});

/* ── Init ── */
applyFilters();
