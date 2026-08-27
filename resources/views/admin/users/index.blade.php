<x-layouts.app :pageTitle="__('User Management')">
    <style>
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            background: var(--primary);
            color: var(--on-primary);
            border-radius: 10px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            font-family: var(--font-body);
            transition: all 0.2s;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 40%, transparent);
        }
        .table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-soft);
            gap: 12px;
            flex-wrap: wrap;
        }
        .search-input {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: var(--font-body);
            color: var(--text-strong);
            outline: none;
            background: var(--surface-3);
            width: 220px;
            transition: all 0.2s;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        /* The toolbar already wraps; let the search fill the row it wraps onto
           instead of sitting at 220px beside empty space. */
        @media (max-width: 640px) {
            .table-toolbar { align-items: stretch; }
            .search-input { width: 100%; }
        }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 12px 16px;
            text-align: start;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        th:last-child { padding-inline-end: 20px; text-align: end; }
        .top-students-card { margin-bottom: 20px; background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; }
        .top-students-header { padding: 16px 20px; border-bottom: 1px solid var(--border-soft); font-weight: 700; color: var(--text-primary); }
        .top-students-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .top-student { padding: 16px; border-inline-end: 1px solid var(--border-soft); }
        .top-student:last-child { border-inline-end: 0; }
        @media (max-width: 900px) { .top-students-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px) { .top-students-grid { grid-template-columns: 1fr; } .top-student { border-inline-end: 0; border-bottom: 1px solid var(--border-soft); } }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ $role ? __(ucfirst($role)) : __('All Users') }}</div>
            <div class="page-desc">{{ $role ? __(':count :role accounts', ['count' => $users->total(), 'role' => __(ucfirst($role))]) : __(':count users registered in the system', ['count' => $users->total()]) }}</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __("Create User") }}
        </a>
    </div>

    @if($role === 'student')
        <div class="top-students-card">
            <div class="top-students-header">{{ __('Top Students') }}</div>
            @if($topStudents->isEmpty())
                <div style="padding: 24px 20px; color: var(--text-faint); font-size: 13px;">{{ __('No grade data is available for the active academic year yet.') }}</div>
            @else
                <div class="top-students-grid">
                    @foreach($topStudents as $index => $row)
                        <div class="top-student">
                            <div style="font-size: 11px; color: var(--primary); font-weight: 700;">#{{ $index + 1 }}</div>
                            <div style="font-weight: 700; color: var(--text-primary); margin-top: 5px;">{{ $row['student']->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $row['student']->studentProfile?->classroom?->name ?? __('No classroom') }}</div>
                            <div style="font-size: 18px; font-weight: 700; color: var(--success-dark); margin-top: 8px;">{{ number_format($row['average'], 1) }}%</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="table-card">
        <div class="table-toolbar">
            <input type="text" class="search-input" placeholder="{{ __('Search users...') }}" data-table-filter="#usersTable">
            <div class="table-meta">{{ __('Showing :from–:to of :total', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]) }}</div>
        </div>

        <div style="overflow-x: auto;">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>{{ __("User") }}</th>
                        <th>{{ __("Role") }}</th>
                        <th>{{ __("Phone") }}</th>
                        <th>{{ __("Status") }}</th>
                        <th>{{ __("Joined") }}</th>
                        <th>{{ __("Actions") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <x-user-table-row :user="$user" />
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: var(--text-faint); font-size: 14px;">
                                {{ __("No users found") }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="pagination-row">
            <div>{{ __('Page :current of :last', ['current' => $users->currentPage(), 'last' => $users->lastPage()]) }}</div>
            <div style="display: flex; gap: 6px; align-items: center;">
                @if($users->onFirstPage())
                    <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("← Prev") }}</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.2s;">{{ __("← Prev") }}</a>
                @endif

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px color-mix(in srgb, var(--primary) 30%, transparent);">{{ __("Next →") }}</a>
                @else
                    <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next →") }}</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</x-layouts.app>
