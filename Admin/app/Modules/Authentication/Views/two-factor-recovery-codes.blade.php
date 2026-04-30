<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK OnePortal Admin — Recovery Codes</title>
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
            if (t === 'dark' || (!t && d)) { document.documentElement.classList.add('dark'); }
        })();
    </script>
    <style>
        .recovery-codes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .recovery-code-item {
            display: block;
            padding: 0.6rem 0.75rem;
            background: #f1f5f9;
            border: 1px solid var(--op-gray-200);
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--op-gray-900);
            letter-spacing: 0.05em;
            text-align: center;
        }
        :root.dark .recovery-code-item {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }
        .recovery-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            color: #92400e;
        }
        :root.dark .recovery-warning {
            background: rgba(245,158,11,0.1);
            border-color: rgba(245,158,11,0.3);
            color: #fbbf24;
        }
        .recovery-warning svg { flex-shrink: 0; margin-top: 1px; }
    </style>
</head>
<body class="login-page">

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

                <div class="form-header">
                    @if (isset($regenerated) && $regenerated)
                        <h2>Recovery Codes Refreshed</h2>
                    @else
                        <h2>Two-Factor Enabled</h2>
                    @endif
                    <p>Store each recovery code securely. Every code can only be used once.</p>
                </div>

                <div class="recovery-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                    </svg>
                    <span>Save these codes somewhere safe. If you lose access to your authenticator app, these are your only way back in.</span>
                </div>

                <div class="recovery-codes-grid">
                    @foreach ($recoveryCodes as $code)
                        <code class="recovery-code-item">{{ $code }}</code>
                    @endforeach
                </div>

                <a href="{{ route('dashboard') }}" class="login-btn" style="display:block;text-align:center;text-decoration:none;">
                    Continue to Dashboard
                </a>

            </div>
        </div>
    </div>

</body>
</html>
