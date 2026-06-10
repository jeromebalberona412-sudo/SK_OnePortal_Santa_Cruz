function escapeParticipationHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function isDocumentAnswer(answer) {
    return answer && typeof answer === 'object' && !Array.isArray(answer)
        && (answer.original_name || answer.preview_url || answer.download_url || answer.path);
}

function formatParticipationAnswer(answer, questionType) {
    if (answer === null || answer === undefined || answer === '') return '—';
    if (questionType === 'file' || isDocumentAnswer(answer)) {
        return String(answer.original_name || 'Uploaded document');
    }
    if (Array.isArray(answer)) return answer.join(', ');
    if (typeof answer === 'object') {
        if (answer.original_name) return String(answer.original_name);
        return '—';
    }
    return String(answer);
}

function renderParticipationDocumentCard(answer, label) {
    const file = answer && typeof answer === 'object' ? answer : {};
    const previewUrl = file.preview_url || file.download_url || '#';
    const downloadUrl = file.download_url || previewUrl;
    const fileName = file.original_name || 'Uploaded PDF';
    const meta = [file.size_display, file.uploaded_at_display].filter(Boolean).join(' • ');

    return `
        <div class="participation-document-card">
            <div class="participation-document-icon" aria-hidden="true">PDF</div>
            <div class="participation-document-body">
                <div class="participation-document-name">${escapeParticipationHtml(fileName)}</div>
                ${meta ? `<div class="participation-document-meta">${escapeParticipationHtml(meta)}</div>` : ''}
                <div class="participation-document-actions">
                    <a href="${escapeParticipationHtml(previewUrl)}" target="_blank" rel="noopener" class="participation-document-link">Preview</a>
                    <a href="${escapeParticipationHtml(downloadUrl)}" target="_blank" rel="noopener" class="participation-document-link">Download</a>
                </div>
            </div>
        </div>
    `;
}

function renderParticipationAnswerItem(item, index) {
    const label = item.question_label || item.label || 'Question';
    const questionType = item.question_type || '';
    const answer = item.answer;

    if (questionType === 'file' || isDocumentAnswer(answer)) {
        return `
            <div class="participation-answer-item participation-answer-item--file">
                <div class="participation-answer-q">${index + 1}. ${escapeParticipationHtml(label)}</div>
                ${renderParticipationDocumentCard(answer, label)}
            </div>
        `;
    }

    return `
        <div class="participation-answer-item">
            <div class="participation-answer-q">${index + 1}. ${escapeParticipationHtml(label)}</div>
            <div class="participation-answer-a">${escapeParticipationHtml(formatParticipationAnswer(answer, questionType))}</div>
        </div>
    `;
}

function setProgramDetailsModalMaximized(isMaximized) {
    const modal = document.getElementById('programDetailsModal');
    const box = document.getElementById('programDetailsModalBox');
    const maxBtn = document.getElementById('programDetailsMaximize');
    if (!modal || !box) return;

    box.classList.toggle('is-maximized', isMaximized);
    modal.classList.toggle('is-maximized', isMaximized);
    if (maxBtn) {
        maxBtn.textContent = isMaximized ? '⧉' : '□';
        maxBtn.title = isMaximized ? 'Restore Down' : 'Maximize';
    }
}

window.viewProgramDetails = function viewProgramDetails(programKey) {
    const detail = window.__participationDetails?.[programKey];
    if (!detail) {
        alert('Participation details not found.');
        return;
    }

    const isSurvey = String(programKey).startsWith('survey-');
    const title = isSurvey
        ? (detail.program_name || 'Survey Response')
        : (detail.program_name || 'Program Application');

    const modalTitle = document.getElementById('programModalTitle');
    const modalContent = document.getElementById('programDetailsContent');
    const modal = document.getElementById('programDetailsModal');

    if (!modalTitle || !modalContent || !modal) return;

    modalTitle.textContent = title;
    setProgramDetailsModalMaximized(false);

    const answers = detail.answers || [];
    let answersHtml = '';

    if (answers.length) {
        answersHtml = `
            <div class="program-description-section">
                <h4 class="section-heading">Your Responses</h4>
                <div class="participation-answers-list">
                    ${answers.map((item, index) => renderParticipationAnswerItem(item, index)).join('')}
                </div>
            </div>`;
    } else {
        answersHtml = `
            <div class="program-description-section">
                <p class="description-text">No detailed answers were recorded for this participation entry.</p>
            </div>`;
    }

    const metaRows = isSurvey
        ? [
            ['Submitted', detail.submitted_at || '—'],
            ['Survey Period', detail.survey_period || '—'],
        ]
        : [
            ['Status', detail.status_display || detail.status || '—'],
            ['Submitted', detail.submitted_at || '—'],
            ['Program Period', detail.program_period || '—'],
        ];

    modalContent.innerHTML = `
        <div class="program-card-header" style="background: linear-gradient(135deg, #0450a8 0%, #1a6fd4 100%);">
            <div class="program-title-row">
                <div>
                    <span class="program-category-tag">${isSurvey ? 'Survey Response' : 'Program Application'}</span>
                    <h3 class="program-card-title">${escapeParticipationHtml(title)}</h3>
                </div>
            </div>
        </div>
        <div class="program-details-grid">
            ${metaRows.map(([label, value]) => `
                <div class="detail-card">
                    <div class="detail-content">
                        <span class="detail-label">${escapeParticipationHtml(label)}</span>
                        <span class="detail-value">${escapeParticipationHtml(value)}</span>
                    </div>
                </div>
            `).join('')}
        </div>
        ${answersHtml}
    `;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeProgramDetailsModal = function closeProgramDetailsModal() {
    const modal = document.getElementById('programDetailsModal');
    if (!modal) return;
    setProgramDetailsModalMaximized(false);
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
};

document.addEventListener('DOMContentLoaded', () => {
    const maxBtn = document.getElementById('programDetailsMaximize');
    if (maxBtn) {
        maxBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            const box = document.getElementById('programDetailsModalBox');
            const isMaximized = box?.classList.contains('is-maximized');
            setProgramDetailsModalMaximized(!isMaximized);
        });
    }
});

document.addEventListener('click', (event) => {
    if (event.target.classList.contains('modal-overlay')) {
        window.closeProgramDetailsModal?.();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        window.closeProgramDetailsModal?.();
    }
});
