// Account Type Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');
    const addButtonText = document.getElementById('addButtonText');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    // Table data for different account types
    const tableData = {
        sk_federation: {
            title: 'Manage SK Federation Account',
            subtitle: 'Create or manage SK Federation member accounts',
            buttonText: 'Add SK Federation',
            searchPlaceholder: 'Search SK Federation accounts...',
            headers: {
                personal: 'Personal Information',
                location: 'Location Information', 
                term: 'Term Information'
            },
            columns: ['Full Name', 'Email Address', 'Barangay', 'Municipality', 'Province', 'Region', 'Position (SK Role)', 'Term Start', 'Term End', 'Status', 'Actions'],
            data: [
                ['Jerome Balberona', 'jerome.balberona@example.com', 'San Roque', 'San Pablo City', 'Laguna', 'Region IV-A (CALABARZON)', 'SK Chairman', '01/01/2023', '12/31/2025', 'Active'],
                ['Maria Santos', 'maria.santos@example.com', 'Santo Niño', 'San Pablo City', 'Laguna', 'Region IV-A (CALABARZON)', 'SK Kagawad', '01/01/2023', '12/31/2025', 'Active'],
                ['John Cruz', 'john.cruz@example.com', 'San Antonio', 'Calauan', 'Laguna', 'Region IV-A (CALABARZON)', 'SK Treasurer', '01/01/2023', '12/31/2025', 'Active']
            ]
        },
        sk_officials: {
            title: 'Manage SK Officials Account',
            subtitle: 'Create or manage SK Officials member accounts',
            buttonText: 'Add SK Officials',
            searchPlaceholder: 'Search SK Officials accounts...',
            headers: {
                personal: 'Personal Information',
                location: 'Location Information',
                term: 'Term Information'
            },
            columns: ['Full Name', 'Email', 'SK Role', 'Barangay', 'Municipality', 'Term Start', 'Term End', 'Status', 'Actions'],
            data: [
                ['Ana Martinez', 'ana.martinez@example.com', 'SK Chairman', 'Santa Cruz', 'Santa Cruz', '01/01/2023', '12/31/2025', 'Active'],
                ['Carlos Reyes', 'carlos.reyes@example.com', 'SK Kagawad', 'Bagumbayan', 'Santa Cruz', '01/01/2023', '12/31/2025', 'Active'],
                ['Elena Santos', 'elena.santos@example.com', 'SK Secretary', 'Poblacion', 'Santa Cruz', '01/01/2023', '12/31/2025', 'Active']
            ]
        }
    };
    
    // Initialize with SK Federation
    updateUI('sk_federation');
    
    // Handle dropdown change
    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', function() {
            const selectedType = this.value;
            updateUI(selectedType);
        });
    }
    
    function updateUI(accountType) {
        const config = tableData[accountType];
        if (!config) return;
        
        // Update page title and subtitle
        if (pageTitle) pageTitle.textContent = config.title;
        if (pageSubtitle) pageSubtitle.textContent = config.subtitle;
        if (addButtonText) addButtonText.textContent = config.buttonText;
        if (searchInput) searchInput.placeholder = config.searchPlaceholder;
        
        // Update table structure
        updateTableStructure(accountType);
    }
    
    function updateTableStructure(accountType) {
        const config = tableData[accountType];
        const table = document.querySelector('.accounts-table');
        if (!table || !config) return;
        
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        
        if (!thead || !tbody) return;
        
        // Update headers
        const colspanPersonal = accountType === 'sk_federation' ? 2 : 2;
        const colspanLocation = accountType === 'sk_federation' ? 4 : 2;
        const colspanTerm = accountType === 'sk_federation' ? 5 : 4;
        
        thead.innerHTML = `
            <tr>
                <th colspan="${colspanPersonal}" class="table-group-header">${config.headers.personal}</th>
                <th colspan="${colspanLocation}" class="table-group-header">${config.headers.location}</th>
                <th colspan="${colspanTerm}" class="table-group-header">${config.headers.term}</th>
            </tr>
            <tr>
                ${config.columns.map(col => `<th>${col}</th>`).join('')}
            </tr>
        `;
        
        // Update table body with Edit buttons
        tbody.innerHTML = config.data.map((row, index) => {
            const cells = row.map((cell, cellIndex) => {
                if (cell === 'Active' || cell === 'Inactive') {
                    return `<td><span class="status-badge ${cell.toLowerCase()}">${cell}</span></td>`;
                }
                return `<td>${cell}</td>`;
            });
            
            // Add Actions column with Edit button
            cells.push(`
                <td>
                    <button type="button" class="btn-edit-modern" onclick="openEditModal('${accountType}', ${index})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </button>
                </td>
            `);
            
            return `<tr>${cells.join('')}</tr>`;
        }).join('');
    }
    
    // Search functionality (UI Only)
    if (searchInput && searchBtn) {
        searchBtn.addEventListener('click', handleSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') handleSearch();
        });
        
        function handleSearch() {
            const searchTerm = searchInput.value.trim();
            const currentType = accountTypeFilter ? accountTypeFilter.value : 'sk_federation';
            console.log('Searching for:', searchTerm, 'in', currentType);
        }
    }
    
    // Make tableData globally accessible for edit functionality
    window.tableData = tableData;
    window.updateTableStructure = updateTableStructure;
});

