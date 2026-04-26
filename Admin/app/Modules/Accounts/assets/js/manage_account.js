document.addEventListener('DOMContentLoaded', function () {
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    const federationForm = document.getElementById('addSkFedForm');
    const officialsForm = document.getElementById('addSkOfficialsForm');
    const federationEditForm = document.getElementById('editAccountForm');
    const officialsEditForm = document.getElementById('editSkOfficialsForm');
    const editButtons = document.querySelectorAll('.btn-edit-account');
    const viewButtons = document.querySelectorAll('.btn-view-account');

    // Pagination System
    const recordsPerPage = 10;
    let currentPage = 1;
    let allAccounts = [];
    let filteredAccounts = [];

    // Pagination Elements
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const paginationNumbers = document.getElementById('paginationNumbers');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableBody = document.querySelector('.accounts-table tbody');

    // Initialize pagination
    function initializePagination() {
        // Get all account rows from the table
        const accountRows = Array.from(tableBody.querySelectorAll('tr')).filter(row => !row.querySelector('td[colspan]'));
        
        allAccounts = accountRows.map((row, index) => ({
            element: row,
            index: index
        }));

        filteredAccounts = [...allAccounts];
        
        if (allAccounts.length > 0) {
            updatePagination();
        }
    }

    // Update pagination display
    function updatePagination() {
        const totalPages = Math.ceil(filteredAccounts.length / recordsPerPage);
        const startIndex = (currentPage - 1) * recordsPerPage;
        const endIndex = Math.min(startIndex + recordsPerPage, filteredAccounts.length);

        // Hide all rows first
        allAccounts.forEach(account => {
            account.element.style.display = 'none';
        });

        // Show only current page rows
        for (let i = startIndex; i < endIndex; i++) {
            if (filteredAccounts[i]) {
                filteredAccounts[i].element.style.display = '';
            }
        }

        // Update pagination info
        const showingStart = filteredAccounts.length > 0 ? startIndex + 1 : 0;
        const showingEnd = endIndex;
        paginationInfo.innerHTML = `Showing <strong>${showingStart}-${showingEnd}</strong> of <strong>${filteredAccounts.length}</strong> accounts`;

        // Update page numbers
        updatePageNumbers(totalPages);

        // Update navigation buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    // Update page number buttons
    function updatePageNumbers(totalPages) {
        paginationNumbers.innerHTML = '';

        if (totalPages === 0) return;

        // Show page numbers with ellipsis for large page counts
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        // Always show first page if not in range
        if (startPage > 1) {
            addPageButton(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }

        // Show page range
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i);
        }

        // Always show last page if not in range
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            addPageButton(totalPages);
        }
    }

    // Add page button
    function addPageButton(pageNum) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `pagination-btn pagination-number ${pageNum === currentPage ? 'active' : ''}`;
        button.textContent = pageNum;
        button.setAttribute('aria-current', pageNum === currentPage ? 'page' : 'false');
        
        button.addEventListener('click', () => {
            currentPage = pageNum;
            updatePagination();
        });

        paginationNumbers.appendChild(button);
    }

    // Add ellipsis
    function addEllipsis() {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'pagination-ellipsis';
        ellipsis.textContent = '...';
        ellipsis.style.cssText = 'padding: 0 0.5rem; color: var(--gray-400); font-weight: 500;';
        paginationNumbers.appendChild(ellipsis);
    }

    // Navigation button event listeners
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredAccounts.length / recordsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
        }
    });

    // Initialize pagination when page loads
    if (tableBody) {
        initializePagination();
    }

    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', function () {
            // Update the form action to match the selected account type, then submit
            const form = accountTypeFilter.closest('form');
            if (form) {
                if (this.value === 'sk_officials') {
                    form.action = '/accounts/officials';
                } else {
                    form.action = '/accounts/federation';
                }
                // Clear barangay filter when switching types so it doesn't carry stale IDs
                const barangaySelect = document.getElementById('barangayFilter');
                if (barangaySelect) barangaySelect.value = '';
                form.submit();
            }
        });
    }

    // Auto-submit when barangay filter changes
    const barangayFilter = document.getElementById('barangayFilter');
    if (barangayFilter) {
        barangayFilter.addEventListener('change', function () {
            const form = this.closest('form');
            if (form) form.submit();
        });
    }

    // ── SK Officials UI-only submit: inject row into table ───────
    const attachSkOfficialsUISubmitHandler = function (form) {
        if (!form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Basic required-field check (mirrors add_sk_officials.js validation)
            let valid = true;
            form.querySelectorAll('[required]').forEach(el => {
                if (!el.value.trim()) {
                    valid = false;
                    el.classList.add('is-invalid');
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                const first = form.querySelector('.is-invalid');
                if (first) first.focus();
                return;
            }

            // Collect values
            const get = id => (document.getElementById(id)?.value ?? '').trim();

            const firstName  = get('official_first_name');
            const middleName = get('official_middle_name');
            const lastName   = get('official_last_name');
            const suffix     = get('official_suffix');
            const email      = get('official_email');
            const position   = get('official_position');
            const status     = get('official_status');
            const termEnd    = get('official_term_end');

            // Barangay label from selected option text
            const barangaySelect = document.getElementById('official_barangay_id');
            const barangayName   = barangaySelect?.options[barangaySelect.selectedIndex]?.text ?? '-';

            // Build display name: First MI. Last Suffix
            const mi = middleName ? middleName.charAt(0).toUpperCase() + '.' : '';
            const displayName = [firstName, mi, lastName, (suffix && suffix !== 'None') ? suffix : '']
                .filter(Boolean).join(' ');

            // Format term end date
            let termEndDisplay = '-';
            if (termEnd) {
                const d = new Date(termEnd);
                if (!isNaN(d)) {
                    termEndDisplay = d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
                }
            }

            const statusLower = status.toLowerCase();

            // Build the new <tr>
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${displayName}</td>
                <td>${email}</td>
                <td>${barangayName}</td>
                <td>${position}</td>
                <td>${termEndDisplay}</td>
                <td><span class="status-badge ${statusLower}">${status}</span></td>
                <td>
                    <div class="action-buttons-container">
                        <button type="button" class="btn-view-modern">View</button>
                        <button type="button" class="btn-edit-modern">Edit</button>
                        <button type="button" class="btn-delete-modern">Delete</button>
                    </div>
                </td>
            `;

            // Remove "No accounts found" empty row if present
            const emptyRow = tableBody?.querySelector('td[colspan]');
            if (emptyRow) emptyRow.closest('tr').remove();

            // Append to table body and refresh pagination
            if (tableBody) {
                tableBody.appendChild(tr);
                allAccounts.push({ element: tr, index: allAccounts.length });
                filteredAccounts = [...allAccounts];
                currentPage = Math.ceil(filteredAccounts.length / recordsPerPage);
                updatePagination();
            }

            // Close modal and show success
            closeAddSkOfficialsModal();
            showSkOfficialsSuccessModal();
        });
    };

    const attachSubmitHandler = function (form) {        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);

            const formData = new FormData(form);
            const payload = {};

            for (const [key, value] of formData.entries()) {
                if (key !== '_token') {
                    payload[key] = value;
                }
            }

            const positionMap = {
                sk_chairman: 'Chairman',
                sk_councilor: 'Councilor',
                sk_kagawad: 'Kagawad',
                sk_treasurer: 'Treasurer',
                sk_secretary: 'Secretary',
                sk_auditor: 'Auditor',
                sk_pio: 'PIO'
            };

            const normalizedStatus = String(payload.status || '')
                .trim()
                .toUpperCase()
                .replace(/\s+/g, '_');

            if (normalizedStatus) {
                payload.status = normalizedStatus;
            }

            if (payload.position && positionMap[payload.position]) {
                payload.position = positionMap[payload.position];
            }

            if (!payload.barangay_id && payload.barangay) {
                payload.barangay_id = payload.barangay;
            }

            delete payload.barangay;

            if (!payload.role) {
                payload.role = form.id === 'addSkOfficialsForm' ? 'sk_official' : 'sk_fed';
            }

            payload.term_status = payload.term_status || (payload.status === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE');

            showLoadingOverlay();

            fetch('/accounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async (response) => {
                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await response.json() : {};
                    return { ok: response.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();

                    if (!ok || !data.success) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                showFieldError(form, field, data.errors[field][0]);
                            });
                        } else {
                            alert('Failed to create account. Please try again.');
                        }
                        return;
                    }

                    closeAddAccountModal();
                    showAddSuccessModal();
                })
                .catch(() => {
                    hideLoadingOverlay();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    };

    attachSubmitHandler(federationForm);
    // SK Officials: UI-only — add row to table without backend call
    attachSkOfficialsUISubmitHandler(officialsForm);

    attachDobAgeAutoFill(federationForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(federationEditForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsEditForm, 'date_of_birth', 'age');

    const attachEditSubmitHandler = function (form) {
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            clearAllErrors(form);

            const accountId = form.dataset.accountId;
            if (!accountId) {
                alert('Unable to update account. Missing account ID.');
                return;
            }

            const formData = new FormData(form);
            const payload = {};

            for (const [key, value] of formData.entries()) {
                if (key !== '_token' && key !== '_method') {
                    payload[key] = value;
                }
            }

            payload.term_status = payload.term_status || 'ACTIVE';

            showLoadingOverlay();

            fetch(`/accounts/${accountId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async (response) => {
                    const contentType = response.headers.get('content-type') || '';
                    const data = contentType.includes('application/json') ? await response.json() : {};
                    return { ok: response.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();

                    if (!ok || !data.success) {
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                showFieldError(form, field, data.errors[field][0]);
                            });
                        } else {
                            alert('Failed to update account. Please try again.');
                        }
                        return;
                    }

                    closeEditModalByType();
                    showEditSuccessModalByType();
                })
                .catch(() => {
                    hideLoadingOverlay();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    };

    const populateEditForm = function (form, data) {
        if (!form) {
            return;
        }

        form.dataset.accountId = data.accountId || '';

        setFormFieldValue(form, 'first_name', data.firstName || '');
        setFormFieldValue(form, 'last_name', data.lastName || '');
        setFormFieldValue(form, 'middle_name', data.middleName || '');
        setFormFieldValue(form, 'suffix', data.suffix || '');
        setFormFieldValue(form, 'date_of_birth', data.dateOfBirth || '');
        setFormFieldValue(form, 'age', data.age || '');
        setFormFieldValue(form, 'contact_number', data.contactNumber || '');
        setFormFieldValue(form, 'email', data.email || '');
        setFormFieldValue(form, 'position', data.position || '');
        setFormFieldValue(form, 'barangay_id', data.barangayId || '');
        setFormFieldValue(form, 'status', data.status || 'ACTIVE');
        setFormFieldValue(form, 'term_start', data.termStart || '');
        setFormFieldValue(form, 'term_end', data.termEnd || '');
        setFormFieldValue(form, 'term_status', data.termStatus || 'ACTIVE');

        clearAllErrors(form);
    };

    const openEditModalWithData = function (button) {
        const data = button.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';

        if (isOfficials) {
            populateEditForm(officialsEditForm, data);
            openEditSkOfficialsModal();
            return;
        }

        populateEditForm(federationEditForm, data);
        openEditModal();
    };

    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            openEditModalWithData(button);
        });
    });

    // View button event listeners
    viewButtons.forEach((button) => {
        button.addEventListener('click', function () {
            openViewModalWithData(button);
        });
    });

    attachEditSubmitHandler(federationEditForm);
    attachEditSubmitHandler(officialsEditForm);
});

