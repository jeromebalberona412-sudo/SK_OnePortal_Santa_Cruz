// ==============================
//   SK Federations Login - Readable JavaScript
// ==============================

/**
 * Toggle password visibility between text and password
 * Shows/hides eye icons accordingly
 */
function togglePassword() {
  const passwordField = document.getElementById('password');
  const eyeIcon = document.getElementById('eye-icon');
  const eyeOffIcon = document.getElementById('eye-off-icon');

  if (passwordField.type === 'password') {
    // Show password
    passwordField.type = 'text';
    eyeIcon.style.display = 'none';
    eyeOffIcon.style.display = 'block';
  } else {
    // Hide password
    passwordField.type = 'password';
    eyeIcon.style.display = 'block';
    eyeOffIcon.style.display = 'none';
  }
}

/**
 * Validate email format using regex
 * @param {string} email - Email address to validate
 * @returns {boolean} - True if email is valid
 */
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Display field-specific error message
 * @param {HTMLElement} field - The input field with error
 * @param {string} message - Error message to display
 */
function showFieldError(field, message) {
  // Remove any existing error for this field
  removeFieldError(field);

  // Create error element
  const errorElement = document.createElement('div');
  errorElement.className = 'field-error';
  errorElement.textContent = message;
  errorElement.style.cssText = `
    color: #ff5252;
    font-size: 0.85rem;
    margin-top: 5px;
    animation: slideDown 0.3s ease-out;
  `;

  // Add error to field's parent
  field.parentElement.appendChild(errorElement);
}

/**
 * Remove field-specific error message
 * @param {HTMLElement} field - The input field to clear error from
 */
function removeFieldError(field) {
  const existingError = field.parentElement.querySelector('.field-error');
  if (existingError) {
    existingError.remove();
  }
}

/**
 * Check password strength based on various criteria
 * @param {string} password - Password to check
 * @returns {number} - Strength score (0-5)
 */
function checkPasswordStrength(password) {
  let strength = 0;

  // Length checks
  if (password.length >= 8) strength++;
  if (password.length >= 12) strength++;

  // Character type checks
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++; // Both cases
  if (/[0-9]/.test(password)) strength++; // Has numbers
  if (/[^a-zA-Z0-9]/.test(password)) strength++; // Has special characters

  return strength;
}

/**
 * Update password field border color based on strength
 * @param {number} strength - Password strength score (0-5)
 */
function updatePasswordStrength(strength) {
  const passwordField = document.getElementById('password');
  let borderColor = '#e9ecef'; // Default gray

  switch (strength) {
    case 0:
    case 1:
      borderColor = '#ff5252'; // Weak - Red
      break;
    case 2:
    case 3:
      borderColor = '#ffc107'; // Medium - Yellow
      break;
    case 4:
    case 5:
      borderColor = '#4caf50'; // Strong - Green
      break;
  }

  // Only apply if password has content
  if (passwordField.value) {
    passwordField.style.borderColor = borderColor;
  }
}

// ==============================
//   DOM Content Loaded Event
// ==============================
document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.querySelector('.login-form');
  const emailField = document.getElementById('email');
  const passwordField = document.getElementById('password');
  const loginButton = document.querySelector('.login-btn');

  // Email field validation on blur
  emailField.addEventListener('blur', function() {
    if (this.value && !validateEmail(this.value)) {
      this.style.borderColor = '#ff5252';
      showFieldError(this, 'Please enter a valid email address');
    } else {
      this.style.borderColor = '';
      removeFieldError(this);
    }
  });

  // Password strength checker on input
  passwordField.addEventListener('input', function() {
    const password = this.value;
    const strength = checkPasswordStrength(password);
    updatePasswordStrength(strength);
  });

  // Form submission handler
  loginForm.addEventListener('submit', function(event) {
    // Validate email
    if (!validateEmail(emailField.value)) {
      event.preventDefault();
      emailField.focus();
      emailField.style.borderColor = '#ff5252';
      showFieldError(emailField, 'Please enter a valid email address');
      return false;
    }

    // Validate password length
    if (passwordField.value.length < 6) {
      event.preventDefault();
      passwordField.focus();
      passwordField.style.borderColor = '#ff5252';
      showFieldError(passwordField, 'Password must be at least 6 characters');
      return false;
    }

    // Show loading state
    loginButton.disabled = true;
    loginButton.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⚙️</span> Signing in...';
    loginButton.style.opacity = '0.7';
    loginButton.style.cursor = 'not-allowed';
  });

  // Input focus effects
  const inputFields = document.querySelectorAll('input[type="email"], input[type="password"]');
  inputFields.forEach(function(field) {
    field.addEventListener('focus', function() {
      this.parentElement.classList.add('focused');
    });

    field.addEventListener('blur', function() {
      if (!this.value) {
        this.parentElement.classList.remove('focused');
      }
    });
  });

  // Set initial focus
  if (emailField.value || passwordField.value) {
    if (!emailField.value) {
      passwordField.focus();
    }
  } else {
    emailField.focus();
  }
});

// ==============================
//   Dynamic Styles
// ==============================
const style = document.createElement('style');
style.textContent = `
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .focused {
    transform: scale(1.02);
  }

  .field-error {
    animation: slideDown 0.3s ease-out;
  }
`;
document.head.appendChild(style);

// ==============================
//   Keyboard Event Handlers
// ==============================
document.addEventListener('keydown', function(event) {
  // Enter key submission for email/password fields
  if (event.key === 'Enter' && (event.target.type === 'email' || event.target.type === 'password')) {
    const form = event.target.closest('form');
    if (form) {
      form.dispatchEvent(new Event('submit'));
    }
  }

  // Escape key to clear field
  if (event.key === 'Escape' && (event.target.type === 'email' || event.target.type === 'password')) {
    event.target.value = '';
    event.target.dispatchEvent(new Event('input'));
  }
});

// ==============================
//   Page Initialization
// ==============================
// Enable smooth scrolling
document.documentElement.style.scrollBehavior = 'smooth';

// Clean up browser history (prevent back button issues)
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}