// Add Account Modal Functions
window.openAddAccountModal = function() {
    const modal = document.getElementById('addAccountModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Reset form when opening modal
        const form = document.getElementById('addSkFedForm');
        if (form) {
            form.reset();
            clearAllErrors();
        }
    }
};

window.closeAddAccountModal = function() {
    const modal = document.getElementById('addAccountModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset form when closing modal
        const form = document.getElementById('addSkFedForm');
        if (form) {
            form.reset();
            clearAllErrors();
        }
    }
};

// Edit Modal Functions
window.openEditModal = function(accountType, index) {
    const config = window.tableData[accountType];
    if (!config || !config.data[index]) return;
    
    const data = config.data[index];
    
    // Determine which edit modal to use based on account type
    const modalId = accountType === 'sk_officials' ? 'editSkOfficialsModal' : 'editAccountModal';
    const modal = document.getElementById(modalId);
    
    if (modal) {
        // Populate form fields based on account type
        if (accountType === 'sk_federation') {
            populateSkFedEditForm(data);
        } else {
            populateSkOfficialsEditForm(data);
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Store current editing data
        modal.currentAccountType = accountType;
        modal.currentIndex = index;
    }
};

function populateSkFedEditForm(data) {
    document.getElementById('edit_full_name').value = data[0] || '';
    document.getElementById('edit_email').value = data[1] || '';
    document.getElementById('edit_barangay').value = data[2] || '';
    document.getElementById('edit_municipality').value = data[3] || '';
    document.getElementById('edit_province').value = data[4] || '';
    document.getElementById('edit_region').value = data[5] || '';
    document.getElementById('edit_position').value = data[6] || '';
    document.getElementById('edit_term_start').value = formatDateForInput(data[7]) || '';
    document.getElementById('edit_term_end').value = formatDateForInput(data[8]) || '';
    document.getElementById('edit_status').value = data[9]?.toLowerCase() || 'active';
}

function populateSkOfficialsEditForm(data) {
    document.getElementById('edit_sk_officials_full_name').value = data[0] || '';
    document.getElementById('edit_sk_officials_email').value = data[1] || '';
    document.getElementById('edit_sk_officials_sk_role').value = data[2] || '';
    document.getElementById('edit_sk_officials_barangay').value = data[3] || '';
    document.getElementById('edit_sk_officials_municipality').value = data[4] || '';
    document.getElementById('edit_sk_officials_term_start').value = formatDateForInput(data[5]) || '';
    document.getElementById('edit_sk_officials_term_end').value = formatDateForInput(data[6]) || '';
    document.getElementById('edit_sk_officials_status').value = data[7]?.toLowerCase() || 'active';
}

function formatDateForInput(dateString) {
    if (!dateString) return '';
    // Convert MM/DD/YYYY to YYYY-MM-DD
    const parts = dateString.split('/');
    if (parts.length === 3) {
        return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
    }
    return dateString;
}

window.closeEditModal = function() {
    const modals = ['editAccountModal', 'editSkOfficialsModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.currentAccountType = null;
            modal.currentIndex = null;
        }
    });
    document.body.style.overflow = '';
};

window.closeEditSkOfficialsModal = closeEditModal;

