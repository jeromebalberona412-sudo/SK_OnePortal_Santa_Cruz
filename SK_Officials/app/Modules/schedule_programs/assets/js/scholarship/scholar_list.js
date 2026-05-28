// ── Scholar List Page JavaScript ──────────────────────────────────────────

// ── Sample Data — matches the reference image (5 scholars) ────────────────
const SL_SCHOLARS = [
    {
        last_name: 'Reyes', first_name: 'Maria', middle_name: 'Santos', suffix: '',
        date_of_birth: '03/14/2005', gender: 'Female', age: 20,
        contact_no: '09171234567', email: 'maria.reyes@email.com',
        address: '123 Sampaguita St., Brgy. Calios, Santa Cruz, Laguna',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '2nd Year',
        program_strand: 'Bachelor of Secondary Education (BSED)',
        program_abbr: 'BSED',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'], purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Jan 15, 2025',
        scholarship_year: '2026',
        status: 'Paid',
        cancellation_reason: ''
    },
    {
        last_name: 'Dela Cruz', first_name: 'Jose', middle_name: 'Ramos', suffix: 'Jr.',
        date_of_birth: '11/20/2004', gender: 'Male', age: 21,
        contact_no: '09721234567', email: 'jose.delacruz@email.com',
        address: '88 Magsaysay St., Brgy. Calios, Santa Cruz, Laguna',
        school_name: 'Laguna State Polytechnic University',
        school_address: 'Brgy. Siniloan, Siniloan, Laguna 4019',
        year_level: '3rd Year',
        program_strand: 'Bachelor of Science in Information Technology (BSIT)',
        program_abbr: 'BSIT',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'], purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Jan 25, 2025',
        scholarship_year: '2026',
        status: 'Pending Payout',
        cancellation_reason: ''
    },
    {
        last_name: 'Bautista', first_name: 'Kristine', middle_name: 'Flores', suffix: '',
        date_of_birth: '06/08/2005', gender: 'Female', age: 20,
        contact_no: '09831234567', email: 'kristine.bautista@email.com',
        address: '14 Quezon Blvd., Brgy. Calios, Santa Cruz, Laguna',
        school_name: 'De La Salle University – Dasmariñas',
        school_address: 'Brgy. Salitran, Dasmariñas, Cavite 4114',
        year_level: '2nd Year',
        program_strand: 'Bachelor of Science in Nursing (BSN)',
        program_abbr: 'BSN',
        purpose: 'Tuition Fees, Books / Equipments',
        purpose_list: ['Tuition Fees', 'Books / Equipments'], purpose_others: '',
        cor_certified: true, photo_id: true,
        approved_at: 'Feb 10, 2025',
        scholarship_year: '2025',
        status: 'Cancelled',
        cancellation_reason: 'Incomplete requirements'
    },
    {
        last_name: 'Santos', first_name: 'Mark', middle_name: 'Villanueva', suffix: '',
        date_of_birth: '09/15/2003', gender: 'Male', age: 22,
        contact_no: '09941234567', email: 'mark.santos@email.com',
        address: '22 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
        school_name: 'University of the Philippines Los Baños',
        school_address: 'Brgy. College, Los Baños, Laguna 4031',
        year_level: '4th Year',
        program_strand: 'Bachelor of Science in Computer Science (BSCS)',
        program_abbr: 'BS Computer Science',
        purpose: 'Tuition Fees, Living Expenses',
        purpose_list: ['Tuition Fees', 'Living Expenses'], purpose_others: '',
        cor_certified: true, photo_id: false,
        approved_at: 'Feb 20, 2025',
        scholarship_year: '2026',
        status: 'Paid',
        cancellation_reason: ''
    },
    {
        last_name: 'Lim', first_name: 'Angela', middle_name: 'Cruz', suffix: '',
        date_of_birth: '04/22/2007', gender: 'Female', age: 18,
        contact_no: '09051234567', email: 'angela.lim@email.com',
        address: '5 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        school_name: 'Santa Cruz National High School',
        school_address: 'Brgy. Poblacion, Santa Cruz, Laguna 4009',
        year_level: 'Grade 12',
        program_strand: 'Science, Technology, Engineering and Mathematics (STEM)',
        program_abbr: 'STEM',
        purpose: 'Books / Equipments',
        purpose_list: ['Books / Equipments'], purpose_others: '',
        cor_certified: false, photo_id: true,
        approved_at: 'Mar 5, 2025',
        scholarship_year: '2025',
        status: 'Pending Payout',
        cancellation_reason: ''
    },
];

