// ── Scholar List (Approved Scholars) ───────────────────────────────────────

const SL_PAYMENT_STATUSES = ['Claimed', 'Unclaimed'];

function slNormalizePaymentStatus(scholar) {
    if (scholar.payment_status && SL_PAYMENT_STATUSES.includes(scholar.payment_status)) {
        return scholar.payment_status;
    }
    if (scholar.status === 'Paid') return 'Claimed';
    if (scholar.status === 'Pending Payout') return 'Unclaimed';
    if (scholar.status === 'Cancelled') return 'Unclaimed';
    return 'Unclaimed';
}

function slEnsurePaymentStatuses() {
    SL_SCHOLARS.forEach(s => {
        s.payment_status = slNormalizePaymentStatus(s);
    });
}

const SL_SCHOLARS = [
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
        payment_status: 'Unclaimed'
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
        payment_status: 'Unclaimed'
    },
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
];

let currentPage = 1;
let perPage = 10;
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
    return 'sl-badge-default';
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
                    <button type="button" class="prog-btn prog-btn-revoke" data-scholar-revoke="${actualIndex}" style="background-color:#ef4444;color:#fff;border:none;">Revoke</button>
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

    tbody.querySelectorAll('[data-scholar-revoke]').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = parseInt(this.getAttribute('data-scholar-revoke'), 10);
            const s = filteredScholars[idx];
            if (s) openRevokeModal(idx, s);
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

    // Revoke Approval Modal handlers
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeClose = document.getElementById('slRevokeClose');
    const revokeCancel = document.getElementById('btnCancelRevoke');
    const revokeConfirm = document.getElementById('btnConfirmRevoke');
    const revokeMaximize = document.getElementById('slRevokeMaximize');
    const revokeBox = document.getElementById('slRevokeBox');
    const revokeOtherRadio = document.getElementById('slRevokeOtherRadio');
    const revokeReasonField = document.getElementById('slRevokeReasonField');
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeReasonCount = document.getElementById('revokeReasonCount');

    const closeRevoke = () => {
        revokeModal.style.display = 'none';
        revokeBox.classList.remove('sl-modal-maximized');
        revokeModal.classList.remove('sl-overlay-maximized');
        if (revokeMaximize) {
            revokeMaximize.textContent = '□';
            revokeMaximize.title = 'Maximize';
        }
    };

    if (revokeClose) revokeClose.addEventListener('click', closeRevoke);
    if (revokeCancel) revokeCancel.addEventListener('click', closeRevoke);
    if (revokeModal) {
        revokeModal.addEventListener('click', (e) => {
            if (e.target === revokeModal) closeRevoke();
        });
    }

    // Maximize/Restore for Revoke modal
    if (revokeMaximize && revokeBox) {
        revokeMaximize.addEventListener('click', () => {
            revokeBox.classList.toggle('sl-modal-maximized');
            const isMax = revokeBox.classList.contains('sl-modal-maximized');
            revokeMaximize.textContent = isMax ? '⧉' : '□';
            revokeMaximize.title = isMax ? 'Restore Down' : 'Maximize';
            if (revokeModal) revokeModal.classList.toggle('sl-overlay-maximized', isMax);
        });
    }

    // Other radio button handler
    if (revokeOtherRadio && revokeReasonField) {
        revokeOtherRadio.addEventListener('change', function () {
            if (this.checked) {
                revokeReasonField.style.display = 'block';
            } else {
                revokeReasonField.style.display = 'none';
                if (revokeReasonInput) revokeReasonInput.value = '';
                if (revokeReasonCount) revokeReasonCount.textContent = '0';
            }
        });
    }

    // Character counter for revoke reason
    if (revokeReasonInput && revokeReasonCount) {
        revokeReasonInput.addEventListener('input', function () {
            revokeReasonCount.textContent = this.value.length;
        });
    }

    if (revokeConfirm) {
        revokeConfirm.addEventListener('click', confirmRevokeApproval);
    }
}

function initializeExportButton() {
    const exportBtn = document.getElementById('slExportCsvBtn');
    if (!exportBtn) return;
    exportBtn.addEventListener('click', () => exportToCsv(filteredScholars));
}

