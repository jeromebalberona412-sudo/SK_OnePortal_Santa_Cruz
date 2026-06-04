// ══════════════════════════════════════════════════════════════
// Scholar Application Schedule Management
// ══════════════════════════════════════════════════════════════

let currentScheduleId = null;
let schedules = [];

// ── Initialize ──
document.addEventListener('DOMContentLoaded', function() {
    loadActiveSchedule();
    initScheduleEventListeners();
});

// ── Load Active Schedule ──
function loadActiveSchedule() {
    const stored = localStorage.getItem('scholarActiveSchedule');
    if (stored) {
        const schedule = JSON.parse(stored);
        displayScheduleInfo(schedule);
    }
}

// ── Display Schedule Info ──
function displayScheduleInfo(schedule) {
    const infoDiv = document.getElementById('scheduledAppInfo');
    const statusBadge = document.getElementById('scheduleStatusBadge');
    const infoText = document.getElementById('scheduleInfoText');
    
    if (!infoDiv || !statusBadge || !infoText) return;
    
    infoDiv.style.display = 'block';
    currentScheduleId = schedule.id;
    
    // Determine status
    const now = new Date();
    const openDateTime = new Date(`${schedule.openDate}T${schedule.openTime}`);
    const closeDateTime = new Date(`${schedule.closeDate}T${schedule.closeTime}`);
    
    let status = schedule.status;
    if (status === 'auto') {
        if (now < openDateTime) {
            status = 'upcoming';
        } else if (now >= openDateTime && now <= closeDateTime) {
            status = 'open';
        } else {
            status = 'closed';
        }
    }
    
    // Status badge
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
        upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[status] || statusColors.closed;
    statusBadge.style.backgroundColor = statusStyle.bg;
    statusBadge.style.color = statusStyle.text;
    statusBadge.textContent = statusStyle.label;
    
    // Info text
    const openDateFormatted = new Date(schedule.openDate).toLocaleDateString('en-US', { 
        month: 'short', day: 'numeric', year: 'numeric' 
    });
    const closeDateFormatted = new Date(schedule.closeDate).toLocaleDateString('en-US', { 
        month: 'short', day: 'numeric', year: 'numeric' 
    });
    
    infoText.innerHTML = `
        <strong>Open:</strong> ${openDateFormatted} at ${formatTime(schedule.openTime)}<br>
        <strong>Close:</strong> ${closeDateFormatted} at ${formatTime(schedule.closeTime)}
    `;
}

