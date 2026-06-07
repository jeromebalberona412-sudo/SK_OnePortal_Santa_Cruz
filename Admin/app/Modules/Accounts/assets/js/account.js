// =============================================================
// account.js — Single JS file for the Accounts module
// Covers: manage table, add/edit SK Fed, add/edit SK Officials,
//         view modal, pagination, batch upload
// =============================================================

import * as XLSX from 'xlsx';

// ── Shared helpers ────────────────────────────────────────────
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
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
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
    form.querySelectorAll('.form-input-modern').forEach(f => f.classList.remove('error'));
    form.querySelectorAll('.form-error').forEach(e => { e.textContent = ''; e.classList.remove('show'); });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── Inline validation helpers (light-theme forms) ─────────────
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
function _validateField(input) {
    const val = input.value.trim();
    if (input.hasAttribute('required') && !val) { _showErr(input, 'This field is required'); return false; }
    if (input.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { _showErr(input, 'Enter a valid email address'); return false; }
    if ((input.id === 'official_contact_number' || input.id === 'contact_number') && val && val.length < 10) { _showErr(input, 'Contact number must be at least 10 digits'); return false; }
    if (input.tagName === 'SELECT' && input.hasAttribute('required') && (!val || val === '')) { _showErr(input, 'Please select an option'); return false; }
    _clearErr(input);
    if (val) input.classList.add('is-valid');
    return true;
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

window.openAddSkOfficialsModal = function () { toggleModal('addSkOfficialsModal', true); };

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
    toggleModal('addSkOfficialsModal', false);
};

window.showSkOfficialsSuccessModal = function () { showAccountToast('SK Officials account successfully created!', 'success'); };
window.closeSkOfficialsSuccessModal = function () { };

window.switchAddOfficialTab = function (tab) {
    const manual = document.getElementById('addOfficialManualPane');
    const batch = document.getElementById('addOfficialBatchPane');
    const tM = document.getElementById('tabManual');
    const tB = document.getElementById('tabBatch');
    if (tab === 'manual') {
        if (manual) manual.style.display = '';
        if (batch) batch.style.display = 'none';
        if (tM) tM.classList.add('active');
        if (tB) tB.classList.remove('active');
    } else {
        if (manual) manual.style.display = 'none';
        if (batch) batch.style.display = '';
        if (tM) tM.classList.remove('active');
        if (tB) tB.classList.add('active');
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
    const ids = _getModalIds(getCurrentAccountType());
    toggleModal(ids.addModalId, true);
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

// ── DOMContentLoaded: wire up everything ──────────────────────
document.addEventListener('DOMContentLoaded', function () {
    cleanupAccountUiState();

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
        btn.addEventListener('click', () => { currentPage = n; updatePagination(); });
        paginationNums.appendChild(btn);
    }

    function addEllipsis() {
        const s = document.createElement('span');
        s.className = 'pagination-ellipsis';
        s.textContent = '...';
        s.style.cssText = 'padding:0 0.5rem;color:var(--gray-400);font-weight:500;';
        paginationNums.appendChild(s);
    }

    if (prevBtn) prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; updatePagination(); } });
    if (nextBtn) nextBtn.addEventListener('click', () => { const t = Math.ceil(filteredAccounts.length / recordsPerPage); if (currentPage < t) { currentPage++; updatePagination(); } });
    if (tableBody) initPagination();

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
        officialsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let valid = true;
            officialsForm.querySelectorAll('[required]').forEach(el => { if (!_validateField(el)) valid = false; });
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
                    showAccountToast(data.message || 'SK Officials account successfully created!', 'success');
                    window.setTimeout(() => window.location.reload(), 900);
                })
                .catch(() => {
                    cleanupAccountUiState();
                    alert('An unexpected error occurred. Please try again.');
                });
        });

        // Text-only name fields
        ['official_first_name', 'official_last_name', 'official_middle_name'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => { el.value = el.value.replace(/[^a-zA-Z\s\-']/g, ''); if (el.classList.contains('is-invalid')) _validateField(el); });
        });
        // Numbers-only contact
        const cEl = document.getElementById('official_contact_number');
        if (cEl) cEl.addEventListener('input', () => { cEl.value = cEl.value.replace(/\D/g, ''); if (cEl.classList.contains('is-invalid')) _validateField(cEl); });
        // Blur validation
        officialsForm.querySelectorAll('[required]').forEach(el => el.addEventListener('blur', () => _validateField(el)));
    }

    // ── Add SK Federation — backend submit ────────────────────
    const fedForm = document.getElementById('addSkFedForm');
    if (fedForm) {
        fedForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate all required fields first
            let valid = true;
            fedForm.querySelectorAll('[required]').forEach(el => {
                if (!_validateField(el)) valid = false;
            });

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
                    showAccountToast(data.message || 'Account successfully created!', 'success');
                    window.setTimeout(() => window.location.reload(), 900);
                })
                .catch(() => {
                    cleanupAccountUiState();
                    alert('An unexpected error occurred. Please try again.');
                });
        });

        // Text-only / numbers-only for fed add form
        ['first_name', 'last_name', 'middle_name'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^a-zA-Z\s\-']/g, '');
                if (el.classList.contains('is-invalid')) _validateField(el);
            });
        });
        const cFed = document.getElementById('contact_number');
        if (cFed) cFed.addEventListener('input', () => {
            cFed.value = cFed.value.replace(/\D/g, '');
            if (cFed.classList.contains('is-invalid')) _validateField(cFed);
        });

        // Blur validation for all required fields
        fedForm.querySelectorAll('[required]').forEach(el => el.addEventListener('blur', () => _validateField(el)));
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
        ['first_name', 'last_name', 'middle_name', 'suffix', 'date_of_birth', 'age', 'contact_number', 'email', 'position', 'barangay_id', 'status', 'term_start', 'term_end', 'term_status'].forEach(n => setFormFieldValue(form, n, data[_camel(n)] ?? data[n] ?? ''));
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

    // ── Delegated click handlers (covers server-rendered + JS-injected rows) ──
    if (tableBody) {
        tableBody.addEventListener('click', function (e) {
            const viewBtn = e.target.closest('.btn-view-account');
            const editBtn = e.target.closest('.btn-edit-account');
            const deleteBtn = e.target.closest('.btn-delete-account');
            if (viewBtn) openViewWithData(viewBtn);
            if (editBtn) openEditWithData(editBtn);
            if (deleteBtn) openDeleteModal(deleteBtn);
        });
    }

    // ── View button click → populate view modal ───────────────
    function openViewWithData(btn) {
        const d = btn.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';
        const fullName = [d.firstName, d.middleName, d.lastName, d.suffix].filter(v => v && v.trim()).join(' ');

        const leftSections = [];
        const rightSections = [];

        leftSections.push(viewSection('Personal Information', [
            ['Full Name', fullName],
            ['Email Address', d.email],
            ['Date of Birth', d.dateOfBirth ? formatDate(d.dateOfBirth) : ''],
            ['Age', d.age],
            ['Contact Number', d.contactNumber],
            ['Email Verification', d.emailVerifiedAt || 'Not Verified'],
        ]));

        leftSections.push(viewSection('Account Information', [
            ['Position', d.position],
            ['Barangay', d.barangayName],
            ['Municipality', d.municipality],
            ['Province', isOfficials ? '-' : (d.province || '-')],
            ['Region', isOfficials ? '-' : (d.region || '-')],
        ]));

        leftSections.push(viewSection('Contact Information', [
            ['Email Address', d.email],
            ['Contact Number', d.contactNumber],
            ['Email Verification', d.emailVerifiedAt || 'Not Verified'],
        ]));

        rightSections.push(viewSection('Additional Details', [
            ['Date of Birth', d.dateOfBirth ? formatDate(d.dateOfBirth) : ''],
            ['Age', d.age],
            ['Municipality', d.municipality],
        ]));

        rightSections.push(viewSection('Status Information', [
            ['Term Start', d.termStart ? formatDate(d.termStart) : ''],
            ['Term End', d.termEnd ? formatDate(d.termEnd) : ''],
            ['Account Status', d.status],
            ['Term Status', d.termStatus || 'ACTIVE'],
        ]));

        rightSections.push(viewSection('Registration Details', [
            ['Account ID', d.accountId || '-'],
            ['Email Verification', d.emailVerifiedAt || 'Not Verified'],
        ]));

        document.getElementById('viewAccountBody').innerHTML = `
            <div class="view-details-grid">
                <div class="detail-column">${leftSections.join('')}</div>
                <div class="detail-column">${rightSections.join('')}</div>
            </div>
        `;
        openViewModal();
    }

    // Helper function to create view sections (like Kabataan)
    function viewSection(title, fields) {
        const rows = fields.map(([label, value]) => `
            <div class="detail-item">
                <label>${label}</label>
                <span>${value || '-'}</span>
            </div>
        `).join('');

        return `
            <div class="detail-section">
                <h4 class="section-title">${title}</h4>
                <div class="detail-grid">
                    ${rows}
                </div>
            </div>
        `;
    }

    // static view bindings handled by delegated listener above

    // ── Delete modal ─────────────────────────────────────────
    let _deleteTargetRow = null;
    let _deleteTargetId = null;

    function openDeleteModal(btn) {
        _deleteTargetRow = btn.closest('tr');
        _deleteTargetId = btn.dataset.accountId || null;
        const name = btn.dataset.displayName || 'this account';
        const nameEl = document.getElementById('deleteAccountName');
        if (nameEl) nameEl.textContent = name;
        toggleModal('deleteAccountModal', true);
    }

    window.closeDeleteModal = function () {
        toggleModal('deleteAccountModal', false);
        _deleteTargetRow = null;
        _deleteTargetId = null;
    };

    window.confirmDeleteAccount = function () {
        if (!_deleteTargetId) {
            alert('Unable to delete account. Please refresh and try again.');
            closeDeleteModal();
            return;
        }

        showLoadingOverlay();
        fetch(`/accounts/${_deleteTargetId}/deactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(async r => {
                const ct = r.headers.get('content-type') || '';
                const hasJson = ct.includes('application/json');
                const data = hasJson ? await r.json() : {};
                return { ok: r.ok, data, hasJson };
            })
            .then(({ ok, data, hasJson }) => {
                hideLoadingOverlay();
                if (!ok || !hasJson || data.success === false) {
                    const msg = data.message || 'Failed to delete account. Please try again.';
                    alert(msg);
                    return;
                }

                if (_deleteTargetRow) {
                    _deleteTargetRow.remove();
                    // Rebuild pagination arrays
                    allAccounts = Array.from(tableBody.querySelectorAll('tr'))
                        .filter(r => !r.querySelector('td[colspan]'))
                        .map((el, i) => ({ element: el, index: i }));
                    filteredAccounts = [...allAccounts];
                    if (currentPage > Math.ceil(filteredAccounts.length / recordsPerPage)) {
                        currentPage = Math.max(1, Math.ceil(filteredAccounts.length / recordsPerPage));
                    }
                    updatePagination();
                }
                closeDeleteModal();
                showAccountToast('Account deleted successfully!', 'delete');
            })
            .catch(() => {
                hideLoadingOverlay();
                alert('An unexpected error occurred. Please try again.');
            });
    };


    // -- Batch upload (SK Officials) ---------------------------

    const BATCH_HEADER_ALIASES = {
        'first name': 'first_name',
        first_name: 'first_name',
        'middle name': 'middle_name',
        middle_name: 'middle_name',
        'last name': 'last_name',
        last_name: 'last_name',
        suffix: 'suffix',
        sex: 'sex',
        gender: 'sex',
        birthdate: 'date_of_birth',
        'birth date': 'date_of_birth',
        'date of birth': 'date_of_birth',
        date_of_birth: 'date_of_birth',
        dob: 'date_of_birth',
        age: 'age',
        'contact number': 'contact_number',
        contact_number: 'contact_number',
        contact: 'contact_number',
        position: 'position',
        status: 'status',
        region: 'region',
        province: 'province',
        municipality: 'municipality',
        barangay: 'barangay',
        'barangay name': 'barangay',
        barangay_name: 'barangay',
        'term start date': 'term_start',
        'term start': 'term_start',
        term_start: 'term_start',
        'start date': 'term_start',
        'term end date': 'term_end',
        'term end': 'term_end',
        term_end: 'term_end',
        'end date': 'term_end',
        committee: 'committee',
        'email address': 'email',
        email: 'email',
    };

    const BATCH_REQUIRED_HEADER_GROUPS = [
        { label: 'First Name', keys: ['first name', 'first_name'] },
        { label: 'Last Name', keys: ['last name', 'last_name'] },
        { label: 'Email Address', keys: ['email address', 'email'] },
        { label: 'Sex', keys: ['sex', 'gender'] },
        { label: 'Birthdate', keys: ['birthdate', 'birth date', 'date of birth', 'date_of_birth', 'dob'] },
        { label: 'Contact Number', keys: ['contact number', 'contact_number', 'contact'] },
        { label: 'Position', keys: ['position'] },
        { label: 'Barangay', keys: ['barangay', 'barangay name', 'barangay_name'] },
        { label: 'Term Start Date', keys: ['term start date', 'term start', 'term_start', 'start date'] },
        { label: 'Term End Date', keys: ['term end date', 'term end', 'term_end', 'end date'] },
    ];

    const fileInput = document.getElementById('officialBatchFile');
    const dropzone = document.getElementById('officialDropzone');
    const fileLabel = document.getElementById('officialFileName');
    const preview = document.getElementById('officialBatchPreview');
    const confirmBtn = document.getElementById('officialBatchConfirmBtn');

    let _batchParsedRows = [];
    let _batchParsedHeaders = [];
    let _batchMappedAccounts = [];

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatBatchCell(value) {
        if (value === null || value === undefined || value === '') {
            return '&mdash;';
        }

        if (value instanceof Date) {
            return escapeHtml(value.toLocaleDateString('en-CA'));
        }

        return escapeHtml(String(value).trim());
    }

    function normalizeHeaderKey(header) {
        return String(header || '').trim().toLowerCase();
    }

    function resolveBatchFieldKey(header) {
        const normalized = normalizeHeaderKey(header);
        if (BATCH_HEADER_ALIASES[normalized]) {
            return BATCH_HEADER_ALIASES[normalized];
        }

        return normalized.replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    }

    function excelSerialToDateString(serial) {
        const utcDays = Math.floor(Number(serial) - 25569);
        const date = new Date(utcDays * 86400 * 1000);
        return date.toISOString().slice(0, 10);
    }

    function coerceBatchCellValue(fieldKey, value) {
        if (value === null || value === undefined || value === '') {
            return '';
        }

        if (value instanceof Date) {
            return value.toISOString().slice(0, 10);
        }

        if (typeof value === 'number' && ['term_start', 'term_end', 'date_of_birth'].includes(fieldKey)) {
            return excelSerialToDateString(value);
        }

        return String(value).trim();
    }

    function getMissingBatchHeaders(headers) {
        const normalizedHeaders = headers.map(normalizeHeaderKey);

        return BATCH_REQUIRED_HEADER_GROUPS
            .filter((group) => !group.keys.some((key) => normalizedHeaders.includes(key)))
            .map((group) => group.label);
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

    function handleBatchFile(file) {
        if (!file) return;
        if (fileLabel) fileLabel.textContent = file.name;

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array', raw: false, cellDates: true });
                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                const allRows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

                const nonEmptyRows = allRows.filter((row) =>
                    row.some((cell) => String(cell).trim() !== '')
                );

                if (nonEmptyRows.length < 2) {
                    _batchParsedHeaders = [];
                    _batchParsedRows = [];
                    _batchMappedAccounts = [];
                    renderBatchPreview([], [], 'No data rows found in the uploaded file.');
                    return;
                }

                _batchParsedHeaders = nonEmptyRows[0].map((header) => String(header).trim());
                _batchParsedRows = nonEmptyRows.slice(1).filter((row) =>
                    row.some((cell) => String(cell).trim() !== '')
                );
                _batchMappedAccounts = _batchParsedRows.map((row) =>
                    mapRowToAccount(_batchParsedHeaders, row)
                );

                const missingHeaders = getMissingBatchHeaders(_batchParsedHeaders);
                let previewMessage = _batchParsedRows.length + ' row' + (_batchParsedRows.length !== 1 ? 's' : '') + ' ready for review';
                if (missingHeaders.length > 0) {
                    previewMessage = 'Missing required columns: ' + missingHeaders.join(', ') + '.';
                }

                renderBatchPreview(_batchParsedHeaders, _batchParsedRows, previewMessage, missingHeaders.length > 0);
            } catch (err) {
                console.error('Batch upload read error:', err);
                _batchParsedHeaders = [];
                _batchParsedRows = [];
                _batchMappedAccounts = [];
                renderBatchPreview([], [], 'Unable to read the Excel file. Please upload a valid .xlsx or .xls file.');
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function renderBatchPreview(headers, rows, message, hasHeaderErrors = false) {
        if (!preview) return;

        if (rows.length === 0) {
            preview.innerHTML = '<p class="batch-row-count" style="color:#94a3b8;">' + escapeHtml(message || 'Upload an Excel file to preview rows.') + '</p>';
            preview.style.display = '';
            if (confirmBtn) confirmBtn.disabled = true;
            return;
        }

        const theadCells = headers.map((header) => '<th>' + escapeHtml(header) + '</th>').join('');
        const tbodyRows = rows.map((row) => {
            const cells = headers.map((_, index) => {
                const value = row[index];
                return '<td>' + formatBatchCell(value) + '</td>';
            }).join('');
            return '<tr>' + cells + '</tr>';
        }).join('');

        const messageClass = hasHeaderErrors ? 'batch-row-count batch-row-count-error' : 'batch-row-count';

        preview.innerHTML =
            '<p class="' + messageClass + '">' + escapeHtml(message || '') + '</p>' +
            '<div class="batch-preview-wrap">' +
            '<table class="batch-preview-table">' +
            '<thead><tr>' + theadCells + '</tr></thead>' +
            '<tbody>' + tbodyRows + '</tbody>' +
            '</table></div>';

        preview.style.display = '';
        if (confirmBtn) confirmBtn.disabled = hasHeaderErrors;
    }

    window.resetBatchUpload = function () {
        if (fileInput) fileInput.value = '';
        if (fileLabel) fileLabel.textContent = 'Supported: .xlsx, .xls';
        _batchParsedHeaders = [];
        _batchParsedRows = [];
        _batchMappedAccounts = [];
        if (preview) {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }
        if (confirmBtn) confirmBtn.disabled = true;
        switchAddOfficialTab('manual');
    };

    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            if (_batchMappedAccounts.length === 0) return;

            const missingHeaders = getMissingBatchHeaders(_batchParsedHeaders);
            if (missingHeaders.length > 0) {
                alert(
                    'Your Excel file is missing required columns:\n\n' +
                    missingHeaders.join('\n') +
                    '\n\nPlease use the full template with Barangay, Term Start Date, Term End Date, Email Address, and other required fields.'
                );
                return;
            }

            showLoadingOverlay('Creating accounts...');
            confirmBtn.disabled = true;

            try {
                const response = await fetch('/accounts/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        role: 'sk_official',
                        accounts: _batchMappedAccounts,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    cleanupAccountUiState();
                    let errorMessage = data.message || 'Batch account creation failed.';
                    if (Array.isArray(data.failed) && data.failed.length > 0) {
                        const details = data.failed
                            .slice(0, 5)
                            .map((item) => 'Row ' + item.row + ': ' + item.message)
                            .join('\n');
                        errorMessage += '\n\n' + details;
                    }
                    alert(errorMessage);
                    confirmBtn.disabled = false;
                    return;
                }

                cleanupAccountUiState();
                resetBatchUpload();

                const toastType = Array.isArray(data.failed) && data.failed.length > 0 ? 'edit' : 'success';
                showAccountToast(data.message || 'Accounts created successfully.', toastType);
                window.setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                cleanupAccountUiState();
                confirmBtn.disabled = false;
                alert('Unable to create accounts from the uploaded file. Please try again.');
            }
        });
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;
            handleBatchFile(file);
        });
    }

    // Dropzone interactions
    if (dropzone) {
        dropzone.addEventListener('click', function (e) {
            if (!e.target.classList.contains('dropzone-browse') && fileInput) fileInput.click();
        });
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('drag-over');
        });
        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('drag-over');
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            var file = e.dataTransfer.files[0];
            if (!file) return;
            var dt = new DataTransfer();
            dt.items.add(file);
            if (fileInput) fileInput.files = dt.files;
            handleBatchFile(file);
        });
    }
});