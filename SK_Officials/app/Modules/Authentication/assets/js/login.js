/**
 * SK Officials — Login form with delayed Cloudflare Turnstile
 *
 * Execution order of submit listeners on #loginForm:
 *   1. login.js — bubbling phase
 *      Handles field validation, Turnstile modal, and final form dispatch.
 *
 * The only way loginForm.submit() should fire is from onTurnstileSuccess().
 * Every other submit-event invocation must call e.preventDefault() so the
 * native browser submit never races with our controlled submission.
 */

(function () {
    'use strict';

    // ─── Module-level state ───────────────────────────────────────────────────
    var turnstileWidgetId   = null;   // widget handle from turnstile.render()
    var turnstileToken      = null;   // verified token from the success callback
    var turnstileRendered   = false;  // render() called at least once
    var isSubmitting        = false;  // true the moment loginForm.submit() is called

    // ─── DOM refs ─────────────────────────────────────────────────────────────
    var loginForm, emailInput, passwordInput,
        emailError, passwordError, submitBtn,
        turnstileModal, turnstileModalBackdrop,
        turnstileContainer, turnstileCloseBtn;

    // ─── Validation helpers ───────────────────────────────────────────────────

    function validEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function showErr(input, el, msg) {
        if (input) input.classList.add('is-invalid');
        el.textContent = msg;
        el.hidden = false;
    }

    function clearErr(input, el) {
        if (input) input.classList.remove('is-invalid');
        el.hidden = true;
    }

    function validateFields() {
        clearErr(emailInput, emailError);
        clearErr(passwordInput, passwordError);

        var ok   = true;
        var email = emailInput.value.trim();
        var pass  = passwordInput.value;

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
            if (typeof window.turnstile !== 'undefined') {
                resolve();
                return;
            }
            var start = Date.now();
            var iv = setInterval(function () {
                if (typeof window.turnstile !== 'undefined') {
                    clearInterval(iv);
                    resolve();
                } else if (Date.now() - start > maxWaitMs) {
                    clearInterval(iv);
                    reject(new Error('Turnstile API did not load within ' + maxWaitMs + ' ms'));
                }
            }, 100);
        });
    }

    // ─── Turnstile modal ──────────────────────────────────────────────────────

    function widgetSize() {
        return window.matchMedia('(max-width: 480px)').matches ? 'compact' : 'normal';
    }

    function showTurnstileModal() {
        if (!turnstileModal) return;
        if (turnstileModal.parentElement !== document.body) {
            document.body.appendChild(turnstileModal);
        }
        turnstileModal.classList.add('turnstile-modal-visible');
        document.body.style.overflow = 'hidden';

        waitForTurnstileAPI().then(function () {
            if (isSubmitting) return;

            if (!turnstileRendered) {
                var siteKey = loginForm.dataset.turnstileSitekey;
                if (!siteKey) {
                    console.error('[Turnstile] site key missing');
                    return;
                }
                try {
                    turnstileWidgetId = window.turnstile.render(turnstileContainer, {
                        sitekey:            siteKey,
                        theme:              'light',
                        size:               widgetSize(),
                        callback:           onTurnstileSuccess,
                        'error-callback':   onTurnstileError,
                        'expired-callback': onTurnstileExpired,
                    });
                    turnstileRendered = true;
                } catch (err) {
                    console.error('[Turnstile] render() failed:', err);
                    setModalError('Failed to initialize verification. Please refresh the page.');
                }
            } else if (!isSubmitting && turnstileWidgetId !== null) {
                window.turnstile.reset(turnstileWidgetId);
            }
        }).catch(function (err) {
            console.error('[Turnstile] API load timeout:', err);
            setModalError('Verification system failed to load. Please refresh the page.');
        });
    }

    /**
     * Hide the modal overlay.
     *
     * @param {boolean} beforeSubmit
     *   When true we are about to call loginForm.submit(). In that case we must
     *   NOT call turnstile.reset() — resetting during submission triggers the
     *   expired-callback on some Cloudflare SDK versions, which would clear the
     *   token and potentially re-open the modal.
     */
    function hideTurnstileModal(beforeSubmit) {
        if (!turnstileModal) return;
        turnstileModal.classList.remove('turnstile-modal-visible');
        document.body.style.overflow = '';

        // Clear inline error message
        var errEl = turnstileModal.querySelector('.turnstile-modal-error');
        if (errEl) errEl.remove();

        if (!beforeSubmit) {
            // User cancelled — reset the widget and discard the token
            if (turnstileRendered && turnstileWidgetId !== null &&
                typeof window.turnstile !== 'undefined') {
                window.turnstile.reset(turnstileWidgetId);
            }
            turnstileToken = null;
            removeTokenInput();
        }
        // When beforeSubmit=true: leave the widget state alone and keep the
        // token input intact so the form submission carries the token.
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
        resetSubmitBtn();
    }

    // ─── Token input helpers ──────────────────────────────────────────────────

    function injectTokenInput(token) {
        // Remove every existing cf-turnstile-response field first (prevents
        // duplicates from Cloudflare's own auto-inject or previous attempts)
        removeTokenInput();

        var hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'cf-turnstile-response';
        hidden.value = token;
        loginForm.appendChild(hidden);
    }

    function removeTokenInput() {
        var fields = loginForm.querySelectorAll('input[name="cf-turnstile-response"]');
        fields.forEach(function (f) { f.remove(); });
    }

    // ─── Turnstile callbacks ──────────────────────────────────────────────────

    function onTurnstileSuccess(token) {
        if (isSubmitting) return; // late callback — already submitted

        turnstileToken = token;
        injectTokenInput(token);

        // Close the modal without resetting the widget (beforeSubmit = true)
        hideTurnstileModal(true);

        // Update button UI
        if (submitBtn) {
            submitBtn.classList.remove('waiting-for-turnstile');
        }
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

    // ─── Submit button state helpers ──────────────────────────────────────────

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
        if (!submitBtn) return;
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        var span = submitBtn.querySelector('span');
        if (span) span.textContent = 'Logging in...';
    }

    function resetSubmitBtn() {
        unlockAuthFields();
        if (!submitBtn) return;
        submitBtn.disabled = false;
        submitBtn.classList.remove('waiting-for-turnstile');
        submitBtn.classList.remove('loading');
        var span = submitBtn.querySelector('span');
        if (span) span.textContent = 'Login';
    }

    // ─── Field edit: reset Turnstile if the modal is open ────────────────────

    function onFieldEdit() {
        if (isSubmitting) return;

        if (turnstileModal && turnstileModal.classList.contains('turnstile-modal-visible')) {
            hideTurnstileModal(false); // cancel — full reset
        }

        if (turnstileToken) {
            turnstileToken = null;
            removeTokenInput();
        }

        resetSubmitBtn();
    }

    // ─── Form submit handler ──────────────────────────────────────────────────
    //
    // Invariant: we ALWAYS call e.preventDefault() unless isSubmitting is true.
    // The only path that actually sends the POST is loginForm.submit() inside
    // onTurnstileSuccess(). This eliminates the race where the browser would
    // natively submit the form at the same time as our programmatic submit.

    function onFormSubmit(e) {
        // ── Guard: never re-enter during an active submission ─────────────────
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        // ── If the token is already present, submit now ───────────────────────
        // This branch is hit when:
        //   a) The user already completed Turnstile this session and then
        //      re-triggered a submit.
        //   b) We should not show the modal again.
        if (turnstileToken) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.classList.remove('waiting-for-turnstile');
            }
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
        if (submitBtn) {
            submitBtn.classList.add('waiting-for-turnstile');
        }
    }

    // ─── Modal close ─────────────────────────────────────────────────────────

    function onModalClose() {
        if (isSubmitting) return;
        hideTurnstileModal(false); // full cancel reset
        resetSubmitBtn();
    }

    // ─── Initialise ──────────────────────────────────────────────────────────

    function init() {
        loginForm              = document.getElementById('loginForm');
        emailInput             = document.getElementById('email');
        passwordInput          = document.getElementById('password');
        emailError             = document.getElementById('email-error');
        passwordError          = document.getElementById('password-error');
        submitBtn              = document.getElementById('loginBtn');
        turnstileModal         = document.getElementById('turnstile-modal');
        turnstileModalBackdrop = document.getElementById('turnstile-modal-backdrop');
        turnstileContainer     = document.getElementById('turnstile-container');
        turnstileCloseBtn      = document.getElementById('turnstile-close-btn');

        if (!loginForm || !emailInput || !passwordInput) return;

        // Tag server-side field errors for other scripts to inspect
        document.querySelectorAll('.sk-field-error').forEach(function (el) {
            if (!el.hidden) el.setAttribute('data-server-error', 'true');
        });

        // Field input: clear inline errors; reset Turnstile if modal was open
        emailInput.addEventListener('input', function () {
            clearErr(emailInput, emailError);
            onFieldEdit();
        });
        passwordInput.addEventListener('input', function () {
            clearErr(passwordInput, passwordError);
            onFieldEdit();
        });

        loginForm.addEventListener('submit', onFormSubmit, false);

        // Forgot password
        var forgotBtn = document.getElementById('forgotBtn');
        if (forgotBtn) {
            forgotBtn.addEventListener('click', function () {
                window.location.href = '/forgot-password';
            });
        }

        var rememberInput = document.getElementById('remember');
        var rememberModal = document.getElementById('remember-modal');
        var rememberCancel = document.getElementById('remember-modal-cancel');
        var rememberConfirm = document.getElementById('remember-modal-confirm');
        var rememberBackdrop = document.getElementById('remember-modal-backdrop');

        function openRememberModal() {
            if (!rememberModal) return;
            rememberModal.hidden = false;
            rememberModal.classList.add('is-open');
        }

        function closeRememberModal() {
            if (!rememberModal) return;
            rememberModal.classList.remove('is-open');
            rememberModal.hidden = true;
        }

        if (rememberInput && rememberModal) {
            rememberInput.addEventListener('change', function () {
                if (!rememberInput.checked) {
                    return;
                }
                rememberInput.checked = false;
                openRememberModal();
            });

            rememberConfirm?.addEventListener('click', function () {
                rememberInput.checked = true;
                closeRememberModal();
            });

            function cancelRemember() {
                rememberInput.checked = false;
                closeRememberModal();
            }

            rememberCancel?.addEventListener('click', cancelRemember);
            rememberBackdrop?.addEventListener('click', cancelRemember);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && rememberModal.classList.contains('is-open')) {
                    cancelRemember();
                }
            });
        }

        // Modal close / cancel buttons
        if (turnstileCloseBtn) {
            turnstileCloseBtn.addEventListener('click', onModalClose);
        }
        if (turnstileModalBackdrop) {
            turnstileModalBackdrop.addEventListener('click', onModalClose);
        }
        var cancelBtn = document.getElementById('turnstile-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', onModalClose);
        }

        // If the server returned a Turnstile error (bad token), auto-open the modal
        // so the user can re-verify without having to click Login again.
        var serverErrEl = document.getElementById('turnstile-server-error');
        if (serverErrEl && loginForm.dataset.turnstileEnabled) {
            showTurnstileModal();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
