<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-two-factor.css') }}?v={{ time() }}">
    <style>
        :root {
            --theme-deep-blue: #1e5fae;
            --theme-deep-blue-hover: #1a5499;
            --theme-blue-teal: #1f7a8c;
            --theme-green: #2e8b57;
        }

        body {
            background: #0b1220;
        }

        .shape-1 {
            background: linear-gradient(135deg, var(--theme-deep-blue) 0%, var(--theme-blue-teal) 100%);
        }
        .shape-2 {
            background: linear-gradient(135deg, var(--theme-green) 0%, var(--theme-blue-teal) 100%);
        }
        .shape-3 {
            background: linear-gradient(135deg, rgba(30, 95, 174, 0.9) 0%, rgba(31, 122, 140, 0.9) 60%, rgba(46, 139, 87, 0.9) 100%);
        }

        .two-factor-header {
            background: linear-gradient(135deg, var(--theme-deep-blue) 0%, var(--theme-blue-teal) 60%, var(--theme-green) 100%);
        }

        .code-input:focus {
            border-color: var(--theme-deep-blue) !important;
            box-shadow: 0 0 0 4px rgba(30, 95, 174, 0.12) !important;
        }

        .btn-verify {
            background: linear-gradient(135deg, var(--theme-deep-blue) 0%, var(--theme-blue-teal) 55%, var(--theme-green) 100%) !important;
        }
        .btn-verify:hover {
            box-shadow: 0 12px 30px rgba(30, 95, 174, 0.28) !important;
        }

        .recovery-link a {
            color: var(--theme-deep-blue) !important;
        }
        .recovery-link a:hover {
            color: var(--theme-green) !important;
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="two-factor-wrapper">
        <div class="two-factor-container">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 90%; height: 90%; object-fit: cover; border-radius: 50%;">
                </div>
                <h1>Two-Factor Authentication</h1>
                <p>Enter your authentication code to continue</p>
            </div>

            <div class="two-factor-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Check your authenticator app for the 6-digit code</span>
                </div>

                <form id="twoFactorForm" method="POST" action="{{ url('/two-factor-challenge') }}">
                    @csrf

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="code">Authentication Code</label>
                        <div class="code-input-container">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                        </div>
                        <input type="hidden" name="code" id="fullCode">
                        <div class="helper-text">
                            Enter the 6-digit code from your authenticator app
                        </div>
                    </div>

                    <div class="session-info">
                        <i class="fas fa-clock"></i>
                        <span id="sessionTimer">Session expires in: <strong>10:00</strong></span>
                    </div>

                    <button type="submit" class="btn-verify" id="verifyBtn" disabled>
                        <span>Verify Code</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="recovery-link">
                        <a href="#" onclick="toggleRecoveryCode(event)"><i class="fas fa-key"></i> Use recovery code instead</a>
                    </div>

                    <div class="back-to-login">
                        <a href="{{ route('login') }}">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Login</span>
                        </a>
                    </div>
                </form>

                <!-- Recovery Code Form (Hidden by default) -->
                <form id="recoveryForm" method="POST" action="{{ url('/two-factor-challenge') }}" style="display: none;">
                    @csrf
                    <div class="form-group">
                        <label for="recovery_code">Recovery Code</label>
                        <input 
                            type="text" 
                            id="recovery_code" 
                            name="recovery_code" 
                            class="form-control" 
                            placeholder="Enter one of your recovery codes"
                            style="text-align: center;"
                            required
                        >

                    </div>

                    <button type="submit" class="btn-verify">
                        <span>Verify Recovery Code</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="recovery-link">
                        <a href="#" onclick="toggleRecoveryCode(event)"><i class="fas fa-mobile-alt"></i> Use authentication code instead</a>
                    </div>

                    <div class="back-to-login">
                        <a href="{{ route('login') }}">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Login</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('modules/authentication/js/admin-two-factor-auth.js') }}"></script>
    <script src="{{ asset('modules/authentication/js/admin-two-factor-recovery.js') }}"></script>
    <script src="{{ asset('modules/authentication/js/admin-two-factor-timer.js') }}"></script>
</body>
</html>
