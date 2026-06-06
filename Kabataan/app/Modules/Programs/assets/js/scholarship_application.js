/**
 * Scholarship Application — section navigation, profile photo, form submit (frontend only)
 */
(function () {
    'use strict';

    const SECTION_ORDER = ['kk-profile', 'personal', 'educational', 'background', 'additional', 'requirements'];

    const navItems = document.querySelectorAll('.sk-side__link');
    const panels = document.querySelectorAll('.sch-app-panel');
    const form = document.getElementById('scholarshipApplicationForm');
    const birthdateInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');

    // ── KK Profile Data Integration ───────────────────────────────────────────
    function loadKKProfileData() {
        const kkProfileContainer = document.getElementById('kkProfileFieldsContainer');
        if (!kkProfileContainer) return;

        // Simulate fetching scholarship program and KK Profile data
        // In production, this would be an API call to fetch the program and user's KK Profile
        const scholarshipProgram = JSON.parse(localStorage.getItem('scholar_application_forms') || '[]')[0];
        const kkProfilingFields = scholarshipProgram?.kkProfilingFields || [];

        if (kkProfilingFields.length === 0) {
            kkProfileContainer.innerHTML = `
                <div class="sch-app-field sch-app-field-span-3">
                    <p style="text-align:center;color:#64748b;font-size:14px;padding:40px 20px;background:#fff;border-radius:8px;border:1px dashed #cbd5e1;">
                        No KK Profiling fields selected for this scholarship program.
                    </p>
                </div>
            `;
            return;
        }

        // Simulated KK Profile data (in production, fetch from API)
        const kkProfileData = {
            last_name: 'Dela Cruz',
            first_name: 'Juan',
            middle_name: 'Santos',
            suffix: '',
            full_name: 'Dela Cruz, Juan Santos',
            birthday: '2005-03-15',
            age: 19,
            sex: 'Male',
            civil_status: 'Single',
            contact_number: '09171234567',
            email: 'juan.delacruz@email.com',
            home_address: '123 Sampaguita St., Brgy. Calios, Santa Cruz, Laguna',
            region: 'Region IV-A (CALABARZON)',
            province: 'Laguna',
            city: 'Santa Cruz',
            barangay: 'Calios',
            purok_zone: 'Zone 1',
            youth_classification: 'In-School Youth',
            youth_age_group: '15-17',
            education: 'College Undergraduate',
            current_school: 'Laguna State Polytechnic University',
            course_strand: 'Bachelor of Science in Information Technology',
            work_status: 'Not Employed',
            sk_voter: 'Yes',
            sk_voted: 'Yes',
            kk_assembly: 'Yes',
            vote_frequency: '3'
        };

        const fieldLabels = {
            last_name: 'Last Name',
            first_name: 'First Name',
            middle_name: 'Middle Name',
            suffix: 'Suffix',
            full_name: 'Full Name',
            birthday: 'Birthday',
            age: 'Age',
            sex: 'Sex',
            civil_status: 'Civil Status',
            contact_number: 'Contact Number',
            email: 'Email Address',
            home_address: 'Home Address',
            region: 'Region',
            province: 'Province',
            city: 'City/Municipality',
            barangay: 'Barangay',
            purok_zone: 'Purok/Zone',
            youth_classification: 'Youth Classification',
            youth_age_group: 'Youth Age Group',
            education: 'Educational Attainment',
            current_school: 'Current School',
            course_strand: 'Course / Strand',
            work_status: 'Work Status',
            sk_voter: 'Registered SK Voter',
            sk_voted: 'Voted Last Election',
            kk_assembly: 'Attended KK Assembly',
            vote_frequency: 'Number of KK Assembly Attendances'
        };

        let fieldsHtml = '';
        kkProfilingFields.forEach(field => {
            const value = kkProfileData[field] || '—';
            const isFullWidth = field === 'home_address' || field === 'full_name';
            fieldsHtml += `
                <div class="sch-app-field ${isFullWidth ? 'sch-app-field-span-3' : ''}">
                    <label style="font-size:13px;font-weight:600;color:#0369a1;margin-bottom:6px;display:block;">${fieldLabels[field] || field}</label>
                    <div style="font-size:15px;color:#111827;padding:10px 14px;background:#fff;border-radius:6px;border:1px solid #bae6fd;box-shadow:0 1px 2px rgba(0,0,0,0.05);cursor:not-allowed;">${value}</div>
                </div>
            `;
        });

        kkProfileContainer.innerHTML = fieldsHtml;

        // Store KK Profile data for form submission
        window.kkProfileData = kkProfileData;
    }

    // Load KK Profile data on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadKKProfileData);
    } else {
        loadKKProfileData();
    }

    function goToSection(sectionId) {
        if (!SECTION_ORDER.includes(sectionId)) return;

        navItems.forEach(function (item) {
            item.classList.toggle('is-active', item.dataset.section === sectionId);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.dataset.panel === sectionId);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    navItems.forEach(function (item) {
        item.addEventListener('click', function () {
            goToSection(item.dataset.section);
        });
    });

    if (birthdateInput && ageInput) {
        function updateAge() {
            const bday = new Date(birthdateInput.value);
            if (Number.isNaN(bday.getTime())) {
                ageInput.value = '';
                return;
            }
            const today = new Date();
            let age = today.getFullYear() - bday.getFullYear();
            const m = today.getMonth() - bday.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
            ageInput.value = age >= 0 ? age : '';
        }
        birthdateInput.addEventListener('change', updateAge);
        updateAge();
    }

    document.querySelectorAll('[data-save-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const currentStep = btn.dataset.saveStep;
            const data = {};
            form.querySelectorAll('input, select, textarea').forEach(function (field) {
                if (!field.name) return;
                if (field.type === 'checkbox') {
                    data[field.name] = field.checked;
                } else {
                    data[field.name] = field.value;
                }
            });
            localStorage.setItem('scholarship-form-draft', JSON.stringify(data));
            alert('Step saved: ' + currentStep);
        });
    });

    if (!form) return;

    const submitBtn = document.getElementById('scholSubmitBtn');
    const successModal = document.getElementById('scholSuccessModal');
    let isSubmitting = false;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (isSubmitting) return;

        const agreement = document.getElementById('formAgreement');
        if (!agreement?.checked) {
            alert('Please certify that all information provided is true and correct.');
            goToSection('requirements');
            agreement?.focus();
            return;
        }

        const email = document.getElementById('email');
        if (email?.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            alert('Please enter a valid email address.');
            goToSection('personal');
            email.focus();
            return;
        }

        const essay = document.getElementById('essay');
        if (essay?.value && essay.value.trim().length < 50) {
            alert('Your essay must be at least 50 characters.');
            goToSection('additional');
            essay.focus();
            return;
        }

        isSubmitting = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            const label = submitBtn.querySelector('.sch-app-btn-label');
            const spinner = submitBtn.querySelector('.sch-app-btn-spinner');
            if (label) label.textContent = 'Submitting...';
            if (spinner) spinner.hidden = false;
        }

        // Collect form data including KK Profile data
        const formData = {};
        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            if (!field.name) return;
            if (field.type === 'checkbox') {
                formData[field.name] = field.checked;
            } else {
                formData[field.name] = field.value;
            }
        });

        // Include KK Profile data if available
        if (window.kkProfileData) {
            formData.kk_profile_data = window.kkProfileData;
        }

        // Save to localStorage for demo purposes
        const scholarshipRequests = JSON.parse(localStorage.getItem('scholarship_requests') || '[]');
        const newApplication = {
            id: Date.now(),
            ...formData,
            status: 'Pending',
            submitted_at: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
            submitted_time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
        };
        scholarshipRequests.unshift(newApplication);
        localStorage.setItem('scholarship_requests', JSON.stringify(scholarshipRequests));

        setTimeout(function () {
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                const label = submitBtn.querySelector('.sch-app-btn-label');
                const spinner = submitBtn.querySelector('.sch-app-btn-spinner');
                if (label) label.textContent = 'Submit Application';
                if (spinner) spinner.hidden = true;
            }
            if (successModal) {
                successModal.hidden = false;
                document.body.style.overflow = 'hidden';
            }
        }, 1600);
    });

    window.scholarshipApp = { goToSection: goToSection };
})();
