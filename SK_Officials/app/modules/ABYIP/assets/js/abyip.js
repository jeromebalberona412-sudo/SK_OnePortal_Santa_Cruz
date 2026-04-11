// ABYIP Document JavaScript

// Print functionality
function printDocument() {
    window.print();
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for the Create ABYIP button
    const addAbyipBtn = document.getElementById('addAbyipBtn');
    if (addAbyipBtn) {
        addAbyipBtn.addEventListener('click', openAbyipModal);
    }
});

// Load saved document data
function loadDocument() {
    const savedData = localStorage.getItem('abyip_document_data');

    if (savedData) {
        const documentData = JSON.parse(savedData);
        const editableElements = document.querySelectorAll('[contenteditable="true"]');

        Object.keys(documentData).forEach((key, index) => {
            if (editableElements[index]) {
                editableElements[index].textContent = documentData[key].content;
            }
        });

        updateTotals();
    }
}

// Update totals calculation
function updateTotals() {
    // Try modal table first, then main table
    let table = document.getElementById('abyipModalTable');
    if (!table) {
        table = document.getElementById('abyipTable');
    }
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr:not(.section-header):not(.subsection-header):not(.category-header):not(.total-row)');

    let totalMOOE = 0;
    let totalCO = 0;
    let grandTotal = 0;

    rows.forEach(row => {
        const mooeCell = row.querySelector('td:nth-child(7)');
        const coCell = row.querySelector('td:nth-child(8)');
        const totalCell = row.querySelector('td:nth-child(9)');

        if (mooeCell && coCell && totalCell) {
            const mooe = parseFloat(mooeCell.textContent.replace(/,/g, '')) || 0;
            const co = parseFloat(coCell.textContent.replace(/,/g, '')) || 0;
            const total = parseFloat(totalCell.textContent.replace(/,/g, '')) || 0;

            totalMOOE += mooe;
            totalCO += co;
            grandTotal += total;
        }
    });

    // Update total row
    const totalRow = table.querySelector('.total-row');
    if (totalRow) {
        const coCell = totalRow.querySelector('td:nth-child(8)');
        const totalCell = totalRow.querySelector('td:nth-child(9)');
        if (coCell && totalCell) {
            coCell.textContent = formatCurrency(totalCO);
            totalCell.textContent = formatCurrency(grandTotal);
        }
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount).replace('₱', '');
}


// Add input event listeners for automatic total calculation
function addCalculationListeners() {
    // Try modal table first, then main table
    let table = document.getElementById('abyipModalTable');
    if (!table) {
        table = document.getElementById('abyipTable');
    }
    if (!table) return;

    // Remove existing listeners to avoid duplicates
    table.removeEventListener('input', handleTableInput);

    function handleTableInput(e) {
        if (e.target.matches('td.number[contenteditable="true"]')) {
            // Calculate row total when MOOE or CO changes
            const row = e.target.closest('tr');
            if (row && !row.classList.contains('total-row')) {
                const mooeCell = row.querySelector('td:nth-child(7)');
                const coCell = row.querySelector('td:nth-child(8)');
                const totalCell = row.querySelector('td:nth-child(9)');

                if (e.target === mooeCell || e.target === coCell) {
                    const mooe = parseFloat(mooeCell.textContent.replace(/,/g, '')) || 0;
                    const co = parseFloat(coCell.textContent.replace(/,/g, '')) || 0;
                    totalCell.textContent = formatCurrency(mooe + co);
                }
            }

            // Update grand total
            updateTotals();
        }
    }

    table.addEventListener('input', handleTableInput);
}

// Add keyboard shortcuts
function addKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printDocument();
        }
    });
}


