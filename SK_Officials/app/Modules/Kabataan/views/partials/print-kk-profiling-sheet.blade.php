@php
    $read = function ($key, $fallback = '—') use ($formData, $registration) {
        $value = $formData[$key] ?? null;
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }
        if (! filled($value) && isset($registration->{$key})) {
            $value = $registration->{$key};
        }

        return filled($value) ? $value : $fallback;
    };

    $respondentDisplay = '—';
    if ($registration->respondent_sequence) {
        $respondentDisplay = str_pad((string) $registration->respondent_sequence, 2, '0', STR_PAD_LEFT);
    } elseif ($registration->respondent_number && preg_match('/(\d+)$/', (string) $registration->respondent_number, $matches)) {
        $respondentDisplay = str_pad((string) (((int) $matches[1]) % 100 ?: 1), 2, '0', STR_PAD_LEFT);
    }

    $fields = [
        'Last Name' => $read('last_name'),
        'First Name' => $read('first_name'),
        'Middle Name' => $read('middle_name'),
        'Suffix' => $read('suffix'),
        'Region' => $read('region', 'Region IV-A (CALABARZON)'),
        'Province' => $read('province', 'Laguna'),
        'City / Municipality' => $read('city', 'Santa Cruz'),
        'Barangay' => $registration->barangay?->name ?? $read('barangay'),
        'Purok / Sitio / Zone' => $read('purok_zone'),
        'Sex Assigned by Birth' => $read('sex'),
        'Age' => $read('age'),
        'Birthday' => $read('birthday'),
        'E-mail Address' => $read('email', $registration->email),
        'Contact Number' => $read('contact_number', $registration->contact_number),
        'Civil Status' => $read('civil_status'),
        'Youth Classification' => $read('youth_classification'),
        'Youth Age Group' => $read('youth_age_group'),
        'Work Status' => $read('work_status'),
        'Highest Educational Attainment' => $read('education'),
        'Registered SK Voter' => $read('sk_voter'),
        'Registered National Voter' => $read('national_voter'),
        'Voted Last SK Election' => $read('sk_voted'),
        'Attended KK Assembly' => $read('kk_assembly'),
        'KK Assembly Times' => $read('kk_times'),
        'Reason (No KK Assembly)' => $read('kk_reason'),
        'Facebook Profile URL' => $read('facebook_profile_url') !== '—' ? $read('facebook_profile_url') : $read('facebook'),
        'Willing to Join Group Chat' => $read('group_chat'),
    ];

    $signature = $formData['signature'] ?? null;
@endphp

<section class="kk-print-sheet">
    <header class="kk-print-header">
        <div>
            <h1>KK Survey Questionnaire</h1>
            <p class="kk-print-meta">
                Profiling Year {{ $profilingYear }}
                · Respondent # {{ $respondentDisplay }}
                · Date {{ optional($submittedAt)->format('m/d/Y') ?? date('m/d/Y') }}
            </p>
        </div>
        <div class="kk-print-name">{{ $registration->full_name }}</div>
    </header>

    <div class="kk-print-section-title">I. PROFILE</div>

    <div class="kk-print-grid">
        @foreach ($fields as $label => $value)
            <div class="kk-print-field">
                <div class="kk-print-label">{{ $label }}</div>
                <div class="kk-print-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    @if (! empty($signature) && is_string($signature) && str_starts_with($signature, 'data:image'))
        <div class="kk-print-signature">
            <div class="kk-print-label">Signature</div>
            <img src="{{ $signature }}" alt="Signature">
        </div>
    @endif
</section>
