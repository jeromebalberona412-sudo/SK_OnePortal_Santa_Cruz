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
function formatTime(timeStr) {
    if (!timeStr) return '—';
    const [h, m] = timeStr.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${m} ${ampm}`;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
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
        throw new Error(err.message || `HTTP ${res.status}`);
    }
    return res.json();
}

// ── Main ───────────────────────────────────────────────────────────────────
function initializeScheduleKKProfiling() {
    let schedules = [];
    let activeId = null;
    let currentPage = 1;
    const perPage = 10;

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

    const deleteModal      = document.getElementById('skkpDeleteModal');
    const deleteCancelBtn  = document.getElementById('skkpDeleteCancelBtn');
    const deleteConfirmBtn = document.getElementById('skkpDeleteConfirmBtn');

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

    function renderTable() {
        const filtered = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end   = Math.min(start + perPage, filtered.length);
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
                            <button class="skkp-btn skkp-btn-view"   data-action="view"   data-id="${s.id}">View</button>
                            <button class="skkp-btn skkp-btn-edit"   data-action="edit"   data-id="${s.id}">Edit</button>
                            <button class="skkp-btn skkp-btn-delete" data-action="delete" data-id="${s.id}">Delete</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updatePagination(filtered.length, start + 1, end, totalPages);
        updateStats();
    }

    function updateStats() {
        const counts = { Upcoming: 0, Ongoing: 0, Completed: 0, Cancelled: 0, Rescheduled: 0 };
        schedules.forEach(s => { if (counts[s.status] !== undefined) counts[s.status]++; });
        document.getElementById('skkpStatUpcoming').textContent    = counts.Upcoming;
        document.getElementById('skkpStatOngoing').textContent     = counts.Ongoing;
        document.getElementById('skkpStatCompleted').textContent   = counts.Completed;
        document.getElementById('skkpStatCancelled').textContent   = counts.Cancelled;
        document.getElementById('skkpStatRescheduled').textContent = counts.Rescheduled;
    }

    function updatePagination(total, start, end, totalPages) {
        const info = document.getElementById('skkpPaginationInfo');
        if (info) {
            info.textContent = total === 0
                ? 'No records found'
                : `Showing ${start}–${end} of ${total} records`;
        }
        const prevBtn  = document.getElementById('skkpPrevBtn');
        const nextBtn  = document.getElementById('skkpNextBtn');
        const pageNums = document.getElementById('skkpPageNumbers');
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;
        if (pageNums) {
            pageNums.innerHTML = '';
            const s = Math.max(1, currentPage - 2);
            const e = Math.min(totalPages, currentPage + 2);
            for (let i = s; i <= e; i++) {
                const btn = document.createElement('button');
                btn.className = 'page-number' + (i === currentPage ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                pageNums.appendChild(btn);
            }
        }
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

    [formModal, viewModal, deleteModal].forEach(modal => {
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
        ['skkpFormDateStart', 'skkpFormDateExpiry', 'skkpFormLink'].forEach(id => setFormField(id, ''));
        setFormField('skkpFormStatus', 'Upcoming');
    }

    // ── Status hint ─────────────────────────────────────────────────────────
    const statusHints = {
        Upcoming:    { cls: 'hint-info',    msg: '📅 Scheduled but not yet open. Kabataan will see the date range but cannot sign up yet.' },
        Ongoing:     { cls: 'hint-success', msg: '✅ Sign-up is currently open. Kabataan can select this barangay and submit the form.' },
        Completed:   { cls: 'hint-warning', msg: '⚠️ Profiling is done. Sign-up will be closed for this barangay.' },
        Cancelled:   { cls: 'hint-danger',  msg: '🚫 Profiling is cancelled. Sign-up will be closed for this barangay.' },
        Rescheduled: { cls: 'hint-warning', msg: '🔄 Profiling has been moved. Update the dates to reflect the new schedule.' },
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
            const dateStart  = getFormField('skkpFormDateStart');
            const dateExpiry = getFormField('skkpFormDateExpiry');
            const link       = getFormField('skkpFormLink');
            const status     = getFormField('skkpFormStatus') || 'Upcoming';

            if (!dateStart || !dateExpiry || !status) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }
            if (dateExpiry < dateStart) {
                showToast('Date Expiry must be on or after Date Start.', 'error');
                return;
            }
            if (link && !isValidUrl(link)) {
                showToast('Please enter a valid URL (e.g. https://example.com).', 'error');
                return;
            }

            const id = editIdInput.value ? parseInt(editIdInput.value, 10) : null;
            const payload = { date_start: dateStart, date_expiry: dateExpiry, link: link || null, status };

            try {
                formSaveBtn.disabled = true;
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
            setFormField('skkpFormDateStart',  sched.dateStart);
            setFormField('skkpFormDateExpiry', sched.dateExpiry);
            setFormField('skkpFormLink',       sched.link);
            setFormField('skkpFormStatus',     sched.status);
            updateStatusHint(sched.status);
            openModal(formModal);
        } else if (action === 'delete') {
            openModal(deleteModal);
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

    // ── Delete ──────────────────────────────────────────────────────────────
    if (deleteCancelBtn) deleteCancelBtn.addEventListener('click', () => closeModal(deleteModal));
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', async () => {
            try {
                deleteConfirmBtn.disabled = true;
                await apiFetch(`/api/schedule-kk-profiling/${activeId}`, { method: 'DELETE' });
                closeModal(deleteModal);
                showToast('Schedule deleted successfully!', 'success');
                await loadData();
            } catch (e) {
                showToast('Failed to delete schedule.', 'error');
            } finally {
                deleteConfirmBtn.disabled = false;
            }
        });
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
    if (prevBtn) prevBtn.addEventListener('click', () => { currentPage--; renderTable(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; renderTable(); });

    // ── Utility ─────────────────────────────────────────────────────────────
    function isValidUrl(str) { try { new URL(str); return true; } catch { return false; } }

    // ── Boot ────────────────────────────────────────────────────────────────
    loadData();
}
