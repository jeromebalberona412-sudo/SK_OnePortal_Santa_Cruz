/**
 * Scholarship Application Form — dynamic questionnaire + PDF document management
 */
import * as pdfjsLib from 'pdfjs-dist';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

const MAX_FILE_BYTES = 5 * 1024 * 1024;
const ACCEPTED_MIME = 'application/pdf';

const program = window.__scheduleProgram || {};
const scheduleProgramId = Number(program.id || window.__scheduleProgramId || 0);
const kkFieldLabels = window.__kkFieldLabels || {};
const isReadOnly = Boolean(program.application);
const uploadedDocuments = { ...(program.uploaded_documents || {}) };

const form = document.getElementById('scholarshipApplicationForm');
const kkProfileFieldsContainer = document.getElementById('kkProfileFieldsContainer');
const customQuestionsContainer = document.getElementById('customQuestionsContainer');
const nextStepBtn = document.getElementById('nextStepBtn');
const prevStepBtn = document.getElementById('prevStepBtn');
const cancelBtn = document.getElementById('cancelBtn');
const successModal = document.getElementById('successModal');
const confirmSubmitModal = document.getElementById('confirmSubmitModal');
const backToReviewBtn = document.getElementById('backToReviewBtn');
const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
const goToDashboardBtn = document.getElementById('goToDashboardBtn');
const reviewStepContainer = document.getElementById('reviewStepContainer');
const reviewStatusList = document.getElementById('reviewStatusList');
const confirmInfoTrue = document.getElementById('confirmInfoTrue');
const confirmDocsValid = document.getElementById('confirmDocsValid');
const confirmFalseInfo = document.getElementById('confirmFalseInfo');
const pdfPreviewModal = document.getElementById('pdfPreviewModal');
const pdfPreviewPages = document.getElementById('pdfPreviewPages');
const pdfPreviewTitle = document.getElementById('pdfPreviewTitle');
const pdfPreviewClose = document.getElementById('pdfPreviewClose');

const TOTAL_STEPS = 4;
let currentStep = 1;
let submittedApplication = null;

const PDF_PREVIEW_SCALE = 1.2;
let currentPreviewDocument = null;

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatFileSize(bytes) {
    if (!bytes || bytes <= 0) return '0 Bytes';
    const units = ['Bytes', 'KB', 'MB', 'GB'];
    const power = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    return `${(bytes / 1024 ** power).toFixed(1)} ${units[power]}`;
}

function loadKKProfileData() {
    if (!kkProfileFieldsContainer) return;

    const kkProfile = program.kk_profile || {};
    const selectedFields = program.kk_profiling_fields || Object.keys(kkProfile);

    let fieldsHtml = '';
    selectedFields.forEach((field) => {
        const value = kkProfile[field];
        if (value === undefined || value === null || String(value).trim() === '') return;
        const label = kkFieldLabels[field] || field.replace(/_/g, ' ');
        const isFullWidth = field === 'home_address';
        fieldsHtml += `
            <div class="gf-kk-field ${isFullWidth ? 'full-width' : ''}">
                <span class="gf-kk-field-label">${escapeHtml(label)}</span>
                <span class="gf-kk-field-value">${escapeHtml(value)}</span>
            </div>
        `;
    });

    kkProfileFieldsContainer.innerHTML = fieldsHtml || '<p style="text-align:center;color:#64748b;font-size:14px;padding:20px;">No KK Profiling fields available.</p>';
}

function renderFileCard(documentMeta) {
    const statusText = documentMeta.status === 'uploaded' ? 'Uploaded Successfully' : (documentMeta.status || 'Uploaded');
    const sizeText = documentMeta.size_display || formatFileSize(documentMeta.size);
    const questionId = escapeHtml(documentMeta.question_id);

    return `
        <div class="gf-file-uploaded-card">
            <button type="button" class="gf-file-card" data-preview-document="${questionId}">
                <span class="gf-file-card-icon-wrap" aria-hidden="true">
                    <svg class="gf-file-card-icon-svg" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span class="gf-file-card-badge">PDF</span>
                </span>
                <span class="gf-file-card-body">
                    <span class="gf-file-card-name">${escapeHtml(documentMeta.original_name)}</span>
                    <span class="gf-file-card-meta-row">
                        <span class="gf-file-card-meta">${escapeHtml(sizeText)}</span>
                        <span class="gf-file-card-status">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            ${escapeHtml(statusText)}
                        </span>
                    </span>
                    ${documentMeta.uploaded_at_display ? `<span class="gf-file-card-time">${escapeHtml(documentMeta.uploaded_at_display)}</span>` : ''}
                </span>
                <span class="gf-file-card-preview-label">Preview</span>
            </button>
            ${isReadOnly ? '' : `<button type="button" class="gf-file-replace-btn" data-replace-document="${questionId}">Replace file</button>`}
        </div>
    `;
}

