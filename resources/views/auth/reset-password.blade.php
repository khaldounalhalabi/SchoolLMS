<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reset Password') }} — SchoolLMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: var(--surface-2); -webkit-font-smoothing: antialiased;
        }
        .card {
            background: var(--surface); border-radius: 20px; padding: 40px;
            width: 100%; max-width: 420px;
            box-shadow: var(--shadow-auth); border: 1px solid var(--border);
        }
        /* Keep the card off the screen edges and ease its padding on phones. */
        @media (max-width: 480px) {
            body { padding: 16px; }
            .card { padding: 28px 22px; border-radius: 16px; }
        }
        h1 { font-family: var(--font-display); font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        [dir="rtl"] body, [dir="rtl"] h1 { font-family: 'Cairo', sans-serif; }
        .sub { font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 28px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-strong); margin-bottom: 7px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: var(--font-body); color: var(--text-primary);
            background: var(--surface-3); outline: none; transition: all 0.2s;
        }
        input:focus { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .error { font-size: 12px; color: var(--danger); margin-top: 5px; }
        .btn {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            color: var(--on-primary); border: none; border-radius: 10px;
            font-size: 14.5px; font-weight: 600; font-family: var(--font-body);
            cursor: pointer; transition: all 0.2s;
            box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 35%, transparent);
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px color-mix(in srgb, var(--primary) 45%, transparent); }
        .back-link {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--primary); text-decoration: none;
            font-weight: 500; margin-top: 20px; justify-content: center;
        }
        .back-link:hover { text-decoration: underline; }
        .auth-lang-switcher {
            position: fixed;
            top: 24px;
            inset-inline-end: 24px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        /* Tuck the controls closer to the corner and give the card room to
           clear them on a short screen. */
        @media (max-width: 480px) {
            .auth-lang-switcher { top: 12px; inset-inline-end: 12px; gap: 8px; }
            body { padding-top: 64px; }
        }
    </style>
</head>
<body>
    <div class="auth-lang-switcher">
        <x-language-switcher />
        <x-theme-switcher />
    </div>
    <div class="card">
        <h1>{{ __("Reset your password") }}</h1>
        <p class="sub">{{ __("Enter the code we emailed you and choose a new password.") }}</p>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">{{ __("Email Address") }}</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}"
                       required autocomplete="email">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="otp">{{ __("Reset Code") }}</label>
                <input type="text" id="otp" name="otp" value="{{ old('otp') }}"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       placeholder="123456" required autocomplete="one-time-code">
                @error('otp')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">{{ __("New Password") }}</label>
                <input type="password" id="password" name="password"
                       required autocomplete="new-password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">{{ __("Confirm New Password") }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       required autocomplete="new-password">
            </div>

            <button type="submit" class="btn">{{ __("Reset Password") }}</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">{{ __("Back to login") }}</a>
    </div>
</body>
</html>
