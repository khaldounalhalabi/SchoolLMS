<x-layouts.app :pageTitle="__('Salaries')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }

        .stat-card {
            background: var(--surface); border-radius: 14px; padding: 20px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            margin-bottom: 24px; max-width: 280px;
        }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .stat-value { font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--success-dark); }

        .table-card { background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft); box-shadow: var(--shadow-card); overflow: hidden; }
        .table-header { padding: 20px; border-bottom: 1px solid var(--border-soft); }
        .table-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .table-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th { padding: 12px 16px; text-align: start; font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
        th:first-child { padding-inline-start: 20px; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--surface-2); font-size: 13.5px; color: var(--text-strong); vertical-align: middle; }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-paid { background: var(--success-border); color: var(--success-dark); }
        .badge-pending { background: var(--warning-tint); color: var(--warning-text); }
        .badge-failed { background: var(--danger-tint); color: var(--danger-dark); }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon { width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }

        .pagination-row { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid var(--border-soft); font-size: 12px; color: var(--text-secondary); }
    </style>

    <div class="page-header">
        <h2>{{ __("Salary History") }}</h2>
        <p>{{ __("View all salary transfers sent to you by the school") }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-label">{{ __("Total Received") }}</div>
        <div class="stat-value">{{ strtoupper($currency) }} {{ number_format((float) $totalPaid, 2) }}</div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __("Transfer History") }}</div>
            <div class="table-meta">{{ __(":count transfers", ['count' => $transfers->total()]) }}</div>
        </div>

        @if($transfers->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="rtl-display" style="font-family: var(--font-display); font-size: 16px; color: var(--text-muted); margin-bottom: 6px;">{{ __("No Transfers Yet") }}</div>
                <div style="font-size: 13px; color: var(--text-faint);">{{ __("No salary transfers have been sent to you yet.") }}</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Description") }}</th>
                            <th>{{ __("Status") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $transfer)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $transfer->transfer_date?->format('M j, Y') }}</div>
                                </td>
                                <td style="font-weight: 600;">{{ strtoupper($transfer->currency) }} {{ number_format((float) $transfer->amount, 2) }}</td>
                                <td style="color: var(--text-secondary);">{{ $transfer->description ?? '—' }}</td>
                                <td><span class="badge badge-{{ $transfer->status->value }}">{{ __(ucfirst($transfer->status->value)) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transfers->hasPages())
                <div class="pagination-row">
                    <div>{{ __("Page :current of :last", ['current' => $transfers->currentPage(), 'last' => $transfers->lastPage()]) }}</div>
                    <div style="display: flex; gap: 6px;">
                        @if($transfers->onFirstPage())
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">&larr; {{ __("Prev") }}</span>
                        @else
                            <a href="{{ $transfers->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600;">&larr; {{ __("Prev") }}</a>
                        @endif
                        @if($transfers->hasMorePages())
                            <a href="{{ $transfers->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("Next") }} &rarr;</a>
                        @else
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next") }} &rarr;</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
