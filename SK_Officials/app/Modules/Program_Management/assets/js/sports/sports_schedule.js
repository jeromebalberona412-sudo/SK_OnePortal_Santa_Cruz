// ══════════════════════════════════════════════════════════════
// Sports Program Schedule Management
// ══════════════════════════════════════════════════════════════

let currentProgramId = null;
let sportsPrograms = [];

// ── Initialize ──
document.addEventListener('DOMContentLoaded', function() {
    loadSportsPrograms();
    initSportsScheduleEventListeners();
});

// ── Load Sports Programs ──
function loadSportsPrograms() {
    const stored = localStorage.getItem('sportsPrograms');
    if (stored) {
        sportsPrograms = JSON.parse(stored);
    } else {
        // Initialize with sample data
        sportsPrograms = [
            {
                id: 1,
                name: 'Summer Basketball League',
                type: 'Sports Development',
                committee: 'Sports Committee',
                participants: 50,
                venue: 'Santa Cruz Sports Complex',
                description: 'Annual summer basketball tournament for youth',
                startDate: '2026-04-15',
                endDate: '2026-05-30',
                status: 'open',
                createdAt: new Date().toISOString()
            },
            {
                id: 2,
                name: 'Volleyball Training Camp',
                type: 'Sports Development',
                committee: 'Sports Committee',
                participants: 30,
                venue: 'Barangay Hall Court',
                description: 'Intensive volleyball training for aspiring players',
                startDate: '2026-06-01',
                endDate: '2026-06-15',
                status: 'upcoming',
                createdAt: new Date().toISOString()
            }
        ];
        localStorage.setItem('sportsPrograms', JSON.stringify(sportsPrograms));
    }
    
    renderSportsProgramsTable();
    updateProgramCount();
    checkActiveProgram();
}

