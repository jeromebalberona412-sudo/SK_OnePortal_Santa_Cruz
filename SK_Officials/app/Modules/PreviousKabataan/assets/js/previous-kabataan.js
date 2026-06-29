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
let recordsPerPage  = 10;
const selectedIds   = new Set();
let uploadedRows    = [];
let deleteMode      = 'single';
let deleteRecordId  = null;
let pendingDeleteIds = [];
let sortColumn      = 'lastName';
let sortDirection   = 'asc';

const TABLE_COL_COUNT = 23;

const SORT_COLUMN_KEYS = [
    'rowNum',
    'lastName',
    'firstName',
    'middleName',
    'suffix',
    'age',
    'birthday',
    'sex',
    'civilStatus',
    'youthClassification',
    'youthAgeGroup',
    'contact',
    'homeAddress',
    'education',
    'workStatus',
    'registeredVoter',
    'votingHistory',
    'kkAssembly',
    'votingFrequency',
    'barangay',
    'region',
    'province',
    'city',
];

const NUMERIC_SORT_COLUMNS = new Set(['rowNum', 'age', 'votingFrequency']);

/* ── DOM refs ── */
const tableBody      = document.getElementById('prevKabTableBody');
const searchInput    = document.getElementById('prevKabSearch');
const yearFilter     = document.getElementById('prevKabYearFilter');
const purokFilter    = document.getElementById('prevKabPurokFilter');
const voterFilter    = document.getElementById('prevKabVoterFilter');
const bulkDeleteBtn  = document.getElementById('prevKabBulkDeleteBtn');
const bulkDeleteLabel = document.getElementById('prevKabBulkDeleteLabel');
const tableActionsBar = document.getElementById('prevKabTableActions');
const selectAllCheckbox = document.getElementById('prevKabSelectAll');

