<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Secure Session Recovery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <style>
        .email-highlight {
            color: #213F99;
            font-weight: 600;
        }

        .cooldown-text {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 16px;
            text-align: center;
        }

        .btn-outline-custom {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            color: #213F99;
            background: white;
            border: 2px solid #213F99;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }

        .btn-outline-custom:hover:not(:disabled) {
            background: #213F99;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(33, 63, 153, 0.3);
        }

        .btn-outline-custom:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .email-highlight {
                display: inline-block;
                word-break: break-word;
            }

            .cooldown-text {
                font-size: 13px;
            }

            .btn-outline-custom {
                padding: 14px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .btn-outline-custom {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Account Currently Active</h2>
                    <p>Verify ownership to continue. We can send a one-time code to <span class="email-highlight">{{ $email }}</span> and securely end the old session.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-info" role="alert">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('skfed.takeover.send', [], false) }}">
                    @csrf
                    <button
                        type="submit"
                        class="btn-outline-custom"
                        @if ($resendLocked) disabled @endif
                    >
                        Send Email Verification Code
                    </button>
                </form>

                @if ($cooldownSeconds > 0)
                    <p class="cooldown-text">
                        You can request another code in {{ $cooldownSeconds }} seconds.
                    </p>
                @endif

                <form method="POST" action="{{ route('skfed.takeover.verify', [], false) }}" class="login-form">
                    @csrf
                    <div class="form-group">
                        <label for="otp_code">Verification Code</label>
                        <input
                            id="otp_code"
                            name="otp_code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            class="form-control @error('otp_code') is-invalid @enderror"
                            placeholder="Enter 6-digit code"
                            required
                            autofocus
                        >
                        @error('otp_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="login-btn btn btn-primary w-100">Verify and Continue</button>
                </form>

                <div class="form-footer">
                    <a href="{{ route('login', [], false) }}">Back to login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('/modules/authentication/js/script.js') }}"></script>
</body>
</html>
