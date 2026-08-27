<x-layouts.app :pageTitle="__('Academic Years')">
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
        td {
            padding: 14px 16px;
            font-size: 13.5px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-soft);
        }
        td:first-child { padding-inline-start: 20px; font-weight: 600; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-active {
            background: var(--success-tint);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }
        .badge-inactive {
            background: var(--surface-2);
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: var(--text-faint);
            font-size: 14px;
        }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __("Academic Years") }}</div>
            <div class="page-desc">{{ __("Manage academic years and semesters") }}</div>
        </div>
        <a href="{{ route('admin.academic-years.create') }}" class="btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __("New Academic Year") }}
        </a>
    </div>

    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __("Name") }}</th>
                    <th>{{ __("Start Date") }}</th>
                    <th>{{ __("End Date") }}</th>
                    <th>{{ __("Semesters") }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($years as $year)
                    <tr style="cursor: pointer; transition: background 0.15s;"
                        data-row-href="{{ route('admin.academic-years.show', $year) }}">
                        <td>{{ $year->name }}</td>
                        <td>{{ $year->start_date->format('M d, Y') }}</td>
                        <td>{{ $year->end_date->format('M d, Y') }}</td>
                        <td>{{ $year->semesters_count }}</td>
                        <td>
                            <span class="badge {{ $year->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $year->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td data-row-ignore>
                            @if(! $year->is_active)
                                <form method="POST" action="{{ route('admin.academic-years.activate', $year) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">{{ __('Activate') }}</button>
                                </form>
                            @else
                                <span style="font-size: 12px; color: var(--text-muted);">{{ __('Current') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">{{ __('No academic years found. Create one to get started.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-layouts.app>
