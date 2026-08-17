<!DOCTYPE html>
<html lang="en">
<head>
    @include('favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation Link Unavailable - SK OnePortal</title>
    @vite(['app/Modules/Authentication/assets/css/sign-in.css'])
</head>
<body class="youth-login-page">
    <main class="youth-login-container" style="justify-content:center;padding:2rem;">
        <div class="youth-login-card" style="max-width:28rem;">
            <h1 class="card-title">Activation link unavailable</h1>
            <p class="card-subtitle">{{ $message }}</p>
            <a href="{{ route('sign-in') }}" class="youth-submit-btn" style="display:inline-flex;margin-top:1rem;text-decoration:none;">Go to Sign in</a>
        </div>
    </main>
</body>
</html>
