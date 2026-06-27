/**
 * Kabataan Programs — dynamic ABYIP + schedule program rendering
 */
(function () {
    'use strict';

    const ICON_CLASS = {
        education: 'education',
        environment: 'others',
        disaster: 'disaster',
        agriculture: 'agriculture',
        health: 'health',
        'anti-drugs': 'anti-drugs',
        gender: 'gender',
        feeding: 'others',
        sports: 'sports',
        others: 'others',
    };

    const MODAL_OPENERS = {
        education: 'openEducationModal',
        'anti-drugs': 'openAntiDrugsModal',
        agriculture: 'openAgricultureModal',
        disaster: 'openDisasterModal',
        sports: 'openSportsModal',
        gender: 'openGenderModal',
        health: 'openHealthModal',
        environment: 'openOthersModal',
        feeding: 'openOthersModal',
        others: 'openOthersModal',
    };

    const MODAL_IDS = {
        education: 'educationModal',
        'anti-drugs': 'antiDrugsModal',
        agriculture: 'agricultureModal',
        disaster: 'disasterModal',
        sports: 'sportsModal',
        gender: 'genderModal',
        health: 'healthModal',
        environment: 'othersModal',
        feeding: 'othersModal',
        others: 'othersModal',
    };

    const CATEGORY_SVGS = {
        education: '<path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>',
        'anti-drugs': '<path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>',
        agriculture: '<path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>',
        disaster: '<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>',
        sports: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>',
        gender: '<path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>',
        health: '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>',
        others: '<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>',
        environment: '<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>',
        feeding: '<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>',
    };

    let programsData = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatIsoDateDisplay(iso) {
        if (!iso) return '';
        const date = new Date(`${iso}T00:00:00`);
        if (Number.isNaN(date.getTime())) return String(iso);
        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    function formatPeriodRange(period, schedule) {
        if (!period) return '';
        const start = period.start_display || formatIsoDateDisplay(period.start || schedule?.start_date);
        const end = period.end_display || formatIsoDateDisplay(period.end || schedule?.end_date);
        if (start && end) return `${start} – ${end}`;
        return start || end || '';
    }

    function renderAnnouncementCard(title, bodyHtml) {
        if (!title && !bodyHtml) return '';
        return `
            <div class="sch-program-announcement-card">
                <div class="sch-program-announcement-card__title">${escapeHtml(title || 'Announcement')}</div>
                <div class="sch-program-announcement-card__body">${bodyHtml}</div>
            </div>`;
    }

    function renderScholarshipDetailsSections(schedule) {
        const details = schedule?.scholarship_details || {};
        const groups = Array.isArray(details.requirement_groups) ? details.requirement_groups : [];
        let html = '';

        if (schedule?.announcement) {
            html += renderAnnouncementCard(
                'Announcement',
                `<p class="description-text">${escapeHtml(schedule.announcement)}</p>`
            );
        }

        groups.forEach((group) => {
            const items = (group.items || []).filter((item) => String(item || '').trim());
            if (!group.title && !items.length) return;
            const listHtml = items.length
                ? `<ul class="sch-program-req-list">${items.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
                : '<p class="description-text">No requirements listed.</p>';
            html += renderAnnouncementCard(group.title || 'Requirements', listHtml);
        });

        const submissionLabel = formatPeriodRange(details.submission_period, schedule);
        const verificationLabel = formatPeriodRange(details.verification_period, schedule);

        if (submissionLabel) {
            html += `
                <div class="program-description-section sch-program-period-section">
                    <h4 class="section-heading">Period for the Submission of Requirements</h4>
                    <p class="description-text sch-program-period-value">${escapeHtml(submissionLabel)}</p>
                </div>`;
        }

        if (verificationLabel) {
            html += `
                <div class="program-description-section sch-program-period-section">
                    <h4 class="section-heading">Period for the Assessment/Verification of Scholar Profile and Requirements</h4>
                    <p class="description-text sch-program-period-value">${escapeHtml(verificationLabel)}</p>
                </div>`;
        }

        return html;
    }

    function renderSportsDetailsSections(schedule) {
        if (String(schedule?.program_letter || '').toUpperCase() !== 'I') {
            return '';
        }

        const details = schedule?.sports_details || {};
        const classifications = Array.isArray(details.age_classifications) ? details.age_classifications : [];
        const matched = schedule?.matched_classification;
        const kkAge = schedule?.kk_age;
        const maxTeam = details.max_team_members ?? 12;

        let html = '';

        if (kkAge != null) {
            html += `
                <div class="program-description-section">
                    <h4 class="section-heading">Your Age (from KK Profiling)</h4>
                    <p class="description-text">${escapeHtml(String(kkAge))} years old</p>
                </div>`;
        }

        if (matched) {
            html += `
                <div class="program-description-section">
                    <h4 class="section-heading">Your Eligible Division</h4>
                    <p class="description-text"><strong>${escapeHtml(matched.name)}</strong> (Ages ${escapeHtml(String(matched.min_age))}–${escapeHtml(String(matched.max_age))})</p>
                </div>`;
        } else if (schedule?.eligibility_message) {
            html += `
                <div class="program-description-section">
                    <h4 class="section-heading">Division Eligibility</h4>
                    <p class="description-text sch-program-slots-full">${escapeHtml(schedule.eligibility_message)}</p>
                </div>`;
        }

        if (classifications.length) {
            const items = classifications.map((item) => {
                const isOpen = details.open_all || item.is_open;
                const status = isOpen ? 'Open' : 'Closed';
                return `<li><strong>${escapeHtml(item.name)}</strong> — Ages ${escapeHtml(String(item.min_age))}–${escapeHtml(String(item.max_age))} <span class="sch-program-q-type">${status}</span></li>`;
            }).join('');

            html += `
                <div class="program-description-section">
                    <h4 class="section-heading">Age Classifications</h4>
                    <ul class="sch-program-req-list">${items}</ul>
                    <p class="description-text">Maximum ${escapeHtml(String(maxTeam))} members per team.</p>
                </div>`;
        }

        return html;
    }

    function renderScheduleInfoSections(schedule) {
        const max = schedule.participation_quantity;
        const remaining = schedule.available_slots;
        const remainingNum = remaining != null ? Number(remaining) : null;
        const remainingClass = remainingNum === 0 ? 'sch-program-slots-full' : 'sch-program-slots-remaining';

        return `
            <div class="program-description-section sch-program-info-section">
                <h4 class="section-heading">Program Information</h4>
                <div class="program-details-grid sch-program-info-grid">
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Program</span>
                            <span class="detail-value">${escapeHtml(schedule.program_name || '—')}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Max Participation Limit</span>
                            <span class="detail-value">${max != null ? escapeHtml(String(max)) : '—'}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Remaining Slots</span>
                            <span class="detail-value ${remaining != null ? remainingClass : ''}">${remaining != null ? escapeHtml(String(remaining)) : '—'}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="program-description-section sch-program-window-section">
                <h4 class="section-heading">Application Window Schedule</h4>
                <div class="program-details-grid">
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Start Date</span>
                            <span class="detail-value">${escapeHtml(schedule.start_date_display || formatIsoDateDisplay(schedule.start_date) || '—')}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">End Date</span>
                            <span class="detail-value">${escapeHtml(schedule.end_date_display || formatIsoDateDisplay(schedule.end_date) || '—')}</span>
                        </div>
                    </div>
                </div>
            </div>
            ${renderSportsDetailsSections(schedule)}
            ${renderScholarshipDetailsSections(schedule)}
            ${renderCustomQuestionsSection(schedule)}`;
    }

    function renderCustomQuestionsSection(schedule) {
        const questions = Array.isArray(schedule?.custom_questions) ? schedule.custom_questions : [];
        if (!questions.length) return '';

        const isSports = String(schedule?.program_letter || '').toUpperCase() === 'I';
        const heading = isSports ? 'Application Questions' : 'Uploading of Requirements';

        const items = questions.map((question, index) => {
            const typeLabel = question.type === 'file' ? 'PDF upload' : (question.type === 'text' ? 'Short answer' : escapeHtml(question.type || 'Question'));
            return `
            <li>
                <strong>${index + 1}. ${escapeHtml(question.label || 'Requirement')}</strong>
                ${question.required ? ' <span class="sch-program-required">*</span>' : ''}
                <span class="sch-program-q-type">${typeLabel}</span>
            </li>`;
        }).join('');

        return `
            <div class="program-description-section">
                <h4 class="section-heading">${heading}</h4>
                <ul class="sch-program-req-list sch-program-upload-list">${items}</ul>
            </div>`;
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    async function fetchPrograms() {
        if (window.__kabataanPrograms) {
            return window.__kabataanPrograms;
        }

        const response = await fetch('/api/kabataan/programs', {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to load programs');
        }

        return response.json();
    }

    function programCountLabel(program) {
        if (program.type === 'education' || program.type === 'sports') {
            const n = Number(program.schedule_count) || 0;
            if (n === 0) return 'Barangay program';
            if (n === 1) return '1 active schedule';
            return `${n} active schedules`;
        }

        if (program.survey?.can_respond) return 'Survey open';
        if (program.survey?.has_responded) return 'Survey submitted';
        if (program.has_survey || program.survey) return 'Survey available';
        return 'Barangay program';
    }

    function renderSidebarItem(program) {
        const iconClass = ICON_CLASS[program.category_key] || 'others';
        const svgPath = CATEGORY_SVGS[program.category_key] || CATEGORY_SVGS.others;
        return `
            <div class="program-category" data-category="${escapeHtml(program.category_key)}" data-letter="${escapeHtml(program.letter)}" style="cursor:pointer;">
                <div class="category-icon ${iconClass}">
                    <svg viewBox="0 0 20 20" fill="currentColor">${svgPath}</svg>
                </div>
                <div class="category-content">
                    <h3>${escapeHtml(program.title)}</h3>
                    <p>${programCountLabel(program)}</p>
                </div>
                <svg class="chevron" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        `;
    }

    function renderSidebars() {
        const containers = [
            document.getElementById('programCategoriesContainer'),
            document.getElementById('programCategoriesDrawerContainer'),
        ].filter(Boolean);

        if (!containers.length) return;

        const programs = programsData?.abyip_programs || [];
        const html = programs.length
            ? programs.map(renderSidebarItem).join('')
            : '<p style="text-align:center;color:#64748b;padding:16px;font-size:14px;">No ABYIP programs uploaded for your barangay yet.</p>';

        containers.forEach((container) => {
            container.innerHTML = html;
            container.querySelectorAll('.program-category').forEach((item) => {
                item.addEventListener('click', () => {
                    const letter = item.getAttribute('data-letter');
                    const program = programs.find((p) => p.letter === letter);
                    if (program) openProgramModal(program);
                });
            });
        });
    }

    function schedulesForAbyipProgram(program) {
        return (programsData?.schedule_programs || []).filter(
            (schedule) => schedule.program_type === program.title
        );
    }

    function openProgramModal(program) {
        const modalKey = program.modal_key || program.category_key;
        const modalId = MODAL_IDS[modalKey] || 'othersModal';
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (program.type === 'education') {
            renderEducationModalBody(program);
        } else if (program.type === 'sports') {
            renderSportsModalBody(program);
        } else {
            renderAbyipModalBody(modal, program);
        }

        const opener = MODAL_OPENERS[modalKey];
        if (opener && typeof window[opener] === 'function') {
            window[opener]();
        } else {
            modal.classList.add('active');
        }
    }

    const EDUCATION_HEADER_GRADIENT = 'linear-gradient(135deg, #1a56db 0%, #1e40af 100%)';
    const SPORTS_HEADER_GRADIENT = 'linear-gradient(135deg, #1a56db 0%, #1e40af 100%)';
    const EMPTY_SCHEDULE_MESSAGE = 'No open scheduled programs from SK Officials yet.';

    function renderProgramsEmptyState(message) {
        return `
            <div class="programs-modal-empty">
                <p>${escapeHtml(message)}</p>
            </div>`;
    }

    function renderEducationModalBody(program) {
        const container = document.getElementById('educationProgramsContainer');
        if (!container) return;

        const quickGuidelinesBtn = document.querySelector('#educationModal [data-open-sch-quick-guidelines]');
        const schedules = schedulesForAbyipProgram(program);

        if (!schedules.length) {
            container.innerHTML = renderProgramsEmptyState(EMPTY_SCHEDULE_MESSAGE);
            if (quickGuidelinesBtn) quickGuidelinesBtn.hidden = true;
            return;
        }

        if (quickGuidelinesBtn) quickGuidelinesBtn.hidden = false;
        container.innerHTML = schedules
            .map((schedule) => renderScheduleCard(program, schedule, EDUCATION_HEADER_GRADIENT, true))
            .join('');
        bindScheduleCardActions(container);
    }

    function renderSportsModalBody(program) {
        const modal = document.getElementById('sportsModal');
        const body = modal?.querySelector('.modal-body');
        if (!body) return;

        const schedules = schedulesForAbyipProgram(program);
        if (!schedules.length) {
            body.innerHTML = renderProgramsEmptyState(EMPTY_SCHEDULE_MESSAGE);
            return;
        }

        body.innerHTML = schedules
            .map((schedule) => renderScheduleCard(program, schedule, SPORTS_HEADER_GRADIENT, true))
            .join('');
        bindScheduleCardActions(body);
    }

    function renderAbyipModalBody(modal, program) {
        const body = modal?.querySelector('.modal-body');
        if (!body) return;

        const schedules = schedulesForAbyipProgram(program);
        const survey = program.survey || {};
        const hasSurveyAction = Boolean(survey.can_respond || survey.has_responded);

        if (schedules.length) {
            body.innerHTML = schedules
                .map((schedule) => renderScheduleCard(program, schedule, EDUCATION_HEADER_GRADIENT, true))
                .join('');
            bindScheduleCardActions(body);
            return;
        }

        if (hasSurveyAction) {
            body.innerHTML = renderSurveyProgramCard(program, null, EDUCATION_HEADER_GRADIENT, true);
            bindSurveyCardActions(body);
            return;
        }

        body.innerHTML = renderProgramsEmptyState(EMPTY_SCHEDULE_MESSAGE);
    }

    function renderSurveyProgramCard(program, emptyNote, gradient, hideEmoji = false) {
        const headerGradient = gradient || EDUCATION_HEADER_GRADIENT;
        const categoryTag = hideEmoji
            ? escapeHtml(program.short_label)
            : `${escapeHtml(program.emoji)} ${escapeHtml(program.short_label)}`;
        const survey = program.survey;
        const canRespond = Boolean(survey?.can_respond);
        const hasOpenSurvey = Boolean(survey?.is_open || canRespond);
        const hasResponded = Boolean(survey?.has_responded);
        const activities = (program.activities || [])
            .map((activity) => `<li>${escapeHtml(activity)}</li>`)
            .join('');

        let actionLabel = 'Survey Not Open';
        let statusLabel = 'Barangay Program';
        if (hasResponded) {
            actionLabel = 'View Response';
            statusLabel = 'Submitted';
        } else if (canRespond) {
            actionLabel = 'Apply Now';
            statusLabel = 'Survey Open';
        } else if (survey) {
            statusLabel = survey.status === 'closed' ? 'Survey Closed' : 'Survey Scheduled';
        }

        return `
            <div class="modern-program-card">
                <div class="program-card-header" style="background:${headerGradient};">
                    <div class="program-title-row">
                        <div>
                            <span class="program-category-tag">${categoryTag}</span>
                            <h3 class="program-card-title">${escapeHtml(program.title)}</h3>
                        </div>
                        <span class="program-status-badge status-active"><span class="status-dot"></span>${escapeHtml(statusLabel)}</span>
                    </div>
                </div>
                <div class="program-description-section">
                    <h4 class="section-heading">Description</h4>
                    <p class="description-text">${escapeHtml(program.description)}</p>
                </div>
                ${activities ? `<div class="program-description-section"><h4 class="section-heading">Activities</h4><ul class="terms-list">${activities}</ul></div>` : ''}
                ${survey ? `
                    <div class="program-details-grid">
                        <div class="detail-card"><div class="detail-content"><span class="detail-label">Survey Open</span><span class="detail-value">${escapeHtml(survey.open_date_display || '—')}</span></div></div>
                        <div class="detail-card"><div class="detail-content"><span class="detail-label">Survey Close</span><span class="detail-value">${escapeHtml(survey.close_date_display || '—')}</span></div></div>
                    </div>
                    <div class="program-description-section">
                        <h4 class="section-heading">Announcement</h4>
                        <p class="description-text">${escapeHtml(survey.announcement || '—')}</p>
                    </div>
                    ${survey.instructions ? `
                    <div class="program-description-section">
                        <h4 class="section-heading">Instructions</h4>
                        <p class="description-text">${escapeHtml(survey.instructions)}</p>
                    </div>` : ''}
                ` : `<p class="description-text" style="margin-top:16px;color:#64748b;">${escapeHtml(emptyNote || 'No survey from SK Officials yet.')}</p>`}
                <div class="program-action">
                    <button type="button" class="apply-now-button ${canRespond || hasResponded ? 'enabled' : ''}" data-apply-survey="${program.id}" data-has-responded="${hasResponded ? '1' : '0'}" ${!canRespond && !hasResponded ? 'disabled' : ''}>
                        ${actionLabel}
                    </button>
                </div>
            </div>
        `;
    }

    function bindSurveyCardActions(container) {
        container.querySelectorAll('[data-apply-survey]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) return;
                goToProgramSurvey(button.getAttribute('data-apply-survey'));
            });
        });
    }

    function goToProgramSurvey(programId) {
        if (!programId) return;

        Object.values(MODAL_IDS).forEach((modalId) => {
            document.getElementById(modalId)?.classList.remove('active');
        });

        if (typeof showLoading === 'function') showLoading('Opening program survey…');

        setTimeout(() => {
            window.location.href = `/programs/survey?program=${encodeURIComponent(programId)}`;
        }, 650);
    }

    function resetProgramModalMaximize(modal) {
        if (!modal) return;
        const container = modal.querySelector('.program-modal-container');
        const toggleBtn = modal.querySelector('.program-modal-toggle-btn');
        container?.classList.remove('is-maximized');
        modal.classList.remove('is-maximized');
        if (toggleBtn) {
            toggleBtn.textContent = '□';
            toggleBtn.setAttribute('aria-label', 'Maximize');
        }
    }

    function closeChromeProgramModal(modal, closeFnName) {
        resetProgramModalMaximize(modal);
        if (closeFnName && typeof window[closeFnName] === 'function') {
            window[closeFnName]();
            return;
        }
        modal.classList.remove('active');
    }

    function bindProgramModalOverlay(modal, closeFnName) {
        const overlay = modal.querySelector('.modal-overlay');
        if (!overlay || overlay.dataset.chromeBound === '1') return;

        const freshOverlay = overlay.cloneNode(true);
        overlay.replaceWith(freshOverlay);
        freshOverlay.dataset.chromeBound = '1';
        freshOverlay.addEventListener('click', () => closeChromeProgramModal(modal, closeFnName));
    }

    function initProgramModalChrome() {
        const skip = new Set(['educationModal', 'programSuccessModal']);

        document.querySelectorAll('.program-modal').forEach((modal) => {
            if (skip.has(modal.id) || modal.dataset.chromeEnhanced === '1') return;

            const container = modal.querySelector('.modal-container');
            const header = modal.querySelector('.modal-header');
            if (!container || !header || header.querySelector('.program-modal-toggle-btn')) return;

            const oldClose = header.querySelector('.modal-close');
            const closeHandler = oldClose?.getAttribute('onclick') || '';
            const closeFnName = closeHandler.replace(/\(\).*/, '').trim();
            const titleText = header.querySelector('h2')?.textContent?.trim() || 'Programs';

            container.classList.add('program-modal-container');
            if (!container.id) {
                container.id = `${modal.id}Container`;
            }

            const body = modal.querySelector('.modal-body');
            if (body) {
                body.classList.add('program-modal-body');
            }

            modal.dataset.chromeEnhanced = '1';
            modal.classList.add('program-modal--chrome');
            header.innerHTML = `
                <h2>${escapeHtml(titleText)}</h2>
                <div class="modal-header-actions">
                    <button type="button" class="modal-toggle-btn education-modal-toggle-btn program-modal-toggle-btn" id="${escapeHtml(modal.id)}Maximize" aria-label="Maximize">□</button>
                    <button type="button" class="modal-close education-modal-close-btn program-modal-close-btn" aria-label="Close">&times;</button>
                </div>`;

            const toggleBtn = header.querySelector('.program-modal-toggle-btn');
            const closeBtn = header.querySelector('.program-modal-close-btn');

            closeBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                closeChromeProgramModal(modal, closeFnName);
            });

            toggleBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                const isMax = !container.classList.contains('is-maximized');
                container.classList.toggle('is-maximized', isMax);
                modal.classList.toggle('is-maximized', isMax);
                toggleBtn.textContent = isMax ? '⧉' : '□';
                toggleBtn.setAttribute('aria-label', isMax ? 'Restore down' : 'Maximize');
            });

            bindProgramModalOverlay(modal, closeFnName);
        });

        wrapProgramModalCloseHandlers();
    }

    const PROGRAM_MODAL_CLOSE_MAP = {
        closeAntiDrugsModal: 'antiDrugsModal',
        closeAgricultureModal: 'agricultureModal',
        closeDisasterModal: 'disasterModal',
        closeSportsModal: 'sportsModal',
        closeGenderModal: 'genderModal',
        closeHealthModal: 'healthModal',
        closeOthersModal: 'othersModal',
    };

    function wrapProgramModalCloseHandlers() {
        Object.entries(PROGRAM_MODAL_CLOSE_MAP).forEach(([closeFn, modalId]) => {
            if (window[`__${closeFn}Wrapped`]) return;

            const original = window[closeFn];
            if (typeof original !== 'function') return;

            window[closeFn] = function (...args) {
                resetProgramModalMaximize(document.getElementById(modalId));
                return original.apply(this, args);
            };
            window[`__${closeFn}Wrapped`] = true;
        });
    }

    function renderAbyipOnlyCard(program, emptyNote, gradient) {
        const headerGradient = gradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        const activities = (program.activities || [])
            .map((activity) => `<li>${escapeHtml(activity)}</li>`)
            .join('');

        return `
            <div class="modern-program-card">
                <div class="program-card-header" style="background:${headerGradient};">
                    <div class="program-title-row">
                        <div>
                            <span class="program-category-tag">${escapeHtml(program.emoji)} ${escapeHtml(program.short_label)}</span>
                            <h3 class="program-card-title">${escapeHtml(program.title)}</h3>
                        </div>
                        <span class="program-status-badge status-active"><span class="status-dot"></span>ABYIP Program</span>
                    </div>
                </div>
                <div class="program-description-section">
                    <h4 class="section-heading">Description</h4>
                    <p class="description-text">${escapeHtml(program.description)}</p>
                </div>
                ${activities ? `<div class="program-description-section"><h4 class="section-heading">Activities</h4><ul class="terms-list">${activities}</ul></div>` : ''}
                ${emptyNote ? `<p class="description-text" style="margin-top:16px;color:#64748b;">${escapeHtml(emptyNote)}</p>` : ''}
            </div>
        `;
    }

    function isScholarshipSchedule(schedule) {
        return String(schedule?.program_letter || '').toUpperCase() !== 'I';
    }

    function renderScheduleCard(abyipProgram, schedule, gradient, hideEmoji = false) {
        const headerGradient = gradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        const statusLabel = schedule.status === 'open' ? 'Open' : 'Closed';
        const applied = schedule.has_applied;
        const canApply = schedule.can_apply !== false;
        const openAndEligible = !applied && schedule.status === 'open' && canApply;
        const scholarship = isScholarshipSchedule(schedule);
        const categoryTag = hideEmoji
            ? escapeHtml(abyipProgram.short_label)
            : `${escapeHtml(abyipProgram.emoji)} ${escapeHtml(abyipProgram.short_label)}`;
        const statusBadge = applied
            ? `<span class="program-status-badge status-active">Applied — ${escapeHtml((schedule.application_status || 'pending').charAt(0).toUpperCase() + (schedule.application_status || 'pending').slice(1))}</span>`
            : `<span class="program-status-badge status-active"><span class="status-dot"></span>${escapeHtml(statusLabel)}</span>`;

        let actionButton = '';
        if (applied) {
            const viewLabel = scholarship ? 'View My Scholar Application Form' : 'View My Application';
            actionButton = `
                    <button type="button" class="apply-now-button enabled apply-view-btn" data-view-schedule-application="${schedule.id}" data-program-letter="${schedule.program_letter || ''}">
                        ${viewLabel}
                    </button>`;
        } else {
            actionButton = `
                    <button type="button" class="apply-now-button ${openAndEligible ? 'enabled' : ''}" data-apply-schedule="${schedule.id}" data-program-letter="${schedule.program_letter || ''}" title="${!canApply ? escapeHtml(schedule.eligibility_message || 'Not eligible for this program') : ''}" ${schedule.status !== 'open' || !canApply ? 'disabled' : ''}>
                        ${!canApply ? 'Not Eligible' : 'Apply Now'}
                    </button>`;
        }

        return `
            <div class="modern-program-card" data-schedule-id="${schedule.id}" style="margin-bottom:24px;">
                <div class="program-card-header" style="background:${headerGradient};">
                    <div class="program-title-row">
                        <div>
                            <span class="program-category-tag">${categoryTag}</span>
                            <h3 class="program-card-title">${escapeHtml(schedule.program_name)}</h3>
                        </div>
                        ${statusBadge}
                    </div>
                </div>
                ${renderScheduleInfoSections(schedule)}
                <div class="program-action">
                    ${actionButton}
                </div>
            </div>
        `;
    }

    function requestScholarshipApply(scheduleId, programLetter) {
        const proceed = () => goToScheduleApplication(scheduleId, programLetter);

        if (isScholarshipSchedule({ program_letter: programLetter }) && window.ScholarshipDataPrivacy) {
            window.ScholarshipDataPrivacy.requestConsent(scheduleId, proceed);
            return;
        }

        proceed();
    }

    function bindScheduleCardActions(container) {
        container.querySelectorAll('[data-view-schedule-application]').forEach((button) => {
            button.addEventListener('click', () => {
                goToScheduleApplication(
                    button.getAttribute('data-view-schedule-application'),
                    button.getAttribute('data-program-letter')
                );
            });
        });

        container.querySelectorAll('[data-apply-schedule]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) return;
                requestScholarshipApply(
                    button.getAttribute('data-apply-schedule'),
                    button.getAttribute('data-program-letter')
                );
            });
        });

        if (window.ScholarshipQuickGuidelines) {
            window.ScholarshipQuickGuidelines.bindTriggers(container);
        }
    }

    function goToScheduleApplication(scheduleId, programLetter) {
        if (typeof closeEducationModal === 'function') closeEducationModal();
        if (typeof closeSportsModal === 'function') closeSportsModal();
        if (typeof showLoading === 'function') showLoading('Redirecting to application…');

        const letter = String(programLetter || '').toUpperCase();
        const basePath = letter === 'I' ? '/sports/apply' : '/scholarship/apply';
        const url = `${basePath}?schedule=${encodeURIComponent(scheduleId)}`;
        setTimeout(() => {
            window.location.href = url;
        }, 650);
    }

    async function init() {
        initProgramModalChrome();

        try {
            programsData = await fetchPrograms();
        } catch (error) {
            console.error(error);
            programsData = window.__kabataanPrograms || { abyip_programs: [], schedule_programs: [] };
        }

        renderSidebars();
        enableSurveyApplyButtons();

        const educationProgram = (programsData?.abyip_programs || []).find((program) => program.type === 'education');
        if (educationProgram) {
            renderEducationModalBody(educationProgram);
        }

        if (window.programsModule) {
            window.programsModule.fetchCategories = async () => programsData?.abyip_programs || [];
            window.programsModule.fetchByCategory = async (categoryId) => {
                const program = (programsData?.abyip_programs || []).find((p) => p.category_key === categoryId);
                return program || null;
            };
        }
    }

    function enableSurveyApplyButtons() {
        const buttonMap = {
            'anti-drugs': 'applyNowBtnAntiDrugs',
            agriculture: 'applyNowBtnAgriculture',
            disaster: 'applyNowBtnDisaster',
            gender: 'applyNowBtnGender',
            health: 'applyNowBtnHealth',
            others: 'applyNowBtnOthers',
            environment: 'applyNowBtnOthers',
            feeding: 'applyNowBtnOthers',
        };

        (programsData?.abyip_programs || []).forEach((program) => {
            if (program.type === 'education' || program.type === 'sports') return;
            const survey = program.survey || {};
            if (!survey.can_respond && !survey.has_responded) return;

            const btnId = buttonMap[program.category_key] || buttonMap[program.modal_key];
            const btn = btnId ? document.getElementById(btnId) : null;
            if (!btn) return;

            btn.disabled = false;
            btn.classList.add('enabled');
            btn.onclick = () => goToProgramSurvey(program.id);
        });
    }

    window.goToProgramSurvey = goToProgramSurvey;

    window.kabataanPrograms = {
        init,
        getData: () => programsData,
        schedulesForAbyipProgram,
        goToScheduleApplication,
        goToProgramSurvey,
        renderSidebarItem,
        escapeHtml,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
