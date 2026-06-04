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
        const statusBg = status === 'Open' ? '#dcfce7' : '#fee2e2';
        const statusColor = status === 'Open' ? '#166534' : '#991b1b';

        return `
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 20px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;">Program Information</h3>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    <div style="grid-column:1/-1;">
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Program Name</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(p.programName)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Program Type</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(p.programType)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Committee</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(p.committee)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Participation Quantity</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(p.participationQty || '—')}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Venue</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${escapeHtml(p.venue || '—')}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Description</div>
                        <div style="font-size:14px;color:#374151;line-height:1.7;">${escapeHtml(p.description || '—')}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Terms and Conditions</div>
                        <div style="font-size:14px;color:#374151;line-height:1.7;white-space:pre-wrap;">${escapeHtml(p.terms || '—')}</div>
                    </div>
                </div>

                <h4 style="font-size:15px;font-weight:700;color:#111827;margin:24px 0 12px;">Application Window Schedule</h4>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Start Date</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${formatDisplayDate(p.startDate)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Start Time</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${formatTime12(p.startTime)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">End Date</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${formatDisplayDate(p.endDate)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">End Time</div>
                        <div style="font-size:15px;font-weight:600;color:#111827;">${formatTime12(p.endTime)}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Status</div>
                        <span style="display:inline-flex;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:700;background:${statusBg};color:${statusColor};">${status}</span>
                    </div>
                </div>

                <h4 style="font-size:15px;font-weight:700;color:#111827;margin:24px 0 12px;">Application Form Builder</h4>
                <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Announcement</div>
                <div style="font-size:14px;color:#374151;line-height:1.7;padding:14px;background:#f0f9ff;border-radius:8px;border:1px solid #bae6fd;white-space:pre-wrap;">${escapeHtml(p.announcement || '—')}</div>
            </div>`;
    }

    function renderFormAnswersSection(record, program) {
        const answers = resolveFormAnswers(record, program);
        const programName = program?.programName || 'Scholarship Application';

        if (!answers.length) {
            return '';
        }

        return `
            <div style="background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="font-size:18px;font-weight:700;color:#111827;margin:0 0 16px;padding-bottom:12px;border-bottom:2px solid #e5e7eb;">Application Form Responses</h3>
                <div style="background:#f8f9fa;border-radius:8px;padding:20px;border:1px solid #e5e7eb;">
                    <div style="background:#673ab7;color:white;padding:16px 20px;border-radius:8px 8px 0 0;margin:-20px -20px 20px;">
                        <h4 style="font-size:20px;font-weight:500;margin:0 0 6px;">${escapeHtml(programName)}</h4>
                        <p style="font-size:13px;margin:0;opacity:0.9;">Applicant responses (Google Form style)</p>
                    </div>
                    ${answers.map((item, idx) => `
                        <div style="background:white;border-radius:8px;padding:16px 18px;margin-bottom:12px;border:1px solid #dadce0;">
                            <div style="font-size:14px;color:#202124;font-weight:500;margin-bottom:10px;">
                                ${idx + 1}. ${escapeHtml(item.question)}
                            </div>
                            <div style="font-size:14px;color:#111827;line-height:1.6;padding:10px 12px;background:#f1f3f4;border-radius:6px;border-left:3px solid #673ab7;">
                                ${escapeHtml(item.answer || '—')}
                            </div>
                        </div>
                    `).join('')}
                </div>
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
