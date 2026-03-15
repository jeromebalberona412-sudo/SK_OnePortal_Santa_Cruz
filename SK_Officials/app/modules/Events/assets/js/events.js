document.addEventListener('DOMContentLoaded', () => {
    initializeEventsUI();
});

function initializeEventsUI() {
    const list = document.getElementById('eventList');
    const todayContainer = document.getElementById('todayEvents');

    const searchInput = document.getElementById('eventSearch');
    const programFilter = document.getElementById('eventProgramFilter');
    const dateFilter = document.getElementById('eventDateFilter');

    const addBtn = document.getElementById('addEventBtn');
    const modal = document.getElementById('eventModal');
    const nameInput = document.getElementById('eventNameInput');
    const programInput = document.getElementById('eventProgramInput');
    const dateInput = document.getElementById('eventDateInput');
    const timeInput = document.getElementById('eventTimeInput');
    const venueInput = document.getElementById('eventVenueInput');
    const participantsInput = document.getElementById('eventParticipantsInput');
    const saveBtn = document.getElementById('eventSaveBtn');

    if (!list) return;

    // Start empty; events appear only after "Schedule Event"
    const events = [];

    let currentQuery = '';
    let currentProgram = '';
    let currentDate = '';

    function formatSchedule(date, time) {
        const opts = { month: 'short', day: '2-digit', year: 'numeric' };
        const d = new Date(`${date}T${time}`);
        const label = d.toLocaleDateString(undefined, opts);
        const t = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        return `${label} · ${t}`;
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
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.innerHTML =
                'No events scheduled yet. <br><strong>Tip:</strong> click "+ Schedule Event" to add an activity (UI only).';
            list.appendChild(empty);
        } else {
            filtered.forEach((e) => {
                const card = document.createElement('article');
                card.className = 'event-card';
                card.innerHTML = `
                    <div>
                        <h3 class="event-title">${e.name}</h3>
                        <p class="event-program">Under: ${e.program}</p>
                        <p class="event-meta"><strong>Participants:</strong> ${e.expectedParticipants} expected</p>
                    </div>
                    <div>
                        <p class="event-schedule">${formatSchedule(e.date, e.time)}</p>
                        <p class="event-venue">${e.venue}</p>
                        <p class="event-participants">SK to coordinate logistics and safety with barangay officials.</p>
                    </div>
                    <div class="event-footer">
                        <span class="status-pill ${e.status}">
                            ${e.status.toUpperCase()}
                        </span>
                        <div class="event-actions">
                            <button type="button" class="btn-outline" data-action="view">View</button>
                            <button type="button" class="btn-outline" data-action="timeline">Timeline</button>
                        </div>
                    </div>
                `;
                list.appendChild(card);
            });
        }

        renderToday(events);
    }

    function renderToday(allEvents) {
        if (!todayContainer) return;

        todayContainer.innerHTML = '';
        const today = new Date();
        const todayString = today.toISOString().slice(0, 10);

        const todayEvents = allEvents.filter((e) => e.date === todayString);

        if (todayEvents.length === 0) {
            const p = document.createElement('p');
            p.className = 'side-note';
            p.textContent = 'No events scheduled today. This panel will highlight activities that match the current date.';
            todayContainer.appendChild(p);
            return;
        }

        todayEvents.forEach((e) => {
            const item = document.createElement('div');
            item.className = 'today-event-item';
            item.innerHTML = `
                <div class="today-event-time">${formatSchedule(e.date, e.time)}</div>
                <div class="today-event-name">${e.name}</div>
                <div class="today-event-program">${e.program}</div>
            `;
            todayContainer.appendChild(item);
        });
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

    // Simple UI handlers for View / Timeline buttons (no backend)
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;
        if (action === 'view') {
            alert('View event details (UI only, backend to be connected later).');
        } else if (action === 'timeline') {
            alert('Event timeline view (UI only, backend to be connected later).');
        }
    });

    // Modal helpers
    function openModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        if (nameInput) nameInput.focus();
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        if (nameInput) nameInput.value = '';
        if (programInput) programInput.value = '';
        if (dateInput) dateInput.value = '';
        if (timeInput) timeInput.value = '';
        if (venueInput) venueInput.value = '';
        if (participantsInput) participantsInput.value = '';
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

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            const name = (nameInput?.value || '').trim();
            const program = (programInput?.value || '').trim();
            const date = (dateInput?.value || '').trim();
            const time = (timeInput?.value || '').trim();
            const venue = (venueInput?.value || '').trim();
            const participantsVal = (participantsInput?.value || '').trim();

            if (!name || !program || !date || !time) {
                alert('Please fill in at least event name, related program, date and time. UI only.');
                return;
            }

            const expectedParticipants = Number(participantsVal) || 0;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Simulated AJAX
            setTimeout(() => {
                events.push({
                    name,
                    program,
                    date,
                    time,
                    venue: venue || 'To be confirmed',
                    expectedParticipants,
                    status: 'upcoming',
                });

                closeModal();
                render();
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';

                alert('Event successfully scheduled (UI only, no backend yet).');
            }, 600);
        });
    }

    render();
}

