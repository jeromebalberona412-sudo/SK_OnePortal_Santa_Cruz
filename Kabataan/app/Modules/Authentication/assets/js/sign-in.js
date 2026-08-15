/**
 * SK OnePortal — Kabataan Sign In
 *
 * Submit listener execution order on #signInForm:
 *   1. auth-legal.js  — capturing phase (useCapture=true)
 *      Blocks if consent radio unchecked; otherwise passes through.
 *   2. sign-in.js — bubbling phase (useCapture=false)
 *      Validates fields, shows Turnstile modal, then calls signInForm.submit().
 *
 * signInForm.submit() is a native call — it does NOT fire the submit event,
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
    var signInForm, emailInput, passwordInput,
        emailError, passwordError, submitBtn,
        turnstileModal, turnstileModalBackdrop,
        turnstileContainer, turnstileCloseBtn;

    // ─── Validation helpers ───────────────────────────────────────────────────

    function validEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function showErr(input, el, msg) {
        if (input) input.classList.add('error');
        if (el) { el.textContent = msg; el.hidden = false; el.style.display = 'block'; }
    }

    function clearErr(input, el) {
        if (input) input.classList.remove('error');
        if (el) { el.hidden = true; el.style.display = 'none'; }
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
    //
    // Renders the Turnstile widget as soon as the API script loads — before
    // the user clicks Sign In. When the modal opens, the widget is already
    // initialized so there is no initialization delay before the checkbox
    // appears.

    function preRenderTurnstile() {
        if (!signInForm || !turnstileContainer) return;
        if (!signInForm.dataset.turnstileEnabled) return;

        var siteKey = signInForm.dataset.turnstileSitekey;
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
            // API didn't load in 15s — modal will attempt render on open
        });
    }

    // ─── Turnstile modal ──────────────────────────────────────────────────────

    function showTurnstileModal() {
        if (!turnstileModal) return;
        turnstileModal.classList.add('turnstile-modal-visible');
        document.body.style.overflow = 'hidden';

        // Widget already pre-rendered — just reset so checkbox appears fresh
        if (turnstileRendered && turnstileWidgetId !== null &&
            typeof window.turnstile !== 'undefined') {
            window.turnstile.reset(turnstileWidgetId);
            return;
        }

        // Fallback: pre-render hasn't completed yet — render now
        waitForTurnstileAPI(10000).then(function () {
            if (isSubmitting || turnstileRendered) return;
            var siteKey = signInForm.dataset.turnstileSitekey;
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
        isSubmitting = false;
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
        removeTokenInput();
        var hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = 'cf-turnstile-response';
        hidden.value = token;
        signInForm.appendChild(hidden);
    }

    function removeTokenInput() {
        signInForm.querySelectorAll('input[name="cf-turnstile-response"]')
            .forEach(function (f) { f.remove(); });
    }

    // ─── Turnstile callbacks ──────────────────────────────────────────────────

    function onTurnstileSuccess(token) {
        if (isSubmitting) return;

        turnstileToken = token;
        injectTokenInput(token);
        hideTurnstileModal(true);

        // Set lock BEFORE submit — prevents any re-entrant handler from firing
        isSubmitting = true;
        setSigningIn();
        signInForm.submit();
    }

    function onTurnstileError(errorCode) {
        console.error('[Turnstile] Error callback triggered with code:', errorCode);
        isSubmitting = false;
        turnstileToken = null;
        removeTokenInput();
        resetSubmitBtn();
        var msg = 'Verification failed. Please try again or refresh the page.';
        if (errorCode === '110200' || errorCode === 110200) {
            msg = 'Domain not authorized in Cloudflare Turnstile dashboard. Please add this domain (or enable localhost) in your Cloudflare widget settings.';
        } else if (errorCode === '110100' || errorCode === 110100) {
            msg = 'Invalid Turnstile site key. Please check your configuration.';
        }
        setModalError(msg);
    }

    function onTurnstileExpired() {
        isSubmitting = false;
        turnstileToken = null;
        removeTokenInput();
        resetSubmitBtn();
        setModalError('Verification expired. Please complete the challenge again.');
    }

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
        if (span) span.textContent = 'Signing in...';
    }

    function resetSubmitBtn() {
        unlockAuthFields();
        if (!submitBtn) return;
        submitBtn.disabled = false;
        submitBtn.classList.remove('waiting-for-turnstile');
        submitBtn.classList.remove('loading');
        var span = submitBtn.querySelector('span');
        if (span) span.textContent = 'Sign In';
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
        resetSubmitBtn();
    }

    // ─── Form submit handler ──────────────────────────────────────────────────

    function onFormSubmit(e) {
        if (isSubmitting) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // Token already verified this session — submit immediately
        if (turnstileToken) {
            e.preventDefault();
            e.stopPropagation();
            isSubmitting = true;
            setSigningIn();
            signInForm.submit();
            return;
        }

        e.preventDefault();

        if (!validateFields()) return;

        // Turnstile disabled — submit directly
        if (!signInForm.dataset.turnstileEnabled) {
            isSubmitting = true;
            setSigningIn();
            signInForm.submit();
            return;
        }

        // Show Turnstile modal — submission happens in onTurnstileSuccess
        showTurnstileModal();
        lockAuthFields();
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('waiting-for-turnstile');
            submitBtn.classList.add('loading');
            var span = submitBtn.querySelector('span');
            if (span) span.textContent = 'Signing in...';
        }
    }

    // ─── Modal close ─────────────────────────────────────────────────────────

    function onModalClose() {
        isSubmitting = false;
        hideTurnstileModal(false);
        resetSubmitBtn();
    }

    // ─── Input animations ─────────────────────────────────────────────────────

    function bindInputAnimations() {
        document.querySelectorAll('.youth-input').forEach(function (input) {
            input.addEventListener('focus', function () {
                if (this.parentElement) this.parentElement.classList.add('input-focused');
            });
            input.addEventListener('blur', function () {
                if (this.parentElement) this.parentElement.classList.remove('input-focused');
            });
        });
    }

    // ─── Alert auto-dismiss ───────────────────────────────────────────────────

    function bindAlertDismiss() {
        document.querySelectorAll('.youth-alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.animation = 'alertSlideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                setTimeout(function () {
                    if (alert.parentNode) alert.parentNode.removeChild(alert);
                }, 400);
            }, 5000);
        });
    }

    // ─── Initialise ──────────────────────────────────────────────────────────

    function init() {
        signInForm              = document.getElementById('signInForm');
        emailInput             = document.getElementById('email');
        passwordInput          = document.getElementById('password');
        emailError             = document.getElementById('email-error');
        passwordError          = document.getElementById('password-error');
        submitBtn              = document.getElementById('signInBtn');
        turnstileModal         = document.getElementById('turnstile-modal');
        turnstileModalBackdrop = document.getElementById('turnstile-modal-backdrop');
        turnstileContainer     = document.getElementById('turnstile-container');
        turnstileCloseBtn      = document.getElementById('turnstile-close-btn');

        if (!signInForm || !emailInput || !passwordInput) return;

        emailInput.addEventListener('input', function () {
            clearErr(emailInput, emailError);
            onFieldEdit();
        });
        passwordInput.addEventListener('input', function () {
            clearErr(passwordInput, passwordError);
            onFieldEdit();
        });

        // Single submit listener — bubbling phase, after auth-legal capturing
        signInForm.addEventListener('submit', onFormSubmit, false);

        // Forgot password link
        var forgotBtn = document.getElementById('forgotBtn');
        if (forgotBtn) {
            forgotBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = forgotBtn.href || '/password/reset';
            });
        }

        // Turnstile modal close / cancel
        if (turnstileCloseBtn) turnstileCloseBtn.addEventListener('click', onModalClose);
        if (turnstileModalBackdrop) turnstileModalBackdrop.addEventListener('click', onModalClose);
        var cancelBtn = document.getElementById('turnstile-cancel-btn');
        if (cancelBtn) cancelBtn.addEventListener('click', onModalClose);

        // Auto-open modal only when server returned a Turnstile-specific error
        var serverErrEl = document.getElementById('turnstile-server-error');
        if (serverErrEl && signInForm.dataset.turnstileEnabled) {
            showTurnstileModal();
        }

        bindInputAnimations();
        bindAlertDismiss();

        // Pre-render Turnstile widget now so it's ready before user clicks Sign In
        preRenderTurnstile();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ─── Inject animation keyframes ───────────────────────────────────────────
    (function injectStyles() {
        if (document.getElementById('youth-sign-in-styles')) return;
        var style = document.createElement('style');
        style.id  = 'youth-sign-in-styles';
        style.textContent = [
            '@keyframes alertSlideOut{to{opacity:0;transform:translateY(-10px)}}',
            '@keyframes errorSlideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}',
            '@keyframes errorSlideOut{to{opacity:0;transform:translateY(-8px)}}',
        ].join('');
        document.head.appendChild(style);
    }());

}());
