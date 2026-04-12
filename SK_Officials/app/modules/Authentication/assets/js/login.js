document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

    if (!loginForm) return;

    // Mark server-side errors so they survive the first input clear
    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) el.setAttribute('data-server-error', 'true');
    });

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showFieldError(input, errorEl, message) {
        input.classList.add('is-invalid');
        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function clearFieldError(input, errorEl) {
        input.classList.remove('is-invalid');
        errorEl.hidden = true;
    }

    emailInput.addEventListener('input', () => clearFieldError(emailInput, emailError));
    passwordInput.addEventListener('input', () => clearFieldError(passwordInput, passwordError));

    loginForm.addEventListener('submit', function (e) {
        let isValid = true;
        clearFieldError(emailInput, emailError);
        clearFieldError(passwordInput, passwordError);

        if (!emailInput.value.trim()) {
            showFieldError(emailInput, emailError, 'Email address is required.');
            isValid = false;
        } else if (!validateEmail(emailInput.value.trim())) {
            showFieldError(emailInput, emailError, 'Please enter a valid email address.');
            isValid = false;
        }

        if (!passwordInput.value) {
            showFieldError(passwordInput, passwordError, 'Password is required.');
            isValid = false;
        } else if (passwordInput.value.length < 8) {
            showFieldError(passwordInput, passwordError, 'Password must be at least 8 characters.');
            isValid = false;
        } else if (passwordInput.value.length > 64) {
            showFieldError(passwordInput, passwordError, 'Password must not exceed 64 characters.');
            isValid = false;
        }

        if (!isValid) { e.preventDefault(); return false; }

        // Disable only the submit button, NOT all inputs
        // Disabling inputs strips the CSRF token from the POST body → causes 419
        const submitBtn = document.getElementById('loginBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Signing In...';
        }
    });

    document.getElementById('forgotBtn')?.addEventListener('click', function () {
        setTimeout(() => { window.location.href = '/forgot-password'; }, 300);
    });
});
