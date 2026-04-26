@extends('layouts.auth')

@section('title', 'OnePortal Admin — Change Email')

@section('head')
@vite(['app/Modules/Profile/assets/css/change-email.css'])
@endsection

@section('content')

<style>
/* ── Inline styles for change-email page ── */
.ce-sent-header{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.ce-sent-icon{display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:#e8f5e9;flex-shrink:0;}
.ce-pending-card{border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;padding:0 1rem;margin-bottom:12px;overflow:hidden;}
.ce-pending-row{display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding:10px 0;}
.ce-pending-label{font-size:0.82rem;font-weight:600;color:#64748b;white-space:nowrap;}
.ce-pending-value{font-size:0.85rem;color:#1e293b;word-break:break-all;text-align:right;}
.ce-pending-new{color:#1565c0;font-weight:700;}
.ce-pending-divider{height:1px;background:#e2e8f0;margin:0;}
.ce-status-badge{display:inline-flex;align-items:center;gap:5px;font-size:0.76rem;font-weight:700;color:#b45309;background:#fef3c7;border:1px solid #fde68a;border-radius:999px;padding:3px 10px;}
.ce-status-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;animation:ce-dot 1.4s ease-in-out infinite;}
@keyframes ce-dot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(.8);}}
.ce-resend-timer{font-size:0.83rem;color:#64748b;text-align:center;margin:0 0 10px;}
.ce-resend-timer strong{color:#1565c0;}
.ce-resend-success{font-size:0.83rem;color:#16a34a;text-align:center;margin:0 0 10px;}
.ce-action-col{display:flex;flex-direction:column;gap:0.55rem;margin-bottom:4px;}
.ce-btn-outline{background:#fff!important;color:#1565c0!important;border:1.5px solid rgba(21,101,192,.5)!important;box-shadow:none!important;}
.ce-btn-outline:hover{background:#e8f0fe!important;}
.ce-btn-outline:disabled{opacity:.55;cursor:not-allowed;}
.ce-btn-danger{background:linear-gradient(135deg,#dc2626,#b91c1c)!important;border-color:transparent!important;}
.ce-btn-danger:hover{background:linear-gradient(135deg,#ef4444,#dc2626)!important;}
.ce-success-icon-wrap{display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#e8f5e9;margin:0 auto 16px;}
.ce-readonly-input{background:#f1f5f9!important;color:#64748b!important;cursor:default;}
.ce-field-err{color:#dc2626;font-size:0.83rem;margin:5px 0 0;}
.ce-fade-in{animation:ce-fade .35s ease both;}
@keyframes ce-fade{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);}}
/* dark */
:root.dark .ce-pending-card{background:rgba(15,23,42,.5);border-color:#334155;}
:root.dark .ce-pending-label{color:#64748b;}
:root.dark .ce-pending-value{color:#e2e8f0;}
:root.dark .ce-pending-new{color:#60a5fa;}
:root.dark .ce-pending-divider{background:#334155;}
:root.dark .ce-status-badge{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.3);color:#fbbf24;}
:root.dark .ce-btn-outline{background:#1e293b!important;color:#60a5fa!important;border-color:rgba(96,165,250,.4)!important;}
:root.dark .ce-readonly-input{background:#172033!important;color:#475569!important;}
:root.dark .ce-resend-timer{color:#475569;}
:root.dark .ce-resend-timer strong{color:#60a5fa;}
:root.dark .ce-success-icon-wrap{background:rgba(22,163,74,.12);}
</style>

    {{-- ══════════════════════════════════════════
         STEP 1 — Request new email
    ══════════════════════════════════════════ --}}
    <div id="ce-step1">
        <div class="login-card-header">
            <div class="fp-icon-wrap mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                </svg>
            </div>
            <h2 class="login-heading">Change Email</h2>
            <p class="login-subheading">Enter your new email address. A verification link will be sent to confirm it.</p>
        </div>

        {{-- Current email (read-only) --}}
        <div class="login-field-group">
            <label class="login-label">Current Email</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </span>
                <input type="email" class="login-input ce-readonly-input"
                    value="{{ auth()->user()->email }}" readonly>
            </div>
        </div>

        {{-- New email --}}
        <div class="login-field-group">
            <label class="login-label" for="ce-new-email">New Email Address</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </span>
                <input type="email" id="ce-new-email" class="login-input"
                    placeholder="example@gmail.com" autocomplete="email">
            </div>
            <p id="ce-email-err" class="ce-field-err" style="display:none;">Please enter a valid email address.</p>
        </div>

        {{-- Current password --}}
        <div class="login-field-group">
            <label class="login-label" for="ce-password">Current Password</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                    </svg>
                </span>
                <input type="password" id="ce-password" class="login-input"
                    placeholder="Enter your current password" autocomplete="current-password">
            </div>
            <p id="ce-pw-err" class="ce-field-err" style="display:none;">Please enter your current password.</p>
        </div>

        <button type="button" class="login-btn mb-3" id="ce-send-btn" onclick="ceSendVerification()">
            Send Verification Link
        </button>

        <p class="text-center mb-0" style="font-size:0.85rem;color:#6b7a99;">
            <a href="{{ route('profile') }}" class="login-forgot">← Back to Profile</a>
        </p>
    </div>

    {{-- ══════════════════════════════════════════
         STEP 2 — Pending / verification sent
    ══════════════════════════════════════════ --}}
    <div id="ce-step2" style="display:none;">

        {{-- Header --}}
        <div class="ce-sent-header">
            <div class="ce-sent-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="#16a34a" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <div>
                <h2 class="login-heading" style="margin:0 0 2px;">Verification Sent!</h2>
                <p class="login-subheading" style="margin:0;">Check your new email inbox and click the link.</p>
            </div>
        </div>

        {{-- Alert --}}
        <div class="login-alert login-alert--success" role="alert" style="margin-bottom:14px;">
            <strong>Verification link sent!</strong> A confirmation link has been sent to
            <strong id="ce-pending-email-display"></strong>.
            Your current email remains active until you verify the new one.
        </div>

        {{-- Pending info card --}}
        <div class="ce-pending-card">
            <div class="ce-pending-row">
                <span class="ce-pending-label">Current email</span>
                <span class="ce-pending-value">{{ auth()->user()->email }}</span>
            </div>
            <div class="ce-pending-divider"></div>
            <div class="ce-pending-row">
                <span class="ce-pending-label">Pending email</span>
                <span class="ce-pending-value ce-pending-new" id="ce-pending-new-display"></span>
            </div>
            <div class="ce-pending-divider"></div>
            <div class="ce-pending-row">
                <span class="ce-pending-label">Status</span>
                <span class="ce-status-badge">Awaiting verification</span>
            </div>
        </div>

        {{-- Resend cooldown timer --}}
        <p class="ce-resend-timer" id="ce-resend-timer" style="display:none;">
            Resend available in <strong id="ce-timer-count">60</strong>s
        </p>
        <p class="ce-resend-success" id="ce-resend-msg" style="display:none;">
            Verification link resent successfully.
        </p>

        {{-- Actions --}}
        <div class="ce-action-col">
            <button type="button" class="login-btn ce-btn-outline" id="ce-resend-btn" onclick="ceResend()">
                Resend Verification
            </button>
            <button type="button" class="login-btn ce-btn-danger" onclick="ceCancel()">
                Cancel Request
            </button>
        </div>

        <p class="text-center mt-3 mb-0" style="font-size:0.85rem;color:#6b7a99;">
            <a href="{{ route('profile') }}" class="login-forgot">← Back to Profile</a>
        </p>
    </div>

    {{-- ══════════════════════════════════════════
         STEP 3 — Email confirmed / updated
    ══════════════════════════════════════════ --}}
    <div id="ce-step3" style="display:none;" class="text-center">
        <div class="ce-success-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#16a34a" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
        </div>
        <h2 class="login-heading mb-2">Email Updated!</h2>
        <p class="login-subheading mb-1">Your email address has been successfully changed.</p>
        <p class="login-subheading mb-4" style="font-size:0.8rem;color:#94a3b8;">A notification has been sent to your old email address.</p>
        <a href="{{ route('profile') }}" class="login-btn d-block text-decoration-none text-center">
            Back to Profile
        </a>
    </div>

@endsection

@push('scripts')
<script src="{{ Vite::asset('app/Modules/Profile/assets/js/change-email.js') }}"></script>
@endpush
