// ===========================
// CALENDAR STATE & CONFIG
// ===========================

let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth(); // 0-11
let eventsData = [];
let selectedDate = null;

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// ===========================
// INITIALIZATION
// ===========================

function bootCalendar() {
    initializeCalendar();
    attachEventListeners();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootCalendar);
} else {
    bootCalendar();
}

function initializeCalendar() {
    updateMonthLabel();
    renderCalendar();
    fetchEvents();
}

function attachEventListeners() {
    document.getElementById('calendarPrevBtn')?.addEventListener('click', goToPreviousMonth);
    document.getElementById('calendarNextBtn')?.addEventListener('click', goToNextMonth);
    document.getElementById('calendarJumpBtn')?.addEventListener('click', openJumpToDateModal);
}

// ===========================
// CALENDAR RENDERING
// ===========================

function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    if (!grid) return;

    grid.innerHTML = '';

    // Render day headers
    dayNames.forEach(day => {
        const header = document.createElement('div');
        header.className = 'calendar-day-header';
        header.textContent = day;
        grid.appendChild(header);
    });

    // Get first day of month and total days
    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Get previous month days
    const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();

    // Render previous month trailing days
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const day = prevMonthLastDay - i;
        const cell = createDayCell(day, true, currentMonth - 1);
        grid.appendChild(cell);
    }

    // Render current month days
    for (let day = 1; day <= daysInMonth; day++) {
        const cell = createDayCell(day, false, currentMonth);
        grid.appendChild(cell);
    }

    // Render next month leading days
    const totalCells = grid.children.length - 7; // exclude headers
    const remainingCells = 42 - totalCells; // 6 rows × 7 days = 42
    for (let day = 1; day <= remainingCells; day++) {
        const cell = createDayCell(day, true, currentMonth + 1);
        grid.appendChild(cell);
    }
}

function createDayCell(day, isOtherMonth, month) {
    const cell = document.createElement('div');
    cell.className = 'calendar-day-cell';

    if (isOtherMonth) {
        cell.classList.add('other-month');
    }

    // Check if today
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const cellDate = new Date(currentYear, month, day);
    cellDate.setHours(0, 0, 0, 0);
    
    const isPastDate = cellDate < today;
    
    if (
        cellDate.getDate() === today.getDate() &&
        cellDate.getMonth() === today.getMonth() &&
        cellDate.getFullYear() === today.getFullYear()
    ) {
        cell.classList.add('today');
    }

    // Add past date class
    if (isPastDate && !isOtherMonth) {
        cell.classList.add('past-date');
        cell.style.cursor = 'not-allowed';
        cell.style.opacity = '0.6';
    }

    // Day number
    const dayNumber = document.createElement('div');
    dayNumber.className = 'calendar-day-number';
    dayNumber.textContent = day;
    cell.appendChild(dayNumber);

    // Check for events on this date
    const dateStr = formatDate(new Date(currentYear, month, day));
    const dayEvents = eventsData.filter(e => e.event_date === dateStr);

    if (dayEvents.length > 0 && !isOtherMonth) {
        cell.classList.add('has-events');

        const eventCount = document.createElement('div');
        eventCount.className = 'calendar-event-count';
        eventCount.innerHTML = `<span class="calendar-event-dot"></span>${dayEvents.length} event${dayEvents.length > 1 ? 's' : ''}`;
        cell.appendChild(eventCount);
    }

    // Click handler
    cell.addEventListener('click', () => {
        if (!isOtherMonth && !isPastDate) {
            selectedDate = dateStr;
            openDayModal(dateStr, dayEvents);
        } else if (isPastDate && !isOtherMonth) {
            showError('Cannot add events to past dates');
        }
    });

    return cell;
}

function updateMonthLabel() {
    const label = document.getElementById('calendarMonthLabel');
    if (label) {
        label.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    }
}

// ===========================
// NAVIGATION
// ===========================

function goToPreviousMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    updateMonthLabel();
    renderCalendar();
    fetchEvents();
}

function goToNextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    updateMonthLabel();
    renderCalendar();
    fetchEvents();
}

