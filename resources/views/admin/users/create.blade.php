<x-layouts.app :pageTitle="__('Create User')">
    <style>
        .form-container { max-width: 600px; }
        .form-header {
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-soft);
        }
        .form-header-title {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        .form-header-sub { font-size: 13px; color: var(--text-secondary); }

        /* Two-column field grid. .form-field keeps its own bottom margin,
           so the row gap only needs to cover the horizontal axis. */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 18px;
        }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
        .form-grid .full { grid-column: 1 / -1; }

        /* Password reveal button, overlaid on a standard .form-control. */
        .password-input-wrapper { position: relative; }
        .password-input-wrapper .form-control { padding-inline-end: 44px; }
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
        .password-toggle svg { width: 17px; height: 17px; }

        /* Role picker: radio cards, not a standard control. */
        .role-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        @media (max-width: 640px) { .role-options { grid-template-columns: repeat(2, 1fr); } }
        .role-option { position: relative; }
        .role-option input { position: absolute; opacity: 0; }
        .role-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px 8px;
            border-radius: var(--field-radius);
            border: var(--field-border-width) solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-align: center;
        }
        .role-option input:checked + label {
            border-color: var(--primary);
            background: var(--primary-tint);
            color: var(--primary-dark);
        }
        .role-option input:focus-visible + label {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--focus-ring);
        }
        .role-option label:hover { border-color: var(--primary-light); background: var(--primary-tint); }
        .role-icon { font-size: 20px; }
    </style>

    <div class="form-container">
        {{-- Breadcrumb --}}
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            <a href="{{ route('admin.users.index') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">{{ __("Users") }}</a>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span>{{ __("Create New User") }}</span>
        </div>

        <x-ui.form :action="route('admin.users.store')" :summary="false" novalidate>
            <div class="form-header">
                <div class="form-header-title">{{ __("Create New User") }}</div>
                <div class="form-header-sub">{{ __("Add a new account to the school system") }}</div>
            </div>

            <div class="form-grid">
                <x-ui.form.field
                    name="name"
                    :label="__('Full Name')"
                    placeholder="e.g. Ahmad Al-Rashid"
                    required
                />

                <x-ui.form.field
                    name="phone"
                    :label="__('Phone Number')"
                    placeholder="+962 79 xxx xxxx"
                />

                <x-ui.form.field
                    name="email"
                    type="email"
                    :label="__('Email Address')"
                    placeholder="user@school.edu"
                    class="full"
                    required
                />

                <x-ui.form.field name="password" :label="__('Password')" required>
                    <div class="password-input-wrapper">
                        <x-ui.form.input
                            type="password"
                            name="password"
                            :placeholder="__('Min. 8 characters')"
                            :invalid="$errors->has('password')"
                        />
                        <button type="button" class="password-toggle" data-password-toggle="password"
                                data-show-label="{{ __('Show password') }}"
                                data-hide-label="{{ __('Hide password') }}"
                                aria-label="{{ __('Show password') }}" aria-pressed="false">
                            <svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </x-ui.form.field>

                <x-ui.form.field name="password_confirmation" :label="__('Confirm Password')" required>
                    <div class="password-input-wrapper">
                        <x-ui.form.input
                            type="password"
                            name="password_confirmation"
                            :placeholder="__('Repeat password')"
                        />
                        <button type="button" class="password-toggle" data-password-toggle="password_confirmation"
                                data-show-label="{{ __('Show password') }}"
                                data-hide-label="{{ __('Hide password') }}"
                                aria-label="{{ __('Show password') }}" aria-pressed="false">
                            <svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </x-ui.form.field>

                <x-ui.form.field name="role" :label="__('Role')" class="full" label-for="none" required>
                    <div class="role-options">
                        @foreach([
                            ['admin',   __('Admin'),   '🔑'],
                            ['teacher', __('Teacher'), '📚'],
                            ['student', __('Student'), '🎓'],
                            ['parent',  __('Parent'),  '👨‍👩‍👧'],
                        ] as [$value, $label, $emoji])
                        <div class="role-option">
                            <input type="radio" name="role" id="role_{{ $value }}" value="{{ $value }}"
                                   {{ old('role', 'student') === $value ? 'checked' : '' }}>
                            <label for="role_{{ $value }}">
                                <span class="role-icon">{{ $emoji }}</span>
                                {{ $label }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </x-ui.form.field>
            </div>

            <x-slot:actions>
                <x-ui.form.actions
                    :submit="__('Create User')"
                    :cancel="route('admin.users.index')"
                    :icon="null"
                />
            </x-slot:actions>
        </x-ui.form>
    </div>
</x-layouts.app>
