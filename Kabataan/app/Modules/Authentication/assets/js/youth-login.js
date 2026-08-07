/**
 * SK OnePortal — Kabataan Login
 *
 * Execution order of submit listeners on #loginForm:
 *   1. auth-legal.js  — capturing phase (useCapture=true)
 *      Intercepts if consent checkbox is unchecked.
 *      If consent IS checked, or pendingSubmit flag is set, it returns immediately.
 *   2. youth-login.js — bubbling phase (useCapture=false, default)
 *      Handles field validation, Turnstile modal, and final form dispatch.
 *
 * The only path that actually sends the POST is loginForm.submit() inside
 * onTurnstileSuccess(). Every other submit-event invocation calls
 * e.preventDefault() so the native browser submit never races with the
 * controlled submission.
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
        if (input) input.classList.add('error');
        if (el) {
            el.textContent = msg;
            el.hidden = false;
            el.style.display = 'block';
        }
    }

    function clearErr(input, el) {
        if (input) input.classList.remove('error');
        if (el) {
            el.hidden = true;
            el.style.display = 'none';
        }
    }

    function validateFields() {
        clearErr(emailInput, emailError);
        clearErr(passwordInput, passwordError);

        var ok    = true;
        var email = emailInput ? emailInput.value.trim() : '';
        var pass  = passwordInput ? passwordInput.value : '';

        if (!email) {
            showErr(emailInput, emailError, 'Email is required.');
            ok = false;
        } else if (!validEmail(email)) {
            showErr(emailInput, emailError, 'Please enter a valid email address.');
            ok = false;
        }

        if (!pass) {
            showErr(passwordInput, passwordError, 'Password is required.');
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

    function showTurnstileModal() {
        if (!turnstileModal) return;
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
                        size:               'normal',
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
                // Already rendered — reset so the widget appears fresh
                window.turnstile.reset(turnstileWidgetId);
            }
        }).catch(function (err) {
            console.error('[Turnstile] API load timeout:', err);
            setModalError('Verification system failed to load. Please refresh the page.');
        });
    }

    /**
     * Hide the Turnstile modal.
     *
     * @param {boolean} beforeSubmit
     *   Pass true when about to call loginForm.submit(). Skips turnstile.reset()
     *   to avoid triggering the expired-callback mid-submission on some SDK versions.
     */
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
        resetSubmitBtn();
    }

    // ─── Token input helpers ──────────────────────────────────────────────────

    function injectTokenInput(token) {
        // Remove duplicates before injecting (prevents double-submit token issues)
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

        // Close modal WITHOUT resetting the widget
        hideTurnstileModal(true);

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.remove('waiting-for-turnstile');
            submitBtn.querySelector('span').textContent = 'Signing In...';
        }

        showLoadingOverlay();

        // Guard before submit to block any re-entrant events
        isSubmitting = true;

        // Native submit — bypasses all JS submit listeners so the POST fires
        // exactly once with email, password, _token, remember, cf-turnstile-response.
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

    // ─── Loading overlay ──────────────────────────────────────────────────────

    function showLoadingOverlay() {
        var overlay = document.querySelector('.loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML =
                '<div class="overlay-backdrop"></div>' +
                '<div class="loading-content">' +
                '<div class="main-spinner"></div>' +
                '<p class="loading-text">Signing In...</p>' +
                '</div>';
            document.body.appendChild(overlay);
        }
        overlay.classList.add('active');
        var container = document.querySelector('.youth-login-container');
        if (container) container.classList.add('blurred');
    }

    // ─── Submit button state helpers ──────────────────────────────────────────

    function resetSubmitBtn() {
        if (!submitBtn) return;
        submitBtn.disabled = false;
        submitBtn.classList.remove('waiting-for-turnstile');
        var span = submitBtn.querySelector('span');
        if (span) span.textContent = 'Login';
    }

    // ─── Field edit: reset Turnstile if the modal is open ────────────────────

    function onFieldEdit() {
        if (isSubmitting) return;

        if (turnstileModal && turnstileModal.classList.contains('turnstile-modal-visible')) {
            hideTurnstileModal(false);
        }

        if (turnstileToken) {
            turnstileToken = null;
            removeTokenInput();
        }

        resetSubmitBtn();
    }

    // ─── Form submit handler ──────────────────────────────────────────────────
    //
    // Runs in the bubbling phase — AFTER auth-legal.js's capturing listener.
    // auth-legal.js has already passed the legal-consent gate before we get here.
    //
    // Invariant: always call e.preventDefault() unless isSubmitting is true.
    // The only path that actually POSTs is loginForm.submit() in onTurnstileSuccess().

    function onFormSubmit(e) {
        // Guard: block re-entrance during an active submission
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        // Token already present (e.g. auth-legal.js re-triggered via requestSubmit)
        if (turnstileToken) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
                var span = submitBtn.querySelector('span');
                if (span) span.textContent = 'Signing In...';
            }
            showLoadingOverlay();
            isSubmitting = true;
            loginForm.submit();
            return;
        }

        e.preventDefault();

        if (!validateFields()) return;

        // Turnstile disabled — submit directly
        if (!loginForm.dataset.turnstileEnabled) {
            if (submitBtn) {
                submitBtn.disabled = true;
                var span = submitBtn.querySelector('span');
                if (span) span.textContent = 'Signing In...';
            }
            showLoadingOverlay();
            isSubmitting = true;
            loginForm.submit();
            return;
        }

        // Show the Turnstile modal and wait for the user to complete the challenge
        showTurnstileModal();
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('waiting-for-turnstile');
            var span = submitBtn.querySelector('span');
            if (span) span.textContent = 'Complete verification';
        }
    }

    // ─── Modal close ─────────────────────────────────────────────────────────

    function onModalClose() {
        if (isSubmitting) return;
        hideTurnstileModal(false);
        resetSubmitBtn();
    }

    // ─── Input animation on focus ─────────────────────────────────────────────

    function bindInputAnimations() {
        document.querySelectorAll('.youth-input').forEach(function (input) {
            input.addEventListener('focus', function () {
                this.parentElement.classList.add('input-focused');
            });
            input.addEventListener('blur', function () {
                this.parentElement.classList.remove('input-focused');
            });
        });
    }

    // ─── Alert auto-dismiss ───────────────────────────────────────────────────

    function bindAlertDismiss() {
        document.querySelectorAll('.youth-alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.animation = 'alertSlideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                setTimeout(function () { alert.remove(); }, 400);
            }, 5000);
        });
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

        // Only wire up login logic on pages that have the login form
        if (!loginForm || !emailInput || !passwordInput) return;

        // Clear inline errors on input
        if (emailInput) {
            emailInput.addEventListener('input', function () {
                clearErr(emailInput, emailError);
                onFieldEdit();
            });
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', function () {
                clearErr(passwordInput, passwordError);
                onFieldEdit();
            });
        }

        // Single bubbling-phase submit listener
        loginForm.addEventListener('submit', onFormSubmit, false);

        // Forgot password
        var forgotBtn = document.getElementById('forgotBtn');
        if (forgotBtn) {
            forgotBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof showLoading === 'function') {
                    showLoading('Redirecting to password recovery');
                    setTimeout(function () {
                        window.location.href = forgotBtn.href;
                    }, 300);
                } else {
                    window.location.href = forgotBtn.href;
                }
            });
        }

        // Homepage button
        var homepageBtn = document.getElementById('homepageBtn');
        if (homepageBtn) {
            homepageBtn.addEventListener('click', function () {
                if (typeof showLoading === 'function') {
                    showLoading('Redirecting to Homepage');
                }
            });
        }

        // Turnstile modal close / cancel
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

        // If the server returned a Turnstile error, auto-open the modal so the
        // user can re-verify without having to click Login again.
        var serverErrEl = document.getElementById('turnstile-server-error');
        if (serverErrEl && loginForm.dataset.turnstileEnabled) {
            showTurnstileModal();
        }

        bindInputAnimations();
        bindAlertDismiss();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ─── Shared animation styles ──────────────────────────────────────────────
    (function injectStyles() {
        if (document.getElementById('youth-login-styles')) return;
        var style = document.createElement('style');
        style.id  = 'youth-login-styles';
        style.textContent = [
            '@keyframes alertSlideOut{to{opacity:0;transform:translateY(-10px)}}',
            '@keyframes errorSlideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}',
            '@keyframes errorSlideOut{to{opacity:0;transform:translateY(-8px)}}',
            '@keyframes youthLoginSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}',
            '.youth-submit-btn .spinner{display:inline-block;vertical-align:middle;transform-origin:center center;animation:youthLoginSpin 0.9s linear infinite}',
        ].join('');
        document.head.appendChild(style);
    }());

}());
