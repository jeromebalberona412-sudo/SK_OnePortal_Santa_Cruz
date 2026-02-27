// Edit SK Officials Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Sample data for SK Officials accounts
    const skOfficialsData = [
        {
            full_name: 'Ana Martinez',
            email: 'ana.martinez@example.com',
            sk_role: 'sk_chairman',
            barangay: 'Santa Cruz',
            municipality: 'Santa Cruz',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        },
        {
            full_name: 'Carlos Reyes',
            email: 'carlos.reyes@example.com',
            sk_role: 'sk_kagawad',
            barangay: 'Bagumbayan',
            municipality: 'Santa Cruz',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        },
        {
            full_name: 'Elena Santos',
            email: 'elena.santos@example.com',
            sk_role: 'sk_secretary',
            barangay: 'Poblacion',
            municipality: 'Santa Cruz',
            term_start: '2023-01-01',
            term_end: '2025-12-31',
            status: 'active'
        }
    ];

    // Make openEditSkOfficialsModal globally accessible
    window.openEditSkOfficialsModal = function(index) {
        const data = skOfficialsData[index];
        if (!data) return;

        // Populate form fields with data
        document.getElementById('edit_sk_officials_full_name').value = data.full_name || '';
        document.getElementById('edit_sk_officials_email').value = data.email || '';
        document.getElementById('edit_sk_officials_sk_role').value = data.sk_role || '';
        document.getElementById('edit_sk_officials_barangay').value = data.barangay || '';
        document.getElementById('edit_sk_officials_municipality').value = data.municipality || '';
        document.getElementById('edit_sk_officials_term_start').value = data.term_start || '';
        document.getElementById('edit_sk_officials_term_end').value = data.term_end || '';
        document.getElementById('edit_sk_officials_status').value = data.status || '';

        // Clear password fields
        document.getElementById('edit_sk_officials_password').value = '';
        document.getElementById('edit_sk_officials_password_confirmation').value = '';

        // Show the modal
        const modal = document.getElementById('editSkOfficialsModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Clear any existing errors
            clearEditSkOfficialsFormErrors();
        }
    };

    // Make closeEditSkOfficialsModal globally accessible
    window.closeEditSkOfficialsModal = function() {
        const modal = document.getElementById('editSkOfficialsModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Reset form
            const form = document.getElementById('editSkOfficialsForm');
            if (form) {
                form.reset();
                clearEditSkOfficialsFormErrors();
            }
        }
    };

    // Make closeEditSkOfficialsSuccessModal globally accessible
    window.closeEditSkOfficialsSuccessModal = function() {
        const modal = document.getElementById('editSkOfficialsSuccessModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Clear edit form errors
    function clearEditSkOfficialsFormErrors() {
        const form = document.getElementById('editSkOfficialsForm');
        if (!form) return;

        form.querySelectorAll('.form-input-modern').forEach(field => {
            field.classList.remove('error');
        });
        form.querySelectorAll('.form-error').forEach(error => {
            error.textContent = '';
            error.classList.remove('show');
        });
    }

    // Form validation rules for edit SK Officials form
    const editSkOfficialsValidationRules = {
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
            match: 'edit_sk_officials_password',
            message: 'Password confirmation must match the password'
        },
        sk_role: {
            required: true,
            message: 'Please select an SK role'
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
            message: 'Please enter a valid municipality name'
        },
        term_start: {
            required: true,
            message: 'Please select a term start date'
        },
        term_end: {
            required: true,
            dateAfter: 'edit_sk_officials_term_start',
            message: 'Term end date must be after term start date'
        },
        status: {
            required: true,
            message: 'Please select a status'
        }
    };

    // Validate single field for edit SK Officials form
    function validateEditSkOfficialsField(fieldName, value) {
        const rules = editSkOfficialsValidationRules[fieldName];
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

    // Show field error for edit SK Officials form
    function showEditSkOfficialsFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.add('error');
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }

    // Clear field error for edit SK Officials form
    function clearEditSkOfficialsFieldError(fieldName) {
        const field = document.getElementById(fieldName);
        const errorElement = field.parentElement.querySelector('.form-error');
        
        field.classList.remove('error');
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }

    // Validate entire edit SK Officials form
    function validateEditSkOfficialsForm() {
        let isValid = true;
        const form = document.getElementById('editSkOfficialsForm');
        if (!form) return false;

        const formData = new FormData(form);
        
        for (let [fieldName, value] of formData.entries()) {
            if (fieldName !== '_token') { // Skip CSRF token
                const validation = validateEditSkOfficialsField(fieldName, value);
                if (!validation.valid) {
                    showEditSkOfficialsFieldError(fieldName, validation.message);
                    isValid = false;
                } else {
                    clearEditSkOfficialsFieldError(fieldName);
                }
            }
        }
        
        return isValid;
    }

    // Handle edit SK Officials form submission
    const editSkOfficialsForm = document.getElementById('editSkOfficialsForm');
    if (editSkOfficialsForm) {
        // Add input event listeners for real-time validation
        editSkOfficialsForm.addEventListener('input', function(e) {
            const fieldName = e.target.name;
            if (fieldName && editSkOfficialsValidationRules[fieldName]) {
                const validation = validateEditSkOfficialsField(fieldName, e.target.value);
                if (!validation.valid) {
                    showEditSkOfficialsFieldError(fieldName, validation.message);
                } else {
                    clearEditSkOfficialsFieldError(fieldName);
                }
            }
        });

        // Add blur event listeners for validation on field exit
        editSkOfficialsForm.addEventListener('blur', function(e) {
            const fieldName = e.target.name;
            if (fieldName && editSkOfficialsValidationRules[fieldName]) {
                const validation = validateEditSkOfficialsField(fieldName, e.target.value);
                if (!validation.valid) {
                    showEditSkOfficialsFieldError(fieldName, validation.message);
                } else {
                    clearEditSkOfficialsFieldError(fieldName);
                }
            }
        }, true);

        // Handle form submission
        editSkOfficialsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateEditSkOfficialsForm()) {
                // Scroll to first error
                const firstError = editSkOfficialsForm.querySelector('.form-error.show');
                if (firstError) {
                    firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            
            // Show loading overlay
            showEditSkOfficialsLoadingOverlay();
            
            // Get form data
            const formData = new FormData(editSkOfficialsForm);
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
                hideEditSkOfficialsLoadingOverlay();
                
                // Close edit modal
                closeEditSkOfficialsModal();
                
                // Show success modal
                showEditSkOfficialsSuccessModal();
                
                console.log('Updated SK Officials account data:', data);
            }, 1500); // Simulate API call
        });
    }

    // Loading overlay functions for edit SK Officials
    function showEditSkOfficialsLoadingOverlay() {
        let overlay = document.getElementById('editSkOfficialsLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'editSkOfficialsLoadingOverlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Updating SK Officials account...</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function hideEditSkOfficialsLoadingOverlay() {
        const overlay = document.getElementById('editSkOfficialsLoadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Show success modal
    function showEditSkOfficialsSuccessModal() {
        const modal = document.getElementById('editSkOfficialsSuccessModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    // Close modal on overlay click
    const editSkOfficialsModal = document.getElementById('editSkOfficialsModal');
    if (editSkOfficialsModal) {
        editSkOfficialsModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSkOfficialsModal();
            }
        });
    }

    // Close success modal on overlay click
    const editSkOfficialsSuccessModal = document.getElementById('editSkOfficialsSuccessModal');
    if (editSkOfficialsSuccessModal) {
        editSkOfficialsSuccessModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSkOfficialsSuccessModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('editSkOfficialsModal');
            const successModal = document.getElementById('editSkOfficialsSuccessModal');
            
            if (modal && modal.style.display === 'flex') {
                closeEditSkOfficialsModal();
            } else if (successModal && successModal.style.display === 'flex') {
                closeEditSkOfficialsSuccessModal();
            }
        }
    });

    // Auto-format date inputs for edit SK Officials form
    const editSkOfficialsDateInputs = document.querySelectorAll('#editSkOfficialsForm input[type="date"]');
    editSkOfficialsDateInputs.forEach(input => {
        // Set max date to today for term_start
        if (input.name === 'term_start') {
            const today = new Date().toISOString().split('T')[0];
            input.max = today;
        }
        
        // Set min date for term_end to be term_start
        if (input.name === 'term_end') {
            const termStartInput = document.getElementById('edit_sk_officials_term_start');
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

    // Password strength indicator for edit SK Officials form
    const editSkOfficialsPasswordInput = document.getElementById('edit_sk_officials_password');
    const editSkOfficialsConfirmInput = document.getElementById('edit_sk_officials_password_confirmation');
    
    if (editSkOfficialsPasswordInput && editSkOfficialsConfirmInput) {
        editSkOfficialsPasswordInput.addEventListener('input', function() {
            const strength = checkEditSkOfficialsPasswordStrength(this.value);
            updateEditSkOfficialsPasswordStrengthIndicator(strength);
            
            // Re-validate confirmation if it has value
            if (editSkOfficialsConfirmInput.value) {
                const validation = validateEditSkOfficialsField('password_confirmation', editSkOfficialsConfirmInput.value);
                if (!validation.valid) {
                    showEditSkOfficialsFieldError('password_confirmation', validation.message);
                } else {
                    clearEditSkOfficialsFieldError('password_confirmation');
                }
            }
        });
    }
    
    function checkEditSkOfficialsPasswordStrength(password) {
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
    
    function updateEditSkOfficialsPasswordStrengthIndicator(strength) {
        const colors = ['#dc3545', '#ffc107', '#28a745', '#007bff', '#28a745'];
        const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        console.log(`Password strength: ${messages[strength]} (${strength}/4)`);
    }
});