<?php

use App\Modules\Barangay_ABYIP\Services\Normalization\AbyipNumericNormalizer;

it('normalizes peso and grouped amounts without shifting decimals', function () {
    $normalizer = new AbyipNumericNormalizer;

    expect($normalizer->parseAmount('₱10,000.00'))->toBe('10000.00')
        ->and($normalizer->parseAmount('₱ 10,000.00'))->toBe('10000.00')
        ->and($normalizer->parseAmount('10,000.00'))->toBe('10000.00')
        ->and($normalizer->numericAmount('10,000'))->toBe('10000.00')
        ->and($normalizer->numericAmount('0'))->toBe('0.00')
        ->and($normalizer->parseAmount('0.00'))->toBe('0.00');
});

it('treats dashes as missing budget values', function () {
    $normalizer = new AbyipNumericNormalizer;

    expect($normalizer->parseAmount('-'))->toBeNull()
        ->and($normalizer->parseAmount('—'))->toBeNull()
        ->and($normalizer->numericAmountOrNull('-'))->toBeNull();
});

it('fills an empty CO cell with zero when MOOE equals TOTAL', function () {
    $normalizer = new AbyipNumericNormalizer;

    $budgets = $normalizer->normalizeBudgetFields([
        'budget_mooe' => '10000.00',
        'budget_co' => '-',
        'budget_total' => '10000.00',
    ]);

    expect($budgets['budget_mooe'])->toBe('10000.00')
        ->and($budgets['budget_co'])->toBe('0.00')
        ->and($budgets['budget_total'])->toBe('10000.00');
});

it('does not invent a missing CO when TOTAL does not match MOOE', function () {
    $normalizer = new AbyipNumericNormalizer;

    $budgets = $normalizer->normalizeBudgetFields([
        'budget_mooe' => '10000.00',
        'budget_co' => null,
        'budget_total' => '25000.00',
    ]);

    expect($budgets['budget_mooe'])->toBe('10000.00')
        ->and($budgets['budget_co'])->toBeNull()
        ->and($budgets['budget_total'])->toBe('25000.00');
});

it('does not treat a calendar year as a budget', function () {
    $normalizer = new AbyipNumericNormalizer;

    expect($normalizer->parseAmount('2025'))->toBeNull()
        ->and($normalizer->parseAmount('2026'))->toBeNull();
});

it('parses a comma used as the decimal separator in pdf amounts', function () {
    $normalizer = new AbyipNumericNormalizer;

    expect($normalizer->parseAmount('20,000,00'))->toBe('20000.00')
        ->and($normalizer->parseAmount('28,398.93'))->toBe('28398.93');
});