/* ── Helpers ── */
function cell(value) {
    if (value === null || value === undefined) {
        return '';
    }

    const text = String(value).trim();
    if (!text || text === '—' || text === 'None') {
        return '';
    }

    return text;
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
        applySort();
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

function getRecordSortValue(record, column) {
    if (column === 'rowNum') {
        return Number(record.id) || 0;
    }

    if (column === 'homeAddress') {
        return cell(record.homeAddress || record.purokZone);
    }

    const value = record[column];
    if (value === null || value === undefined) {
        return '';
    }

    return String(value).trim();
}

function parseBirthdaySortValue(value) {
    const text = String(value || '').trim();
    if (!text) {
        return 0;
    }

    const slashMatch = text.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (slashMatch) {
        const [, month, day, year] = slashMatch;
        return new Date(Number(year), Number(month) - 1, Number(day)).getTime() || 0;
    }

    const parsed = Date.parse(text);
    return Number.isNaN(parsed) ? 0 : parsed;
}

function compareSortValues(a, b, column) {
    if (column === 'birthday') {
        const left = parseBirthdaySortValue(a);
        const right = parseBirthdaySortValue(b);
        return left - right;
    }

    if (NUMERIC_SORT_COLUMNS.has(column)) {
        const left = Number(a) || 0;
        const right = Number(b) || 0;
        return left - right;
    }

    return String(a).localeCompare(String(b), undefined, { sensitivity: 'base', numeric: true });
}

function applySort() {
    const column = sortColumn;
    const direction = sortDirection === 'desc' ? -1 : 1;

    filteredData.sort((left, right) => {
        const result = compareSortValues(
            getRecordSortValue(left, column),
            getRecordSortValue(right, column),
            column
        );

        if (result !== 0) {
            return result * direction;
        }

        const lastNameCompare = compareSortValues(
            getRecordSortValue(left, 'lastName'),
            getRecordSortValue(right, 'lastName'),
            'lastName'
        );
        if (lastNameCompare !== 0) {
            return lastNameCompare;
        }

        return compareSortValues(
            getRecordSortValue(left, 'firstName'),
            getRecordSortValue(right, 'firstName'),
            'firstName'
        );
    });
}

function updateSortHeaderState() {
    document.querySelectorAll('.prev-kab-sortable').forEach((header) => {
        header.classList.remove('sort-asc', 'sort-desc');
        if (header.dataset.sort === sortColumn) {
            header.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

function initSortableHeaders() {
    const headerRow = document.querySelector('.prev-kab-table thead tr');
    if (!headerRow) {
        return;
    }

    const headers = Array.from(headerRow.querySelectorAll('th'));
    let sortableIndex = 0;

    headers.forEach((header) => {
        if (header.classList.contains('th-checkbox')) {
            return;
        }

        const sortKey = SORT_COLUMN_KEYS[sortableIndex];
        sortableIndex += 1;
        if (!sortKey) {
            return;
        }

        const label = header.textContent.trim();
        header.classList.add('prev-kab-sortable');
        header.dataset.sort = sortKey;
        header.innerHTML = `
            <button type="button" class="prev-kab-sort-btn" data-sort="${sortKey}" aria-label="Sort by ${label}">
                <span class="prev-kab-sort-label">${label}</span>
                <span class="prev-kab-sort-icon" aria-hidden="true"></span>
            </button>
        `;
    });

    headerRow.addEventListener('click', (event) => {
        const button = event.target.closest('.prev-kab-sort-btn');
        if (!button) {
            return;
        }

        const column = button.dataset.sort;
        if (!column) {
            return;
        }

        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }

        currentPage = 1;
        applySort();
        renderTable();
    });

    updateSortHeaderState();
}

/* ── Render ── */
function getTotalPages(count = filteredData.length) {
    return Math.max(1, Math.ceil(count / recordsPerPage) || 1);
}

function updatePaginationFooter(totalRecords) {
    const totalPages = getTotalPages(totalRecords);
    const pageInput = document.getElementById('prevKabPageInput');
    const totalPagesEl = document.getElementById('prevKabTotalPages');
    const prevBtn = document.getElementById('prevKabPrevBtn');
    const nextBtn = document.getElementById('prevKabNextBtn');
    const info = document.getElementById('prevKabPaginationInfo');

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    if (pageInput) {
        pageInput.value = String(currentPage);
        pageInput.min = '1';
        pageInput.max = String(totalPages);
    }

    if (totalPagesEl) {
        totalPagesEl.textContent = String(totalPages);
    }

    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages;
    }

    if (info) {
        info.textContent = `${totalRecords} record${totalRecords === 1 ? '' : 's'}`;
    }
}

function updateBulkToolbar() {
    const count = selectedIds.size;
    const hasSelection = count > 0;

    if (tableActionsBar) {
        tableActionsBar.hidden = !hasSelection;
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.hidden = !hasSelection;
    }

    if (bulkDeleteLabel) {
        bulkDeleteLabel.textContent = count > 1 ? `Delete Rows (${count})` : 'Delete Rows';
    }
}

function syncSelectAllCheckbox() {
    if (!selectAllCheckbox || !tableBody) {
        return;
    }

    const visibleCheckboxes = Array.from(tableBody.querySelectorAll('.prev-kab-row-checkbox'));
    const visibleIds = visibleCheckboxes
        .map((checkbox) => checkbox.dataset.id)
        .filter(Boolean);

    selectAllCheckbox.checked = visibleIds.length > 0 && visibleIds.every((id) => selectedIds.has(id));
    selectAllCheckbox.indeterminate = visibleIds.some((id) => selectedIds.has(id)) && !selectAllCheckbox.checked;
}

function renderTable() {
    const total = filteredData.length;
    const start = (currentPage - 1) * recordsPerPage;
    const slice = filteredData.slice(start, start + recordsPerPage);

    if (!tableBody) {
        return;
    }

    if (slice.length === 0) {
        tableBody.innerHTML = `<tr class="empty-state-row"><td colspan="${TABLE_COL_COUNT}">No records found.</td></tr>`;
    } else {
        tableBody.innerHTML = slice.map((r, i) => {
            const recordId = r.id ? String(r.id) : '';
            const voterValue = cell(r.registeredVoter);
            const voterClass = voterValue.toLowerCase() === 'yes' ? 'yes' : (voterValue.toLowerCase() === 'no' ? 'no' : '');

            return `
            <tr${recordId ? ` data-record-id="${recordId}"` : ''}>
                <td class="th-checkbox">
                    ${recordId ? `<input type="checkbox" class="prev-kab-checkbox prev-kab-row-checkbox" data-id="${recordId}" aria-label="Select row" ${selectedIds.has(recordId) ? 'checked' : ''}>` : ''}
                </td>
                <td>${start + i + 1}</td>
                <td>${cell(r.lastName)}</td>
                <td>${cell(r.firstName)}</td>
                <td>${cell(r.middleName)}</td>
                <td>${cell(r.suffix)}</td>
                <td>${cell(r.age)}</td>
                <td>${cell(r.birthday)}</td>
                <td>${cell(r.sex)}</td>
                <td>${cell(r.civilStatus)}</td>
                <td>${cell(r.youthClassification)}</td>
                <td>${cell(r.youthAgeGroup)}</td>
                <td>${cell(r.contact)}</td>
                <td>${cell(r.homeAddress || r.purokZone)}</td>
                <td>${cell(r.education)}</td>
                <td>${cell(r.workStatus)}</td>
                <td>${voterClass ? `<span class="voter-badge ${voterClass}">${voterValue}</span>` : ''}</td>
                <td>${cell(r.votingHistory)}</td>
                <td>${cell(r.kkAssembly)}</td>
                <td>${cell(r.votingFrequency)}</td>
                <td>${cell(r.barangay)}</td>
                <td>${cell(r.region)}</td>
                <td>${cell(r.province)}</td>
                <td>${cell(r.city)}</td>
            </tr>
        `;
        }).join('');
    }

    updatePaginationFooter(total);
    updateBulkToolbar();
    syncSelectAllCheckbox();
    updateSortHeaderState();
}

function resetDeleteModalFields() {
    const confirmInput = document.getElementById('prevKabDeleteConfirmInput');
    const confirmError = document.getElementById('prevKabDeleteConfirmError');
    const confirmBtn = document.getElementById('prevKabDeleteConfirmBtn');

    if (confirmInput) {
        confirmInput.value = '';
    }

    if (confirmError) {
        confirmError.style.display = 'none';
        confirmError.textContent = '';
    }

    syncDeleteConfirmButton();
}

function syncDeleteConfirmButton() {
    const confirmInput = document.getElementById('prevKabDeleteConfirmInput');
    const confirmBtn = document.getElementById('prevKabDeleteConfirmBtn');
    const confirmError = document.getElementById('prevKabDeleteConfirmError');
    const matched = (confirmInput?.value?.trim() || '') === 'Confirm';

    if (confirmBtn) {
        confirmBtn.disabled = !matched;
        confirmBtn.classList.toggle('is-disabled', !matched);
        confirmBtn.classList.toggle('is-enabled', matched);
    }

    if (confirmError && matched) {
        confirmError.style.display = 'none';
        confirmError.textContent = '';
    }
}

function closeDeleteModal() {
    const deleteModal = document.getElementById('prevKabDeleteModal');
    if (deleteModal) {
        deleteModal.style.display = 'none';
    }

    deleteMode = 'single';
    deleteRecordId = null;
    pendingDeleteIds = [];
    resetDeleteModalFields();
}

function updateDeleteModalMessage() {
    const messageEl = document.getElementById('prevKabDeleteMessage');
    if (!messageEl) {
        return;
    }

    const count = deleteMode === 'bulk' ? pendingDeleteIds.length : 1;
    messageEl.textContent = count > 1
        ? `Are you sure you want to delete ${count} selected records?`
        : 'Are you sure you want to delete this record?';
}

/* ── Delete Modal ── */
function openDeleteModal(id) {
    deleteMode = 'single';
    deleteRecordId = id;
    pendingDeleteIds = id ? [String(id)] : [];
    resetDeleteModalFields();
    updateDeleteModalMessage();
    document.getElementById('prevKabDeleteModal').style.display = 'flex';
}

function openBulkDeleteModal() {
    if (selectedIds.size === 0) {
        return;
    }

    deleteMode = 'bulk';
    deleteRecordId = null;
    pendingDeleteIds = Array.from(selectedIds);
    resetDeleteModalFields();
    updateDeleteModalMessage();
    document.getElementById('prevKabDeleteModal').style.display = 'flex';
}

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

/* ── Modal close ── */
document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('prevKabUploadModal').style.display = 'none';
        resetUploadModal();
    });
});

