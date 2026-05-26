/**
 * Scholarship Application — section navigation, profile photo, form submit (frontend only)
 */
(function () {
    'use strict';

    const SECTION_ORDER = ['personal', 'educational', 'background', 'additional', 'requirements'];

    const navItems = document.querySelectorAll('.sk-side__link');
    const panels = document.querySelectorAll('.sch-app-panel');
    const form = document.getElementById('scholarshipApplicationForm');
    const birthdateInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');

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
