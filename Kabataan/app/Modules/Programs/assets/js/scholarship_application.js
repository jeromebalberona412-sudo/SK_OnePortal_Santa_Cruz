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

    document.querySelectorAll('[data-next]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            goToSection(btn.dataset.next);
        });
    });

    document.querySelectorAll('[data-prev]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            goToSection(btn.dataset.prev);
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

    const editPhotoBtn = document.getElementById('schEditPhotoBtn');
    const photoInput = document.getElementById('schPhotoInput');
    const profileImg = document.getElementById('schProfileImg');

    if (editPhotoBtn && photoInput && profileImg) {
        editPhotoBtn.addEventListener('click', function () {
            photoInput.click();
        });
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                profileImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

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

    const sideNav = document.getElementById('skSideNav');
    const sideCollapse = document.getElementById('skSideCollapse');
    const sideMobileToggle = document.getElementById('skSideMobileToggle');

    if (sideCollapse && sideNav) {
        sideCollapse.addEventListener('click', function () {
            sideNav.classList.toggle('is-collapsed');
            const collapsed = sideNav.classList.contains('is-collapsed');
            sideCollapse.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });
    }

    if (sideMobileToggle && sideNav) {
        sideMobileToggle.addEventListener('click', function () {
            sideNav.classList.add('is-mobile-open');
        });
        sideNav.addEventListener('click', function (e) {
            if (e.target === sideNav) sideNav.classList.remove('is-mobile-open');
        });
        navItems.forEach(function (item) {
            item.addEventListener('click', function () {
                if (window.innerWidth <= 900) sideNav.classList.remove('is-mobile-open');
            });
        });
    }

    window.scholarshipApp = { goToSection: goToSection };
})();
