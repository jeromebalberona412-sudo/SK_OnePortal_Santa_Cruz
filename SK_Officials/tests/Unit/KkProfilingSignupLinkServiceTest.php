<?php

use App\Models\Barangay;
use App\Services\KkProfilingSignupLinkService;

test('signup link uses the kabataan app url and barangay slug', function () {
    $barangay = new Barangay;
    $barangay->name = 'Santo Angel Norte';

    $link = (new KkProfilingSignupLinkService)->forBarangay($barangay, 'http://192.168.56.1:8002');

    expect($link)->toBe('http://192.168.56.1:8002/kkprofiling/signup/santo-angel-norte');
});

test('signup link slug strips extra punctuation from barangay names', function () {
    $service = new KkProfilingSignupLinkService;

    expect($service->slugFromName('Poblacion I'))->toBe('poblacion-i')
        ->and($service->slugFromName('San Jose'))->toBe('san-jose');
});
