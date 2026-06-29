/**
 * Login Terms & Privacy consent + modals
 */
(function () {
    'use strict';

    const ACK_STORAGE_PREFIX = 'sk_oneportal_legal_ack_';

    function getAckKey(type) {
        const portal = document.body.classList.contains('sk-login-page') ? 'officials' : 'kabataan';
        return `${ACK_STORAGE_PREFIX}${portal}_${type}`;
    }

    function restoreModalAcks() {
        document.querySelectorAll('[data-legal-ack]').forEach((input) => {
            const type = input.getAttribute('data-legal-ack');
            if (!type) return;
            if (sessionStorage.getItem(getAckKey(type)) === '1') {
                input.checked = true;
            }
            input.addEventListener('change', () => {
                if (input.checked) {
                    sessionStorage.setItem(getAckKey(type), '1');
                }
            });
        });
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.auth-legal-modal:not([hidden])')) {
            document.body.style.overflow = '';
        }
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
                closeModal(el.getAttribute('data-close-legal-modal'));
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.auth-legal-modal:not([hidden])').forEach((modal) => {
                closeModal(modal.id);
            });
        });
    }

    function bindLoginConsent() {
        const form = document.getElementById('loginForm');
        const consent = document.getElementById('loginLegalConsent');
        const consentError = document.getElementById('legalConsentError');
        const submitBtn = document.getElementById('loginBtn') || form?.querySelector('button[type="submit"]');

        if (!form || !consent) return;

        function syncSubmitState() {
            const allowed = consent.checked;
            if (submitBtn) {
                submitBtn.disabled = !allowed;
                submitBtn.classList.toggle('auth-legal-submit-blocked', !allowed);
            }
        }

        syncSubmitState();

        consent.addEventListener('change', () => {
            if (consent.checked && consentError) {
                consentError.hidden = true;
            }
            syncSubmitState();
        });

        form.addEventListener('submit', (e) => {
            if (!consent.checked) {
                e.preventDefault();
                e.stopPropagation();
                if (consentError) consentError.hidden = false;
                consent.focus();
                return false;
            }
        }, true);
    }

    document.addEventListener('DOMContentLoaded', () => {
        restoreModalAcks();
        bindModals();
        bindLoginConsent();
    });
})();