function openJumpToDateModal() {
    const dateInput = prompt('Enter date (YYYY-MM-DD):');
    if (dateInput) {
        const date = new Date(dateInput);
        if (!isNaN(date.getTime())) {
            currentYear = date.getFullYear();
            currentMonth = date.getMonth();
            updateMonthLabel();
            renderCalendar();
            fetchEvents();
        } else {
            alert('Invalid date format. Please use YYYY-MM-DD.');
        }
    }
}

// ===========================
// API CALLS
// ===========================

async function fetchEvents() {
    try {
        const response = await fetch(`/api/calendar/events?year=${currentYear}&month=${currentMonth + 1}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`,
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch events');
        }

        const result = await response.json();
        eventsData = result.data || [];
        renderCalendar();
    } catch (error) {
        console.error('Error fetching events:', error);
        showError('Failed to load events');
    }
}

async function saveEvent(eventData) {
    try {
        const response = await fetch('/api/calendar/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`,
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(eventData),
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                displayValidationErrors(result.errors);
            } else {
                throw new Error(result.message || 'Failed to save event');
            }
            return null;
        }

        return result.data;
    } catch (error) {
        console.error('Error saving event:', error);
        showError('Failed to save event');
        return null;
    }
}

async function updateEvent(eventId, eventData) {
    try {
        const response = await fetch(`/api/calendar/events/${eventId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`,
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(eventData),
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                displayValidationErrors(result.errors);
            } else {
                throw new Error(result.message || 'Failed to update event');
            }
            return null;
        }

        return result.data;
    } catch (error) {
        console.error('Error updating event:', error);
        showError('Failed to update event');
        return null;
    }
}

async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) {
        return false;
    }

    try {
        const response = await fetch(`/api/calendar/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`,
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to delete event');
        }

        return true;
    } catch (error) {
        console.error('Error deleting event:', error);
        showError('Failed to delete event');
        return false;
    }
}

// ===========================
// MODAL HANDLING
// ===========================

function openDayModal(dateStr, dayEvents) {
    const modal = createDayModal(dateStr, dayEvents);
    document.body.appendChild(modal);
}

function createDayModal(dateStr, dayEvents) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';

    const modal = document.createElement('div');
    modal.className = 'modal-content';

    const date = new Date(dateStr);
    const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    modal.innerHTML = `
        <div class="modal-header">
            <h2>${formattedDate}</h2>
            <button class="modal-close-btn" onclick="closeModal(this)">&times;</button>
        </div>
        <div class="modal-body">
            <button class="btn btn-primary" onclick="openEventForm('${dateStr}', null)" style="margin-bottom: 1rem; width: 100%;">
                Add New Event
            </button>
            <div class="event-list">
                ${dayEvents.length > 0 ? renderEventList(dayEvents) : '<div class="no-events">No events scheduled for this day</div>'}
            </div>
        </div>
    `;

    overlay.appendChild(modal);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeModal(overlay);
        }
    });

    return overlay;
}

function renderEventList(events) {
    return events.map(event => `
        <div class="event-item" onclick="openEventForm('${event.event_date}', ${event.id})">
            <div class="event-item-header">
                <h3 class="event-item-title">${escapeHtml(event.title)}</h3>
                <span class="event-item-time">${event.start_time} - ${event.end_time}</span>
            </div>
            <div class="event-item-badges">
                <span class="event-badge task-${event.task_type.toLowerCase()}">${event.task_type}</span>
                <span class="event-badge status-${event.status.toLowerCase()}">${event.status}</span>
            </div>
            <p class="event-item-description">${escapeHtml(event.description)}</p>
            <p class="event-item-audience">To: ${event.target_audience}</p>
        </div>
    `).join('');
}

function openEventForm(dateStr, eventId = null) {
    closeModal(document.querySelector('.modal-overlay'));

    const event = eventId ? eventsData.find(e => e.id === eventId) : null;
    const isEdit = !!event;

    const modal = createEventFormModal(dateStr, event, isEdit);
    document.body.appendChild(modal);
}

