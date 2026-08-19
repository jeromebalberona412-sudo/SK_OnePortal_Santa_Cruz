/**
 * Shared Cloudflare Turnstile challenge for SK Officials auth forms.
 * Renders a fresh widget only while the modal is visible (mobile-safe).
 */
(function () {
    'use strict';

    var widgetId = null;
    var rendered = false;
    var errorRetries = 0;
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

    function afterModalPaint(callback) {
        var delay = window.matchMedia('(max-width: 768px)').matches ? 250 : 80;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                setTimeout(callback, delay);
            });
        });
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
        if (!modalEl || !isModalOpen()) {
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

    function clearWidget() {
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

    function renderWidget() {
        if (rendered || !isModalOpen()) {
            return;
        }

        var mount = container();
        var key = siteKey();
        if (!mount || !key || typeof window.turnstile === 'undefined') {
            throw new Error('Verification config missing. Please refresh the page.');
        }

        widgetId = window.turnstile.render(mount, {
            sitekey: key,
            theme: 'light',
            size: 'normal',
            retry: 'auto',
            'refresh-expired': 'auto',
            callback: onSuccess,
            'error-callback': onError,
            'expired-callback': onExpired,
        });
        rendered = true;
    }

    function mountWidget() {
        if (!isModalOpen()) {
            return;
        }

        clearWidget();

        waitForApi(10000).then(function () {
            if (!isModalOpen()) {
                return;
            }
            afterModalPaint(function () {
                try {
                    renderWidget();
                } catch (err) {
                    if (pending && pending.reject) {
                        pending.reject(new Error(err.message || 'Verification failed to initialize.'));
                    }
                    pending = null;
                    setError(err.message || 'Verification failed to initialize.');
                }
            });
        }).catch(function (err) {
            if (pending && pending.reject) {
                pending.reject(new Error(err.message || 'Verification system failed to load. Please refresh the page.'));
            }
            pending = null;
            setError(err.message || 'Verification system failed to load. Please refresh the page.');
        });
    }

    function preloadTurnstileApi() {
        if (!isEnabled()) {
            return;
        }
        waitForApi(15000).catch(function () {
            // mountWidget() will surface the error when the modal opens.
        });
    }

    function rejectPending(message) {
        if (pending && pending.reject) {
            pending.reject(new Error(message || 'Verification cancelled.'));
        }
        pending = null;
        hideModal();
        clearWidget();
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

        console.warn('[Turnstile] error:', errorCode);
        clearError();

        if (errorRetries < 1) {
            errorRetries += 1;
            clearWidget();
            afterModalPaint(renderWidget);
            return;
        }

        setError('Verification failed. Please try again or refresh the page.');
    }

    function onExpired() {
        if (!isModalOpen()) {
            return;
        }
        clearError();
        clearWidget();
        afterModalPaint(renderWidget);
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
            errorRetries = 0;
            showModal();
            clearError();
            mountWidget();
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
        var card = document.querySelector('.turnstile-modal-card');
        if (card) {
            card.addEventListener('click', function (e) {
                e.stopPropagation();
            });
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
