document.addEventListener('DOMContentLoaded', function () {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('email-error');

    if (!forgotPasswordForm) return;

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

        window.loader?.show('Sending Reset Link...', '.sk-login-container');
    });

    emailInput.addEventListener('input', function () {
        this.classList.remove('is-invalid');
        emailError.hidden = true;
    });

    document.querySelector('.register-link')?.addEventListener('click', function (e) {
        e.preventDefault();
        window.loader?.show('Loading...', '.sk-login-container');
        setTimeout(() => { window.location.href = this.href; }, 300);
    });
});
