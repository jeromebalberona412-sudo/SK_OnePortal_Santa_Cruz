/**
 * Kabataan Sports Registration — frontend only (sports-registration.js)
 */
(function () {
    'use strict';

    const MAX_BYTES = 5 * 1024 * 1024;
    const PDF_MIME = 'application/pdf';
    const IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];

    const form = document.getElementById('sportsRegistrationForm');
    if (!form) return;

    const alertEl = document.getElementById('srFormAlert');
    const submitBtn = document.getElementById('srSubmitBtn');
    const submitHint = document.getElementById('srSubmitHint');
    const agreeTerms = document.getElementById('agreeTerms');
    const successModal = document.getElementById('srSuccessModal');
    const termsModal = document.getElementById('srTermsModal');
    const otherSportWrap = document.getElementById('otherSportWrap');
    const otherSportInput = document.getElementById('otherSport');
    const selectedSportDisplay = document.getElementById('selectedSportDisplay');
    const birthdateInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');
    const progressFill = document.getElementById('srProgressFill');
    const progressPct = document.getElementById('srProgressPct');
    const progressSteps = document.querySelectorAll('#srProgressSteps li');
    const sections = form.querySelectorAll('.sr-section');

    let isSubmitting = false;

    function showAlert(msg) {
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.hidden = false;
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        if (alertEl) alertEl.hidden = true;
    }

    function isValidEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v || '').trim());
    }

    function isValidPhone(v) {
        return /^09\d{9}$/.test(String(v || '').replace(/\s/g, ''));
    }

    function getSelectedSport() {
        const checked = form.querySelector('input[name="sportChoice"]:checked');
        if (!checked) return '';
        if (checked.value === 'Other') {
            return (otherSportInput?.value || '').trim() || 'Other';
        }
        return checked.value;
    }

    function updateSportUI() {
        const checked = form.querySelector('input[name="sportChoice"]:checked');
        const isOther = checked?.value === 'Other';

        if (otherSportWrap) {
            otherSportWrap.hidden = !isOther;
        }
        if (otherSportInput) {
            otherSportInput.required = isOther;
            if (!isOther) otherSportInput.value = '';
        }
        if (selectedSportDisplay) {
            selectedSportDisplay.value = getSelectedSport();
        }
        updateProgress();
    }

    form.querySelectorAll('input[name="sportChoice"]').forEach(function (radio) {
        radio.addEventListener('change', updateSportUI);
    });

    if (otherSportInput) {
        otherSportInput.addEventListener('input', function () {
            if (selectedSportDisplay) selectedSportDisplay.value = getSelectedSport();
        });
    }

    if (birthdateInput && ageInput) {
        birthdateInput.addEventListener('change', function () {
            const bday = new Date(this.value);
            if (Number.isNaN(bday.getTime())) {
                ageInput.value = '';
                return;
            }
            const today = new Date();
            let age = today.getFullYear() - bday.getFullYear();
            const m = today.getMonth() - bday.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--;
            ageInput.value = age >= 0 ? age : '';
            updateProgress();
        });
    }

    function updateProgress() {
        const total = sections.length;
        let completed = 0;

        sections.forEach(function (section) {
            const step = parseInt(section.dataset.step, 10);
            let done = false;

            if (step === 1) {
                const sport = form.querySelector('input[name="sportChoice"]:checked');
                done = !!sport && (sport.value !== 'Other' || (otherSportInput?.value || '').trim());
            } else if (step === 5) {
                const uploads = section.querySelectorAll('.sr-file-input');
                done = uploads.length > 0 && Array.from(uploads).every(function (i) { return i.files?.length; });
            } else if (step === 6) {
                done = agreeTerms?.checked;
            } else {
                const fields = section.querySelectorAll('[required]');
                done = Array.from(fields).every(function (f) {
                    if (f.type === 'file') return f.files?.length;
                    return String(f.value || '').trim() !== '';
                });
            }

            if (done) completed++;

            const stepLi = document.querySelector('#srProgressSteps li[data-step="' + step + '"]');
            if (stepLi) {
                stepLi.classList.toggle('is-done', done);
                stepLi.classList.toggle('is-active', !done && completed === step - 1);
            }
        });

        const pct = Math.round((completed / total) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (progressPct) progressPct.textContent = pct + '%';
    }

    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);

    function toggleSubmit() {
        const enabled = agreeTerms?.checked && !isSubmitting;
        if (submitBtn) submitBtn.disabled = !enabled;
        if (submitHint) {
            submitHint.textContent = enabled
                ? 'Ready to submit your registration.'
                : 'Please agree to the Terms & Conditions to enable submission.';
        }
    }

    if (agreeTerms) {
        agreeTerms.addEventListener('change', function () {
            toggleSubmit();
            updateProgress();
        });
    }

    function openModal(modal) {
        if (!modal) return;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    document.getElementById('openTermsModal')?.addEventListener('click', function () {
        openModal(termsModal);
    });

    document.getElementById('acceptTermsFromModal')?.addEventListener('click', function () {
        if (agreeTerms) agreeTerms.checked = true;
        toggleSubmit();
        updateProgress();
        closeModal(termsModal);
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeModal(el.closest('.sr-modal'));
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        [termsModal, successModal].forEach(closeModal);
    });

    function validateFile(file, pdfOnly) {
        if (!file) return 'No file selected.';
        if (pdfOnly) {
            if (file.type !== PDF_MIME && !file.name.toLowerCase().endsWith('.pdf')) {
                return 'Only PDF files are allowed.';
            }
        } else {
            const ok = file.type === PDF_MIME || IMAGE_TYPES.includes(file.type) ||
                /\.(pdf|jpe?g|png)$/i.test(file.name);
            if (!ok) return 'Only PDF, JPG, or PNG files are allowed.';
        }
        if (file.size > MAX_BYTES) return 'File exceeds 5MB limit.';
        return null;
    }

    function initUpload(item) {
        const dropzone = item.querySelector('.sr-dropzone');
        const input = item.querySelector('.sr-file-input');
        const pdfOnly = item.dataset.pdfOnly === '1';
        const defaultView = dropzone?.querySelector('.sr-dropzone-default');
        const doneView = dropzone?.querySelector('.sr-dropzone-done');
        const fileNameEl = dropzone?.querySelector('.sr-file-name');
        const removeBtn = dropzone?.querySelector('.sr-file-remove');
        const errorEl = item.querySelector('.sr-field-error');

        function setError(msg) {
            if (errorEl) {
                errorEl.textContent = msg || '';
                errorEl.hidden = !msg;
            }
            dropzone?.classList.toggle('sr-has-file', !msg && !!input?.files?.length);
        }

        function showSuccess(file) {
            if (fileNameEl) fileNameEl.textContent = file.name;
            if (defaultView) defaultView.hidden = true;
            if (doneView) doneView.hidden = false;
            dropzone?.classList.add('sr-has-file');
            setError('');
            updateProgress();
        }

        function reset() {
            if (input) input.value = '';
            if (defaultView) defaultView.hidden = false;
            if (doneView) doneView.hidden = true;
            dropzone?.classList.remove('sr-has-file', 'sr-dragover');
            setError('');
            updateProgress();
        }

        input?.addEventListener('change', function () {
            const err = validateFile(this.files[0], pdfOnly);
            if (err) {
                setError(err);
                this.value = '';
                return;
            }
            showSuccess(this.files[0]);
        });

        removeBtn?.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            reset();
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            dropzone?.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.add('sr-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (ev) {
            dropzone?.addEventListener(ev, function (e) {
                e.preventDefault();
                dropzone.classList.remove('sr-dragover');
            });
        });

        dropzone?.addEventListener('drop', function (e) {
            const file = e.dataTransfer?.files?.[0];
            if (!file || !input) return;
            const err = validateFile(file, pdfOnly);
            if (err) {
                setError(err);
                return;
            }
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showSuccess(file);
        });

        dropzone?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input?.click();
            }
        });
    }

    document.querySelectorAll('.sr-upload-item').forEach(initUpload);

    function markInvalid(el) {
        el?.classList.add('sr-invalid');
    }

    function clearInvalids() {
        form.querySelectorAll('.sr-invalid').forEach(function (el) {
            el.classList.remove('sr-invalid');
        });
    }

    function validateForm() {
        clearInvalids();
        hideAlert();

        if (!form.querySelector('input[name="sportChoice"]:checked')) {
            showAlert('Please select a sport (Basketball, Volleyball, or Other).');
            document.getElementById('section-sport')?.scrollIntoView({ behavior: 'smooth' });
            return false;
        }

        if (form.querySelector('input[name="sportChoice"]:checked')?.value === 'Other' && !(otherSportInput?.value || '').trim()) {
            markInvalid(otherSportInput);
            showAlert('Please specify your sport.');
            otherSportInput?.focus();
            return false;
        }

        const required = form.querySelectorAll('[required]');
        for (let i = 0; i < required.length; i++) {
            const field = required[i];
            if (field.type === 'file') continue;
            if (field.type === 'checkbox') continue;
            if (!String(field.value || '').trim()) {
                markInvalid(field);
                showAlert('Please complete all required fields.');
                field.focus?.();
                field.closest('.sr-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return false;
            }
        }

        const email = document.getElementById('email');
        if (email?.value && !isValidEmail(email.value)) {
            markInvalid(email);
            showAlert('Please enter a valid email address.');
            email.focus();
            return false;
        }

        const phone = document.getElementById('contactNumber');
        const emergency = document.getElementById('emergencyNumber');
        if (phone?.value && !isValidPhone(phone.value)) {
            markInvalid(phone);
            showAlert('Contact number must be 11 digits starting with 09.');
            phone.focus();
            return false;
        }
        if (emergency?.value && !isValidPhone(emergency.value)) {
            markInvalid(emergency);
            showAlert('Emergency contact number must be 11 digits starting with 09.');
            emergency.focus();
            return false;
        }

        const fileInputs = form.querySelectorAll('.sr-file-input[required]');
        for (let j = 0; j < fileInputs.length; j++) {
            const inp = fileInputs[j];
            if (!inp.files?.length) {
                const item = inp.closest('.sr-upload-item');
                const err = item?.querySelector('.sr-field-error');
                if (err) {
                    err.textContent = 'This document is required.';
                    err.hidden = false;
                }
                showAlert('Please upload all required documents.');
                inp.closest('.sr-section')?.scrollIntoView({ behavior: 'smooth' });
                return false;
            }
            const pdfOnly = inp.closest('.sr-upload-item')?.dataset.pdfOnly === '1';
            const fileErr = validateFile(inp.files[0], pdfOnly);
            if (fileErr) {
                showAlert(fileErr);
                return false;
            }
        }

        if (!agreeTerms?.checked) {
            showAlert('You must agree to the Terms & Conditions before submitting.');
            document.getElementById('section-terms')?.scrollIntoView({ behavior: 'smooth' });
            return false;
        }

        return true;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (isSubmitting) return;
        if (!validateForm()) return;

        isSubmitting = true;
        toggleSubmit();
        if (submitBtn) {
            submitBtn.classList.add('is-loading');
            const label = submitBtn.querySelector('.sr-submit-label');
            const spinner = submitBtn.querySelector('.sr-submit-spinner');
            if (label) label.textContent = 'Submitting...';
            if (spinner) spinner.hidden = false;
        }

        setTimeout(function () {
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.classList.remove('is-loading');
                const label = submitBtn.querySelector('.sr-submit-label');
                const spinner = submitBtn.querySelector('.sr-submit-spinner');
                if (label) label.textContent = 'Submit Registration';
                if (spinner) spinner.hidden = true;
            }
            toggleSubmit();
            openModal(successModal);
        }, 2000);
    });

    document.getElementById('srResetForm')?.addEventListener('click', function () {
        closeModal(successModal);
        form.reset();
        document.querySelectorAll('.sr-upload-item').forEach(function (item) {
            const input = item.querySelector('.sr-file-input');
            if (input) {
                input.value = '';
                const dropzone = item.querySelector('.sr-dropzone');
                dropzone?.querySelector('.sr-dropzone-default')?.removeAttribute('hidden');
                const done = dropzone?.querySelector('.sr-dropzone-done');
                if (done) done.hidden = true;
                dropzone?.classList.remove('sr-has-file');
            }
        });
        if (otherSportWrap) otherSportWrap.hidden = true;
        toggleSubmit();
        updateProgress();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            const step = entry.target.dataset.step;
            progressSteps.forEach(function (li) {
                li.classList.toggle('is-active', li.dataset.step === step);
            });
        });
    }, { rootMargin: '-30% 0px -55% 0px', threshold: 0 });

    sections.forEach(function (s) { observer.observe(s); });

    toggleSubmit();
    updateSportUI();
    updateProgress();
})();
