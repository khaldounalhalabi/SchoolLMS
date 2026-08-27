<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Forgot Password') }} — SchoolLMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface-2);
            -webkit-font-smoothing: antialiased;
        }
        [dir="rtl"] body, [dir="rtl"] h1 { font-family: 'Cairo', sans-serif; }
        .card {
            background: var(--surface);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-auth);
            border: 1px solid var(--border);
        }
        /* Keep the card off the screen edges and ease its padding on phones. */
        @media (max-width: 480px) {
            body { padding: 16px; }
            .card { padding: 28px 22px; border-radius: 16px; }
        }
        .icon-wrap {
            width: 56px; height: 56px;
            background: var(--primary-tint);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .icon-wrap svg { width: 26px; height: 26px; color: var(--primary); }
        h1 { font-family: var(--font-display); font-size: 24px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .sub { font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 28px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-strong); margin-bottom: 7px; }
        input[type="email"] {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14px; font-family: var(--font-body); color: var(--text-primary);
            background: var(--surface-3); outline: none; transition: all 0.2s;
        }
        input[type="email"]:focus { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
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
        .success-banner {
            background: var(--success-tint); border: 1px solid var(--success-border);
            border-radius: 10px; padding: 14px 16px;
            font-size: 13.5px; color: var(--success-text); margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .success-banner svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
        .back-link {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--primary); text-decoration: none;
            font-weight: 500; margin-top: 20px; justify-content: center;
        }
        .back-link:hover { text-decoration: underline; }
        .back-link svg { width: 14px; height: 14px; }
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
        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>

        <h1>{{ __("Forgot your password?") }}</h1>
        <p class="sub">{{ __("Enter your email address and we'll send you a code to reset your password.") }}</p>

        @if(session('status'))
            <div class="success-banner">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">{{ __("Email Address") }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="you@school.edu" required autocomplete="email">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn">{{ __("Send Reset Code") }}</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __("Back to login") }}
        </a>
    </div>
</body>
</html>
