@extends('layouts.auth')

@section('title', 'OnePortal Admin — Two-Factor Verification')

@section('content')
    <div class="login-card-header">
        <div class="fp-icon-wrap mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
        </div>
        <h2 class="login-heading">Two-Factor Verification</h2>
        <p class="login-subheading">Enter the 6-digit code from your authenticator app.</p>
    </div>

    {{-- Timer --}}
    <div class="d-flex align-items-center justify-content-center gap-2 mb-3 challenge-timer-wrap"
         data-challenge-expiry-seconds="600">
        <span style="font-size:0.8rem;color:#6b7a99;">Session expires in</span>
        <span id="challenge-timer"
              style="background:var(--op-blue-pale);color:var(--op-blue);font-size:0.85rem;padding:5px 10px;border-radius:6px;font-weight:600;"
              aria-live="polite">10:00</span>
    </div>

    <div class="login-alert login-alert--danger" id="challenge-expired-message" hidden role="alert">
        This verification window has expired. Please
        <a href="{{ route('login') }}" class="login-forgot">return to login</a> and try again.
    </div>

    @if ($errors->any())
        <div class="login-alert login-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ url('/two-factor-challenge') }}" novalidate>
        @csrf
        @php($oldCode = preg_replace('/\D/', '', (string) old('code', '')))

        <div class="login-field-group">
            <label class="login-label text-center d-block">Authentication Code</label>
            <div class="otp-input-group" data-otp-group>
                <input type="hidden" id="code" name="code" value="{{ $oldCode }}">
                @for ($i = 0; $i < 6; $i++)
                    <input class="otp-digit" type="text" inputmode="numeric" pattern="[0-9]*"
                        maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                        value="{{ $oldCode[$i] ?? '' }}" aria-label="OTP digit {{ $i + 1 }}" required>
                @endfor
            </div>
        </div>

        <button type="submit" class="login-btn mb-3">Authenticate</button>
        <p class="text-center mb-0" style="font-size:0.85rem;color:#6b7a99;">
            <a href="{{ route('login') }}" class="login-forgot">Back to Login</a>
        </p>
    </form>
@endsection
