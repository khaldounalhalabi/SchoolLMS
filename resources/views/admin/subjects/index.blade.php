<x-layouts.app :pageTitle="__('Subjects')">
    <style>
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 20px; background: var(--primary); color: var(--on-primary);
            border-radius: 10px; text-decoration: none; font-size: 13.5px;
            font-weight: 600; font-family: var(--font-body);
            transition: all 0.2s; box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 12px 16px; text-align: start;
            font-size: 11px; font-weight: 700; color: var(--text-muted);
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        td {
            padding: 14px 16px; font-size: 13.5px; color: var(--text-primary);
            border-bottom: 1px solid var(--border-soft);
        }
        td:first-child { padding-inline-start: 20px; font-weight: 600; }
        .code-badge {
            display: inline-block; padding: 3px 10px; border-radius: 6px;
            font-size: 11.5px; font-weight: 600; font-family: monospace;
            background: var(--border-soft); color: var(--text-secondary); border: 1px solid var(--border);
        }
        .actions { display: flex; gap: 8px; }
        .btn-edit {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 12px; font-size: 12.5px; font-weight: 600;
            border-radius: 8px; text-decoration: none; transition: all 0.15s;
            color: var(--primary); background: var(--primary-tint); border: 1px solid var(--primary-light);
        }
        .btn-edit:hover { background: var(--indigo-wash); }
        .btn-delete {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 12px; font-size: 12.5px; font-weight: 600;
            border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s;
            color: var(--danger-dark); background: var(--danger-tint); border: 1px solid var(--danger-border);
            font-family: var(--font-body);
        }
        .btn-delete:hover { background: var(--danger-tint); }
        .empty-state { text-align: center; padding: 48px; color: var(--text-faint); font-size: 14px; }
        .alert-success {
            background: var(--success-tint); border: 1px solid var(--success-border); border-radius: 10px;
            padding: 12px 16px; font-size: 13.5px; color: var(--success-text); margin-bottom: 16px;
        }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __("Subjects") }}</div>
            <div class="page-desc">{{ __("Manage school subjects") }}</div>
        </div>
        <a href="{{ route('admin.subjects.create') }}" class="btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __("New Subject") }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __("Name") }}</th>
                    <th>{{ __("Code") }}</th>
                    <th>{{ __("School") }}</th>
                    <th>{{ __("Actions") }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    <tr style="cursor: pointer; transition: background 0.15s;"
                        data-row-href="{{ route('admin.subjects.show', $subject) }}">
                        <td>{{ $subject->name }}</td>
                        <td><span class="code-badge">{{ $subject->code }}</span></td>
                        <td style="font-weight: 400;">{{ $subject->school?->name ?? '—' }}</td>
                        <td data-row-ignore>
                            <div class="actions">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-edit">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ __("Edit") }}
                                </a>
                                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                      onsubmit="return confirm('Delete subject {{ $subject->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-delete">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        {{ __("Delete") }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">{{ __("No subjects found. Create one to get started.") }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-layouts.app>
