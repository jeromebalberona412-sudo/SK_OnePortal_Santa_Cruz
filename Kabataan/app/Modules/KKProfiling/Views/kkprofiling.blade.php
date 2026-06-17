<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($fvCameraConfig))
        <meta name="kk-fv-config" content="{{ json_encode($fvCameraConfig) }}">
    @endif
    <title>KK Profiling - {{ $barangay }} - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/KKProfiling/assets/css/kkprofiling.css',
        'app/Modules/KKProfiling/assets/css/facial-verification.css',
        'app/Modules/KKProfiling/assets/css/kkprofiling-wizard.css',
        'app/Modules/KKProfiling/assets/js/kkprofiling.js',
        'app/Modules/KKProfiling/assets/js/facial-verification.js',
        'app/Modules/KKProfiling/assets/js/kkprofiling-wizard.js',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
</head>
<body class="homepage-body kkp-form-page kkp-wizard-mode">

    @include('dashboard::loading')

    <main class="kkp-main">
        <div class="kkp-page-wrap">
            <a href="{{ route('kkprofiling.signup', ['clear' => 1]) }}" class="kkp-back-link" id="kkpTopBackLink" aria-label="Back to KK Profiling signup">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L8.414 9H17a1 1 0 110 2H8.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back
            </a>

            @if (session('success'))
                <div class="kkp-alert kkp-alert-success">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="kkp-alert kkp-alert-error" id="kkpPageErrors">
                    {{ $errors->first() }}
                </div>
            @endif

            <div
                id="kkpRegistrationWizard"
                class="kkp-wizard-root"
                data-barangay-slug="{{ $slug }}"
                data-respondent-number="{{ $respondentNumber ?? '' }}"
                data-initial-step="{{ $wizardInitialStep ?? 1 }}"
                data-email-verified="{{ ($wizardEmailVerified ?? false) ? '1' : '0' }}"
                data-verification-sent="{{ ($verificationSent ?? false) ? '1' : '0' }}"
                @if($errors->has('email')) data-email-error="{{ $errors->first('email') }}" @endif
            >
                @include('kkprofiling::partials.wizard.progress-header')

                <div class="kkp-paper" id="kkpFormCard">
                    <div class="kkp-responsive-container">

                        {{-- STEP 1: Existing KK Profiling Form (unchanged) --}}
                        <section class="kkp-wizard-panel" id="kkpWizardStep1" data-wizard-step="1">
                            <form
                                method="POST"
                                action="{{ route('kkprofiling.submit', ['barangay' => $slug]) }}"
                                id="kkProfilingForm"
                                data-barangay-slug="{{ $slug }}"
                                data-wizard-mode="1"
                                onsubmit="handleFormSubmit(event); return false;"
                            >
                                @csrf

                                @include('kkprofiling::partials.kk-profiling-form-fields', [
                                    'barangay' => $barangay,
                                    'respondentNumber' => $respondentNumber ?? '',
                                    'respondentDisplay' => $respondentDisplay ?? '01',
                                    'submitLabel' => 'Submit KK Profiling',
                                    'barangayLogoUrl' => $barangayLogoUrl ?? null,
                                    'requireFacialVerification' => false,
                                ])
                            </form>
                        </section>

                        {{-- STEP 2: Facial Detection --}}
                        <section class="kkp-wizard-panel" id="kkpWizardStep2" data-wizard-step="2" hidden>
                            @include('kkprofiling::partials.kk-facial-verification')
                        </section>

                        {{-- STEP 3: Supporting Documents (optional) --}}
                        @include('kkprofiling::partials.wizard.step-documents')

                        {{-- STEP 4: Email Verification + Account Setup --}}
                        @include('kkprofiling::partials.wizard.step-account')

                    </div>
                </div>

                <div class="kkp-wizard-nav" id="kkpWizardNav">
                    <button type="button" class="kkp-wizard-btn kkp-wizard-btn-secondary" id="kkpWizardBackBtn" hidden>
                        Back
                    </button>
                    <button type="button" class="kkp-wizard-btn kkp-wizard-btn-primary" id="kkpWizardNextBtn">
                        Next
                    </button>
                </div>
            </div>

        </div>
    </main>

    @include('kkprofiling::partials.kk-profiling-signature-modals')

</body>
</html>
