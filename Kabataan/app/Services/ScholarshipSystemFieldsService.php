<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ScholarshipSystemFieldsService
{
    private const NAME_REGEX = '/^(?!\s)[A-Za-z.\-\s]+$/';

    private const CONTACT_REGEX = '/^09\d{9}$/';

    /** @var list<string> */
    private const ELEMENTARY_ONLY = ['Elementary Level', 'Elementary Grad'];

    /** @var list<string> */
    private const HIGH_SCHOOL_TRACK = ['High School Grad', 'High School Level'];

    /** @var list<string> */
    private const COLLEGE_TRACK = ['College Level', 'College Grad', 'Vocational Grad', 'Masters Level', 'Masters Grad', 'Doctorate Level', 'Doctorate Graduate'];

    /** @var list<string> */
    private const ADDITIONAL_EDUCATION = ['High School Level', 'College Level', 'College Grad', 'Vocational Grad'];

    /**
     * @return list<array{id: string, label: string, type: string, required: bool, section: string}>
     */
    public static function fieldDefinitions(): array
    {
        return [
            ['id' => 'elementary_school', 'label' => 'Elementary School', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'elementary_address', 'label' => 'Address', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'elementary_year_graduated', 'label' => 'Year Graduated', 'type' => 'year', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'secondary_school', 'label' => 'Secondary School', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'secondary_address', 'label' => 'Address', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'secondary_year_graduated', 'label' => 'Year Graduated', 'type' => 'year', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'senior_high_school', 'label' => 'Senior High School', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'senior_high_address', 'label' => 'Address', 'type' => 'text', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'senior_high_year_graduated', 'label' => 'Year Graduated', 'type' => 'year', 'required' => true, 'section' => 'educational_background'],
            ['id' => 'mother_first_name', 'label' => "Mother's First Name", 'type' => 'name', 'required' => true, 'section' => 'background_information'],
            ['id' => 'mother_middle_name', 'label' => "Mother's Middle Name", 'type' => 'name', 'required' => false, 'section' => 'background_information'],
            ['id' => 'mother_last_name', 'label' => "Mother's Last Name", 'type' => 'name', 'required' => true, 'section' => 'background_information'],
            ['id' => 'mother_suffix', 'label' => "Mother's Suffix", 'type' => 'suffix', 'required' => false, 'section' => 'background_information'],
            ['id' => 'mother_occupation', 'label' => "Mother's Occupation", 'type' => 'text', 'required' => true, 'section' => 'background_information'],
            ['id' => 'mother_contact_number', 'label' => "Mother's Contact No.", 'type' => 'contact', 'required' => true, 'section' => 'background_information'],
            ['id' => 'father_first_name', 'label' => "Father's First Name", 'type' => 'name', 'required' => true, 'section' => 'background_information'],
            ['id' => 'father_middle_name', 'label' => "Father's Middle Name", 'type' => 'name', 'required' => false, 'section' => 'background_information'],
            ['id' => 'father_last_name', 'label' => "Father's Last Name", 'type' => 'name', 'required' => true, 'section' => 'background_information'],
            ['id' => 'father_suffix', 'label' => "Father's Suffix", 'type' => 'suffix', 'required' => false, 'section' => 'background_information'],
            ['id' => 'father_occupation', 'label' => "Father's Occupation", 'type' => 'text', 'required' => true, 'section' => 'background_information'],
            ['id' => 'father_contact_number', 'label' => "Father's Contact No.", 'type' => 'contact', 'required' => true, 'section' => 'background_information'],
            ['id' => 'guardian_first_name', 'label' => "Guardian's First Name", 'type' => 'name', 'required' => false, 'section' => 'background_information'],
            ['id' => 'guardian_middle_name', 'label' => "Guardian's Middle Name", 'type' => 'name', 'required' => false, 'section' => 'background_information'],
            ['id' => 'guardian_last_name', 'label' => "Guardian's Last Name", 'type' => 'name', 'required' => false, 'section' => 'background_information'],
            ['id' => 'guardian_suffix', 'label' => "Guardian's Suffix", 'type' => 'suffix', 'required' => false, 'section' => 'background_information'],
            ['id' => 'guardian_relation', 'label' => 'Relation to Guardian', 'type' => 'text', 'required' => false, 'section' => 'background_information'],
            ['id' => 'guardian_contact_number', 'label' => "Guardian's Contact No.", 'type' => 'contact', 'required' => false, 'section' => 'background_information'],
            ['id' => 'annual_family_gross_income', 'label' => 'Annual Family Gross Income', 'type' => 'currency', 'required' => true, 'section' => 'background_information'],
            ['id' => 'strand', 'label' => 'Strand / Course', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'strand_abbreviation', 'label' => 'Strand / Course Abbreviation', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'year_level', 'label' => 'Year Level', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'units_enrolled', 'label' => 'Units Enrolled', 'type' => 'number', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'expected_graduation_year', 'label' => 'Expected Year of Graduation', 'type' => 'year', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'graduating', 'label' => 'Graduating?', 'type' => 'radio', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'semester_of_graduation', 'label' => 'Semester of Graduation', 'type' => 'text', 'required' => false, 'section' => 'additional_information'],
            ['id' => 'school_name', 'label' => 'School Name', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'school_abbreviation', 'label' => 'School Abbreviation', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'school_address', 'label' => 'School Address', 'type' => 'text', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'receiving_gov_aid', 'label' => 'Government scholarship recipient', 'type' => 'radio', 'required' => true, 'section' => 'additional_information'],
            ['id' => 'gov_aid_program_name', 'label' => 'Government aid program name', 'type' => 'text', 'required' => false, 'section' => 'additional_information'],
            ['id' => 'family_on_scholarship', 'label' => 'Family on scholarship program', 'type' => 'radio', 'required' => true, 'section' => 'additional_information'],
        ];
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    public function validate(array $answers, string $kkEducation): array
    {
        $normalized = [];
        foreach ($answers as $key => $value) {
            $normalized[(string) $key] = is_string($value) ? trim($value) : $value;
        }

        $errors = [];

        foreach (self::fieldDefinitions() as $field) {
            if (! $this->isFieldVisible($field['id'], $normalized, $kkEducation)) {
                continue;
            }

            $value = $normalized[$field['id']] ?? '';
            $message = $this->validateField($field, $value);

            if ($message !== '') {
                $errors[$field['id']] = [$message];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if (($normalized['graduating'] ?? '') !== 'Yes') {
            $normalized['semester_of_graduation'] = 'N/A';
        }

        if (($normalized['receiving_gov_aid'] ?? '') !== 'Yes') {
            $normalized['gov_aid_program_name'] = '';
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function isFieldVisible(string $fieldId, array $values, string $education): bool
    {
        $education = trim($education);

        if (str_starts_with($fieldId, 'elementary_')) {
            return $this->isSchoolBlockVisible('elementary', $education);
        }

        if (str_starts_with($fieldId, 'secondary_')) {
            return $this->isSchoolBlockVisible('secondary', $education);
        }

        if (str_starts_with($fieldId, 'senior_high_')) {
            return $this->isSchoolBlockVisible('senior_high', $education);
        }

        $additionalIds = [
            'strand', 'strand_abbreviation', 'year_level', 'units_enrolled',
            'expected_graduation_year', 'graduating', 'semester_of_graduation',
            'school_name', 'school_abbreviation', 'school_address',
        ];

        if (in_array($fieldId, $additionalIds, true)) {
            return in_array($education, self::ADDITIONAL_EDUCATION, true);
        }

        if ($fieldId === 'semester_of_graduation') {
            return ($values['graduating'] ?? '') === 'Yes';
        }

        if ($fieldId === 'gov_aid_program_name') {
            return ($values['receiving_gov_aid'] ?? '') === 'Yes';
        }

        return true;
    }

    private function isSchoolBlockVisible(string $blockId, string $education): bool
    {
        if ($education === '') {
            return false;
        }

        if ($blockId === 'elementary') {
            return in_array($education, self::ELEMENTARY_ONLY, true)
                || in_array($education, self::HIGH_SCHOOL_TRACK, true)
                || in_array($education, self::COLLEGE_TRACK, true);
        }

        if ($blockId === 'secondary') {
            return in_array($education, self::HIGH_SCHOOL_TRACK, true)
                || in_array($education, self::COLLEGE_TRACK, true);
        }

        if ($blockId === 'senior_high') {
            return $education === 'High School Level'
                || in_array($education, self::COLLEGE_TRACK, true);
        }

        return false;
    }

    /**
     * @param  array{id: string, label: string, type: string, required: bool}  $field
     */
    private function validateField(array $field, mixed $value): string
    {
        $stringValue = trim((string) ($value ?? ''));

        if ($field['type'] === 'name') {
            if ($stringValue === '' && $field['required']) {
                return "{$field['label']} is required.";
            }
            if ($stringValue !== '' && (strlen($stringValue) < 3 || strlen($stringValue) > 50)) {
                return "{$field['label']} must be 3–50 characters.";
            }
            if ($stringValue !== '' && ! preg_match(self::NAME_REGEX, $stringValue)) {
                return "{$field['label']} has an invalid format.";
            }

            return '';
        }

        if ($field['type'] === 'suffix') {
            if ($stringValue !== '' && strlen($stringValue) > 10) {
                return "{$field['label']} is too long.";
            }
            if ($stringValue !== '' && ! preg_match(self::NAME_REGEX, $stringValue)) {
                return "{$field['label']} has an invalid format.";
            }

            return '';
        }

        if ($field['type'] === 'contact') {
            if ($stringValue === '' && $field['required']) {
                return "{$field['label']} is required.";
            }
            if ($stringValue !== '' && ! preg_match(self::CONTACT_REGEX, $stringValue)) {
                return "{$field['label']} must use format 09XXXXXXXXX.";
            }

            return '';
        }

        if ($field['type'] === 'currency') {
            $digits = preg_replace('/\D+/', '', $stringValue) ?? '';
            if ($digits === '' && $field['required']) {
                return "{$field['label']} is required.";
            }
            if ($digits !== '' && strlen($digits) > 10) {
                return "{$field['label']} allows a maximum of 10 digits.";
            }

            return '';
        }

        if ($field['type'] === 'year') {
            if ($stringValue === '' && $field['required']) {
                return "{$field['label']} is required.";
            }
            if ($stringValue !== '' && ! preg_match('/^\d{4}$/', $stringValue)) {
                return "{$field['label']} must be a valid 4-digit year.";
            }

            return '';
        }

        if ($field['required'] && $stringValue === '') {
            return "{$field['label']} is required.";
        }

        return '';
    }
}
