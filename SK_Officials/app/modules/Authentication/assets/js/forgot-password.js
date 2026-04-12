document.addEventListener('DOMContentLoaded', function () {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');

    if (!forgotPasswordForm) return;

    // Mark server-side errors so they survive the first input clear
    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) el.setAttribute('data-server-error', 'true');
    });

    forgotPasswordForm.addEventListener('submit', function (e) {
        const email = emailInput.value.trim();

        emailInput.classList.remove('is-invalid');
        emailError.hidden = true;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            e.preventDefault();
            emailInput.classList.add('is-invalid');
            emailError.textContent = 'Please enter your email address.';
            emailError.hidden = false;
            return;
        }

        if (!emailRegex.test(email)) {
            e.preventDefault();
            emailInput.classList.add('is-invalid');
            emailError.textContent = 'Please enter a valid email address.';
            emailError.hidden = false;
            return;
        }

        // Disable only the submit button, NOT all inputs
        // Disabling inputs strips the CSRF token from the POST body → causes 419
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Sending...';
        }
    });

    emailInput.addEventListener('input', function () {
        this.classList.remove('is-invalid');
        emailError.hidden = true;
    });

    document.querySelector('.register-link')?.addEventListener('click', function (e) {
        e.preventDefault();
        setTimeout(() => { window.location.href = this.href; }, 300);
    });
});
