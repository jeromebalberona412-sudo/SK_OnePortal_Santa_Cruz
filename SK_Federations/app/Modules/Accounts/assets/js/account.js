// =============================================================
// account.js — Single JS file for the Accounts module
// Covers: manage table, add/edit SK Fed, add/edit SK Officials,
//         view modal, pagination, batch upload, bulk actions
// =============================================================

// ── Alpine.js accounts page state (selection + bulk bar only) ───
window.accountsPage = function () {
    return {
        selectedRows: [],
        selectAll: false,

        get selectedCount() {
            return this.selectedRows.length;
        },

        init() {
            this.syncSelectAllState();
        },

        getVisibleRowIds() {
            const tbody = document.getElementById('accountsTableBody');
            if (!tbody) return [];

            return Array.from(tbody.querySelectorAll('tr[data-account-id]'))
                .filter((row) => row.style.display !== 'none')
                .map((row) => parseInt(row.dataset.accountId, 10))
                .filter((id) => !Number.isNaN(id));
        },

        toggleRow(id, checked) {
            if (checked) {
                if (!this.selectedRows.includes(id)) {
                    this.selectedRows.push(id);
                }
            } else {
                this.selectedRows = this.selectedRows.filter((rowId) => rowId !== id);
            }
            this.syncSelectAllState();
        },

        toggleSelectAll(checked) {
            const visibleIds = this.getVisibleRowIds();
            if (checked) {
                visibleIds.forEach((id) => {
                    if (!this.selectedRows.includes(id)) {
                        this.selectedRows.push(id);
                    }
                });
            } else {
                this.selectedRows = this.selectedRows.filter((id) => !visibleIds.includes(id));
            }
            this.selectAll = checked;
        },

        syncSelectAllState() {
            const visibleIds = this.getVisibleRowIds();
            this.selectAll = visibleIds.length > 0 && visibleIds.every((id) => this.selectedRows.includes(id));
        },

        openBulkDelete() {
            if (this.selectedRows.length === 0) return;
            window.AccountsDeleteModal.openBulk([...this.selectedRows]);
        },

        clearSelection() {
            this.selectedRows = [];
            this.selectAll = false;
        },
    };
};

