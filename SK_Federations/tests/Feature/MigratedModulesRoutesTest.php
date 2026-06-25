<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

it('registers accounts routes in sk federation', function () {
    expect(Route::has('accounts.federation.index'))->toBeTrue();
    expect(Route::has('accounts.officials.index'))->toBeTrue();
    expect(Route::has('accounts.store'))->toBeTrue();
});

it('registers audit log routes in sk federation', function () {
    expect(Route::has('auditlogs.index'))->toBeTrue();
    expect(Route::has('auditlogs.data'))->toBeTrue();
    expect(Route::has('auditlogs.export.csv'))->toBeTrue();
});

it('registers archive management routes in sk federation', function () {
    expect(Route::has('archived.deleted-sk-federation'))->toBeTrue();
    expect(Route::has('archived.deleted-sk-officials'))->toBeTrue();
    expect(Route::has('archived.sk-federation-records'))->toBeTrue();
    expect(Route::has('archived.sk-officials-records'))->toBeTrue();
    expect(Route::has('archive'))->toBeFalse();
});

it('redirects guests away from migrated module pages', function () {
    get('/accounts/federation')->assertRedirect('/login');
    get('/audit-logs')->assertRedirect('/login');
    get('/archived/deleted-sk-federation')->assertRedirect('/login');
});
