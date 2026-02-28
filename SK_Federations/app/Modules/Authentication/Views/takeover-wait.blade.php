<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Session Recovery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Account currently active on another device</h1>
                        <p class="text-muted mb-3">
                            Verify ownership to continue. We can send a one-time code to <strong>{{ $email }}</strong>
                            and securely end the old session.
                        </p>

                        @if (session('status'))
                            <div class="alert alert-info" role="alert">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('skfed.takeover.send', [], false) }}" class="d-grid gap-2 mb-3">
                            @csrf
                            <button
                                type="submit"
                                class="btn btn-outline-primary"
                                @if ($resendLocked) disabled @endif
                            >
                                Send Email Verification Code
                            </button>
                        </form>

                        @if ($cooldownSeconds > 0)
                            <p class="small text-muted mb-3">
                                You can request another code in {{ $cooldownSeconds }} seconds.
                            </p>
                        @endif

                        <form method="POST" action="{{ route('skfed.takeover.verify', [], false) }}" class="d-grid gap-2">
                            @csrf
                            <label for="otp_code" class="form-label">Verification Code</label>
                            <input
                                id="otp_code"
                                name="otp_code"
                                type="text"
                                inputmode="numeric"
                                maxlength="6"
                                class="form-control"
                                placeholder="Enter 6-digit code"
                                required
                                autofocus
                            >
                            <button type="submit" class="btn btn-primary">Verify and Continue</button>
                            <a href="{{ route('login', [], false) }}" class="btn btn-link">Back to login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
