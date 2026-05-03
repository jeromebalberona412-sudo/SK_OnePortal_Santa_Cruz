// Barangay ABYIP Module - JavaScript
// Handles pagination and filtering

const itemsPerPage = 10;
let currentPage = 1;

/**
 * Get visible rows
 */
function getVisibleRows() {
    const rows = document.querySelectorAll('.abyip-row');
    return Array.from(rows).filter(row => {
        // Exclude approved and rejected rows
        const status = row.getAttribute('data-status');
        if (status === 'Approved' || status === 'Rejected') {
            return false;
        }
        return row.style.display !== 'none';
    });
}

/**
 * Display current page
 */
function displayAbyipPage() {
    const visibleRows = getVisibleRows();
    const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
    
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const allRows = document.querySelectorAll('.abyip-row');
    allRows.forEach(row => {
        // Hide approved and rejected rows
        const status = row.getAttribute('data-status');
        if (status === 'Approved' || status === 'Rejected') {
            row.style.display = 'none';
            return;
        }
        row.style.display = 'none';
    });

    visibleRows.forEach((row, index) => {
        const pageStart = (currentPage - 1) * itemsPerPage;
        const pageEnd = pageStart + itemsPerPage;
        if (index >= pageStart && index < pageEnd) {
            row.style.display = '';
        }
    });

    const pageStart = (currentPage - 1) * itemsPerPage + 1;
    const pageEnd = Math.min(currentPage * itemsPerPage, visibleRows.length);
    
    const startEl = document.getElementById('abyipStart');
    const endEl = document.getElementById('abyipEnd');
    const totalEl = document.getElementById('abyipTotal');
    
    if (startEl) startEl.textContent = visibleRows.length > 0 ? pageStart : 0;
    if (endEl) endEl.textContent = pageEnd;
    if (totalEl) totalEl.textContent = visibleRows.length;
    
    // Update pagination buttons
    updatePaginationButtons(totalPages);
}

/**
 * Update pagination button states
 */
function updatePaginationButtons(totalPages) {
    const prevButtons = document.querySelectorAll('[onclick="prevAbyipPage()"]');
    const nextButtons = document.querySelectorAll('[onclick="nextAbyipPage()"]');
    
    prevButtons.forEach(btn => {
        btn.disabled = currentPage === 1;
    });
    
    nextButtons.forEach(btn => {
        btn.disabled = currentPage >= totalPages;
    });
}

/**
 * Next page
 */
window.nextAbyipPage = function() {
    const visibleRows = getVisibleRows();
    const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        displayAbyipPage();
    }
}

/**
 * Previous page
 */
window.prevAbyipPage = function() {
    if (currentPage > 1) {
        currentPage--;
        displayAbyipPage();
    }
}

/**
 * Filter ABYIP submissions
 */
