// Program Accomplishment Module JavaScript

// State Management
const PAState = {
    programs: [],
    accomplishmentReports: [],
    images: [],
    filteredPrograms: [],
    currentPage: 1,
    itemsPerPage: 10,
    currentMode: 'create', // 'create' or 'edit'
    currentReportId: null,
    currentProgramId: null,
    uploadedImages: [],
    existingImages: [],
    lightboxImages: [],
    currentLightboxIndex: 0,
    filters: {
        search: '',
        barangay: '',
        category: '',
        dateFrom: '',
        dateTo: '',
        reportStatus: ''
    }
};

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showToast(message, type = 'success') {
    const container = document.getElementById('paToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `pa-toast pa-toast-${type}`;
    
    const icon = type === 'success' 
        ? '<svg class="pa-toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg class="pa-toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';

    toast.innerHTML = `
        ${icon}
        <span class="pa-toast-message">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Statistics Update
function updateStatistics() {
    const totalPrograms = PAState.programs.filter(p => p.status === 'Completed').length;
    const reportsCount = PAState.accomplishmentReports.length;
    const pendingReports = totalPrograms - reportsCount;
    const totalImages = PAState.images.length;

    document.getElementById('paStatTotalPrograms').textContent = totalPrograms;
    document.getElementById('paStatReports').textContent = reportsCount;
    document.getElementById('paStatPending').textContent = pendingReports;
    document.getElementById('paStatImages').textContent = totalImages;
}

// Table Rendering
function renderTable() {
    const tbody = document.getElementById('paTableBody');
    if (!tbody) return;

    // Apply filters
    let filtered = PAState.programs.filter(program => {
        if (program.status !== 'Completed') return false;

        // Search filter
        if (PAState.filters.search) {
            const searchLower = PAState.filters.search.toLowerCase();
            const searchableText = `${program.program_name} ${program.program_type} ${program.barangay} ${program.committee} ${program.creator}`.toLowerCase();
            if (!searchableText.includes(searchLower)) return false;
        }

        // Barangay filter
        if (PAState.filters.barangay && program.barangay !== PAState.filters.barangay) return false;

        // Category filter (using program_type)
        if (PAState.filters.category && program.program_type !== PAState.filters.category) return false;

        // Date range filter
        if (PAState.filters.dateFrom) {
            const dateFrom = new Date(PAState.filters.dateFrom);
            const endDate = new Date(program.end_date);
            if (endDate < dateFrom) return false;
        }
        if (PAState.filters.dateTo) {
            const dateTo = new Date(PAState.filters.dateTo);
            const endDate = new Date(program.end_date);
            if (endDate > dateTo) return false;
        }

        // Report status filter
        const hasReport = PAState.accomplishmentReports.some(r => r.program_id === program.id);
        if (PAState.filters.reportStatus === 'With Report' && !hasReport) return false;
        if (PAState.filters.reportStatus === 'Without Report' && hasReport) return false;

        return true;
    });

    PAState.filteredPrograms = filtered;

    // Pagination
    const totalPages = Math.ceil(filtered.length / PAState.itemsPerPage);
    const startIndex = (PAState.currentPage - 1) * PAState.itemsPerPage;
    const endIndex = startIndex + PAState.itemsPerPage;
    const pageData = filtered.slice(startIndex, endIndex);

    // Clear table
    tbody.innerHTML = '';

    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="pa-empty-state">
                    <div class="pa-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="9" y1="21" x2="9" y2="9"></line>
                        </svg>
                    </div>
                    <div class="pa-empty-title">No completed programs found</div>
                    <div class="pa-empty-text">Try adjusting your filters or search criteria</div>
                </td>
            </tr>
        `;
    } else {
        pageData.forEach(program => {
            const report = PAState.accomplishmentReports.find(r => r.program_id === program.id);
            const hasReport = !!report;
            const row = document.createElement('tr');
            
            row.innerHTML = `
                <td><strong>${program.program_name}</strong></td>
                <td>${program.program_type || 'N/A'}</td>
                <td>${program.barangay || 'N/A'}</td>
                <td>${program.committee || 'N/A'}</td>
                <td>${formatDate(program.start_date)}</td>
                <td>${formatDate(program.end_date)}</td>
                <td>${formatCurrency(program.participation_quantity || 0)}</td>
                <td>${program.creator || 'N/A'}</td>
                <td>0</td>
                <td><span class="pa-status-badge pa-status-completed">Completed</span></td>
                <td>
                    <span class="pa-status-badge ${hasReport ? 'pa-status-with-report' : 'pa-status-without-report'}">
                        ${hasReport ? 'With Report' : 'Without Report'}
                    </span>
                </td>
                <td class="col-actions">
                    <div class="pa-actions">
                        <button type="button" class="pa-action-btn view" data-action="view-program" data-id="${program.id}" title="View Program">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        ${hasReport ? `
                            <button type="button" class="pa-action-btn view" data-action="view-report" data-id="${report.id}" title="View Report">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </button>
                            <button type="button" class="pa-action-btn edit" data-action="edit-report" data-id="${report.id}" title="Edit Report">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button type="button" class="pa-action-btn delete" data-action="delete-report" data-id="${report.id}" title="Delete Report">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        ` : `
                            <button type="button" class="pa-action-btn" data-action="create-report" data-id="${program.id}" title="Create Report" style="color: #22c55e;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Update pagination
    updatePagination(filtered.length, totalPages);
}

function updatePagination(totalItems, totalPages) {
    const showingInfo = document.getElementById('paShowingInfo');
    const pageInfo = document.getElementById('paPageInfo');
    const prevBtn = document.getElementById('paPrevBtn');
    const nextBtn = document.getElementById('paNextBtn');

    const startIndex = totalItems === 0 ? 0 : (PAState.currentPage - 1) * PAState.itemsPerPage + 1;
    const endIndex = Math.min(PAState.currentPage * PAState.itemsPerPage, totalItems);

    showingInfo.textContent = `Showing ${startIndex}-${endIndex} of ${totalItems} programs`;
    pageInfo.textContent = `Page ${PAState.currentPage} of ${totalPages || 1}`;

    prevBtn.disabled = PAState.currentPage === 1;
    nextBtn.disabled = PAState.currentPage === totalPages || totalPages === 0;
}

// Modal Management
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Form Management
function resetForm() {
    document.getElementById('paForm').reset();
    PAState.uploadedImages = [];
    PAState.existingImages = [];
    PAState.currentReportId = null;
    PAState.currentProgramId = null;
    
    document.getElementById('paImagePreview').innerHTML = '';
    document.getElementById('paUploadProgress').innerHTML = '';
    document.getElementById('paExistingImages').innerHTML = '';
    document.getElementById('paExistingImagesSection').style.display = 'none';
    document.getElementById('paBudgetValidation').classList.remove('show');
    document.getElementById('paImageValidation').classList.remove('show');
    
    updateBudgetSummary();
}

function loadProgramIntoForm(program) {
    document.getElementById('paProgram').value = program.program_name || '';
    document.getElementById('paBarangay').value = program.barangay || '';
    document.getElementById('paVenue').value = program.program_type || '';
    document.getElementById('paPersonResponsible').value = program.creator || '';
    document.getElementById('paBudgetAllocated').value = formatCurrency(program.participation_quantity || 0);
    document.getElementById('paBudgetAllocatedDisplay').textContent = formatCurrency(program.participation_quantity || 0);
    document.getElementById('paDateStarted').value = formatDate(program.start_date);
    document.getElementById('paDateCompleted').value = formatDate(program.end_date);
    
    PAState.currentProgramId = program.id;
}

function loadReportIntoForm(report) {
    document.getElementById('paTitle').value = report.title || '';
    document.getElementById('paDescription').value = report.description || '';
    document.getElementById('paObjectives').value = report.objectives || '';
    document.getElementById('paImplementationSummary').value = report.implementation_summary || '';
    document.getElementById('paLessonsLearned').value = report.lessons_learned || '';
    document.getElementById('paRecommendations').value = report.recommendations || '';
    document.getElementById('paParticipantsCount').value = report.participants_count || '';
    document.getElementById('paActualExpense').value = report.actual_expense || '';
    document.getElementById('paRemarks').value = report.remarks || '';
    
    PAState.currentReportId = report.id;
    
    // Load existing images
    const reportImages = PAState.images.filter(img => img.accomplishment_report_id === report.id);
    PAState.existingImages = reportImages;
    renderExistingImages();
    
    updateBudgetSummary();
}

function renderExistingImages() {
    const container = document.getElementById('paExistingImages');
    const section = document.getElementById('paExistingImagesSection');
    
    if (PAState.existingImages.length === 0) {
        container.innerHTML = '';
        section.style.display = 'none';
        return;
    }
    
    section.style.display = 'block';
    container.innerHTML = PAState.existingImages.map((img, index) => `
        <div class="pa-existing-image-item" data-index="${index}" data-id="${img.id}">
            <img src="${img.secure_url}" alt="${img.display_name || 'Image'}" loading="lazy">
            <div class="pa-existing-image-caption">${img.display_name || `Image ${index + 1}`}</div>
        </div>
    `).join('');
}

// Budget Calculation
function updateBudgetSummary() {
    const budgetAllocatedText = document.getElementById('paBudgetAllocated').value;
    const actualExpense = parseFloat(document.getElementById('paActualExpense').value) || 0;
    
    // Parse budget allocated (remove currency formatting)
    const budgetAllocated = parseFloat(budgetAllocatedText.replace(/[^0-9.-]+/g, '')) || 0;
    
    const remainingBudget = budgetAllocated - actualExpense;
    const utilization = budgetAllocated > 0 ? (actualExpense / budgetAllocated) * 100 : 0;
    
    document.getElementById('paActualExpenseDisplay').textContent = formatCurrency(actualExpense);
    document.getElementById('paRemainingBudget').textContent = formatCurrency(remainingBudget);
    document.getElementById('paBudgetUtilization').textContent = `${utilization.toFixed(1)}%`;
    
    // Validation
    const validation = document.getElementById('paBudgetValidation');
    if (actualExpense > budgetAllocated) {
        validation.textContent = 'Actual expense cannot exceed budget allocated';
        validation.classList.add('show');
        return false;
    } else {
        validation.classList.remove('show');
        return true;
    }
}

// Image Upload
function handleImageUpload(files) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    const maxImages = 50;
    
    const validation = document.getElementById('paImageValidation');
    validation.classList.remove('show');
    
    if (PAState.uploadedImages.length + files.length > maxImages) {
        validation.textContent = `Maximum ${maxImages} images allowed`;
        validation.classList.add('show');
        return;
    }
    
    Array.from(files).forEach(file => {
        if (!validTypes.includes(file.type)) {
            validation.textContent = `Invalid file type: ${file.name}. Only JPG, JPEG, PNG, and WEBP are allowed`;
            validation.classList.add('show');
            return;
        }
        
        if (file.size > maxSize) {
            validation.textContent = `File too large: ${file.name}. Maximum size is 10MB`;
            validation.classList.add('show');
            return;
        }
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const imageData = {
                file: file,
                preview: e.target.result,
                name: file.name,
                uploaded: false,
                progress: 0
            };
            
            PAState.uploadedImages.push(imageData);
            renderImagePreview();
            simulateUpload(imageData);
        };
        reader.readAsDataURL(file);
    });
}

function renderImagePreview() {
    const container = document.getElementById('paImagePreview');
    container.innerHTML = PAState.uploadedImages.map((img, index) => `
        <div class="pa-image-preview-item" data-index="${index}">
            <img src="${img.preview}" alt="${img.name}" loading="lazy">
            <button type="button" class="pa-image-preview-remove" data-index="${index}" title="Remove">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `).join('');
}

function simulateUpload(imageData) {
    const progressContainer = document.getElementById('paUploadProgress');
    const progressItem = document.createElement('div');
    progressItem.className = 'pa-progress-item';
    progressItem.innerHTML = `
        <span class="pa-progress-name">${imageData.name}</span>
        <div class="pa-progress-bar">
            <div class="pa-progress-fill" style="width: 0%"></div>
        </div>
        <span class="pa-progress-status">0%</span>
    `;
    progressContainer.appendChild(progressItem);
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            imageData.uploaded = true;
            imageData.progress = 100;
        }
        
        progressItem.querySelector('.pa-progress-fill').style.width = `${progress}%`;
        progressItem.querySelector('.pa-progress-status').textContent = `${Math.round(progress)}%`;
        
        if (progress === 100) {
            setTimeout(() => progressItem.remove(), 500);
        }
    }, 100);
}

