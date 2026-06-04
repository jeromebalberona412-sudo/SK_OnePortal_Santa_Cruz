/* ═══════════════════════════════════════════════════════════════════════════
   SCHOLARSHIP EVALUATION JAVASCRIPT
   ═══════════════════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function() {
    // ── Initialize ──
    loadEvaluations();
    loadScholars();
    updateStatCards();

    // ── Event Listeners ──
    document.getElementById('btnCreateEvaluation')?.addEventListener('click', openCreateModal);
    document.getElementById('createEvalClose')?.addEventListener('click', closeCreateModal);
    document.getElementById('btnCancelEval')?.addEventListener('click', closeCreateModal);
    document.getElementById('createEvalForm')?.addEventListener('submit', handleCreateEvaluation);
    document.getElementById('viewEvalClose')?.addEventListener('click', closeViewModal);
    document.getElementById('btnExportEvaluations')?.addEventListener('click', exportEvaluations);
    document.getElementById('evalFilterStatus')?.addEventListener('change', filterEvaluations);
    document.getElementById('evalSearchInput')?.addEventListener('input', searchEvaluations);

    // Close modals on overlay click
    document.getElementById('createEvalModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
    document.getElementById('viewEvalModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeViewModal();
    });
});

// ── Load Evaluations ──
function loadEvaluations() {
    // Sample data - replace with actual API call
    const evaluations = [
        {
            id: 'EVAL-2026-001',
            scholarName: 'Dela Cruz, Juan A.',
            type: 'Academic Performance',
            dateCreated: '2026-05-20',
            dueDate: '2026-06-15',
            status: 'Pending',
            evaluator: 'Maria Santos'
        },
        {
            id: 'EVAL-2026-002',
            scholarName: 'Reyes, Ana Marie B.',
            type: 'Quarterly Review',
            dateCreated: '2026-05-18',
            dueDate: '2026-06-10',
            status: 'In Progress',
            evaluator: 'Jose Mendoza'
        },
        {
            id: 'EVAL-2026-003',
            scholarName: 'Garcia, Carlos D.',
            type: 'Community Service',
            dateCreated: '2026-05-15',
            dueDate: '2026-05-30',
            status: 'Completed',
            evaluator: 'Liza Torres'
        }
    ];

    renderEvaluations(evaluations);
}

// ── Render Evaluations Table ──
function renderEvaluations(evaluations) {
    const tbody = document.getElementById('evalTableBody');
    if (!tbody) return;

    if (evaluations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                    No evaluations found. Click "Create Evaluation" to get started.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = evaluations.map(eval => `
        <tr>
            <td><strong>${eval.id}</strong></td>
            <td>${eval.scholarName}</td>
            <td>${eval.type}</td>
            <td>${formatDate(eval.dateCreated)}</td>
            <td>${formatDate(eval.dueDate)}</td>
            <td>${getStatusBadge(eval.status)}</td>
            <td>${eval.evaluator}</td>
            <td class="col-actions">
                <button class="sl-action-btn sl-action-view" onclick="viewEvaluation('${eval.id}')" title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
                <button class="sl-action-btn sl-action-edit" onclick="editEvaluation('${eval.id}')" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
                <button class="sl-action-btn sl-action-delete" onclick="deleteEvaluation('${eval.id}')" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                </button>
            </td>
        </tr>
    `).join('');
}

// ── Update Stat Cards ──
function updateStatCards() {
    // Sample data - replace with actual API call
    document.getElementById('evalStatTotal').textContent = '15';
    document.getElementById('evalStatPending').textContent = '5';
    document.getElementById('evalStatCompleted').textContent = '8';
    document.getElementById('evalStatInProgress').textContent = '2';
}

// ── Load Scholars for Dropdown ──
function loadScholars() {
    const scholarSelect = document.getElementById('evalScholar');
    if (!scholarSelect) return;

    // Sample data - replace with actual API call
    const scholars = [
        { id: 1, name: 'Dela Cruz, Juan A.' },
        { id: 2, name: 'Reyes, Ana Marie B.' },
        { id: 3, name: 'Garcia, Carlos D.' },
        { id: 4, name: 'Santos, Maria Elena C.' }
    ];

    scholarSelect.innerHTML = '<option value="">— Select Scholar —</option>' +
        scholars.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
}

// ── Modal Functions ──
function openCreateModal() {
    document.getElementById('createEvalModal').style.display = 'flex';
    document.getElementById('createEvalForm').reset();
    document.getElementById('evalId').value = '';
}

function closeCreateModal() {
    document.getElementById('createEvalModal').style.display = 'none';
}

function closeViewModal() {
    document.getElementById('viewEvalModal').style.display = 'none';
}

// ── Handle Create/Edit Evaluation ──
function handleCreateEvaluation(e) {
    e.preventDefault();

    const formData = {
        id: document.getElementById('evalId').value,
        scholar: document.getElementById('evalScholar').value,
        type: document.getElementById('evalType').value,
        dueDate: document.getElementById('evalDueDate').value,
        evaluator: document.getElementById('evalEvaluator').value,
        notes: document.getElementById('evalNotes').value
    };

    // Validate
    if (!formData.scholar || !formData.type || !formData.dueDate || !formData.evaluator) {
        alert('Please fill in all required fields.');
        return;
    }

    // TODO: Send to API
    console.log('Creating evaluation:', formData);

    // Show success message
    alert(formData.id ? 'Evaluation updated successfully!' : 'Evaluation created successfully!');
    
    closeCreateModal();
    loadEvaluations();
    updateStatCards();
}

// ── View Evaluation ──
function viewEvaluation(id) {
    // TODO: Fetch evaluation details from API
    const evalData = {
        id: id,
        scholarName: 'Dela Cruz, Juan A.',
        type: 'Academic Performance',
        dateCreated: '2026-05-20',
        dueDate: '2026-06-15',
        status: 'Pending',
        evaluator: 'Maria Santos',
        notes: 'Review academic performance for the current semester.'
    };

    const body = document.getElementById('viewEvalBody');
    body.innerHTML = `
        <div style="display:grid;gap:16px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Evaluation ID</label>
                <p style="margin:4px 0 0;font-size:14px;font-weight:600;">${evalData.id}</p>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Scholar Name</label>
                <p style="margin:4px 0 0;font-size:14px;">${evalData.scholarName}</p>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Type</label>
                    <p style="margin:4px 0 0;font-size:14px;">${evalData.type}</p>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Status</label>
                    <p style="margin:4px 0 0;">${getStatusBadge(evalData.status)}</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Date Created</label>
                    <p style="margin:4px 0 0;font-size:14px;">${formatDate(evalData.dateCreated)}</p>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Due Date</label>
                    <p style="margin:4px 0 0;font-size:14px;">${formatDate(evalData.dueDate)}</p>
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Evaluator</label>
                <p style="margin:4px 0 0;font-size:14px;">${evalData.evaluator}</p>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;">Notes</label>
                <p style="margin:4px 0 0;font-size:14px;color:#6b7280;">${evalData.notes || 'No notes provided.'}</p>
            </div>
        </div>
    `;

    document.getElementById('viewEvalModal').style.display = 'flex';
}

// ── Edit Evaluation ──
function editEvaluation(id) {
    // TODO: Fetch evaluation data and populate form
    openCreateModal();
    document.getElementById('evalId').value = id;
    // Populate other fields...
}

// ── Delete Evaluation ──
function deleteEvaluation(id) {
    if (!confirm('Are you sure you want to delete this evaluation?')) return;

    // TODO: Send delete request to API
    console.log('Deleting evaluation:', id);
    
    alert('Evaluation deleted successfully!');
    loadEvaluations();
    updateStatCards();
}

// ── Export Evaluations ──
function exportEvaluations() {
    // TODO: Implement CSV export
    alert('Exporting evaluations to CSV...');
}

// ── Filter Evaluations ──
function filterEvaluations() {
    const status = document.getElementById('evalFilterStatus').value;
    // TODO: Filter evaluations by status
    console.log('Filtering by status:', status);
}

// ── Search Evaluations ──
function searchEvaluations() {
    const query = document.getElementById('evalSearchInput').value.toLowerCase();
    // TODO: Search evaluations
    console.log('Searching:', query);
}

// ── Helper Functions ──
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function getStatusBadge(status) {
    const statusMap = {
        'Pending': 'eval-status-pending',
        'In Progress': 'eval-status-progress',
        'Completed': 'eval-status-completed'
    };
    const className = statusMap[status] || 'eval-status-pending';
    return `<span class="eval-status-badge ${className}">${status}</span>`;
}

// ── Make functions globally accessible ──
window.viewEvaluation = viewEvaluation;
window.editEvaluation = editEvaluation;
window.deleteEvaluation = deleteEvaluation;
