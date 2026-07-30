<?php

use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;
use App\Modules\Barangay_ABYIP\Services\Parsing\AbyipBudgetExtractor;

it('extracts mooe total and person from a two-amount budget line', function () {
    $extractor = new AbyipBudgetExtractor(new AbyipNumericNormalizer);

    $result = $extractor->extractBudgetFromAmountLine('324,000.00 324,000.00 SK Treasurer');

    expect($result['budget_mooe'])->toBe('324000.00')
        ->and($result['budget_co'])->toBeNull()
        ->and($result['budget_total'])->toBe('324000.00')
        ->and($result['person_responsible'])->toBe('SK Treasurer');
});

it('collects person responsible from following lines', function () {
    $extractor = new AbyipBudgetExtractor(new AbyipNumericNormalizer);

    $lines = [
        '10,000.00 10,000.00',
        'Sangguniang',
        'Kabataan',
        'Council',
        'Training and Seminar Expenses',
    ];

    $person = $extractor->collectPersonFromFollowingLines($lines, 0);

    expect($person)->toBe('Sangguniang Kabataan Council');
});

it('parses multiline expenditure rows from server-side pdf text', function () {
    $extractor = new AbyipBudgetExtractor(new AbyipNumericNormalizer);

    $lines = [
        'II.ExpenditureProgram',
        'Honoraria',
        'Payment is given for professional services',
        'January01, 2025to December31, 2025',
        '324,000.00 324,000.00 SK Treasurer',
        'Travel Expenses',
        'Costs incurred in the movement',
        '10,000.00 10,000.00',
        'Sangguniang',
        'Kabataan',
        'Council',
    ];

    $rows = $extractor->parseMultilineExpenditureRows($lines);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['ppa_name'])->toBe('Honoraria')
        ->and($rows[0]['budget_mooe'])->toBe('324000.00')
        ->and($rows[0]['budget_total'])->toBe('324000.00')
        ->and($rows[0]['person_responsible'])->toBe('SK Treasurer')
        ->and($rows[1]['ppa_name'])->toBe('Travel Expenses')
        ->and($rows[1]['budget_mooe'])->toBe('10000.00')
        ->and($rows[1]['person_responsible'])->toBe('Sangguniang Kabataan Council');
});
