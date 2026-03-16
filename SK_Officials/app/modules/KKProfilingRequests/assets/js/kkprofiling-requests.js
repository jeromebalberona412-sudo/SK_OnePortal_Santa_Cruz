document.addEventListener('DOMContentLoaded', () => {
    initializeKKProfilingRequestsUI();
});

function initializeKKProfilingRequestsUI() {
    const tbody = document.getElementById('kkRequestsTableBody');
    const statusTabsContainer = document.getElementById('kkStatusTabs');
    const searchInput = document.getElementById('kkSearch');
    const viewModal = document.getElementById('kkViewModal');
    const approveModal = document.getElementById('kkApproveModal');
    const rejectModal = document.getElementById('kkRejectModal');
    const viewModalBox = viewModal ? viewModal.querySelector('.kk-modal-box') : null;

    if (!tbody) return;

    // Sample UI-only data for KK Profiling Requests from Barangay Calios, Santa Cruz, Laguna
    const requests = [
        {
            id: 1,
            firstName: 'Miguel',
            middleName: 'Santos',
            lastName: 'Reyes',
            suffix: 'Jr.',
            age: 18,
            purok: 'Purok 1',
            contact: '09123456789',
            status: 'Pending',
            rejectionReason: '',
        },
        {
            id: 2,
            firstName: 'Angelica',
            middleName: 'Lorenzo',
            lastName: 'Cruz',
            suffix: 'None',
            age: 19,
            purok: 'Sitio 2',
            contact: '09123456790',
            status: 'Approved',
            rejectionReason: '',
        },
        {
            id: 3,
            firstName: 'Jose',
            middleName: 'Antonio',
            lastName: 'Garcia',
            suffix: 'None',
            age: 17,
            purok: 'Villa Gracias',
            contact: '09123456791',
            status: 'Rejected',
            rejectionReason: 'Invalid birthdate / age mismatch',
        },
        {
            id: 4,
            firstName: 'Maria',
            middleName: 'Beatriz',
            lastName: 'Santillan',
            suffix: 'None',
            age: 20,
            purok: 'Bayside Calios',
            contact: '09123456792',
            status: 'Pending',
            rejectionReason: '',
        },
        {
            id: 5,
            firstName: 'Carlos',
            middleName: 'Domingo',
            lastName: 'Mendoza',
            suffix: 'Sr.',
            age: 21,
            purok: 'Purok 3',
            contact: '09123456793',
            status: 'Approved',
            rejectionReason: '',
        },
        {
            id: 6,
            firstName: 'Patricia',
            middleName: 'Rosa',
            lastName: 'Del Rosario',
            suffix: 'None',
            age: 16,
            purok: 'Sitio 1',
            contact: '09123456794',
            status: 'Rejected',
            rejectionReason: 'Incomplete information provided',
        },
        {
            id: 7,
            firstName: 'Antonio',
            middleName: 'Miguel',
            lastName: 'Fernandez',
            suffix: 'III',
            age: 22,
            purok: 'Purok 4',
            contact: '09123456795',
            status: 'Pending',
            rejectionReason: '',
        },
        {
            id: 8,
            firstName: 'Sofia',
            middleName: 'Isabel',
            lastName: 'Castillo',
            suffix: 'None',
            age: 18,
            purok: 'Sitio 3',
            contact: '09123456796',
            status: 'Approved',
            rejectionReason: '',
        }
    ];

    let currentFilterStatus = 'All';
    let currentSearchQuery = '';
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
                const fullName = `${r.firstName} ${r.middleName} ${r.lastName}`.toLowerCase();
                const match = fullName.includes(q) || (r.purok && String(r.purok).toLowerCase().includes(q)) || (r.contact && String(r.contact).toLowerCase().includes(q));
                if (!match) return false;
            }
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
            const fullName = [r.firstName, r.middleName, r.lastName].filter(Boolean).join(' ') || '-';

            let actionsHtml = `
                <button type="button" class="kk-btn-view" data-action="view" data-id="${r.id}">View</button>
            `;

            tr.innerHTML = `
                <td class="kk-fullname-cell">
                    <span class="kk-fullname">${fullName}</span>
                </td>
                <td>${r.age}</td>
                <td>${r.purok}</td>
                <td>${r.contact}</td>
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
                    const fullName = `${r.firstName} ${r.middleName} ${r.lastName}`.toLowerCase();
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
                const fullName = `${r.firstName} ${r.middleName} ${r.lastName}`.toLowerCase();
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
        const { firstName, middleName, lastName, suffix, age, purok, contact, status, rejectionReason } = request;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '';
        };

        setVal('kkViewFirstName', firstName);
        setVal('kkViewMiddleName', middleName);
        setVal('kkViewLastName', lastName);
        setVal('kkViewSuffix', suffix || 'None');
        setVal('kkViewAge', age);
        setVal('kkViewPurok', purok);
        setVal('kkViewContact', contact);
        setVal('kkViewStatus', status);

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

    // Toast notification function (consistent with calendar)
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.app-toast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');
        toast.innerHTML = '<span class="app-toast-icon">✓</span> ' + message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('app-toast-show');
            toast.classList.add('app-toast-hide');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

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
}

