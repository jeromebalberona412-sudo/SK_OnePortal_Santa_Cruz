<?php

use App\Modules\Barangay_ABYIP\Services\Rows\AbyipColumnMap;

it('maps cells by fixed column position and keeps empty co empty', function () {
    $map = new AbyipColumnMap;

    $mapped = $map->mapCells([
        '5-02-01',
        'Medical Assistance',
        'Aid for youth',
        'Assisted youth',
        'Number of beneficiaries',
        'January 2026 to December 2026',
        '50000.00',
        '',
        '50000.00',
        'SK Chairperson',
    ]);

    expect($mapped['code'])->toBe('5-02-01')
        ->and($mapped['ppa_name'])->toBe('Medical Assistance')
        ->and($mapped['budget_mooe'])->toBe('50000.00')
        ->and($mapped['budget_co'])->toBe('')
        ->and($mapped['budget_total'])->toBe('50000.00')
        ->and($mapped['person_responsible'])->toBe('SK Chairperson');
});

it('detects wrapped person responsible headers', function () {
    $map = new AbyipColumnMap;

    expect($map->normalizeHeader("Person\nResponsible"))->toBe('person_responsible')
        ->and($map->normalizeHeader('MO O E'))->toBe('budget_mooe');
});
