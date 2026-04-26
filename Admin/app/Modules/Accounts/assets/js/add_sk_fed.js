// Add SK Federation Form Validation
document.addEventListener('DOMContentLoaded', function() {
    const addSkFedForm = document.getElementById('addSkFedForm');
    if (!addSkFedForm) return;

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
    setupPasswordToggle('password', 'passwordToggle');
    setupPasswordToggle('password_confirmation', 'confirmPasswordToggle');

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

    // Date validation
    function setupDateValidation(inputId, isStartDate = false) {
        const input = document.getElementById(inputId);
        if (!input) return;

        input.addEventListener('change', function(e) {
            const selectedDate = new Date(e.target.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (isStartDate) {
                // Term start: no future dates allowed
                if (selectedDate > today) {
                    showFieldError(input, 'Term start date cannot be in the future');
                    e.target.value = '';
                } else {
                    clearFieldError(input);
                    validateField(input);
                }
            } else {
                // Term end: no past dates allowed
                if (selectedDate < today) {
                    showFieldError(input, 'Term end date cannot be in the past');
                    e.target.value = '';
                } else {
                    clearFieldError(input);
                    validateField(input);
                }
            }
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
        } else if (input.id === 'contact_number' && value) {
            if (value.length < 10) {
                isValid = false;
                errorMessage = 'Contact number must be at least 10 digits';
            } else if (value.length > 20) {
                isValid = false;
                errorMessage = 'Contact number must not exceed 20 digits';
            }
        } else if (input.id === 'password' && value) {
            // Password validation - no minimum length requirement
            if (value.length === 0) {
                isValid = false;
                errorMessage = 'Password is required';
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
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        
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
        const dobInput = document.getElementById('date_of_birth');
        const ageInput = document.getElementById('age');
        
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
    setupTextOnlyInput('first_name');
    setupTextOnlyInput('last_name');
    setupTextOnlyInput('middle_name');
    setupNumbersOnlyInput('contact_number');
    setupDateValidation('term_start', true);
    setupDateValidation('term_end', false);
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
    setupDropdownLock('barangay_id');
    setupDropdownLock('position');
    setupDropdownLock('status');
    setupDropdownLock('suffix'); // Optional field but still lock if selected

    // Add real-time validation to all required fields
    const requiredFields = addSkFedForm.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) {
                validateField(field);
            }
        });
    });

    // Form submission validation
    addSkFedForm.addEventListener('submit', function(e) {
        let isFormValid = true;
        const requiredFields = addSkFedForm.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        // Additional password confirmation check
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        if (password.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            showFieldError(confirmPassword, 'Passwords do not match');
            isFormValid = false;
        }

        if (!isFormValid) {
            e.preventDefault();
            // Focus on first invalid field
            const firstInvalid = addSkFedForm.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});

// Modal functions
function closeAddAccountModal() {
    const modal = document.getElementById('addAccountModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        // Reset form
        const form = document.getElementById('addSkFedForm');
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

function closeAddSuccessModal() {
    const modal = document.getElementById('addSuccessModal');
    if (modal) {
        modal.style.display = 'none';
    }
    // Also close the main modal
    closeAddAccountModal();
    // Reload page to show new data
    location.reload();
}

// Fullscreen toggle function
function toggleFullscreenAddAccountModal() {
    console.log('toggleFullscreenAddAccountModal called');
    const modal = document.getElementById('addAccountModal');
    console.log('Modal found:', modal);
    
    if (modal) {
        const isFullscreen = modal.classList.contains('modal-fullscreen');
        const isMinimized = modal.classList.contains('modal-minimized');
        console.log('Current state - fullscreen:', isFullscreen, 'minimized:', isMinimized);
        
        if (isFullscreen) {
            // Exit fullscreen - show minimize state
            modal.classList.remove('modal-fullscreen');
            modal.classList.add('modal-minimized');
            console.log('Switched to minimized state');
            
            // Update icon to maximize (restore)
            const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
            if (fullscreenBtn) {
                fullscreenBtn.innerHTML = `
                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                `;
            }
            
            // Update tooltip
            const btnElement = modal.querySelector('.modal-fullscreen-btn');
            if (btnElement) {
                btnElement.setAttribute('title', 'Maximize');
            }
            
            // Add click to restore functionality
            const header = modal.querySelector('.modal-header');
            if (header) {
                header.addEventListener('click', function() {
                    console.log('Header clicked, restoring modal');
                    restoreAddAccountModal();
                }, { once: true });
            }
        } else if (isMinimized) {
            // Restore from minimized
            console.log('Restoring from minimized state');
            restoreAddAccountModal();
        } else {
            // Enter fullscreen
            modal.classList.add('modal-fullscreen');
            console.log('Switched to fullscreen state');
            
            // Update icon to minus (minimize)
            const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
            if (fullscreenBtn) {
                fullscreenBtn.innerHTML = `
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                `;
            }
            
            // Update tooltip
            const btnElement = modal.querySelector('.modal-fullscreen-btn');
            if (btnElement) {
                btnElement.setAttribute('title', 'Minimize');
            }
        }
    }
}

// Restore function
function restoreAddAccountModal() {
    console.log('restoreAddAccountModal called');
    const modal = document.getElementById('addAccountModal');
    console.log('Modal found:', modal);
    
    if (modal) {
        modal.classList.remove('modal-minimized');
        modal.classList.remove('modal-fullscreen');
        console.log('Modal restored to normal state');
        
        // Update icon to fullscreen
        const fullscreenBtn = modal.querySelector('.modal-fullscreen-btn svg');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = `
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            `;
        }
        
        // Update tooltip
        const btnElement = modal.querySelector('.modal-fullscreen-btn');
        if (btnElement) {
            btnElement.setAttribute('title', 'Fullscreen');
        }
    }
}

// Make functions globally accessible for onclick handlers
window.toggleFullscreenAddAccountModal = toggleFullscreenAddAccountModal;
window.restoreAddAccountModal = restoreAddAccountModal;
window.closeAddAccountModal = closeAddAccountModal;
window.closeAddSuccessModal = closeAddSuccessModal;

// ── Resize toggle (maximize ↔ restore-down) ──────────────────
let addFedIsMaximized = false;

window.toggleAddFedSize = function () {
    const overlay = document.getElementById('addAccountModal');
    const content = document.getElementById('addSkFedModalContent');
    const icon    = document.getElementById('addFedResizeIcon');
    const btn     = document.getElementById('addFedResizeBtn');

    if (!overlay || !content || !icon) return;

    addFedIsMaximized = !addFedIsMaximized;

    if (addFedIsMaximized) {
        content.style.width        = '100vw';
        content.style.maxWidth     = '100vw';
        content.style.height       = '100vh';
        content.style.maxHeight    = '100vh';
        content.style.borderRadius = '0';
        overlay.style.padding      = '0';
        btn.title = 'Restore Down';
        icon.innerHTML = `
            <rect x="3" y="7" width="11" height="11" rx="1.5"/>
            <path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"/>
        `;
    } else {
        content.style.cssText = '';
        overlay.style.padding = '';
        btn.title = 'Maximize';
        icon.innerHTML = `
            <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
            <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
            <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
            <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
        `;
    }
};

// Reset size state when modal closes
const _origCloseAddAccountModal = window.closeAddAccountModal;
window.closeAddAccountModal = function () {
    addFedIsMaximized = false;
    const content = document.getElementById('addSkFedModalContent');
    if (content) content.style.cssText = '';
    _origCloseAddAccountModal && _origCloseAddAccountModal();
};
