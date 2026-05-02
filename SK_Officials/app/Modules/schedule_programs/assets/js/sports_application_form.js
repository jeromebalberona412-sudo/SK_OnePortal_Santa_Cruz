// Sports Application Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Sample Data - 8 pending applications
    const SAMPLE_APPLICATIONS = [
        {
            id: 1,
            name: 'Juan Dela Cruz',
            sport: 'Basketball',
            division: 'Youth Division (18-21)',
            contact: '09171234567',
            dateApplied: 'Apr 28, 2026',
            paymentStatus: 'Not Paid',
            status: 'Pending'
        },
        {
            id: 2,
            name: 'Maria Santos',
            sport: 'Volleyball',
            division: 'Young Adult (22-25)',
            contact: '09281234567',
            dateApplied: 'Apr 29, 2026',
            paymentStatus: 'Paid',
            status: 'Pending'
        },
        {
            id: 3,
            name: 'Pedro Reyes',
            sport: 'Basketball',
            division: 'Cadet Division (15-17)',
            contact: '09391234567',
            dateApplied: 'Apr 30, 2026',
            paymentStatus: 'Not Paid',
            status: 'Pending'
        },
        {
            id: 4,
            name: 'Ana Lim',
            sport: 'Volleyball',
            division: 'Youth Division (18-21)',
            contact: '09401234567',
            dateApplied: 'May 1, 2026',
            paymentStatus: 'Paid',
            status: 'Pending'
        },
        {
            id: 5,
            name: 'Carlos Garcia',
            sport: 'Basketball',
            division: 'Senior Division (26-30)',
            contact: '09511234567',
            dateApplied: 'May 1, 2026',
            paymentStatus: 'Not Paid',
            status: 'Pending'
        },
        {
            id: 6,
            name: 'Sofia Mendoza',
            sport: 'Volleyball',
            division: 'Cadet Division (15-17)',
            contact: '09621234567',
            dateApplied: 'May 2, 2026',
            paymentStatus: 'Paid',
            status: 'Pending'
        },
        {
            id: 7,
            name: 'Miguel Torres',
            sport: 'Basketball',
            division: 'Young Adult (22-25)',
            contact: '09731234567',
            dateApplied: 'May 2, 2026',
            paymentStatus: 'Not Paid',
            status: 'Pending'
        },
        {
            id: 8,
            name: 'Isabella Cruz',
            sport: 'Volleyball',
            division: 'Youth Division (18-21)',
            contact: '09841234567',
            dateApplied: 'May 2, 2026',
            paymentStatus: 'Paid',
            status: 'Pending'
        }
    ];

    // Load applications from localStorage or use sample data
    let applications = JSON.parse(localStorage.getItem('sports_applications') || 'null');
    if (!applications || applications.length === 0) {
        applications = [...SAMPLE_APPLICATIONS];
        localStorage.setItem('sports_applications', JSON.stringify(applications));
    }

    // Elements
    const createProgramBtn = document.getElementById('createProgramBtn');
    const createProgramModal = document.getElementById('createProgramModal');
    const closeProgramModal = document.getElementById('closeProgramModal');
    const cancelProgramBtn = document.getElementById('cancelProgramBtn');
    const sportsForm = document.getElementById('sportsApplicationForm');
    const applicationsTableBody = document.getElementById('applicationsTableBody');
    const searchApplications = document.getElementById('searchApplications');
    
    // Payment Modal
    const paymentModal = document.getElementById('paymentModal');
    const closePaymentModal = document.getElementById('closePaymentModal');
    const btnPaid = document.getElementById('btnPaid');
    const btnNotPaid = document.getElementById('btnNotPaid');
    
    // Reject Modal
    const rejectModal = document.getElementById('rejectModal');
    const closeRejectModal = document.getElementById('closeRejectModal');
    const cancelReject = document.getElementById('cancelReject');
    const confirmReject = document.getElementById('confirmReject');
    const rejectOtherCheckbox = document.getElementById('rejectOtherCheckbox');
    const rejectOtherTextarea = document.getElementById('rejectOtherTextarea');
    
    // Preview Modal
    const previewModal = document.getElementById('previewModal');
    const previewModalBox = document.getElementById('previewModalBox');
    const previewClose = document.getElementById('previewClose');
    const previewToggle = document.getElementById('previewToggle');
    const previewContent = document.getElementById('previewContent');
    const previewFormBtn = document.getElementById('previewFormBtn');
    
    // Form Elements
    const sportsTypeSelect = document.getElementById('sportsType');
    const otherSportField = document.getElementById('otherSportField');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const questionsContainer = document.getElementById('questionsContainer');
    const questionTemplate = document.getElementById('questionTemplate');

    let questionCounter = 0;
    let isMaximized = false;
    let currentApplicationId = null;

    // Initialize
    renderApplicationsTable();

    // Create Program Button
    if (createProgramBtn) {
        createProgramBtn.addEventListener('click', function() {
            createProgramModal.style.display = 'flex';
        });
    }

    // Close Program Modal
    if (closeProgramModal) {
        closeProgramModal.addEventListener('click', function() {
            createProgramModal.style.display = 'none';
            resetForm();
        });
    }

    if (cancelProgramBtn) {
        cancelProgramBtn.addEventListener('click', function() {
            createProgramModal.style.display = 'none';
            resetForm();
        });
    }

    // Sports Type Change Handler
    if (sportsTypeSelect) {
        sportsTypeSelect.addEventListener('change', function() {
            const selectedSport = this.value;
            
            if (selectedSport === 'other') {
                otherSportField.style.display = 'block';
                document.getElementById('otherSport').required = true;
            } else {
                otherSportField.style.display = 'none';
                document.getElementById('otherSport').required = false;
            }
        });
    }

    // Add Question Button
    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            addQuestion();
        });
    }

    // Function to Add Question
    function addQuestion(data = null) {
        questionCounter++;
        
        const clone = questionTemplate.content.cloneNode(true);
        const questionItem = clone.querySelector('.question-item');
        
        const questionNumber = clone.querySelector('.question-number');
        questionNumber.textContent = `Question ${questionCounter}`;
        
        const questionType = clone.querySelector('.question-type');
        const questionLabel = clone.querySelector('.question-label');
        const questionRequired = clone.querySelector('.question-required');
        const removeBtn = clone.querySelector('.btn-remove-question');

        if (data) {
            questionType.value = data.type || 'text';
            questionLabel.value = data.label || '';
            questionRequired.value = data.required || 'required';
        }

        removeBtn.addEventListener('click', function() {
            questionItem.remove();
            updateQuestionNumbers();
            showToast('Question removed successfully', 'success');
        });

        questionsContainer.appendChild(clone);
    }

    // Update Question Numbers
    function updateQuestionNumbers() {
        const questions = questionsContainer.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const numberSpan = question.querySelector('.question-number');
            numberSpan.textContent = `Question ${index + 1}`;
        });
        questionCounter = questions.length;
    }

    // Preview Form Button
    if (previewFormBtn) {
        previewFormBtn.addEventListener('click', function() {
            generatePreview();
            previewModal.style.display = 'flex';
        });
    }

    // Close Preview Modal
    if (previewClose) {
        previewClose.addEventListener('click', function() {
            previewModal.style.display = 'none';
            isMaximized = false;
            previewModalBox.classList.remove('maximized');
        });
    }

    // Toggle Maximize/Restore
    if (previewToggle) {
        previewToggle.addEventListener('click', function() {
            isMaximized = !isMaximized;
            if (isMaximized) {
                previewModalBox.classList.add('maximized');
            } else {
                previewModalBox.classList.remove('maximized');
            }
        });
    }

    // Generate Preview Function
    function generatePreview() {
        const sportsType = sportsTypeSelect.value;
        const otherSport = document.getElementById('otherSport').value.trim();
        const announcement = document.getElementById('announcement').value.trim();
        const questions = questionsContainer.querySelectorAll('.question-item');

        let previewHTML = '';

        // Sport Type
        previewHTML += `
            <div class="preview-section">
                <div class="preview-section-title">Sport</div>
                <div class="preview-field">
                    <div class="preview-label">Selected Sport:</div>
                    <div class="preview-input">${sportsType === 'other' ? otherSport : sportsType.charAt(0).toUpperCase() + sportsType.slice(1)}</div>
                </div>
            </div>
        `;

        // Announcement
        if (announcement) {
            previewHTML += `
                <div class="preview-announcement">
                    <div class="preview-announcement-title">Announcement</div>
                    <div class="preview-announcement-text">${announcement}</div>
                </div>
            `;
        }

        // Questions
        if (questions.length > 0) {
            previewHTML += `<div class="preview-section"><div class="preview-section-title">Application Questions</div>`;

            questions.forEach((question, index) => {
                const label = question.querySelector('.question-label').value.trim();
                const type = question.querySelector('.question-type').value;
                const required = question.querySelector('.question-required').value;

                if (!label) return;

                const requiredBadge = required === 'required' ? '<span class="required-badge">*</span>' : '';

                previewHTML += `
                    <div class="preview-field">
                        <label class="preview-label">${index + 1}. ${label} ${requiredBadge}</label>
                `;

                switch(type) {
                    case 'text':
                    case 'email':
                    case 'number':
                    case 'phone':
                        previewHTML += `<input type="${type}" class="preview-input" placeholder="Enter ${label.toLowerCase()}">`;
                        break;
                    case 'textarea':
                        previewHTML += `<textarea class="preview-input" rows="4" placeholder="Enter ${label.toLowerCase()}"></textarea>`;
                        break;
                    case 'file':
                        previewHTML += `<input type="file" class="preview-input">`;
                        break;
                }

                previewHTML += `</div>`;
            });

            previewHTML += `</div>`;
        }

        previewContent.innerHTML = previewHTML;
    }

    // Form Submit Handler
    if (sportsForm) {
        sportsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            showToast('Sports program saved successfully!', 'success');
            createProgramModal.style.display = 'none';
            resetForm();
        });
    }

    // Reset Form
    function resetForm() {
        if (sportsForm) sportsForm.reset();
        questionsContainer.innerHTML = '';
        questionCounter = 0;
        otherSportField.style.display = 'none';
    }

    // Render Applications Table
    function renderApplicationsTable(filter = '') {
        if (!applicationsTableBody) return;

        const filtered = applications.filter(app => {
            if (filter) {
                const searchLower = filter.toLowerCase();
                return app.name.toLowerCase().includes(searchLower) ||
                       app.sport.toLowerCase().includes(searchLower) ||
                       app.division.toLowerCase().includes(searchLower);
            }
            return true;
        });

        applicationsTableBody.innerHTML = filtered.map(app => {
            const paymentClass = app.paymentStatus === 'Paid' ? 'status-paid' : 'status-not-paid';
            const statusClass = app.status === 'Pending' ? 'status-pending' : 
                               app.status === 'Approved' ? 'status-approved' : 'status-rejected';

            return `
                <tr data-app-id="${app.id}">
                    <td style="font-weight:600;">${app.name}</td>
                    <td>${app.sport}</td>
                    <td style="font-size:13px;">${app.division}</td>
                    <td>${app.contact}</td>
                    <td>${app.dateApplied}</td>
                    <td><span class="payment-badge ${paymentClass}">${app.paymentStatus}</span></td>
                    <td><span class="status-badge ${statusClass}">${app.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-approve" data-id="${app.id}" title="Approve">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                            <button class="btn-action btn-reject" data-id="${app.id}" title="Reject">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Attach event listeners
        document.querySelectorAll('.btn-approve').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                handleApprove(id);
            });
        });

        document.querySelectorAll('.btn-reject').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                handleReject(id);
            });
        });
    }

    // Search Applications
    if (searchApplications) {
        searchApplications.addEventListener('input', function() {
            renderApplicationsTable(this.value);
        });
    }

    // Handle Approve
    function handleApprove(id) {
        currentApplicationId = id;
        paymentModal.style.display = 'flex';
    }

    // Payment Modal Handlers
    if (btnPaid) {
        btnPaid.addEventListener('click', function() {
            approveApplication(currentApplicationId, 'Paid');
        });
    }

    if (btnNotPaid) {
        btnNotPaid.addEventListener('click', function() {
            approveApplication(currentApplicationId, 'Not Paid');
        });
    }

    if (closePaymentModal) {
        closePaymentModal.addEventListener('click', function() {
            paymentModal.style.display = 'none';
            currentApplicationId = null;
        });
    }

    // Approve Application
    function approveApplication(id, paymentStatus) {
        const index = applications.findIndex(app => app.id === id);
        if (index !== -1) {
            applications[index].paymentStatus = paymentStatus;
            applications.splice(index, 1); // Remove from list
            localStorage.setItem('sports_applications', JSON.stringify(applications));
            
            paymentModal.style.display = 'none';
            currentApplicationId = null;
            
            renderApplicationsTable();
            showToast('Application approved successfully!', 'success');
        }
    }

    // Handle Reject
    function handleReject(id) {
        currentApplicationId = id;
        rejectModal.style.display = 'flex';
        
        // Reset checkboxes
        document.querySelectorAll('input[name="reject_reason"]').forEach(cb => cb.checked = false);
        document.getElementById('rejectOtherText').value = '';
        rejectOtherTextarea.style.display = 'none';
    }

    // Reject Other Checkbox Handler
    if (rejectOtherCheckbox) {
        rejectOtherCheckbox.addEventListener('change', function() {
            rejectOtherTextarea.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Confirm Reject
    if (confirmReject) {
        confirmReject.addEventListener('click', function() {
            const checkedReasons = Array.from(document.querySelectorAll('input[name="reject_reason"]:checked'))
                .map(cb => cb.value);
            
            if (checkedReasons.length === 0) {
                showToast('Please select at least one rejection reason', 'error');
                return;
            }

            if (checkedReasons.includes('Other')) {
                const otherText = document.getElementById('rejectOtherText').value.trim();
                if (!otherText) {
                    showToast('Please specify the other reason', 'error');
                    return;
                }
            }

            rejectApplication(currentApplicationId, checkedReasons);
        });
    }

    // Cancel Reject
    if (cancelReject) {
        cancelReject.addEventListener('click', function() {
            rejectModal.style.display = 'none';
            currentApplicationId = null;
        });
    }

    if (closeRejectModal) {
        closeRejectModal.addEventListener('click', function() {
            rejectModal.style.display = 'none';
            currentApplicationId = null;
        });
    }

    // Reject Application
    function rejectApplication(id, reasons) {
        const index = applications.findIndex(app => app.id === id);
        if (index !== -1) {
            applications.splice(index, 1); // Remove from list
            localStorage.setItem('sports_applications', JSON.stringify(applications));
            
            rejectModal.style.display = 'none';
            currentApplicationId = null;
            
            renderApplicationsTable();
            showToast('Application rejected successfully', 'success');
        }
    }

    // Toast Notification Function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        
        const iconMap = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
        };

        toast.innerHTML = `
            <div class="toast-icon ${type}">
                ${iconMap[type] || iconMap.success}
            </div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        document.body.appendChild(toast);

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        });

        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Close modals when clicking outside
    createProgramModal.addEventListener('click', function(e) {
        if (e.target === createProgramModal) {
            createProgramModal.style.display = 'none';
            resetForm();
        }
    });

    previewModal.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.style.display = 'none';
            isMaximized = false;
            previewModalBox.classList.remove('maximized');
        }
    });

    paymentModal.addEventListener('click', function(e) {
        if (e.target === paymentModal) {
            paymentModal.style.display = 'none';
            currentApplicationId = null;
        }
    });

    rejectModal.addEventListener('click', function(e) {
        if (e.target === rejectModal) {
            rejectModal.style.display = 'none';
            currentApplicationId = null;
        }
    });
});
