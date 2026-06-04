@php
    $inputId = $inputId ?? 'committeeHead';
    $defaultValue = $defaultValue ?? 'SK Jerome Balberona';
@endphp
<div class="sports-field spfb-committee-head-field" style="margin-bottom:14px;">
    <label for="{{ $inputId }}">Program / Committee Head (SK) <span class="sports-req">*</span></label>
    <input type="text" id="{{ $inputId }}" class="sports-input" maxlength="120"
           placeholder="e.g. SK Jerome Balberona" value="{{ $defaultValue }}">
    <p class="spfb-announcement-hint" style="margin-top:4px;">Displayed on the application form for applicants (Google Form style).</p>
</div>
