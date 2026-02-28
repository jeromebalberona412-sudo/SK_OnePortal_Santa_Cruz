<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Verify Your Email to Continue</h1>
                        <p class="text-muted mb-3">
                            We sent a verification email to <strong>{{ $email }}</strong>. You can verify on any device.
                            This page will continue automatically when verification is complete.
                        </p>

                        <div class="alert alert-info" role="alert" id="verification-state">
                            Waiting for verification...
                        </div>

                        <p class="small text-muted mb-4" id="countdown">
                            Verification window: {{ $waitMinutes }} minutes.
                        </p>

                        <form method="POST" action="{{ route('skfed.verification.resend') }}" class="d-grid gap-2">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" class="btn btn-outline-primary">Resend Verification Email</button>
                            <a href="{{ route('login') }}" class="btn btn-link">Back to login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        (() => {
            const statusUrl = "{{ route('skfed.verification.wait.status') }}";
            const expiresAt = new Date("{{ $expiresAtIso }}");
            const stateElement = document.getElementById('verification-state');
            const countdownElement = document.getElementById('countdown');

            function renderCountdown() {
                const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                countdownElement.textContent = `Time remaining: ${minutes}m ${remainingSeconds}s`;
            }

            async function checkStatus() {
                try {
                    const response = await fetch(statusUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const payload = await response.json();

                    if (payload.state === 'verified' && payload.redirect) {
                        stateElement.className = 'alert alert-success';
                        stateElement.textContent = 'Email verified. Redirecting...';
                        window.location.replace(payload.redirect);
                        return;
                    }

                    if (payload.state === 'expired') {
                        stateElement.className = 'alert alert-warning';
                        stateElement.textContent = 'Verification window expired. Please sign in again.';
                        return;
                    }

                    if (payload.state === 'missing') {
                        stateElement.className = 'alert alert-warning';
                        stateElement.textContent = 'Verification session not found. Please sign in again.';
                        return;
                    }
                } catch (error) {
                    stateElement.className = 'alert alert-danger';
                    stateElement.textContent = 'Unable to check verification status. Retrying...';
                }

                renderCountdown();
                setTimeout(checkStatus, 3000);
            }

            renderCountdown();
            checkStatus();
        })();
    </script>
</body>
</html>
