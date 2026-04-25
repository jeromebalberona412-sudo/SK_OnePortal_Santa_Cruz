/**
 * Global Loading Overlay
 * Usage: showLoading('Logging in') / hideLoading()
 * Auto-wires common actions via data attributes and form intercepts.
 */

const MESSAGES = {
    login:      'Logging in',
    register:   'Creating your account',
    logout:     'Logging out',
    redirect:   'Redirecting',
    loading:    'Loading',
};

function showLoading(message = 'Loading') {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    overlay.querySelector('.gl-message').textContent = message;
    overlay.classList.add('gl-visible');
    document.body.style.overflow = 'hidden';
}

function hideLoading() {
    const overlay = document.getElementById('globalLoadingOverlay');
    if (!overlay) return;
    overlay.classList.remove('gl-visible');
    document.body.style.overflow = '';
}

// Expose globally
window.showLoading = showLoading;
window.hideLoading = hideLoading;

document.addEventListener('DOMContentLoaded', () => {

    // ── Login form ────────────────────────────────────────────────────────
    // Only show loading if both email and password are filled (validation passed)
    const loginForm = document.querySelector('form[action*="login"]:not([action*="logout"])');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const emailInput    = loginForm.querySelector('input[type="email"], input[name="email"]');
            const passwordInput = loginForm.querySelector('input[type="password"], input[name="password"]');
            const emailFilled    = emailInput    && emailInput.value.trim() !== '';
            const passwordFilled = passwordInput && passwordInput.value !== '';
            if (emailFilled && passwordFilled) {
                showLoading(MESSAGES.login);
            }
        });
    }

    // ── Register form ─────────────────────────────────────────────────────
    const registerForm = document.querySelector('form[action*="register"]');
    registerForm?.addEventListener('submit', () => showLoading(MESSAGES.register));

    // ── Logout form ───────────────────────────────────────────────────────
    // Intercept the actual form submit (after confirm modal approves it)
    const logoutForm = document.querySelector('form[action*="logout"]');
    if (logoutForm) {
        // The confirm modal calls logoutForm.submit() — patch it
        const origSubmit = HTMLFormElement.prototype.submit;
        logoutForm.submit = function () {
            showLoading(MESSAGES.logout);
            setTimeout(() => origSubmit.call(this), 80);
        };
        // Also catch native submit event (direct submit button without modal)
        logoutForm.addEventListener('submit', (e) => {
            // Only if not already showing (modal path already handled above)
            const overlay = document.getElementById('globalLoadingOverlay');
            if (!overlay?.classList.contains('gl-visible')) {
                showLoading(MESSAGES.logout);
            }
        });
    }

    // ── Internal navigation links (same-origin hrefs) ─────────────────────
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;

        const href = anchor.getAttribute('href');
        // Skip: empty, hash-only, javascript:, mailto:
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
        // Skip links that have data-no-loading
        if (anchor.dataset.noLoading !== undefined) return;
        // Skip on login/register/forgot-password pages — no redirect overlay needed
        const currentPath = window.location.pathname;
        if (currentPath === '/login' || currentPath.endsWith('/login') ||
            currentPath === '/register' || currentPath.endsWith('/register') ||
            currentPath.includes('forgot-password') || currentPath.includes('password/reset')) return;
        // Skip truly external links (different origin)
        if (href.startsWith('http') || href.startsWith('//')) {
            try {
                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return;
            } catch (_) { return; }
        }

        showLoading(MESSAGES.redirect);
    });

    // ── Page reload (beforeunload) ────────────────────────────────────────
    // Show "Loading..." only when the user manually reloads (not on link/form navigation)
    let _navigatingAway = false;
    document.addEventListener('click', () => { _navigatingAway = true; }, true);
    document.addEventListener('submit', () => { _navigatingAway = true; }, true);

    window.addEventListener('beforeunload', () => {
        if (!_navigatingAway) {
            showLoading(MESSAGES.loading);
        }
    });

    // ── Hide on back-forward cache restore ───────────────────────────────
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) hideLoading();
    });
});
