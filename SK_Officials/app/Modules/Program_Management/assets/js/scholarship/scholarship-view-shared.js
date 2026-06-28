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

    function formatScholarshipFullName(record) {
        const ln = String(record?.last_name || record?.lastName || '').trim().toUpperCase();
        const fn = String(record?.first_name || record?.firstName || '').trim().toUpperCase();
        const mn = String(record?.middle_name || record?.middleName || '').trim().toUpperCase();
        const suffix = String(record?.suffix || '').trim().toUpperCase();
        const parts = [fn, mn].filter(Boolean);
        const firstMiddle = parts.join(',');
        const suffixPart = suffix ? `,${suffix}` : '';
        if (ln && firstMiddle) return `${ln},${firstMiddle}${suffixPart}`;
        if (ln) return `${ln}${suffixPart}`;
        if (firstMiddle) return `${firstMiddle}${suffixPart}`;
        return '—';
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
        // Scholarship schedules are persisted in schedule_programs via API.
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
                        <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Announcement</label>
                        <div style="font-size:15px;color:#374151;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;min-height:80px;white-space:pre-wrap;">${escapeHtml(p.announcement || 'No announcement set')}</div>
                    </div>
                </div>
            </div>`;
    }

    function isDocumentAnswer(answer) {
        return answer && typeof answer === 'object' && !Array.isArray(answer)
            && (answer.original_name || answer.preview_url || answer.download_url || answer.path);
    }

    function formatAnswerText(answer) {
        if (answer === null || answer === undefined || answer === '') return '—';
        if (isDocumentAnswer(answer)) return String(answer.original_name || 'Uploaded PDF');
        if (Array.isArray(answer)) return answer.join(', ');
        if (typeof answer === 'object') return answer.original_name ? String(answer.original_name) : '—';
        return String(answer);
    }

    function renderDocumentCard(answer) {
        const file = answer && typeof answer === 'object' ? answer : {};
        const previewUrl = file.preview_url || file.download_url || '#';
        const downloadUrl = file.download_url || previewUrl;
        const fileName = file.original_name || 'Uploaded PDF';
        const meta = [file.size_display, file.question_label].filter(Boolean).join(' • ');

        return `
            <div style="display:flex;gap:14px;align-items:flex-start;padding:14px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="width:44px;height:44px;border-radius:8px;background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">PDF</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:#111827;word-break:break-word;">${escapeHtml(fileName)}</div>
                    ${meta ? `<div style="font-size:12px;color:#6b7280;margin-top:4px;">${escapeHtml(meta)}</div>` : ''}
                    <div style="display:flex;gap:12px;margin-top:10px;flex-wrap:wrap;">
                        <a href="${escapeHtml(previewUrl)}" target="_blank" rel="noopener" style="font-size:13px;font-weight:600;color:#213F99;text-decoration:none;">Preview</a>
                        <a href="${escapeHtml(downloadUrl)}" target="_blank" rel="noopener" style="font-size:13px;font-weight:600;color:#213F99;text-decoration:none;">Download</a>
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

                ${answers.map((item, idx) => {
                    const isFile = item.question_type === 'file' || isDocumentAnswer(item.answer);
                    const answerHtml = isFile
                        ? renderDocumentCard(item.answer)
                        : `<div style="font-size:14px;color:#111827;line-height:1.6;padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #673ab7;">${escapeHtml(formatAnswerText(item.answer))}</div>`;
                    return `
                    <div style="background:white;border-radius:8px;padding:24px;margin-bottom:20px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                        <div style="font-size:15px;color:#202124;font-weight:500;margin-bottom:10px;">
                            ${idx + 1}. ${escapeHtml(item.question)}
                        </div>
                        ${answerHtml}
                    </div>`;
                }).join('')}
            </div>`;
    }

    function formatCurrencyValue(value) {
        const digits = String(value || '').replace(/\D/g, '');
        if (!digits) return '—';
        const SF = global.ScholarshipSystemFields;
        return SF?.formatCurrencyDisplay?.(digits) || `₱${Number(digits).toLocaleString('en-PH')}`;
    }

    function viewFieldHtml(label, value, colSpan) {
        const val = String(value ?? '').trim() || '—';
        return `
            <div class="sch-app-view-field" ${colSpan ? `style="grid-column: span ${colSpan}"` : ''}>
                <span class="sch-app-view-label">${escapeHtml(label)}</span>
                <span class="sch-app-view-value">${escapeHtml(val)}</span>
            </div>`;
    }

    function viewSectionCard(stepNum, title, bodyHtml) {
        return `
            <section class="sch-app-view-section">
                <div class="sch-app-view-section-head">
                    <span class="sch-app-view-step">${stepNum}</span>
                    <h4 class="sch-app-view-section-title">${escapeHtml(title)}</h4>
                </div>
                <div class="sch-app-view-section-body">${bodyHtml}</div>
            </section>`;
    }

    function buildPersonalInfoItems(kkProfile) {
        const SF = global.ScholarshipSystemFields;
        const labels = SF?.KK_FIELD_LABELS || {};
        const order = SF?.DEFAULT_KK_FIELDS || [
            'last_name', 'first_name', 'middle_name', 'suffix', 'birthday', 'age', 'sex',
            'civil_status', 'contact_number', 'email', 'region', 'province', 'city',
            'barangay', 'purok_zone', 'youth_classification', 'youth_age_group', 'education',
            'current_school', 'course_strand',
        ];

        return order
            .map((id) => ({
                label: labels[id] || id.replace(/_/g, ' '),
                value: kkProfile?.[id],
            }))
            .filter((item) => String(item.value ?? '').trim() !== '');
    }

    function renderPersonalInformationSection(kkProfile) {
        const items = buildPersonalInfoItems(kkProfile);
        const fullName = formatScholarshipFullName({
            last_name: kkProfile?.last_name,
            first_name: kkProfile?.first_name,
            middle_name: kkProfile?.middle_name,
            suffix: kkProfile?.suffix,
        });

        const grid = items
            .map((item) => viewFieldHtml(item.label, item.value))
            .join('');

        return viewSectionCard(1, 'Personal Information', `
            ${fullName && fullName !== '—' ? `<p class="sch-app-view-name">${escapeHtml(fullName)}</p>` : ''}
            <div class="sch-app-view-grid">${grid || '<p class="sch-app-view-empty">No personal information recorded.</p>'}</div>
            <p class="sch-app-view-note">Auto-filled from the applicant&apos;s approved KK Profiling.</p>
        `);
    }

    function renderEducationalBackgroundSection(systemFields, kkEducation) {
        const SF = global.ScholarshipSystemFields;
        const edu = kkEducation || systemFields?._kk_education || systemFields?.education || '';
        const blocks = [];

        (SF?.SCHOOL_BLOCKS || []).forEach((block) => {
            if (SF?.isSchoolBlockVisible && !SF.isSchoolBlockVisible(block.id, edu)) {
                return;
            }

            const prefix = block.id === 'senior_high' ? 'senior_high' : block.id;
            const schoolLabel = block.id === 'elementary'
                ? 'Elementary School'
                : (block.id === 'secondary' ? 'Secondary School' : 'Senior High School');

            blocks.push(`
                <div class="sch-app-view-subsection">
                    <h5 class="sch-app-view-subtitle">${escapeHtml(block.title)}</h5>
                    <div class="sch-app-view-grid sch-app-view-grid-2">
                        ${viewFieldHtml(schoolLabel, systemFields[`${prefix}_school`])}
                        ${viewFieldHtml('Address', systemFields[`${prefix}_address`])}
                        ${viewFieldHtml('Year Graduated', systemFields[`${prefix}_year_graduated`])}
                    </div>
                </div>`);
        });

        if (!blocks.length) {
            blocks.push('<p class="sch-app-view-empty">No educational background entries for this profile level.</p>');
        }

        return viewSectionCard(2, 'Educational Background', blocks.join(''));
    }

    function renderBackgroundInformationSection(systemFields) {
        const groups = [
            { title: 'Mother', prefix: 'mother' },
            { title: 'Father', prefix: 'father' },
            { title: 'Guardian', prefix: 'guardian' },
        ];

        const groupHtml = groups.map(({ title, prefix }) => {
            const fullName = [
                systemFields[`${prefix}_first_name`],
                systemFields[`${prefix}_middle_name`],
                systemFields[`${prefix}_last_name`],
                systemFields[`${prefix}_suffix`],
            ].filter((part) => String(part || '').trim()).join(' ');

            if (!fullName && !systemFields[`${prefix}_occupation`] && !systemFields[`${prefix}_contact_number`]) {
                return '';
            }

            return `
                <div class="sch-app-view-subsection">
                    <h5 class="sch-app-view-subtitle">${title}</h5>
                    <div class="sch-app-view-grid">
                        ${viewFieldHtml('Full Name', fullName || '—', 3)}
                        ${prefix === 'guardian' ? viewFieldHtml('Relation', systemFields.guardian_relation) : ''}
                        ${viewFieldHtml('Occupation', systemFields[`${prefix}_occupation`])}
                        ${viewFieldHtml('Contact No.', systemFields[`${prefix}_contact_number`])}
                    </div>
                </div>`;
        }).join('');

        return viewSectionCard(3, 'Background Information', `
            ${groupHtml || '<p class="sch-app-view-empty">No family background recorded.</p>'}
            <div class="sch-app-view-subsection">
                <div class="sch-app-view-grid">
                    ${viewFieldHtml('Annual Family Gross Income', formatCurrencyValue(systemFields.annual_family_gross_income), 3)}
                </div>
            </div>
        `);
    }

    function getSystemFieldLabel(fieldId) {
        const field = global.ScholarshipSystemFields?.getAllFields?.().find((f) => f.id === fieldId);
        return field?.label || fieldId.replace(/_/g, ' ');
    }

    function renderAdditionalInformationSection(systemFields, kkEducation) {
        const SF = global.ScholarshipSystemFields;
        const edu = kkEducation || '';
        const fieldIds = [
            'strand', 'strand_abbreviation', 'year_level', 'units_enrolled',
            'expected_graduation_year', 'graduating', 'semester_of_graduation',
            'school_name', 'school_abbreviation', 'school_address',
            'receiving_gov_aid', 'gov_aid_program_name', 'family_on_scholarship',
        ];

        const visibleFields = fieldIds.filter((id) => {
            const field = SF?.getAllFields?.().find((f) => f.id === id);
            if (!field) return Boolean(systemFields[id]);
            return SF.isFieldVisible(field, systemFields, edu);
        });

        if (!visibleFields.length) {
            return viewSectionCard(4, 'Additional Information',
                '<p class="sch-app-view-empty">No additional information required for this education level.</p>');
        }

        const grid = visibleFields
            .map((id) => viewFieldHtml(getSystemFieldLabel(id), systemFields[id]))
            .join('');

        return viewSectionCard(4, 'Additional Information', `<div class="sch-app-view-grid">${grid}</div>`);
    }

    function renderRequirementsSection(formAnswers, uploadedDocuments) {
        const fileAnswers = (formAnswers || []).filter((item) => item.question_type === 'file' && item.answer);
        const docs = uploadedDocuments?.length ? uploadedDocuments : fileAnswers.map((item) => ({
            question_label: item.question,
            original_name: isDocumentAnswer(item.answer) ? item.answer.original_name : item.question,
            preview_url: item.answer?.preview_url,
            download_url: item.answer?.download_url,
            size_display: item.answer?.size_display,
        }));

        if (!docs.length) {
            return viewSectionCard(5, 'Uploading of Requirements',
                '<p class="sch-app-view-empty">No uploaded requirements found.</p>');
        }

        const list = docs.map((doc) => {
            const previewUrl = doc.preview_url || doc.download_url || '#';
            const downloadUrl = doc.download_url || previewUrl;
            const fileName = doc.original_name || doc.question_label || 'Document';
            const meta = [doc.size_display, doc.question_label].filter(Boolean).join(' • ');
            return `
                <div class="sch-app-view-doc">
                    <div class="sch-app-view-doc-icon">PDF</div>
                    <div class="sch-app-view-doc-body">
                        <div class="sch-app-view-doc-title">${escapeHtml(fileName)}</div>
                        ${meta ? `<div class="sch-app-view-doc-meta">${escapeHtml(meta)}</div>` : ''}
                        <div class="sch-app-view-doc-links">
                            <a href="${escapeHtml(previewUrl)}" target="_blank" rel="noopener">Preview</a>
                            <a href="${escapeHtml(downloadUrl)}" target="_blank" rel="noopener">Download</a>
                        </div>
                    </div>
                </div>`;
        }).join('');

        return viewSectionCard(5, 'Uploading of Requirements', `<div class="sch-app-view-docs">${list}</div>`);
    }

    function resolveKkEducation(kkProfile, systemFields) {
        return kkProfile?.education || systemFields?.education || systemFields?._kk_education || '';
    }

    function renderKabataanApplicationView(record) {
        const kkProfile = record.kk_profile_data || {};
        const systemFields = record.system_field_answers || {};
        const kkEducation = resolveKkEducation(kkProfile, systemFields);

        return `
            <div class="sch-app-view">
                ${renderPersonalInformationSection(kkProfile)}
                ${renderEducationalBackgroundSection(systemFields, kkEducation)}
                ${renderBackgroundInformationSection(systemFields)}
                ${renderAdditionalInformationSection(systemFields, kkEducation)}
                ${renderRequirementsSection(record.form_answers, record.uploaded_documents)}
            </div>`;
    }

    function normalizeApplicationDocuments(docs) {
        if (!docs) return [];
        if (Array.isArray(docs)) return docs;
        if (typeof docs === 'object') return Object.values(docs);
        return [];
    }

    function mapScholarshipApplicationDetail(app) {
        const docs = normalizeApplicationDocuments(app.required_documents);
        return {
            last_name: app.last_name,
            first_name: app.first_name,
            middle_name: app.middle_name,
            suffix: app.suffix,
            kk_profile_data: app.kk_profile_data || {},
            system_field_answers: app.system_field_answers || {},
            form_answers: (app.custom_answers || []).map((item, index) => ({
                question: item.question_label || item.label || `Question ${index + 1}`,
                question_type: item.question_type || '',
                answer: item.answer ?? '—',
            })),
            uploaded_documents: docs,
            submitted_at: app.date_submitted || app.submitted_at,
            submitted_time: app.submitted_time,
            status: app.status_label || app.status,
            program_name: app.program_name,
            schedule_program: app.schedule_program || null,
        };
    }

    function renderApplicationViewBody(record, options = {}) {
        const esc = escapeHtml;
        const sectionsHtml = renderKabataanApplicationView(record);
        const extraHtml = options.extraHtml || '';

        return `
            <div class="sch-app-view-wrap">
                ${extraHtml}
                ${sectionsHtml}
                <section class="sch-app-view-section sch-app-view-section-muted">
                    <div class="sch-app-view-section-head">
                        <span class="sch-app-view-step">•</span>
                        <h4 class="sch-app-view-section-title">Submission Details</h4>
                    </div>
                    <div class="sch-app-view-section-body">
                        <div class="sch-app-view-grid sch-app-view-grid-2">
                            <div class="sch-app-view-field">
                                <span class="sch-app-view-label">Date Submitted</span>
                                <span class="sch-app-view-value">${esc(record.submitted_at || '—')}</span>
                            </div>
                            <div class="sch-app-view-field">
                                <span class="sch-app-view-label">Time Submitted</span>
                                <span class="sch-app-view-value">${esc(record.submitted_time || '—')}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>`;
    }

    global.ScholarshipViewShared = {
        SAF_STORAGE_KEY,
        SAMPLE_SCHOLARSHIP_PROGRAM,
        escapeHtml,
        formatScholarshipFullName,
        formatTime12,
        loadScholarshipProgram,
        seedScholarshipProgramIfNeeded,
        getDefaultFormAnswers,
        resolveFormAnswers,
        renderProgramInformationSection,
        renderFormAnswersSection,
        renderKabataanApplicationView,
        mapScholarshipApplicationDetail,
        renderApplicationViewBody,
    };
})(window);