const deleteBackdrop = document.getElementById('prevKabDeleteModal');
const deleteCancelBtn = document.getElementById('prevKabDeleteCancelBtn');
const deleteConfirmInput = document.getElementById('prevKabDeleteConfirmInput');
const deleteConfirmBtn = document.getElementById('prevKabDeleteConfirmBtn');

if (deleteBackdrop) {
    deleteBackdrop.addEventListener('click', (e) => {
        if (e.target === deleteBackdrop) {
            closeDeleteModal();
        }
    });
}

if (deleteCancelBtn) {
    deleteCancelBtn.addEventListener('click', closeDeleteModal);
}

if (deleteConfirmInput) {
    deleteConfirmInput.addEventListener('input', syncDeleteConfirmButton);
}

if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', () => {
        if (deleteConfirmBtn.disabled) {
            return;
        }

        if ((deleteConfirmInput?.value?.trim() || '') !== 'Confirm') {
            const confirmError = document.getElementById('prevKabDeleteConfirmError');
            if (confirmError) {
                confirmError.textContent = 'Please type Confirm to delete this record.';
                confirmError.style.display = 'block';
            }
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const idsToDelete = [...pendingDeleteIds];
        const recordIdToDelete = deleteRecordId;
        const isBulkDelete = deleteMode === 'bulk' && idsToDelete.length > 0;
        closeDeleteModal();

        if (typeof window.showLoading === 'function') {
            window.showLoading(isBulkDelete ? 'Deleting records' : 'Deleting record');
        }

        const request = isBulkDelete
            ? fetch('/previous-kabataan/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids: idsToDelete.map((id) => parseInt(id, 10)),
                }),
            })
            : fetch(`/previous-kabataan/${recordIdToDelete}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

        request
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    idsToDelete.forEach((id) => selectedIds.delete(String(id)));
                    loadData();
                    showToast(
                        isBulkDelete
                            ? 'Selected records deleted successfully.'
                            : 'Record deleted successfully.',
                        'success'
                    );
                } else {
                    showToast('Failed to delete record. Please try again.', 'error');
                }
            })
            .catch(() => {
                showToast('Network error. Please try again.', 'error');
            })
            .finally(() => {
                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }

                deleteMode = 'single';
                deleteRecordId = null;
                pendingDeleteIds = [];
                resetDeleteModalFields();
            });
    });
}

const downloadSampleBtn = document.getElementById('prevKabDownloadSample');
if (downloadSampleBtn) {
    downloadSampleBtn.addEventListener('click', (event) => {
        event.preventDefault();
        downloadSampleTemplate();
    });
}

function downloadSampleTemplate() {
    import('xlsx').then((XLSX) => {
        const headers = [[
            'Last Name',
            'First Name',
            'Middle Name',
            'Suffix',
            'Age',
            'Birthday',
            'Sex',
            'Civil Status',
            'Youth Classification',
            'Youth Age Group',
            'Contact Number',
            'Home Address',
            'Highest Educational Attainment',
            'Work Status',
            'Registered Voter?',
            'Voted Last Election?',
            'Attended KK Assembly?',
            'If Yes, How Many Times?',
            'Barangay',
            'Region',
            'Province',
            'City/Municipality',
        ]];

        const worksheet = XLSX.utils.aoa_to_sheet(headers);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Previous Kabataan');
        XLSX.writeFile(workbook, 'previous-kabataan-template.xlsx');
    }).catch(() => {
        showToast('Failed to download sample template.', 'error');
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
                    lastName:            norm['last_name'] || norm['lastname'] || '',
                    firstName:           norm['first_name'] || norm['firstname'] || '',
                    middleName:          norm['middle_name'] || norm['middlename'] || '',
                    suffix:              norm['suffix'] || '',
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
            }).filter(r => r.lastName || r.firstName || r.name);

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
    // Hide upload progress indicator
    hideUploadProgress();
    // Reset button loading state
    if (confirmSaveBtn) setButtonLoading(confirmSaveBtn, false);
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
            <td>${cell(r.lastName)}</td>
            <td>${cell(r.firstName)}</td>
            <td>${cell(r.middleName)}</td>
            <td>${cell(r.suffix)}</td>
            <td>${cell(r.age)}</td>
            <td>${cell(r.birthday)}</td>
            <td>${cell(r.sex)}</td>
            <td>${cell(r.civilStatus)}</td>
            <td>${cell(r.youthClassification)}</td>
            <td>${cell(r.youthAgeGroup)}</td>
            <td>${cell(r.contact)}</td>
            <td>${cell(r.homeAddress || r.purokZone)}</td>
            <td>${cell(r.education)}</td>
            <td>${cell(r.workStatus)}</td>
            <td>${cell(r.registeredVoter)}</td>
            <td>${cell(r.votingHistory)}</td>
            <td>${cell(r.kkAssembly)}</td>
            <td>${cell(r.votingFrequency)}</td>
            <td>${cell(r.barangay)}</td>
            <td>${cell(r.region)}</td>
            <td>${cell(r.province)}</td>
            <td>${cell(r.city)}</td>
        </tr>
    `).join('');
}