// Sidebar state management
function updateMainContentForSidebar() {
    const mainContent = document.querySelector('.main-content');
    const sidebar = document.querySelector('.main-sidebar');

    if (!mainContent || !sidebar) return;

    // Check if sidebar is open or closed
    if (sidebar.classList.contains('collapsed') || sidebar.style.width === '70px') {
        mainContent.classList.remove('sidebar-open');
        mainContent.classList.add('sidebar-closed');
    } else {
        mainContent.classList.remove('sidebar-closed');
        mainContent.classList.add('sidebar-open');
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function () {
    // Load saved data
    loadDocument();

    // Add event listeners
    addCalculationListeners();
    addKeyboardShortcuts();

    // Initial total calculation
    updateTotals();

    // Initial sidebar state setup
    updateMainContentForSidebar();

    // Listen for sidebar changes
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                updateMainContentForSidebar();
            }
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                updateMainContentForSidebar();
            }
        });
    });

    const sidebar = document.querySelector('.main-sidebar');
    if (sidebar) {
        observer.observe(sidebar, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }
});

// Add validation for numeric fields
function addNumericValidation() {
    document.addEventListener('input', function (e) {
        if (e.target.matches('td.number[contenteditable="true"]')) {
            // Remove any non-numeric characters except decimal point
            let value = e.target.textContent.replace(/[^\d.]/g, '');

            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }

            e.target.textContent = value;
        }
    });
}

// Initialize numeric validation
document.addEventListener('DOMContentLoaded', addNumericValidation);

// ABYIP Modal and Records Management
let abyipRecords = [];
let currentEditId = null;
let isMaximized = false;

// Load records from localStorage
function loadRecords() {
    const savedRecords = localStorage.getItem('abyip_records');
    if (savedRecords) {
        abyipRecords = JSON.parse(savedRecords);
    } else {
        // Add sample records
        abyipRecords = [
            {
                id: 1,
                title: 'ABYIP 2025 - Initial Draft',
                dateCreated: '2025-01-15',
                status: 'draft',
                data: null
            },
            {
                id: 2,
                title: 'ABYIP 2025 - Final Version',
                dateCreated: '2025-02-20',
                status: 'active',
                data: null
            },
            {
                id: 3,
                title: 'ABYIP 2025 - Pending Review',
                dateCreated: '2025-03-10',
                status: 'pending',
                data: null
            }
        ];
        saveRecords();
    }
    displayRecords();
}

// Save records to localStorage
function saveRecords() {
    localStorage.setItem('abyip_records', JSON.stringify(abyipRecords));
}

