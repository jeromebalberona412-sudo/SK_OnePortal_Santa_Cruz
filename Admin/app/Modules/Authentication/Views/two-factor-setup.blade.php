<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Two-Factor Authentication - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-auth.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/admin-two-factor.css') }}?v={{ time() }}">
</head>
<body>
    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="two-factor-wrapper">
        <div class="two-factor-container" style="max-width: 600px;">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <h1>Enable Two-Factor Authentication</h1>
                <p>2FA is required for all administrators</p>
            </div>

            <div class="two-factor-body">
                <div class="space-y-6">
                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div style="flex: 1;">
                            <strong style="display: block; margin-bottom: 8px;">Setup Instructions</strong>
                            <ol style="list-style: decimal; padding-left: 20px; font-size: 13px; line-height: 1.6;">
                                <li>Install an authenticator app (Google Authenticator, Authy, 1Password)</li>
                                <li>Scan the QR code below with your app</li>
                                <li>Enter the 6-digit code to confirm</li>
                                <li>Save your recovery codes securely</li>
                            </ol>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div style="text-align: center; padding: 20px;">
                        <div style="display: inline-block; padding: 20px; background: white; border: 2px solid #e2e8f0; border-radius: 12px;">
                            {!! $QRCode !!}
                        </div>
                    </div>

                    <!-- Manual Entry -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                        <p style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">Can't scan? Enter this code manually:</p>
                        <code style="display: block; text-align: center; font-size: 16px; font-family: monospace; background: white; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; user-select: all;">
                            {{ $secretKey }}
                        </code>
                    </div>

                    <!-- Confirmation Form -->
                    <form method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="code">Enter the 6-digit code from your authenticator app</label>
                            <div class="code-input-container">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            </div>
                            <input type="hidden" name="code" id="fullCode">
                        </div>

                        <button type="submit" class="btn-verify" id="verifyBtn" disabled>
                            <span>Confirm and Enable 2FA</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('modules/authentication/js/admin-two-factor.js') }}"></script>
</body>
</html>
