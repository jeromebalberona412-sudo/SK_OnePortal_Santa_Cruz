document.addEventListener('DOMContentLoaded', () => {
    initSportsRequests();
});

// ── Sample Data ────────────────────────────────────────────────────────────
const SAMPLE_SPORTS_DATA = [
    {
        id: 2001,
        lastName: 'Dela Cruz',
        firstName: 'Juan',
        middleName: 'Santos',
        suffix: '',
        dateOfBirth: '2003-05-15',
        age: 22,
        contact: '09171234567',
        email: 'juan.delacruz@email.com',
        address: '123 Main St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Youth Division (18-21)',
        dateApplied: 'Apr 28, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2002,
        lastName: 'Santos',
        firstName: 'Maria',
        middleName: 'Reyes',
        suffix: '',
        dateOfBirth: '2001-08-22',
        age: 24,
        contact: '09281234567',
        email: 'maria.santos@email.com',
        address: '456 Rizal Ave., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Young Adult (22-25)',
        dateApplied: 'Apr 29, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: false,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2003,
        lastName: 'Reyes',
        firstName: 'Pedro',
        middleName: 'Garcia',
        suffix: 'Jr.',
        dateOfBirth: '2008-03-10',
        age: 18,
        contact: '09391234567',
        email: 'pedro.reyes@email.com',
        address: '789 Bonifacio St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Cadet Division (15-17)',
        dateApplied: 'Apr 30, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: true
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2004,
        lastName: 'Lim',
        firstName: 'Ana',
        middleName: 'Cruz',
        suffix: '',
        dateOfBirth: '2004-11-18',
        age: 21,
        contact: '09501234567',
        email: 'ana.lim@email.com',
        address: '321 Mabini St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Youth Division (18-21)',
        dateApplied: 'May 1, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2005,
        lastName: 'Garcia',
        firstName: 'Carlos',
        middleName: 'Mendoza',
        suffix: '',
        dateOfBirth: '1996-07-05',
        age: 29,
        contact: '09611234567',
        email: 'carlos.garcia@email.com',
        address: '654 Quezon Blvd., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Senior Division (26-30)',
        dateApplied: 'May 2, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2006,
        lastName: 'Mendoza',
        firstName: 'Sofia',
        middleName: 'Torres',
        suffix: '',
        dateOfBirth: '2009-02-14',
        age: 17,
        contact: '09721234567',
        email: 'sofia.mendoza@email.com',
        address: '987 Luna St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Cadet Division (15-17)',
        dateApplied: 'May 3, 2026',
        requirements: {
            birthCertificate: true,
            validId: false,
            medicalCertificate: true,
            parentConsent: true
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2007,
        lastName: 'Torres',
        firstName: 'Miguel',
        middleName: 'Bautista',
        suffix: '',
        dateOfBirth: '2002-09-30',
        age: 23,
        contact: '09831234567',
        email: 'miguel.torres@email.com',
        address: '147 Aguinaldo Ave., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Basketball',
        division: 'Young Adult (22-25)',
        dateApplied: 'May 4, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    },
    {
        id: 2008,
        lastName: 'Cruz',
        firstName: 'Isabella',
        middleName: 'Villanueva',
        suffix: '',
        dateOfBirth: '2005-12-08',
        age: 20,
        contact: '09941234567',
        email: 'isabella.cruz@email.com',
        address: '258 Del Pilar St., Brgy. Calios, Santa Cruz, Laguna',
        sport: 'Volleyball',
        division: 'Youth Division (18-21)',
        dateApplied: 'May 5, 2026',
        requirements: {
            birthCertificate: true,
            validId: true,
            medicalCertificate: true,
            parentConsent: false
        },
        status: 'Pending',
        paymentStatus: null,
        rejectionReasons: []
    }
];

function initSportsRequests() {
    // Seed sample data if localStorage is empty
    if (!localStorage.getItem('sports_applications_seeded_v1')) {
        localStorage.setItem('sports_applications', JSON.stringify(SAMPLE_SPORTS_DATA));
        localStorage.setItem('sports_applications_seeded_v1', '1');
    }

    let applications = JSON.parse(localStorage.getItem('sports_applications') || '[]');
    let currentApplicationId = null;

    const tbody = document.getElementById('sportsTableBody');
    const searchInput = document.getElementById('sportsSearch');
    const filterSport = document.getElementById('filterSport');
    const filterDivision = document.getElementById('filterDivision');
    const filterStatus = document.getElementById('filterStatus');
    
    const createProgramModal = document.getElementById('createProgramModal');
    const createProgramClose = document.getElementById('createProgramClose');
    const btnCreateProgram = document.getElementById('btnCreateProgram');
    const programCancelBtn = document.getElementById('programCancelBtn');
    const programSaveBtn = document.getElementById('programSaveBtn');
    
    const viewModal = document.getElementById('viewModal');
    const viewModalBody = document.getElementById('viewModalBody');
    const viewClose = document.getElementById('viewClose');
    const btnApprove = document.getElementById('btnApprove');
    const btnReject = document.getElementById('btnReject');
    
    const rejectReasonModal = document.getElementById('rejectReasonModal');
    const rejectReasonClose = document.getElementById('rejectReasonClose');
    const rejectReasonCancel = document.getElementById('rejectReasonCancel');
    const rejectReasonConfirm = document.getElementById('rejectReasonConfirm');
    const rejectReasonOtherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
    const rejectReasonOtherField = document.getElementById('rejectReasonOtherField');
    const rejectReasonOtherText = document.getElementById('rejectReasonOtherText');

    let filterSearchText = '';
    let filterSportValue = '';
    let filterDivisionValue = '';
    let filterStatusValue = '';

    // ── Initialize ──────────────────────────────────────────────────────────
    renderTable();
    updateStats();

    // ── Event Listeners ─────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterSearchText = e.target.value.toLowerCase();
            renderTable();
        });
    }

    if (filterSport) {
        filterSport.addEventListener('change', (e) => {
            filterSportValue = e.target.value;
            renderTable();
        });
    }

    if (filterDivision) {
        filterDivision.addEventListener('change', (e) => {
            filterDivisionValue = e.target.value;
            renderTable();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', (e) => {
            filterStatusValue = e.target.value;
            renderTable();
        });
    }

    // Create Program Modal
    if (btnCreateProgram) {
        btnCreateProgram.addEventListener('click', () => {
            createProgramModal.style.display = 'flex';
        });
    }

    [createProgramClose, programCancelBtn].forEach(btn => {
        if (btn) btn.addEventListener('click', closeCreateProgramModal);
    });

    if (createProgramModal) {
        createProgramModal.addEventListener('click', (e) => {
            if (e.target === createProgramModal) closeCreateProgramModal();
        });
    }

    if (programSaveBtn) {
        programSaveBtn.addEventListener('click', handleCreateProgram);
    }

    // View Modal
    if (viewClose) {
        viewClose.addEventListener('click', closeViewModal);
    }

    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal) closeViewModal();
        });
    }

    if (btnApprove) {
        btnApprove.addEventListener('click', handleApprove);
    }

    if (btnReject) {
        btnReject.addEventListener('click', handleReject);
    }

    // Maximize button for view modal
    const viewMaximize = document.getElementById('viewMaximize');
    const viewBox = document.getElementById('viewBox');
    if (viewMaximize && viewBox) {
        viewMaximize.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = !viewBox.classList.contains('sports-modal-maximized');
            viewModal.classList.toggle('sports-modal-maximized', isMax);
            viewBox.classList.toggle('sports-modal-maximized', isMax);
            viewMaximize.textContent = isMax ? '⧉' : '□';
        });
    }

    // Rejection Reason Modal
    if (rejectReasonOtherCheckbox && rejectReasonOtherField) {
        rejectReasonOtherCheckbox.addEventListener('change', () => {
            rejectReasonOtherField.style.display = rejectReasonOtherCheckbox.checked ? 'block' : 'none';
            if (!rejectReasonOtherCheckbox.checked) {
                rejectReasonOtherText.value = '';
            }
        });
    }

    [rejectReasonClose, rejectReasonCancel].forEach(btn => {
        if (btn) btn.addEventListener('click', closeRejectReasonModal);
    });

    if (rejectReasonModal) {
        rejectReasonModal.addEventListener('click', (e) => {
            if (e.target === rejectReasonModal) closeRejectReasonModal();
        });
    }

    if (rejectReasonConfirm) {
        rejectReasonConfirm.addEventListener('click', confirmReject);
    }

    // ── Functions ───────────────────────────────────────────────────────────
    function renderTable() {
        const filtered = getFilteredApplications();
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr class="sports-empty-row"><td colspan="7">No applications found.</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(app => {
            const fullName = formatFullName(app);
            const statusBadge = getStatusBadge(app.status);

            return `
                <tr>
                    <td style="font-weight:600;">${fullName}</td>
                    <td>${app.sport}</td>
                    <td>${app.division}</td>
                    <td>${app.contact}</td>
                    <td>${app.dateApplied}</td>
                    <td>${statusBadge}</td>
                    <td class="col-actions">
                        <button class="sports-tbl-btn sports-tbl-btn-view" data-id="${app.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // Attach event listeners to view buttons
        tbody.querySelectorAll('.sports-tbl-btn-view').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                openViewModal(id);
            });
        });
    }

    function getFilteredApplications() {
        return applications.filter(app => {
            // Search filter
            if (filterSearchText) {
                const fullName = formatFullName(app).toLowerCase();
                const searchMatch = fullName.includes(filterSearchText) ||
                                  app.sport.toLowerCase().includes(filterSearchText) ||
                                  app.division.toLowerCase().includes(filterSearchText);
                if (!searchMatch) return false;
            }

            // Sport filter
            if (filterSportValue && app.sport !== filterSportValue) return false;

            // Division filter
            if (filterDivisionValue && app.division !== filterDivisionValue) return false;

            // Status filter
            if (filterStatusValue && app.status !== filterStatusValue) return false;

            return true;
        });
    }

    function formatFullName(app) {
        let name = `${app.lastName}, ${app.firstName}`;
        if (app.middleName) name += ` ${app.middleName.charAt(0)}.`;
        if (app.suffix) name += ` ${app.suffix}`;
        return name;
    }

    function getStatusBadge(status) {
        const badges = {
            'Pending': '<span class="sports-badge sports-badge-warning">Pending</span>',
            'Approved': '<span class="sports-badge sports-badge-success">Approved</span>',
            'Rejected': '<span class="sports-badge sports-badge-danger">Rejected</span>'
        };
        return badges[status] || badges['Pending'];
    }

    function updateStats() {
        const total = applications.length;
        const pending = applications.filter(a => a.status === 'Pending').length;
        const approved = applications.filter(a => a.status === 'Approved').length;
        const rejected = applications.filter(a => a.status === 'Rejected').length;

        document.getElementById('statTotal').textContent = total;
        document.getElementById('statPending').textContent = pending;
        document.getElementById('statApproved').textContent = approved;
        document.getElementById('statRejected').textContent = rejected;
    }

    // ── Create Program Modal ────────────────────────────────────────────────
    function closeCreateProgramModal() {
        createProgramModal.style.display = 'none';
        document.getElementById('programForm').reset();
    }

    function handleCreateProgram() {
        const programName = document.getElementById('programName').value.trim();
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;

        // Validation
        if (!programName || !startDate || !endDate || !startTime || !endTime) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        if (endDate < startDate) {
            showToast('End date must be after or equal to start date', 'error');
            return;
        }

        if (startDate === endDate && endTime <= startTime) {
            showToast('End time must be after start time on the same date', 'error');
            return;
        }

        // Create program object
        const program = {
            id: Date.now(),
            programName,
            startDate,
            endDate,
            startTime,
            endTime,
            createdAt: new Date().toISOString()
        };

        // Save to localStorage (for future integration with schedule programs)
        const programs = JSON.parse(localStorage.getItem('sports_programs') || '[]');
        programs.push(program);
        localStorage.setItem('sports_programs', JSON.stringify(programs));

        showToast('Sports program created successfully!', 'success');
        closeCreateProgramModal();
    }

    // ── View Modal ──────────────────────────────────────────────────────────
    function openViewModal(appId) {
        const app = applications.find(a => a.id === appId);
        if (!app) return;

        currentApplicationId = appId;

        viewModalBody.innerHTML = `
            <!-- Personal Information -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Personal Information</h4>
                <div class="sports-info-grid">
                    <div class="sports-info-item">
                        <label>Full Name:</label>
                        <span>${formatFullName(app)}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date of Birth:</label>
                        <span>${app.dateOfBirth}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Age:</label>
                        <span>${app.age}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Contact Number:</label>
                        <span>${app.contact}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Email:</label>
                        <span>${app.email}</span>
                    </div>
                    <div class="sports-info-item sports-info-full">
                        <label>Complete Address:</label>
                        <span>${app.address}</span>
                    </div>
                </div>
            </div>

            <!-- Sports Information -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Sports Information</h4>
                <div class="sports-info-grid">
                    <div class="sports-info-item">
                        <label>Sport:</label>
                        <span>${app.sport}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Division:</label>
                        <span>${app.division}</span>
                    </div>
                    <div class="sports-info-item">
                        <label>Date Applied:</label>
                        <span>${app.dateApplied}</span>
                    </div>
                </div>
            </div>

            <!-- Submitted Requirements -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Submitted Requirements</h4>
                <div class="sports-requirements-grid">
                    ${renderRequirement('Birth Certificate', app.requirements.birthCertificate)}
                    ${renderRequirement('Valid ID', app.requirements.validId)}
                    ${renderRequirement('Medical Certificate', app.requirements.medicalCertificate)}
                    ${renderRequirement('Parent Consent', app.requirements.parentConsent)}
                </div>
            </div>

            <!-- Payment Status -->
            <div class="sports-info-section">
                <h4 class="sports-info-title">Payment Status <span class="sports-req">*</span></h4>
                <div class="sports-radio-group">
                    <label class="sports-radio-label">
                        <input type="radio" name="payment" value="Paid" ${app.paymentStatus === 'Paid' ? 'checked' : ''}>
                        <span>Paid</span>
                    </label>
                    <label class="sports-radio-label">
                        <input type="radio" name="payment" value="Not Paid" ${app.paymentStatus === 'Not Paid' ? 'checked' : ''}>
                        <span>Not Paid</span>
                    </label>
                </div>
            </div>
        `;

        viewModal.style.display = 'flex';
    }

    function renderRequirement(label, submitted) {
        const icon = submitted 
            ? '<svg class="sports-req-icon sports-req-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' 
            : '<svg class="sports-req-icon sports-req-x" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        const className = submitted ? 'sports-req-met' : 'sports-req-missing';
        
        return `
            <div class="sports-req-item ${className}">
                ${icon}
                <span>${label}</span>
            </div>
        `;
    }

    function closeViewModal() {
        viewModal.style.display = 'none';
        viewModal.classList.remove('sports-modal-maximized');
        const viewBox = document.getElementById('viewBox');
        if (viewBox) viewBox.classList.remove('sports-modal-maximized');
        const viewMaximize = document.getElementById('viewMaximize');
        if (viewMaximize) viewMaximize.textContent = '□';
        currentApplicationId = null;
    }

    // ── Approve/Reject ──────────────────────────────────────────────────────
    function handleApprove() {
        const paymentStatus = document.querySelector('input[name="payment"]:checked');
        
        if (!paymentStatus) {
            showToast('Please select payment status before approving', 'error');
            return;
        }

        const app = applications.find(a => a.id === currentApplicationId);
        if (!app) return;

        app.status = 'Approved';
        app.paymentStatus = paymentStatus.value;
        app.approvedDate = new Date().toISOString();

        // Move to approved list
        const approvedApps = JSON.parse(localStorage.getItem('sports_approved') || '[]');
        approvedApps.push(app);
        localStorage.setItem('sports_approved', JSON.stringify(approvedApps));

        // Remove from pending
        applications = applications.filter(a => a.id !== currentApplicationId);
        localStorage.setItem('sports_applications', JSON.stringify(applications));

        closeViewModal();
        renderTable();
        updateStats();
        showToast('Application approved successfully!', 'success');
    }

    function handleReject() {
        const paymentStatus = document.querySelector('input[name="payment"]:checked');
        
        if (!paymentStatus) {
            showToast('Please select payment status before rejecting', 'error');
            return;
        }

        closeViewModal();
        openRejectReasonModal();
    }

    // ── Rejection Reason Modal ──────────────────────────────────────────────
    function openRejectReasonModal() {
        // Reset all checkboxes and other field
        document.querySelectorAll('.reject-reason-checkbox').forEach(cb => cb.checked = false);
        if (rejectReasonOtherText) rejectReasonOtherText.value = '';
        if (rejectReasonOtherField) rejectReasonOtherField.style.display = 'none';
        if (rejectReasonModal) rejectReasonModal.style.display = 'flex';
    }

    function closeRejectReasonModal() {
        if (rejectReasonModal) rejectReasonModal.style.display = 'none';
    }

    function confirmReject() {
        const reasons = getSelectedReasons();
        
        if (reasons.length === 0) {
            showToast('Please select at least one reason for rejection', 'error');
            return;
        }

        // Check if "Other" is selected and textarea is empty
        const otherCheckbox = document.getElementById('rejectReasonOtherCheckbox');
        if (otherCheckbox && otherCheckbox.checked) {
            const otherText = document.getElementById('rejectReasonOtherText').value.trim();
            if (!otherText) {
                showToast('Please specify the other reason', 'error');
                return;
            }
        }

        const app = applications.find(a => a.id === currentApplicationId);
        if (!app) return;

        app.status = 'Rejected';
        app.rejectionReasons = reasons;
        app.rejectedDate = new Date().toISOString();

        // Remove from pending
        applications = applications.filter(a => a.id !== currentApplicationId);
        localStorage.setItem('sports_applications', JSON.stringify(applications));

        closeRejectReasonModal();
        renderTable();
        updateStats();
        showToast('Application rejected successfully', 'success');
    }

    function getSelectedReasons() {
        const reasons = [];
        document.querySelectorAll('.reject-reason-checkbox:checked').forEach(cb => {
            if (cb.value === 'Other') {
                const otherText = document.getElementById('rejectReasonOtherText').value.trim();
                reasons.push(`Other: ${otherText}`);
            } else {
                reasons.push(cb.value);
            }
        });
        return reasons;
    }

    // ── Toast Notification ──────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const toast = document.getElementById('sportsToast');
        const toastMsg = document.getElementById('sportsToastMsg');
        
        if (!toast || !toastMsg) return;

        toastMsg.textContent = message;
        
        // Set color based on type
        if (type === 'error') {
            toast.style.background = '#ef4444';
        } else {
            toast.style.background = '#10b981';
        }

        toast.style.display = 'flex';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
}