function renderQuestionField(question, index) {
    const required = question.required ? '<span class="gf-required">*</span>' : '';
    const requiredAttr = question.required ? 'required' : '';
    const name = `question_${question.id}`;
    const label = escapeHtml(question.label || `Question ${index + 1}`);
    const questionId = escapeHtml(question.id);

    switch (question.type) {
        case 'paragraph':
            return `
                <div class="gf-question">
                    <label class="gf-question-label">${label} ${required}</label>
                    <div class="gf-input-wrapper">
                        <textarea name="${name}" class="gf-input" rows="4" placeholder="Your answer" data-question-id="${questionId}" data-question-type="paragraph" ${requiredAttr} ${isReadOnly ? 'disabled' : ''}></textarea>
                    </div>
                </div>
            `;
        case 'number':
            return `
                <div class="gf-question">
                    <label class="gf-question-label">${label} ${required}</label>
                    <div class="gf-input-wrapper">
                        <input type="number" name="${name}" class="gf-input" placeholder="Your answer" data-question-id="${questionId}" data-question-type="number" ${requiredAttr} ${isReadOnly ? 'disabled' : ''}>
                    </div>
                </div>
            `;
        case 'checkbox':
            return `
                <div class="gf-question">
                    <label class="gf-question-label">${label} ${required}</label>
                    <div class="gf-checkbox-group">
                        ${(question.options || []).map((option) => `
                            <label class="gf-checkbox-item">
                                <input type="checkbox" name="${name}[]" value="${escapeHtml(option)}" data-question-id="${questionId}" data-question-type="checkbox" ${isReadOnly ? 'disabled' : ''}>
                                <span>${escapeHtml(option)}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        case 'radio':
            return `
                <div class="gf-question">
                    <label class="gf-question-label">${label} ${required}</label>
                    <div class="gf-radio-group">
                        ${(question.options || []).map((option) => `
                            <label class="gf-radio-item">
                                <input type="radio" name="${name}" value="${escapeHtml(option)}" data-question-id="${questionId}" data-question-type="radio" ${requiredAttr} ${isReadOnly ? 'disabled' : ''}>
                                <span>${escapeHtml(option)}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        case 'file':
            return `
                <div class="gf-question gf-question-file" data-file-question-id="${questionId}">
                    <label class="gf-question-label">${label} ${required}</label>
                    ${isReadOnly ? '' : `
                        <div class="gf-file-upload" data-file-upload="${questionId}">
                            <input
                                type="file"
                                name="${name}"
                                class="gf-file-input"
                                accept="application/pdf,.pdf"
                                data-question-id="${questionId}"
                                data-question-type="file"
                                data-question-label="${label}"
                                ${requiredAttr}
                            >
                            <div class="gf-file-drop-zone" data-drop-zone="${questionId}">
                                <svg class="gf-file-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <p class="gf-file-text">Drag and drop PDF files here or click to browse</p>
                                <p class="gf-file-hint">Accepted: PDF only, max 5 MB</p>
                            </div>
                            <div class="gf-file-progress" data-file-progress="${questionId}" hidden>
                                <div class="gf-file-progress-bar" data-file-progress-bar="${questionId}"></div>
                                <span class="gf-file-progress-text">Uploading...</span>
                            </div>
                            <p class="gf-file-error" data-file-error="${questionId}" hidden></p>
                        </div>
                    `}
                    <div class="gf-file-card-list" data-file-card-list="${questionId}"></div>
                </div>
            `;
        default:
            return `
                <div class="gf-question">
                    <label class="gf-question-label">${label} ${required}</label>
                    <div class="gf-input-wrapper">
                        <input type="text" name="${name}" class="gf-input" placeholder="Your answer" data-question-id="${questionId}" data-question-type="text" ${requiredAttr} ${isReadOnly ? 'disabled' : ''}>
                    </div>
                </div>
            `;
    }
}

