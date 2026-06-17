{{-- Step 4: Legacy Email Verification + Account Setup cards (same as pre-wizard flow) --}}
<section class="kkp-wizard-panel kkp-wizard-step4-legacy" id="kkpWizardStep4" data-wizard-step="4" hidden>

    {{-- Email Verification Card --}}
    <div class="verify-card" id="emailVerifyCard">
        <div class="verify-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
        </div>
        <h1 class="verify-title">Verify Your Email</h1>
        <p class="verify-message">
            Thank you for submitting your KK Profiling form! We've sent a verification link to:
        </p>
        <p class="verify-email" id="displayEmail">your-email@example.com</p>
        <p class="verify-instruction">
            Please check your email and click the verification link to complete your registration.
        </p>
        <p class="kkp-wizard-email-error" id="kkpWizardEmailError" hidden></p>
        <div class="verify-help">
            <p>Didn't receive the email?</p>
            <div class="verify-resend-wrap">
                <button type="button" class="verify-resend-btn" id="resendEmailBtn" disabled>
                    Resend verification email
                </button>
                <span class="verify-resend-timer" id="resendTimer" style="display:none;"></span>
            </div>
        </div>
        <div class="verify-actions">
            <a href="{{ route('kkprofiling.signup') }}" class="verify-btn verify-btn-secondary">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to KK Profiling
            </a>
        </div>
    </div>

    {{-- Set Password Card --}}
    <div class="setpw-card" id="setPasswordCard" style="display:none;">
        <div class="setpw-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h1 class="setpw-title">Set Your Password</h1>
        <p class="setpw-message">
            Your email has been verified! Please create a secure password for your account.
        </p>
        <form id="setPasswordForm" class="setpw-form">
            <div class="setpw-field">
                <label for="password" class="setpw-label">Password</label>
                <div class="setpw-input-wrapper">
                    <input type="password" id="password" name="password" class="setpw-input"
                        placeholder="Enter your password" required minlength="8">
                    <button type="button" class="setpw-toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <div class="setpw-hint">Minimum 8 characters</div>
            </div>
            <div class="setpw-field">
                <label for="password_confirmation" class="setpw-label">Confirm Password</label>
                <div class="setpw-input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="setpw-input" placeholder="Confirm your password" required minlength="8">
                    <button type="button" class="setpw-toggle-btn" id="togglePasswordConfirm" aria-label="Toggle password visibility">
                        <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="setpw-submit-btn" id="setpwSubmitBtn">
                <svg class="setpw-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                <svg class="setpw-btn-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                </svg>
                <span class="setpw-btn-text">Complete Registration</span>
            </button>
        </form>
        <div class="setpw-footer">
            <a href="{{ route('kkprofiling.signup') }}" class="setpw-back-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to KK Profiling
            </a>
        </div>
    </div>

    {{-- Registration Success Card --}}
    <div class="reg-success-card" id="regSuccessCard" style="display:none;">
        <div class="reg-success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h1 class="reg-success-title">Registration Complete!</h1>
        <p class="reg-success-message">
            Your KK Profiling registration has been completed successfully.<br>
            You can now login with your credentials.
        </p>
        <a href="/youth/login" class="reg-success-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Go to Login
        </a>
    </div>

</section>