/* ── Confirm Save ── */
if (confirmSaveBtn) {
    confirmSaveBtn.addEventListener('click', () => {
        if (!uploadedRows.length) return;

        showUploadProgress();
        updateUploadProgress(0, uploadedRows.length, true);

        if (typeof window.showLoading === 'function') {
            window.showLoading('Uploading records');
        }

        setButtonLoading(confirmSaveBtn, true, 'Confirm & Save');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const BATCH = 200;
        const batches = [];
        for (let i = 0; i < uploadedRows.length; i += BATCH) {
            batches.push(uploadedRows.slice(i, i + BATCH));
        }

        let totalSaved = 0;

        const sendBatch = (idx) => {
            if (idx >= batches.length) {
                hideUploadProgress();
                uploadModal.style.display = 'none';
                resetUploadModal();
                setButtonLoading(confirmSaveBtn, false);

                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }

                loadData();
                showToast(`Successfully replaced records with ${totalSaved} new record${totalSaved === 1 ? '' : 's'}.`, 'success');
                return;
            }

            const currentProgress = Math.min((idx + 1) * BATCH, uploadedRows.length);
            updateUploadProgress(currentProgress, uploadedRows.length);

            fetch('/previous-kabataan/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rows: batches[idx],
                    replace_existing: idx === 0,
                }),
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    totalSaved += res.saved;
                    sendBatch(idx + 1);
                } else {
                    hideUploadProgress();
                    setButtonLoading(confirmSaveBtn, false);

                    if (typeof window.hideLoading === 'function') {
                        window.hideLoading();
                    }

                    showToast('Upload failed. Please try again.', 'error');
                }
            })
            .catch(() => {
                hideUploadProgress();
                setButtonLoading(confirmSaveBtn, false);

                if (typeof window.hideLoading === 'function') {
                    window.hideLoading();
                }

                showToast('Network error. Please try again.', 'error');
            });
        };

        sendBatch(0);
    });
}

