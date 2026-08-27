<x-layouts.app :pageTitle="__('Grades')">
    <style>
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 20px; background: var(--primary); color: var(--on-primary);
            border-radius: 10px; text-decoration: none; font-size: 13.5px;
            font-weight: 600; font-family: var(--font-body);
            transition: all 0.2s; box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-danger {
            padding: 6px 12px; border: 1px solid var(--danger-border); border-radius: 8px;
            background: var(--danger-tint); color: var(--danger-dark); cursor: pointer;
            font: 600 12px var(--font-body);
        }
        .form-card { max-width: 620px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th { padding: 12px 16px; text-align: start; font-size: 11px; color: var(--text-muted); text-transform: uppercase; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--border-soft); font-size: 13.5px; color: var(--text-primary); }
        td:first-child, th:first-child { padding-inline-start: 20px; }
        .empty-state { padding: 48px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __('Grades') }}</div>
            <div class="page-desc">{{ __('Manage school grades used by classrooms') }}</div>
        </div>
    </div>

    <div class="table-card form-card">
        <div class="table-header">
            <div class="table-title">{{ __('Add Grade') }}</div>
            <div class="table-meta">{{ __('Create a grade before adding its classrooms.') }}</div>
        </div>
        <form method="POST" action="{{ route('admin.grades.store') }}" style="padding: 20px;">
            @csrf
            <div class="form-row">
                <x-ui.form.field name="name" :label="__('Grade Name')" :placeholder="__('e.g. Grade 7')" required />
                <x-ui.form.field name="order_index" type="number" :label="__('Display Order')" :placeholder="__('Optional')" min="0" />
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Grade') }}
                </button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __('Existing Grades') }}</div>
            <div class="table-meta">{{ __(':count grades', ['count' => $grades->count()]) }}</div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Grade') }}</th>
                        <th>{{ __('Display Order') }}</th>
                        <th>{{ __('Classrooms') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $grade)
                        <tr>
                            <td style="font-weight: 600;">{{ $grade->name }}</td>
                            <td>{{ $grade->order_index }}</td>
                            <td>{{ $grade->classrooms_count }}</td>
                            <td>
                                @if($grade->classrooms_count === 0)
                                    <form method="POST" action="{{ route('admin.grades.destroy', $grade) }}" onsubmit="return confirm('{{ __('Delete this grade?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger">{{ __('Delete') }}</button>
                                    </form>
                                @else
                                    <span style="font-size: 12px; color: var(--text-muted);">{{ __('In use') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">{{ __('No grades found. Create one to get started.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
