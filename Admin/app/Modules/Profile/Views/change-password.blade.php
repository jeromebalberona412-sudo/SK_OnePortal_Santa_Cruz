<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Change Password</title>
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
                         STEP 1 — Email input (default view)
                    ══════════════════════════════════════════ --}}
                    <div id="cp-step1" @if(session('status')) style="display:none;" @endif>

                        <div class="form-header">
                            <h2>🔒 Change Password</h2>
                            <p>Enter your email to receive a password reset link</p>
                        </div>

                        @if ($errors->any())
                            <div class="login-alert login-alert--danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.change-password.send') }}" novalidate id="cp-form">
                            @csrf

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email ?? '') }}"
                                    placeholder="admin@sccl.gov.ph"
                                    required autofocus autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" id="cp-submit-btn" class="login-btn w-100">
                                Send Reset Link
                            </button>
                        </form>

                        <div class="form-footer">
                            <p>
                                <a href="{{ route('profile') }}" style="color:var(--op-blue);font-weight:600;text-decoration:none;">
                                    ← Back to Profile
                                </a>
                            </p>
                        </div>
                    </div>

                    {{-- ══════════════════════════════════════════
                         STEP 2 — Success (reset link sent)
                    ══════════════════════════════════════════ --}}
                    <div id="cp-step2" @unless(session('status')) style="display:none;" @endunless>

                        <div class="form-header" style="text-align:center;margin-bottom:1.5rem;">
                            <div style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#e8f5e9;margin:0 auto 1.25rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#16a34a" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </div>
                            <h2>Reset Link Sent!</h2>
                            <p>Check your email for the password reset link</p>
                        </div>

                        @if (session('status'))
                            <div class="login-alert login-alert--success" role="alert">
                                <strong>Email sent!</strong> {{ session('status') }}
                            </div>
                        @endif

                        <a href="{{ route('profile') }}" class="login-btn" style="display:block;text-decoration:none;text-align:center;margin-top:1rem;">
                            ← Back to Profile
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>