function removeUploadedImage(index) {
    PAState.uploadedImages.splice(index, 1);
    renderImagePreview();
}

// Lightbox
function openLightbox(images, startIndex = 0) {
    PAState.lightboxImages = images;
    PAState.currentLightboxIndex = startIndex;
    
    updateLightboxImage();
    openModal('paLightbox');
}

function updateLightboxImage() {
    const image = PAState.lightboxImages[PAState.currentLightboxIndex];
    if (!image) return;
    
    const lightboxImage = document.getElementById('paLightboxImage');
    const lightboxCaption = document.getElementById('paLightboxCaption');
    
    lightboxImage.src = image.secure_url || image.preview;
    lightboxCaption.textContent = image.display_name || image.caption || `Image ${PAState.currentLightboxIndex + 1}`;
}

function nextLightboxImage() {
    if (PAState.currentLightboxIndex < PAState.lightboxImages.length - 1) {
        PAState.currentLightboxIndex++;
        updateLightboxImage();
    }
}

function prevLightboxImage() {
    if (PAState.currentLightboxIndex > 0) {
        PAState.currentLightboxIndex--;
        updateLightboxImage();
    }
}

// View Report
async function viewReport(reportId) {
    const report = await loadReportForView(reportId);
    if (!report) return;
    
    const program = report.program;
    const reportImages = report.images || [];
    
    const content = document.getElementById('paViewContent');
    content.innerHTML = `
        <div class="pa-view-section">
            <h3 class="pa-view-section-title">Program Information</h3>
            <div class="pa-view-info-grid">
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Program</span>
                    <span class="pa-view-info-value">${program?.program_name || 'N/A'}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Barangay</span>
                    <span class="pa-view-info-value">${program?.barangay || 'N/A'}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Program Type</span>
                    <span class="pa-view-info-value">${program?.program_type || 'N/A'}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Committee</span>
                    <span class="pa-view-info-value">${program?.committee || 'N/A'}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Budget Allocated</span>
                    <span class="pa-view-info-value">${formatCurrency(program?.participation_quantity || 0)}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Date Started</span>
                    <span class="pa-view-info-value">${formatDate(program?.start_date)}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Date Completed</span>
                    <span class="pa-view-info-value">${formatDate(program?.end_date)}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Status</span>
                    <span class="pa-view-info-value">${program?.status || 'N/A'}</span>
                </div>
            </div>
        </div>
        
        <div class="pa-view-section">
            <h3 class="pa-view-section-title">Budget Summary</h3>
            <div class="pa-budget-summary">
                <div class="pa-budget-item">
                    <span class="pa-budget-label">Budget Allocated:</span>
                    <span class="pa-budget-value">${formatCurrency(program?.participation_quantity || 0)}</span>
                </div>
                <div class="pa-budget-item">
                    <span class="pa-budget-label">Actual Expense:</span>
                    <span class="pa-budget-value">${formatCurrency(report.actual_expense)}</span>
                </div>
                <div class="pa-budget-item pa-budget-item-highlight">
                    <span class="pa-budget-label">Remaining Budget:</span>
                    <span class="pa-budget-value">${formatCurrency(report.remaining_budget || 0)}</span>
                </div>
                <div class="pa-budget-item">
                    <span class="pa-budget-label">Budget Utilization:</span>
                    <span class="pa-budget-value">${report.budget_utilization_percent?.toFixed(1) || 0}%</span>
                </div>
            </div>
        </div>
        
        <div class="pa-view-section">
            <h3 class="pa-view-section-title">Report Information</h3>
            <div class="pa-view-info-grid">
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Title</span>
                    <span class="pa-view-info-value">${report.title || 'N/A'}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Description</span>
                    <span class="pa-view-text">${report.description || 'No description provided'}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Objectives</span>
                    <span class="pa-view-text">${report.objectives || 'No objectives specified'}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Implementation Summary</span>
                    <span class="pa-view-text">${report.implementation_summary || 'No implementation summary provided'}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Lessons Learned</span>
                    <span class="pa-view-text">${report.lessons_learned || 'No lessons learned recorded'}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Recommendations</span>
                    <span class="pa-view-text">${report.recommendations || 'No recommendations provided'}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Participants Count</span>
                    <span class="pa-view-info-value">${report.participants_count || 0}</span>
                </div>
                <div class="pa-view-info-item">
                    <span class="pa-view-info-label">Actual Expense</span>
                    <span class="pa-view-info-value">${formatCurrency(report.actual_expense)}</span>
                </div>
                <div class="pa-view-info-item" style="grid-column: 1 / -1;">
                    <span class="pa-view-info-label">Remarks</span>
                    <span class="pa-view-text">${report.remarks || 'No remarks'}</span>
                </div>
            </div>
        </div>
        
        <div class="pa-view-section">
            <h3 class="pa-view-section-title">Proof Images (${reportImages.length})</h3>
            ${reportImages.length > 0 ? `
                <div class="pa-gallery">
                    ${reportImages.map((img, index) => `
                        <div class="pa-gallery-item" data-index="${index}">
                            <img src="${img.secure_url}" alt="${img.display_name || 'Image'}" loading="lazy">
                            <div class="pa-gallery-caption">${img.display_name || `Image ${index + 1}`}</div>
                        </div>
                    `).join('')}
                </div>
            ` : '<p class="pa-empty-text">No proof images uploaded</p>'}
        </div>
    `;
    
    // Add click handlers for gallery
    content.querySelectorAll('.pa-gallery-item').forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(reportImages, index));
    });
    
    document.getElementById('paViewModalTitle').textContent = report.title || 'Accomplishment Report';
    openModal('paViewModal');
}