function setFormFieldValue(form, name, value) {
    const field = form.querySelector(`[name="${name}"]`);
    if (!field) {
        return;
    }

    field.value = value;
}

function attachDobAgeAutoFill(form, dobFieldName, ageFieldName) {
    if (!form) {
        return;
    }

    const dobField = form.querySelector(`[name="${dobFieldName}"]`);
    const ageField = form.querySelector(`[name="${ageFieldName}"]`);

    if (!dobField || !ageField) {
        return;
    }

    const updateAge = function () {
        ageField.value = calculateAge(dobField.value);
    };

    dobField.addEventListener('change', updateAge);
    dobField.addEventListener('input', updateAge);

    updateAge();
}

function calculateAge(dateOfBirthValue) {
    if (!dateOfBirthValue) {
        return '';
    }

    const dob = new Date(dateOfBirthValue);
    if (Number.isNaN(dob.getTime())) {
        return '';
    }

    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();

    const monthDiff = today.getMonth() - dob.getMonth();
    const dayDiff = today.getDate() - dob.getDate();
    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
        age -= 1;
    }

    return age >= 0 ? String(age) : '';
}

function getCurrentAccountType() {
    const accountTypeFilter = document.getElementById('accountTypeFilter');

    if (accountTypeFilter && accountTypeFilter.value) {
        return accountTypeFilter.value;
    }

    return window.location.pathname.includes('/accounts/officials') ? 'sk_officials' : 'sk_federation';
}

