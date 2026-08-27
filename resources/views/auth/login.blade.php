<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Login') }} — SchoolLMS</title>
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
            -webkit-font-smoothing: antialiased;
            background: var(--sidebar-bg);
        }

        [dir="rtl"] body,
        [dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3 {
            font-family: var(--font-display-rtl);
        }

        /* Left panel — brand */
        .brand-panel {
            width: 44%;
            background: var(--sidebar-bg);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 56px;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 30% 30%, color-mix(in srgb, var(--primary-light) 35%, transparent) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 70%, color-mix(in srgb, var(--primary) 25%, transparent) 0%, transparent 60%);
        }

        /* Geometric decorations */
        .geo-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid color-mix(in srgb, var(--primary-light) 20%, transparent);
        }

        .geo-ring-1 { width: 300px; height: 300px; top: -80px; right: -80px; }
        .geo-ring-2 { width: 200px; height: 200px; top: -40px; right: -40px; border-color: color-mix(in srgb, var(--primary-light) 30%, transparent); }
        .geo-ring-3 { width: 400px; height: 400px; bottom: -120px; left: -120px; }
        .geo-ring-4 { width: 250px; height: 250px; bottom: -80px; left: -80px; border-color: color-mix(in srgb, var(--primary-light) 30%, transparent); }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(color-mix(in srgb, var(--primary-light) 6%, transparent) 1px, transparent 1px),
                linear-gradient(90deg, color-mix(in srgb, var(--primary-light) 6%, transparent) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .brand-illustration {
            position: absolute;
            z-index: 0;
            left: 50%;
            bottom: -22%;
            width: min(760px, 116%);
            max-width: none;
            height: auto;
            pointer-events: none;
            opacity: 0.34;
            transform: translateX(-50%);
            filter: contrast(0.82) saturate(0.62) drop-shadow(0 28px 22px rgba(0, 0, 0, 0.24));
            mix-blend-mode: screen;
            -webkit-mask-image: radial-gradient(ellipse 62% 57% at 50% 47%, #000 32%, rgba(0, 0, 0, 0.78) 56%, transparent 78%);
            mask-image: radial-gradient(ellipse 62% 57% at 50% 47%, #000 32%, rgba(0, 0, 0, 0.78) 56%, transparent 78%);
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            z-index: 1;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(18, 42, 29, 0.2) 0%, rgba(18, 42, 29, 0.34) 58%, rgba(18, 42, 29, 0.52) 100%);
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 48px;
        }

        .brand-logo-mark {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px color-mix(in srgb, var(--primary) 50%, transparent);
        }

        .brand-logo-mark svg { width: 26px; height: 26px; color: var(--on-primary); }

        .brand-name {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: white;
        }

        .brand-tagline {
            font-size: 11px;
            color: var(--sidebar-text-dim);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-headline {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 700;
            color: white;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .brand-headline em {
            font-style: italic;
            color: var(--primary-light);
        }

        .brand-desc {
            font-size: 14px;
            color: var(--sidebar-text);
            line-height: 1.7;
            max-width: 340px;
            margin-bottom: 48px;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--sidebar-text-dim);
        }

        .brand-feature-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary-light);
            flex-shrink: 0;
        }

        /* Right panel — form */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
            padding: 40px 48px;
            position: relative;
        }

        .auth-lang-switcher {
            position: absolute;
            top: 24px;
            inset-inline-end: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        .form-header {
            margin-bottom: 36px;
        }

        .form-title {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-strong);
            margin-bottom: 7px;
            letter-spacing: 0.3px;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: var(--font-body);
            color: var(--text-primary);
            background: var(--surface-3);
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: var(--surface);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent);
        }

        .form-input.error {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--danger) 10%, transparent);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            inset-inline-start: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-wrapper .form-input {
            padding-inline-start: 38px;
        }

        .password-wrapper .form-input {
            padding-inline-end: 44px;
        }

        .password-toggle {
            position: absolute;
            inset-inline-end: 10px;
            top: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            color: var(--text-muted);
            background: transparent;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            transform: translateY(-50%);
            transition: color 0.2s, background 0.2s;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            color: var(--primary);
            background: var(--primary-tint);
            outline: none;
        }

        .password-toggle svg {
            width: 17px;
            height: 17px;
        }

        .error-msg {
            font-size: 12px;
            color: var(--danger);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            border-radius: 4px;
        }

        .forgot-link {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover { text-decoration: underline; }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            color: var(--on-primary);
            border: none;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.2px;
            box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 35%, transparent);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px color-mix(in srgb, var(--primary) 45%, transparent);
        }

        .submit-btn:active { transform: translateY(0); }

        .form-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--text-faint);
            font-size: 12px;
        }

        .form-divider::before, .form-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .demo-credentials {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .demo-credentials strong {
            display: block;
            color: var(--text-strong);
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .demo-cred-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .demo-cred-item code {
            font-family: monospace;
            color: var(--primary);
            font-size: 12px;
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .brand-panel { width: 100%; padding: 32px 24px 24px; min-height: auto; }
            .brand-headline { font-size: 26px; }
            .brand-features { display: none; }
            .brand-illustration {
                bottom: -30%;
                width: 620px;
                opacity: 0.28;
            }
            /* Stack the panel so the switcher row can sit above the card.
               Absolute positioning only works on desktop, where the panel is a
               tall column with empty space at the top; once the panel stacks
               under the brand banner the form starts at the top edge and the
               switchers land on the "Welcome back" heading. */
            .form-panel {
                padding: 24px;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
            }
            .auth-lang-switcher {
                position: static;
                width: 100%;
                max-width: 400px;
                justify-content: flex-end;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Brand Panel -->
    <div class="brand-panel">
        <div class="geo-ring geo-ring-1"></div>
        <div class="geo-ring geo-ring-2"></div>
        <div class="geo-ring geo-ring-3"></div>
        <div class="geo-ring geo-ring-4"></div>
        <div class="grid-overlay"></div>
        <img class="brand-illustration" src="{{ asset('images/login-education.png') }}" alt="" aria-hidden="true">

        <div class="brand-content">
            <div class="brand-logo">
                <div class="brand-logo-mark">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-name">SchoolLMS</div>
                    <div class="brand-tagline">{{ __("Management System") }}</div>
                </div>
            </div>

            <h1 class="brand-headline">
                {{ __("Education") }}<br>
                <em>{{ __("Reimagined") }}</em><br>
                {{ __("for the Modern Era") }}
            </h1>

            <p class="brand-desc">
                {{ __("A comprehensive school management platform designed for administrators, teachers, students, and parents.") }}
            </p>

            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-dot"></div>
                    {{ __("Role-based access for all stakeholders") }}
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-dot"></div>
                    {{ __("Real-time grades and attendance tracking") }}
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-dot"></div>
                    {{ __("Arabic RTL support built-in") }}
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-dot"></div>
                    {{ __("Mobile-first responsive design") }}
                </div>
            </div>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="form-panel">
        <div class="auth-lang-switcher">
            <x-language-switcher />
            <x-theme-switcher />
        </div>
        <div class="login-card">
            <div class="form-header">
                <h2 class="form-title">{{ __("Welcome back") }}</h2>
                <p class="form-subtitle">{{ __("Sign in to your school account") }}</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">{{ __("Email Address") }}</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@school.edu"
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                            autocomplete="email"
                            required
                        >
                    </div>
                    @error('email')
                        <div class="error-msg">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">{{ __("Password") }}</label>
                    <div class="input-wrapper password-wrapper">
                        <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="password-toggle" id="password-toggle"
                                data-password-toggle="password"
                                data-show-label="{{ __('Show password') }}"
                                data-hide-label="{{ __('Hide password') }}"
                                aria-label="{{ __('Show password') }}" aria-pressed="false">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-msg">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-footer">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        {{ __("Remember me") }}
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-link">{{ __("Forgot password?") }}</a>
                </div>

                <button type="submit" class="submit-btn">
                    {{ __("Sign In to Dashboard") }}
                </button>
            </form>

        </div>
    </div>

</body>
</html>
