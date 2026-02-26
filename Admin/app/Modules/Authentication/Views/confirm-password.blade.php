<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Confirm Password - Admin Portal</title>
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
        <div class="two-factor-container">
            <div class="two-factor-header">
                <div class="header-icon">
                    <img src="{{ asset('modules/authentication/images/SKOneportal_logo.webp') }}" alt="SK One Portal Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <h1>Confirm Your Password</h1>
                <p>Please confirm your password to continue</p>
            </div>

            <div class="two-factor-body">
                <form method="POST" action="{{ route('password.confirm.store') }}">
                    @csrf

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control has-right-icon"
                                placeholder="Enter your password"
                                required
                                autofocus
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg id="toggleIcon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="#1e293b">
                                    <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-verify">
                        <span>Confirm</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('modules/authentication/js/admin-login.js') }}"></script>
</body>
</html>