// Pagination state
let currentPage = 1;
let perPage = 25;
let filteredScholars = [...SL_SCHOLARS];

document.addEventListener('DOMContentLoaded', () => {
    updateSummaryCards();
    renderScholarTable();
    initializeExportButton();
    initializeModal();
    initializeFilters();
    initializePagination();
});

function escapeSl(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Render Scholar Table ───────────────────────────────────────────────────
function renderScholarTable() {
    const tbody = document.getElementById('slTableBody');
    if (!tbody) return;

    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const paginatedScholars = filteredScholars.slice(start, end);

    if (paginatedScholars.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="sl-empty">No scholars found.</td></tr>`;
        return;
    }

    tbody.innerHTML = paginatedScholars.map((r, i) => {
        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        const displayProgram = r.program_strand || '—';
        const actualIndex = start + i;
        
        // Status badge with different colors
        let statusBadge = '';
        let statusClass = '';
        let statusText = r.status || 'Unknown';
        
        switch(r.status) {
            case 'Paid':
                statusClass = 'sl-badge-paid';
                break;
            case 'Pending Payout':
                statusClass = 'sl-badge-pending';
                break;
            case 'Cancelled':
                statusClass = 'sl-badge-cancelled';
                statusText = r.cancellation_reason ? `Cancelled: ${r.cancellation_reason}` : 'Cancelled';
                break;
            default:
                statusClass = 'sl-badge-default';
        }
        
        statusBadge = `<span class="sl-badge ${statusClass}" title="${statusText}">${r.status || 'Unknown'}</span>`;
        
        return `
        <tr>
            <td class="sl-td-name">${fullName}</td>
            <td class="sl-td-center">${r.school_name || '—'}</td>
            <td class="sl-td-center">${r.year_level || '—'}</td>
            <td class="sl-td-center sl-td-program">${displayProgram}</td>
            <td class="sl-td-center sl-td-purpose">${r.purpose || '—'}</td>
            <td class="sl-td-center">${r.approved_at || '—'}</td>
            <td class="sl-td-center">${statusBadge}</td>
            <td class="sl-td-center sl-actions">
                <div class="prog-tbl-actions">
                    <button type="button" class="prog-btn prog-btn-view" data-scholar-idx="${actualIndex}">View</button>
                    <button type="button" class="prog-btn prog-btn-edit" data-scholar-edit="${actualIndex}">Edit</button>
                    <button type="button" class="prog-btn prog-btn-delete" data-scholar-delete="${actualIndex}">Delete</button>
                </div>
            </td>
        </tr>`;
    }).join('');

    tbody.querySelectorAll('button[data-scholar-idx]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-scholar-idx'), 10);
            if (filteredScholars[idx]) openScholarModal(filteredScholars[idx]);
        });
    });

    tbody.querySelectorAll('[data-scholar-edit]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-scholar-edit'), 10);
            const s = filteredScholars[idx];
            if (!s) return;
            openEditModal(idx, s);
        });
    });

    tbody.querySelectorAll('[data-scholar-delete]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-scholar-delete'), 10);
            if (!confirm('Remove this scholar from the list?')) return;
            const scholarToDelete = filteredScholars[idx];
            const originalIndex = SL_SCHOLARS.indexOf(scholarToDelete);
            if (originalIndex > -1) {
                SL_SCHOLARS.splice(originalIndex, 1);
            }
            applyFilters();
        });
    });
}

// ── Export to CSV ──────────────────────────────────────────────────────────
function initializeExportButton() {
    const exportBtn = document.getElementById('slExportCsvBtn');
    if (!exportBtn) return;
    exportBtn.addEventListener('click', () => exportToCsv(SL_SCHOLARS));
}

function exportToCsv(scholars) {
    if (scholars.length === 0) { alert('No scholars to export.'); return; }
    const headers = ['Full Name', 'School', 'Year/Level', 'Program/Strand', 'Purpose', 'Date Approved', 'Status'];
    const rows = scholars.map(r => {
        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return [fullName, r.school_name || '', r.year_level || '', r.program_strand || '', r.purpose || '', r.approved_at || '', 'Passed'];
    });
    let csv = headers.join(',') + '\n';
    rows.forEach(row => { csv += row.map(c => `"${c}"`).join(',') + '\n'; });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `scholar_list_${new Date().toISOString().split('T')[0]}.csv`;
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ── Modal ──────────────────────────────────────────────────────────────────
function initializeModal() {
    const modal      = document.getElementById('slViewModal');
    const closeBtn   = document.getElementById('slViewClose');
    const maxBtn     = document.getElementById('slViewMaximize');
    const modalBox   = document.getElementById('slViewBox');

    if (closeBtn)  closeBtn.addEventListener('click',  () => {
        modal.style.display = 'none';
        modalBox.classList.remove('sl-modal-maximized');
        modal.classList.remove('sl-overlay-maximized');
        maxBtn.textContent = '□';
        maxBtn.title = 'Maximize';
    });
    if (modal)     modal.addEventListener('click', e => {
        if (e.target === modal) {
            modal.style.display = 'none';
            modalBox.classList.remove('sl-modal-maximized');
            modal.classList.remove('sl-overlay-maximized');
            maxBtn.textContent = '□';
            maxBtn.title = 'Maximize';
        }
    });
    if (maxBtn && modalBox) {
        maxBtn.addEventListener('click', () => {
            modalBox.classList.toggle('sl-modal-maximized');
            const isMax = modalBox.classList.contains('sl-modal-maximized');
            maxBtn.textContent = isMax ? '⧉' : '□';
            maxBtn.title = isMax ? 'Restore Down' : 'Maximize';
            // Toggle overlay padding so modal truly fills the screen
            if (modal) modal.classList.toggle('sl-overlay-maximized', isMax);
        });
    }
}

// ── Open Scholar Modal — PDF Form Layout ──────────────────────────────────
function openScholarModal(r) {
    const modal = document.getElementById('slViewModal');
    const body  = document.getElementById('slViewBody');
    if (!modal || !body) return;

    // Helper: checkbox HTML
    const chk = (checked) => checked
        ? `<span class="sl-pdf-chk sl-pdf-chk-on"><span class="sl-pdf-chk-mark"></span></span>`
        : `<span class="sl-pdf-chk"></span>`;

    // Purpose checkboxes
    const purposes = [
        { label: 'Tuition Fees',    key: 'Tuition Fees' },
        { label: 'Books/Equipments', key: 'Books / Equipments' },
        { label: 'Living Expenses', key: 'Living Expenses' },
        { label: 'Others',          key: 'Others' },
    ];
    const purposeList = r.purpose_list || [];
    const purposeRows = purposes.map(p => {
        const isChecked = purposeList.some(v =>
            v.toLowerCase().replace(/[\s/]/g, '') === p.key.toLowerCase().replace(/[\s/]/g, '')
        );
        const extra = (p.key === 'Others' && r.purpose_others) ? ` (${r.purpose_others})` : '';
        return `<div class="sl-pdf-chk-row">${chk(isChecked)} <span>${p.label}${extra}</span></div>`;
    }).join('');

    // Cursive signature above line — First Last (no middle initial, like a real signature)
    const fullName = `${r.first_name || ''} ${r.last_name || ''}${r.suffix ? ' ' + r.suffix : ''}`.trim();
    // Printed name below line — First MI. Last
    const printedName = `${r.first_name || ''} ${r.middle_name ? r.middle_name.charAt(0) + '. ' : ''}${r.last_name || ''}${r.suffix ? ' ' + r.suffix : ''}`.trim();

    body.innerHTML = `
    <div class="sl-pdf-wrap">

        <!-- ── Header ── -->
        <div class="sl-pdf-header">
            <img src="/images/barangay_logo.png" alt="SK Logo" class="sl-pdf-logo"
                 onerror="this.style.display='none'">
            <h2 class="sl-pdf-title">SCHOLARSHIP APPLICATION FORM</h2>
            <div class="sl-pdf-picbox">Picture Here</div>
        </div>

        <!-- ── Date ── -->
        <div class="sl-pdf-date-row">
            <span class="sl-pdf-label">Date:</span>
            <span class="sl-pdf-underline sl-pdf-val">${r.approved_at || '—'}</span>
        </div>

        <!-- ── APPLICANT'S PERSONAL INFORMATION ── -->
        <div class="sl-pdf-section-title">APPLICANT'S PERSONAL INFORMATION:</div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Last Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.last_name || '—'}</span>
            <span class="sl-pdf-label">First Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.first_name || '—'}</span>
            <span class="sl-pdf-label">Middle Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.middle_name || '—'}</span>
        </div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Date of Birth:</span>
            <span class="sl-pdf-underline sl-pdf-val">${r.date_of_birth || '—'}</span>
            <span class="sl-pdf-label">Gender:</span>
            <span class="sl-pdf-underline sl-pdf-val">${r.gender || '—'}</span>
            <span class="sl-pdf-label">Age:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-narrow">${r.age || '—'}</span>
            <span class="sl-pdf-label">Contact No:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.contact_no || '—'}</span>
        </div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Complete Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${r.address || '—'}</span>
        </div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Email Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.email || '—'}</span>
        </div>

        <!-- ── ACADEMIC INFORMATION ── -->
        <div class="sl-pdf-section-title" style="margin-top:18px;">ACADEMIC INFORMATION:</div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Name of School:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${r.school_name || '—'}</span>
        </div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">School Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${r.school_address || '—'}</span>
        </div>

        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Year/Grade Level:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${r.year_level || '—'}</span>
            <span class="sl-pdf-label">Program/Strand:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex2">${r.program_strand || '—'}</span>
        </div>

        <!-- ── SCHOLARSHIP + REQUIREMENTS (2-col) ── -->
        <div class="sl-pdf-two-col" style="margin-top:18px;">

            <!-- Left: Scholarship Info -->
            <div>
                <div class="sl-pdf-section-title">SCHOLARSHIP INFORMATION:</div>
                <div class="sl-pdf-sublabel" style="margin-bottom:8px;">Purpose of Scholarship:</div>
                ${purposeRows}
            </div>

            <!-- Right: Submitted Requirements -->
            <div>
                <div class="sl-pdf-section-title">
                    SUBMITTED REQUIREMENTS:
                    <span class="sl-pdf-note">Note: To be filled out by SK officials</span>
                </div>
                <div class="sl-pdf-req-item">
                    <div class="sl-pdf-chk-row">
                        ${chk(r.cor_certified)}
                        <span>COR – CERTIFIED TRUE COPY</span>
                    </div>
                    ${r.cor_certified ? `
                    <button class="sl-pdf-view-btn" onclick="slDownloadPlaceholderPdf('COR_${(r.last_name || 'scholar').replace(/\s/g,'_')}.pdf')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View PDF — COR
                    </button>` : ''}
                </div>
                <div class="sl-pdf-req-item">
                    <div class="sl-pdf-chk-row">
                        ${chk(r.photo_id)}
                        <span>PHOTO COPY OF ID (FRONT AND BACK)</span>
                    </div>
                    ${r.photo_id ? `
                    <button class="sl-pdf-view-btn" onclick="slDownloadPlaceholderPdf('PhotoID_${(r.last_name || 'scholar').replace(/\s/g,'_')}.pdf')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        View PDF — Photo ID
                    </button>` : ''}
                </div>
            </div>

        </div>

        <!-- ── Signature ── -->
        <div class="sl-pdf-sig-wrap">
            <div class="sl-pdf-sig-block">
                <div class="sl-pdf-sig-name">${fullName}</div>
                <div class="sl-pdf-sig-line"></div>
                <div class="sl-pdf-sig-printed">${printedName}</div>
                <div class="sl-pdf-sig-sublabel">Name and Signature of Participant</div>
            </div>
        </div>

    </div>
    `;

    modal.style.display = 'flex';
}

// ── Download placeholder PDF ───────────────────────────────────────────────
function slDownloadPlaceholderPdf(filename) {
    // Build a minimal valid PDF blob so the browser triggers a real download
    const pdfContent = `%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer<</Size 4/Root 1 0 R>>
