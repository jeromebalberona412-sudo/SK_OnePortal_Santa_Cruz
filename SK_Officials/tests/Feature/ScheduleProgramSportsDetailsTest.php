<?php

use App\Modules\Program_Management\Services\ScheduleProgramService;
use Illuminate\Validation\ValidationException;

it('sanitizes default sports age classifications', function () {
    $defaults = ScheduleProgramService::defaultSportsAgeClassificationsPayload();

    expect($defaults)->toHaveCount(4)
        ->and($defaults[0]['name'])->toBe('Mosquito Division')
        ->and($defaults[0]['min_age'])->toBe(15)
        ->and($defaults[0]['max_age'])->toBe(17);
});

it('sanitizes sports details with editable age brackets', function () {
    $service = app(ScheduleProgramService::class);

    $result = $service->sanitizeSportsDetails([
        'open_all' => true,
        'max_team_members' => 12,
        'age_classifications' => [
            [
                'id' => 'cls_custom',
                'name' => 'Custom Division',
                'min_age' => 16,
                'max_age' => 20,
                'is_open' => true,
            ],
        ],
    ]);

    expect($result)->not->toBeNull()
        ->and($result['open_all'])->toBeTrue()
        ->and($result['max_team_members'])->toBe(12)
        ->and($result['age_classifications'][0]['name'])->toBe('Custom Division');
});

it('rejects invalid sports age ranges', function () {
    $service = app(ScheduleProgramService::class);

    $service->sanitizeSportsDetails([
        'age_classifications' => [
            [
                'name' => 'Invalid Division',
                'min_age' => 10,
                'max_age' => 14,
                'is_open' => true,
            ],
        ],
    ]);
})->throws(ValidationException::class);

it('ensures default team name question for sports programs', function () {
    $service = app(ScheduleProgramService::class);

    $questions = $service->ensureDefaultTeamNameQuestion([]);

    expect($questions)->toHaveCount(1)
        ->and($questions[0]['label'])->toBe('Team Name')
        ->and($questions[0]['field_key'])->toBe('team_name');
});

it('does not duplicate team name question when already present', function () {
    $service = app(ScheduleProgramService::class);

    $existing = [[
        'id' => 'q1',
        'label' => 'Team Name',
        'type' => 'text',
        'required' => true,
    ]];

    $questions = $service->ensureDefaultTeamNameQuestion($existing);

    expect($questions)->toHaveCount(1);
});
