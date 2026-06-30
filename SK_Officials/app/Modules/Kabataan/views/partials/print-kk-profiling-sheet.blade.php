@php
    use App\Support\KabataanQuestionnairePrintPresenter;

    $sheet = KabataanQuestionnairePrintPresenter::present(
        $registration,
        is_array($formData ?? null) ? $formData : [],
        $submittedAt ?? null,
        $barangayLogoUrl ?? null,
    );
@endphp

<section class="kk-print-sheet">
    @include('Kabataan::partials.kk-questionnaire-readonly', $sheet)
</section>