function createEventFormModal(dateStr, event, isEdit) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';

    const modal = document.createElement('div');
    modal.className = 'modal-content';

    modal.innerHTML = `
        <div class="modal-header">
            <h2>${isEdit ? 'Edit Event' : 'Add New Event'}</h2>
            <button class="modal-close-btn" onclick="closeModal(this)">&times;</button>
        </div>
        <form id="eventForm" onsubmit="handleEventSubmit(event, ${isEdit}, ${event?.id || null})">
            <div class="modal-body">
                <div class="form-group">
                    <label for="eventTitle">Title *</label>
                    <input type="text" id="eventTitle" name="title" value="${event?.title || ''}" required maxlength="255" placeholder="Enter event title">
                    <div class="form-error" id="error-title"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventTaskType">Task Type *</label>
                        <select id="eventTaskType" name="task_type" required>
                            <option value="">Select Task Type</option>
                            <option value="Event" ${event?.task_type === 'Event' ? 'selected' : ''}>Event</option>
                            <option value="Meeting" ${event?.task_type === 'Meeting' ? 'selected' : ''}>Meeting</option>
                            <option value="Training" ${event?.task_type === 'Training' ? 'selected' : ''}>Training</option>
                            <option value="Reminder" ${event?.task_type === 'Reminder' ? 'selected' : ''}>Reminder</option>
                            <option value="Workshop" ${event?.task_type === 'Workshop' ? 'selected' : ''}>Workshop</option>
                            <option value="Seminar" ${event?.task_type === 'Seminar' ? 'selected' : ''}>Seminar</option>
                            <option value="Conference" ${event?.task_type === 'Conference' ? 'selected' : ''}>Conference</option>
                            <option value="Activity" ${event?.task_type === 'Activity' ? 'selected' : ''}>Activity</option>
                        </select>
                        <div class="form-error" id="error-task_type"></div>
                    </div>

                    <div class="form-group">
                        <label for="eventStatus">Status *</label>
                        <select id="eventStatus" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Pending" ${event?.status === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="Complete" ${event?.status === 'Complete' ? 'selected' : ''}>Complete</option>
                            <option value="Cancel" ${event?.status === 'Cancel' ? 'selected' : ''}>Cancel</option>
                        </select>
                        <div class="form-error" id="error-status"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventStartTime">Start Time * (7:00 AM - 10:00 PM)</label>
                        <input type="time" id="eventStartTime" name="start_time" value="${event?.start_time || ''}" required min="07:00" max="22:00">
                        <small style="color:#6b7280;font-size:0.8rem;">Event hours: 7:00 AM - 10:00 PM</small>
                        <div class="form-error" id="error-start_time"></div>
                    </div>

                    <div class="form-group">
                        <label for="eventEndTime">End Time * (7:00 AM - 10:00 PM)</label>
                        <input type="time" id="eventEndTime" name="end_time" value="${event?.end_time || ''}" required min="07:00" max="22:00">
                        <small style="color:#6b7280;font-size:0.8rem;">Must be after start time</small>
                        <div class="form-error" id="error-end_time"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="eventAudience">To (Target Audience) *</label>
                    <select id="eventAudience" name="target_audience" required>
                        <option value="">Select Target Audience</option>
                        <option value="All SK Officials" ${event?.target_audience === 'All SK Officials' ? 'selected' : ''}>All SK Officials</option>
                        <option value="SK Fed" ${event?.target_audience === 'SK Fed' ? 'selected' : ''}>SK Fed</option>
                    </select>
                    <div class="form-error" id="error-target_audience"></div>
                </div>

                <div class="form-group">
                    <label for="eventDescription">Description *</label>
                    <textarea id="eventDescription" name="description" required maxlength="1000" placeholder="Enter event description">${event?.description || ''}</textarea>
                    <div class="form-error" id="error-description"></div>
                </div>
            </div>

            <div class="modal-footer">
                ${isEdit ? '<button type="button" class="btn btn-danger" onclick="handleEventDelete(' + event.id + ')">Delete</button>' : ''}
                <button type="button" class="btn btn-secondary" onclick="closeModal(this)">Cancel</button>
                <button type="submit" class="btn btn-primary">${isEdit ? 'Update' : 'Save'}</button>
            </div>
        </form>
    `;

    overlay.appendChild(modal);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeModal(overlay);
        }
    });

    return overlay;
}

