/**
 * Shared Cloudflare Turnstile challenge for Kabataan auth forms.
 * Pages call KabataanTurnstileGate.challenge() and then submit/fetch with
 * the returned token as cf-turnstile-response.
 */
(function () {
    'use strict';

    var widgetId = null;
    var rendered = false;
    var pending = null;

    function config() {
        return document.getElementById('turnstile-gate-config');
    }

    function isEnabled() {
        var el = config();
        return Boolean(el && el.dataset.enabled === '1' && el.dataset.sitekey);
    }

    function siteKey() {
        var el = config();
        return el ? (el.dataset.sitekey || '') : '';
    }

    function modal() {
        return document.getElementById('turnstile-modal');
    }

    function container() {
        return document.getElementById('turnstile-container');
    }

    function waitForApi(maxWaitMs) {
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
                    reject(new Error('Verification system failed to load. Please refresh the page.'));
                }
            }, 100);
        });
    }

    function setError(message) {
        var modalEl = modal();
        if (!modalEl) {
            return;
        }
        var errEl = modalEl.querySelector('.turnstile-modal-error');
        if (!errEl) {
            errEl = document.createElement('div');
            errEl.className = 'turnstile-modal-error';
            var body = modalEl.querySelector('.turnstile-modal-body');
            if (body) {
                body.appendChild(errEl);
            }
        }
        errEl.textContent = message;
        errEl.style.display = 'block';
    }

    function clearError() {
        var modalEl = modal();
        var errEl = modalEl ? modalEl.querySelector('.turnstile-modal-error') : null;
        if (errEl) {
            errEl.remove();
        }
    }

    function showModal() {
        var modalEl = modal();
        if (!modalEl) {
            return;
        }
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        modalEl.classList.add('turnstile-modal-visible');
        document.body.style.overflow = 'hidden';
    }

    function hideModal() {
        var modalEl = modal();
        if (!modalEl) {
            return;
        }
        modalEl.classList.remove('turnstile-modal-visible');
        document.body.style.overflow = '';
        clearError();
    }

    function rejectPending(message) {
        if (pending && pending.reject) {
            pending.reject(new Error(message || 'Verification cancelled.'));
        }
        pending = null;
        hideModal();
        if (rendered && widgetId !== null && typeof window.turnstile !== 'undefined') {
            window.turnstile.reset(widgetId);
        }
    }

    function onSuccess(token) {
        var resolve = pending && pending.resolve;
        pending = null;
        hideModal();
        if (resolve) {
            resolve(token);
        }
    }

    function onError(errorCode) {
        var msg = 'Verification failed. Please try again or refresh the page.';
        if (errorCode === '110200' || errorCode === 110200) {
            msg = 'This domain is not authorized in Cloudflare Turnstile. Add it in your widget settings.';
        } else if (errorCode === '110100' || errorCode === 110100) {
            msg = 'Invalid Turnstile site key. Please check your configuration.';
        }
        setError(msg);
    }

    function onExpired() {
        setError('Verification expired. Please complete the challenge again.');
    }

    function renderWidget() {
        var mount = container();
        var key = siteKey();
        if (!mount || !key || typeof window.turnstile === 'undefined') {
            throw new Error('Verification config missing. Please refresh the page.');
        }
        if (rendered && widgetId !== null) {
            window.turnstile.reset(widgetId);
            return;
        }
        widgetId = window.turnstile.render(mount, {
            sitekey: key,
            theme: 'light',
            size: 'normal',
            callback: onSuccess,
            'error-callback': onError,
            'expired-callback': onExpired,
        });
        rendered = true;
    }

    function challenge() {
        if (!isEnabled()) {
            return Promise.resolve('');
        }

        if (pending) {
            rejectPending('Verification cancelled.');
        }

        return new Promise(function (resolve, reject) {
            pending = { resolve: resolve, reject: reject };
            showModal();
            clearError();
            waitForApi(10000).then(renderWidget).catch(function (err) {
                rejectPending(err.message || 'Verification system failed to load. Please refresh the page.');
            });
        });
    }

    function injectToken(form, token) {
        if (!form) {
            return;
        }
        form.querySelectorAll('input[name="cf-turnstile-response"]').forEach(function (el) {
            el.remove();
        });
        if (!token) {
            return;
        }
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'cf-turnstile-response';
        hidden.value = token;
        form.appendChild(hidden);
    }

    function bindClose() {
        var closeBtn = document.getElementById('turnstile-close-btn');
        var cancelBtn = document.getElementById('turnstile-cancel-btn');
        var backdrop = document.getElementById('turnstile-modal-backdrop');
        function onClose() {
            rejectPending('Verification cancelled.');
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', onClose);
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', onClose);
        }
        if (backdrop) {
            backdrop.addEventListener('click', onClose);
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal() && modal().classList.contains('turnstile-modal-visible')) {
                onClose();
            }
        });
    }

    bindClose();

    window.KabataanTurnstileGate = {
        isEnabled: isEnabled,
        challenge: challenge,
        injectToken: injectToken,
    };
}());
