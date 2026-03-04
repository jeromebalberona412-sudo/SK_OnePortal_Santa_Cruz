document.addEventListener('DOMContentLoaded', () => {
    initializeCalendar();
});

function initializeCalendar() {
    const grid = document.getElementById('calendarGrid');
    const monthLabel = document.getElementById('calendarCurrentMonth');
    const navButtons = document.querySelectorAll('.calendar-nav-btn');

    if (!grid || !monthLabel) return;

    let current = new Date();
    current.setDate(1);

    const notes = {};

    function render() {
        grid.innerHTML = '';

        const year = current.getFullYear();
        const monthIndex = current.getMonth();

        const monthName = current.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        monthLabel.textContent = monthName;

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

        for (let i = 0; i < startWeekday; i++) {
            const emptyCell = document.createElement('div');
            grid.appendChild(emptyCell);
        }

        const today = new Date();
        const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;

        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            const dateKey = `${year}-${monthIndex}-${day}`;

            cell.className = 'calendar-day';

            if (dateKey === todayKey) {
                cell.classList.add('is-today');
            }

            const dayNumber = document.createElement('div');
            dayNumber.className = 'calendar-day-number';
            dayNumber.textContent = day;
            cell.appendChild(dayNumber);

            const preview = document.createElement('div');
            preview.className = 'calendar-day-notes-preview';
            preview.textContent = notes[dateKey] || '';
            cell.appendChild(preview);

            if (notes[dateKey]) {
                cell.classList.add('has-notes');
            }

            const addLabel = document.createElement('div');
            addLabel.className = 'calendar-day-add';
            addLabel.textContent = notes[dateKey] ? 'Edit note' : 'Add note';
            cell.appendChild(addLabel);

            cell.addEventListener('click', () => openEditor(dateKey, day, monthName));

            grid.appendChild(cell);
        }
    }

    navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const dir = btn.getAttribute('data-direction');
            if (dir === 'prev') {
                current.setMonth(current.getMonth() - 1);
            } else {
                current.setMonth(current.getMonth() + 1);
            }
            render();
        });
    });

    function openEditor(dateKey, day, monthLabelText) {
        let backdrop = document.querySelector('.calendar-modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'calendar-modal-backdrop';
            backdrop.innerHTML = `
                <div class="calendar-modal">
                    <div class="calendar-modal-header">
                        <h2 class="calendar-modal-title"></h2>
                        <button class="calendar-modal-close" type="button">&times;</button>
                    </div>
                    <textarea placeholder="Write a note for this day..."></textarea>
                    <div class="calendar-modal-actions">
                        <button type="button" class="btn-secondary calendar-modal-cancel">Cancel</button>
                        <button type="button" class="btn-primary calendar-modal-save">Save note</button>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const titleEl = backdrop.querySelector('.calendar-modal-title');
        const textarea = backdrop.querySelector('textarea');
        const closeBtn = backdrop.querySelector('.calendar-modal-close');
        const cancelBtn = backdrop.querySelector('.calendar-modal-cancel');
        const saveBtn = backdrop.querySelector('.calendar-modal-save');

        titleEl.textContent = `Notes for ${monthLabelText} ${day}`;
        textarea.value = notes[dateKey] || '';

        function hide() {
            backdrop.classList.remove('show');
            closeBtn.removeEventListener('click', onClose);
            cancelBtn.removeEventListener('click', onClose);
            saveBtn.removeEventListener('click', onSave);
            backdrop.removeEventListener('click', onBackdrop);
        }

        function onClose() {
            hide();
        }

        function onBackdrop(e) {
            if (e.target === backdrop) {
                hide();
            }
        }

        function onSave() {
            const value = textarea.value.trim();
            if (value) {
                notes[dateKey] = value;
            } else {
                delete notes[dateKey];
            }
            hide();
            render();
        }

        closeBtn.addEventListener('click', onClose);
        cancelBtn.addEventListener('click', onClose);
        saveBtn.addEventListener('click', onSave);
        backdrop.addEventListener('click', onBackdrop);

        backdrop.classList.add('show');
        textarea.focus();
    }

    render();
}

