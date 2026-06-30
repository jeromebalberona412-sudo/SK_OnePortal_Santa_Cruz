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
    const STRAND_COLLEGE_TRACK = ['College Level', 'College Grad', 'Vocational Grad'];
    const EMAIL_PATTERN = /^[A-Za-z0-9._%+-]{6,30}@gmail\.com$/i;
    const UPPERCASE_FIELD_TYPES = new Set(['text', 'name', 'suffix', 'suffix_other']);
    const SCHOOL_TEXT_FIELD_IDS = new Set([
        'elementary_school', 'elementary_address',
        'secondary_school', 'secondary_address',
        'senior_high_school', 'senior_high_address',
    ]);
    const SCHOOL_TEXT_MIN = 20;
    const SCHOOL_TEXT_MAX = 100;
    const OCCUPATION_OTHER_VALUE = 'Other Occupation';
    const OCCUPATION_OTHER_MIN = 3;
    const OCCUPATION_OTHER_MAX = 100;

    const SUFFIX_OPTIONS = ['Jr.', 'Sr.', 'I', 'II', 'III', 'IV', 'V', 'Other'];
    const SUFFIX_OTHER_VALUE = 'Other';
    const SUFFIX_OTHER_MAX = 10;

    const FAMILY_MONTHLY_INCOME_OPTIONS = [
        '₱5,000',
        '₱10,000',
        '₱20,000',
        '₱30,000',
        '₱40,000',
        '₱50,000',
        '₱50,000 and above',
    ];

    const OCCUPATION_OPTIONS = [
        'Unemployed',
        'Self-employed',
        'Government Employee',
        'Private Employee',
        'Teacher',
        'Nurse',
        'Doctor',
        'Dentist',
        'Pharmacist',
        'Engineer',
        'Architect',
        'Accountant',
        'Lawyer',
        'Police Officer',
        'Firefighter',
        'Soldier / Military Personnel',
        'Security Guard',
        'Office Staff / Clerk',
        'Administrative Assistant',
        'Call Center Agent',
        'Customer Service Representative',
        'Sales Associate',
        'Cashier',
        'Store Crew',
        'Service Crew',
        'Waiter / Waitress',
        'Cook / Chef',
        'Baker',
        'Driver',
        'Delivery Rider',
        'Mechanic',
        'Electrician',
        'Plumber',
        'Carpenter',
        'Welder',
        'Construction Worker',
        'Farmer',
        'Fisherman',
        'Vendor',
        'Entrepreneur / Business Owner',
        'Hairdresser / Barber',
        'Beautician',
        'Photographer',
        'Graphic Designer',
        'Web Developer',
        'Software Developer',
        'IT Support Specialist',
        'Freelancer',
        'Content Creator',
        'Social Media Manager',
        'Librarian',
        'Homemaker',
        'Retired',
        'Overseas Filipino Worker (OFW)',
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
    ];

    const KK_FIELD_LABELS = {
        last_name: 'Last Name',
        first_name: 'First Name',
        middle_name: 'Middle Name',
        suffix: 'Suffix',
        birthday: 'Birth Date',
        age: 'Age',
        sex: 'Sex',
        civil_status: 'Civil Status',
        contact_number: 'Contact Number',
        email: 'Email Address',
        region: 'Region',
        province: 'Province',
        city: 'City/Municipality',
        city_municipality: 'City/Municipality',
        barangay: 'Barangay',
        purok_zone: 'Purok / Zone',
        youth_classification: 'Youth Classification',
        youth_age_group: 'Youth Age Group',
        education: 'Education',
        current_school: 'Current School',
        course_strand: 'Course / Strand',
        birth_place: 'Birth Place',
        religion: 'Religion',
    };

    const PERSONAL_ALWAYS_SHOW_KEYS = new Set(['first_name', 'middle_name', 'last_name', 'suffix']);

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
                { id: 'mother_middle_name', label: "Mother's Middle Name", type: 'name', required: false, group: 'mother' },
                { id: 'mother_last_name', label: "Mother's Last Name", type: 'name', required: true, group: 'mother' },
                { id: 'mother_suffix', label: "Mother's Suffix", type: 'suffix_select', required: false, group: 'mother', options: SUFFIX_OPTIONS },
                { id: 'mother_suffix_other', label: "Mother's Suffix (specify)", type: 'suffix_other', required: false, group: 'mother', maxLength: SUFFIX_OTHER_MAX, showWhenField: 'mother_suffix', showWhenValue: SUFFIX_OTHER_VALUE },
                { id: 'mother_occupation', label: "Mother's Occupation", type: 'select', required: true, group: 'mother', options: OCCUPATION_OPTIONS },
                { id: 'mother_occupation_other', label: "Mother's Other Occupation", type: 'text', required: true, group: 'mother', minLength: OCCUPATION_OTHER_MIN, maxLength: OCCUPATION_OTHER_MAX, showWhenField: 'mother_occupation', showWhenValue: OCCUPATION_OTHER_VALUE },
                { id: 'mother_contact_number', label: "Mother's Contact No.", type: 'contact', required: true, group: 'mother' },
                { id: 'father_first_name', label: "Father's First Name", type: 'name', required: true, group: 'father' },
                { id: 'father_middle_name', label: "Father's Middle Name", type: 'name', required: false, group: 'father' },
                { id: 'father_last_name', label: "Father's Last Name", type: 'name', required: true, group: 'father' },
                { id: 'father_suffix', label: "Father's Suffix", type: 'suffix_select', required: false, group: 'father', options: SUFFIX_OPTIONS },
                { id: 'father_suffix_other', label: "Father's Suffix (specify)", type: 'suffix_other', required: false, group: 'father', maxLength: SUFFIX_OTHER_MAX, showWhenField: 'father_suffix', showWhenValue: SUFFIX_OTHER_VALUE },
                { id: 'father_occupation', label: "Father's Occupation", type: 'select', required: true, group: 'father', options: OCCUPATION_OPTIONS },
                { id: 'father_occupation_other', label: "Father's Other Occupation", type: 'text', required: true, group: 'father', minLength: OCCUPATION_OTHER_MIN, maxLength: OCCUPATION_OTHER_MAX, showWhenField: 'father_occupation', showWhenValue: OCCUPATION_OTHER_VALUE },
                { id: 'father_contact_number', label: "Father's Contact No.", type: 'contact', required: true, group: 'father' },
                { id: 'guardian_first_name', label: "Guardian's First Name", type: 'name', required: false, group: 'guardian' },
                { id: 'guardian_middle_name', label: "Guardian's Middle Name", type: 'name', required: false, group: 'guardian' },
                { id: 'guardian_last_name', label: "Guardian's Last Name", type: 'name', required: false, group: 'guardian' },
                { id: 'guardian_suffix', label: "Guardian's Suffix", type: 'suffix_select', required: false, group: 'guardian', options: SUFFIX_OPTIONS },
                { id: 'guardian_suffix_other', label: "Guardian's Suffix (specify)", type: 'suffix_other', required: false, group: 'guardian', maxLength: SUFFIX_OTHER_MAX, showWhenField: 'guardian_suffix', showWhenValue: SUFFIX_OTHER_VALUE },
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
                { id: 'strand', label: 'Strand', type: 'text', required: true, showWhenEducation: ['High School Level', ...STRAND_COLLEGE_TRACK] },
                { id: 'strand_abbreviation', label: 'Strand / Course Abbreviation', type: 'text', required: true, showWhenEducation: STRAND_COLLEGE_TRACK },
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

    function formatProfileValue(value) {
        if (Array.isArray(value)) {
            return value.map((part) => String(part ?? '').trim()).filter(Boolean).join(', ');
        }
        return String(value ?? '').trim();
    }

    function toUpperAnswer(value) {
        const formatted = formatProfileValue(value);
        return formatted ? formatted.toUpperCase() : '';
    }

    function formatOccupationDisplay(occupation, other) {
        const selected = String(occupation || '').trim();
        const otherText = String(other || '').trim();
        if (selected === OCCUPATION_OTHER_VALUE && otherText) {
            return toUpperAnswer(otherText);
        }
        return selected ? toUpperAnswer(selected) : '';
    }

    function resolveOccupationFromValues(prefix, values) {
        const occupation = values[`${prefix}_occupation`] || '';
        const other = values[`${prefix}_occupation_other`] || '';
        return formatOccupationDisplay(occupation, other);
    }

    function getFieldDisplayLabel(field, kkEducation) {
        const education = normalizeEducation(kkEducation);
        if (field.id === 'strand' && STRAND_COLLEGE_TRACK.includes(education)) {
            return 'Strand / Course';
        }
        return field.label;
    }

    function shouldUppercaseField(field) {
        return UPPERCASE_FIELD_TYPES.has(field.type);
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

        if (field.id === 'strand_abbreviation') {
            return STRAND_COLLEGE_TRACK.includes(education);
        }

        if (field.id.endsWith('_occupation_other')) {
            const occupationKey = field.id.replace(/_other$/, '');
            return (values[occupationKey] || '') === OCCUPATION_OTHER_VALUE;
        }

        if (field.id.endsWith('_suffix_other')) {
            const suffixKey = field.id.replace(/_other$/, '');
            return (values[suffixKey] || '') === SUFFIX_OTHER_VALUE;
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

    function validateSchoolText(value, required, label, minLength = SCHOOL_TEXT_MIN, maxLength = SCHOOL_TEXT_MAX) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return required ? `${label} is required.` : '';
        if (trimmed.length < minLength) return `${label} must be at least ${minLength} characters.`;
        if (trimmed.length > maxLength) return `${label} must not exceed ${maxLength} characters.`;
        return '';
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

    function validateSuffixOther(value, required) {
        const trimmed = String(value || '').trim();
        if (!trimmed) return required ? 'Please specify the suffix.' : '';
        if (trimmed.length > SUFFIX_OTHER_MAX) return `Suffix must not exceed ${SUFFIX_OTHER_MAX} characters.`;
        if (!NAME_PATTERN.test(trimmed)) return 'Letters, spaces, periods, and hyphens only.';
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

    function validateField(field, value, visible) {
        if (!visible) return '';
        if (field.id.endsWith('_occupation_other')) {
            return validateSchoolText(
                value,
                field.required,
                field.label,
                field.minLength || OCCUPATION_OTHER_MIN,
                field.maxLength || OCCUPATION_OTHER_MAX,
            );
        }
        if (SCHOOL_TEXT_FIELD_IDS.has(field.id) || (field.minLength && field.maxLength && !field.id.endsWith('_occupation_other') && field.type !== 'suffix_other')) {
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
        if (field.type === 'suffix_select') {
            if (field.required && !String(value || '').trim()) return `${field.label} is required.`;
            return '';
        }
        if (field.type === 'suffix_other') return validateSuffixOther(value, field.required);
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
        container.innerHTML = `
            <div class="schol-system-section schol-system-section-kk">
                <div class="schol-system-section-head">
                    <h5>1. Personal Information</h5>
                    <span class="schol-system-lock-badge">KK Profiling · Auto-filled</span>
                </div>
                <p class="schol-system-section-desc">Pulled from the applicant's KK Profiling record. Select which fields to display on the Kabataan application form.</p>
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
            <div class="schol-system-section schol-system-section-req">
                <div class="schol-system-section-head">
                    <h5>5. Uploading of Requirements</h5>
                    <span class="schol-system-lock-badge">Custom · File Upload Questions</span>
                </div>
                <p class="schol-system-section-desc">Add PDF file upload requirements below using the Requirements builder. Each item becomes a required document for applicants.</p>
            </div>
        `;
    }

    function renderFieldInput(field, values, kkEducation) {
        const visible = isFieldVisible(field, values, kkEducation);
        const required = field.required && visible;
        const rawValue = values[field.id] || '';
        const value = shouldUppercaseField(field) ? toUpperAnswer(rawValue) : rawValue;
        const fieldLabel = getFieldDisplayLabel(field, kkEducation);
        const reqMark = required ? ' <span class="gf-req">*</span>' : '';
        const hiddenAttr = visible ? '' : 'hidden';
        const uppercaseClass = shouldUppercaseField(field) ? ' schol-system-uppercase' : '';

        if (field.type === 'radio') {
            return `
                <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                    <label>${escapeHtml(fieldLabel)}${reqMark}</label>
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

        if (field.type === 'select' || field.type === 'suffix_select') {
            return `
                <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                    <label for="sys_${field.id}">${escapeHtml(fieldLabel)}${reqMark}</label>
                    <select class="gf-input schol-system-input" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" ${required ? 'required' : ''}>
                        <option value="">Select...</option>
                        ${(field.options || []).map((opt) => `<option value="${escapeHtml(opt)}" ${value === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('')}
                    </select>
                </div>`;
        }

        if (field.type === 'suffix_other') {
            return `
                <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                    <label for="sys_${field.id}">${escapeHtml(fieldLabel)}${reqMark}</label>
                    <input class="gf-input schol-system-input schol-system-uppercase" type="text" name="${field.id}" id="sys_${field.id}" data-system-field="${field.id}" data-field-type="suffix_other" value="${escapeHtml(toUpperAnswer(rawValue))}" maxlength="${field.maxLength || SUFFIX_OTHER_MAX}" ${required ? 'required' : ''} autocomplete="off" placeholder="Specify suffix (max ${field.maxLength || SUFFIX_OTHER_MAX} chars)">
                </div>`;
        }

        const inputType = field.type === 'number' || field.type === 'year' ? 'number' : 'text';
        const extraAttrs = field.type === 'year' ? 'min="1950" max="2100" step="1"' : '';
        const lengthAttrs = field.minLength && field.maxLength
            ? `minlength="${field.minLength}" maxlength="${field.maxLength}"`
            : '';
        const inputClass = field.type === 'currency'
            ? 'gf-input schol-system-input schol-system-currency'
            : `gf-input schol-system-input${uppercaseClass}`;
        const displayValue = field.type === 'currency' ? formatCurrencyDisplay(value) : escapeHtml(value);

        return `
            <div class="gf-form-group schol-system-field-wrap" data-field-wrap="${field.id}" ${hiddenAttr}>
                <label for="sys_${field.id}">${escapeHtml(fieldLabel)}${reqMark}</label>
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
                    const raw = parseCurrencyValue(el.value);
                    el.value = raw ? formatCurrencyDisplay(raw) : '';
                    el.setSelectionRange(el.value.length, el.value.length);
                } else if (UPPERCASE_FIELD_TYPES.has(el.dataset.fieldType)) {
                    el.value = el.value.toUpperCase();
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
            } else if (UPPERCASE_FIELD_TYPES.has(el.dataset.fieldType)) {
                values[el.name] = toUpperAnswer(el.value);
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
            const occupationKey = `${prefix}_occupation`;
            const otherKey = `${prefix}_occupation_other`;
            if (values[occupationKey] === OCCUPATION_OTHER_VALUE) {
                values[otherKey] = toUpperAnswer(values[otherKey] || '');
            } else {
                values[otherKey] = '';
            }

            const suffixKey = `${prefix}_suffix`;
            const suffixOtherKey = `${prefix}_suffix_other`;
            if (values[suffixKey] === SUFFIX_OTHER_VALUE) {
                values[suffixOtherKey] = toUpperAnswer(values[suffixOtherKey] || '');
            } else {
                values[suffixOtherKey] = '';
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
            // Skip suffix_other fields here — they're validated via isFieldVisible + validateField
            const err = validateField(field, values[field.id], visible);
            if (err) errors.push({ field: field.id, label: field.label, message: err });
        });

        return { ok: errors.length === 0, errors, values };
    }

    function validatePersonalProfile(profile = {}) {
        const errors = [];
        const value = (key) => {
            if (key === 'city') {
                return formatProfileValue(profile.city || profile.city_municipality);
            }
            return formatProfileValue(profile[key]);
        };

        const requireField = (key, label) => {
            const raw = key === 'city' ? (profile.city || profile.city_municipality) : profile[key];
            const formatted = formatProfileValue(raw);
            if (!formatted) {
                errors.push({ field: key, label, message: `${label} is required.` });
                return;
            }
            if (key !== 'suffix' && ['n/a', 'na', '—', '-', 'null'].includes(formatted.toLowerCase())) {
                errors.push({ field: key, label, message: `${label} is required.` });
            }
        };

        [
            ['first_name', 'First Name'],
            ['last_name', 'Last Name'],
            ['suffix', 'Suffix'],
            ['birthday', 'Birth Date'],
            ['age', 'Age'],
            ['sex', 'Sex'],
            ['civil_status', 'Civil Status'],
            ['contact_number', 'Contact Number'],
            ['email', 'Email Address'],
            ['purok_zone', 'Purok / Zone'],
            ['province', 'Province'],
            ['city', 'City/Municipality'],
            ['barangay', 'Barangay'],
            ['education', 'Education'],
            ['youth_classification', 'Youth Classification'],
            ['youth_age_group', 'Youth Age Group'],
        ].forEach(([key, label]) => requireField(key, label));

        const firstNameErr = validateName(value('first_name'), true);
        if (firstNameErr) errors.push({ field: 'first_name', label: 'First Name', message: firstNameErr });

        const lastNameErr = validateName(value('last_name'), true);
        if (lastNameErr) errors.push({ field: 'last_name', label: 'Last Name', message: lastNameErr });

        const middleNameErr = validateName(value('middle_name'), false);
        if (middleNameErr) errors.push({ field: 'middle_name', label: 'Middle Name', message: middleNameErr });

        const suffixValue = value('suffix');
        if (suffixValue && suffixValue.toLowerCase() !== 'none') {
            const suffixErr = validateSuffix(suffixValue);
            if (suffixErr) errors.push({ field: 'suffix', label: 'Suffix', message: suffixErr });
        }

        const contactErr = validateContact(value('contact_number'), true);
        if (contactErr) errors.push({ field: 'contact_number', label: 'Contact Number', message: contactErr });

        const email = value('email');
        if (email && !EMAIL_PATTERN.test(email)) {
            errors.push({ field: 'email', label: 'Email Address', message: 'Use a valid Gmail address (6–30 characters before @gmail.com).' });
        }

        const age = Number.parseInt(value('age'), 10);
        if (value('age') && (Number.isNaN(age) || age < 15 || age > 30)) {
            errors.push({ field: 'age', label: 'Age', message: 'Age must be between 15 and 30.' });
        }

        return { ok: errors.length === 0, errors };
    }

    global.ScholarshipSystemFields = {
        SECTIONS,
        DEFAULT_KK_FIELDS,
        KK_FIELD_LABELS,
        PERSONAL_ALWAYS_SHOW_KEYS,
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
        validatePersonalProfile,
        validateSchoolText,
        getFieldDisplayLabel,
        formatOccupationDisplay,
        resolveOccupationFromValues,
        FAMILY_MONTHLY_INCOME_OPTIONS,
        OCCUPATION_OPTIONS,
        OCCUPATION_OTHER_VALUE,
        SUFFIX_OPTIONS,
        SUFFIX_OTHER_VALUE,
        SUFFIX_OTHER_MAX,
        toUpperAnswer,
        formatProfileValue,
        STRAND_COLLEGE_TRACK,
    };
})(window);