// Delete Confirmation
function showDeleteConfirm(reportId) {
    const report = PAState.accomplishmentReports.find(r => r.id === reportId);
    if (!report) return;
    
    const program = PAState.programs.find(p => p.id === report.program_id);
    const reportImages = PAState.images.filter(img => img.accomplishment_report_id === reportId);
    
    document.getElementById('paDeleteProgramTitle').textContent = program?.title || 'Unknown Program';
    document.getElementById('paDeleteImageCount').textContent = reportImages.length;
    
    PAState.currentReportId = reportId;
    openModal('paDeleteModal');
}

// Form Validation
function validateForm() {
    const title = document.getElementById('paTitle').value.trim();
    const implementationSummary = document.getElementById('paImplementationSummary').value.trim();
    const participantsCount = document.getElementById('paParticipantsCount').value;
    const actualExpense = document.getElementById('paActualExpense').value;
    
    const validation = document.getElementById('paImageValidation');
    
    if (!title) {
        validation.textContent = 'Title is required';
        validation.classList.add('show');
        return false;
    }
    
    if (!implementationSummary) {
        validation.textContent = 'Implementation summary is required';
        validation.classList.add('show');
        return false;
    }
    
    if (!participantsCount || participantsCount < 0) {
        validation.textContent = 'Valid participants count is required';
        validation.classList.add('show');
        return false;
    }
    
    if (!actualExpense || actualExpense < 0) {
        validation.textContent = 'Valid actual expense is required';
        validation.classList.add('show');
        return false;
    }
    
    if (!updateBudgetSummary()) {
        return false;
    }
    
    if (PAState.uploadedImages.length === 0 && PAState.existingImages.length === 0) {
        validation.textContent = 'At least one proof image is required';
        validation.classList.add('show');
        return false;
    }
    
    if (PAState.uploadedImages.length + PAState.existingImages.length > 50) {
        validation.textContent = 'Maximum 50 images allowed';
        validation.classList.add('show');
        return false;
    }
    
    validation.classList.remove('show');
    return true;
}

