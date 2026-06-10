<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Change Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite([
        'app/Modules/Authentication/assets/css/login.css',
        'app/Modules/Profile/assets/css/change-email.css',
        'app/Modules/Profile/assets/js/change-email.js',
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
</head>
<body class="login-page">

    <div id="signin-overlay" class="signin-overlay" aria-hidden="true" hidden>
        <div class="signin-overlay-inner">
            <div class="signin-spinner">
                <div class="signin-spinner-ring"></div>
                <div class="signin-spinner-ring signin-spinner-ring--2"></div>
                <div class="signin-spinner-dot"></div>
            </div>
            <p class="signin-overlay-title">Sending Verification</p>
            <p class="signin-overlay-sub">Please wait...</p>
        </div>
    </div>

    <div class="login-page">
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
            <div class="logo-container">
                <div class="logo-glow-wrapper">
                    <img src="{{ asset('Images/image.png') }}" alt="SK OnePortal Admin Logo" class="large-logo">
                </div>
                <h1 class="brand-title">SK OnePortal Admin</h1>
                <p class="brand-subtitle">Municipality of Santa Cruz, Laguna</p>
            </div>

            <div class="login-form-container">
                <div class="login-card-inner">
                    <div class="form-header">
                        <h2>Change Email</h2>
                        <p>Enter your new email address. A verification link will be sent to confirm it.</p>
                    </div>

                    @if ($errors->any())
                        <div class="login-alert login-alert--danger" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="login-alert login-alert--success" role="alert">{{ session('status') }}</div>
                    @endif

                    <form class="sk-login-form" id="ceForm" action="{{ route('profile.change-email.request') }}" method="POST" novalidate>
                        @csrf

                        <div class="form-group">
                            <label for="ceCurrentEmail">Current Email</label>
                            <input type="email" id="ceCurrentEmail" name="current_email"
                                class="form-control ce-readonly-input"
                                value="{{ old('current_email', $user->email) }}"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label for="ceNewEmail">New Email Address</label>
                            <input type="email" id="ceNewEmail" name="new_email"
                                class="form-control @error('new_email') is-invalid @enderror"
                                placeholder="example@gmail.com"
                                value="{{ old('new_email') }}"
                                autocomplete="email" required>
                            <p id="ceNewEmailError" class="ce-field-err" @if(!$errors->has('new_email')) style="display:none;" @endif>
                                {{ $errors->first('new_email') }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="cePassword">Current Password</label>
                            <input type="password" id="cePassword" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your current password"
                                autocomplete="current-password" required>
                            <p id="cePasswordError" class="ce-field-err" @if(!$errors->has('password')) style="display:none;" @endif>
                                {{ $errors->first('password') }}
                            </p>
                        </div>

                        <button type="submit" class="login-btn w-100" id="ceSubmitBtn">
                            <span id="ceBtnText">Send Verification Link</span>
                        </button>
                    </form>

                    <div class="back-to-profile-wrap">
                        <a href="{{ route('profile') }}" class="back-to-profile-btn">
                            ← Back to Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
