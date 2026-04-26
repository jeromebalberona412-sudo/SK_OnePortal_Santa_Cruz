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
        if (allAccounts.length > 0) updatePagination();
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

    // ── Add SK Officials — UI-only submit (injects row) ───────
    const officialsForm = document.getElementById('addSkOfficialsForm');
    if (officialsForm) {
        officialsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let valid = true;
            officialsForm.querySelectorAll('[required]').forEach(el => { if (!_validateField(el)) valid = false; });
            if (!valid) { const first = officialsForm.querySelector('.is-invalid'); if (first) first.focus(); return; }

            const get = id => (document.getElementById(id)?.value ?? '').trim();
            const firstName  = get('official_first_name');
            const middleName = get('official_middle_name');
            const lastName   = get('official_last_name');
            const suffix     = get('official_suffix');
            const email      = get('official_email');
            const position   = get('official_position');
            const status     = get('official_status');
            const termEnd    = get('official_term_end');
            const bqSel      = document.getElementById('official_barangay_id');
            const bqName     = bqSel?.options[bqSel.selectedIndex]?.text ?? '-';
            const mi         = middleName ? middleName.charAt(0).toUpperCase() + '.' : '';
            const displayName = [firstName, mi, lastName, (suffix && suffix !== 'None') ? suffix : ''].filter(Boolean).join(' ');
            let termEndDisplay = '-';
            if (termEnd) { const d = new Date(termEnd); if (!isNaN(d)) termEndDisplay = d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }); }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${displayName}</td>
                <td>${email}</td>
                <td>${bqName}</td>
                <td>${position}</td>
                <td>${termEndDisplay}</td>
                <td><span class="status-badge ${status.toLowerCase()}">${status}</span></td>
                <td><div class="action-buttons-container">
                    <button type="button" class="btn-view-modern btn-view-account"
                        data-first-name="${get('official_first_name')}"
                        data-last-name="${get('official_last_name')}"
                        data-middle-name="${get('official_middle_name')}"
                        data-suffix="${get('official_suffix')}"
                        data-email="${email}"
                        data-contact-number="${get('official_contact_number')}"
                        data-date-of-birth="${get('official_date_of_birth')}"
                        data-age=""
                        data-position="${position}"
                        data-barangay-name="${bqName}"
                        data-municipality="Santa Cruz"
                        data-status="${status}"
                        data-term-status="ACTIVE"
                        data-term-start="${get('official_term_start')}"
                        data-term-end="${termEnd}"
                        data-email-verified-at="">View</button>
                    <button type="button" class="btn-edit-modern btn-edit-account"
                        data-first-name="${get('official_first_name')}"
                        data-last-name="${get('official_last_name')}"
                        data-middle-name="${get('official_middle_name')}"
                        data-suffix="${get('official_suffix')}"
                        data-email="${email}"
                        data-contact-number="${get('official_contact_number')}"
                        data-date-of-birth="${get('official_date_of_birth')}"
                        data-age=""
                        data-position="${position}"
                        data-barangay-id="${bqSel?.value ?? ''}"
                        data-status="${status}"
                        data-term-status="ACTIVE"
                        data-term-start="${get('official_term_start')}"
                        data-term-end="${termEnd}">Edit</button>
                    <button type="button" class="btn-delete-modern btn-delete-account"
                        data-display-name="${displayName}">Delete</button>
                </div></td>`;

            const emptyRow = tableBody?.querySelector('td[colspan]');
            if (emptyRow) emptyRow.closest('tr').remove();
            if (tableBody) {
                tableBody.appendChild(tr);
                allAccounts.push({ element: tr, index: allAccounts.length });
                filteredAccounts = [...allAccounts];
                currentPage = Math.ceil(filteredAccounts.length / recordsPerPage);
                updatePagination();
            }
            closeAddSkOfficialsModal();
            showSkOfficialsSuccessModal();
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
            clearAllErrors(fedForm);
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
                    if (data.errors) Object.keys(data.errors).forEach(f => showFieldError(fedForm, f, data.errors[f][0]));
                    else alert('Failed to create account. Please try again.');
                    return;
                }
                closeAddAccountModal();
                showAddSuccessModal();
            })
            .catch(() => { hideLoadingOverlay(); alert('An unexpected error occurred. Please try again.'); });
        });

        // Text-only / numbers-only for fed add form
        ['first_name','last_name','middle_name'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => { el.value = el.value.replace(/[^a-zA-Z\s\-']/g, ''); });
        });
        const cFed = document.getElementById('contact_number');
        if (cFed) cFed.addEventListener('input', () => { cFed.value = cFed.value.replace(/\D/g, ''); });
    }

    // ── Edit forms — UI-only submit (no backend) ─────────────
    function attachEditSubmit(form) {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            _closeEditByType();
            _showEditSuccessByType();
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

    // ── Delete modal (UI-only) ────────────────────────────────
    let _deleteTargetRow = null;

    function openDeleteModal(btn) {
        _deleteTargetRow = btn.closest('tr');
        const name = btn.dataset.displayName || 'this account';
        const nameEl = document.getElementById('deleteAccountName');
        if (nameEl) nameEl.textContent = name;
        toggleModal('deleteAccountModal', true);
    }

    window.closeDeleteModal = function () {
        toggleModal('deleteAccountModal', false);
        _deleteTargetRow = null;
    };

    window.confirmDeleteAccount = function () {
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
    };

    // ── Batch upload (SK Officials) ───────────────────────────
    const BATCH_COLS = ['Full Name','First Name','Middle Name','Last Name','Suffix','Sex','Birthdate','Contact Number','Position','Status','Region','Province','Municipality','Barangay','Term Start Date','Term End Date','Committee','Email Address'];
    const fileInput = document.getElementById('officialBatchFile');
    const dropzone  = document.getElementById('officialDropzone');
    const fileLabel = document.getElementById('officialFileName');
    const preview   = document.getElementById('officialBatchPreview');

    function renderPreview(rows) {
        if (!preview) return;
        const thead = `<thead><tr>${BATCH_COLS.map(c => `<th>${c}</th>`).join('')}</tr></thead>`;
        const tbody = `<tbody>${rows.map(r => `<tr>${BATCH_COLS.map((_, i) => `<td>${r[i] ?? ''}</td>`).join('')}</tr>`).join('')}</tbody>`;
        preview.innerHTML = `<div class="batch-preview-wrap"><table class="batch-preview-table">${thead}${tbody}</table></div>`;
        preview.style.display = '';
    }

    function parseCSV(text) {
        const lines = text.trim().split(/\r?\n/);
        const start = lines[0] && /[a-zA-Z]/.test(lines[0]) ? 1 : 0;
        return lines.slice(start).map(l => l.split(/\t|,/).map(c => c.replace(/^"|"$/g,'').trim())).filter(r => r.some(c => c));
    }

    if (fileInput && dropzone) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (fileLabel) fileLabel.textContent = file?.name || 'No file selected';
            if (file) { const r = new FileReader(); r.onload = e => renderPreview(parseCSV(e.target.result)); r.readAsText(file); }
        });
        dropzone.addEventListener('click', e => { if (!e.target.classList.contains('dropzone-browse')) fileInput.click(); });
        dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
        dropzone.addEventListener('drop', e => {
            e.preventDefault(); dropzone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) {
                const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
                if (fileLabel) fileLabel.textContent = file.name;
                const r = new FileReader(); r.onload = ev => renderPreview(parseCSV(ev.target.result)); r.readAsText(file);
            }
        });
    }
});
