<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Your password reset code') }}</title>
</head>
<body style="margin:0; padding:0; background:#f8fafc; font-family: 'DM Sans', Arial, sans-serif;">
    <div style="max-width:420px; margin:40px auto; background:#ffffff; border-radius:16px; padding:36px; border:1px solid #e2e8f0;">
        <h1 style="font-size:20px; color:#0f172a; margin:0 0 12px;">{{ __('Password reset code') }}</h1>
        <p style="font-size:14px; color:#64748b; line-height:1.6; margin:0 0 24px;">
            {{ __('Use the code below to reset your password. This code expires in :minutes minutes.', ['minutes' => $expiresInMinutes]) }}
        </p>
        <div style="text-align:center; background:#eef2ff; border-radius:10px; padding:18px; margin-bottom:24px;">
            <span style="font-size:32px; font-weight:700; letter-spacing:8px; color:#4F46E5;">{{ $otp }}</span>
        </div>
        <p style="font-size:12.5px; color:#94a3b8; line-height:1.6; margin:0;">
            {{ __("If you didn't request this, you can safely ignore this email.") }}
        </p>
    </div>
</body>
</html>
