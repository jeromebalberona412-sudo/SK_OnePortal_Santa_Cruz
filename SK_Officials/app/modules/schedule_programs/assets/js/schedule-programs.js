document.addEventListener('DOMContentLoaded', () => {
    initializeSchedulePrograms();
    initializeCommitteeCards();
});

// ── Committee Cards Handler ────────────────────────────────────────────────
function initializeCommitteeCards() {
    const committeeCards = document.querySelectorAll('.committee-card');
    
    committeeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Check if card is disabled
            if (this.classList.contains('committee-disabled')) {
                showToast('This committee is coming soon!', 'error');
                return;
            }
            
            const committee = this.getAttribute('data-committee');
            
            // Only sports is active for now
            if (committee === 'sports') {
                window.location.href = '/sports-application-form';
            }
        });
    });
}

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show' + (type === 'error' ? ' app-toast-error' : '');
    toast.innerHTML = '<span>' + (type === 'error' ? '✕' : '✓') + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function formatTime(t) {
    if (!t) return '—';
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`;
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── Main ───────────────────────────────────────────────────────────────────
function initializeSchedulePrograms() {
    let schedules = [];
    let nextId = 1;
    let editingId = null;
    let deleteTargetId = null;
    let currentPage = 1;
    const perPage = 10;
    let filterStatus = '';
    let filterSearch = '';

    // DOM refs — modal
    const formOverlay    = document.getElementById('spFormOverlay');
    const modalTitle     = document.getElementById('spModalTitle');
    const modalClose     = document.getElementById('spModalClose');
    const hiddenId       = document.getElementById('spHiddenId');
    const fProgram       = document.getElementById('spProgram');
    const fActivityType  = document.getElementById('spActivityType');
    const fDate          = document.getElementById('spDate');
    const fStartTime     = document.getElementById('spStartTime');
    const fEndTime       = document.getElementById('spEndTime');
    const fVenue         = document.getElementById('spVenue');
    const fOfficials     = document.getElementById('spOfficials');
    const fParticipants  = document.getElementById('spParticipants');
    const fStatus        = document.getElementById('spStatus');
    const btnSave        = document.getElementById('spBtnSave');
    const btnClear       = document.getElementById('spBtnClear');
    const addBtn         = document.getElementById('spAddBtn');

    // DOM refs — table / filters
    const tbody          = document.getElementById('spTableBody');
    const searchInput    = document.getElementById('spSearch');
    const statusFilter   = document.getElementById('spStatusFilter');

    // DOM refs — delete modal
    const deleteOverlay    = document.getElementById('spDeleteOverlay');
    const btnDeleteConfirm = document.getElementById('spDeleteConfirm');

    // ── Modal open/close ────────────────────────────────────────────────────
    function openFormModal() { formOverlay.style.display = 'flex'; }
    function closeFormModal() { formOverlay.style.display = 'none'; }

    if (addBtn) addBtn.addEventListener('click', () => {
        clearForm();
        modalTitle.textContent = 'Add Schedule Program';
        btnSave.textContent = 'Save';
        openFormModal();
    });

    if (modalClose) modalClose.addEventListener('click', closeFormModal);

    formOverlay.addEventListener('click', e => {
        if (e.target === formOverlay) closeFormModal();
    });

    // ── Render ──────────────────────────────────────────────────────────────
    function getFiltered() {
        return schedules.filter(s => {
            if (filterStatus && s.status !== filterStatus) return false;
            if (filterSearch) {
                const q = filterSearch.toLowerCase();
                const haystack = [s.programName, s.activityType, s.venue, s.status]
                    .map(v => (v || '').toLowerCase()).join(' ');
                if (!haystack.includes(q)) return false;
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
            tr.innerHTML = `<td colspan="7">No schedules found.</td>`;
            tbody.appendChild(tr);
        } else {
            page.forEach(s => {
                const statusCls = s.status.toLowerCase();
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${s.programName || '—'}</td>
                    <td>${s.activityType || '—'}</td>
                    <td>${formatDate(s.date)}</td>
                    <td>${formatTime(s.startTime)}${s.endTime ? ' – ' + formatTime(s.endTime) : ''}</td>
                    <td>${s.venue || '—'}</td>
                    <td><span class="sp-status-badge ${statusCls}">${s.status}</span></td>
                    <td>
                        <div class="sp-actions">
                            <button class="sp-btn sp-btn-edit"   data-action="edit"   data-id="${s.id}">Edit</button>
                            <button class="sp-btn sp-btn-delete" data-action="delete" data-id="${s.id}">Delete</button>
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
        document.getElementById('spStatUpcoming').textContent    = counts.Upcoming;
        document.getElementById('spStatOngoing').textContent     = counts.Ongoing;
        document.getElementById('spStatCompleted').textContent   = counts.Completed;
        document.getElementById('spStatCancelled').textContent   = counts.Cancelled;
        document.getElementById('spStatRescheduled').textContent = counts.Rescheduled;
    }

    function updatePagination(total, start, end, totalPages) {
        const info = document.getElementById('spPaginationInfo');
        if (info) info.textContent = total === 0 ? 'No records found' : `Showing ${start}–${end} of ${total} records`;

        const prevBtn  = document.getElementById('spPrevBtn');
        const nextBtn  = document.getElementById('spNextBtn');
        const pageNums = document.getElementById('spPageNumbers');

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

    // ── Form helpers ────────────────────────────────────────────────────────
    function getVal(el) { return el ? el.value.trim() : ''; }

    function clearForm() {
        hiddenId.value      = '';
        fProgram.value      = '';
        fActivityType.value = '';
        fDate.value         = '';
        fStartTime.value    = '';
        fEndTime.value      = '';
        fVenue.value        = '';
        fOfficials.value    = '';
        fParticipants.value = '';
        fStatus.value       = 'Upcoming';
        editingId = null;
    }

    function populateForm(s) {
        hiddenId.value       = s.id;
        fProgram.value       = s.programName || '';
        fActivityType.value  = s.activityType || '';
        fDate.value          = s.date || '';
        fStartTime.value     = s.startTime || '';
        fEndTime.value       = s.endTime || '';
        fVenue.value         = s.venue || '';
        fOfficials.value     = s.officials || '';
        fParticipants.value  = s.participants || '';
        fStatus.value        = s.status || 'Upcoming';
        editingId = s.id;
    }

    // ── Save ────────────────────────────────────────────────────────────────
    if (btnSave) {
        btnSave.addEventListener('click', () => {
            const programName  = getVal(fProgram);
            const activityType = getVal(fActivityType);
            const date         = getVal(fDate);
            const startTime    = getVal(fStartTime);
            const endTime      = getVal(fEndTime);
            const venue        = getVal(fVenue);
            const officials    = getVal(fOfficials);
            const participants = getVal(fParticipants);
            const status       = getVal(fStatus) || 'Upcoming';

            if (!programName || !activityType || !date || !startTime || !venue) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }

            if (endTime && endTime < startTime) {
                showToast('End time must be after start time.', 'error');
                return;
            }

            const id = hiddenId.value ? parseInt(hiddenId.value, 10) : null;

            if (id) {
                const idx = schedules.findIndex(s => s.id === id);
                if (idx !== -1) {
                    schedules[idx] = { id, programName, activityType, date, startTime, endTime, venue, officials, participants, status };
                    showToast('Schedule updated successfully!');
                }
            } else {
                schedules.push({ id: nextId++, programName, activityType, date, startTime, endTime, venue, officials, participants, status });
                showToast('Schedule saved successfully!');
            }

            closeFormModal();
            clearForm();
            renderTable();
        });
    }

    if (btnClear) btnClear.addEventListener('click', clearForm);

    // ── Table actions ───────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        const id     = parseInt(btn.getAttribute('data-id'), 10);
        const sched  = schedules.find(s => s.id === id);
        if (!sched) return;

        if (action === 'edit') {
            populateForm(sched);
            modalTitle.textContent = 'Edit Schedule Program';
            btnSave.textContent = 'Update';
            openFormModal();
        } else if (action === 'delete') {
            deleteTargetId = id;
            deleteOverlay.style.display = 'flex';
        }
    });

    // ── Delete modal ────────────────────────────────────────────────────────
    [document.getElementById('spDeleteCancel'), document.getElementById('spDeleteCancelFooter')].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            deleteOverlay.style.display = 'none';
            deleteTargetId = null;
        });
    });

    if (btnDeleteConfirm) btnDeleteConfirm.addEventListener('click', () => {
        schedules = schedules.filter(s => s.id !== deleteTargetId);
        deleteOverlay.style.display = 'none';
        deleteTargetId = null;
        renderTable();
        showToast('Schedule deleted successfully!');
    });

    deleteOverlay.addEventListener('click', e => {
        if (e.target === deleteOverlay) {
            deleteOverlay.style.display = 'none';
            deleteTargetId = null;
        }
    });

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
    const prevBtn = document.getElementById('spPrevBtn');
    const nextBtn = document.getElementById('spNextBtn');
    if (prevBtn) prevBtn.addEventListener('click', () => { currentPage--; renderTable(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { currentPage++; renderTable(); });

    // ── Initial render ──────────────────────────────────────────────────────
    renderTable();
}
