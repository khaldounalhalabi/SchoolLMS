{{--
    Theme toggle. Cycles light → dark → system and reflects the resolved
    theme. Behaviour lives in resources/js/theme.js; this component only
    renders the control and lets the JS read/write data-theme-pref on <html>.
--}}
<div class="theme-switcher" data-theme-switcher>
    <button type="button"
            class="theme-btn"
            data-theme-set="light"
            aria-label="{{ __('Light mode') }}"
            title="{{ __('Light mode') }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="4" stroke-width="2"/>
            <path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
        </svg>
    </button>
    <button type="button"
            class="theme-btn"
            data-theme-set="dark"
            aria-label="{{ __('Dark mode') }}"
            title="{{ __('Dark mode') }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
        </svg>
    </button>
    <button type="button"
            class="theme-btn"
            data-theme-set="system"
            aria-label="{{ __('System theme') }}"
            title="{{ __('System theme') }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="13" rx="2" stroke-width="2"/>
            <path stroke-linecap="round" stroke-width="2" d="M8 21h8m-4-4v4"/>
        </svg>
    </button>
</div>

@once
    <style>
        .theme-switcher {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 3px;
            border: 1px solid var(--border);
            border-radius: 50px;
            background: var(--surface-2);
        }

        .theme-switcher .theme-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .theme-switcher .theme-btn:hover {
            background: var(--hover-surface);
            color: var(--text-primary);
        }

        .theme-switcher .theme-btn.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: var(--shadow-card);
        }

        .theme-switcher .theme-btn:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 1px;
        }
    </style>
@endonce
