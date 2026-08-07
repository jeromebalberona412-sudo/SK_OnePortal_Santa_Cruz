/**
 * SK Officials login form validation and shared UI helpers.
 */
function initLoginForm(options = {}) {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const turnstileContainer = document.getElementById('turnstile-container');
    const turnstileWidget = document.getElementById('turnstile-widget');
    const turnstileError = document.getElementById('turnstile-error');
    const submitBtn = document.getElementById('loginBtn');

    if (!loginForm || !emailInput || !passwordInput) {
        return null;
    }

    document.querySelectorAll('.sk-field-error').forEach(function (el) {
        if (!el.hidden) {
            el.setAttribute('data-server-error', 'true');
        }
    });

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showFieldError(input, errorEl, message) {
        if (input) input.classList.add('is-invalid');
        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function clearFieldError(input, errorEl) {
        if (input) input.classList.remove('is-invalid');
        errorEl.hidden = true;
    }

    emailInput.addEventListener('input', () => clearFieldError(emailInput, emailError));
    passwordInput.addEventListener('input', () => clearFieldError(passwordInput, passwordError));

    let turnstileLoaded = false;
    let turnstileWidgetId = null;

    /**
     * Run `fn` as soon as the Turnstile script is ready.
     * If it's already loaded (window.__turnstileReady), run immediately.
     * Otherwise push onto the queue that onTurnstileLoad() will flush.
     */
    function whenTurnstileReady(fn) {
        if (window.__turnstileReady) {
            fn();
        } else {
            window.__turnstileReadyCallbacks = window.__turnstileReadyCallbacks || [];
            window.__turnstileReadyCallbacks.push(fn);
        }
    }

    // Load Turnstile widget dynamically — called once after form validation passes
    function loadTurnstileWidget() {
        if (turnstileLoaded) return;

        whenTurnstileReady(function () {
            if (turnstileLoaded) return; // guard against double-fire
            turnstileLoaded = true;

            // Show the widget container
            if (turnstileContainer) {
                turnstileContainer.style.display = 'block';
            }

            // Restore inputs so the user can see/interact while solving the challenge
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = 'Login';
            }
            emailInput.disabled = false;
            passwordInput.disabled = false;

            turnstileWidgetId = turnstile.render('#turnstile-widget', {
                sitekey: window.turnstileSiteKey,
                callback: function (token) {
                    clearFieldError(null, turnstileError);
                    submitLoginForm();
                },
                'error-callback': function () {
                    showFieldError(null, turnstileError, 'Security verification failed. Please try again.');
                    resetFormState();
                },
                'expired-callback': function () {
                    showFieldError(null, turnstileError, 'Security verification expired. Please try again.');
                    resetFormState();
                },
            });
        });
    }

    function resetFormState() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Login';
        }
        emailInput.disabled = false;
        passwordInput.disabled = false;
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
    }

    function submitLoginForm() {
        // Attach the Turnstile token as a hidden input
        let tokenInput = document.querySelector('input[name="cf-turnstile-response"]');
        if (!tokenInput) {
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'cf-turnstile-response';
            loginForm.appendChild(tokenInput);
        }

        const response = turnstile.getResponse(turnstileWidgetId);
        tokenInput.value = response;

        // Lock the form while submitting
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Authenticating...';
        }
        emailInput.disabled = true;
        passwordInput.disabled = true;
        if (turnstileContainer) {
            turnstileContainer.style.pointerEvents = 'none';
            turnstileContainer.style.opacity = '0.5';
        }

        if (typeof showLoading === 'function') {
            showLoading('Authenticating...');
        }

        loginForm.submit();
    }

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        let isValid = true;

        clearFieldError(emailInput, emailError);
        clearFieldError(passwordInput, passwordError);
        if (turnstileError) clearFieldError(null, turnstileError);

        // Validate email
        if (!emailInput.value.trim()) {
            showFieldError(emailInput, emailError, 'Email address is required.');
            isValid = false;
        } else if (!validateEmail(emailInput.value.trim())) {
            showFieldError(emailInput, emailError, 'Please enter a valid email address.');
            isValid = false;
        }

        // Validate password
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

        if (!isValid) return false;

        // Turnstile enabled — show the widget first (if not yet loaded)
        if (turnstileContainer && window.turnstileSiteKey) {
            if (!turnstileLoaded) {
                // Lock the button and wait for the script + render
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.querySelector('span').textContent = 'Loading Security Check...';
                }
                emailInput.disabled = true;
                passwordInput.disabled = true;

                loadTurnstileWidget();
                // whenTurnstileReady() inside loadTurnstileWidget will re-enable
                // inputs and show the widget once the script is ready.
                return false;
            }

            // Widget already visible — check if user completed the challenge
            const response = turnstile.getResponse(turnstileWidgetId);
            if (!response) {
                showFieldError(null, turnstileError, 'Please complete the security verification.');
                return false;
            }

            // Challenge already passed — submit immediately
            submitLoginForm();
            return false;
        }

        // Turnstile not enabled — submit directly with a loading spinner
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'Authenticating...';
        }
        if (typeof showLoading === 'function') {
            showLoading('Authenticating...');
        }
        loginForm.submit();
    });

    document.getElementById('forgotBtn')?.addEventListener('click', function () {
        setTimeout(() => { window.location.href = '/forgot-password'; }, 300);
    });

    // Expose reset function for server-side error handling
    window.resetTurnstileState = function() {
        if (turnstileLoaded && turnstileWidgetId && typeof turnstile !== 'undefined') {
            turnstile.reset(turnstileWidgetId);
        }
        if (turnstileContainer) {
            turnstileContainer.style.display = 'none';
            turnstileContainer.style.pointerEvents = '';
            turnstileContainer.style.opacity = '';
        }
        turnstileLoaded = false;
        turnstileWidgetId = null;
        resetFormState();
    };
}

// Auto-initialize when the DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initLoginForm();
    });
} else {
    initLoginForm();
}
