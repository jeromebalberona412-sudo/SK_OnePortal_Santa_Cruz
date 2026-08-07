/**
 * SK Officials login form validation and shared UI helpers.
 */
export function initLoginForm(options = {}) {
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

    // Load Turnstile widget dynamically
    function loadTurnstileWidget() {
        if (turnstileLoaded || typeof turnstile === 'undefined') return;

        turnstileLoaded = true;
        turnstileContainer.style.display = 'block';

        turnstileWidgetId = turnstile.render('#turnstile-widget', {
            sitekey: window.turnstileSiteKey,
            callback: function(token) {
                // Turnstile completed successfully
                clearFieldError(null, turnstileError);
                submitLoginForm();
            },
            'error-callback': function() {
                // Turnstile failed
                showFieldError(null, turnstileError, 'Security verification failed. Please try again.');
                resetFormState();
            },
            'expired-callback': function() {
                // Turnstile expired
                showFieldError(null, turnstileError, 'Security verification expired. Please try again.');
                resetFormState();
            }
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
        // Create hidden input for Turnstile token
        let tokenInput = document.querySelector('input[name="cf-turnstile-response"]');
        if (!tokenInput) {
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'cf-turnstile-response';
            loginForm.appendChild(tokenInput);
        }

        // Get the token from Turnstile
        const response = turnstile.getResponse(turnstileWidgetId);
        tokenInput.value = response;

        // Disable form elements and show loading state
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

        // Show loading spinner
        if (typeof showLoading === 'function') {
            showLoading('Authenticating...');
        }

        // Submit the form
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

        // If Turnstile is enabled and not yet loaded, load it now
        if (turnstileContainer && window.turnstileSiteKey && !turnstileLoaded) {
            // Show loading state while preparing Turnstile
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.querySelector('span').textContent = 'Loading Security Check...';
            }
            emailInput.disabled = true;
            passwordInput.disabled = true;

            loadTurnstileWidget();

            // Reset button state after widget loads
            setTimeout(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.querySelector('span').textContent = 'Login';
                }
                emailInput.disabled = false;
                passwordInput.disabled = false;
            }, 500);

            return false;
        }

        // If Turnstile is already loaded but not completed, show error
        if (turnstileContainer && turnstileLoaded) {
            const response = turnstile.getResponse(turnstileWidgetId);
            if (!response) {
                showFieldError(null, turnstileError, 'Pakumpleto ang seguridad na pagpapatunay.');
                return false;
            }
        }

        // If Turnstile is not enabled, submit directly
        if (!turnstileContainer) {
            submitLoginForm();
        }
    });

    document.getElementById('forgotBtn')?.addEventListener('click', function () {
        setTimeout(() => { window.location.href = '/forgot-password'; }, 300);
    });

    // Expose reset function for error handling
    window.resetTurnstileState = function() {
        if (turnstileLoaded && turnstileWidgetId && typeof turnstile !== 'undefined') {
            turnstile.reset(turnstileWidgetId);
        }
        if (turnstileContainer) {
            turnstileContainer.style.display = 'none';
        }
        turnstileLoaded = false;
        turnstileWidgetId = null;
        resetFormState();
    };
}