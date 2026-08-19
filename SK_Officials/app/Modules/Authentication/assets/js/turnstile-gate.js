/**
 * Shared Cloudflare Turnstile challenge for SK Officials auth forms.
 * Call SkOfficialsTurnstileGate.challenge(), then injectToken(form, token) before submit.
 *
 * Only the Turnstile API script is preloaded on page load. The checkbox widget is
 * rendered when the modal opens so it always starts unchecked — the user must
 * click it themselves. After verification completes, the form submits automatically.
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

    function isModalOpen() {
        var modalEl = modal();
        return Boolean(modalEl && modalEl.classList.contains('turnstile-modal-visible'));
    }

    function widgetSize() {
        return window.matchMedia('(max-width: 480px)').matches ? 'compact' : 'normal';
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

    function destroyWidget() {
        if (rendered && widgetId !== null && typeof window.turnstile !== 'undefined') {
            try {
                window.turnstile.remove(widgetId);
            } catch (err) {
                console.warn('[Turnstile] remove failed:', err);
            }
        }
        widgetId = null;
        rendered = false;
        var mount = container();
        if (mount) {
            mount.innerHTML = '';
        }
    }

    function rejectPending(message) {
        if (pending && pending.reject) {
            pending.reject(new Error(message || 'Verification cancelled.'));
        }
        pending = null;
        hideModal();
        destroyWidget();
    }

    function onSuccess(token) {
        if (!isModalOpen() || !pending) {
            return;
        }

        var resolve = pending.resolve;
        pending = null;
        hideModal();

        if (resolve) {
            resolve(token);
        }
    }

    function onError(errorCode) {
        if (!isModalOpen()) {
            return;
        }

        var msg = 'Verification failed. Please try again or refresh the page.';
        if (errorCode === '110200' || errorCode === 110200) {
            msg = 'This domain is not authorized in Cloudflare Turnstile. Add it in your widget settings.';
        } else if (errorCode === '110100' || errorCode === 110100) {
            msg = 'Invalid Turnstile site key. Please check your configuration.';
        }
        setError(msg);
    }

    function onExpired() {
        if (!isModalOpen()) {
            return;
        }
        setError('Verification expired. Please complete the challenge again.');
    }

    function renderWidget() {
        var mount = container();
        var key = siteKey();
        if (!mount || !key || typeof window.turnstile === 'undefined') {
            throw new Error('Verification config missing. Please refresh the page.');
        }

        destroyWidget();

        widgetId = window.turnstile.render(mount, {
            sitekey: key,
            theme: 'light',
            size: widgetSize(),
            callback: onSuccess,
            'error-callback': onError,
            'expired-callback': onExpired,
        });
        rendered = true;
    }

    function preloadTurnstileApi() {
        if (!isEnabled()) {
            return;
        }
        waitForApi(15000).catch(function () {
            // challenge() will surface the error when the modal opens.
        });
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

            waitForApi(10000).then(function () {
                renderWidget();
            }).catch(function (err) {
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

    function submitForm(form) {
        return challenge().then(function (token) {
            injectToken(form, token);
            HTMLFormElement.prototype.submit.call(form);
        });
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
            if (e.key === 'Escape' && isModalOpen()) {
                onClose();
            }
        });
    }

    bindClose();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', preloadTurnstileApi);
    } else {
        preloadTurnstileApi();
    }

    window.SkOfficialsTurnstileGate = {
        isEnabled: isEnabled,
        challenge: challenge,
        injectToken: injectToken,
        submitForm: submitForm,
    };
}());
