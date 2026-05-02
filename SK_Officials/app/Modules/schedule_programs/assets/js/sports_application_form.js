// Sports Application Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Sample Created Programs Data
    const SAMPLE_PROGRAMS = [
        {
            id: 1,
            programName: 'Basketball Tournament 2026',
            startDate: '2026-05-15',
            endDate: '2026-05-20',
            startTime: '08:00',
            endTime: '17:00',
            sport: 'basketball',
            announcement: 'All participants must bring their own jerseys and equipment. Registration fee is ₱500 per team.',
            questions: [
                { label: 'Team Name', type: 'text', required: 'required' },
                { label: 'Team Captain Name', type: 'text', required: 'required' },
                { label: 'Contact Number', type: 'phone', required: 'required' },
                { label: 'Number of Players', type: 'number', required: 'required' }
            ],
            requirements: [
                { name: 'Team Photo', type: 'image', required: 'required', description: 'Submit a team photo with all members' },
                { name: 'Medical Certificate', type: 'pdf', required: 'required', description: 'Valid medical certificate for each player' },
                { name: 'Waiver Form', type: 'pdf', required: 'required', description: 'Signed waiver form by team captain' }
            ],
            divisions: 4,
            createdAt: 'May 15, 2026'
        },
        {
            id: 2,
            programName: 'Volleyball League',
            startDate: '2026-06-10',
            endDate: '2026-06-15',
            startTime: '09:00',
            endTime: '16:00',
            sport: 'volleyball',
            announcement: 'Open to all youth ages 15-30. Bring valid ID for verification.',
            questions: [
                { label: 'Full Name', type: 'text', required: 'required' },
                { label: 'Age', type: 'number', required: 'required' },
                { label: 'Email Address', type: 'email', required: 'required' }
            ],
            requirements: [
                { name: 'Valid ID', type: 'image', required: 'required', description: 'Government-issued ID or school ID' },
                { name: 'Birth Certificate', type: 'pdf', required: 'optional', description: 'For age verification if needed' }
            ],
            divisions: 2,
            createdAt: 'Jun 10, 2026'
        }
    ];

    // Load programs from localStorage or use sample data
    let createdPrograms = JSON.parse(localStorage.getItem('created_sports_programs') || 'null');
    if (!createdPrograms || createdPrograms.length === 0) {
        createdPrograms = [...SAMPLE_PROGRAMS];
        localStorage.setItem('created_sports_programs', JSON.stringify(createdPrograms));
    }
    
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
    const addRequirementBtn = document.getElementById('addRequirementBtn');
    const requirementsContainer = document.getElementById('requirementsContainer');
    const requirementTemplate = document.getElementById('requirementTemplate');

    let questionCounter = 0;
    let requirementCounter = 0;
    let isMaximized = false;
    let currentApplicationId = null;

    // Initialize
    renderApplicationsTable();
    renderCreatedProgramsTable();

    // Render Created Programs Table
    function renderCreatedProgramsTable() {
        const tbody = document.getElementById('createdProgramsTableBody');
        if (!tbody) return;

        tbody.innerHTML = createdPrograms.map(program => {
            const date = new Date(program.startDate).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            return `
                <tr>
                    <td>
                        <div style="font-weight: 600; font-size: 13px; color: #111827;">${program.programName}</div>
                        <div style="font-size: 11px; color: #6b7280;">${program.divisions || 0} Divisions</div>
                    </td>
                    <td style="font-size: 12px; color: #4b5563;">${date}</td>
                    <td>
                        <button type="button" class="btn-view-program" data-program-id="${program.id}" title="View Details">
                            View
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // Attach event listeners to View buttons
        tbody.querySelectorAll('.btn-view-program').forEach(btn => {
            btn.addEventListener('click', function() {
                const programId = parseInt(this.getAttribute('data-program-id'));
                openViewProgramModal(programId);
            });
        });
    }

    // Open View Program Modal
    function openViewProgramModal(programId) {
        const program = createdPrograms.find(p => p.id === programId);
        if (!program) return;

        const viewProgramModal = document.getElementById('viewProgramModal');
        const viewProgramContent = document.getElementById('viewProgramContent');
        
        if (!viewProgramModal || !viewProgramContent) return;

        // Format dates and times
        const startDate = new Date(program.startDate).toLocaleDateString('en-US', { 
            month: 'long', 
            day: 'numeric', 
            year: 'numeric' 
        });
        const endDate = new Date(program.endDate).toLocaleDateString('en-US', { 
            month: 'long', 
            day: 'numeric', 
            year: 'numeric' 
        });
        
        const formatTime = (time) => {
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        };

        const sportName = program.sport === 'other' 
            ? (program.otherSport || 'Other Sport')
            : program.sport.charAt(0).toUpperCase() + program.sport.slice(1);

        // Build questions HTML
        let questionsHTML = '';
        if (program.questions && program.questions.length > 0) {
            questionsHTML = program.questions.map((q, index) => {
                const typeLabels = {
                    'text': 'Text Input',
                    'textarea': 'Long Text',
                    'file': 'File Upload',
                    'email': 'Email',
                    'number': 'Number',
                    'phone': 'Phone Number'
                };
                
                return `
                    <div class="program-question-item">
                        <div class="program-question-number">Question ${index + 1}</div>
                        <div class="program-question-label">${q.label}</div>
                        <div class="program-question-meta">
                            <span class="program-question-type">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                ${typeLabels[q.type] || q.type}
                            </span>
                            <span class="program-question-required">
                                ${q.required === 'required' 
                                    ? '<span class="required-indicator">● Required</span>' 
                                    : '○ Optional'}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            questionsHTML = '<p style="color: #9ca3af; font-style: italic;">No questions added</p>';
        }

        // Build requirements HTML
        let requirementsHTML = '';
        if (program.requirements && program.requirements.length > 0) {
            requirementsHTML = program.requirements.map((req, index) => {
                const typeLabels = {
                    'pdf': 'PDF Document',
                    'image': 'Image (JPG, PNG)',
                    'any': 'Any File Type'
                };
                
                return `
                    <div class="program-question-item">
                        <div class="program-question-number">Requirement ${index + 1}</div>
                        <div class="program-question-label">${req.name}</div>
                        ${req.description ? `<p style="font-size: 12px; color: #6b7280; margin: 4px 0 8px 0;">${req.description}</p>` : ''}
                        <div class="program-question-meta">
                            <span class="program-question-type">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                ${typeLabels[req.type] || req.type}
                            </span>
                            <span class="program-question-required">
                                ${req.required === 'required' 
                                    ? '<span class="required-indicator">● Required</span>' 
                                    : '○ Optional'}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            requirementsHTML = '<p style="color: #9ca3af; font-style: italic;">No requirements added</p>';
        }

        viewProgramContent.innerHTML = `
            <div class="program-detail-section">
                <div class="program-detail-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    Program Information
                </div>
                <div class="program-detail-row">
                    <div class="program-detail-label">Program Name:</div>
                    <div class="program-detail-value">${program.programName}</div>
                </div>
                <div class="program-detail-row">
                    <div class="program-detail-label">Sport:</div>
                    <div class="program-detail-value">${sportName}</div>
                </div>
                <div class="program-detail-row">
                    <div class="program-detail-label">Start Date:</div>
                    <div class="program-detail-value">${startDate}</div>
                </div>
                <div class="program-detail-row">
                    <div class="program-detail-label">End Date:</div>
                    <div class="program-detail-value">${endDate}</div>
                </div>
                <div class="program-detail-row">
                    <div class="program-detail-label">Time:</div>
                    <div class="program-detail-value">${formatTime(program.startTime)} - ${formatTime(program.endTime)}</div>
                </div>
            </div>

            ${program.announcement ? `
            <div class="program-detail-section">
                <div class="program-detail-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                    Announcement / Instructions
                </div>
                <div class="program-announcement-box">
                    <div class="program-announcement-text">${program.announcement}</div>
                </div>
            </div>
            ` : ''}

            <div class="program-detail-section">
                <div class="program-detail-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Application Form Questions
                </div>
                <div class="program-questions-list">
                    ${questionsHTML}
                </div>
            </div>

            <div class="program-detail-section">
                <div class="program-detail-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Requirements for Application
                </div>
                <div class="program-questions-list">
                    ${requirementsHTML}
                </div>
            </div>
        `;

        viewProgramModal.style.display = 'flex';
    }

    // Close View Program Modal
    const closeViewProgramModal = document.getElementById('closeViewProgramModal');
    if (closeViewProgramModal) {
        closeViewProgramModal.addEventListener('click', function() {
            document.getElementById('viewProgramModal').style.display = 'none';
        });
    }

    // Close modal when clicking outside
    const viewProgramModal = document.getElementById('viewProgramModal');
    if (viewProgramModal) {
        viewProgramModal.addEventListener('click', function(e) {
            if (e.target === viewProgramModal) {
                viewProgramModal.style.display = 'none';
            }
        });
    }

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

    // Add Requirement Button
    if (addRequirementBtn) {
        addRequirementBtn.addEventListener('click', function() {
            addRequirement();
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

    // Function to Add Requirement
    function addRequirement(data = null) {
        requirementCounter++;
        
        const clone = requirementTemplate.content.cloneNode(true);
        const requirementItem = clone.querySelector('.requirement-item');
        
        const requirementNumber = clone.querySelector('.requirement-number');
        requirementNumber.textContent = `Requirement ${requirementCounter}`;
        
        const requirementName = clone.querySelector('.requirement-name');
        const requirementType = clone.querySelector('.requirement-type');
        const requirementRequired = clone.querySelector('.requirement-required');
        const requirementDescription = clone.querySelector('.requirement-description');
        const removeBtn = clone.querySelector('.btn-remove-requirement');

        if (data) {
            requirementName.value = data.name || '';
            requirementType.value = data.type || 'pdf';
            requirementRequired.value = data.required || 'required';
            requirementDescription.value = data.description || '';
        }

        removeBtn.addEventListener('click', function() {
            requirementItem.remove();
            updateRequirementNumbers();
            showToast('Requirement removed successfully', 'success');
        });

        requirementsContainer.appendChild(clone);
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

    // Update Requirement Numbers
    function updateRequirementNumbers() {
        const requirements = requirementsContainer.querySelectorAll('.requirement-item');
        requirements.forEach((requirement, index) => {
            const numberSpan = requirement.querySelector('.requirement-number');
            numberSpan.textContent = `Requirement ${index + 1}`;
        });
        requirementCounter = requirements.length;
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
            
            // Collect form data
            const programName = document.getElementById('programName').value.trim();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const sport = sportsTypeSelect.value;
            const otherSport = document.getElementById('otherSport').value.trim();
            const announcement = document.getElementById('announcement').value.trim();
            
            // Collect questions
            const questions = [];
            const questionItems = questionsContainer.querySelectorAll('.question-item');
            questionItems.forEach(item => {
                const label = item.querySelector('.question-label').value.trim();
                const type = item.querySelector('.question-type').value;
                const required = item.querySelector('.question-required').value;
                
                if (label) {
                    questions.push({ label, type, required });
                }
            });

            // Collect requirements
            const requirements = [];
            const requirementItems = requirementsContainer.querySelectorAll('.requirement-item');
            requirementItems.forEach(item => {
                const name = item.querySelector('.requirement-name').value.trim();
                const type = item.querySelector('.requirement-type').value;
                const required = item.querySelector('.requirement-required').value;
                const description = item.querySelector('.requirement-description').value.trim();
                
                if (name) {
                    requirements.push({ name, type, required, description });
                }
            });

            // Calculate divisions (for display purposes)
            const divisions = questions.length > 0 ? Math.ceil(questions.length / 2) : 0;

            // Create new program object
            const newProgram = {
                id: Date.now(), // Simple ID generation
                programName,
                startDate,
                endDate,
                startTime,
                endTime,
                sport,
                otherSport: sport === 'other' ? otherSport : '',
                announcement,
                questions,
                requirements,
                divisions,
                createdAt: new Date().toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                })
            };

            // Add to programs array
            createdPrograms.push(newProgram);
            
            // Save to localStorage
            localStorage.setItem('created_sports_programs', JSON.stringify(createdPrograms));
            
            // Re-render the created programs table
            renderCreatedProgramsTable();
            
            showToast('Sports program created successfully!', 'success');
            createProgramModal.style.display = 'none';
            resetForm();
        });
    }

    // Reset Form
    function resetForm() {
        if (sportsForm) sportsForm.reset();
        questionsContainer.innerHTML = '';
        requirementsContainer.innerHTML = '';
        questionCounter = 0;
        requirementCounter = 0;
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