// Success Modal Functions
window.showAddSuccessModal = function() {
    const modal = document.getElementById('addSuccessModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.closeAddSuccessModal = function() {
    const modal = document.getElementById('addSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

window.showEditSuccessModal = function() {
    const modal = document.getElementById('editSuccessModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.closeEditSuccessModal = function() {
    const modal = document.getElementById('editSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

window.closeEditSkOfficialsSuccessModal = function() {
    const modal = document.getElementById('editSkOfficialsSuccessModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
};

// Form Submission Handlers
window.handleAddAccountSubmit = function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const currentType = document.getElementById('accountTypeFilter').value;
    
    // Create new account data
    const newAccount = [];
    
    if (currentType === 'sk_federation') {
        newAccount.push(formData.get('full_name'));
        newAccount.push(formData.get('email'));
        newAccount.push(formData.get('barangay'));
        newAccount.push(formData.get('municipality'));
        newAccount.push(formData.get('province'));
        newAccount.push(formData.get('region'));
        newAccount.push(formData.get('position'));
        newAccount.push(formatDateForDisplay(formData.get('term_start')));
        newAccount.push(formatDateForDisplay(formData.get('term_end')));
        newAccount.push('Active');
    } else {
        newAccount.push(formData.get('full_name'));
        newAccount.push(formData.get('email'));
        newAccount.push(formData.get('sk_role'));
        newAccount.push(formData.get('barangay'));
        newAccount.push(formData.get('municipality'));
        newAccount.push(formatDateForDisplay(formData.get('term_start')));
        newAccount.push(formatDateForDisplay(formData.get('term_end')));
        newAccount.push('Active');
    }
    
    // Add to table data
    window.tableData[currentType].data.push(newAccount);
    
    // Update table
    window.updateTableStructure(currentType);
    
    // Show loading overlay briefly
    showLoadingOverlay();
    
    setTimeout(() => {
        hideLoadingOverlay();
        closeAddAccountModal();
        showAddSuccessModal();
    }, 1000);
};

window.handleEditAccountSubmit = function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const modal = form.closest('.modal-overlay');
    const accountType = modal.currentAccountType;
    const index = modal.currentIndex;
    
    if (!accountType || index === undefined) return;
    
    // Update account data
    const updatedAccount = [];
    
    if (accountType === 'sk_federation') {
        updatedAccount.push(formData.get('full_name'));
        updatedAccount.push(formData.get('email'));
        updatedAccount.push(formData.get('barangay'));
        updatedAccount.push(formData.get('municipality'));
        updatedAccount.push(formData.get('province'));
        updatedAccount.push(formData.get('region'));
        updatedAccount.push(formData.get('position'));
        updatedAccount.push(formatDateForDisplay(formData.get('term_start')));
        updatedAccount.push(formatDateForDisplay(formData.get('term_end')));
        updatedAccount.push(formData.get('status'));
    } else {
        updatedAccount.push(formData.get('full_name'));
        updatedAccount.push(formData.get('email'));
        updatedAccount.push(formData.get('sk_role'));
        updatedAccount.push(formData.get('barangay'));
        updatedAccount.push(formData.get('municipality'));
        updatedAccount.push(formatDateForDisplay(formData.get('term_start')));
        updatedAccount.push(formatDateForDisplay(formData.get('term_end')));
        updatedAccount.push(formData.get('status'));
    }
    
    // Update table data
    window.tableData[accountType].data[index] = updatedAccount;
    
    // Update table
    window.updateTableStructure(accountType);
    
    // Show loading overlay briefly
    showLoadingOverlay();
    
    setTimeout(() => {
        hideLoadingOverlay();
        closeEditModal();
        
        // Show appropriate success modal
        if (accountType === 'sk_officials') {
            const successModal = document.getElementById('editSkOfficialsSuccessModal');
            if (successModal) {
                successModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        } else {
            showEditSuccessModal();
        }
    }, 1000);
};

window.handleEditSkOfficialsSubmit = handleEditAccountSubmit;

function formatDateForDisplay(dateString) {
    if (!dateString) return '';
    // Convert YYYY-MM-DD to MM/DD/YYYY
    const parts = dateString.split('-');
    if (parts.length === 3) {
        return `${parts[1]}/${parts[2]}/${parts[0]}`;
    }
    return dateString;
}

// Loading Overlay Functions
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
    if (overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Form validation helpers
function clearAllErrors() {
    const forms = ['addSkFedForm', 'addSkOfficialsForm', 'editAccountForm', 'editSkOfficialsForm'];
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.querySelectorAll('.form-input-modern').forEach(field => {
                field.classList.remove('error');
            });
            form.querySelectorAll('.form-error').forEach(error => {
                error.textContent = '';
                error.classList.remove('show');
            });
        }
    });
}

// Close modals on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        const modalId = e.target.id;
        if (modalId === 'addAccountModal') {
            closeAddAccountModal();
        } else if (modalId === 'editAccountModal' || modalId === 'editSkOfficialsModal') {
            closeEditModal();
        } else if (modalId.includes('Success')) {
            const successModals = ['addSuccessModal', 'editSuccessModal', 'editSkOfficialsSuccessModal'];
            successModals.forEach(successModalId => {
                const modal = document.getElementById(successModalId);
                if (modal && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            });
        }
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
});

// Add SK Fed Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addSkFedForm');
    
    // Only proceed if form exists (it's in the modal)
    if (!form) return;
    
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Form validation rules
    const validationRules = {
        full_name: {
            required: true,
            minLength: 2,
            maxLength: 100,
            pattern: /^[a-zA-Z\s\-'\.]+$/,
            message: 'Please enter a valid full name (letters, spaces, hyphens, apostrophes, and periods only)'
        },
        email: {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
            message: 'Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character'
        },
        password_confirmation: {
            required: true,
            match: 'password',
            message: 'Password confirmation must match the password'
        },
        barangay: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: 'Please enter a valid barangay name'
        },
        municipality: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: 'Please enter a valid municipality/city name'
        },
        province: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: 'Please enter a valid province name'
        },
        region: {
            required: true,
            minLength: 2,
            maxLength: 100,
            message: 'Please enter a valid region name'
        },
        position: {
            required: true,
            message: 'Please select a position'
        },
        term_start: {
            required: true,
            message: 'Please select a term start date'
        },
        term_end: {
            required: true,
            dateAfter: 'term_start',
            message: 'Term end date must be after term start date'
        },
        status: {
            required: true,
            message: 'Please select a status'
        }
    };
    
    // Validate single field
    function validateField(fieldName, value) {
        const rules = validationRules[fieldName];
        if (!rules) return { valid: true };
        
        // Required validation
        if (rules.required && (!value || value.trim() === '')) {
            return { valid: false, message: `${fieldName.replace('_', ' ')} is required` };
        }
        
        // Skip other validations if field is empty and not required
        if (!value || value.trim() === '') {
            return { valid: true };
        }
        
        // Length validation
        if (rules.minLength && value.length < rules.minLength) {
            return { valid: false, message: rules.message || `Must be at least ${rules.minLength} characters` };
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            return { valid: false, message: rules.message || `Must be no more than ${rules.maxLength} characters` };
        }
        
        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            return { valid: false, message: rules.message };
        }
        
        // Match validation (for password confirmation)
        if (rules.match) {
            const matchField = document.getElementById(rules.match);
            if (matchField && value !== matchField.value) {
                return { valid: false, message: rules.message };
            }
        }
        
        // Date validation
        if (rules.dateAfter) {
            const dateAfterField = document.getElementById(rules.dateAfter);
            if (dateAfterField && dateAfterField.value) {
                const startDate = new Date(dateAfterField.value);
                const endDate = new Date(value);
                if (endDate <= startDate) {
                    return { valid: false, message: rules.message };
                }
            }
        }
        
        return { valid: true };
    }
    
    // Show field error
    function showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
    
    // Clear field error
    function clearFieldError(fieldName) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.remove('error');
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
    
    // Validate entire form
    function validateForm() {
        let isValid = true;
        const formData = new FormData(form);
        
        for (let [fieldName, value] of formData.entries()) {
            if (fieldName !== '_token') { // Skip CSRF token
                const validation = validateField(fieldName, value);
                if (!validation.valid) {
                    showFieldError(fieldName, validation.message);
                    isValid = false;
                } else {
                    clearFieldError(fieldName);
                }
            }
        }
        
        return isValid;
    }
    
    // Add input event listeners for real-time validation
    form.addEventListener('input', function(e) {
        const fieldName = e.target.name;
        if (fieldName && validationRules[fieldName]) {
            const validation = validateField(fieldName, e.target.value);
            if (!validation.valid) {
                showFieldError(fieldName, validation.message);
            } else {
                clearFieldError(fieldName);
            }
        }
    });
    
    // Add blur event listeners for validation on field exit
    form.addEventListener('blur', function(e) {
        const fieldName = e.target.name;
        if (fieldName && validationRules[fieldName]) {
            const validation = validateField(fieldName, e.target.value);
            if (!validation.valid) {
                showFieldError(fieldName, validation.message);
            } else {
                clearFieldError(fieldName);
            }
        }
    }, true);
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            // Scroll to first error
            const firstError = form.querySelector('.form-error.show');
            if (firstError) {
                firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        
        // Show loading overlay
        showLoadingOverlay();
        
        // Get form data
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key !== '_token') {
                data[key] = value;
            }
        }
        
        // API call to submit form
        fetch('/manage-account', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            // Hide loading overlay
            hideLoadingOverlay();
            
            if (result.success) {
                // Close add account modal
                closeAddAccountModal();
                
                // Show success modal
                showSuccessModal();
            } else {
                // Handle validation errors from server
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        showFieldError(field, result.errors[field][0]);
                    });
                } else {
                    alert('An error occurred. Please try again.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoadingOverlay();
            alert('An error occurred. Please try again.');
        });
    });
    
    // Clear all errors
    function clearAllErrors() {
        form.querySelectorAll('.form-input-modern').forEach(field => {
            field.classList.remove('error');
        });
        form.querySelectorAll('.form-error').forEach(error => {
            error.textContent = '';
            error.classList.remove('show');
        });
    }
    
    // Show success modal
    function showSuccessModal() {
        const modal = document.getElementById('successModal');
        modal.style.display = 'flex';
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    // Close success modal
    window.closeSuccessModal = function() {
        const modal = document.getElementById('successModal');
        modal.style.display = 'none';
        
        // Restore body scroll
        document.body.style.overflow = '';
    };
    
    // Close modal on overlay click
    document.getElementById('addAccountModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddAccountModal();
        }
    });
    
    // Close success modal on overlay click
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSuccessModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const addModal = document.getElementById('addAccountModal');
            const successModal = document.getElementById('successModal');
            
            if (addModal.style.display === 'flex') {
                closeAddAccountModal();
            } else if (successModal.style.display === 'flex') {
                closeSuccessModal();
            }
        }
    });
    
    // Auto-format date inputs
    const dateInputs = form.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set max date to today
        if (input.name === 'term_start') {
            const today = new Date().toISOString().split('T')[0];
            input.max = today;
        }
        
        // Set min date for term_end to be term_start
        if (input.name === 'term_end') {
            const termStartInput = document.getElementById('term_start');
            termStartInput.addEventListener('change', function() {
                if (this.value) {
                    const startDate = new Date(this.value);
                    startDate.setDate(startDate.getDate() + 1); // Minimum next day
                    input.min = startDate.toISOString().split('T')[0];
                }
            });
        }
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    
    if (passwordInput && confirmInput) {
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrengthIndicator(strength);
            
            // Re-validate confirmation if it has value
            if (confirmInput.value) {
                const validation = validateField('password_confirmation', confirmInput.value);
                if (!validation.valid) {
                    showFieldError('password_confirmation', validation.message);
                } else {
                    clearFieldError('password_confirmation');
                }
            }
        });
    }
    
    function checkPasswordStrength(password) {
        if (!password) return 0;
        
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[@$!%*?&]/.test(password)) strength++;
        
        return Math.min(strength, 4); // Max strength of 4
    }
    
    function updatePasswordStrengthIndicator(strength) {
        // This would require adding a strength indicator to the HTML
        // For now, we'll just use the existing validation
        const colors = ['#dc3545', '#ffc107', '#28a745', '#007bff', '#28a745'];
        const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        // You can add a visual indicator if needed
        console.log(`Password strength: ${messages[strength]} (${strength}/4)`);
    }
});

// Function to open Add SK Fed modal from sidebar
window.openAddSkFedModal = function() {
    openAddAccountModal();
};  