<?php

use App\Models\Abyip;
use App\Models\AbyipProgramDuration;
use App\Models\User;
use App\Modules\Program_Management\Services\ProgramEvaluationService;
use App\Modules\Programs\Services\AbyipProgramCatalogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function evaluationTenantId(): int
{
    $existing = DB::table('tenants')->where('code', 'santa_cruz')->value('id');

    if ($existing) {
        return (int) $existing;
    }

    return (int) DB::table('tenants')->insertGetId([
        'code' => 'santa_cruz',
        'name' => 'Santa Cruz',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function evaluationBarangayId(): int
{
    return (int) DB::table('barangays')->insertGetId([
        'tenant_id' => evaluationTenantId(),
        'name' => 'Test Barangay',
        'municipality' => 'Santa Cruz',
        'province' => 'Laguna',
        'region' => 'IV-A CALABARZON',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * @return array{user: User, program: Abyip, document: Abyip}
 */
function createEvaluationFixture(string $endDate = '2025-12-31'): array
{
    $tenantId = evaluationTenantId();
    $barangayId = evaluationBarangayId();

    $user = User::factory()->create([
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId,
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
    ]);

    $document = Abyip::query()->create([
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId,
        'created_by' => $user->id,
        'fiscal_year' => 2025,
        'row_type' => Abyip::ROW_DOCUMENT,
        'document_title' => 'ABYIP 2025',
        'status' => Abyip::STATUS_APPROVED,
    ]);

    $document->update(['document_id' => $document->id]);

    $program = Abyip::query()->create([
        'document_id' => $document->id,
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId,
        'created_by' => $user->id,
        'fiscal_year' => 2025,
        'row_type' => Abyip::ROW_YOUTH_PROGRAM,
        'code' => 'A',
        'program_name' => 'Equitable Access to Quality Education',
        'sort_order' => 1,
    ]);

    AbyipProgramDuration::query()->create([
        'barangay_id' => $barangayId,
        'abyip_program_id' => $program->id,
        'start_date' => '2025-01-01',
        'end_date' => $endDate,
    ]);

    return compact('user', 'program', 'document');
}

it('uses program name as evaluation title and blocks creation before program ends', function () {
    Carbon::setTestNow('2025-06-01');

    $fixture = createEvaluationFixture('2025-12-31');
    $service = app(ProgramEvaluationService::class);

    $context = $service->resolveProgramContext($fixture['user'], 'A');

    expect($context['program']['program_name'])->toBe('Equitable Access to Quality Education')
        ->and($context['can_create'])->toBeFalse()
        ->and($context['create_blocked_reason'])->toContain('after the program period ends');

    expect(fn () => $service->store($fixture['user'], [
        'status' => 'open',
        'custom_questions' => [['label' => 'Rate the program', 'type' => 'text']],
    ], 'A'))->toThrow(ValidationException::class);
});

it('allows one evaluation per program per year after the program period ends', function () {
    Carbon::setTestNow('2026-01-15');

    $fixture = createEvaluationFixture('2025-12-31');
    $service = app(ProgramEvaluationService::class);

    $created = $service->store($fixture['user'], [
        'status' => 'open',
        'custom_questions' => [['label' => 'Rate the program', 'type' => 'text']],
    ], 'A');

    expect($created['title'])->toBe('Equitable Access to Quality Education')
        ->and($created['status'])->toBe('open')
        ->and($created['start_date'])->toBe('2025-01-01')
        ->and($created['end_date'])->toBe('2025-12-31');

    expect(fn () => $service->store($fixture['user'], [
        'status' => 'open',
        'custom_questions' => [['label' => 'Another question', 'type' => 'text']],
    ], 'A'))->toThrow(ValidationException::class);
});

it('persists program durations from the programs module', function () {
    $fixture = createEvaluationFixture();
    $catalog = app(AbyipProgramCatalogService::class);

    $duration = $catalog->upsertProgramDuration(
        (int) $fixture['user']->barangay_id,
        (int) $fixture['program']->id,
        '2025-02-01',
        '2025-11-30',
    );

    expect($duration['startDate'])->toBe('2025-02-01')
        ->and($duration['endDate'])->toBe('2025-11-30');

    $resolved = $catalog->resolveProgramDuration(
        (int) $fixture['user']->barangay_id,
        (int) $fixture['program']->id,
        2025,
    );

    expect($resolved['startDate'])->toBe('2025-02-01')
        ->and($resolved['endDate'])->toBe('2025-11-30');
});

it('detects program status from duration dates', function () {
    $catalog = app(AbyipProgramCatalogService::class);
    $reference = Carbon::parse('2025-06-15');

    expect($catalog->resolveProgramStatus('2025-07-01', '2025-12-31', $reference))
        ->toBe(AbyipProgramCatalogService::STATUS_PLANNED);

    expect($catalog->resolveProgramStatus('2025-01-01', '2025-12-31', $reference))
        ->toBe(AbyipProgramCatalogService::STATUS_ONGOING);

    expect($catalog->resolveProgramStatus('2025-01-01', '2025-06-01', $reference))
        ->toBe(AbyipProgramCatalogService::STATUS_COMPLETED);
});
