/**
 * Sign In Terms & Privacy consent + modals
 */
(function () {
    'use strict';

    const ACK_STORAGE_PREFIX = 'sk_oneportal_legal_ack_';
    const SCROLL_THRESHOLD = 12;
    let pendingSubmit = false;

    function getAckKey(type) {
        const portal = document.body.classList.contains('sk-signin-page') ? 'officials' : 'kabataan';
        return `${ACK_STORAGE_PREFIX}${portal}_${type}`;
    }

    function isScrolledToBottom(element) {
        if (!element) return false;
        return element.scrollHeight - element.scrollTop - element.clientHeight <= SCROLL_THRESHOLD;
    }

    function bindModalAckStorage(ack) {
        const type = ack.getAttribute('data-legal-ack');
        if (!type) return;

        ack.addEventListener('change', () => {
            if (ack.checked) {
                sessionStorage.setItem(getAckKey(type), '1');
            } else {
                sessionStorage.removeItem(getAckKey(type));
            }
        });
    }

    function syncModalOkButton(modal) {
        const ack = modal.querySelector('[data-legal-ack]');
        const okBtn = modal.querySelector('.auth-legal-modal-btn');
        if (!ack || !okBtn) return;

        okBtn.disabled = !ack.checked;
    }

    function syncModalScrollGate(modal) {
        const body = modal.querySelector('.auth-legal-modal-body');
        const ack = modal.querySelector('[data-legal-ack]');
        const ackLabel = modal.querySelector('.auth-legal-modal-ack');
        if (!body || !ack) return;

        const atBottom = isScrolledToBottom(body);

        ack.disabled = !atBottom;
        ackLabel?.classList.toggle('auth-legal-modal-ack--locked', !atBottom);

        if (!atBottom && ack.checked) {
            ack.checked = false;
        }

        syncModalOkButton(modal);
    }

    function resetModalScrollGate(modal) {
        const body = modal.querySelector('.auth-legal-modal-body');
        const ack = modal.querySelector('[data-legal-ack]');
        const ackLabel = modal.querySelector('.auth-legal-modal-ack');
        const okBtn = modal.querySelector('.auth-legal-modal-btn');

        if (body) {
            body.scrollTop = 0;
        }

        if (ack) {
            ack.checked = false;
            ack.disabled = true;
        }

        ackLabel?.classList.add('auth-legal-modal-ack--locked');

        if (okBtn) {
            okBtn.disabled = true;
        }

        requestAnimationFrame(() => {
            syncModalScrollGate(modal);
        });
    }

    function initLegalModalScrollGates() {
        document.querySelectorAll('.auth-legal-modal').forEach((modal) => {
            const body = modal.querySelector('.auth-legal-modal-body');
            const ack = modal.querySelector('[data-legal-ack]');
            const ackLabel = modal.querySelector('.auth-legal-modal-ack');

            if (!body || !ack) return;

            bindModalAckStorage(ack);

            body.addEventListener('scroll', () => {
                syncModalScrollGate(modal);
            }, { passive: true });

            ack.addEventListener('change', () => {
                syncModalOkButton(modal);
            });

            ackLabel?.addEventListener('click', (e) => {
                if (ack.disabled) {
                    e.preventDefault();
                }
            });

            modal._resetLegalScrollGate = () => resetModalScrollGate(modal);
        });
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (typeof modal._resetLegalScrollGate === 'function') {
            modal._resetLegalScrollGate();
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.auth-legal-modal:not([hidden])') && !isPromptOpen()) {
            document.body.style.overflow = '';
        }
    }

    function isPromptOpen() {
        const prompt = document.getElementById('legalConsentPrompt');
        return prompt && !prompt.hidden;
    }

    function openConsentPrompt() {
        const prompt = document.getElementById('legalConsentPrompt');
        if (!prompt) return;
        prompt.hidden = false;
        prompt.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeConsentPrompt(clearPending = true) {
        const prompt = document.getElementById('legalConsentPrompt');
        if (!prompt) return;
        prompt.hidden = true;
        prompt.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.auth-legal-modal:not([hidden])')) {
            document.body.style.overflow = '';
        }
        if (clearPending) {
            pendingSubmit = false;
        }
    }

    function credentialsLookValid(form) {
        const emailInput = form.querySelector('#email');
        const passwordInput = form.querySelector('#password');
        const email = emailInput?.value.trim() || '';
        const password = passwordInput?.value || '';
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (!emailOk || !password) {
            return false;
        }

        if (document.body.classList.contains('sk-signin-page')) {
            return password.length >= 8 && password.length <= 64;
        }

        return true;
    }

    function bindModals() {
        document.querySelectorAll('[data-open-legal-modal]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openModal(btn.getAttribute('data-open-legal-modal'));
            });
        });

        document.querySelectorAll('[data-close-legal-modal]').forEach((el) => {
            el.addEventListener('click', () => {
                if (el.disabled) return;
                closeModal(el.getAttribute('data-close-legal-modal'));
            });
        });

        document.querySelectorAll('[data-close-legal-prompt]').forEach((el) => {
            el.addEventListener('click', () => {
                closeConsentPrompt();
            });
        });

        document.getElementById('legalConsentAgreeBtn')?.addEventListener('click', () => {
            const consent = document.getElementById('signinLegalConsent');
            const form = document.getElementById('signinForm');
            if (consent) {
                consent.checked = true;
                consent.dispatchEvent(new Event('change', { bubbles: true }));
            }
            closeConsentPrompt(false);
            if (form) {
                pendingSubmit = true;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (isPromptOpen()) {
                closeConsentPrompt();
                return;
            }
            document.querySelectorAll('.auth-legal-modal:not([hidden])').forEach((modal) => {
                closeModal(modal.id);
            });
        });
    }

    function bindSigninConsent() {
        const form = document.getElementById('signinForm');
        const consent = document.getElementById('signinLegalConsent');
        const consentError = document.getElementById('legalConsentError');
        const submitBtn = document.getElementById('signinBtn') || form?.querySelector('button[type="submit"]');

        if (!form || !consent) return;

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('auth-legal-submit-blocked');
        }

        consent.addEventListener('change', () => {
            if (consent.checked && consentError) {
                consentError.hidden = true;
            }
        });

        form.addEventListener('submit', (e) => {
            if (pendingSubmit) {
                pendingSubmit = false;
                return;
            }

            if (consent.checked) {
                if (consentError) consentError.hidden = true;
                return;
            }

            if (!credentialsLookValid(form)) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            if (consentError) consentError.hidden = true;
            openConsentPrompt();
        }, true);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLegalModalScrollGates();
        bindModals();
        bindSigninConsent();
    });
})();
