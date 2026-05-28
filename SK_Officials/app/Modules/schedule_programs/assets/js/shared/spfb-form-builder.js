/**
 * Shared sports-style application form question builder (spfb)
 */
(function (global) {
    let spfbQuestions = [];
    let spfbEditingId = null;
    let showToast = function (msg) { if (msg) console.warn(msg); };
    const QUESTION_TYPES = [
        { value: 'text',      label: 'Short Answer'    },
        { value: 'paragraph', label: 'Paragraph'       },
        { value: 'number',    label: 'Number'          },
        { value: 'checkbox',  label: 'Checkboxes'      },
        { value: 'radio',     label: 'Multiple Choice' },
        { value: 'file',      label: 'File Upload'     },
    ];

    function getTypeLabel(value) {
        const t = QUESTION_TYPES.find(t => t.value === value);
        return t ? t.label : value;
    }

    function generateQId() {
        return 'q_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
    }

    function addQuestion() {
        // If currently editing another question, commit it first
        if (spfbEditingId) commitEdit(spfbEditingId);

        const newQ = {
            id:       generateQId(),
            label:    '',
            type:     'text',
            options:  ['Option 1'],
            required: false,
        };
        spfbQuestions.push(newQ);
        spfbEditingId = newQ.id;
        renderQuestionList();
        // Scroll to the new card
        setTimeout(() => {
            const card = document.querySelector(`[data-qid="${newQ.id}"]`);
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 60);
    }

    function deleteQuestion(qid) {
        spfbQuestions = spfbQuestions.filter(q => q.id !== qid);
        if (spfbEditingId === qid) spfbEditingId = null;
        renderQuestionList();
    }

    function copyQuestion(qid) {
        if (spfbEditingId) commitEdit(spfbEditingId);
        const original = spfbQuestions.find(q => q.id === qid);
        if (!original) return;
        const copy = JSON.parse(JSON.stringify(original));
        copy.id = generateQId();
        const idx = spfbQuestions.findIndex(q => q.id === qid);
        spfbQuestions.splice(idx + 1, 0, copy);
        renderQuestionList();
        setTimeout(() => {
            const card = document.querySelector(`[data-qid="${copy.id}"]`);
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 60);
    }

    // â”€â”€ Delete Confirmation Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    let _pendingDeleteQid = null;

    function showDeleteConfirm(qid) {
        _pendingDeleteQid = qid;
        let modal = document.getElementById('spfbDeleteConfirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'spfbDeleteConfirmModal';
            modal.className = 'spfb-delete-confirm-overlay';
            modal.innerHTML = `
                <div class="spfb-delete-confirm-box">
                    <div class="spfb-delete-confirm-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </div>
                    <p class="spfb-delete-confirm-text">Delete this question?</p>
                    <p class="spfb-delete-confirm-sub">This action cannot be undone.</p>
                    <div class="spfb-delete-confirm-actions">
                        <button type="button" class="sports-btn sports-btn-outline" id="spfbDeleteCancelBtn">Cancel</button>
                        <button type="button" class="sports-btn sports-btn-danger" id="spfbDeleteConfirmBtn">Yes, Delete</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.getElementById('spfbDeleteCancelBtn').addEventListener('click', () => {
                modal.style.display = 'none';
                _pendingDeleteQid = null;
            });
            document.getElementById('spfbDeleteConfirmBtn').addEventListener('click', () => {
                if (_pendingDeleteQid) deleteQuestion(_pendingDeleteQid);
                modal.style.display = 'none';
                _pendingDeleteQid = null;
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    _pendingDeleteQid = null;
                }
            });
        }
        modal.style.display = 'flex';
    }

    function startEdit(qid) {
        if (spfbEditingId && spfbEditingId !== qid) commitEdit(spfbEditingId);
        spfbEditingId = qid;
        renderQuestionList();
        setTimeout(() => {
            const inp = document.querySelector(`[data-qid="${qid}"] .spfb-q-label-input`);
            if (inp) inp.focus();
        }, 40);
    }

    function commitEdit(qid) {
        const card = document.querySelector(`[data-qid="${qid}"]`);
        if (!card) return;
        const q = spfbQuestions.find(q => q.id === qid);
        if (!q) return;

        // Read label
        const labelInp = card.querySelector('.spfb-q-label-input');
        if (labelInp) q.label = labelInp.value.trim();

        // Read type
        const typeSelect = card.querySelector('.spfb-q-type-select');
        if (typeSelect) q.type = typeSelect.value;

        // Read options (for checkbox/radio)
        if (q.type === 'checkbox' || q.type === 'radio') {
            const optInputs = card.querySelectorAll('.spfb-option-input');
            q.options = Array.from(optInputs).map(i => i.value.trim()).filter(v => v !== '');
            if (q.options.length === 0) q.options = ['Option 1'];
        }

        // Read required toggle
        const reqToggle = card.querySelector('.spfb-req-toggle');
        if (reqToggle) q.required = reqToggle.checked;
    }

    function renderQuestionList() {
        const list = document.getElementById('spfbQuestionList');
        const emptyState = document.getElementById('spfbEmptyState');
        const countBadge = document.getElementById('spfbQuestionCount');
        if (!list) return;

        // Update count badge
        if (countBadge) {
            countBadge.textContent = `${spfbQuestions.length} question${spfbQuestions.length !== 1 ? 's' : ''}`;
        }

        // Remove all question cards (keep empty state)
        list.querySelectorAll('.spfb-question-card').forEach(c => c.remove());

        if (spfbQuestions.length === 0) {
            if (emptyState) emptyState.style.display = 'flex';
            return;
        }
        if (emptyState) emptyState.style.display = 'none';

        spfbQuestions.forEach((q, idx) => {
            const isEditing = spfbEditingId === q.id;
            const card = document.createElement('div');
            card.className = `spfb-question-card${isEditing ? ' spfb-question-card--editing' : ''}`;
            card.setAttribute('data-qid', q.id);

            if (isEditing) {
                card.innerHTML = buildEditCard(q, idx);
            } else {
                card.innerHTML = buildPreviewCard(q, idx);
            }

            list.appendChild(card);
            attachCardEvents(card, q);
        });
    }

    function buildPreviewCard(q, idx) {
        const typeLabel = getTypeLabel(q.type);
        const reqBadge  = q.required
            ? '<span class="spfb-req-badge spfb-req-badge--on">Required</span>'
            : '<span class="spfb-req-badge spfb-req-badge--off">Optional</span>';

        let previewHTML = '';
        if (q.type === 'text') {
            previewHTML = '<div class="spfb-preview-input">Short answer text</div>';
        } else if (q.type === 'paragraph') {
            previewHTML = '<div class="spfb-preview-input spfb-preview-paragraph">Long answer text</div>';
        } else if (q.type === 'number') {
            previewHTML = '<div class="spfb-preview-input" style="min-width:100px;">0</div>';
        } else if (q.type === 'checkbox' || q.type === 'radio') {
            const inputType = q.type === 'checkbox' ? 'checkbox' : 'radio';
            const opts = (q.options && q.options.length) ? q.options : ['Option 1'];
            previewHTML = `<div class="spfb-preview-options">${opts.map(o =>
                `<label class="spfb-preview-option"><input type="${inputType}" disabled> <span>${o || '(empty)'}</span></label>`
            ).join('')}</div>`;
        } else if (q.type === 'file') {
            previewHTML = '<div class="spfb-preview-file"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> File upload field</div>';
        }

        const labelDisplay = q.label || `<em style="color:#9ca3af;">Untitled Question ${idx + 1}</em>`;

        return `
        <div class="spfb-card-preview-header">
            <div class="spfb-card-num">${idx + 1}</div>
            <div class="spfb-card-meta">
                <div class="spfb-card-label">${labelDisplay}</div>
                <div class="spfb-card-type-tag">${typeLabel}</div>
            </div>
            <div class="spfb-card-actions">
                ${reqBadge}
                <button type="button" class="spfb-icon-btn spfb-edit-btn" title="Edit question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-copy-btn" title="Duplicate question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </div>
        <div class="spfb-card-preview-body">${previewHTML}</div>`;
    }

    function buildEditCard(q, idx) {
        const typeOptions = `<option value="" disabled>Select input type</option>` + QUESTION_TYPES.map(t =>
            `<option value="${t.value}" ${q.type === t.value ? 'selected' : ''}>${t.label}</option>`
        ).join('');

        const hasOptions = q.type === 'checkbox' || q.type === 'radio';
        const opts = (q.options && q.options.length) ? q.options : ['Option 1'];

        const optionsHTML = hasOptions ? `
        <div class="spfb-options-section" id="spfbOptionsSection_${q.id}">
            <label class="spfb-options-label">Answer Options</label>
            <div class="spfb-options-list" id="spfbOptionsList_${q.id}">
                ${opts.map((o, oi) => `
                <div class="spfb-option-row">
                    <span class="spfb-option-bullet">${q.type === 'radio' ? 'â—' : 'â–ª'}</span>
                    <input type="text" class="spfb-option-input sports-input" value="${escapeHtml(o)}" placeholder="Option ${oi + 1}" maxlength="200">
                    <button type="button" class="spfb-icon-btn spfb-remove-option-btn" data-oi="${oi}" title="Remove option">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>`).join('')}
            </div>
            <button type="button" class="spfb-add-option-btn" id="spfbAddOptionBtn_${q.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Option
            </button>
        </div>` : '';

        return `
        <div class="spfb-edit-header">
            <div class="spfb-card-num spfb-card-num--edit">${idx + 1}</div>
            <span class="spfb-editing-label">Editing Question</span>
            <div style="display:flex;align-items:center;gap:4px;margin-left:auto;">
                <button type="button" class="spfb-icon-btn spfb-copy-btn" title="Duplicate question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                </button>
                <button type="button" class="spfb-icon-btn spfb-delete-btn" title="Delete question">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>
        </div>

        <div class="spfb-edit-body">
            <!-- Question Label -->
            <div class="spfb-edit-field">
                <label class="spfb-edit-field-label">Question Label <span class="sports-req">*</span></label>
                <input type="text" class="spfb-q-label-input sports-input" value="${escapeHtml(q.label)}" placeholder="Enter your question here..." maxlength="300">
            </div>

            <!-- Question Type -->
            <div class="spfb-edit-field">
                <label class="spfb-edit-field-label">Input Type</label>
                <select class="spfb-q-type-select sports-input" style="cursor:pointer;">
                    ${typeOptions}
                </select>
            </div>

            <!-- Options (checkbox/radio only) -->
            ${optionsHTML}

            <!-- Required Toggle -->
            <div class="spfb-edit-field spfb-req-row">
                <label class="spfb-edit-field-label">Required</label>
                <label class="spfb-toggle-switch">
                    <input type="checkbox" class="spfb-req-toggle" ${q.required ? 'checked' : ''}>
                    <span class="spfb-toggle-slider"></span>
                </label>
                <span class="spfb-toggle-hint" id="spfbReqHint_${q.id}">${q.required ? 'Required' : 'Optional'}</span>
            </div>
        </div>`;
    }

    function attachCardEvents(card, q) {
        const qid = q.id;

        // Edit button (preview mode)
        const editBtn = card.querySelector('.spfb-edit-btn');
        if (editBtn) editBtn.addEventListener('click', () => startEdit(qid));

        // Copy/duplicate button
        const copyBtn = card.querySelector('.spfb-copy-btn');
        if (copyBtn) copyBtn.addEventListener('click', () => copyQuestion(qid));

        // Delete button â€” show confirmation modal
        const deleteBtn = card.querySelector('.spfb-delete-btn');
        if (deleteBtn) deleteBtn.addEventListener('click', () => showDeleteConfirm(qid));

        // Type select change â†’ re-render to show/hide options
        const typeSelect = card.querySelector('.spfb-q-type-select');
        if (typeSelect) {
            typeSelect.addEventListener('change', () => {
                commitEdit(qid);
                // Keep editing same question
                spfbEditingId = qid;
                renderQuestionList();
            });
        }

        // Required toggle hint update
        const reqToggle = card.querySelector('.spfb-req-toggle');
        const reqHint   = card.querySelector(`#spfbReqHint_${qid}`);
        if (reqToggle && reqHint) {
            reqToggle.addEventListener('change', () => {
                reqHint.textContent = reqToggle.checked ? 'Required' : 'Optional';
            });
        }

        // Add option button
        const addOptBtn = card.querySelector(`#spfbAddOptionBtn_${qid}`);
        if (addOptBtn) {
            addOptBtn.addEventListener('click', () => {
                commitEdit(qid);
                const qObj = spfbQuestions.find(x => x.id === qid);
                if (qObj) {
                    qObj.options.push(`Option ${qObj.options.length + 1}`);
                    spfbEditingId = qid;
                    renderQuestionList();
                    // Focus last option input
                    setTimeout(() => {
                        const inputs = document.querySelectorAll(`[data-qid="${qid}"] .spfb-option-input`);
                        if (inputs.length) inputs[inputs.length - 1].focus();
                    }, 40);
                }
            });
        }

        // Remove option buttons
        card.querySelectorAll('.spfb-remove-option-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const oi = parseInt(btn.getAttribute('data-oi'), 10);
                commitEdit(qid);
                const qObj = spfbQuestions.find(x => x.id === qid);
                if (qObj && qObj.options.length > 1) {
                    qObj.options.splice(oi, 1);
                    spfbEditingId = qid;
                    renderQuestionList();
                } else {
                    showToast('At least one option is required', 'error');
                }
            });
        });

        // Click on preview card body â†’ start edit
        const previewBody = card.querySelector('.spfb-card-preview-body');
        if (previewBody) {
            previewBody.addEventListener('click', () => startEdit(qid));
        }
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }


    function init(options) {
        options = options || {};
        if (typeof options.showToast === 'function') showToast = options.showToast;
        const spfbAddBtn = document.getElementById('spfbAddQuestionBtn');
        if (spfbAddBtn && !spfbAddBtn.dataset.spfbBound) {
            spfbAddBtn.dataset.spfbBound = '1';
            spfbAddBtn.addEventListener('click', addQuestion);
        }
        renderQuestionList();
    }

    function reset() {
        spfbQuestions = [];
        spfbEditingId = null;
        renderQuestionList();
    }

    function setQuestions(list) {
        spfbQuestions = JSON.parse(JSON.stringify(list || []));
        spfbEditingId = null;
        renderQuestionList();
    }

    function getQuestions() {
        if (spfbEditingId) commitEdit(spfbEditingId);
        return JSON.parse(JSON.stringify(spfbQuestions));
    }

    global.SpfbFormBuilder = {
        init,
        reset,
        setQuestions,
        getQuestions,
        renderQuestionList,
        addQuestion,  // Expose addQuestion function
    };
})(window);
