<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Reset Your Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('/modules/authentication/css/style.css') }}" rel="stylesheet">
    <style>
        /* Remove browser validation styling */
        input[type="email"] {
            background-image: none !important;
            background-color: white !important;
            border: 2px solid #e2e8f0 !important;
        }

        input[type="email"]:valid,
        input[type="email"]:invalid {
            background-image: none !important;
            border-color: #e2e8f0 !important;
            background-color: white !important;
            box-shadow: none !important;
        }

        input[type="email"]:focus {
            border-color: #213F99 !important;
            box-shadow: 0 0 0 4px rgba(33, 63, 153, 0.1) !important;
            background-image: none !important;
            outline: none !important;
        }

        input[type="email"].is-invalid {
            border-color: #dc3545 !important;
        }

        input[type="email"].is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1) !important;
        }
    </style>
</head>
<body>
    <script>
        // Prevent back navigation and redirect if authenticated
        (function() {
            @auth
                window.location.replace("{{ route('dashboard') }}");
            @endauth
            
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        })();
    </script>
    <div class="login-container">
        <div class="background-section">
            <div class="logo-container">
                <img src="{{ url('/modules/authentication/images/Sk_Fed_logo.png') }}" alt="SK Federations Logo" class="large-logo">
                <h1 class="brand-title">SK Federations</h1>
                <p class="brand-subtitle">Santa Cruz Youth Leadership Portal</p>
            </div>

            <div class="login-form-container">
                <div class="form-header">
                    <h2>Reset Your Password</h2>
                    <p>Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <form id="forgot-password-form" class="login-form" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            required
                            placeholder="Enter your email"
                            maxlength="100"
                        >
                        <div class="invalid-feedback" style="display: none;"></div>
                    </div>

                    <button type="submit" class="login-btn btn btn-primary w-100">
                        Send Reset Link
                    </button>
                </form>

                <div class="form-footer">
                    <a href="{{ route('login', [], false) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const emailInput = document.getElementById('email');
            const errorMsg = emailInput.nextElementSibling;

            // Reset error state
            emailInput.classList.remove('is-invalid');
            errorMsg.style.display = 'none';

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email) {
                emailInput.classList.add('is-invalid');
                errorMsg.textContent = 'Please enter your email address.';
                errorMsg.style.display = 'block';
                return;
            }

            if (!emailRegex.test(email)) {
                emailInput.classList.add('is-invalid');
                errorMsg.textContent = 'Please enter a valid email address.';
                errorMsg.style.display = 'block';
                return;
            }

            // Prototype: If validation passes, redirect to reset password page
            // Note: In real implementation, server would check if email exists
            window.location.href = "{{ url('/reset-password') }}";
        });

        // Remove error on input
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.nextElementSibling.style.display = 'none';
        });
    </script>
</body>
</html>
