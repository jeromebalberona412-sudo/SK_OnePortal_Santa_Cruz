/**
 * Default Scholarship System Fields (locked) + scholar status automation.
 */
(function (global) {
    const GRADE_BY_LEVEL = {
        'Junior High School': ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'],
        'Senior High School': ['Grade 11', 'Grade 12'],
        'College': ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'],
        'Technical-Vocational (TESDA)': ['Trainee', 'NC II', 'NC III', 'Other'],
        'Graduate Studies': ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'],
        'Other': ['Other'],
    };

    const SECTIONS = [
        {
            id: 'educational_background',
            title: 'Educational Background',
            locked: true,
            fields: [
                { id: 'educational_level', label: 'Educational Level', type: 'select', required: true, automation: false, options: Object.keys(GRADE_BY_LEVEL) },
                { id: 'grade_year_level', label: 'Current Grade/Year Level', type: 'select', required: true, automation: false, dynamicOptionsFrom: 'educational_level' },
                { id: 'course_program', label: 'Course / Program', type: 'text', required: false, showWhenLevels: ['College', 'Graduate Studies', 'Technical-Vocational (TESDA)'] },
                { id: 'course_abbreviation', label: 'Course Abbreviation', type: 'text', required: false },
                { id: 'school_name', label: 'School Name', type: 'text', required: true },
                { id: 'school_abbreviation', label: 'School Abbreviation', type: 'text', required: false },
                { id: 'school_address', label: 'School Address', type: 'text', required: true },
                { id: 'units_enrolled', label: 'Units Enrolled', type: 'number', required: false },
                { id: 'expected_graduation_year', label: 'Expected Year of Graduation', type: 'number', required: true, automation: false },
            ],
        },
        {
            id: 'background_information',
            title: 'Background Information',
            locked: true,
            fields: [
                { id: 'father_guardian_name', label: "Father's/Guardian's Name", type: 'text', required: false },
                { id: 'mother_guardian_name', label: "Mother's/Guardian's Name", type: 'text', required: false },
                { id: 'parent_occupation', label: 'Parent/Guardian Occupation', type: 'text', required: false },
                { id: 'monthly_household_income', label: 'Monthly Household Income', type: 'number', required: false },
                { id: 'household_members', label: 'Number of Household Members', type: 'number', required: false },
            ],
        },
        {
            id: 'additional_information',
            title: 'Additional Information',
            locked: true,
            fields: [
                { id: 'currently_enrolled', label: 'Are you currently enrolled in an educational institution?', type: 'radio', required: true, automation: true, options: ['Yes', 'No'] },
                { id: 'expected_graduate_this_year', label: 'Are you expected to graduate during the current academic year?', type: 'radio', required: true, automation: true, options: ['Yes', 'No'] },
                { id: 'already_graduated', label: 'Have you already graduated or completed your current educational level?', type: 'radio', required: true, automation: true, options: ['Yes', 'No'] },
                { id: 'receiving_gov_aid', label: 'Are you currently receiving any government-funded scholarship, educational assistance, or financial aid?', type: 'radio', required: true, automation: false, options: ['Yes', 'No'] },
                { id: 'gov_aid_program_name', label: 'Scholarship Program Name', type: 'text', required: false, showWhenField: 'receiving_gov_aid', showWhenValue: 'Yes' },
                { id: 'family_on_scholarship', label: 'Are there other members of your family currently benefiting from this scholarship program?', type: 'radio', required: true, automation: false, options: ['Yes', 'No'] },
                { id: 'family_member_name', label: 'Family Member Name', type: 'text', required: false, showWhenField: 'family_on_scholarship', showWhenValue: 'Yes' },
                { id: 'family_member_relationship', label: 'Relationship', type: 'text', required: false, showWhenField: 'family_on_scholarship', showWhenValue: 'Yes' },
                { id: 'agree_requirements', label: 'Do you agree to comply with scholarship requirements and obligations?', type: 'radio', required: true, automation: false, options: ['Yes', 'No'] },
            ],
        },
        {
            id: 'requirements',
            title: 'Requirements',
            locked: true,
            fields: [
                { id: 'req_school_id', label: 'School ID', type: 'file', required: true },
                { id: 'req_enrollment_cert', label: 'Certificate of Enrollment / Registration Form', type: 'file', required: true },
                { id: 'req_grades', label: 'Latest Grades / Report Card / TOR', type: 'file', required: true },
                { id: 'req_barangay_cert', label: 'Barangay Certificate / Clearance', type: 'file', required: true },
                { id: 'req_indigency', label: 'Certificate of Indigency', type: 'file', required: false },
                { id: 'req_graduation_docs', label: 'Graduation Documents', type: 'file', required: false },
                { id: 'req_other_supporting', label: 'Other Supporting Documents', type: 'file', required: false },
            ],
        },
    ];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getAllFields() {
        return SECTIONS.flatMap((section) => section.fields.map((field) => ({ ...field, sectionId: section.id, sectionTitle: section.title })));
    }

    function getFileFields() {
        return getAllFields().filter((f) => f.type === 'file');
    }

    function getNonFileFields() {
        return getAllFields().filter((f) => f.type !== 'file');
    }

    function determineScholarStatus(answers) {
        const enrolled = answers.currently_enrolled;
        const expectedGrad = answers.expected_graduate_this_year;
        const graduated = answers.already_graduated;

        if (graduated === 'Yes') return 'GRADUATED SCHOLAR';
        if (enrolled === 'Yes' && expectedGrad === 'Yes') return 'GRADUATING SCHOLAR';
        if (enrolled === 'Yes' && expectedGrad === 'No' && graduated === 'No') return 'ACTIVE SCHOLAR';
        if (enrolled === 'No' && graduated === 'No') return 'FOR REVIEW';
        return 'FOR REVIEW';
    }

    function renderBuilder(container) {
        if (!container) return;
        container.innerHTML = SECTIONS.map((section) => `
            <div class="schol-system-section" data-section="${section.id}">
                <div class="schol-system-section-head">
                    <h5>${escapeHtml(section.title)}</h5>
                    <span class="schol-system-lock-badge">System Fields · Locked</span>
                </div>
                <div class="schol-system-fields">
                    ${section.fields.map((field) => `
                        <div class="schol-system-field-row">
                            <div class="schol-system-field-meta">
                                <span class="schol-system-field-label">${escapeHtml(field.label)}</span>
                                <span class="schol-system-field-type">${escapeHtml(field.type)}</span>
                                ${field.automation ? '<span class="schol-system-auto-badge">Automation</span>' : ''}
                            </div>
                            <label class="schol-system-required-toggle">
                                <input type="checkbox" class="schol-system-required-cb" data-field-id="${escapeHtml(field.id)}" ${field.required ? 'checked' : ''} ${field.automation || field.required ? 'disabled' : ''}>
                                Required
                            </label>
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }

    function getRequiredConfig() {
        const config = {};
        document.querySelectorAll('.schol-system-required-cb').forEach((cb) => {
            config[cb.dataset.fieldId] = cb.checked;
        });
        getAllFields().forEach((field) => {
            if (field.required && config[field.id] === undefined) config[field.id] = true;
        });
        return config;
    }

    function isFieldVisible(field, values) {
        if (field.showWhenLevels) {
            return field.showWhenLevels.includes(values.educational_level || '');
        }
        if (field.showWhenField) {
            return (values[field.showWhenField] || '') === field.showWhenValue;
        }
        return true;
    }

    function renderApplicantSections(container, values = {}) {
        if (!container) return;

        const nonFileSections = SECTIONS.filter((s) => s.id !== 'requirements');
        container.innerHTML = nonFileSections.map((section) => `
            <div class="gf-card gf-system-section" data-system-section="${section.id}">
                <h2 class="gf-section-title">${escapeHtml(section.title)}</h2>
                <div class="gf-system-grid">
                    ${section.fields.filter((f) => f.type !== 'file').map((field) => {
                        const visible = isFieldVisible(field, values);
                        const required = field.required;
                        let inputHtml = '';

                        if (field.type === 'select') {
                            const options = field.dynamicOptionsFrom
                                ? (GRADE_BY_LEVEL[values[field.dynamicOptionsFrom]] || [])
                                : (field.options || []);
                            inputHtml = `<select class="gf-input schol-system-input" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" ${required ? 'required' : ''} ${visible ? '' : 'hidden'}>
                                <option value="">Select...</option>
                                ${options.map((opt) => `<option value="${escapeHtml(opt)}" ${values[field.id] === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('')}
                            </select>`;
                        } else if (field.type === 'radio') {
                            inputHtml = `<div class="gf-radio-group" data-system-field="${field.id}" ${visible ? '' : 'hidden'}>
                                ${(field.options || []).map((opt) => `
                                    <label class="gf-radio-label">
                                        <input type="radio" name="${field.id}" value="${escapeHtml(opt)}" ${values[field.id] === opt ? 'checked' : ''} ${required ? 'required' : ''}>
                                        ${escapeHtml(opt)}
                                    </label>
                                `).join('')}
                            </div>`;
                        } else {
                            inputHtml = `<input class="gf-input schol-system-input" type="${field.type === 'number' ? 'number' : 'text'}" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" value="${escapeHtml(values[field.id] || '')}" ${required ? 'required' : ''} ${visible ? '' : 'hidden'}>`;
                        }

                        return `
                            <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${visible ? '' : 'hidden'}>
                                <label for="sys_${field.id}">${escapeHtml(field.label)}${required ? ' <span class="gf-req">*</span>' : ''}</label>
                                ${inputHtml}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `).join('');

        container.querySelectorAll('[data-system-field="educational_level"]').forEach((el) => {
            el.addEventListener('change', () => refreshApplicantVisibility(container));
        });
        container.querySelectorAll('input[type="radio"]').forEach((el) => {
            el.addEventListener('change', () => refreshApplicantVisibility(container));
        });
    }

    function refreshApplicantVisibility(container) {
        const values = collectAnswers(container);
        container.querySelectorAll('.schol-system-field-wrap').forEach((wrap) => {
            const fieldId = wrap.dataset.fieldWrap;
            const field = getAllFields().find((f) => f.id === fieldId);
            if (!field) return;
            const visible = isFieldVisible(field, values);
            wrap.hidden = !visible;
            wrap.querySelectorAll('input, select, textarea').forEach((input) => {
                if (!visible) input.removeAttribute('required');
                else if (field.required) input.setAttribute('required', 'required');
            });
        });
    }

    function collectAnswers(root) {
        const values = {};
        if (!root) return values;

        root.querySelectorAll('.schol-system-input').forEach((el) => {
            values[el.name] = el.value;
        });

        getNonFileFields().filter((f) => f.type === 'radio').forEach((field) => {
            const checked = root.querySelector(`input[name="${field.id}"]:checked`);
            values[field.id] = checked ? checked.value : '';
        });

        return values;
    }

    function validateAnswers(root) {
        const values = collectAnswers(root);
        const missing = [];

        getNonFileFields().forEach((field) => {
            if (!isFieldVisible(field, values)) return;
            if (!field.required) return;
            const val = values[field.id];
            if (!String(val || '').trim()) missing.push(field.label);
        });

        return { ok: missing.length === 0, missing, values, scholarStatus: determineScholarStatus(values) };
    }

    global.ScholarshipSystemFields = {
        SECTIONS,
        GRADE_BY_LEVEL,
        getAllFields,
        getFileFields,
        getNonFileFields,
        determineScholarStatus,
        renderBuilder,
        getRequiredConfig,
        renderApplicantSections,
        refreshApplicantVisibility,
        collectAnswers,
        validateAnswers,
        isFieldVisible,
    };
})(window);
