<?php

use App\Models\Abyip;
use App\Models\Barangay;

it('renders the barangay list page', function () {
    $response = $this->get(route('homepage.barangays'));

    $response->assertOk();
    $response->assertSee('Barangay ABYIP');
});

it('renders an empty state when a barangay has no abyip', function () {
    $barangay = Barangay::query()->whereDoesntHave('abyipDocuments')->first();

    if ($barangay === null) {
        $this->markTestSkipped('All barangays currently have ABYIP documents.');
    }

    $response = $this->get(route('homepage.barangays.show', $barangay->slug));

    $response->assertOk();
    $response->assertSee('No ABYIP uploaded yet');
});

it('returns 404 for unknown barangay slugs', function () {
    $this->get(route('homepage.barangays.show', 'does-not-exist'))
        ->assertNotFound();
});

it('renders legacy abyip data when available', function () {
    $document = Abyip::query()->documents()->first();

    if ($document === null) {
        $this->markTestSkipped('No legacy ABYIP documents in database.');
    }

    $barangay = Barangay::query()->findOrFail($document->barangay_id);

    $response = $this->get(route('homepage.barangays.show', $barangay->slug));

    $response->assertOk();
    $response->assertSee((string) $document->fiscal_year);
    $response->assertDontSee('No ABYIP uploaded yet');
});