/* ── Toast Notification System ── */
function showToast(message, type = 'success', duration = 3000) {
    const toast = document.getElementById('prevKabToast');
    const msgEl = document.getElementById('prevKabToastMsg');
    if (!toast || !msgEl) {
        return;
    }

    msgEl.textContent = message;
    toast.className = 'prev-kab-toast prev-kab-toast-show' + (type === 'error' ? ' prev-kab-toast-error' : '');
    toast.style.display = 'flex';

    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => {
        toast.classList.remove('prev-kab-toast-show');
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }, duration);
}

/* ── Upload Progress Indicator Functions ── */
function showUploadProgress() {
    const progressContainer = document.getElementById('prevKabUploadProgress');
    const progressBar = document.getElementById('prevKabProgressBar');

    if (progressContainer) {
        progressContainer.style.display = 'block';
    }

    if (progressBar) {
        progressBar.classList.add('is-indeterminate');
    }
}

function hideUploadProgress() {
    const progressContainer = document.getElementById('prevKabUploadProgress');
    const progressBar = document.getElementById('prevKabProgressBar');

    if (progressContainer) {
        progressContainer.style.display = 'none';
    }

    if (progressBar) {
        progressBar.classList.remove('is-indeterminate');
    }
}

function updateUploadProgress(current, total, indeterminate = false) {
    const progressBar = document.getElementById('prevKabProgressBar');
    const progressBarFill = document.getElementById('prevKabProgressBarFill');
    const progressStatus = document.getElementById('prevKabProgressStatus');
    const progressPercentage = document.getElementById('prevKabProgressPercentage');
    const percentage = total > 0 ? Math.round((current / total) * 100) : 0;

    if (progressBar) {
        progressBar.classList.toggle('is-indeterminate', indeterminate || (current === 0 && total > 0));
    }

    if (progressBarFill) {
        progressBarFill.style.width = `${percentage}%`;
    }

    if (progressStatus) {
        progressStatus.textContent = `${current} / ${total} records uploaded`;
    }

    if (progressPercentage) {
        progressPercentage.textContent = `${percentage}%`;
    }
}

