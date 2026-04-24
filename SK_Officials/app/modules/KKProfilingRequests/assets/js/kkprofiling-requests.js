document.addEventListener('DOMContentLoaded', () => {
    initializeKKProfilingRequestsUI();
});

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
    const viewModalBox = viewModal ? viewModal.querySelector('.kk-modal-box') : null;

    if (!tbody) return;

    // Sample UI-only data for KK Profiling Requests with comprehensive structure
    const requests = [];

    // Sort requests array alphabetically by last name (professional standard)
    function sortRequestsAlphabetically() {
        return requests.sort((a, b) => {
            const lastNameA = (a.lastName || '').toLowerCase();
            const lastNameB = (b.lastName || '').toLowerCase();

            if (lastNameA < lastNameB) return -1;
            if (lastNameA > lastNameB) return 1;

            // If last names are the same, sort by first name
            const firstNameA = (a.firstName || '').toLowerCase();
            const firstNameB = (b.firstName || '').toLowerCase();

            if (firstNameA < firstNameB) return -1;
            if (firstNameA > firstNameB) return 1;

            return 0;
        });
    }

    // Function to format name as "LN,FN,MN,Suffix"
    function formatFullName(r) {
        const parts = [r.firstName, r.middleName].filter(Boolean);
        const firstMiddle = parts.length ? parts.join(',') : '';
        const last = r.lastName || '';
        const suffix = r.suffix ? ',' + r.suffix : '';

        if (last && firstMiddle) {
            return `${last},${firstMiddle}${suffix}`;
        } else if (last) {
            return `${last}${suffix}`;
        } else if (firstMiddle) {
            return `${firstMiddle}${suffix}`;
        } else {
            return '-';
        }
    }

    // Initial sort
    sortRequestsAlphabetically();

    let currentFilterStatus = 'All';
    let currentSearchQuery = '';
    let currentBarangayFilter = '';
    let currentVoterFilter = '';
    let activeRequestId = null;

    // Pagination variables
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

        // Calculate pagination
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
            const statusClass =
                r.status === 'Pending'
                    ? 'pending'
                    : r.status === 'Approved'
                        ? 'approved'
                        : 'rejected';
            const fullName = formatFullName(r);

            let actionsHtml = `
                <button type="button" class="kk-btn-view" data-action="view" data-id="${r.id}">View</button>
            `;

            tr.innerHTML = `
                <td class="kk-fullname-cell">
                    <span class="kk-fullname">${fullName}</span>
                </td>
                <td>${r.age}</td>
                <td>${r.barangay}</td>
                <td>${r.registeredVoter}</td>
                <td><span class="kk-status-pill ${statusClass}">${r.status}</span></td>
                <td><div class="kk-actions">${actionsHtml}</div></td>
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

            // Show max 5 page numbers
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);

            // Adjust if we're near the beginning
            if (endPage - startPage < 5) {
                endPage = Math.min(5, totalPages);
                startPage = 1;
            }

            // Adjust if we're near the end
            if (endPage - startPage < 5 && page > totalPages - 2) {
                startPage = Math.max(1, totalPages - 4);
                endPage = totalPages;
            }

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

        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        }
    }

    function setStatusFilter(status) {
        currentFilterStatus = status;
        currentPage = 1; // Reset to first page when filter changes
        if (!statusTabsContainer) return;

        const tabs = statusTabsContainer.querySelectorAll('.status-tab');
        tabs.forEach((tab) => {
            const tabStatus = tab.getAttribute('data-status-filter');
            tab.classList.toggle('active', tabStatus === status);
        });

        renderTable();
    }

    // Modal helpers
    function openModal(modalElement) {
        if (!modalElement) return;
        modalElement.style.display = 'flex';
    }

    function closeModal(modalElement) {
        if (!modalElement) return;
        modalElement.style.display = 'none';
    }

    function closeAllModals() {
        [viewModal, approveModal, rejectModal].forEach((m) => {
            if (m) m.style.display = 'none';
        });
    }

    function populateViewModal(request) {
        const {
            respondentNumber, date, firstName, middleName, lastName, suffix, age, birthday, sex, civilStatus,
            region, province, city, barangay, purokZone, emailAddress, contactNumber,
            youthClassification, youthAgeGroup, workStatus, educationalBackground,
            registeredSKVoter, registeredNationalVoter, votingHistory, votingFrequency, votingReason, attendedKKAssembly,
            facebookAccount, willingToJoinGroupChat, signature, status, rejectionReason
        } = request;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '';
        };

        // Helper: mark a checkbox item as checked (☑) or unchecked (☐)
        const setCheck = (id, checked) => {
            const el = document.getElementById(id);
            if (!el) return;
            const text = el.textContent.replace(/^[☐☑]\s*/, '');
            el.textContent = (checked ? '☑ ' : '☐ ') + text;
            el.style.fontWeight = checked ? '700' : '400';
            el.style.color = checked ? '#1a1a1a' : '#6b7280';
        };

        // General
        setVal('kkViewRespondentNumber', respondentNumber);
        setVal('kkViewDate', date);

        // Profile
        setVal('kkViewLastName', lastName || '—');
        setVal('kkViewFirstName', firstName || '—');
        setVal('kkViewMiddleName', middleName || '—');
        setVal('kkViewSuffix', suffix || '—');

        // Location
        setVal('kkViewRegion', region || '—');
        setVal('kkViewProvince', province || '—');
        setVal('kkViewCity', city || '—');
        setVal('kkViewBarangay', barangay || '—');
        setVal('kkViewPurokZone', purokZone || '—');

        // Personal
        setVal('kkViewSexAssignedAtBirth', sex || '—');
        setVal('kkViewAge', age || '—');
        setVal('kkViewBirthday', birthday || '—');
        setVal('kkViewEmailAddress', emailAddress || '—');
        setVal('kkViewContactNumber', contactNumber || '—');

        // Civil Status checkboxes
        const csMap = {
            kkViewCS_Single: 'Single', kkViewCS_Married: 'Married', kkViewCS_Widowed: 'Widowed',
            kkViewCS_Divorced: 'Divorced', kkViewCS_Separated: 'Separated', kkViewCS_Annulled: 'Annulled',
            kkViewCS_Unknown: 'Unknown', kkViewCS_Livein: 'Live-in',
        };
        Object.entries(csMap).forEach(([id, val]) => setCheck(id, civilStatus === val));

        // Youth Age Group
        const yagMap = {
            kkViewYAG_Child: 'Child Youth (15-17 yrs old)',
            kkViewYAG_Core: 'Core Youth (18-24 yrs old)',
            kkViewYAG_Young: 'Young Adult (15-30 yrs old)',
        };
        Object.entries(yagMap).forEach(([id, val]) => setCheck(id, youthAgeGroup === val));

        // Educational Background
        const ebMap = {
            kkViewEB_ElemLevel: 'Elementary Level', kkViewEB_ElemGrad: 'Elementary Grad',
            kkViewEB_HSLevel: 'High School Level', kkViewEB_HSGrad: 'High School Grad',
            kkViewEB_VocGrad: 'Vocational Grad', kkViewEB_ColLevel: 'College Level',
            kkViewEB_ColGrad: 'College Grad', kkViewEB_MasLevel: 'Masters Level',
            kkViewEB_MasGrad: 'Masters Grad', kkViewEB_DocLevel: 'Doctorate Level',
            kkViewEB_DocGrad: 'Doctorate Graduate',
        };
        Object.entries(ebMap).forEach(([id, val]) => setCheck(id, educationalBackground === val));

        // Youth Classification
        const ycMap = {
            kkViewYC_ISY: 'In School Youth', kkViewYC_OSY: 'Out of School Youth',
            kkViewYC_Working: 'Working Youth', kkViewYC_Specific: 'Youth w/ Specific Needs',
            kkViewYC_PWD: 'Person w/ Disability', kkViewYC_CICL: 'Children in Conflict w/ Law',
            kkViewYC_IP: 'Indigenous People',
        };
        Object.entries(ycMap).forEach(([id, val]) => setCheck(id, youthClassification === val));

        // Work Status
        const wsMap = {
            kkViewWS_Employed: 'Employed', kkViewWS_Unemployed: 'Unemployed',
            kkViewWS_SelfEmployed: 'Self-Employed', kkViewWS_Looking: 'Currently looking for a Job',
            kkViewWS_NotInterested: 'Not Interested Looking for a Job',
        };
        Object.entries(wsMap).forEach(([id, val]) => setCheck(id, workStatus === val));

        // Voter boxes
        setCheck('kkViewSKV_Yes', registeredSKVoter === 'Yes');
        setCheck('kkViewSKV_No', registeredSKVoter === 'No');
        setCheck('kkViewNV_Yes', registeredNationalVoter === 'Yes');
        setCheck('kkViewNV_No', registeredNationalVoter === 'No');
        setCheck('kkViewVH_Yes', votingHistory === 'Yes');
        setCheck('kkViewVH_No', votingHistory === 'No');
        setCheck('kkViewVF_12', votingFrequency === '1-2 Times');
        setCheck('kkViewVF_34', votingFrequency === '3-4 Times');
        setCheck('kkViewVF_5', votingFrequency === '5 and above');
        setCheck('kkViewKK_Yes', attendedKKAssembly === 'Yes');
        setCheck('kkViewKK_No', attendedKKAssembly === 'No');
        setCheck('kkViewVR_NoKK', votingReason === 'There was no KK Assembly');
        setCheck('kkViewVR_NotInt', votingReason === 'Not Interested to Attend');

        // Social
        setVal('kkViewFacebookAccount', facebookAccount || '—');
        setCheck('kkViewGC_Yes', willingToJoinGroupChat === 'Yes');
        setCheck('kkViewGC_No', willingToJoinGroupChat === 'No');

        // Signature
        setVal('kkViewSignature', signature || '—');

        const rejectionWrap = document.getElementById('kkViewRejectionWrap');
        const rejectionText = document.getElementById('kkViewRejectionText');
        if (rejectionWrap && rejectionText) {
            if (status === 'Rejected' && rejectionReason) {
                rejectionWrap.style.display = 'block';
                rejectionText.textContent = rejectionReason;
            } else {
                rejectionWrap.style.display = 'none';
            }
        }
    }

    // Search
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentSearchQuery = searchInput.value.trim();
            currentPage = 1;
            renderTable();
        });
    }

    // Filter event listeners
    if (barangayFilter) {
        barangayFilter.addEventListener('change', () => {
            currentBarangayFilter = barangayFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    if (voterFilter) {
        voterFilter.addEventListener('change', () => {
            currentVoterFilter = voterFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    // Pagination event listeners
    const prevBtn = document.getElementById('kkPrevBtn');
    const nextBtn = document.getElementById('kkNextBtn');

    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    // Status tab events
    if (statusTabsContainer) {
        statusTabsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.status-tab');
            if (!btn) return;
            const status = btn.getAttribute('data-status-filter') || 'All';
            setStatusFilter(status);
        });
    }

    function setupModalToggle(backdrop, box, toggleBtn) {
        if (!toggleBtn || !box || !backdrop) return;
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = backdrop.classList.toggle('modal-maximized');
            box.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        });
    }

    function closeModalResetMax(modalEl, boxEl, toggleBtn) {
        if (!modalEl) return;
        modalEl.classList.remove('modal-maximized');
        if (boxEl) boxEl.classList.remove('modal-maximized');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    const viewToggle = document.getElementById('kkViewModalToggle');
    setupModalToggle(viewModal, viewModalBox, viewToggle);

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
            closeModalResetMax(viewModal, viewModalBox, viewToggle);
            populateViewModal(request);
            openModal(viewModal);
        } else if (action === 'approve') {
            openModal(approveModal);
        } else if (action === 'reject') {
            const checkboxes = rejectModal
                ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)')
                : [];
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

    // View modal footer buttons
    const viewBackBtn = document.getElementById('kkViewBackBtn');
    const viewApproveBtn = document.getElementById('kkViewApproveBtn');
    const viewRejectBtn = document.getElementById('kkViewRejectBtn');

    if (viewBackBtn) {
        viewBackBtn.addEventListener('click', () => {
            closeAllModals();
        });
    }

    if (viewApproveBtn) {
        viewApproveBtn.addEventListener('click', () => {
            if (activeRequestId) {
                const request = requests.find(r => r.id === activeRequestId);
                if (request) {
                    request.status = 'Approved';
                    renderTable();
                    closeAllModals();
                    showSuccessModal();
                }
            }
        });
    }

    if (viewRejectBtn) {
        viewRejectBtn.addEventListener('click', () => {
            if (activeRequestId) {
                openModal(rejectModal);
            }
        });
    }

    // Other checkbox: when Other is selected, deselect all others; when any other is selected, deselect Other
    const otherCheckbox = document.getElementById('kkRejectOtherCheckbox');
    const otherWrap = document.getElementById('kkRejectOtherWrap');
    const otherReasons = rejectModal ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)') : [];

    if (otherCheckbox && otherWrap) {
        otherCheckbox.addEventListener('change', () => {
            if (otherCheckbox.checked) {
                otherReasons.forEach((cb) => { cb.checked = false; });
                otherWrap.style.display = 'flex';
            } else {
                otherWrap.style.display = 'none';
            }
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

    // Approve confirm
    const approveConfirmBtn = document.getElementById('kkApproveConfirmBtn');
    if (approveConfirmBtn) {
        approveConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) {
                closeModal(approveModal);
                return;
            }

            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) {
                closeModal(approveModal);
                return;
            }

            request.status = 'Approved';
            request.rejectionReason = '';
            closeModal(approveModal);
            renderTable();
            showSuccessModal();
        });
    }

    // Success modal close button - removed and replaced with toast

    // Show success modal function - replaced with toast
    function showSuccessModal(action = 'Approved') {
        const message = action === 'Approved'
            ? 'KK Profiling Request Approved Successfully'
            : 'KK Profiling Request Rejected Successfully';
        showToast(message, 'success');
    }

    // Reject confirm
    const rejectConfirmBtn = document.getElementById('kkRejectConfirmBtn');
    if (rejectConfirmBtn) {
        rejectConfirmBtn.addEventListener('click', () => {
            if (activeRequestId === null) {
                closeModal(rejectModal);
                return;
            }

            const request = requests.find((r) => r.id === activeRequestId);
            if (!request) {
                closeModal(rejectModal);
                return;
            }

            const checkboxes = rejectModal
                ? rejectModal.querySelectorAll('.kk-reject-reason:not(.kk-reject-other-checkbox)')
                : [];
            const selectedReasons = [];
            checkboxes.forEach((cb) => {
                if (cb.checked) selectedReasons.push(cb.value);
            });

            const otherCb = document.getElementById('kkRejectOtherCheckbox');
            const otherInput = document.getElementById('kkRejectOtherReason');
            const otherReason = otherInput ? String(otherInput.value || '').trim() : '';
            if (otherCb && otherCb.checked) {
                if (otherReason) selectedReasons.push('Other: ' + otherReason);
                else {
                    alert('Please specify the reason for "Other".');
                    return;
                }
            }

            if (selectedReasons.length === 0) {
                alert('Please select at least one rejection reason or check Other and specify.');
                return;
            }

            request.status = 'Rejected';
            request.rejectionReason = selectedReasons.join('; ');
            closeModal(rejectModal);
            renderTable();
            showSuccessModal('Rejected');
        });
    }

    // Initial render
    setStatusFilter('All');

    // Initialize KK Profiling Schedule
    initializeKKProfilingSchedule();
}

function initializeKKProfilingSchedule() {
    const scheduleBtn = document.getElementById('kkProfilingScheduleBtn');
    const scheduleModal = document.getElementById('kkScheduleModal');
    const clearScheduleModal = document.getElementById('kkClearScheduleModal');
    const calendarDays = document.getElementById('calendarDays');
    const calendarMonthYear = document.getElementById('calendarMonthYear');
    const calendarPrev = document.getElementById('calendarPrev');
    const calendarNext = document.getElementById('calendarNext');
    const clearScheduleBtn = document.getElementById('clearScheduleBtn');
    const saveScheduleBtn = document.getElementById('saveScheduleBtn');
    const jumpBtn = document.getElementById('scheduleJumpBtn');
    const cancelClearBtn = document.getElementById('cancelClearBtn');
    const confirmClearBtn = document.getElementById('confirmClearBtn');
    const scheduleCount = document.getElementById('scheduleCount');
    const scheduleModalBox = scheduleModal ? scheduleModal.querySelector('.kk-schedule-modal-box') : null;
    const scheduleToggle = document.getElementById('kkScheduleModalToggle');

    let currentDate = new Date();
    let profilingDates = new Set(); // Store selected profiling dates
    let isSelecting = false; // Track if user is in selection mode

    // Load saved schedule from localStorage
    loadSavedSchedule();

    // Schedule button click handler
    if (scheduleBtn) {
        scheduleBtn.addEventListener('click', () => {
            openScheduleModal();
        });
    }

    // Calendar navigation
    if (calendarPrev) {
        calendarPrev.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (calendarNext) {
        calendarNext.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    // Clear schedule button
    if (clearScheduleBtn) {
        clearScheduleBtn.addEventListener('click', () => {
            openClearScheduleModal();
        });
    }

    // Save schedule button
    if (saveScheduleBtn) {
        saveScheduleBtn.addEventListener('click', () => {
            saveSchedule();
        });
    }

    if (jumpBtn) {
        jumpBtn.addEventListener('click', () => {
            openJumpModal();
        });
    }

    // Clear schedule modal event listeners
    if (cancelClearBtn) {
        cancelClearBtn.addEventListener('click', () => {
            closeClearScheduleModal();
        });
    }

    if (confirmClearBtn) {
        confirmClearBtn.addEventListener('click', () => {
            confirmClearSchedule();
        });
    }

    // Modal close handlers
    const closeButtons = scheduleModal?.querySelectorAll('[data-modal-close]');
    closeButtons?.forEach(btn => {
        btn.addEventListener('click', closeScheduleModal);
    });

    // Close handlers for clear schedule modal
    const clearCloseButtons = clearScheduleModal?.querySelectorAll('[data-modal-close]');
    clearCloseButtons?.forEach(btn => {
        btn.addEventListener('click', closeClearScheduleModal);
    });

    // Close modal when clicking backdrop
    scheduleModal?.addEventListener('click', (e) => {
        if (e.target === scheduleModal) {
            closeScheduleModal();
        }
    });

    // Close clear schedule modal when clicking backdrop
    clearScheduleModal?.addEventListener('click', (e) => {
        if (e.target === clearScheduleModal) {
            closeClearScheduleModal();
        }
    });

    function openScheduleModal() {
        if (scheduleModal) {
            scheduleModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            renderCalendar();
        }
    }

    function closeScheduleModal() {
        if (scheduleModal) {
            scheduleModal.style.display = 'none';
            document.body.style.overflow = '';
            scheduleModal.classList.remove('modal-maximized');
            if (scheduleModalBox) scheduleModalBox.classList.remove('modal-maximized');
            if (scheduleToggle) scheduleToggle.textContent = '□';
        }
    }

    function openClearScheduleModal() {
        if (clearScheduleModal && scheduleCount) {
            // Update the schedule count
            scheduleCount.textContent = profilingDates.size;
            clearScheduleModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeClearScheduleModal() {
        if (clearScheduleModal) {
            clearScheduleModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function confirmClearSchedule() {
        if (profilingDates.size > 0) {
            profilingDates.clear();
            renderCalendar();
            closeClearScheduleModal();
            showToast('All profiling schedules cleared successfully!', 'success');
        } else {
            closeClearScheduleModal();
            showToast('No schedules to clear.', 'info');
        }
    }

    function renderCalendar() {
        if (!calendarDays || !calendarMonthYear) return;

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Update month/year display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        calendarMonthYear.textContent = `${monthNames[month]} ${year}`;

        // Clear existing days
        calendarDays.innerHTML = '';

        // Get first day of month and days in month
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        // Add previous month's trailing days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const date = new Date(year, month - 1, day);
            const dayElement = createDayElement(day, 'other-month', date);
            calendarDays.appendChild(dayElement);
        }

        // Add current month's days
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            let className = '';

            // Check if today
            if (today.getDate() === day && today.getMonth() === month && today.getFullYear() === year) {
                className = 'today';
            }

            // Check if in profiling period
            if (isProfilingDate(date)) {
                className += ' profiling-period';
            }

            const dayElement = createDayElement(day, className, date);
            calendarDays.appendChild(dayElement);
        }

        // Add next month's leading days
        const totalCells = calendarDays.children.length;
        const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (let day = 1; day <= remainingCells; day++) {
            const date = new Date(year, month + 1, day);
            const dayElement = createDayElement(day, 'other-month', date);
            calendarDays.appendChild(dayElement);
        }
    }

    function createDayElement(day, className, date) {
        const dayElement = document.createElement('div');
        dayElement.className = `calendar-day ${className}`;
        dayElement.innerHTML = `
            <span class="calendar-day-number">${day}</span>
            <span class="calendar-day-label"></span>
        `;
        dayElement.dataset.date = date.toISOString().split('T')[0];

        const labelEl = dayElement.querySelector('.calendar-day-label');
        if (labelEl && className.includes('profiling-period')) {
            labelEl.textContent = 'KK Profiling Open';
        }

        // Add click handler for date selection
        dayElement.addEventListener('click', () => {
            handleDateClick(date, dayElement);
        });

        return dayElement;
    }

    function handleDateClick(date, element) {
        if (element.classList.contains('other-month')) {
            return; // Don't allow selection of other month dates
        }

        // Instant past-date validation on selection
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const clickedDate = new Date(date);
        clickedDate.setHours(0, 0, 0, 0);

        if (clickedDate < today) {
            showToast('Cannot select a past date.', 'error');
            return;
        }

        // Clear all existing selections first
        profilingDates.clear();

        // Add 7 consecutive days starting from the clicked date
        for (let i = 0; i < 7; i++) {
            const newDate = new Date(date);
            newDate.setDate(date.getDate() + i);
            const newDateString = newDate.toISOString().split('T')[0];
            profilingDates.add(newDateString);
        }

        // Re-render the calendar to show the new selection
        renderCalendar();
    }

    function showInlineError(message) {
        showToast(message, 'error');
    }

    function clearInlineError() {
        // No-op: toast auto-dismisses
    }

    function isProfilingDate(date) {
        const dateString = date.toISOString().split('T')[0];
        return profilingDates.has(dateString);
    }

    function saveSchedule() {
        // Validate that we have complete 1-week periods
        if (profilingDates.size === 0) {
            showToast('Schedule cleared successfully!', 'success');
            closeScheduleModal();
            return;
        }

        // Check for past dates
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to start of day for accurate comparison
        
        const hasPastDates = Array.from(profilingDates).some(dateString => {
            const date = new Date(dateString);
            date.setHours(0, 0, 0, 0);
            return date < today;
        });

        if (hasPastDates) {
            showToast('Cannot schedule KK to past dates.', 'error');
            return;
        }

        // Check if the number of dates is a multiple of 7
        if (profilingDates.size % 7 !== 0) {
            alert('Invalid schedule detected. Please select complete 1-week periods.');
            return;
        }

        // Group dates by week and validate they are consecutive
        const groupedWeeks = groupDatesByWeek(Array.from(profilingDates));
        let isValid = true;

        for (const week of groupedWeeks) {
            if (week.length !== 7) {
                isValid = false;
                break;
            }
        }

        if (isValid) {
            // Save to localStorage
            localStorage.setItem('kkProfilingSchedule', JSON.stringify(Array.from(profilingDates)));
            showToast('Schedule saved successfully!', 'success');
            closeScheduleModal();
        } else {
            alert('Invalid schedule detected. Please select complete 1-week periods.');
        }
    }

    function loadSavedSchedule() {
        const saved = localStorage.getItem('kkProfilingSchedule');
        if (saved) {
            try {
                const dates = JSON.parse(saved);
                profilingDates = new Set(dates);
            } catch (e) {
                console.error('Error loading saved schedule:', e);
                profilingDates = new Set();
            }
        }
    }

    function groupDatesByWeek(dates) {
        if (dates.length === 0) return [];

        // Sort dates
        dates.sort();

        const weeks = [];
        let currentWeek = [];

        for (let i = 0; i < dates.length; i++) {
            const date = new Date(dates[i]);

            if (currentWeek.length === 0) {
                currentWeek.push(date);
            } else {
                const lastDate = currentWeek[currentWeek.length - 1];
                const daysDiff = Math.floor((date - lastDate) / (1000 * 60 * 60 * 24));

                if (daysDiff === 1) {
                    currentWeek.push(date);
                } else {
                    // Start new week
                    if (currentWeek.length > 0) {
                        weeks.push([...currentWeek]);
                    }
                    currentWeek = [date];
                }
            }
        }

        if (currentWeek.length > 0) {
            weeks.push(currentWeek);
        }

        return weeks;
    }

    function openJumpModal() {
        let overlay = document.getElementById('kk-schedule-jump-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'kk-schedule-jump-overlay';
            overlay.className = 'calendar-jump-overlay';
            overlay.innerHTML = `
                <div class="calendar-jump-modal">
                    <h3 class="calendar-jump-title">Jump to date</h3>
                    <p class="calendar-jump-display"></p>
                    <div class="calendar-jump-columns">
                        <div class="calendar-jump-col" data-type="month"><div class="calendar-jump-col-inner"></div></div>
                        <div class="calendar-jump-col" data-type="day"><div class="calendar-jump-col-inner"></div></div>
                        <div class="calendar-jump-col" data-type="year"><div class="calendar-jump-col-inner"></div></div>
                    </div>
                    <div class="calendar-jump-actions">
                        <button type="button" class="calendar-jump-cancel">Cancel</button>
                        <button type="button" class="calendar-jump-ok">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const MIN_YEAR = 1991;

        let selMonth = currentDate.getMonth();
        let selDay = Math.min(currentDate.getDate(), 28);
        let selYear = currentDate.getFullYear();

        const displayEl = overlay.querySelector('.calendar-jump-display');
        const monthCol = overlay.querySelector('.calendar-jump-col[data-type="month"] .calendar-jump-col-inner');
        const dayCol = overlay.querySelector('.calendar-jump-col[data-type="day"] .calendar-jump-col-inner');
        const yearCol = overlay.querySelector('.calendar-jump-col[data-type="year"] .calendar-jump-col-inner');

        function clampDay() {
            const maxDay = new Date(selYear, selMonth + 1, 0).getDate();
            selDay = Math.min(selDay, maxDay);
        }

        function updateDisplay() {
            const d = new Date(selYear, selMonth, selDay);
            displayEl.textContent = d.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        }

        function renderColumns() {
            clampDay();
            monthCol.innerHTML = '';
            dayCol.innerHTML = '';
            yearCol.innerHTML = '';

            for (let i = 0; i < 12; i++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (i === selMonth ? ' selected' : '');
                item.textContent = monthNamesShort[i];
                item.addEventListener('click', () => {
                    selMonth = i;
                    renderColumns();
                    updateDisplay();
                });
                monthCol.appendChild(item);
            }

            const daysInMonth = new Date(selYear, selMonth + 1, 0).getDate();
            for (let d = 1; d <= daysInMonth; d++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (d === selDay ? ' selected' : '');
                item.textContent = String(d).padStart(2, '0');
                item.addEventListener('click', () => {
                    selDay = d;
                    renderColumns();
                    updateDisplay();
                });
                dayCol.appendChild(item);
            }

            for (let y = MIN_YEAR; y <= 2100; y++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (y === selYear ? ' selected' : '');
                item.textContent = y;
                item.addEventListener('click', () => {
                    selYear = y;
                    renderColumns();
                    updateDisplay();
                });
                yearCol.appendChild(item);
            }
        }

        function hide() {
            overlay.classList.remove('show');
            overlay.querySelector('.calendar-jump-cancel').removeEventListener('click', onCancel);
            overlay.querySelector('.calendar-jump-ok').removeEventListener('click', onOk);
            overlay.removeEventListener('click', onBackdrop);
        }

        function onCancel() { hide(); }
        function onOk() {
            currentDate = new Date(selYear, selMonth, 1);
            renderCalendar();
            hide();
        }
        function onBackdrop(e) { if (e.target === overlay) hide(); }

        renderColumns();
        updateDisplay();
        overlay.querySelector('.calendar-jump-cancel').addEventListener('click', onCancel);
        overlay.querySelector('.calendar-jump-ok').addEventListener('click', onOk);
        overlay.addEventListener('click', onBackdrop);
        overlay.classList.add('show');
    }

    if (scheduleToggle && scheduleModal && scheduleModalBox) {
        scheduleToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isMax = scheduleModal.classList.toggle('modal-maximized');
            scheduleModalBox.classList.toggle('modal-maximized', isMax);
            scheduleToggle.textContent = isMax ? '⧉' : '□';
        });
    }
}