function renderCustomQuestions() {
    if (!customQuestionsContainer) return;

    const questions = program.custom_questions || [];
    if (!questions.length) {
        customQuestionsContainer.innerHTML = '<p style="text-align:center;color:#64748b;padding:20px;">No application questions configured for this program.</p>';
        return;
    }

    customQuestionsContainer.innerHTML = `
        <div class="gf-card">
            <h2 class="gf-section-title">Application Questionnaire</h2>
            ${questions.map((question, index) => renderQuestionField(question, index)).join('')}
        </div>
    `;

    setupFileUploads();
    hydrateExistingDocuments();
    hydrateExistingAnswers();
}

function hydrateExistingDocuments() {
    Object.values(uploadedDocuments).forEach((documentMeta) => {
        renderDocumentCard(documentMeta.question_id, documentMeta);
    });
}

function hydrateExistingAnswers() {
    const answers = program.application?.answers || [];
    answers.forEach((answer) => {
        if (answer.question_type === 'file') return;

        const fields = form?.querySelectorAll(`[data-question-id="${answer.question_id}"]`);
        if (!fields?.length) return;

        const value = answer.answer;
        const firstField = fields[0];
        const type = firstField.dataset.questionType;

        if (type === 'checkbox' && Array.isArray(value)) {
            fields.forEach((field) => {
                field.checked = value.includes(field.value);
            });
            return;
        }

        if (type === 'radio') {
            fields.forEach((field) => {
                field.checked = field.value === value;
            });
            return;
        }

        if (firstField.tagName === 'TEXTAREA' || firstField.tagName === 'INPUT') {
            firstField.value = value ?? '';
        }
    });
}

function renderDocumentCard(questionId, documentMeta) {
    const list = document.querySelector(`[data-file-card-list="${questionId}"]`);
    const uploadWrapper = document.querySelector(`[data-file-upload="${questionId}"]`);
    if (!list || !documentMeta) return;

    list.innerHTML = renderFileCard(documentMeta);
    if (uploadWrapper) {
        uploadWrapper.hidden = true;
    }
    bindPreviewButtons(list);
    bindReplaceButtons(list);
}

function showUploadArea(questionId) {
    const uploadWrapper = document.querySelector(`[data-file-upload="${questionId}"]`);
    const list = document.querySelector(`[data-file-card-list="${questionId}"]`);
    if (uploadWrapper) {
        uploadWrapper.hidden = false;
    }
    if (list) {
        list.innerHTML = '';
    }
    setUploadState(questionId, 'idle');
}

function setUploadState(questionId, state, message = '') {
    const progress = document.querySelector(`[data-file-progress="${questionId}"]`);
    const progressBar = document.querySelector(`[data-file-progress-bar="${questionId}"]`);
    const error = document.querySelector(`[data-file-error="${questionId}"]`);
    const dropZone = document.querySelector(`[data-drop-zone="${questionId}"]`);

    if (progress) progress.hidden = state !== 'uploading';
    if (progressBar) progressBar.style.width = state === 'uploading' ? '65%' : '0%';
    if (error) {
        error.hidden = state !== 'error';
        error.textContent = message;
    }
    if (dropZone) {
        dropZone.classList.toggle('is-uploading', state === 'uploading');
        dropZone.classList.toggle('is-error', state === 'error');
        dropZone.classList.toggle('is-success', state === 'success');
    }
}

