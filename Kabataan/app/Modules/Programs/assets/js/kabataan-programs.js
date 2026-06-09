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

    function programCountLabel(count) {
        const n = Number(count) || 0;
        if (n === 0) return 'No active programs';
        if (n === 1) return '1 active program';
        return `${n} active programs`;
    }

    function renderSidebarItem(program) {
        const iconClass = ICON_CLASS[program.category_key] || 'others';
        const svgPath = CATEGORY_SVGS[program.category_key] || CATEGORY_SVGS.others;
        const count = program.type === 'education' ? program.schedule_count : program.active_count;

        return `
            <div class="program-category" data-category="${escapeHtml(program.category_key)}" data-letter="${escapeHtml(program.letter)}" style="cursor:pointer;">
                <div class="category-icon ${iconClass}">
                    <svg viewBox="0 0 20 20" fill="currentColor">${svgPath}</svg>
                </div>
                <div class="category-content">
                    <h3>${escapeHtml(program.title)}</h3>
                    <p>${programCountLabel(count)}</p>
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
            : '<p style="text-align:center;color:#64748b;padding:16px;font-size:14px;">No ABYIP programs available for your barangay yet.</p>';

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

    function renderEducationModalBody(program) {
        const container = document.getElementById('educationProgramsContainer');
        if (!container) return;

        const schedules = schedulesForAbyipProgram(program);
        const cards = schedules.length
            ? schedules.map((schedule) => renderScheduleCard(program, schedule)).join('')
            : renderAbyipOnlyCard(program, 'No open scheduled programs from SK Officials yet.');

        container.innerHTML = cards || '<p style="text-align:center;color:#64748b;padding:24px;">No programs available.</p>';
        bindScheduleCardActions(container);
    }

    function renderSportsModalBody(program) {
        const modal = document.getElementById('sportsModal');
        const body = modal?.querySelector('.modal-body');
        if (!body) return;

        const schedules = schedulesForAbyipProgram(program);
        body.innerHTML = schedules.length
            ? schedules.map((schedule) => renderScheduleCard(program, schedule, '#ea580c')).join('')
            : renderAbyipOnlyCard(program, 'No open sports programs scheduled yet.', '#ea580c');

        bindScheduleCardActions(body);
    }

    function renderAbyipModalBody(modal, program) {
        const body = modal?.querySelector('.modal-body');
        if (!body) return;
        body.innerHTML = renderSurveyProgramCard(program);
        bindSurveyCardActions(body);
    }

    function renderSurveyProgramCard(program, emptyNote, gradient) {
        const headerGradient = gradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        const survey = program.survey;
        const hasOpenSurvey = Boolean(survey?.is_open);
        const hasResponded = Boolean(survey?.has_responded);
        const activities = (program.activities || [])
            .map((activity) => `<li>${escapeHtml(activity)}</li>`)
            .join('');

        let actionLabel = 'Survey Not Open';
        if (hasResponded) actionLabel = 'Already Submitted';
        else if (hasOpenSurvey) actionLabel = 'Apply Now';

        return `
            <div class="modern-program-card">
                <div class="program-card-header" style="background:${headerGradient};">
                    <div class="program-title-row">
                        <div>
                            <span class="program-category-tag">${escapeHtml(program.emoji)} ${escapeHtml(program.short_label)}</span>
                            <h3 class="program-card-title">${escapeHtml(program.title)}</h3>
                        </div>
                        <span class="program-status-badge status-active"><span class="status-dot"></span>${hasOpenSurvey ? 'Survey Open' : 'ABYIP Program'}</span>
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
                ` : `<p class="description-text" style="margin-top:16px;color:#64748b;">${escapeHtml(emptyNote || 'No open survey from SK Officials yet.')}</p>`}
                <div class="program-action">
                    <button type="button" class="apply-now-button ${hasOpenSurvey && !hasResponded ? 'enabled' : ''}" data-apply-survey="${program.id}" ${!hasOpenSurvey || hasResponded ? 'disabled' : ''}>
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

    function renderScheduleCard(abyipProgram, schedule, gradient) {
        const headerGradient = gradient || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        const statusLabel = schedule.status === 'open' ? 'Open' : 'Closed';
        const applied = schedule.has_applied;
        const statusBadge = applied
            ? `<span class="program-status-badge status-active">Applied — ${escapeHtml((schedule.application_status || 'pending').charAt(0).toUpperCase() + (schedule.application_status || 'pending').slice(1))}</span>`
            : `<span class="program-status-badge status-active"><span class="status-dot"></span>${escapeHtml(statusLabel)}</span>`;

        const agreeId = `agreeTermsSchedule${schedule.id}`;
        const termsId = `termsContentSchedule${schedule.id}`;
        const toggleId = `termsToggleSchedule${schedule.id}`;

        return `
            <div class="modern-program-card" data-schedule-id="${schedule.id}" style="margin-bottom:24px;">
                <div class="program-card-header" style="background:${headerGradient};">
                    <div class="program-title-row">
                        <div>
                            <span class="program-category-tag">${escapeHtml(abyipProgram.emoji)} ${escapeHtml(abyipProgram.short_label)}</span>
                            <h3 class="program-card-title">${escapeHtml(schedule.program_name)}</h3>
                        </div>
                        ${statusBadge}
                    </div>
                </div>
                <div class="program-details-grid">
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Committee</span>
                            <span class="detail-value">${escapeHtml(schedule.committee)}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Participation Limit</span>
                            <span class="detail-value">${schedule.available_slots ?? schedule.participation_quantity ? escapeHtml(String(schedule.available_slots ?? schedule.participation_quantity)) : '—'}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">Start Date</span>
                            <span class="detail-value">${escapeHtml(schedule.start_date_display)}</span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="detail-content">
                            <span class="detail-label">End Date</span>
                            <span class="detail-value">${escapeHtml(schedule.end_date_display)}</span>
                        </div>
                    </div>
                </div>
                ${schedule.announcement ? `
                    <div class="program-description-section">
                        <h4 class="section-heading">Announcement</h4>
                        <p class="description-text">${escapeHtml(schedule.announcement)}</p>
                    </div>
                ` : ''}
                <div class="terms-section">
                    <button class="terms-toggle" type="button" id="${toggleId}">
                        <div class="terms-toggle-header">
                            <h4 class="section-heading">Terms & Conditions</h4>
                            <svg class="chevron-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </button>
                    <div class="terms-content" id="${termsId}">
                        <ul class="terms-list">
                            <li>You must be a registered Kabataan member of this barangay.</li>
                            <li>All information submitted must be accurate and complete.</li>
                            <li>False information may result in disqualification.</li>
                        </ul>
                        <div class="terms-agreement">
                            <label class="agreement-checkbox">
                                <input type="checkbox" id="${agreeId}" data-schedule-id="${schedule.id}">
                                <span class="checkbox-custom"></span>
                                <span class="agreement-text">I have read and agree to the Terms & Conditions</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="program-action">
                    <button class="apply-now-button" data-apply-schedule="${schedule.id}" disabled>
                        ${applied ? 'Already Applied' : 'Apply Now'}
                    </button>
                    <p class="apply-note">Please read and agree to the Terms & Conditions to continue</p>
                </div>
            </div>
        `;
    }

    function bindScheduleCardActions(container) {
        container.querySelectorAll('.terms-toggle').forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const content = toggle.nextElementSibling;
                const chevron = toggle.querySelector('.chevron-icon');
                if (!content) return;
                content.classList.toggle('expanded');
                if (chevron) {
                    chevron.style.transform = content.classList.contains('expanded') ? 'rotate(180deg)' : 'rotate(0deg)';
                }
            });
        });

        container.querySelectorAll('input[type="checkbox"][data-schedule-id]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const scheduleId = checkbox.getAttribute('data-schedule-id');
                const card = checkbox.closest('.modern-program-card');
                const applyBtn = card?.querySelector(`[data-apply-schedule="${scheduleId}"]`);
                const note = applyBtn?.nextElementSibling;
                if (!applyBtn || applyBtn.textContent === 'Already Applied') return;

                applyBtn.disabled = !checkbox.checked;
                applyBtn.classList.toggle('enabled', checkbox.checked);
                if (note) note.style.display = checkbox.checked ? 'none' : '';
            });
        });

        container.querySelectorAll('[data-apply-schedule]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled || button.textContent === 'Already Applied') return;
                const scheduleId = button.getAttribute('data-apply-schedule');
                goToScheduleApplication(scheduleId);
            });
        });
    }

    function goToScheduleApplication(scheduleId) {
        if (typeof closeEducationModal === 'function') closeEducationModal();
        if (typeof closeSportsModal === 'function') closeSportsModal();
        if (typeof showLoading === 'function') showLoading('Redirecting to application…');

        const url = `/scholarship/apply?schedule=${encodeURIComponent(scheduleId)}`;
        setTimeout(() => {
            window.location.href = url;
        }, 650);
    }

    async function init() {
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
            if (!program.survey?.is_open || program.survey?.has_responded) return;

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
