@php
    $title = $title ?? 'Application Form Builder';
    $hint = $hint ?? 'Add custom questions that Kabataan members will answer when applying.';
    $showAnnouncement = $showAnnouncement ?? true;
    $wrapCard = $wrapCard ?? true;
@endphp
@if($wrapCard)
<div class="schol-schedule-card gform-application-builder" style="margin-bottom:20px;">
    <h4 class="schol-schedule-title" style="margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        {{ $title }}
    </h4>
@endif

    @if($showAnnouncement)
        @include('GForm_Builder::partials.announcement-field')
    @endif

    @include('GForm_Builder::partials.custom-questions-builder', ['hint' => $hint])

@if($wrapCard)
</div>
@endif
