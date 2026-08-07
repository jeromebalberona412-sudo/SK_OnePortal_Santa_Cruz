/**
 * SK Officials — Login form validation
 */
function initLoginForm() {
    const loginForm     = document.getElementById('loginForm');
    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError    = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const submitBtn     = document.getElementById('loginBtn');

    if (!loginForm || !emailInput || !passwordInput) return;

    // Mark any server-side errors already on the page
    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) el.setAttribute('data-server-error', 'true');
    });

    const showErr = (input, el, msg) => {
        if (input) input.classList.add('is-invalid');
        el.textContent = msg;
        el.hidden = false;
    };
    const clearErr = (input, el) => {
        if (input) input.classList.remove('is-invalid');
        el.hidden = true;
    };
    const validEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

    emailInput.addEventListener('input',    () => clearErr(emailInput,    emailError));
    passwordInput.addEventListener('input', () => clearErr(passwordInput, passwordError));

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        clearErr(emailInput,    emailError);
        clearErr(passwordInput, passwordError);

        let ok = true;
        const email = emailInput.value.trim();
        const pass  = passwordInput.value;

        if (!email) {
            showErr(emailInput, emailError, 'Email address is required.');
            ok = false;
        } else if (!validEmail(email)) {
            showErr(emailInput, emailError, 'Please enter a valid email address.');
            ok = false;
        }

        if (!pass) {
            showErr(passwordInput, passwordError, 'Password is required.');
            ok = false;
        } else if (pass.length < 8) {
            showErr(passwordInput, passwordError, 'Password must be at least 8 characters.');
            ok = false;
        } else if (pass.length > 64) {
            showErr(passwordInput, passwordError, 'Password must not exceed 64 characters.');
            ok = false;
        }

        if (!ok) return;

        // Lock form and submit
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Signing In...';
        }
        emailInput.readOnly    = true;
        passwordInput.readOnly = true;

        loginForm.submit();
    });

    document.getElementById('forgotBtn')?.addEventListener('click', () => {
        window.location.href = '/forgot-password';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLoginForm);
} else {
    initLoginForm();
}