// Display records in table
function displayRecords() {
    const tbody = document.getElementById('recordsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (abyipRecords.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">No records found</td></tr>';
        return;
    }

    abyipRecords.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${record.id}</td>
            <td>${record.title}</td>
            <td>${formatDate(record.dateCreated)}</td>
            <td>
                <div class="action-buttons-cell">
                    <button class="btn-action-view" onclick="viewRecord(${record.id})">View</button>
                    <button class="btn-action-edit" onclick="editRecord(${record.id})">Edit</button>
                    <button class="btn-action-delete" onclick="deleteRecord(${record.id})">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Open ABYIP Modal
function openAbyipModal() {
    const modal = document.getElementById('abyipModal');
    const modalHeader = document.querySelector('.modal-header');
    const modalTitle = document.querySelector('.modal-header h3');

    // Show modal
    modal.classList.add('active');
    currentEditId = null;

    // Reset header to green (create mode)
    if (modalHeader) {
        modalHeader.classList.remove('edit-mode');
    }
    if (modalTitle) {
        modalTitle.textContent = 'Create Annual Barangay Youth Investment Program (ABYIP)';
    }

    // Reset form to default state
    resetModalForm();

    // Initialize calculation listeners for modal table
    setTimeout(() => {
        addCalculationListeners();
        updateTotals();
    }, 100);
}

// Close ABYIP Modal
function closeAbyipModal() {
    const modal = document.getElementById('abyipModal');
    modal.classList.remove('active');

    // Reset maximized state
    const container = document.getElementById('modalContainer');
    container.classList.remove('maximized');
    isMaximized = false;

    currentEditId = null;
}

// Minimize Modal
function minimizeModal() {
    const modal = document.getElementById('abyipModal');
    const minimizedBar = document.getElementById('minimizedModalBar');

    modal.classList.remove('active');
    minimizedBar.style.display = 'flex';
}

// Restore Modal
function restoreModal() {
    const modal = document.getElementById('abyipModal');
    const minimizedBar = document.getElementById('minimizedModalBar');

    minimizedBar.style.display = 'none';
    modal.classList.add('active');
}

// Maximize Modal
function maximizeModal() {
    const container = document.getElementById('modalContainer');
    isMaximized = !isMaximized;

    if (isMaximized) {
        container.classList.add('maximized');
    } else {
        container.classList.remove('maximized');
    }
}

// Save ABYIP
function saveAbyip() {
    const title = prompt('Enter ABYIP Title:');
    if (!title) return;

    const tableData = extractTableData();

    if (currentEditId) {
        // Update existing record
        const record = abyipRecords.find(r => r.id === currentEditId);
        if (record) {
            record.title = title;
            record.data = tableData;
            record.dateCreated = new Date().toISOString().split('T')[0];
        }
    } else {
        // Create new record
        const newRecord = {
            id: abyipRecords.length > 0 ? Math.max(...abyipRecords.map(r => r.id)) + 1 : 1,
            title: title,
            dateCreated: new Date().toISOString().split('T')[0],
            status: 'draft',
            data: tableData
        };
        abyipRecords.push(newRecord);
    }

    saveRecords();
    displayRecords();
    closeAbyipModal();

    // Show success message
    showNotification('ABYIP saved successfully!', 'success');
}

// Extract table data from modal
function extractTableData() {
    const modalTable = document.getElementById('abyipModalTable');
    if (!modalTable) return null;

    const rows = modalTable.querySelectorAll('tbody tr');
    const data = [];

    rows.forEach(row => {
        if (!row.classList.contains('section-header') &&
            !row.classList.contains('subsection-header') &&
            !row.classList.contains('category-header') &&
            !row.classList.contains('total-row')) {

            const cells = row.querySelectorAll('td');
            const rowData = [];
            cells.forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            data.push(rowData);
        }
    });

    return data;
}

// Reset modal form
function resetModalForm() {
    // Clear any edited content
    const editableCells = document.querySelectorAll('#modalBody [contenteditable="true"]');
    editableCells.forEach(cell => {
        // Reset to original values if needed
    });
}

// View Record
function viewRecord(id) {
    const record = abyipRecords.find(r => r.id === id);
    if (!record) return;

    // Open modal with record data
    openAbyipModal();
    currentEditId = id;

    // Apply view mode styling
    setTimeout(() => {
        const modalHeader = document.querySelector('.modal-header');
        const modalTitle = document.querySelector('.modal-header h3');
        if (modalHeader) {
            modalHeader.classList.add('view-mode');
        }
        if (modalTitle) {
            modalTitle.textContent = 'View Annual Barangay Youth Investment Program (ABYIP)';
        }
    }, 100);

    // Load record data into modal table
    if (record.data) {
        loadTableData(record.data);
    }

    // Make modal read-only
    const editableCells = document.querySelectorAll('#modalBody [contenteditable="true"]');
    editableCells.forEach(cell => {
        cell.contentEditable = false;
        cell.style.backgroundColor = '#f5f5f5';
    });
}

// Edit Record
function editRecord(id) {
    const record = abyipRecords.find(r => r.id === id);
    if (!record) return;

    openAbyipModal();
    currentEditId = id;

    // Apply edit mode styling
    setTimeout(() => {
        const modalHeader = document.querySelector('.modal-header');
        const modalTitle = document.querySelector('.modal-header h3');
        if (modalHeader) {
            modalHeader.classList.add('edit-mode');
        }
        if (modalTitle) {
            modalTitle.textContent = 'Edit Annual Barangay Youth Investment Program (ABYIP)';
        }
    }, 100);

    // Load record data into modal
    setTimeout(() => {
        const modalBody = document.getElementById('modalBody');
        if (!modalBody) return;

        const table = modalBody.querySelector('.abyip-table');
        if (!table) return;

        const cells = table.querySelectorAll('td[contenteditable="true"]');
        const recordData = [
            record.title || '',
            record.date || '',
            record.venue || '',
            record.participants || '',
            record.budget || '',
            record.remarks || ''
        ];

        cells.forEach((cell, index) => {
            if (cells[index]) {
                cells[index].textContent = recordData[index] || '';
                cells[index].style.color = 'black';
            }
        });
    }, 300);

    showNotification('Edit mode activated for record: ' + record.title, 'info');
}

// Delete Record
let recordToDelete = null;

function deleteRecord(id) {
    recordToDelete = id;
    openDeleteConfirm();
}

function openDeleteConfirm() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

function closeDeleteConfirm() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
    recordToDelete = null;
}

