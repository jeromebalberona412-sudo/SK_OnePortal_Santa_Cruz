/**
 * Manage Kabataan Module — JavaScript
 * Handles client-side filtering, pagination, view/edit modals, and AJAX updates.
 */

(function () {
    'use strict';

    /* ── State ─────────────────────────────────────────────────── */
    let allRows = [];
    let filteredRows = [];
    let currentPage = 1;
    const PAGE_SIZE = 15;

    /* ── DOM refs ──────────────────────────────────────────────── */
    const tableBody = document.getElementById('mkTableBody');
    const searchInput = document.getElementById('mkSearch');
    const filterBarangay = document.getElementById('mkFilterBarangay');
    const filterGender = document.getElementById('mkFilterGender');
    const filterVoter = document.getElementById('mkFilterVoter');
    const filterAccount = document.getElementById('mkFilterAccount');
    const filterVerif = document.getElementById('mkFilterVerification');
    const ageMin = document.getElementById('mkAgeMin');
    const ageMax = document.getElementById('mkAgeMax');
    const resetBtn = document.getElementById('mkResetFilters');
    const paginationInfo = document.getElementById('mkPaginationInfo');
    const prevBtn = document.getElementById('mkPrevBtn');
    const nextBtn = document.getElementById('mkNextBtn');
    const pageNumbers = document.getElementById('mkPageNumbers');
    const statsRow = document.getElementById('mkStatsRow');

    /* ── Bootstrap ─────────────────────────────────────────────── */
    function init() {
        collectRows();
        buildStats();
        applyFilters();
        bindEvents();
        bindModalEvents();
    }

    /* ── Collect rows from rendered HTML ───────────────────────── */
    function collectRows() {
        allRows = Array.from(tableBody.querySelectorAll('tr[data-id]'));
    }

    /* ── Stats ─────────────────────────────────────────────────── */
    function buildStats() {
        if (!statsRow) return;

        const total = allRows.length;
        const active = allRows.filter(r => r.dataset.accountStatus === 'Active').length;
        const inactive = allRows.filter(r => r.dataset.accountStatus === 'Inactive').length;
        const verified = allRows.filter(r => r.dataset.verificationStatus === 'Verified').length;
        const male = allRows.filter(r => r.dataset.gender === 'Male').length;

        const cards = [
            { label: 'Total Kabataan', value: total, colorClass: 'mk-stat-card-blue', iconClass: 'mk-stat-icon-blue', icon: usersIcon() },
            { label: 'Active', value: active, colorClass: 'mk-stat-card-green', iconClass: 'mk-stat-icon-green', icon: checkIcon() },
            { label: 'Inactive', value: inactive, colorClass: 'mk-stat-card-red', iconClass: 'mk-stat-icon-red', icon: xIcon() },
            { label: 'Verified', value: verified, colorClass: 'mk-stat-card-indigo', iconClass: 'mk-stat-icon-indigo', icon: shieldIcon() },
            { label: 'Male', value: male, colorClass: 'mk-stat-card-yellow', iconClass: 'mk-stat-icon-yellow', icon: personIcon() },
        ];

        statsRow.innerHTML = cards.map(c => `
            <div class="mk-stat-card ${c.colorClass}">
                <div class="mk-stat-top">
                    <span class="mk-stat-value">${c.value}</span>
                    <span class="mk-stat-icon ${c.iconClass}">${c.icon}</span>
                </div>
                <div class="mk-stat-label">${c.label}</div>
            </div>
        `).join('');
    }

    /* ── Filter logic ──────────────────────────────────────────── */
    function applyFilters() {
        const search = (searchInput?.value || '').toLowerCase().trim();
        const bgy = filterBarangay?.value || '';
        const gender = filterGender?.value || '';
        const voter = filterVoter?.value || '';
        const account = filterAccount?.value || '';
        const verif = filterVerif?.value || '';
        const minAge = ageMin?.value ? parseInt(ageMin.value, 10) : null;
        const maxAge = ageMax?.value ? parseInt(ageMax.value, 10) : null;

        filteredRows = allRows.filter(row => {
            const d = row.dataset;

            if (search) {
                const haystack = [
                    d.firstName, d.lastName, d.middleName,
                    d.kkNumber, d.email, d.contact,
                ].join(' ').toLowerCase();
                if (!haystack.includes(search)) return false;
            }

            if (bgy && d.barangayId !== bgy) return false;
            if (gender && d.gender !== gender) return false;
            if (voter && d.nationalVoter !== voter) return false;
            if (account && d.accountStatus !== account) return false;
            if (verif && d.verificationStatus !== verif) return false;

            if (minAge !== null && parseInt(d.age, 10) < minAge) return false;
            if (maxAge !== null && parseInt(d.age, 10) > maxAge) return false;

            return true;
        });

        currentPage = 1;
        renderPage();
    }

    /* ── Render current page ───────────────────────────────────── */
    function renderPage() {
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
        currentPage = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * PAGE_SIZE;
        const end = Math.min(start + PAGE_SIZE, total);

        // Hide all rows, show only current page slice
        allRows.forEach(r => { r.style.display = 'none'; });
        filteredRows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        // Empty state row
        const emptyRow = document.getElementById('mkEmptyRow');
        if (emptyRow) {
            emptyRow.style.display = total === 0 ? '' : 'none';
        } else if (total === 0) {
            // Insert empty row if not present
            const tr = document.createElement('tr');
            tr.id = 'mkEmptyRow';
            tr.innerHTML = `<td colspan="12" class="mk-empty-state">
                <div class="mk-empty-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <p>No Kabataan records found.</p>
                    <span>Try adjusting your search or filters.</span>
                </div>
            </td>`;
            tableBody.appendChild(tr);
        }

        // Pagination info
        if (paginationInfo) {
            paginationInfo.textContent = total === 0
                ? 'No records'
                : `Showing ${start + 1}–${end} of ${total} record${total !== 1 ? 's' : ''}`;
        }

        // Prev / Next
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        // Page numbers
        renderPageNumbers(totalPages);
    }

    function renderPageNumbers(totalPages) {
        if (!pageNumbers) return;
        pageNumbers.innerHTML = '';

        const range = buildPageRange(currentPage, totalPages);
        range.forEach(item => {
            if (item === '…') {
                const span = document.createElement('span');
                span.className = 'mk-page-ellipsis';
                span.textContent = '…';
                pageNumbers.appendChild(span);
            } else {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mk-page-num' + (item === currentPage ? ' active' : '');
                btn.textContent = item;
                btn.addEventListener('click', () => { currentPage = item; renderPage(); });
                pageNumbers.appendChild(btn);
            }
        });
    }

    function buildPageRange(current, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        if (current <= 4) return [1, 2, 3, 4, 5, '…', total];
        if (current >= total - 3) return [1, '…', total - 4, total - 3, total - 2, total - 1, total];
        return [1, '…', current - 1, current, current + 1, '…', total];
    }

    /* ── Event bindings ────────────────────────────────────────── */
    function bindEvents() {
        searchInput?.addEventListener('input', applyFilters);
        filterBarangay?.addEventListener('change', applyFilters);
        filterGender?.addEventListener('change', applyFilters);
        filterVoter?.addEventListener('change', applyFilters);
        filterAccount?.addEventListener('change', applyFilters);
        filterVerif?.addEventListener('change', applyFilters);
        ageMin?.addEventListener('input', applyFilters);
        ageMax?.addEventListener('input', applyFilters);

        resetBtn?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (filterBarangay) filterBarangay.value = '';
            if (filterGender) filterGender.value = '';
            if (filterVoter) filterVoter.value = '';
            if (filterAccount) filterAccount.value = '';
            if (filterVerif) filterVerif.value = '';
            if (ageMin) ageMin.value = '';
            if (ageMax) ageMax.value = '';
            applyFilters();
        });

        prevBtn?.addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderPage(); } });
        nextBtn?.addEventListener('click', () => {
            const total = Math.ceil(filteredRows.length / PAGE_SIZE);
            if (currentPage < total) { currentPage++; renderPage(); }
        });
    }

    /* ── Modal logic ───────────────────────────────────────────── */
    function bindModalEvents() {
        // View modal
        tableBody.addEventListener('click', e => {
            const viewBtn = e.target.closest('.mk-btn-view');
            const editBtn = e.target.closest('.mk-btn-edit');
            const recoverBtn = e.target.closest('.mk-btn-recover');
            if (viewBtn) openViewModal(viewBtn.dataset.id);
            if (editBtn) openEditModal(editBtn.dataset.id);
            if (recoverBtn) openRecoverModal(recoverBtn.dataset.id);
        });

        document.getElementById('mkViewCloseBtn')?.addEventListener('click', closeViewModal);
        document.getElementById('mkEditCloseBtn')?.addEventListener('click', closeEditModal);
        document.getElementById('mkEditCancelBtn')?.addEventListener('click', closeEditModal);

        // Maximize toggles
        document.getElementById('mkViewToggleBtn')?.addEventListener('click', () => {
            document.getElementById('mkViewModalBox')?.classList.toggle('mk-modal-maximized');
        });
        document.getElementById('mkEditToggleBtn')?.addEventListener('click', () => {
            document.getElementById('mkEditModalBox')?.classList.toggle('mk-modal-maximized');
        });

        // Close on backdrop click
        document.getElementById('mkViewModal')?.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeViewModal();
        });
        document.getElementById('mkEditModal')?.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeEditModal();
        });

        // Escape key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeViewModal(); closeEditModal(); closeRecoverModal(); }
        });

        bindRecoverModalEvents();

        // Birthday → auto-calculate age
        document.getElementById('mkEditBirthday')?.addEventListener('change', function () {
            const dob = new Date(this.value);
            if (!isNaN(dob)) {
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
                const ageField = document.getElementById('mkEditAge');
                if (ageField) ageField.value = age;
            }
        });

        // Edit form submit
        document.getElementById('mkEditForm')?.addEventListener('submit', handleEditSubmit);
    }

    /* ── View Modal ────────────────────────────────────────────── */
    function openViewModal(id) {
        const row = allRows.find(r => r.dataset.id === String(id));
        if (!row) return;
        const d = row.dataset;

        const fullName = [d.firstName, d.middleName ? d.middleName[0] + '.' : '', d.lastName, d.suffix]
            .filter(Boolean).join(' ');

        document.getElementById('mkViewBody').innerHTML = `
            ${viewSection('Personal Information', [
            ['Full Name', fullName],
            ['First Name', d.firstName],
            ['Middle Name', d.middleName],
            ['Last Name', d.lastName],
            ['Suffix', d.suffix],
            ['Age', d.age],
            ['Gender', d.gender],
            ['Birthday', d.birthday],
            ['Civil Status', d.civilStatus],
        ])}
            ${viewSection('Contact & Location', [
            ['Contact Number', d.contact],
            ['Email', d.email],
            ['Barangay', d.barangay],
            ['Purok / Zone', d.purok],
        ])}
            ${viewSection('KK Information', [
            ['KK Number', d.kkNumber],
            ['Youth Classification', d.youthClassification],
            ['Educational Background', d.educationalBackground],
            ['Work Status', d.workStatus],
            ['SK Voter', d.skVoter],
            ['National Voter', d.nationalVoter],
        ])}
            ${viewSection('Account Status', [
            ['Account Status', d.accountStatus],
            ['Verification Status', d.verificationStatus],
        ])}
        `;

        document.getElementById('mkViewModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function viewSection(title, fields) {
        return `
            <div class="mk-view-section">
                <div class="mk-view-section-title">${esc(title)}</div>
                <div class="mk-view-grid">
                    ${fields.map(([label, val]) => `
                        <div>
                            <div class="mk-view-field-label">${esc(label)}</div>
                            <div class="mk-view-field-value">${esc(val) || '—'}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    function closeViewModal() {
        const modal = document.getElementById('mkViewModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    /* ── Edit Modal ────────────────────────────────────────────── */
    function openEditModal(id) {
        const row = allRows.find(r => r.dataset.id === String(id));
        if (!row) return;
        const d = row.dataset;

        const f = name => document.getElementById(name);

        f('mkEditId').value = d.id;
        f('mkEditFirstName').value = d.firstName || '';
        f('mkEditMiddleName').value = d.middleName || '';
        f('mkEditLastName').value = d.lastName || '';
        f('mkEditSuffix').value = d.suffix || '';
        f('mkEditBirthday').value = d.birthday || '';
        f('mkEditAge').value = d.age || '';
        f('mkEditGender').value = d.gender || '';
        f('mkEditCivilStatus').value = d.civilStatus || '';
        f('mkEditContact').value = d.contact || '';
        f('mkEditEmail').value = d.email || '';
        f('mkEditBarangay').value = d.barangayId || '';
        f('mkEditPurok').value = d.purok || '';
        f('mkEditKkNumber').value = d.kkNumber || '';
        f('mkEditYouthClassification').value = d.youthClassification || '';
        f('mkEditEducationalBackground').value = d.educationalBackground || '';
        f('mkEditWorkStatus').value = d.workStatus || '';
        f('mkEditSkVoter').value = d.skVoter || '';
        f('mkEditNationalVoter').value = d.nationalVoter || '';
        f('mkEditAccountStatus').value = d.accountStatus || '';
        f('mkEditVerificationStatus').value = d.verificationStatus || '';

        clearErrors();
        document.getElementById('mkEditModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        const modal = document.getElementById('mkEditModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    /* ── Recover Account Modal ─────────────────────────────────── */
    function openRecoverModal(id) {
        const row = allRows.find(r => r.dataset.id === String(id));
        if (!row) return;
        const d = row.dataset;

        const fullName = [d.firstName, d.middleName ? d.middleName[0] + '.' : '', d.lastName, d.suffix]
            .filter(Boolean).join(' ');

        document.getElementById('mkRecoverId').value = d.id;

        // Populate read-only info
        document.getElementById('rInfoFullName').textContent = fullName || '—';
        document.getElementById('rInfoKkNumber').textContent = d.kkNumber || '—';
        document.getElementById('rInfoEmail').textContent = d.email || '—';
        document.getElementById('rInfoBarangay').textContent = d.barangay || '—';

        // Verification badge
        const verifEl = document.getElementById('rInfoVerification');
        verifEl.innerHTML = d.verificationStatus === 'Verified'
            ? '<span class="mk-badge mk-badge-blue">Verified</span>'
            : d.verificationStatus === 'Unverified'
                ? '<span class="mk-badge mk-badge-yellow">Unverified</span>'
                : '<span class="mk-badge mk-badge-gray">—</span>';

        // Account status badge
        const acctEl = document.getElementById('rInfoAccountStatus');
        acctEl.innerHTML = d.accountStatus === 'Active'
            ? '<span class="mk-badge mk-badge-green">Active</span>'
            : d.accountStatus === 'Inactive' || d.accountStatus === 'Disabled'
                ? '<span class="mk-badge mk-badge-red">' + esc(d.accountStatus) + '</span>'
                : d.accountStatus
                    ? '<span class="mk-badge mk-badge-gray">' + esc(d.accountStatus) + '</span>'
                    : '<span class="mk-badge mk-badge-gray">—</span>';

        // Reset form fields
        document.getElementById('rNewEmail').value = '';
        document.getElementById('rConfirmEmail').value = '';

        clearRecoverErrors();

        document.getElementById('mkRecoverModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRecoverModal() {
        const modal = document.getElementById('mkRecoverModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function clearRecoverErrors() {
        ['errRNewEmail', 'errRConfirmEmail'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });
    }

    function bindRecoverModalEvents() {
        document.getElementById('mkRecoverCloseBtn')?.addEventListener('click', closeRecoverModal);
        document.getElementById('mkRecoverCancelBtn')?.addEventListener('click', closeRecoverModal);

        document.getElementById('mkRecoverModal')?.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeRecoverModal();
        });

        // Save Recovery Changes button (frontend-only simulation)
        document.getElementById('mkRecoverForm')?.addEventListener('submit', e => {
            e.preventDefault();
            if (!validateRecoverForm()) return;
            const btn = document.getElementById('mkRecoverSaveBtn');
            const spinner = document.getElementById('mkRecoverSaveSpinner');
            const text = document.getElementById('mkRecoverSaveText');
            btn.disabled = true;
            spinner.style.display = '';
            text.textContent = 'Submitting…';
            setTimeout(() => {
                btn.disabled = false;
                spinner.style.display = 'none';
                text.textContent = 'Submit Recovery';
                showToast('Recovery request submitted successfully.', 'success');
                closeRecoverModal();
            }, 1400);
        });
    }

    function validateRecoverForm() {
        clearRecoverErrors();
        let valid = true;

        const newEmail = document.getElementById('rNewEmail').value.trim();
        const confirmEmail = document.getElementById('rConfirmEmail').value.trim();

        if (newEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
            document.getElementById('errRNewEmail').textContent = 'Enter a valid email address.';
            valid = false;
        }
        if (newEmail && confirmEmail && newEmail !== confirmEmail) {
            document.getElementById('errRConfirmEmail').textContent = 'Email addresses do not match.';
            valid = false;
        }
        return valid;
    }

    /* ── Edit form submit ──────────────────────────────────────── */
    async function handleEditSubmit(e) {
        e.preventDefault();
        clearErrors();

        const id = document.getElementById('mkEditId').value;
        const form = document.getElementById('mkEditForm');
        const saveBtn = document.getElementById('mkEditSaveBtn');
        const spinner = document.getElementById('mkEditSaveBtnSpinner');
        const btnText = document.getElementById('mkEditSaveBtnText');

        const data = Object.fromEntries(new FormData(form).entries());

        saveBtn.disabled = true;
        spinner.style.display = '';
        btnText.textContent = 'Saving…';

        try {
            const res = await fetch(`/manage-kabataan/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const json = await res.json();

            if (res.status === 422 && json.errors) {
                displayErrors(json.errors);
                return;
            }

            if (!json.success) throw new Error(json.message || 'Update failed');

            // Update row dataset
            const row = allRows.find(r => r.dataset.id === String(id));
            if (row) updateRowData(row, data, json.data);

            closeEditModal();
            showToast('Record updated successfully.', 'success');

        } catch (err) {
            showToast(err.message || 'An error occurred.', 'error');
        } finally {
            saveBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.textContent = 'Save Changes';
        }
    }

    function updateRowData(row, data, serverData) {
        const d = row.dataset;
        d.firstName = data.first_name || '';
        d.lastName = data.last_name || '';
        d.middleName = data.middle_name || '';
        d.suffix = data.suffix || '';
        d.kkNumber = data.kk_number || '';
        d.age = data.age || '';
        d.gender = data.gender || '';
        d.birthday = data.birthday || '';
        d.barangayId = data.barangay_id || '';
        d.purok = data.purok_zone || '';
        d.contact = data.contact_number || '';
        d.email = data.email || '';
        d.youthClassification = data.youth_classification || '';
        d.educationalBackground = data.educational_background || '';
        d.workStatus = data.work_status || '';
        d.civilStatus = data.civil_status || '';
        d.skVoter = data.sk_voter || '';
        d.nationalVoter = data.national_voter || '';
        d.accountStatus = data.account_status || '';
        d.verificationStatus = data.verification_status || '';

        // Update visible cells
        const cells = row.querySelectorAll('td');
        const fullName = [data.first_name, data.middle_name ? data.middle_name[0] + '.' : '', data.last_name, data.suffix]
            .filter(Boolean).join(' ');

        if (cells[0]) cells[0].textContent = fullName || 'N/A';
        if (cells[1]) cells[1].textContent = data.kk_number || '—';
        if (cells[2]) cells[2].textContent = data.age || '—';
        if (cells[3]) cells[3].textContent = data.gender || '—';
        if (cells[6]) cells[6].textContent = data.email || '—';
        if (cells[7]) cells[7].textContent = data.youth_classification || '—';

        // Rebuild stats
        buildStats();
    }

    /* ── Validation error display ──────────────────────────────── */
    function displayErrors(errors) {
        const map = {
            first_name: 'errFirstName',
            last_name: 'errLastName',
            birthday: 'errBirthday',
            age: 'errAge',
            gender: 'errGender',
            contact_number: 'errContact',
            email: 'errEmail',
            barangay_id: 'errBarangay',
            kk_number: 'errKkNumber',
            youth_classification: 'errYouthClassification',
            account_status: 'errAccountStatus',
            verification_status: 'errVerificationStatus',
        };
        Object.entries(errors).forEach(([field, msgs]) => {
            const el = document.getElementById(map[field]);
            if (el) {
                el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                const input = document.getElementById('mkEdit' + toCamel(field));
                input?.classList.add('mk-input-error');
            }
        });
    }

    function clearErrors() {
        document.querySelectorAll('.mk-field-error').forEach(el => { el.textContent = ''; });
        document.querySelectorAll('.mk-input-error').forEach(el => el.classList.remove('mk-input-error'));
    }

    /* ── Toast ─────────────────────────────────────────────────── */
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('mkToast');
        const msgEl = document.getElementById('mkToastMsg');
        if (!toast || !msgEl) return;

        msgEl.textContent = msg;
        toast.className = `mk-toast mk-toast-${type} mk-toast-show`;

        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('mk-toast-show');
        }, 3500);
    }

    /* ── Helpers ───────────────────────────────────────────────── */
    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function toCamel(str) {
        return str.replace(/_([a-z])/g, (_, c) => c.toUpperCase())
            .replace(/^./, c => c.toUpperCase());
    }

    /* ── SVG icons ─────────────────────────────────────────────── */
    function usersIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'; }
    function checkIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'; }
    function xIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'; }
    function shieldIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'; }
    function personIcon() { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>'; }

    /* ── Run ───────────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