// ── Delete modal (vanilla JS — reliable show/hide) ─────────────
window.AccountsDeleteModal = (function () {
    let mode = 'single';
    let targetId = null;
    let bulkIds = [];

    function els() {
        return {
            modal: document.getElementById('deleteAccountModal'),
            title: document.getElementById('deleteModalTitle'),
            message: document.getElementById('deleteModalMessage'),
            input: document.getElementById('deleteConfirmationInput'),
            hintError: document.getElementById('deleteConfirmHintError'),
            hintSuccess: document.getElementById('deleteConfirmHintSuccess'),
            confirmBtn: document.getElementById('deleteModalConfirmBtn'),
            cancelBtn: document.getElementById('deleteModalCancelBtn'),
        };
    }

    function updateConfirmState() {
        const { input, hintError, hintSuccess, confirmBtn } = els();
        if (!input || !confirmBtn) return;

        const value = input.value;
        const matched = value === 'Delete';

        if (hintError) hintError.style.display = value.length > 0 && !matched ? 'block' : 'none';
        if (hintSuccess) hintSuccess.style.display = matched ? 'block' : 'none';

        confirmBtn.disabled = !matched;
        confirmBtn.classList.toggle('is-enabled', matched);
        confirmBtn.classList.toggle('is-disabled', !matched);
    }

    function resetForm() {
        const { input, hintError, hintSuccess, confirmBtn } = els();
        if (input) input.value = '';
        if (hintError) hintError.style.display = 'none';
        if (hintSuccess) hintSuccess.style.display = 'none';
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.classList.remove('is-enabled');
            confirmBtn.classList.add('is-disabled');
        }
    }

    function openModal() {
        const { modal, input } = els();
        if (!modal) return;
        resetForm();
        modal.setAttribute('aria-hidden', 'false');
        toggleModal('deleteAccountModal', true);
        window.setTimeout(() => input?.focus(), 50);
    }

    function closeModal() {
        const { modal } = els();
        if (modal) modal.setAttribute('aria-hidden', 'true');
        toggleModal('deleteAccountModal', false);
        mode = 'single';
        targetId = null;
        bulkIds = [];
        resetForm();
    }

    async function confirmDelete() {
        const { input } = els();
        if (!input || input.value !== 'Delete') return;

        showLoadingOverlay('Deleting...');

        try {
            if (mode === 'bulk') {
                const response = await fetch('/accounts/bulk-deactivate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ account_ids: bulkIds }),
                });
                const data = await response.json().catch(() => ({}));
                hideLoadingOverlay();

                if (!response.ok || !data.success) {
                    alert(data.message || 'Failed to delete selected accounts.');
                    return;
                }

                bulkIds.forEach((id) => {
                    const row = document.querySelector(`tr[data-account-id="${id}"]`);
                    if (row) row.remove();
                });

                const root = document.getElementById('mainContent');
                if (root?._x_dataStack?.[0]?.clearSelection) {
                    root._x_dataStack[0].clearSelection();
                }

                if (typeof window.refreshAccountsPagination === 'function') {
                    window.refreshAccountsPagination();
                }

                closeModal();
                showAccountToast(data.message || 'Accounts deleted successfully!', 'delete');
                return;
            }

            const response = await fetch(`/accounts/${targetId}/deactivate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            });
            const data = await response.json().catch(() => ({}));
            hideLoadingOverlay();

            if (!response.ok || !data.success) {
                alert(data.message || 'Failed to delete account.');
                return;
            }

            const row = document.querySelector(`tr[data-account-id="${targetId}"]`);
            if (row) row.remove();

            const root = document.getElementById('mainContent');
            const alpine = root?._x_dataStack?.[0];
            if (alpine && Array.isArray(alpine.selectedRows)) {
                alpine.selectedRows = alpine.selectedRows.filter((id) => id !== targetId);
                alpine.syncSelectAllState?.();
            }

            if (typeof window.refreshAccountsPagination === 'function') {
                window.refreshAccountsPagination();
            }

            closeModal();
            showAccountToast('Account deleted successfully!', 'delete');
        } catch (error) {
            hideLoadingOverlay();
            alert('An unexpected error occurred. Please try again.');
        }
    }

    function wireEvents() {
        const { input, confirmBtn, cancelBtn, modal } = els();
        if (!modal || modal.dataset.wired === '1') return;

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        modal.dataset.wired = '1';
        modal.setAttribute('aria-hidden', 'true');

        input?.addEventListener('input', updateConfirmState);
        confirmBtn?.addEventListener('click', confirmDelete);
        cancelBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display !== 'none' && modal.style.display !== '') {
                closeModal();
            }
        });
    }

    return {
        wireEvents,
        openSingle(id) {
            mode = 'single';
            targetId = id;
            bulkIds = [];
            const { title, message } = els();
            if (title) title.textContent = 'Delete Account';
            if (message) message.textContent = 'Are you sure you want to permanently delete this account?';
            openModal();
        },
        openBulk(ids) {
            mode = 'bulk';
            targetId = null;
            bulkIds = [...ids];
            const { title, message } = els();
            if (title) title.textContent = 'Delete Selected Accounts';
            if (message) message.textContent = 'Are you sure you want to permanently delete the selected account(s)?';
            openModal();
        },
        close: closeModal,
    };
})();

// ── Shared helpers ────────────────────────────────────────────
function getXlsxLib() {
    return window.XLSX || null;
}
function lockBodyScroll() {
    if (document.body.dataset.scrollLocked === '1') return;

    const scrollY = window.scrollY;
    document.body.dataset.scrollLocked = '1';
    document.body.dataset.scrollY = String(scrollY);
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
}

function unlockBodyScroll() {
    if (document.body.dataset.scrollLocked !== '1') {
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('position');
        document.body.style.removeProperty('top');
        document.body.style.removeProperty('width');
        return;
    }

    const scrollY = parseInt(document.body.dataset.scrollY || '0', 10);
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('position');
    document.body.style.removeProperty('top');
    document.body.style.removeProperty('width');
    delete document.body.dataset.scrollLocked;
    delete document.body.dataset.scrollY;
    window.scrollTo(0, scrollY);
}

function isAnyModalOpen() {
    return Array.from(document.querySelectorAll('.modal-overlay')).some((modal) => {
        const display = window.getComputedStyle(modal).display;
        return display !== 'none';
    });
}

function hideAllAccountModals() {
    document.querySelectorAll('.modal-overlay').forEach((modal) => {
        modal.style.display = 'none';
    });
}

function cleanupAccountUiState() {
    hideLoadingOverlay();
    hideAllAccountModals();
    if (!isAnyModalOpen()) {
        unlockBodyScroll();
    }
}

function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.style.display = show ? 'flex' : 'none';

    if (show) {
        lockBodyScroll();
        return;
    }

    if (!isAnyModalOpen()) {
        unlockBodyScroll();
    }
}

// ── Top toast notification ─────────────────────────────────────
let _toastTimer = null;
function showAccountToast(msg, type) {
    // type: 'success' | 'edit' | 'delete'
    const idMap = { success: 'accountToast', edit: 'accountToastEdit', delete: 'accountToastDelete' };
    const msgMap = { success: 'accountToastMsg', edit: 'accountToastEditMsg', delete: 'accountToastDeleteMsg' };
    const toastId = idMap[type] || 'accountToast';
    const msgId = msgMap[type] || 'accountToastMsg';

    // Hide all toasts first
    ['accountToast', 'accountToastEdit', 'accountToastDelete'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
    });

    const toast = document.getElementById(toastId);
    const msgEl = document.getElementById(msgId);
    if (!toast) return;
    if (msgEl) msgEl.textContent = msg;
    toast.classList.add('show');
    if (_toastTimer) clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
}

function getCurrentAccountType() {
    return window.location.pathname.includes('/accounts/officials') ? 'sk_officials' : 'sk_federation';
}

function calculateAge(dateOfBirthValue) {
    if (!dateOfBirthValue) return '';
    const dob = new Date(dateOfBirthValue);
    if (isNaN(dob.getTime())) return '';
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    return age >= 0 ? String(age) : '';
}

function attachDobAgeAutoFill(form, dobName, ageName) {
    if (!form) return;
    const dob = form.querySelector(`[name="${dobName}"]`);
    const age = form.querySelector(`[name="${ageName}"]`);
    if (!dob || !age) return;
    const update = () => { age.value = calculateAge(dob.value); };
    dob.addEventListener('change', update);
    dob.addEventListener('input', update);
    update();
}

function setFormFieldValue(form, name, value) {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.value = value;
}

function showLoadingOverlay(message = 'Processing...') {
    if (typeof window.showLoading === 'function') {
        window.showLoading(message);
        return;
    }

    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `<div class="loading-spinner"><div class="spinner"></div><p>${message}</p></div>`;
        document.body.appendChild(overlay);
    } else {
        const label = overlay.querySelector('p');
        if (label) label.textContent = message;
    }
    overlay.style.display = 'flex';
    lockBodyScroll();
}

function hideLoadingOverlay() {
    if (typeof window.hideLoading === 'function') {
        window.hideLoading();
    }

    const globalOverlay = document.getElementById('globalLoadingOverlay');
    if (globalOverlay) {
        globalOverlay.classList.remove('gl-visible');
        globalOverlay.setAttribute('aria-hidden', 'true');
    }

    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }

    if (typeof window.LoadingScreen !== 'undefined' && window.LoadingScreen.hide) {
        window.LoadingScreen.hide();
    }

    if (!isAnyModalOpen()) {
        unlockBodyScroll();
    }
}

function showFieldError(form, fieldName, message) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return;
    field.classList.add('error');
    const err = field.parentElement.querySelector('.form-error');
    if (err) { err.textContent = message; err.classList.add('show'); }
}

function clearAllErrors(form) {
    form.querySelectorAll('.form-input-modern, .form-input-light').forEach(f => f.classList.remove('error', 'is-invalid', 'is-valid'));
    form.querySelectorAll('.form-error, .form-error-light').forEach(e => { e.textContent = ''; e.classList.remove('show'); });
    form.querySelectorAll('.validation-error').forEach(e => e.remove());
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Inline validation helpers (light-theme forms) ─────────────
const ACCOUNT_TERM_MAX_YEARS = 5;

function _showErr(input, msg) {
    _clearErr(input);
    const span = document.createElement('span');
    span.className = 'validation-error';
    span.textContent = msg;
    input.parentNode.appendChild(span);
    input.classList.add('is-invalid');
}
function _clearErr(input) {
    input.classList.remove('is-invalid', 'is-valid');
    const ex = input.parentNode.querySelector('.validation-error');
    if (ex) ex.remove();
}

function getCurrentYearStartDate() {
    return `${new Date().getFullYear()}-01-01`;
}

function addYearsToDateString(dateStr, years) {
    const date = new Date(`${dateStr}T00:00:00`);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    date.setFullYear(date.getFullYear() + years);
    return date.toISOString().slice(0, 10);
}

function isNameField(input) {
    return ['first_name', 'last_name', 'middle_name'].includes(input.name);
}

function isContactField(input) {
    return input.name === 'contact_number';
}

function isTermField(input) {
    return input.name === 'term_start' || input.name === 'term_end';
}

function _validateNameField(input) {
    const val = (input.value || '').trim();
    if (input.hasAttribute('required') && !val) {
        _showErr(input, 'This field is required');
        return false;
    }
    if (val && !/^[A-Z\s\-']+$/.test(val)) {
        _showErr(input, 'Use uppercase letters only');
        return false;
    }
    _clearErr(input);
    if (val) input.classList.add('is-valid');
    return true;
}

function _validateContactNumber(input) {
    const val = (input.value || '').trim();
    if (input.hasAttribute('required') && !val) {
        _showErr(input, 'Contact number is required');
        return false;
    }
    if (val && !/^09\d{9}$/.test(val)) {
        _showErr(input, 'Contact number must be 11 digits starting with 09');
        return false;
    }
    _clearErr(input);
    if (val) input.classList.add('is-valid');
    return true;
}

function validateTermRange(form) {
    const startInput = form.querySelector('[name="term_start"]');
    const endInput = form.querySelector('[name="term_end"]');
    if (!startInput || !endInput) {
        return true;
    }

    const start = startInput.value;
    const end = endInput.value;
    const yearStart = getCurrentYearStartDate();

    if (startInput.hasAttribute('required') && !start) {
        _showErr(startInput, 'Term start date is required');
        return false;
    }
    if (endInput.hasAttribute('required') && !end) {
        _showErr(endInput, 'Term end date is required');
        return false;
    }
    if (start && start < yearStart) {
        _showErr(startInput, 'Term start date cannot be before the current year');
        return false;
    }
    if (start && end && end <= start) {
        _showErr(endInput, 'Term end date must be after term start date');
        return false;
    }
    if (start && end) {
        const maxEnd = addYearsToDateString(start, ACCOUNT_TERM_MAX_YEARS);
        if (maxEnd && end > maxEnd) {
            _showErr(endInput, 'Term end date must be within 5 years of term start date');
            return false;
        }
    }

    _clearErr(startInput);
    _clearErr(endInput);
    if (start) startInput.classList.add('is-valid');
    if (end) endInput.classList.add('is-valid');
    return true;
}

function _validateField(input) {
    const val = input.value.trim();
    if (isNameField(input)) {
        return _validateNameField(input);
    }
    if (isContactField(input)) {
        return _validateContactNumber(input);
    }
    if (isTermField(input)) {
        return validateTermRange(input.closest('form'));
    }
    if (input.hasAttribute('required') && !val) { _showErr(input, 'This field is required'); return false; }
    if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { _showErr(input, 'Enter a valid email address'); return false; }
    if (input.tagName === 'SELECT' && input.hasAttribute('required') && (!val || val === '')) { _showErr(input, 'Please select an option'); return false; }
    _clearErr(input);
    if (val) input.classList.add('is-valid');
    return true;
}

function applyUppercaseNameInput(input) {
    if (!input) return;
    input.addEventListener('input', () => {
        input.value = input.value.replace(/[^a-zA-Z\s\-']/g, '').toUpperCase();
        if (input.classList.contains('is-invalid')) {
            _validateNameField(input);
        }
    });
    input.addEventListener('blur', () => _validateNameField(input));
}

function applyContactNumberInput(input) {
    if (!input) return;
    const prefix = '09';

    input.addEventListener('focus', () => {
        if (!input.value.startsWith(prefix)) {
            input.value = prefix;
        }
    });

    input.addEventListener('input', () => {
        let digits = input.value.replace(/\D/g, '');
        if (!digits.startsWith('09')) {
            digits = `${prefix}${digits.replace(/^0+/, '')}`;
        }
        input.value = digits.slice(0, 11);
        if (input.classList.contains('is-invalid')) {
            _validateContactNumber(input);
        }
    });

    input.addEventListener('blur', () => _validateContactNumber(input));
}

function clampDateInputYear(input) {
    if (!input || input.type !== 'date') {
        return;
    }

    input.addEventListener('input', () => {
        const value = input.value;
        if (!value) {
            return;
        }

        const match = /^(\d+)-(\d{2})-(\d{2})$/.exec(value);
        if (!match) {
            input.value = '';
            return;
        }

        const year = match[1];
        if (year.length !== 4) {
            input.value = '';
        }
    });
}

function applyFutureOnlyDateConstraints(form) {
    const today = new Date().toISOString().slice(0, 10);
    form.querySelectorAll('input[type="date"]').forEach((input) => {
        if (input.name === 'date_of_birth') {
            input.max = today;
            return;
        }

        if (isTermField(input)) {
            return;
        }

        input.min = today;
        clampDateInputYear(input);
    });
}

function applyTermDateConstraints(form) {
    const startInput = form.querySelector('[name="term_start"]');
    const endInput = form.querySelector('[name="term_end"]');
    if (!startInput || !endInput) {
        return;
    }

    const yearStart = getCurrentYearStartDate();
    startInput.min = yearStart;
    clampDateInputYear(startInput);
    clampDateInputYear(endInput);

    const syncEndConstraints = () => {
        const startVal = startInput.value;
        if (startVal) {
            endInput.min = startVal;
            endInput.max = addYearsToDateString(startVal, ACCOUNT_TERM_MAX_YEARS);
        } else {
            endInput.min = yearStart;
            endInput.removeAttribute('max');
        }

        if (endInput.value && startVal && endInput.value < startVal) {
            endInput.value = '';
        }
        if (endInput.value && endInput.max && endInput.value > endInput.max) {
            endInput.value = endInput.max;
        }

        validateTermRange(form);
    };

    startInput.addEventListener('change', syncEndConstraints);
    startInput.addEventListener('input', syncEndConstraints);
    endInput.addEventListener('change', () => validateTermRange(form));
    endInput.addEventListener('blur', () => validateTermRange(form));
    syncEndConstraints();
}

function initCreateAccountFormDefaults(form) {
    if (!form) {
        return;
    }

    const status = form.querySelector('[name="status"]');
    if (status) {
        status.value = 'ACTIVE';
    }

    const contact = form.querySelector('[name="contact_number"]');
    if (contact && (!contact.value || contact.value.length < 2)) {
        contact.value = '09';
    }
}

function wireCreateAccountForm(form) {
    if (!form) {
        return;
    }

    form.querySelectorAll('[name="first_name"], [name="last_name"], [name="middle_name"]').forEach(applyUppercaseNameInput);
    applyContactNumberInput(form.querySelector('[name="contact_number"]'));
    applyTermDateConstraints(form);
    applyFutureOnlyDateConstraints(form);
    initCreateAccountFormDefaults(form);

    form.querySelectorAll('[required]').forEach((el) => {
        el.addEventListener('blur', () => _validateField(el));
    });
}

// ── Add SK Officials modal ────────────────────────────────────
let addOfficialsIsMaximized = false;
const ICON_MAXIMIZE = '\u25A1';   // □  empty square  (maximize)
const ICON_RESTORE = '\u29C9';   // ⧉  overlapping squares (restore down)

window.toggleAddOfficialsSize = function () {
    const overlay = document.getElementById('addSkOfficialsModal');
    const content = document.getElementById('addSkOfficialsModalContent');
    const icon = document.getElementById('addOfficialsResizeIcon');
    const btn = document.getElementById('addOfficialsResizeBtn');
    if (!overlay || !content || !icon) return;
    addOfficialsIsMaximized = !addOfficialsIsMaximized;
    if (addOfficialsIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down'; icon.innerHTML = '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>';
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize'; icon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
    }
};

window.openAddSkOfficialsModal = function () {
    switchAddOfficialTab('manual');
    toggleModal('addSkOfficialsModal', true);
    initCreateAccountFormDefaults(document.getElementById('addSkOfficialsForm'));
};

window.closeAddSkOfficialsModal = function () {
    addOfficialsIsMaximized = false;
    const content = document.getElementById('addSkOfficialsModalContent');
    if (content) content.style.cssText = '';
    const icon = document.getElementById('addOfficialsResizeIcon');
    const btn = document.getElementById('addOfficialsResizeBtn');
    if (icon) icon.textContent = ICON_MAXIMIZE;
    if (btn) btn.title = 'Maximize';
    const form = document.getElementById('addSkOfficialsForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));
        form.querySelectorAll('.validation-error').forEach(el => el.remove());
    }
    switchAddOfficialTab('manual');
    resetBatchUploadPanel('official');
    toggleModal('addSkOfficialsModal', false);
};

window.showSkOfficialsSuccessModal = function () { showAccountToast('SK Officials account successfully created!', 'success'); };
window.closeSkOfficialsSuccessModal = function () { };

window.switchAddOfficialTab = function (tab) {
    const manual = document.getElementById('addOfficialManualPane');
    const batch = document.getElementById('addOfficialBatchPane');
    const manualFooter = document.getElementById('addOfficialManualFooter');
    const batchFooter = document.getElementById('addOfficialBatchFooter');
    const tM = document.getElementById('tabManual');
    const tB = document.getElementById('tabBatch');
    const isManual = tab === 'manual';
    if (manual) manual.style.display = isManual ? '' : 'none';
    if (batch) batch.style.display = isManual ? 'none' : '';
    if (manualFooter) manualFooter.style.display = isManual ? '' : 'none';
    if (batchFooter) batchFooter.style.display = isManual ? 'none' : '';
    if (tM) tM.classList.toggle('active', isManual);
    if (tB) tB.classList.toggle('active', !isManual);
    if (!isManual && typeof window.resetBatchUploadPanel === 'function') {
        window.resetBatchUploadPanel('official');
    }
};

window.switchAddFedTab = function (tab) {
    const manual = document.getElementById('addFedManualPane');
    const batch = document.getElementById('addFedBatchPane');
    const manualFooter = document.getElementById('addFedManualFooter');
    const batchFooter = document.getElementById('addFedBatchFooter');
    const tM = document.getElementById('fedTabManual');
    const tB = document.getElementById('fedTabBatch');
    const isManual = tab === 'manual';
    if (manual) manual.style.display = isManual ? '' : 'none';
    if (batch) batch.style.display = isManual ? 'none' : '';
    if (manualFooter) manualFooter.style.display = isManual ? '' : 'none';
    if (batchFooter) batchFooter.style.display = isManual ? 'none' : '';
    if (tM) tM.classList.toggle('active', isManual);
    if (tB) tB.classList.toggle('active', !isManual);
    if (!isManual && typeof window.resetBatchUploadPanel === 'function') {
        window.resetBatchUploadPanel('fed');
    }
};

// ── Edit SK Officials modal ───────────────────────────────────
let editOfficialsIsMaximized = false;

window.openEditSkOfficialsModal = function () { toggleModal('editSkOfficialsModal', true); };

window.closeEditSkOfficialsModal = function () {
    editOfficialsIsMaximized = false;
    const overlay = document.getElementById('editSkOfficialsModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    if (overlay) overlay.style.padding = '';
    if (content) content.style.cssText = '';
    const icon = document.getElementById('editOfficialsResizeIcon');
    const btn = document.getElementById('editOfficialsResizeBtn');
    if (icon) icon.textContent = ICON_MAXIMIZE;
    if (btn) btn.title = 'Maximize';
    const form = document.getElementById('editSkOfficialsForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid', 'is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    toggleModal('editSkOfficialsModal', false);
};

window.showEditSkOfficialsSuccessModal = function () {
    toggleModal('editSkOfficialsModal', false);
    showAccountToast('SK Officials account updated successfully!', 'edit');
};
window.closeEditSkOfficialsSuccessModal = function () { };

window.toggleEditOfficialsSize = function () {
    const overlay = document.getElementById('editSkOfficialsModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    const icon = document.getElementById('editOfficialsResizeIcon');
    const btn = document.getElementById('editOfficialsResizeBtn');
    if (!overlay || !content || !icon) return;
    editOfficialsIsMaximized = !editOfficialsIsMaximized;
    if (editOfficialsIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down'; icon.innerHTML = '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>';
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize'; icon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
    }
};
// Keep legacy aliases so any remaining references don't break
window.toggleFullscreenEditSkOfficialsModal = window.toggleEditOfficialsSize;
window.toggleRestoreEditSkOfficialsModal = window.toggleEditOfficialsSize;
window.restoreEditSkOfficialsModal = window.toggleEditOfficialsSize;

// ── Add SK Federation modal ───────────────────────────────────
let addFedIsMaximized = false;

window.toggleAddFedSize = function () {
    const overlay = document.getElementById('addAccountModal');
    const content = document.getElementById('addSkFedModalContent');
    const icon = document.getElementById('addFedResizeIcon');
    const btn = document.getElementById('addFedResizeBtn');
    if (!overlay || !content || !icon) return;
    addFedIsMaximized = !addFedIsMaximized;
    if (addFedIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down';
        icon.innerHTML = '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>';
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize';
        icon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
    }
};

window.openAddAccountModal = function () {
    const type = getCurrentAccountType();
    const ids = _getModalIds(type);
    if (type === 'sk_officials') {
        switchAddOfficialTab('manual');
    } else {
        switchAddFedTab('manual');
    }
    toggleModal(ids.addModalId, true);
    const formId = type === 'sk_officials' ? 'addSkOfficialsForm' : 'addSkFedForm';
    initCreateAccountFormDefaults(document.getElementById(formId));
};

window.closeAddAccountModal = function () {
    addFedIsMaximized = false;
    const content = document.getElementById('addSkFedModalContent');
    if (content) content.style.cssText = '';
    const icon = document.getElementById('addFedResizeIcon');
    const btn = document.getElementById('addFedResizeBtn');
    if (icon) icon.textContent = ICON_MAXIMIZE;
    if (btn) btn.title = 'Maximize';
    const form = document.getElementById('addSkFedForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid', 'is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    switchAddFedTab('manual');
    resetBatchUploadPanel('fed');
    const ids = _getModalIds(getCurrentAccountType());
    toggleModal(ids.addModalId, false);
};

window.showAddSuccessModal = function () { showAccountToast('Account successfully created!', 'success'); };
window.closeAddSuccessModal = function () { };

// ── Edit SK Federation modal ──────────────────────────────────
let editFedIsMaximized = false;

window.openEditModal = function () { toggleModal('editAccountModal', true); };

window.closeEditModal = function () {
    editFedIsMaximized = false;
    const overlay = document.getElementById('editAccountModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    if (overlay) overlay.style.padding = '';
    if (content) content.style.cssText = '';
    const icon = document.getElementById('editFedResizeIcon');
    const btn = document.getElementById('editFedResizeBtn');
    if (icon) icon.textContent = ICON_MAXIMIZE;
    if (btn) btn.title = 'Maximize';
    const form = document.getElementById('editAccountForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid', 'is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    toggleModal('editAccountModal', false);
};

window.showEditSuccessModal = function () {
    toggleModal('editAccountModal', false);
    showAccountToast('Account updated successfully!', 'edit');
};
window.closeEditSuccessModal = function () { };

window.toggleEditFedSize = function () {
    const overlay = document.getElementById('editAccountModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    const icon = document.getElementById('editFedResizeIcon');
    const btn = document.getElementById('editFedResizeBtn');
    if (!overlay || !content || !icon) return;
    editFedIsMaximized = !editFedIsMaximized;
    if (editFedIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down'; icon.innerHTML = '<path d="M4 14h6v6"></path><path d="M20 10h-6V4"></path><path d="M14 10l7-7"></path><path d="M3 21l7-7"></path>';
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize'; icon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>';
    }
};
// Keep legacy aliases so any remaining references don't break
window.toggleFullscreenEditAccountModal = window.toggleEditFedSize;
window.toggleRestoreEditAccountModal = window.toggleEditFedSize;
window.restoreEditAccountModal = window.toggleEditFedSize;

// ── View modal ────────────────────────────────────────────────
let viewIsMaximized = false;

window.openViewModal = function () {
    toggleModal('viewAccountModal', true);
};
window.closeViewModal = function () {
    viewIsMaximized = false;
    const overlay = document.getElementById('viewAccountModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    if (overlay) overlay.style.padding = '';
    if (content) content.style.cssText = '';
    const icon = document.getElementById('viewResizeIcon');
    const btn = document.getElementById('viewToggleBtn');
    if (icon) icon.textContent = ICON_MAXIMIZE;
    if (btn) btn.title = 'Maximize';
    toggleModal('viewAccountModal', false);
};
window.toggleFullscreenViewModal = function () {
    const overlay = document.getElementById('viewAccountModal');
    const content = overlay ? overlay.querySelector('.modal-content') : null;
    const icon = document.getElementById('viewResizeIcon');
    const btn = document.getElementById('viewToggleBtn');
    if (!overlay || !content || !icon) return;
    viewIsMaximized = !viewIsMaximized;
    if (viewIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down'; icon.textContent = ICON_RESTORE;
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize'; icon.textContent = ICON_MAXIMIZE;
    }
};
window.toggleRestoreViewModal = window.toggleFullscreenViewModal;
window.restoreViewModal = window.toggleFullscreenViewModal;

// ── Internal helpers ──────────────────────────────────────────
function _setModalBtns(modal, state) {
    const fb = modal.querySelector('.modal-fullscreen-btn');
    const rb = modal.querySelector('.modal-restore-btn');
    if (state === 'fullscreen') {
        if (fb) { fb.title = 'Restore Down'; fb.style.display = 'none'; }
        if (rb) rb.style.display = 'inline-flex';
    } else {
        if (fb) { fb.title = 'Maximize'; fb.style.display = 'inline-flex'; }
        if (rb) rb.style.display = 'none';
    }
}

function _getModalIds(type) {
    if (type === 'sk_officials') return { addModalId: 'addSkOfficialsModal', successModalId: 'skOfficialsSuccessModal' };
    return { addModalId: 'addAccountModal', successModalId: 'addSuccessModal' };
}

function _closeEditByType() {
    if (getCurrentAccountType() === 'sk_officials') { closeEditSkOfficialsModal(); return; }
    closeEditModal();
}
function _showEditSuccessByType() {
    if (getCurrentAccountType() === 'sk_officials') { showEditSkOfficialsSuccessModal(); return; }
    showEditSuccessModal();
}

// ── Batch upload panels (embedded in Add modals) ──────────────
const BATCH_HEADER_ALIASES = {
    'first name': 'first_name', first_name: 'first_name',
    'middle name': 'middle_name', middle_name: 'middle_name',
    'last name': 'last_name', last_name: 'last_name',
    suffix: 'suffix',
    sex: 'sex', gender: 'sex',
    birthdate: 'date_of_birth', 'birth date': 'date_of_birth', 'date of birth': 'date_of_birth',
    date_of_birth: 'date_of_birth', dob: 'date_of_birth',
    age: 'age',
    'contact number': 'contact_number', contact_number: 'contact_number', contact: 'contact_number',
    position: 'position',
    region: 'region',
    province: 'province',
    municipality: 'municipality',
    barangay: 'barangay', 'barangay name': 'barangay', barangay_name: 'barangay',
    'term start': 'term_start', 'term start date': 'term_start', term_start: 'term_start', 'start date': 'term_start',
    'term end': 'term_end', 'term end date': 'term_end', term_end: 'term_end', 'end date': 'term_end',
    'email address': 'email', email: 'email',
};

const BATCH_TEMPLATE_HEADERS = [
    'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Sex', 'Birthdate', 'Age', 'Contact Number',
    'Position', 'Region', 'Province', 'Municipality', 'Barangay', 'Term Start Date', 'Term End Date', 'Email Address',
];

const BATCH_OPTIONAL_HEADERS = new Set(['middle name', 'middle_name']);

const BATCH_REQUIRED_HEADERS = BATCH_TEMPLATE_HEADERS.filter(
    (header) => !BATCH_OPTIONAL_HEADERS.has(header.trim().toLowerCase())
);

const BATCH_REQUIRED_ROW_FIELDS = [
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'sex', label: 'Sex' },
    { key: 'date_of_birth', label: 'Birthdate' },
    { key: 'contact_number', label: 'Contact Number' },
    { key: 'position', label: 'Position' },
    { key: 'barangay', label: 'Barangay' },
    { key: 'term_start', label: 'Term Start Date' },
    { key: 'term_end', label: 'Term End Date' },
    { key: 'email', label: 'Email Address' },
];

const _batchPanelState = {};

window.resetBatchUploadPanel = function (prefix) {
    const panel = _batchPanelState[prefix];
    if (!panel) return;
    const { els, state, renderBatchErrorReport } = panel;
    state.parsedRows = [];
    state.parsedHeaders = [];
    state.mappedAccounts = [];
    state.validationErrors = [];
    if (els.fileInput) els.fileInput.value = '';
    if (els.fileLabel) els.fileLabel.textContent = 'Supported: .xlsx, .xls';
    if (els.preview) { els.preview.innerHTML = ''; els.preview.style.display = 'none'; }
    renderBatchErrorReport([]);
    if (els.confirmBtn) els.confirmBtn.disabled = true;
};

function initBatchUploadPanel(prefix, role) {
    const els = {
        fileInput: document.getElementById(`${prefix}_batchFileInput`),
        dropzone: document.getElementById(`${prefix}_batchDropzone`),
        fileLabel: document.getElementById(`${prefix}_batchFileName`),
        preview: document.getElementById(`${prefix}_batchPreview`),
        errorReport: document.getElementById(`${prefix}_batchErrorReport`),
        confirmBtn: document.getElementById(`${prefix}_batchConfirmBtn`),
        errorDownloadBtn: document.getElementById(`${prefix}_batchErrorDownloadBtn`),
        templateLink: document.getElementById(`${prefix}_batchTemplateDownloadLink`),
    };

    if (!els.fileInput && !els.dropzone) return;

    const state = {
        parsedRows: [],
        parsedHeaders: [],
        mappedAccounts: [],
        validationErrors: [],
    };

    const templateType = role === 'sk_fed' ? 'federation' : 'officials';
    const closeAddModal = prefix === 'official' ? closeAddSkOfficialsModal : closeAddAccountModal;

    function getRequiredHeaderGroups() {
        return BATCH_REQUIRED_HEADERS.map((label) => {
            const normalized = normalizeHeaderKey(label);
            const fieldKey = resolveBatchFieldKey(label);

            return {
                label,
                keys: [...new Set([normalized, fieldKey])],
            };
        });
    }

    function normalizeHeaderKey(header) {
        return String(header || '').trim().toLowerCase();
    }

    function resolveBatchFieldKey(header) {
        const normalized = normalizeHeaderKey(header);
        return BATCH_HEADER_ALIASES[normalized] || normalized.replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    }

    function excelSerialToDateString(serial) {
        const utcDays = Math.floor(Number(serial) - 25569);
        return new Date(utcDays * 86400 * 1000).toISOString().slice(0, 10);
    }

    function coerceBatchCellValue(fieldKey, value) {
        if (value === null || value === undefined || value === '') return '';
        if (value instanceof Date) return value.toISOString().slice(0, 10);
        if (typeof value === 'number' && (fieldKey === 'term_end' || fieldKey === 'term_start' || fieldKey === 'date_of_birth')) {
            return excelSerialToDateString(value);
        }
        return String(value).trim();
    }

    function getMissingBatchHeaders(headers) {
        const normalizedHeaders = headers.map(normalizeHeaderKey);
        return getRequiredHeaderGroups()
            .filter((group) => !group.keys.some((key) => normalizedHeaders.includes(key)))
            .map((group) => group.label);
    }

    function validateMappedRow(row, rowNumber) {
        const errors = [];

        BATCH_REQUIRED_ROW_FIELDS.forEach(({ key, label }) => {
            const value = row[key];
            if (value === null || value === undefined || String(value).trim() === '') {
                errors.push({ row: rowNumber, error: `${label} is required.` });
            }
        });

        if (row.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(row.email).trim())) {
            errors.push({ row: rowNumber, error: 'Invalid email address.' });
        }

        if (row.contact_number) {
            const digits = String(row.contact_number).replace(/\D+/g, '');
            const normalized = digits.startsWith('09') ? digits.slice(0, 11) : ('09' + digits.replace(/^0+/, '')).slice(0, 11);
            if (!/^09\d{9}$/.test(normalized)) {
                errors.push({ row: rowNumber, error: 'Contact number must be 11 digits starting with 09.' });
            }
        }

        if (row.sex && !['male', 'female', 'm', 'f'].includes(String(row.sex).trim().toLowerCase())) {
            errors.push({ row: rowNumber, error: 'Sex must be Male or Female.' });
        }

        return errors;
    }

    function validateMappedRows(rows) {
        const errors = [];
        rows.forEach((row, index) => {
            errors.push(...validateMappedRow(row, index + 1));
        });
        return errors;
    }

    function mapRowToAccount(headers, row) {
        const mapped = {};
        headers.forEach((header, index) => {
            const key = resolveBatchFieldKey(header);
            if (!key) return;
            mapped[key] = coerceBatchCellValue(key, row[index]);
        });
        return mapped;
    }

    function formatBatchCell(value) {
        if (value === null || value === undefined || value === '') return '&mdash;';
        if (value instanceof Date) return escapeHtml(value.toLocaleDateString('en-CA'));
        return escapeHtml(String(value).trim());
    }

    function renderBatchErrorReport(errors) {
        if (!els.errorReport) return;
        if (!errors || errors.length === 0) {
            els.errorReport.style.display = 'none';
            els.errorReport.innerHTML = '';
            if (els.errorDownloadBtn) els.errorDownloadBtn.style.display = 'none';
            return;
        }

        const rows = errors.map((item) =>
            '<tr><td>' + escapeHtml(String(item.row)) + '</td><td>' + escapeHtml(item.error || item.message || 'Validation failed') + '</td></tr>'
        ).join('');

        els.errorReport.innerHTML =
            '<p class="batch-row-count batch-row-count-error">Validation errors found. Invalid rows were not imported.</p>' +
            '<div class="batch-preview-wrap"><table class="batch-preview-table batch-error-table">' +
            '<thead><tr><th>Row</th><th>Error</th></tr></thead><tbody>' + rows + '</tbody></table></div>';
        els.errorReport.style.display = '';
        if (els.errorDownloadBtn) els.errorDownloadBtn.style.display = '';
        state.validationErrors = errors;
    }

    function renderBatchPreview(headers, rows, message, hasHeaderErrors = false) {
        if (!els.preview) return;
        renderBatchErrorReport([]);

        if (rows.length === 0) {
            els.preview.innerHTML = '<p class="batch-row-count" style="color:#94a3b8;">' + escapeHtml(message || 'Upload an Excel file to preview rows.') + '</p>';
            els.preview.style.display = '';
            if (els.confirmBtn) els.confirmBtn.disabled = true;
            return;
        }

        const theadCells = headers.map((header) => '<th>' + escapeHtml(header) + '</th>').join('');
        const tbodyRows = rows.map((row) => {
            const cells = headers.map((_, index) => '<td>' + formatBatchCell(row[index]) + '</td>').join('');
            return '<tr>' + cells + '</tr>';
        }).join('');

        const messageClass = hasHeaderErrors ? 'batch-row-count batch-row-count-error' : 'batch-row-count';
        els.preview.innerHTML =
            '<p class="' + messageClass + '">' + escapeHtml(message || '') + '</p>' +
            '<div class="batch-preview-wrap"><table class="batch-preview-table"><thead><tr>' + theadCells + '</tr></thead><tbody>' + tbodyRows + '</tbody></table></div>';
        els.preview.style.display = '';
        if (els.confirmBtn) els.confirmBtn.disabled = hasHeaderErrors;
    }

    function handleBatchFile(file) {
        if (!file) return;
        const XLSX = getXlsxLib();
        if (!XLSX) {
            alert('Batch upload library is still loading. Please try again in a moment.');
            return;
        }

        state.parsedHeaders = [];
        state.parsedRows = [];
        state.mappedAccounts = [];
        state.validationErrors = [];
        renderBatchErrorReport([]);
        if (els.preview) {
            els.preview.innerHTML = '';
            els.preview.style.display = 'none';
        }
        if (els.confirmBtn) els.confirmBtn.disabled = true;

        if (els.fileLabel) els.fileLabel.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array', raw: false, cellDates: true });
                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                const allRows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                const nonEmptyRows = allRows.filter((row) => row.some((cell) => String(cell).trim() !== ''));

                if (nonEmptyRows.length < 2) {
                    state.parsedHeaders = [];
                    state.parsedRows = [];
                    state.mappedAccounts = [];
                    renderBatchPreview([], [], 'No data rows found in the uploaded file.');
                    return;
                }

                state.parsedHeaders = nonEmptyRows[0].map((header) => String(header).trim());
                state.parsedRows = nonEmptyRows.slice(1).filter((row) => row.some((cell) => String(cell).trim() !== ''));
                state.mappedAccounts = state.parsedRows.map((row) => mapRowToAccount(state.parsedHeaders, row));

                const missingHeaders = getMissingBatchHeaders(state.parsedHeaders);
                const rowErrors = missingHeaders.length === 0 ? validateMappedRows(state.mappedAccounts) : [];
                let previewMessage = state.parsedRows.length + ' row' + (state.parsedRows.length !== 1 ? 's' : '') + ' ready for review';
                const hasHeaderErrors = missingHeaders.length > 0;
                const hasRowErrors = rowErrors.length > 0;

                if (missingHeaders.length > 0) {
                    previewMessage = 'Missing required columns: ' + missingHeaders.join(', ') + '.';
                } else if (hasRowErrors) {
                    previewMessage = rowErrors.length + ' row validation error' + (rowErrors.length !== 1 ? 's' : '') + ' found.';
                    renderBatchErrorReport(rowErrors);
                }

                renderBatchPreview(state.parsedHeaders, state.parsedRows, previewMessage, hasHeaderErrors || hasRowErrors);
                if (els.confirmBtn) {
                    els.confirmBtn.disabled = hasHeaderErrors || hasRowErrors || state.parsedRows.length === 0;
                }
                return;
            } catch (err) {
                console.error('Batch upload read error:', err);
                state.parsedHeaders = [];
                state.parsedRows = [];
                state.mappedAccounts = [];
                renderBatchPreview([], [], 'Unable to read the Excel file. Please upload a valid .xlsx or .xls file.');
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function downloadBatchTemplateXlsx(event) {
        if (event) event.preventDefault();
        const XLSX = getXlsxLib();
        if (!XLSX) {
            window.location.href = '/accounts/batch-template/' + templateType;
            return;
        }

        const headers = BATCH_TEMPLATE_HEADERS;
        const worksheet = XLSX.utils.aoa_to_sheet([headers]);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Template');
        const filename = role === 'sk_fed' ? 'sk-federation-batch-template.xlsx' : 'sk-officials-batch-template.xlsx';
        XLSX.writeFile(workbook, filename);
    }

    function downloadErrorReport() {
        if (!state.validationErrors.length) return;
        const lines = ['Row,Error', ...state.validationErrors.map((item) =>
            `${item.row},"${String(item.error || item.message || '').replace(/"/g, '""')}"`
        )];
        const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'batch-upload-errors.csv';
        link.click();
        URL.revokeObjectURL(url);
    }

    _batchPanelState[prefix] = { els, state, renderBatchErrorReport };

    if (els.confirmBtn) {
        els.confirmBtn.addEventListener('click', async function () {
            if (state.mappedAccounts.length === 0) return;
            const missingHeaders = getMissingBatchHeaders(state.parsedHeaders);
            if (missingHeaders.length > 0) {
                alert('Your Excel file is missing required columns:\n\n' + missingHeaders.join('\n'));
                return;
            }

            const rowErrors = validateMappedRows(state.mappedAccounts);
            if (rowErrors.length > 0) {
                renderBatchErrorReport(rowErrors);
                alert('Please fix the validation errors in your Excel file before importing.');
                return;
            }

            showLoadingOverlay('Creating accounts...');
            els.confirmBtn.disabled = true;

            try {
                const response = await fetch('/accounts/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role,
                        headers: state.parsedHeaders,
                        accounts: state.mappedAccounts,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    hideLoadingOverlay();
                    const errors = data.validation_errors || data.failed || [];
                    if (errors.length > 0) {
                        renderBatchErrorReport(errors.map((item) => ({
                            row: item.row,
                            error: item.error || item.message,
                        })));
                    }
                    alert(data.message || 'Batch account creation failed.');
                    els.confirmBtn.disabled = false;
                    return;
                }

                hideLoadingOverlay();
                if (Array.isArray(data.failed) && data.failed.length > 0) {
                    renderBatchErrorReport(data.failed.map((item) => ({
                        row: item.row,
                        error: item.message,
                    })));
                } else {
                    closeAddModal();
                }
                const toastType = Array.isArray(data.failed) && data.failed.length > 0 ? 'edit' : 'success';
                showAccountToast(data.message || 'Accounts created successfully.', toastType);
                window.setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                hideLoadingOverlay();
                els.confirmBtn.disabled = false;
                alert('Unable to create accounts from the uploaded file. Please try again.');
            }
        });
    }

    if (els.errorDownloadBtn) {
        els.errorDownloadBtn.addEventListener('click', downloadErrorReport);
    }

    if (els.fileInput) {
        els.fileInput.addEventListener('click', function () {
            this.value = '';
        });
        els.fileInput.addEventListener('change', function () {
            const file = els.fileInput.files[0];
            if (file) handleBatchFile(file);
        });
    }

    if (els.dropzone) {
        els.dropzone.addEventListener('click', function (e) {
            if (!e.target.classList.contains('dropzone-browse') && els.fileInput) els.fileInput.click();
        });
        els.dropzone.addEventListener('dragover', function (e) { e.preventDefault(); els.dropzone.classList.add('drag-over'); });
        els.dropzone.addEventListener('dragleave', function () { els.dropzone.classList.remove('drag-over'); });
        els.dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            els.dropzone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            if (els.fileInput) els.fileInput.files = dt.files;
            handleBatchFile(file);
        });
    }
}

