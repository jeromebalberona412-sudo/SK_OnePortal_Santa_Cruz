<?php

use App\Models\KkSurveyResponse;
use App\Services\KkProfilingRequestDataService;

function kkProfilingDataService(): KkProfilingRequestDataService
{
    return new KkProfilingRequestDataService;
}

test('unanswered group chat stays empty instead of defaulting to yes or no', function () {
    $service = kkProfilingDataService();

    expect($service->normalizeYesNoAnswer(null))->toBeNull()
        ->and($service->normalizeYesNoAnswer(''))->toBeNull()
        ->and($service->normalizeYesNoAnswer('—'))->toBeNull()
        ->and($service->normalizeYesNoAnswer(true))->toBeNull()
        ->and($service->normalizeYesNoAnswer(false))->toBeNull()
        ->and($service->normalizeYesNoAnswer(1))->toBeNull()
        ->and($service->normalizeYesNoAnswer(0))->toBeNull();
});

test('explicit group chat answers stay yes or no', function () {
    $service = kkProfilingDataService();

    expect($service->normalizeYesNoAnswer('Yes'))->toBe('Yes')
        ->and($service->normalizeYesNoAnswer('no'))->toBe('No')
        ->and($service->normalizeYesNoAnswer(['Yes']))->toBe('Yes');
});

test('survey boolean does not mark unanswered group chat as yes or no', function () {
    $service = kkProfilingDataService();
    $survey = new KkSurveyResponse;
    $survey->willing_to_join_group_chat = false;

    $merged = $service->mergeSurveyIntoRegistrationPayload([
        'group_chat' => null,
        'last_name' => 'Dela Cruz',
    ], $survey);

    expect($merged['group_chat'] ?? null)->toBeNull();
});

test('list payload keys omit heavy form data signature and documents', function () {
    $keys = kkProfilingDataService()->listPayloadKeys();

    expect($keys)->not->toContain('form_data')
        ->and($keys)->not->toContain('signature')
        ->and($keys)->not->toContain('supporting_documents')
        ->and($keys)->toContain('has_email')
        ->and($keys)->toContain('group_chat')
        ->and($keys)->toContain('civil_status')
        ->and($keys)->toContain('birthday')
        ->and($keys)->toContain('barangay_logo_url');

    $surveyColumns = kkProfilingDataService()->listSurveyColumns();

    expect($surveyColumns)->not->toContain('participant_signature')
        ->and($surveyColumns)->not->toContain('supporting_documents')
        ->and($surveyColumns)->toContain('civil_status')
        ->and($surveyColumns)->toContain('kabataan_registration_id');
});
