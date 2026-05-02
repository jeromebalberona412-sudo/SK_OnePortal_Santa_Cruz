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
/* ── State ── */
let PREV_KAB_DATA   = [];
let filteredData    = [];
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
    const parts = [r.lastName || r.last_name, r.firstName || r.first_name, r.middleName || r.middle_name].filter(Boolean).join(', ');
    const sfx = r.suffix;
    return sfx ? `${parts} ${sfx}` : parts;
}

/* ── Load from API ── */
function loadData() {
    const url = new URL('/previous-kabataan/data', window.location.origin);
    if (yearFilter?.value)  url.searchParams.set('year',   yearFilter.value);
    if (searchInput?.value) url.searchParams.set('search', searchInput.value.trim());
    if (purokFilter?.value) url.searchParams.set('purok',  purokFilter.value);
    if (voterFilter?.value) url.searchParams.set('voter',  voterFilter.value);

    fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(response => {
        PREV_KAB_DATA = (response.data || []).map(r => ({
            id: r.id,
            year: r.profiling_year,
            respondentNo: r.respondent_no,
            name: r.name || '',
            lastName: r.last_name,
            firstName: r.first_name,
            middleName: r.middle_name,
            suffix: r.suffix,
            age: r.age,
            barangay: r.barangay,
            homeAddress: r.home_address || r.purok_zone || '',
            purokZone: r.purok_zone,
            registeredVoter: r.sk_voter,
            sex: r.sex,
            birthday: r.birthday,
            email: r.email,
            contact: r.contact_number,
            region: r.region,
            province: r.province,
            city: r.city,
            civilStatus: r.civil_status,
            youthAgeGroup: r.youth_age_group,
            youthClassification: r.youth_classification,
            workStatus: r.work_status,
            education: r.education,
            skVoter: r.sk_voter,
            natVoter: r.national_voter,
            votingHistory: r.sk_voted,
            votingFrequency: r.vote_frequency,
            kkAssembly: r.kk_assembly,
            votingReason: Array.isArray(r.kk_reason) ? r.kk_reason[0] : r.kk_reason,
            facebook: r.facebook,
            groupChat: r.group_chat,
            signature: r.signature,
            date: r.date,
        }));

        // Populate year filter dynamically
        if (yearFilter && response.years?.length) {
            const currentVal = yearFilter.value;
            yearFilter.innerHTML = '<option value="">All Years</option>';
            response.years.forEach(y => {
                yearFilter.innerHTML += `<option value="${y}" ${String(y) === currentVal ? 'selected' : ''}>${y}</option>`;
            });
        }

        filteredData = [...PREV_KAB_DATA];
        currentPage = 1;
        renderTable();
    })
    .catch(() => {
        filteredData = [];
        renderTable();
    });
}

/* ── Filter & Render ── */
function applyFilters() {
    loadData();
}

