// =============================================================
// account.js — Single JS file for the Accounts module
// Covers: manage table, add/edit SK Fed, add/edit SK Officials,
//         view modal, pagination, batch upload
// =============================================================

// ── Shared helpers ────────────────────────────────────────────
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : '';
    document.documentElement.style.overflow = show ? 'hidden' : '';
}

// ── Top toast notification ─────────────────────────────────────
let _toastTimer = null;
function showAccountToast(msg, type) {
    // type: 'success' | 'edit' | 'delete'
    const idMap = { success: 'accountToast', edit: 'accountToastEdit', delete: 'accountToastDelete' };
    const msgMap = { success: 'accountToastMsg', edit: 'accountToastEditMsg', delete: 'accountToastDeleteMsg' };
    const toastId = idMap[type] || 'accountToast';
    const msgId   = msgMap[type] || 'accountToastMsg';

    // Hide all toasts first
    ['accountToast','accountToastEdit','accountToastDelete'].forEach(id => {
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

function showLoadingOverlay() {
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `<div class="loading-spinner"><div class="spinner"></div><p>Processing...</p></div>`;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) { overlay.style.display = 'none'; document.body.style.overflow = ''; }
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
const ICON_MAXIMIZE = `<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>`;
const ICON_RESTORE  = `<rect x="3" y="7" width="11" height="11" rx="1.5"/><path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"/>`;

window.toggleAddOfficialsSize = function () {
    const overlay = document.getElementById('addSkOfficialsModal');
    const content = document.getElementById('addSkOfficialsModalContent');
    const icon    = document.getElementById('addOfficialsResizeIcon');
    const btn     = document.getElementById('addOfficialsResizeBtn');
    if (!overlay || !content || !icon) return;
    addOfficialsIsMaximized = !addOfficialsIsMaximized;
    if (addOfficialsIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down'; icon.innerHTML = ICON_RESTORE;
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize'; icon.innerHTML = ICON_MAXIMIZE;
    }
};

window.openAddSkOfficialsModal = function () { toggleModal('addSkOfficialsModal', true); };

window.closeAddSkOfficialsModal = function () {
    addOfficialsIsMaximized = false;
    const content = document.getElementById('addSkOfficialsModalContent');
    if (content) content.style.cssText = '';
    const form = document.getElementById('addSkOfficialsForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(el => el.classList.remove('is-invalid','is-valid'));
        form.querySelectorAll('.validation-error').forEach(el => el.remove());
    }
    switchAddOfficialTab('manual');
    toggleModal('addSkOfficialsModal', false);
};

window.showSkOfficialsSuccessModal  = function () { showAccountToast('SK Officials account successfully created!', 'success'); };
window.closeSkOfficialsSuccessModal = function () {};

window.switchAddOfficialTab = function (tab) {
    const manual = document.getElementById('addOfficialManualPane');
    const batch  = document.getElementById('addOfficialBatchPane');
    const tM = document.getElementById('tabManual');
    const tB = document.getElementById('tabBatch');
    if (tab === 'manual') {
        if (manual) manual.style.display = '';
        if (batch)  batch.style.display  = 'none';
        if (tM) tM.classList.add('active');
        if (tB) tB.classList.remove('active');
    } else {
        if (manual) manual.style.display = 'none';
        if (batch)  batch.style.display  = '';
        if (tM) tM.classList.remove('active');
        if (tB) tB.classList.add('active');
    }
};

// ── Edit SK Officials modal ───────────────────────────────────
window.openEditSkOfficialsModal = function () { toggleModal('editSkOfficialsModal', true); };

window.closeEditSkOfficialsModal = function () {
    const modal = document.getElementById('editSkOfficialsModal');
    if (modal) {
        modal.classList.remove('modal-fullscreen', 'modal-minimized');
        _setModalBtns(modal, 'normal');
    }
    const form = document.getElementById('editSkOfficialsForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid','is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    toggleModal('editSkOfficialsModal', false);
};

window.showEditSkOfficialsSuccessModal  = function () {
    toggleModal('editSkOfficialsModal', false);
    showAccountToast('SK Officials account updated successfully!', 'edit');
};
window.closeEditSkOfficialsSuccessModal = function () {};

window.toggleFullscreenEditSkOfficialsModal = function () {
    const modal = document.getElementById('editSkOfficialsModal');
    if (!modal) return;
    if (modal.classList.contains('modal-fullscreen')) {
        modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
    } else {
        modal.classList.remove('modal-minimized'); modal.classList.add('modal-fullscreen'); _setModalBtns(modal, 'fullscreen');
    }
};
window.toggleRestoreEditSkOfficialsModal = function () {
    const modal = document.getElementById('editSkOfficialsModal');
    if (!modal) return;
    modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
};
window.restoreEditSkOfficialsModal = window.toggleRestoreEditSkOfficialsModal;

// ── Add SK Federation modal ───────────────────────────────────
let addFedIsMaximized = false;

window.toggleAddFedSize = function () {
    const overlay = document.getElementById('addAccountModal');
    const content = document.getElementById('addSkFedModalContent');
    const icon    = document.getElementById('addFedResizeIcon');
    const btn     = document.getElementById('addFedResizeBtn');
    if (!overlay || !content || !icon) return;
    addFedIsMaximized = !addFedIsMaximized;
    if (addFedIsMaximized) {
        content.style.cssText = 'width:100vw;max-width:100vw;height:100vh;max-height:100vh;border-radius:0';
        overlay.style.padding = '0';
        btn.title = 'Restore Down';
        icon.innerHTML = `<rect x="3" y="7" width="11" height="11" rx="1.5"/><path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-2"/>`;
    } else {
        content.style.cssText = ''; overlay.style.padding = '';
        btn.title = 'Maximize';
        icon.innerHTML = `<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>`;
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
    const form = document.getElementById('addSkFedForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid','is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    const ids = _getModalIds(getCurrentAccountType());
    toggleModal(ids.addModalId, false);
};

window.showAddSuccessModal  = function () { showAccountToast('Account successfully created!', 'success'); };
window.closeAddSuccessModal = function () {};

// ── Edit SK Federation modal ──────────────────────────────────
window.openEditModal = function () { toggleModal('editAccountModal', true); };

window.closeEditModal = function () {
    const modal = document.getElementById('editAccountModal');
    if (modal) { modal.classList.remove('modal-fullscreen','modal-minimized'); _setModalBtns(modal, 'normal'); }
    const form = document.getElementById('editAccountForm');
    if (form) {
        form.reset();
        form.querySelectorAll('.is-invalid,.is-valid').forEach(f => f.classList.remove('is-invalid','is-valid'));
        form.querySelectorAll('.validation-error').forEach(e => e.remove());
    }
    toggleModal('editAccountModal', false);
};

window.showEditSuccessModal  = function () {
    toggleModal('editAccountModal', false);
    showAccountToast('Account updated successfully!', 'edit');
};
window.closeEditSuccessModal = function () {};

window.toggleFullscreenEditAccountModal = function () {
    const modal = document.getElementById('editAccountModal');
    if (!modal) return;
    if (modal.classList.contains('modal-fullscreen')) {
        modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
    } else {
        modal.classList.remove('modal-minimized'); modal.classList.add('modal-fullscreen'); _setModalBtns(modal, 'fullscreen');
    }
};
window.toggleRestoreEditAccountModal = function () {
    const modal = document.getElementById('editAccountModal');
    if (!modal) return;
    modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
};
window.restoreEditAccountModal = window.toggleRestoreEditAccountModal;

// ── View modal ────────────────────────────────────────────────
window.openViewModal  = function () { toggleModal('viewAccountModal', true); };
window.closeViewModal = function () {
    const modal = document.getElementById('viewAccountModal');
    if (modal) { modal.classList.remove('modal-fullscreen','modal-minimized'); _setModalBtns(modal, 'normal'); }
    toggleModal('viewAccountModal', false);
};
window.toggleFullscreenViewModal = function () {
    const modal = document.getElementById('viewAccountModal');
    if (!modal) return;
    if (modal.classList.contains('modal-fullscreen')) {
        modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
    } else {
        modal.classList.remove('modal-minimized'); modal.classList.add('modal-fullscreen'); _setModalBtns(modal, 'fullscreen');
    }
};
window.toggleRestoreViewModal = function () {
    const modal = document.getElementById('viewAccountModal');
    if (!modal) return;
    modal.classList.remove('modal-fullscreen'); _setModalBtns(modal, 'normal');
};
window.restoreViewModal = window.toggleRestoreViewModal;

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

    // ── Pagination ────────────────────────────────────────────
    const recordsPerPage = 10;
    let currentPage = 1;
    let allAccounts = [];
    let filteredAccounts = [];

    const prevBtn          = document.getElementById('prevBtn');
    const nextBtn          = document.getElementById('nextBtn');
    const paginationNums   = document.getElementById('paginationNumbers');
    const paginationInfo   = document.getElementById('paginationInfo');
    const tableBody        = document.querySelector('.accounts-table tbody');

    function initPagination() {
        const rows = Array.from(tableBody.querySelectorAll('tr')).filter(r => !r.querySelector('td[colspan]'));
        allAccounts = rows.map((el, i) => ({ element: el, index: i }));
        filteredAccounts = [...allAccounts];
        updatePagination();
    }

    function updatePagination() {
        const total = Math.ceil(filteredAccounts.length / recordsPerPage);
        const start = (currentPage - 1) * recordsPerPage;
        const end   = Math.min(start + recordsPerPage, filteredAccounts.length);
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

            showLoadingOverlay();
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
                hideLoadingOverlay();
                if (!ok || !data.success) {
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
                        alert('Failed to create account. Please try again.');
                    }
                    return;
                }
                closeAddSkOfficialsModal();
                showSkOfficialsSuccessModal();
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(() => { hideLoadingOverlay(); alert('An unexpected error occurred. Please try again.'); });
        });

        // Text-only name fields
        ['official_first_name','official_last_name','official_middle_name'].forEach(id => {
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
            
            showLoadingOverlay();
            fetch('/accounts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async r => { const ct = r.headers.get('content-type') || ''; const data = ct.includes('application/json') ? await r.json() : {}; return { ok: r.ok, data }; })
            .then(({ ok, data }) => {
                hideLoadingOverlay();
                if (!ok || !data.success) {
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
                closeAddAccountModal();
                showAddSuccessModal();
                // Reload page to show new account
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(() => { hideLoadingOverlay(); alert('An unexpected error occurred. Please try again.'); });
        });

        // Text-only / numbers-only for fed add form
        ['first_name','last_name','middle_name'].forEach(id => {
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

    const fedEditForm      = document.getElementById('editAccountForm');
    const officialsEditForm = document.getElementById('editSkOfficialsForm');
    attachEditSubmit(fedEditForm);
    attachEditSubmit(officialsEditForm);

    // DOB → age auto-fill
    attachDobAgeAutoFill(fedForm,          'date_of_birth', 'age');
    attachDobAgeAutoFill(fedEditForm,      'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsForm,    'date_of_birth', 'age');
    attachDobAgeAutoFill(officialsEditForm,'date_of_birth', 'age');

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
        ['first_name','last_name','middle_name','suffix','date_of_birth','age','contact_number','email','position','barangay_id','status','term_start','term_end','term_status'].forEach(n => setFormFieldValue(form, n, data[_camel(n)] ?? data[n] ?? ''));
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
            const viewBtn   = e.target.closest('.btn-view-account');
            const editBtn   = e.target.closest('.btn-edit-account');
            const deleteBtn = e.target.closest('.btn-delete-account');
            if (viewBtn)   openViewWithData(viewBtn);
            if (editBtn)   openEditWithData(editBtn);
            if (deleteBtn) openDeleteModal(deleteBtn);
        });
    }

    // ── View button click → populate view modal ───────────────
    function openViewWithData(btn) {
        const d = btn.dataset;
        const isOfficials = getCurrentAccountType() === 'sk_officials';
        const fullName = [d.firstName, d.middleName, d.lastName, d.suffix].filter(v => v && v.trim()).join(' ');
        document.getElementById('viewFullName').textContent         = fullName || '-';
        document.getElementById('viewEmail').textContent            = d.email || '-';
        document.getElementById('viewDateOfBirth').textContent      = d.dateOfBirth ? formatDate(d.dateOfBirth) : '-';
        document.getElementById('viewAge').textContent              = d.age || '-';
        document.getElementById('viewContactNumber').textContent    = d.contactNumber || '-';
        document.getElementById('viewEmailVerification').textContent = d.emailVerifiedAt || 'Not Verified';
        document.getElementById('viewBarangay').textContent         = d.barangayName || '-';
        document.getElementById('viewMunicipality').textContent     = d.municipality || '-';
        const provC = document.getElementById('viewProvinceContainer');
        const regC  = document.getElementById('viewRegionContainer');
        if (!isOfficials) {
            if (provC) provC.style.display = 'flex';
            if (regC)  regC.style.display  = 'flex';
            document.getElementById('viewProvince').textContent = d.province || '-';
            document.getElementById('viewRegion').textContent   = d.region   || '-';
        } else {
            if (provC) provC.style.display = 'none';
            if (regC)  regC.style.display  = 'none';
        }
        document.getElementById('viewPosition').textContent  = d.position  || '-';
        document.getElementById('viewTermStart').textContent = d.termStart ? formatDate(d.termStart) : '-';
        document.getElementById('viewTermEnd').textContent   = d.termEnd   ? formatDate(d.termEnd)   : '-';
        const accStatus  = document.getElementById('viewAccountStatus');
        const termStatus = document.getElementById('viewTermStatus');
        accStatus.textContent  = d.status     || '-';
        accStatus.className    = `status-badge ${d.status ? d.status.toLowerCase() : ''}`;
        termStatus.textContent = d.termStatus || 'ACTIVE';
        termStatus.className   = `status-badge ${d.termStatus ? d.termStatus.toLowerCase() : 'active'}`;
        openViewModal();
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

    // Fixed column order — matches the Excel template exactly
    const BATCH_LABELS = [
        'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Sex',
        'Birthdate', 'Age', 'Contact Number', 'Position', 'Status',
        'Region', 'Province', 'Municipality', 'Barangay',
        'Term Start Date', 'Term End Date', 'Committee', 'Email Address'
    ];

    const fileInput  = document.getElementById('officialBatchFile');
    const dropzone   = document.getElementById('officialDropzone');
    const fileLabel  = document.getElementById('officialFileName');
    const preview    = document.getElementById('officialBatchPreview');
    const confirmBtn = document.getElementById('officialBatchConfirmBtn');

    // Stores the parsed batch rows so Confirm Import can use them
    var _batchParsedRows = [];
    var _batchParsedHeaders = [];

    // Read the Excel file and render preview immediately
    function handleBatchFile(file) {
        if (!file) return;
        if (fileLabel) fileLabel.textContent = file.name;

        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var data = new Uint8Array(e.target.result);
                var wb   = XLSX.read(data, { type: 'array', raw: false });
                var ws   = wb.Sheets[wb.SheetNames[0]];

                var allRows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

                var dataRows = allRows.filter(function (row) {
                    return row.some(function (cell) {
                        return String(cell).trim() !== '';
                    });
                });

                if (dataRows.length === 0) {
                    _batchParsedHeaders = [];
                    _batchParsedRows    = [];
                    renderBatchPreview([], []);
                    return;
                }

                _batchParsedHeaders = dataRows[0].map(function (h) { return String(h).trim(); });
                _batchParsedRows    = dataRows.slice(1);

                renderBatchPreview(_batchParsedHeaders, _batchParsedRows);
            } catch (err) {
                console.error('Batch upload read error:', err);
                _batchParsedHeaders = [];
                _batchParsedRows    = [];
                renderBatchPreview([], []);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    // Sample row shown when the file has no data rows
    var SAMPLE_ROW = [
        'Juan', 'Dela', 'Cruz', 'Jr.', 'Male',
        '01/01/2000', '24', '09171234567', 'SK Chairman', 'Active',
        'IV-A CALABARZON', 'Laguna', 'Santa Cruz', 'Alipit',
        '01/01/2023', '12/31/2025', 'Youth Dev', 'juan@email.com'
    ];

    // Render the preview table using whatever headers + rows came from the file
    function renderBatchPreview(headers, rows) {
        if (!preview) return;

        // If no headers at all, use the fixed template columns
        var displayHeaders = headers.length > 0 ? headers : BATCH_LABELS;

        var theadCells = displayHeaders.map(function (h) {
            return '<th>' + h + '</th>';
        }).join('');

        var displayRows  = rows;
        var isSample     = false;

        // If no data rows, inject one sample row so the table is never empty
        if (rows.length === 0) {
            displayRows = [SAMPLE_ROW];
            isSample    = true;
        }

        var tbodyRows = displayRows.map(function (row) {
            var cells = displayHeaders.map(function (_, i) {
                var val = row[i] !== undefined ? String(row[i]).trim() : '';
                return '<td>' + (val || '&mdash;') + '</td>';
            }).join('');
            return '<tr>' + cells + '</tr>';
        }).join('');

        var rowCount = isSample
            ? '<p class="batch-row-count" style="color:#94a3b8;">Showing sample row — no data found in file</p>'
            : '<p class="batch-row-count">' + rows.length + ' row' + (rows.length !== 1 ? 's' : '') + ' found</p>';

        preview.innerHTML = rowCount +
            '<div class="batch-preview-wrap">' +
            '<table class="batch-preview-table">' +
            '<thead><tr>' + theadCells + '</tr></thead>' +
            '<tbody>' + tbodyRows + '</tbody>' +
            '</table></div>';

        preview.style.display = '';
        if (confirmBtn) confirmBtn.disabled = false;
    }

    // Reset batch pane to initial state
    window.resetBatchUpload = function () {
        if (fileInput)  { fileInput.value = ''; }
        if (fileLabel)  { fileLabel.textContent = 'Supported: .xlsx, .xls'; }
        if (preview)    { preview.innerHTML = ''; preview.style.display = 'none'; }
        switchAddOfficialTab('manual');
    };

    // Confirm Import — injects actual uploaded rows into the main table
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            var tbody = document.querySelector('.accounts-table tbody');
            if (!tbody) { closeAddSkOfficialsModal(); showAccountToast('SK Officials imported successfully!', 'success'); return; }

            // Use parsed rows from the uploaded file; fall back to SAMPLE_ROW if none
            var rowsToInsert = _batchParsedRows.length > 0 ? _batchParsedRows : [SAMPLE_ROW];
            var hdrs         = _batchParsedHeaders.length > 0 ? _batchParsedHeaders : BATCH_LABELS;

            // Build a header→index map (case-insensitive)
            var hdrMap = {};
            hdrs.forEach(function (h, i) { hdrMap[h.toLowerCase().trim()] = i; });

            function col(row, name) {
                var idx = hdrMap[name.toLowerCase()];
                return idx !== undefined && row[idx] !== undefined ? String(row[idx]).trim() : '';
            }

            // Remove "No accounts found" empty row if present
            var emptyRow = tbody.querySelector('td[colspan]');
            if (emptyRow) emptyRow.closest('tr').remove();

            rowsToInsert.forEach(function (row) {
                var firstName  = col(row, 'first name')   || col(row, 'first_name')  || (row[0]  ? String(row[0]).trim()  : '');
                var middleName = col(row, 'middle name')  || col(row, 'middle_name') || (row[1]  ? String(row[1]).trim()  : '');
                var lastName   = col(row, 'last name')    || col(row, 'last_name')   || (row[2]  ? String(row[2]).trim()  : '');
                var suffix     = col(row, 'suffix')                                  || (row[3]  ? String(row[3]).trim()  : '');
                var sex        = col(row, 'sex')                                     || (row[4]  ? String(row[4]).trim()  : '');
                var birthdate  = col(row, 'birthdate')                               || (row[5]  ? String(row[5]).trim()  : '');
                var age        = col(row, 'age')                                     || (row[6]  ? String(row[6]).trim()  : '');
                var contact    = col(row, 'contact number') || col(row, 'contact_number') || (row[7] ? String(row[7]).trim() : '');
                var position   = col(row, 'position')                                || (row[8]  ? String(row[8]).trim()  : '');
                var status     = col(row, 'status')                                  || (row[9]  ? String(row[9]).trim()  : 'Active');
                var barangay   = col(row, 'barangay')                                || (row[13] ? String(row[13]).trim() : '');
                var termStart  = col(row, 'term start date') || col(row, 'term_start') || (row[14] ? String(row[14]).trim() : '');
                var termEnd    = col(row, 'term end date')   || col(row, 'term_end')   || (row[15] ? String(row[15]).trim() : '');
                var committee  = col(row, 'committee')                               || (row[16] ? String(row[16]).trim() : '');
                var email      = col(row, 'email address')  || col(row, 'email')      || (row[17] ? String(row[17]).trim() : '');

                // Build display name
                var nameParts = [firstName, middleName ? middleName.charAt(0) + '.' : '', lastName, suffix].filter(Boolean);
                var displayName = nameParts.join(' ');

                var statusLower = status.toLowerCase();
                var statusClass = statusLower === 'active' ? 'active' : 'inactive';

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + (displayName || '—') + '</td>' +
                    '<td>' + (email || '—') + '</td>' +
                    '<td>' + (barangay || '—') + '</td>' +
                    '<td>' + (position || '—') + '</td>' +
                    '<td>' + (termEnd || '—') + '</td>' +
                    '<td><span class="status-badge ' + statusClass + '">' + status + '</span></td>' +
                    '<td><div class="action-buttons-container">' +
                        '<button type="button" class="btn-view-modern btn-view-account"' +
                            ' data-first-name="' + firstName + '"' +
                            ' data-middle-name="' + middleName + '"' +
                            ' data-last-name="' + lastName + '"' +
                            ' data-suffix="' + suffix + '"' +
                            ' data-date-of-birth="' + birthdate + '"' +
                            ' data-age="' + age + '"' +
                            ' data-contact-number="' + contact + '"' +
                            ' data-email="' + email + '"' +
                            ' data-position="' + position + '"' +
                            ' data-barangay-name="' + barangay + '"' +
                            ' data-status="' + status + '"' +
                            ' data-term-status="ACTIVE"' +
                            ' data-term-start="' + termStart + '"' +
                            ' data-term-end="' + termEnd + '"' +
                            ' data-email-verified-at="">View</button>' +
                        '<button type="button" class="btn-edit-modern btn-edit-account"' +
                            ' data-account-id=""' +
                            ' data-first-name="' + firstName + '"' +
                            ' data-middle-name="' + middleName + '"' +
                            ' data-last-name="' + lastName + '"' +
                            ' data-suffix="' + suffix + '"' +
                            ' data-date-of-birth="' + birthdate + '"' +
                            ' data-age="' + age + '"' +
                            ' data-contact-number="' + contact + '"' +
                            ' data-email="' + email + '"' +
                            ' data-position="' + position + '"' +
                            ' data-status="' + status + '"' +
                            ' data-term-status="ACTIVE"' +
                            ' data-term-start="' + termStart + '"' +
                            ' data-term-end="' + termEnd + '">Edit</button>' +
                        '<button type="button" class="btn-delete-modern btn-delete-account"' +
                            ' data-account-id=""' +
                            ' data-display-name="' + displayName + '">Delete</button>' +
                    '</div></td>';

                tbody.appendChild(tr);
            });

            // Re-init pagination
            allAccounts = Array.from(tbody.querySelectorAll('tr'))
                .filter(function (r) { return !r.querySelector('td[colspan]'); })
                .map(function (el, i) { return { element: el, index: i }; });
            filteredAccounts = allAccounts.slice();
            currentPage = 1;
            updatePagination();

            closeAddSkOfficialsModal();
            showAccountToast('SK Officials imported successfully!', 'success');
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