// ── Format Time ──
function formatTime(time24) {
    const [hours, minutes] = time24.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

// ── Initialize Event Listeners ──
function initScheduleEventListeners() {
    // Make Schedule button
    const btnMakeSchedule = document.getElementById('btnMakeSchedule');
    if (btnMakeSchedule) {
        btnMakeSchedule.addEventListener('click', openMakeScheduleModal);
    }
    
    // Edit Schedule button
    const btnEditSchedule = document.getElementById('btnEditSchedule');
    if (btnEditSchedule) {
        btnEditSchedule.addEventListener('click', editCurrentSchedule);
    }
    
    // View Schedule List button
    const btnViewScheduleList = document.getElementById('btnViewScheduleList');
    if (btnViewScheduleList) {
        btnViewScheduleList.addEventListener('click', openScheduleListModal);
    }
    
    // Save Schedule button
    const btnSaveSchedule = document.getElementById('btnSaveSchedule');
    if (btnSaveSchedule) {
        btnSaveSchedule.addEventListener('click', saveSchedule);
    }
    
    // Close buttons
    const makeScheduleClose = document.getElementById('makeScheduleClose');
    if (makeScheduleClose) {
        makeScheduleClose.addEventListener('click', closeMakeScheduleModal);
    }
    
    const scheduleListClose = document.getElementById('scheduleListClose');
    if (scheduleListClose) {
        scheduleListClose.addEventListener('click', closeScheduleListModal);
    }
    
    // Maximize buttons
    const makeScheduleMaximize = document.getElementById('makeScheduleMaximize');
    if (makeScheduleMaximize) {
        makeScheduleMaximize.addEventListener('click', () => toggleMaximize('makeScheduleBox'));
    }
    
    const scheduleListMaximize = document.getElementById('scheduleListMaximize');
    if (scheduleListMaximize) {
        scheduleListMaximize.addEventListener('click', () => toggleMaximize('scheduleListBox'));
    }
    
    // Activate/Delete schedule buttons
    const activateScheduleConfirm = document.getElementById('activateScheduleConfirm');
    if (activateScheduleConfirm) {
        activateScheduleConfirm.addEventListener('click', confirmActivateSchedule);
    }
    
    const activateScheduleCancel = document.getElementById('activateScheduleCancel');
    const activateScheduleClose = document.getElementById('activateScheduleClose');
    if (activateScheduleCancel) {
        activateScheduleCancel.addEventListener('click', closeActivateScheduleModal);
    }
    if (activateScheduleClose) {
        activateScheduleClose.addEventListener('click', closeActivateScheduleModal);
    }
    
    const deleteScheduleConfirm = document.getElementById('deleteScheduleConfirm');
    if (deleteScheduleConfirm) {
        deleteScheduleConfirm.addEventListener('click', confirmDeleteSchedule);
    }
    
    const deleteScheduleCancel = document.getElementById('deleteScheduleCancel');
    const deleteScheduleClose = document.getElementById('deleteScheduleClose');
    if (deleteScheduleCancel) {
        deleteScheduleCancel.addEventListener('click', closeDeleteScheduleModal);
    }
    if (deleteScheduleClose) {
        deleteScheduleClose.addEventListener('click', closeDeleteScheduleModal);
    }
}

// ── Open Make Schedule Modal ──
function openMakeScheduleModal() {
    const modal = document.getElementById('makeScheduleModal');
    if (modal) {
        modal.style.display = 'flex';
        clearScheduleForm();
    }
}

// ── Close Make Schedule Modal ──
function closeMakeScheduleModal() {
    const modal = document.getElementById('makeScheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ── Edit Current Schedule ──
function editCurrentSchedule() {
    const stored = localStorage.getItem('scholarActiveSchedule');
    if (stored) {
        const schedule = JSON.parse(stored);
        openMakeScheduleModal();
        populateScheduleForm(schedule);
    }
}

// ── Populate Schedule Form ──
function populateScheduleForm(schedule) {
    document.getElementById('schedOpenDate').value = schedule.openDate;
    document.getElementById('schedCloseDate').value = schedule.closeDate;
    document.getElementById('schedStatus').value = schedule.status || 'auto';
    
    // Set time dropdowns
    if (schedule.openTime) {
        const [openHour, openMin] = schedule.openTime.split(':');
        document.getElementById('schedOpenTimeHour').value = openHour;
        document.getElementById('schedOpenTimeMinute').value = openMin;
    }
    
    if (schedule.closeTime) {
        const [closeHour, closeMin] = schedule.closeTime.split(':');
        document.getElementById('schedCloseTimeHour').value = closeHour;
        document.getElementById('schedCloseTimeMinute').value = closeMin;
    }
}

// ── Clear Schedule Form ──
function clearScheduleForm() {
    document.getElementById('schedOpenDate').value = '';
    document.getElementById('schedCloseDate').value = '';
    document.getElementById('schedStatus').value = 'auto';
    document.getElementById('schedOpenTimeHour').value = '08';
    document.getElementById('schedOpenTimeMinute').value = '00';
    document.getElementById('schedCloseTimeHour').value = '17';
    document.getElementById('schedCloseTimeMinute').value = '00';
    
    // Clear errors
    document.getElementById('schedOpenDateError').style.display = 'none';
    document.getElementById('schedCloseDateError').style.display = 'none';
}

// ── Save Schedule ──
function saveSchedule() {
    const openDate = document.getElementById('schedOpenDate').value;
    const closeDate = document.getElementById('schedCloseDate').value;
    const status = document.getElementById('schedStatus').value;
    
    const openHour = document.getElementById('schedOpenTimeHour').value;
    const openMin = document.getElementById('schedOpenTimeMinute').value;
    const closeHour = document.getElementById('schedCloseTimeHour').value;
    const closeMin = document.getElementById('schedCloseTimeMinute').value;
    
    const openTime = `${openHour}:${openMin}`;
    const closeTime = `${closeHour}:${closeMin}`;
    
    // Validation
    let hasError = false;
    
    if (!openDate) {
        document.getElementById('schedOpenDateError').textContent = 'Open date is required';
        document.getElementById('schedOpenDateError').style.display = 'block';
        hasError = true;
    } else {
        document.getElementById('schedOpenDateError').style.display = 'none';
    }
    
    if (!closeDate) {
        document.getElementById('schedCloseDateError').textContent = 'Close date is required';
        document.getElementById('schedCloseDateError').style.display = 'block';
        hasError = true;
    } else {
        document.getElementById('schedCloseDateError').style.display = 'none';
    }
    
    if (hasError) return;
    
    // Check if close is after open
    const openDateTime = new Date(`${openDate}T${openTime}`);
    const closeDateTime = new Date(`${closeDate}T${closeTime}`);
    
    if (closeDateTime <= openDateTime) {
        document.getElementById('schedCloseDateError').textContent = 'Close date/time must be after open date/time';
        document.getElementById('schedCloseDateError').style.display = 'block';
        return;
    }
    
    // Create schedule object
    const schedule = {
        id: currentScheduleId || Date.now(),
        openDate,
        openTime,
        closeDate,
        closeTime,
        status,
        createdAt: new Date().toISOString()
    };
    
    // Save to localStorage
    localStorage.setItem('scholarActiveSchedule', JSON.stringify(schedule));
    
    // Add to schedules list
    const storedSchedules = localStorage.getItem('scholarSchedules');
    schedules = storedSchedules ? JSON.parse(storedSchedules) : [];
    
    const existingIndex = schedules.findIndex(s => s.id === schedule.id);
    if (existingIndex >= 0) {
        schedules[existingIndex] = schedule;
    } else {
        schedules.push(schedule);
    }
    
    localStorage.setItem('scholarSchedules', JSON.stringify(schedules));
    
    // Display schedule info
    displayScheduleInfo(schedule);
    
    // Close modal
    closeMakeScheduleModal();
    
    // Show toast
    showToast('Schedule saved successfully!', 'success');
}

// ── Open Schedule List Modal ──
function openScheduleListModal() {
    const modal = document.getElementById('scheduleListModal');
    if (modal) {
        modal.style.display = 'flex';
        loadScheduleList();
    }
}

// ── Close Schedule List Modal ──
function closeScheduleListModal() {
    const modal = document.getElementById('scheduleListModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ── Load Schedule List ──
function loadScheduleList() {
    const tbody = document.getElementById('scheduleListTableBody');
    if (!tbody) return;
    
    const storedSchedules = localStorage.getItem('scholarSchedules');
    schedules = storedSchedules ? JSON.parse(storedSchedules) : [];
    
    if (schedules.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">No schedules found</td></tr>';
        return;
    }
    
    tbody.innerHTML = schedules.map(schedule => {
        const isActive = currentScheduleId === schedule.id;
        const statusBadge = getStatusBadgeHTML(schedule);
        
        return `
            <tr>
                <td>${schedule.id}${isActive ? ' <span style="color:#10b981;font-weight:600;">(Active)</span>' : ''}</td>
                <td>${new Date(schedule.openDate).toLocaleDateString()}</td>
                <td>${formatTime(schedule.openTime)}</td>
                <td>${new Date(schedule.closeDate).toLocaleDateString()}</td>
                <td>${formatTime(schedule.closeTime)}</td>
                <td>${statusBadge}</td>
                <td>${new Date(schedule.createdAt).toLocaleDateString()}</td>
                <td class="col-actions">
                    ${!isActive ? `<button class="schol-btn-icon" onclick="activateSchedule(${schedule.id})" title="Activate"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></button>` : ''}
                    <button class="schol-btn-icon" onclick="deleteSchedule(${schedule.id})" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                </td>
            </tr>
        `;
    }).join('');
}

// ── Get Status Badge HTML ──
function getStatusBadgeHTML(schedule) {
    const now = new Date();
    const openDateTime = new Date(`${schedule.openDate}T${schedule.openTime}`);
    const closeDateTime = new Date(`${schedule.closeDate}T${schedule.closeTime}`);
    
    let status = schedule.status;
    if (status === 'auto') {
        if (now < openDateTime) {
            status = 'upcoming';
        } else if (now >= openDateTime && now <= closeDateTime) {
            status = 'open';
        } else {
            status = 'closed';
        }
    }
    
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
        upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[status] || statusColors.closed;
    return `<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>`;
}

// ── Activate Schedule ──
let scheduleToActivate = null;

function activateSchedule(scheduleId) {
    scheduleToActivate = scheduleId;
    const modal = document.getElementById('activateScheduleModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function confirmActivateSchedule() {
    if (scheduleToActivate) {
        const schedule = schedules.find(s => s.id === scheduleToActivate);
        if (schedule) {
            localStorage.setItem('scholarActiveSchedule', JSON.stringify(schedule));
            displayScheduleInfo(schedule);
            showToast('Schedule activated successfully!', 'success');
        }
    }
    closeActivateScheduleModal();
    closeScheduleListModal();
}

function closeActivateScheduleModal() {
    const modal = document.getElementById('activateScheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }
    scheduleToActivate = null;
}

// ── Delete Schedule ──
let scheduleToDelete = null;

function deleteSchedule(scheduleId) {
    scheduleToDelete = scheduleId;
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function confirmDeleteSchedule() {
    if (scheduleToDelete) {
        schedules = schedules.filter(s => s.id !== scheduleToDelete);
        localStorage.setItem('scholarSchedules', JSON.stringify(schedules));
        
        // If deleting active schedule, clear it
        if (currentScheduleId === scheduleToDelete) {
            localStorage.removeItem('scholarActiveSchedule');
            const infoDiv = document.getElementById('scheduledAppInfo');
            if (infoDiv) {
                infoDiv.style.display = 'none';
            }
            currentScheduleId = null;
        }
        
        loadScheduleList();
        showToast('Schedule deleted successfully!', 'success');
    }
    closeDeleteScheduleModal();
}

function closeDeleteScheduleModal() {
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }
    scheduleToDelete = null;
}

// ── Toggle Maximize ──
function toggleMaximize(boxId) {
    const box = document.getElementById(boxId);
    if (box) {
        box.classList.toggle('schol-modal-maximized');
    }
}

// ── Show Toast ──
function showToast(message, type = 'success') {
    const toast = document.getElementById('safToast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Make functions global
window.activateSchedule = activateSchedule;
window.deleteSchedule = deleteSchedule;
window.toggleMaximize = toggleMaximize;
