// ── Scholar List (Approved Scholars) ───────────────────────────────────────

const SL_PAYMENT_STATUSES = ['Pending Release', 'Claimed', 'Unclaimed'];

function slNormalizePaymentStatus(scholar) {
    if (scholar.payment_status && SL_PAYMENT_STATUSES.includes(scholar.payment_status)) {
        return scholar.payment_status;
    }
    if (scholar.status === 'Paid') return 'Claimed';
    if (scholar.status === 'Pending Payout') return 'Pending Release';
    if (scholar.status === 'Cancelled') return 'Unclaimed';
    return 'Pending Release';
}

function slEnsurePaymentStatuses() {
    SL_SCHOLARS.forEach(s => {
        s.payment_status = slNormalizePaymentStatus(s);
    });
}

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
        payment_status: 'Claimed'
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
        payment_status: 'Pending Release'
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
        payment_status: 'Unclaimed'
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
        payment_status: 'Claimed'
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
        payment_status: 'Pending Release'
    },
];

let currentPage = 1;
let perPage = 25;
let activePaymentFilter = 'all';
let filteredScholars = [];

slEnsurePaymentStatuses();
filteredScholars = [...SL_SCHOLARS];

document.addEventListener('DOMContentLoaded', () => {
    updateSummaryCards();
    renderScholarTable();
    initializeExportButton();
    initializeModal();
    initializeFilters();
    initializePaymentFilterTabs();
    initializePagination();
    initializeEditModal();
});

