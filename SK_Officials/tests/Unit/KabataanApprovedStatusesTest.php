<?php

use App\Models\KabataanRegistration;
use App\Support\KabataanApprovedStatuses;

test('unverified portal signups stay out of the kabataan table even if approved', function () {
    $unverified = new KabataanRegistration([
        'status' => 'pending_verification',
        'evaluation_status' => 'active',
        'user_id' => null,
        'email_verified_at' => null,
        'password_set_at' => null,
    ]);

    expect(KabataanApprovedStatuses::hasVerifiedAccount($unverified))->toBeFalse()
        ->and(KabataanApprovedStatuses::isListedInKabataan($unverified))->toBeFalse()
        ->and(KabataanApprovedStatuses::isPendingInKkProfiling($unverified))->toBeTrue();
});

test('verified portal accounts appear in the kabataan table after approval', function () {
    $verified = new KabataanRegistration;
    $verified->setRawAttributes([
        'status' => 'active',
        'evaluation_status' => 'active',
        'user_id' => 12,
        'email_verified_at' => '2026-08-13 12:00:00',
        'password_set_at' => '2026-08-13 12:05:00',
    ]);

    expect(KabataanApprovedStatuses::hasVerifiedAccount($verified))->toBeTrue()
        ->and(KabataanApprovedStatuses::isListedInKabataan($verified))->toBeTrue();
});

test('official walk-in records without a portal account can still be listed', function () {
    $walkIn = new KabataanRegistration([
        'status' => 'active',
        'evaluation_status' => 'active',
        'user_id' => null,
        'email_verified_at' => null,
        'password_set_at' => null,
    ]);

    expect(KabataanApprovedStatuses::hasVerifiedAccount($walkIn))->toBeTrue()
        ->and(KabataanApprovedStatuses::isListedInKabataan($walkIn))->toBeTrue();
});
