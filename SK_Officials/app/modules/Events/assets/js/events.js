document.addEventListener('DOMContentLoaded', () => {
    initializeEventsUI();
});

function initializeEventsUI() {
    const list = document.getElementById('eventList');

    const searchInput = document.getElementById('eventSearch');
    const programFilter = document.getElementById('eventProgramFilter');
    const dateFilter = document.getElementById('eventDateFilter');

    const addBtn = document.getElementById('addEventBtn');
    const modal = document.getElementById('eventModal');
    const nameInput = document.getElementById('eventNameInput');
    const programInput = document.getElementById('eventProgramInput');
    const dateInput = document.getElementById('eventDateInput');
    const timeHourInput = document.getElementById('eventTimeHour');
    const timeMinuteInput = document.getElementById('eventTimeMinute');
    const timePeriodInput = document.getElementById('eventTimePeriod');
    const venueInput = document.getElementById('eventVenueInput');
    const participantsInput = document.getElementById('eventParticipantsInput');
    const saveBtn = document.getElementById('eventSaveBtn');
    const otherEventField = document.getElementById('otherEventField');
    const otherEventInput = document.getElementById('otherEventInput');
    const successModal = document.getElementById('eventSuccessModal');
    const successMessage = document.getElementById('eventSuccessMessage');
    const viewModal = document.getElementById('eventViewModal');
    const viewEventName = document.getElementById('viewEventName');
    const viewEventProgram = document.getElementById('viewEventProgram');
    const viewEventDate = document.getElementById('viewEventDate');
    const viewEventTime = document.getElementById('viewEventTime');
    const viewEventVenue = document.getElementById('viewEventVenue');
    const viewEventParticipants = document.getElementById('viewEventParticipants');

    // Modal maximize/minimize (restore) controls
    function resetModalMaximize(backdropEl) {
        if (!backdropEl) return;
        backdropEl.classList.remove('modal-maximized');
        const box = backdropEl.querySelector('.modal-box');
        if (box) box.classList.remove('modal-maximized');
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        if (toggleBtn) toggleBtn.textContent = '□';
    }

    function wireModalToggle(backdropEl) {
        if (!backdropEl) return;
        const toggleBtn = backdropEl.querySelector('[data-modal-toggle]');
        const box = backdropEl.querySelector('.modal-box');
        if (!toggleBtn || !box) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const willMaximize = !box.classList.contains('modal-maximized');
            backdropEl.classList.toggle('modal-maximized', willMaximize);
            box.classList.toggle('modal-maximized', willMaximize);
            toggleBtn.textContent = willMaximize ? '⧉' : '□';
        });
    }

    if (!list) return;

    // Start empty; events appear only after "Schedule Event"
    const events = [];
    let editingIndex = -1;

    let currentQuery = '';
    let currentProgram = '';
    let currentDate = '';

    function formatSchedule(date, time) {
        const opts = { month: 'short', day: '2-digit', year: 'numeric' };
        const datePart = (date || '').trim();
        const timePart = (time || '').trim();
        if (!datePart) return '';
        let isoTime = '12:00';
        const twelve = timePart.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (twelve) {
            let h = parseInt(twelve[1], 10);
            const m = twelve[2];
            const ap = twelve[3].toUpperCase();
            if (ap === 'PM' && h !== 12) h += 12;
            if (ap === 'AM' && h === 12) h = 0;
            isoTime = `${String(h).padStart(2, '0')}:${m}`;
        } else if (/^\d{1,2}:\d{2}$/.test(timePart)) {
            isoTime = timePart.length === 5 ? timePart : timePart.padStart(5, '0');
        }
        const d = new Date(`${datePart}T${isoTime}`);
        if (Number.isNaN(d.getTime())) return datePart;
        const label = d.toLocaleDateString(undefined, opts);
        const t = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
        return `${label} · ${t}`;
    }

    function initEventTimeSelects() {
        if (!timeHourInput || !timeMinuteInput) return;
        if (!timeHourInput.querySelector('option[value="1"]')) {
            for (let h = 1; h <= 12; h += 1) {
                const o = document.createElement('option');
                o.value = String(h);
                o.textContent = String(h);
                timeHourInput.appendChild(o);
            }
        }
        if (!timeMinuteInput.querySelector('option[value="00"]')) {
            for (let m = 0; m < 60; m += 1) {
                const o = document.createElement('option');
                const v = String(m).padStart(2, '0');
                o.value = v;
                o.textContent = v;
                timeMinuteInput.appendChild(o);
            }
        }
    }

    function parseTimeToParts(timeStr) {
        const s = (timeStr || '').trim();
        if (!s) return { hour: '', minute: '', period: '' };
        const twelve = s.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (twelve) {
            return {
                hour: String(parseInt(twelve[1], 10)),
                minute: twelve[2],
                period: twelve[3].toUpperCase(),
            };
        }
        const twentyfour = s.match(/^(\d{1,2}):(\d{2})$/);
        if (twentyfour) {
            let h = parseInt(twentyfour[1], 10);
            const m = twentyfour[2].padStart(2, '0');
            const period = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 === 0 ? 12 : h % 12;
            return { hour: String(h12), minute: m, period };
        }
        return { hour: '', minute: '', period: '' };
    }

    function getEventTimeFromSelects() {
        const h = timeHourInput?.value || '';
        const m = timeMinuteInput?.value || '';
        const p = timePeriodInput?.value || '';
        if (!h || !m || !p) return '';
        return `${h}:${m} ${p}`;
    }

    function setEventTimeSelects(timeStr) {
        const parts = parseTimeToParts(timeStr);
        if (timeHourInput) timeHourInput.value = parts.hour || '';
        if (timeMinuteInput) timeMinuteInput.value = parts.minute || '';
        if (timePeriodInput) timePeriodInput.value = parts.period || '';
    }

    function clearEventTimeSelects() {
        setEventTimeSelects('');
    }

    function render() {
        list.innerHTML = '';
        const filtered = events.filter((e) => {
            const matchesSearch =
                !currentQuery ||
                e.name.toLowerCase().includes(currentQuery) ||
                e.program.toLowerCase().includes(currentQuery) ||
                e.venue.toLowerCase().includes(currentQuery);

            const matchesProgram =
                !currentProgram ||
                e.program.toLowerCase().includes(currentProgram);

            const matchesDate =
                !currentDate || e.date === currentDate;

            return matchesSearch && matchesProgram && matchesDate;
        });

        if (filtered.length === 0) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="8" class="empty-state">No events scheduled yet.</td>';
            list.appendChild(empty);
        } else {
            filtered.forEach((e) => {
                const sourceIndex = events.indexOf(e);
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${e.name}</td>
                    <td>${e.program}</td>
                    <td>${e.date}</td>
                    <td>${e.time}</td>
                    <td>${e.venue}</td>
                    <td>${e.expectedParticipants}</td>
                    <td><span class="status-pill ${e.status}">${e.status.toUpperCase()}</span></td>
                    <td>
                        <div class="event-actions">
                            <button type="button" class="event-action-btn event-action-view" data-action="view" data-index="${sourceIndex}">View</button>
                            <button type="button" class="event-action-btn event-action-edit" data-action="edit" data-index="${sourceIndex}">Edit</button>
                        </div>
                    </td>
                `;
                list.appendChild(row);
            });
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentQuery = searchInput.value.trim().toLowerCase();
            render();
        });
    }

    if (programFilter) {
        programFilter.addEventListener('change', () => {
            currentProgram = programFilter.value
                ? programFilter.options[programFilter.selectedIndex].text.toLowerCase()
                : '';
            render();
        });
    }

    if (dateFilter) {
        dateFilter.addEventListener('change', () => {
            currentDate = dateFilter.value;
            render();
        });
    }

    // View / Edit actions
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;
        const index = Number(btn.dataset.index);
        if (Number.isNaN(index) || !events[index]) return;
        const event = events[index];
        if (action === 'view') {
            if (viewEventName) viewEventName.value = event.name;
            if (viewEventProgram) viewEventProgram.value = event.program;
            if (viewEventDate) viewEventDate.value = event.date;
            if (viewEventTime) viewEventTime.value = event.time;
            if (viewEventVenue) viewEventVenue.value = event.venue;
            if (viewEventParticipants) viewEventParticipants.value = String(event.expectedParticipants);
            if (viewModal) {
                resetModalMaximize(viewModal);
                viewModal.style.display = 'flex';
            }
        } else if (action === 'edit') {
            editingIndex = index;
            if (nameInput) nameInput.value = event.name;
            if (programInput) programInput.value = event.program;
            if (dateInput) dateInput.value = event.date;
            setEventTimeSelects(event.time);
            if (venueInput) venueInput.value = event.venue;
            if (participantsInput) participantsInput.value = String(event.expectedParticipants);
            if (saveBtn) saveBtn.textContent = 'Update';
            if (modal) {
                resetModalMaximize(modal);
                modal.style.display = 'flex';
            }
        }
    });

    // Modal helpers
    function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        resetModalMaximize(modal);
        editingIndex = -1;
        if (saveBtn) saveBtn.textContent = 'Save';
        if (nameInput) nameInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        resetModalMaximize(modal);
        if (nameInput) nameInput.value = '';
        if (programInput) programInput.value = '';
        if (otherEventInput) otherEventInput.value = '';
        if (otherEventField) otherEventField.style.display = 'none';
        if (dateInput) dateInput.value = '';
        clearEventTimeSelects();
        if (venueInput) venueInput.value = '';
        if (participantsInput) participantsInput.value = '';
    }

    if (viewModal) {
        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal || e.target.hasAttribute('data-view-close')) {
                resetModalMaximize(viewModal);
                viewModal.style.display = 'none';
            }
        });
    }

    function openSuccessModal(message) {
        if (!successModal) return;
        if (successMessage) {
            successMessage.textContent = message || 'Add successful.';
        }
        successModal.style.display = 'flex';
    }

    function closeSuccessModal() {
        if (!successModal) return;
        successModal.style.display = 'none';
    }

    if (addBtn) {
        addBtn.addEventListener('click', openModal);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.hasAttribute('data-modal-close') || e.target.hasAttribute('data-modal-cancel')) {
                closeModal();
            }
        });
    }

    if (nameInput) {
        nameInput.addEventListener('change', () => {
            if (!otherEventField || !otherEventInput) return;
            if (nameInput.value === 'Other') {
                otherEventField.style.display = 'block';
            } else {
                otherEventField.style.display = 'none';
                otherEventInput.value = '';
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            let name = (nameInput?.value || '').trim();
            const program = (programInput?.value || '').trim();
            const otherEvent = (otherEventInput?.value || '').trim();
            const date = (dateInput?.value || '').trim();
            const time = getEventTimeFromSelects().trim();
            const venue = (venueInput?.value || '').trim();
            const participantsVal = (participantsInput?.value || '').trim();

            if (name === 'Other' && otherEvent) {
                name = otherEvent;
            } else if (name === 'Other') {
                alert('Please specify event name.');
                return;
            }

            if (!name || !program || !date || !time) {
                alert('Please fill in at least event name, related program, date and time. UI only.');
                return;
            }

            const expectedParticipants = Number(participantsVal) || 0;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                const payload = {
                    name,
                    program,
                    date,
                    time,
                    venue: venue || 'To be confirmed',
                    expectedParticipants,
                    status: 'upcoming',
                };
                if (editingIndex >= 0 && events[editingIndex]) {
                    events[editingIndex] = payload;
                } else {
                    events.push(payload);
                }

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                openSuccessModal(editingIndex >= 0 ? 'Update successful.' : 'Add successful.');
                editingIndex = -1;
            }, 600);
        });
    }

    if (successModal) {
        successModal.addEventListener('click', (e) => {
            if (e.target === successModal || e.target.hasAttribute('data-success-close')) {
                closeSuccessModal();
            }
        });
    }

    initEventTimeSelects();
    render();

    wireModalToggle(modal);
    wireModalToggle(viewModal);
}

