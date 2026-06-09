function escapeParticipationHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatParticipationAnswer(answer) {
    if (answer === null || answer === undefined || answer === '') return '—';
    if (Array.isArray(answer)) return answer.join(', ');
    if (typeof answer === 'object') return JSON.stringify(answer);
    return String(answer);
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

    const answers = detail.answers || [];
    let answersHtml = '';

    if (answers.length) {
        answersHtml = `
            <div class="program-description-section">
                <h4 class="section-heading">Your Responses</h4>
                <div class="participation-answers-list">
                    ${answers.map((item, index) => `
                        <div class="participation-answer-item">
                            <div class="participation-answer-q">${index + 1}. ${escapeParticipationHtml(item.question_label || item.label || 'Question')}</div>
                            <div class="participation-answer-a">${escapeParticipationHtml(formatParticipationAnswer(item.answer))}</div>
                        </div>
                    `).join('')}
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
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
};

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
