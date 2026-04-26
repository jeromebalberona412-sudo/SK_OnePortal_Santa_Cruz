@extends('layouts.auth')

@section('title', 'OnePortal Admin — Change Password')

@section('content')

    {{-- ══════════════════════════════════════════
         STEP 1 — Email input
    ══════════════════════════════════════════ --}}
    <div id="cp-step1">
        <div class="login-card-header">
            <div class="fp-icon-wrap mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                </svg>
            </div>
            <h2 class="login-heading">Change Password</h2>
            <p class="login-subheading">Enter your email address to receive a password reset link.</p>
        </div>

        <div class="login-field-group">
            <label class="login-label" for="cp-email">Email Address</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </span>
                <input type="email" id="cp-email" class="login-input"
                    placeholder="example@gmail.com" autocomplete="email">
            </div>
            <p id="cp-email-err" style="color:#dc2626;font-size:0.83rem;margin:6px 0 0;display:none;">
                Please enter a valid email address.
            </p>
        </div>

        <button type="button" class="login-btn mb-3" id="cp-send-btn" onclick="cpSendLink()">
            Send Reset Link
        </button>

        <p class="text-center mb-0" style="font-size:0.85rem;color:#6b7a99;">
            <a href="{{ route('profile') }}" class="login-forgot">← Back to Profile</a>
        </p>
    </div>

    {{-- ══════════════════════════════════════════
         STEP 1.5 — Sent confirmation (countdown)
    ══════════════════════════════════════════ --}}
    <div id="cp-step15" style="display:none;">
        <div class="login-card-header">
            <div class="fp-icon-wrap mb-3" style="background:#e8f5e9;color:#16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h2 class="login-heading">Reset Link Sent!</h2>
            <p class="login-subheading">Redirecting you to set your new password…</p>
        </div>

        <div class="login-alert login-alert--success" role="alert">
            <strong>Reset link sent!</strong> A password reset link has been sent to your email address.
        </div>

        <div style="display:flex;align-items:center;gap:10px;justify-content:center;margin-top:8px;">
            <div class="cp-spinner" aria-hidden="true"></div>
            <span style="font-size:0.84rem;color:#6b7a99;">
                Redirecting in <span id="cp-countdown">3</span>s…
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         STEP 2 — New password + confirm
    ══════════════════════════════════════════ --}}
    <div id="cp-step2" style="display:none;">
        <div class="login-card-header">
            <div class="fp-icon-wrap mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                </svg>
            </div>
            <h2 class="login-heading">Change Password</h2>
            <p class="login-subheading">Reset link sent. Set your new password below.</p>
        </div>

        <div class="login-alert login-alert--success" role="alert" style="margin-bottom:20px;">
            <strong>Reset link sent!</strong> A password reset link has been sent to your email address.
        </div>

        <div class="login-field-group">
            <label class="login-label" for="cp-new-pw">New Password</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                    </svg>
                </span>
                <input type="password" id="cp-new-pw" class="login-input"
                    placeholder="Enter new password" autocomplete="new-password">
            </div>
        </div>

        <div class="login-field-group">
            <label class="login-label" for="cp-confirm-pw">Confirm New Password</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                    </svg>
                </span>
                <input type="password" id="cp-confirm-pw" class="login-input"
                    placeholder="Confirm new password" autocomplete="new-password">
            </div>
        </div>

        <p id="cp-pw-feedback" style="font-size:0.84rem;min-height:1.2em;margin:0 0 14px;"></p>

        <button type="button" class="login-btn mb-3" onclick="cpChangePassword()">
            Change Password
        </button>

        <p class="text-center mb-0" style="font-size:0.85rem;color:#6b7a99;">
            <a href="{{ route('profile') }}" class="login-forgot">← Back to Profile</a>
        </p>
    </div>

    {{-- ══════════════════════════════════════════
         STEP 3 — Success
    ══════════════════════════════════════════ --}}
    <div id="cp-step3" style="display:none;" class="text-center">
        <div style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#e8f5e9;margin:0 auto 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#16a34a" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
        </div>
        <h2 class="login-heading mb-2">Password Changed!</h2>
        <p class="login-subheading mb-4">Your password has been updated successfully.</p>
        <a href="{{ route('profile') }}" class="login-btn d-block text-decoration-none text-center">
            Back to Profile
        </a>
    </div>

@endsection

@push('scripts')
<style>
.cp-spinner {
    width: 18px;
    height: 18px;
    border: 2.5px solid #d0daea;
    border-top-color: #1565c0;
    border-radius: 50%;
    animation: cp-spin 0.75s linear infinite;
    flex-shrink: 0;
}
@keyframes cp-spin { to { transform: rotate(360deg); } }

.cp-fade-in {
    animation: cp-fade 0.35s ease both;
}
@keyframes cp-fade {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function cpShow(id) {
    var el = document.getElementById(id);
    el.style.display = 'block';
    el.classList.remove('cp-fade-in');
    void el.offsetWidth;
    el.classList.add('cp-fade-in');
}

function cpHide(id) {
    document.getElementById(id).style.display = 'none';
}

function cpSendLink() {
    var email = document.getElementById('cp-email').value.trim();
    var errEl = document.getElementById('cp-email-err');
    var btn   = document.getElementById('cp-send-btn');

    errEl.style.display = 'none';

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errEl.style.display = 'block';
        document.getElementById('cp-email').focus();
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Sending…';

    setTimeout(function () {
        cpHide('cp-step1');
        cpShow('cp-step15');

        var secs    = 3;
        var countEl = document.getElementById('cp-countdown');
        var timer   = setInterval(function () {
            secs--;
            if (countEl) countEl.textContent = secs;
            if (secs <= 0) {
                clearInterval(timer);
                cpHide('cp-step15');
                cpShow('cp-step2');
                document.getElementById('cp-new-pw').focus();
            }
        }, 1000);

        btn.disabled    = false;
        btn.textContent = 'Send Reset Link';
    }, 800);
}

function cpChangePassword() {
    var np = document.getElementById('cp-new-pw').value;
    var nc = document.getElementById('cp-confirm-pw').value;
    var fb = document.getElementById('cp-pw-feedback');

    fb.style.color = '#dc2626';
    fb.textContent = '';

    if (!np) {
        fb.textContent = 'Please enter a new password.';
        return;
    }
    if (np.length < 8) {
        fb.textContent = 'Password must be at least 8 characters.';
        return;
    }
    if (np !== nc) {
        fb.textContent = 'Passwords do not match.';
        return;
    }

    cpHide('cp-step2');
    cpShow('cp-step3');
}
</script>
@endpush