async function uploadPdfFile(questionId, file, questionLabel) {
    if (file.size > MAX_FILE_BYTES) {
        setUploadState(questionId, 'error', 'PDF file must not exceed 5 MB.');
        return;
    }

    if (file.type !== ACCEPTED_MIME && !file.name.toLowerCase().endsWith('.pdf')) {
        setUploadState(questionId, 'error', 'Only PDF files are allowed.');
        return;
    }

    setUploadState(questionId, 'uploading');

    const formData = new FormData();
    formData.append('schedule_program_id', String(scheduleProgramId));
    formData.append('question_id', questionId);
    formData.append('file', file);

    try {
        const response = await fetch('/api/kabataan/programs/documents/upload', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: formData,
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Upload failed.';
            throw new Error(message);
        }

        const documentMeta = {
            ...data.document,
            question_label: questionLabel,
        };

        uploadedDocuments[questionId] = documentMeta;
        renderDocumentCard(questionId, documentMeta);
        setUploadState(questionId, 'success');

        const progressBar = document.querySelector(`[data-file-progress-bar="${questionId}"]`);
        if (progressBar) progressBar.style.width = '100%';

        setTimeout(() => {
            setUploadState(questionId, 'idle');
            if (currentStep === TOTAL_STEPS) {
                updateSubmitButtonState();
            }
        }, 1200);
    } catch (error) {
        setUploadState(questionId, 'error', error.message || 'Unable to upload PDF.');
    }
}

function setupFileUploads() {
    document.querySelectorAll('[data-file-upload]').forEach((wrapper) => {
        const input = wrapper.querySelector('.gf-file-input');
        const questionId = wrapper.dataset.fileUpload;
        const dropZone = wrapper.querySelector(`[data-drop-zone="${questionId}"]`);
        if (!input || !questionId || isReadOnly) return;

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            uploadPdfFile(questionId, file, input.dataset.questionLabel || 'Document');
            input.value = '';
        });

        if (!dropZone) return;

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.remove('is-dragover');
            });
        });

        dropZone.addEventListener('drop', (event) => {
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            uploadPdfFile(questionId, file, input.dataset.questionLabel || 'Document');
        });
    });

    bindPreviewButtons(document);
}

function bindPreviewButtons(root) {
    root.querySelectorAll('[data-preview-document]').forEach((button) => {
        button.addEventListener('click', () => {
            const questionId = button.getAttribute('data-preview-document');
            const documentMeta = uploadedDocuments[questionId];
            if (!documentMeta) return;
            openPdfPreview(documentMeta);
        });
    });
}

function bindReplaceButtons(root) {
    root.querySelectorAll('[data-replace-document]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const questionId = button.getAttribute('data-replace-document');
            if (!questionId || isReadOnly) return;
            delete uploadedDocuments[questionId];
            showUploadArea(questionId);
        });
    });
}

async function openPdfPreview(documentMeta) {
    if (!pdfPreviewModal || !pdfPreviewPages) return;

    currentPreviewDocument = documentMeta;
    pdfPreviewTitle.textContent = documentMeta.original_name || 'PDF Preview';
    pdfPreviewModal.hidden = false;
    document.body.style.overflow = 'hidden';
    pdfPreviewPages.innerHTML = '<p class="gf-pdf-loading">Loading PDF preview...</p>';

    try {
        const loadingTask = pdfjsLib.getDocument({
            url: documentMeta.preview_url,
            withCredentials: true,
        });
        const pdf = await loadingTask.promise;
        pdfPreviewPages.innerHTML = '';

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
            const page = await pdf.getPage(pageNumber);
            const viewport = page.getViewport({ scale: PDF_PREVIEW_SCALE });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.className = 'gf-pdf-page';

            pdfPreviewPages.appendChild(canvas);
            await page.render({ canvasContext: context, viewport }).promise;
        }
    } catch (error) {
        pdfPreviewPages.innerHTML = '<p class="gf-pdf-error">Unable to preview this PDF right now.</p>';
        console.error(error);
    }
}

function closePdfPreview() {
    if (!pdfPreviewModal) return;
    pdfPreviewModal.hidden = true;
    document.body.style.overflow = '';
    if (pdfPreviewPages) pdfPreviewPages.innerHTML = '';
    currentPreviewDocument = null;
}

function collectAnswers() {
    const answers = [];
    const questions = program.custom_questions || [];

    questions.forEach((question) => {
        const fields = form.querySelectorAll(`[data-question-id="${question.id}"]`);
        if (!fields.length && question.type !== 'file') return;

        let answer = '';

        if (question.type === 'file') {
            answer = uploadedDocuments[question.id] || null;
        } else if (question.type === 'checkbox') {
            answer = Array.from(fields)
                .filter((field) => field.checked)
                .map((field) => field.value);
        } else if (question.type === 'radio') {
            const checked = Array.from(fields).find((field) => field.checked);
            answer = checked ? checked.value : '';
        } else {
            answer = fields[0].value || '';
        }

        answers.push({
            question_id: question.id,
            question_label: question.label,
            question_type: question.type,
            answer,
        });
    });

    return answers;
}