async function handleEventSubmit(e, isEdit, eventId) {
    e.preventDefault();

    clearValidationErrors();

    const formData = new FormData(e.target);
    const eventData = {
        event_date: selectedDate,
        title: formData.get('title'),
        description: formData.get('description'),
        task_type: formData.get('task_type'),
        status: formData.get('status'),
        start_time: formData.get('start_time'),
        end_time: formData.get('end_time'),
        target_audience: formData.get('target_audience'),
    };

    // Client-side validations
    const validationErrors = validateEventData(eventData, isEdit, eventId);
    if (Object.keys(validationErrors).length > 0) {
        displayValidationErrors(validationErrors);
        return;
    }

    let result;
    if (isEdit) {
        result = await updateEvent(eventId, eventData);
    } else {
        result = await saveEvent(eventData);
    }

    if (result) {
        closeModal(document.querySelector('.modal-overlay'));
        await fetchEvents();
        showSuccess(isEdit ? 'Event updated successfully' : 'Event added successfully');
    }
}

function validateEventData(eventData, isEdit, eventId) {
    const errors = {};

    // Validate past date
    const selectedDateObj = new Date(eventData.event_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDateObj < today) {
        errors.event_date = ['Cannot add events to past dates'];
    }

    // Validate time range (7AM to 10PM)
    const startTime = eventData.start_time;
    const endTime = eventData.end_time;

    if (startTime && endTime) {
        const [startHour, startMin] = startTime.split(':').map(Number);
        const [endHour, endMin] = endTime.split(':').map(Number);

        // Check if start time is before 7:00 AM or after 10:00 PM
        if (startHour < 7 || (startHour === 22 && startMin > 0) || startHour > 22) {
            errors.start_time = ['Start time must be between 7:00 AM and 10:00 PM'];
        }

        // Check if end time is before 7:00 AM or after 10:00 PM
        if (endHour < 7 || (endHour === 22 && endMin > 0) || endHour > 22) {
            errors.end_time = ['End time must be between 7:00 AM and 10:00 PM'];
        }

        // Check if end time is after start time
        if (startHour * 60 + startMin >= endHour * 60 + endMin) {
            errors.end_time = ['End time must be after start time'];
        }

        // Check for time conflicts with existing events on the same date
        const existingEvents = eventsData.filter(e => {
            if (e.event_date === eventData.event_date) {
                // If editing, exclude the current event
                if (isEdit && e.id === eventId) {
                    return false;
                }
                return true;
            }
            return false;
        });

        for (const existingEvent of existingEvents) {
            const [existingStartHour, existingStartMin] = existingEvent.start_time.split(':').map(Number);
            const [existingEndHour, existingEndMin] = existingEvent.end_time.split(':').map(Number);

            const newStart = startHour * 60 + startMin;
            const newEnd = endHour * 60 + endMin;
            const existingStart = existingStartHour * 60 + existingStartMin;
            const existingEnd = existingEndHour * 60 + existingEndMin;

            // Check if times overlap
            if (
                (newStart >= existingStart && newStart < existingEnd) ||
                (newEnd > existingStart && newEnd <= existingEnd) ||
                (newStart <= existingStart && newEnd >= existingEnd)
            ) {
                errors.start_time = [`Time conflict with existing event: ${existingEvent.title} (${existingEvent.start_time} - ${existingEvent.end_time})`];
                break;
            }
        }
    }

    return errors;
}

async function handleEventDelete(eventId) {
    const success = await deleteEvent(eventId);
    if (success) {
        closeModal(document.querySelector('.modal-overlay'));
        await fetchEvents();
        showSuccess('Event deleted successfully');
    }
}

function closeModal(element) {
    const modal = element.closest('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// ===========================
// UTILITY FUNCTIONS
// ===========================

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function getAuthToken() {
    return localStorage.getItem('auth_token') || '';
}

function displayValidationErrors(errors) {
    clearValidationErrors();
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(`error-${field}`);
        if (errorElement) {
            errorElement.textContent = errors[field][0];
        }
    });
}

function clearValidationErrors() {
    document.querySelectorAll('.form-error').forEach(el => {
        el.textContent = '';
    });
}

function showError(message) {
    alert('Error: ' + message);
}

function showSuccess(message) {
    alert(message);
}
