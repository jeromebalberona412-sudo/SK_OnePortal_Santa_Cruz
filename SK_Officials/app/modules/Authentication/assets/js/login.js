document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

    if (!loginForm) return;

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

        window.loader?.show('Signing In...', '.sk-login-container');
    });

    document.getElementById('forgotBtn')?.addEventListener('click', function () {
        window.loader?.show('Loading...', '.sk-login-container');
        setTimeout(() => { window.location.href = '/forgot-password'; }, 300);
    });
});