/* ── Render ── */
function renderTable() {
    const total = filteredData.length;
    const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));

    const start = (currentPage - 1) * ROWS_PER_PAGE;
    const slice = filteredData.slice(start, start + ROWS_PER_PAGE);

    if (!tableBody) return;

    if (slice.length === 0) {
        tableBody.innerHTML = `<tr class="empty-state-row"><td colspan="13">No records found.</td></tr>`;
    } else {
        tableBody.innerHTML = slice.map((r, i) => `
            <tr>
                <td>${(currentPage - 1) * ROWS_PER_PAGE + i + 1}</td>
                <td class="fullname-cell">${r.name || fullName(r)}</td>
                <td>${r.age}</td>
                <td>${r.barangay}</td>
                <td>${r.homeAddress || r.purokZone || '—'}</td>
                <td>${r.sex}</td>
                <td>${r.civilStatus}</td>
                <td>${r.youthClassification}</td>
                <td>${r.youthAgeGroup}</td>
                <td>${r.education}</td>
                <td>${r.workStatus}</td>
                <td><span class="voter-badge ${r.registeredVoter === 'YES' || r.registeredVoter === 'Yes' ? 'yes' : 'no'}">${r.registeredVoter}</span></td>
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
    setCheckboxes('pvYouthAgeGroup',       normalizeValue('pvYouthAgeGroup', r.youthAgeGroup));
    setCheckboxes('pvYouthClassification', normalizeValue('pvYouthClassification', r.youthClassification));
    setCheckboxes('pvWorkStatus',          normalizeValue('pvWorkStatus', r.workStatus));
    setCheckboxes('pvEducation',           normalizeValue('pvEducation', r.education));
    setCheckboxes('pvSKVoter',             r.skVoter);
    setCheckboxes('pvNatVoter',            r.natVoter);
    setCheckboxes('pvVotingHistory',       r.votingHistory);
    setCheckboxes('pvVotingFrequency',     normalizeValue('pvVotingFrequency', r.votingFrequency));
    setCheckboxes('pvKKAssembly',          r.kkAssembly);
    setCheckboxes('pvVotingReason',        r.votingReason);
    setCheckboxes('pvGroupChat',           r.groupChat);

    // Signature — image or text
    const sigSvg  = document.getElementById('pvSignatureSvg');
    const sigText = document.getElementById('pvSignatureText');
    const printedName = [r.firstName, r.middleName ? r.middleName.charAt(0) + '.' : null, r.lastName, r.suffix].filter(Boolean).join(' ');

    if (r.signature && r.signature.startsWith('data:image')) {
        if (sigSvg) sigSvg.innerHTML = '';
        if (sigText) sigText.innerHTML = `<img src="${r.signature}" alt="Signature" style="max-width:100%;max-height:60px;display:block;"><span style="display:block;margin-top:4px;font-weight:600;">${printedName}</span>`;
    } else {
        if (sigSvg) {
            sigSvg.innerHTML = `<path d="${getSignaturePath(r.id)}" fill="none" stroke="#1a1a1a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>`;
        }
        if (sigText) sigText.textContent = printedName;
    }

    document.getElementById('prevKabViewModal').style.display = 'flex';
};

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '';
}

function setCheckboxes(field, value) {
    if (!value) return;
    const v = String(value).trim().toLowerCase();
    document.querySelectorAll(`[data-view-field="${field}"]`).forEach(chk => {
        chk.checked = chk.value.trim().toLowerCase() === v;
    });
}

