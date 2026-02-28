<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .check-wrap {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #e8f7ee;
            display: grid;
            place-items: center;
            margin: 0 auto;
        }

        .checkmark {
            width: 28px;
            height: 56px;
            border-right: 6px solid #198754;
            border-bottom: 6px solid #198754;
            transform: rotate(45deg) scale(0);
            animation: pop-check 0.45s ease-out forwards;
            transform-origin: bottom left;
        }

        @keyframes pop-check {
            0% { transform: rotate(45deg) scale(0); opacity: 0; }
            100% { transform: rotate(45deg) scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="check-wrap mb-4" aria-hidden="true">
                            <span class="checkmark"></span>
                        </div>
                        <h1 class="h4 mb-2">Email Verified Successfully</h1>
                        <p class="text-muted mb-4">Your SK Federation account is now verified. You may continue to sign in securely.</p>
                        <a class="btn btn-success" href="{{ route('login') }}">Proceed to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
