<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Verify Your Email</h1>
                        <p class="text-muted mb-3">
                            Your SK Federation account requires email verification before secure access can continue.
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

                        <form method="POST" action="{{ route('skfed.verification.resend') }}" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label for="email" class="form-label">Email Address</label>
                                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                        </form>

                        <a href="{{ route('login') }}" class="btn btn-link px-0 mt-3">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
