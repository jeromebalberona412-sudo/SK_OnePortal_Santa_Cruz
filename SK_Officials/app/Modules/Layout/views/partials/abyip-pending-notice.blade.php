@php
    $abyipGate = $abyipGate ?? null;
    $showPendingNotice = is_array($abyipGate) && ($abyipGate['status'] ?? null) === 'pending';
@endphp

@if($showPendingNotice)
    <div class="abyip-pending-notice" role="status">
        <div class="abyip-pending-notice-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="abyip-pending-notice-content">
            <strong class="abyip-pending-notice-title">ABYIP Pending</strong>
            <p class="abyip-pending-notice-text">{{ $abyipGate['pending_message'] ?? 'Pending — waiting for SK Federation President to approve your ABYIP.' }}</p>
        </div>
    </div>
@endif
