<?php

namespace App\Modules\Program_Management\Services;

use App\Models\Abyip;
use App\Models\Committee;
use App\Models\ProgramApplication;
use App\Models\ScheduleProgram;
use App\Models\ScholarshipQuickGuidelineStep;
use App\Models\User;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ScheduleProgramService
{
    public const LETTER_EDUCATION = 'A';

    public const LETTER_SPORTS = 'I';

    /** @var list<string> */
    private const ELIGIBILITY_YOUTH_CLASSIFICATIONS = [
        'In School Youth',
        'Out of School Youth',
        'Working Youth',
        'Person w/ Disability',
        'Children in Conflict w/ Law',
        'Indigenous People',
    ];

    /** @var list<string> */
    private const ELIGIBILITY_YOUTH_AGE_GROUPS = [
        'Child Youth (15-17 yrs old)',
        'Core Youth (18-24 yrs old)',
        'Young Adult (15-30 yrs old)',
    ];

    /** @var list<string> */
    private const ELIGIBILITY_EDUCATION_LEVELS = [
        'High School Level',
        'High School Grad',
        'College Level',
    ];

    /** @var list<string> */
    public const DEFAULT_SCHOLARSHIP_YOUTH_CLASSIFICATIONS = [
        'In School Youth',
    ];

    /** @var list<string> */
    public const DEFAULT_SCHOLARSHIP_EDUCATION_LEVELS = [
        'High School Level',
        'High School Grad',
        'College Level',
    ];

    /** @var list<string> */
    public const ALLOWED_SCHOLARSHIP_TARGET_LEVELS = [
        'senior_high',
        'college',
    ];

    /** @var list<string> */
    private const ALLOWED_SEMESTERS = [
        '1st Semester',
        '2nd Semester',
    ];

    /** @var list<string> */
    private const ALLOWED_APPLICATION_TYPES = [
        'new_only',
        'renewal_only',
        'both',
    ];

    /** @var list<string> */
    private const ALLOWED_DOCUMENT_FILE_TYPES = [
        'pdf',
        'image',
        'pdf_or_image',
    ];

    /** @var array<string, array{default_type: string, default_committee: string, committee_like: string}> */
    private const LETTER_CONFIG = [
        self::LETTER_EDUCATION => [
            'default_type' => 'Equitable Access to Quality Education',
            'default_committee' => 'Education Committee',
            'committee_like' => '%education%',
        ],
        self::LETTER_SPORTS => [
            'default_type' => 'Sports Development',
            'default_committee' => 'Sports Committee',
            'committee_like' => '%sport%',
        ],
    ];

    /** @var list<string> */
    private const ALLOWED_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix',
        'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
        'region', 'province', 'city', 'barangay', 'purok_zone',
        'youth_classification', 'youth_age_group', 'education', 'current_school',
        'course_strand', 'work_status', 'sk_voter', 'sk_voted',
    ];

    /** @var list<string> */
    public const DEFAULT_SCHOLARSHIP_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix',
        'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
        'region', 'province', 'city', 'barangay', 'purok_zone',
        'youth_classification', 'youth_age_group', 'education',
        'current_school', 'course_strand',
    ];

    /** @var list<string> */
    private const SCHOLARSHIP_EXCLUDED_KK_FIELDS = [
        'work_status',
        'sk_voter',
        'sk_voted',
    ];

    public const SCHOLARSHIP_MAX_BENEFICIARIES = 1000;

    public const SCHOLARSHIP_QUICK_GUIDELINE_MAX_CHARS = 2000;

    public const SCHOLARSHIP_QUICK_GUIDELINE_MAX_STEPS = 10;

    /** @var list<string> */
    public const DEFAULT_SPORTS_KK_FIELDS = [
        'last_name', 'first_name', 'middle_name', 'suffix',
        'birthday', 'age', 'sex', 'civil_status', 'contact_number', 'email',
        'region', 'province', 'city', 'barangay', 'purok_zone',
        'youth_classification', 'youth_age_group',
    ];

    /** @var array<string, mixed> */
    public const DEFAULT_TEAM_NAME_QUESTION = [
        'id' => 'sys_team_name',
        'label' => 'Team Name',
        'type' => 'text',
        'options' => [],
        'required' => true,
        'system_default' => true,
        'field_key' => 'team_name',
    ];

    public const SPORTS_MAX_TEAM_MEMBERS = 12;

    public const SPORT_KEY_BASKETBALL = 'basketball';

    public const SPORT_KEY_VOLLEYBALL = 'volleyball';

    public const SPORT_KEY_OTHER = 'other';

    /** @var array<string, string> */
    public const DEFAULT_SPORT_LABELS = [
        self::SPORT_KEY_BASKETBALL => 'Basketball',
        self::SPORT_KEY_VOLLEYBALL => 'Volleyball',
    ];

    /** @var list<string> */
    public const SPORTS_DISCIPLINE_KEYS = [
        self::SPORT_KEY_BASKETBALL,
        self::SPORT_KEY_VOLLEYBALL,
        self::SPORT_KEY_OTHER,
    ];

    public function __construct(private readonly AbyipProgramCatalogService $catalogService) {}

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveEducationProgramMeta(User $user): array
    {
        return $this->resolveProgramMeta($user, self::LETTER_EDUCATION);
    }

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveSportsProgramMeta(User $user): array
    {
        return $this->resolveProgramMeta($user, self::LETTER_SPORTS);
    }

    /**
     * @return array{program_type: string, committee: string, program_name: string, program_letter: string}
     */
    public function resolveProgramMeta(User $user, string $letter): array
    {
        $letter = strtoupper(trim($letter));
        if (! isset(self::LETTER_CONFIG[$letter])) {
            throw ValidationException::withMessages([
                'program_letter' => ['Unsupported program letter.'],
            ]);
        }

        $config = self::LETTER_CONFIG[$letter];
        $programType = $config['default_type'];
        $abyip = $this->catalogService->getLatestAbyip($user->barangay_id);

        if ($abyip !== null) {
            $program = Abyip::query()
                ->where('document_id', $abyip->id)
                ->where('row_type', Abyip::ROW_YOUTH_PROGRAM)
                ->where('code', $letter)
                ->first();

            if ($program !== null && trim((string) $program->program_name) !== '') {
                $programType = trim((string) $program->program_name);
            }
        }

        $committee = $config['default_committee'];
        if ($user->barangay_id !== null) {
            $committeeRow = Committee::query()
                ->whereHas('head', function ($query) use ($user) {
                    $query->where('barangay_id', $user->barangay_id);
                })
                ->whereRaw('LOWER(committee_name) LIKE ?', [$config['committee_like']])
                ->value('committee_name');

            if (is_string($committeeRow) && trim($committeeRow) !== '') {
                $committee = trim($committeeRow);
            }
        }

        $meta = [
            'program_type' => $programType,
            'committee' => $committee,
            'program_name' => $programType,
            'program_letter' => $letter,
        ];

        if ($letter === self::LETTER_SPORTS) {
            $meta['sports_age_classifications'] = self::sportsAgeClassificationsConfig();
        }

        if ($letter === self::LETTER_EDUCATION) {
            $youthProgram = $this->catalogService->findYouthProgramByLetter($user->barangay_id, $letter);
            $schoolYear = $this->deriveSchoolYearFromYouthProgram($youthProgram);
            $meta['school_year'] = $schoolYear;
            $meta['program_duration'] = $youthProgram !== null ? [
                'start_date' => $youthProgram['start_date'],
                'end_date' => $youthProgram['end_date'],
            ] : null;
            $meta['used_semesters'] = $schoolYear !== null
                ? $this->usedSemestersForSchoolYear($user, $schoolYear)
                : [];
            $meta['default_eligibility'] = self::defaultScholarshipEligibility();
            $meta['committee_head'] = $this->resolveEducationCommitteeHead($user);
        }

        return $meta;
    }

    /**
     * @return array{youth_classifications: list<string>, youth_age_groups: list<string>, education_levels: list<string>}
     */
    public static function scholarshipEligibilityForTargetLevels(array $targetLevels): array
    {
        $educationLevels = [];

        if (in_array('senior_high', $targetLevels, true)) {
            $educationLevels[] = 'High School Level';
        }

        if (in_array('college', $targetLevels, true)) {
            $educationLevels[] = 'College Level';
        }

        return [
            'youth_classifications' => self::DEFAULT_SCHOLARSHIP_YOUTH_CLASSIFICATIONS,
            'youth_age_groups' => [],
            'education_levels' => $educationLevels !== [] ? array_values(array_unique($educationLevels)) : self::DEFAULT_SCHOLARSHIP_EDUCATION_LEVELS,
        ];
    }

    /**
     * @return array{youth_classifications: list<string>, youth_age_groups: list<string>, education_levels: list<string>}
     */
    public static function scholarshipEligibilityForTargetLevel(string $targetLevel): array
    {
        return self::scholarshipEligibilityForTargetLevels([$targetLevel]);
    }

    /**
     * @return array{youth_classifications: list<string>, youth_age_groups: list<string>, education_levels: list<string>}
     */
    public static function defaultScholarshipEligibility(): array
    {
        return [
            'youth_classifications' => self::DEFAULT_SCHOLARSHIP_YOUTH_CLASSIFICATIONS,
            'youth_age_groups' => [],
            'education_levels' => self::DEFAULT_SCHOLARSHIP_EDUCATION_LEVELS,
        ];
    }

    /**
     * @param  array{id: int, program_name: string, letter: string, start_date: string, end_date: string}|null  $youthProgram
     */
    public function deriveSchoolYearFromYouthProgram(?array $youthProgram): ?string
    {
        if ($youthProgram === null) {
            return null;
        }

        $year = (int) date('Y', strtotime((string) ($youthProgram['start_date'] ?? '')));
        if ($year <= 0) {
            return null;
        }

        return sprintf('%d-%d', $year, $year + 1);
    }

    protected function resolveEducationCommitteeHead(User $user): ?string
    {
        if ($user->barangay_id === null) {
            return null;
        }

        $committee = Committee::query()
            ->with('head.officialProfile')
            ->whereHas('head', fn ($query) => $query->where('barangay_id', $user->barangay_id))
            ->whereRaw('LOWER(committee_name) LIKE ?', [self::LETTER_CONFIG[self::LETTER_EDUCATION]['committee_like']])
            ->first();

        if ($committee?->head === null) {
            return null;
        }

        return $this->formatOfficialDisplayName($committee->head);
    }

    protected function formatOfficialDisplayName(User $user): string
    {
        $profile = $user->officialProfile;
        if ($profile !== null) {
            $middleInitial = $profile->middle_name
                ? mb_strtoupper(mb_substr(trim((string) $profile->middle_name), 0, 1)).'.'
                : null;
            $formatted = trim(implode(' ', array_filter([
                $profile->first_name ? mb_strtoupper((string) $profile->first_name, 'UTF-8') : null,
                $middleInitial,
                $profile->last_name ? mb_strtoupper((string) $profile->last_name, 'UTF-8') : null,
                $profile->suffix,
            ])));

            if ($formatted !== '') {
                return $formatted;
            }
        }

        return trim((string) $user->name);
    }

    public function resolveSchoolYearForBarangay(?int $barangayId, string $letter = self::LETTER_EDUCATION): ?string
    {
        return $this->deriveSchoolYearFromYouthProgram(
            $this->catalogService->findYouthProgramByLetter($barangayId, $letter)
        );
    }

    /**
     * @return list<string>
     */
    public function usedSemestersForSchoolYear(User $user, string $schoolYear, ?int $ignoreProgramId = null): array
    {
        if ($user->barangay_id === null || $schoolYear === '') {
            return [];
        }

        return ScheduleProgram::query()
            ->where('barangay_id', $user->barangay_id)
            ->active()
            ->when(
                Schema::hasColumn('schedule_programs', 'program_letter'),
                fn ($query) => $query->where('program_letter', self::LETTER_EDUCATION),
                fn ($query) => $query->whereRaw('LOWER(committee) LIKE ?', ['%education%']),
            )
            ->when($ignoreProgramId !== null, fn ($query) => $query->where('id', '!=', $ignoreProgramId))
            ->get()
            ->filter(function (ScheduleProgram $program) use ($schoolYear) {
                $details = is_array($program->scholarship_details) ? $program->scholarship_details : [];

                return ($details['school_year'] ?? '') === $schoolYear;
            })
            ->map(function (ScheduleProgram $program) {
                $details = is_array($program->scholarship_details) ? $program->scholarship_details : [];

                return trim((string) ($details['semester'] ?? ''));
            })
            ->filter(fn (string $semester) => $semester !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForBarangay(User $user, ?string $letter = null): Collection
    {
        if ($user->barangay_id === null) {
            return collect();
        }

        $query = ScheduleProgram::query()
            ->with('quickGuidelineSteps')
            ->where('barangay_id', $user->barangay_id)
            ->active()
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        if ($letter !== null && $letter !== '') {
            $letter = strtoupper($letter);

            if (Schema::hasColumn('schedule_programs', 'program_letter')) {
                $query->where('program_letter', $letter);
            } else {
                $config = self::LETTER_CONFIG[$letter] ?? null;
                if ($config !== null) {
                    $query->whereRaw('LOWER(committee) LIKE ?', [$config['committee_like']]);
                }
            }
        }

        return $query->get()->map(fn (ScheduleProgram $program) => $this->formatProgram($program));
    }

    /**
     * @return array<string, mixed>
     */
    public function findForBarangay(User $user, int $programId): array
    {
        return $this->formatProgram($this->findModel($user, $programId));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function store(User $user, array $data, string $letter = self::LETTER_EDUCATION): array
    {
        $validated = $this->validatePayload($data, $letter, null, $user);
        $meta = $this->resolveProgramMeta($user, $letter);

        if ($user->barangay_id !== null) {
            $existsQuery = ScheduleProgram::query()
                ->where('barangay_id', $user->barangay_id)
                ->active();

            if (Schema::hasColumn('schedule_programs', 'program_letter')) {
                $existsQuery->where('program_letter', $meta['program_letter']);
            } elseif ($letter === self::LETTER_SPORTS) {
                $existsQuery->whereRaw('LOWER(committee) LIKE ?', ['%sport%']);
            } else {
                $existsQuery->whereRaw('LOWER(committee) LIKE ?', ['%education%']);
            }

            if ($letter === self::LETTER_SPORTS) {
                $sportKey = (string) ($validated['sports_details']['sport_key'] ?? '');
                $year = (int) date('Y', strtotime((string) $validated['start_date']));

                if ($sportKey !== '' && $this->sportsProgramExistsForYear(
                    (int) $user->barangay_id,
                    $meta['program_letter'],
                    $sportKey,
                    $year,
                )) {
                    $label = (string) ($validated['sports_details']['sport_label'] ?? $sportKey);
                    throw ValidationException::withMessages([
                        'program' => ["A {$label} program already exists for {$year}. Edit the existing program instead."],
                    ]);
                }
            } elseif ($letter === self::LETTER_EDUCATION) {
                $schoolYear = (string) ($validated['scholarship_details']['school_year'] ?? '');
                $semester = (string) ($validated['scholarship_details']['semester'] ?? '');

                if ($this->scholarshipProgramExistsForYearSemester(
                    (int) $user->barangay_id,
                    $schoolYear,
                    $semester,
                )) {
                    throw ValidationException::withMessages([
                        'program' => ["A scholarship program already exists for {$schoolYear} ({$semester}). Edit the existing program instead."],
                    ]);
                }
            } elseif ($existsQuery->exists()) {
                $message = 'A program already exists for this barangay. Edit the existing program instead of creating a new one.';

                throw ValidationException::withMessages([
                    'program' => [$message],
                ]);
            }
        }

        $program = ScheduleProgram::create([
            'tenant_id' => $user->tenant_id,
            'barangay_id' => $user->barangay_id,
            'created_by' => $user->id,
            'program_letter' => $meta['program_letter'],
            'program_type' => $meta['program_type'],
            'committee' => $meta['committee'],
            'program_name' => $meta['program_name'],
            'participation_quantity' => $validated['participation_quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'announcement' => $validated['announcement'],
            'scholarship_details' => $validated['scholarship_details'],
            'sports_details' => $validated['sports_details'],
            'kk_profiling_fields' => $validated['kk_profiling_fields'],
            'custom_questions' => $validated['custom_questions'],
        ]);

        $this->syncQuickGuidelineSteps($program, $validated['quick_guidelines'] ?? []);

        return $this->formatProgram($program->fresh(['quickGuidelineSteps']));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $user, int $programId, array $data, ?string $letter = null): array
    {
        $program = $this->findModel($user, $programId);
        $validated = $this->validatePayload(
            $data,
            $letter ?? (string) ($program->program_letter ?? self::LETTER_EDUCATION),
            $programId,
            $user,
        );
        $meta = $this->resolveProgramMeta($user, $letter ?? (string) ($program->program_letter ?? self::LETTER_EDUCATION));

        $program->update([
            'program_letter' => $meta['program_letter'],
            'program_type' => $meta['program_type'],
            'committee' => $meta['committee'],
            'program_name' => $meta['program_name'],
            'participation_quantity' => $validated['participation_quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'announcement' => $validated['announcement'],
            'scholarship_details' => $validated['scholarship_details'],
            'sports_details' => $validated['sports_details'],
            'kk_profiling_fields' => $validated['kk_profiling_fields'],
            'custom_questions' => $validated['custom_questions'],
        ]);

        $this->syncQuickGuidelineSteps($program, $validated['quick_guidelines'] ?? []);

        return $this->formatProgram($program->fresh(['quickGuidelineSteps']));
    }

    public function delete(User $user, int $programId): void
    {
        $this->findModel($user, $programId)->delete();
    }

    protected function findModel(User $user, int $programId): ScheduleProgram
    {
        $program = ScheduleProgram::query()
            ->with('quickGuidelineSteps')
            ->where('id', $programId)
            ->where('barangay_id', $user->barangay_id)
            ->active()
            ->first();

        if ($program === null) {
            throw ValidationException::withMessages([
                'program' => ['Schedule program not found.'],
            ]);
        }

        return $program;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     participation_quantity: ?int,
     *     start_date: string,
     *     end_date: string,
     *     status: string,
     *     announcement: ?string,
     *     kk_profiling_fields: list<string>,
     *     custom_questions: list<array<string, mixed>>,
     *     sports_details: ?array<string, mixed>
     * }
     */
    protected function validatePayload(array $data, ?string $letter = null, ?int $programId = null, ?User $user = null): array
    {
        $letter = strtoupper(trim((string) ($letter ?? $data['program_letter'] ?? '')));
        $isScholarshipPayload = $letter === self::LETTER_EDUCATION
            && isset($data['scholarship_details'])
            && is_array($data['scholarship_details']);
        $scholarshipDetails = $this->sanitizeScholarshipDetails($data['scholarship_details'] ?? null);

        if ($isScholarshipPayload) {
            $startDate = (string) ($scholarshipDetails['submission_period']['start'] ?? '');
            $endDate = (string) ($scholarshipDetails['submission_period']['end'] ?? '');
        } else {
            $startDate = (string) ($data['start_date'] ?? '');
            $endDate = (string) ($data['end_date'] ?? '');
        }

        $status = (string) ($data['status'] ?? ScheduleProgram::STATUS_OPEN);

        if ($startDate === '' || $endDate === '') {
            throw ValidationException::withMessages([
                $isScholarshipPayload ? 'scholarship_details' : 'start_date' => [
                    $isScholarshipPayload
                        ? 'Submission period start and end dates are required.'
                        : 'Start date and end date are required.',
                ],
            ]);
        }

        if ($endDate < $startDate) {
            throw ValidationException::withMessages([
                'end_date' => ['End date cannot be before start date.'],
            ]);
        }

        if (! in_array($status, [ScheduleProgram::STATUS_OPEN, ScheduleProgram::STATUS_CLOSED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Status must be open or closed.'],
            ]);
        }

        $qtyRaw = $data['participation_quantity'] ?? null;
        $participationQuantity = null;
        if ($qtyRaw !== null && $qtyRaw !== '') {
            $qty = (int) $qtyRaw;
            if ($letter === self::LETTER_EDUCATION) {
                if ($qty < 0 || $qty > self::SCHOLARSHIP_MAX_BENEFICIARIES) {
                    throw ValidationException::withMessages([
                        'participation_quantity' => ['Maximum beneficiaries must be between 0 and '.self::SCHOLARSHIP_MAX_BENEFICIARIES.'.'],
                    ]);
                }
            } elseif ($qty < 0 || $qty > 500) {
                throw ValidationException::withMessages([
                    'participation_quantity' => ['Participation quantity must be between 0 and 500.'],
                ]);
            }
            $participationQuantity = $qty;
        }

        $kkFields = array_values(array_filter(
            (array) ($data['kk_profiling_fields'] ?? []),
            fn ($field) => in_array((string) $field, self::ALLOWED_KK_FIELDS, true)
        ));

        if ($letter === self::LETTER_EDUCATION) {
            $kkFields = array_values(array_filter(
                $kkFields,
                fn ($field) => ! in_array($field, self::SCHOLARSHIP_EXCLUDED_KK_FIELDS, true)
            ));
        }

        if ($letter === self::LETTER_SPORTS && $kkFields === []) {
            $kkFields = self::DEFAULT_SPORTS_KK_FIELDS;
        }

        if ($letter === self::LETTER_EDUCATION && $kkFields === []) {
            $kkFields = self::DEFAULT_SCHOLARSHIP_KK_FIELDS;
        }

        $customQuestions = $this->sanitizeCustomQuestions((array) ($data['custom_questions'] ?? []));

        if ($letter === self::LETTER_SPORTS) {
            $customQuestions = $this->ensureDefaultTeamNameQuestion($customQuestions);
        }

        if ($letter === self::LETTER_EDUCATION) {
            $customQuestions = collect($customQuestions)
                ->reject(fn ($question) => ($question['type'] ?? '') === 'file')
                ->values()
                ->all();
        }

        $sportsDetails = $letter === self::LETTER_SPORTS
            ? $this->sanitizeSportsDetails($data['sports_details'] ?? null)
            : null;

        $quickGuidelines = [];

        if ($isScholarshipPayload && $user !== null) {
            $expectedSchoolYear = $this->resolveSchoolYearForBarangay($user->barangay_id);
            if ($expectedSchoolYear === null) {
                throw ValidationException::withMessages([
                    'scholarship_details' => ['Set the program duration in Programs before creating a scholarship schedule.'],
                ]);
            }

            $scholarshipDetails['school_year'] = $expectedSchoolYear;
            $targetLevels = $this->resolveScholarshipTargetLevels($scholarshipDetails);
            if ($targetLevels === []) {
                throw ValidationException::withMessages([
                    'scholarship_details' => ['Please select at least one scholarship level (Senior High and/or College).'],
                ]);
            }

            $scholarshipDetails['scholarship_target_levels'] = $targetLevels;
            unset($scholarshipDetails['scholarship_target_level']);
            $scholarshipDetails['eligibility'] = self::scholarshipEligibilityForTargetLevels($targetLevels);
            $scholarshipDetails['committee_head'] = $this->resolveEducationCommitteeHead($user);
            $rawScholarshipDetails = is_array($data['scholarship_details'] ?? null)
                ? $data['scholarship_details']
                : [];
            $quickGuidelines = self::sanitizeQuickGuidelines(
                $rawScholarshipDetails['quick_guidelines'] ?? null
            );
            $this->assertQuickGuidelinesWithinLimit($quickGuidelines);
            unset($scholarshipDetails['quick_guidelines']);

            if (empty($scholarshipDetails['semester'])) {
                throw ValidationException::withMessages([
                    'scholarship_details' => ['School year and semester are required for scholarship programs.'],
                ]);
            }

            $applicationType = (string) ($scholarshipDetails['application_type'] ?? 'new_only');
            if (in_array($applicationType, ['renewal_only', 'both'], true)
                && ! $this->programSupportsRenewalOptions($programId)) {
                throw ValidationException::withMessages([
                    'scholarship_details' => ['Renewal application types are only available after at least one scholar has applied.'],
                ]);
            }
        }

        if ($letter === self::LETTER_SPORTS && $sportsDetails === null) {
            throw ValidationException::withMessages([
                'sports_details' => ['At least one age classification is required for sports programs.'],
            ]);
        }

        return [
            'participation_quantity' => $participationQuantity,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'announcement' => $this->nullableString($data['announcement'] ?? null),
            'scholarship_details' => $scholarshipDetails,
            'quick_guidelines' => $isScholarshipPayload ? ($quickGuidelines ?? []) : [],
            'sports_details' => $sportsDetails,
            'kk_profiling_fields' => $kkFields,
            'custom_questions' => $customQuestions,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function sanitizeScholarshipDetails(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $groups = [];
        foreach ((array) ($raw['requirement_groups'] ?? []) as $group) {
            if (! is_array($group)) {
                continue;
            }

            $title = trim((string) ($group['title'] ?? ''));
            $items = array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                (array) ($group['items'] ?? [])
            )));

            if ($title === '' && $items === []) {
                continue;
            }

            $groups[] = [
                'title' => $title !== '' ? $title : 'Requirements',
                'items' => $items,
            ];
        }

        $submission = $this->sanitizePeriod($raw['submission_period'] ?? null);
        $verification = $this->sanitizePeriod($raw['verification_period'] ?? null);
        $targetLevels = $this->resolveScholarshipTargetLevels($raw);
        $eligibility = $targetLevels !== []
            ? self::scholarshipEligibilityForTargetLevels($targetLevels)
            : self::defaultScholarshipEligibility();
        $quickGuidelines = self::sanitizeQuickGuidelines($raw['quick_guidelines'] ?? null);
        $committeeHead = $this->nullableString($raw['committee_head'] ?? null);
        $schoolYear = $this->sanitizeSchoolYear($raw['school_year'] ?? null);
        $semester = $this->sanitizeSemester($raw['semester'] ?? null);
        $applicationType = $this->sanitizeApplicationType($raw['application_type'] ?? null);
        $programDescription = $this->nullableString($raw['program_description'] ?? null);
        $documentRequirements = $this->sanitizeDocumentRequirements($raw['document_requirements'] ?? null);

        if (
            $groups === []
            && $submission === null
            && $verification === null
            && $schoolYear === null
            && $semester === null
            && $applicationType === null
            && $targetLevels === []
            && $quickGuidelines === []
            && $committeeHead === null
            && $programDescription === null
            && $documentRequirements === []
        ) {
            return null;
        }

        $payload = [
            'requirement_groups' => $groups,
            'submission_period' => $submission,
            'verification_period' => $verification,
            'eligibility' => $eligibility,
        ];

        if ($schoolYear !== null) {
            $payload['school_year'] = $schoolYear;
        }

        if ($semester !== null) {
            $payload['semester'] = $semester;
        }

        if ($applicationType !== null) {
            $payload['application_type'] = $applicationType;
        }

        if ($targetLevels !== []) {
            $payload['scholarship_target_levels'] = $targetLevels;
        }

        if ($committeeHead !== null) {
            $payload['committee_head'] = $committeeHead;
        }

        if ($programDescription !== null) {
            $payload['program_description'] = $programDescription;
        }

        if ($documentRequirements !== []) {
            $payload['document_requirements'] = $documentRequirements;
        }

        return $payload;
    }

    protected function sanitizeSchoolYear(mixed $value): ?string
    {
        $schoolYear = trim((string) $value);
        if ($schoolYear === '' || ! preg_match('/^\d{4}-\d{4}$/', $schoolYear)) {
            return null;
        }

        [$startYear, $endYear] = array_map('intval', explode('-', $schoolYear));
        if ($endYear !== $startYear + 1) {
            return null;
        }

        return $schoolYear;
    }

    protected function sanitizeSemester(mixed $value): ?string
    {
        $semester = trim((string) $value);

        return in_array($semester, self::ALLOWED_SEMESTERS, true) ? $semester : null;
    }

    protected function sanitizeApplicationType(mixed $value): ?string
    {
        $type = trim((string) $value);

        return in_array($type, self::ALLOWED_APPLICATION_TYPES, true) ? $type : null;
    }

    protected function sanitizeScholarshipTargetLevel(mixed $value): ?string
    {
        $level = trim((string) $value);

        return in_array($level, self::ALLOWED_SCHOLARSHIP_TARGET_LEVELS, true) ? $level : null;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return list<string>
     */
    protected function resolveScholarshipTargetLevels(array $raw): array
    {
        $levels = $this->sanitizeScholarshipTargetLevels($raw['scholarship_target_levels'] ?? null);
        if ($levels !== []) {
            return $levels;
        }

        $legacy = $this->sanitizeScholarshipTargetLevel($raw['scholarship_target_level'] ?? null);

        return $legacy !== null ? [$legacy] : [];
    }

    /**
     * @return list<string>
     */
    protected function sanitizeScholarshipTargetLevels(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $levels = [];
        foreach ($raw as $level) {
            $sanitized = $this->sanitizeScholarshipTargetLevel($level);
            if ($sanitized !== null) {
                $levels[] = $sanitized;
            }
        }

        return array_values(array_unique($levels));
    }

    /**
     * @return list<array{en: string, tl: string}>
     */
    public static function sanitizeQuickGuidelines(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $steps = [];
        foreach ($raw as $step) {
            if (! is_array($step)) {
                continue;
            }

            $en = trim((string) ($step['en'] ?? ''));
            $tl = trim((string) ($step['tl'] ?? ''));
            if ($en === '' && $tl === '') {
                continue;
            }

            $steps[] = [
                'en' => $en !== '' ? $en : $tl,
                'tl' => $tl !== '' ? $tl : $en,
            ];

            if (count($steps) >= self::SCHOLARSHIP_QUICK_GUIDELINE_MAX_STEPS) {
                break;
            }
        }

        return $steps;
    }

    /**
     * @param  list<array{en: string, tl: string}>  $guidelines
     */
    protected function assertQuickGuidelinesWithinLimit(array $guidelines): void
    {
        if (count($guidelines) > self::SCHOLARSHIP_QUICK_GUIDELINE_MAX_STEPS) {
            throw ValidationException::withMessages([
                'scholarship_details' => [
                    sprintf(
                        'Quick Guidelines cannot exceed %d steps.',
                        self::SCHOLARSHIP_QUICK_GUIDELINE_MAX_STEPS
                    ),
                ],
            ]);
        }

        foreach ($guidelines as $index => $step) {
            foreach (['en' => 'English', 'tl' => 'Tagalog'] as $field => $label) {
                $text = (string) ($step[$field] ?? '');
                if (mb_strlen($text) > self::SCHOLARSHIP_QUICK_GUIDELINE_MAX_CHARS) {
                    throw ValidationException::withMessages([
                        'scholarship_details' => [
                            sprintf(
                                'Quick Guidelines step #%d (%s) exceeds %d characters.',
                                $index + 1,
                                $label,
                                self::SCHOLARSHIP_QUICK_GUIDELINE_MAX_CHARS
                            ),
                        ],
                    ]);
                }
            }
        }
    }

    /**
     * @return list<array{id: string, name: string, file_type: string, required: bool, max_size_mb: int, description: string}>
     */
    protected function sanitizeDocumentRequirements(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $requirements = [];
        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $fileType = (string) ($item['file_type'] ?? 'pdf');
            if (! in_array($fileType, self::ALLOWED_DOCUMENT_FILE_TYPES, true)) {
                $fileType = 'pdf';
            }

            $maxSize = (int) ($item['max_size_mb'] ?? 5);
            if ($maxSize < 1) {
                $maxSize = 1;
            }
            if ($maxSize > 10) {
                $maxSize = 10;
            }

            $requirements[] = [
                'id' => trim((string) ($item['id'] ?? ('doc_req_'.uniqid()))),
                'name' => $name,
                'file_type' => $fileType,
                'required' => (bool) ($item['required'] ?? true),
                'max_size_mb' => $maxSize,
                'description' => trim((string) ($item['description'] ?? '')),
            ];
        }

        return $requirements;
    }

    /**
     * @return array{youth_classifications: list<string>, youth_age_groups: list<string>, education_levels: list<string>}|null
     */
    protected function sanitizeEligibility(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $classifications = array_values(array_intersect(
            self::ELIGIBILITY_YOUTH_CLASSIFICATIONS,
            array_map(fn ($value) => trim((string) $value), (array) ($raw['youth_classifications'] ?? []))
        ));

        $ageGroups = array_values(array_intersect(
            self::ELIGIBILITY_YOUTH_AGE_GROUPS,
            array_map(fn ($value) => trim((string) $value), (array) ($raw['youth_age_groups'] ?? []))
        ));

        $educationLevels = array_values(array_intersect(
            self::ELIGIBILITY_EDUCATION_LEVELS,
            array_map(fn ($value) => trim((string) $value), (array) ($raw['education_levels'] ?? []))
        ));

        if ($classifications === [] && $ageGroups === [] && $educationLevels === []) {
            return null;
        }

        return [
            'youth_classifications' => $classifications,
            'youth_age_groups' => $ageGroups,
            'education_levels' => $educationLevels,
        ];
    }

    /**
     * @return array{start: ?string, end: ?string}|null
     */
    protected function sanitizePeriod(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $start = $this->nullableDateString($raw['start'] ?? null);
        $end = $this->nullableDateString($raw['end'] ?? null);

        if ($start === null && $end === null) {
            return null;
        }

        if ($start !== null && $end !== null && $end < $start) {
            throw ValidationException::withMessages([
                'scholarship_details' => ['Period end date cannot be before start date.'],
            ]);
        }

        return ['start' => $start, 'end' => $end];
    }

    protected function nullableDateString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    protected function sanitizeCustomQuestions(array $questions): array
    {
        $allowedTypes = ['text', 'paragraph', 'number', 'checkbox', 'radio', 'dropdown', 'date', 'file'];
        $sanitized = [];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $label = trim((string) ($question['label'] ?? $question['question'] ?? ''));
            if ($label === '') {
                continue;
            }

            $type = (string) ($question['type'] ?? 'text');
            if (! in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $options = [];
            if (in_array($type, ['checkbox', 'radio', 'dropdown'], true)) {
                $options = array_values(array_filter(array_map(
                    fn ($option) => trim((string) $option),
                    (array) ($question['options'] ?? [])
                )));
                if ($options === []) {
                    $options = ['Option 1'];
                }
            }

            $entry = [
                'id' => (string) ($question['id'] ?? ('q_'.uniqid())),
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'required' => (bool) ($question['required'] ?? false),
            ];

            if ($type === 'file') {
                $fileType = (string) ($question['file_type'] ?? 'pdf');
                if (! in_array($fileType, self::ALLOWED_DOCUMENT_FILE_TYPES, true)) {
                    $fileType = 'pdf';
                }

                $maxSize = (int) ($question['max_size_mb'] ?? 5);
                if ($maxSize < 1) {
                    $maxSize = 1;
                }
                if ($maxSize > 10) {
                    $maxSize = 10;
                }

                $entry['file_type'] = $fileType;
                $entry['max_size_mb'] = $maxSize;
                $entry['description'] = trim((string) ($question['description'] ?? ''));
            }

            $sanitized[] = $entry;
        }

        return $sanitized;
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    public function ensureDefaultTeamNameQuestion(array $questions): array
    {
        $hasTeamName = collect($questions)->contains(function (array $question) {
            $fieldKey = (string) ($question['field_key'] ?? '');
            $label = strtolower(trim((string) ($question['label'] ?? '')));

            return $fieldKey === 'team_name' || $label === 'team name';
        });

        if ($hasTeamName) {
            return $questions;
        }

        return array_merge([self::DEFAULT_TEAM_NAME_QUESTION], $questions);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function sanitizeSportsDetails(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $classifications = [];
        foreach ((array) ($raw['age_classifications'] ?? []) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $name = trim((string) ($entry['name'] ?? ''));
            $minAge = (int) ($entry['min_age'] ?? 0);
            $maxAge = (int) ($entry['max_age'] ?? 0);

            if ($name === '') {
                continue;
            }

            if ($minAge < 15 || $maxAge > 30 || $minAge > $maxAge) {
                throw ValidationException::withMessages([
                    'sports_details' => ["Invalid age range for \"{$name}\". Ages must be between 15 and 30."],
                ]);
            }

            $classifications[] = [
                'id' => (string) ($entry['id'] ?? ('cls_'.uniqid())),
                'name' => $name,
                'min_age' => $minAge,
                'max_age' => $maxAge,
                'is_open' => (bool) ($entry['is_open'] ?? true),
            ];
        }

        if ($classifications === []) {
            return null;
        }

        $maxTeamMembers = (int) ($raw['max_team_members'] ?? self::SPORTS_MAX_TEAM_MEMBERS);
        if ($maxTeamMembers < 1) {
            $maxTeamMembers = 1;
        }
        if ($maxTeamMembers > self::SPORTS_MAX_TEAM_MEMBERS) {
            $maxTeamMembers = self::SPORTS_MAX_TEAM_MEMBERS;
        }

        $minTeamMembers = (int) ($raw['min_team_members'] ?? 1);
        if ($minTeamMembers < 1) {
            $minTeamMembers = 1;
        }
        if ($minTeamMembers > $maxTeamMembers) {
            $minTeamMembers = $maxTeamMembers;
        }

        $sportKey = $this->sanitizeSportKey($raw['sport_key'] ?? null);
        $sportLabel = $this->sanitizeSportLabel(
            $sportKey,
            $raw['sport_label'] ?? $raw['other_sport_name'] ?? null,
        );

        return [
            'open_all' => (bool) ($raw['open_all'] ?? false),
            'max_team_members' => $maxTeamMembers,
            'min_team_members' => $minTeamMembers,
            'sport_key' => $sportKey,
            'sport_label' => $sportLabel,
            'age_classifications' => $classifications,
        ];
    }

    public function sanitizeSportKey(mixed $value): string
    {
        $sportKey = strtolower(trim((string) $value));

        if (! in_array($sportKey, self::SPORTS_DISCIPLINE_KEYS, true)) {
            throw ValidationException::withMessages([
                'sports_details' => ['Please select Basketball, Volleyball, or Other.'],
            ]);
        }

        return $sportKey;
    }

    public function sanitizeSportLabel(string $sportKey, mixed $customLabel = null): string
    {
        if ($sportKey === self::SPORT_KEY_OTHER) {
            $label = trim((string) $customLabel);
            if ($label === '') {
                throw ValidationException::withMessages([
                    'sports_details' => ['Please enter the sport name for Other.'],
                ]);
            }

            return $label;
        }

        return self::DEFAULT_SPORT_LABELS[$sportKey] ?? ucfirst($sportKey);
    }

    public function sportsProgramExistsForYear(
        int $barangayId,
        string $programLetter,
        string $sportKey,
        int $year,
        ?int $ignoreProgramId = null,
    ): bool {
        return ScheduleProgram::query()
            ->where('barangay_id', $barangayId)
            ->active()
            ->where('program_letter', strtoupper(trim($programLetter)))
            ->whereYear('start_date', $year)
            ->when($ignoreProgramId !== null, fn ($query) => $query->where('id', '!=', $ignoreProgramId))
            ->get()
            ->contains(function (ScheduleProgram $program) use ($sportKey) {
                $details = is_array($program->sports_details) ? $program->sports_details : [];

                return strtolower(trim((string) ($details['sport_key'] ?? ''))) === strtolower(trim($sportKey));
            });
    }

    /**
     * @return array<string, list<array{id: string, name: string, min_age: int, max_age: int}>>
     */
    public static function sportsAgeClassificationsConfig(): array
    {
        static $config = null;

        if ($config === null) {
            $config = require app_path('Modules/Program_Management/config/sports-age-classifications.php');
        }

        return $config;
    }

    /**
     * @return list<array{id: string, name: string, min_age: int, max_age: int, is_open: bool}>
     */
    public static function defaultSportsAgeClassificationsPayload(?string $sportKey = null): array
    {
        $config = self::sportsAgeClassificationsConfig();
        $key = strtolower(trim((string) ($sportKey ?? self::SPORT_KEY_BASKETBALL)));

        if (! isset($config[$key])) {
            $key = self::SPORT_KEY_BASKETBALL;
        }

        return array_map(function (array $entry) {
            return [
                'id' => (string) ($entry['id'] ?? 'cls_'.strtolower(str_replace(' ', '_', $entry['name']))),
                'name' => $entry['name'],
                'min_age' => $entry['min_age'],
                'max_age' => $entry['max_age'],
                'is_open' => true,
            ];
        }, $config[$key]);
    }

    public function scholarshipProgramExistsForYearSemester(
        int $barangayId,
        string $schoolYear,
        string $semester,
        ?int $ignoreProgramId = null,
    ): bool {
        if ($schoolYear === '' || $semester === '') {
            return false;
        }

        return ScheduleProgram::query()
            ->where('barangay_id', $barangayId)
            ->active()
            ->when(
                Schema::hasColumn('schedule_programs', 'program_letter'),
                fn ($query) => $query->where('program_letter', self::LETTER_EDUCATION),
                fn ($query) => $query->whereRaw('LOWER(committee) LIKE ?', ['%education%']),
            )
            ->when($ignoreProgramId !== null, fn ($query) => $query->where('id', '!=', $ignoreProgramId))
            ->get()
            ->contains(function (ScheduleProgram $program) use ($schoolYear, $semester) {
                $details = is_array($program->scholarship_details) ? $program->scholarship_details : [];

                return ($details['school_year'] ?? '') === $schoolYear
                    && ($details['semester'] ?? '') === $semester;
            });
    }

    public function programSupportsRenewalOptions(?int $programId): bool
    {
        if ($programId === null) {
            return false;
        }

        return ProgramApplication::query()
            ->where('program_id', $programId)
            ->exists();
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatProgramPublic(ScheduleProgram $program): array
    {
        return $this->formatProgram($program);
    }

    protected function formatProgram(ScheduleProgram $program): array
    {
        $sportsDetails = is_array($program->sports_details) ? $program->sports_details : [];
        $sportLabel = trim((string) ($sportsDetails['sport_label'] ?? ''));
        if ($sportLabel === '' && isset($sportsDetails['sport_key'])) {
            $sportLabel = $this->sanitizeSportLabel((string) $sportsDetails['sport_key'], null);
        }

        $scholarshipDetails = is_array($program->scholarship_details) ? $program->scholarship_details : [];
        unset($scholarshipDetails['quick_guidelines']);
        $quickGuidelines = $this->resolveQuickGuidelinesForProgram($program);
        if ($quickGuidelines !== []) {
            $scholarshipDetails['quick_guidelines'] = $quickGuidelines;
        }

        return [
            'id' => $program->id,
            'program_letter' => $program->program_letter,
            'program_name' => $program->program_name,
            'program_type' => $program->program_type,
            'sport_key' => $sportsDetails['sport_key'] ?? null,
            'sport_label' => $sportLabel !== '' ? $sportLabel : $program->program_type,
            'committee' => $program->committee,
            'participation_quantity' => $program->participation_quantity,
            'participationQty' => $program->participation_quantity,
            'start_date' => $program->start_date?->toDateString(),
            'end_date' => $program->end_date?->toDateString(),
            'startDate' => $program->start_date?->toDateString(),
            'endDate' => $program->end_date?->toDateString(),
            'status' => $program->status,
            'announcement' => $program->announcement,
            'scholarship_details' => $scholarshipDetails !== [] ? $scholarshipDetails : null,
            'sports_details' => $program->sports_details,
            'kk_profiling_fields' => $program->kk_profiling_fields ?? [],
            'kkProfilingFields' => $program->kk_profiling_fields ?? [],
            'custom_questions' => $program->custom_questions ?? [],
            'customQuestions' => $program->custom_questions ?? [],
            'renewal_options_enabled' => $this->programSupportsRenewalOptions($program->id),
            'created_at' => $program->created_at?->toIso8601String(),
            'updated_at' => $program->updated_at?->toIso8601String(),
            'is_archived' => (bool) ($program->is_archived ?? false),
            'archived_at' => $program->archived_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{en: string, tl: string}>
     */
    protected function resolveQuickGuidelinesForProgram(ScheduleProgram $program): array
    {
        if (! $program->relationLoaded('quickGuidelineSteps')) {
            $program->load('quickGuidelineSteps');
        }

        $steps = $program->quickGuidelineSteps
            ->map(fn (ScholarshipQuickGuidelineStep $step) => [
                'en' => (string) $step->content_en,
                'tl' => (string) $step->content_tl,
            ])
            ->values()
            ->all();

        if ($steps !== []) {
            return $steps;
        }

        $legacy = is_array($program->scholarship_details)
            ? ($program->scholarship_details['quick_guidelines'] ?? null)
            : null;

        return self::sanitizeQuickGuidelines($legacy);
    }

    /**
     * @param  list<array{en: string, tl: string}>  $steps
     */
    protected function syncQuickGuidelineSteps(ScheduleProgram $program, array $steps): void
    {
        if (! Schema::hasTable('scholarship_quick_guideline_steps')) {
            return;
        }

        ScholarshipQuickGuidelineStep::query()
            ->where('schedule_program_id', $program->id)
            ->delete();

        foreach ($steps as $index => $step) {
            ScholarshipQuickGuidelineStep::create([
                'schedule_program_id' => $program->id,
                'step_order' => $index + 1,
                'content_en' => (string) ($step['en'] ?? ''),
                'content_tl' => (string) ($step['tl'] ?? ''),
            ]);
        }
    }
}
