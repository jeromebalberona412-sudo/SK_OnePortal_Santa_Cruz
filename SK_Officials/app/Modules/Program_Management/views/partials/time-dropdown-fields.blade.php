@php
    $prefix = $prefix ?? 'startTime';
    $label = $label ?? 'Time';
    $required = $required ?? false;
    $inputClass = $inputClass ?? 'schol-input';
    $wrapClass = $wrapClass ?? 'spfb-time-dropdowns';
@endphp
<div class="schol-time-field{{ empty($label) ? ' schol-time-field--inline' : '' }}">
    @if($label)
        <label>{{ $label }} @if($required)<span class="schol-req">*</span>@endif</label>
    @endif
    <div class="{{ $wrapClass }}">
        <select id="{{ $prefix }}Hour" class="{{ $inputClass }} spfb-time-part" title="Hour">
            <option value="" disabled selected>HH</option>
            @for ($h = 1; $h <= 12; $h++)
                <option value="{{ $h }}">{{ $h }}</option>
            @endfor
        </select>
        <span class="spfb-time-sep">:</span>
        <select id="{{ $prefix }}Min" class="{{ $inputClass }} spfb-time-part" title="Minute">
            <option value="" disabled selected>MM</option>
            @for ($m = 0; $m <= 59; $m++)
                @php $mm = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $mm }}">{{ $mm }}</option>
            @endfor
        </select>
        <select id="{{ $prefix }}Period" class="{{ $inputClass }} spfb-time-part" title="AM/PM">
            <option value="" disabled selected>AM/PM</option>
            <option value="AM">AM</option>
            <option value="PM">PM</option>
        </select>
    </div>
</div>
