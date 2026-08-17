<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activate Your Kabataan Account - SK OnePortal</title>
    @vite([
        'app/Modules/Authentication/assets/css/sign-in.css',
        'app/Modules/KKProfiling/assets/css/kkprofiling.css',
        'app/Modules/KKProfiling/assets/js/kkprofiling.js',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body class="youth-login-page kkp-setpw-page">
    @include('dashboard::loading')

    <div class="youth-bg-wrapper">
        <div class="youth-bg-image"></div>
        <div class="youth-gradient-overlay"></div>
    </div>

    <main class="youth-login-container">
        <div class="youth-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="{{ asset('images/SK_OnePortal.png') }}" alt="SK OnePortal Logo" class="youth-logo kkp-setpw-logo">
                </div>
                <h1 class="youth-main-title">SK OnePortal</h1>
                <p class="youth-tagline">Official Youth Portal – Santa Cruz, Laguna</p>
                <p class="kkp-setpw-branding-sub">KK Profiling · {{ $barangay }}</p>
            </div>
        </div>

        <div class="youth-login-section">
            <div class="youth-login-card kkp-setpw-card">
                <div class="card-header">
                    <h2 class="card-title">Activate Your Kabataan Account</h2>
                    <p class="card-subtitle">Create a secure password for your <strong>{{ $barangay }}</strong> account.</p>
                </div>

                @if(!empty($email))
                    <div class="youth-form-group kkp-setpw-field">
                        <label for="activationEmail" class="youth-label">Email</label>
                        <input
                            type="email"
                            id="activationEmail"
                            class="youth-input"
                            value="{{ $email }}"
                            readonly
                            tabindex="-1"
                        >
                    </div>
                @endif

                @if($errors->any())
                    <div class="kkp-setpw-alert kkp-setpw-alert-error">{{ $errors->first() }}</div>
                @endif

                <form
                    id="setPasswordForm"
                    class="kkp-setpw-form"
                    method="POST"
                    action="{{ route('kkprofiling.account-invite.activate', ['registration' => $registration->id, 'token' => $token]) }}"
                    novalidate
                >
                    @csrf
                    <div class="youth-form-group kkp-setpw-field">
                        <label for="password" class="youth-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="youth-input" placeholder="Enter your password" autocomplete="new-password">
                        </div>
                        <div class="pw-rules" id="pwRules">
                            <div class="pw-rule" data-rule="len">At least 8 characters</div>
                            <div class="pw-rule" data-rule="lower">At least one lowercase letter</div>
                            <div class="pw-rule" data-rule="upper">At least one uppercase letter</div>
                            <div class="pw-rule" data-rule="num">At least one number</div>
                            <div class="pw-rule" data-rule="special">At least one special character</div>
                        </div>
                    </div>
                    <div class="youth-form-group kkp-setpw-field">
                        <label for="password_confirmation" class="youth-label">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="youth-input" placeholder="Re-enter your password" autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="youth-submit-btn" id="setpwSubmitBtn">
                        <span class="setpw-btn-text">Activate Account</span>
                    </button>
                </form>
            </div>
        </div>
    </main>
    <script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
