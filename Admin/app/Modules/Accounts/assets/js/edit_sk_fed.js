// Edit SK Federation Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const editAccountForm = document.getElementById('editAccountForm');
    if (!editAccountForm) return;

    // Password toggle functionality
    function setupPasswordToggle(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const toggleBtn = document.getElementById(toggleId);
        
        if (!passwordInput || !toggleBtn) return;

        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update icon
            const svg = toggleBtn.querySelector('svg');
            if (type === 'text') {
                svg.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                svg.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        });
    }

    // Setup password toggles
    setupPasswordToggle('edit_password', 'editPasswordToggle');
    setupPasswordToggle('edit_password_confirmation', 'editConfirmPasswordToggle');

    // Text-only validation for name fields
    function setupTextOnlyInput(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('input', function(e) {
            // Remove numbers and special characters except spaces, hyphens, and apostrophes
            const value = e.target.value.replace(/[^a-zA-Z\s\-']/g, '');
            e.target.value = value;
            
            validateField(input);
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const cleanedData = pastedData.replace(/[^a-zA-Z\s\-']/g, '');
            input.value = cleanedData;
            validateField(input);
        });
    }

    // Numbers-only validation for contact number
    function setupNumbersOnlyInput(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('input', function(e) {
            // Only allow numbers
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            
            validateField(input);
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const cleanedData = pastedData.replace(/[^0-9]/g, '');
            input.value = cleanedData;
            validateField(input);
        });
    }

    // Field validation
    function validateField(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Remove existing error/success classes
        input.classList.remove('is-invalid', 'is-valid');
        clearFieldError(input);

        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        } else if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        } else if (input.id === 'edit_contact_number' && value) {
            if (value.length < 10) {
                isValid = false;
                errorMessage = 'Contact number must be at least 10 digits';
            } else if (value.length > 20) {
                isValid = false;
                errorMessage = 'Contact number must not exceed 20 digits';
            }
        } else if (input.id === 'edit_password' && value) {
            if (value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters';
            }
        }

        if (!isValid) {
            input.classList.add('is-invalid');
            showFieldError(input, errorMessage);
        } else if (value) {
            input.classList.add('is-valid');
        }

        return isValid;
    }

    function showFieldError(input, message) {
        clearFieldError(input);
        const error = document.createElement('span');
        error.className = 'validation-error';
        error.textContent = message;
        input.parentNode.appendChild(error);
    }

    function clearFieldError(input) {
        const existingError = input.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
    }

    // Password confirmation validation
    function validatePasswordConfirmation() {
        const password = document.getElementById('edit_password');
        const confirmPassword = document.getElementById('edit_password_confirmation');
        
        if (!password || !confirmPassword) return;

        confirmPassword.addEventListener('input', function() {
            if (confirmPassword.value && password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                showFieldError(confirmPassword, 'Passwords do not match');
            } else if (confirmPassword.value) {
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
                clearFieldError(confirmPassword);
            }
        });
    }

    // Auto-calculate age from date of birth
    function setupAgeCalculation() {
        const dobInput = document.getElementById('edit_date_of_birth');
        const ageInput = document.getElementById('edit_age');
        
        if (!dobInput || !ageInput) return;

        dobInput.addEventListener('change', function() {
            const dob = new Date(dobInput.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            ageInput.value = age >= 0 ? age : '';
            validateField(ageInput);
        });
    }

    // Initialize all validations
    setupTextOnlyInput('edit_first_name');
    setupTextOnlyInput('edit_last_name');
    setupTextOnlyInput('edit_middle_name');
    setupNumbersOnlyInput('edit_contact_number');
    setupAgeCalculation();
    validatePasswordConfirmation();

    // Setup dropdown lock functionality
    function setupDropdownLock(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.addEventListener('change', function() {
            if (this.value !== "") {
                // Disable the placeholder option
                const placeholderOption = this.querySelector('option[value=""]');
                if (placeholderOption) {
                    placeholderOption.disabled = true;
                }
            }
        });
    }

    // Initialize dropdown locks for all required selects
    setupDropdownLock('edit_barangay_id');
    setupDropdownLock('edit_position');
    setupDropdownLock('edit_status');
    setupDropdownLock('edit_suffix'); // Optional field but still lock if selected

    // Add real-time validation to all required fields
    const requiredFields = editAccountForm.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) {
                validateField(field);
            }
        });
    });

    // Form submission validation
    editAccountForm.addEventListener('submit', function(e) {
        let isFormValid = true;
        const requiredFields = editAccountForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        // Additional password confirmation check
        const password = document.getElementById('edit_password');
        const confirmPassword = document.getElementById('edit_password_confirmation');
        if (password.value && password.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            showFieldError(confirmPassword, 'Passwords do not match');
            isFormValid = false;
        }

        if (!isFormValid) {
            e.preventDefault();
            // Focus on first invalid field
            const firstInvalid = editAccountForm.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});

// Modal functions
function closeEditModal() {
    const modal = document.getElementById('editAccountModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        // Reset form
        const form = document.getElementById('editAccountForm');
        if (form) {
            form.reset();
            // Clear all validation states
            form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
                field.classList.remove('is-invalid', 'is-valid');
            });
            form.querySelectorAll('.validation-error').forEach(error => {
                error.remove();
            });
        }
    }
}

function closeEditSuccessModal() {
    const modal = document.getElementById('editSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
    // Also close the main modal
    closeEditModal();
    // Reload page to show updated data
    location.reload();
}

// Fullscreen toggle function
function toggleFullscreenEditAccountModal() {
    const modal = document.getElementById('editAccountModal');
    if (!modal) return;
    const isFullscreen = modal.classList.contains('modal-fullscreen');
    if (isFullscreen) {
        modal.classList.remove('modal-fullscreen');
        _setEditFedBtns(modal, 'normal');
    } else {
        modal.classList.remove('modal-minimized');
        modal.classList.add('modal-fullscreen');
        _setEditFedBtns(modal, 'fullscreen');
    }
}

// Restore-down button handler
function toggleRestoreEditAccountModal() {
    const modal = document.getElementById('editAccountModal');
    if (!modal) return;
    modal.classList.remove('modal-fullscreen');
    _setEditFedBtns(modal, 'normal');
}

function _setEditFedBtns(modal, state) {
    const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn');
    const restoreBtn = modal.querySelector('.modal-restore-btn');
    if (state === 'fullscreen') {
        if (fullscreenBtn) { fullscreenBtn.title = 'Restore Down'; fullscreenBtn.style.display = 'none'; }
        if (restoreBtn)    { restoreBtn.style.display = 'inline-flex'; }
    } else {
        if (fullscreenBtn) { fullscreenBtn.title = 'Maximize'; fullscreenBtn.style.display = 'inline-flex'; }
        if (restoreBtn)    { restoreBtn.style.display = 'none'; }
    }
}

// Legacy alias
function restoreEditAccountModal() { toggleRestoreEditAccountModal(); }

// Make functions globally accessible for onclick handlers
window.toggleFullscreenEditAccountModal = toggleFullscreenEditAccountModal;
window.toggleRestoreEditAccountModal = toggleRestoreEditAccountModal;
window.restoreEditAccountModal = restoreEditAccountModal;
window.closeEditModal = closeEditModal;
window.closeEditSuccessModal = closeEditSuccessModal;