function getModalIdsByType(type) {
    if (type === 'sk_officials') {
        return {
            addModalId: 'addSkOfficialsModal',
            successModalId: 'skOfficialsSuccessModal'
        };
    }

    return {
        addModalId: 'addAccountModal',
        successModalId: 'addSuccessModal'
    };
}

function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        return;
    }

    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : '';
    document.documentElement.style.overflow = show ? 'hidden' : '';
}

window.openAddAccountModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.addModalId, true);
};

window.closeAddAccountModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.addModalId, false);
};

window.showAddSuccessModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.successModalId, true);
};

window.closeAddSuccessModal = function () {
    const ids = getModalIdsByType(getCurrentAccountType());
    toggleModal(ids.successModalId, false);
    window.location.reload();
};

window.openAddSkOfficialsModal = function () {
    toggleModal('addSkOfficialsModal', true);
};

window.closeAddSkOfficialsModal = function () {
    toggleModal('addSkOfficialsModal', false);
};

window.showSkOfficialsSuccessModal = function () {
    toggleModal('skOfficialsSuccessModal', true);
};

window.closeSkOfficialsSuccessModal = function () {
    toggleModal('skOfficialsSuccessModal', false);
};

window.openEditModal = function () {
    toggleModal('editAccountModal', true);
};

