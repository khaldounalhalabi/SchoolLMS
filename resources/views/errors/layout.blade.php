<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} — {{ config('app.name', 'SchoolLMS') }}</title>
    <x-theme-script />
    <style>
        /* Self-contained on purpose: error pages must render even when the
           Vite bundle is unavailable, so they cannot rely on app.css tokens.
           The dark block below mirrors the app's mechanism (data-theme stamped
           pre-paint by the inline script) but keeps this page's own palette —
           light-mode appearance is unchanged.
           NOTE: this palette is still the pre-rebrand indigo while the app is
           forest green; aligning it is a light-mode visual change and so is
           deliberately out of scope here. */
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #667085;
            --line: #e4e7ec;
            --surface: #ffffff;
            --background: #f7f8fc;
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --secondary-hover: #f9fafb;
            --on-primary: #fff;
        }

        :root[data-theme="dark"] {
            color-scheme: dark;
            --ink: #E8EAE4;
            --muted: #A0A6AD;
            --line: #333B3E;
            --surface: #1B201D;
            --background: #141815;
            --primary: #A5B4FC;
            --primary-dark: #C4CCFE;
            --secondary-hover: #232925;
            --on-primary: #1E1B4B;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            color: var(--ink);
            background: var(--background);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .error-card {
            width: min(100%, 560px);
            padding: clamp(32px, 7vw, 64px);
            text-align: center;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 24px 70px rgba(23, 32, 51, 0.08);
        }

        .error-status {
            margin: 0 0 16px;
            color: var(--primary);
            font-size: clamp(64px, 14vw, 112px);
            font-weight: 800;
            letter-spacing: -0.08em;
            line-height: 0.9;
        }

        h1 {
            margin: 0;
            font-size: clamp(24px, 4vw, 34px);
            letter-spacing: -0.03em;
        }

        p {
            margin: 16px auto 0;
            max-width: 390px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 32px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .button-primary {
            color: var(--on-primary);
            background: var(--primary);
        }

        .button-primary:hover { background: var(--primary-dark); }

        .button-secondary {
            color: var(--ink);
            border: 1px solid var(--line);
            background: var(--surface);
        }

        .button-secondary:hover { background: var(--secondary-hover); }
    </style>
</head>
<body>
    <main class="error-card" role="main">
        <div class="error-status">{{ $status }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <div class="actions">
            <a class="button button-primary" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
            <a class="button button-secondary" href="{{ url('/') }}">{{ __('Back to home') }}</a>
        </div>
    </main>
</body>
</html>
