<?php

use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;
use App\Modules\Barangay_ABYIP\Services\Parsing\AbyipBudgetValidator;

it('accepts a reconciling mooe co total triple', function () {
    $validator = new AbyipBudgetValidator(new AbyipNumericNormalizer);

    $result = $validator->validate([
        'budget_mooe' => '10000.00',
        'budget_co' => '20000.00',
        'budget_total' => '30000.00',
    ]);

    expect($result['validation_status'])->toBe('valid')
        ->and($result['manual_review_required'])->toBeFalse()
        ->and($result['mooe'])->toBe('10000.00')
        ->and($result['co'])->toBe('20000.00')
        ->and($result['total'])->toBe('30000.00');
});

it('flags a budget mismatch without changing source values', function () {
    $validator = new AbyipBudgetValidator(new AbyipNumericNormalizer);

    $result = $validator->validate([
        'mooe' => '150000',
        'co' => '0',
        'total' => '160000',
    ]);

    expect($result['validation_status'])->toBe('warning')
        ->and($result['manual_review_required'])->toBeTrue()
        ->and($result['mooe'])->toBe('150000.00')
        ->and($result['co'])->toBe('0.00')
        ->and($result['total'])->toBe('160000.00');
});

it('does not invent a missing total', function () {
    $validator = new AbyipBudgetValidator(new AbyipNumericNormalizer);

    $result = $validator->validate([
        'budget_mooe' => '10000.00',
        'budget_co' => null,
        'budget_total' => null,
    ]);

    expect($result['validation_status'])->toBe('warning')
        ->and($result['manual_review_required'])->toBeTrue()
        ->and($result['total'])->toBeNull();
});
