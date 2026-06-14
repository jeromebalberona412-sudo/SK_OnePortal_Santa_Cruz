@extends('profile::layouts.account-auth')

@section('title', 'Verify Email Change')

@section('card-class', 'ce-verify-card sk-fed-compact-card')

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/profile/css/sk-fed-account-auth.css') }}">
@endpush

@push('scripts-before')
    <script>
        window.ceResendCooldown = {{ (int) $resendCooldown }};
        window.ceFreshVerification = {{ ($freshVerification ?? false) ? 'true' : 'false' }};
    </script>
@endpush

@push('scripts')
    <script src="{{ url('/modules/profile/js/change-email-verify.js') }}"></script>
@endpush

@section('content')
    <div id="ceVerifySection">
        <div class="card-header">
            <h2 class="card-title">Verify Email Change</h2>
            <p class="card-subtitle">We sent a confirmation link to your new email address.</p>
        </div>

        <div class="ce-verify-content">
            <div class="ce-sent-header">
                <div class="ce-sent-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ce-sent-title">Verification Sent!</div>
                <div class="ce-sent-sub">Open your inbox and tap the confirmation link.</div>
            </div>

            @if ($errors->any())
                <div class="sk-alert sk-alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="ce-info-box">{{ session('status') }}</div>
            @endif

            <div class="ce-info-box">
                A confirmation link has been sent to <strong id="cePendingEmail">{{ $user->pending_email }}</strong>. Your current email stays active until you verify the new one.
            </div>

            <div class="ce-status-table">
                <div class="ce-status-row">
                    <span class="ce-status-key">Current email</span>
                    <span class="ce-status-val" id="ceCurrentEmailVal">{{ $user->email }}</span>
                </div>
                <div class="ce-status-row">
                    <span class="ce-status-key">Pending email</span>
                    <span class="ce-status-val" id="cePendingEmailVal">{{ $user->pending_email }}</span>
                </div>
                <div class="ce-status-row">
                    <span class="ce-status-key">Status</span>
                    <span class="ce-status-val"><span class="ce-badge-awaiting">Awaiting verification</span></span>
                </div>
            </div>

            <div class="ce-resend-timer" id="ceTimer" @if($resendCooldown <= 0) style="display:none;" @endif>
                Resend available in <strong id="ceTimerCount">{{ $resendCooldown > 0 ? sprintf('%d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) : '1:00' }}</strong>
            </div>

            <div class="ce-actions">
                <form action="{{ route('change-email.resend') }}" method="POST" id="ceResendForm">
                    @csrf
                    <button type="submit" class="ce-btn-resend" id="ceResendBtn" @if($resendCooldown > 0) disabled @endif>Resend Verification</button>
                </form>
                <form action="{{ route('change-email.cancel') }}" method="POST" id="ceCancelForm">
                    @csrf
                    <button type="submit" class="ce-btn-cancel" id="ceCancelBtn">Cancel Request</button>
                </form>
            </div>

            <div class="youth-register-section ce-back-section">
                <p class="register-text">
                    <a href="{{ route('profile') }}#settings" class="register-link">← Back to Profile</a>
                </p>
            </div>
        </div>
    </div>
@endsection
