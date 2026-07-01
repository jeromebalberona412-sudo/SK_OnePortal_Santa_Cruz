<?php

use App\Modules\Program_Management\Services\ScheduleProgramService;
use App\Modules\Program_Management\Services\ScholarshipSchoolYearService;
use Illuminate\Validation\ValidationException;

it('sanitizes school year labels with hyphen or en dash', function () {
    $service = app(ScholarshipSchoolYearService::class);

    expect($service->sanitizeLabel('2026-2027'))->toBe('2026-2027')
        ->and($service->sanitizeLabel('2026–2027'))->toBe('2026-2027');
});

it('rejects invalid school year labels', function () {
    $service = app(ScholarshipSchoolYearService::class);

    $service->sanitizeLabel('2026-2028');
})->throws(ValidationException::class);

it('derives school year from youth program duration start date', function () {
    $service = app(ScheduleProgramService::class);

    expect($service->deriveSchoolYearFromYouthProgram([
        'id' => 1,
        'program_name' => 'Education',
        'letter' => 'A',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
    ]))->toBe('2026-2027');
});

it('returns default scholarship eligibility filters', function () {
    $eligibility = ScheduleProgramService::defaultScholarshipEligibility();

    expect($eligibility['youth_classifications'])->toBe(['In School Youth'])
        ->and($eligibility['education_levels'])->toContain('High School Grad')
        ->and($eligibility['education_levels'])->toContain('College Level');
});

it('builds senior high eligibility from target level', function () {
    $eligibility = ScheduleProgramService::scholarshipEligibilityForTargetLevel('senior_high');

    expect($eligibility['youth_classifications'])->toBe(['In School Youth'])
        ->and($eligibility['education_levels'])->toBe(['High School Level']);
});

it('builds college eligibility from target level', function () {
    $eligibility = ScheduleProgramService::scholarshipEligibilityForTargetLevel('college');

    expect($eligibility['youth_classifications'])->toBe(['In School Youth'])
        ->and($eligibility['education_levels'])->toBe(['College Level']);
});

it('builds combined eligibility when both levels are selected', function () {
    $eligibility = ScheduleProgramService::scholarshipEligibilityForTargetLevels(['senior_high', 'college']);

    expect($eligibility['youth_classifications'])->toBe(['In School Youth'])
        ->and($eligibility['education_levels'])->toBe(['High School Level', 'College Level']);
});

it('sanitizes quick guidelines and requires both languages per step', function () {
    expect(ScheduleProgramService::sanitizeQuickGuidelines(null))->toBe([])
        ->and(ScheduleProgramService::sanitizeQuickGuidelines([]))->toBe([])
        ->and(ScheduleProgramService::sanitizeQuickGuidelines([
            ['en' => ' Step one ', 'tl' => ' Hakbang isa '],
        ]))->toBe([
            ['en' => 'Step one', 'tl' => 'Hakbang isa'],
        ]);

    expect(fn () => ScheduleProgramService::sanitizeQuickGuidelines([
        ['en' => 'Only English', 'tl' => ''],
    ]))->toThrow(ValidationException::class);
});

it('limits quick guidelines to ten steps when sanitizing', function () {
    $raw = array_fill(0, 12, ['en' => 'Step', 'tl' => 'Hakbang']);

    expect(ScheduleProgramService::sanitizeQuickGuidelines($raw))->toHaveCount(10);
});

it('reports renewal options disabled for new programs', function () {
    $service = app(ScheduleProgramService::class);

    expect($service->programSupportsRenewalOptions(null))->toBeFalse();
});
