// Modal functions
function openEditModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editProfileModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Auto-calculate age from date of birth
document.getElementById('edit_date_of_birth')?.addEventListener('change', function() {
    const dob = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    document.getElementById('edit_age').value = age >= 0 ? age : '';
});

// Validate contact number - only allow numbers (11 digits)
document.getElementById('edit_contact_number')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
    }
});

// Validate name fields - only letters and spaces
const textOnlyFields = ['edit_last_name', 'edit_first_name', 'edit_middle_initial'];
textOnlyFields.forEach(id => {
    const element = document.getElementById(id);
    element?.addEventListener('input', function() {
        // Allow only letters (including accented characters), spaces, hyphens, and apostrophes
        this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-'\.]/g, '');
    });
});

// Validate name fields - total 150 characters limit
const nameFieldIds = ['edit_last_name', 'edit_first_name', 'edit_middle_initial', 'edit_suffix'];

function updateNameCharCount() {
    let total = 0;
    nameFieldIds.forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            total += field.value.length;
        }
    });
    return total;
}

nameFieldIds.forEach(id => {
    const element = document.getElementById(id);
    element?.addEventListener('input', function() {
        const currentTotal = updateNameCharCount();
        if (currentTotal > 150) {
            // Prevent further input if total exceeds 150
            const excess = currentTotal - 150;
            this.value = this.value.slice(0, this.value.length - excess);
            updateNameCharCount();
        }
    });
});

// Handle form submission (prototype only)
document.getElementById('editProfileForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Close edit modal
    closeEditModal();
    
    // Show success modal
    showSuccessModal();
});

// Handle password change form submission (prototype only)
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    
    // Reset custom validity
    currentPassword.setCustomValidity('');
    newPassword.setCustomValidity('');
    confirmPassword.setCustomValidity('');
    
    // Validate current password length
    if (currentPassword.value.length < 8) {
        currentPassword.setCustomValidity('Current password must be at least 8 characters long.');
        currentPassword.reportValidity();
        return;
    }
    
    if (currentPassword.value.length > 150) {
        currentPassword.setCustomValidity('Current password must not exceed 150 characters.');
        currentPassword.reportValidity();
        return;
    }
    
    // Validate new password length
    if (newPassword.value.length < 8) {
        newPassword.setCustomValidity('New password must be at least 8 characters long.');
        newPassword.reportValidity();
        return;
    }
    
    if (newPassword.value.length > 150) {
        newPassword.setCustomValidity('New password must not exceed 150 characters.');
        newPassword.reportValidity();
        return;
    }
    
    // Validate passwords match
    if (newPassword.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity('Passwords do not match. Please make sure both passwords are the same.');
        confirmPassword.reportValidity();
        return;
    }
    
    // Validate new password is different from current
    if (currentPassword.value === newPassword.value) {
        newPassword.setCustomValidity('New password must be different from current password.');
        newPassword.reportValidity();
        return;
    }
    
    // All validations passed - show password success modal
    showPasswordSuccessModal();
});

// Real-time validation for password confirmation
document.getElementById('new_password_confirmation')?.addEventListener('input', function() {
    const newPassword = document.getElementById('new_password');
    if (this.value && newPassword.value !== this.value) {
        this.setCustomValidity('Passwords do not match.');
    } else {
        this.setCustomValidity('');
    }
});

// Clear custom validity on input
document.getElementById('new_password')?.addEventListener('input', function() {
    this.setCustomValidity('');
    const confirmPassword = document.getElementById('new_password_confirmation');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});

// Password visibility toggle function
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Show success modal function
function showSuccessModal() {
    const successModal = document.getElementById('successModal');
    successModal.style.display = 'flex';
    successModal.classList.add('show');
    
    // Auto-hide after 3 seconds with smooth fade out
    setTimeout(() => {
        successModal.classList.remove('show');
        successModal.classList.add('hide');
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            successModal.style.display = 'none';
            successModal.classList.remove('hide');
        }, 300);
    }, 3000);
}

// Show password success modal and logout
function showPasswordSuccessModal() {
    const passwordModal = document.getElementById('passwordSuccessModal');
    passwordModal.style.display = 'flex';
    passwordModal.classList.add('show');
    
    // Auto-logout after 3 seconds
    setTimeout(() => {
        passwordModal.classList.remove('show');
        passwordModal.classList.add('hide');
        
        // Logout after animation completes by submitting the logout form
        setTimeout(() => {
            // Find and submit the logout form
            const logoutForm = document.querySelector('form[action*="logout"]');
            if (logoutForm) {
                logoutForm.submit();
            } else {
                // Fallback: create and submit a logout form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.logoutRoute || '/logout';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }
                
                document.body.appendChild(form);
                form.submit();
            }
        }, 300);
    }, 3000);
}

// Show forgot password confirmation modal
function showForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Close forgot password modal
function closeForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Proceed to forgot password - logout and redirect
async function proceedToForgotPassword() {
    // Show loading screen
    if (typeof LoadingScreen !== 'undefined') {
        LoadingScreen.show('Redirecting', 'Logging out and redirecting to password reset...');
    }
    
    try {
        // Perform logout via fetch
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        await fetch(window.logoutRoute || '/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
        
        // After logout, redirect to forgot password page
        window.location.href = window.forgotPasswordRoute || '/forgot-password';
    } catch (error) {
        // Fallback: if fetch fails, use form submission
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.logoutRoute || '/logout';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        document.body.appendChild(form);
        form.submit();
        
        // Redirect after brief delay
        setTimeout(() => {
            window.location.href = window.forgotPasswordRoute || '/forgot-password';
        }, 200);
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const editModal = document.getElementById('editProfileModal');
    const forgotModal = document.getElementById('forgotPasswordModal');
    
    if (event.target === editModal) {
        closeEditModal();
    }
    if (event.target === forgotModal) {
        closeForgotPasswordModal();
    }
});
