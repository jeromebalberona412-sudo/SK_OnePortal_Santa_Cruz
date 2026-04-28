// Module-level toast — accessible by all functions in this file
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

function initializeKKProfilingRequestsUI() {
    const tbody = document.getElementById('kkRequestsTableBody');
    const statusTabsContainer = document.getElementById('kkStatusTabs');
    const searchInput = document.getElementById('kkSearch');
    const barangayFilter = document.getElementById('kkBarangayFilter');
    const voterFilter = document.getElementById('kkVoterFilter');
    const viewModal = document.getElementById('kkViewModal');
    const approveModal = document.getElementById('kkApproveModal');
    const rejectModal = document.getElementById('kkRejectModal');

    if (!tbody) return;

    const requests = [
        {
            id: 1,
            respondentNumber: 'KK-2026-001',
            date: '2026-04-20',
            firstName: 'Juan',
            middleName: 'Santos',
            lastName: 'Dela Cruz',
            suffix: '',
            age: 22,
            birthday: '2004-03-15',
            sex: 'Male',
            civilStatus: 'Single',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purokZone: 'Purok 1',
            emailAddress: 'juan.delacruz@email.com',
            contactNumber: '09171234567',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Core Youth (18-24 yrs old)',
            workStatus: 'Unemployed',
            educationalBackground: 'College Level',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            registeredVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '1-2 Times',
            votingReason: '',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'juan.delacruz',
            willingToJoinGroupChat: 'Yes',
            signature: 'Juan Dela Cruz',
            status: 'Pending',
            rejectionReason: ''
        },
        {
            id: 2,
            respondentNumber: 'KK-2026-002',
            date: '2026-04-21',
            firstName: 'Maria',
            middleName: 'Garcia',
            lastName: 'Reyes',
            suffix: '',
            age: 19,
            birthday: '2007-07-22',
            sex: 'Female',
            civilStatus: 'Single',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purokZone: 'Purok 2',
            emailAddress: 'maria.reyes@email.com',
            contactNumber: '09281234567',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Core Youth (18-24 yrs old)',
            workStatus: 'Currently looking for a Job',
            educationalBackground: 'College Level',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            registeredVoter: 'Yes',
            votingHistory: 'No',
            votingFrequency: '',
            votingReason: 'Not Interested to Attend',
            attendedKKAssembly: 'No',
            facebookAccount: 'maria.reyes22',
            willingToJoinGroupChat: 'Yes',
            signature: 'Maria G. Reyes',
            status: 'Approved',
            rejectionReason: ''
        },
        {
            id: 3,
            respondentNumber: 'KK-2026-003',
            date: '2026-04-22',
            firstName: 'Pedro',
            middleName: 'Ramos',
            lastName: 'Santos',
            suffix: 'Jr.',
            age: 24,
            birthday: '2002-11-10',
            sex: 'Male',
            civilStatus: 'Married',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purokZone: 'Purok 3',
            emailAddress: 'pedro.santos@email.com',
            contactNumber: '09391234567',
            youthClassification: 'Working Youth',
            youthAgeGroup: 'Core Youth (18-24 yrs old)',
            workStatus: 'Employed',
            educationalBackground: 'College Grad',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            registeredVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '3-4 Times',
            votingReason: '',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'pedro.santos.jr',
            willingToJoinGroupChat: 'Yes',
            signature: 'Pedro R. Santos Jr.',
            status: 'Approved',
            rejectionReason: ''
        },
        {
            id: 4,
            respondentNumber: 'KK-2026-004',
            date: '2026-04-23',
            firstName: 'Ana',
            middleName: 'Lopez',
            lastName: 'Cruz',
            suffix: '',
            age: 17,
            birthday: '2009-01-05',
            sex: 'Female',
            civilStatus: 'Single',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purokZone: 'Purok 4',
            emailAddress: 'ana.cruz@email.com',
            contactNumber: '09451234567',
            youthClassification: 'In School Youth',
            youthAgeGroup: 'Child Youth (15-17 yrs old)',
            workStatus: 'Unemployed',
            educationalBackground: 'High School Level',
            registeredSKVoter: 'No',
            registeredNationalVoter: 'No',
            registeredVoter: 'No',
            votingHistory: 'No',
            votingFrequency: '',
            votingReason: 'There was no KK Assembly',
            attendedKKAssembly: 'No',
            facebookAccount: 'ana.cruz05',
            willingToJoinGroupChat: 'Yes',
            signature: 'Ana L. Cruz',
            status: 'Pending',
            rejectionReason: ''
        },
        {
            id: 5,
            respondentNumber: 'KK-2026-005',
            date: '2026-04-24',
            firstName: 'Carlos',
            middleName: 'Mendoza',
            lastName: 'Bautista',
            suffix: '',
            age: 21,
            birthday: '2005-09-18',
            sex: 'Male',
            civilStatus: 'Single',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purokZone: 'Purok 5',
            emailAddress: 'carlos.bautista@email.com',
            contactNumber: '09561234567',
            youthClassification: 'Out of School Youth',
            youthAgeGroup: 'Core Youth (18-24 yrs old)',
            workStatus: 'Self-Employed',
            educationalBackground: 'High School Grad',
            registeredSKVoter: 'Yes',
            registeredNationalVoter: 'Yes',
            registeredVoter: 'Yes',
            votingHistory: 'Yes',
            votingFrequency: '5 and above',
            votingReason: '',
            attendedKKAssembly: 'Yes',
            facebookAccount: 'carlos.bautista18',
            willingToJoinGroupChat: 'No',
            signature: 'Carlos M. Bautista',
            status: 'Rejected',
            rejectionReason: 'Incomplete Information; Invalid Contact Number'
        }
    ];

    function sortRequestsAlphabetically() {
        return requests.sort((a, b) => {
            const lastNameA = (a.lastName || '').toLowerCase();
            const lastNameB = (b.lastName || '').toLowerCase();
            if (lastNameA < lastNameB) return -1;
            if (lastNameA > lastNameB) return 1;
            const firstNameA = (a.firstName || '').toLowerCase();
            const firstNameB = (b.firstName || '').toLowerCase();
            if (firstNameA < firstNameB) return -1;
            if (firstNameA > firstNameB) return 1;
            return 0;
        });
    }

    function formatFullName(r) {
        const parts = [r.firstName, r.middleName].filter(Boolean);
        const firstMiddle = parts.length ? parts.join(',') : '';
        const last = r.lastName || '';
        const suffix = r.suffix ? ',' + r.suffix : '';
        if (last && firstMiddle) return `${last},${firstMiddle}${suffix}`;
        else if (last) return `${last}${suffix}`;
        else if (firstMiddle) return `${firstMiddle}${suffix}`;
        else return '-';
    }

    sortRequestsAlphabetically();

    let currentFilterStatus = 'All';
    let currentSearchQuery = '';
    let currentBarangayFilter = '';
    let currentVoterFilter = '';
    let activeRequestId = null;
    let currentPage = 1;
    const recordsPerPage = 10;

    function renderTable() {
        tbody.innerHTML = '';
        const filtered = requests.filter((r) => {
            if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = formatFullName(r).toLowerCase();
                const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
            if (currentBarangayFilter && r.barangay !== currentBarangayFilter) return false;
            if (currentVoterFilter && r.registeredVoter !== currentVoterFilter) return false;
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
            td.colSpan = 6;
            td.textContent = 'No KK Profiling requests for this status.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            updatePaginationInfo(0, 0, 1);
            return;
        }

        paginatedData.forEach((r) => {
            const tr = document.createElement('tr');
            const statusClass = r.status === 'Pending' ? 'pending' : r.status === 'Approved' ? 'approved' : 'rejected';
            const fullName = formatFullName(r);
            const voterStatus = r.registeredVoter || 'No';
            tr.innerHTML = `
                <td class="kk-fullname-cell"><span class="kk-fullname">${fullName}</span></td>
                <td>${r.age}</td>
                <td>${r.barangay}</td>
                <td>${voterStatus}</td>
                <td><span class="kk-status-pill ${statusClass}">${r.status}</span></td>
                <td><div class="kk-actions"><button type="button" class="kk-btn-view" data-action="view" data-id="${r.id}">View</button></div></td>
            `;
            tbody.appendChild(tr);
        });

        updatePaginationInfo(startIndex + 1, endIndex, currentPage, totalPages);
        updatePaginationControls(currentPage, totalPages);
    }

    function updatePaginationInfo(start, end, page, totalPages) {
        const info = document.getElementById('kkPaginationInfo');
        if (info) {
            const total = requests.filter((r) => {
                if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
                if (currentSearchQuery) {
                    const q = currentSearchQuery.toLowerCase();
                    const fullName = formatFullName(r).toLowerCase();
                    const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                    if (!match) return false;
                }
                return true;
            }).length;
            info.textContent = total === 0 ? 'No records found' : `Showing ${start}-${end} of ${total} records`;
        }
    }

    function updatePaginationControls(page, totalPages) {
        const prevBtn = document.getElementById('kkPrevBtn');
        const nextBtn = document.getElementById('kkNextBtn');
        const pageNumbers = document.getElementById('kkPageNumbers');
        if (prevBtn) prevBtn.disabled = page === 1;
        if (nextBtn) nextBtn.disabled = page === totalPages;
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);
            if (endPage - startPage < 5) { endPage = Math.min(5, totalPages); startPage = 1; }
            if (endPage - startPage < 5 && page > totalPages - 2) { startPage = Math.max(1, totalPages - 4); endPage = totalPages; }
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
        const totalPages = Math.ceil(requests.filter((r) => {
            if (currentFilterStatus !== 'All' && r.status !== currentFilterStatus) return false;
            if (currentSearchQuery) {
                const q = currentSearchQuery.toLowerCase();
                const fullName = formatFullName(r).toLowerCase();
                const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
            return true;
        }).length / recordsPerPage);
        if (page >= 1 && page <= totalPages) { currentPage = page; renderTable(); }
    }

    function setStatusFilter(status) {
        currentFilterStatus = status;
        currentPage = 1;
        if (!statusTabsContainer) return;
        const tabs = statusTabsContainer.querySelectorAll('.status-tab');
        tabs.forEach((tab) => {
            tab.classList.toggle('active', tab.getAttribute('data-status-filter') === status);
        });
        renderTable();
    }

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }
    function closeAllModals() { [viewModal, approveModal, rejectModal].forEach((m) => { if (m) m.style.display = 'none'; }); }

    function populateViewModal(request) {
        const { respondentNumber, date, firstName, middleName, lastName, suffix, age, birthday, sex, civilStatus,
            region, province, city, barangay, purokZone, emailAddress, contactNumber,
            youthClassification, youthAgeGroup, workStatus, educationalBackground,
            registeredSKVoter, registeredNationalVoter, votingHistory, votingFrequency, votingReason, attendedKKAssembly,
            facebookAccount, willingToJoinGroupChat, signature, status, rejectionReason } = request;

        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? ''; };
        const setCheck = (id, checked) => {
            const el = document.getElementById(id);
            if (!el) return;
            const text = el.textContent.replace(/^[☐☑]\s*/, '');
            el.textContent = (checked ? '☑ ' : '☐ ') + text;
            el.style.fontWeight = checked ? '700' : '400';
            el.style.color = checked ? '#1a1a1a' : '#6b7280';
        };

        setVal('kkViewRespondentNumber', respondentNumber); setVal('kkViewDate', date);
        setVal('kkViewLastName', lastName || '—'); setVal('kkViewFirstName', firstName || '—');
        setVal('kkViewMiddleName', middleName || '—'); setVal('kkViewSuffix', suffix || '—');
        setVal('kkViewRegion', region || '—'); setVal('kkViewProvince', province || '—');
        setVal('kkViewCity', city || '—'); setVal('kkViewBarangay', barangay || '—');
        setVal('kkViewPurokZone', purokZone || '—'); setVal('kkViewSexAssignedAtBirth', sex || '—');
        setVal('kkViewAge', age || '—'); setVal('kkViewBirthday', birthday || '—');
        setVal('kkViewEmailAddress', emailAddress || '—'); setVal('kkViewContactNumber', contactNumber || '—');

        const csMap = { kkViewCS_Single:'Single', kkViewCS_Married:'Married', kkViewCS_Widowed:'Widowed', kkViewCS_Divorced:'Divorced', kkViewCS_Separated:'Separated', kkViewCS_Annulled:'Annulled', kkViewCS_Unknown:'Unknown', kkViewCS_Livein:'Live-in' };
        Object.entries(csMap).forEach(([id, val]) => setCheck(id, civilStatus === val));
        const yagMap = { kkViewYAG_Child:'Child Youth (15-17 yrs old)', kkViewYAG_Core:'Core Youth (18-24 yrs old)', kkViewYAG_Young:'Young Adult (15-30 yrs old)' };
        Object.entries(yagMap).forEach(([id, val]) => setCheck(id, youthAgeGroup === val));
        const ebMap = { kkViewEB_ElemLevel:'Elementary Level', kkViewEB_ElemGrad:'Elementary Grad', kkViewEB_HSLevel:'High School Level', kkViewEB_HSGrad:'High School Grad', kkViewEB_VocGrad:'Vocational Grad', kkViewEB_ColLevel:'College Level', kkViewEB_ColGrad:'College Grad', kkViewEB_MasLevel:'Masters Level', kkViewEB_MasGrad:'Masters Grad', kkViewEB_DocLevel:'Doctorate Level', kkViewEB_DocGrad:'Doctorate Graduate' };
        Object.entries(ebMap).forEach(([id, val]) => setCheck(id, educationalBackground === val));
        const ycMap = { kkViewYC_ISY:'In School Youth', kkViewYC_OSY:'Out of School Youth', kkViewYC_Working:'Working Youth', kkViewYC_Specific:'Youth w/ Specific Needs', kkViewYC_PWD:'Person w/ Disability', kkViewYC_CICL:'Children in Conflict w/ Law', kkViewYC_IP:'Indigenous People' };
        Object.entries(ycMap).forEach(([id, val]) => setCheck(id, youthClassification === val));
        const wsMap = { kkViewWS_Employed:'Employed', kkViewWS_Unemployed:'Unemployed', kkViewWS_SelfEmployed:'Self-Employed', kkViewWS_Looking:'Currently looking for a Job', kkViewWS_NotInterested:'Not Interested Looking for a Job' };
        Object.entries(wsMap).forEach(([id, val]) => setCheck(id, workStatus === val));

        setCheck('kkViewSKV_Yes', registeredSKVoter === 'Yes'); setCheck('kkViewSKV_No', registeredSKVoter === 'No');
        setCheck('kkViewNV_Yes', registeredNationalVoter === 'Yes'); setCheck('kkViewNV_No', registeredNationalVoter === 'No');
        setCheck('kkViewVH_Yes', votingHistory === 'Yes'); setCheck('kkViewVH_No', votingHistory === 'No');
        setCheck('kkViewVF_12', votingFrequency === '1-2 Times'); setCheck('kkViewVF_34', votingFrequency === '3-4 Times'); setCheck('kkViewVF_5', votingFrequency === '5 and above');
        setCheck('kkViewKK_Yes', attendedKKAssembly === 'Yes'); setCheck('kkViewKK_No', attendedKKAssembly === 'No');
        setCheck('kkViewVR_NoKK', votingReason === 'There was no KK Assembly'); setCheck('kkViewVR_NotInt', votingReason === 'Not Interested to Attend');
        setVal('kkViewFacebookAccount', facebookAccount || '—');
        setCheck('kkViewGC_Yes', willingToJoinGroupChat === 'Yes'); setCheck('kkViewGC_No', willingToJoinGroupChat === 'No');
        setVal('kkViewSignature', signature || '—');

        const rejectionWrap = document.getElementById('kkViewRejectionWrap');
        const rejectionText = document.getElementById('kkViewRejectionText');
        if (rejectionWrap && rejectionText) {
            if (status === 'Rejected' && rejectionReason) { rejectionWrap.style.display = 'block'; rejectionText.textContent = rejectionReason; }
            else { rejectionWrap.style.display = 'none'; }
        }
    }

    if (searchInput) { searchInput.addEventListener('input', () => { currentSearchQuery = searchInput.value.trim(); currentPage = 1; renderTable(); }); }
    if (barangayFilter) { barangayFilter.addEventListener('change', () => { currentBarangayFilter = barangayFilter.value; currentPage = 1; renderTable(); }); }
    if (voterFilter) { voterFilter.addEventListener('change', () => { currentVoterFilter = voterFilter.value; currentPage = 1; renderTable(); }); }

    const prevBtn = document.getElementById('kkPrevBtn');
    const nextBtn = document.getElementById('kkNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    if (statusTabsContainer) {
        statusTabsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.status-tab');
            if (!btn) return;
            setStatusFilter(btn.getAttribute('data-status-filter') || 'All');
        });
    }

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

    // Wire toggle buttons after modals exist in DOM
    wireModalToggle(viewModal);

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = parseInt(btn.getAttribute('data-id') || '', 10);
        if (!action || Number.isNaN(id)) return;
        const request = requests.find((r) => r.id === id);
        if (!request) return;
        activeRequestId = id;
        if (action === 'view') {
            resetModalMaximize(viewModal);
            populateViewModal(request);
            openModal(viewModal);
        } else if (action === 'approve') {
            openModal(approveModal);
        } else if (action === 'reject') {
            const checkboxes = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];
            checkboxes.forEach((cb) => { cb.checked = false; });
            const otherCheckbox = document.getElementById('kkRejectOtherCheckbox');
            const otherWrap = document.getElementById('kkRejectOtherWrap');
            const otherInput = document.getElementById('kkRejectOtherReason');
            if (otherCheckbox) otherCheckbox.checked = false;
            if (otherWrap) otherWrap.style.display = 'none';
            if (otherInput) otherInput.value = '';
            openModal(rejectModal);
        }
    });

    const viewApproveBtn = document.getElementById('kkViewApproveBtn');
    const viewRejectBtn = document.getElementById('kkViewRejectBtn');

    if (viewApproveBtn) {
        viewApproveBtn.addEventListener('click', () => {
            if (activeRequestId) {
                const request = requests.find(r => r.id === activeRequestId);
                if (request) { request.status = 'Approved'; renderTable(); closeAllModals(); showSuccessModal(); }
            }
        });
    }

    if (viewRejectBtn) {
        viewRejectBtn.addEventListener('click', () => { if (activeRequestId) openModal(rejectModal); });
    }

    const otherCheckbox = document.getElementById('kkRejectOtherCheckbox');
    const otherWrap = document.getElementById('kkRejectOtherWrap');
    const otherReasons = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];

    if (otherCheckbox && otherWrap) {
        otherCheckbox.addEventListener('change', () => {
            if (otherCheckbox.checked) { otherReasons.forEach((cb) => { cb.checked = false; }); otherWrap.style.display = 'flex'; }
            else { otherWrap.style.display = 'none'; }
        });
    }
    otherReasons.forEach((cb) => {
        cb.addEventListener('change', () => {
            if (cb.checked && otherCheckbox) {
                otherCheckbox.checked = false;
                otherWrap.style.display = 'none';
                const otherInput = document.getElementById('kkRejectOtherReason');
                if (otherInput) otherInput.value = '';
            }
        });
    });

    [viewModal, approveModal, rejectModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                closeModal(modal);
                if (modal === viewModal) closeModalResetMax(viewModal, viewModalBox, viewToggle);
            }
        });
    });

    const approveConfirmBtn = document.getElementById('kkApproveConfirmBtn');
    if (approveConfirmBtn) {
        approveConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) { closeModal(approveModal); return; }
            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) { closeModal(approveModal); return; }
            request.status = 'Approved';
            request.rejectionReason = '';
            closeModal(approveModal);
            renderTable();
            showSuccessModal();
        });
    }

    function showSuccessModal(action = 'Approved') {
        showToast(action === 'Approved' ? 'KK Profiling Request Approved Successfully' : 'KK Profiling Request Rejected Successfully', 'success');
    }

    const rejectConfirmBtn = document.getElementById('kkRejectConfirmBtn');
    if (rejectConfirmBtn) {
        rejectConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) { closeModal(rejectModal); return; }
            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) { closeModal(rejectModal); return; }
            const checkboxes = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];
            const selectedReasons = [];
            checkboxes.forEach((cb) => { if (cb.checked) selectedReasons.push(cb.value); });
            const otherCb = document.getElementById('kkRejectOtherCheckbox');
            const otherInput = document.getElementById('kkRejectOtherReason');
            const otherReason = otherInput ? String(otherInput.value || '').trim() : '';
            if (otherCb && otherCb.checked) {
                if (otherReason) selectedReasons.push('Other: ' + otherReason);
                else { alert('Please specify the reason for "Other".'); return; }
            }
            if (selectedReasons.length === 0) { alert('Please select at least one rejection reason or check Other and specify.'); return; }
            request.status = 'Rejected';
            request.rejectionReason = selectedReasons.join('; ');
            closeModal(rejectModal);
            closeModal(viewModal);
            renderTable();
            showSuccessModal('Rejected');
        });
    }

    // Initial render
    setStatusFilter('All');
}

