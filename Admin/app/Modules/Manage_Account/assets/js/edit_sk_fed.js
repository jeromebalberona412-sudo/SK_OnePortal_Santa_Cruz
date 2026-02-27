// Edit SK Federation Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Sample data for SK Federation accounts
    const skFedData = [
        {
            full_name: 'Jerome Balberona',
            email: 'jerome.balberona@example.com',
            barangay: 'San Roque',
            municipality: 'San Pablo City',
            province: 'Laguna',
            region: 'Region IV-A (CALABARZON)',
            position: 'sk_chairman',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        },
        {
            full_name: 'Maria Santos',
            email: 'maria.santos@example.com',
            barangay: 'Santo Niño',
            municipality: 'San Pablo City',
            province: 'Laguna',
            region: 'Region IV-A (CALABARZON)',
            position: 'sk_kagawad',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        },
        {
            full_name: 'John Cruz',
            email: 'john.cruz@example.com',
            barangay: 'San Antonio',
            municipality: 'Calauan',
            province: 'Laguna',
            region: 'Region IV-A (CALABARZON)',
            position: 'sk_treasurer',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        }
    ];

    // Format date for input field
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

    // Make openEditModal globally accessible
    window.openEditModal = function(accountType, index) {
        // Use the global tableData from manage_account.js
        const data = window.tableData && window.tableData[accountType] ? window.tableData[accountType].data[index] : null;
        if (!data) return;

        // Populate form fields with data
        document.getElementById('edit_full_name').value = data[0] || '';
        document.getElementById('edit_email').value = data[1] || '';
        document.getElementById('edit_barangay').value = data[2] || '';
        document.getElementById('edit_municipality').value = data[3] || '';
        document.getElementById('edit_province').value = data[4] || '';
        document.getElementById('edit_region').value = data[5] || '';
        document.getElementById('edit_position').value = data[6] || '';
        document.getElementById('edit_term_start').value = formatDateForInput(data[7]) || '';
        document.getElementById('edit_term_end').value = formatDateForInput(data[8]) || '';
        document.getElementById('edit_status').value = data[9]?.toLowerCase() || '';

        // Clear password fields
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';

        // Show the modal
        const modal = document.getElementById('editAccountModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Store current editing data
            modal.currentAccountType = accountType;
            modal.currentIndex = index;
            
            // Clear any existing errors
            clearEditFormErrors();
        }
    };

    // Make closeEditModal globally accessible
    window.closeEditModal = function() {
        const modal = document.getElementById('editAccountModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Reset form
            const form = document.getElementById('editAccountForm');
            if (form) {
                form.reset();
                clearEditFormErrors();
            }
        }
    };

    // Make closeEditSuccessModal globally accessible
    window.closeEditSuccessModal = function() {
        const modal = document.getElementById('editSuccessModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Clear edit form errors
    function clearEditFormErrors() {
        const form = document.getElementById('editAccountForm');
        if (!form) return;

        form.querySelectorAll('.form-input-modern').forEach(field => {
            field.classList.remove('error');
        });
        form.querySelectorAll('.form-error').forEach(error => {
            error.textContent = '';
            error.classList.remove('show');
        });
    }

    // Form validation rules for edit form
    const editValidationRules = {
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
            required: false,
            minLength: 8,
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
            message: 'Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character'
        },
        password_confirmation: {
            required: false,
            match: 'edit_password',
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
            dateAfter: 'edit_term_start',
            message: 'Term end date must be after term start date'
        },
        status: {
            required: true,
            message: 'Please select a status'
        }
    };

    // Validate single field for edit form
    function validateEditField(fieldName, value) {
        const rules = editValidationRules[fieldName];
        if (!rules) return { valid: true };
        
        // Required validation (skip password fields if empty)
        if (rules.required && (!value || value.trim() === '')) {
            if (fieldName !== 'password' && fieldName !== 'password_confirmation') {
                return { valid: false, message: `${fieldName.replace('_', ' ')} is required` };
            }
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

    // Show field error for edit form
    function showEditFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }

    // Clear field error for edit form
    function clearEditFieldError(fieldName) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.remove('error');
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }

    // Validate entire edit form
    function validateEditForm() {
        let isValid = true;
        const form = document.getElementById('editAccountForm');
        if (!form) return false;

        const formData = new FormData(form);
        
        for (let [fieldName, value] of formData.entries()) {
            if (fieldName !== '_token') { // Skip CSRF token
                const validation = validateEditField(fieldName, value);
                if (!validation.valid) {
                    showEditFieldError(fieldName, validation.message);
                    isValid = false;
                } else {
                    clearEditFieldError(fieldName);
                }
            }
        }
        
        return isValid;
    }

    // Handle edit form submission
    const editForm = document.getElementById('editAccountForm');
    if (editForm) {
        // Add input event listeners for real-time validation
        editForm.addEventListener('input', function(e) {
            const fieldName = e.target.name;
            if (fieldName && editValidationRules[fieldName]) {
                const validation = validateEditField(fieldName, e.target.value);
                if (!validation.valid) {
                    showEditFieldError(fieldName, validation.message);
                } else {
                    clearEditFieldError(fieldName);
                }
            }
        });

        // Add blur event listeners for validation on field exit
        editForm.addEventListener('blur', function(e) {
            const fieldName = e.target.name;
            if (fieldName && editValidationRules[fieldName]) {
                const validation = validateEditField(fieldName, e.target.value);
                if (!validation.valid) {
                    showEditFieldError(fieldName, validation.message);
                } else {
                    clearEditFieldError(fieldName);
                }
            }
        }, true);

        // Handle form submission
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateEditForm()) {
                // Scroll to first error
                const firstError = editForm.querySelector('.form-error.show');
                if (firstError) {
                    firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            
            // Show loading overlay
            showEditLoadingOverlay();
            
            // Get form data
            const formData = new FormData(editForm);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (key !== '_token') {
                    data[key] = value;
                }
            }
            
            // Remove password fields if empty
            if (!data.password) {
                delete data.password;
                delete data.password_confirmation;
            }
            
            // API call to update form (simulated)
            setTimeout(() => {
                hideEditLoadingOverlay();
                
                // Close edit modal
                closeEditModal();
                
                // Show success modal
                showEditSuccessModal();
                
                console.log('Updated account data:', data);
            }, 1500); // Simulate API call
        });
    }

    // Loading overlay functions for edit
    function showEditLoadingOverlay() {
        let overlay = document.getElementById('editLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'editLoadingOverlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Updating account...</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function hideEditLoadingOverlay() {
        const overlay = document.getElementById('editLoadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Show success modal
    function showEditSuccessModal() {
        const modal = document.getElementById('editSuccessModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    // Close modal on overlay click
    const editModal = document.getElementById('editAccountModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    }

    // Close success modal on overlay click
    const editSuccessModal = document.getElementById('editSuccessModal');
    if (editSuccessModal) {
        editSuccessModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSuccessModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('editAccountModal');
            const successModal = document.getElementById('editSuccessModal');
            
            if (modal && modal.style.display === 'flex') {
                closeEditModal();
            } else if (successModal && successModal.style.display === 'flex') {
                closeEditSuccessModal();
            }
        }
    });

    // Auto-format date inputs for edit form
    const editDateInputs = document.querySelectorAll('#editAccountForm input[type="date"]');
    editDateInputs.forEach(input => {
        // Set max date to today for term_start
        if (input.name === 'term_start') {
            const today = new Date().toISOString().split('T')[0];
            input.max = today;
        }
        
        // Set min date for term_end to be term_start
        if (input.name === 'term_end') {
            const termStartInput = document.getElementById('edit_term_start');
            if (termStartInput) {
                termStartInput.addEventListener('change', function() {
                    if (this.value) {
                        const startDate = new Date(this.value);
                        startDate.setDate(startDate.getDate() + 1); // Minimum next day
                        input.min = startDate.toISOString().split('T')[0];
                    }
                });
            }
        }
    });

    // Password strength indicator for edit form
    const editPasswordInput = document.getElementById('edit_password');
    const editConfirmInput = document.getElementById('edit_password_confirmation');
    
    if (editPasswordInput && editConfirmInput) {
        editPasswordInput.addEventListener('input', function() {
            const strength = checkEditPasswordStrength(this.value);
            updateEditPasswordStrengthIndicator(strength);
            
            // Re-validate confirmation if it has value
            if (editConfirmInput.value) {
                const validation = validateEditField('password_confirmation', editConfirmInput.value);
                if (!validation.valid) {
                    showEditFieldError('password_confirmation', validation.message);
                } else {
                    clearEditFieldError('password_confirmation');
                }
            }
        });
    }
    
    function checkEditPasswordStrength(password) {
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
    
    function updateEditPasswordStrengthIndicator(strength) {
        const colors = ['#dc3545', '#ffc107', '#28a745', '#007bff', '#28a745'];
        const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        console.log(`Password strength: ${messages[strength]} (${strength}/4)`);
    }
});