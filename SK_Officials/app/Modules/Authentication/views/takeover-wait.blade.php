<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Verification - SK Officials</title>
</head>
<body>
    <h1>Session verification required</h1>
    <p>Send a verification code to {{ $email }} to continue on this device.</p>
    <form method="POST" action="{{ route('sk_official.takeover.send') }}">
        @csrf
        <button type="submit" @disabled($resendLocked)>Send Code</button>
    </form>
    <form method="POST" action="{{ route('sk_official.takeover.verify') }}">
        @csrf
        <label for="otp_code">Verification code</label>
        <input id="otp_code" name="otp_code" maxlength="6" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
