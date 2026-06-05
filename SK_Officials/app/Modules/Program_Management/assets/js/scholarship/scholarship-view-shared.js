/**
 * Shared helpers: scholarship program info + Google Form-style Q&A in view modals
 */
(function (global) {
    const SAF_STORAGE_KEY = 'scholar_application_forms';
    const PROGRAM_SEED_KEY = 'scholar_application_forms_seeded_v1';

    const SAMPLE_SCHOLARSHIP_PROGRAM = {
        id: 'saf_sample_education_2026',
        programName: '150 Students for Educational Assistance',
        programType: 'Equitable Access to Quality Education',
        committee: 'Education Committee',
        participationQty: '150',
        venue: 'SK Santa Cruz Youth Center, Brgy. Calios, Santa Cruz, Laguna',
        description: 'Educational assistance for qualified Kabataan members enrolled in public or private schools within Santa Cruz, Laguna. Funds may cover tuition, books, and related school expenses based on committee evaluation.',
        terms: 'Applicants must be registered KK members, ages 15–30, with proof of enrollment. Incomplete or invalid documents will not be processed. The SK Education Committee reserves the right to approve or reject applications.',
        startDate: '2026-01-15',
        endDate: '2026-06-30',
        startTime: '08:00',
        endTime: '17:00',
        status: 'open',
        announcement: 'Welcome! Please read all instructions before applying. Prepare your COR (certified true copy), valid ID, and complete all required questions below. Applications are reviewed by the Education Committee.',
        customQuestions: [
            {
                id: 'q_schol_why',
                label: 'Why do you need this scholarship assistance?',
                type: 'paragraph',
                required: true,
            },
            {
                id: 'q_schol_gpa',
                label: 'What is your current GPA or general average?',
                type: 'text',
                required: true,
            },
            {
                id: 'q_schol_income',
                label: 'Are you currently employed or receiving other income?',
                type: 'radio',
                options: ['Yes', 'No'],
                required: true,
            },
            {
                id: 'q_schol_purpose',
                label: 'Purpose of assistance (select all that apply)',
                type: 'checkbox',
                options: ['Tuition Fees', 'Books / Equipments', 'Living Expenses', 'Others'],
                required: false,
            },
            {
                id: 'q_schol_household',
                label: 'How many dependents are in your household?',
                type: 'number',
                required: false,
            },
        ],
        createdAt: '2026-01-10T08:00:00.000Z',
    };

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatTime12(time24) {
        if (!time24) return '—';
        const parts = String(time24).split(':');
        if (parts.length < 2) return escapeHtml(time24);
        const h = parseInt(parts[0], 10);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${m} ${ampm}`;
    }

    function formatStatusLabel(status) {
        if (status === 'open' || status === 'Open') return 'Open';
        if (status === 'closed' || status === 'Closed') return 'Closed';
        return 'Open';
    }

    function formatDisplayDate(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return escapeHtml(iso);
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function loadPrograms() {
        try {
            return JSON.parse(localStorage.getItem(SAF_STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function loadScholarshipProgram() {
        const forms = loadPrograms();
        if (forms.length) return forms[0];
        return SAMPLE_SCHOLARSHIP_PROGRAM;
    }

    function seedScholarshipProgramIfNeeded() {
        if (localStorage.getItem(PROGRAM_SEED_KEY)) return;
        const forms = loadPrograms();
        if (!forms.length) {
            localStorage.setItem(SAF_STORAGE_KEY, JSON.stringify([SAMPLE_SCHOLARSHIP_PROGRAM]));
        }
        localStorage.setItem(PROGRAM_SEED_KEY, '1');
    }

    function questionLabel(q) {
        return q.label || q.question || 'Question';
    }

    function getDefaultFormAnswers(record) {
        const name = record?.first_name || 'Applicant';
        const purpose = Array.isArray(record?.purpose_list) ? record.purpose_list.join(', ') : (record?.purpose || 'Tuition Fees');
        return [
            {
                question: 'Why do you need this scholarship assistance?',
                answer: `I am applying because my family needs support to continue my studies. ${name} is committed to finishing the current school year with the committee's help.`,
            },
            {
                question: 'What is your current GPA or general average?',
                answer: record?.gpa || '1.75 (Good Standing)',
            },
            {
                question: 'Are you currently employed or receiving other income?',
                answer: record?.employed === true ? 'Yes' : 'No',
            },
            {
                question: 'Purpose of assistance (select all that apply)',
                answer: purpose,
            },
            {
                question: 'How many dependents are in your household?',
                answer: String(record?.household_dependents ?? '4'),
            },
        ];
    }

    function resolveFormAnswers(record, program) {
        if (Array.isArray(record?.form_answers) && record.form_answers.length) {
            return record.form_answers;
        }
        const programQuestions = program?.customQuestions || [];
        if (programQuestions.length) {
            const defaults = getDefaultFormAnswers(record);
            return programQuestions.map((q, i) => ({
                question: questionLabel(q),
                answer: defaults[i]?.answer || '—',
            }));
        }
        return getDefaultFormAnswers(record);
    }

    function renderProgramInformationSection(program) {
        const p = program || SAMPLE_SCHOLARSHIP_PROGRAM;
        const status = formatStatusLabel(p.status);
        const statusStyle = status === 'Open' || status === 'open' 
            ? { bg: '#dcfce7', text: '#166534', label: 'Open' }
            : { bg: '#fee2e2', text: '#991b1b', label: 'Closed' };

        return `
            <div style="padding:24px;background:#f0f1f5;">
                <!-- Program Information Section -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Program Information
                    </h4>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program Name <span style="color:#dc2626;">*</span></label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${escapeHtml(p.programName)}</div>
                            <div style="font-size:12px;color:#6b7280;margin-top:6px;text-align:right;">${(p.programName || '').length}/200 characters</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Program Type</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${escapeHtml(p.programType)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Committee</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${escapeHtml(p.committee)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Participation Quantity</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${escapeHtml(p.participationQty || 'N/A')}</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Venue</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:50px;">${escapeHtml(p.venue || 'Not specified')}</div>
                            <div style="font-size:12px;color:#6b7280;margin-top:6px;text-align:right;">${(p.venue || '').length}/500 characters</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Description</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:80px;white-space:pre-wrap;">${escapeHtml(p.description || 'Not specified')}</div>
                            <div style="font-size:12px;color:#6b7280;margin-top:6px;text-align:right;">${(p.description || '').length}/500 characters</div>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Terms and Conditions</label>
                            <div style="font-size:15px;color:#374151;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);min-height:100px;white-space:pre-wrap;">${escapeHtml(p.terms || 'Not specified')}</div>
                            <div style="font-size:12px;color:#6b7280;margin-top:6px;text-align:right;">${(p.terms || '').length}/500 characters</div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Section -->
                <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 20px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Application Window Schedule
                    </h4>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Start Date <span style="color:#dc2626;">*</span></label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${formatDisplayDate(p.startDate)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Start Time</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${formatTime12(p.startTime)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">End Date <span style="color:#dc2626;">*</span></label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${formatDisplayDate(p.endDate)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">End Time</label>
                            <div style="font-size:15px;font-weight:600;color:#111827;padding:12px 16px;background:#fff;border-radius:8px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">${formatTime12(p.endTime)}</div>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Status</label>
                            <span style="display:inline-flex;align-items:center;padding:8px 20px;border-radius:999px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;background:${statusStyle.bg};color:${statusStyle.text};box-shadow:0 1px 2px rgba(0,0,0,0.1);">${statusStyle.label}</span>
                        </div>
                    </div>
                </div>

                <!-- Application Form Builder Section -->
                <div style="background:white;border-radius:12px;padding:24px;border:2px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Application Form Builder
                    </h4>
                    
                    <!-- Announcement Section -->
                    <div style="background:#fff;border-radius:8px;padding:20px;margin-bottom:20px;border:2px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Announcement <span style="color:#dc2626;">*</span></label>
                        <div style="font-size:13px;color:#6b7280;margin-bottom:12px;">This message will be shown to Kabataan members when they open the application form.</div>
                        <div style="font-size:15px;color:#374151;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;min-height:80px;white-space:pre-wrap;">${escapeHtml(p.announcement || 'No announcement set')}</div>
                    </div>
                </div>
            </div>`;
    }

    function renderFormAnswersSection(record, program) {
        const answers = resolveFormAnswers(record, program);
        const programName = program?.programName || 'Scholarship Application';

        if (!answers.length) {
            return `
                <div style="background:#fff3cd;border:2px solid #ffc107;border-radius:12px;padding:24px;text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" style="margin-bottom:12px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div style="font-size:16px;color:#856404;font-weight:600;">No custom questions added</div>
                    <div style="font-size:14px;color:#856404;margin-top:8px;">Applicants will use the Kabataan application form with their profile details.</div>
                </div>`;
        }

        return `
            <div style="background:#f8f9fa;border-radius:12px;padding:24px;border:2px solid #e5e7eb;">
                <!-- Form Header -->
                <div style="background:#673ab7;color:white;padding:24px;border-radius:12px 12px 0 0;margin:-24px -24px 24px;">
                    <h5 style="font-size:26px;font-weight:500;margin:0 0 8px;">${escapeHtml(programName)}</h5>
                    <p style="font-size:14px;margin:0;opacity:0.95;">Application Form Questions</p>
                </div>

                ${answers.map((item, idx) => `
                    <div style="background:white;border-radius:8px;padding:24px;margin-bottom:20px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                        <div style="font-size:15px;color:#202124;font-weight:500;margin-bottom:10px;">
                            ${idx + 1}. ${escapeHtml(item.question)}
                        </div>
                        <div style="font-size:14px;color:#111827;line-height:1.6;padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #673ab7;">
                            ${escapeHtml(item.answer || '—')}
                        </div>
                    </div>
                `).join('')}
            </div>`;
    }

    global.ScholarshipViewShared = {
        SAF_STORAGE_KEY,
        SAMPLE_SCHOLARSHIP_PROGRAM,
        escapeHtml,
        formatTime12,
        loadScholarshipProgram,
        seedScholarshipProgramIfNeeded,
        getDefaultFormAnswers,
        resolveFormAnswers,
        renderProgramInformationSection,
        renderFormAnswersSection,
    };
})(window);
