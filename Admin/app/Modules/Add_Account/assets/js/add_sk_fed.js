// Loading Overlay Functions
function showLoadingOverlay() {
    // Create overlay if it doesn't exist
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Creating account...</p>
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

// Add SK Fed Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addSkFedForm');
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
        fetch('/admin/add-sk-fed', {
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
                // Show success modal
                showSuccessModal();
                
                // Reset form after successful submission
                form.reset();
                clearAllErrors();
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
        
        // Stay on current page instead of redirecting
        // Reset form after successful submission
        const form = document.getElementById('addSkFedForm');
        if (form) {
            form.reset();
            clearAllErrors();
        }
    };
    
    // Close modal on overlay click
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSuccessModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('successModal');
            if (modal.style.display === 'flex') {
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
    // Redirect to the Add SK Fed page
    window.location.href = '/admin/add-sk-fed';
};