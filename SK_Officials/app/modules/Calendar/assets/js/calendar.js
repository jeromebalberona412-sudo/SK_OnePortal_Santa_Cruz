document.addEventListener('DOMContentLoaded', () => {
    initializeCalendar();
});

function showConfirm(options) {
    return new Promise((resolve) => {
        const { title, message, confirmText = 'OK', cancelText = 'Cancel', confirmClass = 'confirm-primary', theme = 'default' } = options;
        const isAlert = cancelText === '';
        let overlay = document.getElementById('calendar-confirm-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'calendar-confirm-overlay';
            overlay.className = 'calendar-confirm-overlay';
            overlay.innerHTML = `
                <div class="calendar-confirm-modal">
                    <div class="calendar-confirm-header"></div>
                    <div class="calendar-confirm-body"></div>
                    <div class="calendar-confirm-actions">
                        <button type="button" class="calendar-confirm-cancel"></button>
                        <button type="button" class="calendar-confirm-ok"></button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        const headerEl = overlay.querySelector('.calendar-confirm-header');
        const bodyEl = overlay.querySelector('.calendar-confirm-body');
        const cancelBtn = overlay.querySelector('.calendar-confirm-cancel');
        const okBtn = overlay.querySelector('.calendar-confirm-ok');

        headerEl.textContent = title;
        bodyEl.textContent = message;
        cancelBtn.textContent = cancelText;
        cancelBtn.style.display = isAlert ? 'none' : '';
        okBtn.textContent = confirmText;
        okBtn.className = 'calendar-confirm-ok ' + confirmClass;
        overlay.className = 'calendar-confirm-overlay theme-' + theme;

        const cleanup = () => {
            overlay.classList.remove('show');
            cancelBtn.onclick = null;
            okBtn.onclick = null;
            overlay.onclick = null;
        };

        cancelBtn.onclick = () => { cleanup(); resolve(false); };
        okBtn.onclick = () => { cleanup(); resolve(true); };
        overlay.onclick = (e) => {
            if (e.target === overlay) {
                cleanup();
                resolve(isAlert ? true : false);
            }
        };

        overlay.classList.add('show');
    });
}

function showToast(message, type) {
    const existing = document.querySelector('.app-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'app-toast app-toast-show app-toast-' + (type || 'success');

    let icon = '✓';
    if (type === 'error') {
        icon = '✕';
    }

    toast.innerHTML = '<span class="app-toast-icon">' + icon + '</span> ' + message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('app-toast-show');
        toast.classList.add('app-toast-hide');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

const MIN_YEAR = 1991;

function initializeCalendar() {
    const grid = document.getElementById('calendarGrid');
    const monthLabel = document.getElementById('calendarMonthLabel');
    const prevBtn = document.getElementById('calendarPrevBtn');
    const nextBtn = document.getElementById('calendarNextBtn');
    const jumpBtn = document.getElementById('calendarJumpBtn');

    if (!grid || !monthLabel || !prevBtn || !nextBtn || !jumpBtn) return;

    let current = new Date();
    current.setDate(1);
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const notes = {};

    // Function to check if a date is in the past
    function isDateInPast(year, month, day) {
        const checkDate = new Date(year, month, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Set to start of day for accurate comparison
        checkDate.setHours(0, 0, 0, 0);
        return checkDate < today;
    }

    // Function to check if a date is today or in the future
    function isDateCurrentOrFuture(year, month, day) {
        return !isDateInPast(year, month, day);
    }

    prevBtn.addEventListener('click', () => {
        current.setMonth(current.getMonth() - 1);
        render();
    });
    nextBtn.addEventListener('click', () => {
        current.setMonth(current.getMonth() + 1);
        render();
    });
    jumpBtn.addEventListener('click', () => openJumpModal());

    function render() {
        grid.innerHTML = '';
        const year = current.getFullYear();
        const monthIndex = current.getMonth();

        monthLabel.textContent = `${monthNames[monthIndex]} ${year}`;

        const firstDay = new Date(year, monthIndex, 1);
        const startWeekday = firstDay.getDay();
        const daysInMonth = new Date(year, monthIndex + 1, 0).getDate();

        const weekdayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        weekdayNames.forEach(name => {
            const header = document.createElement('div');
            header.className = 'calendar-day-header';
            header.textContent = name;
            grid.appendChild(header);
        });

        for (let i = 0; i < startWeekday; i++) grid.appendChild(document.createElement('div'));

        const today = new Date();
        const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
        const monthName = monthNames[monthIndex];

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            const dateKey = `${year}-${monthIndex}-${day}`;
            const dayOfWeek = new Date(year, monthIndex, day).getDay();
            const isSaturday = dayOfWeek === 6;
            const note = notes[dateKey];
            const hasNote = note && (note.title || note.content);
            const isPastDate = isDateInPast(year, monthIndex, day);
            const canEdit = isDateCurrentOrFuture(year, monthIndex, day);

            cell.className = 'calendar-day';
            if (dateKey === todayKey) cell.classList.add('is-today');
            if (hasNote) cell.classList.add('has-notes');
            if (isPastDate) cell.classList.add('is-past');


            const dayNumber = document.createElement('div');
            dayNumber.className = 'calendar-day-number';
            dayNumber.textContent = day;
            cell.appendChild(dayNumber);

            const preview = document.createElement('div');
            preview.className = 'calendar-day-notes-preview';
            preview.textContent = hasNote ? (note.title || note.content || '').slice(0, 40) : '';
            cell.appendChild(preview);

            const addLabel = document.createElement('div');
            addLabel.className = 'calendar-day-add';
            if (isPastDate && hasNote) {
                addLabel.textContent = 'View note';
            } else if (isPastDate) {
                addLabel.textContent = 'Past date';
            } else if (hasNote) {
                addLabel.textContent = 'Edit note';
            } else {
                addLabel.textContent = 'Add note';
            }
            cell.appendChild(addLabel);

            cell.addEventListener('click', (e) => {
                if (!e.target.closest('.calendar-day-cell-actions')) openEditor(dateKey, day, monthName);
            });
            grid.appendChild(cell);
        }
    }

    function openJumpModal() {
        let overlay = document.getElementById('calendar-jump-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'calendar-jump-overlay';
            overlay.className = 'calendar-jump-overlay';
            overlay.innerHTML = `
                <div class="calendar-jump-modal">
                    <h3 class="calendar-jump-title">Jump to date</h3>
                    <p class="calendar-jump-display"></p>
                    <div class="calendar-jump-columns">
                        <div class="calendar-jump-col" data-type="month">
                            <div class="calendar-jump-col-inner"></div>
                        </div>
                        <div class="calendar-jump-col" data-type="day">
                            <div class="calendar-jump-col-inner"></div>
                        </div>
                        <div class="calendar-jump-col" data-type="year">
                            <div class="calendar-jump-col-inner"></div>
                        </div>
                    </div>
                    <div class="calendar-jump-actions">
                        <button type="button" class="calendar-jump-cancel">Cancel</button>
                        <button type="button" class="calendar-jump-ok">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        let selMonth = current.getMonth();
        let selDay = Math.min(current.getDate(), 28);
        let selYear = current.getFullYear();

        function clampDay() {
            const maxDay = new Date(selYear, selMonth + 1, 0).getDate();
            selDay = Math.min(selDay, maxDay);
        }

        const displayEl = overlay.querySelector('.calendar-jump-display');
        const monthCol = overlay.querySelector('.calendar-jump-col[data-type="month"] .calendar-jump-col-inner');
        const dayCol = overlay.querySelector('.calendar-jump-col[data-type="day"] .calendar-jump-col-inner');
        const yearCol = overlay.querySelector('.calendar-jump-col[data-type="year"] .calendar-jump-col-inner');

        function updateDisplay() {
            const d = new Date(selYear, selMonth, selDay);
            displayEl.textContent = d.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        }

        function renderColumns() {
            clampDay();
            monthCol.innerHTML = '';
            for (let i = 0; i < 12; i++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (i === selMonth ? ' selected' : '');
                item.textContent = monthNamesShort[i];
                item.dataset.value = i;
                item.addEventListener('click', () => {
                    selMonth = i;
                    clampDay();
                    renderColumns();
                    updateDisplay();
                });
                monthCol.appendChild(item);
            }

            dayCol.innerHTML = '';
            const daysInMonth = new Date(selYear, selMonth + 1, 0).getDate();
            for (let d = 1; d <= daysInMonth; d++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (d === selDay ? ' selected' : '');
                item.textContent = String(d).padStart(2, '0');
                item.dataset.value = d;
                item.addEventListener('click', () => {
                    selDay = d;
                    renderColumns();
                    updateDisplay();
                });
                dayCol.appendChild(item);
            }

            yearCol.innerHTML = '';
            for (let y = MIN_YEAR; y <= 2100; y++) {
                const item = document.createElement('div');
                item.className = 'calendar-jump-item' + (y === selYear ? ' selected' : '');
                item.textContent = y;
                item.dataset.value = y;
                item.addEventListener('click', () => {
                    selYear = y;
                    clampDay();
                    renderColumns();
                    updateDisplay();
                });
                yearCol.appendChild(item);
            }

            monthCol.parentElement.scrollTop = selMonth * 36;
            dayCol.parentElement.scrollTop = (selDay - 1) * 36;
            yearCol.parentElement.scrollTop = (selYear - MIN_YEAR) * 36;
        }

        function hide() {
            overlay.classList.remove('show');
            overlay.querySelector('.calendar-jump-cancel').removeEventListener('click', onCancel);
            overlay.querySelector('.calendar-jump-ok').removeEventListener('click', onOk);
            overlay.removeEventListener('click', onBackdrop);
        }

        function onOk() {
            current.setFullYear(selYear);
            current.setMonth(selMonth);
            current.setDate(1);
            hide();
            render();
        }

        function onCancel() { hide(); }
        function onBackdrop(e) { if (e.target === overlay) hide(); }

        renderColumns();
        updateDisplay();

        overlay.querySelector('.calendar-jump-cancel').addEventListener('click', onCancel);
        overlay.querySelector('.calendar-jump-ok').addEventListener('click', onOk);
        overlay.addEventListener('click', onBackdrop);

        overlay.classList.add('show');
    }

    let backdrop, modal, titleInput, contentArea, closeBtn, cancelBtn, saveBtn, editBtn, delBtn, toggleBtn;
    let dateKey, isEditMode, originalNote;

    function switchToViewMode() {
        isEditMode = false;
        const note = notes[dateKey] || {};
        titleInput.value = note.title || '';
        titleInput.readOnly = true;
        contentArea.value = note.content || '';
        contentArea.readOnly = true;
        cancelBtn.style.display = 'none';
        saveBtn.style.display = 'none';
        editBtn.style.display = '';
        delBtn.style.display = '';
    }

    function switchToEditMode() {
        isEditMode = true;
        const note = notes[dateKey] || {};
        originalNote = { title: note.title || '', content: note.content || '' };
        titleInput.readOnly = false;
        contentArea.readOnly = false;
        cancelBtn.style.display = '';
        saveBtn.style.display = '';
        editBtn.style.display = 'none';
        delBtn.style.display = 'none';
    }

    function openEditor(dateKeyParam, day, monthLabelText) {
        dateKey = dateKeyParam;
        const note = notes[dateKey];
        const hasNote = note && (note.title || note.content);

        // Parse the dateKey to get year, month, day
        const [year, month, dayNum] = dateKey.split('-').map(Number);
        const isPastDate = isDateInPast(year, month, dayNum);
        const canEdit = isDateCurrentOrFuture(year, month, dayNum);

        let backdropEl = document.querySelector('.calendar-modal-backdrop');
        if (!backdropEl) {
            backdropEl = document.createElement('div');
            backdropEl.className = 'calendar-modal-backdrop';
            backdropEl.innerHTML = `
                <div class="calendar-modal">
                    <div class="calendar-modal-header">
                        <h2 class="calendar-modal-title"></h2>
                        <div class="calendar-modal-window-controls">
                            <button type="button" class="modal-toggle-btn" aria-label="Maximize">□</button>
                            <button type="button" class="calendar-modal-close" aria-label="Close">&times;</button>
                        </div>
                    </div>
                    <div class="calendar-modal-body">
                        <label class="calendar-note-label">Title</label>
                        <input type="text" class="calendar-note-title-input" placeholder="Note title..." />
                        <label class="calendar-note-label">Content</label>
                        <textarea class="calendar-note-content" placeholder="Write your note..."></textarea>
                        <div class="calendar-modal-actions">
                            <button type="button" class="btn-secondary calendar-modal-cancel" style="display:none">Cancel</button>
                            <button type="button" class="btn-primary calendar-modal-save" style="display:none">Save</button>
                            <button type="button" class="modal-action-btn edit" style="display:none">Edit</button>
                            <button type="button" class="modal-action-btn delete" style="display:none">Delete</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(backdropEl);
        }

        backdrop = backdropEl;
        modal = backdrop.querySelector('.calendar-modal');
        const titleHeading = modal.querySelector('.calendar-modal-title');
        titleHeading.textContent = `Notes for ${monthLabelText} ${day}`;
        titleInput = modal.querySelector('.calendar-note-title-input');
        contentArea = modal.querySelector('.calendar-note-content');
        closeBtn = modal.querySelector('.calendar-modal-close');
        cancelBtn = modal.querySelector('.calendar-modal-cancel');
        saveBtn = modal.querySelector('.calendar-modal-save');
        editBtn = modal.querySelector('.modal-action-btn.edit');
        delBtn = modal.querySelector('.modal-action-btn.delete');
        toggleBtn = modal.querySelector('.modal-toggle-btn');

        titleInput.value = note ? (note.title || '') : '';
        contentArea.value = note ? (note.content || '') : '';
        originalNote = note ? { title: note.title || '', content: note.content || '' } : { title: '', content: '' };

        if (hasNote) {
            if (canEdit) {
                // Can edit existing note
                titleInput.readOnly = true;
                contentArea.readOnly = true;
                isEditMode = false;
                cancelBtn.style.display = 'none';
                saveBtn.style.display = 'none';
                editBtn.style.display = '';
                delBtn.style.display = '';
            } else {
                // Past date - can only view
                titleInput.readOnly = true;
                contentArea.readOnly = true;
                isEditMode = false;
                cancelBtn.style.display = 'none';
                saveBtn.style.display = 'none';
                editBtn.style.display = 'none';
                delBtn.style.display = 'none';
            }
        } else {
            if (canEdit) {
                // Can add new note
                titleInput.readOnly = false;
                contentArea.readOnly = false;
                isEditMode = true;
                cancelBtn.style.display = '';
                saveBtn.style.display = '';
                editBtn.style.display = 'none';
                delBtn.style.display = 'none';
            } else {
                // Past date - cannot add note
                showToast('Cannot add notes to past dates', 'error');
                return;
            }
        }

        backdrop.classList.remove('modal-maximized');
        modal.classList.remove('modal-maximized');
        toggleBtn.textContent = '□';

        function hide() {
            backdrop.classList.remove('show', 'modal-maximized');
            modal.classList.remove('modal-maximized');
            render();
            closeBtn.removeEventListener('click', onClose);
            cancelBtn.removeEventListener('click', onCancel);
            saveBtn.removeEventListener('click', onSave);
            editBtn.removeEventListener('click', onEditClick);
            delBtn.removeEventListener('click', onDelete);
            toggleBtn.removeEventListener('click', onToggle);
            backdrop.removeEventListener('click', onBackdrop);
        }

        function onClose() {
            if (isEditMode) {
                const t = titleInput.value.trim();
                const c = contentArea.value.trim();
                if (t !== originalNote.title || c !== originalNote.content) {
                    showConfirm({ title: 'Unsaved Changes', message: 'Discard unsaved changes?', confirmText: 'Discard', cancelText: 'Stay', theme: 'edit' }).then((ok) => { if (ok) hide(); });
                    return;
                }
            }
            hide();
        }

        function onBackdrop(e) {
            if (e.target === backdrop) onClose();
        }

        async function onEditClick() {
            // Re-check if editing is still allowed
            const [year, month, dayNum] = dateKey.split('-').map(Number);
            const canEdit = isDateCurrentOrFuture(year, month, dayNum);

            if (!canEdit) {
                showToast('Cannot edit notes on past dates', 'error');
                return;
            }

            const ok = await showConfirm({ title: 'Edit Note', message: 'Edit this note?', confirmText: 'OK', cancelText: 'Cancel', confirmClass: 'confirm-edit', theme: 'edit' });
            if (ok) { switchToEditMode(); titleInput.focus(); showToast('Edit successful'); }
        }

        async function onDelete() {
            // Re-check if deletion is still allowed
            const [year, month, dayNum] = dateKey.split('-').map(Number);
            const canEdit = isDateCurrentOrFuture(year, month, dayNum);

            if (!canEdit) {
                showToast('Cannot delete notes on past dates', 'error');
                return;
            }

            const ok = await showConfirm({ title: 'Delete Note', message: 'Delete this note?', confirmText: 'Delete', cancelText: 'Cancel', confirmClass: 'confirm-danger', theme: 'delete' });
            if (ok) { delete notes[dateKey]; hide(); showToast('Delete successful'); }
        }

        async function onSave() {
            const title = titleInput.value.trim();
            const content = contentArea.value.trim();
            const hadNote = !!notes[dateKey];
            if (hadNote) {
                const ok = await showConfirm({ title: 'Save Changes', message: 'Are you sure you want to save changes?', confirmText: 'Save', cancelText: 'Cancel', confirmClass: 'confirm-edit', theme: 'edit' });
                if (!ok) return;
            }
            if (title || content) notes[dateKey] = { title, content };
            else delete notes[dateKey];
            originalNote = { title, content };
            if (notes[dateKey]) { switchToViewMode(); showToast('Save changes successful'); }
            else { hide(); showToast('Note removed'); }
        }

        function onCancel() {
            titleInput.value = originalNote.title;
            contentArea.value = originalNote.content;
            if (notes[dateKey]) switchToViewMode();
            else hide();
        }

        function onToggle() {
            const isMax = backdrop.classList.toggle('modal-maximized');
            modal.classList.toggle('modal-maximized', isMax);
            toggleBtn.textContent = isMax ? '⧉' : '□';
        }

        closeBtn.addEventListener('click', onClose);
        cancelBtn.addEventListener('click', onCancel);
        saveBtn.addEventListener('click', onSave);
        editBtn.addEventListener('click', onEditClick);
        delBtn.addEventListener('click', onDelete);
        toggleBtn.addEventListener('click', onToggle);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
        (titleInput.value ? contentArea : titleInput).focus();
    }

    render();
}