window.closeEditModal = function () {
    const modal = document.getElementById('editAccountModal');
    if (modal) {
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        const fb = modal.querySelector('.modal-fullscreen-btn');
        const rb = modal.querySelector('.modal-restore-btn');
        if (fb) { fb.style.display = 'inline-flex'; fb.title = 'Maximize'; }
        if (rb) rb.style.display = 'none';
    }
    toggleModal('editAccountModal', false);
};

window.showEditSuccessModal = function () {
    toggleModal('editSuccessModal', true);
};

window.closeEditSuccessModal = function () {
    toggleModal('editSuccessModal', false);
    window.location.reload();
};

window.openEditSkOfficialsModal = function () {
    toggleModal('editSkOfficialsModal', true);
};

window.closeEditSkOfficialsModal = function () {
    const modal = document.getElementById('editSkOfficialsModal');
    if (modal) {
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        const fb = modal.querySelector('.modal-fullscreen-btn');
        const rb = modal.querySelector('.modal-restore-btn');
        if (fb) { fb.style.display = 'inline-flex'; fb.title = 'Maximize'; }
        if (rb) rb.style.display = 'none';
    }
    toggleModal('editSkOfficialsModal', false);
};

window.showEditSkOfficialsSuccessModal = function () {
    toggleModal('editSkOfficialsSuccessModal', true);
};

