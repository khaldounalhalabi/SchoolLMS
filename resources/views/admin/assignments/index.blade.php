<x-layouts.app :pageTitle="__('Teacher Assignments')">
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
        td {
            padding: 12px 16px;
            font-size: 13.5px;
            color: var(--text-strong);
            border-bottom: 1px solid var(--surface-2);
        }
        td:first-child { padding-inline-start: 20px; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-faint);
        }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __("Teacher Assignments") }}</div>
            <div class="page-desc">{{ __("Manage teacher-subject-classroom assignments") }}</div>
        </div>
        <a href="{{ route('admin.assignments.create') }}" class="btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __("Create Assignment") }}
        </a>
    </div>

    <div class="table-card">
        <div class="table-toolbar">
            <input type="text" class="search-input" placeholder="{{ __('Search assignments...') }}" data-table-filter="#assignmentsTable">
            <div class="table-meta">{{ __(":count assignments total", ['count' => $assignments->total()]) }}</div>
        </div>

        <div style="overflow-x: auto;">
            <table id="assignmentsTable">
                <thead>
                    <tr>
                        <th>{{ __("Teacher") }}</th>
                        <th>{{ __("Subject") }}</th>
                        <th>{{ __("Classroom") }}</th>
                        <th>{{ __("Academic Year") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary-tint); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--primary-dark);">
                                        {{ collect(explode(' ', $a->teacher->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('') }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;">{{ $a->teacher->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $a->subject->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $a->subject->code }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $a->classroom->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $a->classroom->grade->name }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--success-tint); color: var(--success-text);">{{ $a->academicYear->name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <svg width="40" height="40" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                {{ __("No assignments found. Create one to get started.") }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignments->hasPages())
        <div class="pagination-row">
            <div>{{ __("Page :current of :last", ['current' => $assignments->currentPage(), 'last' => $assignments->lastPage()]) }}</div>
            <div style="display: flex; gap: 6px; align-items: center;">
                @if($assignments->onFirstPage())
                    <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">&larr; {{ __("Prev") }}</span>
                @else
                    <a href="{{ $assignments->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.2s;">&larr; {{ __("Prev") }}</a>
                @endif

                @if($assignments->hasMorePages())
                    <a href="{{ $assignments->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600; box-shadow: 0 2px 6px color-mix(in srgb, var(--primary) 30%, transparent);">{{ __("Next") }} &rarr;</a>
                @else
                    <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next") }} &rarr;</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</x-layouts.app>
