/**
 * SK Federations — Login form with Cloudflare Turnstile
 *
 * Submit listener execution order on #loginForm:
 *   1. auth-legal.js  — capturing phase (useCapture=true)
 *      Blocks if consent checkbox unchecked; otherwise passes through.
 *   2. login.js       — bubbling phase (useCapture=false)
 *      Validates fields, shows Turnstile modal, then calls loginForm.submit().
 *
 * loginForm.submit() is a native DOM call — it does NOT fire the submit event,
 * so no listener can intercept it. It is the only path that sends the POST.
 *
 * Performance: Turnstile widget is pre-rendered on page load so the checkbox
 * is already initialized when the modal opens — no initialization delay.
 */

(function () {
    'use strict';

    // ─── Module-level state ───────────────────────────────────────────────────
    var turnstileWidgetId = null;
    var turnstileToken    = null;
    var turnstileRendered = false;
    var isSubmitting      = false;

    // ─── DOM refs ─────────────────────────────────────────────────────────────
    var loginForm, emailInput, passwordInput,
        emailError, passwordError, loginBtn, loginBtnText,
        turnstileModal, turnstileModalBackdrop,
        turnstileContainer, turnstileCloseBtn;

    // ─── Validation ──────────────────────────────────────────────────────────

    function validEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function showErr(input, el, msg) {
        if (input) input.classList.add('is-invalid');
        if (el) { el.textContent = msg; el.style.display = 'block'; }
    }

    function clearErr(input, el) {
        if (input) input.classList.remove('is-invalid');
        if (el) { el.style.display = 'none'; el.textContent = ''; }
    }

    function validateFields() {
        clearErr(emailInput, emailError);
        clearErr(passwordInput, passwordError);

        var ok    = true;
        var email = emailInput ? emailInput.value.trim() : '';
        var pass  = passwordInput ? passwordInput.value : '';

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

        return ok;
    }

    // ─── Turnstile API readiness ──────────────────────────────────────────────

    function waitForTurnstileAPI(maxWaitMs) {
        maxWaitMs = maxWaitMs || 10000;
        return new Promise(function (resolve, reject) {
            if (typeof window.turnstile !== 'undefined') { resolve(); return; }
            var start = Date.now();
            var iv = setInterval(function () {
                if (typeof window.turnstile !== 'undefined') {
                    clearInterval(iv); resolve();
                } else if (Date.now() - start > maxWaitMs) {
                    clearInterval(iv);
                    reject(new Error('Turnstile API timed out'));
                }
            }, 100);
        });
    }

    // ─── Pre-render widget on page load ──────────────────────────────────────
    // Renders the Turnstile widget as soon as the API loads, before the user
    // clicks Login. When the modal opens the widget is already initialized —
    // no visible delay before the checkbox appears.

    function preRenderTurnstile() {
        if (!loginForm || !turnstileContainer) return;
        if (!loginForm.dataset.turnstileEnabled) return;

        var siteKey = loginForm.dataset.turnstileSitekey;
        if (!siteKey) return;

        waitForTurnstileAPI(15000).then(function () {
            if (turnstileRendered) return;
            try {
                turnstileWidgetId = window.turnstile.render(turnstileContainer, {
                    sitekey:            siteKey,
                    theme:              'light',
                    size:               'normal',
                    callback:           onTurnstileSuccess,
                    'error-callback':   onTurnstileError,
                    'expired-callback': onTurnstileExpired,
                });
                turnstileRendered = true;
            } catch (err) {
                console.warn('[Turnstile] pre-render failed:', err);
            }
        }).catch(function () {
            // API didn't load in 15s — modal fallback will render on open
        });
    }

    // ─── Turnstile modal ──────────────────────────────────────────────────────

    function showTurnstileModal() {
        if (!turnstileModal) return;
        turnstileModal.classList.add('turnstile-modal-visible');
        document.body.style.overflow = 'hidden';

        // Widget pre-rendered — just reset so the checkbox appears fresh
        if (turnstileRendered && turnstileWidgetId !== null &&
            typeof window.turnstile !== 'undefined') {
            window.turnstile.reset(turnstileWidgetId);
            return;
        }

        // Fallback: pre-render hasn't finished — render now
        waitForTurnstileAPI(10000).then(function () {
            if (isSubmitting || turnstileRendered) return;
            var siteKey = loginForm.dataset.turnstileSitekey;
            if (!siteKey) { setModalError('Verification config missing. Please refresh.'); return; }
            try {
                turnstileWidgetId = window.turnstile.render(turnstileContainer, {
                    sitekey:            siteKey,
                    theme:              'light',
                    size:               'normal',
                    callback:           onTurnstileSuccess,
                    'error-callback':   onTurnstileError,
                    'expired-callback': onTurnstileExpired,
                });
                turnstileRendered = true;
            } catch (err) {
                setModalError('Failed to initialize verification. Please refresh the page.');
            }
        }).catch(function () {
            setModalError('Verification system failed to load. Please refresh the page.');
        });
    }

    function hideTurnstileModal(beforeSubmit) {
        if (!turnstileModal) return;
        turnstileModal.classList.remove('turnstile-modal-visible');
        document.body.style.overflow = '';

        var errEl = turnstileModal.querySelector('.turnstile-modal-error');
        if (errEl) errEl.remove();

        if (!beforeSubmit) {
            if (turnstileRendered && turnstileWidgetId !== null &&
                typeof window.turnstile !== 'undefined') {
                window.turnstile.reset(turnstileWidgetId);
            }
            turnstileToken = null;
            removeTokenInput();
        }
    }

    function setModalError(message) {
        if (!turnstileModal) return;
        var errEl = turnstileModal.querySelector('.turnstile-modal-error');
        if (!errEl) {
            errEl = document.createElement('div');
            errEl.className = 'turnstile-modal-error';
            var body = turnstileModal.querySelector('.turnstile-modal-body');
            if (body) body.appendChild(errEl);
        }
        errEl.textContent = message;
        errEl.style.display = 'block';
        resetLoginBtn();
    }

    // ─── Token input helpers ──────────────────────────────────────────────────

    function injectTokenInput(token) {
        removeTokenInput();
        var hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'cf-turnstile-response';
        hidden.value = token;
        loginForm.appendChild(hidden);
    }

    function removeTokenInput() {
        loginForm.querySelectorAll('input[name="cf-turnstile-response"]')
            .forEach(function (f) { f.remove(); });
    }

    // ─── Turnstile callbacks ──────────────────────────────────────────────────

    function onTurnstileSuccess(token) {
        if (isSubmitting) return;

        turnstileToken = token;
        injectTokenInput(token);
        hideTurnstileModal(true);

        // Cloudflare checkbox completed — show feedback then submit
        setSigningIn();
        isSubmitting = true;
        loginForm.submit();
    }

    function onTurnstileError() {
        if (isSubmitting) return;
        turnstileToken = null;
        setModalError('Verification failed. Please try again or refresh the page.');
    }

    function onTurnstileExpired() {
        if (isSubmitting) return;
        turnstileToken = null;
        removeTokenInput();
        setModalError('Verification expired. Please complete the challenge again.');
    }

    // ─── Button helpers ───────────────────────────────────────────────────────

    function lockAuthFields() {
        if (emailInput) emailInput.readOnly = true;
        if (passwordInput) passwordInput.readOnly = true;
    }

    function unlockAuthFields() {
        if (emailInput) emailInput.readOnly = false;
        if (passwordInput) passwordInput.readOnly = false;
    }

    function setSigningIn() {
        lockAuthFields();
        if (!loginBtn) return;
        loginBtn.disabled = true;
        loginBtn.classList.add('loading');
        if (loginBtnText) loginBtnText.textContent = 'Logging in...';
    }

    function resetLoginBtn() {
        unlockAuthFields();
        if (!loginBtn) return;
        loginBtn.disabled = false;
        loginBtn.classList.remove('waiting-for-turnstile');
        loginBtn.classList.remove('loading');
        if (loginBtnText) loginBtnText.textContent = 'Login';
    }

    // ─── Field edit ───────────────────────────────────────────────────────────

    function onFieldEdit() {
        if (isSubmitting) return;
        if (turnstileModal && turnstileModal.classList.contains('turnstile-modal-visible')) {
            hideTurnstileModal(false);
        }
        if (turnstileToken) {
            turnstileToken = null;
            removeTokenInput();
        }
        resetLoginBtn();
    }

    // ─── Password toggle ──────────────────────────────────────────────────────

    function initPasswordToggle() {
        var btn = document.getElementById('pwToggleBtn');
        if (!btn || !passwordInput) return;
        btn.addEventListener('click', function () {
            var show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.classList.toggle('pw-visible', show);
        });
    }

    // ─── Form submit handler ──────────────────────────────────────────────────
    //
    // Runs in BUBBLING phase — after auth-legal.js capturing listener.
    // We ALWAYS call e.preventDefault() unless isSubmitting is already true.
    // The only POST is sent by loginForm.submit() inside onTurnstileSuccess().

    function onFormSubmit(e) {
        if (isSubmitting) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // Token already verified — submit immediately (e.g. re-submit after consent gate)
        if (turnstileToken) {
            e.preventDefault();
            e.stopPropagation();
            setSigningIn();
            isSubmitting = true;
            loginForm.submit();
            return;
        }

        e.preventDefault();

        if (!validateFields()) return;

        if (!loginForm.dataset.turnstileEnabled) {
            setSigningIn();
            isSubmitting = true;
            loginForm.submit();
            return;
        }

        showTurnstileModal();
        lockAuthFields();
        if (loginBtn) {
            loginBtn.disabled = true;
            loginBtn.classList.add('waiting-for-turnstile');
            loginBtn.classList.add('loading');
        }
        if (loginBtnText) loginBtnText.textContent = 'Logging in...';
    }

    // ─── Modal close ─────────────────────────────────────────────────────────

    function onModalClose() {
        if (isSubmitting) return;
        hideTurnstileModal(false);
        resetLoginBtn();
    }

    // ─── Initialise ──────────────────────────────────────────────────────────

    function init() {
        loginForm              = document.getElementById('loginForm');
        emailInput             = document.getElementById('email');
        passwordInput          = document.getElementById('password');
        emailError             = document.getElementById('email-error');
        passwordError          = document.getElementById('password-error');
        loginBtn               = document.getElementById('loginBtn');
        loginBtnText           = document.getElementById('loginBtnText');
        turnstileModal         = document.getElementById('turnstile-modal');
        turnstileModalBackdrop = document.getElementById('turnstile-modal-backdrop');
        turnstileContainer     = document.getElementById('turnstile-container');
        turnstileCloseBtn      = document.getElementById('turnstile-close-btn');

        if (!loginForm || !emailInput || !passwordInput) return;

        initPasswordToggle();

        emailInput.addEventListener('input', function () {
            clearErr(emailInput, emailError);
            onFieldEdit();
        });
        passwordInput.addEventListener('input', function () {
            clearErr(passwordInput, passwordError);
            onFieldEdit();
        });

        // Single bubbling-phase submit listener — fires after auth-legal capturing listener
        loginForm.addEventListener('submit', onFormSubmit, false);

        // Forgot password
        var forgotBtn = document.getElementById('forgotBtn');
        if (forgotBtn) {
            forgotBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = forgotBtn.href;
            });
        }

        // Turnstile modal close / cancel
        if (turnstileCloseBtn) turnstileCloseBtn.addEventListener('click', onModalClose);
        if (turnstileModalBackdrop) turnstileModalBackdrop.addEventListener('click', onModalClose);
        var cancelBtn = document.getElementById('turnstile-cancel-btn');
        if (cancelBtn) cancelBtn.addEventListener('click', onModalClose);

        // Auto-open modal only for Turnstile-specific server errors
        var serverErrEl = document.getElementById('turnstile-server-error');
        if (serverErrEl && loginForm.dataset.turnstileEnabled) {
            showTurnstileModal();
        }

        // Push back history state to prevent back-button login bypass
        window.history.pushState(null, '', window.location.href);
        window.addEventListener('popstate', function () {
            window.history.pushState(null, '', window.location.href);
        });

        // Pre-render Turnstile widget now so it's ready before user clicks Sign In
        preRenderTurnstile();

        // ─── Refresh CSRF token every 10 minutes to prevent "Page Expired" ───────
        setInterval(function refreshCsrfToken() {
            var metaTag = document.querySelector('meta[name="csrf-token"]');
            var csrfInput = loginForm.querySelector('input[name="_token"]');
            
            if (!metaTag || !csrfInput) return;

            // Fetch new CSRF token from a lightweight endpoint
            fetch('/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.token) {
                    metaTag.setAttribute('content', data.token);
                    csrfInput.value = data.token;
                }
            })
            .catch(function(err) {
                console.warn('[CSRF] Token refresh failed:', err);
            });
        }, 10 * 60 * 1000); // Every 10 minutes
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
