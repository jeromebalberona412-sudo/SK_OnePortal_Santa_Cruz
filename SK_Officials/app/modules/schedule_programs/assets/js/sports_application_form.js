// Sports Application Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Elements
    const sportsTypeSelect = document.getElementById('sportsType');
    const otherSportField = document.getElementById('otherSportField');
    const ageClassificationSection = document.getElementById('ageClassificationSection');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const questionsContainer = document.getElementById('questionsContainer');
    const questionTemplate = document.getElementById('questionTemplate');
    const sportsForm = document.getElementById('sportsApplicationForm');
    const previewBtn = document.getElementById('previewBtn');
    const previewBtnBottom = document.getElementById('previewBtnBottom');
    const previewModal = document.getElementById('previewModal');
    const previewModalBox = document.getElementById('previewModalBox');
    const previewClose = document.getElementById('previewClose');
    const previewToggle = document.getElementById('previewToggle');
    const previewContent = document.getElementById('previewContent');
    const deleteModal = document.getElementById('deleteModal');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    let questionCounter = 0;
    let isMaximized = false;
    let questionToDelete = null;

    // Sports Type Change Handler
    sportsTypeSelect.addEventListener('change', function() {
        const selectedSport = this.value;
        
        // Show/hide "Other" sport input field
        if (selectedSport === 'other') {
            otherSportField.style.display = 'block';
            document.getElementById('otherSport').required = true;
        } else {
            otherSportField.style.display = 'none';
            document.getElementById('otherSport').required = false;
        }

        // Show/hide Age Classification for Basketball
        if (selectedSport === 'basketball') {
            ageClassificationSection.style.display = 'block';
        } else {
            ageClassificationSection.style.display = 'none';
            // Uncheck all divisions when not basketball
            document.querySelectorAll('input[name="divisions[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    });

    // Add Question Button Handler
    addQuestionBtn.addEventListener('click', function() {
        addQuestion();
    });

    // Function to Add Question
    function addQuestion(data = null) {
        questionCounter++;
        
        // Clone template
        const clone = questionTemplate.content.cloneNode(true);
        const questionItem = clone.querySelector('.question-item');
        
        // Set question number
        const questionNumber = clone.querySelector('.question-number');
        questionNumber.textContent = `Question ${questionCounter}`;
        
        // Get elements
        const questionType = clone.querySelector('.question-type');
        const questionLabel = clone.querySelector('.question-label');
        const questionRequired = clone.querySelector('.question-required');
        const optionsGroup = clone.querySelector('.options-group');
        const optionsList = clone.querySelector('.options-list');
        const addOptionBtn = clone.querySelector('.btn-add-option');
        const removeBtn = clone.querySelector('.btn-remove-question');
        const duplicateBtn = clone.querySelector('.btn-duplicate-question');

        // Set data if provided
        if (data) {
            questionType.value = data.type || 'text';
            questionLabel.value = data.label || '';
            questionRequired.value = data.required || 'required';
            
            if (['select', 'radio', 'checkbox'].includes(data.type)) {
                optionsGroup.style.display = 'block';
                if (data.options) {
                    const opts = data.options.split(',').map(o => o.trim());
                    opts.forEach(opt => addOptionToList(optionsList, opt));
                }
            }
        }

        // Question Type Change Handler
        questionType.addEventListener('change', function() {
            const type = this.value;
            
            // Show options field for select, radio, checkbox
            if (['select', 'radio', 'checkbox'].includes(type)) {
                optionsGroup.style.display = 'block';
                // Add default options if empty
                if (optionsList.children.length === 0) {
                    addOptionToList(optionsList, 'Option 1');
                }
            } else {
                optionsGroup.style.display = 'none';
            }
        });

        // Add Option Button Handler
        addOptionBtn.addEventListener('click', function() {
            const optionCount = optionsList.children.length + 1;
            addOptionToList(optionsList, `Option ${optionCount}`);
        });

        // Remove Button Handler
        removeBtn.addEventListener('click', function() {
            questionToDelete = questionItem;
            deleteModal.style.display = 'flex';
        });

        // Duplicate Button Handler
        duplicateBtn.addEventListener('click', function() {
            const currentData = {
                type: questionType.value,
                label: questionLabel.value + ' (Copy)',
                required: questionRequired.value,
                options: getOptionsFromList(optionsList)
            };
            addQuestion(currentData);
        });

        // Append to container
        questionsContainer.appendChild(clone);
    }

    // Function to Update Question Numbers
    function updateQuestionNumbers() {
        const questions = questionsContainer.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const numberSpan = question.querySelector('.question-number');
            numberSpan.textContent = `Question ${index + 1}`;
        });
        questionCounter = questions.length;
    }

    // Function to Add Option to List
    function addOptionToList(optionsList, value = '') {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.innerHTML = `
            <input type="text" class="form-control option-input" placeholder="Enter option" value="${value}">
            <button type="button" class="btn-remove-option">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        `;

        // Remove option handler
        const removeBtn = optionItem.querySelector('.btn-remove-option');
        removeBtn.addEventListener('click', function() {
            if (optionsList.children.length > 1) {
                optionItem.remove();
            } else {
                showToast('At least one option is required', 'warning');
            }
        });

        optionsList.appendChild(optionItem);
    }

    // Function to Get Options from List
    function getOptionsFromList(optionsList) {
        const options = [];
        const inputs = optionsList.querySelectorAll('.option-input');
        inputs.forEach(input => {
            const value = input.value.trim();
            if (value) options.push(value);
        });
        return options.join(', ');
    }

    // Preview Button Handler
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            generatePreview();
            previewModal.style.display = 'flex';
        });
    }

    // Preview Button Bottom Handler
    if (previewBtnBottom) {
        previewBtnBottom.addEventListener('click', function() {
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
            updateToggleIcon();
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
            updateToggleIcon();
        });
    }

    // Update Toggle Icon
    function updateToggleIcon() {
        if (!previewToggle) return;
        
        const icon = previewToggle.querySelector('svg');
        if (isMaximized) {
            // Restore down icon (double box overlapping)
            icon.innerHTML = '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>';
            previewToggle.setAttribute('title', 'Restore Down');
        } else {
            // Maximize icon (single box)
            icon.innerHTML = '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>';
            previewToggle.setAttribute('title', 'Maximize');
        }
    }

    // Close modal when clicking outside
    previewModal.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.style.display = 'none';
            isMaximized = false;
            previewModalBox.classList.remove('maximized');
            updateToggleIcon();
        }
    });

    // Delete Modal Handlers
    if (cancelDelete) {
        cancelDelete.addEventListener('click', function() {
            deleteModal.style.display = 'none';
            questionToDelete = null;
        });
    }

    if (confirmDelete) {
        confirmDelete.addEventListener('click', function() {
            if (questionToDelete) {
                questionToDelete.remove();
                updateQuestionNumbers();
                deleteModal.style.display = 'none';
                questionToDelete = null;
                showToast('Question removed successfully', 'success');
            }
        });
    }

    // Close delete modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
            questionToDelete = null;
        }
    });

    // Toast Notification Function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        
        const iconMap = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
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

        // Close button handler
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        });

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Generate Preview Function
    function generatePreview() {
        console.log('=== GENERATING PREVIEW ===');
        
        const sportsType = sportsTypeSelect.value;
        const otherSport = document.getElementById('otherSport').value.trim();
        const announcement = document.getElementById('announcement').value.trim();
        const questions = questionsContainer.querySelectorAll('.question-item');

        console.log('Total questions found:', questions.length);

        let previewHTML = '';

        // Sport Type
        previewHTML += `
            <div class="preview-section">
                <div class="preview-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    Sport
                </div>
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
                    <div class="preview-announcement-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                        Announcement
                    </div>
                    <div class="preview-announcement-text">${announcement}</div>
                </div>
            `;
        }

        // Age Divisions (if basketball)
        if (sportsType === 'basketball') {
            const checkedDivisions = document.querySelectorAll('input[name="divisions[]"]:checked');
            if (checkedDivisions.length > 0) {
                previewHTML += `
                    <div class="preview-section">
                        <div class="preview-section-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                            Age Divisions
                        </div>
                        <div class="preview-divisions">
                `;
                
                const divisionLabels = {
                    'cadet': 'Cadet Division (15–17 years old)',
                    'youth': 'Youth Division (18–21 years old)',
                    'young_adult': 'Young Adult Division (22–25 years old)',
                    'senior': 'KK Senior Division (26–30 years old)'
                };

                checkedDivisions.forEach(checkbox => {
                    previewHTML += `
                        <div class="preview-division">
                            <input type="radio" disabled>
                            <span class="preview-division-label">${divisionLabels[checkbox.value]}</span>
                        </div>
                    `;
                });

                previewHTML += `
                        </div>
                    </div>
                `;
            }
        }

        // Questions
        if (questions.length > 0) {
            previewHTML += `
                <div class="preview-section">
                    <div class="preview-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Application Questions
                    </div>
            `;

            questions.forEach((question, index) => {
                const label = question.querySelector('.question-label').value.trim();
                const type = question.querySelector('.question-type').value;
                const required = question.querySelector('.question-required').value;
                const optionsList = question.querySelector('.options-list');
                const options = getOptionsFromList(optionsList);

                console.log(`Question ${index + 1}:`, {
                    label: label,
                    type: type,
                    required: required,
                    options: options
                });

                // Skip questions without labels
                if (!label) {
                    console.log(`Skipping question ${index + 1} - no label`);
                    return;
                }

                const requiredBadge = required === 'required' ? '<span class="required-badge">*</span>' : '';

                previewHTML += `
                    <div class="preview-field">
                        <label class="preview-label">${index + 1}. ${label} ${requiredBadge}</label>
                `;

                switch(type) {
                    case 'text':
                        previewHTML += `<input type="text" class="preview-input" placeholder="Enter ${label.toLowerCase()}">`;
                        break;
                    case 'email':
                        previewHTML += `<input type="email" class="preview-input" placeholder="Enter ${label.toLowerCase()}">`;
                        break;
                    case 'number':
                        previewHTML += `<input type="number" class="preview-input" placeholder="Enter ${label.toLowerCase()}">`;
                        break;
                    case 'phone':
                        previewHTML += `<input type="tel" class="preview-input" placeholder="Enter ${label.toLowerCase()}">`;
                        break;
                    case 'textarea':
                        previewHTML += `<textarea class="preview-input" rows="4" placeholder="Enter ${label.toLowerCase()}"></textarea>`;
                        break;
                    case 'file':
                        previewHTML += `
                            <div class="file-upload-wrapper">
                                <input type="file" class="preview-input preview-file-input" data-preview-id="file_${index}">
                                <div class="file-upload-preview" id="preview_file_${index}"></div>
                            </div>
                        `;
                        break;
                    case 'select':
                        if (options) {
                            const opts = options.split(',').map(o => o.trim()).filter(o => o);
                            console.log(`Select options for question ${index + 1}:`, opts);
                            if (opts.length > 0) {
                                previewHTML += `<select class="preview-input"><option>-- Select --</option>`;
                                opts.forEach(opt => {
                                    previewHTML += `<option>${opt}</option>`;
                                });
                                previewHTML += `</select>`;
                            } else {
                                previewHTML += `<select class="preview-input"><option>-- No options available --</option></select>`;
                            }
                        } else {
                            previewHTML += `<select class="preview-input"><option>-- No options available --</option></select>`;
                        }
                        break;
                    case 'radio':
                        if (options) {
                            const opts = options.split(',').map(o => o.trim()).filter(o => o);
                            console.log(`Radio options for question ${index + 1}:`, opts);
                            if (opts.length > 0) {
                                previewHTML += `<div class="preview-radio-group">`;
                                opts.forEach(opt => {
                                    previewHTML += `
                                        <div class="preview-option">
                                            <input type="radio" name="preview_${index}">
                                            <span>${opt}</span>
                                        </div>
                                    `;
                                });
                                previewHTML += `</div>`;
                            } else {
                                previewHTML += `<p class="preview-no-options">No options available</p>`;
                            }
                        } else {
                            previewHTML += `<p class="preview-no-options">No options available</p>`;
                        }
                        break;
                    case 'checkbox':
                        if (options) {
                            const opts = options.split(',').map(o => o.trim()).filter(o => o);
                            console.log(`Checkbox options for question ${index + 1}:`, opts);
                            if (opts.length > 0) {
                                previewHTML += `<div class="preview-checkbox-group">`;
                                opts.forEach(opt => {
                                    previewHTML += `
                                        <div class="preview-option">
                                            <input type="checkbox">
                                            <span>${opt}</span>
                                        </div>
                                    `;
                                });
                                previewHTML += `</div>`;
                            } else {
                                previewHTML += `<p class="preview-no-options">No options available</p>`;
                            }
                        } else {
                            previewHTML += `<p class="preview-no-options">No options available</p>`;
                        }
                        break;
                }

                previewHTML += `</div>`;
            });

            previewHTML += `</div>`;
        } else {
            console.log('No questions to display in preview');
        }

        console.log('Preview HTML length:', previewHTML.length);
        previewContent.innerHTML = previewHTML;
        console.log('Preview content updated');

        // Add file upload preview handlers
        setTimeout(() => {
            const fileInputs = previewContent.querySelectorAll('.preview-file-input');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    handleFilePreview(e.target);
                });
            });
        }, 100);
    }

    // Handle File Preview
    function handleFilePreview(input) {
        const file = input.files[0];
        if (!file) return;

        const previewId = input.getAttribute('data-preview-id');
        const previewContainer = document.getElementById('preview_' + previewId);
        
        if (!previewContainer) return;

        const fileSize = (file.size / 1024).toFixed(2); // KB
        const fileName = file.name;
        const fileType = file.type;

        // If it's an image, show image preview
        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let previewHTML = `
                    <div class="file-preview-item">
                        <div class="file-preview-info">
                            <div class="file-preview-name">${fileName}</div>
                            <div class="file-preview-size">${fileSize} KB</div>
                        </div>
                        <button type="button" class="file-preview-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <img src="${e.target.result}" class="file-preview-image" alt="Preview">
                `;
                previewContainer.innerHTML = previewHTML;
                previewContainer.classList.add('active');
                
                // Add remove button handler
                const removeBtn = previewContainer.querySelector('.file-preview-remove');
                removeBtn.addEventListener('click', function() {
                    previewContainer.classList.remove('active');
                    previewContainer.innerHTML = '';
                    input.value = '';
                });
            };
            reader.readAsDataURL(file);
        } else {
            // For non-image files, show file info only
            let previewHTML = `
                <div class="file-preview-item">
                    <div class="file-preview-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div class="file-preview-info">
                        <div class="file-preview-name">${fileName}</div>
                        <div class="file-preview-size">${fileSize} KB</div>
                    </div>
                    <button type="button" class="file-preview-remove">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            `;
            previewContainer.innerHTML = previewHTML;
            previewContainer.classList.add('active');
            
            // Add remove button handler
            const removeBtn = previewContainer.querySelector('.file-preview-remove');
            removeBtn.addEventListener('click', function() {
                previewContainer.classList.remove('active');
                previewContainer.innerHTML = '';
                input.value = '';
            });
        }
    }

    // Form Submit Handler
    sportsForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate form
        if (!validateForm()) {
            return;
        }

        // Collect form data
        const formData = collectFormData();

        // Log form data (for testing)
        console.log('Form Data:', formData);

        // Show success message
        alert('Sports program saved successfully!');
        showToast('Sports program saved successfully!', 'success');

        // Here you would typically send the data to the server
        // Example:
        // fetch('/api/sports-programs', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //     },
        //     body: JSON.stringify(formData)
        // })
        // .then(response => response.json())
        // .then(data => {
        //     alert('Sports program saved successfully!');
        //     window.location.href = '/schedule-programs';
        // })
        // .catch(error => {
        //     console.error('Error:', error);
        //     alert('Error saving sports program. Please try again.');
        // });
    });

    // Function to Validate Form
    function validateForm() {
        // Check sports type
        const sportsType = sportsTypeSelect.value;
        if (!sportsType) {
            alert('Please select a sport type.');
            return false;
        }

        // Check other sport if selected
        if (sportsType === 'other') {
            const otherSport = document.getElementById('otherSport').value.trim();
            if (!otherSport) {
                alert('Please specify the other sport.');
                return false;
            }
        }

        // Check if at least one division is selected for basketball
        if (sportsType === 'basketball') {
            const checkedDivisions = document.querySelectorAll('input[name="divisions[]"]:checked');
            if (checkedDivisions.length === 0) {
                alert('Please select at least one age division for basketball.');
                return false;
            }
        }

        // Check if at least one question is added
        const questions = questionsContainer.querySelectorAll('.question-item');
        if (questions.length === 0) {
            alert('Please add at least one question to the application form.');
            return false;
        }

        // Validate each question
        for (let i = 0; i < questions.length; i++) {
            const question = questions[i];
            const label = question.querySelector('.question-label').value.trim();
            const type = question.querySelector('.question-type').value;
            
            if (!label) {
                alert(`Please enter a label for Question ${i + 1}.`);
                return false;
            }

            // Check options for select, radio, checkbox
            if (['select', 'radio', 'checkbox'].includes(type)) {
                const optionsList = question.querySelector('.options-list');
                const optionInputs = optionsList.querySelectorAll('.option-input');
                let hasValidOption = false;
                
                optionInputs.forEach(input => {
                    if (input.value.trim()) hasValidOption = true;
                });

                if (!hasValidOption) {
                    alert(`Please enter at least one option for Question ${i + 1}.`);
                    return false;
                }
            }
        }

        return true;
    }

    // Function to Collect Form Data
    function collectFormData() {
        const formData = {
            sports_type: sportsTypeSelect.value,
            other_sport: document.getElementById('otherSport').value.trim(),
            announcement: document.getElementById('announcement').value.trim(),
            divisions: [],
            questions: []
        };

        // Collect divisions (if basketball)
        if (sportsTypeSelect.value === 'basketball') {
            const checkedDivisions = document.querySelectorAll('input[name="divisions[]"]:checked');
            checkedDivisions.forEach(checkbox => {
                formData.divisions.push(checkbox.value);
            });
        }

        // Collect questions
        const questions = questionsContainer.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const type = question.querySelector('.question-type').value;
            const label = question.querySelector('.question-label').value.trim();
            const required = question.querySelector('.question-required').value === 'required';
            const optionsList = question.querySelector('.options-list');
            const options = getOptionsFromList(optionsList);

            formData.questions.push({
                order: index + 1,
                type: type,
                label: label,
                required: required,
                options: options ? options.split(',').map(opt => opt.trim()) : []
            });
        });

        return formData;
    }

    // Add initial question on page load (optional)
    // addQuestion({
    //     type: 'file',
    //     label: 'Upload School ID',
    //     required: true
    // });

    // addQuestion({
    //     type: 'file',
    //     label: 'Upload Birth Certificate',
    //     required: true
    // });

});
