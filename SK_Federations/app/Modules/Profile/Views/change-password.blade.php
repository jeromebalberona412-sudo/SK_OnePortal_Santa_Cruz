@extends('profile::layouts.account-auth')

@section('title', 'Change Password')

@section('card-class', 'ce-verify-card sk-fed-compact-card')

@php
    $pwMin = (int) config('sk_fed_auth.password_reset.password.min_length', 12);
    $pwMax = (int) config('sk_fed_auth.password_reset.password.max_length', 64);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/profile/css/sk-fed-account-auth.css') }}">
@endpush

@push('scripts')
    <script src="{{ url('/modules/profile/js/change-password.js') }}"></script>
@endpush

@section('content')
    <div class="card-header">
        <h2 class="card-title">Change Password</h2>
        <p class="card-subtitle">Confirm your account email, then set a new password. We will send a verification link before the change takes effect.</p>
    </div>

    @if ($errors->any())
        <div class="sk-alert sk-alert-error">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('password.change.update') }}" method="POST" class="sk-login-form sk-fed-auth-form" id="change-password-form" data-password-min-length="{{ $pwMin }}" data-password-max-length="{{ $pwMax }}" novalidate>
        @csrf

        <div class="sk-form-group" id="cpStepEmail">
            <label for="cpEmail" class="sk-label">Account Email</label>
            <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                <input type="email" id="cpEmail" name="email" class="sk-input @error('email') is-invalid @enderror" placeholder="Enter your account email" autocomplete="email" maxlength="255" value="{{ old('email', $user->email ?? '') }}" required>
            </div>
            @error('email')
                <div class="sk-field-error">{{ $message }}</div>
            @enderror
            <div class="sk-field-error" id="cpEmailClientError" hidden></div>
        </div>

        <div id="cpStepPassword" hidden>
            <div class="sk-form-group">
                <label for="password" class="sk-label">New Password</label>
                <div class="password-wrapper">
                    <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <input type="password" id="password" name="password" class="sk-input password-input @error('password') is-invalid @enderror" placeholder="Enter new password" autocomplete="new-password" minlength="{{ $pwMin }}" maxlength="{{ $pwMax }}">
                    <button type="button" class="pw-toggle-btn" data-target="password" aria-label="Show password" tabindex="-1">
                        <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                    </button>
                </div>
                <ul class="password-rules" id="passwordRules" aria-live="polite" hidden>
                    <li id="rule-length">At least {{ $pwMin }} characters</li>
                    <li id="rule-lowercase">At least one lowercase letter</li>
                    <li id="rule-uppercase">At least one uppercase letter</li>
                    <li id="rule-number">At least one number</li>
                    <li id="rule-special">At least one special character</li>
                </ul>
                @error('password')
                    <div class="sk-field-error">{{ $message }}</div>
                @enderror
                <div class="sk-field-error" id="password-client-error" hidden></div>
            </div>

            <div class="sk-form-group">
                <label for="password_confirmation" class="sk-label">Confirm New Password</label>
                <div class="password-wrapper">
                    <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="sk-input password-input" placeholder="Re-enter new password" autocomplete="new-password" minlength="{{ $pwMin }}" maxlength="{{ $pwMax }}">
                    <button type="button" class="pw-toggle-btn" data-target="password_confirmation" aria-label="Show password" tabindex="-1">
                        <svg class="pw-eye pw-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="pw-eye pw-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <div class="sk-field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="button" class="sk-submit-btn sk-fed-primary-btn" id="cpContinueBtn">
            <span>Continue</span>
        </button>

        <button type="submit" class="sk-submit-btn sk-fed-primary-btn" id="cpSubmitBtn" hidden>
            <span id="cpBtnText">Send Email Verification</span>
            <svg class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
            </svg>
        </button>
    </form>

    <div class="youth-register-section ce-back-section">
        <p class="register-text">
            <a href="{{ route('profile') }}#settings" class="register-link">← Back to Profile</a>
        </p>
    </div>
@endsection