function validateForm() {
    if (!form) return true;

    let isValid = true;
    let firstInvalid = null;
    const questions = program.custom_questions || [];

    questions.forEach((question) => {
        if (!question.required) return;

        if (question.type === 'file') {
            if (!uploadedDocuments[question.id]) {
                isValid = false;
                const error = document.querySelector(`[data-file-error="${question.id}"]`);
                if (error) {
                    error.hidden = false;
                    error.textContent = 'Please upload the required PDF document.';
                }
                if (!firstInvalid) firstInvalid = document.querySelector(`[data-file-question-id="${question.id}"]`);
            }
            return;
        }

        const fields = form.querySelectorAll(`[data-question-id="${question.id}"]`);
        const firstField = fields[0];
        if (!firstField) return;

        if (firstField.type === 'radio') {
            if (!Array.from(fields).some((field) => field.checked)) {
                isValid = false;
                if (!firstInvalid) firstInvalid = firstField;
            }
            return;
        }

        if (!String(firstField.value || '').trim()) {
            isValid = false;
            if (!firstInvalid) firstInvalid = firstField;
        }
    });

    if (!isValid && firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return isValid;
}

async function submitApplication() {
    const response = await fetch('/api/kabataan/programs/applications', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            schedule_program_id: scheduleProgramId,
            answers: collectAnswers(),
        }),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Submission failed.';
        throw new Error(message);
    }

    return data;
}

function getKkProfileValue(field) {
    const kkProfile = program.kk_profile || {};
    return kkProfile[field] ?? '';
}

function getFullName() {
    const fullName = String(getKkProfileValue('full_name') || '').trim();
    if (fullName) return fullName;

    return [getKkProfileValue('first_name'), getKkProfileValue('middle_name'), getKkProfileValue('last_name'), getKkProfileValue('suffix')]
        .map((part) => String(part || '').trim())
        .filter(Boolean)
        .join(' ');
}

function getReviewPersonalFields() {
    const address = getKkProfileValue('home_address')
        || [getKkProfileValue('purok_zone'), getKkProfileValue('barangay'), getKkProfileValue('city_municipality') || getKkProfileValue('city')]
            .map((part) => String(part || '').trim())
            .filter(Boolean)
            .join(', ');

    return [
        ['Full Name', getFullName()],
        ['Birth Date', getKkProfileValue('birthday')],
        ['Age', getKkProfileValue('age')],
        ['Address', address],
        ['Contact Number', getKkProfileValue('contact_number')],
    ];
}

function isPersonalInfoComplete() {
    return getReviewPersonalFields().every(([, value]) => String(value || '').trim() !== '');
}

function getFileQuestions() {
    return (program.custom_questions || []).filter((question) => question.type === 'file');
}

function getCompletionItems() {
    const items = [
        { label: 'Personal Information', complete: isPersonalInfoComplete() },
    ];

    getFileQuestions().forEach((question) => {
        const uploaded = Boolean(uploadedDocuments[question.id]);
        const required = Boolean(question.required);
        items.push({
            label: question.label || 'Document',
            complete: !required || uploaded,
            required,
        });
    });

    return items;
}

function isApplicationComplete() {
    return getCompletionItems().every((item) => item.complete);
}

