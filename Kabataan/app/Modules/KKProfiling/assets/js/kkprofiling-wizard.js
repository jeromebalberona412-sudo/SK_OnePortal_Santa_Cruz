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
    const verificationSentOnLoad = root.dataset.verificationSent === '1';

    const apiBase = `/api/kkprofiling/${slug}/wizard`;

    const STEP_META = {
        1: {
            title: 'Profiling Form',
            desc: 'Complete your personal and demographic information.',
        },
        2: {
            title: 'Supporting Documents',
            desc: 'Optional: upload your School ID or PhilSys / National ID now, or skip and continue to email verification.',
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
    const nationalIdUploadPanel = document.getElementById('kkpNationalIdUpload');

    const DOCUMENT_INPUT_IDS = {
        school_id: ['kkpSchoolIdFront', 'kkpSchoolIdBack'],
        national_id: ['kkpNationalIdFront', 'kkpNationalIdBack'],
    };

    function buildPreviewConfig(inputId) {
        return {
            empty: document.getElementById(`${inputId}Empty`),
            preview: document.getElementById(`${inputId}Preview`),
            img: document.getElementById(`${inputId}PreviewImg`),
            fileName: document.getElementById(`${inputId}FileName`),
            dropzone: document.getElementById(`${inputId}Dropzone`),
        };
    }

    const previewConfig = {};

    Object.values(DOCUMENT_INPUT_IDS).flat().forEach((inputId) => {
        previewConfig[inputId] = buildPreviewConfig(inputId);
    });

    function getDocumentInput(inputId) {
        return document.getElementById(inputId);
    }

    function getDocumentInputsForType(documentType) {
        return (DOCUMENT_INPUT_IDS[documentType] || []).map((inputId) => getDocumentInput(inputId));
    }

    const previewUrls = {};

    let currentStep = 1;
    let verificationSent = verificationSentOnLoad;
    let registrationCompleted = root.dataset.registrationComplete === '1';
    let registrationAutoApproved = root.dataset.autoApproved === '1';
    const initialStep = parseInt(root.dataset.initialStep, 10) || 1;
    let restoredStep = initialStep;
    let registrationCompletionPoll = null;

    if (barangayNameEl && barangayName) {
        barangayNameEl.textContent = barangayName;
    }

    function hideDocUploadError() {
        // No inline error panel on step 2
    }

    function showDocUploadError(message) {
        if (!message) {
            hideDocUploadError();
            return;
        }

        alert(message);
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

    function getActiveDocumentFiles() {
        const documentType = getSelectedDocumentType();

        if (!documentType) {
            return { front: null, back: null };
        }

        const inputs = getDocumentInputsForType(documentType);

        return {
            front: inputs[0]?.files?.[0] || null,
            back: inputs[1]?.files?.[0] || null,
        };
    }

    function hasPartialDocumentUpload() {
        const files = getActiveDocumentFiles();

        return Boolean(files.front || files.back);
    }

    function hasCompleteDocumentUpload() {
        const files = getActiveDocumentFiles();

        return Boolean(files.front && files.back);
    }

    function clearDocumentInputsForType(documentType) {
        getDocumentInputsForType(documentType).forEach((input) => clearDocumentInput(input));
    }

    function clearAllDocumentInputs() {
        Object.keys(DOCUMENT_INPUT_IDS).forEach((documentType) => clearDocumentInputsForType(documentType));
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
            clearDocumentInputsForType(previousType);
        }

        syncDocumentUploadPanels.lastType = selectedType;

        if (schoolIdUploadPanel) {
            schoolIdUploadPanel.hidden = selectedType !== 'school_id';
        }

        if (nationalIdUploadPanel) {
            nationalIdUploadPanel.hidden = selectedType !== 'national_id';
        }

        if (!selectedType) {
            clearAllDocumentInputs();
        } else if (selectedType === 'school_id') {
            clearDocumentInputsForType('national_id');
        } else if (selectedType === 'national_id') {
            clearDocumentInputsForType('school_id');
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

        Object.values(DOCUMENT_INPUT_IDS).flat().forEach((inputId) => {
            const input = getDocumentInput(inputId);
            input?.addEventListener('change', () => updateFilePreview(input));
            bindDropzone(previewConfig[inputId]?.dropzone, input);
        });

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

    function showRegistrationCompleteState(autoApproved = registrationAutoApproved) {
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
            const titleEl = document.getElementById('kkpRegSuccessTitle');
            const messageEl = document.getElementById('kkpRegSuccessMessage');
            const loginBtn = modal.querySelector('.kkp-reg-success-modal-btn');

            if (autoApproved) {
                if (titleEl) {
                    titleEl.textContent = 'Registration Verified!';
                }
                if (messageEl) {
                    messageEl.textContent = 'Your details match a previous KK profiling record for your barangay. Your account is approved — you can log in now.';
                }
            } else {
                if (titleEl) {
                    titleEl.textContent = 'Registration Submitted Successfully';
                }
                if (messageEl) {
                    messageEl.textContent = 'Your account has been created successfully. Please wait for SK Officials to review and verify your registration before you can access the system.';
                }
            }

            if (loginBtn) {
                loginBtn.textContent = autoApproved ? 'Go to Login' : 'Go to Login';
            }

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
                {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                },
            );
            const data = await response.json();

            if (data.completed) {
                registrationAutoApproved = Boolean(data.auto_approved);
                showRegistrationCompleteState(registrationAutoApproved);
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

        if (step1.age && typeof window.kkpSyncYouthAgeGroupFromAge === 'function') {
            window.kkpSyncYouthAgeGroupFromAge(step1.age);
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

        const sides = step2.sides || {};
        const inputIds = DOCUMENT_INPUT_IDS[documentType] || [];

        ['front', 'back'].forEach((side, index) => {
            const inputId = inputIds[index];
            const config = previewConfig[inputId];

            if (!config || !sides[side]) {
                return;
            }

            const previewUrl = `${apiBase}/document/${documentType}/${side}`;

            if (config.img) {
                config.img.src = previewUrl;
            }

            if (config.fileName && sides[side].original_name) {
                config.fileName.textContent = sides[side].original_name;
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
        });

        updateNavButtons(currentStep);
    }

    function showVerifyCard() {
        if (emailVerifyCard) {
            emailVerifyCard.hidden = false;
            emailVerifyCard.style.display = 'block';
        }
    }

    function showEmailStatus(message, type = 'error') {
        const emailErrorEl = document.getElementById('kkpWizardEmailError');
        if (!emailErrorEl) {
            return;
        }

        emailErrorEl.textContent = message || '';
        emailErrorEl.hidden = !message;
        emailErrorEl.classList.toggle('is-success', type === 'success');
    }

    function enableResendButton() {
        const resendBtn = document.getElementById('resendEmailBtn');
        const timer = document.getElementById('resendTimer');

        if (resendBtn) {
            resendBtn.disabled = false;
            resendBtn.hidden = false;
        }

        if (timer) {
            timer.hidden = true;
            timer.textContent = '';
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
            } else {
                enableResendButton();
            }
        } else if (verificationSent) {
            if (window.restoreResendTimer) {
                window.restoreResendTimer();
            } else if (window.startResendTimer) {
                window.startResendTimer();
            } else {
                enableResendButton();
            }
        } else {
            enableResendButton();
        }

        if (!registrationCompleted) {
            startRegistrationCompletionPoll();
        }
    }

    function updateNavButtons(step) {
        const canGoBack = step >= 2;
        const hasSelectedFiles = hasPartialDocumentUpload();

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
                nextLabelEl.textContent = hasSelectedFiles ? 'Upload & Continue' : 'Skip & Continue';
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
            credentials: 'same-origin',
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
            credentials: 'same-origin',
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

        const hasFiles = hasPartialDocumentUpload();

        if (hasFiles && !hasCompleteDocumentUpload()) {
            showDocUploadError('Please upload both front and back images of your selected ID, or remove the files to skip this step.');
            return false;
        }

        if (!hasFiles) {
            showLoading('Continuing to email verification...');

            let saved = false;

            try {
                const formData = new FormData();
                formData.append('skip_documents', '1');

                const response = await postFormData(`${apiBase}/step-2`, formData);

                if (response?.verification_sent) {
                    verificationSent = true;
                    root.dataset.verificationSent = '1';

                    if (displayEmail && response.email) {
                        displayEmail.textContent = response.email;
                    }

                    if (window.startResendTimer) {
                        window.startResendTimer();
                    }
                }

                saved = true;

                if (saved) {
                    const skipAutoSend = Boolean(response?.verification_sent);
                    await setStep(3, { skipAutoSend });

                    if (response?.email_error) {
                        showEmailStatus(response.email_error, 'error');
                        enableResendButton();
                    }
                }
            } catch (error) {
                const message = error.errors?.document_type?.[0]
                    || error.errors?.registration?.[0]
                    || error.message;

                showDocUploadError(message);
            } finally {
                hideLoading();
            }

            return saved;
        }

        const documentType = getSelectedDocumentType();
        const files = getActiveDocumentFiles();

        if (!documentType) {
            showDocUploadError('Please select School ID or PhilSys / National ID.');
            return false;
        }

        const frontError = validateDocumentFile(files.front);
        const backError = validateDocumentFile(files.back);

        if (frontError || backError) {
            showDocUploadError(frontError || backError);
            return false;
        }

        showLoading('Saving your documents...');

        let saved = false;

        try {
            const formData = new FormData();
            formData.append('document_type', documentType);

            if (documentType === 'school_id') {
                formData.append('school_id_front', files.front);
                formData.append('school_id_back', files.back);
            }

            if (documentType === 'national_id') {
                formData.append('national_id_front', files.front);
                formData.append('national_id_back', files.back);
            }

            const response = await postFormData(`${apiBase}/step-2`, formData);

            if (response?.verification_sent) {
                verificationSent = true;
                root.dataset.verificationSent = '1';

                if (displayEmail && response.email) {
                    displayEmail.textContent = response.email;
                }

                if (window.startResendTimer) {
                    window.startResendTimer();
                }
            }

            saved = true;

            if (saved) {
                const skipAutoSend = Boolean(response?.verification_sent);
                await setStep(3, { skipAutoSend });

                if (response?.email_error) {
                    showEmailStatus(response.email_error, 'error');
                    enableResendButton();
                }
            }
        } catch (error) {
            const message = error.errors?.document_type?.[0]
                || error.errors?.registration?.[0]
                || error.errors?.school_id_front?.[0]
                || error.errors?.school_id_back?.[0]
                || error.errors?.national_id_front?.[0]
                || error.errors?.national_id_back?.[0]
                || error.message;

            showDocUploadError(message);
        } finally {
            hideLoading();
        }

        return saved;
    }

    async function sendVerificationEmail(isResend) {
        showLoading(isResend ? 'Resending set password link...' : 'Sending set password link...');

        showEmailStatus('');

        try {
            const endpoint = isResend ? `${apiBase}/resend-verification` : `${apiBase}/send-verification`;
            const data = await postJson(endpoint, {});

            if (data.registration_completed) {
                showRegistrationCompleteState(Boolean(data.auto_approved));
                return true;
            }

            if (displayEmail && data.email) {
                displayEmail.textContent = data.email;
            }

            verificationSent = true;
            root.dataset.verificationSent = '1';

            showEmailStatus(
                data.message || 'Set password link sent. Please check your inbox.',
                'success',
            );

            const resendBtn = document.getElementById('resendEmailBtn');
            if (isResend && resendBtn) {
                const originalLabel = resendBtn.textContent;
                resendBtn.textContent = 'Email sent!';
                setTimeout(() => {
                    resendBtn.textContent = originalLabel || 'Resend set password link';
                }, 2500);
            }

            if (window.startResendTimer) {
                window.startResendTimer();
            }

            return true;
        } catch (error) {
            if (error.errors?.draft?.[0] && registrationCompleted) {
                showRegistrationCompleteState(registrationAutoApproved);
                return false;
            }

            const emailMsg = error.errors?.email?.[0]
                || error.errors?.draft?.[0]
                || error.message
                || 'Failed to send set password link.';

            showEmailStatus(emailMsg, 'error');
            enableResendButton();

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

        try {
            await postJson(`${apiBase}/set-step`, { step: targetStep });
        } catch (error) {
            // Non-blocking — still show the previous step locally
        }

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
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            const draft = data.draft;

            if (data.registration_completed) {
                if (data.email && displayEmail) {
                    displayEmail.textContent = data.email;
                }

                registrationAutoApproved = Boolean(data.auto_approved);
                showRegistrationCompleteState(registrationAutoApproved);
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
                root.dataset.verificationSent = '1';
            }

            if (draft.current_step) {
                restoredStep = parseInt(draft.current_step, 10) || restoredStep;
            }

            if (draft.step1) {
                populateWizardForm(draft.step1, draft.respondent_number);
            }

            if (draft.step2) {
                restoreStep2Documents(draft.step2);
            }
        } catch (error) {
            // Non-blocking
        }
    }

    async function resetWizardFormState() {
        if (form) {
            form.reset();
        }

        clearAllDocumentInputs();
        hideDocUploadError();

        docTypeRadios.forEach((radio) => {
            radio.checked = false;
        });

        if (schoolIdUploadPanel) schoolIdUploadPanel.hidden = true;
        if (nationalIdUploadPanel) nationalIdUploadPanel.hidden = true;

        const sigInput = document.getElementById('kkpSignatureData');
        if (sigInput) {
            sigInput.value = '';
        }

        if (typeof window.kkpRestoreSignaturePreview === 'function') {
            window.kkpRestoreSignaturePreview('');
        }

        if (displayEmail) {
            displayEmail.textContent = 'your-email@example.com';
        }

        verificationSent = false;
        root.dataset.verificationSent = '0';
        restoredStep = 1;
        root.dataset.initialStep = '1';
    }

    function isBrowserReload() {
        const navEntry = performance.getEntriesByType('navigation')[0];
        return navEntry?.type === 'reload';
    }

    async function clearDraftOnRefresh() {
        if (!isBrowserReload() || registrationCompleted) {
            return false;
        }

        try {
            await postJson(`${apiBase}/clear-draft`, {});
        } catch (error) {
            // Non-blocking — still reset the visible form
        }

        await resetWizardFormState();
        return true;
    }

    async function initWizard() {
        bindDocumentTypeControls();

        try {
            sessionStorage.removeItem(`kkp_wizard_step_${slug}`);
        } catch (error) {
            // Non-blocking
        }

        if (root.dataset.completedEmail && displayEmail) {
            displayEmail.textContent = root.dataset.completedEmail;
        }

        if (registrationCompleted) {
            await setStep(3, { skipAutoSend: true });
            showRegistrationCompleteState(registrationAutoApproved);
            return;
        }

        const wasClearedOnRefresh = await clearDraftOnRefresh();

        if (!wasClearedOnRefresh) {
            if (root.dataset.draftEmail && displayEmail) {
                displayEmail.textContent = root.dataset.draftEmail;
            }

            await restoreDraftState();
        }

        const serverEmailError = root.dataset.emailError;

        if (serverEmailError) {
            if (verificationSentOnLoad) {
                verificationSent = true;
                root.dataset.verificationSent = '1';
            }

            await setStep(3, { skipAutoSend: verificationSent });

            showEmailStatus(serverEmailError, 'error');
            return;
        }

        const targetStep = Math.max(1, Math.min(3, wasClearedOnRefresh ? 1 : (restoredStep || initialStep)));
        const skipAutoSendOnStep3 = targetStep === 3 && (verificationSent || verificationSentOnLoad);

        await setStep(targetStep, {
            skipAutoSend: targetStep !== 3 || skipAutoSendOnStep3,
        });
    }

    initWizard();
})();
