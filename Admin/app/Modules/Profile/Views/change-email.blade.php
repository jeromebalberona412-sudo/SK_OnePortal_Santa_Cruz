<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Change Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Authentication/assets/js/login.js',
        'resources/js/theme.js',
    ])
    <script>
        (function () {
            var t = localStorage.getItem('op_theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
    /* ── Change Email specific styles ── */
    .ce-pending-card{border:1px solid var(--op-gray-200);border-radius:12px;background:#f8fafc;padding:0 1rem;margin-bottom:12px;overflow:hidden;}
    .ce-pending-row{display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding:10px 0;}
    .ce-pending-label{font-size:0.82rem;font-weight:600;color:#64748b;white-space:nowrap;}
    .ce-pending-value{font-size:0.85rem;color:#1e293b;word-break:break-all;text-align:right;}
    .ce-pending-new{color:#1565c0;font-weight:700;}
    .ce-pending-divider{height:1px;background:var(--op-gray-200);margin:0;}
    .ce-status-badge{display:inline-flex;align-items:center;gap:5px;font-size:0.76rem;font-weight:700;color:#b45309;background:#fef3c7;border:1px solid #fde68a;border-radius:999px;padding:3px 10px;}
    .ce-status-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:#f59e0b;flex-shrink:0;animation:ce-dot 1.4s ease-in-out infinite;}
    @keyframes ce-dot{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(.8);}}
    .ce-resend-timer{font-size:0.83rem;color:#64748b;text-align:center;margin:0 0 10px;}
    .ce-resend-timer strong{color:#1565c0;}
    .ce-resend-success{font-size:0.83rem;color:#16a34a;text-align:center;margin:0 0 10px;}
    .ce-action-col{display:flex;flex-direction:column;gap:0.55rem;margin-bottom:4px;}
    .ce-btn-outline{background:#fff!important;color:var(--op-blue)!important;border:1.5px solid rgba(21,101,192,.5)!important;box-shadow:none!important;}
    .ce-btn-outline:hover{background:#e8f0fe!important;}
    .ce-btn-outline:disabled{opacity:.55;cursor:not-allowed;}
    .ce-btn-danger{background:linear-gradient(135deg,#dc2626,#b91c1c)!important;border-color:transparent!important;}
    .ce-btn-danger:hover{background:linear-gradient(135deg,#ef4444,#dc2626)!important;}
    .ce-readonly-input{background:#f1f5f9!important;color:#64748b!important;cursor:default;}
    .ce-field-err{color:#dc2626;font-size:0.83rem;margin:5px 0 0;}
    .ce-sent-header{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
    .ce-sent-icon{display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:#e8f5e9;flex-shrink:0;}
    </style>
</head>
<body class="login-page">

    <div class="login-page">
        {{-- Background --}}
        <div class="bg-wrapper">
            <div class="bg-image"></div>
            <div class="gradient-overlay"></div>
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>

        <div class="login-container">

            {{-- LEFT: Logo --}}
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            {{-- RIGHT: Card --}}
            <div class="login-form-container">
                <div class="login-card-inner">

                    {{-- ══════════════════════════════════════════
                         STEP 1 — Request new email
                    ══════════════════════════════════════════ --}}
                    <div id="ce-step1">
                        <div class="form-header">
                            <h2>📧 Change Email</h2>
                            <p>Enter your new email address. A verification link will be sent to confirm it.</p>
                        </div>

                        {{-- Current email (read-only) --}}
                        <div class="form-group">
                            <label>Current Email</label>
                            <input type="email" class="form-control ce-readonly-input"
                                value="{{ auth()->user()->email }}" readonly>
                        </div>

                        {{-- New email --}}
                        <div class="form-group">
                            <label for="ce-new-email">New Email Address</label>
                            <input type="email" id="ce-new-email" class="form-control"
                                placeholder="example@gmail.com" autocomplete="email">
                            <p id="ce-email-err" class="ce-field-err" style="display:none;">Please enter a valid email address.</p>
                        </div>

                        {{-- Current password --}}
                        <div class="form-group">
                            <label for="ce-password">Current Password</label>
                            <input type="password" id="ce-password" class="form-control"
                                placeholder="Enter your current password" autocomplete="current-password">
                            <p id="ce-pw-err" class="ce-field-err" style="display:none;">Please enter your current password.</p>
                        </div>

                        <button type="button" class="login-btn w-100" id="ce-send-btn" onclick="ceSendVerification()">
                            Send Verification Link
                        </button>

                        <div class="form-footer">
                            <p>
                                <a href="{{ route('profile') }}" style="color:var(--op-blue);font-weight:600;text-decoration:none;">
                                    ← Back to Profile
                                </a>
                            </p>
                        </div>
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
                                <h2 style="font-size:1.5rem;font-weight:800;color:var(--op-blue-dark);margin:0 0 2px;">Verification Sent!</h2>
                                <p style="font-size:0.9rem;color:var(--op-gray-500);margin:0;">Check your new email inbox and click the link.</p>
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

                        <div class="form-footer">
                            <p>
                                <a href="{{ route('profile') }}" style="color:var(--op-blue);font-weight:600;text-decoration:none;">
                                    ← Back to Profile
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- ══════════════════════════════════════════
                         STEP 3 — Email confirmed / updated
                    ══════════════════════════════════════════ --}}
                    <div id="ce-step3" style="display:none;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#e8f5e9;margin:0 auto 1.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#16a34a" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <h2 style="font-size:1.875rem;font-weight:800;color:var(--op-blue-dark);margin-bottom:0.5rem;">Email Updated!</h2>
                        <p style="font-size:0.9rem;color:var(--op-gray-500);margin-bottom:0.25rem;">Your email address has been successfully changed.</p>
                        <p style="font-size:0.8rem;color:#94a3b8;margin-bottom:1.5rem;">A notification has been sent to your old email address.</p>
                        <a href="{{ route('profile') }}" class="login-btn" style="display:block;text-decoration:none;text-align:center;">
                            Back to Profile
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ Vite::asset('app/Modules/Profile/assets/js/change-email.js') }}"></script>

</body>
</html>