window.filterAbyipSubmissions = function() {
    const barangayFilter = document.getElementById('barangayFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const searchTerm = document.getElementById('abyipSearchInput').value.toLowerCase();
    
    const rows = document.querySelectorAll('.abyip-row');
    rows.forEach(row => {
        const rowBarangay = row.getAttribute('data-barangay');
        const rowDate = row.getAttribute('data-date');
        const rowText = row.textContent.toLowerCase();
        
        const barangayMatch = (barangayFilter === 'all' || rowBarangay === barangayFilter);
        const searchMatch = rowText.includes(searchTerm);
        const dateMatch = isDateInRange(rowDate, dateFilter);
        
        row.style.display = (barangayMatch && searchMatch && dateMatch) ? '' : 'none';
    });
    
    currentPage = 1;
    displayAbyipPage();
}

/**
 * Check if date is in range
 */
function isDateInRange(dateStr, filter) {
    if (filter === 'all') return true;
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const rowDate = new Date(dateStr);
    rowDate.setHours(0, 0, 0, 0);
    
    const daysDiff = Math.floor((today - rowDate) / (1000 * 60 * 60 * 24));
    
    switch(filter) {
        case 'today':
            return daysDiff === 0;
        case 'week':
            return daysDiff >= 0 && daysDiff < 7;
        case 'month':
            return daysDiff >= 0 && daysDiff < 30;
        default:
            return true;
    }
}

/**
 * Perform search
 */
window.performAbyipSearch = function() {
    filterAbyipSubmissions();
}

/**
 * Initialize on DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    displayAbyipPage();
    console.log('Barangay ABYIP module loaded successfully');
    
    // Add character counter for approval notes
    const approvalNotes = document.getElementById('approvalNotes');
    if (approvalNotes) {
        approvalNotes.addEventListener('input', function() {
            const count = this.value.length;
            const counter = document.getElementById('approvalNotesCount');
            if (counter) {
                counter.textContent = count;
            }
        });
    }
    
    // Add character counter for other reason
    const otherReason = document.getElementById('otherReason');
    if (otherReason) {
        otherReason.addEventListener('input', function() {
            const count = this.value.length;
            const counter = document.getElementById('otherReasonCount');
            if (counter) {
                counter.textContent = count;
            }
        });
    }
});

/**
 * Open view modal with submission details
 */
window.openViewModal = function(button) {
    console.log('Opening modal...', button);
    const row = button.closest('.abyip-row');
    console.log('Row found:', row);
    
    // Get data from row attributes
    const barangay = row.getAttribute('data-barangay');
    const dateSubmitted = row.getAttribute('data-date');
    const submittedBy = row.getAttribute('data-submitted-by');
    const title = row.getAttribute('data-title');
    const submittedTime = row.getAttribute('data-submitted-time');
    const status = row.getAttribute('data-status');
    
    console.log('Data:', { barangay, dateSubmitted, submittedBy, title, submittedTime, status });
    
    // Store current row ID for later use
    window.currentSubmissionRow = row;
    
    // Populate modal
    document.getElementById('modalBarangay').textContent = barangay || '-';
    document.getElementById('modalDateSubmitted').textContent = dateSubmitted || '-';
    document.getElementById('modalSubmittedBy').textContent = submittedBy || '-';
    document.getElementById('modalTitle').textContent = title || '-';
    document.getElementById('modalSubmittedTime').textContent = submittedTime || '-';
    
    // Update status badge
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.textContent = status || 'Pending';
    statusBadge.className = 'status-badge';
    if (status === 'Approved') {
        statusBadge.classList.add('status-approved');
    } else if (status === 'Rejected') {
        statusBadge.classList.add('status-rejected');
    } else {
        statusBadge.classList.add('status-pending');
    }
    
    // Show/hide action buttons based on status
    const modalActions = document.getElementById('modalActions');
    if (status === 'Pending') {
        modalActions.style.display = 'flex';
    } else {
        modalActions.style.display = 'none';
    }
    
    // Reset forms
    hideApproveForm();
    hideRejectForm();
    
    // Show modal
    const modal = document.getElementById('viewModal');
    console.log('Modal element:', modal);
    modal.classList.add('active');
    console.log('Modal classes:', modal.className);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

/**
 * Show approve form
 */
window.showApproveForm = function() {
    document.getElementById('modalActions').style.display = 'none';
    document.getElementById('approveForm').style.display = 'block';
    document.getElementById('rejectForm').style.display = 'none';
    document.getElementById('approvalNotes').value = '';
    document.getElementById('approvalNotesError').textContent = '';
    document.getElementById('approvalNotesCount').textContent = '0';
}

/**
 * Hide approve form
 */
window.hideApproveForm = function() {
    document.getElementById('approveForm').style.display = 'none';
    document.getElementById('modalActions').style.display = 'flex';
}

/**
 * Show reject form
 */
window.showRejectForm = function() {
    document.getElementById('modalActions').style.display = 'none';
    document.getElementById('rejectForm').style.display = 'block';
    document.getElementById('approveForm').style.display = 'none';
    
    // Reset form
    const checkboxes = document.querySelectorAll('input[name="rejectReason"]');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('otherReasonGroup').style.display = 'none';
    document.getElementById('otherReason').value = '';
    document.getElementById('otherReasonCount').textContent = '0';
    document.getElementById('rejectReasonError').textContent = '';
    document.getElementById('otherReasonError').textContent = '';
}

/**
 * Hide reject form
 */
window.hideRejectForm = function() {
    document.getElementById('rejectForm').style.display = 'none';
    document.getElementById('modalActions').style.display = 'flex';
}

/**
 * Handle reject reason checkbox change
 */
window.handleRejectReasonChange = function() {
    const otherCheckbox = document.getElementById('otherReasonCheckbox');
    const otherReasonGroup = document.getElementById('otherReasonGroup');
    
    if (otherCheckbox.checked) {
        otherReasonGroup.style.display = 'block';
    } else {
        otherReasonGroup.style.display = 'none';
        document.getElementById('otherReason').value = '';
    }
    
    // Clear error message
    document.getElementById('rejectReasonError').textContent = '';
}

/**
 * Submit approval
 */
window.submitApproval = function() {
    const approvalNotes = document.getElementById('approvalNotes').value.trim();
    const errorEl = document.getElementById('approvalNotesError');
    
    // Validation
    if (!approvalNotes) {
        errorEl.textContent = 'Approval notes are required';
        document.getElementById('approvalNotes').classList.add('error');
        return;
    }
    
    // Clear error
    errorEl.textContent = '';
    document.getElementById('approvalNotes').classList.remove('error');
    
    // Update status in table
    if (window.currentSubmissionRow) {
        window.currentSubmissionRow.setAttribute('data-status', 'Approved');
        const statusCell = window.currentSubmissionRow.querySelector('.status-badge');
        if (statusCell) {
            statusCell.textContent = 'Approved';
            statusCell.className = 'status-badge status-approved';
        }
    }
    
    // Update modal status
    const modalStatus = document.getElementById('modalStatus');
    modalStatus.textContent = 'Approved';
    modalStatus.className = 'status-badge status-approved';
    
    // Hide forms and action buttons
    document.getElementById('approveForm').style.display = 'none';
    document.getElementById('modalActions').style.display = 'none';
    
    // Show success toast
    showToast('Submission successfully approved!', 'success');
    
    // Close modal and refresh table after 2 seconds
    setTimeout(() => {
        closeViewModal();
        displayAbyipPage(); // Refresh to hide approved row
    }, 2000);
}

/**
 * Submit rejection
 */
window.submitRejection = function() {
    const checkboxes = document.querySelectorAll('input[name="rejectReason"]:checked');
    const otherCheckbox = document.getElementById('otherReasonCheckbox');
    const otherReason = document.getElementById('otherReason').value.trim();
    const reasonError = document.getElementById('rejectReasonError');
    const otherError = document.getElementById('otherReasonError');
    
    // Clear previous errors
    reasonError.textContent = '';
    otherError.textContent = '';
    document.getElementById('otherReason').classList.remove('error');
    
    // Validation
    if (checkboxes.length === 0) {
        reasonError.textContent = 'Please select at least one rejection reason';
        return;
    }
    
    if (otherCheckbox.checked && !otherReason) {
        otherError.textContent = 'Please specify the reason';
        document.getElementById('otherReason').classList.add('error');
        return;
    }
    
    // Update status in table
    if (window.currentSubmissionRow) {
        window.currentSubmissionRow.setAttribute('data-status', 'Rejected');
        const statusCell = window.currentSubmissionRow.querySelector('.status-badge');
        if (statusCell) {
            statusCell.textContent = 'Rejected';
            statusCell.className = 'status-badge status-rejected';
        }
    }
    
    // Update modal status
    const modalStatus = document.getElementById('modalStatus');
    modalStatus.textContent = 'Rejected';
    modalStatus.className = 'status-badge status-rejected';
    
    // Hide forms and action buttons
    document.getElementById('rejectForm').style.display = 'none';
    document.getElementById('modalActions').style.display = 'none';
    
    // Show success toast
    showToast('Submission successfully rejected!', 'error');
    
    // Close modal and refresh table after 2 seconds
    setTimeout(() => {
        closeViewModal();
        displayAbyipPage(); // Refresh to hide rejected row
    }, 2000);
}

/**
 * Download ABYIP file
 */
window.downloadABYIPFile = function(event) {
    event.preventDefault();
    
    // Create a sample PDF blob
    const pdfContent = '%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/Resources <<\n/Font <<\n/F1 4 0 R\n>>\n>>\n/MediaBox [0 0 612 792]\n/Contents 5 0 R\n>>\nendobj\n4 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n5 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 24 Tf\n100 700 Td\n(ABYIP Document) Tj\nET\nendstream\nendobj\nxref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000262 00000 n\n0000000341 00000 n\ntrailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n433\n%%EOF';
    
    const blob = new Blob([pdfContent], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'abyip.pdf';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // Show toast
    showToast('Downloading abyip.pdf...', 'success');
}

/**
 * Show toast notification
 */
window.showToast = function(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = toast.querySelector('.toast-message');
    const toastIcon = toast.querySelector('.toast-icon');
    
    toastMessage.textContent = message;
    toast.className = 'toast show ' + type;
    
    if (type === 'success') {
        toastIcon.className = 'toast-icon fas fa-check-circle';
    } else {
        toastIcon.className = 'toast-icon fas fa-times-circle';
    }
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

/**
 * Close view modal
 */
window.closeViewModal = function() {
    const modal = document.getElementById('viewModal');
    modal.classList.remove('active', 'fullscreen');
    
    // Reset fullscreen button icon
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        fullscreenBtn.title = 'Fullscreen';
    }
    
    // Restore body scroll
    document.body.style.overflow = '';
}

/**
 * Toggle fullscreen mode
 */
window.toggleFullscreen = function() {
    const modal = document.getElementById('viewModal');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    
    if (modal.classList.contains('fullscreen')) {
        // Exit fullscreen
        modal.classList.remove('fullscreen');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            fullscreenBtn.title = 'Fullscreen';
        }
    } else {
        // Enter fullscreen
        modal.classList.add('fullscreen');
        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
            fullscreenBtn.title = 'Restore';
        }
    }
}

/**
 * Close modal when clicking outside
 */
document.addEventListener('click', function(event) {
    const modal = document.getElementById('viewModal');
    if (event.target === modal) {
        closeViewModal();
    }
});

/**
 * Close modal with Escape key
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('viewModal');
        if (modal && modal.classList.contains('active')) {
            closeViewModal();
        }
    }
});
