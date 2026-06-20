<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account Settings') - SK OnePortal</title>
    <link rel="stylesheet" href="{{ url('/modules/authentication/css/forgot-password.css') }}">
    <link rel="stylesheet" href="{{ url('/modules/profile/css/change-email.css') }}">
    @php $skFedAuthCssVersion = @filemtime(app_path('Modules/Profile/assets/css/sk-fed-account-auth.css')) ?: time(); @endphp
    <link rel="stylesheet" href="{{ url('/modules/profile/css/sk-fed-account-auth.css') }}?v={{ $skFedAuthCssVersion }}">
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
    @stack('styles')
</head>
<body class="sk-login-page">

    <div class="sk-bg-wrapper">
        <div class="sk-bg-image"></div>
        <div class="sk-gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <main class="sk-login-container">
        <div class="sk-branding-section">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <img src="{{ asset('Images/SK_OnePortal.png') }}" alt="SK OnePortal Logo" class="sk-logo">
                </div>
                <h1 class="sk-main-title">SK OnePortal</h1>
                <p class="sk-tagline">SK Federations Portal – Santa Cruz, Laguna</p>
            </div>
        </div>

        <div class="sk-login-section">
            <div class="sk-login-card @yield('card-class', '')">
                @yield('content')
            </div>
        </div>
    </main>

    @stack('scripts-before')
    <script src="{{ url('/shared/js/loading.js') }}"></script>
    @stack('scripts')
</body>
</html>
