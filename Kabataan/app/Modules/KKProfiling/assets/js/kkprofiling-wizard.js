/**
 * KK Profiling 4-step registration wizard
 * Step 1: existing form (unchanged) — draft only
 * Step 4: legacy verify-email / set-password cards (pre-wizard layout)
 */

(function () {
    const root = document.getElementById('kkpRegistrationWizard');
    if (!root) {
        return;
    }

    const slug = root.dataset.barangaySlug || '';
    const initialStep = parseInt(root.dataset.initialStep || '1', 10);
    const emailVerifiedOnLoad = root.dataset.emailVerified === '1';
    const verificationSentOnLoad = root.dataset.verificationSent === '1';

    const apiBase = `/api/kkprofiling/${slug}/wizard`;

    const panels = {
        1: document.getElementById('kkpWizardStep1'),
        2: document.getElementById('kkpWizardStep2'),
        3: document.getElementById('kkpWizardStep3'),
        4: document.getElementById('kkpWizardStep4'),
    };

    const progressItems = document.querySelectorAll('#kkpWizardSteps .kkp-wizard-step-item');
    const backBtn = document.getElementById('kkpWizardBackBtn');
    const nextBtn = document.getElementById('kkpWizardNextBtn');
    const form = document.getElementById('kkProfilingForm');
    const navBar = document.getElementById('kkpWizardNav');
    const emailVerifyCard = document.getElementById('emailVerifyCard');
    const displayEmail = document.getElementById('displayEmail');
    const setPasswordCard = document.getElementById('setPasswordCard');
    const regSuccessCard = document.getElementById('regSuccessCard');

    let currentStep = Math.min(Math.max(initialStep, 1), 4);
    let emailVerified = emailVerifiedOnLoad;
    let verificationSent = verificationSentOnLoad;

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function showLoading(message) {
        if (window.showLoading) {
            window.showLoading(message || 'Please wait...');
        }
    }

    function hideLoading() {
        if (window.hideLoading) {
            window.hideLoading();
        }
    }

    function getDraftEmail() {
        const fromForm = form?.querySelector('input[name="email"]')?.value?.trim();
        const fromDisplay = displayEmail?.textContent?.trim();

        if (fromForm) {
            return fromForm;
        }

        if (fromDisplay && fromDisplay !== 'your-email@example.com') {
            return fromDisplay;
        }

        return '';
    }

    function showLegacyVerifyCard() {
        if (emailVerifyCard) {
            emailVerifyCard.style.display = 'block';
        }
        if (setPasswordCard) {
            setPasswordCard.style.display = 'none';
        }
        if (regSuccessCard) {
            regSuccessCard.style.display = 'none';
        }
    }

    function showLegacySetPasswordCard() {
        emailVerified = true;

        if (emailVerifyCard) {
            emailVerifyCard.style.display = 'none';
        }
        if (setPasswordCard) {
            setPasswordCard.style.display = 'block';
        }
        if (regSuccessCard) {
            regSuccessCard.style.display = 'none';
        }

        updateNavButtons(4);
    }

    function showLegacySuccessCard() {
        if (emailVerifyCard) {
            emailVerifyCard.style.display = 'none';
        }
        if (setPasswordCard) {
            setPasswordCard.style.display = 'none';
        }
        if (regSuccessCard) {
            regSuccessCard.style.display = 'block';
        }

        progressItems.forEach((item) => {
            item.classList.add('is-complete');
            item.classList.remove('is-active');
        });
    }

    async function prepareStep4(options = {}) {
        const skipAutoSend = options.skipAutoSend === true;
        const email = getDraftEmail();

        if (displayEmail && email) {
            displayEmail.textContent = email;
        }

        if (emailVerified) {
            showLegacySetPasswordCard();
            return;
        }

        showLegacyVerifyCard();

        if (!verificationSent && !skipAutoSend) {
            const sent = await sendVerificationEmail(false);
            if (sent) {
                verificationSent = true;
                root.dataset.verificationSent = '1';
            }
        } else if (verificationSent && window.startResendTimer) {
            window.startResendTimer();
        }
    }

    function updateNavButtons(step) {
        const showWizardNav = step !== 4 || !emailVerified;
        const canGoBack = step > 1 && (step !== 4 || !emailVerified);

        if (navBar) {
            navBar.hidden = !showWizardNav;
        }

        if (backBtn) {
            backBtn.hidden = !canGoBack;
            backBtn.disabled = !canGoBack;
        }

        if (nextBtn) {
            nextBtn.hidden = step === 4;
            nextBtn.textContent = 'Next';
        }
    }

    async function setStep(step, options = {}) {
        currentStep = step;

        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) {
                return;
            }
            panel.hidden = parseInt(key, 10) !== step;
        });

        progressItems.forEach((item) => {
            const itemStep = parseInt(item.dataset.step, 10);
            item.classList.toggle('is-active', itemStep === step);
            item.classList.toggle('is-complete', itemStep < step);
        });

        document.body.classList.toggle('kkp-wizard-step4-active', step === 4);

        updateNavButtons(step);

        if (step === 4) {
            await prepareStep4(options);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function applyServerErrors(errors) {
        if (!errors || typeof errors !== 'object') {
            return;
        }

        Object.entries(errors).forEach(([field, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;
            const input = form?.querySelector(`[name="${field}"]`);

            if (input) {
                input.classList.add('kkp-input-err');
                const err = document.createElement('span');
                err.className = 'kkp-field-error';
                err.textContent = message;
                input.parentNode?.insertBefore(err, input.nextSibling);
            }
        });
    }

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message || 'Request failed.');
            error.errors = data.errors || {};
            throw error;
        }

        return data;
    }

    async function postFormData(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message || 'Request failed.');
            error.errors = data.errors || {};
            throw error;
        }

        return data;
    }

    async function saveStep1() {
        if (!form) {
            return false;
        }

        document.querySelectorAll('.kkp-field-error').forEach((el) => el.remove());
        document.querySelectorAll('.kkp-input-err').forEach((el) => el.classList.remove('kkp-input-err'));

        if (typeof window.validateKkProfilingForm !== 'function') {
            return false;
        }

        const valid = await window.validateKkProfilingForm({
            skipFacialVerification: true,
            skipEmailExistenceCheck: true,
        });

        if (!valid) {
            return false;
        }

        showLoading('Saving your profile...');

        try {
            const formData = new FormData(form);
            formData.append('respondent_number', root.dataset.respondentNumber || '');

            await postFormData(`${apiBase}/step-1`, formData);
            await setStep(2);
            return true;
        } catch (error) {
            applyServerErrors(error.errors);
            if (!error.errors || Object.keys(error.errors).length === 0) {
                alert(error.message);
            }
            return false;
        } finally {
            hideLoading();
        }
    }

    async function saveStep2() {
        const completed = document.getElementById('kkpFacialVerificationCompleted');
        const selfie = document.getElementById('kkpVerifiedSelfie');

        if (completed?.value !== '1' || !selfie?.value?.trim()) {
            const section = document.getElementById('kkpIdentitySection');
            let err = section?.querySelector('.kkp-field-error');

            if (section && !err) {
                err = document.createElement('span');
                err.className = 'kkp-field-error';
                err.textContent = 'Please complete facial identity verification before continuing.';
                section.appendChild(err);
            }

            section?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        showLoading('Saving verification...');

        try {
            await postJson(`${apiBase}/step-2`, {
                facial_verification_completed: '1',
                verified_selfie: selfie.value,
            });
            await setStep(3);
            return true;
        } catch (error) {
            alert(error.message);
            return false;
        } finally {
            hideLoading();
        }
    }

    async function saveStep3() {
        showLoading('Saving documents...');

        let saved = false;

        try {
            const formData = new FormData();
            const schoolId = document.getElementById('kkpSchoolId');
            const clearance = document.getElementById('kkpBarangayClearance');

            if (schoolId?.files?.[0]) {
                formData.append('school_id', schoolId.files[0]);
            }

            if (clearance?.files?.[0]) {
                formData.append('barangay_clearance', clearance.files[0]);
            }

            await postFormData(`${apiBase}/step-3`, formData);
            verificationSent = false;
            root.dataset.verificationSent = '0';
            saved = true;
        } catch (error) {
            alert(error.message);
        } finally {
            hideLoading();
        }

        if (saved) {
            await setStep(4);
        }

        return saved;
    }

    async function sendVerificationEmail(isResend) {
        showLoading(isResend ? 'Resending verification email...' : 'Sending verification email...');

        const emailErrorEl = document.getElementById('kkpWizardEmailError');

        if (emailErrorEl) {
            emailErrorEl.hidden = true;
            emailErrorEl.textContent = '';
        }

        try {
            const endpoint = isResend ? `${apiBase}/resend-verification` : `${apiBase}/send-verification`;
            const data = await postJson(endpoint, {});

            if (displayEmail && data.email) {
                displayEmail.textContent = data.email;
            }

            verificationSent = true;
            root.dataset.verificationSent = '1';

            if (window.startResendTimer) {
                window.startResendTimer();
            }

            return true;
        } catch (error) {
            const emailMsg = error.errors?.email?.[0] || error.message || 'Failed to send verification email.';

            if (emailErrorEl) {
                emailErrorEl.textContent = emailMsg;
                emailErrorEl.hidden = false;
            } else {
                alert(emailMsg);
            }

            const resendBtn = document.getElementById('resendEmailBtn');
            const timer = document.getElementById('resendTimer');

            if (resendBtn) {
                resendBtn.disabled = true;
            }

            if (timer) {
                timer.style.display = 'none';
                timer.textContent = '';
            }

            if (isResend) {
                verificationSent = false;
                root.dataset.verificationSent = '0';
            }

            return false;
        } finally {
            hideLoading();
        }
    }

    window.kkpWizardSendVerification = sendVerificationEmail;
    window.kkpWizardShowSetPassword = showLegacySetPasswordCard;
    window.kkpWizardShowSuccess = showLegacySuccessCard;

    async function handleNext() {
        if (currentStep === 1) {
            await saveStep1();
            return;
        }

        if (currentStep === 2) {
            await saveStep2();
            return;
        }

        if (currentStep === 3) {
            await saveStep3();
        }
    }

    async function handleBack() {
        if (currentStep <= 1) {
            return;
        }

        if (currentStep === 4 && emailVerified) {
            return;
        }

        if (currentStep === 4) {
            verificationSent = false;
            root.dataset.verificationSent = '0';

            const emailErrorEl = document.getElementById('kkpWizardEmailError');
            if (emailErrorEl) {
                emailErrorEl.hidden = true;
                emailErrorEl.textContent = '';
            }
            delete root.dataset.emailError;
        }

        await setStep(currentStep - 1);
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', handleNext);
    }

    if (backBtn) {
        backBtn.addEventListener('click', handleBack);
    }

    async function restoreDraftState() {
        try {
            const response = await fetch(`${apiBase}/status`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            const draft = data.draft;

            if (!draft) {
                return;
            }

            if (draft.email && displayEmail) {
                displayEmail.textContent = draft.email;
            }

            if (draft.email_verified) {
                emailVerified = true;
                verificationSent = true;
            }

            if (draft.verification_sent) {
                verificationSent = true;
            }

            currentStep = Math.max(currentStep, parseInt(draft.current_step, 10) || 1);
        } catch (error) {
            // Non-blocking
        }
    }

    async function initWizard() {
        await restoreDraftState();

        if (emailVerifiedOnLoad) {
            emailVerified = true;
            verificationSent = true;
        }

        if (verificationSentOnLoad) {
            verificationSent = true;
        }

        const serverEmailError = root.dataset.emailError;

        if (serverEmailError) {
            currentStep = Math.max(currentStep, 4);
        }

        if (currentStep === 4) {
            await setStep(4, { skipAutoSend: Boolean(serverEmailError || verificationSent) });

            if (serverEmailError) {
                const emailErrorEl = document.getElementById('kkpWizardEmailError');
                if (emailErrorEl) {
                    emailErrorEl.textContent = serverEmailError;
                    emailErrorEl.hidden = false;
                }
            }
        } else {
            await setStep(currentStep);
        }
    }

    initWizard();
})();