// ── Render Sports Programs Table ──
function renderSportsProgramsTable() {
    const tbody = document.getElementById('safFormsTableBody');
    if (!tbody) return;
    
    if (sportsPrograms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">No sports programs yet. Click <strong>Create Program</strong> to get started.</td></tr>';
        return;
    }
    
    tbody.innerHTML = sportsPrograms.map(program => {
        const statusBadge = getStatusBadge(program);
        const isActive = currentProgramId === program.id;
        const rowClass = isActive ? 'active-program-row' : '';
        
        return `
            <tr class="${rowClass}" data-program-id="${program.id}">
                <td>${program.name}</td>
                <td>${program.type}</td>
                <td>${program.committee}</td>
                <td>${program.participants || 0}</td>
                <td>${formatDate(program.startDate)}</td>
                <td>${formatDate(program.endDate)}</td>
                <td>${statusBadge}</td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button class="prog-btn prog-btn-view" onclick="viewSportsProgram(${program.id})" title="View">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button class="prog-btn prog-btn-edit" onclick="editSportsProgram(${program.id})" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="prog-btn prog-btn-delete" onclick="deleteSportsProgram(${program.id})" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// ── Get Status Badge ──
function getStatusBadge(program) {
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
        upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[program.status] || statusColors.closed;
    return `<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>`;
}

// ── Format Date ──
function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ── Update Program Count ──
function updateProgramCount() {
    const countEl = document.getElementById('programCount');
    if (countEl) {
        countEl.textContent = sportsPrograms.length;
    }
}

// ── Check Active Program ──
function checkActiveProgram() {
    const stored = localStorage.getItem('sportsActiveProgram');
    if (stored) {
        const activeProgram = JSON.parse(stored);
        currentProgramId = activeProgram.id;
        displayActiveProgram(activeProgram);
    }
}

// ── Display Active Program ──
function displayActiveProgram(program) {
    const card = document.getElementById('activeProgramCard');
    const nameEl = document.getElementById('activeProgramName');
    const infoEl = document.getElementById('activeProgramInfo');
    const statusBadge = document.getElementById('activeProgramStatusBadge');
    
    if (!card || !nameEl || !infoEl || !statusBadge) return;
    
    card.style.display = 'block';
    nameEl.textContent = program.name;
    
    const statusColors = {
        open: { bg: 'rgba(255,255,255,0.25)', text: '#fff', label: 'Open' },
        closed: { bg: 'rgba(239,68,68,0.3)', text: '#fff', label: 'Closed' },
        upcoming: { bg: 'rgba(59,130,246,0.3)', text: '#fff', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[program.status] || statusColors.closed;
    statusBadge.style.background = statusStyle.bg;
    statusBadge.textContent = statusStyle.label;
    
    infoEl.innerHTML = `
        <strong>Venue:</strong> ${program.venue || 'TBD'}<br>
        <strong>Participants:</strong> ${program.participants || 0}<br>
        <strong>Dates:</strong> ${formatDate(program.startDate)} - ${formatDate(program.endDate)}
    `;
    
    renderSportsProgramsTable();
}

// ── Initialize Event Listeners ──
function initSportsScheduleEventListeners() {
    // Create Program button
    const createBtn = document.getElementById('safOpenFormBtn');
    if (createBtn) {
        createBtn.addEventListener('click', openCreateProgramModal);
    }
    
    // Save Program button
    const saveBtn = document.getElementById('btnSaveProgram');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveSportsProgram);
    }
    
    // Cancel button
    const cancelBtn = document.getElementById('btnCancelProgram');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeProgramModal);
    }
    
    // Modal close buttons
    const closeBtn = document.getElementById('scholarProgramClose');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeProgramModal);
    }
    
    // View Active Program button
    const viewActiveBtn = document.getElementById('btnViewActiveProgram');
    if (viewActiveBtn) {
        viewActiveBtn.addEventListener('click', () => {
            if (currentProgramId) {
                viewSportsProgram(currentProgramId);
            }
        });
    }
    
    // Edit Active Program button
    const editActiveBtn = document.getElementById('btnEditActiveProgram');
    if (editActiveBtn) {
        editActiveBtn.addEventListener('click', () => {
            if (currentProgramId) {
                editSportsProgram(currentProgramId);
            }
        });
    }
    
    // Close Active Program button
    const closeActiveBtn = document.getElementById('btnCloseActiveProgram');
    if (closeActiveBtn) {
        closeActiveBtn.addEventListener('click', closeActiveProgram);
    }
    
    // View Program Modal close
    const viewCloseBtn = document.getElementById('viewProgramClose');
    const viewCloseBtn2 = document.getElementById('viewProgramCloseBtn');
    if (viewCloseBtn) viewCloseBtn.addEventListener('click', closeViewModal);
    if (viewCloseBtn2) viewCloseBtn2.addEventListener('click', closeViewModal);
    
    // Close Program Modal
    const closeProgramModalBtn = document.getElementById('closeProgramClose');
    const closeProgramCancel = document.getElementById('closeProgramCancel');
    const closeProgramConfirm = document.getElementById('closeProgramConfirm');
    
    if (closeProgramModalBtn) closeProgramModalBtn.addEventListener('click', closeCloseProgramModal);
    if (closeProgramCancel) closeProgramCancel.addEventListener('click', closeCloseProgramModal);
    if (closeProgramConfirm) closeProgramConfirm.addEventListener('click', confirmCloseProgram);
    
    // Delete Modal
    const deleteClose = document.getElementById('slDeleteClose');
    const deleteCancel = document.getElementById('slDeleteCancel');
    const deleteConfirm = document.getElementById('slDeleteConfirm');
    
    if (deleteClose) deleteClose.addEventListener('click', closeDeleteModal);
    if (deleteCancel) deleteCancel.addEventListener('click', closeDeleteModal);
    if (deleteConfirm) deleteConfirm.addEventListener('click', confirmDeleteSportsProgram);
    
    // Program filter
    const filterSelect = document.getElementById('programFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', filterPrograms);
    }
}

// ── Open Create Program Modal ──
function openCreateProgramModal() {
    const modal = document.getElementById('scholarProgramModal');
    const title = document.getElementById('modalTitle');
    if (modal && title) {
        title.textContent = 'Create Sports Program';
        modal.style.display = 'flex';
        clearProgramForm();
    }
}

// ── Close Program Modal ──
function closeProgramModal() {
    const modal = document.getElementById('scholarProgramModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ── Clear Program Form ──
function clearProgramForm() {
    document.getElementById('programName').value = '';
    document.getElementById('programType').value = 'Sports Development';
    document.getElementById('programCommittee').value = 'Sports';
    document.getElementById('participationQty').value = '';
    document.getElementById('programVenue').value = '';
    document.getElementById('programDescription').value = '';
    document.getElementById('schedStartDate').value = '';
    document.getElementById('schedEndDate').value = '';
    document.getElementById('programStatus').value = 'auto';
    
    // Reset character counters
    document.getElementById('programNameCount').textContent = '0';
    document.getElementById('venueCount').textContent = '0';
    document.getElementById('descriptionCount').textContent = '0';
}

// ── Save Sports Program ──
function saveSportsProgram() {
    const name = document.getElementById('programName').value.trim();
    const type = document.getElementById('programType').value;
    const committee = document.getElementById('programCommittee').value;
    const participants = document.getElementById('participationQty').value;
    const venue = document.getElementById('programVenue').value.trim();
    const description = document.getElementById('programDescription').value.trim();
    const startDate = document.getElementById('schedStartDate').value;
    const endDate = document.getElementById('schedEndDate').value;
    const status = document.getElementById('programStatus').value;
    
    // Validation
    if (!name) {
        showToast('Program name is required', 'error');
        return;
    }
    
    if (!startDate || !endDate) {
        showToast('Start and end dates are required', 'error');
        return;
    }
    
    if (new Date(endDate) < new Date(startDate)) {
        showToast('End date must be after start date', 'error');
        return;
    }
    
    // Determine status if auto
    let finalStatus = status;
    if (status === 'auto') {
        const now = new Date();
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (now < start) {
            finalStatus = 'upcoming';
        } else if (now >= start && now <= end) {
            finalStatus = 'open';
        } else {
            finalStatus = 'closed';
        }
    }
    
    // Create program object
    const program = {
        id: Date.now(),
        name,
        type,
        committee,
        participants: participants ? parseInt(participants) : 0,
        venue,
        description,
        startDate,
        endDate,
        status: finalStatus,
        createdAt: new Date().toISOString()
    };
    
    // Add to programs list
    sportsPrograms.push(program);
    localStorage.setItem('sportsPrograms', JSON.stringify(sportsPrograms));
    
    // Set as active program
    localStorage.setItem('sportsActiveProgram', JSON.stringify(program));
    currentProgramId = program.id;
    
    // Update UI
    renderSportsProgramsTable();
    updateProgramCount();
    displayActiveProgram(program);
    
    // Close modal
    closeProgramModal();
    
    // Show toast
    showToast('Sports program created successfully!', 'success');
}

// ── View Sports Program ──
function viewSportsProgram(programId) {
    const program = sportsPrograms.find(p => p.id === programId);
    if (!program) return;
    
    const modal = document.getElementById('viewProgramModal');
    const body = document.getElementById('viewProgramBody');
    
    if (!modal || !body) return;
    
    const statusColors = {
        open: { bg: '#dcfce7', text: '#166534', label: 'Open' },
        closed: { bg: '#fee2e2', text: '#991b1b', label: 'Closed' },
        upcoming: { bg: '#dbeafe', text: '#1e40af', label: 'Upcoming' }
    };
    
    const statusStyle = statusColors[program.status] || statusColors.closed;
    
    body.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="grid-column:1/-1;">
                <h4 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:12px;">${program.name}</h4>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Program Type</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${program.type}</div>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Committee</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${program.committee}</div>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Participants</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${program.participants || 0}</div>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Status</label>
                <div style="margin-top:4px;">
                    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;background:${statusStyle.bg};color:${statusStyle.text};">${statusStyle.label}</span>
                </div>
            </div>
            
            <div style="grid-column:1/-1;">
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Venue</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${program.venue || 'TBD'}</div>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Start Date</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${formatDate(program.startDate)}</div>
            </div>
            
            <div>
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">End Date</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;">${formatDate(program.endDate)}</div>
            </div>
            
            <div style="grid-column:1/-1;">
                <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;">Description</label>
                <div style="font-size:13px;color:#111827;margin-top:4px;line-height:1.6;">${program.description || 'No description provided'}</div>
            </div>
        </div>
    `;
    
    modal.style.display = 'flex';
}

// ── Close View Modal ──
function closeViewModal() {
    const modal = document.getElementById('viewProgramModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ── Edit Sports Program ──
function editSportsProgram(programId) {
    const program = sportsPrograms.find(p => p.id === programId);
    if (!program) return;
    
    const modal = document.getElementById('scholarProgramModal');
    const title = document.getElementById('modalTitle');
    
    if (modal && title) {
        title.textContent = 'Edit Sports Program';
        modal.style.display = 'flex';
        
        // Populate form
        document.getElementById('programName').value = program.name;
        document.getElementById('programType').value = program.type;
        document.getElementById('programCommittee').value = program.committee;
        document.getElementById('participationQty').value = program.participants || '';
        document.getElementById('programVenue').value = program.venue || '';
        document.getElementById('programDescription').value = program.description || '';
        document.getElementById('schedStartDate').value = program.startDate;
        document.getElementById('schedEndDate').value = program.endDate;
        document.getElementById('programStatus').value = program.status;
        
        // Update character counters
        document.getElementById('programNameCount').textContent = program.name.length;
        document.getElementById('venueCount').textContent = (program.venue || '').length;
        document.getElementById('descriptionCount').textContent = (program.description || '').length;
        
        // Store the ID for updating
        document.getElementById('scholarProgramModal').setAttribute('data-editing-id', programId);
    }
}

// ── Delete Sports Program ──
let programToDelete = null;

function deleteSportsProgram(programId) {
    programToDelete = programId;
    const program = sportsPrograms.find(p => p.id === programId);
    
    if (program) {
        const nameEl = document.getElementById('slDeleteName');
        if (nameEl) {
            nameEl.textContent = program.name;
        }
    }
    
    const modal = document.getElementById('slDeleteModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// ── Confirm Delete Sports Program ──
function confirmDeleteSportsProgram() {
    if (programToDelete === null) return;
    
    // Delete from array
    sportsPrograms = sportsPrograms.filter(p => p.id !== programToDelete);
    
    // Update localStorage
    localStorage.setItem('sportsPrograms', JSON.stringify(sportsPrograms));
    
    // If deleting active program, clear it
    if (currentProgramId === programToDelete) {
        localStorage.removeItem('sportsActiveProgram');
        currentProgramId = null;
        const card = document.getElementById('activeProgramCard');
        if (card) {
            card.style.display = 'none';
        }
    }
    
    // Update UI
    renderSportsProgramsTable();
    updateProgramCount();
    
    // Show toast
    showToast('Sports program deleted successfully!', 'success');
    
    // Close modal
    closeDeleteModal();
}

// ── Close Delete Modal ──
function closeDeleteModal() {
    const modal = document.getElementById('slDeleteModal');
    if (modal) {
        modal.style.display = 'none';
    }
    programToDelete = null;
}

// ── Close Active Program ──
function closeCloseProgramModal() {
    const modal = document.getElementById('closeProgramModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmCloseProgram() {
    if (currentProgramId) {
        localStorage.removeItem('sportsActiveProgram');
        currentProgramId = null;
        
        const card = document.getElementById('activeProgramCard');
        if (card) {
            card.style.display = 'none';
        }
        
        renderSportsProgramsTable();
        showToast('Sports program closed successfully!', 'success');
    }
    closeCloseProgramModal();
}

function closeActiveProgram() {
    const modal = document.getElementById('closeProgramModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// ── Filter Programs ──
function filterPrograms() {
    const filter = document.getElementById('programFilter').value;
    const tbody = document.getElementById('safFormsTableBody');
    
    if (!tbody) return;
    
    let filteredPrograms = sportsPrograms;
    
    if (filter === 'recent') {
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        filteredPrograms = sportsPrograms.filter(p => new Date(p.createdAt) >= sevenDaysAgo);
    } else if (filter === 'monthly') {
        const oneMonthAgo = new Date();
        oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
        filteredPrograms = sportsPrograms.filter(p => new Date(p.createdAt) >= oneMonthAgo);
    } else if (filter === 'yearly') {
        const oneYearAgo = new Date();
        oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
        filteredPrograms = sportsPrograms.filter(p => new Date(p.createdAt) >= oneYearAgo);
    }
    
    if (filteredPrograms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="saf-table-empty">No sports programs found for the selected filter.</td></tr>';
        return;
    }
    
    tbody.innerHTML = filteredPrograms.map(program => {
        const statusBadge = getStatusBadge(program);
        const isActive = currentProgramId === program.id;
        const rowClass = isActive ? 'active-program-row' : '';
        
        return `
            <tr class="${rowClass}" data-program-id="${program.id}">
                <td>${program.name}</td>
                <td>${program.type}</td>
                <td>${program.committee}</td>
                <td>${program.participants || 0}</td>
                <td>${formatDate(program.startDate)}</td>
                <td>${formatDate(program.endDate)}</td>
                <td>${statusBadge}</td>
                <td class="col-actions">
                    <div class="prog-tbl-actions">
                        <button class="prog-btn prog-btn-view" onclick="viewSportsProgram(${program.id})" title="View">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button class="prog-btn prog-btn-edit" onclick="editSportsProgram(${program.id})" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="prog-btn prog-btn-delete" onclick="deleteSportsProgram(${program.id})" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// ── Show Toast ──
function showToast(message, type = 'success') {
    const toast = document.getElementById('safToast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.style.display = 'flex';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : '#ef4444';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Make functions global
window.viewSportsProgram = viewSportsProgram;
window.editSportsProgram = editSportsProgram;
window.deleteSportsProgram = deleteSportsProgram;
window.confirmDeleteSportsProgram = confirmDeleteSportsProgram;