function renderReviewStep() {
    if (!reviewStepContainer || !reviewStatusList) return;

    const completionItems = getCompletionItems();
    reviewStatusList.innerHTML = completionItems.map((item) => `
        <div class="gf-review-status-item ${item.complete ? '' : 'is-missing'}">
            <span>${escapeHtml(item.label)}</span>
            <span>${item.complete ? '✅ Complete' : '❌ Missing'}</span>
        </div>
    `).join('');

    const personalFields = getReviewPersonalFields();
    const personalHtml = personalFields.map(([label, value]) => `
        <div class="gf-review-field">
            <span class="gf-review-field-label">${escapeHtml(label)}</span>
            <span class="gf-review-field-value">${escapeHtml(value || '—')}</span>
        </div>
    `).join('');

    const fileQuestions = getFileQuestions();
    const documentsHtml = fileQuestions.length
        ? fileQuestions.map((question) => {
            const doc = uploadedDocuments[question.id];
            const isUploaded = Boolean(doc);
            return `
                <div class="gf-review-doc">
                    <div class="gf-review-doc-header">
                        <span class="gf-review-doc-title">${escapeHtml(question.label || 'Document')}</span>
                        <span class="gf-review-doc-status ${isUploaded ? '' : 'is-missing'}">
                            ${isUploaded ? '✅ Uploaded Successfully' : '❌ Missing'}
                        </span>
                    </div>
                    ${isUploaded ? `
                        <div class="gf-review-doc-preview">
                            <button type="button" data-preview-document="${escapeHtml(question.id)}">📷 Preview document</button>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('')
        : '<p class="gf-review-intro">No document uploads required for this program.</p>';

    const answers = collectAnswers().filter((answer) => answer.question_type !== 'file' && String(answer.answer || '').trim() !== '');
    const answersHtml = answers.length
        ? `
            <div class="gf-review-section">
                <h3>Additional Responses</h3>
                <div class="gf-review-grid">
                    ${answers.map((answer) => `
                        <div class="gf-review-field">
                            <span class="gf-review-field-label">${escapeHtml(answer.question_label || 'Answer')}</span>
                            <span class="gf-review-field-value">${escapeHtml(Array.isArray(answer.answer) ? answer.answer.join(', ') : answer.answer)}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `
        : '';

    reviewStepContainer.innerHTML = `
        <div class="gf-review-section">
            <h3>Personal Information</h3>
            <div class="gf-review-grid">${personalHtml}</div>
        </div>
        <div class="gf-review-section">
            <h3>Uploaded Requirements</h3>
            ${documentsHtml}
        </div>
        ${answersHtml}
    `;

    bindPreviewButtons(reviewStepContainer);
}

function updateStepUI() {
    document.querySelectorAll('.gf-step-panel').forEach((panel) => {
        const step = Number(panel.dataset.step);
        panel.hidden = step !== currentStep;
        panel.classList.toggle('is-active', step === currentStep);
    });

    document.querySelectorAll('[data-step-item]').forEach((item) => {
        const step = Number(item.dataset.stepItem);
        item.classList.toggle('is-active', step === currentStep);
        item.classList.toggle('is-done', step < currentStep);
    });

    if (prevStepBtn) prevStepBtn.hidden = currentStep <= 1;

    if (nextStepBtn) {
        if (isReadOnly) {
            nextStepBtn.textContent = 'Already Applied';
            nextStepBtn.disabled = true;
            nextStepBtn.hidden = false;
        } else if (currentStep === TOTAL_STEPS) {
            nextStepBtn.textContent = 'Submit Application';
            nextStepBtn.hidden = false;
            updateSubmitButtonState();
        } else {
            nextStepBtn.textContent = 'Continue';
            nextStepBtn.disabled = false;
            nextStepBtn.hidden = false;
        }
    }

    if (currentStep === TOTAL_STEPS && !isReadOnly) {
        updateSubmitButtonState();
    }

    if (currentStep === 3) {
        renderReviewStep();
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateSubmitButtonState() {
    if (!nextStepBtn || currentStep !== TOTAL_STEPS || isReadOnly) return;

    const allConfirmed = [confirmInfoTrue, confirmDocsValid, confirmFalseInfo].every((input) => input?.checked);
    const complete = isApplicationComplete();
    nextStepBtn.disabled = !allConfirmed || !complete;
}

function goToStep(step) {
    currentStep = Math.max(1, Math.min(TOTAL_STEPS, step));
    updateStepUI();
}

function validateCurrentStep() {
    if (currentStep === 1) {
        if (!isPersonalInfoComplete()) {
            alert('Your KK Profiling information is incomplete. Please update your KK Profile before applying.');
            return false;
        }
        return true;
    }

    if (currentStep === 2) {
        return validateForm();
    }

    if (currentStep === 3) {
        if (!isApplicationComplete()) {
            alert('Please complete all required sections before continuing.');
            renderReviewStep();
            return false;
        }
        return true;
    }

    return true;
}

function formatReferenceNumber(applicationId) {
    const year = new Date().getFullYear();
    const padded = String(applicationId || 0).padStart(6, '0');
    return `KK-${year}-${padded}`;
}

function showSuccessModal(application) {
    submittedApplication = application;
    const referenceEl = document.getElementById('successReferenceNumber');
    if (referenceEl) {
        referenceEl.textContent = formatReferenceNumber(application?.id);
    }
    if (successModal) {
        successModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }
}

function openConfirmSubmitModal() {
    if (!confirmSubmitModal) return;
    confirmSubmitModal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeConfirmSubmitModal() {
    if (!confirmSubmitModal) return;
    confirmSubmitModal.hidden = true;
    if (!successModal || successModal.hidden) {
        document.body.style.overflow = '';
    }
}

function updateHeader() {
    const title = document.getElementById('gfProgramTitle');
    const description = document.getElementById('gfProgramDescription');
    const period = document.getElementById('gfApplicationPeriod');
    const status = document.getElementById('gfProgramStatus');

    if (title) title.textContent = program.program_name || 'Scholarship Application';
    if (description) description.textContent = program.announcement || program.program_type || '';
    if (period) period.textContent = `${program.start_date_display || '—'} - ${program.end_date_display || '—'}`;
    if (status) {
        status.textContent = program.status === 'open' ? 'Open' : 'Closed';
        status.className = `gf-status-badge ${program.status === 'open' ? 'gf-status-open' : 'gf-status-closed'}`;
    }
}

async function handleFinalSubmit() {
    if (isReadOnly) {
        alert('You have already applied for this program.');
        return;
    }

    if (nextStepBtn) nextStepBtn.disabled = true;
    if (confirmSubmitBtn) confirmSubmitBtn.disabled = true;

    if (typeof showLoading === 'function') {
        showLoading('Submitting application...');
    }

    try {
        const data = await submitApplication();
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        closeConfirmSubmitModal();
        showSuccessModal(data.application || data);
    } catch (error) {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        alert(error.message || 'Unable to submit application.');
        updateSubmitButtonState();
        if (confirmSubmitBtn) confirmSubmitBtn.disabled = false;
    }
}

if (form) {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });
}

if (nextStepBtn) {
    nextStepBtn.addEventListener('click', () => {
        if (isReadOnly) return;

        if (currentStep === TOTAL_STEPS) {
            if (!validateCurrentStep() || !isApplicationComplete()) {
                alert('Please complete all required sections and confirmations before submitting.');
                updateSubmitButtonState();
                return;
            }
            openConfirmSubmitModal();
            return;
        }

        if (!validateCurrentStep()) return;
        goToStep(currentStep + 1);
    });
}

[confirmInfoTrue, confirmDocsValid, confirmFalseInfo].forEach((input) => {
    input?.addEventListener('change', updateSubmitButtonState);
});

if (backToReviewBtn) {
    backToReviewBtn.addEventListener('click', () => {
        closeConfirmSubmitModal();
        goToStep(3);
    });
}

if (confirmSubmitBtn) {
    confirmSubmitBtn.addEventListener('click', handleFinalSubmit);
}

if (confirmSubmitModal) {
    confirmSubmitModal.querySelectorAll('[data-close-confirm-modal]').forEach((el) => {
        el.addEventListener('click', closeConfirmSubmitModal);
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to cancel? All your progress will be lost.')) {
            window.location.href = `/scholarship/apply?schedule=${encodeURIComponent(scheduleProgramId)}`;
        }
    });
}

if (goToDashboardBtn) {
    goToDashboardBtn.addEventListener('click', () => {
        window.location.href = window.__dashboardUrl || '/dashboard';
    });
}

if (pdfPreviewClose) {
    pdfPreviewClose.addEventListener('click', closePdfPreview);
}

if (pdfPreviewModal) {
    pdfPreviewModal.addEventListener('click', (event) => {
        if (event.target === pdfPreviewModal || event.target.classList.contains('gf-pdf-modal-overlay')) {
            closePdfPreview();
        }
    });
}

function init() {
    if (!scheduleProgramId) return;

    updateHeader();
    loadKKProfileData();
    renderCustomQuestions();
    updateStepUI();

    if (isReadOnly && form) {
        form.querySelectorAll('input, select, textarea, button').forEach((element) => {
            if (element.id !== 'goToDashboardBtn') {
                element.disabled = true;
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