window.closeEditSkOfficialsSuccessModal = function () {
    toggleModal('editSkOfficialsSuccessModal', false);
    window.location.reload();
};

function closeEditModalByType() {
    if (getCurrentAccountType() === 'sk_officials') {
        closeEditSkOfficialsModal();
        return;
    }

    closeEditModal();
}

function showEditSuccessModalByType() {
    if (getCurrentAccountType() === 'sk_officials') {
        showEditSkOfficialsSuccessModal();
        return;
    }

    showEditSuccessModal();
}

function showFieldError(form, fieldName, message) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return;

    field.classList.add('error');
    const errorElement = field.parentElement.querySelector('.form-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearAllErrors(form) {
    form.querySelectorAll('.form-input-modern').forEach((field) => {
        field.classList.remove('error');
    });

    form.querySelectorAll('.form-error').forEach((errorElement) => {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    });
}

function showLoadingOverlay() {
    let overlay = document.getElementById('loadingOverlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Processing...</p>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (!overlay) return;

    overlay.style.display = 'none';
    document.body.style.overflow = '';
}

// View Modal Functions
function openViewModalWithData(button) {
    const data = button.dataset;
    const isOfficials = getCurrentAccountType() === 'sk_officials';

    // Populate personal information
    const fullName = [data.firstName, data.middleName, data.lastName, data.suffix]
        .filter(val => val && val.trim() !== '')
        .join(' ');
    
    document.getElementById('viewFullName').textContent = fullName || '-';
    document.getElementById('viewEmail').textContent = data.email || '-';
    document.getElementById('viewDateOfBirth').textContent = data.dateOfBirth ? formatDate(data.dateOfBirth) : '-';
    document.getElementById('viewAge').textContent = data.age || '-';
    document.getElementById('viewContactNumber').textContent = data.contactNumber || '-';
    document.getElementById('viewEmailVerification').textContent = data.emailVerifiedAt || 'Not Verified';

    // Populate location information
    document.getElementById('viewBarangay').textContent = data.barangayName || '-';
    document.getElementById('viewMunicipality').textContent = data.municipality || '-';

    // Show/hide province and region for SK Federation
    const provinceContainer = document.getElementById('viewProvinceContainer');
    const regionContainer = document.getElementById('viewRegionContainer');
    
    if (!isOfficials) {
        provinceContainer.style.display = 'flex';
        regionContainer.style.display = 'flex';
        document.getElementById('viewProvince').textContent = data.province || '-';
        document.getElementById('viewRegion').textContent = data.region || '-';
    } else {
        provinceContainer.style.display = 'none';
        regionContainer.style.display = 'none';
    }

    // Populate term information
    document.getElementById('viewPosition').textContent = data.position || '-';
    document.getElementById('viewTermStart').textContent = data.termStart ? formatDate(data.termStart) : '-';
    document.getElementById('viewTermEnd').textContent = data.termEnd ? formatDate(data.termEnd) : '-';
    
    // Update status badges
    const accountStatusEl = document.getElementById('viewAccountStatus');
    const termStatusEl = document.getElementById('viewTermStatus');
    
    accountStatusEl.textContent = data.status || '-';
    accountStatusEl.className = `status-badge ${data.status ? data.status.toLowerCase() : ''}`;
    
    termStatusEl.textContent = data.termStatus || 'ACTIVE';
    termStatusEl.className = `status-badge ${data.termStatus ? data.termStatus.toLowerCase() : 'active'}`;

    openViewModal();
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '-';
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

window.openViewModal = function () {
    toggleModal('viewAccountModal', true);
};

window.closeViewModal = function () {
    const modal = document.getElementById('viewAccountModal');
    if (modal) {
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        const fb = modal.querySelector('.modal-fullscreen-btn');
        const rb = modal.querySelector('.modal-restore-btn');
        if (fb) { fb.style.display = 'inline-flex'; fb.title = 'Maximize'; }
        if (rb) rb.style.display = 'none';
    }
    toggleModal('viewAccountModal', false);
};