function openRevokeModal(idx, scholar) {
    const revokeModal = document.getElementById('slRevokeModal');
    const revokeNameInput = document.getElementById('revokeScholarName');
    const revokeIndexInput = document.getElementById('revokeScholarIndex');
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeReasonCount = document.getElementById('revokeReasonCount');
    const revokeReasonField = document.getElementById('slRevokeReasonField');
    const revokeOtherRadio = document.getElementById('slRevokeOtherRadio');

    if (!revokeModal) return;

    const fullName = `${scholar.last_name || ''}, ${scholar.first_name || ''}${scholar.middle_name ? ' ' + scholar.middle_name.charAt(0) + '.' : ''}`;

    if (revokeNameInput) revokeNameInput.value = fullName;
    if (revokeIndexInput) revokeIndexInput.value = idx;
    if (revokeReasonInput) revokeReasonInput.value = '';
    if (revokeReasonCount) revokeReasonCount.textContent = '0';
    if (revokeReasonField) revokeReasonField.style.display = 'none';

    // Reset radio buttons to default (Mistakenly Approved)
    const radioButtons = document.querySelectorAll('input[name="revokeReason"]');
    radioButtons.forEach(rb => {
        if (rb.value === 'Mistakenly Approved') {
            rb.checked = true;
        } else {
            rb.checked = false;
        }
    });

    revokeModal.style.display = 'flex';
}

function closeRevokeModal() {
    const revokeModal = document.getElementById('slRevokeModal');
    if (revokeModal) revokeModal.style.display = 'none';
}

function confirmRevokeApproval() {
    const revokeIndexInput = document.getElementById('revokeScholarIndex');
    const revokeReasonInput = document.getElementById('revokeReason');
    const revokeOtherRadio = document.getElementById('slRevokeOtherRadio');

    if (!revokeIndexInput) return;

    const idx = parseInt(revokeIndexInput.value, 10);
    let reason = '';

    // Get selected reason from radio buttons
    const selectedRadio = document.querySelector('input[name="revokeReason"]:checked');
    if (selectedRadio) {
        if (selectedRadio.value === 'other') {
            reason = revokeReasonInput ? revokeReasonInput.value.trim() : '';
            if (!reason) {
                alert('Please enter a revocation reason when selecting "Other Reason".');
                return;
            }
        } else {
            reason = selectedRadio.value;
        }
    }

    if (!reason) {
        alert('Please select a revocation reason.');
        return;
    }

    const scholarToRevoke = filteredScholars[idx];
    if (!scholarToRevoke) return;

    // Find the scholar in the main SL_SCHOLARS array
    const originalIndex = SL_SCHOLARS.indexOf(scholarToRevoke);
    if (originalIndex === -1) return;

    // Update the scholar's status to Rejected and add revocation reason
    SL_SCHOLARS[originalIndex].status = 'Rejected';
    SL_SCHOLARS[originalIndex].revocation_reason = reason;
    SL_SCHOLARS[originalIndex].revoked_at = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

    // Save to localStorage
    localStorage.setItem('sl_scholars', JSON.stringify(SL_SCHOLARS));

    // Close modal and refresh table
    closeRevokeModal();
    applyFilters();

    // Show success message
    alert(`Scholar approval has been revoked. The record has been moved to Rejected Scholars.`);
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

    const fullName = `${r.first_name || ''} ${r.last_name || ''}${r.suffix ? ' ' + r.suffix : ''}`.trim();
    const initials = `${(r.first_name || '').charAt(0)}${(r.last_name || '').charAt(0)}`.toUpperCase();
    const paymentStatus = slNormalizePaymentStatus(r);

    const purposeText = r.purpose || (Array.isArray(r.purpose_list) ? r.purpose_list.join(', ') : '—');
    const reqList = [];
    if (r.cor_certified) reqList.push('COR – CERTIFIED TRUE COPY');
    if (r.photo_id) reqList.push('PHOTO COPY OF ID (FRONT AND BACK)');

    const SV = window.ScholarshipViewShared;
    const esc = (s) => (SV ? SV.escapeHtml(s) : String(s ?? ''));
    const program = SV ? SV.loadScholarshipProgram() : null;
    const programHtml = SV ? SV.renderProgramInformationSection(program) : '';
    const formAnswersHtml = SV ? SV.renderFormAnswersSection(r, program) : '';

    const statusStyle = paymentStatus === 'Claimed' 
        ? { bg: '#dcfce7', text: '#166534', label: 'Claimed' }
        : paymentStatus === 'Pending Release'
        ? { bg: '#fef3c7', text: '#92400e', label: 'Pending Release' }
        : { bg: '#fee2e2', text: '#991b1b', label: 'Unclaimed' };

    body.innerHTML = `
        <div style="padding:24px;background:#f0f1f5;">
            <!-- Personal Information -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Personal Information
                </h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Date of Birth</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.date_of_birth) || 'Not specified'}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Gender</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.gender) || 'Not specified'}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Age</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.age) || 'Not specified'}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Contact Number</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.contact_no) || 'Not specified'}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Address</label>
                        <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${esc(r.address) || 'Not specified'}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Email Address</label>
                        <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.email) || 'Not specified'}</div>
                    </div>
                </div>
            </div>

            <!-- Education & Scholarship -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/></svg>
                    Education & Scholarship
                </h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">School Name</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.school_name) || 'Not specified'}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">School Address</label>
                        <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${esc(r.school_address) || 'Not specified'}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Year Level</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.year_level) || 'Not specified'}</div>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program / Strand</label>
                        <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${esc(r.program_strand) || 'Not specified'}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Purpose of Application</label>
                        <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${purposeText || 'Not specified'}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Submitted Requirements</label>
                        <div style="background:#f9fafb;border-radius:8px;padding:16px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                            ${reqList.length > 0 ? reqList.map(req => `
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span style="font-size:14px;color:#111827;">${req}</span>
                                </div>
                            `).join('') : '<span style="font-size:14px;color:#9ca3af;">No requirements submitted</span>'}
                        </div>
                    </div>
                </div>
            </div>

            ${programHtml}

            ${formAnswersHtml}

            <!-- Payment Status -->
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    Payment Status
                </h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Current Status</label>
                        <span style="display:inline-flex;align-items:center;padding:8px 20px;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:${statusStyle.bg};color:${statusStyle.text};box-shadow:0 1px 2px rgba(0,0,0,0.1);">${statusStyle.label}</span>
                    </div>
                    <div>
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Date Approved</label>
                        <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${esc(r.approved_at) || 'Not specified'}</div>
                    </div>
                </div>
            </div>

            <!-- Applicant summary -->
            <div style="background:white;border-radius:12px;padding:20px 24px;box-shadow:0 1px 3px rgba(0,0,0,0.1);display:flex;align-items:center;gap:16px;border-top:3px solid #213F99;">
                <div style="width:56px;height:56px;background:#e8eef9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#213F99;flex-shrink:0;">${initials}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:4px;">${fullName}</div>
                    <div style="font-size:14px;color:#6b7280;">${esc(program?.programName || 'Scholarship Program')}</div>
                </div>
                <span style="display:inline-flex;align-items:center;padding:8px 20px;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:${statusStyle.bg};color:${statusStyle.text};box-shadow:0 1px 2px rgba(0,0,0,0.1);flex-shrink:0;">${statusStyle.label}</span>
            </div>
        </div>
    `;

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

    // Sort filtered scholars alphabetically by last name, then first name
    filteredScholars.sort((a, b) => {
        const lastNameA = (a.last_name || '').toLowerCase();
        const lastNameB = (b.last_name || '').toLowerCase();
        if (lastNameA !== lastNameB) {
            return lastNameA.localeCompare(lastNameB);
        }
        const firstNameA = (a.first_name || '').toLowerCase();
        const firstNameB = (b.first_name || '').toLowerCase();
        return firstNameA.localeCompare(firstNameB);
    });

    currentPage = 1;
    updateSummaryCards();
    renderScholarTable();
    renderPagination();
}

