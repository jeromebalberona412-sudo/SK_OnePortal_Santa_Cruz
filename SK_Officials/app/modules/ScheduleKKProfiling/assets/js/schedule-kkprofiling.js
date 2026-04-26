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
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── Main ───────────────────────────────────────────────────────────────────
function initializeScheduleKKProfiling() {
    let schedules = [];
    let nextId = 1;
    let activeId = null;
    let currentPage = 1;
    const perPage = 10;

    // Filter state
    let filterStatus = '';
    let filterSearch = '';

    // DOM refs
    const tbody       = document.getElementById('skkpTableBody');
    const searchInput = document.getElementById('skkpSearch');
    const statusFilter = document.getElementById('skkpStatusFilter');
    const createBtn   = document.getElementById('skkpCreateBtn');

    // Form modal
    const formModal    = document.getElementById('skkpFormModal');
    const formTitle    = document.getElementById('skkpFormModalTitle');
    const formSaveBtn  = document.getElementById('skkpFormSaveBtn');
    const formCancelBtn = document.getElementById('skkpFormCancelBtn');
    const editIdInput  = document.getElementById('skkpEditId');

    // View modal
    const viewModal = document.getElementById('skkpViewModal');

    // Delete modal
    const deleteModal      = document.getElementById('skkpDeleteModal');
    const deleteCancelBtn  = document.getElementById('skkpDeleteCancelBtn');
    const deleteConfirmBtn = document.getElementById('skkpDeleteConfirmBtn');

    // ── Render ──────────────────────────────────────────────────────────────
    function getFiltered() {
        return schedules.filter(s => {
            if (filterStatus && s.status !== filterStatus) return false;
            if (filterSearch) {
                const q = filterSearch.toLowerCase();
                const match = [s.dateStart, s.dateExpiry, s.link, s.remarks, s.status]
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
            td.colSpan = 8;
            td.textContent = 'No schedules found.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            page.forEach(s => {
                const tr = document.createElement('tr');
                const statusClass = s.status.toLowerCase().replace(/\s+/g, '-');

                const linkHtml = s.link
                    ? `<a href="${s.link}" target="_blank" rel="noopener noreferrer" class="skkp-link">Open Link</a>`
                    : '—';

                tr.innerHTML = `
                    <td>${formatDate(s.dateStart)}</td>
                    <td>${formatDate(s.dateExpiry)}</td>
                    <td>${formatTime(s.startTime)}</td>
                    <td>${formatTime(s.endTime)}</td>
                    <td>${linkHtml}</td>
                    <td><span class="skkp-status-badge ${statusClass}">${s.status}</span></td>
                    <td class="skkp-remarks-cell">${s.remarks || '—'}</td>
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

    [formModal, viewModal, deleteModal].forEach(modal => {
        if (!modal) return;
        modal.addEventListener('click', e => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close')) {
                closeModal(modal);
            }
        });
    });

    // ── Form helpers ────────────────────────────────────────────────────────
    function getFormField(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function setFormField(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
    }

    function clearForm() {
        ['skkpFormDateStart', 'skkpFormDateExpiry', 'skkpFormStartTime',
         'skkpFormEndTime', 'skkpFormLink', 'skkpFormRemarks'].forEach(id => setFormField(id, ''));
        setFormField('skkpFormStatus', 'Upcoming');
    }

    // ── Create ──────────────────────────────────────────────────────────────
    if (createBtn) {
        createBtn.addEventListener('click', () => {
            editIdInput.value = '';
            formTitle.textContent = 'Create Schedule';
            formSaveBtn.textContent = 'Save Schedule';
            clearForm();
            openModal(formModal);
        });
    }

    if (formCancelBtn) formCancelBtn.addEventListener('click', () => closeModal(formModal));

    if (formSaveBtn) {
        formSaveBtn.addEventListener('click', () => {
            const dateStart  = getFormField('skkpFormDateStart');
            const dateExpiry = getFormField('skkpFormDateExpiry');
            const startTime  = getFormField('skkpFormStartTime');
            const endTime    = getFormField('skkpFormEndTime');
            const link       = getFormField('skkpFormLink');
            const status     = getFormField('skkpFormStatus') || 'Upcoming';
            const remarks    = getFormField('skkpFormRemarks');

            // Validation
            if (!dateStart || !dateExpiry || !startTime || !endTime) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }

            if (dateExpiry < dateStart) {
                showToast('Date Expiry must be on or after Date Start.', 'error');
                return;
            }

            if (startTime >= endTime) {
                showToast('End Time must be after Start Time.', 'error');
                return;
            }

            if (link && !isValidUrl(link)) {
                showToast('Please enter a valid URL (e.g. https://example.com).', 'error');
                return;
            }

            const id = editIdInput.value ? parseInt(editIdInput.value, 10) : null;

            if (id) {
                const idx = schedules.findIndex(s => s.id === id);
                if (idx !== -1) {
                    schedules[idx] = { id, dateStart, dateExpiry, startTime, endTime, link, status, remarks };
                    showToast('Schedule updated successfully!', 'success');
                }
            } else {
                schedules.push({ id: nextId++, dateStart, dateExpiry, startTime, endTime, link, status, remarks });
                showToast('Schedule created successfully!', 'success');
            }

            closeModal(formModal);
            renderTable();
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
            openModal(viewModal);
        } else if (action === 'edit') {
            editIdInput.value = sched.id;
            formTitle.textContent = 'Edit Schedule';
            formSaveBtn.textContent = 'Update Schedule';
            setFormField('skkpFormDateStart',  sched.dateStart);
            setFormField('skkpFormDateExpiry', sched.dateExpiry);
            setFormField('skkpFormStartTime',  sched.startTime);
            setFormField('skkpFormEndTime',    sched.endTime);
            setFormField('skkpFormLink',       sched.link);
            setFormField('skkpFormStatus',     sched.status);
            setFormField('skkpFormRemarks',    sched.remarks);
            openModal(formModal);
        } else if (action === 'delete') {
            openModal(deleteModal);
        }
    });

    // ── View modal ──────────────────────────────────────────────────────────
    function populateViewModal(s) {
        const set = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val || '—';
        };

        set('skkpViewDateStart',  formatDate(s.dateStart));
        set('skkpViewDateExpiry', formatDate(s.dateExpiry));
        set('skkpViewStartTime',  formatTime(s.startTime));
        set('skkpViewEndTime',    formatTime(s.endTime));
        set('skkpViewRemarks',    s.remarks);

        // Link
        const linkEl = document.getElementById('skkpViewLink');
        if (linkEl) {
            if (s.link) {
                linkEl.innerHTML = `<a href="${s.link}" target="_blank" rel="noopener noreferrer" class="skkp-link">${s.link}</a>`;
            } else {
                linkEl.textContent = '—';
            }
        }

        // Status badge
        const statusEl = document.getElementById('skkpViewStatus');
        if (statusEl) {
            const cls = s.status.toLowerCase().replace(/\s+/g, '-');
            statusEl.innerHTML = `<span class="skkp-status-badge ${cls}">${s.status}</span>`;
        }
    }

    // ── Delete ──────────────────────────────────────────────────────────────
    if (deleteCancelBtn)  deleteCancelBtn.addEventListener('click', () => closeModal(deleteModal));
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', () => {
            schedules = schedules.filter(s => s.id !== activeId);
            closeModal(deleteModal);
            renderTable();
            showToast('Schedule deleted successfully!', 'success');
        });
    }

    // ── Filters ─────────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            filterSearch = searchInput.value.trim();
            currentPage = 1;
            renderTable();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            filterStatus = statusFilter.value;
            currentPage = 1;
            renderTable();
        });
    }

    // ── Pagination ──────────────────────────────────────────────────────────
    const prevBtn = document.getElementById('skkpPrevBtn');
    const nextBtn = document.getElementById('skkpNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => { currentPage--; renderTable(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; renderTable(); });

    // ── Utility ─────────────────────────────────────────────────────────────
    function isValidUrl(str) {
        try { new URL(str); return true; } catch { return false; }
    }

    // ── Initial render ──────────────────────────────────────────────────────
    renderTable();
}
