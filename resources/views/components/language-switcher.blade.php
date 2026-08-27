@php
    $currentLocale = app()->getLocale();
    // Preserve where the user is: redirect()->back() in the controller returns here.
@endphp
<div class="lang-switcher">
    <a href="{{ route('language.switch', 'en') }}"
       class="lang-option {{ $currentLocale === 'en' ? 'active' : '' }}"
       aria-label="Switch to English">EN</a>
    <span class="lang-sep">|</span>
    <a href="{{ route('language.switch', 'ar') }}"
       class="lang-option {{ $currentLocale === 'ar' ? 'active' : '' }}"
       aria-label="التبديل إلى العربية">ع</a>
</div>

@once
    <style>
        .lang-switcher {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 50px;
            background: var(--surface-2, var(--surface-2));
            border: 1px solid var(--border, var(--border));
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-body);
        }
        .lang-switcher .lang-option {
            color: var(--text-muted, var(--text-muted));
            text-decoration: none;
            transition: color 0.2s;
            line-height: 1;
        }
        .lang-switcher .lang-option:hover { color: var(--primary, var(--primary)); }
        .lang-switcher .lang-option.active { color: var(--primary, var(--primary)); }
        .lang-switcher .lang-sep { color: var(--border, var(--text-faint)); }
    </style>
@endonce
