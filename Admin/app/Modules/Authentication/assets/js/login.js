document.addEventListener('DOMContentLoaded', () => {

    const loginForm = document.querySelector('form[action*="login"]');
    const overlay   = document.getElementById('signin-overlay');
    const overlaySub = document.getElementById('signin-overlay-sub');

    if (loginForm && overlay) {
        loginForm.addEventListener('submit', () => {
            overlay.hidden = false;
            overlay.classList.add('is-visible');

            const stages = [
                { text: 'Verifying credentials...', delay: 0 },
                { text: 'Checking authentication...', delay: 900 },
                { text: 'Signing you in...', delay: 1800 },
            ];

            stages.forEach(({ text, delay }) => {
                setTimeout(() => {
                    if (overlaySub) overlaySub.textContent = text;
                }, delay);
            });
        });
    }

    document.querySelectorAll('.login-toggle-pw').forEach((btn) => {
        const input   = btn.closest('.login-input-wrap')?.querySelector('input[type="password"], input[type="text"]');
        const iconShow = btn.querySelector('.pw-icon-show');
        const iconHide = btn.querySelector('.pw-icon-hide');
        if (!input) return;

        btn.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            iconShow?.classList.toggle('d-none', !isPassword);
            iconHide?.classList.toggle('d-none', isPassword);
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });
});
