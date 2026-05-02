<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Password - KK Profiling - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 2rem;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
            width: 100%;
            padding: 3rem;
        }
        .card-header { text-align: center; margin-bottom: 2rem; }
        .card-header img { height: 48px; margin-bottom: 1rem; }
        .card-header h1 { font-size: 1.5rem; color: #1a1a1a; margin-bottom: 0.5rem; }
        .card-header p { color: #666; font-size: 0.9rem; }
        .field { margin-bottom: 1.25rem; }
        .field label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            padding: 0.75rem 2.75rem 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus { outline: none; border-color: #667eea; }
        .toggle-btn {
            position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9ca3af; padding: 0;
        }
        .toggle-btn svg { width: 20px; height: 20px; }
        .hint { font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem; }
        .error { font-size: 0.8rem; color: #ef4444; margin-top: 0.25rem; }
        .alert-success {
            background: #f0fdf4; border: 1px solid #86efac; color: #166534;
            padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;
        }
        .submit-btn {
            width: 100%; padding: 0.875rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 6px;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: opacity 0.2s;
        }
        .submit-btn:hover { opacity: 0.9; }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>
    @include('dashboard::loading')

    <div class="card">
        <div class="card-header">
            <img src="/images/skoneportal_logo.webp" alt="SK OnePortal">
            <h1>Set Your Password</h1>
            <p>Almost done! Create a password for your <strong>{{ $barangay }}</strong> KK account.</p>
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-success" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('kkprofiling.store-password', ['barangay' => request()->route('barangay')]) }}" id="setPasswordForm">
            @csrf
            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                    <button type="button" class="toggle-btn" onclick="toggleVisibility('password', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="hint">Minimum 8 characters</div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Re-enter your password">
                    <button type="button" class="toggle-btn" onclick="toggleVisibility('password_confirmation', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="error" id="matchError" style="display:none;">Passwords do not match.</div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">Complete Registration</button>
        </form>
    </div>

    <script>
        function toggleVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.getElementById('setPasswordForm').addEventListener('submit', function(e) {
            const pw = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const err = document.getElementById('matchError');

            if (pw !== confirm) {
                e.preventDefault();
                err.style.display = 'block';
                return;
            }
            err.style.display = 'none';
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Creating account...';
            if (window.showLoading) window.showLoading('Creating your account...');
        });
    </script>
</body>
</html>