// ── DOMContentLoaded: wire up everything ──────────────────────
document.addEventListener('DOMContentLoaded', function () {
    cleanupAccountUiState();
    window.AccountsDeleteModal?.wireEvents?.();

    // ── Pagination ────────────────────────────────────────────
    const recordsPerPage = 10;
    let currentPage = 1;
    let allAccounts = [];
    let filteredAccounts = [];

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const paginationNums = document.getElementById('paginationNumbers');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableBody = document.querySelector('.accounts-table tbody');

    function initPagination() {
        const rows = Array.from(tableBody.querySelectorAll('tr')).filter(r => !r.querySelector('td[colspan]'));
        allAccounts = rows.map((el, i) => ({ element: el, index: i }));
        filteredAccounts = [...allAccounts];
        updatePagination();
    }

    function updatePagination() {
        const total = Math.ceil(filteredAccounts.length / recordsPerPage);
        const start = (currentPage - 1) * recordsPerPage;
        const end = Math.min(start + recordsPerPage, filteredAccounts.length);
        allAccounts.forEach(a => { a.element.style.display = 'none'; });
        for (let i = start; i < end; i++) { if (filteredAccounts[i]) filteredAccounts[i].element.style.display = ''; }
        if (paginationInfo) paginationInfo.innerHTML = `Showing <strong>${filteredAccounts.length > 0 ? start + 1 : 0}-${end}</strong> of <strong>${filteredAccounts.length}</strong> accounts`;
        updatePageNumbers(total);
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === total || total === 0;
    }

    function updatePageNumbers(total) {
        if (!paginationNums) return;
        paginationNums.innerHTML = '';
        if (total === 0) return;
        let s = Math.max(1, currentPage - 2), e = Math.min(total, currentPage + 2);
        if (s > 1) { addPageBtn(1); if (s > 2) addEllipsis(); }
        for (let i = s; i <= e; i++) addPageBtn(i);
        if (e < total) { if (e < total - 1) addEllipsis(); addPageBtn(total); }
    }

    function addPageBtn(n) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `pagination-btn pagination-number ${n === currentPage ? 'active' : ''}`;
        btn.textContent = n;
        btn.setAttribute('aria-current', n === currentPage ? 'page' : 'false');
        btn.addEventListener('click', () => { currentPage = n; updatePagination(); syncPaginationSelection(); });
        paginationNums.appendChild(btn);
    }

    function addEllipsis() {
        const s = document.createElement('span');
        s.className = 'pagination-ellipsis';
        s.textContent = '...';
        s.style.cssText = 'padding:0 0.5rem;color:var(--gray-400);font-weight:500;';
        paginationNums.appendChild(s);
    }

    if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; updatePagination(); syncPaginationSelection(); } });
    if (nextBtn) nextBtn.addEventListener('click', () => { const t = Math.ceil(filteredAccounts.length / recordsPerPage); if (currentPage < t) { currentPage++; updatePagination(); syncPaginationSelection(); } });
    if (tableBody) initPagination();

    function syncPaginationSelection() {
        const root = document.getElementById('mainContent');
        if (root && root._x_dataStack && root._x_dataStack[0]) {
            root._x_dataStack[0].syncSelectAllState();
        }
    }

    window.refreshAccountsPagination = function () {
        const rows = Array.from(tableBody.querySelectorAll('tr')).filter(r => !r.querySelector('td[colspan]'));
        allAccounts = rows.map((el, i) => ({ element: el, index: i }));
        filteredAccounts = [...allAccounts];
        const total = Math.ceil(filteredAccounts.length / recordsPerPage);
        if (currentPage > total) currentPage = Math.max(1, total);
        updatePagination();
        syncPaginationSelection();
    };

    // ── Filter dropdowns ──────────────────────────────────────
    const barangayFilter = document.getElementById('barangayFilter');
    if (barangayFilter) {
        barangayFilter.addEventListener('change', function () {
            const form = this.closest('form');
            if (form) form.submit();
        });
    }

    // ── Add SK Officials — backend submit ───────────────────
    const officialsForm = document.getElementById('addSkOfficialsForm');
    if (officialsForm) {
        wireCreateAccountForm(officialsForm);

        officialsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let valid = true;
            officialsForm.querySelectorAll('[required]').forEach(el => { if (!_validateField(el)) valid = false; });
            if (!validateTermRange(officialsForm)) valid = false;
            if (!valid) { const first = officialsForm.querySelector('.is-invalid'); if (first) first.focus(); return; }

            officialsForm.querySelectorAll('.validation-error').forEach(err => err.remove());
            const formData = new FormData(officialsForm);
            const payload = {};
            for (const [k, v] of formData.entries()) { if (k !== '_token') payload[k] = v; }
            payload.term_status = payload.term_status || (payload.status === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE');

            showLoadingOverlay('Creating account...');
            fetch('/accounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async r => {
                    const ct = r.headers.get('content-type') || '';
                    const data = ct.includes('application/json') ? await r.json() : {};
                    return { ok: r.ok, data };
                })
                .then(({ ok, data }) => {
                    if (!ok || !data.success) {
                        cleanupAccountUiState();
                        if (data.errors) {
                            let handledError = false;
                            Object.keys(data.errors).forEach(f => {
                                const input = officialsForm.querySelector(`[name="${f}"]`);
                                if (input) {
                                    _showErr(input, data.errors[f][0]);
                                    handledError = true;
                                }
                            });
                            if (!handledError) {
                                const firstError = Object.values(data.errors).flat()[0] || 'Failed to create account. Please try again.';
                                alert(firstError);
                            }
                        } else {
                            alert(data.message || 'Failed to create account. Please try again.');
                        }
                        return;
                    }

                    cleanupAccountUiState();
                    showAccountToast(data.message || 'SK Officials account created. Password setup email sent.', 'success');
                    window.setTimeout(() => window.location.reload(), 900);
                })
                .catch(() => {
                    cleanupAccountUiState();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    }

    // ── Add SK Federation — backend submit ────────────────────
    const fedForm = document.getElementById('addSkFedForm');
    if (fedForm) {
        wireCreateAccountForm(fedForm);

        fedForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate all required fields first
            let valid = true;
            fedForm.querySelectorAll('[required]').forEach(el => {
                if (!_validateField(el)) valid = false;
            });
            if (!validateTermRange(fedForm)) valid = false;

            if (!valid) {
                const first = fedForm.querySelector('.is-invalid');
                if (first) first.focus();
                return;
            }

            clearAllErrors(fedForm);
            fedForm.querySelectorAll('.validation-error').forEach(err => err.remove());

            const formData = new FormData(fedForm);
            const payload = {};
            for (const [k, v] of formData.entries()) { if (k !== '_token') payload[k] = v; }
            payload.term_status = payload.term_status || (payload.status === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE');

            showLoadingOverlay('Creating account...');
            fetch('/accounts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(async r => { const ct = r.headers.get('content-type') || ''; const data = ct.includes('application/json') ? await r.json() : {}; return { ok: r.ok, data }; })
                .then(({ ok, data }) => {
                    if (!ok || !data.success) {
                        cleanupAccountUiState();
                        if (data.errors) {
                            let handledError = false;
                            Object.keys(data.errors).forEach(f => {
                                const input = fedForm.querySelector(`[name="${f}"]`);
                                if (input) {
                                    _showErr(input, data.errors[f][0]);
                                    handledError = true;
                                }
                            });
                            if (!handledError) {
                                const firstError = Object.values(data.errors).flat()[0] || 'Failed to create account. Please try again.';
                                alert(firstError);
                            }
                        } else {
                            alert('Failed to create account. Please try again.');
                        }
                        return;
                    }
                    cleanupAccountUiState();
                    showAccountToast(data.message || 'SK Federation account created. Password setup email sent.', 'success');
                    window.setTimeout(() => window.location.reload(), 900);
                })
                .catch(() => {
                    cleanupAccountUiState();
                    alert('An unexpected error occurred. Please try again.');
                });
        });
    }

    // ── Edit forms — backend submit ─────────────────────────
    function attachEditSubmit(form) {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearAllErrors(form);
            const accountId = form.dataset.accountId || '';
            if (!accountId) {
                alert('Missing account id. Please refresh and try again.');
                return;
            }

            const formData = new FormData(form);
            const payload = {};
            for (const [k, v] of formData.entries()) { if (k !== '_token') payload[k] = v; }

            showLoadingOverlay();
            fetch(`/accounts/${accountId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(async r => {
                    const ct = r.headers.get('content-type') || '';
                    const data = ct.includes('application/json') ? await r.json() : {};
                    return { ok: r.ok, data };
                })
                .then(({ ok, data }) => {
                    hideLoadingOverlay();
                    if (!ok || !data.success) {
                        if (data.errors) {
                            let handledError = false;
                            Object.keys(data.errors).forEach(f => {
                                const input = form.querySelector(`[name="${f}"]`);
                                if (input) {
                                    _showErr(input, data.errors[f][0]);
                                    handledError = true;
                                }
                            });
                            if (!handledError) {
                                const firstError = Object.values(data.errors).flat()[0] || 'Failed to update account. Please try again.';
                                alert(firstError);
                            }
                        } else alert('Failed to update account. Please try again.');
                        return;
                    }
                    _closeEditByType();
                    _showEditSuccessByType();
                })
                .catch(() => { hideLoadingOverlay(); alert('An unexpected error occurred. Please try again.'); });
        });
    }

    const fedEditForm = document.getElementById('editAccountForm');
    const officialsEditForm = document.getElementById('editSkOfficialsForm');
    attachEditSubmit(fedEditForm);
    attachEditSubmit(officialsEditForm);

    // DOB → age auto-fill
    attachDobAgeAutoFill(fedForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(fedEditForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsForm, 'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsEditForm, 'date_of_birth', 'age');

    // Edit SK Officials — age auto-calc from DOB
    const editOfficialsDob = document.getElementById('edit_sk_officials_date_of_birth');
    const editOfficialsAge = document.getElementById('edit_sk_officials_age');
    if (editOfficialsDob && editOfficialsAge) {
        editOfficialsDob.addEventListener('change', () => { editOfficialsAge.value = calculateAge(editOfficialsDob.value); });
    }

    // ── Edit button click → populate form ────────────────────
    function populateEditForm(form, data) {
        if (!form) return;
        form.dataset.accountId = data.accountId || '';
        ['first_name', 'last_name', 'middle_name', 'suffix', 'sex', 'date_of_birth', 'age', 'contact_number', 'email', 'position', 'barangay_id', 'term_start', 'term_end', 'term_status'].forEach(n => setFormFieldValue(form, n, data[_camel(n)] ?? data[n] ?? ''));
        const statusField = form.querySelector('[name="status"]');
        if (statusField) statusField.value = 'ACTIVE';
        clearAllErrors(form);
    }

    function _camel(s) {
        return s.replace(/_([a-z])/g, (_, c) => c.toUpperCase());
    }

    function openEditWithData(btn) {
        const d = btn.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';
        if (isOfficials) { populateEditForm(officialsEditForm, d); openEditSkOfficialsModal(); return; }
        populateEditForm(fedEditForm, d); openEditModal();
    }

    function resetAccountActionsDropdownPosition(menu) {
        const dropdown = menu?.querySelector('.account-actions-dropdown');
        if (!dropdown) return;
        dropdown.classList.remove('is-floating', 'account-actions-dropdown-up');
        dropdown.style.position = '';
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.right = '';
        dropdown.style.bottom = '';
        dropdown.style.zIndex = '';
    }

    function positionAccountActionsDropdown(menu) {
        const trigger = menu.querySelector('.account-actions-trigger');
        const dropdown = menu.querySelector('.account-actions-dropdown');
        if (!trigger || !dropdown) return;

        dropdown.classList.add('is-floating');
        dropdown.style.position = 'fixed';
        dropdown.style.zIndex = '1300';

        const rect = trigger.getBoundingClientRect();
        const gap = 6;
        const dropdownHeight = dropdown.offsetHeight || 150;
        const dropdownWidth = dropdown.offsetWidth || 188;

        let top = rect.bottom + gap;
        dropdown.classList.remove('account-actions-dropdown-up');

        if (top + dropdownHeight > window.innerHeight - 8 && rect.top - dropdownHeight - gap > 8) {
            top = rect.top - dropdownHeight - gap;
            dropdown.classList.add('account-actions-dropdown-up');
        }

        let right = window.innerWidth - rect.right;
        right = Math.max(8, Math.min(right, window.innerWidth - dropdownWidth - 8));

        dropdown.style.top = `${Math.max(8, top)}px`;
        dropdown.style.right = `${right}px`;
        dropdown.style.left = 'auto';
        dropdown.style.bottom = 'auto';
    }

    function closeAllAccountActionMenus(exceptMenu = null) {
        document.querySelectorAll('.account-actions-menu').forEach((menu) => {
            if (menu === exceptMenu && menu.classList.contains('is-open')) return;
            menu.classList.remove('is-open');
            const trigger = menu.querySelector('.account-actions-trigger');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            resetAccountActionsDropdownPosition(menu);
        });
    }

    // ── Delegated click handlers (covers server-rendered + JS-injected rows) ──
    if (tableBody) {
        tableBody.addEventListener('click', function (e) {
            const trigger = e.target.closest('.account-actions-trigger');
            if (trigger) {
                e.stopPropagation();
                const menu = trigger.closest('.account-actions-menu');
                if (!menu) return;
                const willOpen = !menu.classList.contains('is-open');
                closeAllAccountActionMenus();
                if (willOpen) {
                    menu.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    requestAnimationFrame(() => positionAccountActionsDropdown(menu));
                }
                return;
            }

            const viewBtn = e.target.closest('.btn-view-account');
            const editBtn = e.target.closest('.btn-edit-account');
            const deleteBtn = e.target.closest('.btn-delete-account');
            if (viewBtn) {
                closeAllAccountActionMenus();
                openViewWithData(viewBtn);
            }
            if (editBtn) {
                closeAllAccountActionMenus();
                openEditWithData(editBtn);
            }
            if (deleteBtn) {
                closeAllAccountActionMenus();
                const id = parseInt(deleteBtn.dataset.accountId, 10);
                if (!Number.isNaN(id)) {
                    window.AccountsDeleteModal.openSingle(id);
                }
            }
        });
    }

    document.addEventListener('click', function () {
        closeAllAccountActionMenus();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllAccountActionMenus();
    });

    window.addEventListener('resize', function () {
        const openMenu = document.querySelector('.account-actions-menu.is-open');
        if (openMenu) positionAccountActionsDropdown(openMenu);
    });

    window.addEventListener('scroll', function () {
        closeAllAccountActionMenus();
    }, true);

    // ── View button click → populate view modal ───────────────
    function openViewWithData(btn) {
        const d = btn.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';
        const fullName = [d.firstName, d.middleName, d.lastName]
            .filter(v => v && v.trim())
            .map(v => v.trim().toUpperCase())
            .concat(d.suffix && d.suffix.trim() ? [d.suffix.trim()] : [])
            .join(' ');

        const sections = [];

        sections.push(viewProfileGroup('fa-user', 'Personal Information', [
            ['Full Name', fullName],
            ['Sex', d.sex],
            ['Date of Birth', d.dateOfBirth ? formatDate(d.dateOfBirth) : ''],
            ['Age', d.age],
            ['Contact Number', d.contactNumber],
        ]));

        sections.push(viewProfileGroup('fa-briefcase', 'Position & Account', [
            ['Position', d.position],
            ['Email Address', d.email],
            ['Email Verification', d.emailVerifiedAt || 'Not Verified'],
        ]));

        sections.push(viewProfileGroup('fa-location-dot', 'Address', [
            ['Region', isOfficials ? (d.region || 'IV-A CALABARZON') : (d.region || 'IV-A CALABARZON')],
            ['Province', d.province || 'Laguna'],
            ['Municipality', d.municipality || 'Santa Cruz'],
            ['Barangay', d.barangayName || '-'],
        ]));

        sections.push(viewProfileGroup('fa-calendar-check', 'Term Information', [
            ['Term Start', d.termStart ? formatDate(d.termStart) : ''],
            ['Term End', d.termEnd ? formatDate(d.termEnd) : ''],
            ['Term Status', d.termStatus || 'ACTIVE'],
        ]));

        document.getElementById('viewAccountBody').innerHTML = `
            <div class="account-modal-card">
                ${sections.join('')}
            </div>
        `;
        openViewModal();
    }

    function viewProfileGroup(iconClass, title, fields) {
        const cells = fields.map(([label, value]) => `
            <div class="account-profile-field">
                <label>${escapeHtml(label)}</label>
                <p>${escapeHtml(value || '-')}</p>
            </div>
        `).join('');

        return `
            <div class="account-profile-group">
                <div class="account-profile-group-label">
                    <i class="fa-solid ${iconClass}"></i> ${escapeHtml(title)}
                </div>
                <div class="account-profile-row">${cells}</div>
            </div>
        `;
    }

    // legacy helper kept for compatibility
    function viewSection(title, fields) {
        return viewProfileGroup('fa-circle-info', title, fields);
    }

    // static view bindings handled by delegated listener above

    initBatchUploadPanel('official', 'sk_official');
    initBatchUploadPanel('fed', 'sk_fed');
});
