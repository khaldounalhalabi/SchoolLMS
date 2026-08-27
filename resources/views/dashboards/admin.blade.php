<x-layouts.app :pageTitle="__('Admin Dashboard')">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .section-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }

        @media (max-width: 768px) { .quick-links-grid { grid-template-columns: repeat(2, 1fr); } }

        .quick-link {
            background: var(--surface);
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            padding: 18px;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .quick-link:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 10%, transparent);
            transform: translateY(-1px);
        }

        .quick-link-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quick-link-label {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .quick-link-sub {
            font-size: 11.5px;
            color: var(--text-muted);
        }

    </style>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <x-stat-card :label="__('Total Users')"    value="{{ $stats['total_users'] }}"  icon="users"        color="indigo" />
        <x-stat-card :label="__('Administrators')" value="{{ $stats['admins'] }}"        icon="user-admin"   color="purple" />
        <x-stat-card :label="__('Teachers')"       value="{{ $stats['teachers'] }}"      icon="user-teacher" color="blue"   />
        <x-stat-card :label="__('Students')"       value="{{ $stats['students'] }}"      icon="user-student" color="green"  />
        <x-stat-card :label="__('Parents')"        value="{{ $stats['parents'] }}"       icon="user-parent"  color="amber"  />
        <x-stat-card :label="__('Active Users')"   value="{{ $stats['active_users'] }}"  icon="check-circle" color="green"  />
    </div>

    {{-- Quick Links --}}
    <div class="section-header">
        <div>
            <div class="section-title">{{ __("Quick Actions") }}</div>
            <div class="section-subtitle">{{ __("Jump to frequently used sections") }}</div>
        </div>
    </div>

    <div class="quick-links-grid">
        <a href="{{ route('admin.users.index') }}" class="quick-link">
            <div class="quick-link-icon" style="background: var(--primary-tint);">
                <svg width="18" height="18" fill="none" stroke="var(--primary)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="quick-link-label">{{ __("Manage Users") }}</div>
            <div class="quick-link-sub">{{ __("View & edit all users") }}</div>
        </a>

        <a href="{{ route('admin.users.create') }}" class="quick-link">
            <div class="quick-link-icon" style="background: var(--success-tint);">
                <svg width="18" height="18" fill="none" stroke="var(--success)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div class="quick-link-label">{{ __("Create User") }}</div>
            <div class="quick-link-sub">{{ __("Add a new account") }}</div>
        </a>

        <a href="{{ route('admin.reports.index') }}" class="quick-link">
            <div class="quick-link-icon" style="background: var(--warning-tint);">
                <svg width="18" height="18" fill="none" stroke="var(--warning)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="quick-link-label">{{ __("Reports") }}</div>
            <div class="quick-link-sub">{{ __("Analytics & exports") }}</div>
        </a>

        <a href="#" class="quick-link">
            <div class="quick-link-icon" style="background: var(--violet-tint);">
                <svg width="18" height="18" fill="none" stroke="var(--violet)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>
                </svg>
            </div>
            <div class="quick-link-label">{{ __("Settings") }}</div>
            <div class="quick-link-sub">{{ __("System configuration") }}</div>
        </a>
    </div>

</x-layouts.app>
