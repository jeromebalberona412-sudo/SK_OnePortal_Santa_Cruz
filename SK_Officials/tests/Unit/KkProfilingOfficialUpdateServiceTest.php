<?php

use App\Services\KkProfilingOfficialUpdateService;

test('officials must use a valid gmail address when adding email', function () {
    expect(preg_match(KkProfilingOfficialUpdateService::EMAIL_REGEX, 'juandelacruz@gmail.com'))->toBe(1)
        ->and(preg_match(KkProfilingOfficialUpdateService::EMAIL_REGEX, ''))->toBe(0)
        ->and(preg_match(KkProfilingOfficialUpdateService::EMAIL_REGEX, 'juan@yahoo.com'))->toBe(0)
        ->and(preg_match(KkProfilingOfficialUpdateService::EMAIL_REGEX, 'ab@gmail.com'))->toBe(0);
});