// ═══════════════════════════════════════════════════════
// BARANGAY CENSUS FUNCTIONALITY
// ═══════════════════════════════════════════════════════

function initializeCensusUI() {
    const censusTableBody = document.getElementById('kkCensusTableBody');
    const uploadCensusBtn = document.getElementById('kkUploadCensusBtn');
    const uploadCensusModal = document.getElementById('kkUploadCensusModal');
    const uploadCensusConfirmBtn = document.getElementById('kkUploadCensusConfirmBtn');
    const censusFileInput = document.getElementById('kkCensusFile');
    const viewCensusModal = document.getElementById('kkViewCensusModal');

    if (!censusTableBody) return;

    // Sample census data
    const censusData = [
        {
            id: 1,
            formNo: 'CMP-04-001',
            controlNumber: 'CN-2026-001',
            cy: '2026',
            lastName: 'Santos',
            firstName: 'Maria',
            middleName: 'Garcia',
            houseNo: '123',
            street: 'Purok 1',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Female',
            civilStatus: 'Married',
            dateOfBirth: '1985-05-15',
            placeOfBirth: 'Santa Cruz, Laguna',
            height: '5\'4"',
            weight: '120 lbs',
            contactNumber: '09171234567',
            religion: 'Roman Catholic',
            email: 'maria.santos@email.com'
        },
        {
            id: 2,
            formNo: 'CMP-04-002',
            controlNumber: 'CN-2026-002',
            cy: '2026',
            lastName: 'Reyes',
            firstName: 'Juan',
            middleName: 'Cruz',
            houseNo: '456',
            street: 'Purok 2',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Male',
            civilStatus: 'Single',
            dateOfBirth: '1990-08-20',
            placeOfBirth: 'Manila',
            height: '5\'8"',
            weight: '150 lbs',
            contactNumber: '09281234567',
            religion: 'Iglesia ni Cristo',
            email: 'juan.reyes@email.com'
        },
        {
            id: 3,
            formNo: 'CMP-04-003',
            controlNumber: 'CN-2026-003',
            cy: '2026',
            lastName: 'Dela Cruz',
            firstName: 'Ana',
            middleName: 'Lopez',
            houseNo: '789',
            street: 'Purok 3',
            barangay: 'Calios',
            city: 'Santa Cruz',
            province: 'Laguna',
            sex: 'Female',
            civilStatus: 'Widow',
            dateOfBirth: '1975-12-10',
            placeOfBirth: 'Laguna',
            height: '5\'2"',
            weight: '110 lbs',
            contactNumber: '09391234567',
            religion: 'Born Again',
            email: 'ana.delacruz@email.com'
        }
    ];

    function renderCensusTable() {
        censusTableBody.innerHTML = '';
        if (censusData.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 7;
            td.textContent = 'No census data available. Upload an Excel file to get started.';
            tr.appendChild(td);
            censusTableBody.appendChild(tr);
            return;
        }

        censusData.forEach((c) => {
            const tr = document.createElement('tr');
            const fullName = `${c.lastName}, ${c.firstName} ${c.middleName}`;
            tr.innerHTML = `
                <td>${c.formNo}</td>
                <td>${c.controlNumber}</td>
                <td class="kk-fullname-cell"><span class="kk-fullname">${fullName}</span></td>
                <td>${c.barangay}</td>
                <td>${c.dateOfBirth}</td>
                <td>${c.civilStatus}</td>
                <td><div class="kk-actions"><button type="button" class="kk-btn-view" data-action="view-census" data-id="${c.id}">View</button></div></td>
            `;
            censusTableBody.appendChild(tr);
        });
    }

    function populateViewCensusModal(census) {
        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? '—'; };
        
        setVal('kkCensusFormNo', census.formNo);
        setVal('kkCensusControlNumber', census.controlNumber);
        setVal('kkCensusCY', census.cy);
        setVal('kkCensusLastName', census.lastName);
        setVal('kkCensusFirstName', census.firstName);
        setVal('kkCensusMiddleName', census.middleName);
        setVal('kkCensusHouseNo', census.houseNo);
        setVal('kkCensusStreet', census.street);
        setVal('kkCensusBarangay', census.barangay);
        setVal('kkCensusCity', census.city);
        setVal('kkCensusProvince', census.province);
        setVal('kkCensusSex', census.sex);
        setVal('kkCensusCivilStatus', census.civilStatus);
        setVal('kkCensusDOB', census.dateOfBirth);
        setVal('kkCensusContact', census.contactNumber);
    }

    // Upload Census Button
    if (uploadCensusBtn) {
        uploadCensusBtn.addEventListener('click', () => {
            if (censusFileInput) censusFileInput.value = '';
            openModal(uploadCensusModal);
        });
    }

    // Upload Census Confirm
    if (uploadCensusConfirmBtn && censusFileInput) {
        uploadCensusConfirmBtn.addEventListener('click', () => {
            const file = censusFileInput.files[0];
            if (!file) {
                showToast('Please select an Excel file to upload', 'error');
                return;
            }
            
            // Validate file type
            const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                showToast('Please upload a valid Excel file (.xlsx or .xls)', 'error');
                return;
            }

            // Simulate upload (in real implementation, this would send to server)
            closeModal(uploadCensusModal);
            showToast('Census data uploaded successfully', 'success');
            renderCensusTable();
        });
    }

    // View Census Details
    if (censusTableBody) {
        censusTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action="view-census"]');
            if (!btn) return;
            const id = parseInt(btn.getAttribute('data-id') || '', 10);
            if (Number.isNaN(id)) return;
            const census = censusData.find((c) => c.id === id);
            if (!census) return;
            
            populateViewCensusModal(census);
            resetModalMaximize(viewCensusModal);
            openModal(viewCensusModal);
        });
    }

    // Wire toggle buttons for census modal
    wireModalToggle(viewCensusModal);

    // Close modals
    [uploadCensusModal, viewCensusModal].forEach((modal) => {
        if (!modal) return;
        modal.addEventListener('click', (e) => {
            const target = e.target;
            if (target === modal || target.hasAttribute('data-modal-close')) {
                resetModalMaximize(modal);
                closeModal(modal);
            }
        });
    });

    function openModal(modalElement) { if (modalElement) modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { if (modalElement) modalElement.style.display = 'none'; }

    // Initial render
    renderCensusTable();
}

// Initialize both modules
document.addEventListener('DOMContentLoaded', () => {
    initializeKKProfilingRequestsUI();
    initializeCensusUI();
});