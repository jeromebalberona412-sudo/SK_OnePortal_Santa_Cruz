// ABYIP Document JavaScript

// Print functionality
function printDocument() {
    window.print();
}

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
    const table = document.getElementById('abyipTable');
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
    const table = document.getElementById('abyipTable');
    if (!table) return;

    table.addEventListener('input', function (e) {
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
    });
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
