@extends('layouts.auth')

@section('title', 'OnePortal Admin — Recovery Codes')

@section('content')
    <div class="login-card-header">
        <div class="fp-icon-wrap mb-3" style="background:#e8f5e9;color:#2e7d32;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
        </div>
        @if (isset($regenerated) && $regenerated)
            <h2 class="login-heading">Recovery Codes Refreshed</h2>
        @else
            <h2 class="login-heading">Two-Factor Enabled</h2>
        @endif
        <p class="login-subheading">Store each recovery code securely. Every code can only be used once.</p>
    </div>

    <div class="recovery-codes-grid mb-4">
        @foreach ($recoveryCodes as $code)
            <code class="recovery-code-item">{{ $code }}</code>
        @endforeach
    </div>

    <a href="{{ route('dashboard') }}" class="login-btn d-block text-decoration-none text-center">
        Continue to Dashboard
    </a>
@endsection
