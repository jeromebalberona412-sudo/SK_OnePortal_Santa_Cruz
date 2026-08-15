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
    uploadedDocuments: [],
    existingImages: [],
    existingDocuments: [],
    deletedImageIds: [],
    deletedDocumentIds: [],
    lightboxImages: [],
    currentLightboxIndex: 0,
    filters: {
        search: '',
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function programApprovedBudget(program, report) {
    const values = [
        program?.total,
        program?.approved_budget,
        report?.approved_budget,
        program?.participation_quantity,
    ];

    for (const value of values) {
        const amount = Number(value);
        if (Number.isFinite(amount) && amount > 0) {
            return amount;
        }
    }

    return 0;
}

function remainingBudgetAmount(program, report, actualExpense) {
    const approved = programApprovedBudget(program, report);
    const spent = Number(actualExpense);
    const expense = Number.isFinite(spent) ? spent : 0;

    return Math.max(0, approved - expense);
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
    const totalPrograms = PAState.programs.filter(p => String(p.status || '').toLowerCase() === 'completed').length;
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
        if (String(program.status || '').toLowerCase() !== 'completed') return false;

        // Search filter
        if (PAState.filters.search) {
            const searchLower = PAState.filters.search.toLowerCase();
            const searchableText = `${program.program_name} ${program.program_type} ${program.committee} ${program.creator}`.toLowerCase();
            if (!searchableText.includes(searchLower)) return false;
        }

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
        const hasReport = PAState.accomplishmentReports.some(r =>
            r.program_id === program.id || r.id === program.accomplishment_report_id
        );
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
                <td colspan="10" class="pa-empty-state">
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
            const report = PAState.accomplishmentReports.find(r =>
                r.program_id === program.id || r.id === program.accomplishment_report_id
            );
            const hasReport = !!report;
            const row = document.createElement('tr');
            const createId = program.id || program.abyip_program_id || '';
            
            row.innerHTML = `
                <td><strong>${program.program_name}</strong></td>
                <td>${program.program_type || 'N/A'}</td>
                <td>${program.committee || 'N/A'}</td>
                <td>${formatDate(program.start_date)}</td>
                <td>${formatDate(program.end_date)}</td>
                <td>${formatCurrency(programApprovedBudget(program))}</td>
                <td>${program.creator || 'N/A'}</td>
                <td><span class="pa-status-badge pa-status-completed">Completed</span></td>
                <td>
                    <span class="pa-status-badge ${hasReport ? 'pa-status-with-report' : 'pa-status-without-report'}">
                        ${hasReport ? 'With Report' : 'Without Report'}
                    </span>
                </td>
                <td class="col-actions">
                    <div class="row-actions-menu">
                        <button type="button" class="row-actions-trigger" aria-label="Actions" aria-haspopup="true" aria-expanded="false">${window.ROW_ACTIONS_ELLIPSIS || '⋯'}</button>
                        <div class="row-actions-dropdown" role="menu">
                            ${hasReport ? `
                                <button type="button" class="row-actions-item row-actions-item-view" data-action="view-report" data-id="${report.id}" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>View</span>
                                </button>
                                <button type="button" class="row-actions-item row-actions-item-edit" data-action="edit-report" data-id="${report.id}" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    <span>Edit</span>
                                </button>
                                <button type="button" class="row-actions-item row-actions-item-danger" data-action="delete-report" data-id="${report.id}" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    <span>Delete</span>
                                </button>
                            ` : `
                                <button type="button" class="row-actions-item row-actions-item-approve" data-action="create-report" data-id="${createId}" data-abyip-id="${program.abyip_program_id || ''}" role="menuitem">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Create</span>
                                </button>
                            `}
                        </div>
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
    PAState.uploadedDocuments = [];
    PAState.existingImages = [];
    PAState.existingDocuments = [];
    PAState.deletedImageIds = [];
    PAState.deletedDocumentIds = [];
    PAState.currentReportId = null;
    PAState.currentProgramId = null;
    
    document.getElementById('paImagePreview').innerHTML = '';
    document.getElementById('paUploadProgress').innerHTML = '';
    document.getElementById('paExistingImages').innerHTML = '';
    document.getElementById('paExistingImagesSection').style.display = 'none';
    const existingDocs = document.getElementById('paExistingDocuments');
    if (existingDocs) existingDocs.innerHTML = '';
    const newDocs = document.getElementById('paDocumentPreview');
    if (newDocs) newDocs.innerHTML = '';
    document.getElementById('paBudgetValidation').classList.remove('show');
    document.getElementById('paImageValidation').classList.remove('show');
    
    updateBudgetSummary();
}

function loadProgramIntoForm(program) {
    document.getElementById('paProgram').value = program.program_name || '';
    const categoryEl = document.getElementById('paCategory');
    if (categoryEl) categoryEl.value = program.category || program.program_type || '';
    const descEl = document.getElementById('paProgramDescription');
    if (descEl) descEl.value = program.description || '';
    const expectedEl = document.getElementById('paExpectedResult');
    if (expectedEl) expectedEl.value = program.expected_result || '';
    const indicatorEl = document.getElementById('paPerformanceIndicator');
    if (indicatorEl) indicatorEl.value = program.performance_indicator || '';
    document.getElementById('paPersonResponsible').value = program.person_responsible || program.creator || '';
    const budget = programApprovedBudget(program);
    document.getElementById('paBudgetAllocated').value = formatCurrency(budget);
    document.getElementById('paBudgetAllocatedDisplay').textContent = formatCurrency(budget);
    document.getElementById('paDateStarted').value = formatDate(program.start_date);
    document.getElementById('paDateCompleted').value = formatDate(program.end_date);
    
    PAState.currentProgramId = program.id;
}

function loadReportIntoForm(report) {
    const summary = document.getElementById('paImplementationSummary');
    if (summary) summary.value = report.implementation_summary || '';
    const actualResult = document.getElementById('paActualResult');
    if (actualResult) actualResult.value = report.actual_result || '';
    const target = document.getElementById('paTargetBeneficiaries');
    if (target) target.value = report.target_beneficiaries || '';
    document.getElementById('paParticipantsCount').value = report.participants_count || '';
    document.getElementById('paActualExpense').value = report.actual_expense || '';
    document.getElementById('paRemarks').value = report.remarks || '';
    
    PAState.currentReportId = report.id;
    
    const reportImages = Array.isArray(report.images) && report.images.length
        ? report.images
        : PAState.images.filter((img) => img.accomplishment_report_id === report.id);
    PAState.existingImages = reportImages;
    PAState.existingDocuments = Array.isArray(report.documents) ? report.documents : [];
    renderExistingImages();
    renderExistingDocuments();
    
    updateBudgetSummary();
}

function renderExistingImages() {
    const container = document.getElementById('paExistingImages');
    const section = document.getElementById('paExistingImagesSection');
    
    if (!container || !section) return;

    if (PAState.existingImages.length === 0) {
        container.innerHTML = '';
        section.style.display = 'none';
        return;
    }
    
    section.style.display = 'block';
    container.innerHTML = PAState.existingImages.map((img, index) => `
        <div class="pa-existing-image-item" data-index="${index}" data-id="${img.id}">
            <img src="${escapeHtml(img.secure_url || img.image_url)}" alt="Program photo" loading="lazy">
            <button type="button" class="pa-image-preview-remove pa-existing-image-remove" data-index="${index}" aria-label="Remove image">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `).join('');
}

function renderExistingDocuments() {
    const container = document.getElementById('paExistingDocuments');
    if (!container) return;

    if (!PAState.existingDocuments.length) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = PAState.existingDocuments.map((doc, index) => `
        <div class="pa-doc-row pa-existing-doc-row">
            <span>${escapeHtml(doc.original_name || 'Document')}</span>
            <button type="button" class="pa-existing-doc-remove" data-index="${index}">Remove</button>
        </div>
    `).join('');
}

function removeExistingImage(index) {
    const image = PAState.existingImages[index];
    if (!image) return;
    if (image.id) {
        PAState.deletedImageIds.push(image.id);
    }
    PAState.existingImages.splice(index, 1);
    renderExistingImages();
}

function removeExistingDocument(index) {
    const doc = PAState.existingDocuments[index];
    if (!doc) return;
    if (doc.id) {
        PAState.deletedDocumentIds.push(doc.id);
    }
    PAState.existingDocuments.splice(index, 1);
    renderExistingDocuments();
}

// Budget Calculation
function updateBudgetSummary() {
    const budgetAllocatedText = document.getElementById('paBudgetAllocated').value;
    const actualExpense = parseFloat(document.getElementById('paActualExpense').value) || 0;
    
    // Parse budget allocated (remove currency formatting)
    const budgetAllocated = parseFloat(budgetAllocatedText.replace(/[^0-9.-]+/g, '')) || 0;
    
    const remainingBudget = Math.max(0, budgetAllocated - actualExpense);
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
            <img src="${img.preview}" alt="Program photo" loading="lazy">
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
    
    lightboxImage.src = image.secure_url || image.image_url || image.preview;
    if (lightboxCaption) {
        lightboxCaption.textContent = '';
    }
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

    const listedProgram = PAState.programs.find((p) =>
        p.id === report.program_id || p.accomplishment_report_id === report.id
    );
    const program = listedProgram
        ? { ...(report.program || {}), ...listedProgram }
        : (report.program || null);
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
                    <span class="pa-view-info-value">${formatCurrency(programApprovedBudget(program, report))}</span>
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
                    <span class="pa-budget-value">${formatCurrency(programApprovedBudget(program, report))}</span>
                </div>
                <div class="pa-budget-item">
                    <span class="pa-budget-label">Actual Expense:</span>
                    <span class="pa-budget-value">${formatCurrency(report.actual_expense)}</span>
                </div>
                <div class="pa-budget-item pa-budget-item-highlight">
                    <span class="pa-budget-label">Remaining Budget:</span>
                    <span class="pa-budget-value">${formatCurrency(remainingBudgetAmount(program, report, report.actual_expense))}</span>
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
                            <img src="${img.secure_url}" alt="Program photo" loading="lazy">
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
    const publishBtn = document.getElementById('paPublishBtn');
    if (publishBtn) {
        const canPublish = report.status === 'Submitted' || report.status === 'Unpublished';
        publishBtn.hidden = !canPublish;
        PAState.currentReportId = canPublish ? report.id : PAState.currentReportId;
        if (canPublish) PAState.currentReportId = report.id;
        const viewFooter = document.getElementById('paViewModalFooter');
        if (viewFooter) viewFooter.hidden = !canPublish;
    }
    openModal('paViewModal');
}

// Delete Confirmation
function showDeleteConfirm(reportId) {
    const report = PAState.accomplishmentReports.find(r => r.id === reportId);
    if (!report) return;
    
    const program = PAState.programs.find(p => p.id === report.program_id);
    const programName = program?.program_name
        || report.program?.program_name
        || report.title
        || 'this program';
    const reportImages = Array.isArray(report.images) && report.images.length
        ? report.images
        : PAState.images.filter(img => img.accomplishment_report_id === reportId);
    
    document.getElementById('paDeleteProgramTitle').textContent = programName;
    document.getElementById('paDeleteImageCount').textContent = String(reportImages.length);

    const confirmInput = document.getElementById('paDeleteConfirmInput');
    const confirmBtn = document.getElementById('paDeleteConfirmBtn');
    const hintError = document.getElementById('paDeleteConfirmHintError');
    if (confirmInput) confirmInput.value = '';
    if (hintError) hintError.hidden = true;
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.classList.add('is-disabled');
        confirmBtn.classList.remove('is-enabled');
    }
    
    PAState.currentReportId = reportId;
    openModal('paDeleteModal');
    confirmInput?.focus();
}

function syncPaDeleteConfirm() {
    const confirmInput = document.getElementById('paDeleteConfirmInput');
    const confirmBtn = document.getElementById('paDeleteConfirmBtn');
    const hintError = document.getElementById('paDeleteConfirmHintError');
    if (!confirmInput || !confirmBtn) return;

    const matched = confirmInput.value === 'Confirm';
    if (hintError) {
        hintError.hidden = !(confirmInput.value.length > 0 && !matched);
    }
    confirmBtn.disabled = !matched;
    confirmBtn.classList.toggle('is-disabled', !matched);
    confirmBtn.classList.toggle('is-enabled', matched);
}

// Form Validation
function validateForm(requireComplete) {
    const implementationSummary = document.getElementById('paImplementationSummary').value.trim();
    const actualExpense = document.getElementById('paActualExpense').value;
    
    const validation = document.getElementById('paImageValidation');
    
    if (requireComplete && !implementationSummary) {
        validation.textContent = 'Accomplishment summary is required';
        validation.classList.add('show');
        return false;
    }
    
    if (requireComplete && (actualExpense === '' || Number(actualExpense) < 0)) {
        validation.textContent = 'Valid actual expenditure is required';
        validation.classList.add('show');
        return false;
    }
    
    if (!updateBudgetSummary()) {
        return false;
    }
    
    if (requireComplete && PAState.uploadedImages.length === 0 && PAState.existingImages.length === 0) {
        validation.textContent = 'At least one proof image is required to submit';
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

async function openCreateForProgram(button) {
    const abyipId = Number(button.dataset.abyipId || 0);
    const scheduleId = Number(button.dataset.id || 0);
    let program = PAState.programs.find((p) =>
        (scheduleId && p.id === scheduleId) || (abyipId && p.abyip_program_id === abyipId)
    );

    if (!program) {
        showToast('Program not found.', 'error');
        return;
    }

    if (abyipId && !program.id) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch('/api/program-accomplishment/prepare-from-catalog', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ abyip_program_id: abyipId }),
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || result.error || 'Unable to prepare accomplishment.');
            }
            program = {
                ...program,
                id: result.data?.schedule_program_id || program.id,
                accomplishment_report_id: result.data?.accomplishment_report_id || program.accomplishment_report_id,
            };
            const index = PAState.programs.findIndex((p) => p.abyip_program_id === abyipId);
            if (index >= 0) {
                PAState.programs[index] = program;
            }
            if (result.data?.accomplishment_report_id) {
                showToast('An accomplishment already exists for this program.', 'error');
                return;
            }
        } catch (error) {
            showToast(error.message || 'Unable to create accomplishment.', 'error');
            return;
        }
    }

    resetForm();
    loadProgramIntoForm(program);
    document.getElementById('paModalTitle').textContent = 'Create Program Accomplishment';
    openModal('paModal');
}

// Event Listeners
function initializeEventListeners() {
    // Table actions
    document.getElementById('paTableBody').addEventListener('click', (e) => {
        const button = e.target.closest('[data-action]');
        if (!button || button.classList.contains('row-actions-trigger')) return;
        
        const action = button.dataset.action;
        const id = parseInt(button.dataset.id);
        
        switch (action) {
            case 'create-report':
                PAState.currentMode = 'create';
                openCreateForProgram(button);
                break;
            case 'view-report':
                viewReport(id);
                break;
            case 'edit-report':
                PAState.currentMode = 'edit';
                const report = PAState.accomplishmentReports.find(r => r.id === id);
                if (report) {
                    resetForm();
                    const program = PAState.programs.find((p) => p.id === report.program_id)
                        || PAState.programs.find((p) => p.accomplishment_report_id === report.id)
                        || report.program;
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
    
    document.getElementById('paDeleteModalClose')?.addEventListener('click', () => closeModal('paDeleteModal'));
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
    document.getElementById('paSaveBtn').addEventListener('click', () => saveReport());
    const publishBtn = document.getElementById('paPublishBtn');
    if (publishBtn) {
        publishBtn.addEventListener('click', publishCurrentReport);
    }
    const documentInput = document.getElementById('paDocumentInput');
    if (documentInput) {
        documentInput.addEventListener('change', (e) => {
            PAState.uploadedDocuments = Array.from(e.target.files || []).map((file) => ({
                file,
                document_type: 'other',
            }));
            const preview = document.getElementById('paDocumentPreview');
            if (preview) {
                preview.innerHTML = PAState.uploadedDocuments.map((doc) => `
                    <div class="pa-doc-row">
                        <span>${escapeHtml(doc.file.name)}</span>
                    </div>
                `).join('');
            }
        });
    }
    
    // Delete confirm
    document.getElementById('paDeleteConfirmBtn').addEventListener('click', () => {
        const confirmInput = document.getElementById('paDeleteConfirmInput');
        if ((confirmInput?.value || '') !== 'Confirm') {
            return;
        }
        if (PAState.currentReportId) {
            deleteReport(PAState.currentReportId);
        }
    });
    document.getElementById('paDeleteConfirmInput')?.addEventListener('input', syncPaDeleteConfirm);
    
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
            e.stopPropagation();
            const index = parseInt(button.dataset.index, 10);
            removeUploadedImage(index);
            return;
        }

        const item = e.target.closest('.pa-image-preview-item');
        if (item) {
            const index = parseInt(item.dataset.index, 10);
            const images = PAState.uploadedImages.map((img) => ({
                secure_url: img.preview,
            }));
            openLightbox(images, index);
        }
    });

    const existingImages = document.getElementById('paExistingImages');
    if (existingImages) {
        existingImages.addEventListener('click', (e) => {
            const button = e.target.closest('.pa-existing-image-remove');
            if (button) {
                e.stopPropagation();
                removeExistingImage(parseInt(button.dataset.index, 10));
                return;
            }

            const item = e.target.closest('.pa-existing-image-item');
            if (item) {
                openLightbox(PAState.existingImages, parseInt(item.dataset.index, 10));
            }
        });
    }

    const existingDocs = document.getElementById('paExistingDocuments');
    if (existingDocs) {
        existingDocs.addEventListener('click', (e) => {
            const button = e.target.closest('.pa-existing-doc-remove');
            if (button) {
                removeExistingDocument(parseInt(button.dataset.index, 10));
            }
        });
    }
    
    // Budget calculation
    document.getElementById('paActualExpense').addEventListener('input', updateBudgetSummary);
    
    // Filters
    document.getElementById('paSearch').addEventListener('input', (e) => {
        PAState.filters.search = e.target.value;
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
    // Show loading state
    const loadingElement = document.getElementById('loading');
    if (loadingElement) {
        loadingElement.style.display = 'flex';
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
            showToast('Security token missing. Please refresh the page.', 'error');
            return;
        }

        console.log('Fetching data from /api/program-accomplishment/data...');

        const response = await fetch('/api/program-accomplishment/data', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error Response:', errorText);
            
            // Handle specific error cases
            if (response.status === 401) {
                showToast('Session expired. Please login again.', 'error');
                setTimeout(() => window.location.href = '/login', 2000);
                return;
            }
            if (response.status === 403) {
                showToast('Access denied. Please check your permissions.', 'error');
                return;
            }
            
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('Data received:', data);

        PAState.programs = data.programs || [];
        PAState.accomplishmentReports = data.accomplishmentReports || [];
        PAState.images = data.images || [];

        updateStatistics();
        renderTable();

        if (data.error) {
            console.warn('API returned data with error:', data.error);
            showToast(data.error, 'warning');
        }
    } catch (error) {
        console.error('Error fetching initial data:', error);
        showToast('Failed to load data. Please refresh the page.', 'error');
    } finally {
        // Hide loading state
        const loadingElement = document.getElementById('loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }
}

async function saveReport() {
    if (PAState.saving) return;
    if (!validateForm(true)) {
        return;
    }
    
    PAState.saving = true;
    const formData = new FormData();
    formData.append('program_id', PAState.currentProgramId);
    formData.append('title', document.getElementById('paProgram').value.trim());
    formData.append('implementation_summary', document.getElementById('paImplementationSummary').value.trim());
    formData.append('actual_result', document.getElementById('paActualResult')?.value.trim() || '');
    formData.append('target_beneficiaries', document.getElementById('paTargetBeneficiaries')?.value || '');
    formData.append('participants_count', document.getElementById('paParticipantsCount').value || '0');
    formData.append('actual_expense', document.getElementById('paActualExpense').value || '0');
    formData.append('remarks', document.getElementById('paRemarks').value.trim());
    
    PAState.uploadedImages.forEach((img, index) => {
        if (img.file) {
            const key = PAState.currentMode === 'create' ? `images[${index}]` : `new_images[${index}]`;
            formData.append(key, img.file);
        }
    });

    PAState.uploadedDocuments.forEach((doc, index) => {
        formData.append(`documents[${index}]`, doc.file);
        formData.append(`document_types[${index}]`, doc.document_type || 'other');
    });

    if (PAState.currentMode !== 'create') {
        PAState.deletedImageIds.forEach((id, index) => {
            formData.append(`delete_images[${index}]`, id);
        });
        PAState.deletedDocumentIds.forEach((id, index) => {
            formData.append(`delete_documents[${index}]`, id);
        });
    }
    
    const url = PAState.currentMode === 'create' 
        ? '/api/program-accomplishment'
        : `/api/program-accomplishment/${PAState.currentReportId}`;
    
    if (PAState.currentMode !== 'create') {
        formData.append('_method', 'PUT');
    }

    const saveBtn = document.getElementById('paSaveBtn');
    const cancelBtn = document.getElementById('paCancelBtn');
    const modal = document.querySelector('#paModal .pa-modal-container');
    const label = saveBtn?.querySelector('.pa-btn-label');
    const spinner = saveBtn?.querySelector('.pa-btn-spinner');
    const previousLabel = label ? label.textContent : 'Submit';
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.classList.add('is-loading');
    }
    if (label) label.textContent = 'Submitting...';
    if (spinner) spinner.hidden = false;
    if (cancelBtn) cancelBtn.disabled = true;
    if (modal) modal.classList.add('is-submitting');
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
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
        
        showToast(result.message || 'Accomplishment submitted successfully!', 'success');
        closeModal('paModal');
        resetForm();
        await fetchInitialData();
    } catch (error) {
        console.error('Error saving report:', error);
        showToast(error.message || 'Failed to submit. Please try again.', 'error');
    } finally {
        PAState.saving = false;
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.classList.remove('is-loading');
        }
        if (label) label.textContent = previousLabel || 'Submit';
        if (spinner) spinner.hidden = true;
        if (cancelBtn) cancelBtn.disabled = false;
        if (modal) modal.classList.remove('is-submitting');
    }
}

async function publishCurrentReport() {
    if (!PAState.currentReportId) return;
    try {
        const response = await fetch(`/api/program-accomplishment/${PAState.currentReportId}/publish`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || result.error || 'Failed to publish');
        }
        showToast(result.message || 'Published.', 'success');
        closeModal('paViewModal');
        await fetchInitialData();
    } catch (error) {
        showToast(error.message || 'Failed to publish.', 'error');
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
    const local = PAState.accomplishmentReports.find((r) => r.id === reportId);
    if (local) {
        return local;
    }

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(`/api/program-accomplishment/${reportId}`, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
        });
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
    fetchInitialData().then(() => {
        const params = new URLSearchParams(window.location.search);
        const programId = Number(params.get('program_id'));
        const editId = Number(params.get('edit'));
        if (programId) {
            const program = PAState.programs.find((p) => p.id === programId);
            const report = PAState.accomplishmentReports.find((r) => r.program_id === programId);
            if (report) {
                document.querySelector(`[data-action="view-report"][data-id="${report.id}"]`)?.click();
            } else if (program) {
                PAState.currentMode = 'create';
                resetForm();
                loadProgramIntoForm(program);
                document.getElementById('paModalTitle').textContent = 'Create Program Accomplishment';
                openModal('paModal');
            }
        } else if (editId) {
            const report = PAState.accomplishmentReports.find((r) => r.id === editId);
            if (report) {
                PAState.currentMode = 'edit';
                resetForm();
                const program = PAState.programs.find((p) => p.id === report.program_id);
                if (program) loadProgramIntoForm(program);
                loadReportIntoForm(report);
                document.getElementById('paModalTitle').textContent = 'Edit Program Accomplishment';
                openModal('paModal');
            }
        }
    });
    initializeEventListeners();
    if (typeof window.bindRowActionsTable === 'function') {
        window.bindRowActionsTable(document.getElementById('paTableBody'));
    }
});