startxref
190
%%EOF`;
    const blob = new Blob([pdfContent], { type: 'application/pdf' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}


// ── Update Summary Cards ──────────────────────────────────────────────────
function updateSummaryCards() {
    const total = filteredScholars.length;
    const pending = filteredScholars.filter(s => s.status === 'Pending Payout').length;
    const paid = filteredScholars.filter(s => s.status === 'Paid').length;
    const cancelled = filteredScholars.filter(s => s.status === 'Cancelled').length;

    document.getElementById('slStatTotal').textContent = total;
    document.getElementById('slStatPending').textContent = pending;
    document.getElementById('slStatPaid').textContent = paid;
    document.getElementById('slStatCancelled').textContent = cancelled;
}

// ── Initialize Filters ─────────────────────────────────────────────────────
function initializeFilters() {
    const yearFilter = document.getElementById('slYearFilter');
    const searchInput = document.getElementById('slSearchInput');

    if (yearFilter) {
        yearFilter.addEventListener('change', applyFilters);
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
}

// ── Apply Filters ──────────────────────────────────────────────────────────
function applyFilters() {
    const yearFilter = document.getElementById('slYearFilter')?.value || '';
    const searchQuery = document.getElementById('slSearchInput')?.value.toLowerCase() || '';

    filteredScholars = SL_SCHOLARS.filter(scholar => {
        // Year filter
        if (yearFilter && scholar.scholarship_year !== yearFilter) {
            return false;
        }

        // Search filter
        if (searchQuery) {
            const fullName = `${scholar.last_name} ${scholar.first_name} ${scholar.middle_name}`.toLowerCase();
            const school = (scholar.school_name || '').toLowerCase();
            const program = (scholar.program_strand || '').toLowerCase();
            
            if (!fullName.includes(searchQuery) && 
                !school.includes(searchQuery) && 
                !program.includes(searchQuery)) {
                return false;
            }
        }

        return true;
    });

    currentPage = 1;
    updateSummaryCards();
    renderScholarTable();
    renderPagination();
}

// ── Initialize Pagination ──────────────────────────────────────────────────
function initializePagination() {
    const perPageSelect = document.getElementById('slPerPage');
    const firstBtn = document.getElementById('slFirstPage');
    const prevBtn = document.getElementById('slPrevPage');
    const nextBtn = document.getElementById('slNextPage');
    const lastBtn = document.getElementById('slLastPage');

    if (perPageSelect) {
        perPageSelect.addEventListener('change', (e) => {
            perPage = parseInt(e.target.value);
            currentPage = 1;
            renderScholarTable();
            renderPagination();
        });
    }

    if (firstBtn) firstBtn.addEventListener('click', () => goToPage(1));
    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));
    if (lastBtn) lastBtn.addEventListener('click', () => goToPage(getTotalPages()));

    renderPagination();
}

// ── Get Total Pages ────────────────────────────────────────────────────────
function getTotalPages() {
    return Math.ceil(filteredScholars.length / perPage) || 1;
}

// ── Go To Page ─────────────────────────────────────────────────────────────
function goToPage(page) {
    const totalPages = getTotalPages();
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderScholarTable();
    renderPagination();
}

// ── Render Pagination ──────────────────────────────────────────────────────
function renderPagination() {
    const totalPages = getTotalPages();
    const totalRecords = filteredScholars.length;
    const start = totalRecords === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, totalRecords);

    // Update info
    document.getElementById('slShowingStart').textContent = start;
    document.getElementById('slShowingEnd').textContent = end;
    document.getElementById('slTotalRecords').textContent = totalRecords;

    // Update buttons
    document.getElementById('slFirstPage').disabled = currentPage === 1;
    document.getElementById('slPrevPage').disabled = currentPage === 1;
    document.getElementById('slNextPage').disabled = currentPage === totalPages;
    document.getElementById('slLastPage').disabled = currentPage === totalPages;

    // Render page numbers
    const pageNumbers = document.getElementById('slPageNumbers');
    if (!pageNumbers) return;

    let pages = [];
    
    if (totalPages <= 7) {
        // Show all pages
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        // Show first, last, current and surrounding pages
        pages.push(1);
        
        if (currentPage > 3) {
            pages.push('...');
        }
        
        for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
            pages.push(i);
        }
        
        if (currentPage < totalPages - 2) {
            pages.push('...');
        }
        
        pages.push(totalPages);
    }

    pageNumbers.innerHTML = pages.map(page => {
        if (page === '...') {
            return '<span class="sl-page-ellipsis">...</span>';
        }
        const isActive = page === currentPage ? 'active' : '';
        return `<button type="button" class="sl-page-num ${isActive}" onclick="goToPage(${page})">${page}</button>`;
    }).join('');
}

// Make goToPage globally accessible
window.goToPage = goToPage;


// ── Edit Status Modal ──────────────────────────────────────────────────────
function openEditModal(index, scholar) {
    const modal = document.getElementById('slEditModal');
    const scholarName = `${scholar.last_name}, ${scholar.first_name}${scholar.middle_name ? ' ' + scholar.middle_name.charAt(0) + '.' : ''}${scholar.suffix ? ' ' + scholar.suffix : ''}`;
    
    document.getElementById('editScholarIndex').value = index;
    document.getElementById('editScholarName').value = scholarName;
    document.getElementById('editStatus').value = scholar.status || '';
    document.getElementById('editCancelReason').value = '';
    document.getElementById('editOtherReason').value = '';
    
    // Show/hide cancellation reason fields based on current status
    toggleCancelReasonFields(scholar.status);
    
    // If already cancelled, populate the reason
    if (scholar.status === 'Cancelled' && scholar.cancellation_reason) {
        const predefinedReasons = [
            'Incomplete requirements',
            'Failed to meet criteria',
            'Duplicate application',
            'Student request',
            'Budget constraints'
        ];
        
        if (predefinedReasons.includes(scholar.cancellation_reason)) {
            document.getElementById('editCancelReason').value = scholar.cancellation_reason;
        } else {
            document.getElementById('editCancelReason').value = 'Other';
            document.getElementById('editOtherReason').value = scholar.cancellation_reason;
            document.getElementById('otherReasonField').style.display = 'block';
        }
    }
    
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('slEditModal').style.display = 'none';
}

function toggleCancelReasonFields(status) {
    const cancelReasonField = document.getElementById('cancelReasonField');
    const otherReasonField = document.getElementById('otherReasonField');
    
    if (status === 'Cancelled') {
        cancelReasonField.style.display = 'block';
    } else {
        cancelReasonField.style.display = 'none';
        otherReasonField.style.display = 'none';
        document.getElementById('editCancelReason').value = '';
        document.getElementById('editOtherReason').value = '';
    }
}

function saveEditStatus() {
    const index = parseInt(document.getElementById('editScholarIndex').value);
    const status = document.getElementById('editStatus').value;
    const cancelReason = document.getElementById('editCancelReason').value;
    const otherReason = document.getElementById('editOtherReason').value.trim();
    
    if (!status) {
        alert('Please select a status.');
        return;
    }
    
    if (status === 'Cancelled') {
        if (!cancelReason) {
            alert('Please select a cancellation reason.');
            return;
        }
        
        if (cancelReason === 'Other' && !otherReason) {
            alert('Please specify the cancellation reason.');
            return;
        }
    }
    
    // Find the scholar in the filtered list
    const scholar = filteredScholars[index];
    if (!scholar) {
        alert('Scholar not found.');
        return;
    }
    
    // Find the scholar in the original list
    const originalIndex = SL_SCHOLARS.indexOf(scholar);
    if (originalIndex === -1) {
        alert('Scholar not found in original list.');
        return;
    }
    
    // Update status
    SL_SCHOLARS[originalIndex].status = status;
    
    // Update cancellation reason
    if (status === 'Cancelled') {
        SL_SCHOLARS[originalIndex].cancellation_reason = cancelReason === 'Other' ? otherReason : cancelReason;
    } else {
        SL_SCHOLARS[originalIndex].cancellation_reason = '';
    }
    
    // Refresh the display
    applyFilters();
    closeEditModal();
}

// ── Initialize Edit Modal Event Listeners ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    // Edit modal elements
    const editModal = document.getElementById('slEditModal');
    const editClose = document.getElementById('slEditClose');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    const btnSaveEdit = document.getElementById('btnSaveEdit');
    const editStatus = document.getElementById('editStatus');
    const editCancelReason = document.getElementById('editCancelReason');

    // Close button listeners
    if (editClose) {
        editClose.addEventListener('click', closeEditModal);
    }

    if (btnCancelEdit) {
        btnCancelEdit.addEventListener('click', closeEditModal);
    }

    // Save button listener
    if (btnSaveEdit) {
        btnSaveEdit.addEventListener('click', saveEditStatus);
    }

    // Status change listener
    if (editStatus) {
        editStatus.addEventListener('change', function() {
            toggleCancelReasonFields(this.value);
        });
    }
    
    // Cancel reason change listener
    if (editCancelReason) {
        editCancelReason.addEventListener('change', function() {
            const otherReasonField = document.getElementById('otherReasonField');
            if (this.value === 'Other') {
                otherReasonField.style.display = 'block';
            } else {
                otherReasonField.style.display = 'none';
                document.getElementById('editOtherReason').value = '';
            }
        });
    }

    // Click outside to close
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    }
});
