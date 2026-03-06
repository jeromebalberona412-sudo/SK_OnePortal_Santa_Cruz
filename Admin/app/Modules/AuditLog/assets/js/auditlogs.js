// Audit Logs JavaScript functionality

// Global variables
let currentPage = 1;
let totalPages = 12;
let itemsPerPage = 10;
let totalItems = 120;
let currentFilters = {
    search: '',
    event: '',
    dateFrom: '',
    dateTo: ''
};

// Sample data for demonstration
const sampleAuditLogs = [
    { id: 1, user_id: 1, event: 'Login', ip_address: '192.168.1.100', user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', metadata: {'user_id': 1, 'timestamp': '2024-03-02 10:15:30'}, created_at: '2024-03-02 10:15:30' },
    { id: 2, user_id: 2, event: 'Update', ip_address: '192.168.1.101', user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', metadata: {'user_id': 2, 'fields': ['name', 'email']}, created_at: '2024-03-02 09:45:22' },
    { id: 3, user_id: 3, event: 'Delete', ip_address: '192.168.1.102', user_agent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', metadata: {'user_id': 3, 'deleted_by': 1}, created_at: '2024-03-02 08:30:15' },
    { id: 4, user_id: 1, event: 'Logout', ip_address: '192.168.1.100', user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', metadata: {'user_id': 1, 'session_duration': '2h 15m'}, created_at: '2024-03-02 08:00:00' },
    { id: 5, user_id: 4, event: 'Create', ip_address: '192.168.1.103', user_agent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', metadata: {'user_id': 4, 'role': 'sk_member'}, created_at: '2024-03-01 16:45:30' },
    { id: 6, user_id: 5, event: 'View', ip_address: '192.168.1.104', user_agent: 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0', metadata: {'user_id': 5, 'page': '/dashboard'}, created_at: '2024-03-01 15:20:45' },
    { id: 7, user_id: 2, event: 'Update', ip_address: '192.168.1.101', user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', metadata: {'user_id': 2, 'method': 'manual'}, created_at: '2024-03-01 14:10:20' },
    { id: 8, user_id: 1, event: 'Login', ip_address: '192.168.1.100', user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', metadata: {'user_id': 1, 'timestamp': '2024-03-01 13:00:00'}, created_at: '2024-03-01 13:00:00' },
    { id: 9, user_id: 6, event: 'Delete', ip_address: '192.168.1.105', user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', metadata: {'user_id': 6, 'record_type': 'audit_log', 'record_id': 123}, created_at: '2024-03-01 11:30:15' },
    { id: 10, user_id: 7, event: 'Create', ip_address: '192.168.1.106', user_agent: 'Mozilla/5.0 (iPad; CPU OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15', metadata: {'user_id': 7, 'auto_generated': true}, created_at: '2024-03-01 10:45:30' }
];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updatePagination();
    updateSummary();
    
    // Add active state to sidebar
    setActiveSidebar();
    
    // Add enter key support for search
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchLogs();
        }
    });
});

// Set active sidebar item
function setActiveSidebar() {
    const auditlogsBtn = document.querySelector('.nav-link.auditlogs-btn');
    if (auditlogsBtn) {
        auditlogsBtn.classList.add('active');
    }
}

// Search functionality
function searchLogs() {
    const searchValue = document.getElementById('searchInput').value.trim();
    currentFilters.search = searchValue;
    currentPage = 1;
    
    // Show loading state
    showLoading();
    
    // Simulate API call
    setTimeout(() => {
        filterTableData();
        hideLoading();
        updateSummary();
        updatePagination();
    }, 300);
}

// Filter functionality
function filterLogs() {
    currentFilters.event = document.getElementById('eventFilter').value;
    currentFilters.dateFrom = document.getElementById('dateFrom').value;
    currentFilters.dateTo = document.getElementById('dateTo').value;
    currentPage = 1;
    
    // Show loading state
    showLoading();
    
    // Simulate API call
    setTimeout(() => {
        filterTableData();
        hideLoading();
        updateSummary();
        updatePagination();
    }, 300);
}

// Clear all filters
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('eventFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    
    currentFilters = {
        search: '',
        event: '',
        dateFrom: '',
        dateTo: ''
    };
    
    currentPage = 1;
    
    // Show loading state
    showLoading();
    
    // Simulate API call
    setTimeout(() => {
        filterTableData();
        hideLoading();
        updateSummary();
        updatePagination();
    }, 300);
}

// Filter table data based on current filters
function filterTableData() {
    const tbody = document.getElementById('auditlogsTableBody');
    const rows = tbody.getElementsByTagName('tr');
    
    for (let row of rows) {
        const userId = row.cells[0].textContent.trim();
        const event = row.cells[1].textContent.trim();
        const createdAt = row.cells[5].textContent.trim();
        
        let showRow = true;
        
        // Search filter
        if (currentFilters.search && 
            !userId.toLowerCase().includes(currentFilters.search.toLowerCase()) && 
            !event.toLowerCase().includes(currentFilters.search.toLowerCase())) {
            showRow = false;
        }
        
        // Event filter
        if (currentFilters.event && !event.includes(currentFilters.event)) {
            showRow = false;
        }
        
        // Date range filter
        if (currentFilters.dateFrom && createdAt < currentFilters.dateFrom) {
            showRow = false;
        }
        
        if (currentFilters.dateTo && createdAt > currentFilters.dateTo + ' 23:59:59') {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    }
}

// Show details modal
function showDetails(description, metadata) {
    const modal = document.getElementById('detailsModal');
    const descriptionElement = document.getElementById('eventDescription');
    const metadataElement = document.getElementById('metadataContent');
    
    descriptionElement.textContent = description;
    metadataElement.textContent = JSON.stringify(metadata, null, 2);
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close details modal
function closeDetailsModal() {
    const modal = document.getElementById('detailsModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
const detailsModalEl = document.getElementById('detailsModal');
if (detailsModalEl) {
    detailsModalEl.addEventListener('click', function(e) {
        if (e.target === this) {
            const modal = document.getElementById('detailsModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('detailsModal');
        if (modal && modal.classList.contains('active')) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            closeDetailsModal();
        }
    }
});

// Pagination functions
function changePage(direction) {
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    }
    
    updatePagination();
    updateSummary();
    loadPageData();
}

function goToPage(page) {
    currentPage = page;
    updatePagination();
    updateSummary();
    loadPageData();
}

function updatePagination() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const paginationNumbers = document.getElementById('paginationNumbers');
    
    // Update button states
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
    
    // Update page numbers
    let pageNumbersHTML = '';
    
    if (totalPages <= 7) {
        // Show all pages if total is small
        for (let i = 1; i <= totalPages; i++) {
            pageNumbersHTML += `<button class="pagination-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }
    } else {
        // Show smart pagination for larger totals
        if (currentPage <= 3) {
            for (let i = 1; i <= 5; i++) {
                pageNumbersHTML += `<button class="pagination-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }
            pageNumbersHTML += '<span class="pagination-dots">...</span>';
            pageNumbersHTML += `<button class="pagination-number" onclick="goToPage(${totalPages})">${totalPages}</button>`;
        } else if (currentPage >= totalPages - 2) {
            pageNumbersHTML += `<button class="pagination-number" onclick="goToPage(1)">1</button>`;
            pageNumbersHTML += '<span class="pagination-dots">...</span>';
            for (let i = totalPages - 4; i <= totalPages; i++) {
                pageNumbersHTML += `<button class="pagination-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }
        } else {
            pageNumbersHTML += `<button class="pagination-number" onclick="goToPage(1)">1</button>`;
            pageNumbersHTML += '<span class="pagination-dots">...</span>';
            for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                pageNumbersHTML += `<button class="pagination-number ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }
            pageNumbersHTML += '<span class="pagination-dots">...</span>';
            pageNumbersHTML += `<button class="pagination-number" onclick="goToPage(${totalPages})">${totalPages}</button>`;
        }
    }
    
    paginationNumbers.innerHTML = pageNumbersHTML;
}

function updateSummary() {
    const summaryText = document.getElementById('summaryText');
    const paginationData = calculatePagination();
    const startItem = (paginationData.currentPage - 1) * paginationData.itemsPerPage + 1;
    const endItem = Math.min(paginationData.currentPage * paginationData.itemsPerPage, paginationData.totalItems);
    summaryText.textContent = `Showing ${startItem}-${endItem} of ${paginationData.totalItems} logs`;
}

function loadPageData() {
    // Show loading state
    showLoading();
    
    // Simulate API call to load page data
    setTimeout(() => {
        hideLoading();
        // In a real application, this would fetch data from the server
        // For now, we're just using the static data in the HTML
    }, 300);
}

function calculatePagination() {
    // Calculate pagination logic
    return {
        currentPage: currentPage,
        totalPages: totalPages,
        itemsPerPage: itemsPerPage,
        totalItems: totalItems
    };
}

// Loading state functions
function showLoading() {
    const container = document.querySelector('.manage-account-container');
    container.classList.add('loading');
}

function hideLoading() {
    const container = document.querySelector('.manage-account-container');
    container.classList.remove('loading');
}

// Utility function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Utility function to truncate text
function truncateText(text, maxLength) {
    if (text.length <= maxLength) {
        return text;
    }
    return text.substring(0, maxLength) + '...';
}

// Export functions for global access
window.searchLogs = searchLogs;
window.filterLogs = filterLogs;
window.clearFilters = clearFilters;
window.showDetails = showDetails;
window.closeDetailsModal = closeDetailsModal;
window.changePage = changePage;
window.goToPage = goToPage;
window.calculatePagination = calculatePagination;

// Export Audit Logs Function
window.exportAuditLogs = function() {
    // Show loading state
    showLoading();
    
    // Simulate export process
    setTimeout(() => {
        // Get current filter values
        const searchValue = document.getElementById('searchInput').value;
        const eventFilter = document.getElementById('eventFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        // Create CSV content (simplified example)
        let csvContent = "User ID,Event,IP Address,User Agent,Metadata,Created At\n";
        
        // Get visible rows from table
        const tbody = document.getElementById('auditlogsTableBody');
        const rows = tbody.getElementsByTagName('tr');
        
        for (let row of rows) {
            if (row.style.display !== 'none') {
                const cells = row.getElementsByTagName('td');
                const rowData = [
                    cells[0].textContent,
                    cells[1].textContent.trim(),
                    cells[2].textContent,
                    cells[3].textContent,
                    cells[4].textContent,
                    cells[5].textContent
                ];
                csvContent += rowData.map(cell => `"${cell}"`).join(',') + '\n';
            }
        }
        
        // Create download link
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        // Generate filename with timestamp
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        link.setAttribute('href', url);
        link.setAttribute('download', `audit-logs-${timestamp}.csv`);
        link.style.visibility = 'hidden';
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        hideLoading();
        
        // Show success message (optional)
        console.log('Audit logs exported successfully!');
    }, 1000);
};