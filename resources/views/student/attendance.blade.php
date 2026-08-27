<x-layouts.app :pageTitle="__('My Attendance')">
    <style>
        .page-header { margin-bottom: 20px; }
        .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }

        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
        @media(max-width: 768px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }

        .stat-card {
            background: var(--surface); border-radius: 12px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            padding: 16px 18px;
            display: flex; align-items: center; gap: 12px;
        }
        .stat-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .stat-value { font-size: 22px; font-weight: 800; color: var(--text-primary); line-height: 1; }
        .stat-label { font-size: 11.5px; color: var(--text-muted); font-weight: 600; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }

        .filter-card {
            background: var(--surface); border-radius: 14px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
        }
        .filter-input {
            padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; font-family: var(--font-body); color: var(--text-strong);
            background: var(--surface-3); outline: none; transition: border 0.2s;
        }
        .filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .btn-filter {
            padding: 8px 18px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 8px; font-size: 13px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
        }
        .btn-filter:hover { background: var(--primary-dark); }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 12px 16px; text-align: start;
            font-size: 11px; font-weight: 700; color: var(--text-muted);
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--surface-2);
            font-size: 13.5px; color: var(--text-strong);
        }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-present  { background: var(--success-border); color: var(--success-dark); }
        .badge-absent   { background: var(--danger-tint); color: var(--danger-dark); }
        .badge-late     { background: var(--warning-tint); color: var(--warning-dark); }
        .badge-excused  { background: var(--primary-tint); color: var(--primary); }
        .badge-pending  { background: var(--warning-tint); color: var(--warning-text); }
        .badge-approved { background: var(--success-border); color: var(--success-dark); }
        .badge-rejected { background: var(--danger-tint); color: var(--danger-dark); }
        .badge-none     { background: var(--border-soft); color: var(--text-muted); }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon {
            width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
        }
    </style>

    <div class="page-header">
        <div class="page-title">{{ __("My Attendance") }}</div>
        <div class="page-desc">{{ __("View your attendance history and justification statuses") }}</div>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        @php
            $total   = $summary->sum();
            $present = $summary->get('present', 0);
            $absent  = $summary->get('absent', 0) + $summary->get('excused', 0);
            $late    = $summary->get('late', 0);
            $rate    = $total > 0 ? round(($present / $total) * 100) : 0;
        @endphp
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div><div class="stat-value">{{ $total }}</div><div class="stat-label">{{ __("Total Days") }}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-border);">
                <svg width="20" height="20" fill="none" stroke="var(--success-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><div class="stat-value" style="color: var(--success-dark);">{{ $present }}</div><div class="stat-label">{{ __("Present") }}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--danger-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div><div class="stat-value" style="color: var(--danger-dark);">{{ $absent }}</div><div class="stat-label">{{ __("Absent") }}</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--warning-dark)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="stat-value" style="color: var(--warning-dark);">{{ $rate }}%</div><div class="stat-label">{{ __("Rate") }}</div></div>
        </div>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('student.attendance') }}">
        <div class="filter-card">
            <div class="filter-group">
                <label class="filter-label">{{ __("From") }}</label>
                <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">{{ __("To") }}</label>
                <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-filter">{{ __("Filter") }}</button>
            @if(request('date_from') || request('date_to'))
                <a href="{{ route('student.attendance') }}" style="align-self: flex-end; padding: 8px 14px; font-size: 12.5px; color: var(--text-secondary); text-decoration: none;">{{ __("Clear") }}</a>
            @endif
        </div>
    </form>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __("Attendance History") }}</div>
            <div class="table-meta">{{ __(":count records", ['count' => $records->total()]) }}</div>
        </div>

        @if($records->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="empty-title">{{ __("No Records Found") }}</div>
                <div class="empty-desc">{{ __("No attendance records match the selected filters.") }}</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __("Subject") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th>{{ __("Justification") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $record->date?->format('M j, Y') }}</div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $record->date?->format('l') }}</div>
                                </td>
                                <td style="color: var(--text-secondary);">{{ $record->scheduleSlot?->subject?->name ?? '—' }}</td>
                                <td><span class="badge badge-{{ $record->status->value }}">{{ __(ucfirst($record->status->value)) }}</span></td>
                                <td>
                                    @if($record->justification)
                                        <span class="badge badge-{{ $record->justification->status->value }}">{{ __(ucfirst($record->justification->status->value)) }}</span>
                                    @elseif($record->isAbsent())
                                        <span class="badge badge-none" style="font-size: 11px;">{{ __("No justification") }}</span>
                                    @else
                                        <span style="color: var(--text-faint); font-size: 12px;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="pagination-row">
                    <div>{{ __("Page :current of :last", ['current' => $records->currentPage(), 'last' => $records->lastPage()]) }}</div>
                    <div style="display: flex; gap: 6px;">
                        @if($records->onFirstPage())
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("← Prev") }}</span>
                        @else
                            <a href="{{ $records->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("← Prev") }}</a>
                        @endif
                        @if($records->hasMorePages())
                            <a href="{{ $records->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("Next →") }}</a>
                        @else
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next →") }}</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