/* ── Button Loading State ── */
function setButtonLoading(button, isLoading, originalText = '') {
    if (!button) return;

    if (isLoading) {
        button.classList.add('loading');
        button.disabled = true;
        button.dataset.originalText = originalText || button.textContent;
    } else {
        button.classList.remove('loading');
        button.disabled = false;
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    }
}

/* ── Event Listeners ── */
if (searchInput)    searchInput.addEventListener('input', applyFilters);
if (yearFilter)     yearFilter.addEventListener('change', applyFilters);
if (purokFilter)    purokFilter.addEventListener('change', applyFilters);
if (voterFilter)    voterFilter.addEventListener('change', applyFilters);

const prevBtn = document.getElementById('prevKabPrevBtn');
const nextBtn = document.getElementById('prevKabNextBtn');
const pageInput = document.getElementById('prevKabPageInput');
const rowsPerPageSelect = document.getElementById('prevKabRowsPerPageSelect');

function goToPage(page) {
    const totalPages = getTotalPages();
    currentPage = Math.min(Math.max(1, page), totalPages);
    renderTable();
}

if (prevBtn) {
    prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
}

if (nextBtn) {
    nextBtn.addEventListener('click', () => goToPage(currentPage + 1));
}

if (pageInput) {
    pageInput.addEventListener('change', () => {
        const value = parseInt(pageInput.value, 10);
        if (!Number.isNaN(value)) {
            goToPage(value);
        }
    });
}

if (rowsPerPageSelect) {
    rowsPerPageSelect.addEventListener('change', () => {
        recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
        currentPage = 1;
        renderTable();
    });
}

if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', openBulkDeleteModal);
}

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', () => {
        if (!tableBody) {
            return;
        }

        const visibleCheckboxes = Array.from(tableBody.querySelectorAll('.prev-kab-row-checkbox'));
        visibleCheckboxes.forEach((checkbox) => {
            const id = checkbox.dataset.id;
            if (!id) {
                return;
            }

            if (selectAllCheckbox.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }

            checkbox.checked = selectAllCheckbox.checked;
        });

        updateBulkToolbar();
        syncSelectAllCheckbox();
    });
}

if (tableBody) {
    tableBody.addEventListener('change', (event) => {
        const checkbox = event.target.closest('.prev-kab-row-checkbox');
        if (!checkbox) {
            return;
        }

        const id = checkbox.dataset.id;
        if (!id) {
            return;
        }

        if (checkbox.checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }

        updateBulkToolbar();
        syncSelectAllCheckbox();
    });
}

/* ── Init ── */
initSortableHeaders();
loadData();
