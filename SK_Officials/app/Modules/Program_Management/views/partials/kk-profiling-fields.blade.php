@php
    $kkFields = [
        'last_name' => 'Last Name',
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'suffix' => 'Suffix',
        'birthday' => 'Birthday',
        'age' => 'Age',
        'sex' => 'Sex',
        'civil_status' => 'Civil Status',
        'contact_number' => 'Contact Number',
        'email' => 'Email Address',
        'region' => 'Region',
        'province' => 'Province',
        'city' => 'City/Municipality',
        'barangay' => 'Barangay',
        'purok_zone' => 'Purok/Zone',
        'youth_classification' => 'Youth Classification',
        'youth_age_group' => 'Youth Age Group',
        'education' => 'Educational Attainment',
        'current_school' => 'Current School',
        'course_strand' => 'Course / Strand',
        'work_status' => 'Work Status',
        'sk_voter' => 'Registered SK Voter',
        'sk_voted' => 'Voted Last Election',
    ];
    $programLabel = $programLabel ?? 'program';
    $excludedFields = $excludedFields ?? [];
    $displayOnly = $displayOnly ?? false;
    $displayFields = array_diff_key($kkFields, array_flip($excludedFields));
    $checkboxFields = $displayOnly ? [] : $displayFields;
@endphp

<div style="background:#f0f9ff;border:2px solid #0ea5e9;border-radius:12px;padding:20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        <h5 style="margin:0;font-size:16px;font-weight:700;color:#0369a1;">Include KK Profiling Data</h5>
        @if ($displayOnly)
            <span style="margin-left:auto;font-size:12px;font-weight:600;color:#0369a1;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #0ea5e9;">Always included</span>
        @endif
    </div>
    <p style="font-size:13px;color:#475569;margin-bottom:16px;line-height:1.6;">
        @if ($displayOnly)
            These KK Profiling fields are automatically included in {{ $programLabel }} applications. They are auto-filled from the applicant's KK Profile and shown as read-only.
        @else
            Select KK Profiling fields to automatically include in {{ $programLabel }} applications. Selected fields will be auto-filled from the applicant's KK Profile and displayed as read-only.
        @endif
    </p>

    @if ($displayOnly)
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
            @foreach ($displayFields as $value => $label)
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;padding:8px 10px;background:#fff;border:1px solid #bae6fd;border-radius:6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
            @foreach ($checkboxFields as $value => $label)
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#374151;padding:8px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;transition:all 0.2s;">
                    <input type="checkbox" class="kk-profiling-field" value="{{ $value }}" style="cursor:pointer;width:18px;height:18px;accent-color:#fbbf24;">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div style="margin-top:16px;display:flex;gap:12px;">
            <button type="button" id="selectAllKKFields" style="padding:8px 16px;font-size:13px;font-weight:600;color:#0369a1;background:#e0f2fe;border:1px solid #0ea5e9;border-radius:6px;cursor:pointer;">Select All</button>
            <button type="button" id="clearAllKKFields" style="padding:8px 16px;font-size:13px;font-weight:600;color:#64748b;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;">Clear All</button>
        </div>
    @endif
</div>
