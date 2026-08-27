{{--
    Shared settings page — reachable by every role from the sidebar.
    Hosts the language and appearance controls that previously lived in the
    dashboard topbar. The auth pages (login / forgot / reset) keep their own
    inline switchers on purpose: a settings page is post-login, and a user who
    cannot sign in still needs to reach Arabic/RTL and a readable theme.
--}}
<x-layouts.app :pageTitle="__('Settings')">
    @once
        <style>
            .settings-page { max-width: 720px; }

            .settings-section + .settings-section { margin-top: 16px; }

            .settings-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                flex-wrap: wrap;
                padding: 20px 22px;
            }

            .settings-label {
                font-size: 14px;
                font-weight: 700;
                color: var(--text-primary);
                margin-bottom: 4px;
            }

            .settings-hint {
                font-size: 12.5px;
                color: var(--text-secondary);
                line-height: 1.5;
                max-width: 42ch;
            }

            .settings-control { flex-shrink: 0; }
        </style>
    @endonce

    <div class="settings-page">
        <div class="page-header">
            <div>
                <div class="page-title">{{ __('Settings') }}</div>
                <div class="page-desc">{{ __('Manage your language and appearance preferences.') }}</div>
            </div>
        </div>

        <div class="card settings-section">
            <div class="settings-row">
                <div>
                    <div class="settings-label">{{ __('Language') }}</div>
                    <div class="settings-hint">
                        {{ __('Choose the language used across the dashboard. Arabic switches the interface to right-to-left.') }}
                    </div>
                </div>
                <div class="settings-control">
                    <x-language-switcher />
                </div>
            </div>
        </div>

        <div class="card settings-section">
            <div class="settings-row">
                <div>
                    <div class="settings-label">{{ __('Appearance') }}</div>
                    <div class="settings-hint">
                        {{ __('Pick a light or dark theme, or follow your device setting. Your choice is remembered on this browser.') }}
                    </div>
                </div>
                <div class="settings-control">
                    <x-theme-switcher />
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
