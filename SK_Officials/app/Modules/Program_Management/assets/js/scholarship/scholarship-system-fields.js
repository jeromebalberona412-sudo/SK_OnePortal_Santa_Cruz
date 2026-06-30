/**
 * Default Scholarship System Fields — locked sections for SK Officials + Kabataan applicants.
 */
(function (global) {
    const NAME_PATTERN = /^(?!\s)[A-Za-z.\-\s]+$/;
    const CONTACT_PATTERN = /^09\d{9}$/;

    const YEAR_LEVEL_OPTIONS = ['Grade 11', 'Grade 12', '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Other'];
    const GRADUATING_OPTIONS = ['Yes', 'No'];
    const SEMESTER_OPTIONS = ['1st Semester', '2nd Semester', 'N/A'];

    const COLLEGE_TRACK = ['College Level', 'College Grad', 'Vocational Grad', 'Masters Level', 'Masters Grad', 'Doctorate Level', 'Doctorate Graduate'];
    const HIGH_SCHOOL_TRACK = ['High School Grad', 'High School Level'];
    const ELEMENTARY_ONLY = ['Elementary Level', 'Elementary Grad'];

    const SCHOOL_TEXT_MIN = 20;
    const SCHOOL_TEXT_MAX = 100;
    const SCHOOL_TEXT_FIELD_IDS = new Set([
        'elementary_school', 'elementary_address',
        'secondary_school', 'secondary_address',
        'senior_high_school', 'senior_high_address',
    ]);
    const OCCUPATION_OTHER_VALUE = 'Other Occupation';
    const OCCUPATION_OTHER_MIN = 3;
    const OCCUPATION_OTHER_MAX = 100;
    const FAMILY_MONTHLY_INCOME_OPTIONS = [
        '₱5,000', '₱10,000', '₱20,000', '₱30,000', '₱40,000', '₱50,000', '₱50,000 and above',
    ];
    const OCCUPATION_OPTIONS = [
        'Unemployed', 'Self-employed', 'Government Employee', 'Private Employee', 'Teacher', 'Nurse', 'Doctor',
        'Dentist', 'Pharmacist', 'Engineer', 'Architect', 'Accountant', 'Lawyer', 'Police Officer', 'Firefighter',
        'Soldier / Military Personnel', 'Security Guard', 'Office Staff / Clerk', 'Administrative Assistant',
        'Call Center Agent', 'Customer Service Representative', 'Sales Associate', 'Cashier', 'Store Crew',
        'Service Crew', 'Waiter / Waitress', 'Cook / Chef', 'Baker', 'Driver', 'Delivery Rider', 'Mechanic',
        'Electrician', 'Plumber', 'Carpenter', 'Welder', 'Construction Worker', 'Farmer', 'Fisherman', 'Vendor',
        'Entrepreneur / Business Owner', 'Hairdresser / Barber', 'Beautician', 'Photographer', 'Graphic Designer',
        'Web Developer', 'Software Developer', 'IT Support Specialist', 'Freelancer', 'Content Creator',
        'Social Media Manager', 'Librarian', 'Homemaker', 'Retired', 'Overseas Filipino Worker (OFW)',
        OCCUPATION_OTHER_VALUE,
    ];

    const SCHOOL_BLOCKS = [
        { id: 'elementary', title: 'Elementary School' },
        { id: 'secondary', title: 'Secondary School' },
        { id: 'senior_high', title: 'Senior High School' },
    ];

    const DEFAULT_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix', 'birthday', 'age', 'sex',
        'civil_status', 'contact_number', 'email', 'region', 'province', 'city',
        'barangay', 'purok_zone', 'youth_classification', 'youth_age_group', 'education',
        'current_school', 'course_strand',
    ];

    const KK_FIELD_LABELS = {
        last_name: 'Last Name',
        first_name: 'First Name',
        middle_name: 'Middle Name',
        suffix: 'Suffix',
        birthday: 'Birthday',
        age: 'Age',
        sex: 'Sex',
        civil_status: 'Civil Status',
        contact_number: 'Contact Number',
        email: 'Email Address',
        region: 'Region',
        province: 'Province',
        city: 'City/Municipality',
        barangay: 'Barangay',
        purok_zone: 'Purok/Zone',
        youth_classification: 'Youth Classification',
        youth_age_group: 'Youth Age Group',
        education: 'Educational Attainment',
        current_school: 'Current School',
        course_strand: 'Course / Strand',
    };

    const SECTIONS = [
        {
            id: 'personal_information',
            title: 'Personal Information',
            locked: true,
            kkProfiling: true,
            fields: [],
        },
        {
            id: 'educational_background',
            title: 'Educational Background',
            locked: true,
            fields: [
                { id: 'elementary_school', label: 'Elementary School', type: 'text', required: true, schoolBlock: 'elementary', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'elementary_address', label: 'Address', type: 'text', required: true, schoolBlock: 'elementary', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'elementary_year_graduated', label: 'Year Graduated', type: 'year', required: true, schoolBlock: 'elementary' },
                { id: 'secondary_school', label: 'Secondary School', type: 'text', required: true, schoolBlock: 'secondary', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'secondary_address', label: 'Address', type: 'text', required: true, schoolBlock: 'secondary', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'secondary_year_graduated', label: 'Year Graduated', type: 'year', required: true, schoolBlock: 'secondary' },
                { id: 'senior_high_school', label: 'Senior High School', type: 'text', required: true, schoolBlock: 'senior_high', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'senior_high_address', label: 'Address', type: 'text', required: true, schoolBlock: 'senior_high', minLength: SCHOOL_TEXT_MIN, maxLength: SCHOOL_TEXT_MAX },
                { id: 'senior_high_year_graduated', label: 'Year Graduated', type: 'year', required: true, schoolBlock: 'senior_high' },
            ],
        },
        {
            id: 'background_information',
            title: 'Background Information',
            locked: true,
            fields: [
                { id: 'mother_first_name', label: "Mother's First Name", type: 'name', required: true, group: 'mother' },
                { id: 'mother_last_name', label: "Mother's Last Name", type: 'name', required: true, group: 'mother' },
                { id: 'mother_occupation', label: "Mother's Occupation", type: 'select', required: true, group: 'mother', options: OCCUPATION_OPTIONS },
                { id: 'mother_occupation_other', label: "Mother's Other Occupation", type: 'text', required: true, group: 'mother', minLength: OCCUPATION_OTHER_MIN, maxLength: OCCUPATION_OTHER_MAX, showWhenField: 'mother_occupation', showWhenValue: OCCUPATION_OTHER_VALUE },
                { id: 'mother_contact_number', label: "Mother's Contact No.", type: 'contact', required: true, group: 'mother' },
                { id: 'father_first_name', label: "Father's First Name", type: 'name', required: true, group: 'father' },
                { id: 'father_last_name', label: "Father's Last Name", type: 'name', required: true, group: 'father' },
                { id: 'father_occupation', label: "Father's Occupation", type: 'select', required: true, group: 'father', options: OCCUPATION_OPTIONS },
                { id: 'father_occupation_other', label: "Father's Other Occupation", type: 'text', required: true, group: 'father', minLength: OCCUPATION_OTHER_MIN, maxLength: OCCUPATION_OTHER_MAX, showWhenField: 'father_occupation', showWhenValue: OCCUPATION_OTHER_VALUE },
                { id: 'father_contact_number', label: "Father's Contact No.", type: 'contact', required: true, group: 'father' },
                { id: 'guardian_first_name', label: "Guardian's First Name", type: 'name', required: false, group: 'guardian' },
                { id: 'guardian_last_name', label: "Guardian's Last Name", type: 'name', required: false, group: 'guardian' },
                { id: 'guardian_occupation', label: "Guardian's Occupation", type: 'select', required: false, group: 'guardian', options: OCCUPATION_OPTIONS },
                { id: 'guardian_occupation_other', label: "Guardian's Other Occupation", type: 'text', required: true, group: 'guardian', minLength: OCCUPATION_OTHER_MIN, maxLength: OCCUPATION_OTHER_MAX, showWhenField: 'guardian_occupation', showWhenValue: OCCUPATION_OTHER_VALUE },
                { id: 'guardian_relation', label: 'Relation to Guardian', type: 'text', required: false, group: 'guardian' },
                { id: 'guardian_contact_number', label: "Guardian's Contact No.", type: 'contact', required: false, group: 'guardian' },
                { id: 'annual_family_gross_income', label: 'Family Monthly Income', type: 'select', required: true, options: FAMILY_MONTHLY_INCOME_OPTIONS },
            ],
        },
        {
            id: 'additional_information',
            title: 'Additional Information',
            locked: true,
            fields: [
                { id: 'strand', label: 'Strand / Course', type: 'text', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'strand_abbreviation', label: 'Strand / Course Abbreviation', type: 'text', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'year_level', label: 'Year Level (based on required/attached Registration Form)', type: 'select', required: true, options: YEAR_LEVEL_OPTIONS, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'units_enrolled', label: 'Units Enrolled (based on required/attached Registration Form)', type: 'number', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'expected_graduation_year', label: 'Expected Year of Graduation', type: 'year', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'graduating', label: 'Graduating?', type: 'radio', required: true, options: GRADUATING_OPTIONS, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'semester_of_graduation', label: 'Semester of Graduation', type: 'select', required: true, options: SEMESTER_OPTIONS, showWhenField: 'graduating', showWhenValue: 'Yes', showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'school_name', label: 'School Name', type: 'text', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'school_abbreviation', label: 'School Abbreviation', type: 'text', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'school_address', label: 'School Address', type: 'text', required: true, showWhenEducation: ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'] },
                { id: 'receiving_gov_aid', label: 'Are you a recipient of any other government funded financial assistance/scholarship program?', type: 'radio', required: true, options: GRADUATING_OPTIONS },
                { id: 'gov_aid_program_name', label: 'If yes, please specify', type: 'text', required: false, showWhenField: 'receiving_gov_aid', showWhenValue: 'Yes' },
                { id: 'family_on_scholarship', label: 'Are there other members of the family who is/are currently on a government scholarship program?', type: 'radio', required: true, options: GRADUATING_OPTIONS },
            ],
        },
        {
            id: 'requirements',
            title: 'Uploading of Requirements',
            locked: true,
            customFileUploads: true,
            fields: [],
        },
    ];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getApplicantSections() {
        return SECTIONS.filter((s) => s.id !== 'personal_information' && s.id !== 'requirements');
    }

    function getAllFields() {
        return SECTIONS.flatMap((section) => section.fields.map((field) => ({
            ...field,
            sectionId: section.id,
            sectionTitle: section.title,
        })));
    }

    function getFileFieldsFromProgram(program) {
        return (program?.custom_questions || []).filter((q) => q.type === 'file');
    }

    function normalizeEducation(education) {
        return String(education || '').trim();
    }

    function formatOccupationDisplay(occupation, other) {
        const selected = String(occupation || '').trim();
        const otherText = String(other || '').trim();
        if (selected === OCCUPATION_OTHER_VALUE && otherText) {
            return otherText.toUpperCase();
        }
        return selected ? selected.toUpperCase() : '';
    }

    function resolveOccupationFromValues(prefix, values) {
        return formatOccupationDisplay(values[`${prefix}_occupation`] || '', values[`${prefix}_occupation_other`] || '');
    }

    function isSchoolBlockVisible(blockId, education) {
        const edu = normalizeEducation(education);

        if (!edu) {
            return false;
        }

        if (blockId === 'elementary') {
            return ELEMENTARY_ONLY.includes(edu)
                || HIGH_SCHOOL_TRACK.includes(edu)
                || COLLEGE_TRACK.includes(edu);
        }

        if (blockId === 'secondary') {
            return HIGH_SCHOOL_TRACK.includes(edu) || COLLEGE_TRACK.includes(edu);
        }

        if (blockId === 'senior_high') {
            return edu === 'High School Level' || COLLEGE_TRACK.includes(edu);
        }

        return false;
    }

    function isFieldVisible(field, values, kkEducation) {
        const education = normalizeEducation(kkEducation || values._kk_education || '');

        if (field.schoolBlock) {
            return isSchoolBlockVisible(field.schoolBlock, education);
        }

        if (field.id && String(field.id).endsWith('_occupation_other')) {
            const occupationKey = String(field.id).replace(/_other$/, '');
            return (values[occupationKey] || '') === OCCUPATION_OTHER_VALUE;
        }

        if (field.showWhenEducation) {
            if (!field.showWhenEducation.includes(education)) {
                return false;
            }
        }

        if (field.showWhenField) {
            return (values[field.showWhenField] || '') === field.showWhenValue;
        }

        return true;
    }

    function formatCurrencyDisplay(raw) {
        const digits = String(raw || '').replace(/\D/g, '').slice(0, 10);
        if (!digits) return '';
        const num = Number(digits);
        return `₱${num.toLocaleString('en-PH')}`;
    }

    function parseCurrencyValue(raw) {
        return String(raw || '').replace(/\D/g, '').slice(0, 10);
    }

    function validateName(value, required) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return required ? 'This field is required.' : '';
        if (trimmed.length < 3 || trimmed.length > 50) return 'Must be 3–50 characters.';
        if (!NAME_PATTERN.test(trimmed)) return 'Letters, spaces, periods, and hyphens only.';
        return '';
    }

    function validateSuffix(value) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return '';
        if (trimmed.length > 10) return 'Suffix is too long.';
        if (!NAME_PATTERN.test(trimmed)) return 'Invalid suffix.';
        return '';
    }

    function validateContact(value, required) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return required ? 'Contact number is required.' : '';
        if (!CONTACT_PATTERN.test(trimmed)) return 'Use format 09XXXXXXXXX.';
        return '';
    }

    function validateCurrency(value, required) {
        const digits = parseCurrencyValue(value);
        if (!digits) return required ? 'Annual family gross income is required.' : '';
        if (Number(digits) < 0) return 'Income cannot be negative.';
        if (digits.length > 10) return 'Maximum 10 digits only.';
        return '';
    }

    function validateSchoolText(value, required, label, minLength = SCHOOL_TEXT_MIN, maxLength = SCHOOL_TEXT_MAX) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return required ? `${label} is required.` : '';
        if (trimmed.length < minLength) return `${label} must be at least ${minLength} characters.`;
        if (trimmed.length > maxLength) return `${label} must not exceed ${maxLength} characters.`;
        return '';
    }

    function validateField(field, value, visible) {
        if (!visible) return '';
        if (field.id && String(field.id).endsWith('_occupation_other')) {
            return validateSchoolText(
                value,
                field.required,
                field.label,
                field.minLength || OCCUPATION_OTHER_MIN,
                field.maxLength || OCCUPATION_OTHER_MAX,
            );
        }
        if (SCHOOL_TEXT_FIELD_IDS.has(field.id) || (field.minLength && field.maxLength)) {
            return validateSchoolText(
                value,
                field.required,
                field.label,
                field.minLength || SCHOOL_TEXT_MIN,
                field.maxLength || SCHOOL_TEXT_MAX,
            );
        }
        if (field.type === 'name') return validateName(value, field.required);
        if (field.type === 'suffix') return validateSuffix(value);
        if (field.type === 'contact') return validateContact(value, field.required);
        if (field.type === 'currency') return validateCurrency(value, field.required);
        if (field.type === 'year') {
            const year = String(value || '').trim();
            if (!year && field.required) return 'Year is required.';
            if (year && !/^\d{4}$/.test(year)) return 'Enter a valid 4-digit year.';
            return '';
        }
        if (field.required && !String(value || '').trim()) return `${field.label} is required.`;
        return '';
    }

    function renderBuilder(container) {
        if (!container) return;

        const editableSections = SECTIONS.filter((s) => !s.kkProfiling && !s.customFileUploads);
        const kkFieldList = DEFAULT_KK_FIELDS.map((key) => `
            <div class="schol-system-field-row">
                <div class="schol-system-field-meta">
                    <span class="schol-system-field-label">${escapeHtml(KK_FIELD_LABELS[key] || key)}</span>
                </div>
                <span class="schol-system-required-pill">Auto-filled</span>
            </div>
        `).join('');

        container.innerHTML = `
            <div class="schol-system-section schol-system-section-kk">
                <div class="schol-system-section-head">
                    <h5>1. Personal Information (KK Profiling)</h5>
                    <span class="schol-system-lock-badge">System Fields · Auto-included</span>
                </div>
                <p class="schol-system-section-desc">All KK Profiling fields are automatically included in scholarship applications — including Middle Name and Suffix. Data is auto-filled from the applicant's KK Profile and displayed as read-only on the Kabataan apply form.</p>
                <div class="schol-system-fields">${kkFieldList}</div>
            </div>
            ${editableSections.map((section, index) => `
                <div class="schol-system-section" data-section="${section.id}">
                    <div class="schol-system-section-head">
                        <h5>${index + 2}. ${escapeHtml(section.title)}</h5>
                        <span class="schol-system-lock-badge">System Fields · Locked</span>
                    </div>
                    <div class="schol-system-fields">
                        ${section.fields.map((field) => `
                            <div class="schol-system-field-row">
                                <div class="schol-system-field-meta">
                                    <span class="schol-system-field-label">${escapeHtml(field.label)}</span>
                                    <span class="schol-system-field-type">${escapeHtml(field.type)}</span>
                                </div>
                                <span class="schol-system-required-pill">${field.required ? 'Required' : 'Optional'}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('')}
        `;
    }

    function renderFieldInput(field, values, kkEducation) {
        const visible = isFieldVisible(field, values, kkEducation);
        const required = field.required && visible;
        const value = values[field.id] || '';
        const reqMark = required ? ' <span class="gf-req">*</span>' : '';
        const hiddenAttr = visible ? '' : 'hidden';

        if (field.type === 'radio') {
            return `
                <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                    <label>${escapeHtml(field.label)}${reqMark}</label>
                    <div class="gf-radio-group" data-system-field="${field.id}">
                        ${(field.options || []).map((opt) => `
                            <label class="gf-radio-item">
                                <input type="radio" name="${field.id}" value="${escapeHtml(opt)}" ${value === opt ? 'checked' : ''} ${required ? 'required' : ''}>
                                <span>${escapeHtml(opt)}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>`;
        }

        if (field.type === 'select') {
            return `
                <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                    <label for="sys_${field.id}">${escapeHtml(field.label)}${reqMark}</label>
                    <select class="gf-input schol-system-input" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" ${required ? 'required' : ''}>
                        <option value="">Select...</option>
                        ${(field.options || []).map((opt) => `<option value="${escapeHtml(opt)}" ${value === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('')}
                    </select>
                </div>`;
        }

        const inputType = field.type === 'number' || field.type === 'year' ? 'number' : 'text';
        const extraAttrs = field.type === 'year' ? 'min="1950" max="2100" step="1"' : '';
        const lengthAttrs = field.minLength && field.maxLength
            ? `minlength="${field.minLength}" maxlength="${field.maxLength}"`
            : '';
        const inputClass = field.type === 'currency' ? 'gf-input schol-system-input schol-system-currency' : 'gf-input schol-system-input';
        const displayValue = field.type === 'currency' ? formatCurrencyDisplay(value) : escapeHtml(value);

        return `
            <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                <label for="sys_${field.id}">${escapeHtml(field.label)}${reqMark}</label>
                <input class="${inputClass}" type="${inputType}" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" data-field-type="${field.type}" value="${displayValue}" ${required ? 'required' : ''} ${extraAttrs} ${lengthAttrs} autocomplete="off">
            </div>`;
    }

    function renderApplicantSection(section, values, kkEducation) {
        if (!section.fields.length) return '';

        const groups = ['mother', 'father', 'guardian'];
        const groupedFields = section.fields.filter((f) => f.group);
        const ungroupedFields = section.fields.filter((f) => !f.group);

        let inner = '';

        groups.forEach((group) => {
            const groupFields = groupedFields.filter((f) => f.group === group);
            if (!groupFields.length) return;
            const groupTitle = group === 'mother' ? "Mother's Information" : (group === 'father' ? "Father's Information" : "Guardian's Information");
            inner += `
                <div class="schol-system-group" data-group="${group}">
                    <h3 class="schol-system-group-title">${groupTitle}</h3>
                    <div class="gf-system-grid">${groupFields.map((f) => renderFieldInput(f, values, kkEducation)).join('')}</div>
                </div>`;
        });

        if (ungroupedFields.length) {
            if (section.id === 'educational_background') {
                SCHOOL_BLOCKS.forEach((block) => {
                    const blockFields = ungroupedFields.filter((f) => f.schoolBlock === block.id);
                    if (!blockFields.length) return;
                    const visibleFields = blockFields.filter((f) => isFieldVisible(f, values, kkEducation));
                    if (!visibleFields.length) return;
                    inner += `
                        <div class="schol-edu-block" data-school-block="${block.id}">
                            <h3 class="schol-system-group-title">${escapeHtml(block.title)}</h3>
                            <div class="gf-system-grid">${visibleFields.map((f) => renderFieldInput(f, values, kkEducation)).join('')}</div>
                        </div>`;
                });
            } else {
                inner += `<div class="gf-system-grid">${ungroupedFields.map((f) => renderFieldInput(f, values, kkEducation)).join('')}</div>`;
            }
        }

        return `
            <div class="gf-card gf-system-section" data-system-section="${section.id}">
                <h2 class="gf-section-title">${escapeHtml(section.title)}</h2>
                ${inner}
            </div>`;
    }

    function renderApplicantSectionById(container, sectionId, values = {}, kkEducation = '') {
        if (!container) return;
        const section = SECTIONS.find((s) => s.id === sectionId);
        if (!section) return;
        const enriched = { ...values, _kk_education: kkEducation };
        container.innerHTML = renderApplicantSection(section, enriched, kkEducation);
        bindApplicantEvents(container, kkEducation);
    }

    function renderApplicantSections(container, values = {}, kkEducation = '') {
        if (!container) return;
        const enriched = { ...values, _kk_education: kkEducation };
        container.innerHTML = getApplicantSections()
            .map((section) => renderApplicantSection(section, enriched, kkEducation))
            .join('');

        bindApplicantEvents(container, kkEducation);
    }

    function bindApplicantEvents(container, kkEducation) {
        container.querySelectorAll('.schol-system-input').forEach((el) => {
            el.addEventListener('input', () => {
                if (el.dataset.fieldType === 'currency') {
                    const pos = el.selectionStart;
                    const raw = parseCurrencyValue(el.value);
                    el.value = raw ? formatCurrencyDisplay(raw) : '';
                    el.setSelectionRange(el.value.length, el.value.length);
                }
                refreshApplicantVisibility(container, kkEducation);
            });
            el.addEventListener('change', () => refreshApplicantVisibility(container, kkEducation));
        });
        container.querySelectorAll('input[type="radio"]').forEach((el) => {
            el.addEventListener('change', () => refreshApplicantVisibility(container, kkEducation));
        });
    }

    function refreshApplicantVisibility(container, kkEducation) {
        const values = collectAnswers(container, kkEducation);
        getAllFields().forEach((field) => {
            const wrap = container.querySelector(`[data-field-wrap="${field.id}"]`);
            if (!wrap) return;
            const visible = isFieldVisible(field, values, kkEducation);
            wrap.hidden = !visible;
            wrap.querySelectorAll('input, select, textarea').forEach((input) => {
                if (!visible) input.removeAttribute('required');
                else if (field.required) input.setAttribute('required', 'required');
            });
        });

        container.querySelectorAll('[data-school-block]').forEach((blockEl) => {
            const blockId = blockEl.getAttribute('data-school-block') || '';
            blockEl.hidden = !isSchoolBlockVisible(blockId, kkEducation);
        });
    }

    function collectAnswers(root, kkEducation = '') {
        const values = { _kk_education: kkEducation };
        if (!root) return values;

        root.querySelectorAll('.schol-system-input').forEach((el) => {
            if (el.dataset.fieldType === 'currency') {
                values[el.name] = parseCurrencyValue(el.value);
            } else {
                values[el.name] = el.value;
            }
        });

        getAllFields().filter((f) => f.type === 'radio').forEach((field) => {
            const checked = root.querySelector(`input[name="${field.id}"]:checked`);
            values[field.id] = checked ? checked.value : '';
        });

        if (values.graduating === 'No') {
            values.semester_of_graduation = 'N/A';
        }

        ['mother', 'father', 'guardian'].forEach((prefix) => {
            const otherKey = `${prefix}_occupation_other`;
            if (values[`${prefix}_occupation`] === OCCUPATION_OTHER_VALUE) {
                values[otherKey] = String(values[otherKey] || '').trim().toUpperCase();
            } else {
                values[otherKey] = '';
            }
        });

        return values;
    }

    function validateAnswers(root, kkEducation = '') {
        const values = collectAnswers(root, kkEducation);
        const errors = [];

        getAllFields().forEach((field) => {
            if (!root.querySelector(`[data-field-wrap="${field.id}"]`)) {
                return;
            }
            const visible = isFieldVisible(field, values, kkEducation);
            const err = validateField(field, values[field.id], visible);
            if (err) errors.push({ field: field.id, label: field.label, message: err });
        });

        return { ok: errors.length === 0, errors, values };
    }

    global.ScholarshipSystemFields = {
        SECTIONS,
        DEFAULT_KK_FIELDS,
        KK_FIELD_LABELS,
        getApplicantSections,
        getAllFields,
        getFileFieldsFromProgram,
        renderBuilder,
        renderApplicantSections,
        renderApplicantSectionById,
        refreshApplicantVisibility,
        collectAnswers,
        validateAnswers,
        isFieldVisible,
        isSchoolBlockVisible,
        SCHOOL_BLOCKS,
        formatCurrencyDisplay,
        parseCurrencyValue,
        validateName,
        validateContact,
        formatOccupationDisplay,
        resolveOccupationFromValues,
        FAMILY_MONTHLY_INCOME_OPTIONS,
        OCCUPATION_OPTIONS,
    };
})(window);