// Normalize Excel abbreviated values to full display values
function normalizeValue(field, value) {
    if (!value) return value;
    const v = String(value).trim().toUpperCase();

    if (field === 'pvYouthAgeGroup') {
        if (v === '15-17') return 'Child Youth (15-17 yrs old)';
        if (v === '18-24') return 'Core Youth (18-24 yrs old)';
        if (v === '15-30') return 'Young Adult (15-30 yrs old)';
    }
    if (field === 'pvYouthClassification') {
        if (v === 'IN SCHOOL' || v === 'IN SCHOOL YOUTH') return 'In School Youth';
        if (v === 'OUT OF SCHOOL' || v === 'OUT OF SCHOOL YOUTH') return 'Out of School Youth';
        if (v === 'WORKING' || v === 'WORKING YOUTH') return 'Working Youth';
    }
    if (field === 'pvWorkStatus') {
        if (v === 'N/A' || v === 'NA') return '';
        if (v === 'EMPLOYED') return 'Employed';
        if (v === 'UNEMPLOYED') return 'Unemployed';
        if (v === 'SELF-EMPLOYED') return 'Self-Employed';
    }
    if (field === 'pvEducation') {
        if (v === 'HIGHSCHOOL' || v === 'HIGH SCHOOL') return 'High School Level';
        if (v === 'SENIOR HIGH' || v === 'SENIOR HIGHSCHOOL' || v === 'SENIOR HIGH SCHOOL') return 'High School Grad';
        if (v === 'COLLEGE') return 'College Level';
        if (v === 'COLLEGE GRADUATE' || v === 'COLLEGE GRAD') return 'College Grad';
        if (v === 'MASTERS' || v === 'MASTERS LEVEL') return 'Masters Level';
    }
    if (field === 'pvVotingFrequency') {
        if (v === '1' || v === '1-2' || v === '1-2 TIMES') return '1-2 Times';
        if (v === '2' || v === '3-4' || v === '3-4 TIMES') return '3-4 Times';
        if (v === '3' || v === '5+' || v === '5 AND ABOVE' || v === '5 ABOVE') return '5 and above';
    }
    return value;
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

/* ── Upload modal maximize/restore toggle ── */
const uploadToggleBtn = document.getElementById('prevKabUploadModalToggle');
if (uploadToggleBtn) {
    uploadToggleBtn.addEventListener('click', () => {
        uploadModal?.classList.toggle('modal-maximized');
        const isMax = uploadModal?.classList.contains('modal-maximized');
        uploadToggleBtn.textContent = isMax ? '❐' : '□';
        // When maximizing while previewing, also ensure wide class stays
        const modalBox = uploadModal?.querySelector('.upload-modal-box--wide');
        if (modalBox && isMax && uploadedRows.length > 0) {
            modalBox.classList.add('is-previewing');
        }
    });
}

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
    if (confirmUploadBtn) confirmUploadBtn.disabled = true; // disable until parsed

    // Parse Excel with SheetJS
    import('xlsx').then((XLSX) => {
        const { read, utils } = XLSX;
        const reader = new FileReader();
        reader.onload = (e) => {
            const wb = read(e.target.result, { type: 'array' });
            const ws = wb.Sheets[wb.SheetNames[0]];
            const raw = utils.sheet_to_json(ws, { defval: '' });

            // Normalize keys: lowercase + underscores
            uploadedRows = raw.map((r, idx) => {
                const norm = {};
                Object.keys(r).forEach(k => { norm[k.toLowerCase().replace(/[\s\/\-]+/g, '_')] = r[k]; });

                // Convert Excel date serial to readable date
                const rawBday = norm['birthday'] || norm['birthday_(month_day_year)'] || '';
                let birthday = rawBday;
                if (typeof rawBday === 'number') {
                    const d = XLSX.SSF.parse_date_code(rawBday);
                    birthday = `${String(d.m).padStart(2,'0')}/${String(d.d).padStart(2,'0')}/${d.y}`;
                }

                const voteTimes = norm['if_yes,_how_many_times?'] || norm['if_yes__how_many_times?'] || '';

                return {
                    respondentNo:        String(idx + 1).padStart(3, '0'),
                    date:                norm['date'] || '',
                    name:                norm['name'] || '',
                    lastName:            '',
                    firstName:           '',
                    middleName:          '',
                    suffix:              '',
                    region:              norm['region'] || 'Region IV-A (CALABARZON)',
                    province:            norm['province'] || 'Laguna',
                    city:                norm['city_municipality'] || norm['city'] || 'Santa Cruz',
                    barangay:            norm['barangay'] || '',
                    homeAddress:         String(norm['home_address'] || '').trim(),
                    purokZone:           String(norm['home_address'] || '').trim(),
                    sex:                 norm['sex_assigned_at_birth'] || norm['sex'] || '',
                    age:                 norm['age'] || '',
                    birthday:            birthday,
                    email:               norm['email'] || '',
                    contact:             String(norm['contact_number'] || ''),
                    civilStatus:         String(norm['civil_status'] || '').trim(),
                    youthAgeGroup:       norm['youth_age_group'] || '',
                    youthClassification: String(norm['youth_classification'] || '').trim(),
                    workStatus:          String(norm['work_status'] || '').trim(),
                    education:           String(norm['highest_educational_attainment'] || norm['education'] || '').trim(),
                    skVoter:             norm['registered_voter?'] || norm['registered_voter'] || '',
                    natVoter:            '',
                    votingHistory:       norm['voted_last_election?'] || norm['voted_last_election'] || '',
                    votingFrequency:     voteTimes ? String(voteTimes) : '',
                    kkAssembly:          norm['attended_kk__assembly?'] || norm['attended_kk_assembly?'] || norm['attended_kk_assembly'] || '',
                    votingReason:        '',
                    facebook:            norm['facebook'] || '',
                    groupChat:           norm['group_chat'] || '',
                    registeredVoter:     norm['registered_voter?'] || norm['registered_voter'] || '',
                };
            }).filter(r => r.name || r.lastName);

            renderFullPreviewTable(uploadedRows);

            if (inlinePreview) inlinePreview.style.display = 'block';
            if (uploadZone)    uploadZone.style.display = 'none';
            if (confirmSaveBtn) confirmSaveBtn.style.display = 'inline-flex';
            if (confirmUploadBtn) { confirmUploadBtn.style.display = 'none'; confirmUploadBtn.disabled = false; }

            const modalBox = uploadModal?.querySelector('.upload-modal-box--wide');
            if (modalBox) modalBox.classList.add('is-previewing');
        };
        reader.readAsArrayBuffer(file);
    }).catch(() => {
        alert('Failed to load Excel parser. Please try again.');
    });
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
    // Reset maximize state
    if (uploadModal) uploadModal.classList.remove('modal-maximized');
    const uploadToggleBtn = document.getElementById('prevKabUploadModalToggle');
    if (uploadToggleBtn) uploadToggleBtn.textContent = '□';
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

    tbody.innerHTML = rows.map((r, idx) => `
        <tr>
            <td>${idx + 1}</td>
            <td>${r.name || [r.lastName, r.firstName, r.middleName].filter(Boolean).join(', ')}</td>
            <td>${r.age}</td>
            <td>${r.birthday}</td>
            <td>${r.sex}</td>
            <td>${r.civilStatus}</td>
            <td>${r.youthClassification}</td>
            <td>${r.youthAgeGroup}</td>
            <td>${r.contact}</td>
            <td>${r.homeAddress || r.purokZone}</td>
            <td>${r.education}</td>
            <td>${r.workStatus}</td>
            <td>${r.registeredVoter}</td>
            <td>${r.votingHistory}</td>
            <td>${r.kkAssembly}</td>
            <td>${r.votingFrequency}</td>
            <td>${r.barangay}</td>
            <td>${r.region}</td>
            <td>${r.province}</td>
            <td>${r.city}</td>
        </tr>
    `).join('');
}