function escapeSl(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function slPaymentBadgeClass(status) {
    if (status === 'Claimed') return 'sl-badge-claimed';
    if (status === 'Unclaimed') return 'sl-badge-unclaimed';
    return 'sl-badge-pending-release';
}

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
        const paymentStatus = slNormalizePaymentStatus(r);
        const statusBadge = `<span class="sl-badge ${slPaymentBadgeClass(paymentStatus)}">${escapeSl(paymentStatus)}</span>`;

        return `
        <tr>
            <td class="sl-td-name">${escapeSl(fullName)}</td>
            <td class="sl-td-center">${escapeSl(r.school_name || '—')}</td>
            <td class="sl-td-center">${escapeSl(r.year_level || '—')}</td>
            <td class="sl-td-center sl-td-program">${escapeSl(displayProgram)}</td>
            <td class="sl-td-center sl-td-purpose">${escapeSl(r.purpose || '—')}</td>
            <td class="sl-td-center">${escapeSl(r.approved_at || '—')}</td>
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
            if (s) openEditModal(idx, s);
        });
    });

    tbody.querySelectorAll('[data-scholar-delete]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-scholar-delete'), 10);
            if (!confirm('Remove this scholar from the list?')) return;
            const scholarToDelete = filteredScholars[idx];
            const originalIndex = SL_SCHOLARS.indexOf(scholarToDelete);
            if (originalIndex > -1) SL_SCHOLARS.splice(originalIndex, 1);
            applyFilters();
        });
    });
}

function initializeExportButton() {
    const exportBtn = document.getElementById('slExportCsvBtn');
    if (!exportBtn) return;
    exportBtn.addEventListener('click', () => exportToCsv(filteredScholars));
}

function exportToCsv(scholars) {
    if (scholars.length === 0) { alert('No scholars to export.'); return; }
    const headers = ['Full Name', 'School', 'Year/Level', 'Program/Strand', 'Purpose', 'Date Approved', 'Payment Status'];
    const rows = scholars.map(r => {
        const fullName = `${r.last_name || ''}, ${r.first_name || ''}${r.middle_name ? ' ' + r.middle_name.charAt(0) + '.' : ''}${r.suffix ? ' ' + r.suffix : ''}`;
        return [fullName, r.school_name || '', r.year_level || '', r.program_strand || '', r.purpose || '', r.approved_at || '', slNormalizePaymentStatus(r)];
    });
    let csv = headers.join(',') + '\n';
    rows.forEach(row => { csv += row.map(c => `"${c}"`).join(',') + '\n'; });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `approved_scholars_${new Date().toISOString().split('T')[0]}.csv`;
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function initializeModal() {
    const modal = document.getElementById('slViewModal');
    const closeBtn = document.getElementById('slViewClose');
    const maxBtn = document.getElementById('slViewMaximize');
    const modalBox = document.getElementById('slViewBox');

    const closeView = () => {
        modal.style.display = 'none';
        modalBox.classList.remove('sl-modal-maximized');
        modal.classList.remove('sl-overlay-maximized');
        if (maxBtn) {
            maxBtn.textContent = '□';
            maxBtn.title = 'Maximize';
        }
    };

    if (closeBtn) closeBtn.addEventListener('click', closeView);
    if (modal) {
        modal.addEventListener('click', e => {
            if (e.target === modal) closeView();
        });
    }
    if (maxBtn && modalBox) {
        maxBtn.addEventListener('click', () => {
            modalBox.classList.toggle('sl-modal-maximized');
            const isMax = modalBox.classList.contains('sl-modal-maximized');
            maxBtn.textContent = isMax ? '⧉' : '□';
            maxBtn.title = isMax ? 'Restore Down' : 'Maximize';
            if (modal) modal.classList.toggle('sl-overlay-maximized', isMax);
        });
    }
}

function openScholarModal(r) {
    const modal = document.getElementById('slViewModal');
    const body = document.getElementById('slViewBody');
    if (!modal || !body) return;

    const chk = (checked) => checked
        ? `<span class="sl-pdf-chk sl-pdf-chk-on"><span class="sl-pdf-chk-mark"></span></span>`
        : `<span class="sl-pdf-chk"></span>`;

    const purposes = [
        { label: 'Tuition Fees', key: 'Tuition Fees' },
        { label: 'Books/Equipments', key: 'Books / Equipments' },
        { label: 'Living Expenses', key: 'Living Expenses' },
        { label: 'Others', key: 'Others' },
    ];
    const purposeList = r.purpose_list || [];
    const purposeRows = purposes.map(p => {
        const isChecked = purposeList.some(v =>
            v.toLowerCase().replace(/[\s/]/g, '') === p.key.toLowerCase().replace(/[\s/]/g, '')
        );
        const extra = (p.key === 'Others' && r.purpose_others) ? ` (${r.purpose_others})` : '';
        return `<div class="sl-pdf-chk-row">${chk(isChecked)} <span>${p.label}${extra}</span></div>`;
    }).join('');

    const fullName = `${r.first_name || ''} ${r.last_name || ''}${r.suffix ? ' ' + r.suffix : ''}`.trim();
    const printedName = `${r.first_name || ''} ${r.middle_name ? r.middle_name.charAt(0) + '. ' : ''}${r.last_name || ''}${r.suffix ? ' ' + r.suffix : ''}`.trim();
    const paymentStatus = slNormalizePaymentStatus(r);

    body.innerHTML = `
    <div class="sl-pdf-wrap">
        <div class="sl-view-payment-banner">
            <span class="sl-badge ${slPaymentBadgeClass(paymentStatus)}">${escapeSl(paymentStatus)}</span>
            <span>Payment Status</span>
        </div>
        <div class="sl-pdf-header">
            <img src="/images/barangay_logo.png" alt="SK Logo" class="sl-pdf-logo" onerror="this.style.display='none'">
            <h2 class="sl-pdf-title">SCHOLARSHIP APPLICATION FORM</h2>
            <div class="sl-pdf-picbox">Picture Here</div>
        </div>
        <div class="sl-pdf-date-row">
            <span class="sl-pdf-label">Date:</span>
            <span class="sl-pdf-underline sl-pdf-val">${escapeSl(r.approved_at || '—')}</span>
        </div>
        <div class="sl-pdf-section-title">APPLICANT'S PERSONAL INFORMATION:</div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Last Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.last_name || '—')}</span>
            <span class="sl-pdf-label">First Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.first_name || '—')}</span>
            <span class="sl-pdf-label">Middle Name:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.middle_name || '—')}</span>
        </div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Date of Birth:</span>
            <span class="sl-pdf-underline sl-pdf-val">${escapeSl(r.date_of_birth || '—')}</span>
            <span class="sl-pdf-label">Gender:</span>
            <span class="sl-pdf-underline sl-pdf-val">${escapeSl(r.gender || '—')}</span>
            <span class="sl-pdf-label">Age:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-narrow">${escapeSl(r.age || '—')}</span>
            <span class="sl-pdf-label">Contact No:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.contact_no || '—')}</span>
        </div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Complete Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${escapeSl(r.address || '—')}</span>
        </div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Email Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.email || '—')}</span>
        </div>
        <div class="sl-pdf-section-title" style="margin-top:18px;">ACADEMIC INFORMATION:</div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Name of School:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${escapeSl(r.school_name || '—')}</span>
        </div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">School Address:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-full">${escapeSl(r.school_address || '—')}</span>
        </div>
        <div class="sl-pdf-row">
            <span class="sl-pdf-label">Year/Grade Level:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex1">${escapeSl(r.year_level || '—')}</span>
            <span class="sl-pdf-label">Program/Strand:</span>
            <span class="sl-pdf-underline sl-pdf-val sl-pdf-flex2">${escapeSl(r.program_strand || '—')}</span>
        </div>
        <div class="sl-pdf-two-col" style="margin-top:18px;">
            <div>
                <div class="sl-pdf-section-title">SCHOLARSHIP INFORMATION:</div>
                <div class="sl-pdf-sublabel" style="margin-bottom:8px;">Purpose of Scholarship:</div>
                ${purposeRows}
            </div>
            <div>
                <div class="sl-pdf-section-title">
                    SUBMITTED REQUIREMENTS:
                    <span class="sl-pdf-note">Note: To be filled out by SK officials</span>
                </div>
                <div class="sl-pdf-req-item">
                    <div class="sl-pdf-chk-row">${chk(r.cor_certified)}<span>COR – CERTIFIED TRUE COPY</span></div>
                </div>
                <div class="sl-pdf-req-item">
                    <div class="sl-pdf-chk-row">${chk(r.photo_id)}<span>PHOTO COPY OF ID (FRONT AND BACK)</span></div>
                </div>
            </div>
        </div>
        <div class="sl-pdf-sig-wrap">
            <div class="sl-pdf-sig-block">
                <div class="sl-pdf-sig-name">${escapeSl(fullName)}</div>
                <div class="sl-pdf-sig-line"></div>
                <div class="sl-pdf-sig-printed">${escapeSl(printedName)}</div>
                <div class="sl-pdf-sig-sublabel">Name and Signature of Participant</div>
            </div>
        </div>
    </div>`;

    modal.style.display = 'flex';
}

function slDownloadPlaceholderPdf(filename) {
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
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}
window.slDownloadPlaceholderPdf = slDownloadPlaceholderPdf;

function updateSummaryCards() {
    const total = filteredScholars.length;
    const pending = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Pending Release').length;
    const claimed = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Claimed').length;
    const unclaimed = filteredScholars.filter(s => slNormalizePaymentStatus(s) === 'Unclaimed').length;

    const elTotal = document.getElementById('slStatTotal');
    const elPending = document.getElementById('slStatPending');
    const elPaid = document.getElementById('slStatPaid');
    const elUnclaimed = document.getElementById('slStatUnclaimed');
    if (elTotal) elTotal.textContent = total;
    if (elPending) elPending.textContent = pending;
    if (elPaid) elPaid.textContent = claimed;
    if (elUnclaimed) elUnclaimed.textContent = unclaimed;
}

function initializePaymentFilterTabs() {
    document.querySelectorAll('.sl-payment-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sl-payment-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activePaymentFilter = this.dataset.paymentFilter || 'all';
            applyFilters();
        });
    });
}

function initializeFilters() {
    const yearFilter = document.getElementById('slYearFilter');
    const searchInput = document.getElementById('slSearchInput');
    if (yearFilter) yearFilter.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('input', applyFilters);
}

function applyFilters() {
    const yearFilter = document.getElementById('slYearFilter')?.value || '';
    const searchQuery = document.getElementById('slSearchInput')?.value.toLowerCase() || '';

    filteredScholars = SL_SCHOLARS.filter(scholar => {
        const paymentStatus = slNormalizePaymentStatus(scholar);
        if (activePaymentFilter !== 'all' && paymentStatus !== activePaymentFilter) {
            return false;
        }
        if (yearFilter && scholar.scholarship_year !== yearFilter) {
            return false;
        }
        if (searchQuery) {
            const fullName = `${scholar.last_name} ${scholar.first_name} ${scholar.middle_name}`.toLowerCase();
            const school = (scholar.school_name || '').toLowerCase();
            const program = (scholar.program_strand || '').toLowerCase();
            const payment = paymentStatus.toLowerCase();
            if (!fullName.includes(searchQuery) &&
                !school.includes(searchQuery) &&
                !program.includes(searchQuery) &&
                !payment.includes(searchQuery)) {
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

function initializePagination() {
    const perPageSelect = document.getElementById('slPerPage');
    const firstBtn = document.getElementById('slFirstPage');
    const prevBtn = document.getElementById('slPrevPage');
    const nextBtn = document.getElementById('slNextPage');
    const lastBtn = document.getElementById('slLastPage');

    if (perPageSelect) {
        perPageSelect.addEventListener('change', (e) => {
            perPage = parseInt(e.target.value, 10);
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

function getTotalPages() {
    return Math.ceil(filteredScholars.length / perPage) || 1;
}

function goToPage(page) {
    const totalPages = getTotalPages();
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderScholarTable();
    renderPagination();
}
window.goToPage = goToPage;

function renderPagination() {
    const totalPages = getTotalPages();
    const totalRecords = filteredScholars.length;
    const start = totalRecords === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, totalRecords);

    const showingStart = document.getElementById('slShowingStart');
    const showingEnd = document.getElementById('slShowingEnd');
    const totalEl = document.getElementById('slTotalRecords');
    if (showingStart) showingStart.textContent = start;
    if (showingEnd) showingEnd.textContent = end;
    if (totalEl) totalEl.textContent = totalRecords;

    const firstBtn = document.getElementById('slFirstPage');
    const prevBtn = document.getElementById('slPrevPage');
    const nextBtn = document.getElementById('slNextPage');
    const lastBtn = document.getElementById('slLastPage');
    if (firstBtn) firstBtn.disabled = currentPage === 1;
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    if (lastBtn) lastBtn.disabled = currentPage === totalPages;

    const pageNumbers = document.getElementById('slPageNumbers');
    if (!pageNumbers) return;

    let pages = [];
    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        pages.push(1);
        if (currentPage > 3) pages.push('...');
        for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
            pages.push(i);
        }
        if (currentPage < totalPages - 2) pages.push('...');
        pages.push(totalPages);
    }

    pageNumbers.innerHTML = pages.map(page => {
        if (page === '...') return '<span class="sl-page-ellipsis">...</span>';
        const isActive = page === currentPage ? 'active' : '';
        return `<button type="button" class="sl-page-num ${isActive}" onclick="goToPage(${page})">${page}</button>`;
    }).join('');
}

function openEditModal(index, scholar) {
    const modal = document.getElementById('slEditModal');
    const scholarName = `${scholar.last_name}, ${scholar.first_name}${scholar.middle_name ? ' ' + scholar.middle_name.charAt(0) + '.' : ''}${scholar.suffix ? ' ' + scholar.suffix : ''}`;

    document.getElementById('editScholarIndex').value = index;
    document.getElementById('editScholarName').value = scholarName;
    document.getElementById('editPaymentStatus').value = slNormalizePaymentStatus(scholar);

    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('slEditModal').style.display = 'none';
}

function saveEditStatus() {
    const index = parseInt(document.getElementById('editScholarIndex').value, 10);
    const paymentStatus = document.getElementById('editPaymentStatus').value;

    if (!SL_PAYMENT_STATUSES.includes(paymentStatus)) {
        alert('Please select a valid payment status.');
        return;
    }

    const scholar = filteredScholars[index];
    if (!scholar) {
        alert('Scholar not found.');
        return;
    }

    const originalIndex = SL_SCHOLARS.indexOf(scholar);
    if (originalIndex === -1) {
        alert('Scholar not found in original list.');
        return;
    }

    SL_SCHOLARS[originalIndex].payment_status = paymentStatus;
    applyFilters();
    closeEditModal();
}

function initializeEditModal() {
    const editModal = document.getElementById('slEditModal');
    const editClose = document.getElementById('slEditClose');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    const btnSaveEdit = document.getElementById('btnSaveEdit');

    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
    if (btnSaveEdit) btnSaveEdit.addEventListener('click', saveEditStatus);
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) closeEditModal();
        });
    }
}