function initializePagination() {
    const firstBtn = document.getElementById('slFirstPage');
    const prevBtn = document.getElementById('slPrevPage');
    const nextBtn = document.getElementById('slNextPage');
    const lastBtn = document.getElementById('slLastPage');

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
    const editMaxBtn = document.getElementById('slEditMaximize');
    const editBox = document.getElementById('slEditBox');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    const btnSaveEdit = document.getElementById('btnSaveEdit');

    const closeEdit = () => {
        editModal.style.display = 'none';
        editBox.classList.remove('sl-modal-maximized');
        editModal.classList.remove('sl-overlay-maximized');
        if (editMaxBtn) {
            editMaxBtn.textContent = '□';
            editMaxBtn.title = 'Maximize';
        }
    };

    if (editClose) editClose.addEventListener('click', closeEdit);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);
    if (btnSaveEdit) btnSaveEdit.addEventListener('click', saveEditStatus);
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) closeEdit();
        });
    }
    if (editMaxBtn && editBox) {
        editMaxBtn.addEventListener('click', () => {
            editBox.classList.toggle('sl-modal-maximized');
            const isMax = editBox.classList.contains('sl-modal-maximized');
            editMaxBtn.textContent = isMax ? '⧉' : '□';
            editMaxBtn.title = isMax ? 'Restore Down' : 'Maximize';
            if (editModal) editModal.classList.toggle('sl-overlay-maximized', isMax);
        });
    }
}