/* ── Confirm Save ── */
if (confirmSaveBtn) {
    confirmSaveBtn.addEventListener('click', () => {
        if (!uploadedRows.length) return;

        confirmSaveBtn.disabled = true;
        confirmSaveBtn.textContent = 'Saving...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const BATCH = 200;
        const batches = [];
        for (let i = 0; i < uploadedRows.length; i += BATCH) {
            batches.push(uploadedRows.slice(i, i + BATCH));
        }

        let totalSaved = 0;

        const sendBatch = (idx) => {
            if (idx >= batches.length) {
                uploadModal.style.display = 'none';
                resetUploadModal();
                loadData();
                showToast(`${totalSaved} record(s) saved successfully.`);
                return;
            }

            confirmSaveBtn.textContent = `Saving... (${idx * BATCH}/${uploadedRows.length})`;

            fetch('/previous-kabataan/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ rows: batches[idx] }),
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    totalSaved += res.saved;
                    sendBatch(idx + 1);
                } else {
                    showToast('Failed to save records. Please try again.', 'error');
                    confirmSaveBtn.disabled = false;
                    confirmSaveBtn.textContent = 'Confirm & Save';
                }
            })
            .catch(() => {
                showToast('Network error. Please try again.', 'error');
                confirmSaveBtn.disabled = false;
                confirmSaveBtn.textContent = 'Confirm & Save';
            });
        };

        sendBatch(0);
    });
}

/* ── Header center banner (replaces bottom toast) ── */
function showToast(msg) {
    const banner     = document.getElementById('headerCenterBanner');
    const bannerText = document.getElementById('headerSuccessBannerText');
    if (!banner || !bannerText) return;

    bannerText.textContent = msg;
    banner.style.display = 'flex';

    // Reset animation
    const inner = document.getElementById('headerSuccessBanner');
    if (inner) {
        inner.style.animation = 'none';
        // Force reflow then re-apply
        void inner.offsetWidth;
        inner.style.animation = 'bannerFadeIn 0.3s ease';
    }

    // Auto-hide after 4 seconds
    clearTimeout(window._prevKabBannerTimer);
    window._prevKabBannerTimer = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);
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
loadData();
