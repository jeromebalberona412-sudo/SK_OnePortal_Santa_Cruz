document.addEventListener('DOMContentLoaded', () => {
    initializeScheduleKKProfiling();
});

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');
    const icon = type === 'error' ? '✕' : '✓';
    toast.innerHTML = '<span class="app-toast-icon">' + icon + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ── Format helpers ─────────────────────────────────────────────────────────
function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(`${dateStr}T00:00:00`);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── CSRF helper ────────────────────────────────────────────────────────────
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function apiFetch(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            ...options.headers,
        },
        ...options,
    });
    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        const firstFieldError = err.errors
            ? Object.values(err.errors).flat()[0]
            : null;
        throw new Error(firstFieldError || err.message || `HTTP ${res.status}`);
    }
    return res.json();
}

// ── Main ───────────────────────────────────────────────────────────────────
function initializeScheduleKKProfiling() {
    const SCHEDULE_YEAR = new Date().getFullYear();

    let schedules = [];
    let activeId = null;
    let currentPage = 1;
    let recordsPerPage = 10;

    let filterStatus = '';
    let filterSearch = '';

    // DOM refs
    const tbody        = document.getElementById('skkpTableBody');
    const searchInput  = document.getElementById('skkpSearch');
    const statusFilter = document.getElementById('skkpStatusFilter');
    const createBtn    = document.getElementById('skkpCreateBtn');

    const formModal     = document.getElementById('skkpFormModal');
    const formTitle     = document.getElementById('skkpFormModalTitle');
    const formSaveBtn   = document.getElementById('skkpFormSaveBtn');
    const formCancelBtn = document.getElementById('skkpFormCancelBtn');
    const formRestoreBtn = document.getElementById('skkpFormRestoreBtn');
    const editIdInput   = document.getElementById('skkpEditId');

    const viewModal     = document.getElementById('skkpViewModal');
    const viewRestoreBtn = document.getElementById('skkpViewRestoreBtn');

    const dateStartMdInput = document.getElementById('skkpFormDateStartMd');
    const dateExpiryMdInput = document.getElementById('skkpFormDateExpiryMd');
    const dateStartError = document.getElementById('skkpDateStartError');
    const dateExpiryError = document.getElementById('skkpDateExpiryError');
    const MAX_RANGE_DAYS = 366;

    function localDateString(date = new Date()) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function parseYmdToLocalDate(ymd) {
        if (!ymd) return null;
        const [y, m, d] = ymd.split('-').map(Number);
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d, 0, 0, 0, 0);
    }

    function dayDiffInclusive(startYmd, endYmd) {
        const start = parseYmdToLocalDate(startYmd);
        const end = parseYmdToLocalDate(endYmd);
        if (!start || !end) return 0;
        const ms = end.getTime() - start.getTime();
        return Math.floor(ms / 86400000);
    }

    function ymdToMd(ymd) {
        if (!ymd) return '';
        const parts = ymd.split('-');
        if (parts.length !== 3) return '';
        const month = parseInt(parts[1], 10);
        const day = parseInt(parts[2], 10);
        if (!month || !day) return '';
        return `${month}/${day}`;
    }

    function mdToYmd(mdStr) {
        if (!mdStr) return '';
        const match = String(mdStr).trim().match(/^(\d{1,2})\/(\d{1,2})$/);
        if (!match) return '';
        const month = parseInt(match[1], 10);
        const day = parseInt(match[2], 10);
        if (month < 1 || month > 12 || day < 1 || day > 31) return '';
        const monthStr = String(month).padStart(2, '0');
        const dayStr = String(day).padStart(2, '0');
        const candidate = `${SCHEDULE_YEAR}-${monthStr}-${dayStr}`;
        const parsed = parseYmdToLocalDate(candidate);
        if (!parsed || parsed.getMonth() + 1 !== month || parsed.getDate() !== day) return '';
        return candidate;
    }

    function formatMdInput(raw) {
        const digits = String(raw).replace(/\D/g, '').slice(0, 4);
        if (digits.length <= 2) return digits;
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }

    function showFieldError(el, message) {
        if (!el) return;
        el.textContent = message || '';
        el.style.display = message ? 'block' : 'none';
    }

    function getDateStartYmd() {
        return mdToYmd(dateStartMdInput?.value || '');
    }

    function getDateExpiryYmd() {
        return mdToYmd(dateExpiryMdInput?.value || '');
    }

    function validateDateWindow() {
        const today = localDateString();
        const dateStart = getDateStartYmd();
        const dateExpiry = getDateExpiryYmd();
        const startMd = dateStartMdInput?.value?.trim() || '';
        const expiryMd = dateExpiryMdInput?.value?.trim() || '';
        let valid = true;

        showFieldError(dateStartError, '');
        showFieldError(dateExpiryError, '');

        if (startMd && !dateStart) {
            showFieldError(dateStartError, 'Enter a valid date as MM/DD.');
            valid = false;
        }

        if (expiryMd && !dateExpiry) {
            showFieldError(dateExpiryError, 'Enter a valid date as MM/DD.');
            valid = false;
        }

        if (dateStart && dateStart < today) {
            showFieldError(dateStartError, 'Past dates are not allowed.');
            valid = false;
        }

        if (dateExpiry && dateExpiry < today) {
            showFieldError(dateExpiryError, 'Past dates are not allowed.');
            valid = false;
        }

        if (dateStart && dateExpiry && dateExpiry < dateStart) {
            showFieldError(dateExpiryError, 'Date Expiry must be on or after Date Start.');
            valid = false;
        }

        if (dateStart && dateExpiry && dayDiffInclusive(dateStart, dateExpiry) > MAX_RANGE_DAYS) {
            showFieldError(dateExpiryError, 'Date range cannot exceed one year.');
            valid = false;
        }

        return valid;
    }

    function hasScheduleForCurrentYear(excludeId = null) {
        return schedules.some((s) => {
            if (excludeId && s.id === excludeId) return false;
            const year = parseInt((s.dateStart || '').split('-')[0], 10);
            return year === SCHEDULE_YEAR;
        });
    }

    function updateCreateBtnState() {
        if (!createBtn) return;
        const blocked = hasScheduleForCurrentYear();
        createBtn.disabled = blocked;
        createBtn.title = blocked
            ? `A schedule for ${SCHEDULE_YEAR} already exists.`
            : '';
    }

    // ── API ─────────────────────────────────────────────────────────────────
    async function loadData() {
        try {
            const res = await apiFetch('/api/schedule-kk-profiling/data');
            schedules = res.data.map(s => ({
                id:          s.id,
                dateStart:   s.date_start,
                dateExpiry:  s.date_expiry,
                link:        s.link,
                status:      s.status,
            }));
            renderTable();
            updateCreateBtnState();
        } catch (e) {
            showToast('Failed to load schedules.', 'error');
        }
    }

    // ── Render ──────────────────────────────────────────────────────────────
    function getFiltered() {
        return schedules.filter(s => {
            if (filterStatus && s.status !== filterStatus) return false;
            if (filterSearch) {
                const q = filterSearch.toLowerCase();
                const match = [s.dateStart, s.dateExpiry, s.link, s.status]
                    .some(v => v && String(v).toLowerCase().includes(q));
                if (!match) return false;
            }
            return true;
        });
    }

    function getTotalPages(count = getFiltered().length) {
        return Math.max(1, Math.ceil(count / recordsPerPage) || 1);
    }

    function updatePaginationFooter(totalRecords) {
        const totalPages = getTotalPages(totalRecords);
        const pageInput = document.getElementById('skkpPageInput');
        const totalPagesEl = document.getElementById('skkpTotalPages');
        const prevBtn = document.getElementById('skkpPrevBtn');
        const nextBtn = document.getElementById('skkpNextBtn');
        const info = document.getElementById('skkpPaginationInfo');

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        if (pageInput) {
            pageInput.value = String(currentPage);
            pageInput.min = '1';
            pageInput.max = String(totalPages);
        }

        if (totalPagesEl) {
            totalPagesEl.textContent = String(totalPages);
        }

        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        if (info) {
            info.textContent = `${totalRecords} record${totalRecords === 1 ? '' : 's'}`;
        }
    }

    function goToPage(page) {
        const totalPages = getTotalPages();
        if (page >= 1 && page <= totalPages) {
            currentPage = page;
            renderTable();
        }
    }

    function renderTable() {
        const filtered = getFiltered();
        const totalPages = getTotalPages(filtered.length);
        const start = (currentPage - 1) * recordsPerPage;
        const end   = Math.min(start + recordsPerPage, filtered.length);
        const page  = filtered.slice(start, end);

        tbody.innerHTML = '';

        if (page.length === 0) {
            const tr = document.createElement('tr');
            tr.className = 'empty-state-row';
            const td = document.createElement('td');
            td.colSpan = 5;
            td.textContent = 'No schedules found.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            page.forEach(s => {
                const tr = document.createElement('tr');
                const statusClass = s.status.toLowerCase().replace(/\s+/g, '-');
                const linkHtml = s.link
                    ? `<a href="${s.link}" target="_blank" rel="noopener noreferrer" class="skkp-link skkp-link-full">${s.link}</a>`
                    : '—';
                tr.innerHTML = `
                    <td>${formatDate(s.dateStart)}</td>
                    <td>${formatDate(s.dateExpiry)}</td>
                    <td>${linkHtml}</td>
                    <td><span class="skkp-status-badge ${statusClass}">${s.status}</span></td>
                    <td>
                        <div class="skkp-actions">
                            <button class="skkp-btn skkp-btn-view" data-action="view" data-id="${s.id}">View</button>
                            <button class="skkp-btn skkp-btn-edit" data-action="edit" data-id="${s.id}">Edit</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updatePaginationFooter(filtered.length);
        updateStats();
    }

    function updateStats() {
        const counts = { Upcoming: 0, Ongoing: 0, Completed: 0, Cancelled: 0 };
        schedules.forEach(s => { if (counts[s.status] !== undefined) counts[s.status]++; });
        const el = (id) => document.getElementById(id);
        if (el('skkpStatUpcoming'))  el('skkpStatUpcoming').textContent  = counts.Upcoming;
        if (el('skkpStatOngoing'))   el('skkpStatOngoing').textContent   = counts.Ongoing;
        if (el('skkpStatCompleted')) el('skkpStatCompleted').textContent = counts.Completed;
        if (el('skkpStatCancelled')) el('skkpStatCancelled').textContent = counts.Cancelled;
    }

    // ── Modal helpers ───────────────────────────────────────────────────────
    function openModal(el)  { if (el) el.style.display = 'flex'; }
    function closeModal(el) { if (el) el.style.display = 'none'; }

    // ── Maximize / Restore ──────────────────────────────────────────────────
    let isMaximized = false;
    const maximizeIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>`;
    const restoreIcon  = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M3 16V5a2 2 0 0 1 2-2h11"/></svg>`;
    const formModalBox = formModal ? formModal.querySelector('.skkp-form-modal-box') : null;

    function resetModalSize() {
        isMaximized = false;
        if (formModalBox) {
            formModalBox.style.maxWidth = formModalBox.style.maxHeight =
            formModalBox.style.width    = formModalBox.style.height    =
            formModalBox.style.borderRadius = '';
        }
        if (formRestoreBtn) { formRestoreBtn.innerHTML = maximizeIcon; formRestoreBtn.title = 'Maximize / Restore'; }
    }

    if (formRestoreBtn) {
        formRestoreBtn.addEventListener('click', () => {
            isMaximized = !isMaximized;
            if (formModalBox) {
                if (isMaximized) {
                    formModalBox.style.maxWidth = formModalBox.style.width  = '100vw';
                    formModalBox.style.maxHeight = formModalBox.style.height = '100vh';
                    formModalBox.style.borderRadius = '0';
                    formRestoreBtn.innerHTML = restoreIcon;
                    formRestoreBtn.title = 'Restore Down';
                } else { resetModalSize(); }
            }
        });
    }

    let isViewMaximized = false;
    const viewModalBox = viewModal ? viewModal.querySelector('.skkp-view-modal-box') : null;

    function resetViewModalSize() {
        isViewMaximized = false;
        if (viewModalBox) {
            viewModalBox.style.maxWidth = viewModalBox.style.maxHeight =
            viewModalBox.style.width    = viewModalBox.style.height    =
            viewModalBox.style.borderRadius = '';
        }
        if (viewRestoreBtn) { viewRestoreBtn.innerHTML = maximizeIcon; viewRestoreBtn.title = 'Maximize / Restore'; }
    }

    if (viewRestoreBtn) {
        viewRestoreBtn.addEventListener('click', () => {
            isViewMaximized = !isViewMaximized;
            if (viewModalBox) {
                if (isViewMaximized) {
                    viewModalBox.style.maxWidth = viewModalBox.style.width  = '100vw';
                    viewModalBox.style.maxHeight = viewModalBox.style.height = '100vh';
                    viewModalBox.style.borderRadius = '0';
                    viewRestoreBtn.innerHTML = restoreIcon;
                    viewRestoreBtn.title = 'Restore Down';
                } else { resetViewModalSize(); }
            }
        });
    }

    [formModal, viewModal].forEach(modal => {
        if (!modal) return;
        modal.addEventListener('click', e => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close')) {
                closeModal(modal);
                if (modal === formModal) resetModalSize();
                if (modal === viewModal) resetViewModalSize();
            }
        });
    });

    // ── Form helpers ────────────────────────────────────────────────────────
    const getFormField = id => { const el = document.getElementById(id); return el ? el.value.trim() : ''; };
    const setFormField = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };

    function clearForm() {
        setFormField('skkpFormDateStartMd', '');
        setFormField('skkpFormDateExpiryMd', '');
        setFormField('skkpFormLink', '');
        setFormField('skkpFormStatus', 'Upcoming');
        showFieldError(dateStartError, '');
        showFieldError(dateExpiryError, '');
    }

    // ── Status hint ─────────────────────────────────────────────────────────
    const statusHints = {
        Upcoming:  { cls: 'hint-info',    msg: 'Scheduled but not yet open. Kabataan will see the date range but cannot sign up yet.' },
        Ongoing:   { cls: 'hint-success', msg: 'Sign-up is currently open. Kabataan can select this barangay and submit the form.' },
        Completed: { cls: 'hint-warning', msg: 'Profiling is done. Sign-up will be closed for this barangay.' },
        Cancelled: { cls: 'hint-danger',  msg: 'Profiling is cancelled. Sign-up will be closed for this barangay.' },
    };

    const statusHintEl = document.getElementById('skkpStatusHint');

    function updateStatusHint(val) {
        if (!statusHintEl) return;
        const h = statusHints[val];
        statusHintEl.className = 'skkp-status-hint' + (h ? ' ' + h.cls : '');
        statusHintEl.textContent = h ? h.msg : '';
    }

    const statusSelectEl = document.getElementById('skkpFormStatus');
    if (statusSelectEl) {
        statusSelectEl.addEventListener('change', () => updateStatusHint(statusSelectEl.value));
    }

    // ── Create ──────────────────────────────────────────────────────────────
    if (createBtn) {
        createBtn.addEventListener('click', () => {
            if (hasScheduleForCurrentYear()) {
                showToast(`A schedule for ${SCHEDULE_YEAR} already exists.`, 'error');
                return;
            }
            editIdInput.value = '';
            formTitle.textContent = 'Create Schedule';
            formSaveBtn.textContent = 'Save Schedule';
            clearForm();
            updateStatusHint('Upcoming');
            openModal(formModal);
        });
    }

    if (formCancelBtn) formCancelBtn.addEventListener('click', () => {
        closeModal(formModal);
        resetModalSize();
        if (statusHintEl) statusHintEl.className = 'skkp-status-hint';
    });

    if (formSaveBtn) {
        formSaveBtn.addEventListener('click', async () => {
            const dateStart  = getDateStartYmd();
            const dateExpiry = getDateExpiryYmd();
            const link       = getFormField('skkpFormLink');
            const status     = getFormField('skkpFormStatus') || 'Upcoming';
            const id = editIdInput.value ? parseInt(editIdInput.value, 10) : null;

            if (!dateStart || !dateExpiry || !status) {
                showToast('Please fill in all required fields (MM/DD).', 'error');
                validateDateWindow();
                return;
            }
            if (!validateDateWindow()) {
                return;
            }
            if (!id && hasScheduleForCurrentYear()) {
                showToast(`A schedule for ${SCHEDULE_YEAR} already exists.`, 'error');
                return;
            }
            if (link && !isValidUrl(link)) {
                showToast('Please enter a valid URL (e.g. https://example.com).', 'error');
                return;
            }

            const payload = { date_start: dateStart, date_expiry: dateExpiry, link: link || null, status };

            try {
                formSaveBtn.disabled = true;
                if (typeof showLoading === 'function') {
                    showLoading(id ? 'Updating schedule' : 'Creating schedule');
                }
                if (id) {
                    await apiFetch(`/api/schedule-kk-profiling/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
                    showToast('Schedule updated successfully!', 'success');
                } else {
                    await apiFetch('/api/schedule-kk-profiling', { method: 'POST', body: JSON.stringify(payload) });
                    showToast('Schedule created successfully!', 'success');
                }
                closeModal(formModal);
                resetModalSize();
                await loadData();
            } catch (e) {
                showToast(e.message || 'Failed to save schedule.', 'error');
            } finally {
                formSaveBtn.disabled = false;
                if (typeof hideLoading === 'function') {
                    hideLoading();
                }
            }
        });
    }

    // ── Table actions ───────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        const id     = parseInt(btn.getAttribute('data-id'), 10);
        const sched  = schedules.find(s => s.id === id);
        if (!sched) return;

        activeId = id;

        if (action === 'view') {
            populateViewModal(sched);
            resetViewModalSize();
            openModal(viewModal);
        } else if (action === 'edit') {
            editIdInput.value = sched.id;
            formTitle.textContent = 'Edit Schedule';
            formSaveBtn.textContent = 'Update Schedule';
            const today = localDateString();
            let startVal = sched.dateStart || '';
            let expiryVal = sched.dateExpiry || '';

            if (startVal && startVal < today) {
                startVal = today;
            }
            if (expiryVal && startVal && expiryVal < startVal) {
                expiryVal = startVal;
            }
            if (expiryVal && expiryVal < today) {
                expiryVal = startVal || today;
            }

            setFormField('skkpFormDateStartMd', ymdToMd(startVal));
            setFormField('skkpFormDateExpiryMd', ymdToMd(expiryVal));
            setFormField('skkpFormLink', sched.link);
            setFormField('skkpFormStatus', sched.status);
            updateStatusHint(sched.status);
            showFieldError(dateStartError, '');
            showFieldError(dateExpiryError, '');
            openModal(formModal);
        }
    });

    // ── View modal ──────────────────────────────────────────────────────────
    function populateViewModal(s) {
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
        set('skkpViewDateStart',  formatDate(s.dateStart));
        set('skkpViewDateExpiry', formatDate(s.dateExpiry));
        const linkEl = document.getElementById('skkpViewLink');
        if (linkEl) {
            linkEl.innerHTML = s.link
                ? `<a href="${s.link}" target="_blank" rel="noopener noreferrer" class="skkp-link">${s.link}</a>`
                : '—';
        }
        const statusEl = document.getElementById('skkpViewStatus');
        if (statusEl) {
            const cls = s.status.toLowerCase().replace(/\s+/g, '-');
            statusEl.innerHTML = `<span class="skkp-status-badge ${cls}">${s.status}</span>`;
        }
    }

    // ── Filters ─────────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => { filterSearch = searchInput.value.trim(); currentPage = 1; renderTable(); });
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', () => { filterStatus = statusFilter.value; currentPage = 1; renderTable(); });
    }

    // ── Pagination ──────────────────────────────────────────────────────────
    const prevBtn = document.getElementById('skkpPrevBtn');
    const nextBtn = document.getElementById('skkpNextBtn');
    const pageInput = document.getElementById('skkpPageInput');
    const rowsPerPageSelect = document.getElementById('skkpRowsPerPageSelect');

    if (prevBtn) prevBtn.addEventListener('click', () => goToPage(currentPage - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => goToPage(currentPage + 1));

    if (pageInput) {
        pageInput.addEventListener('change', () => {
            const page = parseInt(pageInput.value, 10);
            if (!Number.isNaN(page)) {
                goToPage(page);
            }
        });
    }

    if (rowsPerPageSelect) {
        recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
        rowsPerPageSelect.addEventListener('change', () => {
            recordsPerPage = parseInt(rowsPerPageSelect.value, 10) || 10;
            currentPage = 1;
            renderTable();
        });
    }

    // ── Date input formatting ───────────────────────────────────────────────
    function bindMdInput(input) {
        if (!input) return;
        input.addEventListener('input', () => {
            const pos = input.selectionStart;
            const before = input.value;
            input.value = formatMdInput(input.value);
            if (input.value.length < before.length && pos !== null) {
                input.setSelectionRange(pos, pos);
            }
            validateDateWindow();
        });
        input.addEventListener('blur', validateDateWindow);
    }

    bindMdInput(dateStartMdInput);
    bindMdInput(dateExpiryMdInput);

    // ── Utility ─────────────────────────────────────────────────────────────
    function isValidUrl(str) { try { new URL(str); return true; } catch { return false; } }

    // ── Boot ────────────────────────────────────────────────────────────────
    loadData();
}
