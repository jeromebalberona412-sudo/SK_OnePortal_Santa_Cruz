/**
 * Google Forms-style Scholarship Application
 */
(function () {
    'use strict';

    const form = document.getElementById('scholarshipApplicationForm');
    const kkProfileFieldsContainer = document.getElementById('kkProfileFieldsContainer');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const successModal = document.getElementById('successModal');
    const closeSuccessModal = document.getElementById('closeSuccessModal');

    // ── KK Profile Data Integration ───────────────────────────────────────────
    function loadKKProfileData() {
        if (!kkProfileFieldsContainer) return;

        // Fetch selected KK Profiling fields from scholarship program configuration
        // In production, this would be fetched from the API based on the active scholarship program
        const scholarshipProgram = JSON.parse(localStorage.getItem('scholar_application_forms') || '[]')[0];
        const selectedFields = scholarshipProgram?.kkProfilingFields || [
            'full_name', 'birthday', 'age', 'sex', 'civil_status',
            'contact_number', 'home_address', 'current_school',
            'year_level', 'course_strand', 'barangay', 'city_municipality',
            'province', 'region'
        ]; // Default to all fields if none selected

        // Simulated KK Profile data (in production, fetch from API)
        const kkProfileData = {
            full_name: 'Juan Dela Cruz',
            birthday: 'January 15, 2006',
            age: '20',
            sex: 'Male',
            civil_status: 'Single',
            contact_number: '09123456789',
            email: 'juan@example.com',
            home_address: 'Barangay Santa Cruz',
            current_school: 'Laguna State Polytechnic University',
            year_level: '2nd Year',
            course_strand: 'BS Information Technology',
            barangay: 'Santa Cruz',
            city_municipality: 'Santa Cruz',
            province: 'Laguna',
            region: 'Region IV-A (CALABARZON)'
        };

        const fieldLabels = {
            full_name: 'Full Name',
            birthday: 'Birthday',
            age: 'Age',
            sex: 'Sex',
            civil_status: 'Civil Status',
            contact_number: 'Contact Number',
            email: 'Email Address',
            home_address: 'Home Address',
            current_school: 'Current School',
            year_level: 'Year Level',
            course_strand: 'Course / Strand',
            barangay: 'Barangay',
            city_municipality: 'City/Municipality',
            province: 'Province',
            region: 'Region'
        };

        let fieldsHtml = '';
        selectedFields.forEach(field => {
            if (!kkProfileData[field]) return;
            const value = kkProfileData[field] || '—';
            const isFullWidth = field === 'full_name' || field === 'home_address';
            fieldsHtml += `
                <div class="gf-kk-field ${isFullWidth ? 'full-width' : ''}">
                    <span class="gf-kk-field-label">${fieldLabels[field] || field}</span>
                    <span class="gf-kk-field-value">${value}</span>
                </div>
            `;
        });

        if (fieldsHtml === '') {
            fieldsHtml = '<p style="text-align:center;color:#64748b;font-size:14px;padding:20px;">No KK Profiling fields selected for this scholarship program.</p>';
        }

        kkProfileFieldsContainer.innerHTML = fieldsHtml;

        // Store KK Profile data for form submission
        window.kkProfileData = kkProfileData;
    }

    // Load KK Profile data on page load (always displayed, no toggle)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKKProfileData);
    } else {
        loadKKProfileData();
    }

    // ── File Upload Handling ─────────────────────────────────────────────────
    function setupFileUpload(inputId, previewId) {
        const input = document.querySelector(`input[name="${inputId}"]`);
        const preview = document.getElementById(previewId);
        
        if (!input || !preview) return;

        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) {
                preview.classList.remove('show');
                preview.innerHTML = '';
                return;
            }

            const fileSize = formatFileSize(file.size);
            preview.innerHTML = `
                <div class="gf-file-name">${file.name}</div>
                <div class="gf-file-size">${fileSize}</div>
                <div class="gf-file-remove" data-input="${inputId}">Remove</div>
            `;
            preview.classList.add('show');

            // Add remove functionality
            const removeBtn = preview.querySelector('.gf-file-remove');
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                preview.classList.remove('show');
                preview.innerHTML = '';
            });
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Setup file uploads
    setupFileUpload('cor_file', 'corPreview');
    setupFileUpload('grades_file', 'gradesPreview');
    setupFileUpload('school_id_file', 'schoolIdPreview');
    setupFileUpload('indigency_file', 'indigencyPreview');

    // ── Form Validation ─────────────────────────────────────────────────────
    function validateForm() {
        if (!form) return true;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalid = null;

        requiredFields.forEach(function (field) {
            if (field.type === 'file') {
                if (!field.files || field.files.length === 0) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            } else {
                if (!field.value.trim()) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            }
        });

        if (!isValid && firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    // ── Form Submission ────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validateForm()) {
                alert('Please fill in all required fields and upload all required documents.');
                return;
            }

            // Disable submit button and show spinner
            if (submitBtn) {
                submitBtn.disabled = true;
                const label = submitBtn.querySelector('.gf-btn-label');
                const spinner = submitBtn.querySelector('.gf-btn-spinner');
                if (label) label.textContent = 'Submitting...';
                if (spinner) spinner.hidden = false;
            }

            // Collect form data
            const formData = {};
            form.querySelectorAll('input, select, textarea').forEach(function (field) {
                if (!field.name) return;
                if (field.type === 'file') {
                    if (field.files && field.files.length > 0) {
                        formData[field.name] = field.files[0].name;
                    }
                } else if (field.type === 'checkbox') {
                    formData[field.name] = field.checked;
                } else {
                    formData[field.name] = field.value;
                }
            });

            // Include KK Profile data (always included)
            if (window.kkProfileData) {
                formData.kk_profile_data = window.kkProfileData;
            }

            // Save to localStorage for demo purposes
            const scholarshipRequests = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
            const newApplication = {
                id: Date.now(),
                ...formData,
                status: 'Pending Review',
                submitted_at: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                submitted_time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
            };
            scholarshipRequests.unshift(newApplication);
            localStorage.setItem('scholarship_requests', JSON.stringify(scholarshipRequests));

            // Simulate API call delay
            setTimeout(function () {
                // Reset submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    const label = submitBtn.querySelector('.gf-btn-label');
                    const spinner = submitBtn.querySelector('.gf-btn-spinner');
                    if (label) label.textContent = 'Submit Application';
                    if (spinner) spinner.hidden = true;
                }

                // Show success modal
                if (successModal) {
                    successModal.hidden = false;
                    document.body.style.overflow = 'hidden';
                }

                // Reset form
                form.reset();
                
                // Reset file previews
                document.querySelectorAll('.gf-file-preview').forEach(function (preview) {
                    preview.classList.remove('show');
                    preview.innerHTML = '';
                });
            }, 1500);
        });
    }

    // ── Cancel Button ───────────────────────────────────────────────────────
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (confirm('Are you sure you want to cancel? All your progress will be lost.')) {
                form.reset();
                document.querySelectorAll('.gf-file-preview').forEach(function (preview) {
                    preview.classList.remove('show');
                    preview.innerHTML = '';
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // ── Success Modal ───────────────────────────────────────────────────────
    if (closeSuccessModal) {
        closeSuccessModal.addEventListener('click', function () {
            if (successModal) {
                successModal.hidden = true;
                document.body.style.overflow = '';
            }
        });
    }

    // Close modal on backdrop click
    if (successModal) {
        successModal.addEventListener('click', function (e) {
            if (e.target === successModal) {
                successModal.hidden = true;
                document.body.style.overflow = '';
            }
        });
    }

    // ── Auto-save Draft (Demo Mode) ─────────────────────────────────────────
    function saveDraft() {
        if (!form) return;

        const formData = {};
        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (!field.name) return;
            if (field.type === 'file') {
                if (field.files && field.files.length > 0) {
                    formData[field.name] = field.files[0].name;
                }
            } else if (field.type === 'checkbox') {
                formData[field.name] = field.checked;
            } else {
                formData[field.name] = field.value;
            }
        });

        localStorage.setItem('scholarship_form_draft', JSON.stringify(formData));
    }

    // Save draft every 30 seconds
    setInterval(saveDraft, 30000);

    // Load draft on page load
    function loadDraft() {
        const draft = localStorage.getItem('scholarship_form_draft');
        if (!draft || !form) return;

        const formData = JSON.parse(draft);
        Object.keys(formData).forEach(function (name) {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) return;

            if (field.type === 'checkbox') {
                field.checked = formData[name];
            } else if (field.type !== 'file') {
                field.value = formData[name];
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadDraft);
    } else {
        loadDraft();
    }
})();
