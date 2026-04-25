@extends('layouts.auth')

@section('title', 'OnePortal Admin — Forgot Password')

@section('content')
    <div class="login-card-header">
        <div class="fp-icon-wrap mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
        </div>
        <h2 class="login-heading">Forgot Password?</h2>
        <p class="login-subheading">
            Enter the email address associated with your account and
            we'll send you a link to reset your password.
        </p>
    </div>

    @if (session('status'))
        <div class="login-alert login-alert--success" role="alert">
            <strong>Email sent!</strong> {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="login-field-group">
            <label class="login-label" for="email">Email Address</label>
            <div class="login-input-wrap">
                <span class="login-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.029 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                </span>
                <input type="email" id="email" name="email"
                    class="login-input @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="Enter your email address"
                    required autofocus autocomplete="email">
            </div>
        </div>

        <button type="submit" class="login-btn mb-3">Send Reset Link</button>

        <p class="text-center mb-0" style="font-size:0.85rem;color:#6b7a99;">
            Remember your password?
            <a href="{{ route('login') }}" class="login-forgot">Back to Login</a>
        </p>
    </form>
@endsection
