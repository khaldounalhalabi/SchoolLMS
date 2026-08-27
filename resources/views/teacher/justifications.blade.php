<x-layouts.app :pageTitle="__('Absence Justifications')">
    <style>
        .page-header { margin-bottom: 20px; }
        .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }

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
            vertical-align: middle;
        }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .student-cell { display: flex; align-items: center; gap: 10px; }
        .student-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--violet-dark));
            display: flex; align-items: center; justify-content: center;
            color: var(--on-primary); font-size: 11px; font-weight: 700; flex-shrink: 0;
        }
        .student-name { font-weight: 600; color: var(--text-primary); font-size: 13px; }
        .student-class { font-size: 11.5px; color: var(--text-muted); }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
        }
        .badge-absent  { background: var(--danger-tint); color: var(--danger-text); }
        .badge-pending { background: var(--warning-tint); color: var(--warning-text); }

        .reason-text {
            max-width: 240px;
            font-size: 12.5px;
            color: var(--text-secondary);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .action-forms { display: flex; gap: 6px; align-items: center; }
        .btn-approve, .btn-reject {
            padding: 6px 14px;
            border-radius: 8px;
            border: none;
            font-size: 12px; font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-approve { background: var(--success-border); color: var(--success-text); }
        .btn-approve:hover { background: var(--success-border); }
        .btn-reject  { background: var(--danger-tint); color: var(--danger-text); }
        .btn-reject:hover  { background: var(--danger-border); }

        .doc-link {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;
        }
        .doc-link:hover { text-decoration: underline; }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon {
            width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
        }
    </style>

    <x-page-header
        :title="__('Absence Justifications')"
        :description="__('Review and approve or reject pending parent justifications')"
    />

    @if(session('success'))
        <div style="background: var(--success-border); border: 1px solid var(--success-border); color: var(--success-text); padding: 12px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 500; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __("Pending Justifications") }}</div>
            <div class="table-meta">{{ __(":count pending", ['count' => $justifications->total()]) }}</div>
        </div>

        @if($justifications->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="empty-title">{{ __("All Clear!") }}</div>
                <div class="empty-desc">{{ __("No pending justifications to review.") }}</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __("Student") }}</th>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __("Reason") }}</th>
                            <th>{{ __("Submitted By") }}</th>
                            <th>{{ __("Document") }}</th>
                            <th>{{ __("Actions") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($justifications as $j)
                            <tr>
                                <td>
                                    <div class="student-cell">
                                        <div class="student-avatar">{{ strtoupper(substr($j->attendance->student->name ?? '?', 0, 2)) }}</div>
                                        <div>
                                            <div class="student-name">{{ $j->attendance->student->name ?? '—' }}</div>
                                            <div class="student-class">{{ $j->attendance->classroom->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $j->attendance->date?->format('M j, Y') }}</div>
                                    <span class="badge badge-absent">{{ __("Absent") }}</span>
                                </td>
                                <td>
                                    <div class="reason-text">{{ $j->reason }}</div>
                                </td>
                                <td style="font-size: 12.5px; color: var(--text-secondary);">{{ $j->submittedBy->name ?? '—' }}</td>
                                <td>
                                    @if($j->document_path)
                                        <a href="{{ route('teacher.justifications.document', $j) }}" class="doc-link">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            {{ __("View") }}
                                        </a>
                                    @else
                                        <span style="color: var(--text-faint); font-size: 12px;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-forms">
                                        <form method="POST" action="{{ route('teacher.justifications.approve', $j) }}">
                                            @csrf
                                            <button type="submit" class="btn-approve">{{ __("Approve") }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('teacher.justifications.reject', $j) }}">
                                            @csrf
                                            <button type="submit" class="btn-reject">{{ __("Reject") }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($justifications->hasPages())
                <div class="pagination-row">
                    <div>{{ __("Page :current of :last", ['current' => $justifications->currentPage(), 'last' => $justifications->lastPage()]) }}</div>
                    <div style="display: flex; gap: 6px;">
                        @if($justifications->onFirstPage())
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("← Prev") }}</span>
                        @else
                            <a href="{{ $justifications->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("← Prev") }}</a>
                        @endif
                        @if($justifications->hasMorePages())
                            <a href="{{ $justifications->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("Next →") }}</a>
                        @else
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next →") }}</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