// Event Listeners
function initializeEventListeners() {
    // Table actions
    document.getElementById('paTableBody').addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;
        
        const action = button.dataset.action;
        const id = parseInt(button.dataset.id);
        
        switch (action) {
            case 'create-report':
                PAState.currentMode = 'create';
                const program = PAState.programs.find(p => p.id === id);
                if (program) {
                    resetForm();
                    loadProgramIntoForm(program);
                    document.getElementById('paModalTitle').textContent = 'Create Accomplishment Report';
                    openModal('paModal');
                }
                break;
            case 'view-report':
                viewReport(id);
                break;
            case 'edit-report':
                PAState.currentMode = 'edit';
                const report = PAState.accomplishmentReports.find(r => r.id === id);
                if (report) {
                    resetForm();
                    const program = PAState.programs.find(p => p.id === report.program_id);
                    if (program) {
                        loadProgramIntoForm(program);
                    }
                    loadReportIntoForm(report);
                    document.getElementById('paModalTitle').textContent = 'Edit Accomplishment Report';
                    openModal('paModal');
                }
                break;
            case 'delete-report':
                showDeleteConfirm(id);
                break;
        }
    });
    
    // Modal controls
    document.getElementById('paModalClose').addEventListener('click', () => closeModal('paModal'));
    document.getElementById('paModalOverlay').addEventListener('click', () => closeModal('paModal'));
    document.getElementById('paCancelBtn').addEventListener('click', () => closeModal('paModal'));
    
    document.getElementById('paViewModalClose').addEventListener('click', () => closeModal('paViewModal'));
    document.getElementById('paViewModalOverlay').addEventListener('click', () => closeModal('paViewModal'));
    document.getElementById('paViewCloseBtn').addEventListener('click', () => closeModal('paViewModal'));
    
    document.getElementById('paDeleteModalClose').addEventListener('click', () => closeModal('paDeleteModal'));
    document.getElementById('paDeleteModalOverlay').addEventListener('click', () => closeModal('paDeleteModal'));
    document.getElementById('paDeleteCancelBtn').addEventListener('click', () => closeModal('paDeleteModal'));
    
    // Lightbox controls
    document.getElementById('paLightboxClose').addEventListener('click', () => closeModal('paLightbox'));
    document.getElementById('paLightboxOverlay').addEventListener('click', () => closeModal('paLightbox'));
    document.getElementById('paLightboxPrev').addEventListener('click', prevLightboxImage);
    document.getElementById('paLightboxNext').addEventListener('click', nextLightboxImage);
    
    // Keyboard navigation for lightbox
    document.addEventListener('keydown', (e) => {
        if (document.getElementById('paLightbox').classList.contains('active')) {
            if (e.key === 'Escape') closeModal('paLightbox');
            if (e.key === 'ArrowLeft') prevLightboxImage();
            if (e.key === 'ArrowRight') nextLightboxImage();
        }
    });
    
    // Form save
    document.getElementById('paSaveBtn').addEventListener('click', saveReport);
    
    // Delete confirm
    document.getElementById('paDeleteConfirmBtn').addEventListener('click', () => {
        if (PAState.currentReportId) {
            deleteReport(PAState.currentReportId);
        }
    });
    
    // Image upload
    const uploadArea = document.getElementById('paUploadArea');
    const imageInput = document.getElementById('paImageInput');
    
    uploadArea.addEventListener('click', () => imageInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleImageUpload(e.dataTransfer.files);
    });
    
    imageInput.addEventListener('change', (e) => {
        handleImageUpload(e.target.files);
        imageInput.value = '';
    });
    
    // Image preview remove
    document.getElementById('paImagePreview').addEventListener('click', (e) => {
        const button = e.target.closest('.pa-image-preview-remove');
        if (button) {
            const index = parseInt(button.dataset.index);
            removeUploadedImage(index);
        }
    });
    
    // Budget calculation
    document.getElementById('paActualExpense').addEventListener('input', updateBudgetSummary);
    
    // Filters
    document.getElementById('paSearch').addEventListener('input', (e) => {
        PAState.filters.search = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    document.getElementById('paBarangayFilter').addEventListener('change', (e) => {
        PAState.filters.barangay = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    document.getElementById('paCategoryFilter').addEventListener('change', (e) => {
        PAState.filters.category = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    document.getElementById('paDateFromFilter').addEventListener('change', (e) => {
        PAState.filters.dateFrom = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    document.getElementById('paDateToFilter').addEventListener('change', (e) => {
        PAState.filters.dateTo = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    document.getElementById('paReportStatusFilter').addEventListener('change', (e) => {
        PAState.filters.reportStatus = e.target.value;
        PAState.currentPage = 1;
        renderTable();
    });
    
    // Pagination
    document.getElementById('paPrevBtn').addEventListener('click', () => {
        if (PAState.currentPage > 1) {
            PAState.currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('paNextBtn').addEventListener('click', () => {
        const totalPages = Math.ceil(PAState.filteredPrograms.length / PAState.itemsPerPage);
        if (PAState.currentPage < totalPages) {
            PAState.currentPage++;
            renderTable();
        }
    });
}

// API Functions
async function fetchInitialData() {
    try {
        const response = await fetch('/api/program-accomplishment/data');
        if (!response.ok) throw new Error('Failed to fetch data');
        
        const data = await response.json();
        PAState.programs = data.programs || [];
        PAState.accomplishmentReports = data.accomplishmentReports || [];
        PAState.images = data.images || [];
        
        updateStatistics();
        renderTable();
    } catch (error) {
        console.error('Error fetching initial data:', error);
        showToast('Failed to load data. Please refresh the page.', 'error');
    }
}

async function saveReport() {
    if (!validateForm()) {
        return;
    }
    
    const formData = new FormData();
    formData.append('program_id', PAState.currentProgramId);
    formData.append('title', document.getElementById('paTitle').value.trim());
    formData.append('description', document.getElementById('paDescription').value.trim());
    formData.append('objectives', document.getElementById('paObjectives').value.trim());
    formData.append('implementation_summary', document.getElementById('paImplementationSummary').value.trim());
    formData.append('lessons_learned', document.getElementById('paLessonsLearned').value.trim());
    formData.append('recommendations', document.getElementById('paRecommendations').value.trim());
    formData.append('participants_count', document.getElementById('paParticipantsCount').value);
    formData.append('actual_expense', document.getElementById('paActualExpense').value);
    formData.append('remarks', document.getElementById('paRemarks').value.trim());
    
    // Add images
    PAState.uploadedImages.forEach((img, index) => {
        if (img.file && !img.uploaded) {
            formData.append(`images[${index}]`, img.file);
        }
    });
    
    const url = PAState.currentMode === 'create' 
        ? '/api/program-accomplishment'
        : `/api/program-accomplishment/${PAState.currentReportId}`;
    
    const method = PAState.currentMode === 'create' ? 'POST' : 'PUT';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            if (result.errors) {
                // Display validation errors
                const validation = document.getElementById('paImageValidation');
                const errorMessages = Object.values(result.errors).flat();
                validation.textContent = errorMessages.join(', ');
                validation.classList.add('show');
            } else {
                throw new Error(result.error || 'Failed to save report');
            }
            return;
        }
        
        showToast(result.message || 'Accomplishment report saved successfully!', 'success');
        closeModal('paModal');
        resetForm();
        
        // Refresh data
        await fetchInitialData();
    } catch (error) {
        console.error('Error saving report:', error);
        showToast(error.message || 'Failed to save report. Please try again.', 'error');
    }
}

async function deleteReport(reportId) {
    try {
        const response = await fetch(`/api/program-accomplishment/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to delete report');
        }
        
        showToast(result.message || 'Accomplishment report deleted successfully!', 'success');
        closeModal('paDeleteModal');
        
        // Refresh data
        await fetchInitialData();
    } catch (error) {
        console.error('Error deleting report:', error);
        showToast(error.message || 'Failed to delete report. Please try again.', 'error');
    }
}

async function loadReportForView(reportId) {
    try {
        const response = await fetch(`/api/program-accomplishment/${reportId}`);
        if (!response.ok) throw new Error('Failed to load report');
        
        const result = await response.json();
        if (result.success && result.report) {
            return result.report;
        }
        throw new Error('Invalid response format');
    } catch (error) {
        console.error('Error loading report:', error);
        showToast('Failed to load report details.', 'error');
        return null;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Load initial data from server
    fetchInitialData();
    
    // Initialize event listeners
    initializeEventListeners();
});