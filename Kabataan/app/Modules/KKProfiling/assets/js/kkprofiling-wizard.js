/**
 * KK Profiling 3-step registration wizard
 */

(function () {
    const root = document.getElementById('kkpRegistrationWizard');
    if (!root) {
        return;
    }

    const slug = root.dataset.barangaySlug || '';
    const barangayName = root.dataset.barangayName || '';
    const initialStep = parseInt(root.dataset.initialStep || '1', 10);
    const verificationSentOnLoad = root.dataset.verificationSent === '1';

    const apiBase = `/api/kkprofiling/${slug}/wizard`;

    const STEP_META = {
        1: {
            title: 'Profiling Form',
            desc: 'Complete your personal and demographic information.',
        },
        2: {
            title: 'Supporting Documents',
            desc: 'Optionally upload a School ID or Barangay Clearance to support your registration.',
        },
        3: {
            title: 'Check Your Email',
            desc: 'We sent a secure link to set your account password. Open your email to continue.',
        },
    };

    const panels = {
        1: document.getElementById('kkpWizardStep1'),
        2: document.getElementById('kkpWizardStep2'),
        3: document.getElementById('kkpWizardStep3'),
    };

    const progressItems = document.querySelectorAll('#kkpWizardSteps .kkp-wizard-step-item');
    const progressConnectors = document.querySelectorAll('#kkpWizardSteps .kkp-wizard-step-connector');
    const stepTitleEl = document.getElementById('kkpWizardStepTitle');
    const stepDescEl = document.getElementById('kkpWizardStepDesc');
    const barangayNameEl = document.getElementById('kkpWizardBarangayName');
    const backBtn = document.getElementById('kkpWizardBackBtn');
    const nextBtn = document.getElementById('kkpWizardNextBtn');
    const nextLabelEl = document.getElementById('kkpWizardNextLabel');
    const form = document.getElementById('kkProfilingForm');
    const navBar = document.getElementById('kkpWizardNav');
    const emailVerifyCard = document.getElementById('emailVerifyCard');
    const displayEmail = document.getElementById('displayEmail');

    const DOC_MAX_BYTES = 10 * 1024 * 1024;
    const DOC_ALLOWED_TYPES = ['image/jpeg', 'image/png'];
    const DOC_ALLOWED_EXT = ['.jpg', '.jpeg', '.png'];

    const docTypeRadios = document.querySelectorAll('input[name="document_type"]');
    const schoolIdUploadPanel = document.getElementById('kkpSchoolIdUpload');
    const clearanceUploadPanel = document.getElementById('kkpBarangayClearanceUpload');
    const schoolIdInput = document.getElementById('kkpSchoolId');
    const clearanceInput = document.getElementById('kkpBarangayClearance');

    const previewConfig = {
        kkpSchoolId: {
            empty: document.getElementById('kkpSchoolIdEmpty'),
            preview: document.getElementById('kkpSchoolIdPreview'),
            img: document.getElementById('kkpSchoolIdPreviewImg'),
            fileName: document.getElementById('kkpSchoolIdFileName'),
            dropzone: document.getElementById('kkpSchoolIdDropzone'),
        },
        kkpBarangayClearance: {
            empty: document.getElementById('kkpBarangayClearanceEmpty'),
            preview: document.getElementById('kkpBarangayClearancePreview'),
            img: document.getElementById('kkpBarangayClearancePreviewImg'),
            fileName: document.getElementById('kkpBarangayClearanceFileName'),
            dropzone: document.getElementById('kkpBarangayClearanceDropzone'),
        },
    };

    const previewUrls = {};

    let currentStep = Math.min(Math.max(initialStep, 1), 3);
    let verificationSent = verificationSentOnLoad;
    let registrationCompleted = root.dataset.registrationComplete === '1';
    let registrationCompletionPoll = null;

    if (barangayNameEl && barangayName) {
        barangayNameEl.textContent = barangayName;
    }

    function hideDocUploadError() {
        // Document type switches clear files automatically — no inline error UI.
    }

    function showDocUploadError(message) {
        if (message) {
            alert(message);
        }
    }

    function isAllowedDocumentFile(file) {
        if (!file) {
            return false;
        }

        const name = file.name.toLowerCase();
        const hasAllowedExt = DOC_ALLOWED_EXT.some((ext) => name.endsWith(ext));

        return DOC_ALLOWED_TYPES.includes(file.type) || hasAllowedExt;
    }

    function validateDocumentFile(file) {
        if (!file) {
            return null;
        }

        if (!isAllowedDocumentFile(file)) {
            return 'Only JPG or PNG images are allowed.';
        }

        if (file.size > DOC_MAX_BYTES) {
            return 'Image must be 10MB or smaller.';
        }

        return null;
    }

    function getSelectedDocumentType() {
        return document.querySelector('input[name="document_type"]:checked')?.value || '';
    }

    function getActiveDocumentFile() {
        const documentType = getSelectedDocumentType();

        if (documentType === 'school_id') {
            return schoolIdInput?.files?.[0] || null;
        }

        if (documentType === 'barangay_clearance') {
            return clearanceInput?.files?.[0] || null;
        }

        return null;
    }

    function clearDocumentInput(input) {
        if (!input) {
            return;
        }

        resetFilePreview(input.id);
        input.value = '';
    }

    function resetFilePreview(inputId) {
        const config = previewConfig[inputId];

        if (!config) {
            return;
        }

        if (previewUrls[inputId]) {
            URL.revokeObjectURL(previewUrls[inputId]);
            delete previewUrls[inputId];
        }

        if (config.dropzone) {
            config.dropzone.hidden = false;
        }

        if (config.preview) {
            config.preview.hidden = true;
        }

        if (config.img) {
            config.img.removeAttribute('src');
        }

        if (config.fileName) {
            config.fileName.textContent = '';
        }
    }

    function updateFilePreview(input) {
        if (!input) {
            return;
        }

        const config = previewConfig[input.id];
        const file = input.files?.[0];

        if (!config) {
            return;
        }

        if (!file) {
            resetFilePreview(input.id);
            updateNavButtons(currentStep);
            return;
        }

        const error = validateDocumentFile(file);

        if (error) {
            input.value = '';
            resetFilePreview(input.id);
            showDocUploadError(error);
            updateNavButtons(currentStep);
            return;
        }

        hideDocUploadError();
        resetFilePreview(input.id);

        const objectUrl = URL.createObjectURL(file);
        previewUrls[input.id] = objectUrl;

        if (config.img) {
            config.img.src = objectUrl;
        }

        if (config.fileName) {
            config.fileName.textContent = file.name;
        }

        if (config.dropzone) {
            config.dropzone.hidden = true;
        }

        if (config.preview) {
            config.preview.hidden = false;
        }

        updateNavButtons(currentStep);
    }

    function resetAllDocumentPreviews() {
        Object.keys(previewConfig).forEach((inputId) => resetFilePreview(inputId));
    }

    function syncDocumentUploadPanels() {
        const selectedType = getSelectedDocumentType();
        const previousType = syncDocumentUploadPanels.lastType || '';

        hideDocUploadError();

        if (previousType && previousType !== selectedType) {
            if (previousType === 'school_id') {
                clearDocumentInput(schoolIdInput);
            } else if (previousType === 'barangay_clearance') {
                clearDocumentInput(clearanceInput);
            }
        }

        syncDocumentUploadPanels.lastType = selectedType;

        if (schoolIdUploadPanel) {
            schoolIdUploadPanel.hidden = selectedType !== 'school_id';
        }

        if (clearanceUploadPanel) {
            clearanceUploadPanel.hidden = selectedType !== 'barangay_clearance';
        }

        if (!selectedType) {
            clearDocumentInput(schoolIdInput);
            clearDocumentInput(clearanceInput);
        } else if (selectedType === 'school_id') {
            clearDocumentInput(clearanceInput);
        } else if (selectedType === 'barangay_clearance') {
            clearDocumentInput(schoolIdInput);
        }

        updateNavButtons(currentStep);
    }
    syncDocumentUploadPanels.lastType = '';

    function bindDropzone(dropzone, input) {
        if (!dropzone || !input) {
            return;
        }

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', (event) => {
            const file = event.dataTransfer?.files?.[0];

            if (!file) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            updateFilePreview(input);
        });
    }

    function bindDocumentTypeControls() {
        docTypeRadios.forEach((radio) => {
            radio.addEventListener('change', syncDocumentUploadPanels);
        });

        [schoolIdInput, clearanceInput].forEach((input) => {
            input?.addEventListener('change', () => updateFilePreview(input));
        });

        bindDropzone(previewConfig.kkpSchoolId.dropzone, schoolIdInput);
        bindDropzone(previewConfig.kkpBarangayClearance.dropzone, clearanceInput);

        document.querySelectorAll('.kkp-wizard-dropzone-remove').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const inputId = button.dataset.clearInput;
                const input = inputId ? document.getElementById(inputId) : null;

                if (input) {
                    clearDocumentInput(input);
                    hideDocUploadError();
                    updateNavButtons(currentStep);
                }
            });
        });

        syncDocumentUploadPanels();
    }

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

    function stopRegistrationCompletionPoll() {
        if (registrationCompletionPoll) {
            clearInterval(registrationCompletionPoll);
            registrationCompletionPoll = null;
        }
    }

    function showRegistrationCompleteState() {
        if (registrationCompleted) {
            return;
        }

        registrationCompleted = true;
        stopRegistrationCompletionPoll();

        if (window.kkpStopResendTimer) {
            window.kkpStopResendTimer();
        }

        root.classList.add('kkp-wizard-registration-complete');
        document.body.classList.add('kkp-wizard-registration-complete');

        const resendBtn = document.getElementById('resendEmailBtn');
        const verifyHelp = document.querySelector('#kkpWizardStep3 .verify-help');
        const emailErrorEl = document.getElementById('kkpWizardEmailError');

        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.hidden = true;
        }

        if (verifyHelp) {
            verifyHelp.hidden = true;
        }

        if (emailErrorEl) {
            emailErrorEl.hidden = true;
            emailErrorEl.textContent = '';
        }

        if (navBar) {
            navBar.hidden = true;
        }

        const topBack = document.getElementById('kkpTopBackLink');
        if (topBack) {
            topBack.hidden = true;
        }

        const modal = document.getElementById('kkpRegSuccessModal');
        if (modal) {
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('kkp-wizard-success-modal-open');
        }
    }

    window.kkpShowRegistrationComplete = showRegistrationCompleteState;

    async function pollRegistrationCompletion() {
        if (registrationCompleted) {
            return;
        }

        const email = getDraftEmail() || root.dataset.completedEmail || '';

        if (!email || email === 'your-email@example.com') {
            return;
        }

        try {
            const response = await fetch(
                `${apiBase}/registration-complete?email=${encodeURIComponent(email)}`,
                { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
            );
            const data = await response.json();

            if (data.completed) {
                showRegistrationCompleteState();
            }
        } catch (error) {
            // Non-blocking
        }
    }

    function startRegistrationCompletionPoll() {
        if (registrationCompleted) {
            return;
        }

        stopRegistrationCompletionPoll();
        pollRegistrationCompletion();
        registrationCompletionPoll = setInterval(pollRegistrationCompletion, 4000);
    }

    function updateStepMeta(step) {
        const meta = STEP_META[step];

        if (!meta) {
            return;
        }

        if (stepTitleEl) {
            stepTitleEl.textContent = meta.title;
        }

        if (stepDescEl) {
            stepDescEl.textContent = meta.desc;
        }
    }

    const KKP_CHECKBOX_FIELDS = [
        { name: 'sex', chk: 'sexChk', hiddenId: 'kkpSex' },
        { name: 'civil_status', chk: 'civil_statusChk', hiddenId: 'kkpCivilStatus' },
        { name: 'youth_age_group', chk: 'youth_age_groupChk', hiddenId: 'kkpYouthAgeGroup' },
        { name: 'education', chk: 'educationChk', hiddenId: 'kkpEducation' },
        { name: 'youth_classification', chk: 'youth_classificationChk', hiddenId: 'kkpYouthClass' },
        { name: 'work_status', chk: 'work_statusChk', hiddenId: 'kkpWorkStatus' },
        { name: 'sk_voter', chk: 'sk_voterChk', hiddenId: 'kkpSkVoter' },
        { name: 'national_voter', chk: 'national_voterChk', hiddenId: 'kkpNationalVoter' },
        { name: 'kk_assembly', chk: 'kk_assemblyChk', hiddenId: 'kkpKkAssembly' },
        { name: 'sk_voted', chk: 'sk_votedChk', hiddenId: 'kkpSkVoted' },
        { name: 'group_chat', chk: 'group_chatChk', hiddenId: 'kkpGroupChat' },
    ];

    function setCheckboxGroupValue(chkName, hiddenId, value) {
        if (!value || !form) return;

        const hidden = document.getElementById(hiddenId);
        if (hidden) {
            hidden.value = value;
        }

        form.querySelectorAll(`input[name="${chkName}"]`).forEach((input) => {
            input.checked = input.value === value;
        });

        const matched = form.querySelector(`input[name="${chkName}"][value="${value}"]`);
        if (matched) {
            matched.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function populateWizardForm(step1, respondentNumber) {
        if (!form || !step1 || typeof step1 !== 'object') {
            return;
        }

        Object.entries(step1).forEach(([key, value]) => {
            if (value === null || value === undefined || value === '') {
                return;
            }

            const direct = form.querySelector(`[name="${key}"]`);
            if (direct && direct.type !== 'hidden' && direct.type !== 'checkbox' && direct.type !== 'radio') {
                direct.value = value;
                direct.dispatchEvent(new Event('input', { bubbles: true }));
                direct.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        KKP_CHECKBOX_FIELDS.forEach(({ name, chk, hiddenId }) => {
            if (step1[name]) {
                setCheckboxGroupValue(chk, hiddenId, step1[name]);
            }
        });

        if (step1.suffix) {
            const suffixSelect = document.getElementById('kkpSuffix');
            if (suffixSelect) {
                suffixSelect.value = step1.suffix;
                suffixSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        if (step1.custom_suffix) {
            const customSuffix = document.getElementById('kkpCustomSuffix');
            if (customSuffix) {
                customSuffix.value = step1.custom_suffix;
            }
        }

        if (step1.kk_times) {
            const kkTimes = document.getElementById('kkpKkTimes');
            if (kkTimes) {
                kkTimes.value = step1.kk_times;
            }
        }

        if (step1.kk_reason) {
            const kkReason = document.getElementById('kkpKkReason');
            if (kkReason) {
                kkReason.value = step1.kk_reason;
            }
        }

        if (step1.signature) {
            const sigInput = document.getElementById('kkpSignatureData');
            if (sigInput) {
                sigInput.value = step1.signature;
            }
            if (typeof window.kkpRestoreSignaturePreview === 'function') {
                window.kkpRestoreSignaturePreview(step1.signature);
            }
        }

        if (step1.data_agreement) {
            const agreement = form.querySelector('[name="data_agreement"]');
            if (agreement) {
                agreement.checked = true;
            }
        }

        if (respondentNumber) {
            const respondentInput = form.querySelector('[name="respondent_number"]');
            if (respondentInput) {
                respondentInput.value = respondentNumber;
            }
            root.dataset.respondentNumber = respondentNumber;
        }

        if (typeof window.kkpRefreshSignatureName === 'function') {
            window.kkpRefreshSignatureName();
        }
    }

    function restoreStep2Documents(step2) {
        if (!step2 || !step2.document_type) {
            return;
        }

        const documentType = step2.document_type;
        const typeRadio = document.querySelector(`input[name="document_type"][value="${documentType}"]`);

        if (typeRadio) {
            typeRadio.checked = true;
            typeRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }

        const inputId = documentType === 'school_id' ? 'kkpSchoolId' : 'kkpBarangayClearance';
        const config = previewConfig[inputId];

        if (!config) {
            return;
        }

        const previewUrl = `${apiBase}/document/${documentType}`;

        if (config.img) {
            config.img.src = previewUrl;
        }

        if (config.fileName && step2.original_name) {
            config.fileName.textContent = step2.original_name;
        }

        if (config.preview) {
            config.preview.hidden = false;
        }

        if (config.empty) {
            config.empty.hidden = true;
        }

        if (config.dropzone) {
            config.dropzone.hidden = true;
        }

        updateNavButtons(currentStep);
    }

    function showVerifyCard() {
        if (emailVerifyCard) {
            emailVerifyCard.hidden = false;
            emailVerifyCard.style.display = 'block';
        }
    }

    async function prepareStep3(options = {}) {
        const skipAutoSend = options.skipAutoSend === true;
        const email = getDraftEmail();

        if (displayEmail && email) {
            displayEmail.textContent = email;
        }

        showVerifyCard();

        if (!verificationSent && !skipAutoSend) {
            const sent = await sendVerificationEmail(false);
            if (sent) {
                verificationSent = true;
                root.dataset.verificationSent = '1';
            }
        } else if (verificationSent) {
            if (window.restoreResendTimer) {
                window.restoreResendTimer();
            } else if (window.startResendTimer) {
                window.startResendTimer();
            }
        }

        if (!registrationCompleted) {
            startRegistrationCompletionPoll();
        }
    }

    function updateNavButtons(step) {
        const canGoBack = step >= 2;
        const hasSelectedFile = Boolean(getActiveDocumentFile());

        if (navBar) {
            navBar.hidden = false;
            navBar.classList.toggle('kkp-wizard-nav--step1', step === 1);
            navBar.classList.toggle('kkp-wizard-nav--step3', step === 3);
        }

        if (backBtn) {
            backBtn.hidden = !canGoBack;
            backBtn.disabled = !canGoBack;
            backBtn.style.display = canGoBack ? '' : 'none';
        }

        if (nextBtn) {
            nextBtn.hidden = step === 3;
        }

        if (nextLabelEl) {
            if (step === 1) {
                nextLabelEl.textContent = 'Save & Continue';
            } else if (step === 2) {
                nextLabelEl.textContent = hasSelectedFile ? 'Upload & Continue' : 'Continue';
            } else {
                nextLabelEl.textContent = 'Continue';
            }
        }
    }

    async function setStep(step, options = {}) {
        currentStep = step;

        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) {
                return;
            }

            const isActive = parseInt(key, 10) === step;
            panel.hidden = !isActive;

            if (isActive) {
                panel.style.animation = 'none';
                panel.offsetHeight;
                panel.style.animation = '';
            }
        });

        progressItems.forEach((item) => {
            const itemStep = parseInt(item.dataset.step, 10);
            item.classList.toggle('is-active', itemStep === step);
            item.classList.toggle('is-complete', itemStep < step);
        });

        progressConnectors.forEach((connector) => {
            const afterStep = parseInt(connector.dataset.afterStep, 10);
            connector.classList.toggle('is-complete', afterStep < step);
        });

        document.body.classList.toggle('kkp-wizard-step3-active', step === 3);

        updateStepMeta(step);
        updateNavButtons(step);

        if (step === 3) {
            await prepareStep3(options);
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
        hideDocUploadError();

        const documentType = getSelectedDocumentType();
        const schoolFile = schoolIdInput?.files?.[0] || null;
        const clearanceFile = clearanceInput?.files?.[0] || null;

        if (schoolFile && clearanceFile) {
            showDocUploadError('You can only upload one supporting document at a time.');
            return false;
        }

        if (documentType === 'school_id') {
            const error = validateDocumentFile(schoolFile);
            if (error) {
                showDocUploadError(error);
                return false;
            }
        } else if (documentType === 'barangay_clearance') {
            const error = validateDocumentFile(clearanceFile);
            if (error) {
                showDocUploadError(error);
                return false;
            }
        }

        showLoading(schoolFile || clearanceFile ? 'Uploading document...' : 'Continuing...');

        let saved = false;

        try {
            const formData = new FormData();

            if (documentType) {
                formData.append('document_type', documentType);
            }

            if (documentType === 'school_id' && schoolFile) {
                formData.append('school_id', schoolFile);
            }

            if (documentType === 'barangay_clearance' && clearanceFile) {
                formData.append('barangay_clearance', clearanceFile);
            }

            await postFormData(`${apiBase}/step-2`, formData);
            verificationSent = false;
            root.dataset.verificationSent = '0';
            saved = true;
        } catch (error) {
            const message = error.errors?.document_type?.[0]
                || error.errors?.school_id?.[0]
                || error.errors?.barangay_clearance?.[0]
                || error.message;

            showDocUploadError(message);
        } finally {
            hideLoading();
        }

        if (saved) {
            await setStep(3);
        }

        return saved;
    }

    async function sendVerificationEmail(isResend) {
        showLoading(isResend ? 'Resending set password link...' : 'Sending set password link...');

        const emailErrorEl = document.getElementById('kkpWizardEmailError');

        if (emailErrorEl) {
            emailErrorEl.hidden = true;
            emailErrorEl.textContent = '';
        }

        try {
            const endpoint = isResend ? `${apiBase}/resend-verification` : `${apiBase}/send-verification`;
            const data = await postJson(endpoint, {});

            if (data.registration_completed) {
                showRegistrationCompleteState();
                return true;
            }

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
            if (error.errors?.draft?.[0] && registrationCompleted) {
                showRegistrationCompleteState();
                return false;
            }

            const emailMsg = error.errors?.email?.[0] || error.message || 'Failed to send set password link.';

            if (emailErrorEl) {
                emailErrorEl.textContent = emailMsg;
                emailErrorEl.hidden = false;
            } else {
                alert(emailMsg);
            }

            const resendBtn = document.getElementById('resendEmailBtn');
            const timer = document.getElementById('resendTimer');

            if (resendBtn && !isResend) {
                resendBtn.disabled = false;
            }

            if (timer && !isResend) {
                timer.hidden = true;
                timer.textContent = '';
            }

            if (isResend && resendBtn && window.restoreResendTimer) {
                window.restoreResendTimer();
            }

            return false;
        } finally {
            hideLoading();
        }
    }

    window.kkpWizardSendVerification = sendVerificationEmail;

    async function handleNext() {
        if (currentStep === 1) {
            await saveStep1();
            return;
        }

        if (currentStep === 2) {
            await saveStep2();
        }
    }

    async function handleBack() {
        if (currentStep <= 1) {
            return;
        }

        const targetStep = currentStep - 1;
        await setStep(targetStep, { skipAutoSend: true });

        if (targetStep === 2) {
            try {
                const response = await fetch(`${apiBase}/status`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();

                if (data.draft?.step2) {
                    restoreStep2Documents(data.draft.step2);
                }
            } catch (error) {
                // Non-blocking
            }
        }
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

            if (data.registration_completed) {
                if (data.email && displayEmail) {
                    displayEmail.textContent = data.email;
                }

                showRegistrationCompleteState();
                return;
            }

            if (!draft) {
                return;
            }

            if (draft.email && displayEmail) {
                displayEmail.textContent = draft.email;
            }

            if (draft.verification_sent) {
                verificationSent = true;
            }

            if (draft.step1) {
                populateWizardForm(draft.step1, draft.respondent_number);
            }

            if (draft.step2) {
                restoreStep2Documents(draft.step2);
            }

            currentStep = Math.max(currentStep, parseInt(draft.current_step, 10) || 1);
        } catch (error) {
            // Non-blocking
        }
    }

    async function initWizard() {
        bindDocumentTypeControls();

        if (root.dataset.completedEmail && displayEmail) {
            displayEmail.textContent = root.dataset.completedEmail;
        }

        if (registrationCompleted) {
            await setStep(3, { skipAutoSend: true });
            showRegistrationCompleteState();
            return;
        }

        await restoreDraftState();

        if (verificationSentOnLoad) {
            verificationSent = true;
        }

        const serverEmailError = root.dataset.emailError;

        if (serverEmailError) {
            currentStep = Math.max(currentStep, 3);
        }

        if (currentStep === 3) {
            await setStep(3, { skipAutoSend: Boolean(serverEmailError || verificationSent || registrationCompleted) });

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
