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
    <link rel="stylesheet" href="{{ asset('modules/authentication/css/preloader.css') }}?v={{ time() }}">
</head>
<body>
    <!-- Simple Preloader -->
    <div id="preloader" class="preloader">
        <div class="preloader-content">
            <div class="spinner-logo">
                <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK Logo">
            </div>
            <h2>SK OnePortal</h2>
            <p>Loading...</p>
        </div>
    </div>

    <div class="background-animation">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="two-factor-wrapper">
        <div class="two-factor-container">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
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
                            placeholder="XXXXXXXXXX"
                            style="text-align: center; font-family: monospace; letter-spacing: 2px;"
                        >
                        <div class="helper-text">
                            Enter one of your recovery codes
                        </div>
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

    <script src="{{ asset('modules/authentication/js/admin-two-factor.js') }}"></script>
    <script>
    function toggleRecoveryCode(event) {
        event.preventDefault();
        const twoFactorForm = document.getElementById('twoFactorForm');
        const recoveryForm = document.getElementById('recoveryForm');
        
        if (twoFactorForm.style.display === 'none') {
            twoFactorForm.style.display = 'block';
            recoveryForm.style.display = 'none';
        } else {
            twoFactorForm.style.display = 'none';
            recoveryForm.style.display = 'block';
            document.getElementById('recovery_code').focus();
        }
    }

    // 10-minute countdown timer
    (function() {
        const timerElement = document.getElementById('sessionTimer');
        let timeLeft = 10 * 60; // 10 minutes in seconds

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            timerElement.innerHTML = `Session expires in: <strong style="color: ${timeLeft < 60 ? '#ef4444' : '#10b981'}">${display}</strong>`;
            
            if (timeLeft <= 0) {
                // Session expired
                timerElement.innerHTML = '<strong style="color: #ef4444">Session expired!</strong>';
                
                // Disable forms
                document.getElementById('twoFactorForm').querySelectorAll('input, button').forEach(el => el.disabled = true);
                document.getElementById('recoveryForm').querySelectorAll('input, button').forEach(el => el.disabled = true);
                
                // Show alert and redirect
                setTimeout(() => {
                    alert('Your session has expired. Please login again.');
                    window.location.href = '{{ route('login') }}';
                }, 1000);
                
                return;
            }
            
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        
        updateTimer();
    })();

    // Simple Preloader
    window.addEventListener('load', function() {
        setTimeout(() => {
            document.getElementById('preloader').classList.add('fade-out');
        }, 800);
    });
    </script>
</body>
</html>
