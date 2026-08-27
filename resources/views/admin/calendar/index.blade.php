<x-layouts.app :pageTitle="__('School Calendar')">
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
        .badge-holiday { background: var(--warning-tint); color: var(--warning-text); border: 1px solid var(--warning-border); }
        .badge-event { background: var(--info-tint-2); color: var(--info-strong); border: 1px solid var(--info-border); }
        .badge-exam { background: var(--pink-tint); color: var(--pink-text); border: 1px solid var(--pink-border); }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: var(--text-faint);
            font-size: 14px;
        }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __("School Calendar") }}</div>
            <div class="page-desc">{{ __("Manage holidays, events, and exam schedules") }}</div>
        </div>
        <a href="{{ route('admin.calendar.create') }}" class="btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __("New Event") }}
        </a>
    </div>

    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __("Date") }}</th>
                    <th>{{ __("Type") }}</th>
                    <th>{{ __("Description") }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                    <tr style="cursor: pointer; transition: background 0.15s;"
                        data-row-href="{{ route('admin.calendar.show', $event) }}">
                        <td>{{ $event->date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $event->type->value }}">
                                {{ __(ucfirst($event->type->value)) }}
                            </span>
                        </td>
                        <td>{{ $event->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty-state">{{ __("No calendar events found. Add one to get started.") }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-layouts.app>
