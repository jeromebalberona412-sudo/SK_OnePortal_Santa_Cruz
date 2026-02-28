<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Verification Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3">Device Verification Required</h1>
                        <p class="text-muted">
                            A verification email has been sent@if($email) to <strong>{{ $email }}</strong>@endif for this sign-in attempt.
                            Access remains blocked until the new device is verified.
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

                        <a href="{{ route('login') }}" class="btn btn-primary">Back to login</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