function confirmDelete() {
    if (!recordToDelete) return;

    // Remove record from array (pure frontend)
    abyipRecords = abyipRecords.filter(r => r.id !== recordToDelete);

    // Save to localStorage for persistence
    localStorage.setItem('abyipRecords', JSON.stringify(abyipRecords));

    // Refresh display
    displayRecords();

    // Close confirmation modal
    closeDeleteConfirm();

    // Show success message
    showNotification('ABYIP record deleted successfully!', 'success');

    // Clear the record to delete
    recordToDelete = null;
}

// Print Records
function printRecords() {
    const recordsSection = document.querySelector('.records-section');
    if (!recordsSection) return;

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    const recordsHTML = recordsSection.outerHTML;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>ABYIP Records</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
                .status-active { background-color: #d4edda; color: #155724; }
                .status-pending { background-color: #fff3cd; color: #856404; }
                .status-inactive { background-color: #f8d7da; color: #721c24; }
                .status-draft { background-color: #e2e3e5; color: #383d41; }
                .action-buttons-cell { display: none; }
            </style>
        </head>
        <body>
            <h1>ABYIP Records</h1>
            <p>Printed on: ${new Date().toLocaleDateString()}</p>
            ${recordsHTML}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

// Show notification
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 2000;
        animation: slideIn 0.3s ease;
    `;

    // Set background color based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        case 'info':
            notification.style.backgroundColor = '#17a2b8';
            break;
        default:
            notification.style.backgroundColor = '#6c757d';
    }

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function (e) {
    const modal = document.getElementById('abyipModal');
    const modalContainer = document.getElementById('modalContainer');

    if (e.target === modal && modal.classList.contains('active')) {
        closeAbyipModal();
    }
});

// Keyboard shortcuts for modal
document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('abyipModal');

    if (modal.classList.contains('active')) {
        // Escape to close
        if (e.key === 'Escape') {
            closeAbyipModal();
        }
        // Ctrl+S to save
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            saveAbyip();
        }
    }
});

// Initialize records on page load
document.addEventListener('DOMContentLoaded', function () {
    loadRecords();
});

// Toggle Maximize/Restore functionality
function toggleMaximize() {
    const modal = document.getElementById('abyipModal');
    const container = document.getElementById('modalContainer');
    const toggleBtn = document.getElementById('maximizeToggleBtn');

    if (!modal || !container || !toggleBtn) return;

    if (modal.classList.contains('modal-maximized')) {
        // Restore to normal size
        modal.classList.remove('modal-maximized');
        toggleBtn.textContent = '□'; // Single box for maximize
        toggleBtn.title = 'Maximize';
    } else {
        // Maximize
        modal.classList.add('modal-maximized');
        toggleBtn.textContent = '❐'; // Double box for restore
        toggleBtn.title = 'Restore';
    }
}

// Make all functions globally accessible
window.openAbyipModal = openAbyipModal;
window.closeAbyipModal = closeAbyipModal;
window.maximizeModal = maximizeModal;
window.saveAbyip = saveAbyip;
window.toggleMaximize = toggleMaximize;
window.viewRecord = viewRecord;
window.editRecord = editRecord;
window.deleteRecord = deleteRecord;
window.printRecords = printRecords;
