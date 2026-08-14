<?php

test('community feed reaction sound file is published', function () {
    expect(file_exists(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'sounds'.DIRECTORY_SEPARATOR.'reactions_ux.mp3'))->toBeTrue();
});
